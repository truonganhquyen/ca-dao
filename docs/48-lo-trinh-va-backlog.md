# 48 · Lộ trình theo giai đoạn và backlog ưu tiên — V2 Core và Next

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Lịch sử (21/11/2026)** — định hướng, kiến trúc, lộ trình đã thay bởi `docs/50`–`57`; kế hoạch đang dùng: [57](57-ke-hoach-chot.md). Nguyên trạng lúc lập: **Đề xuất, chờ người sáng lập duyệt.** Mã việc ở đây (C0.1, N1.2…) là mã tạm; khi duyệt, agent chuyển thành T-xxx trong `docs/22` theo [47](47-ke-hoach-tong-core-next.md) mục 6. Trước đó không ai nhận việc từ tệp này |
| Đọc khi | Nhận việc, xếp thứ tự, nghiệm thu |
| Bộ tài liệu | [45 kiểm kê, đối chiếu](45-kiem-ke-va-doi-chieu.md) · [46 kiến trúc, khuyến nghị](46-kien-truc-va-khuyen-nghi.md) · [47 kế hoạch tổng](47-ke-hoach-tong-core-next.md) · **48 lộ trình, backlog (tệp này)** · [49 quyết định, câu hỏi mở](49-quyet-dinh-va-cau-hoi-mo.md) |

**Ai:** **N** người sáng lập · **A** agent · **N+A** agent làm, người sáng lập quyết. **Ưu tiên:** **P0** làm trước (niềm tin, gỡ chặn) · **P1** giá trị cao · **P2** nên làm · **P3** khi đủ điều kiện. **Độ phức tạp:** **S** vài ngày agent · **M** một đến hai tuần · **L** nhiều tuần hoặc phụ thuộc nội dung. **Gate:** Human Gate trong AGENTS.md; "QĐ-n" là quyết định cần duyệt ở 49.

---

## 1. Bản đồ Now · Next · Later · Not Recommended

### 1.1. PHONGDAO V2 — Core

| Nhóm | Việc |
|---|---|
| **Now** (giai đoạn 0–1) | Ghi quyết định, đưa việc vào `docs/22`, sửa lệch tài liệu – mã · Sổ tay biên tập v1 · Năm máy nhắc mới ở Cần sửa · Bảng độ phủ theo thể loại · Hàng "Ghi chú" · Danh sách 7 trang cần xem · Vòng nháp Ý nghĩa hằng tuần · Chọn 20 bài mẫu |
| **Next** (giai đoạn 2–3) | Loại mục từ (Từ ngữ · Địa danh · Nhân vật · Điển tích) · Gợi ý Địa danh · Lọc Từ điển theo loại · "Trong bài có: …" · Đọc tiếp lý do theo loại · Bài viết nối tác phẩm · Mẫu trang bài viết · Đố vui · Trò chơi có lọc · Khối Biên soạn · Sao chép cả bộ |
| **Later** (giai đoạn 4, có điều kiện) | Đối chiếu sách Wikisource · Cho lớp học, tấm sửa SGK, phiếu in · Dị bản hai cột · Quan hệ đối đáp (khi có ≥ 10 cặp) · Đọc thêm ngoài (khi có ≥ 10 liên kết) · Ca kiểm phóng 200% · Sửa tại chỗ mô tả mô-típ / chủ đề / vùng · Gợi ý mô-típ, Wikidata thành việc chờ · Dọn dữ liệu MVP · Thể loại mới, truyện, giọng đọc sinh sẵn · Tìm theo tên cũ địa danh · Lọc Tìm theo vùng, mô-típ |
| **Not Recommended** | Loại bài, bảng mới theo ĐT-ND (`editorial_article`, `knowledge_entry`, `source_record`, `work_version`, `content_relation`) · Trạng thái quyền ở cấp tác phẩm (D-027) · Chip thể loại riêng cho thành ngữ (D-079) · Đưa Ý nghĩa của vè xuống sau lời (giữ D-119, rút gọn nội dung) · Nhãn "chuyên gia thẩm định" (D-013) · Tìm ngữ nghĩa bằng AI ở giai đoạn này (cổng D-015) · Plugin ngoài cho nội dung, SEO, "bài liên quan" |

