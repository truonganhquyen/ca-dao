# 53 · Thiết kế lại trải nghiệm — kế hoạch "Trải nghiệm tham chiếu"

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Căn cứ (D-127, 21/11/2026)** — kế hoạch đang dùng: [57](57-ke-hoach-chot.md); nơi khác nhau, 57 thắng. Nguyên trạng lúc lập: **Đề xuất, chờ người sáng lập duyệt** ([56](56-goi-quyet-dinh.md) nhóm D). Mọi thay đổi giao diện lớn đi qua bản mẫu và thử người dùng trước khi viết mã (Human Gate H4) |
| Bộ tài liệu | [50 tầm nhìn](50-tai-dinh-vi-chien-luoc.md) · [51 rà soát quyết định](51-danh-gia-lai-quyet-dinh.md) · [52 chất lượng nội dung](52-chien-luoc-chat-luong-noi-dung.md) · **53 trải nghiệm** · [54 kiến trúc](54-kien-truc-dich-va-chuyen-doi.md) · [55 lộ trình, backlog](55-lo-trinh-va-backlog.md) · [56 gói quyết định](56-goi-quyet-dinh.md) |
| Căn cứ | Ảnh chụp site thật ngày 21/11/2026 (chỉ GET), khổ 390 × 844 và 1440 × 900: trang chủ, một bài ca dao, tục ngữ, đồng dao, câu đố, vè, Tuyển tập, trang thể loại ca dao, Từ điển, Khám phá. Ảnh lưu ngoài kho mã. `docs/27`, bộ thiết kế `design/v2/` |

---

## 1. Cách đánh giá

"Kiểm thử đạt" và "tính năng đã có" **không** được dùng làm bằng chứng chất lượng trải nghiệm. Đánh giá dựa trên ba nguồn:

1. **Xem trang thật** trên điện thoại và máy tính (mục 2).
2. **Thử với người dùng thật** — đo tác vụ trước khi thiết kế lại (chuẩn so sánh) và trên bản mẫu (mục 7).
3. **Số đo hành vi tổng hợp**, không định danh: sao chép, chia sẻ, lưu, đi tiếp, tìm không ra.

## 2. Hiện trạng — điểm mạnh và vấn đề

**Điểm mạnh (giữ):**

- Trang tác phẩm ngắn (bài ca dao 2.590 px trên điện thoại), lời đứng đầu, nhãn thể loại rõ.
- Chú thích kiểu sách ngay dưới lời; dòng nguồn trung thực.
- Câu đố giấu đáp án; vè có mục lục và chú thích theo đoạn.
- Tắt JavaScript vẫn đọc được; trợ năng đạt chuẩn; tải nhẹ.

**Vấn đề:**

