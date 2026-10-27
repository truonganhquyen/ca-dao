# 26 · Kiến trúc kỹ thuật V2

| | |
|---|---|
| Phiên bản | 1.0 · 27/10/2026 |
| Trạng thái | **Đã duyệt (D-083, D-084, D-085, D-086).** Đổi kiến trúc phải ghi D-xxx |
| Đọc khi | Mọi task V2 (T-141 trở đi trong `docs/22`). Task chạm giao diện đọc thêm `27`; task V2.0 đọc thêm `28` |
| Căn cứ | `docs/nghien-cuu/v2-backend-plugin-theme-2026-10-27.md` (hiện trạng mã đo trên plugin 0.33.2, theme 0.19.1) |
| Không thay | `docs/24-wordpress.md` vẫn đúng cho mã MVP đang chạy (thu thập, máy chủ, tự cập nhật). Khi một module V2 thay phần nào, agent sửa `docs/24` ở mục đó trong cùng PR |

## 1. Quyết định gốc

| Thành phần | Chốt | Quyết định |
|---|---|---|
| Plugin `ca-dao` | Cải tiến tại chỗ, thay dần từng phần. Giữ tên plugin, loại bài `cdtn`, đường dẫn, 9 bảng đang có, thuật toán chống trùng (`trung.php`), chỉ mục tìm (`tim.php`), bộ trích tất định (`trich.php`) | D-085 (1) |
| Dữ liệu | Tác phẩm nhiều lớp trong bảng riêng; `post_content` là ảnh chụp sinh lại | D-083 |
| Trang người đọc | Khối động `cadao/*` render trên máy chủ + JavaScript thuần nhỏ | D-085 (2) |
| Theme | Theme khối độc lập 1.0, tên `ca-dao`, không nghiệp vụ | D-085 (3) |
| Trang quản trị | React của WordPress, bản dựng commit vào repo | D-085 (4), D-086 |
| Việc nền | Hàng đợi tự làm `wp_cadao_hang_doi` + cron thật của máy chủ | D-085 (6) |
| Tuyển tập | Loại bài `cd_tuyen_tap` + bảng mục | D-085 (7) |
| Hiện nguồn | Cài đặt theo loại nguồn, mặc định = D-068 + D-078 + D-081 | D-084 |

## 2. Hiện trạng cần thay (tóm tắt)

| Mặt | Hiện trạng | Vấn đề |
|---|---|---|
| Tổ chức mã | `ca-dao.php` nạp tuần tự 39 tệp `inc/*.php`, hàm toàn cục `cadao_*` | Thứ tự nạp ngầm; khó kiểm riêng |
| Dựng trang | 10 bộ lọc `the_content` ở 8 tệp plugin + 2 ở theme; theme lọc `render_block_core/verse`, `list`, `details`; trạng thái bài trong `$GLOBALS['cadao_bai_dung']` | Đổi giao diện là đụng nghiệp vụ; không có mẫu theo thể loại |
| Lưu nội dung | Lời, dị bản, chú thích, đáp án ghép thành khối Gutenberg trong `post_content` (`cadao_noi_dung_tu_kho`); ~40 khoá `_cadao_*` | Máy phải đọc lại HTML; sửa tay một chữ là mất đường cập nhật từ kho |
| Nguồn | 6+ chỗ lưu khác nhau (hộp Nguồn, `doi_chieu_*`, `_cadao_url_goc`, lớp CSS `cadao-chep`, term meta mục từ, `_cadao_cc_*`, `_cadao_ho_tro_ai`) | Không có mô hình chung; đổi chính sách là sửa mã |
| Bảng riêng | 9 bảng, mỗi bảng tự giữ số phiên bản, tự `dbDelta` | Không một chỗ biết cơ sở dữ liệu ở phiên bản nào |
| Quản trị | 11 trang `add_submenu_page` PHP + 2 trang phân loại | Quyết việc ở 13 nơi |
| Kiểm thử | `wordpress/tests/kiem-thu.php` 1.486 dòng trong Playground | Một tệp khổng lồ; không có kiểm thử đơn vị không cần WordPress |

## 3. Mô hình dữ liệu

### 3.1. Tác phẩm và lớp

