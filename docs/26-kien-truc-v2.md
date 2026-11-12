# 26 · Kỹ thuật V2

| | |
|---|---|
| Phiên bản | 2.3 · 11/11/2026 (đợt 3: D-103..D-107, `docs/32`; quản trị wp-admin: D-096, D-100; dữ liệu giữ D-094) |
| Trạng thái | **Đã duyệt.** Đổi điều đã chốt ở đây phải ghi D-xxx; chi tiết kỹ thuật trong phạm vi do agent tự chọn và ghi trong PR |
| Đọc khi | Mọi task V2 (`docs/22`, nhóm V2) |
| Đọc kèm | `27` (sản phẩm cho người đọc), `28` (luồng nội dung, biên tập), `32` (kế hoạch đợt 3), AGENTS.md; chuyển đổi và kế hoạch quản trị cũ ở `docs/luu-tru/` (`29`, `30`) |
| Căn cứ | `docs/nghien-cuu/v2-backend-plugin-theme-2026-10-27.md` (đo trên plugin 0.33.2, theme 0.19.1) |

## 1. Quyết định gốc

| Mặt | Chốt |
|---|---|
| Nền tảng | WordPress làm khung; viết mới toàn bộ plugin, theme, trang quản trị, mô hình dữ liệu (D-083) |
| Cách làm | Xây song song trong `wordpress/v2/`, **đã chuyển 04/11/2026** (`docs/luu-tru/29-chuyen-doi-v2.md`); mã MVP đã gỡ ở T-192 (12/11/2026); plugin MVP chỉ còn làm dữ liệu cho kiểm thử chuyển đổi ở `tests/chuyen-doi-mvp/` |
| Tài sản giữ | Thuật toán đã kiểm chứng được chuyển sang cấu trúc mới kèm kiểm thử (mục 12) |
| Dữ liệu | Mỗi phần của bài lưu trong bài (post meta có schema), kèm người làm và nguồn; một bảng mới `wp_cadao_viec` cho việc chờ và nhật ký (D-084, D-094) |
| Trang người đọc | Theme khối 1.0 + khối động `cadao/*` render trên máy chủ + JavaScript thuần nhỏ |
| Quản trị | wp-admin là nơi làm việc (D-096, D-100); ba dòng việc Bài mới · Hộp việc · Bổ sung (D-103); sửa nhanh trên trang đọc giữ; `/bien-tap/` đã gộp về wp-admin (D-104, `docs/32`; chuyển hướng 301); một đường kiểm/lưu; không Gutenberg cho tác phẩm |
| Việc nền | WP-Cron theo lô (như MVP) + cron thật của máy chủ |
| Không làm | Headless, Next.js, Astro, Meilisearch / Elasticsearch, Action Scheduler, React / bước dựng JavaScript, thư viện JavaScript ngoài, tài khoản người đọc |

## 2. Repo trong lúc xây và sau khi chuyển

```text
wordpress/
├── v2/plugins/ca-dao/     V2 — máy chủ đang chạy (từ 04/11/2026)
├── v2/themes/ca-dao/      V2
├── v2/tests/              kiểm thử V2 (đơn vị PHP thuần + Playground)
├── v2/blueprint.json      Playground cho bản xem trước V2
├── may-chu/               kịch bản máy chủ (mục 15)
└── nguon-trien-khai        ghi "v2": máy chủ chép wordpress/v2/…

tests/chuyen-doi-mvp/      plugin MVP cũ — chỉ là dữ liệu cho kiểm thử chuyển đổi (T-192), máy chủ không chép
tests/e2e-v2/              công cụ khảo sát V2 cho người review

design/v2/                 bộ thiết kế UX/UI bản cuối (D-092): mã nguồn bản mẫu, token, đặc tả, qa — không chạy trên site
```

- Máy chủ (`cap-nhat-plugin.sh`) chép `wordpress/v2/plugins/ca-dao` và `wordpress/v2/themes/ca-dao` khi commit có tệp `wordpress/nguon-trien-khai` ghi `v2` (mọi commit từ ngày chuyển); commit cũ hơn (quay về bằng `NHANH=<mã>`) vẫn ra mã MVP của commit đó. Trước ngày chuyển gộp PR V2 vào `main` không đụng site thật.
- **Lúc chuyển** (`docs/luu-tru/29-chuyen-doi-v2.md` mục 5 bước 2): PR chuyển đổi chỉ thêm tệp đánh dấu và đặt 1.0.0; mã MVP để nguyên (bộ kiểm thử chuyển đổi cần nó, quay về commit cũ vẫn ra MVP). **Khi dọn dữ liệu cũ** (`docs/luu-tru/29-chuyen-doi-v2.md` mục 6): chuyển `wordpress/v2/…` lên chỗ `wordpress/…`, bỏ tệp đánh dấu, xoá mã MVP (lịch sử Git vẫn giữ), đổi lệnh npm mặc định sang V2, chuyển `docs/20`, `docs/24` vào `docs/luu-tru/`. Mỗi lần thay mã, script làm mới quy tắc đường dẫn (`wp rewrite flush`) vì MVP và V2 lưu quy tắc khác nhau trong cơ sở dữ liệu.
- Tên thư mục giữ `ca-dao` để kịch bản máy chủ không phải đổi (AGENTS.md, KHÔNG BAO GIỜ 15). Tên hiển thị của plugin: "Phongdao — Thư viện Văn học Dân gian Việt Nam"; theme: "Phongdao".
- Phiên bản: plugin và theme V2 bắt đầu `0.1.0`, tăng ở mỗi PR (AGENTS.md); PR chuyển đổi đặt `1.0.0` và thêm `wordpress/nguon-trien-khai` (`v2`): máy chủ và `scripts/lib/phien-ban.mjs` (`kiem-site-that`) cùng đọc tệp này để biết lấy plugin, theme ở `wordpress/v2/`; trang V2 lộ số phiên bản qua `do-luong.js?ver=` và `giao-dien.css?ver=`.
- PHP: đo phiên bản PHP của máy chủ ở T-141 (qua WP-CLI khi được đồng ý, hoặc người sáng lập xem FlashPanel); viết mã cho phiên bản đó, không thấp hơn 8.1. **Kết quả (T-141, 02/11/2026):** máy chủ chạy PHP 8.5 (người sáng lập chọn trong FlashPanel ở T-075, `docs/luu-tru/24-wordpress.md` mục 6; tiêu đề HTTP không lộ số); V2 khai `Requires PHP: 8.3` — không dùng cú pháp chỉ có từ 8.4; máy CI có sẵn 8.3 cho bộ kiểm thuần, Playground kiểm ở 8.3.

## 3. Cấu trúc plugin V2

