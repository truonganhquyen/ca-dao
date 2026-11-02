#!/usr/bin/env node
// In link bản xem trước V2 cho PR (D-035, docs/26 mục 13): `npm run xem-truoc:v2 [-- <nhánh>]`.
// Link mở WordPress Playground trên trình duyệt (máy tính hoặc điện thoại), cài plugin + theme V2 từ nhánh đã đẩy lên GitHub.
import fs from 'node:fs';
import { execSync } from 'node:child_process';

const nhanh = process.argv[2] ?? execSync('git rev-parse --abbrev-ref HEAD', { encoding: 'utf8' }).trim();
const bp = JSON.parse(fs.readFileSync('wordpress/v2/blueprint.json', 'utf8'));
bp.meta.title = `Xem trước V2: ${nhanh}`;
// Tại máy plugin, theme được gắn thư mục; trên trình duyệt thì cài thẳng từ GitHub
const tuGit = (thuMuc) => ({ resource: 'git:directory', url: 'https://github.com/truonganhquyen/ca-dao', ref: nhanh, refType: 'branch', path: thuMuc });
bp.steps = bp.steps.map((s) =>
  s.step === 'activatePlugin'
    ? { step: 'installPlugin', pluginData: tuGit('wordpress/v2/plugins/ca-dao'), options: { activate: true, targetFolderName: 'ca-dao' } }
    : s.step === 'activateTheme'
      ? { step: 'installTheme', themeData: tuGit('wordpress/v2/themes/ca-dao'), options: { activate: true, targetFolderName: 'ca-dao' } }
      : s,
);
console.log('https://playground.wordpress.net/#' + encodeURIComponent(JSON.stringify(bp)));
