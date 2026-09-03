<?php
/**
 * Plugin Name: Katapali Student Records
 * Description: Import student admission-register Excel/CSV data, verify students by name/roll no, browse an alumni directory by batch, and generate printable ID cards and library cards. Cards and full records are admin-only; the public search/alumni pages only ever show name, roll no, stream and batch.
 * Version: 1.1.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'KSR_VERSION', '1.1.0' );
define( 'KSR_DIR', plugin_dir_path( __FILE__ ) );
define( 'KSR_URI', plugin_dir_url( __FILE__ ) );

require_once KSR_DIR . 'includes/class-install.php';
require_once KSR_DIR . 'includes/class-xlsx-reader.php';
require_once KSR_DIR . 'includes/class-importer.php';
require_once KSR_DIR . 'includes/class-cards.php';
require_once KSR_DIR . 'includes/class-admin.php';
require_once KSR_DIR . 'includes/class-shortcodes.php';

register_activation_hook( __FILE__, array( 'KSR_Install', 'activate' ) );
add_action( 'init', array( 'KSR_Install', 'maybe_upgrade' ) );

if ( is_admin() ) {
	KSR_Admin::init();
}
KSR_Shortcodes::init();
