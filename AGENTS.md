# AGENTS.md — Hướng dẫn cho AI agent

Tệp này dành cho mọi AI agent làm việc trong kho mã (Claude Code, Codex, Cursor, Copilot…). Dev là người cũng nên đọc: đây là luật chơi của dự án.

## Dự án là gì

**Kho tàng Ca dao Tục ngữ Việt Nam**: một website tĩnh, phi lợi nhuận, không quảng cáo, nơi mỗi câu ca dao tục ngữ có nguồn in, dị bản, chú giải từ khó và nhãn mức bằng chứng. Một người vận hành, khoảng 10 giờ/tuần. **Giá trị duy nhất của dự án là niềm tin** — một lỗi bịa nguồn có thể phá hủy nó.

## Đọc trước khi làm

1. `docs/00-tai-lieu-nen.md` — luôn đọc.
2. `docs/02-trang-thai-hien-tai.md` + `du-an/trang-thai.json` — dự án đang ở đâu.
3. `docs/03-quy-trinh-thuc-thi.md` — nếu người dùng không giao task cụ thể, tự chọn việc theo quy trình này.
4. `docs/04-cong-chat-luong.md` — điều kiện được phép chuyển giai đoạn.
5. `docs/22-backlog-trien-khai.md` — task, phụ thuộc, trạng thái, tiêu chí chấp nhận, mục "Đọc".
6. `docs/01-nhat-ky-quyet-dinh.md` — nếu task chạm tới Q-xxx còn mở: **dừng và hỏi**.

| Loại việc | Tài liệu |
|---|---|
| Dữ liệu, schema, công cụ kiểm tra | `docs/10-dac-ta-du-lieu.md` |
| Soạn nháp nội dung | `docs/11-huong-dan-bien-soan.md`, `templates/prompt-soan-nhap.md` |
| Nguồn, bản quyền | `docs/12-chinh-sach-nguon.md` |
| Giao diện, câu chữ | `docs/20-dac-ta-giao-dien.md` |
| Kỹ thuật, build, tìm kiếm | `docs/21-kien-truc-ky-thuat.md` |
| Kiểm thử | `docs/23-ke-hoach-kiem-thu.md` |
| Đóng góp, ghi âm | `docs/40-…`, `docs/41-…` |


## Chế độ tự thực thi

Nếu người dùng không giao task cụ thể mà yêu cầu tiếp tục dự án, **không hỏi người dùng phải làm task nào**. Làm theo `docs/03-quy-trinh-thuc-thi.md`:

- xác định phase hiện tại;
- đọc cổng chất lượng;
- chọn task `READY` dành cho A/N+A;
- ưu tiên Core và việc trực tiếp tiến tới cổng;
- tự phân rã task nếu cần nhưng không mở rộng scope;
- cập nhật trạng thái sau khi làm.

Một Q-xxx đang mở chỉ chặn task thực sự phụ thuộc/chạm Q đó; không được dùng một Q không liên quan làm lý do dừng toàn dự án.

## Human Gate

Chỉ trả về **HUMAN DECISION REQUIRED** khi gặp một trong các trường hợp:

1. **H1 — Q-xxx/domain:** phải chốt câu hỏi mở hoặc thay đổi mô hình miền/schema lớn.
2. **H2 — sự thật biên tập:** nâng/hạ mức bằng chứng, xác nhận lời/nguồn/vùng/diễn giải.
3. **H3 — quyền:** thay đổi trạng thái bản quyền/quyền tái sử dụng/đồng ý ghi âm.
4. **H4 — hướng UX lớn:** lựa chọn thay đổi đáng kể định hướng thẩm mỹ hoặc hành vi đã được chốt.
5. **H5 — chuyển phase:** đóng băng schema, chấp thuận lát cắt dọc, beta, ra mắt công khai.
6. **H6 — dữ liệu canonical:** xóa, gộp, đổi mã hoặc sửa phá hủy hồ sơ canonical.

Ngoài các Human Gate này, agent phải tự quyết các chi tiết kỹ thuật trong phạm vi source-of-truth thay vì hỏi người dùng các câu nhỏ.

## AI được làm gì

**Đề xuất, không bao giờ lặng lẽ sửa.** AI được: trích xuất, phân loại, so sánh, gợi ý, gắn cờ, soạn nháp (luôn có `NHAP_AI`), viết mã và kiểm thử theo task. Mọi thay đổi đi qua một PR để người duyệt.

## KHÔNG BAO GIỜ

