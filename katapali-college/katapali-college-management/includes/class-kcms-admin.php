<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KCMS_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_kcms_employee_save', array( __CLASS__, 'save_employee' ) );
		add_action( 'admin_post_kcms_employee_delete', array( __CLASS__, 'delete_employee' ) );
		add_action( 'admin_post_kcms_student_save', array( __CLASS__, 'save_student' ) );
		add_action( 'admin_post_kcms_settings_save', array( __CLASS__, 'save_settings' ) );
	}

	public static function menu() {
		add_menu_page( 'Katapali College Management', 'College Management', 'kcms_manage_settings', 'kcms-dashboard', array( __CLASS__, 'page_dashboard' ), 'dashicons-id-alt', 26 );
		add_submenu_page( 'kcms-dashboard', 'Dashboard', 'Dashboard', 'kcms_manage_settings', 'kcms-dashboard', array( __CLASS__, 'page_dashboard' ) );
		add_submenu_page( 'kcms-dashboard', 'Employees', 'Employees', 'kcms_manage_leave', 'kcms-employees', array( __CLASS__, 'page_employees' ) );
		add_submenu_page( 'kcms-dashboard', 'Leave Applications', 'Leave Applications', 'kcms_manage_leave', 'kcms-leave', array( __CLASS__, 'page_leave' ) );
		add_submenu_page( 'kcms-dashboard', 'Students', 'Students', 'kcms_manage_certificates', 'kcms-students', array( __CLASS__, 'page_students' ) );
		add_submenu_page( 'kcms-dashboard', 'Certificate Requests', 'Certificate Requests', 'kcms_manage_certificates', 'kcms-certificates', array( __CLASS__, 'page_certificates' ) );
		add_submenu_page( 'kcms-dashboard', 'ID Cards', 'ID Cards', 'kcms_manage_idcards', 'kcms-idcards', array( __CLASS__, 'page_idcards' ) );
		add_submenu_page( 'kcms-dashboard', 'Uploads Log', 'Uploads Log', 'kcms_manage_idcards', 'kcms-uploads', array( __CLASS__, 'page_uploads' ) );
		add_submenu_page( 'kcms-dashboard', 'Settings', 'Settings', 'kcms_manage_settings', 'kcms-settings', array( __CLASS__, 'page_settings' ) );
	}

	private static function msg() {
		if ( ! empty( $_GET['kcms_msg'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( wp_unslash( $_GET['kcms_msg'] ) ) . '</p></div>';
		}
	}

	/* ---------------------------- Dashboard ---------------------------- */
	public static function page_dashboard() {
		global $wpdb;
		$leave_t = KCMS_DB::t( 'leave_applications' );
		$cert_t  = KCMS_DB::t( 'certificate_requests' );
		$id_t    = KCMS_DB::t( 'id_cards' );

		$leave_pending = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$leave_t} WHERE status='submitted'" );
		$leave_approved = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$leave_t} WHERE status='approved'" );
		$cert_pending = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$cert_t} WHERE status='pending'" );
		$cert_issued = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$cert_t} WHERE status='issued'" );
		$id_total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$id_t}" );
		$id_generated = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$id_t} WHERE id_card_generated=1" );
		?>
		<div class="wrap kcms-admin">
			<h1>Katapali College Management System</h1>
			<p>Leave applications, certificate/marksheet requests and student ID cards - all tracked here.</p>
			<div class="kcms-stat-grid">
				<div class="kcms-stat-card"><div class="n"><?php echo esc_html( $leave_pending ); ?></div><div class="l">Pending Leave Applications</div><a href="<?php echo esc_url( admin_url( 'admin.php?page=kcms-leave' ) ); ?>">Review &rarr;</a></div>
				<div class="kcms-stat-card"><div class="n"><?php echo esc_html( $leave_approved ); ?></div><div class="l">Approved Leaves</div></div>
				<div class="kcms-stat-card"><div class="n"><?php echo esc_html( $cert_pending ); ?></div><div class="l">Pending Certificate Requests</div><a href="<?php echo esc_url( admin_url( 'admin.php?page=kcms-certificates' ) ); ?>">Review &rarr;</a></div>
				<div class="kcms-stat-card"><div class="n"><?php echo esc_html( $cert_issued ); ?></div><div class="l">Certificates Issued</div></div>
				<div class="kcms-stat-card"><div class="n"><?php echo esc_html( $id_total ); ?></div><div class="l">Students in ID Card System</div><a href="<?php echo esc_url( admin_url( 'admin.php?page=kcms-idcards' ) ); ?>">Manage &rarr;</a></div>
				<div class="kcms-stat-card"><div class="n"><?php echo esc_html( $id_generated ); ?></div><div class="l">ID Cards Generated</div></div>
			</div>
			<h2>Quick Links</h2>
			<ul>
				<li>Add teachers/employees under <strong>Employees</strong>, then share the shortcode <code>[kcms_leave_form]</code> on a page for the Leave Application portal.</li>
				<li>Import students under <strong>ID Cards</strong> (Excel/CSV upload), then share <code>[kcms_certificate_form]</code> for the Certificate/Marksheet portal and <code>[kcms_my_id_card]</code> / <code>[kcms_my_dashboard]</code> for the student self-service portal.</li>
				<li>Configure an SMS gateway (optional) under <strong>Settings</strong> - until configured, OTPs are emailed to the applicant instead.</li>
			</ul>
		</div>
		<?php
	}

	/* ---------------------------- Employees ---------------------------- */
	public static function save_employee() {
		if ( ! current_user_can( 'kcms_manage_leave' ) ) wp_die( 'Not allowed.' );
		check_admin_referer( 'kcms_employee_save' );
		global $wpdb;
		$id = absint( $_POST['emp_id'] ?? 0 );
		$now = current_time( 'mysql' );
		$data = array(
			'name'        => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'designation' => sanitize_text_field( wp_unslash( $_POST['designation'] ?? '' ) ),
			'department'  => sanitize_text_field( wp_unslash( $_POST['department'] ?? '' ) ),
			'email'       => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
			'status'      => sanitize_text_field( wp_unslash( $_POST['status'] ?? 'active' ) ),
			'updated_at'  => $now,
		);
		$phone = preg_replace( '/\D/', '', wp_unslash( $_POST['phone'] ?? '' ) );
		if ( $phone ) $data['phone_enc'] = KCMS_Crypto::encrypt( $phone );

		if ( $id ) {
			$wpdb->update( KCMS_DB::t( 'employees' ), $data, array( 'emp_id' => $id ) );
		} else {
			$data['created_at'] = $now;
			$wpdb->insert( KCMS_DB::t( 'employees' ), $data );
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'kcms-employees', 'kcms_msg' => 'Saved.' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function delete_employee() {
		if ( ! current_user_can( 'kcms_manage_leave' ) ) wp_die( 'Not allowed.' );
		check_admin_referer( 'kcms_employee_delete' );
		global $wpdb;
		$wpdb->delete( KCMS_DB::t( 'employees' ), array( 'emp_id' => absint( $_POST['emp_id'] ?? 0 ) ) );
		wp_safe_redirect( add_query_arg( array( 'page' => 'kcms-employees', 'kcms_msg' => 'Deleted.' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function page_employees() {
		global $wpdb;
		$table = KCMS_DB::t( 'employees' );
		$edit_id = absint( $_GET['edit'] ?? 0 );
		$edit_row = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE emp_id=%d", $edit_id ) ) : null;
		$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY name ASC" );
		?>
		<div class="wrap kcms-admin">
			<h1>Employees (Teachers/Staff)</h1>
			<?php self::msg(); ?>
			<div class="kcms-two-col">
				<div class="kcms-panel">
					<h2><?php echo $edit_row ? 'Edit Employee' : 'Add Employee'; ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'kcms_employee_save' ); ?>
						<input type="hidden" name="action" value="kcms_employee_save">
						<input type="hidden" name="emp_id" value="<?php echo esc_attr( $edit_row->emp_id ?? '' ); ?>">
						<p><label>Name<br><input type="text" name="name" required value="<?php echo esc_attr( $edit_row->name ?? '' ); ?>"></label></p>
						<p><label>Designation<br><input type="text" name="designation" value="<?php echo esc_attr( $edit_row->designation ?? '' ); ?>"></label></p>
						<p><label>Department<br><input type="text" name="department" value="<?php echo esc_attr( $edit_row->department ?? '' ); ?>"></label></p>
						<p><label>Email (must match their WordPress login email to auto-link)<br><input type="email" name="email" value="<?php echo esc_attr( $edit_row->email ?? '' ); ?>"></label></p>
						<p><label>Mobile Number<br><input type="text" name="phone" value="<?php echo $edit_row ? esc_attr( KCMS_Crypto::decrypt( $edit_row->phone_enc ) ) : ''; ?>"></label></p>
						<p><label>Status<br><select name="status"><option value="active" <?php selected( $edit_row->status ?? 'active', 'active' ); ?>>Active</option><option value="inactive" <?php selected( $edit_row->status ?? '', 'inactive' ); ?>>Inactive</option></select></label></p>
						<p><button class="button button-primary"><?php echo $edit_row ? 'Update' : 'Add Employee'; ?></button></p>
					</form>
				</div>
				<div class="kcms-panel kcms-panel-wide">
					<h2>All Employees</h2>
					<table class="widefat striped">
						<tr><th>Name</th><th>Designation</th><th>Department</th><th>Email</th><th>Mobile</th><th>Status</th><th>WP Account Linked</th><th></th></tr>
						<?php foreach ( $rows as $r ) : ?>
						<tr>
							<td><?php echo esc_html( $r->name ); ?></td>
							<td><?php echo esc_html( $r->designation ); ?></td>
							<td><?php echo esc_html( $r->department ); ?></td>
							<td><?php echo esc_html( $r->email ); ?></td>
							<td><?php echo esc_html( KCMS_Crypto::mask( $r->phone_enc ) ); ?></td>
							<td><?php echo esc_html( ucfirst( $r->status ) ); ?></td>
							<td><?php echo $r->user_id ? '&#10003; Yes' : '&mdash; not yet'; ?></td>
							<td>
								<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'kcms-employees', 'edit' => $r->emp_id ), admin_url( 'admin.php' ) ) ); ?>">Edit</a> |
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('Delete this employee?');">
									<?php wp_nonce_field( 'kcms_employee_delete' ); ?>
									<input type="hidden" name="action" value="kcms_employee_delete">
									<input type="hidden" name="emp_id" value="<?php echo esc_attr( $r->emp_id ); ?>">
									<button type="submit" class="button-link-delete">Delete</button>
								</form>
							</td>
						</tr>
						<?php endforeach; ?>
					</table>
					<p class="description">New teacher account: create a normal WordPress user (Users &rarr; Add New) with role "Teacher / Employee" and the same email as above - their account links automatically the first time they open the leave application form.</p>
				</div>
			</div>
		</div>
		<?php
	}

	/* ---------------------------- Students ---------------------------- */
	public static function save_student() {
		if ( ! current_user_can( 'kcms_manage_certificates' ) ) wp_die( 'Not allowed.' );
		check_admin_referer( 'kcms_student_save' );
		global $wpdb;
		$id = absint( $_POST['student_id'] ?? 0 );
		$now = current_time( 'mysql' );
		$data = array(
			'name'               => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'father_name'        => sanitize_text_field( wp_unslash( $_POST['father_name'] ?? '' ) ),
			'college_roll_no'    => sanitize_text_field( wp_unslash( $_POST['college_roll_no'] ?? '' ) ),
			'university_roll_no' => sanitize_text_field( wp_unslash( $_POST['university_roll_no'] ?? '' ) ),
			'registration_no'    => sanitize_text_field( wp_unslash( $_POST['registration_no'] ?? '' ) ),
			'email'              => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
			'status'             => sanitize_text_field( wp_unslash( $_POST['status'] ?? 'active' ) ),
			'updated_at'         => $now,
		);
		$phone = preg_replace( '/\D/', '', wp_unslash( $_POST['phone'] ?? '' ) );
		if ( $phone ) $data['phone_enc'] = KCMS_Crypto::encrypt( $phone );

		if ( $id ) {
			$wpdb->update( KCMS_DB::t( 'students' ), $data, array( 'student_id' => $id ) );
		} else {
			$data['created_at'] = $now;
			$wpdb->insert( KCMS_DB::t( 'students' ), $data );
			$id = $wpdb->insert_id;
		}

		$session = sanitize_text_field( wp_unslash( $_POST['session'] ?? '' ) );
		$class = sanitize_text_field( wp_unslash( $_POST['class'] ?? '' ) );
		if ( $session || $class ) {
			$wpdb->insert( KCMS_DB::t( 'academic_records' ), array(
				'student_id'      => $id,
				'session'         => $session,
				'class'           => $class,
				'semester'        => sanitize_text_field( wp_unslash( $_POST['semester'] ?? '' ) ),
				'branch'          => sanitize_text_field( wp_unslash( $_POST['branch'] ?? '' ) ),
				'result_status'   => sanitize_text_field( wp_unslash( $_POST['result_status'] ?? '' ) ),
				'marks_obtained'  => sanitize_text_field( wp_unslash( $_POST['marks_obtained'] ?? '' ) ),
				'percentage_cgpa' => sanitize_text_field( wp_unslash( $_POST['percentage_cgpa'] ?? '' ) ),
				'updated_at'      => $now,
			) );
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'kcms-students', 'kcms_msg' => 'Saved.' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function page_students() {
		global $wpdb;
		$table = KCMS_DB::t( 'students' );
		$edit_id = absint( $_GET['edit'] ?? 0 );
		$edit_row = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE student_id=%d", $edit_id ) ) : null;
		$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY name ASC LIMIT 200" );
		?>
		<div class="wrap kcms-admin">
			<h1>Students</h1>
			<?php self::msg(); ?>
			<p class="description">Bulk-adding students is easiest via <strong>ID Cards &rarr; Import from Excel/CSV</strong>. Use this form for one-off additions or edits.</p>
			<div class="kcms-two-col">
				<div class="kcms-panel">
					<h2><?php echo $edit_row ? 'Edit Student' : 'Add Student'; ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'kcms_student_save' ); ?>
						<input type="hidden" name="action" value="kcms_student_save">
						<input type="hidden" name="student_id" value="<?php echo esc_attr( $edit_row->student_id ?? '' ); ?>">
						<p><label>Name<br><input type="text" name="name" required value="<?php echo esc_attr( $edit_row->name ?? '' ); ?>"></label></p>
						<p><label>Father/Guardian Name<br><input type="text" name="father_name" value="<?php echo esc_attr( $edit_row->father_name ?? '' ); ?>"></label></p>
						<p><label>College Roll No.<br><input type="text" name="college_roll_no" required value="<?php echo esc_attr( $edit_row->college_roll_no ?? '' ); ?>"></label></p>
						<p><label>University Roll No.<br><input type="text" name="university_roll_no" value="<?php echo esc_attr( $edit_row->university_roll_no ?? '' ); ?>"></label></p>
						<p><label>Registration No.<br><input type="text" name="registration_no" value="<?php echo esc_attr( $edit_row->registration_no ?? '' ); ?>"></label></p>
						<p><label>Email (must match their WordPress login email to auto-link)<br><input type="email" name="email" value="<?php echo esc_attr( $edit_row->email ?? '' ); ?>"></label></p>
						<p><label>Mobile Number<br><input type="text" name="phone" value="<?php echo $edit_row ? esc_attr( KCMS_Crypto::decrypt( $edit_row->phone_enc ) ) : ''; ?>"></label></p>
						<p><label>Status<br><select name="status"><option value="active" <?php selected( $edit_row->status ?? 'active', 'active' ); ?>>Active</option><option value="inactive" <?php selected( $edit_row->status ?? '', 'inactive' ); ?>>Inactive</option></select></label></p>
						<h4>Latest academic record (optional - adds a new record row)</h4>
						<p><label>Session<br><input type="text" name="session" placeholder="2025-26"></label></p>
						<p><label>Class<br><input type="text" name="class" placeholder="+3 2nd Year"></label></p>
						<p><label>Branch<br><input type="text" name="branch" placeholder="Science"></label></p>
						<p><label>Result<br><select name="result_status"><option value="pursuing">Pursuing</option><option value="pass">Pass</option><option value="fail">Fail</option><option value="incomplete">Incomplete</option></select></label></p>
						<p><label>Marks Obtained<br><input type="text" name="marks_obtained"></label></p>
						<p><label>Percentage/CGPA<br><input type="text" name="percentage_cgpa"></label></p>
						<p><button class="button button-primary"><?php echo $edit_row ? 'Update' : 'Add Student'; ?></button></p>
					</form>
				</div>
				<div class="kcms-panel kcms-panel-wide">
					<h2>Students (latest 200)</h2>
					<table class="widefat striped">
						<tr><th>Roll No.</th><th>Name</th><th>Email</th><th>Mobile</th><th>WP Account Linked</th><th></th></tr>
						<?php foreach ( $rows as $r ) : ?>
						<tr>
							<td><?php echo esc_html( $r->college_roll_no ); ?></td>
							<td><?php echo esc_html( $r->name ); ?></td>
							<td><?php echo esc_html( $r->email ); ?></td>
							<td><?php echo esc_html( KCMS_Crypto::mask( $r->phone_enc ) ); ?></td>
							<td><?php echo $r->user_id ? '&#10003; Yes' : '&mdash; not yet'; ?></td>
							<td><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'kcms-students', 'edit' => $r->student_id ), admin_url( 'admin.php' ) ) ); ?>">Edit</a></td>
						</tr>
						<?php endforeach; ?>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	/* ---------------------------- Leave ---------------------------- */
	public static function page_leave() {
		global $wpdb;
		$filter = sanitize_text_field( wp_unslash( $_GET['status'] ?? 'submitted' ) );
		$table = KCMS_DB::t( 'leave_applications' );
		$emp_table = KCMS_DB::t( 'employees' );
		$where = $filter && 'all' !== $filter ? $wpdb->prepare( 'WHERE l.status=%s', $filter ) : '';
		$rows = $wpdb->get_results( "SELECT l.*, e.name AS emp_name FROM {$table} l LEFT JOIN {$emp_table} e ON e.emp_id=l.emp_id {$where} ORDER BY l.application_id DESC LIMIT 200" );
		?>
		<div class="wrap kcms-admin">
			<h1>Leave Applications</h1>
			<?php self::msg(); ?>
			<ul class="subsubsub">
				<li><a href="<?php echo esc_url( add_query_arg( 'status', 'submitted' ) ); ?>" <?php echo 'submitted' === $filter ? 'class="current"' : ''; ?>>Pending</a> |</li>
				<li><a href="<?php echo esc_url( add_query_arg( 'status', 'approved' ) ); ?>" <?php echo 'approved' === $filter ? 'class="current"' : ''; ?>>Approved</a> |</li>
				<li><a href="<?php echo esc_url( add_query_arg( 'status', 'rejected' ) ); ?>" <?php echo 'rejected' === $filter ? 'class="current"' : ''; ?>>Rejected</a> |</li>
				<li><a href="<?php echo esc_url( add_query_arg( 'status', 'all' ) ); ?>" <?php echo 'all' === $filter ? 'class="current"' : ''; ?>>All</a></li>
			</ul>
			<table class="widefat striped">
				<tr><th>App. No.</th><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th><th>Action</th></tr>
				<?php if ( ! $rows ) : ?>
				<tr><td colspan="8">No applications found.</td></tr>
				<?php endif; ?>
				<?php foreach ( $rows as $r ) :
					$print = add_query_arg( array( 'kcms_print' => 'leave', 'kcms_id' => $r->application_id ), home_url( '/' ) );
					?>
				<tr>
					<td><?php echo esc_html( $r->application_number ); ?></td>
					<td><?php echo esc_html( $r->emp_name ); ?></td>
					<td><?php echo esc_html( $r->leave_type ); ?></td>
					<td><?php echo esc_html( $r->from_date ); ?></td>
					<td><?php echo esc_html( $r->to_date ); ?></td>
					<td><?php echo esc_html( $r->number_of_days ); ?></td>
					<td><span class="kcms-badge kcms-badge-<?php echo esc_attr( $r->status ); ?>"><?php echo esc_html( ucfirst( $r->status ) ); ?></span></td>
					<td>
						<a href="<?php echo esc_url( $print ); ?>" target="_blank">View/Print</a>
						<?php if ( 'submitted' === $r->status ) : ?>
						<button type="button" class="button button-small kcms-toggle" data-target="kcms-leave-<?php echo (int) $r->application_id; ?>">Decide</button>
						<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="kcms-leave-<?php echo (int) $r->application_id; ?>" class="kcms-inline-form" hidden>
							<?php wp_nonce_field( 'kcms_leave_decision' ); ?>
							<input type="hidden" name="action" value="kcms_leave_decision">
							<input type="hidden" name="id" value="<?php echo (int) $r->application_id; ?>">
							<textarea name="principal_remarks" placeholder="Remarks (optional)"></textarea>
							<label>Principal signature (optional): <input type="file" name="principal_signature"></label>
							<button type="submit" name="decision" value="approved" class="button button-primary button-small">Approve</button>
							<button type="submit" name="decision" value="rejected" class="button button-small">Reject</button>
						</form>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<?php
	}

	/* ---------------------------- Certificates ---------------------------- */
	public static function page_certificates() {
		global $wpdb;
		$filter = sanitize_text_field( wp_unslash( $_GET['status'] ?? 'pending' ) );
		$table = KCMS_DB::t( 'certificate_requests' );
		$stu_table = KCMS_DB::t( 'students' );
		$where = $filter && 'all' !== $filter ? $wpdb->prepare( 'WHERE c.status=%s', $filter ) : '';
		$rows = $wpdb->get_results( "SELECT c.*, s.name AS student_name, s.college_roll_no FROM {$table} c LEFT JOIN {$stu_table} s ON s.student_id=c.student_id {$where} ORDER BY c.request_id DESC LIMIT 200" );
		?>
		<div class="wrap kcms-admin">
			<h1>Certificate / Marksheet Requests</h1>
			<?php self::msg(); ?>
			<ul class="subsubsub">
				<li><a href="<?php echo esc_url( add_query_arg( 'status', 'pending' ) ); ?>" <?php echo 'pending' === $filter ? 'class="current"' : ''; ?>>Pending</a> |</li>
				<li><a href="<?php echo esc_url( add_query_arg( 'status', 'approved' ) ); ?>" <?php echo 'approved' === $filter ? 'class="current"' : ''; ?>>Approved</a> |</li>
				<li><a href="<?php echo esc_url( add_query_arg( 'status', 'issued' ) ); ?>" <?php echo 'issued' === $filter ? 'class="current"' : ''; ?>>Issued</a> |</li>
				<li><a href="<?php echo esc_url( add_query_arg( 'status', 'rejected' ) ); ?>" <?php echo 'rejected' === $filter ? 'class="current"' : ''; ?>>Rejected</a> |</li>
				<li><a href="<?php echo esc_url( add_query_arg( 'status', 'all' ) ); ?>" <?php echo 'all' === $filter ? 'class="current"' : ''; ?>>All</a></li>
			</ul>
			<table class="widefat striped">
				<tr><th>Ref No.</th><th>Student</th><th>Roll No.</th><th>Type(s)</th><th>Copies</th><th>Status</th><th>Action</th></tr>
				<?php if ( ! $rows ) : ?>
				<tr><td colspan="7">No requests found.</td></tr>
				<?php endif; ?>
				<?php foreach ( $rows as $r ) :
					$print = add_query_arg( array( 'kcms_print' => 'certificate', 'kcms_id' => $r->request_id ), home_url( '/' ) );
					$types = implode( ', ', (array) json_decode( $r->certificate_type, true ) );
					?>
				<tr>
					<td><?php echo esc_html( $r->request_number ); ?></td>
					<td><?php echo esc_html( $r->student_name ); ?></td>
					<td><?php echo esc_html( $r->college_roll_no ); ?></td>
					<td><?php echo esc_html( $types ); ?></td>
					<td><?php echo esc_html( $r->num_copies ); ?></td>
					<td><span class="kcms-badge kcms-badge-<?php echo esc_attr( $r->status ); ?>"><?php echo esc_html( ucfirst( $r->status ) ); ?></span></td>
					<td>
						<a href="<?php echo esc_url( $print ); ?>" target="_blank">View/Print</a>
						<?php if ( in_array( $r->status, array( 'pending', 'approved' ), true ) ) : ?>
						<button type="button" class="button button-small kcms-toggle" data-target="kcms-cert-<?php echo (int) $r->request_id; ?>">Decide</button>
						<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="kcms-cert-<?php echo (int) $r->request_id; ?>" class="kcms-inline-form" hidden>
							<?php wp_nonce_field( 'kcms_cert_decision' ); ?>
							<input type="hidden" name="action" value="kcms_cert_decision">
							<input type="hidden" name="id" value="<?php echo (int) $r->request_id; ?>">
							<label>Principal signature (optional): <input type="file" name="principal_signature"></label>
							<?php if ( 'pending' === $r->status ) : ?>
							<button type="submit" name="decision" value="approved" class="button button-primary button-small">Approve</button>
							<button type="submit" name="decision" value="rejected" class="button button-small">Reject</button>
							<?php else : ?>
							<button type="submit" name="decision" value="issued" class="button button-primary button-small">Mark Issued</button>
							<?php endif; ?>
						</form>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<?php
	}

	/* ---------------------------- ID Cards ---------------------------- */
	public static function page_idcards() {
		global $wpdb;
		$table = KCMS_DB::t( 'id_cards' );
		$search = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
		$where = '';
		if ( $search ) {
			$where = $wpdb->prepare( 'WHERE roll_number LIKE %s OR name LIKE %s', '%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		$rows = $wpdb->get_results( "SELECT * FROM {$table} {$where} ORDER BY id_card_id DESC LIMIT 200" );
		?>
		<div class="wrap kcms-admin">
			<h1>ID Card Management</h1>
			<?php self::msg(); ?>
			<div class="kcms-two-col">
				<div class="kcms-panel">
					<h2>Import Students (Excel/CSV)</h2>
					<p><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kcms_download_template' ), 'kcms_download_template' ) ); ?>">Download upload template (.csv)</a></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'kcms_idcard_upload' ); ?>
						<input type="hidden" name="action" value="kcms_idcard_upload">
						<p><input type="file" name="data_file" accept=".xlsx,.csv" required></p>
						<p><button class="button button-primary">Upload &amp; Import</button></p>
					</form>
					<p class="description">Columns (in order): roll_number, name, father_name, dob, address, mobile, email, class, branch, session, blood_group, gender, photo_filename. Re-uploading a roll number that already exists updates that record instead of duplicating it. Photos are uploaded separately per student below.</p>
				</div>
				<div class="kcms-panel kcms-panel-wide">
					<h2>Students</h2>
					<form method="get"><input type="hidden" name="page" value="kcms-idcards"><input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search name or roll no."> <button class="button">Search</button></form>
					<table class="widefat striped">
						<tr><th>Photo</th><th>Roll No.</th><th>Name</th><th>Class</th><th>Session</th><th>ID Card</th><th>Library Card</th><th>Action</th></tr>
						<?php foreach ( $rows as $r ) : ?>
						<tr>
							<td><?php if ( $r->photo_path ) : ?><img src="<?php echo esc_url( $r->photo_path ); ?>" style="width:36px;height:44px;object-fit:cover;"><?php else : ?>&mdash;<?php endif; ?></td>
							<td><?php echo esc_html( $r->roll_number ); ?></td>
							<td><?php echo esc_html( $r->name ); ?></td>
							<td><?php echo esc_html( $r->class ); ?></td>
							<td><?php echo esc_html( $r->session ); ?></td>
							<td><?php echo $r->id_card_generated ? '&#10003;' : '&mdash;'; ?></td>
							<td><?php echo $r->library_card_generated ? '&#10003;' : '&mdash;'; ?></td>
							<td>
								<a href="<?php echo esc_url( add_query_arg( array( 'kcms_print' => 'idcard', 'kcms_id' => $r->id_card_id ), home_url( '/' ) ) ); ?>" target="_blank">ID Card</a> |
								<a href="<?php echo esc_url( add_query_arg( array( 'kcms_print' => 'librarycard', 'kcms_id' => $r->id_card_id ), home_url( '/' ) ) ); ?>" target="_blank">Library Card</a> |
								<button type="button" class="button button-small kcms-toggle" data-target="kcms-photo-<?php echo (int) $r->id_card_id; ?>">Photo</button>
								<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="kcms-photo-<?php echo (int) $r->id_card_id; ?>" class="kcms-inline-form" hidden>
									<?php wp_nonce_field( 'kcms_idcard_photo' ); ?>
									<input type="hidden" name="action" value="kcms_idcard_photo">
									<input type="hidden" name="id" value="<?php echo (int) $r->id_card_id; ?>">
									<input type="file" name="photo" accept="image/*" required>
									<button type="submit" class="button button-small">Upload</button>
								</form>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
									<?php wp_nonce_field( 'kcms_idcard_generate' ); ?>
									<input type="hidden" name="action" value="kcms_idcard_generate">
									<input type="hidden" name="id" value="<?php echo (int) $r->id_card_id; ?>">
									<input type="hidden" name="which" value="id">
									<button type="submit" class="button button-small">Mark ID Generated</button>
								</form>
							</td>
						</tr>
						<?php endforeach; ?>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	public static function page_uploads() {
		global $wpdb;
		$table = KCMS_DB::t( 'excel_uploads' );
		$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY upload_id DESC LIMIT 50" );
		?>
		<div class="wrap kcms-admin">
			<h1>Uploads Log</h1>
			<table class="widefat striped">
				<tr><th>Date</th><th>File</th><th>Total</th><th>Success</th><th>Errors</th><th>Details</th></tr>
				<?php foreach ( $rows as $r ) :
					$errors = json_decode( $r->error_details, true ) ?: array();
					?>
				<tr>
					<td><?php echo esc_html( $r->upload_date ); ?></td>
					<td><?php echo esc_html( $r->file_name ); ?></td>
					<td><?php echo esc_html( $r->total_records ); ?></td>
					<td><?php echo esc_html( $r->success_count ); ?></td>
					<td><?php echo esc_html( $r->error_count ); ?></td>
					<td><?php echo $errors ? '<details><summary>View</summary>' . esc_html( implode( "\n", $errors ) ) . '</details>' : '&mdash;'; ?></td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<?php
	}

	/* ---------------------------- Settings ---------------------------- */
	public static function save_settings() {
		if ( ! current_user_can( 'kcms_manage_settings' ) ) wp_die( 'Not allowed.' );
		check_admin_referer( 'kcms_settings_save' );
		update_option( 'kcms_portal_page_id', absint( $_POST['portal_page_id'] ?? 0 ) );
		$settings = array(
			'gateway'            => sanitize_text_field( wp_unslash( $_POST['gateway'] ?? '' ) ),
			'msg91_authkey'      => sanitize_text_field( wp_unslash( $_POST['msg91_authkey'] ?? '' ) ),
			'msg91_template_id'  => sanitize_text_field( wp_unslash( $_POST['msg91_template_id'] ?? '' ) ),
			'twilio_sid'         => sanitize_text_field( wp_unslash( $_POST['twilio_sid'] ?? '' ) ),
			'twilio_token'       => sanitize_text_field( wp_unslash( $_POST['twilio_token'] ?? '' ) ),
			'twilio_from'        => sanitize_text_field( wp_unslash( $_POST['twilio_from'] ?? '' ) ),
		);
		update_option( 'kcms_sms_settings', $settings );
		wp_safe_redirect( add_query_arg( array( 'page' => 'kcms-settings', 'kcms_msg' => 'Settings saved.' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function page_settings() {
		$s = get_option( 'kcms_sms_settings', array() );
		$portal_page_id = (int) get_option( 'kcms_portal_page_id' );
		?>
		<div class="wrap kcms-admin">
			<h1>Settings</h1>
			<?php self::msg(); ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'kcms_settings_save' ); ?>
				<input type="hidden" name="action" value="kcms_settings_save">

				<h2>Student/Teacher Portal Page</h2>
				<p>Teachers and students never see wp-admin - the moment they log in (and if they ever try to open a wp-admin link directly), they are sent straight to this page instead. Create a page with the <code>[kcms_my_dashboard]</code> shortcode on it and select it here.</p>
				<table class="form-table">
					<tr><th>Portal Page</th><td>
						<?php
						wp_dropdown_pages( array(
							'name'              => 'portal_page_id',
							'selected'          => $portal_page_id,
							'show_option_none'  => '— Select a page —',
							'option_none_value' => '0',
						) );
						?>
					</td></tr>
				</table>

				<h2>SMS Gateway (OTP delivery)</h2>
				<p>Until a gateway is configured here, OTP codes are emailed to the applicant's registered email address instead of SMS - the leave/certificate portals keep working either way.</p>
				<table class="form-table">
					<tr><th>Gateway</th><td>
						<select name="gateway">
							<option value="" <?php selected( $s['gateway'] ?? '', '' ); ?>>None (email OTP)</option>
							<option value="msg91" <?php selected( $s['gateway'] ?? '', 'msg91' ); ?>>MSG91</option>
							<option value="twilio" <?php selected( $s['gateway'] ?? '', 'twilio' ); ?>>Twilio</option>
						</select>
					</td></tr>
					<tr><th>MSG91 Auth Key</th><td><input type="text" class="regular-text" name="msg91_authkey" value="<?php echo esc_attr( $s['msg91_authkey'] ?? '' ); ?>"></td></tr>
					<tr><th>MSG91 OTP Template ID</th><td><input type="text" class="regular-text" name="msg91_template_id" value="<?php echo esc_attr( $s['msg91_template_id'] ?? '' ); ?>"></td></tr>
					<tr><th>Twilio Account SID</th><td><input type="text" class="regular-text" name="twilio_sid" value="<?php echo esc_attr( $s['twilio_sid'] ?? '' ); ?>"></td></tr>
					<tr><th>Twilio Auth Token</th><td><input type="text" class="regular-text" name="twilio_token" value="<?php echo esc_attr( $s['twilio_token'] ?? '' ); ?>"></td></tr>
					<tr><th>Twilio From Number</th><td><input type="text" class="regular-text" name="twilio_from" value="<?php echo esc_attr( $s['twilio_from'] ?? '' ); ?>" placeholder="+1xxxxxxxxxx"></td></tr>
				</table>
				<p><button class="button button-primary">Save Settings</button></p>
			</form>
		</div>
		<?php
	}
}
KCMS_Admin::init();
