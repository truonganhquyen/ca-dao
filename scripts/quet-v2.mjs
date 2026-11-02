#!/usr/bin/env node
// Quét V2 (docs/26 mục 13): dựng WordPress trong Playground với plugin, theme V2 và dữ liệu mẫu (wordpress/v2/blueprint.json),
// rồi trên từng trang: axe-core WCAG 2.2 A/AA (điện thoại 360 px sáng, tối; máy tính 1280 px; mở mọi <details>), không tràn ngang,
// mỗi câu của lời một dòng (D-093), không gọi máy chủ ngoài, ngân sách (trang ≤ 200 KB, JavaScript ≤ 21 KB — tính theo
// dung lượng nén gzip như khi qua Cloudflare; woff2 đã nén sẵn). Cần Node ≥ 24.18 và Chrome/Chromium.
// Dùng:  npm run quet:v2 [-- --anh <thư-mục>]   (--anh: lưu ảnh chụp 390 px từng trang để xem bằng mắt)
// Các đợt sau thêm trang vào DS_TRANG khi có mẫu: thể loại, tuyển tập, tìm, từ điển, Bàn biên tập.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawn, spawnSync } from 'node:child_process';
import { createRequire } from 'node:module';
import { gzipSync } from 'node:zlib';
import puppeteer from 'puppeteer-core';

const CI = Boolean(process.env.CI);
const [chinh, phu] = process.versions.node.split('.').map(Number);
if (chinh < 24 || (chinh === 24 && phu < 18)) {
  console.log(`${CI ? 'LỖI ' : 'NHẮC'}     [quét v2] Cần Node ≥ 24.18 để chạy Playground (đang dùng ${process.versions.node}) — bỏ qua.`);
  process.exit(CI ? 1 : 0);
}
const chrome = [
  process.env.CHROME_PATH,
  '/usr/bin/google-chrome', '/usr/bin/google-chrome-stable', '/usr/bin/chromium', '/usr/bin/chromium-browser',
  'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
].find(p => p && fs.existsSync(p));
if (!chrome) {
  console.log(`${CI ? 'LỖI ' : 'NHẮC'}     [quét v2] Không tìm thấy Chrome/Chromium (đặt CHROME_PATH).`);
  process.exit(CI ? 1 : 0);
}
const iAnh = process.argv.indexOf('--anh');
const thuMucAnh = iAnh > 0 ? path.resolve(process.argv[iAnh + 1]) : null;
if (thuMucAnh) fs.mkdirSync(thuMucAnh, { recursive: true });

const DS_TRANG = ['/', '/trang-mau-v2/', '/bai-mau-ca-dao/', '/bai-mau-tuc-ngu/'];
const NGAN_SACH = { trang: 200 * 1024, js: 21 * 1024 };
const CHE_DO = [
  { ten: 'điện thoại · sáng', w: 360, h: 800, toi: false },
  { ten: 'điện thoại · tối', w: 360, h: 800, toi: true },
  { ten: 'máy tính · sáng', w: 1280, h: 800, toi: false },
];

