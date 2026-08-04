<?php
/**
 * Ad slot placement tests.
 *
 * Runs inc/ads.php against stubbed WordPress functions so the injection
 * logic can be checked without a WordPress install:
 *
 *   php design/tests/ad-placement.php
 *
 * Exits non-zero on failure.
 */

define( 'ABSPATH', __DIR__ );

// --- Test-controlled state ------------------------------------------------

$GLOBALS['hr_theme_mods'] = array();

// --- WordPress stubs ------------------------------------------------------

function apply_filters( $tag, $value ) { return $value; }
function add_action() {}
function add_filter() {}
function __( $text ) { return $text; }
function esc_html__( $text ) { return $text; }
function esc_attr__( $text ) { return $text; }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function sanitize_html_class( $class ) { return preg_replace( '/[^A-Za-z0-9_-]/', '', $class ); }
function is_singular() { return true; }
function in_the_loop() { return true; }
function is_main_query() { return true; }
function is_feed() { return false; }
function post_password_required() { return false; }
function get_the_ID() { return 1; }
function get_post_meta() { return ''; }

function get_theme_mod( $key, $default = false ) {
	return array_key_exists( $key, $GLOBALS['hr_theme_mods'] )
		? $GLOBALS['hr_theme_mods'][ $key ]
		: $default;
}

/**
 * Stands in for inc/adsterra.php so these tests exercise the placement logic
 * against known input rather than whatever creatives happen to be shipping.
 * The real codes are checked in adsterra-codes.php.
 */
function hauntedreal_default_ad_code( $slot, $variant = 'desktop' ) {
	return '';
}

require_once dirname( __DIR__, 2 ) . '/hauntedreal-child/inc/ads.php';

// --- Helpers --------------------------------------------------------------

$failures = array();

function check( $label, $condition ) {
	global $failures;
	if ( ! $condition ) {
		$failures[] = $label;
	}
	printf( "  [%s] %s\n", $condition ? 'PASS' : 'FAIL', $label );
}

/** Ten paragraphs of body copy. */
function sample_content() {
	$out = '';
	for ( $i = 1; $i <= 10; $i++ ) {
		$out .= '<p>Paragraph ' . $i . '</p>';
	}
	return $out;
}

/** Which paragraph each slot ended up after. 0 = before all body copy. */
function slot_positions( $html ) {
	$positions = array();
	$paragraph = 0;

	// Walk the output in order, counting paragraphs and noting each slot.
	preg_match_all( '/<p>Paragraph \d+<\/p>|data-hr-ad-slot="([a-z_]+)"/', $html, $m, PREG_SET_ORDER );

	foreach ( $m as $match ) {
		if ( empty( $match[1] ) ) {
			++$paragraph;
		} else {
			$positions[ $match[1] ] = $paragraph;
		}
	}

	return $positions;
}

// --- Tests ----------------------------------------------------------------

echo "Default placement — banner near the top, native at the midpoint\n";

$GLOBALS['hr_theme_mods'] = array(
	'hauntedreal_ad_after_intro_code'   => '<script>BANNER_START</script>',
	'hauntedreal_ad_mid_content_code'   => '<script>NATIVE_MID</script>',
	'hauntedreal_ad_after_content_code' => '<script>BANNER_END</script>',
);

$out = hauntedreal_inject_in_content_ads( sample_content() );
$pos = slot_positions( $out );

check( 'intro banner is injected', isset( $pos['after_intro'] ) );
check( 'intro banner follows paragraph 2', 2 === ( $pos['after_intro'] ?? -1 ) );
check( 'native unit is injected', isset( $pos['mid_content'] ) );
check( 'native unit lands mid-article (4-6)', ( $pos['mid_content'] ?? 0 ) >= 4 && ( $pos['mid_content'] ?? 0 ) <= 6 );
check( 'native sits after the intro banner', ( $pos['mid_content'] ?? 0 ) > ( $pos['after_intro'] ?? 0 ) );
check( 'intro code is live, not a placeholder', false !== strpos( $out, 'BANNER_START' ) );
check( 'native code is live', false !== strpos( $out, 'NATIVE_MID' ) );
check( 'live slots drop the placeholder class', false === strpos( $out, 'Responsive · reserves' ) );
check( 'end banner is NOT in the content (it renders after the article)', false === strpos( $out, 'BANNER_END' ) );

echo "\nPosition 0 — banner opens the article\n";

$GLOBALS['hr_theme_mods']['hauntedreal_intro_ad_paragraph'] = 0;
$out = hauntedreal_inject_in_content_ads( sample_content() );
$pos = slot_positions( $out );

check( 'intro banner precedes all body copy', 0 === ( $pos['after_intro'] ?? -1 ) );
check( 'native unit still mid-article', ( $pos['mid_content'] ?? 0 ) >= 4 );

echo "\nArticle-bottom and header slots\n";

