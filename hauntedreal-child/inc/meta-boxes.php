<?php
/**
 * Editorial meta boxes.
 *
 * Classic meta boxes rather than a block-editor sidebar plugin: no build
 * step, no JavaScript bundle, and they work identically in the classic
 * editor and in Gutenberg.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the boxes.
 */
function hauntedreal_add_meta_boxes() {
	add_meta_box(
		'hauntedreal_location',
		__( 'Location & Case Details', 'hauntedreal' ),
		'hauntedreal_render_location_box',
		array( 'post', 'ghost_story' ),
		'side',
		'default'
	);

	add_meta_box(
		'hauntedreal_options',
		__( 'HauntedReal Display Options', 'hauntedreal' ),
		'hauntedreal_render_options_box',
		array( 'post', 'ghost_story' ),
		'side',
		'low'
	);
}
add_action( 'add_meta_boxes', 'hauntedreal_add_meta_boxes' );

/**
 * Location and case detail fields.
 *
 * @param WP_Post $post Current post.
 */
function hauntedreal_render_location_box( $post ) {
	wp_nonce_field( 'hauntedreal_save_meta', 'hauntedreal_meta_nonce' );

	$city    = get_post_meta( $post->ID, '_hauntedreal_city', true );
	$state   = get_post_meta( $post->ID, '_hauntedreal_state', true );
	$place   = get_post_meta( $post->ID, '_hauntedreal_place', true );
	$date    = get_post_meta( $post->ID, '_hauntedreal_experience_date', true );
	$witness = get_post_meta( $post->ID, '_hauntedreal_witness', true );
	$status  = get_post_meta( $post->ID, '_hauntedreal_status', true );
	?>
	<p>
		<label for="hauntedreal_place"><strong><?php esc_html_e( 'Location name', 'hauntedreal' ); ?></strong></label>
		<input type="text" class="widefat" id="hauntedreal_place" name="hauntedreal_place"
			value="<?php echo esc_attr( $place ); ?>" placeholder="<?php esc_attr_e( 'The Stanley Hotel', 'hauntedreal' ); ?>">
	</p>
	<p>
		<label for="hauntedreal_city"><strong><?php esc_html_e( 'City', 'hauntedreal' ); ?></strong></label>
		<input type="text" class="widefat" id="hauntedreal_city" name="hauntedreal_city"
			value="<?php echo esc_attr( $city ); ?>" placeholder="<?php esc_attr_e( 'Gettysburg', 'hauntedreal' ); ?>">
	</p>
	<p>
		<label for="hauntedreal_state"><strong><?php esc_html_e( 'State', 'hauntedreal' ); ?></strong></label>
		<select class="widefat" id="hauntedreal_state" name="hauntedreal_state">
			<option value=""><?php esc_html_e( '— Select a state —', 'hauntedreal' ); ?></option>
			<?php foreach ( hauntedreal_us_states() as $name ) : ?>
				<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $state, $name ); ?>>
					<?php echo esc_html( $name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<span class="description"><?php esc_html_e( 'Also files the story under Haunted America.', 'hauntedreal' ); ?></span>
	</p>
	<p>
		<label for="hauntedreal_experience_date"><strong><?php esc_html_e( 'Date of experience', 'hauntedreal' ); ?></strong></label>
		<input type="text" class="widefat" id="hauntedreal_experience_date" name="hauntedreal_experience_date"
			value="<?php echo esc_attr( $date ); ?>" placeholder="<?php esc_attr_e( 'October 2023', 'hauntedreal' ); ?>">
	</p>
	<p>
		<label for="hauntedreal_witness"><strong><?php esc_html_e( 'Witness', 'hauntedreal' ); ?></strong></label>
		<input type="text" class="widefat" id="hauntedreal_witness" name="hauntedreal_witness"
			value="<?php echo esc_attr( $witness ); ?>" placeholder="<?php esc_attr_e( 'Dana R.', 'hauntedreal' ); ?>">
	</p>
	<p>
		<label for="hauntedreal_status"><strong><?php esc_html_e( 'Verification', 'hauntedreal' ); ?></strong></label>
		<select class="widefat" id="hauntedreal_status" name="hauntedreal_status">
			<option value=""><?php esc_html_e( '— None —', 'hauntedreal' ); ?></option>
			<option value="verified" <?php selected( $status, 'verified' ); ?>><?php esc_html_e( 'Details verified', 'hauntedreal' ); ?></option>
			<option value="investigating" <?php selected( $status, 'investigating' ); ?>><?php esc_html_e( 'Under review', 'hauntedreal' ); ?></option>
			<option value="unverified" <?php selected( $status, 'unverified' ); ?>><?php esc_html_e( 'Unverified account', 'hauntedreal' ); ?></option>
		</select>
	</p>
	<?php
	if ( 'ghost_story' === $post->post_type ) {
		$email = get_post_meta( $post->ID, '_hauntedreal_submitter_email', true );

		if ( $email && current_user_can( 'edit_others_posts' ) ) {
			printf(
				'<p><strong>%s</strong><br><a href="mailto:%s">%s</a><br><span class="description">%s</span></p>',
				esc_html__( 'Submitter contact', 'hauntedreal' ),
				esc_attr( $email ),
				esc_html( $email ),
				esc_html__( 'Never displayed on the site.', 'hauntedreal' )
			);
		}
	}
}

/**
 * Per-post display options.
 *
 * @param WP_Post $post Current post.
 */
