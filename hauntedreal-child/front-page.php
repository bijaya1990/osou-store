<?php
/**
 * The homepage.
 *
 * A publication front page, not a blog roll: one lead story, a secondary
 * column, the latest investigations, a Haunted America strip, and the
 * community feed — each visually distinct, all sharing one grid.
 *
 * Every query below sets no_found_rows and ignore_sticky_posts; none of
 * them need pagination, so none of them should pay for a COUNT.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Track what has already appeared so no story shows up twice.
$hauntedreal_seen = array();
?>

<div class="hr-container">

	<?php
	/* ------------------------------------------------------------------
	 * Lead story + secondary column
	 * ------------------------------------------------------------------ */
	$hauntedreal_lead = new WP_Query( array(
		'posts_per_page'      => 4,
		'ignore_sticky_posts' => false,
		'no_found_rows'       => true,
		'post_status'         => 'publish',
	) );

	if ( $hauntedreal_lead->have_posts() ) :
		?>
		<section class="hr-hero" aria-label="<?php esc_attr_e( 'Lead story', 'hauntedreal' ); ?>">
			<?php
			$hauntedreal_index = 0;

			while ( $hauntedreal_lead->have_posts() ) :
				$hauntedreal_lead->the_post();
				$hauntedreal_seen[] = get_the_ID();

				if ( 0 === $hauntedreal_index ) :
					?>
					<article class="hr-hero__lead">
						<div class="hr-hero__media">
							<?php
							if ( has_post_thumbnail() ) {
								/*
								 * The LCP element on the homepage. Eager, high
								 * priority, and sized so mobile never downloads
								 * the 1600px source.
								 */
								the_post_thumbnail( 'hauntedreal-hero', array(
									'class'         => 'hr-media__img',
									'loading'       => 'eager',
									'fetchpriority' => 'high',
									'decoding'      => 'async',
									'sizes'         => '(max-width: 1023px) 100vw, 66vw',
								) );
							} else {
								echo '<span class="hr-media__empty" aria-hidden="true">' . esc_html__( 'HauntedReal', 'hauntedreal' ) . '</span>';
							}
							?>
						</div>

						<div class="hr-hero__body">
							<span class="hr-kicker">
								<?php
								$hauntedreal_cat = hauntedreal_get_primary_term( get_the_ID(), 'category' );
								echo esc_html( $hauntedreal_cat ? $hauntedreal_cat->name : __( 'Investigation', 'hauntedreal' ) );
								?>
							</span>

							<h2 class="hr-hero__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>

							<p class="hr-hero__excerpt">
								<?php echo esc_html( wp_trim_words( get_the_excerpt(), 28, '…' ) ); ?>
							</p>

							<?php
							hauntedreal_entry_meta( array(
								'author'   => true,
								'location' => true,
								'date'     => true,
								'reading'  => true,
							) );
							?>
						</div>
					</article>

					<div class="hr-hero__side">
					<?php
				else :
					get_template_part( 'template-parts/card', 'row' );
				endif;

				++$hauntedreal_index;
			endwhile;
			?>
					</div>
		</section>
		<?php
	endif;

	wp_reset_postdata();
	?>

	<?php
	/* ------------------------------------------------------------------
	 * Latest investigations
	 * ------------------------------------------------------------------ */
	$hauntedreal_latest = new WP_Query( array(
		'posts_per_page'      => 6,
		'post__not_in'        => $hauntedreal_seen,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'post_status'         => 'publish',
	) );

	if ( $hauntedreal_latest->have_posts() ) :
		?>
		<section class="hr-section">
			<?php
			hauntedreal_section_head(
				esc_html__( 'Latest', 'hauntedreal' ) . ' <span class="hr-mark">' . esc_html__( 'Investigations', 'hauntedreal' ) . '</span>',
				get_permalink( get_option( 'page_for_posts' ) ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' ),
				__( 'All reporting', 'hauntedreal' )
			);
			?>

			<div class="hr-grid hr-grid--3">
				<?php
				while ( $hauntedreal_latest->have_posts() ) :
					$hauntedreal_latest->the_post();
					$hauntedreal_seen[] = get_the_ID();
					get_template_part( 'template-parts/card', 'default' );
				endwhile;
				?>
			</div>
		</section>
		<?php
	endif;

	wp_reset_postdata();
	?>

	<?php
	/**
	 * AD SLOT 06 — Homepage Feed.
	 * Sits between article groups, labelled, with its height reserved.
	 */
	do_action( 'hauntedreal_home_feed_ad' );
	?>

	<?php
	/* ------------------------------------------------------------------
	 * Haunted America
	 * ------------------------------------------------------------------ */
	$hauntedreal_states = get_terms( array(
		'taxonomy'   => 'us_state',
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 12,
		'hide_empty' => true,
	) );

	if ( $hauntedreal_states && ! is_wp_error( $hauntedreal_states ) ) :
		?>
		<section class="hr-section">
			<?php
			hauntedreal_section_head(
				esc_html__( 'Haunted', 'hauntedreal' ) . ' <span class="hr-mark">' . esc_html__( 'America', 'hauntedreal' ) . '</span>',
				home_url( '/haunted-america/' ),
				__( 'All 50 states', 'hauntedreal' )
			);
			?>

			<ul class="hr-states">
				<?php foreach ( $hauntedreal_states as $hauntedreal_state ) : ?>
					<li>
						<a href="<?php echo esc_url( get_term_link( $hauntedreal_state ) ); ?>">
							<span><?php echo esc_html( $hauntedreal_state->name ); ?></span>
							<span class="hr-states__count"><?php echo esc_html( number_format_i18n( $hauntedreal_state->count ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
	endif;
	?>

	<?php
	/* ------------------------------------------------------------------
	 * Community experiences
	 * ------------------------------------------------------------------ */
	$hauntedreal_ghosts = new WP_Query( array(
		'post_type'      => 'ghost_story',
		'posts_per_page' => 3,
		'no_found_rows'  => true,
		'post_status'    => 'publish',
	) );

	if ( $hauntedreal_ghosts->have_posts() ) :
		?>
		<section class="hr-section">
			<?php
			hauntedreal_section_head(
				esc_html__( 'Community', 'hauntedreal' ) . ' <span class="hr-mark">' . esc_html__( 'Experiences', 'hauntedreal' ) . '</span>',
				get_post_type_archive_link( 'ghost_story' ),
				__( 'Read all accounts', 'hauntedreal' )
			);
			?>

			<div class="hr-grid hr-grid--3">
				<?php
				while ( $hauntedreal_ghosts->have_posts() ) :
					$hauntedreal_ghosts->the_post();
					get_template_part( 'template-parts/card', 'ghost' );
				endwhile;
				?>
			</div>

			<?php
			$hauntedreal_submit = hauntedreal_submit_url();

			if ( $hauntedreal_submit ) :
				?>
				<div class="hr-panel" style="margin-top:var(--hr-s-5)">
					<h3><?php esc_html_e( 'Something happened to you that you cannot explain?', 'hauntedreal' ); ?></h3>
					<p><?php esc_html_e( 'Readers across all fifty states send us their accounts. Every submission is read by an editor before it appears.', 'hauntedreal' ); ?></p>
					<a class="hr-btn hr-btn--primary" href="<?php echo esc_url( $hauntedreal_submit ); ?>">
						<?php esc_html_e( 'Share Your Experience', 'hauntedreal' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</section>
		<?php
	endif;

	wp_reset_postdata();
	?>

	<?php
	/* ------------------------------------------------------------------
	 * More reporting
	 * ------------------------------------------------------------------ */
	$hauntedreal_more = new WP_Query( array(
		'posts_per_page'      => 4,
		'post__not_in'        => $hauntedreal_seen,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'post_status'         => 'publish',
	) );

	if ( $hauntedreal_more->have_posts() ) :
		?>
		<section class="hr-section">
			<?php hauntedreal_section_head( esc_html__( 'More from HauntedReal', 'hauntedreal' ) ); ?>

			<div class="hr-grid hr-grid--4">
				<?php
				while ( $hauntedreal_more->have_posts() ) :
					$hauntedreal_more->the_post();
					get_template_part( 'template-parts/card', 'default' );
				endwhile;
				?>
			</div>
		</section>
		<?php
	endif;

	wp_reset_postdata();
	?>

</div>

<?php
get_footer();
