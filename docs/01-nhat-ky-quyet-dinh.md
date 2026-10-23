# 01 · Nhật ký quyết định

| | |
|---|---|
| Trạng thái | **Sống.** Thêm dòng mới, không xóa dòng cũ; quyết định bị thay thì ghi "Thay bởi D-xxx" |
| Đọc khi | Trước khi đổi bất kỳ điều gì trong tài liệu 00, 10 hoặc kiến trúc |

**Quy tắc cho AI agent:** task chạm tới một câu hỏi còn mở (Q-xxx) có cột "Chặn" khác "—" thì **dừng và hỏi người sáng lập**. Không tự chọn phương án, kể cả phương án khuyến nghị.

## 1. Câu hỏi còn mở

| Mã | Câu hỏi | Khuyến nghị | Chặn | Hạn chốt |
|---|---|---|---|---|
| Q-001 | Thực tế có bao nhiêu giờ mỗi tuần? | Lập kế hoạch theo 10 giờ; dư thì rút ngắn mốc, không thêm tính năng | Lịch trong `00` mục 7 | Tuần 1 |
| Q-002 | Người sáng lập có thoải mái làm việc với Git + YAML không? | Có → phương án A. Không → phương án B (bảng tính → script xuất YAML, hoặc CMS chạy trên Git như Sveltia/Decap); thử trên 10 hồ sơ mẫu | T-007 | Tuần 1 |
| Q-003 | Giấy phép phần dự án tự viết: CC BY-SA 4.0 hay CC BY-NC-SA 4.0? | CC BY-SA 4.0: tương thích Wikipedia/Wiktionary, không cản giáo viên và nhà xuất bản giáo dục; điều khoản phi thương mại cũng không ngăn được trang rác chép lại | T-115 (câu đồng ý trong form), T-201, trang Giấy phép, chân trang, thẻ chia sẻ | Tuần 2 |
| Q-004 | Quy ước bỏ dấu cho văn bản dự án tự viết ("hòa" hay "hoà") | Chọn một, ghi vào `11` mục 9; lời trích từ sách giữ nguyên bản in; tìm kiếm khớp cả hai kiểu | `11` mục 9 | Tuần 2 |
| Q-005 | Tên miền (.vn / .org / tên riêng) | Chốt trước khi làm thẻ chia sẻ (tên miền in trên ảnh) | T-111, T-112 (P0.5) | Trước GĐ2 |
| Q-006 | Lục bát: thụt lề câu lục hay căn giữa? | Mặc định thụt lề, đặt bằng một biến cấu hình; thử với nhóm beta | — (đã có mặc định) | Beta |
| Q-007 | Có làm đồng dao trong 6 tháng đầu? | Để sau | — | Tháng 3 |
| Q-008 | Nhận tài trợ? Pháp nhân bảo trợ? | Để giai đoạn 2 | — | GĐ2 |

## 2. Quyết định đã chốt

Ngày chốt chung: 04/10/2026, người quyết định: người sáng lập (qua phiên hợp nhất).

