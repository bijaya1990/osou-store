<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
kc_banner( 'Faculty', 'Faculty' );
$depts = get_terms( array( 'taxonomy' => 'kc_department', 'hide_empty' => true ) );
?>
<section class="section">
	<div class="container">
		<?php if ( $depts && ! is_wp_error( $depts ) && count( $depts ) ) : ?>
		<div class="gallery-filters" id="fac-filters">
			<button class="gf-btn active" data-dept="all">All Departments</button>
			<?php foreach ( $depts as $d ) : ?>
				<button class="gf-btn" data-dept="<?php echo esc_attr( $d->name ); ?>"><?php echo esc_html( $d->name ); ?></button>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<div class="grid grid-4" id="fac-grid">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
				$id = get_the_ID();
				$dept_terms = get_the_terms( $id, 'kc_department' );
				$dept_name = ( $dept_terms && ! is_wp_error( $dept_terms ) ) ? $dept_terms[0]->name : '';
				$desig = get_post_meta( $id, 'kc_designation', true );
				$qual = get_post_meta( $id, 'kc_qualification', true );
				$img = get_the_post_thumbnail_url( $id, 'medium' ) ?: KC_URI . '/assets/demo-images/principal.svg';
				?>
				<div class="faculty-card" data-dept="<?php echo esc_attr( $dept_name ); ?>" style="text-align:center;">
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php the_title_attribute(); ?>">
					<h4><?php the_title(); ?></h4>
					<div class="desig"><?php echo esc_html( $desig ); ?></div>
					<div class="dept"><?php echo esc_html( $dept_name ); ?></div>
					<div class="qual" style="font-size:.78rem;color:var(--muted);"><?php echo esc_html( $qual ); ?></div>
				</div>
			<?php endwhile; else : ?>
				<p class="empty-msg">No faculty added yet — add one under Katapali College &rarr; Faculty.</p>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
