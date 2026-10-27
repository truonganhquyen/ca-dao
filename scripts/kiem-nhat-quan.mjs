#!/usr/bin/env node
// Kiểm nhất quán giữa các nguồn trạng thái (một phần của `npm run kiem-tra`, D-034):
// backlog đọc được và hợp lệ; trang-thai.json khớp bảng câu hỏi mở trong docs/01.
// Lỗi ở đây nghĩa là tài liệu điều phối đang lệch nhau — agent sẽ chọn sai việc.
import fs from 'node:fs';
import { docTrangThai, docBacklog, phuThuocCua, cauHoiTrong, maQuyetDinhTrung, TRANG_THAI_TASK, TEN_GIAI_DOAN } from './lib/backlog.mjs';

const loi = [];
const state = docTrangThai();
const { tasks, giaiDoanLa } = docBacklog();
const byId = new Map(tasks.map(t => [t.id, t]));

if (!tasks.length) loi.push('docs/22: không đọc được task nào trong bảng tổng');
for (const g of giaiDoanLa) loi.push(`docs/22: tiêu đề giai đoạn lạ "${g}" (chỉ dùng: ${Object.values(TEN_GIAI_DOAN).join(', ')})`);
if (!TEN_GIAI_DOAN[state.giai_doan]) loi.push(`trang-thai.json: giai_doan "${state.giai_doan}" không hợp lệ`);
for (const g of state.giai_doan_song_song ?? []) {
  if (!TEN_GIAI_DOAN[g]) loi.push(`trang-thai.json: giai_doan_song_song "${g}" không hợp lệ`);
  else if ((state.thu_tu_giai_doan ?? []).includes(g)) loi.push(`trang-thai.json: "${g}" vừa là mốc tuần tự vừa chạy song song`);
}
for (const t of tasks) {
  if (!TRANG_THAI_TASK.has(t.trangThai)) loi.push(`${t.id}: trạng thái "${t.trangThai}" không thuộc ${[...TRANG_THAI_TASK].join('/')}`);
  if (!t.phase) loi.push(`${t.id}: nằm ngoài mọi tiêu đề giai đoạn`);
  for (const d of phuThuocCua(t.phuThuoc)) {
    if (!byId.has(d)) loi.push(`${t.id}: phụ thuộc ${d} không có trong backlog`);
    else if (t.trangThai === 'DONE' && byId.get(d).trangThai !== 'DONE') loi.push(`${t.id} đã DONE nhưng phụ thuộc ${d} chưa DONE`);
  }
}
if (byId.size !== tasks.length) loi.push('docs/22: có mã task bị lặp');

// Số quyết định không được trùng: hai PR cùng lấy số D kế tiếp rồi gộp sẽ trùng (docs/03 mục 7.1)
for (const ma of maQuyetDinhTrung(fs.readFileSync('docs/01-nhat-ky-quyet-dinh.md', 'utf8'))) loi.push(`docs/01: số quyết định ${ma} xuất hiện nhiều lần — đánh lại số cho quyết định mới hơn (lớn nhất trên main và các PR đang mở + 1)`);

// Câu hỏi mở: trang-thai.json phải khớp bảng "Câu hỏi còn mở" của docs/01
const nhatKy = fs.readFileSync('docs/01-nhat-ky-quyet-dinh.md', 'utf8');
// Chỉ bảng ngay dưới tiêu đề mục 1, dừng ở tiêu đề kế tiếp (kể cả "### Câu hỏi đã chốt")
const phanMo = nhatKy.split(/^## 1\. Câu hỏi còn mở/m)[1]?.split(/^#{2,3} /m)[0] ?? '';
const moTrongNhatKy = new Set([...phanMo.matchAll(/^\| (Q-\d+) \|/gm)].map(m => m[1]));
const moTrongJson = new Set(state.quyet_dinh_dang_mo ?? []);
for (const q of moTrongNhatKy) if (!moTrongJson.has(q)) loi.push(`${q} còn mở trong docs/01 nhưng thiếu trong trang-thai.json`);
for (const q of moTrongJson) if (!moTrongNhatKy.has(q)) loi.push(`${q} có trong trang-thai.json nhưng không còn mở trong docs/01`);
for (const t of tasks) for (const q of cauHoiTrong(t.chan)) if (!moTrongNhatKy.has(q) && !nhatKy.includes(q)) loi.push(`${t.id}: bị chặn bởi ${q} không có trong docs/01`);

for (const l of loi) console.log(`LỖI      [nhất quán] ${l}`);
console.log(`Kiểm nhất quán: ${tasks.length} task, ${moTrongJson.size} câu hỏi mở — ${loi.length} lỗi.`);
process.exit(loi.length ? 1 : 0);
