<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KCMS_Certificate {

	public static function init() {
		add_shortcode( 'kcms_certificate_form', array( __CLASS__, 'shortcode_form' ) );
		add_action( 'wp_ajax_kcms_cert_send_otp', array( __CLASS__, 'ajax_send_otp' ) );
		add_action( 'wp_ajax_kcms_cert_submit', array( __CLASS__, 'ajax_submit' ) );
		add_action( 'admin_post_kcms_cert_decision', array( __CLASS__, 'handle_decision' ) );
	}

	public static function get_student_for_user( $user_id ) {
		global $wpdb;
		$table = KCMS_DB::t( 'students' );
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id=%d", $user_id ) );
		if ( $row ) return $row;

		$user = get_userdata( $user_id );
		if ( ! $user ) return null;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE email=%s AND user_id IS NULL", $user->user_email ) );
		if ( $row ) {
			$wpdb->update( $table, array( 'user_id' => $user_id ), array( 'student_id' => $row->student_id ) );
			$row->user_id = $user_id;
		}
		return $row;
	}

	public static function shortcode_form() {
		if ( ! is_user_logged_in() ) {
			return KCMS_Login::render_inline( 'student', 'Please Log In to Apply' );
		}
		$student = self::get_student_for_user( get_current_user_id() );
		if ( ! $student ) {
			return '<div class="kcms-box kcms-notice">Your account is not yet linked to a student record. Please contact the college office (your roll number must first be added under <strong>Katapali College Management &rarr; Students / ID Cards</strong>).</div>';
		}
		ob_start();
		include KCMS_DIR . 'templates/certificate-form.php';
		return ob_get_clean();
	}

	public static function ajax_send_otp() {
		check_ajax_referer( 'kcms_nonce', 'nonce' );
		if ( ! is_user_logged_in() ) wp_send_json_error( 'Not logged in.' );
		$student = self::get_student_for_user( get_current_user_id() );
		if ( ! $student ) wp_send_json_error( 'No student record linked to your account.' );
		$mobile = KCMS_Crypto::decrypt( $student->phone_enc );
		$result = KCMS_OTP::generate_and_send( 'certificate', $student->student_id, $mobile, $student->email );
		if ( is_wp_error( $result ) ) wp_send_json_error( $result->get_error_message() );
		wp_send_json_success( 'OTP sent.' );
	}

	public static function ajax_submit() {
		check_ajax_referer( 'kcms_nonce', 'nonce' );
		if ( ! is_user_logged_in() ) wp_send_json_error( 'Not logged in.' );
		$student = self::get_student_for_user( get_current_user_id() );
		if ( ! $student ) wp_send_json_error( 'No student record linked to your account.' );

		$otp = isset( $_POST['otp'] ) ? sanitize_text_field( wp_unslash( $_POST['otp'] ) ) : '';
		$verify = KCMS_OTP::verify( 'certificate', $student->student_id, $otp );
		if ( is_wp_error( $verify ) ) wp_send_json_error( $verify->get_error_message() );

		$types = isset( $_POST['certificate_type'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['certificate_type'] ) ) : array();
		$session = isset( $_POST['session'] ) ? sanitize_text_field( wp_unslash( $_POST['session'] ) ) : '';
		$reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
		$copies = max( 1, absint( $_POST['num_copies'] ?? 1 ) );
		$delivery = isset( $_POST['delivery_method'] ) ? sanitize_text_field( wp_unslash( $_POST['delivery_method'] ) ) : '';
		$remarks = isset( $_POST['remarks'] ) ? sanitize_textarea_field( wp_unslash( $_POST['remarks'] ) ) : '';

		if ( empty( $types ) ) wp_send_json_error( 'Please select at least one certificate/document type.' );

		$signature_path = '';
		if ( ! empty( $_FILES['signature']['name'] ) ) {
			$uploaded = KCMS_Leave::handle_upload( 'signature' );
			if ( is_wp_error( $uploaded ) ) wp_send_json_error( $uploaded->get_error_message() );
			$signature_path = $uploaded;
		}

		global $wpdb;
		$number = KCMS_Numbering::certificate_number();
		$now = current_time( 'mysql' );
		$wpdb->insert( KCMS_DB::t( 'certificate_requests' ), array(
			'request_number'   => $number,
			'student_id'       => $student->student_id,
			'certificate_type' => wp_json_encode( $types ),
			'session'          => $session,
			'reason'           => $reason,
			'num_copies'       => $copies,
			'delivery_method'  => $delivery,
			'remarks'          => $remarks,
			'signature_file'   => $signature_path,
			'date_requested'   => $now,
			'status'           => 'pending',
			'otp_verified'     => 1,
			'created_at'       => $now,
			'updated_at'       => $now,
		) );
		$id = $wpdb->insert_id;
		KCMS_DB::log( 'certificate_requested', 'certificate_request', $id, $number );
		wp_send_json_success( array( 'id' => $id, 'number' => $number, 'print_url' => add_query_arg( array( 'kcms_print' => 'certificate', 'kcms_id' => $id ), home_url( '/' ) ) ) );
	}

	public static function handle_decision() {
		if ( ! current_user_can( 'kcms_manage_certificates' ) ) wp_die( 'Not allowed.' );
		check_admin_referer( 'kcms_cert_decision' );
		global $wpdb;
		$id = absint( $_POST['id'] ?? 0 );
		$decision = sanitize_text_field( wp_unslash( $_POST['decision'] ?? '' ) );
		if ( ! in_array( $decision, array( 'approved', 'issued', 'rejected' ), true ) || ! $id ) wp_die( 'Invalid request.' );

		$signature_path = '';
		if ( ! empty( $_FILES['principal_signature']['name'] ) ) {
			$uploaded = KCMS_Leave::handle_upload( 'principal_signature' );
			if ( ! is_wp_error( $uploaded ) ) $signature_path = $uploaded;
		}

		$data = array( 'status' => $decision, 'approval_date' => current_time( 'mysql' ), 'updated_at' => current_time( 'mysql' ) );
		if ( $signature_path ) { $data['principal_signature'] = $signature_path; $data['principal_date'] = current_time( 'mysql' ); }
		$wpdb->update( KCMS_DB::t( 'certificate_requests' ), $data, array( 'request_id' => $id ) );
		KCMS_DB::log( 'certificate_' . $decision, 'certificate_request', $id );

		wp_safe_redirect( add_query_arg( array( 'page' => 'kcms-certificates', 'kcms_msg' => 'saved' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function render_print( $id ) {
		global $wpdb;
		$table = KCMS_DB::t( 'certificate_requests' );
		$req = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE request_id=%d", $id ) );
		if ( ! $req ) { status_header( 404 ); echo 'Not found.'; return; }

		$is_owner = false;
		if ( is_user_logged_in() ) {
			$student = self::get_student_for_user( get_current_user_id() );
			$is_owner = $student && (int) $student->student_id === (int) $req->student_id;
		}
		if ( ! $is_owner && ! current_user_can( 'kcms_manage_certificates' ) ) { status_header( 403 ); echo 'Not allowed.'; return; }

		$stu_table = KCMS_DB::t( 'students' );
		$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$stu_table} WHERE student_id=%d", $req->student_id ) );
		$rec_table = KCMS_DB::t( 'academic_records' );
		$academic = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$rec_table} WHERE student_id=%d ORDER BY record_id DESC LIMIT 1", $req->student_id ) );
		$types = json_decode( $req->certificate_type, true ) ?: array();
		include KCMS_DIR . 'templates/certificate-print.php';
	}
}
KCMS_Certificate::init();
