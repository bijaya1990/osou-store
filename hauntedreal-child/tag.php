<?php
/**
 * Tag archive.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hauntedreal_term = get_queried_object();
?>

<div class="hr-container">

	<header class="hr-pagehead">
		<?php hauntedreal_breadcrumbs(); ?>

		<span class="hr-kicker"><?php esc_html_e( 'Tagged', 'hauntedreal' ); ?></span>

		<h1 class="hr-pagehead__title"><?php single_tag_title(); ?></h1>

		<?php if ( tag_description() ) : ?>
			<div class="hr-pagehead__desc"><?php echo wp_kses_post( tag_description() ); ?></div>
		<?php endif; ?>

		<span class="hr-pagehead__count">
			<?php
			printf(
				/* translators: %s: number of stories. */
				esc_html( _n( '%s story', '%s stories', (int) $hauntedreal_term->count, 'hauntedreal' ) ),
				esc_html( number_format_i18n( $hauntedreal_term->count ) )
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
