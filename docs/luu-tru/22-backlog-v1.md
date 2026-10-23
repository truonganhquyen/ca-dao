# 22 · Backlog triển khai

| | |
|---|---|
| Phiên bản | 1.2 · 06/10/2026 |
| Trạng thái | **Sống.** Cập nhật cột trạng thái ở mục 1 sau mỗi task/PR; enum: `READY`, `IN_PROGRESS`, `REVIEW`, `BLOCKED`, `DONE`, `DEFERRED` |
| Đọc khi | Nhận việc. Mỗi task ghi rõ cần đọc tài liệu nào |
| Ai làm | **N** = người sáng lập · **A** = AI agent hoặc dev · **N+A** = A làm, N duyệt và quyết định |
| Lớp | **Core** = P0-Core, chặn preview/beta · **0.5** = P0.5, chặn ra mắt công khai, không chặn beta · **P1/P2** = sau ra mắt |

**Cách nhận một task (cho cả người và AI agent):**
1. Nếu không được giao task cụ thể, đọc `docs/02-trang-thai-hien-tai.md`, `docs/03-quy-trinh-thuc-thi.md` và chạy `npm run viec-tiep-theo`.
2. Nhận task `READY` **hoặc task được `npm run viec-tiep-theo` suy ra là READY** vì mọi phụ thuộc đã `DONE` và Q-xxx trong cột "Chặn" đã được giải quyết.
3. Đọc các tài liệu trong "Đọc".
4. Làm trên một nhánh riêng `task/T-xxx-<mo-ta-ngan>`; mỗi task một PR.
5. Khi bắt đầu đổi trạng thái thành `IN_PROGRESS`. Khi implementation + acceptance + `npm run kiem-tra` đã đạt, đổi task thành `DONE` trong commit cuối của branch rồi mở PR; trạng thái review được GitHub PR thể hiện. `REVIEW` chỉ dùng khi cần lưu một trạng thái review kéo dài trong repo.
6. Tự kiểm theo "Tiêu chí chấp nhận" + Definition of Done (mục 6) bằng `npm run kiem-tra`.
7. Mô tả PR theo `.github/pull_request_template.md`.

User story gốc (US-xx) ở mục 5.

---

## 1. Bảng tổng

