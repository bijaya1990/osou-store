<?php
/**
 * Advertising slot system.
 *
 * Two rules govern this file:
 *
 * 1. No ad network code is ever hard-coded into a template. Templates fire
 *    a named action (`hauntedreal_header_ad`, `hauntedreal_mid_content_ad`,
 *    …); the markup and the network snippet are resolved here. Swapping
 *    Adsterra for anything else is a Customizer edit, not a rebuild.
 *
 * 2. Every slot reserves its height before anything loads. An ad that
 *    arrives 400ms late must never shove the article downwards. The
 *    reserved heights live in main.css against `.hr-ad--{slot}`; the markup
 *    below always emits the container, even when the slot is empty.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slot registry.
 *
 * `hook` is the action name a template calls. `modifier` maps to the CSS
 * class that reserves the space.
 *
 * @return array<string, array>
 */
function hauntedreal_ad_slots() {
	$slots = array(
		'header'        => array(
			'hook'     => 'hauntedreal_header_ad',
			'modifier' => 'header',
			'name'     => __( 'Slot 01 — Header / Leaderboard', 'hauntedreal' ),
			'formats'  => __( '728×90 desktop · 320×50 mobile', 'hauntedreal' ),
			'position' => __( 'Directly below the masthead and navigation.', 'hauntedreal' ),
		),
		'after_intro'   => array(
			'hook'     => 'hauntedreal_after_intro_ad',
			'modifier' => 'intro',
			'name'     => __( 'Slot 02 — Article Introduction', 'hauntedreal' ),
			'formats'  => __( 'Responsive · reserves 250px', 'hauntedreal' ),
			'position' => __( 'After the second paragraph of an article.', 'hauntedreal' ),
		),
		'mid_content'   => array(
			'hook'     => 'hauntedreal_mid_content_ad',
			'modifier' => 'mid',
			'name'     => __( 'Slot 03 — Mid Article', 'hauntedreal' ),
			'formats'  => __( '300×250 or responsive/native', 'hauntedreal' ),
			'position' => __( 'Around 45% of the way through the article body.', 'hauntedreal' ),
		),
		'after_content' => array(
			'hook'     => 'hauntedreal_after_content_ad',
			'modifier' => 'after',
			'name'     => __( 'Slot 04 — Article Bottom', 'hauntedreal' ),
			'formats'  => __( 'Responsive · reserves 250px', 'hauntedreal' ),
			'position' => __( 'After the article, before related stories.', 'hauntedreal' ),
		),
		'sidebar'       => array(
			'hook'     => 'hauntedreal_sidebar_ad',
			'modifier' => 'sidebar',
			'name'     => __( 'Slot 05 — Desktop Sidebar', 'hauntedreal' ),
			'formats'  => __( '300×250 — desktop only (1024px+)', 'hauntedreal' ),
			'position' => __( 'Top of the article sidebar. Never rendered on mobile.', 'hauntedreal' ),
		),
		'home_feed'     => array(
			'hook'     => 'hauntedreal_home_feed_ad',
			'modifier' => 'feed',
			'name'     => __( 'Slot 06 — Homepage Feed', 'hauntedreal' ),
			'formats'  => __( 'Native · reserves 250px', 'hauntedreal' ),
			'position' => __( 'Between article groups on the homepage.', 'hauntedreal' ),
		),
	);

	/**
	 * Filters the registered advertising slots.
	 *
	 * @param array $slots Slot registry.
	 */
	return apply_filters( 'hauntedreal_ad_slots', $slots );
}

/**
 * Whether a slot is switched on.
 *
 * @param string $slot Slot key.
 * @return bool
 */
function hauntedreal_ad_slot_is_enabled( $slot ) {
	$slots = hauntedreal_ad_slots();

	if ( ! isset( $slots[ $slot ] ) ) {
		return false;
	}

	$enabled = (bool) get_theme_mod( 'hauntedreal_ad_' . $slot . '_enabled', true );

	// Individual posts can opt out of in-content advertising entirely.
	if ( in_array( $slot, array( 'after_intro', 'mid_content', 'after_content' ), true )
		&& is_singular()
		&& get_post_meta( get_the_ID(), '_hauntedreal_disable_ads', true ) ) {
		$enabled = false;
	}

	/**
	 * Filters whether a given ad slot renders.
	 *
	 * @param bool   $enabled Enabled state.
	 * @param string $slot    Slot key.
	 */
	return (bool) apply_filters( 'hauntedreal_ad_slot_enabled', $enabled, $slot );
}

/**
 * The network snippet stored for a slot.
 *
 * Only users with `edit_theme_options` can write this value (the Customizer
 * enforces that), so it is intentionally output unescaped — ad tags are
 * scripts and iframes by nature.
 *
 * @param string $slot Slot key.
 * @return string
 */
