#!/usr/bin/env node
// In link bản xem trước WordPress cho PR (D-035, docs/24 mục 6): `npm run xem-truoc:wordpress [-- <nhánh>]`.
// Link mở WordPress Playground trên trình duyệt (máy tính hoặc điện thoại), cài plugin từ nhánh đã đẩy lên GitHub.
import fs from 'node:fs';
import { execSync } from 'node:child_process';

const nhanh = process.argv[2] ?? execSync('git rev-parse --abbrev-ref HEAD', { encoding: 'utf8' }).trim();
const bp = JSON.parse(fs.readFileSync('wordpress/blueprint.json', 'utf8'));
bp.meta.title = `Xem trước: ${nhanh}`;
bp.landingPage = '/wp-admin/edit.php?post_type=cdtn&page=cadao-nguon-tu-dong';
// Tại máy plugin được gắn thư mục; trên trình duyệt thì cài thẳng từ GitHub
bp.steps = bp.steps.map((s) =>
  s.step === 'activatePlugin'
    ? {
        step: 'installPlugin',
        pluginData: { resource: 'git:directory', url: 'https://github.com/truonganhquyen/ca-dao', ref: nhanh, refType: 'branch', path: 'wordpress/plugins/ca-dao' },
        options: { activate: true, targetFolderName: 'ca-dao' },
      }
    : s,
);
console.log('https://playground.wordpress.net/#' + encodeURIComponent(JSON.stringify(bp)));
