# 23 · Kế hoạch kiểm thử

| | |
|---|---|
| Phiên bản | 1.2 · 06/10/2026 |
| Trạng thái | **Ổn định cho GĐ0–GĐ1** |
| Đọc khi | Viết hoặc duyệt bất kỳ task nào; trước mỗi cổng |

Bốn lớp kiểm thử, xếp theo mức tự động: dữ liệu (tự động hoàn toàn) → giao diện (tự động + tay) → tìm kiếm (bộ truy vấn) → người dùng thật (preview và beta, `24`).

**Cổng lệnh cho agent/CI:** `npm run kiem-tra` (D-034). Ở GĐ0 lệnh này gồm validator + fixture; từ T-002/T-004 trở đi phải mở rộng để bao gồm các bước bắt buộc của phase như `astro check`, build, kiểm liên kết và E2E khi chúng xuất hiện.

---

## 1. Dữ liệu

| Kiểm | Lệnh / cách làm | Khi chạy |
|---|---|---|
| Quy tắc R01–R19 | `npm run check:data` | Mỗi lần lưu, mỗi PR, mỗi build |
| Chính công cụ kiểm tra | `npm run test:fixtures` | Mỗi PR, mỗi build |
| Kiểu dữ liệu | `astro check` | Mỗi PR |
| Sức khỏe dữ liệu, nợ dữ liệu | `npm run health` | Hằng tuần; trước mỗi cổng |
| Liên kết nội bộ | Kiểm `dist/` sau build | Mỗi PR |

**Bộ fixture hiện có** (`tests/fixtures/`): `hop-le` phải qua; 12 bộ lỗi phải thất bại với đúng mã: R01 (Unicode tổ hợp), R04 (tham chiếu hỏng), R05 (bản tham chiếu mức C), R06 (neo không tìm thấy; neo mơ hồ), R08 (mức A thiếu trang), R09 (còn NHAP_AI), R10 (không thuộc tình huống), R16 (trường lạ), R17 (nguồn chưa xác minh), R18 (tư liệu điền dã chưa được phép), R19 (trùng lời).

**Quy tắc:** thêm hoặc sửa một quy tắc trong `scripts/kiem-tra-du-lieu.mjs` thì thêm một thư mục fixture `loi-rxx-<mo-ta>/` gồm `content/`, `KY_VONG` (mã lỗi mong đợi) và `MO_TA.md`.

Fixture dùng **dữ liệu giả** rõ ràng ("Dòng thử nghiệm thứ nhất"); không dùng lời ca dao thật để tránh dữ liệu thử lẫn vào dữ liệu thật.

## 2. Giao diện — kiểm tay theo trang

Mỗi trang P0, trên điện thoại 360 px và máy tính:

- [ ] Lời xuống dòng đúng; lục bát trình bày theo cấu hình.
- [ ] Dấu tiếng Việt đúng ở mọi cỡ chữ ("Ngưỡng", "Thuở", "Quyện", "ẫ ỡ ự").
- [ ] Không có từ cấm (`20` mục 10).
- [ ] Tắt JavaScript: lời, chú giải, nguồn vẫn đọc được.
- [ ] Chế độ tối: mọi chữ đọc được, nhãn phân biệt được.
- [ ] Không popup, không tự phát âm thanh, không cookie (kiểm tab Storage của trình duyệt).

**Trang tác phẩm mẫu để kiểm:** một bài ngắn (2 dòng), một bài dài (> 12 dòng), một bài có 3+ dị bản và khối C, một bài có cảnh báo nội dung, một bài có cờ tranh luận.

## 3. Trợ năng (WCAG 2.2 AA)

| Kiểm | Cách |
|---|---|
| Lỗi tự động | Công cụ quét trợ năng (ví dụ axe hoặc Lighthouse): 0 lỗi mức nghiêm trọng |
| Bàn phím | Đi qua toàn trang bằng Tab; mở/đóng chú giải bằng Enter/Esc; focus luôn nhìn thấy, **không bị thanh cố định che**; focus quay về đúng chỗ |
| Cỡ chữ | Phóng to trình duyệt 200% không vỡ bố cục, không mất nội dung |
| Trình đọc màn hình | Đọc được nhãn bằng chứng bằng chữ; nút chú giải có tên; ô chú giải có nhãn |
| Tương phản | Theo bảng `20` mục 1.1; đổi màu thì đo lại |
| Vùng chạm | ≥ 44×44 px |
| Chuyển động | Tôn trọng `prefers-reduced-motion` |

