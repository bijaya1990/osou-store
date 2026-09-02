<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* [kcms_my_dashboard] - a single shortcode a teacher or student can drop
   on their portal page to see their own submission history + status,
   without needing wp-admin access. Read-only, as required by the spec
   ("Edit on Portal: No" for all three systems). */
class KCMS_Portal {

	public static function init() {
		add_shortcode( 'kcms_my_dashboard', array( __CLASS__, 'shortcode_dashboard' ) );
		add_filter( 'login_redirect', array( __CLASS__, 'login_redirect' ), 10, 3 );
		add_action( 'admin_init', array( __CLASS__, 'block_wp_admin' ) );
		add_filter( 'show_admin_bar', array( __CLASS__, 'hide_admin_bar' ) );
	}

	/* Teachers/students have no business in wp-admin - send them straight
	   to their own portal page (set under College Management -> Settings)
	   the moment they log in, instead of the confusing wp-admin dashboard. */
	public static function portal_url() {
		$page_id = (int) get_option( 'kcms_portal_page_id' );
		return $page_id ? get_permalink( $page_id ) : home_url( '/' );
	}

	private static function is_portal_only_user( $user ) {
		if ( ! $user || is_wp_error( $user ) ) return false;
		$roles = (array) $user->roles;
		return in_array( 'kcms_teacher', $roles, true ) || in_array( 'kcms_student', $roles, true );
	}

	public static function login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		if ( self::is_portal_only_user( $user ) ) {
			return self::portal_url();
		}
		return $redirect_to;
	}

	/* Belt-and-braces: even if a teacher/student bookmarks a wp-admin URL,
	   bounce them back out to the portal page (AJAX calls are left alone,
	   since the leave/certificate forms submit through admin-ajax.php). */
	public static function block_wp_admin() {
		if ( wp_doing_ajax() ) return;
		if ( self::is_portal_only_user( wp_get_current_user() ) ) {
			wp_safe_redirect( self::portal_url() );
			exit;
		}
	}

	public static function hide_admin_bar( $show ) {
		if ( self::is_portal_only_user( wp_get_current_user() ) ) {
			return false;
		}
		return $show;
	}

	public static function shortcode_dashboard() {
		if ( ! is_user_logged_in() ) {
			return KCMS_Login::render_inline_with_tabs( 'Please Log In to Continue' );
		}
		global $wpdb;
		$uid = get_current_user_id();
		ob_start();
		echo '<div class="kcms-dashboard">';

		$emp = KCMS_Leave::get_employee_for_user( $uid );
		if ( $emp ) {
			echo '<h3>Apply for Leave (CL / EL / ML / DL)</h3>';
			echo '<button type="button" class="kcms-btn kcms-btn-primary kcms-toggle" data-target="kcms-inline-leave-form">New Leave Application</button>';
			echo '<div id="kcms-inline-leave-form" hidden style="margin-top:16px;">' . KCMS_Leave::shortcode_form() . '</div>';

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
			echo '<h3>Request a Certificate / Marksheet</h3>';
			echo '<button type="button" class="kcms-btn kcms-btn-primary kcms-toggle" data-target="kcms-inline-cert-form">New Certificate Request</button>';
			echo '<div id="kcms-inline-cert-form" hidden style="margin-top:16px;">' . KCMS_Certificate::shortcode_form() . '</div>';

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
