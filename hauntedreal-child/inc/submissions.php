<?php
/**
 * Ghost story submissions.
 *
 * A reader-facing form that writes a `pending` ghost_story. Nothing a
 * visitor submits is ever published without an editor approving it.
 *
 * Spam defence is deliberately JavaScript-free: nonce, honeypot, a
 * time-to-complete floor, and a per-IP rate limit. No third-party captcha,
 * no external request, no privacy footprint.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Are submissions being accepted?
 *
 * @return bool
 */
function hauntedreal_submissions_open() {
	return (bool) get_theme_mod( 'hauntedreal_submissions_open', true );
}

/**
 * Process a posted form before any output happens.
 */
function hauntedreal_handle_submission() {
	if ( ! isset( $_POST['hauntedreal_action'] ) || 'submit_ghost_story' !== $_POST['hauntedreal_action'] ) {
		return;
	}

	if ( ! isset( $_POST['hauntedreal_submit_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hauntedreal_submit_nonce'] ) ), 'hauntedreal_submit_ghost_story' ) ) {
		hauntedreal_submission_redirect( array( __( 'Your session expired before the story could be sent. Please try again.', 'hauntedreal' ) ) );
	}

	if ( ! hauntedreal_submissions_open() ) {
		hauntedreal_submission_redirect( array( __( 'Submissions are closed at the moment. Please check back soon.', 'hauntedreal' ) ) );
	}

	// Honeypot: a field no human ever sees, let alone fills in.
	if ( ! empty( $_POST['hauntedreal_website'] ) ) {
		hauntedreal_submission_redirect( array( __( 'Your submission could not be processed.', 'hauntedreal' ) ) );
	}

	// Nobody writes an account of a haunting in under five seconds.
	$started = isset( $_POST['hauntedreal_started'] ) ? absint( $_POST['hauntedreal_started'] ) : 0;

	if ( $started && ( time() - $started ) < 5 ) {
		hauntedreal_submission_redirect( array( __( 'That was submitted a little too quickly. Please try again.', 'hauntedreal' ) ) );
	}

	if ( hauntedreal_submission_rate_limited() ) {
		hauntedreal_submission_redirect( array( __( 'You have already sent us several stories in the past hour. Please try again later.', 'hauntedreal' ) ) );
	}

	$input = array(
		'title'      => isset( $_POST['hauntedreal_title'] ) ? sanitize_text_field( wp_unslash( $_POST['hauntedreal_title'] ) ) : '',
		'story'      => isset( $_POST['hauntedreal_story'] ) ? sanitize_textarea_field( wp_unslash( $_POST['hauntedreal_story'] ) ) : '',
		'city'       => isset( $_POST['hauntedreal_city'] ) ? sanitize_text_field( wp_unslash( $_POST['hauntedreal_city'] ) ) : '',
		'state'      => isset( $_POST['hauntedreal_state'] ) ? sanitize_text_field( wp_unslash( $_POST['hauntedreal_state'] ) ) : '',
		'place'      => isset( $_POST['hauntedreal_place'] ) ? sanitize_text_field( wp_unslash( $_POST['hauntedreal_place'] ) ) : '',
		'when'       => isset( $_POST['hauntedreal_when'] ) ? sanitize_text_field( wp_unslash( $_POST['hauntedreal_when'] ) ) : '',
		'type'       => isset( $_POST['hauntedreal_type'] ) ? absint( $_POST['hauntedreal_type'] ) : 0,
		'name'       => isset( $_POST['hauntedreal_name'] ) ? sanitize_text_field( wp_unslash( $_POST['hauntedreal_name'] ) ) : '',
		'email'      => isset( $_POST['hauntedreal_email'] ) ? sanitize_email( wp_unslash( $_POST['hauntedreal_email'] ) ) : '',
		'credit'     => isset( $_POST['hauntedreal_credit'] ) ? sanitize_text_field( wp_unslash( $_POST['hauntedreal_credit'] ) ) : 'initials',
		'consent'    => ! empty( $_POST['hauntedreal_consent'] ),
	);

	$errors = hauntedreal_validate_submission( $input );

	if ( $errors ) {
		hauntedreal_submission_redirect( $errors, $input );
	}

	$post_id = wp_insert_post( array(
		'post_type'    => 'ghost_story',
		'post_status'  => 'pending',
		'post_title'   => $input['title'],
		'post_content' => $input['story'],
		'post_excerpt' => wp_trim_words( $input['story'], 34, '…' ),
	), true );

	if ( is_wp_error( $post_id ) ) {
		hauntedreal_submission_redirect(
			array( __( 'Something went wrong saving your story. Please try again.', 'hauntedreal' ) ),
			$input
		);
	}

	// How the witness wants to be credited if the story is published.
	$credit = 'anonymous' === $input['credit']
		? __( 'Anonymous', 'hauntedreal' )
		: hauntedreal_initialize_name( $input['name'], 'full' === $input['credit'] );

	update_post_meta( $post_id, '_hauntedreal_city', $input['city'] );
	update_post_meta( $post_id, '_hauntedreal_state', $input['state'] );
	update_post_meta( $post_id, '_hauntedreal_place', $input['place'] );
	update_post_meta( $post_id, '_hauntedreal_experience_date', $input['when'] );
	update_post_meta( $post_id, '_hauntedreal_witness', $credit );
	update_post_meta( $post_id, '_hauntedreal_status', 'unverified' );
	update_post_meta( $post_id, '_hauntedreal_submitter_email', $input['email'] );

	if ( $input['state'] ) {
		wp_set_object_terms( $post_id, $input['state'], 'us_state', false );
	}

	if ( $input['type'] ) {
		wp_set_object_terms( $post_id, array( $input['type'] ), 'ghost_theme', false );
	}

	hauntedreal_record_submission();
	hauntedreal_notify_editors( $post_id, $input );

	hauntedreal_submission_redirect( array(), array(), true );
}
add_action( 'template_redirect', 'hauntedreal_handle_submission' );

