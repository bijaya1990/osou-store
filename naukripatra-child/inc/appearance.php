<?php
/**
 * NaukriPatra — Appearance Engine (v3.0)
 * =====================================================================
 * Every colour, font family, font weight, letter size, letter spacing,
 * radius and container width used by the theme is a DESIGN TOKEN.
 *
 * Tokens live in one option (`np_theme`) and are printed as CSS custom
 * properties on :root in wp_head. style.css only ever reads the custom
 * properties — it never hard-codes a colour or a size. That is what
 * makes the whole theme editable from the dashboard
 * (Appearance > NaukriPatra Design) without touching a single file.
 *
 * 100% additive: this module adds a new option and a new admin page.
 * It does not read, rename or remove any existing option, meta field,
 * REST field or SEO output.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================================================
 * 1. TOKEN REGISTRY
 * =======================================================
 * Each token: css var name => [label, type, default, group, extra]
 * type: color | font | number | select | text
 */
function np_token_registry() {
	return array(

		/* ---------- Brand colours ---------- */
		'primary'        => array( 'Primary navy',            'color',  '#0F1F3B', 'colors' ),
		'primary_dark'   => array( 'Deep navy (footer/dark)', 'color',  '#0A1628', 'colors' ),
		'primary_mid'    => array( 'Navy mid (gradient)',     'color',  '#16305C', 'colors' ),
		'primary_light'  => array( 'Navy light (links)',      'color',  '#1E3A6B', 'colors' ),
		'accent'         => array( 'Accent gold (CTA)',       'color',  '#C9A227', 'colors' ),
		'accent_dark'    => array( 'Accent gold hover',       'color',  '#AD8A1B', 'colors' ),

		/* ---------- Surfaces ---------- */
		'bg'             => array( 'Page background',         'color',  '#F5F7FA', 'colors' ),
		'surface'        => array( 'Card surface',            'color',  '#FFFFFF', 'colors' ),
		'border'         => array( 'Card border',             'color',  '#E2E7F0', 'colors' ),
		'text'           => array( 'Body text',               'color',  '#1A2233', 'colors' ),
		'muted'          => array( 'Muted text',              'color',  '#5B6472', 'colors' ),

		/* ---------- Status ---------- */
		'govt'           => array( 'Government badge green',  'color',  '#1E8E5A', 'colors' ),
		'private'        => array( 'Private badge teal',      'color',  '#0D9488', 'colors' ),
		'danger'         => array( 'Alert red (last date)',   'color',  '#B23B3B', 'colors' ),

		/* ---------- Dark mode ---------- */
		'dark_bg'        => array( 'Dark mode background',    'color',  '#0A1120', 'dark' ),
		'dark_surface'   => array( 'Dark mode card',          'color',  '#111C31', 'dark' ),
		'dark_border'    => array( 'Dark mode border',        'color',  '#22304C', 'dark' ),
		'dark_text'      => array( 'Dark mode text',          'color',  '#E8ECF4', 'dark' ),
		'dark_muted'     => array( 'Dark mode muted text',    'color',  '#9AA6BC', 'dark' ),

		/* ---------- Typography: families ---------- */
		'font_heading'   => array( 'Heading font',            'font',   'Sora',      'type' ),
		'font_body'      => array( 'Body font',               'font',   'Work Sans', 'type' ),

		/* ---------- Typography: weights ---------- */
		'weight_heading' => array( 'Heading weight',          'select', '700', 'type', array( '500','600','700','800' ) ),
		'weight_body'    => array( 'Body weight',             'select', '400', 'type', array( '300','400','500','600' ) ),

		/* ---------- Typography: letter sizes (px) ---------- */
		'size_base'      => array( 'Base body text',          'number', '16', 'sizes', array( 12, 24 ) ),
		'size_small'     => array( 'Small text / meta',       'number', '13', 'sizes', array( 9,  20 ) ),
		'size_h1'        => array( 'H1 / hero headline',      'number', '34', 'sizes', array( 20, 80 ) ),
		'size_h2'        => array( 'H2 / section title',      'number', '28', 'sizes', array( 16, 60 ) ),
		'size_h3'        => array( 'H3 / card title',         'number', '20', 'sizes', array( 14, 44 ) ),
		'size_button'    => array( 'Button text',             'number', '15', 'sizes', array( 10, 26 ) ),
		'size_nav'       => array( 'Navigation text',         'number', '15', 'sizes', array( 10, 26 ) ),
		'size_mobile_h1' => array( 'H1 on mobile',            'number', '21', 'sizes', array( 16, 48 ) ),

		/* ---------- Typography: rhythm ---------- */
		'line_height'    => array( 'Body line height',        'number', '1.65', 'sizes', array( 1, 2.4, 0.05 ) ),
		'tracking_head'  => array( 'Heading letter spacing (px)', 'number', '-0.4', 'sizes', array( -3, 3, 0.1 ) ),
		'tracking_body'  => array( 'Body letter spacing (px)',    'number', '0',    'sizes', array( -2, 3, 0.1 ) ),

		/* ---------- Shape & layout ---------- */
		'radius'         => array( 'Corner radius (px)',      'number', '14',   'layout', array( 0, 32 ) ),
		'radius_sm'      => array( 'Small radius (px)',       'number', '10',   'layout', array( 0, 24 ) ),
		'container'      => array( 'Max content width (px)',  'number', '1220', 'layout', array( 900, 1600 ) ),
		'section_gap'    => array( 'Space between sections (px)', 'number', '34', 'layout', array( 8, 96 ) ),
	);
}

