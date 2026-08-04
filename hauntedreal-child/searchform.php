<?php
/**
 * Search form.
 *
 * Used in the masthead panel, on the search results page and on the 404.
 * IDs are made unique because more than one can appear per document.
 *
 * @package HauntedReal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hauntedreal_field_id = wp_unique_id( 'hr-search-field-' );
?>
<form role="search" method="get" class="hr-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="hr-screen-reader-text" for="<?php echo esc_attr( $hauntedreal_field_id ); ?>">
		<?php esc_html_e( 'Search HauntedReal', 'hauntedreal' ); ?>
	</label>
	<input
		type="search"
		id="<?php echo esc_attr( $hauntedreal_field_id ); ?>"
		class="hr-searchform__field"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Search hauntings, places, states…', 'hauntedreal' ); ?>"
		autocomplete="off"
	>
	<button type="submit" class="hr-btn hr-btn--primary hr-searchform__submit">
		<?php esc_html_e( 'Search', 'hauntedreal' ); ?>
	</button>
</form>