```text
wordpress/v2/plugins/ca-dao/
├── ca-dao.php           khai báo, hằng số, spl_autoload cho Phongdao\, khởi động module
├── src/
│   ├── MienDuLieu/      không gọi hàm WordPress — kiểm bằng PHP thuần
│   │   ├── Phan (danh mục các phần của bài), KiemPhan (lằn ranh khi lưu), NguoiLam, TheLoai, BanChu
│   │   ├── HoSoNguon, ChinhSachHien, CapChatLuong, DongMucKiem
│   │   ├── TheTho (nhận lục bát, song thất, một dòng…), ToChoKhac (dị bản), ChiaDoan
│   │   └── VanTay (dấu vân tay lời), BoDau (bỏ dấu, chuẩn hoá tìm)
│   ├── KhoLuu/          đọc / ghi post meta, term meta, bảng — một lớp PHP cho mỗi loại
│   ├── DichVu/
│   │   ├── NhapNguon/   CadaoMe (chuyển từ doc-cadao-me.php), Wikipedia (cách chơi), Wikidata, (đợt 5) Wikisource
│   │   ├── UngVien/     kho → thẻ "Câu mới" → tác phẩm khi Đăng
│   │   ├── ChongTrung/  chuyển từ trung.php
│   │   ├── GoiY/        vùng, chủ đề, mô-típ, Bạn có biết
│   │   ├── ChiMucTim/   chuyển từ tim.php, dựng từ các phần đã lưu
│   │   ├── DungTrang/   phần + chính sách hiện → dữ liệu cho khối; bản chữ cho RSS
│   │   ├── XepViec/     Hộp việc, Tuần này, Kho đang thiếu (bảng việc)
│   │   ├── LuuPhan/     lưu một phần: kiểm lằn ranh, ghi nhật ký vào bảng việc, dựng lại chỉ mục
│   │   │               LuuPhanLoai: người sửa tay phân loại (chỉ mục có sẵn, chống ghi đè, nhật ký cùng giao dịch)
│   │   │               LichSu: khôi phục lần sửa mới nhất khi chưa có thay đổi mới hơn; DauVetTrangThai: dấu vết đổi trạng thái
│   │   ├── GopBai/      gộp tác phẩm trùng, giữ đường dẫn
│   │   ├── DoLuong/, LuotDoc/, AnhChiaSe/, Seo/, DocTiep/, NguoiDoc/
│   │   └── NhapDuLieuCu/  công cụ chuyển đổi (docs/luu-tru/29-chuyen-doi-v2.md) — gỡ sau khi dọn dữ liệu cũ
│   ├── GiaoTiep/
│   │   ├── Rest/        phongdao/v1 (mục 5)
│   │   ├── Khoi/        đăng ký khối cadao/* (thư mục khoi/)
│   │   ├── Cli/         wp phongdao …
│   │   ├── BienTap/     thẻ Hộp việc, nhận form Thêm bài, nạp mã sửa cho người có quyền, chế độ sửa trên trang đọc
│   │   └── QuanTri/     trang Nguồn & cài đặt (PHP), ẩn menu thừa
│   └── HaTang/          KhaiBao (loại bài, phân loại, khoá meta, đường dẫn), CaiDat (bảng, nâng cấp CSDL), LichChay, BoNhoDem, AnToan, ChuyenHuong
├── khoi/<ten>/          block.json, render.php, style.css, view.js (nếu cần)
├── assets/              bien-tap.js (chỉ người biên soạn), phông cho ảnh chia sẻ
└── languages/           (không cần: giao diện viết thẳng tiếng Việt)
```

Quy ước: tên tiếng Việt không dấu; mỗi module một lớp `Module` có `dang_ky()`; không hàm toàn cục mới (trừ hàm mẫu theme cần); không `$GLOBALS`; mọi chuỗi in ra đều escape; mọi truy vấn dùng `$wpdb->prepare`.

## 4. Mô hình dữ liệu

### 4.1. Loại bài

| Loại | Công khai | Đường dẫn | Ghi chú |
|---|---|---|---|
| `tac_pham` | có | `/<thể loại>/<tên>/` (ca-dao, tuc-ngu, dong-dao, ve, cau-do; sau: truyen, hat-ru, ho, ly) | Không hỗ trợ trình soạn khối, không bản sửa; tiêu đề, tên đường dẫn, trạng thái, tác giả (người đăng), ngày; các phần ở post meta (mục 4.3); `post_content` là bản chữ do `LuuPhan` ghi |
| `tuyen_tap` | có | `/tuyen-tap/<tên>/` | Lời dẫn (đoạn văn người viết), loại (`chu_de`, `dip`, `tinh_huong`, `bai_hoc`), đối tượng, các mục — đều ở meta của tuyển tập (mục 4.3) |
| `phan_tich` | có, sau duyệt | `/phan-tich/<tên>/` | Bài cảm nhận của người đọc (giữ từ MVP) |
| `gop_y` | không | — | Góp ý, báo lỗi, gửi số trang, dị bản người đọc gửi (giữ từ MVP) |
| `page` | có | như hiện nay | Trang tĩnh, vẫn dùng Gutenberg |
| `ho_so_nguon` | không | — | Hồ sơ nguồn (mục 4.3), sửa ở Nguồn & cài đặt |

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
| `muc_tu` | máy tạo từ chú thích, người sửa | Mục từ từ điển; term meta `loai` = `tu` / `dia_danh` / `nhan_vat`, Wikidata đã xác nhận, ảnh, nguồn lời giải. Địa danh trong lời = mục `chu_thich` → mục từ loại `dia_danh` ("được nhắc đến") |
| `tro_choi` | người tạo | Term meta: số người, độ tuổi, nơi chơi, thời lượng, đồ dùng, các bước, nguồn, giấy phép |

Bỏ phân loại `tinh_huong` (thành tuyển tập loại `tinh_huong`) và `the_tho` (thành `hinh_thuc`).

**Term meta (03/11/2026, đặt khi viết công cụ nhập — `docs/luu-tru/29-chuyen-doi-v2.md`):** mục từ: lời giải là mô tả của term; `_phongdao_loi_giai` `{nguoi_lam: may_chep | nguoi_viet, nguon?: {ho_so, url?}}`, `_phongdao_wikidata` (bản đã xác nhận, dạng MVP), `_phongdao_anh` `{rieng?: ID ảnh, ghi_cong?, an?}`, `_phongdao_khoa` (khoá từ + lời giải để nối chú thích). Trò chơi: `_phongdao_cach_choi` `{so_nguoi?, do_tuoi?, do_dung?, buoc: [], nguoi_lam, nguon?: {ho_so, url?, ghi_chu?}, giay_phep?, wiki?}`. Mô-típ: `_phongdao_mo_tip_dau` (dấu nhóm của máy; tên do người đặt là tên term). Bộ chủ đề, bảng ánh xạ chủ đề, mô-típ đã đặt / bỏ qua giữ ở các tuỳ chọn MVP cùng tên (`cadao_bo_chu_de`, `cadao_anh_xa_chu_de`, `cadao_mo_tip_bo`, `cadao_mo_tip_bo_qua`).

### 4.3. Phần của bài