| Task | Tên | Ai | Lớp | Phụ thuộc | Chặn | Trạng thái |
|---|---|---|---|---|---|---|
| **GĐ0 · Chứng minh mô hình dữ liệu** |||||||
| T-001 | Chốt Q-001, Q-002, Q-003 | N | Core | — | — | READY |
| T-002 | Khởi tạo kho mã Astro + bộ tài liệu | A | Core | — | — | READY |
| T-003 | Schema Zod cho content collections | A | Core | T-002 | — | BLOCKED |
| T-004 | Kiểm tra dữ liệu trong build, CI, bảo vệ `main` | A | Core | T-002 | — | BLOCKED |
| T-005 | Biên soạn 10 hồ sơ thật, đo thời gian 5 phần | N | Core | T-001 | — | BLOCKED |
| T-006 | Chỉnh schema theo điểm đau thật, **đóng băng v1** | N+A | Core | T-005 | — | BLOCKED |
| T-007 | Thử phương án B (nếu Q-002 = không Git) | A | Core | T-001 | Q-002 | BLOCKED |
| T-008 | Token, phông, layout gốc, nguyên mẫu màn hình | A | Core | T-002 | — | BLOCKED |
| T-009 | Bộ 30 truy vấn thử có đáp án | N | Core | T-005 | — | BLOCKED |
| **GĐ0.5 · Lát cắt dọc** |||||||
| T-010 | Lát cắt dọc với 3 hồ sơ thật, tới bản xem trước | A (+N thử) | Core | T-003, T-004, T-006, T-008 | — | BLOCKED |
| **GĐ1 · Lõi sản phẩm + phản hồi sớm** |||||||
| T-101 | Trang tác phẩm đọc theo lớp, không JS vẫn đọc | A | Core | T-010 | — | BLOCKED |
| T-102 | Chú giải tương tác | A | Core | T-101 | — | BLOCKED |
| T-103 | Dị bản + khối Cộng đồng ghi nhận | A | Core | T-101 | — | BLOCKED |
| T-104 | Nhãn bằng chứng, cờ, cảnh báo nội dung | A | Core | T-101 | — | BLOCKED |
| T-105 | Trích dẫn 2 định dạng | A | Core | T-101 | — | BLOCKED |
| T-106 | Module dữ liệu suy ra (`src/lib/du-lieu.ts`) | A | Core | T-010 | — | BLOCKED |
| T-107 | Tìm kiếm: chỉ mục, chuẩn hóa tiếng Việt, giao diện | A | Core | T-106 | — | BLOCKED |
| T-108 | Trang tình huống + danh sách tình huống trên trang chủ (thứ tự cố định) | A | Core | T-106 | — | BLOCKED |
| T-109 | Duyệt theo chủ đề, thể loại | A | Core | T-106 | — | BLOCKED |
| T-110 | Trang từ điển | A | Core | T-106 | — | BLOCKED |
| T-113 | SEO kỹ thuật | A | Core | T-101 | — | BLOCKED |
| T-114 | Đo lường không cookie, 7 sự kiện P0 | A | Core | T-101 | — | BLOCKED |
| T-115 | Form báo lỗi/gửi dị bản + trang Đóng góp | N+A | Core | T-101 | Q-003 | BLOCKED |
| T-116 | Trang Phương pháp, Nguồn, Về dự án | N+A | Core | T-104 | — | BLOCKED |
| T-117 | Kiểm thử trợ năng (WCAG 2.2 AA) + hiệu năng | A | Core | T-101..T-110, T-113..T-116 | — | BLOCKED |
| T-118 | Biên soạn: 20 → 30 → 60 hồ sơ, 20 tình huống | N | Core | T-006 | — | BLOCKED |
| T-119 | **Preview kín** ở 15–20 hồ sơ | N | Core | T-101..T-110, T-116 | — | BLOCKED |
| T-120 | **Beta khả dụng** ở 25–30 hồ sơ (Cổng 2) | N | Core | T-117, T-119 | — | BLOCKED |
| T-111 | Thẻ chia sẻ (2 mẫu, 2 khổ) | A | 0.5 | T-101 | Q-005 | BLOCKED |
| T-112 | Ảnh OG riêng từng bài lúc build | A | 0.5 | T-101 | Q-005 | BLOCKED |
| T-121 | Thứ tự tình huống theo dịp (lịch) | A | 0.5 | T-108 | — | BLOCKED |
| T-122 | Ngẫu nhiên, câu hôm nay, A–Z | A | 0.5 | T-106 | — | BLOCKED |
| T-123 | Nút đổi sáng/tối | A | 0.5 | T-008 | — | BLOCKED |
| T-124 | Trình phát âm thanh | A | 0.5 | T-101 | — | BLOCKED |
| **GĐ2 · Ra mắt công khai** |||||||
| T-201 | Trang công khai đầy đủ + ra mắt (Cổng 3) | N+A | 0.5 | T-120, T-111, T-112, T-121..T-124 | Q-003, Q-005 | BLOCKED |
| T-202 | Script so khớp đóng góp | A | P1 | — | — | BLOCKED |
| T-203 | So sánh dị bản | A | P1 | T-103 | — | BLOCKED |
| T-204 | Trang mô-típ, SGK, vùng | A | P1 | T-106 | — | BLOCKED |
| T-205 | Trang "Những người giữ lửa" | A | P1 | T-106 | — | BLOCKED |
| T-206 | Bản phát hành dữ liệu + Zenodo | A | P1 | T-201 | Q-003 | BLOCKED |
| T-207 | Script đánh giá tìm ngữ nghĩa (offline) | A | P1 | T-009 | — | BLOCKED |
| T-208 | Mời hội đồng cố vấn | N | P1 | T-201 | — | BLOCKED |
| T-209 | Thử nghiệm ghi âm dịp Tết | N | P1 | — | — | BLOCKED |
| **GĐ3 · Cộng đồng, chiều sâu** |||||||
| T-301 | Tìm ngữ nghĩa (Worker) | A | Cổng | T-207 qua cổng | — | BLOCKED |
| T-302 | Chiến dịch "Giọng bà, giọng mẹ" (Vu Lan) | N | P2 | T-209 | — | BLOCKED |
| T-303 | Trang "Thường bị nhầm là ca dao" | N+A | P2 | — | — | BLOCKED |
| T-304 | Thử nghiệm với một khoa Ngữ văn | N | P2 | T-208 | — | BLOCKED |

