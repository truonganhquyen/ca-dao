#!/usr/bin/env node
// Sinh bản đồ nhỏ Việt Nam (wordpress/plugins/ca-dao/assets/ban-do-vn.svg) cho chú thích địa danh (T-087): đường biên
// từ Natural Earth 1:50m (phạm vi công cộng), có quần đảo Hoàng Sa và Trường Sa. Phép chiếu đơn giản (kinh độ, vĩ độ
// tuyến tính) trong khung KHUNG; JavaScript đặt chấm địa danh theo đúng khung này.
// Dùng: node scripts/sinh-ban-do.mjs <ne_50m_admin_0_countries.geojson>
import fs from 'node:fs';

export const KHUNG = { tay: 102.0, dong: 117.6, bac: 23.6, nam: 8.0 };
const R = 300; // chiều rộng viewBox
const C = Math.round(R * (KHUNG.bac - KHUNG.nam) / (KHUNG.dong - KHUNG.tay));
const x = lon => ((lon - KHUNG.tay) / (KHUNG.dong - KHUNG.tay) * R).toFixed(1);
const y = lat => ((KHUNG.bac - lat) / (KHUNG.bac - KHUNG.nam) * C).toFixed(1);

const j = JSON.parse(fs.readFileSync(process.argv[2], 'utf8'));
const vn = j.features.find(f => f.properties.ADM0_A3 === 'VNM');
// Bỏ bớt điểm gần nhau (< 0,5 đơn vị) cho tệp nhẹ
const vong = poly => {
  let truoc = null;
  const diem = [];
  for (const [lon, lat] of poly[0]) {
    const p = [x(lon), y(lat)];
    if (truoc && Math.hypot(p[0] - truoc[0], p[1] - truoc[1]) < 0.5) continue;
    diem.push(p);
    truoc = p;
  }
  return 'M' + diem.map(p => p.join(',')).join('L') + 'Z';
};
const duong = vn.geometry.coordinates.map(vong).join('');
// Hoàng Sa, Trường Sa: chấm nhỏ theo vị trí các đảo chính (tọa độ gần đúng), kèm tên
const DAO = {
  'Hoàng Sa': [[16.53, 111.61], [16.83, 112.33], [16.45, 111.5], [16.67, 112.73], [16.0, 111.75]],
  'Trường Sa': [[10.38, 114.36], [8.65, 111.92], [10.72, 115.82], [11.43, 114.33], [9.9, 114.5], [10.2, 114.25]],
};
const cham = Object.entries(DAO).map(([ten, ds]) => {
  const tron = ds.map(([lat, lon]) => `<circle cx="${x(lon)}" cy="${y(lat)}" r="1.6"/>`).join('');
  const [lat, lon] = ds[0];
  return `<g class="dao">${tron}</g><text x="${x(lon)}" y="${(Number(y(lat)) - 6).toFixed(1)}" text-anchor="middle">${ten}</text>`;
}).join('');
const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${R} ${C}" role="img" aria-label="Bản đồ Việt Nam">`
  + `<style>path{fill:#e8dfcc;stroke:#9e8f72;stroke-width:.8}circle{fill:#9e8f72}text{font:9px sans-serif;fill:#5f594f}</style>`
  + `<path d="${duong}"/>${cham}</svg>\n`;
fs.writeFileSync('wordpress/plugins/ca-dao/assets/ban-do-vn.svg', svg);
console.log(`Bản đồ: ${svg.length} byte, khung ${JSON.stringify(KHUNG)}, viewBox ${R}×${C}`);
