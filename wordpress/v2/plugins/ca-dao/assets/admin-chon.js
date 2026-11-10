/**
 * Ô chọn ở danh sách Tác phẩm / Hộp việc / Kho và ở trang xem trước lô (T-187).
 * - Khối "Làm theo lô" gọn: thu lại thành một dòng, tự mở và đếm khi người đánh dấu bài/thẻ/câu.
 * - Thanh "Chọn tất cả trên trang này": ô ba trạng thái (chưa / một phần / hết), giữ Shift để chọn cả một dải.
 *   Ở danh sách, khi đã chọn cả trang mà bộ lọc còn nhiều hơn thì có nút chuyển Phạm vi sang "Mọi … khớp bộ lọc".
 *   Ở trang xem trước lô (ô "Áp dụng" tên giu[]), nút "Đánh dấu cả … ở trang khác" gửi lên máy chủ; nút chính đếm lại
 *   ngay số mục sẽ áp dụng.
 * Không có JavaScript thì thanh chọn không hiện; khối lô vẫn mở được bằng tay (thẻ <details>), từng ô vẫn đánh dấu được.
 */
(() => {
  const thanh = document.querySelector('.phongdao-chon-het');
  const O = `input[type=checkbox][name="${thanh?.dataset.o || 'ids[]'}"]`;
  const khoi = [...document.querySelectorAll('details.phongdao-lo-gon')];
  const nutChinh = [...document.querySelectorAll('button[data-mau]')];
  if (!khoi.length && !thanh) return;
  const cacO = () => [...document.querySelectorAll(O)].filter(o => !o.disabled);
  const locO = () => document.querySelector('input[name=pham_vi][value=loc]:not(:disabled)');
  const chonO = () => document.querySelector('input[name=pham_vi][value=chon]');
  const tong = thanh ? Number(thanh.dataset.tong) || 0 : 0;
  const donVi = thanh ? thanh.dataset.donVi || 'mục' : 'mục';
  const het = thanh?.querySelector('.phongdao-chon-het-o');
  const nutLoc = thanh?.querySelector('.phongdao-chon-loc');
  const guiMayChu = !!nutLoc && 'gui' in nutLoc.dataset;

  const capNhat = () => {
    const ds = cacO();
    const n = ds.filter(o => o.checked).length;
    const loc = locO();
    const theoLoc = !!loc?.checked;
    for (const d of khoi) {
      const dem = d.querySelector('.phongdao-lo-dem');
      if (dem) dem.textContent = theoLoc ? (thanh ? ` — mọi ${tong} ${donVi} khớp bộ lọc` : ' — mọi mục khớp bộ lọc') : (n ? ` — đã chọn ${n}` : '');
      if (n && !d.open) d.open = true;
    }
    for (const b of nutChinh) b.textContent = b.dataset.mau.replace('%d', String((Number(b.dataset.ngoai) || 0) + n));
    if (!thanh) return;
    if (!ds.length) { thanh.hidden = true; return; }
    thanh.hidden = false;
    het.checked = n > 0 && n === ds.length;
    het.indeterminate = n > 0 && n < ds.length;
    thanh.querySelector('.phongdao-chon-het-chu').textContent = `Chọn tất cả ${ds.length} ${donVi} trên trang này`;
    if (nutLoc && guiMayChu) {
      nutLoc.hidden = !het.checked;
    } else if (nutLoc) {
      nutLoc.textContent = `Chọn mọi ${tong} ${donVi} khớp bộ lọc`;
      nutLoc.hidden = !(loc && !theoLoc && het.checked && tong > ds.length);
    }
  };

  // Giữ Shift khi bấm: mọi ô giữa ô bấm trước và ô vừa bấm theo trạng thái ô vừa bấm
  let truoc = null;
  document.addEventListener('click', e => {
    const o = e.target;
    if (!(o instanceof HTMLInputElement) || !o.matches(O)) return;
    if (e.shiftKey && truoc && truoc !== o && truoc.isConnected) {
      const ds = cacO();
      const a = ds.indexOf(truoc);
      const b = ds.indexOf(o);
      if (a >= 0 && b >= 0) for (const x of ds.slice(Math.min(a, b), Math.max(a, b) + 1)) x.checked = o.checked;
    }
    truoc = o;
    // Đánh dấu lại từng ô nghĩa là chọn tay: Phạm vi về "đã chọn trên trang này"
    if (locO()?.checked && chonO()) chonO().checked = true;
    capNhat();
  });

  het?.addEventListener('change', () => {
    for (const o of cacO()) o.checked = het.checked;
    if (!het.checked && chonO()) chonO().checked = true;
    capNhat();
  });

  if (nutLoc && !guiMayChu) {
    nutLoc.addEventListener('click', () => {
      const loc = locO();
      if (!loc) return;
      loc.checked = true;
      const d = loc.closest('details');
      if (d) d.open = true;
      capNhat();
      loc.focus();
    });
  }

  document.addEventListener('change', e => { if (e.target.matches?.('input[name=pham_vi]')) capNhat(); });
  capNhat();
})();
