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
		'social_bar'    => array(
			'hook'     => 'hauntedreal_social_bar_ad',
			'modifier' => 'social',
			'name'     => __( 'Slot 07 — Social Bar (site-wide overlay)', 'hauntedreal' ),
			'formats'  => __( 'Overlay · reserves no space', 'hauntedreal' ),
			'position' => __( 'Printed in the footer on every page. Positions itself.', 'hauntedreal' ),
			/*
			 * Overlay formats float above the page and never occupy layout, so
			 * they get no wrapper, no label and no reserved height. Chrome that
			 * reserves space for something which does not take any would just
			 * punch a hole in the design.
			 */
			'chrome'   => false,
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
function hauntedreal_get_ad_code( $slot, $variant = 'desktop' ) {
	$key = 'mobile' === $variant
		? 'hauntedreal_ad_' . $slot . '_code_mobile'
		: 'hauntedreal_ad_' . $slot . '_code';

	$code = (string) get_theme_mod( $key, hauntedreal_default_ad_code( $slot, $variant ) );

	/**
	 * Filters the ad code for a slot. Ad-management plugins can hook here
	 * instead of touching the Customizer.
	 *
	 * @param string $code    Raw ad markup.
	 * @param string $slot    Slot key.
	 * @param string $variant `desktop` or `mobile`.
	 */
	return trim( (string) apply_filters( 'hauntedreal_ad_code', $code, $slot, $variant ) );
}

/**
 * Preview mode: draw labelled placeholders instead of running live ad code.
 *
 * Defaults to off. A live site should serve whatever has been pasted into a
 * slot without anyone having to remember to untick a box first.
 *
 * @return bool
 */
function hauntedreal_ads_show_placeholders() {
	return (bool) get_theme_mod( 'hauntedreal_ads_show_placeholders', false );
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

	$config  = $slots[ $slot ];
	$code    = hauntedreal_get_ad_code( $slot );
	$preview = hauntedreal_ads_show_placeholders();
	$chrome  = ! isset( $config['chrome'] ) || $config['chrome'];

	/*
	 * Overlay formats — Adsterra's Social Bar and anything like it. The
	 * network's own script positions the unit; all we do is print it.
	 */
	if ( ! $chrome ) {
		return $preview ? '' : $code;
	}

	/*
	 * An unsold slot on a live site renders nothing at all. Reserving 250px
	 * for an ad that is never coming is just a hole in the page — the
	 * reservation exists to stop *loading* ads from shifting content, not to
	 * hold space open permanently.
	 */
	if ( '' === $code && ! $preview ) {
		return '';
	}

	$live   = ( '' !== $code ) && ! $preview;
	$mobile = $live ? hauntedreal_get_ad_code( $slot, 'mobile' ) : '';

	// A slot with two creatives picks one at runtime; see the note on
	// hauntedreal_print_ad_switcher() for why this cannot be done in CSS.
	$responsive = ( '' !== $mobile ) && ( $mobile !== $code );

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

	$inner_attr = '';

	if ( $responsive ) {
		/*
		 * Hand both creatives to the client and let it insert exactly one.
		 * The element starts empty so neither creative loads twice.
		 */
		hauntedreal_needs_ad_switcher( true );

		$inner_attr = sprintf(
			' data-hr-ad-desktop="%s" data-hr-ad-mobile="%s" data-hr-ad-breakpoint="768"',
			esc_attr( $code ),
			esc_attr( $mobile )
		);
		$inner = '';
	}

	return sprintf(
		'<aside class="%1$s" aria-label="%2$s" data-hr-ad-slot="%3$s"><span class="hr-ad__label">%4$s</span><div class="hr-ad__inner"%5$s>%6$s</div></aside>',
		esc_attr( implode( ' ', $classes ) ),
		esc_attr__( 'Advertisement', 'hauntedreal' ),
		esc_attr( $slot ),
		esc_html__( 'Advertisement', 'hauntedreal' ),
		$inner_attr, // Escaped above.
		$inner // phpcs:ignore WordPress.Security.EscapeOutput -- ad markup is intentionally raw.
	);
}

/**
 * Track whether any slot on this page needs the runtime creative switcher.
 *
 * @param bool|null $set Pass true to flag it.
 * @return bool
 */
function hauntedreal_needs_ad_switcher( $set = null ) {
	static $needed = false;

	if ( true === $set ) {
		$needed = true;
	}

	return $needed;
}

/**
 * Print the creative switcher, only on pages that contain a dual-creative slot.
 *
 * Why this cannot be CSS: an Adsterra banner is a fixed-size iframe, so a
 * leaderboard needs a 728×90 creative on desktop and a 320×50 on a phone.
 * Rendering both and hiding one with a media query still loads both — the
 * hidden one burns an impression no reader can ever see. So exactly one is
 * inserted, chosen once, at load.
 *
 * Insertion is serial: Adsterra's banner snippet sets a single global
 * (`atOptions`) that its invoke.js reads when it executes, so two units
 * inserted concurrently would race and could swap each other's dimensions.
 * Each unit therefore waits for the previous script to finish loading.
 */
function hauntedreal_print_ad_switcher() {
	if ( ! hauntedreal_needs_ad_switcher() ) {
		return;
	}
	?>
	<script id="hauntedreal-ad-switcher">
	(function () {
		var slots = document.querySelectorAll( '.hr-ad__inner[data-hr-ad-desktop]' );

		if ( ! slots.length ) {
			return;
		}

		var queue = [];

		Array.prototype.forEach.call( slots, function ( slot ) {
			var wide = window.matchMedia(
				'(min-width: ' + ( slot.getAttribute( 'data-hr-ad-breakpoint' ) || 768 ) + 'px)'
			).matches;
			var markup = slot.getAttribute( wide ? 'data-hr-ad-desktop' : 'data-hr-ad-mobile' );

			if ( markup ) {
				queue.push( { el: slot, html: markup } );
			}
		} );

		function run( index ) {
			if ( index >= queue.length ) {
				return;
			}

			var item = queue[ index ];
			var holder = document.createElement( 'template' );
			holder.innerHTML = item.html;

			var nodes = Array.prototype.slice.call( holder.content.childNodes );
			var pending = 0;
			var advanced = false;

			function next() {
				if ( ! advanced ) {
					advanced = true;
					run( index + 1 );
				}
			}

			nodes.forEach( function ( node ) {
				if ( node.nodeName !== 'SCRIPT' ) {
					item.el.appendChild( node.cloneNode( true ) );
					return;
				}

				var script = document.createElement( 'script' );

				Array.prototype.forEach.call( node.attributes, function ( attr ) {
					script.setAttribute( attr.name, attr.value );
				} );

				if ( node.src ) {
					// External: the next unit must not set atOptions until
					// this one has read it.
					pending++;
					script.addEventListener( 'load', function () {
						if ( --pending === 0 ) { next(); }
					} );
					script.addEventListener( 'error', function () {
						if ( --pending === 0 ) { next(); }
					} );
				} else {
					// Inline: runs synchronously the moment it is appended.
					script.text = node.textContent;
				}

				item.el.appendChild( script );
			} );

			if ( pending === 0 ) {
				next();
			}
		}

		if ( document.readyState === 'loading' ) {
			// Wait for the parser-inserted ad scripts to finish first.
			document.addEventListener( 'DOMContentLoaded', function () { run( 0 ); } );
		} else {
			run( 0 );
		}
	}());
	</script>
	<?php
}
add_action( 'wp_footer', 'hauntedreal_print_ad_switcher', 5 );

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
 * Print the site-wide overlay slot.
 *
 * In the footer rather than the head, so a third-party overlay script never
 * competes with the article for the critical path.
 */
function hauntedreal_print_social_bar() {
	do_action( 'hauntedreal_social_bar_ad' );
}
add_action( 'wp_footer', 'hauntedreal_print_social_bar', 20 );

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
	 * Filters the paragraph the introduction ad follows.
	 *
	 * Zero places it above the first paragraph, at the very top of the article.
	 *
	 * @param int $after Paragraph number.
	 */
	$intro_after = (int) apply_filters(
		'hauntedreal_intro_ad_paragraph',
		(int) get_theme_mod( 'hauntedreal_intro_ad_paragraph', 2 )
	);
	$intro_after = max( 0, $intro_after );

	$mid_after = (int) round( $total * 0.45 );

	// Keep at least two paragraphs of reading between the two units.
	if ( $mid_after <= $intro_after + 1 ) {
		$mid_after = $intro_after + 3;
	}

	$intro_ad = hauntedreal_get_ad_slot( 'after_intro' );
	$mid_ad   = hauntedreal_get_ad_slot( 'mid_content' );

	// Position 0 means the unit opens the article rather than following a
	// paragraph, so it is prepended before the loop runs.
	$output = ( 0 === $intro_after ) ? $intro_ad : '';

	foreach ( $chunks as $index => $chunk ) {
		$output .= $chunk;

		if ( $index < $total ) {
			$output .= '</p>';
		}

		$paragraph = $index + 1;

		if ( $intro_after > 0 && $paragraph === $intro_after ) {
			$output .= $intro_ad;
		}

		if ( $paragraph === $mid_after && $mid_after <= $total ) {
			$output .= $mid_ad;
		}
	}

	return $output;
}
add_filter( 'the_content', 'hauntedreal_inject_in_content_ads', 20 );
