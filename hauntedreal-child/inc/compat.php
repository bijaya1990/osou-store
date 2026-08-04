<?php
/**
 * GeneratePress compatibility.
 *
 * HauntedReal replaces every rendering template in the parent theme
 * (header, footer, index, single, page, archive, search, 404, comments,
 * sidebar), so none of GeneratePress' own markup ever reaches the page.
 * Its stylesheet and navigation scripts are therefore dead weight — about
 * 30KB of CSS and JS that would block nothing but still cost bytes, parse
 * time and a request.
 *
 * We drop them by default and leave a filter for anyone who wants them back.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether to strip GeneratePress' own front-end assets.
 *
 * Return false from the filter (or a child-of-child) to restore them.
 *
 * @return bool
 */
function hauntedreal_should_dequeue_parent_assets() {
	return (bool) apply_filters( 'hauntedreal_dequeue_generatepress_assets', true );
}

/**
 * Remove parent-theme styles and scripts we do not render markup for.
 *
 * Runs late (priority 50) so it fires after GeneratePress has enqueued.
 */
function hauntedreal_dequeue_parent_assets() {
	if ( ! hauntedreal_should_dequeue_parent_assets() ) {
		return;
	}

	$styles = array(
		'generate-style',        // main.css + all Customizer inline CSS
		'generate-style-grid',
		'generate-mobile-style',
		'generate-font-icons',
		'generate-widget-areas',
		'generate-blog',
	);

	foreach ( $styles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}

	$scripts = array(
		'generate-classic-menu-bg-images',
		'generate-menu',
		'generate-dropdown-click',
		'generate-navigation-search',
		'generate-back-to-top',
		'generate-offside',
		'generate-a11y',
	);

	foreach ( $scripts as $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'hauntedreal_dequeue_parent_assets', 50 );

/**
 * GeneratePress prints a block of dynamic CSS in the head on some setups.
 * With our own design system in charge it only adds bytes and specificity
 * fights.
 */
function hauntedreal_disable_parent_dynamic_css() {
	if ( ! hauntedreal_should_dequeue_parent_assets() ) {
		return;
	}

	remove_action( 'wp_head', 'generate_do_dynamic_css', 50 );
	remove_action( 'wp_enqueue_scripts', 'generate_enqueue_dynamic_css', 50 );
}
add_action( 'wp_head', 'hauntedreal_disable_parent_dynamic_css', 1 );

/**
 * GeneratePress Customizer panels control markup this theme does not output
 * (its header layouts, sidebar layouts, typography). Leaving them visible
 * invites edits that silently do nothing.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function hauntedreal_trim_parent_customizer( $wp_customize ) {
	if ( ! hauntedreal_should_dequeue_parent_assets() ) {
		return;
	}

	foreach ( array( 'generate_layout_panel', 'generate_colors_panel', 'generate_typography_panel' ) as $panel ) {
		$wp_customize->remove_panel( $panel );
	}

	foreach ( array( 'generate_colors_section', 'generate_backgrounds_section', 'generate_layout_section' ) as $section ) {
		$wp_customize->remove_section( $section );
	}
}
add_action( 'customize_register', 'hauntedreal_trim_parent_customizer', 999 );