### 1.2. PHONGDAO NEXT — Future Products (chỉ hoạch định)

| Nhóm | Việc |
|---|---|
| **Now** | Không viết mã. Chỉ chốt cổng N0: hướng tài chính (Q-008), thư Ca dao Mẹ, ngưỡng nội dung, quy tắc dùng lại |
| **Next** (sau cổng N0) | Lớp lọc dùng lại · Xuất dữ liệu mở có phiên bản · "Bộ của tôi" từ Đã lưu (phiếu in, ảnh, trình chiếu) — bản Creator Studio không tài khoản · Khung nhúng "Câu hôm nay", "Đố một câu" |
| **Later** (khi có đối tác hoặc người vận hành) | Tuyến di sản, triển lãm với đối tác · API đọc cho đối tác · Tuyển tập in, sách · Ghi âm, ký ức cộng đồng có đồng ý · Người soát lời tình nguyện · Bản đồ tiếng nói · Đêm văn hoá gia đình (gói nội dung) |
| **Not Recommended** | Trợ lý nghiên cứu, chatbot AI · Tài khoản, thuê bao, watermark, thanh toán trước khi có pháp nhân · AR, 3D, ứng dụng iOS / Android · Microsite theo đơn · Cuộc thi sáng tạo · Truyện tương tác nhiều nhánh · Giọng AI giả giọng vùng · Điểm thưởng, bảng xếp hạng, chuỗi ngày · Bán "độc quyền" phần đã CC BY |

## 2. Lộ trình tương đối (tính từ ngày duyệt)

| Tuần | V2 Core | NEXT |
|---|---|---|
| 1 | Giai đoạn 0: một PR tài liệu (C0.1–C0.3); bắt đầu Sổ tay (C1.1) | — |
| 2–3 | Giai đoạn 1: máy nhắc, bảng độ phủ, hàng Ghi chú (một PR); lô nháp 1, 2; người sáng lập xem 7 trang | — |
| 4–7 | Giai đoạn 2: loại mục từ và Từ điển (PR 1), bài viết nối tác phẩm và mẫu trang (PR 2); lô nháp tiếp | — |
| 8–10 | Giai đoạn 3: Đố vui, Trò chơi, khối Biên soạn, Sao chép cả bộ (1–2 PR) | — |
| 11–12 | Rà chỉ số (47 mục 8); chọn việc giai đoạn 4 | Xét cổng N0 |
| Sau 12 | Giai đoạn 4 theo nội dung | N1 nếu qua cổng |

Tuần chỉ là ước lượng cho agent; vòng nội dung đi theo giờ thật của người sáng lập. Giai đoạn 2 và 3 đổi chỗ được nếu người sáng lập muốn phần cho trẻ (Đố vui, Trò chơi) trước.

## 3. Backlog V2 Core

### E0 · Chốt và dọn nền (giai đoạn 0)

| ID | Việc | Ai | Phụ thuộc | Ưu tiên | Độ phức tạp | Gate |
|---|---|---|---|---|---|---|
| C0.1 | Ghi quyết định đã duyệt vào `docs/01`; đưa việc vào `docs/22`; cập nhật `docs/02`, bảng tài liệu AGENTS.md, `du-an/trang-thai.json` | A | Người sáng lập trả lời 49 | P0 | S | Chỉ ghi điều người sáng lập đã chọn |
| C0.2 | Đối soát phạm vi T-185: bỏ phần đã làm (nạp nháp theo lô — T-210; màn Tuyển tập — T-208), tách phần còn lại vào C3.1–C3.3, C4.2, C4.7, C4.8 | A | C0.1 | P0 | S | — |
| C0.3 | Sửa tám chỗ lệch tài liệu – mã (45, mục 5: L1–L8) | A | C0.1 | P0 | S | — |

**Nghiệm thu E0:** mỗi QĐ được duyệt có đúng một dòng D-xxx; không việc nào của giai đoạn 2 trở đi ở `READY`; dòng T-185 chỉ còn việc chưa có, mỗi phần đã làm có tham chiếu task/PR; `docs/26` mục 3, 4.2, 4.3 và `docs/27` mục 7.1, 7.4 khớp mã hoặc trỏ tới việc sẽ làm. **Kiểm:** `npm run kiem-tra` đạt; `npm run viec-tiep-theo` ra việc đầu của giai đoạn 1.

