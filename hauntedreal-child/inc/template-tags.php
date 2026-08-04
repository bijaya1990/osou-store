<?php
/**
 * Template tags.
 *
 * The presentation helpers every template leans on. Keeping them here means
 * a template file stays readable as markup rather than logic.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Editorial vs community badge.
 *
 * The clearest signal on the site: a HauntedReal investigation and a reader's
 * account of their grandmother's house must never look interchangeable.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function hauntedreal_get_post_badge( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( 'ghost_story' === get_post_type( $post_id ) ) {
		return '<span class="hr-badge hr-badge--community">' . esc_html__( 'Community Experience', 'hauntedreal' ) . '</span>';
	}

	$primary = hauntedreal_get_primary_term( $post_id, 'category' );
	$label   = $primary ? $primary->name : __( 'Report', 'hauntedreal' );

	return '<span class="hr-badge hr-badge--editorial">' . esc_html( $label ) . '</span>';
}

/**
 * Echo the badge.
 *
 * @param int|null $post_id Post ID.
 */
function hauntedreal_post_badge( $post_id = null ) {
	echo wp_kses_post( hauntedreal_get_post_badge( $post_id ) );
}

/**
 * Verification status pill for community experiences.
 *
 * A paranormal publication earns trust by being explicit about what it has
 * and has not checked.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function hauntedreal_get_verification_badge( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$status  = get_post_meta( $post_id, '_hauntedreal_status', true );

	$map = array(
		'verified'      => array( 'hr-badge--verified', __( 'Details Verified', 'hauntedreal' ) ),
		'investigating' => array( 'hr-badge--unverified', __( 'Under Review', 'hauntedreal' ) ),
		'unverified'    => array( 'hr-badge--unverified', __( 'Unverified Account', 'hauntedreal' ) ),
	);

	if ( ! isset( $map[ $status ] ) ) {
		return '';
	}

	return sprintf(
		'<span class="hr-badge %1$s">%2$s</span>',
		esc_attr( $map[ $status ][0] ),
		esc_html( $map[ $status ][1] )
	);
}

/**
 * The first term of a taxonomy, used as the "primary" one.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy name.
 * @return WP_Term|null
 */
function hauntedreal_get_primary_term( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}

	return $terms[0];
}

/**
 * Estimated reading time in whole minutes.
 *
 * @param int|null $post_id Post ID.
 * @return int
 */
function hauntedreal_get_reading_time( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( $content ) ) );

	return max( 1, (int) ceil( $words / 225 ) );
}

/**
 * The meta line beneath a headline: byline · location · date · read time.
 *
 * @param array $args {
 *     @type bool $author   Show the byline.
 *     @type bool $date     Show the publication date.
 *     @type bool $location Show "City, State".
 *     @type bool $reading  Show reading time.
 * }
 */
function hauntedreal_entry_meta( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'author'   => true,
		'date'     => true,
		'location' => true,
		'reading'  => false,
	) );

	$parts = array();

	if ( $args['author'] ) {
		$parts[] = sprintf(
			'<span class="hr-meta__author">%s <a href="%s" rel="author">%s</a></span>',
			esc_html_x( 'By', 'byline', 'hauntedreal' ),
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author() )
		);
	}

	if ( $args['location'] ) {
		$location = hauntedreal_get_location();

		if ( $location ) {
			$parts[] = '<span class="hr-meta__loc">' . esc_html( $location ) . '</span>';
		}
	}

	if ( $args['date'] ) {
		$parts[] = sprintf(
			'<time datetime="%s">%s</time>',
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);
	}

	if ( $args['reading'] ) {
		$minutes = hauntedreal_get_reading_time();
		$parts[] = sprintf(
			/* translators: %d: number of minutes. */
			'<span class="hr-meta__read">' . esc_html( _n( '%d min read', '%d min read', $minutes, 'hauntedreal' ) ) . '</span>',
			$minutes
		);
	}

	if ( ! $parts ) {
		return;
	}

	echo '<div class="hr-meta">';
	echo implode( '<span class="hr-meta__sep" aria-hidden="true">·</span>', $parts ); // phpcs:ignore WordPress.Security.EscapeOutput -- parts escaped above.
	echo '</div>';
}

