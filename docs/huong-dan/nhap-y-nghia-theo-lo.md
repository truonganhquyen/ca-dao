# Nháp Ý nghĩa theo lô

Kho có hơn 16 nghìn bài nhưng rất ít bài có Ý nghĩa. Công cụ này giúp có thêm Ý nghĩa nhanh hơn mà vẫn do bạn quyết từng bài. Máy chỉ chuẩn bị lô, nháp đi vào Hộp việc; không có gì vào bài cho tới khi bạn bấm Duyệt.

Chỉ bài có Ý nghĩa (hoặc phần riêng khác) mới lên Google khi mở. Vì vậy mỗi nháp được duyệt là thêm một trang có giá trị.

Nơi làm: **wp-admin → Phongdao → Bổ sung → mục "Nháp Ý nghĩa theo lô"**.

## Các bước

1. **Tải lô**: chọn số bài (tối đa 50) và thể loại (ca dao, đồng dao, vè), bấm **Tải tệp lô (CSV)**.
   - Máy chọn bài đã đăng, chưa có Ý nghĩa, chưa có nháp chờ; bài được đọc nhiều xếp trước.
   - Tệp có các cột id, tên, thể loại, đường dẫn, lời, từ khó, và cột `y_nghia` để trống.
2. **Soạn nháp** vào cột `y_nghia`:
   - tự viết, hoặc nhờ AI (ví dụ gửi tệp cho Claude) theo quy tắc ở dưới;
   - bài nào không chắc thì để trống, máy sẽ bỏ qua.
3. **Nạp tệp nháp**: chọn tệp đã điền, bấm **Xem trước**. Màn xem trước ghi từng dòng sẽ tạo thẻ hay bỏ qua, kèm lý do: bài đã có Ý nghĩa, đã có nháp chờ, nháp trống hoặc quá dài…
4. Bấm **Tạo N thẻ nháp**. Mỗi dòng hợp lệ thành một thẻ "Nháp ý nghĩa" trong **Hộp việc**, đánh dấu có hỗ trợ AI.
5. Ở **Hộp việc**, xử lý từng thẻ:
   - đọc lời;
   - sửa vài chữ ngay trên thẻ nếu cần;
   - bấm **Duyệt** (Ý nghĩa lên trang, ghi "Có hỗ trợ của AI, đã được người biên soạn duyệt") hoặc **Bỏ nháp**.

## Lô nhỏ

Mỗi lúc có tối đa **50 nháp chờ**. Duyệt hoặc bỏ bớt thì mới nạp được lô mới. Cách này giúp Hộp việc không quá tải và mỗi nháp đều được đọc kỹ.

## Quy tắc soạn nháp (cho người hoặc AI soạn)

- **2–3 câu, khoảng 30–60 chữ.** Nói câu ca dao muốn nói gì, ai nói với ai nếu lời cho thấy rõ.
- **Chỉ dựa vào lời và từ khó trong tệp.** Không thêm:
  - điển tích, nguồn gốc;
  - vùng, địa danh lịch sử, năm;
  - tên tác giả, tên sách.
- **Không đạo đức hoá, không áp một bài học** khi lời không nói vậy. Câu có nhiều cách hiểu thì viết "Có thể hiểu là…".
- **Không chép giải nghĩa của trang khác.** Viết bằng lời mới, giọng thư viện: rõ, ngắn, trung tính.
- **Không chắc thì để trống.** Thiếu một nháp tốt hơn một nháp sai.

## Lưu ý

- Tệp tải về chứa lời và chú thích lấy từ nguồn. Giữ trên máy bạn; **không đưa vào kho mã công khai**.
- Nạp nháp cần quyền duyệt. Người chỉ soạn tải được lô nhưng không nạp được.
- Tục ngữ dùng Nghĩa đen / Nghĩa bóng, câu đố dùng Giải thích đáp án, nên hai thể loại này chưa có trong lô.
