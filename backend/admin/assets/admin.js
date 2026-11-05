/* BB Caterers admin — small vanilla helpers (no build step) */
(function () {
  'use strict';

  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));
  const csrf = $('meta[name="csrf-token"]')?.content || $('input[name="_csrf"]')?.value || '';
  // Custom icons that match the Figma design (same shapes as frontend/src/components/common/customIcons.js)
  const CUSTOM_ICONS = {
    AwardStar: [['circle', { cx: '12', cy: '8', r: '6' }], ['path', { d: 'm12 5.4.8 1.6 1.8.3-1.3 1.2.3 1.8-1.6-.85-1.6.85.3-1.8L9.4 7.3l1.8-.3z' }], ['path', { d: 'm15.48 12.89 1.51 8.53a.5.5 0 0 1-.81.47l-3.58-2.69a1 1 0 0 0-1.2 0l-3.58 2.69a.5.5 0 0 1-.81-.47l1.51-8.53' }]],
    Rings: [['circle', { cx: '8.5', cy: '14.5', r: '5.5' }], ['circle', { cx: '15.5', cy: '14.5', r: '5.5' }], ['path', { d: 'm8.5 6.5-1.5-2 1.5-2 1.5 2z' }]],
    Laddoo: [['circle', { cx: '12', cy: '7', r: '3.5' }], ['circle', { cx: '7.5', cy: '14', r: '3.5' }], ['circle', { cx: '16.5', cy: '14', r: '3.5' }], ['path', { d: 'M3 20.5h18' }]],
  };
  const icons = () => window.lucide && window.lucide.createIcons({ icons: Object.assign({}, window.lucide.icons, CUSTOM_ICONS) });

  /* ---------------- Toast popups ---------------- */
  const TITLES = { success: 'Success', error: 'Something went wrong', warning: 'Heads up' };
  const ICONS = { success: 'check', error: 'x', warning: 'alert-triangle' };

  // Centred popup. One at a time; others wait in a queue.
  // Success closes by itself; errors/warnings stay until the user clicks OK.
  const queue = [];
  let showing = false;

  function toast(type, message, ms) {
    queue.push({ type: TITLES[type] ? type : 'success', message, ms });
    if (!showing) next();
  }

  function next() {
    const box = $('#toasts');
    const item = queue.shift();
    if (!box || !item) { showing = false; box && box.classList.remove('active'); return; }
    showing = true;
    const { type, message } = item;
    const ms = item.ms ?? (type === 'success' ? 2600 : 0); // 0 = stay until closed
    const el = document.createElement('div');
    el.className = 'toast ' + type;
    el.setAttribute('role', type === 'error' ? 'alertdialog' : 'status');
    el.setAttribute('aria-live', 'assertive');
    el.innerHTML =
      `<span class="t-icon"><i data-lucide="${ICONS[type]}"></i></span>` +
      `<div class="t-body"><strong>${TITLES[type]}</strong><p></p></div>` +
      `<button class="t-close" type="button" aria-label="Close"><i data-lucide="x"></i></button>` +
      (ms ? `<span class="t-progress" style="animation-duration:${ms}ms"></span>`
          : `<button class="btn ${type === 'error' ? 'danger' : 'primary'} small t-ok" type="button">OK</button>`);
    $('p', el).textContent = message;
    box.classList.add('active');
    box.appendChild(el);
    icons();
    $('.t-ok', el)?.focus();

    let timer = null;
    const close = () => {
      clearTimeout(timer);
      document.removeEventListener('keydown', onKey);
      box.removeEventListener('click', onBackdrop);
      el.classList.add('hide');
      setTimeout(() => { el.remove(); next(); }, 250);
    };
    const onKey = (e) => { if (e.key === 'Escape' || e.key === 'Enter') { e.preventDefault(); close(); } };
    const onBackdrop = (e) => { if (e.target === box) close(); };
    if (ms) timer = setTimeout(close, ms);
    $('.t-close', el).addEventListener('click', close);
    $('.t-ok', el)?.addEventListener('click', close);
    document.addEventListener('keydown', onKey);
    box.addEventListener('click', onBackdrop);
  }
  window.toast = toast;
  (window.__FLASH__ || []).forEach((f) => toast(f.type, f.message));

  /* ---------------- Confirm dialog ---------------- */
  const modal = $('#confirmModal');
  function confirmDialog(text) {
    return new Promise((resolve) => {
      if (!modal) return resolve(window.confirm(text));
      $('#confirmText', modal).textContent = text;
      modal.hidden = false;
      const ok = $('[data-confirm-ok]', modal);
      ok.focus();
      const done = (v) => {
        modal.hidden = true;
        ok.removeEventListener('click', yes);
        $('[data-confirm-cancel]', modal).removeEventListener('click', no);
        document.removeEventListener('keydown', esc);
        resolve(v);
      };
      const yes = () => done(true);
      const no = () => done(false);
      const esc = (e) => { if (e.key === 'Escape') done(false); };
      ok.addEventListener('click', yes);
      $('[data-confirm-cancel]', modal).addEventListener('click', no);
      document.addEventListener('keydown', esc);
    });
  }
  $$('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', async (e) => {
      if (form.dataset.confirmed) return;
      e.preventDefault();
      if (await confirmDialog(form.dataset.confirm)) {
        form.dataset.confirmed = '1';
        form.submit();
      }
    });
  });

  /* Bulk delete confirmation on enquiries */
  const bulk = $('#bulkForm');
  if (bulk) {
    const all = $('[data-check-all]', bulk);
    all && all.addEventListener('change', () => $$('input[name="ids[]"]', bulk).forEach((c) => (c.checked = all.checked)));
    bulk.addEventListener('submit', async (e) => {
      const action = $('select[name="action"]', bulk).value;
      const n = $$('input[name="ids[]"]:checked', bulk).length;
      if (!action) { e.preventDefault(); toast('warning', 'Choose a bulk action first.'); return; }
      if (!n) { e.preventDefault(); toast('warning', 'Select at least one enquiry.'); return; }
      if (action === 'delete' && !bulk.dataset.confirmed) {
        e.preventDefault();
        if (await confirmDialog(`Delete ${n} enquir${n === 1 ? 'y' : 'ies'} permanently?`)) {
          bulk.dataset.confirmed = '1';
          bulk.submit();
        }
      }
    });
  }

  /* ---------------- Sidebar (mobile) ---------------- */
  $$('[data-sidebar-open]').forEach((b) => b.addEventListener('click', () => document.body.classList.add('sidebar-open')));
  $$('[data-sidebar-close]').forEach((b) => b.addEventListener('click', () => document.body.classList.remove('sidebar-open')));

  /* ---------------- Clickable rows ---------------- */
  $$('tr[data-href]').forEach((tr) => tr.addEventListener('click', () => (location.href = tr.dataset.href)));

  /* ---------------- Password show/hide + strength ---------------- */
  $$('[data-pw-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const input = btn.parentElement.querySelector('input');
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.innerHTML = `<i data-lucide="${show ? 'eye-off' : 'eye'}"></i>`;
      icons();
    });
  });
  $$('[data-strength]').forEach((input) => {
    const bar = input.closest('.field').querySelector('.strength span');
    input.addEventListener('input', () => {
      const v = input.value;
      let s = 0;
      if (v.length >= 8) s++;
      if (v.length >= 12) s++;
      if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
      if (/\d/.test(v)) s++;
      if (/[^A-Za-z0-9]/.test(v)) s++;
      const colors = ['#b3261e', '#b3261e', '#c48a22', '#c48a22', '#2f872e', '#214a2b'];
      bar.style.width = (s / 5) * 100 + '%';
      bar.style.background = colors[s];
    });
  });

  /* ---------------- Image upload preview + drag & drop ---------------- */
  $$('[data-image-upload]').forEach((wrap) => {
    const input = $('input[type=file]', wrap);
    const drop = $('.image-drop', wrap);
    const show = (file) => {
      if (!file) return;
      if (file.size > 8 * 1024 * 1024) { toast('error', 'Image is larger than 8 MB.'); input.value = ''; return; }
      const url = URL.createObjectURL(file);
      drop.innerHTML = `<img src="${url}" alt="">`;
      const rm = $('input[type=checkbox]', wrap);
      if (rm) rm.checked = false;
    };
    $('[data-pick]', wrap)?.addEventListener('click', () => input.click());
    input.addEventListener('change', () => show(input.files[0]));
    ['dragenter', 'dragover'].forEach((ev) => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.add('dragover'); }));
    ['dragleave', 'drop'].forEach((ev) => drop.addEventListener(ev, () => drop.classList.remove('dragover')));
    drop.addEventListener('drop', (e) => {
      e.preventDefault();
      const file = e.dataTransfer.files[0];
      if (!file || !file.type.startsWith('image/')) return toast('error', 'Please drop an image file.');
      const dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
      show(file);
    });
  });

  /* ---------------- Icon picker ---------------- */
  $$('[data-icon-picker]').forEach((picker) => {
    const select = $('select', picker);
    const preview = $('.icon-preview', picker);
    const set = (name) => {
      select.value = name;
      preview.innerHTML = name ? `<i data-lucide="${name}"></i>` : '';
      $$('.icon-choice', picker).forEach((b) => b.classList.toggle('selected', b.dataset.icon === name));
      icons();
    };
    select.addEventListener('change', () => set(select.value));
    $$('.icon-choice', picker).forEach((b) => b.addEventListener('click', () => set(b.dataset.icon)));
  });

  /* ---------------- SMTP: suggest port for encryption ---------------- */
  $('[data-port-sync]')?.addEventListener('change', (e) => {
    const port = $('#smtp_port');
    const map = { tls: '587', ssl: '465', none: '25' };
    if (port && Object.values(map).includes(port.value)) port.value = map[e.target.value];
  });

  /* ---------------- Loading state on submit ---------------- */
  $$('form').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (e.defaultPrevented) return;
      const btn = e.submitter || $('button[type=submit]', form);
      if (!btn || btn.dataset.busy) return;
      setTimeout(() => {
        if (e.defaultPrevented) return;
        btn.dataset.busy = '1';
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner"></span> ${btn.dataset.loading || 'Saving…'}`;
      }, 0);
      // keep the clicked button's name/value (e.g. "add_another")
      if (btn && btn.name) {
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = btn.name; h.value = btn.value;
        form.appendChild(h);
      }
    });
  });

  /* ---------------- Warn about unsaved changes ---------------- */
  $$('form[data-dirty-check]').forEach((form) => {
    let dirty = false;
    form.addEventListener('input', () => (dirty = true));
    form.addEventListener('change', () => (dirty = true));
    form.addEventListener('submit', () => (dirty = false));
    window.addEventListener('beforeunload', (e) => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  });

  /* ---------------- AJAX helper ---------------- */
  async function post(url, data) {
    const body = new URLSearchParams(data);
    body.append('_csrf', csrf);
    const res = await fetch(url, { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrf } });
    const json = await res.json().catch(() => ({ success: false, message: 'Unexpected server response.' }));
    if (!res.ok || !json.success) throw new Error(json.message || 'Request failed.');
    return json;
  }

  /* ---------------- Visible toggle ---------------- */
  const table = $('table[data-sortable]');
  if (table) {
    const endpoint = table.dataset.endpoint;
    $$('[data-toggle-id]', table).forEach((cb) => {
      cb.addEventListener('change', async () => {
        try {
          const r = await post(endpoint, { action: 'toggle', id: cb.dataset.toggleId });
          cb.checked = r.active;
          toast('success', r.message, 2600);
        } catch (err) {
          cb.checked = !cb.checked;
          toast('error', err.message);
        }
      });
    });

    /* Drag & drop reorder (handle only) */
    const tbody = $('tbody', table);
    let dragRow = null;
    $$('tr', tbody).forEach((tr) => {
      const handle = $('.drag-handle', tr);
      handle.addEventListener('mousedown', () => (tr.draggable = true));
      handle.addEventListener('touchstart', () => (tr.draggable = true), { passive: true });
      tr.addEventListener('dragstart', (e) => { dragRow = tr; tr.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; });
      tr.addEventListener('dragend', async () => {
        tr.classList.remove('dragging');
        tr.draggable = false;
        $$('tr', tbody).forEach((r) => r.classList.remove('drop-target'));
        if (!dragRow) return;
        dragRow = null;
        try {
          const ids = $$('tr', tbody).map((r) => r.dataset.id);
          const body = {};
          ids.forEach((id, i) => (body[`ids[${i}]`] = id));
          const r = await post(endpoint, Object.assign({ action: 'reorder' }, body));
          toast('success', r.message, 2200);
        } catch (err) {
          toast('error', err.message);
        }
      });
      tr.addEventListener('dragover', (e) => {
        e.preventDefault();
        if (!dragRow || dragRow === tr) return;
        const rect = tr.getBoundingClientRect();
        const after = e.clientY > rect.top + rect.height / 2;
        tbody.insertBefore(dragRow, after ? tr.nextSibling : tr);
      });
    });
  }

  /* ---------------- Count-up numbers on dashboard ---------------- */
  $$('[data-count]').forEach((el) => {
    const end = +el.dataset.count;
    if (!end) return;
    const start = performance.now();
    const step = (t) => {
      const p = Math.min(1, (t - start) / 700);
      el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(step);
    };
    el.textContent = '0';
    requestAnimationFrame(step);
  });

  /* ---------------- Character counters (SEO) ---------------- */
  $$("[data-counter]").forEach((input) => {
    const max = +input.dataset.counter;
    const out = document.createElement("small");
    out.className = "char-count";
    input.insertAdjacentElement("afterend", out);
    const upd = () => { const n = input.value.length; out.textContent = n + " / " + max; out.classList.toggle("over", n > max); };
    input.addEventListener("input", upd); upd();
  });

  icons();
})();
