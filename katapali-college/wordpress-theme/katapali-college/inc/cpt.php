<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ------------------------------ post types ------------------------------ */
function kc_register_cpts() {

	register_post_type( 'kc_slide', array(
		'label' => __( 'Hero Slides', 'katapali-college' ),
		'labels' => array( 'name' => 'Hero Slides', 'singular_name' => 'Slide', 'add_new_item' => 'Add New Slide' ),
		'public' => false, 'show_ui' => true, 'show_in_menu' => 'katapali-college',
		'menu_icon' => 'dashicons-images-alt2', 'supports' => array( 'title', 'thumbnail' ),
	) );

	register_post_type( 'kc_notice', array(
		'label' => __( 'Notices', 'katapali-college' ),
		'labels' => array( 'name' => 'Notices', 'singular_name' => 'Notice', 'add_new_item' => 'Add New Notice' ),
		'public' => true, 'has_archive' => true, 'rewrite' => array( 'slug' => 'notices' ),
		'show_in_menu' => 'katapali-college', 'menu_icon' => 'dashicons-megaphone',
		'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
	) );
	register_taxonomy( 'kc_notice_cat', 'kc_notice', array(
		'label' => 'Notice Category', 'hierarchical' => true, 'show_ui' => true,
		'rewrite' => array( 'slug' => 'notice-category' ), 'show_in_menu' => true,
	) );

	register_post_type( 'kc_recruitment', array(
		'label' => __( 'Recruitment', 'katapali-college' ),
		'labels' => array( 'name' => 'Recruitment', 'singular_name' => 'Job Posting', 'add_new_item' => 'Add New Job Posting' ),
		'public' => true, 'has_archive' => true, 'rewrite' => array( 'slug' => 'recruitment' ),
		'show_in_menu' => 'katapali-college', 'menu_icon' => 'dashicons-id-alt',
		'supports' => array( 'title', 'editor', 'excerpt' ),
	) );

	register_post_type( 'kc_tender', array(
		'label' => __( 'Tenders', 'katapali-college' ),
		'labels' => array( 'name' => 'Tenders', 'singular_name' => 'Tender', 'add_new_item' => 'Add New Tender' ),
		'public' => true, 'has_archive' => true, 'rewrite' => array( 'slug' => 'tenders' ),
		'show_in_menu' => 'katapali-college', 'menu_icon' => 'dashicons-media-document',
		'supports' => array( 'title', 'editor' ),
	) );

	register_post_type( 'kc_faculty', array(
		'label' => __( 'Faculty', 'katapali-college' ),
		'labels' => array( 'name' => 'Faculty', 'singular_name' => 'Faculty Member', 'add_new_item' => 'Add New Faculty Member' ),
		'public' => true, 'has_archive' => true, 'rewrite' => array( 'slug' => 'faculty' ),
		'show_in_menu' => 'katapali-college', 'menu_icon' => 'dashicons-groups',
		'supports' => array( 'title', 'thumbnail' ),
	) );
	register_taxonomy( 'kc_department', 'kc_faculty', array(
		'label' => 'Department', 'hierarchical' => true, 'show_ui' => true,
		'rewrite' => array( 'slug' => 'department' ), 'show_in_menu' => true,
	) );

	register_post_type( 'kc_gallery', array(
		'label' => __( 'Gallery', 'katapali-college' ),
		'labels' => array( 'name' => 'Gallery', 'singular_name' => 'Photo', 'add_new_item' => 'Add New Photo' ),
		'public' => true, 'has_archive' => true, 'rewrite' => array( 'slug' => 'gallery-item' ),
		'show_in_menu' => 'katapali-college', 'menu_icon' => 'dashicons-format-gallery',
		'supports' => array( 'title', 'thumbnail' ),
	) );
	register_taxonomy( 'kc_gallery_cat', 'kc_gallery', array(
		'label' => 'Gallery Category', 'hierarchical' => true, 'show_ui' => true,
		'rewrite' => array( 'slug' => 'gallery-category' ), 'show_in_menu' => true,
	) );

	register_post_type( 'kc_download', array(
		'label' => __( 'Downloads', 'katapali-college' ),
		'labels' => array( 'name' => 'Downloads', 'singular_name' => 'Download', 'add_new_item' => 'Add New Download' ),
		'public' => true, 'has_archive' => true, 'rewrite' => array( 'slug' => 'downloads-list' ),
		'show_in_menu' => 'katapali-college', 'menu_icon' => 'dashicons-download',
		'supports' => array( 'title' ),
	) );

	/* One CPT reused for three link lists, grouped by taxonomy: the homepage
	   footer's "Resources" / "Useful Links" columns. */
	register_post_type( 'kc_link', array(
		'label' => __( 'Links', 'katapali-college' ),
		'labels' => array( 'name' => 'Links (Resources/Useful)', 'singular_name' => 'Link', 'add_new_item' => 'Add New Link' ),
		'public' => false, 'show_ui' => true, 'show_in_menu' => 'katapali-college', 'menu_icon' => 'dashicons-admin-links',
		'supports' => array( 'title' ),
	) );
	register_taxonomy( 'kc_link_group', 'kc_link', array(
		'label' => 'Link Group', 'hierarchical' => true, 'show_ui' => true, 'show_in_menu' => true,
	) );

	register_post_type( 'kc_org_logo', array(
		'label' => __( 'Organisation Logos', 'katapali-college' ),
		'labels' => array( 'name' => 'Organisation Logos', 'singular_name' => 'Organisation Logo', 'add_new_item' => 'Add New Organisation Logo' ),
		'public' => false, 'show_ui' => true, 'show_in_menu' => 'katapali-college', 'menu_icon' => 'dashicons-awards',
		'supports' => array( 'title', 'thumbnail' ),
	) );
}
add_action( 'init', 'kc_register_cpts' );

