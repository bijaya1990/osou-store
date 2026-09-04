<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var object $req @var object $student @var object|null $academic @var array $types */
$college = get_theme_mod( 'kc_college_name', get_bloginfo( 'name' ) );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<title>Certificate Request - <?php echo esc_html( $req->request_number ); ?></title>
<style>
  body{font-family:Georgia,'Times New Roman',serif;color:#111;max-width:800px;margin:0 auto;padding:30px;}
  .kcms-print-header{text-align:center;border-bottom:3px double #012D58;padding-bottom:14px;margin-bottom:18px;}
  .kcms-print-header h1{margin:6px 0 2px;font-size:1.4rem;color:#012D58;}
  .kcms-print-header p{margin:2px 0;font-size:.85rem;}
  h2.kcms-title{text-align:center;text-decoration:underline;font-size:1.1rem;margin:18px 0;}
  .kcms-ref{text-align:right;font-size:.85rem;margin-bottom:10px;}
  table.kcms-fields{width:100%;border-collapse:collapse;margin-bottom:16px;}
  table.kcms-fields td{border:1px solid #999;padding:7px 10px;font-size:.92rem;}
  table.kcms-fields td.label{background:#f3f3f3;font-weight:bold;width:38%;}
  .kcms-declaration{font-size:.85rem;margin:20px 0;line-height:1.6;}
  .kcms-sign-row{display:flex;justify-content:space-between;margin-top:40px;}
  .kcms-sign-box{width:45%;text-align:center;border-top:1px solid #333;padding-top:6px;font-size:.85rem;}
  .kcms-official{margin-top:34px;border:2px solid #012D58;padding:14px;}
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
	<p><?php echo esc_html( get_theme_mod( 'kc_affiliation', '' ) ); ?></p>
	<p><?php echo esc_html( get_theme_mod( 'kc_address', '' ) ); ?> <?php echo esc_html( get_theme_mod( 'kc_pin', '' ) ); ?></p>
	<p><?php echo esc_html( get_theme_mod( 'kc_phone', '' ) ); ?> | <?php echo esc_html( get_theme_mod( 'kc_email', '' ) ); ?></p>
</div>

<h2 class="kcms-title">REQUEST FOR CERTIFICATE AND ACADEMIC DOCUMENTS</h2>
<div class="kcms-ref">Reference No: <strong><?php echo esc_html( $req->request_number ); ?></strong> | Date: <?php echo esc_html( gmdate( 'd-m-Y', strtotime( $req->date_requested ) ) ); ?></div>

<table class="kcms-fields">
	<tr><td class="label">Applicant Name</td><td><?php echo esc_html( $student->name ); ?></td></tr>
	<tr><td class="label">Father/Guardian Name</td><td><?php echo esc_html( $student->father_name ); ?></td></tr>
	<tr><td class="label">College Roll No.</td><td><?php echo esc_html( $student->college_roll_no ); ?></td></tr>
	<tr><td class="label">University Roll No.</td><td><?php echo esc_html( $student->university_roll_no ); ?></td></tr>
	<tr><td class="label">Session</td><td><?php echo esc_html( $req->session ); ?></td></tr>
	<?php if ( $academic ) : ?>
	<tr><td class="label">Class / Branch</td><td><?php echo esc_html( $academic->class ); ?> <?php echo $academic->branch ? '/ ' . esc_html( $academic->branch ) : ''; ?></td></tr>
	<tr><td class="label">Result</td><td><?php echo esc_html( ucfirst( $academic->result_status ) ); ?><?php echo $academic->marks_obtained ? ' - Marks: ' . esc_html( $academic->marks_obtained ) : ''; ?><?php echo $academic->percentage_cgpa ? ' (' . esc_html( $academic->percentage_cgpa ) . ')' : ''; ?></td></tr>
	<?php endif; ?>
	<tr><td class="label">Document(s) Requested</td><td><?php echo esc_html( implode( ', ', $types ) ); ?></td></tr>
	<tr><td class="label">Purpose</td><td><?php echo esc_html( $req->reason ); ?></td></tr>
	<tr><td class="label">Number of Copies</td><td><?php echo esc_html( $req->num_copies ); ?></td></tr>
	<tr><td class="label">Delivery Method</td><td><?php echo esc_html( $req->delivery_method ); ?></td></tr>
	<?php if ( $req->remarks ) : ?><tr><td class="label">Remarks</td><td><?php echo nl2br( esc_html( $req->remarks ) ); ?></td></tr><?php endif; ?>
</table>

<div class="kcms-declaration">I hereby declare that the information provided above is true to the best of my knowledge, and I request the college to issue the document(s) checked above.</div>

<div class="kcms-sign-row">
	<div class="kcms-sign-box">
		<?php if ( $req->signature_file ) : ?><img src="<?php echo esc_url( $req->signature_file ); ?>" style="max-height:50px;"><br><?php endif; ?>
		Applicant Signature<br>Date: <?php echo esc_html( gmdate( 'd-m-Y', strtotime( $req->date_requested ) ) ); ?>
	</div>
	<div class="kcms-sign-box">&nbsp;<br>Mobile Verified (OTP)</div>
</div>

<div class="kcms-official">
	<h3>OFFICIAL VERIFICATION</h3>
	<p><strong>Status:</strong> <?php echo esc_html( ucfirst( $req->status ) ); ?></p>
	<?php if ( $req->principal_date ) : ?><p><strong>Approval Date:</strong> <?php echo esc_html( gmdate( 'd-m-Y', strtotime( $req->principal_date ) ) ); ?></p><?php endif; ?>
	<div class="kcms-sign-row">
		<div></div>
		<div class="kcms-sign-box">
			<?php if ( $req->principal_signature ) : ?><img src="<?php echo esc_url( $req->principal_signature ); ?>" style="max-height:50px;"><br><?php endif; ?>
			Principal / Authorised Officer
		</div>
	</div>
</div>

<div class="kcms-footer-note">NOT FOR DIRECT CIRCULATION - This document was generated electronically by the <?php echo esc_html( $college ); ?> Certificate Request System.</div>
</body>
</html>
