// Chỉ nạp cho người biên soạn. Bộ đệm theo tab + phiên đăng nhập, hết hạn sau 30 phút; không phải nội dung đã lưu/duyệt.
(() => {
  'use strict';
  const forms = [...document.querySelectorAll('form[data-giu-nhap]')];
  if (!forms.length) return;
  const prefix = 'phongdao-nhap-do:', scope = forms[0].dataset.phienSoan, ttl = 30 * 60 * 1000;
  const changed = new Set(), initial = new Map();
  let leaving = false, storage = true;
  const key = f => prefix + scope + ':' + f.dataset.giuNhap;
  const allowed = e => e.name && !e.disabled && e.type !== 'password' && e.type !== 'file' && !['submit', 'button'].includes(e.type)
    && (e.type !== 'hidden' || /^muc\[[^\]]+\]\[goc\]$/.test(e.name) || ['phien_phan', 'attachment_id'].includes(e.name));
  const fields = f => [...f.querySelectorAll('input, textarea, select')].filter(allowed);
  const values = f => fields(f).map(e => [e.name, e.type === 'checkbox' || e.type === 'radio' ? e.checked : e.value]);
  const remove = k => { try { sessionStorage.removeItem(k); } catch (_) { storage = false; } };
  // Dọn dữ liệu hết hạn hoặc thuộc phiên/tài khoản khác trên chính tab này.
  try {
    for (const k of Object.keys(sessionStorage).filter(k => k.startsWith(prefix))) {
      const d = JSON.parse(sessionStorage.getItem(k) || 'null');
      if (!k.startsWith(prefix + scope + ':') || !d || Date.now() - d.time > ttl) remove(k);
    }
  } catch (_) { storage = false; }
  const params = new URLSearchParams(location.search);
  if (['da_luu', 'khong_doi', 'nhap', 'da_luu_sang'].includes(params.get('ket_qua'))) {
    const id = params.get('da_luu_bai') || params.get('id'), part = params.get('da_luu_phan') || params.get('phan');
    if (id && part) remove(prefix + scope + ':' + id + ':' + part);
  }
  const save = f => {
    const entries = values(f), dirty = f.dataset.giuLoi === '1' || JSON.stringify(entries) !== initial.get(f);
    if (!dirty) { changed.delete(f); remove(key(f)); return; }
    changed.add(f);
    const rows = [...f.querySelectorAll('li.muc-sua input[name$="[goc]"]')].map(e => e.name.match(/^muc\[([^\]]+)\]/)?.[1]).filter(Boolean);
    try { sessionStorage.setItem(key(f), JSON.stringify({ time: Date.now(), entries, rows })); } catch (_) { storage = false; }
  };
  const notice = f => {
    if (f.querySelector('[data-nhap-do-bao]')) return;
    const p = document.createElement('p'); p.dataset.nhapDoBao = '1'; p.setAttribute('role', 'status');
    p.textContent = 'Đã lấy lại phần đang nhập trên tab này. Chưa lưu vào bài. ';
    const b = document.createElement('button'); b.type = 'button'; b.className = 'button btn'; b.textContent = 'Bỏ phần chưa lưu';
    b.addEventListener('click', () => { changed.delete(f); remove(key(f)); leaving = true; location.reload(); });
    p.append(b); (f.querySelector('.tam-than') || f).prepend(p);
  };
  for (const f of forms) {
    initial.set(f, JSON.stringify(values(f)));
    let d;
    try { d = JSON.parse(sessionStorage.getItem(key(f)) || 'null'); } catch (_) { storage = false; }
    if (f.dataset.giuLoi === '1') { save(f); }
    else if (d && Date.now() - d.time <= ttl && Array.isArray(d.entries)) {
      const list = f.querySelector('[data-ds]'), template = f.querySelector('template[data-mau]');
      if (list && template && Array.isArray(d.rows)) {
        list.replaceChildren();
        for (const row of d.rows.filter(k => /^(?:\d+|m\d+)$/.test(k))) list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__i__', row));
      }
      const controls = fields(f);
      for (const [name, value] of d.entries) {
        for (const e of controls.filter(e => e.name === name)) {
          if (e.type === 'checkbox' || e.type === 'radio') e.checked = value === true;
          else if (typeof value === 'string') e.value = value;
        }
      }
      f.dataset.giuLoi = '1'; changed.add(f); notice(f);
      const section = f.closest('details'); if (section) section.open = true;
    }
    for (const event of ['input', 'change']) f.addEventListener(event, () => save(f));
  }
  document.addEventListener('submit', e => {
    forms.forEach(save);
    const others = [...changed].filter(f => f !== e.target);
    if (!storage && others.length && !confirm('Phần khác còn chữ chưa lưu và bộ nhớ tab không dùng được. Tiếp tục sẽ bỏ chữ đó.')) { e.preventDefault(); return; }
    if (!e.target.matches('dialog.tam-sua form:not([data-gui-thuong])')) leaving = true;
  });
  window.addEventListener('beforeunload', e => { if (!leaving && changed.size) { e.preventDefault(); e.returnValue = ''; } });
  document.addEventListener('click', e => {
    if (e.target.closest('a[href*="action=logout"]')) {
      try { Object.keys(sessionStorage).filter(k => k.startsWith(prefix)).forEach(remove); } catch (_) { /* bộ nhớ bị chặn */ }
      changed.clear(); leaving = true;
    }
  });
  window.phongdaoNhapDo = { daLuu(f) { changed.delete(f); remove(key(f)); leaving = true; } };
  if (forms.some(f => f.dataset.giuLoi === '1')) {
    const error = document.querySelector('.notice-error');
    if (error) { error.tabIndex = -1; error.focus(); }
  }
})();
