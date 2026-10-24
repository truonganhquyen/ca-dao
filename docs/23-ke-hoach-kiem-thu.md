# 23 · Kế hoạch kiểm thử

| | |
|---|---|
| Phiên bản | 2.0 · 23/10/2026 (kiểm hai tầng LỖI/NHẮC, tiêu chí theo mốc: D-043, D-044) |
| Trạng thái | **Ổn định cho Mốc A–C** |
| Đọc khi | Viết hoặc duyệt bất kỳ task nào; trước khi xong mỗi mốc |

Bốn lớp kiểm thử, xếp theo mức tự động: dữ liệu (tự động hoàn toàn) → giao diện (tự động + tay) → tìm kiếm (bộ truy vấn) → người dùng thật (5 người ở Mốc B).

**Cổng lệnh cho agent/CI:** `npm run kiem-tra` (D-034) = `check:data` + `test:fixtures` + `test:lien-ket` + `kiem-nhat-quan` + `kiem-sinh` (từ T-036) + `astro check` + `astro build` + `kiem-lien-ket` (từ T-035) + `kiem-trang` (dấu nháp lộ ra, từ cấm, ngân sách JS, nội dung trang mẫu) + `quet-trang` (axe WCAG 2.2 A/AA ở 360 px sáng/tối và máy tính, ô chú giải, tắt JavaScript) (từ T-040).

---

## 1. Dữ liệu

| Kiểm | Lệnh / cách làm | Khi chạy |
|---|---|---|
| Quy tắc R01–R22 (LỖI chặn, NHẮC không chặn) | `npm run check:data` | Mỗi lần lưu (CI), mỗi PR, mỗi build |
| Chính công cụ kiểm tra | `npm run test:fixtures` | Mỗi PR, mỗi build |
| Nhất quán backlog / trạng thái / câu hỏi mở | `npm run kiem-nhat-quan` | Mỗi PR |
| Kiểu dữ liệu | `npm run check:astro` (`astro check`, nạp content qua Zod sinh từ `scripts/lib/schema.mjs`) | Mỗi PR |
| Tệp sinh khớp nguồn (Zod, từ vựng, cấu hình CMS) | `npm run kiem-sinh` | Mỗi PR |
| Sức khỏe dữ liệu, nợ dữ liệu | `npm run health` / Bàn biên tập | Hằng tuần |
| Liên kết nội bộ | `npm run kiem-lien-ket` sau build; chính bộ kiểm: `npm run test:lien-ket` | Mỗi PR |

**Bộ fixture** (`tests/fixtures/`, 26 bộ):
- **Hợp lệ (phải qua):** `hop-le` (nguồn in đã xác minh → nhãn A); `hop-le-web-toi-thieu` (chỉ có nguồn web, chưa có nghĩa/chú giải/tình huống → qua, chỉ NHẮC R10); `hop-le-canh-bao-ngoai-tinh-huong` (bài có cảnh báo không thuộc tình huống → qua); `hop-le-nhap-ai-da-duyet`; `hop-le-trich-ngan` (trích nguyên văn ≤ 15 từ).
- **Lỗi (phải thất bại với đúng và chỉ mã kỳ vọng):** R01 (Unicode tổ hợp), R02 (trạng thái v1 đã ngừng), R03 (trùng slug), R04 (tham chiếu hỏng), R05 (bản tham chiếu không có), R06 (neo không tìm thấy; neo mơ hồ), R07 (nghĩa ngắn quá dài), R09 (nháp AI chưa duyệt; lời mang `NHAP_AI`), R11 (bài có cảnh báo trong tình huống), R12 (ghi âm sai dị bản), R13 (cờ thiếu căn cứ), R14 (tranh luận thiếu ghi chú), R16 (trường `xac_thuc` của v1), R17 (nguồn đã xác minh còn thiếu), R18 (tư liệu điền dã chưa phép), R19 (trùng lời), R20 (chép nguyên văn chú thích dài chưa phép), R21 (bài công khai không có nguồn gốc), R22 (nguồn web thiếu url).
- R15 (từ vựng) cần tệp từ vựng riêng nên kiểm bằng tay khi sửa `taxonomy/`.

**Quy tắc:** thêm hoặc sửa một quy tắc thì thêm thư mục fixture `loi-rxx-<mo-ta>/` gồm `content/`, `KY_VONG` (mã lỗi mong đợi) và `MO_TA.md`; quy tắc bị hạ xuống NHẮC thì thêm bộ `hop-le-*` có tệp `NHAC` liệt kê mã nhắc phải xuất hiện. Trình chạy đòi bộ lỗi báo **đúng và chỉ** mã kỳ vọng.

Fixture dùng **dữ liệu giả** rõ ràng ("Dòng thử nghiệm thứ nhất", `example.org`); không dùng lời ca dao thật.

## 2. Giao diện — kiểm tay theo trang

Mỗi trang, trên điện thoại 360 px và máy tính:

- [ ] Lời xuống dòng đúng; lục bát trình bày theo cấu hình.
- [ ] Dấu tiếng Việt đúng ở mọi cỡ chữ ("Ngưỡng", "Thuở", "Quyện", "ẫ ỡ ự").
- [ ] Không có từ cấm (`20` mục 10); không lộ chuỗi `CAN_BO_SUNG`/`NHAP_AI`.
- [ ] Nhãn bằng chứng đúng với dữ liệu (so với `npm run health`); khối Nguồn gốc luôn có ở lớp 1.
- [ ] Tắt JavaScript: lời, chú giải, nguồn gốc vẫn đọc được.
- [ ] Chế độ tối: mọi chữ đọc được, 4 nhãn phân biệt được.
- [ ] Bản in: chỉ lời, nghĩa, nguồn gốc, URL.
- [ ] Không popup, không tự phát âm thanh, không cookie (kiểm tab Storage của trình duyệt).

