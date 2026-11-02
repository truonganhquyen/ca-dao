#!/usr/bin/env node
// Đồng bộ giao diện từ bộ thiết kế sang theme V2 (design/v2 là nguồn hình ảnh, D-092, D-093):
//   design/v2/source/styles.css → wordpress/v2/themes/ca-dao/assets/giao-dien.css
//   design/v2/source/fit.js      → wordpress/v2/themes/ca-dao/assets/fit.js
// Bỏ @font-face của bản mẫu (theme.json khai phông woff2 đã cắt gọn); thêm chế độ tối theo cài đặt máy
// (bản mẫu chỉ có [data-theme=dark] do nút bấm đặt). Dùng: `npm run dong-bo:giao-dien-v2` · `--kiem`: chỉ báo lệch (CI).
import fs from 'node:fs';
import path from 'node:path';

const kiem = process.argv.includes('--kiem');
const nguon = path.resolve('design/v2/source');
const dich = path.resolve('wordpress/v2/themes/ca-dao/assets');
const DAU = '/* SINH TỪ design/v2/source — đừng sửa tay. Sửa ở bộ thiết kế rồi chạy `npm run dong-bo:giao-dien-v2`. */\n';

let css = fs.readFileSync(path.join(nguon, 'styles.css'), 'utf8').replace(/\r\n/g, '\n');
const dong = css.split('\n');
if (!dong[0].startsWith('@font-face')) throw new Error('Dòng đầu styles.css không còn là @font-face — kiểm lại lệnh đồng bộ.');
css = dong.slice(1).join('\n');
const toi = [...css.matchAll(/:root\[data-theme=dark\]\{([^{}]*)\}/g)].map(m => m[1]).join(';').replace(/;;+/g, ';');
if (!toi.includes('--giay')) throw new Error('Không tìm thấy khối màu tối :root[data-theme=dark] trong styles.css.');
css = DAU + css.trimEnd() + `\n/* Chế độ tối theo cài đặt máy khi người đọc chưa bấm chọn */\n@media (prefers-color-scheme:dark){:root:not([data-theme=light]){${toi}}}\n`;
const fit = DAU.replace('/*', '//').replace(' */', '') + fs.readFileSync(path.join(nguon, 'fit.js'), 'utf8').replace(/\r\n/g, '\n');

const tep = { 'giao-dien.css': css, 'fit.js': fit };
let lech = [];
for (const [ten, noiDung] of Object.entries(tep)) {
  const p = path.join(dich, ten);
  const cu = fs.existsSync(p) ? fs.readFileSync(p, 'utf8') : null;
  if (cu === noiDung) continue;
  if (kiem) lech.push(ten);
  else { fs.mkdirSync(dich, { recursive: true }); fs.writeFileSync(p, noiDung); console.log(`Đã ghi ${path.relative('.', p)} (${(Buffer.byteLength(noiDung) / 1024).toFixed(1)} KB)`); }
}
if (kiem) {
  if (lech.length) { console.log(`LỖI      [giao diện v2] ${lech.join(', ')} lệch bộ thiết kế — chạy \`npm run dong-bo:giao-dien-v2\`.`); process.exit(1); }
  console.log('ĐẠT   giao diện theme V2 khớp bộ thiết kế design/v2');
}
