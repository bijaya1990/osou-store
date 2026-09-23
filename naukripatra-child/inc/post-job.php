<?php
/**
 * NaukriPatra — "Post a Job" submission form (v4.0)
 * =====================================================================
 * Until now the header's "Post a Job" button pointed at /contact-us/,
 * which on most installs does not exist — so it opened a blank page.
 * There was no form behind it at all.
 *
 * This module supplies the real thing:
 *   - a shortcode, [naukripatra_post_job], that renders the form
 *   - a page holding it, created automatically on theme activation
 *   - submission handling that creates a PENDING post for review
 *
 * Nothing is ever published straight to the site. A submission lands in
 * Posts > Pending exactly like a draft, with every Job Details field
 * already filled in, so an editor only has to read it and hit Publish.
 *
 * Reuses the existing meta keys (_np_qualification, _np_last_date,
 * _np_posts_count, _np_organization, _np_salary, _np_job_sector,
 * _np_apply_url ...), so a submitted job behaves like any other post
 * everywhere: lists, schema, REST, the app.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Public contact address shown on the form and used for submissions. */
function np_contact_email() {
	/**
	 * Filterable so the address can be changed without editing the
	 * theme: add_filter( 'np_contact_email', fn() => 'jobs@example.com' );
	 */
	return apply_filters( 'np_contact_email', 'info@naukripatra.in' );
}

/* =========================================================
 * 1. THE PAGE
 * ======================================================= */

/** Create the Post a Job page on activation if it is not there yet. */
function np_create_post_job_page() {
	$existing = (int) get_option( 'np_post_job_page_id' );
	if ( $existing && 'page' === get_post_type( $existing ) && 'trash' !== get_post_status( $existing ) ) {
		return $existing;
	}

	// Someone may have made the page by hand already — find it first
	// rather than creating a duplicate.
	$found = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'pending' ),
		'posts_per_page' => 1,
		'name'           => 'post-a-job',
		'fields'         => 'ids',
	) );
	if ( $found ) {
		update_option( 'np_post_job_page_id', (int) $found[0] );
		return (int) $found[0];
	}

	$id = wp_insert_post( array(
		'post_title'   => 'Post a Job',
		'post_name'    => 'post-a-job',
		'post_content' => '[naukripatra_post_job]',
		'post_status'  => 'publish',
		'post_type'    => 'page',
	) );

	if ( $id && ! is_wp_error( $id ) ) {
		update_option( 'np_post_job_page_id', (int) $id );
		return (int) $id;
	}
	return 0;
}
add_action( 'after_switch_theme', 'np_create_post_job_page' );
add_action( 'admin_init', function () {
	if ( ! get_option( 'np_post_job_page_id' ) ) np_create_post_job_page();
} );

/** URL of the Post a Job page; falls back to the home page. */
function np_post_job_page_url() {
	$id = (int) get_option( 'np_post_job_page_id' );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return get_permalink( $id );
	}
	return home_url( '/post-a-job/' );
}

/* =========================================================
 * 2. SUBMISSION HANDLING
 * =======================================================
 * Runs on template_redirect so a successful submission can redirect
 * back with ?np_job=sent — the post/redirect/get pattern, which stops
 * a browser refresh from filing the same job twice.
 */
