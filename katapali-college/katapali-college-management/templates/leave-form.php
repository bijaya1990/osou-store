<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var object $emp */
?>
<div class="kcms-box">
	<h3>Leave Application Form</h3>
	<div class="kcms-readonly-grid">
		<div><label>Employee Name</label><div class="kcms-ro"><?php echo esc_html( $emp->name ); ?></div></div>
		<div><label>Designation</label><div class="kcms-ro"><?php echo esc_html( $emp->designation ); ?></div></div>
		<div><label>Department</label><div class="kcms-ro"><?php echo esc_html( $emp->department ); ?></div></div>
		<div><label>Employee ID</label><div class="kcms-ro">EMP<?php echo esc_html( $emp->emp_id ); ?></div></div>
	</div>

	<form id="kcms-leave-form" enctype="multipart/form-data">
		<div class="kcms-form-grid">
			<p><label>Leave Type
				<select name="leave_type" required>
					<option value="">-- select --</option>
					<option value="CL">Casual Leave (CL)</option>
					<option value="EL">Earned Leave (EL)</option>
					<option value="ML">Medical Leave (ML)</option>
					<option value="DL">Duty Leave (DL)</option>
					<option value="Other">Other</option>
				</select>
			</label></p>
			<p><label>From Date<input type="date" name="from_date" required></label></p>
			<p><label>To Date<input type="date" name="to_date" required></label></p>
		</div>
		<p><label>Reason<textarea name="reason" rows="3" required placeholder="Reason for leave"></textarea></label></p>
		<p><label>Remarks (optional)<textarea name="remarks" rows="2"></textarea></label></p>
		<p><label>Upload Signature (image or PDF, optional)<input type="file" name="signature" accept=".jpg,.jpeg,.png,.pdf"></label></p>

		<div class="kcms-otp-row">
			<button type="button" id="kcms-leave-send-otp" class="kcms-btn kcms-btn-outline">Send OTP to Verify</button>
			<input type="text" name="otp" id="kcms-leave-otp" placeholder="Enter 6-digit OTP" maxlength="6" inputmode="numeric">
		</div>
		<p class="kcms-hint">An OTP will be sent to your registered mobile/email for verification before submission.</p>

		<div id="kcms-leave-msg" class="kcms-msg" hidden></div>
		<p><button type="submit" class="kcms-btn kcms-btn-primary">Submit Leave Application</button></p>
	</form>
	<div id="kcms-leave-success" class="kcms-success" hidden></div>
</div>
