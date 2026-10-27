#!/usr/bin/env node
// Kiểm thử các hàm phân tích của kiem-nhat-quan (scripts/lib/backlog.mjs) — một phần của `npm run kiem-tra`.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import { maQuyetDinhTrung } from './lib/backlog.mjs';
import { phienBanRepo, phienBanTrang, chenhLech, baiDauTien } from './lib/phien-ban.mjs';

const bang = ids => ids.map(id => `| ${id} | Quyết định | Lý do | Nguồn |`).join('\n');
assert.deepEqual(maQuyetDinhTrung(bang(['D-001', 'D-002', 'D-050'])), [], 'không trùng thì không báo');
assert.deepEqual(maQuyetDinhTrung(bang(['D-050', 'D-051', 'D-050'])), ['D-050'], 'trùng một số');
assert.deepEqual(maQuyetDinhTrung(bang(['D-050', 'D-050', 'D-051', 'D-051', 'D-052'])), ['D-050', 'D-051'], 'trùng nhiều số');
assert.deepEqual(maQuyetDinhTrung('Xem D-050 và D-050 trong câu văn; | D-050 | chỉ ở đầu hàng mới tính'), [], 'chỉ tính số ở đầu hàng bảng');
// Dữ liệu thật hiện tại phải sạch
assert.deepEqual(maQuyetDinhTrung(fs.readFileSync('docs/01-nhat-ky-quyet-dinh.md', 'utf8')), [], 'docs/01 hiện đang có số D trùng');
// Kiểm "site thật đúng bản" (T-129)
const mau = '<link href="https://phongdao.com/wp-content/themes/ca-dao/style.css?ver=0.19.1"><script src="https://phongdao.com/wp-content/plugins/ca-dao/assets/ca-dao.js?ver=0.33.1"></script>';
assert.deepEqual(phienBanTrang(mau), { plugin: '0.33.1', theme: '0.19.1' }, 'đọc phiên bản từ ?ver= của tệp plugin, theme');
assert.deepEqual(phienBanTrang('<p>không có tệp</p>'), { plugin: null, theme: null }, 'trang không có tệp thì không đoán số');
assert.deepEqual(chenhLech({ plugin: '0.33.1', theme: '0.19.1' }, { plugin: '0.33.1', theme: '0.19.1' }), [], 'khớp thì không báo');
assert.deepEqual(chenhLech({ plugin: '0.33.1', theme: '0.19.1' }, { plugin: '0.28.0', theme: '0.19.1' }), ['plugin: repo 0.33.1, site 0.28.0'], 'plugin cũ thì báo (lần kẹt 0.28.0 ngày 26–27/10)');
assert.equal(chenhLech({ plugin: '0.33.1', theme: '0.19.1' }, { plugin: null, theme: null }).length, 2, 'không đọc được số trên site cũng là lệch');
assert.equal(baiDauTien('<a href="https://phongdao.com/tu-dien/">x</a><a href="https://phongdao.com/ca-dao/cong-cha-nhu-nui-thai-son-2/">y</a>', 'https://phongdao.com'), 'https://phongdao.com/ca-dao/cong-cha-nhu-nui-thai-son-2/', 'lấy trang bài đầu tiên, bỏ qua trang khác');
const rp = phienBanRepo();
assert.match(rp.plugin ?? '', /^\d+\.\d+\.\d+$/, 'đọc được CADAO_PHIEN_BAN trong repo');
assert.match(rp.theme ?? '', /^\d+\.\d+\.\d+$/, 'đọc được Version: của theme trong repo');
assert.equal(rp.plugin, fs.readFileSync('wordpress/plugins/ca-dao/ca-dao.php', 'utf8').match(/\* Version:\s*([0-9.]+)/)[1], 'CADAO_PHIEN_BAN khớp dòng Version: của plugin');
console.log('Kiểm thử kiểm nhất quán: đạt.');
