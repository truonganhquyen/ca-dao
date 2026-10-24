#!/usr/bin/env node
// Kiểm thử chính công cụ kiểm liên kết (scripts/kiem-lien-ket.mjs) trên các bản build mẫu trong tests/lien-ket/ (mỗi thư mục con đóng vai dist/).
// - dat/  -> phải thoát mã 0.
// - hong/ -> phải thoát mã 1 và báo đúng số lỗi ghi trong tệp SO_LOI.
import fs from 'node:fs';
import path from 'node:path';
import { spawnSync } from 'node:child_process';

const ROOT = 'tests/lien-ket';
let hong = 0;
for (const name of fs.readdirSync(ROOT).sort()) {
  const dir = path.join(ROOT, name);
  const soLoiFile = path.join(dir, 'SO_LOI');
  const soLoiKyVong = fs.existsSync(soLoiFile) ? Number(fs.readFileSync(soLoiFile, 'utf8').trim()) : 0;
  const r = spawnSync(process.execPath, ['scripts/kiem-lien-ket.mjs', dir], { encoding: 'utf8' });
  const out = r.stdout + r.stderr;
  const soLoi = (out.match(/^LỖI +\[liên kết\]/gm) ?? []).length;
  const ok = r.status === (soLoiKyVong ? 1 : 0) && soLoi === soLoiKyVong;
  console.log(`${ok ? 'ĐẠT ' : 'HỎNG'}  lien-ket/${name}  (kỳ vọng ${soLoiKyVong} lỗi; mã thoát ${r.status}, ${soLoi} lỗi)`);
  if (!ok) { hong++; console.log(out.split('\n').map(l => '      ' + l).join('\n')); }
}
console.log(hong ? `\n${hong} bộ kiểm liên kết không đạt.` : 'Tất cả bộ kiểm liên kết đạt.');
process.exit(hong ? 1 : 0);
