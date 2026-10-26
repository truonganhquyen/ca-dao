# Hướng dẫn · Quay lại bản trước khi site lỗi

| | |
|---|---|
| Cho ai | Người sáng lập (cách 1 không cần dòng lệnh); agent (cách 2) |
| Làm khi | Ngay sau khi một PR được gộp, site hiện sai hoặc một chức năng hỏng |
| Mất bao lâu | Khoảng 10 phút (chờ kiểm tra tự động và máy chủ tự cập nhật) |

Máy chủ tự lấy bản mới từ GitHub mỗi 5 phút. Nếu trang chủ không mở được sau khi cập nhật, máy chủ **đã tự trả lại bản cũ**. Hướng dẫn này dành cho trường hợp trang vẫn mở nhưng có chỗ hỏng.

## Cách 1 · Trên GitHub (điện thoại hay máy tính đều được)

1. Mở github.com/truonganhquyen/ca-dao → **Pull requests** → **Closed**.
2. Mở PR vừa gộp (PR có chỗ hỏng), kéo xuống cuối, bấm **Revert**.
3. GitHub tạo một PR mới tên "Revert …". Bấm **Create pull request**.
4. Chờ dấu xanh ✓ (kiểm tra tự động, vài phút), rồi bấm **Merge pull request** → **Confirm merge**.
5. Khoảng 5 phút sau, site chạy lại bản trước PR đó.

Hoặc nhắn agent: "Quay lại bản trước PR số …" — agent làm đúng các bước trên.

## Cách 2 · Trên máy chủ (agent làm, cần SSH)

Nhanh hơn cách 1 khi cần gấp. Bản đã lên site ghi trong `/home/cadao/.cap-nhat-lich-su` (mới nhất ở cuối):

```bash
tail -n 5 /home/cadao/.cap-nhat-lich-su
cd /home/cadao && sudo -u cadao NHANH=<mã commit của bản muốn quay về> bash bin/cap-nhat-plugin.sh
```

Site giữ bản đó tới khi `main` có commit mới. Sau đó vẫn phải sửa lỗi trên GitHub (cách 1 hoặc PR sửa), nếu không lần gộp tiếp theo sẽ đưa lỗi trở lại.

## Điều cần biết

- Quay lại chỉ đổi **mã** (plugin, theme). **Dữ liệu** đã thay đổi (bài bị gộp, cài đặt, bài vào thùng rác) không tự quay lại: dùng bản sao lưu trong FlashPanel → *Sao Lưu* → khôi phục bản của ngày trước đó.
- Vì vậy sao lưu hằng ngày phải bật trước khi dùng các công cụ sửa hàng loạt (gộp bài, chuyển khối Thơ, duyệt hàng loạt).
