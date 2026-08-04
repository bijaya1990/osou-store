<?php
/**
 * A single US state within Haunted America.
 *
 * Mixes editorial investigations and community accounts for one state —
 * the card variants keep the two visually distinct within the same grid.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wp_query;

$hauntedreal_state = get_queried_object();
?>

<div class="hr-container">

	<header class="hr-statehead">
		<?php hauntedreal_breadcrumbs(); ?>

		<span class="hr-kicker hr-kicker--spectral hr-statehead__eyebrow"><?php esc_html_e( 'Haunted America', 'hauntedreal' ); ?></span>

		<h1 class="hr-pagehead__title"><?php echo esc_html( $hauntedreal_state->name ); ?></h1>

		<?php if ( $hauntedreal_state->description ) : ?>
			<p class="hr-pagehead__desc"><?php echo esc_html( $hauntedreal_state->description ); ?></p>
		<?php endif; ?>

		<span class="hr-pagehead__count">
			<?php
			printf(
				/* translators: %s: number of stories. */
				esc_html( _n( '%s story from this state', '%s stories from this state', $wp_query->found_posts, 'hauntedreal' ) ),
				esc_html( number_format_i18n( $wp_query->found_posts ) )
			);
			?>
		</span>
	</header>

	<?php
	// Neighbouring states by story count — sideways movement without a map.
	$hauntedreal_others = get_terms( array(
		'taxonomy'   => 'us_state',
		'hide_empty' => true,
		'number'     => 12,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'exclude'    => array( $hauntedreal_state->term_id ),
	) );

	if ( $hauntedreal_others && ! is_wp_error( $hauntedreal_others ) ) :
		?>
		<nav aria-label="<?php esc_attr_e( 'Other states', 'hauntedreal' ); ?>">
			<ul class="hr-chips">
				<li><a href="<?php echo esc_url( get_term_link( $hauntedreal_state ) ); ?>" aria-current="true"><?php echo esc_html( $hauntedreal_state->name ); ?></a></li>
				<?php foreach ( $hauntedreal_others as $hauntedreal_other ) : ?>
					<li>
						<a href="<?php echo esc_url( get_term_link( $hauntedreal_other ) ); ?>">
							<?php echo esc_html( $hauntedreal_other->name ); ?>
						</a>
					</li>
				<?php endforeach; ?>
				<li><a href="<?php echo esc_url( home_url( '/haunted-america/' ) ); ?>"><?php esc_html_e( 'All 50 states', 'hauntedreal' ); ?></a></li>
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
