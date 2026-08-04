<?php
/**
 * Customizer settings.
 *
 * Everything an editor needs to run the site without touching a template:
 * masthead copy, the six advertising slots, and the ghost story pipeline.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logo support, registered here so the branding controls have something to
 * attach to.
 */
function hauntedreal_logo_support() {
	add_theme_support( 'custom-logo', array(
		'height'      => 48,
		'width'       => 260,
		'flex-height' => true,
		'flex-width'  => true,
	) );
}
add_action( 'after_setup_theme', 'hauntedreal_logo_support' );

/**
 * Register controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function hauntedreal_customize_register( $wp_customize ) {

	$wp_customize->add_panel( 'hauntedreal_panel', array(
		'title'    => __( 'HauntedReal', 'hauntedreal' ),
		'priority' => 20,
	) );

	/* ------------------------------------------------------------------
	 * Masthead
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section( 'hauntedreal_masthead', array(
		'title' => __( 'Masthead & Footer', 'hauntedreal' ),
		'panel' => 'hauntedreal_panel',
	) );

	$wp_customize->add_setting( 'hauntedreal_topbar_text', array(
		'default'           => __( 'Verified paranormal reporting from across the United States', 'hauntedreal' ),
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'hauntedreal_topbar_text', array(
		'label'       => __( 'Top bar line (desktop only)', 'hauntedreal' ),
		'description' => __( 'A short credibility statement above the masthead.', 'hauntedreal' ),
		'section'     => 'hauntedreal_masthead',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'hauntedreal_footer_tagline', array(
		'default'           => __( 'HauntedReal investigates America\'s most persistent hauntings — and publishes the experiences readers cannot explain.', 'hauntedreal' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'hauntedreal_footer_tagline', array(
		'label'   => __( 'Footer tagline', 'hauntedreal' ),
		'section' => 'hauntedreal_masthead',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'hauntedreal_copyright', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'hauntedreal_copyright', array(
		'label'       => __( 'Copyright line', 'hauntedreal' ),
		'description' => __( 'Leave blank to use the site title and current year.', 'hauntedreal' ),
		'section'     => 'hauntedreal_masthead',
		'type'        => 'text',
	) );

	/* ------------------------------------------------------------------
	 * Advertising
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section( 'hauntedreal_ads', array(
		'title'       => __( 'Advertising Slots', 'hauntedreal' ),
		'description' => __(
			'Paste each network snippet into its slot below. Ad code lives here, never in a template, so networks can be swapped without touching the theme. Every in-page slot reserves its height in advance, so layout does not shift when an ad loads. An empty slot renders nothing at all.',
			'hauntedreal'
		) . '<br><br><strong>' . esc_html__( 'Adsterra:', 'hauntedreal' ) . '</strong> ' . __(
			'put a Banner in slots 01, 02 and 04, a Native Banner in slot 03, and the Social Bar in slot 07. Paste the whole snippet including every &lt;script&gt; tag and any &lt;div id="container-…"&gt; that comes with it.',
			'hauntedreal'
		),
		'panel'       => 'hauntedreal_panel',
	) );

	$wp_customize->add_setting( 'hauntedreal_ads_show_placeholders', array(
		'default'           => false,
		'sanitize_callback' => 'hauntedreal_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'hauntedreal_ads_show_placeholders', array(
		'label'       => __( 'Preview mode — show labelled placeholders', 'hauntedreal' ),
		'description' => __( 'Draws a marked box in every slot instead of running live ad code. Use it to review layout. Leave it off on a live site.', 'hauntedreal' ),
		'section'     => 'hauntedreal_ads',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'hauntedreal_intro_ad_paragraph', array(
		'default'           => 2,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'hauntedreal_intro_ad_paragraph', array(
		'label'       => __( 'Slot 02 position: after how many paragraphs?', 'hauntedreal' ),
		'description' => __( 'Set to 0 to place it above the first paragraph, at the very top of the article. 2 keeps the opening of the story clear.', 'hauntedreal' ),
		'section'     => 'hauntedreal_ads',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 20,
			'step' => 1,
		),
	) );

	foreach ( hauntedreal_ad_slots() as $slot => $config ) {
		$wp_customize->add_setting( 'hauntedreal_ad_' . $slot . '_enabled', array(
			'default'           => true,
			'sanitize_callback' => 'hauntedreal_sanitize_checkbox',
		) );
		$wp_customize->add_control( 'hauntedreal_ad_' . $slot . '_enabled', array(
			'label'   => sprintf(
				/* translators: %s: slot name. */
				__( 'Enable %s', 'hauntedreal' ),
				$config['name']
			),
			'section' => 'hauntedreal_ads',
			'type'    => 'checkbox',
		) );

		$wp_customize->add_setting( 'hauntedreal_ad_' . $slot . '_code', array(
			'default'           => hauntedreal_default_ad_code( $slot ),
			'sanitize_callback' => 'hauntedreal_sanitize_ad_code',
		) );
		$wp_customize->add_control( 'hauntedreal_ad_' . $slot . '_code', array(
			'label'       => $config['name'],
			'description' => $config['formats'] . ' — ' . $config['position'],
			'section'     => 'hauntedreal_ads',
			'type'        => 'textarea',
			'input_attrs' => array(
				'rows'        => 4,
				'placeholder' => '<script>…</script>',
			),
		) );

		// Overlay formats have no dimensions to outgrow, so they need no
		// second creative.
		if ( isset( $config['chrome'] ) && false === $config['chrome'] ) {
			continue;
		}

		$wp_customize->add_setting( 'hauntedreal_ad_' . $slot . '_code_mobile', array(
			'default'           => hauntedreal_default_ad_code( $slot, 'mobile' ),
			'sanitize_callback' => 'hauntedreal_sanitize_ad_code',
		) );
		$wp_customize->add_control( 'hauntedreal_ad_' . $slot . '_code_mobile', array(
			'label'       => sprintf(
				/* translators: %s: slot name. */
				__( '%s — mobile creative', 'hauntedreal' ),
				$config['name']
			),
			'description' => __( 'Optional. Only needed when the desktop creative is a fixed size that cannot fit a phone — a 728×90 leaderboard, for instance. Leave blank to serve the same creative everywhere. Exactly one of the two is ever inserted, so a hidden unit never burns an impression.', 'hauntedreal' ),
			'section'     => 'hauntedreal_ads',
			'type'        => 'textarea',
			'input_attrs' => array(
				'rows'        => 4,
				'placeholder' => __( 'Blank = same as above', 'hauntedreal' ),
			),
		) );
	}

	/* ------------------------------------------------------------------
	 * Ghost stories
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section( 'hauntedreal_ghost', array(
		'title' => __( 'Ghost Stories', 'hauntedreal' ),
		'panel' => 'hauntedreal_panel',
	) );

	$wp_customize->add_setting( 'hauntedreal_submissions_open', array(
		'default'           => true,
		'sanitize_callback' => 'hauntedreal_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'hauntedreal_submissions_open', array(
		'label'       => __( 'Accept new submissions', 'hauntedreal' ),
		'description' => __( 'Turn off to close the form without unpublishing the page.', 'hauntedreal' ),
		'section'     => 'hauntedreal_ghost',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'hauntedreal_submit_page', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'hauntedreal_submit_page', array(
		'label'       => __( 'Submission page', 'hauntedreal' ),
		'description' => __( 'The page using the "Submit Ghost Story" template. Used by every "Share your experience" button.', 'hauntedreal' ),
		'section'     => 'hauntedreal_ghost',
		'type'        => 'dropdown-pages',
	) );

	$wp_customize->add_setting( 'hauntedreal_moderation_email', array(
		'default'           => get_option( 'admin_email' ),
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'hauntedreal_moderation_email', array(
		'label'       => __( 'Send new submissions to', 'hauntedreal' ),
		'section'     => 'hauntedreal_ghost',
		'type'        => 'email',
	) );

	$wp_customize->add_setting( 'hauntedreal_ghost_disclaimer', array(
		'default'           => __( 'This account was submitted by a member of the HauntedReal community and reflects their personal experience. HauntedReal has not independently verified the events described.', 'hauntedreal' ),
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'hauntedreal_ghost_disclaimer', array(
		'label'       => __( 'Community experience disclaimer', 'hauntedreal' ),
		'description' => __( 'Shown at the end of every ghost story.', 'hauntedreal' ),
		'section'     => 'hauntedreal_ghost',
		'type'        => 'textarea',
	) );

	/* ------------------------------------------------------------------
	 * Live preview for the text bits
	 * ------------------------------------------------------------------ */

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->get_setting( 'hauntedreal_topbar_text' )->transport = 'postMessage';
		$wp_customize->selective_refresh->add_partial( 'hauntedreal_topbar_text', array(
			'selector'        => '.hr-topbar__text',
			'render_callback' => function () {
				return get_theme_mod( 'hauntedreal_topbar_text' );
			},
		) );
	}
}
add_action( 'customize_register', 'hauntedreal_customize_register' );

/**
 * Checkbox sanitizer.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function hauntedreal_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Ad code sanitizer.
 *
 * Ad tags are scripts and iframes; stripping them would defeat the purpose.
 * We keep the raw value for users who are allowed to post unfiltered HTML
 * (which on single-site installs is any administrator) and fall back to
 * post-level filtering for anyone else.
 *
 * @param string $value Raw ad markup.
 * @return string
 */
function hauntedreal_sanitize_ad_code( $value ) {
	if ( current_user_can( 'unfiltered_html' ) ) {
		return $value;
	}

	return wp_kses_post( $value );
}
