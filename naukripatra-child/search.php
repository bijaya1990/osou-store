<?php
/**
 * NaukriPatra — Search results (same listing layout as the archive).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
global $wp_query;
?>
<div class="np-archive">

	<?php np_ad_slot( 'list_top', 'np-ad-leaderboard' ); ?>

	<header class="np-page-head">
		<h1 class="np-page-title">Search: &ldquo;<?php echo esc_html( get_search_query() ); ?>&rdquo;</h1>
		<span class="np-count-badge">
			<span class="np-dot"></span>
			<?php echo esc_html( number_format_i18n( (int) $wp_query->found_posts ) ); ?> results
		</span>
	</header>

	<?php np_render_filter_bar(); ?>

	<?php np_ad_slot( 'arch_filter', 'np-ad-leaderboard' ); ?>

	<?php np_render_job_table(); ?>

	<?php np_ad_slot( 'arch_bottom', 'np-ad-rect' ); ?>
</div>
<?php
get_footer();
