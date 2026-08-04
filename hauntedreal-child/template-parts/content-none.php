<?php
/**
 * Empty state.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="hr-panel">
	<h2><?php esc_html_e( 'Nothing here — yet', 'hauntedreal' ); ?></h2>

	<?php if ( is_search() ) : ?>
		<p>
			<?php esc_html_e( 'No stories matched that search. Try a place name, a state, or the kind of encounter you are looking for.', 'hauntedreal' ); ?>
		</p>
		<?php get_search_form(); ?>
	<?php else : ?>
		<p>
			<?php esc_html_e( 'There are no published stories in this section at the moment. New investigations and community accounts are added every week.', 'hauntedreal' ); ?>
		</p>
		<p>
			<a class="hr-btn hr-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to the front page', 'hauntedreal' ); ?>
			</a>
		</p>
	<?php endif; ?>
</section>