/**
 * A media block with its aspect ratio reserved.
 *
 * Always outputs a box, image or not, so a thumbnail-less post does not
 * collapse the grid or shift its neighbours.
 *
 * @param array $args {
 *     @type string $size    Registered image size.
 *     @type string $class   Wrapper class.
 *     @type bool   $lcp     Treat as the Largest Contentful Paint candidate.
 *     @type bool   $link    Wrap in a permalink.
 * }
 */
function hauntedreal_post_media( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'size'  => 'hauntedreal-card',
		'class' => 'hr-card__media',
		'lcp'   => false,
		'link'  => false,
	) );

	$attr = array(
		'class'    => 'hr-media__img',
		'decoding' => 'async',
	);

	if ( $args['lcp'] ) {
		// The hero image must not wait in the lazy-load queue.
		$attr['loading']       = 'eager';
		$attr['fetchpriority'] = 'high';
	} else {
		$attr['loading'] = 'lazy';
	}

	echo '<div class="' . esc_attr( $args['class'] ) . '">';

	if ( $args['link'] ) {
		echo '<a href="' . esc_url( get_permalink() ) . '" tabindex="-1" aria-hidden="true">';
	}

	if ( has_post_thumbnail() ) {
		the_post_thumbnail( $args['size'], $attr );
	} else {
		echo '<span class="hr-media__empty" aria-hidden="true">' . esc_html__( 'HauntedReal', 'hauntedreal' ) . '</span>';
	}

	if ( $args['link'] ) {
		echo '</a>';
	}

	echo '</div>';
}

/**
 * Breadcrumb trail.
 *
 * Plain, semantic, and mirrored by BreadcrumbList JSON-LD in inc/schema.php.
 */
function hauntedreal_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$crumbs = array(
		array(
			'url'   => home_url( '/' ),
			'label' => __( 'Home', 'hauntedreal' ),
		),
	);

	if ( is_singular( 'ghost_story' ) ) {
		$crumbs[] = array(
			'url'   => get_post_type_archive_link( 'ghost_story' ),
			'label' => __( 'Ghost Stories', 'hauntedreal' ),
		);
	} elseif ( is_singular( 'post' ) ) {
		$category = hauntedreal_get_primary_term( get_the_ID(), 'category' );

		if ( $category ) {
			$crumbs[] = array(
				'url'   => get_category_link( $category->term_id ),
				'label' => $category->name,
			);
		}
	} elseif ( is_tax( 'us_state' ) ) {
		$crumbs[] = array(
			'url'   => home_url( '/haunted-america/' ),
			'label' => __( 'Haunted America', 'hauntedreal' ),
		);
	}

	if ( is_singular() ) {
		$crumbs[] = array(
			'url'   => '',
			'label' => get_the_title(),
		);
	} elseif ( is_archive() ) {
		$crumbs[] = array(
			'url'   => '',
			'label' => wp_strip_all_tags( get_the_archive_title() ),
		);
	} elseif ( is_search() ) {
		$crumbs[] = array(
			'url'   => '',
			/* translators: %s: search query. */
			'label' => sprintf( __( 'Search: %s', 'hauntedreal' ), get_search_query() ),
		);
	} elseif ( is_404() ) {
		$crumbs[] = array(
			'url'   => '',
			'label' => __( 'Page Not Found', 'hauntedreal' ),
		);
	}

	echo '<nav aria-label="' . esc_attr__( 'Breadcrumb', 'hauntedreal' ) . '"><ol class="hr-breadcrumb">';

	foreach ( $crumbs as $crumb ) {
		echo '<li>';

		if ( $crumb['url'] ) {
			echo '<a href="' . esc_url( $crumb['url'] ) . '">' . esc_html( $crumb['label'] ) . '</a>';
		} else {
			echo '<span aria-current="page">' . esc_html( wp_trim_words( $crumb['label'], 8, '…' ) ) . '</span>';
		}

		echo '</li>';
	}

	echo '</ol></nav>';
}

/**
 * Archive pagination.
 */
