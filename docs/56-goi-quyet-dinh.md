# 56 · Gói quyết định trình người sáng lập — Chương trình nâng cấp lớn V2

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Căn cứ (D-127, 21/11/2026)** — kế hoạch đang dùng: [57](57-ke-hoach-chot.md); nơi khác nhau, 57 thắng. Nguyên trạng lúc lập: **Chờ người sáng lập trả lời.** Mỗi mục được duyệt thành một D-xxx ở `docs/01` (việc E0.1.1 ở [55](55-lo-trinh-va-backlog.md)); mục bị bác ghi lại ở đây là "giữ như cũ". Q-008 ở `docs/01` giữ mở tới khi trả lời F7. Thay gói quyết định ở [49](49-quyet-dinh-va-cau-hoi-mo.md) |
| Bộ tài liệu | [50 tầm nhìn](50-tai-dinh-vi-chien-luoc.md) · [51 rà soát quyết định](51-danh-gia-lai-quyet-dinh.md) · [52 chất lượng nội dung](52-chien-luoc-chat-luong-noi-dung.md) · [53 trải nghiệm](53-thiet-ke-lai-trai-nghiem.md) · [54 kiến trúc](54-kien-truc-dich-va-chuyen-doi.md) · [55 lộ trình, backlog](55-lo-trinh-va-backlog.md) · **56 gói quyết định** |

---

## 1. Cách trả lời

- Gọn nhất: **"Đồng ý các khuyến nghị"**, hoặc ghi mã muốn khác (vd. "B1 chọn (b)", "C2 chọn B1").
- **Gói bắt đầu** (mục 3) đủ để chạy GĐ0 và GĐ1 ngay. Các nhóm còn lại trả lời trước khi tới giai đoạn cần chúng.

## 2. Bảng tóm tắt

