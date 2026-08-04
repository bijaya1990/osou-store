<?php
/**
 * The article sidebar.
 *
 * Desktop only. CSS hides the column below 1024px and hauntedreal_has_sidebar()
 * skips the markup where it makes no sense, so the sidebar advertisement is
 * never forced onto a phone.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! hauntedreal_has_sidebar() ) {
	return;
}
?>
<aside class="hr-sidebar" aria-label="<?php esc_attr_e( 'More from HauntedReal', 'hauntedreal' ); ?>">

	<?php
	/**
	 * AD SLOT 05 — Desktop Sidebar. 300×250, 1024px and up only.
	 */
	do_action( 'hauntedreal_sidebar_ad' );
	?>

	<?php if ( is_active_sidebar( 'hauntedreal-sidebar' ) ) : ?>
		<?php dynamic_sidebar( 'hauntedreal-sidebar' ); ?>
	<?php else : ?>

		<?php
		// A useful default rather than an empty column on a fresh install.
		$hauntedreal_popular = new WP_Query( array(
			'posts_per_page'      => 5,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post__not_in'        => is_singular() ? array( get_the_ID() ) : array(),
			'orderby'             => 'comment_count',
		) );

		if ( $hauntedreal_popular->have_posts() ) :
			?>
			<section class="hr-widget">
				<h2 class="hr-widget__title"><?php esc_html_e( 'Most Read', 'hauntedreal' ); ?></h2>
				<ol class="hr-ranked">
					<?php while ( $hauntedreal_popular->have_posts() ) : $hauntedreal_popular->the_post(); ?>
						<li>
							<div>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<div class="hr-meta">
									<?php
									$hauntedreal_location = hauntedreal_get_location();

									if ( $hauntedreal_location ) {
										echo '<span class="hr-meta__loc">' . esc_html( $hauntedreal_location ) . '</span>';
									} else {
										echo '<time datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>';
									}
									?>
								</div>
							</div>
						</li>
					<?php endwhile; ?>
				</ol>
			</section>
			<?php
		endif;

		wp_reset_postdata();
		?>

		<section class="hr-widget">
			<div class="hr-panel">
				<h3><?php esc_html_e( 'Seen something you cannot explain?', 'hauntedreal' ); ?></h3>
				<p><?php esc_html_e( 'HauntedReal publishes first-hand accounts from readers across all fifty states. Tell us what happened.', 'hauntedreal' ); ?></p>
				<?php
				$hauntedreal_submit = hauntedreal_submit_url();

				if ( $hauntedreal_submit ) :
					?>
					<a class="hr-btn hr-btn--primary hr-btn--block" href="<?php echo esc_url( $hauntedreal_submit ); ?>">
						<?php esc_html_e( 'Share Your Experience', 'hauntedreal' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</section>

	<?php endif; ?>

</aside>
