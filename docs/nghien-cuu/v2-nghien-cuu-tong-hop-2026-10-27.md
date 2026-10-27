# Phongdao V2 — Nghiên cứu tổng hợp: hiện trạng MVP + đề xuất "Phongdao Next Generation"

| | |
|---|---|
| Ngày | 27/10/2026 |
| Người soạn | Claude Code (agent), theo yêu cầu của người sáng lập |
| Trạng thái | **Đã quyết 27/10/2026 (D-082..D-091), có thay đổi so với tài liệu này:** xây mới thay cải tiến tại chỗ; chuyển một lần rồi mở (thay Q5 A); đổi tên ngay; bỏ đường dự phòng Gutenberg; L2 không tính bài phân tích; câu đố có nút Hiện đáp án. Bản chốt: `docs/00` (V2), `docs/26`–`29`. Tài liệu này giữ làm căn cứ, không sửa thêm |
| Căn cứ | Mã trên `main` (plugin 0.33.2, theme 0.19.1); `docs/00`, `01` (tới D-081), `02` (bản 2.4), `12`, `20`, `22`, `24`, `25`; `docs/e2e-review/REPORT.md`; `docs/nghien-cuu/khao-sat-doi-thu-2026-10-27.md`; bản đề xuất *Phongdao Next Generation v1.0* (27/10/2026, gọi tắt **PNG**) |
| Giới hạn | Không đo lại site thật trong phiên này; số liệu site lấy từ báo cáo E2E 26/10 và khảo sát 27/10. Không vào trang quản trị trên VPS |
| Đọc kèm | Phiên bổ sung: `v2-backend-plugin-theme-2026-10-27.md` (kiến trúc plugin, theme, dữ liệu, thứ tự PR) và `v2-ux-ui-2026-10-27.md` (UX/UI, học từ vanvn.net) |

Người sáng lập xác định: tài liệu nền, quyết định, trạng thái và backlog hiện tại là **bản MVP đầu tiên**. V2 phải đột phá hơn ở ba chỗ: **luồng nội dung, giao diện, quản trị**. Tài liệu này gom hiện trạng, lọc đề xuất PNG, rồi đưa ra một hướng V2 cụ thể kèm các câu hỏi cần chốt.

---

## 1. Tóm tắt (đọc trước)

### Năm phát hiện

1. **Phần mềm chạy tốt, nội dung còn mỏng.** Site có 171 bài (151 ca dao, 20 vè), Hiểu nhanh 1/171, đối chiếu sách 0/171, 70/171 bài chỉ có lời. Trong khi đó kho thu thập có khoảng 16.400 câu. Trang bài vẫn nhẹ nhất trong các site Việt Nam đã đo: 177 KB, 0 quảng cáo, 0 lỗi trợ năng.
2. **Chỗ nghẽn là thời gian biên soạn, không phải tính năng.** Một người dưới 10 giờ/tuần duyệt qua 13 trang quản trị rời nhau. Mỗi bài phải mở trình soạn Gutenberg, mà trên điện thoại ô quan trọng lại nằm trong thanh bên đang đóng. Viết 10 Hiểu nhanh mỗi tuần thì 1.000 bài cần 2 năm.
3. **Gốc rễ nằm ở mô hình dữ liệu.** Lời, dị bản, chú thích, đáp án được ghép thành khối Gutenberg trong *thân bài* (`cadao_noi_dung_tu_kho`, `inc/kho.php`). Máy nhận ra phần nào chép từ nguồn bằng lớp CSS (`cadao-chep`) và dấu băm (`_cadao_bam`). Nguồn của từng thứ nằm rải ở ít nhất 6 chỗ: hộp Nguồn của chú thích, hộp "Lời đã đối chiếu với", địa chỉ gốc ẩn, nguồn của trò chơi, nguồn của mục từ, dấu "có hỗ trợ AI". Hệ quả:
   - mỗi tính năng mới phải đọc lại HTML;
   - sửa tay một bài là mất đường tự cập nhật từ kho;
   - mỗi lần đổi chính sách hiện nguồn (D-057 → D-068 → D-078 → D-081) đều phải sửa mã và dọn dữ liệu.
4. **Tài liệu nền còn mang dấu thời Astro.** Nhãn A–D, "Bản tham chiếu", quy tắc R01–R22, bảng "Đọc theo lớp" ở `docs/20` mục 4.1 mô tả một hệ thống đã gỡ (D-071). Còn `docs/00` mục 5 vẫn ghi "không TTS", "không bản đồ tương tác", mà cả hai đã làm theo D-063, T-104. V2 cần một tài liệu nền mới, không vá tiếp.
5. **PNG đúng hướng nhưng giả định một tổ chức chưa có.** Phần định vị, kiến trúc nội dung, 18 màn hình, tuyển tập, mức chất lượng L0–L3 đều đáng dùng. Phần Ban biên tập, cổng G0–G4 có chữ ký, 30–65 ngày công biên tập trong 90 ngày, "viết PRD rồi mới lập trình" thì không khớp với một người cộng agent. V2 nên lấy **nội dung** của PNG và thay **cách vận hành** bằng mô hình máy làm nặng, người quyết nhanh.

### Ba đột phá đề xuất cho V2

