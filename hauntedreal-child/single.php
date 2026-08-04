<?php
/**
 * Single editorial article.
 *
 * One H1. Semantic article > header/entry/footer. Ads arrive through hooks,
 * never hard-coded. The reading column is capped at ~72 characters no
 * matter how wide the screen gets.
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
				?>

				<?php hauntedreal_breadcrumbs(); ?>

				<article <?php post_class( 'hr-article' ); ?>>

					<header class="hr-article__header">
						<?php hauntedreal_post_badge(); ?>

						<h1 class="hr-article__title"><?php the_title(); ?></h1>

						<?php if ( has_excerpt() ) : ?>
							<p class="hr-article__standfirst"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

						<div class="hr-byline">
							<div class="hr-byline__avatar">
								<?php echo get_avatar( get_the_author_meta( 'ID' ), 80, '', '', array( 'loading' => 'lazy' ) ); ?>
							</div>
							<div class="hr-byline__text">
								<div class="hr-byline__name">
									<?php echo esc_html_x( 'By', 'byline', 'hauntedreal' ); ?>
									<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
										<?php the_author(); ?>
									</a>
								</div>
								<?php
								hauntedreal_entry_meta( array(
									'author'   => false,
									'location' => true,
									'date'     => true,
									'reading'  => true,
								) );
								?>
							</div>
						</div>
					</header>

					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="hr-featured">
							<?php
							/* The LCP element on an article page. */
							the_post_thumbnail( 'hauntedreal-hero', array(
								'class'         => 'hr-featured__img',
								'loading'       => 'eager',
								'fetchpriority' => 'high',
								'decoding'      => 'async',
								'sizes'         => '(max-width: 1023px) 100vw, 800px',
							) );

							$hauntedreal_caption = get_the_post_thumbnail_caption();

							if ( $hauntedreal_caption ) {
								echo '<figcaption>' . esc_html( $hauntedreal_caption ) . '</figcaption>';
							}
							?>
						</figure>
					<?php endif; ?>

					<div class="hr-entry">
						<?php
						/*
						 * AD SLOTS 02 and 03 are injected into this content by
						 * hauntedreal_inject_in_content_ads() — after the intro
						 * and at roughly 45% — so editors never place ad markup
						 * inside a post.
						 */
						the_content();

						wp_link_pages( array(
							'before' => '<nav class="hr-pagination"><div class="nav-links">',
							'after'  => '</div></nav>',
						) );
						?>
					</div>

					<footer class="hr-article__footer">
						<?php
						$hauntedreal_tags = get_the_tag_list( '<ul class="hr-taglist"><li>', '</li><li>', '</li></ul>' );

						if ( $hauntedreal_tags ) :
							?>
							<div class="hr-article__tags">
								<h2 class="hr-screen-reader-text"><?php esc_html_e( 'Filed under', 'hauntedreal' ); ?></h2>
								<?php echo wp_kses_post( $hauntedreal_tags ); ?>
							</div>
						<?php endif; ?>

						<div class="hr-authorcard">
							<div class="hr-authorcard__avatar">
								<?php echo get_avatar( get_the_author_meta( 'ID' ), 128, '', '', array( 'loading' => 'lazy' ) ); ?>
							</div>
							<div>
								<h2 class="hr-authorcard__name">
									<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
										<?php the_author(); ?>
									</a>
								</h2>
								<?php if ( get_the_author_meta( 'description' ) ) : ?>
									<p class="hr-authorcard__bio"><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</footer>

				</article>

				<?php
				/**
				 * AD SLOT 04 — Article Bottom. After the article, before
				 * related stories.
				 */
				do_action( 'hauntedreal_after_content_ad' );
				?>

				<?php
				$hauntedreal_related = hauntedreal_get_related_query( 3 );

				if ( $hauntedreal_related ) :
					?>
					<section class="hr-section hr-related">
						<?php hauntedreal_section_head( esc_html__( 'Related', 'hauntedreal' ) . ' <span class="hr-mark">' . esc_html__( 'Stories', 'hauntedreal' ) . '</span>' ); ?>

						<div class="hr-grid hr-grid--3">
							<?php
							while ( $hauntedreal_related->have_posts() ) :
								$hauntedreal_related->the_post();
								get_template_part( 'template-parts/card', 'default' );
							endwhile;
							?>
						</div>
					</section>
					<?php
					wp_reset_postdata();
				endif;
				?>

				<?php
				$hauntedreal_prev = get_previous_post();
				$hauntedreal_next = get_next_post();

				if ( $hauntedreal_prev || $hauntedreal_next ) :
					?>
					<nav class="hr-adjacent" aria-label="<?php esc_attr_e( 'Continue reading', 'hauntedreal' ); ?>">
						<?php if ( $hauntedreal_prev ) : ?>
							<a class="hr-adjacent--prev" href="<?php echo esc_url( get_permalink( $hauntedreal_prev ) ); ?>">
								<small><?php esc_html_e( 'Previous story', 'hauntedreal' ); ?></small>
								<?php echo esc_html( get_the_title( $hauntedreal_prev ) ); ?>
							</a>
						<?php endif; ?>

						<?php if ( $hauntedreal_next ) : ?>
							<a class="hr-adjacent--next" href="<?php echo esc_url( get_permalink( $hauntedreal_next ) ); ?>">
								<small><?php esc_html_e( 'Next story', 'hauntedreal' ); ?></small>
								<?php echo esc_html( get_the_title( $hauntedreal_next ) ); ?>
							</a>
						<?php endif; ?>
					</nav>
					<?php
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
