import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const file = path.join(root, 'du-an', 'trang-thai.json');

if (!fs.existsSync(file)) {
  console.error('Không tìm thấy du-an/trang-thai.json');
  process.exit(1);
}

const state = JSON.parse(fs.readFileSync(file, 'utf8'));
const mo = state.quyet_dinh_dang_mo ?? [];
const chan = state.human_gate_dang_chan ?? [];

console.log('KHO CA DAO — TRẠNG THÁI DỰ ÁN');
console.log('');
console.log(`Giai đoạn: ${state.giai_doan} — ${state.ten_giai_doan}`);
console.log(`Trạng thái: ${state.trang_thai_giai_doan}`);
console.log(`Cổng tiếp theo: ${state.cong_chat_luong_tiep_theo}`);
console.log(`Công việc hiện tại: ${state.cong_viec_hien_tai ?? 'Không có'}`);
console.log(`Hồ sơ GĐ0: ${state.ho_so_gd0?.hoan_thanh ?? 0}/${state.ho_so_gd0?.muc_tieu ?? 10}`);
console.log(`Schema: ${state.schema?.da_dong_bang ? 'Đã đóng băng' : 'Chưa đóng băng'}`);
console.log('');
console.log(`Human Gate đang chặn: ${chan.length ? chan.join(', ') : 'Không có'}`);
console.log(`Quyết định đang mở: ${mo.length ? mo.join(', ') : 'Không có'}`);
console.log('');
console.log(`Kiểm tra gần nhất: ${state.kiem_tra_cuoi?.trang_thai ?? 'CHUA_CHAY'}`);
