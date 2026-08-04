<?php
/**
 * Compact horizontal card — related stories, secondary hero column, feeds.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hauntedreal_is_ghost = 'ghost_story' === get_post_type();
?>
<article <?php post_class( 'hr-card hr-card--row' . ( $hauntedreal_is_ghost ? ' hr-card--ghost' : '' ) ); ?>>

	<?php
	hauntedreal_post_media( array(
		'size'  => 'hauntedreal-thumb',
		'class' => 'hr-card__media',
	) );
	?>

	<div class="hr-card__body">
		<?php if ( $hauntedreal_is_ghost ) : ?>
			<span class="hr-kicker hr-kicker--spectral"><?php esc_html_e( 'Community', 'hauntedreal' ); ?></span>
		<?php endif; ?>

		<h3 class="hr-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php
		hauntedreal_entry_meta( array(
			'author'   => false,
			'location' => true,
			'date'     => true,
		) );
		?>
	</div>

</article>
