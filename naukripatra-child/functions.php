<?php
/**
 * NaukriPatra — GeneratePress Child Theme
 * =====================================================================
 * v3.0 "Premium Corporate" — navy + gold corporate redesign,
 * government AND private jobs, dashboard-controlled design tokens,
 * dashboard-controlled ad system (image banner or network ad code).
 *
 * There is no tracking or phone-home code in this theme.
 * The site stays 100% under your control.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Design tokens (colours / fonts / letter sizes) — dashboard driven */
require_once get_stylesheet_directory() . '/inc/appearance.php';
/** Ad slots — dashboard driven, image banner or ad code per slot */
require_once get_stylesheet_directory() . '/inc/ads.php';
/** Power features: SEO schema, ticker, trending, related posts, security */
require_once get_stylesheet_directory() . '/inc/features.php';

/* =========================================================
 * 0. EASY SETTINGS
 * ======================================================= */

/** Social links */
function np_social_links() {
	return array(
		'whatsapp'  => 'https://whatsapp.com/channel/0029Vb86J5W0QearPaRwGA1E',
		'telegram'  => 'https://t.me/naukripatra',
		'facebook'  => '#',
		'youtube'   => '#',
		'playstore' => 'https://play.google.com/store/apps/details?id=in.naukripatra.in',
	);
}

/** The 6 main homepage sections (name => category slug) */
function np_main_sections() {
	return array(
		'Latest Jobs' => 'latest-jobs',
		'Admit Card'  => 'admit-card',
		'Result'      => 'result',
		'Answer Key'  => 'answer-key',
		'Syllabus'    => 'syllabus',
		'Admission'   => 'admission',
	);
}

/** All India + 28 States + 8 Union Territories */
function np_locations() {
	return array(
		'all-india' => 'All India',
		// 28 States
		'andhra-pradesh' => 'Andhra Pradesh', 'arunachal-pradesh' => 'Arunachal Pradesh',
		'assam' => 'Assam', 'bihar' => 'Bihar', 'chhattisgarh' => 'Chhattisgarh',
		'goa' => 'Goa', 'gujarat' => 'Gujarat', 'haryana' => 'Haryana',
		'himachal-pradesh' => 'Himachal Pradesh', 'jharkhand' => 'Jharkhand',
		'karnataka' => 'Karnataka', 'kerala' => 'Kerala', 'madhya-pradesh' => 'Madhya Pradesh',
		'maharashtra' => 'Maharashtra', 'manipur' => 'Manipur', 'meghalaya' => 'Meghalaya',
		'mizoram' => 'Mizoram', 'nagaland' => 'Nagaland', 'odisha' => 'Odisha',
		'punjab' => 'Punjab', 'rajasthan' => 'Rajasthan', 'sikkim' => 'Sikkim',
		'tamil-nadu' => 'Tamil Nadu', 'telangana' => 'Telangana', 'tripura' => 'Tripura',
		'uttar-pradesh' => 'Uttar Pradesh', 'uttarakhand' => 'Uttarakhand',
		'west-bengal' => 'West Bengal',
		// 8 Union Territories
		'andaman-nicobar' => 'Andaman & Nicobar', 'chandigarh' => 'Chandigarh',
		'dadra-nagar-haveli-daman-diu' => 'Dadra & Nagar Haveli and Daman & Diu',
		'delhi' => 'Delhi', 'jammu-kashmir' => 'Jammu & Kashmir', 'ladakh' => 'Ladakh',
		'lakshadweep' => 'Lakshadweep', 'puducherry' => 'Puducherry',
	);
}

/* =========================================================
 * 0B. JOB SECTOR — Government + Private
 * =======================================================
 * Stored as post meta `_np_job_sector` with a registered choice list,
 * which matches the existing meta-box pattern (qualification, last
 * date, employment type…) rather than introducing a second taxonomy
 * alongside the 43 auto-created categories.
 *
 * Posts published before v3.0 have no value saved. They are treated as
 * Government, because every one of them is a government listing — no
 * back-fill migration and no data rewrite is needed.
 */
function np_job_sectors() {
	return array(
		'government' => 'Government',
		'private'    => 'Private',
	);
}

/** A post's sector, always one of the registered keys. */
function np_get_sector( $post_id ) {
	$val = get_post_meta( $post_id, '_np_job_sector', true );
	return array_key_exists( $val, np_job_sectors() ) ? $val : 'government';
}

/** Badge markup for a job card. */
function np_sector_badge( $post_id ) {
	$sector = np_get_sector( $post_id );
	$labels = np_job_sectors();
	return '<span class="np-badge np-badge-' . esc_attr( $sector ) . '">'
		. esc_html( $labels[ $sector ] ) . '</span>';
}

/* =========================================================
 * 0D. JOB DEADLINES — active count + "ending soon"
 * =======================================================
 * `_np_last_date` is free text an editor types by hand, so it cannot
 * be compared or sorted in SQL. v3.1 mirrors it into a NUMERIC
 * companion meta field, `_np_last_date_ts`, written every time a post
 * is saved and back-filled in batches for older posts.
 *
 * ADDITIVE: `_np_last_date` itself is never modified, never read
 * differently and never removed. The app, the Job Details box and the
 * JobPosting schema all keep reading the original field exactly as
 * before. The mirror is an index, not a replacement.
 *
 * Parsing goes through np_schema_parse_date(), the same parser the
 * JobPosting schema uses, so the countdown on screen and validThrough
 * in the structured data can never disagree.
 *
 * Value stored:
 *   > 0  the deadline day's 00:00 timestamp
 *   0    a last date was typed but could not be parsed
 *   0    no last date was given at all
 * A post is ACTIVE when its deadline day has not passed yet, and a
 * post with no usable deadline (results, admit cards, answer keys)
 * counts as active too — it has no closing date to expire against.
 */

/** Midnight today, in the site's own timezone. */
function np_today_ts() {
	return (int) strtotime( current_time( 'Y-m-d' ) . ' 00:00:00' );
}

/** Write the numeric mirror of `_np_last_date` for one post. */
function np_sync_last_date_ts( $post_id ) {
	$raw = get_post_meta( $post_id, '_np_last_date', true );
	$ts  = np_schema_parse_date( $raw );
	update_post_meta( $post_id, '_np_last_date_ts', $ts ? (int) $ts : 0 );
	delete_transient( 'np_active_jobs' );
	return $ts ? (int) $ts : 0;
}

/**
 * Whole days until a post's deadline.
 * null = no usable deadline, 0 = closes today, negative = closed.
 */
function np_days_left( $post_id ) {
	$ts = (int) get_post_meta( $post_id, '_np_last_date_ts', true );
	if ( ! $ts ) {
		// Not mirrored yet (an old post the back-fill has not reached).
		$ts = np_sync_last_date_ts( $post_id );
		if ( ! $ts ) return null;
	}
	return (int) floor( ( $ts - np_today_ts() ) / DAY_IN_SECONDS );
}

/**
 * Real count of active listings — published jobs whose deadline has
 * not passed. Replaces the old wp_count_posts() total, which counted
 * every post ever published, expired ones included.
 * Cached for 15 minutes and cleared whenever a post is saved.
 */
function np_count_active_jobs() {
	$cached = get_transient( 'np_active_jobs' );
	if ( false !== $cached ) return (int) $cached;

	$q = new WP_Query( array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 1,
		'fields'              => 'ids',
		'ignore_sticky_posts' => true,
		'meta_query'          => array(
			'relation' => 'OR',
			// Deadline still ahead (the closing day itself counts).
			array( 'key' => '_np_last_date_ts', 'value' => np_today_ts(), 'compare' => '>=', 'type' => 'NUMERIC' ),
			// No usable deadline — nothing to expire against.
			array( 'key' => '_np_last_date_ts', 'value' => 0, 'compare' => '=', 'type' => 'NUMERIC' ),
			array( 'key' => '_np_last_date_ts', 'compare' => 'NOT EXISTS' ),
		),
	) );

	$count = (int) $q->found_posts;
	set_transient( 'np_active_jobs', $count, 15 * MINUTE_IN_SECONDS );
	return $count;
}

/** Jobs closing within the next $days days, soonest first. */
function np_ending_soon_query( $count = 6, $days = 15 ) {
	$today = np_today_ts();
	return new WP_Query( array(
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'meta_key'            => '_np_last_date_ts',
		'orderby'             => 'meta_value_num',
		'order'               => 'ASC',
		'meta_query'          => array(
			array(
				'key'     => '_np_last_date_ts',
				'value'   => array( $today, $today + ( (int) $days * DAY_IN_SECONDS ) ),
				'compare' => 'BETWEEN',
				'type'    => 'NUMERIC',
			),
		),
	) );
}

/** Short label for a deadline, e.g. "3 days left". */
function np_days_left_label( $days ) {
	if ( null === $days )  return 'No closing date';
	if ( $days < 0 )       return 'Closed';
	if ( 0 === $days )     return 'Closes today';
	if ( 1 === $days )     return '1 day left';
	return $days . ' days left';
}

