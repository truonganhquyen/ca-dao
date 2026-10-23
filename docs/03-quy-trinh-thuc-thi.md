# 03 · Quy trình thực thi tự động

| | |
|---|---|
| Phiên bản | 1.2 · 06/10/2026 |
| Áp dụng cho | Codex Cloud và mọi AI agent triển khai |
| Mục tiêu | Agent tự xác định việc nên làm tiếp theo mà không cần người sáng lập viết task kỹ thuật bằng tay |

## 1. Câu lệnh mặc định

Khi người sáng lập chỉ nói **“Tiếp tục dự án theo quy trình thực thi trong repo”**, agent phải tự làm quy trình dưới đây.

Nếu người dùng giao một task cụ thể, task đó được ưu tiên nhưng vẫn phải tuân thủ `AGENTS.md`, cổng chất lượng và mọi Human Gate.

## 2. Thứ tự đọc bắt buộc

1. `AGENTS.md`.
2. `docs/02-trang-thai-hien-tai.md`.
3. `du-an/trang-thai.json`.
4. `docs/04-cong-chat-luong.md` — cổng của giai đoạn hiện tại.
5. `docs/22-backlog-trien-khai.md` — task, phụ thuộc, trạng thái, tiêu chí chấp nhận.
6. Tài liệu chuyên môn mà task được chọn yêu cầu.
7. `docs/01-nhat-ky-quyet-dinh.md` nếu task chạm Q-xxx hoặc D-xxx liên quan.

## 3. Cách tự chọn việc tiếp theo

Agent phải:

1. Xác định giai đoạn hiện tại từ `du-an/trang-thai.json`.
2. Xác định cổng chất lượng tiếp theo.
3. Chỉ xét task thuộc giai đoạn hiện tại.
4. Loại task `DONE`, `DEFERRED`, `IN_PROGRESS`, `REVIEW`. Task đang ghi `BLOCKED` có thể được **suy ra thành READY** nếu mọi dependency đã `DONE` và không còn Q-xxx trong cột `Chặn`; `npm run viec-tiep-theo` thực hiện phép suy ra này.
5. Không chọn task dành riêng cho **N** (người sáng lập).
6. Trong các task READY (khai báo hoặc suy ra) dành cho A hoặc N+A, ưu tiên theo thứ tự:
   - Core trước 0.5/P1/P2;
   - task trực tiếp gỡ phụ thuộc hoặc tiến tới cổng hiện tại;
   - task có số nhỏ hơn khi các yếu tố trên ngang nhau.
7. Có thể chạy `npm run viec-tiep-theo` để lấy đề xuất máy đọc; agent vẫn phải đối chiếu với tài liệu trước khi làm.

Một Q-xxx đang mở **không chặn toàn dự án**. Chỉ dừng nếu task được chọn chạm trực tiếp Q đó hoặc cột `Chặn` của task nêu Q đó.

## 4. Phân rã task

Nếu task quá lớn cho một PR an toàn, agent được phép chia thành task con tạm thời, ví dụ `T-010.1`, `T-010.2`.

Task con:

- phải nằm hoàn toàn trong phạm vi task cha;
- không được tạo tính năng của phase sau;
- không thay đổi quyết định đã chốt;
- phải được ghi rõ trong mô tả PR hoặc backlog nếu cần theo dõi qua nhiều PR.

## 5. Thực hiện

Trước khi sửa:

- đọc acceptance criteria;
- kiểm tra working tree và thay đổi hiện có;
- không ghi đè thay đổi không thuộc task;
- không mở rộng kiến trúc vì “sau này có thể cần”.

Trong khi sửa:

- ưu tiên thay đổi nhỏ nhất giải quyết đúng task;
- bổ sung kiểm thử cùng hành vi mới;
- cập nhật tài liệu source-of-truth trong cùng PR nếu hành vi thay đổi;
- tuân thủ đầy đủ giới hạn dữ liệu văn hóa trong `AGENTS.md`.

## 6. Kiểm tra trước khi hoàn tất

Chạy:

```bash
npm run kiem-tra
```

Khi dự án đã có Astro hoặc kiểm thử mới, `npm run kiem-tra` phải được mở rộng để bao gồm các kiểm tra bắt buộc của phase đó. Không được bỏ qua hoặc làm yếu kiểm tra để task qua.

Agent phải xem lại `git diff` trước khi kết thúc.

## 7. Cập nhật trạng thái

Khi task thực sự hoàn tất và kiểm tra đạt:

1. chỉ khi implementation + acceptance criteria + `npm run kiem-tra` đều đạt, chuyển task thành `DONE` trong commit cuối của branch; GitHub PR thể hiện trạng thái đang review;
2. cập nhật `docs/02-trang-thai-hien-tai.md`;
3. cập nhật `du-an/trang-thai.json` nếu tiến độ/gate/current task thay đổi;
4. có thể chuyển các task phụ thuộc vừa được gỡ sang `READY`, nhưng không bắt buộc vì `npm run viec-tiep-theo` có thể suy ra trạng thái READY từ dependency.

Không đánh dấu `DONE` chỉ vì code đã viết xong; phải đạt acceptance và cổng kiểm tra. Nếu PR không được gộp, thay đổi `DONE` cũng không đi vào `main`.

## 8. Điều kiện dừng

Agent phải dừng và trả về **HUMAN DECISION REQUIRED** nếu gặp một Human Gate trong `AGENTS.md`.

Agent cũng dừng nếu:

- acceptance criteria mâu thuẫn với source-of-truth;
- cần mở rộng scope để tiếp tục;
- kiểm tra thất bại vì nguyên nhân không thể giải quyết an toàn trong phạm vi task;
- phát hiện thay đổi dữ liệu canonical cần phán quyết của người biên soạn.

Nếu không thuộc các trường hợp trên, agent tự quyết trong giới hạn đã chốt và không hỏi người dùng các câu kỹ thuật nhỏ.

## 9. Báo cáo cuối task

Báo cáo ngắn theo đúng thứ tự:

1. Giai đoạn hiện tại.
2. Task đã chọn và lý do chọn.
3. Việc đã làm.
4. Việc cố ý không làm.
5. Kết quả `npm run kiem-tra`.
6. Human Gate/câu hỏi còn lại, nếu có.
7. Task được đề xuất tiếp theo.
