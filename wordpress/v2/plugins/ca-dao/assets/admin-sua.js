// Thêm mục vào biểu mẫu danh sách; việc lưu và kiểm tra vẫn ở máy chủ.
document.addEventListener('click', e => {
  const nut = e.target.closest('[data-action="them-muc"]');
  if (!nut) return;
  const form = nut.closest('form');
  const mau = form?.querySelector('template[data-mau]');
  const ds = form?.querySelector('[data-ds]');
  if (!mau || !ds) return;
  const id = 'm' + Date.now();
  ds.insertAdjacentHTML('beforeend', mau.innerHTML.replaceAll('__i__', id));
  ds.lastElementChild?.querySelector('textarea, input:not([type=hidden])')?.focus();
});
