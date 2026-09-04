<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
kc_banner( 'Tenders', 'Tenders' );
?>
<section class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid-3">
				<?php while ( have_posts() ) : the_post(); kc_tender_card( get_the_ID() ); endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p class="empty-msg">No tenders currently — add one under Katapali College &rarr; Tenders.</p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
