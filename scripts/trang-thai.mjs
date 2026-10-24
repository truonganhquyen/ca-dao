import fs from 'node:fs';
import path from 'node:path';
import { TEN_GIAI_DOAN } from './lib/backlog.mjs';

const root = process.cwd();
const file = path.join(root, 'du-an', 'trang-thai.json');

if (!fs.existsSync(file)) {
  console.error('Không tìm thấy du-an/trang-thai.json');
  process.exit(1);
}

const state = JSON.parse(fs.readFileSync(file, 'utf8'));
const mo = state.quyet_dinh_dang_mo ?? [];
const chan = state.human_gate_dang_chan ?? [];

// Đếm trực tiếp từ content/ để con số không bao giờ lệch với dữ liệu
const dir = path.join(root, 'content', 'tac-pham');
const theoTrangThai = {};
for (const f of fs.existsSync(dir) ? fs.readdirSync(dir).filter(f => f.endsWith('.yaml')) : []) {
  const tt = fs.readFileSync(path.join(dir, f), 'utf8').match(/^trang_thai:\s*(\w+)/m)?.[1] ?? 'khong_ro';
  theoTrangThai[tt] = (theoTrangThai[tt] || 0) + 1;
}

console.log('KHO CA DAO — TRẠNG THÁI DỰ ÁN');
console.log('');
console.log(`Hướng: ${state.huong ?? '—'}`);
console.log(`Giai đoạn: ${state.giai_doan} — ${TEN_GIAI_DOAN[state.giai_doan] ?? '?'}: ${state.ten_giai_doan}`);
console.log(`Trạng thái: ${state.trang_thai_giai_doan}`);
console.log(`Mốc tiếp theo: ${state.moc_tiep_theo ?? '—'}`);
console.log(`Công việc hiện tại: ${state.cong_viec_hien_tai ?? 'Không có'}`);
console.log(`Tác phẩm: ${JSON.stringify(theoTrangThai)} · mục tiêu MVP: ${state.noi_dung?.muc_tieu_mvp ?? '—'} bài công khai`);
console.log(`Schema: ${state.schema?.phien_ban ?? '—'}`);
console.log('');
console.log(`Human Gate đang chặn: ${chan.length ? chan.join(', ') : 'Không có'}`);
console.log(`Quyết định đang mở: ${mo.length ? mo.join(', ') : 'Không có'}`);
console.log('');
console.log(`Kiểm tra gần nhất: ${state.kiem_tra_cuoi?.trang_thai ?? 'CHUA_CHAY'}${state.kiem_tra_cuoi?.thoi_diem ? ` (${state.kiem_tra_cuoi.thoi_diem})` : ''}`);
