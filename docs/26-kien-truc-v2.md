# 26 · Kỹ thuật V2

| | |
|---|---|
| Phiên bản | 2.0 · 27/10/2026 (xây mới: D-083..D-086, D-088, D-089) |
| Trạng thái | **Đã duyệt.** Đổi điều đã chốt ở đây phải ghi D-xxx; chi tiết kỹ thuật trong phạm vi do agent tự chọn và ghi trong PR |
| Đọc khi | Mọi task V2 (`docs/22`, nhóm V2) |
| Đọc kèm | `27` (sản phẩm cho người đọc), `28` (luồng nội dung, Bàn biên tập), `29` (chuyển dữ liệu, chuyển đổi), AGENTS.md |
| Căn cứ | `docs/nghien-cuu/v2-backend-plugin-theme-2026-10-27.md` (đo trên plugin 0.33.2, theme 0.19.1) |

## 1. Quyết định gốc

| Mặt | Chốt |
|---|---|
| Nền tảng | WordPress làm khung; viết mới toàn bộ plugin, theme, trang quản trị, mô hình dữ liệu (D-083) |
| Cách làm | Xây song song trong `wordpress/v2/`, chuyển một lần, mở ngay sau khi chuyển; MVP đóng băng tính năng |
| Tài sản giữ | Thuật toán đã kiểm chứng được chuyển sang cấu trúc mới kèm kiểm thử (mục 12) |
| Dữ liệu | Tác phẩm nhiều lớp trong bảng riêng; `post_content` do máy sinh (D-084) |
| Trang người đọc | Theme khối 1.0 + khối động `cadao/*` render trên máy chủ + JavaScript thuần nhỏ |
| Quản trị | React có sẵn của WordPress, bản dựng commit vào repo, thay hẳn Gutenberg cho tác phẩm (D-086) |
| Việc nền | Hàng đợi tự làm + cron thật của máy chủ |
| Không làm | Headless, Next.js, Astro, Meilisearch / Elasticsearch, Action Scheduler, thư viện JavaScript ngoài cho trang người đọc, tài khoản người đọc |

## 2. Repo trong lúc xây và sau khi chuyển

```text
wordpress/
├── plugins/ca-dao/        MVP — máy chủ đang chạy; đóng băng tính năng
├── themes/ca-dao/         MVP
├── v2/plugins/ca-dao/     V2 — máy chủ CHƯA lấy
├── v2/themes/ca-dao/      V2
├── v2/tests/              kiểm thử V2 (đơn vị PHP thuần + Playground)
├── v2/blueprint.json      Playground cho bản xem trước V2
├── may-chu/               kịch bản máy chủ (không đổi trong lúc xây)
└── tests/                 kiểm thử MVP
```

- Máy chủ (`cap-nhat-plugin.sh`) chỉ chép `wordpress/plugins/ca-dao` và `wordpress/themes/ca-dao`, nên gộp PR V2 vào `main` không đụng site thật.
- **Lúc chuyển** (`docs/29`): một PR chuyển `wordpress/v2/plugins/ca-dao` → `wordpress/plugins/ca-dao` (và theme tương tự), xoá mã MVP khỏi cây (lịch sử Git vẫn giữ), đổi lệnh npm mặc định sang V2, chuyển `docs/20`, `docs/24` vào `docs/luu-tru/`.
- Tên thư mục giữ `ca-dao` để kịch bản máy chủ không phải đổi (AGENTS.md, KHÔNG BAO GIỜ 15). Tên hiển thị của plugin: "Phongdao — Thư viện Văn học Dân gian Việt Nam"; theme: "Phongdao".
- Phiên bản: plugin và theme V2 bắt đầu `0.1.0`, tăng ở mỗi PR (AGENTS.md); PR chuyển đổi đặt `1.0.0`. Giữ hằng số `CADAO_PHIEN_BAN` và tệp `assets/ca-dao.js` (hoặc sửa `scripts/lib/phien-ban.mjs` trong PR chuyển đổi) để `npm run kiem-site-that` tiếp tục đúng.
- PHP: đo phiên bản PHP của máy chủ ở T-141 (qua WP-CLI khi được đồng ý, hoặc người sáng lập xem FlashPanel); viết mã cho phiên bản đó, không thấp hơn 8.1.

