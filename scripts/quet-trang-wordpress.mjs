#!/usr/bin/env node
// Quét trợ năng giao diện WordPress (T-072; docs/20 mục 11): dựng WordPress trong Playground với plugin, theme
// và bài mẫu tổng hợp (wordpress/tests/blueprint-giao-dien.json), rồi chạy axe-core WCAG 2.2 A/AA trên trang chủ,
// kho, thể loại và từng trang bài — điện thoại 360 px (sáng, tối) và máy tính; mở mọi <details>.
// Cần Node ≥ 24.18 (Playground) và Chrome/Chromium (CHROME_PATH hoặc đường dẫn thường gặp).
// Dùng:  npm run quet:wordpress [-- --anh <thư-mục>]   (--anh: lưu ảnh chụp từng trang để xem bằng mắt)
import fs from 'node:fs';
import path from 'node:path';
import { spawn, spawnSync } from 'node:child_process';
import { createRequire } from 'node:module';
import puppeteer from 'puppeteer-core';

const CI = Boolean(process.env.CI);
const [chinh, phu] = process.versions.node.split('.').map(Number);
if (chinh < 24 || (chinh === 24 && phu < 18)) {
  console.log(`NHẮC     [quét wordpress] Cần Node ≥ 24.18 để chạy Playground (đang dùng ${process.versions.node}) — bỏ qua.`);
  process.exit(CI ? 1 : 0);
}
const chrome = [
  process.env.CHROME_PATH,
  '/usr/bin/google-chrome', '/usr/bin/google-chrome-stable', '/usr/bin/chromium', '/usr/bin/chromium-browser',
  'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
].find(p => p && fs.existsSync(p));
if (!chrome) {
  console.log(`${CI ? 'LỖI ' : 'NHẮC'}     [quét wordpress] Không tìm thấy Chrome/Chromium (đặt CHROME_PATH).`);
  process.exit(CI ? 1 : 0);
}
const iAnh = process.argv.indexOf('--anh');
const thuMucAnh = iAnh > 0 ? path.resolve(process.argv[iAnh + 1]) : null;
if (thuMucAnh) fs.mkdirSync(thuMucAnh, { recursive: true });

const goc = path.resolve('wordpress');
const CONG = 9431;
const G = `http://127.0.0.1:${CONG}`;
const mayChu = spawn('npx', [
  '-y', '@wp-playground/cli@3.1.57', 'server', `--port=${CONG}`,
  `--blueprint=${path.join(goc, 'tests', 'blueprint-giao-dien.json')}`,
  '--mount-dir', path.join(goc, 'plugins', 'ca-dao'), '/wordpress/wp-content/plugins/ca-dao',
  '--mount-dir', path.join(goc, 'themes', 'ca-dao'), '/wordpress/wp-content/themes/ca-dao',
  '--mount-dir', goc, '/repo',
], { shell: process.platform === 'win32', detached: process.platform !== 'win32', env: { ...process.env, MSYS_NO_PATHCONV: '1' } });
let nhatKy = '';
const san = new Promise((ok, hong) => {
  const nghe = d => { nhatKy += d; if (/Ready!/.test(nhatKy)) ok(); };
  mayChu.stdout.on('data', nghe);
  mayChu.stderr.on('data', nghe);
  mayChu.on('exit', ma => hong(new Error(`Playground dừng (mã ${ma}):\n${nhatKy.slice(-2000)}`)));
  setTimeout(() => hong(new Error('Playground không sẵn sàng sau 5 phút')), 300_000);
});
// Tắt cả cây tiến trình (npx → node Playground); đồng bộ để không để lại máy chủ giữ cổng
const tat = () => { try { process.platform === 'win32' ? spawnSync('taskkill', ['/pid', String(mayChu.pid), '/T', '/F']) : process.kill(-mayChu.pid); } catch { mayChu.kill(); } };

const axeNguon = fs.readFileSync(createRequire(import.meta.url).resolve('axe-core/axe.min.js'), 'utf8');
const THE_WCAG = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'];
const loi = [];
let trinhDuyet;
try {
  await san;
  const bai = await (await fetch(`${G}/wp-json/wp/v2/cdtn?per_page=50&_fields=link`)).json();
  const dsTrang = ['/', '/kho/', '/the-loai/ca-dao/', ...bai.map(b => new URL(b.link).pathname)];
  if (bai.length < 4) loi.push(`chỉ có ${bai.length} bài mẫu (cần 4) — bài mẫu không dựng được`);
  trinhDuyet = await puppeteer.launch({ executablePath: chrome, headless: true, args: ['--no-sandbox', '--font-render-hinting=none'] });
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
      const tl = await tab.goto(G + duong, { waitUntil: 'load', timeout: 120_000 });
      if (tl.status() !== 200) loi.push(`${duong} (${c.ten}): trả ${tl.status()}`);
      await tab.evaluate(() => document.querySelectorAll('details').forEach(d => { d.open = true; }));
      // Không cuộn ngang ở 360 px
      const ngang = await tab.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      if (ngang > 1) loi.push(`${duong} (${c.ten}): trang tràn ngang ${ngang}px`);
      await tab.addScriptTag({ content: axeNguon });
      const kq = await tab.evaluate(the => window.axe.run(document, { runOnly: { type: 'tag', values: the } }), THE_WCAG);
      for (const v of kq.violations) {
        for (const n of v.nodes.slice(0, 3)) loi.push(`${duong} (${c.ten}): [${v.id}] ${v.help} — ${n.target.join(' ')}`);
      }
      if (thuMucAnh) {
        const ten = (duong.replace(/\W+/g, '-').replace(/^-|-$/g, '') || 'trang-chu') + `-${c.w}${c.toi ? '-toi' : ''}.png`;
        await tab.screenshot({ path: path.join(thuMucAnh, ten), fullPage: true });
      }
      await tab.close();
    }
  }
  console.log(`Quét WordPress: ${dsTrang.length} trang × ${CHE_DO.length} chế độ (axe ${THE_WCAG.join(', ')}) — ${loi.length} lỗi.`);
} catch (e) {
  loi.push(String(e.message ?? e));
} finally {
  await trinhDuyet?.close();
  tat();
}
for (const l of loi) console.log(`LỖI      [quét wordpress] ${l}`);
process.exit(loi.length ? 1 : 0);
