<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function kc_importer_menu() {
	add_submenu_page( 'katapali-college', 'Demo Content Importer', 'Demo Content Importer', 'manage_options', 'kc-demo-importer', 'kc_importer_page' );
}
add_action( 'admin_menu', 'kc_importer_menu' );

function kc_import_image( $file, $title = '' ) {
	static $cache = array();
	if ( isset( $cache[ $file ] ) ) return $cache[ $file ];
	$path = KC_DIR . '/assets/demo-images/' . $file;
	if ( ! file_exists( $path ) ) return 0;
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$filetype = wp_check_filetype( basename( $path ), null );
	if ( substr( $path, -4 ) === '.svg' ) $filetype = array( 'ext' => 'svg', 'type' => 'image/svg+xml' );
	$upload_dir = wp_upload_dir();
	$filename = wp_unique_filename( $upload_dir['path'], basename( $path ) );
	$dest = trailingslashit( $upload_dir['path'] ) . $filename;
	copy( $path, $dest );
	$attachment = array(
		'post_mime_type' => $filetype['type'],
		'post_title'     => $title ? $title : sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);
	$attach_id = wp_insert_attachment( $attachment, $dest );
	$meta = wp_generate_attachment_metadata( $attach_id, $dest );
	if ( $meta ) wp_update_attachment_metadata( $attach_id, $meta );
	$cache[ $file ] = $attach_id;
	return $attach_id;
}

function kc_import_page( $title, $slug, $html ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) return $existing->ID;
	return wp_insert_post( array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $html,
		'post_status'  => 'publish',
		'post_type'    => 'page',
	) );
}

function kc_import_term( $name, $taxonomy ) {
	$t = term_exists( $name, $taxonomy );
	if ( $t ) return (int) $t['term_id'];
	$r = wp_insert_term( $name, $taxonomy );
	return is_wp_error( $r ) ? 0 : $r['term_id'];
}

function kc_import_cpt( $post_type, $title, $meta = array(), $terms = array(), $img = '', $content = '' ) {
	$id = wp_insert_post( array(
		'post_title'   => $title,
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => $post_type,
	) );
	if ( is_wp_error( $id ) || ! $id ) return 0;
	foreach ( $meta as $k => $v ) update_post_meta( $id, $k, $v );
	foreach ( $terms as $tax => $names ) {
		$ids = array();
		foreach ( (array) $names as $n ) $ids[] = kc_import_term( $n, $tax );
		wp_set_object_terms( $id, $ids, $tax );
	}
	if ( $img ) {
		$att = kc_import_image( $img, $title );
		if ( $att ) set_post_thumbnail( $id, $att );
	}
	return $id;
}

