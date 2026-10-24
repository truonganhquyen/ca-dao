#!/usr/bin/env node
// Kiểm trang đã build, không cần trình duyệt (T-040; docs/23 mục 1, 5) — một phần của `npm run kiem-tra`:
//   1. Không lộ dấu nháp: CAN_BO_SUNG, NHAP_AI (docs/10 mục 6).
//   2. Từ cấm trong giao diện (AGENTS.md KHÔNG BAO GIỜ mục 10, docs/20 mục 10) — chỉ NHẮC, không chặn (D-054:
//      không chặn đăng vì lý do biên tập; từ cấm có thể nằm trong nội dung do người biên soạn viết).
//   3. Ngân sách JavaScript tải ban đầu < 50 KB mỗi trang (docs/20 mục 12).
//   4. Trang mẫu giao diện (D-050, khi có): đúng các tiêu chí chấp nhận của T-040.
// Dùng:  node scripts/kiem-trang.mjs [thư-mục-build, mặc định dist]
import fs from 'node:fs';
import path from 'node:path';

const GOC = process.argv[2] ?? 'dist';
const NGAN_SACH_JS = 50 * 1024;
const TU_CAM = ['bản gốc', 'bản đúng', 'bản chuẩn', 'bản sai', 'AI giải thích', 'câu hay nhất'];

const tatCaTep = dir => fs.readdirSync(dir, { withFileTypes: true })
  .flatMap(e => e.isDirectory() ? tatCaTep(path.join(dir, e.name)) : [path.join(dir, e.name)]);
const trang = tatCaTep(GOC).filter(f => f.endsWith('.html'));
const ten = f => '/' + path.relative(GOC, f).split(path.sep).join('/');
const loi = [];
const nhacNho = [];

/** Chữ người đọc thấy (và nghe qua trình đọc màn hình): nội dung + title, alt, aria-label, mô tả trang. */
function chuHienThi(html) {
  const thuocTinh = [...html.matchAll(/\s(?:alt|title|aria-label|content)="([^"]*)"/g)].map(m => m[1]);
  const than = html.replace(/<script[\s\S]*?<\/script>/g, ' ').replace(/<style[\s\S]*?<\/style>/g, ' ')
    .replace(/<[^>]+>/g, ' ').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ');
  return `${than} ${thuocTinh.join(' ')}`;
}

for (const f of trang) {
  const html = fs.readFileSync(f, 'utf8');
  const chu = chuHienThi(html);
  for (const dau of ['CAN_BO_SUNG', 'NHAP_AI']) if (chu.includes(dau)) loi.push(`${ten(f)}: lộ dấu nháp "${dau}" ra trang`);
  for (const tu of TU_CAM) if (chu.toLocaleLowerCase('vi').includes(tu.toLocaleLowerCase('vi'))) nhacNho.push(`${ten(f)}: dùng từ cấm "${tu}"`);
  if (/\bmức [ABCD]\b/.test(chu)) nhacNho.push(`${ten(f)}: hiện "mức A/B/C/D" — chỉ dùng tên nhãn bằng lời thường`);

  // JavaScript tải ban đầu: script nội tuyến + tệp script cùng site (không tính phông)
  let js = 0;
  for (const m of html.matchAll(/<script\b([^>]*)>([\s\S]*?)<\/script>/g)) {
    if (/type="application\/(ld\+)?json"/.test(m[1])) continue;
    const src = m[1].match(/\ssrc="([^"]+)"/)?.[1];
    if (src?.startsWith('/')) js += fs.statSync(path.join(GOC, src.split('?')[0])).size;
    else js += Buffer.byteLength(m[2]);
  }
  if (js > NGAN_SACH_JS) loi.push(`${ten(f)}: JavaScript tải ban đầu ${(js / 1024).toFixed(1)} KB, vượt ngân sách 50 KB`);
}

// ---------- Trang mẫu giao diện (D-050) ----------
const MAU = {
  '/ca-dao/mau-nhan-d/index.html': {
    co: ['Lời phổ biến – chưa đối chiếu', 'Chưa đối chiếu với sách in', 'Lời tham khảo từ', 'Bạn có sách? Gửi số trang',
      'Hiểu trong 10 giây', 'Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt.', 'Cộng đồng ghi nhận',
      'Dòng mẫu của một dị bản do cộng đồng gửi', 'class="chu-giai-cuoi', 'id="o-chu-giai"', 'Trang mẫu giao diện'],
    khong: ['Dị bản này không được hiện trên trang'], // dị bản D khác bản tham chiếu ở lại nội bộ
    noindex: true, soChuGiai: 2,
  },
  '/ca-dao/mau-nhan-a/index.html': {
    co: ['Đã đối chiếu tư liệu', 'Đối chiếu theo:', 'tr. 123', 'Có 1 dị bản', 'Đang có tranh luận', 'Xem tiếp 6 dòng', 'Âm luật', 'Bối cảnh diễn xướng'],
    khong: ['Chưa đối chiếu với sách in'],
    noindex: true, soChuGiai: 1,
  },
  '/tuc-ngu/mau-tuc-ngu/index.html': {
    co: ['Nghi không phải dân gian', 'căn cứ mẫu cho cờ nghi gán nhầm', 'Lời theo <cite>Sách mẫu để xem giao diện</cite>.', 'Lưu ý về nội dung', 'Nghĩa đen.', 'Bài đang được bổ sung'],
    khong: ['Nghĩa bóng.', 'Hiểu trong 10 giây'], // nghia_bong còn CAN_BO_SUNG → ẩn; không có nghĩa ngắn → ẩn khối
    noindex: true, soChuGiai: 0,
  },
};
let soMau = 0;
for (const [duong, k] of Object.entries(MAU)) {
  const f = path.join(GOC, duong);
  if (!fs.existsSync(f)) continue;
  soMau++;
  const html = fs.readFileSync(f, 'utf8');
  for (const s of k.co) if (!html.includes(s)) loi.push(`${duong}: thiếu "${s}"`);
  for (const s of k.khong) if (html.includes(s)) loi.push(`${duong}: không được có "${s}"`);
  if (k.noindex !== /<meta name="robots" content="noindex"/.test(html)) loi.push(`${duong}: noindex sai`);
  const n = (html.match(/<li id="loi-cg-\d+"/g) ?? []).length;
  if (n !== k.soChuGiai) loi.push(`${duong}: có ${n} chú giải cuối khối lời, kỳ vọng ${k.soChuGiai}`);
}

for (const l of nhacNho) console.log(`NHẮC     [trang] ${l}`);
for (const l of loi) console.log(`LỖI      [trang] ${l}`);
console.log(`Kiểm trang: ${trang.length} trang${soMau ? `, ${soMau} trang mẫu giao diện` : ''} — ${loi.length} lỗi.`);
process.exit(loi.length ? 1 : 0);