### E1 · Niềm tin và chất lượng dữ liệu (giai đoạn 1)

| ID | Việc | Ai | Phụ thuộc | Ưu tiên | Độ phức tạp | Gate |
|---|---|---|---|---|---|---|
| C1.1 | Sổ tay biên tập v1 (`docs/huong-dan/so-tay-bien-tap.md`) | A soạn, N duyệt | C0.1 | P0 | S | Người sáng lập duyệt câu chữ |
| C1.2 | Năm máy nhắc mới ở Bổ sung → Cần sửa (46, KT-5) | A | C0.1 | P0 | S | — |
| C1.3 | Bảng độ phủ theo thể loại ở Số liệu (46, KT-11) | A | C0.1 | P1 | S | — |
| C1.4 | Hàng "Ghi chú" cho chú thích chung không gắn từ (46, KT-6) | A | QĐ-4 | P1 | S | H4 nhỏ |
| C1.5 | Bảy trang ĐT-ND cần xem (49, mục 3): người quyết sửa hay giữ | N (A chuẩn bị) | — | P1 | S | H2 |
| C1.6 | Vòng nháp Ý nghĩa theo lô 30 bài, hằng tuần | N+A | C1.1, QĐ-7 | P0 | L, liên tục | H2; đồng ý từng lần nạp |
| C1.7 | Chọn 20 bài mẫu (4 mỗi thể loại) trong việc thử làm sâu và QA mẫu đang chờ | N | C1.1 | P1 | S | H2 |

**C1.1 · Sổ tay biên tập v1**
- *Nội dung bắt buộc:* (1) bảng ranh giới các phần — Ý nghĩa · Nghĩa đen / Nghĩa bóng / Dùng khi · Hình ảnh trong bài · Chú thích từ khó · Ghi chú chung · Chuyện quanh bài / Nguồn gốc · Dị bản · Bối cảnh (vè) · Cách chơi (đồng dao) · Giải thích đáp án (câu đố): mỗi phần ghi viết gì, không viết gì, độ dài, ví dụ cấu trúc bằng chữ mẫu; (2) cách viết điều chưa chắc ("Có cách hiểu cho rằng…"; không kể sự tích chắc chắn khi không có nguồn); (3) bài phản ánh chuẩn mực cũ: tả bối cảnh, không bình đạo lý; (4) ghi địa danh theo thời điểm (theo lựa chọn ở 49, Q-G); (5) khi nào bật cờ "Có thể không phải dân gian"; (6) quy tắc cho nháp AI — dẫn chiếu `nhap-y-nghia-theo-lo.md`, không chép lại; (7) mỗi thể loại: phần nào nên có trước (L1 → L2).
- *Nghiệm thu:* một tệp, mỗi phần có bảng "viết / không viết"; được liên kết từ `sua-day-du-va-phan-loai.md` và `nhap-y-nghia-theo-lo.md`; không có lời ca dao thật, chú thích chép từ nguồn hay dữ liệu kho trong tệp (kho mã công khai); người sáng lập duyệt.
- *Kiểm:* `npm run kiem-tra`; người sáng lập đọc trên điện thoại.

**C1.2 · Năm máy nhắc mới**
- *Nghiệm thu:* năm nhóm ở 46 (KT-5) hiện ở Bổ sung → Cần sửa, mỗi nhóm có số bài, nút mở đúng phần để sửa, "Bỏ qua" được nhớ; nhóm tên tuyển tập hiện ở cột "Còn thiếu gì" của màn Tuyển tập; không quy tắc nào ghi vào bài; lời, đáp án, câu hỏi không bị xét; đếm trên kho thử khớp đếm tay.
- *Kiểm:* PHP thuần mỗi quy tắc ≥ 2 ca dương và ≥ 2 ca âm (vd. "nay" trong "hôm nay" không bị bắt; "có bản" giữa câu không bị bắt; Hình ảnh trùng 59% không bị bắt); Playground mở rộng `kiem-can-sua.php` (bài mẫu có ghi chú dạng dị bản hiện ở nhóm; Bỏ qua thì ẩn; sửa xong thì hết nhắc); `quet:v2` màn Bổ sung và Tuyển tập 0 lỗi; tăng phiên bản plugin.