| Mã | Quyết định | Lý do ngắn | Nguồn gốc |
|---|---|---|---|
| D-001 | Lấy bản Opus 5.5 làm khung; bổ sung từ GPT, Qwen, Meta và bản review của GPT | Ít "lỗ" nhất ở phần khó sửa về sau | Phiên hợp nhất |
| D-002 | Kiến trúc tĩnh: Astro + YAML trong Git + Cloudflare Pages | Chi phí gần 0, ít thứ hỏng, lịch sử sửa đổi miễn phí | Opus |
| D-003 | Không dùng cơ sở dữ liệu cho tới khi đạt một ngưỡng: > 2 biên tập viên thường xuyên, > 100 đóng góp/tháng, hoặc cần duyệt theo thời gian thực | "Động sau" phải có điều kiện đo được | Review GPT |
| D-004 | Mô hình Tác phẩm → Dị bản; mỗi tác phẩm có đúng một bản đại diện kèm lý do | Tôn trọng tính truyền miệng, tránh "bản đúng duy nhất" | Opus, GPT |
| D-005 | Giao diện gọi bản đại diện là "Bản tham chiếu"; không dùng "bản gốc/bản đúng/bản chuẩn" | Nhãn sai làm người đọc hiểu sai dù dữ liệu đúng | Review GPT |
| D-006 | Chú giải là con trỏ tới Từ điển dùng chung, neo theo dòng + cụm từ + lần xuất hiện | Neo ký tự lệch khi sửa chính tả; giải nghĩa một lần dùng mọi bài | Opus |
| D-007 | Mức bằng chứng A/B/C/D cho từng dị bản; mức của tác phẩm suy ra từ bản đại diện, không lưu riêng | Tránh hai nơi ghi cùng một sự thật | Opus, GPT, phiên hợp nhất |
| D-008 | "Nghi không phải dân gian" là cờ riêng ở cấp tác phẩm, không phải mức bằng chứng | Một câu có thể in trong sách (A) mà vẫn bị gán nhầm | Review GPT |
| D-009 | Trạng thái quy trình: nhap → cho_kiem_dinh → da_xuat_ban; an. Cờ tranh_luan riêng | Tách "đang ở bước nào" khỏi "có bằng chứng gì" | Phiên hợp nhất |
| D-010 | Quan hệ tác phẩm–tình huống chỉ lưu ở tệp tình huống (kèm "Hợp khi…"); trang tác phẩm tính ngược lúc build | Tránh dữ liệu hai chiều lệch nhau (cùng nguyên tắc Opus áp cho mô-típ) | Phiên tách tài liệu |
| D-011 | Dấu `CAN_BO_SUNG` (thiếu thông tin) và `NHAP_AI` (văn bản AI chưa viết lại) chặn chuyển trạng thái | Biến quy tắc "AI không xuất bản" thành kiểm tra máy được | Phiên tách tài liệu |
| D-012 | Không tài khoản người dùng; đóng góp qua form có sẵn → bảng tính riêng tư | Không máy chủ, không dữ liệu cá nhân trong Git | Opus |
| D-013 | Nâng mức chỉ bằng bằng chứng; chuyên gia không tự động "đã xác minh"; không bình chọn | Bằng chứng trước, uy tín sau | Opus, Qwen, GPT |
| D-014 | Không TTS, kể cả "cho trợ năng" | Thiết bị đã có trình đọc màn hình; giọng máy dễ bị nhầm là tư liệu | Opus |
| D-015 | Tìm ngữ nghĩa chỉ bật khi recall@5 ≥ 70% trên ≥ 50 truy vấn thật; vector từ tiêu đề + nghĩa ngắn + mô tả tình huống, không từ lời thơ | Kho nhỏ thì vector luôn trả về "gần nhất" dù không hợp | Opus |
| D-016 | North Star: Lượt dùng tin cậy/tuần (trích dẫn + thẻ có nguồn + tải dữ liệu) | Đo đúng sứ mệnh | Opus, review GPT |
| D-017 | Thẻ chia sẻ 2 mẫu khi ra mắt (Giấy dó, Mực tàu), 2 khổ | Bớt việc GĐ1 | Review GPT |
| D-018 | Không xuất bản "mục nhẹ" | Tránh tái tạo trang mỏng | Opus, review GPT |
| D-019 | Công cụ kiểm tra dữ liệu viết bằng JavaScript (Node), chạy trước build; là nơi duy nhất định nghĩa quy tắc R01–R17 (từ v1.1: R01–R19, xem D-028) | Một nguồn sự thật cho quy tắc; schema Zod trong Astro chỉ để có kiểu dữ liệu | Phiên tách tài liệu |

### Bổ sung sau review trước triển khai (05/10/2026)