$GLOBALS['hr_theme_mods'] = array(
	'hauntedreal_ad_after_content_code' => '<script>BANNER_END</script>',
	'hauntedreal_ad_header_code'        => '<script>BANNER_TOP</script>',
);

$end = hauntedreal_get_ad_slot( 'after_content' );
check( 'end banner renders', false !== strpos( $end, 'BANNER_END' ) );
check( 'end banner reserves height via its modifier class', false !== strpos( $end, 'hr-ad--after' ) );
check( 'end banner is labelled for the reader', false !== strpos( $end, 'Advertisement' ) );

$top = hauntedreal_get_ad_slot( 'header' );
check( 'header banner renders', false !== strpos( $top, 'BANNER_TOP' ) );
check( 'header banner uses the leaderboard reservation', false !== strpos( $top, 'hr-ad--header' ) );

echo "\nSocial Bar — overlay, no chrome, no reserved space\n";

$GLOBALS['hr_theme_mods'] = array(
	'hauntedreal_ad_social_bar_code' => '<script src="//example.net/sb.js"></script>',
);

$social = hauntedreal_get_ad_slot( 'social_bar' );
check( 'social bar prints its script', false !== strpos( $social, 'sb.js' ) );
check( 'social bar has no wrapper element', false === strpos( $social, '<aside' ) );
check( 'social bar reserves no height', false === strpos( $social, 'hr-ad__inner' ) );
check( 'social bar carries no visible label', false === strpos( $social, 'hr-ad__label' ) );

echo "\nUnsold slots stay out of the page\n";

$GLOBALS['hr_theme_mods'] = array();

check( 'empty in-content slot renders nothing', '' === hauntedreal_get_ad_slot( 'mid_content' ) );
check( 'empty sidebar slot renders nothing', '' === hauntedreal_get_ad_slot( 'sidebar' ) );
check( 'empty social bar renders nothing', '' === hauntedreal_get_ad_slot( 'social_bar' ) );
check( 'content is untouched when every slot is empty', sample_content() === hauntedreal_inject_in_content_ads( sample_content() ) );

echo "\nPreview mode restores the labelled placeholders\n";

$GLOBALS['hr_theme_mods'] = array(
	'hauntedreal_ads_show_placeholders' => true,
	'hauntedreal_ad_mid_content_code'   => '<script>NATIVE_MID</script>',
);

$preview = hauntedreal_get_ad_slot( 'mid_content' );
check( 'preview draws a placeholder', false !== strpos( $preview, 'hr-ad__inner' ) );
check( 'preview suppresses live code', false === strpos( $preview, 'NATIVE_MID' ) );
check( 'preview suppresses the overlay too', '' === hauntedreal_get_ad_slot( 'social_bar' ) );

echo "\nDual-creative slot — one unit inserted, never both\n";

$GLOBALS['hr_theme_mods'] = array(
	'hauntedreal_ad_header_code'        => '<script>LEADERBOARD_728</script>',
	'hauntedreal_ad_header_code_mobile' => '<script>LEADERBOARD_320</script>',
);

$header = hauntedreal_get_ad_slot( 'header' );

check( 'both creatives are handed to the client', false !== strpos( $header, 'data-hr-ad-desktop' ) && false !== strpos( $header, 'data-hr-ad-mobile' ) );
check( 'neither creative is inline (so neither loads twice)', false === strpos( $header, '<script>LEADERBOARD' ) );
check( 'the creatives are attribute-escaped', false !== strpos( $header, '&lt;script&gt;LEADERBOARD_728' ) );
check( 'the switching breakpoint is declared', false !== strpos( $header, 'data-hr-ad-breakpoint="768"' ) );
check( 'the switcher script is requested for this page', true === hauntedreal_needs_ad_switcher() );
check( 'the slot still reserves its height', false !== strpos( $header, 'hr-ad--header' ) );

echo "\nSingle-creative slot stays inline — no JavaScript involved\n";

$GLOBALS['hr_theme_mods'] = array(
	'hauntedreal_ad_after_intro_code' => '<script>BANNER_300</script>',
);

$intro = hauntedreal_get_ad_slot( 'after_intro' );

check( 'creative is printed inline', false !== strpos( $intro, '<script>BANNER_300</script>' ) );
check( 'no switching attributes are emitted', false === strpos( $intro, 'data-hr-ad-desktop' ) );

echo "\nDisabled slots\n";

$GLOBALS['hr_theme_mods'] = array(
	'hauntedreal_ad_mid_content_enabled' => false,
	'hauntedreal_ad_mid_content_code'    => '<script>NATIVE_MID</script>',
);

check( 'a disabled slot renders nothing', '' === hauntedreal_get_ad_slot( 'mid_content' ) );

// --- Result ---------------------------------------------------------------

echo "\n";

if ( $failures ) {
	echo count( $failures ) . " FAILED:\n  - " . implode( "\n  - ", $failures ) . "\n";
	exit( 1 );
}

echo "ALL CHECKS PASSED\n";
exit( 0 );
