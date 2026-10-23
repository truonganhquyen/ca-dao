# 30 · Sổ tay vận hành

| | |
|---|---|
| Phiên bản | 1.1 · 05/10/2026 |
| Trạng thái | **Nháp.** Hoàn thiện trước khi ra mắt công khai (T-201); mục 5 điền khi tạo tài khoản dịch vụ |
| Đọc khi | Hằng tuần; khi có sự cố; khi một người khác cần tiếp quản dự án |

Mục tiêu: dự án sống được với **khoảng 10 giờ/tuần của một người**, và một người khác có thể tiếp quản chỉ bằng kho mã + tài liệu này.

---

## 1. Nhịp làm việc

**Hằng tuần (~10 giờ, từ GĐ2):**

| Việc | Giờ |
|---|---|
| Biên soạn hồ sơ mới (10–12 hồ sơ) | 6–7 |
| Duyệt đóng góp theo lô (`40`) | 1–2 |
| Phát triển, sửa lỗi | ~1 |
| `npm run health`, xem chỉ số, cập nhật danh sách ưu tiên | 0,5 |

Ở GĐ0–GĐ1 tỉ lệ đảo lại: phần lớn thời gian cho phát triển.

**Hằng tháng:** sắp xếp lại danh sách ưu tiên (`13` mục 4); gửi danh sách việc cho cố vấn (`42`); xuất bản sao bảng tính riêng tư; xem North Star và các chỉ số ở `00` mục 8.
**Hằng quý:** bản phát hành dữ liệu (`21` mục 10); thử khôi phục từ sao lưu; xem lại rủi ro ở `00` mục 9.

**Trần thời gian:** vượt 12 giờ/tuần hai tuần liền thì cắt việc, không cắt chất lượng: giảm số hồ sơ/tuần trước, sau đó dời tính năng.

## 2. Sao lưu

| Dữ liệu | Cách | Tần suất |
|---|---|---|
| Kho mã (`content/`, mã, tài liệu) | Đẩy lên bản sao kho thứ hai | Hằng tuần |
| Bảng tính riêng tư (đóng góp, hồ sơ đồng ý) | Xuất CSV, nén có mật khẩu, lưu ổ riêng | Hằng tháng |
| Ảnh chụp trang sách (bằng chứng nội bộ) | Ổ riêng, kèm SHA-256 trong bảng tính riêng tư | Hằng tháng |
| Âm thanh gốc | Internet Archive + ổ riêng | Khi nhận |
| Bản phát hành dữ liệu | GitHub Releases + Zenodo | Hằng quý |

**Thử khôi phục mỗi quý:** clone bản sao kho thứ hai vào máy mới → `npm ci` → `npm run check:data` → build → so sánh số trang với site đang chạy; kiểm SHA-256 của vài tệp nhị phân ngẫu nhiên.

## 3. Xử lý sự cố

| Sự cố | Làm gì |
|---|---|
| Build thất bại | Bản cũ vẫn chạy. Đọc thông báo lỗi Rxx, đối chiếu `10` mục 9, sửa dữ liệu. Không tắt kiểm tra để build qua. Không sửa trực tiếp trên môi trường chính thức; mọi sửa đi qua PR (`21` mục 3) |
| Site không truy cập được | Kiểm trạng thái Cloudflare; nếu kéo dài > 24 giờ, triển khai bản build sẵn lên dịch vụ thay thế (`21` mục 1) |
| Phát hiện lời hoặc nguồn sai trên trang | Đặt tác phẩm về `an` (ẩn ngay, không làm hỏng build), sửa, kiểm lại, xuất bản lại; ghi lý do trong commit |
| Yêu cầu gỡ ghi âm hoặc gỡ tên | Gỡ trong 7 ngày, không cần lý do (`41` mục 4) |
| Khiếu nại bản quyền | Gỡ tạm nội dung bị khiếu nại trong khi xem xét; trả lời trong 7 ngày |
| Spam qua form | Bật thêm chống bot của dịch vụ form; không bao giờ tự xuất bản |
| Lộ dữ liệu cá nhân trong kho công khai | Xóa khỏi kho, viết lại lịch sử Git nếu cần, thông báo cho người liên quan |

## 4. Nơi lưu riêng tư

| Thứ | Ở đâu | Ai truy cập |
|---|---|---|
| Bảng tính đóng góp | [CAN_BO_SUNG] | Người sáng lập |
| Hồ sơ đồng ý ghi âm | [CAN_BO_SUNG] | Người sáng lập |
| Ảnh chụp trang sách | [CAN_BO_SUNG] | Người sáng lập, cố vấn khi cần |

## 5. Tài khoản dịch vụ (bus factor)

Mọi tài khoản bật xác thực hai lớp. Ghi ở đây **chủ sở hữu và cách khôi phục**, không ghi mật khẩu.

| Dịch vụ | Dùng cho | Chủ sở hữu | Khôi phục | Người dự phòng |
|---|---|---|---|---|
| Nhà đăng ký tên miền | Tên miền | [CAN_BO_SUNG] | | |
| GitHub | Kho mã chính | | | |
| Kho thứ hai | Sao lưu | | | |
| Cloudflare | Lưu trữ web, Worker, R2 | | | |
| Dịch vụ form | Đóng góp | | | |
| Công cụ đo lường | Chỉ số | | | |
| Internet Archive | Âm thanh | | | |
| Zenodo | DOI dữ liệu | | | |
| Email liên hệ của dự án | Liên hệ, khiếu nại | | | |

## 6. Tiếp quản dự án

Một người mới cần, theo thứ tự:
1. Quyền vào các tài khoản ở mục 5 (qua người dự phòng).
2. Đọc `README.md` → `00` → `01` → `10` → `11`.
3. Chạy `npm ci && npm run check:data && npm run test:fixtures`.
4. Biên soạn thử một hồ sơ theo `11` mục 1.
5. Đọc mục 1 của tài liệu này để giữ nhịp.
