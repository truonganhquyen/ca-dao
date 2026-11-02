# 26 · Kỹ thuật V2

| | |
|---|---|
| Phiên bản | 2.1 · 02/11/2026 (xây mới: D-083..D-086, D-088, D-089; quản trị và lưu bài: D-094) |
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
| Dữ liệu | Mỗi phần của bài lưu trong bài (post meta có schema), kèm người làm và nguồn; một bảng mới `wp_cadao_viec` cho việc chờ và nhật ký (D-084, D-094) |
| Trang người đọc | Theme khối 1.0 + khối động `cadao/*` render trên máy chủ + JavaScript thuần nhỏ |
| Quản trị | Sửa ngay trên trang đọc + bốn trang `/bien-tap/…` + Nguồn & cài đặt trong wp-admin; JavaScript thuần, không bước dựng; không Gutenberg cho tác phẩm (D-086, D-094) |
| Việc nền | WP-Cron theo lô (như MVP) + cron thật của máy chủ |
| Không làm | Headless, Next.js, Astro, Meilisearch / Elasticsearch, Action Scheduler, React / bước dựng JavaScript, thư viện JavaScript ngoài, tài khoản người đọc |

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

design/v2/                 bộ thiết kế UX/UI bản cuối (D-092): mã nguồn bản mẫu, token, đặc tả, qa — không chạy trên site
```

- Máy chủ (`cap-nhat-plugin.sh`) chỉ chép `wordpress/plugins/ca-dao` và `wordpress/themes/ca-dao`, nên gộp PR V2 vào `main` không đụng site thật.
- **Lúc chuyển** (`docs/29`): một PR chuyển `wordpress/v2/plugins/ca-dao` → `wordpress/plugins/ca-dao` (và theme tương tự), xoá mã MVP khỏi cây (lịch sử Git vẫn giữ), đổi lệnh npm mặc định sang V2, chuyển `docs/20`, `docs/24` vào `docs/luu-tru/`.
- Tên thư mục giữ `ca-dao` để kịch bản máy chủ không phải đổi (AGENTS.md, KHÔNG BAO GIỜ 15). Tên hiển thị của plugin: "Phongdao — Thư viện Văn học Dân gian Việt Nam"; theme: "Phongdao".
- Phiên bản: plugin và theme V2 bắt đầu `0.1.0`, tăng ở mỗi PR (AGENTS.md); PR chuyển đổi đặt `1.0.0`. Giữ hằng số `CADAO_PHIEN_BAN` và tệp `assets/ca-dao.js` (hoặc sửa `scripts/lib/phien-ban.mjs` trong PR chuyển đổi) để `npm run kiem-site-that` tiếp tục đúng.
- PHP: đo phiên bản PHP của máy chủ ở T-141 (qua WP-CLI khi được đồng ý, hoặc người sáng lập xem FlashPanel); viết mã cho phiên bản đó, không thấp hơn 8.1. **Kết quả (T-141, 02/11/2026):** máy chủ chạy PHP 8.5 (người sáng lập chọn trong FlashPanel ở T-075, `docs/24` mục 6; tiêu đề HTTP không lộ số); V2 khai `Requires PHP: 8.3` — không dùng cú pháp chỉ có từ 8.4; máy CI có sẵn 8.3 cho bộ kiểm thuần, Playground kiểm ở 8.3.

## 3. Cấu trúc plugin V2

```text
wordpress/v2/plugins/ca-dao/
├── ca-dao.php           khai báo, hằng số, spl_autoload cho Phongdao\, khởi động module
├── src/
│   ├── MienDuLieu/      không gọi hàm WordPress — kiểm bằng PHP thuần
│   │   ├── TacPham, Phan (các phần của bài), NguoiLam, KiemPhan (lằn ranh khi lưu)
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
│   │   ├── GopBai/      gộp tác phẩm trùng, giữ đường dẫn
│   │   ├── DoLuong/, LuotDoc/, AnhChiaSe/, Seo/, DocTiep/, NguoiDoc/
│   │   └── NhapDuLieuCu/  công cụ chuyển đổi (docs/29) — gỡ sau khi dọn dữ liệu cũ
│   ├── GiaoTiep/
│   │   ├── Rest/        phongdao/v1 (mục 5)
│   │   ├── Khoi/        đăng ký khối cadao/* (thư mục khoi/)
│   │   ├── Cli/         wp phongdao …
│   │   ├── BienTap/     trang /bien-tap/…, nạp mã sửa cho người có quyền, chế độ sửa trên trang đọc
│   │   └── QuanTri/     trang Nguồn & cài đặt (PHP), ẩn menu thừa
│   └── HaTang/          CaiDat (khai báo meta, bảng, nâng cấp CSDL), LichChay, BoNhoDem, AnToan, ChuyenHuong
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

### 4.3. Phần của bài

Mỗi phần của tác phẩm là **một khoá post meta** `_phongdao_<phần>` (D-094), khai báo bằng `register_post_meta` trong `HaTang\CaiDat`, không mở qua REST mặc định của WordPress. Giá trị là mảng PHP; hình dạng và lằn ranh kiểm ở `MienDuLieu\KiemPhan` (PHP thuần, có kiểm thử) mỗi lần lưu.

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
| `gan_nghia` | danh sách `{tac_pham_id, quan_he: gan / trai}` | tục ngữ – thành ngữ | `nguoi_viet` |
| `giai_sach` | `{chu}` (chính tả xưa) | mọi | `may_chep` (sách hết bản quyền) |
| `chu_thich` | danh sách `{tu?, loi_giai, muc_tu_id?, vi_tri?}` | mọi | `may_chep`, `nguoi_viet` |
| `dap_an` | `{chu}` | câu đố | `may_chep`, `nguoi_viet` |
| `goi_y` | `{chu, ai}` | câu đố | `nguoi_viet`, `ai_da_duyet` |
| `doan` | danh sách `{tieu_de?, tu_dong, den_dong}` | vè dài, truyện | `nguoi_viet` |
| `boi_canh` | `{su_kien?, nam?, noi?, nhan_vat?: [muc_tu_id]}` | vè, truyện | `nguoi_viet` |
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
- `ai_da_duyet`, `duyet_boi`, `duyet_luc` chỉ đặt qua route có quyền `cadao_duyet`, do người bấm; không lệnh WP-CLI, lịch chạy hay bước nhập nào ghi phần có `ai` bật vào bài (trừ bước nhập dữ liệu cũ mang đúng trạng thái người sáng lập đã duyệt trên MVP, `docs/29`).
- `nguon`, `doi_chieu`, `noi`, `nguoi_ke`, `nam`, `sgk` không có giá trị mặc định, không do máy điền.
- Mọi lần lưu ghi nhật ký vào `wp_cadao_viec`; lưu thất bại thì không ghi nửa chừng.

**Phân loại và loại bài khác:** mục từ, trò chơi, chủ đề, vùng, mô-típ, thể loại dùng term meta (mục 4.2), cùng quy ước người làm / nguồn. Tuyển tập: meta `_phongdao_loi_dan` (`{chu, nguoi_lam}`, chỉ `nguoi_viet`), `_phongdao_loai`, `_phongdao_doi_tuong`, `_phongdao_muc` (danh sách `{tac_pham_id, nhom?, ghi_chu?}` — "Hợp khi…"). Trang tác phẩm tìm "Có trong tuyển tập" bằng cách đọc danh sách tuyển tập (vài chục, có đệm).

**Hồ sơ nguồn:** loại bài ẩn `ho_so_nguon` (không công khai, không có trình soạn, không REST mặc định); tên là tiêu đề; meta: `khoa`, `loai` (web, sách, người kể, người đọc, wiki), `tac_gia`, `nam`, `giay_phep`, `ghi_cong`, `xin_phep` (chua, da_gui, dong_y, tu_choi, khong_can), `chinh_sach_hien` (tai_cho, tu_lieu, an), `chep_chu_thich` (bật / tắt), `ghi_chu`. Sửa ở trang Nguồn & cài đặt.

### 4.4. Bảng

| Bảng | Nguồn | Ghi chú |
|---|---|---|
| `wp_cadao_viec` | **mới, bảng duy nhất mới** | Việc chờ người quyết **và** nhật ký. Cột: `id`, `loai` (cau_moi, nhap_y_nghia, nhap_nghia, co_the_trung, gop_tac_pham, goi_y_chu_de, goi_y_vung, goi_y_mo_tip, wikidata, doi_chieu, nguoi_doc_gui, cam_nghi, phan_tich, mong_co, bao_loi, can_xem, sua), `trang_thai` (cho, de_sau, xong, bo), `doi_tuong_loai` (post, term, kho), `doi_tuong_id`, `phan`, `noi_dung` (JSON: nháp, gợi ý, lý do, hai lời đặt cạnh…), `uu_tien`, `nguoi_tao` (`may`, ID người dùng, `doc_gia`), `tao_luc`, `quyet` (hành động đã chọn), `truoc`, `sau` (JSON), `nguoi_quyet`, `quyet_luc`. Chỉ mục (`trang_thai`, `uu_tien`), (`doi_tuong_loai`, `doi_tuong_id`). Một lần sửa trực tiếp trên trang = một dòng `sua` ở trạng thái `xong` |
| `wp_cadao_tim` | giữ tên, dựng lại | chỉ mục tìm từ các phần đã lưu của tác phẩm đã đăng; danh sách âm tiết cho tìm gõ sai tính từ chỉ mục, giữ trong một tuỳ chọn không tự nạp |
| `wp_cadao_kho`, `wp_cadao_van_tay`, `wp_cadao_cap_am`, `wp_cadao_trung` | giữ cấu trúc MVP | kho thu thập và chống trùng; cột `post_id` trỏ tác phẩm |
| `wp_cadao_luot_doc`, `wp_cadao_su_kien` | giữ cấu trúc MVP | số liệu |
| `wp_cadao_gioi_thieu`, `wp_cadao_xac_nhan` | đọc khi nhập, rồi bỏ | thành phần `ban_co_biet`, việc Wikidata đã quyết |

Một số phiên bản cơ sở dữ liệu duy nhất: tuỳ chọn `phongdao_csdl`; các bước nâng cấp đánh số trong `HaTang\CaiDat`, mỗi bước chạy lại được.

**Hộp việc là một truy vấn** trên `wp_cadao_viec` (`trang_thai = cho`, xếp theo `uu_tien`). Máy thêm việc khi: chọn câu mới từ kho mỗi ngày, chống trùng thấy "có thể trùng", gợi ý chủ đề / vùng / mô-típ / Wikidata / đối chiếu sách, nạp nháp theo lô, câu được "mong có" mà kho có, người đọc gửi góp ý / dị bản / số trang / báo lỗi (bài `gop_y` giữ từ MVP, việc trỏ tới bài đó), cảm nghĩ, bài phân tích chờ duyệt, bước nhập dữ liệu cũ gặp bài "Cần xem".

### 4.5. Cấp chất lượng và thân bài

`MienDuLieu\CapChatLuong`: L0 có `loi` · L1 + thể loại + dòng mức kiểm · **L2 + `y_nghia` hoặc bộ `nghia_*`** · L3 + lời đã đối chiếu sách hoặc `boi_canh` hoặc `media` có giấy phép. Bài phân tích không tính (D-084). Lập chỉ mục: tác phẩm L2 trở lên.

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

### 5.1. Quyền

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

## 7. Biên tập: sửa trên trang đọc, bốn trang biên tập, Nguồn & cài đặt

Không có ứng dụng quản trị riêng (D-094). Màn mẫu: nhóm B của `design/v2` (B01–B05). Màn hình và hành vi: `docs/28` mục 4.

- **Chế độ sửa trên trang đọc.** Khi người xem có quyền `cadao_soan`, các khối `cadao/*` in thêm nút **Sửa** và dải "Đang sửa" (trạng thái bài, "Xem như người đọc", Ẩn / Đăng); khách không bao giờ nhận các phần này trong HTML. Nút Sửa mở **tấm sửa** (`<dialog>`: trượt từ dưới lên trên điện thoại, cột phải trên máy tính), lưu một phần qua `PUT /tac-pham/{id}/phan/{phan}`, rồi khối được vẽ lại từ máy chủ — người biên soạn thấy đúng như người đọc sẽ thấy. Trang tuyển tập, mục từ, trò chơi, mô-típ, chủ đề, vùng, thể loại làm cùng cách.
- **Bốn trang biên tập** do plugin đăng ký đường dẫn và mẫu khối (`register_block_template`): `/bien-tap/` (Hộp việc), `/bien-tap/tac-pham/`, `/bien-tap/them/`, `/bien-tap/so-lieu/`; khối `cadao/hop-viec`, `cadao/ds-tac-pham`, `cadao/them-bai`, `cadao/so-lieu-tuan`. Chưa đăng nhập → chuyển tới trang đăng nhập rồi quay lại; không đủ quyền → 403 tiếng Việt. Luôn `noindex`, không có trong sitemap.
- **Mã sửa:** một tệp JavaScript thuần `bien-tap.js` (không bước dựng, không thư viện), chỉ nạp khi người xem có quyền `cadao_soan`; ngân sách riêng ≤ 30 KB gzip, không tính vào ngân sách trang bài của người đọc. Không có JavaScript thì các trang biên tập vẫn đọc được và các quyết định chính của Hộp việc vẫn gửi được bằng form.
- **Bộ nhớ đệm:** trang của người đăng nhập trả `Cache-Control: private, no-store`; đệm transient của khối chỉ dùng cho khách; lưu một phần xoá đệm của bài đó.
- **Nguồn & cài đặt:** một trang PHP trong wp-admin (menu "Phongdao"), form gửi qua `admin-post.php` có nonce và kiểm quyền: hồ sơ nguồn, nguồn tự động, kho (tìm, Đã gộp, "Tách ra"), máy đang làm, cài đặt. Menu "Phongdao" còn có đường tắt tới Hộp việc.
- **wp-admin còn lại:** ẩn với vai Biên tập các menu không cần (Bài viết, Bình luận gốc, Công cụ…); liên kết "Sửa" của tác phẩm trong WordPress (thanh quản trị, `get_edit_post_link`) trỏ về trang bài; `post.php` của `tac_pham` chuyển hướng về trang bài.
- Điện thoại trước (390 px), dùng một tay; máy tính có phím tắt ở Hộp việc. Đạt WCAG 2.2 AA như trang người đọc (cùng `quet:v2`).

## 8. Tìm

- Chỉ mục `wp_cadao_tim` dựng từ các phần đã lưu của tác phẩm đã đăng: tên, lời, dị bản, ý nghĩa, nghĩa, chú thích; bản có dấu đã chuẩn hoá + không dấu; so trọn âm tiết, âm tiết cuối khớp phần đầu (giữ thuật toán `tim.php`, D-067).
- **Gõ sai:** danh sách âm tiết của chỉ mục; khi 0 hoặc ít kết quả, thử thay một âm tiết sai một chữ cái hoặc sai dấu, hiện "Có phải bạn tìm …?" (L-09: "ba co doi gai" → "Ba cô đội gạo lên chùa"); câu không có trong kho không ra bài lạc đề.
- Kết quả nhóm theo thể loại; khớp đúng một câu thì thẻ lớn đầu tiên; câu có cờ nghi vẫn hiện kèm cờ.
- Tìm không ra mà kho có câu (khớp bằng dấu vân tay hoặc chỉ mục kho riêng, không hiện lời kho): "Câu này đang chờ biên soạn" + nút "Mong có câu này".
- Dựng lại dòng chỉ mục của bài ngay khi lưu một phần; dựng lại toàn bộ theo lô bằng lệnh `wp phongdao tim --dung-lai`.

## 9. Việc nền

Cron thật của máy chủ gọi WP-Cron mỗi 5 phút (như MVP); mỗi loại việc nền là một sự kiện WP-Cron chạy theo lô, có khoá (transient) để không chạy chồng; con trỏ lô, lần chạy, lỗi gần nhất giữ trong tuỳ chọn `phongdao_viec_nen` — không bảng hàng đợi (D-094). Loại việc: lấy nguồn, chọn câu mới cho Hộp việc, chống trùng đêm, dựng chỉ mục, sinh ảnh chia sẻ, gọi Wikidata, gợi ý, nhập dữ liệu cũ. Nguồn & cài đặt có mục "Máy đang làm". Lịch sự với máy chủ nguồn như MVP (giãn cách, giới hạn mỗi lượt).

## 10. SEO, ảnh chia sẻ

- `noindex` mọi trang khi `blog_public` tắt (tới H5). Sau khi mở: lập chỉ mục tác phẩm L2+, tuyển tập đã công khai, trang thể loại, trang chủ đề / vùng / mô-típ có ≥ 3 tác phẩm L2, mục từ có lời giải do dự án viết hoặc nguồn mở; còn lại `noindex`.
- `<title>`: tác phẩm L2 "… — nghĩa và cách dùng"; tuyển tập "…: N câu có giải nghĩa" khi mọi câu có ý nghĩa. Mô tả lấy từ ý nghĩa.
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
| `tim.php` | `ChiMucTim`, `MienDuLieu\BoDau` | chuyển thuật toán, dựng chỉ mục từ các phần, thêm gõ sai |
| `do-luong.php`, `luot-doc.php`, `so-lieu.php` | `DoLuong`, `LuotDoc`, trang Số liệu tuần | chuyển; thêm `mong_co_cau` |
| `an-toan.php` | `HaTang\AnToan` | chuyển |
| `anh-chia-se.php` | `AnhChiaSe` | chuyển (cùng phông) |
| `seo.php` | `Seo` | viết lại theo cấp L2 |
| `the-tho.php`; nhận lục bát, tô dị bản, gắn nút chạm, chia đoạn trong `functions.php` của theme | `MienDuLieu\TheTho`, `ToChoKhac`, `ChiaDoan`, khối `loi`, `di-ban`, `doan` | chuyển thuật toán sang PHP thuần |
| `kho.php`, `nguon-tu-dong.php` | `KhoLuu\Kho`, `UngVien`, trang Nguồn & cài đặt | giữ bảng; **bỏ tạo bài Chờ duyệt hằng ngày**, thay bằng thẻ "Câu mới" trong Hộp việc |
| `gop-bai.php` | `GopBai` | viết lại trên các phần, giữ hành vi (dị bản, lượt đọc, cảm nghĩ, phân tích dồn sang, 301) |
| `goi-y.php`, `mo-tip.php`, `wikidata.php` | `GoiY`, `NhapNguon\Wikidata` | chuyển logic gợi ý; mỗi gợi ý là một việc trong `wp_cadao_viec` |
| `dong-dao.php` | phân loại `tro_choi`, `NhapNguon\Wikipedia`, khối `thuc-hanh`, Đọc cùng bé | chuyển |
| `nguoi-doc.php`, `phan-tich.php` | `NguoiDoc` | chuyển (ô bẫy, giới hạn, Turnstile) |
| `doc-tiep.php` | `DocTiep` | chuyển (mô-típ trước, rồi chủ đề, từ, địa danh, vùng) |
| `ban-do.php` | khối `ban-do` | chuyển (SVG tự lưu, không dịch vụ ngoài) |
| `da-luu.php`, `trich-dan.php` | khối `hanh-dong`, trang Đã lưu | chuyển; trích dẫn mang tên mới |
| `tu-dien.php`, `sua-muc-tu.php` | phân loại `muc_tu`, sửa trên trang mục từ | viết lại |
| `loai-bai.php`, `danh-sach.php`, `trang-chu.php`, `muc-kiem.php` | khai báo dữ liệu, khối, mẫu theme | viết lại |
| `ban-bien-tap.php`, `trang-quan-tri.php`, `trang-trung.php`, `hieu-nhanh.php` | `GiaoTiep\BienTap`, `XepViec`, `LuuPhan` | viết lại; giữ lệnh nạp nháp AI theo lô (`wp phongdao nhap-nhap`, nháp thành việc chờ) |
| `khoi-tho.php` | — | **bỏ** (không còn Gutenberg cho tác phẩm) |
| `tuc-ngu-thanh-ngu.php`, `site-cu.php` | `HaTang\ChuyenHuong` | **bỏ**, chỉ giữ bảng chuyển hướng 301 |
| `chinh-sach-du-lieu.php`, `trang-du-an.php` | — | **bỏ** mã sinh trang; nội dung trang tĩnh mang sang (`docs/29`) |

## 13. Kiểm thử, CI, lệnh

| Lệnh | Việc |
|---|---|
| `npm run kiem-tra` | nhất quán backlog, trạng thái (giữ) |
| `npm run test:v2` | PHP thuần (`wordpress/v2/tests/don-vi/`, vài giây) + Playground (`wordpress/v2/tests/`) |
| `npm run quet:v2` | axe WCAG 2.2 AA mọi mẫu thể loại, trang chủ, tuyển tập, tìm, từ điển, Hộp việc, trang bài ở chế độ sửa; ảnh chụp 390 px; đo ngân sách (trang bài ≤ 200 KB, JavaScript trang bài ≤ 21 KB, tuyển tập ≤ 300 KB); kiểm liên kết ra ngoài |
| `npm run xem-truoc:v2` | link Playground của nhánh với plugin + theme V2 và dữ liệu mẫu |
| `npm run dong-bo:giao-dien-v2` | chép CSS và `fit.js` từ bộ thiết kế `design/v2/source` sang theme (thêm chế độ tối theo cài đặt máy); `test:v2` báo lỗi khi theme lệch bộ thiết kế — sửa giao diện ở bộ thiết kế trước |
| `npm run e2e:v2` | Playwright trên bản xem trước: luồng đọc, tìm, đố vui, góp ý; Hộp việc (câu mới → đăng, nháp → duyệt, có thể trùng → gộp, người đọc gửi → áp dụng), sửa một phần trên trang bài, hoàn tác |

- Dữ liệu mẫu: chỉ chữ mẫu và các bài mẫu đã có trong kiểm thử MVP (D-050); không chép kho thô hay lời từ site thật vào repo (AGENTS.md, KHÔNG BAO GIỜ 14).
- SQL chạy được trên SQLite (Playground) và MySQL; tránh cú pháp chỉ MySQL có.
- CI: job `v2` (kiểm thử, quét) chạy khi chạm `wordpress/v2/`; job MVP giữ tới khi chuyển. Không có bản dựng JavaScript để so.
- AGENTS.md "LUÔN LUÔN": task V2 chạy `npm run kiem-tra`, `npm run test:v2`, `npm run quet:v2` trước khi kết thúc.

## 14. Rủi ro kỹ thuật

| Rủi ro | Cách giảm |
|---|---|
| Chuyển thuật toán làm đổi kết quả (chống trùng, trích) | Chuyển kèm kiểm thử của MVP; chạy cùng bộ ca trên hai bản |
| Lộ phần sửa cho khách qua bộ nhớ đệm | Phần sửa chỉ in khi người xem có quyền; trang người đăng nhập `private, no-store`; đệm transient chỉ cho khách; `quet:v2` kiểm HTML khách không có nút Sửa, không có `nguon_may` |
| Meta của bài sai hình dạng | Mọi lần ghi qua `LuuPhan` + `KiemPhan` (PHP thuần, có kiểm thử); lệnh `wp phongdao kiem-phan` rà toàn bộ |
| Khối động chậm | Đệm transient theo bài, xoá khi lưu một phần; `Cache-Control` cho khách khi kịch bản cập nhật biết xoá đệm; đo TTFB (MVP 0,40–0,67 s) |
| Playground khác MySQL | Tránh SQL riêng của MySQL; chạy thử công cụ nhập trên site thật ở chế độ chỉ đọc trước (`docs/29`) |
| Xây lâu, MVP cũ dần | Chỉ làm đúng phạm vi đợt 1–3; mọi thứ khác để đợt 5 |
