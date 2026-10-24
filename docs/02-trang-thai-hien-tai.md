# 02 · Trạng thái hiện tại

| | |
|---|---|
| Phiên bản | 2.1 · 24/10/2026 |
| Mục đích | Trang tổng quan ngắn cho người sáng lập và AI agent |
| Nguồn máy đọc | `du-an/trang-thai.json` (`npm run trang-thai`) |

> Tệp này mô tả **thực tế hiện tại**, không phải kế hoạch tương lai. Sau mỗi task đã gộp, agent cập nhật tệp này cùng `du-an/trang-thai.json` và trạng thái trong `docs/22-backlog-trien-khai.md`. `npm run kiem-nhat-quan` báo lỗi khi các nguồn này lệch nhau.

## Giai đoạn hiện tại

**Đang chuyển sang WordPress** (D-056, `docs/24-wordpress.md`): plugin và theme làm trước tại máy (T-071..T-073), chờ hosting (T-074). Từ D-054 không còn chặn đăng vì lý do biên tập, nguồn không bắt buộc; từ D-055 chép chú thích có ghi công được phép.

**Mốc A · Chạy được** — hướng v2 "Trung thực hơn hoàn hảo" (D-039). Mốc Chuẩn bị đã xong (T-032, T-033 DONE, 24/10/2026); điều kiện xong của Mốc A ở `docs/22` mục 2. Mốc tiếp theo: **B · Thử nhỏ**.

## Đã có

- Gói quyết định D-035..D-049 trong `docs/01`.
- Tài liệu v2: `AGENTS.md`, `docs/00`–`03`, `10`–`12`, `20`–`23`; tài liệu v1.2 không còn dùng ở `docs/luu-tru/`.
- Validator hai tầng (LỖI/NHẮC), quy tắc R01–R22; nhãn bằng chứng do máy tính (`scripts/lib/suy-ra.mjs`); 26 bộ fixture.
- Nguồn đầu mối `content/nguon/src-ca-dao-me.yaml`; 3 hồ sơ khung ở trạng thái `nhap` (chưa có nguồn gốc, không công khai được).
- **Website khung (T-035):** Astro 7 + Tailwind 4, trang "Sắp ra mắt" và trang 404, sáng/tối theo hệ thống; `npm run kiem-tra` gồm `astro check`, build và kiểm liên kết; triển khai bằng Cloudflare Workers Static Assets (D-049): bản chính thức `https://ca-dao.truonganhquyen.workers.dev` (trang "Sắp ra mắt"), bản xem trước cho mỗi PR.
- **Trang tác phẩm (T-040):** đọc theo lớp, nhãn tự tính, nguồn gốc ở lớp 1, chú giải chạm-là-hiện (tắt JavaScript vẫn đọc được), sáng/tối, bản in, thanh dưới; quét trợ năng tự động. Chưa có bài `cong_khai` thật: bản xem trước có 3 trang mẫu giao diện dữ liệu giả (D-050).
- **CMS (T-037):** `/admin` — Sveltia, đăng nhập GitHub qua Worker `sveltia-cms-auth`, form tiếng Việt, "Thêm nhanh"; hướng dẫn `docs/huong-dan/bien-soan-cms.md`.
- **Schema một nguồn (T-036):** `scripts/lib/schema.mjs` dùng chung cho validator và bộ sinh Zod / kiểu từ vựng / cấu hình CMS (`npm run sinh:schema`); 7 content collections trong Astro.
- GitHub (24/10/2026): repo công khai `truonganhquyen/ca-dao` (`main` = hướng v2; PR #1–#3 đã gộp) và repo riêng tư `truonganhquyen/ca-dao-kho-tho` (mã thu thập T-038; dữ liệu đổ dần bằng GitHub Actions hằng ngày sau khi PR của repo đó gộp). Commit dùng email ẩn của GitHub.

## Đang chờ người sáng lập

- **T-074 · Chọn và mua hosting WordPress** — yêu cầu ở `docs/24` mục 8; cần để đưa WordPress lên mạng.
- **Duyệt danh sách từ gắn cờ thô tục** `tu-tho-tuc.txt` trong repo kho thô (bản khởi đầu do agent soạn; cờ chỉ là gợi ý, không xóa gì).
- **Thử CMS** (điều kiện xong T-037, PR #9): đăng nhập `/admin` trên điện thoại, tạo một bài thật tới Công khai — `docs/huong-dan/bien-soan-cms.md`. Đăng nhập GitHub đã tạo xong và đã kiểm (24/10/2026).
- **T-034** · Gửi thư cho Ca dao Mẹ — không gấp, nhưng phải xong trước khi mở công khai (T-053).
- **T-049** · Chốt tên miền (Q-005) — trước Mốc C.

## Công việc kỹ thuật tiếp theo

Chạy `npm run viec-tiep-theo` — máy suy ra từ backlog (`docs/22`), luôn đúng. Mục này **không liệt kê task bằng tay** để các PR không sửa chung một dòng (xem `docs/03` mục 7).

## Quyết định đang mở

Q-005 (tên miền), Q-006 (trình bày lục bát), Q-007 (đồng dao), Q-008 (tài trợ). Không câu nào chặn task kỹ thuật hiện tại.

## Lệnh điều hành

```bash
npm run trang-thai
npm run viec-tiep-theo
npm run kiem-tra
```