## 3. Cấu trúc plugin V2

```text
wordpress/v2/plugins/ca-dao/
├── ca-dao.php           khai báo, hằng số, spl_autoload cho Phongdao\, khởi động module
├── src/
│   ├── MienDuLieu/      không gọi hàm WordPress — kiểm bằng PHP thuần
│   │   ├── TacPham, Lop, LoaiLop, NguoiLam, TrangThai
│   │   ├── HoSoNguon, ChinhSachHien, CapChatLuong, DongMucKiem
│   │   ├── TheTho (nhận lục bát, song thất, một dòng…), ToChoKhac (dị bản), ChiaDoan
│   │   └── VanTay (dấu vân tay lời), BoDau (bỏ dấu, chuẩn hoá tìm)
│   ├── KhoLuu/          đọc / ghi bảng, post, term — một lớp cho mỗi bảng
│   ├── DichVu/
│   │   ├── NhapNguon/   CadaoMe (chuyển từ doc-cadao-me.php), Wikipedia (cách chơi), Wikidata, (đợt 5) Wikisource
│   │   ├── UngVien/     kho → ứng viên → tác phẩm khi Đăng
│   │   ├── ChongTrung/  chuyển từ trung.php
│   │   ├── GoiY/        vùng, chủ đề, mô-típ, Bạn có biết
│   │   ├── ChiMucTim/   chuyển từ tim.php, dựng từ lớp
│   │   ├── DungTrang/   lớp + chính sách hiện → dữ liệu cho khối; sinh post_content
│   │   ├── XepViec/     Hộp việc, Tuần này, Kho đang thiếu
│   │   ├── GopBai/      gộp tác phẩm trùng, giữ đường dẫn
│   │   ├── DoLuong/, LuotDoc/, AnhChiaSe/, Seo/, DocTiep/, NguoiDoc/
│   │   ├── NhapDuLieuCu/  công cụ chuyển đổi (docs/29) — gỡ sau khi dọn dữ liệu cũ
│   │   └── NhatKy/
│   ├── GiaoTiep/
│   │   ├── Rest/        phongdao/v1 (mục 5)
│   │   ├── Khoi/        đăng ký khối cadao/* (thư mục khoi/)
│   │   ├── Cli/         wp phongdao …
│   │   └── QuanTri/     nạp ứng dụng React, ẩn menu thừa
│   └── HaTang/          CaiDat (khai báo bảng, nâng cấp CSDL), HangDoi, LichChay, BoNhoDem, AnToan, ChuyenHuong
├── khoi/<ten>/          block.json, render.php, style.css, view.js (nếu cần)
├── quan-tri/src/        mã nguồn React
├── quan-tri/build/      bản dựng (commit)
├── assets/              JavaScript thuần cho người đọc, phông cho ảnh chia sẻ
└── languages/           (không cần: giao diện viết thẳng tiếng Việt)
```

Quy ước: tên tiếng Việt không dấu; mỗi module một lớp `Module` có `dang_ky()`; không hàm toàn cục mới (trừ hàm mẫu theme cần); không `$GLOBALS`; mọi chuỗi in ra đều escape; mọi truy vấn dùng `$wpdb->prepare`.

## 4. Mô hình dữ liệu

### 4.1. Loại bài

| Loại | Công khai | Đường dẫn | Ghi chú |
|---|---|---|---|
| `tac_pham` | có | `/<thể loại>/<tên>/` (ca-dao, tuc-ngu, dong-dao, ve, cau-do; sau: truyen, hat-ru, ho, ly) | Không hỗ trợ trình soạn khối; tiêu đề, tên đường dẫn, trạng thái, tác giả (người đăng), ngày; `post_content` do `DungTrang` sinh |
| `tuyen_tap` | có | `/tuyen-tap/<tên>/` | Lời dẫn (đoạn văn người viết), loại (`chu_de`, `dip`, `tinh_huong`, `bai_hoc`), đối tượng; mục ở `wp_cadao_tuyen_tap_muc` |
| `phan_tich` | có, sau duyệt | `/phan-tich/<tên>/` | Bài cảm nhận của người đọc (giữ từ MVP) |
| `gop_y` | không | — | Góp ý, báo lỗi, gửi số trang, dị bản người đọc gửi (giữ từ MVP) |
| `page` | có | như hiện nay | Trang tĩnh, vẫn dùng Gutenberg |

