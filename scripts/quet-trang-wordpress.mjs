#!/usr/bin/env node
// Quét trợ năng giao diện WordPress (T-072; docs/20 mục 11): dựng WordPress trong Playground với plugin, theme
// và bài mẫu tổng hợp (wordpress/tests/blueprint-giao-dien.json), rồi chạy axe-core WCAG 2.2 A/AA trên trang chủ,
// kho, thể loại và từng trang bài — điện thoại 360 px (sáng, tối) và máy tính; mở mọi <details>.
// Cần Node ≥ 24.18 (Playground) và Chrome/Chromium (CHROME_PATH hoặc đường dẫn thường gặp).
// Dùng:  npm run quet:wordpress [-- --anh <thư-mục>]   (--anh: lưu ảnh chụp từng trang để xem bằng mắt)
import fs from 'node:fs';
import os from 'node:os';
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

// Playground để lại thư mục site tạm (~175 MB) khi bị tắt: dọn thư mục do chính lần chạy này tạo
const SITE_TAM = /^node-playground-cli-site-/;
const tamTruoc = new Set(fs.readdirSync(os.tmpdir()).filter(t => SITE_TAM.test(t)));
const donTam = () => { for (const t of fs.readdirSync(os.tmpdir())) if (SITE_TAM.test(t) && !tamTruoc.has(t)) fs.rmSync(path.join(os.tmpdir(), t), { recursive: true, force: true }); };
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
  const tuDien = await (await fetch(`${G}/wp-json/wp/v2/tu_kho?per_page=1&_fields=link`)).json(); // từ điển (T-044)
  const dsTrang = ['/', '/kho/', '/the-loai/ca-dao/', '/tu-dien/', ...tuDien.map(t => new URL(t.link).pathname), ...bai.map(b => new URL(b.link).pathname)];
  if (bai.length < 5) loi.push(`chỉ có ${bai.length} bài mẫu (cần 5) — bài mẫu không dựng được`);
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

  // Trích dẫn (T-042) và lượt đọc (T-081) trên trình duyệt thật: bấm "Sao chép kèm nguồn" rồi đọc bộ nhớ tạm;
  // lượt đọc gửi đúng một lần sau 5 giây, tải lại trong ngày không gửi nữa, tắt JavaScript thì không gửi; không cookie.
  const baiThu = new URL(bai.find(b => /\/ca-dao\//.test(b.link))?.link ?? bai[0].link).pathname;
  const UA = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Mobile Safari/537.36';
  // Ngữ cảnh riêng: bộ nhớ trình duyệt (đánh dấu đã đọc) không lẫn với các tab quét ở trên
  const ngCanh = await trinhDuyet.createBrowserContext();
  await ngCanh.overridePermissions(G, ['clipboard-read', 'clipboard-write', 'clipboard-sanitized-write']);
  const moTab = async (js = true) => {
    const tab = await ngCanh.newPage();
    await tab.setUserAgent(UA);
    await tab.setJavaScriptEnabled(js);
    const gui = [];
    tab.on('response', r => { if (r.request().method() === 'POST' && r.url().includes('/cadao/v1/luot-doc/')) gui.push(r); });
    return { tab, gui };
  };
  const cho = ms => new Promise(ok => setTimeout(ok, ms));
  let kiemThem = 0;
  const kiem = (ten, dk) => { kiemThem++; if (!dk) loi.push(`${baiThu}: ${ten}`); };

  const tatJs = await moTab(false);
  await tatJs.tab.goto(G + baiThu, { waitUntil: 'load' });
  kiem('tắt JavaScript: chữ trích dẫn vẫn có trên trang', (await tatJs.tab.$eval('.cadao-mau-chu', e => e.textContent)).includes('Truy cập tại:'));
  await cho(6000);
  kiem('tắt JavaScript: không gửi lượt đọc', tatJs.gui.length === 0);
  await tatJs.tab.close();

  const { tab, gui } = await moTab();
  await tab.goto(G + baiThu, { waitUntil: 'load' });
  await tab.click('.cadao-trich-dan summary');
  await tab.click('.cadao-nut button[data-viec="kem_nguon"]');
  await cho(500);
  const bao = await tab.$eval('.cadao-bao', e => e.textContent);
  const tam = await tab.evaluate(() => navigator.clipboard.readText().catch(() => ''));
  kiem(`"Sao chép kèm nguồn": báo "${bao}"`, bao === 'Đã sao chép. Cảm ơn bạn đã ghi nguồn!');
  kiem('"Sao chép kèm nguồn": bộ nhớ tạm có lời và trích dẫn', tam.length > 40 && tam.includes('Kho tàng Ca dao Tục ngữ Việt Nam. Truy cập tại:') && !tam.includes('{ngay}'));
  await cho(6000);
  kiem(`lượt đọc gửi một lần sau 5 giây (đã gửi ${gui.length})`, gui.length === 1 && gui[0].status() === 200 && (await gui[0].json()).tinh === true);
  await tab.reload({ waitUntil: 'load' });
  await cho(6000);
  kiem(`tải lại trong ngày không gửi thêm (đã gửi ${gui.length})`, gui.length === 1);
  kiem('không có cookie', (await tab.cookies()).length === 0);
  // Chạm vào từ khó (T-082): mở bằng Enter, đóng bằng Esc, trả focus về từ; dị bản tô chỗ khác (T-083)
  const tuTab = await ngCanh.newPage();
  await tuTab.setViewport({ width: 360, height: 800, isMobile: true, hasTouch: true });
  const baiDiBan = new URL(bai.find(b => /dong-mau-mot/.test(b.link))?.link ?? bai[0].link).pathname; // bài mẫu có dị bản, chú thích
  await tuTab.goto(G + baiDiBan, { waitUntil: 'load' });
  kiem('dị bản có nhãn và chữ khác được tô', (await tuTab.$('.cadao-nhan-di-ban')) !== null && (await tuTab.$('mark.cadao-khac')) !== null);
  if (await tuTab.$('a.cadao-tu')) {
    await tuTab.focus('a.cadao-tu');
    await tuTab.keyboard.press('Enter');
    await cho(300);
    const mo = await tuTab.evaluate(() => { const d = document.querySelector('dialog.cadao-o-ct'); return d && d.open ? d.innerText : ''; });
    kiem('chạm từ: Enter mở ô chú thích có lời giải và "Xem trong từ điển"', /Chú thích tổng hợp/.test(mo) && /Xem trong từ điển/.test(mo));
    await tuTab.keyboard.press('Escape');
    await cho(300);
    kiem('chạm từ: Esc đóng ô, focus trở về từ', await tuTab.evaluate(() => !document.querySelector('dialog.cadao-o-ct').open && document.activeElement.classList.contains('cadao-tu')));
  } else {
    kiem('bài mẫu có từ chú thích bấm được', false);
  }
  // Công cụ đọc (T-085): cỡ chữ, sáng / tối nhớ trên máy; trình chiếu; không có giọng Việt thì không hiện nút Nghe
  await tuTab.click('.cadao-co-chu');
  await tuTab.click('.cadao-sang-toi');
  await tuTab.reload({ waitUntil: 'load' });
  kiem('cỡ chữ, sáng / tối nhớ sau khi tải lại', await tuTab.evaluate(() => document.documentElement.getAttribute('data-co-chu') === '2' && !!document.documentElement.getAttribute('data-che-do')));
  kiem('máy không có giọng tiếng Việt: không hiện nút Nghe', await tuTab.$eval('.cadao-nghe', n => n.hidden));
  await tuTab.click('.cadao-trinh-chieu');
  await cho(300);
  await tuTab.keyboard.press('ArrowRight');
  const tcSo = await tuTab.evaluate(() => { const d = document.querySelector('dialog.cadao-tc'); return d && d.open ? d.querySelector('.cadao-tc-so').textContent : ''; });
  kiem(`trình chiếu: mở, mũi tên sang đoạn sau (${tcSo})`, /^2 \/ \d+$/.test(tcSo));
  await tuTab.keyboard.press('Escape');
  await cho(300);
  if (await tuTab.evaluate(() => document.querySelector('dialog.cadao-tc').open)) { await tuTab.keyboard.press('Escape'); await cho(300); }
  kiem('trình chiếu: Esc thoát', await tuTab.evaluate(() => !document.querySelector('dialog.cadao-tc').open));
  // Bài dài (T-090): hiện phần đầu, "Đọc toàn bài" mở phần còn lại
  const baiDai = new URL(bai.find(b => /ve-mau-dai/.test(b.link))?.link ?? bai[0].link).pathname;
  await tuTab.goto(G + baiDai, { waitUntil: 'load' });
  kiem('bài dài: phần sau thu gọn, có nút "Đọc toàn bài"', await tuTab.evaluate(() => !!document.querySelector('.cadao-doc-toan-bai') && getComputedStyle(document.querySelector('.cadao-doan[data-sau]')).display === 'none'));
  await tuTab.click('.cadao-doc-toan-bai');
  kiem('bài dài: bấm "Đọc toàn bài" thì hiện đủ', await tuTab.evaluate(() => getComputedStyle(document.querySelector('.cadao-doan[data-sau]')).display !== 'none'));
  await ngCanh.close();
  console.log(`Thử trích dẫn, lượt đọc trên trình duyệt: ${kiemThem} phép thử.`);
} catch (e) {
  loi.push(String(e.message ?? e));
} finally {
  await trinhDuyet?.close();
  tat();
  await new Promise(ok => setTimeout(ok, 1000)); // chờ tiến trình nhả tệp
  try { donTam(); } catch { /* tệp còn bị giữ: lần sau dọn */ }
}
for (const l of loi) console.log(`LỖI      [quét wordpress] ${l}`);
process.exit(loi.length ? 1 : 0);
