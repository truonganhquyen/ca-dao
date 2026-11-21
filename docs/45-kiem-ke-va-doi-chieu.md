# 45 · Kiểm kê hiện trạng và đối chiếu hai tài liệu đề xuất (20/11/2026)

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Căn cứ (D-127, 21/11/2026)** — kế hoạch đang dùng: [57](57-ke-hoach-chot.md); nơi khác nhau, 57 thắng. Nguyên trạng lúc lập: **Đề xuất, chờ người sáng lập duyệt.** Chưa phải quyết định D-xxx; chưa mở task trong `docs/22` |
| Đọc khi | Trước khi làm bất kỳ việc nào bắt nguồn từ hai tài liệu đề xuất ngày 20/11/2026 |
| Bộ tài liệu | **45 kiểm kê, đối chiếu (tệp này)** · [46 kiến trúc, khuyến nghị](46-kien-truc-va-khuyen-nghi.md) · [47 kế hoạch tổng](47-ke-hoach-tong-core-next.md) · [48 lộ trình, backlog](48-lo-trinh-va-backlog.md) · [49 quyết định, câu hỏi mở](49-quyet-dinh-va-cau-hoi-mo.md) |
| Căn cứ | (1) *PHONGDAO V2 — Đặc tả chiến lược nội dung & trải nghiệm đọc*, đề xuất 1.0, 20/11/2026 — gọi tắt **ĐT-ND**; (2) *PhongDao Next — Bổ sung nghiên cứu sản phẩm sau nâng cấp nội dung* v1.0, 20/11/2026 — gọi tắt **ĐT-NEXT**. Hai tài liệu do người sáng lập đưa vào phiên làm việc, không lưu trong kho mã (kho công khai). Mã `main` tại commit `d79c41a` (plugin 1.0.90, theme 1.0.20); `docs/00`–`44`; bốn trang site thật đọc bằng GET ngày 21/11 |

**Mức bằng chứng** dùng trong tài liệu: **M** — mã nguồn (ghi tệp); **TL** — tài liệu, quyết định trong repo; **S** — site thật, đọc bằng GET ngày 21/11/2026; **ĐT** — chỉ là quan sát của tài liệu đề xuất, chưa kiểm lại.

**Ký hiệu hiện trạng:** ✅ đã có · ◐ có một phần · ○ chưa có · ✕ đi ngược quyết định đã chốt.

---

## 1. Kết luận ngắn

1. **Phần trang đọc của ĐT-ND phần lớn đã có trên V2.** Trong 67 mục đối chiếu ở mục 3: 34 đã có, 17 có một phần, 12 chưa có, 4 đi ngược quyết định đã chốt (bảng tổng ở mục 3.10). Đơn vị tác phẩm, ba tầng đọc, năm mẫu theo thể loại, dòng nguồn ngay dưới lời, dị bản trong bài, ẩn mục trống, câu đố không lộ đáp án, chú thích chạm-là-hiện, tìm không dấu đều đang chạy.
2. **Khoảng trống lớn nhất là nội dung, không phải khuôn trang.** Kho có 16.285 bài đã đăng (15/11) nhưng chỉ khoảng 130 bài có Ý nghĩa (dưới 1%, D-117); mô-típ 8 nhóm; bản đồ 44 địa danh trong 29 bài (ĐT). Nguyên nhân gốc: một người biên soạn, dưới 10 giờ mỗi tuần (D-035). Mọi tính năng mới chỉ có giá trị khi có người viết hoặc chọn nội dung cho nó.
3. **Bốn khoảng trống cấu trúc đáng làm, đều nhỏ và dùng lại mô hình đang có:** (a) nối **bài viết** với tác phẩm — site đang có 46 bài viết (loại `post`) nhưng không bài nào nối với tác phẩm, đang `noindex` chờ hỏi quyền (D-126); (b) **loại mục từ** (địa danh, nhân vật, thêm điển tích) — `docs/26` ghi là có nhưng mã chưa có; (c) **sổ tay biên tập** phân ranh giới giữa các phần (Ý nghĩa, Hình ảnh trong bài, Chuyện quanh bài, Dị bản, Chú thích, Bối cảnh); (d) **máy nhắc chất lượng** cho đúng các lỗi ĐT-ND tìm thấy.
4. **Mô hình dữ liệu mới của ĐT-ND không nên làm.** Năm loại bài/bảng đề xuất (`editorial_article`, `knowledge_entry`, `source_record`, `work_version`, bảng `content_relation`) trùng với thứ đang có (`post`, mục từ, hồ sơ nguồn, dị bản, các quan hệ đã lưu) hoặc đi ngược quyết định đã chốt: D-094 (một bảng mới duy nhất), D-110 (hoãn loại nội dung mới và bảng quan hệ), D-114 (không mở mô hình dữ liệu mới), D-027 (quyền kiểm ở nguồn, không ở tác phẩm).
5. **ĐT-NEXT gần như toàn bộ là việc mới.** Nền có sẵn: mỗi phần của bài đã ghi người làm và nguồn (lọc được phần nào được phép dùng lại), ảnh chia sẻ dựng ở máy chủ, trình chiếu, Đã lưu, tuyển tập; việc xuất dữ liệu mở (T-163) và ghi âm có đồng ý (T-067) đã có chỗ trong backlog. Ba vướng lớn: chưa quyết pháp nhân, tài trợ (Q-008); chú thích chép từ Ca dao Mẹ chưa có thư đồng ý (T-034); lằn ranh không tài khoản, không theo dõi, không chatbot (`docs/00` mục 5, AGENTS.md).
6. **Có 8 chỗ tài liệu lệch mã** (mục 5): sửa tài liệu hoặc làm cho khớp trước khi giao việc mới, để agent sau không tưởng tính năng đã có.

## 2. Hiện trạng repository

### 2.1. Kiến trúc

