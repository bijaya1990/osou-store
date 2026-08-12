/* Live results ticker.
 *
 * The markup is rendered server-side, so the ticker is fully readable without
 * JavaScript. This script only makes the scroll seamless by duplicating the
 * track and setting a duration proportional to the content width. */
(function () {
  'use strict';

  function setup(ticker) {
    var track = ticker.querySelector('.npr-ticker__track');
    if (!track || ticker.getAttribute('data-npr-ready') === '1') {
      return;
    }

    var items = track.innerHTML.trim();
    if (!items) {
      return;
    }

    var viewportWidth = ticker.querySelector('.npr-ticker__viewport').offsetWidth;
    var contentWidth = track.scrollWidth;

    // Repeat the content until it comfortably exceeds the viewport, then
    // duplicate the whole thing once so the loop can wrap without a gap.
    var guard = 0;
    while (track.scrollWidth < viewportWidth * 2 && guard < 12) {
      track.innerHTML += items;
      guard++;
    }
    contentWidth = track.scrollWidth;
    track.innerHTML += track.innerHTML;

    ticker.classList.add('npr-ticker--seamless');

    // ~60 pixels per second, clamped to a sane range.
    var seconds = Math.min(120, Math.max(14, contentWidth / 60));
    track.style.setProperty('--npr-duration', seconds.toFixed(1) + 's');

    ticker.setAttribute('data-npr-ready', '1');
  }

  function init() {
    var tickers = document.querySelectorAll('.npr-ticker[data-npr-scroll="1"]');
    for (var i = 0; i < tickers.length; i++) {
      setup(tickers[i]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  var resizeTimer = null;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      var tickers = document.querySelectorAll('.npr-ticker[data-npr-ready="1"]');
      for (var i = 0; i < tickers.length; i++) {
        // Recalculate duration only; the duplicated markup stays valid.
        var track = tickers[i].querySelector('.npr-ticker__track');
        if (track) {
          var seconds = Math.min(120, Math.max(14, (track.scrollWidth / 2) / 60));
          track.style.setProperty('--npr-duration', seconds.toFixed(1) + 's');
        }
      }
    }, 250);
  });
})();
