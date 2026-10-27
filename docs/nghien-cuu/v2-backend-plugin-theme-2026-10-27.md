# Phongdao V2 — Nghiên cứu backend: plugin và theme `ca-dao`

| | |
|---|---|
| Ngày | 27/10/2026 |
| Người soạn | Claude Code (agent), phiên bổ sung theo yêu cầu của người sáng lập |
| Trạng thái | **Nghiên cứu, chờ quyết.** Đi sâu phần kỹ thuật của `v2-nghien-cuu-tong-hop-2026-10-27.md` (mục 4, 5). Chưa đổi mã, chưa tạo task |
| Căn cứ | Mã trên `main`: plugin 0.33.2 (`wordpress/plugins/ca-dao/`), theme 0.19.1 (`wordpress/themes/ca-dao/`), `wordpress/tests/`, `tests/e2e/`, `wordpress/may-chu/` |
| Đọc kèm | `v2-ux-ui-2026-10-27.md` (giao diện mà backend này phục vụ) |

Tài liệu trả lời ba câu: **xây mới hay cải tiến**, **kiến trúc đích** của plugin và theme, **cách đi tới đó** mà site không ngừng chạy và không mất dữ liệu.

---

## 1. Kết luận

| Thành phần | Đề xuất | Lý do ngắn |
|---|---|---|
| **Plugin `ca-dao`** | **Cải tiến tại chỗ, thay dần từng phần.** Giữ tên plugin, loại bài `cdtn`, đường dẫn, các bảng đang có. Mã mới viết theo cấu trúc mới; mã cũ được thay dần rồi gỡ | Hơn 10.000 dòng đang chạy đúng, có dữ liệu thật, có bộ kiểm 1.486 dòng. Chống trùng, bộ đọc nguồn, chỉ mục tìm đã chín. Viết lại từ đầu là bỏ đi tài sản lớn nhất và mất vài tuần không giao được gì |
| **Theme `ca-dao`** | **Dựng mới thành theme khối độc lập (bản 1.0), giữ tên `ca-dao`.** Chuyển hết nghiệp vụ còn nằm trong theme sang plugin | Theme nhỏ (401 dòng PHP, 1.408 dòng CSS, 6 mẫu), lại phải thay gần hết vì giao diện V2 đổi sâu. Hiện theme đang làm việc của plugin (mục 2.2) |
| **Trang quản trị mới** | **Ứng dụng React của WordPress** (`@wordpress/components`, `@wordpress/data`) nằm trong plugin; mã đã dựng được commit vào repo | Máy chủ lấy mã thẳng từ `main`, không có bước dựng (mục 2.4). Hộp việc, Duyệt nhanh, trình soạn có cấu trúc cần tương tác nhanh |
| **Trang người đọc** | Khối động dựng trên máy chủ + JavaScript thuần nhỏ như hiện nay. API Interactivity của WordPress chỉ dùng nếu đo thấy vẫn trong ngân sách | Giữ 177 KB, tắt JavaScript vẫn đọc được |

---

## 2. Hiện trạng kiến trúc

### 2.1. Plugin