```text
Tác phẩm = bài cdtn (giữ ID, đường dẫn, the_loai, chu_de, vung, mo_tip, tu_kho, tro_choi, tinh_huong)
└── Lớp (wp_cadao_lop), mỗi mục:
      loai · thu_tu · noi_dung (JSON theo loại)
      nguoi_lam · ho_so_nguon_id · vi_tri_nguon · trang_thai · duyet_boi · duyet_luc · bam
```

| `loai` | Nội dung (JSON) | Thể loại dùng | Người làm được phép |
|---|---|---|---|
| `loi` | `{dong: [..]}` — đúng một mục `thu_tu = 0` mỗi tác phẩm | mọi | `may_chep`, `nguoi_viet` |
| `di_ban` | `{dong: [..], noi?, nguoi_ke?, nam?}` | mọi | `may_chep`, `nguoi_viet`, `doc_gia_gop` (sau duyệt) |
| `hieu_nhanh` | `{van_ban}` (1–2 câu) | mọi | `nguoi_viet`, `ai_nhap_da_duyet` |
| `nghia_den`, `nghia_bong`, `dung_khi` | `{van_ban}` | tục ngữ – thành ngữ | `nguoi_viet`, `ai_nhap_da_duyet`, `may_chep` (Wiktionary, CC BY-SA) |
| `gan_nghia` | `{tac_pham_id, quan_he: gan|trai}` | tục ngữ – thành ngữ | `nguoi_viet` |
| `giai_sach` | `{van_ban}` (giữ chính tả xưa) | mọi | `may_chep` (sách hết bản quyền, sau duyệt) |
| `chu_thich` | `{tu, loi_giai, vi_tri_trong_loi?}` | mọi | `may_chep`, `nguoi_viet` |
| `boi_canh` | `{su_kien?, nam?, noi?, nhan_vat?: [wikidata_id]}` | vè, (truyện) | `nguoi_viet` |
| `cach_choi` | `{tro_choi_id}` (trỏ phân loại `tro_choi`) | đồng dao | — |
| `cau_hoi`, `dap_an`, `goi_y` | `{van_ban}` | câu đố | `may_chep`, `nguoi_viet` (gợi ý: cả `ai_nhap_da_duyet`) |
| `doan` | `{tieu_de?, tu_dong, den_dong}` | vè dài, (truyện) | `nguoi_viet` |
| `media` | `{attachment_id, loai: anh|giong_may|giong_nguoi}` | mọi | `nguoi_viet` |

**Trạng thái:** `nhap` → `cho` → `da_duyet` | `an`. Chỉ `da_duyet` lên trang (trừ lớp do `may_chep` từ nguồn đã bật "Chép chú thích", lên trang khi bài được đăng — giữ hành vi MVP, D-055, D-058).

**Ràng buộc trong mã (kiểm thử khẳng định):**
- `loi`, `di_ban` không bao giờ nhận `nguoi_lam` bắt đầu bằng `ai_`.
- Lớp do AI soạn luôn tạo ở `nhap`; chỉ chuyển `da_duyet` qua route có `cadao_duyet` do người bấm; không có đường WP-CLI hay lịch chạy nào đặt `da_duyet`.
- `vi_tri_nguon` không được điền mặc định; trống là "chưa rõ".
- Sửa nội dung lớp `loi` của bài đã đăng chỉ qua trình soạn (người bấm), luôn ghi nhật ký.

### 3.2. Bảng mới

Giữ mọi bảng hiện có (`kho`, `van_tay`, `cap_am`, `trung`, `xac_nhan`, `tim`, `luot_doc`, `gioi_thieu`, `su_kien`). Thêm:

