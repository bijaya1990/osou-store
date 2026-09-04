<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KSR_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_ksr_import', array( __CLASS__, 'handle_import' ) );
		add_action( 'admin_post_ksr_save_student', array( __CLASS__, 'handle_save_student' ) );
		add_action( 'admin_post_ksr_delete_student', array( __CLASS__, 'handle_delete_student' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		// Runs before the wp-admin page chrome is output, so a card can be
		// rendered as its own clean standalone HTML document (own <html>,
		// no admin menu/toolbar) instead of being trapped inside it.
		add_action( 'admin_init', array( __CLASS__, 'maybe_render_card' ) );
	}

	public static function maybe_render_card() {
		if ( ( $_GET['page'] ?? '' ) !== 'ksr-students' || ! isset( $_GET['ksr_card'], $_GET['id'] ) ) return;
		if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Not allowed.' );
		$type = sanitize_key( $_GET['ksr_card'] );
		$id   = (int) $_GET['id'];
		check_admin_referer( 'ksr_card_' . $type . '_' . $id );
		KSR_Cards::render( $type, $id ); // outputs a full standalone HTML page and exits
	}

	public static function assets( $hook ) {
		if ( strpos( $hook, 'ksr-' ) === false && strpos( $hook, 'page_ksr' ) === false ) return;
		wp_enqueue_media();
		wp_enqueue_style( 'ksr-admin', KSR_URI . '/assets/css/admin.css', array(), KSR_VERSION );
		wp_enqueue_script( 'ksr-admin', KSR_URI . '/assets/js/admin.js', array( 'jquery' ), KSR_VERSION, true );
	}

	public static function menu() {
		$cap = 'edit_posts';
		add_menu_page( 'Student Records', 'Student Records', $cap, 'ksr-students', array( __CLASS__, 'page_list' ), 'dashicons-groups', 27 );
		add_submenu_page( 'ksr-students', 'All Students', 'All Students', $cap, 'ksr-students', array( __CLASS__, 'page_list' ) );
		add_submenu_page( 'ksr-students', 'Import Students', 'Import Students', $cap, 'ksr-import', array( __CLASS__, 'page_import' ) );
		add_submenu_page( 'ksr-students', 'Edit Student', '', $cap, 'ksr-edit', array( __CLASS__, 'page_edit' ) );
	}

	/* ------------------------------- list ---------------------------------- */

	public static function page_list() {
		global $wpdb;
		$table = KSR_Install::table_name();

		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$batch  = isset( $_GET['batch'] ) ? sanitize_text_field( wp_unslash( $_GET['batch'] ) ) : '';
		$paged  = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$per_page = 20;
		$offset = ( $paged - 1 ) * $per_page;

		$where = array( '1=1' );
		$args  = array();
		if ( $search !== '' ) {
			$where[] = '(name LIKE %s OR roll_no LIKE %s)';
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$args[] = $like; $args[] = $like;
		}
		if ( $batch !== '' ) {
			$where[] = 'batch_year = %s';
			$args[] = $batch;
		}
		$where_sql = implode( ' AND ', $where );

		$total = (int) $wpdb->get_var( $args ? $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE $where_sql", $args ) : "SELECT COUNT(*) FROM $table WHERE $where_sql" );

		$sql = "SELECT * FROM $table WHERE $where_sql ORDER BY batch_year DESC, name ASC LIMIT %d OFFSET %d";
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $args, array( $per_page, $offset ) ) ) );

		$batches = $wpdb->get_col( "SELECT DISTINCT batch_year FROM $table ORDER BY batch_year DESC" );

		echo '<div class="wrap ksr-wrap"><h1 class="wp-heading-inline">Student Records</h1> ';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=ksr-import' ) ) . '" class="page-title-action">Import Students</a>';
		echo '<hr class="wp-header-end">';

		echo '<form method="get" class="ksr-filters"><input type="hidden" name="page" value="ksr-students">';
		echo '<input type="search" name="s" value="' . esc_attr( $search ) . '" placeholder="Search name or roll no">';
		echo '<select name="batch"><option value="">All batches</option>';
		foreach ( $batches as $b ) {
			echo '<option value="' . esc_attr( $b ) . '"' . selected( $batch, $b, false ) . '>' . esc_html( $b ) . '</option>';
		}
		echo '</select> <button class="button">Filter</button></form>';

		echo '<table class="widefat striped ksr-table"><thead><tr><th>Photo</th><th>Name</th><th>Roll No</th><th>Batch</th><th>Stream</th><th>ID Card No.</th><th>Actions</th></tr></thead><tbody>';
		if ( ! $rows ) {
			echo '<tr><td colspan="7">No students found. <a href="' . esc_url( admin_url( 'admin.php?page=ksr-import' ) ) . '">Import an Excel/CSV file</a> to get started.</td></tr>';
		}
		foreach ( $rows as $r ) {
			$photo = $r->photo_attachment_id ? wp_get_attachment_image_url( $r->photo_attachment_id, 'thumbnail' ) : '';
			$edit_url = admin_url( 'admin.php?page=ksr-edit&id=' . $r->id );
			$id_card_url = self::card_url( $r->id, 'id' );
			$lib_card_url = self::card_url( $r->id, 'library' );
			$del_url = wp_nonce_url( admin_url( 'admin-post.php?action=ksr_delete_student&id=' . $r->id ), 'ksr_delete_' . $r->id );
			echo '<tr>';
			echo '<td>' . ( $photo ? '<img src="' . esc_url( $photo ) . '" class="ksr-thumb">' : '<span class="ksr-thumb ksr-thumb-empty"></span>' ) . '</td>';
			echo '<td><strong>' . esc_html( $r->name ) . '</strong></td>';
			echo '<td>' . esc_html( $r->roll_no ) . '</td>';
			echo '<td>' . esc_html( $r->batch_year ) . '</td>';
			echo '<td>' . esc_html( $r->stream ) . '</td>';
			echo '<td>' . esc_html( $r->id_card_no ) . '</td>';
			echo '<td class="ksr-actions">';
			echo '<a href="' . esc_url( $edit_url ) . '">Edit</a> | ';
			echo '<a href="' . esc_url( $id_card_url ) . '" target="_blank">ID Card</a> | ';
			echo '<a href="' . esc_url( $lib_card_url ) . '" target="_blank">Library Card</a> | ';
			echo '<a href="' . esc_url( $del_url ) . '" class="ksr-del" onclick="return confirm(\'Delete this student record?\');">Delete</a>';
			echo '</td></tr>';
		}
		echo '</tbody></table>';

		$total_pages = (int) ceil( $total / $per_page );
		if ( $total_pages > 1 ) {
			echo '<div class="tablenav"><div class="tablenav-pages">' . paginate_links( array(
				'base' => add_query_arg( 'paged', '%#%' ),
				'current' => $paged, 'total' => $total_pages,
			) ) . '</div></div>';
		}
		echo '</div>';
	}

	public static function card_url( $student_id, $type ) {
		return wp_nonce_url( admin_url( 'admin.php?page=ksr-students&ksr_card=' . $type . '&id=' . $student_id ), 'ksr_card_' . $type . '_' . $student_id );
	}

	/* ------------------------------ import ---------------------------------- */

	public static function page_import() {
		echo '<div class="wrap ksr-wrap"><h1>Import Students</h1>';

		if ( isset( $_GET['ksr_result'] ) ) {
			$r = get_transient( 'ksr_import_result_' . get_current_user_id() );
			if ( $r ) {
				if ( is_wp_error( $r ) ) {
					echo '<div class="notice notice-error"><p>' . esc_html( $r->get_error_message() ) . '</p></div>';
				} else {
					echo '<div class="notice notice-success"><p>Import complete - ' . intval( $r['inserted'] ) . ' added, ' . intval( $r['updated'] ) . ' updated, ' . intval( $r['skipped'] ) . ' skipped (blank roll no/name).</p></div>';
				}
				delete_transient( 'ksr_import_result_' . get_current_user_id() );
			}
		}

		echo '<p>Upload the admission register Excel (.xlsx) or CSV export. Matching is done by <strong>Roll No</strong> - importing the same file again safely updates existing records instead of duplicating them.</p>';
		echo '<p><strong>Note:</strong> the file has no student photos, so upload each photo individually from the student\'s Edit screen after import. Aadhaar numbers are stored for office records only and are never shown on any public page or printed card.</p>';

		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'ksr_import' );
		echo '<input type="hidden" name="action" value="ksr_import">';
		echo '<table class="form-table"><tr><th><label>Batch Year</label></th><td><input type="text" name="batch_year" placeholder="e.g. 2025-26" required> <p class="description">Used to group this file\'s students for the Alumni Directory.</p></td></tr>';
		echo '<tr><th><label>File</label></th><td><input type="file" name="ksr_file" accept=".xlsx,.csv" required></td></tr></table>';
		submit_button( 'Import' );
		echo '</form></div>';
	}

	public static function handle_import() {
		if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Not allowed.' );
		check_admin_referer( 'ksr_import' );

		$batch_year = isset( $_POST['batch_year'] ) ? sanitize_text_field( wp_unslash( $_POST['batch_year'] ) ) : '';
		if ( $batch_year === '' || empty( $_FILES['ksr_file']['tmp_name'] ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=ksr-import' ) );
			exit;
		}

		$filename = sanitize_file_name( $_FILES['ksr_file']['name'] );
		$is_csv = ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) === 'csv' );

		$result = KSR_Importer::import( $_FILES['ksr_file']['tmp_name'], $batch_year, $is_csv );
		set_transient( 'ksr_import_result_' . get_current_user_id(), $result, 60 );

		wp_safe_redirect( admin_url( 'admin.php?page=ksr-import&ksr_result=1' ) );
		exit;
	}

	/* ------------------------------- edit ------------------------------------ */

	public static function page_edit() {
		global $wpdb;
		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$table = KSR_Install::table_name();
		$s = $id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) ) : null;
		if ( ! $s ) { echo '<div class="wrap"><p>Student not found.</p></div>'; return; }
		$fields = json_decode( $s->fields_json, true );
		if ( ! is_array( $fields ) ) $fields = array();

		$photo_url = $s->photo_attachment_id ? wp_get_attachment_image_url( $s->photo_attachment_id, 'medium' ) : '';

		echo '<div class="wrap ksr-wrap"><h1>Edit Student</h1>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'ksr_save_student' );
		echo '<input type="hidden" name="action" value="ksr_save_student"><input type="hidden" name="id" value="' . esc_attr( $s->id ) . '">';

		echo '<div class="ksr-edit-grid"><div class="ksr-edit-photo">';
		echo '<img id="ksr-photo-preview" src="' . esc_url( $photo_url ?: KSR_URI . '/assets/no-photo.svg' ) . '" alt="">';
		echo '<input type="hidden" name="photo_attachment_id" id="ksr-photo-id" value="' . esc_attr( $s->photo_attachment_id ) . '">';
		echo '<p><button type="button" class="button" id="ksr-upload-photo-btn">Upload / Change Photo</button></p>';
		echo '</div><div class="ksr-edit-fields"><table class="form-table">';

		$row = function ( $label, $name, $value ) {
			echo '<tr><th>' . esc_html( $label ) . '</th><td><input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text"></td></tr>';
		};
		$row( 'Name', 'name', $s->name );
		$row( 'Roll No', 'roll_no', $s->roll_no );
		$row( 'Batch Year', 'batch_year', $s->batch_year );
		$row( 'Stream', 'stream', $s->stream );
		$row( 'Date of Birth', 'dob', $s->dob );
		$row( 'Mobile', 'mobile', $s->mobile );
		$row( 'ID Card No.', 'id_card_no', $s->id_card_no );
		echo '</table></div></div>';

		echo '<h2>Imported Details (read-only)</h2><table class="form-table ksr-readonly-fields">';
		$labels = array(
			'father_name' => "Father's Name", 'mother_name' => "Mother's Name", 'aadhaar_no' => 'Aadhaar No (office use only, never printed)',
			'email' => 'Email', 'board' => 'Board', 'gender' => 'Gender', 'blood_group' => 'Blood Group', 'address' => 'Address',
			'category' => 'Category', 'religion' => 'Religion', 'slc_no' => 'SLC No', 'slc_date' => 'SLC Date',
			'marks_percent' => 'Marks', 'admission_date' => 'Admission Date', 'admission_type' => 'Type of Admission',
			'hostel_allot' => 'Hostel Allot', 'subject_name' => 'Subject', 'tc_date' => 'TC Date', 'amount' => 'Amount',
			'barcode_no' => 'Barcode No', 'sl_no' => 'Sl No',
		);
		foreach ( $labels as $key => $label ) {
			if ( empty( $fields[ $key ] ) ) continue;
			echo '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( $fields[ $key ] ) . '</td></tr>';
		}
		echo '</table>';

		submit_button( 'Save Changes' );
		echo '</form></div>';
	}

	public static function handle_save_student() {
		if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Not allowed.' );
		check_admin_referer( 'ksr_save_student' );
		global $wpdb;
		$id = (int) ( $_POST['id'] ?? 0 );
		if ( ! $id ) wp_die( 'Missing student id.' );

		$data = array(
			'name'                 => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'roll_no'              => sanitize_text_field( wp_unslash( $_POST['roll_no'] ?? '' ) ),
			'batch_year'           => sanitize_text_field( wp_unslash( $_POST['batch_year'] ?? '' ) ),
			'stream'               => sanitize_text_field( wp_unslash( $_POST['stream'] ?? '' ) ),
			'dob'                  => sanitize_text_field( wp_unslash( $_POST['dob'] ?? '' ) ),
			'mobile'               => sanitize_text_field( wp_unslash( $_POST['mobile'] ?? '' ) ),
			'id_card_no'           => sanitize_text_field( wp_unslash( $_POST['id_card_no'] ?? '' ) ),
			'photo_attachment_id'  => (int) ( $_POST['photo_attachment_id'] ?? 0 ),
		);
		$wpdb->update( KSR_Install::table_name(), $data, array( 'id' => $id ) );

		wp_safe_redirect( admin_url( 'admin.php?page=ksr-edit&id=' . $id . '&saved=1' ) );
		exit;
	}

	public static function handle_delete_student() {
		if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Not allowed.' );
		$id = (int) ( $_GET['id'] ?? 0 );
		check_admin_referer( 'ksr_delete_' . $id );
		global $wpdb;
		$wpdb->delete( KSR_Install::table_name(), array( 'id' => $id ) );
		wp_safe_redirect( admin_url( 'admin.php?page=ksr-students' ) );
		exit;
	}
}
