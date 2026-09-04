<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
kc_banner( 'Downloads', 'Downloads' );
?>
<section class="section">
	<div class="container">
		<p>Frequently required forms, circulars and the college prospectus are available here for download.</p>
		<?php if ( have_posts() ) : ?>
		<div style="overflow-x:auto;">
		<table class="dl-table">
			<thead><tr><th>Title</th><th>Category</th><th>Size</th><th></th></tr></thead>
			<tbody>
			<?php while ( have_posts() ) : the_post();
				$id = get_the_ID();
				$cat = get_post_meta( $id, 'kc_category', true );
				$size = get_post_meta( $id, 'kc_file_size', true );
				$file = get_post_meta( $id, 'kc_file_url', true );
				?>
				<tr>
					<td><i class="fa-solid fa-file-pdf" style="color:var(--red);margin-right:8px;"></i><?php the_title(); ?></td>
					<td><span class="tag tag-blue"><?php echo esc_html( $cat ); ?></span></td>
					<td><?php echo esc_html( $size ); ?></td>
					<td><a href="<?php echo esc_url( $file ); ?>" target="_blank" class="btn btn-line btn-sm"><i class="fa-solid fa-download"></i> Download</a></td>
				</tr>
			<?php endwhile; ?>
			</tbody>
		</table>
		</div>
		<?php else : ?>
			<p class="empty-msg">No downloads added yet — add one under Katapali College &rarr; Downloads.</p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