Không có task nào cho Admin workbench, đường ống nạp tư liệu hay cơ sở dữ liệu: chúng chỉ được tạo khi đạt điều kiện ở `21` mục 14.

## 2. GĐ0 · Chứng minh mô hình dữ liệu

### T-001 · Chốt Q-001, Q-002, Q-003 — N
**Đọc:** `01` mục 1. **Đầu ra:** ba dòng D-xxx mới trong `01`, xóa ba dòng Q tương ứng khỏi bảng câu hỏi mở.
**Chấp nhận:** mỗi quyết định có lý do một câu.

### T-002 · Khởi tạo kho mã — A
**Đọc:** `21` mục 1–3, `AGENTS.md`.
**Việc:** tạo dự án Astro (phiên bản ổn định hiện hành, ghi vào `01`), TypeScript, xuất tĩnh; chép toàn bộ bộ tài liệu này vào gốc kho; gộp `scripts` của `package.json`; thêm `.gitignore`, `.editorconfig` (UTF-8, LF, không khoảng trắng cuối dòng); kết nối Cloudflare Pages.
**Chấp nhận:**
- [ ] `npm run check:data` và `npm run test:fixtures` chạy được trong kho mới.
- [ ] Trang "Sắp ra mắt" triển khai được.
- [ ] Không thêm thư viện nào ngoài Astro và `yaml`.

### T-003 · Schema Zod — A
**Đọc:** `10` mục 4, `21` mục 4.
**Việc:** khai báo 7 collection với schema Zod theo `10` mục 4 (kiểu, enum lấy từ `taxonomy/tu-vung-kiem-soat.yaml`, trường lạ bị từ chối). Quy tắc nghiệp vụ R01–R19 **không** viết lại trong Zod.
**Chấp nhận:**
- [ ] `astro check` qua với 3 hồ sơ khung hiện có.
- [ ] Enum đọc từ tệp từ vựng, không chép tay.
- [ ] Đổi một giá trị enum trong tệp từ vựng thì kiểu TypeScript đổi theo sau khi build lại.

### T-004 · Kiểm tra dữ liệu trong build, CI, bảo vệ `main` — A
**Đọc:** `10` mục 9, `21` mục 3.
**Chấp nhận:**
- [ ] Lệnh build Cloudflare: `npm run check:data && npm run test:fixtures && astro build`.
- [ ] Một PR cố tình đưa vào lỗi R06 thì build xem trước thất bại và thông báo lỗi đọc được.
- [ ] Có workflow CI (ví dụ GitHub Actions) chạy trên mọi PR theo `21` mục 3: `check:data` → `test:fixtures` → `astro check` → build → kiểm liên kết nội bộ.
- [ ] Nhánh `main` được bảo vệ: chỉ gộp qua PR đã qua CI.

### T-005 · 10 hồ sơ thật, đo thời gian — N
**Đọc:** `11`, `12`, `13`.
**Việc:** chọn 10 bài trải đều: ≥ 3 tục ngữ, ≥ 2 bài có dị bản, ≥ 1 bài cần chú giải điển tích, ≥ 1 bài có bối cảnh diễn xướng rõ; đối chiếu bản in, đưa tới `cho_kiem_dinh`; bấm giờ **theo 5 phần** (`11` mục 1).
**Chấp nhận:** 10 hồ sơ qua `check:data` ở `cho_kiem_dinh`; 5 cột thời gian có số liệu cho cả 10 bài; ghi chú những trường khó điền, thừa, hoặc thiếu.

### T-006 · Chỉnh schema, đóng băng v1 — N+A
**Đọc:** `10` mục 12. **Việc:** từ ghi chú của T-005, đề xuất thay đổi — **chỉ những gì 10 hồ sơ thật cần**; N quyết định; A cập nhật tài liệu + script + mẫu + fixture cùng một PR.
**Chấp nhận:**
- [ ] `test:fixtures` đạt; ghi D-xxx (kèm mã hồ sơ thật gây ra thay đổi); "Phiên bản schema" = 2.0 và ghi **đóng băng**.
- [ ] Ghi vào `01` một dòng tổng kết thời gian: trung bình từng phần và tỉ lệ (tìm nguồn + xác minh) / tổng; chọn kịch bản lộ trình (`00` mục 7).
- [ ] Nếu tìm nguồn + xác minh > 50% tổng thời gian: ghi rõ ưu tiên giải bài toán nguồn trước mọi tối ưu công cụ nhập liệu.
- [ ] **Cổng 1** đạt.