Cảm nghĩ ngắn của người đọc: bình luận WordPress, luôn chờ duyệt (giữ từ MVP).

**Tên đường dẫn:** khử dấu tiêu đề; trùng thì thêm chữ phân biệt lấy từ dòng sau (không dùng đuôi `-2`). Đường dẫn MVP đổi khi chuyển đều có 301 (`HaTang\ChuyenHuong`, D-089).

### 4.2. Phân loại (sáu chiều)

| Phân loại | Ai gán | Ghi chú |
|---|---|---|
| `the_loai` | máy từ nguồn, người sửa | Danh sách cố định trong mã; thể loại mới chỉ qua cổng (D-091) |
| `chu_de` | máy gợi ý, người duyệt | Bộ chủ đề của dự án (D-061 (B), T-091); bảng ánh xạ chủ đề nguồn → dự án giữ ở cài đặt |
| `vung` | máy gợi ý, người duyệt | **Vùng lưu truyền** có căn cứ; không suy từ địa danh trong lời |
| `mo_tip` | máy gợi ý nhóm, người đặt tên | Như T-092 |
| `hinh_thuc` | thể thơ: máy nhận; lối hát (hát ru, hò, lý…): người gán | Gộp `the_tho` của MVP |
| `muc_tu` | máy tạo từ chú thích, người sửa | Mục từ từ điển; term meta `loai` = `tu` / `dia_danh` / `nhan_vat`, Wikidata đã xác nhận, ảnh, nguồn lời giải. Địa danh trong lời = quan hệ lớp `chu_thich` → mục từ loại `dia_danh` ("được nhắc đến") |
| `tro_choi` | người tạo | Term meta: số người, độ tuổi, nơi chơi, thời lượng, đồ dùng, các bước, nguồn, giấy phép |

Bỏ phân loại `tinh_huong` (thành tuyển tập loại `tinh_huong`) và `the_tho` (thành `hinh_thuc`).

### 4.3. Lớp

Bảng `wp_cadao_lop`, mỗi mục: `id`, `tac_pham_id`, `loai`, `thu_tu`, `noi_dung` (JSON), `nguoi_lam`, `ho_so_nguon_id`, `vi_tri_nguon` (JSON: trang, url, ngày truy cập, ghi chú), `trang_thai` (`nhap`, `cho`, `da_duyet`, `an`), `duyet_boi`, `duyet_luc`, `tao_luc`, `sua_luc`. Duy nhất (`tac_pham_id`, `loai`, `thu_tu`); chỉ mục (`loai`, `trang_thai`).

| `loai` | `noi_dung` | Thể loại | Người làm được phép |
|---|---|---|---|
| `loi` | `{dong: []}` — đúng một mục mỗi tác phẩm | mọi | `may_chep`, `nguoi_viet` |
| `di_ban` | `{dong: [], noi?, nguoi_ke?, nam?}` | mọi | `may_chep`, `nguoi_viet`, `doc_gia_gop` |
| `hieu_nhanh` | `{van_ban}` (1–2 câu) | mọi | `nguoi_viet`, `ai_nhap_da_duyet` |
| `nghia_den`, `nghia_bong`, `dung_khi` | `{van_ban}` | tục ngữ – thành ngữ | `nguoi_viet`, `ai_nhap_da_duyet`, `may_chep` (Wiktionary, CC BY-SA) |
| `gan_nghia` | `{tac_pham_id, quan_he: gan / trai}` | tục ngữ – thành ngữ | `nguoi_viet` |
| `giai_sach` | `{van_ban}` (chính tả xưa) | mọi | `may_chep` (sách hết bản quyền) |
| `chu_thich` | `{tu, loi_giai, muc_tu_id?, vi_tri_trong_loi?}` | mọi | `may_chep`, `nguoi_viet` |
| `boi_canh` | `{su_kien?, nam?, noi?, nhan_vat?: [muc_tu_id]}` | vè, truyện | `nguoi_viet` |
| `cau_hoi`, `dap_an` | `{van_ban}` | câu đố | `may_chep`, `nguoi_viet` |
| `goi_y` | `{van_ban}` | câu đố | `nguoi_viet`, `ai_nhap_da_duyet` |
| `doan` | `{tieu_de?, tu_dong, den_dong}` | vè dài, truyện | `nguoi_viet` |
| `media` | `{attachment_id, loai: anh / giong_may / giong_nguoi}` | mọi | `nguoi_viet` |
| `ban_co_biet` | `{van_ban}` | mọi | `nguoi_viet`, `ai_nhap_da_duyet` (máy ghép từ chú thích) |
| `co_nghi` | `{ly_do}` + nguồn của lý do | mọi | `nguoi_viet` (máy chỉ gợi ý) |
| `sgk` | `{bo_sach, lop, bai, trang?}` | mọi | `nguoi_viet` (chỉ khi đã thấy sách) |

