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

// Điện thoại: các phần gập lại cho gọn (màn rộng mở sẵn phần đã có để thấy nội dung); phần vừa lưu vẫn mở
if (window.matchMedia('(max-width: 782px)').matches) {
  const vua = 'phan-' + (new URLSearchParams(location.search).get('phan') || '');
  document.querySelectorAll('.phongdao-sua-chinh details.phongdao-sua-phan[open]').forEach(d => { if (d.id !== vua) d.open = false; });
}

// "Lưu xong tự sang bài tiếp theo" (đến từ danh sách đã lọc): nhớ trên máy; khi gửi một phần, thêm ô sang=1 vào form.
const sang = document.getElementById('phongdao-sang-tiep');
if (sang) {
  try { sang.checked = localStorage.getItem('phongdao-sang-tiep') === '1'; } catch (e) { /* bộ nhớ bị chặn */ }
  sang.addEventListener('change', () => { try { localStorage.setItem('phongdao-sang-tiep', sang.checked ? '1' : '0'); } catch (e) { /* bộ nhớ bị chặn */ } });
  document.addEventListener('submit', e => {
    const f = e.target;
    if (!sang.checked || !(f instanceof HTMLFormElement) || !f.querySelector('input[name=bai_sau]')) return;
    const o = document.createElement('input');
    o.type = 'hidden';
    o.name = 'sang';
    o.value = '1';
    f.append(o);
  });
}

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
