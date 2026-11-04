# Review độc lập V2 · 04/11/2026

Kịch bản khảo sát, không sửa sản phẩm. Báo cáo ở `docs/e2e-review/v2/REPORT.md`.

## Site thật: chỉ đọc

Node có `@playwright/test`, `axe-core` từ `npm ci`, Chrome đã cài. Không cần tài khoản. **Chạy tuần tự từng lệnh, không mở một lượt khảo sát site thật khác đồng thời.** Mỗi kịch bản chặn mọi phương thức ngoài GET/HEAD, chặn service worker, cách điều hướng ít nhất 1,1 giây. Không gửi Góp, cảm nghĩ, Mong có câu này hay Báo vi phạm. Tải PNG là form GET của sản phẩm.

```sh
node tests/e2e-v2/review-public.mjs --full
node tests/e2e-v2/review-followup.mjs
node tests/e2e-v2/review-interactions.mjs
node tests/e2e-v2/review-source.mjs
node --import ./tests/e2e-v2/throttle-puppeteer.mjs scripts/e2e-v2.mjs --site https://phongdao.com
```

`review-public` lấy danh sách qua phân trang Kho, chọn mẫu tái lập (seed 20261104, 8 bài/thể loại), đo 5 khổ × 2 chế độ; `--full` đếm thêm các bài còn lại. `review-followup` cần kết quả lượt đầu, kiểm lại vùng thay đổi ở 1.0.3, đo ngân sách bằng phiên mới và đọc không JavaScript. `review-interactions` bổ sung thao tác; nhãn PASS của bước quan sát nghĩa là thu được dữ liệu, **không** tự kết luận mọi thuộc tính trong dữ liệu đạt. Ví dụ bước xác định phần tràn có thể thu được `overflow: 3`. `review-source` so hai trang nguồn đã xác định, chỉ ghi số dòng khớp, không lưu lời nguồn.

Chi tiết JSON và nhật ký ở `test-results/review-v2/`, đã bị Git bỏ qua. Không đưa các tệp này vào PR: có thể chứa văn bản trích từ trang. Ảnh công khai ở đường dẫn báo cáo. Kết quả ngân sách của lượt quét dài chỉ dùng sàng lọc; số kết luận phải lấy từ phiên mới, đúng khổ màn hình. Các bài khác nhau có thể tải ảnh lazy khác nhau ở cùng một viewport.

## Playground: được ghi dữ liệu mẫu

Node ≥ 24.18. Dùng blueprint `wordpress/v2/blueprint.json`, gắn plugin/theme V2 vào WordPress Playground CLI, cổng tùy chọn tại máy. Các kịch bản sau chỉ nhận địa chỉ loopback, không nhận tên miền site thật:

```sh
node tests/e2e-v2/review-playground.mjs http://127.0.0.1:9436
node tests/e2e-v2/review-queue-shortcuts.mjs http://127.0.0.1:9436
```

Blueprint đăng nhập sẵn quản trị. Phiên khách đặt cookie bỏ qua tự đăng nhập của Playground trước khi mở trang. Dữ liệu thử có dấu `[E2E-TEST]`, chỉ tồn tại ở bản mẫu; ảnh quản trị ở `docs/e2e-review/screenshots/admin/v2/` bị Git bỏ qua. Không dùng thông tin đăng nhập site thật.

`fixture-review.php` là bộ tạo thẻ giả lập, **không phải plugin, không triển khai**. Chỉ chép tạm vào gốc WordPress của một Playground tại máy đã dựng bằng blueprint trên; mở tệp qua loopback rồi xoá ngay. Nó tạo sáu loại thẻ, lời và cảm nghĩ thuần kiểm thử. Không chứa tài khoản hay mật khẩu. Khảo sát đã kiểm từng nút bằng trình duyệt; fixture tự nó không chứng minh các nút hoạt động. `review-queue-shortcuts` cần ít nhất hai thẻ và một thẻ có nút Để sau.

Trong lần review này, Node mặc định 22.20 chưa chạy được Playground; dùng Node 24.18 tạm qua npm, PHP 8.4.12 có mbstring/intl/GD. Trên Windows, gói CLI 3.1.57 có tên executable không khớp tên mà npx suy ra; đã dùng shim **ngoài repo** trỏ tới entrypoint của đúng gói để chạy các lệnh chuẩn, không thay kiểm thử sản phẩm.

## Giới hạn

- Axe không thay thế toàn bộ kiểm bàn phím hay đánh giá bằng người dùng trợ năng.
- DOM lời trống ở tục ngữ không đồng nghĩa mất lời: lời nằm trong tiêu đề lớn.
- Đếm bài công khai không phải đối chiếu cơ sở dữ liệu trước/sau chuyển đổi.
- Không suy ra quyền dùng nguồn, tính đúng của văn bản, hay trạng thái duyệt từ hình thức trình bày.
- Không đưa ảnh quản trị, dữ liệu kho thô, tệp môi trường hoặc nhật ký chứa dữ liệu riêng tư lên Git.
