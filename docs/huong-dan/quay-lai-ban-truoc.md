# Hướng dẫn · Quay lại bản trước khi site lỗi

| | |
|---|---|
| Cho ai | Người sáng lập (cách 1 không cần dòng lệnh); agent (cách 2) |
| Làm khi | Ngay sau khi một PR được gộp, site hiện sai hoặc một chức năng hỏng |
| Mất bao lâu | Khoảng 10 phút (chờ kiểm tra tự động và máy chủ tự cập nhật) |

Máy chủ tự lấy bản mới từ GitHub mỗi 5 phút. Nếu bước thay thư mục hoặc kiểm trang chủ thất bại, script cố phục hồi bản cũ và ghi lỗi vào `/home/cadao/cap-nhat.log`. Nếu nhật ký báo **chưa phục hồi hết**, giữ nguyên thư mục `.ca-dao-cu` và nhờ agent kiểm tra trước khi chạy lại. Hướng dẫn dưới đây dành cho trường hợp trang vẫn mở nhưng có chỗ hỏng.

**Trước khi bấm Revert, xác định bản cần quay về:**

- **V2 → V2:** chỉ đổi mã; dùng Cách 1 hoặc Cách 2. Sau đó kiểm trang chủ, một bài, Hộp việc và sửa một phần trên bản thử.
- **V2 → MVP (qua mốc chuyển):** làm theo mục *Quay qua mốc V2 → MVP* bên dưới **trước khi Revert hoặc thay mã**. Cách 1 một mình không đủ vì dữ liệu V2 cần hoàn tác trước. Chỉ agent được người sáng lập đồng ý cho lần ghi lên máy chủ mới thực hiện bước dữ liệu.

## Cách 1 · Trên GitHub, chỉ cho V2 → V2 (điện thoại hay máy tính đều được)

1. Mở github.com/truonganhquyen/ca-dao → **Pull requests** → **Closed**.
2. Mở PR vừa gộp (PR có chỗ hỏng), kéo xuống cuối, bấm **Revert**.
3. GitHub tạo một PR mới tên "Revert …". Bấm **Create pull request**.
4. Chờ dấu xanh ✓ (kiểm tra tự động, vài phút), rồi bấm **Merge pull request** → **Confirm merge**.
5. Khoảng 5 phút sau, site chạy lại bản trước PR đó.

Hoặc nhắn agent: "Quay lại bản trước PR số …" — agent làm đúng các bước trên.

## Cách 2 · Trên máy chủ (agent làm khi được đồng ý cho lần ghi, cần SSH)

Nhanh hơn cách 1 khi cần gấp. Bản đã lên site ghi trong `/home/cadao/.cap-nhat-lich-su` (mới nhất ở cuối):

```bash
tail -n 5 /home/cadao/.cap-nhat-lich-su
cd /home/cadao && sudo -u cadao NHANH=<mã commit của bản muốn quay về> bash bin/cap-nhat-plugin.sh
```

## Quay qua mốc V2 → MVP

Đây là quy trình riêng, không dùng ngay nút Revert. Đọc `docs/29` mục 5, chọn mã commit MVP trước ngày chuyển và kiểm bản sao lưu đêm trước có trong FlashPanel (D-090 không yêu cầu thử khôi phục). Sau khi người sáng lập đồng ý cho lần ghi này:

1. Khi **mã V2 còn đang chạy**, agent dùng WP-CLI của site chạy `wp phongdao nhap --hoan-tac`; kiểm kết quả và đối chiếu số bài, quyết định còn lại. Nếu hoàn tác thất bại, dừng tại V2, không đổi mã.
2. Dùng Cách 2 với `NHANH=<mã commit MVP>` hoặc gộp PR Revert của PR chuyển đổi. Script chọn đúng mã MVP vì commit đó chưa có `wordpress/nguon-trien-khai`.
3. Khi mã MVP đã chạy, dùng WP-CLI của site chạy `wp option delete cadao_tim_phien_ban cadao_trung_phien_ban` để MVP dựng lại chỉ mục tìm và chống trùng. Kiểm trang chủ, một bài cũ và đường dẫn cũ; nếu đường dẫn hỏng thì chạy `wp rewrite flush`. Kiểm cả lối biên tập bằng tài khoản thử.

Quay mã không tự quay lại các chỉnh sửa nội dung hoặc cài đặt đã phát sinh sau ngày chuyển. Ghi sai khác để người sáng lập quyết, không tự gộp hay xoá bài. Việc khôi phục bản sao lưu FlashPanel là lựa chọn vận hành riêng của người sáng lập.

Site giữ bản đó tới khi `main` có commit mới. Sau đó vẫn phải sửa lỗi trên GitHub (cách 1 hoặc PR sửa), nếu không lần gộp tiếp theo sẽ đưa lỗi trở lại.

## Điều cần biết

- Quay lại chỉ đổi **mã** (plugin, theme). **Dữ liệu** đã thay đổi (bài bị gộp, cài đặt, bài vào thùng rác) không tự quay lại; cần đối chiếu và quyết định cách xử lý từng trường hợp.
- Vì vậy sao lưu hằng ngày phải bật trước khi dùng các công cụ sửa hàng loạt (gộp bài, chuyển khối Thơ, duyệt hàng loạt).
