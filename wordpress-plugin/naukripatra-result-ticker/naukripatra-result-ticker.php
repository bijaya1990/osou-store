<?php
/**
 * Plugin Name: NaukriPatra Live Results Ticker
 * Description: Shows published results from the NaukriPatra Result Management System as a breaking-news style ticker. Use the shortcode [naukripatra_results_ticker] or the block/widget. Shows "LIVE RESULTS → Coming Soon" while nothing is published.
 * Version:     1.0.0
 * Requires PHP: 7.0
 * License:     GPL-2.0-or-later
 * Text Domain: naukripatra-result-ticker
 */

if (!defined('ABSPATH')) {
    exit;
}

define('NPRT_VERSION', '1.1.0');
define('NPRT_OPTION', 'nprt_settings');
define('NPRT_CACHE_KEY', 'nprt_ticker_items');

// Short lifetimes for the two states that must never get stuck on the
// homepage: an empty list, and a failed database lookup.
define('NPRT_TTL_EMPTY', 30);
define('NPRT_TTL_ERROR', 15);

/* -------------------------------------------------------------------------
 * Settings
 * ---------------------------------------------------------------------- */

function nprt_defaults()
{
    return array(
        // Absolute server path to the result system's config.php.
        'config_path' => rtrim(ABSPATH, '/\\') . '/result/config.php',
        // Public URL of the result system (used for the stylesheet and links).
        'base_url'    => home_url('/result'),
        'label'       => 'LIVE RESULTS',
        'empty_text'  => 'Coming Soon',
        'button_text' => 'CHECK RESULT',
        'limit'       => 10,
        'cache_ttl'   => 300,
    );
}

function nprt_settings()
{
    $saved = get_option(NPRT_OPTION, array());
    return wp_parse_args(is_array($saved) ? $saved : array(), nprt_defaults());
}

add_action('admin_menu', function () {
    add_options_page(
        'Live Results Ticker',
        'Live Results Ticker',
        'manage_options',
        'nprt-settings',
        'nprt_render_settings_page'
    );
});

add_action('admin_init', function () {
    register_setting('nprt_settings_group', NPRT_OPTION, 'nprt_sanitise_settings');
});

function nprt_sanitise_settings($input)
{
    $defaults = nprt_defaults();
    $clean = array();

    $clean['config_path'] = isset($input['config_path']) ? trim(wp_unslash($input['config_path'])) : $defaults['config_path'];
    $clean['base_url']    = isset($input['base_url']) ? esc_url_raw(trim(wp_unslash($input['base_url']))) : $defaults['base_url'];
    $clean['label']       = isset($input['label']) ? sanitize_text_field($input['label']) : $defaults['label'];
    $clean['empty_text']  = isset($input['empty_text']) ? sanitize_text_field($input['empty_text']) : $defaults['empty_text'];
    $clean['button_text'] = isset($input['button_text']) ? sanitize_text_field($input['button_text']) : $defaults['button_text'];
    $clean['limit']       = isset($input['limit']) ? max(1, min(30, (int) $input['limit'])) : $defaults['limit'];
    $clean['cache_ttl']   = isset($input['cache_ttl']) ? max(0, min(3600, (int) $input['cache_ttl'])) : $defaults['cache_ttl'];

    nprt_purge_cache();

    return $clean;
}

/**
 * Drop every cached ticker payload, whatever key it was stored under.
 *
 * Cache keys carry a revision hash, so stale entries are normally never read
 * again and simply expire; this clears them out immediately for the button on
 * the settings page, for setting changes and on deactivation.
 */
