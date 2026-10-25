# 02 · Trạng thái hiện tại

| | |
|---|---|
| Phiên bản | 2.2 · 25/10/2026 |
| Mục đích | Trang tổng quan ngắn cho người sáng lập và AI agent |
| Nguồn máy đọc | `du-an/trang-thai.json` (`npm run trang-thai`) |

> Tệp này mô tả **thực tế hiện tại**, không phải kế hoạch tương lai. Sau mỗi task đã gộp, agent cập nhật tệp này cùng `du-an/trang-thai.json` và trạng thái trong `docs/22-backlog-trien-khai.md`. `npm run kiem-nhat-quan` báo lỗi khi các nguồn này lệch nhau.

## Giai đoạn hiện tại

**Đang chuyển sang WordPress** (D-056, `docs/24-wordpress.md`): WordPress, plugin, theme chạy trên VPS tại cadao.sdigi.net (T-075 xong 25/10/2026; sao lưu người sáng lập để sau), chưa mở cho Google; máy chủ tự cập nhật sau mỗi PR gộp (T-078). **Gói nâng cấp đã duyệt 25/10/2026** (D-059..D-065): 51 mục (gồm 4 ý chống trùng), kế hoạch 5 đợt ở `docs/25-nang-cap-giao-dien-noi-dung.md`, task T-080..T-111. Từ D-054 không còn chặn đăng vì lý do biên tập, nguồn không bắt buộc; từ D-055 chép chú thích có ghi công được phép.

**Mốc B · Thử nhỏ** — hướng v2 "Trung thực hơn hoàn hảo" (D-039). Mốc A xong 25/10/2026: WordPress chạy trên VPS, người sáng lập đăng bài từ điện thoại, nguồn tự động đổ bài Chờ duyệt mỗi ngày, trang bài có lời, dị bản, chú thích, trích dẫn, chống trùng; nội dung cũ đã chuyển, site Astro ngừng (T-076). Đang làm đợt 2 của `docs/25` mục 6 (đọc dễ hơn). Điều kiện xong Mốc B ở `docs/22` mục 2. Mốc tiếp theo: **C · Mở lặng lẽ**.

## Đã có

