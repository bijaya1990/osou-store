<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var object $app @var object $emp */
$college = get_theme_mod( 'kc_college_name', get_bloginfo( 'name' ) );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<title>Leave Application - <?php echo esc_html( $app->application_number ); ?></title>
<style>
  body{font-family:Georgia,'Times New Roman',serif;color:#111;max-width:800px;margin:0 auto;padding:30px;}
  .kcms-print-header{text-align:center;border-bottom:3px double #012D58;padding-bottom:14px;margin-bottom:18px;}
  .kcms-print-header h1{margin:6px 0 2px;font-size:1.4rem;color:#012D58;}
  .kcms-print-header p{margin:2px 0;font-size:.85rem;}
  h2.kcms-title{text-align:center;text-decoration:underline;font-size:1.15rem;margin:18px 0;}
  .kcms-ref{text-align:right;font-size:.85rem;margin-bottom:10px;}
  table.kcms-fields{width:100%;border-collapse:collapse;margin-bottom:16px;}
  table.kcms-fields td{border:1px solid #999;padding:7px 10px;font-size:.92rem;}
  table.kcms-fields td.label{background:#f3f3f3;font-weight:bold;width:38%;}
  .kcms-sign-row{display:flex;justify-content:space-between;margin-top:50px;}
  .kcms-sign-box{width:45%;text-align:center;border-top:1px solid #333;padding-top:6px;font-size:.85rem;}
  .kcms-official{margin-top:40px;border:2px solid #012D58;padding:14px;}
  .kcms-official h3{margin-top:0;color:#012D58;font-size:1rem;}
  .kcms-footer-note{text-align:center;font-size:.75rem;color:#666;margin-top:30px;border-top:1px solid #ccc;padding-top:8px;}
  .kcms-print-btn{text-align:center;margin-bottom:20px;}
  .kcms-print-btn button{padding:8px 22px;font-size:1rem;cursor:pointer;}
  @media print{ .kcms-print-btn{display:none;} body{padding:0;} }
</style>
</head>
<body>
<div class="kcms-print-btn"><button onclick="window.print()">Print / Save as PDF</button></div>

<div class="kcms-print-header">
	<h1><?php echo esc_html( $college ); ?></h1>
	<p><?php echo esc_html( get_theme_mod( 'kc_address', '' ) ); ?> <?php echo esc_html( get_theme_mod( 'kc_pin', '' ) ); ?></p>
	<p><?php echo esc_html( get_theme_mod( 'kc_phone', '' ) ); ?> | <?php echo esc_html( get_theme_mod( 'kc_email', '' ) ); ?></p>
</div>

<h2 class="kcms-title">LEAVE APPLICATION FORM</h2>
<div class="kcms-ref">Application No: <strong><?php echo esc_html( $app->application_number ); ?></strong></div>

<table class="kcms-fields">
	<tr><td class="label">Employee Name</td><td><?php echo esc_html( $emp->name ); ?></td></tr>
	<tr><td class="label">Designation</td><td><?php echo esc_html( $emp->designation ); ?></td></tr>
	<tr><td class="label">Department</td><td><?php echo esc_html( $emp->department ); ?></td></tr>
	<tr><td class="label">Employee ID</td><td>EMP<?php echo esc_html( $emp->emp_id ); ?></td></tr>
	<tr><td class="label">Leave Type</td><td><?php echo esc_html( $app->leave_type ); ?></td></tr>
	<tr><td class="label">Leave Year</td><td><?php echo esc_html( $app->leave_year ); ?></td></tr>
	<tr><td class="label">From Date</td><td><?php echo esc_html( $app->from_date ); ?></td></tr>
	<tr><td class="label">To Date</td><td><?php echo esc_html( $app->to_date ); ?></td></tr>
	<tr><td class="label">Number of Days</td><td><?php echo esc_html( $app->number_of_days ); ?></td></tr>
	<tr><td class="label">Reason</td><td><?php echo nl2br( esc_html( $app->reason ) ); ?></td></tr>
	<?php if ( $app->remarks ) : ?><tr><td class="label">Remarks</td><td><?php echo nl2br( esc_html( $app->remarks ) ); ?></td></tr><?php endif; ?>
</table>

<div class="kcms-sign-row">
	<div class="kcms-sign-box">
		<?php if ( $app->signature_file ) : ?><img src="<?php echo esc_url( $app->signature_file ); ?>" style="max-height:50px;"><br><?php endif; ?>
		Employee Signature<br>Date: <?php echo esc_html( gmdate( 'd-m-Y', strtotime( $app->submitted_date ) ) ); ?>
	</div>
	<div class="kcms-sign-box">&nbsp;<br>Office Use</div>
</div>

<div class="kcms-official">
	<h3>FOR OFFICIAL USE ONLY</h3>
	<p><strong>Status:</strong> <?php echo esc_html( ucfirst( $app->status ) ); ?></p>
	<?php if ( $app->principal_remarks ) : ?><p><strong>Remarks:</strong> <?php echo esc_html( $app->principal_remarks ); ?></p><?php endif; ?>
	<?php if ( $app->approval_date ) : ?><p><strong>Decision Date:</strong> <?php echo esc_html( gmdate( 'd-m-Y', strtotime( $app->approval_date ) ) ); ?></p><?php endif; ?>
	<div class="kcms-sign-row">
		<div></div>
		<div class="kcms-sign-box">
			<?php if ( $app->principal_signature ) : ?><img src="<?php echo esc_url( $app->principal_signature ); ?>" style="max-height:50px;"><br><?php endif; ?>
			Principal Signature
		</div>
	</div>
</div>

<div class="kcms-footer-note">This document was generated electronically by the <?php echo esc_html( $college ); ?> Leave Application System.</div>
</body>
</html>