**C1.3 · Bảng độ phủ theo thể loại**
- *Nghiệm thu:* năm hàng thể loại × cột đã đăng · có phần riêng (lên Google) · L2 trở lên · thiếu Ý nghĩa (câu đố: thiếu giải thích; tục ngữ: thiếu nghĩa) · có cờ; số khớp đếm trực tiếp trên kho thử; màn Số liệu không chậm hơn 10% so với trước (`scripts/do-hom-nay-v2.mjs`).
- *Kiểm:* Playground ca số theo thể loại với kho thử có đủ năm thể loại và các trạng thái; `quet:v2` màn Số liệu.

**C1.4 · Hàng "Ghi chú"**
- *Nghiệm thu:* bài có cả `chuyen_quanh` và chú thích chung hiện hai hàng đúng tên; tục ngữ giữ "Nguồn gốc" cho mục trùng cả câu; chế độ sửa giữ nút "Chuyển sang Chuyện quanh bài"; bài không có thì không in gì; bài thô tục vẫn che như cũ.
- *Kiểm:* Playground (dựng hai hàng); `quet:v2` mẫu ca dao, tục ngữ.

**C1.5 · Bảy trang cần xem** — *Nghiệm thu:* mỗi trang ở 49 mục 3 có quyết định của người sáng lập (sửa / giữ kèm lý do); agent không sửa nội dung.

**C1.6 · Vòng nháp Ý nghĩa** (đường D-117 đã có: Bổ sung → "Nháp Ý nghĩa theo lô")
- *Mỗi lô:* người sáng lập tải tệp lô (30 bài, ưu tiên đọc nhiều, tìm nhiều; câu đố là Giải thích đáp án); agent soạn nháp theo Sổ tay ở ngoài kho mã; người sáng lập nạp tệp → xem trước → duyệt hoặc bỏ từng thẻ ở Hộp việc.
- *Nghiệm thu mỗi lô:* không nháp nào vào bài khi chưa bấm duyệt (ca kiểm có sẵn); người sáng lập ghi thời gian duyệt; bảng độ phủ tăng đúng số bài đã duyệt; tệp lô không vào repo.

**C1.7 · Bài mẫu** — *Nghiệm thu:* 20 đường dẫn (4 mỗi thể loại) và thời gian, chỗ vướng ghi theo `docs/huong-dan/thu-lam-sau.md`; chấm theo Sổ tay; bằng chứng ghi vào T-180 và T-198 (không tạo task mới).

### E2 · Mạng tri thức tối thiểu (giai đoạn 2)

| ID | Việc | Ai | Phụ thuộc | Ưu tiên | Độ phức tạp | Gate |
|---|---|---|---|---|---|---|
| C2.1 | Loại mục từ: trường `_phongdao_loai_muc`, ô chọn ở tấm sửa mục từ, lưu có Lịch sử | A | QĐ-2 | P1 | M | H1 |
| C2.2 | Gợi ý "Địa danh" cho mục có toạ độ Wikidata đã xác nhận | A | C2.1 | P2 | S | (trong QĐ-2) |
| C2.3 | Lọc Từ điển theo loại; trang mục từ theo loại | A | C2.1 | P1 | M | H4 nhỏ |
| C2.4 | Dòng "Trong bài có: …" trên trang tác phẩm | A | C2.1 | P2 | S | H4 nhỏ |
| C2.5 | Đọc tiếp: lý do và thứ tự theo loại mục từ | A | C2.1 | P2 | S | H4 nhỏ |
| C2.6 | Bài viết nối tác phẩm: trường, hộp sửa, chỉ mục ngược, hàng "Bài viết về bài này" | A | QĐ-3 | P1 | M | H1 + H4 |
| C2.7 | Mẫu trang bài viết; ba chuyên mục | A | C2.6 | P2 | M | H4 |
| C2.8 | Trang Khám phá có mục "Bài viết" khi ≥ 3 bài đã có quyền và đã nối | A | C2.6 | P3 | S | H4 |

