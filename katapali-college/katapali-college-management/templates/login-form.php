<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var string $error
 *  @var bool $show_tabs
 *  @var string $locked_type ('teacher'|'student') - used when $show_tabs is false
 *  @var string $heading - custom heading shown under the logo when embedded inline
 *  @var string $after_login ('portal'|'return') */
$college = get_theme_mod( 'kc_college_name', get_bloginfo( 'name' ) );
$show_tabs = $show_tabs ?? true;
$locked_type = $locked_type ?? 'teacher';
$heading = $heading ?? '';
$after_login = $after_login ?? 'portal';
$requested_type = $show_tabs ? ( isset( $_GET['type'] ) && 'student' === $_GET['type'] ? 'student' : 'teacher' ) : $locked_type;
$logo_id = get_theme_mod( 'custom_logo' );
$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
$primary = get_theme_mod( 'kc_color_primary' ) ?: '#012D58';
$dark    = get_theme_mod( 'kc_color_dark' ) ?: '#001A33';
$accent  = get_theme_mod( 'kc_color_accent' ) ?: '#DB3918';
$gold    = get_theme_mod( 'kc_color_gold' ) ?: '#EBC30F';
$sub_default = 'Enter the Mobile Number and Date of Birth on your ' . ( 'teacher' === $requested_type ? 'Teacher/Staff' : 'Student' ) . ' record.';
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&family=Inter:wght@400;500;600;700&display=swap">
<style>
	/* Scoped, self-contained styles for the login page - deliberately not
	   relying on the theme stylesheet cascade, so this always renders
	   correctly regardless of theme CSS or caching. */
	.kcms-login-page{--kp:<?php echo esc_html( $primary ); ?>;--kd:<?php echo esc_html( $dark ); ?>;--ka:<?php echo esc_html( $accent ); ?>;--kg:<?php echo esc_html( $gold ); ?>;
		background:linear-gradient(160deg,var(--kd) 0%,var(--kp) 55%,#0f4a7a 100%);
		margin:16px 0;padding:56px 16px;display:flex;justify-content:center;
		font-family:'Inter',system-ui,-apple-system,sans-serif;position:relative;overflow:hidden;border-radius:14px;
	}
	.kcms-login-page::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle at 15% 20%,rgba(235,195,15,.10),transparent 40%),radial-gradient(circle at 85% 85%,rgba(219,57,24,.14),transparent 45%);pointer-events:none;}
	.kcms-login-page *{box-sizing:border-box;}
	.kcms-lp-card{position:relative;z-index:1;width:100%;max-width:408px;background:#fff;border-radius:16px;box-shadow:0 24px 60px rgba(0,10,25,.35);overflow:hidden;}
	.kcms-lp-head{background:linear-gradient(135deg,var(--kp),var(--kd));color:#fff;text-align:center;padding:34px 26px 26px;}
	.kcms-lp-logo{width:60px;height:60px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;overflow:hidden;box-shadow:0 4px 14px rgba(0,0,0,.25);}
	.kcms-lp-logo img{width:100%;height:100%;object-fit:cover;}
	.kcms-lp-logo span{color:var(--kp);font-family:'Poppins',sans-serif;font-weight:800;font-size:1.4rem;}
	.kcms-lp-head h1{margin:0 0 4px;font-family:'Poppins',sans-serif;font-size:1.08rem;line-height:1.35;font-weight:700;}
	.kcms-lp-head p{margin:0;font-size:.78rem;letter-spacing:.03em;text-transform:uppercase;opacity:.75;font-weight:600;}
	.kcms-lp-tabs{display:flex;background:#eef1f5;}
	.kcms-lp-tab{flex:1;border:none;background:transparent;color:#5b6b7d;font-weight:700;font-size:.82rem;padding:15px 8px;cursor:pointer;border-bottom:3px solid transparent;transition:.2s;font-family:inherit;}
	.kcms-lp-tab:hover{color:var(--kp);}
	.kcms-lp-tab.active{background:#fff;color:var(--kp);border-bottom-color:var(--ka);}
	.kcms-lp-pill{text-align:center;padding:12px 20px;background:#eef1f5;font-size:.78rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--kp);}
	.kcms-lp-error{margin:18px 26px 0;padding:11px 14px;background:#fdecea;color:#a52a1f;border:1px solid #f5c2bd;border-radius:8px;font-size:.84rem;line-height:1.4;}
	.kcms-lp-form{padding:26px 26px 8px;}
	.kcms-lp-sub{font-size:.82rem;color:#6b7684;margin:0 0 20px;line-height:1.4;}
	.kcms-lp-field{margin-bottom:16px;}
	.kcms-lp-field label{display:flex;align-items:center;gap:6px;font-weight:700;font-size:.8rem;color:var(--kp);margin-bottom:7px;letter-spacing:.02em;}
	.kcms-lp-field label svg{flex-shrink:0;opacity:.8;}
	.kcms-lp-field input{width:100%;padding:12px 13px;border:1.5px solid #dbe1e8;border-radius:9px;font-size:1rem;font-family:inherit;color:#1a2430;background:#fbfcfd;transition:.15s;}
	.kcms-lp-field input:focus{outline:none;border-color:var(--kp);background:#fff;box-shadow:0 0 0 3px rgba(1,45,88,.1);}
	.kcms-lp-submit{width:100%;padding:14px;background:var(--ka);color:#fff;border:none;border-radius:9px;font-weight:700;font-size:.98rem;cursor:pointer;margin:6px 0 4px;font-family:inherit;letter-spacing:.02em;transition:.15s;}
	.kcms-lp-submit:hover{background:#b52c10;transform:translateY(-1px);}
	.kcms-lp-help{text-align:center;font-size:.75rem;color:#8b96a3;padding:16px 26px 24px;margin:0;line-height:1.5;border-top:1px solid #eef1f5;margin-top:14px;padding-top:16px;}
	@media(max-width:480px){.kcms-lp-head{padding:28px 20px 22px;}.kcms-lp-form{padding:22px 20px 6px;}}
</style>

<div class="kcms-login-page">
	<div class="kcms-lp-card">
		<div class="kcms-lp-head">
			<div class="kcms-lp-logo">
				<?php if ( $logo_url ) : ?>
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $college ); ?>">
				<?php else : ?>
					<span><?php echo esc_html( mb_substr( $college, 0, 1 ) ); ?></span>
				<?php endif; ?>
			</div>
			<h1><?php echo esc_html( $heading ?: $college ); ?></h1>
			<p><?php echo $heading ? esc_html( $college ) : 'Staff &amp; Student Portal'; ?></p>
		</div>

		<?php if ( $show_tabs ) : ?>
			<div class="kcms-lp-tabs" id="kcms-login-tabs">
				<button type="button" class="kcms-lp-tab<?php echo 'teacher' === $requested_type ? ' active' : ''; ?>" data-type="teacher">Teacher / Staff</button>
				<button type="button" class="kcms-lp-tab<?php echo 'student' === $requested_type ? ' active' : ''; ?>" data-type="student">Student</button>
			</div>
		<?php else : ?>
			<div class="kcms-lp-pill"><?php echo 'teacher' === $requested_type ? 'Teacher / Staff Login' : 'Student Login'; ?></div>
		<?php endif; ?>

		<?php if ( $error ) : ?>
			<div class="kcms-lp-error"><?php echo esc_html( $error ); ?></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="kcms-lp-form">
			<?php wp_nonce_field( 'kcms_login' ); ?>
			<input type="hidden" name="action" value="kcms_login">
			<input type="hidden" name="login_type" id="kcms-login-type" value="<?php echo esc_attr( $requested_type ); ?>">
			<input type="hidden" name="redirect_page" value="<?php echo esc_url( get_permalink() ); ?>">
			<input type="hidden" name="after_login" value="<?php echo esc_attr( $after_login ); ?>">

			<p class="kcms-lp-sub" id="kcms-login-sub"><?php echo esc_html( $sub_default ); ?></p>

			<div class="kcms-lp-field">
				<label for="kcms-lp-mobile-<?php echo esc_attr( $requested_type ); ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="7" y="2" width="10" height="20" rx="2"/><line x1="11" y1="18" x2="13" y2="18"/></svg> Mobile Number</label>
				<input id="kcms-lp-mobile-<?php echo esc_attr( $requested_type ); ?>" type="tel" name="mobile" inputmode="numeric" pattern="[0-9+ ]{8,15}" placeholder="98765 43210" required autofocus>
			</div>
			<div class="kcms-lp-field">
				<label for="kcms-lp-dob-<?php echo esc_attr( $requested_type ); ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Date of Birth</label>
				<input id="kcms-lp-dob-<?php echo esc_attr( $requested_type ); ?>" type="date" name="dob" required>
			</div>

			<button type="submit" class="kcms-lp-submit">Log In</button>
		</form>

		<p class="kcms-lp-help">No OTP, no password to remember &mdash; just the Mobile Number and Date of Birth already on file with the college office. Trouble logging in? Contact the office to check your record.</p>
	</div>
</div>

<?php if ( $show_tabs ) : ?>
<script>
(function(){
	var tabs = document.getElementById('kcms-login-tabs');
	if (!tabs) return;
	var typeInput = document.getElementById('kcms-login-type');
	var sub = document.getElementById('kcms-login-sub');
	tabs.addEventListener('click', function(e){
		var btn = e.target.closest('.kcms-lp-tab');
		if (!btn) return;
		tabs.querySelectorAll('.kcms-lp-tab').forEach(function(t){ t.classList.toggle('active', t===btn); });
		var type = btn.dataset.type;
		typeInput.value = type;
		sub.textContent = 'Enter the Mobile Number and Date of Birth on your ' + (type === 'teacher' ? 'Teacher/Staff' : 'Student') + ' record.';
	});
})();
</script>
<?php endif; ?>
