#!/usr/bin/env node
// Đo tìm kiếm V2 trên kho thử lớn (docs/22 T-181): `npm run do:tim:v2 [-- 1000,5000,10000]`.
// Dựng WordPress Playground (SQLite) có plugin V2, đổ kho thử tổng hợp vào bảng chỉ mục tìm rồi so cách cũ
// (500 ứng viên) — lấy hết — cách mới; in bảng kết quả (wordpress/v2/tests/.do-tim.txt). Không chạm site thật.
// Đo trên MySQL/MariaDB: xem wordpress/v2/tests/do-tim/mysql.php. Cần Node ≥ 24.18 và mạng.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import { tamPlayground } from './lib/tam-playground.mjs';
import { kiemNode } from './lib/playground-v2.mjs';

kiemNode('v2 · đo tìm');
const co = (process.argv[2] || '1000,5000,10000').replace(/[^0-9,]/g, '');
const goc = path.resolve('wordpress', 'v2');
const tepKq = path.join(goc, 'tests', '.do-tim.txt');
const bp = {
  $schema: 'https://playground.wordpress.net/blueprint-schema.json',
  preferredVersions: { php: '8.3', wp: 'latest' },
  steps: [
    { step: 'activatePlugin', pluginPath: '/wordpress/wp-content/plugins/ca-dao/ca-dao.php' },
    { step: 'runPHP', code: `<?php require '/wordpress/wp-load.php'; define('DO_TIM_CO', '${co}'); require '/repo/tests/do-tim/do.php';` },
  ],
};
const tepBp = path.join(os.tmpdir(), `phongdao-do-tim-${process.pid}.json`);
fs.writeFileSync(tepBp, JSON.stringify(bp));
fs.rmSync(tepKq, { force: true });
const tam = tamPlayground();
const r = spawnSync('npx', [
  '-y', '@wp-playground/cli@3.1.57', 'run-blueprint', '--verbosity=quiet', `--blueprint=${tepBp}`,
  '--mount-dir', path.join(goc, 'plugins', 'ca-dao'), '/wordpress/wp-content/plugins/ca-dao',
  '--mount-dir', goc, '/repo',
], { stdio: ['ignore', 'ignore', 'inherit'], shell: process.platform === 'win32', env: { ...process.env, ...tam.env, MSYS_NO_PATHCONV: '1' } });
tam.don();
fs.rmSync(tepBp, { force: true });
const kq = fs.existsSync(tepKq) ? fs.readFileSync(tepKq, 'utf8') : '';
process.stdout.write(kq || `LỖI      [v2 · đo tìm] không chạy được (mã thoát ${r.status}).\n`);
process.exit(r.status === 0 && kq.includes('cách mới ĐÚNG') ? 0 : 1);
