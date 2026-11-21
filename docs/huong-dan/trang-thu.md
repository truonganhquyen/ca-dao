# Dựng trang thử và thử khôi phục sao lưu

| | |
|---|---|
| Trạng thái | Bản v1 · 21/11/2026. Làm khi bạn sẵn sàng; agent hướng dẫn từng bước |
| Mục đích | Có một trang thử riêng tư mang bản sao dữ liệu thật, đạt đủ 10 mục an toàn và cách ly (`docs/57` mục 8.1), và biết chắc bản sao lưu khôi phục được. Đây là điều kiện để chạy thử kiến trúc dữ liệu mới. **Không đổi gì trên phongdao.com** |
| Thời gian của bạn | Khoảng 2–4 giờ, chia 3 buổi |
| Ai làm gì | **Bạn:** FlashPanel, Cloudflare, DNS. **Agent:** lệnh trên trang thử qua SSH, chỉ khi bạn đồng ý cho từng lần; kiểm và ghi biên bản |

**Không ghi vào kho mã** tên miền trang thử, IP, tài khoản, mật khẩu, khoá hay email. Bằng chứng ghi ở backlog chỉ có kết quả đạt hay không và số đếm.

## Bước 0 — Bốn câu bạn xem trong FlashPanel (10 phút)

Gửi agent câu trả lời. Chụp màn hình cũng được, nhớ che email và IP.

1. **Sao Lưu:** bản đêm qua có không? Lưu ở đâu (trên VPS, Google Drive, nơi khác)? Có gồm cả tệp site và cơ sở dữ liệu không?
2. **Sao Lưu** có cho khôi phục vào **một site khác** (không phải phongdao.com) không?
3. FlashPanel có nút tạo bản sao site (Clone, Staging) không?
4. Gói đang dùng có cho tạo thêm một site và một cơ sở dữ liệu không? Ổ đĩa VPS còn trống bao nhiêu?

Agent chọn cách làm ít bước nhất, an toàn nhất, và báo lại trước khi làm.

> **Không bấm "Khôi phục" trên site phongdao.com.** Khôi phục nhầm vào site chính sẽ ghi đè dữ liệu thật.

## Bước 1 — Tạo site thử (bạn, FlashPanel, ~20 phút)

1. Tạo site mới bằng **một tên miền phụ riêng**:
   - tự đặt tên khó đoán;
   - không liên kết từ site chính;
   - gửi tên cho agent bằng tin nhắn, không ghi vào kho mã.
2. Chọn cùng phiên bản PHP như site chính.
3. **Cài Đặt → Update Owner:** đặt một tài khoản mới, khác tài khoản của site chính. FlashPanel sẽ tạo tài khoản Linux và pool PHP riêng, như đã làm cho site chính.
4. Cài WordPress cho site thử bằng FlashPanel, với **cơ sở dữ liệu mới, người dùng và mật khẩu mới**. Agent không cần biết mật khẩu này.
5. Bật SSL cho site thử.
6. **Không** thêm lịch tự cập nhật, không gắn kho mã.

## Bước 2 — Chặn người lạ (bạn, Cloudflare, ~20 phút)

1. **Cloudflare → DNS:** thêm bản ghi cho tên miền phụ, bật proxy (mây cam) như site chính.
2. **Cloudflare → Zero Trust → Access → Applications → Add an application (Self-hosted):**
   - chọn tên miền phụ;
   - thêm chính sách **Allow**, chỉ email của bạn.

   Gói miễn phí dùng được. Người lạ mở trang thử chỉ thấy màn đăng nhập của Cloudflare.
3. Nhắn agent "đã bật Access".

## Bước 3 — Agent chuẩn bị trang thử (khi bạn đồng ý, ~1 giờ)

Agent báo trước từng nhóm lệnh và chỉ chạy trên site thử. Agent cũng có thể đếm trên site chính để đối chiếu; mọi lệnh ở đó chỉ đọc.

**1. Khôi phục:**

- Lấy **bản sao lưu đêm trước** (để thử chính bản sao lưu), theo cách chọn ở bước 0.
- Bản sao cơ sở dữ liệu đi thẳng từ nơi lưu sang máy chủ: **không qua máy cá nhân, không vào kho mã**.
- Nhập vào cơ sở dữ liệu của site thử.
- Đổi đường dẫn sang tên miền thử, chỉ trên site thử.
- Chép thư mục ảnh tải lên từ bản sao lưu.
- Ghi giờ bắt đầu và giờ xong.

**2. Cấu hình riêng của site thử:**

