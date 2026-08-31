<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
kc_banner( 'Photo Gallery', 'Gallery' );
$cats = get_terms( array( 'taxonomy' => 'kc_gallery_cat', 'hide_empty' => true ) );
?>
<section class="section">
	<div class="container">
		<?php if ( $cats && ! is_wp_error( $cats ) && count( $cats ) ) : ?>
		<div class="gallery-filters" id="gal-filters">
			<button class="gf-btn active" data-cat="all">All</button>
			<?php foreach ( $cats as $c ) : ?>
				<button class="gf-btn" data-cat="<?php echo esc_attr( $c->name ); ?>"><?php echo esc_html( $c->name ); ?></button>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<div class="grid grid-4" id="gal-grid">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); kc_gallery_item( get_the_ID() ); endwhile; else : ?>
				<p class="empty-msg">No photos added yet — add one under Katapali College &rarr; Gallery.</p>
			<?php endif; ?>
		</div>
	</div>
</section>
<div class="lightbox" id="lightbox"><button class="lb-close" id="lbClose"><i class="fa-solid fa-xmark"></i></button><div><img id="lbImg" alt=""><div class="lb-cap" id="lbCap"></div></div></div>
<?php get_footer(); ?>