add_action( 'template_redirect', function () {
	if ( empty( $_POST['np_job_form'] ) ) return;
	if ( ! isset( $_POST['np_job_nonce'] ) || ! wp_verify_nonce( $_POST['np_job_nonce'], 'np_job_submit' ) ) {
		np_job_redirect( 'nonce' );
	}

	// Honeypot: a real person never fills a field they cannot see.
	if ( ! empty( $_POST['np_website'] ) ) {
		np_job_redirect( 'sent' ); // fail silently, bots learn nothing
	}

	// One submission per IP per 10 minutes.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	$key = 'np_job_' . md5( $ip );
	if ( get_transient( $key ) ) {
		np_job_redirect( 'slow' );
	}

	$f = function ( $name ) {
		return isset( $_POST[ $name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) : '';
	};

	$title = $f( 'np_title' );
	$email = sanitize_email( isset( $_POST['np_email'] ) ? wp_unslash( $_POST['np_email'] ) : '' );
	$desc  = isset( $_POST['np_description'] ) ? wp_unslash( $_POST['np_description'] ) : '';

	if ( '' === $title || '' === trim( wp_strip_all_tags( $desc ) ) || ! is_email( $email ) ) {
		np_job_redirect( 'missing' );
	}

	// Description: plain paragraphs and links only. wp_kses with a tiny
	// allow-list, because this is untrusted input from the public.
	$desc = wp_kses( $desc, array(
		'p' => array(), 'br' => array(), 'strong' => array(), 'em' => array(), 'ul' => array(),
		'ol' => array(), 'li' => array(), 'a' => array( 'href' => array(), 'target' => array() ),
	) );
	if ( false === strpos( $desc, '<p' ) ) {
		$desc = wpautop( $desc );
	}

	$post_id = wp_insert_post( array(
		'post_title'   => $title,
		'post_content' => $desc,
		'post_status'  => 'pending',   // never published automatically
		'post_type'    => 'post',
		'post_author'  => 0,
	), true );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		np_job_redirect( 'error' );
	}

	/* ---- Job Details meta, same keys the meta box uses ---- */
	$sector = $f( 'np_sector' );
	update_post_meta( $post_id, '_np_job_sector',
		array_key_exists( $sector, np_job_sectors() ) ? $sector : 'private' );

	$emp = $f( 'np_employment_type' );
	update_post_meta( $post_id, '_np_employment_type',
		array_key_exists( $emp, np_employment_types() ) ? $emp : 'FULL_TIME' );

	update_post_meta( $post_id, '_np_organization',  $f( 'np_organization' ) );
	update_post_meta( $post_id, '_np_qualification', $f( 'np_qualification' ) );
	update_post_meta( $post_id, '_np_last_date',     $f( 'np_last_date' ) );
	update_post_meta( $post_id, '_np_posts_count',   $f( 'np_posts_count' ) );
	update_post_meta( $post_id, '_np_salary',        $f( 'np_salary' ) );
	update_post_meta( $post_id, '_np_locality',      $f( 'np_locality' ) );
	update_post_meta( $post_id, '_np_apply_url',
		esc_url_raw( isset( $_POST['np_apply_url'] ) ? wp_unslash( $_POST['np_apply_url'] ) : '' ) );

	// Who sent it — for the editor only, never shown on the site.
	update_post_meta( $post_id, '_np_submitter_email', $email );
	update_post_meta( $post_id, '_np_submitted_ip', $ip );

	// Keep the derived fields in step with the rest of the theme.
	np_sync_last_date_ts( $post_id );
	np_sync_apply_url( $post_id );
	np_sync_notification_url( $post_id );

	/* ---- Categories: the section and the state ---- */
	$cats = array();
	$section = $f( 'np_section' );
	if ( in_array( $section, np_main_sections(), true ) ) {
		$t = get_term_by( 'slug', $section, 'category' );
		if ( $t ) $cats[] = (int) $t->term_id;
	}
	$state = $f( 'np_state' );
	if ( array_key_exists( $state, np_locations() ) ) {
		$t = get_term_by( 'slug', $state, 'category' );
		if ( $t ) $cats[] = (int) $t->term_id;
	}
	if ( $cats ) wp_set_post_categories( $post_id, $cats );

	set_transient( $key, 1, 10 * MINUTE_IN_SECONDS );

	/* ---- Tell the site owner there is something to review ---- */
	/* Submissions go to the public contact inbox, with the WordPress
	   admin address as a fallback if that is ever emptied. */
	$admin = np_contact_email();
	if ( ! is_email( $admin ) ) $admin = get_option( 'admin_email' );
	if ( $admin ) {
		wp_mail(
			$admin,
			'New job submitted for review: ' . $title,
			"A job has been submitted through the Post a Job form.\n\n"
			. "Title: {$title}\n"
			. 'Organisation: ' . $f( 'np_organization' ) . "\n"
			. "Submitted by: {$email}\n\n"
			. "It is waiting in Posts > Pending. Nothing is published until you approve it:\n"
			. admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . "\n",
			array( 'Reply-To: ' . $email )
		);
	}

	np_job_redirect( 'sent' );
} );

