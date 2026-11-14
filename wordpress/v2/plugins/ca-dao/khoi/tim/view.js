// Tìm khi gõ (trang /tim/): gọi REST phongdao/v1/tim, thay trạng thái và kết quả bằng HTML máy chủ dựng
// (cùng HTML khi tắt JavaScript). Không gửi gì ra ngoài site.
(() => {
  'use strict';
  const f = document.querySelector('form[data-tim]');
  if (!f || !window.fetch || !f.dataset.api) return;
  const o = f.querySelector('[name=q]');
  let hen, dang;
  const dung = () => { clearTimeout(hen); if (dang) dang.abort(); };
  const thay = html => {
    const mo = document.querySelector('.tim-loc-mo')?.open;
    const t = document.createElement('div');
    t.innerHTML = html;
    for (const id of ['search-status', 'search-results']) {
      const cu = document.getElementById(id), moi = t.querySelector('#' + id);
      if (cu && moi) cu.innerHTML = moi.innerHTML;
    }
    if (mo) { const d = document.querySelector('.tim-loc-mo'); if (d) d.open = true; }
    document.querySelectorAll('[data-action=mong-co-cau][hidden]').forEach(b => { b.hidden = false; });
    if (window.PDfit) window.PDfit();
  };
  f.addEventListener('submit', dung);
  document.addEventListener('change', e => { if (e.target.closest('[data-tim-loc]')) dung(); });
  o.addEventListener('input', () => {
    dung();
    hen = setTimeout(async () => {
      const q = o.value.trim();
      if (dang) dang.abort();
      const yc = new AbortController();
      dang = yc;
      try {
        const params = new URLSearchParams(new FormData(f));
        params.set('q', q);
        params.set('dang_go', '1');
        const r = await fetch(f.dataset.api + (f.dataset.api.includes('?') ? '&' : '?') + params, { signal: yc.signal });
        if (!r.ok) return;
        const html = (await r.json()).html;
        if (!yc.signal.aborted && dang === yc) thay(html);
        // Form GET xác nhận tìm trọn chữ; gợi ý gõ dở không thay đường dẫn đang chia sẻ.
      } catch (e) { /* mất mạng: giữ kết quả cũ, form vẫn gửi được */ }
    }, 300);
  });
})();
