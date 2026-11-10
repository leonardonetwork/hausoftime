/* HAUS OF TIME — interazioni del marketplace (senza dipendenze) */
(function () {
  'use strict';
  var H = window.HOT || {};
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  function post(url, data, method) {
    return fetch(url, {
      method: method || 'POST',
      headers: { 'X-CSRF-TOKEN': H.csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: data ? JSON.stringify(data) : null
    }).then(function (r) { return r.json().then(function (j) { j._ok = r.ok; return j; }); });
  }

  // ── Toast ────────────────────────────────────────────────────────
  var toastTimer;
  function toast(msg, link) {
    var t = $('#hsToast'); if (!t) return;
    t.innerHTML = '';
    var s = document.createElement('span'); s.textContent = msg; t.appendChild(s);
    if (link) { var a = document.createElement('a'); a.href = link.href; a.textContent = link.label; t.appendChild(a); }
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { t.classList.remove('show'); }, 3800);
  }
  H.toast = toast;

  // ── Header ───────────────────────────────────────────────────────
  var header = $('#hsHeader');
  if (header) {
    var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 24); };
    onScroll(); window.addEventListener('scroll', onScroll, { passive: true });
  }

  // ── Drawer ───────────────────────────────────────────────────────
  var drawer = $('#hsDrawer'), lastFocus;
  function openDrawer() {
    lastFocus = document.activeElement;
    drawer.classList.add('open'); drawer.setAttribute('aria-hidden', 'false');
    document.body.classList.add('hs-lock');
    $$('[data-drawer-open]').forEach(function (b) { b.setAttribute('aria-expanded', 'true'); });
    setTimeout(function () { var c = $('.hs-close', drawer); c && c.focus(); }, 50);
  }
  function closeDrawer() {
    drawer.classList.remove('open'); drawer.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('hs-lock');
    $$('[data-drawer-open]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
    lastFocus && lastFocus.focus();
  }
  if (drawer) {
    $$('[data-drawer-open]').forEach(function (b) { b.addEventListener('click', openDrawer); });
    $$('[data-drawer-close]', drawer).forEach(function (b) { b.addEventListener('click', closeDrawer); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && drawer.classList.contains('open')) closeDrawer(); });
  }

  // ── Ricerca con suggerimenti ─────────────────────────────────────
  $$('form[data-suggest]').forEach(function (form) {
    var input = $('input[name=q]', form), box = $('.hs-suggest', form), timer, ctrl, idx = -1;
    function close() { box.classList.remove('open'); idx = -1; }
    function render(items, q) {
      box.innerHTML = '';
      if (!items.length) { close(); return; }
      items.forEach(function (it) {
        var a = document.createElement('a'); a.href = it.url; a.setAttribute('role', 'option');
        var img;
        if (it.image) { img = document.createElement('img'); img.src = it.image; img.alt = ''; img.loading = 'lazy'; }
        else { img = document.createElement('span'); img.className = 'ph'; }
        var txt = document.createElement('span');
        var st = document.createElement('strong'); st.textContent = it.title;
        var sm = document.createElement('small'); sm.textContent = it.sub || '';
        txt.appendChild(st); txt.appendChild(sm);
        var em = document.createElement('em'); em.textContent = it.price;
        a.appendChild(img); a.appendChild(txt); a.appendChild(em); box.appendChild(a);
      });
      var all = document.createElement('a'); all.className = 'all';
      all.href = form.action + '?q=' + encodeURIComponent(q); all.textContent = box.dataset.all;
      box.appendChild(all);
      box.classList.add('open');
    }
    input.addEventListener('input', function () {
      var q = input.value.trim();
      clearTimeout(timer);
      if (q.length < 2) { close(); return; }
      timer = setTimeout(function () {
        if (ctrl) ctrl.abort();
        ctrl = window.AbortController ? new AbortController() : null;
        fetch(form.dataset.suggest + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, signal: ctrl ? ctrl.signal : undefined })
          .then(function (r) { return r.json(); }).then(function (d) { render(d, q); }).catch(function () {});
      }, 180);
    });
    input.addEventListener('keydown', function (e) {
      var links = $$('a', box); if (!box.classList.contains('open') || !links.length) return;
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        idx = (idx + (e.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length;
        links.forEach(function (l, i) { l.classList.toggle('active', i === idx); });
      } else if (e.key === 'Enter' && idx >= 0) { e.preventDefault(); window.location = links[idx].href; }
      else if (e.key === 'Escape') { close(); }
    });
    document.addEventListener('click', function (e) { if (!form.contains(e.target)) close(); });
  });

  // ── Comparsa card ────────────────────────────────────────────────
  function reveal(scope) {
    var cards = $$('.hs-card:not(.in)', scope);
    if (!('IntersectionObserver' in window)) { cards.forEach(function (c) { c.classList.add('in'); }); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) {
          var el = en.target, i = Array.prototype.indexOf.call(el.parentNode.children, el) % 6;
          setTimeout(function () { el.classList.add('in'); }, i * 70);
          io.unobserve(el);
        }
      });
    }, { rootMargin: '0px 0px -40px 0px', threshold: .05 });
    cards.forEach(function (c) { io.observe(c); });
  }
  reveal(document);

  // ── Preferiti ────────────────────────────────────────────────────
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-fav]');
    if (!btn) return;
    e.preventDefault();
    btn.classList.add('pop'); setTimeout(function () { btn.classList.remove('pop'); }, 460);
    post(btn.dataset.fav).then(function (r) {
      if (!r._ok) return;
      $$('[data-fav="' + btn.dataset.fav + '"]').forEach(function (b) {
        b.classList.toggle('on', r.active);
        b.setAttribute('aria-pressed', r.active ? 'true' : 'false');
        b.setAttribute('aria-label', r.active ? H.i18n.unfav : H.i18n.fav);
      });
      $$('[data-fav-count]').forEach(function (c) { c.textContent = r.count; c.dataset.count = r.count; });
    });
  });

  // ── Pin ubicazione (tap su mobile) ──────────────────────────────
  document.addEventListener('click', function (e) {
    var pin = e.target.closest('.hs-pin');
    $$('.hs-pin.show').forEach(function (p) { if (p !== pin) p.classList.remove('show'); });
    if (pin) { e.preventDefault(); pin.classList.toggle('show'); }
  });

  // ── Bolsa ────────────────────────────────────────────────────────
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-bag-add]');
    if (!form || e.submitter && e.submitter.name === 'buy_now') return;
    e.preventDefault();
    var btn = $('button[type=submit]', form); btn && btn.classList.add('is-loading');
    post(form.action).then(function (r) {
      btn && btn.classList.remove('is-loading');
      $$('[data-bag-count]').forEach(function (c) { c.textContent = r.count; c.dataset.count = r.count; });
      toast(r.message, r._ok ? { href: H.routes.checkout, label: H.i18n.checkout } : null);
      if (r._ok && form.dataset.afterAdd) { var t = $(form.dataset.afterAdd); t && (t.hidden = false); form.hidden = true; }
    }).catch(function () { btn && btn.classList.remove('is-loading'); form.submit(); });
  });

  // ── Carica altri ─────────────────────────────────────────────────
  var more = $('[data-load-more]');
  if (more) {
    more.addEventListener('click', function (e) {
      e.preventDefault();
      var url = more.dataset.next; if (!url) return;
      more.classList.add('is-loading');
      fetch(url + (url.indexOf('?') > -1 ? '&' : '?') + 'partial=1', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); }).then(function (d) {
          var grid = $('#hsGrid'); var tmp = document.createElement('div'); tmp.innerHTML = d.html;
          Array.prototype.slice.call(tmp.children).forEach(function (c) { grid.appendChild(c); });
          reveal(grid);
          more.classList.remove('is-loading');
          if (d.next) { more.dataset.next = d.next; } else { more.parentNode.remove(); }
          history.replaceState(null, '', url);
        });
    });
  }

  // ── Galleria scheda ──────────────────────────────────────────────
  var gal = $('[data-gallery]');
  if (gal) {
    var imgs = $$('img', gal), thumbs = $$('[data-thumb]'), cur = 0, lb = $('#hsLightbox'), lbImg = lb && $('img', lb);
    function show(i) {
      cur = (i + imgs.length) % imgs.length;
      imgs.forEach(function (im, k) { im.classList.toggle('on', k === cur); });
      thumbs.forEach(function (t, k) { t.classList.toggle('on', k === cur); });
      if (lb && lb.classList.contains('open')) lbImg.src = imgs[cur].dataset.full || imgs[cur].src;
    }
    thumbs.forEach(function (t, k) { t.addEventListener('click', function () { show(k); }); });
    $$('[data-gal-prev]').forEach(function (b) { b.addEventListener('click', function (e) { e.stopPropagation(); show(cur - 1); }); });
    $$('[data-gal-next]').forEach(function (b) { b.addEventListener('click', function (e) { e.stopPropagation(); show(cur + 1); }); });
    var sx = null;
    gal.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    gal.addEventListener('touchend', function (e) {
      if (sx === null) return; var dx = e.changedTouches[0].clientX - sx;
      if (Math.abs(dx) > 40) show(cur + (dx < 0 ? 1 : -1)); sx = null;
    });
    if (lb) {
      gal.addEventListener('click', function () {
        if (!imgs.length) return;
        lbImg.src = imgs[cur].dataset.full || imgs[cur].src; lb.classList.add('open'); document.body.classList.add('hs-lock');
      });
      $$('[data-lb-close]', lb).forEach(function (b) { b.addEventListener('click', function () { lb.classList.remove('open'); document.body.classList.remove('hs-lock'); }); });
      document.addEventListener('keydown', function (e) {
        if (!lb.classList.contains('open')) return;
        if (e.key === 'Escape') { lb.classList.remove('open'); document.body.classList.remove('hs-lock'); }
        if (e.key === 'ArrowRight') show(cur + 1);
        if (e.key === 'ArrowLeft') show(cur - 1);
      });
    }
  }

  // ── Condividi ────────────────────────────────────────────────────
  $$('[data-share]').forEach(function (b) {
    b.addEventListener('click', function () {
      var data = { title: document.title, url: location.href };
      if (navigator.share) { navigator.share(data).catch(function () {}); }
      else if (navigator.clipboard) { navigator.clipboard.writeText(location.href).then(function () { toast(H.i18n.copied); }); }
    });
  });

  // ── Bottoni che inviano form: stato di caricamento ──────────────
  document.addEventListener('submit', function (e) {
    if (e.defaultPrevented) return;
    var b = e.submitter || $('button[type=submit]', e.target);
    if (b && b.classList.contains('hs-btn')) b.classList.add('is-loading');
  });

  // ── Conferme ─────────────────────────────────────────────────────
  document.addEventListener('submit', function (e) {
    var f = e.target.closest('form[data-confirm]');
    if (f && !window.confirm(f.dataset.confirm)) { e.preventDefault(); e.stopImmediatePropagation(); var b = $('.is-loading', f); b && b.classList.remove('is-loading'); }
  }, true);
})();
