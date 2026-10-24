# 24 · WordPress: thiết kế và chuyển đổi

| | |
|---|---|
| Phiên bản | 1.0 · 24/10/2026 (D-054, D-055, D-056) |
| Trạng thái | **Đang làm.** Site Astro (`docs/21`) vẫn chạy tới khi T-076 xong |
| Đọc khi | Làm T-071..T-076, hoặc bất kỳ việc gì trên WordPress |
| Mục tiêu | Người sáng lập đăng bài như WordPress quen thuộc; phần lớn nội dung tự lấy từ các nguồn cấu hình trong trang quản trị, vào **Chờ duyệt**, xem rồi bấm **Đăng** |

## 1. Luồng nội dung

```text
Nguồn tự động (cấu hình trong wp-admin)        Kho thu thập (bảng riêng)            Bài viết
───────────────────────────────────────        ─────────────────────────            ────────
Ca dao Mẹ · bật · 20 bài/ngày     ──lấy theo lịch──►  lời nguyên văn, dị bản,  ──mỗi ngày N bài──►  Chờ duyệt ──bấm Đăng──► trên trang
(nguồn khác: thêm bộ đọc)                         chú thích, địa chỉ trang
                                                                                    Nhập tay ──────────► Đăng ngay / Nháp
```

- **Nhập tay:** "Ca dao – Tục ngữ → Thêm mới": gõ tiêu đề, dán lời vào khối thơ, chọn thể loại → **Đăng**. Mọi ô khác tùy chọn.
- **Tự động:** bài vào trạng thái **Chờ duyệt** (Pending Review có sẵn của WordPress). Duyệt từng bài hoặc chọn nhiều bài → Sửa hàng loạt → Đã xuất bản.
- **Không chặn đăng vì lý do biên tập** (D-054). Không có "nhãn mức kiểm"; trang hiện một dòng **"Nguồn: …"** khi bài có nguồn.

## 2. Thành phần (mã trong repo này)

```text
wordpress/
├── plugins/ca-dao/          plugin của dự án — mọi chức năng riêng nằm ở đây
│   ├── ca-dao.php           khai báo plugin, nạp các phần
│   ├── inc/trich.php        trích lời tất định — khớp từng ký tự với bộ trích JavaScript của kho thô (100/100 bài mẫu)
│   ├── inc/loai-bai.php     loại bài, phân loại, hộp Nguồn, dòng "Nguồn: …"
│   ├── inc/kho.php          bảng kho thu thập, tạo bài Chờ duyệt từ kho
│   ├── inc/nguon-tu-dong.php trang cấu hình nguồn, lịch chạy
│   ├── inc/doc-cadao-me.php bộ đọc Ca dao Mẹ (trích tất định, không qua AI)
│   └── inc/trang-quan-tri.php trang "Kho thu thập"
├── themes/ca-dao/           theme con của Twenty Twenty-Five (T-072)
├── blueprint.json           dựng WordPress thử tại máy bằng Playground
└── tests/                   kiểm thử chạy trong Playground (kiem-thu.php, blueprint-kiem-thu.json)
```

Giữ ít plugin bên thứ ba (mỗi plugin là một nguy cơ bảo mật). Đang dùng: Two Factor (xác thực hai lớp, của WordPress.org). Dự kiến chỉ thêm: chống spam form, SEO (T-050). Sao lưu do FlashPanel làm.

## 3. Mô hình dữ liệu