/** Google Fonts offered in the dashboard dropdowns. */
function np_font_choices() {
	return array(
		'Sora'             => 'Sora',
		'Work Sans'        => 'Work Sans',
		'Inter'            => 'Inter',
		'Manrope'          => 'Manrope',
		'Poppins'          => 'Poppins',
		'Montserrat'       => 'Montserrat',
		'Figtree'          => 'Figtree',
		'Plus Jakarta Sans'=> 'Plus Jakarta Sans',
		'DM Sans'          => 'DM Sans',
		'Source Sans 3'    => 'Source Sans 3',
		'Roboto'           => 'Roboto',
		'Open Sans'        => 'Open Sans',
		'Lato'             => 'Lato',
		'Nunito Sans'      => 'Nunito Sans',
		'Rubik'            => 'Rubik',
		'Noto Sans'        => 'Noto Sans',
		'Mukta'            => 'Mukta',
		'Hind'             => 'Hind',
		'__system__'       => 'System UI (no Google Fonts request)',
	);
}

/** Saved tokens merged over defaults. */
function np_theme_tokens() {
	static $cache = null;
	if ( null !== $cache ) return $cache;

	$saved    = get_option( 'np_theme', array() );
	if ( ! is_array( $saved ) ) $saved = array();
	$defaults = array();
	foreach ( np_token_registry() as $key => $def ) {
		$defaults[ $key ] = $def[2];
	}
	$cache = array_merge( $defaults, array_intersect_key( $saved, $defaults ) );
	return $cache;
}

/** One token value. */
function np_token( $key ) {
	$t = np_theme_tokens();
	return isset( $t[ $key ] ) ? $t[ $key ] : '';
}

/** CSS font-family stack for a token value. */
function np_font_stack( $value ) {
	if ( '__system__' === $value || '' === $value ) {
		return 'system-ui,-apple-system,"Segoe UI",sans-serif';
	}
	return '"' . $value . '",system-ui,-apple-system,"Segoe UI",sans-serif';
}

/* =========================================================
 * 2. GOOGLE FONTS URL (built from the two chosen families)
 * ======================================================= */
