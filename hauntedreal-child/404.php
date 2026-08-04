<?php
/**
 * 404.
 *
 * A dead end is a chance to do one useful thing: search, or read something
 * else. No ads on this page — there is nothing here to fund.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="hr-container">

	<section class="hr-404">
		<p class="hr-404__code" aria-hidden="true">404</p>

		<h1 class="hr-404__title"><?php esc_html_e( 'This page has gone cold', 'hauntedreal' ); ?></h1>

		<p class="hr-404__text">
			<?php esc_html_e( 'The story you were looking for has either moved or never existed. Try searching for a place, a state, or the kind of encounter you had in mind.', 'hauntedreal' ); ?>
		</p>

		<?php get_search_form(); ?>

		<div class="hr-404__links">
			<a class="hr-btn hr-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Front page', 'hauntedreal' ); ?>
			</a>
			<a class="hr-btn hr-btn--ghost" href="<?php echo esc_url( home_url( '/haunted-america/' ) ); ?>">
				<?php esc_html_e( 'Haunted America', 'hauntedreal' ); ?>
			</a>
		</div>
	</section>

	<?php
	$hauntedreal_recent = new WP_Query( array(
		'posts_per_page' => 3,
		'no_found_rows'  => true,
		'post_status'    => 'publish',
	) );

	if ( $hauntedreal_recent->have_posts() ) :
		?>
		<section class="hr-section">
			<?php hauntedreal_section_head( esc_html__( 'Read instead', 'hauntedreal' ) ); ?>

			<div class="hr-grid hr-grid--3">
				<?php
				while ( $hauntedreal_recent->have_posts() ) :
					$hauntedreal_recent->the_post();
					get_template_part( 'template-parts/card', 'default' );
				endwhile;
				?>
			</div>
		</section>
		<?php
		wp_reset_postdata();
	endif;
	?>

</div>

<?php
get_footer();
