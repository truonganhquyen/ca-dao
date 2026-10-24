#!/usr/bin/env node
// Quét trang đã build bằng trình duyệt thật (T-040; docs/23 mục 2, 5) — một phần của `npm run kiem-tra`:
//   1. Trợ năng: axe-core, WCAG 2.2 A/AA, mỗi trang ở điện thoại 360 px (sáng, tối) và máy tính; mở mọi <details>.
//   2. Ô chú giải (trang mẫu): mở bằng chạm/Enter, đóng bằng Esc/chạm ra ngoài, trả focus về từ vừa chạm.
//   3. Tắt JavaScript (trang mẫu): đọc được lời, chú giải cuối khối lời, nguồn gốc.
// Dùng Chrome/Chromium có sẵn trên máy (không tải trình duyệt): CHROME_PATH, hoặc đường dẫn thường gặp.
// Không có trình duyệt: trên CI là LỖI; trên máy cục bộ chỉ NHẮC và bỏ qua.
// Dùng:  node scripts/quet-trang.mjs [thư-mục-build, mặc định dist]
import fs from 'node:fs';
import http from 'node:http';
import path from 'node:path';
import { createRequire } from 'node:module';
import puppeteer from 'puppeteer-core';

const GOC = path.resolve(process.argv[2] ?? 'dist');
const axeNguon = fs.readFileSync(createRequire(import.meta.url).resolve('axe-core/axe.min.js'), 'utf8');
const THE_WCAG = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'];
const TRANG_MAU = '/ca-dao/mau-nhan-d/';

const chrome = [
  process.env.CHROME_PATH,
  '/usr/bin/google-chrome', '/usr/bin/google-chrome-stable', '/usr/bin/chromium', '/usr/bin/chromium-browser',
  'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
].find(p => p && fs.existsSync(p));
if (!chrome) {
  if (process.env.CI) { console.log('LỖI      [quét] Không tìm thấy Chrome/Chromium (đặt CHROME_PATH).'); process.exit(1); }
  console.log('NHẮC     [quét] Không tìm thấy Chrome/Chromium trên máy — bỏ qua quét trợ năng (CI vẫn chạy). Đặt CHROME_PATH nếu có.');
  process.exit(0);
}

// ---------- Máy chủ tệp tĩnh cho dist/ (cách Cloudflare phục vụ: /a/ → /a/index.html, không có → 404.html) ----------
const LOAI = { '.html': 'text/html; charset=utf-8', '.css': 'text/css', '.js': 'text/javascript', '.svg': 'image/svg+xml', '.woff2': 'font/woff2', '.yml': 'text/plain' };
const mayChu = http.createServer((req, res) => {
  let p = decodeURIComponent(new URL(req.url, 'http://x').pathname);
  let f = path.join(GOC, p);
  if (fs.existsSync(f) && fs.statSync(f).isDirectory()) f = path.join(f, 'index.html');
  else if (!fs.existsSync(f) && fs.existsSync(`${f}.html`)) f = `${f}.html`;
  const co = f.startsWith(GOC) && fs.existsSync(f);
  if (!co) f = path.join(GOC, '404.html');
  res.writeHead(co ? 200 : 404, { 'content-type': LOAI[path.extname(f)] ?? 'application/octet-stream' });
  res.end(fs.readFileSync(f));
});
await new Promise(r => mayChu.listen(0, '127.0.0.1', r));
const GOC_URL = `http://127.0.0.1:${mayChu.address().port}`;

const tatCaTep = dir => fs.readdirSync(dir, { withFileTypes: true })
  .flatMap(e => e.isDirectory() ? tatCaTep(path.join(dir, e.name)) : [path.join(dir, e.name)]);
// /admin/ là giao diện của Sveltia CMS (tải từ CDN, D-052), không phải trang đọc: không quét ở đây
const dsTrang = tatCaTep(GOC).filter(f => f.endsWith('.html'))
  .map(f => '/' + path.relative(GOC, f).split(path.sep).join('/').replace(/index\.html$/, '').replace(/\.html$/, ''))
  .filter(d => !d.startsWith('/admin/'));

