# AGENTS.md — Hướng dẫn cho AI agent

Tệp này dành cho mọi AI agent làm việc trong kho mã (Claude Code, Codex, Cursor, Copilot…). Dev là người cũng nên đọc: đây là luật chơi của dự án.

## Dự án là gì

**Kho tàng Ca dao Tục ngữ Việt Nam**: một website tĩnh, phi lợi nhuận, không quảng cáo, đọc tốt trên điện thoại; mỗi câu ca dao tục ngữ có giải nghĩa từ khó, tình huống dùng, và **ghi rõ lời lấy từ đâu, đã được kiểm đến mức nào**. Một người vận hành, **dưới 10 giờ/tuần** (lập kế hoạch theo ~5 giờ), không làm việc trực tiếp với Git/YAML — người sáng lập biên soạn qua CMS và duyệt thay đổi bằng cách xem bản xem trước, **không đọc mã** (D-035, D-036).

**Hướng v2 — "Trung thực hơn hoàn hảo"** (D-039): xuất bản sớm, nội dung cải tiến dần. **Từ 24/10/2026 (D-054..D-056):** làm dày nội dung là ưu tiên — không chặn đăng vì lý do biên tập, nguồn không bắt buộc (bài tự động tự ghi "Nguồn: …"), chép chú thích có ghi công được phép; và **chuyển sang WordPress** (`docs/24-wordpress.md`): người sáng lập đăng và duyệt bài trong `wp-admin`, nguồn tự động cấu hình trong trang quản trị, bài tự động vào **Chờ duyệt**. Site Astro hiện tại chạy tới khi WordPress lên (T-076). Vẫn giữ các lằn ranh dưới đây.

## Đọc trước khi làm

1. `docs/00-tai-lieu-nen.md` — luôn đọc.
2. `docs/02-trang-thai-hien-tai.md` + `du-an/trang-thai.json` — dự án đang ở đâu.
3. `docs/03-quy-trinh-thuc-thi.md` — nếu người dùng không giao task cụ thể, tự chọn việc theo quy trình này.
4. `docs/22-backlog-trien-khai.md` — task, phụ thuộc, trạng thái, tiêu chí chấp nhận, mục "Đọc"; mục 2 là điều kiện xong của từng mốc.
5. `docs/01-nhat-ky-quyet-dinh.md` — nếu task chạm tới Q-xxx còn mở: **dừng và hỏi**.

| Loại việc | Tài liệu |
|---|---|
| Dữ liệu, schema, công cụ kiểm tra | `docs/10-dac-ta-du-lieu.md` |
| Biên soạn, soạn nháp AI | `docs/11-huong-dan-bien-soan.md`, `templates/prompt-soan-nhap.md` |
| Nguồn, bản quyền, thu thập từ web | `docs/12-chinh-sach-nguon.md` |
| Giao diện, câu chữ | `docs/20-dac-ta-giao-dien.md` |
| Kỹ thuật, build, CMS, kho thô, tìm kiếm | `docs/21-kien-truc-ky-thuat.md` (site Astro hiện tại) |
| **WordPress** (plugin, theme, nguồn tự động, chuyển đổi) | `docs/24-wordpress.md` |
| **Gói nâng cấp 25/10/2026** (giao diện, nguồn nội dung, chống trùng, từng thể loại, lượt đọc, người đọc góp phần, giọng đọc) | `docs/25-nang-cap-giao-dien-noi-dung.md` |
| Kiểm thử | `docs/23-ke-hoach-kiem-thu.md` |
| Đóng góp, ghi âm, cố vấn, vận hành (sau Mốc C) | `docs/luu-tru/` (đọc README của thư mục) |

## Chế độ tự thực thi

Nếu người dùng không giao task cụ thể mà yêu cầu tiếp tục dự án, **không hỏi người dùng phải làm task nào**. Làm theo `docs/03-quy-trinh-thuc-thi.md`:

- xác định giai đoạn (mốc) hiện tại;
- đọc điều kiện xong của mốc ở `docs/22` mục 2;
- chọn task `READY` dành cho A/N+A, ưu tiên task gỡ phụ thuộc và tiến tới mốc;
- tự phân rã task nếu cần nhưng không mở rộng scope;
- cập nhật trạng thái sau khi làm.

