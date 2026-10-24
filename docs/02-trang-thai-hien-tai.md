# 02 · Trạng thái hiện tại

| | |
|---|---|
| Phiên bản | 2.0 · 23/10/2026 |
| Mục đích | Trang tổng quan ngắn cho người sáng lập và AI agent |
| Nguồn máy đọc | `du-an/trang-thai.json` (`npm run trang-thai`) |

> Tệp này mô tả **thực tế hiện tại**, không phải kế hoạch tương lai. Sau mỗi task đã gộp, agent cập nhật tệp này cùng `du-an/trang-thai.json` và trạng thái trong `docs/22-backlog-trien-khai.md`. `npm run kiem-nhat-quan` báo lỗi khi các nguồn này lệch nhau.

## Giai đoạn hiện tại

**Chuẩn bị** — hướng v2 "Trung thực hơn hoàn hảo" (D-039) đã chốt ngày 23/10/2026. Mốc tiếp theo: **A · Chạy được** (`docs/22` mục 2).

## Đã có

- Gói quyết định D-035..D-047 trong `docs/01`.
- Tài liệu v2: `AGENTS.md`, `docs/00`–`03`, `10`–`12`, `20`–`23`; tài liệu v1.2 không còn dùng ở `docs/luu-tru/`.
- Validator hai tầng (LỖI/NHẮC), quy tắc R01–R22; nhãn bằng chứng do máy tính (`scripts/lib/suy-ra.mjs`); 26 bộ fixture.
- Nguồn đầu mối `content/nguon/src-ca-dao-me.yaml`; 3 hồ sơ khung ở trạng thái `nhap` (chưa có nguồn gốc, không công khai được).
- GitHub (24/10/2026): repo công khai `truonganhquyen/ca-dao` (nhánh `main` = nguyên trạng v1.2; nhánh `task/T-033-huong-v2` = hướng v2, đang chờ review qua PR) và repo riêng tư `truonganhquyen/ca-dao-kho-tho` (trống, chờ T-038). Commit dùng email ẩn của GitHub.

## Đang chờ người sáng lập

- **T-032** (phần còn lại) · Tạo tài khoản Cloudflare; cho phép Codex truy cập `ca-dao` và `ca-dao-kho-tho`. Phần GitHub đã xong. Chặn mọi task kỹ thuật của Mốc A.
- **Review và gộp PR T-033** · Review độc lập theo `CLAUDE.md`, rồi người sáng lập gộp. Chưa gộp thì Codex vẫn đọc luật v1.2 trên `main`.
- **T-034** · Gửi thư cho Ca dao Mẹ — không gấp, nhưng phải xong trước khi mở công khai (T-053).
- **T-049** · Chốt tên miền (Q-005) — trước Mốc C.

## Công việc kỹ thuật tiếp theo

Sau T-032: **T-035** (Astro + CI + triển khai) và **T-038** (kho thô) chạy song song được.

## Quyết định đang mở

Q-005 (tên miền), Q-006 (trình bày lục bát), Q-007 (đồng dao), Q-008 (tài trợ). Không câu nào chặn task kỹ thuật hiện tại.

## Lệnh điều hành

```bash
npm run trang-thai
npm run viec-tiep-theo
npm run kiem-tra
```
