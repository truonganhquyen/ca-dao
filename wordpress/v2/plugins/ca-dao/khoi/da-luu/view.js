/**
 * Trang Đã lưu: đọc danh sách trong bộ nhớ trình duyệt (khoá "cadao-da-luu", dạng {id, ten, url, loai, dong} như MVP —
 * bài lưu trước khi chuyển sang V2 vẫn còn), lọc theo thể loại, bỏ lưu từng bài. Không gửi gì về máy chủ; chữ lấy từ
 * bộ nhớ chỉ được đặt bằng textContent, đường dẫn chỉ nhận đường dẫn của chính trang này.
 */
(() => {
  const hop = document.querySelector('[data-da-luu]');
  if (!hop) return;
  const KHOA = 'cadao-da-luu';
  const $ = s => hop.querySelector(s);
  let bai;
  try {
    bai = JSON.parse(localStorage.getItem(KHOA) || '[]');
    if (!Array.isArray(bai)) bai = [];
  } catch {
    $('[data-chan]').hidden = false;
    return;
  }
  bai = bai.filter(x => { try { return x && x.id && new URL(x.url, location.href).origin === location.origin; } catch { return false; } });
  const loc = $('#da-luu-loc');
  const ghi = () => { try { localStorage.setItem(KHOA, JSON.stringify(bai)); return true; } catch { $('[data-chan]').hidden = false; return false; } };
  const tao = (the, lop, chu) => { const e = document.createElement(the); if (lop) e.className = lop; if (chu) e.textContent = chu; return e; };
  const dung = vaoSau => {
    const ds = $('[data-ds]');
    const hien = bai.filter(x => !loc.value || x.loai === loc.value);
    ds.textContent = '';
    $('[data-so]').textContent = bai.length ? `${hien.length} / ${bai.length} bài đã lưu trên máy này.` : '';
    $('[data-trong]').hidden = bai.length > 0;
    $('[data-loc]').hidden = bai.length < 2;
    hien.forEach(x => {
      const the = tao('article', 'card');
      if (x.loai) the.append(tao('span', 'tag', x.loai));
      const h = tao('h2'), a = tao('a', '', x.ten || x.dong || 'Bài đã lưu');
      a.href = x.url; h.append(a); the.append(h);
      if (x.dong) the.append(tao('p', 'serif', x.dong));
      const hang = tao('div', 'row between'), bo = tao('button', 'btn compact ghost', 'Bỏ lưu');
      bo.type = 'button';
      bo.setAttribute('aria-label', 'Bỏ lưu: ' + (x.ten || x.dong || ''));
      bo.addEventListener('click', () => {
        const i = bai.indexOf(x);
        bai.splice(i, 1);
        if (ghi()) dung(i);
      });
      hang.append(tao('span'), bo); the.append(hang); ds.append(the);
    });
    // Bỏ lưu xong: đưa tiêu điểm sang nút kế (hoặc dòng báo khi hết bài) — người dùng bàn phím không bị lạc
    if (typeof vaoSau === 'number') {
      const nut = ds.querySelectorAll('button');
      const toi = nut[Math.min(vaoSau, nut.length - 1)];
      if (toi) toi.focus(); else { const t = $('[data-trong] h2'); t.tabIndex = -1; t.focus(); }
    }
  };
  loc.addEventListener('change', () => dung());
  dung();
})();