Trò chơi của đồng dao: quan hệ phân loại `tro_choi`, không phải lớp.

**Lên trang:** chỉ lớp `da_duyet`. Lớp `may_chep` từ nguồn bật "Chép chú thích" được `da_duyet` khi người biên soạn Đăng tác phẩm (giữ D-055, D-058 phần chép). Lớp do AI soạn luôn bắt đầu ở `nhap`.

**Ràng buộc trong mã (có kiểm thử khẳng định):**
- `loi`, `di_ban` không nhận `nguoi_lam` bắt đầu bằng `ai_`.
- `da_duyet` chỉ đặt qua route có quyền `cadao_duyet`, do người bấm; không lệnh WP-CLI, lịch chạy hay bước nhập nào đặt `da_duyet` cho lớp `ai_*` (trừ bước nhập dữ liệu cũ mang đúng trạng thái người sáng lập đã duyệt trên MVP, `docs/29`).
- `vi_tri_nguon` không có giá trị mặc định.
- Mọi thay đổi lớp ghi `wp_cadao_nhat_ky`.

### 4.4. Bảng

| Bảng | Nguồn | Ghi chú |
|---|---|---|
| `wp_cadao_lop` | mới | mục 4.3 |
| `wp_cadao_ho_so_nguon` | mới | `khoa`, `loai` (web, sách, người kể, người đọc, wiki), `ten`, `tac_gia`, `nam`, `giay_phep`, `ghi_cong`, `xin_phep` (chua, da_gui, dong_y, tu_choi, khong_can), `chinh_sach_hien` (tai_cho, tu_lieu, an), `chep_chu_thich` (bật / tắt), `ghi_chu` |
| `wp_cadao_nhat_ky` | mới | `doi_tuong`, `hanh_dong`, `truoc`, `sau`, `nguoi`, `luc` |
| `wp_cadao_hang_doi` | mới | `loai_viec`, `tham_so`, `trang_thai`, `lan_thu`, `khoa_luc`, `loi` |
| `wp_cadao_tuyen_tap_muc` | mới | `tuyen_tap_id`, `tac_pham_id`, `thu_tu`, `nhom`, `ghi_chu` ("Hợp khi…") |
| `wp_cadao_goi_y` | mới | `doi_tuong`, `loai` (vung, chu_de, mo_tip, wikidata, doi_chieu), `gia_tri`, `ly_do`, `trang_thai` — thay meta `_cadao_goi_y` và bảng `xac_nhan` |
| `wp_cadao_tim`, `wp_cadao_am_tiet` | dựng lại | chỉ mục tìm từ lớp đã duyệt |
| `wp_cadao_kho`, `wp_cadao_van_tay`, `wp_cadao_cap_am`, `wp_cadao_trung` | giữ cấu trúc MVP | kho thu thập và chống trùng; cột `post_id` trỏ tác phẩm |
| `wp_cadao_luot_doc`, `wp_cadao_su_kien` | giữ cấu trúc MVP | số liệu |
| `wp_cadao_gioi_thieu`, `wp_cadao_xac_nhan` | đọc khi nhập, rồi bỏ | thành lớp `ban_co_biet`, bảng `goi_y` |

Một số phiên bản cơ sở dữ liệu duy nhất: tuỳ chọn `cadao_csdl_phien_ban`; các bước nâng cấp đánh số trong `HaTang\CaiDat`, mỗi bước chạy lại được.

