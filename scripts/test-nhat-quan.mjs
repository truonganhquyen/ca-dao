#!/usr/bin/env node
// Kiểm thử các hàm phân tích của kiem-nhat-quan (scripts/lib/backlog.mjs) — một phần của `npm run kiem-tra`.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import { maQuyetDinhTrung } from './lib/backlog.mjs';
import { phienBanRepo, phienBanTrang, chenhLech, baiDauTien, thuMucTrienKhai } from './lib/phien-ban.mjs';
import { demTrangChu, dauTheLoai, locs, loaiSoDo, theLoaiCuaBai, robots, bangNguon, theTrongDanhSach, soTrang, gioVN, soVN } from './lib/so-nen.mjs';

const bang = ids => ids.map(id => `| ${id} | Quyết định | Lý do | Nguồn |`).join('\n');
assert.deepEqual(maQuyetDinhTrung(bang(['D-001', 'D-002', 'D-050'])), [], 'không trùng thì không báo');
assert.deepEqual(maQuyetDinhTrung(bang(['D-050', 'D-051', 'D-050'])), ['D-050'], 'trùng một số');
assert.deepEqual(maQuyetDinhTrung(bang(['D-050', 'D-050', 'D-051', 'D-051', 'D-052'])), ['D-050', 'D-051'], 'trùng nhiều số');
assert.deepEqual(maQuyetDinhTrung('Xem D-050 và D-050 trong câu văn; | D-050 | chỉ ở đầu hàng mới tính'), [], 'chỉ tính số ở đầu hàng bảng');
// Dữ liệu thật hiện tại phải sạch
assert.deepEqual(maQuyetDinhTrung(fs.readFileSync('docs/01-nhat-ky-quyet-dinh.md', 'utf8')), [], 'docs/01 hiện đang có số D trùng');
// Kiểm "site thật đúng bản" (T-129)
const mau = '<link href="https://phongdao.com/wp-content/themes/ca-dao/style.css?ver=0.19.1"><script src="https://phongdao.com/wp-content/plugins/ca-dao/assets/ca-dao.js?ver=0.33.1"></script>';
assert.deepEqual(phienBanTrang(mau), { plugin: '0.33.1', theme: '0.19.1' }, 'đọc phiên bản từ ?ver= của tệp plugin, theme');
assert.deepEqual(phienBanTrang('<p>không có tệp</p>'), { plugin: null, theme: null }, 'trang không có tệp thì không đoán số');
assert.deepEqual(phienBanTrang('<link href="https://phongdao.com/wp-content/themes/ca-dao/assets/giao-dien.css?ver=1.0.0"><script src="https://phongdao.com/wp-content/plugins/ca-dao/assets/do-luong.js?ver=1.0.0"></script>'), { plugin: '1.0.0', theme: '1.0.0' }, 'V2 (sau ngày chuyển): đọc do-luong.js, giao-dien.css');
assert.deepEqual(chenhLech({ plugin: '0.33.1', theme: '0.19.1' }, { plugin: '0.33.1', theme: '0.19.1' }), [], 'khớp thì không báo');
assert.deepEqual(chenhLech({ plugin: '0.33.1', theme: '0.19.1' }, { plugin: '0.28.0', theme: '0.19.1' }), ['plugin: repo 0.33.1, site 0.28.0'], 'plugin cũ thì báo (lần kẹt 0.28.0 ngày 26–27/10)');
assert.equal(chenhLech({ plugin: '0.33.1', theme: '0.19.1' }, { plugin: null, theme: null }).length, 2, 'không đọc được số trên site cũng là lệch');
assert.equal(baiDauTien('<a href="https://phongdao.com/tu-dien/">x</a><a href="https://phongdao.com/ca-dao/cong-cha-nhu-nui-thai-son-2/">y</a>', 'https://phongdao.com'), 'https://phongdao.com/ca-dao/cong-cha-nhu-nui-thai-son-2/', 'lấy trang bài đầu tiên, bỏ qua trang khác');
const rp = phienBanRepo();
assert.match(rp.plugin ?? '', /^\d+\.\d+\.\d+$/, 'đọc được CADAO_PHIEN_BAN trong repo');
assert.match(rp.theme ?? '', /^\d+\.\d+\.\d+$/, 'đọc được Version: của theme trong repo');
assert.equal(rp.plugin, fs.readFileSync(`${thuMucTrienKhai()}/plugins/ca-dao/ca-dao.php`, 'utf8').match(/\* Version:\s*([0-9.]+)/)[1], 'CADAO_PHIEN_BAN khớp dòng Version: của plugin');
// Số đo nền (T-230): bộ phân tích chỉ đếm, không lấy chữ
const G = 'https://phongdao.com';
assert.deepEqual(demTrangChu('<a href="/the-loai/ca-dao/" class="tile tl g-ca-dao"><span class="tl-chu" aria-hidden="true">C</span><strong>Ca dao</strong><span>11.764 bài</span></a><a href="/the-loai/ve/" class="tile tl g-ve"><strong>Vè</strong><span>138 bài</span></a><a href="/kho/">Kho <span>5 bài</span></a>'), { 'ca-dao': 11764, ve: 138 }, 'bộ đếm trang chủ chỉ đọc ô thể loại');
assert.deepEqual(dauTheLoai('<p class="dau-tl-so"><span><b>3.487</b> bài</span><span><b>120</b> bài đã có giải nghĩa</span></p>'), { bai: 3487, giaiNghia: 120 }, 'đầu trang thể loại: số bài và số có giải nghĩa');
assert.deepEqual(dauTheLoai('<p class="dau-tl-so"><span><b>138</b> bài</span></p>'), { bai: 138, giaiNghia: 0 }, 'không in dòng giải nghĩa thì là 0');
assert.equal(dauTheLoai('<h1>Không phải trang thể loại</h1>'), null, 'không có đầu trang thể loại thì không đoán');
assert.deepEqual(locs('<sitemapindex><sitemap><loc>https://phongdao.com/wp-sitemap-posts-tac_pham-1.xml</loc></sitemap></sitemapindex>'), ['https://phongdao.com/wp-sitemap-posts-tac_pham-1.xml'], 'đọc <loc> của tệp chỉ mục');
assert.equal(loaiSoDo('https://phongdao.com/wp-sitemap-posts-tac_pham-1.xml'), 'tac_pham', 'tệp con của tác phẩm');
assert.equal(loaiSoDo('https://phongdao.com/wp-sitemap-taxonomies-chu_de-1.xml'), 'chu_de', 'tệp con của chủ đề');
assert.equal(loaiSoDo('https://phongdao.com/wp-sitemap.xml'), 'khac', 'tệp chỉ mục không phải tệp con');
assert.equal(theLoaiCuaBai(`${G}/ca-dao/con-vua-thi-lai-lam-vua-sai/`, G), 'ca-dao', 'thể loại lấy từ đường dẫn bài');
assert.equal(theLoaiCuaBai(`${G}/the-loai/ca-dao/`, G), null, 'trang thể loại không phải bài');
assert.equal(theLoaiCuaBai(`${G}/ca-dao/a/b/`, G), null, 'đường dẫn hai cấp dưới thể loại không phải bài');
assert.equal(robots("<meta name='robots' content='noindex, follow' />"), 'noindex, follow', 'đọc thẻ robots của WordPress');
assert.equal(robots('<title>x</title>'), '', 'không có thẻ robots');
assert.deepEqual(bangNguon('<dl><dt>Lời</dt><dd>Bản lưu truyền phổ biến, chưa đối chiếu với sách in. </dd><dt>Ý nghĩa</dt><dd>Người biên soạn của thư viện viết; có hỗ trợ của AI, đã duyệt.</dd><dt>Chú thích</dt><dd>3 mục chép từ nguồn tham khảo · nguồn đã ẩn theo <a href="/chinh-sach/">Chính sách thu thập dữ liệu</a><br>1 mục người biên soạn viết<br>2 mục theo <a href="/tu-dien/">Từ điển</a> của thư viện (người biên soạn nối nghĩa vào bài)</dd><dt>Cách làm</dt><dd><a href="/phuong-phap/">Cách chúng tôi biên soạn</a></dd></dl>'),
  { coBang: true, yNghia: true, yNghiaAi: true, ctMay: 3, ctViet: 1, ctTuDien: 2, cqMay: 0, cqViet: 0, doiChieu: false }, 'bảng nguồn ca dao: Ý nghĩa có AI, ba loại chú thích');