| Bảng | Cột chính | Chỉ mục, ràng buộc |
|---|---|---|
| `wp_cadao_lop` | `id`, `tac_pham_id`, `loai`, `thu_tu`, `noi_dung` (LONGTEXT JSON), `nguoi_lam`, `ho_so_nguon_id`, `vi_tri_nguon` (JSON: trang, url, ngay_truy_cap), `trang_thai`, `duyet_boi`, `duyet_luc`, `bam`, `tao_luc`, `sua_luc` | duy nhất (`tac_pham_id`, `loai`, `thu_tu`); chỉ mục (`loai`, `trang_thai`) |
| `wp_cadao_ho_so_nguon` | `id`, `khoa`, `loai` (`web`, `sach`, `nguoi_ke`, `doc_gia`, `wiki`), `ten`, `tac_gia`, `nam`, `giay_phep`, `ghi_cong` (mẫu câu), `xin_phep` (`chua`, `da_gui`, `dong_y`, `tu_choi`, `khong_can`), `chinh_sach_hien` (`tai_cho`, `tu_lieu`, `an`), `ghi_chu` | duy nhất `khoa` |
| `wp_cadao_nhat_ky` | `id`, `doi_tuong` (`lop:<id>`, `tac_pham:<id>`, `nguon:<id>`), `hanh_dong`, `truoc`, `sau` (JSON), `nguoi`, `luc` | chỉ mục (`doi_tuong`, `luc`) |
| `wp_cadao_hang_doi` | `id`, `loai_viec`, `tham_so`, `trang_thai`, `lan_thu`, `khoa_luc`, `loi` | chỉ mục (`trang_thai`, `loai_viec`) |
| `wp_cadao_tuyen_tap_muc` | `tuyen_tap_id` (post ID của `cd_tuyen_tap`), `tac_pham_id`, `thu_tu`, `nhom?`, `ghi_chu?` | duy nhất (`tuyen_tap_id`, `tac_pham_id`) |
| `wp_cadao_am_tiet` | `am_tiet` (bỏ dấu), `tac_pham_id` | cho "Có phải bạn tìm …?" nếu T-132 trên MVP chưa đủ |

**Hồ sơ nguồn ban đầu** (`khoa`): `cadao-me`, `wikipedia-vi`, `wiktionary-vi`, `commons`, `wikidata`, `wikisource-cau-hat-gop-1901`, `wikisource-tuc-ngu-co-ngu-gia-ngon-1897`, `doc-gia`, `nguoi-bien-soan`. Nguồn tự động hiện có (tuỳ chọn `cadao_nguon…`) trỏ sang hồ sơ. Trạng thái `xin_phep` của `cadao-me` = `chua` tới khi người sáng lập ghi khác (H3) — agent không tự đổi.

**Không có bảng "thẻ việc".** Hộp việc tính từ bài `pending`, lớp `cho`, `wp_cadao_trung`, `wp_cadao_xac_nhan`, góp ý, gợi ý.

**Vùng và địa danh (D-083):** `vung` (phân loại) giữ nghĩa **vùng lưu truyền** (có căn cứ, người duyệt). Địa danh nhắc trong lời là quan hệ lớp `chu_thich` → mục từ `tu_kho` có Wikidata đã xác nhận; bản đồ dùng loại quan hệ này và ghi rõ "được nhắc đến".

### 3.3. Cấp chất lượng (máy tính, nội bộ)

`MienDuLieu\CapChatLuong::tinh(TacPham)`:

| Cấp | Điều kiện |
|---|---|
| L0 | có lớp `loi` |
| L1 | L0 + có `the_loai` + dòng mức kiểm tính được |
| L2 | L1 + (`hieu_nhanh` đã duyệt **hoặc** bộ nghĩa đã duyệt **hoặc** ≥ 1 bài phân tích đã duyệt) |
| L3 | L2 + (lời đã đối chiếu sách **hoặc** `boi_canh` **hoặc** `media` có giấy phép) |

`cadao_co_gia_tri_rieng()` (quy tắc lập chỉ mục, D-043, D-062) được viết lại thành "cấp ≥ L2" và **phải cho cùng kết quả** trên mọi bài hiện có (kiểm thử).

### 3.4. Thân bài là ảnh chụp

`DichVu\DungTrang` sinh lại `post_content` sau mỗi lần lớp đổi: lời + dị bản + Hiểu nhanh dạng HTML đơn giản, để RSS, tìm của WordPress, bản xem trước và plugin ngoài vẫn đọc được. Bài bị sửa thân bài bằng đường khác (Gutenberg dự phòng, WP-CLI, REST cũ) → máy so dấu băm, đưa vào hàng "Cần xem", **không tự sửa**.

## 4. Cấu trúc plugin

