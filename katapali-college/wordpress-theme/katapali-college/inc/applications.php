<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Stores every CLC / C.L. / Certificate-Marksheet application submitted
   through the homepage "Online Applications" bar (front-page.php +
   assets/js/apply-forms.js), so the office has a permanent record even
   though applicants themselves never log in. Nothing here requires an
   applicant account - only wp-admin viewing is capability-gated. */

define( 'KC_APPS_DB_VERSION', '1' );

function kc_apps_table() {
	global $wpdb;
	return $wpdb->prefix . 'kc_applications';
}

function kc_apps_create_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();
	$table = kc_apps_table();
	$sql = "CREATE TABLE {$table} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		app_type VARCHAR(20) NOT NULL,
		applicant_name VARCHAR(190) NOT NULL,
		reference VARCHAR(100) NULL,
		fields_json LONGTEXT NULL,
		letter_html LONGTEXT NULL,
		ip_address VARCHAR(60) NULL,
		submitted_at DATETIME NOT NULL,
		PRIMARY KEY (id),
		KEY app_type (app_type),
		KEY submitted_at (submitted_at)
	) $charset;";
	dbDelta( $sql );
	update_option( 'kc_apps_db_version', KC_APPS_DB_VERSION );
}
add_action( 'after_switch_theme', 'kc_apps_create_table' );

function kc_apps_maybe_upgrade() {
	if ( get_option( 'kc_apps_db_version' ) !== KC_APPS_DB_VERSION ) {
		kc_apps_create_table();
	}
}
add_action( 'init', 'kc_apps_maybe_upgrade' );

/* ------------------------------- submission ------------------------------- */
function kc_ajax_save_application() {
	check_ajax_referer( 'kc_apps_nonce', 'nonce' );

	$type = sanitize_key( wp_unslash( $_POST['app_type'] ?? '' ) );
	if ( ! in_array( $type, array( 'clc', 'cl', 'certmark' ), true ) ) {
		wp_send_json_error( 'Invalid application type.' );
	}
	$name = sanitize_text_field( wp_unslash( $_POST['applicant_name'] ?? '' ) );
	$reference = sanitize_text_field( wp_unslash( $_POST['reference'] ?? '' ) );
	$fields_raw = wp_unslash( $_POST['fields'] ?? '{}' );
	$letter_html = wp_kses_post( wp_unslash( $_POST['letter_html'] ?? '' ) );

	if ( ! $name || ! $letter_html ) {
		wp_send_json_error( 'Missing required data.' );
	}

	// re-encode fields through json_decode/json_encode so only well-formed JSON is ever stored
	$fields_decoded = json_decode( $fields_raw, true );
	$fields_json = is_array( $fields_decoded ) ? wp_json_encode( array_map( 'sanitize_text_field', $fields_decoded ) ) : '{}';

	global $wpdb;
	$wpdb->insert( kc_apps_table(), array(
		'app_type'       => $type,
		'applicant_name' => $name,
		'reference'      => $reference,
		'fields_json'    => $fields_json,
		'letter_html'    => $letter_html,
		'ip_address'     => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
		'submitted_at'   => current_time( 'mysql' ),
	) );

	wp_send_json_success( array( 'id' => $wpdb->insert_id ) );
}
add_action( 'wp_ajax_kc_save_application', 'kc_ajax_save_application' );
add_action( 'wp_ajax_nopriv_kc_save_application', 'kc_ajax_save_application' );

/* ------------------------------- admin list ------------------------------- */
function kc_apps_admin_menu() {
	add_submenu_page( 'katapali-college', 'Applications', 'Applications', 'manage_options', 'kc-applications', 'kc_apps_admin_page' );
}
add_action( 'admin_menu', 'kc_apps_admin_menu' );

function kc_apps_type_label( $type ) {
	$labels = array( 'clc' => 'CLC', 'cl' => 'C.L.', 'certmark' => 'Certificate/Marksheet' );
	return $labels[ $type ] ?? $type;
}