| Mặt | Hiện trạng | Bằng chứng |
|---|---|---|
| Nền tảng | WordPress trên VPS; plugin `wordpress/v2/plugins/ca-dao` 1.0.90 (156 tệp PHP, riêng `src/` khoảng 27.500 dòng), theme khối `wordpress/v2/themes/ca-dao` 1.0.20; PHP ≥ 8.3 (máy chủ 8.5), MariaDB 10.11; máy chủ tự lấy bản mới của `main` mỗi 5 phút | M `ca-dao.php`, `style.css`; TL `docs/26` mục 2, 15 |
| Lớp mã | `MienDuLieu` (PHP thuần, kiểm thử được), `KhoLuu`, `DichVu`, `GiaoTiep` (Rest, Khoi, QuanTri, BienTap, Seo, Cli, TrangCoDinh), `HaTang` | M `src/` |
| Trang đọc | Khối động vẽ trên máy chủ; JavaScript thuần (`doc.js`, `fit.js`, `che.js`); trang bài ≤ 200 KB, JavaScript ≤ 21 KB; tắt JavaScript vẫn đọc; CSP; WCAG 2.2 AA | TL `docs/26` mục 6, 11; `docs/27` mục 9 |
| Quản trị | wp-admin → Phongdao: Hôm nay · Bài mới · Hộp việc · Bổ sung · Tác phẩm · Tuyển tập · Danh mục · Nguồn & cài đặt; sửa nhanh trên trang đọc | TL D-103, D-104, D-121 |
| Kiểm thử | `test:v2` (PHP thuần 292 · Playground 838 · chuyển đổi 55), `quet:v2` (0 lỗi axe), `e2e:v2` (81 bước); CI ba việc song song | TL `du-an/trang-thai.json` (21/11) |
| Đo lường | Lượt đọc + 5 sự kiện (`tim_khong_ket_qua`, `trich_dan_sao_chep`, `chu_giai_mo`, `chia_se`, `mong_co_cau`); không cookie, không IP | TL D-064, D-088 |

### 2.2. Mô hình dữ liệu (theo mã)

| Thành phần | Nội dung | Bằng chứng |
|---|---|---|
| Loại bài | `tac_pham` (`/<thể loại>/<tên>/`), `tuyen_tap`, `phan_tich` (bài cảm nhận của bạn đọc), `gop_y` (riêng tư), `ho_so_nguon` (ẩn), `page`; **`post` (bài viết WordPress): 46 bài đã đăng**, mẫu trang trơn, `noindex` trừ khi người biên soạn tích "Đã có quyền đăng" | M `HaTang/KhaiBao.php`, `themes/ca-dao/templates/single.html`, `GiaoTiep/Seo/Module.php`; S REST `X-WP-Total: 46` |
| Phân loại | `the_loai`, `chu_de`, `vung` (vùng lưu truyền), `mo_tip`, `hinh_thuc`, `muc_tu` (Từ điển), `tro_choi` | M `KhaiBao::PHAN_LOAI` |
| Phần của tác phẩm (23) | `loi` (+ `doi_chieu`), `di_ban` (`noi`, `nguoi_ke`, `nam`), `y_nghia`, `nghia_den`, `nghia_bong`, `dung_khi`, `hinh_anh`, `gan_nghia` (tục ngữ: gần / trái nghĩa), `giai_sach`, `chu_thich` (`muc_tu_id`), `chuyen_quanh`, `cau_hoi`, `dap_an`, `goi_y`, `doan`, `boi_canh` (`su_kien`, `nam`, `noi`, `nhan_vat`), `media` (ảnh, giọng máy, giọng người), `ban_co_biet`, `co_nghi`, `tho_tuc`, `sgk`, `dong_gop`, `nguon_may` (nội bộ). Mỗi phần hoặc mục mang `nguoi_lam` (`may_chep` · `nguoi_viet` · `ai_da_duyet` · `doc_gia_gop`) và `nguon` (`ho_so`, `trang`, `url`, `ngay_truy_cap`) | M `MienDuLieu/Phan.php` |
| Tuyển tập | `loi_dan` (người viết), `thong_tin` (loại: chủ đề, dịp, tình huống, bài học; người đọc), `muc` (tác phẩm, nhóm, ghi chú, thứ tự) | M `Phan.php` |
| Hồ sơ nguồn | `loai`, `url`, `tac_gia`, `nha_xuat_ban`, `nam`, `giay_phep`, `ghi_cong`, `xin_phep`, `chinh_sach_hien`, `chep_chu_thich`, `ghi_chu` | M `MienDuLieu/HoSoNguon.php` |
| Mục từ | lời giải (mô tả term), `_phongdao_loi_giai` (người làm, nguồn), `_phongdao_wikidata` (đã xác nhận; toạ độ → Bản đồ), `_phongdao_anh`, `_phongdao_khoa`. **Không có trường loại** (mục 5, L1) | M `GiaoTiep/Khoi/BanDo.php`; TL `docs/26` mục 4.2 |
| Trò chơi | `_phongdao_cach_choi`: số người, độ tuổi, đồ dùng, các bước, nguồn, giấy phép | TL `docs/26` mục 4.2 |
| Bảng | `wp_cadao_viec` (việc chờ và nhật ký, có phiên), `wp_cadao_tom_tat` (mỗi bài một dòng: thể loại, cấp, phần thiếu, cờ, lượt đọc 7 ngày, ưu tiên đọc), `wp_cadao_tim`, `wp_cadao_kho` · `van_tay` · `cap_am` · `trung` (kho, chống trùng), `wp_cadao_luot_doc` · `su_kien` | M `HaTang/CaiDat.php`, `DichVu/TomTat/TomTat.php` |
| Cấp chất lượng | L0–L3 nội bộ, máy tính (`CapChatLuong`); **phần riêng** (`PhanRieng`) quyết lập chỉ mục từ D-117 | M `MienDuLieu/CapChatLuong.php`, `PhanRieng.php` |

### 2.3. Trang người đọc

- **Đang chạy:** trang chủ (bản D-126), thanh thể loại, Tất cả `/kho/`, trang thể loại, chủ đề / vùng / mô-típ / hình thức đọc liền, trang tác phẩm năm mẫu (D-119), tuyển tập đọc liền và Trình chiếu cả bộ, Tìm (D-112, D-113), Từ điển và mục từ (D-125, D-126), Bản đồ, Mô-típ, Khám phá, Một câu ngẫu nhiên, Hay bị nhầm (hiện khi ≥ 5 câu), Đã lưu (trên máy người đọc), Góp (D-108, D-123), bài cảm nhận, bài viết.
- **Đã thiết kế, chưa làm:** Đố vui `/do-vui/`, Trò chơi có lọc `/tro-choi/`, Cho lớp học `/lop-hoc/`, khối Biên soạn (bút danh người biên soạn), "Sao chép cả bộ" ở tuyển tập (M: `TrangCoDinh::TRANG` không có `do-vui`, `lop-hoc`; `Khoi/TuyenTap.php` chỉ có Trình chiếu cả bộ).

### 2.4. Hiện trạng nội dung

