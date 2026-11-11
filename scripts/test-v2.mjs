#!/usr/bin/env node
// Kiểm thử V2 (docs/26 mục 13): `npm run test:v2`.
//  1. PHP thuần: wordpress/v2/tests/don-vi/chay.php (không cần WordPress, vài giây) — cần lệnh `php` ≥ 8.3 có intl.
//  2. Playground: plugin + theme V2 bật trong WordPress thật (wordpress/v2/tests/kiem-thu.php) — cần Node ≥ 24.18 và mạng.
// Thiếu công cụ tại máy thì in NHẮC và bỏ qua phần đó; trên CI thì thiếu là lỗi.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawnSync } from 'node:child_process';

const CI = Boolean(process.env.CI);
const goc = path.resolve('wordpress', 'v2');
let hong = false;

// ---------- 0. Giao diện theme khớp bộ thiết kế design/v2 ----------
const dongBo = spawnSync(process.execPath, [path.resolve('scripts', 'dong-bo-giao-dien-v2.mjs'), '--kiem'], { encoding: 'utf8' });
process.stdout.write(dongBo.stdout + dongBo.stderr);
if (dongBo.status !== 0) hong = true;

// ---------- 1. PHP thuần ----------
const php = process.env.PHP_BIN || 'php';
const coPhp = spawnSync(php, ['-v'], { encoding: 'utf8' }).status === 0;
if (!coPhp) {
  console.log(`${CI ? 'LỖI ' : 'NHẮC'}     [v2 · PHP thuần] Không có lệnh \`${php}\` (đặt PHP_BIN) — bỏ qua.`);
  if (CI) hong = true;
} else {
  const bd = Date.now();
  const r = spawnSync(php, [path.join(goc, 'tests', 'don-vi', 'chay.php')], { encoding: 'utf8' });
  process.stdout.write(r.stdout + r.stderr);
  const giay = (Date.now() - bd) / 1000;
  if (r.status !== 0) hong = true;
  if (giay >= 5) { console.log(`LỖI      [v2 · PHP thuần] chạy ${giay} giây, cần < 5 giây (docs/22 T-141)`); hong = true; }
}

// ---------- 2. Playground ----------
const [chinh, phu] = process.versions.node.split('.').map(Number);
if (chinh < 24 || (chinh === 24 && phu < 18)) {
  console.log(`${CI ? 'LỖI ' : 'NHẮC'}     [v2 · Playground] Cần Node ≥ 24.18 (đang dùng ${process.versions.node}) — bỏ qua. CI dùng Node theo .node-version.`);
  if (CI) hong = true;
} else {
  // Playground để lại thư mục site tạm (~175 MB) khi bị tắt: dọn thư mục do chính lần chạy này tạo
  const SITE_TAM = /^node-playground-cli-site-/;
  const chay = (nhan, blueprint, tepKq, gan = []) => {
    const tamTruoc = new Set(fs.readdirSync(os.tmpdir()).filter(t => SITE_TAM.test(t)));
    fs.rmSync(tepKq, { force: true });
    const r = spawnSync('npx', [
      '-y', '@wp-playground/cli@3.1.57', 'run-blueprint', '--verbosity=quiet',
      `--blueprint=${blueprint}`,
      '--mount-dir', path.join(goc, 'plugins', 'ca-dao'), '/wordpress/wp-content/plugins/ca-dao',
      '--mount-dir', path.join(goc, 'themes', 'ca-dao'), '/wordpress/wp-content/themes/ca-dao',
      '--mount-dir', goc, '/repo',
      ...gan,
    ], { stdio: ['ignore', 'ignore', 'inherit'], shell: process.platform === 'win32', env: { ...process.env, MSYS_NO_PATHCONV: '1' } });
    for (const t of fs.readdirSync(os.tmpdir())) if (SITE_TAM.test(t) && !tamTruoc.has(t)) fs.rmSync(path.join(os.tmpdir(), t), { recursive: true, force: true });
    const kq = fs.existsSync(tepKq) ? fs.readFileSync(tepKq, 'utf8') : '';
    process.stdout.write('\n' + (kq || `LỖI      [${nhan}] không chạy được kiểm thử (mã thoát ${r.status}).\n`));
    if (r.status !== 0 || !kq.includes('KẾT QUẢ: ĐẠT')) hong = true;
  };
  chay('v2 · Playground', path.join(goc, 'tests', 'blueprint-kiem-thu.json'), path.join(goc, 'tests', '.ket-qua.txt'));
  // Chuyển đổi (docs/luu-tru/29-chuyen-doi-v2.md): plugin MVP tạo dữ liệu mẫu, rồi V2 thay chỗ trên cùng cơ sở dữ liệu như ngày chuyển
  const tepMvp = path.join(goc, 'tests', '.chuyen-doi-mvp.json');
  fs.rmSync(tepMvp, { force: true });
  chay('v2 · chuyển đổi', path.join(goc, 'tests', 'chuyen-doi', 'blueprint.json'), path.join(goc, 'tests', '.ket-qua-chuyen-doi.txt'),
    ['--mount-dir', path.resolve('wordpress', 'plugins', 'ca-dao'), '/wordpress/wp-content/plugins/ca-dao-cu']);
  // Bước tạo dữ liệu bằng mã MVP hỏng thì bước kiểm không chạy: in lỗi bước đó ra
  const mvp = fs.existsSync(tepMvp) ? JSON.parse(fs.readFileSync(tepMvp, 'utf8')) : null;
  for (const l of mvp?.loi ?? []) console.log(`LỖI      [v2 · chuyển đổi · tạo dữ liệu MVP] ${l}`);
}

process.exit(hong ? 1 : 0);
