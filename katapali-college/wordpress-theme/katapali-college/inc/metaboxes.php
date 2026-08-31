<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Field map per CPT: meta_key => [Label, type(text|date|textarea|number|select|url), options] */
function kc_meta_fields( $post_type ) {
	switch ( $post_type ) {
		case 'kc_notice':
			return array(
				'kc_expiry'  => array( 'Expiry Date', 'date' ),
				'kc_file_url'=> array( 'Attachment File (PDF/Notice) URL', 'url' ),
				'kc_is_new'  => array( 'Mark as "New" badge', 'checkbox' ),
			);
		case 'kc_recruitment':
			return array(
				'kc_department'   => array( 'Department', 'text' ),
				'kc_job_type'     => array( 'Engagement Type', 'text' ),
				'kc_vacancies'    => array( 'Number of Vacancies', 'number' ),
				'kc_salary'       => array( 'Salary / Remuneration', 'text' ),
				'kc_qualification'=> array( 'Eligibility / Qualification', 'textarea' ),
				'kc_last_date'    => array( 'Last Date to Apply', 'date' ),
				'kc_status'       => array( 'Status', 'select', array( 'Open', 'Closed' ) ),
				'kc_file_url'     => array( 'Notification File URL', 'url' ),
			);
		case 'kc_tender':
			return array(
				'kc_tender_id' => array( 'Tender ID', 'text' ),
				'kc_last_date' => array( 'Submission Last Date', 'date' ),
				'kc_open_date' => array( 'Opening Date', 'date' ),
				'kc_emd'       => array( 'EMD Amount', 'text' ),
				'kc_value'     => array( 'Estimated Value', 'text' ),
				'kc_status'    => array( 'Status', 'select', array( 'Open', 'Closed' ) ),
				'kc_file_url'  => array( 'Tender Document URL', 'url' ),
			);
		case 'kc_faculty':
			return array(
				'kc_designation'   => array( 'Designation', 'text' ),
				'kc_qualification' => array( 'Qualification', 'text' ),
				'kc_experience'    => array( 'Experience', 'text' ),
				'kc_email'         => array( 'Email', 'text' ),
				'kc_phone'         => array( 'Phone', 'text' ),
				'kc_on_slider'     => array( 'Show on Homepage Slider (top 7 shown)', 'checkbox' ),
				'kc_order'         => array( 'Display Order', 'number' ),
			);
		case 'kc_gallery':
			return array(
				'kc_order' => array( 'Display Order', 'number' ),
				'kc_featured' => array( 'Show in Homepage Preview', 'checkbox' ),
			);
		case 'kc_download':
			return array(
				'kc_category'  => array( 'Category', 'select', array( 'Prospectus', 'Forms', 'Syllabus', 'Circulars' ) ),
				'kc_file_url'  => array( 'File URL', 'url' ),
				'kc_file_size' => array( 'File Size (e.g. 1.2 MB)', 'text' ),
			);
		case 'kc_link':
			return array(
				'kc_url'   => array( 'Link URL', 'url' ),
				'kc_order' => array( 'Display Order', 'number' ),
			);
		case 'kc_slide':
			return array(
				'kc_subtitle' => array( 'Subtitle', 'text' ),
				'kc_btn1_text'=> array( 'Button 1 Text', 'text' ),
				'kc_btn1_link'=> array( 'Button 1 Link', 'text' ),
				'kc_btn2_text'=> array( 'Button 2 Text', 'text' ),
				'kc_btn2_link'=> array( 'Button 2 Link', 'text' ),
				'kc_order'    => array( 'Display Order', 'number' ),
			);
	}
	return array();
}

function kc_add_meta_boxes() {
	$types = array( 'kc_notice', 'kc_recruitment', 'kc_tender', 'kc_faculty', 'kc_gallery', 'kc_download', 'kc_slide', 'kc_link' );
	foreach ( $types as $t ) {
		add_meta_box( 'kc_fields_' . $t, __( 'Details', 'katapali-college' ), 'kc_render_meta_box', $t, 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'kc_add_meta_boxes' );

function kc_render_meta_box( $post ) {
	wp_nonce_field( 'kc_save_meta', 'kc_meta_nonce' );
	$fields = kc_meta_fields( $post->post_type );
	echo '<table class="form-table">';
	foreach ( $fields as $key => $def ) {
		$label = $def[0]; $type = $def[1]; $opts = isset( $def[2] ) ? $def[2] : array();
		$val = get_post_meta( $post->ID, $key, true );
		echo '<tr><th style="width:220px;"><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
		if ( $type === 'textarea' ) {
			echo '<textarea style="width:100%;" rows="3" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '">' . esc_textarea( $val ) . '</textarea>';
		} elseif ( $type === 'select' ) {
			echo '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '">';
			foreach ( $opts as $o ) {
				echo '<option value="' . esc_attr( $o ) . '" ' . selected( $val, $o, false ) . '>' . esc_html( $o ) . '</option>';
			}
			echo '</select>';
		} elseif ( $type === 'checkbox' ) {
			echo '<label><input type="checkbox" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="1" ' . checked( $val, '1', false ) . '> Yes</label>';
		} elseif ( $type === 'date' ) {
			echo '<input type="date" style="width:220px;" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '">';
		} elseif ( $type === 'url' ) {
			echo '<input type="text" style="width:100%;" placeholder="https:// or leave blank" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '">';
		} elseif ( $type === 'number' ) {
			echo '<input type="number" style="width:120px;" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '">';
		} else {
			echo '<input type="text" style="width:100%;" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '">';
		}
		echo '</td></tr>';
	}
	echo '</table>';
	if ( $post->post_type === 'kc_faculty' ) {
		echo '<p style="margin-top:10px;">Set the faculty photo using the <strong>Featured Image</strong> box on the right. Assign a <strong>Department</strong> from the box on the right.</p>';
	}
	if ( $post->post_type === 'kc_gallery' || $post->post_type === 'kc_slide' ) {
		echo '<p style="margin-top:10px;">Set the photo using the <strong>Featured Image</strong> box on the right' . ( $post->post_type === 'kc_gallery' ? '. Assign a <strong>Gallery Category</strong> from the box on the right.' : '.' ) . '</p>';
	}
}

function kc_save_meta_boxes( $post_id, $post ) {
	if ( ! isset( $_POST['kc_meta_nonce'] ) || ! wp_verify_nonce( $_POST['kc_meta_nonce'], 'kc_save_meta' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$fields = kc_meta_fields( $post->post_type );
	foreach ( $fields as $key => $def ) {
		$type = $def[1];
		if ( $type === 'checkbox' ) {
			update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '0' );
			continue;
		}
		if ( isset( $_POST[ $key ] ) ) {
			$value = $type === 'textarea' ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post', 'kc_save_meta_boxes', 10, 2 );
