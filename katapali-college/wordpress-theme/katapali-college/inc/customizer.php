<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function kc_customize_register( $wp_customize ) {

	/* ---------------- College Info ---------------- */
	$wp_customize->add_section( 'kc_college_info', array( 'title' => 'College Info', 'priority' => 30 ) );
	$fields = array(
		'kc_college_name' => array( 'College Name', 'text', 'KATAPALI +3 COLLEGE, KATAPALI' ),
		'kc_tagline'      => array( 'Tagline', 'text', 'Empowering Rural Education Since 1985' ),
		'kc_address'      => array( 'Address', 'textarea', 'AT/PO - KATAPALI, VIA - BIJEPUR, DISTRICT - BARGARH, ODISHA' ),
		'kc_pin'          => array( 'PIN Code', 'text', '768032' ),
		'kc_phone'        => array( 'Phone', 'text', '+91 98765 43210' ),
		'kc_email'        => array( 'Email', 'text', 'info@katapalicollege.edu.in' ),
		'kc_established'  => array( 'Established Year', 'text', '1985' ),
		'kc_affiliation'  => array( 'Affiliation Line', 'text', 'Affiliated to Sambalpur University | Recognised under UGC 2(f) & 12(B)' ),
	);
	foreach ( $fields as $id => $f ) {
		$wp_customize->add_setting( $id, array( 'default' => $f[2], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $id, array( 'label' => $f[0], 'section' => 'kc_college_info', 'type' => $f[1] === 'textarea' ? 'textarea' : 'text' ) );
	}

	/* ---------------- Social Links ---------------- */
	$wp_customize->add_section( 'kc_social', array( 'title' => 'Social Media Links', 'priority' => 31 ) );
	$socials = array(
		'kc_facebook'  => array( 'Facebook URL', 'https://facebook.com/katapalicollege' ),
		'kc_twitter'   => array( 'Twitter / X URL', 'https://twitter.com/katapalicollege' ),
		'kc_youtube'   => array( 'YouTube URL', 'https://youtube.com/@katapalicollege' ),
		'kc_instagram' => array( 'Instagram URL', 'https://instagram.com/katapalicollege' ),
	);
	foreach ( $socials as $id => $f ) {
		$wp_customize->add_setting( $id, array( 'default' => $f[1], 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( $id, array( 'label' => $f[0], 'section' => 'kc_social', 'type' => 'url' ) );
	}

	/* ---------------- Colours ---------------- */
	$wp_customize->add_section( 'kc_colors', array( 'title' => 'Theme Colours', 'priority' => 32 ) );
	$colors = array(
		'kc_color_primary'   => array( 'Primary Colour', '#1e40af' ),
		'kc_color_secondary' => array( 'Secondary Colour', '#0f766e' ),
		'kc_color_accent'    => array( 'Accent Colour', '#f59e0b' ),
		'kc_color_dark'      => array( 'Dark / Header Colour', '#0b1e4f' ),
	);
	foreach ( $colors as $id => $f ) {
		$wp_customize->add_setting( $id, array( 'default' => $f[1], 'sanitize_callback' => 'sanitize_hex_color' ) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array( 'label' => $f[0], 'section' => 'kc_colors' ) ) );
	}

	/* ---------------- Principal ---------------- */
	$wp_customize->add_section( 'kc_principal', array( 'title' => "Principal's Message (Homepage)", 'priority' => 33 ) );
	$wp_customize->add_setting( 'kc_principal_photo', array( 'default' => '' ) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'kc_principal_photo', array( 'label' => 'Principal Photo', 'section' => 'kc_principal' ) ) );
	$p_fields = array(
		'kc_principal_name'  => array( 'Name', 'Dr. Demo Name' ),
		'kc_principal_desig' => array( 'Designation', 'Principal, Katapali +3 College' ),
		'kc_principal_qual'  => array( 'Qualification', 'M.A., Ph.D. (Political Science)' ),
	);
	foreach ( $p_fields as $id => $f ) {
		$wp_customize->add_setting( $id, array( 'default' => $f[1], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $id, array( 'label' => $f[0], 'section' => 'kc_principal', 'type' => 'text' ) );
	}
	$wp_customize->add_setting( 'kc_principal_message', array( 'default' => 'It gives me immense pleasure to welcome you to Katapali +3 College, an institution that has been serving the educational needs of rural Bargarh for four decades.', 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'kc_principal_message', array( 'label' => 'Welcome Message', 'section' => 'kc_principal', 'type' => 'textarea' ) );

	/* ---------------- Quick Stats ---------------- */
	$wp_customize->add_section( 'kc_stats', array( 'title' => 'Quick Stats (Homepage)', 'priority' => 34 ) );
	$stats = array(
		'kc_stat_students' => array( 'Total Students', '1284' ),
		'kc_stat_faculty'  => array( 'Total Faculty', '42' ),
		'kc_stat_depts'    => array( 'Departments', '10' ),
		'kc_stat_years'    => array( 'Years of Excellence', '40' ),
	);
	foreach ( $stats as $id => $f ) {
		$wp_customize->add_setting( $id, array( 'default' => $f[1], 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( $id, array( 'label' => $f[0], 'section' => 'kc_stats', 'type' => 'number' ) );
	}

	/* ---------------- Map ---------------- */
	$wp_customize->add_section( 'kc_map', array( 'title' => 'Google Map', 'priority' => 35 ) );
	$wp_customize->add_setting( 'kc_map_embed', array(
		'default' => '<iframe src="https://www.google.com/maps?q=Katapali%2C%20Bijepur%2C%20Bargarh%2C%20Odisha%20768032&output=embed" width="100%" height="400" style="border:0" allowfullscreen loading="lazy"></iframe>',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'kc_map_embed', array( 'label' => 'Map Embed Code (paste iframe from Google Maps > Share > Embed)', 'section' => 'kc_map', 'type' => 'textarea' ) );
	$wp_customize->add_setting( 'kc_map_note', array( 'default' => 'The college is located on the Bijepur–Katapali road, about 8 km from Bijepur Block Head Quarters.', 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'kc_map_note', array( 'label' => 'Directions Note', 'section' => 'kc_map', 'type' => 'textarea' ) );
}
add_action( 'customize_register', 'kc_customize_register' );
