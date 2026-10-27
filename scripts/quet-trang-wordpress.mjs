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
  const dsTrang = ['/', '/kho/', '/the-loai/ca-dao/', '/tu-dien/', '/tim/?q=dong mau', '/da-luu/', '/ban-do/', '/tro-choi/tro-choi-mau/', ...tuDien.map(t => new URL(t.link).pathname), ...bai.map(b => new URL(b.link).pathname)];
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
    const suKien = [];
    tab.on('response', r => { if (r.request().method() === 'POST' && r.url().includes('/cadao/v1/luot-doc/')) gui.push(r); });
    tab.on('request', r => { if (r.method() === 'POST' && r.url().includes('/cadao/v1/su-kien')) suKien.push(r); }); // đo lường: bắt lúc gửi
    return { tab, gui, suKien };
  };
  const cho = ms => new Promise(ok => setTimeout(ok, ms));
  const choDk = async (dk, ms = 15000) => { const het = Date.now() + ms; while (!dk() && Date.now() < het) await cho(200); }; // Playground chạy PHP chậm
  let kiemThem = 0;
  const kiem = (ten, dk) => { kiemThem++; if (!dk) loi.push(`${baiThu}: ${ten}`); };

  const tatJs = await moTab(false);
  await tatJs.tab.goto(G + baiThu, { waitUntil: 'load' });
  kiem('tắt JavaScript: chữ trích dẫn vẫn có trên trang', (await tatJs.tab.$eval('.cadao-mau-chu', e => e.textContent)).includes('Truy cập tại:'));
  kiem('tắt JavaScript: nút Lưu, Chia sẻ (cần JavaScript) không hiện', await tatJs.tab.evaluate(() => ['.cadao-luu', '.cadao-chia-se'].every(c => getComputedStyle(document.querySelector(c)).display === 'none')));
  await cho(6000);
  kiem('tắt JavaScript: không gửi lượt đọc', tatJs.gui.length === 0);
  await tatJs.tab.close();

  const { tab, gui, suKien } = await moTab();
  await tab.goto(G + baiThu, { waitUntil: 'load' });
  await tab.click('.cadao-trich-dan summary');
  await tab.click('.cadao-nut button[data-viec="kem_nguon"]');
  await cho(500);
  const bao = await tab.$eval('.cadao-bao', e => e.textContent);
  const tam = await tab.evaluate(() => navigator.clipboard.readText().catch(() => ''));
  kiem(`"Sao chép kèm nguồn": báo "${bao}"`, bao === 'Đã sao chép. Cảm ơn bạn đã ghi nguồn!');
  kiem('"Sao chép kèm nguồn": bộ nhớ tạm có lời và trích dẫn', tam.length > 40 && tam.includes('Kho tàng Ca dao Tục ngữ Việt Nam. Truy cập tại:') && !tam.includes('{ngay}'));
  // Đo lường (T-052): sao chép gửi đúng một sự kiện, được tính
  // Chia sẻ (T-115): gửi xong mới tính — giả lập hộp chia sẻ của máy báo thành công
  await tab.evaluate(() => { navigator.share = () => Promise.resolve(); });
  await tab.click('.cadao-chia-se');
  await choDk(() => suKien.length >= 2);
  kiem('chia sẻ: gửi xong thì tính một lượt chia sẻ', suKien.some(r => JSON.parse(r.postData() || '{}').loai === 'chia_se'));
  await choDk(() => suKien[0]?.response());
  const sk0 = suKien[0];
  kiem(`đo lường: sao chép gửi sự kiện "trich_dan_sao_chep", được tính (đã gửi ${suKien.length})`, JSON.parse(sk0.postData() || '{}').loai === 'trich_dan_sao_chep' && !!sk0.response() && (await sk0.response().json()).tinh === true);
  await cho(6000);
  kiem(`lượt đọc gửi một lần sau 5 giây (đã gửi ${gui.length})`, gui.length === 1 && gui[0].status() === 200 && (await gui[0].json()).tinh === true);
  await tab.reload({ waitUntil: 'load' });
  await cho(6000);
  kiem(`tải lại trong ngày không gửi thêm (đã gửi ${gui.length})`, gui.length === 1);
  kiem('không có cookie', (await tab.cookies()).length === 0);
  // Chạm vào từ khó (T-082): mở bằng Enter, đóng bằng Esc, trả focus về từ; dị bản tô chỗ khác (T-083)
  const { tab: tuTab, suKien: skTu } = await moTab();
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
    await choDk(() => skTu.length > 0, 5000);
    kiem(`đo lường: mở chú giải gửi sự kiện "chu_giai_mo" (đã gửi ${skTu.length})`, skTu.some(r => JSON.parse(r.postData() || '{}').loai === 'chu_giai_mo'));
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
  // Đã lưu (T-051): bấm ☆ Lưu → trang /da-luu/ có bài; Bỏ lưu → trống, focus về dòng thông báo
  await tuTab.goto(G + baiDiBan, { waitUntil: 'load' });
  await tuTab.click('.cadao-luu');
  kiem('Lưu: nút chuyển "đã bấm", hiện "Bài đã lưu (1)"', await tuTab.evaluate(() => document.querySelector('.cadao-luu').getAttribute('aria-pressed') === 'true' && document.querySelector('.cadao-xem-da-luu').textContent === 'Bài đã lưu (1)' && !document.querySelector('.cadao-xem-da-luu').hidden));
  await tuTab.reload({ waitUntil: 'load' });
  kiem('Lưu: tải lại vẫn nhớ', await tuTab.evaluate(() => document.querySelector('.cadao-luu').getAttribute('aria-pressed') === 'true'));
  await tuTab.goto(G + '/da-luu/', { waitUntil: 'load' });
  const dsLuu = await tuTab.evaluate(() => Array.from(document.querySelectorAll('.cadao-da-luu-ds li a')).map(a => a.pathname));
  kiem(`trang Đã lưu: có đúng bài vừa lưu (${dsLuu.join(', ')})`, dsLuu.length === 1 && dsLuu[0] === baiDiBan);
  await tuTab.addScriptTag({ content: axeNguon });
  const axeLuu = await tuTab.evaluate(the => window.axe.run(document, { runOnly: { type: 'tag', values: the } }), THE_WCAG);
  kiem(`trang Đã lưu có bài: không lỗi trợ năng (${axeLuu.violations.map(v => v.id).join(', ')})`, axeLuu.violations.length === 0);
  await tuTab.click('.cadao-bo-luu');
  kiem('Bỏ lưu: danh sách trống, hiện lời nhắc, focus về lời nhắc', await tuTab.evaluate(() => !document.querySelector('.cadao-da-luu-ds li') && !document.querySelector('.cadao-da-luu-trong').hidden && document.activeElement === document.querySelector('.cadao-da-luu-trong')));
  // Đồng dao (T-103): khối Cách chơi; "Đọc cùng bé" — một dòng, chạm sang dòng, vỗ tay tô từng chữ, Esc thoát
  const baiDd = new URL(bai.find(b => /dong-dao-mau/.test(b.link))?.link ?? bai[0].link).pathname;
  await tuTab.goto(G + baiDd, { waitUntil: 'load' });
  kiem('đồng dao: có khối Cách chơi của trò chơi', /Cách chơi: Trò chơi mẫu/.test(await tuTab.$eval('main', m => m.innerText)));
  if (await tuTab.$('.cadao-doc-cung-be:not([hidden])')) {
    // Bàn phím: nút được focus không bị thanh dưới (điện thoại) che (WCAG 2.4.11)
    await tuTab.evaluate(() => { scrollTo(0, 0); document.querySelector('.cadao-doc-cung-be').focus(); });
    await cho(200);
    kiem('focus không bị thanh dưới che', await tuTab.evaluate(() => document.querySelector('.cadao-doc-cung-be').getBoundingClientRect().bottom <= document.querySelector('.cadao-thanh-duoi').getBoundingClientRect().top + 1));
    await tuTab.$eval('.cadao-doc-cung-be', n => n.scrollIntoView({ block: 'center' })); // người đọc cuộn tới nút rồi mới chạm
    await tuTab.click('.cadao-doc-cung-be');
    await cho(300);
    const be = () => tuTab.evaluate(() => { const d = document.querySelector('dialog.cadao-be'); return d && d.open ? { loi: d.querySelector('.cadao-be-dong').textContent, so: d.querySelector('.cadao-be-so').textContent, nhip: Array.from(d.querySelectorAll('.cadao-be-dong span')).findIndex(s => s.classList.contains('cadao-be-nhip')) } : null; });
    const b1 = await be();
    kiem(`Đọc cùng bé: mở ở dòng đầu (${b1?.so})`, b1 && b1.loi === 'Mẫu đồng dao một' && b1.so === 'Dòng 1 / 4');
    await tuTab.addScriptTag({ content: axeNguon });
    const axeBe = await tuTab.evaluate(the => window.axe.run(document, { runOnly: { type: 'tag', values: the } }), THE_WCAG);
    kiem(`Đọc cùng bé đang mở: không lỗi trợ năng (${axeBe.violations.map(v => v.id).join(', ')})`, axeBe.violations.length === 0);
    await tuTab.click('.cadao-be-dong');
    await cho(200);
    kiem('Đọc cùng bé: chạm vào chữ sang dòng sau', (await be())?.so === 'Dòng 2 / 4');
    await tuTab.click('.cadao-be-vo');
    const n0 = (await be())?.nhip;
    await cho(800);
    const n1 = (await be())?.nhip;
    kiem(`Đọc cùng bé: vỗ tay tô từng chữ theo nhịp (${n0} → ${n1})`, n0 === 0 && n1 === 1);
    await tuTab.keyboard.press('Escape');
    await cho(300);
    if (await be()) { await tuTab.keyboard.press('Escape'); await cho(300); }
    kiem('Đọc cùng bé: Esc thoát, focus về nút', await tuTab.evaluate(() => !document.querySelector('dialog.cadao-be').open && document.activeElement.classList.contains('cadao-doc-cung-be')));
  } else {
    kiem('đồng dao: có nút Đọc cùng bé', false);
  }
  // Dòng mức kiểm (T-119, D-072): ngay dưới lời, trước Hiểu nhanh; bài tự động "chưa đối chiếu", bài người biên soạn đối chiếu thì nêu sách
  const baiTay = bai.map(b => new URL(b.link).pathname).find(d => /bai-nhap-tay-mau/.test(d));
  await tuTab.goto(G + baiTay, { waitUntil: 'load' });
  kiem('mức kiểm: dòng đứng ngay sau lời, trước Hiểu nhanh; nêu sách đã đối chiếu', await tuTab.evaluate(() => { const m = document.querySelector('.cadao-muc-kiem'); return !!m && m.previousElementSibling?.tagName === 'PRE' && m.nextElementSibling?.classList.contains('cadao-hieu-nhanh') && /Lời đã đối chiếu với Sách mẫu tổng hợp, tr\. 12/.test(m.textContent); }));
  // SEO (T-050): mô tả, Open Graph, JSON-LD đọc được trong <head> của trang thật
  const seo = await tuTab.evaluate(() => {
    const ld = [...document.querySelectorAll('script[type="application/ld+json"]')].map(s => { try { return JSON.parse(s.textContent); } catch { return null; } });
    return { mt: document.querySelector('meta[name="description"]')?.content || '', og: document.querySelector('meta[property="og:title"]')?.content || '', ld: ld[0]?.['@graph']?.map(x => x['@type']) || [], loi: ld.includes(null), gen: !!document.querySelector('meta[name="generator"]') };
  });
  kiem(`SEO trang bài: mô tả, og:title, JSON-LD CreativeWork + BreadcrumbList (${seo.ld.join(', ')}), không lộ generator`, seo.mt.length > 20 && seo.og === 'Bài nhập tay mẫu' && seo.ld.includes('CreativeWork') && seo.ld.includes('BreadcrumbList') && !seo.loi && !seo.gen);
  await tuTab.goto(G + baiDiBan, { waitUntil: 'load' });
  kiem('mức kiểm: bài tự động ghi "chưa đối chiếu sách"', /chưa đối chiếu sách/.test(await tuTab.$eval('.cadao-muc-kiem', m => m.textContent).catch(() => '')));
  // Điều hướng, danh sách (T-120): trang Tất cả xếp A–Z, chip lọc, thanh dưới trên điện thoại
  await tuTab.goto(G + '/kho/', { waitUntil: 'load' });
  const ds = await tuTab.evaluate(() => ({
    h1: document.querySelector('h1')?.textContent.trim(),
    ten: [...document.querySelectorAll('.cadao-ds-bai h3')].map(h => h.textContent.trim()),
    duoi: (() => { const n = document.querySelector('.cadao-thanh-duoi'); const r = n?.getBoundingClientRect(); return n && getComputedStyle(n).display !== 'none' && r.bottom >= innerHeight - 1 ? n.querySelector('[aria-current="page"]')?.textContent.trim() : null; })(),
    menuTatCa: getComputedStyle(document.querySelector('.wp-block-navigation .cadao-nav-co-duoi')).display,
    phu: document.querySelectorAll('.cadao-ds-bai .cadao-ds-phu').length,
  }));
  kiem(`Tất cả: mỗi mục có dòng thể loại (${ds.phu}/${ds.ten.length}) và có đoạn lời`, ds.phu === ds.ten.length && await tuTab.evaluate(() => document.querySelectorAll('.cadao-ds-bai .cadao-ds-loi').length > 0));
  const khoa = t => t.toLowerCase().normalize('NFC').replace(/đ/g, 'd{').normalize('NFD').replace(/[̀-ͯ]/g, ''); // như cadao_khoa_xep()
  const azDung = ds.ten.length > 1 && ds.ten.every((t, i) => i === 0 || khoa(ds.ten[i - 1]) <= khoa(t));
  kiem(`Tất cả: tiêu đề "Tất cả bài", mục là h3, xếp A–Z (${ds.ten.slice(0, 4).join(' | ')})`, ds.h1 === 'Tất cả bài' && azDung);
  kiem(`điện thoại: thanh dưới hiện, "Tất cả" đang chọn (${ds.duoi}); menu trên ẩn mục đã có ở thanh dưới`, ds.duoi === 'Tất cả' && ds.menuTatCa === 'none');
  const chip = await tuTab.$eval('.cadao-chip', a => a.getAttribute('href')).catch(() => '');
  if (chip) {
    await tuTab.goto(chip.startsWith('http') ? chip : G + chip, { waitUntil: 'load' });
    kiem('danh sách đã lọc: noindex (tránh trang trùng lặp)', await tuTab.evaluate(() => /noindex/.test(document.querySelector('meta[name="robots"]')?.content || '')));
    kiem('lọc: bấm chip thì vẫn ở trang Tất cả, đường dẫn có tham số, chip có ✓ và aria-current, danh sách ít đi hoặc bằng', await tuTab.evaluate(n => /[?&](loai|the|mien|chude)=/.test(location.search) && document.querySelector('h1')?.textContent.trim() === 'Tất cả bài' && !!document.querySelector('.cadao-chip[aria-current="true"]') && document.querySelectorAll('.cadao-ds-bai h3').length <= n, ds.ten.length));
  } else {
    kiem('lọc: có chip để lọc', false);
  }
  const cuoi = await tuTab.evaluate(async () => { scrollTo(0, document.body.scrollHeight); await new Promise(r => setTimeout(r, 200)); const a = [...document.querySelectorAll('footer a')].pop(); const r = a.getBoundingClientRect(); return r.bottom <= document.querySelector('.cadao-thanh-duoi').getBoundingClientRect().top + 1; });
  kiem('thanh dưới không che liên kết cuối trang', cuoi);
  await tuTab.setViewport({ width: 1280, height: 900 });
  kiem('máy tính: không có thanh dưới', await tuTab.evaluate(() => getComputedStyle(document.querySelector('.cadao-thanh-duoi')).display === 'none'));
  await tuTab.setViewport({ width: 360, height: 800, isMobile: true, hasTouch: true });
  // Cuối bài (T-121): một khối "Bạn muốn góp gì?", không còn hai form mở sẵn
  await tuTab.goto(G + baiDiBan, { waitUntil: 'load' });
  kiem('cuối bài: một khối "Bạn muốn góp gì?", form cảm nghĩ thu gọn, Chủ đề/Vùng (nếu có) đứng trước khối', await tuTab.evaluate(() => document.querySelectorAll('.cadao-gop').length === 1 && !document.querySelector('.cadao-gop-hop').open && document.querySelectorAll('#commentform').length === 1 && [...document.querySelectorAll('.cadao-nhan-cuoi')].every(n => n.compareDocumentPosition(document.querySelector('.cadao-gop')) & Node.DOCUMENT_POSITION_FOLLOWING)));
  // Đọc tiếp (D-075): một khối riêng như các khối cuối bài khác — tiêu đề nằm trên danh sách, không mượn kiểu nút bo tròn
  // (lớp .cadao-doc-tiep từng trùng với nút "Đọc tiếp từ đoạn N" của bài dài nên khối thành một hình bầu dục viền son)
  kiem('cuối bài: khối "Đọc tiếp" là khối thường, tiêu đề ở trên danh sách, mỗi bài một ô ≥ 44 px', await tuTab.evaluate(() => {
    const s = document.querySelector('.cadao-doc-tiep');
    if (!s) return false;
    const cs = getComputedStyle(s);
    const h = s.querySelector('h2').getBoundingClientRect();
    const o = [...s.querySelectorAll('li a')].map(a => a.getBoundingClientRect());
    return cs.display === 'block' && parseFloat(cs.borderTopLeftRadius) === 0 && o.length > 0 && o.every(r => r.top >= h.bottom - 1 && r.height >= 44);
  }));
  // Bản đồ (T-104): điểm đủ lớn để chạm, chạm tới đúng địa danh trong danh sách; không tải gì từ nơi khác
  const ngoai = [];
  const ngheNgoai = r => { if (!r.url().startsWith(G) && !r.url().startsWith('data:')) ngoai.push(r.url()); };
  tuTab.on('request', ngheNgoai);
  await tuTab.goto(G + '/ban-do/', { waitUntil: 'load' });
  const diemBd = await tuTab.$$eval('.cadao-ban-do-diem', ds => ds.map(d => { const r = d.getBoundingClientRect(); return { w: r.width, h: r.height, href: d.getAttribute('href') }; }));
  kiem(`bản đồ: có điểm, mỗi điểm ≥ 24 px (${diemBd.map(d => d.w + '×' + d.h).join(', ')})`, diemBd.length >= 2 && diemBd.every(d => d.w >= 24 && d.h >= 24));
  if (diemBd.length) {
    await tuTab.click('.cadao-ban-do-diem');
    await cho(300);
    kiem('bản đồ: chạm điểm tới địa danh trong danh sách, có bài', await tuTab.evaluate(h => location.hash === h && !!document.querySelector(h + ' .cadao-tim-ds a'), diemBd[0].href));
  }
  tuTab.off('request', ngheNgoai);
  kiem(`bản đồ: không tải gì từ dịch vụ ngoài (${ngoai.slice(0, 3).join(', ')})`, ngoai.length === 0);
  // Góp ý (T-045): nút mở form, loại chọn sẵn
  await tuTab.goto(G + baiDiBan, { waitUntil: 'load' });
  await tuTab.click('a.cadao-gop-y-mo[data-loai="bao_loi"]');
  kiem('góp ý: bấm "Báo lỗi" mở form, loại chọn sẵn', await tuTab.evaluate(() => document.querySelector('.cadao-gop-y-hop').open && document.querySelector('input[name="loai"][value="bao_loi"]').checked));
  // Tìm (T-041): gõ không dấu, kết quả hiện khi gõ, phần khớp được tô
  await tuTab.goto(G + '/tim/', { waitUntil: 'load' });
  await tuTab.type('#cadao-tim-o', 'dong mau mot');
  await tuTab.waitForFunction(() => document.querySelectorAll('.cadao-tim-ds li').length > 0, { timeout: 15000 }).catch(() => {});
  kiem('tìm: gõ không dấu, kết quả hiện khi gõ, có tô phần khớp', await tuTab.evaluate(() => document.querySelectorAll('.cadao-tim-ds li').length > 0 && !!document.querySelector('.cadao-tim-ds mark') && /q=dong/.test(location.search)));
  // Đo lường: tìm không ra, đứng yên 2 giây → gửi một lần; lúc đang gõ dở không gửi
  const skTruoc = skTu.length;
  await tuTab.$eval('#cadao-tim-o', o => { o.value = ''; o.dispatchEvent(new Event('input')); o.focus(); });
  await cho(500);
  await tuTab.type('#cadao-tim-o', 'zzqx khong co cau nay', { delay: 60 });
  await choDk(() => skTu.length > skTruoc, 20000);
  await cho(2500); // không gửi thêm lần nào
  const skTim = skTu.slice(skTruoc).map(r => JSON.parse(r.postData() || '{}'));
  kiem(`đo lường: tìm không ra gửi một lần, đúng câu đã gõ xong (${skTim.map(x => x.q).join(' | ')})`, skTim.length === 1 && skTim[0].loai === 'tim_khong_ket_qua' && skTim[0].q === 'zzqx khong co cau nay');
  await ngCanh.close();
  console.log(`Thử trên trình duyệt (trích dẫn, lượt đọc, đã lưu, đo lường…): ${kiemThem} phép thử.`);
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