| Mặt | Hiện trạng | Hệ quả |
|---|---|---|
| Tổ chức mã | `ca-dao.php` nạp tuần tự 40 tệp `inc/*.php`; toàn bộ là hàm toàn cục `cadao_*` và hằng số; không namespace, không tự nạp | Thứ tự nạp quan trọng ngầm; khó kiểm riêng từng phần; tên dễ trùng |
| Dựng trang | **10 bộ lọc `the_content`** ở 9 tệp (`doc-tiep`, `dong-dao`, `loai-bai`, `muc-kiem`, `nguoi-doc` ×2, `phan-tich` ×2, `site-cu`, `trich-dan`), mỗi bộ chèn thêm HTML vào thân bài | Thứ tự khối do số ưu tiên của bộ lọc quyết định; không thiết kế lại được bằng mẫu |
| Lưu nội dung | Lời, dị bản, chú thích, đáp án ghép thành khối Gutenberg trong `post_content` (`cadao_noi_dung_tu_kho`, `inc/kho.php`); khoảng 40 khoá meta `_cadao_*` rải rác | Xem G1, G2 ở tài liệu tổng hợp |
| Bảng riêng | 9 bảng (`kho`, `van_tay`, `cap_am`, `trung`, `xac_nhan`, `tim`, `luot_doc`, `gioi_thieu`, `su_kien`), mỗi bảng tự giữ số phiên bản trong một tùy chọn riêng, tự `dbDelta` ở `plugins_loaded` | Không có một chỗ xem "cơ sở dữ liệu đang ở phiên bản nào"; nâng cấp dữ liệu nằm rải rác |
| Việc chạy nền | WP-Cron mỗi 5 phút (`cadao_thu_thap`) + hằng ngày (`cadao_tao_bai_hang_ngay`); cron thật của máy chủ gọi tới | Chạy ổn; chưa có hàng đợi việc chung (chuyển dữ liệu, làm lại chỉ mục, sinh ảnh đều tự quản) |
| REST | 7 route rải ở 5 tệp (`do-luong`, `khoi-tho`, `luot-doc`, `tim`, `trang-trung` ×3); quyền kiểm đúng (E2E 26/10) | Chưa có không gian `v2`, chưa đủ cho trang quản trị mới |
| JavaScript | 5 tệp thuần, tổng 41 KB chưa nén (`ca-dao.js` 20 KB), không bước dựng | Nhẹ, đúng ngân sách. Giữ cho trang người đọc |
| Trang quản trị | 11 trang `add_submenu_page` viết bằng PHP + HTML trực tiếp | Mỗi trang một kiểu (G4) |
| Kiểm thử | `wordpress/tests/kiem-thu.php` (1.486 dòng, chạy trong WordPress Playground); `npm run test:anh` (PHP thật, ảnh chia sẻ); `quet:wordpress` (axe); E2E Playwright trên site thật | Phủ tốt nhưng một tệp khổng lồ; chưa có kiểm thử đơn vị không cần WordPress |

**Đáng giữ nguyên ý tưởng:**
- bộ trích tất định khớp từng ký tự (`trich.php`);
- chống trùng bằng dấu vân tay hai mức + cặp âm tiết (`trung.php`, 835 dòng);
- chỉ mục tìm bỏ dấu tự làm (`tim.php`);
- đo không cookie, không IP;
- "lịch sự với máy chủ nguồn";
- gộp bài giữ đường dẫn;
- khoá cửa quản trị.

### 2.2. Theme

- Theme con của Twenty Twenty-Five. Gồm `functions.php` 401 dòng, `style.css` 1.408 dòng, `theme.json` 290 dòng, 6 mẫu, 2 phần đầu/chân.
- **Theme đang làm nghiệp vụ**, qua các bộ lọc `the_content` (ưu tiên 8) và `render_block_core/verse`, `render_block_core/list`, `render_block_core/details`:
  - đọc lại thân bài để biết khối Thơ nào là dị bản, lời chính là gì, chú thích nào gắn với từ nào (`cadao_chuan_bi_bai`);
  - nhận lục bát (`cadao_la_luc_bat`);
  - tô chữ khác ở dị bản (`cadao_to_cho_khac`);
  - biến từ có chú thích thành nút chạm (`cadao_gan_tu_chu_thich`);
  - chia đoạn bài dài (`cadao_chia_doan`).
- Trạng thái của bài đang dựng giữ trong biến toàn cục `$GLOBALS['cadao_bai_dung']`.
- Theme gọi hàm của plugin qua `function_exists`.
- `single-cdtn.html` chỉ có nhãn thể loại + tiêu đề + `post-content` + bình luận. Trang chủ là mã ngắn `[cadao_trang_chu]`.

Hệ quả: đổi giao diện là đụng nghiệp vụ, đổi nghiệp vụ là đụng giao diện. Đổi theme thì mất chạm từ, tô dị bản. Không thể có mẫu riêng cho từng thể loại.

### 2.3. Đường đi của một trang bài hôm nay

```text
post_content (khối Gutenberg trộn lời + dị bản + chú thích + đáp án)
 → the_content @8   theme: parse_blocks → $GLOBALS (lời, dị bản, chú thích)
 → do_blocks @9     render_block_core/verse  theme: lục bát, tô dị bản, gắn nút chạm
                    render_block_core/list   theme: đánh số chú thích
                    render_block_core/details theme: đáp án
 → the_content @10+ plugin ×10: dòng mức kiểm, trích dẫn, đồng dao, nguồn đã ẩn,
                    góp ý, phân tích, Đọc tiếp, site cũ…
 → HTML
```

### 2.4. Triển khai

