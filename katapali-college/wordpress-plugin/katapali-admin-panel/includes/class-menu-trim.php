<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Trims wp-admin down to exactly the sections a College Staff Admin was
   given: Katapali College (Hero Slides, Notices, Recruitment, Tenders,
   Faculty, Gallery, Downloads, Links, Organisation Logos, Applications -
   all already grouped under that one top-level menu), Posts, Student
   Records, and Media. Everything structural (Pages, Comments, Appearance,
   Plugins, Users, Tools, Settings, Dashboard) is hidden. Full
   administrators are never touched - this only runs for the limited
   role. */
class KAP_Menu_Trim {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'trim_menu' ), 999 );
		add_action( 'admin_bar_menu', array( __CLASS__, 'trim_admin_bar' ), 999 );
		add_action( 'admin_head', array( __CLASS__, 'skin_css' ) );
		add_action( 'admin_footer', array( __CLASS__, 'brand_banner_script' ) );
		add_filter( 'admin_footer_text', array( __CLASS__, 'footer_text' ) );
		add_filter( 'update_footer', '__return_empty_string', 999 );
	}

	private static function applies() {
		return is_user_logged_in() && KAP_Role::is_staff_admin();
	}

	public static function trim_menu() {
		if ( ! self::applies() ) return;
		foreach ( array( 'index.php', 'edit.php?post_type=page', 'edit-comments.php', 'themes.php', 'plugins.php', 'users.php', 'tools.php', 'options-general.php' ) as $slug ) {
			remove_menu_page( $slug );
		}
	}

	public static function trim_admin_bar( $wp_admin_bar ) {
		if ( ! self::applies() ) return;
		foreach ( array( 'wp-logo', 'comments', 'new-content' ) as $id ) {
			$wp_admin_bar->remove_node( $id );
		}
	}

	public static function footer_text( $text ) {
		if ( ! self::applies() ) return $text;
		return get_theme_mod( 'kc_college_name', 'Katapali +3 College, Katapali' ) . ' &mdash; Admin Panel';
	}

	/* Recolours the wp-admin menu/toolbar to the college's own brand
	   colours instead of the default WordPress blue-grey, and hides a
	   few remaining WordPress-branded bits (About/WP logo link, version
	   nag) CSS can't reach any other way. */
	public static function skin_css() {
		if ( ! self::applies() ) return;
		$primary   = get_theme_mod( 'kc_color_primary', '#012D58' );
		$primary_d = get_theme_mod( 'kc_color_dark', '#001A33' );
		$gold      = get_theme_mod( 'kc_color_gold', '#EBC30F' );
		?>
		<style>
			#adminmenuback, #adminmenuwrap, #adminmenu, #adminmenu .wp-submenu { background: <?php echo esc_attr( $primary ); ?>; }
			#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, #adminmenu li.current a.menu-top, #adminmenu .wp-has-current-submenu .wp-submenu, #adminmenu a.wp-has-current-submenu:focus { background: <?php echo esc_attr( $primary_d ); ?>; }
			#adminmenu li:hover, #adminmenu li a:focus { background: <?php echo esc_attr( $primary_d ); ?>; }
			#adminmenu .wp-submenu a:hover, #adminmenu a:hover { color: <?php echo esc_attr( $gold ); ?> !important; }
			#adminmenu div.wp-menu-image:before { color: rgba(255,255,255,.75); }
			#wpadminbar { background: <?php echo esc_attr( $primary_d ); ?>; }
			#wpadminbar #wp-admin-bar-site-name > .ab-item { font-weight: 700; }
			#kap-admin-banner { background: <?php echo esc_attr( $primary ); ?>; padding: 14px 12px; text-align: center; border-bottom: 3px solid <?php echo esc_attr( $gold ); ?>; }
			#kap-admin-banner img { width: 40px; height: 40px; border-radius: 50%; background: #fff; padding: 3px; display: block; margin: 0 auto 6px; }
			#kap-admin-banner .kap-name { color: #fff; font-weight: 800; font-size: .8rem; line-height: 1.25; }
			#kap-admin-banner .kap-label { color: <?php echo esc_attr( $gold ); ?>; font-size: .62rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; margin-top: 2px; }
		</style>
		<?php
	}

	/* Prepends the college logo/name/"Admin Panel" banner above the
	   sidebar menu. Done in JS (rather than a PHP admin_menu hook) since
	   wp-admin doesn't offer a clean action right at the top of
	   #adminmenuwrap - this runs once, on every admin page load. */
	public static function brand_banner_script() {
		if ( ! self::applies() ) return;
		$logo_id  = get_theme_mod( 'custom_logo' );
		$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : KAP_URI . '/assets/logo-placeholder.svg';
		$name     = get_theme_mod( 'kc_college_name', 'Katapali +3 College, Katapali' );
		?>
		<script>
		(function(){
			var wrap = document.getElementById('adminmenuwrap');
			if (!wrap) return;
			var banner = document.createElement('div');
			banner.id = 'kap-admin-banner';
			banner.innerHTML = '<img src="<?php echo esc_js( $logo_url ); ?>" alt="">' +
				'<div class="kap-name"><?php echo esc_js( $name ); ?></div>' +
				'<div class="kap-label">Admin Panel</div>';
			wrap.parentNode.insertBefore(banner, wrap);
		})();
		</script>
		<?php
	}
}
