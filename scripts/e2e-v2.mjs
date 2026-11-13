#!/usr/bin/env node
// Kiểm thử đầu-cuối V2 (docs/luu-tru/29-chuyen-doi-v2.md mục 4; lệnh ở docs/26 mục 13): đi từng thao tác như người dùng thật trong Chrome, trên điện thoại 390 px.
//   npm run e2e:v2                          bản xem trước (Playground, dữ liệu mẫu): người đọc rồi người biên soạn
//   npm run e2e:v2 -- --site https://…      site thật sau khi chuyển: chỉ người đọc, CHỈ ĐỌC — trình duyệt chặn mọi yêu cầu
//                                           không phải GET/HEAD (không gửi form, không lượt đọc, không sự kiện đo lường)
//   thêm --anh <thư-mục>: lưu ảnh chụp bước hỏng
// Mỗi bước in ĐẠT hoặc LỖI; thoát mã 1 khi có bước hỏng. Cần Chrome/Chromium; bản xem trước cần Node ≥ 24.18.
import fs from 'node:fs';
import path from 'node:path';
import puppeteer from 'puppeteer-core';
import { dangNhap, kiemNode, moPlayground, timChrome } from './lib/playground-v2.mjs';
import { kiemTrangSua } from './lib/e2e-trang-sua.mjs';
import { kiemEditorUrl } from './lib/e2e-editor-url.mjs';

const TEN = 'e2e v2';
const thamSo = ten => { const i = process.argv.indexOf(ten); return i > 0 ? process.argv[i + 1] : null; };
const SITE = thamSo('--site')?.replace(/\/+$/, '') || null;
if (SITE && !/^https?:\/\/[^/]+$/.test(SITE)) { console.log(`LỖI      [${TEN}] --site cần dạng https://ten-mien (không kèm đường dẫn)`); process.exit(1); }
const thuMucAnh = thamSo('--anh') ? path.resolve(thamSo('--anh')) : null;
if (thuMucAnh) fs.mkdirSync(thuMucAnh, { recursive: true });
if (!SITE) kiemNode(TEN);
const chrome = timChrome(TEN);
const may = SITE ? null : moPlayground(9433, TEN);
const G = SITE || may.G;

const loi = [];
const nhac = [];
let dat = 0;
let soAnh = 0;
let tabDangChay = null;
const cacTab = [];
const chac = (dk, bao) => { if (!dk) throw new Error(bao); };
const thu = async (ten, fn) => {
  try { await fn(); dat++; console.log(`ĐẠT      ${ten}`); }
  catch (e) {
    const chu = String(e.message ?? e).split('\n')[0];
    loi.push(`${ten}: ${chu}`);
    console.log(`LỖI      [${TEN}] ${ten}: ${chu}`);
    const treo = cacTab.flatMap(t => [...t.dangCho].map(([r, luc]) => `${t.nhan}: ${r.method()} ${r.url().replace(G, '')} (${Math.round((Date.now() - luc) / 1000)} giây)`));
    if (treo.length) console.log(`THÔNG TIN [${TEN}] yêu cầu chưa xong lúc hỏng: ${treo.join('; ')}`);
    if (thuMucAnh && tabDangChay) { try { await tabDangChay.screenshot({ path: path.join(thuMucAnh, `e2e-hong-${++soAnh}.png`), fullPage: true }); } catch { /* tab đã đóng */ } }
  }
};
const boQua = (ten, ly_do) => { nhac.push(`${ten}: ${ly_do}`); console.log(`NHẮC     [${TEN}] bỏ qua "${ten}": ${ly_do}`); };

// Điện thoại 390 px; lỗi JavaScript của trang tính là lỗi; site thật: chặn mọi yêu cầu ghi
let daChan = 0;
const moTab = async (ctx, nhan) => {
  const tab = await ctx.newPage();
  await tab.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  tab.on('pageerror', e => loi.push(`${nhan}: lỗi JavaScript trên ${tab.url().replace(G, '')} — ${String(e.message ?? e).split('\n')[0]}`));
  if (SITE) {
    await tab.setRequestInterception(true);
    tab.on('request', r => { if (['GET', 'HEAD'].includes(r.method())) r.continue(); else { daChan++; r.abort(); } });
  }
  // Chẩn đoán: yêu cầu tải trang / gọi máy chủ nào còn treo khi một bước hỏng (in ra ở lỗi của bước)
  const dangCho = new Map();
  tab.on('request', r => { if (['document', 'xhr', 'fetch'].includes(r.resourceType())) dangCho.set(r, Date.now()); });
  tab.on('requestfinished', r => dangCho.delete(r));
  tab.on('requestfailed', r => dangCho.delete(r));
  tab.dangCho = dangCho;
  tab.nhan = nhan;
  cacTab.push(tab);
  tabDangChay = tab;
  return tab;
};
const di = async (tab, duong, { trang = 200 } = {}) => {
  const tl = await tab.goto(G + duong, { waitUntil: 'load', timeout: 120_000 });
  if (trang) chac(tl.status() === trang, `${duong} trả ${tl.status()}`);
  await tab.evaluate(() => document.fonts.ready);
  return tl;
};
const doi = async (tab, fn, arg, bao, ms = 15_000) => {
  try { await tab.waitForFunction(fn, { timeout: ms }, arg); }
  catch { throw new Error(bao); }
};
// Đưa phần tử ra giữa màn trước khi bấm: thanh nổi ở đáy màn (wp-admin) không che chỗ bấm
const bam = async (tab, sel) => {
	await tab.bringToFront(); // hai tab: thao tác ở tab đang hiện như người dùng thực
  const node=await tab.$(sel);
  if(node)await node.evaluate(e=>{const parents=[];for(let d=e.closest('details');d;d=d.parentElement?.closest('details'))if(!d.open&&e!==d.querySelector(':scope>summary'))parents.push(d);parents.reverse().forEach(d=>d.querySelector(':scope>summary')?.click());});
  const b = await tab.waitForSelector(sel, { visible: true, timeout: 15_000 }).catch(() => null);
  chac(b, `không thấy ${sel}`);
  await b.evaluate(e => e.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'instant' }));
  await b.click();
};
// Bấm rồi chờ trang mới (form gửi thẳng, tải lại sau khi lưu). Không dựa vào sự kiện chuyển trang của puppeteer: Chrome mới
// có lúc thay khung trang khi gửi form rồi chuyển hướng, làm waitForNavigation treo 120 giây hoặc "frame detached" ngẫu
// nhiên ở các bước lô (#141–#145). Đánh dấu trang cũ, bấm, rồi chờ tới khi trang hiện tại là trang mới đã tải xong.
const bamDoiTrang = async (tab, sel) => {
  await tab.evaluate(() => { window.__trangCu = 1; });
  await bam(tab, sel);
  const het = Date.now() + 120_000;
  for (;;) {
    try { if (await tab.evaluate(() => !window.__trangCu && document.readyState === 'complete')) return; } catch { /* đang chuyển trang */ }
    if (Date.now() > het) throw new Error(`bấm ${sel} mà không sang trang mới sau 120 giây`);
    await new Promise(ok => setTimeout(ok, 200));
  }
};
const baoCo = (tab, chu) => doi(tab, c => (document.querySelector('#phongdao-bao')?.textContent || '').includes(c), chu, `không hiện thông báo "${chu}"`);
const coChu = (tab, chu) => tab.evaluate(c => document.body.innerText.includes(c), chu);
const datO = (tab, sel, gt) => tab.$eval(sel, (o, v) => { o.value = v; o.dispatchEvent(new Event('input', { bubbles: true })); }, gt);
const boDau = s => s.normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D');

// Trang để thử: bản xem trước dùng dữ liệu mẫu; site thật tìm trong danh sách thể loại bài đầu tiên có thứ cần thử
const MAU = {
  caDao: '/ca-dao/dong-mau-mot-cua-bai/', ve: '/ve/ve-mau/', cauDo: '/cau-do/cau-do-mau/',
  tuyenTap: '/tuyen-tap/gui-mot-loi-nhan-mau/', mucTu: '/tu-dien/ben-mau-bac/',
};
const timBai = async (tab, danhSach, sel, toiDa = 25) => {
  await di(tab, danhSach);
  const ds = await tab.$$eval('main a[href]', (as, g) => [...new Set(as.map(a => a.href).filter(h => h.startsWith(g)).map(h => new URL(h).pathname))], G);
  const ung = ds.filter(p => p.split('/').filter(Boolean).length === 2 && !/^\/(the-loai|tim|kho|chu-de|vung|tu-dien\/?$|tuyen-tap\/?$)/.test(p));
  for (const p of ung.slice(0, toiDa)) {
    await di(tab, p, { trang: 0 });
    if (await tab.$(sel)) return p;
  }
  return null;
};