function nprt_purge_cache()
{
    global $wpdb;

    delete_transient(NPRT_CACHE_KEY);

    $like = $wpdb->esc_like('_transient_' . NPRT_CACHE_KEY) . '%';
    $timeoutLike = $wpdb->esc_like('_transient_timeout_' . NPRT_CACHE_KEY) . '%';

    $names = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $like,
            $timeoutLike
        )
    );

    foreach ((array) $names as $name) {
        if (strpos($name, '_transient_timeout_') === 0) {
            delete_transient(substr($name, strlen('_transient_timeout_')));
        } else {
            delete_transient(substr($name, strlen('_transient_')));
        }
    }

    // External object caches keep transients out of the options table.
    if (function_exists('wp_cache_flush_group')) {
        wp_cache_flush_group('transient');
    }
}

function nprt_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $purged = false;
    if (isset($_POST['nprt_purge']) && check_admin_referer('nprt_purge_cache')) {
        nprt_purge_cache();
        $purged = true;
    }

    $settings = nprt_settings();
    $status = nprt_connection_status($settings);
    $revision = nprt_revision($settings);
    ?>
    <div class="wrap">
      <h1>Live Results Ticker</h1>

      <?php if ($purged): ?>
        <div class="notice notice-success"><p><strong>Ticker cache cleared.</strong></p></div>
      <?php endif; ?>

      <div class="notice notice-<?php echo $status['ok'] ? 'success' : 'error'; ?>">
        <p><strong><?php echo esc_html($status['message']); ?></strong></p>
      </div>

      <div class="notice notice-info">
        <p>
          <?php if ($revision > 0): ?>
            Automatic refresh is <strong>active</strong>: the result system last signalled a change on
            <?php echo esc_html(date_i18n('d M Y H:i:s', $revision)); ?>. Publishing a result clears this
            cache immediately — no manual action needed.
          <?php else: ?>
            The result system has not signalled a change yet (no
            <code>uploads/.ticker-revision</code> file). The ticker still refreshes on its own within
            about 30 seconds of a result being published. If this message stays after you publish,
            check that <code>result/uploads/</code> is writable.
          <?php endif; ?>
        </p>
        <form method="post" style="margin-bottom:8px">
          <?php wp_nonce_field('nprt_purge_cache'); ?>
          <button class="button" type="submit" name="nprt_purge" value="1">Clear ticker cache now</button>
        </form>
      </div>

      <form method="post" action="options.php">
        <?php settings_fields('nprt_settings_group'); ?>
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="nprt_config_path">Path to result config.php</label></th>
            <td>
              <input class="regular-text code" id="nprt_config_path" type="text"
                     name="<?php echo esc_attr(NPRT_OPTION); ?>[config_path]"
                     value="<?php echo esc_attr($settings['config_path']); ?>">
              <p class="description">Server path, e.g. <code><?php echo esc_html(rtrim(ABSPATH, '/\\') . '/result/config.php'); ?></code></p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="nprt_base_url">Result system URL</label></th>
            <td>
              <input class="regular-text code" id="nprt_base_url" type="url"
                     name="<?php echo esc_attr(NPRT_OPTION); ?>[base_url]"
                     value="<?php echo esc_attr($settings['base_url']); ?>">
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="nprt_label">Ticker label</label></th>
            <td><input class="regular-text" id="nprt_label" type="text" name="<?php echo esc_attr(NPRT_OPTION); ?>[label]" value="<?php echo esc_attr($settings['label']); ?>"></td>
          </tr>
          <tr>
            <th scope="row"><label for="nprt_empty">Text when nothing is published</label></th>
            <td><input class="regular-text" id="nprt_empty" type="text" name="<?php echo esc_attr(NPRT_OPTION); ?>[empty_text]" value="<?php echo esc_attr($settings['empty_text']); ?>"></td>
          </tr>
          <tr>
            <th scope="row"><label for="nprt_button">Button text</label></th>
            <td><input class="regular-text" id="nprt_button" type="text" name="<?php echo esc_attr(NPRT_OPTION); ?>[button_text]" value="<?php echo esc_attr($settings['button_text']); ?>"></td>
          </tr>
          <tr>
            <th scope="row"><label for="nprt_limit">Maximum results shown</label></th>
            <td><input class="small-text" id="nprt_limit" type="number" min="1" max="30" name="<?php echo esc_attr(NPRT_OPTION); ?>[limit]" value="<?php echo esc_attr($settings['limit']); ?>"></td>
          </tr>
          <tr>
            <th scope="row"><label for="nprt_cache">Cache (seconds)</label></th>
            <td>
              <input class="small-text" id="nprt_cache" type="number" min="0" max="3600" name="<?php echo esc_attr(NPRT_OPTION); ?>[cache_ttl]" value="<?php echo esc_attr($settings['cache_ttl']); ?>">
              <p class="description">0 disables caching. Publishing a result appears on the homepage within this many seconds.</p>
            </td>
          </tr>
        </table>
        <?php submit_button(); ?>
      </form>

      <h2>How to show the ticker</h2>
      <p>Add the shortcode <code>[naukripatra_results_ticker]</code> to any page, post or widget area.</p>
      <p>To place it on the homepage from your theme, add this to <code>header.php</code>:</p>
      <pre><code>&lt;?php echo do_shortcode('[naukripatra_results_ticker]'); ?&gt;</code></pre>
      <p>Shortcode attributes: <code>label</code>, <code>empty_text</code>, <code>button_text</code>, <code>limit</code>.</p>
    </div>
    <?php
}

