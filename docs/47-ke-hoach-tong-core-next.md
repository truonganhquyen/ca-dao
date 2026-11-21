# 47 · Kế hoạch tổng triển khai — V2 Core và Next

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Lịch sử (21/11/2026)** — định hướng, kiến trúc, lộ trình đã thay bởi `docs/50`–`57`; kế hoạch đang dùng: [57](57-ke-hoach-chot.md). Nguyên trạng lúc lập: **Đề xuất, chờ người sáng lập duyệt.** Khi duyệt, agent làm theo mục 6 để đưa việc vào `docs/22`; trước đó **không** agent nào nhận việc từ tài liệu này |
| Đọc khi | Lập kế hoạch, nhận việc hay nghiệm thu một giai đoạn của V2 Core hoặc Next |
| Bộ tài liệu | [45 kiểm kê, đối chiếu](45-kiem-ke-va-doi-chieu.md) · [46 kiến trúc, khuyến nghị](46-kien-truc-va-khuyen-nghi.md) · **47 kế hoạch tổng (tệp này)** · [48 lộ trình, backlog](48-lo-trinh-va-backlog.md) · [49 quyết định, câu hỏi mở](49-quyet-dinh-va-cau-hoi-mo.md) |
| Giả định | Người sáng lập khoảng 5 giờ mỗi tuần (D-035); agent làm mọi phần mã, tự gộp khi bốn cổng đạt (D-066); không hứa ngày công khai |

---

## 1. Mục tiêu

**V2 Core — 12 tuần tới (đề xuất):**

1. **Niềm tin:** mọi lỗi ĐT-ND chỉ ra (45, mục 3.2) đã được người biên soạn xử lý hoặc ghi lý do giữ; máy nhắc chạy để lỗi cùng loại không quay lại.
2. **Nội dung đều đặn:** vòng nháp Ý nghĩa theo lô chạy hằng tuần; số bài có phần riêng tăng đều và đo được theo thể loại. Đích tham chiếu `docs/00` mục 8: ≥ 300 bài L2.
3. **Mạng tri thức tối thiểu:** mục từ có loại (địa danh, nhân vật, điển tích); bài viết đã có quyền nối với tác phẩm; Đọc tiếp có lý do theo loại.
4. **Thiết kế đã duyệt được làm nốt:** Đố vui, Trò chơi có lọc, khối Biên soạn, Sao chép cả bộ.

**PHONGDAO NEXT — chỉ hoạch định:** chốt cổng điều kiện và thứ tự thử (46, mục 5); không viết mã cho tới khi qua cổng N0.

**Không làm trong kế hoạch này:** loại bài hay bảng mới, đổi đường dẫn, đổi luật lập chỉ mục (D-117, D-126), đổi bảng màu, phông, bố cục lớn của trang tác phẩm (D-119), mở thể loại mới.

## 2. Nguyên tắc thực hiện

1. **Mỗi giai đoạn một hoặc hai PR**, gom việc liên quan (D-066); nhiều PR mở cùng lúc thì xếp chồng (D-053).
2. **Bốn cổng** trước khi gộp: `npm run kiem-tra`, `npm run test:v2`, `npm run quet:v2`, `npm run e2e:v2`; tăng `CADAO_PHIEN_BAN` + `Version:` của plugin, `Version:` của theme khi chạm; PR có link bản xem trước (`npm run xem-truoc:v2 -- <nhánh>`).
3. **Nội dung đi đường người:** máy và agent chỉ gợi ý, soạn nháp vào Hộp việc; người sáng lập duyệt, nối, đặt loại, tích quyền.
4. **Không mở rộng phạm vi trong PR:** việc phát sinh ghi vào 48 hoặc `docs/22`, không làm kèm.
5. **Đo trước khi tối ưu:** hiệu năng kho lớn vẫn hoãn (D-098) trừ khi màn mới chậm thấy rõ.

## 3. Các giai đoạn

