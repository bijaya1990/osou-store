<?php
/**
 * Ticker markup for direct PHP integration (an alternative to the WordPress
 * plugin — useful in a child theme's header.php):
 *
 *   <?php include '/home/USER/public_html/result/ticker-widget.php'; ?>
 *
 * It prints the same markup the plugin renders and enqueues nothing, so add
 * the stylesheet once in your theme's <head>:
 *
 *   <link rel="stylesheet" href="https://naukripatra.in/result/public/assets/css/ticker.css">
 *   <script src="https://naukripatra.in/result/public/assets/js/ticker.js" defer></script>
 */

require_once __DIR__ . '/includes/bootstrap.php';

$nprTickerLabel = isset($nprTickerLabel) ? $nprTickerLabel : 'LIVE RESULTS';
$nprTickerEmpty = isset($nprTickerEmpty) ? $nprTickerEmpty : 'Coming Soon';
$nprTickerButton = isset($nprTickerButton) ? $nprTickerButton : 'CHECK RESULT';
$nprTickerLimit = isset($nprTickerLimit) ? (int) $nprTickerLimit : 10;

$nprTickerItems = npr_ticker_results($nprTickerLimit);
?>
<div class="npr-ticker"<?php echo $nprTickerItems ? ' data-npr-scroll="1"' : ''; ?> role="region" aria-label="<?php echo e($nprTickerLabel); ?>">
  <div class="npr-ticker__label"><span class="npr-ticker__dot" aria-hidden="true"></span><?php echo e($nprTickerLabel); ?></div>
  <div class="npr-ticker__viewport">
    <?php if (!$nprTickerItems): ?>
      <div class="npr-ticker__empty"><?php echo e($nprTickerEmpty); ?></div>
    <?php else: ?>
      <div class="npr-ticker__track">
        <?php foreach ($nprTickerItems as $nprTickerRow): ?>
          <?php
            $nprTickerUrl = npr_result_link($nprTickerRow);
            $nprTickerExternal = npr_is_external($nprTickerRow);
            if ($nprTickerExternal && !preg_match('~^https?://~i', $nprTickerUrl)) {
                continue; // never emit anything but a plain http(s) link
            }
            $nprTickerCta = $nprTickerExternal && !empty($nprTickerRow['external_button_text'])
                ? $nprTickerRow['external_button_text']
                : $nprTickerButton;
          ?>
          <a class="npr-ticker__item" href="<?php echo e($nprTickerUrl); ?>"
             <?php echo $nprTickerExternal ? 'target="_blank" rel="noopener nofollow external"' : ''; ?>>
            <span class="npr-ticker__text"><?php echo e($nprTickerRow['result_title']); ?></span>
            <span class="npr-ticker__cta"><?php echo e($nprTickerCta); ?></span>
          </a>
          <span class="npr-ticker__sep" aria-hidden="true">&bull;</span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
