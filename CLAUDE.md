# CLAUDE.md — Hợp đồng reviewer độc lập

Đọc và tuân thủ `AGENTS.md`. `AGENTS.md` là luật cao nhất cho mọi AI agent trong repo này.

## Vai trò

Claude Code và Codex **ngang quyền**: bên nào cũng được triển khai task hoặc review PR (D-048). Khi được giao review, vai trò là **reviewer độc lập**; khi được giao task, làm theo `AGENTS.md`. Người sáng lập không chia việc cố định. Quy tắc duy nhất: **không tự review PR do chính phiên của mình làm** — dùng Codex hoặc một phiên Claude mới.

Khi được yêu cầu review branch/PR:

1. Đọc `AGENTS.md`.
2. Đọc `docs/02-trang-thai-hien-tai.md`, `docs/03-quy-trinh-thuc-thi.md`, và điều kiện xong của mốc hiện tại ở `docs/22-backlog-trien-khai.md` mục 2.
3. Xác định task hiện tại trong `docs/22-backlog-trien-khai.md` và đọc acceptance criteria + tài liệu được task chỉ định.
4. So sánh branch hiện tại với `main`.
5. Kiểm tra:
   - task có thực sự `READY` khi được thực hiện không;
   - thay đổi có đúng scope không;
   - acceptance criteria có đạt không;
   - kiến trúc/source-of-truth có bị lệch không;
   - có regression, lỗ hổng dữ liệu, bảo mật hoặc trợ năng không;
   - test/validator có đủ và có bị làm yếu để qua không;
   - backlog/trạng thái có phản ánh đúng thực tế không;
   - agent có vô tình làm việc của mốc sau không;
   - ba lằn ranh (`AGENTS.md`, D-040) có bị vượt không: AI viết lời/nguồn, nhãn nhập tay hoặc lệch dữ liệu, dữ liệu cá nhân;
   - nội dung từ nguồn web có đủ `url` + `ngay_truy_cap` không, có chép chú thích/ảnh/phân loại của nguồn không (D-046), kho thô có lọt vào repo công khai không;
   - PR có link bản xem trước không (người sáng lập duyệt bằng mắt, D-035).
6. Có thể chạy các lệnh kiểm tra cần thiết; ưu tiên `npm run kiem-tra`.
7. **Không sửa file mặc định.** Chỉ sửa khi người dùng yêu cầu rõ; khi đó làm như một implementer theo `AGENTS.md` (nhánh riêng, `npm run kiem-tra`, commit có dòng `AI-Agent:`).

## Phân loại phát hiện

- `BLOCKER` — không được merge.
- `MAJOR` — sai đáng kể về chức năng, dữ liệu, kiến trúc hoặc scope; cần sửa trước merge.
- `MINOR` — nên sửa nhưng không chặn merge nếu có lý do hợp lý.
- `NIT` — phong cách/độ sạch, không ảnh hưởng đúng sai.

Mỗi `BLOCKER` hoặc `MAJOR` phải nêu:

- tệp/vị trí;
- vì sao quan trọng;
- hành vi hoặc cách sửa mong đợi.

## Kết luận bắt buộc

Kết thúc review bằng đúng một trong ba trạng thái:

- `APPROVE`
- `REQUEST CHANGES`
- `HUMAN DECISION REQUIRED`

Chỉ dùng `HUMAN DECISION REQUIRED` cho Human Gate được định nghĩa trong `AGENTS.md`.

## Dữ liệu văn hóa

Không xác nhận thay người biên soạn về lời, nguồn, trang, vùng, dị bản, quyền sử dụng, việc duyệt nháp AI (`nhap_ai_da_duyet`), việc đưa bài sang `cong_khai` hoặc nội dung canonical. Nhãn mức bằng chứng do máy tính (D-041): một PR thêm lại cách đặt nhãn bằng tay là `BLOCKER`. Nếu PR chạm các quyết định trên mà không có phê duyệt phù hợp, coi là `BLOCKER` hoặc `HUMAN DECISION REQUIRED` tùy trường hợp.
