# 03 · Quy trình thực thi tự động

> **Lưu ý (D-071, 26/10/2026):** site Astro và CMS Sveltia đã gỡ khỏi repo. Chỗ nào nhắc `content/`, YAML, `templates/`, `config/`, Astro hay `/admin` là lịch sử; quy tắc nội dung và nguồn vẫn còn hiệu lực trên WordPress.

| | |
|---|---|
| Phiên bản | 2.0 · 23/10/2026 (mốc thay cổng, D-044) |
| Áp dụng cho | Codex Cloud và mọi AI agent triển khai |
| Mục tiêu | Agent tự xác định việc nên làm tiếp theo mà không cần người sáng lập viết task kỹ thuật bằng tay |

## 1. Câu lệnh mặc định

Khi người sáng lập chỉ nói **“Tiếp tục dự án theo quy trình thực thi trong repo”**, agent phải tự làm quy trình dưới đây.

Nếu người dùng giao một task cụ thể, task đó được ưu tiên nhưng vẫn phải tuân thủ `AGENTS.md` (ba lằn ranh) và mọi Human Gate.

## 2. Thứ tự đọc bắt buộc

1. `AGENTS.md`.
2. `docs/02-trang-thai-hien-tai.md`.
3. `du-an/trang-thai.json`.
4. `docs/22-backlog-trien-khai.md` — mục 2 (điều kiện xong của mốc hiện tại), mục 1 và 3 (task, phụ thuộc, tiêu chí chấp nhận).
5. Tài liệu chuyên môn mà task được chọn yêu cầu.
6. `docs/01-nhat-ky-quyet-dinh.md` nếu task chạm Q-xxx hoặc D-xxx liên quan.

## 3. Cách tự chọn việc tiếp theo

Agent phải:

1. Xác định giai đoạn (mốc) hiện tại từ `du-an/trang-thai.json`.
2. Đọc điều kiện xong của mốc đó ở `docs/22` mục 2.
3. Xét task của mốc hiện tại **và** các mốc trước còn sót; không lấy task của mốc sau.
4. Loại task `DONE`, `DEFERRED`, `IN_PROGRESS`, `REVIEW`. Task đang ghi `BLOCKED` có thể được **suy ra thành READY** nếu mọi dependency đã `DONE` và không còn Q-xxx trong cột `Chặn`; `npm run viec-tiep-theo` thực hiện phép suy ra này.
5. Không chọn task dành riêng cho **N** (người sáng lập).
6. Trong các task READY (khai báo hoặc suy ra) dành cho A hoặc N+A, ưu tiên theo thứ tự:
   - mốc sớm hơn trước;
   - task trực tiếp gỡ phụ thuộc hoặc tiến tới điều kiện xong của mốc;
   - task có số nhỏ hơn khi các yếu tố trên ngang nhau.
7. Có thể chạy `npm run viec-tiep-theo` để lấy đề xuất máy đọc; agent vẫn phải đối chiếu với tài liệu trước khi làm.
8. Khi mọi task A của mốc hiện tại đã `DONE` và điều kiện xong ở `docs/22` mục 2 đạt, agent **tự chuyển** `giai_doan` sang mốc kế tiếp (Chuẩn bị → A → B → C). Riêng **mở công khai (T-053) là Human Gate H5**. Nhóm **V2** chạy song song (`giai_doan_song_song`, D-091): không chuyển mốc; mã ở `wordpress/v2/`, MVP đóng băng tính năng. T-156 (dùng thử) và T-157 (ngày chuyển) cần người sáng lập; agent không tự đánh `DONE` khi chưa có câu trả lời.

Một Q-xxx đang mở **không chặn toàn dự án**. Chỉ dừng nếu task được chọn chạm trực tiếp Q đó hoặc cột `Chặn` của task nêu Q đó.

## 4. Phân rã task

Nếu task quá lớn cho một PR an toàn, agent được phép chia thành task con tạm thời, ví dụ `T-040.1`, `T-040.2`.

Task con:

- phải nằm hoàn toàn trong phạm vi task cha;
- không được tạo tính năng của mốc sau;
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

Khi dự án đã có Astro hoặc kiểm thử mới, `npm run kiem-tra` phải được mở rộng để bao gồm các kiểm tra bắt buộc của mốc đó. Không được bỏ qua hoặc làm yếu kiểm tra để task qua.