let trinhDuyet;
try {
  if (may) await may.san;
  trinhDuyet = await puppeteer.launch({ executablePath: chrome, headless: true, args: ['--no-sandbox', '--font-render-hinting=none'] });
  const khach = await trinhDuyet.createBrowserContext();
  let chepDuoc = true;
  try { await khach.overridePermissions(G, ['clipboard-read', 'clipboard-write', 'clipboard-sanitized-write']); } catch { chepDuoc = false; }
  const k = await moTab(khach, 'người đọc');

  const T = { ...MAU };
  if (SITE) {
    T.caDao = await timBai(k, '/the-loai/ca-dao/', '.verse-card a.word') || await timBai(k, '/the-loai/ca-dao/', '.verse-card .line', 3);
    T.ve = await timBai(k, '/the-loai/ve/', '.bai-dai .doan[data-sau]', 40);
    T.cauDo = await timBai(k, '/the-loai/cau-do/', 'details.answer', 5);
    T.tuyenTap = await timBai(k, '/tuyen-tap/', '[data-action=present-set]', 5);
    T.mucTu = await timBai(k, '/tu-dien/', '.muc-tu, h1', 3);
    console.log(`Site thật ${SITE} — trang thử: ${Object.entries(T).map(([a, b]) => `${a} ${b || '(không có)'}`).join(', ')}`);
  }
  const caDao = T.caDao;

  // —— Người đọc ——
  await thu('Trang chủ: chọn "Bạn là" rồi tải lại, máy nhớ', async () => {
    await di(k, '/');
    await bam(k, '[data-action=role][data-value="Giáo viên"]');
    await k.reload({ waitUntil: 'load' });
    chac(await k.$eval('[data-action=role][data-value="Giáo viên"]', b => b.getAttribute('aria-pressed')) === 'true', 'tải lại thì mất lựa chọn');
    await bam(k, '[data-action=role-reset]');
  });

  await thu('Liên kết trong menu, chân trang, thanh dưới, trang chủ, Khám phá, trang bài không hỏng', async () => {
    const ds = new Set();
    for (const p of ['/', '/kham-pha/', ...(caDao ? [caDao] : [])]) {
      await di(k, p);
      for (const h of await k.$$eval('a[href]', (as, g) => as.map(a => a.href).filter(h => h.startsWith(g)), G)) {
        const u = new URL(h); u.hash = '';
        if (!/^\/(bien-tap|wp-admin|wp-login)/.test(u.pathname) && !u.searchParams.has('phongdao_anh')) ds.add(u.pathname + u.search);
      }
    }
    const hong = [];
    for (const p of [...ds].slice(0, 150)) {
      const ma = await k.evaluate(async u => { try { return (await fetch(u, { redirect: 'follow' })).status; } catch { return 0; } }, G + p);
      if (ma >= 400 || ma === 0) hong.push(`${p} (${ma})`);
    }
    chac(!hong.length, `${hong.length}/${ds.size} liên kết hỏng: ${hong.slice(0, 8).join(', ')}`);
  });

  if (!caDao) boQua('các bước trang bài', 'không tìm thấy bài ca dao nào');
  else {
    await di(k, caDao);
    const tieuDe = await k.$eval('[data-action=save]', b => b.dataset.tieuDe);
    const dongDau = await k.$eval('.verse-card .line', l => [...l.childNodes].filter(x => !(x.classList && x.classList.contains('ln'))).map(x => x.textContent).join('').replace(/\s+/g, ' ').trim());

    await thu('Tìm: gõ không dấu, kết quả hiện ngay; Enter vẫn ra trang kết quả', async () => {
      const q = boDau(dongDau).split(' ').slice(0, 4).join(' ');
      await di(k, '/tim/');
      await k.type('#query', q);
      await doi(k, p => !!document.querySelector(`#search-results a[href$="${p}"]`), caDao, `gõ "${q}" không ra ${caDao}`);
      await Promise.all([k.waitForNavigation({ waitUntil: 'load' }), k.keyboard.press('Enter')]);
      chac(k.url().includes('q='), 'Enter không mở trang kết quả');
      chac(await k.$(`#search-results a[href$="${caDao}"]`), 'trang kết quả (máy chủ dựng) thiếu bài');
    });

    await thu('Trang bài: Sao chép lời kèm đường dẫn', async () => {
      await di(k, caDao);
      await k.bringToFront();
      await bam(k, '.verse-card [data-action=copy-loi]');
      if (!chepDuoc) { await baoCo(k, 'sao chép'); return; }
      await baoCo(k, 'Đã sao chép lời');
      const chu = await k.evaluate(() => navigator.clipboard.readText());
      chac(chu.includes(dongDau) && chu.includes(G + caDao), `bộ nhớ tạm thiếu lời hoặc đường dẫn: ${chu.slice(0, 80)}`);
    });

    await thu('Lưu bài → có ở Đã lưu → Bỏ lưu', async () => {
      await bam(k, '[data-action=save]');
      await baoCo(k, 'Đã lưu trên máy này');
      await di(k, '/da-luu/');
      await doi(k, t => [...document.querySelectorAll('[data-ds] a')].some(a => a.textContent.includes(t)), tieuDe, 'Đã lưu không có bài vừa lưu');
      await bam(k, '[data-ds] button');
      await doi(k, () => !document.querySelector('[data-ds] a') && !document.querySelector('[data-trong]').hidden, null, 'Bỏ lưu xong vẫn còn bài');
    });

    await thu('Trích dẫn: đổi sang APA, Esc đóng và trả tiêu điểm về nút', async () => {
      await di(k, caDao);
      await bam(k, '[data-action=cite]');
      await bam(k, 'dialog[open] [data-kieu=apa]');
      chac((await k.$eval('dialog[open] textarea', o => o.value)).includes('Truy cập ngày'), 'chữ trích dẫn không đổi sang APA');
      await k.keyboard.press('Escape');
      await doi(k, () => !document.querySelector('dialog[open]') && document.activeElement?.dataset.action === 'cite', null, 'Esc không đóng hộp hoặc mất tiêu điểm');
    });

    await thu('Chia sẻ: hộp có liên kết đúng bài', async () => {
      await bam(k, '[data-action=share]');
      chac((await k.$eval('dialog[open] textarea', o => o.value)) === G + caDao, 'liên kết trong hộp chia sẻ không đúng bài');
      await k.keyboard.press('Escape');
    });

    await thu('Cỡ chữ Rất lớn và sáng/tối: tải lại vẫn giữ', async () => {
      await bam(k, '[data-action=aa]');
      await bam(k, 'dialog[open] [data-size="34"]');
      const toiTruoc = await k.evaluate(() => document.documentElement.dataset.theme || '');
      await bam(k, 'dialog[open] [data-action=theme]');
      const toi = await k.evaluate(() => document.documentElement.dataset.theme);
      chac(toi && toi !== toiTruoc, 'bấm Đổi sáng / tối không đổi');
      await k.reload({ waitUntil: 'load' });
      const sau = await k.evaluate(() => [document.documentElement.style.getPropertyValue('--reading-size'), document.documentElement.dataset.theme]);
      chac(sau[0] === '34px', `cỡ chữ sau khi tải lại: ${sau[0] || 'mặc định'}`);
      chac(sau[1] === toi, `sáng/tối sau khi tải lại: ${sau[1] || 'theo máy'}`);
      // trả về như cũ cho các bước sau
      await k.evaluate(() => { localStorage.removeItem('phongdao-co-chu'); localStorage.removeItem('phongdao-giao-dien'); });
    });

    await di(k, caDao);
    if (!(await k.$('.verse-card a.word'))) boQua('Chạm từ khó', 'bài thử không có từ khó trong lời');
    else await thu('Chạm từ khó trong lời: ô chú thích hiện ngay chỗ đọc', async () => {
      const tu = await k.$eval('.verse-card a.word', a => a.textContent.trim());
      await bam(k, '.verse-card a.word');
      await doi(k, t => { const d = document.querySelector('dialog.word-dialog[open]'); return d && d.querySelector('h2').textContent.trim() === t && d.querySelector('.small').textContent.trim().length > 0; }, tu, `chạm "${tu}" không mở ô chú thích`);
      await k.keyboard.press('Escape');
    });

    await thu('Tắt JavaScript: vẫn đọc được lời, nút cần JavaScript ẩn', async () => {
      const ctx = await trinhDuyet.createBrowserContext();
      const t = await moTab(ctx, 'người đọc (tắt JavaScript)');
      await t.setJavaScriptEnabled(false);
      await di(t, caDao);
      chac(await coChu(t, dongDau), 'không thấy lời');
      chac(await t.$$eval('[data-chi-js], [data-action=mong-co-cau]', bs => bs.every(b => b.hidden)), 'nút chỉ chạy bằng JavaScript vẫn hiện');
      // Hiểu thêm: tắt JavaScript thì ba phần nằm liền nhau, mỗi phần một tiêu đề, không phần nào bị giấu
      if (await t.$('[data-hieu-them]')) chac(3 === await t.$$eval('[data-hieu-them] .ht-phan', ps => ps.filter(p => !p.hidden && p.offsetParent).length) && await coChu(t, 'Cách hiểu của bạn đọc'), 'tắt JavaScript: tầng Hiểu thêm không hiện đủ ba phần');
      await ctx.close();
      tabDangChay = k;
    });
  }

  if (!T.ve) boQua('Vè dài', 'không có bài vè nhiều phần');
  else await thu('Vè dài: thu gọn sau dòng 30, Mục lục mở đúng đoạn, "Đọc toàn bài" mở hết', async () => {
    await di(k, T.ve);
    chac(await k.$('.bai-dai.thu-gon'), 'bài dài không thu gọn');
    chac(await k.$eval('.doan[data-sau]', d => !d.offsetParent), 'đoạn sau vẫn hiện khi thu gọn');
    await bam(k, 'details.muc-luc summary');
    const dich = await k.$$eval('details.muc-luc a[href^="#"]', as => as.at(-1).getAttribute('href').slice(1));
    await bam(k, `details.muc-luc a[href="#${dich}"]`);
    await doi(k, id => !document.querySelector('.bai-dai.thu-gon') && document.getElementById(id)?.offsetParent, dich, 'Mục lục không mở đoạn cuối');
    await k.evaluate(() => localStorage.removeItem('cadao-doc-den-' + document.querySelector('.bai-dai').dataset.bai));
    await di(k, T.ve);
    await bam(k, '.doc-toan-bai');
    await doi(k, () => !document.querySelector('.bai-dai.thu-gon') && document.activeElement?.matches('.doan[data-sau]'), null, '"Đọc toàn bài" không mở hết hoặc không đưa tiêu điểm tới đoạn sau');
  });

  if (!T.cauDo) boQua('Câu đố', 'không có câu đố có đáp án');
  else await thu('Câu đố: đáp án ẩn, bấm "Hiện đáp án" thì hiện', async () => {
    await di(k, T.cauDo);
    chac(await k.$eval('details.answer', d => !d.open), 'đáp án mở sẵn');
    await bam(k, 'details.answer summary');
    chac(await k.$eval('details.answer', d => d.open && d.querySelector('.detail-body p').textContent.trim().length > 0), 'bấm không hiện đáp án');
  });

  if (!T.tuyenTap) boQua('Trình chiếu tuyển tập', 'không có tuyển tập');
  else await thu('Tuyển tập: Trình chiếu cả bộ, Tiếp sang bài 2, Esc thoát', async () => {
    await di(k, T.tuyenTap);
    await bam(k, '[data-action=present-set]');
    await doi(k, () => /^1 \/ \d+$/.test(document.querySelector('.fullscreen-mode .mode-progress')?.textContent || ''), null, 'không mở trình chiếu');
    const tong = await k.$eval('.mode-progress', s => +s.textContent.split('/')[1]);
    if (tong > 1) { await bam(k, '.mode-next'); await doi(k, () => document.querySelector('.mode-progress').textContent.startsWith('2 /'), null, 'Tiếp không sang bài 2'); }
    await k.keyboard.press('Escape');
    await doi(k, () => !document.querySelector('.fullscreen-mode'), null, 'Esc không thoát');
  });

  if (!SITE) {
    let baiGop = 0;
    await thu('Góp: bảy loại hiện đúng ô, giữ chữ khi đổi loại, chọn loại bằng bàn phím', async () => {
      await di(k, '/ca-dao/dong-mau-mot-cua-bai/');
      baiGop = await k.$eval('#cach-hieu-form input[name=bai]', e => +e.value);
      await di(k, `/gop/?loai=vi_pham&bai=${baiGop}&vi_pham=17`);
      chac(await k.$eval('input[name=vi_pham]', e => e.value === '17'), 'mất đối tượng báo vi phạm');
      await k.focus('.gop-chon-loai > summary');
      await k.keyboard.press('Enter');
      chac(await k.$eval('.gop-chon-loai', e => e.open), 'bàn phím không mở được bộ chọn');
      const nhap = '[UX-TEST] Chữ đang nhập khi đổi loại.';
      await datO(k, '#gop-noi-dung', nhap);
      const thay = async s => k.$eval(s, e => !!e.getClientRects().length && getComputedStyle(e).visibility !== 'hidden');
      for (const loai of ['di_ban', 'chu_thich', 'bao_loi', 'so_trang', 'cam_nghi', 'phan_tich', 'vi_pham']) {
        await bam(k, `input[name=loai][value=${loai}]`);
        chac(await thay('#gop-sach') === (loai === 'so_trang'), `${loai}: hiện sai ô sách`);
        chac(await thay('#gop-tieu-de') === (loai === 'phan_tich'), `${loai}: hiện sai tiêu đề`);
        chac(await thay('input[name=tu_viet]') === (loai === 'phan_tich'), `${loai}: hiện sai cam kết tự viết`);
        chac(await k.$eval('#gop-noi-dung', (e, chu) => e.value === chu, nhap), `${loai}: mất chữ khi đổi loại`);
      }
      await bam(k, 'input[name=loai][value=bao_loi]');
      await k.keyboard.press('ArrowDown');
      chac(await k.$eval('input[name=loai]:checked', e => e.value === 'so_trang'), 'phím mũi tên không đổi loại');
      await bam(k, 'input[name=loai][value=bao_loi]');
      await bam(k, '.gop-chon-loai > summary');
      for (const width of [390, 1440]) {
        await k.setViewport({ width, height: 844, isMobile: width < 640, hasTouch: width < 640 });
        chac(await k.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), `Góp tràn ngang ở ${width}`);
        if (thuMucAnh) await k.screenshot({ path: path.join(thuMucAnh, `gop-bao-loi-${width}.png`), fullPage: true });
      }
      await k.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
    });
    await thu('Góp không JavaScript: lỗi nonce giữ nguyên chữ, lựa chọn và bút danh, phản hồi không bị đệm', async () => {
      await k.setJavaScriptEnabled(false);
      try {
        await di(k, `/gop/?loai=phan_tich&bai=${baiGop}`);
        const chu = '[UX-TEST] </textarea><script>window.__gopXss=1</script>\nChữ cần giữ khi gửi lỗi.';
        await datO(k, '#gop-tieu-de', 'Tiêu đề "mẫu"');
        await datO(k, '#gop-noi-dung', chu);
        await bam(k, '.gop-than > details:not([data-gop-loai]) > summary');
        await datO(k, '#gop-ten', 'Bút danh mẫu');
        await k.select('#gop-vai', 'giao_vien');
        await bam(k, 'input[name=tu_viet]');
        await bam(k, 'input[name=dong_y]');
        await k.$eval('input[name=_wpnonce]', e => { e.value = 'phien-qua-han'; });
        const phanHoi = k.waitForResponse(r => r.request().method() === 'POST' && new URL(r.url()).pathname === '/gop/');
        await bamDoiTrang(k, '#gop-form [type=submit]');
        const tl = await phanHoi;
        chac(tl.status() === 422 && /no-store/.test(tl.headers()['cache-control'] || ''), 'lỗi không trả 422/no-store');
        chac(await k.$eval('#gop-noi-dung', (e, c) => e.value === c, chu), 'lỗi làm mất hoặc sửa chữ đã nhập');
        chac(await k.$eval('#gop-tieu-de', e => e.value === 'Tiêu đề "mẫu"'), 'mất tiêu đề');
        chac(await k.$eval('#gop-ten', e => e.value === 'Bút danh mẫu') && await k.$eval('#gop-vai', e => e.value === 'giao_vien'), 'mất thông tin phụ');
        chac(await k.$eval('input[name=tu_viet]', e => e.checked) && await k.$eval('input[name=dong_y]', e => e.checked), 'mất cam kết người gửi đã chọn');
        chac(await k.$eval('input[name=bai]', (e, id) => +e.value === id, baiGop), 'mất bài khi gửi lỗi');
        chac(await k.evaluate(() => ![...document.scripts].some(s => s.textContent.includes('window.__gopXss=1'))), 'chữ nhập trở thành thẻ script');
        chac(await coChu(k, 'Phiên gửi đã hết hạn'), 'không thấy phản hồi cạnh form');
        await bam(k, '.gop-chon-loai > summary');
        await bam(k, 'input[name=loai][value=bao_loi]');
        chac(await k.$eval('#gop-tieu-de', e => !e.getClientRects().length), 'tắt JavaScript vẫn hiện trường không liên quan');
        chac(await k.$eval('#gop-noi-dung', (e, c) => e.value === c, chu), 'tắt JavaScript đổi loại làm mất chữ');
        if (thuMucAnh) await k.screenshot({ path: path.join(thuMucAnh, 'gop-loi-no-js-390.png'), fullPage: true });
      } finally { await k.setJavaScriptEnabled(true); }
    });
    await thu('Góp: báo vi phạm gửi lỗi vẫn giữ đúng bài và đối tượng được báo', async () => {
      await di(k, `/gop/?loai=vi_pham&bai=${baiGop}&vi_pham=17`);
      await datO(k, '#gop-noi-dung', '[UX-TEST] Vấn đề cần người biên soạn kiểm tra.');
      await bam(k, 'input[name=dong_y]');
      await k.$eval('input[name=_wpnonce]', e => { e.value = 'phien-qua-han'; });
      await bamDoiTrang(k, '#gop-form [type=submit]');
      chac(await k.$eval('input[name=vi_pham]', e => e.value === '17'), 'mất đối tượng sau lỗi');
      chac(await k.$eval('input[name=bai]', (e, id) => +e.value === id, baiGop), 'mất bài sau lỗi');
      chac(await k.$eval('input[name=loai]:checked', e => e.value === 'vi_pham'), 'đổi loại sau lỗi');
      chac(await k.$eval('#gop-noi-dung', e => e.value === '[UX-TEST] Vấn đề cần người biên soạn kiểm tra.'), 'mất lý do sau lỗi');
    });
    await thu('Góp không JavaScript: gửi chỉ số trang được, không cần viết ghi chú', async () => {
      await k.setJavaScriptEnabled(false);
      try {
        await di(k, `/gop/?loai=so_trang&bai=${baiGop}`);
        await datO(k, '#gop-trang', '12');
        await bam(k, 'input[name=dong_y]');
        await bamDoiTrang(k, '#gop-form [type=submit]');
        chac(await coChu(k, 'Cảm ơn bạn!'), 'gửi số trang không được');
        chac(new URL(k.url()).pathname === '/ca-dao/dong-mau-mot-cua-bai/', 'không quay về bài đã góp');
      } finally { await k.setJavaScriptEnabled(true); }
    });
  }

  if (SITE) boQua('Góp của người đọc', 'chế độ chỉ đọc không gửi form');
  else await thu('Góp: thiếu đồng ý bị chặn, giữ chữ, sửa rồi gửi được và hiện lời cảm ơn', async () => {
    await di(k, '/gop/?loai=bao_loi');
    await datO(k, '#gop-noi-dung', '[UX-TEST] Chưa đồng ý sử dụng nội dung.');
    await bam(k, 'input[name=dong_y]');
    await k.$eval('input[name=web]', e => { e.name = 'web[0]'; e.value = 'rac'; });
    await bamDoiTrang(k, '#gop-form [type=submit]');
    chac(await coChu(k, 'Chưa gửi được, xin thử lại.'), 'ô bẫy sai dạng mảng bị coi như trống');
    await bam(k, 'input[name=dong_y]');
    // Cố ý bỏ kiểm tra HTML để kiểm cổng ở máy chủ; không thay kiểm tra của sản phẩm.
    await k.$eval('#gop-form', e => { e.noValidate = true; });
    await bamDoiTrang(k, '#gop-form [type=submit]');
    chac(await coChu(k, 'đánh dấu đồng ý'), 'máy chủ không chặn thiếu đồng ý');
    chac(await k.$eval('#gop-noi-dung', e => e.value === '[UX-TEST] Chưa đồng ý sử dụng nội dung.'), 'lỗi thiếu đồng ý làm mất chữ');
    chac(await k.$eval('input[name=dong_y]', e => !e.checked), 'tự đánh dấu đồng ý hộ người gửi');
    await datO(k, '#gop-noi-dung', 'Góp thử đầu-cuối: dòng hai đúng ra là chữ khác.');
    await bam(k, 'input[name=dong_y]');
    await bamDoiTrang(k, '#gop-form [type=submit]');
    chac(await coChu(k, 'Cảm ơn bạn!'), 'không hiện lời cảm ơn');
  });

  // —— Người biên soạn (chỉ bản xem trước) ——
  if (!SITE) {
    const bt = await moTab(trinhDuyet.defaultBrowserContext(), 'người biên soạn');
    chac(await dangNhap(bt, G, TEN), 'không đăng nhập được người biên soạn');
    const yNghia = 'Ý nghĩa thử đầu-cuối do người biên soạn viết.';

    await thu('Trang biên tập cũ /bien-tap/… chuyển về wp-admin; lối gốc WordPress của tác phẩm mở màn Phongdao', async () => {
      const den = async (duong, page, them = {}) => {
        await di(bt, duong);
        const u = new URL(bt.url());
        chac(u.pathname.endsWith('/wp-admin/admin.php') && u.searchParams.get('page') === page && Object.entries(them).every(([k2, v]) => u.searchParams.get(k2) === v), `${duong} không mở ${page} (${bt.url()})`);
      };
      await den('/bien-tap/', 'phongdao-hop-viec');
      await den('/bien-tap/?loai=nguoi_doc&tt=de_sau', 'phongdao-hop-viec', { loai: 'nguoi_doc', tt: 'de_sau' });
      await den('/bien-tap/tac-pham/?loc=thieu-y-nghia', 'phongdao-tac-pham', { muc: 'thieu_y_nghia' });
      await den('/bien-tap/them/', 'phongdao-them');
      await den('/bien-tap/so-lieu/', 'phongdao-ban-lam-viec');
      await den('/wp-admin/edit.php?post_type=tac_pham', 'phongdao-tac-pham');
      await den('/wp-admin/post-new.php?post_type=tac_pham', 'phongdao-them');
      // Menu trái vẫn đủ: mục Tác phẩm của Phongdao, và menu gốc của WordPress (Bài viết, Trang, Bình luận) không bị ẩn
      chac(await bt.$('#adminmenu a[href*="page=phongdao-tac-pham"]') && await bt.$('#adminmenu a[href="edit.php"]') && await bt.$('#adminmenu a[href="edit-comments.php"]'), 'menu trái thiếu mục Tác phẩm của Phongdao hoặc menu gốc WordPress bị ẩn');
      // Khách mở /bien-tap/ thì sang trang đăng nhập (wp-admin tự đòi đăng nhập), không thấy Hộp việc
      await di(k, '/bien-tap/');
      chac(/wp-login\.php/.test(k.url()) && !(await coChu(k, 'Hộp việc')), `khách mở /bien-tap/ không sang trang đăng nhập (${k.url()})`);
    });

    await thu('Câu đố thiếu giải thích: danh sách ở wp-admin Tác phẩm dẫn tới nút Thêm ý nghĩa, khách không thấy lời nhắc thiếu', async () => {
      await di(bt, '/wp-admin/admin.php?page=phongdao-tac-pham&muc=thieu_giai_thich');
      const href = await bt.$eval('#wpbody-content a[href*="/cau-do/cau-do-mau/"]', e => e.href).catch(() => '');
      chac(href, 'danh sách Câu đố thiếu giải thích không có câu đố mẫu');
      await bt.goto(href, { waitUntil: 'load', timeout: 120_000 });
      chac(await bt.$('button[data-tam="y_nghia"]') && await coChu(bt, 'Thêm giải thích đáp án'), 'trang câu đố không cho người biên soạn thêm Giải thích đáp án');
      await di(k, T.cauDo);
      chac(!(await k.$('button[data-tam="y_nghia"]')) && !(await coChu(k, 'Bài đang được bổ sung ý nghĩa')) && !(await coChu(k, 'Câu này chưa có lời giải nghĩa')), 'khách thấy điều khiển hoặc lời nhắc thiếu Ý nghĩa');
    });

    await thu('Biên tập: sửa ý nghĩa chưa duyệt → nháp, người đọc chưa thấy', async () => {
      await di(bt, caDao);
      await bam(bt, '[data-action=mo-tam][data-tam=y_nghia]');
      await datO(bt, 'dialog#tam-y_nghia[open] textarea[name=chu]', yNghia);
      // Hai nút lưu thay ô "đã duyệt" (D-102): bấm "Lưu nháp"
      chac(await bt.$('dialog#tam-y_nghia button[name=duyet][value="1"]') && !(await bt.$('dialog#tam-y_nghia input[name=duyet]')), 'tấm sửa Ý nghĩa chưa có hai nút "Lưu nháp" / "Lưu và hiện cho người đọc"');
      await bamDoiTrang(bt, 'dialog#tam-y_nghia button[name=duyet][value=""]');
      await baoCo(bt, 'nháp vào Hộp việc');
      // Dải "Đang sửa" có lối "Mở trang sửa đầy đủ" (wp-admin Sửa tác phẩm của đúng bài)
      const id = await bt.$eval('.dai-bien-tap', e => e.dataset.bai);
      chac(await bt.$(`.dai-bien-tap a[href*="page=phongdao-sua-tac-pham"][href*="id=${id}"]`), 'dải Đang sửa thiếu lối "Mở trang sửa đầy đủ"');
      await di(k, caDao);
      chac(!(await coChu(k, yNghia)), 'người đọc đã thấy nháp');
    });

    await thu('wp-admin Hộp việc: lọc thẻ, mở đúng form quyết và dùng được ở điện thoại', async () => {
      await di(bt, '/wp-admin/admin.php?page=phongdao-hop-viec&tt=cho&loai=nhap_y_nghia');
      chac(await bt.$('.phongdao-hv-ds a[href*="viec="]'), 'danh sách admin không có thẻ nháp');
      const dich = await bt.$eval('.phongdao-hv-ds a[href*="viec="]', e => e.href);
      await bt.goto(dich, { waitUntil: 'load', timeout: 120_000 });
      chac(await bt.$('.phongdao-admin-viec article.the-viec form input[name="_wpnonce"]') && await bt.$('.phongdao-admin-viec article.the-viec form input[name="phien"]'), 'thẻ admin không dùng form quyết có nonce và phiên');
      await bt.setViewport({ width: 390, height: 844 });
      chac(await bt.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'màn Hộp việc admin tràn ngang ở 390 px');
      // Điện thoại: đã mở một thẻ thì chỉ hiện thẻ đó (danh sách ẩn), có lối về danh sách
      chac(await bt.$eval('.phongdao-hv-trai', e => !e.offsetParent) && await bt.$eval('.phongdao-hv-ve a', e => !!e.offsetParent), 'điện thoại: mở thẻ mà danh sách vẫn chiếm màn hoặc thiếu lối về danh sách');
      await bt.setViewport({ width: 1280, height: 800 });
    });

    await thu('wp-admin Hộp việc: phím số chọn nút, phím tắt không kích hoạt khi đang nhập hoặc focus nút', async () => {
      await di(bt, '/wp-admin/admin.php?page=phongdao-hop-viec');
      chac(await bt.$('.phongdao-hv-phai article.the-viec form button[name="quyet"]'), 'không có thẻ mở sẵn để thử phím tắt');
      const veDau = bt.url();
      await bt.evaluate(() => {
        window.__phimQuyet = [];
        // Chặn gửi ở pha bắt (trước cả bộ gửi không tải lại trang): chỉ ghi nút được chọn
        window.addEventListener('submit', e => { e.preventDefault(); e.stopImmediatePropagation(); window.__phimQuyet.push(e.submitter?.value); }, true);
        const o = document.createElement('input');
        o.id = 'kiem-phim-nhap';
        document.querySelector('.phongdao-hv-phai').prepend(o);
        o.focus();
      });
      await bt.keyboard.press('j');
      await bt.keyboard.press('1');
      chac(bt.url() === veDau && 0 === await bt.evaluate(() => window.__phimQuyet.length), 'phím tắt chạy khi đang nhập');
      await bt.focus('.phongdao-hv-phai form button[name="quyet"]');
      await bt.keyboard.press('j');
      chac(bt.url() === veDau, 'J chạy khi focus ở nút');
      const nut = await bt.$$eval('.phongdao-hv-phai form button[name="quyet"]:not([disabled])', ds => ds.slice(0, 3).map(n => n.value));
      await bt.evaluate(() => { document.getElementById('kiem-phim-nhap').remove(); document.activeElement.blur(); });
      for (let i = 0; i < nut.length; i++) await bt.keyboard.press(String(i + 1));
      chac(JSON.stringify(await bt.evaluate(() => window.__phimQuyet)) === JSON.stringify(nut), 'phím 1/2/3 không chọn đúng nút');
    });

    await thu('wp-admin Hộp việc hộp thư: danh sách và thẻ cạnh nhau, J/K đổi thẻ, quyết xong tự sang thẻ kế, Hoàn tác về đúng thẻ', async () => {
      await di(bt, '/wp-admin/admin.php?page=phongdao-hop-viec');
      const ds = await bt.$$eval('.phongdao-hv-ds .phongdao-hv-mo', as => as.map(a => new URL(a.href).searchParams.get('viec')));
      chac(ds.length >= 2, `Hộp việc không đủ 2 thẻ để thử (thấy ${ds.length})`);
      const [trai, phai] = await bt.evaluate(() => ['.phongdao-hv-trai', '.phongdao-hv-phai'].map(s => document.querySelector(s).getBoundingClientRect().toJSON()));
      chac(phai.width > 0 && phai.left >= trai.right - 1, 'màn rộng không đặt danh sách và thẻ cạnh nhau');
      chac(await bt.$(`.phongdao-hv-phai article#viec-${ds[0]}`), 'màn rộng không mở sẵn thẻ đầu danh sách');
      chac(await bt.$eval('.phongdao-hv-phai [data-hv="goi-y"]', e => !e.hidden), 'không hiện hướng dẫn phím tắt khi JavaScript chạy');
      await bt.evaluate(() => document.activeElement?.blur());
      await Promise.all([bt.waitForNavigation({ waitUntil: 'load' }), bt.keyboard.press('j')]);
      chac(new URL(bt.url()).searchParams.get('viec') === ds[1] && await bt.$(`.phongdao-hv-phai article#viec-${ds[1]}`), 'J không mở thẻ sau');
      await Promise.all([bt.waitForNavigation({ waitUntil: 'load' }), bt.keyboard.press('k')]);
      chac(new URL(bt.url()).searchParams.get('viec') === ds[0], 'K không về thẻ trước');
      // Để sau một thẻ người đọc gửi: tự sang thẻ kế, lời báo có Hoàn tác; Hoàn tác đưa về đúng thẻ đó, thẻ trở lại Chờ
      await di(bt, '/wp-admin/admin.php?page=phongdao-hop-viec&loai=nguoi_doc');
      const id = await bt.$eval('.phongdao-hv-phai article.the-viec', a => a.id.replace('viec-', '')).catch(() => '');
      chac(id, 'không có thẻ Người đọc gửi để thử');
      await bamDoiTrang(bt, '.phongdao-hv-phai button[name=quyet][value=de_sau]');
      chac(new URL(bt.url()).searchParams.get('vua') === id && await bt.$('[data-hv-hoan] button:not([disabled])'), 'quyết xong không sang thẻ kế kèm nút Hoàn tác còn hạn');
      chac(!(await bt.$(`.phongdao-hv-phai article#viec-${id}`)), 'quyết xong vẫn mở thẻ vừa quyết');
      await bamDoiTrang(bt, '[data-hv-hoan] button');
      chac(new URL(bt.url()).searchParams.get('viec') === id && await bt.$(`.phongdao-hv-phai article#viec-${id} button[name=quyet][value=de_sau]`), 'Hoàn tác không đưa về đúng thẻ vừa quyết ở Chờ');
    });

    await thu('wp-admin Hộp việc: Duyệt nháp ý nghĩa → người đọc thấy, thẻ đóng không hiện nút quyết', async () => {
      await di(bt, `/wp-admin/admin.php?page=phongdao-hop-viec&tt=cho&loai=nhap_y_nghia&q=${encodeURIComponent(yNghia)}`);
      const dich = await bt.$eval('.phongdao-hv-ds a[href*="viec="]', e => e.href).catch(() => '');
      chac(dich, 'không tìm thấy đúng thẻ nháp ý nghĩa trong admin');
      await bt.goto(dich, { waitUntil: 'load', timeout: 120_000 });
      chac(await coChu(bt, yNghia), 'chi tiết thẻ không có đúng nháp cần duyệt');
      // Sửa vài chữ ngay trên thẻ rồi Duyệt; quyết không tải lại trang (dấu đặt trên trang còn nguyên sau khi quyết)
      chac(await bt.$('.phongdao-admin-viec article.the-viec textarea.nhap-sua'), 'thẻ nháp không có ô sửa trước khi duyệt');
      await datO(bt, '.phongdao-admin-viec article.the-viec textarea.nhap-sua', yNghia + ' Đã sửa trên thẻ.');
      await bt.evaluate(() => { window.__khongTaiLai = 1; });
      await bamDoiTrang(bt, '.phongdao-admin-viec article.the-viec button[name=quyet][value=duyet]');
      chac(await coChu(bt, 'Đã sửa và duyệt'), 'quyết trong admin không báo đã sửa và duyệt');
      chac(await bt.evaluate(() => window.__khongTaiLai === 1), 'quyết thẻ mà trang tải lại (cần chỉ thay phần thẻ)');
      // Quyết xong tự sang thẻ kế (ở đây hết thẻ khớp tìm); xem lại thẻ vừa quyết qua lối trong lời báo
      await bamDoiTrang(bt, '.phongdao-hv-bao a');
      chac(!(await bt.$('.phongdao-admin-viec article.the-viec button[name=quyet]')), 'thẻ đã quyết còn nút quyết');
      // Quyết xong không phải ngõ cụt: ghi nhãn quyết định, có lối sang bài và sang thẻ kế (hoặc báo hết thẻ)
      chac(await coChu(bt, 'Đã quyết: Duyệt') && !(await bt.$eval('.phongdao-viec-tt', e => e.textContent.includes('Phiên'))), 'thẻ đã quyết chưa ghi nhãn quyết định dễ đọc');
      chac(!(await bt.$eval('article.the-viec', e => e.textContent.includes('chưa duyệt') || e.textContent.includes('người đọc chưa thấy'))), 'thẻ đã duyệt vẫn ghi "chưa duyệt" / "người đọc chưa thấy"');
      chac(await bt.$('.phongdao-viec-bai a[href*="phongdao-sua-tac-pham"]') && (await bt.$('.phongdao-viec-tiep a') || await coChu(bt, 'Hết thẻ')), 'quyết xong không có lối Sửa bài hoặc Thẻ tiếp theo');
      await di(k, caDao);
      chac(await coChu(k, yNghia + ' Đã sửa trên thẻ.'), 'duyệt bản đã sửa rồi người đọc vẫn chưa thấy đúng bản đã sửa');
    });

    await thu('wp-admin Hộp việc trên điện thoại: mở một thẻ chỉ còn "← Danh sách · Thẻ N / M", nút quyết thấy ngay ở màn đầu; thanh dưới Hôm nay · Hộp việc · Bổ sung · Bài mới', async () => {
      // Có ít nhất một thẻ chờ: người biên soạn "Lưu nháp" một ý nghĩa
      await di(bt, caDao);
      await bam(bt, '[data-action=mo-tam][data-tam=y_nghia]');
      await datO(bt, 'dialog#tam-y_nghia[open] textarea[name=chu]', 'Nháp thử điện thoại.');
      await bamDoiTrang(bt, 'dialog#tam-y_nghia button[name=duyet][value=""]');
      await bt.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
      await di(bt, '/wp-admin/admin.php?page=phongdao-hop-viec&tt=cho&loai=nhap_y_nghia');
      const duoi = await bt.$$eval('.phongdao-thanh-duoi a', as => as.filter(a => a.offsetParent).map(a => a.textContent.replace(/\d+/g, '').trim()));
      chac(JSON.stringify(duoi) === JSON.stringify(['Hôm nay', 'Hộp việc', 'Bổ sung', 'Bài mới']), `thanh dưới điện thoại: ${duoi.join(' · ')}`);
      const mo = await bt.$eval('.phongdao-hv-ds a.phongdao-hv-mo', a => a.href).catch(() => '');
      chac(mo, 'không có thẻ nháp để mở trên điện thoại');
      await di(bt, mo.replace(G, ''));
      chac(await bt.$eval('.phongdao-hv-tab', e => !e.offsetParent) && await bt.$eval('.phongdao-hv-tim', e => !e.offsetParent), 'mở thẻ mà tab, ô tìm vẫn chiếm màn');
      chac(await bt.$eval('.phongdao-hv-ve', e => /Danh sách/.test(e.textContent) && /Thẻ \d+ \/ \d+/.test(e.textContent)), 'đầu thẻ không có "← Danh sách · Thẻ N / M"');
      const nut = await bt.$eval('.phongdao-hv-phai .tv-nut button[name=quyet]', b => { const r = b.getBoundingClientRect(); return { duoi: r.bottom, cao: innerHeight }; });
      chac(nut.duoi <= nut.cao, `nút quyết nằm dưới màn đầu (y = ${Math.round(nut.duoi)} / ${nut.cao})`);
      chac(await bt.evaluate(() => { const b = document.querySelector('.phongdao-hv-phai .tv-nut button[name=quyet]'); const r = b.getBoundingClientRect(); return document.elementFromPoint(r.x + r.width / 2, r.y + r.height / 2)?.closest('button') === b; }), 'nút quyết bị thanh dưới che');
      chac(await bt.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'Hộp việc tràn ngang ở 390 px');
      await bt.setViewport({ width: 1280, height: 800 });
    });

    await thu('wp-admin Sửa tác phẩm: lời hiện ở đầu; Ẩn khỏi người đọc rồi Đăng lại ngay trên trang sửa', async () => {
      await di(bt, caDao);
      const id = await bt.$eval('.dai-bien-tap', e => e.dataset.bai);
      await di(bt, `/wp-admin/admin.php?page=phongdao-sua-tac-pham&id=${id}`);
      chac(await bt.$('.phongdao-loi-xem li'), 'trang sửa chưa hiện lời ở đầu');
      await bam(bt, '.phongdao-quan-ly-bai>summary');
      await bamDoiTrang(bt, '.phongdao-quan-ly-bai .phongdao-trang-thai form:has(input[name=trang_thai][value=private]) [type=submit]');
      chac(await coChu(bt, 'Đã ẩn bài khỏi người đọc'), 'không báo đã ẩn bài');
      const an = await k.goto(G + caDao, { waitUntil: 'load', timeout: 120_000 });
      chac(an.status() === 404, `ẩn rồi khách vẫn mở được bài (HTTP ${an.status()})`);
      await bam(bt, '.phongdao-quan-ly-bai>summary');
      await bamDoiTrang(bt, '.phongdao-quan-ly-bai .phongdao-trang-thai form:has(input[name=trang_thai][value=publish]) [type=submit]');
      chac(await coChu(bt, 'Đã đăng: người đọc thấy bài'), 'không báo đã đăng lại');
      await di(k, caDao);
    });

    await thu('wp-admin Sửa tác phẩm: lưu Ý nghĩa qua cùng dịch vụ, người đọc thấy bản đã duyệt', async () => {
      const moi = 'Ý nghĩa thử từ biểu mẫu quản trị.';
      await di(bt, caDao);
      const id = await bt.$eval('.dai-bien-tap', e => e.dataset.bai);
      await di(bt, `/wp-admin/admin.php?page=phongdao-sua-tac-pham&id=${id}`);
      chac(await bt.$('#phan-y_nghia form') && await coChu(bt, 'Phân loại') && await bt.$('#phan-co_nghi'), 'không mở được trang sửa có cấu trúc (phần, phân loại, công tắc cờ)');
      // Một màn hai cột: lời và các phần bên trái, trạng thái và phân loại bên phải (màn rộng)
      const [chinh, ben] = await bt.evaluate(() => ['.phongdao-sua-chinh', '.phongdao-sua-ben'].map(s => document.querySelector(s).getBoundingClientRect().toJSON()));
      chac(ben.width > 0 && ben.left >= chinh.right - 1, 'trang sửa không chia hai cột ở màn rộng');
      // Phần đã có mở sẵn; phần chưa có thì mở bằng tay
      await bt.$eval('#phan-y_nghia', d => { d.open = true; });
      await datO(bt, '#phan-y_nghia textarea[name=chu]', moi);
      await bamDoiTrang(bt, '#phan-y_nghia button[name=duyet][value="1"]');
      chac(await coChu(bt, 'Đã lưu phần bài.'), 'wp-admin không báo lưu thành công');
      await di(k, caDao);
      chac(await coChu(k, moi), 'người đọc không thấy Ý nghĩa vừa duyệt trong wp-admin');
    });

    await thu('wp-admin giữ chữ: hàng từ khó thiếu lời giải / quá dài còn nguyên; lưu một phần giữ chữ phần khác', async () => {
      await bt.$eval('#phan-hinh_anh', d => { d.open = true; });
      await datO(bt, '#phan-hinh_anh textarea[name=chu]', '[UX-TEST] Phần chữ chưa lưu cần giữ');
      await bt.$eval('#phan-chu_thich', d => { d.open = true; });
      await bam(bt, '#phan-chu_thich [data-action=them-muc]');
      const last = '#phan-chu_thich li.muc-sua:last-child';
      await datO(bt, `${last} input[name$="[tu]"]`, '[UX-TEST] Từ kỹ thuật');
      await bamDoiTrang(bt, '#phan-chu_thich button[type=submit]');
      chac(await coChu(bt, 'cần điền Lời giải'), 'hàng điền dở không báo thiếu');
      chac(await bt.$eval(`${last} input[name$="[tu]"]`, o => o.value) === '[UX-TEST] Từ kỹ thuật', 'từ vừa gõ đã mất');
      chac(await bt.$eval('#phan-hinh_anh textarea[name=chu]', o => o.value) === '[UX-TEST] Phần chữ chưa lưu cần giữ', 'chữ ở phần khác đã mất');
      await datO(bt, `${last} textarea[name$="[loi_giai]"]`, 'a'.repeat(4001));
      await bamDoiTrang(bt, '#phan-chu_thich button[type=submit]');
      chac(await bt.$eval(`${last} textarea[name$="[loi_giai]"]`, o => o.value.length) === 4001, 'lỗi giới hạn làm mất đầu vào');
      await datO(bt, `${last} textarea[name$="[loi_giai]"]`, '[UX-TEST] Lời giải kỹ thuật');
      await bamDoiTrang(bt, '#phan-chu_thich button[type=submit]');
      chac(await coChu(bt, 'Đã lưu phần bài.'), 'không lưu được hàng sau khi sửa lỗi');
      chac(await bt.$eval('#phan-hinh_anh textarea[name=chu]', o => o.value) === '[UX-TEST] Phần chữ chưa lưu cần giữ', 'lưu thành công phần khác làm mất chữ');
      await bt.$eval('#phan-chu_thich', d => { d.open = true; });
      await bam(bt, `${last} input[name$="[xoa]"]`);
      await bamDoiTrang(bt, '#phan-chu_thich button[type=submit]');
      await bt.evaluate(() => document.querySelectorAll('form[data-giu-nhap]').forEach(f => window.phongdaoNhapDo.daLuu(f)));
    });

    await thu('wp-admin Sửa tác phẩm: đổi một chủ đề có sẵn, người đọc thấy đúng, Lịch sử ghi lại, trả về như cũ', async () => {
      await di(bt, caDao);
      const id = await bt.$eval('.dai-bien-tap', e => e.dataset.bai);
      const duong = `/wp-admin/admin.php?page=phongdao-sua-tac-pham&id=${id}`;
      const chipChuDe = async () => k.$$eval('a.tag-pill', as => as.map(a => a.textContent.trim()).filter(t => t.startsWith('Chủ đề: ')));
      const doiMot = async () => {
        await di(bt, duong);
        await bam(bt, '#phan-loai-chu_de summary');
        const o = await bt.$eval('#phan-loai-chu_de label.choice', l => ({ ten: l.textContent.trim(), co: l.querySelector('input').checked }));
        await bam(bt, '#phan-loai-chu_de label.choice input');
        await bamDoiTrang(bt, '#phan-loai-chu_de form [type=submit]');
        chac(await coChu(bt, 'Đã lưu phân loại.'), 'wp-admin không báo lưu phân loại');
        await di(k, caDao);
        chac((await chipChuDe()).includes(`Chủ đề: ${o.ten}`) === !o.co, `người đọc thấy sai chủ đề "${o.ten}" sau khi ${o.co ? 'gỡ' : 'gắn'}`);
        return o;
      };
      await di(k, caDao);
      const truoc = await chipChuDe();
      const lan1 = await doiMot();
      const lan2 = await doiMot();
      chac(lan1.ten === lan2.ten && lan1.co !== lan2.co, 'lần hai không trả lại đúng mục vừa đổi');
      chac(JSON.stringify(await chipChuDe()) === JSON.stringify(truoc), 'chủ đề trên trang đọc không về như cũ');
      chac(await bt.$eval('#lich-su', (e, ten) => e.textContent.includes('Chủ đề: sửa') && e.textContent.includes(ten), lan1.ten), 'Lịch sử trong wp-admin không ghi lần sửa chủ đề');
    });

    await thu('wp-admin Sửa tác phẩm: nút riêng "Đối chiếu sách" — thêm sách mới ngay tại chỗ, ghi trang, "Người đọc sẽ thấy" đổi theo', async () => {
      await di(bt, caDao);
      const id = await bt.$eval('.dai-bien-tap', e => e.dataset.bai);
      await di(bt, `/wp-admin/admin.php?page=phongdao-sua-tac-pham&id=${id}`);
      chac(!(await bt.$('#phan-loi [name="doi_chieu[ho_so]"]')), 'ô Sửa lời vẫn còn phần đối chiếu');
      await bam(bt, '#doi-chieu > summary');
      await bam(bt, '#doi-chieu .phongdao-sach-moi > summary');
      await datO(bt, '#dc-ten', 'Sách thử đầu-cuối');
      await datO(bt, '#dc-trang', '9');
      chac(await bt.$eval('[data-dc-xem]', e => e.textContent.includes('«Sách thử đầu-cuối», tr. 9')), 'dòng "Người đọc sẽ thấy" không đổi theo sách, trang');
      await bamDoiTrang(bt, '#doi-chieu form button[type=submit]');
      chac(await coChu(bt, 'Đã ghi đối chiếu sách'), 'không báo đã ghi đối chiếu');
      chac(await bt.$eval('.phongdao-dc-hien', e => e.textContent.includes('«Sách thử đầu-cuối», tr. 9')), 'trang sửa không hiện đối chiếu vừa ghi');
    });

    await thu('wp-admin Lịch sử: xem trước/sau rồi Khôi phục bản trước của Ý nghĩa, người đọc thấy bản cũ', async () => {
      const moi = 'Ý nghĩa thử từ biểu mẫu quản trị.';
      await di(bt, caDao);
      const id = await bt.$eval('.dai-bien-tap', e => e.dataset.bai);
      await di(bt, `/wp-admin/admin.php?page=phongdao-sua-tac-pham&id=${id}`);
      // Dòng sửa Ý nghĩa mới nhất có nút khôi phục: đánh dấu, mở so sánh, đọc các dòng bị bỏ (bản cũ)
      const cu = await bt.evaluate(() => {
        const li = [...document.querySelectorAll('#lich-su li')].find(l => l.textContent.includes('Ý nghĩa: sửa') && l.querySelector('form.phongdao-khoi-phuc'));
        if (!li) return null;
        li.dataset.thu = '1';
        const d = li.querySelector('details');
        if (d) d.open = true;
        return [...li.querySelectorAll('.phongdao-so-sanh li.bo del')].map(e => e.textContent.replace(/^bỏ: /, '').trim()).filter(t => t && !t.startsWith('('));
      });
      chac(cu, 'không có lần sửa Ý nghĩa nào khôi phục được trong Lịch sử');
      if (await bt.$eval('#lich-su li[data-thu]', li => li.closest('details.phongdao-lich-cu')?.open === false)) await bam(bt, '#lich-su .phongdao-lich-cu > summary');
      chac(await bt.$eval('#lich-su li[data-thu] .phongdao-so-sanh', e => e.textContent.includes('thêm: ')), 'so sánh không đánh dấu dòng thêm');
      await bamDoiTrang(bt, '#lich-su li[data-thu] form.phongdao-khoi-phuc [type=submit]');
      chac(await coChu(bt, 'Đã khôi phục bản trước'), 'không báo đã khôi phục');
      await di(k, caDao);
      chac(!(await coChu(k, moi)), 'người đọc vẫn thấy Ý nghĩa đã khôi phục bỏ đi');
      if (cu.length) chac(await coChu(k, cu[0]), `người đọc không thấy bản cũ "${cu[0]}"`);
    });

    // Thẻ người đọc gửi có chữ `chu` (dòng danh sách Hộp việc in vài chữ đầu của nội dung gửi); trả ID các thẻ khớp
    const theGop = async (tab, chu, tt = 'cho') => {
      await di(tab, `/wp-admin/admin.php?page=phongdao-hop-viec&loai=nguoi_doc&tt=${tt}`);
      return tab.$$eval('.phongdao-hv-ds .phongdao-hv-mo', (as, c) => as.filter(a => a.textContent.includes(c)).map(a => new URL(a.href).searchParams.get('viec')), chu);
    };
    await thu('wp-admin Hộp việc: Để sau thẻ góp của người đọc rồi Hoàn tác trong 10 giây, sau đó xử lý thẻ', async () => {
      const [id] = await theGop(bt, 'Góp thử đầu-cuối');
      chac(id, 'không có thẻ góp vừa gửi');
      await di(bt, `/wp-admin/admin.php?page=phongdao-hop-viec&loai=nguoi_doc&viec=${id}`);
      await bamDoiTrang(bt, `.phongdao-hv-phai article#viec-${id} button[name=quyet][value=de_sau]`);
      chac(await bt.$('[data-hv-hoan] button:not([disabled])'), 'Để sau không hiện nút Hoàn tác còn hạn');
      await bamDoiTrang(bt, '[data-hv-hoan] button');
      chac(await bt.$(`.phongdao-hv-phai article#viec-${id} button[name=quyet][value=de_sau]`), 'Hoàn tác không đưa thẻ về Chờ');
      const nutXong = `.phongdao-hv-phai article#viec-${id} button[name=quyet][value=xong]`;
      // Cuộn ngay: CSS của trang dùng smooth scrolling, nên kiểm hit-test trước khi cuộn xong có thể trúng thanh dưới.
      await bt.$eval(nutXong, e => e.scrollIntoView({ block: 'center', behavior: 'instant' }));
      chac(await bt.$eval(nutXong, e => { const r = e.getBoundingClientRect(); return e === document.elementFromPoint(r.left + r.width / 2, r.top + r.height / 2); }), 'nút Đã xử lý bị thanh điều hướng che');
      await bamDoiTrang(bt, nutXong);
      chac(!(await theGop(bt, 'Góp thử đầu-cuối')).includes(id), 'thẻ vẫn còn ở Chờ sau khi xử lý');
    });

    await thu('wp-admin Hộp việc hai phiên: thẻ đã quyết ở phiên khác thì màn cũ bị chặn, báo rõ, không quyết chồng', async () => {
      const chu = 'Góp thử hai phiên: dòng một nên đọc khác.';
      await di(k, '/gop/');
      await bam(k, 'input[name=loai][value=bao_loi]');
      await datO(k, '#gop-noi-dung', chu);
      await bam(k, 'input[name=dong_y]');
      await bamDoiTrang(k, '#gop-form [type=submit]');
      chac(await coChu(k, 'Cảm ơn bạn!'), 'người đọc chưa gửi được góp thử');
      const bt2 = await moTab(trinhDuyet.defaultBrowserContext(), 'người biên soạn (phiên 2)');
      try {
        const [id] = await theGop(bt, chu);
        chac(id, 'không có thẻ góp vừa gửi');
        const mo = `/wp-admin/admin.php?page=phongdao-hop-viec&loai=nguoi_doc&viec=${id}`;
        await di(bt, mo);
        await di(bt2, mo);
        chac(await bt2.$(`.phongdao-hv-phai article#viec-${id}`), 'hai phiên không cùng mở thẻ góp vừa gửi');
        // Đưa thẻ trình duyệt lên trước và cuộn tức thì (trang cuộn mượt): cú bấm không trúng thanh dính
        const bamThe = async (tab, nut) => {
          const sel = `.phongdao-hv-phai article#viec-${id} button[name=quyet][value=${nut}]`;
          await tab.bringToFront();
          await tab.$eval(sel, e => e.scrollIntoView({ block: 'center', behavior: 'instant' }));
          await bamDoiTrang(tab, sel);
        };
        await bamThe(bt, 'de_sau');
        await bamThe(bt2, 'xong'); // màn cũ: vẫn thấy thẻ ở Chờ
        chac(await coChu(bt2, 'Thẻ đã đổi ở phiên khác'), 'màn cũ không báo thẻ đã đổi ở phiên khác');
        await di(bt2, mo + '&tt=de_sau');
        chac(await bt2.$(`.phongdao-hv-phai article#viec-${id}`), 'thẻ không còn ở Để sau: màn cũ đã quyết chồng');
        await bamThe(bt2, 'xong'); // màn mới tải lại thì quyết được
        chac(!(await theGop(bt2, chu, 'de_sau')).includes(id), 'màn đã tải lại vẫn không quyết được thẻ');
      } finally {
        await bt2.close();
        await bt.bringToFront();
        tabDangChay = bt;
      }
    });

    await thu('Cách hiểu của bạn đọc: ba thẻ Hiểu thêm; khách gửi bằng form nhanh trên bài chưa có Ý nghĩa, chưa hiện ngay; người biên soạn bấm Hiện ở Hộp việc; khách thấy ở phần Cách hiểu', async () => {
      const bai = '/ca-dao/cau-mau-thu-hai/', chu = 'Cách hiểu thử đầu-cuối: câu nói về lòng biết ơn.';
      await di(k, bai);
      chac(await coChu(k, 'Câu này chưa có lời giải nghĩa'), 'bài chưa có Ý nghĩa không mời chia sẻ cách hiểu');
      chac(await k.$eval('[data-form-cach-hieu]', d => !d.open), 'form cách hiểu chưa thu gọn lúc đầu');
      chac(await k.$eval('[data-hieu-them] [role=tablist]', e => !e.hidden) && 3 === (await k.$$('[data-hieu-them] [role=tab]')).length, 'Hiểu thêm chưa thành ba thẻ khi có JavaScript');
      await bam(k, '#ht-nut-cam-nghi');
      chac(await k.$eval('#cach-hieu', e => e.hidden) && await k.$eval('#cam-nghi', e => !e.hidden && e.getAttribute('role') === 'tabpanel'), 'bấm thẻ Cảm nghĩ không đổi phần');
      await k.keyboard.press('Home');
      chac(await k.$eval('#ht-nut-cach-hieu', e => e === document.activeElement && e.getAttribute('aria-selected') === 'true'), 'phím Home không về thẻ đầu');
      await k.keyboard.press('ArrowLeft');
      chac(await k.$eval('#ht-nut-cam-nghi', e => e === document.activeElement && e.getAttribute('aria-selected') === 'true'), 'phím mũi tên không đổi thẻ');
      await bam(k, 'a[data-mo-cach-hieu]');
      chac(await k.$eval('[data-form-cach-hieu]', d => d.open), 'lời mời không mở form cách hiểu');
      chac(await k.$eval('#cach-hieu', e => !e.hidden), '"Chia sẻ cách hiểu" không mở phần Cách hiểu');
      await datO(k, '#cach-hieu-chu', chu);
      await datO(k, '#cach-hieu-ten', 'Bút danh thử');
      await bam(k, '#cach-hieu-form input[name=tu_viet]');
      await bam(k, '#cach-hieu-form input[name=dong_y]');
      await bamDoiTrang(k, '#cach-hieu-form [type=submit]');
      chac(new URL(k.url()).hash === '#cach-hieu' && await coChu(k, 'Cách hiểu đã tới người biên soạn') && !(await coChu(k, chu)), `gửi xong không báo đã tới người biên soạn, hoặc đã hiện ngay (${k.url()})`);
      const [id] = await theGop(bt, 'Cách hiểu thử đầu-cuối');
      chac(id, 'Hộp việc không có thẻ cách hiểu vừa gửi');
      await di(bt, `/wp-admin/admin.php?page=phongdao-hop-viec&loai=nguoi_doc&viec=${id}`);
      chac(await coChu(bt, 'có tham khảo cách hiểu của bạn đọc'), 'thẻ cách hiểu không nhắc tự viết lại khi dùng làm gốc cho Ý nghĩa');
      // Sửa chữ và tên hiển thị ngay trên thẻ rồi bấm "Hiện trên trang" (gửi không tải lại trang)
      const daSua = 'Cách hiểu thử đầu-cuối đã sửa: câu nói về lòng biết ơn cha mẹ.';
      chac(await bt.$(`#nd-chu-${id}`) && await bt.$eval(`#nd-ten-${id}`, o => o.value) === 'Bút danh thử', 'thẻ cách hiểu không có ô sửa chữ và tên hiển thị');
      await datO(bt, `#nd-chu-${id}`, daSua);
      await datO(bt, `#nd-ten-${id}`, 'Bút danh đã sửa');
      await bamDoiTrang(bt, `.phongdao-hv-phai article#viec-${id} button[name=quyet][value=duyet]`);
      chac(await coChu(bt, 'bản người đọc gửi giữ trong nhật ký'), 'hiện xong không báo đã lưu chữ, tên đã sửa');
      await di(k, bai);
      await bam(k, '#ht-nut-cach-hieu');
      chac(await k.$eval('#cach-hieu', (e, c) => !e.hidden && e.innerText.includes(c) && e.innerText.includes('Bút danh đã sửa') && !e.innerText.includes('Bút danh thử'), daSua), 'khách chưa thấy bản cách hiểu đã sửa (chữ, tên hiển thị) ở phần Cách hiểu');
      chac(!(await k.$eval('.work-main', (e, c) => e.innerText.includes(c), daSua)), 'cách hiểu lẫn vào khung lời và Ý nghĩa');
    });

    await thu('Trang đọc (chế độ sửa): "Sửa phân loại" gắn một chủ đề có sẵn, quay lại trang đọc, người đọc thấy; gỡ lại như cũ', async () => {
      const bai = '/ca-dao/cau-mau-thu-hai/';
      const form = 'dialog#tam-phan-loai[open] form:has(input[name=tax][value=chu_de])';
      await di(bt, bai);
      await bam(bt, '[data-action=mo-tam][data-tam=phan-loai]');
      const o = await bt.$eval(`${form} label.chip-chon:has(input:not(:checked))`, l => ({ id: l.getAttribute('for'), ten: l.innerText.replace(/\s*·\s*máy gợi ý\s*$/, '').trim() })).catch(() => null);
      chac(o, 'tấm Sửa phân loại không có chủ đề nào để chọn');
      await bam(bt, `#${o.id}`);
      await bamDoiTrang(bt, `${form} button[type=submit]`);
      chac(new URL(bt.url()).pathname === bai, `lưu xong không quay lại trang đọc (${bt.url()})`);
      await di(k, bai);
      chac(await coChu(k, 'Chủ đề: ' + o.ten), `người đọc chưa thấy chủ đề "${o.ten}"`);
      await di(bt, bai);
      await bam(bt, '[data-action=mo-tam][data-tam=phan-loai]');
      await bam(bt, `#${o.id}`);
      await bamDoiTrang(bt, `${form} button[type=submit]`);
      await di(k, bai);
      chac(!(await coChu(k, 'Chủ đề: ' + o.ten)), 'gỡ chủ đề trên trang đọc không được');
      chac(await coChu(k, 'Ở quê bạn có câu này không? Góp'), 'bài chưa có nơi lưu truyền không mời người đọc góp');
    });

    await thu('Biên tập: sửa lời rồi Hoàn tác', async () => {
      const bai = '/ca-dao/cau-mau-thu-hai/', them = 'Dòng thêm thử đầu cuối';
      await di(bt, bai);
      await bam(bt, '[data-action=mo-tam][data-tam=loi]');
      await bt.$eval('dialog#tam-loi textarea[name=dong]', (o, d) => { o.value += '\n' + d; }, them);
      await bamDoiTrang(bt, 'dialog#tam-loi [type=submit]');
      await baoCo(bt, 'Đã lưu');
      chac(await coChu(bt, them), 'lời mới không hiện');
      await Promise.all([bt.waitForNavigation({ waitUntil: 'load' }), bam(bt, '#phongdao-bao button')]);
      await baoCo(bt, 'Đã hoàn tác');
      chac(!(await coChu(bt, them)), 'hoàn tác xong lời mới vẫn còn');
    });

    let baiMoi = '';
    const loiMoi = 'Câu thử đầu cuối không có thật';
    await thu('wp-admin Thêm tác phẩm: dán lời, máy nhận thể thơ, đăng ngay, mở trang Sửa tác phẩm của bài', async () => {
      await di(bt, '/wp-admin/admin.php?page=phongdao-them');
      await bam(bt, 'label.phongdao-them-loai:has(input[name=the_loai][value="tuc-ngu"])');
      chac(await bt.$eval('input[name=the_loai][value="tuc-ngu"]', o => o.checked), 'chọn thể loại không được');
      await bt.type('form[data-them-bai] [name=loi]', loiMoi);
      await doi(bt, () => /Máy nhận/.test(document.getElementById('them-phan-tich')?.textContent || ''), null, 'máy không nhận thể thơ');
      await bamDoiTrang(bt, 'form[data-them-bai] button[name=dang][value="1"]');
      chac(new URL(bt.url()).searchParams.get('page') === 'phongdao-sua-tac-pham' && await coChu(bt, loiMoi), `đăng xong không mở trang Sửa tác phẩm của bài (${bt.url()})`);
      baiMoi = new URL(await bt.$eval('.phongdao-sua-lien a[href*="preview"], .phongdao-sua-lien a:nth-of-type(2)', a => a.href)).pathname;
      chac(baiMoi.startsWith('/tuc-ngu/'), `bài vừa đăng không nằm ở /tuc-ngu/ (${baiMoi})`);
      await di(k, baiMoi);
      chac(await coChu(k, loiMoi), 'người đọc không thấy bài vừa đăng');
    });

    if (baiMoi) await thu('Ẩn bài: người đọc không còn thấy', async () => {
      await di(bt, baiMoi);
      await bamDoiTrang(bt, '[data-action=trang-thai][data-trang-thai=private]');
      await baoCo(bt, 'Đã ẩn bài');
      const tl = await di(k, baiMoi, { trang: 0 });
      chac(tl.status() >= 400 || !(await coChu(k, loiMoi)), 'người đọc vẫn thấy bài đã ẩn');
    });

    await thu('Sửa mục từ trên trang mục từ: người đọc thấy lời giải mới', async () => {
      const giai = 'Lời giải thử đầu-cuối do người biên soạn viết.';
      await di(bt, T.mucTu);
      await bam(bt, '#sua-muc-tu summary');
      await datO(bt, '#smt-giai', giai);
      await bamDoiTrang(bt, '#sua-muc-tu [type=submit]');
      chac(await coChu(bt, 'Đã lưu mục từ.'), 'không báo đã lưu');
      await di(k, T.mucTu);
      chac(await coChu(k, giai), 'người đọc không thấy lời giải mới');
    });

    await thu('wp-admin Bài mới: Để dành một câu (không tạo thẻ, không vào Hộp việc), lọc Để dành thấy câu, Bỏ dấu', async () => {
      await di(bt, '/wp-admin/admin.php?page=phongdao-kho');
      chac(await coChu(bt, 'câu khớp bộ lọc'), 'không mở được màn Bài mới (Kho)');
      chac(!(await coChu(bt, 'Đưa vào Hộp việc')), 'Bài mới còn nút "Đưa vào Hộp việc"');
      const id = await bt.$eval('form input[name=action][value=phongdao_kho_danh_dau]', o => o.form.querySelector('input[name=id]').value);
      await bamDoiTrang(bt, `form:has(input[name=action][value=phongdao_kho_danh_dau]):has(input[name=id][value="${id}"]) button[type=submit]:not([name=mot])`);
      const bao = await bt.$eval('.phongdao-admin-kho .notice', e => e.textContent.trim()).catch(() => '(không có thông báo)');
      chac(bao.includes('Để dành') && bao.includes(`#${id}`), `không báo đã Để dành — thấy: ${bao}`);
      await di(bt, '/wp-admin/admin.php?page=phongdao-kho&danh=de_danh');
      chac(await bt.$(`input[name=id][value="${id}"]`), 'câu Để dành không còn trong danh sách lọc Để dành');
      await di(bt, '/wp-admin/admin.php?page=phongdao-hop-viec&tt=cho&loai=cau_moi');
      chac(await coChu(bt, '0 thẻ khớp bộ lọc') || await coChu(bt, 'Không có thẻ phù hợp'), 'Để dành mà Hộp việc có thẻ Câu mới');
      // Bỏ dấu: câu về như cũ
      await di(bt, '/wp-admin/admin.php?page=phongdao-kho&danh=de_danh');
      await bamDoiTrang(bt, `form:has(input[name=id][value="${id}"]):has(input[name=lam][value=bo]) button[type=submit]:not([name=mot])`);
      chac((await bt.$eval('.phongdao-admin-kho .notice', e => e.textContent)).includes('Đã bỏ dấu 1 câu'), 'Bỏ dấu không báo');
      // Khách không bao giờ thấy màn Kho (bị đưa sang trang đăng nhập)
      const tl = await k.goto(G + '/wp-admin/admin.php?page=phongdao-kho', { waitUntil: 'domcontentloaded', timeout: 120_000 });
      chac(!(await coChu(k, 'Câu kho mẫu')) && !(await coChu(k, 'Tục ngữ kho mẫu')), `khách thấy dữ liệu kho (HTTP ${tl.status()})`);
    });

    await thu('wp-admin chọn hàng loạt ở Tác phẩm: chọn tất cả trên trang, giữ Shift chọn một dải, bỏ chọn hết', async () => {
      await di(bt, '/wp-admin/admin.php?page=phongdao-tac-pham&tt=tat_ca');
      const o = 'input[name="ids[]"]';
      const n = await bt.$$eval(o, os => os.length);
      chac(n >= 3, `danh sách Tác phẩm không đủ bài để thử chọn hàng loạt (thấy ${n})`);
      await bt.waitForSelector('.phongdao-chon-het:not([hidden])', { visible: true, timeout: 15_000 });
      const dem = () => bt.$$eval(o + ':checked', os => os.length);
      const trangThai = () => bt.$eval('.phongdao-chon-het-o', h => (h.checked ? 'het' : h.indeterminate ? 'mot_phan' : 'chua'));
      chac(await coChu(bt, `Chọn tất cả ${n} bài trên trang này`), 'thanh chọn không ghi số bài trên trang');
      await bam(bt, '.phongdao-chon-het-o');
      chac(await dem() === n && await trangThai() === 'het', `bấm "Chọn tất cả" mà chỉ chọn ${await dem()}/${n} bài`);
      chac(await bt.$eval('details.phongdao-lo-gon', (d, so) => !d.open && d.querySelector('.phongdao-lo-dem').textContent.includes(`đã chọn ${so}`), n), 'chọn tất cả mà khối Làm theo lô không đếm, hoặc tự mở đẩy danh sách xuống');
      chac(await bt.$eval('.phongdao-thanh-noi', (t, so) => !t.hidden && t.textContent.includes(`Đã chọn ${so} bài`), n), 'chọn tất cả mà thanh ở đáy màn không hiện số đã chọn');
      await bam(bt, '.phongdao-chon-het-o');
      chac(await dem() === 0 && await trangThai() === 'chua', 'bấm lại "Chọn tất cả" mà không bỏ chọn hết');
      // Bấm ô thứ nhất, giữ Shift bấm ô thứ ba: chọn cả ba
      const ids = await bt.$$eval(o, os => os.map(x => x.value));
      await bam(bt, `${o}[value="${ids[0]}"]`);
      await bt.keyboard.down('Shift');
      await bam(bt, `${o}[value="${ids[2]}"]`);
      await bt.keyboard.up('Shift');
      const chon = await bt.$$eval(o + ':checked', os => os.map(x => x.value));
      chac(chon.join() === ids.slice(0, 3).join(), `giữ Shift chọn dải được ${chon.length} bài (${chon.join()}), cần 3 bài đầu`);
      chac(n === 3 ? await trangThai() === 'het' : await trangThai() === 'mot_phan', 'ô "Chọn tất cả" không hiện chọn một phần');
      // Có nhiều bài khớp bộ lọc hơn trên trang thì có nút chuyển sang "Mọi bài khớp bộ lọc"
      await bt.$eval('.phongdao-chon-het-o', h => { if (!h.checked) h.click(); });
      const tong = await bt.$eval('.phongdao-chon-het', t => Number(t.dataset.tong));
      if (tong > n) {
        await bam(bt, '.phongdao-chon-loc');
        chac(await bt.$eval('input[name=pham_vi][value=loc]', r => r.checked), 'bấm "Chọn mọi bài khớp bộ lọc" mà Phạm vi không đổi');
      }
      await bt.$eval('.phongdao-chon-het-o', h => { if (h.checked) h.click(); });
      chac(await dem() === 0 && await bt.$eval('input[name=pham_vi][value=chon]', r => r.checked), 'bỏ chọn hết mà còn ô chọn hoặc Phạm vi vẫn là mọi bài');
    });

    await thu('wp-admin Lô: Để sau (hoặc Bỏ nháp) mọi thẻ một loại khớp bộ lọc — xem trước, áp dụng, kết quả từng mục', async () => {
      // Loại việc có thẻ Chờ và có nút Để sau (dữ liệu mẫu không còn thẻ Câu mới: câu mới nằm ở Bài mới)
      const loaiCo = () => bt.$$eval('nav[aria-label="Loại việc"] a.phongdao-chip', as => as.map(a => new URL(a.href).searchParams.get('loai')).filter(l => l && l !== 'co_the_trung').sort((x, y) => (x === 'nguoi_doc') - (y === 'nguoi_doc')));
      await di(bt, '/wp-admin/admin.php?page=phongdao-hop-viec&tt=cho');
      let loai = await loaiCo();
      if (!loai.length) {
        // Các bước trước đã quyết hết thẻ mẫu: tạo một nháp ý nghĩa (người biên soạn "Lưu nháp") để có thẻ Chờ
        await di(bt, caDao);
        await bam(bt, '[data-action=mo-tam][data-tam=y_nghia]');
        await datO(bt, 'dialog#tam-y_nghia[open] textarea[name=chu]', 'Nháp thử lô Để sau.');
        await bamDoiTrang(bt, 'dialog#tam-y_nghia button[name=duyet][value=""]');
        await di(bt, '/wp-admin/admin.php?page=phongdao-hop-viec&tt=cho');
        loai = await loaiCo();
      }
      chac(loai.length, 'Hộp việc mẫu không có loại thẻ nào để thử lô Để sau');
      await di(bt, `/wp-admin/admin.php?page=phongdao-hop-viec&tt=cho&loai=${loai[0]}`);
      chac(await bt.$('.phongdao-lo-chon'), 'Hộp việc không có khối Làm theo lô');
      // Khối lô thu gọn khi chưa chọn thẻ nào: mở bằng tay để chọn "mọi thẻ khớp bộ lọc"
      chac(!(await bt.$eval('details.phongdao-lo-gon', d => d.open)), 'khối Làm theo lô mở sẵn khi chưa chọn thẻ nào');
      await bam(bt, 'details.phongdao-lo-gon > summary');
      // Nháp ý nghĩa chỉ có Duyệt / Bỏ nháp; các loại khác có Để sau
      await bt.select('#phongdao-lo-thao-tac', loai[0] === 'nhap_y_nghia' ? 'viec_bo' : 'viec_de_sau');
      await bam(bt, 'input[name=pham_vi][value=loc]');
      await bamDoiTrang(bt, '.phongdao-lo-chon [type=submit]');
      chac(await coChu(bt, 'Chờ xem trước') && await bt.$('input[name="giu[]"]'), 'không sang bước xem trước');
      const so = await bt.$$eval('input[name="giu[]"]', os => os.length);
      await bamDoiTrang(bt, 'button[name=lam][value=ap_dung]');
      await doi(bt, () => document.body.innerText.includes('Lô đã chạy xong.'), null, 'lô không chạy xong', 60_000);
      chac(await coChu(bt, `Thành công ${so}`), `kết quả không ghi ${so} mục thành công`);
      await di(bt, `/wp-admin/admin.php?page=phongdao-hop-viec&tt=cho&loai=${loai[0]}`);
      chac(await coChu(bt, '0 thẻ khớp bộ lọc') || await coChu(bt, 'Không có thẻ phù hợp'), `còn thẻ loại ${loai[0]} ở Chờ sau lô`);
    });

    await thu('wp-admin Lô Tác phẩm: thêm một chủ đề có sẵn qua xem trước, người đọc thấy; gỡ lại theo lô', async () => {
      await di(bt, T.cauDo);
      const id = await bt.$eval('.dai-bien-tap', e => e.dataset.bai);
      const lam = async thaoTac => {
        await di(bt, `/wp-admin/admin.php?page=phongdao-tac-pham&q=${id}`);
        await bam(bt, `input[name="ids[]"][value="${id}"]`);
        await bam(bt, '.phongdao-thanh-noi [data-mo-lo]');
        await bt.select('#phongdao-lo-tp-thao-tac', thaoTac);
        const v = await bt.$$eval('#phongdao-lo-tp-muc option', os => os.find(o => o.textContent.trim() === 'Lao động')?.value || '');
        chac(v, 'không có chủ đề "Lao động" để chọn');
        await bt.select('#phongdao-lo-tp-muc', v);
        await bamDoiTrang(bt, '.phongdao-lo-chon [type=submit]');
        chac(await coChu(bt, 'Chờ xem trước'), 'không sang bước xem trước');
        await bamDoiTrang(bt, 'button[name=lam][value=ap_dung]');
        chac(await coChu(bt, 'Lô đã chạy xong.') && await coChu(bt, 'Thành công 1'), `lô ${thaoTac} không thành công`);
      };
      await lam('pl_them');
      await di(k, T.cauDo);
      chac(await coChu(k, 'Chủ đề: Lao động'), 'người đọc không thấy chủ đề vừa thêm theo lô');
      await lam('pl_go');
      await di(k, T.cauDo);
      chac(!(await coChu(k, 'Chủ đề: Lao động')), 'gỡ theo lô rồi người đọc vẫn thấy chủ đề');
    });

    await thu('wp-admin Lô sửa từng hàng: viết Ý nghĩa cho một bài, xem so trước/sau, duyệt, người đọc thấy', async () => {
      const moi = 'Ý nghĩa thử viết theo lô từng hàng.';
      await di(bt, T.cauDo);
      const id = await bt.$eval('.dai-bien-tap', e => e.dataset.bai);
      await di(bt, `/wp-admin/admin.php?page=phongdao-tac-pham&q=${id}`);
      await bam(bt, `input[name="ids[]"][value="${id}"]`);
      chac(await bt.$eval('.phongdao-thanh-noi', t => !t.hidden && t.textContent.includes('Đã chọn 1 bài')), 'đánh dấu bài mà thanh ở đáy màn không hiện, không đếm');
      // "Làm theo lô…" trên thanh mở khối lô; khối đang mở trong màn thì thanh ẩn, không che nút của khối
      await bam(bt, '.phongdao-thanh-noi [data-mo-lo]');
      chac(await bt.$eval('details.phongdao-lo-gon', d => d.open && d.querySelector('.phongdao-lo-dem').textContent.includes('đã chọn 1')), '"Làm theo lô…" không mở khối lô hoặc khối không đếm');
      // Máy tính: thanh nằm trong thanh "Chọn tất cả" ở đầu danh sách (dưới khối lô, không che nút của khối); điện thoại thì ẩn
      await doi(bt, () => { const t = document.querySelector('.phongdao-thanh-noi'); return t.hidden || t.classList.contains('o-tren'); }, null, 'khối lô đang mở mà thanh nổi ở đáy màn vẫn hiện');
      await bt.select('#phongdao-lo-tp-thao-tac', 'sua_phan');
      await bt.select('#phongdao-lo-tp-phan', 'y_nghia');
      await bamDoiTrang(bt, '.phongdao-lo-chon [type=submit]');
      chac(await bt.$(`textarea[name="gia_tri[${id}]"]`), 'không có ô giá trị riêng cho bài');
      await datO(bt, `textarea[name="gia_tri[${id}]"]`, moi);
      // Đánh dấu "đã duyệt" chắc chắn (không bấm theo toạ độ — bố cục còn nhích khi tải xong) rồi kiểm lại
      await bt.$eval('input[name=duyet]', o => { if (!o.checked) o.click(); });
      chac(await bt.$eval('input[name=duyet]', o => o.checked), 'chưa đánh dấu được ô "đã duyệt"');
      await bamDoiTrang(bt, 'button[name=lam][value=luu_bo]');
      chac(await coChu(bt, moi) && await bt.$('.phongdao-lo-so-sanh ins'), 'không hiện so trước/sau sau khi lưu giá trị');
      chac(await bt.$eval('input[name=duyet]', o => o.checked), 'lưu giá trị xong ô "đã duyệt" mất dấu');
      await bamDoiTrang(bt, 'button[name=lam][value=ap_dung]');
      chac(await coChu(bt, 'Lô đã chạy xong.') && await coChu(bt, 'Thành công 1'), 'lô sửa từng hàng không thành công');
      await di(k, T.cauDo);
      // Câu đố: giải nghĩa là "Giải thích đáp án", nằm trong khối "Hiện đáp án" — mở khối như người đọc rồi mới đọc
      await k.$eval('details.answer', d => { d.open = true; }).catch(() => {});
      chac(await coChu(k, moi), 'người đọc không thấy Ý nghĩa vừa duyệt theo lô');
    });

    await thu('wp-admin Kho "Xem trước và đăng" một mạch: chọn câu, thanh nổi đếm, một màn xem trước rồi Đăng, người đọc thấy bài', async () => {
      // Thử trên khổ điện thoại: chọn câu thì thanh ở đáy màn hiện số đã chọn và nút đăng; khối lô ở đầu không tự mở
      await bt.setViewport({ width: 390, height: 844 });
      await di(bt, '/wp-admin/admin.php?page=phongdao-kho');
      chac(await coChu(bt, 'Bài mới') && await coChu(bt, 'Câu trong Kho'), 'màn Kho không mang tên "Bài mới"');
      const chon = await bt.$$eval('input[name="ids[]"][form=phongdao-kho-lo]', os => os.map(o => o.value));
      chac(chon.length >= 2, `màn Kho không có đủ câu để chọn nhiều (thấy ${chon.length})`);
      chac(await bt.$eval('.phongdao-thanh-noi', d => d.hidden), 'điện thoại: thanh nổi đã hiện khi chưa chọn câu nào');
      for (const id of chon.slice(0, 2)) await bam(bt, `input[name="ids[]"][value="${id}"]`);
      await bt.waitForSelector('.phongdao-thanh-noi:not([hidden])', { visible: true, timeout: 15_000 });
      chac(await coChu(bt, 'Đã chọn 2 câu'), 'thanh nổi không đếm 2 câu đã chọn');
      chac(!(await bt.$eval('details.phongdao-lo-gon', d => d.open)), 'khối lô tự mở dù đã có thanh ở đáy màn');
      chac(await bt.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'màn Kho có thanh nổi tràn ngang ở 390 px');
      // Đi thẳng tới một màn xem trước Đăng, không qua Hộp việc (D-101)
      await bamDoiTrang(bt, '.phongdao-thanh-noi button[name=lam][value=dang]');
      chac(await coChu(bt, 'Xem trước: đăng 2 câu từ Kho') && await coChu(bt, '← Về Kho') && await coChu(bt, 'Chưa có gì được đăng') && await coChu(bt, 'Kho mẫu'), 'Kho không sang thẳng màn xem trước "Xem trước: đăng 2 câu từ Kho" có lời và lối về Kho');
      chac(!(await bt.$('button[name=lam][value=luu_bo]')), 'xem trước một trang mà vẫn có nút "Lưu bỏ chọn"');
      chac(await bt.$eval('#adminmenu .wp-has-current-submenu .wp-submenu li.current a', a => a.href.includes('page=phongdao-kho')).catch(() => false), 'xem trước từ Kho mà menu Phongdao không tô mục Bài mới');
      const so = await bt.$$eval('input[name="giu[]"]', os => os.length);
      chac(so === 2, `xem trước lô Đăng có ${so} câu, cần đúng 2 câu vừa đưa vào`);
      // Trang xem trước có "Chọn tất cả": bỏ hết thì nút chính đếm 0, chọn lại thì đếm 2
      await bt.waitForSelector('.phongdao-chon-het:not([hidden])', { visible: true, timeout: 15_000 });
      const nutDang = () => bt.$eval('button[name=lam][value=ap_dung]', b => b.textContent.trim());
      chac(await nutDang() === 'Đăng 2 câu', `nút chính ghi "${await nutDang()}", cần "Đăng 2 câu"`);
      await bt.$eval('.phongdao-chon-het-o', h => { if (h.checked) h.click(); });
      chac(await bt.$$eval('input[name="giu[]"]:checked', os => os.length) === 0 && await nutDang() === 'Đăng 0 câu', 'bỏ chọn hết ở xem trước mà ô hoặc nút chính không đổi');
      await bam(bt, '.phongdao-chon-het-o');
      chac(await bt.$$eval('input[name="giu[]"]:checked', os => os.length) === 2 && await nutDang() === 'Đăng 2 câu', 'chọn tất cả ở xem trước mà không đánh dấu lại đủ 2 câu');
      await bamDoiTrang(bt, 'button[name=lam][value=ap_dung]');
      await doi(bt, () => document.body.innerText.includes('Lô đã chạy xong.'), null, 'lô Đăng không chạy xong', 120_000);
      chac(await coChu(bt, `Thành công ${so}`), `lô Đăng không đăng đủ ${so} câu`);
      chac(await bt.$('.phongdao-lo-tiep a.button-primary[href*="page=phongdao-kho"]'), 'lô Đăng từ Kho xong mà không có nút "Về Kho"');
      await bt.setViewport({ width: 1280, height: 800 });
      const bai = await bt.$eval('.phongdao-admin-lo td a[href*="phongdao-sua-tac-pham"]', a => new URL(a.href).searchParams.get('id'));
      await di(k, `/?p=${bai}`);
      chac(await coChu(k, 'Kho mẫu'), 'người đọc không thấy bài vừa đăng theo lô');
    });

    await thu('wp-admin menu theo ba dòng việc, màn Hôm nay, khung "Phongdao hôm nay" ở trang chào mừng WordPress (máy tính và điện thoại)', async () => {
      await bt.setViewport({ width: 1280, height: 800 });
      await di(bt, '/wp-admin/admin.php?page=phongdao-ban-lam-viec');
      const muc = await bt.$$eval('#toplevel_page_phongdao-ban-lam-viec .wp-submenu a', as => as.map(a => a.textContent.replace(/\d+/g, '').trim()));
      chac(JSON.stringify(muc) === JSON.stringify(['Hôm nay', 'Bài mới', 'Hộp việc', 'Bổ sung', 'Tác phẩm', 'Danh mục', 'Nguồn & cài đặt']), `menu Phongdao: ${muc.join(' · ')}`);
      chac(await bt.$eval('h1', h => h.textContent.trim()) === 'Hôm nay', 'màn đầu không mang tên "Hôm nay"');
      chac(await bt.$$eval('.phongdao-ban-so > .phongdao-ban-o', os => os.map(o => o.querySelector('h3').textContent).join()) === 'Bài mới,Hộp việc,Bổ sung', 'Hôm nay không có ba ô Bài mới · Hộp việc · Bổ sung');
      chac(await bt.$$eval('.phongdao-ban-so > .phongdao-ban-o a.button', as => as.length) === 3, 'mỗi ô của Hôm nay không có nút bắt đầu');
      chac(await bt.$('a[href*="page=phongdao-lo"]'), 'Hôm nay không có lối Lịch sử thao tác hàng loạt');
      // Bổ sung mở được, có danh sách ưu tiên
      await bamDoiTrang(bt, '.phongdao-ban-so a[href*="page=phongdao-bo-sung"]');
      chac(await coChu(bt, 'Danh sách ưu tiên'), 'nút "Bắt đầu bổ sung" không mở màn Bổ sung');
      // Trang chào mừng WordPress: khung ở đầu, phần còn lại của WordPress giữ nguyên
      await di(bt, '/wp-admin/');
      chac(await bt.$('.phongdao-khung-wp') && await bt.$('#dashboard-widgets'), 'trang chào mừng WordPress không có khung "Phongdao hôm nay" hoặc mất phần của WordPress');
      chac(await bt.evaluate(() => { const k = document.querySelector('.phongdao-khung-wp'); const w = document.querySelector('#dashboard-widgets-wrap'); return !!(k && w && (k.compareDocumentPosition(w) & Node.DOCUMENT_POSITION_FOLLOWING)); }), 'khung "Phongdao hôm nay" không nằm ở đầu trang chào mừng');
      // Điện thoại: Hôm nay không tràn ngang, nút bắt đầu đủ lớn để chạm
      await bt.setViewport({ width: 390, height: 844 });
      await di(bt, '/wp-admin/admin.php?page=phongdao-ban-lam-viec');
      chac(await bt.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'Hôm nay tràn ngang ở 390 px');
      chac(await bt.$$eval('.phongdao-ban-so a.button', as => as.every(a => a.getBoundingClientRect().height >= 44)), 'nút bắt đầu ở Hôm nay nhỏ hơn 44 px');
      await bt.setViewport({ width: 1280, height: 800 });
      // Màn con giữ menu Phongdao mở và tô đúng mục
      const sua = await bt.$eval('.phongdao-nen-lam a[href*="phongdao-sua-tac-pham"], .phongdao-ban a[href*="phongdao-sua-tac-pham"]', a => a.href).catch(() => '');
      await di(bt, sua ? sua.replace(G, '') : '/wp-admin/admin.php?page=phongdao-tac-pham');
      if (!sua) await bamDoiTrang(bt, 'a[href*="page=phongdao-sua-tac-pham"]');
      chac(await bt.$eval('#toplevel_page_phongdao-ban-lam-viec', li => li.classList.contains('wp-has-current-submenu')).catch(() => false), 'Sửa tác phẩm: menu Phongdao không mở');
      chac(await bt.$eval('#toplevel_page_phongdao-ban-lam-viec .wp-submenu li.current a', a => a.href.includes('page=phongdao-tac-pham')).catch(() => false), 'Sửa tác phẩm: menu không tô mục Tác phẩm');
    });

    await thu('wp-admin thanh chọn trên máy tính: nằm đầu danh sách, dính khi cuộn, nút mờ khi chưa chọn, không che hàng cuối', async () => {
      await bt.setViewport({ width: 1280, height: 800 });
      await di(bt, '/wp-admin/admin.php?page=phongdao-tac-pham&tt=tat_ca');
      await bt.waitForSelector('.phongdao-chon-het:not([hidden]) .phongdao-thanh-noi.o-tren', { visible: true, timeout: 15_000 });
      chac(await bt.$$eval('.phongdao-thanh-noi.o-tren button', bs => bs.length > 0 && bs.every(b => b.disabled)), 'chưa chọn gì mà nút của thanh không mờ');
      // Cuộn xuống cuối: thanh dính ở trên (hoặc chưa tới chỗ dính nếu trang ngắn), ô chọn của hàng cuối bấm trúng (không bị che)
      const t0 = await bt.$eval('.phongdao-chon-het', t => t.getBoundingClientRect().top + scrollY);
      await bt.evaluate(() => window.scrollTo(0, document.documentElement.scrollHeight));
      const daCuon = await bt.evaluate(() => scrollY);
      const canTop = Math.max(32, Math.round(t0 - daCuon));
      const cuoi = await bt.$$eval('input[name="ids[]"]', os => os[os.length - 1].value);
      const trung = await bt.$eval(`input[name="ids[]"][value="${cuoi}"]`, o => { const r = o.getBoundingClientRect(); return r.bottom <= innerHeight && document.elementFromPoint(r.x + r.width / 2, r.y + r.height / 2) === o; });
      chac(trung, 'hàng cuối danh sách bị che, ô chọn không bấm trúng');
      await bt.click(`input[name="ids[]"][value="${cuoi}"]`);
      chac(await bt.$eval('.phongdao-thanh-noi', t => t.textContent.includes('Đã chọn 1 bài') && [...t.querySelectorAll('button')].every(b => !b.disabled)), 'chọn hàng cuối mà thanh không đếm hoặc nút còn mờ');
      const top = await bt.$eval('.phongdao-chon-het', t => Math.round(t.getBoundingClientRect().top));
      chac(Math.abs(top - canTop) <= 8 && await bt.$eval('.phongdao-chon-het', t => getComputedStyle(t).position === 'sticky'), `thanh chọn không dính ở trên khi cuộn (cách đỉnh ${top} px, cần ${canTop} px)`);
      chac(await bt.$$eval('.phongdao-thanh-noi .button-primary', bs => bs.length) <= 1, 'thanh chọn có hơn một nút chính');
      await bam(bt, '.phongdao-thanh-noi-bo');
    });

    await thu('wp-admin Bổ sung: duyệt gợi ý theo nhóm — chọn một chủ đề / vùng, Đồng ý các bài đã chọn trong ≤ 5 bấm; Hộp việc không còn gợi ý', async () => {
      await bt.setViewport({ width: 1280, height: 800 });
      await di(bt, '/wp-admin/admin.php?page=phongdao-hop-viec');
      chac(!(await bt.$('nav[aria-label="Loại việc"] a[href*="loai=goi_y"]')), 'Hộp việc còn tab Gợi ý chủ đề, vùng');
      await di(bt, '/wp-admin/admin.php?page=phongdao-bo-sung');
      const nhom = await bt.$eval('.phongdao-bs-nhom a', a => a.href).catch(() => '');
      chac(nhom, 'Bổ sung không có nhóm gợi ý nào (dữ liệu mẫu có gợi ý chủ đề "Lao động")');
      let bam_ = 1; // mở Bổ sung
      await bamDoiTrang(bt, '.phongdao-bs-nhom a'); bam_++;
      const so = await bt.$$eval('.phongdao-bs-ds input[name="ids[]"]:checked', os => os.length);
      chac(so > 0, 'màn nhóm không có gợi ý đánh dấu sẵn');
      await bamDoiTrang(bt, '.phongdao-bs-goi-y button[name=lam][value=dong_y]'); bam_++;
      chac(await coChu(bt, `Đã đồng ý ${so} gợi ý`), `không báo đã đồng ý ${so} gợi ý`);
      chac(bam_ <= 5, `duyệt một nhóm tốn ${bam_} bấm`);
    });

    await thu('wp-admin Tác phẩm và Bài mới trên điện thoại: mỗi mục một thẻ ba dòng, bộ lọc gập, thấy mục ngay màn đầu', async () => {
      await bt.setViewport({ width: 390, height: 844 });
      for (const [duong, ten] of [['/wp-admin/admin.php?page=phongdao-tac-pham&tt=tat_ca', 'Tác phẩm'], ['/wp-admin/admin.php?page=phongdao-kho', 'Bài mới']]) {
        await di(bt, duong);
        chac(await bt.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${ten} tràn ngang ở 390 px`);
        chac(!(await bt.$eval('details.phongdao-tp-them', d => d.open)), `${ten}: bộ lọc không gập sẵn trên điện thoại`);
        const hang = await bt.$$eval('.phongdao-tp-bang tbody tr', trs => trs.map(tr => Math.round(tr.getBoundingClientRect().height)));
        chac(hang.length > 0, `${ten}: không có mục nào`);
        const dau = await bt.$eval('.phongdao-tp-bang tbody tr', tr => Math.round(tr.getBoundingClientRect().top + scrollY));
        chac(dau < 844, `${ten}: mục đầu tiên nằm dưới màn đầu (y = ${dau})`);
        chac(Math.max(...hang) <= 260, `${ten}: thẻ cao ${Math.max(...hang)} px (30 mục phải gọn trong khoảng 8 màn)`);
        chac(await bt.$eval('.phongdao-tp-bang tbody tr .phongdao-tp-dong2', e => !!e.offsetParent), `${ten}: thẻ không có dòng thứ hai (trạng thái, thể loại)`);
      }
      await bt.setViewport({ width: 1280, height: 800 });
      await di(bt, '/wp-admin/admin.php?page=phongdao-tac-pham&tt=tat_ca');
      chac(await bt.$$eval('.phongdao-tp-tab .phongdao-chip', as => as.map(a => a.textContent.replace(/[\d.]+/g, '').trim()).join('|')) === 'Tất cả|Đã đăng|Bản nháp|Đã ẩn|Thùng rác', 'Tác phẩm không có tab trạng thái kèm số');
      chac(await bt.$('.phongdao-tp-hanh a[href*="phongdao-sua-tac-pham"]') && await bt.$('.phongdao-tp-nhanh a.button'), 'Tác phẩm thiếu Sửa · Xem · Ẩn trên hàng hoặc nút lọc nhanh');
    });

    await thu('wp-admin Bài mới: Xem trước và đăng một câu → màn kết quả có "Bài vừa đăng" kèm Xem bài · Ẩn nếu nhầm; Ẩn một bấm, người đọc không còn thấy', async () => {
      await bt.setViewport({ width: 1280, height: 800 });
      await di(bt, '/wp-admin/admin.php?page=phongdao-kho');
      const mot = await bt.$eval('button[name=mot]', b => b.value).catch(() => '');
      chac(mot, 'Bài mới không còn câu nào để thử đăng');
      await bamDoiTrang(bt, `button[name=mot][value="${mot}"]`);
      await bamDoiTrang(bt, 'button[name=lam][value=ap_dung]');
      await doi(bt, () => document.body.innerText.includes('Lô đã chạy xong.'), null, 'lô đăng một câu không chạy xong', 120_000);
      chac(await coChu(bt, 'Bài vừa đăng'), 'màn kết quả không có "Bài vừa đăng"');
      const xem = await bt.$eval('.phongdao-lo-lui .phongdao-hv-lui a', a => a.href).catch(() => '');
      chac(xem, 'bài vừa đăng không có lối "Xem bài"');
      const tl1 = await k.goto(xem, { waitUntil: 'load', timeout: 120_000 });
      chac(tl1.status() === 200, `người đọc không mở được bài vừa đăng (HTTP ${tl1.status()})`);
      await bamDoiTrang(bt, '.phongdao-lo-lui .phongdao-hv-lui button[type=submit]');
      chac(await coChu(bt, 'Đã ẩn “'), 'bấm "Ẩn nếu nhầm" mà không báo đã ẩn');
      chac(bt.url().includes('page=phongdao-lo'), 'Ẩn nếu nhầm xong không về màn kết quả');
      const tl2 = await k.goto(xem, { waitUntil: 'load', timeout: 120_000 });
      chac(tl2.status() === 404, `bài đã ẩn mà người đọc vẫn mở được (HTTP ${tl2.status()})`);
    });
    await thu('phiên phần bài: wp-admin và sửa nhanh chặn bản cũ, giữ chữ, đối chiếu rồi mới lưu tiếp', async () => {
      await di(bt, '/wp-admin/admin.php?page=phongdao-them');
      await datO(bt, '#them-loi', '[UX-TEST] Dòng kỹ thuật kiểm phiên phần bài');
      await datO(bt, '#them-ten', '[UX-TEST] Hai tab sửa phần');
      await bamDoiTrang(bt, 'button[type=submit][name=dang][value="0"]');
      const id = await bt.$eval('#phan-loi input[name=id]', o => o.value);
      const edit = `/wp-admin/admin.php?page=phongdao-sua-tac-pham&id=${id}`;
      const bt2 = await moTab(trinhDuyet.defaultBrowserContext(), 'người biên soạn · tab cũ');
      try {
        await di(bt2, edit);
        for (const tab of [bt, bt2]) await tab.$eval('#phan-y_nghia', d => { d.open = true; });
        await datO(bt, '#phan-y_nghia textarea[name=chu]', '[UX-TEST] Bản A');
        await bamDoiTrang(bt, '#phan-y_nghia button[name=duyet][value="1"]');
        await datO(bt2, '#phan-y_nghia textarea[name=chu]', '[UX-TEST] Bản B đang nhập');
        await bamDoiTrang(bt2, '#phan-y_nghia button[name=duyet][value="1"]');
        chac(await coChu(bt2, 'Phần này đã đổi'), 'wp-admin không báo xung đột');
        chac(await bt2.$eval('#phan-y_nghia textarea[name=chu]', o => o.value) === '[UX-TEST] Bản B đang nhập', 'xung đột làm mất chữ B');
        await bam(bt2, '#phan-y_nghia [data-phien-moi] > summary');
        chac(await bt2.$eval('#phan-y_nghia [data-phien-moi] pre', o => o.textContent.includes('[UX-TEST] Bản A')), 'không thấy bản A để đối chiếu');
        await bam(bt2, '#phan-y_nghia [data-phien-moi] input[type=checkbox]');
        await bamDoiTrang(bt2, '#phan-y_nghia button[name=duyet][value="1"]');
        chac(await coChu(bt2, 'Đã lưu phần bài.'), 'không lưu được sau khi đã đối chiếu');
        const preview = await bt2.$eval('.phongdao-sua-lien a', a => { const u = new URL(a.parentElement.querySelectorAll('a')[1].href); return u.pathname + u.search; });
        await di(bt, preview); await di(bt2, preview);
        for (const tab of [bt, bt2]) await bam(tab, '[data-action=mo-tam][data-tam=y_nghia]');
        await datO(bt, '#tam-y_nghia textarea[name=chu]', '[UX-TEST] Bản C sửa nhanh');
        await bamDoiTrang(bt, '#tam-y_nghia button[name=duyet][value="1"]');
        await datO(bt2, '#tam-y_nghia textarea[name=chu]', '[UX-TEST] Bản D sửa nhanh');
        await bam(bt2, '#tam-y_nghia button[name=duyet][value="1"]');
        await doi(bt2, () => !!document.querySelector('#tam-y_nghia [data-phien-moi]'), null, 'sửa nhanh không giữ hộp đối chiếu khi xung đột');
        chac(await bt2.$eval('#tam-y_nghia textarea[name=chu]', o => o.value) === '[UX-TEST] Bản D sửa nhanh', 'sửa nhanh làm mất đầu vào');
        await bam(bt2, '#tam-y_nghia [data-phien-moi] > summary');
        await bam(bt2, '#tam-y_nghia [data-phien-moi] input[type=checkbox]');
        await bamDoiTrang(bt2, '#tam-y_nghia button[name=duyet][value="1"]');
        await di(bt, edit);
        chac(await bt.$eval('#phan-y_nghia textarea[name=chu]', o => o.value) === '[UX-TEST] Bản D sửa nhanh', 'không lưu đúng bản đã chọn sau đối chiếu');
      } finally { await bt2.close(); }
    });
    await thu('ảnh minh hoạ: tải bằng Thư viện, xem trước, lưu, khách thấy, bỏ và khôi phục; câu đố giấu ảnh', async () => {
      await di(bt, '/wp-admin/admin.php?page=phongdao-them');
      await datO(bt, '#them-loi', '[UX-TEST] Dòng kiểm luồng tệp ảnh');
      await datO(bt, '#them-ten', '[UX-TEST] Tác phẩm có ảnh');
      await bamDoiTrang(bt, 'button[type=submit][name=dang][value="0"]');
      const edit = new URL(bt.url());
      await bam(bt, '[data-mo-phan=media]');
      await bam(bt, '[data-anh-chon]');
      await bam(bt, '#menu-item-upload');
      const file = await bt.waitForSelector('.media-frame input[type=file]');
      await file.uploadFile(path.resolve('wordpress/v2/plugins/ca-dao/du-lieu-mau/anh-ky-thuat.png'));
      await doi(bt, () => !!document.querySelector('.media-frame .attachment.selected:not(.uploading) .thumbnail img'), null, 'tệp không tải xong trong Thư viện', 60000);
      const alt = '.media-frame [data-setting=alt] textarea';
      await datO(bt, alt, '[UX-TEST] Ảnh tải từ màn sửa');
      await bt.$eval(alt, e => { e.dispatchEvent(new Event('change', { bubbles: true })); e.blur(); });
      const credit = '.media-frame .compat-field-phongdao_ghi_cong textarea';
      await datO(bt, credit, '[UX-TEST] Ghi công mẫu kỹ thuật https://example.org/credit');
      const creditSaved = bt.waitForResponse(async r => r.url().includes('admin-ajax.php') && (r.request().postData() || '').includes('save-attachment-compat') && r.status() === 200);
      await bt.$eval(credit, e => e.dispatchEvent(new Event('change', { bubbles: true })));
      await creditSaved;
      await bam(bt, '.media-button-select');
      await doi(bt, () => Number(document.querySelector('#phan-media [name=attachment_id]').value) > 0 && !!document.querySelector('[data-anh-xem] img'), null, 'chọn ảnh chưa có bản xem trước');
      const idAnh = await bt.$eval('#phan-media [name=attachment_id]', e => e.value);
      chac(await coChu(bt, 'chưa lưu vào bài'), 'không phân biệt chọn ảnh với lưu ảnh vào bài');
      await bamDoiTrang(bt, '#phan-media button[type=submit]');
      chac(await bt.$eval('#phan-media [name=attachment_id]', e => e.value) === idAnh, 'lưu không giữ ảnh đã chọn');
      const xem = await bt.$eval('.phongdao-sua-lien a:nth-child(2)', e => e.href);
      await k.goto(xem, { waitUntil: 'load' });
      chac(!(await k.$('.phongdao-anh-minh-hoa')), 'ảnh của bài nháp hiện cho khách');
      await bamDoiTrang(bt, '.phongdao-quan-ly-bai form:has(input[name=trang_thai][value=publish]) button[type=submit]');
      const publicUrl = await bt.$eval('.phongdao-sua-lien a:nth-child(2)', e => e.href);
      const fault = r => {
        if (r.url().includes('admin-ajax.php') && (r.postData() || '').includes('action=get-attachment')) r.respond({ status: 403, contentType: 'application/json', body: '{"success":false}' });
        else r.continue();
      };
      await bt.setRequestInterception(true); bt.on('request', fault);
      try {
        await bt.reload({ waitUntil: 'load' });
        await doi(bt, () => document.querySelector('[data-anh-bao]').textContent.includes('Chọn lại ảnh'), null, 'lỗi đọc ảnh không có hướng xử lý');
        chac(await bt.$eval('#phan-media [name=attachment_id]', e => e.value) === idAnh, 'lỗi đọc ảnh tự thay liên kết đang lưu');
      } finally { await bt.setRequestInterception(false); bt.off('request', fault); }
      await bt.reload({ waitUntil: 'load' });
      await k.goto(publicUrl, { waitUntil: 'load' });
      await k.waitForSelector('.phongdao-anh-minh-hoa img');
      chac(await k.$eval('.phongdao-anh-minh-hoa img', i => i.naturalWidth > 0 && i.alt === '[UX-TEST] Ảnh tải từ màn sửa' && i.hasAttribute('width')), 'khách thiếu tệp/alt/kích thước ảnh');
      chac(await k.$eval('.phongdao-anh-minh-hoa figcaption', c => c.textContent.includes('[UX-TEST] Ghi công mẫu kỹ thuật') && !!c.querySelector('a[href="https://example.org/credit"]')), 'ghi công đã điền không đến trang khách');
      await bam(bt, '[data-anh-bo]');
      await bamDoiTrang(bt, '#phan-media button[type=submit]');
      await k.reload({ waitUntil: 'load' });
      chac(!(await k.$('.phongdao-anh-minh-hoa')), 'bỏ ảnh mà khách vẫn thấy');
      const restore = '.phongdao-sua-ben form:has(input[name=viec]) button[type=submit]';
      await bamDoiTrang(bt, restore);
      chac(await bt.$eval('#phan-media [name=attachment_id]', e => e.value) === idAnh, 'khôi phục lịch sử không lấy lại ảnh');
      await k.reload({ waitUntil: 'load' });
      chac(!!(await k.$('.phongdao-anh-minh-hoa img')), 'khách không thấy ảnh khôi phục');
      await bam(bt, '[data-anh-chon]');
      await bam(bt, '#menu-item-browse');
      const other = `.media-frame li.attachment:not([data-id="${idAnh}"])`;
      await bam(bt, other);
      const replacement = await bt.$eval(other, e => e.dataset.id);
      await bam(bt, '.media-button-select');
      await bamDoiTrang(bt, '#phan-media button[type=submit]');
      chac(await bt.$eval('#phan-media [name=attachment_id]', e => e.value) === replacement, 'chọn ảnh sẵn để thay nhưng không lưu đúng');
      for (const js of [true, false]) {
        await k.setJavaScriptEnabled(js);
        await di(k, MAU.cauDo);
        chac(await k.$eval('details.answer', d => !d.open && !!d.querySelector('.phongdao-anh-minh-hoa img')), 'ảnh câu đố lộ ra ngoài đáp án');
        await bam(k, 'details.answer > summary');
        chac(await k.$eval('details.answer .phongdao-anh-minh-hoa img', i => i.getBoundingClientRect().height > 0), 'mở đáp án không thấy ảnh');
      }
      await k.setJavaScriptEnabled(true);
      await di(bt, edit.pathname + edit.search);
    });
    await thu('Hiểu thêm gọn: 390/1440 px, bàn phím và không JavaScript vẫn mở được form, đủ consent', async () => {
      for (const width of [390, 1440]) for (const js of [true, false]) {
        await k.setViewport({ width, height: 900 });
        await k.setJavaScriptEnabled(js);
        await di(k, MAU.cauDo);
        chac(await k.$eval('[data-form-cach-hieu]', d => !d.open), 'form trải sẵn khi vừa mở bài');
        const summary = await k.$('[data-form-cach-hieu] > summary');
        await summary.focus(); await k.keyboard.press('Enter');
        chac(await k.$eval('[data-form-cach-hieu]', d => d.open), 'bàn phím không mở được form');
        chac(await k.$$eval('#cach-hieu-form input[type=checkbox][required]', os => os.length) === 2, 'mở gọn làm mất cam kết hoặc đồng ý giấy phép');
        chac(await k.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `form tràn ngang ${width} px`);
        await k.goto('about:blank'); // mở liên kết trực tiếp từ trang khác, có HTTP response để kiểm
        await di(k, MAU.cauDo + '#cach-hieu-form');
        chac(await k.$eval('[data-form-cach-hieu]', d => d.open) && await k.$eval('#cach-hieu-chu', e => e.getBoundingClientRect().height > 0), 'liên kết trực tiếp không mở form');
      }
      await k.setJavaScriptEnabled(true);
    });
		await kiemTrangSua({bt,G,di,bam,datO,bamDoiTrang,chac,thu,thuMucAnh});
		await kiemEditorUrl({bt,G,di,bam,datO,bamDoiTrang,chac,thu,thuMucAnh});
  }
} catch (e) {
  loi.push(String(e.message ?? e).split('\n')[0]);
} finally {
  await trinhDuyet?.close();
  if (may) await may.dong();
}
console.log(`E2E V2 ${SITE ? `(site thật ${SITE}, chỉ đọc — đã chặn ${daChan} yêu cầu ghi)` : '(bản xem trước)'}: ${dat} bước đạt, ${loi.length} lỗi, ${nhac.length} bỏ qua.`);
for (const l of loi) console.log(`LỖI      [${TEN}] ${l}`);
process.exit(loi.length ? 1 : 0);