```text
wordpress/plugins/ca-dao/
├── ca-dao.php            khai báo, spl_autoload cho Phongdao\, khởi động module, CADAO_PHIEN_BAN
├── src/                  mã mới, namespace Phongdao\
│   ├── MienDuLieu/       TacPham, Lop, HoSoNguon, TuyenTap, CapChatLuong, ChinhSachHien — không gọi WordPress
│   ├── KhoLuu/           đọc/ghi bảng, postmeta, term
│   ├── DichVu/           NhapNguon, TachLop, ChongTrung, ChiMucTim, XepViec, DungTrang, NhatKy
│   ├── GiaoTiep/         Rest (cadao/v2), Khoi (cadao/*), Cli (wp cadao …), QuanTri (nạp ứng dụng React)
│   └── HaTang/           CoTinhNang, ChuyenDuLieu, HangDoi, LichChay, BoNhoDem
├── inc/                  mã MVP — thu hẹp dần; mỗi tệp gỡ khi module mới thay xong
├── khoi/<ten>/           block.json, render.php, style.css, view.js (nếu cần)
├── quan-tri/src/         mã nguồn React
├── quan-tri/build/       bản dựng (commit)
└── assets/               JavaScript thuần cho người đọc (giữ)
```

Quy ước: tên tiếng Việt không dấu như mã hiện tại; mỗi module một lớp `Module` có `dang_ky()`; tầng `MienDuLieu` kiểm bằng PHP thuần.

### 4.1. Cờ tính năng

`HaTang\CoTinhNang`: tuỳ chọn `cadao_v2` (mảng), mặc định mọi cờ **tắt trên site thật**, bật trong Playground / bản xem trước. Cờ: `lop_doc` (trang đọc dùng lớp), `ban_bien_tap`, `trinh_soan`, `theme_v2`, `tuyen_tap`. Người sáng lập bật trong Cài đặt của Bàn biên tập (hoặc agent bật bằng WP-CLI khi được đồng ý cho lần đó). Mã MVP chạy khi cờ tắt; mỗi PR V2 phải giữ site đúng như trước khi cờ tắt.

### 4.2. Chuyển dữ liệu

- Tuỳ chọn `cadao_csdl_phien_ban`; các bước đánh số trong `src/HaTang/ChuyenDuLieu/`; mỗi bước idempotent.
- Lệnh: `wp cadao chuyen-du-lieu --thu` (chỉ đọc, in báo cáo) và `--lam` (theo lô qua hàng đợi).
- 9 bảng cũ dần chuyển về khung này (bước "nhận bảng cũ" không đổi cấu trúc).
- Bước V2.0 "thân bài → lớp" và tiêu chí "trước = sau": `docs/28` mục 3.
- **Chạy `--lam` trên site thật:** chỉ sau T-146 (sao lưu + thử khôi phục) và khi người sáng lập đồng ý cho lần đó (AGENTS.md, KHÔNG BAO GIỜ 15).

### 4.3. Khối động

| Khối | Dữ liệu | Thay cho | Đợt |
|---|---|---|---|
| `cadao/loi` | `loi` + `chu_thich` (nút chạm) + thể thơ | `render_block_core/verse` của theme | V2.2 |
| `cadao/muc-kiem` | `loi` + vị trí nguồn | `inc/muc-kiem.php` | V2.2 |
| `cadao/nghia` | `hieu_nhanh`, `nghia_*`, `dung_khi`, `gan_nghia`, `giai_sach` | ô Tóm tắt, T-098 | V2.2 |
| `cadao/chu-thich` | `chu_thich` | `render_block_core/list` | V2.2 |
| `cadao/di-ban` | `di_ban` + tô chữ khác | `cadao_to_cho_khac` | V2.2 |
| `cadao/hanh-dong` | trích dẫn, chia sẻ, lưu, trình chiếu | `inc/trich-dan.php` | V2.2 |
| `cadao/tu-lieu` | mọi lớp + `ChinhSachHien` | dòng "[N nguồn tham khảo đã ẩn]" | V2.2 |
| `cadao/bien-soan` | bút danh, người duyệt | T-136 | V2.2 |
| `cadao/thuc-hanh` | `cach_choi` / `cau_hoi` + `goi_y` + `dap_an` | `inc/dong-dao.php`, `render_block_core/details` | V2.2 |
| `cadao/boi-canh`, `cadao/doan` | `boi_canh`, `doan` | T-102, `cadao_chia_doan` | V2.2 |
| `cadao/doc-tiep`, `cadao/gop` | logic `doc-tiep.php`, `nguoi-doc.php` | bộ lọc `the_content` | V2.2 |
| `cadao/the-bai`, `danh-sach`, `tuyen-tap`, `loi-vao`, `cau-hom-nay` | thẻ, danh sách, trang chủ | `[cadao_trang_chu]`, `danh-sach.php` | V2.2 |

