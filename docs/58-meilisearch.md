# 58 · Meilisearch — tìm gần và kiểm trùng

Ngày 21/11/2026 · D-131 · T-239 · đang kiểm chứng, chưa bật site thật.

Người sáng lập chốt Meilisearch làm hướng chính sau PoC. Tích hợp nhỏ trong plugin hiện có, không tạo màn quản trị hay luồng biên tập mới. Đây là phạm vi riêng đã được yêu cầu, không mở rộng PoC schema của `docs/57`.

## Phạm vi nghiệm thu

- Tìm hiện tại giữ nguyên bộ lọc, phân trang, từ điển và gợi ý khi gõ. Khi gửi câu tìm không có kết quả trọn chữ, thêm tối đa năm bài gần, ghi rõ là gợi ý; không nới cụm trong ngoặc kép.
- Thêm tác phẩm và đóng góp dùng chung chuẩn hoá và đối chiếu. Chỉ khớp toàn bộ lời có dấu sau chuẩn hoá mới là khớp hệt; bỏ dấu hoặc gần giống không chặn như khớp hệt. Đoạn trích phân biệt với toàn văn. Người biên soạn vẫn quyết dị bản, nguồn, thể loại và đăng bài.
- Lõi chống trùng tự động của kho giữ nguyên; chưa thay luật tự nối kho hoặc các quyết định trùng đã lưu. Meilisearch chưa thay mọi nơi tìm trong quản trị.
- NFC, thường hoá, Ð/ð ↔ Đ/đ, dấu kiểu cũ/mới, khoảng trắng đặc biệt, ký tự định dạng ẩn; chỉ trong bản tìm, giữ nguyên lời lưu.
- Mỗi lần tìm gần tối đa một HTTP multi-search (2–18 nhánh, 30 ứng viên/nhánh). Đọc lại lời và quyền từ WordPress, kiểm hash bản chữ rồi mới đối chiếu. Điểm là giống đoạn, không phải xác suất trùng.
- Hai chỉ mục public/staff. Public loại nháp, mật khẩu, cờ thô tục ngay khi xuất, và kiểm lại lúc trả kết quả. Staff chỉ gọi khi có quyền soạn. Không gửi email, bút danh, nguồn/hồ sơ cá nhân vào engine; khoá chỉ phía máy chủ.
- Khi sửa/ẩn/xoá/đổi thể loại, hàng đợi bền chặn sử dụng chỉ mục cũ tới khi xác nhận cả hai index. WP-Cron thử lại; không coi HTTP 202 là đã hoàn tất. Khoá chống hai worker chạy chồng. Dựng lại dùng index mới, chuyển tên sau khi ghi xong.
- Engine lỗi/chưa dựng/đang đồng bộ: trang tìm vẫn dùng tìm hiện tại; Thêm bài báo chưa kiểm được đầy đủ; góp bài dùng cơ chế báo lỗi và giữ chữ hiện có. Không coi lỗi là không trùng.

## Cấu hình và thao tác vận hành

Chỉ chạy trên trang thử hoặc lần triển khai đã được người vận hành cho phép. Không đăng ký dịch vụ, không đổi DNS. Meilisearch ghim `v1.57.0`, chỉ lắng nghe loopback, tắt telemetry, dữ liệu lưu bền, tự khởi động lại; không mở cổng ra Internet. Giới hạn CPU/RAM theo máy thật sau kiểm tải; snapshot PoC không phải cấu hình sizing.

Trong cấu hình WordPress ngoài repo, đặt `PHONGDAO_MEILI_BAT=true`, `PHONGDAO_MEILI_URL` dạng `http://127.0.0.1:7700`, `PHONGDAO_MEILI_KEY` là khoá riêng đủ mạnh. Tạo khoá trực tiếp tại máy chủ, không gửi qua chat, không đưa vào repo. Bản đầu chỉ chấp nhận loopback IPv4; PHP và Meili phải cùng host/network namespace.

Chạy `wp phongdao meili --dung-lai`; sau đó `wp phongdao meili` phải báo sẵn sàng. Lệnh dựng lại cũng xử lý hàng đợi phát sinh trong quá trình dựng. WP-Cron tự đồng bộ các lần sửa sau đó; có thể chạy `wp phongdao meili --dong-bo` khi cần. Các lệnh chỉ ghi chỉ mục/hàng đợi, không sửa lời hay trạng thái tác phẩm.

Khoá `phongdao_meili_khoa` không tự hết hạn để tránh hai lượt ghi chồng. Nếu tiến trình bị dừng cứng: kiểm chắc không còn worker/CLI đang chạy, rồi người vận hành xoá riêng option khoá và chạy đồng bộ lại. Build lỗi trước khi đổi index giữ index hoạt động cũ; chỉ mục dựng dở cần dọn riêng theo tên của lượt lỗi. Hàng đợi chỉ xoá bằng điều kiện token khớp, nên lượt sửa mới không bị lượt cũ xác nhận nhầm.

Hoàn tác: đặt `PHONGDAO_MEILI_BAT=false`; các đường tìm/so trùng trở về hành vi trước. Không cần hoàn tác nội dung. Dừng dịch vụ sau đó nếu không còn dùng; giữ dữ liệu chỉ mục để điều tra khi cần. Khi bật lại phải dựng lại toàn bộ trước để bù thay đổi xảy ra trong lúc tắt.

## Kiểm chứng

Các kiểm thử WordPress nằm trong `npm run test:v2`: mặc định tắt, hai phạm vi, mật khẩu/ẩn, task thất bại, thử lại, hàng đợi, một HTTP multi-search, kiểm hash lời hiện tại, quyền staff, thông báo admin. Kiểm thuần: chuẩn hoá và phân biệt toàn văn/đoạn/khác dấu/câu dài.

Chạy đủ `kiem-tra`, `test:v2`, `quet:v2`, `e2e:v2`. Bản thử engine thật lấy lời công khai vào thư mục riêng ngoài repo, chỉ lưu số đo; kết quả và giới hạn bổ sung sau khi chạy. Truy vấn sinh biến đổi kỹ thuật chưa thay thế bộ nhãn do con người đánh giá, không suy độ chính xác toàn kho từ vài tác phẩm.

Để bật site thật cần: bộ đo đại diện, kiểm engine thật; CI đủ; thử dựng/đồng bộ/ẩn/xoá/dị bản; kiểm service, cron, quyền, hết kết nối; xác nhận cấu hình triển khai cụ thể. Chưa có bằng chứng thì ghi chưa đạt, không suy từ mã đã viết.
