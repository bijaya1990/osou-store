(function () {
	'use strict';
	document.addEventListener('DOMContentLoaded', function () {

		/* Student search / verification */
		var input = document.getElementById('ksr-search-input');
		var btn = document.getElementById('ksr-search-btn');
		var out = document.getElementById('ksr-search-results');
		if (input && btn && out && typeof KSR_DATA !== 'undefined') {
			function doSearch() {
				var q = input.value.trim();
				if (q.length < 2) { out.innerHTML = '<p class="ksr-hint">Type at least 2 characters.</p>'; return; }
				out.innerHTML = '<p class="ksr-hint">Searching...</p>';
				var body = new URLSearchParams();
				body.set('action', 'ksr_search');
				body.set('nonce', KSR_DATA.nonce);
				body.set('q', q);
				fetch(KSR_DATA.ajaxUrl, { method: 'POST', body: body })
					.then(function (r) { return r.json(); })
					.then(function (res) {
						var rows = (res && res.success) ? res.data : [];
						if (!rows.length) { out.innerHTML = '<p class="ksr-hint">No matching student found.</p>'; return; }
						var html = '<table class="ksr-result-table"><thead><tr><th>Name</th><th>Roll No</th><th>Stream</th><th>Batch</th></tr></thead><tbody>';
						rows.forEach(function (s) {
							html += '<tr><td>' + escapeHtml(s.name) + '</td><td>' + escapeHtml(s.roll_no) + '</td><td>' + escapeHtml(s.stream) + '</td><td>' + escapeHtml(s.batch) + '</td></tr>';
						});
						html += '</tbody></table>';
						out.innerHTML = html;
					})
					.catch(function () { out.innerHTML = '<p class="ksr-hint">Search failed, please try again.</p>'; });
			}
			function escapeHtml(s) {
				var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML;
			}
			btn.addEventListener('click', doSearch);
			input.addEventListener('keydown', function (e) { if (e.key === 'Enter') doSearch(); });
		}

		/* Alumni directory batch toggles */
		document.querySelectorAll('.ksr-alumni-toggle').forEach(function (t) {
			t.addEventListener('click', function () {
				var list = t.nextElementSibling;
				var open = t.classList.toggle('open');
				list.hidden = !open;
			});
		});
	});
})();