## 4. Tìm kiếm

**Bộ truy vấn:** `data/truy-van-thu.csv` (30 truy vấn khởi đầu, mở rộng tới ≥ 50 ở GĐ2). Người vận hành điền `dap_an_mong_doi` (T-009): một hoặc vài mã tác phẩm, hoặc `chua_co` nếu kho chưa có bài phù hợp.

| Kiểu truy vấn | Chỉ tiêu | Giai đoạn |
|---|---|---|
| `khong_dau`, `tu_khoa`, `tu_khoa_chinh_xac`, `dau_kieu_cu` | 100% có đáp án trong 5 kết quả đầu (khi đáp án đã xuất bản) | GĐ1 |
| `tinh_huong` | ≥ 80% đưa tới đúng trang tình huống hoặc bài trong tình huống đó | GĐ1 |
| `tu_dien` | Trang mục từ xuất hiện trong kết quả | GĐ1 |
| `chua_co` | Hiện trang "chưa tìm thấy", **không** hiện kết quả gượng | GĐ1 |
| `ngu_nghia`, `tu_nhien` | recall@5 ≥ 70% mới được bật tìm ngữ nghĩa | Cổng, từ 150 hồ sơ |

**recall@5** = số truy vấn có ≥ 1 đáp án đúng trong 5 kết quả đầu ÷ số truy vấn có đáp án. Truy vấn `chua_co` tính riêng: tỉ lệ trả "chưa tìm thấy" phải ≥ 80%.

## 5. Hiệu năng

Đo trên điện thoại tầm trung, 4G mô phỏng, trang tác phẩm dài nhất và trang chủ:

| Chỉ số | Mục tiêu |
|---|---|
| LCP | < 2,0 s (lời thơ < 1,5 s) |
| CLS | < 0,05 |
| Trang (không tính phông) | < 150 KB |
| JS ban đầu | < 50 KB |
| Tạo thẻ chia sẻ | < 2 s |

## 6. Thẻ chia sẻ

- [ ] 2 mẫu × 2 khổ × 3 bài (ngắn, có dấu chồng, > 8 dòng) = 12 ảnh, kiểm từng ảnh.
- [ ] Dòng nguồn và tên miền có mặt trên mọi ảnh; không có cách tắt.
- [ ] Nút Tạo thẻ không xuất hiện ở bài C/D, cờ nghi gán nhầm/phỏng tác, bài có cảnh báo nội dung.
- [ ] Web Share hoạt động trên ít nhất một điện thoại Android và một iPhone.

## 7. Tiêu chí theo cổng

**Cổng 1.5 — Lát cắt dọc xong (T-010):**
- [ ] 3 hồ sơ thật hiển thị trên bản xem trước: lời, chú giải, nguồn, dị bản, trích dẫn, tìm không dấu.
- [ ] Người chưa biết dự án làm được chuỗi tìm → đọc → hiểu → chú giải → nguồn → dị bản → trích dẫn trên điện thoại.
- [ ] Build thất bại khi neo chú giải hỏng.

**"P0-Core đã xong" (điều kiện của Cổng 2, kèm `24` mục 6):**
- [ ] Mọi task lớp Core trong `22` mục 1 xong.
- [ ] 25–30 tác phẩm `da_xuat_ban`, mọi bài qua checklist `11` mục 11.
- [ ] Tìm kiếm đạt mục 4 với các truy vấn có đáp án đã xuất bản.
- [ ] Trích dẫn đúng 2 định dạng.
- [ ] Đạt mục 3 và 5 trên trang chủ, trang tác phẩm, trang tìm, trang tình huống.
- [ ] Build thất bại với mọi fixture lỗi.
- [ ] Trang `/phuong-phap` giải thích đầy đủ hệ thống bằng chứng.

**Cổng 3 — Ra mắt công khai (T-201):**
- [ ] ≥ 120 tác phẩm `da_xuat_ban`; 20 tình huống, mỗi tình huống ≥ 3 bài.
- [ ] Mọi task lớp 0.5 xong; thẻ chia sẻ đạt mục 6.
- [ ] `npm run check:data` 0 lỗi; `npm run health` không còn nợ ở hồ sơ đã xuất bản (dấu nháp, bản tham chiếu dưới A/B).
- [ ] Lỗi từ preview và beta đã xử lý.
- [ ] Trang Chính sách AI, Giấy phép, Quyền riêng tư đã duyệt; sao lưu đã thử khôi phục.