/**
 * Back-fill the mirror for posts published before v3.1, 200 at a time
 * so a large site is never asked to do it all in one request.
 */
function np_backfill_last_date_ts() {
	if ( get_option( 'np_last_date_ts_done' ) ) return;

	$q = new WP_Query( array(
		'post_type'           => 'post',
		'post_status'         => 'any',
		'posts_per_page'      => 200,
		'fields'              => 'ids',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'meta_query'          => array(
			array( 'key' => '_np_last_date_ts', 'compare' => 'NOT EXISTS' ),
		),
	) );

	if ( empty( $q->posts ) ) {
		update_option( 'np_last_date_ts_done', 1 );
		delete_transient( 'np_active_jobs' );
		return;
	}
	foreach ( $q->posts as $pid ) {
		np_sync_last_date_ts( $pid );
	}
}
add_action( 'admin_init', 'np_backfill_last_date_ts' );
add_action( 'after_switch_theme', function () {
	delete_option( 'np_last_date_ts_done' );
	np_backfill_last_date_ts();
} );

/* =========================================================
 * 0B2. APPLY LINK — automatic detection
 * =======================================================
 * The sidebar "Apply Now" button was dead on every post published
 * before v3.0, because the _np_apply_url field did not exist then and
 * nobody has gone back to fill 1,000 old posts by hand.
 *
 * The official link is almost always already IN the post — the "Apply
 * Online" / "Official Website" row of the links table. So the theme
 * now reads it out of the content instead of demanding it be typed a
 * second time.
 *
 * Order of preference:
 *   1. The Apply / Official Link field in the Job Details box, if the
 *      editor filled it. An explicit choice always wins.
 *   2. The best external link found in the post content.
 *   3. Nothing — the button then scrolls to "How to Apply" rather
 *      than going nowhere, which is what it did before.
 *
 * The detected URL is cached in _np_apply_url_auto and recalculated
 * whenever the post is saved, so the content is parsed once, not on
 * every page view.
 */

/** Hosts that are never the official application site. */
function np_apply_link_blocklist() {
	return array(
		'facebook.com', 'fb.com', 'twitter.com', 'x.com', 'instagram.com',
		'youtube.com', 'youtu.be', 'whatsapp.com', 'wa.me', 't.me', 'telegram.me',
		'linkedin.com', 'pinterest.com', 'play.google.com', 'apps.apple.com',
		'google.com', 'blogspot.com', 'amazon.in', 'amzn.to',
	);
}

/**
 * Score one candidate link. Higher is better; 0 or less = reject.
 *
 * @param string $href Absolute URL.
 * @param string $text The anchor's visible text.
 * @param string $home_host The site's own host.
 */
function np_score_apply_link( $href, $text, $home_host ) {
	$host = strtolower( (string) wp_parse_url( $href, PHP_URL_HOST ) );
	if ( '' === $host ) return 0;

	// Our own site is never the official application site.
	if ( false !== strpos( $host, $home_host ) ) return 0;

	foreach ( np_apply_link_blocklist() as $bad ) {
		if ( $host === $bad || substr( $host, -strlen( '.' . $bad ) ) === '.' . $bad ) return 0;
	}

	$text  = strtolower( wp_strip_all_tags( $text ) );
	$score = 1;

	// What the link SAYS is the strongest signal.
	if ( preg_match( '/\bapply\s*(online|now|here)?\b/', $text ) )      $score += 60;
	if ( preg_match( '/\bofficial\s*(website|site|link)\b/', $text ) )  $score += 55;
	if ( preg_match( '/\b(registration|register|online\s*form)\b/', $text ) ) $score += 40;
	if ( preg_match( '/\blogin\b/', $text ) )                            $score += 15;

	// A notification or advertisement link is usually the PDF, not the form.
	if ( preg_match( '/\b(notification|advertisement|notice|syllabus|admit|result)\b/', $text ) ) $score -= 25;
	if ( preg_match( '/\.pdf($|\?)/i', $href ) )                         $score -= 35;

	// Indian government domains are very likely the real thing.
	if ( preg_match( '/\.(gov|nic)\.in$/', $host ) ) $score += 30;
	elseif ( preg_match( '/\.(ac|edu)\.in$/', $host ) ) $score += 15;

	return $score;
}