| Mã | Quyết định | Khuyến nghị | Gate | Cần trước |
|---|---|---|---|---|
| **A1** | Định vị "thư viện tham chiếu" | Đồng ý | H4 | GĐ0 |
| **A2** | Năng lực biên tập: nhóm cộng tác viên | Chạy S0 ngay, tuyển S1, xét S2 khi có tài chính | — | GĐ0 |
| **A3** | Trang thử, thử khôi phục, đo hiệu năng | Đồng ý | H6 | GĐ0 |
| **A4** | Gộp PR theo mức rủi ro | Đồng ý | — | GĐ0 |
| **A5** | Sổ đăng ký sự kiện đo (không định danh) | Đồng ý | — | GĐ1 |
| **A6** | Thêm Human Gate H7 (thương mại), H8 (AI trả lời người đọc), H9 (tài khoản, dữ liệu học sinh) | Đồng ý | — | GĐ0 |
| **B1** | Chú thích chép từ Ca dao Mẹ | Gửi thư + **bật lại ghi nguồn tại chỗ** + thay dần | H3 | GĐ0 |
| **B2** | Nhãn phụ Tục ngữ / Thành ngữ | Đồng ý | H1 | GĐ1 |
| **C1** | Bảng quan hệ có kiểu, có bằng chứng | Đồng ý | H1 | GĐ3 (tuần ~10) |
| **C2** | Hồ sơ thực thể | Loại bài riêng (B2) | H1 | GĐ3 |
| **C3** | Bài viết: dùng `post`, đường dẫn `/bai-viet/`, ba nhóm | Đồng ý; bạn chốt tên nhóm | H1, H4, H6 | GĐ3 |
| **C4** | Họ dị bản, công cụ gộp có hoàn tác | Đồng ý; gộp từng lô do bạn duyệt | H6 | GĐ3 |
| **C5** | Xưởng biên tập (bảng việc mở rộng), cấp mới, quyền ảnh / âm thanh | Đồng ý | H1 | GĐ1 |
| **C6** | Ngân sách JavaScript riêng cho trang ứng dụng | Đồng ý | H4 | GĐ2 |
| **C7** | Giữ WordPress; một lớp lọc dùng lại; đo trên trang thử trước khi bật | Đồng ý | — | GĐ0 |
| **D1** | Thiết kế lại qua bản mẫu và thử người dùng; IA mới; mời góp nhẹ hơn | Đồng ý quy trình; bạn duyệt ở PG1, PG4 | H4 | GĐ2 (tuần ~3) |
| **D2** | Thử hai hướng thị giác; sàn chữ lời 18 px | Đồng ý thử | H4 | GĐ2 |
| **D3** | Nhãn mức biên tập cho người đọc | Thử trong bản mẫu rồi quyết | H4 | GĐ2 |
| **E1** | Hai tầng xuất bản; Bộ tham chiếu ~3.000, hạng A 500; nâng ngưỡng lập chỉ mục | Đồng ý | H4 | GĐ1 |
| **E2** | Xưởng biên tập có AI: cách chạy, mô hình, ngân sách | Phương án tệp (P1); Claude Opus 5.5 mặc định; trần 100 USD / tháng lúc đầu | H2 | GĐ1 |
| **E3** | Đa dạng nguồn: nhận dạng chữ sách hết bản quyền, người so lại; sách hiện đại chỉ ghi số trang | Đồng ý | H3 | GĐ3 |
| **E4** | Bước Thẩm định học thuật cho hạng A | Đồng ý | — | GĐ1 |
| **E5** | Sổ tay biên tập làm chuẩn chung | Đồng ý; duyệt bản nháp khi có | — | GĐ1 |
| **E6** | Máy gợi ý thô tục, người xác nhận | Đồng ý | — | GĐ1 |
| **F1** | Danh mục sản phẩm NEXT và điều kiện khởi động | Đồng ý | — | GĐ4 |
| **F2** | Trò chơi hoá có giới hạn | Đồng ý | H4 | GĐ4 |
| **F3** | Tài khoản cho cộng tác viên, giáo viên (không cho người đọc) | Đồng ý nguyên tắc; mở cho giáo viên ở GĐ5 | H9 | GĐ5 |
| **F4** | "Hỏi thư viện" — AI có trích dẫn, có điều kiện | Đồng ý nguyên tắc; làm khi đủ điều kiện | H8 | GĐ5 |
| **F5** | PWA (thêm vào màn hình chính, đọc ngoại tuyến) | Đồng ý | H4 | GĐ4 |
| **F6** | Bộ sưu tập thử nghiệm cho thể loại mới | Đồng ý | H1 | GĐ4 |
| **F7** | Hướng tài chính, pháp nhân | (a) bây giờ; quyết (b) hoặc (c) trước GĐ5 | H7 | GĐ5 |

## 3. Gói bắt đầu

**A1, A2, A3, A4, A6, B1, C5, C7, E1, E2, E4, E5, E6.** Thêm **D1, D2** nếu muốn nghiên cứu giao diện chạy song song từ tuần đầu (khuyến nghị).

## 4. Chi tiết

### Nhóm A — Chiến lược và vận hành

**A1 · Định vị.**

- **(a) Khuyến nghị:** "Thư viện tham chiếu văn học dân gian Việt Nam" ([50](50-tai-dinh-vi-chien-luoc.md) mục 3). Câu khẩu hiệu công khai do bạn viết.
- **(b)** Giữ định vị hiện tại.
- Ảnh hưởng: thứ tự ưu tiên của mọi giai đoạn.

**A2 · Năng lực biên tập.**

- **(a) Khuyến nghị:** bắt đầu S0 (bạn + AI); tuyển 4–6 cộng tác viên (S1) trong 3–6 tuần đầu; xét một biên tập viên bán thời gian và 2 người thẩm định (S2) khi có tài chính.
- **(b)** Chỉ S0.
- Nếu (b): mục tiêu nội dung chia khoảng 3 — sau 6 tháng ~1.000 thay vì ~3.000 bài chuẩn tham chiếu ([52](52-chien-luoc-chat-luong-noi-dung.md) mục 7.3).
- **Đây là quyết định ảnh hưởng lớn nhất:** phần mềm không thay được giờ người duyệt.

**A3 · An toàn dữ liệu.**

- **(a) Khuyến nghị:**
  - bạn tạo trang thử riêng tư trong FlashPanel, có bản sao dữ liệu (1–2 giờ, agent hướng dẫn từng bước);
  - thử khôi phục một lần trước giai đoạn có di chuyển dữ liệu, sau đó mỗi quý;
  - đo hiệu năng trên trang thử.