/**
 * Validate the posted values.
 *
 * @param array $input Sanitized input.
 * @return string[] Error messages.
 */
function hauntedreal_validate_submission( $input ) {
	$errors = array();

	if ( mb_strlen( $input['title'] ) < 6 ) {
		$errors[] = __( 'Please give your experience a title of at least six characters.', 'hauntedreal' );
	}

	if ( mb_strlen( $input['story'] ) < 200 ) {
		$errors[] = __( 'Please tell us what happened in at least 200 characters — detail is what makes an account worth reading.', 'hauntedreal' );
	}

	if ( mb_strlen( $input['story'] ) > 20000 ) {
		$errors[] = __( 'That story is longer than our form accepts. Please email it to us instead.', 'hauntedreal' );
	}

	if ( '' === $input['city'] ) {
		$errors[] = __( 'Please tell us which city or town this happened in.', 'hauntedreal' );
	}

	if ( ! in_array( $input['state'], hauntedreal_us_states(), true ) ) {
		$errors[] = __( 'Please choose a US state.', 'hauntedreal' );
	}

	if ( ! is_email( $input['email'] ) ) {
		$errors[] = __( 'Please give us a valid email address so we can contact you about your story.', 'hauntedreal' );
	}

	if ( 'anonymous' !== $input['credit'] && '' === $input['name'] ) {
		$errors[] = __( 'Please tell us your name, or choose to stay anonymous.', 'hauntedreal' );
	}

	if ( ! $input['consent'] ) {
		$errors[] = __( 'Please confirm you are happy for HauntedReal to publish your account.', 'hauntedreal' );
	}

	return $errors;
}

/**
 * "Dana Robinson" → "Dana R." unless the witness asked for a full credit.
 *
 * @param string $name Full name.
 * @param bool   $full Use the whole name.
 * @return string
 */
function hauntedreal_initialize_name( $name, $full = false ) {
	if ( $full || false === strpos( $name, ' ' ) ) {
		return $name;
	}

	$parts = explode( ' ', trim( $name ) );
	$last  = array_pop( $parts );

	return implode( ' ', $parts ) . ' ' . mb_substr( $last, 0, 1 ) . '.';
}

/**
 * Per-IP throttle: three submissions an hour.
 *
 * The address is hashed, never stored in the clear.
 *
 * @return bool
 */
