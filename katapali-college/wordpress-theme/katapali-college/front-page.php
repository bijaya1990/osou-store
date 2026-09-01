<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>

<section class="hero" id="kc-hero">
	<?php
	$slides = get_posts( array( 'post_type' => 'kc_slide', 'posts_per_page' => -1, 'meta_key' => 'kc_order', 'orderby' => 'meta_value_num', 'order' => 'ASC' ) );
	if ( $slides ) :
		foreach ( $slides as $i => $s ) :
			$img = get_the_post_thumbnail_url( $s->ID, 'full' );
			if ( ! $img ) $img = KC_URI . '/assets/demo-images/banner1.svg';
			$sub = get_post_meta( $s->ID, 'kc_subtitle', true );
			$b1t = get_post_meta( $s->ID, 'kc_btn1_text', true ) ?: 'Admissions';
			$b1l = get_post_meta( $s->ID, 'kc_btn1_link', true ) ?: '#';
			$b2t = get_post_meta( $s->ID, 'kc_btn2_text', true ) ?: 'Know More';
			$b2l = get_post_meta( $s->ID, 'kc_btn2_link', true ) ?: '#';
			?>
			<div class="hero-slide<?php echo $i === 0 ? ' active' : ''; ?>" style="background-image:url('<?php echo esc_url( $img ); ?>')">
				<div class="hero-content"><div class="inner">
					<h1><?php echo esc_html( $s->post_title ); ?></h1>
					<p><?php echo esc_html( $sub ); ?></p>
					<div class="hero-btns">
						<a href="<?php echo esc_url( $b1l ); ?>" class="btn btn-accent"><?php echo esc_html( $b1t ); ?></a>
						<a href="<?php echo esc_url( $b2l ); ?>" class="btn btn-outline"><?php echo esc_html( $b2t ); ?></a>
					</div>
				</div></div>
			</div>
			<?php
		endforeach;
		?>
		<button class="hero-arrow prev"><i class="fa-solid fa-chevron-left"></i></button>
		<button class="hero-arrow next"><i class="fa-solid fa-chevron-right"></i></button>
		<div class="hero-dots">
			<?php foreach ( $slides as $i => $s ) : ?>
				<span class="<?php echo $i === 0 ? 'active' : ''; ?>" data-i="<?php echo esc_attr( $i ); ?>"></span>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="hero-slide active" style="background-image:url('<?php echo esc_url( KC_URI . '/assets/demo-images/banner1.svg' ); ?>')">
			<div class="hero-content"><div class="inner">
				<h1><?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?></h1>
				<p><?php echo esc_html( get_theme_mod( 'kc_tagline', kc_default( 'kc_tagline' ) ) ); ?></p>
				<p style="color:#fbbf24;font-size:.85rem;margin-top:14px;">No hero slides yet — run the Demo Content Importer under <strong>Katapali College &rarr; Demo Content Importer</strong>, or add some under Hero Slides.</p>
			</div></div>
		</div>
	<?php endif; ?>
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
						$ticker_html .= '<a href="' . esc_url( get_permalink( $tn ) ) . '">' . esc_html( get_the_title( $tn ) ) . ' <span>(' . esc_html( get_the_date( 'd M Y', $tn ) ) . ')</span></a>';
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
			<div class="about-card">
				<span class="eyebrow">Who We Are</span>
				<h2>About <?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?></h2>
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
				<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="btn btn-line btn-sm">Read More <i class="fa-solid fa-arrow-right"></i></a>
			</div>
			<div class="about-card principal-card">
				<span class="eyebrow">Welcome Message</span>
				<h2>Principal's Message</h2>
				<div class="principal-mini">
					<img src="<?php echo esc_url( get_theme_mod( 'kc_principal_photo' ) ?: KC_URI . '/assets/demo-images/principal.svg' ); ?>" alt="Principal">
					<div>
						<h4><?php echo esc_html( get_theme_mod( 'kc_principal_name', kc_default( 'kc_principal_name' ) ) ); ?></h4>
						<div class="desig"><?php echo esc_html( get_theme_mod( 'kc_principal_desig', kc_default( 'kc_principal_desig' ) ) ); ?></div>
					</div>
				</div>
				<p><?php echo esc_html( wp_trim_words( get_theme_mod( 'kc_principal_message', kc_default( 'kc_principal_message' ) ), 30 ) ); ?></p>
				<a href="<?php echo esc_url( home_url( '/about-us/#principal-desk' ) ); ?>" class="btn btn-line btn-sm">Read More <i class="fa-solid fa-arrow-right"></i></a>
			</div>
		</div>
	</div>
</section>

<section class="section section-alt">
	<div class="container">
		<div class="section-head fade-in"><span class="eyebrow">Stay Updated</span><h2>Notices, Recruitment &amp; Tenders</h2></div>
		<div class="grid grid-3 fade-in three-col-fixed">
			<div class="tc-col">
				<h3 class="tc-head">Latest Notices</h3>
				<div class="tc-body">
					<?php
					$notices = get_posts( array( 'post_type' => 'kc_notice', 'posts_per_page' => 3 ) );
					if ( $notices ) { foreach ( $notices as $n ) kc_notice_card( $n->ID ); } else { echo '<div class="empty-msg">No notices yet.</div>'; }
					?>
				</div>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'kc_notice' ) ); ?>" class="btn btn-line btn-sm">View All Notices <i class="fa-solid fa-arrow-right"></i></a>
			</div>
			<div class="tc-col">
				<h3 class="tc-head">Latest Recruitment</h3>
				<div class="tc-body">
					<?php
					$rec = get_posts( array( 'post_type' => 'kc_recruitment', 'posts_per_page' => 3, 'meta_key' => 'kc_status', 'meta_value' => 'Open' ) );
					if ( ! $rec ) $rec = get_posts( array( 'post_type' => 'kc_recruitment', 'posts_per_page' => 3 ) );
					if ( $rec ) { foreach ( $rec as $r ) kc_recruitment_card( $r->ID ); } else { echo '<div class="empty-msg">No openings currently.</div>'; }
					?>
				</div>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'kc_recruitment' ) ); ?>" class="btn btn-line btn-sm">View All Openings <i class="fa-solid fa-arrow-right"></i></a>
			</div>
			<div class="tc-col">
				<h3 class="tc-head">Latest Tenders</h3>
				<div class="tc-body">
					<?php
					$tnd = get_posts( array( 'post_type' => 'kc_tender', 'posts_per_page' => 3 ) );
					if ( $tnd ) { foreach ( $tnd as $t ) kc_tender_card( $t->ID ); } else { echo '<div class="empty-msg">No tenders currently.</div>'; }
					?>
				</div>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'kc_tender' ) ); ?>" class="btn btn-line btn-sm">View All Tenders <i class="fa-solid fa-arrow-right"></i></a>
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