`wordpress/may-chu/tu-cap-nhat.sh` chạy mỗi 5 phút trên VPS. Nó so SHA của `main`, tải script cập nhật của đúng commit đó, rồi `cap-nhat-plugin.sh` chép plugin và theme từ repo. **Máy chủ không chạy `npm`.** Mọi mã JavaScript cần dựng (trang quản trị React) phải được dựng sẵn và commit vào repo. CI phải kiểm "bản dựng khớp mã nguồn" để không ai quên dựng lại.

---

## 3. Kiến trúc đích

### 3.1. Bốn tầng trong plugin

```text
wordpress/plugins/ca-dao/
├── ca-dao.php                 khai báo, tự nạp (spl_autoload), khởi động module
├── src/                       mã mới, namespace Phongdao\
│   ├── MienDuLieu/            (domain, không gọi WordPress)
│   │   ├── TacPham.php         ID, thể loại, tiêu đề, trạng thái
│   │   ├── Lop.php             loại lớp, thứ tự, nội dung, người làm, trạng thái
│   │   ├── HoSoNguon.php       loại, tên, giấy phép, cách ghi công, trạng thái xin phép
│   │   ├── TuyenTap.php
│   │   ├── CapChatLuong.php    tính L0–L3 từ lớp
│   │   └── ChinhSachHien.php   lớp nào, nguồn nào hiện ở đâu (đọc từ cài đặt)
│   ├── KhoLuu/                (repository: SQL, postmeta, term)
│   ├── DichVu/                (nghiệp vụ)
│   │   ├── NhapNguon/          bộ đọc Ca dao Mẹ, Wikisource…; ghi vào Kho
│   │   ├── TachLop/            Kho → lớp; post_content cũ → lớp (chuyển dữ liệu)
│   │   ├── ChongTrung/         giữ thuật toán trung.php
│   │   ├── ChiMucTim/          giữ tim.php, thêm lớp nghĩa, gõ sai một chữ
│   │   ├── XepViec/            sinh thẻ việc cho Hộp việc, xếp theo tác động
│   │   ├── DungTrang/          lớp + chính sách → dữ liệu cho khối
│   │   └── NhatKy/             ai đổi gì, lúc nào
│   ├── GiaoTiep/              (interfaces)
│   │   ├── Rest/               namespace cadao/v2
│   │   ├── Khoi/               khối động cadao/* (render.php trên máy chủ)
│   │   ├── Cli/                wp cadao …
│   │   └── QuanTri/            nạp ứng dụng React, menu
│   └── HaTang/                lịch chạy, hàng đợi, chuyển dữ liệu (migration), bộ nhớ đệm
├── inc/                       mã cũ — thu hẹp dần, mỗi tệp gỡ khi module mới thay xong
├── khoi/                      block.json + render.php + style.css + view.js của từng khối
├── quan-tri/                  mã nguồn React (src) → build/ (commit)
└── assets/                    JavaScript thuần cho người đọc (giữ)
```

**Quy ước:**
- Tầng miền dữ liệu không gọi hàm WordPress, nên kiểm được bằng PHP thuần, nhanh.
- Mỗi module mới có một lớp `Module` với `dang_ky()`, gọi ở `ca-dao.php`.
- Tên tiếng Việt không dấu như mã hiện tại.

### 3.2. Lược đồ dữ liệu mới

Giữ mọi bảng hiện có. Thêm:

