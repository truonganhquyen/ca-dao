#!/usr/bin/env node
// Kiểm thử đầu-cuối V2 (docs/29 mục 4): đi từng thao tác như người dùng thật trong Chrome, trên điện thoại 390 px.
//   npm run e2e:v2                          bản xem trước (Playground, dữ liệu mẫu): người đọc rồi người biên soạn
//   npm run e2e:v2 -- --site https://…      site thật sau khi chuyển: chỉ người đọc, CHỈ ĐỌC — trình duyệt chặn mọi yêu cầu
//                                           không phải GET/HEAD (không gửi form, không lượt đọc, không sự kiện đo lường)
//   thêm --anh <thư-mục>: lưu ảnh chụp bước hỏng
// Mỗi bước in ĐẠT hoặc LỖI; thoát mã 1 khi có bước hỏng. Cần Chrome/Chromium; bản xem trước cần Node ≥ 24.18.
import fs from 'node:fs';
import path from 'node:path';
import puppeteer from 'puppeteer-core';
import { dangNhap, kiemNode, moPlayground, timChrome } from './lib/playground-v2.mjs';

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
const chac = (dk, bao) => { if (!dk) throw new Error(bao); };
const thu = async (ten, fn) => {
  try { await fn(); dat++; console.log(`ĐẠT      ${ten}`); }
  catch (e) {
    const chu = String(e.message ?? e).split('\n')[0];
    loi.push(`${ten}: ${chu}`);
    console.log(`LỖI      [${TEN}] ${ten}: ${chu}`);
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
const bam = async (tab, sel) => { const b = await tab.waitForSelector(sel, { visible: true, timeout: 15_000 }).catch(() => null); chac(b, `không thấy ${sel}`); await b.click(); };
// Bấm rồi chờ trang mới (form gửi thẳng, tải lại sau khi lưu)
const bamDoiTrang = async (tab, sel) => { await Promise.all([tab.waitForNavigation({ waitUntil: 'load', timeout: 120_000 }), bam(tab, sel)]); };
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

  if (SITE) boQua('Góp của người đọc', 'chế độ chỉ đọc không gửi form');
  else await thu('Góp: gửi báo lỗi, hiện lời cảm ơn', async () => {
    await di(k, '/gop/');
    await bam(k, 'input[name=loai][value=bao_loi]');
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

    await thu('Câu đố thiếu Ý nghĩa: bộ lọc dẫn tới nút Thêm ý nghĩa, khách không thấy lời nhắc thiếu', async () => {
      await di(bt, '/bien-tap/tac-pham/?loc=thieu-y-nghia');
      const href = await bt.$eval('.ds-tp a.dtp-lien-ket[href*="/cau-do/cau-do-mau/"]', e => e.href).catch(() => '');
      chac(href, 'bộ lọc Thiếu ý nghĩa không có câu đố mẫu');
      await bt.goto(href, { waitUntil: 'load', timeout: 120_000 });
      chac(await bt.$('button[data-tam="y_nghia"]') && await coChu(bt, 'Thêm ý nghĩa'), 'trang câu đố không cho người biên soạn thêm Ý nghĩa');
      await di(k, T.cauDo);
      chac(!(await k.$('button[data-tam="y_nghia"]')) && !(await coChu(k, 'Bài đang được bổ sung ý nghĩa')), 'khách thấy điều khiển hoặc lời nhắc thiếu Ý nghĩa');
    });

    await thu('Biên tập: sửa ý nghĩa chưa duyệt → nháp, người đọc chưa thấy', async () => {
      await di(bt, caDao);
      await bam(bt, '[data-action=mo-tam][data-tam=y_nghia]');
      await datO(bt, 'dialog#tam-y_nghia[open] textarea[name=chu]', yNghia);
      chac(await bt.$eval('dialog#tam-y_nghia input[name=duyet]', o => !o.checked), 'ô "đã duyệt" đánh sẵn');
      await bamDoiTrang(bt, 'dialog#tam-y_nghia [type=submit]');
      await baoCo(bt, 'nháp vào Hộp việc');
      await di(k, caDao);
      chac(!(await coChu(k, yNghia)), 'người đọc đã thấy nháp');
    });

    await thu('wp-admin Hộp việc: lọc thẻ, mở đúng form quyết và dùng được ở điện thoại', async () => {
      await di(bt, '/wp-admin/admin.php?page=phongdao-hop-viec&tt=cho&loai=nhap_y_nghia');
      chac(await bt.$('.phongdao-admin-viec .widefat tbody tr a[href*="viec="]'), 'danh sách admin không có thẻ nháp');
      const dich = await bt.$eval('.phongdao-admin-viec .widefat tbody tr a[href*="viec="]', e => e.href);
      await bt.goto(dich, { waitUntil: 'load', timeout: 120_000 });
      chac(await bt.$('.phongdao-admin-viec article.the-viec form input[name="_wpnonce"]') && await bt.$('.phongdao-admin-viec article.the-viec form input[name="phien"]'), 'thẻ admin không dùng form quyết có nonce và phiên');
      await bt.setViewport({ width: 390, height: 844 });
      chac(await bt.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'màn Hộp việc admin tràn ngang ở 390 px');
      await bt.setViewport({ width: 1280, height: 800 });
    });

    await thu('Hộp việc: J/K đổi thẻ, phím số chọn nút, không kích hoạt khi đang nhập hoặc focus nút', async () => {
      await di(bt, '/bien-tap/?tung=1');
      chac(await bt.$('.tung-the [data-hv="sau"]'), 'không có thẻ sau để thử J/K');
      chac(await bt.$eval('[data-hv="goi-y"]', e => !e.hidden), 'không hiện hướng dẫn phím tắt khi JavaScript chạy');
      const dau = bt.url();
      const theDau = await bt.$eval('.tung-the article.the-viec', e => e.id);
      await Promise.all([bt.waitForNavigation({ waitUntil: 'load' }), bt.keyboard.press('j')]);
      chac(bt.url() !== dau && await bt.$('.tung-the [data-hv="truoc"]'), 'J không tới thẻ sau');
      await Promise.all([bt.waitForNavigation({ waitUntil: 'load' }), bt.keyboard.press('k')]);
      chac(theDau === await bt.$eval('.tung-the article.the-viec', e => e.id), 'K không về thẻ trước');
      const veDau = bt.url();
      await bt.evaluate(() => {
        window.__phimQuyet = [];
        const f = document.querySelector('.tung-the form');
        f.addEventListener('submit', e => { e.preventDefault(); window.__phimQuyet.push(e.submitter?.value); });
        const o = document.createElement('input');
        o.id = 'kiem-phim-nhap';
        document.querySelector('.tung-the').prepend(o);
        o.focus();
      });
      await bt.keyboard.press('j');
      await bt.keyboard.press('1');
      chac(bt.url() === veDau && 0 === await bt.evaluate(() => window.__phimQuyet.length), 'phím tắt chạy khi đang nhập');
      await bt.focus('.tung-the button[name="quyet"]');
      await bt.keyboard.press('j');
      chac(bt.url() === veDau, 'J chạy khi focus ở nút');
      const nut = await bt.$$eval('.tung-the button[name="quyet"]', ds => ds.slice(0, 3).map(n => n.value));
      await bt.evaluate(() => { document.getElementById('kiem-phim-nhap').remove(); document.activeElement.blur(); });
      for (let i = 0; i < nut.length; i++) await bt.keyboard.press(String(i + 1));
      chac(JSON.stringify(await bt.evaluate(() => window.__phimQuyet)) === JSON.stringify(nut), 'phím 1/2/3 không chọn đúng nút');
    });

    await thu('wp-admin Hộp việc: Duyệt nháp ý nghĩa → người đọc thấy, thẻ đóng không hiện nút quyết', async () => {
      await di(bt, `/wp-admin/admin.php?page=phongdao-hop-viec&tt=cho&loai=nhap_y_nghia&q=${encodeURIComponent(yNghia)}`);
      const dich = await bt.$eval('.phongdao-admin-viec .widefat tbody tr a[href*="viec="]', e => e.href).catch(() => '');
      chac(dich, 'không tìm thấy đúng thẻ nháp ý nghĩa trong admin');
      await bt.goto(dich, { waitUntil: 'load', timeout: 120_000 });
      chac(await coChu(bt, yNghia), 'chi tiết thẻ không có đúng nháp cần duyệt');
      await bamDoiTrang(bt, '.phongdao-admin-viec article.the-viec button[name=quyet][value=duyet]');
      chac(await coChu(bt, 'Đã duyệt') && !(await bt.$('.phongdao-admin-viec article.the-viec button[name=quyet]')), 'quyết trong admin chưa đóng thẻ hoặc còn nút quyết');
      await di(k, caDao);
      chac(await coChu(k, yNghia), 'duyệt rồi người đọc vẫn chưa thấy');
    });

    await thu('wp-admin Sửa tác phẩm: lưu Ý nghĩa qua cùng dịch vụ, người đọc thấy bản đã duyệt', async () => {
      const moi = 'Ý nghĩa thử từ biểu mẫu quản trị.';
      await di(bt, caDao);
      const id = await bt.$eval('.dai-bien-tap', e => e.dataset.bai);
      await di(bt, `/wp-admin/admin.php?page=phongdao-sua-tac-pham&id=${id}`);
      chac(await bt.$('#phan-y_nghia form') && await coChu(bt, 'Việc đang chờ'), 'không mở được trang sửa có cấu trúc');
      await bam(bt, '#phan-y_nghia summary');
      await datO(bt, '#phan-y_nghia textarea[name=chu]', moi);
      await bam(bt, '#phan-y_nghia input[name=duyet]');
      await bamDoiTrang(bt, '#phan-y_nghia form [type=submit]');
      chac(await coChu(bt, 'Đã lưu phần bài.'), 'wp-admin không báo lưu thành công');
      await di(k, caDao);
      chac(await coChu(k, moi), 'người đọc không thấy Ý nghĩa vừa duyệt trong wp-admin');
    });

    await thu('Hộp việc: Để sau rồi Hoàn tác trong 10 giây, sau đó xử lý thẻ', async () => {
      await di(bt, '/bien-tap/?loai=nguoi_doc');
      const id = await bt.$$eval('article.the-viec[data-loai=nguoi_doc]', as => as.find(a => a.textContent.includes('Góp thử đầu-cuối'))?.id);
      chac(id, 'không có thẻ góp vừa gửi');
      await bamDoiTrang(bt, `#${id} button[name=quyet][value=de_sau]`);
      chac(await bt.$('[data-hv-hoan] button:not([disabled])'), 'Để sau không hiện nút Hoàn tác còn hạn');
      await bamDoiTrang(bt, '[data-hv-hoan] button');
      chac(await coChu(bt, 'Đã hoàn tác quyết định thẻ') && await bt.$(`#${id}`), 'Hoàn tác không đưa thẻ về Chờ');
      const nutXong = `#${id} button[name=quyet][value=xong]`;
      // Cuộn ngay: CSS của trang dùng smooth scrolling, nên kiểm hit-test trước khi cuộn xong có thể trúng thanh dưới.
      await bt.$eval(nutXong, e => e.scrollIntoView({ block: 'center', behavior: 'instant' }));
      chac(await bt.$eval(nutXong, e => { const r = e.getBoundingClientRect(); return e === document.elementFromPoint(r.left + r.width / 2, r.top + r.height / 2); }), 'nút Đã xử lý bị thanh điều hướng che');
      await bamDoiTrang(bt, nutXong);
      await di(bt, '/bien-tap/?loai=nguoi_doc');
      chac(!(await bt.$(`#${id}`)), 'thẻ vẫn còn sau khi xử lý');
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
    await thu('Thêm bài: dán lời, máy nhận thể thơ, đăng ngay', async () => {
      await di(bt, '/bien-tap/them/');
      await bam(bt, 'label.chip-chon:has(input[name=the_loai][value="tuc-ngu"])');
      chac(await bt.$eval('input[name=the_loai][value="tuc-ngu"]', o => o.checked), 'chọn thể loại không được');
      await bt.type('form[data-them-bai] [name=loi]', loiMoi);
      await doi(bt, () => /Máy nhận/.test(document.getElementById('them-phan-tich')?.textContent || ''), null, 'máy không nhận thể thơ');
      chac(await bt.$eval('form[data-them-bai] [name=ten]', o => o.value) === loiMoi, 'tên không tự lấy dòng đầu');
      await bamDoiTrang(bt, 'form[data-them-bai] button[name=dang][value="1"]');
      baiMoi = new URL(bt.url()).pathname;
      chac(baiMoi.startsWith('/tuc-ngu/') && await coChu(bt, loiMoi), `không mở bài vừa đăng (${baiMoi})`);
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