| # | Vấn đề | Thấy ở | Mức | Nguyên tắc bị vi phạm (mục 3) |
|---|---|---|---|---|
| U1 | Lời chưa là trung tâm thị giác: cỡ chữ lời gần bằng tiêu đề phụ; nút "Trích dẫn" tím đặc là điểm nhấn mạnh nhất màn hình | Ca dao, tục ngữ (390 và 1440 px) | Cao | P1 |
| U2 | Đọc tiếp liên quan yếu hoặc trống: ba gợi ý cùng lý do "cùng chủ đề Tình yêu, hôn nhân"; tục ngữ về một thú chơi ở Huế dẫn sang câu chỉ "cùng vùng"; Vè cô Giang: "Chưa có bài gần với bài này" dù bài nhắc nhiều nhân vật, địa danh | Ca dao, tục ngữ, vè | Cao | P4 |
| U3 | Khối mời góp nặng: 7–8 lối (Góp cách hiểu, Ở quê nói khác, Gửi số trang, nút lớn Góp một câu, Góp ý chú thích, Báo lỗi, Viết cảm nhận, Viết cảm nghĩ) ở cuối mọi bài; "Góp câu" chiếm một trong năm ô thanh dưới; nút "Góp một câu" tím đặc ở đầu trang máy tính | Mọi trang bài; đầu trang | Cao | P5 |
| U4 | Chất lượng câu chữ: Ý nghĩa dùng tính từ chung chung; phần Nghĩa của tục ngữ mở đầu nhắc lại câu rồi trích dài một đoạn sách; chú thích có từ đánh giá; vè có nhận định lịch sử không thấy nguồn ngay trên lời | Ca dao, tục ngữ, vè | Cao | P2, P3 |
| U5 | Vè: lời bị co nhỏ để giữ mỗi câu một dòng; Ý nghĩa dài đứng trước lời; không có khối nhân vật, sự kiện | Vè cô Giang | TB | P1, P6 |
| U6 | Thanh công cụ 5 nút bằng nhau trên 390 px, nhãn bị bó; "Trích dẫn" là hành động chính — đúng với giáo viên, chưa chắc với đa số người đọc | Mọi trang bài | TB | P2 |
| U7 | Không thấy ai biên soạn, duyệt, khi nào; không có lịch sử sửa đổi cho người đọc | Mọi trang bài | TB | P3 |
| U8 | Trang chủ dài 5.655 px trên điện thoại, lối vào lặp ("Theo dịp" xuất hiện ba lần, "Tra từ khó" hai lần), lời mời góp đứng ngay sau Câu hôm nay, thiếu giọng biên tập (chuyên đề, câu chuyện) | Trang chủ | TB | P5, P7 |
| U9 | Trang thể loại ca dao dài 10.336 px trên điện thoại — danh sách là lối vào chính cho gần 12.000 bài | Trang thể loại | TB | P4 |
| U10 | Từ điển là lưới chữ cái và ô tìm; chưa có loại (nhân vật, địa danh, điển tích), chưa là nơi khám phá | Từ điển | TB | P4 |
| U11 | Câu đố còn tĩnh: không gợi ý từng bước, chưa có nhịp "đoán – mở – hiểu"; nhãn "Lục bát" kỹ thuật với người chơi; phân loại chồng (bộ "Đố con người, sinh hoạt" cùng chủ đề "Đố đồ vật") | Câu đố | TB | P6 |
| U12 | Máy tính: lời khoảng 24 px trong khung rộng; chú thích chữ nhỏ không chân ở dưới, không dùng lề để chú giải cạnh dòng như sách khảo cứu | Ca dao (1440 px) | Thấp | P1, P7 |

## 3. Nguyên tắc trải nghiệm mới

| # | Nguyên tắc | Nghĩa trên trang |
|---|---|---|
| P1 | **Lời là nhân vật chính** | Chữ lời lớn, nhiều khoảng trắng; không thành phần nào nổi hơn lời ở màn hình đầu |
| P2 | **Trả lời trước, chiều sâu theo lựa chọn** | Một câu nghĩa ngay dưới lời; phần sâu mở khi người đọc muốn |
| P3 | **Nguồn và người làm trong tầm một chạm** | "Hồ sơ bài" cho biết lời từ đâu, ai viết nghĩa, ai duyệt, ngày, lịch sử sửa |
| P4 | **Mỗi lối đi tiếp có lý do cụ thể** | Gợi ý theo hình tượng, nhân vật, nơi chốn, điển tích, họ dị bản; không gợi ý theo chủ đề quá rộng |
| P5 | **Đọc trước, góp sau** | Mời góp một dòng trên trang đọc; nơi góp đầy đủ ở trang riêng |
| P6 | **Mỗi thể loại một nhịp** | Ca dao để cảm, tục ngữ để dùng, đồng dao để chơi, câu đố để đoán, vè để nghe kể |
| P7 | **Cảm xúc từ chữ, nhịp, khoảng trắng, hoạ tiết nhẹ, tiếng thật** | Không ảnh trang trí rỗng, không ảnh AI |
| P8 | **Nhẹ, nhanh, ai cũng dùng được** | Giữ ngân sách trang đọc, WCAG 2.2 AA, tắt JavaScript vẫn đọc |

## 4. Hướng thị giác — thử hai phương án

Phông giữ nguyên: Literata cho lời và tiêu đề, Be Vietnam Pro cho giao diện; cả hai hiển thị tiếng Việt tốt.

| | **A · Giấy và mực** | **B · Tím – lục tinh chỉnh** |
|---|---|---|
| Ý | Trang như sách khảo cứu đẹp: nền giấy, chữ mực, màu chỉ để đánh dấu | Giữ nhận diện hiện tại, giảm độ nổi của màu |
| Màu | Mực, giấy, một màu son nhấn nhẹ; màu thể loại chỉ ở nhãn nhỏ | Tím cho liên kết, lục cho nghĩa; bỏ nút tím đặc khỏi màn hình đầu |
| Lời | Cỡ lớn (điện thoại: 24–28 px với bài ≤ 4 dòng; sàn 18 px), giãn dòng rộng | Như A |
| Chú giải | Máy tính: **ghi chú lề** cạnh dòng; điện thoại: chạm để mở, danh sách dưới lời | Như A |
| Hoạ tiết | Đường nét mảnh (sóng, mây — đã duyệt D-126 (4)) ở đầu khối | Như hiện tại |
| Rủi ro | Đổi nhận diện; cần cập nhật ảnh chia sẻ | Ít khác biệt, cảm xúc tăng ít |