### T-007 · Phương án B — A (chỉ khi Q-002 = không dùng Git)
**Đọc:** `21` mục 12. **Việc:** dựng B1 hoặc B2 với 10 hồ sơ mẫu; viết hướng dẫn 1 trang.
**Chấp nhận:** người sáng lập tự tạo một hồ sơ mới từ đầu tới `cho_kiem_dinh` mà không mở tệp YAML.

### T-008 · Token, phông, layout, nguyên mẫu — A
**Đọc:** `20` mục 1, 2, 11, 14. **Chấp nhận:**
- [ ] Biến màu sáng/tối đúng bảng `20` mục 1.1; nút đổi chế độ.
- [ ] Phông tự lưu trữ, WOFF2, chỉ Latin + tiếng Việt; trang kiểm dấu hiển thị "Ngưỡng Thuở Quyện ẫ ỡ ự" đúng ở 14 px và 32 px.
- [ ] Layout chung có thanh trên, menu, chân trang theo `20` mục 2.
- [ ] Tailwind CSS 4 cấu hình từ token; không thêm thư viện thành phần (D-030).
- [ ] Nguyên mẫu tĩnh (dữ liệu giả) cho 5 màn hình tham chiếu ở `20` mục 14, để N duyệt hướng thẩm mỹ trước T-010.

### T-009 · Bộ truy vấn thử — N
**Đọc:** `23` mục 4. **Việc:** điền cột `dap_an_mong_doi` cho 30 truy vấn trong `data/truy-van-thu.csv` (mã tác phẩm hoặc "chua_co"); bổ sung tới ≥ 50 trong GĐ2.

## 2b. GĐ0.5 · Lát cắt dọc

### T-010 · Lát cắt dọc — A (+N thử)
**Đọc:** `20` mục 4–6, 8; `21` mục 3–5; `23` mục 7.
**Việc:** chỉ với **3 hồ sơ thật** (trong 10 hồ sơ của T-005), làm trọn một đường đi: YAML thật → kiểm tra → Astro → trang tác phẩm (lớp 1 đầy đủ) → chú giải (vị trí từ `phanGiaiNeo()`) → nguồn → dị bản → tìm kiếm không dấu → trích dẫn → bản xem trước trên Cloudflare. Chưa cần đẹp hoàn chỉnh; cần đúng và chạy trên điện thoại.
**Chấp nhận:**
- [ ] Một người chưa biết cấu trúc dự án, trên điện thoại, làm được chuỗi: tìm một câu → đọc → hiểu nghĩa ngắn → mở chú giải → xem nguồn → xem dị bản → sao chép trích dẫn, không cần trợ giúp. (N mời 1–2 người thử.)
- [ ] Sửa lời một hồ sơ làm neo hỏng thì build bản xem trước thất bại với R06.
- [ ] Ghi lại mọi chỗ "vướng giữa các phần" (schema ↔ giao diện ↔ tìm kiếm ↔ trích dẫn ↔ build) thành task sửa trước khi mở GĐ1.
- [ ] **Cổng 1.5** đạt.

## 3. GĐ1 · Lõi sản phẩm + phản hồi sớm

Các task Core mở rộng từ mã của lát cắt dọc, không dựng lại từ đầu.

### T-101 · Trang tác phẩm — A
**Đọc:** `20` mục 4, 14; `10` mục 4–5, 10. US-01, US-02.
**Chấp nhận:**
- [ ] Đọc theo lớp đúng `20` mục 4.1 (lớp 1 mở sẵn, lớp 2 thu gọn bằng `<details>`); khối trống thì ẩn; đạt FR-1.8.
- [ ] Chỉ tác phẩm `da_xuat_ban` có trang; dị bản D không xuất hiện ở bất cứ đâu.
- [ ] Bản đại diện được gọi là "Bản tham chiếu"; không có chữ nào trong danh sách từ cấm (`20` mục 10).
- [ ] Tắt JavaScript: đọc được lời, chú giải (cuối khối lời), nguồn.
- [ ] URL theo thể loại: `/ca-dao/{slug}`, `/tuc-ngu/{slug}`, `/thanh-ngu/{slug}`.

