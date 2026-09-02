<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var object $card */
$college = get_theme_mod( 'kc_college_name', get_bloginfo( 'name' ) );
$member_id = $card->member_id ?: KCMS_Numbering::member_id( $card->id_card_id );
$issue = $card->issue_date ? strtotime( $card->issue_date ) : time();
$expiry = strtotime( '+1 year', $issue );
$barcode_url = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . rawurlencode( $member_id );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<title>Library Card - <?php echo esc_html( $card->roll_number ); ?></title>
<style>
  body{font-family:Arial,Helvetica,sans-serif;margin:0;padding:20px;background:#eee;}
  .kcms-print-btn{text-align:center;margin-bottom:16px;}
  .kcms-print-btn button{padding:8px 22px;font-size:1rem;cursor:pointer;}
  .libcard{width:105mm;min-height:148mm;margin:0 auto;background:linear-gradient(160deg,#012D58,#0f766e);color:#fff;border-radius:10px;box-sizing:border-box;padding:18px;position:relative;overflow:hidden;}
  .libcard::after{content:'';position:absolute;right:-40px;top:-40px;width:140px;height:140px;background:rgba(235,195,15,.25);border-radius:50%;}
  .libcard h2{margin:0 0 2px;font-size:1.05rem;}
  .libcard .sub{font-size:.68rem;opacity:.85;margin-bottom:14px;}
  .libcard .member-id{background:#EBC30F;color:#402a00;font-weight:bold;padding:4px 10px;border-radius:6px;display:inline-block;font-size:.85rem;margin-bottom:14px;}
  .libcard-fields{font-size:.82rem;line-height:1.8;position:relative;z-index:1;}
  .libcard-fields b{display:inline-block;width:90px;opacity:.85;}
  .libcard-barcode{background:#fff;padding:8px;border-radius:6px;text-align:center;margin-top:16px;width:100px;}
  .libcard-footer{margin-top:18px;font-size:.7rem;opacity:.85;border-top:1px solid rgba(255,255,255,.3);padding-top:8px;position:relative;z-index:1;}
  @media print{ .kcms-print-btn{display:none;} body{background:#fff;padding:0;} }
</style>
</head>
<body>
<div class="kcms-print-btn"><button onclick="window.print()">Print / Save as PDF</button></div>
<div class="libcard">
	<h2><?php echo esc_html( $college ); ?></h2>
	<div class="sub">LIBRARY MEMBERSHIP CARD</div>
	<div class="member-id">ID: <?php echo esc_html( $member_id ); ?></div>
	<div class="libcard-fields">
		<div><b>Name:</b> <?php echo esc_html( $card->name ); ?></div>
		<div><b>Roll No:</b> <?php echo esc_html( $card->roll_number ); ?></div>
		<div><b>Class:</b> <?php echo esc_html( $card->class ); ?></div>
		<div><b>Session:</b> <?php echo esc_html( $card->session ); ?></div>
		<div><b>Issued:</b> <?php echo esc_html( gmdate( 'd-m-Y', $issue ) ); ?></div>
		<div><b>Valid Till:</b> <?php echo esc_html( gmdate( 'd-m-Y', $expiry ) ); ?></div>
	</div>
	<div class="libcard-barcode"><img src="<?php echo esc_url( $barcode_url ); ?>" width="84" height="84" alt="barcode"></div>
	<div class="libcard-footer">Librarian Signature: ______________<br>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $college ); ?></div>
</div>
</body>
</html>