| Giai đoạn | Tên | Kết quả | Cổng ra | Người sáng lập | Agent |
|---|---|---|---|---|---|
| **0** | Chốt và dọn nền | Quyết định ở 49 được ghi D-xxx; việc vào `docs/22`; phạm vi T-185 được đối soát; tám chỗ lệch tài liệu – mã đã sửa | `kiem-tra` đạt; `npm run viec-tiep-theo` ra đúng việc đầu của giai đoạn 1 | 1–2 giờ: đọc 49, trả lời | 1 PR tài liệu |
| **1** | Niềm tin và vòng nội dung | Sổ tay biên tập v1; năm máy nhắc mới ở Cần sửa; bảng độ phủ ở Số liệu; hàng "Ghi chú" (nếu duyệt); lô nháp Ý nghĩa đầu tiên | Bốn cổng đạt; người sáng lập xử lý danh sách trang ĐT-ND; một lô nháp đã duyệt xong | 2–3 giờ/tuần | 1–2 PR mã + 1 tệp hướng dẫn |
| **2** | Mạng tri thức tối thiểu | Loại mục từ + gợi ý Địa danh + lọc Từ điển + "Trong bài có"; Đọc tiếp lý do theo loại; bài viết nối tác phẩm + mẫu trang bài viết | Bốn cổng đạt; ≥ 20 mục từ có loại do người chọn; ≥ 3 bài viết đã có quyền và đã nối hiện đúng ở trang tác phẩm | 1–2 giờ/tuần (chọn loại, tích quyền, nối) | 2 PR |
| **3** | Hoàn thiện trải nghiệm đã thiết kế | Đố vui; Trò chơi có lọc; khối Biên soạn; Sao chép cả bộ | Bốn cổng đạt; thử tác vụ phụ huynh, giáo viên (`docs/27` mục 12) đạt | 1 giờ duyệt bản xem trước | 1–2 PR |
| **4** | Tư liệu và lớp học (Later) | Đối chiếu sách Wikisource; Cho lớp học và phiếu in; dị bản hai cột | Theo task gốc T-158, T-159, T-063 | theo nội dung | nhiều PR |
| **N0** | Cổng NEXT | Quyết hướng tài chính (Q-008), thư Ca dao Mẹ, ngưỡng nội dung, quy tắc dùng lại | Các quyết định ở 49 (QĐ-9, QĐ-10, QĐ-11) đã có | quyết định | soạn câu hỏi phỏng vấn nếu cần |
| **N1** | Kênh không tài khoản | Lớp lọc dùng lại; xuất dữ liệu mở; "Bộ của tôi"; khung nhúng | Kiểm thử khẳng định không phần chưa có quyền nào ra ngoài | theo kênh | theo kênh |
| **N2** | Đối tác | Tuyến di sản / triển lãm thử; API đối tác; tuyển tập in | Có đối tác thật, có thoả thuận | cao | theo dự án |
| **N3** | Lưu trữ sống | Ghi âm có đồng ý; người soát lời tình nguyện | Hồ sơ đồng ý, nơi lưu riêng tư, người duyệt | cao | theo dự án |

Thứ tự bắt buộc: 0 → 1 → (2 và 3 có thể đổi chỗ nếu người sáng lập muốn phần trẻ em trước) → 4. N0 xét lại sau khi xong giai đoạn 3 hoặc khi người sáng lập yêu cầu.

## 4. Giai đoạn 1 — đề xuất triển khai ngay sau khi duyệt

Chi tiết từng việc, tiêu chí và ca kiểm ở [48](48-lo-trinh-va-backlog.md) (C1.1–C1.7). Tóm tắt:

| Việc | Ai | Kết quả người sáng lập thấy |
|---|---|---|
| Sổ tay biên tập v1 (`docs/huong-dan/so-tay-bien-tap.md`) | Agent soạn, người sáng lập duyệt | Một tệp: mỗi phần của bài viết gì, không viết gì; cách viết điều chưa chắc; cách ghi địa danh theo thời điểm; quy tắc cho nháp AI |
| Năm máy nhắc mới | Agent | Bổ sung → Cần sửa có thêm năm nhóm, mỗi nhóm có số bài và nút sửa / Bỏ qua |
| Bảng độ phủ theo thể loại | Agent | Số liệu cho biết mỗi thể loại có bao nhiêu bài lên Google, bao nhiêu thiếu Ý nghĩa |
| Hàng "Ghi chú" (nếu duyệt QĐ-4) | Agent | Ghi chú chung không còn nằm dưới tên "Chuyện quanh bài" |
| Danh sách trang cần xem (từ ĐT-ND) | Agent chuẩn bị, người sáng lập quyết | 7 trang kèm việc cần quyết (49, mục 3) |
| Lô nháp Ý nghĩa 30 bài | Người sáng lập tải lô, agent soạn nháp theo Sổ tay ngoài kho mã, người sáng lập nạp và duyệt | Hộp việc có 30 thẻ nháp; mỗi thẻ ≤ 60 giây |
| Chọn 20 bài mẫu | Người sáng lập | 4 bài mỗi thể loại, gộp vào việc "thử làm sâu 20–30 bài" đang chờ |