Nguyên tắc: khối không có dữ liệu thì không in gì (kể cả tiêu đề); CSS của khối chỉ nạp khi khối có mặt; không đọc HTML bằng biểu thức chính quy để biết bài có gì.

## 5. Theme 1.0

- Theme khối độc lập; thư mục `ca-dao`; `Version: 1.0.0` ở T-154.
- `theme.json`: token màu, phông của `docs/20` mục 1.1–1.2; thang cỡ chữ 7 bậc, khoảng cách 8 bậc; bề rộng đọc 38em; kiểu cho khối `cadao/*`; biến thể kiểu sáng / tối và một biến thể "bảng màu đề xuất" để so (D-087 (5)).
- Plugin thêm `single-cdtn-{slug thể loại}` vào thứ bậc mẫu (`single_template_hierarchy`). Mẫu: `single-cdtn-ca-dao`, `-tuc-ngu`, `-ve`, `-dong-dao`, `-cau-do`, mặc định `single-cdtn`; sau: `-truyen`.
- Mẫu trang: `front-page`, `archive-cdtn`, `taxonomy-the_loai`, `taxonomy-chu_de`, `taxonomy-vung`, `taxonomy-mo_tip`, `taxonomy-tu_kho`, `taxonomy-tro_choi`, `single-cd_tuyen_tap`, `archive-cd_tuyen_tap`, `search`, `404`.
- Phần: đầu trang, chân trang, thanh dưới điện thoại.
- `functions.php` dưới 50 dòng, không nghiệp vụ. Người sáng lập chỉnh bố cục bằng Trình sửa site mà không đụng nghiệp vụ.
- Đổi theme bằng một PR có đường quay lại (`docs/huong-dan/quay-lai-ban-truoc.md`); theme cũ giữ trong repo tới khi `quet:wordpress` và ảnh chụp đạt trên mọi thể loại.

## 6. Bàn biên tập (React)

| Màn hình | Đường dẫn | REST `cadao/v2` |
|---|---|---|
| Hộp việc + Tuần này + Kho đang thiếu | `admin.php?page=phongdao` | `GET /viec?loai=&trang=`, `GET /ke-hoach`, `GET /thong-ke/kho` |
| Duyệt nhanh | `…#/duyet` | `GET /viec/tiep`, `POST /viec/{id}/quyet` (`dang`, `sua`, `de_sau`, `bo`) |
| Soạn tác phẩm | `…#/tac-pham/{id}` | `GET/PUT /tac-pham/{id}`, `GET/POST/PUT/DELETE /tac-pham/{id}/lop`, `POST /trung/kiem` |
| Đối chiếu sách | trong Soạn | `GET /doi-chieu/goi-y?tac_pham=`, `POST /doi-chieu/xac-nhan` |
| Nguồn, hồ sơ nguồn | `…#/nguon` | `GET/PUT /ho-so-nguon`, `POST /nguon/{id}/lay-ngay` |
| Cài đặt (chính sách hiện, cờ tính năng, bút danh) | `…#/cai-dat` | `GET/PUT /cai-dat` |

Kỹ thuật:
- `@wordpress/scripts` dựng; chỉ dùng gói WordPress đã có (`@wordpress/components`, `@wordpress/data`, `@wordpress/api-fetch`, `@wordpress/element`); không thư viện ngoài.
- `npm run dung:quan-tri` dựng vào `quan-tri/build/`; CI chạy `npm run dung:quan-tri && git diff --exit-code wordpress/plugins/ca-dao/quan-tri/build` (thêm vào job wordpress; mở rộng `npm run kiem-tra` nếu chạy được nhanh).
- Điện thoại trước: Duyệt nhanh dùng một tay, nút ≥ 48 px; máy tính phím 1/2/3, J/K.
- Mọi route: `permission_callback` kiểm quyền riêng + nonce REST.
- Trang PHP cũ ở menu "Công cụ cũ" tới khi màn hình mới thay hết, rồi gỡ.
- Gutenberg vẫn mở được cho bài `cdtn` (đường dự phòng, D-086) tới khi người sáng lập xác nhận trình soạn mới đủ dùng.