**C2.1 · Loại mục từ**
- *Nghiệm thu:* người có `cadao_soan` chọn loại ở tấm sửa mục từ; Lịch sử ghi trước / sau, khôi phục được; mục chưa chọn hiện như Từ ngữ; không lệnh, lịch chạy hay bước nâng cấp nào ghi loại; người không có quyền bị từ chối.
- *Kiểm:* PHP thuần (giá trị hợp lệ, thiếu = `tu`, giá trị lạ bị từ chối); Playground (lưu, khôi phục, quyền; lằn ranh: chạy lịch và bước nâng cấp không đổi loại); `quet:v2` trang mục từ ở chế độ sửa.

**C2.2 · Gợi ý Địa danh**
- *Nghiệm thu:* danh sách ở Bổ sung, cạnh "Gợi ý Từ điển"; "Đồng ý" từng mục hoặc cả nhóm qua xem trước (đường lô `XuLyLo`); "Bỏ" được nhớ; không mục nào đổi loại khi chưa bấm.
- *Kiểm:* Playground ca có / không toạ độ, ca đã có loại khác (không gợi ý lại).

**C2.3 · Lọc Từ điển, trang mục từ theo loại**
- *Nghiệm thu:* chip lọc bằng GET, có số mục, ẩn loại 0, chạy khi tắt JavaScript; trang mục từ có nhãn loại; Điển tích, Nhân vật dùng khối "Câu có chữ …" với tiêu đề theo loại; luật lập chỉ mục mục từ không đổi (D-068, D-106); trang đã lọc `noindex` như mọi trang lọc.
- *Kiểm:* Playground (đếm, lọc); `quet:v2` thêm `/tu-dien/?loai=dien_tich` và một mục từ mỗi loại; `e2e:v2` lọc bằng bàn phím.

**C2.4 · "Trong bài có: …"**
- *Nghiệm thu:* chỉ mục từ khác Từ ngữ mà chú thích của bài **đã được người nối**; tối đa 6, dẫn sang mục từ; bài không có thì không in gì; bài thô tục: nằm trong khối che.
- *Kiểm:* Playground (vè mẫu nối hai nhân vật → hiện hai); `quet:v2` mẫu vè.

**C2.5 · Đọc tiếp theo loại**
- *Nghiệm thu:* lý do "Cùng điển tích …", "Cùng nhắc tới nhân vật …", "Cùng nhắc tới nơi …"; thứ tự đề xuất: cùng tuyển tập → điển tích → mô-típ → nhân vật → chủ đề → địa danh, từ ngữ → vùng; vẫn tối đa 3, không lấp "cùng thể loại", không bài thô tục (D-111, D-119).
- *Kiểm:* PHP thuần hoặc Playground với bộ ca hai bài chung điển tích và chung chủ đề (điển tích đứng trước).

**C2.6 · Bài viết nối tác phẩm**
- *Nghiệm thu:* người biên soạn thêm, bỏ, sắp tác phẩm ở hộp Phongdao của bài viết (tìm, dán đường dẫn, số ID) kèm ghi chú tuỳ chọn; chỉ tác phẩm đã đăng được nối; trang tác phẩm hiện tối đa 3 bài viết **đã đăng và đã tích "Đã có quyền đăng"**; bỏ tích quyền, ẩn hay bỏ bài viết vào thùng rác thì hàng biến mất ngay; luật lập chỉ mục bài viết không đổi (D-126); không thêm JavaScript trên trang tác phẩm.
- *Kiểm:* Playground (nối → hiện; chưa tích quyền → không; thùng rác → không; dựng lại chỉ mục ngược bằng lệnh WP-CLI); `quet:v2` trang tác phẩm có hàng mới; `e2e:v2` từ tác phẩm mở bài viết rồi quay lại; HTML khách không có phần sửa.