Thử cả hai trên cùng nội dung ở bản mẫu (mục 7). Người sáng lập chọn sau khi có kết quả thử (H4).

## 5. Kiến trúc thông tin mới (đề xuất để thử)

```text
Máy tính — đầu trang
  Phongdao · Đọc ▾ · Khám phá ▾ · Học · Chơi · Tra cứu ▾ · [ô tìm] · ☆ Đã lưu
  Đọc ▾      Ca dao · Tục ngữ – Thành ngữ · Đồng dao · Vè · Câu đố · Tuyển tập · Bộ tham chiếu
  Khám phá ▾ Hình tượng (mô-típ) · Nhân vật · Nơi chốn (bản đồ) · Điển tích · Chủ đề · Theo dịp · Chuyên đề
  Học        Bộ câu theo bài học, phiếu in, trình chiếu (EduKit)
  Chơi       PhongDao Play: đố vui, ghép câu, đồng dao
  Tra cứu ▾  Tìm · Từ điển · Phương pháp · Góp tư liệu

Điện thoại — thanh dưới (thử hai phương án)
  (1) Trang chủ · Tìm · Khám phá · Chơi · Đã lưu
  (2) Trang chủ · Tìm · Khám phá · Học · Đã lưu
  "Góp" chuyển vào menu, cuối bài (một dòng), trang tìm không ra, chân trang
```

Mục nào chưa có nội dung đủ ngưỡng thì chưa hiện — giữ "chỉ hứa điều đã có" (D-119 (8)). Đường dẫn cũ giữ nguyên.

## 6. Mẫu tham chiếu — đặc tả để làm bản mẫu

### 6.1. Khung chung trang tác phẩm

| Thứ tự | Khối | Hành vi | Ghi chú dữ liệu |
|---|---|---|---|
| 1 | Đầu bài: thể loại · nhãn phụ · nhãn mức biên tập (thử, 51 R-06) | Một dòng nhỏ | Cấp mới (52 mục 2.1) |
| 2 | **Lời** | Cỡ lớn, khoảng trắng; chữ có chú giải gạch chấm mảnh (thử so với nền vàng hiện tại); máy tính: ghi chú lề; điện thoại: chạm mở; số dòng từ 8 dòng | Phần `loi`, `chu_thich` |
| 3 | **Nghĩa một câu** | Câu đầu của Ý nghĩa ngay dưới lời, "Đọc tiếp nghĩa" mở phần còn lại; câu đố không có khối này trước đáp án | Không thêm trường (D-114 đã bác trường "nghĩa ngắn") |
| 4 | Thanh dùng bài | Lưu · Chia sẻ · **Dùng bài này ▾** (Trích dẫn, Sao chép, Trình chiếu, In, Tạo ảnh) — thử so với thanh 5 nút | — |
| 5 | **Hiểu sâu** | Theo thể loại (6.2–6.6); mỗi phần có dòng người viết | Phần hiện có |
| 6 | **Mạng liên kết** | Chip có biểu tượng theo loại: mô-típ, nhân vật, nơi chốn, điển tích, tuyển tập; Đọc tiếp 3 bài, mỗi bài một lý do cụ thể | Bảng quan hệ (54) |
| 7 | Khám phá sâu | Bài viết về bài này (bình giảng, khảo cứu), ảnh và âm thanh có quyền; chỉ khi có | Bài viết, media |
| 8 | Dị bản | Máy tính: so hai cột, tô chỗ khác; điện thoại: xếp dọc; ghi nơi, người kể, nguồn | `di_ban`, họ dị bản |
| 9 | **Hồ sơ bài** | "Bài này được làm thế nào": lời từ đâu (đối chiếu sách, trang), ai viết nghĩa, ai duyệt, thẩm định, ngày; lịch sử sửa đổi; cách trích dẫn; giấy phép | Dữ liệu người làm, nguồn, nhật ký — đã có |
| 10 | Góc bạn đọc | Cách hiểu, cảm nhận (chỉ khi có); một dòng "Góp tư liệu cho bài này" mở trang Góp đúng loại | Đã có |