/* -------------------------------------------------------------------------
 * Data access
 * ---------------------------------------------------------------------- */

/**
 * Connect to the result system's database using its own config.php.
 * Returns a PDO handle plus the table prefix, or a WP_Error.
 */
function nprt_connect(array $settings)
{
    $path = $settings['config_path'];

    if ($path === '' || !is_file($path) || !is_readable($path)) {
        return new WP_Error('nprt_no_config', 'config.php of the result system was not found at that path.');
    }

    // config.php only defines constants; load it once.
    if (!defined('NPR_DB_HOST')) {
        require_once $path;
    }
    if (!defined('NPR_DB_HOST') || !defined('NPR_TABLE_PREFIX')) {
        return new WP_Error('nprt_bad_config', 'That file is not a valid result system config.php.');
    }
    if (!class_exists('PDO')) {
        return new WP_Error('nprt_no_pdo', 'PDO is not available on this server.');
    }

    try {
        $pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=%s', NPR_DB_HOST, NPR_DB_NAME, NPR_DB_CHARSET),
            NPR_DB_USER,
            NPR_DB_PASS,
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC)
        );
    } catch (Exception $e) {
        return new WP_Error('nprt_db', 'Could not connect to the result database.');
    }

    return array('pdo' => $pdo, 'prefix' => NPR_TABLE_PREFIX);
}

function nprt_connection_status(array $settings)
{
    $connection = nprt_connect($settings);
    if (is_wp_error($connection)) {
        return array('ok' => false, 'message' => $connection->get_error_message());
    }

    try {
        $count = $connection['pdo']
            ->query('SELECT COUNT(*) FROM `' . $connection['prefix'] . 'results` WHERE status = \'published\'')
            ->fetchColumn();
    } catch (Exception $e) {
        return array('ok' => false, 'message' => 'Connected, but the result tables were not found. Run install.php first.');
    }

    return array(
        'ok'      => true,
        'message' => sprintf('Connected to the result system. %d published result(s).', (int) $count),
    );
}

/**
 * Revision stamp of the result system.
 *
 * The result system rewrites uploads/.ticker-revision whenever a result is
 * published, unpublished, edited, toggled or deleted. Reading its mtime is a
 * single filesystem stat — far cheaper than a database round trip — and it
 * goes into the cache key, so a newly published result invalidates the cached
 * copy at once instead of waiting for the TTL to lapse.
 *
 * Returns 0 when the file does not exist (older result system, or uploads/ not
 * readable); the short TTLs below then keep the ticker fresh anyway.
 */