**Không có bảng "thẻ việc".** Hộp việc tính từ: kho (ứng viên), lớp `nhap`/`cho`, `wp_cadao_trung`, `wp_cadao_goi_y`, `gop_y`, bình luận chờ, `phan_tich` chờ, sự kiện `mong_co_cau`.

### 4.5. Cấp chất lượng và thân bài

`MienDuLieu\CapChatLuong`: L0 có `loi` · L1 + thể loại + dòng mức kiểm · **L2 + `hieu_nhanh` đã duyệt hoặc bộ `nghia_*` đã duyệt** · L3 + lời đã đối chiếu sách hoặc `boi_canh` hoặc `media` có giấy phép. Bài phân tích không tính (D-084). Lập chỉ mục: tác phẩm L2 trở lên.

`DichVu\DungTrang` sinh `post_content` (HTML đơn giản: lời, dị bản, Hiểu nhanh) sau mỗi lần lớp đổi — cho RSS và công cụ ngoài; trang người đọc dựng từ lớp, không đọc `post_content`.

## 5. REST `phongdao/v1`

| Nhóm | Route | Quyền |
|---|---|---|
| Người đọc | `GET /tim?q=`, `POST /su-kien`, `POST /luot-doc`, `POST /gop` (góp ý, dị bản, số trang), `POST /cam-nghi`, `POST /phan-tich` | công khai; chống rác (ô bẫy, giới hạn mỗi giờ, Turnstile khi có khoá); không cookie, không IP |
| Việc | `GET /viec`, `GET /viec/tiep`, `POST /viec/{id}/quyet` | `cadao_soan` / `cadao_duyet` |
| Tác phẩm | `GET/POST /tac-pham`, `GET/PUT /tac-pham/{id}`, `GET/POST/PUT/DELETE /tac-pham/{id}/lop`, `POST /tac-pham/{id}/dang`, `POST /trung/kiem`, `POST /gop-bai` | soạn / duyệt |
| Tuyển tập, từ điển, trò chơi, mô-típ | CRUD tương ứng | `cadao_soan` |
| Nguồn | `GET/PUT /ho-so-nguon`, `GET/PUT /nguon-tu-dong`, `POST /nguon/{id}/lay-ngay`, `GET /kho` | `cadao_nguon` |
| Người đọc gửi | `GET /nguoi-doc`, `POST /nguoi-doc/{id}/quyet` | `cadao_duyet` |
| Số liệu, cài đặt | `GET /so-lieu`, `GET/PUT /cai-dat` | quản trị |

Mọi route quản trị: `permission_callback` kiểm quyền riêng + nonce REST; trả lỗi tiếng Việt; không trả dữ liệu cá nhân thừa. REST mặc định của WordPress cho `tac_pham` tắt (`show_in_rest` chỉ cho quản trị).

### 5.1. Quyền

| Quyền | Cho phép | Gán sẵn |
|---|---|---|
| `cadao_soan` | soạn lớp, tạo nháp, quản lý tuyển tập, từ điển | Quản trị, Biên tập |
| `cadao_duyet` | duyệt lớp, đăng / ẩn tác phẩm, duyệt người đọc gửi | Quản trị, Biên tập |
| `cadao_nguon` | hồ sơ nguồn, nguồn tự động, chính sách hiện | Quản trị |
| `cadao_cong_tac` | gửi đối chiếu, soát lời; không đăng | Cộng tác (vai mới) |

## 6. Trang người đọc

