<?php
/**
 * The blog index.
 *
 * Used when a static front page is assigned and posts live at their own URL.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hauntedreal_posts_page = (int) get_option( 'page_for_posts' );
?>

<div class="hr-container">

	<header class="hr-pagehead">
		<?php hauntedreal_breadcrumbs(); ?>

		<span class="hr-kicker"><?php esc_html_e( 'All Reporting', 'hauntedreal' ); ?></span>

		<h1 class="hr-pagehead__title">
			<?php
			echo esc_html( $hauntedreal_posts_page ? get_the_title( $hauntedreal_posts_page ) : __( 'Latest Investigations', 'hauntedreal' ) );
			?>
		</h1>

		<?php if ( $hauntedreal_posts_page && get_post_field( 'post_excerpt', $hauntedreal_posts_page ) ) : ?>
			<p class="hr-pagehead__desc">
				<?php echo esc_html( get_post_field( 'post_excerpt', $hauntedreal_posts_page ) ); ?>
			</p>
		<?php endif; ?>
	</header>

	<div class="hr-shell<?php echo hauntedreal_has_sidebar() ? '' : ' hr-shell--full'; ?>">
		<div class="hr-shell__main">
			<?php get_template_part( 'template-parts/loop', null, array( 'columns' => 3 ) ); ?>
		</div>

		<?php get_sidebar(); ?>
	</div>

</div>

<?php
get_footer();
