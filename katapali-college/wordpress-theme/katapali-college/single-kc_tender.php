<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
while ( have_posts() ) : the_post();
kc_banner( get_the_title(), 'Tenders' );
$id = get_the_ID();
$tid = get_post_meta( $id, 'kc_tender_id', true );
$last = get_post_meta( $id, 'kc_last_date', true );
$open = get_post_meta( $id, 'kc_open_date', true );
$emd = get_post_meta( $id, 'kc_emd', true );
$val = get_post_meta( $id, 'kc_value', true );
$status = get_post_meta( $id, 'kc_status', true );
$file = get_post_meta( $id, 'kc_file_url', true );
?>
<section class="section">
	<div class="container">
		<div class="content-block fade-in">
			<span class="tag <?php echo $status === 'Closed' ? 'tag-closed' : 'tag-open'; ?>"><?php echo esc_html( $status ?: 'Open' ); ?></span>
			<div class="detail-meta">
				<div><strong>Tender ID</strong><?php echo esc_html( $tid ); ?></div>
				<div><strong>EMD</strong><?php echo esc_html( $emd ); ?></div>
				<div><strong>Estimated Value</strong><?php echo esc_html( $val ); ?></div>
				<div><strong>Submission Last Date</strong><?php echo esc_html( $last ); ?></div>
				<div><strong>Opening Date</strong><?php echo esc_html( $open ); ?></div>
			</div>
			<?php the_content(); ?>
			<?php if ( $file ) : ?>
				<p><a href="<?php echo esc_url( $file ); ?>" target="_blank" class="btn btn-accent"><i class="fa-solid fa-download"></i> Download Tender Document</a></p>
			<?php endif; ?>
			<p><a href="<?php echo esc_url( get_post_type_archive_link( 'kc_tender' ) ); ?>" class="btn btn-line btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Tenders</a></p>
		</div>
	</div>
</section>
<?php endwhile; get_footer(); ?>
