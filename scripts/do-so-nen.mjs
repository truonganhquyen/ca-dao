#!/usr/bin/env node
// Số đo nền (T-230, docs/57 mục 4) — CHỈ ĐỌC: GET công khai, không đăng nhập, không chạy JavaScript của trang.
// Lượt đọc chỉ được cộng khi JavaScript của trang gọi REST `luot-doc`, nên công cụ này không làm sai số liệu đọc.
// Chỉ in số đếm (và đường dẫn công khai khi dùng --ds); không lưu lời, chú thích hay dữ liệu cá nhân.
//
//   node scripts/do-so-nen.mjs              trang chủ, đầu trang 5 thể loại, sơ đồ trang, số bài viết (~25 lượt GET)
//   node scripts/do-so-nen.mjs --trang      thêm: đọc từng bài trong sơ đồ trang — thẻ robots, bảng nguồn
//   node scripts/do-so-nen.mjs --kho        thêm: đếm thẻ bài ở mọi trang /kho/ để so với bộ đếm trang chủ (~550 lượt)
//   node scripts/do-so-nen.mjs --ds <tệp>   thêm: trạng thái từng đường dẫn trong tệp (mỗi dòng một) — cho pilot
//                                           (docs/57 mục 5.5); giữ tệp danh sách và kết quả ngoài repo
//   --json <tệp>                            ghi thêm kết quả JSON (đặt ngoài repo)
import fs from 'node:fs';
import { THE_LOAI, demTrangChu, dauTheLoai, locs, loaiSoDo, theLoaiCuaBai, robots, bangNguon, theTrongDanhSach, soTrang, gioVN, soVN } from './lib/so-nen.mjs';

const GOC = (process.env.SITE_GOC || 'https://phongdao.com').replace(/\/$/, '');
const co = k => process.argv.includes(k);
const sau = k => { const i = process.argv.indexOf(k); return i > 0 ? process.argv[i + 1] ?? null : null; };
const TEN = { 'ca-dao': 'Ca dao', 'tuc-ngu': 'Tục ngữ – thành ngữ', 'cau-do': 'Câu đố', 'dong-dao': 'Đồng dao', 've': 'Vè' };
const NGHI_MS = 150; // nghỉ giữa hai lượt GET cho nhẹ máy chủ
const ngu = ms => new Promise(r => setTimeout(r, ms));
let luot = 0;

async function lay(url) {
  if (luot++) await ngu(NGHI_MS);
  let loi;
  for (let thu = 0; thu < 3; thu++) {
    try {
      const r = await fetch(url, { headers: { 'user-agent': 'phongdao-do-so-nen' }, redirect: 'follow' });
      const chu = await r.text();
      return { ma: r.status, chu: r.ok ? chu : '', tong: r.headers.get('x-wp-total') };
    } catch (e) {
      loi = e;
      await ngu(1000 * (thu + 1));
    }
  }
  throw new Error(`${url}: ${loi?.cause?.code || loi?.message}`);
}

const batDau = Date.now();
const kq = { goc: GOC, bat_dau: gioVN(batDau) };

// 1. Bộ đếm trang chủ = số đếm của từng thể loại (DuLieuTrangChu::so_bai_the_loai)
kq.trang_chu = demTrangChu((await lay(`${GOC}/`)).chu);

// 2. Đầu trang thể loại: số bài và "N bài đã có giải nghĩa"
kq.dau_the_loai = {};
for (const tl of THE_LOAI) kq.dau_the_loai[tl] = dauTheLoai((await lay(`${GOC}/the-loai/${tl}/`)).chu);

// 3. Sơ đồ trang: đếm theo tệp con; tác phẩm đếm theo thể loại ở đường dẫn
const soDo = {};
const baiSoDo = [];
for (const tep of locs((await lay(`${GOC}/wp-sitemap.xml`)).chu)) {
  const ds = locs((await lay(tep)).chu);
  const loai = loaiSoDo(tep);
  soDo[loai] = (soDo[loai] ?? 0) + ds.length;
  if ('tac_pham' === loai) baiSoDo.push(...ds);
}
kq.so_do = soDo;
kq.so_do_the_loai = Object.fromEntries(THE_LOAI.map(tl => [tl, baiSoDo.filter(u => theLoaiCuaBai(u, GOC) === tl).length]));
kq.so_do_lac = baiSoDo.filter(u => !theLoaiCuaBai(u, GOC)).length;
kq.so_do_ds = baiSoDo; // chỉ ghi vào JSON (--json, đặt ngoài repo) để so lần đo sau; không in

