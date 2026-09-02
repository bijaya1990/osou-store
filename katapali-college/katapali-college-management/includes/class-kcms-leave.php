<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KCMS_Leave {

	public static function init() {
		add_shortcode( 'kcms_leave_form', array( __CLASS__, 'shortcode_form' ) );
		add_action( 'wp_ajax_kcms_leave_send_otp', array( __CLASS__, 'ajax_send_otp' ) );
		add_action( 'wp_ajax_kcms_leave_submit', array( __CLASS__, 'ajax_submit' ) );
		add_action( 'admin_post_kcms_leave_decision', array( __CLASS__, 'handle_decision' ) );
	}

	/* Find (or auto-link, by matching email) the employee record for the
	   logged-in WP user - lets an admin pre-load employee master data
	   without a manual "link this account" step for every teacher. */
	public static function get_employee_for_user( $user_id ) {
		global $wpdb;
		$table = KCMS_DB::t( 'employees' );
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id=%d", $user_id ) );
		if ( $row ) return $row;

		$user = get_userdata( $user_id );
		if ( ! $user ) return null;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE email=%s AND user_id IS NULL", $user->user_email ) );
		if ( $row ) {
			$wpdb->update( $table, array( 'user_id' => $user_id ), array( 'emp_id' => $row->emp_id ) );
			$row->user_id = $user_id;
		}
		return $row;
	}

	public static function shortcode_form() {
		if ( ! is_user_logged_in() ) {
			return KCMS_Login::render_inline( 'teacher', 'Please Log In to Apply' );
		}
		$emp = self::get_employee_for_user( get_current_user_id() );
		if ( ! $emp ) {
			return '<div class="kcms-box kcms-notice">Your account is not yet linked to an employee record. Please contact the college office/admin to be added under <strong>Katapali College Management &rarr; Employees</strong>.</div>';
		}
		ob_start();
		include KCMS_DIR . 'templates/leave-form.php';
		return ob_get_clean();
	}

	public static function ajax_send_otp() {
		check_ajax_referer( 'kcms_nonce', 'nonce' );
		if ( ! is_user_logged_in() ) wp_send_json_error( 'Not logged in.' );
		$emp = self::get_employee_for_user( get_current_user_id() );
		if ( ! $emp ) wp_send_json_error( 'No employee record linked to your account.' );
		$mobile = KCMS_Crypto::decrypt( $emp->phone_enc );
		$result = KCMS_OTP::generate_and_send( 'leave', $emp->emp_id, $mobile, $emp->email );
		if ( is_wp_error( $result ) ) wp_send_json_error( $result->get_error_message() );
		wp_send_json_success( 'OTP sent.' );
	}

	public static function ajax_submit() {
		check_ajax_referer( 'kcms_nonce', 'nonce' );
		if ( ! is_user_logged_in() ) wp_send_json_error( 'Not logged in.' );
		$emp = self::get_employee_for_user( get_current_user_id() );
		if ( ! $emp ) wp_send_json_error( 'No employee record linked to your account.' );

		$otp = isset( $_POST['otp'] ) ? sanitize_text_field( wp_unslash( $_POST['otp'] ) ) : '';
		$verify = KCMS_OTP::verify( 'leave', $emp->emp_id, $otp );
		if ( is_wp_error( $verify ) ) wp_send_json_error( $verify->get_error_message() );

		$leave_type = isset( $_POST['leave_type'] ) ? sanitize_text_field( wp_unslash( $_POST['leave_type'] ) ) : '';
		$from_date  = isset( $_POST['from_date'] ) ? sanitize_text_field( wp_unslash( $_POST['from_date'] ) ) : '';
		$to_date    = isset( $_POST['to_date'] ) ? sanitize_text_field( wp_unslash( $_POST['to_date'] ) ) : '';
		$reason     = isset( $_POST['reason'] ) ? sanitize_textarea_field( wp_unslash( $_POST['reason'] ) ) : '';
		$remarks    = isset( $_POST['remarks'] ) ? sanitize_textarea_field( wp_unslash( $_POST['remarks'] ) ) : '';

		if ( ! in_array( $leave_type, array( 'CL', 'EL', 'ML', 'DL', 'Other' ), true ) ) wp_send_json_error( 'Invalid leave type.' );
		$from_ts = strtotime( $from_date );
		$to_ts   = strtotime( $to_date );
		if ( ! $from_ts || ! $to_ts || $to_ts < $from_ts ) wp_send_json_error( 'Please provide a valid date range.' );
		$days = round( ( $to_ts - $from_ts ) / DAY_IN_SECONDS ) + 1;
		$year = (int) gmdate( 'Y', $from_ts );

		$signature_path = '';
		if ( ! empty( $_FILES['signature']['name'] ) ) {
			$uploaded = self::handle_upload( 'signature' );
			if ( is_wp_error( $uploaded ) ) wp_send_json_error( $uploaded->get_error_message() );
			$signature_path = $uploaded;
		}

		global $wpdb;
		$number = KCMS_Numbering::leave_number( $emp->emp_id, $leave_type, $year );
		$now = current_time( 'mysql' );
		$wpdb->insert( KCMS_DB::t( 'leave_applications' ), array(
			'emp_id'             => $emp->emp_id,
			'application_number' => $number,
			'leave_type'         => $leave_type,
			'leave_year'         => $year,
			'from_date'          => gmdate( 'Y-m-d', $from_ts ),
			'to_date'            => gmdate( 'Y-m-d', $to_ts ),
			'number_of_days'     => $days,
			'reason'             => $reason,
			'remarks'            => $remarks,
			'signature_file'     => $signature_path,
			'status'             => 'submitted',
			'submitted_date'     => $now,
			'otp_verified'       => 1,
			'created_at'         => $now,
			'updated_at'         => $now,
		) );
		$id = $wpdb->insert_id;
		KCMS_DB::log( 'leave_submitted', 'leave_application', $id, $number );
		wp_send_json_success( array( 'id' => $id, 'number' => $number, 'print_url' => add_query_arg( array( 'kcms_print' => 'leave', 'kcms_id' => $id ), home_url( '/' ) ) ) );
	}

	public static function handle_upload( $field ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$allowed = array( 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf' );
		$overrides = array( 'test_form' => false, 'mimes' => $allowed );
		$moved = wp_handle_upload( $_FILES[ $field ], $overrides );
		if ( isset( $moved['error'] ) ) return new WP_Error( 'kcms_upload_failed', $moved['error'] );
		return $moved['url'];
	}

	public static function handle_decision() {
		if ( ! current_user_can( 'kcms_manage_leave' ) ) wp_die( 'Not allowed.' );
		check_admin_referer( 'kcms_leave_decision' );
		global $wpdb;
		$id = absint( $_POST['id'] ?? 0 );
		$decision = sanitize_text_field( wp_unslash( $_POST['decision'] ?? '' ) );
		$remarks = sanitize_textarea_field( wp_unslash( $_POST['principal_remarks'] ?? '' ) );
		if ( ! in_array( $decision, array( 'approved', 'rejected' ), true ) || ! $id ) wp_die( 'Invalid request.' );

		$signature_path = '';
		if ( ! empty( $_FILES['principal_signature']['name'] ) ) {
			$uploaded = self::handle_upload( 'principal_signature' );
			if ( ! is_wp_error( $uploaded ) ) $signature_path = $uploaded;
		}

		$data = array( 'status' => $decision, 'principal_remarks' => $remarks, 'approval_date' => current_time( 'mysql' ), 'updated_at' => current_time( 'mysql' ) );
		if ( $signature_path ) $data['principal_signature'] = $signature_path;
		$wpdb->update( KCMS_DB::t( 'leave_applications' ), $data, array( 'application_id' => $id ) );
		KCMS_DB::log( 'leave_' . $decision, 'leave_application', $id, $remarks );

		wp_safe_redirect( add_query_arg( array( 'page' => 'kcms-leave', 'kcms_msg' => 'saved' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function render_print( $id ) {
		global $wpdb;
		$table = KCMS_DB::t( 'leave_applications' );
		$app = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE application_id=%d", $id ) );
		if ( ! $app ) { status_header( 404 ); echo 'Not found.'; return; }

		$is_owner = false;
		if ( is_user_logged_in() ) {
			$emp = self::get_employee_for_user( get_current_user_id() );
			$is_owner = $emp && (int) $emp->emp_id === (int) $app->emp_id;
		}
		if ( ! $is_owner && ! current_user_can( 'kcms_manage_leave' ) ) { status_header( 403 ); echo 'Not allowed.'; return; }

		$emp_table = KCMS_DB::t( 'employees' );
		$emp = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$emp_table} WHERE emp_id=%d", $app->emp_id ) );
		include KCMS_DIR . 'templates/leave-print.php';
	}
}
KCMS_Leave::init();