function hauntedreal_submission_rate_limited() {
	$key = hauntedreal_submission_rate_key();

	if ( ! $key ) {
		return false;
	}

	return (int) get_transient( $key ) >= 3;
}

/**
 * Increment the throttle counter.
 */
function hauntedreal_record_submission() {
	$key = hauntedreal_submission_rate_key();

	if ( ! $key ) {
		return;
	}

	set_transient( $key, (int) get_transient( $key ) + 1, HOUR_IN_SECONDS );
}

/**
 * Transient key for the current visitor.
 *
 * @return string
 */
function hauntedreal_submission_rate_key() {
	if ( empty( $_SERVER['REMOTE_ADDR'] ) ) {
		return '';
	}

	$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );

	return 'hauntedreal_sub_' . md5( $ip . wp_salt() );
}

/**
 * Email the moderation address.
 *
 * @param int   $post_id New post ID.
 * @param array $input   Submitted values.
 */
function hauntedreal_notify_editors( $post_id, $input ) {
	$to = get_theme_mod( 'hauntedreal_moderation_email', get_option( 'admin_email' ) );

	if ( ! is_email( $to ) ) {
		return;
	}

	$subject = sprintf(
		/* translators: %s: story title. */
		__( '[%1$s] New ghost story: %2$s', 'hauntedreal' ),
		get_bloginfo( 'name' ),
		$input['title']
	);

	$body = implode( "\n", array(
		__( 'A new community experience is waiting for review.', 'hauntedreal' ),
		'',
		sprintf( __( 'Title: %s', 'hauntedreal' ), $input['title'] ),
		sprintf( __( 'Location: %s', 'hauntedreal' ), trim( $input['city'] . ', ' . $input['state'], ', ' ) ),
		sprintf( __( 'When: %s', 'hauntedreal' ), $input['when'] ? $input['when'] : __( 'Not given', 'hauntedreal' ) ),
		sprintf( __( 'Contact: %s', 'hauntedreal' ), $input['email'] ),
		'',
		__( 'Review it here:', 'hauntedreal' ),
		admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
	) );

	wp_mail( $to, $subject, $body );
}

/**
 * Redirect back to the form with a stored notice (post/redirect/get).
 *
 * Errors and the visitor's typed answers ride in a short-lived transient
 * rather than the URL, so nothing personal ends up in a browser history or
 * a server log.
 *
 * @param string[] $errors  Error messages.
 * @param array    $input   Submitted values to repopulate.
 * @param bool     $success Whether the submission succeeded.
 */
function hauntedreal_submission_redirect( $errors = array(), $input = array(), $success = false ) {
	$token = wp_generate_password( 16, false );

	set_transient(
		'hauntedreal_notice_' . $token,
		array(
			'success' => $success,
			'errors'  => $errors,
			'input'   => $input,
		),
		10 * MINUTE_IN_SECONDS
	);

	$url = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$url = add_query_arg( 'hr_notice', $token, remove_query_arg( 'hr_notice', $url ) );

	if ( $success ) {
		$url .= '#hr-submission-notice';
	}

	wp_safe_redirect( $url );
	exit;
}

/**
 * Read (and clear) the stored notice for this request.
 *
 * @return array|null
 */
function hauntedreal_get_submission_notice() {
	static $notice = null;
	static $loaded = false;

	if ( $loaded ) {
		return $notice;
	}

	$loaded = true;

	if ( empty( $_GET['hr_notice'] ) ) {
		return null;
	}

	$token = sanitize_key( wp_unslash( $_GET['hr_notice'] ) );
	$data  = get_transient( 'hauntedreal_notice_' . $token );

	if ( ! $data ) {
		return null;
	}

	delete_transient( 'hauntedreal_notice_' . $token );
	$notice = $data;

	return $notice;
}

/**
 * Previously submitted value for a field, so a failed submission never
 * costs the visitor their typing.
 *
 * @param string $field Field key.
 * @return string
 */
function hauntedreal_submission_value( $field ) {
	$notice = hauntedreal_get_submission_notice();

	if ( ! $notice || empty( $notice['input'][ $field ] ) ) {
		return '';
	}

	return (string) $notice['input'][ $field ];
}