function kc_run_demo_import() {
	$college = 'KATAPALI +3 COLLEGE, KATAPALI';

	/* ---------- logo ---------- */
	$logo_id = kc_import_image( 'logo.svg', $college . ' Logo' );
	if ( $logo_id ) set_theme_mod( 'custom_logo', $logo_id );

	/* ---------- hero slides ---------- */
	$slides = array(
		array( 'KATAPALI +3 COLLEGE, KATAPALI', 'banner1.svg', 'Empowering Rural Education Since 1985', 'Admissions', home_url( '/admissions/' ), 'Know More', home_url( '/about-us/' ) ),
		array( 'A Green, Peaceful Campus', 'banner2.svg', 'Spacious classrooms, laboratories and playground spread over 6 acres', 'Our Campus', home_url( '/gallery/' ), 'Departments', home_url( '/academics/' ) ),
		array( 'Our Students, Our Pride', 'banner3.svg', 'Over 1200 students from 40+ villages of Bijepur and Bargarh block', 'Student Corner', home_url( '/student-corner/' ), 'Scholarships', home_url( '/student-corner/' ) ),
		array( 'Learn. Grow. Serve.', 'banner4.svg', 'Central library with 18,000+ books, e-journals and reading hall', 'Library', home_url( '/student-corner/' ), 'Contact Us', home_url( '/contact-us/' ) ),
	);
	foreach ( $slides as $i => $s ) {
		kc_import_cpt( 'kc_slide', $s[0], array(
			'kc_subtitle' => $s[2], 'kc_btn1_text' => $s[3], 'kc_btn1_link' => $s[4],
			'kc_btn2_text' => $s[5], 'kc_btn2_link' => $s[6], 'kc_order' => $i,
		), array(), $s[1] );
	}

	/* ---------- faculty ---------- */
	$faculty = array(
		array( 'Prof. Demo Name 1', 'Lecturer in History', 'History', 'M.A., M.Phil. (History)', '18 years', 1 ),
		array( 'Prof. Demo Name 2', 'Lecturer in Political Science', 'Political Science', 'M.A., NET (Pol. Science)', '14 years', 1 ),
		array( 'Prof. Demo Name 3', 'Lecturer in Odia', 'Odia', 'M.A., Ph.D. (Odia)', '21 years', 1 ),
		array( 'Prof. Demo Name 4', 'Lecturer in English', 'English', 'M.A., NET (English)', '11 years', 1 ),
		array( 'Prof. Demo Name 5', 'Lecturer in Mathematics', 'Mathematics', 'M.Sc., M.Phil. (Mathematics)', '9 years', 1 ),
		array( 'Prof. Demo Name 6', 'Lecturer in Physics', 'Physics', 'M.Sc. (Physics), NET', '12 years', 1 ),
		array( 'Prof. Demo Name 7', 'Lecturer in Chemistry', 'Chemistry', 'M.Sc., Ph.D. (Chemistry)', '16 years', 1 ),
		array( 'Prof. Demo Name 8', 'Lecturer in Economics', 'Economics', 'M.A. (Economics)', '8 years', 0 ),
		array( 'Prof. Demo Name 9', 'Lecturer in Botany', 'Botany', 'M.Sc. (Botany), SLET', '10 years', 0 ),
		array( 'Prof. Demo Name 10', 'Lecturer in Zoology', 'Zoology', 'M.Sc., Ph.D. (Zoology)', '13 years', 0 ),
		array( 'Prof. Demo Name 11', 'Lecturer in Commerce', 'Commerce', 'M.Com., NET', '7 years', 0 ),
		array( 'Prof. Demo Name 12', 'Lecturer in Education', 'Education', 'M.A. (Education), B.Ed.', '15 years', 0 ),
		array( 'Prof. Demo Name 13', 'Lecturer in Sanskrit', 'Sanskrit', 'M.A. (Sanskrit), Acharya', '19 years', 0 ),
		array( 'Prof. Demo Name 14', 'Lecturer in Hindi', 'Hindi', 'M.A. (Hindi), NET', '6 years', 0 ),
		array( 'Prof. Demo Name 15', 'Lecturer in Philosophy', 'Philosophy', 'M.A. (Philosophy)', '12 years', 0 ),
		array( 'Prof. Demo Name 16', 'Lecturer in Computer Science', 'Computer Science', 'MCA, M.Tech (CSE)', '5 years', 0 ),
		array( 'Prof. Demo Name 17', 'Physical Education Teacher', 'Physical Education', 'M.P.Ed.', '17 years', 0 ),
		array( 'Prof. Demo Name 18', 'Librarian', 'Library', 'M.Lib.I.Sc., NET', '20 years', 0 ),
		array( 'Prof. Demo Name 19', 'Lecturer in Geography', 'Geography', 'M.A. (Geography)', '4 years', 0 ),
		array( 'Prof. Demo Name 20', 'Lecturer in Statistics', 'Statistics', 'M.Sc. (Statistics)', '6 years', 0 ),
	);
	foreach ( $faculty as $i => $f ) {
		$img = 'faculty' . ( ( $i % 7 ) + 1 ) . '.svg';
		kc_import_cpt( 'kc_faculty', $f[0], array(
			'kc_designation' => $f[1], 'kc_qualification' => $f[3], 'kc_experience' => $f[4],
			'kc_email' => 'faculty' . ( $i + 1 ) . '@katapalicollege.edu.in', 'kc_phone' => '+91 98765 4' . ( 3220 + $i ),
			'kc_on_slider' => $f[5], 'kc_order' => $i + 1,
		), array( 'kc_department' => $f[2] ), $img );
	}

	/* ---------- notices ---------- */
	$notices = array(
		array( 'Semester Exam Routine Released (Sem-I, III & V)', 'Examination', '<p>The examination routine for <strong>Semester I, III and V</strong> under Sambalpur University CBCS pattern has been released. Examinations will commence from <strong>15 November 2026</strong> and continue till <strong>05 December 2026</strong>.</p><ul><li>Reporting time at examination hall: 9:30 AM</li><li>Admit cards will be distributed from 05 November 2026 at the college office counter.</li><li>Use of mobile phones inside the examination hall is strictly prohibited.</li></ul>', '2026-11-30', 1, 'exam-routine.pdf' ),
		array( 'Admission Notice 2026-27 — +3 First Year', 'Admission', '<p>Applications are invited from eligible candidates for admission into <strong>+3 First Year Degree (Arts / Science / Commerce)</strong> for the academic session 2026-27 through SAMS, Government of Odisha.</p><ul><li>Start of online application: 01 June 2026</li><li>Last date of application: 25 June 2026</li><li>Classes commence: 01 August 2026</li></ul>', '2026-10-15', 1, 'notice-demo.pdf' ),
		array( 'Holiday Notice — Ganesh Puja & Nuakhai', 'General', '<p>All students and staff are hereby informed that the college will remain <strong>closed from 14 September 2026 to 18 September 2026</strong> on account of Ganesh Puja and the Nuakhai festival.</p><p>Regular classes will resume on 19 September 2026.</p>', '2026-09-20', 0, '' ),
		array( 'Post Matric Scholarship — Last Date Extended', 'Scholarship', '<p>The last date for online submission of <strong>Post Matric Scholarship</strong> applications for SC / ST / SEBC students has been extended to <strong>30 September 2026</strong>.</p>', '2026-09-30', 0, 'scholarship-form.pdf' ),
		array( 'Annual Sports Meet 2026 — Registration Open', 'Event', '<p>The <strong>Annual Athletic Meet 2026</strong> will be held on 12 and 13 December 2026 at the college play ground. Registration is open for athletics, football, volleyball, kabaddi and indoor games at the Physical Education Department.</p>', '2026-10-05', 0, '' ),
		array( 'Library Book Return Notice for Final Year Students', 'General', '<p>All students of the 6th semester are directed to return the books borrowed from the central library and obtain the <strong>No Dues Certificate</strong> from the Librarian before applying for the College Leaving Certificate.</p>', '2026-09-15', 0, '' ),
	);
	foreach ( $notices as $n ) {
		$file_url = $n[5] ? wp_get_attachment_url( kc_import_image( $n[5] ) ) : '';
		kc_import_cpt( 'kc_notice', $n[0], array( 'kc_expiry' => $n[3], 'kc_is_new' => $n[4], 'kc_file_url' => $file_url ), array( 'kc_notice_cat' => $n[1] ), '', $n[2] );
	}

	/* ---------- recruitment ---------- */
	$recruitment = array(
		array( 'Guest Faculty – Department of Odia', 'Odia', 'Guest Faculty', 2, 'Rs. 15,000/- per month (consolidated)', 'M.A. in Odia with minimum 55% marks. NET / SLET / Ph.D. holders preferred.', '2026-09-20', 'Open', '<p>Applications are invited for engagement as <strong>Guest Faculty in Odia</strong> purely on a temporary basis for the academic session 2026-27. Walk-in interview on 25 September 2026 at 11:00 AM in the Principal\'s chamber.</p>' ),
		array( 'Assistant Professor – Political Science', 'Political Science', 'Contractual', 1, 'Rs. 25,000/- per month (consolidated)', 'M.A. in Political Science with 55% marks and NET/SLET qualified.', '2026-09-15', 'Open', '<p>The college invites applications for the post of <strong>Assistant Professor in Political Science</strong> on a contractual basis for one academic year.</p>' ),
		array( 'Laboratory Attendant – Physics & Chemistry', 'Science', 'Contractual', 1, 'Rs. 9,500/- per month', '+2 Science pass with basic knowledge of laboratory handling.', '2026-07-25', 'Closed', '<p>Engagement of one <strong>Laboratory Attendant</strong> for the Physics and Chemistry laboratories.</p>' ),
	);
	foreach ( $recruitment as $r ) {
		kc_import_cpt( 'kc_recruitment', $r[0], array(
			'kc_department' => $r[1], 'kc_job_type' => $r[2], 'kc_vacancies' => $r[3], 'kc_salary' => $r[4],
			'kc_qualification' => $r[5], 'kc_last_date' => $r[6], 'kc_status' => $r[7], 'kc_file_url' => wp_get_attachment_url( kc_import_image( 'recruitment-notice.pdf' ) ),
		), array(), '', $r[8] );
	}

	/* ---------- tenders ---------- */
	$tenders = array(
		array( 'KPC/TND/2026/07', 'Supply of Laboratory Equipment for Physics & Chemistry Departments', '2026-09-18', '2026-09-20', 'Rs. 15,000/-', 'Rs. 6,50,000/- (approx.)', 'Open', '<p>Sealed tenders are invited from registered suppliers for the <strong>supply and installation of laboratory equipment</strong> for the Physics and Chemistry departments.</p>' ),
		array( 'KPC/TND/2026/06', 'Annual Maintenance Contract for Campus Housekeeping & Sanitation', '2026-08-05', '2026-08-08', 'Rs. 10,000/-', 'Rs. 3,20,000/- per annum', 'Closed', '<p>Quotations were invited for the <strong>annual housekeeping and sanitation contract</strong> for the period 2026-27. The tender has been finalised.</p>' ),
	);
	foreach ( $tenders as $t ) {
		kc_import_cpt( 'kc_tender', $t[1], array(
			'kc_tender_id' => $t[0], 'kc_last_date' => $t[2], 'kc_open_date' => $t[3], 'kc_emd' => $t[4],
			'kc_value' => $t[5], 'kc_status' => $t[6], 'kc_file_url' => wp_get_attachment_url( kc_import_image( 'tender-doc.pdf' ) ),
		), array(), '', $t[7] );
	}

	/* ---------- gallery ---------- */
	$gallery = array(
		array( 'Annual Function 2025', 'Annual Function' ), array( 'Independence Day Celebration', 'Events' ),
		array( 'Science Exhibition', 'Events' ), array( 'Annual Sports Meet', 'Sports' ),
		array( 'New Library Wing', 'Campus' ), array( 'Campus Front View', 'Campus' ),
		array( 'NSS Tree Plantation Drive', 'Events' ), array( 'Cultural Night', 'Annual Function' ),
		array( "Freshers' Welcome", 'Annual Function' ), array( 'Blood Donation Camp', 'Events' ),
		array( 'Republic Day Parade', 'Events' ), array( 'Computer Laboratory', 'Campus' ),
		array( 'Inter-College Football Tournament', 'Sports' ), array( 'Seminar Hall', 'Campus' ),
		array( 'Botanical Garden', 'Campus' ), array( 'Convocation Ceremony', 'Annual Function' ),
	);
	foreach ( $gallery as $i => $g ) {
		kc_import_cpt( 'kc_gallery', $g[0], array( 'kc_order' => $i + 1, 'kc_featured' => $i < 8 ? 1 : 0 ), array( 'kc_gallery_cat' => $g[1] ), 'gallery' . ( $i + 1 ) . '.svg' );
	}

	/* ---------- downloads ---------- */
	$downloads = array(
		array( 'College Prospectus 2026-27', 'Prospectus', '1.2 MB', 'prospectus.pdf' ),
		array( 'Admission Application Form', 'Forms', '240 KB', 'admission-form.pdf' ),
		array( 'Scholarship Application Form', 'Forms', '180 KB', 'scholarship-form.pdf' ),
		array( 'Transfer Certificate (CLC) Form', 'Forms', '150 KB', 'tc-form.pdf' ),
		array( '+3 Arts Syllabus (CBCS)', 'Syllabus', '860 KB', 'syllabus-arts.pdf' ),
		array( '+3 Science Syllabus (CBCS)', 'Syllabus', '910 KB', 'syllabus-science.pdf' ),
		array( '+3 Commerce Syllabus (CBCS)', 'Syllabus', '780 KB', 'syllabus-commerce.pdf' ),
		array( 'Academic Calendar 2026-27', 'Circulars', '320 KB', 'academic-calendar.pdf' ),
		array( 'Semester Examination Routine', 'Circulars', '210 KB', 'exam-routine.pdf' ),
		array( 'Anti-Ragging Undertaking Circular', 'Circulars', '160 KB', 'notice-demo.pdf' ),
	);
	foreach ( $downloads as $d ) {
		kc_import_cpt( 'kc_download', $d[0], array(
			'kc_category' => $d[1], 'kc_file_size' => $d[2], 'kc_file_url' => wp_get_attachment_url( kc_import_image( $d[3] ) ),
		) );
	}

	/* ---------- resources / useful links (footer columns) ---------- */
	$links = array(
		'Resources' => array(
			array( 'SWAYAM', 'https://swayam.gov.in/' ),
			array( 'e-Gyan Kosh', 'https://egyankosh.ac.in/' ),
			array( 'Shodhganga', 'https://shodhganga.inflibnet.ac.in/' ),
			array( 'Shodh Sindhu', 'https://ess.inflibnet.ac.in/' ),
			array( 'National Digital Library', 'https://ndl.iitkgp.ac.in/' ),
			array( 'CPET', '#' ),
			array( 'CUET', 'https://cuet.samarth.ac.in/' ),
		),
		'Useful Links' => array(
			array( 'NAAC', 'https://www.naac.gov.in/' ),
			array( 'UGC', 'https://www.ugc.gov.in/' ),
			array( 'DHE Odisha', 'https://dhe.odisha.gov.in/' ),
			array( 'AISHE', 'https://aishe.gov.in/' ),
			array( 'National Scholarship Portal', 'https://scholarships.gov.in/' ),
			array( 'Bargarh District Website', 'https://bargarh.nic.in/' ),
		),
	);
	foreach ( $links as $group => $items ) {
		foreach ( $items as $i => $item ) {
			kc_import_cpt( 'kc_link', $item[0], array( 'kc_url' => $item[1], 'kc_order' => $i + 1 ), array( 'kc_link_group' => $group ) );
		}
	}

	/* ---------- organisation logos (strip shown above the footer) ----------
	   Demo placeholder badges only - swap each Featured Image for the real
	   official logo you are authorised to use before going live. */
	$org_logos = array(
		array( 'Ministry of Education', 'org-moe.svg', 'https://www.education.gov.in/' ),
		array( 'Election Commission of India', 'org-eci.svg', 'https://eci.gov.in/' ),
		array( 'OPSC Odisha', 'org-opsc.svg', 'https://opsc.gov.in/' ),
		array( 'SSB Odisha', 'org-ssb.svg', '#' ),
		array( 'Meri Sarkar', 'org-merisarkar.svg', '#' ),
		array( 'Swachh Bharat Abhiyan', 'org-swachh.svg', 'https://swachhbharatmission.gov.in/' ),
		array( 'G20 Summit India', 'org-g20.svg', 'https://www.g20.in/' ),
		array( 'Azadi Ka Amrit Mahotsav', 'org-amrit.svg', 'https://amritmahotsav.nic.in/' ),
		array( 'Digital India', 'org-digitalindia.svg', 'https://www.digitalindia.gov.in/' ),
		array( 'UGC', 'org-ugc.svg', 'https://www.ugc.gov.in/' ),
		array( 'UGC NET', 'org-ugcnet.svg', 'https://ugcnet.nta.nic.in/' ),
	);
	foreach ( $org_logos as $i => $o ) {
		kc_import_cpt( 'kc_org_logo', $o[0], array( 'kc_url' => $o[2], 'kc_order' => $i + 1 ), array(), $o[1] );
	}

	/* ---------- pages ---------- */
	$pages = kc_demo_page_content();
	$page_ids = array();
	foreach ( $pages as $slug => $p ) {
		$page_ids[ $slug ] = kc_import_page( $p[0], $slug, $p[1] );
	}

	/* ---------- navigation menu ---------- */
	kc_import_menu( $page_ids );

	update_option( 'kc_demo_imported', 1 );
	return true;
}

