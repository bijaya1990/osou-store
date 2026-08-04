<?php
/**
 * Structured data.
 *
 * Search engines need to be able to tell a HauntedReal investigation from a
 * reader's submitted account. Editorial posts are NewsArticle; community
 * experiences are a plain Article carrying an explicit contentLocation and
 * no publisher-authored claim of fact.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print the JSON-LD graph for the current view.
 */
function hauntedreal_print_schema() {
	$graph = array();

	$publisher = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	$logo_id = get_theme_mod( 'custom_logo' );

	if ( $logo_id ) {
		$logo = wp_get_attachment_image_src( $logo_id, 'full' );

		if ( $logo ) {
			$publisher['logo'] = array(
				'@type'  => 'ImageObject',
				'url'    => $logo[0],
				'width'  => $logo[1],
				'height' => $logo[2],
			);
		}
	}

	$graph[] = $publisher;

	if ( is_front_page() || is_home() ) {
		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'url'             => home_url( '/' ),
			'name'            => get_bloginfo( 'name' ),
			'description'     => get_bloginfo( 'description' ),
			'inLanguage'      => get_bloginfo( 'language' ),
			'publisher'       => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	if ( is_singular( array( 'post', 'ghost_story' ) ) ) {
		$graph[] = hauntedreal_article_schema();
	}

	if ( is_author() ) {
		$author = get_queried_object();

		if ( $author instanceof WP_User ) {
			$graph[] = array(
				'@type'       => 'ProfilePage',
				'@id'         => get_author_posts_url( $author->ID ) . '#profile',
				'mainEntity'  => array(
					'@type'       => 'Person',
					'name'        => $author->display_name,
					'description' => get_the_author_meta( 'description', $author->ID ),
					'url'         => get_author_posts_url( $author->ID ),
				),
			);
		}
	}

	$breadcrumbs = hauntedreal_breadcrumb_schema();

	if ( $breadcrumbs ) {
		$graph[] = $breadcrumbs;
	}

	if ( ! $graph ) {
		return;
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		)
	);
}
add_action( 'wp_head', 'hauntedreal_print_schema', 20 );

/**
 * Article node for the current singular view.
 *
 * @return array
 */
function hauntedreal_article_schema() {
	$post_id = get_the_ID();
	$is_ghost = 'ghost_story' === get_post_type( $post_id );

	$schema = array(
		'@type'            => $is_ghost ? 'Article' : 'NewsArticle',
		'@id'              => get_permalink() . '#article',
		'headline'         => wp_strip_all_tags( get_the_title() ),
		'description'      => wp_strip_all_tags( get_the_excerpt() ),
		'datePublished'    => get_the_date( DATE_W3C ),
		'dateModified'     => get_the_modified_date( DATE_W3C ),
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => get_permalink(),
		),
		'publisher'        => array( '@id' => home_url( '/#organization' ) ),
		'inLanguage'       => get_bloginfo( 'language' ),
	);

	if ( $is_ghost ) {
		$witness = get_post_meta( $post_id, '_hauntedreal_witness', true );

		$schema['author'] = array(
			'@type' => 'Person',
			'name'  => $witness ? $witness : __( 'HauntedReal Community', 'hauntedreal' ),
		);
		// An unverified first-person account is not reported news.
		$schema['genre'] = __( 'Community-submitted paranormal experience', 'hauntedreal' );
	} else {
		$schema['author'] = array(
			'@type' => 'Person',
			'name'  => get_the_author(),
			'url'   => get_author_posts_url( get_the_author_meta( 'ID' ) ),
		);
	}

	if ( has_post_thumbnail() ) {
		$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'hauntedreal-hero' );

		if ( $image ) {
			$schema['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $image[0],
				'width'  => $image[1],
				'height' => $image[2],
			);
		}
	}

	$city  = get_post_meta( $post_id, '_hauntedreal_city', true );
	$state = get_post_meta( $post_id, '_hauntedreal_state', true );
	$place = get_post_meta( $post_id, '_hauntedreal_place', true );

	if ( $city || $state ) {
		$schema['contentLocation'] = array(
			'@type'   => 'Place',
			'name'    => $place ? $place : hauntedreal_get_location( $post_id ),
			'address' => array_filter( array(
				'@type'           => 'PostalAddress',
				'addressLocality' => $city,
				'addressRegion'   => $state,
				'addressCountry'  => 'US',
			) ),
		);
	}

	$keywords = array();

	foreach ( array( 'post_tag', 'ghost_theme', 'us_state' ) as $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( $terms && ! is_wp_error( $terms ) ) {
			$keywords = array_merge( $keywords, wp_list_pluck( $terms, 'name' ) );
		}
	}

	if ( $keywords ) {
		$schema['keywords'] = implode( ', ', array_unique( $keywords ) );
	}

	return $schema;
}

/**
 * BreadcrumbList mirroring the visible trail.
 *
 * @return array|null
 */
function hauntedreal_breadcrumb_schema() {
	if ( is_front_page() ) {
		return null;
	}

	$items = array(
		array(
			'url'  => home_url( '/' ),
			'name' => __( 'Home', 'hauntedreal' ),
		),
	);

	if ( is_singular( 'ghost_story' ) ) {
		$items[] = array(
			'url'  => get_post_type_archive_link( 'ghost_story' ),
			'name' => __( 'Ghost Stories', 'hauntedreal' ),
		);
		$items[] = array(
			'url'  => get_permalink(),
			'name' => wp_strip_all_tags( get_the_title() ),
		);
	} elseif ( is_singular( 'post' ) ) {
		$category = hauntedreal_get_primary_term( get_the_ID(), 'category' );

		if ( $category ) {
			$items[] = array(
				'url'  => get_category_link( $category->term_id ),
				'name' => $category->name,
			);
		}

		$items[] = array(
			'url'  => get_permalink(),
			'name' => wp_strip_all_tags( get_the_title() ),
		);
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$items[] = array(
				'url'  => get_term_link( $term ),
				'name' => $term->name,
			);
		}
	} elseif ( is_page() ) {
		$items[] = array(
			'url'  => get_permalink(),
			'name' => wp_strip_all_tags( get_the_title() ),
		);
	} else {
		return null;
	}

	$list = array();

	foreach ( $items as $index => $item ) {
		$list[] = array(
			'@type'    => 'ListItem',
			'position' => $index + 1,
			'name'     => $item['name'],
			'item'     => $item['url'],
		);
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => home_url( '/#breadcrumb' ),
		'itemListElement' => $list,
	);
}