- **Theme khối độc lập 1.0** (`wordpress/v2/themes/ca-dao`): `theme.json` (token `docs/20` mục 1.1–1.2, thang chữ 7 bậc, khoảng cách 8 bậc, 38em, biến thể sáng / tối / bảng màu đề xuất); `functions.php` dưới 50 dòng, không nghiệp vụ; người sáng lập chỉnh bố cục bằng Trình sửa site.
- **Mẫu:** `front-page`, `single-tac_pham-{thể loại}` (plugin thêm vào thứ bậc mẫu bằng `single_template_hierarchy`) với mặc định `single-tac_pham`, `archive-tac_pham` (Tất cả), `taxonomy-the_loai`, `taxonomy-chu_de`, `taxonomy-vung`, `taxonomy-mo_tip`, `taxonomy-muc_tu`, `taxonomy-tro_choi`, `single-tuyen_tap`, `archive-tuyen_tap`, `single-phan_tich`, `search`, `404`, `page`. Phần: đầu trang, chân trang, thanh dưới.
- **Khối động** (`cadao/*`, render trên máy chủ, khối không có dữ liệu không in gì, CSS nạp khi khối có mặt): `loi`, `muc-kiem`, `nghia`, `chu-thich`, `di-ban`, `boi-canh`, `doan`, `thuc-hanh` (câu đố, trò chơi), `hanh-dong`, `tu-lieu`, `bien-soan`, `doc-tiep`, `gop`, `thuoc-tuyen-tap`, `the-bai`, `danh-sach`, `bo-loc`, `tuyen-tap`, `loi-vao`, `cau-hom-nay`, `ban-co-biet`, `ban-do`, `tim`, `do-vui`, `vai`.
- **JavaScript thuần**, mô-đun nhỏ nạp theo trang: chạm từ (popover), lưu, chia sẻ, sao chép trích dẫn, trình chiếu, cỡ chữ / sáng tối, đọc cùng bé, tìm khi gõ, đố vui (nhớ tiến độ), vai, nhớ chỗ đọc. Tổng trên trang bài ≤ 21 KB; mọi thứ hoạt động cơ bản khi tắt JavaScript (`<details>`, form, liên kết).

## 7. Trang quản trị React

- `@wordpress/scripts` dựng; chỉ gói WordPress có sẵn (`@wordpress/components`, `@wordpress/data`, `@wordpress/api-fetch`, `@wordpress/element`, `@wordpress/icons`); không thư viện ngoài.
- `npm run dung:quan-tri` → `quan-tri/build/`; CI: dựng lại và `git diff --exit-code` thư mục build.
- Một mục menu "Phongdao" mở ứng dụng; ẩn với vai Biên tập các menu WordPress không cần (Bài viết, Bình luận gốc, Công cụ…); trình soạn tác phẩm của WordPress (`post.php` cho `tac_pham`) chuyển về ứng dụng.
- Điện thoại trước (390 px), dùng một tay; máy tính có phím tắt. Đạt WCAG 2.2 AA.
- Màn hình và hành vi: `docs/28`.

## 8. Tìm

- Chỉ mục `wp_cadao_tim` dựng từ lớp đã duyệt của tác phẩm đã đăng: tên, lời, dị bản, Hiểu nhanh, nghĩa, chú thích; bản có dấu đã chuẩn hoá + không dấu; so trọn âm tiết, âm tiết cuối khớp phần đầu (giữ thuật toán `tim.php`, D-067).
- **Gõ sai:** bảng âm tiết → tác phẩm; khi 0 hoặc ít kết quả, thử thay một âm tiết sai một chữ cái hoặc sai dấu, hiện "Có phải bạn tìm …?" (L-09: "ba co doi gai" → "Ba cô đội gạo lên chùa"); câu không có trong kho không ra bài lạc đề.
- Kết quả nhóm theo thể loại; khớp đúng một câu thì thẻ lớn đầu tiên; câu có cờ nghi vẫn hiện kèm cờ.
- Tìm không ra mà kho có câu (khớp bằng dấu vân tay hoặc chỉ mục kho riêng, không hiện lời kho): "Câu này đang chờ biên soạn" + nút "Mong có câu này".
- Dựng lại chỉ mục qua hàng đợi khi lớp đổi; lệnh `wp phongdao tim --dung-lai`.

## 9. Việc nền

Cron thật của máy chủ gọi WP-Cron mỗi 5 phút (như MVP); `HaTang\HangDoi` lấy việc theo lô có khoá, số lần thử, lỗi ghi lại. Loại việc: lấy nguồn, cập nhật ứng viên, chống trùng đêm, dựng chỉ mục, sinh ảnh chia sẻ, gọi Wikidata, gợi ý, nhập dữ liệu cũ. Hộp việc có mục "Máy đang làm". Lịch sự với máy chủ nguồn như MVP (giãn cách, giới hạn mỗi lượt).

## 10. SEO, ảnh chia sẻ

