<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>

<section class="hex-hero">
	<div class="container">
		<div class="hex-hero-head fade-in">
			<span class="eyebrow">Welcome to</span>
			<h1><?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?></h1>
			<p><?php echo esc_html( get_theme_mod( 'kc_tagline', kc_default( 'kc_tagline' ) ) ); ?></p>
			<div class="hero-btns">
				<a href="<?php echo esc_url( home_url( '/admissions/' ) ); ?>" class="btn btn-accent">Admissions <i class="fa-solid fa-arrow-right"></i></a>
				<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="btn btn-line">Know More</a>
			</div>
		</div>
		<?php
		$slides = get_posts( array( 'post_type' => 'kc_slide', 'posts_per_page' => 7, 'meta_key' => 'kc_order', 'orderby' => 'meta_value_num', 'order' => 'ASC' ) );
		if ( $slides ) :
			?>
			<div class="hex-grid">
				<?php foreach ( $slides as $i => $s ) :
					$img = get_the_post_thumbnail_url( $s->ID, 'large' );
					if ( ! $img ) $img = KC_URI . '/assets/demo-images/banner1.svg';
					?>
					<div class="hex-item hex-pos-<?php echo esc_attr( ( $i % 2 ) + 1 ); ?>">
						<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $s->post_title ); ?>" loading="lazy">
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p style="text-align:center;color:var(--accent);font-size:.9rem;">No hero photos yet — run the Demo Content Importer under <strong>Katapali College &rarr; Demo Content Importer</strong>, or add some under Hero Slides.</p>
		<?php endif; ?>
	</div>
</section>