> Đợt 3 (D-105, `docs/32` mục 6): thêm phần "Hình ảnh trong bài" (ca dao, đồng dao), loại góp "Cách hiểu" của người đọc (**đã làm, plugin 1.0.50:** bình luận `comment_type = cach_hieu`, chờ duyệt, chỉ bút danh — không email, địa chỉ mạng, trình duyệt; thẻ `nguoi_doc` có `noi_dung.loai = cach_hieu`; đọc bằng `DocBai::cam_nghi( $id, 'cach_hieu' )`, hiện ở `GiaoTiep\Khoi\HieuThem`); câu đố hiển thị `y_nghia` là "Giải thích đáp án"; tục ngữ mới dùng bộ `nghia_*` + `dung_khi` thay `y_nghia`. Bảng dưới cập nhật khi các thay đổi đó được làm.

Mỗi phần của tác phẩm là **một khoá post meta** `_phongdao_<phần>` (D-094), khai báo bằng `register_post_meta` trong `HaTang\KhaiBao` (danh mục phần ở `MienDuLieu\Phan`), không mở qua REST mặc định của WordPress. Giá trị là mảng PHP; hình dạng và lằn ranh kiểm ở `MienDuLieu\KiemPhan` (PHP thuần, có kiểm thử) mỗi lần lưu.

Mỗi phần (hoặc mỗi mục trong phần dạng danh sách) mang:
- `nguoi_lam`: `may_chep` (máy chép từ nguồn bật "Chép chú thích"), `nguoi_viet`, `ai_da_duyet` (nháp AI người đã duyệt), `doc_gia_gop`;
- `nguon`: `{ho_so, trang?, url?, ngay_truy_cap?, ghi_chu?}` — `ho_so` là ID hồ sơ nguồn; trống = chưa rõ;
- `sua_boi`, `sua_luc`.

| Phần | Giá trị (ngoài các trường chung) | Thể loại | Người làm được phép |
|---|---|---|---|
| `loi` | `{dong: [], doi_chieu?: {ho_so, trang, url?}}` — một mỗi tác phẩm; câu đố: lời là câu đố | mọi | `may_chep`, `nguoi_viet` |
| `di_ban` | danh sách `{dong: [], noi?, nguoi_ke?, nam?}` | mọi | `may_chep`, `nguoi_viet`, `doc_gia_gop` |
| `y_nghia` | `{chu, ai, duyet_boi, duyet_luc}` — 2–3 câu; tên hiển thị "Ý nghĩa" (trước gọi `hieu_nhanh`) | mọi | `nguoi_viet`, `ai_da_duyet` |
| `nghia_den`, `nghia_bong`, `dung_khi` | `{chu, ai}` | tục ngữ – thành ngữ | `nguoi_viet`, `ai_da_duyet`, `may_chep` (Wiktionary, CC BY-SA) |
| `hinh_anh` | `{chu, ai}` — mỗi dòng "hình ảnh → điều được nói tới" | ca dao, đồng dao | `nguoi_viet`, `ai_da_duyet` (D-105; máy không chép) |
| `gan_nghia` | danh sách `{tac_pham_id, quan_he: gan / trai}` | tục ngữ – thành ngữ | `nguoi_viet` |
| `giai_sach` | `{chu}` (chính tả xưa) | mọi | `may_chep` (sách hết bản quyền) |
| `chu_thich` | danh sách `{tu?, loi_giai, muc_tu_id?, vi_tri?}` | mọi | `may_chep`, `nguoi_viet` |
| `cau_hoi` | `{chu}` — dòng hỏi nguồn tách khỏi lời ("Là cái gì?") | câu đố | `may_chep`, `nguoi_viet` |
| `dap_an` | `{chu}` | câu đố | `may_chep`, `nguoi_viet` |
| `goi_y` | `{chu, ai}` | câu đố | `nguoi_viet`, `ai_da_duyet` |
| `doan` | danh sách `{tieu_de?, tu_dong, den_dong}` — dòng đếm theo dòng có chữ của lời, từ 1; dòng ngoài mọi đoạn hiện thành đoạn không tiêu đề. Bài từ 41 dòng không có phần này thì tự chia theo khổ (`MienDuLieu\ChiaDoan`, như MVP) | vè dài, truyện | `nguoi_viet` |
| `boi_canh` | `{chu?, su_kien?, nam?, noi?, nhan_vat?: [muc_tu_id]}` — `chu`: lời giới thiệu ngắn hiện ngay trên lời | vè, truyện | `nguoi_viet` |
| `media` | danh sách `{attachment_id, loai: anh / giong_may / giong_nguoi}` | mọi | `nguoi_viet` |
| `ban_co_biet` | `{chu, ai}` | mọi | `nguoi_viet`, `ai_da_duyet` (máy ghép từ chú thích) |
| `co_nghi` | `{ly_do}` | mọi | `nguoi_viet` (máy chỉ gợi ý) |
| `sgk` | danh sách `{bo_sach, lop, bai, trang?}` | mọi | `nguoi_viet` (chỉ khi đã thấy sách) |
| `nguon_may` | `{nguon_id, url_goc, ma_goc, ngay_lay}` — **nội bộ**, không bao giờ có trong HTML gửi người đọc | mọi | máy ghi khi người bấm Đăng từ thẻ "Câu mới" |

Trò chơi của đồng dao: quan hệ phân loại `tro_choi`, không phải phần.

**Lên trang:** mọi phần đã nằm trong bài là phần người đã quyết — lời, chú thích máy chép chỉ vào bài khi người bấm Đăng thẻ "Câu mới" (giữ D-055, D-058 phần chép). **Nháp** (nháp AI theo lô, ý nghĩa người lưu mà chưa đánh "đã duyệt", dị bản người đọc gửi) chỉ nằm ở bảng việc (mục 4.4), không vào bài trước khi người có quyền bấm duyệt.

**Lịch sử:** không dùng bản sửa (revisions) của WordPress cho tác phẩm. Mỗi lần lưu một phần ghi một dòng vào `wp_cadao_viec` (ai, lúc nào, phần, trước, sau); khối Lịch sử cuối bài (chỉ người biên soạn thấy) đọc từ đó; **Hoàn tác** trong 10 giây đặt lại giá trị "trước", về sau khôi phục được từ Lịch sử.

**Ràng buộc trong mã (`KiemPhan`, `LuuPhan`; có kiểm thử khẳng định):**
- `loi`, `di_ban` không nhận `nguoi_lam` bắt đầu bằng `ai_`; nháp AI không bao giờ vào `loi`.
- `ai_da_duyet`, `duyet_boi`, `duyet_luc` chỉ đặt qua route có quyền `cadao_duyet`, do người bấm; không lệnh WP-CLI, lịch chạy hay bước nhập nào ghi phần có `ai` bật vào bài (trừ bước nhập dữ liệu cũ mang đúng trạng thái người sáng lập đã duyệt trên MVP, `docs/luu-tru/29-chuyen-doi-v2.md`).
- `nguon`, `doi_chieu`, `noi`, `nguoi_ke`, `nam`, `sgk` không có giá trị mặc định, không do máy điền.
- Mọi lần lưu ghi nhật ký vào `wp_cadao_viec`; lưu thất bại thì không ghi nửa chừng.

