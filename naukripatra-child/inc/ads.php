<?php
/**
 * NaukriPatra — Ad Manager (v3.0)
 * =====================================================================
 * Upgrade of the old Settings > NaukriPatra Ads page.
 *
 * WHAT CHANGED vs v2.8
 *   - Each slot is now a structured array instead of a flat code string:
 *       np_ads['header'] = [ type, image_id, image_url, link, alt,
 *                            code, start, end, enabled, sponsored ]
 *   - Every slot can be EITHER an image ad (uploaded from the WP Media
 *     Library, with a destination link, alt text and an optional
 *     start/end schedule) OR raw ad code (AdSense, Adsterra, Media.net,
 *     PropellerAds, Ezoic, any network's HTML/JS tag).
 *   - Standard IAB slot inventory added for Home / Archive / Single,
 *     with a mobile size noted per slot.
 *
 * BACKWARD COMPATIBLE: the five original slots (header, list_top,
 * in_article, after_content, footer) still exist with the same IDs, and
 * any value saved by v2.8 as a plain string is still read correctly and
 * rendered as ad code.
 *
 * An empty slot renders nothing at all — no empty box, no broken layout.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================================================
 * 1. SLOT INVENTORY
 * =======================================================
 * id => [ label, desktop size, mobile size, group ]
 */
function np_ad_slots() {
	return array(

		/* ---- Original v2.8 slots (kept, never removed) ---- */
		'header'        => array( 'Below the header (all pages)',              '728x90',  '320x50',  'global' ),
		'list_top'      => array( 'Above the job list (archive pages)',        '728x90',  '320x50',  'global' ),
		'in_article'    => array( 'Inside the article (after 2nd paragraph)',  '336x280', '300x250', 'global' ),
		'after_content' => array( 'End of article (before share buttons)',     '728x90',  '320x50',  'global' ),
		'footer'        => array( 'Above the footer',                          '728x90',  '320x50',  'global' ),

		/* ---- Homepage ---- */
		'home_top'      => array( 'Home — below header banner',                '728x90',  '320x50',  'home' ),
		'home_hero'     => array( 'Home — below hero / billboard',             '970x250', '320x100', 'home' ),
		'home_infeed'   => array( 'Home — in-feed, mid page',                  '300x250', '300x250', 'home' ),
		'home_footer'   => array( 'Home — before footer CTA',                  '728x90',  '320x50',  'home' ),

		/* ---- Archive / listing ---- */
		'arch_filter'   => array( 'Listing — below the filter bar',            '728x90',  '320x50',  'archive' ),
		'arch_native'   => array( 'Listing — native, splits list at item 3',   'Native',  'Native',  'archive' ),
		'arch_bottom'   => array( 'Listing — below the list',                  '300x250', '300x250', 'archive' ),

		/* ---- Single job post ---- */
		'single_top'    => array( 'Single — below breadcrumb',                 '728x90',  '320x50',  'single' ),
		'single_mid'    => array( 'Single — in-article (Eligibility → Apply)', '336x280', '300x250', 'single' ),
		'single_side_a' => array( 'Single — sidebar box',                      '300x250', 'hidden',  'single' ),
		'single_side_b' => array( 'Single — sidebar skyscraper',               '160x600', 'hidden',  'single' ),
		'single_footer' => array( 'Single — above footer',                     '728x90',  '320x50',  'single' ),
	);
}

/** Default shape of one slot. */
function np_ad_slot_defaults() {
	return array(
		'type'      => 'code',   // code | image
		'image_id'  => 0,
		'image_url' => '',
		'link'      => '',
		'alt'       => '',
		'code'      => '',
		'start'     => '',
		'end'       => '',
		'enabled'   => 1,
	);
}

/**
 * One slot's stored config, normalised.
 * Reads the v2.8 flat-string format transparently.
 */
function np_get_ad_config( $slot ) {
	$all = get_option( 'np_ads', array() );
	if ( ! is_array( $all ) ) $all = array();

	$raw = isset( $all[ $slot ] ) ? $all[ $slot ] : array();

	// v2.8 format: the slot was a plain ad-code string.
	if ( is_string( $raw ) ) {
		$raw = array( 'type' => 'code', 'code' => $raw, 'enabled' => 1 );
	}
	if ( ! is_array( $raw ) ) $raw = array();

	return array_merge( np_ad_slot_defaults(), $raw );
}

