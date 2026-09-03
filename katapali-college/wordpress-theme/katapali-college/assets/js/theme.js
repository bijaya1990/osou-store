(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {

    /* mobile nav */
    var nav = document.getElementById('kc-nav'), overlay = document.getElementById('kc-nav-overlay');
    var toggle = document.getElementById('kc-menu-toggle'), close = document.getElementById('kc-nav-close');
    if (toggle && nav && overlay) {
      toggle.onclick = function () { nav.classList.add('open'); overlay.classList.add('open'); };
      if (close) close.onclick = function () { nav.classList.remove('open'); overlay.classList.remove('open'); };
      overlay.onclick = function () { nav.classList.remove('open'); overlay.classList.remove('open'); };
      if (window.innerWidth <= 1080) {
        nav.querySelectorAll('li.has-sub > a').forEach(function (a) {
          a.addEventListener('click', function (e) { e.preventDefault(); a.parentElement.classList.toggle('open'); });
        });
      }
    }

    /* hero slider - current slide exits to the right, next slide fades in.
       The welcome text/overlay is a one-time thing: visible for 5s after
       the very first slide appears on page load, then hidden for good -
       it does NOT come back on later slide changes, only on a fresh page
       load/refresh. */
    var heroWrap = document.getElementById('kc-hero');
    if (heroWrap) {
      var slides = heroWrap.querySelectorAll('.hero-slide'), dots = heroWrap.querySelectorAll('.hero-dot');
      var idx = 0, heroTimer;
      var HERO_INTERVAL = 8000, TEXT_VISIBLE_MS = 5000;

      function showSlide(n) {
        var old = slides[idx];
        old.classList.remove('active');
        old.classList.add('exiting');
        setTimeout(function () { old.classList.remove('exiting'); }, 700);
        if (dots.length) dots[idx].classList.remove('active');
        idx = (n + slides.length) % slides.length;
        slides[idx].classList.add('active');
        if (dots.length) dots[idx].classList.add('active');
      }
      function nextSlide() { showSlide(idx + 1); }

      /* one-time only: after 5s, hide the welcome text/overlay on every
         slide (not just the current one) so it stays gone through every
         later auto/manual slide change - only a fresh page load brings
         it back. */
      setTimeout(function () {
        slides.forEach(function (s) { s.classList.add('kc-text-hidden'); });
      }, TEXT_VISIBLE_MS);

      if (slides.length > 1) {
        heroTimer = setInterval(nextSlide, HERO_INTERVAL);
        var prevH = document.getElementById('kc-hero-prev'), nextH = document.getElementById('kc-hero-next');
        if (nextH) nextH.onclick = function () { clearInterval(heroTimer); nextSlide(); heroTimer = setInterval(nextSlide, HERO_INTERVAL); };
        if (prevH) prevH.onclick = function () { clearInterval(heroTimer); showSlide(idx - 1); heroTimer = setInterval(nextSlide, HERO_INTERVAL); };
        dots.forEach(function (d) {
          d.addEventListener('click', function () { clearInterval(heroTimer); showSlide(+d.dataset.slide); heroTimer = setInterval(nextSlide, HERO_INTERVAL); });
        });
      }
    }

    /* faculty auto-scroll */
    var track = document.getElementById('kc-faculty-track');
    if (track) {
      var cards = track.querySelectorAll('.faculty-card');
      /* Only a looped track (enough faculty to need auto-scroll) has its
         card set duplicated in the markup - setLen is the length of one
         set. A non-looped track shows every card once with no wrap. */
      var looped = track.dataset.looped === '1';
      var setLen = looped ? cards.length / 2 : cards.length;
      var pos = 0, cardW = 242, timer;
      function step() { pos += cardW; if (pos >= cardW * setLen) pos = 0; track.scrollTo({ left: pos, behavior: 'smooth' }); }
      if (looped && setLen > 0) timer = setInterval(step, 2600);
      var prevF = document.getElementById('kc-faculty-prev'), nextF = document.getElementById('kc-faculty-next');
      if (nextF) nextF.onclick = function () { clearInterval(timer); step(); if (looped) timer = setInterval(step, 2600); };
      if (prevF) prevF.onclick = function () { clearInterval(timer); pos -= cardW; if (pos < 0) pos = cardW * (setLen - 1); track.scrollTo({ left: pos, behavior: 'smooth' }); if (looped) timer = setInterval(step, 2600); };
    }

    /* stat counters */
    var nums = document.querySelectorAll('#kc-stats .num');
    if (nums.length && 'IntersectionObserver' in window) {
      var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (!e.isIntersecting) return;
          var el = e.target, target = +el.dataset.target, cur = 0, s = Math.max(1, Math.ceil(target / 60));
          var t = setInterval(function () { cur += s; if (cur >= target) { cur = target; clearInterval(t); } el.textContent = cur.toLocaleString('en-IN'); }, 25);
          obs.unobserve(el);
        });
      }, { threshold: .4 });
      nums.forEach(function (n) { obs.observe(n); });
    }

    /* faculty department filter (archive page) */
    var facFilters = document.getElementById('fac-filters');
    if (facFilters) {
      facFilters.addEventListener('click', function (e) {
        var btn = e.target.closest('.gf-btn'); if (!btn) return;
        facFilters.querySelectorAll('.gf-btn').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        var dept = btn.dataset.dept;
        document.querySelectorAll('#fac-grid .faculty-card').forEach(function (c) {
          c.style.display = (dept === 'all' || c.dataset.dept === dept) ? '' : 'none';
        });
      });
    }

    /* gallery category filter + lightbox (archive page) */
    var galFilters = document.getElementById('gal-filters');
    if (galFilters) {
      galFilters.addEventListener('click', function (e) {
        var btn = e.target.closest('.gf-btn'); if (!btn) return;
        galFilters.querySelectorAll('.gf-btn').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        var cat = btn.dataset.cat;
        document.querySelectorAll('#gal-grid .gallery-item').forEach(function (g) {
          g.style.display = (cat === 'all' || g.dataset.cat === cat) ? '' : 'none';
        });
      });
    }
    var lightbox = document.getElementById('lightbox');
    if (lightbox) {
      document.querySelectorAll('.gallery-item').forEach(function (g) {
        g.addEventListener('click', function () {
          document.getElementById('lbImg').src = g.dataset.img;
          document.getElementById('lbCap').textContent = g.dataset.cap || '';
          lightbox.classList.add('open');
        });
      });
      var lbClose = document.getElementById('lbClose');
      if (lbClose) lbClose.onclick = function () { lightbox.classList.remove('open'); };
      lightbox.addEventListener('click', function (e) { if (e.target === lightbox) lightbox.classList.remove('open'); });
    }

    /* back to top */
    var backTop = document.getElementById('kc-back-top');
    if (backTop) {
      window.addEventListener('scroll', function () { backTop.classList.toggle('show', window.scrollY > 400); });
      backTop.onclick = function () { window.scrollTo({ top: 0, behavior: 'smooth' }); };
    }

    /* fade-ins */
    var fadeEls = document.querySelectorAll('.fade-in');
    if (fadeEls.length && 'IntersectionObserver' in window) {
      var fObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('show'); fObs.unobserve(e.target); } });
      }, { threshold: .12 });
      fadeEls.forEach(function (e) { fObs.observe(e); });
    }
  });
})();