- `noindex` mọi trang khi `blog_public` tắt (tới H5). Sau khi mở: lập chỉ mục tác phẩm L2+, tuyển tập đã công khai, trang thể loại, trang chủ đề / vùng / mô-típ có ≥ 3 tác phẩm L2, mục từ có lời giải do dự án viết hoặc nguồn mở; còn lại `noindex`.
- `<title>`: tác phẩm L2 "… — nghĩa và cách dùng"; tuyển tập "…: N câu có giải nghĩa" khi mọi câu có Hiểu nhanh. Mô tả lấy từ Hiểu nhanh.
- JSON-LD: `WebSite` + `SearchAction`, `Organization` (tên mới), `CreativeWork` cho tác phẩm (`isBasedOn` chỉ khi đã đối chiếu), `DefinedTerm` cho mục từ, `CollectionPage` cho tuyển tập / phân loại, `BreadcrumbList`.
- Sitemap chỉ gồm trang được lập chỉ mục; canonical về phongdao.com.
- Ảnh chia sẻ 1200×630 vẽ bằng GD trên máy chủ (chuyển từ `anh-chia-se.php` cùng phông tự lưu): thể loại, 4 dòng đầu, dòng mức kiểm, tên miền; sinh lại khi lời đổi.

## 11. An toàn, quyền riêng tư, đo lường

- Chuyển nguyên các biện pháp của T-118, T-124: tắt XML-RPC, không lộ tên đăng nhập, câu báo chung khi đăng nhập sai, tạm khoá 15 phút sau 5 lần sai (theo tài khoản, không theo IP), chặn lặp lượt đọc và sự kiện bằng dấu băm tạm một ngày, chỉ tin IP Cloudflare khi yêu cầu đi qua Cloudflare, không lộ phiên bản WordPress (cả tệp emoji `ver=`).
- Thêm `Content-Security-Policy` (không mã nội tuyến), `Referrer-Policy`.
- Đo lường đúng 5 sự kiện của D-088 + lượt đọc (D-064); không cookie, không IP, không dịch vụ bên thứ ba.
- Không lưu email, số điện thoại của người đọc; chuỗi tìm giống email / số điện thoại bị bỏ.

## 12. Từ mã MVP sang V2

| Tệp MVP (`inc/`) | V2 | Cách |
|---|---|---|
| `doc-cadao-me.php`, `trich.php` | `NhapNguon\CadaoMe` | **chuyển nguyên thuật toán** + kiểm thử |
| `trung.php` | `ChongTrung`, `MienDuLieu\VanTay` | **chuyển nguyên** + kiểm thử |
| `tim.php` | `ChiMucTim`, `MienDuLieu\BoDau` | chuyển thuật toán, dựng chỉ mục từ lớp, thêm gõ sai |
| `do-luong.php`, `luot-doc.php`, `so-lieu.php` | `DoLuong`, `LuotDoc`, màn hình Số liệu | chuyển; thêm `mong_co_cau` |
| `an-toan.php` | `HaTang\AnToan` | chuyển |
| `anh-chia-se.php` | `AnhChiaSe` | chuyển (cùng phông) |
| `seo.php` | `Seo` | viết lại theo cấp L2 |
| `the-tho.php`; nhận lục bát, tô dị bản, gắn nút chạm, chia đoạn trong `functions.php` của theme | `MienDuLieu\TheTho`, `ToChoKhac`, `ChiaDoan`, khối `loi`, `di-ban`, `doan` | chuyển thuật toán sang PHP thuần |
| `kho.php`, `nguon-tu-dong.php` | `KhoLuu\Kho`, `UngVien`, màn hình Nguồn | giữ bảng; **bỏ tạo bài Chờ duyệt hằng ngày**, thay bằng ứng viên |
| `gop-bai.php` | `GopBai` | viết lại trên lớp, giữ hành vi (dị bản, lượt đọc, cảm nghĩ, phân tích dồn sang, 301) |
| `goi-y.php`, `mo-tip.php`, `wikidata.php` | `GoiY`, `NhapNguon\Wikidata` | chuyển logic gợi ý; lưu vào `wp_cadao_goi_y` |
| `dong-dao.php` | phân loại `tro_choi`, `NhapNguon\Wikipedia`, khối `thuc-hanh`, Đọc cùng bé | chuyển |
| `nguoi-doc.php`, `phan-tich.php` | `NguoiDoc` | chuyển (ô bẫy, giới hạn, Turnstile) |
| `doc-tiep.php` | `DocTiep` | chuyển (mô-típ trước, rồi chủ đề, từ, địa danh, vùng) |
| `ban-do.php` | khối `ban-do` | chuyển (SVG tự lưu, không dịch vụ ngoài) |
| `da-luu.php`, `trich-dan.php` | khối `hanh-dong`, trang Đã lưu | chuyển; trích dẫn mang tên mới |
| `tu-dien.php`, `sua-muc-tu.php` | phân loại `muc_tu`, màn hình Từ điển | viết lại |
| `loai-bai.php`, `danh-sach.php`, `trang-chu.php`, `muc-kiem.php` | khai báo dữ liệu, khối, mẫu theme | viết lại |
| `ban-bien-tap.php`, `trang-quan-tri.php`, `trang-trung.php`, `hieu-nhanh.php` | Bàn biên tập React | viết lại; giữ lệnh nhập nháp AI theo lô (`wp phongdao nhap-nhap`) |
| `khoi-tho.php` | — | **bỏ** (không còn Gutenberg cho tác phẩm) |
| `tuc-ngu-thanh-ngu.php`, `site-cu.php` | `HaTang\ChuyenHuong` | **bỏ**, chỉ giữ bảng chuyển hướng 301 |
| `chinh-sach-du-lieu.php`, `trang-du-an.php` | — | **bỏ** mã sinh trang; nội dung trang tĩnh mang sang (`docs/29`) |