| Chỉ số | Số | Nguồn |
|---|---|---|
| Tác phẩm đã đăng | 16.285 (15/11) · 16.303 đường dẫn riêng (19/11) · trang chủ ghi 16.450 (20/11) | TL D-117, `docs/02`; ĐT |
| Có Ý nghĩa | khoảng 130 (15/11, dưới 1%; chưa đo lại) | TL D-117 |
| Bài viết (`post`) | 46 đã đăng, phần lớn của tác giả khác, `noindex` chờ quyền | S; TL D-126 |
| Mô-típ · địa danh trên bản đồ | 8 nhóm · 44 địa danh trong 29 bài | ĐT |
| Việc người sáng lập đang chờ | thư Ca dao Mẹ; lô nháp Ý nghĩa đầu tiên; rút gọn Ý nghĩa dài; dọn chủ đề; thử làm sâu 20–30 bài; QA 10 bài mẫu; bật công tắc thô tục cho câu đố; khoá Turnstile | TL `docs/02` mục "Đang chờ người sáng lập" |

## 3. Đối chiếu ĐT-ND

### 3.1. Bảy quyết định tóm tắt (ĐT-ND mục 0)

| # | Đề xuất | Hiện trạng | Bằng chứng | Nhận xét |
|---|---|---|---|---|
| 0.1 | Tác phẩm là đơn vị trung tâm | ✅ | TL D-084, D-094; `docs/27` mục 1 | Giữ |
| 0.2 | Ba lớp Thưởng thức → Hiểu → Khảo cứu | ◐ | TL D-105 (Đọc · Hiểu thêm · Đọc thêm), D-119 | Thiếu tầng khảo cứu: bài viết chưa nối tác phẩm |
| 0.3 | Mở rộng các dạng nội dung liên kết | ◐ | mục 3.4 | Đa số đã có chỗ lưu; thiếu bài viết ↔ tác phẩm và loại mục từ |
| 0.4 | Quan hệ có tên, không chỉ "cùng chủ đề" | ◐ | M `DungTrang/DocTiep.php` (mỗi gợi ý có lý do), `Phan.php` (`gan_nghia`) | Mục 3.5 |
| 0.5 | Nguồn và độ chắc chắn lên đầu | ✅ | TL D-072, D-093, D-119 (dòng nguồn trong khung lời); nguồn từng phần; cờ "Có thể không phải dân gian" (D-105) | Dữ liệu đã tách nguồn của lời (`loi.doi_chieu`) với nguồn của chú giải (`nguon` từng phần) |
| 0.6 | Năm biến thể trình bày theo thể loại | ✅ | M `GiaoTiep/Khoi/TacPham.php`; TL `docs/27` mục 6 | Giữ |
| 0.7 | Chất lượng hơn làm giàu giả, không tự đăng bài AI | ✅ | TL `docs/00` mục 4; `docs/27` mục 1.7; D-117 (3) | Giữ |

### 3.2. Quan sát trên các trang mẫu (ĐT-ND mục 1.1)

| Trang | ĐT-ND nói | Kiểm lại | Nguyên nhân | Hướng xử lý |
|---|---|---|---|---|
| `/ca-dao/tho-tay-ma-ngat-cong-ngo/` | "Chuyện quanh bài" chứa ghi chú cách chép khác, trùng chức năng dị bản | S: đúng | Chú thích chung không gắn từ (máy chép từ nguồn) được hiện ở hàng "Chuyện quanh bài" — đúng thiết kế D-119 (7), M `TacPham.php` (nhánh `ct_chung`) | Máy nhắc + đổi tên hàng cho chú thích chung (49, QĐ-4) |
| cùng trang | "Hình ảnh trong bài" lặp một phần giải nghĩa | ĐT | Nội dung | Sổ tay biên tập + máy nhắc trùng chữ |
| `/ca-dao/o-day-co-nuoc-song-huong/` | Giải nghĩa nói đoạn thơ thuộc Nguyễn Bính: phải gắn cờ xét lại | S: chú thích nói đoạn này từng được Nguyễn Bính đưa vào một bài thơ của ông; **chiều vay mượn chưa rõ** | Công cụ cờ "Có thể không phải dân gian" kèm "Vì sao?" đã có (D-105) | Người biên soạn xét (H2); máy chỉ nhắc, không tự gắn |
| `/tuc-ngu/cau-doi-cu-phan-chu-nghe-dan/` | Nhân vật, bối cảnh dồn vào mục Nghĩa | ĐT | Phần Nguồn gốc / Chuyện quanh bài đã có (D-119, T-215) | Việc nội dung |
| `/dong-dao/nhong-nhong-nhong-nhong/` | Thiếu nhịp đọc, tình huống chơi | ĐT | Khối Cách chơi lấy từ trò chơi đã có (D-119); bài chưa có dữ liệu | Việc nội dung, cần nguồn |
| `/dong-dao/nha-toi-co-bay-anh-em/` | Cần thể loại phụ / nhãn lối | ĐT | `hinh_thuc` có "hình thức bổ sung: người gán" (TL `docs/26` mục 4.2) | Không cần mô hình mới |
| `/cau-do/cai-gi-trong-trang-ngoai-xanh/` | Giữ hiện đáp án và giải thích | ✅ | TL D-087, D-105, D-114 | Giữ |
| `/ve/ve-co-giang/` | Cần mục lục đoạn, chú thích theo đoạn, từ điển nhân vật, trang khảo cứu | ◐ | Đoạn, mục lục, chú thích theo đoạn đã có (D-114, D-119); `boi_canh.nhan_vat` có trong schema nhưng không có ô nhập (M `BienTap/TamSua.php`, nhánh bối cảnh chỉ giữ giá trị cũ); mục từ chưa có loại nhân vật | Loại mục từ + bài viết nối tác phẩm |
| `/ve/ve-cau-doumer/` | "Ý nghĩa" xuất hiện trước lời, cần chuẩn hoá | S: đúng | **Cố ý:** D-119 (5) đặt Bối cảnh và Ý nghĩa của vè trước lời dài; M `TacPham.php` (`$y_truoc`) | ✕ với đề xuất. Khuyến nghị giữ D-119, rút gọn Ý nghĩa dài (49, QĐ-5) |
| `/tuyen-tap/` | Bộ "Top 10…" có 11 câu | S: đúng | Nội dung | Người sửa; máy nhắc tên có số khác số câu |
| `/phuong-phap/` | Tách nguồn của bản sưu tầm với nguồn của chú giải | ◐ | Dữ liệu đã tách (mục 3.1, 0.5); trang tĩnh do người sáng lập viết | Người sáng lập sửa câu chữ trang khi thấy cần |