### 6.1. Quyền

| Quyền | Cho phép | Gán sẵn |
|---|---|---|
| `cadao_soan` | soạn lớp, tạo nháp | Quản trị, Biên tập |
| `cadao_duyet` | duyệt lớp, đăng bài | Quản trị, Biên tập |
| `cadao_nguon` | sửa hồ sơ nguồn, bộ đọc, chính sách hiện | Quản trị |
| `cadao_cong_tac` | gửi đối chiếu, soát lời, không đăng | Cộng tác (vai mới) |

## 7. Tìm, bộ nhớ đệm, việc nền

- **Tìm:** giữ `wp_cadao_tim`; thêm cột cho lớp nghĩa; "Có phải bạn tìm …?" (T-132 trên MVP, rồi chuyển sang `ChiMucTim`); câu "tìm không ra" khớp câu trong Kho → thẻ việc "Người đọc tìm câu này, kho có sẵn" (không hiện lời kho thô cho người đọc).
- **Bộ nhớ đệm:** `Cache-Control` cho khách chưa đăng nhập để Cloudflare giữ (L-13) — chỉ bật khi kịch bản cập nhật xoá đệm sau mỗi lần thay bản; khối nặng đệm bằng transient theo bài, xoá khi lớp hoặc bài đổi.
- **Việc nền:** cron thật của máy chủ lấy việc từ `wp_cadao_hang_doi` theo lô, có khoá, số lần thử, lỗi ghi lại; Hộp việc → "Máy đang làm".

## 8. An toàn, quyền riêng tư, đo lường

- Giữ mọi biện pháp T-118, T-124 (XML-RPC tắt, không lộ tên đăng nhập, tạm khoá, chặn lặp sự kiện, chỉ tin IP Cloudflare đúng lúc).
- Thêm `Content-Security-Policy`, `Referrer-Policy` (L-12) khi theme 1.0 không còn mã nội tuyến.
- Gỡ chỗ còn lộ số phiên bản WordPress (tệp emoji `ver=` trên trang chủ, đo 27/10).
- Đo lường: 4 sự kiện hiện có + **`mong_co_cau`** (D-087 (8)): khoá = câu tìm đã chuẩn hoá khớp một câu trong Kho; không cookie, không IP, một lần mỗi máy mỗi ngày mỗi câu; chuỗi trông như email / số điện thoại bị bỏ.
- Không thêm cookie, theo dõi, dịch vụ bên thứ ba khi người đọc chưa mở.

## 9. Dữ liệu mở

`wp cadao xuat --dinh-dang=json|csv`: chỉ lớp **đã duyệt**, của bài **đã đăng**, do dự án viết (CC BY 4.0) hoặc nguồn có giấy phép mở (giữ giấy phép từng phần); không xuất phần chép từ nguồn chưa cho phép (`xin_phep ≠ dong_y`), không xuất kho thô. Kèm số phiên bản và ngày. Nơi đăng (GitHub Release, Zenodo) người sáng lập quyết sau (T-066, H3).

## 10. Kiểm thử và ngân sách

