<?php
/**
 * Single community experience.
 *
 * Everything on this page works to keep one thing unambiguous: this is a
 * reader's account, not HauntedReal reporting. Cold spectral accent, the
 * witness credited instead of a staff byline, a witness detail strip, and
 * a standing disclaimer beneath the story.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="hr-container">
	<div class="hr-shell<?php echo hauntedreal_has_sidebar() ? '' : ' hr-shell--full'; ?>">

		<div class="hr-shell__main">

			<?php
			while ( have_posts() ) :
				the_post();

				$hauntedreal_witness  = get_post_meta( get_the_ID(), '_hauntedreal_witness', true );
				$hauntedreal_when     = get_post_meta( get_the_ID(), '_hauntedreal_experience_date', true );
				$hauntedreal_place    = get_post_meta( get_the_ID(), '_hauntedreal_place', true );
				$hauntedreal_location = hauntedreal_get_location();
				$hauntedreal_type     = hauntedreal_get_primary_term( get_the_ID(), 'ghost_theme' );
				?>

				<?php hauntedreal_breadcrumbs(); ?>

				<article <?php post_class( 'hr-article hr-ghost-single' ); ?>>

					<header class="hr-article__header">
						<span class="hr-badge hr-badge--community"><?php esc_html_e( 'Community Experience', 'hauntedreal' ); ?></span>
						<?php echo wp_kses_post( hauntedreal_get_verification_badge() ); ?>

						<h1 class="hr-article__title"><?php the_title(); ?></h1>

						<?php if ( has_excerpt() ) : ?>
							<p class="hr-article__standfirst"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>
					</header>

					<dl class="hr-witness">
						<div>
							<dt><?php esc_html_e( 'Witness', 'hauntedreal' ); ?></dt>
							<dd><?php echo esc_html( $hauntedreal_witness ? $hauntedreal_witness : __( 'Anonymous', 'hauntedreal' ) ); ?></dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'Location', 'hauntedreal' ); ?></dt>
							<dd class="hr-witness__loc">
								<?php echo esc_html( $hauntedreal_location ? $hauntedreal_location : __( 'Not disclosed', 'hauntedreal' ) ); ?>
							</dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'When', 'hauntedreal' ); ?></dt>
							<dd><?php echo esc_html( $hauntedreal_when ? $hauntedreal_when : __( 'Not given', 'hauntedreal' ) ); ?></dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'Experience', 'hauntedreal' ); ?></dt>
							<dd><?php echo esc_html( $hauntedreal_type ? $hauntedreal_type->name : __( 'Unclassified', 'hauntedreal' ) ); ?></dd>
						</div>
					</dl>

					<?php if ( $hauntedreal_place ) : ?>
						<div class="hr-factbox">
							<h3><?php esc_html_e( 'Reported site', 'hauntedreal' ); ?></h3>
							<p style="margin:0"><?php echo esc_html( $hauntedreal_place ); ?></p>
						</div>
					<?php endif; ?>

					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="hr-featured">
							<?php
							the_post_thumbnail( 'hauntedreal-hero', array(
								'class'         => 'hr-featured__img',
								'loading'       => 'eager',
								'fetchpriority' => 'high',
								'decoding'      => 'async',
								'sizes'         => '(max-width: 1023px) 100vw, 800px',
							) );
							?>
						</figure>
					<?php endif; ?>

					<div class="hr-entry">
						<?php the_content(); ?>
					</div>

					<div class="hr-disclaimer">
						<strong><?php esc_html_e( 'About this account:', 'hauntedreal' ); ?></strong>
						<?php
						echo esc_html( get_theme_mod(
							'hauntedreal_ghost_disclaimer',
							__( 'This account was submitted by a member of the HauntedReal community and reflects their personal experience. HauntedReal has not independently verified the events described.', 'hauntedreal' )
						) );
						?>
					</div>

					<footer class="hr-article__footer">
						<?php
						$hauntedreal_terms = get_the_terms( get_the_ID(), 'ghost_theme' );

						if ( $hauntedreal_terms && ! is_wp_error( $hauntedreal_terms ) ) :
							?>
							<div class="hr-article__tags">
								<h2 class="hr-screen-reader-text"><?php esc_html_e( 'Experience types', 'hauntedreal' ); ?></h2>
								<ul class="hr-taglist">
									<?php foreach ( $hauntedreal_terms as $hauntedreal_term ) : ?>
										<li>
											<a href="<?php echo esc_url( get_term_link( $hauntedreal_term ) ); ?>">
												<?php echo esc_html( $hauntedreal_term->name ); ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>

						<?php
						$hauntedreal_submit = hauntedreal_submit_url();

						if ( $hauntedreal_submit ) :
							?>
							<div class="hr-panel" style="margin-top:var(--hr-s-6)">
								<h3><?php esc_html_e( 'Has something like this happened to you?', 'hauntedreal' ); ?></h3>
								<p><?php esc_html_e( 'Add your account to the HauntedReal archive. Every story is read by an editor before publication.', 'hauntedreal' ); ?></p>
								<a class="hr-btn hr-btn--primary" href="<?php echo esc_url( $hauntedreal_submit ); ?>">
									<?php esc_html_e( 'Share Your Experience', 'hauntedreal' ); ?>
								</a>
							</div>
						<?php endif; ?>
					</footer>

				</article>

				<?php do_action( 'hauntedreal_after_content_ad' ); ?>

				<?php
				$hauntedreal_related = hauntedreal_get_related_query( 3 );

				if ( $hauntedreal_related ) :
					?>
					<section class="hr-section hr-related">
						<?php
						hauntedreal_section_head(
							$hauntedreal_location
								? sprintf(
									/* translators: %s: city, state. */
									esc_html__( 'More from %s', 'hauntedreal' ),
									'<span class="hr-mark">' . esc_html( $hauntedreal_location ) . '</span>'
								)
								: esc_html__( 'More Community Experiences', 'hauntedreal' )
						);
						?>

						<div class="hr-grid hr-grid--3">
							<?php
							while ( $hauntedreal_related->have_posts() ) :
								$hauntedreal_related->the_post();
								get_template_part( 'template-parts/card', 'ghost' );
							endwhile;
							?>
						</div>
					</section>
					<?php
					wp_reset_postdata();
				endif;
				?>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>

			<?php endwhile; ?>

		</div>

		<?php get_sidebar(); ?>

	</div>
</div>

<?php
get_footer();