### 3.3. Bảy lỗ hổng lớn (ĐT-ND mục 1.3)

| # | Lỗ hổng ĐT-ND nêu | Đánh giá | Căn cứ |
|---|---|---|---|
| 1 | Phân loại chưa sâu, độ phủ gắn nhãn thấp | **Đúng — về nội dung** | Sáu chiều phân loại và máy gợi ý chủ đề / vùng đã có (Bổ sung); thiếu người duyệt, không thiếu chỗ lưu |
| 2 | Khung biên tập thiếu ranh giới; nguồn chú thích không truy nguyên được | **Đúng một phần** | Quy tắc viết Ý nghĩa có trong mã (M `MienDuLieu/KiemTrinhBay.php`: ≤ 60 chữ, không mở đầu nhắc lại câu, không nêu tác giả) và hướng dẫn lô nháp; thiếu ranh giới giữa các phần. Việc người đọc không thấy nguồn chú thích chép từ Ca dao Mẹ là **chính sách có chủ đích** (D-068, D-078, D-117 (2)) — đổi là H3 |
| 3 | Năm thể loại vẫn chung nhiều yếu tố | **Phần lớn không còn đúng** | Năm mẫu riêng (D-105, D-119) |
| 4 | Liên kết nội bộ thiên về cùng thể loại / chủ đề / vùng | **Đúng một phần** | Đọc tiếp đã bỏ lấp "cùng thể loại", ưu tiên cùng tuyển tập → mô-típ → chủ đề → mục từ → vùng (D-119 (8)); thiếu quan hệ do người chọn: đối đáp, điển tích, bài viết |
| 5 | Chưa thấy hệ thống bài nghiên cứu | **Đúng** | 46 bài viết dùng mẫu trơn, không nối tác phẩm, `noindex` chờ quyền (D-126) |
| 6 | Địa danh lịch sử ghi "nay là…" dễ lỗi thời | **Đúng, và cấp thiết hơn** sau đợt sắp xếp đơn vị hành chính năm 2025 (bỏ cấp huyện, giảm số tỉnh) | Chưa có quy tắc nhắc; chú thích chép từ nguồn thường ghi theo địa giới cũ |
| 7 | Chưa kiểm responsive | **Không đúng với repo** | `quet:v2` kiểm 320 / 390 px, axe WCAG 2.2 AA mọi mẫu, ngân sách trang; `e2e:v2` ở 390 px. Còn thiếu: phóng 200%, thiết bị thật |

Bảng "xu hướng ngành" (ĐT-ND mục 2) không đòi thay đổi riêng: kể chuyện biên tập → bài viết nối tác phẩm (mục 3.4); dữ liệu liên kết → Wikidata do người xác nhận đã có; tư liệu đa định dạng → phần `media` có, ghi âm cần hồ sơ đồng ý; cộng đồng tham gia → Góp một câu, "Ở quê nói khác" đã có; truy nguyên dị bản → đối chiếu sách (T-158); nội dung hữu ích → D-117 chỉ lập chỉ mục bài có phần riêng; WCAG 2.2 → `quet:v2`.

### 3.4. Thực thể (ĐT-ND mục 3.1, 6.1, 6.2)

| Thực thể ĐT-ND | Đang có | Còn thiếu | Khuyến nghị (chi tiết ở 46) |
|---|---|---|---|
| A · Tác phẩm (`Work`), thể loại chính / phụ, lý do, độ chắc | `tac_pham` + `the_loai` + `hinh_thuc` + cờ `co_nghi` (lý do) | Thể loại phụ có ghi lý do | ◐ Dùng `hinh_thuc` và cờ; không thêm trường |
| B · Bản lời (`TextWitness`) | `loi` (Bản tham chiếu, đối chiếu sách) + `di_ban` (nơi, người kể, năm, nguồn từng mục) + lịch sử ở `wp_cadao_viec` | Người ghi, ngày ghi, chữ viết | ◐ Để sau; chỉ thêm khi có tư liệu điền dã thật |
| C · Bài biên tập (`EditorialArticle`) | `post` (46 bài), `phan_tich` (bạn đọc), `chuyen_quanh` (chuyện ngắn trong bài) | Nối bài viết ↔ tác phẩm; nhóm bài viết; người viết, người biên tập trên trang | ◐ Mở rộng `post`, không tạo loại bài mới |
| D · Hồ sơ tri thức (`KnowledgeEntry`) | `muc_tu` (lời giải, nguồn, Wikidata, ảnh, bản đồ), `mo_tip`, `tro_choi`, nhóm Lối hát trong chủ đề | Loại mục từ; điển tích | ◐ Thêm loại cho mục từ |
| E · Nguồn (`SourceRecord`) | `ho_so_nguon` — đủ các trường ĐT-ND nêu trừ mã lưu trữ và ngày kiểm liên kết; trang / vị trí nằm ở từng lần dùng (`nguon.trang`) | Ngày kiểm liên kết | ✅ Giữ; để sau |
| F · Hiện vật số (`MediaRecord`) | Tệp đính kèm + phần `media` + ghi công ảnh + Commons | Giấy phép có cấu trúc; hồ sơ đồng ý ghi âm | ◐ Để sau (lằn ranh 3) |
| G · Tuyển tập (`CuratedCollection`) | `tuyen_tap`: lời dẫn người viết, ≥ 8 câu đã đăng, nhóm, ghi chú, thứ tự (D-116) | Người tuyển, ngày cập nhật hiện trên trang | ◐ Khối Biên soạn (đã thiết kế, T-185) |
| H · Bảng quan hệ (`ContentRelation`) | Quan hệ nằm đúng chỗ của nó: `di_ban`, `gan_nghia`, `tuyen_tap.muc`, phân loại, `chu_thich.muc_tu_id` | — | ✕ D-110 đã hoãn "bảng quan hệ"; không làm |

ĐT-ND mục 3.2 (không mở thể loại mới ồ ạt) **khớp** `docs/00` mục 5 và cổng thể loại D-091. Mục 3.3 (chú thích ngắn → hồ sơ → bài nghiên cứu) **khớp** cách chia Chú thích → Chuyện quanh bài → mục từ → bài viết.

### 3.5. Quan hệ (ĐT-ND mục 4)

