# Form Góp gọn và giữ chữ khi gửi lỗi

Ngày 13/11/2026 · D-108 · T-199. Người sáng lập yêu cầu sửa sau khi xem đề xuất và đối chiếu với Phong Dao hiện tại.

## 1. Phạm vi

Giữ một trang Góp và đường nhận vào Hộp việc đang có. Chỉ hiện trường theo loại đóng góp, giữ chữ khi đổi loại/gửi lỗi và làm rõ phản hồi. Không đổi dữ liệu văn hoá, schema, giấy phép, chính sách nguồn, quyền duyệt, chống rác hoặc đo lường. Bốn ngoại lệ của `docs/33` giữ nguyên; thử người dùng thật vẫn là phần riêng, chưa suy ra từ kiểm phần mềm.

| Loại góp | Trường chính và phần phụ |
|---|---|
| Ở quê nói khác | Lời người đọc gõ; nơi nghe/người kể thu gọn, không bắt buộc |
| Góp ý chú thích | Một ô nội dung, hướng dẫn nêu từ/đoạn và điều muốn bổ sung |
| Báo lỗi | Một ô mô tả chỗ gặp lỗi và điều cần sửa; có thể dán đường dẫn khi góp từ trang chung |
| Gửi số trang | Sách nếu biết, số trang, ghi chú tuỳ chọn; vẫn gửi được chỉ số trang |
| Cảm nghĩ ngắn | Chỉ có khi gắn bài, một ô nội dung |
| Bài cảm nhận | Tiêu đề, nội dung, cam kết tự viết; vai tuỳ chọn nằm trong phần thông tin phụ |
| Báo vi phạm | Giữ bài và mã đối tượng từ liên kết, một ô nêu vấn đề |

Bút danh tuỳ chọn thu gọn; đồng ý CC BY 4.0 luôn hiện trong form đã chọn loại và không được đánh dấu hộ. Cách hiểu tiếp tục ở trang bài, dùng đường gửi và duyệt hiện có.

## 2. Hành vi

- Đến từ trang bài: giữ loại/bài/đối tượng vi phạm, bộ chọn loại thu gọn. Vào `/gop/` trực tiếp: bộ chọn mở, người đọc tự chọn loại.
- CSS chọn nhóm theo radio. Các giá trị ở nhóm đang ẩn vẫn ở trong form để đổi lại không mất chữ. Trình duyệt cũ chưa hỗ trợ CSS `:has()` dùng form đủ nhóm và nhãn chung; vẫn gửi được.
- Gửi qua POST tại `/gop/`, đi qua nonce và cùng nghiệp vụ nhận đang dùng. Thành công chuyển hướng 303, tránh tải lại trang tự gửi một lần nữa; cơ chế chống gửi trùng hiện có giữ nguyên.
- Lỗi: trả HTTP 422 với `Cache-Control: private, no-store`, vẽ lại chữ nguyên trạng bằng HTML escape; giữ lựa chọn và các cam kết người đọc đã chọn. Nonce sinh lại, token chống rác không chép lại. Không lưu nháp lỗi trên máy chủ hoặc vào URL/cookie, không tạo tài khoản người đọc.
- Đường admin-post giữ cho form cũ và Cách hiểu. Form mới không thêm JavaScript. Đường duyệt và trạng thái Chờ duyệt giữ nguyên.

## 3. Nghiệm thu

- Bảy nhánh hiện đúng trường; nhánh cần ngữ cảnh giữ đúng bài/đối tượng; các liên kết cũ vẫn dùng được.
- Đổi loại giữ chữ; bàn phím chọn được; kiểm điện thoại/máy tính, khổ 320 px, tắt JavaScript.
- Nonce hết hạn, thiếu đồng ý trả lại chữ an toàn; nội dung dạng thẻ HTML không trở thành mã; lỗi sửa xong gửi lại được.
- Chỉ số trang vẫn gửi được, không ép viết ghi chú. Thiếu cam kết bài cảm nhận vẫn bị chặn; chống rác/chống trùng và hàng chờ không yếu đi.
- Bốn cổng chính đạt; xem lại diff và ảnh; trạng thái/tài liệu khớp bằng chứng. Các ca ghi chỉ dùng dữ liệu mẫu trong môi trường thử.

## 4. Kết quả

Bản mã plugin **1.0.58**, theme **1.0.7** đã qua bốn cổng local; không có kiểm tra bị bỏ qua.

| Cổng | Kết quả |
|---|---|
| `npm run kiem-tra` | 161 task, 1 câu hỏi mở, 0 lỗi |
| `npm run test:v2` | 255 PHP (2,1 giây, dưới cổng 5 giây) + 554 WordPress + 55 chuyển đổi |
| `npm run quet:v2` | 36 trang × 3 chế độ, thêm 12 trang × 2 chế độ 320 px, 3 trang biên tập và 9 trang admin; 0 lỗi |
| `npm run e2e:v2` | 54 bước đạt, 0 lỗi, 0 bỏ qua |

Đã xem ảnh form Báo lỗi ở 390/1440 px và Bài cảm nhận không JavaScript. Mẫu 390 px, vào trang Góp chung rồi chọn Báo lỗi: nút gửi cách đầu trang khoảng 1.196 px; mẫu chọn sẵn loại khoảng 855 px. Đây là số đo bố cục mẫu, chưa là kết quả thử người dùng. Ô viết bài dài cao tối thiểu 320 px; form không thêm JavaScript. Các trang Góp trong mẫu khoảng 154,1–154,2 KB, JavaScript khách 9,6 KB, trong ngân sách 200/21 KB.

Log, ảnh local ở `output/` của worktree triển khai; không đưa tệp runtime hoặc tài khoản vào Git. Giữ riêng log lượt đầu E2E lỗi truyền biến trong ca kiểm và lượt PHP bị quá ngưỡng khi chạy đồng thời; đã sửa ca kiểm, chạy lại đầy đủ và chạy riêng PHP đạt, không nới cổng. CI lưu log và ảnh trong artifact của PR.

Sau khi gộp, đối chiếu bằng `npm run kiem-site-that` và mở `/gop/` bằng phiên khách chỉ GET/HEAD. Không dùng nội dung thử để ghi lên production. Pilot nội dung thật T-198/H2 giữ nguyên.
