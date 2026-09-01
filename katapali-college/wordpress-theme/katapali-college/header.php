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
			<span class="tb-item"><i class="fa-solid fa-phone"></i><?php echo esc_html( get_theme_mod( 'kc_phone', kc_default( 'kc_phone' ) ) ); ?></span>
			<span class="tb-item"><i class="fa-solid fa-envelope"></i><?php echo esc_html( get_theme_mod( 'kc_email', kc_default( 'kc_email' ) ) ); ?></span>
			<span class="tb-item tb-item-address"><i class="fa-solid fa-location-dot"></i><?php echo esc_html( get_theme_mod( 'kc_address', kc_default( 'kc_address' ) ) ); ?></span>
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
	<div class="container site-header-main">
		<div class="brand-logo">
			<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( KC_URI . '/assets/demo-images/logo.svg' ); ?>" alt="<?php bloginfo( 'name' ); ?> logo"></a>
			<?php endif; ?>
		</div>
		<div class="brand-center">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="b-name"><?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?></a>
			<span class="b-address"><?php echo esc_html( get_theme_mod( 'kc_address', kc_default( 'kc_address' ) ) ); ?> - <?php echo esc_html( get_theme_mod( 'kc_pin', kc_default( 'kc_pin' ) ) ); ?></span>
		</div>
		<div class="header-actions">
			<a href="<?php echo esc_url( wp_login_url() ); ?>" class="admin-btn"><i class="fa-solid fa-user-shield"></i> <span>Admin Login</span></a>
			<button class="menu-toggle" id="kc-menu-toggle" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
		</div>
	</div>

	<div class="site-nav-bar">
		<div class="container">
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
					echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li></ul><p style="padding:14px;color:#fff;">No menu assigned — go to Appearance &rarr; Menus, or run the Demo Content Importer.</p>';
				}
				?>
			</nav>
		</div>
	</div>
	<div class="nav-overlay" id="kc-nav-overlay"></div>
</header>
