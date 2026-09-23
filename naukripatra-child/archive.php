<?php
/**
 * NaukriPatra — Archive (category / state / tag / date)
 * Full width, no sidebar (generate_sidebar_layout filter unchanged).
 * np_render_job_table() is still the data layer — only the chrome
 * around it (breadcrumb, count badge, filter bar) is new in v3.0.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$np_term  = get_queried_object();
$np_title = single_term_title( '', false );
if ( ! $np_title ) $np_title = get_the_archive_title();
global $wp_query;
?>
<div class="np-archive">

	<?php np_ad_slot( 'list_top', 'np-ad-leaderboard' ); ?>

	<header class="np-page-head">
		<h1 class="np-page-title"><?php echo esc_html( $np_title ); ?></h1>
		<span class="np-count-badge">
			<span class="np-dot"></span>
			<?php echo esc_html( number_format_i18n( (int) $wp_query->found_posts ) ); ?> live listings
		</span>
		<?php
		$np_desc = term_description();
		if ( $np_desc ) echo '<div class="np-page-desc">' . wp_kses_post( $np_desc ) . '</div>';
		?>
	</header>

	<?php np_render_filter_bar(); ?>

	<?php np_ad_slot( 'arch_filter', 'np-ad-leaderboard' ); ?>

	<?php np_render_job_table(); ?>

	<?php np_ad_slot( 'arch_bottom', 'np-ad-rect' ); ?>
</div>
<?php
get_footer();
