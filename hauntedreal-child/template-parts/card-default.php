<?php
/**
 * Editorial story card.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'hr-card' ); ?>>

	<?php
	hauntedreal_post_media( array(
		'size'  => 'hauntedreal-card',
		'class' => 'hr-card__media',
	) );
	?>

	<span class="hr-card__flag"><?php hauntedreal_post_badge(); ?></span>

	<div class="hr-card__body">
		<h3 class="hr-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( has_excerpt() || get_the_excerpt() ) : ?>
			<p class="hr-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?></p>
		<?php endif; ?>

		<?php
		hauntedreal_entry_meta( array(
			'author'   => false,
			'location' => true,
			'date'     => true,
			'reading'  => false,
		) );
		?>
	</div>

</article>
