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

**Đã triển khai và xác minh trên phongdao.com ngày 13/11/2026:** plugin **1.0.59**, theme **1.0.7**. Mã sản phẩm qua [PR #171](https://github.com/truonganhquyen/ca-dao/pull/171), commit phát hành `5e7aace`. Tất cả hạng mục trong bảng nghiệm thu đã làm; không có hạng mục nào trong bốn ngoại lệ được mở lại.

| Kiểm tra | Kết quả |
|---|---|
| `npm run kiem-tra` | Đạt; 162 task, một câu hỏi mở, 0 lỗi nhất quán |
| `npm run test:v2` | Đạt; 255 kiểm tra PHP thuần + 574 WordPress + 55 chuyển đổi; cùng bộ WordPress còn đạt 574 trên MySQL local |
| `npm run quet:v2` | Đạt, 0 lỗi được phát hiện; giữ quét/ngân sách cũ và thêm trang sửa 36 mục ở sáu cỡ gập/mở, Danh mục ở hai cỡ |
| `npm run e2e:v2` | Đạt cả CI và lượt local cuối: 61 bước, 0 lỗi, 0 bỏ qua; gồm bảy nhóm hồi quy mới |
| `npm run kiem-site-that` | Đúng plugin 1.0.59/theme 1.0.7 sau hệ thống tự cập nhật |
| wp-admin thật, chỉ đọc | Sáu bài gồm năm thể loại và một bản nháp × 390/1440 px: mục lục tới đúng phần, thể thơ và lối thêm mục hiện đúng quyền, không tràn ngang, không tự phát sinh phần chưa lưu; focus ô chọn không bị che; quét axe 0 lỗi ở trạng thái kiểm cuối |
| Danh mục thật, chỉ đọc | Mở đủ năm màn WordPress có form thêm và danh sách sửa; không tạo/sửa/gỡ mục thật |

[CI V2 đã đạt](https://github.com/truonganhquyen/ca-dao/actions/runs/43408900445). Các phép ghi, gồm tải/lưu ảnh, thể thơ, danh mục, nonce sai, xung đột hai tab, lỗi giữa giao dịch và lưu không JavaScript, chạy trên WordPress mẫu với dữ liệu kỹ thuật. Kiểm production chỉ xác nhận phiên bản và giao diện/quyền hiển thị, không thay cho thử ghi trên tác phẩm thật.

**Các lượt không tính đạt:** Playground từng không khởi động vì ổ C thiếu chỗ; một lượt chạy đồng thời vượt ngân sách PHP và trượt cửa sổ Hoàn tác 10 giây. Đã chuyển thư mục tạm, giữ hồ sơ lỗi và chạy lại tuần tự; PHP còn 1,38 giây, E2E cuối đạt 61/61. Không nới ngưỡng hoặc bỏ kiểm thử. Một lượt quét live khi mở nhóm ngoài màn hình tại vị trí Lịch sử báo ô chọn bị thanh dính che; nhãn bấm thực cao 46 px. Kiểm focus bằng bàn phím xác nhận ô tự cuộn ra vùng không bị che; quét lại ở đầu trang sau khi mở nhóm đạt. Đây là điều chỉnh cách đặt phép quét, không phải sửa dữ liệu hoặc che lỗi bằng mã sản phẩm.

Hồ sơ local ở `output/playwright/editor-implementation/` (không đưa vào repo công khai): log từng cổng, `native-result.log`, ảnh bài mẫu 36 mục, `site-version.log`, `live-result.json`, cùng các lượt lỗi và chẩn đoán vị trí cuộn. Không lưu tài khoản, cookie hay nội dung văn hoá thật vào hồ sơ kết quả.

[Hướng dẫn trang sửa, thể thơ và danh mục](huong-dan/sua-day-du-va-phan-loai.md). Pilot nội dung thật vẫn chờ người biên soạn; chưa có số đo tác vụ người thật để kết luận mức tăng năng suất.
