<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function kc_customize_register( $wp_customize ) {

	/* ---------------- College Info ---------------- */
	$wp_customize->add_section( 'kc_college_info', array( 'title' => 'College Info', 'priority' => 30 ) );
	$fields = array(
		'kc_college_name' => array( 'College Name', 'text' ),
		'kc_tagline'      => array( 'Tagline', 'text' ),
		'kc_address'      => array( 'Address', 'textarea' ),
		'kc_pin'          => array( 'PIN Code', 'text' ),
		'kc_phone'        => array( 'Phone', 'text' ),
		'kc_email'        => array( 'Email', 'text' ),
		'kc_established'  => array( 'Established Year', 'text' ),
		'kc_affiliation'  => array( 'Affiliation Line', 'text' ),
	);
	foreach ( $fields as $id => $f ) {
		$wp_customize->add_setting( $id, array( 'default' => kc_default( $id ), 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $id, array( 'label' => $f[0], 'section' => 'kc_college_info', 'type' => $f[1] === 'textarea' ? 'textarea' : 'text' ) );
	}
	$wp_customize->add_setting( 'kc_footer_about', array( 'default' => kc_default( 'kc_footer_about' ), 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'kc_footer_about', array( 'label' => 'Footer "About" paragraph', 'section' => 'kc_college_info', 'type' => 'textarea' ) );

	/* ---------------- Social Links ---------------- */
	$wp_customize->add_section( 'kc_social', array( 'title' => 'Social Media Links', 'priority' => 31 ) );
	$socials = array(
		'kc_facebook'  => 'Facebook URL',
		'kc_twitter'   => 'Twitter / X URL',
		'kc_youtube'   => 'YouTube URL',
		'kc_instagram' => 'Instagram URL',
		'kc_whatsapp'  => 'WhatsApp URL',
	);
	foreach ( $socials as $id => $label ) {
		$wp_customize->add_setting( $id, array( 'default' => kc_default( $id ), 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( $id, array( 'label' => $label, 'section' => 'kc_social', 'type' => 'url' ) );
	}

	/* ---------------- Header Logo ---------------- */
	$wp_customize->add_section( 'kc_topbar', array( 'title' => 'Header Logo Size', 'priority' => 32 ) );
	$wp_customize->add_setting( 'kc_logo_size', array( 'default' => kc_default( 'kc_logo_size' ), 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'kc_logo_size', array( 'label' => 'Logo Size (px, 40-160)', 'section' => 'kc_topbar', 'type' => 'number', 'input_attrs' => array( 'min' => 40, 'max' => 160, 'step' => 2 ) ) );

	/* ---------------- Colours ---------------- */
	$wp_customize->add_section( 'kc_colors', array( 'title' => 'Theme Colours', 'priority' => 32 ) );
	$colors = array(
		'kc_color_primary'   => 'Primary Colour',
		'kc_color_secondary' => 'Secondary Colour',
		'kc_color_accent'    => 'Accent Colour',
		'kc_color_dark'      => 'Dark / Header Colour',
		'kc_color_gold'      => 'Gold Colour (footer highlights)',
	);
	foreach ( $colors as $id => $label ) {
		$wp_customize->add_setting( $id, array( 'default' => kc_default( $id ), 'sanitize_callback' => 'sanitize_hex_color' ) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array( 'label' => $label, 'section' => 'kc_colors' ) ) );
	}

	/* ---------------- Principal ---------------- */
	$wp_customize->add_section( 'kc_principal', array( 'title' => "Principal's Message (Homepage)", 'priority' => 33 ) );
	$wp_customize->add_setting( 'kc_principal_photo', array( 'default' => kc_default( 'kc_principal_photo' ) ) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'kc_principal_photo', array( 'label' => 'Principal Photo', 'section' => 'kc_principal' ) ) );
	$p_fields = array(
		'kc_principal_name'  => 'Name',
		'kc_principal_desig' => 'Designation',
		'kc_principal_qual'  => 'Qualification',
	);
	foreach ( $p_fields as $id => $label ) {
		$wp_customize->add_setting( $id, array( 'default' => kc_default( $id ), 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $id, array( 'label' => $label, 'section' => 'kc_principal', 'type' => 'text' ) );
	}
	$wp_customize->add_setting( 'kc_principal_message', array( 'default' => kc_default( 'kc_principal_message' ), 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'kc_principal_message', array( 'label' => 'Welcome Message', 'section' => 'kc_principal', 'type' => 'textarea' ) );

	/* ---------------- Quick Stats ---------------- */
	$wp_customize->add_section( 'kc_stats', array( 'title' => 'Quick Stats (Homepage)', 'priority' => 34 ) );
	$stats = array(
		'kc_stat_students' => 'Total Students',
		'kc_stat_faculty'  => 'Total Faculty',
		'kc_stat_depts'    => 'Departments',
		'kc_stat_years'    => 'Years of Excellence',
	);
	foreach ( $stats as $id => $label ) {
		$wp_customize->add_setting( $id, array( 'default' => kc_default( $id ), 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( $id, array( 'label' => $label, 'section' => 'kc_stats', 'type' => 'number' ) );
	}

	/* ---------------- Map ---------------- */
	$wp_customize->add_section( 'kc_map', array( 'title' => 'Google Map', 'priority' => 35 ) );
	$wp_customize->add_setting( 'kc_map_embed', array( 'default' => kc_default( 'kc_map_embed' ), 'sanitize_callback' => 'wp_kses_post' ) );
	$wp_customize->add_control( 'kc_map_embed', array( 'label' => 'Map Embed Code (paste iframe from Google Maps > Share > Embed)', 'section' => 'kc_map', 'type' => 'textarea' ) );
	$wp_customize->add_setting( 'kc_map_note', array( 'default' => kc_default( 'kc_map_note' ), 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'kc_map_note', array( 'label' => 'Directions Note', 'section' => 'kc_map', 'type' => 'textarea' ) );
}
add_action( 'customize_register', 'kc_customize_register' );
