/**
 * Khối "Làm theo lô" gọn (T-187): thu lại thành một dòng, tự mở và đếm khi người đánh dấu bài/thẻ/câu ở danh sách.
 * Không có JavaScript thì khối vẫn mở được bằng tay (thẻ <details>).
 */
(() => {
  const khoi = [...document.querySelectorAll('details.phongdao-lo-gon')];
  if (!khoi.length) return;
  const O = 'input[type=checkbox][name="ids[]"]';
  const capNhat = () => {
    const n = document.querySelectorAll(O + ':checked').length;
    for (const d of khoi) {
      const dem = d.querySelector('.phongdao-lo-dem');
      if (dem) dem.textContent = n ? ` — đã chọn ${n}` : '';
      if (n && !d.open) d.open = true;
    }
  };
  document.addEventListener('change', e => { if (e.target.matches(O)) capNhat(); });
  capNhat();
})();