1. **Không tạo, đoán hay "điền cho đủ" nội dung văn hóa:** lời ca dao/tục ngữ, dị bản, vùng, địa danh, người kể, tên sách, NXB, năm, số trang, giải nghĩa, điển tích, bối cảnh diễn xướng, luật bằng trắc. Thiếu thì ghi `CAN_BO_SUNG` và báo lại.
2. **Không viết văn bản nội dung mà không gắn tiền tố `NHAP_AI`.** Mọi chuỗi bạn viết vào `content/` (nghĩa, diễn giải, mô tả tình huống, "Hợp khi…", giải nghĩa) phải bắt đầu bằng `NHAP_AI ` để người biên soạn viết lại.
3. **Không sửa trường `loi`** của dị bản đã có, trừ khi task nói rõ và đưa kèm trích bản in.
4. **Không đặt `xac_thuc: A` hoặc `B`**, không đặt `trang_thai` khác `nhap`, không đặt `da_xac_minh_ban_in: true`, không đổi `ban_quyen` của nguồn sang `cong_cong`/`duoc_phep`. Chỉ người biên soạn làm.
5. **Không xóa, gộp, đổi mã** tác phẩm, dị bản, nguồn, mục từ đã có — kể cả khi công cụ báo nghi trùng (R19). Báo lại để người biên soạn quyết.
6. **Không thêm giá trị vào `taxonomy/tu-vung-kiem-soat.yaml`** nếu task không yêu cầu rõ.
7. **Không làm yếu, tắt hay bỏ qua kiểm tra dữ liệu** để build qua. Build lỗi thì sửa dữ liệu hoặc báo lại.
8. **Không thêm:** cơ sở dữ liệu, backend, tài khoản/phân quyền, CMS (kể cả Strapi, Directus), Elasticsearch hay cơ sở dữ liệu vector, cookie, theo dõi, quảng cáo, TTS/giọng tổng hợp, chatbot, tính năng AI sinh văn bản công khai, dịch vụ trả phí, React/shadcn/thư viện thành phần ở trang công khai, thư viện nặng — trừ khi task cho phép rõ. Công cụ biên tập (Admin), đường ống nạp tư liệu, monorepo **chỉ** bắt đầu khi có task được tạo theo `docs/21` mục 14.
9. **Không đưa thông tin cá nhân** (email, số điện thoại, tên thật của người chọn ẩn danh, địa chỉ) vào bất kỳ tệp nào trong kho.
10. **Không dùng từ cấm trong giao diện:** "bản gốc", "bản đúng", "bản chuẩn", "bản sai", "AI giải thích", "câu hay nhất". Bản đại diện gọi là **"Bản tham chiếu"**.
11. **Không chép ví dụ ca dao từ các bản ý tưởng gốc hay từ trí nhớ vào `content/`** như dữ liệu thật.
12. **Không tự chọn phương án cho câu hỏi còn mở** (Q-xxx), kể cả phương án khuyến nghị.

## LUÔN LUÔN

- Chạy `npm run kiem-tra` trước khi kết thúc. Khi Astro/test mới xuất hiện, mở rộng chính lệnh này thay vì tạo đường kiểm tra rời rạc.
- Lưu văn bản UTF-8, Unicode dựng sẵn (NFC), xuống dòng LF.
- Giữ JavaScript phía trình duyệt tối thiểu; trang đọc được khi tắt JavaScript.
- Giữ trợ năng: bàn phím, focus rõ, nhãn không chỉ dựa vào màu, vùng chạm ≥ 44 px.
- Đổi schema thì đổi cùng lúc: `docs/10`, `scripts/kiem-tra-du-lieu.mjs`, `templates/`, fixture trong `tests/fixtures/`, và ghi D-xxx vào `docs/01`.
- Làm một task mỗi nhánh/PR; mô tả PR gồm: đã làm, **không** làm, câu hỏi còn lại.
- Commit của bạn có dòng `AI-Agent: <công cụ/mô hình>` ở cuối nội dung commit (dấu vết tối thiểu, D-032).
- Khai chú giải bằng `cum_tu` (gõ đúng như trong lời); chỉ thêm `dong`/`lan` khi công cụ báo mơ hồ. Vị trí chú giải trong mã giao diện lấy từ `phanGiaiNeo()` ở `scripts/lib/neo-chu-giai.mjs`, không tự viết bộ tìm vị trí khác.
- Phân biệt lớp tính năng: **Core** chặn beta, **0.5** chỉ làm sau Cổng 2 (`docs/22` mục 1).

## Khi không chắc

- Nếu liên quan Human Gate: dừng và hỏi người sáng lập, ghi rõ `HUMAN DECISION REQUIRED`.
- Nếu chỉ là chi tiết kỹ thuật nhỏ trong phạm vi đã chốt: tự chọn phương án đơn giản nhất, ghi giả định trong PR và tiếp tục.
- Tuyệt đối không biến sự không chắc chắn về dữ liệu văn hóa thành phỏng đoán. Một câu hỏi đúng chỗ tốt hơn một nguồn bịa.

## Lệnh

```bash
npm install            # cài yaml (và Astro, sau T-002)
npm run trang-thai     # xem phase, cổng, Human Gate và tiến độ
npm run viec-tiep-theo # đề xuất task kỹ thuật READY tiếp theo
npm run kiem-tra       # cổng kiểm tra bắt buộc hiện tại
npm run check:data     # kiểm tra dữ liệu theo R01–R19
npm run test:fixtures  # kiểm thử chính công cụ kiểm tra
npm run health         # báo cáo sức khỏe dữ liệu, nợ dữ liệu (không chặn build)
```


## Điều kiện hoàn tất task

Một task phần mềm chỉ được coi là sẵn sàng review khi:

- mọi tiêu chí chấp nhận liên quan đã đạt;
- `npm run kiem-tra` đạt;
- agent đã xem lại `git diff`;
- tài liệu/trạng thái/backlog phản ánh đúng thực tế;
- PR nêu rõ đã làm, không làm, kết quả kiểm tra và Human Gate nếu có.

Khi implementation, acceptance criteria và `npm run kiem-tra` đã đạt, branch có thể ghi task `DONE` trước khi mở PR; GitHub PR là trạng thái review thực tế. Nếu PR không được gộp, thay đổi `DONE` không đi vào `main`.

## Quy ước

- Tài liệu, câu chữ giao diện, thông báo lỗi: **tiếng Việt**.
- Mã định danh, tên trường, mã từ vựng: chữ thường không dấu, gạch dưới (`ban_dai_dien`, `xac_thuc_dan_gian`).
- Tên tệp nội dung: `<id>.yaml`.
- Commit: `T-xxx: <mô tả ngắn>`; nâng/hạ mức bằng chứng thì ghi lý do trong nội dung commit.
