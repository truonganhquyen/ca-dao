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

// Lọc danh sách mục phân loại dài (không dấu); tắt JavaScript thì vẫn chọn được, chỉ không có ô lọc.
const khongDau = s => s.normalize('NFD').replace(/\p{M}/gu, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase();
document.querySelectorAll('.phongdao-admin-sua [data-chi-js]').forEach(e => { e.hidden = false; });
document.addEventListener('input', e => {
  const o = e.target.closest('[data-loc-muc]');
  if (!o) return;
  const ds = document.getElementById(o.dataset.locMuc);
  const tim = khongDau(o.value.trim());
  ds?.querySelectorAll('label.choice').forEach(l => { l.hidden = tim !== '' && !khongDau(l.textContent).includes(tim); });
});
