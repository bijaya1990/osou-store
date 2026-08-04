<?php
/**
 * Community experience card.
 *
 * Reads deliberately differently from an editorial card: cold spectral
 * accent, the witness's own words as a pulled quote, and the community
 * badge always visible. A reader should never mistake one for reporting.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hauntedreal_witness  = get_post_meta( get_the_ID(), '_hauntedreal_witness', true );
$hauntedreal_location = hauntedreal_get_location();
?>
<article <?php post_class( 'hr-card hr-card--ghost' ); ?>>

	<?php
	hauntedreal_post_media( array(
		'size'  => 'hauntedreal-card',
		'class' => 'hr-card__media',
	) );
	?>

	<span class="hr-card__flag">
		<span class="hr-badge hr-badge--community"><?php esc_html_e( 'Community Experience', 'hauntedreal' ); ?></span>
	</span>

	<div class="hr-card__body">
		<h3 class="hr-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( get_the_excerpt() ) : ?>
			<p class="hr-card__quote">&ldquo;<?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?>&rdquo;</p>
		<?php endif; ?>

		<div class="hr-meta">
			<?php if ( $hauntedreal_witness ) : ?>
				<span><?php echo esc_html( $hauntedreal_witness ); ?></span>
			<?php endif; ?>

			<?php if ( $hauntedreal_location ) : ?>
				<span class="hr-meta__sep" aria-hidden="true">·</span>
				<span class="hr-meta__loc"><?php echo esc_html( $hauntedreal_location ); ?></span>
			<?php endif; ?>
		</div>
	</div>

</article>
