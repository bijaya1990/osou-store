<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Sequential, human-readable reference numbers:
   Leave:       EMP/CL/2026-001  (per employee, per leave type, per year)
   Certificate: REF-000001       (site-wide sequential) */
class KCMS_Numbering {

	public static function leave_number( $emp_id, $leave_type, $year ) {
		global $wpdb;
		$table = KCMS_DB::t( 'leave_applications' );
		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE emp_id=%d AND leave_type=%s AND leave_year=%d",
			$emp_id, $leave_type, $year
		) );
		return sprintf( 'EMP%d/%s/%d-%03d', $emp_id, strtoupper( $leave_type ), $year, $count + 1 );
	}

	public static function certificate_number() {
		global $wpdb;
		$table = KCMS_DB::t( 'certificate_requests' );
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		return sprintf( 'REF-%06d', $count + 1 );
	}

	public static function member_id( $student_id ) {
		return sprintf( 'LIB-%05d', $student_id );
	}
}
