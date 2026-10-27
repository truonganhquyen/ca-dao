#!/usr/bin/env node
// Kiểm "site thật đúng bản" (T-129, D-080): phiên bản plugin, theme phongdao.com đang phục vụ có khớp `main` không.
// Chỉ đọc site. Thoát 1 khi lệch (GitHub Action dùng mã này để mở issue). Chạy: node scripts/kiem-site-that.mjs
import { phienBanRepo, phienBanTrang, chenhLech, baiDauTien } from './lib/phien-ban.mjs';

const GOC = (process.env.SITE_GOC || 'https://phongdao.com').replace(/\/$/, '');
const lay = async url => {
  const r = await fetch(url, { headers: { 'user-agent': 'kiem-site-that (repo truonganhquyen/ca-dao)' }, redirect: 'follow' });
  if (!r.ok) throw new Error(`${url}: HTTP ${r.status}`);
  return r.text();
};

const repo = phienBanRepo();
let bai, html;
try {
  bai = baiDauTien(await lay(`${GOC}/kho/`), GOC);
  if (bai) html = await lay(`${bai}?kiem=${Date.now()}`);
} catch (e) {
  console.log(`LỆCH     [site thật] không đọc được site: ${e.cause?.code || e.message}`);
  process.exit(1);
}
if (!bai) {
  console.log(`LỆCH     [site thật] không tìm thấy trang bài nào ở ${GOC}/kho/ để đọc phiên bản`);
  process.exit(1);
}
const site = phienBanTrang(html);
const lech = chenhLech(repo, site);
if (lech.length) {
  console.log(`LỆCH     [site thật] ${bai} — ${lech.join('; ')}`);
  process.exit(1);
}
console.log(`ĐÚNG BẢN [site thật] plugin ${site.plugin}, theme ${site.theme} (${bai})`);
