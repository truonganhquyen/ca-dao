# AGENTS.md — Hướng dẫn cho AI agent

Tệp này dành cho mọi AI agent làm việc trong kho mã (Claude Code, Codex, Cursor, Copilot…). Dev là người cũng nên đọc: đây là luật chơi của dự án.

## Dự án là gì

**Kho tàng Ca dao Tục ngữ Việt Nam** (phongdao.com): một website WordPress, phi lợi nhuận, không quảng cáo, đọc tốt trên điện thoại. Mỗi câu ca dao, tục ngữ có giải nghĩa từ khó, tình huống dùng, dị bản, và **ghi rõ lời lấy từ đâu và đã được kiểm đến mức nào**.

**Hướng hiện tại — "Trung thực hơn hoàn hảo"** (D-039, chuyển WordPress D-054..D-056): xuất bản sớm, nội dung dày dần. Không chặn đăng vì lý do biên tập; nguồn không bắt buộc nhưng không được bịa. Site Astro cũ đã gỡ khỏi repo (D-071).

## Đọc trước khi làm

1. `docs/00-tai-lieu-nen.md` — luôn đọc.
2. `docs/02-trang-thai-hien-tai.md` — dự án đang ở đâu.
3. `docs/03-quy-trinh-thuc-thi.md` — nếu người dùng không giao task cụ thể, tự chọn việc theo quy trình này.
4. `docs/22-backlog-trien-khai.md` — task, phụ thuộc, trạng thái, tiêu chí chấp nhận.
5. `docs/01-nhat-ky-quyet-dinh.md` — nếu task chạm tới Q-xxx còn mở: **dừng và hỏi**.

| Loại việc | Tài liệu |
|---|---|
| **WordPress** (plugin, theme, nguồn tự động, kho, chuyển đổi, công cụ) | `docs/24-wordpress.md` |
| **Gói nâng cấp 25/10/2026** (giao diện, nguồn nội dung, chống trùng, lượt đọc, người đọc góp phần, giọng đọc) | `docs/25-nang-cap-giao-dien-noi-dung.md` |
| Giao diện, câu chữ, màu, phông | `docs/20-dac-ta-giao-dien.md` (phần WordPress ở cuối) |
| Nguồn, bản quyền, thu thập từ web, ghi công | `docs/12-chinh-sach-nguon.md` |
| Hướng dẫn cho người biên soạn, người đọc | `docs/huong-dan/` |
| Kiểm thử | `docs/24-wordpress.md` mục 6 (plugin) và `scripts/` |
| Lưu trữ (đóng góp, ghi âm, cố vấn — sau Mốc C) | `docs/luu-tru/` (đọc README của thư mục) |

## Chế độ tự thực thi

Nếu người dùng không giao task cụ thể mà yêu cầu tiếp tục dự án, **không hỏi người dùng phải làm task nào**. Làm theo `docs/03-quy-trinh-thuc-thi.md`:

- chạy `npm run viec-tiep-theo` để biết task READY tiếp theo;
- đọc tiêu chí chấp nhận trong `docs/22`;
- tự phân rã task nếu cần nhưng không mở rộng scope;
- cập nhật trạng thái sau khi làm.

Một Q-xxx đang mở chỉ chặn task thực sự phụ thuộc vào Q đó; không được dùng một Q không liên quan làm lý do dừng toàn dự án.

## Ba lằn ranh (D-040, nới bởi D-054)

1. **AI không bao giờ viết** lời, dị bản, nguồn, số trang, vùng, địa danh, tác giả, niên đại. Lời ca dao vào site bằng hai đường: **đường thu thập máy** (chép nguyên văn từ một nguồn có địa chỉ, đúng nguồn đã bật) hoặc **người biên soạn gõ**. AI không tự viết lời để lấp chỗ trống.
2. **Nguồn ghi đúng sự thật, không bịa.** Lời ca dao, tục ngữ không ghi nguồn dưới lời. "Nguồn: …" chỉ hiện dưới chú thích, lời giải chép từ nguồn khi người biên soạn bật (mặc định tắt từ D-068; các nguồn tự động được ghi nhận ở trang Chính sách thu thập dữ liệu) hoặc khi người biên soạn tự điền. Không ai được ghi một nguồn, trang, ngày truy cập mà mình không thấy tận mắt. Ghi công theo giấy phép mở (Commons, Wikipedia CC BY-SA) luôn ở ngay tại chỗ.
3. **Dữ liệu cá nhân và ghi âm phải có đồng ý.** Không thông tin cá nhân trên site hay trong repo; ghi âm cần hồ sơ đồng ý.

## Human Gate

Chỉ trả về **HUMAN DECISION REQUIRED** khi gặp một trong các trường hợp:

