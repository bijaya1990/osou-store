<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Public-facing shortcodes. Both only ever expose safe, non-sensitive
   fields (name, roll no, stream, batch) - never DOB, address, mobile,
   Aadhaar, marks, or photo. Those stay admin-only (see KSR_Admin /
   KSR_Cards), matching what was agreed: cards and full records are for
   office use, not something any visitor can pull up by roll number. */
class KSR_Shortcodes {

	public static function init() {
		add_shortcode( 'ksr_student_search', array( __CLASS__, 'search_shortcode' ) );
		add_shortcode( 'ksr_alumni_directory', array( __CLASS__, 'alumni_shortcode' ) );
		add_action( 'wp_ajax_ksr_search', array( __CLASS__, 'ajax_search' ) );
		add_action( 'wp_ajax_nopriv_ksr_search', array( __CLASS__, 'ajax_search' ) );
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

	public static function search_shortcode() {
		wp_enqueue_style( 'ksr-public' );
		wp_enqueue_script( 'ksr-public' );
		ob_start();
		?>
		<div class="ksr-search-box">
			<div class="ksr-search-row">
				<input type="text" id="ksr-search-input" placeholder="Enter Roll No or Name to verify a student">
				<button type="button" id="ksr-search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
			</div>
			<div id="ksr-search-results"></div>
		</div>
		<?php
		return ob_get_clean();
	}

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

	public static function alumni_shortcode() {
		global $wpdb;
		$table = KSR_Install::table_name();
		$rows = $wpdb->get_results( "SELECT name, roll_no, stream, batch_year FROM $table ORDER BY batch_year DESC, name ASC" );
		if ( ! $rows ) return '<p class="empty-msg">No student records added yet.</p>';

		$batches = array();
		foreach ( $rows as $r ) {
			$batches[ $r->batch_year ][] = $r;
		}

		wp_enqueue_style( 'ksr-public' );
		wp_enqueue_script( 'ksr-public' );

		ob_start();
		echo '<div class="ksr-alumni">';
		$first = true;
		foreach ( $batches as $batch => $students ) {
			echo '<div class="ksr-alumni-batch">';
			echo '<button type="button" class="ksr-alumni-toggle' . ( $first ? ' open' : '' ) . '"><span>Batch ' . esc_html( $batch ) . '</span> <em>(' . count( $students ) . ' students)</em> <i class="fa-solid fa-chevron-down"></i></button>';
			echo '<div class="ksr-alumni-list"' . ( $first ? '' : ' hidden' ) . '>';
			echo '<table><thead><tr><th>Name</th><th>Roll No</th><th>Stream</th></tr></thead><tbody>';
			foreach ( $students as $s ) {
				echo '<tr><td>' . esc_html( $s->name ) . '</td><td>' . esc_html( $s->roll_no ) . '</td><td>' . esc_html( $s->stream ) . '</td></tr>';
			}
			echo '</tbody></table></div></div>';
			$first = false;
		}
		echo '</div>';
		return ob_get_clean();
	}
}
