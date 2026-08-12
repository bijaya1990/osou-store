/* Admin helpers.
 *
 * 1. Show only the fields that apply to the selected result type.
 * 2. Show the subject-name / max-marks boxes only for subject mappings. */
(function () {
  'use strict';

  /* ---- result type (internal vs external link) ---- */

  function currentType() {
    var checked = document.querySelector('input[name="result_type"]:checked');
    return checked ? checked.value : 'internal';
  }

  function applyType() {
    var type = currentType();
    var blocks = document.querySelectorAll('[data-npr-when]');

    for (var i = 0; i < blocks.length; i++) {
      var block = blocks[i];
      var wanted = block.getAttribute('data-npr-when');
      var show = wanted === type;
      block.style.display = show ? '' : 'none';

      var url = block.querySelector('input[name="external_url"]');
      if (url) {
        // Only require the URL while the external mode is actually selected.
        if (show) {
          url.setAttribute('required', 'required');
        } else {
          url.removeAttribute('required');
        }
      }
    }

    var cards = document.querySelectorAll('.radio-card');
    for (var j = 0; j < cards.length; j++) {
      var input = cards[j].querySelector('input[type="radio"]');
      cards[j].classList.toggle('is-selected', !!input && input.checked);
    }
  }

  var radios = document.querySelectorAll('input[name="result_type"]');
  for (var r = 0; r < radios.length; r++) {
    radios[r].addEventListener('change', applyType);
  }
  if (radios.length) {
    applyType();
  }

  /* ---- column mapping rows ---- */

  function applyMappingRow(select) {
    var row = select.closest ? select.closest('tr') : null;
    if (!row) {
      return;
    }
    var subject = row.querySelector('.map-table__subject');
    var max = row.querySelector('.map-table__max');
    var value = select.value;
    var isSubject = value === 'subject_secured' || value === 'subject_max' || value === 'subject_grade';

    if (subject) {
      subject.style.display = isSubject ? '' : 'none';
    }
    if (max) {
      max.style.display = value === 'subject_secured' ? '' : 'none';
    }
  }

  var selects = document.querySelectorAll('.map-table__select');
  for (var s = 0; s < selects.length; s++) {
    (function (select) {
      select.addEventListener('change', function () { applyMappingRow(select); });
      applyMappingRow(select);
    })(selects[s]);
  }
})();
