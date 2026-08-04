<?php
/**
 * Renders a dual-creative ad slot plus the switcher script, for the browser
 * test in ad-switcher.js to load. Not used at runtime.
 *
 * The creatives are data: URLs that record which atOptions they saw, so the
 * browser test can prove each invoke.js read its own configuration rather
 * than a neighbour's.
 */

define( 'ABSPATH', __DIR__ );

$GLOBALS['hr_theme_mods'] = array();

function apply_filters( $tag, $value ) { return $value; }
function add_action() {}
function add_filter() {}
function __( $text ) { return $text; }
function esc_html__( $text ) { return $text; }
function esc_attr__( $text ) { return $text; }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function sanitize_html_class( $c ) { return preg_replace( '/[^A-Za-z0-9_-]/', '', $c ); }
function is_singular() { return true; }
function in_the_loop() { return true; }
function is_main_query() { return true; }
function is_feed() { return false; }
function post_password_required() { return false; }
function get_the_ID() { return 1; }
function get_post_meta() { return ''; }
function hauntedreal_default_ad_code( $slot, $variant = 'desktop' ) { return ''; }

function get_theme_mod( $key, $default = false ) {
	return array_key_exists( $key, $GLOBALS['hr_theme_mods'] )
		? $GLOBALS['hr_theme_mods'][ $key ]
		: $default;
}

require_once dirname( __DIR__, 2 ) . '/hauntedreal-child/inc/ads.php';

/**
 * An Adsterra-shaped banner snippet whose "invoke.js" records what it read.
 *
 * @param string $key    Unit key.
 * @param int    $width  Creative width.
 * @param int    $height Creative height.
 * @return string
 */
function fixture_banner( $key, $width, $height ) {
	$recorder = 'window.__seen=window.__seen||[];'
		. 'window.__seen.push({key:window.atOptions.key,w:window.atOptions.width,h:window.atOptions.height});';

	return sprintf(
		'<script type="text/javascript">
	atOptions = {
		\'key\' : \'%1$s\',
		\'format\' : \'iframe\',
		\'height\' : %3$d,
		\'width\' : %2$d,
		\'params\' : {}
	};
</script>
<script type="text/javascript" src="data:text/javascript,%4$s"></script>',
		$key,
		$width,
		$height,
		rawurlencode( $recorder )
	);
}

$GLOBALS['hr_theme_mods'] = array(
	// Slot 01 — two creatives, switched at 768px.
	'hauntedreal_ad_header_code'        => fixture_banner( 'desktop-728', 728, 90 ),
	'hauntedreal_ad_header_code_mobile' => fixture_banner( 'mobile-320', 320, 50 ),
	// A second dual-creative slot, to prove two units cannot race each other
	// over the shared atOptions global.
	'hauntedreal_ad_home_feed_code'        => fixture_banner( 'desktop-feed', 728, 90 ),
	'hauntedreal_ad_home_feed_code_mobile' => fixture_banner( 'mobile-feed', 300, 250 ),
	// A single-creative slot, which must stay inline and untouched.
	'hauntedreal_ad_after_intro_code'   => fixture_banner( 'inline-300', 300, 250 ),
);

?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ad switcher fixture</title></head>
<body>
<?php
echo hauntedreal_get_ad_slot( 'header' );
echo hauntedreal_get_ad_slot( 'after_intro' );
echo hauntedreal_get_ad_slot( 'home_feed' );
hauntedreal_print_ad_switcher();
?>
</body>
</html>
