<?php
/**
 * Generic archive — dates, custom taxonomies, post-type archives.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="hr-container">

	<header class="hr-pagehead">
		<?php hauntedreal_breadcrumbs(); ?>

		<h1 class="hr-pagehead__title"><?php the_archive_title(); ?></h1>

		<?php if ( get_the_archive_description() ) : ?>
			<div class="hr-pagehead__desc"><?php the_archive_description(); ?></div>
		<?php endif; ?>

		<span class="hr-pagehead__count">
			<?php
			global $wp_query;

			printf(
				/* translators: %s: number of stories. */
				esc_html( _n( '%s story', '%s stories', $wp_query->found_posts, 'hauntedreal' ) ),
				esc_html( number_format_i18n( $wp_query->found_posts ) )
			);
			?>
		</span>
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
