<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
while ( have_posts() ) : the_post();
$id = get_the_ID();
kc_banner( get_the_title(), 'Faculty' );
$desig = get_post_meta( $id, 'kc_designation', true );
$qual  = get_post_meta( $id, 'kc_qualification', true );
$exp   = get_post_meta( $id, 'kc_experience', true );
$email = get_post_meta( $id, 'kc_email', true );
$phone = get_post_meta( $id, 'kc_phone', true );
$img   = get_the_post_thumbnail_url( $id, 'large' ) ?: KC_URI . '/assets/demo-images/principal.svg';
?>
<section class="section">
	<div class="container">
		<div class="principal-wrap fade-in">
			<div class="principal-photo"><img src="<?php echo esc_url( $img ); ?>" alt="<?php the_title_attribute(); ?>"></div>
			<div class="principal-info">
				<h3><?php the_title(); ?></h3>
				<div class="desig"><?php echo esc_html( $desig ); ?></div>
				<div class="qual"><?php echo esc_html( $qual ); ?> &bull; <?php echo esc_html( $exp ); ?> experience</div>
				<p><?php if ( $email ) echo 'Email: ' . esc_html( $email ) . '<br>'; if ( $phone ) echo 'Phone: ' . esc_html( $phone ); ?></p>
			</div>
		</div>
		<p style="margin-top:24px;"><a href="<?php echo esc_url( get_post_type_archive_link( 'kc_faculty' ) ); ?>" class="btn btn-line btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Faculty</a></p>
	</div>
</section>
<?php endwhile; get_footer(); ?>
