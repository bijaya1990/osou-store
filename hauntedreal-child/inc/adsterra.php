<?php
/**
 * Adsterra ad unit codes.
 *
 * ── This is the only file in the theme that contains ad network code. ──
 *
 * The snippets below are *defaults*. Anything pasted into
 * Customize → HauntedReal → Advertising Slots overrides them, so a slot can
 * be changed or a whole network swapped without editing PHP. They live here
 * rather than in a template so that switching away from Adsterra later means
 * clearing seven fields, not rebuilding templates.
 *
 * To retire Adsterra entirely: empty the arrays below, or drop the
 * `require_once` for this file from functions.php.
 *
 * A note on `atOptions`: Adsterra's banner snippet sets a single global
 * variable and then loads invoke.js, which reads it. That only works when the
 * pairs run in document order, which is why banner slots are printed inline
 * and never deferred. Do not let a "delay JavaScript" optimiser touch them —
 * see the README.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default ad code per slot, per variant.
 *
 * `mobile` is optional and only needed where the desktop creative physically
 * cannot fit a phone — in practice, the leaderboard.
 *
 * @return array<string, array<string, string>>
 */
function hauntedreal_adsterra_codes() {
	$banner_728x90 = <<<'HTML'
<script type="text/javascript">
	atOptions = {
		'key' : '0c1fe4b62102ab00c1935841b3908b40',
		'format' : 'iframe',
		'height' : 90,
		'width' : 728,
		'params' : {}
	};
</script>
<script type="text/javascript" src="https://windowthrilling.com/0c1fe4b62102ab00c1935841b3908b40/invoke.js"></script>
HTML;

	$banner_320x50 = <<<'HTML'
<script type="text/javascript">
	atOptions = {
		'key' : '7540bd4eaee43bf6f48734bca7b810bd',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="https://windowthrilling.com/7540bd4eaee43bf6f48734bca7b810bd/invoke.js"></script>
HTML;

	$banner_300x250 = <<<'HTML'
<script type="text/javascript">
	atOptions = {
		'key' : 'b2b45c0322c045021d5af31da8b302db',
		'format' : 'iframe',
		'height' : 250,
		'width' : 300,
		'params' : {}
	};
</script>
<script type="text/javascript" src="https://windowthrilling.com/b2b45c0322c045021d5af31da8b302db/invoke.js"></script>
HTML;

	$native = <<<'HTML'
<script async="async" data-cfasync="false" src="https://windowthrilling.com/6b5b67b32e7a128536a2b854a758f72d/invoke.js"></script>
<div id="container-6b5b67b32e7a128536a2b854a758f72d"></div>
HTML;

	$social_bar = <<<'HTML'
<script type="text/javascript" src="https://windowthrilling.com/76/81/60/7681609224e5bae94becdffff65df373.js"></script>
HTML;

	$codes = array(
		// Slot 01 — leaderboard. The one slot that genuinely needs two
		// creatives: 728×90 will not fit a 320px viewport.
		'header'        => array(
			'desktop' => $banner_728x90,
			'mobile'  => $banner_320x50,
		),

		// Slot 02 — opens the article. 300×250 fits every viewport down to
		// 320px, so no mobile variant is needed.
		'after_intro'   => array( 'desktop' => $banner_300x250 ),

		// Slot 03 — mid article. Native, as requested: it inherits the
		// surrounding type and reads as part of the page.
		'mid_content'   => array( 'desktop' => $native ),

		// Slot 04 — closes the article, before related stories.
		'after_content' => array( 'desktop' => $banner_300x250 ),

		// Slot 05 — desktop sidebar. Never rendered below 1024px.
		'sidebar'       => array( 'desktop' => $banner_300x250 ),

		// Slot 06 — homepage feed. The native unit again; it never appears on
		// the same page as slot 03, so the container id cannot collide.
		'home_feed'     => array( 'desktop' => $native ),

		// Slot 07 — site-wide overlay. Positions itself, reserves nothing.
		'social_bar'    => array( 'desktop' => $social_bar ),
	);

	/**
	 * Filters the built-in ad codes.
	 *
	 * @param array $codes Slot key => variant => markup.
	 */
	return apply_filters( 'hauntedreal_adsterra_codes', $codes );
}

/**
 * The default snippet for a slot and variant.
 *
 * @param string $slot    Slot key.
 * @param string $variant `desktop` or `mobile`.
 * @return string
 */
function hauntedreal_default_ad_code( $slot, $variant = 'desktop' ) {
	$codes = hauntedreal_adsterra_codes();

	return isset( $codes[ $slot ][ $variant ] ) ? $codes[ $slot ][ $variant ] : '';
}