/** Is this slot inside its scheduled window right now? */
function np_ad_in_schedule( $cfg ) {
	$today = current_time( 'Y-m-d' );
	if ( ! empty( $cfg['start'] ) && $today < $cfg['start'] ) return false;
	if ( ! empty( $cfg['end'] )   && $today > $cfg['end'] )   return false;
	return true;
}

/**
 * Rendered HTML for a slot — '' when the slot is empty, disabled or
 * outside its schedule.
 *
 * Kept as the same function name/signature as v2.8 so every existing
 * call site keeps working.
 */
function np_get_ad( $slot ) {
	$cfg = np_get_ad_config( $slot );

	if ( empty( $cfg['enabled'] ) )   return '';
	if ( ! np_ad_in_schedule( $cfg ) ) return '';

	if ( 'image' === $cfg['type'] ) {
		$url = '';
		if ( ! empty( $cfg['image_id'] ) ) {
			$url = wp_get_attachment_image_url( (int) $cfg['image_id'], 'full' );
		}
		if ( ! $url && ! empty( $cfg['image_url'] ) ) {
			$url = $cfg['image_url'];
		}
		if ( ! $url ) return '';

		$alt = $cfg['alt'] ? $cfg['alt'] : 'Advertisement';
		$img = '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async">';

		if ( ! empty( $cfg['link'] ) ) {
			return '<a class="np-ad-img" href="' . esc_url( $cfg['link'] ) . '" target="_blank" rel="noopener sponsored nofollow">' . $img . '</a>';
		}
		return '<span class="np-ad-img">' . $img . '</span>';
	}

	// Raw ad code (AdSense / Adsterra / Media.net / any network tag).
	return trim( (string) $cfg['code'] );
}

/**
 * Echo a slot wrapped in its sizing container.
 * Same signature as v2.8; $class is still appended.
 */
function np_ad_slot( $slot, $class = '' ) {
	$html = np_get_ad( $slot );
	if ( '' === $html ) return;

	$slots  = np_ad_slots();
	$size   = isset( $slots[ $slot ] ) ? $slots[ $slot ][1] : '';
	$mobile = isset( $slots[ $slot ] ) ? $slots[ $slot ][2] : '';

	$classes = 'np-ad np-ad--' . sanitize_html_class( str_replace( '_', '-', $slot ) );
	if ( $size )   $classes .= ' np-ad-size-' . sanitize_html_class( strtolower( str_replace( 'x', '-', $size ) ) );
	if ( 'hidden' === $mobile ) $classes .= ' np-ad-desktop-only';
	if ( $class )  $classes .= ' ' . $class;

	echo '<div class="' . esc_attr( $classes ) . '" aria-label="Advertisement">'
		. '<span class="np-ad-label">Advertisement</span>'
		. $html
		. '</div>';
}

/* =========================================================
 * 2. ADMIN PAGE — Settings > NaukriPatra Ads
 * ======================================================= */
add_action( 'admin_menu', function () {
	add_options_page( 'NaukriPatra Ads', 'NaukriPatra Ads', 'manage_options', 'np-ads', 'np_ads_page' );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'settings_page_np-ads' !== $hook ) return;
	wp_enqueue_media();
} );

