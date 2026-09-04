<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Renders the ID Card / Library Card as a standalone printable HTML page
   (own <html>, no wp-admin chrome) with a "Print / Save as PDF" button -
   the same reliable, dependency-free pattern the theme's Applications
   feature uses for its printable letters, so there's no PHP PDF library
   to worry about on shared hosting. Admin-only: the caller already
   checked manage_options + a per-card nonce before this runs. */
class KSR_Cards {

	public static function render( $type, $student_id ) {
		global $wpdb;
		$s = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . KSR_Install::table_name() . " WHERE id = %d", $student_id ) );
		if ( ! $s ) wp_die( 'Student not found.' );

		$fields = json_decode( $s->fields_json, true );
		if ( ! is_array( $fields ) ) $fields = array();

		$college_name = get_theme_mod( 'kc_college_name', 'Katapali +3 College, Katapali' );
		$address      = get_theme_mod( 'kc_address', 'AT/PO - Katapali, Via - Bijepur, District - Bargarh, Odisha' );
		$pin          = get_theme_mod( 'kc_pin', '768032' );
		$logo_id      = get_theme_mod( 'custom_logo' );
		$logo_url     = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
		$photo_url    = $s->photo_attachment_id ? wp_get_attachment_image_url( $s->photo_attachment_id, 'medium' ) : '';
		$principal    = get_theme_mod( 'kc_principal_name', '' );

		$data = array(
			'name' => $s->name, 'roll_no' => $s->roll_no, 'batch_year' => $s->batch_year, 'stream' => $s->stream,
			'dob' => $s->dob, 'mobile' => $s->mobile, 'id_card_no' => $s->id_card_no,
			'father_name' => $fields['father_name'] ?? '', 'blood_group' => $fields['blood_group'] ?? '',
			'address' => $fields['address'] ?? '', 'subject_name' => $fields['subject_name'] ?? '',
			'college_name' => $college_name, 'address_line' => $address . ' - ' . $pin,
			'logo_url' => $logo_url, 'photo_url' => $photo_url, 'principal' => $principal,
		);

		header( 'Content-Type: text/html; charset=utf-8' );
		if ( $type === 'library' ) {
			self::render_library_card( $data );
		} else {
			self::render_id_card( $data );
		}
		exit;
	}

	private static function page_open( $title ) {
		?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo esc_html( $title ); ?></title>
<style>
* { box-sizing: border-box; }
body { font-family: Arial, Helvetica, sans-serif; background: #ececec; margin: 0; padding: 24px; }
.ksr-toolbar { max-width: 900px; margin: 0 auto 18px; text-align: center; }
.ksr-toolbar button { background: #012D58; color: #fff; border: none; padding: 10px 22px; font-size: 15px; border-radius: 5px; cursor: pointer; }
.ksr-toolbar button:hover { background: #001A33; }
.card-sheet { background: #fff; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,.15); }
@media print {
	body { background: #fff; padding: 0; }
	.ksr-toolbar { display: none; }
	/* margin stays "0 auto" (not "0") so a card narrower than the page
	   (the library card on A4) still centers horizontally instead of
	   sticking to the left edge. */
	.card-sheet { box-shadow: none; margin: 0 auto; }
}
</style>
</head>
<body>
<div class="ksr-toolbar"><button onclick="window.print()">Print / Save as PDF</button></div>
		<?php
	}

	private static function page_close() {
		echo '</body></html>';
	}

	/* --------------------------------- ID card -------------------------------- */

	private static function render_id_card( $d ) {
		self::page_open( 'ID Card - ' . $d['name'] );
		?>
<style>
/* CR80 card size (85.6mm x 54mm) x2, side by side, folded down the
   middle so front (left) and back (right) become the two faces of one
   physical card. */
.card-sheet.idcard { width: 171.2mm; height: 54mm; display: flex; position: relative; }
.card-sheet.idcard .fold-line { position: absolute; left: 50%; top: 0; bottom: 0; border-left: 1px dashed #999; }
.id-face { width: 85.6mm; height: 54mm; overflow: hidden; position: relative; display: flex; flex-direction: column; }
.id-head { background: linear-gradient(135deg,#012D58,#001A33); color: #fff; padding: 3mm 3mm 2mm; text-align: center; flex-shrink: 0; }
.id-head img { height: 8mm; vertical-align: middle; margin-right: 2mm; }
.id-head .cname { font-size: 8.5pt; font-weight: 800; line-height: 1.15; display: inline-block; vertical-align: middle; max-width: 62mm; }
.id-head .caddr { font-size: 5.3pt; margin-top: 1mm; opacity: .9; line-height: 1.2; }
.id-body { flex: 1; display: flex; padding: 2.2mm 3mm; gap: 2.5mm; }
.id-photo { width: 18mm; height: 22mm; border: 1px solid #ccc; object-fit: cover; flex-shrink: 0; background: #f2f2f2; }
.id-fields { font-size: 6.3pt; line-height: 1.55; }
.id-fields b { display: inline-block; width: 15mm; font-weight: 700; }
.id-foot { display: flex; justify-content: space-between; align-items: flex-end; padding: 0 3mm 2mm; font-size: 5.5pt; }
.id-foot .sign { text-align: center; }
.id-foot .sign .line { width: 20mm; border-top: 1px solid #333; margin-bottom: 1mm; }

.id-back { padding: 3mm; font-size: 5.6pt; line-height: 1.45; }
.id-back h4 { margin: 0 0 1.5mm; font-size: 7.5pt; color: #012D58; border-bottom: 1px solid #012D58; padding-bottom: 1mm; }
.id-back ul { margin: 0 0 2mm; padding-left: 3.5mm; }
.id-back .not-transferable { font-weight: 700; text-align: center; margin: 1.5mm 0; }
.id-back table.renew { width: 100%; border-collapse: collapse; margin-top: 1mm; font-size: 5.3pt; }
.id-back table.renew th, .id-back table.renew td { border: 1px solid #ccc; padding: 0.8mm 1.5mm; text-align: left; }
@page { size: 175mm 58mm; margin: 2mm; }
</style>
<div class="card-sheet idcard">
	<div class="fold-line"></div>
	<div class="id-face id-front">
		<div class="id-head">
			<?php if ( $d['logo_url'] ) : ?><img src="<?php echo esc_url( $d['logo_url'] ); ?>" alt="">
			<?php endif; ?>
			<span class="cname"><?php echo esc_html( strtoupper( $d['college_name'] ) ); ?></span>
			<div class="caddr"><?php echo esc_html( $d['address_line'] ); ?></div>
		</div>
		<div class="id-body">
			<img class="id-photo" src="<?php echo esc_url( $d['photo_url'] ?: KSR_URI . '/assets/no-photo.svg' ); ?>" alt="">
			<div class="id-fields">
				<div><b>Name</b> <?php echo esc_html( $d['name'] ); ?></div>
				<div><b>Father</b> <?php echo esc_html( $d['father_name'] ); ?></div>
				<div><b>Roll No</b> <?php echo esc_html( $d['roll_no'] ); ?></div>
				<div><b>Class</b> <?php echo esc_html( $d['stream'] ); ?> (<?php echo esc_html( $d['batch_year'] ); ?>)</div>
				<div><b>DOB</b> <?php echo esc_html( $d['dob'] ); ?></div>
				<div><b>Blood Grp</b> <?php echo esc_html( $d['blood_group'] ); ?></div>
				<div><b>Mobile</b> <?php echo esc_html( $d['mobile'] ); ?></div>
			</div>
		</div>
		<div class="id-foot">
			<div>ID No: <?php echo esc_html( $d['id_card_no'] ); ?></div>
			<div class="sign"><div class="line"></div>Principal</div>
		</div>
	</div>
	<div class="id-face id-back">
		<h4>Rules &amp; Instructions</h4>
		<ul>
			<li>This card must be carried at all times within the college campus.</li>
			<li>Loss of this card must be reported to the college office immediately.</li>
			<li>Card must be produced on demand to any college staff.</li>
			<li>Valid only for the academic session printed on this card.</li>
		</ul>
		<div class="not-transferable">This card is not transferable.</div>
		<table class="renew">
			<tr><th>Session</th><th>Renewed On</th><th>Principal's Sign</th></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
		</table>
	</div>
</div>
		<?php
		self::page_close();
	}

	/* ------------------------------ Library card ------------------------------ */

	private static function render_library_card( $d ) {
		self::page_open( 'Library Card - ' . $d['name'] );
		?>
<style>
.card-sheet.libcard { width: 170mm; padding: 4mm 6mm 6mm; }
.lib-head { text-align: center; border-bottom: 2px solid #012D58; padding-bottom: 2mm; margin-bottom: 3mm; }
.lib-head img { height: 12mm; display: block; margin: 0 auto 1.5mm; }
.lib-head .cname { font-size: 12pt; font-weight: 800; color: #012D58; }
.lib-head .caddr { font-size: 7.5pt; color: #555; margin-top: 1mm; }
.lib-title { text-align: center; font-size: 13pt; font-weight: 800; letter-spacing: 1px; margin: 3mm 0; }
.lib-issued { font-size: 9pt; margin-bottom: 3mm; display: flex; flex-wrap: wrap; gap: 3mm 8mm; }
.lib-issued span b { display: inline-block; min-width: 20mm; }
table.lib-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
table.lib-table th, table.lib-table td { border: 1px solid #333; padding: 2mm; text-align: left; }
table.lib-table th { background: #f2f2f2; }
table.lib-table td.rowno { width: 8mm; text-align: center; color: #999; }
@page { size: A4; margin: 10mm; }
</style>
<div class="card-sheet libcard">
	<div class="lib-head">
		<?php if ( $d['logo_url'] ) : ?><img src="<?php echo esc_url( $d['logo_url'] ); ?>" alt="">
		<?php endif; ?>
		<div class="cname"><?php echo esc_html( strtoupper( $d['college_name'] ) ); ?></div>
		<div class="caddr"><?php echo esc_html( $d['address_line'] ); ?></div>
	</div>
	<div class="lib-title">LIBRARY CARD</div>
	<div class="lib-issued">
		<span><b>Issued to:</b> <?php echo esc_html( $d['name'] ); ?></span>
		<span><b>Roll No:</b> <?php echo esc_html( $d['roll_no'] ); ?></span>
		<span><b>Class:</b> <?php echo esc_html( $d['stream'] ); ?> (<?php echo esc_html( $d['batch_year'] ); ?>)</span>
		<?php if ( $d['subject_name'] ) : ?><span><b>Subject:</b> <?php echo esc_html( $d['subject_name'] ); ?></span><?php endif; ?>
	</div>
	<table class="lib-table">
		<tr><th class="rowno">#</th><th>Date Issued</th><th>Book Title</th><th>Date Returned</th></tr>
		<?php for ( $i = 1; $i <= 20; $i++ ) : ?>
		<tr><td class="rowno"><?php echo (int) $i; ?></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
		<?php endfor; ?>
	</table>
</div>
		<?php
		self::page_close();
	}
}