**Phân loại và loại bài khác:** mục từ, trò chơi, chủ đề, vùng, mô-típ, thể loại dùng term meta (mục 4.2), cùng quy ước người làm / nguồn. Tuyển tập: meta `_phongdao_loi_dan` (`{chu, nguoi_lam}`, chỉ `nguoi_viet`), `_phongdao_loai`, `_phongdao_doi_tuong`, `_phongdao_muc` (danh sách `{tac_pham_id, nhom?, ghi_chu?}` — "Hợp khi…"). Trang tác phẩm tìm "Có trong tuyển tập" bằng cách đọc danh sách tuyển tập (vài chục, có đệm).

**Hồ sơ nguồn:** loại bài ẩn `ho_so_nguon` (không công khai, không có trình soạn, không REST mặc định); tên là tiêu đề; meta: `khoa`, `loai` (web, sách, người kể, người đọc, wiki), `tac_gia`, `nam`, `giay_phep`, `ghi_cong`, `xin_phep` (chua, da_gui, dong_y, tu_choi, khong_can), `chinh_sach_hien` (tai_cho, tu_lieu, an), `chep_chu_thich` (bật / tắt), `ghi_chu`. Sửa ở trang Nguồn & cài đặt.

### 4.4. Bảng

| Bảng | Nguồn | Ghi chú |
|---|---|---|
| `wp_cadao_viec` | **mới, bảng duy nhất mới** | Việc chờ người quyết **và** nhật ký. Cột: `id`, `loai` (cau_moi, nhap_y_nghia, nhap_nghia, co_the_trung, gop_tac_pham, goi_y_chu_de, goi_y_vung, goi_y_mo_tip, wikidata, doi_chieu, nguoi_doc_gui, cam_nghi, phan_tich, mong_co, bao_loi, can_xem, sua), `trang_thai` (cho, de_sau, xong, bo), `doi_tuong_loai` (post, term, kho), `doi_tuong_id`, `phan`, `noi_dung` (JSON: nháp, gợi ý, lý do, hai lời đặt cạnh…), `uu_tien`, `nguoi_tao` (`may`, ID người dùng, `doc_gia`), `tao_luc`, `quyet` (hành động đã chọn), `truoc`, `sau` (JSON), `nguoi_quyet`, `quyet_luc`. Chỉ mục (`trang_thai`, `uu_tien`), (`doi_tuong_loai`, `doi_tuong_id`). Một lần sửa trực tiếp trên trang = một dòng `sua` ở trạng thái `xong` |
| `wp_cadao_tom_tat` | **mới (đợt 3, CSDL bước 12)** | Bảng tóm tắt bài (`DichVu\TomTat`, `docs/32` mục 3.9): mỗi tác phẩm một dòng tính sẵn — `the_loai`, `trang_thai`, `cap` (L0–L3, −1 khi chưa có lời), `thieu` (",y_nghia,tu_kho,…," — câu đố: `dap_an`, `giai_thich`; tục ngữ: `nghia_tn`), `co`, `nguon_may`, `nguon_nguoi`, `doc_7` (lượt đọc 7 ngày), `sua_luc`. Ghi lại khi một post meta `_phongdao_…` đổi, đổi trạng thái, đổi thể loại; xoá bài thì xoá dòng; lượt đọc tính lại hằng ngày (WP-Cron). Dựng lại toàn bộ: bước nâng cấp (≤ 3.000 bài dựng ngay, nhiều hơn theo lô) hoặc `wp phongdao tom-tat --dung-lai`. Chỉ là bản đọc nhanh: nguồn sự thật vẫn là các phần trong bài. Danh sách Tác phẩm, bộ lọc, số đếm, Hôm nay, Bổ sung đọc từ đây |
| `wp_cadao_tim` | giữ tên, dựng lại | chỉ mục tìm từ các phần đã lưu của tác phẩm đã đăng; danh sách âm tiết cho tìm gõ sai tính từ chỉ mục, giữ trong một tuỳ chọn không tự nạp |
| `wp_cadao_kho`, `wp_cadao_van_tay`, `wp_cadao_cap_am`, `wp_cadao_trung` | giữ cấu trúc MVP | kho thu thập và chống trùng; cột `post_id` trỏ tác phẩm |
| `wp_cadao_luot_doc`, `wp_cadao_su_kien` | giữ cấu trúc MVP | số liệu |
| `wp_cadao_gioi_thieu`, `wp_cadao_xac_nhan` | đọc khi nhập, rồi bỏ | thành phần `ban_co_biet`, việc Wikidata đã quyết |

Từ bước nâng cấp CSDL 10, bảng việc có thêm `phien` (số nguyên bắt đầu từ 0, tăng một lần sau mỗi quyết định). Form Hộp việc gửi số phiên đã nhìn thấy; dịch vụ quyết giữ thẻ bằng cập nhật có điều kiện trong giao dịch, dùng trạng thái `dang_lam` chỉ trong giao dịch, rồi chốt trạng thái và nhật ký. Yêu cầu mang phiên cũ nhận lỗi xung đột rõ ràng. Đây là lớp bảo vệ cho quyết từng thẻ hiện chạy; nhất quán ghi trực tiếp phần bài và các đường máy/loạt còn thuộc phần tiếp theo của T-177.

Khi `post_content` không cập nhật được sau khi ghi phần bài, `LuuPhan` trả lỗi, hoàn tác meta/nhật ký và không phát sự kiện sau lưu. Trong lượt quyết thẻ, giao dịch bao cả bước này nên lỗi ghi DB trả thẻ về trạng thái chờ; quyết định cặp nghi trùng cũng phải xác nhận đã ghi được trước khi đóng thẻ. Các nhánh khác còn được rà tiếp trong T-177.

Một số phiên bản cơ sở dữ liệu duy nhất: tuỳ chọn `phongdao_csdl`; các bước nâng cấp đánh số trong `HaTang\CaiDat`, mỗi bước chạy lại được.

**Hộp việc là một truy vấn** trên `wp_cadao_viec` (`trang_thai = cho`, xếp theo `uu_tien`). Máy thêm việc khi: chọn câu mới từ kho mỗi ngày, chống trùng thấy "có thể trùng", gợi ý chủ đề / vùng / mô-típ / Wikidata / đối chiếu sách, nạp nháp theo lô, câu được "mong có" mà kho có, người đọc gửi góp ý / dị bản / số trang / báo lỗi (bài `gop_y` giữ từ MVP, việc trỏ tới bài đó), cảm nghĩ, bài phân tích chờ duyệt, bước nhập dữ liệu cũ gặp bài "Cần xem".

### 4.5. Cấp chất lượng và thân bài

`MienDuLieu\TuKho` (T-190.2): khoá so khớp từ khó theo tiếng (chữ thường, bỏ dấu thanh + ghi dấu thanh, NFC), lần đầu trong cả bài, báo từ không có trong lời; `Ve::loi` / `Ve::loi_doan` dùng chung.

`MienDuLieu\CapChatLuong`: L0 có `loi` · L1 + thể loại + dòng mức kiểm · **L2 + `y_nghia` hoặc bộ `nghia_*`** · L3 + lời đã đối chiếu sách hoặc `boi_canh` hoặc `media` có giấy phép. Bài phân tích, cách hiểu của bạn đọc không tính (D-084, D-105). Cấp chỉ dùng nội bộ; lập chỉ mục không xét cấp (D-106, mục 10).