| Trục | MVP hiện tại | V2 đề xuất |
|---|---|---|
| **Luồng nội dung** | Một bài = một khối HTML; nguồn rải rác; Hiểu nhanh = ô Tóm tắt | **Tác phẩm nhiều lớp**: lời, dị bản, nghĩa, chú thích, bối cảnh, cách chơi, đáp án, media là các **lớp có cấu trúc**. Mỗi lớp mang hồ sơ nguồn, loại người làm (máy chép / người viết / AI nháp đã duyệt / người đọc góp), trạng thái và lịch sử. Chính sách hiện nguồn thành **cài đặt** |
| **Giao diện** | Một mẫu trang bài chung; phần riêng chèn bằng PHP vào thân bài; trang chủ là một mã ngắn | **Trang đọc theo bản chất thể loại** (ca dao, tục ngữ, vè dài, đồng dao, câu đố, sau là truyện), dựng từ lớp. **Lối vào theo nhu cầu** (Đọc · Khám phá · Tra cứu), tuyển tập "đọc liền", trang "Thông tin tư liệu" của từng bài |
| **Quản trị** | 13 trang con, mỗi trang một kiểu; Bàn biên tập là danh sách liên kết | **Một Bàn biên tập**: hộp việc hợp nhất, mỗi thẻ là một quyết định. **Duyệt nhanh** trên điện thoại (Đăng · Sửa · Để sau). **Trình soạn tác phẩm có cấu trúc** thay khối Gutenberg tự do; kế hoạch tuần do máy đề xuất |

Mục tiêu đo được: **người sáng lập duyệt một bài trong ≤ 60 giây trên điện thoại** (hiện mất vài phút) và **≥ 300 bài có Hiểu nhanh sau 12 tuần** (hiện 1). Cùng lúc giữ trang ≤ 200 KB và 0 lỗi trợ năng.

### Những gì **không đổi** ở V2

Đây là các lằn ranh của dự án, không phải đặc điểm của MVP:

- AI không viết lời, dị bản, nguồn, số trang, vùng.
- Không bịa nguồn. Máy không tự đặt dấu "đã đối chiếu", không tự đăng.
- Không quảng cáo, không theo dõi bên thứ ba, không cookie đo lường.
- Dữ liệu cá nhân và ghi âm phải có đồng ý.
- Kho thô không vào repo công khai.
- Không chặn đăng vì lý do biên tập (D-054).
- Không đổi đường dẫn đã đăng.
- WordPress + plugin + theme hiện có; không viết lại, không chuyển headless.

---

## 2. Hiện trạng MVP

### 2.1. Đã có và nên giữ

| Nhóm | Đã có (task) | Đánh giá cho V2 |
|---|---|---|
| Đọc | Chạm từ xem nghĩa, dị bản tô chỗ khác, thể thơ, cỡ chữ, sáng/tối, trình chiếu, bài dài chia đoạn (T-082..T-085, T-090) | Lõi trải nghiệm. Giữ hành vi, dựng lại trên lớp |
| Niềm tin | Dòng "lời và mức kiểm" (T-119), nguồn đã ẩn (D-078, D-081), trang Chính sách thu thập dữ liệu | Giữ lời hứa; chuyển cách hiện thành cài đặt theo lớp |
| Tìm | Chỉ mục bỏ dấu tự làm, kết quả khi gõ, nhóm "chữ rời rạc" (T-041, T-126) | Giữ; thêm chịu gõ sai (T-132) và tìm trong lớp nghĩa |
| Khám phá | Trang chủ, tình huống, chủ đề, vùng, mô-típ, bản đồ, Đọc tiếp (T-043, T-091..T-094, T-104, T-122) | Giữ dữ liệu; thiết kế lại trang chủ và trang chủ đề |
| Thể loại | Tục ngữ – Thành ngữ một mục, A–Z (T-095, T-128); đồng dao + trò chơi + Đọc cùng bé (T-103) | Nền cho template riêng từng thể loại |
| Nguồn vào | Bộ đọc Ca dao Mẹ tất định, kho thu thập, Chờ duyệt hằng ngày xoay vòng theo loại (T-073, T-125) | Giữ; thêm bộ đọc Wikisource; đầu ra đổ vào lớp |
| Chống trùng | Dấu vân tay cả lời, 4 mức, tự gắn trùng hệt và trùng dị bản, gộp bài giữ đường dẫn (T-101, T-109..T-111, T-123) | Tài sản mạnh, giữ nguyên |
| Người đọc | Góp ý, cảm nghĩ, bài phân tích, Gửi số trang; không email, không cookie (T-045, T-097) | Giữ; đưa vào hộp việc chung |
| Đo | Lượt đọc, chia sẻ, trích dẫn, tìm không ra; không IP, không cookie (T-052, T-081, T-115) | Giữ; dùng để máy xếp việc |
| An toàn | Khoá cửa quản trị, chặn lặp, kiểm site đúng bản, E2E (T-118, T-124, T-129, T-130) | Giữ |
| SEO | Ảnh chia sẻ, JSON-LD, sitemap chỉ trang có giá trị riêng, canonical (T-050, T-127) | Giữ quy tắc noindex theo giá trị riêng |

### 2.2. Số liệu chính

| Chỉ số | Giá trị | Nguồn |
|---|---|---|
| Bài đã đăng | 171 (151 ca dao, 20 vè, 0 tục ngữ) | E2E 26/10 |
| Có Hiểu nhanh | 1 | E2E 26/10 |
| Lời đã đối chiếu sách | 0 | E2E 26/10 |
| Bài chỉ có lời, không chú thích | 70/171 | E2E 26/10 |
| Kho thu thập | 11.844 ca dao · 3.369 tục ngữ – thành ngữ · 304 đồng dao · 131 vè · 749 câu đố | `docs/25` mục 2.1 |
| Trang bài | 177 KB, 12 yêu cầu, 0 lỗi axe; Ca dao Mẹ 1 MB, Thi viện 3,2 MB | Khảo sát 27/10 |
| Hiện diện khi tìm câu quen | Trang văn mẫu chiếm 12/13 lần; kho ca dao 0/13 | Khảo sát 27/10 |
| Nơi người biên soạn phải vào để quyết việc | 13: 11 trang riêng của plugin (`add_submenu_page`) + Góp ý của người đọc, Trò chơi | `inc/` |
| Mô hình dữ liệu | 3 loại bài, 8 phân loại, ~40 khoá meta `_cadao_*`, 9 bảng riêng | `inc/` |