- **(b)** Giữ cách làm hiện tại (chỉ Playground, không thử khôi phục).
- Nếu (b): agent **không nên** làm các bước di chuyển dữ liệu ở GĐ3.

**A4 · Gộp PR.**

- **(a) Khuyến nghị:**
  - rủi ro thấp: tự gộp như hiện nay;
  - giao diện người đọc: chờ bạn xem bản xem trước;
  - dữ liệu, mô hình, lập chỉ mục, quyền, đường dẫn: chờ bạn duyệt, cộng review độc lập và diễn tập trên trang thử.
- **(b)** Tự gộp mọi PR như hiện nay.

**A5 · Sổ đăng ký sự kiện đo.**

- **(a) Khuyến nghị:** một danh sách sự kiện duyệt một lần (chỉ đếm tổng hợp, không cookie, không địa chỉ IP, không định danh); thêm sự kiện mới chỉ cần ghi vào sổ và qua kiểm riêng tư.
- **(b)** Giữ "mỗi sự kiện một quyết định".

**A6 · Ba Human Gate mới.**

- **H7:** thu tiền, hợp đồng, pháp nhân, thoả thuận dữ liệu.
- **H8:** AI trả lời trực tiếp người đọc.
- **H9:** tài khoản, dữ liệu học sinh và trẻ em.
- Khuyến nghị: đồng ý.

### Nhóm B — Quyền và nguồn

**B1 · Chú thích chép từ Ca dao Mẹ** (thay D-046 (2), D-055, D-068, D-078, D-081, D-117 (2)).

- **(a) Khuyến nghị:**
  - gửi thư tuần này;
  - **bật lại ghi nguồn tại chỗ** cho chú thích chép ("Theo Ca dao Mẹ ↗", liên kết có `nofollow` — không hại SEO);
  - chạy chương trình thay chú thích cho Bộ tham chiếu ([52](52-chien-luoc-chat-luong-noi-dung.md) mục 5);
  - nếu Ca dao Mẹ từ chối: tắt chép, ẩn toàn bộ.
- **(b)** Gửi thư + **ẩn** chú thích chép khỏi trang công khai ngay (dữ liệu giữ nội bộ) + thay dần. Trang mỏng hơn trong thời gian chờ.
- **(c)** Giữ như hiện nay. **Không khuyến nghị:** rủi ro quyền tăng theo lượt truy cập từ Google; chặn dữ liệu mở, sách, hợp tác.
- Cả (a) và (b) chỉ là đổi cài đặt hồ sơ nguồn, không cần mã mới.

**B2 · Nhãn phụ Tục ngữ / Thành ngữ** (sửa D-079).

- Khuyến nghị: một mục, một đường dẫn như cũ; thêm nhãn phụ do máy gợi ý và người xác nhận, chỉ hiện khi đã xác nhận.
- Lý do: chương trình Ngữ văn dạy phân biệt hai loại.

### Nhóm C — Kiến trúc ([54](54-kien-truc-dich-va-chuyen-doi.md))

**C1 · Bảng quan hệ.**

- **(a) Khuyến nghị:** bảng `wp_cadao_lien_ket` có kiểu, bằng chứng, trạng thái (máy chỉ gợi ý, người duyệt); danh mục quan hệ ở 54 mục 5.
- **(b)** Giữ quan hệ rải trong meta (như 46).
- Nếu (b): không có Đọc tiếp theo nhân vật, điển tích, họ dị bản; nợ kỹ thuật tăng theo mỗi loại quan hệ.

**C2 · Hồ sơ thực thể.**

- **B2 (khuyến nghị):** loại bài riêng, đường dẫn `/nhan-vat/…`, `/dia-danh/…`, `/dien-tich/…`, tách khỏi Từ điển.
- **B1:** mở rộng mục từ Từ điển, rẻ hơn nhưng lẫn hai khái niệm.

**C3 · Bài viết.**

