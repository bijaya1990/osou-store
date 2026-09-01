<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function kc_banner( $title, $crumb = '' ) {
	if ( ! $crumb ) $crumb = $title;
	echo '<section class="page-banner"><div class="container"><h1>' . esc_html( $title ) . '</h1>';
	echo '<div class="breadcrumb"><a href="' . esc_url( home_url( '/' ) ) . '">Home</a> &nbsp;/&nbsp; ' . esc_html( $crumb ) . '</div></div></section>';
}

function kc_notice_card( $post_id ) {
	$cats = get_the_terms( $post_id, 'kc_notice_cat' );
	$cat  = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : 'General';
	$is_new = get_post_meta( $post_id, 'kc_is_new', true ) === '1';
	?>
	<div class="card">
		<span class="tag"><?php echo esc_html( $cat ); ?></span>
		<?php if ( $is_new ) : ?><span class="tag tag-new">New</span><?php endif; ?>
		<h4><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h4>
		<div class="cdate"><i class="fa-regular fa-calendar"></i> <?php echo esc_html( get_the_date( 'd M Y', $post_id ) ); ?></div>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 20 ) ); ?></p>
		<a class="read-more" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">Read More <i class="fa-solid fa-arrow-right"></i></a>
	</div>
	<?php
}

function kc_recruitment_card( $post_id ) {
	$dept   = get_post_meta( $post_id, 'kc_department', true );
	$type   = get_post_meta( $post_id, 'kc_job_type', true );
	$last   = get_post_meta( $post_id, 'kc_last_date', true );
	$status = get_post_meta( $post_id, 'kc_status', true );
	?>
	<div class="card">
		<span class="tag <?php echo $status === 'Closed' ? 'tag-closed' : 'tag-open'; ?>"><?php echo esc_html( $status ? $status : 'Open' ); ?></span>
		<h4><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h4>
		<p style="color:var(--muted);font-size:.85rem;margin:8px 0;"><?php echo esc_html( $dept ); ?> &bull; <?php echo esc_html( $type ); ?></p>
		<div class="cdate"><i class="fa-regular fa-clock"></i> Last date: <?php echo esc_html( $last ); ?></div>
		<a class="read-more" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">View Details <i class="fa-solid fa-arrow-right"></i></a>
	</div>
	<?php
}

function kc_tender_card( $post_id ) {
	$tid    = get_post_meta( $post_id, 'kc_tender_id', true );
	$last   = get_post_meta( $post_id, 'kc_last_date', true );
	$status = get_post_meta( $post_id, 'kc_status', true );
	?>
	<div class="card">
		<span class="tag <?php echo $status === 'Closed' ? 'tag-closed' : 'tag-open'; ?>"><?php echo esc_html( $status ? $status : 'Open' ); ?></span>
		<h4><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h4>
		<p style="color:var(--muted);font-size:.85rem;margin:8px 0;">Tender ID: <?php echo esc_html( $tid ); ?></p>
		<div class="cdate"><i class="fa-regular fa-clock"></i> Last date: <?php echo esc_html( $last ); ?></div>
		<a class="read-more" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">View Details <i class="fa-solid fa-arrow-right"></i></a>
	</div>
	<?php
}

function kc_faculty_card( $post_id ) {
	$desig = get_post_meta( $post_id, 'kc_designation', true );
	$img = get_the_post_thumbnail_url( $post_id, 'medium' );
	if ( ! $img ) $img = KC_URI . '/assets/demo-images/principal.svg';
	?>
	<div class="faculty-card">
		<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
		<h4><?php echo esc_html( get_the_title( $post_id ) ); ?></h4>
		<div class="desig"><?php echo esc_html( $desig ); ?></div>
	</div>
	<?php
}

function kc_gallery_item( $post_id ) {
	$img = get_the_post_thumbnail_url( $post_id, 'large' );
	if ( ! $img ) return;
	$cats = get_the_terms( $post_id, 'kc_gallery_cat' );
	$cat  = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : 'Campus';
	?>
	<div class="gallery-item" data-cat="<?php echo esc_attr( $cat ); ?>" data-img="<?php echo esc_url( $img ); ?>" data-cap="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
		<img src="<?php echo esc_url( $img ); ?>" loading="lazy" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
		<div class="gi-cap"><?php echo esc_html( get_the_title( $post_id ) ); ?></div>
	</div>
	<?php
}

function kc_footer_address() {
	echo nl2br( esc_html( get_theme_mod( 'kc_address', 'AT/PO - KATAPALI, VIA - BIJEPUR, DISTRICT - BARGARH, ODISHA' ) ) ) . ' - ' . esc_html( get_theme_mod( 'kc_pin', '768032' ) );
}

/* Renders <li> items for a kc_link group (Resources / Useful Links). */
function kc_link_list( $group_name ) {
	$q = new WP_Query( array(
		'post_type' => 'kc_link', 'posts_per_page' => -1,
		'tax_query' => array( array( 'taxonomy' => 'kc_link_group', 'field' => 'name', 'terms' => $group_name ) ),
		'meta_key' => 'kc_order', 'orderby' => 'meta_value_num', 'order' => 'ASC',
	) );
	if ( ! $q->have_posts() ) { echo '<li class="empty-msg">No links added yet — add one under Katapali College &rarr; Links, group "' . esc_html( $group_name ) . '".</li>'; return; }
	while ( $q->have_posts() ) : $q->the_post();
		$url = get_post_meta( get_the_ID(), 'kc_url', true );
		echo '<li><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener"><i class="fa-solid fa-angles-right"></i> ' . esc_html( get_the_title() ) . '</a></li>';
	endwhile;
	wp_reset_postdata();
}

/* Renders the auto-scrolling organisation-logo strip shown just above
   the footer on every page (Ministry of Education, ECI, UGC, etc.). */
function kc_org_logo_strip() {
	$logos = get_posts( array( 'post_type' => 'kc_org_logo', 'posts_per_page' => -1, 'meta_key' => 'kc_order', 'orderby' => 'meta_value_num', 'order' => 'ASC' ) );
	if ( ! $logos ) return;
	$track = '';
	foreach ( $logos as $l ) {
		$img = get_the_post_thumbnail_url( $l->ID, 'medium' );
		if ( ! $img ) continue;
		$url = get_post_meta( $l->ID, 'kc_url', true );
		$item = '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( $l->post_title ) . '" title="' . esc_attr( $l->post_title ) . '" loading="lazy">';
		$track .= $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $item . '</a>' : '<span>' . $item . '</span>';
	}
	if ( ! $track ) return;
	echo '<div class="org-logo-strip"><div class="container"><div class="org-logo-track-wrap"><div class="org-logo-track">' . $track . $track . '</div></div></div></div>';
}
