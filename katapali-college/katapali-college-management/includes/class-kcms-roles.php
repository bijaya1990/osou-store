<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KCMS_Roles {

	public static function register() {
		add_role( 'kcms_teacher', 'Teacher / Employee', array( 'read' => true ) );
		add_role( 'kcms_student', 'Student', array( 'read' => true ) );
	}

	public static function add_caps() {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( array( 'kcms_manage_leave', 'kcms_manage_certificates', 'kcms_manage_idcards', 'kcms_manage_settings' ) as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	public static function is_teacher( $user_id = 0 ) {
		$user_id = $user_id ?: get_current_user_id();
		$u = get_userdata( $user_id );
		return $u && in_array( 'kcms_teacher', (array) $u->roles, true );
	}

	public static function is_student( $user_id = 0 ) {
		$user_id = $user_id ?: get_current_user_id();
		$u = get_userdata( $user_id );
		return $u && in_array( 'kcms_student', (array) $u->roles, true );
	}
}
