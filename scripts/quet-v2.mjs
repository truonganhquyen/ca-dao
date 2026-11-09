#!/usr/bin/env node
// Quét V2 (docs/26 mục 13): dựng WordPress trong Playground với plugin, theme V2 và dữ liệu mẫu (wordpress/v2/blueprint.json),
// rồi trên từng trang: axe-core WCAG 2.2 A/AA (điện thoại 360 px sáng/tối; thêm 320 px sáng/tối cho trang tĩnh; máy tính 1280 px; mở mọi <details>), không tràn ngang,
// mỗi câu của lời một dòng (D-093), không gọi máy chủ ngoài, có đầu CSP và không bị CSP chặn gì, ngân sách (trang ≤ 200 KB, JavaScript ≤ 21 KB — tính theo
// dung lượng nén gzip như khi qua Cloudflare; woff2 đã nén sẵn). Cần Node ≥ 24.18 và Chrome/Chromium.
// Dùng:  npm run quet:v2 [-- --anh <thư-mục>]   (--anh: lưu ảnh chụp 390 px từng trang để xem bằng mắt)
// Các đợt sau thêm trang vào DS_TRANG khi có mẫu: tuyển tập, tìm, từ điển, trang biên tập.
import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';
import { gzipSync } from 'node:zlib';
import puppeteer from 'puppeteer-core';
import { dangNhap, kiemNode, moPlayground, timChrome } from './lib/playground-v2.mjs';

kiemNode('quét v2');
const chrome = timChrome('quét v2');
const iAnh = process.argv.indexOf('--anh');
const thuMucAnh = iAnh > 0 ? path.resolve(process.argv[iAnh + 1]) : null;
if (thuMucAnh) fs.mkdirSync(thuMucAnh, { recursive: true });

const DS_TRANG = ['/', '/trang-mau-v2/', '/tim/', '/tim/?q=dong%20mau', '/ca-dao/dong-mau-mot-cua-bai/', '/tuc-ngu/tuc-ngu-mau-ngan/', '/cau-do/cau-do-mau/', '/ve/ve-mau/', '/dong-dao/dong-dao-mau/', '/kho/', '/the-loai/ca-dao/', '/tuyen-tap/', '/tuyen-tap/gui-mot-loi-nhan-mau/', '/tu-dien/', '/tu-dien/?chu=C', '/tu-dien/chut/', '/tu-dien/ben-mau-bac/', '/gop/', '/da-luu/', '/phan-tich/mot-cach-doc-mau/', '/ban-do/', '/kham-pha/', '/chu-de/', '/mo-tip/', '/ngau-nhien/', '/ve-du-an/', '/phuong-phap/', '/nguon/', '/chinh-sach-du-lieu/'];
const DS_TINH_320 = new Set(['/ve-du-an/', '/phuong-phap/', '/nguon/', '/chinh-sach-du-lieu/']);
const NGAN_SACH = { trang: 200 * 1024, js: 21 * 1024 };
const CHE_DO = [
  { ten: 'điện thoại · sáng', w: 360, h: 800, toi: false },
  { ten: 'điện thoại · tối', w: 360, h: 800, toi: true },
  { ten: 'máy tính · sáng', w: 1280, h: 800, toi: false },
];
const CHE_DO_320 = [
  { ten: '320 px · sáng', w: 320, h: 700, toi: false },
  { ten: '320 px · tối', w: 320, h: 700, toi: true },
];

const { G, san, dong: dongMayChu } = moPlayground(9432, 'quét v2');

// Tràn ngang của một trang: null khi không tràn; có thì "N px — phần tử vượt mép phải"
const doTran = trang => trang.evaluate(async () => {
  await new Promise(ok => requestAnimationFrame(() => requestAnimationFrame(ok)));
  const w = document.documentElement.clientWidth, du = document.documentElement.scrollWidth - w;
  if (du <= 1) return null;
  const ten = e => e.tagName.toLowerCase() + (typeof e.className === 'string' && e.className.trim() ? '.' + e.className.trim().split(/\s+/).join('.') : '');
  // Bỏ phần tử nằm trong khung tự cuộn / cắt (thanh thể loại…): chúng không làm trang rộng ra
  const trongKhung = e => { for (let p = e.parentElement; p && p !== document.body; p = p.parentElement) if (getComputedStyle(p).overflowX !== 'visible') return true; return false; };
  const vuot = [...document.querySelectorAll('body *')].filter(e => e.getBoundingClientRect().right > w + 1 && !trongKhung(e) && ![...e.children].some(x => x.getBoundingClientRect().right > w + 1 && !trongKhung(x)));
  return `${du}px — ${vuot.slice(0, 3).map(e => `${ten(e)} (${Math.round(e.getBoundingClientRect().right)}px)`).join(', ') || 'không rõ phần tử'}`;
});

