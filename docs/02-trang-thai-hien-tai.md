# 02 · Trạng thái hiện tại

| | |
|---|---|
| Phiên bản | 2.1 · 24/10/2026 |
| Mục đích | Trang tổng quan ngắn cho người sáng lập và AI agent |
| Nguồn máy đọc | `du-an/trang-thai.json` (`npm run trang-thai`) |

> Tệp này mô tả **thực tế hiện tại**, không phải kế hoạch tương lai. Sau mỗi task đã gộp, agent cập nhật tệp này cùng `du-an/trang-thai.json` và trạng thái trong `docs/22-backlog-trien-khai.md`. `npm run kiem-nhat-quan` báo lỗi khi các nguồn này lệch nhau.

## Giai đoạn hiện tại

**Mốc A · Chạy được** — hướng v2 "Trung thực hơn hoàn hảo" (D-039). Mốc Chuẩn bị đã xong (T-032, T-033 DONE, 24/10/2026); điều kiện xong của Mốc A ở `docs/22` mục 2. Mốc tiếp theo: **B · Thử nhỏ**.

## Đã có

- Gói quyết định D-035..D-049 trong `docs/01`.
- Tài liệu v2: `AGENTS.md`, `docs/00`–`03`, `10`–`12`, `20`–`23`; tài liệu v1.2 không còn dùng ở `docs/luu-tru/`.
- Validator hai tầng (LỖI/NHẮC), quy tắc R01–R22; nhãn bằng chứng do máy tính (`scripts/lib/suy-ra.mjs`); 26 bộ fixture.
- Nguồn đầu mối `content/nguon/src-ca-dao-me.yaml`; 3 hồ sơ khung ở trạng thái `nhap` (chưa có nguồn gốc, không công khai được).
- **Website khung (T-035):** Astro 7 + Tailwind 4, trang "Sắp ra mắt" và trang 404, sáng/tối theo hệ thống; `npm run kiem-tra` gồm `astro check`, build và kiểm liên kết; triển khai bằng Cloudflare Workers Static Assets (D-049) — bản xem trước chạy sau khi người sáng lập kết nối Cloudflare.
- GitHub (24/10/2026): repo công khai `truonganhquyen/ca-dao` (`main` = hướng v2; PR #1–#3 đã gộp) và repo riêng tư `truonganhquyen/ca-dao-kho-tho` (trống, chờ T-038). Commit dùng email ẩn của GitHub.

## Đang chờ người sáng lập

- **Kết nối Cloudflare** (một lần, ~15 phút): theo `docs/huong-dan/ket-noi-cloudflare.md`, để PR của T-035 và mọi PR sau có link bản xem trước.
- **T-034** · Gửi thư cho Ca dao Mẹ — không gấp, nhưng phải xong trước khi mở công khai (T-053).
- **T-049** · Chốt tên miền (Q-005) — trước Mốc C.

## Công việc kỹ thuật tiếp theo

**T-036** (schema → Zod + cấu hình CMS), **T-038** (kho thô) và **T-040** (trang tác phẩm) sẵn sàng, chạy song song được.

## Quyết định đang mở

Q-005 (tên miền), Q-006 (trình bày lục bát), Q-007 (đồng dao), Q-008 (tài trợ). Không câu nào chặn task kỹ thuật hiện tại.

## Lệnh điều hành

```bash
npm run trang-thai
npm run viec-tiep-theo
npm run kiem-tra
```
