/**
 * Đo lường không cookie (chỉ nạp cho khách chưa đăng nhập): gửi một lượt đọc khi trang bài đã hiện trên màn hình 5 giây
 * (máy không chạy JavaScript thì không tính), mỗi máy một lần mỗi ngày; gửi các sự kiện doc.js phát trong trang
 * ("phongdao:su-kien": sao chép trích dẫn, chia sẻ, mở chú giải, mong có câu này). Chỉ gửi tên miền trang giới thiệu, không gửi đường dẫn.
 * Đánh dấu "đã gửi hôm nay" trong bộ nhớ trình duyệt (không cookie); máy chủ cũng chặn gửi lặp.
 */
(() => {
  // Cấu hình ở khối JSON (không chạy) để CSP không phải cho phép mã nội tuyến
  let ch = null;
  try { ch = JSON.parse(document.getElementById('phongdao-do-luong').textContent); } catch { /* không có khối cấu hình */ }
  if (!ch || !window.fetch) return;
  const hom = new Date().toISOString().slice(0, 10);
  const da = k => { try { const d = JSON.parse(localStorage.getItem('cadao-da-gui') || '{}'); return d.ngay === hom && d.ds.includes(k); } catch { return false; } };
  const nho = k => { try { let d = JSON.parse(localStorage.getItem('cadao-da-gui') || '{}'); if (d.ngay !== hom) d = { ngay: hom, ds: [] }; d.ds.push(k); localStorage.setItem('cadao-da-gui', JSON.stringify(d)); } catch { /* bị chặn: máy chủ vẫn chặn lặp */ } };
  const gui = (url, du, k) => {
    if (da(k)) return;
    nho(k);
    fetch(url, { method: 'POST', keepalive: true, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(du) }).catch(() => { /* bỏ lần này */ });
  };

  document.addEventListener('phongdao:su-kien', e => {
    const d = e.detail || {};
    gui(ch.suKien, d, 'sk:' + d.loai + ':' + (d.bai || '') + ':' + (d.kieu || d.muc || d.kho || ''));
  });

  if (!ch.luotDoc) return;
  let tu = '';
  try { const r = new URL(document.referrer); if (r.hostname !== location.hostname) tu = r.hostname; } catch { /* vào thẳng */ }
  let con = 5000, bat = 0, hen = 0;
  const chay = () => {
    if (document.visibilityState !== 'visible') { if (hen) { clearTimeout(hen); hen = 0; con -= Date.now() - bat; } return; }
    if (hen || con <= 0) return;
    bat = Date.now();
    hen = setTimeout(() => gui(ch.luotDoc, { tu }, 'doc:' + ch.bai), Math.max(0, con));
  };
  document.addEventListener('visibilitychange', chay);
  chay();
})();
