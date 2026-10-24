# 04 · Cổng chất lượng theo giai đoạn

| | |
|---|---|
| Phiên bản | 1.2 · 06/10/2026 |
| Mục đích | Biến điều kiện chuyển giai đoạn thành checklist rõ cho người và agent |
| Nguồn đầy đủ | `docs/00-tai-lieu-nen.md`, `docs/22-backlog-trien-khai.md`, `docs/24-ke-hoach-beta.md` |

> Tệp này không thay thế các tài liệu nguồn. Nếu có khác biệt, tài liệu nguồn và nhật ký quyết định có thẩm quyền cao hơn.

## GD0_EXIT · Cổng 1 — Chứng minh mô hình dữ liệu

Phải đạt **đúng các điều kiện Cổng 1 trong `docs/00-tai-lieu-nen.md`**:

- [ ] 10 hồ sơ thật của T-005 đi hết vòng và qua checklist ở trạng thái `cho_kiem_dinh`.
- [ ] Có số liệu thời gian cho đủ 5 phần của cả 10 hồ sơ.
- [ ] Điểm đau schema/authoring được ghi lại từ dữ liệu thật.
- [ ] T-006 hoàn tất: schema chỉnh theo bằng chứng thực tế và được đóng băng v1.
- [ ] Validator và fixture đạt sau thay đổi schema.

Để **bắt đầu T-010** sau khi Cổng 1 được chấp thuận, các dependency kỹ thuật riêng của T-010 (T-003, T-004, T-006, T-008) cũng phải `DONE`; đây là dependency của task, không phải định nghĩa lại Cổng 1.

**Human Gate:** người sáng lập xác nhận đóng băng schema v1 và cho phép chuyển sang GĐ0.5.

## GD0_5_EXIT · Cổng 1.5 — Lát cắt dọc

Phải đạt **tất cả**:

- [ ] T-010 hoàn tất với 3 hồ sơ thật.
- [ ] Luồng YAML → validator → Astro → trang tác phẩm → chú giải → nguồn → dị bản → tìm kiếm → trích dẫn → preview chạy trọn.
- [ ] Người mới có thể hoàn thành chuỗi tìm → đọc → hiểu → xem nguồn/dị bản → sao chép trích dẫn trên điện thoại mà không cần biết cấu trúc repo.
- [ ] Neo chú giải hỏng làm preview/build thất bại theo R06.
- [ ] Mọi vướng mắc giao nhau giữa schema/UI/search/citation/build đã thành task sửa hoặc đã xử lý.
- [ ] `npm run kiem-tra` đạt với bộ kiểm tra phù hợp phase.

**Human Gate:** người sáng lập chấp thuận lát cắt dọc và hướng UX trước khi mở GĐ1.

## GD1_PREVIEW · Preview kín

- [ ] P0-Core cần thiết cho luồng thử đã hoạt động.
- [ ] Có 15–20 hồ sơ đủ chất lượng.
- [ ] T-119 hoàn tất với nhóm người thử theo `docs/24-ke-hoach-beta.md`.
- [ ] Lỗi chặn nhiệm vụ thử đã được ghi thành task.

Không yêu cầu P0.5.

## GD1_BETA_EXIT · Cổng 2 — Beta khả dụng

- [ ] Có 25–30 hồ sơ đủ chất lượng.
- [ ] T-117 hoàn tất: WCAG 2.2 AA và ngân sách hiệu năng đạt cho phạm vi quy định.
- [ ] T-120 hoàn tất và mọi tiêu chí Cổng 2 trong `docs/24-ke-hoach-beta.md` đạt.
- [ ] Lỗi blocker/major từ preview đã xử lý hoặc có quyết định chấp nhận rõ.

**Human Gate:** người sáng lập xác nhận beta đủ tốt để mở rộng nội dung và làm P0.5.

## GD2_EXIT · Cổng 3 — Ra mắt công khai

- [ ] Khoảng 120 hồ sơ qua checklist chất lượng.
- [ ] P0.5 cần cho ra mắt đã hoàn tất.
- [ ] 0 lỗi validator/build.
- [ ] `npm run health` không còn nợ chặn ở hồ sơ đã xuất bản.
- [ ] Bộ trang công khai, giấy phép, quyền riêng tư và phương pháp đã được người sáng lập duyệt.
- [ ] Sao lưu đã được thử khôi phục ít nhất một lần.
- [ ] Các lỗi beta quan trọng đã xử lý.

**Human Gate:** quyết định ra mắt công khai.

## Nguyên tắc chuyển giai đoạn

Agent không được tự sửa `giai_doan` trong `du-an/trang-thai.json` sang phase tiếp theo nếu Human Gate của cổng chưa được người sáng lập chấp thuận.