function kc_import_menu( $page_ids ) {
	$menu_name = 'Primary Menu';
	$existing = wp_get_nav_menu_object( $menu_name );
	$menu_id = $existing ? $existing->term_id : wp_create_nav_menu( $menu_name );
	if ( is_wp_error( $menu_id ) ) return;

	// clear existing items so re-running the importer doesn't duplicate the menu
	$items = wp_get_nav_menu_items( $menu_id );
	if ( $items ) foreach ( $items as $it ) wp_delete_post( $it->ID, true );

	$add = function ( $title, $url, $parent = 0 ) use ( $menu_id ) {
		return wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title' => $title, 'menu-item-url' => $url, 'menu-item-status' => 'publish',
			'menu-item-parent-id' => $parent,
		) );
	};

	$add( 'Home', home_url( '/' ) );

	$about = $add( 'About Us', get_permalink( $page_ids['about-us'] ) );
	$add( 'About the College', get_permalink( $page_ids['about-us'] ) . '#about-college', $about );
	$add( 'Vision & Mission', get_permalink( $page_ids['about-us'] ) . '#vision-mission', $about );
	$add( 'Governing Body', get_permalink( $page_ids['about-us'] ) . '#governing-body', $about );
	$add( "Principal's Desk", get_permalink( $page_ids['about-us'] ) . '#principal-desk', $about );

	$acad = $add( 'Academics', get_permalink( $page_ids['academics'] ) );
	$add( 'Departments', get_permalink( $page_ids['academics'] ) . '#departments', $acad );
	$add( 'Courses Offered', get_permalink( $page_ids['academics'] ) . '#courses', $acad );
	$add( 'Academic Calendar', get_permalink( $page_ids['academics'] ) . '#calendar', $acad );

	$adm = $add( 'Admissions', get_permalink( $page_ids['admissions'] ) );
	$add( 'Admission Process', get_permalink( $page_ids['admissions'] ) . '#process', $adm );
	$add( 'Eligibility Criteria', get_permalink( $page_ids['admissions'] ) . '#eligibility', $adm );
	$add( 'Fee Structure', get_permalink( $page_ids['admissions'] ) . '#fees', $adm );

	$fac = $add( 'Faculty', get_post_type_archive_link( 'kc_faculty' ) );

	$add( 'Notices', get_post_type_archive_link( 'kc_notice' ) );
	$add( 'Recruitment', get_post_type_archive_link( 'kc_recruitment' ) );
	$add( 'Tenders', get_post_type_archive_link( 'kc_tender' ) );

	$exam = $add( 'Examination', get_permalink( $page_ids['examination'] ) );
	$add( 'Exam Routine', get_permalink( $page_ids['examination'] ) . '#routine', $exam );
	$add( 'Results', get_permalink( $page_ids['examination'] ) . '#results', $exam );
	$add( 'Rules & Regulations', get_permalink( $page_ids['examination'] ) . '#rules', $exam );

	$sc = $add( 'Student Corner', get_permalink( $page_ids['student-corner'] ) );
	$add( 'Scholarships', get_permalink( $page_ids['student-corner'] ) . '#scholarships', $sc );
	$add( "Students' Union", get_permalink( $page_ids['student-corner'] ) . '#union', $sc );
	$add( 'Sports & NCC/NSS', get_permalink( $page_ids['student-corner'] ) . '#sports', $sc );
	$add( 'Library', get_permalink( $page_ids['student-corner'] ) . '#library', $sc );

	$gal = $add( 'Gallery', get_post_type_archive_link( 'kc_gallery' ) );

	$al = $add( 'Alumni', get_permalink( $page_ids['alumni'] ) );
	$add( 'Alumni Association', get_permalink( $page_ids['alumni'] ) . '#association', $al );
	$add( 'Notable Alumni', get_permalink( $page_ids['alumni'] ) . '#notable', $al );

	$add( 'Downloads', get_post_type_archive_link( 'kc_download' ) );
	$add( 'Contact Us', get_permalink( $page_ids['contact-us'] ) );

	set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu_id, 'footer' => $menu_id ) );
}

