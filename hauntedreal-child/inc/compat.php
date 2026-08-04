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
 * Dequeue only — never deregister.
 *
 * GeneratePress enqueues the child theme's own style.css under the handle
 * `generate-child`, declaring `generate-style` as a dependency. Deregistering
 * `generate-style` therefore leaves that dependency dangling, which WordPress
 * 6.9.1 and later report as a _doing_it_wrong notice. Dequeuing leaves the
 * registration intact so every dependency still resolves; the handles simply
 * never reach the queue.
 *
 * Runs at priority 999 so it fires after everything else has enqueued,
 * whichever priority the parent theme or a plugin used. Styles are printed
 * later still (wp_head priority 8), so nothing has gone out by then.
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
		// The parent's copy of our own style.css. We enqueue that ourselves as
		// `hauntedreal-style`, so letting this one through would fetch the same
		// file twice.
		'generate-child',
	);

	foreach ( $styles as $handle ) {
		wp_dequeue_style( $handle );
	}

	/*
	 * A dequeued style is still pulled back in if something enqueued depends on
	 * it. Severing generate-child's dependency means that even if a plugin
	 * re-enqueues it, GeneratePress' stylesheet does not ride along.
	 */
	$registered = wp_styles()->query( 'generate-child', 'registered' );

	if ( $registered ) {
		$registered->deps = array();
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
	}
}
add_action( 'wp_enqueue_scripts', 'hauntedreal_dequeue_parent_assets', 999 );

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