function np_google_fonts_url() {
	$families = array();

	$head = np_token( 'font_heading' );
	$body = np_token( 'font_body' );

	foreach ( array( $head => '500;600;700;800', $body => '300;400;500;600;700' ) as $family => $weights ) {
		if ( '__system__' === $family || '' === $family ) continue;
		if ( isset( $families[ $family ] ) ) continue;
		$families[ $family ] = 'family=' . str_replace( ' ', '+', $family ) . ':wght@' . $weights;
	}

	if ( empty( $families ) ) return '';
	return 'https://fonts.googleapis.com/css2?' . implode( '&', $families ) . '&display=swap';
}

/* =========================================================
 * 3. PRINT THE TOKENS AS CSS CUSTOM PROPERTIES
 * =======================================================
 * Printed after style.css is enqueued so dashboard values always win.
 * Every value is escaped; numeric tokens are cast so no raw string can
 * ever reach the stylesheet.
 */
function np_inline_tokens_css() {
	$t = np_theme_tokens();

	$num = function ( $key, $unit = 'px' ) use ( $t ) {
		$v = isset( $t[ $key ] ) ? (float) $t[ $key ] : 0;
		return rtrim( rtrim( number_format( $v, 2, '.', '' ), '0' ), '.' ) . $unit;
	};
	$col = function ( $key ) use ( $t ) {
		$v = isset( $t[ $key ] ) ? sanitize_hex_color( $t[ $key ] ) : '';
		return $v ? $v : '#000000';
	};

	$css  = ':root{';
	foreach ( array(
		'primary', 'primary_dark', 'primary_mid', 'primary_light', 'accent', 'accent_dark',
		'bg', 'surface', 'border', 'text', 'muted', 'govt', 'private', 'danger',
	) as $c ) {
		$css .= '--np-' . str_replace( '_', '-', $c ) . ':' . $col( $c ) . ';';
	}

	$css .= '--np-font-head:' . np_font_stack( $t['font_heading'] ) . ';';
	$css .= '--np-font-body:' . np_font_stack( $t['font_body'] ) . ';';
	$css .= '--np-weight-head:' . (int) $t['weight_heading'] . ';';
	$css .= '--np-weight-body:' . (int) $t['weight_body'] . ';';

	$css .= '--np-size-base:' . $num( 'size_base' ) . ';';
	$css .= '--np-size-small:' . $num( 'size_small' ) . ';';
	$css .= '--np-size-h1:' . $num( 'size_h1' ) . ';';
	$css .= '--np-size-h2:' . $num( 'size_h2' ) . ';';
	$css .= '--np-size-h3:' . $num( 'size_h3' ) . ';';
	$css .= '--np-size-button:' . $num( 'size_button' ) . ';';
	$css .= '--np-size-nav:' . $num( 'size_nav' ) . ';';
	$css .= '--np-size-h1-mobile:' . $num( 'size_mobile_h1' ) . ';';

	$css .= '--np-line:' . $num( 'line_height', '' ) . ';';
	$css .= '--np-track-head:' . $num( 'tracking_head' ) . ';';
	$css .= '--np-track-body:' . $num( 'tracking_body' ) . ';';

	$css .= '--np-radius:' . $num( 'radius' ) . ';';
	$css .= '--np-radius-sm:' . $num( 'radius_sm' ) . ';';
	$css .= '--np-container:' . $num( 'container' ) . ';';
	$css .= '--np-gap:' . $num( 'section_gap' ) . ';';
	$css .= '}';

	/* Dark-mode overrides — the moon toggle adds .np-dark on <html>. */
	$css .= 'html.np-dark{';
	$css .= '--np-bg:' . $col( 'dark_bg' ) . ';';
	$css .= '--np-surface:' . $col( 'dark_surface' ) . ';';
	$css .= '--np-border:' . $col( 'dark_border' ) . ';';
	$css .= '--np-text:' . $col( 'dark_text' ) . ';';
	$css .= '--np-muted:' . $col( 'dark_muted' ) . ';';
	$css .= '}';

	return $css;
}

