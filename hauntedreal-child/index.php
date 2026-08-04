<?php
/**
 * Fallback template.
 *
 * Every view this theme cares about has a dedicated template; this catches
 * anything the hierarchy has no better answer for.
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
		<h1 class="hr-pagehead__title">
			<?php
			if ( is_archive() ) {
				the_archive_title();
			} else {
				esc_html_e( 'Latest Stories', 'hauntedreal' );
			}
			?>
		</h1>
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