`DichVu\LuuPhan` ghi `post_content` dạng chữ đơn giản (lời, dị bản, ý nghĩa) sau mỗi lần lưu — cho RSS và công cụ ngoài; trang người đọc dựng từ các phần, không đọc `post_content`; không ai sửa `post_content` bằng tay.

## 5. REST `phongdao/v1`

| Nhóm | Route | Quyền |
|---|---|---|
| Người đọc | `GET /tim?q=`, `POST /su-kien`, `POST /luot-doc`, `POST /gop` (góp ý, dị bản, số trang), `POST /cam-nghi`, `POST /phan-tich` | công khai; chống rác (ô bẫy, giới hạn mỗi giờ, Turnstile khi có khoá); không cookie, không IP |
| Việc | `GET /viec`, `GET /viec/tiep`, `POST /viec/{id}/quyet`, `POST /viec/{id}/hoan-tac` | `cadao_soan` / `cadao_duyet` |
| Tác phẩm | `POST /tac-pham` (Thêm bài), `GET /tac-pham` (danh sách biên tập), `PUT /tac-pham/{id}/phan/{phan}` (lưu một phần, trả HTML khối vẽ lại), `POST /tac-pham/{id}/trang-thai` (đăng / ẩn), `GET /tac-pham/{id}/lich-su`, `POST /trung/kiem`, `POST /gop-bai` | soạn / duyệt |
| Tuyển tập, mục từ, trò chơi, mô-típ, chủ đề, vùng, thể loại | `POST /<loại>` (tạo), `PUT /<loại>/{id}/phan/{phan}` (sửa trên trang) | `cadao_soan` |
| Nguồn, cài đặt | không qua REST: trang PHP Nguồn & cài đặt gửi form qua `admin-post.php` (nonce + quyền) | `cadao_nguon` / quản trị |
| Số liệu tuần | không qua REST: khối `cadao/so-lieu-tuan` vẽ trên máy chủ | `cadao_soan` |

Người đọc gửi (góp ý, dị bản, số trang, báo lỗi), cảm nghĩ, bài phân tích được quyết qua route Việc như mọi thẻ khác.

Mọi route biên tập: `permission_callback` kiểm quyền riêng + nonce REST; mọi lần lưu đi qua `LuuPhan` (kiểm lằn ranh, ghi nhật ký); trả lỗi tiếng Việt; không trả dữ liệu cá nhân thừa. REST mặc định của WordPress cho `tac_pham`, `ho_so_nguon` và các khoá `_phongdao_*` tắt.

**Contract đang chạy của lưu từng phần (T-195; thay mô tả PUT ở hàng Tác phẩm phía trên):** `POST /tac-pham/{id}/phan/{phan}`, body là các trường form và `phien_phan` của bản đã mở. wp-admin qua `admin-post.php?action=phongdao_sua_phan` và sửa nhanh cùng gọi đường lưu này. Thiếu/sai phiên trả `{ok:false, xung_dot:true, loi:[...], phien_moi, so_ban_moi}`; không tự lấy phiên hiện tại để ghi tiếp. `so_ban_moi` là HTML đã escape cho người có quyền sửa đối chiếu, chỉ khi họ chọn dùng phần đang nhập thì gửi phiên mới. Đọc/so/ghi trực tiếp giữ khóa bài trong cùng giao dịch; lỗi thì rollback và xoá cache bài. Lô sửa từng hàng giữ kiểm bản xem trước hiện có và gửi phiên vào cùng đường lưu. Các mô tả route khác ở bảng này chưa được đồng bộ bởi T-195.

### 5.1. Quyền

Ảnh minh hoạ (T-196): cùng `POST /tac-pham/{id}/phan/media`, form gửi `attachment_id` (0 = bỏ liên kết) và `phien_phan`. Tấm sửa chuyển thành danh sách `media.muc` hiện có, thay các mục `loai=anh` và giữ loại khác. Không đổi bảng/schema phần bài. Cần quyền tải tệp; khi lưu hoặc khôi phục kiểm attachment là ảnh raster còn đọc được tại site. Tải tệp và metadata dùng quyền/nonce của Thư viện WordPress; ghi công là attachment meta `_phongdao_anh_ghi_cong`, đọc được ghi công Commons cũ khi có. Alt/chú thích/ghi công dùng chung theo tệp, lịch sử phần bài chỉ khôi phục liên kết. `wp.media` chỉ nạp ở wp-admin; trang đọc nhận ảnh đáp ứng qua mapper/renderer, ảnh câu đố nằm trong đáp án.

| Quyền | Cho phép | Gán sẵn |
|---|---|---|
| `cadao_soan` | thấy chế độ sửa và bốn trang biên tập; sửa các phần, lưu nháp, thêm bài, sửa tuyển tập, mục từ | Quản trị, Biên tập |
| `cadao_duyet` | duyệt nháp (ý nghĩa, nghĩa, gợi ý), đăng / ẩn tác phẩm, quyết thẻ người đọc gửi | Quản trị, Biên tập |
| `cadao_nguon` | hồ sơ nguồn, nguồn tự động, chính sách hiện | Quản trị |
| `cadao_cong_tac` | gửi đối chiếu, soát lời; không đăng | Cộng tác (vai mới) |

## 6. Trang người đọc

- **Theme khối độc lập 1.0** (`wordpress/v2/themes/ca-dao`): `theme.json` (token bảng tím – lục ở `design/v2/tokens/` — D-092, thang chữ 7 bậc, khoảng cách 8 bậc, 38em, biến thể sáng / tối; hình ảnh từng màn theo bộ thiết kế `design/v2/`); `functions.php` dưới 50 dòng, không nghiệp vụ; người sáng lập chỉnh bố cục bằng Trình sửa site.
- **Mẫu:** `front-page`, `single-tac_pham-{thể loại}` (plugin thêm vào thứ bậc mẫu bằng `single_template_hierarchy`) với mặc định `single-tac_pham`, `archive-tac_pham` (Tất cả), `taxonomy-the_loai`, `taxonomy-chu_de`, `taxonomy-vung`, `taxonomy-mo_tip`, `taxonomy-muc_tu`, `taxonomy-tro_choi`, `single-tuyen_tap`, `archive-tuyen_tap`, `single-phan_tich`, `search`, `404`, `page`. Phần: đầu trang, chân trang, thanh dưới.
- **Khối động** (`cadao/*`, render trên máy chủ, khối không có dữ liệu không in gì, CSS nạp khi khối có mặt): `loi`, `muc-kiem`, `nghia`, `chu-thich`, `di-ban`, `boi-canh`, `doan`, `thuc-hanh` (câu đố, trò chơi), `hanh-dong`, `tu-lieu`, `bien-soan`, `doc-tiep`, `gop`, `thuoc-tuyen-tap`, `the-bai`, `danh-sach`, `bo-loc`, `tuyen-tap`, `loi-vao`, `cau-hom-nay`, `ban-co-biet`, `ban-do`, `tim`, `do-vui`, `vai`.
- **JavaScript thuần**, mô-đun nhỏ nạp theo trang: chạm từ (popover), lưu, chia sẻ, sao chép trích dẫn, trình chiếu, cỡ chữ / sáng tối, đọc cùng bé, tìm khi gõ, đố vui (nhớ tiến độ), vai, nhớ chỗ đọc. Tổng trên trang bài ≤ 21 KB; mọi thứ hoạt động cơ bản khi tắt JavaScript (`<details>`, form, liên kết).

