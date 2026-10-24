# Hướng dẫn · Kết nối Cloudflare để có bản xem trước

| | |
|---|---|
| Cho ai | Người sáng lập (không cần dòng lệnh, không cần đọc mã) |
| Làm khi | Một lần duy nhất, khi PR của T-035 mở ra; khoảng 10–15 phút, làm trên máy tính dễ hơn điện thoại |
| Kết quả | Mỗi PR có **link bản xem trước** để bạn mở trên điện thoại; gộp vào `main` thì trang chính thức tự cập nhật |

Kho mã đã có sẵn mọi cấu hình (`wrangler.jsonc`, `.node-version`). Bạn chỉ cần cho Cloudflare quyền đọc repo `ca-dao` và điền 3 ô lệnh dưới đây. Tên nút trên Cloudflare có thể khác đôi chút theo thời gian; nếu không thấy đúng chữ, tìm nút có ý tương tự.

## Các bước

1. Đăng nhập [dash.cloudflare.com](https://dash.cloudflare.com).
2. Ở menu bên trái, mở **Workers & Pages** (có thể nằm trong nhóm **Compute**). Bấm **Create** (Tạo).
3. Ở thẻ **Workers**, chọn **Import a repository** (Nhập từ repo) → **Connect GitHub** / **Continue with GitHub**.
4. GitHub hỏi cài ứng dụng **Cloudflare Workers and Pages**:
   - chọn **Only select repositories** (Chỉ các repo được chọn);
   - chỉ chọn **`truonganhquyen/ca-dao`**. **Không chọn `ca-dao-kho-tho`** — kho thô không bao giờ được đưa lên web (D-045).
5. Quay lại Cloudflare, chọn repo **`ca-dao`**, rồi điền:

   | Ô | Điền |
   |---|---|
   | Project name (Tên dự án) | `ca-dao` — phải đúng tên này |
   | Build command (Lệnh build) | `npm run build` |
   | Deploy command (Lệnh triển khai) | `npx wrangler deploy` |
   | Builds for non-production branches (Build cho nhánh khác `main`) | **Bật** (thường nằm trong **Advanced settings**) |
   | Non-production branch deploy command | `npx wrangler versions upload` |
   | Root directory / Path | để trống (hoặc `/`) |

   Không cần thêm biến môi trường hay khóa bí mật nào.
6. Nếu Cloudflare hỏi chọn **tên miền phụ `workers.dev`** cho tài khoản: chọn một tên trung tính (ví dụ `ca-dao`), không dùng họ tên hay số điện thoại — tên này nằm trong mọi link xem trước.
7. Bấm **Deploy** (Triển khai).

## Sau khi kết nối

- Cloudflare build nhánh `main` trước. **Lần đầu có thể báo lỗi** vì `main` chưa có trang web (trang nằm trong PR của T-035) — bình thường, không cần làm gì.
- Mở PR của T-035 trên GitHub. Trong vài phút, Cloudflare thêm một dòng kiểm tra (và thường một bình luận) có **link bản xem trước**. Nếu chưa thấy, bấm **Retry build** trong Cloudflare hoặc nhờ agent đẩy lại một thay đổi nhỏ.
- Mở link trên điện thoại: bạn sẽ thấy trang **"Sắp ra mắt"**. Thử bật chế độ tối của điện thoại — trang đổi màu theo.
- Thấy đúng thì bấm gộp PR. Vài phút sau, trang chính thức ở `https://ca-dao.<tên-miền-phụ>.workers.dev` hiện trang "Sắp ra mắt". Tên miền riêng làm sau (T-049).

## Khi có lỗi

- **PR không có link bản xem trước:** chụp màn hình trang build trong Cloudflare (Workers & Pages → `ca-dao` → **Deployments** / **Builds**) và gửi cho agent. Không gộp PR khi chưa xem được bản xem trước.
- **Build báo lỗi dữ liệu** (sau này, khi bạn sửa bài trong CMS): trang cũ vẫn chạy, không có gì bị mất. Dòng bắt đầu bằng `LỖI` trong nhật ký build cho biết tệp nào cần sửa.
