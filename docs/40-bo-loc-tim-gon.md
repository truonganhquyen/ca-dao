# Bộ lọc Tìm gọn — D-113

Người sáng lập duyệt 14/11/2026: máy tính hiện hai ô chọn **Cách khớp · Thể loại** và nút **Áp dụng** trên một hàng, nền nhẹ; điện thoại mặc định thu vào **Bộ lọc**, luôn có dòng tóm tắt lựa chọn. Số bài ở trong lựa chọn; **Đặt lại** chỉ hiện khi khác mặc định, giữ từ khoá và về trang 1. Không kết quả thì mở sẵn bộ lọc và chỉ lối sang phạm vi còn bài. Giữ vùng chạm 44 px, bàn phím và hoạt động khi tắt JavaScript. Plugin 1.0.63/theme 1.0.10, [PR #177](https://github.com/truonganhquyen/ca-dao/pull/177).

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

## Kết quả nghiệm thu 14/11/2026

- `kiem-tra`: 166 task, 1 câu hỏi mở, 0 lỗi.
- `test:v2`: 273 ca PHP thuần, 628 ca WordPress Playground, 55 ca chuyển đổi đạt trên CI. PHP thuần 2,6 giây trên CI, dưới ngưỡng 5 giây.
- `quet:v2`: 43 trang × 3 chế độ, 16 trang × 2 chế độ 320 px, các màn biên tập/admin/trang sửa; 0 lỗi. Trang bài dùng 9,6 KB JavaScript, trang Tìm 10,6 KB, dưới 21 KB.
- `e2e:v2`: 68/68 bước, 0 lỗi, 0 bỏ qua trong [lượt CI đầy đủ](https://github.com/truonganhquyen/ca-dao/actions/runs/43593950385). Gồm chọn hai ô rồi Áp dụng/Đặt lại và bàn phím ở 320/390/1440 px, bật/tắt JavaScript. Lượt CI trước có hai lỗi chờ chuyển trang ở thao tác Lô wp-admin, không tính là đạt; giữ nguyên kiểm thao tác hàng loạt.
- Xem trực tiếp bản thử 390 px: phần số bài và bộ lọc thu gọn cao 70,5 px; thẻ đầu bắt đầu ở y=516 px, nằm trong màn 844 px. Đây là dữ liệu mẫu, chưa phải phép đo trên site thật với truy vấn gốc. Máy tính 1440 px: hai select và Áp dụng cùng hàng, Đặt lại chỉ hiện khi có lựa chọn khác mặc định.
- Kiểm trực tiếp luồng GET khi bật/tắt JavaScript: thay từ khoá và chọn đồng thời hai ô gửi đúng cả ba giá trị; Đặt lại giữ từ khoá, về trang 1 và tự mở khi 0 kết quả. Gõ gợi ý rồi Enter giữ thể loại và trạng thái đang mở.
- Lượt tại Windows đầu có ca PHP đúng nhưng quá ngưỡng thời gian; một lượt khác thiếu bộ nhớ khi chạy nhiều Playground. Bộ quét bị ảnh hưởng đã dừng, không tính là đạt; không hạ ngưỡng hay bỏ bước. Kết quả bốn cổng trên CI độc lập là căn cứ gộp.

CI của head cuối phải đạt trước khi gộp. Kiểm phiên bản và UI site thật chỉ đọc sau gộp ghi trong PR; chưa dùng kiểm bản thử để khẳng định site đã cập nhật. Pilot T-198/H2 vẫn chờ người biên soạn.
