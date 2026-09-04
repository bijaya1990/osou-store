<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var object $student */
$sessions = array();
for ( $y = 2010; $y <= 2050; $y++ ) { $sessions[] = $y . '-' . substr( $y + 1, 2 ); }
?>
<div class="kcms-box">
	<h3>Certificate / Marksheet Request Form</h3>
	<div class="kcms-readonly-grid">
		<div><label>Student Name</label><div class="kcms-ro"><?php echo esc_html( $student->name ); ?></div></div>
		<div><label>Father/Guardian</label><div class="kcms-ro"><?php echo esc_html( $student->father_name ); ?></div></div>
		<div><label>College Roll No.</label><div class="kcms-ro"><?php echo esc_html( $student->college_roll_no ); ?></div></div>
	</div>

	<form id="kcms-cert-form" enctype="multipart/form-data">
		<div class="kcms-form-grid">
			<p><label>Session
				<select name="session" required>
					<option value="">-- select --</option>
					<?php foreach ( $sessions as $s ) : ?><option value="<?php echo esc_attr( $s ); ?>"><?php echo esc_html( $s ); ?></option><?php endforeach; ?>
				</select>
			</label></p>
			<p><label>Result Status
				<select name="result_status">
					<option value="pass">Pass</option>
					<option value="fail">Fail</option>
					<option value="incomplete">Incomplete</option>
					<option value="pursuing">Pursuing</option>
				</select>
			</label></p>
		</div>

		<p><strong>Certificate Type(s) Requested</strong> (select all that apply)</p>
		<div class="kcms-checkbox-grid">
			<?php foreach ( array( 'Character Certificate (CC)', 'Transfer Certificate (TC)', 'Marksheet (Photocopy)', 'Conduct Certificate', 'Original Certificate', 'Provisional Certificate', 'Duplicate Certificate' ) as $t ) : ?>
			<label><input type="checkbox" name="certificate_type[]" value="<?php echo esc_attr( $t ); ?>"> <?php echo esc_html( $t ); ?></label>
			<?php endforeach; ?>
		</div>

		<div class="kcms-form-grid">
			<p><label>Reason
				<select name="reason">
					<option value="Higher Education">Higher Education</option>
					<option value="Employment">Employment</option>
					<option value="Scholarship">Scholarship</option>
					<option value="Verification Purpose">Verification Purpose</option>
					<option value="Migration">Migration</option>
					<option value="Other">Other</option>
				</select>
			</label></p>
			<p><label>Number of Copies<input type="number" name="num_copies" min="1" value="1" required></label></p>
			<p><label>Delivery Method
				<select name="delivery_method">
					<option value="Digital (Email)">Digital (Email)</option>
					<option value="Online Portal">Online Portal</option>
					<option value="Physical Delivery">Physical Delivery</option>
					<option value="Courier (Paid)">Courier (Paid option)</option>
				</select>
			</label></p>
		</div>
		<p><label>Additional Remarks (optional)<textarea name="remarks" rows="2"></textarea></label></p>
		<p><label>Upload Signature (image or PDF, optional)<input type="file" name="signature" accept=".jpg,.jpeg,.png,.pdf"></label></p>

		<div class="kcms-otp-row">
			<button type="button" id="kcms-cert-send-otp" class="kcms-btn kcms-btn-outline">Send OTP to Verify</button>
			<input type="text" name="otp" id="kcms-cert-otp" placeholder="Enter 6-digit OTP" maxlength="6" inputmode="numeric">
		</div>
		<p class="kcms-hint">An OTP will be sent to your registered mobile/email for verification before submission.</p>

		<div id="kcms-cert-msg" class="kcms-msg" hidden></div>
		<p><button type="submit" class="kcms-btn kcms-btn-primary">Submit Request</button></p>
	</form>
	<div id="kcms-cert-success" class="kcms-success" hidden></div>
</div>
