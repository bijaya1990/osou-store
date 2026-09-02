<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KCMS_DB {

	public static function t( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'kcms_' . $name;
	}

	public static function create_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();

		$sql = array();

		$sql[] = "CREATE TABLE " . self::t( 'employees' ) . " (
			emp_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NULL,
			name VARCHAR(190) NOT NULL,
			designation VARCHAR(190) NULL,
			department VARCHAR(190) NULL,
			phone_enc TEXT NULL,
			phone_hash VARCHAR(64) NULL,
			dob DATE NULL,
			email VARCHAR(190) NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (emp_id),
			KEY user_id (user_id),
			KEY phone_hash (phone_hash)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::t( 'leave_applications' ) . " (
			application_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			emp_id BIGINT UNSIGNED NOT NULL,
			application_number VARCHAR(60) NOT NULL,
			leave_type VARCHAR(10) NOT NULL,
			leave_year INT NOT NULL,
			from_date DATE NOT NULL,
			to_date DATE NOT NULL,
			number_of_days INT NOT NULL,
			reason TEXT NULL,
			remarks TEXT NULL,
			signature_file VARCHAR(255) NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'submitted',
			submitted_date DATETIME NULL,
			approval_date DATETIME NULL,
			principal_remarks TEXT NULL,
			principal_signature VARCHAR(255) NULL,
			otp_token VARCHAR(255) NULL,
			otp_verified TINYINT(1) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (application_id),
			UNIQUE KEY application_number (application_number),
			KEY emp_id (emp_id),
			KEY status (status)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::t( 'students' ) . " (
			student_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NULL,
			name VARCHAR(190) NOT NULL,
			father_name VARCHAR(190) NULL,
			college_roll_no VARCHAR(60) NOT NULL,
			university_roll_no VARCHAR(60) NULL,
			registration_no VARCHAR(60) NULL,
			email VARCHAR(190) NULL,
			phone_enc TEXT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (student_id),
			UNIQUE KEY college_roll_no (college_roll_no),
			KEY user_id (user_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::t( 'academic_records' ) . " (
			record_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			student_id BIGINT UNSIGNED NOT NULL,
			session VARCHAR(20) NULL,
			class VARCHAR(60) NULL,
			semester VARCHAR(20) NULL,
			branch VARCHAR(100) NULL,
			result_status VARCHAR(20) NULL,
			marks_obtained VARCHAR(20) NULL,
			percentage_cgpa VARCHAR(20) NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (record_id),
			KEY student_id (student_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::t( 'certificate_requests' ) . " (
			request_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			request_number VARCHAR(60) NOT NULL,
			student_id BIGINT UNSIGNED NOT NULL,
			certificate_type TEXT NULL,
			session VARCHAR(20) NULL,
			reason VARCHAR(190) NULL,
			num_copies INT NOT NULL DEFAULT 1,
			delivery_method VARCHAR(30) NULL,
			remarks TEXT NULL,
			signature_file VARCHAR(255) NULL,
			date_requested DATETIME NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			approval_date DATETIME NULL,
			principal_signature VARCHAR(255) NULL,
			principal_date DATETIME NULL,
			otp_verified TINYINT(1) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (request_id),
			UNIQUE KEY request_number (request_number),
			KEY student_id (student_id),
			KEY status (status)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::t( 'id_cards' ) . " (
			id_card_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			student_id BIGINT UNSIGNED NOT NULL,
			roll_number VARCHAR(60) NOT NULL,
			name VARCHAR(190) NOT NULL,
			father_name VARCHAR(190) NULL,
			dob DATE NULL,
			address TEXT NULL,
			mobile_enc TEXT NULL,
			phone_hash VARCHAR(64) NULL,
			email VARCHAR(190) NULL,
			class VARCHAR(60) NULL,
			branch VARCHAR(100) NULL,
			session VARCHAR(20) NULL,
			blood_group VARCHAR(10) NULL,
			gender VARCHAR(20) NULL,
			photo_path VARCHAR(255) NULL,
			photo_upload_date DATETIME NULL,
			id_card_generated TINYINT(1) NOT NULL DEFAULT 0,
			library_card_generated TINYINT(1) NOT NULL DEFAULT 0,
			member_id VARCHAR(60) NULL,
			issue_date DATE NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id_card_id),
			UNIQUE KEY roll_number (roll_number),
			KEY student_id (student_id),
			KEY phone_hash (phone_hash)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::t( 'excel_uploads' ) . " (
			upload_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			upload_date DATETIME NOT NULL,
			file_name VARCHAR(255) NULL,
			total_records INT NOT NULL DEFAULT 0,
			success_count INT NOT NULL DEFAULT 0,
			error_count INT NOT NULL DEFAULT 0,
			error_details LONGTEXT NULL,
			uploaded_by BIGINT UNSIGNED NULL,
			processing_status VARCHAR(20) NOT NULL DEFAULT 'completed',
			PRIMARY KEY (upload_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::t( 'otp_tokens' ) . " (
			otp_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			context VARCHAR(60) NOT NULL,
			ref_id BIGINT UNSIGNED NOT NULL,
			otp_hash VARCHAR(255) NOT NULL,
			attempts INT NOT NULL DEFAULT 0,
			verified TINYINT(1) NOT NULL DEFAULT 0,
			expires_at DATETIME NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (otp_id),
			KEY context_ref (context, ref_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::t( 'audit_log' ) . " (
			log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NULL,
			action VARCHAR(100) NOT NULL,
			object_type VARCHAR(60) NULL,
			object_id BIGINT UNSIGNED NULL,
			details TEXT NULL,
			ip_address VARCHAR(60) NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (log_id),
			KEY object (object_type, object_id)
		) $charset;";

		foreach ( $sql as $s ) {
			dbDelta( $s );
		}

		update_option( 'kcms_db_version', KCMS_DB_VERSION );
	}

	public static function log( $action, $object_type = '', $object_id = 0, $details = '' ) {
		global $wpdb;
		$wpdb->insert( self::t( 'audit_log' ), array(
			'user_id'     => get_current_user_id(),
			'action'      => $action,
			'object_type' => $object_type,
			'object_id'   => $object_id,
			'details'     => is_array( $details ) ? wp_json_encode( $details ) : $details,
			'ip_address'  => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			'created_at'  => current_time( 'mysql' ),
		) );
	}
}
