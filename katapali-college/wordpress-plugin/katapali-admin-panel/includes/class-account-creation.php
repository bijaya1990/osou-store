<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* "Add College Admin" screen - full site administrators only
   (manage_options, never the limited staff role) fill in Name/User ID/
   Mobile/Email/Password and the account (role: kc_staff_admin) is
   created immediately, same as WordPress's own Users -> Add New. The
   administrator then shares that User ID/Password with the staff
   member directly. A welcome email is still attempted, but it's
   best-effort - never blocks account creation, since mail delivery on
   shared hosting can't be relied on to gate something as basic as
   creating a login. */
class KAP_Account_Creation {

	public static function init() {
		// Priority 20 (after the theme's own admin_menu hook, which
		// registers the 'katapali-college' top-level page at the default
		// priority 10) - registering this submenu before that parent page
		// exists makes WordPress treat it as a phantom top-level page
		// internally (wrong hookname), which then 403s every real
		// administrator trying to open it.
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_post_kap_create_admin', array( __CLASS__, 'handle_create_admin' ) );
	}

	public static function menu() {
		add_submenu_page( 'katapali-college', 'Add College Admin', 'Add College Admin', 'manage_options', 'kap-add-admin', array( __CLASS__, 'page' ) );
	}

	private static function college_name() {
		return get_theme_mod( 'kc_college_name', 'Katapali +3 College, Katapali' );
	}

	public static function page() {
		echo '<div class="wrap"><h1>Add College Admin</h1>';

		if ( isset( $_GET['kap_done'] ) ) {
			$uname = isset( $_GET['u'] ) ? sanitize_user( wp_unslash( $_GET['u'] ) ) : '';
			echo '<div class="notice notice-success"><p><strong>Account created</strong> for User ID <strong>' . esc_html( $uname ) . '</strong>. Share the User ID and password you just set with them directly - they can sign in from the site\'s "Admin Login" button.</p></div>';
		}
		if ( isset( $_GET['kap_error'] ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( sanitize_text_field( wp_unslash( $_GET['kap_error'] ) ) ) . '</p></div>';
		}

		self::render_form();
		echo '</div>';
	}

	private static function render_form() {
		?>
		<p>Creates a limited "College Staff Admin" account - access to Hero Slides, Notices, Recruitment,
		Tenders, Faculty, Gallery, Downloads, Links, Organisation Logos, Applications, Posts, Student
		Records and Media only. No Settings, Plugins, Themes, or Users access. Share the User ID and
		password you set below with the person directly - there is no separate verification step.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kap_create_admin' ); ?>
			<input type="hidden" name="action" value="kap_create_admin">
			<table class="form-table">
				<tr><th><label>Full Name</label></th><td><input type="text" name="kap_name" class="regular-text" required></td></tr>
				<tr><th><label>User ID (username)</label></th><td><input type="text" name="kap_username" class="regular-text" required pattern="[A-Za-z0-9_\.\-]+"></td></tr>
				<tr><th><label>Mobile Number</label></th><td><input type="text" name="kap_mobile" class="regular-text" required></td></tr>
				<tr><th><label>Email ID</label></th><td><input type="email" name="kap_email" class="regular-text" required></td></tr>
				<tr><th><label>Password</label></th><td><input type="password" name="kap_password" class="regular-text" required minlength="8"></td></tr>
			</table>
			<?php submit_button( 'Create Admin Account' ); ?>
		</form>
		<?php
	}

	public static function handle_create_admin() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Not allowed.' );
		check_admin_referer( 'kap_create_admin' );

		$name     = sanitize_text_field( wp_unslash( $_POST['kap_name'] ?? '' ) );
		$username = sanitize_user( wp_unslash( $_POST['kap_username'] ?? '' ) );
		$mobile   = sanitize_text_field( wp_unslash( $_POST['kap_mobile'] ?? '' ) );
		$email    = sanitize_email( wp_unslash( $_POST['kap_email'] ?? '' ) );
		$password = (string) ( $_POST['kap_password'] ?? '' );

		$error = '';
		if ( ! $name || ! $username || ! $mobile || ! $email || strlen( $password ) < 8 ) {
			$error = 'Please fill in every field (password at least 8 characters).';
		} elseif ( ! is_email( $email ) ) {
			$error = 'That email address does not look valid.';
		} elseif ( username_exists( $username ) ) {
			$error = 'That User ID is already taken.';
		} elseif ( email_exists( $email ) ) {
			$error = 'That email is already used by another account.';
		}

		if ( $error ) {
			wp_safe_redirect( admin_url( 'admin.php?page=kap-add-admin&kap_error=' . rawurlencode( $error ) ) );
			exit;
		}

		$user_id = wp_insert_user( array(
			'user_login'   => $username,
			'user_email'   => $email,
			'user_pass'    => $password,
			'display_name' => $name,
			'first_name'   => $name,
			'role'         => KAP_Role::ROLE,
		) );

		if ( is_wp_error( $user_id ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=kap-add-admin&kap_error=' . rawurlencode( $user_id->get_error_message() ) ) );
			exit;
		}

		update_user_meta( $user_id, 'kap_mobile', $mobile );

		// Best-effort welcome email - failing to send it never undoes the
		// account that was just created.
		$college = self::college_name();
		wp_mail(
			$email,
			'Welcome to ' . $college,
			"Welcome to $college!\n\nYou are now an admin of this college website.\n\nUser ID: $username\nLogin: " . wp_login_url() . "\n\nThank you."
		);

		wp_safe_redirect( admin_url( 'admin.php?page=kap-add-admin&kap_done=1&u=' . rawurlencode( $username ) ) );
		exit;
	}
}
