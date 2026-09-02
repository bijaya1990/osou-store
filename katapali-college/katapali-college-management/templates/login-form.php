<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var string $error */
$college = get_theme_mod( 'kc_college_name', get_bloginfo( 'name' ) );
$requested_type = isset( $_GET['type'] ) && 'student' === $_GET['type'] ? 'student' : 'teacher';
?>
<div class="kcms-login-wrap">
	<div class="kcms-login-card">
		<div class="kcms-login-head">
			<div class="kcms-login-badge"><?php echo esc_html( mb_substr( $college, 0, 1 ) ); ?></div>
			<h2><?php echo esc_html( $college ); ?></h2>
			<p>Staff &amp; Student Portal Login</p>
		</div>

		<div class="kcms-login-tabs" id="kcms-login-tabs">
			<button type="button" class="kcms-login-tab<?php echo 'teacher' === $requested_type ? ' active' : ''; ?>" data-type="teacher">Teacher / Staff Login</button>
			<button type="button" class="kcms-login-tab<?php echo 'student' === $requested_type ? ' active' : ''; ?>" data-type="student">Student Login</button>
		</div>

		<?php if ( $error ) : ?>
			<div class="kcms-login-error"><?php echo esc_html( $error ); ?></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="kcms-login-form">
			<?php wp_nonce_field( 'kcms_login' ); ?>
			<input type="hidden" name="action" value="kcms_login">
			<input type="hidden" name="login_type" id="kcms-login-type" value="<?php echo esc_attr( $requested_type ); ?>">
			<input type="hidden" name="redirect_page" value="<?php echo esc_url( get_permalink() ); ?>">

			<p class="kcms-login-sub" id="kcms-login-sub">Enter the Mobile Number and Date of Birth on your <?php echo 'teacher' === $requested_type ? 'Teacher/Staff' : 'Student'; ?> record</p>

			<label>Mobile Number
				<input type="tel" name="mobile" inputmode="numeric" pattern="[0-9+ ]{8,15}" placeholder="98765 43210" required autofocus>
			</label>
			<label>Date of Birth
				<input type="date" name="dob" required>
			</label>

			<button type="submit" class="kcms-login-submit">Log In</button>
		</form>

		<p class="kcms-login-help">No OTP, no password to remember - just the Mobile Number and Date of Birth already on file with the college office. If it doesn't work, contact the office to check your record.</p>
	</div>
</div>

<script>
(function(){
	var tabs = document.getElementById('kcms-login-tabs');
	if (!tabs) return;
	var typeInput = document.getElementById('kcms-login-type');
	var sub = document.getElementById('kcms-login-sub');
	tabs.addEventListener('click', function(e){
		var btn = e.target.closest('.kcms-login-tab');
		if (!btn) return;
		tabs.querySelectorAll('.kcms-login-tab').forEach(function(t){ t.classList.toggle('active', t===btn); });
		var type = btn.dataset.type;
		typeInput.value = type;
		sub.textContent = 'Enter the Mobile Number and Date of Birth on your ' + (type === 'teacher' ? 'Teacher/Staff' : 'Student') + ' record';
	});
})();
</script>
