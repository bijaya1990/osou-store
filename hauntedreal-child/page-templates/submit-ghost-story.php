<?php
/**
 * Template Name: Submit Ghost Story
 * Template Post Type: page
 *
 * The community submission form.
 *
 * Posts back to itself and is processed on template_redirect in
 * inc/submissions.php, which redirects afterwards — so a refresh can never
 * send the same story twice. Nothing here needs JavaScript: validation is
 * native HTML plus a server-side pass, and spam defence is a nonce, a
 * honeypot, a time floor and a per-IP throttle.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hauntedreal_notice = hauntedreal_get_submission_notice();
$hauntedreal_open   = hauntedreal_submissions_open();
$hauntedreal_types  = get_terms( array(
	'taxonomy'   => 'ghost_theme',
	'hide_empty' => false,
) );
?>

<div class="hr-container hr-container--narrow">

	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<header class="hr-page__header">
			<?php hauntedreal_breadcrumbs(); ?>

			<span class="hr-kicker hr-kicker--spectral"><?php esc_html_e( 'Community Experience', 'hauntedreal' ); ?></span>

			<h1 class="hr-page__title"><?php the_title(); ?></h1>

			<?php if ( has_excerpt() ) : ?>
				<p class="hr-article__standfirst" style="margin:var(--hr-s-4) 0 0">
					<?php echo esc_html( get_the_excerpt() ); ?>
				</p>
			<?php endif; ?>
		</header>

		<?php if ( get_the_content() ) : ?>
			<div class="hr-entry" style="margin-bottom:var(--hr-s-6)">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

	<?php endwhile; ?>

	<div id="hr-submission-notice">
		<?php if ( $hauntedreal_notice && ! empty( $hauntedreal_notice['success'] ) ) : ?>
			<div class="hr-notice hr-notice--success" role="status">
				<p><strong><?php esc_html_e( 'Your story is with our editors.', 'hauntedreal' ); ?></strong></p>
				<p><?php esc_html_e( 'Thank you for trusting us with it. Every account is read by a person before it is published, which usually takes a few days. We will email you if we need to ask anything.', 'hauntedreal' ); ?></p>
			</div>
		<?php elseif ( $hauntedreal_notice && ! empty( $hauntedreal_notice['errors'] ) ) : ?>
			<div class="hr-notice hr-notice--error" role="alert">
				<p><strong><?php esc_html_e( 'Your story was not sent:', 'hauntedreal' ); ?></strong></p>
				<ul>
					<?php foreach ( $hauntedreal_notice['errors'] as $hauntedreal_error ) : ?>
						<li><?php echo esc_html( $hauntedreal_error ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( ! $hauntedreal_open ) : ?>

		<div class="hr-panel">
			<h2><?php esc_html_e( 'Submissions are paused', 'hauntedreal' ); ?></h2>
			<p><?php esc_html_e( 'We have more accounts than we can currently read. The form will reopen shortly — thank you for your patience.', 'hauntedreal' ); ?></p>
		</div>

	<?php else : ?>

		<form class="hr-form" method="post" action="<?php echo esc_url( get_permalink() ); ?>#hr-submission-notice">

			<?php wp_nonce_field( 'hauntedreal_submit_ghost_story', 'hauntedreal_submit_nonce' ); ?>
			<input type="hidden" name="hauntedreal_action" value="submit_ghost_story">
			<input type="hidden" name="hauntedreal_started" value="<?php echo esc_attr( time() ); ?>">

			<?php // Honeypot. Positioned off-screen rather than display:none so bots fill it. ?>
			<div class="hr-hp" aria-hidden="true">
				<label for="hauntedreal_website"><?php esc_html_e( 'Leave this field empty', 'hauntedreal' ); ?></label>
				<input type="text" id="hauntedreal_website" name="hauntedreal_website" tabindex="-1" autocomplete="off">
			</div>

			<fieldset style="border:0;padding:0;margin:0 0 var(--hr-s-6)">
				<legend style="font-size:var(--hr-t-h4);font-family:var(--hr-font-display);margin-bottom:var(--hr-s-4)">
					<?php esc_html_e( 'What happened', 'hauntedreal' ); ?>
				</legend>

				<div class="hr-field">
					<label for="hauntedreal_title">
						<?php esc_html_e( 'Give your experience a title', 'hauntedreal' ); ?>
						<span class="hr-req" aria-hidden="true">*</span>
					</label>
					<input class="hr-input" type="text" id="hauntedreal_title" name="hauntedreal_title"
						required maxlength="120"
						value="<?php echo esc_attr( hauntedreal_submission_value( 'title' ) ); ?>"
						placeholder="<?php esc_attr_e( 'The woman on the third-floor landing', 'hauntedreal' ); ?>">
					<span class="hr-field__hint"><?php esc_html_e( 'Short and specific reads better than dramatic.', 'hauntedreal' ); ?></span>
				</div>

				<div class="hr-field">
					<label for="hauntedreal_story">
						<?php esc_html_e( 'Tell us what happened', 'hauntedreal' ); ?>
						<span class="hr-req" aria-hidden="true">*</span>
					</label>
					<textarea class="hr-textarea" id="hauntedreal_story" name="hauntedreal_story"
						required minlength="200" maxlength="20000"
						placeholder="<?php esc_attr_e( 'Start with where you were and what you were doing. Include the small details — times, sounds, temperatures, who else was there.', 'hauntedreal' ); ?>"><?php echo esc_textarea( hauntedreal_submission_value( 'story' ) ); ?></textarea>
					<span class="hr-field__hint"><?php esc_html_e( 'At least 200 characters. Write it as you would tell it.', 'hauntedreal' ); ?></span>
				</div>

				<div class="hr-field">
					<label for="hauntedreal_type"><?php esc_html_e( 'What kind of experience was it?', 'hauntedreal' ); ?></label>
					<select class="hr-select" id="hauntedreal_type" name="hauntedreal_type">
						<option value=""><?php esc_html_e( 'I am not sure', 'hauntedreal' ); ?></option>
						<?php
						if ( $hauntedreal_types && ! is_wp_error( $hauntedreal_types ) ) :
							foreach ( $hauntedreal_types as $hauntedreal_type ) :
								?>
								<option value="<?php echo esc_attr( $hauntedreal_type->term_id ); ?>"
									<?php selected( hauntedreal_submission_value( 'type' ), (string) $hauntedreal_type->term_id ); ?>>
									<?php echo esc_html( $hauntedreal_type->name ); ?>
								</option>
								<?php
							endforeach;
						endif;
						?>
					</select>
				</div>
			</fieldset>

			<fieldset style="border:0;padding:0;margin:0 0 var(--hr-s-6)">
				<legend style="font-size:var(--hr-t-h4);font-family:var(--hr-font-display);margin-bottom:var(--hr-s-4)">
					<?php esc_html_e( 'Where and when', 'hauntedreal' ); ?>
				</legend>

				<div class="hr-field">
					<label for="hauntedreal_place"><?php esc_html_e( 'Place name', 'hauntedreal' ); ?></label>
					<input class="hr-input" type="text" id="hauntedreal_place" name="hauntedreal_place"
						maxlength="140"
						value="<?php echo esc_attr( hauntedreal_submission_value( 'place' ) ); ?>"
						placeholder="<?php esc_attr_e( 'A house, a hotel, a stretch of road', 'hauntedreal' ); ?>">
					<span class="hr-field__hint"><?php esc_html_e( 'Leave blank if it was a private home you would rather not name.', 'hauntedreal' ); ?></span>
				</div>

				<div class="hr-fieldrow">
					<div class="hr-field">
						<label for="hauntedreal_city">
							<?php esc_html_e( 'City or town', 'hauntedreal' ); ?>
							<span class="hr-req" aria-hidden="true">*</span>
						</label>
						<input class="hr-input" type="text" id="hauntedreal_city" name="hauntedreal_city"
							required maxlength="80"
							value="<?php echo esc_attr( hauntedreal_submission_value( 'city' ) ); ?>"
							placeholder="<?php esc_attr_e( 'Savannah', 'hauntedreal' ); ?>">
					</div>

					<div class="hr-field">
						<label for="hauntedreal_state">
							<?php esc_html_e( 'State', 'hauntedreal' ); ?>
							<span class="hr-req" aria-hidden="true">*</span>
						</label>
						<select class="hr-select" id="hauntedreal_state" name="hauntedreal_state" required>
							<option value=""><?php esc_html_e( 'Select a state', 'hauntedreal' ); ?></option>
							<?php foreach ( hauntedreal_us_states() as $hauntedreal_state_name ) : ?>
								<option value="<?php echo esc_attr( $hauntedreal_state_name ); ?>"
									<?php selected( hauntedreal_submission_value( 'state' ), $hauntedreal_state_name ); ?>>
									<?php echo esc_html( $hauntedreal_state_name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="hr-field">
					<label for="hauntedreal_when"><?php esc_html_e( 'When did it happen?', 'hauntedreal' ); ?></label>
					<input class="hr-input" type="text" id="hauntedreal_when" name="hauntedreal_when"
						maxlength="60"
						value="<?php echo esc_attr( hauntedreal_submission_value( 'when' ) ); ?>"
						placeholder="<?php esc_attr_e( 'October 2023, or "the summer I was fourteen"', 'hauntedreal' ); ?>">
				</div>
			</fieldset>

			<fieldset style="border:0;padding:0;margin:0 0 var(--hr-s-6)">
				<legend style="font-size:var(--hr-t-h4);font-family:var(--hr-font-display);margin-bottom:var(--hr-s-4)">
					<?php esc_html_e( 'About you', 'hauntedreal' ); ?>
				</legend>

				<div class="hr-fieldrow">
					<div class="hr-field">
						<label for="hauntedreal_name"><?php esc_html_e( 'Your name', 'hauntedreal' ); ?></label>
						<input class="hr-input" type="text" id="hauntedreal_name" name="hauntedreal_name"
							maxlength="80" autocomplete="name"
							value="<?php echo esc_attr( hauntedreal_submission_value( 'name' ) ); ?>">
					</div>

					<div class="hr-field">
						<label for="hauntedreal_email">
							<?php esc_html_e( 'Your email', 'hauntedreal' ); ?>
							<span class="hr-req" aria-hidden="true">*</span>
						</label>
						<input class="hr-input" type="email" id="hauntedreal_email" name="hauntedreal_email"
							required autocomplete="email"
							value="<?php echo esc_attr( hauntedreal_submission_value( 'email' ) ); ?>">
						<span class="hr-field__hint"><?php esc_html_e( 'Never published. Used only if an editor needs to reach you.', 'hauntedreal' ); ?></span>
					</div>
				</div>

				<div class="hr-field">
					<label for="hauntedreal_credit"><?php esc_html_e( 'How should we credit you?', 'hauntedreal' ); ?></label>
					<select class="hr-select" id="hauntedreal_credit" name="hauntedreal_credit">
						<option value="initials" <?php selected( hauntedreal_submission_value( 'credit' ), 'initials' ); ?>>
							<?php esc_html_e( 'First name and last initial — Dana R.', 'hauntedreal' ); ?>
						</option>
						<option value="full" <?php selected( hauntedreal_submission_value( 'credit' ), 'full' ); ?>>
							<?php esc_html_e( 'My full name', 'hauntedreal' ); ?>
						</option>
						<option value="anonymous" <?php selected( hauntedreal_submission_value( 'credit' ), 'anonymous' ); ?>>
							<?php esc_html_e( 'Keep me anonymous', 'hauntedreal' ); ?>
						</option>
					</select>
				</div>

				<div class="hr-field hr-checkbox">
					<input type="checkbox" id="hauntedreal_consent" name="hauntedreal_consent" value="1" required>
					<label for="hauntedreal_consent">
						<?php esc_html_e( 'This happened to me or to someone who asked me to share it, and I am happy for HauntedReal to publish and edit it for length and clarity.', 'hauntedreal' ); ?>
						<span class="hr-req" aria-hidden="true">*</span>
					</label>
				</div>
			</fieldset>

			<div class="hr-form__actions">
				<button type="submit" class="hr-btn hr-btn--primary">
					<?php esc_html_e( 'Send my story', 'hauntedreal' ); ?>
				</button>
				<p class="hr-form__note">
					<?php esc_html_e( 'Nothing is published automatically. An editor reads every submission first.', 'hauntedreal' ); ?>
				</p>
			</div>

		</form>

	<?php endif; ?>

</div>

<?php
get_footer();
