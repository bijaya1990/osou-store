<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
while ( have_posts() ) : the_post();
kc_banner( get_the_title(), 'Recruitment' );
$id = get_the_ID();
$dept = get_post_meta( $id, 'kc_department', true );
$type = get_post_meta( $id, 'kc_job_type', true );
$vac  = get_post_meta( $id, 'kc_vacancies', true );
$sal  = get_post_meta( $id, 'kc_salary', true );
$qual = get_post_meta( $id, 'kc_qualification', true );
$last = get_post_meta( $id, 'kc_last_date', true );
$status = get_post_meta( $id, 'kc_status', true );
$file = get_post_meta( $id, 'kc_file_url', true );
?>
<section class="section">
	<div class="container">
		<div class="content-block fade-in">
			<span class="tag <?php echo $status === 'Closed' ? 'tag-closed' : 'tag-open'; ?>"><?php echo esc_html( $status ?: 'Open' ); ?></span>
			<div class="detail-meta">
				<div><strong>Department</strong><?php echo esc_html( $dept ); ?></div>
				<div><strong>Engagement Type</strong><?php echo esc_html( $type ); ?></div>
				<div><strong>Vacancies</strong><?php echo esc_html( $vac ); ?></div>
				<div><strong>Salary</strong><?php echo esc_html( $sal ); ?></div>
				<div><strong>Last Date</strong><?php echo esc_html( $last ); ?></div>
			</div>
			<h3>Eligibility / Qualification</h3>
			<p><?php echo esc_html( $qual ); ?></p>
			<?php the_content(); ?>
			<?php if ( $file ) : ?>
				<p><a href="<?php echo esc_url( $file ); ?>" target="_blank" class="btn btn-accent"><i class="fa-solid fa-download"></i> Download Notification</a></p>
			<?php endif; ?>
			<p><a href="<?php echo esc_url( get_post_type_archive_link( 'kc_recruitment' ) ); ?>" class="btn btn-line btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Recruitment</a></p>
		</div>
	</div>
</section>
<?php endwhile; get_footer(); ?>
