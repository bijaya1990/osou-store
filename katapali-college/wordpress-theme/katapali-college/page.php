<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
kc_banner( get_the_title() );
?>
<section class="section">
	<div class="container">
		<?php while ( have_posts() ) : the_post(); ?>
			<div class="content-block fade-in">
				<?php the_content(); ?>
			</div>
		<?php endwhile; ?>
	</div>
</section>
<?php get_footer(); ?>