| Quan hệ ĐT-ND | Hiện trạng | Chỗ lưu / ghi chú |
|---|---|---|
| `variant_of` | ✅ trong bài · ○ giữa hai bài | `di_ban`; gộp hai tác phẩm đã đăng chưa làm, thuộc H6 (`docs/28` mục 3.4) |
| `response_to` (đối đáp) | ○ | Chưa có chỗ lưu; `gan_nghia` chỉ cho tục ngữ |
| `shares_motif` | ✅ | `mo_tip`, nhãn mô-típ ở trang bài, Đọc tiếp |
| `explained_by` (bài nghiên cứu về tác phẩm) | ○ | Bài viết chưa nối |
| `alludes_to` (điển tích) | ◐ | Chữ trong Chuyện quanh bài; chưa có mục từ điển tích |
| `mentions_person` | ◐ | `boi_canh.nhan_vat` chỉ có trong schema; chưa có loại mục từ nhân vật |
| `mentions_place` | ✅ | Mục từ có Wikidata đã xác nhận → Bản đồ ghi "được nhắc đến" |
| `origin_recorded_at` | ✅ | `vung` là vùng lưu truyền có căn cứ; `di_ban.noi` |
| `performed_with` (trò chơi) | ✅ | `tro_choi`, khối Cách chơi, Đọc cùng bé |
| `source_attests` | ✅ | `loi.doi_chieu` → "Đã đối chiếu: <sách>, tr. N" |
| `source_supports_claim` | ✅ | `nguon` của từng phần, từng mục |
| `reading_related` (đọc thêm ngoài) | ○ | Liên kết ra ngoài chỉ tới tên miền cho phép (`docs/27` mục 9.4) |
| `collection_contains` | ✅ | `tuyen_tap.muc` có thứ tự, nhóm, ghi chú |

Thứ tự Đọc tiếp: D-119 là cùng tuyển tập → mô-típ → chủ đề → mục từ → vùng, tối đa 3, không lấp; ĐT-ND là dị bản → đối đáp → mô-típ → điển tích → chủ đề hẹp → vùng → thể loại. Hai bên khác nhau chủ yếu ở hai quan hệ chưa có (đối đáp, điển tích); ĐT-ND còn lấp "cùng thể loại" mà D-119 đã bỏ.

### 3.6. Mạch trải nghiệm (ĐT-ND mục 5)

| Bước ĐT-ND | Hiện trạng | Bằng chứng |
|---|---|---|
| 1 · Đường dẫn vị trí, thể loại, tiêu đề | ✅ | Bài ngắn lấy lời làm tiêu đề (D-119 (1)) |
| 2 · Nguyên văn, giữ xuống dòng | ✅ | Mỗi câu một dòng, thụt câu lục (D-092, D-093) |
| 3 · Trạng thái nguồn ngắn, công cụ phụ | ✅ | Dòng nguồn ở chân khung lời; thanh công cụ Trích dẫn · Sao chép · Lưu · Chia sẻ; nút Nghe giọng máy (D-095) |
| 4 · Hiểu lời | ✅ | Ý nghĩa · khối Nghĩa tục ngữ · Cách chơi · Giải thích đáp án (D-105, D-118, D-119) |
| 5 · Cái hay trong lời | ◐ | Hình ảnh trong bài cho ca dao, đồng dao (D-105) |
| 6 · Giải chú | ✅ | Chú thích kiểu sách, theo đoạn với bài dài (D-114, D-119) |
| 7 · Khám phá tác phẩm (bài nghiên cứu, ảnh, âm thanh) | ○ | Ảnh minh hoạ có (T-196); chưa có bài viết, âm thanh thật |
| 8 · Dị bản và nguồn | ✅ | Hàng Dị bản; dòng nguồn mở ra bảng nguồn |
| 9 · Đọc tiếp có lý do | ✅ | D-119 (8) |
| 10 · Góc bạn đọc, góp tư liệu | ✅ | Hiểu thêm chỉ khi có bài đã duyệt; một lời mời góp (D-119 (6)) |
| Ngoại lệ: bài 2 câu không có hộp trống | ✅ | Trang mẫu ngắn đi 40–57% (`docs/22`, T-214) |
| Biến thể thể loại (mục 5.2) | ✅ · ✕ một ý | Năm mẫu có đủ; "chip thể loại riêng cho thành ngữ" ✕ D-079 (gộp hẳn) |

**Năm luồng người đọc (mục 5.3):** phổ thông ✅; học sinh ✅ (tuyển tập, Ý nghĩa, trích dẫn); giáo viên, phụ huynh ◐ (Trình chiếu, Đọc cùng bé, Cách chơi có; Cho lớp học, phiếu in, Đố vui, Trò chơi có lọc chưa); người nghiên cứu ◐ (dị bản, nguồn có; nhân vật, khảo cứu chưa); người đóng góp ✅ (Góp một câu, Ở quê nói khác, ghi công theo đồng ý — D-123, D-124).

### 3.7. Trạng thái chất lượng và quy trình (ĐT-ND mục 6.3, 7)

| Đề xuất | Hiện trạng | Ghi chú |
|---|---|---|
| Trạng thái nguồn văn bản | ✅ | Máy tính từ dữ liệu: "Bản lưu truyền phổ biến" hoặc "Đã đối chiếu: <sách>, tr. N" (D-072, D-093) |
| Trạng thái kiểm nghĩa | ◐ | Nháp → duyệt, dấu AI, người duyệt, ngày duyệt; **không** có "chuyên gia thẩm định" (D-013: bằng chứng trước, uy tín sau) |
| Trạng thái loại tác phẩm | ◐ | Cờ "Có thể không phải dân gian" + lý do |
| Trạng thái quyền sử dụng | ✕ ở cấp tác phẩm · ✅ ở nguồn và ảnh | D-027: kiểm ở nguồn, tư liệu, ghi âm; không thêm `rights_status` cho tác phẩm |
| AI hỗ trợ: trường nào, ai duyệt, ngày | ✅ | `ai`, `duyet_boi`, `duyet_luc`; máy không đặt (`docs/28` mục 6) |
| L0–L3 | ✅ | `CapChatLuong`; L3 = đã đối chiếu sách, có bối cảnh hoặc media có quyền |
| Quy trình 7 bước | ✅ phân tán · ○ sổ tay gom lại | Bài mới → chống trùng → Hộp việc → Bổ sung → Cần sửa → Lịch sử → Góp; hướng dẫn rời ở `docs/huong-dan/` |
| Chính sách chú giải (mục 7.3) | ✅ · ○ một ý | Không đoán (AGENTS.md); không làm sạch từ tục (D-060, D-111); cờ có lý do (D-105). Chưa có quy ước ghi địa danh theo thời điểm |

