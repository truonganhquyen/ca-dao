#!/usr/bin/env node
// Chạy công cụ kiểm tra trên từng bộ dữ liệu mẫu trong tests/fixtures/.
// - Thư mục có KY_VONG = PASS  -> phải thoát mã 0.
// - Thư mục có KY_VONG = Rxx   -> phải thoát mã 1 VÀ báo đúng mã lỗi Rxx.
// Mỗi quy tắc mới trong scripts/kiem-tra-du-lieu.mjs cần thêm một bộ ở đây.
import fs from 'node:fs';
import path from 'node:path';
import { spawnSync } from 'node:child_process';

const ROOT = 'tests/fixtures';
let failed = 0;
for (const name of fs.readdirSync(ROOT).sort()) {
  const dir = path.join(ROOT, name);
  const expect = fs.readFileSync(path.join(dir, 'KY_VONG'), 'utf8').trim();
  const r = spawnSync('node', ['scripts/kiem-tra-du-lieu.mjs', '--content', path.join(dir, 'content')], { encoding: 'utf8' });
  const out = r.stdout + r.stderr;
  const ok = expect === 'PASS' ? r.status === 0 : (r.status === 1 && out.includes(`LỖI      [${expect}]`));
  console.log(`${ok ? 'ĐẠT ' : 'HỎNG'}  ${name}  (kỳ vọng ${expect}, mã thoát ${r.status})`);
  if (!ok) { failed++; console.log(out.split('\n').map(l => '      ' + l).join('\n')); }
}
console.log(failed ? `\n${failed} bộ không đạt.` : '\nTất cả bộ kiểm thử đạt.');
process.exit(failed ? 1 : 0);