### 2.3. Giới hạn cấu trúc: vì sao không vá tiếp được

**G1 · Nội dung trộn trong thân bài.**
- `cadao_noi_dung_tu_kho` ghép lời (khối Thơ), câu hỏi, đáp án (khối Chi tiết), dị bản (khối Thơ dưới tiêu đề "Dị bản") và chú thích (danh sách mang lớp `cadao-chep`) thành một chuỗi HTML.
- Các phần khác lưu ở chỗ khác:
  - Hiểu nhanh ở ô Tóm tắt;
  - dị bản thêm tay ở `_cadao_di_ban_them`;
  - chú thích tự viết ở `_cadao_chu_thich_them`.
- Muốn biết bài có gì, máy phải đọc lại HTML: tìm, từ điển, chạm từ, gộp bài, mục lục bài dài đều làm vậy.
- Người biên soạn sửa một chữ trong thân bài thì dấu băm lệch, bài ngừng nhận dị bản và chú thích mới từ kho.

**G2 · Nguồn không có một mô hình chung.**

| Nguồn của | Lưu ở | Hiện ra trang |
|---|---|---|
| Chú thích do người viết điền | `nguon_ten`, `nguon_url`, `ngay_truy_cap` | "Nguồn: …" |
| Lời đã đối chiếu | `doi_chieu_ten`, `doi_chieu_trang`, `doi_chieu_url` | Dòng mức kiểm |
| Bài máy lấy | `_cadao_url_goc`, `_cadao_ngay_lay`, `_cadao_nguon_id` | "[N nguồn tham khảo đã ẩn]" |
| Chú thích chép | lớp CSS `cadao-chep` trong HTML | Không |
| Mục từ | `_cadao_nguon_ten`, `_cadao_nguon_url` (term meta) | Có, khi còn |
| Cách chơi | `_cadao_cc_nguon_*`, `_cadao_cc_giay_phep` | Có |
| Hiểu nhanh có AI | `_cadao_ho_tro_ai` | Dòng nhỏ |

Không có chỗ nào ghi được "dị bản này nghe ở Quảng Nam, bà X kể, năm 2026" hay "nghĩa đen này theo sách 1897, tr. 12". Đó lại đúng là cơ hội khác biệt mà khảo sát 27/10 nêu (mục 5.3: dị bản có nguồn, nơi, người kể; lời có nguồn tới trang).

**G3 · Hiểu nhanh chỉ là một ô văn bản.** Tục ngữ cần nghĩa đen, nghĩa bóng, dùng khi nào, câu gần nghĩa (T-098). Vè cần bối cảnh (T-102). Câu đố cần gợi ý và giải thích (T-096). Truyện cần bản kể, đoạn, nhân vật. Với MVP, mỗi thứ này lại thêm một bộ meta và một hộp trong trình soạn.

**G4 · Quản trị phân mảnh.**
- 13 trang con: Bàn biên tập, Nguồn tự động, Kho thu thập, Có thể trùng, Viết Hiểu nhanh, Gợi ý vùng – chủ đề, Gợi ý mô-típ, Xác nhận Wikidata, Bạn có biết?, Lời chưa vào khối Thơ, Chống rác, Góp ý của người đọc, Trò chơi.
- Mỗi trang một bố cục, một kiểu nút.
- Bàn biên tập chỉ đếm và dẫn đi; quyết định vẫn diễn ra ở 13 nơi.
- Báo cáo E2E (mục 4, Quản trị) ghi: trên điện thoại, ô "Có thể trùng" và "Hiểu nhanh" nằm trong hộp đang đóng; khối Thơ rỗng có sẵn khiến người dán lời ra ngoài thành hai khối Thơ.

**G5 · Giao diện khó thiết kế theo thể loại.**
- Mẫu `single-cdtn.html` chỉ có tiêu đề + `post-content` + bình luận; mọi khối khác do PHP chèn vào thân bài qua bộ lọc.
- Trang chủ là mã ngắn `[cadao_trang_chu]`.
- Muốn trang tục ngữ khác trang vè khác trang câu đố thì phải sửa PHP, không chỉnh được bằng mẫu, khối của theme.

**G6 · Tài liệu nền lệch với hệ thống.** `docs/00` mục 4–6 và `docs/20` mục 4 còn nói về nhãn A–D, `tinhNhan()`, YAML, Bản tham chiếu, GitHub làm lịch sử sửa đổi. Agent mới đọc dễ áp luật cũ (lý do D-071 gỡ Astro).

---

## 3. Lọc đề xuất PNG cho V2

