/* KATAPALI +3 COLLEGE — Admin Panel shared logic */
(function () {
  'use strict';
  var Store = KC.Store;
  var SESSION_KEY = 'kc_admin_session';

  function isLoggedIn() { return sessionStorage.getItem(SESSION_KEY) === '1'; }
  function isLoginPage() { return /(^|\/)index\.html$/.test(location.pathname) || /\/admin-panel\/?$/.test(location.pathname); }
  function requireAuth() {
    if (!isLoggedIn() && !isLoginPage()) {
      location.href = 'index.html';
    }
  }
  window.KCAdmin = window.KCAdmin || {};
  KCAdmin.login = function (email, pass) {
    var users = Store.get('users');
    var u = users.find(function (x) { return x.email.toLowerCase() === email.toLowerCase() && x.password === pass && x.status === 'Active'; });
    if (u) {
      sessionStorage.setItem(SESSION_KEY, '1');
      sessionStorage.setItem('kc_admin_name', u.name);
      sessionStorage.setItem('kc_admin_role', u.role);
      return true;
    }
    return false;
  };
  KCAdmin.logout = function () { sessionStorage.removeItem(SESSION_KEY); location.href = 'index.html'; };

  var NAV = [
    { group: 'Overview', items: [['dashboard.html', 'fa-gauge-high', 'Dashboard']] },
    { group: 'Content Modules', items: [
      ['notices.html', 'fa-bullhorn', 'Notices'],
      ['recruitment.html', 'fa-briefcase', 'Recruitment'],
      ['tenders.html', 'fa-file-contract', 'Tenders'],
      ['faculty.html', 'fa-chalkboard-user', 'Faculty'],
      ['gallery.html', 'fa-images', 'Gallery'],
      ['downloads.html', 'fa-download', 'Downloads'],
      ['messages.html', 'fa-envelope-open-text', 'Messages / Enquiries']
    ]},
    { group: 'Website Settings', items: [
      ['homepage.html', 'fa-house', 'Homepage Content'],
      ['pages.html', 'fa-file-lines', 'Menu Content Editor'],
      ['map.html', 'fa-map-location-dot', 'Google Map Settings'],
      ['college-info.html', 'fa-building-columns', 'College Info Settings'],
      ['theme.html', 'fa-palette', 'Theme Customizer'],
      ['users.html', 'fa-users-gear', 'User Management']
    ]}
  ];

  function renderShell(activeFile) {
    var sb = document.getElementById('kc-sidebar');
    var s = Store.get('site');
    if (sb) {
      var navHtml = NAV.map(function (g) {
        return '<div class="nav-group-title">' + g.group + '</div>' + g.items.map(function (it) {
          return '<a class="nav-link' + (it[0] === activeFile ? ' active' : '') + '" href="' + it[0] + '"><i class="fa-solid ' + it[1] + '"></i> ' + it[2] + '</a>';
        }).join('');
      }).join('');
      sb.innerHTML =
        '<div class="brand"><img src="' + s.logo + '" alt="logo"><span>' + s.shortName + '<br><small style="font-weight:400;color:#93a4d1;">Admin Panel</small></span></div>' +
        '<nav>' + navHtml + '</nav>';
    }
    var tb = document.getElementById('kc-topbar-admin');
    if (tb) {
      var name = sessionStorage.getItem('kc_admin_name') || 'Admin';
      var role = sessionStorage.getItem('kc_admin_role') || 'Administrator';
      tb.innerHTML =
        '<div style="display:flex;align-items:center;gap:14px;"><button class="mobile-toggle" id="kc-mtoggle"><i class="fa-solid fa-bars"></i></button><h1 id="kc-page-title">' + (document.body.getAttribute('data-title') || 'Dashboard') + '</h1></div>' +
        '<div class="right">' +
        '<a href="../frontend/index.html" target="_blank" class="view-site"><i class="fa-solid fa-globe"></i> View Site</a>' +
        '<div class="admin-user"><div class="av">' + name.charAt(0) + '</div><div><strong>' + name + '</strong><br><small style="color:var(--muted);">' + role + '</small></div></div>' +
        '<button class="logout-btn" id="kc-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>' +
        '</div>';
      document.getElementById('kc-logout').onclick = KCAdmin.logout;
      var mt = document.getElementById('kc-mtoggle');
      if (mt) mt.onclick = function () { document.getElementById('kc-sidebar').classList.toggle('open'); };
    }
  }

  function toast(msg, icon) {
    var t = document.getElementById('kc-toast');
    if (!t) { t = document.createElement('div'); t.id = 'kc-toast'; t.className = 'toast'; document.body.appendChild(t); }
    t.innerHTML = '<i class="fa-solid ' + (icon || 'fa-circle-check') + '"></i> ' + msg;
    t.classList.add('show');
    clearTimeout(t._timer);
    t._timer = setTimeout(function () { t.classList.remove('show'); }, 2600);
  }
  KCAdmin.toast = toast;

  function confirmDelete(msg, cb) {
    if (confirm(msg || 'Are you sure you want to delete this item? This action cannot be undone.')) cb();
  }
  KCAdmin.confirmDelete = confirmDelete;

  function fileToDataURL(input, cb) {
    var file = input.files && input.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) { cb(e.target.result); };
    reader.readAsDataURL(file);
  }
  KCAdmin.fileToDataURL = fileToDataURL;

  function esc(s) { return (s || '').toString().replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  KCAdmin.esc = esc;

  document.addEventListener('DOMContentLoaded', function () {
    requireAuth();
    if (!/login\.html$|index\.html$/.test(location.pathname) || document.getElementById('kc-sidebar')) {
      var file = location.pathname.split('/').pop();
      renderShell(file);
    }
  });
})();

