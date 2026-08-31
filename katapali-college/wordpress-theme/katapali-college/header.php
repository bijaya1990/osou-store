<?php if ( ! defined( 'ABSPATH' ) ) exit; ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php $logo_l = get_theme_mod( 'kc_topbar_logo_left', kc_default( 'kc_topbar_logo_left' ) ); $logo_r = get_theme_mod( 'kc_topbar_logo_right', kc_default( 'kc_topbar_logo_right' ) ); ?>
<div class="micro-bar">
	<div class="container micro-bar-inner">
		<div class="mb-logo mb-logo-left"><?php if ( $logo_l ) echo '<img src="' . esc_url( $logo_l ) . '" alt="">'; ?></div>
		<div class="mb-links">
			<a href="<?php echo esc_url( get_theme_mod( 'kc_feedback_link', kc_default( 'kc_feedback_link' ) ) ?: home_url( '/contact-us/' ) ); ?>"><i class="fa-solid fa-comment-dots"></i> Feedback</a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'kc_gallery' ) ); ?>"><i class="fa-solid fa-images"></i> Gallery</a>
			<a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><i class="fa-solid fa-phone"></i> Contact Us</a>
		</div>
		<div class="mb-logo mb-logo-right"><?php if ( $logo_r ) echo '<img src="' . esc_url( $logo_r ) . '" alt="">'; ?></div>
	</div>
</div>

<div class="topbar">
	<div class="container">
		<div class="tb-left">
			<span class="tb-item"><i class="fa-solid fa-phone"></i><?php echo esc_html( get_theme_mod( 'kc_phone', kc_default( 'kc_phone' ) ) ); ?></span>
			<span class="tb-item"><i class="fa-solid fa-envelope"></i><?php echo esc_html( get_theme_mod( 'kc_email', kc_default( 'kc_email' ) ) ); ?></span>
			<span class="tb-item"><i class="fa-solid fa-location-dot"></i><?php echo esc_html( get_theme_mod( 'kc_address', kc_default( 'kc_address' ) ) ); ?></span>
		</div>
		<div class="tb-social">
			<a href="<?php echo esc_url( get_theme_mod( 'kc_facebook', kc_default( 'kc_facebook' ) ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-facebook"></i></a>
			<a href="<?php echo esc_url( get_theme_mod( 'kc_twitter', kc_default( 'kc_twitter' ) ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-twitter"></i></a>
			<a href="<?php echo esc_url( get_theme_mod( 'kc_youtube', kc_default( 'kc_youtube' ) ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i></a>
			<a href="<?php echo esc_url( get_theme_mod( 'kc_instagram', kc_default( 'kc_instagram' ) ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a>
			<a href="<?php echo esc_url( get_theme_mod( 'kc_whatsapp', kc_default( 'kc_whatsapp' ) ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i></a>
		</div>
	</div>
</div>

<header class="site-header">
	<div class="container header-inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand">
			<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
				<img src="<?php echo esc_url( KC_URI . '/assets/demo-images/logo.svg' ); ?>" alt="<?php bloginfo( 'name' ); ?> logo">
			<?php endif; ?>
			<span><span class="b-name"><?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?></span><br>
			<span class="b-sub">Est. <?php echo esc_html( get_theme_mod( 'kc_established', kc_default( 'kc_established' ) ) ); ?> &bull; <?php echo esc_html( get_theme_mod( 'kc_affiliation', kc_default( 'kc_affiliation' ) ) ); ?></span></span>
		</a>
		<nav class="main-nav" id="kc-nav">
			<button class="nav-close" id="kc-nav-close"><i class="fa-solid fa-xmark"></i></button>
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container' => false,
					'items_wrap' => '<ul>%3$s</ul>',
					'walker' => new KC_Nav_Walker(),
				) );
			} else {
				echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li></ul><p style="padding:14px;color:#900;">No menu assigned — go to Appearance &rarr; Menus, or run the Demo Content Importer.</p>';
			}
			?>
		</nav>
		<div class="header-actions">
			<a href="<?php echo esc_url( wp_login_url() ); ?>" class="admin-btn"><i class="fa-solid fa-user-shield"></i> Admin Login</a>
			<button class="menu-toggle" id="kc-menu-toggle"><i class="fa-solid fa-bars"></i></button>
		</div>
	</div>
	<div class="nav-overlay" id="kc-nav-overlay"></div>
</header>