**Bài tầng Lưu trữ** (L0–L1): khối 1, 2, 4, 8, 9 và một dòng "Bài trong kho lưu trữ, chưa biên tập — Góp cách hiểu". Không Mạng liên kết rỗng, không mời góp lớn.

### 6.2. Ca dao — để cảm

- Sau lời: Nghĩa một câu → "Hình ảnh và biểu tượng" (từng hình ảnh → điều được nói tới, chip mô-típ dẫn sang Atlas) → Ý nghĩa đầy đủ.
- Ca dao đối đáp: ghi lời ai nói khi có căn cứ.
- Đọc tiếp ưu tiên cùng mô-típ, cùng họ dị bản.

### 6.3. Tục ngữ – thành ngữ — để dùng

- Câu là tiêu đề lớn; nhãn phụ "Tục ngữ" hay "Thành ngữ" (51 R-08).
- Nghĩa một câu → Nghĩa đen (khi khác xa) → **Dùng khi**: 1–2 ví dụ đặt câu do người viết.
- "Mạng câu": gần nghĩa, trái nghĩa, cùng hình ảnh.
- Thành ngữ: ghi cách dùng trong câu.
- Nguồn gốc, giai thoại ở khối riêng, không chen vào phần Nghĩa.

### 6.4. Đồng dao — để chơi

- Ngay sau lời: thẻ **Chơi cùng bé**: số người, tuổi, nơi chơi, các bước có hình minh hoạ (có quyền), nhịp đọc khi có nguồn.
- Nút "Đọc cùng bé" (đã có).
- Âm thanh thật khi có ghi âm đồng ý (chặng 3).
- Không diễn giải đạo đức áp đặt.

### 6.5. Câu đố — để đoán

Nhịp ngay trong trang: câu đố (chữ lớn) → "Gợi ý 1", "Gợi ý 2" (khi có) → **Hiện đáp án** → Giải thích từng dấu hiệu → ảnh đáp án (chỉ sau khi mở) → **Đố tiếp cùng chủ đề**.

- Không chấm điểm, không lưu gì ngoài "đã đố tới câu nào" trên máy.
- Ẩn nhãn thể thơ với người chơi.
- Giữ `details` cho khi tắt JavaScript.

### 6.6. Vè — để nghe kể

- Thẻ mở đầu: bối cảnh 2–3 dòng **có nguồn** (52 mục 2.2).
- Mục lục đoạn dính đầu màn hình; vị trí đọc (thanh mảnh); chú giải theo đoạn.
- Bảng **Nhân vật và nơi chốn** dẫn sang hồ sơ; dòng thời gian khi có.
- Đọc to bằng giọng máy có nhãn.
- Lời không co nhỏ dưới 18 px (51 R-13).
- Vị trí Ý nghĩa theo quyết định ([56](56-goi-quyet-dinh.md) D1): khuyến nghị bối cảnh ngắn trước lời, Ý nghĩa đầy đủ sau lời.

### 6.7. Trang hồ sơ thực thể (nhân vật, địa danh, điển tích, mô-típ)

| Khối | Nội dung |
|---|---|
| Đầu trang | Tên, loại, tên khác, thời gian (nhân vật, sự kiện), toạ độ (địa danh đã kiểm) |
| Tóm tắt | Có nguồn; người viết, thẩm định |
| Trong văn học dân gian | Bài nhắc tới, nhóm theo thể loại, dòng có tên được tô, lý do quan hệ ("nhắc tới" khác "ghi nhận tại") |
| Liên quan | Hồ sơ liên quan (nhân vật – nơi chốn – sự kiện); bài viết liên quan |
| Bản đồ nhỏ | Chỉ địa danh có toạ độ đã kiểm |
| Nguồn | Thư mục |

### 6.8. Bài viết (khảo cứu, bình giảng, chuyên đề)

- Đầu bài: chữ dài dễ đọc (cột 38–40em).
- Thông tin bài: loại, người viết, người biên tập, thẩm định, ngày đăng, ngày sửa.
- Mục lục thu gọn.
- **Thẻ tác phẩm chèn trong bài** dẫn về trang lời; chú thích cuối bài trỏ hồ sơ nguồn.
- "Các tác phẩm trong bài"; Đọc tiếp bài viết cùng mạch.
- Bài cảm nhận của bạn đọc giữ nhãn riêng.

