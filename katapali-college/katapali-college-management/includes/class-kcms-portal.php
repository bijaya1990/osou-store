<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* [kcms_my_dashboard] - a single shortcode a teacher or student can drop
   on their portal page to see their own submission history + status,
   without needing wp-admin access. Read-only, as required by the spec
   ("Edit on Portal: No" for all three systems). */
class KCMS_Portal {

	public static function init() {
		add_shortcode( 'kcms_my_dashboard', array( __CLASS__, 'shortcode_dashboard' ) );
	}

	public static function shortcode_dashboard() {
		if ( ! is_user_logged_in() ) {
			return '<div class="kcms-box kcms-notice">Please log in to view your dashboard. <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Log in</a></div>';
		}
		global $wpdb;
		$uid = get_current_user_id();
		ob_start();
		echo '<div class="kcms-dashboard">';

		$emp = KCMS_Leave::get_employee_for_user( $uid );
		if ( $emp ) {
			$table = KCMS_DB::t( 'leave_applications' );
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE emp_id=%d ORDER BY application_id DESC LIMIT 20", $emp->emp_id ) );
			echo '<h3>My Leave Applications</h3>';
			if ( $rows ) {
				echo '<table class="kcms-table"><tr><th>No.</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th><th></th></tr>';
				foreach ( $rows as $r ) {
					$print = esc_url( add_query_arg( array( 'kcms_print' => 'leave', 'kcms_id' => $r->application_id ), home_url( '/' ) ) );
					echo '<tr><td>' . esc_html( $r->application_number ) . '</td><td>' . esc_html( $r->leave_type ) . '</td><td>' . esc_html( $r->from_date ) . '</td><td>' . esc_html( $r->to_date ) . '</td><td>' . esc_html( $r->number_of_days ) . '</td><td><span class="kcms-badge kcms-badge-' . esc_attr( $r->status ) . '">' . esc_html( ucfirst( $r->status ) ) . '</span></td><td><a href="' . $print . '" target="_blank">View/Print</a></td></tr>';
				}
				echo '</table>';
			} else {
				echo '<p>No leave applications submitted yet.</p>';
			}
		}

		$student = KCMS_Certificate::get_student_for_user( $uid );
		if ( $student ) {
			$table = KCMS_DB::t( 'certificate_requests' );
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE student_id=%d ORDER BY request_id DESC LIMIT 20", $student->student_id ) );
			echo '<h3>My Certificate/Marksheet Requests</h3>';
			if ( $rows ) {
				echo '<table class="kcms-table"><tr><th>Ref No.</th><th>Type(s)</th><th>Requested</th><th>Status</th><th></th></tr>';
				foreach ( $rows as $r ) {
					$types = implode( ', ', (array) json_decode( $r->certificate_type, true ) );
					$print = esc_url( add_query_arg( array( 'kcms_print' => 'certificate', 'kcms_id' => $r->request_id ), home_url( '/' ) ) );
					echo '<tr><td>' . esc_html( $r->request_number ) . '</td><td>' . esc_html( $types ) . '</td><td>' . esc_html( $r->date_requested ) . '</td><td><span class="kcms-badge kcms-badge-' . esc_attr( $r->status ) . '">' . esc_html( ucfirst( $r->status ) ) . '</span></td><td><a href="' . $print . '" target="_blank">View/Print</a></td></tr>';
				}
				echo '</table>';
			} else {
				echo '<p>No certificate requests submitted yet.</p>';
			}
			echo do_shortcode( '[kcms_my_id_card]' );
		}

		if ( ! $emp && ! $student ) {
			echo '<p>No records linked to your account yet. Please contact the college office.</p>';
		}
		echo '</div>';
		return ob_get_clean();
	}
}
KCMS_Portal::init();
