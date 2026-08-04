<?php
/**
 * Author profile.
 *
 * A masthead credit page: who they are, what they cover, how long they have
 * been doing it. Credibility is the product on a paranormal site.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hauntedreal_author = get_queried_object();
$hauntedreal_id     = $hauntedreal_author instanceof WP_User ? $hauntedreal_author->ID : 0;
$hauntedreal_count  = $hauntedreal_id ? (int) count_user_posts( $hauntedreal_id, array( 'post', 'ghost_story' ) ) : 0;
$hauntedreal_role   = get_the_author_meta( 'hauntedreal_role', $hauntedreal_id );
$hauntedreal_since  = $hauntedreal_id ? get_the_author_meta( 'user_registered', $hauntedreal_id ) : '';
?>

<div class="hr-container">

	<header class="hr-authorhead">
		<div class="hr-authorhead__avatar">
			<?php echo get_avatar( $hauntedreal_id, 192, '', '', array( 'loading' => 'eager' ) ); ?>
		</div>

		<span class="hr-authorhead__role">
			<?php echo esc_html( $hauntedreal_role ? $hauntedreal_role : __( 'HauntedReal Contributor', 'hauntedreal' ) ); ?>
		</span>

		<h1 class="hr-authorhead__name"><?php echo esc_html( get_the_author_meta( 'display_name', $hauntedreal_id ) ); ?></h1>

		<?php if ( get_the_author_meta( 'description', $hauntedreal_id ) ) : ?>
			<p class="hr-authorhead__bio"><?php echo esc_html( get_the_author_meta( 'description', $hauntedreal_id ) ); ?></p>
		<?php endif; ?>

		<ul class="hr-authorhead__stats">
			<li>
				<b><?php echo esc_html( number_format_i18n( $hauntedreal_count ) ); ?></b>
				<span><?php esc_html_e( 'Stories', 'hauntedreal' ); ?></span>
			</li>
			<?php if ( $hauntedreal_since ) : ?>
				<li>
					<b><?php echo esc_html( wp_date( 'Y', strtotime( $hauntedreal_since ) ) ); ?></b>
					<span><?php esc_html_e( 'Writing since', 'hauntedreal' ); ?></span>
				</li>
			<?php endif; ?>
			<?php
			$hauntedreal_beat = get_the_author_meta( 'hauntedreal_beat', $hauntedreal_id );

			if ( $hauntedreal_beat ) :
				?>
				<li>
					<b><?php echo esc_html( $hauntedreal_beat ); ?></b>
					<span><?php esc_html_e( 'Beat', 'hauntedreal' ); ?></span>
				</li>
			<?php endif; ?>
		</ul>

		<?php
		$hauntedreal_links = array_filter( array(
			'url'   => get_the_author_meta( 'url', $hauntedreal_id ),
			'email' => get_the_author_meta( 'hauntedreal_public_email', $hauntedreal_id ),
		) );

		if ( $hauntedreal_links ) :
			?>
			<div class="hr-authorhead__social">
				<?php if ( ! empty( $hauntedreal_links['url'] ) ) : ?>
					<a href="<?php echo esc_url( $hauntedreal_links['url'] ); ?>" rel="nofollow noopener">
						<?php esc_html_e( 'Website', 'hauntedreal' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $hauntedreal_links['email'] ) ) : ?>
					<a href="mailto:<?php echo esc_attr( $hauntedreal_links['email'] ); ?>">
						<?php esc_html_e( 'Contact', 'hauntedreal' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</header>

	<div class="hr-shell<?php echo hauntedreal_has_sidebar() ? '' : ' hr-shell--full'; ?>">
		<div class="hr-shell__main">
			<?php
			hauntedreal_section_head(
				sprintf(
					/* translators: %s: author name. */
					esc_html__( 'Stories by %s', 'hauntedreal' ),
					esc_html( get_the_author_meta( 'display_name', $hauntedreal_id ) )
				)
			);

			get_template_part( 'template-parts/loop', null, array( 'columns' => 3 ) );
			?>
		</div>

		<?php get_sidebar(); ?>
	</div>

</div>

<?php
get_footer();