Một Q-xxx đang mở chỉ chặn task thực sự phụ thuộc/chạm Q đó; không được dùng một Q không liên quan làm lý do dừng toàn dự án.

## Ba lằn ranh (D-040, nới bởi D-054)

1. **AI không bao giờ viết** lời, dị bản, nguồn, số trang, vùng, địa danh, tác giả, niên đại. Lời chỉ vào `content/` bằng hai đường: **trích nguyên văn bằng máy** từ một nguồn có URL (đường ống T-039, không qua AI), hoặc **do người biên soạn nhập**.
2. **Nguồn ghi đúng sự thật, không bịa.** Lời ca dao, tục ngữ không ghi nguồn; "Nguồn: …" chỉ hiện dưới chú thích, lời giải chép từ nguồn (tùy chọn theo nguồn) hoặc khi người viết tự điền (D-057). Không ai được ghi một nguồn, số trang, ngày truy cập mà mình không thấy tận mắt — kể cả CMS tự điền mặc định (D-052). Dòng "Nguồn: …" dưới chú thích chép luôn trỏ đúng trang đã lấy.
3. **Dữ liệu cá nhân và ghi âm phải có đồng ý.** Không thông tin cá nhân trong kho; ghi âm cần hồ sơ đồng ý.

## Human Gate

Chỉ trả về **HUMAN DECISION REQUIRED** khi gặp một trong các trường hợp:

1. **H1 — Q-xxx/domain:** phải chốt câu hỏi mở hoặc thay đổi mô hình miền/schema lớn.
2. **H2 — sự thật biên tập:** xác nhận lời/nguồn/vùng/diễn giải; đặt `nhap_ai_da_duyet: true`; đặt `da_xac_minh_ban_in: true`; đưa bài sang `cong_khai`.
3. **H3 — quyền:** đổi `ban_quyen` của nguồn, quyết định dùng chú thích nguyên văn, đồng ý ghi âm.
4. **H4 — hướng UX lớn:** thay đổi đáng kể định hướng thẩm mỹ hoặc hành vi đã chốt.
5. **H5 — mở công khai:** quyết định mở site (T-053). Chuyển giữa các mốc A → B → C **không** phải Human Gate (`docs/22` mục 2).
6. **H6 — dữ liệu canonical:** xóa, gộp, đổi mã hoặc sửa phá hủy hồ sơ canonical.

Ngoài các Human Gate này, agent phải tự quyết các chi tiết kỹ thuật trong phạm vi source-of-truth thay vì hỏi người dùng các câu nhỏ.

## AI được làm gì

**Đề xuất, không bao giờ lặng lẽ sửa.** AI được: trích xuất, phân loại, so sánh, gợi ý, gắn cờ, soạn nháp (luôn có `NHAP_AI`), viết mã và kiểm thử theo task, **chạy đường ống thu thập** theo `docs/21` mục 16 và `docs/12` mục 4. Thay đổi về mã đi qua PR; nội dung người sáng lập sửa trong CMS ghi thẳng vào `main` (D-036).

## KHÔNG BAO GIỜ