function nprt_revision(array $settings)
{
    $configPath = $settings['config_path'];
    if ($configPath === '') {
        return 0;
    }

    $stamp = dirname($configPath) . '/uploads/.ticker-revision';

    // clearstatcache so a stamp written moments ago is not missed.
    clearstatcache(true, $stamp);

    return is_file($stamp) ? (int) filemtime($stamp) : 0;
}

/**
 * Cache key for the current settings and revision. Changing the revision, the
 * limit or the result URL yields a different key, so stale entries are simply
 * never read again (they expire on their own).
 */
function nprt_cache_key(array $settings)
{
    return NPRT_CACHE_KEY . '_' . substr(md5(
        nprt_revision($settings) . '|' .
        $settings['base_url'] . '|' .
        (int) $settings['limit'] . '|' .
        $settings['button_text']
    ), 0, 20);
}

/**
 * Published, ticker-enabled results — cached in a transient.
 *
 * Three different lifetimes are used on purpose:
 *   - a populated list is cached for the full configured TTL;
 *   - an empty list is cached only briefly, so "Coming Soon" can never be
 *     pinned to the homepage for minutes after the first result goes live;
 *   - a connection failure is barely cached at all, because an unreachable
 *     database must not be mistaken for "nothing published".
 */
function nprt_get_items(array $settings)
{
    $ttl = (int) $settings['cache_ttl'];
    $key = nprt_cache_key($settings);

    if ($ttl > 0) {
        $cached = get_transient($key);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $items = array();
    $failed = false;
    $connection = nprt_connect($settings);

    if (is_wp_error($connection)) {
        $failed = true;
    } else {
        $limit = max(1, min(30, (int) $settings['limit']));
        try {
            $sql = 'SELECT result_title, institution_name, examination_name, academic_session, slug,
                           result_type, external_url, external_button_text
                      FROM `' . $connection['prefix'] . 'results`
                     WHERE status = \'published\' AND show_on_ticker = 1
                  ORDER BY COALESCE(published_at, created_at) DESC, id DESC
                     LIMIT ' . $limit;
            $rows = $connection['pdo']->query($sql)->fetchAll();
        } catch (Exception $e) {
            $rows = array();
            $failed = true;
        }

        $base = rtrim($settings['base_url'], '/');
        foreach ($rows as $row) {
            $external = isset($row['result_type']) && $row['result_type'] === 'external';
            $url = $external ? (string) $row['external_url'] : $base . '/' . rawurlencode($row['slug']) . '/';

            // Never emit anything but a plain http(s) link.
            if (!preg_match('~^https?://~i', $url)) {
                continue;
            }

            $button = $settings['button_text'];
            if ($external && !empty($row['external_button_text'])) {
                $button = $row['external_button_text'];
            }

            $items[] = array(
                'title'    => $row['result_title'] !== '' ? $row['result_title'] : $row['examination_name'],
                'url'      => $url,
                'external' => $external,
                'button'   => $button,
            );
        }
    }

    if ($ttl > 0) {
        if ($failed) {
            $store = min($ttl, NPRT_TTL_ERROR);   // never pin a failure
        } elseif (!$items) {
            $store = min($ttl, NPRT_TTL_EMPTY);   // never pin "Coming Soon"
        } else {
            $store = $ttl;
        }
        set_transient($key, $items, $store);
    }

    return $items;
}

/* -------------------------------------------------------------------------
 * Rendering
 * ---------------------------------------------------------------------- */

add_action('wp_enqueue_scripts', function () {
    $settings = nprt_settings();
    $base = rtrim($settings['base_url'], '/');

    wp_register_style('nprt-ticker', $base . '/public/assets/css/ticker.css', array(), NPRT_VERSION);
    wp_register_script('nprt-ticker', $base . '/public/assets/js/ticker.js', array(), NPRT_VERSION, true);
});

function nprt_render_ticker($atts = array())
{
    $settings = nprt_settings();
    $atts = shortcode_atts(array(
        'label'       => $settings['label'],
        'empty_text'  => $settings['empty_text'],
        'button_text' => $settings['button_text'],
        'limit'       => $settings['limit'],
    ), is_array($atts) ? $atts : array(), 'naukripatra_results_ticker');

    $settings['label'] = $atts['label'];
    $settings['empty_text'] = $atts['empty_text'];
    $settings['button_text'] = $atts['button_text'];
    $settings['limit'] = max(1, min(30, (int) $atts['limit']));

    $items = nprt_get_items($settings);

    // Styles and script are registered on wp_enqueue_scripts. When the ticker
    // is printed from header.php / front-page.php that hook has already fired
    // and wp_head() may already be flushed, in which case enqueuing silently
    // does nothing — so fall back to printing the tag inline, once.
    wp_enqueue_style('nprt-ticker');
    wp_enqueue_script('nprt-ticker');

    $lateAssets = '';
    if (did_action('wp_head') && !wp_style_is('nprt-ticker', 'done')) {
        static $printedLate = false;
        if (!$printedLate) {
            $printedLate = true;
            $base = rtrim($settings['base_url'], '/');
            $lateAssets =
                '<link rel="stylesheet" href="' . esc_url($base . '/public/assets/css/ticker.css?ver=' . NPRT_VERSION) . '">' .
                '<script src="' . esc_url($base . '/public/assets/js/ticker.js?ver=' . NPRT_VERSION) . '" defer></script>';
        }
    }

    // The JSON feed is served by the result system itself, so it is never held
    // by a WordPress page cache. The script below re-reads it and updates the
    // ticker in place when the cached HTML turns out to be out of date.
    $feedUrl = rtrim($settings['base_url'], '/') . '/ticker.json';

    ob_start();
    echo $lateAssets;
    ?>
    <div class="npr-ticker" <?php echo $items ? 'data-npr-scroll="1"' : ''; ?>
         data-npr-feed="<?php echo esc_url($feedUrl); ?>"
         data-npr-empty="<?php echo esc_attr($settings['empty_text']); ?>"
         data-npr-button="<?php echo esc_attr($settings['button_text']); ?>"
         data-npr-limit="<?php echo (int) $settings['limit']; ?>"
         data-npr-count="<?php echo count($items); ?>"
         role="region" aria-label="<?php echo esc_attr($settings['label']); ?>">
      <div class="npr-ticker__label"><span class="npr-ticker__dot" aria-hidden="true"></span><?php echo esc_html($settings['label']); ?></div>
      <div class="npr-ticker__viewport">
        <?php if (!$items): ?>
          <div class="npr-ticker__empty"><?php echo esc_html($settings['empty_text']); ?></div>
        <?php else: ?>
          <div class="npr-ticker__track">
            <?php foreach ($items as $item): ?>
              <a class="npr-ticker__item"
                 href="<?php echo esc_url($item['url']); ?>"
                 <?php echo $item['external'] ? 'target="_blank" rel="noopener nofollow external"' : ''; ?>>
                <span class="npr-ticker__text"><?php echo esc_html($item['title']); ?></span>
                <span class="npr-ticker__cta"><?php echo esc_html($item['button']); ?></span>
              </a>
              <span class="npr-ticker__sep" aria-hidden="true">&bull;</span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php
    return trim(ob_get_clean());
}

add_shortcode('naukripatra_results_ticker', 'nprt_render_ticker');
// Backwards-friendly alias.
add_shortcode('naukripatra_result_ticker', 'nprt_render_ticker');

// Allow the shortcode inside text widgets.
add_filter('widget_text', 'do_shortcode');

register_deactivation_hook(__FILE__, 'nprt_purge_cache');
register_activation_hook(__FILE__, 'nprt_purge_cache');