### 3.8. Điều hướng, hiển thị, tìm (ĐT-ND mục 8)

| Đề xuất | Hiện trạng | Ghi chú |
|---|---|---|
| Menu Tác phẩm · Khám phá · Tuyển tập · Bài viết · Về thư viện | ◐ | Đang là Khám phá ▾ · Tra cứu ▾ + thanh thể loại (D-087, D-093); thêm "Bài viết" là H4 |
| Bề rộng đọc 620–720 px, chữ thơ rõ | ✅ | 38em; trang tác phẩm 1040 px gồm cột công cụ |
| Chú thích chạm / focus, không chỉ rê chuột, đọc được khi tắt JavaScript | ✅ | D-119 (2); `docs/27` mục 9.3 |
| Bài dài: mục lục đoạn, chú thích theo đoạn | ✅ | D-114, D-119 |
| Bài dài: thanh chỉ vị trí đọc | ○ | Có "nhớ chỗ đọc"; không có thanh tiến độ |
| Dị bản hai cột trên máy tính | ○ | T-063 hoãn; hiện có tô chỗ khác (T-083) |
| Ảnh có giá trị, ghi công, không ảnh AI | ✅ | D-061 (C), T-196 |
| Âm thanh: nhãn giọng máy, không tự phát | ✅ | D-063, D-095 |
| Câu đố không lộ đáp án, kể cả ảnh chia sẻ | ✅ | D-093 (7), D-114 |
| Tách nội dung biên tập với đóng góp | ✅ | Hiểu thêm tách Ý nghĩa; "Đóng góp bởi … · chưa đối chiếu" (D-124) |
| Khối Khám phá tác phẩm | ○ | Chưa có bài viết nối |
| Khối Tư liệu về bài | ✅ | Dòng nguồn ở chân khung lời |
| Khối Tài liệu đọc thêm (ngoài) | ○ | — |
| Tìm có dấu / không dấu, gõ sai, có dị bản, lọc thể loại, đo tìm không ra | ✅ | D-067, D-112, D-113; `tim_khong_ket_qua` |
| Tìm theo tên cũ địa danh; lọc Tìm theo vùng, mô-típ, "đã đối chiếu" | ○ | Danh sách có chip lọc; Tìm chỉ lọc Cách khớp và Thể loại (D-113) |

### 3.9. Lộ trình, chỉ số, ca kiểm (ĐT-ND mục 10–13)

| Đề xuất | Hiện trạng | Ghi chú |
|---|---|---|
| P0 · Kiểm kê CMS theo thể loại | ◐ | Số liệu có số bài theo thể loại, theo cấp, ngoại lệ, hồ sơ nguồn chưa rõ; Bổ sung có số bài sẽ lên Google. Chưa có bảng phần riêng × thể loại |
| P0 · 20 bài mẫu (4 mỗi thể loại) | ◐ trùng việc đang chờ | Thử làm sâu 20–30 bài (T-180) và QA 10 bài thuộc 5 thể loại (T-198) đang chờ người sáng lập — gộp, không tạo việc mới |
| P1 · CPT bài biên tập; hồ sơ từ, điển tích; quan hệ | ◐ | Mục 3.4, 3.5 |
| P1 · 30–50 bài chuyên sâu, 5 tuyển tập mẫu | — việc nội dung | Màn Tuyển tập có (D-116) |
| P2 · Ghi âm, chơi đồng dao, bản đồ, đối chiếu sách | ◐ | T-067 (hoãn), Cách chơi + Đọc cùng bé có, Bản đồ có, T-158 sẵn sàng |
| P3 · Thể loại mới, tìm ngữ nghĩa, IIIF, API | ◐ trong backlog | T-161, T-162 qua cổng D-091; tìm ngữ nghĩa có cổng D-015 (T-068 hoãn) |
| Chỉ số (mục 12) | ◐ | North Star và 5 sự kiện có (`docs/00` mục 8); "% chú thích có chứng cứ", "% dị bản ghi nơi / người / năm" tính được từ dữ liệu, chưa hiện |
| 10 ca kiểm bắt buộc | ✅ 8 · ◐ 1 · ○ 1 | Có ca: bài ngắn, dị bản, đồng dao cách chơi, câu đố không lộ, vè dài, nguồn sách số trang, liên kết ngoài hỏng (kiểm trong `quet:v2`), bàn phím / trình đọc màn hình, SEO canonical; ◐ tục ngữ nhiều cách hiểu (Ý nghĩa "Cách hiểu 1 / 2", D-105); ○ phóng 200% |
| Những việc không nên làm (mục 13) | ✅ khớp | Trùng tinh thần `docs/00` mục 5 và AGENTS.md |

### 3.10. Bảng tổng ĐT-ND

| Mục | ✅ | ◐ | ○ | ✕ |
|---|---|---|---|---|
| 3.1 Bảy quyết định | 4 | 3 | 0 | 0 |
| 3.4 Thực thể | 1 | 6 | 0 | 1 |
| 3.5 Quan hệ | 7 | 2 | 4 | 0 |
| 3.6 Mạch trải nghiệm | 9 | 1 | 1 | 1 |
| 3.7 Trạng thái, quy trình | 3 | 2 | 2 | 1 |
| 3.8 Điều hướng, hiển thị, tìm | 9 | 1 | 5 | 0 |
| 3.2 Trang mẫu (dòng có ký hiệu) | 1 | 2 | 0 | 1 |
| **Cộng (67 mục)** | **34** | **17** | **12** | **4** |

Ghi chú cách đếm: dòng có hai trạng thái (vd. "✅ trong bài · ○ giữa hai bài") đếm theo trạng thái thấp hơn; không đếm mục 3.3 (đánh giá), 3.9 (lộ trình) và các dòng "việc nội dung" ở 3.2. Kết luận mục 1 dùng các số này.

## 4. Đối chiếu ĐT-NEXT

### 4.1. Hiện trạng ĐT-NEXT tự ghi (mục 02)

Đúng với repo: thư viện phi lợi nhuận, không quảng cáo, không theo dõi (`docs/00`); địa danh được nhắc đến không phải nơi lưu truyền (`docs/27` mục 5.3, 7.5); mô-típ còn mỏng. "Chưa xác minh creator studio, lưu lời kể gia đình, API" — mã **không có** các phần này.