1. **H1 — Q-xxx / mô hình dữ liệu:** phải chốt câu hỏi mở, hoặc đổi cấu trúc dữ liệu lớn (loại bài, trường, bảng).
2. **H2 — sự thật biên tập:** xác nhận lời, nguồn, vùng, diễn giải; đăng bài thay người biên soạn; đặt dấu "đã duyệt" cho nội dung do máy soạn; đặt dấu "có hỗ trợ AI" thay người biên soạn; xác nhận Wikidata thay người biên soạn.
3. **H3 — quyền:** đổi giấy phép hay quyền sử dụng của một nguồn; quyết định chép chú thích nguyên văn từ một nguồn mới; đồng ý ghi âm.
4. **H4 — hướng UX lớn:** thay đổi đáng kể định hướng thẩm mỹ hoặc hành vi đã chốt.
5. **H5 — mở công khai:** quyết định bỏ chặn lập chỉ mục và mở site cho công chúng (T-053). Chuyển giữa các mốc A → B → C **không** phải Human Gate.
6. **H6 — dữ liệu gốc:** xóa, gộp, đổi đường dẫn của bài, mục từ, nguồn đã đăng; đổi tên miền chính.

Ngoài các Human Gate này, agent phải tự quyết các chi tiết kỹ thuật trong phạm vi đã chốt thay vì hỏi người dùng các câu nhỏ.

## AI được làm gì

**Đề xuất, không bao giờ lặng lẽ sửa.** AI được: trích xuất, phân loại, so sánh, gợi ý, gắn cờ trùng, soạn bản nháp (luôn ở trạng thái Chờ duyệt, không bao giờ Đăng), viết mã và kiểm thử theo task, chạy đường thu thập theo `docs/24` mục 7, dựng công cụ kiểm tra và giao diện giúp người biên soạn quyết nhanh hơn. Mọi quyết định nội dung đi qua người biên soạn.

## KHÔNG BAO GIỜ

1. **Không tạo, đoán hay "điền cho đủ" nội dung văn hóa:** lời ca dao, tục ngữ, dị bản, vùng, địa danh, người kể, tên sách, nhà xuất bản, năm, số trang, giải nghĩa khẳng định mà không có căn cứ, điển tích.
2. **Không đưa văn bản do máy soạn lên site như nội dung đã duyệt.** Nghĩa, diễn giải, mô tả tình huống, "Hợp khi…", giải nghĩa mục từ do máy soạn phải ở trạng thái Chờ duyệt và chỉ hiện sau khi người biên soạn duyệt. Đừng bật dấu "đã duyệt" hay "có hỗ trợ AI" thay người biên soạn.
3. **Không sửa lời** của dị bản hay bài đã có, trừ khi task nói rõ và đưa kèm nguồn (trang in hoặc URL).
4. **Không tự đăng bài** (trạng thái Đăng / công khai), không tự đổi nguồn sang "được phép dùng", không tự chọn quyền sử dụng. Chỉ người biên soạn làm.
5. **Không xóa, gộp, đổi đường dẫn** bài, dị bản, nguồn, mục từ đã có — kể cả khi công cụ báo nghi trùng. Báo lại để người biên soạn quyết (chỉ dùng công cụ gộp khi người biên soạn đã bấm).
6. **Không thêm giá trị vào bộ chủ đề, vùng của dự án** nếu task không yêu cầu rõ (bảng ánh xạ trong docs/25 là nguồn).
7. **Không làm yếu, tắt hay bỏ qua kiểm thử** để qua cổng, và không hạ một kiểm thử từ bắt buộc xuống không chặn khi không có quyết định (D-xxx).
8. **Không thêm:** cookie, theo dõi, quảng cáo, đăng nhập hay tài khoản người đọc, dịch vụ bên thứ ba khi người đọc chưa mở (ví dụ bản đồ ngoài), thu IP. Đo lường chỉ các sự kiện đã quyết trong docs/21 mục 7 và D-047/D-069.
9. **Không đưa thông tin cá nhân** (email, số điện thoại, tên thật của người chọn ẩn danh, địa chỉ) vào bất kỳ tệp nào trong repo hay vào nội dung site. Email người dùng không được gửi đi đâu.
10. **Không dùng từ cấm trong giao diện:** "bản gốc", "bản đúng", "bản chuẩn", "bản sai", "AI giải thích", "câu hay nhất". Bản đại diện gọi là **"Bản tham chiếu"**.
11. **Không chép lời ca dao từ các bản ý tưởng gốc hay từ trí nhớ** vào site. Lời chỉ đến từ đường thu thập hoặc người biên soạn.
12. **Không tự chọn phương án cho câu hỏi còn mở** (Q-xxx), kể cả phương án khuyến nghị.
13. **Không chép chú thích của nguồn mà không ghi công.** Chép chú thích, chú giải kèm dòng nguồn trỏ đúng trang và giấy phép là được phép (D-055, D-068). Chỉ chép khi nguồn bật "Chép chú thích".
14. **Không đưa kho thô** (dữ liệu thu thập từ nguồn web) vào repo công khai. Kho thô nằm trong repo riêng tư `truonganhquyen/ca-dao-kho-tho` hoặc trong cơ sở dữ liệu WordPress (D-045, D-056). Repo này công khai: không đưa lời, chú thích, số liệu, ảnh chụp kho thô vào đây.
15. **Không đổi cấu hình máy chủ hay đăng ký dịch vụ** (DNS, FlashPanel, Cloudflare, GitHub OAuth) thay người vận hành. Không ghi lên máy chủ khi người sáng lập chưa đồng ý cho lần đó.