### T-102 · Chú giải tương tác — A
**Đọc:** `20` mục 4.3 (FR-1.2–1.4), mục 5. US-02.
**Chấp nhận:** mở bằng chạm, chuột, Enter/Space; đóng bằng Esc/chạm ngoài; trả focus; điện thoại dạng bảng trượt không che dòng đang đọc; ghi sự kiện `chu_giai_mo`.

### T-103 · Dị bản — A
**Chấp nhận:** khối chính chỉ A/B, khối "Cộng đồng ghi nhận" chỉ C kèm câu cảnh báo; mỗi dị bản hiện vùng, địa danh, nguồn, nhãn. US-03.

### T-104 · Nhãn, cờ, cảnh báo — A
**Chấp nhận:** nhãn chữ + biểu tượng + màu; chạm vào hiện câu giải thích (`20` mục 10) và liên kết `/phuong-phap`; cờ nghi gán nhầm hiện nhãn xám + căn cứ; tranh luận hiện dòng cảnh báo; `canh_bao_noi_dung` hiện ngay dưới lời.

### T-105 · Trích dẫn — A
**Chấp nhận:** 2 định dạng `20` mục 6; ngày truy cập tự điền; chỉ trích dẫn dị bản A/B; sự kiện `trich_dan_sao_chep`. US-04.

### T-106 · Dữ liệu suy ra — A
**Đọc:** `10` mục 10. **Chấp nhận:** một module duy nhất tính: mức tác phẩm, tình huống của tác phẩm, bài theo mô-típ, bài theo mục từ, tình huống đủ điều kiện tạo trang (≥ 3 bài đã xuất bản); có kiểm thử đơn vị trên dữ liệu fixture.

### T-107 · Tìm kiếm — A
**Đọc:** `21` mục 5, `20` mục 8, `23` mục 4. US-08, US-10.
**Chấp nhận:**
- [ ] Chỉ mục sinh lúc build, tải trễ khi chạm ô tìm.
- [ ] `boDau` và `chuanHoaCoDau` có kiểm thử đơn vị với các ca trong `21` mục 5.
- [ ] Mọi truy vấn kiểu `khong_dau`, `tu_khoa`, `dau_kieu_cu` trong `data/truy-van-thu.csv` có đáp án trong 5 kết quả đầu (khi đáp án đã xuất bản).
- [ ] Trang không kết quả đúng `20` mục 8; ghi `tim_khong_ket_qua`.

### T-108 · Tình huống — A
**Chấp nhận:** trang chỉ tạo khi ≥ 3 bài đã xuất bản; trang chủ hiện thẻ tình huống theo `mac_dinh` trong `config/lich-tinh-huong.yaml`; mỗi bài hiện lời rút gọn ≤ 4 dòng + "Hợp khi…". US-08.

### T-109 · Chủ đề, thể loại — A
**Chấp nhận:** trang chủ đề, thể loại theo `20` mục 9; trang < 3 mục có `noindex`.

### T-110 · Từ điển — A
**Chấp nhận:** `/tu-dien/` liệt kê mục từ đang được dùng; trang mục từ hiện mọi bài có từ này.

### T-111 · Thẻ chia sẻ — A — P0.5 — **chặn bởi Q-005**
**Đọc:** `20` mục 7. US-09.
**Chấp nhận:**
- [ ] 2 mẫu × 2 khổ; dòng nguồn và tên miền không tắt được.
- [ ] Không có nút Tạo thẻ cho C/D, cờ nghi gán nhầm/phỏng tác, bài có cảnh báo nội dung.
- [ ] Dấu đúng ở cả 4 tổ hợp với các chữ "Ngưỡng", "Thuở", "Quyện".
- [ ] Ảnh tạo < 2 giây trên điện thoại tầm trung; Web Share khi có, tải về khi không.

### T-112 · Ảnh OG riêng — A — P0.5 — **chặn bởi Q-005**
**Chấp nhận:** mỗi tác phẩm đã xuất bản có ảnh 1200×630 sinh lúc build, dấu đúng. (Trước đó, T-113 dùng một ảnh OG chung cho toàn site.)