- khoá bí mật (salt) mới;
- tắt lịch chạy của WordPress, không có cron hệ thống cho site thử;
- **chặn mọi yêu cầu ra ngoài** của WordPress (nguồn tự động, Wikidata);
- **chặn gửi thư**;
- đầu trang `X-Robots-Tag: noindex, nofollow`;
- `blog_public = 0`;
- bắt đăng nhập cho mọi trang, kể cả REST — lớp thứ hai, phòng khi có người vào thẳng máy chủ mà không qua Cloudflare.

**3. Dữ liệu cá nhân và tài khoản:**

- ẩn danh email và tên hiển thị của mọi người dùng, trừ tài khoản bạn. Không xoá người dùng, để giữ tác giả bài;
- xoá email, địa chỉ mạng, trình duyệt của người bình luận nếu có;
- bản gửi của bạn đọc chỉ giữ khi việc thử kiến trúc cần, nếu không thì xoá trên bản sao;
- đặt lại mật khẩu mọi tài khoản, thu hồi mật khẩu ứng dụng, xoá mọi phiên đăng nhập cũ.

**4. Khoá dịch vụ:** xoá hoặc thay bằng khoá thử: Turnstile, mọi khoá API, thông tin máy gửi thư trong tuỳ chọn. Không đặt khoá AI nào trên site thử.

## Bước 4 — Kiểm đủ 10 mục (agent; bạn xem biên bản)

| # | Mục | Agent kiểm | Đạt khi |
|---|---|---|---|
| 1 | Truy cập công khai | Mở trang, REST, sơ đồ trang khi không đăng nhập, cả qua Cloudflare lẫn vào thẳng máy chủ | 401 / 403 hoặc màn đăng nhập Cloudflare; không có nội dung; có `noindex`; `blog_public = 0` |
| 2 | Kết nối production | Tìm tên miền chính trong cấu hình và tuỳ chọn của site thử; cơ sở dữ liệu, thư mục ảnh, tài khoản Linux riêng | Tìm ra 0; không dùng chung thứ gì |
| 3 | Tác vụ tự động | Danh sách lịch chạy; thử một yêu cầu ra ngoài; nguồn tự động | Không có lịch chạy tự động; yêu cầu ra ngoài bị chặn; nguồn tự động tắt; không tự cập nhật |
| 4 | Email | Thử gửi một thư tới địa chỉ không có thật | Thư không đi |
| 5 | Tài khoản | Danh sách tài khoản đăng nhập được | Chỉ bạn (và agent khi được đồng ý); phiên cũ không dùng được |
| 6 | Dữ liệu cá nhân | Đếm email thật còn lại | 0, trừ tài khoản bạn |
| 7 | Khoá dịch vụ | Đọc lại các tuỳ chọn khoá | Trống hoặc khoá thử |
| 8 | Sao lưu và khôi phục | Đối chiếu với site chính tại giờ sao lưu | Biên bản ở dưới, số khớp hoặc chênh có giải thích |
| 9 | Số liệu đọc | Đếm bảng lượt đọc của site chính trước và sau khi thao tác trên site thử | Không đổi vì site thử; không dùng số của site thử để báo cáo |
| 10 | Thu dọn | Ghi ngày hết hạn | Ngày dựng + 30 ngày, hoặc khi xong việc thử |

Đủ 10 mục thì agent ghi bằng chứng vào backlog và hỏi bạn. **Chỉ khi bạn đồng ý, việc thử kiến trúc mới bắt đầu.**

## Biên bản khôi phục (agent điền)

| Mục | Site chính (giờ sao lưu) | Site thử | Khớp |
|---|---|---|---|
| Bản sao lưu dùng | ngày giờ | — | — |
| Thời gian khôi phục | — | từ … đến … (phút) | — |
| Tác phẩm theo trạng thái (đăng / nháp / chờ / thùng rác) | | | |
| Bài loại khác (tuyển tập, bài viết, mục từ…) | | | |
| Số phần của bài (dữ liệu phần) | | | |
| Số dòng lịch sử sửa | | | |
| Số tệp ảnh tải lên | | | |
| Kết luận | | | |

## Thu dọn

Khi xong việc thử, hoặc quá 30 ngày không dùng:

- **Bạn:** xoá site thử, cơ sở dữ liệu, tài khoản Linux trong FlashPanel; xoá ứng dụng Cloudflare Access và bản ghi DNS.
- **Agent:** xoá tệp sao lưu tạm trên máy chủ; ghi ngày thu dọn vào backlog.

## Lưu ý

- Site thử dùng chung VPS với site chính. Agent kiểm ổ đĩa trước khi khôi phục; việc đo hiệu năng chạy giờ vắng.
- Site thử chạy đúng phiên bản plugin, theme của site chính lúc sao lưu. Cập nhật tay khi cần, không tự lấy từ nhánh chính.
- Sau khi khôi phục, site thử có lời và chú thích của kho: chỉ dùng để thử, không chép ra ngoài.