**Vì sao giai đoạn này trước:** rẻ nhất (cỡ S), không cần Human Gate dữ liệu, gỡ đúng các lỗi bên ngoài nhìn thấy, và làm chuẩn cho mọi nội dung về sau — kể cả nháp AI — trước khi mở thêm chỗ chứa nội dung mới.

## 5. Nhịp làm việc và năng lực

| Việc lặp mỗi tuần | Người sáng lập | Agent |
|---|---|---|
| Duyệt bản xem trước của PR đang mở | ≤ 30 phút | Dựng link, ghi rõ cần xem gì |
| Lô nháp Ý nghĩa | 30–45 phút (30 thẻ × ≤ 60 giây + sửa) | Soạn nháp theo Sổ tay khi người sáng lập gửi tệp lô |
| Xử lý Cần sửa | 30–60 phút | Giữ ngưỡng nhắc vừa phải |
| Nối mục từ, bài viết; đặt loại (từ giai đoạn 2) | 30–60 phút | Gợi ý, không tự gắn |
| Nội dung sâu (chuyện quanh bài, bài viết) | phần còn lại | — |

- **Giới hạn việc dở:** tối đa một PR mã đang chờ gộp; tài liệu có thể chồng lên.
- **Khi người sáng lập bận:** giai đoạn 1 vẫn chạy (máy nhắc, bảng độ phủ không cần người); vòng nội dung tạm dừng, không dồn thẻ vào Hộp việc (tối đa 50 nháp chờ — D-117).

## 6. Cách đưa kế hoạch vào repo khi được duyệt (việc của agent, giai đoạn 0)

1. Mỗi QĐ được duyệt ở 49 → một dòng D-xxx ở `docs/01` (số = lớn nhất trên `main` và mọi PR đang mở + 1); QĐ bị bác → ghi vào 49 mục 2 là "không làm", không ghi D.
2. Việc ở 48 → các dòng T-xxx trong `docs/22` (số kế tiếp lớn nhất), một nhóm tiêu đề riêng cho kế hoạch này; giai đoạn 1 `READY`, giai đoạn 2–4 `BLOCKED` theo phụ thuộc, NEXT `DEFERRED` tới khi qua N0. Gộp vào task cũ khi trùng (T-180, T-185, T-198, T-158, T-159, T-063…) thay vì tạo task mới — bảng ánh xạ ở 48 mục 5.
3. Cập nhật `docs/02` (mục "Đang chờ người sáng lập", "Tài liệu"), bảng tài liệu trong AGENTS.md, `du-an/trang-thai.json` nếu việc hiện tại đổi.
4. Sửa tám chỗ lệch tài liệu – mã (45, mục 5) trong cùng PR.
5. Chạy `npm run kiem-tra`; mở PR tài liệu; tự gộp khi CI đạt (D-066).

## 7. Kiểm thử và chất lượng

### 7.1. Ca kiểm theo lớp

| Lớp | Khi nào bắt buộc | Ở đâu |
|---|---|---|
| PHP thuần | Mọi quy tắc mới trong `MienDuLieu` (máy nhắc, loại mục từ, lọc dùng lại) — ca dương và ca âm | `wordpress/v2/tests/don-vi/` |
| Playground (WordPress thật) | Mọi luồng lưu, hiển thị, quyền; lằn ranh "máy không ghi thay người" | `wordpress/v2/tests/kiem-*.php` (mở rộng tệp gần nhất, không tạo đường kiểm rời) |
| Quét trợ năng, ngân sách | Mọi trang, màn mới hoặc đổi bố cục | `npm run quet:v2` (thêm đường dẫn mẫu) |
| Đầu – cuối | Mọi luồng người đọc mới (lọc Từ điển, mở bài viết từ tác phẩm, Đố vui, Trò chơi lọc, Sao chép cả bộ) | `npm run e2e:v2` |