function hauntedreal_get_ad_code( $slot ) {
	$code = (string) get_theme_mod( 'hauntedreal_ad_' . $slot . '_code', '' );

	/**
	 * Filters the ad code for a slot. Ad-management plugins can hook here
	 * instead of touching the Customizer.
	 *
	 * @param string $code Raw ad markup.
	 * @param string $slot Slot key.
	 */
	return trim( (string) apply_filters( 'hauntedreal_ad_code', $code, $slot ) );
}

/**
 * Whether to draw the labelled placeholder instead of live code.
 *
 * @return bool
 */
function hauntedreal_ads_show_placeholders() {
	return (bool) get_theme_mod( 'hauntedreal_ads_show_placeholders', true );
}

/**
 * Build the markup for one slot.
 *
 * @param string $slot Slot key.
 * @param array  $args Optional overrides: `class`.
 * @return string
 */
function hauntedreal_get_ad_slot( $slot, $args = array() ) {
	$slots = hauntedreal_ad_slots();

	if ( ! isset( $slots[ $slot ] ) || ! hauntedreal_ad_slot_is_enabled( $slot ) ) {
		return '';
	}

	$config = $slots[ $slot ];
	$code   = hauntedreal_get_ad_code( $slot );
	$live   = ( '' !== $code ) && ! hauntedreal_ads_show_placeholders();

	$classes = array( 'hr-ad', 'hr-ad--' . $config['modifier'] );

	if ( $live ) {
		$classes[] = 'hr-ad--live';
	}

	if ( ! empty( $args['class'] ) ) {
		$classes[] = sanitize_html_class( $args['class'] );
	}

	$inner = $live
		? $code
		: '<span>' . esc_html( $config['formats'] ) . '</span>';

	return sprintf(
		'<aside class="%1$s" aria-label="%2$s" data-hr-ad-slot="%3$s"><span class="hr-ad__label">%4$s</span><div class="hr-ad__inner">%5$s</div></aside>',
		esc_attr( implode( ' ', $classes ) ),
		esc_attr__( 'Advertisement', 'hauntedreal' ),
		esc_attr( $slot ),
		esc_html__( 'Advertisement', 'hauntedreal' ),
		$inner // phpcs:ignore WordPress.Security.EscapeOutput -- ad markup is intentionally raw.
	);
}

/**
 * Echo one slot.
 *
 * @param string $slot Slot key.
 * @param array  $args Optional overrides.
 */
function hauntedreal_ad_slot( $slot, $args = array() ) {
	echo hauntedreal_get_ad_slot( $slot, $args ); // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Wire each slot's action to its renderer.
 *
 * Templates only ever call do_action( 'hauntedreal_…_ad' ). Everything else
 * — placeholders, live code, CLS reservations — resolves here.
 */
function hauntedreal_register_ad_hooks() {
	foreach ( hauntedreal_ad_slots() as $slot => $config ) {
		add_action(
			$config['hook'],
			function () use ( $slot ) {
				hauntedreal_ad_slot( $slot );
			}
		);
	}
}
add_action( 'init', 'hauntedreal_register_ad_hooks' );

/**
 * Inject the two in-article slots into post content.
 *
 * Editors write plain articles; placement is the theme's job. We split on
 * closing paragraph tags and insert between them, never inside a block.
 *
 * @param string $content Post content.
 * @return string
 */
function hauntedreal_inject_in_content_ads( $content ) {
	if ( ! is_singular( array( 'post', 'ghost_story' ) )
		|| ! in_the_loop()
		|| ! is_main_query()
		|| is_feed()
		|| post_password_required() ) {
		return $content;
	}

	$chunks = explode( '</p>', $content );
	$total  = count( $chunks ) - 1; // Trailing chunk is whatever follows the last paragraph.

	// Short posts do not get interrupted.
	if ( $total < 5 ) {
		return $content;
	}

	/**
	 * Filters the paragraph index the introduction ad follows.
	 *
	 * @param int $after Paragraph number.
	 */
	$intro_after = (int) apply_filters( 'hauntedreal_intro_ad_paragraph', 2 );
	$mid_after   = (int) round( $total * 0.45 );

	// Keep at least two paragraphs of reading between the two units.
	if ( $mid_after <= $intro_after + 1 ) {
		$mid_after = $intro_after + 3;
	}

	$intro_ad = hauntedreal_get_ad_slot( 'after_intro' );
	$mid_ad   = hauntedreal_get_ad_slot( 'mid_content' );

	$output = '';

	foreach ( $chunks as $index => $chunk ) {
		$output .= $chunk;

		if ( $index < $total ) {
			$output .= '</p>';
		}

		$paragraph = $index + 1;

		if ( $paragraph === $intro_after ) {
			$output .= $intro_ad;
		}

		if ( $paragraph === $mid_after && $mid_after <= $total ) {
			$output .= $mid_ad;
		}
	}

	return $output;
}
add_filter( 'the_content', 'hauntedreal_inject_in_content_ads', 20 );