/** Redirect back to the form with a status, then stop. */
function np_job_redirect( $status ) {
	$url = add_query_arg( 'np_job', $status, np_post_job_page_url() );
	wp_safe_redirect( $url . '#np-job-form' );
	exit;
}

/* =========================================================
 * 3. THE FORM
 * ======================================================= */
add_shortcode( 'naukripatra_post_job', 'np_post_job_form' );

function np_post_job_form() {
	$status = isset( $_GET['np_job'] ) ? sanitize_key( wp_unslash( $_GET['np_job'] ) ) : '';

	$messages = array(
		'sent'    => array( 'ok',  'Thank you. Your job has been submitted and is now waiting for review. It goes live once our team approves it, usually within 24 hours.' ),
		'missing' => array( 'bad', 'Please fill in the job title, the description and a valid email address.' ),
		'slow'    => array( 'bad', 'You have just submitted a job. Please wait a few minutes before sending another one.' ),
		'nonce'   => array( 'bad', 'That form had expired. Please fill it in and send it again.' ),
		'error'   => array( 'bad', 'Something went wrong while saving. Please try again.' ),
	);

	ob_start();
	?>
	<div class="np-jobform-wrap" id="np-job-form">

		<?php if ( isset( $messages[ $status ] ) ) : ?>
			<div class="np-formnote np-formnote-<?php echo esc_attr( $messages[ $status ][0] ); ?>">
				<?php echo np_icon( 'ok' === $messages[ $status ][0] ? 'check' : 'bolt' ); ?>
				<p><?php echo esc_html( $messages[ $status ][1] ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( 'sent' === $status ) : ?>
			<p class="np-jobform-contact">
				<?php echo np_icon( 'chat' ); ?>
				Need to change something, or have a question? Write to
				<a href="mailto:<?php echo esc_attr( np_contact_email() ); ?>"><?php echo esc_html( np_contact_email() ); ?></a>.
			</p>
		<?php endif; ?>

		<?php if ( 'sent' !== $status ) : ?>
			<p class="np-jobform-intro">
				Fill this in to list a vacancy on NaukriPatra. Every submission is read by our
				team before it goes live, so nothing is published automatically.
			</p>

			<p class="np-jobform-contact">
				<?php echo np_icon( 'chat' ); ?>
				Questions, or would you rather send the details by email? Write to
				<a href="mailto:<?php echo esc_attr( np_contact_email() ); ?>"><?php echo esc_html( np_contact_email() ); ?></a>.
			</p>

			<form class="np-jobform" method="post" action="<?php echo esc_url( np_post_job_page_url() ); ?>">
				<?php wp_nonce_field( 'np_job_submit', 'np_job_nonce' ); ?>
				<input type="hidden" name="np_job_form" value="1">

				<?php /* Honeypot — hidden from people, irresistible to bots. */ ?>
				<div class="np-hp" aria-hidden="true">
					<label>Website<input type="text" name="np_website" tabindex="-1" autocomplete="off"></label>
				</div>

				<h3 class="np-jobform-legend">About the job</h3>
				<div class="np-jobform-grid">
					<label class="np-field np-field-wide">
						<span>Job title <b>*</b></span>
						<input type="text" name="np_title" required maxlength="180"
							placeholder="e.g. Junior Accounts Officer">
					</label>

					<label class="np-field">
						<span>Organisation / Company</span>
						<input type="text" name="np_organization" maxlength="180"
							placeholder="e.g. State Bank of India">
					</label>

					<label class="np-field">
						<span>Job sector</span>
						<select name="np_sector">
							<?php foreach ( np_job_sectors() as $val => $label ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $val, 'private' ); ?>>
									<?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>

					<label class="np-field">
						<span>Section</span>
						<select name="np_section">
							<?php foreach ( np_main_sections() as $name => $slug ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>

					<label class="np-field">
						<span>State / UT</span>
						<select name="np_state">
							<?php foreach ( np_locations() as $slug => $label ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>

					<label class="np-field">
						<span>City / Locality</span>
						<input type="text" name="np_locality" maxlength="120" placeholder="e.g. Bhubaneswar">
					</label>

					<label class="np-field">
						<span>Employment type</span>
						<select name="np_employment_type">
							<?php foreach ( np_employment_types() as $val => $label ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</div>

				<h3 class="np-jobform-legend">Eligibility &amp; dates</h3>
				<div class="np-jobform-grid">
					<label class="np-field">
						<span>Qualification</span>
						<input type="text" name="np_qualification" maxlength="180"
							placeholder="e.g. Graduate in any discipline">
					</label>

					<label class="np-field">
						<span>Number of posts</span>
						<input type="text" name="np_posts_count" maxlength="40" placeholder="e.g. 25">
					</label>

					<label class="np-field">
						<span>Last date to apply</span>
						<input type="text" name="np_last_date" maxlength="60" placeholder="e.g. 25 Dec 2026">
					</label>

					<label class="np-field">
						<span>Salary / pay scale</span>
						<input type="text" name="np_salary" maxlength="180"
							placeholder="e.g. Rs 25,000 - 40,000 per month">
					</label>

					<label class="np-field np-field-wide">
						<span>Official application link</span>
						<input type="url" name="np_apply_url" maxlength="300" placeholder="https://...">
					</label>
				</div>

				<h3 class="np-jobform-legend">Details</h3>
				<div class="np-jobform-grid">
					<label class="np-field np-field-wide">
						<span>Job description <b>*</b></span>
						<textarea name="np_description" rows="9" required
							placeholder="Responsibilities, eligibility, selection process, how to apply..."></textarea>
						<em class="np-field-help">Plain text works fine. Basic formatting and links are kept.</em>
					</label>

					<label class="np-field np-field-wide">
						<span>Your email <b>*</b></span>
						<input type="email" name="np_email" required maxlength="180"
							placeholder="you@company.com">
						<em class="np-field-help">Only so we can reach you about this listing. It is never shown on the site.</em>
					</label>
				</div>

				<div class="np-jobform-submit">
					<button class="np-btn np-btn-accent np-btn-lg" type="submit">
						Submit for review <?php echo np_icon( 'chevron' ); ?>
					</button>
					<span class="np-jobform-note">Nothing is published until our team approves it.</span>
				</div>
			</form>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/* =========================================================
 * 4. SHOW THE SUBMITTER IN THE ADMIN LIST
 * ======================================================= */
add_action( 'add_meta_boxes', function () {
	global $post;
	if ( ! $post || ! get_post_meta( $post->ID, '_np_submitter_email', true ) ) return;

	add_meta_box( 'np_submission', 'Submitted through Post a Job', function ( $post ) {
		$email = get_post_meta( $post->ID, '_np_submitter_email', true );
		$ip    = get_post_meta( $post->ID, '_np_submitted_ip', true );
		echo '<p><strong>From:</strong> <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>';
		if ( $ip ) echo '<p><strong>IP:</strong> ' . esc_html( $ip ) . '</p>';
		echo '<p style="color:#646970">This job came from the public form. Check the details before publishing.</p>';
	}, 'post', 'side', 'high' );
} );