**Trang tác phẩm mẫu để kiểm:** bộ mẫu giao diện (D-050, `tests/fixtures/mau-giao-dien/`) có sẵn trên mọi bản xem trước: `/ca-dao/mau-nhan-d/`, `/ca-dao/mau-nhan-a/`, `/tuc-ngu/mau-tuc-ngu/`. Khi có dữ liệu thật: một bài nhãn A, một bài nhãn D lấy từ Ca dao Mẹ, một bài dài (> 12 dòng), một bài có dị bản, một bài có cảnh báo nội dung, một bài chưa có giá trị riêng (phải có `noindex`), một bài có nháp AI đã duyệt (phải có dòng "có hỗ trợ AI").

## 3. Trợ năng (WCAG 2.2 AA)

| Kiểm | Cách |
|---|---|
| Lỗi tự động | `npm run quet-trang` (axe-core, mọi trang, mọi <details> mở) trong `kiem-tra` từ T-040: 0 lỗi WCAG A/AA |
| Bàn phím | Đi qua toàn trang bằng Tab; mở/đóng chú giải bằng Enter/Esc; focus luôn nhìn thấy, **không bị thanh trên/thanh dưới cố định che**; focus quay về đúng chỗ |
| Cỡ chữ | Phóng to trình duyệt 200% và nút "Aa" mức lớn nhất không vỡ bố cục |
| Trình đọc màn hình | Đọc được nhãn bằng chứng bằng chữ; nút chú giải có tên; ô chú giải có nhãn |
| Tương phản | Theo bảng `20` mục 1.1; đổi màu thì đo lại |
| Vùng chạm | ≥ 44×44 px |
| Chuyển động | Tôn trọng `prefers-reduced-motion` |

## 4. Tìm kiếm

**Bộ truy vấn:** `data/truy-van-thu.csv` (30 truy vấn, mở rộng dần từ truy vấn không có kết quả thật). Người vận hành điền `dap_an_mong_doi`: một hoặc vài mã tác phẩm, hoặc `chua_co`.

| Kiểu truy vấn | Chỉ tiêu | Mốc |
|---|---|---|
| `khong_dau`, `tu_khoa`, `tu_khoa_chinh_xac`, `dau_kieu_cu` | 100% có đáp án trong 5 kết quả đầu (khi đáp án đã công khai) | A |
| `tinh_huong` | ≥ 80% đưa tới đúng trang tình huống hoặc bài trong tình huống đó | B |
| `tu_dien` | Trang mục từ xuất hiện trong kết quả | B |
| `chua_co` | Hiện trang "chưa tìm thấy", **không** hiện kết quả gượng | A |
| `ngu_nghia`, `tu_nhien` | recall@5 ≥ 70% mới được bật tìm ngữ nghĩa | Sau, từ 150 bài |

**recall@5** = số truy vấn có ≥ 1 đáp án đúng trong 5 kết quả đầu ÷ số truy vấn có đáp án.

## 5. Hiệu năng

Đo trên điện thoại tầm trung, 4G mô phỏng, trang tác phẩm dài nhất và trang chủ:

| Chỉ số | Mục tiêu |
|---|---|
| LCP | < 2,0 s (lời thơ < 1,5 s) |
| CLS | < 0,05 |
| Trang (không tính phông) | < 150 KB |
| JS ban đầu | < 50 KB |

## 6. Chia sẻ và ảnh xem trước

- [ ] Nút Chia sẻ mở bảng chia sẻ của hệ điều hành trên ít nhất một điện thoại Android và một iPhone; văn bản chia sẻ có dòng nguồn gốc.
- [ ] (Mốc C) Ảnh xem trước của 3 bài (ngắn, có dấu chồng, nhãn D) hiện đúng khi dán link vào Zalo và Facebook; có nhãn và tên miền.
- [ ] Bài có cờ hoặc cảnh báo nội dung dùng ảnh chung toàn site.

## 7. Tiêu chí theo mốc

Điều kiện xong của từng mốc nằm ở `22` mục 2; phần kiểm thử cụ thể:

**Mốc A — Chạy được:**
- [x] `npm run kiem-tra` gồm `astro check` + build; dữ liệu có LỖI làm build thất bại với thông báo đọc được (T-035).
- [ ] Bài thêm qua CMS từ điện thoại hiện trên bản xem trước.
- [ ] 5–10 bài hiện đủ lớp 1; tìm không dấu và trích dẫn chạy; mục 2 đạt cho trang tác phẩm.

**Mốc B — Thử nhỏ:**
- [ ] Mục 2, 3 đạt trên trang chủ, trang tác phẩm, trang tìm, trang tình huống.
- [ ] Mục 4 đạt với truy vấn có đáp án đã công khai.
- [ ] 5 người thử làm được các nhiệm vụ N1–N6 (`docs/luu-tru/24-ke-hoach-beta.md` mục 2); vướng mắc đã thành task.

**Mốc C — Mở lặng lẽ:**
- [ ] 100–150 bài công khai; `npm run check:data` 0 lỗi.
- [ ] Mục 5, 6 đạt; sitemap chỉ gồm trang được đánh chỉ mục.
- [ ] Trang Phương pháp và thông báo quyền riêng tư đã được người sáng lập duyệt; thư đã gửi Ca dao Mẹ.
