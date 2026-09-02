<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* A branded [kcms_login] page replacing the default wp-login.php look for
   teachers and students: two tabs ("Teacher Login" / "Student Login"),
   same username+password underneath, but if the account's actual role
   doesn't match the tab they picked, they're logged back out with a
   clear message instead of landing somewhere confusing. */
class KCMS_Login {

	public static function init() {
		add_shortcode( 'kcms_login', array( __CLASS__, 'shortcode' ) );
		add_action( 'admin_post_kcms_login', array( __CLASS__, 'handle_login' ) );
		add_action( 'admin_post_nopriv_kcms_login', array( __CLASS__, 'handle_login' ) );
	}

	public static function login_page_url() {
		$page_id = (int) get_option( 'kcms_login_page_id' );
		return $page_id ? get_permalink( $page_id ) : wp_login_url();
	}

	public static function shortcode() {
		if ( is_user_logged_in() ) {
			$url = KCMS_Portal::portal_url();
			return '<div class="kcms-box kcms-notice">You are already logged in. <a href="' . esc_url( $url ) . '">Go to My Dashboard</a> or <a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '">log out</a>.</div>';
		}

		$error = '';
		if ( isset( $_GET['kcms_login_error'] ) ) {
			$errors = array(
				'bad_credentials' => 'Incorrect username or password. Please try again.',
				'wrong_type'      => 'That account is not registered under the login type you selected. Please use the correct tab.',
				'no_account'      => 'No account found. Please contact the college office.',
			);
			$key = sanitize_key( wp_unslash( $_GET['kcms_login_error'] ) );
			$error = $errors[ $key ] ?? 'Login failed. Please try again.';
		}

		ob_start();
		include KCMS_DIR . 'templates/login-form.php';
		return ob_get_clean();
	}

	public static function handle_login() {
		check_admin_referer( 'kcms_login' );
		$type = sanitize_key( wp_unslash( $_POST['login_type'] ?? '' ) );
		$creds = array(
			'user_login'    => sanitize_text_field( wp_unslash( $_POST['username'] ?? '' ) ),
			'user_password' => wp_unslash( $_POST['password'] ?? '' ),
			'remember'      => true,
		);
		$redirect_page = esc_url_raw( wp_unslash( $_POST['redirect_page'] ?? '' ) ) ?: self::login_page_url();

		$user = wp_signon( $creds, is_ssl() );
		if ( is_wp_error( $user ) ) {
			wp_safe_redirect( add_query_arg( 'kcms_login_error', 'bad_credentials', $redirect_page ) );
			exit;
		}

		$expected_role = 'teacher' === $type ? 'kcms_teacher' : 'kcms_student';
		if ( ! in_array( $expected_role, (array) $user->roles, true ) && ! user_can( $user, 'kcms_manage_settings' ) ) {
			wp_logout();
			wp_safe_redirect( add_query_arg( 'kcms_login_error', 'wrong_type', $redirect_page ) );
			exit;
		}

		KCMS_DB::log( 'portal_login', 'user', $user->ID, $type );
		wp_safe_redirect( KCMS_Portal::portal_url() );
		exit;
	}
}
KCMS_Login::init();
