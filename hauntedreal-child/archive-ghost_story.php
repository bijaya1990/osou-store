<?php
/**
 * Ghost Story archive — the community feed.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wp_query;

$hauntedreal_submit = hauntedreal_submit_url();
?>

<div class="hr-container">

	<header class="hr-ghosthead">
		<?php hauntedreal_breadcrumbs(); ?>

		<span class="hr-kicker hr-kicker--spectral"><?php esc_html_e( 'Reader Accounts', 'hauntedreal' ); ?></span>

		<h1 class="hr-pagehead__title"><?php esc_html_e( 'Ghost Stories', 'hauntedreal' ); ?></h1>

		<p class="hr-pagehead__desc">
			<?php esc_html_e( 'First-hand paranormal experiences sent to HauntedReal by readers across the United States. These are personal accounts, published as told to us and clearly separated from our reporting.', 'hauntedreal' ); ?>
		</p>

		<span class="hr-pagehead__count">
			<?php
			printf(
				/* translators: %s: number of accounts. */
				esc_html( _n( '%s account', '%s accounts', $wp_query->found_posts, 'hauntedreal' ) ),
				esc_html( number_format_i18n( $wp_query->found_posts ) )
			);
			?>
		</span>

		<?php if ( $hauntedreal_submit ) : ?>
			<a class="hr-btn hr-btn--primary" href="<?php echo esc_url( $hauntedreal_submit ); ?>">
				<?php esc_html_e( 'Share Your Experience', 'hauntedreal' ); ?>
			</a>
		<?php endif; ?>
	</header>

	<?php
	$hauntedreal_types = get_terms( array(
		'taxonomy'   => 'ghost_theme',
		'hide_empty' => true,
		'number'     => 12,
		'orderby'    => 'count',
		'order'      => 'DESC',
	) );

	if ( $hauntedreal_types && ! is_wp_error( $hauntedreal_types ) ) :
		?>
		<nav aria-label="<?php esc_attr_e( 'Filter by experience type', 'hauntedreal' ); ?>">
			<ul class="hr-chips">
				<li><a href="<?php echo esc_url( get_post_type_archive_link( 'ghost_story' ) ); ?>" aria-current="true"><?php esc_html_e( 'All', 'hauntedreal' ); ?></a></li>
				<?php foreach ( $hauntedreal_types as $hauntedreal_type ) : ?>
					<li>
						<a href="<?php echo esc_url( get_term_link( $hauntedreal_type ) ); ?>">
							<?php echo esc_html( $hauntedreal_type->name ); ?>
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
