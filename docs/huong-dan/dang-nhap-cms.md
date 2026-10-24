# Hướng dẫn · Tạo đăng nhập GitHub cho CMS

| | |
|---|---|
| Cho ai | Người sáng lập (không cần dòng lệnh, không cần đọc mã) |
| Làm khi | Một lần, trước khi T-037 (CMS ở `/admin`) chạy được; khoảng 15–20 phút, làm trên máy tính |
| Kết quả | Một "người gác cửa" nhỏ trên Cloudflare (Worker `sveltia-cms-auth`) cho phép bạn bấm **Đăng nhập bằng GitHub** ở trang `/admin`, cả trên điện thoại |
| Sau đó | Gửi cho agent **địa chỉ của Worker** (bước 1). **Không gửi** Client Secret cho ai, kể cả agent |

CMS (Sveltia) ghi bài thẳng vào repo `ca-dao` trên GitHub bằng tài khoản của bạn. Để GitHub tin trang `/admin`, cần ba thứ: một Worker nhỏ trên Cloudflare (mã có sẵn của Sveltia, không phải mã của dự án), một "ứng dụng OAuth" trên GitHub và hai khóa nối chúng với nhau. Tên nút có thể khác đôi chút theo thời gian; nếu không thấy đúng chữ, tìm nút có ý tương tự.

## Bước 1 · Đặt Worker đăng nhập lên Cloudflare

1. Mở trang [github.com/sveltia/sveltia-cms-auth](https://github.com/sveltia/sveltia-cms-auth), kéo xuống phần hướng dẫn, bấm nút **Deploy to Cloudflare** (Triển khai lên Cloudflare).
2. Cloudflare mở ra, có thể hỏi đăng nhập và hỏi tạo một repo GitHub mới chứa mã của Worker:
   - giữ tên **`sveltia-cms-auth`**; để repo ở chế độ **riêng tư** (Private) nếu có lựa chọn;
   - nếu GitHub hỏi cấp quyền cho ứng dụng **Cloudflare Workers and Pages**: cho phép thêm repo `sveltia-cms-auth`. Vẫn **không** chọn `ca-dao-kho-tho`.
3. Bấm **Create and deploy** / **Deploy**. Chờ 1–2 phút tới khi báo thành công.
4. Chép **địa chỉ của Worker**, dạng:

   ```text
   https://sveltia-cms-auth.truonganhquyen.workers.dev
   ```

   (Workers & Pages → `sveltia-cms-auth` → địa chỉ hiện ở đầu trang, hoặc trong **Settings → Domains & Routes**.)

## Bước 2 · Tạo ứng dụng OAuth trên GitHub

1. Mở [github.com/settings/applications/new](https://github.com/settings/applications/new) (GitHub → ảnh đại diện → **Settings** → **Developer settings** → **OAuth Apps** → **New OAuth App**).
2. Điền:

   | Ô | Điền |
   |---|---|
   | Application name | `Kho tàng Ca dao – CMS` |
   | Homepage URL | `https://ca-dao.truonganhquyen.workers.dev` |
   | Application description | để trống |
   | Authorization callback URL | địa chỉ Worker ở bước 1 **thêm `/callback`**, ví dụ `https://sveltia-cms-auth.truonganhquyen.workers.dev/callback` |
   | Enable Device Flow | để trống (không đánh dấu) |

3. Bấm **Register application**.
4. Trên trang vừa mở:
   - chép **Client ID** (dãy chữ số, không bí mật);
   - bấm **Generate a new client secret**, GitHub có thể hỏi lại mật khẩu hoặc mã hai lớp. Chép **Client Secret** ngay — GitHub chỉ hiện **một lần**. Đây là khóa bí mật: không dán vào chat, email, tài liệu hay repo.

## Bước 3 · Nối hai khóa vào Worker

1. Cloudflare → **Workers & Pages** → `sveltia-cms-auth` → **Settings** → **Variables and Secrets** (có nơi ghi **Variables**) → **Add**.
2. Thêm ba dòng:

   | Tên (Variable name) | Giá trị | Kiểu |
   |---|---|---|
   | `GITHUB_CLIENT_ID` | Client ID ở bước 2 | Text (văn bản thường) |
   | `GITHUB_CLIENT_SECRET` | Client Secret ở bước 2 | **Secret** (hoặc bấm **Encrypt**) |
   | `ALLOWED_DOMAINS` | `*.truonganhquyen.workers.dev` | Text |

   `ALLOWED_DOMAINS` là tên miền của **trang có CMS** (trang `ca-dao`), **không phải** địa chỉ của Worker; gõ đúng `*.truonganhquyen.workers.dev`, không có `https://`. Dòng này chỉ cho phép trang chính thức và các bản xem trước của bạn dùng Worker này. Khi có tên miền riêng (T-049), agent sẽ nhắc bạn thêm tên miền đó vào dòng này.
3. Bấm **Deploy** / **Save and deploy**.

## Bước 4 · Báo cho agent

Gửi cho agent đúng một dòng: **địa chỉ Worker** ở bước 1 (ví dụ `https://sveltia-cms-auth.truonganhquyen.workers.dev`). Agent ghi địa chỉ này vào cấu hình CMS ở T-037; sau khi PR của T-037 gộp, bạn mở `/admin` trên điện thoại và bấm **Sign in with GitHub**.

Lần đăng nhập đầu, GitHub hỏi cho phép ứng dụng `Kho tàng Ca dao – CMS` truy cập repo. Bấm **Authorize**. Quyền GitHub cấp theo tài khoản của bạn (về kỹ thuật, đủ để đọc/ghi các repo của bạn, kể cả `ca-dao-kho-tho`), nhưng CMS chỉ được cấu hình để đọc/ghi repo `ca-dao`. Đăng xuất CMS khi dùng máy lạ.

## Kiểm tra (24/10/2026)

Agent đã kiểm Worker `https://sveltia-cms-auth.truonganhquyen.workers.dev` từ bên ngoài: trang chính thức và bản xem trước được chuyển sang GitHub đúng ứng dụng; tên miền lạ bị chặn; Client ID và Client Secret được GitHub chấp nhận. Địa chỉ callback chỉ kiểm được khi bạn đăng nhập thật lần đầu (sau T-037).

## Khi có lỗi

- **Bấm đăng nhập thì báo lỗi "Your domain is not allowed" / không cho phép tên miền:** dòng `ALLOWED_DOMAINS` ở bước 3 phải đúng `*.truonganhquyen.workers.dev` — không có `https://`, không có `/` ở cuối, và không phải địa chỉ của Worker (lỗi hay gặp nhất, gặp ngày 24/10/2026).
- **GitHub báo "redirect_uri is not associated with this application":** ô **Authorization callback URL** ở bước 2 phải đúng địa chỉ Worker + `/callback`.
- **Lỡ để lộ Client Secret:** vào lại ứng dụng OAuth trên GitHub, bấm **Generate a new client secret**, xóa khóa cũ, rồi thay giá trị `GITHUB_CLIENT_SECRET` ở bước 3.
- Không chắc thì chụp màn hình (che Client Secret) gửi cho agent.