**C2.7 · Mẫu trang bài viết**
- *Nghiệm thu:* nhãn nhóm (chuyên mục); "Người viết" chỉ khi người biên soạn nhập; "Biên tập: …" khi có bút danh (C3.3); ngày đăng, ngày sửa; "Các tác phẩm trong bài" bằng thẻ bài có sẵn; mục lục thu gọn bằng `details` khi có từ 4 tiêu đề; ≤ 200 KB; tắt JavaScript đọc được; tên ba chuyên mục theo người sáng lập chốt.
- *Kiểm:* `quet:v2` thêm một bài viết mẫu; Playground (bài không nối thì không có khối; "Người viết" trống thì không in).

**C2.8 · Khám phá có mục Bài viết** — *Nghiệm thu:* mục chỉ hiện khi đủ ngưỡng; dưới ngưỡng không có tiêu đề rỗng. *Kiểm:* Playground hai ca dưới / trên ngưỡng; `quet:v2` trang Khám phá.

### E3 · Hoàn thiện trải nghiệm đã thiết kế (giai đoạn 3; phần còn lại của T-185)

| ID | Việc | Ai | Phụ thuộc | Ưu tiên | Độ phức tạp | Gate |
|---|---|---|---|---|---|---|
| C3.1 | Đố vui `/do-vui/` (46, KT-12) | A | QĐ-6 | P1 | M | thiết kế đã duyệt |
| C3.2 | Trò chơi `/tro-choi/` có lọc | A | C0.2 | P2 | M | thiết kế đã duyệt |
| C3.3 | Khối Biên soạn và bút danh trong Cài đặt (46, KT-11) | A | C0.2 | P1 | S | đã duyệt (D-080 (6)) |
| C3.4 | Sao chép cả bộ ở tuyển tập | A | C0.2 | P2 | S | đã duyệt (D-093 (6)) |

**C3.1 · Đố vui**
- *Nghiệm thu:* 10 câu một lượt theo chủ đề nhóm "Loại câu đố"; chỉ câu đủ điều kiện KT-12 (có Giải thích đáp án do người viết hoặc AI đã được duyệt, không thô tục, không cờ); Gợi ý, Hiện đáp án bằng `details`; "đã đố tới câu nào" nhớ trên máy, bọc try/catch; không điểm, không xếp hạng; tắt JavaScript: một trang 10 câu; trang tự ẩn khi dưới 10 câu đủ điều kiện; `noindex`; JavaScript trong ngân sách.
- *Kiểm:* Playground (câu thô tục, câu chưa có giải thích bị loại; đủ / thiếu 10 câu); `quet:v2` trang Đố vui; `e2e:v2` mở đáp án bằng bàn phím, "Câu khác".

**C3.2 · Trò chơi có lọc**
- *Nghiệm thu:* lọc GET theo số người, độ tuổi, đồ dùng (các ô đang có trong `_phongdao_cach_choi`); thẻ trò chơi có thông số, thiếu ô nào bỏ ô đó; trang ẩn khi dưới 3 trò chơi có cách chơi; không suy diễn luật chơi.
- *Kiểm:* Playground; `quet:v2`; `e2e:v2` lọc.

**C3.3 · Khối Biên soạn**
- *Nghiệm thu:* ô bút danh ở Nguồn & cài đặt → Cài đặt chung (chỉ Quản trị); hiện ở trang tác phẩm, tuyển tập ("Biên soạn: …, cập nhật …"), bài viết ("Biên tập: …"); để trống thì không hiện; liên kết trang Phương pháp; JSON-LD `editor` khi có; không tên thật nếu người sáng lập không tự gõ.
- *Kiểm:* Playground (trống / có); `quet:v2` ba loại trang.

**C3.4 · Sao chép cả bộ**
- *Nghiệm thu:* chép tên tuyển tập rồi mọi câu đánh số, giữ thụt câu lục, mỗi câu kèm đường dẫn bài; bỏ qua bài có từ ngữ thô tục và ghi rõ số bài bỏ qua; tính `trich_dan_sao_chep` một lần mỗi lần bấm; lỗi bộ nhớ tạm thì cho chọn chữ để chép tay.
- *Kiểm:* `e2e:v2` (sao chép trong Chrome); ngân sách JavaScript trang tuyển tập.

### E4 · Tư liệu, lớp học và hạng mục có điều kiện (giai đoạn 4 — Later)