### 7.2. Hồi quy luôn kiểm

- Đường dẫn: không đường dẫn nào đổi; đường cũ vẫn chuyển hướng 301.
- Lập chỉ mục: bài chỉ có lời vẫn `noindex` (D-117); bài viết chưa tích quyền vẫn `noindex` (D-126); trang mới có `noindex` khi là trang tương tác.
- HTML gửi khách không có nút sửa, `nguon_may`, nguồn đã ẩn (`docs/28` mục 6).
- Câu đố không lộ đáp án ở thẻ bài, mô tả, dữ liệu có cấu trúc, ảnh chia sẻ (D-114).
- Trang bài ≤ 200 KB, JavaScript ≤ 21 KB, tắt JavaScript vẫn đọc; 0 lỗi axe.
- Bài có "Có từ ngữ thô tục" không vào Đố vui, Đọc tiếp, trang chủ (D-111).

### 7.3. Định nghĩa xong (mọi việc)

Tiêu chí chấp nhận ở 48 đạt · bốn cổng đạt · đã xem `git diff` · tài liệu nguồn sự thật (`docs/26`, `27`, `28`, `02`, `22`) phản ánh hành vi mới · PR ghi đã làm, **không** làm, kết quả kiểm, link bản xem trước, Human Gate nếu có · commit có dòng `AI-Agent:`.

## 8. Chỉ số theo dõi

| Nhóm | Chỉ số | Nguồn | Hướng |
|---|---|---|---|
| Nội dung | Bài có phần riêng theo thể loại | Bảng độ phủ (C1.3) | tăng đều mỗi tuần |
| Nội dung | Bài L2 trở lên | Số liệu | ≥ 300 (`docs/00` mục 8) |
| Chất lượng | Mục đang mở ở Cần sửa, theo nhóm | Bổ sung → Cần sửa | giảm |
| Biên tập | Thời gian duyệt một thẻ nháp | Hộp việc (ước theo phiên) | ≤ 60 giây |
| Biên tập | Tuổi thẻ Người đọc gửi | Số liệu | không thẻ nào > 30 ngày |
| Tri thức | Mục từ có loại · bài viết đã nối | Từ điển, bài viết | tăng khi có người làm |
| Người đọc | Lượt dùng tin cậy (trích dẫn + chia sẻ) | 5 sự kiện hiện có | tăng |
| Người đọc | `tim_khong_ket_qua` | 5 sự kiện hiện có | giảm |

Không đo thêm sự kiện nào nếu không có D-xxx (AGENTS.md).

## 9. Rủi ro thực hiện

| Rủi ro | Mức | Cách giảm |
|---|---|---|
| Người sáng lập không đủ giờ cho vòng nội dung | Cao | Lô nhỏ (30), dừng được bất kỳ tuần nào; phần máy của giai đoạn 1–3 không phụ thuộc giờ người |
| Khối mới không có nội dung, trang trông rỗng | TB | Mọi khối ẩn khi trống; chỉ thêm mục menu khi đủ ngưỡng (KT-10) |
| Agent tự nhận việc trước khi duyệt | TB | Không thêm việc vào `docs/22` trước giai đoạn 0; tài liệu ghi rõ "chờ duyệt" |
| Nháp AI kém chất lượng làm người duyệt mệt | TB | Sổ tay làm chuẩn; máy nhắc Ý nghĩa sẵn có (độ dài, mở đầu, tác giả); người bỏ thẻ dễ dàng |
| Xung đột PR do sửa tệp điều phối chung | Thấp | Xếp chồng nhánh (D-053); giai đoạn 0 gom mọi sửa `docs/01`, `02`, `22` vào một PR |
| Kênh NEXT bị mở sớm vì có ý tưởng hấp dẫn | TB | Cổng N0 là điều kiện; tài liệu ghi rõ "chỉ hoạch định" |
