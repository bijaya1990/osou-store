<?php if ( ! defined( 'ABSPATH' ) ) exit; ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="topbar">
	<div class="container">
		<div class="tb-left">
			<span class="tb-item"><i class="fa-solid fa-phone"></i><?php echo esc_html( get_theme_mod( 'kc_phone', '+91 98765 43210' ) ); ?></span>
			<span class="tb-item"><i class="fa-solid fa-envelope"></i><?php echo esc_html( get_theme_mod( 'kc_email', 'info@katapalicollege.edu.in' ) ); ?></span>
			<span class="tb-item"><i class="fa-solid fa-location-dot"></i><?php echo esc_html( get_theme_mod( 'kc_address', 'AT/PO - KATAPALI, VIA - BIJEPUR, DISTRICT - BARGARH, ODISHA' ) ); ?></span>
		</div>
		<div class="tb-social">
			<a href="<?php echo esc_url( get_theme_mod( 'kc_facebook', '#' ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-facebook"></i></a>
			<a href="<?php echo esc_url( get_theme_mod( 'kc_twitter', '#' ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-twitter"></i></a>
			<a href="<?php echo esc_url( get_theme_mod( 'kc_youtube', '#' ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i></a>
			<a href="<?php echo esc_url( get_theme_mod( 'kc_instagram', '#' ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a>
		</div>
	</div>
</div>

<header class="site-header">
	<div class="container header-inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand">
			<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
				<img src="<?php echo esc_url( KC_URI . '/assets/demo-images/logo.svg' ); ?>" alt="<?php bloginfo( 'name' ); ?> logo">
			<?php endif; ?>
			<span><span class="b-name"><?php echo esc_html( get_theme_mod( 'kc_college_name', 'KATAPALI +3 COLLEGE, KATAPALI' ) ); ?></span><br>
			<span class="b-sub">Est. <?php echo esc_html( get_theme_mod( 'kc_established', '1985' ) ); ?> &bull; <?php echo esc_html( get_theme_mod( 'kc_affiliation', '' ) ); ?></span></span>
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