function np_ads_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	$slots = np_ad_slots();

	/* ---- Save ---- */
	if ( isset( $_POST['np_ads_nonce'] ) && wp_verify_nonce( $_POST['np_ads_nonce'], 'np_ads_save' ) ) {
		$out = array();
		foreach ( $slots as $id => $meta ) {
			$p = function ( $field ) use ( $id ) {
				$key = 'np_ad_' . $id . '_' . $field;
				return isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
			};

			$type = ( 'image' === $p( 'type' ) ) ? 'image' : 'code';

			$date = function ( $v ) {
				$v = trim( (string) $v );
				return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $v ) ? $v : '';
			};

			$out[ $id ] = array(
				'type'      => $type,
				'image_id'  => (int) $p( 'image_id' ),
				'image_url' => esc_url_raw( trim( (string) $p( 'image_url' ) ) ),
				'link'      => esc_url_raw( trim( (string) $p( 'link' ) ) ),
				'alt'       => sanitize_text_field( (string) $p( 'alt' ) ),
				// Ad code is intentionally stored raw: AdSense/Adsterra tags
				// are <script>/<ins> markup and must survive verbatim. Only
				// users with manage_options (admins) can reach this form, and
				// unfiltered_html is the same capability WordPress itself uses
				// for the Custom HTML widget.
				'code'      => (string) $p( 'code' ),
				'start'     => $date( $p( 'start' ) ),
				'end'       => $date( $p( 'end' ) ),
				'enabled'   => $p( 'enabled' ) ? 1 : 0,
			);
		}
		update_option( 'np_ads', $out );
		echo '<div class="notice notice-success is-dismissible"><p>Ad slots saved.</p></div>';
	}

	$groups = array(
		'global'  => array( 'Site-wide slots', 'These appear on every page type. They are the five slots that existed before — your saved ad codes are still here.' ),
		'home'    => array( 'Homepage slots', 'Standard IAB sizes placed down the homepage.' ),
		'archive' => array( 'Listing / category slots', 'Shown on state, category, and search result pages.' ),
		'single'  => array( 'Single job page slots', 'Shown on an individual job post. The two sidebar slots sit in the sticky sidebar on desktop and are hidden on mobile, where the in-article slot carries the impression instead.' ),
	);
	?>
	<div class="wrap np-ads-wrap">
		<h1>NaukriPatra Ads</h1>
		<p class="np-ads-intro">
			Each slot below can run <strong>either</strong> an image banner you upload here
			(with its own click-through link) <strong>or</strong> a raw ad tag pasted from any
			network — AdSense, Adsterra, Media.net, PropellerAds, Ezoic and so on.
			Leave a slot empty and nothing is printed at all: no gap, no empty box.
		</p>

		<form method="post">
			<?php wp_nonce_field( 'np_ads_save', 'np_ads_nonce' ); ?>

			<?php foreach ( $groups as $gid => $g ) : ?>
				<h2 class="np-ads-h2"><?php echo esc_html( $g[0] ); ?></h2>
				<p class="description"><?php echo esc_html( $g[1] ); ?></p>

				<?php foreach ( $slots as $id => $meta ) :
					if ( $meta[3] !== $gid ) continue;
					$cfg  = np_get_ad_config( $id );
					$n    = 'np_ad_' . $id . '_';
					$prev = $cfg['image_id'] ? wp_get_attachment_image_url( (int) $cfg['image_id'], 'medium' ) : $cfg['image_url'];
					?>
					<div class="np-slot" data-slot="<?php echo esc_attr( $id ); ?>">
						<div class="np-slot-head">
							<h3><?php echo esc_html( $meta[0] ); ?></h3>
							<span class="np-slot-size">Desktop <?php echo esc_html( $meta[1] ); ?>
								&middot; Mobile <?php echo esc_html( $meta[2] ); ?></span>
							<label class="np-slot-on">
								<input type="checkbox" name="<?php echo esc_attr( $n ); ?>enabled" value="1"
									<?php checked( ! empty( $cfg['enabled'] ) ); ?>> Active
							</label>
						</div>

						<p class="np-slot-type">
							<label><input type="radio" name="<?php echo esc_attr( $n ); ?>type" value="code"
								<?php checked( $cfg['type'], 'code' ); ?> class="np-type-radio"> Ad code (AdSense / Adsterra / any network)</label>
							<label><input type="radio" name="<?php echo esc_attr( $n ); ?>type" value="image"
								<?php checked( $cfg['type'], 'image' ); ?> class="np-type-radio"> Image banner + link</label>
						</p>

						<div class="np-pane np-pane-code" <?php echo 'image' === $cfg['type'] ? 'style="display:none"' : ''; ?>>
							<textarea name="<?php echo esc_attr( $n ); ?>code" rows="5"
								placeholder="Paste the full ad tag here, exactly as the network gives it."><?php
								echo esc_textarea( $cfg['code'] ); ?></textarea>
						</div>

						<div class="np-pane np-pane-image" <?php echo 'image' === $cfg['type'] ? '' : 'style="display:none"'; ?>>
							<div class="np-img-row">
								<div class="np-img-preview">
									<?php if ( $prev ) : ?>
										<img src="<?php echo esc_url( $prev ); ?>" alt="">
									<?php else : ?>
										<span class="np-img-empty">No image selected</span>
									<?php endif; ?>
								</div>
								<div class="np-img-fields">
									<input type="hidden" class="np-img-id" name="<?php echo esc_attr( $n ); ?>image_id"
										value="<?php echo (int) $cfg['image_id']; ?>">
									<p>
										<button type="button" class="button np-img-pick">Choose / upload image</button>
										<button type="button" class="button-link np-img-clear">Remove</button>
									</p>
									<p>
										<label>Or paste an image URL<br>
										<input type="url" class="np-img-url regular-text" name="<?php echo esc_attr( $n ); ?>image_url"
											value="<?php echo esc_attr( $cfg['image_url'] ); ?>"
											placeholder="https://..."></label>
									</p>
									<p>
										<label>Destination link (where the banner sends the visitor)<br>
										<input type="url" class="regular-text" name="<?php echo esc_attr( $n ); ?>link"
											value="<?php echo esc_attr( $cfg['link'] ); ?>"
											placeholder="https://advertiser.example/offer"></label>
									</p>
									<p>
										<label>Alt text<br>
										<input type="text" class="regular-text" name="<?php echo esc_attr( $n ); ?>alt"
											value="<?php echo esc_attr( $cfg['alt'] ); ?>"
											placeholder="Short description of the banner"></label>
									</p>
								</div>
							</div>
						</div>

						<p class="np-slot-sched">
							<label>Start date <input type="date" name="<?php echo esc_attr( $n ); ?>start"
								value="<?php echo esc_attr( $cfg['start'] ); ?>"></label>
							<label>End date <input type="date" name="<?php echo esc_attr( $n ); ?>end"
								value="<?php echo esc_attr( $cfg['end'] ); ?>"></label>
							<span class="description">Optional. Leave both empty to run the slot permanently.</span>
						</p>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>

			<p class="submit"><button class="button button-primary button-large">Save all ad slots</button></p>
		</form>
	</div>

	<style>
		.np-ads-intro{max-width:820px;font-size:14px}
		.np-ads-h2{margin-top:32px;padding-top:18px;border-top:1px solid #dcdcde}
		.np-slot{background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:16px 18px;margin:14px 0;max-width:900px}
		.np-slot-head{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:6px}
		.np-slot-head h3{margin:0;font-size:15px}
		.np-slot-size{font-size:12px;color:#646970;background:#f0f0f1;border-radius:3px;padding:2px 8px}
		.np-slot-on{margin-left:auto;font-size:13px}
		.np-slot-type label{margin-right:22px;font-size:13px}
		.np-pane textarea{width:100%;font-family:Menlo,Consolas,monospace;font-size:12px}
		.np-img-row{display:flex;gap:18px;flex-wrap:wrap}
		.np-img-preview{width:220px;min-height:90px;border:1px dashed #c3c4c7;border-radius:4px;display:flex;
			align-items:center;justify-content:center;background:#fafafa;padding:6px}
		.np-img-preview img{max-width:100%;height:auto}
		.np-img-empty{color:#8c8f94;font-size:12px}
		.np-img-fields{flex:1;min-width:280px}
		.np-img-fields p{margin:8px 0}
		.np-slot-sched{margin-top:12px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;font-size:13px}
		@media(max-width:782px){.np-slot-on{margin-left:0}}
	</style>
	<script>
	(function(){
		document.querySelectorAll('.np-slot').forEach(function(slot){
			slot.querySelectorAll('.np-type-radio').forEach(function(r){
				r.addEventListener('change', function(){
					slot.querySelector('.np-pane-code').style.display  = (r.value === 'code')  ? '' : 'none';
					slot.querySelector('.np-pane-image').style.display = (r.value === 'image') ? '' : 'none';
				});
			});

			var pick = slot.querySelector('.np-img-pick');
			if (pick && window.wp && wp.media) {
				var frame;
				pick.addEventListener('click', function(e){
					e.preventDefault();
					if (!frame) {
						frame = wp.media({ title: 'Choose an ad banner', button: { text: 'Use this image' }, multiple: false });
						frame.on('select', function(){
							var a = frame.state().get('selection').first().toJSON();
							slot.querySelector('.np-img-id').value = a.id;
							slot.querySelector('.np-img-url').value = '';
							var box = slot.querySelector('.np-img-preview');
							box.innerHTML = '<img alt="">';
							box.querySelector('img').src = (a.sizes && a.sizes.medium) ? a.sizes.medium.url : a.url;
						});
					}
					frame.open();
				});
			}

			var clear = slot.querySelector('.np-img-clear');
			if (clear) {
				clear.addEventListener('click', function(e){
					e.preventDefault();
					slot.querySelector('.np-img-id').value = 0;
					slot.querySelector('.np-img-url').value = '';
					slot.querySelector('.np-img-preview').innerHTML = '<span class="np-img-empty">No image selected</span>';
				});
			}
		});
	})();
	</script>
	<?php
}
