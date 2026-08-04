<?php
/**
 * Performance and Core Web Vitals.
 *
 * Everything here is subtraction. The fastest asset is the one that never
 * gets requested.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Drop WordPress' emoji detection script and its inline styles.
 *
 * ~12KB of JavaScript that runs on every page load to polyfill emoji on
 * browsers that have supported them natively for a decade.
 */
function hauntedreal_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'emoji_svg_url', '__return_false' );
	add_filter( 'tiny_mce_plugins', function ( $plugins ) {
		return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
	} );
}
add_action( 'init', 'hauntedreal_disable_emojis' );

/**
 * Remove head clutter that no modern site consumes.
 */
function hauntedreal_clean_head() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
}
add_action( 'init', 'hauntedreal_clean_head' );

/**
 * The block editor's front-end stylesheets are only worth loading if the
 * content actually uses blocks that need them.
 *
 * wp-block-library is ~90KB uncompressed. We keep it (posts do use core
 * blocks) but drop the theme.json duotone/global-styles payload and the
 * classic-theme-styles shim, which our design system already covers.
 */
function hauntedreal_trim_block_assets() {
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );

	// wp-embed only powers embedding *this* site elsewhere.
	wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_enqueue_scripts', 'hauntedreal_trim_block_assets', 100 );

/**
 * jQuery Migrate is a compatibility shim for pre-3.0 jQuery code.
 *
 * @param WP_Scripts $scripts Scripts registry.
 */
function hauntedreal_remove_jquery_migrate( $scripts ) {
	if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
		return;
	}

	$scripts->registered['jquery']->deps = array_diff(
		$scripts->registered['jquery']->deps,
		array( 'jquery-migrate' )
	);
}
add_action( 'wp_default_scripts', 'hauntedreal_remove_jquery_migrate' );

/**
 * Only the very first image in the document may skip lazy loading.
 *
 * WordPress defaults this threshold to 3, which means three images race the
 * LCP element for bandwidth. One is the right number.
 *
 * @return int
 */
function hauntedreal_lazy_threshold() {
	return 1;
}
add_filter( 'wp_omit_loading_attr_threshold', 'hauntedreal_lazy_threshold' );

/**
 * Allow modern image formats to be uploaded to the media library.
 *
 * WebP is supported by core; AVIF support depends on the server's imagick
 * or GD build, so we allow the upload and let the editor decide.
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function hauntedreal_allow_modern_images( $mimes ) {
	$mimes['webp'] = 'image/webp';
	$mimes['avif'] = 'image/avif';

	return $mimes;
}
add_filter( 'upload_mimes', 'hauntedreal_allow_modern_images' );

/**
 * Serve responsive images at sensible widths.
 *
 * The article measure caps at ~736px and cards at ~400px, so the browser
 * never needs a 2560px source on a phone.
 *
 * @param string $sizes Existing sizes attribute.
 * @param array  $size  Image size data.
 * @return string
 */
function hauntedreal_image_sizes_attr( $sizes, $size ) {
	if ( is_singular() ) {
		return '(max-width: 767px) 100vw, (max-width: 1023px) 90vw, 800px';
	}

	return '(max-width: 767px) 100vw, (max-width: 1023px) 50vw, 380px';
}
add_filter( 'wp_calculate_image_sizes', 'hauntedreal_image_sizes_attr', 10, 2 );

/**
 * Big images generate a -scaled original at 2560px by default. For a
 * publication that never displays anything wider than 1600px, that is a
 * wasted derivative.
 *
 * @return int
 */
function hauntedreal_big_image_threshold() {
	return 1920;
}
add_filter( 'big_image_size_threshold', 'hauntedreal_big_image_threshold' );

/**
 * Slightly higher JPEG quality than core's 82 — dark, grainy photography
 * shows banding at low quality settings.
 *
 * @return int
 */
function hauntedreal_image_quality() {
	return 86;
}
add_filter( 'wp_editor_set_quality', 'hauntedreal_image_quality' );

/**
 * Preconnect to the avatar host only when avatars are actually enabled.
 *
 * @param array  $hints Existing hints.
 * @param string $relation_type Hint type.
 * @return array
 */
function hauntedreal_resource_hints( $hints, $relation_type ) {
	if ( 'preconnect' === $relation_type && get_option( 'show_avatars' ) ) {
		$hints[] = array(
			'href'        => 'https://secure.gravatar.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $hints;
}
add_filter( 'wp_resource_hints', 'hauntedreal_resource_hints', 10, 2 );

/**
 * Strip the `type` attribute from script tags. Saves bytes and is valid
 * HTML5 for classic scripts.
 *
 * @param string $tag Script tag.
 * @return string
 */
function hauntedreal_clean_script_tag( $tag ) {
	return str_replace( " type='text/javascript'", '', $tag );
}
add_filter( 'script_loader_tag', 'hauntedreal_clean_script_tag' );