## LUÔN LUÔN

- Chạy `npm run kiem-tra` và `npm run test:wordpress` trước khi kết thúc. Khi thêm kiểm thử mới, mở rộng chính lệnh này thay vì tạo đường kiểm tra rời rạc.
- Mỗi lần đổi plugin hoặc theme: tăng số phiên bản (`CADAO_PHIEN_BAN` và dòng `Version:` của plugin; dòng `Version:` của theme). Máy chủ dùng số này để biết đã cập nhật.
- Lưu văn bản UTF-8, Unicode dựng sẵn (NFC), xuống dòng LF (`.gitattributes` giữ LF trên Windows).
- Giữ JavaScript phía trình duyệt tối thiểu; trang đọc được khi tắt JavaScript. Tệp JavaScript của trang bài dưới ngân sách 21 KB.
- Giữ trợ năng: bàn phím, focus rõ, nhãn không chỉ dựa vào màu, vùng chạm ≥ 44 px. `npm run quet:wordpress` (WCAG 2.2 AA) phải đạt.
- Ghi nguồn gốc cho mọi lời lấy từ web: địa chỉ đúng trang, ngày truy cập, bản lưu trữ khi có (R22 trong docs/12).
- Nhiều PR mở cùng lúc: xếp chồng nhánh (D-053); số D mới = lớn nhất trên `main` và mọi PR đang mở + 1.
- Một nhánh/PR gom các task liên quan của cùng một đợt; agent tự gộp khi kiểm tra và CI đạt (D-066). Mô tả PR gồm: đã làm, **không** làm, câu hỏi còn lại, **link bản xem trước** (`npm run xem-truoc:wordpress`).
- Commit có dòng `AI-Agent: <công cụ/mô hình>` ở cuối nội dung commit (D-032).

## Khi không chắc

- Nếu liên quan Human Gate: dừng và hỏi người sáng lập, ghi rõ `HUMAN DECISION REQUIRED`.
- Nếu chỉ là chi tiết kỹ thuật nhỏ trong phạm vi đã chốt: tự chọn phương án đơn giản nhất, ghi giả định trong PR và tiếp tục.
- Tuyệt đối không biến sự không chắc chắn về dữ liệu văn hóa thành phỏng đoán. Một câu hỏi đúng chỗ tốt hơn một nguồn bịa.

## Lệnh

```bash
npm ci                          # cài công cụ (axe-core, puppeteer-core, @playwright/test) — Node ≥ 22.12
npm run kiem-tra                # cổng nhanh: kiểm thử nhất quán backlog/trạng thái
npm run test:wordpress          # kiểm thử plugin WordPress trong Playground (cần Node ≥ 24.18; CI chạy job riêng)
npm run test:anh                # vẽ ảnh chia sẻ bằng PHP thật (GD + FreeType); không có PHP thì bỏ qua tại máy, CI luôn chạy
npm run quet:wordpress          # quét trợ năng theme WordPress (WCAG 2.2 AA) và thử trên trình duyệt; -- --anh <thư-mục> để lưu ảnh
npm run xem-truoc:wordpress     # in link bản xem trước WordPress cho nhánh đã đẩy — dán vào PR
npm run viec-tiep-theo          # đề xuất task kỹ thuật READY tiếp theo
npm run kiem-site-that          # phongdao.com có đang chạy đúng phiên bản của main không (chỉ đọc; T-129)
npm run e2e                     # khảo sát E2E site thật, không ghi (tests/e2e/README.md); e2e:quan-tri chỉ tạo bản ghi [E2E-TEST] rồi xóa
```

## Điều kiện hoàn tất task

Một task phần mềm chỉ được coi là sẵn sàng review khi:

- mọi tiêu chí chấp nhận liên quan đã đạt;
- `npm run kiem-tra`, `npm run test:wordpress` và `npm run quet:wordpress` đạt;
- agent đã xem lại `git diff`;
- tài liệu, trạng thái và backlog phản ánh đúng thực tế;
- PR nêu rõ đã làm, không làm, kết quả kiểm tra, link bản xem trước và Human Gate nếu có.

Khi implementation, acceptance criteria và kiểm thử đã đạt, branch có thể ghi task `DONE` trước khi mở PR; GitHub PR là trạng thái review thực tế. Nếu PR không được gộp, thay đổi `DONE` không đi vào `main`.

## Quy ước

- Tài liệu, câu chữ giao diện, thông báo lỗi: **tiếng Việt**; văn bản dự án tự viết đặt dấu theo SGK — hoà, thuỷ (D-038).
- Mã định danh, tên trường, mã từ vựng: chữ thường không dấu, gạch dưới (`ban_dai_dien`, `xac_thuc_dan_gian`).
- Commit: `T-xxx: <mô tả ngắn>`.
- Người sáng lập không dùng mã task trong lời nói chuyện: khi báo cáo, mô tả việc bằng tiếng Việt thường.