- Gói quyết định D-035..D-049 trong `docs/01`.
- Tài liệu v2: `AGENTS.md`, `docs/00`–`03`, `10`–`12`, `20`–`23`; tài liệu v1.2 không còn dùng ở `docs/luu-tru/`.
- Validator hai tầng (LỖI/NHẮC), quy tắc R01–R22; nhãn bằng chứng do máy tính (`scripts/lib/suy-ra.mjs`); 26 bộ fixture.
- Nguồn đầu mối `content/nguon/src-ca-dao-me.yaml`; 3 hồ sơ khung ở trạng thái `nhap` (chưa có nguồn gốc, không công khai được).
- **Site Astro cũ (T-035, T-040) — đã ngừng (T-076, 25/10/2026):** địa chỉ `https://ca-dao.truonganhquyen.workers.dev` chuyển hướng 301 sang cadao.sdigi.net; 2 bài công khai đã chuyển vào WordPress. Mã Astro, `content/`, công cụ kiểm dữ liệu giữ trong repo (`npm run kiem-tra` vẫn chạy).
- **Plugin WordPress (T-071):** loại bài Ca dao – Tục ngữ (lời không ghi nguồn; "Nguồn: …" chỉ dưới chú thích chép từ nguồn — D-057), trang **Nguồn tự động**, **Kho thu thập**, tự tạo bài **Chờ duyệt**, ô Chờ duyệt trên Bảng tin (`wordpress/`, `docs/24`). Bộ đọc Ca dao Mẹ đầy đủ (T-073): ca dao, thành ngữ–tục ngữ, đồng dao, vè, câu đố, kèm dị bản, chú thích, đáp án; bài chưa sửa tay tự được bổ sung.
- **Trích dẫn, chia sẻ, lượt đọc (T-042, T-081, 25/10/2026):** hộp Trích dẫn (thông dụng, APA 7; "Sao chép kèm nguồn" / "Chỉ sao chép lời"), nút Chia sẻ; lượt đọc tự đếm không cookie, không lưu IP, "Đọc nhiều tuần này" ở trang chủ, ô lượt đọc 7 ngày trên Bảng tin.
- **Đọc dễ hơn, phần 1 (T-082..T-084, 25/10/2026):** chạm vào từ có chú thích để xem nghĩa ngay trong lời; dị bản đánh số, tô chữ khác lời chính; máy nhận thể thơ (lục bát, song thất lục bát, câu 3–5 chữ, một dòng, tự do), lọc danh sách theo thể.
- **Chống trùng (T-109..T-111, 25/10/2026):** máy so cả lời của câu mới lấy từ nguồn và của bài viết tay với các bài, câu đã có: giống hệt, cùng thể loại thì tự gắn vào bài có sẵn (Kho thu thập → Đã gộp, "Tách ra" được); còn lại vào trang **Có thể trùng** chờ người sáng lập quyết; trình soạn có ô "Có thể trùng" (chỉ báo, không chặn đăng).
- **Theme WordPress (T-072):** theme con của Twenty Twenty-Five theo `docs/20`: màu, phông Literata / Be Vietnam Pro tự lưu trữ, sáng/tối theo máy, trang bài (lời lớn, lục bát thụt lề câu lục, Hiểu nhanh dưới lời, dị bản, chú thích); quét trợ năng 0 lỗi.
- **Trang tác phẩm (T-040):** đọc theo lớp, nhãn tự tính, nguồn gốc ở lớp 1, chú giải chạm-là-hiện (tắt JavaScript vẫn đọc được), sáng/tối, bản in, thanh dưới; quét trợ năng tự động. Chưa có bài `cong_khai` thật: bản xem trước có 3 trang mẫu giao diện dữ liệu giả (D-050).
- **CMS (T-037):** `/admin` — Sveltia, đăng nhập GitHub qua Worker `sveltia-cms-auth`, form tiếng Việt, "Thêm nhanh"; hướng dẫn `docs/huong-dan/bien-soan-cms.md`.
- **Schema một nguồn (T-036):** `scripts/lib/schema.mjs` dùng chung cho validator và bộ sinh Zod / kiểu từ vựng / cấu hình CMS (`npm run sinh:schema`); 7 content collections trong Astro.
- GitHub (24/10/2026): repo công khai `truonganhquyen/ca-dao` (`main` = hướng v2; PR #1–#3 đã gộp) và repo riêng tư `truonganhquyen/ca-dao-kho-tho` (mã thu thập T-038; dữ liệu đổ dần bằng GitHub Actions hằng ngày sau khi PR của repo đó gộp). Commit dùng email ẩn của GitHub.

## Đang chờ người sáng lập

- **Sao lưu FlashPanel** — để sau (người sáng lập, 25/10/2026): khi làm, chọn hằng ngày, gồm site và cơ sở dữ liệu cadao, lưu ra ngoài VPS.
- **Tách thành ngữ – tục ngữ** (Ca dao – Tục ngữ → Tách thành ngữ – tục ngữ): xem nhóm "Máy đã chắc", kiểm khoảng 50 câu xem máy đoán đúng không (báo agent nếu sai nhiều), rồi chọn hàng loạt và bấm "Đồng ý gợi ý"; nhóm "Nghiêng về", "Chưa rõ" chọn loại từng câu khi rảnh.
- **Quyết các cặp "Có thể trùng"** (menu Ca dao – Tục ngữ → Có thể trùng; Bảng tin báo số cặp): mỗi cặp chọn "Gộp: là cùng một bài" / "Gộp làm dị bản" / "Là bài khác" / "Để sau". Câu đang chờ thì máy chưa tạo thành bài.
- **Việc của người sáng lập trong gói nâng cấp** (`docs/25` mục 6): duyệt hàng loạt thể loại tách, xác nhận Wikidata, duyệt bộ chủ đề và vùng; cài Meilisearch và tạo khóa Turnstile khi tới đợt đó; nghe thử 30 bài giọng đọc; tìm *Tục ngữ phong dao* (T-107).
- **Thử CMS** (điều kiện xong T-037, PR #9): đăng nhập `/admin` trên điện thoại, tạo một bài thật tới Công khai — `docs/huong-dan/bien-soan-cms.md`. Đăng nhập GitHub đã tạo xong và đã kiểm (24/10/2026).
- **T-034** · Gửi thư cho Ca dao Mẹ — không gấp, nhưng phải xong trước khi mở công khai (T-053).
- **T-049** · Chốt tên miền (Q-005) — trước Mốc C.

## Công việc kỹ thuật tiếp theo

Chạy `npm run viec-tiep-theo` — máy suy ra từ backlog (`docs/22`), luôn đúng. Mục này **không liệt kê task bằng tay** để các PR không sửa chung một dòng (xem `docs/03` mục 7).

## Quyết định đang mở

Q-005 (tên miền), Q-006 (trình bày lục bát), Q-008 (tài trợ). Q-007 (đồng dao) đã chốt: làm ngay (D-059). Không câu nào chặn task kỹ thuật hiện tại.

## Lệnh điều hành

```bash
npm run trang-thai
npm run viec-tiep-theo
npm run kiem-tra
```
