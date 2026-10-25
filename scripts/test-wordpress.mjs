#!/usr/bin/env node
// Kiểm thử plugin WordPress `ca-dao` trong WordPress Playground (docs/24 mục 6): `npm run test:wordpress`.
// Cần Node ≥ 24.18 (yêu cầu của Playground) và mạng (tải WordPress lần đầu). Thêm --mang để thử bộ đọc trên cadao.me thật.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawnSync } from 'node:child_process';

const [chinh, phu] = process.versions.node.split('.').map(Number);
if (chinh < 24 || (chinh === 24 && phu < 18)) {
  console.log(`NHẮC     [wordpress] Cần Node ≥ 24.18 để chạy Playground (đang dùng ${process.versions.node}) — bỏ qua. CI dùng Node theo .node-version.`);
  process.exit(process.env.CI ? 1 : 0);
}

// Playground để lại thư mục site tạm (~175 MB) khi bị tắt: dọn thư mục do chính lần chạy này tạo
const SITE_TAM = /^node-playground-cli-site-/;
const tamTruoc = new Set(fs.readdirSync(os.tmpdir()).filter(t => SITE_TAM.test(t)));
const donTam = () => { for (const t of fs.readdirSync(os.tmpdir())) if (SITE_TAM.test(t) && !tamTruoc.has(t)) fs.rmSync(path.join(os.tmpdir(), t), { recursive: true, force: true }); };
const goc = path.resolve('wordpress');
const tepKq = path.join(goc, 'tests', '.ket-qua.txt');
fs.rmSync(tepKq, { force: true });
const doiSo = [
  '-y', '@wp-playground/cli@3.1.57', 'run-blueprint', '--verbosity=quiet',
  `--blueprint=${path.join(goc, 'tests', 'blueprint-kiem-thu.json')}`,
  '--mount-dir', path.join(goc, 'plugins', 'ca-dao'), '/wordpress/wp-content/plugins/ca-dao',
  '--mount-dir', goc, '/repo',
  ...(process.argv.includes('--mang') ? ['--define-bool', 'CADAO_KIEM_MANG', 'true'] : []),
];
const r = spawnSync('npx', doiSo, { stdio: ['ignore', 'ignore', 'inherit'], shell: process.platform === 'win32', env: { ...process.env, MSYS_NO_PATHCONV: '1' } });
donTam();
const kq = fs.existsSync(tepKq) ? fs.readFileSync(tepKq, 'utf8') : '';
process.stdout.write(kq || `LỖI      [wordpress] Playground không chạy được kiểm thử (mã thoát ${r.status}).\n`);
process.exit(r.status === 0 && kq.includes('KẾT QUẢ: ĐẠT') ? 0 : 1);
