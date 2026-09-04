<?php
/**
 * Plugin Name: Katapali Admin Panel
 * Description: A custom-branded, role-restricted admin experience for college staff - reskins the login page and wp-admin with the college's logo/name/address/colours, trims the menu to only content management (Hero Slides, Notices, Recruitment, Tenders, Faculty, Gallery, Downloads, Links, Organisation Logos, Applications, Posts, Student Records, Media), and lets a real site administrator create OTP-verified staff admin accounts.
 * Version: 1.0.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'KAP_VERSION', '1.0.0' );
define( 'KAP_DIR', plugin_dir_path( __FILE__ ) );
define( 'KAP_URI', plugin_dir_url( __FILE__ ) );

require_once KAP_DIR . 'includes/class-role.php';
require_once KAP_DIR . 'includes/class-login-branding.php';
require_once KAP_DIR . 'includes/class-menu-trim.php';
require_once KAP_DIR . 'includes/class-account-creation.php';

register_activation_hook( __FILE__, array( 'KAP_Role', 'activate' ) );
add_action( 'init', array( 'KAP_Role', 'install_role' ) );

KAP_Login_Branding::init();
if ( is_admin() ) {
	KAP_Menu_Trim::init();
	KAP_Account_Creation::init();
}