const loi = [];
const trinhDuyet = await puppeteer.launch({ executablePath: chrome, headless: true, args: ['--no-sandbox', '--font-render-hinting=none'] });
try {
  const CHE_DO = [
    { ten: 'điện thoại · sáng', w: 360, h: 800, toi: false },
    { ten: 'điện thoại · tối', w: 360, h: 800, toi: true },
    { ten: 'máy tính · sáng', w: 1280, h: 800, toi: false },
  ];
  for (const duong of dsTrang) {
    for (const c of CHE_DO) {
      const tab = await trinhDuyet.newPage();
      await tab.setViewport({ width: c.w, height: c.h, isMobile: c.w < 640, hasTouch: c.w < 640 });
      await tab.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: c.toi ? 'dark' : 'light' }]);
      await tab.goto(GOC_URL + duong, { waitUntil: 'load' });
      await tab.evaluate(() => document.querySelectorAll('details').forEach(d => { d.open = true; }));
      await tab.addScriptTag({ content: axeNguon });
      const kq = await tab.evaluate(the => window.axe.run(document, { runOnly: { type: 'tag', values: the } }), THE_WCAG);
      for (const v of kq.violations) {
        for (const n of v.nodes.slice(0, 3)) loi.push(`${duong} (${c.ten}): [${v.id}] ${v.help} — ${n.target.join(' ')}`);
      }
      await tab.close();
    }
  }

  // ---------- Ô chú giải và trang khi tắt JavaScript (chỉ khi có trang mẫu) ----------
  if (dsTrang.includes(TRANG_MAU)) {
    for (const mayTinh of [false, true]) {
      const noi = mayTinh ? 'máy tính' : 'điện thoại';
      const tab = await trinhDuyet.newPage();
      await tab.setViewport(mayTinh ? { width: 1280, height: 800 } : { width: 360, height: 800, isMobile: true, hasTouch: true });
      await tab.goto(GOC_URL + TRANG_MAU, { waitUntil: 'load' });
      const nut = await tab.$('button.tu-chu-giai');
      if (!nut) { loi.push(`${TRANG_MAU} (${noi}): từ có chú giải không thành nút khi có JavaScript`); await tab.close(); continue; }
      await nut.focus();
      await tab.keyboard.press('Enter');
      const mo = await tab.evaluate(() => {
        const d = document.getElementById('o-chu-giai');
        return { open: d.open, tu: d.querySelector('[data-o="tu"]').textContent, nghia: d.querySelector('[data-o="giai-nghia"]').textContent,
          expanded: document.querySelector('button.tu-chu-giai').getAttribute('aria-expanded') };
      });
      if (!mo.open || !mo.tu || !mo.nghia) loi.push(`${TRANG_MAU} (${noi}): Enter trên từ có chú giải không mở ô chú giải đủ nội dung`);
      if (mo.expanded !== 'true') loi.push(`${TRANG_MAU} (${noi}): nút chưa đặt aria-expanded="true" khi mở`);
      if (mayTinh) await tab.mouse.click(5, 790); else await tab.keyboard.press('Escape');
      const dong = await tab.evaluate(() => ({
        open: document.getElementById('o-chu-giai').open,
        focusVe: document.activeElement === document.querySelector('button.tu-chu-giai'),
      }));
      if (dong.open) loi.push(`${TRANG_MAU} (${noi}): ${mayTinh ? 'chạm ra ngoài' : 'Esc'} không đóng ô chú giải`);
      if (!mayTinh && !dong.focusVe) loi.push(`${TRANG_MAU} (${noi}): đóng ô chú giải không trả focus về từ vừa chạm`);
      await tab.close();
    }

    const tab = await trinhDuyet.newPage();
    await tab.setJavaScriptEnabled(false);
    await tab.setViewport({ width: 360, height: 800 });
    await tab.goto(GOC_URL + TRANG_MAU, { waitUntil: 'load' });
    const khongJs = await tab.evaluate(() => {
      const hien = el => Boolean(el && el.offsetParent !== null && el.getBoundingClientRect().height > 0);
      const lienKet = [...document.querySelectorAll('a.tu-chu-giai')];
      return {
        loi: hien(document.querySelector('.khoi-loi .loi')),
        chuGiai: hien(document.querySelector('.chu-giai-cuoi')),
        neoHaiChieu: lienKet.length > 0 && lienKet.every(a => document.querySelector(a.getAttribute('href'))?.querySelector(`a[href="#${a.id}"]`)),
        nguonGoc: hien(document.querySelector('.nguon-goc')),
      };
    });
    if (!khongJs.loi) loi.push(`${TRANG_MAU} (tắt JavaScript): không thấy lời`);
    if (!khongJs.chuGiai) loi.push(`${TRANG_MAU} (tắt JavaScript): chú giải không hiện ở cuối khối lời`);
    if (!khongJs.neoHaiChieu) loi.push(`${TRANG_MAU} (tắt JavaScript): liên kết chú giải không hai chiều`);
    if (!khongJs.nguonGoc) loi.push(`${TRANG_MAU} (tắt JavaScript): không thấy khối nguồn gốc`);
    await tab.close();
  }
} finally {
  await trinhDuyet.close();
  mayChu.close();
}

for (const l of loi) console.log(`LỖI      [quét] ${l}`);
console.log(`Quét trang: ${dsTrang.length} trang × 3 chế độ (axe ${THE_WCAG.join(', ')}) — ${loi.length} lỗi.`);
process.exit(loi.length ? 1 : 0);
