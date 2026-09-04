<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Reskins wp-login.php with the college's own branding (logo, name,
   address, "Admin Panel" label, theme colours) instead of the default
   WordPress logo/blue scheme, and shows a one-time "Login Successful"
   welcome popup right after signing in - all on top of the same,
   already-battle-tested wp-login.php flow (nonces, lockouts, password
   resets etc. keep working exactly as before). */
class KAP_Login_Branding {

	public static function init() {
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'login_styles' ) );
		add_filter( 'login_headerurl', array( __CLASS__, 'header_url' ) );
		add_filter( 'login_headertext', array( __CLASS__, 'header_text' ) );
		add_action( 'login_header', array( __CLASS__, 'header_banner' ) );
		add_action( 'login_footer', array( __CLASS__, 'footer_note' ) );

		add_action( 'wp_login', array( __CLASS__, 'flag_welcome_popup' ), 10, 2 );
		add_action( 'admin_footer', array( __CLASS__, 'maybe_show_welcome_popup' ) );
		add_filter( 'login_redirect', array( __CLASS__, 'login_redirect' ), 10, 3 );
	}

	private static function college_name() {
		return get_theme_mod( 'kc_college_name', 'Katapali +3 College, Katapali' );
	}
	private static function address_line() {
		$addr = get_theme_mod( 'kc_address', 'AT/PO - Katapali, Via - Bijepur, District - Bargarh, Odisha' );
		$pin  = get_theme_mod( 'kc_pin', '768032' );
		return $addr . ' - ' . $pin;
	}
	private static function logo_url() {
		$logo_id = get_theme_mod( 'custom_logo' );
		return $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : KAP_URI . '/assets/logo-placeholder.svg';
	}

	public static function login_styles() {
		$primary   = get_theme_mod( 'kc_color_primary', '#012D58' );
		$primary_d = get_theme_mod( 'kc_color_dark', '#001A33' );
		$gold      = get_theme_mod( 'kc_color_gold', '#EBC30F' );
		?>
		<style>
			body.login { background: linear-gradient(135deg, <?php echo esc_attr( $primary ); ?>, <?php echo esc_attr( $primary_d ); ?>); }
			body.login #login { padding-top: 0; width: 380px; }
			.kap-login-banner { text-align: center; margin-bottom: 22px; }
			.kap-login-banner img { width: 78px; height: 78px; object-fit: contain; border-radius: 50%; background: #fff; padding: 6px; box-shadow: 0 4px 14px rgba(0,0,0,.25); }
			.kap-login-banner h1 { color: #fff; font-size: 1.15rem; font-weight: 800; margin: 12px 0 2px; line-height: 1.3; }
			.kap-login-banner .kap-addr { color: rgba(255,255,255,.85); font-size: .72rem; margin: 0 0 8px; }
			.kap-login-banner .kap-tag { display: inline-block; background: <?php echo esc_attr( $gold ); ?>; color: <?php echo esc_attr( $primary_d ); ?>; font-weight: 800; font-size: .72rem; letter-spacing: .04em; text-transform: uppercase; padding: 4px 14px; border-radius: 20px; }
			body.login #login h1 a { display: none; }
			body.login form { border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,.25); border-top: 4px solid <?php echo esc_attr( $gold ); ?>; }
			body.login .button-primary { background: <?php echo esc_attr( $primary ); ?>; border-color: <?php echo esc_attr( $primary_d ); ?>; box-shadow: none; text-shadow: none; }
			body.login .button-primary:hover, body.login .button-primary:focus { background: <?php echo esc_attr( $primary_d ); ?>; }
			body.login #nav, body.login #backtoblog { text-align: center; }
			body.login #nav a, body.login #backtoblog a { color: rgba(255,255,255,.85) !important; }
			.kap-login-footer { text-align: center; color: rgba(255,255,255,.6); font-size: .72rem; margin-top: 18px; }
		</style>
		<?php
	}

	public static function header_url() {
		return home_url( '/' );
	}

	public static function header_text() {
		return self::college_name();
	}

	public static function header_banner() {
		?>
		<div class="kap-login-banner">
			<img src="<?php echo esc_url( self::logo_url() ); ?>" alt="<?php echo esc_attr( self::college_name() ); ?>">
			<h1><?php echo esc_html( self::college_name() ); ?></h1>
			<p class="kap-addr"><?php echo esc_html( self::address_line() ); ?></p>
			<div><span class="kap-tag">Admin Panel</span></div>
		</div>
		<?php
	}

	public static function footer_note() {
		echo '<p class="kap-login-footer">&copy; ' . esc_html( gmdate( 'Y' ) ) . ' ' . esc_html( self::college_name() ) . '</p>';
	}

	public static function flag_welcome_popup( $user_login, $user ) {
		set_transient( 'kap_welcome_' . $user->ID, 1, MINUTE_IN_SECONDS * 2 );
	}

	public static function login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		if ( $user instanceof WP_User && KAP_Role::is_staff_admin( $user ) ) {
			return admin_url( 'index.php' );
		}
		return $redirect_to;
	}

	public static function maybe_show_welcome_popup() {
		$uid = get_current_user_id();
		if ( ! $uid || ! get_transient( 'kap_welcome_' . $uid ) ) return;
		delete_transient( 'kap_welcome_' . $uid );
		$user = wp_get_current_user();
		$msg  = 'Welcome to ' . self::college_name() . '!';
		?>
		<div id="kap-welcome-overlay" style="position:fixed;inset:0;background:rgba(1,45,88,.55);z-index:999999;display:flex;align-items:center;justify-content:center;">
			<div style="background:#fff;border-radius:12px;max-width:360px;width:90%;padding:30px 26px;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,.3);">
				<div style="width:56px;height:56px;border-radius:50%;background:#e9f7ef;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:28px;color:#16a34a;">&#10003;</div>
				<h2 style="margin:0 0 6px;font-size:1.15rem;">Login Successful</h2>
				<p style="margin:0 0 18px;color:#555;font-size:.92rem;"><?php echo esc_html( $msg ); ?></p>
				<button onclick="document.getElementById('kap-welcome-overlay').remove();" style="background:#012D58;color:#fff;border:none;padding:10px 26px;border-radius:6px;font-weight:700;cursor:pointer;">OK</button>
			</div>
		</div>
		<?php
	}
}
