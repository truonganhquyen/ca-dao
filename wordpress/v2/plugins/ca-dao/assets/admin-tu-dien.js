/**
 * Bổ sung → Gợi ý Từ điển (D-126): nút "Chọn cả nhóm: <nghĩa>" đặt mọi câu đang "Chưa chọn" vào nghĩa đó; câu đã chọn
 * (kể cả "Không nối") giữ nguyên. Đếm số câu đã chọn trên nút Lưu. Không có JavaScript thì nút ẩn, chọn từng câu vẫn được.
 */
(() => {
  const form = document.querySelector('.phongdao-td-form');
  if (!form) return;
  const dem = form.querySelector('[data-td-dem]');
  const nhom = () => [...form.querySelectorAll('.phongdao-td-chon')];
  const capNhat = () => {
    const so = nhom().filter(f => f.querySelector('input:checked')?.value !== '').length;
    if (dem) dem.textContent = so ? `${so} câu đã chọn` : '';
  };
  form.querySelectorAll('[data-ca-nhom]').forEach(nut => {
    nut.hidden = false;
    nut.addEventListener('click', () => {
      nhom().forEach(f => {
        if (f.querySelector('input:checked')?.value !== '') return;
        const o = f.querySelector(`input[data-nghia="${nut.dataset.caNhom}"]`);
        if (o) o.checked = true;
      });
      capNhat();
    });
  });
  form.addEventListener('change', capNhat);
})();
