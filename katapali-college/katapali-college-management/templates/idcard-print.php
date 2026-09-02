<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var object $card */
$college = get_theme_mod( 'kc_college_name', get_bloginfo( 'name' ) );
$logo_id = get_theme_mod( 'custom_logo' );
$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
$portal_link = home_url( '/' );
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . rawurlencode( $portal_link . '?roll=' . $card->roll_number );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<title>ID Card - <?php echo esc_html( $card->roll_number ); ?></title>
<style>
  body{font-family:Arial,Helvetica,sans-serif;margin:0;padding:20px;background:#eee;}
  .kcms-print-btn{text-align:center;margin-bottom:16px;}
  .kcms-print-btn button{padding:8px 22px;font-size:1rem;cursor:pointer;}
  .idcard{width:148mm;min-height:210mm;margin:0 auto;background:#fff;border:2px solid #012D58;box-sizing:border-box;padding:16px 20px;}
  .idcard-header{text-align:center;border-bottom:2px solid #012D58;padding-bottom:10px;margin-bottom:14px;}
  .idcard-header img.logo{height:52px;}
  .idcard-header h1{font-size:1.1rem;color:#012D58;margin:6px 0 2px;}
  .idcard-header p{font-size:.72rem;margin:1px 0;color:#333;}
  .idcard-photo-row{display:flex;gap:16px;align-items:flex-start;margin-bottom:14px;}
  .idcard-photo{width:4cm;height:4cm;border:1px solid #999;object-fit:cover;background:#f5f5f5;}
  .idcard-roll{font-size:1rem;font-weight:bold;color:#DB3918;}
  .idcard-fields{font-size:.86rem;line-height:1.7;}
  .idcard-fields b{display:inline-block;width:130px;}
  .idcard-footer{display:flex;justify-content:space-between;align-items:flex-end;margin-top:26px;border-top:1px solid #ccc;padding-top:10px;}
  .idcard-footer img.qr{width:70px;height:70px;}
  .idcard-notice{font-size:.68rem;color:#b30000;text-align:center;margin-top:14px;font-weight:bold;}
  @media print{ .kcms-print-btn{display:none;} body{background:#fff;padding:0;} .idcard{border:none;} }
</style>
</head>
<body>
<div class="kcms-print-btn"><button onclick="window.print()">Print / Save as PDF</button></div>
<div class="idcard">
	<div class="idcard-header">
		<?php if ( $logo_url ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" class="logo"><?php endif; ?>
		<h1><?php echo esc_html( $college ); ?></h1>
		<p><?php echo esc_html( get_theme_mod( 'kc_address', '' ) ); ?> <?php echo esc_html( get_theme_mod( 'kc_pin', '' ) ); ?></p>
		<p><?php echo esc_html( get_theme_mod( 'kc_phone', '' ) ); ?></p>
	</div>
	<div class="idcard-photo-row">
		<?php if ( $card->photo_path ) : ?>
			<img src="<?php echo esc_url( $card->photo_path ); ?>" class="idcard-photo">
		<?php else : ?>
			<div class="idcard-photo"></div>
		<?php endif; ?>
		<div>
			<div class="idcard-roll">Roll No: <?php echo esc_html( $card->roll_number ); ?></div>
			<div class="idcard-fields">
				<div><b>Name:</b> <?php echo esc_html( $card->name ); ?></div>
				<div><b>Father/Guardian:</b> <?php echo esc_html( $card->father_name ); ?></div>
				<div><b>Date of Birth:</b> <?php echo esc_html( $card->dob ? gmdate( 'd-m-Y', strtotime( $card->dob ) ) : '' ); ?></div>
				<div><b>Class / Branch:</b> <?php echo esc_html( $card->class ); ?> <?php echo $card->branch ? '/ ' . esc_html( $card->branch ) : ''; ?></div>
				<div><b>Session:</b> <?php echo esc_html( $card->session ); ?></div>
				<div><b>Blood Group:</b> <?php echo esc_html( $card->blood_group ); ?></div>
			</div>
		</div>
	</div>
	<div class="idcard-footer">
		<img src="<?php echo esc_url( $qr_url ); ?>" class="qr" alt="QR">
		<div style="text-align:center;font-size:.75rem;">&nbsp;<br>Principal Signature<br>Issue Date: <?php echo esc_html( $card->issue_date ? gmdate( 'd-m-Y', strtotime( $card->issue_date ) ) : gmdate( 'd-m-Y' ) ); ?></div>
	</div>
	<div class="idcard-notice">NOT TRANSFERABLE &bull; RETURN ON LAST DAY OF SESSION</div>
</div>
</body>
</html>
