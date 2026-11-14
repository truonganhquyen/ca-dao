# Thùng rác và chờ trùng

Phạm vi người sáng lập chốt ngày 14/11/2026 (D-115): đưa bài vào thùng rác, xoá bài trong thùng rác, sửa lối xử lý chờ trùng bị kẹt. Không làm chuyển tác phẩm thành dị bản.

## Cách dùng

- **Phongdao → Tác phẩm:** bấm **Thùng rác** trên một hàng, hoặc chọn nhiều bài rồi bấm **Đưa vào thùng rác** ở thanh chọn. Đọc màn xem trước, bỏ chọn ngoại lệ rồi áp dụng.
- **Tab Thùng rác:** có **Khôi phục** và **Xoá vĩnh viễn** trên hàng và thanh chọn nhiều bài. Khôi phục trả về nháp như luồng cũ, không tự đăng lại. Xoá vĩnh viễn chỉ dành cho quản trị có quyền duyệt/xoá bài; phải đánh dấu xác nhận ở màn xem trước và từng lượt tiếp tục. Không có nút xoá sạch mọi bài ngoài danh sách đã xem trước, không tự chạy tiếp lô xoá vĩnh viễn.
- **Bài mới → câu Đang chờ quyết trùng:** bấm **Kiểm tra chờ trùng**. Nếu cặp còn hợp lệ, mở đúng thẻ Có thể trùng (kể cả Để sau); thẻ bị mất được tạo lại. Nếu câu đã được dùng hoặc đối tượng so trùng đã mất/vào thùng rác, đóng cờ chờ hết hiệu lực, ghi nhật ký rồi trở về Kho. Nếu cặp đã có thẻ được người xử lý, giữ quyết định cũ và gỡ cờ chờ sót. Có nhiều cặp thì đối soát từng cặp, vẫn chặn đăng khi còn cặp hợp lệ.

## Phạm vi dữ liệu

Xoá vĩnh viễn không lấy lại được bằng Khôi phục: WordPress xoá bài, meta/phần bài, bình luận và quan hệ phân loại của bài. Hồ sơ nguồn, tệp ảnh trong Thư viện và nhật ký được giữ. Các chỗ nhắc đến bài (ví dụ tuyển tập, câu kho đã thành bài) giữ hồ sơ cũ để đối soát; chức năng này không tự dồn chúng sang một bài khác. Chỉ mục tìm, tóm tắt và vân tay đi qua hook xoá hiện có. Các cặp chờ trùng trỏ bài vừa xoá được đóng hết hiệu lực cùng giao dịch.

Mọi thao tác ghi dùng POST, nonce và kiểm quyền. Màn xem trước chốt danh sách; xoá vĩnh viễn so dấu phiên của bài và meta dưới khoá bài, từ chối nếu bài đã thay đổi hoặc được khôi phục. Ghi nhật ký và xoá trong giao dịch DB, hoàn nguyên nếu bước bắt buộc thất bại. Không cam kết hoàn nguyên tác dụng bên ngoài DB của plugin khác.

Đối soát chờ trùng không chạy khi mở trang GET, không gọi bộ tự gộp, không kết luận cùng bài / dị bản / bài khác. `het_hieu_luc` là trạng thái kỹ thuật của cặp không còn xử lý được, không phải nhận định về lời. Không đổi schema.

## Kiểm chứng

Hồi quy WordPress ở `wordpress/v2/tests/kiem-xoa-va-cho-trung.php` nằm trong `npm run test:v2`: xác nhận bắt buộc cả phía máy chủ, quyền, xem trước, xung đột sau sửa/khôi phục, lỗi nhật ký/WordPress, giữ nguồn/ảnh/lịch sử, đóng cặp chờ, nối thẻ mất, không tạo thẻ lặp, giữ Để sau/quyết định cũ và xử lý nhiều cặp. Kiểm thử chỉ dùng dữ liệu kỹ thuật tổng hợp; không thao tác xoá bài trên site thật.
