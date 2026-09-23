<?php
/**
 * NaukriPatra — Single job post (NEW in v3.0)
 * =====================================================================
 * v2.8 had no single.php: the single-post layout was patched onto the
 * parent GeneratePress template through generate_before_content and
 * generate_after_entry_title plus a pile of CSS. This template takes
 * direct control of the layout instead. The hooks still exist in
 * functions.php for any other route into the parent template, but they
 * stand down while this template is running (np_single_template_active)
 * so nothing is printed twice.
 *
 * Layout: main content ~68% + sticky right sidebar ~32%.
 * The sticky bug from style.css line ~348 is fixed in the new CSS:
 * the sticky container now has a max-height and its own scroll, the
 * offset follows the real sticky-header height via --np-header-h, and
 * no ancestor sets overflow:hidden (which silently kills sticky).
 *
 * The JobPosting JSON-LD in inc/features.php is untouched — it runs on
 * wp_head and reads the same meta fields, whose shape has not changed.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Tell the v2.8 hooks to stand down — this template owns the layout.
$GLOBALS['np_single_template'] = true;

get_header();

while ( have_posts() ) :
	the_post();

	$np_id     = get_the_ID();
	$np_qual   = get_post_meta( $np_id, '_np_qualification', true );
	$np_last   = get_post_meta( $np_id, '_np_last_date', true );
	$np_total  = get_post_meta( $np_id, '_np_posts_count', true );
	$np_salary = get_post_meta( $np_id, '_np_salary', true );
	$np_org    = get_post_meta( $np_id, '_np_organization', true );
	$np_fee    = get_post_meta( $np_id, '_np_app_fee', true );
	/* The Apply / Official Link field when the editor filled it,
	   otherwise the official link detected in the post content, so the
	   button works on old posts too instead of sitting there dead. */
	$np_apply  = np_get_apply_url( $np_id );
	/* The official notification PDF, from the field if filled, else the
	   one detected in the post content. */
	$np_notif  = np_get_notification_url( $np_id );
	$np_emp    = np_employment_types();
	$np_emp_l  = get_post_meta( $np_id, '_np_employment_type', true );
	$np_emp_l  = isset( $np_emp[ $np_emp_l ] ) ? $np_emp[ $np_emp_l ] : '';
	$np_locs   = np_get_job_locations( $np_id );

	/* Org initials for the header card avatar. */
	$np_source   = $np_org ? $np_org : get_the_title();
	$np_words    = preg_split( '/\s+/', trim( wp_strip_all_tags( $np_source ) ) );
	$np_initials = '';
	foreach ( $np_words as $w ) {
		if ( '' === $w ) continue;
		$np_initials .= strtoupper( mb_substr( $w, 0, 1 ) );
		if ( strlen( $np_initials ) >= 2 ) break;
	}

	/* Days left — np_days_left() reads the numeric deadline mirror that
	   is parsed with np_schema_parse_date(), the same parser the
	   JobPosting schema uses, so this countdown and validThrough can
	   never disagree. null means no usable closing date. */
	$np_days_left = np_days_left( $np_id );
	?>

	<div class="np-single">

		<?php np_breadcrumbs(); ?>
		<?php np_ad_slot( 'single_top', 'np-ad-leaderboard' ); ?>

		<div class="np-single-grid">

			<!-- ================= MAIN ================= -->
			<main class="np-single-main">

				<!-- Job header card -->
				<header class="np-jobhead">
					<div class="np-jobhead-top">
						<span class="np-avatar" aria-hidden="true"><?php echo esc_html( $np_initials ); ?></span>
						<div class="np-jobhead-text">
							<div class="np-jobhead-badges">
								<?php echo np_sector_badge( $np_id ); ?>
								<?php if ( $np_emp_l ) : ?><span class="np-tag"><?php echo esc_html( $np_emp_l ); ?></span><?php endif; ?>
							</div>
							<h1 class="np-jobhead-title"><?php the_title(); ?></h1>
							<?php if ( $np_org ) : ?>
								<p class="np-jobhead-org"><?php echo np_icon( 'building' ); ?><?php echo esc_html( $np_org ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<ul class="np-chips">
						<?php if ( $np_qual ) : ?>
							<li><?php echo np_icon( 'cap' ); ?><span><em>Qualification</em><strong><?php echo esc_html( $np_qual ); ?></strong></span></li>
						<?php endif; ?>
						<?php if ( $np_total ) : ?>
							<li><?php echo np_icon( 'users' ); ?><span><em>Vacancies</em><strong><?php echo esc_html( $np_total ); ?></strong></span></li>
						<?php endif; ?>
						<li><?php echo np_icon( 'pin' ); ?><span><em>Location</em><strong><?php echo esc_html( np_location_text( $np_id ) ); ?></strong></span></li>
						<?php if ( $np_last ) : ?>
							<li class="np-chip-alert"><?php echo np_icon( 'calendar' ); ?><span><em>Last date</em><strong><?php echo esc_html( $np_last ); ?></strong></span></li>
						<?php endif; ?>
					</ul>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<?php
					/**
					 * Core Web Vitals — LCP (kept from v2.8): this is the page's
					 * Largest Contentful Paint candidate, so it is loaded eagerly
					 * with a high fetch priority and the LiteSpeed Cache skip
					 * attribute. Never lazy-load this one image.
					 */
					?>
					<figure class="np-featured">
						<?php the_post_thumbnail( 'large', array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'data-no-lazy'  => '1',
						) ); ?>
					</figure>
				<?php endif; ?>

				<!-- Important dates -->
				<?php if ( $np_last || get_the_date() ) : ?>
					<section class="np-block">
						<h2 class="np-block-title"><?php echo np_icon( 'calendar' ); ?> Important Dates</h2>
						<table class="np-dates">
							<tbody>
								<tr>
									<th scope="row">Notification published</th>
									<td><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></td>
								</tr>
								<?php if ( $np_last ) : ?>
									<tr>
										<th scope="row">Last date to apply</th>
										<td class="np-red"><strong><?php echo esc_html( $np_last ); ?></strong></td>
									</tr>
								<?php endif; ?>
								<?php if ( null !== $np_days_left ) : ?>
									<tr>
										<th scope="row">Status</th>
										<td>
											<?php if ( $np_days_left > 0 ) : ?>
												<span class="np-pill np-pill-live"><?php echo (int) $np_days_left; ?> days left</span>
											<?php elseif ( 0 === $np_days_left ) : ?>
												<span class="np-pill np-pill-live">Closes today</span>
											<?php else : ?>
												<span class="np-pill np-pill-closed">Closed</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</section>
				<?php endif; ?>

				<!-- Eligibility + application fee -->
				<?php if ( $np_qual || $np_total || $np_salary || $np_emp_l || $np_fee ) : ?>
					<section class="np-block">
						<h2 class="np-block-title"><?php echo np_icon( 'check' ); ?> Eligibility &amp; Application Fee</h2>
						<div class="np-kv">
							<?php if ( $np_qual ) : ?>
								<div><span>Qualification</span><strong><?php echo esc_html( $np_qual ); ?></strong></div>
							<?php endif; ?>
							<?php if ( $np_total ) : ?>
								<div><span>Total vacancies</span><strong><?php echo esc_html( $np_total ); ?></strong></div>
							<?php endif; ?>
							<?php if ( $np_emp_l ) : ?>
								<div><span>Employment type</span><strong><?php echo esc_html( $np_emp_l ); ?></strong></div>
							<?php endif; ?>
							<?php if ( $np_salary ) : ?>
								<div><span>Pay scale</span><strong><?php echo esc_html( $np_salary ); ?></strong></div>
							<?php endif; ?>
							<?php if ( $np_fee ) : ?>
								<div><span>Application fee</span><strong><?php echo esc_html( $np_fee ); ?></strong></div>
							<?php endif; ?>
						</div>
					</section>
				<?php endif; ?>

				<?php np_ad_slot( 'single_mid', 'np-ad-rect' ); ?>

				<!-- The post body (Quick Details box, in-article ad and share
				     buttons are still injected by the the_content filter) -->
				<section class="np-block np-content entry-content">
					<?php the_content(); ?>
				</section>

				<!-- How to apply -->
				<section class="np-block np-howto" id="np-how-to-apply">
					<h2 class="np-block-title"><?php echo np_icon( 'file' ); ?> How to Apply</h2>
					<ol class="np-steps">
						<li><span>1</span><p>Read the full official notification above and confirm you meet every eligibility condition.</p></li>
						<li><span>2</span><p>Keep your scanned photograph, signature and certificates ready in the sizes the form asks for.</p></li>
						<li><span>3</span><p>Fill the online application form on the official website and pay the fee if one applies.</p></li>
						<li><span>4</span><p>Submit before the last date and save a printed copy of the confirmation page for your records.</p></li>
					</ol>
					<?php if ( $np_apply || $np_notif ) : ?>
						<div class="np-howto-actions">
							<?php if ( $np_apply ) : ?>
								<a class="np-btn np-btn-accent np-btn-lg" href="<?php echo esc_url( $np_apply ); ?>"
									target="_blank" rel="noopener nofollow">
									<?php echo np_icon( 'link' ); ?> Go to the official application page</a>
							<?php endif; ?>
							<?php if ( $np_notif ) : ?>
								<a class="np-btn np-btn-outline np-btn-lg" href="<?php echo esc_url( $np_notif ); ?>"
									target="_blank" rel="noopener nofollow">
									<?php echo np_icon( 'file' ); ?> Read the official notification</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</section>

				<!-- Similar jobs -->
				<?php
				$np_cats = wp_get_post_categories( $np_id );
				if ( $np_cats ) :
					$np_similar = new WP_Query( array(
						'category__in'        => $np_cats,
						'post__not_in'        => array( $np_id ),
						'posts_per_page'      => 4,
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
					) );
					if ( $np_similar->have_posts() ) : ?>
						<section class="np-block">
							<h2 class="np-block-title"><?php echo np_icon( 'briefcase' ); ?> Similar Jobs</h2>
							<div class="np-cards np-cards-2">
								<?php while ( $np_similar->have_posts() ) : $np_similar->the_post();
									$np_sid  = get_the_ID();
									$np_slast = get_post_meta( $np_sid, '_np_last_date', true ); ?>
									<a class="np-card" href="<?php the_permalink(); ?>">
										<span class="np-card-top"><?php echo np_sector_badge( $np_sid ); ?></span>
										<span class="np-card-title"><?php the_title(); ?></span>
										<span class="np-card-meta">
											<span><?php echo np_icon( 'pin' ); ?><?php echo esc_html( np_location_text( $np_sid ) ); ?></span>
											<?php if ( $np_slast ) : ?>
												<span class="np-red"><?php echo np_icon( 'calendar' ); ?>Last date: <?php echo esc_html( $np_slast ); ?></span>
											<?php endif; ?>
										</span>
									</a>
								<?php endwhile; wp_reset_postdata(); ?>
							</div>
						</section>
					<?php endif;
				endif; ?>

				<?php np_ad_slot( 'after_content', 'np-ad-after' ); ?>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</main>

			<!-- ================= SIDEBAR ================= -->
			<aside class="np-single-side" aria-label="Job summary">
				<div class="np-sticky">

					<!-- Apply Now CTA -->
					<div class="np-side-card np-apply-card">
						<?php if ( null !== $np_days_left ) : ?>
							<?php if ( $np_days_left > 0 ) : ?>
								<p class="np-apply-count"><strong><?php echo (int) $np_days_left; ?></strong> days left to apply</p>
							<?php elseif ( 0 === $np_days_left ) : ?>
								<p class="np-apply-count"><strong>Today</strong> is the last day</p>
							<?php else : ?>
								<p class="np-apply-count np-apply-closed">Applications have closed</p>
							<?php endif; ?>
						<?php else : ?>
							<p class="np-apply-count">Check the notification for the deadline</p>
						<?php endif; ?>

						<div class="np-apply-actions<?php echo $np_notif ? ' np-apply-actions-2' : ''; ?>">
							<a class="np-btn np-btn-accent np-btn-lg"
								href="<?php echo esc_url( $np_apply ? $np_apply : '#np-how-to-apply' ); ?>"
								<?php echo $np_apply ? 'target="_blank" rel="noopener nofollow"' : ''; ?>>
								Apply Now <?php echo np_icon( 'chevron' ); ?></a>

							<?php if ( $np_notif ) : ?>
								<a class="np-btn np-btn-outline-light np-btn-lg"
									href="<?php echo esc_url( $np_notif ); ?>"
									target="_blank" rel="noopener nofollow">
									<?php echo np_icon( 'file' ); ?> Notification</a>
							<?php endif; ?>
						</div>

						<?php if ( $np_last ) : ?>
							<p class="np-apply-note">Last date: <strong><?php echo esc_html( $np_last ); ?></strong></p>
						<?php endif; ?>
					</div>

					<!-- Quick info -->
					<div class="np-side-card">
						<h3 class="np-side-title">Quick Info</h3>
						<dl class="np-quickinfo">
							<?php if ( $np_org ) : ?><div><dt>Organisation</dt><dd><?php echo esc_html( $np_org ); ?></dd></div><?php endif; ?>
							<div><dt>Sector</dt><dd><?php echo esc_html( np_job_sectors()[ np_get_sector( $np_id ) ] ); ?></dd></div>
							<?php if ( $np_qual ) : ?><div><dt>Qualification</dt><dd><?php echo esc_html( $np_qual ); ?></dd></div><?php endif; ?>
							<?php if ( $np_total ) : ?><div><dt>Vacancies</dt><dd><?php echo esc_html( $np_total ); ?></dd></div><?php endif; ?>
							<?php if ( $np_salary ) : ?><div><dt>Pay scale</dt><dd><?php echo esc_html( $np_salary ); ?></dd></div><?php endif; ?>
							<div><dt>Location</dt><dd><?php echo esc_html( np_location_text( $np_id ) ); ?></dd></div>
							<div><dt>Published</dt><dd><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></dd></div>
						</dl>
						<?php if ( $np_locs ) : ?>
							<div class="np-side-locs">
								<?php foreach ( $np_locs as $np_l ) : ?>
									<a class="np-loc" href="<?php echo esc_url( $np_l['link'] ); ?>">
										<?php echo np_icon( 'pin' ); ?><?php echo esc_html( $np_l['name'] ); ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<!-- Share -->
					<div class="np-side-card">
						<h3 class="np-side-title">Share this job</h3>
						<?php
						$np_u = rawurlencode( get_permalink() );
						$np_t = rawurlencode( get_the_title() );
						?>
						<div class="np-side-share">
							<a class="np-sh np-sh-wa" target="_blank" rel="noopener nofollow"
								href="https://api.whatsapp.com/send?text=<?php echo $np_t; ?>%20-%20<?php echo $np_u; ?>">WhatsApp</a>
							<a class="np-sh np-sh-tg" target="_blank" rel="noopener nofollow"
								href="https://t.me/share/url?url=<?php echo $np_u; ?>&amp;text=<?php echo $np_t; ?>">Telegram</a>
							<a class="np-sh np-sh-fb" target="_blank" rel="noopener nofollow"
								href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $np_u; ?>">Facebook</a>
							<button class="np-sh np-sh-copy" type="button"
								data-np-copy="<?php echo esc_attr( get_permalink() ); ?>">Copy Link</button>
						</div>
					</div>

					<?php np_ad_slot( 'single_side_a', 'np-ad-rect' ); ?>

					<!-- Trending -->
					<?php $np_trend = np_trending_query( 5 ); ?>
					<?php if ( $np_trend->have_posts() ) : ?>
						<div class="np-side-card">
							<h3 class="np-side-title">Trending Jobs</h3>
							<ul class="np-side-list">
								<?php while ( $np_trend->have_posts() ) : $np_trend->the_post(); ?>
									<li><a href="<?php the_permalink(); ?>">
										<?php echo np_icon( 'chevron', 'np-i-lead' ); ?>
										<span><?php the_title(); ?></span></a></li>
								<?php endwhile; wp_reset_postdata(); ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php np_ad_slot( 'single_side_b', 'np-ad-sky' ); ?>
				</div>
			</aside>
		</div>

		<?php np_ad_slot( 'single_footer', 'np-ad-leaderboard' ); ?>
	</div>

	<!-- Sticky mobile apply bar (the sidebar CTA is not visible on phones) -->
	<div class="np-applybar">
		<div class="np-applybar-text">
			<?php if ( null !== $np_days_left && $np_days_left > 0 ) : ?>
				<strong><?php echo (int) $np_days_left; ?> days left</strong>
			<?php elseif ( 0 === $np_days_left ) : ?>
				<strong>Closes today</strong>
			<?php elseif ( null !== $np_days_left ) : ?>
				<strong>Closed</strong>
			<?php else : ?>
				<strong>Apply online</strong>
			<?php endif; ?>
			<?php if ( $np_last ) : ?><span><?php echo esc_html( $np_last ); ?></span><?php endif; ?>
		</div>
		<?php if ( $np_notif ) : ?>
			<a class="np-btn np-btn-outline-light np-applybar-pdf" href="<?php echo esc_url( $np_notif ); ?>"
				target="_blank" rel="noopener nofollow" aria-label="Official notification PDF">
				<?php echo np_icon( 'file' ); ?><span>PDF</span></a>
		<?php endif; ?>
		<a class="np-btn np-btn-accent" href="<?php echo esc_url( $np_apply ? $np_apply : '#np-how-to-apply' ); ?>"
			<?php echo $np_apply ? 'target="_blank" rel="noopener nofollow"' : ''; ?>>Apply Now</a>
	</div>

<?php
endwhile;

get_footer();
