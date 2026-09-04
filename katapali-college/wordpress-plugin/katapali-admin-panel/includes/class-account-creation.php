<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* "Add College Admin" screen - full site administrators only (manage_options,
   never the limited staff role) fill in Name/User ID/Mobile/Email/Password,
   an OTP is emailed to that address, and only once the correct OTP is
   entered is the actual WordPress account created (role: kc_staff_admin) -
   so a mistyped/unreachable email never leaves a half-created account
   behind. A final welcome email goes out once the account is live. */
class KAP_Account_Creation {

	const OTP_TTL = 10 * MINUTE_IN_SECONDS;

	public static function init() {
		// Priority 20 (after the theme's own admin_menu hook, which
		// registers the 'katapali-college' top-level page at the default
		// priority 10) - registering this submenu before that parent page
		// exists makes WordPress treat it as a phantom top-level page
		// internally (wrong hookname), which then 403s every real
		// administrator trying to open it.
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_post_kap_request_otp', array( __CLASS__, 'handle_request_otp' ) );
		add_action( 'admin_post_kap_verify_otp', array( __CLASS__, 'handle_verify_otp' ) );
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
			echo '<div class="notice notice-success"><p>Admin account created and the welcome email has been sent.</p></div>';
		}
		if ( isset( $_GET['kap_error'] ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( sanitize_text_field( wp_unslash( $_GET['kap_error'] ) ) ) . '</p></div>';
		}

		$token = isset( $_GET['token'] ) ? sanitize_key( wp_unslash( $_GET['token'] ) ) : '';
		$pending = $token ? get_transient( 'kap_pending_' . $token ) : false;

		if ( $token && $pending ) {
			self::render_otp_form( $token, $pending );
		} else {
			self::render_request_form();
		}

		echo '</div>';
	}

	private static function render_request_form() {
		?>
		<p>Creates a limited "College Staff Admin" account - access to Hero Slides, Notices, Recruitment,
		Tenders, Faculty, Gallery, Downloads, Links, Organisation Logos, Applications, Posts, Student
		Records and Media only. No Settings, Plugins, Themes, or Users access.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kap_request_otp' ); ?>
			<input type="hidden" name="action" value="kap_request_otp">
			<table class="form-table">
				<tr><th><label>Full Name</label></th><td><input type="text" name="kap_name" class="regular-text" required></td></tr>
				<tr><th><label>User ID (username)</label></th><td><input type="text" name="kap_username" class="regular-text" required pattern="[A-Za-z0-9_\.\-]+"></td></tr>
				<tr><th><label>Mobile Number</label></th><td><input type="text" name="kap_mobile" class="regular-text" required></td></tr>
				<tr><th><label>Email ID</label></th><td><input type="email" name="kap_email" class="regular-text" required></td></tr>
				<tr><th><label>Password</label></th><td><input type="password" name="kap_password" class="regular-text" required minlength="8"></td></tr>
			</table>
			<?php submit_button( 'Send OTP to this Email' ); ?>
		</form>
		<?php
	}

	private static function render_otp_form( $token, $pending ) {
		?>
		<p>An OTP has been sent to <strong><?php echo esc_html( $pending['email'] ); ?></strong>. Enter it below to finish creating the account for <strong><?php echo esc_html( $pending['name'] ); ?></strong>.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kap_verify_otp_' . $token ); ?>
			<input type="hidden" name="action" value="kap_verify_otp">
			<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">
			<table class="form-table">
				<tr><th><label>OTP</label></th><td><input type="text" name="kap_otp" class="regular-text" required autocomplete="one-time-code" maxlength="6"></td></tr>
			</table>
			<?php submit_button( 'Verify & Create Account' ); ?>
		</form>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=kap-add-admin' ) ); ?>">&larr; Start over</a></p>
		<?php
	}

	public static function handle_request_otp() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Not allowed.' );
		check_admin_referer( 'kap_request_otp' );

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

		$token = wp_generate_password( 20, false, false );
		$otp   = (string) wp_rand( 100000, 999999 );

		set_transient( 'kap_pending_' . $token, array(
			'name' => $name, 'username' => $username, 'mobile' => $mobile,
			'email' => $email, 'password' => $password, 'otp' => $otp,
		), self::OTP_TTL );

		$college = self::college_name();
		$mail_error = '';
		add_action( 'wp_mail_failed', function ( $wp_error ) use ( &$mail_error ) { $mail_error = $wp_error->get_error_message(); } );
		$sent = wp_mail(
			$email,
			'Your OTP for ' . $college . ' Admin Panel',
			"Hello $name,\n\nYour OTP to activate your College Staff Admin account for $college is:\n\n$otp\n\nThis code expires in 10 minutes. If you did not request this, you can ignore this email.\n\nThank you."
		);

		if ( ! $sent ) {
			delete_transient( 'kap_pending_' . $token );
			$msg = 'Could not send the OTP email' . ( $mail_error ? ' (' . $mail_error . ')' : '' ) . '. This is usually a hosting/mail setup issue - see the plugin README for fixing email delivery (an SMTP plugin is normally needed on shared hosting).';
			wp_safe_redirect( admin_url( 'admin.php?page=kap-add-admin&kap_error=' . rawurlencode( $msg ) ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=kap-add-admin&token=' . $token ) );
		exit;
	}

	public static function handle_verify_otp() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Not allowed.' );
		$token = isset( $_POST['token'] ) ? sanitize_key( wp_unslash( $_POST['token'] ) ) : '';
		check_admin_referer( 'kap_verify_otp_' . $token );

		$pending = $token ? get_transient( 'kap_pending_' . $token ) : false;
		if ( ! $pending ) {
			wp_safe_redirect( admin_url( 'admin.php?page=kap-add-admin&kap_error=' . rawurlencode( 'This OTP request has expired - please start again.' ) ) );
			exit;
		}

		$otp_entered = sanitize_text_field( wp_unslash( $_POST['kap_otp'] ?? '' ) );
		if ( ! hash_equals( $pending['otp'], $otp_entered ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=kap-add-admin&token=' . $token . '&kap_error=' . rawurlencode( 'Incorrect OTP, please try again.' ) ) );
			exit;
		}

		$user_id = wp_insert_user( array(
			'user_login' => $pending['username'],
			'user_email' => $pending['email'],
			'user_pass'  => $pending['password'],
			'display_name' => $pending['name'],
			'first_name' => $pending['name'],
			'role' => KAP_Role::ROLE,
		) );

		delete_transient( 'kap_pending_' . $token );

		if ( is_wp_error( $user_id ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=kap-add-admin&kap_error=' . rawurlencode( $user_id->get_error_message() ) ) );
			exit;
		}

		update_user_meta( $user_id, 'kap_mobile', $pending['mobile'] );

		$college = self::college_name();
		wp_mail(
			$pending['email'],
			'Welcome to ' . $college,
			"Welcome to $college!\n\nYou are now an admin of this college website.\n\nUser ID: {$pending['username']}\nLogin: " . wp_login_url() . "\n\nThank you."
		);

		wp_safe_redirect( admin_url( 'admin.php?page=kap-add-admin&kap_done=1' ) );
		exit;
	}
}
