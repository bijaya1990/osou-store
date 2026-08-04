<?php
/**
 * Content types: Ghost Stories, US States, Experience Types.
 *
 * Nothing here touches existing posts, categories, tags or permalinks.
 * Editorial content on HauntedReal keeps working exactly as it does today;
 * this file only adds new structures alongside it.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ghost Story — community-submitted paranormal experiences.
 *
 * Deliberately a separate post type rather than a category, so the archive,
 * the submission flow, the schema and the visual treatment can all diverge
 * from editorial reporting without risking the two being confused.
 */
function hauntedreal_register_ghost_story() {
	$labels = array(
		'name'                  => _x( 'Ghost Stories', 'post type general name', 'hauntedreal' ),
		'singular_name'         => _x( 'Ghost Story', 'post type singular name', 'hauntedreal' ),
		'menu_name'             => _x( 'Ghost Stories', 'admin menu', 'hauntedreal' ),
		'add_new'               => __( 'Add New', 'hauntedreal' ),
		'add_new_item'          => __( 'Add New Ghost Story', 'hauntedreal' ),
		'edit_item'             => __( 'Edit Ghost Story', 'hauntedreal' ),
		'new_item'              => __( 'New Ghost Story', 'hauntedreal' ),
		'view_item'             => __( 'View Ghost Story', 'hauntedreal' ),
		'view_items'            => __( 'View Ghost Stories', 'hauntedreal' ),
		'search_items'          => __( 'Search Ghost Stories', 'hauntedreal' ),
		'not_found'             => __( 'No ghost stories found.', 'hauntedreal' ),
		'not_found_in_trash'    => __( 'No ghost stories found in Trash.', 'hauntedreal' ),
		'all_items'             => __( 'All Ghost Stories', 'hauntedreal' ),
		'featured_image'        => __( 'Experience Image', 'hauntedreal' ),
		'archives'              => __( 'Ghost Story Archive', 'hauntedreal' ),
	);

	register_post_type( 'ghost_story', array(
		'labels'             => $labels,
		'public'             => true,
		'show_in_rest'       => true,
		'has_archive'        => 'ghost-stories',
		'rewrite'            => array(
			'slug'       => 'ghost-stories',
			'with_front' => false,
		),
		'menu_icon'          => 'dashicons-visibility',
		'menu_position'      => 5,
		'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'comments', 'revisions', 'custom-fields' ),
		'taxonomies'         => array( 'us_state', 'ghost_theme' ),
		'capability_type'    => 'post',
		'map_meta_cap'       => true,
	) );
}
add_action( 'init', 'hauntedreal_register_ghost_story' );

/**
 * US State taxonomy — powers the Haunted America section.
 *
 * Shared by editorial posts and ghost stories so a state archive reads as
 * one place with two kinds of story about it.
 */
function hauntedreal_register_state_taxonomy() {
	$labels = array(
		'name'              => _x( 'US States', 'taxonomy general name', 'hauntedreal' ),
		'singular_name'     => _x( 'US State', 'taxonomy singular name', 'hauntedreal' ),
		'search_items'      => __( 'Search States', 'hauntedreal' ),
		'all_items'         => __( 'All States', 'hauntedreal' ),
		'edit_item'         => __( 'Edit State', 'hauntedreal' ),
		'update_item'       => __( 'Update State', 'hauntedreal' ),
		'add_new_item'      => __( 'Add New State', 'hauntedreal' ),
		'new_item_name'     => __( 'New State Name', 'hauntedreal' ),
		'menu_name'         => __( 'US States', 'hauntedreal' ),
	);

	register_taxonomy( 'us_state', array( 'post', 'ghost_story' ), array(
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array(
			'slug'       => 'haunted-america',
			'with_front' => false,
		),
	) );
}
add_action( 'init', 'hauntedreal_register_state_taxonomy' );

/**
 * Experience Type — apparition, poltergeist, shadow figure, EVP, and so on.
 */
function hauntedreal_register_experience_taxonomy() {
	$labels = array(
		'name'          => _x( 'Experience Types', 'taxonomy general name', 'hauntedreal' ),
		'singular_name' => _x( 'Experience Type', 'taxonomy singular name', 'hauntedreal' ),
		'all_items'     => __( 'All Experience Types', 'hauntedreal' ),
		'edit_item'     => __( 'Edit Experience Type', 'hauntedreal' ),
		'add_new_item'  => __( 'Add New Experience Type', 'hauntedreal' ),
		'menu_name'     => __( 'Experience Types', 'hauntedreal' ),
	);

	register_taxonomy( 'ghost_theme', array( 'ghost_story', 'post' ), array(
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array(
			'slug'       => 'experience',
			'with_front' => false,
		),
	) );
}
add_action( 'init', 'hauntedreal_register_experience_taxonomy' );

/**
 * Post meta shared by articles and ghost stories.
 *
 * Registered properly (rather than left as loose custom fields) so the REST
 * API, the block editor and any future app all see the same schema.
 */
