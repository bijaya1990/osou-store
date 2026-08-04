<?php
/**
 * The footer.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main><!-- #hr-content -->

<?php do_action( 'generate_before_footer' ); ?>

<footer class="hr-footer">
	<div class="hr-container">

		<div class="hr-footer__cols">

			<div class="hr-footer__brand">
				<a class="hr-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<b><?php echo esc_html( get_bloginfo( 'name' ) ); ?></b>
				</a>
				<p class="hr-footer__tagline">
					<?php
					echo esc_html( get_theme_mod(
						'hauntedreal_footer_tagline',
						__( 'HauntedReal investigates America\'s most persistent hauntings — and publishes the experiences readers cannot explain.', 'hauntedreal' )
					) );
					?>
				</p>
			</div>

			<div class="hr-footer__col">
				<h2><?php esc_html_e( 'Sections', 'hauntedreal' ); ?></h2>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
					) );
				} else {
					wp_list_categories( array(
						'title_li' => '',
						'number'   => 6,
						'orderby'  => 'count',
						'order'    => 'DESC',
					) );
				}
				?>
			</div>

			<div class="hr-footer__col">
				<h2><?php esc_html_e( 'Haunted America', 'hauntedreal' ); ?></h2>
				<ul>
					<?php
					$states = get_terms( array(
						'taxonomy'   => 'us_state',
						'orderby'    => 'count',
						'order'      => 'DESC',
						'number'     => 6,
						'hide_empty' => true,
					) );

					if ( $states && ! is_wp_error( $states ) ) {
						foreach ( $states as $state ) {
							printf(
								'<li><a href="%s">%s</a></li>',
								esc_url( get_term_link( $state ) ),
								esc_html( $state->name )
							);
						}
					} else {
						printf(
							'<li><a href="%s">%s</a></li>',
							esc_url( home_url( '/haunted-america/' ) ),
							esc_html__( 'Browse all 50 states', 'hauntedreal' )
						);
					}
					?>
				</ul>
			</div>

			<div class="hr-footer__col">
				<h2><?php esc_html_e( 'The Community', 'hauntedreal' ); ?></h2>
				<ul>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'ghost_story' ) ); ?>"><?php esc_html_e( 'Ghost Stories', 'hauntedreal' ); ?></a></li>
					<?php
					$submit = hauntedreal_submit_url();

					if ( $submit ) {
						printf(
							'<li><a href="%s">%s</a></li>',
							esc_url( $submit ),
							esc_html__( 'Share Your Experience', 'hauntedreal' )
						);
					}
					?>
				</ul>
				<?php
				if ( is_active_sidebar( 'hauntedreal-footer' ) ) {
					dynamic_sidebar( 'hauntedreal-footer' );
				}
				?>
			</div>

		</div>

		<div class="hr-footer__bottom">
			<p style="margin:0">
				<?php
				$copyright = get_theme_mod( 'hauntedreal_copyright', '' );

				if ( $copyright ) {
					echo wp_kses_post( $copyright );
				} else {
					printf(
						/* translators: 1: year, 2: site name. */
						esc_html__( '© %1$s %2$s. All rights reserved.', 'hauntedreal' ),
						esc_html( wp_date( 'Y' ) ),
						esc_html( get_bloginfo( 'name' ) )
					);
				}
				?>
			</p>

			<?php
			if ( has_nav_menu( 'legal' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'legal',
					'container'      => false,
					'menu_class'     => 'hr-footer__legal',
					'depth'          => 1,
				) );
			}
			?>
		</div>

	</div>
</footer>

<?php do_action( 'generate_after_footer' ); ?>
<?php wp_footer(); ?>
</body>
</html>
