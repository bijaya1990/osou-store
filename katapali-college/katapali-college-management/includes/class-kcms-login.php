<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* A branded [kcms_login] page replacing the default wp-login.php look:
   two tabs ("Teacher Login" / "Student Login"), both authenticating with
   just Mobile Number + Date of Birth (no separate username/password to
   remember) - matched against the encrypted-and-hashed phone number and
   DOB already on file for that employee/student. A matching WordPress
   account is created transparently on first login if one doesn't exist
   yet, so there is no separate "create a login" admin step. */
class KCMS_Login {

	const MAX_ATTEMPTS = 6;
	const LOCKOUT_MINUTES = 15;

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

		return self::render( array( 'show_tabs' => true, 'after_login' => 'portal' ) );
	}

	/* Renders the same branded login card, but locked to one type (no tab
	   switcher) with a contextual heading, and set to return the user to
	   the current page after logging in rather than the generic portal -
	   used inline wherever a form says "please log in first" (leave,
	   certificate, ID card) so login happens right there instead of
	   sending them off to a separate page. */
	public static function render_inline( $type, $heading ) {
		return self::render( array(
			'show_tabs'   => false,
			'locked_type' => $type,
			'heading'     => $heading,
			'after_login' => 'return',
		) );
	}

	/* Same as render_inline() but keeps the Teacher/Student tab switcher -
	   used where the visitor's type isn't already known (e.g. the shared
	   "My Dashboard" page, which both roles can land on). */
	public static function render_inline_with_tabs( $heading ) {
		return self::render( array(
			'show_tabs'   => true,
			'heading'     => $heading,
			'after_login' => 'return',
		) );
	}

	private static function render( $args ) {
		$args = wp_parse_args( $args, array(
			'show_tabs'   => true,
			'locked_type' => 'teacher',
			'heading'     => '',
			'after_login' => 'portal',
		) );

		$error = '';
		if ( isset( $_GET['kcms_login_error'] ) ) {
			$errors = array(
				'no_account'  => 'No account found with that Mobile Number and Date of Birth. Please check your details, or make sure you selected the correct tab (Teacher / Student).',
				'locked'      => 'Too many incorrect attempts. Please try again in ' . self::LOCKOUT_MINUTES . ' minutes, or contact the college office.',
				'bad_request' => 'Please fill in both your Mobile Number and Date of Birth.',
			);
			$key = sanitize_key( wp_unslash( $_GET['kcms_login_error'] ) );
			$error = $errors[ $key ] ?? 'Login failed. Please try again.';
		}

		$show_tabs = $args['show_tabs'];
		$locked_type = $args['locked_type'];
		$heading = $args['heading'];
		$after_login = $args['after_login'];

		ob_start();
		include KCMS_DIR . 'templates/login-form.php';
		return ob_get_clean();
	}

	private static function lockout_key( $ip, $type ) {
		return 'kcms_login_fail_' . $type . '_' . md5( $ip );
	}

	private static function is_locked_out( $ip, $type ) {
		return (int) get_transient( self::lockout_key( $ip, $type ) ) >= self::MAX_ATTEMPTS;
	}

	private static function register_failure( $ip, $type ) {
		$key = self::lockout_key( $ip, $type );
		$count = (int) get_transient( $key );
		set_transient( $key, $count + 1, self::LOCKOUT_MINUTES * MINUTE_IN_SECONDS );
	}

	private static function clear_failures( $ip, $type ) {
		delete_transient( self::lockout_key( $ip, $type ) );
	}

	public static function handle_login() {
		check_admin_referer( 'kcms_login' );
		$redirect_page = esc_url_raw( wp_unslash( $_POST['redirect_page'] ?? '' ) ) ?: self::login_page_url();
		$type = 'student' === sanitize_key( wp_unslash( $_POST['login_type'] ?? '' ) ) ? 'student' : 'teacher';
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';

		if ( self::is_locked_out( $ip, $type ) ) {
			wp_safe_redirect( add_query_arg( array( 'kcms_login_error' => 'locked', 'type' => $type ), $redirect_page ) );
			exit;
		}

		$mobile = preg_replace( '/\D/', '', wp_unslash( $_POST['mobile'] ?? '' ) );
		$dob = sanitize_text_field( wp_unslash( $_POST['dob'] ?? '' ) );
		if ( ! $mobile || ! $dob ) {
			wp_safe_redirect( add_query_arg( array( 'kcms_login_error' => 'bad_request', 'type' => $type ), $redirect_page ) );
			exit;
		}
		$hash = KCMS_Crypto::hash_phone( $mobile );

		$user_id = 'teacher' === $type ? self::match_employee( $hash, $dob ) : self::match_student( $hash, $dob );

		if ( ! $user_id ) {
			self::register_failure( $ip, $type );
			wp_safe_redirect( add_query_arg( array( 'kcms_login_error' => 'no_account', 'type' => $type ), $redirect_page ) );
			exit;
		}

		self::clear_failures( $ip, $type );
		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );
		$user = get_userdata( $user_id );
		if ( $user ) {
			do_action( 'wp_login', $user->user_login, $user );
		}
		KCMS_DB::log( 'portal_login', 'user', $user_id, $type );
		$after_login = sanitize_key( wp_unslash( $_POST['after_login'] ?? '' ) );
		$destination = ( 'return' === $after_login && $redirect_page ) ? $redirect_page : KCMS_Portal::portal_url();
		wp_safe_redirect( $destination );
		exit;
	}

	/* Finds the employee row matching this phone+DOB, then finds (or
	   creates) the WordPress account linked to it, and makes sure the
	   link is recorded so the rest of the plugin (get_employee_for_user)
	   recognises them immediately. */
	private static function match_employee( $hash, $dob ) {
		global $wpdb;
		$table = KCMS_DB::t( 'employees' );
		$emp = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE phone_hash=%s AND dob=%s AND status='active'", $hash, $dob ) );
		if ( ! $emp ) return 0;

		if ( $emp->user_id && get_userdata( $emp->user_id ) ) {
			return (int) $emp->user_id;
		}

		$user_id = self::get_or_create_user( $emp->email, $emp->name, 'kcms_teacher', 'emp' . $emp->emp_id );
		$wpdb->update( $table, array( 'user_id' => $user_id ), array( 'emp_id' => $emp->emp_id ) );
		return $user_id;
	}

	private static function match_student( $hash, $dob ) {
		global $wpdb;
		$table = KCMS_DB::t( 'id_cards' );
		$card = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE phone_hash=%s AND dob=%s AND status='active'", $hash, $dob ) );
		if ( ! $card ) return 0;

		$user_id = self::get_or_create_user( $card->email, $card->name, 'kcms_student', 'stu' . $card->roll_number );

		// keep the kcms_students master row (used by the certificate system) linked to the same account
		$stu_table = KCMS_DB::t( 'students' );
		$student_row = $wpdb->get_row( $wpdb->prepare( "SELECT student_id FROM {$stu_table} WHERE college_roll_no=%s", $card->roll_number ) );
		if ( $student_row ) {
			$wpdb->update( $stu_table, array( 'user_id' => $user_id ), array( 'student_id' => $student_row->student_id ) );
		} else {
			$wpdb->insert( $stu_table, array(
				'user_id'         => $user_id,
				'name'            => $card->name,
				'father_name'     => $card->father_name,
				'college_roll_no' => $card->roll_number,
				'email'           => $card->email,
				'phone_enc'       => $card->mobile_enc,
				'status'          => 'active',
				'created_at'      => current_time( 'mysql' ),
				'updated_at'      => current_time( 'mysql' ),
			) );
		}
		return $user_id;
	}

	/* Reuses an existing WP account by email when there is one; otherwise
	   creates one transparently with a random password (never used - login
	   here is always by phone+DOB) so first-time users need no separate
	   "your account has been created" step. */
	private static function get_or_create_user( $email, $name, $role, $username_hint ) {
		if ( $email ) {
			$existing = get_user_by( 'email', $email );
			if ( $existing ) {
				if ( ! in_array( $role, (array) $existing->roles, true ) ) {
					$existing->add_role( $role );
				}
				return $existing->ID;
			}
		}

		$username = sanitize_user( $username_hint, true );
		$base = $username;
		$i = 1;
		while ( username_exists( $username ) ) {
			$username = $base . $i;
			$i++;
		}
		if ( ! $email ) {
			$email = $username . '+' . wp_generate_password( 6, false ) . '@portal.invalid';
		}

		$user_id = wp_insert_user( array(
			'user_login' => $username,
			'user_email' => $email,
			'user_pass'  => wp_generate_password( 32, true, true ),
			'display_name' => $name ?: $username,
			'role'       => $role,
		) );
		return is_wp_error( $user_id ) ? 0 : $user_id;
	}
}
KCMS_Login::init();