// Playground để lại thư mục site tạm (~175 MB) khi bị tắt: dọn thư mục do chính lần chạy này tạo
const SITE_TAM = /^node-playground-cli-site-/;
const tamTruoc = new Set(fs.readdirSync(os.tmpdir()).filter(t => SITE_TAM.test(t)));
const donTam = () => { for (const t of fs.readdirSync(os.tmpdir())) if (SITE_TAM.test(t) && !tamTruoc.has(t)) fs.rmSync(path.join(os.tmpdir(), t), { recursive: true, force: true }); };
const goc = path.resolve('wordpress', 'v2');
// Quét như người đọc: bản sao blueprint không đăng nhập (thanh quản trị thêm JavaScript, làm sai ngân sách)
const bp = JSON.parse(fs.readFileSync(path.join(goc, 'blueprint.json'), 'utf8'));
delete bp.login;
const tepBp = path.join(os.tmpdir(), `phongdao-v2-quet-${process.pid}.json`);
fs.writeFileSync(tepBp, JSON.stringify(bp));
const CONG = 9432;
const G = `http://127.0.0.1:${CONG}`;
const mayChu = spawn('npx', [
  '-y', '@wp-playground/cli@3.1.57', 'server', `--port=${CONG}`,
  `--blueprint=${tepBp}`,
  '--mount-dir', path.join(goc, 'plugins', 'ca-dao'), '/wordpress/wp-content/plugins/ca-dao',
  '--mount-dir', path.join(goc, 'themes', 'ca-dao'), '/wordpress/wp-content/themes/ca-dao',
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
const kb = n => `${(n / 1024).toFixed(1)} KB`;
let trinhDuyet;
try {
  await san;
  trinhDuyet = await puppeteer.launch({ executablePath: chrome, headless: true, args: ['--no-sandbox', '--font-render-hinting=none'] });
  for (const duong of DS_TRANG) {
    for (const c of CHE_DO) {
      const tab = await trinhDuyet.newPage();
      const ngoai = new Set();
      const tai = [];
      tab.on('response', async r => { try { const loai = r.request().resourceType(); const than = await r.buffer(); tai.push({ loai, nen: /woff2|png|jpe?g|webp/.test(r.url()) ? than.length : gzipSync(than).length }); } catch { /* chuyển hướng, không có thân */ } });
      tab.on('request', r => { const h = new URL(r.url()).hostname; if (!['127.0.0.1', 'localhost'].includes(h) && !r.url().startsWith('data:')) ngoai.add(h); });
      await tab.setViewport({ width: c.w, height: c.h, isMobile: c.w < 640, hasTouch: c.w < 640 });
      await tab.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: c.toi ? 'dark' : 'light' }]);
      const tl = await tab.goto(G + duong, { waitUntil: 'load', timeout: 120_000 });
      if (tl.status() !== 200) loi.push(`${duong} (${c.ten}): trả ${tl.status()}`);
      if (ngoai.size) loi.push(`${duong} (${c.ten}): gọi máy chủ ngoài ${[...ngoai].join(', ')}`);
      await tab.evaluate(() => document.querySelectorAll('details').forEach(d => { d.open = true; }));
      // Tràn ngang: đo lúc vừa tải và sau khi phông nạp xong; báo phần tử vượt mép phải để biết chỗ sửa
      const tran = () => tab.evaluate(() => {
        const w = document.documentElement.clientWidth, du = document.documentElement.scrollWidth - w;
        if (du <= 1) return null;
        const ten = e => e.tagName.toLowerCase() + (typeof e.className === 'string' && e.className.trim() ? '.' + e.className.trim().split(/\s+/).join('.') : '');
        // Bỏ phần tử nằm trong khung tự cuộn / cắt (thanh thể loại…): chúng không làm trang rộng ra
        const trongKhung = e => { for (let p = e.parentElement; p && p !== document.body; p = p.parentElement) if (getComputedStyle(p).overflowX !== 'visible') return true; return false; };
        const vuot = [...document.querySelectorAll('body *')].filter(e => e.getBoundingClientRect().right > w + 1 && !trongKhung(e) && ![...e.children].some(x => x.getBoundingClientRect().right > w + 1 && !trongKhung(x)));
        return `${du}px — ${vuot.slice(0, 3).map(e => `${ten(e)} (${Math.round(e.getBoundingClientRect().right)}px)`).join(', ') || 'không rõ phần tử'}`;
      });
      const tranLucTai = await tran();
      // Mỗi câu một dòng: không khối lời nào phải xuống dòng dự phòng, không dòng nào bị gãy
      await tab.evaluate(() => document.fonts.ready);
      await new Promise(ok => setTimeout(ok, 150));
      const tranSau = await tran();
      if (tranLucTai) loi.push(`${duong} (${c.ten}): trang tràn ngang lúc vừa tải ${tranLucTai}`);
      if (tranSau) loi.push(`${duong} (${c.ten}): trang tràn ngang sau khi nạp phông ${tranSau}`);
      const gay = await tab.evaluate(() => {
        const dong = el => { const s = new Set(); const w = document.createTreeWalker(el, NodeFilter.SHOW_TEXT); let t; while ((t = w.nextNode())) { if (!t.textContent.trim() || t.parentElement.closest('.ln')) continue; const r = document.createRange(); r.selectNodeContents(t); for (const x of r.getClientRects()) if (x.width > 1) s.add(Math.round((x.top + x.height / 2) / 6)); } return s.size; };
        const o = [];
        for (const v of document.querySelectorAll('[data-fit]')) { if (!v.offsetParent) continue; if (v.classList.contains('tran')) { o.push('dự phòng: ' + v.textContent.trim().slice(0, 30)); continue; } for (const l of v.classList.contains('theo-ve') ? v.querySelectorAll('.ve') : v.querySelectorAll('.line')) if (dong(l) > 1) o.push(l.textContent.trim().slice(0, 30)); }
        return o;
      });
      for (const g of gay) loi.push(`${duong} (${c.ten}): lời xuống dòng — ${g}`);
      if (c.w === 1280) {
        await new Promise(ok => setTimeout(ok, 300));
        const jsTrong = await tab.evaluate(() => [...document.querySelectorAll('script:not([src])')].filter(s => !s.type || /javascript|module/.test(s.type)).map(s => s.textContent).join('\n'));
        const cs = { trang: tai.reduce((s, x) => s + x.nen, 0), js: tai.filter(x => x.loai === 'script').reduce((s, x) => s + x.nen, 0) + gzipSync(Buffer.from(jsTrong)).length };
        console.log(`  ${duong}: trang ${kb(cs.trang)}, JavaScript ${kb(cs.js)}`);
        if (cs.trang > NGAN_SACH.trang) loi.push(`${duong}: trang ${kb(cs.trang)} vượt ngân sách ${kb(NGAN_SACH.trang)}`);
        if (cs.js > NGAN_SACH.js) loi.push(`${duong}: JavaScript ${kb(cs.js)} vượt ngân sách ${kb(NGAN_SACH.js)}`);
      }
      await tab.addScriptTag({ content: axeNguon });
      const kq = await tab.evaluate(the => window.axe.run(document, { runOnly: { type: 'tag', values: the } }), THE_WCAG);
      for (const v of kq.violations) {
        for (const n of v.nodes.slice(0, 3)) loi.push(`${duong} (${c.ten}): [${v.id}] ${v.help} — ${n.target.join(' ')}`);
      }
      if (thuMucAnh && c.w === 360 && !c.toi) {
        await tab.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
        await new Promise(ok => setTimeout(ok, 300)); // fit.js co lại cỡ chữ sau khi đổi khổ
        const ten = (duong.replace(/\W+/g, '-').replace(/^-|-$/g, '') || 'trang-chu') + '-390.png';
        await tab.screenshot({ path: path.join(thuMucAnh, ten), fullPage: true });
      }
      await tab.close();
    }
  }
  console.log(`Quét V2: ${DS_TRANG.length} trang × ${CHE_DO.length} chế độ (axe ${THE_WCAG.join(', ')}) — ${loi.length} lỗi.`);
} catch (e) {
  loi.push(String(e.message ?? e));
} finally {
  await trinhDuyet?.close();
  tat();
  await new Promise(ok => setTimeout(ok, 1000)); // chờ tiến trình nhả tệp
  try { donTam(); } catch { /* tệp còn bị giữ: lần sau dọn */ }
  fs.rmSync(tepBp, { force: true });
}
for (const l of loi) console.log(`LỖI      [quét v2] ${l}`);
process.exit(loi.length ? 1 : 0);
