(function () {
	'use strict';
	function escapeHtml(s) {
		var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML;
	}

	document.addEventListener('DOMContentLoaded', function () {

		/* Session/batch dropdown "View List" widgets - used by both the
		   Students List and Alumni Directory pages. Several can exist on
		   one page, each identified by its data-target id prefix. */
		document.querySelectorAll('.ksr-batch-btn').forEach(function (btn) {
			var prefix = btn.dataset.target;
			var select = document.getElementById(prefix + '-select');
			var out = document.getElementById(prefix + '-results');
			if (!select || !out || typeof KSR_DATA === 'undefined') return;

			function loadBatch() {
				var batch = select.value;
				if (!batch) { out.innerHTML = '<p class="ksr-hint">Select a session first.</p>'; return; }
				out.innerHTML = '<p class="ksr-hint">Loading...</p>';
				var body = new URLSearchParams();
				body.set('action', 'ksr_batch_list');
				body.set('nonce', KSR_DATA.nonce);
				body.set('batch', batch);
				fetch(KSR_DATA.ajaxUrl, { method: 'POST', body: body })
					.then(function (r) { return r.json(); })
					.then(function (res) {
						var rows = (res && res.success) ? res.data : [];
						if (!rows.length) { out.innerHTML = '<p class="ksr-hint">No students found for this session.</p>'; return; }
						var html = '<table class="ksr-result-table"><thead><tr><th>#</th><th>Name</th><th>Roll No</th><th>Stream</th></tr></thead><tbody>';
						rows.forEach(function (s, i) {
							html += '<tr><td>' + (i + 1) + '</td><td>' + escapeHtml(s.name) + '</td><td>' + escapeHtml(s.roll_no) + '</td><td>' + escapeHtml(s.stream) + '</td></tr>';
						});
						html += '</tbody></table>';
						out.innerHTML = html;
					})
					.catch(function () { out.innerHTML = '<p class="ksr-hint">Could not load the list, please try again.</p>'; });
			}
			btn.addEventListener('click', loadBatch);
			select.addEventListener('change', loadBatch);
		});
	});
})();
