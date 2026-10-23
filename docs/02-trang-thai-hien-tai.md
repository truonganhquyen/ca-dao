# 02 · Trạng thái hiện tại

| | |
|---|---|
| Phiên bản | 1.2 · 06/10/2026 |
| Mục đích | Trang tổng quan ngắn cho người sáng lập và AI agent |
| Nguồn máy đọc | `du-an/trang-thai.json` |

> Tệp này mô tả **thực tế hiện tại**, không phải kế hoạch tương lai. Sau mỗi task đã gộp, agent phải cập nhật tệp này cùng `du-an/trang-thai.json` và trạng thái trong `docs/22-backlog-trien-khai.md`.

## Giai đoạn hiện tại

**GĐ0 · Chứng minh mô hình dữ liệu**

Mục tiêu: đưa 10 hồ sơ thật đi hết vòng biên soạn, đo thời gian theo 5 phần, sửa các điểm đau được dữ liệu thật chứng minh và đóng băng schema v1 trước khi mở lát cắt dọc.

## Đã có

- Bộ tài liệu triển khai v1.2.
- Đặc tả dữ liệu, biên soạn, nguồn, giao diện, kỹ thuật, kiểm thử và vận hành.
- Validator R01–R19 và 13 bộ fixture.
- Cơ chế neo chú giải bằng `cum_tu`, máy resolve vị trí qua `phanGiaiNeo()`.
- `npm run health` để báo cáo sức khỏe và nợ dữ liệu.
- Backlog có phụ thuộc và tiêu chí chấp nhận.
- Quy trình tự chọn công việc cho Codex và hợp đồng review độc lập cho Claude Code.

## Công việc đang làm

Không có.

## Công việc sẵn sàng

- **T-001 · Chốt Q-001, Q-002, Q-003** — người sáng lập.
- **T-002 · Khởi tạo kho mã Astro + bộ tài liệu** — AI agent/dev; đây là công việc kỹ thuật tiếp theo.

## Công việc đang bị chặn đáng chú ý

- T-003, T-004, T-008 chờ T-002.
- T-005 chờ T-001.
- T-010 chờ T-003, T-004, T-006, T-008.

## Quyết định của người sáng lập đang mở

- Q-001 · Số giờ thực tế mỗi tuần.
- Q-002 · Có thoải mái làm việc với Git + YAML hay không.
- Q-003 · Giấy phép phần dự án tự viết.

Các Q trên **không chặn T-002**. Agent chỉ dừng khi task được chọn thực sự chạm một quyết định còn mở.

## Cổng tiếp theo

**GD0_EXIT / Cổng 1** — xem `docs/04-cong-chat-luong.md`.

Chưa đạt. Không được tự chuyển sang GĐ0.5.

## Lệnh điều hành

```bash
npm run trang-thai
npm run viec-tiep-theo
npm run kiem-tra
```
