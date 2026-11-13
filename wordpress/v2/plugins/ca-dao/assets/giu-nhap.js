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
    && (e.type !== 'hidden' || /^muc\[[^\]]+\]\[(?:goc|dinh_dang)\]$/.test(e.name) || ['phien_phan', 'phien_the_tho', 'attachment_id', 'da_thay[]', 'dinh_dang', 'phien_duong_dan'].includes(e.name));
  const fields = f => [...f.querySelectorAll('input, textarea, select')].filter(allowed);
  const sorted=entries=>entries.sort((a,b)=>(a[0]+'|'+a[2]).localeCompare(b[0]+'|'+b[2]));
  const values = f => {const seen=new Map();return sorted(fields(f).map(e=>{const n=seen.get(e.name)||0;seen.set(e.name,n+1);const choice=e.type==='checkbox'||e.type==='radio';return [e.name,choice?e.checked:e.value,choice?e.value:n];}));};
  const title = f => f.dataset.tenPhan || f.closest('[data-ten-phan]')?.dataset.tenPhan || 'Phần đang sửa';
  const paint = f => {
    const dirty = changed.has(f);f.dataset.nhapChuaLuu=dirty?'1':'0';
    if (f.closest('.phongdao-admin-sua')) {
      let p=f.querySelector('[data-nhap-trang-thai]');
      if(!p){p=document.createElement('p');p.dataset.nhapTrangThai='1';p.setAttribute('role','status');f.prepend(p);}
      const text=dirty?(storage?'Chưa lưu vào bài · đã giữ trên tab này (tối đa 30 phút).':'Chưa lưu vào bài · bộ nhớ tab không dùng được, hãy lưu trước khi rời trang.'):'Đang xem bản đã lưu; thay đổi mới cần lưu riêng phần này.';
      if(p.textContent!==text)p.textContent=text;
      const flag=f.closest('details.phongdao-sua-phan')?.querySelector(':scope>summary [data-phan-trang-thai]');
      if(flag)flag.textContent=dirty?' · Chưa lưu':'';
    }
    const banner=document.querySelector('[data-tong-chua-luu]');
    if(banner){banner.hidden=false;const text=changed.size?`${changed.size} phần chưa lưu vào bài: ${[...changed].map(title).join(', ')}.`:'Chưa có thay đổi chưa lưu trên trang này. Mỗi phần được lưu riêng.';if(banner.textContent!==text)banner.textContent=text;}
    document.dispatchEvent(new CustomEvent('phongdao:nhap-do'));
  };
  const remove = k => { try { sessionStorage.removeItem(k); } catch (_) { storage = false; } };
  // Dọn dữ liệu hết hạn hoặc thuộc phiên/tài khoản khác trên chính tab này.
  try {
    for (const k of Object.keys(sessionStorage).filter(k => k.startsWith(prefix))) {
      const d = JSON.parse(sessionStorage.getItem(k) || 'null');
      if (!k.startsWith(prefix + scope + ':') || !d || Date.now() - d.time > ttl) remove(k);
    }
  } catch (_) { storage = false; }
  const params = new URLSearchParams(location.search);
  if (['da_luu', 'khong_doi', 'nhap', 'da_luu_sang', 'da_luu_pl'].includes(params.get('ket_qua'))) {
    const id = params.get('da_luu_bai') || params.get('id'), part = params.get('da_luu_phan') || params.get('phan') || params.get('phan_loai');
    if (id && part) remove(prefix + scope + ':' + id + ':' + part);
  }
  const save = f => {
    const entries = values(f), dirty = f.dataset.giuLoi === '1' || JSON.stringify(entries) !== initial.get(f);
    if (!dirty) { changed.delete(f); remove(key(f)); paint(f); return; }
    changed.add(f);
    const rows = [...f.querySelectorAll('li.muc-sua input[name$="[goc]"]')].map(e => e.name.match(/^muc\[([^\]]+)\]/)?.[1]).filter(Boolean);
    const openRows=[...f.querySelectorAll('li.muc-sua')].filter(li=>li.querySelector('.phongdao-muc-gon')?.open).map(li=>li.querySelector('input[name$="[goc]"]')?.name.match(/^muc\[([^\]]+)\]/)?.[1]).filter(Boolean);
    try { sessionStorage.setItem(key(f), JSON.stringify({ time: Date.now(), entries, rows, openRows, mocPhanLoai:f.dataset.mocDaNhap||f.dataset.mocServer })); } catch (_) { storage = false; }
    paint(f);
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
      if(f.dataset.mocServer&&typeof d.mocPhanLoai==='string'){
        try{const ids=JSON.parse(d.mocPhanLoai);if(!Array.isArray(ids)||!ids.every(Number.isInteger))throw Error('invalid baseline');f.querySelectorAll('[name="da_thay[]"]').forEach(e=>e.remove());for(const id of ids){const e=document.createElement('input');e.type='hidden';e.name='da_thay[]';e.value=id;f.append(e);}f.dataset.mocDaNhap=d.mocPhanLoai;}catch(_){storage=false;}
      }
      const controls = fields(f);
      for (const [name, value, identity] of d.entries) {
        const same=controls.filter(e=>e.name===name);
        for (const e of same.filter((e,i)=>identity===undefined || (e.type==='checkbox'||e.type==='radio'?e.value===identity:i===identity))) {
          if (e.type === 'checkbox' || e.type === 'radio') e.checked = value === true;
          else if (typeof value === 'string') e.value = value;
        }
      }
      f.dataset.giuLoi = '1'; changed.add(f); notice(f);
      const section = f.closest('details'); if (section) section.open = true;
      f.querySelectorAll('li.muc-sua').forEach(li=>{const detail=li.querySelector('.phongdao-muc-gon'),id=li.querySelector('input[name$="[goc]"]')?.name.match(/^muc\[([^\]]+)\]/)?.[1];if(detail)detail.open=Array.isArray(d.openRows)?d.openRows.includes(id):false;});
    }
    for (const event of ['input', 'change']) f.addEventListener(event, () => save(f));
    paint(f);
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
  window.phongdaoNhapDo = { coDoi(f) {return changed.has(f);}, cacPhan() {return [...changed];}, themTruong(f) {const base=JSON.parse(initial.get(f)||'[]');for(const item of values(f))if(!base.some(old=>old[0]===item[0]&&old[2]===item[2]))base.push(item);initial.set(f,JSON.stringify(sorted(base)));save(f);}, daLuu(f) { changed.delete(f); remove(key(f)); initial.set(f,JSON.stringify(values(f)));delete f.dataset.giuLoi;paint(f);leaving = true; } };
  if (forms.some(f => f.dataset.giuLoi === '1')) {
    const error = document.querySelector('.notice-error');
    if (error) { error.tabIndex = -1; error.focus(); }
  }
})();