// 4. Bài viết (post): tổng do REST công khai báo
const rest = await lay(`${GOC}/wp-json/wp/v2/posts?per_page=1&_fields=id`);
kq.bai_viet = rest.tong === null ? null : Number(rest.tong);

// 5. --trang: từng bài trong sơ đồ trang
if (co('--trang')) {
  const moi = () => ({ doc: 0, noindex: 0, coBang: 0, yNghia: 0, yNghiaAi: 0, ctViet: 0, ctTuDien: 0, ctMay: 0, cqViet: 0, doiChieu: 0, khongYNghia: 0 });
  kq.trang = Object.fromEntries(THE_LOAI.map(tl => [tl, moi()]));
  for (const u of baiSoDo) {
    const tl = theLoaiCuaBai(u, GOC);
    if (!tl) continue;
    const r = await lay(u);
    const d = kq.trang[tl];
    if (200 !== r.ma) continue;
    d.doc++;
    if (robots(r.chu).includes('noindex')) d.noindex++;
    const b = bangNguon(r.chu);
    d.coBang += b.coBang ? 1 : 0;
    d.yNghia += b.yNghia ? 1 : 0;
    d.yNghiaAi += b.yNghiaAi ? 1 : 0;
    d.ctViet += b.ctViet > 0 ? 1 : 0;
    d.ctTuDien += b.ctTuDien > 0 ? 1 : 0;
    d.ctMay += b.ctMay > 0 ? 1 : 0;
    d.cqViet += b.cqViet > 0 ? 1 : 0;
    d.doiChieu += b.doiChieu ? 1 : 0;
    d.khongYNghia += b.yNghia ? 0 : 1;
  }
}

// 6. --kho: mọi trang /kho/
if (co('--kho')) {
  const dau = await lay(`${GOC}/kho/`);
  const n = soTrang(dau.chu);
  const the = [...theTrongDanhSach(dau.chu)];
  for (let p = 2; p <= n; p++) the.push(...theTrongDanhSach((await lay(`${GOC}/kho/page/${p}/`)).chu));
  const rieng = new Set(the);
  kq.kho = {
    so_trang: n,
    so_the: the.length,
    duong_dan_rieng: rieng.size,
    theo_the_loai: Object.fromEntries(THE_LOAI.map(tl => [tl, [...rieng].filter(u => theLoaiCuaBai(u, GOC) === tl).length])),
    bai_so_do_co_o_kho: baiSoDo.filter(u => rieng.has(u)).length,
  };
}

// 7. --ds <tệp>: trạng thái từng đường dẫn (pilot)
const tepDs = sau('--ds');
if (tepDs) {
  const trongSoDo = new Set(baiSoDo);
  kq.ds = [];
  for (const u of fs.readFileSync(tepDs, 'utf8').split(/\r?\n/).map(s => s.trim()).filter(s => s && !s.startsWith('#'))) {
    const r = await lay(u);
    const rb = 200 === r.ma ? robots(r.chu) : '';
    kq.ds.push({ url: u, http: r.ma, trong_so_do: trongSoDo.has(u), robots: rb, du_dieu_kien: trongSoDo.has(u) && 200 === r.ma && !rb.includes('noindex') });
  }
}

kq.xong = gioVN();
kq.luot_get = luot;