| ID | Việc | Ai | Gộp với | Độ phức tạp | Gate / điều kiện |
|---|---|---|---|---|---|
| C4.1 | Đối chiếu lời với sách Wikisource (hai sách 1897, 1901), máy gợi ý cặp, người xác nhận | N+A | T-158 | L | H3 đã có cho hai sách (D-080); người xem ảnh trang và bấm |
| C4.2 | Tấm sửa SGK; Cho lớp học; phiếu in A4 | N+A | T-159, phần SGK của T-185 | L | H2: chỉ người đã thấy sách điền |
| C4.3 | So sánh dị bản hai cột trên máy tính | A | T-063 | S–M | H4 nhỏ |
| C4.4 | Quan hệ đối đáp (mở rộng `gan_nghia` sang ca dao, quan hệ `dap`) | A | — | S | H1; khi có ≥ 10 cặp có căn cứ |
| C4.5 | Đọc thêm ngoài: mỗi liên kết là hồ sơ nguồn loại web, có ngày kiểm | A | — | S | H4; khi có ≥ 10 liên kết |
| C4.6 | Ca kiểm phóng 200% và chữ "Rất lớn" trong `quet:v2` | A | — | S | — |
| C4.7 | Sửa tại chỗ mô tả mô-típ, chủ đề, vùng | A | phần còn lại của T-185 | M | — |
| C4.8 | Gợi ý mô-típ, Wikidata thành việc chờ | A | phần còn lại của T-185 | M | máy gợi ý, người duyệt |
| C4.9 | Dọn dữ liệu MVP còn trong cơ sở dữ liệu | N+A | T-164 | S | H6: người sáng lập đồng ý |
| C4.10 | Cổng thể loại mới, truyện, giọng đọc sinh sẵn | N+A | T-160, T-161, T-162 | L | Cổng D-091; H3 |

## 4. Backlog NEXT (chỉ hoạch định — `DEFERRED` tới khi qua cổng N0)

| ID | Việc | Ai | Phụ thuộc | Độ phức tạp | Gate / điều kiện |
|---|---|---|---|---|---|
| N0.1 | Quyết hướng tài chính, pháp nhân (Q-008) | N | — | — | quyết định |
| N0.2 | Gửi thư Ca dao Mẹ (T-034); chốt quy tắc dùng lại (QĐ-10) | N | — | — | H3 |
| N0.3 | Chốt ngưỡng nội dung mở thử (QĐ-9) | N | — | — | quyết định |
| N0.4 | Phỏng vấn ngắn 5 giáo viên, phụ huynh về "Bộ của tôi" và Đố vui nhúng; agent soạn câu hỏi, không lưu dữ liệu cá nhân vào repo | N+A | — | S | lằn ranh 3 |
| N1.1 | Lớp lọc dùng lại `MienDuLieu\TaiDung` + ca kiểm khẳng định | A | N0.2 | M | H3 (quy tắc) |
| N1.2 | Xuất dữ liệu mở có phiên bản, kèm giấy phép, danh sách mục đã gỡ; DOI | A | N1.1, N0.3 | M | H3 (phạm vi lời được xuất) |
| N1.3 | "Bộ của tôi": từ Đã lưu → phiếu in A4, ảnh, trình chiếu, sao chép kèm nguồn | A | N1.1 | M | H4 |
| N1.4 | Khung nhúng `/nhung/cau-hom-nay/`, `/nhung/do-mot-cau/` | A | N1.1, C3.1 | S–M | H4; D mới nếu muốn đếm lượt hiển thị |
| N2.1 | Thử tuyến di sản với một đối tác: tuyển tập theo địa điểm, mã QR trỏ trang có sẵn | N+A | N1.1, C2.1 | L | Có đối tác; từng điểm có nguồn, đã kiểm thực địa |
| N2.2 | API đọc cho đối tác (khoá, giới hạn tần suất) | A | N1.1 | M | Có thoả thuận; H3, H4 |
| N2.3 | Tuyển tập in, PDF | A | N1.3 | M | Q-008 nếu bán |
| N3.1 | Ghi âm cộng đồng có đồng ý (`docs/luu-tru/41-ghi-am-va-dong-y.md`) | N+A | — | L | T-067; H3; lằn ranh 3 |
| N3.2 | Người soát lời tình nguyện qua "Gửi số trang" | N+A | — | M | T-134 |
| N3.3 | Bản đồ tiếng nói theo vùng (nghiên cứu) | N+A | N3.1 | L | Đủ dữ liệu có nguồn |