## 13. Kiểm thử, CI, lệnh

| Lệnh | Việc |
|---|---|
| `npm run kiem-tra` | nhất quán backlog, trạng thái (giữ) |
| `npm run test:v2` | PHP thuần (`wordpress/v2/tests/don-vi/`, vài giây) + Playground (`wordpress/v2/tests/`) |
| `npm run quet:v2` | axe WCAG 2.2 AA mọi mẫu thể loại, trang chủ, tuyển tập, tìm, từ điển, trang quản trị chính; ảnh chụp 390 px; đo ngân sách (trang bài ≤ 200 KB, JavaScript trang bài ≤ 21 KB, tuyển tập ≤ 300 KB); kiểm liên kết ra ngoài |
| `npm run xem-truoc:v2` | link Playground của nhánh với plugin + theme V2 và dữ liệu mẫu |
| `npm run dung:quan-tri` | dựng ứng dụng React |
| `npm run e2e:v2` | Playwright trên bản xem trước: luồng đọc, tìm, đố vui, góp ý, Duyệt nhanh, soạn |

- Dữ liệu mẫu: chỉ chữ mẫu và các bài mẫu đã có trong kiểm thử MVP (D-050); không chép kho thô hay lời từ site thật vào repo (AGENTS.md, KHÔNG BAO GIỜ 14).
- SQL chạy được trên SQLite (Playground) và MySQL; tránh cú pháp chỉ MySQL có.
- CI: thêm job `v2` (kiểm thử, quét, so bản dựng) chạy khi chạm `wordpress/v2/`; job MVP giữ tới khi chuyển.
- AGENTS.md "LUÔN LUÔN": task V2 chạy `npm run kiem-tra`, `npm run test:v2`, `npm run quet:v2` trước khi kết thúc.

## 14. Rủi ro kỹ thuật

| Rủi ro | Cách giảm |
|---|---|
| Chuyển thuật toán làm đổi kết quả (chống trùng, trích) | Chuyển kèm kiểm thử của MVP; chạy cùng bộ ca trên hai bản |
| Bản dựng React quên cập nhật | CI so bản dựng |
| Khối động chậm | Đệm transient theo bài, xoá khi lớp đổi; `Cache-Control` cho khách khi kịch bản cập nhật biết xoá đệm; đo TTFB (MVP 0,40–0,67 s) |
| Playground khác MySQL | Tránh SQL riêng của MySQL; chạy thử công cụ nhập trên site thật ở chế độ chỉ đọc trước (`docs/29`) |
| Xây lâu, MVP cũ dần | Chỉ làm đúng phạm vi đợt 1–3; mọi thứ khác để đợt 5 |