function kc_apps_admin_page() {
	global $wpdb;
	$filter = sanitize_key( wp_unslash( $_GET['type'] ?? '' ) );
	$where = $filter ? $wpdb->prepare( 'WHERE app_type=%s', $filter ) : '';
	$rows = $wpdb->get_results( "SELECT id, app_type, applicant_name, reference, submitted_at FROM " . kc_apps_table() . " {$where} ORDER BY id DESC LIMIT 300" );
	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . kc_apps_table() );
	?>
	<div class="wrap">
		<h1>Online Applications</h1>
		<p>Every CLC / C.L. / Certificate-Marksheet application submitted from the homepage is recorded here automatically (<?php echo esc_html( $total ); ?> total).</p>
		<ul class="subsubsub">
			<li><a href="<?php echo esc_url( remove_query_arg( 'type' ) ); ?>" <?php echo ! $filter ? 'class="current"' : ''; ?>>All</a> |</li>
			<li><a href="<?php echo esc_url( add_query_arg( 'type', 'clc' ) ); ?>" <?php echo 'clc' === $filter ? 'class="current"' : ''; ?>>CLC</a> |</li>
			<li><a href="<?php echo esc_url( add_query_arg( 'type', 'cl' ) ); ?>" <?php echo 'cl' === $filter ? 'class="current"' : ''; ?>>C.L.</a> |</li>
			<li><a href="<?php echo esc_url( add_query_arg( 'type', 'certmark' ) ); ?>" <?php echo 'certmark' === $filter ? 'class="current"' : ''; ?>>Certificate/Marksheet</a></li>
		</ul>
		<table class="widefat striped">
			<tr><th>Date</th><th>Type</th><th>Applicant</th><th>Roll No. / Designation</th><th>Action</th></tr>
			<?php if ( ! $rows ) : ?>
			<tr><td colspan="5">No applications submitted yet.</td></tr>
			<?php endif; ?>
			<?php foreach ( $rows as $r ) :
				$view_url = add_query_arg( array( 'kc_app_view' => $r->id, '_wpnonce' => wp_create_nonce( 'kc_app_view_' . $r->id ) ), home_url( '/' ) );
				?>
			<tr>
				<td><?php echo esc_html( mysql2date( 'd-m-Y h:i A', $r->submitted_at ) ); ?></td>
				<td><?php echo esc_html( kc_apps_type_label( $r->app_type ) ); ?></td>
				<td><?php echo esc_html( $r->applicant_name ); ?></td>
				<td><?php echo esc_html( $r->reference ); ?></td>
				<td><a href="<?php echo esc_url( $view_url ); ?>" target="_blank" class="button button-small">View / Download PDF</a></td>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>
	<?php
}

/* ------------------------------- admin view/print ------------------------------- */
function kc_apps_query_vars( $vars ) {
	$vars[] = 'kc_app_view';
	return $vars;
}
add_filter( 'query_vars', 'kc_apps_query_vars' );

function kc_apps_maybe_render_view() {
	$id = absint( get_query_var( 'kc_app_view' ) );
	if ( ! $id ) return;
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'kc_app_view_' . $id ) ) {
		status_header( 403 );
		echo 'Not allowed.';
		exit;
	}
	global $wpdb;
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . kc_apps_table() . " WHERE id=%d", $id ) );
	if ( ! $row ) { status_header( 404 ); echo 'Not found.'; exit; }
	?>
	<!doctype html>
	<html>
	<head>
	<meta charset="utf-8">
	<title>Application - <?php echo esc_html( $row->applicant_name ); ?></title>
	<style>
		body{font-family:Georgia,'Times New Roman',serif;background:#eee;margin:0;padding:30px;}
		.kc-print-btn{text-align:center;margin-bottom:20px;}
		.kc-print-btn button{padding:9px 22px;font-size:1rem;cursor:pointer;}
		.kc-doc{max-width:720px;margin:0 auto;background:#fff;box-shadow:0 8px 30px rgba(0,0,0,.15);padding:44px 48px;color:#111;}
		.kc-doc .kc-p-head{text-align:center;border-bottom:3px double #012D58;padding-bottom:14px;margin-bottom:20px;}
		.kc-doc .kc-p-head h3{margin:0 0 4px;color:#012D58;font-size:1.3rem;}
		.kc-doc .kc-p-title{text-align:center;text-decoration:underline;font-weight:700;margin:18px 0;font-size:1.05rem;}
		.kc-doc .kc-p-body p{line-height:1.8;margin:0 0 14px;text-align:justify;}
		.kc-doc table.kc-p-fields{width:100%;border-collapse:collapse;margin:18px 0;font-size:.92rem;}
		.kc-doc table.kc-p-fields td{border:1px solid #999;padding:8px 12px;}
		.kc-doc table.kc-p-fields td.kc-p-label{background:#f3f3f3;font-weight:bold;width:38%;}
		.kc-doc .kc-p-declaration{font-size:.86rem;line-height:1.7;margin:18px 0;padding:12px 16px;background:#f9f9f9;border-left:3px solid #EBC30F;}
		.kc-doc .kc-p-sign-row{display:flex;justify-content:space-between;margin-top:50px;font-size:.88rem;}
		.kc-doc .kc-p-sign-box{width:46%;}
		.kc-doc .kc-p-sign-box .line{border-top:1px solid #333;margin-top:44px;padding-top:6px;}
		.kc-doc .kc-p-office{margin-top:34px;border:2px solid #012D58;padding:14px 16px;font-size:.82rem;color:#444;}
		@media print{ .kc-print-btn{display:none;} body{background:#fff;padding:0;} .kc-doc{box-shadow:none;} }
	</style>
	</head>
	<body>
	<div class="kc-print-btn"><button onclick="window.print()">Print / Save as PDF</button></div>
	<div class="kc-doc"><?php echo $row->letter_html; ?></div>
	</body>
	</html>
	<?php
	exit;
}
add_action( 'template_redirect', 'kc_apps_maybe_render_view' );