| Bảng | Cột chính | Ghi chú |
|---|---|---|
| `wp_cadao_lop` | `id`, `tac_pham_id` (post ID), `loai` (`loi`, `di_ban`, `hieu_nhanh`, `nghia_den`, `nghia_bong`, `dung_khi`, `gan_nghia`, `giai_sach`, `chu_thich`, `boi_canh`, `cach_choi`, `cau_hoi`, `dap_an`, `goi_y`, `doan`, `media`), `thu_tu`, `noi_dung` (JSON theo loại), `nguoi_lam` (`may_chep`, `nguoi_viet`, `ai_nhap_da_duyet`, `doc_gia_gop`), `ho_so_nguon_id`, `vi_tri_nguon` (trang, URL, ngày truy cập), `trang_thai` (`nhap`, `cho`, `da_duyet`, `an`), `duyet_boi`, `duyet_luc`, `bam` (dấu băm nội dung), `tao_luc`, `sua_luc` | Khoá: (`tac_pham_id`, `loai`, `thu_tu`); chỉ mục theo `loai`, `trang_thai`. Lớp `loi` của tác phẩm luôn có đúng một mục `thu_tu = 0` |
| `wp_cadao_ho_so_nguon` | `id`, `khoa` (`cadao-me`, `wikisource-cau-hat-gop-1901`…), `loai` (`web`, `sach`, `nguoi_ke`, `doc_gia`, `wiki`), `ten`, `tac_gia`, `nam`, `giay_phep`, `ghi_cong` (mẫu câu), `xin_phep` (`chua`, `da_gui`, `dong_y`, `tu_choi`, `khong_can`), `chinh_sach_hien` (`tai_cho`, `tu_lieu`, `an`), `ghi_chu` | Nguồn tự động hiện có (tùy chọn `cadao_nguon…`) trỏ sang một hồ sơ |
| `wp_cadao_nhat_ky` | `id`, `doi_tuong` (`lop:<id>`, `tac_pham:<id>`, `nguon:<id>`), `hanh_dong`, `truoc`, `sau` (JSON), `nguoi`, `luc` | Lịch sử của lớp, trả lời "ai duyệt, lúc nào" |
| `wp_cadao_tuyen_tap` + `wp_cadao_tuyen_tap_muc` | tuyển tập: `id`, `slug`, `tieu_de`, `loi_dan`, `doi_tuong`, `trang_thai`, `sua_luc`; mục: `tuyen_tap_id`, `tac_pham_id`, `thu_tu`, `ghi_chu` | Hoặc một loại bài `cd_tuyen_tap` + bảng mục; chọn khi viết đặc tả V2.2 |
| `wp_cadao_hang_doi` | `id`, `loai_viec`, `tham_so`, `trang_thai`, `lan_thu`, `khoa_luc`, `loi` | Hàng đợi việc chạy nền chung (chuyển dữ liệu, làm lại chỉ mục, sinh ảnh, gọi Wikidata); cron thật lấy theo lô |

**Không làm bảng riêng cho "thẻ việc".** Hộp việc được *tính* từ các bảng có sẵn: bài `pending`, lớp `cho`, `wp_cadao_trung`, `wp_cadao_xac_nhan`, góp ý. Tránh hai nguồn sự thật.

**`post_content` thành ảnh chụp.** Sau mỗi lần lớp đổi, `DungTrang` sinh lại thân bài, gồm lời + dị bản + Hiểu nhanh dạng HTML đơn giản. Nhờ vậy RSS, tìm của WordPress, bản xem trước, plugin ngoài vẫn đọc được. Trình soạn có cấu trúc không cho sửa thân bài trực tiếp. Bài nào bị sửa thân bài bằng đường khác (WP-CLI, REST cũ) thì máy báo lệch và đưa vào hàng "Cần xem".

### 3.3. Chuyển dữ liệu

- Một khung chuyển dữ liệu duy nhất: tùy chọn `cadao_csdl_phien_ban` và danh sách bước có số thứ tự trong `src/HaTang/ChuyenDuLieu/`. Mỗi bước chạy lại được nhiều lần cho ra cùng kết quả (idempotent).
- Lệnh: `wp cadao chuyen-du-lieu --thu` (chỉ đọc, in báo cáo) và `wp cadao chuyen-du-lieu --lam`. Bước dài chạy theo lô qua `wp_cadao_hang_doi`.
- Bước V2.0 "thân bài → lớp":

| Đọc từ | Thành lớp |
|---|---|
| khối Thơ đầu tiên | `loi` (so từng ký tự với `cadao_loi_tu_html_tho` hiện có) |
| khối Thơ sau tiêu đề "Dị bản" + `_cadao_di_ban_them` | `di_ban` |
| danh sách `cadao-chu-thich cadao-chep` | `chu_thich`, `nguoi_lam = may_chep`, nguồn = hồ sơ của `_cadao_nguon_id` |
| `_cadao_chu_thich_them` | `chu_thich`, `nguoi_viet` |
| khối Chi tiết "Đáp án" + câu hỏi đậm | `dap_an`, `cau_hoi` |
| `post_excerpt` | `hieu_nhanh`; `_cadao_ho_tro_ai = 1` → `ai_nhap_da_duyet` |
| `nguon_ten/url`, `doi_chieu_*`, `_cadao_cc_*` | vị trí nguồn của lớp tương ứng |
| phần không nhận ra | giữ nguyên, ghi `can_xem` |