## 7. Biên tập: nơi làm việc

- **wp-admin là nơi làm việc** (D-096, D-100): menu Phongdao theo ba dòng việc **Hôm nay · Bài mới · Hộp việc · Bổ sung · Tác phẩm · Nguồn & cài đặt**; đăng nhập vào Hôm nay (`page=phongdao-ban-lam-viec`, giữ đường dẫn cũ của Bàn làm việc); trang chào mừng WordPress có khung "Phongdao hôm nay" (móc `load-index.php` → `admin_notices`). Màn con (Sửa tác phẩm, Thao tác hàng loạt) không lên menu nhưng menu vẫn mở và tô đúng mục (`QuanTri\Module::muc_cha`). Ba dòng việc **Bài mới · Hộp việc · Bổ sung** (D-103); hiện trạng màn và đích đợt 3: `docs/28` mục 4, `docs/32`.
- **Mã:** `GiaoTiep\QuanTri` (Hôm nay `BanLamViec`, Bài mới `DanhSachKho`, Hộp việc, Bổ sung `BoSung`, Tác phẩm, Sửa tác phẩm, Thao tác hàng loạt, Nguồn & cài đặt) gọi cùng dịch vụ với trang đọc: `DichVu\LuuPhan` + `MienDuLieu\KiemPhan` (mọi lần lưu một phần), `DichVu\XepViec\QuyetViec` (quyết thẻ), `DichVu\XuLyLo\Lo` (thao tác hàng loạt: chốt phạm vi → xem trước → áp dụng từng lượt, kết quả từng mục), `DichVu\LuuPhan\LichSu` (so trước/sau, khôi phục). Form gửi `admin-post.php`, có nonce, kiểm quyền, kiểm phiên.
- **Sửa nhanh trên trang đọc** giữ (D-094, D-104): nút Sửa trên từng khối, tấm sửa, dải "Đang sửa" (trạng thái, Ẩn / Đăng, Xem như người đọc); `bien-tap.js` thuần, chỉ nạp cho người có `cadao_soan`; khách không bao giờ nhận các phần sửa.
- **`/bien-tap/…`** đã gộp về wp-admin (D-104, plugin 1.0.49): `TrangCoDinh\Module::dich_quan_tri()` chuyển hướng 301 — `/bien-tap/` → Hộp việc (giữ `viec`, `loai`, `tt=de_sau`), `/bien-tap/tac-pham/` → Tác phẩm (`loc=thieu-y-nghia` → `muc=thieu_y_nghia`, `loc=ban-nhap` → `tt=draft`, `q`), `/bien-tap/them/` → Thêm tác phẩm, `/bien-tap/so-lieu/` → Hôm nay; wp-admin tự đòi đăng nhập. Khối `cadao/hop-viec`, `ds-tac-pham`, `them-bai`, `so-lieu-tuan` và bốn mẫu trang biên tập của theme đã gỡ. Lối gốc WordPress của tác phẩm (`edit.php?post_type=tac_pham`, `post.php?action=edit`, `post-new.php?post_type=tac_pham`) chuyển sang Tác phẩm / Sửa tác phẩm / Thêm tác phẩm (`QuanTri\Module::dich_loi_goc()`; thùng rác, khôi phục và loại bài khác giữ nguyên); menu WordPress không ẩn (D-100).
- **Bộ nhớ đệm:** trang của người đăng nhập trả `Cache-Control: private, no-store`; đệm transient của khối chỉ dùng cho khách; lưu một phần xoá đệm của bài đó.
- **Giao diện quản trị:** PHP dựng HTML, JavaScript thuần nhỏ theo màn (`assets/admin-*.js`), không ứng dụng React, không bước dựng; điện thoại 390 px trước, WCAG 2.2 AA (`quet:v2` quét các màn admin chính). Không ẩn menu WordPress với vai nào (D-100).
- Tác phẩm không mở sửa trực tiếp post meta mảng qua ô Trường tuỳ chỉnh hay REST mặc định; `show_in_menu` của `tac_pham` tắt — đợt 3 chuyển hướng danh sách và màn sửa gốc của WordPress sang màn Phongdao (D-104).

## 8. Tìm

- Chỉ mục `wp_cadao_tim` dựng từ các phần đã lưu của tác phẩm đã đăng: tên, lời, dị bản, ý nghĩa, nghĩa, chú thích; bản có dấu đã chuẩn hoá + không dấu; so trọn âm tiết, âm tiết cuối khớp phần đầu (giữ thuật toán `tim.php`, D-067).
- **Gõ sai:** danh sách âm tiết của chỉ mục; khi 0 hoặc ít kết quả, thử thay một âm tiết sai một chữ cái hoặc sai dấu, hiện "Có phải bạn tìm …?" (L-09: "ba co doi gai" → "Ba cô đội gạo lên chùa"); câu không có trong kho không ra bài lạc đề.
- Kết quả nhóm theo thể loại; khớp đúng một câu thì thẻ lớn đầu tiên; câu có cờ nghi vẫn hiện kèm cờ.
- Tìm không ra mà kho có câu (khớp bằng dấu vân tay hoặc chỉ mục kho riêng, không hiện lời kho): "Câu này đang chờ biên soạn" + nút "Mong có câu này".
- Dựng lại dòng chỉ mục của bài ngay khi lưu một phần; dựng lại toàn bộ theo lô bằng lệnh `wp phongdao tim --dung-lai`.
- **Xếp trên mọi ứng viên (T-181):** câu 1 lấy mọi bài có đủ các chữ, chỉ kèm cờ khớp do CSDL tính (`MienDuLieu\ChiMuc::phep_so()`: cả cụm, đủ chữ, đúng dấu, từ đầu tên — `LIKE BINARY`, chạy trên MariaDB và SQLite của Playground) và độ dài, không kèm chữ; `ChiMuc::xep_co()` xếp theo điểm cận trên và chỉ đọc chữ (từng lô 100 bài) của bài còn phải xét các chữ có gần nhau không mà còn có thể lọt vào 20 bài đầu. Tổng là số bài thật (trước đây dừng ở 500, 20 bài đầu lấy từ 500 bài đầu bảng nên bỏ sót bài mới). Kết quả trùng cách xếp mọi ứng viên bằng PHP (`ChiMuc::xep()`), kiểm trên kho thử 1.500 bài (PHP thuần) và 1.200 dòng trong Playground. Gợi ý gõ sai chỉ đếm (`ChiMucTim::dem()`, `COUNT(*)`).
- **Đo:** `npm run do:tim:v2` (Playground) hoặc `php wordpress/v2/tests/do-tim/mysql.php …` (CSDL MySQL/MariaDB riêng để đo) đổ kho thử tổng hợp 1.000–10.000 bài (`tests/do-tim/kho-thu.php`, không phải lời thật) và so cách cũ, lấy hết, cách mới theo 12 truy vấn đại diện. Số đo lần đầu ghi ở `docs/22` T-181.1. Chưa cần chỉ mục toàn văn: ở 10.000 bài, truy vấn chậm nhất (ba chữ, quét `LIKE`) khoảng 55 ms trên MySQL 8.4.

