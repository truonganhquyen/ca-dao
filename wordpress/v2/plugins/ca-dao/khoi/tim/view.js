// Tìm khi gõ (trang /tim/): gọi REST phongdao/v1/tim, thay trạng thái và kết quả bằng HTML máy chủ dựng
// (cùng HTML khi tắt JavaScript). Không gửi gì ra ngoài site.
(() => {
  'use strict';
  const f = document.querySelector('form[data-tim]');
  if (!f || !window.fetch || !f.dataset.api) return;
  const o = f.querySelector('[name=q]');
  let hen, dang;
  const thay = html => {
    const t = document.createElement('div');
    t.innerHTML = html;
    for (const id of ['search-status', 'search-results']) {
      const cu = document.getElementById(id), moi = t.querySelector('#' + id);
      if (cu && moi) cu.innerHTML = moi.innerHTML;
    }
    if (window.PDfit) window.PDfit();
  };
  o.addEventListener('input', () => {
    clearTimeout(hen);
    hen = setTimeout(async () => {
      const q = o.value.trim();
      if (dang) dang.abort();
      dang = new AbortController();
      try {
        const r = await fetch(f.dataset.api + (f.dataset.api.includes('?') ? '&' : '?') + 'q=' + encodeURIComponent(q), { signal: dang.signal });
        if (!r.ok) return;
        thay((await r.json()).html);
        history.replaceState(null, '', q ? '?q=' + encodeURIComponent(q) : location.pathname);
      } catch (e) { /* mất mạng: giữ kết quả cũ, form vẫn gửi được */ }
    }, 300);
  });
})();