/** Best official link found inside a post's content, or '' if none. */
function np_detect_apply_url( $post_id ) {
	$content = get_post_field( 'post_content', $post_id );
	if ( ! $content ) return '';

	// NOTE: the raw content is parsed on purpose. Running it through
	// the_content would re-enter this theme's own content filter.
	if ( ! preg_match_all( '/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $content, $m, PREG_SET_ORDER ) ) {
		return '';
	}

	$home_host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	$home_host = preg_replace( '/^www\./', '', $home_host );

	$best = '';
	$best_score = 0;
	foreach ( $m as $hit ) {
		$href = trim( html_entity_decode( $hit[1], ENT_QUOTES ) );
		if ( 0 !== stripos( $href, 'http' ) ) continue; // skip #, mailto:, tel:, relative

		$score = np_score_apply_link( $href, $hit[2], $home_host );
		if ( $score > $best_score ) {
			$best_score = $score;
			$best       = $href;
		}
	}

	// A bare external link with no telling words is a weak guess, so
	// require at least one real signal before offering it as "Apply".
	return ( $best_score >= 15 ) ? esc_url_raw( $best ) : '';
}

/** Refresh the cached detection for one post. */
function np_sync_apply_url( $post_id ) {
	$found = np_detect_apply_url( $post_id );
	// '-' records "looked, found nothing", so it is not re-parsed forever.
	update_post_meta( $post_id, '_np_apply_url_auto', $found ? $found : '-' );
	return $found;
}

/**
 * The URL the Apply Now button should use. '' when there is none.
 */
function np_get_apply_url( $post_id ) {
	$manual = trim( (string) get_post_meta( $post_id, '_np_apply_url', true ) );
	if ( $manual ) return $manual;

	$auto = (string) get_post_meta( $post_id, '_np_apply_url_auto', true );
	if ( '-' === $auto ) return '';          // already searched, nothing there
	if ( $auto ) return $auto;

	return np_sync_apply_url( $post_id );    // first look for an older post
}

/* =========================================================
 * 0C. INLINE STROKE SVG ICONS (no emoji anywhere in the UI)
 * ======================================================= */
function np_icon( $name, $class = '' ) {
	$paths = array(
		'search'     => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
		'briefcase'  => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18"/>',
		'card'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 10h4M7 14h7"/>',
		'trophy'     => '<path d="M8 4h8v5a4 4 0 0 1-8 0V4zM8 6H5v1a3 3 0 0 0 3 3M16 6h3v1a3 3 0 0 1-3 3M10 17h4M9 21h6"/>',
		'key'        => '<circle cx="8" cy="12" r="4"/><path d="M12 12h9M18 12v3M15 12v2"/>',
		'book'       => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2V5zM19 19H6"/>',
		'cap'        => '<path d="M3 9l9-4 9 4-9 4-9-4zM7 11v4c0 1.5 2.4 3 5 3s5-1.5 5-3v-4"/>',
		'pin'        => '<path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
		'calendar'   => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
		'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'users'      => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M17 11a3 3 0 1 0 0-6M18 20a5.6 5.6 0 0 0-2-4.3"/>',
		'file'       => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z"/><path d="M14 3v5h5"/>',
		'image'      => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9.5" r="1.8"/><path d="M4 17l5-4 4 3 3-2 4 3"/>',
		'pen'        => '<path d="M4 20h4L19 9a2.5 2.5 0 0 0-3.5-3.5L4.5 16.5 4 20z"/>',
		'compress'   => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z"/><path d="M9 13h6M12 10v6"/>',
		'download'   => '<path d="M12 4v11M8 11l4 4 4-4M5 20h14"/>',
		'phone'      => '<rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M10.5 18h3"/>',
		'chat'       => '<path d="M21 12a8 8 0 1 1-3.3-6.4L21 4l-1 4.2A7.9 7.9 0 0 1 21 12z"/>',
		'send'       => '<path d="M21 4L3 11l7 3 3 7 8-17z"/>',
		'chevron'    => '<path d="M9 6l6 6-6 6"/>',
		'chevrondown'=> '<path d="M6 9l6 6 6-6"/>',
		'menu'       => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'      => '<path d="M6 6l12 12M18 6L6 18"/>',
		'arrowup'    => '<path d="M12 20V5M6 11l6-6 6 6"/>',
		'share'      => '<circle cx="6" cy="12" r="2.5"/><circle cx="17" cy="6" r="2.5"/><circle cx="17" cy="18" r="2.5"/><path d="M8.3 10.9l6.4-3.5M8.3 13.1l6.4 3.5"/>',
		'link'       => '<path d="M10 13a4 4 0 0 0 5.7 0l2.6-2.6a4 4 0 0 0-5.7-5.7L11 6.3"/><path d="M14 11a4 4 0 0 0-5.7 0l-2.6 2.6a4 4 0 0 0 5.7 5.7L13 17.7"/>',
		'home'       => '<path d="M3 11l9-7 9 7"/><path d="M6 10v10h12V10"/>',
		'bolt'       => '<path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z"/>',
		'check'      => '<path d="M4 12.5l5 5L20 6.5"/>',
		'building'   => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2"/>',
		'globe'      => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.8 2.5 15.2 0 18-2.5-2.8-2.5-15.2 0-18z"/>',
		'filter'     => '<path d="M3 5h18l-7 8v6l-4 2v-8L3 5z"/>',
		'rupee'      => '<path d="M7 4h10M7 8h10M16 4c0 4-3.5 5.5-7 5.5h-1L16 20"/>',
		'eye'        => '<path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="2.8"/>',
		// Brand marks, drawn as strokes so they inherit currentColor like
		// every other icon here.
		'whatsapp'   => '<path d="M3.5 20.5l1.3-4.3A8.2 8.2 0 1 1 8 19.3l-4.5 1.2z"/><path d="M9 9.2c.2 1 .7 2 1.5 2.8s1.8 1.3 2.8 1.5l.9-1.2 1.9.9c-.2.9-1 1.5-1.9 1.4a7.6 7.6 0 0 1-6.3-6.3c-.1-.9.5-1.7 1.4-1.9l.9 1.9-1.2.9z"/>',
		'telegram'   => '<path d="M21.5 4.3L2.9 11.4c-.7.3-.7.8 0 1l4.6 1.4L19 6.6c.5-.3.9 0 .6.3l-9.2 8.3-.3 4.3c.4 0 .6-.2.8-.4l2-1.9 4.2 3.1c.8.4 1.3.2 1.5-.7l2.7-12.7c.2-1-.4-1.5-1.1-1.2z"/>',
		'facebook'   => '<path d="M15 3h-2.2A3.8 3.8 0 0 0 9 6.8V9H7v3h2v9h3v-9h2.4l.6-3H12V7a1 1 0 0 1 1-1h2V3z"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) return '';

	return '<svg class="np-i ' . esc_attr( $class ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
		. ' stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
		. $paths[ $name ] . '</svg>';
}

/** Icon assigned to each of the 6 main sections. */
function np_section_icon( $slug ) {
	$map = array(
		'latest-jobs' => 'briefcase',
		'admit-card'  => 'card',
		'result'      => 'trophy',
		'answer-key'  => 'key',
		'syllabus'    => 'book',
		'admission'   => 'cap',
	);
	return isset( $map[ $slug ] ) ? $map[ $slug ] : 'file';
}

/* =========================================================
 * 1. STYLES & FONTS
 * =======================================================
 * The Google Fonts URL is built from whatever families are chosen in
 * Appearance > NaukriPatra Design. Choosing "System UI" for both makes
 * no external font request at all.
 */
/**
 * Asset version string.
 *
 * The theme version alone is not enough: it only changes when the theme
 * is re-versioned, so a CDN, a caching plugin or a browser can keep
 * serving the PREVIOUS style.css after an upload and the site still
 * looks like the old theme. Using the file's own modified time means
 * every upload produces a new URL, so nothing can serve a stale copy.
 * Falls back to the theme version if the file cannot be read.
 */
function np_asset_version( $relative_path ) {
	$file = get_stylesheet_directory() . '/' . ltrim( $relative_path, '/' );
	$mtime = @filemtime( $file );
	return $mtime ? (string) $mtime : (string) wp_get_theme()->get( 'Version' );
}

/**
 * Priority 999, not 20.
 *
 * WordPress prints stylesheets in the order they are enqueued, and when
 * two rules have the SAME specificity the later stylesheet wins. At
 * priority 20 this theme's CSS was printed before most plugin CSS, so
 * any plugin rule that merely matched as specifically as ours quietly
 * overrode the design. Enqueuing last puts this theme's stylesheet
 * after them, which is where a theme's own styling belongs.
 *
 * Note what this does NOT override, by design: the Customizer's
 * "Additional CSS" box and anything a plugin injects directly into
 * wp_head both print after ALL enqueued styles, so they still win.
 * That is correct — those are the site owner's own deliberate
 * overrides, and the theme should not fight them.
 */
add_action( 'wp_enqueue_scripts', function () {

	$fonts_url = np_google_fonts_url();
	if ( $fonts_url ) {
		wp_enqueue_style( 'np-fonts', $fonts_url, array(), null );
	}

	wp_enqueue_style( 'np-child',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'generate-style' ), np_asset_version( 'style.css' ) );

	// Dashboard design tokens, printed after style.css so they always win.
	wp_add_inline_style( 'np-child', np_inline_tokens_css() );

	wp_enqueue_script( 'np-main',
		get_stylesheet_directory_uri() . '/js/np-main.js',
		array(), np_asset_version( 'js/np-main.js' ), true );

	wp_localize_script( 'np-main', 'npData', array(
		'viewsRoot' => esc_url_raw( rest_url( 'naukripatra/v1/views/' ) ),
	) );
}, 999 );

// Core Web Vitals: keep our JS deferred (never render-blocking).
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	if ( 'np-main' === $handle && false === strpos( $tag, 'defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}, 10, 2 );

/**
 * Core Web Vitals: a Google Fonts stylesheet is render-blocking by
 * default — the browser downloads the whole font CSS before it paints
 * (slow FCP/LCP). Fix: the "preload + swap on load" pattern recommended
 * by web.dev. Nothing changes visually, the first paint just happens
 * sooner. The <noscript> copy covers JS-disabled browsers.
 *
 * (Unchanged from v2.8. No preconnect tags are added here — GeneratePress
 * already prints its own fonts.googleapis.com / fonts.gstatic.com
 * resource hints, and duplicating them was removed in v2.8.)
 */
add_filter( 'style_loader_tag', function ( $html, $handle, $href ) {
	if ( 'np-fonts' !== $handle ) return $html;
	$url = esc_url( $href );
	return '<link rel="preload" as="style" href="' . $url . '" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n"
		. '<noscript><link rel="stylesheet" href="' . $url . '"></noscript>' . "\n";
}, 10, 3 );

/* =========================================================
 * 1B. WEB APP FEEL — PWA meta tags
 * =========================================================
 * NOTE (technical SEO audit fix, kept from v2.8): a viewport meta tag
 * and two Google Fonts preconnect links used to be printed here too.
 * GeneratePress already prints its own viewport tag and those resource
 * hints, so all three were duplicates (two viewport tags = invalid
 * HTML). They stay removed. Only the PWA-specific tags — which nothing
 * else on the site outputs — are printed. The theme-color now follows
 * the primary colour chosen in the dashboard.
 * ======================================================= */
add_action( 'wp_head', function () {
	echo '<meta name="theme-color" content="' . esc_attr( np_token( 'primary' ) ) . '">' . "\n";
	echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
	echo '<meta name="apple-mobile-web-app-title" content="NaukriPatra">' . "\n";
}, 1 );

/* =========================================================
 * 2. AUTO-CREATE CATEGORIES (on activation)
 * =======================================================
 * Unchanged: the 6 job-type sections + All India + 28 states + 8 UTs.
 * Existing terms are never touched or renamed.
 */
function np_create_categories() {
	foreach ( np_main_sections() as $name => $slug ) {
		if ( ! term_exists( $slug, 'category' ) ) {
			wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
	}
	foreach ( np_locations() as $slug => $name ) {
		if ( ! term_exists( $slug, 'category' ) ) {
			wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
	}
	update_option( 'np_categories_done', 1 );
}
add_action( 'after_switch_theme', 'np_create_categories' );
add_action( 'admin_init', function () {
	if ( ! get_option( 'np_categories_done' ) ) np_create_categories();
} );

/* =========================================================
 * 3. JOB DETAILS META BOX
 * ======================================================= */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'np_job_details', 'Job Details (shown automatically in every list)',
		'np_job_details_box', 'post', 'normal', 'high' );
} );

/**
 * Allowed employmentType enum for Google's JobPosting schema.
 * (https://developers.google.com/search/docs/appearance/structured-data/job-posting)
 * Key = value saved in post meta (Google's exact enum),
 * Value = label shown in the admin dropdown.
 */
function np_employment_types() {
	return array(
		'FULL_TIME'  => 'Full-Time',
		'PART_TIME'  => 'Part-Time',
		'CONTRACTOR' => 'Contract',
		'TEMPORARY'  => 'Temporary',
		'INTERN'     => 'Internship',
		'VOLUNTEER'  => 'Volunteer',
		'PER_DIEM'   => 'Per Diem',
		'OTHER'      => 'Other',
	);
}

function np_job_details_box( $post ) {
	wp_nonce_field( 'np_job_details_save', 'np_job_details_nonce' );
	$qual     = get_post_meta( $post->ID, '_np_qualification', true );
	$last     = get_post_meta( $post->ID, '_np_last_date', true );
	$total    = get_post_meta( $post->ID, '_np_posts_count', true );
	$salary   = get_post_meta( $post->ID, '_np_salary', true );
	$org      = get_post_meta( $post->ID, '_np_organization', true );
	$emp_type = get_post_meta( $post->ID, '_np_employment_type', true );
	$locality = get_post_meta( $post->ID, '_np_locality', true );
	$street   = get_post_meta( $post->ID, '_np_street', true );
	$postal   = get_post_meta( $post->ID, '_np_postal_code', true );
	$sector   = np_get_sector( $post->ID );
	$apply    = get_post_meta( $post->ID, '_np_apply_url', true );
	$auto     = $apply ? '' : np_get_apply_url( $post->ID );
	$fee      = get_post_meta( $post->ID, '_np_app_fee', true );
	if ( '' === $emp_type ) $emp_type = 'FULL_TIME';
	?>
	<style>
		.np-mb{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
		.np-mb label{font-weight:600;display:block;margin-bottom:4px}
		.np-mb input,.np-mb select{width:100%;padding:8px;border:1px solid #8c8f94;border-radius:6px;font:inherit}
		.np-mb-sub{margin:18px 0 4px;font-weight:700;border-top:1px solid #dcdcde;padding-top:14px}
		.np-mb-hint{margin:5px 0 0;font-size:12px;color:#646970;line-height:1.4}
		@media(max-width:782px){.np-mb{grid-template-columns:1fr}}
	</style>
	<div class="np-mb">
		<div>
			<label for="np_job_sector">Job Sector</label>
			<select id="np_job_sector" name="np_job_sector">
				<?php foreach ( np_job_sectors() as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $sector, $val ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div>
			<label for="np_qualification">Qualification</label>
			<input type="text" id="np_qualification" name="np_qualification"
				value="<?php echo esc_attr( $qual ); ?>" placeholder="e.g. 10th Pass / Graduate">
		</div>
		<div>
			<label for="np_last_date">Last Date</label>
			<input type="text" id="np_last_date" name="np_last_date"
				value="<?php echo esc_attr( $last ); ?>" placeholder="e.g. 25 Aug 2026">
		</div>
		<div>
			<label for="np_posts_count">No. of Posts</label>
			<input type="text" id="np_posts_count" name="np_posts_count"
				value="<?php echo esc_attr( $total ); ?>" placeholder="e.g. 1250">
		</div>
		<div>
			<label for="np_apply_url">Apply / Official Link</label>
			<input type="url" id="np_apply_url" name="np_apply_url"
				value="<?php echo esc_attr( $apply ); ?>"
				placeholder="<?php echo esc_attr( $auto ? $auto : 'https://... (optional)' ); ?>">
			<?php if ( ! $apply ) : ?>
				<p class="np-mb-hint">
					<?php if ( $auto ) : ?>
						Detected in this post and already in use by the Apply Now button.
						Fill this field only to override it.
					<?php else : ?>
						No official link found in this post yet. Add an "Apply Online" or
						"Official Website" link to the content, or paste it here.
					<?php endif; ?>
				</p>
			<?php endif; ?>
		</div>
		<div>
			<label for="np_app_fee">Application Fee</label>
			<input type="text" id="np_app_fee" name="np_app_fee"
				value="<?php echo esc_attr( $fee ); ?>" placeholder="e.g. Rs 500 (SC/ST/PwD: Nil)">
		</div>
	</div>

	<p class="np-mb-sub">Google Jobs schema fields (all optional — whatever you fill goes into the schema)</p>
	<div class="np-mb">
		<div>
			<label for="np_organization">Recruiting Organisation</label>
			<input type="text" id="np_organization" name="np_organization"
				value="<?php echo esc_attr( $org ); ?>" placeholder="e.g. Staff Selection Commission">
		</div>
		<div>
			<label for="np_salary">Salary / Pay Scale</label>
			<input type="text" id="np_salary" name="np_salary"
				value="<?php echo esc_attr( $salary ); ?>" placeholder="e.g. Rs 35,400 - 1,12,400 per month">
		</div>
		<div>
			<label for="np_employment_type">Employment Type</label>
			<select id="np_employment_type" name="np_employment_type">
				<?php foreach ( np_employment_types() as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $emp_type, $val ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div>
			<label for="np_locality">City / Locality</label>
			<input type="text" id="np_locality" name="np_locality"
				value="<?php echo esc_attr( $locality ); ?>" placeholder="e.g. Delhi (optional)">
		</div>
		<div>
			<label for="np_street">Street Address</label>
			<input type="text" id="np_street" name="np_street"
				value="<?php echo esc_attr( $street ); ?>" placeholder="Optional — only if you publish an office address">
		</div>
		<div>
			<label for="np_postal_code">PIN / Postal Code</label>
			<input type="text" id="np_postal_code" name="np_postal_code"
				value="<?php echo esc_attr( $postal ); ?>" placeholder="e.g. 110001 (optional)">
		</div>
	</div>
	<p style="margin-bottom:0;color:#646970">Location does not need to be typed separately — the state categories you
		select are what appear in the lists. These extra fields only make the Google Jobs schema more complete.</p>
	<?php
}

add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['np_job_details_nonce'] ) ||
		 ! wp_verify_nonce( $_POST['np_job_details_nonce'], 'np_job_details_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	foreach ( array(
		'np_qualification' => '_np_qualification',
		'np_last_date'     => '_np_last_date',
		'np_posts_count'   => '_np_posts_count',
		'np_organization'  => '_np_organization',
		'np_salary'        => '_np_salary',
		'np_locality'      => '_np_locality',
		'np_street'        => '_np_street',
		'np_postal_code'   => '_np_postal_code',
		'np_app_fee'       => '_np_app_fee',
	) as $field => $key ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	if ( isset( $_POST['np_apply_url'] ) ) {
		update_post_meta( $post_id, '_np_apply_url', esc_url_raw( wp_unslash( $_POST['np_apply_url'] ) ) );
	}

	// Employment type: validated against the whitelist (tamper-safe),
	// falling back to FULL_TIME for an invalid or blank value.
	if ( isset( $_POST['np_employment_type'] ) ) {
		$emp_type = sanitize_text_field( wp_unslash( $_POST['np_employment_type'] ) );
		if ( ! array_key_exists( $emp_type, np_employment_types() ) ) {
			$emp_type = 'FULL_TIME';
		}
		update_post_meta( $post_id, '_np_employment_type', $emp_type );
	}

	// Job sector: same whitelist treatment.
	if ( isset( $_POST['np_job_sector'] ) ) {
		$sector = sanitize_text_field( wp_unslash( $_POST['np_job_sector'] ) );
		if ( ! array_key_exists( $sector, np_job_sectors() ) ) {
			$sector = 'government';
		}
		update_post_meta( $post_id, '_np_job_sector', $sector );
	}

	// Refresh the numeric deadline mirror used by the active count and
	// the "Ending soon" panel. `_np_last_date` itself is untouched.
	np_sync_last_date_ts( $post_id );

	// Re-detect the official apply link from the freshly saved content.
	np_sync_apply_url( $post_id );
} );

/** Keep the mirror correct for edits made through the REST API too. */
add_action( 'updated_post_meta', function ( $meta_id, $post_id, $meta_key ) {
	if ( '_np_last_date' === $meta_key ) np_sync_last_date_ts( $post_id );
}, 10, 3 );
add_action( 'added_post_meta', function ( $meta_id, $post_id, $meta_key ) {
	if ( '_np_last_date' === $meta_key ) np_sync_last_date_ts( $post_id );
}, 10, 3 );

/* =========================================================
 * 4. AUTO JOB LOCATION (from the selected categories)
 * ======================================================= */
function np_get_job_locations( $post_id ) {
	$locations = np_locations();
	$cats = get_the_category( $post_id );
	$out  = array();
	if ( $cats ) {
		foreach ( $cats as $cat ) {
			if ( isset( $locations[ $cat->slug ] ) ) {
				$out[] = array( 'name' => $locations[ $cat->slug ], 'link' => get_category_link( $cat ) );
			}
		}
	}
	return $out;
}

/** Plain-text location summary for a card. */
function np_location_text( $post_id ) {
	$locs = np_get_job_locations( $post_id );
	if ( ! $locs ) return 'All India';
	$names = array();
	foreach ( $locs as $l ) $names[] = $l['name'];
	if ( count( $names ) > 2 ) {
		return $names[0] . ' +' . ( count( $names ) - 1 ) . ' more';
	}
	return implode( ', ', $names );
}

/* =========================================================
 * 5. LAYOUT + MAIN QUERY
 * =======================================================
 * List pages stay full width; only a single job post gets a sidebar.
 * (Unchanged filter — single.php reads the same layout decision.)
 */
add_filter( 'generate_sidebar_layout', function ( $layout ) {
	if ( is_single() ) return 'right-sidebar';
	return 'no-sidebar';
} );

/**
 * 20 posts per page on list views, plus the new Job Type filter.
 * The sector filter reads ?np_sector=government|private from the
 * archive filter bar and is applied as a meta query.
 *
 * Government intentionally matches posts with NO saved sector too:
 * every post published before v3.0 is a government listing.
 */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) return;
	if ( ! ( $q->is_archive() || $q->is_search() || $q->is_home() ) ) return;

	$q->set( 'posts_per_page', 20 );

	$sector = isset( $_GET['np_sector'] ) ? sanitize_key( wp_unslash( $_GET['np_sector'] ) ) : '';
	if ( ! array_key_exists( $sector, np_job_sectors() ) ) return;

	if ( 'government' === $sector ) {
		$q->set( 'meta_query', array(
			'relation' => 'OR',
			array( 'key' => '_np_job_sector', 'value' => 'government' ),
			array( 'key' => '_np_job_sector', 'compare' => 'NOT EXISTS' ),
			array( 'key' => '_np_job_sector', 'value' => '' ),
		) );
	} else {
		$q->set( 'meta_query', array(
			array( 'key' => '_np_job_sector', 'value' => 'private' ),
		) );
	}
} );

/* =========================================================
 * 6. THE JOB LIST (core of the site)
 * =======================================================
 * np_render_job_table() is kept as the single data layer — same name,
 * same signature, same meta fields. Only its markup changed: the old
 * 8-column table is now a stack of corporate job cards that already
 * reflow on mobile without any horizontal scroll.
 */
function np_render_job_table( $query = null, $args = array() ) {
	if ( null === $query ) { global $wp_query; $query = $wp_query; }

	$args = wp_parse_args( $args, array(
		'native_ad_after' => 3,   // split the list with the native ad slot
		'ad_slot'         => 'arch_native',
		'paginate'        => true,
	) );

	$paged  = max( 1, (int) $query->get( 'paged' ) );
	$ppp    = (int) $query->get( 'posts_per_page' );
	if ( $ppp < 1 ) $ppp = (int) get_option( 'posts_per_page', 10 );
	$serial = ( $paged - 1 ) * $ppp;

	if ( ! $query->have_posts() ) {
		echo '<div class="np-empty">' . np_icon( 'search' )
			. '<p>No listings in this section yet. New updates are added every day.</p></div>';
		return;
	}

	echo '<div class="np-joblist">';

	$i = 0;
	while ( $query->have_posts() ) {
		$query->the_post();
		$serial++;
		$i++;

		$id     = get_the_ID();
		$qual   = get_post_meta( $id, '_np_qualification', true );
		$last   = get_post_meta( $id, '_np_last_date', true );
		$total  = get_post_meta( $id, '_np_posts_count', true );
		$is_new = ( time() - get_post_time( 'U', true, $id ) ) < 3 * DAY_IN_SECONDS;
		?>
		<article class="np-job">
			<div class="np-job-main">
				<div class="np-job-top">
					<?php echo np_sector_badge( $id ); ?>
					<?php
					$cats = get_the_category( $id );
					$sections = np_main_sections();
					foreach ( (array) $cats as $c ) {
						if ( in_array( $c->slug, $sections, true ) ) {
							echo '<span class="np-tag">' . esc_html( $c->name ) . '</span>';
							break;
						}
					}
					?>
					<?php if ( $is_new ) echo '<span class="np-new">NEW</span>'; ?>
					<span class="np-job-date"><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></span>
				</div>

				<h3 class="np-job-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>

				<ul class="np-job-meta">
					<li><?php echo np_icon( 'cap' ); ?><span><?php echo $qual ? esc_html( $qual ) : 'See notification'; ?></span></li>
					<li><?php echo np_icon( 'pin' ); ?><span><?php echo esc_html( np_location_text( $id ) ); ?></span></li>
					<?php if ( $total ) : ?>
						<li><?php echo np_icon( 'users' ); ?><span><?php echo esc_html( $total ); ?> posts</span></li>
					<?php endif; ?>
					<?php if ( $last ) : ?>
						<li class="np-job-last"><?php echo np_icon( 'calendar' ); ?><span>Last date: <strong><?php echo esc_html( $last ); ?></strong></span></li>
					<?php endif; ?>
				</ul>
			</div>

			<div class="np-job-cta">
				<span class="np-job-sl">#<?php echo (int) $serial; ?></span>
				<a class="np-btn np-btn-primary" href="<?php the_permalink(); ?>">
					View Details <?php echo np_icon( 'chevron' ); ?>
				</a>
			</div>
		</article>
		<?php

		if ( $args['native_ad_after'] && $i === (int) $args['native_ad_after'] ) {
			np_ad_slot( $args['ad_slot'], 'np-ad-native' );
		}
	}
	wp_reset_postdata();

	echo '</div>';

	if ( $args['paginate'] ) {
		$links = paginate_links( array(
			'total'     => $query->max_num_pages,
			'current'   => $paged,
			'prev_text' => 'Previous',
			'next_text' => 'Next',
		) );
		if ( $links ) {
			echo '<nav class="np-pagination" aria-label="Job list pages">' . $links . '</nav>';
		}
	}
}

/* =========================================================
 * 6B. FILTER BAR (archive / listing pages)
 * ======================================================= */
function np_render_filter_bar() {
	$sector = isset( $_GET['np_sector'] ) ? sanitize_key( wp_unslash( $_GET['np_sector'] ) ) : '';
	$sort   = isset( $_GET['np_sort'] ) ? sanitize_key( wp_unslash( $_GET['np_sort'] ) ) : '';

	/**
	 * On a term archive the filters must stay INSIDE that term, so the
	 * form posts back to the term's own URL and the Category dropdown
	 * is not offered (it would fight the term already in the URL).
	 * On the blog index and on search results the form goes to the site
	 * root, where a category can be chosen freely.
	 *
	 * np-main.js strips empty fields before submit, so an untouched
	 * search box never turns a category page into a search page.
	 */
	$term        = ( is_category() || is_tag() || is_tax() ) ? get_queried_object() : null;
	$action      = ( $term && ! is_wp_error( $term ) ) ? get_term_link( $term ) : home_url( '/' );
	if ( is_wp_error( $action ) ) $action = home_url( '/' );
	$show_cats   = ! $term;
	?>
	<form class="np-filterbar" method="get" action="<?php echo esc_url( $action ); ?>" data-np-filterbar>
		<div class="np-filter-search">
			<?php echo np_icon( 'search' ); ?>
			<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="Search within jobs" aria-label="Search jobs">
		</div>

		<label class="np-filter-field">
			<span>Job Type</span>
			<select name="np_sector">
				<option value="">All</option>
				<?php foreach ( np_job_sectors() as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $sector, $val ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>

		<?php if ( $show_cats ) : ?>
			<label class="np-filter-field">
				<span>Category</span>
				<select name="category_name">
					<option value="">All categories</option>
					<?php foreach ( np_main_sections() as $name => $slug ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>"
							<?php selected( get_query_var( 'category_name' ), $slug ); ?>><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		<?php endif; ?>

		<label class="np-filter-field">
			<span>Sort</span>
			<select name="np_sort">
				<option value="" <?php selected( $sort, '' ); ?>>Newest first</option>
				<option value="popular" <?php selected( $sort, 'popular' ); ?>>Most viewed</option>
				<option value="title" <?php selected( $sort, 'title' ); ?>>Title A–Z</option>
			</select>
		</label>

		<button class="np-btn np-btn-primary np-filter-go" type="submit">
			<?php echo np_icon( 'filter' ); ?> Apply
		</button>
	</form>
	<?php
}

/** Sorting requested from the filter bar. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) return;
	if ( ! ( $q->is_archive() || $q->is_search() || $q->is_home() ) ) return;

	$sort = isset( $_GET['np_sort'] ) ? sanitize_key( wp_unslash( $_GET['np_sort'] ) ) : '';
	if ( 'popular' === $sort ) {
		$q->set( 'meta_key', '_np_views' );
		$q->set( 'orderby', 'meta_value_num' );
		$q->set( 'order', 'DESC' );
	} elseif ( 'title' === $sort ) {
		$q->set( 'orderby', 'title' );
		$q->set( 'order', 'ASC' );
	}
}, 20 );

/* =========================================================
 * 7. HEADER — brand, sticky nav, mobile drawer
 * ======================================================= */
add_filter( 'generate_site_title_output', function () {
	return '<div class="np-brand"><a href="' . esc_url( home_url( '/' ) ) . '" rel="home">'
		. '<span class="np-logo">Naukri<span>Patra</span></span>'
		. '<span class="np-tagline">Government &amp; Private Job Updates</span>'
		. '</a></div>';
} );
add_filter( 'generate_site_description_output', '__return_empty_string' );

/**
 * Switch off GeneratePress' own navigation.
 *
 * THE BUG THIS FIXES: the site was showing TWO menus stacked on top of
 * each other — this theme's .np-navbar with its hamburger, and below it
 * GeneratePress' own header with its "MENU" toggle. Both were real and
 * only the GP one responded, because it is the parent theme's script
 * that drives it.
 *
 * .np-navbar carries the logo, the full nav, search, the CTAs and the
 * mobile drawer, so GP's navigation is redundant. Returning an empty
 * location from this filter stops GP printing its nav and its mobile
 * menu toggle altogether. The CSS has a matching fallback for any GP
 * setting that prints a separate mobile header.
 */
add_filter( 'generate_navigation_location', '__return_empty_string', 20 );

/** The nav links shown in the desktop bar and the mobile drawer. */
function np_nav_items() {
	$items = array( array( 'Home', home_url( '/' ), 'home' ) );
	foreach ( np_main_sections() as $name => $slug ) {
		$t = get_term_by( 'slug', $slug, 'category' );
		$items[] = array( $name, $t ? get_category_link( $t ) : home_url( '/' ), np_section_icon( $slug ) );
	}
	return $items;
}

/** Sticky corporate nav bar, printed right under the GeneratePress header. */
add_action( 'generate_after_header', function () {
	$social = np_social_links();
	?>
	<div class="np-navbar" id="npNavbar">
		<div class="np-navbar-inner">
			<button class="np-burger" type="button" id="npBurger"
				aria-label="Open menu" aria-expanded="false" aria-controls="npDrawer">
				<?php echo np_icon( 'menu' ); ?>
			</button>

			<a class="np-nav-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php
				/* Use the logo uploaded in Customize > Site Identity when there
				   is one, so the real brand mark is used instead of the text
				   fallback. This navbar IS the site header now, so the logo
				   belongs here. */
				$np_logo_id  = get_theme_mod( 'custom_logo' );
				$np_logo_url = $np_logo_id ? wp_get_attachment_image_url( $np_logo_id, 'full' ) : '';
				if ( $np_logo_url ) :
					?>
					<img class="np-nav-logo" src="<?php echo esc_url( $np_logo_url ); ?>"
						alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php else : ?>
					<span class="np-logo">Naukri<span>Patra</span></span>
				<?php endif; ?>
			</a>

			<nav class="np-nav" aria-label="Main">
				<?php foreach ( np_nav_items() as $it ) : ?>
					<a href="<?php echo esc_url( $it[1] ); ?>"><?php echo esc_html( $it[0] ); ?></a>
				<?php endforeach; ?>
			</nav>

			<div class="np-nav-actions">
				<button class="np-iconbtn" type="button" id="npSearchToggle"
					aria-label="Search" aria-expanded="false"><?php echo np_icon( 'search' ); ?></button>
				<a class="np-btn np-btn-ghost np-nav-app" href="<?php echo esc_url( $social['playstore'] ); ?>"
					target="_blank" rel="noopener"><?php echo np_icon( 'download' ); ?> Download App</a>
				<a class="np-btn np-btn-accent" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">Post a Job</a>
			</div>
		</div>

		<div class="np-navsearch" id="npNavSearch" hidden>
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo np_icon( 'search' ); ?>
				<input type="search" name="s" placeholder="Search jobs, exams, results" aria-label="Search" required>
				<button class="np-btn np-btn-accent" type="submit">Search</button>
			</form>
		</div>
	</div>
	<?php
	np_ad_slot( 'header', 'np-ad-header' );
}, 5 );

/** Full-height slide-out drawer (mobile). Built in plain HTML + vanilla JS. */
add_action( 'wp_footer', function () {
	$social = np_social_links();
	?>
	<div class="np-overlay" id="npOverlay" hidden></div>
	<aside class="np-drawer" id="npDrawer" hidden aria-label="Mobile menu">
		<div class="np-drawer-head">
			<span class="np-logo np-logo-drawer">Naukri<span>Patra</span></span>
			<button class="np-drawer-close" type="button" id="npDrawerClose" aria-label="Close menu">
				<?php echo np_icon( 'close' ); ?>
			</button>
		</div>

		<nav class="np-drawer-nav" aria-label="Mobile">
			<?php foreach ( np_nav_items() as $it ) : ?>
				<a href="<?php echo esc_url( $it[1] ); ?>">
					<?php echo np_icon( $it[2], 'np-i-lead' ); ?>
					<span><?php echo esc_html( $it[0] ); ?></span>
					<?php echo np_icon( 'chevron', 'np-i-end' ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="np-drawer-foot">
			<a class="np-btn np-btn-accent np-btn-block" href="<?php echo esc_url( $social['playstore'] ); ?>"
				target="_blank" rel="noopener"><?php echo np_icon( 'download' ); ?> Download App</a>
			<?php /* Kept here because the bar drops this button below 600px. */ ?>
			<a class="np-btn np-btn-outline np-btn-block" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">Post a Job</a>
			<a class="np-btn np-btn-outline np-btn-block" href="<?php echo esc_url( wp_login_url() ); ?>">Login / Register</a>
		</div>
	</aside>
	<?php
} );

/* =========================================================
 * 8. SINGLE POST HOOKS
 * =======================================================
 * v3.0 ships a real single.php, so the layout is controlled directly
 * by the template instead of being patched through these hooks. The
 * hooks are kept (other post types / plugins may still route through
 * the parent template) but they now stand down whenever single.php is
 * driving the page, so nothing is printed twice.
 */
function np_single_template_active() {
	return ! empty( $GLOBALS['np_single_template'] );
}

// GeneratePress' own featured image is off on single (ours is in the template).
add_filter( 'generate_show_post_image', function ( $show ) {
	return is_single() ? false : $show;
} );

add_action( 'generate_before_content', function () {
	if ( np_single_template_active() ) return;
	if ( is_single() && has_post_thumbnail() ) {
		/**
		 * Core Web Vitals — LCP fix (kept from v2.8): this featured image
		 * is the single post's Largest Contentful Paint candidate, and the
		 * caching plugin's lazy-load was being applied to it. An LCP image
		 * must never be lazy-loaded. Eager loading + high fetch priority +
		 * LiteSpeed Cache's own skip attribute.
		 */
		$attrs = array(
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'data-no-lazy'  => '1',
		);
		echo '<div class="np-featured">' . get_the_post_thumbnail( null, 'large', $attrs ) . '</div>';
	}
}, 5 );

add_action( 'generate_after_entry_title', function () {
	if ( np_single_template_active() || ! is_single() ) return;
	$locs = np_get_job_locations( get_the_ID() );
	echo '<div class="np-meta-strip">';
	echo '<span class="np-meta-date">' . np_icon( 'calendar' ) . esc_html( get_the_date( 'd M Y' ) ) . '</span>';
	foreach ( $locs as $l ) {
		echo '<a class="np-loc" href="' . esc_url( $l['link'] ) . '">' . np_icon( 'pin' ) . esc_html( $l['name'] ) . '</a>';
	}
	echo '</div>';
} );

/**
 * Quick Details box at the top of the article + the in-article ad +
 * share buttons at the end. Unchanged behaviour; single.php calls
 * the_content() so this still runs there too.
 */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) return $content;

	$id    = get_the_ID();
	$qual  = get_post_meta( $id, '_np_qualification', true );
	$last  = get_post_meta( $id, '_np_last_date', true );
	$total = get_post_meta( $id, '_np_posts_count', true );

	$box = '';
	if ( $qual || $last || $total ) {
		$box .= '<div class="np-detail-box"><h3>Quick Details</h3><div class="np-detail-grid">';
		if ( $qual )  $box .= '<div><span>Qualification</span><strong>' . esc_html( $qual ) . '</strong></div>';
		if ( $last )  $box .= '<div><span>Last Date</span><strong class="np-red">' . esc_html( $last ) . '</strong></div>';
		if ( $total ) $box .= '<div><span>No. of Posts</span><strong>' . esc_html( $total ) . '</strong></div>';
		$box .= '</div></div>';
	}

	// In-article ad, after the 2nd paragraph.
	$ad = np_get_ad( 'in_article' );
	if ( $ad ) {
		$parts = explode( '</p>', $content );
		if ( count( $parts ) > 2 ) {
			$parts[1] .= '</p><div class="np-ad np-ad-inarticle"><span class="np-ad-label">Advertisement</span>' . $ad . '</div>';
			array_splice( $parts, 2, 0, '' );
			$content = implode( '</p>', array_filter( $parts, function( $v, $k ){ return $v !== '' || $k === 0; }, ARRAY_FILTER_USE_BOTH ) );
		} else {
			$content .= '<div class="np-ad np-ad-inarticle"><span class="np-ad-label">Advertisement</span>' . $ad . '</div>';
		}
	}

	return $box . $content . np_share_buttons_html();
} );

/**
 * Post engagement panel — live view count + working share buttons.
 *
 * Printed under the article body on every single post. The view number
 * is filled in and refreshed by np-main.js from the REST endpoint
 * below, so it keeps ticking up while the page is open and is correct
 * even when a caching plugin is serving the page.
 */
function np_share_buttons_html() {
	$id    = get_the_ID();
	$url   = get_permalink( $id );
	$eurl  = rawurlencode( $url );
	$title = rawurlencode( get_the_title( $id ) );

	$wa = "https://api.whatsapp.com/send?text={$title}%20-%20{$eurl}";
	$tg = "https://t.me/share/url?url={$eurl}&text={$title}";
	$fb = "https://www.facebook.com/sharer/sharer.php?u={$eurl}";

	ob_start();
	?>
	<section class="np-engage" data-np-post="<?php echo (int) $id; ?>">
		<div class="np-engage-views">
			<span class="np-engage-eye"><?php echo np_icon( 'eye' ); ?></span>
			<span class="np-engage-num">
				<strong data-np-views><?php echo esc_html( number_format_i18n( np_get_views( $id ) ) ); ?></strong>
				<em>people have viewed this job</em>
			</span>
			<span class="np-engage-live" aria-hidden="true"><i></i>live</span>
		</div>

		<div class="np-engage-share">
			<span class="np-engage-label">Share this job</span>
			<div class="np-share">
				<a class="np-sh np-sh-wa" href="<?php echo esc_url( $wa ); ?>"
					target="_blank" rel="noopener nofollow" aria-label="Share on WhatsApp">
					<?php echo np_icon( 'whatsapp' ); ?><span>WhatsApp</span></a>
				<a class="np-sh np-sh-tg" href="<?php echo esc_url( $tg ); ?>"
					target="_blank" rel="noopener nofollow" aria-label="Share on Telegram">
					<?php echo np_icon( 'telegram' ); ?><span>Telegram</span></a>
				<a class="np-sh np-sh-fb" href="<?php echo esc_url( $fb ); ?>"
					target="_blank" rel="noopener nofollow" aria-label="Share on Facebook">
					<?php echo np_icon( 'facebook' ); ?><span>Facebook</span></a>
				<button class="np-sh np-sh-copy" type="button"
					data-np-copy="<?php echo esc_attr( $url ); ?>" aria-label="Copy link">
					<?php echo np_icon( 'link' ); ?><span>Copy Link</span></button>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================
 * 8B. LIVE VIEW COUNTER (REST)
 * =======================================================
 * v2.8 incremented _np_views straight from wp_head. That silently
 * stopped counting the moment a full-page cache was switched on,
 * because a cached page never runs PHP — which is why counts on a
 * cached site drift far below reality.
 *
 * The count is now registered and read over a tiny REST route that
 * runs on every real visit, cached page or not:
 *   GET  /wp-json/naukripatra/v1/views/<id>   read the count
 *   POST /wp-json/naukripatra/v1/views/<id>   count one view, return it
 *
 * Guards: logged-in users are never counted (same as v2.8), and one IP
 * can only add one view per post per hour, so a refresh or a cleared
 * browser cannot inflate the number.
 */
add_action( 'rest_api_init', function () {
	register_rest_route( 'naukripatra/v1', '/views/(?P<id>\d+)', array(
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => function ( $request ) {
				$id = (int) $request['id'];
				if ( 'publish' !== get_post_status( $id ) ) {
					return new WP_Error( 'np_not_found', 'No such post', array( 'status' => 404 ) );
				}
				return array( 'id' => $id, 'views' => np_get_views( $id ) );
			},
		),
		array(
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => 'np_rest_count_view',
		),
	) );
} );

function np_rest_count_view( $request ) {
	$id = (int) $request['id'];
	if ( 'publish' !== get_post_status( $id ) ) {
		return new WP_Error( 'np_not_found', 'No such post', array( 'status' => 404 ) );
	}

	// Editors reading their own posts are not an audience.
	if ( is_user_logged_in() ) {
		return array( 'id' => $id, 'views' => np_get_views( $id ), 'counted' => false );
	}

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	$key = 'np_v_' . $id . '_' . md5( $ip );

	if ( get_transient( $key ) ) {
		return array( 'id' => $id, 'views' => np_get_views( $id ), 'counted' => false );
	}
	set_transient( $key, 1, HOUR_IN_SECONDS );

	$views = np_get_views( $id ) + 1;
	update_post_meta( $id, '_np_views', $views );

	return array( 'id' => $id, 'views' => $views, 'counted' => true );
}

/* =========================================================
 * 9. AD HOOKS
 * =======================================================
 * The slot definitions, the admin page and np_ad_slot()/np_get_ad()
 * all live in inc/ads.php now. Only the placement hooks are here.
 */
add_action( 'generate_after_content', function () {
	if ( is_single() && ! np_single_template_active() ) np_ad_slot( 'after_content', 'np-ad-after' );
}, 5 );

/* =========================================================
 * 10. FOOTER (4 columns)
 * ======================================================= */
add_action( 'generate_before_copyright', function () {
	np_ad_slot( 'footer', 'np-ad-footer' );
	$social = np_social_links();
	?>
	<div class="np-footer">
		<div class="np-footer-inner">
			<div class="np-fcol">
				<span class="np-logo np-logo-footer">Naukri<span>Patra</span></span>
				<p>NaukriPatra publishes verified government and private job notifications, admit cards,
					results, answer keys, syllabus and admission updates for every state and union
					territory in India — updated every day.</p>
				<div class="np-fapps">
					<a class="np-btn np-btn-accent" href="<?php echo esc_url( $social['playstore'] ); ?>" target="_blank" rel="noopener">
						<?php echo np_icon( 'download' ); ?> Download App
					</a>
				</div>
			</div>

			<div class="np-fcol">
				<h4>Job Categories</h4>
				<ul>
					<?php foreach ( np_main_sections() as $name => $slug ) :
						$t = get_term_by( 'slug', $slug, 'category' );
						$link = $t ? get_category_link( $t ) : '#'; ?>
						<li><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="np-fcol">
				<h4>Company</h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">About Us</a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">Contact Us</a></li>
					<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a></li>
					<li><a href="<?php echo esc_url( home_url( '/disclaimer/' ) ); ?>">Disclaimer</a></li>
				</ul>
			</div>

			<div class="np-fcol">
				<h4>Stay Connected</h4>
				<div class="np-fsocial">
					<a class="np-sh-wa" href="<?php echo esc_url( $social['whatsapp'] ); ?>" target="_blank" rel="noopener">
						<?php echo np_icon( 'chat' ); ?> WhatsApp</a>
					<a class="np-sh-tg" href="<?php echo esc_url( $social['telegram'] ); ?>" target="_blank" rel="noopener">
						<?php echo np_icon( 'send' ); ?> Telegram</a>
					<a class="np-sh-fb" href="<?php echo esc_url( $social['facebook'] ); ?>" target="_blank" rel="noopener">Facebook</a>
					<a class="np-sh-yt" href="<?php echo esc_url( $social['youtube'] ); ?>" target="_blank" rel="noopener">YouTube</a>
				</div>
				<p class="np-fnote">Follow our channels for a daily job alert.</p>
			</div>
		</div>
	</div>
	<?php
} );

/* =========================================================
 * 11. REST API — JOB DETAILS FOR THE ANDROID APP
 * =======================================================
 * 100% ADDITIVE, exactly as in v2.8. No existing API field is renamed,
 * reshaped or removed. v3.0 only ADDS `job_sector` (and `apply_url`)
 * alongside them, using the same register_rest_field pattern, and adds
 * `job_sector` as one more key inside the existing `job_details`
 * object — every key the app already reads is still there, unchanged.
 *
 * The app can read (GET /wp-json/wp/v2/posts):
 *   post.qualification          "10th Pass / Graduate"
 *   post.last_date              "25 Aug 2026"
 *   post.posts_count            "1250"
 *   post.job_sector             "government" | "private"     (NEW)
 *   post.job_details            { everything in one object }
 *   post.meta._np_qualification (raw meta available too)
 * ======================================================= */

add_action( 'init', function () {
	foreach ( array(
		'_np_qualification', '_np_last_date', '_np_posts_count',
		'_np_organization', '_np_salary', '_np_employment_type',
		'_np_locality', '_np_street', '_np_postal_code',
		'_np_job_sector', '_np_apply_url', '_np_app_fee',
	) as $key ) {
		register_post_meta( 'post', $key, array(
			'type'          => 'string',
			'single'        => true,
			'default'       => '',
			'show_in_rest'  => true,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		) );
	}
} );

add_action( 'rest_api_init', function () {

	// One complete object — the easiest thing for the app to consume.
	register_rest_field( 'post', 'job_details', array(
		'get_callback' => function ( $post ) {
			$id   = $post['id'];
			$locs = np_get_job_locations( $id );
			$loc_names = array();
			foreach ( $locs as $l ) { $loc_names[] = $l['name']; }
			return array(
				'qualification'    => (string) get_post_meta( $id, '_np_qualification', true ),
				'last_date'        => (string) get_post_meta( $id, '_np_last_date', true ),
				'posts_count'      => (string) get_post_meta( $id, '_np_posts_count', true ),
				'organization'     => (string) get_post_meta( $id, '_np_organization', true ),
				'salary'           => (string) get_post_meta( $id, '_np_salary', true ),
				'employment_type'  => np_schema_valid_employment_type( get_post_meta( $id, '_np_employment_type', true ) ),
				'locality'         => (string) get_post_meta( $id, '_np_locality', true ),
				'street'           => (string) get_post_meta( $id, '_np_street', true ),
				'postal_code'      => (string) get_post_meta( $id, '_np_postal_code', true ),
				'locations'        => $loc_names,
				'views'            => (int) get_post_meta( $id, '_np_views', true ),
				'publish_date'     => get_the_date( 'd M Y', $id ),
				'is_new'           => ( time() - get_post_time( 'U', true, $id ) ) < 3 * DAY_IN_SECONDS,
				// NEW in v3.0 — added keys only, nothing above was changed.
				'job_sector'       => np_get_sector( $id ),
				'job_sector_label' => np_job_sectors()[ np_get_sector( $id ) ],
				'apply_url'        => (string) get_post_meta( $id, '_np_apply_url', true ),
				// NEW in v3.8: the link the Apply button actually uses —
				// the field above when filled, otherwise the official link
				// detected in the post content. `apply_url` keeps its exact
				// old meaning (the raw field) so nothing the app reads changes.
				'apply_url_resolved' => np_get_apply_url( $id ),
				'app_fee'          => (string) get_post_meta( $id, '_np_app_fee', true ),
			);
		},
		'schema' => array(
			'description' => 'NaukriPatra job details (meta box data)',
			'type'        => 'object',
			'context'     => array( 'view', 'edit', 'embed' ),
		),
	) );

	// Flat top-level fields — read + write.
	foreach ( array(
		'qualification' => '_np_qualification',
		'last_date'     => '_np_last_date',
		'posts_count'   => '_np_posts_count',
		'organization'  => '_np_organization',
		'salary'        => '_np_salary',
		'locality'      => '_np_locality',
		'street'        => '_np_street',
		'postal_code'   => '_np_postal_code',
		'app_fee'       => '_np_app_fee',
	) as $field => $meta_key ) {
		register_rest_field( 'post', $field, array(
			'get_callback'    => function ( $post ) use ( $meta_key ) {
				return (string) get_post_meta( $post['id'], $meta_key, true );
			},
			'update_callback' => function ( $value, $post ) use ( $meta_key ) {
				if ( ! current_user_can( 'edit_post', $post->ID ) ) {
					return new WP_Error( 'rest_forbidden', 'Not allowed', array( 'status' => 403 ) );
				}
				update_post_meta( $post->ID, $meta_key, sanitize_text_field( (string) $value ) );
				return true;
			},
			'schema' => array(
				'description' => 'NaukriPatra job field: ' . $field,
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
		) );
	}

	// employment_type — whitelist-validated read/write against Google's
	// JobPosting enum. Kept out of the generic loop because every write
	// must be confirmed against np_employment_types(). UNCHANGED.
	register_rest_field( 'post', 'employment_type', array(
		'get_callback'    => function ( $post ) {
			return np_schema_valid_employment_type( get_post_meta( $post['id'], '_np_employment_type', true ) );
		},
		'update_callback' => function ( $value, $post ) {
			if ( ! current_user_can( 'edit_post', $post->ID ) ) {
				return new WP_Error( 'rest_forbidden', 'Not allowed', array( 'status' => 403 ) );
			}
			$value = sanitize_text_field( (string) $value );
			if ( ! array_key_exists( $value, np_employment_types() ) ) {
				return new WP_Error( 'rest_invalid_param', 'employment_type must be one of: ' . implode( ', ', array_keys( np_employment_types() ) ), array( 'status' => 400 ) );
			}
			update_post_meta( $post->ID, '_np_employment_type', $value );
			return true;
		},
		'schema' => array(
			'description' => 'NaukriPatra job field: employment_type (Google JobPosting enum)',
			'type'        => 'string',
			'enum'        => array( 'FULL_TIME', 'PART_TIME', 'CONTRACTOR', 'TEMPORARY', 'INTERN', 'VOLUNTEER', 'PER_DIEM', 'OTHER' ),
			'context'     => array( 'view', 'edit', 'embed' ),
		),
	) );

	// NEW in v3.0 — job_sector, same additive pattern, whitelist-validated.
	register_rest_field( 'post', 'job_sector', array(
		'get_callback'    => function ( $post ) {
			return np_get_sector( $post['id'] );
		},
		'update_callback' => function ( $value, $post ) {
			if ( ! current_user_can( 'edit_post', $post->ID ) ) {
				return new WP_Error( 'rest_forbidden', 'Not allowed', array( 'status' => 403 ) );
			}
			$value = sanitize_key( (string) $value );
			if ( ! array_key_exists( $value, np_job_sectors() ) ) {
				return new WP_Error( 'rest_invalid_param', 'job_sector must be one of: government, private', array( 'status' => 400 ) );
			}
			update_post_meta( $post->ID, '_np_job_sector', $value );
			return true;
		},
		'schema' => array(
			'description' => 'NaukriPatra job field: job_sector',
			'type'        => 'string',
			'enum'        => array( 'government', 'private' ),
			'context'     => array( 'view', 'edit', 'embed' ),
		),
	) );

	// NEW in v3.0 — apply_url.
	register_rest_field( 'post', 'apply_url', array(
		'get_callback'    => function ( $post ) {
			return (string) get_post_meta( $post['id'], '_np_apply_url', true );
		},
		'update_callback' => function ( $value, $post ) {
			if ( ! current_user_can( 'edit_post', $post->ID ) ) {
				return new WP_Error( 'rest_forbidden', 'Not allowed', array( 'status' => 403 ) );
			}
			update_post_meta( $post->ID, '_np_apply_url', esc_url_raw( (string) $value ) );
			return true;
		},
		'schema' => array(
			'description' => 'NaukriPatra job field: apply_url',
			'type'        => 'string',
			'context'     => array( 'view', 'edit', 'embed' ),
		),
	) );
} );

/**
 * REST collection filter: /wp-json/wp/v2/posts?job_sector=private
 * Additive — the parameter is optional and changes nothing when absent.
 */
add_filter( 'rest_post_query', function ( $args, $request ) {
	$sector = $request->get_param( 'job_sector' );
	if ( ! $sector || ! array_key_exists( $sector, np_job_sectors() ) ) return $args;

	if ( 'government' === $sector ) {
		$args['meta_query'] = array(
			'relation' => 'OR',
			array( 'key' => '_np_job_sector', 'value' => 'government' ),
			array( 'key' => '_np_job_sector', 'compare' => 'NOT EXISTS' ),
			array( 'key' => '_np_job_sector', 'value' => '' ),
		);
	} else {
		$args['meta_query'] = array(
			array( 'key' => '_np_job_sector', 'value' => 'private' ),
		);
	}
	return $args;
}, 10, 2 );

add_filter( 'rest_post_collection_params', function ( $params ) {
	$params['job_sector'] = array(
		'description' => 'Filter jobs by sector.',
		'type'        => 'string',
		'enum'        => array( 'government', 'private' ),
	);
	return $params;
} );

/* =========================================================
 * 12. STICKY MOBILE FOOTER MENU
 * ======================================================= */
add_action( 'wp_footer', function () {
	$items = array(
		array( 'Home', home_url( '/' ), 'home' ),
		array( 'Jobs', 'latest-jobs', 'briefcase' ),
		array( 'Admit Card', 'admit-card', 'card' ),
		array( 'Result', 'result', 'trophy' ),
	);
	echo '<nav class="np-sticky-menu" aria-label="Quick menu">';
	$current = home_url( add_query_arg( array() ) );
	foreach ( $items as $it ) {
		$url = $it[1];
		if ( strpos( $url, 'http' ) !== 0 ) {
			$t = get_term_by( 'slug', $url, 'category' );
			$url = $t ? get_category_link( $t ) : home_url( '/' );
		}
		$active = untrailingslashit( $url ) === untrailingslashit( $current ) ? ' np-sm-active' : '';
		echo '<a class="np-sm-link' . $active . '" href="' . esc_url( $url ) . '">'
			. '<span class="np-sm-ico">' . np_icon( $it[2] ) . '</span>'
			. '<span>' . esc_html( $it[0] ) . '</span></a>';
	}
	echo '</nav>';
} );