### 6.9. Tuyển tập

- Lời dẫn như một câu chuyện; nhóm có tiêu đề.
- Mỗi câu kèm Nghĩa một câu.
- Trình chiếu, In, Sao chép cả bộ.
- Người tuyển, ngày cập nhật.
- Tuyển tập theo bài học dẫn sang Học (EduKit).

### 6.10. Trang chủ

- **Chiều dài mục tiêu trên điện thoại: ≤ 3.000 px.**
- Ô tìm lớn.
- Câu hôm nay (lời + nghĩa một câu).
- **Chuyên đề** (một câu chuyện biên tập, đổi hai tuần một lần).
- Ba cửa Atlas: Hình tượng, Nhân vật, Nơi chốn.
- Đố một câu (Play).
- Cho giáo viên (EduKit).
- Tuyển tập nổi bật.
- Khối "Cách thư viện biên soạn".
- Bỏ các lối vào lặp. Lời mời góp chuyển xuống cuối trang.

### 6.11. Khám phá và Atlas

- Trang trung tâm bốn ống kính: **Hình tượng** (mô-típ), **Con người** (nhân vật), **Nơi chốn** (bản đồ có hai lớp "được nhắc đến" và "nơi lưu truyền", không trộn), **Điển tích**; cộng Chủ đề, Dịp, Chuyên đề.
- Mỗi ống kính có danh sách khi tắt JavaScript.
- Từ điển thành một phần của Tra cứu, có lọc theo loại (54).

### 6.12. Danh sách thể loại, chủ đề

- Đầu trang: lời giới thiệu thể loại, Bộ tham chiếu trước, lối vào theo mô-típ và chủ đề hẹp.
- Bộ lọc: nhãn phụ, mức biên tập, vùng, mô-típ, có giải nghĩa.
- Phân trang.
- Bài tầng Lưu trữ có nhãn nhỏ.

### 6.13. Tìm

- Thẻ "đúng câu" đầu tiên.
- Dị bản gom chung một kết quả.
- Kết quả hồ sơ thực thể và bài viết.
- Đồng nghĩa và tên cũ địa danh.
- Lọc theo mức biên tập.
- Tìm không ra: mời "Góp một câu" (giữ mạnh ở đây).

### 6.14. Điện thoại

- Thanh dưới theo mục 5.
- Ghi chú lề chuyển thành chạm mở và danh sách.
- Thanh dùng bài gọn.
- Vùng chạm ≥ 44 px.
- Không khối nào rộng quá màn hình.
- Kiểm 320 px và phóng to 200%.

## 7. Bản mẫu, thử người dùng và cổng duyệt

| Bước | Thời gian | Việc | Đầu ra | Cổng |
|---|---|---|---|---|
| UX-0 · Chuẩn so sánh | 1 tuần | Kế hoạch nghiên cứu; tuyển 8–10 người (2 giáo viên, 2 học sinh, 2 phụ huynh, 2 người yêu văn hoá, 1–2 người trên 60 tuổi); thử 6 tác vụ trên **site hiện tại** | Báo cáo chuẩn so sánh: tỷ lệ đạt, thời gian, mức dễ (SEQ), trích lời người thử (ẩn danh) | — |
| UX-1 · Khái niệm | 2 tuần | Bản mẫu thô ba trang (ca dao, vè, câu đố) × hai hướng thị giác; IA hai phương án | Bản mẫu thô; ghi chú | **PG1** — người sáng lập và 3 người thử chọn hướng |
| UX-2 · Bản mẫu bấm được | 3 tuần | HTML tĩnh dựng bằng công cụ `design/v2` với nội dung thật của 10 bài đã đăng (2 mỗi thể loại) — **để ngoài kho mã** như bản xem trước của D-119, D-121; đủ năm mẫu thể loại, hồ sơ bài, hồ sơ thực thể, bài viết, tuyển tập, trang chủ, Khám phá, điện thoại và máy tính | Bản mẫu, đặc tả thành phần | **PG2** — thử 8–10 người trên điện thoại của họ |
| UX-3 · Kiểm kỹ thuật | 1 tuần | Rà trợ năng trên bản mẫu (độ tương phản, bàn phím, trình đọc màn hình, 200%); ước ngân sách trang; danh sách dữ liệu cần có | Báo cáo trợ năng, ngân sách | **PG3** |
| Duyệt | — | Người sáng lập xem kết quả, chọn | Ghi D-xxx; cập nhật `design/v2` (token, đặc tả), `docs/27` | **PG4** (H4) |