const axeNguon = fs.readFileSync(createRequire(import.meta.url).resolve('axe-core/axe.min.js'), 'utf8');
const THE_WCAG = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'];
const loi = [];
const kb = n => `${(n / 1024).toFixed(1)} KB`;
let trinhDuyet;
try {
  await san;
  trinhDuyet = await puppeteer.launch({ executablePath: chrome, headless: true, args: ['--no-sandbox', '--font-render-hinting=none'] });
  for (const duong of DS_TRANG) {
    for (const c of (DS_TINH_320.has(duong) ? [...CHE_DO, ...CHE_DO_320] : CHE_DO)) {
      const tab = await trinhDuyet.newPage();
      const ngoai = new Set();
      const tai = [];
      tab.on('response', async r => { try { const loai = r.request().resourceType(); const than = await r.buffer(); tai.push({ loai, nen: /woff2|png|jpe?g|webp/.test(r.url()) ? than.length : gzipSync(than).length }); } catch { /* chuyển hướng, không có thân */ } });
      tab.on('request', r => { const h = new URL(r.url()).hostname; if (!['127.0.0.1', 'localhost'].includes(h) && !r.url().startsWith('data:')) ngoai.add(h); });
      await tab.setViewport({ width: c.w, height: c.h, isMobile: c.w < 640, hasTouch: c.w < 640 });
      await tab.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: c.toi ? 'dark' : 'light' }]);
      // CSP (docs/26 mục 11): ghi mọi lần trình duyệt chặn, từ trước khi trang chạy mã đầu tiên
      await tab.evaluateOnNewDocument(() => { window.__csp = []; document.addEventListener('securitypolicyviolation', e => window.__csp.push(e.violatedDirective + ' ' + (e.blockedURI || 'nội tuyến') + (e.sample ? ' «' + e.sample.slice(0, 40) + '»' : ''))); });
      const tl = await tab.goto(G + duong, { waitUntil: 'load', timeout: 120_000 });
      if (tl.status() !== 200) loi.push(`${duong} (${c.ten}): trả ${tl.status()}`);
      if (!tl.headers()['content-security-policy']) loi.push(`${duong} (${c.ten}): thiếu đầu Content-Security-Policy`);
      if (ngoai.size) loi.push(`${duong} (${c.ten}): gọi máy chủ ngoài ${[...ngoai].join(', ')}`);
      await tab.evaluate(() => document.querySelectorAll('details').forEach(d => { d.open = true; }));
      // Tràn ngang: đo lúc vừa tải và sau khi phông nạp xong; báo phần tử vượt mép phải để biết chỗ sửa.
      // Chờ hai khung hình rồi mới đo: fit.js co chữ trong requestAnimationFrame (trước khi vẽ), nên đo giữa hai
      // khung hình là đo trạng thái không bao giờ hiện trên màn hình.
      const tran = () => doTran(tab);
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
      for (const v of new Set(await tab.evaluate(() => window.__csp || []))) loi.push(`${duong} (${c.ten}): CSP chặn ${v}`);
      if (c.w === 1280) {
        await new Promise(ok => setTimeout(ok, 300));
        const jsTrong = await tab.evaluate(() => [...document.querySelectorAll('script:not([src])')].filter(s => !s.type || /javascript|module/.test(s.type)).map(s => s.textContent).join('\n'));
        const cs = { trang: tai.reduce((s, x) => s + x.nen, 0), js: tai.filter(x => x.loai === 'script').reduce((s, x) => s + x.nen, 0) + gzipSync(Buffer.from(jsTrong)).length };
        console.log(`  ${duong}: trang ${kb(cs.trang)}, JavaScript ${kb(cs.js)}`);
        if (cs.trang > NGAN_SACH.trang) loi.push(`${duong}: trang ${kb(cs.trang)} vượt ngân sách ${kb(NGAN_SACH.trang)}`);
        if (cs.js > NGAN_SACH.js) loi.push(`${duong}: JavaScript ${kb(cs.js)} vượt ngân sách ${kb(NGAN_SACH.js)}`);
      }
      await tab.evaluate(axeNguon); // không qua thẻ <script>: CSP của trang khách không chặn công cụ quét
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
  // Trình chiếu, Đọc cùng bé (che.js tải khi bấm): mở được, axe đạt trong khung, Esc đóng và trả tiêu điểm về nút
  for (const [duong, nut] of [['/dong-dao/dong-dao-mau/', 'kids'], ['/ca-dao/dong-mau-mot-cua-bai/', 'present'], ['/tuyen-tap/gui-mot-loi-nhan-mau/', 'present-set']]) {
    const tab = await trinhDuyet.newPage();
    await tab.setViewport({ width: 1280, height: 900 });
    await tab.evaluateOnNewDocument(() => { window.__csp = []; document.addEventListener('securitypolicyviolation', e => window.__csp.push(e.violatedDirective + ' ' + (e.blockedURI || 'nội tuyến'))); });
    await tab.goto(G + duong, { waitUntil: 'load', timeout: 120_000 });
    const b = await tab.$(`[data-action=${nut}]:not([hidden])`);
    if (!b) { loi.push(`${duong}: không thấy nút ${nut}`); await tab.close(); continue; }
    await b.click();
    try { await tab.waitForSelector('.fullscreen-mode .line, .fullscreen-mode .kid-line', { timeout: 15_000 }); } catch { loi.push(`${duong}: bấm ${nut} không mở khung toàn màn hình`); await tab.close(); continue; }
    await tab.evaluate(axeNguon);
    const kq = await tab.evaluate(the => window.axe.run('.fullscreen-mode', { runOnly: { type: 'tag', values: the } }), THE_WCAG);
    for (const v of kq.violations) loi.push(`${duong} (${nut}): ${v.id} — ${v.help}`);
    await tab.keyboard.press('ArrowRight');
    await tab.keyboard.press('Escape');
    const dong = await tab.evaluate(n => !document.querySelector('.fullscreen-mode') && document.activeElement?.dataset.action === n, nut);
    if (!dong) loi.push(`${duong} (${nut}): Esc không đóng khung hoặc không trả tiêu điểm về nút`);
    for (const v of new Set(await tab.evaluate(() => window.__csp || []))) loi.push(`${duong} (${nut}): CSP chặn ${v}`);
    await tab.close();
  }
  // Người biên soạn (D-094): đăng nhập rồi quét các trang biên tập và trang bài ở chế độ sửa (trợ năng, tràn ngang;
  // không tính ngân sách — mã sửa chỉ nạp cho người biên soạn). Bỏ qua thanh quản trị của WordPress khi chạy axe.
  const DS_BIEN_TAP = ['/bien-tap/', '/bien-tap/tac-pham/', '/bien-tap/them/', '/bien-tap/so-lieu/', '/tu-dien/ben-mau-bac/', '/ca-dao/dong-mau-mot-cua-bai/', '/ve/ve-mau/']; // trang bài để cuối: bước sau mở tấm sửa trên trang cuối
  const bt = await trinhDuyet.newPage();
  await bt.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  if (!(await dangNhap(bt, G, 'quét v2'))) loi.push('không đăng nhập được người biên soạn');
  const axeBt = async (nhan) => {
    await bt.addScriptTag({ content: axeNguon });
    const kq = await bt.evaluate(the => window.axe.run({ exclude: [['#wpadminbar']] }, { runOnly: { type: 'tag', values: the } }), THE_WCAG);
    for (const v of kq.violations) for (const n of v.nodes.slice(0, 3)) loi.push(`${nhan} (biên tập): [${v.id}] ${v.help} — ${n.target.join(' ')}`);
  };
  for (const duong of DS_BIEN_TAP) {
    const tl = await bt.goto(G + duong, { waitUntil: 'load', timeout: 120_000 });
    if (tl.status() !== 200) loi.push(`${duong} (biên tập): trả ${tl.status()}`);
    await bt.evaluate(() => document.fonts.ready);
    const t = await doTran(bt);
    if (t) loi.push(`${duong} (biên tập): trang tràn ngang ${t}`);
    if (!(await bt.$('.dai-bien-tap, .hv-dau, .ds-tp, .empty, [data-them-bai], .so-lieu, #sua-muc-tu'))) loi.push(`${duong} (biên tập): không thấy giao diện biên tập — đăng nhập hỏng?`);
    await axeBt(duong);
    if (thuMucAnh) await bt.screenshot({ path: path.join(thuMucAnh, (duong.replace(/\W+/g, '-').replace(/^-|-$/g, '')) + '-bien-tap-390.png'), fullPage: true });
  }
  // Một tấm sửa đang mở (trang bài cuối danh sách)
  await bt.click('[data-action="mo-tam"][data-tam="y_nghia"]');
  await new Promise(ok => setTimeout(ok, 300));
  if (!(await bt.$('dialog#tam-y_nghia[open]'))) loi.push('trang bài (biên tập): bấm "Sửa ý nghĩa" không mở tấm sửa');
  await axeBt('tấm sửa ý nghĩa');
  if (thuMucAnh) await bt.screenshot({ path: path.join(thuMucAnh, 'tam-sua-y-nghia-390.png') });
  // Trang sửa mới trong wp-admin: quét nội dung plugin, tránh báo lỗi menu lõi WordPress.
  const baiAdmin = await bt.$eval('.dai-bien-tap', e => e.dataset.bai);
  const duongAdmin = `/wp-admin/admin.php?page=phongdao-sua-tac-pham&id=${baiAdmin}`;
  const tlAdmin = await bt.goto(G + duongAdmin, { waitUntil: 'load', timeout: 120_000 });
  if (tlAdmin.status() !== 200 || !(await bt.$('.phongdao-admin-sua form'))) loi.push(`${duongAdmin}: không mở được biểu mẫu admin`);
  else {
    await bt.evaluate(() => { document.querySelectorAll('.phongdao-admin-sua details').forEach(d => { d.open = true; }); });
    const tranAdmin = await doTran(bt);
    if (tranAdmin) loi.push(`${duongAdmin}: tràn ngang ${tranAdmin}`);
    // wp-admin không nạp CSS của theme: biểu tượng SVG dùng chung phải tự có cỡ (không phình thành hình to)
    const iconTo = await bt.$$eval('.phongdao-admin-sua svg', ss => ss.filter(s => s.getBoundingClientRect().width > 40).length);
    if (iconTo) loi.push(`${duongAdmin}: ${iconTo} biểu tượng SVG rộng quá 40 px (thiếu CSS cỡ biểu tượng trong admin)`);
    await bt.addScriptTag({ content: axeNguon });
    const kqAdmin = await bt.evaluate(the => window.axe.run('.phongdao-admin-sua', { runOnly: { type: 'tag', values: the } }), THE_WCAG);
    for (const v of kqAdmin.violations) for (const n of v.nodes.slice(0, 3)) loi.push(`${duongAdmin}: [${v.id}] ${v.help} — ${n.target.join(' ')}`);
    if (thuMucAnh) await bt.screenshot({ path: path.join(thuMucAnh, 'admin-sua-390.png'), fullPage: true });
  }
  // Màn Kho (T-184): danh sách câu thu thập có nút "Đưa vào Hộp việc"; quét ở 390 px như trang sửa
  const duongKho = '/wp-admin/admin.php?page=phongdao-kho';
  const tlKho = await bt.goto(G + duongKho, { waitUntil: 'load', timeout: 120_000 });
  if (tlKho.status() !== 200 || !(await bt.$('.phongdao-admin-kho table'))) loi.push(`${duongKho}: không mở được màn Kho`);
  else {
    if (!(await bt.$('.phongdao-admin-kho input[name=action][value=phongdao_kho_dua_vao]'))) loi.push(`${duongKho}: dữ liệu mẫu không có câu nào đưa vào Hộp việc được`);
    const tranKho = await doTran(bt);
    if (tranKho) loi.push(`${duongKho}: tràn ngang ${tranKho}`);
    await bt.addScriptTag({ content: axeNguon });
    const kqKho = await bt.evaluate(the => window.axe.run('.phongdao-admin-kho', { runOnly: { type: 'tag', values: the } }), THE_WCAG);
    for (const v of kqKho.violations) for (const n of v.nodes.slice(0, 3)) loi.push(`${duongKho}: [${v.id}] ${v.help} — ${n.target.join(' ')}`);
    if (thuMucAnh) await bt.screenshot({ path: path.join(thuMucAnh, 'admin-kho-390.png'), fullPage: true });
  }
  await bt.close();
  console.log(`Quét V2: ${DS_TRANG.length} trang × ${CHE_DO.length} chế độ + ${DS_TINH_320.size} trang tĩnh × 2 chế độ 320 px + ${DS_BIEN_TAP.length} trang biên tập + 2 trang admin (axe ${THE_WCAG.join(', ')}) — ${loi.length} lỗi.`);
} catch (e) {
  loi.push(String(e.message ?? e));
} finally {
  await trinhDuyet?.close();
  await dongMayChu();
}
for (const l of loi) console.log(`LỖI      [quét v2] ${l}`);
process.exit(loi.length ? 1 : 0);
