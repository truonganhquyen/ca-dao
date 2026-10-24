#!/usr/bin/env node
// Kiểm thử các hàm phân tích của kiem-nhat-quan (scripts/lib/backlog.mjs) — một phần của `npm run kiem-tra`.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import { maQuyetDinhTrung } from './lib/backlog.mjs';

const bang = ids => ids.map(id => `| ${id} | Quyết định | Lý do | Nguồn |`).join('\n');
assert.deepEqual(maQuyetDinhTrung(bang(['D-001', 'D-002', 'D-050'])), [], 'không trùng thì không báo');
assert.deepEqual(maQuyetDinhTrung(bang(['D-050', 'D-051', 'D-050'])), ['D-050'], 'trùng một số');
assert.deepEqual(maQuyetDinhTrung(bang(['D-050', 'D-050', 'D-051', 'D-051', 'D-052'])), ['D-050', 'D-051'], 'trùng nhiều số');
assert.deepEqual(maQuyetDinhTrung('Xem D-050 và D-050 trong câu văn; | D-050 | chỉ ở đầu hàng mới tính'), [], 'chỉ tính số ở đầu hàng bảng');
// Dữ liệu thật hiện tại phải sạch
assert.deepEqual(maQuyetDinhTrung(fs.readFileSync('docs/01-nhat-ky-quyet-dinh.md', 'utf8')), [], 'docs/01 hiện đang có số D trùng');
console.log('Kiểm thử kiểm nhất quán: đạt.');
