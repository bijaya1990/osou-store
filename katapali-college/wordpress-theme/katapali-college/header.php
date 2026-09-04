<?php if ( ! defined( 'ABSPATH' ) ) exit; ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="deco-border"></div>

<header class="site-header">
	<div class="header-top-bg">
	<div class="container site-header-main">
		<div class="brand-logo">
			<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( KC_URI . '/assets/demo-images/logo.svg' ); ?>" alt="<?php bloginfo( 'name' ); ?> logo"></a>
			<?php endif; ?>
		</div>
		<div class="brand-center">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="b-name"><?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?></a>
			<span class="b-address"><?php echo esc_html( get_theme_mod( 'kc_address', kc_default( 'kc_address' ) ) ); ?> - <?php echo esc_html( get_theme_mod( 'kc_pin', kc_default( 'kc_pin' ) ) ); ?> &bull; <?php echo esc_html( get_theme_mod( 'kc_phone', kc_default( 'kc_phone' ) ) ); ?></span>
		</div>
		<div class="header-actions">
			<a href="<?php echo esc_url( wp_login_url() ); ?>" class="admin-btn"><i class="fa-solid fa-user-shield"></i> <span>Admin Login</span></a>
			<button class="menu-toggle" id="kc-menu-toggle" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
		</div>
	</div>
	</div>

	<div class="deco-border deco-border-alt"></div>

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
					echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li></ul><p style="padding:14px;color:#900;">No menu assigned — go to Appearance &rarr; Menus, or run the Demo Content Importer.</p>';
				}
				?>
			</nav>
		</div>
	</div>
	<div class="nav-overlay" id="kc-nav-overlay"></div>
</header>