- **Điều kiện chạy trên site thật:** sao lưu hằng ngày đã bật và đã thử khôi phục (đang chờ người sáng lập).
- **Kiểm "trước = sau":** với mọi bài, lời dựng từ lớp phải khớp từng ký tự lời dựng từ thân bài cũ. Bộ kiểm chạy trên bản sao 171 bài thật (nội dung đã công khai) trong Playground.

### 3.4. Dựng trang bằng khối động

Plugin đăng ký các khối (mỗi khối có `block.json`, `render.php`, CSS riêng, `view.js` nếu cần):

| Khối | Dữ liệu | Thay cho |
|---|---|---|
| `cadao/loi` | lớp `loi` + chú thích (nút chạm) + nhận thể thơ | `render_block_core/verse` của theme |
| `cadao/muc-kiem` | lớp `loi` + vị trí nguồn | bộ lọc ở `muc-kiem.php` |
| `cadao/nghia` | `hieu_nhanh`, `nghia_den`, `nghia_bong`, `dung_khi`, `gan_nghia`, `giai_sach` | ô Tóm tắt + T-098 |
| `cadao/chu-thich` | lớp `chu_thich` | `render_block_core/list` của theme |
| `cadao/di-ban` | lớp `di_ban` + tô chữ khác | `cadao_to_cho_khac` |
| `cadao/boi-canh` | lớp `boi_canh` | T-102 |
| `cadao/thuc-hanh` | `cach_choi` / `cau_hoi` + `goi_y` + `dap_an` | `dong-dao.php`, `render_block_core/details` |
| `cadao/doan` | lớp `doan` (bài dài, truyện) + mục lục | `cadao_chia_doan` |
| `cadao/hanh-dong` | trích dẫn, chia sẻ, lưu, trình chiếu | `trich-dan.php` |
| `cadao/tu-lieu` | mọi lớp + `ChinhSachHien` | dòng "[N nguồn tham khảo đã ẩn]" + tab Thông tin tư liệu |
| `cadao/bien-soan` | người duyệt, bút danh (T-136) | — |
| `cadao/doc-tiep`, `cadao/gop` | giữ logic `doc-tiep.php`, `nguoi-doc.php` | bộ lọc `the_content` |
| `cadao/the-bai`, `cadao/danh-sach`, `cadao/tuyen-tap`, `cadao/loi-vao`, `cadao/cau-hom-nay` | thẻ, danh sách, trang chủ | mã ngắn `[cadao_trang_chu]`, `danh-sach.php` |

Nguyên tắc:
- Khối không có dữ liệu thì không in gì, kể cả tiêu đề.
- CSS của khối chỉ nạp khi khối có mặt, nên trang nhẹ hơn hiện nay (một `style.css` 1.408 dòng nạp mọi nơi).
- Mọi chỗ đọc HTML bằng biểu thức chính quy để biết bài có gì đều được thay bằng đọc lớp.

### 3.5. Theme 1.0

- **Theme khối độc lập**, không còn là theme con của Twenty Twenty-Five, để chủ động `theme.json` và không kéo CSS thừa. Giữ tên thư mục `ca-dao`, đổi phiên bản thành 1.0.0.
- `theme.json`:
  - token màu, phông hiện có (`docs/20` mục 1.1, 1.2) — đã đo tương phản;
  - bộ khoảng cách, cỡ chữ theo thang; bề rộng đọc 38em;
  - kiểu dáng cho khối `cadao/*`;
  - biến thể kiểu (sáng / tối; bảng màu PNG nếu người sáng lập muốn thử — Q8).
- **Mẫu theo thể loại.** WordPress không tự chọn mẫu bài theo phân loại, nên plugin thêm `single-cdtn-{thể loại}` vào thứ bậc mẫu qua bộ lọc `single_template_hierarchy`. Theme có: `single-cdtn-ca-dao`, `single-cdtn-tuc-ngu`, `single-cdtn-ve`, `single-cdtn-dong-dao`, `single-cdtn-cau-do`, (sau) `single-cdtn-truyen`, và `single-cdtn` làm mặc định. Mỗi mẫu chỉ xếp khối `cadao/*`.
- **Mẫu trang:** `front-page`, `archive-cdtn` (Tất cả), `taxonomy-the_loai`, `taxonomy-chu_de`, `taxonomy-vung`, `taxonomy-mo_tip`, `taxonomy-tu_kho` (mục từ), `page-tuyen-tap`, `search`, `404`.
- **Phần:** đầu trang, chân trang, thanh dưới điện thoại (thay `cadao_thanh_duoi` trong `functions.php`).
- **Mẫu dựng sẵn (pattern):** khối lối vào, khối tuyển tập, khối "Bài đang được bổ sung".
- `functions.php` còn dưới 50 dòng: nạp phông, đăng ký pattern, hỗ trợ theme. **Không còn nghiệp vụ.**
- Người sáng lập chỉnh được bố cục bằng Trình sửa site của WordPress (kéo khối trong mẫu) mà không đụng mã nghiệp vụ.