/* top-level admin menu that groups every CPT together */
function kc_admin_menu() {
	add_menu_page( 'Katapali College', 'Katapali College', 'manage_options', 'katapali-college', 'kc_admin_dashboard_page', 'dashicons-admin-multisite', 3 );
}
add_action( 'admin_menu', 'kc_admin_menu' );

function kc_admin_dashboard_page() {
	?>
	<div class="wrap">
		<h1><span class="dashicons dashicons-admin-multisite" style="font-size:28px;width:28px;height:28px;"></span> Katapali +3 College — Theme Control Centre</h1>
		<p>Manage every editable part of the website from the links below. Each item on the public site (Notices, Recruitment, Tenders, Faculty, Gallery, Downloads and Hero Slides) is a normal WordPress post type — add, edit, delete, search and filter exactly like Posts.</p>
		<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px;margin-top:20px;max-width:1100px;">
			<?php
			$cards = array(
				array( 'edit.php?post_type=kc_notice', 'Notices', 'megaphone', __wp_count( 'kc_notice' ) ),
				array( 'edit.php?post_type=kc_recruitment', 'Recruitment', 'id-alt', __wp_count( 'kc_recruitment' ) ),
				array( 'edit.php?post_type=kc_tender', 'Tenders', 'media-document', __wp_count( 'kc_tender' ) ),
				array( 'edit.php?post_type=kc_faculty', 'Faculty', 'groups', __wp_count( 'kc_faculty' ) ),
				array( 'edit.php?post_type=kc_gallery', 'Gallery Photos', 'format-gallery', __wp_count( 'kc_gallery' ) ),
				array( 'edit.php?post_type=kc_download', 'Downloads', 'download', __wp_count( 'kc_download' ) ),
				array( 'edit.php?post_type=kc_link', 'Links (Resources/Useful)', 'admin-links', __wp_count( 'kc_link' ) ),
				array( 'edit.php?post_type=kc_org_logo', 'Organisation Logos', 'awards', __wp_count( 'kc_org_logo' ) ),
				array( 'edit.php?post_type=kc_slide', 'Hero Slides', 'images-alt2', __wp_count( 'kc_slide' ) ),
				array( 'customize.php', 'Theme Customizer (College Info, Colours, Map)', 'admin-customizer', '' ),
				array( 'nav-menus.php', 'Menus', 'menu', '' ),
				array( 'admin.php?page=kc-demo-importer', 'Demo Content Importer', 'database-import', '' ),
			);
			foreach ( $cards as $c ) {
				echo '<a href="' . esc_url( admin_url( $c[0] ) ) . '" style="display:block;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px;text-decoration:none;color:#1e40af;">';
				echo '<span class="dashicons dashicons-' . esc_attr( $c[1] === 'Theme Customizer (College Info, Colours, Map)' ? 'admin-customizer' : $c[2] ) . '" style="font-size:26px;width:26px;height:26px;"></span>';
				echo '<div style="font-weight:600;margin-top:8px;color:#111;">' . esc_html( $c[1] ) . '</div>';
				if ( $c[3] !== '' ) echo '<div style="color:#646970;font-size:.85em;">' . intval( $c[3] ) . ' item(s)</div>';
				echo '</a>';
			}
			?>
		</div>
	</div>
	<?php
}
function __wp_count( $pt ) {
	$c = wp_count_posts( $pt );
	return isset( $c->publish ) ? $c->publish : 0;
}
