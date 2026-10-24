#!/usr/bin/env node
// Kiểm liên kết nội bộ trong bản build (docs/21 mục 3, docs/23 mục 1) — một phần của `npm run kiem-tra`.
// Mọi href/src trỏ trong site phải tới một tệp có thật; `#neo` phải có id (hoặc name) tương ứng.
// Liên kết ra ngoài (http:, https:, mailto:…) không kiểm ở đây.
//
// Dùng:  node scripts/kiem-lien-ket.mjs [thư-mục-build, mặc định dist]
import fs from 'node:fs';
import path from 'node:path';

const GOC = process.argv[2] ?? 'dist';
if (!fs.existsSync(GOC)) {
  console.log(`LỖI      [liên kết] Không có thư mục ${GOC}/ — chạy astro build trước.`);
  process.exit(1);
}

const tatCaTep = dir => fs.readdirSync(dir, { withFileTypes: true })
  .flatMap(e => e.isDirectory() ? tatCaTep(path.join(dir, e.name)) : [path.join(dir, e.name)]);
const trangHtml = tatCaTep(GOC).filter(f => f.endsWith('.html'));

const BEN_NGOAI = /^(?:[a-z][a-z0-9+.-]*:|\/\/)/i;
const giaiMa = s => s.replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&#39;/g, "'");

const idCache = new Map();
function idCua(tep) {
  if (!idCache.has(tep)) {
    const html = fs.readFileSync(tep, 'utf8');
    idCache.set(tep, new Set([...html.matchAll(/\s(?:id|name)="([^"]*)"/g)].map(m => giaiMa(m[1]))));
  }
  return idCache.get(tep);
}

// "/a/b" → dist/a/b | dist/a/b/index.html | dist/a/b.html (cách Cloudflare phục vụ tệp tĩnh)
function timTep(tuyetDoi) {
  const ungVien = [tuyetDoi, path.join(tuyetDoi, 'index.html'), `${tuyetDoi}.html`];
  return ungVien.find(p => fs.existsSync(p) && fs.statSync(p).isFile());
}

const loi = [];
let soLienKet = 0;
for (const trang of trangHtml) {
  const html = fs.readFileSync(trang, 'utf8');
  for (const m of html.matchAll(/\s(?:href|src)="([^"]*)"/g)) {
    const dich = giaiMa(m[1]).trim();
    if (!dich || BEN_NGOAI.test(dich)) continue;
    soLienKet++;
    const [phanDuong, neo] = dich.split('#');
    let duong;
    try { duong = decodeURIComponent(phanDuong.split('?')[0]); } catch { duong = phanDuong; }
    const tepDich = duong === ''
      ? trang
      : timTep(duong.startsWith('/') ? path.join(GOC, duong) : path.join(path.dirname(trang), duong));
    const viTri = path.relative(GOC, trang).split(path.sep).join('/');
    if (!tepDich) { loi.push(`${viTri}: liên kết "${dich}" không tới trang hay tệp nào`); continue; }
    if (neo && tepDich.endsWith('.html') && !idCua(tepDich).has(neo)) {
      loi.push(`${viTri}: liên kết "${dich}" — không có neo #${neo} trong trang đích`);
    }
  }
}

for (const l of loi) console.log(`LỖI      [liên kết] ${l}`);
console.log(`Kiểm liên kết: ${trangHtml.length} trang, ${soLienKet} liên kết nội bộ — ${loi.length} lỗi.`);
process.exit(loi.length ? 1 : 0);