### T-113 · SEO kỹ thuật — A
**Đọc:** `21` mục 8. **Chấp nhận:** đủ bảng `21` mục 8; JSON-LD qua công cụ kiểm tra dữ liệu có cấu trúc; sitemap chỉ trang đã xuất bản; 301 từ `redirects.yaml`; một ảnh OG chung cho toàn site.

### T-114 · Đo lường — A
**Đọc:** `21` mục 7. **Chấp nhận:** không cookie; **đúng 7 sự kiện P0**, không thêm; hướng dẫn đọc North Star theo tuần trên công cụ đo lường.

### T-115 · Form và trang Đóng góp — N+A
**Đọc:** `40` mục 2–3. US-13.
**Chấp nhận:** form theo `40` mục 2 (có phần người kể / người gửi); nút "Ở quê bạn câu này nói khác?" và nút báo lỗi mở form có mã tác phẩm và đường dẫn trang điền sẵn; trang cảm ơn giải thích bước tiếp theo; một người ghi hộ (ví dụ cháu ghi lời bà) điền xong trên điện thoại trong 5 phút.

### T-116 · Trang Phương pháp, Nguồn, Về dự án — N+A
**Đọc:** `50` mục 1, 5. **Chấp nhận:** nội dung do N duyệt; `/nguon` sinh tự động từ `content/nguon/` (chỉ nguồn đang được dùng). Các trang Chính sách AI, Giấy phép, Quyền riêng tư làm ở T-201.

### T-117 · Trợ năng và hiệu năng — A
**Đọc:** `23` mục 3, 5. **Chấp nhận:** mọi mục ở `23` mục 3 và 5 đạt trên trang chủ, một trang tác phẩm dài, trang tìm, trang tình huống. US-16.

### T-118 · Biên soạn 20 → 30 → 60 hồ sơ — N
**Chấp nhận:** mốc 15–20 hồ sơ trước T-119; 25–30 trước T-120; 60 hồ sơ `da_xuat_ban` và 20 tình huống (mỗi tình huống ≥ 3 bài) trước GĐ2; danh sách ưu tiên cập nhật; `npm run health` không còn nợ ở hồ sơ đã xuất bản.

### T-119 · Preview kín — N
**Đọc:** `24` mục 1–2. **Chấp nhận:** 5–7 người (2–3 giáo viên, 2–3 người trẻ, 1 người am hiểu văn hóa dân gian) đã thử; ghi nhận theo `24` mục 4; lỗi chặn nhiệm vụ đã thành task.

### T-120 · Beta khả dụng — N
**Đọc:** `24`. **Chấp nhận:** đủ tiêu chí **Cổng 2** (`24` mục 6).

### T-121 – T-124 · P0.5 — A
Làm sau Cổng 2, trước T-201. Mỗi task: đúng đặc tả ở `20` (mục 3, 9, 1.1, 4.3 FR-1.6), thêm sự kiện đo lường tương ứng ở `21` mục 7 nếu có, không làm tăng JS ban đầu của trang tác phẩm.

## 4. GĐ2–GĐ3 (tóm tắt; viết chi tiết khi tới giai đoạn)

| Task | Đọc | Tiêu chí chính |
|---|---|---|
| T-201 | `50`, `30`, `00` mục 7 | Mọi task P0.5 xong; trang công khai do N duyệt; giấy phép theo Q-003; sao lưu đã thử khôi phục một lần; **Cổng 3** đạt |
| T-202 | `40` mục 4 | Đọc bảng tính xuất CSV, ghi cột `ket_qua_so_khop`; ngưỡng cấu hình được; không bao giờ tự sửa `content/` |
| T-203 | `20` mục 13 | Đạt các tiêu chí ở `20` mục 13 |
| T-204 | `20` mục 2 | Trang < 3 mục `noindex` |
| T-205 | `40` mục 8 | Chỉ tên hiển thị; tôn trọng `kieu_hien_thi` |
| T-206 | `21` mục 10 | JSON gộp, không có D và dữ liệu riêng tư; README; DOI |
| T-207 | `23` mục 4, `21` mục 9 | Báo cáo recall@5 trên ≥ 50 truy vấn cho ≥ 2 mô hình ứng viên |
| T-301 | `21` mục 9 | Chỉ bật khi recall@5 ≥ 70%; quay về từ khóa khi lỗi/chậm; không sinh văn bản |
| T-302 | `41` mục 5 | Tài liệu chiến dịch; mọi bản ghi có hồ sơ đồng ý |

