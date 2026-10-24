#!/usr/bin/env node
// Chạy công cụ kiểm tra trên từng bộ dữ liệu mẫu trong tests/fixtures/.
// - KY_VONG = PASS  -> phải thoát mã 0, không có LỖI nào.
// - KY_VONG = Rxx   -> phải thoát mã 1 và LỖI chỉ mang đúng mã Rxx (không "đạt nhờ lỗi khác").
// - Tệp NHAC (tùy chọn, mỗi dòng một mã) -> các mã đó phải xuất hiện ở tầng NHẮC (kiểm việc nới từ LỖI xuống NHẮC).
// Mỗi quy tắc mới trong scripts/kiem-tra-du-lieu.mjs cần thêm một bộ ở đây.
import fs from 'node:fs';
import path from 'node:path';
import { spawnSync } from 'node:child_process';

const ROOT = 'tests/fixtures';
const maTrong = (out, tang) => new Set([...out.matchAll(new RegExp(`^${tang} +\\[(R\\d+)\\]`, 'gm'))].map(m => m[1]));
let failed = 0;
for (const name of fs.readdirSync(ROOT).sort()) {
  const dir = path.join(ROOT, name);
  const expect = fs.readFileSync(path.join(dir, 'KY_VONG'), 'utf8').trim();
  const nhacFile = path.join(dir, 'NHAC');
  const nhacKyVong = fs.existsSync(nhacFile) ? fs.readFileSync(nhacFile, 'utf8').split(/\s+/).filter(Boolean) : [];
  const r = spawnSync(process.execPath, ['scripts/kiem-tra-du-lieu.mjs', '--content', path.join(dir, 'content')], { encoding: 'utf8' });
  const out = r.stdout + r.stderr;
  const loi = maTrong(out, 'LỖI');
  const nhac = maTrong(out, 'NHẮC');
  const loiDung = expect === 'PASS' ? (r.status === 0 && loi.size === 0) : (r.status === 1 && loi.size === 1 && loi.has(expect));
  const nhacDung = nhacKyVong.every(m => nhac.has(m));
  const ok = loiDung && nhacDung;
  const ghiChu = nhacKyVong.length ? `, nhắc ${nhacKyVong.join('/')}` : '';
  console.log(`${ok ? 'ĐẠT ' : 'HỎNG'}  ${name}  (kỳ vọng ${expect}${ghiChu}; mã thoát ${r.status}, lỗi: ${[...loi].join(' ') || '—'})`);
  if (!ok) { failed++; console.log(out.split('\n').map(l => '      ' + l).join('\n')); }
}
console.log(failed ? `\n${failed} bộ không đạt.` : '\nTất cả bộ kiểm thử đạt.');
process.exit(failed ? 1 : 0);