| Mã | Quyết định | Lý do ngắn | Nguồn gốc |
|---|---|---|---|
| D-020 | Neo chú giải khai bằng `cum_tu` (thay `tu`); `dong`, `lan` chỉ khai khi cần. Máy phân giải bằng một module duy nhất `scripts/lib/neo-chu-giai.mjs`, dùng cho cả kiểm tra và build; xuất hiện nhiều lần mà chưa chỉ rõ → lỗi, không tự chọn | Con người không nhập thứ máy suy ra được; máy không đoán khi không chắc | Review |
| D-021 | GĐ0 đo thời gian theo 5 phần (tìm nguồn, xác minh, nhập liệu, chú giải, QA); sau 10 hồ sơ thật, chỉnh schema một lần rồi **đóng băng v1**; chỉ đổi khi hồ sơ thật không biểu diễn được | Biết nút thắt thật trước khi tối ưu; chặn phình schema | Review |
| D-022 | Thêm **lát cắt dọc** (T-010, Cổng 1.5) với 3 hồ sơ thật trước khi xây diện rộng | Bắt lỗi giao nhau giữa schema, giao diện, tìm kiếm, trích dẫn, build | Review |
| D-023 | Thử với người dùng sớm: preview kín ở 15–20 hồ sơ, beta khả dụng ở 25–30 (Cổng 2); 60 hồ sơ là mốc mở rộng nội dung, không phải điều kiện beta; ra mắt công khai ~120 khi qua Cổng 3. Thay phần lộ trình của D-001/`00` bản 1.0 | Không làm kín 2–3 tháng rồi mới phát hiện vấn đề | Review |
| D-024 | MVP chia **P0-Core** (chặn beta) và **P0.5** (chặn ra mắt, không chặn beta): thẻ chia sẻ, ảnh OG riêng, sáng/tối có nút, ngẫu nhiên, câu hôm nay, A–Z, lịch tình huống, trình phát âm thanh, trang Chính sách AI/Giấy phép/Quyền riêng tư, đo lường mở rộng. Thay danh sách 12 hạng mục của bản 1.0 | Chứng minh giá trị cốt lõi trước | Review |
| D-025 | Trang tác phẩm đọc theo lớp: lớp 1 mở sẵn (lời → hiểu nhanh → chú giải → nguồn → dị bản), phần sâu thu gọn | Mô hình giàu, trải nghiệm đọc đơn giản | Review |
| D-026 | Đo lường P0 chỉ 7 sự kiện phục vụ quyết định (`21` mục 7); còn lại để P0.5 trở đi | Tránh nợ đo lường | Review |
| D-027 | Quyền sử dụng kiểm ở nguồn (`ban_quyen`), ở tư liệu điền dã (R18) và ở ghi âm (`ma_dong_y`); **không** thêm `rights_status` trên tác phẩm | Lời là di sản, chú giải do dự án viết: rủi ro quyền nằm ở nguồn và ghi âm | Review (điều chỉnh) |
| D-028 | Thêm R19 (nghi trùng) và `npm run health` (báo cáo sức khỏe, nợ dữ liệu) vào cùng công cụ kiểm tra | Phát hiện trùng sớm; có danh sách việc mà chưa cần công cụ biên tập | Review |
| D-029 | Chuẩn trợ năng: **WCAG 2.2 AA** (thay 2.1) | Có nhóm người dùng lớn tuổi; 2.2 thêm tiêu chí về focus và vùng chạm | Review |
| D-030 | Giao diện công khai: Astro + TypeScript + **Tailwind CSS 4** dùng token ở `20` mục 1.1; đảo tương tác bằng TypeScript thuần + `<dialog>`/`popover` gốc của HTML. **Không** React, shadcn/ui, Base UI ở trang công khai MVP. Tìm kiếm giữ MiniSearch | Ngân sách JS < 50 KB; React runtime riêng đã gần hết ngân sách; MiniSearch cho kiểm soát chuẩn hóa dấu tiếng Việt | Review (điều chỉnh) |
| D-031 | Công cụ biên tập riêng (Admin "workbench", không phải CRUD), đường ống nạp tư liệu, Rights Gate xanh/vàng/đỏ, monorepo: **thiết kế được ghi nhận, chưa xây**; chỉ làm khi đạt điều kiện kích hoạt ở `21` mục 14 | Một người, chưa có hàng đợi ứng viên; tránh xây trước nhu cầu | Review (hoãn) |
| D-032 | Dấu vết AI tối thiểu qua Git: commit do AI tạo có dòng `AI-Agent: <công cụ/mô hình>`; người duyệt PR là bước kiểm duyệt; không lưu toàn bộ lệnh | Truy được ai/cái gì đã sửa mà không thêm hạ tầng | Review |

### Bổ sung cho quy trình agent-driven (06/10/2026)

| Mã | Quyết định | Lý do ngắn | Nguồn gốc |
|---|---|---|---|
| D-033 | GitHub là control plane; **Codex Cloud** là planner + implementer chính; **Claude Code** là reviewer độc lập mặc định. Repo tự mô tả phase/task qua `docs/02`, `docs/03`, `docs/04`, `du-an/trang-thai.json`; người sáng lập chỉ cần tham gia tại Human Gate | Giảm việc người sáng lập phải tự chia/giao task; vẫn có kiểm tra độc lập và dấu vết qua Git/PR | Quyết định vận hành 06/10/2026 |
| D-034 | Có một cổng lệnh duy nhất `npm run kiem-tra`; CI gọi lệnh này. Khi dự án lớn lên, mở rộng nội dung lệnh thay vì tạo nhiều quy trình kiểm tra không đồng bộ | Agent cần một tiêu chuẩn máy kiểm được để biết task đã đủ điều kiện review | Quyết định vận hành 06/10/2026 |

## 3. Mẫu ghi quyết định mới

```markdown
| D-0xx | <quyết định> | <lý do 1 câu> | <ai/nguồn> |
```
Nếu quyết định đổi schema: cập nhật cùng lúc `10-dac-ta-du-lieu.md`, `scripts/kiem-tra-du-lieu.mjs`, `templates/`, và thêm fixture trong `tests/fixtures/`.