| Mục PNG | Lấy cho V2? | Ghi chú |
|---|---|---|
| Định vị "Thư viện văn học dân gian Việt Nam", ba trụ TIN – HIỂU – CẢM (§3) | **Lấy, theo đợt** | Ba trụ đã có ở `docs/00`. Mở phạm vi ra ngoài ca dao – tục ngữ là thay đổi lớn nhất. V2 nên đổi định vị nhưng mở thể loại mới theo cổng (mục 5.4) |
| Giữ WordPress, không làm lại, không đổi URL (§10.1) | **Lấy** | Khớp hiện trạng |
| Sáu chiều phân loại; tách vùng lưu truyền khỏi địa danh trong lời (§5.2) | **Lấy** | Hiện `vung` trộn hai nghĩa; V2 tách thành hai quan hệ |
| Content type + dữ liệu tối thiểu (§5.3) | **Lấy, dựng bằng mô hình lớp** | Mỗi dòng của bảng PNG là một tập lớp (mục 4.1) |
| Bốn cấp L0–L3 (§5.4) | **Lấy cho nội bộ** | Máy tính từ lớp có sẵn, dùng để xếp việc và chọn bài nổi bật. Không thành huy hiệu trên trang (giữ D-072 hướng B) |
| Tuyển tập liên kết, không nhân bản (§5.5) | **Lấy** | Đối tượng mới, nhẹ |
| Hồ sơ provenance (§6.2) | **Lấy, rút gọn** | Thành hồ sơ nguồn dùng chung (mục 4.1). Thiếu thì để trống, không đoán |
| Workflow biên tập 8 bước (§6.3) | **Lấy, gộp vào hộp việc** | Bước 6 "reviewer khác người nhập" là tùy chọn khi có người thứ hai |
| Ba phương án hiện nguồn (§6.1) | **Lấy phương án B làm đích** | Tab "Thông tin tư liệu" theo lớp. Với mô hình lớp, đổi giữa A/B/C chỉ còn là cài đặt |
| IA Đọc · Khám phá · Tra cứu (+ Nghe) (§7) | **Lấy** | |
| Art direction "Sách Việt đương đại" (§8) | **Lấy hướng; bảng màu thử ở nguyên mẫu** | Token hiện tại đã đo tương phản (`docs/20` mục 1.1). Bảng màu PNG (#F4EDE1…) chỉ thay khi nguyên mẫu đẹp hơn và đo đạt |
| 18 màn hình S01–S18 (§9) | **Lấy làm danh mục** | Mục 5.2 gom lại theo đợt |
| NFR (§10.3) | **Lấy** | Thêm ngân sách 200 KB đang có |
| Ban biên tập, cổng G0–G4, nhân sự, ngày công (§13–14) | **Không lấy dạng này** | Thay bằng vai trò trong phần mềm (mục 4.3) và cổng do người sáng lập quyết |
| "Chỉ lập trình sau khi PRD, UX Specs được duyệt" (§18, D10) | **Không lấy** | Thay bằng "tài liệu nền V2 + đặc tả từng đợt được duyệt trước khi làm đợt đó" (mục 6) |
| Truyện dân gian pilot 15–20 truyện trong 90 ngày (§11) | **Hoãn tới V2.3** | Cần mô hình lớp và nguồn có quyền (Wikisource, sách hết bản quyền) trước |
| Audio 30 bài TTS (D7) | **Đã quyết** | D-063, T-105 |

---

## 4. Thiết kế V2

### 4.1. Luồng nội dung: "Tác phẩm nhiều lớp"

**Mô hình**

```text
Tác phẩm (bài cdtn — giữ ID, đường dẫn, thể loại, chủ đề, vùng, mô-típ)
├── Lớp VĂN BẢN
│   ├── Lời chính ─────────── hồ sơ nguồn: "bản lưu truyền phổ biến" | đã đối chiếu <sách, tr.>
│   └── Dị bản 1..n ───────── mỗi dị bản: nguồn, nơi, người kể (nếu có), vùng
├── Lớp NGHĨA
│   ├── Hiểu nhanh (1–2 câu)
│   ├── Nghĩa đen / nghĩa bóng / dùng khi / câu gần nghĩa   (tục ngữ)
│   └── Giải nghĩa trong sách (ví dụ sách 1897, giữ chính tả xưa — D-080 (4))
├── Lớp CHÚ THÍCH ─────────── từng mục: từ, lời giải, nguồn → nối sang Từ điển
├── Lớp BỐI CẢNH ──────────── sự kiện, năm, nơi, nhân vật (vè, truyện)
├── Lớp THỰC HÀNH ─────────── cách chơi (đồng dao), câu hỏi + đáp án + gợi ý (câu đố)
├── Lớp MEDIA ─────────────── ảnh Commons, giọng đọc (máy / người), mỗi tệp một giấy phép
└── Lớp ĐỘC GIẢ ───────────── bài phân tích, cảm nghĩ đã duyệt (đã có)
```

**Mỗi mục trong một lớp mang:**

| Trường | Ví dụ |
|---|---|
| `nguoi_lam` | `may_chep` (bộ đọc nguồn) · `nguoi_viet` · `ai_nhap_da_duyet` · `doc_gia_gop` |
| `nguon` | trỏ tới một hồ sơ nguồn + trang/địa chỉ + ngày truy cập; trống = chưa rõ, không đoán |
| `trang_thai` | nháp · chờ duyệt · đã duyệt · ẩn |
| `hien` | theo chính sách của loại nguồn (dưới đây) |
| `lich_su` | ai đổi, lúc nào, từ gì sang gì |

**Hồ sơ nguồn dùng chung**: một bảng gồm Ca dao Mẹ, Wikisource *Câu hát góp* 1901, *Tục ngữ, cổ ngữ, gia ngôn* 1897, Wikipedia, Wiktionary, Commons, người đọc góp, người kể. Mỗi hồ sơ có loại, giấy phép, trạng thái xin phép, cách ghi công.

**Chính sách hiện nguồn thành cài đặt.** Mỗi loại nguồn có một dòng cài đặt: hiện tại chỗ / gom về tab "Thông tin tư liệu" / ẩn với người đọc (chỉ người biên soạn thấy). Mặc định V2 tái tạo đúng hiện trạng D-068 + D-078 + D-081: chú thích chép từ Ca dao Mẹ ẩn; ghi công giấy phép mở tại chỗ; nguồn người biên soạn điền thì hiện. Đổi chính sách về sau chỉ là đổi cài đặt và ghi một dòng quyết định, không phải sửa mã.

**Lưu ở đâu.** Đề xuất một bảng riêng `wp_cadao_lop` (tác phẩm, lớp, thứ tự, nội dung, người làm, nguồn, trạng thái) và bảng `wp_cadao_ho_so_nguon`; lịch sử dùng bảng nhật ký. Thân bài (`post_content`) **được sinh lại từ lớp** để tìm của WordPress, RSS và các plugin ngoài vẫn đọc được, nhưng không còn là nơi sửa. So với dùng postmeta: bảng riêng truy vấn được theo lớp (ví dụ "bài nào chưa có nghĩa bóng", "dị bản nào có người kể"), đúng như PNG §10.1 nói "chỉ dùng bảng quan hệ khi phải lưu vai trò, nguồn của từng liên kết". Ở đây điều kiện đó có thật.

**Chuyển dữ liệu.** Một lần, chạy lại được nhiều lần cho ra cùng kết quả:
- Đọc khối hiện có:
  - khối Thơ đầu → lời chính;
  - khối Thơ dưới "Dị bản" → dị bản;
  - danh sách `cadao-chep` → chú thích, kèm hồ sơ nguồn lấy từ `_cadao_nguon_id`;
  - Tóm tắt → Hiểu nhanh;
  - `_cadao_ho_tro_ai` → `ai_nhap_da_duyet`.
- Bài nào không tách được thì giữ nguyên và đưa vào hàng "Cần xem".
- Không đổi chữ nào của lời, không đổi đường dẫn.
- Chạy sau khi sao lưu hằng ngày đã bật và thử khôi phục xong.

**Luồng từ nguồn tới trang**

```text
Nguồn (Ca dao Mẹ · Wikisource · người đọc · nhập tay)
  → Kho thu thập (giữ như MVP, kho thô không lên trang)
  → Máy: tách lớp · chống trùng · gợi ý thể loại/chủ đề/vùng/mô-típ · tìm trang sách quét khớp lời
  → Agent: nháp Hiểu nhanh / nghĩa theo lô — gắn `ai_nhap`, trạng thái "chờ duyệt"
  → HỘP VIỆC (một nơi): mỗi thẻ = một quyết định
  → Người sáng lập: Đăng · Sửa · Để sau            (máy không bao giờ tự bấm)
  → Trang đọc dựng từ lớp đã duyệt
  → Hậu kiểm: góp ý, báo lỗi, tìm không ra → quay lại Hộp việc
```

**Cấp chất lượng (nội bộ, máy tính).** L0 có lời → L1 có thể loại + dòng mức kiểm → L2 có Hiểu nhanh hoặc nghĩa đã duyệt → L3 có thêm đối chiếu sách, bối cảnh hoặc media. Dùng để:
- xếp việc ("nâng 20 bài đọc nhiều nhất lên L2");
- chọn bài cho trang chủ, tuyển tập;
- thay ngưỡng `noindex` hiện tại (L2 trở lên mới lập chỉ mục, đúng như quy tắc đang có).

Cấp này không hiện thành huy hiệu cho người đọc.

**Tuyển tập** là đối tượng riêng, gồm tiêu đề, lời dẫn do người viết, danh sách ID có thứ tự, ngày sửa. Trang tuyển tập dựng từ lớp của từng bài (lời + Hiểu nhanh + chú thích địa danh). Đây là dạng tổng quát của T-137 "đọc liền" và trả lời các truy vấn kiểu "ca dao về mẹ" (khảo sát 27/10, mục 3.3).

### 4.2. Giao diện

**Kiến trúc thông tin**

```text
Đọc        Ca dao · Tục ngữ – Thành ngữ · Đồng dao · Vè · Câu đố · (Truyện — khi đủ)
Khám phá   Chủ đề · Tuyển tập · Vùng & Bản đồ · Mô-típ · Theo dịp · Ngẫu nhiên
Tra cứu    Tìm · Từ điển · Thông tin tư liệu · Phương pháp
(Nghe)     chỉ lên menu khi giọng đọc sinh sẵn qua phiên nghe thử (T-105)
Điện thoại: thanh dưới  Trang chủ · Tìm · Khám phá · Đã lưu
```

**Trang đọc theo thể loại.** Mỗi thể loại một mẫu, dựng từ cùng bộ thành phần:

| Thể loại | Ưu tiên trên màn hình đầu (390 px) | Thành phần riêng |
|---|---|---|
| Ca dao | Lời lục bát thụt lề → dòng mức kiểm → Hiểu nhanh | Chạm từ, dị bản tô chỗ khác |
| Tục ngữ – Thành ngữ | Câu chữ to một dòng → **nghĩa trong 10 giây** → nghĩa đen / bóng → dùng khi | Câu gần nghĩa, ví dụ đã biên tập |
| Vè, bài dài | Tiêu đề → số dòng, thời gian đọc → mục lục | Bối cảnh (sự kiện, năm, nơi), nhớ chỗ đọc |
| Đồng dao | Lời chữ lớn → Đọc cùng bé → Cách chơi | Số người, độ tuổi, đồ dùng |
| Câu đố | Câu hỏi → Gợi ý → **Hiện đáp án** (`<details>`, không JS vẫn dùng được) | Câu khác, không chấm điểm |
| Truyện (V2.3) | Tên → bản kể và nguồn → mục lục đoạn | Nhân vật, mô-típ, bản kể khác |

Phác trang tục ngữ trên điện thoại:

```text
┌──────────────────────────────┐
│ TỤC NGỮ – THÀNH NGỮ          │
│                              │
│  Ăn quả nhớ kẻ trồng cây     │  ← câu, chữ to
│  Lời phổ biến · chưa đối     │
│  chiếu sách · Vì sao?        │
│ ──────────────────────────── │
│  NGHĨA                       │
│  Hưởng thành quả thì nhớ     │  ← Hiểu nhanh (≤ 2 câu)
│  công người làm ra nó.       │
│  ▸ Nghĩa đen · nghĩa bóng    │
│  ▸ Dùng khi                  │
│  ▸ Câu gần nghĩa (3)         │
│ ──────────────────────────── │
│ [❝ Trích dẫn] [↗] [☆] [▣]    │
│  Thông tin tư liệu ›         │  ← tab nguồn theo lớp (cài đặt)
└──────────────────────────────┘
```

**Trang chủ: lối vào theo nhu cầu**, thứ tự theo PNG S01, chỉ hiện khối có dữ liệu:
1. Ô tìm.
2. Câu hôm nay (L2).
3. Bốn lối vào: *Tìm một câu* · *Hiểu nghĩa câu tục ngữ* · *Theo dịp* (Tết, Vu Lan, 20/11) · *Cùng bé*.
4. Tuyển tập nổi bật.
5. Thể loại (ẩn thể loại 0 bài).
6. Đọc nhiều / Vừa bổ sung.
7. Phương pháp.

**Thông tin tư liệu** (PNG §6.1 B, S18): một khối thu gọn cuối bài. Mỗi lớp một dòng, nói lời theo đâu, nghĩa ai viết (có AI hỗ trợ hay không), chú thích từ đâu, ảnh giấy phép gì. Dòng nào hiện, dòng nào chỉ người biên soạn thấy thì theo cài đặt ở 4.1.

**Bộ thành phần.** Giữ token màu, phông, bề rộng 38em của `docs/20`. Thêm một danh mục thành phần có tên, trạng thái trống / đang tải / lỗi cho từng thành phần:
- thẻ bài; khối lời; dòng mức kiểm; khối nghĩa; khối chú thích;
- khối dị bản; khối bối cảnh; khối thực hành; khối thông tin tư liệu;
- chip lọc; thẻ tuyển tập; thanh hành động.

Theme chuyển từ "PHP chèn vào thân bài" sang **khối động do plugin đăng ký**, mỗi lớp một khối. Mẫu từng thể loại trong theme chỉ xếp khối, nên thiết kế lại một thể loại là sửa mẫu, không sửa nghiệp vụ.

**Ngân sách giữ nguyên:** trang bài ≤ 200 KB, tắt JavaScript vẫn đọc được, 0 lỗi axe WCAG 2.2 AA, không tự phát âm thanh.

### 4.3. Quản trị: một Bàn biên tập

**Hộp việc hợp nhất.** Mọi hàng chờ hiện nay thành thẻ trong một danh sách:
- bài Chờ duyệt, nháp Hiểu nhanh;
- cặp Có thể trùng;
- Wikidata, gợi ý vùng/chủ đề, mô-típ, Bạn có biết?;
- góp ý, báo lỗi, phân tích, cảm nghĩ;
- trang sách quét khớp lời (T-131).

Máy xếp thẻ theo tác động: báo lỗi trước, rồi bài được đọc / tìm nhiều chưa đạt L2, rồi phần còn lại. Có lọc theo loại việc. 13 trang con hiện nay vẫn còn trong menu "Công cụ nâng cao" cho tới khi hộp việc thay hết.

**Duyệt nhanh trên điện thoại:**

```text
┌──────────────────────────────┐
│ Việc 3/27 · Nháp Hiểu nhanh  │
│ "Ăn quả nhớ kẻ trồng cây"    │
│ đọc 41 lần tuần này          │
│ ──────────────────────────── │
│ Nháp (AI, chưa duyệt):       │
│ Hưởng thành quả thì nhớ công │
│ người làm ra nó.             │  ← chạm để sửa ngay tại chỗ
│ Chú thích của bài: —         │
│ ──────────────────────────── │
│ [ Để sau ] [ Sửa ] [ ĐĂNG ]  │  ← nút ≥ 48 px; máy tính: phím 1/2/3
│ ☑ Có hỗ trợ của AI           │  ← người duyệt tự bỏ nếu viết lại hết
└──────────────────────────────┘
```

Mỗi lần bấm Đăng ghi `da_duyet_boi` + thời điểm vào nhật ký lớp. Máy không bao giờ đặt trạng thái "đã duyệt".

**Trình soạn tác phẩm có cấu trúc** (cho bài `cdtn`; trang tĩnh vẫn dùng Gutenberg):
- ô Lời (một dòng thơ một dòng, máy nhận thể thơ ngay);
- danh sách Dị bản, mỗi dòng có nguồn / nơi / người kể;
- Chú thích dạng bảng từ – lời giải – nguồn, gợi ý từ Từ điển có sẵn;
- Nghĩa (Hiểu nhanh + các ô theo thể loại);
- Đối chiếu: bên cạnh là ảnh trang quét Wikisource, bấm "Lời khớp trang này" (T-131).

Báo trùng hiện thành dải ngay dưới ô Lời, không giấu trong thanh bên. Thiết kế cho điện thoại trước.

**Kế hoạch tuần.** Đầu Bàn biên tập là một khối "Tuần này", máy đề xuất từ số liệu sẵn có:
- 10 bài nên nâng L2 (đọc nhiều, tìm nhiều);
- 5 câu "tìm không ra" có trong kho;
- số việc chờ theo loại.

Kèm ô "Kho đang thiếu gì" (số bài theo thể loại, theo cấp L0–L3), như báo cáo E2E (mục 5) đề xuất.

**Vai trò.** Khai báo sẵn ba vai: Quản trị · Biên tập (duyệt, đăng) · Cộng tác (soát lời, gửi số trang, không đăng). Hiện chỉ có một người, nhưng nhật ký lớp ghi ai làm gì. Khi có người thứ hai thì mở bước "người duyệt khác người nhập" của PNG §6.3 cho bài nổi bật và nội dung trẻ em.

**Công nghệ.** Hộp việc, Duyệt nhanh và trình soạn có cấu trúc cần tương tác nhanh, nên đề xuất một trang quản trị dựng bằng `@wordpress/components` (React có sẵn trong WordPress), gọi REST của plugin (có kiểm quyền, nonce như các REST hiện có). Đổi lại, repo phải có bước dựng mã JavaScript (`@wordpress/scripts`). Nếu không muốn thêm bước dựng, có thể làm bằng PHP + JavaScript thuần như hiện tại, nhưng chậm hơn và khó làm Duyệt nhanh mượt. Cần người sáng lập chọn (Q4).

---

## 5. Lộ trình V2

### 5.1. Nguyên tắc chia đợt

- Mỗi đợt có một **đặc tả ngắn** được người sáng lập duyệt trước khi làm. Agent làm và tự gộp theo D-066 trong đợt đó. Không chờ một bộ PRD trọn gói.
- Đợt sau dựa trên đợt trước; mỗi đợt để lại site chạy được.
- Không đổi đường dẫn; chuyển dữ liệu chạy lại được và có đường quay lại.
- **Mở công khai (H5) không chờ V2.** Điều kiện còn lại (sao lưu + thử khôi phục) giữ nguyên. Bài đăng trong lúc chuyển đều đi qua bước chuyển dữ liệu.

### 5.2. Các đợt

| Đợt | Nội dung | Màn hình PNG | Đặt cạnh task hiện có |
|---|---|---|---|
| **V2.0 · Nền** | Tài liệu nền V2 (thay `docs/00`, viết lại `docs/20` mục 4); mô hình lớp + hồ sơ nguồn + nhật ký; chuyển dữ liệu; chính sách hiện nguồn thành cài đặt (mặc định = hiện trạng). **Giao diện người đọc không đổi** | — | Cần sao lưu đã bật (đang chờ người sáng lập) |
| **V2.1 · Bàn biên tập** | Hộp việc hợp nhất, Duyệt nhanh, trình soạn có cấu trúc, Kế hoạch tuần, vai trò | S17 (phần duyệt) | Gộp T-138 (lô Hiểu nhanh), T-131 (đối chiếu trang quét), T-098 (ô nghĩa tục ngữ), T-135 (cờ nghi không phải dân gian) vào đây |
| **V2.2 · Trang đọc** | Khối theo lớp, mẫu theo thể loại (ca dao, tục ngữ, vè, đồng dao, câu đố), trang chủ lối vào, IA mới, Thông tin tư liệu, tuyển tập | S01–S08, S11–S15, S18 | Gộp T-096 (câu đố), T-102 (bối cảnh vè), T-136 (khối Biên soạn), T-137 (đọc liền) |
| **V2.3 · Mở rộng** | Thể loại mới qua cổng (truyện, hát ru – hò – lý dạng văn bản); giọng đọc sinh sẵn; bộ đọc Wikisource; bộ câu SGK; dữ liệu mở có phiên bản | S09, S10, S16, S17 (ghi âm) | T-105, T-133, T-066; T-062, T-068 vẫn chờ nhu cầu |

Các task READY **nên làm xong ngay trên MVP** vì nhỏ và không phụ thuộc mô hình lớp: **T-132** (tìm chịu gõ sai). **T-138** phần nội dung (agent soạn nháp 10 câu mỗi tuần) cũng nên chạy tiếp ngay bằng công cụ Viết Hiểu nhanh hiện có, vì nội dung là chỗ nghẽn lớn nhất. Các task còn lại nên đổi thành `DEFERRED → V2.x` khi người sáng lập duyệt hướng này, để khỏi làm hai lần.

### 5.3. Ước lượng (agent làm, người sáng lập duyệt)

| Đợt | Agent | Người sáng lập | Rủi ro chính |
|---|---|---|---|
| V2.0 | 3–5 PR | ~1 giờ đọc đặc tả + tài liệu nền | Tách lớp sai ở bài đã sửa tay → hàng "Cần xem", không đoán |
| V2.1 | 4–6 PR | ~1 giờ duyệt đặc tả, rồi dùng thử | Thêm bước dựng JavaScript (nếu chọn React) |
| V2.2 | 5–8 PR | ~2 giờ xem bản xem trước từng mẫu | Giữ ngân sách 200 KB khi thêm khối |
| V2.3 | theo từng thể loại | duyệt nguồn, quyền | Quyền của bản kể truyện, giọng đọc |

### 5.4. Cổng mở một thể loại mới

Một thể loại chỉ lên menu khi đủ cả ba:
1. Có mẫu trang đọc riêng qua quét trợ năng.
2. Có ít nhất 20 bài đã đăng, trong đó ít nhất 10 bài ở cấp L2.
3. Nguồn của lời có hồ sơ nguồn, quyền đã ghi.

Riêng truyện dân gian còn cần một nguồn có quyền rõ (sách hết bản quyền trên Wikisource hoặc thỏa thuận). Không chép bản kể hiện đại.

---

## 6. Chỉ số thành công V2

| Nhóm | Chỉ số | Hiện tại | Đích sau V2.1 + 12 tuần |
|---|---|---|---|
| Thông lượng | Thời gian duyệt một nháp Hiểu nhanh trên điện thoại | vài phút (mở trình soạn) | ≤ 60 giây |
| Nội dung | Bài ở cấp L2 | 1 | ≥ 300 |
| Nội dung | Thể loại có bài đã đăng | 2 | 5 |
| Niềm tin | Lớp có hồ sơ nguồn (hoặc ghi rõ "chưa rõ") | chưa đo được | 100% |
| Niềm tin | Lời đã đối chiếu sách | 0 | ≥ 50 (qua T-131) |
| Đọc | Trang bài | 177 KB, 0 lỗi axe | ≤ 200 KB, 0 lỗi axe |
| Quản trị | Số nơi phải vào để quyết việc | 13 | 1 |
| Người đọc | Câu "tìm không ra" có trong kho đã thành bài | chưa đo | ≥ 50% mỗi tháng |

---

## 7. Rủi ro

| Rủi ro | Mức | Cách giảm |
|---|---|---|
| Chuyển dữ liệu làm hỏng bài | Cao | Chỉ chạy sau sao lưu + thử khôi phục; chạy lại được nhiều lần; so từng ký tự lời trước/sau; hàng "Cần xem" thay vì đoán |
| V2 kéo dài, MVP đứng yên | Cao | Mở công khai không chờ V2; Hiểu nhanh chạy tiếp trên công cụ hiện có; mỗi đợt để lại site chạy được |
| Hộp việc làm việc dễ quá, duyệt qua loa | Vừa | Thẻ luôn hiện chú thích và nguồn liên quan; nhãn AI rõ; báo lỗi của người đọc đứng đầu hộp |
| Mở rộng thể loại làm loãng chất lượng | Vừa | Cổng mục 5.4; cấp L2 làm thước đo |
| Thêm bước dựng JavaScript làm khó bảo trì | Vừa | Chỉ cho trang quản trị; trang người đọc vẫn không phụ thuộc |
| Đổi chính sách nguồn ngoài ý muốn | Vừa | Mặc định V2 tái tạo đúng D-068/D-078/D-081; đổi cài đặt phải ghi quyết định |
| Thư cho Ca dao Mẹ chưa gửi khi site lớn dần | Vừa | Không phụ thuộc V2; nên gửi trước H5 (T-034) |

---

## 8. Câu hỏi cần người sáng lập quyết

| # | Câu hỏi | Các hướng | Gợi ý của agent |
|---|---|---|---|
| **Q1** | Định vị V2 | (A) "Thư viện văn học dân gian Việt Nam", mở thể loại theo cổng · (B) giữ "Kho tàng Ca dao Tục ngữ", chỉ đào sâu · (C) A nhưng giữ tên site hiện tại tới khi có truyện | **C**: đổi định vị trong tài liệu nền, đổi tên trên trang khi thể loại thứ sáu qua cổng |
| **Q2** | Mô hình dữ liệu | (A) Tác phẩm nhiều lớp, bảng riêng, chuyển dữ liệu một lần · (B) thêm meta từng thứ như MVP | **A**: là điều kiện cho cả ba đột phá |
| **Q3** | Trình soạn cho bài ca dao – tục ngữ | (A) trình soạn có cấu trúc thay Gutenberg · (B) giữ Gutenberg, thêm hộp | **A** |
| **Q4** | Công nghệ trang quản trị mới | (A) React của WordPress (`@wordpress/components`, thêm bước dựng) · (B) PHP + JavaScript thuần | **A**, chỉ cho quản trị |
| **Q5** | Thứ tự với mở công khai | (A) mở trước, V2 song song · (B) chờ V2.1 rồi mở | **A** |
| **Q6** | Hiện nguồn ở V2 | (A) giữ đúng hiện trạng làm mặc định, có tab Thông tin tư liệu · (B) đổi ngay sang tab hiện nguồn theo lớp cho người đọc | **A** trước; B khi thư cho Ca dao Mẹ có trả lời |
| **Q7** | Truyện dân gian | (A) V2.3 qua cổng · (B) ngoài V2 | **A** |
| **Q8** | Bảng màu | (A) giữ token hiện tại · (B) thử bảng màu PNG trên nguyên mẫu V2.2 rồi chọn | **B**, quyết bằng mắt trên bản xem trước |

---

## 9. Bước tiếp theo khi bạn trả lời

1. Agent ghi các lựa chọn thành quyết định mới trong `docs/01` (số tiếp theo sau D-081).
2. Agent soạn **tài liệu nền V2** (thay `docs/00`, giữ bản MVP ở `docs/luu-tru/`) và **đặc tả đợt V2.0**. Bạn đọc, duyệt.
3. Agent thêm task V2.0 vào `docs/22`, chuyển các task READY nêu ở mục 5.2 thành `DEFERRED → V2.x`.
4. Song song, không chờ: bạn bật sao lưu + thử khôi phục (điều kiện cho cả H5 lẫn chuyển dữ liệu V2.0); agent tiếp tục lô Hiểu nhanh 10 câu/tuần (T-138) và T-132.