- Khuyến nghị: dùng loại `post` đang có (46 bài); đường dẫn `/bai-viet/` (301 cho đường cũ — H6); người viết, biên tập, thẩm định, thư mục; chỉ hiện ở trang tác phẩm khi bài đã tích "Đã có quyền đăng".
- **Cần bạn trả lời thêm:** tên ba nhóm — đề xuất Khảo cứu · Bình giảng · Chuyên đề.

**C4 · Họ dị bản và gộp.**

- Khuyến nghị: máy gợi ý cặp dị bản giữa các bài đã đăng; người liên kết thành họ hoặc gộp.
- Gộp có xem trước, có hoàn tác, chuyển hướng 301; mỗi lô gộp do bạn duyệt (H6).

**C5 · Xưởng biên tập, cấp mới, quyền ảnh và âm thanh.**

- Mở rộng bảng việc (vai cần, hạng, người giữ thẻ, kiểm mẫu).
- Thêm vai Thẩm định.
- Định nghĩa lại cấp L0–L4 ([52](52-chien-luoc-chat-luong-noi-dung.md) mục 2.1), tính sẵn trong bảng tóm tắt.
- Thêm trường quyền có cấu trúc cho tệp ảnh, âm thanh.
- Khuyến nghị: đồng ý.

**C6 · Trang ứng dụng.**

- Trang đọc giữ ngân sách hiện tại (≤ 21 KB JavaScript, tắt JavaScript vẫn đọc).
- Play, Học, Xưởng có ngân sách riêng (≤ 80 KB; Xưởng ≤ 150 KB); dùng module trình duyệt, không framework nặng.
- Khuyến nghị: đồng ý.

**C7 · Nền tảng.**

- Giữ WordPress và mã riêng: không giao diện headless, không cơ sở dữ liệu đồ thị.
- Mọi kênh ngoài trang đọc đi qua một lớp lọc quyền.
- Máy tìm ngoài chỉ khi số đo vượt ngưỡng.
- Khuyến nghị: đồng ý.

### Nhóm D — Trải nghiệm ([53](53-thiet-ke-lai-trai-nghiem.md))

**D1 · Thiết kế lại.**

- **(a) Khuyến nghị:** đi qua bốn cổng (chuẩn so sánh → khái niệm → bản mẫu bấm được, thử 8–10 người → kiểm kỹ thuật → bạn duyệt).
- Phạm vi: năm mẫu thể loại, Hồ sơ bài, hồ sơ thực thể, bài viết, tuyển tập, trang chủ, Khám phá, danh sách; IA mới (Đọc · Khám phá · Học · Chơi · Tra cứu); mời góp còn một dòng trên trang đọc.
- Vị trí Ý nghĩa của vè quyết trong bản mẫu. Khuyến nghị: bối cảnh ngắn trước lời, Ý nghĩa đầy đủ sau lời.
- **(b)** Chỉ chỉnh từng điểm trên giao diện hiện tại.

**D2 · Hướng thị giác.**

- Thử A "Giấy và mực" và B "Tím – lục tinh chỉnh" trên cùng nội dung; giữ phông.
- Sàn cỡ chữ lời trên điện thoại 18 px; dưới sàn thì xuống dòng có thụt treo thay vì co nhỏ.
- Khuyến nghị: đồng ý thử.

**D3 · Nhãn mức biên tập cho người đọc** (Lưu trữ · Đã biên tập · Tham chiếu).

- Khuyến nghị: thử trong bản mẫu, quyết sau PG2.

### Nhóm E — Nội dung và biên tập ([52](52-chien-luoc-chat-luong-noi-dung.md))

**E1 · Hai tầng, Bộ tham chiếu, lập chỉ mục.**

- Bộ tham chiếu ~3.000 bài (toàn bộ vè, câu đố; ~800 tục ngữ; ~1.150 ca dao; ~150 đồng dao), hạng A 500 do bạn và người thẩm định chọn.
- Phần còn lại là Kho lưu trữ, vẫn đọc được, không vào lối vào nổi bật.
- Chỉ bài đạt chuẩn tham chiếu mới lên Google (nâng từ "có một phần riêng").
- Khuyến nghị: đồng ý.

**E2 · Xưởng biên tập có AI.**