function hauntedreal_pagination() {
	// 'plain' returns bare anchors, which is exactly what the flex row wants.
	$links = paginate_links( array(
		'type'      => 'plain',
		'mid_size'  => 1,
		'prev_text' => __( '&larr; Newer', 'hauntedreal' ),
		'next_text' => __( 'Older &rarr;', 'hauntedreal' ),
	) );

	if ( ! $links ) {
		return;
	}

	printf(
		'<nav class="hr-pagination" aria-label="%s"><div class="nav-links">%s</div></nav>',
		esc_attr__( 'Posts navigation', 'hauntedreal' ),
		wp_kses_post( $links )
	);
}

/**
 * Related stories.
 *
 * Editorial posts relate by category; ghost stories relate by state, because
 * "another experience from Savannah" is the connection a reader wants.
 *
 * @param int $count How many to show.
 * @return WP_Query|null
 */
function hauntedreal_get_related_query( $count = 3 ) {
	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );

	$args = array(
		'post_type'           => $post_type,
		'posts_per_page'      => $count,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'post_status'         => 'publish',
	);

	if ( 'ghost_story' === $post_type ) {
		$state = hauntedreal_get_primary_term( $post_id, 'us_state' );

		if ( $state ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'us_state',
					'field'    => 'term_id',
					'terms'    => $state->term_id,
				),
			);
		}
	} else {
		$category = hauntedreal_get_primary_term( $post_id, 'category' );

		if ( $category ) {
			$args['cat'] = $category->term_id;
		}
	}

	$query = new WP_Query( $args );

	// Fall back to recent posts of the same type rather than showing nothing.
	if ( ! $query->have_posts() ) {
		unset( $args['tax_query'], $args['cat'] );
		$query = new WP_Query( $args );
	}

	return $query->have_posts() ? $query : null;
}

/**
 * The masthead date line — small, but it reads like a publication.
 *
 * @return string
 */
function hauntedreal_current_date() {
	return wp_date( 'l, F j, Y' );
}

/**
 * URL of the ghost story submission page, if one has been assigned.
 *
 * @return string
 */
function hauntedreal_submit_url() {
	$page_id = (int) get_theme_mod( 'hauntedreal_submit_page', 0 );

	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		return get_permalink( $page_id );
	}

	// Fall back to a page using the submission template.
	$pages = get_posts( array(
		'post_type'   => 'page',
		'numberposts' => 1,
		'fields'      => 'ids',
		'meta_key'    => '_wp_page_template',
		'meta_value'  => 'page-templates/submit-ghost-story.php',
	) );

	return $pages ? get_permalink( $pages[0] ) : '';
}

/**
 * Render a card. Thin wrapper so templates read declaratively.
 *
 * @param string $variant One of: default, ghost, row.
 */
function hauntedreal_card( $variant = 'default' ) {
	get_template_part( 'template-parts/card', $variant );
}

/**
 * Menu shown until an editor assigns one in Appearance → Menus.
 *
 * An opinionated starting structure beats the alphabetical page list
 * WordPress otherwise falls back to.
 */
function hauntedreal_default_menu() {
	$items = array(
		home_url( '/' )                             => __( 'Home', 'hauntedreal' ),
		home_url( '/haunted-america/' )             => __( 'Haunted America', 'hauntedreal' ),
		get_post_type_archive_link( 'ghost_story' ) => __( 'Ghost Stories', 'hauntedreal' ),
	);

	echo '<ul id="hr-menu-primary">';

	foreach ( $items as $url => $label ) {
		if ( ! $url ) {
			continue;
		}

		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}

	$submit = hauntedreal_submit_url();

	if ( $submit ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $submit ), esc_html__( 'Share Your Story', 'hauntedreal' ) );
	}

	echo '</ul>';
}

/**
 * Section heading with the ember accent on a highlighted word.
 *
 * @param string $title Heading text.
 * @param string $url   Optional "view all" URL.
 * @param string $more  Optional "view all" label.
 */
function hauntedreal_section_head( $title, $url = '', $more = '' ) {
	echo '<div class="hr-section__head">';
	echo '<h2 class="hr-section__title">' . wp_kses_post( $title ) . '</h2>';

	if ( $url ) {
		printf(
			'<a class="hr-section__more" href="%s">%s</a>',
			esc_url( $url ),
			esc_html( $more ? $more : __( 'View all', 'hauntedreal' ) )
		);
	}

	echo '</div>';
}
