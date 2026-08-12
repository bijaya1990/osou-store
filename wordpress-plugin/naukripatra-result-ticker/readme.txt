=== NaukriPatra Live Results Ticker ===
Requires PHP: 7.0
Stable tag: 1.1.1
License: GPLv2 or later

Shows results published in the NaukriPatra Result Management System as a
breaking-news style ticker on your WordPress site.

== Description ==

The plugin reads directly from the result system's database (using its own
config.php) and renders a scrolling ticker of published results. Internal
results link to the result checking page; external result links open the
official website that published them.

While nothing is published the ticker shows: LIVE RESULTS -> Coming Soon

Features:

* Shortcode [naukripatra_results_ticker]
* Only results with status = published and "Show on homepage ticker" = Yes
* Newest published results first
* Smooth right-to-left scroll, pauses on hover, responsive, mobile friendly
* Respects prefers-reduced-motion
* Cached in a transient (default 5 minutes, configurable)
* Cache is invalidated automatically when a result is published: the cache key
  follows the result system's revision stamp (result/uploads/.ticker-revision)
* "Coming Soon" is cached for 30 seconds only, and a database error for 15, so
  the empty state can never get stuck on the homepage
* Refreshes itself from /result/ticker.json in the browser, so a WordPress page
  cache or CDN cannot keep serving a stale ticker
* "Clear ticker cache now" button on the settings page

== Installation ==

1. Copy this folder to wp-content/plugins/ and activate the plugin.
2. Go to Settings -> Live Results Ticker.
3. Set the server path to result/config.php and the public result system URL.
   The page confirms whether the connection works.
4. Add [naukripatra_results_ticker] to your homepage, a widget, or a template:
   <?php echo do_shortcode('[naukripatra_results_ticker]'); ?>

== Shortcode attributes ==

* label       - text in the red badge (default "LIVE RESULTS")
* empty_text  - shown when nothing is published (default "Coming Soon")
* button_text - call-to-action label (default "CHECK RESULT")
* limit       - maximum results shown (default 10)

Example: [naukripatra_results_ticker label="RESULTS OUT" limit="5"]

== Changelog ==

= 1.1.1 =
* Fixed: a single short result was repeated across the width of the screen.
  The ticker now scrolls only when the content is wider than the screen, and
  duplicates the content exactly once instead of as many times as it fits.
* Fixed: the CHECK RESULT badge could overlap the result title. Layout-critical
  properties are now pinned so theme CSS cannot shift the ticker's contents.
* Fixed: deleting or unpublishing a result now clears the ticker without any
  manual cache clearing, including behind a page cache.
* Falls back to /result/index.php?npr_route=ticker.json when mod_rewrite is
  unavailable, and warns in the browser console if the feed cannot be read.
* Populated lists are cached for 60s at most while the result system cannot
  write its revision stamp.

= 1.1.0 =
* Fixed: a newly published result could stay invisible because an empty result
  list was cached for the full TTL, and a failed database connection was cached
  as "nothing published".
* Cache key now follows the result system's revision stamp (instant refresh).
* Short cache lifetimes for the empty and error states.
* Browser-side refresh from the result system's JSON feed, which bypasses
  WordPress page caches and CDNs.
* Added a "Clear ticker cache now" button and a status line on the settings page.
* Stylesheet/script now also load when the shortcode runs from header.php or
  front-page.php after wp_head() has already been printed.

= 1.0.0 =
* First release: internal and external results, settings page, caching.
