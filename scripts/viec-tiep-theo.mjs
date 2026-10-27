#!/usr/bin/env node
// Đề xuất task kỹ thuật READY tiếp theo (docs/03-quy-trinh-thuc-thi.md mục 3).
// Xét task của giai đoạn hiện tại VÀ các giai đoạn trước còn sót; không lấy task của giai đoạn sau.
// Nhóm chạy song song (giai_doan_song_song, ví dụ V2 — D-088) luôn được xét, xếp sau task của mốc.
import { docTrangThai, docBacklog, phuThuocCua, cauHoiTrong, TEN_GIAI_DOAN } from './lib/backlog.mjs';

const state = docTrangThai();
const thuTu = state.thu_tu_giai_doan ?? Object.keys(TEN_GIAI_DOAN);
const viTri = thuTu.indexOf(state.giai_doan);
if (viTri < 0 || !TEN_GIAI_DOAN[state.giai_doan]) {
  console.error(`Không nhận diện được giai đoạn: ${state.giai_doan}`);
  process.exit(1);
}
const duocXet = new Set([...thuTu.slice(0, viTri + 1), ...(state.giai_doan_song_song ?? [])].map(g => TEN_GIAI_DOAN[g]));

const { tasks } = docBacklog();
if (!tasks.length) {
  console.error('Không đọc được task nào trong docs/22-backlog-trien-khai.md — kiểm định dạng bảng tổng.');
  process.exit(1);
}
const byId = new Map(tasks.map(t => [t.id, t]));
const openQuestions = new Set(state.quyet_dinh_dang_mo ?? []);

function inferredReady(task) {
  if (!['READY', 'BLOCKED'].includes(task.trangThai)) return false;
  if (!/A/.test(task.ai)) return false;
  if (cauHoiTrong(task.chan).some(q => openQuestions.has(q))) return false;
  return phuThuocCua(task.phuThuoc).every(id => byId.get(id)?.trangThai === 'DONE');
}

const thuTuGiaiDoan = t => Object.values(TEN_GIAI_DOAN).indexOf(t.phase);
const ready = tasks
  .filter(t => duocXet.has(t.phase))
  .filter(inferredReady)
  .sort((a, b) => thuTuGiaiDoan(a) - thuTuGiaiDoan(b) || Number(a.id.slice(2)) - Number(b.id.slice(2)));

if (!ready.length) {
  const choNguoi = tasks.filter(t => duocXet.has(t.phase) && !['DONE', 'DEFERRED'].includes(t.trangThai) && !/A/.test(t.ai));
  console.log('KHÔNG CÓ CÔNG VIỆC KỸ THUẬT SẴN SÀNG');
  console.log('');
  console.log(`Giai đoạn hiện tại: ${state.giai_doan} (${TEN_GIAI_DOAN[state.giai_doan]})`);
  if (choNguoi.length) console.log(`Đang chờ người sáng lập: ${choNguoi.map(t => `${t.id} ${t.ten}`).join('; ')}`);
  console.log('Nếu mọi task của giai đoạn đã DONE: chuyển giai_doan sang mốc kế tiếp theo docs/22 mục 2 (riêng mở công khai là Human Gate H5).');
  process.exit(2);
}

const task = ready[0];
console.log('CÔNG VIỆC KỸ THUẬT TIẾP THEO');
console.log('');
console.log(`${task.id} · ${task.ten}`);
console.log(`Giai đoạn của task: ${task.phase} (hiện tại: ${TEN_GIAI_DOAN[state.giai_doan]})`);
console.log(`Ai làm: ${task.ai}`);
console.log(`Phụ thuộc: ${task.phuThuoc}`);
console.log(`Chặn: ${task.chan}`);
console.log(`Trạng thái trong bảng: ${task.trangThai}`);
console.log('Trạng thái suy ra: READY');
console.log('');
console.log('Lý do: dependency đã DONE và không có Q-xxx đang mở chặn task.');
console.log('Đọc chi tiết task và tiêu chí chấp nhận trong docs/22-backlog-trien-khai.md trước khi sửa.');
