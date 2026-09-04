<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* The limited "College Staff Admin" role given to accounts created via
   the Add College Admin screen. Cloned from WordPress's built-in Editor
   role (edit/publish/delete posts, upload media - well-tested, exactly
   the level of access "manage content, nothing structural" needs) minus
   the Pages capabilities (not part of what was asked for) and, since it
   was never an Editor to begin with, it never had manage_options,
   edit_theme_options, install/edit plugins or themes, or manage users -
   those stay administrator-only. */
class KAP_Role {

	const ROLE = 'kc_staff_admin';

	public static function activate() {
		self::install_role();
	}

	public static function install_role() {
		if ( ! get_role( self::ROLE ) ) {
			$editor = get_role( 'editor' );
			$caps = $editor ? $editor->capabilities : array();
			foreach ( array( 'edit_pages', 'edit_others_pages', 'edit_published_pages', 'publish_pages', 'delete_pages', 'delete_others_pages', 'delete_published_pages', 'delete_private_pages', 'edit_private_pages', 'read_private_pages' ) as $cap ) {
				unset( $caps[ $cap ] );
			}
			add_role( self::ROLE, 'College Staff Admin', $caps );
		}
	}

	public static function is_staff_admin( $user = null ) {
		$user = $user ?: wp_get_current_user();
		return in_array( self::ROLE, (array) $user->roles, true );
	}
}
