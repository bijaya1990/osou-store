<?php
/**
 * Plugin Name: Katapali College Management System
 * Description: Leave Application, Certificate/Marksheet Request, and Student ID Card management for Katapali +3 College. Works alongside the Katapali College theme (or any theme).
 * Version: 1.0.0
 * Author: Katapali +3 College
 * Text Domain: kcms
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'KCMS_VERSION', '1.0.0' );
define( 'KCMS_DIR', plugin_dir_path( __FILE__ ) );
define( 'KCMS_URI', plugin_dir_url( __FILE__ ) );
define( 'KCMS_DB_VERSION', '1' );

require_once KCMS_DIR . 'includes/class-kcms-db.php';
require_once KCMS_DIR . 'includes/class-kcms-roles.php';
require_once KCMS_DIR . 'includes/class-kcms-otp.php';
require_once KCMS_DIR . 'includes/class-kcms-xlsx-reader.php';
require_once KCMS_DIR . 'includes/class-kcms-numbering.php';
require_once KCMS_DIR . 'includes/class-kcms-crypto.php';
require_once KCMS_DIR . 'includes/class-kcms-leave.php';
require_once KCMS_DIR . 'includes/class-kcms-certificate.php';
require_once KCMS_DIR . 'includes/class-kcms-idcard.php';
require_once KCMS_DIR . 'includes/class-kcms-portal.php';
require_once KCMS_DIR . 'includes/class-kcms-login.php';
require_once KCMS_DIR . 'includes/class-kcms-admin.php';

function kcms_activate() {
	KCMS_DB::create_tables();
	KCMS_Roles::register();
	KCMS_Roles::add_caps();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'kcms_activate' );

function kcms_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'kcms_deactivate' );

/* keep tables/roles in sync if the plugin is updated without a deactivate/activate cycle */
function kcms_maybe_upgrade() {
	if ( get_option( 'kcms_db_version' ) !== KCMS_DB_VERSION ) {
		KCMS_DB::create_tables();
	}
	KCMS_Roles::register();
	KCMS_Roles::add_caps();
}
add_action( 'plugins_loaded', 'kcms_maybe_upgrade' );

function kcms_assets() {
	wp_enqueue_style( 'kcms-style', KCMS_URI . 'assets/css/kcms.css', array(), KCMS_VERSION );
	wp_enqueue_script( 'kcms-script', KCMS_URI . 'assets/js/kcms.js', array( 'jquery' ), KCMS_VERSION, true );
	wp_localize_script( 'kcms-script', 'KCMS', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'kcms_nonce' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'kcms_assets' );

function kcms_admin_assets( $hook ) {
	if ( strpos( $hook, 'kcms' ) === false ) return;
	wp_enqueue_style( 'kcms-admin-style', KCMS_URI . 'assets/css/kcms.css', array(), KCMS_VERSION );
	wp_enqueue_script( 'kcms-admin-script', KCMS_URI . 'assets/js/kcms.js', array( 'jquery' ), KCMS_VERSION, true );
	wp_localize_script( 'kcms-admin-script', 'KCMS', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'kcms_nonce' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'kcms_admin_assets' );

/* printable A4/A5/A6 templates render outside the normal theme chrome
   (no header/footer/menu) via a query var, e.g. ?kcms_print=leave&id=12 */
function kcms_print_query_vars( $vars ) {
	$vars[] = 'kcms_print';
	$vars[] = 'kcms_id';
	$vars[] = 'kcms_token';
	return $vars;
}
add_filter( 'query_vars', 'kcms_print_query_vars' );

function kcms_maybe_render_print() {
	$type = get_query_var( 'kcms_print' );
	if ( ! $type ) return;
	$id = absint( get_query_var( 'kcms_id' ) );
	switch ( $type ) {
		case 'leave':
			KCMS_Leave::render_print( $id );
			exit;
		case 'certificate':
			KCMS_Certificate::render_print( $id );
			exit;
		case 'idcard':
			KCMS_IDCard::render_id_card_print( $id );
			exit;
		case 'librarycard':
			KCMS_IDCard::render_library_card_print( $id );
			exit;
	}
}
add_action( 'template_redirect', 'kcms_maybe_render_print' );

/* pretty-ish print URLs: /?kcms_print=leave&kcms_id=12 works out of the box
   with no rewrite rules needed since it's a plain query string. */