| Tầng | Công cụ | Chạy ở |
|---|---|---|
| Miền dữ liệu (cấp chất lượng, chính sách hiện, tách lớp, tô dị bản, nhận lục bát, ràng buộc lằn ranh) | PHP thuần, tệp chạy nhỏ tự viết `wordpress/tests/don-vi/` | `npm run test:wordpress` + CI |
| Plugin trong WordPress | Playground; tách `kiem-thu.php` thành một tệp mỗi module (dần, theo module được thay) | `npm run test:wordpress` + CI |
| Chuyển dữ liệu | "trước = sau" trên bản sao nội dung đã công khai | CI khi chạm `ChuyenDuLieu` |
| Khối, mẫu | `quet:wordpress` (axe) trên mọi mẫu thể loại; ảnh chụp 390 px | CI |
| Ngân sách | trang bài mẫu mỗi thể loại ≤ 200 KB, JavaScript trang bài ≤ 21 KB, tuyển tập 30 câu ≤ 300 KB | CI |
| Liên kết ra ngoài | quét HTML các mẫu, lỗi khi có tên miền ngoài danh sách cho phép | CI (T-156) |
| Trang quản trị | Playwright `e2e:quan-tri` thêm luồng Duyệt nhanh, Soạn | trước gộp |
| Site thật | `kiem-site-that`, `e2e` | sau mỗi lần cập nhật |

Playground dùng SQLite: câu SQL mới tránh cú pháp chỉ MySQL có (bài học `INSERT…SELECT…ON DUPLICATE`).

Mỗi PR đổi plugin hoặc theme tăng `CADAO_PHIEN_BAN`, `Version:` (AGENTS.md).

## 11. Thứ tự PR

| Đợt | Task | Nội dung | Tắt mã cũ |
|---|---|---|---|
| V2.0 | T-141 | Tự nạp, `src/`, khung module, cờ tính năng, khung chuyển dữ liệu, bộ kiểm PHP thuần | — |
| | T-142 | Bảng `lop`, `ho_so_nguon`, `nhat_ky`, `hang_doi`; hồ sơ nguồn ban đầu | — |
| | T-143 | Tách lớp: kho → lớp (bài mới), thân bài → lớp (`--thu`); "Cần xem"; "trước = sau" | — |
| | T-144 | Ghi song song mọi đường ghi; `DungTrang` sinh thân bài; kiểm lệch mỗi đêm | — |
| | T-145 | Chính sách hiện thành cài đặt; HTML người đọc không đổi | — |
| | T-147 | Chạy chuyển dữ liệu trên site thật (sau T-146) | — |
| V2.1 | T-148 | REST `cadao/v2`, quyền riêng | — |
| | T-149 | Bước dựng React; Hộp việc, Tuần này, Kho đang thiếu | — |
| | T-150 | Duyệt nhanh | `goi-y`, `wikidata`, `trang-trung` vào "Công cụ cũ" |
| | T-151 | Trình soạn có cấu trúc; nghĩa tục ngữ; cờ nghi | hộp Hiểu nhanh, hộp nguồn trong Gutenberg |
| | T-152 | Đối chiếu sách + bộ đọc Wikisource | — |
| V2.2 | T-153 | Khối lời, mức kiểm, chú thích, dị bản, nghĩa, hành động | `render_block_core/*` của theme, `muc-kiem`, `trich-dan` |
| | T-154 | Theme 1.0 | theme con TT25 |
| | T-155 | Khối tư liệu, biên soạn, đọc tiếp, góp, thực hành, bối cảnh, đoạn | bộ lọc `the_content` còn lại |
| | T-156 | Trang chủ, IA, tuyển tập, vai, `mong_co_cau`, kiểm liên kết ngoài | `[cadao_trang_chu]` |
| V2.3 | T-157..T-161 | Cổng thể loại, truyện, Cho lớp học, giọng đọc, dữ liệu mở | `inc/` còn lại |

## 12. Rủi ro kỹ thuật

| Rủi ro | Cách giảm |
|---|---|
| Ghi song song lệch giữa thân bài và lớp | Kiểm lệch mỗi đêm; lệch vào "Cần xem", không tự sửa |
| Bản dựng React quên cập nhật | CI so bản dựng với mã nguồn |
| Khối động chậm hơn bộ lọc | Đệm theo bài; đo TTFB trước/sau (hiện 0,40–0,67 s) |
| Theme mới vỡ trang | Cờ `theme_v2`; mẫu cũ giữ tới khi quét và ảnh chụp đạt; đường quay lại |
| Playground khác MySQL | Tránh SQL chỉ MySQL; thử chuyển dữ liệu trên bản sao cơ sở dữ liệu thật khi được đồng ý |
| Máy chủ tự cập nhật đưa mã V2 lên giữa chừng | Mọi tính năng V2 sau cờ tắt; mỗi PR giữ site như cũ khi cờ tắt |
