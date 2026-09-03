<?php
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'KC_VERSION', '1.3.2' );
define( 'KC_DIR', get_template_directory() );
define( 'KC_URI', get_template_directory_uri() );

/* ---------------------------- theme setup ------------------------------ */
function kc_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 120, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'katapali-college' ),
		'footer'  => __( 'Footer Menu', 'katapali-college' ),
	) );
}
add_action( 'after_setup_theme', 'kc_setup' );

/* our demo content already ships as clean, valid HTML (tables, lists, etc.)
   so we skip wpautop's automatic <p> wrapping, which would otherwise mangle it */
remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'wpautop', 99 ); // still available for plain paragraphs typed by the admin
function kc_smart_wpautop( $content ) {
	if ( strpos( $content, '<table' ) !== false || strpos( $content, '<ul' ) !== false || strpos( $content, '<ol' ) !== false ) {
		return $content; // already block-level HTML, leave as-is
	}
	return wpautop( $content );
}
remove_filter( 'the_content', 'wpautop', 99 );
add_filter( 'the_content', 'kc_smart_wpautop', 10 );

/* ------------------------------- assets --------------------------------- */
function kc_assets() {
	wp_enqueue_style( 'kc-google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap', array(), null );
	wp_enqueue_style( 'kc-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1' );
	wp_enqueue_style( 'kc-style', KC_URI . '/assets/css/style.css', array(), KC_VERSION );
	wp_enqueue_style( 'kc-style-main', get_stylesheet_uri(), array( 'kc-style' ), KC_VERSION );
	wp_enqueue_script( 'kc-theme', KC_URI . '/assets/js/theme.js', array(), KC_VERSION, true );

	wp_localize_script( 'kc-theme', 'KC_DATA', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
	) );

	if ( is_front_page() ) {
		wp_enqueue_script( 'html2pdf', 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js', array(), '0.10.1', true );
		wp_enqueue_script( 'kc-apply-forms', KC_URI . '/assets/js/apply-forms.js', array( 'html2pdf' ), KC_VERSION, true );
		wp_localize_script( 'kc-apply-forms', 'KC_APPS', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'kc_apps_nonce' ),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'kc_assets' );

/* Theme colours -> CSS custom properties, driven by the Customizer */
function kc_theme_color_vars() {
	$primary   = get_theme_mod( 'kc_color_primary', kc_default( 'kc_color_primary' ) );
	$secondary = get_theme_mod( 'kc_color_secondary', kc_default( 'kc_color_secondary' ) );
	$accent    = get_theme_mod( 'kc_color_accent', kc_default( 'kc_color_accent' ) );
	$dark      = get_theme_mod( 'kc_color_dark', kc_default( 'kc_color_dark' ) );
	$gold      = get_theme_mod( 'kc_color_gold', kc_default( 'kc_color_gold' ) );
	$logo_size = absint( get_theme_mod( 'kc_logo_size', kc_default( 'kc_logo_size' ) ) );
	if ( $logo_size < 40 || $logo_size > 160 ) $logo_size = 70;
	echo "<style id='kc-theme-vars'>:root{--primary:{$primary};--secondary:{$secondary};--accent:{$accent};--dark:{$dark};--gold:{$gold};--logo-size:{$logo_size}px;}</style>\n";
}
add_action( 'wp_head', 'kc_theme_color_vars' );

/* ------------------------------- includes -------------------------------- */
require KC_DIR . '/inc/defaults.php';
require KC_DIR . '/inc/nav-walker.php';
require KC_DIR . '/inc/cpt.php';
require KC_DIR . '/inc/metaboxes.php';
require KC_DIR . '/inc/customizer.php';
require KC_DIR . '/inc/template-tags.php';
require KC_DIR . '/inc/demo-importer.php';
require KC_DIR . '/inc/applications.php';

/* excerpt length for card previews */
add_filter( 'excerpt_length', function () { return 24; } );
add_filter( 'excerpt_more', function () { return '&hellip;'; } );

/* our demo photos/logo/banners ship as SVG illustrations; allow them through
   the media uploader (used both by admins and by the Demo Content Importer) */
add_filter( 'upload_mimes', function ( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
} );
add_filter( 'wp_check_filetype_and_ext', function ( $data, $file, $filename ) {
	if ( substr( $filename, -4 ) === '.svg' ) {
		$data['ext'] = 'svg';
		$data['type'] = 'image/svg+xml';
	}
	return $data;
}, 10, 3 );
