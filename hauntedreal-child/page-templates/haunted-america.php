<?php
/**
 * Template Name: Haunted America (US States Index)
 * Template Post Type: page
 *
 * The section index for Haunted America: every state we have stories from,
 * plus the states still waiting for their first account.
 *
 * Assign this template to a page with the slug `haunted-america` so it sits
 * directly above the /haunted-america/{state}/ term archives.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hauntedreal_all = get_terms( array(
	'taxonomy'   => 'us_state',
	'hide_empty' => false,
	'orderby'    => 'name',
) );

$hauntedreal_covered = array();
$hauntedreal_empty   = array();

if ( $hauntedreal_all && ! is_wp_error( $hauntedreal_all ) ) {
	foreach ( $hauntedreal_all as $hauntedreal_term ) {
		if ( $hauntedreal_term->count > 0 ) {
			$hauntedreal_covered[] = $hauntedreal_term;
		} else {
			$hauntedreal_empty[] = $hauntedreal_term;
		}
	}
}
?>

<div class="hr-container">

	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<header class="hr-statehead">
			<?php hauntedreal_breadcrumbs(); ?>

			<span class="hr-kicker hr-kicker--spectral"><?php esc_html_e( 'The Fifty States', 'hauntedreal' ); ?></span>

			<h1 class="hr-pagehead__title"><?php the_title(); ?></h1>

			<p class="hr-pagehead__desc">
				<?php
				if ( has_excerpt() ) {
					echo esc_html( get_the_excerpt() );
				} else {
					esc_html_e( 'Every state has its own haunted geography — battlefields, asylums, hotels, and the stretches of road people will not drive after dark. Choose a state to read what has been reported there.', 'hauntedreal' );
				}
				?>
			</p>
		</header>

		<?php if ( get_the_content() ) : ?>
			<div class="hr-entry" style="margin-bottom:var(--hr-s-7)">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

	<?php endwhile; ?>

	<?php if ( $hauntedreal_covered ) : ?>
		<section class="hr-section">
			<?php hauntedreal_section_head( esc_html__( 'States with', 'hauntedreal' ) . ' <span class="hr-mark">' . esc_html__( 'reported activity', 'hauntedreal' ) . '</span>' ); ?>

			<ul class="hr-states">
				<?php foreach ( $hauntedreal_covered as $hauntedreal_term ) : ?>
					<li>
						<a href="<?php echo esc_url( get_term_link( $hauntedreal_term ) ); ?>">
							<span><?php echo esc_html( $hauntedreal_term->name ); ?></span>
							<span class="hr-states__count"><?php echo esc_html( number_format_i18n( $hauntedreal_term->count ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php
	/**
	 * AD SLOT 06 — reused here as a section break on a long index page.
	 */
	do_action( 'hauntedreal_home_feed_ad' );
	?>

	<?php if ( $hauntedreal_empty ) : ?>
		<section class="hr-section">
			<?php hauntedreal_section_head( esc_html__( 'Still', 'hauntedreal' ) . ' <span class="hr-mark">' . esc_html__( 'unreported', 'hauntedreal' ) . '</span>' ); ?>

			<p class="hr-pagehead__desc" style="margin-bottom:var(--hr-s-5)">
				<?php esc_html_e( 'We have no published accounts from these states yet. If something happened to you in one of them, we would like to hear about it.', 'hauntedreal' ); ?>
			</p>

			<ul class="hr-states">
				<?php foreach ( $hauntedreal_empty as $hauntedreal_term ) : ?>
					<li>
						<a href="<?php echo esc_url( get_term_link( $hauntedreal_term ) ); ?>">
							<span><?php echo esc_html( $hauntedreal_term->name ); ?></span>
							<span class="hr-states__count"><?php echo esc_html__( '—', 'hauntedreal' ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php
			$hauntedreal_submit = hauntedreal_submit_url();

			if ( $hauntedreal_submit ) :
				?>
				<div class="hr-panel" style="margin-top:var(--hr-s-6)">
					<h3><?php esc_html_e( 'Put your state on the map', 'hauntedreal' ); ?></h3>
					<p><?php esc_html_e( 'HauntedReal is built partly from reader accounts. Tell us what happened where you live.', 'hauntedreal' ); ?></p>
					<a class="hr-btn hr-btn--primary" href="<?php echo esc_url( $hauntedreal_submit ); ?>">
						<?php esc_html_e( 'Share Your Experience', 'hauntedreal' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

</div>

<?php
get_footer();