1. **Không tạo, đoán hay "điền cho đủ" nội dung văn hóa:** lời ca dao/tục ngữ, dị bản, vùng, địa danh, người kể, tên sách, NXB, năm, số trang, giải nghĩa có tính khẳng định mà không có căn cứ, điển tích, bối cảnh diễn xướng, luật bằng trắc. Thiếu thì ghi `CAN_BO_SUNG` và báo lại.
2. **Không viết văn bản nội dung mà không gắn tiền tố `NHAP_AI`.** Mọi chuỗi bạn viết vào `content/` (nghĩa, diễn giải, mô tả tình huống, "Hợp khi…", giải nghĩa mục từ) phải bắt đầu bằng `NHAP_AI `. Chỉ người biên soạn đặt `nhap_ai_da_duyet: true` (D-042).
3. **Không sửa trường `loi`** của dị bản đã có, trừ khi task nói rõ và đưa kèm nguồn (trang in hoặc URL).
4. **Không tự đặt** `trang_thai: cong_khai`, `nhap_ai_da_duyet: true`, `da_xac_minh_ban_in: true`, `cach_dung: trich_nguyen_van`; không đổi `ban_quyen` của nguồn sang `cong_cong`/`duoc_phep`. Chỉ người biên soạn làm.
5. **Không xóa, gộp, đổi mã** tác phẩm, dị bản, nguồn, mục từ đã có — kể cả khi công cụ báo nghi trùng (R19). Báo lại để người biên soạn quyết.
6. **Không thêm giá trị vào `taxonomy/tu-vung-kiem-soat.yaml`** nếu task không yêu cầu rõ.
7. **Không làm yếu, tắt hay bỏ qua kiểm tra dữ liệu** để build qua, và không hạ một quy tắc từ LỖI xuống NHẮC khi không có D-xxx. Build lỗi thì sửa dữ liệu hoặc báo lại.
8. **Không thêm:** cơ sở dữ liệu, backend (ngoài Worker nhỏ cho đăng nhập CMS, D-036), tài khoản/phân quyền người đọc, Elasticsearch hay cơ sở dữ liệu vector, cookie, theo dõi, quảng cáo, TTS/giọng tổng hợp, chatbot, tính năng AI sinh văn bản công khai, dịch vụ trả phí, React/shadcn/thư viện thành phần ở trang công khai, thư viện nặng — trừ khi task cho phép rõ. CMS là Sveltia (D-036); không thêm CMS khác.
9. **Không đưa thông tin cá nhân** (email, số điện thoại, tên thật của người chọn ẩn danh, địa chỉ) vào bất kỳ tệp nào trong kho.
10. **Không dùng từ cấm trong giao diện:** "bản gốc", "bản đúng", "bản chuẩn", "bản sai", "AI giải thích", "câu hay nhất". Bản đại diện gọi là **"Bản tham chiếu"**.
11. **Không chép lời ca dao từ các bản ý tưởng gốc hay từ trí nhớ vào `content/`.** Lời chỉ đến từ đường ống thu thập hoặc người biên soạn.
12. **Không tự chọn phương án cho câu hỏi còn mở** (Q-xxx), kể cả phương án khuyến nghị.
13. **Không chép chú thích của nguồn mà không ghi công.** Chép chú thích, chú giải kèm "Theo …" và liên kết về trang gốc là được phép (D-055 — người sáng lập quyết, đã biết rủi ro bản quyền); chỉ chép từ nguồn có công tắc "Chép chú thích" đang bật. Không tải ảnh; không sao chép cách phân loại, bộ thẻ của nguồn ra trang.
14. **Không đưa kho thô** (dữ liệu thu thập từ nguồn web) vào repo công khai. Kho thô nằm trong repo riêng tư `truonganhquyen/ca-dao-kho-tho` (D-045) hoặc trong cơ sở dữ liệu WordPress (D-056). `content/` là công khai — kể cả bài nháp.

## LUÔN LUÔN

- Chạy `npm run kiem-tra` trước khi kết thúc. Khi Astro/test mới xuất hiện, mở rộng chính lệnh này thay vì tạo đường kiểm tra rời rạc.
- Lưu văn bản UTF-8, Unicode dựng sẵn (NFC), xuống dòng LF (`.gitattributes` giữ LF trên Windows).
- Giữ JavaScript phía trình duyệt tối thiểu; trang đọc được khi tắt JavaScript.
- Giữ trợ năng: bàn phím, focus rõ, nhãn không chỉ dựa vào màu, vùng chạm ≥ 44 px.
- Đổi schema thì đổi cùng lúc: `docs/10`, `scripts/lib/schema.mjs` (hình dạng dữ liệu; chạy `npm run sinh:schema` để sinh lại Zod, kiểu từ vựng, cấu hình CMS), `scripts/kiem-tra-du-lieu.mjs` nếu chạm quy tắc, `scripts/lib/suy-ra.mjs` nếu chạm dữ liệu suy ra, `templates/`, fixture trong `tests/fixtures/`, và ghi D-xxx vào `docs/01`.
- Dữ liệu suy ra (nhãn, dị bản hiển thị, chuỗi hiển thị, noindex) lấy từ `scripts/lib/suy-ra.mjs`; vị trí chú giải lấy từ `phanGiaiNeo()` ở `scripts/lib/neo-chu-giai.mjs`. Không viết bản tính thứ hai.
- Ghi nguồn gốc cho mọi lời lấy từ web: `url` của đúng trang, `ngay_truy_cap`, `ban_luu_tru` (R22).
- Nhiều PR mở cùng lúc: xếp chồng nhánh theo `docs/03` mục 7.1 (không tách song song từ `main`); số D mới = lớn nhất trên `main` và mọi PR đang mở + 1.
- Một nhánh/PR gom các task liên quan của cùng một đợt; agent tự gộp khi kiểm tra và CI đạt (D-066). Mô tả PR gồm: đã làm, **không** làm, câu hỏi còn lại, **link bản xem trước**.
- Commit của bạn có dòng `AI-Agent: <công cụ/mô hình>` ở cuối nội dung commit (D-032).
- Khai chú giải bằng `cum_tu` (gõ đúng như trong lời); chỉ thêm `dong`/`lan` khi công cụ báo mơ hồ.