function kc_demo_page_content() {
	$pages = array();

	$pages['about-us'] = array( 'About Us',
		'<div id="about-college"><h3>About the College</h3>' .
		'<p><strong>KATAPALI +3 COLLEGE, KATAPALI</strong> was established in the year <strong>1985</strong> by the sustained effort of the educated youth, farmers and philanthropists of the Katapali region, with the singular objective of bringing higher education within reach of the rural boys and girls of the Bijepur area of Bargarh district.</p>' .
		'<p>Today the institution runs <strong>+3 Arts, Science and Commerce</strong> streams under the Choice Based Credit System of Sambalpur University, with more than <strong>1,200 students</strong> on its rolls and a faculty strength of <strong>42</strong>. The campus, spread over nearly six acres, houses an academic block, well-equipped laboratories, a central library holding over 18,000 volumes, a seminar hall and a large playground.</p></div>' .
		'<div id="vision-mission"><h3>Vision & Mission</h3>' .
		'<p><strong>Vision:</strong> To emerge as a leading rural centre of higher learning in western Odisha that transforms first-generation learners into knowledgeable, skilled, self-reliant and socially responsible citizens.</p>' .
		'<p><strong>Mission:</strong> To provide affordable, quality higher education; promote academic excellence through effective teaching and evaluation; develop discipline and social awareness; and strengthen employability through skill development and career counselling.</p></div>' .
		'<div id="governing-body"><h3>Governing Body / College Committee</h3>' .
		'<table class="kc-table"><thead><tr><th>Name</th><th>Designation</th><th>Category</th></tr></thead><tbody>' .
		'<tr><td>Sri Demo Name</td><td>President</td><td>Nominee of the Management</td></tr>' .
		'<tr><td>Dr. Demo Name</td><td>Secretary / Member Convener</td><td>Principal (Ex-Officio)</td></tr>' .
		'<tr><td>Sri Demo Name</td><td>Member</td><td>Government Nominee</td></tr>' .
		'<tr><td>Prof. Demo Name 3</td><td>Member</td><td>Teaching Staff Representative</td></tr>' .
		'</tbody></table></div>' .
		'<div id="principal-desk"><h3>Principal\'s Desk</h3>' .
		'<p>Education, in its truest sense, is the drawing out of the best that lies within a learner. At Katapali +3 College we have consciously built an academic culture in which a student from the remotest village of Bijepur block feels equally at home and equally capable.</p>' .
		'<p><strong>Dr. Demo Name</strong><br>Principal, Katapali +3 College, Katapali</p></div>'
	);

	$pages['academics'] = array( 'Academics',
		'<div id="departments"><h3>Departments</h3>' .
		'<p>The college offers instruction through ten teaching departments across the Arts, Science and Commerce faculties: Odia, English, History, Political Science, Economics, Mathematics, Physics, Chemistry, Botany and Zoology.</p></div>' .
		'<div id="courses"><h3>Courses Offered</h3>' .
		'<table class="kc-table"><thead><tr><th>Course</th><th>Duration</th><th>Honours Subjects</th></tr></thead><tbody>' .
		'<tr><td><strong>+3 Arts</strong></td><td>3 Years / 6 Semesters</td><td>Odia, English, History, Political Science, Economics</td></tr>' .
		'<tr><td><strong>+3 Science</strong></td><td>3 Years / 6 Semesters</td><td>Physics, Chemistry, Mathematics, Botany, Zoology</td></tr>' .
		'<tr><td><strong>+3 Commerce</strong></td><td>3 Years / 6 Semesters</td><td>Accounting & Finance, Marketing Management</td></tr>' .
		'</tbody></table></div>' .
		'<div id="calendar"><h3>Academic Calendar 2026-27</h3>' .
		'<table class="kc-table"><thead><tr><th>Activity</th><th>Date</th></tr></thead><tbody>' .
		'<tr><td>Commencement of classes</td><td>01 August 2026</td></tr>' .
		'<tr><td>Puja Vacation</td><td>14–25 October 2026</td></tr>' .
		'<tr><td>Odd Semester Examination</td><td>15 Nov – 05 Dec 2026</td></tr>' .
		'<tr><td>Annual Function</td><td>18 February 2027</td></tr>' .
		'</tbody></table></div>'
	);

	$pages['admissions'] = array( 'Admissions',
		'<div id="process"><h3>Admission Process</h3>' .
		'<p>Admission into +3 First Year is conducted entirely online through the <strong>Student Academic Management System (SAMS), Government of Odisha</strong>. Register online, fill the Common Application Form, upload documents, pay the fee, check the merit list, and report for document verification.</p></div>' .
		'<div id="eligibility"><h3>Eligibility Criteria</h3>' .
		'<table class="kc-table"><thead><tr><th>Course</th><th>Minimum Qualification</th></tr></thead><tbody>' .
		'<tr><td>+3 Arts</td><td>Pass in +2 (any stream)</td></tr>' .
		'<tr><td>+3 Science</td><td>Pass in +2 Science with PCM/B, min. 45% for Honours</td></tr>' .
		'<tr><td>+3 Commerce</td><td>Pass in +2 Commerce, min. 45% for Honours</td></tr>' .
		'</tbody></table></div>' .
		'<div id="fees"><h3>Fee Structure 2026-27 (Demo)</h3>' .
		'<table class="kc-table"><thead><tr><th>Particulars</th><th>+3 Arts</th><th>+3 Science</th><th>+3 Commerce</th></tr></thead><tbody>' .
		'<tr><td>Tuition Fee (p.a.)</td><td>Rs. 2,400</td><td>Rs. 3,600</td><td>Rs. 2,800</td></tr>' .
		'<tr><td>Laboratory Fee</td><td>—</td><td>Rs. 1,800</td><td>Rs. 400</td></tr>' .
		'<tr><td><strong>Total (First Year)</strong></td><td><strong>Rs. 4,500</strong></td><td><strong>Rs. 7,700</strong></td><td><strong>Rs. 5,300</strong></td></tr>' .
		'</tbody></table><p class="note">Tuition fee is fully exempted for SC/ST students and for girl students up to +3 level as per Government of Odisha policy.</p></div>'
	);

	$pages['examination'] = array( 'Examination',
		'<div id="routine"><h3>Examination Routine</h3>' .
		'<p>The odd semester examination will be conducted from <strong>15 November to 05 December 2026</strong>. See the Notices section for the detailed subject-wise routine and downloadable PDF.</p></div>' .
		'<div id="results"><h3>Results</h3>' .
		'<table class="kc-table"><thead><tr><th>Examination</th><th>Appeared</th><th>Passed</th><th>Pass %</th></tr></thead><tbody>' .
		'<tr><td>+3 6th Semester (Arts)</td><td>212</td><td>197</td><td>92.9%</td></tr>' .
		'<tr><td>+3 6th Semester (Science)</td><td>104</td><td>99</td><td>95.2%</td></tr>' .
		'<tr><td>+3 6th Semester (Commerce)</td><td>48</td><td>44</td><td>91.7%</td></tr>' .
		'</tbody></table></div>' .
		'<div id="rules"><h3>Rules & Regulations</h3>' .
		'<ol><li>A student must have at least <strong>75% attendance</strong> to be eligible to appear in the University examination.</li>' .
		'<li>Mobile phones and programmable calculators are strictly prohibited inside the examination hall.</li>' .
		'<li>Ragging in any form is a criminal offence punishable under the Odisha Education Act.</li></ol></div>'
	);

	$pages['student-corner'] = array( 'Student Corner',
		'<div id="scholarships"><h3>Scholarships</h3>' .
		'<table class="kc-table"><thead><tr><th>Scheme</th><th>Amount (approx.)</th></tr></thead><tbody>' .
		'<tr><td>Post Matric Scholarship (SC/ST)</td><td>Rs. 3,000 – 12,000/year</td></tr>' .
		'<tr><td>Medhabruti (Merit Scholarship)</td><td>Rs. 10,000/year</td></tr>' .
		'<tr><td>Students Aid Fund (College)</td><td>Rs. 2,000 (one time)</td></tr>' .
		'</tbody></table></div>' .
		'<div id="union"><h3>Students\' Union</h3>' .
		'<p>The Students\' Union is elected every academic session in accordance with the Lyngdoh Committee recommendations. It organises the annual function, sports meet, college magazine and debate competitions.</p></div>' .
		'<div id="sports"><h3>Sports & NCC/NSS</h3>' .
		'<p>The college maintains a full-size playground and runs two NSS units with 200 volunteers, plus an NCC (Army Wing) unit. Regular cleanliness drives, tree plantation, blood donation camps and voter awareness drives are conducted every year.</p></div>' .
		'<div id="library"><h3>Central Library</h3>' .
		'<p>18,450 volumes, 24 journal titles, a reading room for 80 students, and N-LIST e-resource access, open 10:00 AM – 5:00 PM on all working days.</p></div>'
	);

	$pages['alumni'] = array( 'Alumni',
		'<div id="association"><h3>Alumni Association</h3>' .
		'<p>The Katapali +3 College Alumni Association was registered in 2005 and brings together former students who now serve in teaching, administration, medicine, engineering, defence, agriculture and business. The annual alumni meet is held on the first Sunday of January.</p></div>' .
		'<div id="notable"><h3>Notable Alumni</h3>' .
		'<table class="kc-table"><thead><tr><th>Name</th><th>Batch</th><th>Present Position (Demo)</th></tr></thead><tbody>' .
		'<tr><td>Demo Alumnus 1</td><td>1990</td><td>Officer, Odisha Administrative Service</td></tr>' .
		'<tr><td>Demo Alumnus 2</td><td>1994</td><td>Professor, State University</td></tr>' .
		'<tr><td>Demo Alumnus 3</td><td>1999</td><td>Medical Officer, Government Hospital</td></tr>' .
		'</tbody></table></div>'
	);

	$pages['contact-us'] = array( 'Contact Us',
		'<p>The college office remains open on all working days from 10:00 AM to 5:00 PM. For admission related queries please contact the Admission Cell during office hours.</p>' .
		'<table class="kc-table"><tbody>' .
		'<tr><th>Address</th><td>AT/PO - KATAPALI, VIA - BIJEPUR, DISTRICT - BARGARH, ODISHA - 768032</td></tr>' .
		'<tr><th>Phone</th><td>+91 98765 43210</td></tr>' .
		'<tr><th>Email</th><td>info@katapalicollege.edu.in</td></tr>' .
		'</tbody></table>'
	);

	return $pages;
}

