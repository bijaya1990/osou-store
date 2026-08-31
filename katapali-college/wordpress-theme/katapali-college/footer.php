<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<footer class="site-footer">
	<div class="container">
		<div class="footer-grid">
			<div>
				<div class="footer-brand">
					<img src="<?php echo esc_url( KC_URI . '/assets/demo-images/logo.svg' ); ?>" alt="logo">
					<span><?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?></span>
				</div>
				<p><?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?> is a premier rural degree college offering +3 Arts, Science and Commerce streams with a commitment to accessible, affordable and quality higher education.</p>
				<div class="footer-social">
					<a href="<?php echo esc_url( get_theme_mod( 'kc_facebook', kc_default( 'kc_facebook' ) ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a>
					<a href="<?php echo esc_url( get_theme_mod( 'kc_twitter', kc_default( 'kc_twitter' ) ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-twitter"></i></a>
					<a href="<?php echo esc_url( get_theme_mod( 'kc_youtube', kc_default( 'kc_youtube' ) ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i></a>
					<a href="<?php echo esc_url( get_theme_mod( 'kc_instagram', kc_default( 'kc_instagram' ) ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a>
					<a href="<?php echo esc_url( get_theme_mod( 'kc_whatsapp', kc_default( 'kc_whatsapp' ) ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i></a>
				</div>
			</div>
			<div>
				<h4>Resources</h4>
				<ul class="footer-links">
					<?php kc_link_list( 'Resources' ); ?>
				</ul>
			</div>
			<div>
				<h4>Useful Links</h4>
				<ul class="footer-links">
					<?php kc_link_list( 'Useful Links' ); ?>
				</ul>
			</div>
			<div>
				<h4>Find Us on Map</h4>
				<div class="footer-map"><?php echo get_theme_mod( 'kc_map_embed', kc_default( 'kc_map_embed' ) ); ?></div>
			</div>
		</div>
		<div class="footer-grid footer-grid-single">
			<div>
				<h4>Contact Info</h4>
				<ul class="footer-contact footer-contact-row">
					<li><i class="fa-solid fa-location-dot"></i><span><?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?>, <?php kc_footer_address(); ?></span></li>
					<li><i class="fa-solid fa-phone"></i><span><?php echo esc_html( get_theme_mod( 'kc_phone', kc_default( 'kc_phone' ) ) ); ?></span></li>
					<li><i class="fa-solid fa-envelope"></i><span><?php echo esc_html( get_theme_mod( 'kc_email', kc_default( 'kc_email' ) ) ); ?></span></li>
					<li><i class="fa-solid fa-clock"></i><span>Monday to Saturday, 10:00 AM - 5:00 PM</span></li>
				</ul>
				<?php if ( is_active_sidebar( 'footer-1' ) ) dynamic_sidebar( 'footer-1' ); ?>
			</div>
		</div>
	</div>
	<div class="footer-bottom">&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( get_theme_mod( 'kc_college_name', kc_default( 'kc_college_name' ) ) ); ?>. All Rights Reserved. | Built on WordPress -- every section above is editable from wp-admin.</div>
</footer>

<div class="back-top" id="kc-back-top"><i class="fa-solid fa-arrow-up"></i></div>

<?php wp_footer(); ?>
</body>
</html>
