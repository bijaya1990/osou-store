<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
kc_banner( is_search() ? 'Search Results' : 'Latest' );
?>
<section class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid-3">
				<?php while ( have_posts() ) : the_post(); ?>
					<div class="card">
						<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
						<div class="cdate"><i class="fa-regular fa-calendar"></i> <?php echo esc_html( get_the_date() ); ?></div>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
						<a class="read-more" href="<?php the_permalink(); ?>">Read More <i class="fa-solid fa-arrow-right"></i></a>
					</div>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p>Nothing found.</p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
