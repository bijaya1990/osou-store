/* KATAPALI +3 COLLEGE — shared site behaviour */
(function () {
  'use strict';
  var Store = KC.Store, IMG = KC.IMG;

  var MENU = [
    { label: 'Home', href: 'index.html' },
    { label: 'About Us', href: 'about.html', sub: [
      ['About College', 'about.html#about-college'], ['Vision & Mission', 'about.html#vision-mission'],
      ['Governing Body / College Committee', 'about.html#governing-body'], ["Principal's Desk", 'about.html#principal-desk']
    ]},
    { label: 'Academics', href: 'academics.html', sub: [
      ['Departments', 'academics.html#departments'], ['Courses Offered', 'academics.html#courses'],
      ['Syllabus', 'academics.html#syllabus'], ['Academic Calendar', 'academics.html#academic-calendar']
    ]},
    { label: 'Admissions', href: 'admissions.html', sub: [
      ['Admission Process', 'admissions.html#admission-process'], ['Eligibility Criteria', 'admissions.html#eligibility'],
      ['Fee Structure', 'admissions.html#fee-structure'], ['Online Admission Form', 'admissions.html#apply']
    ]},
    { label: 'Faculty', href: 'faculty.html' },
    { label: 'Notices', href: 'notices.html' },
    { label: 'Recruitment', href: 'recruitment.html' },
    { label: 'Tenders', href: 'tenders.html' },
    { label: 'Examination', href: 'examination.html', sub: [
      ['Exam Routine', 'examination.html#exam-routine'], ['Results', 'examination.html#results'],
      ['Rules & Regulations', 'examination.html#exam-rules']
    ]},
    { label: 'Student Corner', href: 'student-corner.html', sub: [
      ['Scholarships', 'student-corner.html#scholarships'], ["Student Union", 'student-corner.html#student-union'],
      ['Sports & NCC/NSS', 'student-corner.html#sports-nss'], ['Library', 'student-corner.html#library']
    ]},
    { label: 'Gallery', href: 'gallery.html', sub: [
      ['Photo Gallery', 'gallery.html'], ['Video Gallery', 'gallery.html#videos']
    ]},
    { label: 'Alumni', href: 'alumni.html', sub: [
      ['Alumni Association', 'alumni.html#alumni-association'], ['Notable Alumni', 'alumni.html#notable-alumni']
    ]},
    { label: 'Downloads', href: 'downloads.html' },
    { label: 'Contact Us', href: 'contact.html' }
  ];

  function esc(s) { return (s || '').toString().replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function fmtDate(d) { if (!d) return ''; var dt = new Date(d + 'T00:00:00'); if (isNaN(dt)) return d; return dt.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }); }
  function truncate(s, n) { s = (s || '').replace(/<[^>]+>/g, ''); return s.length > n ? s.slice(0, n) + '…' : s; }
  window.KCU = { esc: esc, fmtDate: fmtDate, truncate: truncate };

  function applyTheme() {
    var t = Store.get('theme');
    var r = document.documentElement.style;
    r.setProperty('--primary', t.primary); r.setProperty('--secondary', t.secondary);
    r.setProperty('--accent', t.accent); r.setProperty('--dark', t.dark);
    r.setProperty('--font-head', "'" + t.headingFont + "',sans-serif");
    r.setProperty('--font-body', "'" + t.bodyFont + "',sans-serif");
    document.title = document.title.replace('KATAPALI +3 COLLEGE, KATAPALI', Store.get('site').name);
  }

  function renderTopbar() {
    var s = Store.get('site');
    var el = document.getElementById('kc-topbar');
    if (!el) return;
    el.innerHTML =
      '<div class="container">' +
      '<div class="tb-left">' +
      '<span class="tb-item"><i class="fa-solid fa-phone"></i>' + esc(s.phone) + '</span>' +
      '<span class="tb-item"><i class="fa-solid fa-envelope"></i>' + esc(s.email) + '</span>' +
      '<span class="tb-item"><i class="fa-solid fa-location-dot"></i>' + esc(s.address) + '</span>' +
      '</div>' +
      '<div class="tb-social">' +
      '<a href="' + s.social.facebook + '" target="_blank" rel="noopener"><i class="fa-brands fa-facebook"></i></a>' +
      '<a href="' + s.social.twitter + '" target="_blank" rel="noopener"><i class="fa-brands fa-twitter"></i></a>' +
      '<a href="' + s.social.youtube + '" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i></a>' +
      '<a href="' + s.social.instagram + '" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a>' +
      '</div></div>';
  }

  function renderHeader() {
    var s = Store.get('site');
    var el = document.getElementById('kc-header');
    if (!el) return;
    var cur = (document.body.getAttribute('data-page') || '').toLowerCase();
    var items = MENU.map(function (m) {
      var page = m.href.split('#')[0].replace('.html', '');
      var active = page === cur ? ' active' : '';
      var sub = '';
      if (m.sub) {
        sub = '<ul class="submenu">' + m.sub.map(function (s2) { return '<li><a href="' + s2[1] + '">' + s2[0] + '</a></li>'; }).join('') + '</ul>';
      }
      return '<li class="' + (m.sub ? 'has-sub' : '') + active + '"><a href="' + m.href + '">' + m.label + (m.sub ? ' <i class="fa-solid fa-chevron-down" style="font-size:.6em;"></i>' : '') + '</a>' + sub + '</li>';
    }).join('');
    el.innerHTML =
      '<div class="container header-inner">' +
      '<a href="index.html" class="brand"><img src="' + s.logo + '" alt="' + esc(s.name) + ' logo">' +
      '<span><span class="b-name">' + esc(s.name) + '</span><br><span class="b-sub">Est. ' + esc(s.established) + ' &bull; ' + esc(s.affiliation.split('|')[0]) + '</span></span></a>' +
      '<nav class="main-nav" id="kc-nav"><button class="nav-close" id="kc-nav-close"><i class="fa-solid fa-xmark"></i></button><ul>' + items + '</ul></nav>' +
      '<div class="header-actions">' +
      '<a href="../admin-panel/index.html" class="admin-btn"><i class="fa-solid fa-user-shield"></i> Admin Login</a>' +
      '<button class="menu-toggle" id="kc-menu-toggle"><i class="fa-solid fa-bars"></i></button>' +
      '</div></div><div class="nav-overlay" id="kc-nav-overlay"></div>';

    var nav = document.getElementById('kc-nav'), overlay = document.getElementById('kc-nav-overlay');
    document.getElementById('kc-menu-toggle').onclick = function () { nav.classList.add('open'); overlay.classList.add('open'); };
    document.getElementById('kc-nav-close').onclick = function () { nav.classList.remove('open'); overlay.classList.remove('open'); };
    overlay.onclick = function () { nav.classList.remove('open'); overlay.classList.remove('open'); };
    if (window.innerWidth <= 1080) {
      nav.querySelectorAll('li.has-sub > a').forEach(function (a) {
        a.addEventListener('click', function (e) { e.preventDefault(); a.parentElement.classList.toggle('open'); });
      });
    }
  }

  function renderFooter() {
    var s = Store.get('site');
    var el = document.getElementById('kc-footer');
    if (!el) return;
    var links1 = MENU.slice(0, 7).map(function (m) { return '<li><a href="' + m.href + '">' + m.label + '</a></li>'; }).join('');
    var links2 = MENU.slice(7).map(function (m) { return '<li><a href="' + m.href + '">' + m.label + '</a></li>'; }).join('');
    el.innerHTML =
      '<div class="container"><div class="footer-grid">' +
      '<div><div class="footer-brand"><img src="' + s.logo + '" alt="logo"><span>' + esc(s.name) + '</span></div>' +
      '<p>' + esc(s.about) + '</p>' +
      '<div class="footer-social">' +
      '<a href="' + s.social.facebook + '" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a>' +
      '<a href="' + s.social.twitter + '" target="_blank" rel="noopener"><i class="fa-brands fa-twitter"></i></a>' +
      '<a href="' + s.social.youtube + '" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i></a>' +
      '<a href="' + s.social.instagram + '" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a>' +
      '</div></div>' +
      '<div><h4>Quick Links</h4><ul class="footer-links">' + links1 + '</ul></div>' +
      '<div><h4>&nbsp;</h4><ul class="footer-links">' + links2 + '</ul></div>' +
      '<div><h4>Contact Info</h4><ul class="footer-contact">' +
      '<li><i class="fa-solid fa-location-dot"></i><span>' + esc(s.name) + ', ' + esc(s.address) + ' - ' + esc(s.pin) + '</span></li>' +
      '<li><i class="fa-solid fa-phone"></i><span>' + esc(s.phone) + ' / ' + esc(s.altPhone) + '</span></li>' +
      '<li><i class="fa-solid fa-envelope"></i><span>' + esc(s.email) + '</span></li>' +
      '<li><i class="fa-solid fa-clock"></i><span>' + esc(s.officeHours) + '</span></li>' +
      '</ul></div>' +
      '</div></div>' +
      '<div class="footer-bottom">&copy; <span id="kc-year"></span> ' + esc(s.name) + '. All Rights Reserved. | Designed as a demo college website template.</div>';
    document.getElementById('kc-year').textContent = new Date().getFullYear();
  }

  /* ---------------- Hero slider (home page) ---------------- */
  function initHero() {
    var wrap = document.getElementById('kc-hero'); if (!wrap) return;
    var slides = Store.get('hero');
    if (!slides.length) return;
    var idx = 0;
    wrap.innerHTML = slides.map(function (s, i) {
      return '<div class="hero-slide' + (i === 0 ? ' active' : '') + '" style="background-image:url(\'' + s.image + '\')">' +
        '<div class="hero-content"><div class="inner">' +
        '<h1>' + esc(s.title) + '</h1><p>' + esc(s.subtitle) + '</p>' +
        '<div class="hero-btns"><a href="' + s.link1 + '" class="btn btn-accent">' + esc(s.btn1) + '</a>' +
        '<a href="' + s.link2 + '" class="btn btn-outline">' + esc(s.btn2) + '</a></div>' +
        '</div></div></div>';
    }).join('') +
      '<button class="hero-arrow prev"><i class="fa-solid fa-chevron-left"></i></button>' +
      '<button class="hero-arrow next"><i class="fa-solid fa-chevron-right"></i></button>' +
      '<div class="hero-dots">' + slides.map(function (_, i) { return '<span class="' + (i === 0 ? 'active' : '') + '" data-i="' + i + '"></span>'; }).join('') + '</div>';
    var slideEls = wrap.querySelectorAll('.hero-slide'), dots = wrap.querySelectorAll('.hero-dots span');
    function show(i) { idx = (i + slides.length) % slides.length; slideEls.forEach(function (s, j) { s.classList.toggle('active', j === idx); }); dots.forEach(function (d, j) { d.classList.toggle('active', j === idx); }); }
    wrap.querySelector('.prev').onclick = function () { show(idx - 1); };
    wrap.querySelector('.next').onclick = function () { show(idx + 1); };
    dots.forEach(function (d) { d.onclick = function () { show(+d.dataset.i); }; });
    setInterval(function () { show(idx + 1); }, 5500);
  }

  function initPrincipal() {
    var el = document.getElementById('kc-principal'); if (!el) return;
    var p = Store.get('principal');
    el.innerHTML =
      '<div class="principal-photo"><img src="' + p.image + '" alt="' + esc(p.name) + '"></div>' +
      '<div class="principal-info"><h3>' + esc(p.name) + '</h3>' +
      '<div class="desig">' + esc(p.designation) + '</div>' +
      '<div class="qual">' + esc(p.qualification) + '</div>' +
      '<p>' + esc(p.message) + '</p></div>';
  }

  function initFacultySlider() {
    var track = document.getElementById('kc-faculty-track'); if (!track) return;
    var list = Store.get('faculty').filter(function (f) { return f.onSlider; }).sort(function (a, b) { return a.order - b.order; });
    if (!list.length) list = Store.get('faculty').slice(0, 7);
    var doubled = list.concat(list);
    track.innerHTML = doubled.map(function (f) {
      return '<div class="faculty-card"><img src="' + f.image + '" alt="' + esc(f.name) + '">' +
        '<h4>' + esc(f.name) + '</h4><div class="desig">' + esc(f.designation) + '</div></div>';
    }).join('');
    var pos = 0, cardW = 242, dir = 1, timer;
    function step() {
      pos += cardW;
      if (pos >= cardW * list.length) pos = 0;
      track.scrollTo({ left: pos, behavior: 'smooth' });
    }
    timer = setInterval(step, 2600);
    var prev = document.getElementById('kc-faculty-prev'), next = document.getElementById('kc-faculty-next');
    if (next) next.onclick = function () { clearInterval(timer); step(); timer = setInterval(step, 2600); };
    if (prev) prev.onclick = function () { clearInterval(timer); pos -= cardW; if (pos < 0) pos = cardW * (list.length - 1); track.scrollTo({ left: pos, behavior: 'smooth' }); timer = setInterval(step, 2600); };
  }

  function animateCounter(el, target) {
    var cur = 0, step = Math.max(1, Math.ceil(target / 60));
    var t = setInterval(function () { cur += step; if (cur >= target) { cur = target; clearInterval(t); } el.textContent = cur.toLocaleString('en-IN'); }, 25);
  }
  function initStats() {
    var el = document.getElementById('kc-stats'); if (!el) return;
    var stats = Store.get('stats');
    el.innerHTML = stats.map(function (s) {
      return '<div class="stat-card"><i class="fa-solid ' + s.icon + '"></i><div class="num" data-target="' + s.value + '">0</div><div class="lbl">' + esc(s.label) + '</div></div>';
    }).join('');
    var nums = el.querySelectorAll('.num');
    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { animateCounter(e.target, +e.target.dataset.target); obs.unobserve(e.target); } });
    }, { threshold: .4 });
    nums.forEach(function (n) { obs.observe(n); });
  }

  function initNoticesPreview() {
    var el = document.getElementById('kc-notices-preview'); if (!el) return;
    var list = Store.get('notices').slice(0, 3);
    el.innerHTML = list.map(function (n) {
      return '<div class="card"><div class="card-body">' +
        '<span class="tag tag-blue">' + esc(n.category) + '</span>' +
        '<h4 style="margin-top:10px;">' + esc(n.title) + (n.isNew ? '<span class="badge-new">NEW</span>' : '') + '</h4>' +
        '<div class="card-date"><i class="fa-regular fa-calendar"></i>' + fmtDate(n.date) + '</div>' +
        '<p>' + esc(truncate(n.summary, 110)) + '</p>' +
        '<a href="notices.html?id=' + n.id + '" class="btn btn-line btn-sm">Read More <i class="fa-solid fa-arrow-right"></i></a>' +
        '</div></div>';
    }).join('') || '<div class="empty-msg">No notices yet.</div>';
  }

  function initRecruitmentPreview() {
    var el = document.getElementById('kc-recruitment-preview'); if (!el) return;
    var list = Store.get('recruitment').filter(function (r) { return r.status === 'Open'; }).slice(0, 2);
    if (!list.length) list = Store.get('recruitment').slice(0, 2);
    el.innerHTML = list.map(function (r) {
      return '<div class="card"><div class="card-body">' +
        '<span class="tag ' + (r.status === 'Open' ? 'tag-green' : 'tag-red') + '">' + esc(r.status) + '</span>' +
        '<h4 style="margin-top:10px;">' + esc(r.title) + '</h4>' +
        '<div class="card-date"><i class="fa-regular fa-calendar"></i>Last Date: ' + fmtDate(r.lastDate) + '</div>' +
        '<p>' + esc(r.qualification).slice(0, 100) + '…</p>' +
        '<a href="recruitment.html?id=' + r.id + '" class="btn btn-line btn-sm">View Details <i class="fa-solid fa-arrow-right"></i></a>' +
        '</div></div>';
    }).join('') || '<div class="empty-msg">No openings currently.</div>';
  }

  function initTendersPreview() {
    var el = document.getElementById('kc-tenders-preview'); if (!el) return;
    var list = Store.get('tenders').slice(0, 2);
    el.innerHTML = list.map(function (t) {
      return '<div class="card"><div class="card-body">' +
        '<span class="tag ' + (t.status === 'Open' ? 'tag-green' : 'tag-red') + '">' + esc(t.status) + '</span>' +
        '<h4 style="margin-top:10px;">' + esc(t.tenderId) + '</h4>' +
        '<p>' + esc(truncate(t.title, 90)) + '</p>' +
        '<div class="card-date"><i class="fa-regular fa-calendar"></i>Last Date: ' + fmtDate(t.lastDate) + '</div>' +
        '<a href="tenders.html?id=' + t.id + '" class="btn btn-line btn-sm">View Details <i class="fa-solid fa-arrow-right"></i></a>' +
        '</div></div>';
    }).join('') || '<div class="empty-msg">No tenders currently.</div>';
  }

  function initGalleryPreview() {
    var el = document.getElementById('kc-gallery-preview'); if (!el) return;
    var list = Store.get('gallery').filter(function (g) { return g.featured; }).slice(0, 8);
    el.innerHTML = list.map(function (g) {
      return '<div class="gallery-item"><img src="' + g.image + '" alt="' + esc(g.title) + '" loading="lazy"><div class="gi-cap">' + esc(g.title) + '</div></div>';
    }).join('');
  }

  function initMap() {
    var el = document.getElementById('kc-map'); var info = document.getElementById('kc-map-info');
    if (!el) return;
    var m = Store.get('map'), s = Store.get('site');
    el.innerHTML = m.embed;
    if (info) {
      info.innerHTML =
        '<div class="mi-row"><i class="fa-solid fa-location-dot"></i><div><strong>' + esc(s.name) + '</strong><br>' + esc(s.address) + ' - ' + esc(s.pin) + '</div></div>' +
        '<div class="mi-row"><i class="fa-solid fa-phone"></i><div>' + esc(s.phone) + '</div></div>' +
        '<div class="mi-row"><i class="fa-solid fa-envelope"></i><div>' + esc(s.email) + '</div></div>' +
        '<div class="mi-row"><i class="fa-solid fa-circle-info"></i><div>' + esc(m.note) + '</div></div>';
    }
  }

  function initBackTop() {
    var btn = document.createElement('div'); btn.className = 'back-top'; btn.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
    document.body.appendChild(btn);
    window.addEventListener('scroll', function () { btn.classList.toggle('show', window.scrollY > 400); });
    btn.onclick = function () { window.scrollTo({ top: 0, behavior: 'smooth' }); };
  }

  function initFadeIns() {
    var els = document.querySelectorAll('.fade-in');
    if (!els.length) return;
    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('show'); obs.unobserve(e.target); } });
    }, { threshold: .12 });
    els.forEach(function (e) { obs.observe(e); });
  }

  function injectPageContent(id, targetSel) {
    var el = document.querySelector(targetSel); if (!el) return;
    var pg = Store.page(id);
    el.innerHTML = pg.html || '<p class="note">Content coming soon.</p>';
  }
  window.KCU.injectPageContent = injectPageContent;

  document.addEventListener('DOMContentLoaded', function () {
    applyTheme();
    renderTopbar(); renderHeader(); renderFooter();
    initHero(); initPrincipal(); initFacultySlider(); initStats();
    initNoticesPreview(); initRecruitmentPreview(); initTendersPreview();
    initGalleryPreview(); initMap();
    initBackTop(); initFadeIns();
    if (window.KCPageInit) window.KCPageInit();
  });
})();