/* =========================================================
 * 4. ADMIN PAGE — Appearance > NaukriPatra Design
 * ======================================================= */
add_action( 'admin_menu', function () {
	add_theme_page(
		'NaukriPatra Design',
		'NaukriPatra Design',
		'edit_theme_options',
		'np-design',
		'np_design_page'
	);
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'appearance_page_np-design' !== $hook ) return;
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
} );

function np_design_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) return;

	$registry = np_token_registry();

	/* ---- Save ---- */
	if ( isset( $_POST['np_design_nonce'] ) && wp_verify_nonce( $_POST['np_design_nonce'], 'np_design_save' ) ) {

		if ( isset( $_POST['np_reset'] ) ) {
			delete_option( 'np_theme' );
			echo '<div class="notice notice-success is-dismissible"><p>Design reset to the built-in NaukriPatra defaults.</p></div>';
		} else {
			$clean = array();
			foreach ( $registry as $key => $def ) {
				list( $label, $type, $default ) = $def;
				$raw = isset( $_POST[ 'np_' . $key ] ) ? wp_unslash( $_POST[ 'np_' . $key ] ) : '';

				switch ( $type ) {
					case 'color':
						$hex = sanitize_hex_color( trim( (string) $raw ) );
						$clean[ $key ] = $hex ? $hex : $default;
						break;

					case 'font':
						$choices = np_font_choices();
						$clean[ $key ] = isset( $choices[ $raw ] ) ? $raw : $default;
						break;

					case 'select':
						$allowed = isset( $def[4] ) ? $def[4] : array();
						$clean[ $key ] = in_array( (string) $raw, $allowed, true ) ? (string) $raw : $default;
						break;

					case 'number':
						$range = isset( $def[4] ) ? $def[4] : array( 0, 9999 );
						$v = is_numeric( $raw ) ? (float) $raw : (float) $default;
						$v = max( (float) $range[0], min( (float) $range[1], $v ) );
						$clean[ $key ] = (string) $v;
						break;

					default:
						$clean[ $key ] = sanitize_text_field( (string) $raw );
				}
			}
			update_option( 'np_theme', $clean );
			echo '<div class="notice notice-success is-dismissible"><p>Design saved. Clear any page cache to see it on the live site.</p></div>';
		}
	}

	$t = get_option( 'np_theme', array() );
	if ( ! is_array( $t ) ) $t = array();
	foreach ( $registry as $key => $def ) {
		if ( ! isset( $t[ $key ] ) ) $t[ $key ] = $def[2];
	}

	$groups = array(
		'colors' => array( 'Colours', 'Every colour on the site. Change one value here and it updates on every page — home, listings, single job, footer, badges and buttons.' ),
		'type'   => array( 'Fonts', 'Pick the heading and body font. Google Fonts are loaded automatically for whatever you choose; "System UI" makes no external font request at all (fastest option).' ),
		'sizes'  => array( 'Letter sizes &amp; spacing', 'All text sizes in pixels. Mobile scales down from these automatically, and the mobile H1 has its own value.' ),
		'layout' => array( 'Shape &amp; layout', 'Corner rounding, content width and vertical rhythm.' ),
		'dark'   => array( 'Dark mode', 'Colours used when a visitor turns on the moon toggle. Brand colours above are shared by both modes.' ),
	);
	?>
	<div class="wrap np-design-wrap">
		<h1>NaukriPatra Design</h1>
		<p class="np-design-intro">
			This page controls the whole theme's look. Colours, fonts, letter sizes, spacing
			and corner radius are stored as design tokens and applied site-wide instantly —
			no file editing, no code.
		</p>

		<form method="post">
			<?php wp_nonce_field( 'np_design_save', 'np_design_nonce' ); ?>

			<?php foreach ( $groups as $gid => $g ) : ?>
				<h2 class="np-design-h2"><?php echo wp_kses_post( $g[0] ); ?></h2>
				<p class="description"><?php echo wp_kses_post( $g[1] ); ?></p>
				<table class="form-table np-design-table" role="presentation">
					<tbody>
					<?php foreach ( $registry as $key => $def ) :
						if ( $def[3] !== $gid ) continue;
						$label = $def[0];
						$type  = $def[1];
						$val   = $t[ $key ];
						$name  = 'np_' . $key;
						?>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label></th>
							<td>
								<?php if ( 'color' === $type ) : ?>
									<input type="text" class="np-color-field" id="<?php echo esc_attr( $name ); ?>"
										name="<?php echo esc_attr( $name ); ?>"
										value="<?php echo esc_attr( $val ); ?>"
										data-default-color="<?php echo esc_attr( $def[2] ); ?>">

								<?php elseif ( 'font' === $type ) : ?>
									<select id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>">
										<?php foreach ( np_font_choices() as $fval => $flabel ) : ?>
											<option value="<?php echo esc_attr( $fval ); ?>" <?php selected( $val, $fval ); ?>><?php echo esc_html( $flabel ); ?></option>
										<?php endforeach; ?>
									</select>

								<?php elseif ( 'select' === $type ) : ?>
									<select id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>">
										<?php foreach ( $def[4] as $opt ) : ?>
											<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $val, $opt ); ?>><?php echo esc_html( $opt ); ?></option>
										<?php endforeach; ?>
									</select>

								<?php elseif ( 'number' === $type ) :
									$min  = $def[4][0];
									$max  = $def[4][1];
									$step = isset( $def[4][2] ) ? $def[4][2] : 1;
									?>
									<input type="range" class="np-range" min="<?php echo esc_attr( $min ); ?>"
										max="<?php echo esc_attr( $max ); ?>" step="<?php echo esc_attr( $step ); ?>"
										value="<?php echo esc_attr( $val ); ?>"
										data-target="<?php echo esc_attr( $name ); ?>">
									<input type="number" id="<?php echo esc_attr( $name ); ?>"
										name="<?php echo esc_attr( $name ); ?>"
										min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>"
										step="<?php echo esc_attr( $step ); ?>"
										value="<?php echo esc_attr( $val ); ?>" class="np-num">
									<span class="description">default <?php echo esc_html( $def[2] ); ?></span>

								<?php else : ?>
									<input type="text" id="<?php echo esc_attr( $name ); ?>"
										name="<?php echo esc_attr( $name ); ?>"
										value="<?php echo esc_attr( $val ); ?>" class="regular-text">
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>

			<p class="submit">
				<button class="button button-primary button-large">Save design</button>
				<button class="button button-secondary" name="np_reset" value="1"
					onclick="return confirm('Reset every colour, font and size back to the NaukriPatra defaults?');">
					Reset to defaults
				</button>
			</p>
		</form>
	</div>

	<style>
		.np-design-intro{max-width:760px;font-size:14px}
		.np-design-h2{margin-top:30px;padding-top:18px;border-top:1px solid #dcdcde}
		.np-design-table th{width:230px}
		.np-design-table .np-range{vertical-align:middle;width:220px;max-width:45vw}
		.np-design-table .np-num{width:90px;margin-left:10px}
		@media(max-width:782px){.np-design-table .np-range{width:100%;display:block;margin-bottom:8px}}
	</style>
	<script>
	(function(){
		if (window.jQuery && jQuery.fn.wpColorPicker) {
			jQuery('.np-color-field').wpColorPicker();
		}
		document.querySelectorAll('.np-range').forEach(function(r){
			var num = document.getElementById(r.dataset.target);
			if (!num) return;
			r.addEventListener('input', function(){ num.value = r.value; });
			num.addEventListener('input', function(){ r.value = num.value; });
		});
	})();
	</script>
	<?php
}
