// Bộ phân tích cho số đo nền (T-230, docs/57 mục 4). Hàm thuần, không gọi mạng; kiểm thử ở scripts/test-nhat-quan.mjs.
// Chỉ trả số đếm và đường dẫn; không lấy lời, chú thích hay dữ liệu cá nhân.

export const THE_LOAI = ['ca-dao', 'tuc-ngu', 'cau-do', 'dong-dao', 've'];

const so = s => Number(String(s).replace(/\./g, ''));

/** Bộ đếm "Đọc theo thể loại" ở trang chủ (số đếm của từng thể loại): { 'ca-dao': 11764, … }. */
export function demTrangChu(html) {
  const ra = {};
  for (const m of html.matchAll(/<a\b([^>]*)>([\s\S]*?)<\/a>/g)) {
    const lop = m[1].match(/class="tile tl g-([a-z-]+)"/);
    const n = m[2].match(/<span>([\d.]+) bài<\/span>/);
    if (lop && n) ra[lop[1]] = so(n[1]);
  }
  return ra;
}

/**
 * Đầu trang thể loại: { bai, giaiNghia }. `giaiNghia` là dòng "N bài đã có giải nghĩa" (mức ưu tiên đọc ≥ 2:
 * Ý nghĩa, giải nghĩa sách, nghĩa đen / bóng, hình ảnh, bối cảnh — mọi người làm); 0 khi trang không in dòng đó.
 */
export function dauTheLoai(html) {
  const m = html.match(/<p class="dau-tl-so">([\s\S]*?)<\/p>/);
  if (!m) return null;
  const bai = m[1].match(/<b>([\d.]+)<\/b> bài<\/span>/);
  const nghia = m[1].match(/<b>([\d.]+)<\/b> bài đã có giải nghĩa/);
  return { bai: bai ? so(bai[1]) : null, giaiNghia: nghia ? so(nghia[1]) : 0 };
}

/** Các <loc> của một tệp sơ đồ trang (tệp chỉ mục hoặc tệp danh sách URL). */
export function locs(xml) {
  return [...xml.matchAll(/<loc>([^<]+)<\/loc>/g)].map(m => m[1].trim());
}

/** Loại của tệp sơ đồ trang con: …/wp-sitemap-posts-tac_pham-1.xml → 'tac_pham'; …-taxonomies-chu_de-1.xml → 'chu_de'. */
export function loaiSoDo(url) {
  const m = url.match(/wp-sitemap-(?:posts|taxonomies|users)-([a-z_]+)-\d+\.xml$/);
  return m ? m[1] : 'khac';
}

/** Thể loại của đường dẫn bài: https://phongdao.com/ca-dao/x/ → 'ca-dao'; trang khác → null. */
export function theLoaiCuaBai(url, goc) {
  if (!url.startsWith(goc)) return null;
  const m = url.slice(goc.length).match(/^\/([a-z-]+)\/[^/?#]+\/$/);
  return m && THE_LOAI.includes(m[1]) ? m[1] : null;
}

/** Nội dung thẻ robots (chữ thường, gọn khoảng trắng); '' khi trang không có thẻ. */
export function robots(html) {
  const m = html.match(/<meta\s+name=['"]robots['"]\s+content=['"]([^'"]*)['"]/i);
  return m ? m[1].toLowerCase().replace(/\s+/g, ' ').trim() : '';
}

/**
 * Bảng nguồn ở chân khung lời của trang bài (TacPham::tu_lieu_dong) — chỉ đếm, không lấy chữ.
 * yNghia: Ý nghĩa (câu đố: Giải thích đáp án) do người biên soạn viết; yNghiaAi: có hỗ trợ của AI, đã duyệt;
 * ct* / cq*: số mục chú thích / chuyện quanh bài máy chép, người viết, theo Từ điển; doiChieu: lời đã đối chiếu sách.
 */
export function bangNguon(html) {
  const dd = ten => html.match(new RegExp(`<dt>${ten}</dt><dd>([\\s\\S]*?)</dd>`))?.[1] ?? null;
  const dem = (chu, mau) => Number(chu?.match(mau)?.[1] ?? 0);
  const y = dd('Ý nghĩa') ?? dd('Giải thích đáp án');
  const ct = dd('Chú thích');
  const cq = dd('Chuyện quanh bài') ?? dd('Nguồn gốc');
  const loi = dd('Lời');
  return {
    coBang: null !== loi,
    yNghia: null !== y && y.includes('Người biên soạn của thư viện viết'),
    yNghiaAi: null !== y && y.includes('có hỗ trợ của AI'),
    ctMay: dem(ct, /(\d+) mục chép từ nguồn tham khảo/),
    ctViet: dem(ct, /(\d+) mục người biên soạn viết/),
    ctTuDien: dem(ct, /(\d+) mục theo <a[^>]*>Từ điển/),
    cqMay: dem(cq, /(\d+) mục chép từ nguồn tham khảo/),
    cqViet: dem(cq, /(\d+) mục người biên soạn viết/),
    doiChieu: null !== loi && loi.startsWith('Đã đối chiếu'),
  };
}

/** Đường dẫn bài của các thẻ trong một trang danh sách (/kho/page/N/): nút "Đọc" của mỗi thẻ. */
export function theTrongDanhSach(html) {
  return [...html.matchAll(/<article class="card the-loi[^"]*">[\s\S]*?<a class="btn compact ghost" href="([^"]+)"/g)].map(m => m[1]);
}

/** Số trang lớn nhất ở thanh phân trang (aria-label="Trang N"); 1 khi không có. */
export function soTrang(html) {
  return Math.max(1, ...[...html.matchAll(/aria-label="Trang (\d+)"/g)].map(m => Number(m[1])));
}

/** Giờ Việt Nam dạng ISO có múi giờ: 2026-11-21T17:05:12+07:00. */
export function gioVN(ms = Date.now()) {
  return new Date(ms + 7 * 3600e3).toISOString().replace(/\.\d{3}Z$/, '+07:00');
}

/** Số kiểu Việt: 16453 → "16.453" (không phụ thuộc dữ liệu ngôn ngữ của Node). */
export function soVN(n) {
  return String(Math.trunc(Number(n) || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
