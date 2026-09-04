<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Public-facing shortcodes. Both only ever expose safe, non-sensitive
   fields (name, roll no, stream, batch) - never DOB, address, mobile,
   Aadhaar, marks, or photo. Those stay admin-only (see KSR_Admin /
   KSR_Cards), matching what was agreed: cards and full records are for
   office use, not something any visitor can pull up by roll number. */
class KSR_Shortcodes {

	public static function init() {
		add_shortcode( 'ksr_students_list', array( __CLASS__, 'students_list_shortcode' ) );
		add_shortcode( 'ksr_alumni_directory', array( __CLASS__, 'alumni_shortcode' ) );
		add_action( 'wp_ajax_ksr_search', array( __CLASS__, 'ajax_search' ) );
		add_action( 'wp_ajax_nopriv_ksr_search', array( __CLASS__, 'ajax_search' ) );
		add_action( 'wp_ajax_ksr_batch_list', array( __CLASS__, 'ajax_batch_list' ) );
		add_action( 'wp_ajax_nopriv_ksr_batch_list', array( __CLASS__, 'ajax_batch_list' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function assets() {
		wp_register_style( 'ksr-public', KSR_URI . '/assets/css/public.css', array(), KSR_VERSION );
		wp_register_script( 'ksr-public', KSR_URI . '/assets/js/public.js', array(), KSR_VERSION, true );
		wp_localize_script( 'ksr-public', 'KSR_DATA', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ksr_search_nonce' ),
		) );
	}

	private static function get_batches() {
		global $wpdb;
		return $wpdb->get_col( "SELECT DISTINCT batch_year FROM " . KSR_Install::table_name() . " ORDER BY batch_year DESC" );
	}

	/* Shared "pick a session, get that session's list" widget - used by
	   both the Students List and Alumni Directory pages (same mechanic,
	   different heading/copy), so selecting a batch always behaves the
	   same way everywhere on the site. */
	private static function render_batch_selector( $id_prefix, $placeholder ) {
		$batches = self::get_batches();
		ob_start();
		?>
		<div class="ksr-batch-box">
			<div class="ksr-batch-row">
				<select id="<?php echo esc_attr( $id_prefix ); ?>-select">
					<option value=""><?php echo esc_html( $placeholder ); ?></option>
					<?php foreach ( $batches as $b ) : ?>
						<option value="<?php echo esc_attr( $b ); ?>"><?php echo esc_html( $b ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="button" class="ksr-batch-btn" data-target="<?php echo esc_attr( $id_prefix ); ?>"><i class="fa-solid fa-list"></i> View List</button>
			</div>
			<div id="<?php echo esc_attr( $id_prefix ); ?>-results" class="ksr-batch-results"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function students_list_shortcode() {
		wp_enqueue_style( 'ksr-public' );
		wp_enqueue_script( 'ksr-public' );
		return self::render_batch_selector( 'ksr-students', 'Select Session' );
	}

	public static function alumni_shortcode() {
		wp_enqueue_style( 'ksr-public' );
		wp_enqueue_script( 'ksr-public' );
		return self::render_batch_selector( 'ksr-alumni', 'Select Session' );
	}

	public static function ajax_batch_list() {
		check_ajax_referer( 'ksr_search_nonce', 'nonce' );
		global $wpdb;
		$batch = isset( $_POST['batch'] ) ? sanitize_text_field( wp_unslash( $_POST['batch'] ) ) : '';
		if ( $batch === '' ) wp_send_json_success( array() );

		$table = KSR_Install::table_name();
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT name, roll_no, stream, fields_json FROM $table WHERE batch_year = %s ORDER BY name ASC", $batch
		) );

		$out = array();
		foreach ( $rows as $r ) {
			$fields = json_decode( $r->fields_json, true );
			$subject = is_array( $fields ) && ! empty( $fields['subject_name'] ) ? $fields['subject_name'] : '';
			$out[] = array( 'name' => $r->name, 'roll_no' => $r->roll_no, 'stream' => $r->stream, 'subject' => $subject );
		}
		wp_send_json_success( $out );
	}

	/* Kept for any page still using the old free-text name/roll search box. */
	public static function ajax_search() {
		check_ajax_referer( 'ksr_search_nonce', 'nonce' );
		global $wpdb;
		$q = isset( $_POST['q'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['q'] ) ) ) : '';
		if ( strlen( $q ) < 2 ) wp_send_json_success( array() );

		$table = KSR_Install::table_name();
		$like  = '%' . $wpdb->esc_like( $q ) . '%';
		$rows  = $wpdb->get_results( $wpdb->prepare(
			"SELECT name, roll_no, stream, batch_year FROM $table WHERE roll_no LIKE %s OR name LIKE %s ORDER BY name ASC LIMIT 20",
			$like, $like
		) );

		$out = array();
		foreach ( $rows as $r ) {
			$out[] = array(
				'name' => $r->name, 'roll_no' => $r->roll_no,
				'stream' => $r->stream, 'batch' => $r->batch_year,
			);
		}
		wp_send_json_success( $out );
	}
}