/* ===================== Generic CRUD builder ===================== */
(function () {
  'use strict';
  var Store = KC.Store, esc = KCAdmin.esc;

  function fieldHTML(f, val) {
    val = val === undefined || val === null ? (f.default !== undefined ? f.default : '') : val;
    var req = f.required ? 'required' : '';
    var id = 'f_' + f.name;
    if (f.type === 'select') {
      var opts = f.options.map(function (o) { return '<option value="' + esc(o) + '"' + (o === val ? ' selected' : '') + '>' + esc(o) + '</option>'; }).join('');
      return '<div class="form-group"><label>' + f.label + '</label><select id="' + id + '" ' + req + '>' + opts + '</select></div>';
    }
    if (f.type === 'textarea' || f.type === 'richtext') {
      return '<div class="form-group"><label>' + f.label + '</label><textarea id="' + id + '" rows="' + (f.type === 'richtext' ? 7 : 3) + '" ' + req + '>' + esc(val) + '</textarea>' + (f.type === 'richtext' ? '<div class="small-note">Basic HTML tags allowed (e.g. &lt;p&gt;, &lt;strong&gt;, &lt;ul&gt;&lt;li&gt;).</div>' : '') + '</div>';
    }
    if (f.type === 'image') {
      var prev = val ? '<img src="' + val + '" class="img-preview" id="' + id + '_prev">' : '<div class="img-preview" id="' + id + '_prev"></div>';
      return '<div class="form-group"><label>' + f.label + '</label>' + prev +
        '<input type="hidden" id="' + id + '" value="' + esc(val) + '">' +
        '<div class="file-drop" id="' + id + '_drop"><i class="fa-solid fa-cloud-arrow-up"></i> Click to upload image (replaces demo image)</div>' +
        '<input type="file" accept="image/*" id="' + id + '_file" style="display:none;"></div>';
    }
    if (f.type === 'checkbox') {
      return '<div class="form-group"><label><input type="checkbox" id="' + id + '" ' + (val ? 'checked' : '') + ' style="width:auto;margin-right:8px;">' + f.label + '</label></div>';
    }
    return '<div class="form-group"><label>' + f.label + '</label><input type="' + (f.type || 'text') + '" id="' + id + '" value="' + esc(val) + '" ' + req + '></div>';
  }

  function bindImageField(f) {
    if (f.type !== 'image') return;
    var id = 'f_' + f.name;
    var drop = document.getElementById(id + '_drop'), input = document.getElementById(id + '_file');
    if (!drop) return;
    drop.onclick = function () { input.click(); };
    input.onchange = function () {
      KCAdmin.fileToDataURL(input, function (data) {
        document.getElementById(id).value = data;
        var prev = document.getElementById(id + '_prev');
        prev.outerHTML = '<img src="' + data + '" class="img-preview" id="' + id + '_prev">';
      });
    };
  }

  function readField(f) {
    var id = 'f_' + f.name;
    var el = document.getElementById(id);
    if (!el) return f.default;
    if (f.type === 'checkbox') return el.checked;
    if (f.type === 'number') return +el.value;
    return el.value;
  }

  KCAdmin.crud = function (cfg) {
    // cfg: {key, title, fields:[], columns:[{label, render(item)}], searchKeys:[], filterField, emptyIcon}
    var listWrap = document.getElementById(cfg.tableBodyId);
    var searchInput = document.getElementById(cfg.searchId);
    var filterSelect = document.getElementById(cfg.filterId);
    var addBtn = document.getElementById(cfg.addBtnId);
    var overlay = document.getElementById('kc-modal-overlay');
    var modal = document.getElementById('kc-modal');

    function all() { return Store.get(cfg.key); }

    function matchesSearch(item, q) {
      if (!q) return true;
      q = q.toLowerCase();
      return (cfg.searchKeys || []).some(function (k) { return (item[k] || '').toString().toLowerCase().indexOf(q) !== -1; });
    }
    function matchesFilter(item, val) {
      if (!val || val === 'All') return true;
      return item[cfg.filterField] === val;
    }

    function render() {
      var q = searchInput ? searchInput.value.trim() : '';
      var fv = filterSelect ? filterSelect.value : '';
      var items = all().filter(function (it) { return matchesSearch(it, q) && matchesFilter(it, fv); });
      if (!items.length) {
        listWrap.innerHTML = '<tr class="empty-row"><td colspan="' + (cfg.columns.length + 1) + '"><i class="fa-solid ' + (cfg.emptyIcon || 'fa-inbox') + '" style="font-size:1.6rem;display:block;margin-bottom:8px;color:#cbd5e1;"></i>No records found.</td></tr>';
        return;
      }
      listWrap.innerHTML = items.map(function (it) {
        var cols = cfg.columns.map(function (c) { return '<td>' + c.render(it) + '</td>'; }).join('');
        return '<tr><td class="actions">' +
          '<button class="act-btn act-edit" data-id="' + it.id + '" data-act="edit" title="Edit"><i class="fa-solid fa-pen"></i></button>' +
          '<button class="act-btn act-del" data-id="' + it.id + '" data-act="del" title="Delete"><i class="fa-solid fa-trash"></i></button>' +
          '</td>' + cols + '</tr>';
      }).join('');
      listWrap.querySelectorAll('[data-act="edit"]').forEach(function (b) { b.onclick = function () { openModal(Store.find(cfg.key, b.dataset.id)); }; });
      listWrap.querySelectorAll('[data-act="del"]').forEach(function (b) {
        b.onclick = function () {
          KCAdmin.confirmDelete('Delete this ' + cfg.itemName + '? This cannot be undone.', function () {
            Store.remove(cfg.key, b.dataset.id);
            render();
            KCAdmin.toast(cfg.itemName + ' deleted.', 'fa-trash');
          });
        };
      });
    }

    function openModal(item) {
      var isEdit = !!item;
      modal.innerHTML =
        '<div class="modal-head"><h3>' + (isEdit ? 'Edit ' : 'Add New ') + cfg.itemName + '</h3><button class="modal-close" id="kc-modal-x"><i class="fa-solid fa-xmark"></i></button></div>' +
        '<div class="modal-body" id="kc-modal-body"></div>' +
        '<div class="modal-foot"><button class="btn btn-outline" id="kc-modal-cancel">Cancel</button><button class="btn btn-primary" id="kc-modal-save"><i class="fa-solid fa-floppy-disk"></i> Save</button></div>';
      var body = document.getElementById('kc-modal-body');
      var rowsHtml = '';
      var i = 0;
      while (i < cfg.fields.length) {
        var f = cfg.fields[i];
        if (f.pairWith) { i++; continue; }
        if (cfg.fields[i + 1] && cfg.fields[i + 1].pairWith === f.name) {
          rowsHtml += '<div class="form-row">' + fieldHTML(f, item ? item[f.name] : undefined) + fieldHTML(cfg.fields[i + 1], item ? item[cfg.fields[i + 1].name] : undefined) + '</div>';
          i += 2; continue;
        }
        rowsHtml += fieldHTML(f, item ? item[f.name] : undefined);
        i++;
      }
      body.innerHTML = rowsHtml;
      cfg.fields.forEach(bindImageField);
      overlay.classList.add('open');
      document.getElementById('kc-modal-x').onclick = closeModal;
      document.getElementById('kc-modal-cancel').onclick = closeModal;
      document.getElementById('kc-modal-save').onclick = function () {
        var data = {};
        var missing = false;
        cfg.fields.forEach(function (f) {
          var v = readField(f);
          if (f.required && (v === '' || v === undefined)) missing = true;
          data[f.name] = v;
        });
        if (missing) { KCAdmin.toast('Please fill all required fields.', 'fa-triangle-exclamation'); return; }
        if (cfg.beforeSave) data = cfg.beforeSave(data, item);
        if (isEdit) { Store.update(cfg.key, item.id, data); KCAdmin.toast(cfg.itemName + ' updated successfully.'); }
        else { Store.add(cfg.key, data); KCAdmin.toast(cfg.itemName + ' added successfully.'); }
        closeModal(); render();
      };
    }
    function closeModal() { overlay.classList.remove('open'); modal.innerHTML = ''; }

    if (addBtn) addBtn.onclick = function () { openModal(null); };
    if (searchInput) searchInput.oninput = render;
    if (filterSelect) filterSelect.onchange = render;
    overlay.onclick = function (e) { if (e.target === overlay) closeModal(); };
    render();
    return { render: render, openModal: openModal };
  };
})();
