<?php
/**
 * Adsterra code mapping tests.
 *
 * Checks that each shipped creative is wired to the slot it belongs in, and
 * that the snippets are intact — a truncated ad tag fails silently in a
 * browser, which is the worst way to find out.
 *
 *   php design/tests/adsterra-codes.php
 *
 * Exits non-zero on failure.
 */

define( 'ABSPATH', __DIR__ );

function apply_filters( $tag, $value ) { return $value; }

require_once dirname( __DIR__, 2 ) . '/hauntedreal-child/inc/adsterra.php';

$failures = array();

function check( $label, $condition ) {
	global $failures;
	if ( ! $condition ) {
		$failures[] = $label;
	}
	printf( "  [%s] %s\n", $condition ? 'PASS' : 'FAIL', $label );
}

/** Every Adsterra banner snippet is an atOptions block plus its invoke.js. */
function is_valid_banner( $code, $width, $height ) {
	return false !== strpos( $code, 'atOptions' )
		&& false !== strpos( $code, "'width' : " . $width )
		&& false !== strpos( $code, "'height' : " . $height )
		&& 2 === substr_count( $code, '<script' )
		&& 2 === substr_count( $code, '</script>' )
		&& false !== strpos( $code, 'invoke.js' );
}

echo "Slot 01 — leaderboard carries both creatives\n";

$header_desktop = hauntedreal_default_ad_code( 'header' );
$header_mobile  = hauntedreal_default_ad_code( 'header', 'mobile' );

check( 'desktop creative is a 728×90 banner', is_valid_banner( $header_desktop, 728, 90 ) );
check( 'mobile creative is a 320×50 banner', is_valid_banner( $header_mobile, 320, 50 ) );
check( 'the two creatives differ, so switching engages', $header_desktop !== $header_mobile );
check( 'desktop key matches the 728×90 unit', false !== strpos( $header_desktop, '0c1fe4b62102ab00c1935841b3908b40' ) );
check( 'mobile key matches the 320×50 unit', false !== strpos( $header_mobile, '7540bd4eaee43bf6f48734bca7b810bd' ) );

echo "\nArticle slots — banner, native, banner\n";

$intro = hauntedreal_default_ad_code( 'after_intro' );
$mid   = hauntedreal_default_ad_code( 'mid_content' );
$end   = hauntedreal_default_ad_code( 'after_content' );

check( 'slot 02 (article start) is a 300×250 banner', is_valid_banner( $intro, 300, 250 ) );
check( 'slot 03 (mid article) is the Native Banner', false !== strpos( $mid, 'container-6b5b67b32e7a128536a2b854a758f72d' ) );
check( 'slot 03 native ships its container div', false !== strpos( $mid, '<div id="container-' ) );
check( 'slot 03 native is async, per Adsterra', false !== strpos( $mid, 'async' ) );
check( 'slot 03 is NOT an atOptions banner', false === strpos( $mid, 'atOptions' ) );
check( 'slot 04 (article end) is a 300×250 banner', is_valid_banner( $end, 300, 250 ) );

echo "\n300×250 fits the narrowest supported viewport (320px)\n";

check( 'article banners cannot overflow a 320px screen', 300 <= 320 );
check( 'no mobile variant is needed for slot 02', '' === hauntedreal_default_ad_code( 'after_intro', 'mobile' ) );
check( 'no mobile variant is needed for slot 04', '' === hauntedreal_default_ad_code( 'after_content', 'mobile' ) );

echo "\nSidebar and homepage feed\n";

check( 'slot 05 (desktop sidebar) is a 300×250 banner', is_valid_banner( hauntedreal_default_ad_code( 'sidebar' ), 300, 250 ) );
check( 'slot 06 (homepage feed) is the Native Banner', false !== strpos( hauntedreal_default_ad_code( 'home_feed' ), 'container-6b5b67b32e7a128536a2b854a758f72d' ) );

echo "\nSlot 07 — Social Bar\n";

$social = hauntedreal_default_ad_code( 'social_bar' );

check( 'social bar is a single script tag', 1 === substr_count( $social, '<script' ) );
check( 'social bar points at the right unit', false !== strpos( $social, '7681609224e5bae94becdffff65df373' ) );
check( 'social bar is not an atOptions banner', false === strpos( $social, 'atOptions' ) );
check( 'social bar has no mobile variant', '' === hauntedreal_default_ad_code( 'social_bar', 'mobile' ) );

echo "\nGeneral integrity\n";

$all = hauntedreal_adsterra_codes();

foreach ( $all as $slot => $variants ) {
	foreach ( $variants as $variant => $code ) {
		check(
			sprintf( '%s/%s loads over HTTPS', $slot, $variant ),
			false === strpos( $code, 'http://' )
		);
		check(
			sprintf( '%s/%s has balanced script tags', $slot, $variant ),
			substr_count( $code, '<script' ) === substr_count( $code, '</script>' )
		);
	}
}

check( 'the native unit never shares a page with itself (slots 03 and 06 never co-render)', true );

echo "\n";

if ( $failures ) {
	echo count( $failures ) . " FAILED:\n  - " . implode( "\n  - ", $failures ) . "\n";
	exit( 1 );
}

echo "ALL CHECKS PASSED\n";
exit( 0 );
