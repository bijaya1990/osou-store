<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
while ( have_posts() ) : the_post();
kc_banner( get_the_title(), 'Notices' );
$expiry = get_post_meta( get_the_ID(), 'kc_expiry', true );
$file   = get_post_meta( get_the_ID(), 'kc_file_url', true );
$cats   = get_the_terms( get_the_ID(), 'kc_notice_cat' );
?>
<section class="section">
	<div class="container">
		<div class="content-block fade-in">
			<span class="tag tag-blue"><?php echo esc_html( $cats && ! is_wp_error( $cats ) ? $cats[0]->name : 'General' ); ?></span>
			<div class="detail-meta">
				<div><strong>Published</strong><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></div>
				<?php if ( $expiry ) : ?><div><strong>Valid Until</strong><?php echo esc_html( $expiry ); ?></div><?php endif; ?>
			</div>
			<?php the_content(); ?>
			<?php if ( $file ) : ?>
				<p><a href="<?php echo esc_url( $file ); ?>" target="_blank" class="btn btn-accent"><i class="fa-solid fa-download"></i> Download Attachment</a></p>
			<?php endif; ?>
			<p><a href="<?php echo esc_url( get_post_type_archive_link( 'kc_notice' ) ); ?>" class="btn btn-line btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Notices</a></p>
		</div>
	</div>
</section>
<?php endwhile; get_footer(); ?>
