/* Marksheet actions: print / save as PDF. */
(function () {
  'use strict';

  function print() {
    window.print();
  }

  var printBtn = document.getElementById('npr-print');
  var saveBtn = document.getElementById('npr-save');

  if (printBtn) {
    printBtn.addEventListener('click', print);
  }
  if (saveBtn) {
    saveBtn.setAttribute('title', 'Choose "Save as PDF" as the destination in the print dialog.');
    saveBtn.addEventListener('click', print);
  }

  // Bring the marksheet into view after a successful lookup on mobile.
  var sheet = document.getElementById('marksheet');
  if (sheet && window.location.hash === '#check') {
    sheet.scrollIntoView({ block: 'start' });
  }
})();