assert.deepEqual(bangNguon('<dt>Lời</dt><dd>Đã đối chiếu với Sách mẫu.</dd><dt>Giải thích đáp án</dt><dd>Người biên soạn của thư viện viết.</dd><dt>Chuyện quanh bài</dt><dd>2 mục người biên soạn viết, nguồn ghi tại chỗ</dd>'),
  { coBang: true, yNghia: true, yNghiaAi: false, ctMay: 0, ctViet: 0, ctTuDien: 0, cqMay: 0, cqViet: 2, doiChieu: true }, 'bảng nguồn câu đố: giải thích đáp án, đối chiếu sách, chuyện quanh bài');
assert.deepEqual(theTrongDanhSach('<article class="card the-loi g-ca-dao"><span class="tag">Ca dao</span><h3><a href="https://phongdao.com/ca-dao/a/">A</a></h3><div class="row between"><span class="small muted"></span><a class="btn compact ghost" href="https://phongdao.com/ca-dao/a/" aria-label="Đọc: A">Đọc</a></div></article><aside><a href="https://phongdao.com/ve/khong-tinh/">x</a></aside><article class="card the-loi g-ve"><h3><a href="https://phongdao.com/ve/b/">B</a></h3><a class="btn compact ghost" href="https://phongdao.com/ve/b/" aria-label="Đọc: B">Đọc</a></article>'),
  ['https://phongdao.com/ca-dao/a/', 'https://phongdao.com/ve/b/'], 'chỉ đếm thẻ bài của danh sách');
assert.equal(soTrang('<a class="btn" href="/kho/page/2/" aria-label="Trang 2">2</a><a class="btn" href="/kho/page/549/" aria-label="Trang 549">549</a>'), 549, 'số trang lớn nhất');
assert.equal(soTrang('<p>một trang</p>'), 1, 'không có phân trang thì một trang');
assert.equal(gioVN(Date.UTC(2026, 10, 21, 7, 23, 0)), '2026-11-21T14:23:00+07:00', 'giờ Việt Nam có múi giờ');
assert.equal(soVN(16453), '16.453', 'số kiểu Việt');
assert.equal(soVN(216), '216', 'số nhỏ không có dấu chấm');
console.log('Kiểm thử kiểm nhất quán: đạt.');