### 3.6. Trang quản trị React

**Màn hình:**

| Màn hình | Đường dẫn quản trị | Dữ liệu (REST `cadao/v2`) |
|---|---|---|
| Hộp việc | `admin.php?page=phongdao` | `GET /viec?loai=&trang=` (tính từ các bảng) |
| Duyệt nhanh | `…#/duyet` | `GET /viec/tiep`, `POST /viec/{id}/quyet` (`dang`, `sua`, `de_sau`, `bo`) |
| Soạn tác phẩm | `…#/tac-pham/{id}` | `GET/PUT /tac-pham/{id}`, `GET/POST/PUT/DELETE /tac-pham/{id}/lop`, `POST /trung/kiem` |
| Đối chiếu sách | trong Soạn | `GET /doi-chieu/goi-y?tac_pham=`, `POST /doi-chieu/xac-nhan` |
| Nguồn và hồ sơ nguồn | `…#/nguon` | `GET/PUT /ho-so-nguon`, `POST /nguon/{id}/lay-ngay` |
| Kế hoạch tuần, Kho đang thiếu | đầu Hộp việc | `GET /ke-hoach`, `GET /thong-ke/kho` |
| Cài đặt chính sách hiện | `…#/cai-dat` | `GET/PUT /cai-dat/chinh-sach-hien` |

**Kỹ thuật:**
- Dùng `@wordpress/scripts` để dựng; `@wordpress/components`, `@wordpress/data`, `@wordpress/api-fetch` (có sẵn trong WordPress, không tải thêm thư viện ngoài).
- Mã nguồn ở `quan-tri/src`, bản dựng ở `quan-tri/build` (commit).
- CI thêm bước `npm run dung:quan-tri && git diff --exit-code quan-tri/build`.
- Thiết kế cho điện thoại trước: Duyệt nhanh dùng được bằng một tay; trên máy tính có phím 1/2/3, J/K.
- Mọi route kiểm `current_user_can` theo quyền riêng (mục 3.8) + nonce REST của WordPress.
- Các trang PHP cũ ở lại trong menu con "Công cụ cũ" tới khi màn hình mới thay hết, rồi gỡ.

**Nếu không muốn thêm bước dựng:** làm cùng các màn hình bằng PHP + JavaScript thuần. Không cần Node trong quy trình, nhưng công sức gần gấp đôi cho Duyệt nhanh và trình soạn, và khó giữ trạng thái khi sửa nhiều lớp cùng lúc.

### 3.7. Tìm, bộ nhớ đệm, việc chạy nền

**Tìm:**
- Giữ bảng `wp_cadao_tim`, thêm cột cho lớp nghĩa.
- Thêm bảng âm tiết → bài để "Có phải bạn tìm …?" (T-132): so từng âm tiết sai một chữ cái hoặc sai dấu.
- Câu "tìm không ra" khớp câu trong Kho thì sinh thẻ việc "Người đọc tìm câu này, kho có sẵn".

**Bộ nhớ đệm:**
- Trang người đọc gửi `Cache-Control` cho khách chưa đăng nhập để Cloudflare giữ (L-13).
- Khối nặng (Đọc tiếp, trang chủ, danh sách tuyển tập) lưu đệm theo bài bằng transient, xoá khi lớp hoặc bài đổi.
- Không cần bộ nhớ đệm đối tượng cho tới khi đo thấy chậm.

**Việc chạy nền:**
- Giữ cron thật của máy chủ (đang chạy). Mọi việc dài đi qua `wp_cadao_hang_doi` với khoá, số lần thử và lỗi ghi lại, xem được ở Hộp việc → "Máy đang làm".
- Cân nhắc thư viện Action Scheduler (mã mở, nhiều site lớn dùng). Đổi lại phải mang thêm mã ngoài vào plugin. Đề xuất bảng hàng đợi nhỏ tự làm, vì việc nền của dự án ít loại.

