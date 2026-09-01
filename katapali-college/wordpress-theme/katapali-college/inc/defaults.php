<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Single source of truth for every Customizer default. Used both by
   inc/customizer.php (so the control shows the right default) and by
   kc_apply_default_theme_mods() below (so get_theme_mod() returns a real
   value immediately on activation, before anyone has opened the
   Customizer and clicked Save - WordPress does NOT persist a setting's
   registered 'default' to theme_mods until it is actually saved). */
function kc_theme_mod_defaults() {
	return array(
		'kc_college_name'      => 'KATAPALI +3 COLLEGE, KATAPALI',
		'kc_tagline'           => 'Empowering Rural Education Since 1985',
		'kc_address'           => 'AT/PO - KATAPALI, VIA - BIJEPUR, DISTRICT - BARGARH, ODISHA',
		'kc_pin'               => '768032',
		'kc_phone'             => '+91 98765 43210',
		'kc_email'             => 'info@katapalicollege.edu.in',
		'kc_established'       => '1985',
		'kc_affiliation'       => 'Affiliated to Sambalpur University | Recognised under UGC 2(f) & 12(B)',
		'kc_facebook'          => 'https://facebook.com/katapalicollege',
		'kc_twitter'           => 'https://twitter.com/katapalicollege',
		'kc_youtube'           => 'https://youtube.com/@katapalicollege',
		'kc_instagram'         => 'https://instagram.com/katapalicollege',
		'kc_whatsapp'          => 'https://wa.me/919876543210',
		'kc_logo_size'         => '70',
		'kc_color_primary'     => '#012D58',
		'kc_color_secondary'   => '#0f766e',
		'kc_color_accent'      => '#DB3918',
		'kc_color_dark'        => '#001A33',
		'kc_color_gold'        => '#EBC30F',
		'kc_principal_photo'   => '',
		'kc_principal_name'    => 'Dr. Demo Name',
		'kc_principal_desig'   => 'Principal, Katapali +3 College',
		'kc_principal_qual'    => 'M.A., Ph.D. (Political Science)',
		'kc_principal_message' => 'It gives me immense pleasure to welcome you to Katapali +3 College, an institution that has been serving the educational needs of rural Bargarh for four decades. Our aim is not merely to prepare students for examinations, but to shape responsible citizens with knowledge, discipline and compassion.',
		'kc_stat_students'     => '1284',
		'kc_stat_faculty'      => '42',
		'kc_stat_depts'        => '10',
		'kc_stat_years'        => '40',
		'kc_map_embed'         => '<iframe src="https://www.google.com/maps?q=Katapali%2C%20Bijepur%2C%20Bargarh%2C%20Odisha%20768032&output=embed" width="100%" height="400" style="border:0" allowfullscreen loading="lazy"></iframe>',
		'kc_map_note'          => 'The college is located on the Bijepur-Katapali road, about 8 km from Bijepur Block Head Quarters and 55 km from Bargarh town.',
		'kc_footer_about'      => 'KATAPALI +3 COLLEGE, KATAPALI is a premier rural degree college offering +3 Arts streams with a commitment to accessible, affordable and quality higher education.',
	);
}

/* Read a default by key - used throughout the templates instead of
   hardcoding (and risking drift from) the value a second time. */
function kc_default( $key ) {
	$d = kc_theme_mod_defaults();
	return isset( $d[ $key ] ) ? $d[ $key ] : '';
}

/* Persist every default straight into theme_mods so the site is fully
   populated the moment the theme is activated - not only after an admin
   opens the Customizer and clicks Save. Never overwrites a value the
   admin (or the demo importer) has already set. */
function kc_apply_default_theme_mods() {
	$existing = get_theme_mods();
	foreach ( kc_theme_mod_defaults() as $key => $value ) {
		if ( ! isset( $existing[ $key ] ) ) {
			set_theme_mod( $key, $value );
		}
	}
}
add_action( 'after_switch_theme', 'kc_apply_default_theme_mods' );