## Khi không chắc

- Nếu liên quan Human Gate: dừng và hỏi người sáng lập, ghi rõ `HUMAN DECISION REQUIRED`.
- Nếu chỉ là chi tiết kỹ thuật nhỏ trong phạm vi đã chốt: tự chọn phương án đơn giản nhất, ghi giả định trong PR và tiếp tục.
- Tuyệt đối không biến sự không chắc chắn về dữ liệu văn hóa thành phỏng đoán. Một câu hỏi đúng chỗ tốt hơn một nguồn bịa.

## Lệnh

```bash
npm install            # cài yaml, Astro, Tailwind (Node ≥ 22.12)
npm run dev            # xem website trên máy
npm run build          # kiểm dữ liệu rồi astro build (Cloudflare chạy lệnh này)
npm run trang-thai     # xem giai đoạn, mốc, Human Gate và số bài
npm run viec-tiep-theo # đề xuất task kỹ thuật READY tiếp theo
npm run kiem-tra       # cổng kiểm tra bắt buộc: dữ liệu + fixture + nhất quán + tệp sinh + astro check + build + liên kết + trang + trợ năng
npm run test:wordpress # kiểm thử plugin WordPress trong Playground (cần Node ≥ 24.18; CI chạy job riêng)
npm run quet:wordpress # quét trợ năng theme WordPress (WCAG 2.2 AA) trên bài mẫu tổng hợp; -- --anh <thư-mục> để lưu ảnh chụp
npm run xem-truoc:wordpress # in link bản xem trước WordPress (Playground trên trình duyệt) cho nhánh đã đẩy — dán vào PR
npm run sinh:schema    # sinh lại Zod, kiểu từ vựng, cấu hình CMS sau khi sửa schema.mjs hoặc từ vựng
npm run check:data     # kiểm tra dữ liệu theo R01–R22 (LỖI chặn, NHẮC không chặn)
npm run test:fixtures  # kiểm thử chính công cụ kiểm tra
npm run health         # báo cáo sức khỏe dữ liệu, nợ dữ liệu (không chặn build)
```

## Điều kiện hoàn tất task

Một task phần mềm chỉ được coi là sẵn sàng review khi:

- mọi tiêu chí chấp nhận liên quan đã đạt;
- `npm run kiem-tra` đạt;
- agent đã xem lại `git diff`;
- tài liệu/trạng thái/backlog phản ánh đúng thực tế;
- PR nêu rõ đã làm, không làm, kết quả kiểm tra, link bản xem trước và Human Gate nếu có.

Khi implementation, acceptance criteria và `npm run kiem-tra` đã đạt, branch có thể ghi task `DONE` trước khi mở PR; GitHub PR là trạng thái review thực tế. Nếu PR không được gộp, thay đổi `DONE` không đi vào `main`.

## Quy ước

- Tài liệu, câu chữ giao diện, thông báo lỗi: **tiếng Việt**; văn bản dự án tự viết đặt dấu theo SGK — hoà, thuỷ (D-038).
- Mã định danh, tên trường, mã từ vựng: chữ thường không dấu, gạch dưới (`ban_dai_dien`, `xac_thuc_dan_gian`).
- Tên tệp nội dung: `<id>.yaml`.
- Commit: `T-xxx: <mô tả ngắn>`.
