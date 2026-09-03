<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Creates/upgrades the plugin's own DB table on activation, and also on
   every admin page load (dbDelta is idempotent) so an update that adds a
   column never leaves an old table behind - the same version-check
   pattern used by the theme's Applications feature. */
class KSR_Install {

	const DB_VERSION = '1';

	public static function activate() {
		self::maybe_upgrade();
	}

	public static function maybe_upgrade() {
		if ( get_option( 'ksr_db_version' ) === self::DB_VERSION ) return;
		self::create_table();
		update_option( 'ksr_db_version', self::DB_VERSION );
	}

	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ksr_students';
	}

	private static function create_table() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table   = self::table_name();
		$charset = $wpdb->get_charset_collate();

		/* Only the fields we actually query/filter/print by get their own
		   column; every other Excel column (father's name, Aadhaar, marks,
		   admission date, etc.) rides along in fields_json so the importer
		   never has to be re-migrated when a college's Excel layout adds or
		   renames a column. */
		$sql = "CREATE TABLE $table (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			roll_no VARCHAR(64) NOT NULL,
			name VARCHAR(190) NOT NULL,
			batch_year VARCHAR(20) NOT NULL DEFAULT '',
			stream VARCHAR(100) NOT NULL DEFAULT '',
			dob VARCHAR(20) NOT NULL DEFAULT '',
			mobile VARCHAR(20) NOT NULL DEFAULT '',
			id_card_no VARCHAR(40) NOT NULL DEFAULT '',
			photo_attachment_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			fields_json LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY roll_no (roll_no),
			KEY batch_year (batch_year),
			KEY name (name)
		) $charset;";

		dbDelta( $sql );
	}
}