// In kết quả (Markdown)
const tong = o => Object.values(o).reduce((a, b) => a + (Number(b) || 0), 0);
const dong = [];
dong.push(`# Số đo nền — ${GOC}`, '', `Bắt đầu ${kq.bat_dau} · xong ${kq.xong} · ${luot} lượt GET · chỉ đọc`, '');
dong.push('## Tác phẩm theo thể loại', '');
const coKho = Boolean(kq.kho);
dong.push(`| Thể loại | Đã xuất bản (trang chủ) | Đầu trang thể loại | Có phần nghĩa công khai¹ | Trong sơ đồ trang = đủ điều kiện lập chỉ mục |${coKho ? ' Thẻ ở /kho/ |' : ''}`);
dong.push(`|---|---|---|---|---|${coKho ? '---|' : ''}`);
for (const tl of THE_LOAI) {
  const d = kq.dau_the_loai[tl];
  dong.push(`| ${TEN[tl]} | ${soVN(kq.trang_chu[tl] ?? 0)} | ${d?.bai == null ? '—' : soVN(d.bai)} | ${d ? soVN(d.giaiNghia) : '—'} | ${soVN(kq.so_do_the_loai[tl])} |${coKho ? ` ${soVN(kq.kho.theo_the_loai[tl])} |` : ''}`);
}
dong.push(`| **Tổng** | **${soVN(tong(kq.trang_chu))}** | ${soVN(tong(Object.fromEntries(THE_LOAI.map(tl => [tl, kq.dau_the_loai[tl]?.bai]))))} | ${soVN(tong(Object.fromEntries(THE_LOAI.map(tl => [tl, kq.dau_the_loai[tl]?.giaiNghia]))))} | **${soVN(baiSoDo.length)}** |${coKho ? ` ${soVN(kq.kho.duong_dan_rieng)} |` : ''}`);
dong.push('', '¹ Dòng "N bài đã có giải nghĩa" ở đầu trang thể loại: Ý nghĩa, giải nghĩa sách, nghĩa đen / bóng, hình ảnh, bối cảnh — mọi người làm. **Không phải** số bài đã biên tập (H2).', '');
dong.push('- **Đã biên tập (≥ H2):** chưa đo — chỉ có từ phiếu duyệt của pilot (docs/57 mục 3.4).');
dong.push('- **Google đã lập chỉ mục:** chưa đo — chỉ lấy từ Search Console.', '');
dong.push('## Sơ đồ trang', '');
dong.push(`- Tác phẩm: ${soVN(baiSoDo.length)}${kq.so_do_lac ? ` (trong đó ${kq.so_do_lac} đường dẫn không theo mẫu /thể-loại/tên/)` : ''}`);
const khac = Object.entries(soDo).filter(([k]) => 'tac_pham' !== k);
dong.push(`- Đường dẫn khác: ${soVN(tong(Object.fromEntries(khac)))} — ${khac.map(([k, v]) => `${k} ${v}`).join(' · ')}`, '');
dong.push(`## Bài viết (post)`, '', `- ${kq.bai_viet ?? 'không đọc được'} (REST công khai, X-WP-Total)`, '');
if (kq.trang) {
  dong.push('## Bài trong sơ đồ trang (đọc từng trang)', '');
  dong.push('| Thể loại | Đọc được | Có noindex | Ý nghĩa người viết | … có hỗ trợ AI | Có chú thích người viết | Có chú thích theo Từ điển | Có chuyện quanh bài người viết | Lời đã đối chiếu sách | Không có Ý nghĩa người viết |');
  dong.push('|---|---|---|---|---|---|---|---|---|---|');
  const cot = ['doc', 'noindex', 'yNghia', 'yNghiaAi', 'ctViet', 'ctTuDien', 'cqViet', 'doiChieu', 'khongYNghia'];
  for (const tl of THE_LOAI) dong.push(`| ${TEN[tl]} | ${cot.map(c => kq.trang[tl][c]).join(' | ')} |`);
  dong.push(`| **Tổng** | ${cot.map(c => tong(Object.fromEntries(THE_LOAI.map(tl => [tl, kq.trang[tl][c]])))).join(' | ')} |`, '');
}
if (kq.kho) {
  dong.push('## Trang /kho/', '');
  dong.push(`- ${kq.kho.so_trang} trang, ${soVN(kq.kho.so_the)} thẻ, ${soVN(kq.kho.duong_dan_rieng)} đường dẫn khác nhau.`);
  dong.push(`- So bộ đếm trang chủ (${soVN(tong(kq.trang_chu))}): chênh ${soVN(tong(kq.trang_chu) - kq.kho.duong_dan_rieng)}.`);
  dong.push(`- Bài trong sơ đồ trang có mặt ở /kho/: ${kq.kho.bai_so_do_co_o_kho}/${baiSoDo.length}.`, '');
}
if (kq.ds) {
  dong.push('## Trạng thái từng bài (--ds) — giữ ngoài repo', '');
  dong.push('| Đường dẫn | HTTP | Trong sơ đồ trang | robots | Đủ điều kiện lập chỉ mục |', '|---|---|---|---|---|');
  for (const d of kq.ds) dong.push(`| ${d.url} | ${d.http} | ${d.trong_so_do ? 'có' : 'không'} | ${d.robots || '—'} | ${d.du_dieu_kien ? 'có' : 'không'} |`);
  dong.push('', 'Đã biên tập (H2) lấy từ phiếu duyệt; Google đã lập chỉ mục lấy từ Search Console.', '');
}
console.log(dong.join('\n'));
const tepJson = sau('--json');
if (tepJson) fs.writeFileSync(tepJson, JSON.stringify(kq, null, 2));
