<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>

<section class="hero-slider" id="kc-hero">
	<?php
	$slides = get_posts( array( 'post_type' => 'kc_slide', 'posts_per_page' => 7, 'meta_key' => 'kc_order', 'orderby' => 'meta_value_num', 'order' => 'ASC' ) );
	if ( $slides ) :
		foreach ( $slides as $i => $s ) :
			$img = get_the_post_thumbnail_url( $s->ID, 'full' );
			if ( ! $img ) $img = KC_URI . '/assets/demo-images/banner1.svg';
			?>
			<div class="hero-slide<?php echo $i === 0 ? ' active' : ''; ?>" style="background-image:url('<?php echo esc_url( $img ); ?>');">
				<div class="hero-slide-overlay"></div>
				<div class="container hero-slide-content fade-in">
					<span class="eyebrow">Welcome to</span>
					<h1><?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?></h1>
					<p><?php echo esc_html( get_theme_mod( 'kc_tagline', kc_default( 'kc_tagline' ) ) ); ?></p>
					<div class="hero-btns">
						<a href="<?php echo esc_url( home_url( '/admissions/' ) ); ?>" class="btn btn-accent">Admissions <i class="fa-solid fa-arrow-right"></i></a>
						<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="btn btn-line">Know More</a>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
		<?php if ( count( $slides ) > 1 ) : ?>
			<button class="hero-arrow hero-arrow-prev" id="kc-hero-prev"><i class="fa-solid fa-chevron-left"></i></button>
			<button class="hero-arrow hero-arrow-next" id="kc-hero-next"><i class="fa-solid fa-chevron-right"></i></button>
			<div class="hero-dots" id="kc-hero-dots">
				<?php foreach ( $slides as $i => $s ) : ?>
					<span class="hero-dot<?php echo $i === 0 ? ' active' : ''; ?>" data-slide="<?php echo esc_attr( $i ); ?>"></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="hero-slide active" style="background-image:url('<?php echo esc_url( KC_URI . '/assets/demo-images/banner1.svg' ); ?>');">
			<div class="hero-slide-overlay"></div>
			<div class="container hero-slide-content fade-in">
				<span class="eyebrow">Welcome to</span>
				<h1><?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?></h1>
				<p><?php echo esc_html( get_theme_mod( 'kc_tagline', kc_default( 'kc_tagline' ) ) ); ?></p>
				<div class="hero-btns">
					<a href="<?php echo esc_url( home_url( '/admissions/' ) ); ?>" class="btn btn-accent">Admissions <i class="fa-solid fa-arrow-right"></i></a>
					<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="btn btn-line">Know More</a>
				</div>
			</div>
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