| Câu hỏi | Khuyến nghị |
|---|---|
| Cách chạy | **P1 tệp** — agent chạy ngoài WordPress, khoá API không đặt trên máy chủ; xét P2 (plugin gọi API) sau 3 tháng |
| Mô hình | **Claude Opus 5.5** mặc định; Sonnet 5.5 hoặc Haiku 5.5 cho một số việc **chỉ khi** bộ đánh giá 200 bài cho thấy chất lượng giữ được — bạn quyết |
| Ngân sách | Trần **100 USD / tháng** giai đoạn đầu. Ước một lượt cho 3.000 bài: ~60–115 USD với Opus 5.5 (52 mục 6.5) |
| Lô | Theo năng lực người duyệt, không cố định 30–50 |
| Dữ liệu gửi đi | Chỉ lời đã đăng, phần thư viện viết, nguồn được phép. Không dữ liệu cá nhân, không chú thích của nguồn chưa đồng ý |

Không nội dung máy soạn nào công khai khi chưa có người duyệt — giữ nguyên.

**E3 · Nguồn.**

- Cho phép nhận dạng chữ từ bản quét sách hết thời hạn bảo hộ, với điều kiện người so từng dòng với ảnh trang (làm rõ lằn ranh 1).
- Sách hiện đại còn bản quyền chỉ dùng để đối chiếu và ghi số trang.
- Khuyến nghị: đồng ý.

**E4 · Thẩm định.**

- Bài hạng A có người thẩm định ghi tên hoặc bút danh, ngày, phạm vi.
- Tách khỏi mức kiểm của lời.
- Khuyến nghị: đồng ý.

**E5 · Sổ tay biên tập.**

- Một chuẩn chung cho người duyệt và cho lời nhắc AI.
- Bạn duyệt bản nháp đầu (việc E1.4.1).

**E6 · Thô tục.**

- Máy gợi ý từ danh sách từ và AI; người xác nhận.
- Rà xong 765 câu đố trước khi mở Play.
- Khuyến nghị: đồng ý.

### Nhóm F — PhongDao NEXT ([55](55-lo-trinh-va-backlog.md) mục 7–8)

**F1 · Danh mục sản phẩm.**

- Xếp Next / Later / Explore, mỗi sản phẩm có điều kiện khởi động. Không sản phẩm nào bị coi là "không bao giờ", trừ những điều trái lằn ranh.
- **Next:** Play, Học, Bộ của tôi, PWA, bộ sưu tập thử nghiệm.
- **Later:** dữ liệu mở, API, nhúng, Creator Studio đầy đủ, tuyến di sản, ấn phẩm, lưu trữ sống.
- **Explore:** "Hỏi thư viện", truyện tương tác.

**F2 · Trò chơi hoá.**

- Tiến độ cá nhân lưu trên máy, huy hiệu không so sánh, thử thách lớp do giáo viên tạo (khi có tài khoản giáo viên).
- Không bảng xếp hạng công khai cho trẻ, không chuỗi ngày ép quay lại, không thưởng vật chất.

**F3 · Tài khoản.**

- Người đọc vẫn không cần tài khoản.
- Cộng tác viên dùng tài khoản biên tập.
- Giáo viên có tài khoản ở GĐ5 với dữ liệu tối thiểu (H9).

**F4 · "Hỏi thư viện".**

- Chỉ trả lời từ nội dung đã duyệt, mỗi câu trả lời dẫn tới bài, từ chối khi không có căn cứ, gắn nhãn AI.
- Bộ đánh giá trích dẫn đúng ≥ 95%.
- Không bao giờ để AI sáng tác lời dân gian.

**F5 · PWA.** Khuyến nghị: đồng ý ở GĐ4.

**F6 · Bộ sưu tập thử nghiệm.** Thể loại mới thử với nhãn "thử nghiệm", không lên menu, không lập chỉ mục; lên menu khi qua cổng D-091.

**F7 · Hướng tài chính, pháp nhân** (Q-008).

| Phương án | Nội dung |
|---|---|
| **(a)** | Phi lợi nhuận, nhận tài trợ có ghi nhãn rõ |
| (b) | Thư viện phi lợi nhuận + dịch vụ có thu (học liệu cho trường, tuyến di sản, ấn phẩm) qua một pháp nhân hoặc đối tác |
| (c) | Doanh nghiệp xã hội |