### 4.2. Cổng chất lượng G01–G08

| Mã | Điều kiện | Hiện trạng |
|---|---|---|
| G01 | ≥ 150 tác phẩm biên tập, đủ 5 thể loại | ◐ khoảng 130 bài có Ý nghĩa, chưa tách thể loại; `docs/00` mục 8 đặt đích ≥ 300 bài L2 |
| G02 | Định danh bền, nguyên văn, loại hình, tình trạng nguồn | ✅ ID và đường dẫn bền (đổi có 301), lời có lịch sử, dòng nguồn máy tính |
| G03 | Quyền ảnh / âm thanh / video được ghi | ◐ ghi công ảnh, Commons; chưa có trạng thái quyền có cấu trúc |
| G04 | Đáp án câu đố, độ tuổi được duyệt | ◐ đáp án có; độ tuổi chỉ ở trò chơi; câu đố tục giảng thanh chưa đánh dấu (công tắc thô tục chờ người sáng lập) |
| G05 | Địa danh của tuyến tham quan được xác minh thực địa | ○ |
| G06 | Sửa / thu hồi đồng bộ xuống sản phẩm dùng lại | ○ chưa có kênh dùng lại |
| G07 | Điều khoản đóng góp, riêng tư, gỡ | ✅ trang Quyền riêng tư, Chính sách thu thập dữ liệu, đồng ý CC BY 4.0 trên form; ◐ quy trình gỡ cho sản phẩm dùng lại |
| G08 | Kiểm soát truy cập, đệm, giới hạn tần suất, nhật ký | ◐ REST có quyền và nonce, giới hạn gửi mỗi giờ, nhật ký bảng việc; chưa có khoá API |

### 4.3. Mười bốn ý tưởng F01–F14

| ID | Ý tưởng | Nền đang có | Vướng chính |
|---|---|---|---|
| F01 | Tuyến di sản (QR, nhiệm vụ) | Mục từ có toạ độ Wikidata, Bản đồ; trang tác phẩm, tuyển tập làm đích QR được | Quan hệ "được nhắc đến" không phải nơi lưu truyền; cần đối tác và khảo sát thực địa |
| F02 | Triển lãm tương tác | Trình chiếu rộng 1240 px cho máy chiếu (D-093) | Phần cứng, đối tác, vận hành tại chỗ |
| F03 | Creator Studio | Đã lưu, Trình chiếu cả bộ, ảnh chia sẻ 3 khổ × 3 nền, T-062 (bộ bài từ Đã lưu, in cả bộ — hoãn), T-159 (phiếu in) | Tài khoản tổ chức, thuê bao, watermark trái "không tài khoản người đọc" (`docs/00` mục 5) và chờ Q-008 |
| F04 | Nhúng widget | Khối Câu hôm nay; Đố vui (chưa làm) | Đo lượt hiển thị là sự kiện mới cần D-xxx (AGENTS.md, KHÔNG BAO GIỜ 8); chú thích chép không được ra ngoài |
| F05 | Ký ức gia đình (ghi âm) | Phần `media` loại `giong_nguoi`; thiết kế đồng ý ở `docs/luu-tru/41-ghi-am-va-dong-y.md`; T-067 | Lằn ranh 3; giọng trẻ em; site không có tài khoản để lưu riêng tư |
| F06 | Sưu tầm cộng đồng | Góp một câu (D-123), Gửi số trang, T-134 người soát lời (hoãn) | "Hai người duyệt" — hiện chỉ có một người |
| F07 | Truyện tương tác theo mô-típ | — | Nội dung phóng tác, gánh viết lớn, lệch trọng tâm thư viện |
| F08 | Nghe lời, giọng vùng | Nút Nghe giọng máy (D-095); T-160 giọng sinh sẵn (D-063) | Không giả giọng vùng hay người thật |
| F09 | Đêm văn hoá gia đình | Đọc cùng bé, Cách chơi; Đố vui (chưa) | Cần bộ nội dung đã duyệt |
| F10 | Microsite đối tác | — | Dịch vụ theo dự án, một người vận hành |
| F11 | API tri thức | T-163 xuất dữ liệu mở, T-066 DOI; REST `phongdao/v1` chỉ phục vụ trang | Giấy phép từng phần |
| F12 | Trợ lý nghiên cứu AI | — | ✕ `docs/00` mục 5: chatbot, AI sinh nội dung công khai — "không bao giờ" |
| F13 | Bản đồ tiếng nói | Vùng lưu truyền, `di_ban.noi`, Bản đồ | Dữ liệu thưa |
| F14 | Cuộc thi sáng tạo | — | Kiểm duyệt, bảo vệ trẻ vị thành niên, quyền bài dự thi |

### 4.4. Kiến trúc ĐT-NEXT (mục 12)

- **WordPress là nguồn sự thật** (12.1): khớp.
- **Lớp phân phối chỉ xuất bản ghi đã duyệt** (12.1, 12.3): nền đã có — mỗi phần có `nguoi_lam` và `nguon`, mỗi hồ sơ nguồn có `giay_phep`, `xin_phep`. Lọc được bằng máy; **không cần** cờ `approved_for_reuse` nhập tay (cách làm giống nhãn mức kiểm do máy tính, D-041).
- **Đối tượng logic** (12.2): `work` ✅, `work_variant` ✅ (`di_ban`), `source` ✅, `place` ◐ (mục từ + Wikidata, chưa có loại), `relation` ◐, `media_asset` ◐, `contribution` ✅ (`gop_y`, `dong_gop`, bảng việc), `experience` / `experience_step` / `partner_license` ○.
- **API** (12.4): ○.

### 4.5. Thương mại và pháp lý

- **Q-008** (tài trợ, pháp nhân bảo trợ) còn mở: mọi sản phẩm có thu tiền phải chờ.
- **Giấy phép:** phần dự án tự viết là CC BY 4.0 (D-037) — người khác được dùng thương mại; không bán được "độc quyền" phần này (ĐT-NEXT mục 17.2 đúng).
- **Chú thích chép từ Ca dao Mẹ** (D-055, D-068, D-117 (2)): chưa có thư đồng ý (T-034) → không đưa vào bất kỳ sản phẩm dùng lại hay xuất bản ngoài site.
- **Lời dân gian** là di sản, không thuộc dự án (D-037); khi xuất vẫn kèm đường dẫn bài và dòng nguồn.
- Điều luật ĐT-NEXT dẫn ([S13]) cần tư vấn pháp lý; tài liệu này không kết luận.