**Ngưỡng PG2:**

- tác vụ đạt ≥ 85%;
- thấy nghĩa ≤ 10 giây;
- tìm dị bản và nguồn ≤ 30 giây;
- giáo viên lập bộ 5 câu và trình chiếu ≤ 3 phút;
- SEQ trung bình ≥ 5,5 / 7; SUS ≥ 75;
- người trên 60 tuổi đọc lời không phải phóng to.

Tác vụ nào không đạt phải sửa bản mẫu và thử lại, không được bỏ qua.

**Sáu tác vụ thử:**

1. Tìm nghĩa một câu tục ngữ quen.
2. Một câu ca dao có những bản nào, lấy từ đâu.
3. Từ một bài vè, tìm hiểu một nhân vật được nhắc tới.
4. Đố bé ba câu đố về đồ vật.
5. Chuẩn bị 5 câu cho tiết học và trình chiếu.
6. Đọc một bài và nói cảm nhận về cách trang trình bày.

**Riêng tư:** không lưu tên thật, liên lạc hay ghi âm người thử trong kho mã; chỉ lưu kết quả tổng hợp, câu trích đã ẩn danh.

## 8. Ngân sách theo loại trang

| Loại trang | Trang (nén) | JavaScript | Khác |
|---|---|---|---|
| Tác phẩm | ≤ 200 KB (giữ) | ≤ 21 KB (giữ) | LCP p75 ≤ 2,5 giây, CLS ≤ 0,1; tắt JavaScript vẫn đọc |
| Hồ sơ thực thể, bài viết, tuyển tập | ≤ 250 KB (tuyển tập 30 câu ≤ 300 KB, giữ) | ≤ 21 KB | Như trên |
| Khám phá, Atlas, bản đồ | ≤ 350 KB | ≤ 40 KB | Danh sách thay bản đồ khi tắt JavaScript |
| PhongDao Play, trang ứng dụng | ≤ 300 KB | ≤ 80 KB (51 R-26) | Chơi được bằng bàn phím; tôn trọng giảm chuyển động |
| Xưởng biên tập (quản trị) | — | ≤ 150 KB | Điện thoại 390 px dùng được |

Mọi trang: 0 lỗi axe WCAG 2.2 AA; phóng 200% không mất nội dung (ca kiểm mới, 55 nhóm việc E2.9).

## 9. Triển khai và quay lại

1. **Xây song song:** mẫu mới là một biến thể giao diện ("giao diện tham chiếu") bật theo thể loại và theo hạng bài, qua cài đặt. Mẫu cũ giữ nguyên.
2. **Thứ tự bật:** bài hạng A của một thể loại → toàn thể loại đó → các thể loại khác → trang chủ, Khám phá. Mỗi bước 1–2 tuần.
3. **Theo dõi** (sự kiện tổng hợp, 51 R-17): sao chép, chia sẻ, lưu, bấm Đọc tiếp, mở Hồ sơ bài, tỷ lệ tìm không ra; Search Console (lượt hiển thị, nhấp) trước / sau.
4. **Quay lại:** tắt biến thể theo thể loại ngay trong cài đặt. Đường dẫn, dữ liệu có cấu trúc, `<title>` không đổi giữa hai mẫu nên không ảnh hưởng SEO.
5. **Dọn:** xoá mẫu cũ một tháng sau khi bật toàn bộ, nếu không phải quay lại.

## 10. Rủi ro

| Rủi ro | Mức | Giảm thiểu |
|---|---|---|
| Thiết kế đẹp nhưng nội dung chưa đủ dày (khối rỗng) | Cao | Bật theo hạng bài có nội dung; khối ẩn khi trống; làm song song Xưởng biên tập (52) |
| Người quen giao diện cũ không thích | TB | Thử người dùng; bật dần; quay lại theo thể loại |
| Ghi chú lề phức tạp, lỗi bố cục | TB | Chỉ trên máy tính rộng; điện thoại dùng cách hiện tại đã kiểm |
| Đổi nhận diện ảnh hưởng ảnh chia sẻ, chất liệu đã in | Thấp | Ảnh chia sẻ sinh lại bằng máy (đã có); làm theo hướng được chọn |
| Thử người dùng thiếu đa dạng | TB | Tuyển theo cơ cấu ở UX-0; có người lớn tuổi, người dùng trình đọc màn hình nếu tìm được |