Khuyến nghị: (a) bây giờ; quyết (b) hoặc (c) trước khi mở bất kỳ sản phẩm có thu nào ở GĐ5. Thư viện miễn phí, không quảng cáo trong mọi phương án.

## 5. Việc của người sáng lập trong GĐ0 (khoảng 10 giờ, rải trong 3 tuần)

| Việc | Thời gian ước | Ghi chú |
|---|---|---|
| Trả lời gói quyết định này | 1–2 giờ | Có thể trả lời "Đồng ý các khuyến nghị" |
| Tạo trang thử trong FlashPanel, bản sao dữ liệu | 1–2 giờ | Agent hướng dẫn từng bước; agent không tự đổi cấu hình máy chủ |
| Thử khôi phục cùng agent | ~1 giờ | Ghi biên bản |
| Gửi thư Ca dao Mẹ; đổi cài đặt chú thích theo B1 | ~45 phút | Mẫu thư ở `docs/12` mục 7 |
| Nối Google Search Console | ~20 phút | |
| Lời kêu gọi cộng tác viên; liên hệ 2–3 trường, câu lạc bộ | ~2 giờ | Thông tin người đăng ký giữ ngoài kho mã |
| Mời 8–10 người thử trải nghiệm (chuẩn so sánh) | ~3 giờ | Agent soạn kịch bản thử, phiếu ghi |

## 6. Giai đoạn ưu tiên đầu tiên — kết quả nghiệm thu được

**GĐ0 + GĐ1, khoảng 12 tuần từ ngày duyệt** ([55](55-lo-trinh-va-backlog.md) mục 2–3):

| # | Kết quả | Cách nghiệm thu |
|---|---|---|
| 1 | Trang thử có dữ liệu thật; khôi phục sao lưu thành công | Biên bản khôi phục, số bài khớp |
| 2 | Chú thích chép không còn hiện mà không ghi nguồn; thư đã gửi | Kiểm HTML khách chỉ đọc; ngày gửi thư |
| 3 | Số đo nền: độ phủ theo thể loại, số cặp trùng, chuẩn so sánh trải nghiệm, Search Console | Bảng trong Số liệu; báo cáo UX-0 |
| 4 | Xưởng biên tập: hàng duyệt theo vai và hạng, nháp nhiều phần, 9 trình kiểm, kiểm mẫu, số đo | Bốn cổng kiểm đạt; trung vị duyệt ≤ 4 phút / bài hạng B đo trên người thật |
| 5 | Bộ đánh giá 200 bài đạt ngưỡng trước khi chạy lớn | Báo cáo: ≥ 60% duyệt không sửa, lỗi nghiêm trọng ≤ 1% |
| 6 | **1.000 bài đạt chuẩn tham chiếu** (S1; S0: ~400), gồm 215 bài cũ đã đính chính | Bảng độ phủ; kiểm mẫu lỗi lớn ≤ 3%, nghiêm trọng 0 |
| 7 | 765 câu đố đã rà thô tục; ≥ 400 câu có Giải thích đáp án | Danh sách trong Bổ sung |
| 8 | Sổ tay biên tập v1 được duyệt; 3–6 người duyệt hoạt động | Tệp hướng dẫn; số đo Xưởng |
| 9 | Bản mẫu trải nghiệm qua PG1 và đang thử PG2 (nhánh giao diện chạy song song) | Biên bản PG1; lịch thử |

## 7. Khi đã trả lời

1. Agent làm E0.1: ghi D-xxx (mỗi mục một dòng, đúng lựa chọn của bạn), sửa AGENTS.md, `docs/00`, `26`, `27`, `28`, đưa việc vào `docs/22`. Một PR tài liệu, rủi ro cao (đổi luật chơi) → chờ bạn xem và một review độc lập trước khi gộp (A4).
2. Agent hướng dẫn bạn tạo trang thử, thử khôi phục.
3. Bắt đầu GĐ1 và nhánh nghiên cứu giao diện song song.
4. Mỗi hai tuần một bản tin ngắn bằng lời thường: đã xong gì, số đo, cần bạn quyết gì.