**Nghiệm thu chung cho mọi kênh NEXT:** ca kiểm khẳng định không chú thích chép từ nguồn chưa đồng ý, không nháp, không `nguon_may`, không bài thô tục (trừ kênh người lớn chọn) xuất hiện trong bất kỳ đầu ra nào; mỗi đầu ra có đường dẫn về bài và dòng nguồn; không cookie, không thu IP; sửa hay ẩn một bài thì kênh đọc thẳng cập nhật ngay, kênh xuất tệp có trong danh sách gỡ của bản sau.

## 5. Phụ thuộc và ánh xạ việc cũ

### 5.1. Sơ đồ phụ thuộc

```text
Người sáng lập trả lời 49
 └─ C0.1 ── C0.2, C0.3
     ├─ C1.1 ──┬─ C1.6 (lặp hằng tuần)
     │         └─ C1.7
     ├─ C1.2, C1.3            (không cần quyết định thêm)
     ├─ C1.4                  (khi QĐ-4)
     ├─ C2.1 (khi QĐ-2) ──┬─ C2.2, C2.3, C2.4, C2.5
     ├─ C2.6 (khi QĐ-3) ──┬─ C2.7 (dòng "Biên tập" cần C3.3) ── C2.8
     ├─ C3.1 (khi QĐ-6), C3.2, C3.3, C3.4
     └─ E4 (Later, theo điều kiện từng việc)

N0.1, N0.2, N0.3 ── N1.1 ──┬─ N1.2, N1.3, N1.4 ── N2.*
                            └─ N3.* (độc lập, cần H3)
```

### 5.2. Ánh xạ với việc đang có trong `docs/22`

| Việc cũ | Trạng thái hiện tại | Trong kế hoạch này |
|---|---|---|
| T-034 Thư Ca dao Mẹ | READY (N) | N0.2 — điều kiện của mọi kênh NEXT |
| T-180 Thử làm sâu 20–30 bài | IN_PROGRESS (N+A) | C1.7 (chọn 20 bài mẫu nằm trong việc này) |
| T-198 QA mẫu 10 bài, 5 thể loại | IN_PROGRESS (N+A) | C1.7 |
| T-183 Thử nhỏ lối đọc chủ đề, bài viết dài, công cụ giáo viên | BLOCKED | Tách: bài viết dài → C2.6–C2.7; công cụ giáo viên → N1.3; lối đọc theo chủ đề giữ ở T-183, chờ kết quả C1.7 |
| T-185 Phần thiết kế V2 chưa làm | READY | C0.2 đối soát → C3.1–C3.3, C4.2, C4.7, C4.8 |
| T-158 Đối chiếu sách | READY | C4.1 |
| T-159 Cho lớp học | READY | C4.2 |
| T-063 So sánh dị bản | DEFERRED | C4.3 |
| T-062 Bộ bài từ Đã lưu, in cả bộ | DEFERRED | N1.3 |
| T-163, T-066 Dữ liệu mở, DOI | READY / DEFERRED | N1.2 |
| T-067 Ghi âm cộng đồng | DEFERRED | N3.1 |
| T-134 Người soát lời tình nguyện | DEFERRED | N3.2 |
| T-160, T-161, T-162 Giọng đọc, thể loại mới, truyện | READY | C4.10 |
| T-164 Dọn dữ liệu MVP | READY (H6) | C4.9 |

Khi đưa vào `docs/22` (C0.1): việc có cột "Gộp với" thì **sửa dòng cũ** (phạm vi, tiêu chí, phụ thuộc), không thêm dòng trùng; việc mới (C1.1–C1.4, C2.1–C2.8, C3.4, C4.4–C4.6, N1.1, N1.4, N2.*, N3.3) mới cần số T mới.