## 9. Việc nền

Cron thật của máy chủ gọi WP-Cron mỗi 5 phút (như MVP); mỗi loại việc nền là một sự kiện WP-Cron chạy theo lô, có khoá (transient) để không chạy chồng; con trỏ lô, lần chạy, lỗi gần nhất giữ trong tuỳ chọn `phongdao_viec_nen` — không bảng hàng đợi (D-094). Loại việc: lấy nguồn, chọn câu mới cho Hộp việc, chống trùng đêm, dựng chỉ mục, sinh ảnh chia sẻ, gọi Wikidata, gợi ý, nhập dữ liệu cũ. Nguồn & cài đặt có mục "Máy đang làm". Lịch sự với máy chủ nguồn như MVP (giãn cách, giới hạn mỗi lượt).

## 10. SEO, ảnh chia sẻ

- `noindex` mọi trang khi `blog_public` tắt (công tắc chặn Google toàn site, Human Gate H5 — người sáng lập mở).
- Khi đã mở (D-106): **mọi tác phẩm đã đăng được lập chỉ mục**, không xét độ đầy đủ; tuyển tập đã công khai; trang thể loại; trang chủ đề / vùng / mô-típ có từ 3 tác phẩm đã đăng; mục từ có lời giải do dự án viết hoặc nguồn mở (lời giải chép từ nguồn: không, D-068). Trang lọc, trang tìm, bài nháp / ẩn: `noindex`. Cấp L0–L3 chỉ dùng nội bộ (xếp việc, bài nổi bật, Câu hôm nay).
- `<title>`: tác phẩm có Ý nghĩa (tục ngữ: nghĩa bóng) "… — nghĩa và cách dùng"; chưa có: tên + thể loại. Tuyển tập "…: N câu có giải nghĩa" khi mọi câu có ý nghĩa. Mô tả lấy từ ý nghĩa khi có, không thì từ lời.
- JSON-LD: `WebSite` + `SearchAction`, `Organization` (tên mới), `CreativeWork` cho tác phẩm (`isBasedOn` chỉ khi đã đối chiếu), `DefinedTerm` cho mục từ, `CollectionPage` cho tuyển tập / phân loại, `BreadcrumbList`.
- Sitemap chỉ gồm trang được lập chỉ mục; canonical về phongdao.com.
- Ảnh chia sẻ 1200×630 vẽ bằng GD trên máy chủ (chuyển từ `anh-chia-se.php` cùng phông tự lưu): thể loại, 4 dòng đầu, dòng mức kiểm, tên miền; sinh lại khi lời đổi.

## 11. An toàn, quyền riêng tư, đo lường

- Chuyển nguyên các biện pháp của T-118, T-124: tắt XML-RPC, không lộ tên đăng nhập, câu báo chung khi đăng nhập sai, tạm khoá 15 phút sau 5 lần sai (theo tài khoản, không theo IP), chặn lặp lượt đọc và sự kiện bằng dấu băm tạm một ngày, chỉ tin IP Cloudflare khi yêu cầu đi qua Cloudflare, không lộ phiên bản WordPress (cả tệp emoji `ver=`).
- Thêm `Content-Security-Policy` (không mã nội tuyến), `Referrer-Policy`. Đã làm (`HaTang\AnToan::dau_bao_ve`): mọi trang người đọc có `Referrer-Policy: strict-origin-when-cross-origin`, `X-Content-Type-Options: nosniff`; khách có CSP `script-src 'self'` + Turnstile (tắt quy tắc tải trước của WordPress và liên kết bỏ qua tự chèn — đều là mã nội tuyến), mã nội tuyến cố định (đoạn sáng / tối của theme) khai dấu băm qua bộ lọc `phongdao_csp_script`, cấu hình đo lường là khối JSON; `style-src` cho kiểu nội tuyến (WordPress, `style="--dai"`); không máy chủ ngoài khác; người đăng nhập không nhận CSP. `quet:v2` báo lỗi khi thiếu đầu CSP hoặc trình duyệt chặn gì. Cloudflare: không bật Rocket Loader, Web Analytics (chèn mã, CSP chặn).
- Đo lường đúng 5 sự kiện của D-088 + lượt đọc (D-064); không cookie, không IP, không dịch vụ bên thứ ba.
- Không lưu email, số điện thoại của người đọc; chuỗi tìm giống email / số điện thoại bị bỏ.

## 12. Từ mã MVP sang V2

Đã chuyển xong 04/11/2026. Bảng chuyển từng tệp MVP sang lớp V2: `docs/luu-tru/26-muc-7-12-truoc-dot-3.md`. Mã MVP đã gỡ ở T-192 (12/11/2026): theme, kiểm thử, bản xem trước, quét và job CI `wordpress` của MVP, bộ khảo sát E2E Playwright của site MVP. Plugin MVP chuyển sang `tests/chuyen-doi-mvp/plugin-ca-dao/` — chỉ để kiểm thử chuyển đổi (`test:v2`) tạo dữ liệu MVP như ngày chuyển; không chạy trên máy chủ.

## 13. Kiểm thử, CI, lệnh

| Lệnh | Việc |
|---|---|
| `npm run kiem-tra` | nhất quán backlog, trạng thái (giữ) |
| `npm run test:v2` | PHP thuần (`wordpress/v2/tests/don-vi/`, vài giây) + Playground (`wordpress/v2/tests/`) |
| `npm run quet:v2` | axe WCAG 2.2 AA mọi mẫu thể loại, trang chủ, tuyển tập, tìm, từ điển, Hộp việc, trang bài ở chế độ sửa; ảnh chụp 390 px; đo ngân sách (trang bài ≤ 200 KB, JavaScript trang bài ≤ 21 KB, tuyển tập ≤ 300 KB); kiểm liên kết ra ngoài |
| `npm run xem-truoc:v2` | link Playground của nhánh với plugin + theme V2 và dữ liệu mẫu |
| `npm run do:tim:v2 [-- 1000,5000,10000]` | đo tìm kiếm trên kho thử tổng hợp trong Playground: so cách cũ (500 ứng viên), lấy hết, cách mới theo 12 truy vấn đại diện; bản MySQL/MariaDB: `php wordpress/v2/tests/do-tim/mysql.php` (CSDL riêng để đo). Không chạy trong CI |
| `npm run dong-bo:giao-dien-v2` | chép CSS và `fit.js` từ bộ thiết kế `design/v2/source` sang theme (thêm chế độ tối theo cài đặt máy); `test:v2` báo lỗi khi theme lệch bộ thiết kế — sửa giao diện ở bộ thiết kế trước |
| `npm run e2e:v2` | đi từng thao tác trong Chrome (puppeteer-core, điện thoại 390 px; lỗi JavaScript của trang là lỗi) trên bản xem trước: người đọc (Bạn là, tìm khi gõ và khi Enter, Sao chép lời, Lưu → Đã lưu → Bỏ lưu, Trích dẫn APA + Esc trả tiêu điểm, Chia sẻ, cỡ chữ và sáng/tối nhớ sau khi tải lại, chạm từ khó, tắt JavaScript vẫn đọc được, vè dài: Mục lục và Đọc toàn bài, câu đố, trình chiếu tuyển tập, góp); người biên soạn (sửa ý nghĩa chưa duyệt → nháp → Duyệt ở Hộp việc → người đọc thấy, góp của người đọc → Đã xử lý, sửa lời → Hoàn tác, Thêm bài → đăng → Ẩn bài, sửa mục từ). `-- --site <địa chỉ>`: chỉ phần người đọc trên site thật, chặn mọi yêu cầu không phải GET/HEAD (không gửi form, không tính lượt đọc hay sự kiện); trang thử tự tìm trong danh sách thể loại |

