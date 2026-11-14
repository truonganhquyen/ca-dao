# Bộ lọc Tìm gọn — D-113

Người sáng lập duyệt 14/11/2026: máy tính hiện hai ô chọn **Cách khớp · Thể loại** và nút **Áp dụng** trên một hàng, nền nhẹ; điện thoại mặc định thu vào **Bộ lọc**, luôn có dòng tóm tắt lựa chọn. Số bài ở trong lựa chọn; **Đặt lại** chỉ hiện khi khác mặc định, giữ từ khoá và về trang 1. Không kết quả thì mở sẵn bộ lọc và chỉ lối sang phạm vi còn bài. Giữ vùng chạm 44 px, bàn phím và hoạt động khi tắt JavaScript.

Trước sửa, đo trên site thật ở 390 px: hai hàng liên kết lọc cao 336 px, thẻ đầu bắt đầu tại y=818 px. Mục tiêu mặc định sau sửa: phần số bài và bộ lọc thu gọn không quá 100 px trên màn 390 px, thẻ đầu nằm trong màn hình đầu.

## Phạm vi triển khai

- Một form GET `tim-form` sở hữu ô từ khoá, hai select và cả nút Tìm/Áp dụng. Các select ở ngoài hình học của form nhưng gắn bằng thuộc tính HTML `form`; không lồng form và không cần JavaScript để giữ từ khoá. Không gửi số trang cũ khi áp dụng lựa chọn mới.
- Disclosure `details/summary` mở nhóm điều khiển liền kề bằng CSS và có `aria-controls`; CSS màn rộng hiện nhóm sẵn, màn hẹp dùng trạng thái `open` của details. Cùng một cặp select, không nhân đôi điều khiển cho hai kích thước.
- Số trong select là số của truy vấn/phạm vi đang xem; cập nhật khi Áp dụng hoặc khi nhận gợi ý mới. Giữ cả lựa chọn 0 để người đọc có thể đổi từ khoá, cách khớp và thể loại trong một lượt gửi.
- Huy hiệu Bộ lọc (1)/(2) chỉ đếm lựa chọn khác mặc định. Tóm tắt có tên cách khớp và thể loại. Đặt lại về `khop=dung`, mọi thể loại, trang 1, giữ nguyên `q`.
- Khi gõ: vẫn dùng REST và HTML máy chủ hiện có; FormData lấy cả các ô gắn với form. Đổi select hoặc gửi form huỷ yêu cầu đang chờ để gợi ý cũ không ghi đè lựa chọn. Giữ bộ lọc đang mở sau khi thay HTML kết quả.
- Không đổi truy vấn, số đếm, xếp hạng, phân trang, dữ liệu bài, nội dung bị che, nguồn hay đo lường đã chốt trong D-112.
- Công cụ thử nhận `PHONGDAO_CONG_THU` (1024–65535) để các worktree dùng cổng riêng; không dừng máy chủ thử của nhánh khác. Không đổi cổng/cấu hình site thật.

## Nghiệm thu

- 320/390/1440 px, sáng/tối: không tràn ngang; desktop cùng hàng, mobile gập/mở bằng bàn phím; vùng chạm tối thiểu 44 px.
- Mặc định có kết quả: mobile thu gọn, không hiện Đặt lại; số bài và khối lọc cao tối đa 100 px ở 390 px.
- Bật/tắt JavaScript: chọn đồng thời cách khớp và thể loại, sửa từ khoá rồi Áp dụng gửi đúng cả ba giá trị; không mang trang cũ. Tìm và Enter dùng cùng lựa chọn.
- Sau phân trang, các select giữ lựa chọn; Đặt lại giữ `q`, xoá hai lựa chọn, về trang 1. Trường hợp 0 kết quả tự mở và có liên kết sang phạm vi còn bài.
- Hồi quy trong `test:v2`, `quet:v2`, `e2e:v2` hiện có; đủ bốn cổng trước gộp. Kết quả thực tế cập nhật khi chạy xong; chưa coi mã trên nhánh là phiên bản site.