function hauntedreal_render_options_box( $post ) {
	$disable_ads = get_post_meta( $post->ID, '_hauntedreal_disable_ads', true );
	?>
	<p>
		<label>
			<input type="checkbox" name="hauntedreal_disable_ads" value="1" <?php checked( $disable_ads, '1' ); ?>>
			<?php esc_html_e( 'Hide in-article advertising', 'hauntedreal' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Use for sensitive investigations and sponsored features. The header and sidebar slots are unaffected.', 'hauntedreal' ); ?>
	</p>
	<?php
}

/**
 * Persist the fields.
 *
 * @param int $post_id Post ID.
 */
function hauntedreal_save_meta( $post_id ) {
	if ( ! isset( $_POST['hauntedreal_meta_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hauntedreal_meta_nonce'] ) ), 'hauntedreal_save_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'hauntedreal_city'            => '_hauntedreal_city',
		'hauntedreal_state'           => '_hauntedreal_state',
		'hauntedreal_place'           => '_hauntedreal_place',
		'hauntedreal_experience_date' => '_hauntedreal_experience_date',
		'hauntedreal_witness'         => '_hauntedreal_witness',
		'hauntedreal_status'          => '_hauntedreal_status',
	);

	foreach ( $fields as $input => $meta_key ) {
		if ( isset( $_POST[ $input ] ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST[ $input ] ) );

			if ( '' === $value ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}

	if ( isset( $_POST['hauntedreal_disable_ads'] ) ) {
		update_post_meta( $post_id, '_hauntedreal_disable_ads', '1' );
	} else {
		delete_post_meta( $post_id, '_hauntedreal_disable_ads' );
	}

	// Keep the state taxonomy in step with the state field so Haunted America
	// stays accurate without editors having to remember two places.
	if ( isset( $_POST['hauntedreal_state'] ) ) {
		$state = sanitize_text_field( wp_unslash( $_POST['hauntedreal_state'] ) );

		if ( $state && in_array( $state, hauntedreal_us_states(), true ) ) {
			wp_set_object_terms( $post_id, $state, 'us_state', false );
		}
	}
}
add_action( 'save_post', 'hauntedreal_save_meta' );

/**
 * Extra author profile fields used by author.php.
 *
 * @param WP_User $user User being edited.
 */
function hauntedreal_user_fields( $user ) {
	?>
	<h2><?php esc_html_e( 'HauntedReal Masthead', 'hauntedreal' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="hauntedreal_role"><?php esc_html_e( 'Masthead title', 'hauntedreal' ); ?></label></th>
			<td>
				<input type="text" class="regular-text" id="hauntedreal_role" name="hauntedreal_role"
					value="<?php echo esc_attr( get_the_author_meta( 'hauntedreal_role', $user->ID ) ); ?>"
					placeholder="<?php esc_attr_e( 'Senior Investigations Editor', 'hauntedreal' ); ?>">
				<p class="description"><?php esc_html_e( 'Shown above the name on the author profile.', 'hauntedreal' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="hauntedreal_beat"><?php esc_html_e( 'Beat', 'hauntedreal' ); ?></label></th>
			<td>
				<input type="text" class="regular-text" id="hauntedreal_beat" name="hauntedreal_beat"
					value="<?php echo esc_attr( get_the_author_meta( 'hauntedreal_beat', $user->ID ) ); ?>"
					placeholder="<?php esc_attr_e( 'The American South', 'hauntedreal' ); ?>">
			</td>
		</tr>
		<tr>
			<th><label for="hauntedreal_public_email"><?php esc_html_e( 'Public contact email', 'hauntedreal' ); ?></label></th>
			<td>
				<input type="email" class="regular-text" id="hauntedreal_public_email" name="hauntedreal_public_email"
					value="<?php echo esc_attr( get_the_author_meta( 'hauntedreal_public_email', $user->ID ) ); ?>">
				<p class="description"><?php esc_html_e( 'Optional. Displayed on the profile — leave blank to hide.', 'hauntedreal' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'hauntedreal_user_fields' );
add_action( 'edit_user_profile', 'hauntedreal_user_fields' );

/**
 * Save the profile fields.
 *
 * @param int $user_id User ID.
 */
function hauntedreal_save_user_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	if ( isset( $_POST['hauntedreal_role'] ) ) {
		update_user_meta( $user_id, 'hauntedreal_role', sanitize_text_field( wp_unslash( $_POST['hauntedreal_role'] ) ) );
	}

	if ( isset( $_POST['hauntedreal_beat'] ) ) {
		update_user_meta( $user_id, 'hauntedreal_beat', sanitize_text_field( wp_unslash( $_POST['hauntedreal_beat'] ) ) );
	}

	if ( isset( $_POST['hauntedreal_public_email'] ) ) {
		update_user_meta( $user_id, 'hauntedreal_public_email', sanitize_email( wp_unslash( $_POST['hauntedreal_public_email'] ) ) );
	}
}
add_action( 'personal_options_update', 'hauntedreal_save_user_fields' );
add_action( 'edit_user_profile_update', 'hauntedreal_save_user_fields' );

/**
 * Surface pending community submissions where editors will see them.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function hauntedreal_ghost_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			$new['hauntedreal_location'] = __( 'Location', 'hauntedreal' );
			$new['hauntedreal_status']   = __( 'Verification', 'hauntedreal' );
		}
	}

	return $new;
}
add_filter( 'manage_ghost_story_posts_columns', 'hauntedreal_ghost_columns' );

/**
 * Render the custom columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function hauntedreal_ghost_column_content( $column, $post_id ) {
	if ( 'hauntedreal_location' === $column ) {
		echo esc_html( hauntedreal_get_location( $post_id ) ?: '—' );
	}

	if ( 'hauntedreal_status' === $column ) {
		$status = get_post_meta( $post_id, '_hauntedreal_status', true );
		echo esc_html( $status ? ucfirst( $status ) : __( 'Not reviewed', 'hauntedreal' ) );
	}
}
add_action( 'manage_ghost_story_posts_custom_column', 'hauntedreal_ghost_column_content', 10, 2 );