### 3.8. Quyền và an toàn

**Quyền riêng:**

| Quyền | Cho phép |
|---|---|
| `cadao_soan` | soạn lớp, tạo nháp |
| `cadao_duyet` | duyệt lớp, đăng bài |
| `cadao_nguon` | sửa hồ sơ nguồn, bộ đọc, chính sách hiện |
| `cadao_cong_tac` | gửi đối chiếu, soát lời, không đăng |

Gán sẵn: Quản trị = tất cả; Biên tập = soạn + duyệt; Cộng tác = cộng tác. Hiện chỉ một tài khoản; vai trò có sẵn để mở khi có người thứ hai.

**Lằn ranh trong mã:**
- `trang_thai = da_duyet` chỉ đặt được qua route có `cadao_duyet` và do người bấm. Không có đường WP-CLI hay lịch chạy nào đặt được.
- Lớp `loi`, `di_ban` không nhận `nguoi_lam = ai_*`. Lớp nghĩa do AI soạn luôn bắt đầu ở `nhap`.
- Kiểm thử khẳng định các điều trên.

**An toàn:**
- Giữ mọi biện pháp của T-118, T-124.
- Thêm `Content-Security-Policy`, `Referrer-Policy` (L-12) khi theme mới không còn mã nội tuyến.

### 3.9. Dữ liệu mở

`wp cadao xuat --dinh-dang=json|csv` xuất các lớp **đã duyệt, của bài đã đăng, do dự án viết hoặc nguồn có giấy phép mở**. Không xuất phần chép từ nguồn chưa cho phép, không xuất kho thô. Kèm số phiên bản và ngày. Đây là nền cho T-066; nơi đăng (GitHub Release, Zenodo) để người sáng lập quyết sau.

### 3.10. Kiểm thử

| Tầng | Công cụ | Chạy ở |
|---|---|---|
| Miền dữ liệu (cấp chất lượng, chính sách hiện, tách lớp, tô dị bản, nhận lục bát) | PHP thuần, không WordPress, một tệp chạy nhỏ tự viết (không cần thêm PHPUnit) | máy + CI, vài giây |
| Plugin trong WordPress | giữ Playground (`npm run test:wordpress`), **tách** `kiem-thu.php` thành một tệp mỗi module | máy + CI |
| Chuyển dữ liệu | bộ "trước = sau" trên bản sao 171 bài | CI khi chạm `ChuyenDuLieu` |
| Khối, mẫu | `quet:wordpress` (axe) trên mọi mẫu thể loại; ảnh chụp điện thoại 390 px | CI |
| Trang quản trị | Playwright `e2e:quan-tri` thêm luồng Duyệt nhanh, Soạn | máy, trước gộp |
| Site thật | `kiem-site-that`, `e2e` | sau mỗi lần cập nhật |
| Ngân sách | đo dung lượng trang bài mẫu mỗi thể loại; lỗi khi > 200 KB | CI |

---

## 4. Thay dần: thứ tự PR

Mỗi bước để lại site chạy được. "Tắt mã cũ" nghĩa là gỡ bộ lọc / tệp `inc/` tương ứng khi khối mới đã hiện đúng trên mọi bài.

