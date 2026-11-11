/**
 * Hộp việc wp-admin kiểu hộp thư (T-188): phím tắt trên thẻ đang mở (J thẻ sau, K thẻ trước, 1/2/3 bấm nút quyết), nút
 * Hoàn tác tự tắt khi hết 10 giây, hộp xác nhận ngắn trước khi làm nhanh nhiều thẻ trên thanh nổi.
 * Không có JavaScript thì mọi thứ vẫn là liên kết và form thường (máy chủ kiểm lại quyền, phiên, hạn Hoàn tác).
 */
(() => {
  const hoan = document.querySelector('[data-hv-hoan]');
  if (hoan) {
    const het = () => {
      const nut = hoan.querySelector('button');
      if (nut) { nut.disabled = true; nut.textContent = 'Đã hết thời gian Hoàn tác nhanh'; }
    };
    const con = Number(hoan.dataset.han) * 1000 - Date.now();
    if (con <= 0) het(); else setTimeout(het, con);
  }

  // Phím tắt chỉ khi ô thẻ đang hiện (màn rộng, hoặc đã mở một thẻ ở màn hẹp) và người không đang thao tác điều khiển khác
  const o = document.querySelector('.phongdao-hv-phai');
  if (o && o.querySelector('article.the-viec')) {
    const goiY = o.querySelector('[data-hv="goi-y"]');
    if (goiY) goiY.hidden = false;
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