function hauntedreal_register_meta() {
	$string_fields = array(
		'_hauntedreal_city'           => __( 'City', 'hauntedreal' ),
		'_hauntedreal_state'          => __( 'State', 'hauntedreal' ),
		'_hauntedreal_place'          => __( 'Location name', 'hauntedreal' ),
		'_hauntedreal_experience_date'=> __( 'Date of experience', 'hauntedreal' ),
		'_hauntedreal_witness'        => __( 'Witness name', 'hauntedreal' ),
		'_hauntedreal_status'         => __( 'Verification status', 'hauntedreal' ),
	);

	foreach ( array( 'post', 'ghost_story' ) as $post_type ) {
		foreach ( $string_fields as $key => $label ) {
			register_post_meta( $post_type, $key, array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			) );
		}

		register_post_meta( $post_type, '_hauntedreal_disable_ads', array(
			'type'              => 'boolean',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}

	// Submitter contact details stay private — never exposed over REST.
	register_post_meta( 'ghost_story', '_hauntedreal_submitter_email', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => false,
		'sanitize_callback' => 'sanitize_email',
		'auth_callback'     => function () {
			return current_user_can( 'edit_others_posts' );
		},
	) );
}
add_action( 'init', 'hauntedreal_register_meta' );

/**
 * Rewrite rules for the new post type and taxonomies only exist once they
 * have been flushed. Do it on theme switch, never on every request.
 */
function hauntedreal_activate() {
	hauntedreal_register_ghost_story();
	hauntedreal_register_state_taxonomy();
	hauntedreal_register_experience_taxonomy();
	hauntedreal_seed_states();
	hauntedreal_seed_experience_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'hauntedreal_activate' );

/**
 * Create the 50 states (plus DC) the first time the theme is activated, so
 * Haunted America is populated rather than empty on day one.
 */
function hauntedreal_seed_states() {
	if ( get_option( 'hauntedreal_states_seeded' ) ) {
		return;
	}

	foreach ( hauntedreal_us_states() as $abbr => $state ) {
		if ( ! term_exists( $state, 'us_state' ) ) {
			wp_insert_term( $state, 'us_state', array(
				'slug'        => sanitize_title( $state ),
				'description' => sprintf(
					/* translators: %s: US state name. */
					__( 'Reported hauntings, investigations and community experiences from across %s.', 'hauntedreal' ),
					$state
				),
			) );
		}
	}

	update_option( 'hauntedreal_states_seeded', 1 );
}

/**
 * Seed a starting vocabulary of experience types.
 */
function hauntedreal_seed_experience_types() {
	if ( get_option( 'hauntedreal_experiences_seeded' ) ) {
		return;
	}

	$types = array(
		'Apparition',
		'Shadow Figure',
		'Poltergeist',
		'Residual Haunting',
		'EVP Recording',
		'Full-Body Apparition',
		'Haunted Object',
		'Roadside Encounter',
		'Cryptid Sighting',
		'Unexplained Sound',
	);

	foreach ( $types as $type ) {
		if ( ! term_exists( $type, 'ghost_theme' ) ) {
			wp_insert_term( $type, 'ghost_theme' );
		}
	}

	update_option( 'hauntedreal_experiences_seeded', 1 );
}

/**
 * The states list, keyed by USPS abbreviation.
 *
 * @return array<string, string>
 */
function hauntedreal_us_states() {
	return array(
		'AL' => 'Alabama',        'AK' => 'Alaska',        'AZ' => 'Arizona',
		'AR' => 'Arkansas',       'CA' => 'California',    'CO' => 'Colorado',
		'CT' => 'Connecticut',    'DE' => 'Delaware',      'DC' => 'District of Columbia',
		'FL' => 'Florida',        'GA' => 'Georgia',       'HI' => 'Hawaii',
		'ID' => 'Idaho',          'IL' => 'Illinois',      'IN' => 'Indiana',
		'IA' => 'Iowa',           'KS' => 'Kansas',        'KY' => 'Kentucky',
		'LA' => 'Louisiana',      'ME' => 'Maine',         'MD' => 'Maryland',
		'MA' => 'Massachusetts',  'MI' => 'Michigan',      'MN' => 'Minnesota',
		'MS' => 'Mississippi',    'MO' => 'Missouri',      'MT' => 'Montana',
		'NE' => 'Nebraska',       'NV' => 'Nevada',        'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',     'NM' => 'New Mexico',    'NY' => 'New York',
		'NC' => 'North Carolina', 'ND' => 'North Dakota',  'OH' => 'Ohio',
		'OK' => 'Oklahoma',       'OR' => 'Oregon',        'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',   'SC' => 'South Carolina','SD' => 'South Dakota',
		'TN' => 'Tennessee',      'TX' => 'Texas',         'UT' => 'Utah',
		'VT' => 'Vermont',        'VA' => 'Virginia',      'WA' => 'Washington',
		'WV' => 'West Virginia',  'WI' => 'Wisconsin',     'WY' => 'Wyoming',
	);
}

/**
 * "Gettysburg, Pennsylvania" — the location line used across the design.
 *
 * Falls back to the state taxonomy when the city meta is empty, so a post
 * tagged only with a state still reads correctly.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function hauntedreal_get_location( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( ! $post_id ) {
		return '';
	}

	$city  = get_post_meta( $post_id, '_hauntedreal_city', true );
	$state = get_post_meta( $post_id, '_hauntedreal_state', true );

	if ( ! $state ) {
		$terms = get_the_terms( $post_id, 'us_state' );

		if ( $terms && ! is_wp_error( $terms ) ) {
			$state = $terms[0]->name;
		}
	}

	if ( $city && $state ) {
		/* translators: 1: city, 2: US state. */
		return sprintf( _x( '%1$s, %2$s', 'city, state', 'hauntedreal' ), $city, $state );
	}

	return $city ? $city : (string) $state;
}
