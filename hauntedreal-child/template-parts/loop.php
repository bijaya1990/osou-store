<?php
/**
 * The shared archive loop.
 *
 * Card variant follows post type, so a state archive can mix editorial
 * reporting and community accounts and each still reads as itself.
 *
 * @param array $args {
 *     @type int $columns Grid columns at desktop. 2, 3 or 4.
 * }
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hauntedreal_columns = isset( $args['columns'] ) ? absint( $args['columns'] ) : 3;

if ( ! have_posts() ) {
	get_template_part( 'template-parts/content', 'none' );
	return;
}
?>

<div class="hr-grid hr-grid--<?php echo esc_attr( $hauntedreal_columns ); ?>">
	<?php
	while ( have_posts() ) :
		the_post();

		get_template_part(
			'template-parts/card',
			'ghost_story' === get_post_type() ? 'ghost' : 'default'
		);
	endwhile;
	?>
</div>

<?php hauntedreal_pagination(); ?>
