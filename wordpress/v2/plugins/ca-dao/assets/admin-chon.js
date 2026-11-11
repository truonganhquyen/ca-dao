/**
 * Ô chọn ở danh sách Tác phẩm / Hộp việc / Kho và ở trang xem trước lô (T-187).
 * - Khối "Làm theo lô" gọn: thu lại thành một dòng, đếm khi người đánh dấu bài/thẻ/câu. Màn có thanh nổi thì khối không tự
 *   mở (không đẩy danh sách xuống); nút "Làm theo lô…" của thanh mở khối. Màn không có thanh nổi thì khối tự mở.
 * - Thanh "Chọn tất cả trên trang này": ô ba trạng thái (chưa / một phần / hết), giữ Shift để chọn cả một dải.
 *   Ở danh sách, khi đã chọn cả trang mà bộ lọc còn nhiều hơn thì có nút chuyển Phạm vi sang "Mọi … khớp bộ lọc".
 *   Ở trang xem trước lô (ô "Áp dụng" tên giu[]), nút "Đánh dấu cả … ở trang khác" gửi lên máy chủ; nút chính đếm lại
 *   ngay số mục sẽ áp dụng.
 * - Thanh nổi ở đáy màn (T-188): khi đã đánh dấu mục thì hiện số đã chọn, nút làm tiếp (Kho: "Xem trước và đăng", "Đưa
 *   vào Hộp việc…"; Hộp việc: làm nhanh; Tác phẩm, Hộp việc: "Làm theo lô…" mở khối lô) và "Bỏ chọn". Khối lô đang mở và
 *   còn trong màn thì thanh ẩn, không che nút của khối.
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
  const noi = document.querySelector('.phongdao-thanh-noi');
  const donViNoi = noi?.dataset.donVi || donVi;
  // Khối lô đang mở và còn trong màn (trừ dải đáy thanh nổi chiếm) thì không cần thanh nổi
  const khoiThay = new Set();
  const khoiChe = () => [...khoiThay].some(d => d.open);

  const capNhat = () => {
    const ds = cacO();
    const n = ds.filter(o => o.checked).length;
    const loc = locO();
    const theoLoc = !!loc?.checked;
    for (const d of khoi) {
      const dem = d.querySelector('.phongdao-lo-dem');
      if (dem) dem.textContent = theoLoc ? (thanh ? ` — mọi ${tong} ${donVi} khớp bộ lọc` : ' — mọi mục khớp bộ lọc') : (n ? ` — đã chọn ${n}` : '');
      if (n && !d.open && !noi) d.open = true;
    }
    for (const b of nutChinh) b.textContent = b.dataset.mau.replace('%d', String((Number(b.dataset.ngoai) || 0) + n));
    if (noi) {
      const hien = (n > 0 || theoLoc) && !khoiChe();
      noi.hidden = !hien;
      document.body.classList.toggle('phongdao-co-thanh-noi', hien);
      noi.querySelector('.phongdao-thanh-noi-dem').textContent = theoLoc ? `Mọi ${tong} ${donViNoi} khớp bộ lọc` : `Đã chọn ${n} ${donViNoi}`;
    }
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

  if (noi && khoi.length && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver(ds => {
      for (const x of ds) { if (x.isIntersecting) khoiThay.add(x.target); else khoiThay.delete(x.target); }
      capNhat();
    }, { rootMargin: '0px 0px -96px 0px' });
    for (const d of khoi) io.observe(d);
  }
  for (const d of khoi) d.addEventListener('toggle', capNhat);

  // Thanh nổi: bỏ chọn hết; "Làm theo lô…" mở khối lô ở đầu danh sách và đưa con trỏ tới ô Thao tác
  noi?.querySelector('.phongdao-thanh-noi-bo')?.addEventListener('click', () => {
    for (const o of cacO()) o.checked = false;
    if (chonO()) chonO().checked = true;
    capNhat();
    (het && !thanh.hidden ? het : cacO()[0])?.focus();
  });
  for (const b of document.querySelectorAll('[data-mo-lo]')) {
    b.addEventListener('click', () => {
      const d = khoi[0];
      if (!d) return;
      d.open = true;
      d.scrollIntoView({ block: 'start' });
      (d.querySelector('select') || d.querySelector('summary'))?.focus({ preventScroll: true });
    });
  }

  document.addEventListener('change', e => { if (e.target.matches?.('input[name=pham_vi]')) capNhat(); });
  capNhat();
})();