<section class="kc-apply-bar-section" id="kc-apply-page" data-college="<?php echo esc_attr( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?>" data-address="<?php echo esc_attr( get_theme_mod( 'kc_address', kc_default( 'kc_address' ) ) . ' - ' . get_theme_mod( 'kc_pin', kc_default( 'kc_pin' ) ) ); ?>">
	<div class="container">
		<div class="kc-apply-bar-label">Online Applications</div>
		<div class="kc-apply-btns">
			<button type="button" class="kc-apply-btn" data-form="clc"><i class="fa-solid fa-file-circle-check"></i> Apply CLC</button>
			<button type="button" class="kc-apply-btn" data-form="cl"><i class="fa-solid fa-calendar-days"></i> Apply C.L.</button>
			<button type="button" class="kc-apply-btn" data-form="certmark" data-preselect="certificate"><i class="fa-solid fa-award"></i> Apply Certificate</button>
			<button type="button" class="kc-apply-btn" data-form="certmark" data-preselect="marksheet"><i class="fa-solid fa-file-lines"></i> Apply Marksheet</button>
		</div>

		<!-- CLC form -->
		<form class="kc-apply-form-box" id="kc-form-clc" hidden onsubmit="return false;">
			<h2>Application for College Leaving Certificate (CLC)</h2>
			<div id="kc-clc-form-el">
			<div class="kc-row">
				<div class="kc-f"><label>Title</label><select id="clc_title" required><option value="Mr.">Mr.</option><option value="Miss">Miss</option><option value="Mrs.">Mrs.</option></select></div>
				<div class="kc-f"><label>First Name</label><input type="text" id="clc_first" required></div>
				<div class="kc-f"><label>Middle Name</label><input type="text" id="clc_middle"></div>
				<div class="kc-f"><label>Last Name</label><input type="text" id="clc_last" required></div>
			</div>
			<div class="kc-row">
				<div class="kc-f"><label>Relation</label><select id="clc_relation" required><option value="S/o">S/o</option><option value="D/o">D/o</option><option value="W/o">W/o</option></select></div>
				<div class="kc-f kc-f-wide"><label>Father's / Husband's Name</label><input type="text" id="clc_parent" required></div>
			</div>
			<div class="kc-row">
				<div class="kc-f"><label>College Roll No.</label><input type="text" id="clc_college_roll" required></div>
				<div class="kc-f"><label>University Roll No.</label><input type="text" id="clc_univ_roll" required></div>
				<div class="kc-f"><label>Semester</label><input type="text" id="clc_semester" required></div>
				<div class="kc-f"><label>Date of Birth</label><input type="date" id="clc_dob" required></div>
			</div>
			<div class="kc-f"><label>Result</label>
				<div class="kc-check-group">
					<label><input type="radio" name="clc_result" id="clc_result_pass"> Pass</label>
					<label><input type="radio" name="clc_result" id="clc_result_fail"> Fail</label>
				</div>
			</div>
			<h4>Address</h4>
			<div class="kc-row">
				<div class="kc-f"><label>At</label><input type="text" id="clc_addr_at" required></div>
				<div class="kc-f"><label>P.O.</label><input type="text" id="clc_addr_po" required></div>
				<div class="kc-f"><label>Block</label><input type="text" id="clc_addr_block" required></div>
			</div>
			<div class="kc-row">
				<div class="kc-f"><label>District</label><input type="text" id="clc_addr_dist" required></div>
				<div class="kc-f"><label>State</label><input type="text" id="clc_addr_state" required></div>
				<div class="kc-f"><label>PIN</label><input type="text" id="clc_addr_pin" required></div>
			</div>
			</div>
			<div class="kc-apply-actions"><button type="button" id="kc-generate-clc" class="btn btn-accent">Generate Application <i class="fa-solid fa-arrow-right"></i></button></div>
		</form>

		<!-- C.L. form -->
		<form class="kc-apply-form-box" id="kc-form-cl" hidden onsubmit="return false;">
			<h2>Application for Casual Leave (C.L.)</h2>
			<div id="kc-cl-form-el">
			<div class="kc-row">
				<div class="kc-f"><label>Title</label><select id="cl_title" required><option value="Mr.">Mr.</option><option value="Miss">Miss</option><option value="Mrs.">Mrs.</option></select></div>
				<div class="kc-f"><label>First Name</label><input type="text" id="cl_first" required></div>
				<div class="kc-f"><label>Middle Name</label><input type="text" id="cl_middle"></div>
				<div class="kc-f"><label>Last Name</label><input type="text" id="cl_last" required></div>
			</div>
			<div class="kc-f"><label>Designation</label>
				<select id="cl_designation" required>
					<option value="Lecturer in History">Lecturer in History</option>
					<option value="Lecturer in Political Science">Lecturer in Political Science</option>
					<option value="Lecturer in Education">Lecturer in Education</option>
					<option value="Lecturer in Odia">Lecturer in Odia</option>
					<option value="Lecturer in English">Lecturer in English</option>
					<option value="Lecturer in Sociology">Lecturer in Sociology</option>
					<option value="Lecturer in Hindi">Lecturer in Hindi</option>
					<option value="Lecturer in Sanskrit">Lecturer in Sanskrit</option>
					<option value="Lecturer in Economics">Lecturer in Economics</option>
				</select>
			</div>
			<div class="kc-f"><label>Reason for Leave</label><textarea id="cl_reason" rows="2" required></textarea></div>
			<div class="kc-row">
				<div class="kc-f"><label>C.L. From Date</label><input type="date" id="cl_from" required></div>
				<div class="kc-f"><label>To Date</label><input type="date" id="cl_to" required></div>
				<div class="kc-f"><label>Number of Days</label><input type="text" id="cl_days" readonly placeholder="auto-calculated"></div>
				<div class="kc-f"><label>Date of Joining</label><input type="date" id="cl_joining" required></div>
			</div>
			</div>
			<div class="kc-apply-actions"><button type="button" id="kc-generate-cl" class="btn btn-accent">Generate Application <i class="fa-solid fa-arrow-right"></i></button></div>
		</form>

		<!-- Certificate / Marksheet form -->
		<form class="kc-apply-form-box" id="kc-form-certmark" hidden onsubmit="return false;">
			<h2>Application for Certificate / Mark Sheet</h2>
			<div id="kc-cm-form-el">
			<div class="kc-row">
				<div class="kc-f"><label>Title</label><select id="cm_title" required><option value="Mr.">Mr.</option><option value="Miss">Miss</option><option value="Mrs.">Mrs.</option></select></div>
				<div class="kc-f"><label>First Name</label><input type="text" id="cm_first" required></div>
				<div class="kc-f"><label>Middle Name</label><input type="text" id="cm_middle"></div>
				<div class="kc-f"><label>Last Name</label><input type="text" id="cm_last" required></div>
			</div>
			<div class="kc-row">
				<div class="kc-f"><label>Relation</label><select id="cm_relation" required><option value="S/o">S/o</option><option value="D/o">D/o</option><option value="W/o">W/o</option></select></div>
				<div class="kc-f kc-f-wide"><label>Father's / Mother's Name</label><input type="text" id="cm_parent" required></div>
			</div>
			<div class="kc-row">
				<div class="kc-f"><label>College Roll No.</label><input type="text" id="cm_college_roll" required></div>
				<div class="kc-f"><label>University Roll No.</label><input type="text" id="cm_univ_roll" required></div>
				<div class="kc-f"><label>Course / Stream</label><input type="text" id="cm_course" required></div>
				<div class="kc-f"><label>Semester / Examination</label><input type="text" id="cm_semester" required></div>
			</div>
			<div class="kc-row">
				<div class="kc-f"><label>Examination Year</label><input type="text" id="cm_exam_year" required></div>
				<div class="kc-f"><label>Grade Point Secured</label><input type="text" id="cm_gradepoint" required></div>
				<div class="kc-f"><label>Date of Birth</label><input type="date" id="cm_dob" required></div>
			</div>
			<div class="kc-f"><label>Final Semester Result</label>
				<div class="kc-check-group">
					<label><input type="checkbox" id="cm_class_1"> First Class Honours</label>
					<label><input type="checkbox" id="cm_class_2"> Second Class Honours</label>
					<label><input type="checkbox" id="cm_class_3"> Pass Without Honours</label>
					<label><input type="checkbox" id="cm_class_4"> First Class Honours with Distinction</label>
				</div>
			</div>
			<h4>Address</h4>
			<div class="kc-row">
				<div class="kc-f"><label>At</label><input type="text" id="cm_addr_at" required></div>
				<div class="kc-f"><label>P.O.</label><input type="text" id="cm_addr_po" required></div>
				<div class="kc-f"><label>Block</label><input type="text" id="cm_addr_block" required></div>
			</div>
			<div class="kc-row">
				<div class="kc-f"><label>District</label><input type="text" id="cm_addr_dist" required></div>
				<div class="kc-f"><label>State</label><input type="text" id="cm_addr_state" required></div>
				<div class="kc-f"><label>PIN</label><input type="text" id="cm_addr_pin" required></div>
			</div>
			<h4>Document(s) Requested</h4>
			<div class="kc-check-group">
				<label><input type="checkbox" id="cm_type_cert"> Certificate</label>
				<label><input type="checkbox" id="cm_type_mark"> Mark Sheet</label>
			</div>
			</div>
			<div class="kc-apply-actions"><button type="button" id="kc-generate-certmark" class="btn btn-accent">Generate Application <i class="fa-solid fa-arrow-right"></i></button></div>
		</form>

		<!-- Preview -->
		<div class="kc-apply-preview-wrap" id="kc-apply-preview-wrap" hidden>
			<div class="kc-apply-preview" id="kc-apply-preview"></div>
			<div class="kc-apply-preview-actions">
				<button type="button" id="kc-apply-download" class="btn btn-accent"><i class="fa-solid fa-download"></i> Download PDF</button>
				<button type="button" id="kc-apply-print" class="btn btn-line"><i class="fa-solid fa-print"></i> Print</button>
				<button type="button" id="kc-apply-edit" class="btn btn-line"><i class="fa-solid fa-pen"></i> Edit Application</button>
			</div>
		</div>
	</div>

	<div class="kc-apply-modal" id="kc-apply-success-modal" hidden>
		<div class="kc-apply-modal-box">
			<i class="fa-solid fa-circle-check"></i>
			<h3>Form Submitted Successfully</h3>
			<p>Download your application now, put your sign, and submit it to the college authority.</p>
			<button type="button" class="btn btn-accent" id="kc-apply-modal-close">OK, Got It</button>
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
					<div class="principal-split">
						<div class="principal-photo-card">
							<img src="<?php echo esc_url( get_theme_mod( 'kc_principal_photo' ) ?: KC_URI . '/assets/demo-images/principal.svg' ); ?>" alt="Principal">
							<h4><?php echo esc_html( get_theme_mod( 'kc_principal_name', kc_default( 'kc_principal_name' ) ) ); ?></h4>
							<div class="desig-rule"></div>
							<div class="desig"><?php echo esc_html( get_theme_mod( 'kc_principal_desig', kc_default( 'kc_principal_desig' ) ) ); ?></div>
						</div>
						<div class="principal-message-col">
							<p><?php echo esc_html( wp_trim_words( get_theme_mod( 'kc_principal_message', kc_default( 'kc_principal_message' ) ), 30 ) ); ?></p>
							<a href="<?php echo esc_url( home_url( '/about-us/#principal-desk' ) ); ?>" class="read-more-link">[Read More]</a>
						</div>
					</div>
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
		<div class="section-head fade-in"><span class="eyebrow">Our Faculty</span><h2>Meet Our Staff</h2><p>Dedicated and experienced faculty guiding our students</p></div>
		<div class="faculty-slider-wrap fade-in">
			<div class="faculty-track" id="kc-faculty-track">
				<?php
				$fac_q = new WP_Query( array(
					'post_type' => 'kc_faculty', 'posts_per_page' => -1,
					'meta_query' => array( array( 'key' => 'kc_on_slider', 'value' => '1' ) ),
					'meta_key' => 'kc_order', 'orderby' => 'meta_value_num', 'order' => 'ASC',
				) );
				if ( ! $fac_q->have_posts() ) {
					$fac_q = new WP_Query( array( 'post_type' => 'kc_faculty', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
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