## 5. Lệch giữa tài liệu và mã

| # | Tài liệu ghi | Mã thực tế | Đề xuất |
|---|---|---|---|
| L1 | `docs/26` mục 4.2: mục từ có term meta `loai` = `tu` / `dia_danh` / `nhan_vat` | Không có; Bản đồ nhận địa danh qua toạ độ Wikidata (M `Khoi/BanDo.php`) | Làm theo 49 · QĐ-2, hoặc ghi "chưa làm" |
| L2 | `docs/27` mục 7.4: Từ điển lọc từ cổ, Hán Việt, phương ngữ, địa danh, vùng | `Khoi/TuDien.php` chỉ có tìm, chữ cái, phân trang | Như trên |
| L3 | `docs/26` mục 4.3: `boi_canh.nhan_vat` là danh sách mục từ | Có trong schema; tấm sửa chỉ giữ giá trị cũ, không có ô nhập (M `BienTap/TamSua.php`) | Lấy nhân vật từ chú thích đã nối mục từ loại nhân vật (46, KT-4); giữ trường, không xoá |
| L4 | `docs/27` mục 7.1: tuyển tập có "Sao chép cả bộ", "In" | Chỉ có "Trình chiếu cả bộ" (M `Khoi/TuyenTap.php`); in dựa vào CSS in chung | Làm "Sao chép cả bộ" (việc nhỏ) hoặc sửa tài liệu |
| L5 | `docs/22` T-185 còn ghi "nạp nháp ý nghĩa theo lô", "sửa tuyển tập" | Đã làm ở T-210 (D-117) và T-208 (D-116) | Đối soát lại phạm vi T-185 trước khi làm |
| L6 | `docs/26` mục 3 liệt kê lớp `ChinhSachHien`, `DongMucKiem` trong `MienDuLieu` | Không có tệp; chính sách hiện nằm ở `HoSoNguon` | Sửa danh sách lớp |
| L7 | `docs/22` đầu tệp ghi phiên bản 2.6 · 05/11/2026 | Bảng đã cập nhật tới T-228 | Sửa dòng phiên bản khi chạm `docs/22` lần tới |
| L8 | `docs/26` mục 4.3: tuyển tập có meta `_phongdao_loai`, `_phongdao_doi_tuong` | Lưu trong phần `thong_tin` (`loai`, `doi_tuong`) cùng `loi_dan`, `muc` (M `MienDuLieu/Phan.php`) | Sửa tên khoá trong tài liệu |

## 6. Tổng hợp khoảng trống và đánh giá

Thang 1–5 (5 = cao nhất; "Khả thi" 5 = dễ nhất). Công sức: **S** vài ngày agent · **M** một đến hai tuần · **L** nhiều tuần hoặc phụ thuộc nội dung. Xếp hạng dùng ở 48.

| # | Khoảng trống | Loại | Giá trị | Khả thi | Công sức | Rủi ro | Dùng lại cho | Gate | Xếp |
|---|---|---|---|---|---|---|---|---|---|
| G1 | Độ phủ phần riêng (Ý nghĩa…) dưới 1% | Nội dung | 5 | 3 | L, liên tục | Thấp | Google, mọi kênh sau | H2 | **Now** |
| G2 | Ranh giới các phần biên tập chưa rõ | Tài liệu | 4 | 5 | S | Thấp | Nháp AI, người góp | — | **Now** |
| G3 | Lỗi nội dung ĐT-ND tìm thấy (chú thích chung, tên tuyển tập, "nay là…") | Công cụ + nội dung | 4 | 5 | S | Thấp | Bổ sung → Cần sửa | — | **Now** |
| G4 | Lệch tài liệu – mã L1–L8 | Tài liệu | 3 | 5 | S | Thấp | Agent sau | — | **Now** |
| G5 | Bảng độ phủ theo thể loại | Công cụ | 3 | 5 | S | Thấp | Chỉ số, chọn bài mẫu | — | **Now** |
| G6 | Loại mục từ (địa danh, nhân vật, điển tích) | Cấu trúc | 4 | 4 | M | Thấp | Vè, Đọc tiếp, Bản đồ, tuyến di sản | H1 | Next |
| G7 | Bài viết nối tác phẩm | Cấu trúc + UX | 4 | 4 | M | TB (quyền bài viết) | Khám phá, khảo cứu | H1 + H4 | Next |
| G8 | Đố vui, Trò chơi có lọc | UX | 4 | 4 | M | TB (câu đố thô tục) | Nhúng, học liệu | thiết kế đã duyệt | Next |
| G9 | Khối Biên soạn (bút danh) | UX | 3 | 5 | S | Thấp | Tuyển tập, bài viết | đã duyệt (D-080) | Next |
| G10 | Sao chép cả bộ | UX | 3 | 5 | S | Thấp | Giáo viên | đã duyệt (D-093) | Next |
| G11 | Cho lớp học, phiếu in | UX + nội dung SGK | 4 | 3 | L | TB | Creator Studio | H2 (SGK chỉ khi đã thấy sách) | Later |
| G12 | Đối chiếu sách Wikisource | Công cụ + nội dung | 4 | 3 | L | Thấp | Niềm tin | H3 (sách đã duyệt — D-080) | Later |
| G13 | Dị bản so sánh hai cột | UX | 2 | 4 | S–M | Thấp | — | H4 nhỏ | Later |
| G14 | Quan hệ đối đáp | Cấu trúc | 2 | 4 | S | Thấp | — | H1 | Later (khi có ≥ 10 cặp) |
| G15 | Đọc thêm ngoài | Cấu trúc | 2 | 3 | S | TB (liên kết hỏng) | — | H4 | Later |
| G16 | Bảng quan hệ chung, loại bài mới | Cấu trúc | 1 | 2 | L | Cao (nợ kỹ thuật) | — | H1, ngược D-110 | **Không làm** |
| G17 | Trạng thái quyền ở cấp tác phẩm | Cấu trúc | 1 | 3 | M | TB | — | ngược D-027 | **Không làm** |
| G18 | Chip thể loại riêng cho thành ngữ | UX | 1 | 4 | S | TB (gán sai) | — | ngược D-079 | **Không làm** |
| G19 | Đưa Ý nghĩa của vè xuống sau lời | UX | 2 | 5 | S | Thấp | — | H4, đổi D-119 | **Không làm** (rút gọn nội dung thay) |
