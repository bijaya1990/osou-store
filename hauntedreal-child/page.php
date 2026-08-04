<?php
/**
 * Standard WordPress page.
 *
 * About, Contact, Editorial Standards, Privacy — the pages that make a
 * publication look like one. Narrow measure, no sidebar, no ads.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="hr-container hr-container--narrow">

	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<article <?php post_class( 'hr-page' ); ?>>

			<header class="hr-page__header">
				<?php hauntedreal_breadcrumbs(); ?>

				<h1 class="hr-page__title"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="hr-article__standfirst" style="margin-top:var(--hr-s-4);margin-bottom:0">
						<?php echo esc_html( get_the_excerpt() ); ?>
					</p>
				<?php endif; ?>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="hr-featured">
					<?php
					the_post_thumbnail( 'hauntedreal-hero', array(
						'class'         => 'hr-featured__img',
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'decoding'      => 'async',
						'sizes'         => '(max-width: 1023px) 100vw, 736px',
					) );
					?>
				</figure>
			<?php endif; ?>

			<div class="hr-entry">
				<?php
				the_content();

				wp_link_pages( array(
					'before' => '<nav class="hr-pagination"><div class="nav-links">',
					'after'  => '</div></nav>',
				) );
				?>
			</div>

			<?php
			if ( get_the_modified_date() ) :
				?>
				<footer class="hr-article__footer">
					<div class="hr-meta">
						<?php
						printf(
							/* translators: %s: date. */
							esc_html__( 'Last updated %s', 'hauntedreal' ),
							'<time datetime="' . esc_attr( get_the_modified_date( DATE_W3C ) ) . '">' . esc_html( get_the_modified_date() ) . '</time>'
						);
						?>
					</div>
				</footer>
			<?php endif; ?>

		</article>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>

	<?php endwhile; ?>

</div>

<?php
get_footer();
