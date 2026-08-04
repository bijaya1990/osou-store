<?php
/**
 * HauntedReal — theme bootstrap.
 *
 * A GeneratePress Free child theme. No premium modules, no page builder,
 * no external dependencies.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HAUNTEDREAL_VERSION', '1.1.0' );
define( 'HAUNTEDREAL_DIR', get_stylesheet_directory() );
define( 'HAUNTEDREAL_URI', get_stylesheet_directory_uri() );

/**
 * Asset version string. filemtime() in development gives cache-busting for
 * free; fall back to the theme version if the file is unreadable.
 *
 * @param string $relative_path Path relative to the theme root.
 * @return string
 */
function hauntedreal_asset_version( $relative_path ) {
	$file = HAUNTEDREAL_DIR . '/' . ltrim( $relative_path, '/' );

	if ( file_exists( $file ) ) {
		return (string) filemtime( $file );
	}

	return HAUNTEDREAL_VERSION;
}

require_once HAUNTEDREAL_DIR . '/inc/compat.php';
require_once HAUNTEDREAL_DIR . '/inc/performance.php';
require_once HAUNTEDREAL_DIR . '/inc/template-tags.php';
require_once HAUNTEDREAL_DIR . '/inc/adsterra.php';
require_once HAUNTEDREAL_DIR . '/inc/ads.php';
require_once HAUNTEDREAL_DIR . '/inc/content-types.php';
require_once HAUNTEDREAL_DIR . '/inc/meta-boxes.php';
require_once HAUNTEDREAL_DIR . '/inc/submissions.php';
require_once HAUNTEDREAL_DIR . '/inc/customizer.php';
require_once HAUNTEDREAL_DIR . '/inc/schema.php';

/**
 * Theme supports, image sizes, menus.
 */
function hauntedreal_setup() {
	load_theme_textdomain( 'hauntedreal', HAUNTEDREAL_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	/*
	 * Fixed aspect ratios, hard cropped. Every image slot in the design
	 * declares width and height from these sizes so nothing reflows when an
	 * image decodes. This is most of our CLS budget.
	 */
	add_image_size( 'hauntedreal-hero', 1600, 900, true );      // 16:9 lead
	add_image_size( 'hauntedreal-hero-portrait', 1200, 800, true ); // 3:2 mobile lead
	add_image_size( 'hauntedreal-card', 800, 450, true );        // 16:9 card
	add_image_size( 'hauntedreal-thumb', 320, 320, true );       // 1:1 row card

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'hauntedreal' ),
		'topbar'  => __( 'Top Bar Menu (desktop only)', 'hauntedreal' ),
		'footer'  => __( 'Footer Menu', 'hauntedreal' ),
		'legal'   => __( 'Legal Menu', 'hauntedreal' ),
	) );

	// Content width used by oEmbeds and wide images.
	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 800;
	}
}
add_action( 'after_setup_theme', 'hauntedreal_setup' );

/**
 * Front-end assets.
 *
 * One stylesheet. One tiny script, deferred. That is the entire payload.
 */
function hauntedreal_enqueue_assets() {
	wp_enqueue_style(
		'hauntedreal-main',
		HAUNTEDREAL_URI . '/assets/css/main.css',
		array(),
		hauntedreal_asset_version( 'assets/css/main.css' )
	);

	// style.css holds only the admin-bar offsets; it still needs to load.
	wp_enqueue_style(
		'hauntedreal-style',
		get_stylesheet_uri(),
		array( 'hauntedreal-main' ),
		hauntedreal_asset_version( 'style.css' )
	);

	wp_enqueue_script(
		'hauntedreal-navigation',
		HAUNTEDREAL_URI . '/assets/js/navigation.js',
		array(),
		hauntedreal_asset_version( 'assets/js/navigation.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'hauntedreal_enqueue_assets' );

/**
 * Widget areas.
 */
function hauntedreal_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Article Sidebar', 'hauntedreal' ),
		'id'            => 'hauntedreal-sidebar',
		'description'   => __( 'Shown beside articles and archives on screens 1024px and wider only.', 'hauntedreal' ),
		'before_widget' => '<section id="%1$s" class="hr-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="hr-widget__title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Column', 'hauntedreal' ),
		'id'            => 'hauntedreal-footer',
		'description'   => __( 'Optional extra column in the footer.', 'hauntedreal' ),
		'before_widget' => '<section id="%1$s" class="hr-footer__widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'hauntedreal_widgets_init' );

/**
 * Body classes used by the layout shell.
 *
 * @param array $classes Existing classes.
 * @return array
 */
function hauntedreal_body_classes( $classes ) {
	$classes[] = 'hr-body';

	if ( ! hauntedreal_has_sidebar() ) {
		$classes[] = 'hr-no-sidebar';
	}

	if ( is_singular( 'ghost_story' ) || is_post_type_archive( 'ghost_story' ) ) {
		$classes[] = 'hr-ghost-context';
	}

	return $classes;
}
add_filter( 'body_class', 'hauntedreal_body_classes' );

/**
 * Whether the current view should render the sidebar column.
 *
 * The sidebar is a desktop affordance. CSS hides it below 1024px, but we
 * also skip the markup entirely where it makes no sense.
 *
 * @return bool
 */
function hauntedreal_has_sidebar() {
	$has_sidebar = is_active_sidebar( 'hauntedreal-sidebar' ) || hauntedreal_ad_slot_is_enabled( 'sidebar' );

	if ( is_page_template( 'page-templates/submit-ghost-story.php' ) ) {
		$has_sidebar = false;
	}

	if ( is_404() || is_page_template( 'page-templates/haunted-america.php' ) ) {
		$has_sidebar = false;
	}

	if ( is_page() && ! is_page_template() ) {
		$has_sidebar = false;
	}

	/**
	 * Filters whether the sidebar column renders.
	 *
	 * @param bool $has_sidebar
	 */
	return (bool) apply_filters( 'hauntedreal_has_sidebar', $has_sidebar );
}

/**
 * Excerpt length and ending, tuned for the card design (~2 lines).
 */
function hauntedreal_excerpt_length() {
	return 22;
}
add_filter( 'excerpt_length', 'hauntedreal_excerpt_length' );

function hauntedreal_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'hauntedreal_excerpt_more' );

/**
 * Posts per page on the ghost story archive.
 *
 * @param WP_Query $query Query object.
 */
function hauntedreal_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'ghost_story' ) || $query->is_tax( 'ghost_theme' ) ) {
		$query->set( 'posts_per_page', 12 );
	}

	// The Haunted America state archives mix editorial posts and ghost stories.
	if ( $query->is_tax( 'us_state' ) ) {
		$query->set( 'post_type', array( 'post', 'ghost_story' ) );
		$query->set( 'posts_per_page', 12 );
	}

	// Search covers both editorial and community content.
	if ( $query->is_search() ) {
		$query->set( 'post_type', array( 'post', 'page', 'ghost_story' ) );
	}
}
add_action( 'pre_get_posts', 'hauntedreal_pre_get_posts' );

/**
 * Editor styles so the writing surface resembles the published article.
 */
function hauntedreal_editor_assets() {
	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'hauntedreal_editor_assets' );