| WordPress | Nội dung |
|---|---|
| Loại bài `cdtn` ("Ca dao – Tục ngữ") | Mỗi câu / bài một mục; đường dẫn `/ca-dao/<ten>/`, `/tuc-ngu/<ten>/`… theo thể loại đầu tiên (chưa chọn thì `ca-dao`); đường dẫn gốc `/bai/<ten>/` vẫn dẫn tới bài. Quy tắc đường dẫn là `add_rewrite_rule` đứng đầu — không dùng `add_rewrite_tag` trong slug (WordPress sinh quy tắc tệp đính kèm sai, nuốt mọi `/ca-dao/…`) |
| Tiêu đề | Thường là dòng đầu |
| Nội dung | **Khối Thơ** (core/verse) chứa lời; phần "Dị bản" (khối Thơ); phần "Chú thích" (danh sách, mỗi mục ghi "Theo …" khi chép từ nguồn) |
| Đoạn trích (excerpt) | "Hiểu nhanh" — một câu giải nghĩa (tùy chọn) |
| Phân loại `the_loai` | Ca dao · Tục ngữ · Thành ngữ · Đồng dao · Vè · Câu đố |
| Phân loại `chu_de`, `vung` | Chủ đề, vùng (tùy chọn) |
| Trường `_cadao_nguon_ten`, `_cadao_nguon_url`, `_cadao_ngay_truy_cap` | Dòng "Nguồn: …" |
| Trường `_cadao_ma_goc`, `_cadao_nguon_id` | Mã bài trên nguồn và nguồn tự động đã lấy — để không lấy trùng |

## 4. Nguồn tự động

Trang **Ca dao – Tục ngữ → Nguồn tự động**. Mỗi nguồn: tên · kiểu bộ đọc · địa chỉ · loại lấy · bật/tắt · **số bài Chờ duyệt mỗi ngày** · dòng ghi nguồn ("Lời tham khảo từ Ca dao Mẹ") · **chép chú thích** (bật/tắt, D-055) · bỏ qua bài gắn cờ (thô tục, trào phúng hiện đại) · nút **Lấy ngay**.

Kiểu bộ đọc: `cadao_me` (API WordPress + trang, đã kiểm ở T-038); sau đó `wordpress_api` (site WordPress bất kỳ). Site có cấu trúc riêng cần một bộ đọc mới — việc của agent; trang cấu hình chỉ chọn và chỉnh bộ đọc đã có.

**Lịch sự với máy chủ nguồn** (như `21` mục 16.2): tuần tự, ≥ 3 giây giữa hai yêu cầu, User-Agent ghi tên dự án, gặp 429/5xx thì dừng tới lượt sau, lưu điểm dừng. Lịch: WP-Cron mỗi 5 phút, mỗi lượt tối đa ~20 giây (hợp giới hạn thời gian chạy của hosting phổ thông); tạo bài Chờ duyệt mỗi ngày một lần.

## 5. Những điều vẫn giữ

- **Lời chép nguyên văn bằng máy, không qua AI**; AI không viết lời, dị bản, nguồn.
- **Nguồn ghi đúng sự thật**: bài tự động ghi đúng địa chỉ trang đã lấy và ngày lấy; không tự điền nguồn cho bài nhập tay.
- **Chú thích chép luôn kèm ghi công** và liên kết về trang gốc; chỉ khi công tắc của nguồn đang bật (D-055).
- **Không tải ảnh** của nguồn; không chép cách phân loại, bộ thẻ ra trang.
- **Google:** bài chưa có "Hiểu nhanh" (đoạn trích do dự án viết) mang `noindex` — lời và chú thích chép từ nguồn không tính là giá trị riêng (chính sách spam của Google về nội dung sao chép). Người đọc vẫn xem được.
- Không thông tin cá nhân trong nội dung.

## 6. Phát triển và kiểm thử tại máy

WordPress Playground (PHP 8.5 — như máy chủ — chạy bằng WebAssembly, cơ sở dữ liệu SQLite, có `intl`) — không cần cài PHP/MySQL. Cần Node ≥ 24.18.

```bash
npm run test:wordpress            # kiểm thử plugin (CI chạy ở job "wordpress"); thêm -- --mang để thử bộ đọc trên cadao.me thật
npx @wp-playground/cli@3.1.57 server --port=9411 --blueprint=wordpress/blueprint.json \
  --mount-dir wordpress/plugins/ca-dao /wordpress/wp-content/plugins/ca-dao
# mở http://127.0.0.1:9411/wp-login.php — tài khoản admin / password
```

