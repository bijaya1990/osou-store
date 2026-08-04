<?php
/**
 * Comments.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Never expose a password-protected post's discussion.
if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="hr-comments">

	<?php if ( have_comments() ) : ?>

		<h2 class="hr-comments__title">
			<?php
			$hauntedreal_count = get_comments_number();

			printf(
				/* translators: %s: comment count. */
				esc_html( _n( '%s Response', '%s Responses', $hauntedreal_count, 'hauntedreal' ) ),
				esc_html( number_format_i18n( $hauntedreal_count ) )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 44,
			) );
			?>
		</ol>

		<?php
		the_comments_pagination( array(
			'prev_text' => __( '&larr; Older', 'hauntedreal' ),
			'next_text' => __( 'Newer &rarr;', 'hauntedreal' ),
		) );
		?>

	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="hr-form__note"><?php esc_html_e( 'Comments are closed on this story.', 'hauntedreal' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form( array(
		'title_reply'        => __( 'Add your account', 'hauntedreal' ),
		'title_reply_before' => '<h2 class="hr-comments__title">',
		'title_reply_after'  => '</h2>',
		'class_submit'       => 'submit hr-btn hr-btn--primary',
		'comment_notes_before' => '<p class="hr-form__note">' . esc_html__( 'Your email address is never published. Comments are moderated before they appear.', 'hauntedreal' ) . '</p>',
		'label_submit'       => __( 'Post response', 'hauntedreal' ),
	) );
	?>

</section>
