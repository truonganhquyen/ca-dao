# Tối ưu trang sửa đầy đủ và quản lý phân loại

Người sáng lập đồng ý triển khai toàn bộ đề xuất audit ngày 13/11/2026, gồm sửa hình thức thơ và quản lý danh mục (D-109). Đây là phạm vi nghiệm thu của đợt; kết quả thực tế cập nhật sau kiểm thử.

## Phạm vi và nghiệm thu

| Phần | Kết quả cần đạt |
|---|---|
| Chưa lưu và khôi phục | Thấy số/tên phần chưa lưu, phân biệt giữ trên tab với lưu vào bài; phần khôi phục luôn mở, kể cả điện thoại; chuyển phần giữ chữ |
| Lưu và trạng thái | Tên nút theo phần; bản nháp không nói đã đăng; Ý nghĩa phân biệt nháp Hộp việc với bài nháp; còn phần chưa lưu thì không tự sang bài |
| Mục lục và responsive | Tới từng phần, Ảnh, Phân loại, Căn cứ nội bộ, Lịch sử từ đầu trang; kiểm 390/768/1024/1199/1200/1440 px; hạn chế cuộn lồng |
| Danh sách dài | 36 mục từ có tóm tắt, lọc, thêm ở đầu, mở từng mục/mở tất cả; nhảy từ cảnh báo tới mục; giá trị/nguồn/đầu vào lỗi còn nguyên |
| Thể thơ | Người có quyền sửa theo bài, bỏ nhãn hoặc quay lại dùng máy; tính lại lời không ghi đè lựa chọn người; có lịch sử và kiểm bản mở cũ |
| Danh mục | Có menu Phongdao và lối thêm cạnh bộ chọn; kiểm quyền, nonce, trùng tên; thêm mục không tự gắn vào bài hoặc làm mất chữ chưa lưu; không thêm thể loại tác phẩm mới |
| Theo thể loại | Sắp thứ tự phần riêng, giữ trường Lời/Câu hỏi/Đáp án riêng; Ý nghĩa cũ của tục ngữ vẫn sửa được; không di chuyển/viết lại dữ liệu |
| Ảnh | Thấy giới hạn thực trước mở hộp; chọn/tải → kiểm thông tin → lưu; ảnh chọn còn chưa lưu có nhãn; bỏ ảnh bị tắt khi trống; giữ phạm vi dùng chung của thông tin attachment |
| Thao tác và trợ năng | Nhóm quản lý trạng thái là thao tác phụ; xem bản đã lưu ghi đúng nghĩa; vùng chạm ≥44 px, bàn phím/focus, no-JS còn truy cập và lưu được |

Giữ WordPress, form lưu từng phần, kiểm phiên và lịch sử. Không thêm nút lưu toàn bài, không tự công khai bài, không tự chọn dữ liệu văn hoá, không thêm bảng hoặc loại bài. Cơ chế ghi đè thể thơ là lựa chọn kỹ thuật nhỏ theo từng bài; không thay cấu trúc nội dung.

Bốn ngoại lệ giữ nguyên: parser/nhãn Video; token nguồn có chủ ý; mở nguồn văn bản/nguồn cờ/xem trước sách cho người đọc; diễn tập phục hồi hoặc kiểm kê DB/ảnh. Pilot nội dung thật và mở Google tiếp tục do người sáng lập quyết.

## Kiểm thử và phát hành

Mở rộng bốn cổng hiện có; kiểm thao tác ghi trên WordPress mẫu, các lỗi quyền/nonce/xung đột và giữ chữ. Xem ảnh và quét trợ năng ở các cỡ nêu trên. Sau CI đạt, gộp PR và kiểm site đang chạy đúng phiên bản; kiểm production chỉ đọc, không sửa phân loại/nội dung thật thay người biên soạn.

## Kết quả

Đang triển khai. Chưa xác nhận hoàn tất hoặc phát hành.
