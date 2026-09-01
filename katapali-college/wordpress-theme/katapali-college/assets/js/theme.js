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

    /* hero slider */
    var heroWrap = document.getElementById('kc-hero');
    if (heroWrap) {
      var slides = heroWrap.querySelectorAll('.hero-slide'), dots = heroWrap.querySelectorAll('.hero-dot');
      var idx = 0, heroTimer;
      function showSlide(n) {
        slides[idx].classList.remove('active');
        if (dots.length) dots[idx].classList.remove('active');
        idx = (n + slides.length) % slides.length;
        slides[idx].classList.add('active');
        if (dots.length) dots[idx].classList.add('active');
      }
      function nextSlide() { showSlide(idx + 1); }
      if (slides.length > 1) {
        heroTimer = setInterval(nextSlide, 5000);
        var prevH = document.getElementById('kc-hero-prev'), nextH = document.getElementById('kc-hero-next');
        if (nextH) nextH.onclick = function () { clearInterval(heroTimer); nextSlide(); heroTimer = setInterval(nextSlide, 5000); };
        if (prevH) prevH.onclick = function () { clearInterval(heroTimer); showSlide(idx - 1); heroTimer = setInterval(nextSlide, 5000); };
        dots.forEach(function (d) {
          d.addEventListener('click', function () { clearInterval(heroTimer); showSlide(+d.dataset.slide); heroTimer = setInterval(nextSlide, 5000); });
        });
      }
    }

    /* faculty auto-scroll */
    var track = document.getElementById('kc-faculty-track');
    if (track) {
      var cards = track.querySelectorAll('.faculty-card'), setLen = cards.length / 2;
      var pos = 0, cardW = 242, timer;
      function step() { pos += cardW; if (pos >= cardW * setLen) pos = 0; track.scrollTo({ left: pos, behavior: 'smooth' }); }
      if (setLen > 0) timer = setInterval(step, 2600);
      var prevF = document.getElementById('kc-faculty-prev'), nextF = document.getElementById('kc-faculty-next');
      if (nextF) nextF.onclick = function () { clearInterval(timer); step(); timer = setInterval(step, 2600); };
      if (prevF) prevF.onclick = function () { clearInterval(timer); pos -= cardW; if (pos < 0) pos = cardW * (setLen - 1); track.scrollTo({ left: pos, behavior: 'smooth' }); timer = setInterval(step, 2600); };
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
