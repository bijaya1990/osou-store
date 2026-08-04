<?php
/**
 * Category archive.
 *
 * Existing HauntedReal categories and their URLs are untouched — this only
 * changes how they present. Sibling categories render as a scrollable chip
 * row so a reader can move sideways through the section on a phone without
 * the page itself ever scrolling horizontally.
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

		<span class="hr-kicker"><?php esc_html_e( 'Section', 'hauntedreal' ); ?></span>

		<h1 class="hr-pagehead__title"><?php single_cat_title(); ?></h1>

		<?php if ( category_description() ) : ?>
			<div class="hr-pagehead__desc"><?php echo wp_kses_post( category_description() ); ?></div>
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

	<?php
	// Children if this category has them, otherwise its siblings.
	$hauntedreal_siblings = get_categories( array(
		'parent'     => $hauntedreal_term->parent ? $hauntedreal_term->parent : 0,
		'hide_empty' => true,
		'number'     => 12,
	) );

	if ( count( $hauntedreal_siblings ) > 1 ) :
		?>
		<nav aria-label="<?php esc_attr_e( 'Related sections', 'hauntedreal' ); ?>">
			<ul class="hr-chips">
				<?php foreach ( $hauntedreal_siblings as $hauntedreal_sibling ) : ?>
					<li>
						<a href="<?php echo esc_url( get_category_link( $hauntedreal_sibling ) ); ?>"
							<?php echo (int) $hauntedreal_sibling->term_id === (int) $hauntedreal_term->term_id ? ' aria-current="true"' : ''; ?>>
							<?php echo esc_html( $hauntedreal_sibling->name ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php
	endif;
	?>

	<div class="hr-shell<?php echo hauntedreal_has_sidebar() ? '' : ' hr-shell--full'; ?>">
		<div class="hr-shell__main">
			<?php get_template_part( 'template-parts/loop', null, array( 'columns' => 3 ) ); ?>
		</div>

		<?php get_sidebar(); ?>
	</div>

</div>

<?php
get_footer();