<section class="notice-ticker">
	<div class="container ticker-wrap">
		<span class="ticker-label"><i class="fa-solid fa-bullhorn"></i> Latest Notice</span>
		<div class="ticker-track-wrap">
			<div class="ticker-track">
				<?php
				$ticker_notices = get_posts( array( 'post_type' => 'kc_notice', 'posts_per_page' => 6 ) );
				if ( $ticker_notices ) {
					$ticker_html = '';
					foreach ( $ticker_notices as $tn ) {
						$ticker_html .= '<a href="' . esc_url( get_permalink( $tn ) ) . '"><i class="fa-solid fa-hand-point-right"></i>' . esc_html( get_the_title( $tn ) ) . ' <span>(' . esc_html( get_the_date( 'd M Y', $tn ) ) . ')</span></a>';
					}
					echo $ticker_html . $ticker_html; // duplicated for a seamless CSS loop
				} else {
					echo '<a href="' . esc_url( get_post_type_archive_link( 'kc_notice' ) ) . '">No notices published yet — add one under Katapali College &rarr; Notices.</a>';
				}
				?>
			</div>
		</div>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="grid grid-2 about-grid fade-in">
			<div class="about-card tbar-box">
				<h2 class="tbar-head">About <?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?></h2>
				<div class="tbar-body">
					<p>
					<?php
					$about_page = get_page_by_path( 'about-us' );
					if ( $about_page ) {
						$about_spaced = preg_replace( '#<h[1-6][^>]*>.*?</h[1-6]>#i', '', $about_page->post_content, 1 ); // drop the first sub-heading; our own <h2> above already labels this card
						$about_spaced = preg_replace( '#</(p|div|li|h[1-6])>#i', '$0 ', $about_spaced );
						echo esc_html( wp_trim_words( wp_strip_all_tags( $about_spaced ), 55 ) );
					} else {
						echo esc_html( get_theme_mod( 'kc_tagline', kc_default( 'kc_tagline' ) ) ) . ' Add an "About Us" page (slug: about-us), or run the Demo Content Importer.';
					}
					?>
					</p>
					<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="read-more-link">[Read More]</a>
				</div>
			</div>
			<div class="about-card principal-card tbar-box">
				<h2 class="tbar-head">Principal's Message</h2>
				<div class="tbar-body">
					<div class="principal-mini">
						<img src="<?php echo esc_url( get_theme_mod( 'kc_principal_photo' ) ?: KC_URI . '/assets/demo-images/principal.svg' ); ?>" alt="Principal">
						<div>
							<h4><?php echo esc_html( get_theme_mod( 'kc_principal_name', kc_default( 'kc_principal_name' ) ) ); ?></h4>
							<div class="desig"><?php echo esc_html( get_theme_mod( 'kc_principal_desig', kc_default( 'kc_principal_desig' ) ) ); ?></div>
						</div>
					</div>
					<p><?php echo esc_html( wp_trim_words( get_theme_mod( 'kc_principal_message', kc_default( 'kc_principal_message' ) ), 30 ) ); ?></p>
					<a href="<?php echo esc_url( home_url( '/about-us/#principal-desk' ) ); ?>" class="read-more-link">[Read More]</a>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="section section-alt">
	<div class="container">
		<div class="grid grid-3 fade-in three-col-fixed">
			<div class="tc-col tbar-box">
				<h3 class="tbar-head">Notice</h3>
				<div class="tbar-body tc-body">
					<?php
					$notices = get_posts( array( 'post_type' => 'kc_notice', 'posts_per_page' => 5 ) );
					if ( $notices ) { foreach ( $notices as $n ) kc_notice_mini( $n->ID ); } else { echo '<div class="empty-msg">No notices yet.</div>'; }
					?>
				</div>
			</div>
			<div class="tc-col tbar-box">
				<h3 class="tbar-head">Tenders</h3>
				<div class="tbar-body tc-body">
					<?php
					$tnd = get_posts( array( 'post_type' => 'kc_tender', 'posts_per_page' => 5 ) );
					if ( $tnd ) { foreach ( $tnd as $t ) kc_tender_mini( $t->ID ); } else { echo '<div class="empty-msg">Opps, No posts were found.</div>'; }
					?>
				</div>
			</div>
			<div class="tc-col tbar-box">
				<h3 class="tbar-head">Quick Links</h3>
				<div class="tbar-body tc-body">
					<ul class="quick-links-list">
						<?php kc_link_list( 'Quick Links' ); ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="section-head fade-in"><span class="eyebrow">Our Faculty</span><h2>Meet Our Teachers</h2><p>Dedicated and experienced faculty guiding our students</p></div>
		<div class="faculty-slider-wrap fade-in">
			<div class="faculty-track" id="kc-faculty-track">
				<?php
				$fac_q = new WP_Query( array(
					'post_type' => 'kc_faculty', 'posts_per_page' => 7,
					'meta_query' => array( array( 'key' => 'kc_on_slider', 'value' => '1' ) ),
					'meta_key' => 'kc_order', 'orderby' => 'meta_value_num', 'order' => 'ASC',
				) );
				if ( ! $fac_q->have_posts() ) {
					$fac_q = new WP_Query( array( 'post_type' => 'kc_faculty', 'posts_per_page' => 7, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
				}
				$loop_count = 0; $ids = array();
				while ( $fac_q->have_posts() ) : $fac_q->the_post(); $ids[] = get_the_ID(); kc_faculty_card( get_the_ID() ); $loop_count++;
				endwhile;
				foreach ( $ids as $id ) kc_faculty_card( $id ); // duplicate set for seamless auto-scroll
				wp_reset_postdata();
				?>
			</div>
			<div class="faculty-arrows">
				<button id="kc-faculty-prev"><i class="fa-solid fa-chevron-left"></i></button>
				<button id="kc-faculty-next"><i class="fa-solid fa-chevron-right"></i></button>
			</div>
		</div>
	</div>
</section>

<section class="stats-bar">
	<div class="container">
		<div class="stats-grid" id="kc-stats">
			<?php
			$stat_defs = array(
				array( 'fa-user-graduate', get_theme_mod( 'kc_stat_students', 1284 ), 'Total Students' ),
				array( 'fa-chalkboard-user', get_theme_mod( 'kc_stat_faculty', 42 ), 'Total Faculty' ),
				array( 'fa-building-columns', get_theme_mod( 'kc_stat_depts', 10 ), 'Departments' ),
				array( 'fa-award', get_theme_mod( 'kc_stat_years', 40 ), 'Years of Excellence' ),
			);
			foreach ( $stat_defs as $s ) {
				echo '<div class="stat-card"><i class="fa-solid ' . esc_attr( $s[0] ) . '"></i><div class="num" data-target="' . esc_attr( $s[1] ) . '">0</div><div class="lbl">' . esc_html( $s[2] ) . '</div></div>';
			}
			?>
		</div>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="section-head fade-in"><span class="eyebrow">Campus Life</span><h2>Photo Gallery</h2><p>Glimpses of campus, events, sports and celebrations</p></div>
		<div class="grid grid-4 fade-in">
			<?php
			$gal = get_posts( array( 'post_type' => 'kc_gallery', 'posts_per_page' => 8, 'meta_key' => 'kc_featured', 'meta_value' => '1' ) );
			if ( ! $gal ) $gal = get_posts( array( 'post_type' => 'kc_gallery', 'posts_per_page' => 8 ) );
			foreach ( $gal as $g ) kc_gallery_item( $g->ID );
			?>
		</div>
		<div style="text-align:center;margin-top:34px;"><a href="<?php echo esc_url( get_post_type_archive_link( 'kc_gallery' ) ); ?>" class="btn btn-accent">View Full Gallery <i class="fa-solid fa-images"></i></a></div>
	</div>
</section>

<?php get_footer(); ?>
