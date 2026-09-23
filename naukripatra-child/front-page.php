<?php
/**
 * NaukriPatra — Homepage (v3.0 premium corporate)
 *
 * Structure:
 *  1. Hero — headline, search, live stats, All / Government / Private toggle
 *  2. Quick category tiles (the 6 existing sections)
 *  3. Trending Jobs (np_trending_query) with sector badges
 *  4. Browse by State — state dropdown, All India + USA Jobs, full 36 grid
 *  5. Free Career Tools (card UI only — see README, section 5)
 *  6. Latest Jobs (np_render_job_table data layer)
 *  7. App & channels band
 *
 * Every functional building block from v2.2–v2.8 is reused as-is; only
 * the markup and CSS around them changed.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$np_social = np_social_links();

/* Live stats — counted, never invented. */
$np_live_jobs = (int) wp_count_posts()->publish;
$np_states    = count( np_locations() ) - 1; // "All India" is not a state
$np_cats      = count( np_main_sections() );
?>
<div class="np-home">

	<!-- ============ 1. HERO ============ -->
	<section class="np-hero">
		<div class="np-hero-inner">
			<p class="np-hero-eyebrow"><?php echo np_icon( 'bolt' ); ?> Updated every day</p>
			<h1 class="np-hero-title">Government &amp; Private Jobs,<br>verified and in one place.</h1>
			<p class="np-hero-sub">
				Sarkari and private sector notifications, admit cards, results, answer keys and
				syllabus updates for every state and union territory in India.
			</p>

			<form role="search" method="get" class="np-hero-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo np_icon( 'search' ); ?>
				<input type="search" name="s" placeholder="Search by post, department or exam"
					value="<?php echo esc_attr( get_search_query() ); ?>" aria-label="Search jobs" required>
				<button class="np-btn np-btn-accent" type="submit">Search Jobs</button>
			</form>

			<?php
			$np_latest = get_term_by( 'slug', 'latest-jobs', 'category' );
			$np_latest_link = $np_latest ? get_category_link( $np_latest ) : home_url( '/' );
			?>
			<div class="np-segment" role="group" aria-label="Filter jobs by sector">
				<a class="np-seg is-active" href="<?php echo esc_url( $np_latest_link ); ?>">All Jobs</a>
				<a class="np-seg" href="<?php echo esc_url( add_query_arg( 'np_sector', 'government', $np_latest_link ) ); ?>">Government</a>
				<a class="np-seg" href="<?php echo esc_url( add_query_arg( 'np_sector', 'private', $np_latest_link ) ); ?>">Private</a>
			</div>

			<dl class="np-stats">
				<div><dt>Live listings</dt><dd><?php echo esc_html( number_format_i18n( $np_live_jobs ) ); ?></dd></div>
				<div><dt>States &amp; UTs</dt><dd><?php echo esc_html( $np_states ); ?></dd></div>
				<div><dt>Categories</dt><dd><?php echo esc_html( $np_cats ); ?></dd></div>
			</dl>
		</div>
	</section>

	<?php np_ad_slot( 'home_hero', 'np-ad-billboard' ); ?>

	<!-- ============ LIVE TICKER ============ -->
	<?php np_render_ticker(); ?>

	<!-- ============ 2. QUICK CATEGORY TILES ============ -->
	<section class="np-section">
		<header class="np-section-head">
			<h2>Browse by category</h2>
		</header>
		<div class="np-tiles">
			<?php foreach ( np_main_sections() as $name => $slug ) :
				$t = get_term_by( 'slug', $slug, 'category' );
				$l = $t ? get_category_link( $t ) : '#';
				$c = $t ? (int) $t->count : 0; ?>
				<a class="np-tile" href="<?php echo esc_url( $l ); ?>">
					<span class="np-tile-ico"><?php echo np_icon( np_section_icon( $slug ) ); ?></span>
					<span class="np-tile-name"><?php echo esc_html( $name ); ?></span>
					<span class="np-tile-count"><?php echo esc_html( number_format_i18n( $c ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<?php np_ad_slot( 'home_top', 'np-ad-leaderboard' ); ?>

	<!-- ============ 3. TRENDING JOBS ============ -->
	<section class="np-section">
		<header class="np-section-head">
			<h2>Trending this month</h2>
			<a class="np-section-link" href="<?php echo esc_url( $np_latest_link ); ?>">
				View all <?php echo np_icon( 'chevron' ); ?></a>
		</header>

		<?php $np_trending = np_trending_query( 6 ); ?>
		<?php if ( $np_trending->have_posts() ) : ?>
			<div class="np-cards">
				<?php while ( $np_trending->have_posts() ) : $np_trending->the_post();
					$np_id   = get_the_ID();
					$np_last = get_post_meta( $np_id, '_np_last_date', true ); ?>
					<a class="np-card" href="<?php the_permalink(); ?>">
						<span class="np-card-top">
							<?php echo np_sector_badge( $np_id ); ?>
							<span class="np-card-views"><?php echo np_icon( 'bolt' ); ?>
								<?php echo esc_html( number_format_i18n( np_get_views( $np_id ) ) ); ?> views</span>
						</span>
						<span class="np-card-title"><?php the_title(); ?></span>
						<span class="np-card-meta">
							<span><?php echo np_icon( 'pin' ); ?><?php echo esc_html( np_location_text( $np_id ) ); ?></span>
							<?php if ( $np_last ) : ?>
								<span class="np-red"><?php echo np_icon( 'calendar' ); ?>Last date: <?php echo esc_html( $np_last ); ?></span>
							<?php endif; ?>
						</span>
					</a>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		<?php else : ?>
			<div class="np-empty"><?php echo np_icon( 'bolt' ); ?><p>Trending jobs appear here once listings start collecting views.</p></div>
		<?php endif; ?>
	</section>

	<!-- ============ 4. BROWSE BY STATE ============ -->
	<section class="np-section np-states" id="npStates">
		<header class="np-section-head">
			<h2>Browse by state</h2>
		</header>

		<div class="np-states-bar">
			<label class="np-state-select">
				<span class="screen-reader-text">Select your state</span>
				<select id="npStateJump" aria-label="Select your state">
					<option value="">Select your state</option>
					<?php foreach ( np_locations() as $slug => $label ) :
						$t = get_term_by( 'slug', $slug, 'category' );
						if ( ! $t ) continue; ?>
						<option value="<?php echo esc_url( get_category_link( $t ) ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php echo np_icon( 'chevrondown' ); ?>
			</label>

			<?php
			$np_ai = get_term_by( 'slug', 'all-india', 'category' );
			$np_us = get_term_by( 'slug', 'usa-jobs', 'category' );
			?>
			<div class="np-states-cta">
				<a class="np-btn np-btn-primary" href="<?php echo esc_url( $np_ai ? get_category_link( $np_ai ) : '#' ); ?>">
					<?php echo np_icon( 'globe' ); ?> All India Jobs</a>
				<a class="np-btn np-btn-outline" href="<?php echo esc_url( $np_us ? get_category_link( $np_us ) : '#' ); ?>">
					<?php echo np_icon( 'globe' ); ?> USA Jobs</a>
			</div>
		</div>

		<?php
		/* All 36 state/UT buttons stay visible at all times — the v2.4
		   "View All" toggle is not coming back, and the 2-column mobile
		   grid from the v2.5 fix is preserved. */
		$np_locs = np_locations();
		array_shift( $np_locs ); // All India already has its own button above
		?>
		<div class="np-state-grid">
			<?php foreach ( $np_locs as $slug => $label ) :
				$t = get_term_by( 'slug', $slug, 'category' );
				$l = $t ? get_category_link( $t ) : '#'; ?>
				<a class="np-state-btn" href="<?php echo esc_url( $l ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</div>
	</section>

	<?php np_ad_slot( 'home_infeed', 'np-ad-infeed' ); ?>

	<!-- ============ 5. FREE CAREER TOOLS ============ -->
	<?php
	/**
	 * SCOPE NOTE (see README v3.0 and the brief, section 5):
	 * these four tools do not exist anywhere in the theme. This is the
	 * card UI only — each card links to a placeholder page. No backend
	 * logic is implemented until the scope is confirmed.
	 */
	$np_tools = array(
		array( 'Resume Maker',   'pen',      'Build a clean, ATS-friendly resume in minutes.',   '/tools/resume-maker/' ),
		array( 'Photo Resizer',  'image',    'Resize a photo to any exam form specification.',   '/tools/photo-resizer/' ),
		array( 'Signature Maker','pen',      'Create a signature image in the required size.',   '/tools/signature-maker/' ),
		array( 'PDF Compressor', 'compress', 'Shrink a PDF below the upload limit of any form.', '/tools/pdf-compressor/' ),
	);
	?>
	<section class="np-section np-tools-section">
		<header class="np-section-head">
			<h2>Free career tools</h2>
			<p class="np-section-sub">Everything you need while filling an application form.</p>
		</header>
		<div class="np-tools">
			<?php foreach ( $np_tools as $tool ) : ?>
				<a class="np-tool" href="<?php echo esc_url( home_url( $tool[3] ) ); ?>">
					<span class="np-tool-ico"><?php echo np_icon( $tool[1] ); ?></span>
					<span class="np-tool-body">
						<span class="np-tool-name"><?php echo esc_html( $tool[0] ); ?></span>
						<span class="np-tool-desc"><?php echo esc_html( $tool[2] ); ?></span>
					</span>
					<?php echo np_icon( 'chevron', 'np-i-end' ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- ============ 6. LATEST JOBS ============ -->
	<section class="np-section">
		<header class="np-section-head">
			<h2>Latest jobs</h2>
			<a class="np-section-link" href="<?php echo esc_url( $np_latest_link ); ?>">
				View all <?php echo np_icon( 'chevron' ); ?></a>
		</header>

		<?php
		$np_latest_q = new WP_Query( array(
			'posts_per_page'      => 8,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		) );
		np_render_job_table( $np_latest_q, array(
			'native_ad_after' => 4,
			'ad_slot'         => 'home_infeed',
			'paginate'        => false,
		) );
		?>
	</section>

	<!-- ============ 7. APP + CHANNELS BAND ============ -->
	<section class="np-appbar">
		<div class="np-appbar-info">
			<span class="np-appbar-ico"><?php echo np_icon( 'phone' ); ?></span>
			<div>
				<h2 class="np-appbar-title">Get the NaukriPatra app</h2>
				<p class="np-appbar-sub">Be the first to know when a new notification goes live.</p>
			</div>
		</div>
		<div class="np-appbar-btns">
			<a class="np-btn np-btn-accent" href="<?php echo esc_url( $np_social['playstore'] ); ?>" target="_blank" rel="noopener">
				<?php echo np_icon( 'download' ); ?> Download App</a>
			<a class="np-btn np-btn-outline-light" href="<?php echo esc_url( $np_social['whatsapp'] ); ?>" target="_blank" rel="noopener">
				<?php echo np_icon( 'chat' ); ?> WhatsApp Channel</a>
			<a class="np-btn np-btn-outline-light" href="<?php echo esc_url( $np_social['telegram'] ); ?>" target="_blank" rel="noopener">
				<?php echo np_icon( 'send' ); ?> Telegram Channel</a>
		</div>
	</section>

	<?php np_ad_slot( 'home_footer', 'np-ad-leaderboard' ); ?>

</div>
<?php
get_footer();
