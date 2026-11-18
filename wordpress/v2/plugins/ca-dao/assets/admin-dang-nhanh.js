// Bài mới → Đăng nhanh (D-121): phím tắt cho người làm nhiều thẻ — Ctrl/⌘ + Enter: Đăng · Alt + S: Để sau · Alt + →: thẻ sau.
// Chỉ bấm đúng nút đang có trên thẻ (câu chờ quyết trùng không có nút Đăng thì phím Đăng không làm gì).
(() => {
  'use strict';
  const the = document.querySelector('.phongdao-dang-nhanh');
  if (!the) return;
  the.querySelectorAll('[data-chi-js]').forEach(e => { e.hidden = false; });
  const bam = ma => {
    const b = the.querySelector(`[data-phim="${ma}"]`);
    if (!b) return false;
    if (b.form && b.matches('button')) b.form.requestSubmit(b); else b.click();
    return true;
  };
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && !e.altKey && e.key === 'Enter') { if (bam('dang')) e.preventDefault(); }
    else if (e.altKey && !e.ctrlKey && !e.metaKey && e.key.toLowerCase() === 's') { if (bam('de-sau')) e.preventDefault(); }
    else if (e.altKey && !e.ctrlKey && !e.metaKey && e.key === 'ArrowRight') { if (bam('sau')) e.preventDefault(); }
  });
})();
