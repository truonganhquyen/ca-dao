/**
 * Hộp việc wp-admin kiểu hộp thư (T-188, T-189): phím tắt trên thẻ đang mở (J thẻ sau, K thẻ trước, 1/2/3 bấm nút quyết —
 * máy tính ghi số phím trên nút), nút Hoàn tác tự tắt khi hết 10 giây, hộp xác nhận ngắn trước khi làm nhanh nhiều thẻ.
 * Quyết không tải lại trang (báo cáo trinh sát lần 2, mục 2.2–2.5): form quyết và Hoàn tác gửi bằng fetch tới đúng
 * admin-post như form thường (cùng nonce, phiên, quyền — máy chủ kiểm lại), rồi chỉ thay ô thẻ, lời báo, số trên tab và dòng
 * vừa quyết trong danh sách theo trang máy chủ trả về. Lỗi mạng hay trang lạ thì gửi form thường.
 * Không có JavaScript thì mọi thứ vẫn là liên kết và form thường.
 */
(() => {
  const o = document.querySelector('.phongdao-hv-phai');
  const wrap = document.querySelector('.phongdao-admin-viec');

  const hoanTac = () => {
    const hoan = document.querySelector('[data-hv-hoan]');
    if (!hoan) return;
    const het = () => {
      const nut = hoan.querySelector('button');
      if (nut) { nut.disabled = true; nut.textContent = 'Đã hết thời gian Hoàn tác nhanh'; }
    };
    const con = Number(hoan.dataset.han) * 1000 - Date.now();
    if (con <= 0) het(); else setTimeout(het, con);
  };

  // Máy tính: nút quyết ghi số phím tắt ("1 Đăng"); chỉ khi có bàn phím (màn rộng, chuột)
  const soPhim = () => {
    if (!o || !window.matchMedia('(min-width: 783px) and (pointer: fine)').matches) return;
    o.querySelectorAll('form button[name="quyet"]:not([disabled])').forEach((b, i) => {
      if (i < 3 && !b.querySelector('.phongdao-phim')) b.insertAdjacentHTML('afterbegin', `<kbd class="phongdao-phim" aria-hidden="true">${i + 1}</kbd> `);
    });
    const goiY = o.querySelector('[data-hv="goi-y"]');
    if (goiY) goiY.hidden = false;
  };

  hoanTac();
  soPhim();

  // Quyết không tải lại trang: gửi form, đọc trang máy chủ chuyển tới (thẻ kế), thay đúng các vùng đổi
  const thay = (moi, url) => {
    const mo = moi.querySelector('.phongdao-hv-phai');
    if (!mo || !o || !wrap) return false;
    o.innerHTML = mo.innerHTML;
    o.toggleAttribute('data-tu-chon', mo.hasAttribute('data-tu-chon'));
    // Lời báo (kết quả, Hoàn tác, Xem bài · Ẩn nếu nhầm) và số trên tab
    wrap.querySelectorAll(':scope > .notice, :scope > .phongdao-hv-bao').forEach(n => n.remove());
    const dau = wrap.querySelector(':scope > .phongdao-hv-tab');
    moi.querySelectorAll('.phongdao-admin-viec > .notice, .phongdao-admin-viec > .phongdao-hv-bao').forEach(n => dau?.before(n));
    const tabMoi = moi.querySelectorAll('.phongdao-admin-viec > .phongdao-hv-tab');
    wrap.querySelectorAll(':scope > .phongdao-hv-tab').forEach((t, i) => { if (tabMoi[i]) t.innerHTML = tabMoi[i].innerHTML; });
    const soMoi = moi.querySelector('.phongdao-hv-so');
    const so = wrap.querySelector('.phongdao-hv-so');
    if (so && soMoi) so.innerHTML = soMoi.innerHTML;
    // Danh sách: bỏ dòng thẻ không còn trong danh sách mới, tô dòng thẻ đang mở
    const con = new Set([...moi.querySelectorAll('.phongdao-hv-dong a.phongdao-hv-mo')].map(a => new URL(a.href).searchParams.get('viec')));
    document.querySelectorAll('.phongdao-hv-dong').forEach(li => {
      const id = new URL(li.querySelector('a.phongdao-hv-mo').href).searchParams.get('viec');
      if (!con.has(id)) li.remove();
    });
    const dang = new URL(url).searchParams.get('viec');
    document.querySelectorAll('.phongdao-hv-dong').forEach(li => {
      const a = li.querySelector('a.phongdao-hv-mo');
      const la = new URL(a.href).searchParams.get('viec') === dang;
      li.classList.toggle('dang-chon', la);
      if (la) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
    });
    const hv = document.querySelector('.phongdao-hv');
    if (hv) hv.classList.toggle('co-chon', !!dang);
    wrap.classList.toggle('co-the', !!dang);
    history.replaceState(null, '', url);
    // Trang đã đổi sang thẻ kế (không tải lại): xoá dấu "trang cũ" mà công cụ kiểm đầu-cuối đặt trước khi bấm
    window.__trangCu = undefined;
    hoanTac();
    soPhim();
    // Đưa con trỏ về đầu thẻ mới (người dùng bàn phím, trình đọc màn hình biết thẻ đã đổi)
    const tieuDe = o.querySelector('article.the-viec h3') || o;
    tieuDe.setAttribute('tabindex', '-1');
    tieuDe.focus({ preventScroll: false });
    return true;
  };

  document.addEventListener('submit', async e => {
    const f = e.target;
    if (!(f instanceof HTMLFormElement) || !o || !wrap || !window.fetch || !window.DOMParser) return;
    const loai = f.querySelector('input[name=action]')?.value;
    if (!['phongdao_quyet', 'phongdao_hoan_tac_quyet'].includes(loai) || !(o.contains(f) || f.closest('.phongdao-hv-bao'))) return;
    e.preventDefault();
    const du = new FormData(f);
    if (e.submitter?.name) du.set(e.submitter.name, e.submitter.value);
    f.querySelectorAll('button').forEach(b => { b.disabled = true; });
    // Form có ô tên "action" nên f.action là ô đó, không phải địa chỉ gửi: đọc thuộc tính
    const dich = f.getAttribute('action');
    try {
      const r = await fetch(dich, { method: 'POST', body: du, credentials: 'same-origin', redirect: 'follow' });
      const moi = new DOMParser().parseFromString(await r.text(), 'text/html');
      if (r.ok && r.url.includes('page=phongdao-hop-viec') && thay(moi, r.url)) return;
      if (r.ok && r.redirected) { location.assign(r.url); return; }
      throw new Error('không thay được thẻ');
    } catch (loi) {
      // Lỗi mạng: gửi form thường, giữ nút đã bấm
      if (e.submitter?.name) f.insertAdjacentHTML('beforeend', `<input type="hidden" name="${e.submitter.name}" value="${e.submitter.value}">`);
      f.submit();
    }
  });

  if (o && o.querySelector('article.the-viec')) {
    document.addEventListener('keydown', e => {
      if (e.defaultPrevented || e.altKey || e.ctrlKey || e.metaKey || e.shiftKey || e.isComposing || e.repeat) return;
      if (!o.offsetParent) return;
      const focus = document.activeElement;
      if (focus?.closest('input, textarea, select, button, a, summary, [contenteditable], [role="button"], [role="textbox"], dialog[open]')) return;
      const phim = e.key.toLowerCase();
      if (phim === 'j' || phim === 'k') {
        const lien = o.querySelector(`[data-hv="${phim === 'j' ? 'sau' : 'truoc'}"]`);
        if (lien) { e.preventDefault(); location.assign(lien.href); }
      } else if (/^[1-3]$/.test(phim)) {
        const nut = o.querySelectorAll('form button[name="quyet"]:not([disabled])')[Number(phim) - 1];
        if (nut) { e.preventDefault(); nut.click(); }
      }
    });
  }

  // Làm nhanh nhiều thẻ (thanh nổi): hỏi lại một câu trước khi làm. Phạm vi "mọi thẻ khớp bộ lọc" thì máy chủ mở màn xem
  // trước như Làm theo lô, nên không hỏi.
  document.addEventListener('click', e => {
    const nut = e.target.closest?.('button[data-hoi]');
    if (!nut) return;
    if (document.querySelector('input[name=pham_vi][value=loc]')?.checked) return;
    const n = document.querySelectorAll('input[type=checkbox][name="ids[]"]:checked').length;
    if (!window.confirm(nut.dataset.hoi.replace('%d', String(n)))) e.preventDefault();
  });
})();
