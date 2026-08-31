<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
kc_banner( 'Page Not Found', '404' );
?>
<section class="section">
	<div class="container" style="text-align:center;">
		<h2 style="font-size:3rem;color:var(--primary);">404</h2>
		<p>Sorry, the page you are looking for could not be found.</p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">Back to Home</a>
	</div>
</section>
<?php get_footer(); ?>