Agent phải xem lại `git diff` trước khi kết thúc.

## 7. Cập nhật trạng thái

Khi task thực sự hoàn tất và kiểm tra đạt:

1. chỉ khi implementation + acceptance criteria + `npm run kiem-tra` đều đạt, chuyển task thành `DONE` trong commit cuối của branch; GitHub PR thể hiện trạng thái đang review;
2. cập nhật `docs/02-trang-thai-hien-tai.md` — **chỉ** mục "Đã có" và "Đang chờ người sáng lập"; **không** viết tay danh sách "việc tiếp theo" (ở đó chỉ có lệnh `npm run viec-tiep-theo`, README cũng vậy) để các PR đang mở cùng lúc không sửa chung một dòng;
3. cập nhật `du-an/trang-thai.json` nếu tiến độ/gate/current task thay đổi;
4. có thể chuyển các task phụ thuộc vừa được gỡ sang `READY`, nhưng không bắt buộc vì `npm run viec-tiep-theo` có thể suy ra trạng thái READY từ dependency.

Không đánh dấu `DONE` chỉ vì code đã viết xong; phải đạt acceptance và cổng kiểm tra. Nếu PR không được gộp, thay đổi `DONE` cũng không đi vào `main`.

### 7.1. Nhiều PR mở cùng lúc: xếp chồng nhánh, không song song từ `main`

PR nào cũng sửa vài tệp điều phối chung (`docs/01` bảng quyết định, `docs/02`, bảng ở `docs/22`). Hai PR cùng tách từ một `main` rồi cùng sửa chỗ đó thì PR gộp sau **chắc chắn** xung đột (24/10/2026: ba PR T-040, T-038, T-037 xung đột, tổng 8 lượt tệp phải giải tay). Quy tắc để không xảy ra:

1. **Trước khi tách nhánh** chạy `git fetch` và xem `gh pr list --state open`.
2. **Không có PR nào đang chờ gộp:** tách nhánh từ `origin/main`, PR trỏ vào `main`.
3. **Đang có PR chờ gộp và việc mới phụ thuộc hoặc cùng sửa tệp điều phối với nó:** tách nhánh **từ nhánh của PR đó**; tạo PR với `gh pr create --base <nhánh-PR-trước>` và ghi "Chồng lên PR #n; gộp #n trước" ở đầu mô tả. GitHub chỉ tự chuyển PR này về `main` khi nhánh của #n **bị xóa** sau khi gộp, nên repo phải bật "Automatically delete head branches" (Settings → General). Chưa bật thì PR chồng được gộp vào nhánh trung gian, không vào `main`: ngày 24/10/2026, #12 và #13 đã bị gộp như vậy, phải mở thêm một PR đưa vào `main`. Nếu đã lỡ gộp vào nhánh trung gian: mở PR từ nhánh cuối chuỗi vào `main`.
4. **Việc mới độc lập hẳn** (không sửa tệp điều phối chung nào) thì tách từ `main` như thường.
5. **Số D mới** = số D lớn nhất trên `origin/main` **và trên mọi PR đang mở** cộng 1. `npm run kiem-nhat-quan` báo LỖI nếu hai quyết định trùng số.
6. Từ D-066 **agent tự gộp** theo thứ tự PR đã mở, khi `npm run kiem-tra`, kiểm thử WordPress và CI đều đạt, và chỉ gộp khi đích của PR là `main` (`gh pr merge --merge`). Sau khi gộp, agent kiểm `git log origin/main` có đủ commit của cả chuỗi. Khi báo "PR báo xung đột", agent giải xung đột bằng cách **giữ thông tin của cả hai phía** (không chọn một bên), chạy lại `npm run kiem-tra`, đẩy lên.

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

1. Giai đoạn (mốc) hiện tại.
2. Task đã chọn và lý do chọn.
3. Việc đã làm.
4. Việc cố ý không làm.
5. Kết quả `npm run kiem-tra`.
6. Human Gate/câu hỏi còn lại, nếu có.
7. Task được đề xuất tiếp theo.
8. Link bản xem trước (từ T-035). Thay đổi trong `wordpress/`: thêm link Playground từ `npm run xem-truoc:wordpress`.