- Dữ liệu mẫu: chỉ chữ mẫu và các bài mẫu đã có trong kiểm thử MVP (D-050); không chép kho thô hay lời từ site thật vào repo (AGENTS.md, KHÔNG BAO GIỜ 14).
- SQL chạy được trên SQLite (Playground) và MySQL; tránh cú pháp chỉ MySQL có.
- CI: job `v2` (kiểm thử, quét, e2e) chạy khi chạm `wordpress/v2/`, `tests/chuyen-doi-mvp/`, kịch bản; job `kiem-tra` (bắt buộc) kiểm nhất quán; job `wordpress` của MVP đã gỡ (T-192). Không có bản dựng JavaScript để so.
- `npm run kiem-site-that`: site thật có đúng phiên bản plugin, theme của `main` không (chỉ đọc).
- AGENTS.md "LUÔN LUÔN": task V2 chạy `npm run kiem-tra`, `npm run test:v2`, `npm run quet:v2` trước khi kết thúc.

## 14. Rủi ro kỹ thuật

| Rủi ro | Cách giảm |
|---|---|
| Chuyển thuật toán làm đổi kết quả (chống trùng, trích) | Chuyển kèm kiểm thử của MVP; chạy cùng bộ ca trên hai bản |
| Lộ phần sửa cho khách qua bộ nhớ đệm | Phần sửa chỉ in khi người xem có quyền; trang người đăng nhập `private, no-store`; đệm transient chỉ cho khách; `quet:v2` kiểm HTML khách không có nút Sửa, không có `nguon_may` |
| Meta của bài sai hình dạng | Mọi lần ghi qua `LuuPhan` + `KiemPhan` (PHP thuần, có kiểm thử); lệnh `wp phongdao kiem-phan` rà toàn bộ |
| Khối động chậm | Đệm transient theo bài, xoá khi lưu một phần; `Cache-Control` cho khách khi kịch bản cập nhật biết xoá đệm; đo TTFB (MVP 0,40–0,67 s) |
| Playground khác MySQL | Tránh SQL riêng của MySQL; đo lại trên MySQL/MariaDB khi đổi truy vấn nặng (`do:tim:v2`) |
| Đợt 3 lớn, nhiều màn đổi cùng lúc | Làm theo thứ tự `docs/32` mục 8, mỗi bước một PR có bản xem trước; giữ đường cũ chuyển hướng |

## 15. Máy chủ và triển khai (T-074, T-075; chuyển từ `docs/24` mục 8)

**phongdao.com** (D-070; trước 26/10/2026 là cadao.sdigi.net, nay chuyển hướng 301) — VPS của người sáng lập (Ubuntu 24.04), quản lý bằng **FlashPanel** (gói miễn phí), web server Apache + PHP-FPM, MariaDB 10.11. Tên miền qua Cloudflare (proxy bật, chứng chỉ Let's Encrypt do FlashPanel cấp và gia hạn). VPS còn chạy **các site khác của người sáng lập**: chỉ đụng tới site cadao.

| Thành phần | Cách làm |
|---|---|
| Site, PHP 8.5, SSL, cài WordPress | Người sáng lập bấm trong FlashPanel |
| **Cách ly** | Gói miễn phí không có ô "Website Isolation" khi tạo site → *Cài Đặt → Update Owner* `cadao`: FlashPanel tạo tài khoản Linux riêng, pool PHP riêng, thêm vào nhóm `isolated` (ACL chặn vào `/home` của site khác) |
| Bổ sung cách ly | `cai-dat.sh` gỡ `cadao` khỏi nhóm `www-data` (nhóm đó gửi lệnh được tới pool PHP chung của các site khác), `wp-config.php` quyền 600 |
| Tiếng Việt, múi giờ, đường dẫn, plugin, cron | `wordpress/may-chu/cai-dat.sh` (chạy bằng root, chạy lại được) |
| **Tự cập nhật sau khi gộp PR** (T-078, gia cố T-175) | Cron mỗi 5 phút chạy `tu-cap-nhat.sh` bằng tài khoản `cadao`: thấy `main` có commit mới thì lấy plugin/theme của đúng commit đó (V2 khi có `wordpress/nguon-trien-khai`, MVP khi không), kiểm cú pháp PHP, thay thư mục và giữ bản cũ tới khi kiểm trang chủ đạt. Lỗi giữa các bước chuyển thư mục hoặc trang chủ không đạt thì cố phục hồi bản cũ; nếu phục hồi thất bại, giữ thư mục `.ca-dao-cu` để xử lý tay, không tự xoá. Commit lỗi không được thử lại. Nhật ký `/home/cadao/cap-nhat.log`; các bản đã lên site ở `/home/cadao/.cap-nhat-lich-su` (50 bản gần nhất). Chạy tay: `cd /home/cadao && sudo -u cadao bash bin/cap-nhat-plugin.sh`. **Quay lại bản trước:** V2 → V2 dùng Revert hoặc `NHANH=<mã commit>`; V2 → MVP phải hoàn tác dữ liệu trước khi đổi mã — `docs/huong-dan/quay-lai-ban-truoc.md` |
| Lịch chạy | `DISABLE_WP_CRON`; `/etc/cron.d/cadao-wordpress` chạy `wp cron event run --due-now` mỗi phút |
| WP-CLI | `sudo -u cadao /home/cadao/bin/wp <lệnh>` |
| Chặn công cụ tìm kiếm | `blog_public = 0` tới khi mở công khai (H5, T-053) — ô "Discourage search engines" của FlashPanel đã không có tác dụng, script đặt lại |
| Sao lưu | Người sáng lập: FlashPanel → *Sao Lưu*, hằng ngày, gồm site và cơ sở dữ liệu cadao, lưu ra ngoài VPS (Google Drive…) |

Agent vào VPS bằng khóa SSH riêng trên máy người sáng lập (không dùng mật khẩu). Không ghi IP, tài khoản hay thông tin các site khác vào repo.