function kc_importer_page() {
	if ( isset( $_POST['kc_import_nonce'] ) && wp_verify_nonce( $_POST['kc_import_nonce'], 'kc_run_import' ) && current_user_can( 'manage_options' ) ) {
		kc_run_demo_import();
		echo '<div class="notice notice-success"><p><strong>Demo content imported.</strong> Hero slides, faculty, notices, recruitment, tenders, gallery, downloads, pages and the navigation menu have been created. Visit your <a href="' . esc_url( home_url( '/' ) ) . '" target="_blank">homepage</a> to see it.</p></div>';
	}
	$already = get_option( 'kc_demo_imported' );
	?>
	<div class="wrap">
		<h1>Demo Content Importer</h1>
		<p>Click the button below to fill your site with the KATAPALI +3 COLLEGE demo content: hero slides, 20 faculty members, notices, recruitment postings, tenders, a 16-photo gallery, 10 downloadable documents, 13 resource/useful links (shown in the footer), 11 organisation logos (shown in the strip above the footer), 8 content pages, and a ready-made navigation menu.</p>
		<?php if ( $already ) : ?>
			<div class="notice notice-warning"><p>Demo content has already been imported once. Running it again will add a fresh copy of every item (it will not delete or duplicate-check existing posts other than the menu, which is rebuilt cleanly).</p></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'kc_run_import', 'kc_import_nonce' ); ?>
			<p><button type="submit" class="button button-primary button-hero">
				<?php echo $already ? 'Re-Import Demo Content' : 'Import Demo Content Now'; ?>
			</button></p>
		</form>
		<p style="color:#646970;">Once imported, edit everything from <strong>Katapali College</strong> in the left menu, the <strong>Customizer</strong> (Appearance &rarr; Customize) for colours/logo/college info, and <strong>Appearance &rarr; Menus</strong> for navigation.</p>
	</div>
	<?php
}
