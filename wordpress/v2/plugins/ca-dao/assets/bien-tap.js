// Chế độ sửa trên trang bài (D-094, docs/28 mục 4.2). Chỉ nạp cho người có quyền biên tập; JavaScript thuần, không bước dựng.
// Lưu: gửi form của tấm sửa qua REST phongdao/v1 (nonce của WordPress), rồi tải lại trang để thấy đúng như người đọc sẽ thấy.
(() => {
  'use strict';
  const cfg = window.phongdaoBienTap;
  if (!cfg) return;
  const dai = document.querySelector('.dai-bien-tap');
  const bai = dai ? dai.dataset.bai : '';
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  const goi = async (duong, than) => {
    const r = await fetch(cfg.api + duong, { method: 'POST', headers: { 'X-WP-Nonce': cfg.nonce }, body: than, credentials: 'same-origin' });
    let d = {};
    try { d = await r.json(); } catch (e) { /* trả về không phải JSON */ }
    if (!r.ok && !d.loi) d.loi = [d.message || 'Chưa lưu được (mã ' + r.status + '). Thử tải lại trang.'];
    return d;
  };

  // Thông báo sau khi tải lại, kèm Hoàn tác trong 10 giây
  const bao = (chu, viec) => {
    let t = document.getElementById('phongdao-bao');
    if (!t) { t = document.createElement('div'); t.id = 'phongdao-bao'; t.className = 'toast'; t.setAttribute('role', 'status'); document.body.append(t); }
    t.textContent = chu;
    if (viec) {
      const b = document.createElement('button');
      b.type = 'button'; b.textContent = 'Hoàn tác';
      b.onclick = async () => { b.disabled = true; const d = await goi('/viec/' + viec + '/hoan-tac'); tai_lai(d.ok ? 'Đã hoàn tác.' : (d.loi || []).join(' ')); };
      t.append(' ', b);
    }
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), viec ? 10000 : 4500);
  };
  const tai_lai = (chu, viec) => { try { sessionStorage.setItem('phongdao-bao', JSON.stringify({ chu, viec })); } catch (e) { /* bộ nhớ bị chặn */ } location.reload(); };
  try {
    const cu = JSON.parse(sessionStorage.getItem('phongdao-bao') || 'null');
    sessionStorage.removeItem('phongdao-bao');
    if (cu && cu.chu) bao(cu.chu, cu.viec);
  } catch (e) { /* bộ nhớ bị chặn */ }

  const hoanQuyet = document.querySelector('[data-hv-hoan]');
  if (hoanQuyet) {
    const hetHan = () => {
      const nut = hoanQuyet.querySelector('button');
      if (nut) { nut.disabled = true; nut.textContent = 'Đã hết thời gian Hoàn tác nhanh'; }
    };
    const conLai = Number(hoanQuyet.dataset.han) * 1000 - Date.now();
    if (conLai <= 0) hetHan(); else setTimeout(hetHan, conLai);
  }

  // Duyệt từng thẻ: phím tắt chỉ chạy khi người dùng không đang thao tác với điều khiển khác.
  const tung = document.querySelector('.tung-the');
  if (tung) {
    const goiY = tung.querySelector('[data-hv="goi-y"]');
    if (goiY) goiY.hidden = false;
    document.addEventListener('keydown', e => {
      if (e.defaultPrevented || e.altKey || e.ctrlKey || e.metaKey || e.shiftKey || e.isComposing || e.repeat) return;
      const focus = document.activeElement;
      if (focus?.closest('input, textarea, select, button, a, [contenteditable], [role="button"], [role="textbox"], dialog[open]')) return;
      const phim = e.key.toLowerCase();
      if (phim === 'j' || phim === 'k') {
        const lienKet = tung.querySelector(`[data-hv="${phim === 'j' ? 'sau' : 'truoc'}"]`);
        if (lienKet) { e.preventDefault(); location.assign(lienKet.href); }
      } else if (/^[1-3]$/.test(phim)) {
        const nut = tung.querySelectorAll('form button[name="quyet"]:not([disabled])')[Number(phim) - 1];
        if (nut) { e.preventDefault(); nut.click(); }
      }
    });
  }

  // Xem như người đọc: ẩn mọi phần chỉ người biên soạn thấy (nhớ trên máy)
  const xem = bat => {
    if (!dai) return;
    $$('.nut-sua, .goc-nguon, #lich-su, [data-chi-sua]').forEach(x => { x.hidden = bat; });
    const n = dai.querySelector('[data-action="xem-nhu-doc-gia"]');
    if (n) n.setAttribute('aria-pressed', String(bat));
    try { localStorage.setItem('phongdao-xem-nhu-doc-gia', bat ? '1' : '0'); } catch (e) { /* bộ nhớ bị chặn */ }
  };
  try { if (localStorage.getItem('phongdao-xem-nhu-doc-gia') === '1') xem(true); } catch (e) { /* bộ nhớ bị chặn */ }

  document.addEventListener('click', async e => {
    const b = e.target.closest('[data-action]');
    if (!b) return;
    const viec = b.dataset.action;
    if (viec === 'mo-tam') {
      const d = document.getElementById('tam-' + b.dataset.tam);
      if (!d) return;
      e.preventDefault();
      d.showModal();
      const o = d.querySelector('textarea, input:not([type=hidden]), select');
      if (o) o.focus();
    } else if (viec === 'dong-tam') {
      b.closest('dialog').close();
    } else if (viec === 'them-muc') {
      const d = b.closest('dialog'), mau = d.querySelector('template[data-mau]'), ds = d.querySelector('[data-ds]');
      const k = 'm' + Date.now();
      ds.insertAdjacentHTML('beforeend', mau.innerHTML.replaceAll('__i__', k));
      const o = ds.lastElementChild.querySelector('textarea, input:not([type=hidden])');
      if (o) o.focus();
    } else if (viec === 'xem-nhu-doc-gia') {
      xem(b.getAttribute('aria-pressed') !== 'true');
    } else if (viec === 'trang-thai') {
      b.disabled = true;
      const f = new FormData();
      f.append('trang_thai', b.dataset.trangThai);
      f.append('trang_thai_cu', dai.dataset.trangThai);
      const d = await goi('/tac-pham/' + bai + '/trang-thai', f);
      if (d.ok) tai_lai(d.thong_bao || 'Đã đổi trạng thái.');
      else { b.disabled = false; bao((d.loi || []).join(' ')); }
    }
  });

  // Thêm bài: máy nhận thể thơ, báo trùng khi dán lời (chỉ báo, không chặn); tên tự lấy dòng đầu nếu người chưa gõ
  const them = document.querySelector('form[data-them-bai]');
  if (them) {
    const o = them.querySelector('[name=loi]'), ten = them.querySelector('[name=ten]'), ra = document.getElementById('them-phan-tich');
    const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
    let hen, ten_tu_go = false;
    ten.addEventListener('input', () => { ten_tu_go = true; });
    o.addEventListener('input', () => {
      if (!ten_tu_go) ten.value = (o.value.split('\n').find(d => d.trim()) || '').trim();
      clearTimeout(hen);
      hen = setTimeout(async () => {
        const f = new FormData();
        f.append('loi', o.value);
        const d = await goi('/trung/kiem', f);
        if (!d.so_dong) { ra.innerHTML = '<p class="small muted">Dán lời để máy nhận thể thơ và kiểm trùng.</p>'; return; }
        ra.innerHTML = '<p class="nhan-may">Máy nhận: ' + esc(d.the_tho) + ' · ' + d.so_dong + ' dòng</p>'
          + (d.trung || []).map(t => '<div class="notice"><strong>' + esc(t.muc) + '</strong> (' + t.giong + '%) với ' + (t.url ? '<a href="' + esc(t.url) + '">' + esc(t.ten) + '</a>' : esc(t.ten)) + '. Vẫn lưu được; nếu là cùng bài, thêm làm dị bản ở trang bài đó.</div>').join('');
      }, 400);
    });
  }

  document.addEventListener('submit', async e => {
    const f = e.target.closest('dialog.tam-sua form');
    if (!f) return;
    e.preventDefault();
    const nut = [...f.querySelectorAll('[type=submit]')], loi = f.querySelector('.tam-loi');
    // Nút đã bấm mang giá trị riêng (Lưu nháp / Lưu và hiện cho người đọc): FormData không tự kèm nút bấm
    const du = new FormData(f);
    if (e.submitter && e.submitter.name) du.set(e.submitter.name, e.submitter.value);
    nut.forEach(n => { n.disabled = true; });
    loi.hidden = true;
    const d = await goi('/tac-pham/' + bai + '/phan/' + f.dataset.phan, du);
    if (d.ok) { tai_lai(d.thong_bao || 'Đã lưu.', d.doi ? d.viec : 0); return; }
    nut.forEach(n => { n.disabled = false; });
    loi.textContent = (d.loi || ['Chưa lưu được.']).join(' ');
    loi.hidden = false;
  });
})();
