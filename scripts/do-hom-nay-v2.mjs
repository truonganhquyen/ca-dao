#!/usr/bin/env node
// Đo tốc độ màn Hôm nay trên kho thử lớn (T-217): `npm run do:hom-nay:v2 [-- 16000]`.
// Dựng WordPress Playground (SQLite) có plugin V2, đổ kho thử bằng SQL rồi đo từng phần của màn (wordpress/v2/tests/do-hom-nay/do.php);
// in bảng kết quả (wordpress/v2/tests/.do-hom-nay.txt). Không chạm site thật. Cần Node ≥ 24.18 và mạng.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import { tamPlayground } from './lib/tam-playground.mjs';
import { kiemNode } from './lib/playground-v2.mjs';

kiemNode('v2 · đo Hôm nay');
const co = String(Number(process.argv[2]) || 16000);
const goc = path.resolve('wordpress', 'v2');
const tepKq = path.join(goc, 'tests', '.do-hom-nay.txt');
const bp = {
  $schema: 'https://playground.wordpress.net/blueprint-schema.json',
  preferredVersions: { php: '8.3', wp: 'latest' },
  steps: [
    { step: 'activatePlugin', pluginPath: '/wordpress/wp-content/plugins/ca-dao/ca-dao.php' },
    { step: 'runPHP', code: `<?php require '/wordpress/wp-load.php'; define('DO_HOM_NAY_CO', ${co}); require '/repo/tests/do-hom-nay/do.php';` },
  ],
};
const tepBp = path.join(os.tmpdir(), `phongdao-do-hom-nay-${process.pid}.json`);
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
process.stdout.write(kq || `LỖI      [v2 · đo Hôm nay] không chạy được (mã thoát ${r.status}).\n`);
process.exit(r.status === 0 && kq.includes('Cả màn') ? 0 : 1);
