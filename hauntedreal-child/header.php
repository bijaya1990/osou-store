<?php
/**
 * The masthead.
 *
 * Compact on mobile (56px, hamburger + wordmark + search), a full editorial
 * masthead from 1024px up. GeneratePress' own header hooks still fire so
 * plugins that target them keep working.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="hr-skip" href="#hr-content"><?php esc_html_e( 'Skip to content', 'hauntedreal' ); ?></a>

<?php do_action( 'generate_before_header' ); ?>

<div class="hr-topbar">
	<div class="hr-container hr-topbar__inner">
		<span class="hr-topbar__text"><?php echo esc_html( get_theme_mod( 'hauntedreal_topbar_text', __( 'Verified paranormal reporting from across the United States', 'hauntedreal' ) ) ); ?></span>
		<?php
		if ( has_nav_menu( 'topbar' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'topbar',
				'container'      => false,
				'menu_class'     => 'hr-topbar__links',
				'depth'          => 1,
			) );
		} else {
			echo '<span class="hr-topbar__date">' . esc_html( hauntedreal_current_date() ) . '</span>';
		}
		?>
	</div>
</div>

<header class="hr-header">
	<div class="hr-container hr-header__bar">

		<button class="hr-iconbtn hr-navtoggle" type="button"
			aria-expanded="false" aria-controls="hr-primary-nav">
			<span class="hr-navtoggle__box" aria-hidden="true"><i></i><i></i><i></i></span>
			<span class="hr-screen-reader-text"><?php esc_html_e( 'Menu', 'hauntedreal' ); ?></span>
		</button>

		<?php if ( has_custom_logo() ) : ?>
			<div class="hr-logo"><?php the_custom_logo(); ?></div>
		<?php else : ?>
			<a class="hr-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<b><?php echo esc_html( get_bloginfo( 'name' ) ); ?></b>
			</a>
		<?php endif; ?>

		<button class="hr-iconbtn hr-iconbtn--search hr-searchtoggle" type="button"
			aria-expanded="false" aria-controls="hr-search-panel">
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
				<circle cx="11" cy="11" r="7"></circle>
				<line x1="16.5" y1="16.5" x2="21" y2="21"></line>
			</svg>
			<span class="hr-screen-reader-text"><?php esc_html_e( 'Search', 'hauntedreal' ); ?></span>
		</button>

		<nav id="hr-primary-nav" class="hr-nav" aria-label="<?php esc_attr_e( 'Primary', 'hauntedreal' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_id'        => 'hr-menu-primary',
				'depth'          => 2,
				'fallback_cb'    => 'hauntedreal_default_menu',
			) );
			?>
		</nav>

	</div>

	<div id="hr-search-panel" class="hr-searchpanel">
		<div class="hr-container">
			<?php get_search_form(); ?>
		</div>
	</div>
</header>

<?php do_action( 'generate_after_header' ); ?>

<div class="hr-container">
	<?php
	/**
	 * AD SLOT 01 — Header / Leaderboard.
	 * 728×90 desktop, 320×50 mobile. Height reserved in CSS.
	 */
	do_action( 'hauntedreal_header_ad' );
	?>
</div>

<main id="hr-content" class="hr-main">