| Đợt | PR | Nội dung | Tắt mã cũ |
|---|---|---|---|
| **V2.0** | 1 | Tự nạp + `src/` + khung module; khung chuyển dữ liệu; bộ kiểm PHP thuần | — |
| | 2 | Bảng `lop`, `ho_so_nguon`, `nhat_ky`, `hang_doi`; hồ sơ nguồn cho Ca dao Mẹ, Wikipedia, Wiktionary, Commons, người đọc | — |
| | 3 | Tách lớp: kho → lớp (bài mới), thân bài → lớp (bài cũ) chạy `--thu`; báo cáo "Cần xem" | — |
| | 4 | Ghi song song: mọi đường ghi hiện có (tạo bài từ kho, gộp, Hiểu nhanh, hộp nguồn) ghi cả lớp; `DungTrang` sinh thân bài từ lớp | — |
| | 5 | Chính sách hiện thành cài đặt, mặc định = D-068 + D-078 + D-081; kiểm thử khẳng định HTML người đọc không đổi | — |
| **V2.1** | 6 | REST `cadao/v2` + quyền riêng | — |
| | 7 | Ứng dụng quản trị: khung, Hộp việc, Kế hoạch tuần, Kho đang thiếu | — |
| | 8 | Duyệt nhanh (nháp Hiểu nhanh, bài Chờ duyệt, cặp trùng, Wikidata, gợi ý) | các trang `goi-y`, `wikidata`, `trang-trung` vào "Công cụ cũ" |
| | 9 | Soạn tác phẩm có cấu trúc; T-098 (ô nghĩa tục ngữ); T-135 (cờ) | hộp Hiểu nhanh, hộp nguồn trong Gutenberg |
| | 10 | Đối chiếu sách (T-131) + bộ đọc Wikisource | — |
| **V2.2** | 11 | Khối `cadao/loi`, `muc-kiem`, `chu-thich`, `di-ban`, `nghia`, `hanh-dong` | `render_block_core/*` của theme, bộ lọc `muc-kiem`, `trich-dan` |
| | 12 | Theme 1.0: `theme.json`, mẫu theo thể loại, phần, thanh dưới | theme con TT25, `functions.php` cũ |
| | 13 | Khối `tu-lieu`, `bien-soan` (T-136), `doc-tiep`, `gop`, `thuc-hanh` (T-096), `boi-canh` (T-102), `doan` | các bộ lọc `the_content` còn lại |
| | 14 | Trang chủ, danh sách, tuyển tập (T-137), IA mới | mã ngắn `[cadao_trang_chu]` |
| **V2.3** | 15+ | Thể loại mới qua cổng, giọng đọc (T-105), bộ câu SGK (T-133), xuất dữ liệu mở (T-066) | phần `inc/` còn lại |

Ước lượng: V2.0 khoảng 5 PR, V2.1 khoảng 5, V2.2 khoảng 4 PR lớn. Người sáng lập chỉ cần xem bản xem trước ở V2.1 (dùng thử Duyệt nhanh) và V2.2 (từng mẫu thể loại).

---

## 5. Rủi ro kỹ thuật

| Rủi ro | Cách giảm |
|---|---|
| Ghi song song lệch giữa thân bài và lớp | Giai đoạn ghi song song có kiểm "lệch" mỗi đêm; lệch thì vào "Cần xem", không tự sửa |
| Bản dựng React quên cập nhật | Bước CI so `build/` với mã nguồn |
| Khối động chậm hơn bộ lọc | Đệm theo bài; đo TTFB trước/sau (hiện 0,40–0,67 s) |
| Theme mới vỡ trang khi chưa đủ khối | Mẫu cũ giữ tới khi `quet:wordpress` và ảnh chụp đạt trên mọi thể loại; đổi theme bằng một PR có đường quay lại (`docs/huong-dan/quay-lai-ban-truoc.md`) |
| Playground không giống MySQL thật (SQLite) | Câu SQL mới tránh cú pháp chỉ MySQL có (bài học cũ: `INSERT…SELECT…ON DUPLICATE`); chuyển dữ liệu thử thêm trên bản sao cơ sở dữ liệu thật khi người sáng lập đồng ý |
| Phiên bản quên tăng | Giữ quy tắc tăng `CADAO_PHIEN_BAN` và `Version:`; theme lên 1.0.0 ở PR 12 |

---

## 6. Câu hỏi kỹ thuật cần quyết

| # | Câu hỏi | Gợi ý |
|---|---|---|
| **B1** | Plugin: cải tiến tại chỗ hay viết plugin mới song song? | Cải tiến tại chỗ |
| **B2** | Theme: dựng mới theme khối độc lập 1.0 hay sửa dần theme con? | Dựng mới, giữ tên `ca-dao` |
| **B3** | Trang quản trị: React (có bước dựng, commit bản dựng) hay PHP thuần? | React (= Q4 tài liệu tổng hợp) |
| **B4** | Tuyển tập: loại bài riêng + bảng mục, hay bảng riêng hoàn toàn? | Loại bài riêng (có đường dẫn, SEO, sửa tiêu đề bằng WordPress) + bảng mục |
| **B5** | Hàng đợi: tự làm hay Action Scheduler? | Tự làm |
| **B6** | Cho phép chạy chuyển dữ liệu thử trên **bản sao** cơ sở dữ liệu thật (chỉ đọc trên máy chủ, chạy ở máy agent)? | Có, sau khi sao lưu đã bật |
