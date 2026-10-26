# Hướng dẫn · Viết "Hiểu nhanh"

| | |
|---|---|
| Cho ai | Người sáng lập (không cần dòng lệnh) |
| Làm khi | Mỗi tuần khoảng 10 bài; mục tiêu ≥ 50 bài trước khi mở cho Google |
| Vì sao | Bài chưa có Hiểu nhanh thì Google không lập chỉ mục (lời chép giống nơi khác không tính là giá trị riêng) |

## Viết một bài

1. Mở **Ca dao – Tục ngữ → Viết Hiểu nhanh**: danh sách bài chưa có, bài đọc nhiều đứng trước.
2. Bấm tên bài. Ở thanh bên, hộp **Viết Hiểu nhanh** có chú thích của bài để bạn tham khảo.
3. Viết 2–3 câu vào ô **Tóm tắt** (thanh bên, mục Bài viết): câu này nghĩa là gì, thường nói khi nào. Viết như nói với học trò lớp 7.
4. Bấm **Cập nhật**. Hiểu nhanh hiện ngay dưới lời, và bài được Google lập chỉ mục khi site mở.

## Khi bài có nháp máy

Nếu bạn nhờ agent soạn nháp cho một danh sách bài, hộp **Viết Hiểu nhanh** có ô **"Nháp máy — chưa duyệt"**. Nháp này không hiện ra trang.

1. Đọc nháp. Đúng ý thì bấm **Dùng nháp này**: nháp chép vào ô Tóm tắt, ô **"Phần giải nghĩa có hỗ trợ của AI"** được đánh dấu.
2. Sửa câu chữ cho đúng ý mình. Nếu bạn viết lại hoàn toàn, có thể bỏ dấu ô đó.
3. Bấm **Cập nhật**. Bài có dấu sẽ ghi dưới trang: "Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt."
4. Nháp không dùng được: đánh dấu **Xoá nháp máy khi lưu** rồi Cập nhật.

## Nhờ agent soạn nháp

Nói với agent: "Soạn nháp Hiểu nhanh cho 10 bài đầu danh sách" (hoặc gửi tên bài). Agent đọc lời và chú thích, soạn nháp, rồi hỏi bạn trước khi nạp lên site. Nháp chỉ vào ô nháp, không bao giờ tự lên trang.

Lệnh agent dùng (trên máy chủ): `wp cadao hieu-nhanh-ds --so=10` lấy danh sách bài; `wp cadao hieu-nhanh-nhap <tệp.json>` nạp nháp `[{"id": …, "nhap": "…"}]`.
