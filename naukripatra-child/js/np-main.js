/**
 * NaukriPatra — Frontend JS (v3.0)
 * ---------------------------------------------------------------
 * Vanilla JS only. No jQuery, no framework, no build step.
 * Loaded with `defer` so it is never render-blocking.
 *
 * Contents:
 *   1. Dark mode toggle (re-skinned, same localStorage key as v2.x)
 *   2. Sticky-header height published as --np-header-h (drives the
 *      sticky sidebar offset, which is what broke in v2.8)
 *   3. Mobile slide-out drawer: focus trap, Escape, overlay click,
 *      prefers-reduced-motion aware
 *   4. Header search toggle
 *   5. "Select your state" jump menu
 *   6. Live filter over the job list
 *   7. Copy-link buttons
 *   8. Back to top + reading progress
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia &&
		window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* =========================================================
	 * 1. DARK MODE
	 * ======================================================= */
	var KEY = 'np-theme';

	var SUN  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"/></svg>';
	var MOON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5z"/></svg>';

	function applyTheme(t) {
		var dark = (t === 'dark');
		document.documentElement.classList.toggle('np-dark', dark);
		var btn = document.getElementById('npDark');
		if (btn) {
			btn.innerHTML = dark ? SUN : MOON;
			btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
		}
	}

	function storedTheme() {
		try { return localStorage.getItem(KEY) || 'light'; } catch (e) { return 'light'; }
	}

	// Run before paint so there is no light flash for dark-mode visitors.
	applyTheme(storedTheme());

	/* =========================================================
	 * 2. STICKY HEADER HEIGHT -> --np-header-h
	 * =========================================================
	 * The v2.8 sticky sidebar used a hard-coded top:32px with no
	 * max-height, so it detached on short sidebars. The CSS now uses
	 * this measured value for both the offset and the max-height.
	 */
	function measureHeader() {
		var h = 0;
		var header = document.querySelector('.site-header');
		var navbar = document.getElementById('npNavbar');
		if (header && getComputedStyle(header).position === 'sticky') h += header.offsetHeight;
		if (navbar && getComputedStyle(navbar).position === 'sticky') h += navbar.offsetHeight;
		if (!h && navbar) h = navbar.offsetHeight;
		document.documentElement.style.setProperty('--np-header-h', (h || 64) + 'px');
	}

	/* =========================================================
	 * 2B. STICKY GUARD
	 * =========================================================
	 * position:sticky is silently cancelled when any ancestor is a
	 * scroll container. `overflow-x:hidden` on html or body does
	 * exactly that: it forces overflow-y to `auto`. A leftover copy of
	 * the old stylesheet — or a plugin setting it inline — therefore
	 * breaks the single-post sidebar even though the CSS here is
	 * correct.
	 *
	 * An inline style beats every stylesheet, so this repairs it at
	 * runtime. `clip` clips identically but creates no scroll
	 * container. Browsers without `clip` are left alone rather than
	 * having their horizontal-overflow guard removed.
	 */
	function guardSticky() {
		if (!(window.CSS && CSS.supports && CSS.supports('overflow', 'clip'))) return;

		[document.documentElement, document.body].forEach(function (el) {
			var ox = getComputedStyle(el).overflowX;
			if (ox === 'hidden' || ox === 'auto' || ox === 'scroll') {
				el.style.setProperty('overflow-x', 'clip', 'important');
			}
		});
	}

	document.addEventListener('DOMContentLoaded', function () {

		guardSticky();
		measureHeader();
		window.addEventListener('resize', debounce(measureHeader, 150), { passive: true });

		/* ---------- Dark toggle button ---------- */
		var host = document.querySelector('.np-navbar-inner .np-nav-actions') ||
			document.querySelector('.site-header .inside-header') || document.body;
		var btn = document.createElement('button');
		btn.id = 'npDark';
		btn.type = 'button';
		btn.className = 'np-iconbtn np-dark-toggle';
		btn.setAttribute('aria-label', 'Toggle dark mode');
		host.insertBefore(btn, host.firstChild);
		applyTheme(storedTheme());
		btn.addEventListener('click', function () {
			var next = document.documentElement.classList.contains('np-dark') ? 'light' : 'dark';
			try { localStorage.setItem(KEY, next); } catch (e) {}
			applyTheme(next);
		});

		/* =========================================================
		 * 3. MOBILE DRAWER
		 * ======================================================= */
		var drawer  = document.getElementById('npDrawer');
		var overlay = document.getElementById('npOverlay');
		var burger  = document.getElementById('npBurger');
		var closeBt = document.getElementById('npDrawerClose');
		var lastFocus = null;

		function focusables() {
			if (!drawer) return [];
			return Array.prototype.slice.call(
				drawer.querySelectorAll('a[href], button:not([disabled])')
			).filter(function (el) { return el.offsetParent !== null; });
		}

		function openDrawer() {
			if (!drawer) return;
			lastFocus = document.activeElement;
			drawer.hidden = false;
			if (overlay) overlay.hidden = false;
			// Next frame so the transition actually runs.
			requestAnimationFrame(function () {
				document.body.classList.add('np-drawer-open');
				if (reduceMotion) drawer.classList.add('np-no-anim');
			});
			if (burger) burger.setAttribute('aria-expanded', 'true');
			var f = focusables();
			if (f.length) f[0].focus();
			document.addEventListener('keydown', onKeydown);
		}

		function closeDrawer() {
			if (!drawer) return;
			document.body.classList.remove('np-drawer-open');
			if (burger) burger.setAttribute('aria-expanded', 'false');
			document.removeEventListener('keydown', onKeydown);

			var finish = function () {
				drawer.hidden = true;
				if (overlay) overlay.hidden = true;
			};
			if (reduceMotion) { finish(); } else { setTimeout(finish, 260); }

			if (lastFocus && lastFocus.focus) lastFocus.focus();
		}

		function onKeydown(e) {
			if (e.key === 'Escape') { closeDrawer(); return; }
			if (e.key !== 'Tab') return;

			// Focus trap.
			var f = focusables();
			if (!f.length) return;
			var first = f[0], last = f[f.length - 1];
			if (e.shiftKey && document.activeElement === first) {
				e.preventDefault(); last.focus();
			} else if (!e.shiftKey && document.activeElement === last) {
				e.preventDefault(); first.focus();
			}
		}

		if (burger)  burger.addEventListener('click', openDrawer);
		if (closeBt) closeBt.addEventListener('click', closeDrawer);
		if (overlay) overlay.addEventListener('click', closeDrawer);

		/* =========================================================
		 * 4. HEADER SEARCH TOGGLE
		 * ======================================================= */
		var stog = document.getElementById('npSearchToggle');
		var sbox = document.getElementById('npNavSearch');
		if (stog && sbox) {
			stog.addEventListener('click', function () {
				var open = sbox.hidden;
				sbox.hidden = !open;
				stog.setAttribute('aria-expanded', open ? 'true' : 'false');
				if (open) {
					var input = sbox.querySelector('input[type="search"]');
					if (input) input.focus();
				}
			});
		}

		/* ---------- Filter bar: never submit empty fields ---------- */
		Array.prototype.forEach.call(document.querySelectorAll('[data-np-filterbar]'), function (form) {
			form.addEventListener('submit', function () {
				Array.prototype.forEach.call(form.elements, function (el) {
					if (el.name && !el.value) el.disabled = true;
				});
			});
		});

		/* =========================================================
		 * 5. STATE JUMP MENU
		 * ======================================================= */
		var jump = document.getElementById('npStateJump');
		if (jump) {
			jump.addEventListener('change', function () {
				if (jump.value) window.location.href = jump.value;
			});
		}

		/* =========================================================
		 * 6. LIVE FILTER OVER THE JOB LIST
		 * ======================================================= */
		var list = document.querySelector('.np-joblist');
		if (list && !document.querySelector('.np-filterbar')) {
			var box = document.createElement('div');
			box.className = 'np-livefilter';
			box.innerHTML =
				'<input type="search" id="npFilter" placeholder="Filter this list by post, state or qualification" aria-label="Filter this list">' +
				'<span class="np-livefilter-count" id="npFilterCount" aria-live="polite"></span>';
			list.parentNode.insertBefore(box, list);

			var input = document.getElementById('npFilter');
			var count = document.getElementById('npFilterCount');
			var rows  = list.querySelectorAll('.np-job');

			input.addEventListener('input', debounce(function () {
				var q = input.value.trim().toLowerCase();
				var shown = 0;
				Array.prototype.forEach.call(rows, function (r) {
					var hit = !q || r.textContent.toLowerCase().indexOf(q) !== -1;
					r.style.display = hit ? '' : 'none';
					if (hit) shown++;
				});
				count.textContent = q ? shown + ' of ' + rows.length + ' shown' : '';
			}, 120));
		}

		/* =========================================================
		 * 6B. LIVE VIEW COUNTER
		 * =========================================================
		 * Counts one view over REST (works even when a caching plugin
		 * is serving this page, which the old PHP counter did not),
		 * then refreshes the number every 30s so it keeps ticking up
		 * while the page is open.
		 */
		var engage = document.querySelector('[data-np-post]');
		if (engage && window.npData && npData.viewsRoot) {
			var postId = engage.getAttribute('data-np-post');
			var url    = npData.viewsRoot + encodeURIComponent(postId);
			var out    = engage.querySelector('[data-np-views]');

			var show = function (n) {
				if (!out || typeof n !== 'number') return;
				var old = out.textContent;
				var next = n.toLocaleString();
				if (old === next) return;
				out.textContent = next;
				out.classList.remove('np-views-bump');
				void out.offsetWidth;          // restart the animation
				out.classList.add('np-views-bump');
			};

			var read = function () {
				fetch(url, { credentials: 'same-origin' })
					.then(function (r) { return r.ok ? r.json() : null; })
					.then(function (d) { if (d) show(d.views); })
					.catch(function () {});
			};

			// One count per browser tab; the server also holds an
			// IP guard, so a refresh cannot inflate the number.
			var seen = false;
			try { seen = sessionStorage.getItem('np-viewed-' + postId) === '1'; } catch (e) {}

			if (seen) {
				read();
			} else {
				fetch(url, { method: 'POST', credentials: 'same-origin' })
					.then(function (r) { return r.ok ? r.json() : null; })
					.then(function (d) {
						if (d) show(d.views);
						try { sessionStorage.setItem('np-viewed-' + postId, '1'); } catch (e) {}
					})
					.catch(read);
			}

			// Keep it live, but pause while the tab is in the background.
			setInterval(function () {
				if (!document.hidden) read();
			}, 30000);
		}

		/* =========================================================
		 * 7. COPY LINK
		 * ======================================================= */
		document.addEventListener('click', function (e) {
			var t = e.target.closest ? e.target.closest('[data-np-copy]') : null;
			if (!t) return;
			var url = t.getAttribute('data-np-copy');
			// The button holds an icon plus a <span> label, so only the
			// label is swapped — replacing textContent would wipe the icon.
			var label = t.querySelector('span') || t;
			var done = function () {
				var old = label.textContent;
				label.textContent = 'Copied';
				t.classList.add('np-sh-done');
				setTimeout(function () {
					label.textContent = old;
					t.classList.remove('np-sh-done');
				}, 1800);
			};
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url).then(done, function () {});
			} else {
				var ta = document.createElement('textarea');
				ta.value = url;
				ta.setAttribute('readonly', '');
				ta.style.position = 'fixed';
				ta.style.opacity = '0';
				document.body.appendChild(ta);
				ta.select();
				try { document.execCommand('copy'); done(); } catch (err) {}
				document.body.removeChild(ta);
			}
		});

		/* =========================================================
		 * 8. BACK TO TOP + READING PROGRESS
		 * ======================================================= */
		var top = document.getElementById('npTop');
		if (top) {
			window.addEventListener('scroll', function () {
				top.classList.toggle('np-top-show', window.scrollY > 500);
			}, { passive: true });
			top.addEventListener('click', function () {
				window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
			});
		}

		var bar = document.getElementById('npProgress');
		if (bar) {
			window.addEventListener('scroll', function () {
				var h = document.documentElement;
				var max = h.scrollHeight - h.clientHeight;
				bar.style.width = (max > 0 ? (h.scrollTop / max) * 100 : 0) + '%';
			}, { passive: true });
		}

		/* Sticky mobile apply bar: hide it once the footer is reached so
		   it never covers the footer links. */
		var applybar = document.querySelector('.np-applybar');
		if (applybar && 'IntersectionObserver' in window) {
			var footer = document.querySelector('.np-footer');
			if (footer) {
				new IntersectionObserver(function (entries) {
					applybar.classList.toggle('np-applybar-hide', entries[0].isIntersecting);
				}, { rootMargin: '0px' }).observe(footer);
			}
		}
	});

	/* ---------- helper ---------- */
	function debounce(fn, wait) {
		var t;
		return function () {
			var ctx = this, args = arguments;
			clearTimeout(t);
			t = setTimeout(function () { fn.apply(ctx, args); }, wait);
		};
	}
})();
