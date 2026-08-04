<?php
/**
 * Search results.
 *
 * Results span editorial reporting, community experiences and pages, and
 * each row says which it is — the type label is doing real work here.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wp_query;
?>

<div class="hr-container">

	<header class="hr-searchhead">
		<?php hauntedreal_breadcrumbs(); ?>

		<h1 class="hr-pagehead__title">
			<?php esc_html_e( 'Search:', 'hauntedreal' ); ?>
			<span class="hr-searchhead__query"><?php echo esc_html( get_search_query() ); ?></span>
		</h1>

		<span class="hr-pagehead__count">
			<?php
			printf(
				/* translators: %s: number of results. */
				esc_html( _n( '%s result', '%s results', $wp_query->found_posts, 'hauntedreal' ) ),
				esc_html( number_format_i18n( $wp_query->found_posts ) )
			);
			?>
		</span>

		<?php get_search_form(); ?>
	</header>

	<div class="hr-shell<?php echo hauntedreal_has_sidebar() ? '' : ' hr-shell--full'; ?>">
		<div class="hr-shell__main">

			<?php if ( have_posts() ) : ?>

				<ol class="hr-results">
					<?php
					while ( have_posts() ) :
						the_post();

						$hauntedreal_type     = get_post_type_object( get_post_type() );
						$hauntedreal_location = hauntedreal_get_location();
						?>
						<li>
							<span class="hr-results__type">
								<?php
								echo esc_html(
									'ghost_story' === get_post_type()
										? __( 'Community Experience', 'hauntedreal' )
										: ( $hauntedreal_type ? $hauntedreal_type->labels->singular_name : '' )
								);
								?>
							</span>

							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

							<div class="hr-meta">
								<?php if ( $hauntedreal_location ) : ?>
									<span class="hr-meta__loc"><?php echo esc_html( $hauntedreal_location ); ?></span>
									<span class="hr-meta__sep" aria-hidden="true">·</span>
								<?php endif; ?>
								<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							</div>

							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32, '…' ) ); ?></p>
						</li>
						<?php
					endwhile;
					?>
				</ol>

				<?php hauntedreal_pagination(); ?>

			<?php else : ?>

				<?php get_template_part( 'template-parts/content', 'none' ); ?>

				<?php
				// Give a dead end somewhere to go.
				$hauntedreal_recent = new WP_Query( array(
					'posts_per_page' => 3,
					'no_found_rows'  => true,
					'post_status'    => 'publish',
				) );

				if ( $hauntedreal_recent->have_posts() ) :
					?>
					<section class="hr-section" style="margin-top:var(--hr-s-7)">
						<?php hauntedreal_section_head( esc_html__( 'Latest from HauntedReal', 'hauntedreal' ) ); ?>

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

			<?php endif; ?>

		</div>

		<?php get_sidebar(); ?>
	</div>

</div>

<?php
get_footer();