Trên Windows (Git Bash) đặt `MSYS_NO_PATHCONV=1` để Git Bash không đổi `/wordpress/…` thành đường dẫn Windows. Bước `runPHP` của Playground không in ra màn hình: kiểm thử ghi kết quả vào `wordpress/tests/.ket-qua.txt`.

**Bản xem trước cho PR (D-035):** `npm run xem-truoc:wordpress` in một link playground.wordpress.net dựng WordPress ngay trên trình duyệt (máy tính hoặc điện thoại) và cài plugin từ nhánh hiện tại trên GitHub — nhánh phải đã đẩy lên. Mỗi lần mở là một site mới, trống; đóng tab là mất. API của Ca dao Mẹ cho phép playground.wordpress.net gọi tới (CORS), nên "Lấy ngay" có thể chạy ở đó; chạy chắc chắn là ở `npm run test:wordpress -- --mang`.

## 7. Chuyển đổi (T-076)

Bài, mục từ, nguồn trong `content/*.yaml` → WordPress bằng script nhập (qua REST API hoặc WP-CLI), giữ lời nguyên văn và dòng nguồn. Sau đó ngừng triển khai site Astro; repo vẫn giữ mã plugin, theme, tài liệu.

## 8. Máy chủ (T-074, T-075)

**cadao.sdigi.net** — VPS của người sáng lập (Ubuntu 24.04), quản lý bằng **FlashPanel** (gói miễn phí), web server Apache + PHP-FPM, MariaDB 10.11. Tên miền qua Cloudflare (proxy bật, chứng chỉ Let's Encrypt do FlashPanel cấp và gia hạn). VPS còn chạy **các site khác của người sáng lập**: chỉ đụng tới site cadao.

| Thành phần | Cách làm |
|---|---|
| Site, PHP 8.5, SSL, cài WordPress | Người sáng lập bấm trong FlashPanel |
| **Cách ly** | Gói miễn phí không có ô "Website Isolation" khi tạo site → *Cài Đặt → Update Owner* `cadao`: FlashPanel tạo tài khoản Linux riêng, pool PHP riêng, thêm vào nhóm `isolated` (ACL chặn vào `/home` của site khác) |
| Bổ sung cách ly | `cai-dat.sh` gỡ `cadao` khỏi nhóm `www-data` (nhóm đó gửi lệnh được tới pool PHP chung của các site khác), `wp-config.php` quyền 600 |
| Tiếng Việt, múi giờ, đường dẫn, plugin, cron | `wordpress/may-chu/cai-dat.sh` (chạy bằng root, chạy lại được) |
| Cập nhật plugin sau khi gộp PR | `sudo -u cadao bash /home/cadao/bin/cap-nhat-plugin.sh` — lấy `wordpress/plugins/ca-dao` từ nhánh `main`, thay thư mục một lần |
| Lịch chạy | `DISABLE_WP_CRON`; `/etc/cron.d/cadao-wordpress` chạy `wp cron event run --due-now` mỗi phút |
| WP-CLI | `sudo -u cadao /home/cadao/bin/wp <lệnh>` |
| Chặn công cụ tìm kiếm | `blog_public = 0` tới khi mở công khai (H5, T-053) — ô "Discourage search engines" của FlashPanel đã không có tác dụng, script đặt lại |
| Sao lưu | Người sáng lập: FlashPanel → *Sao Lưu*, hằng ngày, gồm site và cơ sở dữ liệu cadao, lưu ra ngoài VPS (Google Drive…) |

Agent vào VPS bằng khóa SSH riêng trên máy người sáng lập (không dùng mật khẩu). Không ghi IP, tài khoản hay thông tin các site khác vào repo.