## 5. User story

| ID | Vai trò | Nhu cầu | Mục đích | Ưu tiên | Task |
|---|---|---|---|---|---|
| US-01 | Giáo viên | Thấy Bản tham chiếu và nguồn sách ngay đầu trang | Đưa vào giáo án, đề thi | Core | T-101, T-104 |
| US-02 | Giáo viên, học sinh | Chạm vào từ khó để xem nghĩa tại chỗ | Hiểu mà không rời trang | Core | T-102 |
| US-03 | Giáo viên | Xem các dị bản và chỗ khác nhau | Giải thích vì sao có nhiều bản | Core / P1 | T-103, T-203 |
| US-04 | Giáo viên, sinh viên | Sao chép dòng trích dẫn chuẩn | Ghi nguồn đúng | Core | T-105 |
| US-05 | Giáo viên | Xem các cách hiểu khác nhau, mỗi cách có nguồn | Dạy theo hướng mở | Core | T-101 |
| US-06 | Giáo viên | Duyệt bài theo bộ sách, lớp | Soạn bài nhanh | P1 | T-204 |
| US-07 | Giáo viên, sinh viên | Xem các bài cùng mô-típ | So sánh, mở rộng | P1 | T-204 |
| US-08 | Người trẻ | Tìm câu theo tình huống, cảm xúc | Tìm câu nói hộ lòng mình | Core | T-107, T-108 |
| US-09 | Người trẻ | Tạo ảnh đẹp có nguồn để chia sẻ | Đăng mạng xã hội | 0.5 | T-111 |
| US-10 | Mọi người | Gõ không dấu vẫn tìm được | Tìm nhanh trên điện thoại | Core | T-107 |
| US-11 | Phụ huynh, người trẻ | Nghe giọng hát ru, đọc địa phương | Cảm nhận, dạy con | 0.5 / P1 | T-124, T-209 |
| US-12 | Người trẻ | Tìm bằng một câu mô tả tự do | Tìm khi không biết từ khóa | Theo cổng | T-301 |
| US-13 | Người ghi hộ | Gửi một câu ông bà còn nhớ, ghi rõ người kể, không cần tài khoản | Góp phần gìn giữ | Core (form) / P1 (xử lý) | T-115, T-202 |
| US-14 | Người ghi hộ | Gửi ghi âm giọng ông bà, kèm đồng ý của người được ghi | Lưu giữ giọng người thân | P2 | T-302 |
| US-15 | Cố vấn | Xem danh sách mục cần nâng mức | Duyệt nhanh, đúng chuyên môn | P1 | T-208 |
| US-16 | Người vận hành | Build thất bại khi dữ liệu sai | Không bao giờ xuất bản dữ liệu hỏng | Core | T-004 |
| US-17 | Sinh viên, nhà nghiên cứu | Tải toàn bộ dữ liệu có DOI | Trích dẫn học thuật | P1 | T-206 |

## 6. Definition of Done (mọi task phần mềm)

- [ ] Đạt mọi tiêu chí chấp nhận của task.
- [ ] `npm run kiem-tra` đạt; khi đã có Astro, lệnh này phải bao gồm `astro check` và build theo phase.
- [ ] Chạy đúng trên điện thoại (màn hình 360 px) và máy tính.
- [ ] Đạt WCAG 2.2 AA cho phần đã làm: bàn phím, focus nhìn thấy và không bị che, vùng chạm, tương phản, nhãn cho trình đọc màn hình (`23` mục 3).
- [ ] Không thêm cookie, theo dõi, dịch vụ hay thư viện mới ngoài những gì task cho phép.
- [ ] Không đổi dữ liệu trong `content/` (trừ khi task là biên soạn) và không đổi từ vựng kiểm soát.
- [ ] Tài liệu liên quan được cập nhật trong cùng PR nếu hành vi khác đặc tả.
- [ ] Commit do AI agent tạo có dòng `AI-Agent: <công cụ/mô hình>` (D-032).
