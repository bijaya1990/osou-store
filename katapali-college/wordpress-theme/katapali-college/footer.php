<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<footer class="site-footer">
	<div class="container">
		<div class="footer-grid">
			<div>
				<div class="footer-brand">
					<img src="<?php echo esc_url( KC_URI . '/assets/demo-images/logo.svg' ); ?>" alt="logo">
					<span><?php echo esc_html( get_theme_mod( 'kc_college_name', 'KATAPALI +3 COLLEGE, KATAPALI' ) ); ?></span>
				</div>
				<p><?php bloginfo( 'description' ); ?> KATAPALI +3 COLLEGE, KATAPALI is a premier rural degree college of Bargarh district, offering +3 Arts, Science and Commerce streams with a commitment to accessible, affordable and quality higher education.</p>
				<div class="footer-social">
					<a href="<?php echo esc_url( get_theme_mod( 'kc_facebook', '#' ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a>
					<a href="<?php echo esc_url( get_theme_mod( 'kc_twitter', '#' ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-twitter"></i></a>
					<a href="<?php echo esc_url( get_theme_mod( 'kc_youtube', '#' ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i></a>
					<a href="<?php echo esc_url( get_theme_mod( 'kc_instagram', '#' ) ); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a>
				</div>
			</div>
			<div>
				<h4>Quick Links</h4>
				<ul class="footer-links">
					<?php
					if ( has_nav_menu( 'footer' ) ) {
						wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1 ) );
					} else {
						$pages = get_pages( array( 'number' => 8 ) );
						foreach ( $pages as $pg ) echo '<li><a href="' . esc_url( get_permalink( $pg ) ) . '">' . esc_html( $pg->post_title ) . '</a></li>';
					}
					?>
				</ul>
			</div>
			<div>
				<h4>Explore</h4>
				<ul class="footer-links">
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'kc_notice' ) ); ?>">Notices</a></li>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'kc_recruitment' ) ); ?>">Recruitment</a></li>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'kc_tender' ) ); ?>">Tenders</a></li>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'kc_faculty' ) ); ?>">Faculty</a></li>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'kc_gallery' ) ); ?>">Gallery</a></li>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'kc_download' ) ); ?>">Downloads</a></li>
				</ul>
			</div>
			<div>
				<h4>Contact Info</h4>
				<ul class="footer-contact">
					<li><i class="fa-solid fa-location-dot"></i><span><?php echo esc_html( get_theme_mod( 'kc_college_name', '' ) ); ?>, <?php kc_footer_address(); ?></span></li>
					<li><i class="fa-solid fa-phone"></i><span><?php echo esc_html( get_theme_mod( 'kc_phone', '' ) ); ?></span></li>
					<li><i class="fa-solid fa-envelope"></i><span><?php echo esc_html( get_theme_mod( 'kc_email', '' ) ); ?></span></li>
					<li><i class="fa-solid fa-clock"></i><span>Monday to Saturday, 10:00 AM – 5:00 PM</span></li>
				</ul>
				<?php if ( is_active_sidebar( 'footer-1' ) ) dynamic_sidebar( 'footer-1' ); ?>
			</div>
		</div>
	</div>
	<div class="footer-bottom">&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( get_theme_mod( 'kc_college_name', 'KATAPALI +3 COLLEGE, KATAPALI' ) ); ?>. All Rights Reserved. | Built on WordPress — every section above is editable from wp-admin.</div>
</footer>

<div class="back-top" id="kc-back-top"><i class="fa-solid fa-arrow-up"></i></div>

<?php wp_footer(); ?>
</body>
</html>
