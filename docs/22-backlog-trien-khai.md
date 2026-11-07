# 22 · Backlog triển khai

| | |
|---|---|
| Phiên bản | 2.6 · 05/11/2026 (kế hoạch cải tiến sau review T-173..T-183; `docs/31`, gồm nhánh quản trị `docs/30`) |
| Trạng thái | **Sống.** Cập nhật cột trạng thái ở mục 1 sau mỗi task/PR; enum: `READY`, `IN_PROGRESS`, `REVIEW`, `BLOCKED`, `DONE`, `DEFERRED` |
| Đọc khi | Nhận việc. Mỗi task ghi rõ cần đọc tài liệu nào |
| Ai làm | **N** = người sáng lập · **A** = AI agent hoặc dev · **N+A** = A làm, N duyệt và quyết định |
| Thay cho | Backlog v1 (T-001..T-304, P0-Core/P0.5, các cổng đếm hồ sơ): `docs/luu-tru/22-backlog-v1.md` |

**Cách nhận một task (cho cả người và AI agent):**
1. Nếu không được giao task cụ thể, đọc `docs/02-trang-thai-hien-tai.md`, `docs/03-quy-trinh-thuc-thi.md` và chạy `npm run viec-tiep-theo`.
2. Nhận task `READY` **hoặc task được `npm run viec-tiep-theo` suy ra là READY** vì mọi phụ thuộc đã `DONE` và Q-xxx trong cột "Chặn" đã được giải quyết.
3. Đọc các tài liệu trong "Đọc".
4. Làm trên một nhánh riêng `task/T-xxx-<mo-ta-ngan>`; một PR gom các task liên quan của cùng một đợt, agent tự gộp khi kiểm tra đạt (D-066).
5. Khi bắt đầu đổi trạng thái thành `IN_PROGRESS`. Khi implementation + tiêu chí chấp nhận + `npm run kiem-tra` đã đạt, đổi task thành `DONE` trong commit cuối của nhánh rồi mở PR.
6. Mô tả PR theo `.github/pull_request_template.md`; luôn kèm **link bản xem trước** — người sáng lập duyệt bằng mắt trên điện thoại, không đọc mã (D-035).

---

## 1. Bảng tổng

Mã task v2 bắt đầu từ T-031 để không trùng với backlog v1 đã lưu trữ.

| Task | Tên | Ai | Phụ thuộc | Chặn | Trạng thái |
|---|---|---|---|---|---|
| **Chuẩn bị · Tài khoản, tài liệu v2, thư cho nguồn** ||||||
| T-031 | Chốt gói quyết định D-035..D-047 | N | — | — | DONE |
| T-032 | Tài khoản GitHub (repo `ca-dao` công khai, `ca-dao-kho-tho` riêng tư — xong 24/10) và Cloudflare; cho Codex truy cập | N | — | — | DONE |
| T-033 | Cập nhật tài liệu, công cụ kiểm tra, mẫu theo hướng v2 | A | T-031 | — | DONE |
| T-034 | Gửi thư xin phép dùng chú thích cho Ca dao Mẹ (nên gửi trước khi mở, không chặn — D-077) | N | — | — | READY |
| **Mốc A · Chạy được** ||||||
| T-035 | Khởi tạo Astro 7 + Tailwind 4 + CI + triển khai Cloudflare (D-049) | A | T-032, T-033 | — | DONE |
| T-036 | Mô tả schema một nguồn → Zod + cấu hình CMS | A | T-035 | — | DONE |
| T-037 | CMS Sveltia: đăng nhập GitHub, form tiếng Việt, "Thêm nhanh", duyệt nháp AI | A | T-036 | — | DONE |
| T-038 | Thu thập kho thô từ cadao.me | A | T-032, T-033 | — | DONE |
| T-039 | Đường ống ứng viên: kho thô → bài nháp trong content/ (thay bởi T-071, T-073 — D-056) | A | T-036, T-038 | — | DEFERRED |
| T-040 | Trang tác phẩm đọc theo lớp, nhãn tự tính, nguồn gốc, sáng/tối, in, thanh điều hướng dưới | A | T-035 | — | DONE |
| T-070 | Áp dụng D-054..D-056: nới quy tắc, kế hoạch chuyển WordPress | A | T-037 | — | DONE |
| T-071 | Plugin WordPress `ca-dao`: loại bài, phân loại, dòng Nguồn, Nguồn tự động, kho thu thập, đổ bài Chờ duyệt | A | T-070 | — | DONE |
| T-072 | Theme WordPress `ca-dao` (theme con, giao diện theo docs/20) | A | T-071 | — | DONE |
| T-073 | Bộ đọc Ca dao Mẹ đầy đủ: dị bản, chú thích có ghi nguồn, tục ngữ, đồng dao, vè, câu đố | A | T-071 | — | DONE |
| T-074 | Chọn và mua hosting WordPress | N | — | — | DONE |
| T-075 | Cài WordPress lên hosting: plugin, sao lưu, bảo mật cơ bản (theme cài khi T-072 xong) | N+A | T-071, T-074 | — | DONE |
| T-076 | Chuyển nội dung hiện có sang WordPress, ngừng site Astro | A | T-075, T-109 | — | DONE |
| T-078 | Máy chủ tự cập nhật plugin từ `main` sau mỗi PR gộp | A | T-071 | — | DONE |
| T-077 | Áp dụng D-057: lời không ghi nguồn, "Nguồn: …" chỉ dưới chú thích đã chép (plugin 0.2.0) | A | T-071 | — | DONE |
| T-079 | Ghi gói nâng cấp 25/10/2026 vào tài liệu, lập kế hoạch (`docs/25`, D-059..D-065) | A | — | — | DONE |
| T-080 | Tách thành ngữ và tục ngữ (máy gợi ý, người duyệt) — thay bởi T-128 (gộp, D-079) | N+A | T-073 | — | DONE |
| T-081 | Đếm lượt đọc không cookie, "Đọc nhiều", thống kê trên Bảng tin | A | T-071 | — | DONE |
| T-109 | Nhận ra bài trùng: "dấu vân tay" của lời, các mức giống (D-065) | A | T-073 | — | DONE |
| T-110 | Chống trùng khi lấy từ nguồn: gắn vào bài có sẵn, hàng "Có thể trùng" | N+A | T-109 | — | DONE |
| T-111 | Báo trùng khi viết bài tay (chỉ báo, không chặn) | A | T-109 | — | DONE |
| T-112 | Lời dán vào khối Đoạn văn: máy đề xuất đưa vào khối Thơ, người biên soạn bấm chuyển | A | T-072 | — | DONE |
| T-113 | Sửa mục từ: nguồn của lời giải, ảnh riêng / ẩn ảnh Wikidata | A | T-044, T-087 | — | DONE |
| T-114 | Bỏ dòng Nguồn dưới chú thích chép, trang Chính sách thu thập dữ liệu (D-068) | A | T-073 | — | DONE |
| T-115 | Dòng số liệu dưới tiêu đề: 👁 đọc · ↗ chia sẻ · ❝ trích dẫn (D-069) | A | T-081, T-052 | — | DONE |
| T-042 | Trích dẫn 2 định dạng + chia sẻ (trên WordPress) | A | T-072 | — | DONE |
| **Mốc B · Thử nhỏ** ||||||
| T-041 | Tìm kiếm tức thì, gõ không dấu, tìm cả trong chú thích (WordPress) | N+A | T-072 | — | DONE |
| T-043 | Trang chủ mới (Câu hôm nay, Vừa bổ sung, Đọc nhiều, Bạn có biết?) + trang tình huống | A | T-072, T-081, T-093 | — | DONE |
| T-044 | Từ điển chú giải: trang từng từ, "Các bài có từ này", lọc theo vùng | A | T-073 | — | DONE |
| T-045 | Góp ý, dị bản, cảm nghĩ ngắn của người đọc; chống rác; quyền riêng tư; "Gửi số trang" | N+A | T-075 | — | DONE |
| T-046 | Trang Phương pháp, Nguồn, Về dự án | N+A | T-075 | — | DONE |
| T-047 | Bàn biên tập: gom các hàng chờ (Chờ duyệt, xác nhận, người đọc gửi) trong wp-admin | A | T-071 | — | DONE |
| T-082 | Chạm vào từ khó, nghĩa hiện ngay (lưu vị trí chú thích) | A | T-073 | — | DONE |
| T-083 | Dị bản đánh số, tô chỗ khác lời chính | A | T-072 | — | DONE |
| T-084 | Nhận thể thơ, trình bày theo thể | A | T-072 | — | DONE |
| T-085 | Công cụ đọc: cỡ chữ, sáng/tối, trình chiếu, nghe bằng giọng có sẵn của điện thoại | A | T-072 | — | DONE |
| T-086 | Gắn Wikidata: hàng chờ xác nhận, ảnh Commons có ghi tác giả | N+A | T-073 | — | DONE |
| T-087 | Chú thích địa danh: bản đồ nhỏ, ảnh, "Bài khác nhắc tới…" | A | T-082, T-086 | — | DONE |
| T-088 | Câu đố: ảnh đáp án | A | T-086 | — | DONE |
| T-089 | Tự gợi ý vùng cho bài (phương ngữ, địa danh) | N+A | T-073 | — | DONE |
| T-090 | Vè và bài dài: chia đoạn, mục lục, đọc tiếp | A | T-072 | — | DONE |
| T-091 | Bộ chủ đề riêng của dự án (gồm nhóm tục ngữ kinh nghiệm, lối hát, loại câu đố), máy gợi ý | N+A | T-073 | — | DONE |
| T-092 | "Bài gần gũi" cuối bài và trang mô-típ (mở lại D-077; bản tối giản "Đọc tiếp" đã có ở T-122) | A | T-091, T-122 | — | DONE |
| T-093 | "Bạn có biết?" ghép từ chú thích | N+A | T-073 | — | DONE |
| T-094 | Trang riêng cho từng thể loại | N+A | T-072 | — | DONE |
| T-095 | Tục ngữ – Thành ngữ: trình bày câu một dòng, chỉ mục A–Z (mở lại D-077; một mục, D-079) | A | T-128 | — | DONE |
| T-096 | Câu đố: nhóm theo đáp án và theo loại, trang "Đố vui" (mở lại D-077) — gộp vào T-150 (D-091) | A | T-091 | — | DEFERRED |
| T-097 | Bài cảm nhận, phân tích của người đọc (cần duyệt); bài có phân tích được duyệt thì lên Google | A | T-045 | — | DONE |
| T-118 | An toàn vận hành: chặn lặp lượt đọc, sự kiện phía máy chủ; chỉ tin IP Cloudflare khi đúng; gỡ thẻ phiên bản; quay lại bản trước (D-073) | A | T-081, T-052, T-078 | — | DONE |
| T-119 | Dòng "lời và mức kiểm" dưới lời, trong Tìm và trích dẫn (D-072) | A | T-072 | — | DONE |
| T-120 | Điều hướng và danh sách bài: menu, trang Tất cả A–Z có đoạn trích và bộ lọc, thanh dưới cho điện thoại (D-073) | A | T-072, T-094 | — | DONE |
| T-121 | Cuối bài gọn hơn, Tìm rõ hơn, Bàn biên tập ưu tiên việc quan trọng (D-073) | A | T-045, T-097, T-041, T-047 | — | DONE |
| T-050 | Ảnh chia sẻ tự sinh từng bài, noindex theo giá trị riêng, SEO cơ bản (kéo lên Mốc B, D-073) | A | T-076, T-119 | — | DONE |
| T-100 | Trợ giúp viết "Hiểu nhanh" (gợi ý; nháp máy chờ duyệt) (kéo lên Mốc B, D-073) | A | T-082 | — | DONE |
| T-122 | "Đọc tiếp" cuối bài, bản tối giản: 3–5 bài cùng chủ đề, vùng, thể loại (D-075) | A | T-121, T-089 | — | DONE |
| T-123 | Câu mới trùng 100% một dị bản đã ghi: máy tự gắn làm dị bản (D-074) | A | T-110 | — | DONE |
| T-124 | Khoá cửa quản trị: tắt XML-RPC, không lộ tên đăng nhập, câu báo chung, tạm khoá khi sai nhiều, `?ly_do=` → mã (D-076) | A | T-118 | — | DONE |
| T-125 | Tục ngữ, thành ngữ vào hàng Chờ duyệt: tạo bài xoay vòng theo loại; dòng "Kho đang có" (D-076) | A | T-071, T-047 | — | DONE |
| T-126 | Người đọc bớt tắc: mũi tên nhóm lọc, 30 bài/trang, 404 riêng, kết quả tìm hiện tên bài, xếp hạng chữ liền nhau (D-076) | A | T-120, T-121, T-041 | — | DONE |
| T-127 | Canonical cho trang chủ, Tất cả bài, trang phân loại (D-076) | A | T-050 | — | DONE |
| T-128 | Một mục "Tục ngữ – Thành ngữ": gộp thể loại, chuyển hướng đường dẫn cũ, bỏ trang tách loại (D-079) | A | T-080 | — | DONE |
| T-048 | Thử với 5 người trên bản xem trước V2 (`docs/27` mục 12; nên làm, không chặn mở — D-077, D-091) | N | T-156 | — | READY |
| T-129 | Kiểm tự động "site thật đúng bản": so phiên bản trên phongdao.com với `main`, báo khi lệch (D-080) | A | T-078 | — | DONE |
| T-130 | Bộ kiểm E2E chạy được ở mọi nơi (trình duyệt cài sẵn, proxy); cập nhật kỳ vọng lỗi đã sửa (D-080) | A | — | — | DONE |
| T-131 | Đối chiếu lời với sách có bản quét (Câu hát góp 1901; Tục ngữ, cổ ngữ, gia ngôn 1897): máy gợi ý, người biên soạn bấm (D-080) — gộp vào T-158 (D-091) | N+A | T-119 | — | DEFERRED |
| T-132 | Tìm chịu gõ sai một chữ, "Có phải bạn tìm …?" (L-09; D-080) — gộp vào T-144 (D-091) | A | T-126 | — | DEFERRED |
| T-133 | Bộ câu "Có trong SGK": người sáng lập đánh dấu bộ sách, lớp, bài; trang lọc, nối Trình chiếu (D-080) — gộp vào T-159 (D-091) | N+A | — | — | DEFERRED |
| T-134 | Người soát lời tình nguyện qua "Gửi số trang", ghi bút danh (sau khi mở; D-080) | N+A | T-053 | — | DEFERRED |
| T-135 | Cờ "Nghi không phải dân gian" trên WordPress (D-008, `docs/20` mục 4.1, 10; D-080) — gộp vào T-150, T-154 (D-091) | N+A | — | — | DEFERRED |
| T-136 | Khối "Biên soạn" cuối bài: bút danh trong cài đặt, liên kết Phương pháp, dữ liệu có cấu trúc (D-080) — gộp vào T-148 (D-091) | A | T-046 | — | DEFERRED |
| T-137 | Trang chủ đề, vùng "đọc liền": mỗi câu kèm Hiểu nhanh, chú thích địa danh; giới thiệu do người sáng lập viết (D-080) — gộp vào T-149 (D-091) | N+A | T-094, T-095 | — | DEFERRED |
| T-138 | 50 câu được tìm nhiều: danh sách đề xuất, nháp Hiểu nhanh theo lô 10 câu trên công cụ hiện có, hướng dẫn duyệt (phần nội dung; tiêu đề "nghĩa và cách dùng" làm ở T-151 — D-091) | N+A | T-100, T-125 | — | READY |
| **Mốc C · Mở lặng lẽ** ||||||
| T-049 | Chốt tên miền (D-070: phongdao.com) | N | — | — | DONE |
| T-051 | "Đã lưu": bài yêu thích nhớ trên máy (Câu hôm nay, Vừa bổ sung → T-043; cỡ chữ, sáng/tối, trình chiếu → T-085) | A | T-072 | — | DONE |
| T-052 | Đo lường không cookie: tìm không ra, sao chép trích dẫn, mở chú giải | A | T-076, T-081, T-041 | — | DONE |
| T-098 | Tục ngữ – Thành ngữ: nghĩa đen, nghĩa bóng, "dùng khi", đồng nghĩa (Wiktionary, CC BY-SA) — gộp vào T-147, T-154 (D-091) | N+A | T-128 | — | DEFERRED |
| T-099 | Chú giải từ sách hết bản quyền; chữ Hán Việt, điển tích của thành ngữ (làm cùng đối chiếu sách, D-091) | N+A | T-082, T-158 | — | BLOCKED |
| T-101 | Gộp bài đã đăng bị trùng: bài chính + dị bản, chuyển hướng, dồn lượt đọc và bài người đọc | N+A | T-109 | — | DONE |
| T-102 | Vè: khối "Bối cảnh" (sự kiện, năm, nơi chốn, nhân vật) (mở lại D-077) — gộp vào T-147, T-154 (D-091) | N+A | T-086 | — | DEFERRED |
| T-103 | Đồng dao: cách chơi, chế độ "Đọc cùng bé" | A | T-085 | — | DONE |
| T-104 | Bản đồ ca dao theo vùng | A | T-086, T-089 | — | DONE |
| T-105 | Giọng đọc sinh sẵn theo miền (VieNeu-TTS), phiên nghe thử 30 bài (mở lại D-077) — gộp vào T-160 (D-091) | N+A | T-085, T-089 | — | DEFERRED |
| T-106 | Giọng riêng của dự án, thu từ người đồng ý | N | T-105 | — | BLOCKED |
| T-107 | Tìm bản quét "Tục ngữ phong dao" (Nguyễn Văn Ngọc, 1928) (không chặn — D-077) | N | — | — | READY |
| T-140 | Sao lưu hằng ngày ngoài VPS đã bật (người sáng lập xác nhận; bỏ yêu cầu thử khôi phục — D-090) | N | — | — | DONE |
| T-053 | Mở công khai lặng lẽ (H5; số bài, Hiểu nhanh, thư cho Ca dao Mẹ nên có nhưng không chặn — D-077) | N | T-049..T-052, T-140, T-157 | — | READY |
| **V2 · Xây mới song song, chuyển một lần (D-082..D-091)** ||||||
| T-139 | [V2] Quyết định xây mới, tài liệu nền V2, đặc tả `docs/26` kỹ thuật, `27` sản phẩm, `28` Bàn biên tập, `29` chuyển đổi | A | — | — | DONE |
| T-165 | [V2] Bộ thiết kế UX/UI bản chốt ở `design/v2/`: bảng tím – lục, mỗi câu một dòng, 66 màn, token, đặc tả (D-092, D-093) | N+A | T-139 | — | DONE |
| T-141 | [Đợt 1] Khung V2 ở `wordpress/v2/`: plugin (tự nạp `Phongdao\`, module), theme khối trống, lệnh `test:v2`, `quet:v2`, `xem-truoc:v2`, job CI, bộ kiểm PHP thuần | A | T-139 | — | DONE |
| T-142 | [Đợt 1] Mô hình dữ liệu (D-094): loại bài (gồm hồ sơ nguồn ẩn), sáu phân loại, các phần của bài trong post meta, bảng việc, cấp chất lượng, lưu một phần có kiểm lằn ranh và nhật ký | A | T-141 | — | DONE |
| T-143 | [Đợt 1] Nguồn vào: bộ đọc Ca dao Mẹ, kho, hồ sơ nguồn, chống trùng (chuyển kèm kiểm thử), câu mới từ kho thành việc chờ, gợi ý chủ đề / vùng / mô-típ / Wikidata thành việc chờ | A | T-142 | — | READY |
| T-144 | [Đợt 1] Tìm: chỉ mục từ các phần của bài, bỏ dấu, gõ sai (gộp T-132), nhóm theo thể loại, "mong có câu này" | A | T-142 | — | READY |
| T-145 | [Đợt 1] Công cụ nhập dữ liệu người làm: kiểm kê, chạy thử, nhập, hoàn tác, "Cần xem", kiểm "trước = sau" (`docs/29`) | A | T-143 | — | READY |
| T-146 | [Đợt 2] Theme 1.0 và hệ thống thiết kế theo `design/v2/` (D-092): `theme.json`, token tím – lục sáng / tối, thang chữ và khoảng cách, đầu / chân trang, thanh thể loại, thanh dưới | A | T-141 | — | READY |
| T-147 | [Đợt 2] Trang tác phẩm theo 5 thể loại: lời, chạm từ, dòng mức kiểm, nghĩa, dị bản, chú thích, bối cảnh, đoạn, đồng dao (Đọc cùng bé, cách chơi), câu đố (nút Hiện đáp án) | A | T-142, T-146 | — | READY |
| T-148 | [Đợt 2] Thông tin tư liệu và chính sách hiện; thanh hành động (trích dẫn, chia sẻ, lưu, trình chiếu, cỡ chữ); khối Biên soạn (gộp T-136); ảnh chia sẻ | A | T-147 | — | READY |
| T-149 | [Đợt 2] Trang chủ lối vào, vai, menu và IA mới; Tất cả, thể loại, chủ đề / vùng / mô-típ đọc liền (gộp T-137); tuyển tập; Một câu ngẫu nhiên | A | T-148 | — | READY |
| T-150 | [Đợt 2] Tra cứu: trang Tìm, Từ điển và mục từ, Bản đồ, Đố vui (gộp T-096), Trò chơi có lọc, Hay bị nhầm (gộp T-135 phần hiện) | A | T-144, T-148 | — | READY |
| T-151 | [Đợt 2] Người đọc góp, Đã lưu, đo lường 5 sự kiện + lượt đọc, Đọc tiếp, SEO (L2, tiêu đề "nghĩa và cách dùng", JSON-LD, sitemap), an toàn, kiểm liên kết ra ngoài, 404 | A | T-149, T-150 | — | READY |
| T-152 | [Đợt 3] Nền biên tập (D-094): REST `phongdao/v1` cho việc và phần của bài, quyền riêng, chế độ sửa trên trang đọc (nút Sửa, tấm sửa, dải Đang sửa, `bien-tap.js`), bốn trang `/bien-tap/…` có đăng nhập, không đệm cho người đăng nhập | A | T-142 | — | READY |
| T-153 | [Đợt 3] Hộp việc: danh sách, lọc, Tuần này, Duyệt từng thẻ trên điện thoại, hoàn tác 10 giây | A | T-143, T-152 | — | READY |
| T-154 | [Đợt 3] Sửa trang bài: tấm sửa lời và đối chiếu, ý nghĩa, nghĩa tục ngữ (gộp T-098), từ khó, dị bản, theo thể loại (bối cảnh gộp T-102), phân loại, cờ nghi (gộp T-135), SGK; Lịch sử; Tác phẩm và Thêm bài có báo trùng; nạp nháp AI theo lô thành việc chờ | A | T-153 | — | READY |
| T-155 | [Đợt 3] Sửa tại chỗ trang tuyển tập, mục từ, trò chơi, mô-típ, chủ đề / vùng, thể loại; Số liệu tuần; Nguồn & cài đặt trong wp-admin (hồ sơ nguồn, nguồn tự động, kho, máy đang làm, cài đặt) | A | T-154 | — | READY |
| T-156 | [Đợt 3] E2E V2 trên bản xem trước; người sáng lập dùng thử phần biên tập trên điện thoại; chạy thử công cụ nhập trên site thật (chỉ đọc) | N+A | T-145, T-151, T-155 | — | READY |
| T-157 | [Đợt 4] Ngày chuyển (`docs/29` mục 5): PR chuyển đổi 1.0.0, nhập dữ liệu trên site thật, kiểm, người sáng lập kiểm trên điện thoại | N+A | T-156, T-140 | — | READY |
| T-158 | [Đợt 5] Đối chiếu lời với sách có bản quét, bộ đọc Wikisource, giải nghĩa sách 1897 (gộp T-131, T-099) | N+A | T-157 | — | READY |
| T-159 | [Đợt 5] Cho lớp học, bộ câu SGK, phiếu in (gộp T-133) | N+A | T-157 | — | READY |
| T-160 | [Đợt 5] Giọng đọc sinh sẵn theo miền, phiên nghe thử 30 bài, mục Nghe (gộp T-105) | N+A | T-157 | — | READY |
| T-161 | [Đợt 5] Cổng thể loại mới; hát ru – hò – lý dạng văn bản | N+A | T-157 | — | READY |
| T-162 | [Đợt 5] Truyện dân gian: mẫu trang, lớp đoạn, nhân vật; chỉ từ sách hết bản quyền người sáng lập đã kiểm | N+A | T-161 | — | READY |
| T-163 | [Đợt 5] Xuất dữ liệu mở có phiên bản (nền cho T-066) | A | T-157 | — | READY |
| T-164 | [Đợt 5] Dọn dữ liệu MVP sau ≥ 2 tuần (`docs/29` mục 6; H6, người sáng lập đồng ý) | N+A | T-157 | — | READY |
| **V2 · Nâng cấp quản trị sau chuyển đổi (D-096; `docs/30`)** ||||||
| T-166 | Chốt hướng admin chính, kế hoạch, hợp đồng thao tác và đồng bộ tài liệu | A | T-142 | — | DONE |
| T-167 | Nền wp-admin: menu theo quyền, danh sách Tác phẩm toàn kho, bộ lọc, phân trang, số đếm đúng, bản xem trước màn admin | A | T-166 | — | READY |
| T-168 | Hộp việc trong wp-admin: mọi trạng thái, tìm/lọc/phân trang, quyết một thẻ dùng nghiệp vụ hiện có | A | T-167, T-176, T-177 | — | BLOCKED |
| T-169 | Trang sửa tác phẩm có cấu trúc: phần, nguồn gốc, việc liên quan, xem trước; dùng chung đường kiểm/lưu | A | T-167 | — | BLOCKED |
| T-170 | Lịch sử và khôi phục: so trước/sau, phiên bản/xung đột, nguồn và nhật ký quyết việc | A | T-168, T-169 | — | BLOCKED |
| T-171 | Xử lý lô: duyệt gợi ý, sửa phân loại/nhiều phần, ẩn/thùng rác, đổi trạng thái việc; xem trước và kết quả từng mục | A | T-170 | — | BLOCKED |
| T-172 | Dùng thử admin trên máy tính/điện thoại, kiểm tương đương, chuyển lối vào `/bien-tap/…` an toàn | N+A | T-171 | — | BLOCKED |
| **V2 · Cải tiến tổng thể sau review (`docs/31`)** ||||||
| T-173 | Lập lộ trình chung sau review, đối chiếu toàn bộ đề xuất và đồng bộ tài liệu/backlog | A | T-166 | — | DONE |
| T-174 | Đóng lỗi trang đọc E2E: Bản đồ, ảnh, câu đố thiếu Ý nghĩa, tràn ngang, trang Quyền riêng tư | A | T-173 | — | IN_PROGRESS |
| T-175 | Gia cố cập nhật và phục hồi V2, diễn tập rollback trên bản sao, kiểm đường kích hoạt CI | A | T-173 | — | DONE |
| T-176 | Sửa Hộp việc hiện chạy: thẻ cuối, số đếm/phân trang, Để sau, Hoàn tác và thao tác bàn phím | A | T-173 | — | IN_PROGRESS |
| T-177 | Chặn hai quyết định đồng thời, bảo toàn cập nhật bài/việc/nhật ký khi ghi lỗi hoặc thử lại | A | T-173 | — | IN_PROGRESS |
| T-178 | Đối soát số bài, dữ liệu sau chuyển và backlog với site thật; ghi bằng chứng sai khác | A | T-173 | — | IN_PROGRESS |
| T-179 | Hồ sơ nguồn và nội dung máy đưa vào: căn cứ, lịch sử, quyền và công cụ sửa/ẩn theo phần | A | T-169, T-177 | — | BLOCKED |
| T-180 | Bàn làm việc tuần, danh sách ngoại lệ dữ liệu và thử 20–30 bài nội dung chuyên sâu | N+A | T-176, T-178 | — | BLOCKED |
| T-181 | Đo và sửa độ đầy đủ/thứ tự tìm kiếm khi kho lớn, giữ tìm không dấu | A | T-173 | — | READY |
| T-182 | Đo và tối ưu nghẽn Bản đồ, Số liệu tuần, ảnh và truy vấn dữ liệu lớn | A | T-174, T-181 | — | BLOCKED |
| T-183 | Thử nhỏ lối đọc theo chủ đề, bài viết dài và công cụ giáo viên với dữ liệu đã duyệt | N+A | T-180 | — | BLOCKED |
| **Sau · Cải tiến liên tục** ||||||
| T-060 | Thẻ chia sẻ dạng ảnh (2 mẫu × 2 khổ) — gộp vào T-050 (D-059) | A | T-050 | — | DEFERRED |
| T-061 | Thứ tự tình huống theo dịp | A | T-043 | — | DEFERRED |
| T-062 | Bộ bài cho tiết học từ "Đã lưu", in cả bộ | A | T-051 | — | DEFERRED |
| T-063 | So sánh dị bản | A | T-040 | — | DEFERRED |
| T-064 | Tìm kiếm mở rộng sang kho thô ("xem tại Ca dao Mẹ") | A | T-038, T-041 | — | DEFERRED |
| T-065 | Đọc ngoại tuyến, thêm vào màn hình chính | A | T-040 | — | DEFERRED |
| T-066 | Bản phát hành dữ liệu mở + DOI | A | T-053 | — | DEFERRED |
| T-067 | Thử nghiệm ghi âm cộng đồng | N | T-053 | — | DEFERRED |
| T-068 | Tìm theo ý (ngữ nghĩa), khi qua cổng recall@5 | A | T-041 | — | DEFERRED |
| T-069 | Mời hội đồng cố vấn | N | T-053 | — | DEFERRED |
| T-108 | Gắn cờ (không chặn) bài có từ tục; bỏ ô lọc thô tục của nguồn tự động (ý 21, để sau — D-060) | A | T-071 | — | DEFERRED |

Không monorepo, không công cụ quản trị ngoài WordPress. Bảng việc và phần biên tập của V2 (sửa trên trang đọc, không React) nằm trong plugin `ca-dao` (D-083, D-094). Task `DEFERRED` chỉ mở khi người sáng lập chọn ở nhịp hằng tháng sau Mốc C; task `DEFERRED` có ghi "gộp vào T-xxx" thì không mở lại — việc đó làm trong task V2 tương ứng (D-091).

**Nhóm V2** (`giai_doan_song_song` trong `du-an/trang-thai.json`): `npm run viec-tiep-theo` xét task của mốc hiện tại trước, rồi tới task V2. Mã V2 nằm ở `wordpress/v2/`, máy chủ chưa lấy tới ngày chuyển (T-157); **MVP đóng băng tính năng** — chỉ sửa lỗi bảo mật, mất dữ liệu (D-083). `docs/26`–`29` là đặc tả đã duyệt một lần cho đợt 1–4; đổi điều đã chốt thì ghi D-xxx (D-091).

## 2. Mốc và điều kiện xong

Thay cho các cổng đếm hồ sơ của v1 (D-044). Agent được tự chuyển `giai_doan` trong `du-an/trang-thai.json` sang mốc kế tiếp khi mọi task A của mốc hiện tại đã `DONE` và điều kiện dưới đây đạt; **riêng mở công khai (T-053) là Human Gate H5.**

| Mốc | Xong khi | Ước tính |
|---|---|---|
| **Chuẩn bị** | Gói D-035..D-047 nằm trong repo (T-033); có repo GitHub công khai, repo riêng tư cho kho thô và tài khoản Cloudflare (T-032) | Tuần cuối 10/2026 |
| **A · Chạy được** | (D-056) WordPress chạy trên hosting thật; người sáng lập đăng một bài thủ công từ điện thoại trong vài phút; nguồn tự động Ca dao Mẹ (cấu hình trong trang quản trị) đổ bài vào **Chờ duyệt** mỗi ngày, duyệt là lên trang; trang bài hiện lời, dị bản, chú thích (kèm "Nguồn: …" khi chép từ nguồn); nội dung cũ đã chuyển, site Astro ngừng | Cuối 11/2026 |
| **B · Thử nhỏ** | 30–50 bài trên bản xem trước; trang tình huống, từ điển, form góp ý, trang Phương pháp chạy; đọc dễ hơn (chạm từ khó, dị bản tô chỗ khác, thể thơ, công cụ đọc), tìm kiếm, trang thể loại, cảm nghĩ và bài phân tích của người đọc (`docs/25` mục 6, đợt 2–4); dòng mức kiểm dưới lời (D-072), điều hướng và danh sách bài, SEO cơ bản (D-073: T-118..T-121, T-050, T-100); bài gần gũi – mô-típ, câu một dòng, đố vui (T-092, T-095, T-096 — mở lại D-077). **Nên làm, không chặn (D-077):** 5 người dùng thật thử (2 giáo viên, 2 người trẻ, 1 người am hiểu văn hóa dân gian), vướng mắc thành task; chốt Q-006 | Giữa–cuối 12/2026 |
| **C · Mở lặng lẽ** | Tên miền (xong: phongdao.com, D-070); ảnh xem trước từng bài; sao lưu hằng ngày đã bật và đã thử khôi phục một lần (D-073); người sáng lập quyết định mở (H5, không thông báo rộng). **Nên có, không chặn (D-077):** 100–150 bài công khai, ≥ 50 bài có Hiểu nhanh (bài chưa có Hiểu nhanh chưa vào Google), thư đã gửi Ca dao Mẹ | Trước Tết Đinh Mùi 2027 (đầu 02/2027) |
| **V2 · Xây mới** (D-083, D-091) | **Đợt 1 · Nền:** mô hình, nguồn vào, chống trùng, tìm, công cụ nhập chạy trên dữ liệu mẫu (T-141..T-145). **Đợt 2 · Trang đọc:** 5 mẫu thể loại, trang chủ lối vào, tuyển tập, tra cứu, người đọc góp, đo lường, SEO; mỗi mẫu ≤ 200 KB, JavaScript trang bài ≤ 21 KB, 0 lỗi axe (T-146..T-151). **Đợt 3 · Bàn biên tập:** duyệt một nháp ≤ 60 giây trên điện thoại, mọi việc ở Hộp việc; E2E và dùng thử đạt (T-152..T-156). **Đợt 4 · Chuyển đổi:** site chạy V2 1.0.0, "trước = sau" đạt, không mất dữ liệu người làm (T-157), rồi mở (T-053). **Đợt 5:** sau khi mở (T-158..T-164). Chi tiết: `docs/26`–`29` | Đợt 1–4 trước Tết Đinh Mùi 2027 |

Cổng tự động luôn áp dụng: `npm run kiem-tra` đạt (validator tầng LỖI, fixture, kiểm nhất quán; từ T-035 thêm `astro check`, build, kiểm liên kết), quét trợ năng và ngân sách JS (từ T-040).

## 3. Chi tiết task

### Chuẩn bị

#### T-032 · Tài khoản và đẩy kho mã — N
**Việc:** tạo tài khoản GitHub; repo **công khai** `truonganhquyen/ca-dao` cho kho mã này (bảo vệ nhánh `main` miễn phí với repo công khai; chặn agent đẩy thẳng, cho phép tài khoản người sáng lập/CMS); repo **riêng tư** `truonganhquyen/ca-dao-kho-tho` cho kho thô (T-038); tài khoản Cloudflare; bật xác thực hai lớp cho cả hai. Đẩy nhánh `main` và nhánh T-033 lên. Agent soạn hướng dẫn từng bước khi người sáng lập cần.
**Chấp nhận:** Codex Cloud truy cập được cả hai repo; CI chạy trên PR đầu tiên.
**Tiến độ (24/10/2026):** đã có 2 repo, đã đẩy `main` và nhánh T-033 (commit dùng email ẩn `…@users.noreply.github.com`), đã bật bảo vệ nhánh `main`. Tài khoản Cloudflare đã tạo; Codex đã được cấp quyền vào 2 repo (người sáng lập xác nhận 24/10/2026).

#### T-033 · Tài liệu và công cụ kiểm tra v2 — A
**Đọc:** `01` (D-035..D-047). **Việc:** áp dụng gói quyết định vào `AGENTS.md`, `docs/`, validator, thư viện suy ra, từ vựng, mẫu, fixture; chuyển tài liệu không còn dùng vào `docs/luu-tru/`.
**Chấp nhận:** `npm run kiem-tra` đạt; fixture phủ mọi quy tắc tầng LỖI có thể kiểm bằng `--content`; không còn tài liệu đang dùng nhắc tới cổng/trạng thái cũ.

#### T-034 · Thư cho Ca dao Mẹ — N
**Việc:** gửi thư thông báo + xin phép dùng chú thích qua form `cadao.me/dong-gop` (bản nháp thư trong `docs/12` mục 7). Có văn bản đồng ý thì đổi `ban_quyen` của `src-ca-dao-me` sang `duoc_phep` và ghi D-xxx.
**Chấp nhận:** thư đã gửi (nên trước T-053, không chặn — D-077); kết quả (đồng ý / từ chối / im lặng) ghi vào `ghi_chu` của nguồn.

### Mốc A · Chạy được

#### T-035 · Khởi tạo Astro + CI + triển khai — A
**Đọc:** `21` mục 1–3, `AGENTS.md`.
**Việc:** Astro xuất tĩnh (phiên bản: D-049 — Astro 7 thay Astro 6 vì lỗ hổng chưa vá), TypeScript strict, Tailwind CSS 4 cấu hình từ token `20` mục 1.1; trang "Sắp ra mắt"; mở rộng `npm run kiem-tra` thêm `astro check`, `astro build`, kiểm liên kết nội bộ trong `dist/`; CI dùng `npm ci`; triển khai Cloudflare với bản xem trước cho mỗi nhánh/PR (người sáng lập bấm kết nối theo hướng dẫn); ghi phiên bản Astro, Tailwind vào `01`.
**Chấp nhận:**
- [x] `npm run kiem-tra` gồm `astro check` + build; dữ liệu có LỖI thì build thất bại với thông báo đọc được.
- [x] Mỗi PR có link bản xem trước — Cloudflare Workers Builds, sau khi người sáng lập kết nối theo `docs/huong-dan/ket-noi-cloudflare.md`; PR của T-035 là PR đầu tiên kiểm điều này và chỉ được gộp khi đã có link.
- [x] Không thêm thư viện nào ngoài Astro, Tailwind, `yaml` (không adapter). Chỉ thêm công cụ lúc phát triển mà `astro check` bắt buộc (`@astrojs/check`, `typescript`) và plugin Vite chính thức của Tailwind 4 (`@tailwindcss/vite`).

**Tiến độ (24/10/2026):** trang "Sắp ra mắt" + trang 404; token màu `20` mục 1.1 trong `src/styles/global.css` (sáng/tối theo hệ thống); phông Be Vietnam Pro + Literata tự lưu trữ qua Fonts API; `npm run build` dừng trước `astro build` khi dữ liệu có LỖI; bộ kiểm liên kết `scripts/kiem-lien-ket.mjs` có kiểm thử riêng (`tests/lien-ket/`); CI chạy Node theo `.node-version` với `npm ci`.

#### T-036 · Schema một nguồn → Zod + cấu hình CMS — A
**Đọc:** `10` mục 4, 9; `21` mục 4.
**Việc:** chuyển mô tả schema (`SCHEMA` trong `scripts/kiem-tra-du-lieu.mjs`) sang `scripts/lib/schema.mjs` để validator dùng chung; viết script sinh (1) schema Zod cho 7 collection, (2) cấu hình Sveltia CMS, (3) kiểu TypeScript cho từ vựng từ `taxonomy/tu-vung-kiem-soat.yaml`. Quy tắc nghiệp vụ R01–R22 **không** viết lại trong Zod.
**Chấp nhận:**
- [x] `astro check` qua với dữ liệu hiện có; fixture vẫn đạt.
- [x] Đổi một giá trị trong tệp từ vựng → chạy lại script → kiểu và cấu hình CMS đổi theo; `kiem-tra` báo lỗi nếu tệp sinh ra lệch với nguồn.

**Tiến độ (24/10/2026):** `scripts/lib/schema.mjs` (validator dùng trực tiếp); `npm run sinh:schema` sinh `src/sinh-ra/schema.ts`, `src/sinh-ra/tu-vung.ts`, `public/admin/config.yml`; `npm run kiem-sinh` trong `kiem-tra`; `src/content.config.ts` nạp 7 collection. Cấu hình CMS hợp lệ theo JSON schema của Sveltia 0.233.0. **Để T-037 thử trên CMS thật:** đối tượng không bắt buộc nhưng có trường con bắt buộc (`goc_kiem_chung`, `sgk`) khi để trống; `nguon.nam` = `CAN_BO_SUNG` (ô số không nhập được chữ); thư mục tệp `public/tep` (Sveltia bắt buộc, dự án không đăng ảnh).

#### T-037 · CMS Sveltia — A
**Đọc:** `21` mục 12; `11`.
**Việc:** Sveltia CMS (ghim phiên bản) ở `/admin`; đăng nhập GitHub qua một Worker OAuth nhỏ; nhãn tiếng Việt, ví dụ trong từng ô; form tác phẩm theo thứ tự **Lời → Nguồn → Nghĩa → Chú giải → Tình huống → Nâng cao** (âm luật, diễn xướng, kiểm chứng: thu gọn); bộ "**Thêm nhanh**" (tiêu đề, lời, vùng, url nguồn) tạo bài `nhap`; ô đánh dấu `nhap_ai_da_duyet` ghi rõ "Tôi đã đọc và đồng ý mọi phần do AI soạn"; quan hệ tới mục từ, nguồn, mô-típ dạng chọn từ danh sách; hướng dẫn 1 trang cho người sáng lập (gồm cách thêm bài vào tình huống — quan hệ lưu ở tệp tình huống, D-010).
**Chấp nhận:** người sáng lập tạo một bài từ điện thoại tới `cong_khai` trên bản xem trước mà không thấy YAML; lỗi kiểm tra hiện được cho người sáng lập (email CI + Bàn biên tập).
**Tiến độ (24/10/2026):** đăng nhập (T-037.1, Worker đã kiểm), `/admin` với Sveltia 0.233.0, nhãn tiếng Việt + gợi ý trong từng ô, thứ tự Lời → Nguồn → Nghĩa → Chú giải → Tình huống → Nâng cao (âm luật, diễn xướng, kiểm chứng thu gọn), "Thêm nhanh", ô "Tôi đã đọc và đồng ý mọi phần do AI soạn", quan hệ chọn từ danh sách, hướng dẫn `docs/huong-dan/bien-soan-cms.md`; đã thử bằng Sveltia thật (kho thử). Lỗi kiểm tra tới người sáng lập qua **email CI**; phần Bàn biên tập làm ở T-047 (Mốc B). **Điều kiện gộp:** người sáng lập thử đăng nhập và tạo một bài từ điện thoại tới `cong_khai` (cần T-040 đã gộp để trang bài hiện ra).

#### T-038 · Kho thô từ cadao.me — A
**Đọc:** `21` mục 16; `12` mục 4; D-045, D-046.
**Việc:** theo đặc tả `21` mục 16: thu thập **toàn bộ lời** (và dị bản), danh sách từ có chú thích, bản tham chiếu chú thích vào **repo riêng tư**; chạy nhỏ giọt bằng GitHub Action có điểm dừng; cập nhật hằng tháng.
**Chấp nhận:**
- [x] Không một byte nào của kho thô vào repo công khai (mã và dữ liệu ở `truonganhquyen/ca-dao-kho-tho`; repo này chỉ có đặc tả).
- [x] Lời lưu nguyên văn (so khớp từng ký tự với trang gốc trên mẫu 20 bài), không qua AI — `npm run kiem-mau` trong repo kho thô.
- [x] Nhịp ≤ 1 yêu cầu/3 giây (API) và ≤ 1/5 giây (HTML), dừng khi gặp 429/5xx hoặc lỗi mạng, User-Agent `KhoTangCaDao-ThuThap/1.0 (+https://github.com/truonganhquyen/ca-dao)`.
- [x] Không tải ảnh (bỏ cả khối ảnh trong lời và chú thích); báo cáo số mục theo loại (`bao-cao.md`).

**Tiến độ (24/10/2026):** mã thu thập + kiểm thử + workflow ở PR của repo kho thô; chạy thử 16 phút tại máy: nhận diện đủ 16.530 mục, API xong, lời API và lời trang không lệch bài nào. Khi PR đó gộp, GitHub Actions chạy mỗi ngày tới khi đọc hết trang (ước 4–8 lần), rồi 30 ngày cập nhật một lần. Phát hiện khi làm: dị bản/chú thích chỉ có trên trang HTML; bài liên quan chỉ là đoạn trích (`21` mục 16.2). `tu-tho-tuc.txt`: bản khởi đầu, **chờ người sáng lập duyệt**.

#### T-039 · Đường ống ứng viên — A
**Đọc:** `21` mục 16; `11`; `templates/prompt-soan-nhap.md`.
**Việc:** nhận danh sách mục tiêu (dòng đầu hoặc mã kho thô); với mỗi mục: tìm trong kho thô, gom bản trùng/gần trùng, đề xuất bản phổ biến nhất làm `v1`; tạo `content/tac-pham/<id>.yaml` trạng thái `nhap` với nguồn `src-ca-dao-me` (url, ngày truy cập, bản lưu Wayback nếu lưu được); gợi ý cụm từ cần chú giải từ danh sách từ có chú thích; bước **soạn nháp AI** (nghĩa ngắn, mô tả tình huống, giải nghĩa mục từ viết lại theo ý — `cach_dung: tham_khao`) chạy trong phiên agent theo mẫu lệnh, không cần khóa API riêng; **kiểm chống chép** (độ trùng n-gram giữa nháp và chú thích gốc, vượt ngưỡng thì gắn cờ); kiểm trùng với `content/` (R19).
**Chấp nhận:** từ 10 dòng đầu mục tiêu sinh 10 bài `nhap` có lời khớp kho thô từng ký tự; `npm run kiem-tra` đạt; không bài nào `cong_khai` hay `nhap_ai_da_duyet: true` do máy đặt; lời không mang `NHAP_AI`.

#### T-070 · Áp dụng D-054..D-056 — A
**Việc:** ghi quyết định; nới bộ kiểm của site Astro (lỗi biên tập thành NHẮC, kể cả R20, R21; từ cấm trong trang chỉ nhắc); cập nhật AGENTS.md, CLAUDE.md; viết `docs/24-wordpress.md`; lập T-071..T-076.
**Chấp nhận:** `npm run kiem-tra` đạt; các bộ mẫu `nhac-r*` chứng minh quy tắc đã nới vẫn được phát hiện (NHẮC).

#### T-071 · Plugin WordPress `ca-dao` — A
**Đọc:** `24`; D-054..D-056.
**Việc:** loại bài "Ca dao – Tục ngữ" (lời ở nội dung, "Hiểu nhanh" ở đoạn trích), phân loại Thể loại / Chủ đề / Vùng, hộp "Nguồn" (tên, địa chỉ, ngày) và dòng "Nguồn: …" tự hiện cuối bài; trang **Nguồn tự động** (thêm, bật/tắt, số bài mỗi ngày, công tắc chép chú thích); kho thu thập (bảng riêng trong cơ sở dữ liệu); bộ đọc Ca dao Mẹ cho ca dao qua API; mỗi ngày tự tạo N bài **Chờ duyệt**.
**Chấp nhận:** trên WordPress chạy tại máy (Playground): cấu hình nguồn → bấm "Lấy ngay" → kho có bài → "Tạo bài chờ duyệt" → bài ở Chờ duyệt có lời đúng nguyên văn và nguồn đúng trang; đăng bài thủ công chỉ cần tiêu đề + lời; không chặn đăng vì lý do biên tập.
**Tiến độ (24/10/2026):** đạt cả tiêu chí trên WordPress 7.1.3 tại máy — một lượt "Lấy ngay" lấy 400 ca dao (5 yêu cầu), "Tạo bài chờ duyệt ngay" tạo 5 bài, đăng một bài: trang `/ca-dao/<ten>/` có lời, "Nguồn: Ca dao Mẹ ↗" đúng trang gốc, `noindex` khi chưa có Hiểu nhanh. `npm run test:wordpress`: 39/39 (CI job `wordpress`). Bộ trích PHP khớp từng ký tự với bộ trích JavaScript của kho thô trên 100 bài mẫu thật (chạy ngoài repo). Ô "Chờ duyệt" trên Bảng tin. Chưa có: theme riêng (T-072), dị bản/chú thích/loại khác từ trang (T-073).

#### T-072 · Theme WordPress `ca-dao` — A
**Đọc:** `20` mục 1, 4, 11. **Việc:** theme con của Twenty Twenty-Five: token màu, phông, sáng/tối, trang bài (lời lớn, lục bát thụt lề, chú thích và dòng "Nguồn: …" dưới chú thích), đọc tốt ở 360 px. **Chấp nhận:** quét trợ năng WCAG 2.2 AA 0 lỗi trên trang bài.
**Tiến độ (25/10/2026):** `wordpress/themes/ca-dao` (`24` mục 2). `npm run quet:wordpress`: 7 trang (trang chủ, kho, thể loại, 4 bài mẫu tổng hợp: lục bát có dị bản và chú thích, câu đố, tục ngữ, bài nhập tay có Hiểu nhanh) × 3 chế độ — 0 lỗi, không tràn ngang ở 360 px; đã xem ảnh chụp bằng mắt. Máy chủ tự lấy theme và bật một lần sau khi gộp (T-078).

#### T-073 · Bộ đọc Ca dao Mẹ đầy đủ — A
**Việc:** dị bản và chú thích (chú thích, lời giải chép kèm "Nguồn: Ca dao Mẹ ↗" khi công tắc bật — D-055, D-057), thành ngữ–tục ngữ, đồng dao, vè, câu đố (câu hỏi + đáp án); dùng lại cách trích đã kiểm ở kho thô (T-038: 20/20 khớp từng ký tự). **Chấp nhận:** kiểm mẫu 20 bài khớp từng ký tự với trang gốc.
**Tiến độ (25/10/2026):** plugin 0.3.0. Kiểm mẫu trên cadao.me thật: 23/23 bài (5 ca dao, 5 thành ngữ–tục ngữ, 5 đồng dao, 4 vè, 4 câu đố) khớp từng ký tự với trang gốc, trích bằng cách làm độc lập; bộ trích trang PHP khớp bộ trích JavaScript của kho thô trên 53 bài, 120 chú thích (so ở thư mục tạm, không đưa dữ liệu thật vào repo). Kiểm thử plugin: 59 phép thử, 62 khi thêm `--mang` (có nguồn giả dựng bằng `pre_http_request`).

#### T-074 · Hosting WordPress — N
**Việc:** chọn và mua hosting theo yêu cầu ở `24` mục 8 (agent soạn so sánh khi cần). **Chấp nhận:** có tài khoản quản trị hosting, PHP ≥ 8.3, có sao lưu. **Đã có (24/10/2026):** VPS + FlashPanel, cadao.sdigi.net, PHP 8.5 (`24` mục 8).

#### T-075 · Cài WordPress lên hosting — N+A
**Việc:** cài WordPress, plugin `ca-dao` (theme cài khi T-072 xong); sao lưu tự động; cron thật thay WP-Cron nếu hosting cho phép. **Chấp nhận:** người sáng lập đăng một bài từ điện thoại; nguồn tự động chạy theo lịch.

**Tiến độ (25/10/2026):** xong — WordPress, plugin, theme chạy ở cadao.sdigi.net; người sáng lập đã tự đăng bài; nguồn tự động chạy theo lịch. Xác thực hai lớp bỏ (D-057); **sao lưu FlashPanel: người sáng lập để sau**.

#### T-078 · Tự cập nhật từ main — A
**Việc:** `wordpress/may-chu/tu-cap-nhat.sh` (cron 5 phút): commit mới trên `main` → lấy plugin của commit đó, kiểm cú pháp, thay một lần, trả lại bản cũ nếu trang chủ hỏng. Người sáng lập đồng ý 25/10/2026. **Chấp nhận:** gộp một PR chạm plugin thì site có bản mới trong ≤ 10 phút, không cần agent vào VPS.

#### T-077 · Lời không ghi nguồn (D-057) — A
**Việc:** bài tự động không ghi nguồn cho lời (địa chỉ gốc ở ô nội bộ `_cadao_url_goc`); dòng "Nguồn: … ↗" dưới chú thích đã chép, theo công tắc "Ghi nguồn cho chú thích" của từng nguồn; nâng cấp 0.2.0 bỏ nguồn máy tự điền ở bài cũ. **Chấp nhận:** kiểm thử plugin đạt; trên site thật, bài tự động không còn "Nguồn: …".

#### T-079 · Ghi gói nâng cấp vào tài liệu — A
**Việc:** tổng hợp 51 mục người sáng lập duyệt ngày 25/10/2026 (47 mục, sau đó thêm 4 ý chống trùng) vào `25`, D-059..D-065 trong `01`, task T-080..T-111 và các task cũ viết lại ở đây. **Chấp nhận:** `npm run kiem-tra` đạt; người sáng lập xem PR.

#### T-080 · Tách thành ngữ và tục ngữ — N+A
**Đọc:** `25` mục 4.4. **Việc:** máy gợi ý loại cho từng câu nhóm "thành ngữ – tục ngữ" của nguồn (danh mục Wiktionary, rồi cấu trúc câu), không chắc thì để chờ; hàng duyệt hàng loạt trong trang quản trị; bài đã đăng đổi thể loại và đường dẫn (`/thanh-ngu/…` ↔ `/tuc-ngu/…`, chuyển hướng 301 từ đường dẫn cũ). **Chấp nhận:** mẫu 50 câu do người sáng lập kiểm, gợi ý đúng ≥ 90% với câu máy đã chắc; không câu nào tự đổi loại khi chưa duyệt; kiểm thử plugin đạt.

**Tiến độ (25/10/2026):** phần máy xong — plugin 0.6.0 (`inc/tach-loai.php`, `24` mục 2), kiểm thử: các quy tắc gợi ý, không tự đổi khi chưa duyệt, duyệt thì bài đổi thể loại và đường dẫn cũ chuyển hướng 301, câu duyệt trước khi thành bài vào đúng Thành ngữ. **Còn chờ người sáng lập:** kiểm 50 câu ở nhóm "Máy đã chắc" (đúng ≥ 90% thì chuyển DONE; dưới thì agent chỉnh quy tắc).
**DONE theo D-077 (26/10/2026):** phần máy xong; việc duyệt của người sáng lập làm dần trong Bàn biên tập, không chặn task sau.


#### T-081 · Đếm lượt đọc — A
**Đọc:** `25` mục 4.7, D-064. **Việc:** đếm khi trang hiện ≥ 5 giây, một máy một lần/ngày, không cookie, không lưu IP, gom theo bài theo ngày; "N lượt đọc" trên trang bài khi ≥ 10; danh sách "Đọc nhiều tuần này"; ô Bảng tin (7 ngày, bài đọc nhiều, trang giới thiệu). **Chấp nhận:** kiểm thử: máy dò không chạy JavaScript không được tính, tải lại trong ngày không cộng thêm; không có cookie mới; quét trợ năng đạt.

**Tiến độ (25/10/2026):** xong — plugin 0.4.0 (`inc/luot-doc.php`, `assets/ca-dao.js`, `24` mục 2). Kiểm thử plugin: đếm, bỏ máy dò tự khai tên, bài chưa đăng / không phải Ca dao – Tục ngữ không nhận, chỉ lưu tên miền giới thiệu, "N lượt đọc" từ 10, "Đọc nhiều tuần này" từ 3 bài. Thử trên trình duyệt (`quet:wordpress`): gửi đúng một lần sau 5 giây, tải lại không gửi, tắt JavaScript không gửi, không cookie. Danh sách "Đọc nhiều tuần này" ở cuối trang chủ; trang chủ mới: T-043.

#### T-109 · Nhận ra bài trùng — A
**Đọc:** `25` mục 4.9, D-065. **Việc:** mỗi bài, mỗi dị bản, mỗi câu trong kho có hai dấu vân tay của lời (bỏ dấu câu, chữ hoa, khoảng trắng thừa; và bỏ cả dấu thanh), lưu có chỉ mục; hàm so trả mức: trùng y hệt / gần như y hệt (≥ 90%, so cặp âm tiết liền nhau) / có thể là dị bản (50–90%, hoặc trùng một dị bản đã ghi) / khác. Câu một dòng chỉ xét y hệt và gần như y hệt; câu ngắn nằm trong bài dài chỉ là "liên quan". **Không so tiêu đề hay dòng đầu.** **Chấp nhận:** kiểm thử trên dữ liệu tổng hợp đủ các mức (kể cả hai bài khác nhau chung dòng đầu, "hoà/hòa", câu ngắn trong bài dài, cùng lời khác thể loại); chạy lại trên kho thật ra số liệu khớp phép đo 25/10/2026 (`25` mục 4.9); không tự gộp, không tự xóa gì.

**Tiến độ (25/10/2026):** xong — plugin 0.5.0 (`inc/trung.php`, `24` mục 3–4). Kiểm thử trên lời tổng hợp đủ các mức (y hệt; chỉ khác dấu, "hoà/hòa"; khác một chữ trong bài dài; 50–90%; câu ngắn trong bài dài; chung dòng đầu; câu một dòng; dưới 6 âm tiết; trùng dị bản đã ghi). Chạy trên bản sao kho thật (12.606 câu có lời, chỉ trên máy): 10 câu y hệt, 1 chỉ khác dấu, 9 là dị bản đã ghi của câu khác, 3 cặp ≥ 80%, 99 cặp 50–80%, 278 dòng đầu dùng chung — khớp phép đo 25/10/2026 (9 / 1 / 8 / 3 / 96 / 274 trên 12.328 câu). `wp cadao trung-thong-ke` chạy lại phép đo (chỉ đọc).

#### T-110 · Chống trùng khi lấy từ nguồn — N+A
**Đọc:** `25` mục 4.9. **Việc:** câu mới vào kho (mọi nguồn) được so với bài đã có và các câu trong kho; trùng y hệt / gần như y hệt → không tạo bài mới, gắn vào bài có sẵn (trạng thái kho "Đã gộp"), chú thích và dị bản mới của nguồn khác được bổ sung (bài chưa sửa tay tự bổ sung, kèm dòng "Nguồn" riêng của nguồn đó); có thể là dị bản → hàng "Có thể trùng" (Gộp làm dị bản / Là bài khác / Để sau) trong Bàn biên tập; máy tạo bài hằng ngày bỏ qua câu đang chờ quyết. Dọn luôn các trùng sẵn có trong kho (9 câu y hệt, 8 câu là dị bản đã ghi của câu khác). **Chấp nhận:** kiểm thử với hai nguồn giả cùng một câu (y hệt, khác dấu, dị bản) ra đúng kết quả; không câu nào bị gộp khi chưa duyệt ở mức "có thể là dị bản".

**Tiến độ (25/10/2026):** xong — trang **Có thể trùng**, tab "Đã gộp" + "Tách ra" ở Kho thu thập, làm dần khi nâng cấp (dọn trùng sẵn có). Chặt hơn đặc tả: chỉ tự gắn khi lời giống hệt và cùng thể loại; khác vài chữ (≥ 90%) cũng chờ người sáng lập quyết (`25` mục 4.9). Thử trên bản sao kho thật: ~40 cặp vào hàng chờ, toàn dị bản thật hoặc cùng lời khác thể loại; chưa câu nào tự gộp (các câu trùng y hệt cùng thể loại phần lớn chưa đọc trang gốc — sẽ gộp khi máy đọc tới). Việc của người sáng lập: quyết các cặp trong trang "Có thể trùng".

#### T-111 · Báo trùng khi viết tay — A
**Đọc:** `25` mục 4.9. **Việc:** trong trình soạn, sau khi dán/gõ lời (ngừng gõ một chút) và khi bấm Đăng, ô bên cạnh hiện các bài có thể trùng và mức giống, với nút Mở bài này / Thêm làm dị bản (lời vừa gõ thành dị bản của bài cũ; bản nháp mới vào thùng rác, khôi phục được 30 ngày) / Vẫn đăng bài mới; danh sách bài có nhãn "Có thể trùng". **Chấp nhận:** không chặn đăng (D-054); trên điện thoại dùng được; kiểm thử "Thêm làm dị bản" giữ đúng lời từng ký tự.

**Tiến độ (25/10/2026):** xong — `assets/soan-trung.js`, REST `cadao/v1/trung`, `them-di-ban`, `khong-trung`. Kiểm thử: dán lời bài đã có → "Trùng y hệt"; "Thêm làm dị bản" giữ đúng từng ký tự (cả ngoặc kép, &, <), bản nháp vào thùng rác; "Vẫn đăng bài mới" thì không báo nữa; bài trùng có nhãn "Có thể trùng" mà vẫn đăng được. Thử bằng trình duyệt trên máy tính và màn hình điện thoại 390 px: ô hiện và tự mở khi có bài giống, không lỗi JavaScript.

#### T-112 · Lời vào khối Thơ — A
**Ghi chú (25/10/2026):** phát hiện khi kiểm site thật — 16 bài vè người sáng lập nhập tay tối 25/10 có lời trong khối Đoạn văn (dán từ trang web khác), nên trang bài thiếu hộp Trích dẫn, ☆ Lưu, Trình chiếu, Nghe; tìm theo lời và so trùng không thấy bài (mọi thứ đọc lời từ khối Thơ). **Chấp nhận:** máy đề xuất, người biên soạn quyết; không đổi chữ nào; dòng chú giữ là đoạn văn; hoàn tác được.

**Tiến độ (25/10/2026):** xong — plugin 0.18.0 (`inc/khoi-tho.php`). Đề xuất: đoạn văn chứa lời → khối Thơ (mỗi dòng một dòng; mỗi dòng một đoạn văn thì nối liền; hai đoạn nhiều dòng thì cách một dòng trống), dòng chú in nghiêng trọn dòng hoặc nằm trong ngoặc giữ là đoạn văn, tiêu đề và khối khác giữ nguyên, đoạn trống bỏ; chữ sau khi chuyển khác chữ trước thì không đề xuất. Trang **Lời chưa vào khối Thơ** (menu Ca dao – Tục ngữ, có số đếm, chỉ hiện khi còn bài): mỗi bài xem trước số dòng, 4 dòng đầu, dòng chú giữ lại; nút **Chuyển bài này** hoặc chọn nhiều rồi **Chuyển các bài đã chọn**; WordPress lưu bản sửa đổi để hoàn tác. Trình soạn: ô bên cạnh (và bước xác nhận khi bấm Đăng) báo "Lời đang nằm trong khối Đoạn văn" với nút **Đưa lời vào khối Thơ**. Đã chạy thử (chỉ đọc) trên bản sao 16 bài vè: cả 16 đề xuất được, lời chú và dòng nguồn người sáng lập tự ghi giữ là đoạn văn.

#### T-113 · Sửa mục từ — A
**Ghi chú (26/10/2026):** người sáng lập hỏi cách đổi ảnh và dòng "Nguồn: Ca dao Mẹ ↗" của mục từ "canh" (`/tu-dien/canh/`). Ảnh xúp là do khớp nhầm Wikidata ("canh" giờ đêm ↔ Q41415 "xúp") đã được bấm "Đúng" — sửa tận gốc bằng "Sai" ở Xác nhận Wikidata; nhưng trang quản trị chưa có chỗ đổi ảnh hay sửa nguồn. **Chấp nhận:** người biên soạn sửa được nguồn (xóa khi tự viết lại lời giải), chọn ảnh khác kèm ghi công, ẩn ảnh Wikidata; máy không tự điền nguồn.

**Tiến độ (26/10/2026):** xong — plugin 0.20.0 (`inc/sua-muc-tu.php`). Trang **Sửa mục từ** (Ca dao – Tục ngữ → Từ điển → bấm tên mục từ; hoặc "Sửa mục từ" trên thanh quản trị khi xem trang mục từ) thêm: **Nguồn của lời giải** (tên, đường dẫn — điền sẵn giá trị đang có; xóa thì trang không còn dòng Nguồn và mục từ hết `noindex`), **Ảnh** (xem ảnh đang dùng, "Chọn ảnh khác…" mở Thư viện ảnh của WordPress, "Bỏ ảnh đã chọn", ô ghi công hiện dưới ảnh, "Không hiện ảnh lấy từ Wikidata"), và lối tới Xác nhận Wikidata → Đúng → "Sai" khi máy khớp nhầm thực thể. Ảnh riêng dùng cho cả trang mục từ lẫn ô chạm từ trong bài (`cadao_phan_them_muc_tu`); chữ thay thế của ảnh riêng là tên mục từ. Thử trên trình duyệt: ô hiện đúng ở khổ 390 px, nút mở Thư viện ảnh.

#### T-114 · Chính sách thu thập dữ liệu — A
**Đọc:** D-068. **Chấp nhận:** không còn dòng "Nguồn: …" trỏ về nguồn tự động dưới chú thích, đáp án (bài, mục từ đã đăng); nguồn khác người biên soạn ghi vẫn giữ; không đổi chữ nào khác; trang chính sách đã đăng, có danh sách nguồn; ghi công CC BY-SA giữ tại chỗ; mục từ chép vẫn `noindex`.

**Tiến độ (26/10/2026):** xong — plugin 0.21.0 (`inc/chinh-sach-du-lieu.php`). Nâng cấp tắt "Ghi nguồn cho chú thích" ở mọi nguồn tự động; mỗi lượt lấy bài (5 phút) dọn tối đa 25 giây: bài chưa sửa tay làm lại từ kho, bài đã sửa tay chỉ bỏ khối Nguồn trỏ về tên miền nguồn đó (giữ khoảng trắng, đánh dấu `cadao-chep` cho danh sách chú thích ngay trước); mục từ: bỏ `_cadao_nguon_ten/_url`, đặt `_cadao_chep`. Đếm trên site thật trước khi chạy (chỉ đọc): 90 bài (đều chưa sửa tay), 180 mục từ. Trang **Chính sách thu thập dữ liệu** (`/chinh-sach-du-lieu/`, đăng ngay vì là nơi ghi nhận nguồn): lấy gì, cách lấy, xin gỡ, `[cadao_nguon]`; chân trang có "Chính sách dữ liệu"; bản nháp Phương pháp sửa câu hứa dòng Nguồn. Sửa mục từ: viết lại lời giải thì bỏ dấu "chép".

**Nguồn công khai (27/10/2026, D-081):** bài đã có dòng "Nguồn: …" do người biên soạn điền thì người đọc không còn thấy dòng "[N nguồn tham khảo đã ẩn]"; người biên soạn đăng nhập vẫn thấy liên kết về trang gốc (`cadao_dong_nguon_an`, plugin 0.33.2; 3 kiểm thử mới trong `wordpress/tests/kiem-thu.php`).

#### T-115 · Số liệu của bài — A
**Đọc:** D-069. **Chấp nhận:** dòng nhỏ dưới tiêu đề, icon nét, số rút gọn, ẩn dưới ngưỡng; trình đọc màn hình đọc câu đầy đủ; chia sẻ chỉ tính khi gửi xong; không cookie, không IP.

**Tiến độ (26/10/2026):** xong — plugin 0.22.0 (`inc/so-lieu.php`), theme 0.13.0. Bộ lọc `render_block_core/post-title`: trang bài → 👁 · ↗ · ❝ dưới tiêu đề; trong vòng lặp bài (kho, thể loại, chủ đề, vùng, tình huống…) → chỉ 👁; "Đọc nhiều tuần này" và kết quả tìm cũng có 👁. Số "N lượt đọc" trong hàng nút bỏ (chuyển lên dòng số liệu). Sự kiện `chia_se` (`web_share` khi `navigator.share` báo thành công, `sao_chep_lien_ket`), đánh dấu một lần mỗi ngày mỗi bài trong `localStorage` (`cadao-da-chia-se`); Bảng tin có tổng chia sẻ 30 ngày. `ca-dao.js` ~20 KB (ngân sách 21 KB).

#### T-076 · Chuyển nội dung, ngừng site Astro — A
**Việc:** chuyển bài, mục từ, nguồn trong `content/` sang WordPress (giữ lời nguyên văn), **qua bước chống trùng** (T-109: bài cũ trùng câu đã có thì thành dị bản hoặc gộp, người sáng lập quyết); trỏ địa chỉ tạm; ngừng triển khai Astro. **Chấp nhận:** mọi bài công khai cũ hiện trên WordPress; lời khớp từng ký tự.

**Tiến độ (25/10/2026):** xong — 2 bài công khai (`cd-0001`, `cd-0003`) chuyển bằng `du-lieu/site-cu.json`, qua bước chống trùng (bài trùng bài đã có thì vào ở dạng Bản nháp và hiện ở "Có thể trùng"); kiểm thử: lời nguyên văn, Hiểu nhanh, chú giải, nguồn đã nhập, đường dẫn cũ, chạy lại không tạo trùng. Địa chỉ cũ chuyển hướng 301 sang cadao.sdigi.net (`24` mục 7).

#### T-040 · Trang tác phẩm — A
**Đọc:** `20` mục 4–5, 10–12, 14; `10` mục 5, 10.
**Việc:** trang `/ca-dao/{slug}`, `/tuc-ngu/{slug}`, `/thanh-ngu/{slug}` cho bài `cong_khai`, đọc theo lớp (`20` mục 4.1); nhãn từ `tinhNhan()`, dị bản từ `diBanHienThi()`, chuỗi qua `chuoiHienThi()` (cùng `scripts/lib/suy-ra.mjs`); vị trí chú giải từ `phanGiaiNeo()`; khối Nguồn gốc ở lớp 1; dòng "có hỗ trợ AI" khi `nhap_ai_da_duyet`; `noindex` khi chưa có giá trị riêng; sáng/tối theo hệ thống; bản in; thanh điều hướng dưới trên điện thoại.
**Chấp nhận:**
- [x] Bài nhãn D hiện nhãn "Lời phổ biến – chưa đối chiếu" và dòng nguồn gốc; không có chữ nào trong danh sách từ cấm. (`kiem-trang`)
- [x] Không còn chuỗi `CAN_BO_SUNG` hay `NHAP_AI` nào lộ ra trang. (`kiem-trang`, mọi trang)
- [x] Tắt JavaScript: đọc được lời, chú giải (cuối khối lời), nguồn gốc. (`quet-trang`)
- [x] Đạt `20` FR-1.8 trên màn hình 360 px; WCAG 2.2 AA cho phần đã làm. (`quet-trang`: axe 0 lỗi ở 360 px sáng/tối và máy tính; ảnh chụp 360 px)

**Tiến độ (24/10/2026):** `VerseBlock`, `GlossSheet`, `EvidenceBadge`, `ProvenanceBlock`, `VariantList`, `BottomNav`, khung `KhungDoc`; dữ liệu qua `src/lib/kho.ts` (gọi lại `suy-ra.mjs`, `phanGiaiNeo()`); bộ mẫu giao diện ở bản xem trước (D-050); `kiem-trang` + `quet-trang` trong `kiem-tra` (D-051). **Chưa làm, chờ task khác:** nút trích dẫn/chia sẻ (T-042); liên kết "Gửi số trang", "Góp ý", "Ở quê bạn…" (T-045 — hiện chữ "sắp có"); "Xem thêm" tới `/tu-dien/` (T-044); liên kết `/phuong-phap` từ nhãn (T-046); thẻ tình huống thành liên kết (T-043); mục Tìm/Tình huống/Đã lưu của thanh dưới (T-041, T-043, Mốc C); JSON-LD (T-050). `dien_giai.noi_dung` hiện thành đoạn văn thường, chưa dựng Markdown.

#### T-041 · Tìm kiếm — N+A
**Đọc:** `25` mục 4.3, `21` mục 5, `20` mục 8, `23` mục 4.
**Việc (viết lại 25/10/2026 cho WordPress):** ô tìm đầu trang, kết quả hiện khi gõ, tìm trong lời, tiêu đề, chú thích; phương án chính Meilisearch (người sáng lập cài ứng dụng trong FlashPanel), chỉ mục cập nhật khi bài đăng/sửa; thử "d" → "đ" trước khi chốt, không đạt thì dùng chỉ mục bỏ dấu tự làm.
**Chấp nhận:** `boDau` và `chuanHoaCoDau` có kiểm thử đơn vị với các ca trong `21` mục 5; truy vấn kiểu `khong_dau`, `tu_khoa`, `dau_kieu_cu` trong `data/truy-van-thu.csv` có đáp án trong 5 kết quả đầu (khi đáp án đã công khai); trang không kết quả đúng `20` mục 8.

**Tiến độ (25/10/2026):** xong theo D-067 (chỉ mục bỏ dấu tự làm, không cần Meilisearch) — plugin 0.11.0 (`inc/tim.php`, `assets/tim.js`). Trang **Tìm** (`/tim/?q=…`, có ở thanh đầu trang; ô tìm của WordPress chuyển về đây): kết quả hiện khi gõ (150 ms), tô phần khớp, dòng giữa bài có chứa phần khớp, thể loại, Hiểu nhanh; nhóm "Từ điển" phía trên khi khớp tên mục từ; nhớ 5 lần tìm gần đây trên máy; tắt JavaScript vẫn tìm được. Trọng số: tiêu đề, lời ×3; Hiểu nhanh, chú thích ×1; gõ có dấu thì bài đúng dấu đứng trước. Kiểm thử: các ca của `21` mục 5 (boDau, chuanHoaCoDau), `q03` "thuy chung", `q04` "hoà thuận", gõ dở chữ cuối, trọn âm tiết, tìm trong chú thích, bài chưa đăng không ra. Câu trong `data/truy-van-thu.csv` chưa có đáp án công khai nên chưa chấm được 5 kết quả đầu. Trang không kết quả: báo "Chưa tìm thấy câu này trong kho" kèm gợi ý; 6 tình huống phổ biến và form gửi câu thêm khi có T-043, T-045. Chưa có: khớp gần đúng (sai một ký tự), đo "tìm không ra" (T-052).

#### T-042 · Trích dẫn và chia sẻ — A
**Đọc:** `20` mục 6–7.
**Chấp nhận (viết lại 25/10/2026 theo D-054, D-057):** 2 định dạng; nút "Sao chép kèm nguồn" (mặc định: lời, tên bài, thể loại, địa chỉ trang của site, ngày truy cập tự điền) và "Chỉ sao chép lời"; lời không ghi nguồn ngoài, chú thích chép từ nguồn khi được sao chép thì kèm dòng "Nguồn: …" của nó; nút Chia sẻ dùng Web Share (văn bản + URL), không hỗ trợ thì sao chép link; chạy được trên WordPress (theme + plugin).

**Tiến độ (25/10/2026):** xong — plugin 0.4.0 (`inc/trich-dan.php`), theme 0.2.0; kiểu trích dẫn mới ở `20` mục 6. Hộp Trích dẫn nằm ngay dưới lời và Hiểu nhanh; tắt JavaScript vẫn hiện chữ trích dẫn. Thử trên trình duyệt: "Sao chép kèm nguồn" ra lời + trích dẫn, báo "Đã sao chép. Cảm ơn bạn đã ghi nguồn!". Đo lường lượt sao chép: T-052.

### Mốc B · Thử nhỏ

#### T-043 · Tình huống và trang chủ — A
**Đọc:** `25` mục 4.3. **Việc (thêm 25/10/2026):** trang chủ có "Câu hôm nay" chữ lớn, "Vừa bổ sung", "Đọc nhiều tuần này" (T-081), "Bạn có biết?" (T-093), lối vào theo thể loại và theo vùng, ô tìm; không khung mạng xã hội, không mây thẻ.
**Chấp nhận:** trang tình huống chỉ tạo khi có ≥ `NGUONG_TRANG_TINH_HUONG` (2) bài công khai; mỗi bài hiện lời rút gọn ≤ 4 dòng + "Hợp khi…"; trang chủ: câu định vị, ô tìm lớn, thẻ tình huống theo `mac_dinh` trong `config/lich-tinh-huong.yaml`.

**Tiến độ (25/10/2026):** xong — plugin 0.12.0 (`inc/trang-chu.php`), theme 0.8.0. Trang chủ: câu định vị, ô tìm lớn (tới `/tim/`), **Câu hôm nay** chữ lớn (mỗi ngày một bài ≤ 4 dòng, ai mở cũng thấy cùng câu), **Bạn có biết?** (câu đã duyệt của tuần), **Hôm nay bạn đang…** (thẻ tình huống đủ 2 bài đang đăng, xếp theo dịp trong lịch rồi thứ tự `mac_dinh`), Đọc nhiều tuần này, **Vừa bổ sung**, lối vào theo thể loại và theo vùng; không khung mạng xã hội, không mây thẻ. Tình huống: phân loại "Tình huống" (`/tinh-huong/<tên>/`) người biên soạn gắn bài, ô **"Hợp khi…"** trong trình soạn; trang tình huống chỉ có khi đủ 2 bài (chưa đủ thì 404), mỗi bài lời rút gọn ≤ 4 dòng + "Hợp khi…". Hai tình huống của site cũ tạo sẵn (lời mở đầu, lịch dịp từ `content/`, `config/` qua `npm run xuat:site-cu` → `du-lieu/tinh-huong.json`; dịp chưa có ngày bỏ qua).

#### T-044 · Từ điển — A
**Đọc:** `25` mục 4.3. **Việc (viết lại 25/10/2026 cho WordPress):** mỗi chú thích (gộp theo mã của nguồn) một trang; trang tổng lọc Nam Bộ / Trung Bộ / Bắc Bộ / từ cổ / Hán Việt theo ghi chú trong lời giải; chạm từ trong lời (T-082) dẫn tới đây.
**Chấp nhận:** `/tu-dien/` liệt kê mục từ đang được dùng; trang mục từ hiện giải nghĩa, nguồn ("Tham khảo: Ca dao Mẹ ↗" khi `cach_dung: tham_khao`, "Theo Ca dao Mẹ ↗" khi `trich_nguyen_van`), mọi bài có từ này.

**Tiến độ (25/10/2026):** xong — plugin 0.9.0 (`inc/tu-dien.php`). Phân loại "Từ điển" (`tu_kho`, `/tu-dien/<từ>/`): mỗi chú thích (cùng từ, cùng lời giải — một chú thích của nguồn dùng ở nhiều bài) là một mục từ, máy gắn khi lưu bài (bài đã có gắn dần); trang mục từ có lời giải, dòng "Nguồn: …" đúng như dưới chú thích trong bài (D-057 thay cho "Tham khảo / Theo" của bản Astro), số bài, danh sách bài; mục từ chép từ nguồn mang `noindex`. Trang "Từ điển" (`/tu-dien/`, tạo một lần, sửa được lời giới thiệu): theo chữ cái (Đ riêng), lọc Nam Bộ / Trung Bộ / Bắc Bộ / từ cổ / Hán Việt theo lời giải. Ô chạm từ (T-082) có "Xem trong từ điển (N bài có từ này)". Thêm "Từ điển" vào thanh đầu trang.

#### T-045 · Góp ý và cảm nghĩ của người đọc — N+A
**Đọc:** `25` mục 4.6, D-062; `docs/luu-tru/40-quy-trinh-dong-gop.md` mục 2–3.
**Việc (viết lại 25/10/2026):** form chạy ngay trong WordPress (thay Tally): "Ở quê bạn câu này nói khác?", "Góp ý chú thích", "Báo lỗi", "Bạn có sách? Gửi số trang"; ô "Cảm nghĩ của bạn" (bình luận WordPress, luôn chờ duyệt); Cloudflare Turnstile + ô bẫy ẩn + giới hạn theo giờ; khóa Turnstile người sáng lập dán vào trang cấu hình plugin.
**Chấp nhận:** mọi thứ gửi vào Chờ duyệt, không tự hiện; không bắt email, tên thật, số điện thoại; tối đa 5 câu bắt buộc; có **thông báo quyền riêng tư ngay trong form** (Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15, hiệu lực 01/01/2026; dịch vụ đặt máy chủ ở nước ngoài); nút "Ở quê bạn câu này nói khác?", "Báo lỗi" và **"Bạn có sách? Gửi số trang"** mở form với mã bài và URL điền sẵn; người ghi hộ điền xong trên điện thoại trong 5 phút.

**Tiến độ (25/10/2026):** xong — plugin 0.13.0 (`inc/nguoi-doc.php`). Cuối mỗi bài: **Góp ý cho bài này** với 4 nút ("Ở quê bạn câu này nói khác?", "Góp ý chú thích", "Báo lỗi", "Bạn có sách? Gửi số trang") mở cùng một form, loại chọn sẵn, mã bài điền sẵn; 3 câu bắt buộc (loại, nội dung, đồng ý CC BY 4.0), còn lại tùy chọn (nơi nghe, người kể, tên hiển thị); không ô email, điện thoại; thông báo quyền riêng tư ngay trong form (Luật 91/2025/QH15, máy chủ ở nước ngoài, cách xin gỡ). Góp ý lưu thành mục **Góp ý của người đọc** (Chờ duyệt, chỉ trong trang quản trị). **Cảm nghĩ của bạn** = bình luận WordPress: luôn chờ duyệt, không bắt tên / email (không tên thì "Một bạn đọc"), bỏ cookie bình luận của WordPress; cảm nghĩ đã duyệt có "Báo vi phạm". Chống rác: ô bẫy ẩn, 5 lần gửi mỗi giờ (dấu băm tạm một giờ, không lưu IP), **Cloudflare Turnstile** khi người sáng lập dán khóa ở trang **Chống rác** (chưa có khóa thì vẫn chạy, chỉ thiếu lớp này); Turnstile chỉ tải khi người đọc bắt đầu điền form. Việc của người sáng lập (khi muốn): tạo khóa Turnstile, dán vào trang Chống rác.

#### T-046 · Trang Phương pháp, Nguồn, Về dự án — N+A
**Đọc:** `docs/luu-tru/50-ban-nhap-trang-cong-khai.md` mục 1, 5; `12`.
**Chấp nhận:** Phương pháp giải thích 4 nhãn, lời hứa mới, cách dùng AI, ghi công Ca dao Mẹ; `/nguon` sinh tự động từ `content/nguon/` (chỉ nguồn đang được dùng); N duyệt câu chữ.

**Tiến độ (25/10/2026):** phần máy xong — plugin 0.14.0 (`inc/trang-du-an.php`). Ba trang tạo một lần ở dạng **Bản nháp**, câu chữ soạn theo cách site đang làm (thay các nhãn A–D của bản Astro, đã bỏ từ D-054): **Phương pháp** (lời chép nguyên văn bằng máy, không ghi nguồn dưới lời; chú thích, đáp án có dòng "Nguồn"; dị bản; máy làm gì — người làm gì; AI không viết lời, dị bản, nguồn; đóng góp vào Chờ duyệt; báo lỗi), **Nguồn** (mã ngắn `[cadao_nguon]` tự sinh: nguồn tự động có chú thích chép trên bài đang đăng, nguồn người biên soạn ghi trong bài, dữ liệu mở đang dùng — Wikidata, Commons, Natural Earth — kèm số bài), **Về dự án** (mục đích, phi lợi nhuận, mã nguồn mở, giấy phép, không cookie theo dõi, cách liên hệ / xin gỡ). Chân trang chỉ hiện liên kết tới trang đã đăng. **Còn chờ người sáng lập:** đọc, sửa câu chữ, bấm Đăng (Trang → Bản nháp).
**DONE theo D-077 (26/10/2026):** phần máy xong; việc duyệt của người sáng lập làm dần trong Bàn biên tập, không chặn task sau.


#### T-047 · Bàn biên tập — A
**Việc (viết lại 25/10/2026 cho WordPress):** một trang trong wp-admin gom các hàng chờ: bài Chờ duyệt, xác nhận Wikidata (T-086), vùng (T-089), thể loại tách (T-080), chủ đề (T-091), "Có thể trùng" (T-110, T-111, T-101), bài và góp ý người đọc gửi (T-045, T-097), mỗi hàng có số đếm và nút duyệt hàng loạt. Phần dưới đây là đặc tả cũ cho site Astro, giữ làm tham khảo.
**Chấp nhận (cũ):** trang `/ban-bien-tap` (noindex, khóa bằng Cloudflare Access) dựng từ `npm run health:json`: nháp AI chờ duyệt, bài công khai đang noindex, mục từ thiếu nguồn, tham chiếu web thiếu bản lưu trữ, chú thích trích nguyên văn chờ phép, tình huống chưa đủ bài, lỗi build gần nhất. Người sáng lập không cần dòng lệnh.

**Tiến độ (25/10/2026):** xong — plugin 0.19.0 (`inc/ban-bien-tap.php`). Trang **Bàn biên tập** (mục đầu tiên của menu Ca dao – Tục ngữ, số đếm = việc của người đọc + bài Chờ duyệt): 13 hàng chờ xếp theo thứ tự nên làm — báo lỗi / báo vi phạm của người đọc (đứng đầu), góp ý khác, cảm nghĩ, bài phân tích, bài Chờ duyệt, lời chưa vào khối Thơ, Có thể trùng, tách thành ngữ – tục ngữ, Wikidata, gợi ý vùng – chủ đề, Bạn có biết?, ba trang dự án, giới thiệu thể loại; mỗi hàng có số, một dòng "làm gì" và nút **Mở** tới đúng trang (duyệt hàng loạt làm ở trang đó: tách loại, gợi ý, khối Thơ; bài Chờ duyệt dùng "Chỉnh sửa" hàng loạt của WordPress — không có nút đăng tất cả). Hàng đã xong gom ở cuối. Bảng tin có ô **"việc chờ bạn"** ở đầu. Sửa kèm: bản đồ chỉ hiện mô tả Wikidata khi là tiếng Việt.

#### T-082 · Chạm vào từ khó — A
**Đọc:** `25` mục 4.1; `20` FR-1.2–1.4. **Việc:** lưu vị trí dấu chú thích (mã, dòng, cột) và từ được chú thích; nút gạch chân chấm trong lời; ô trượt (điện thoại) / ô cạnh từ (máy tính); bài đã đăng chưa sửa tay tự cập nhật. **Chấp nhận:** mở bằng chạm và Enter, đóng bằng Esc/chạm ra ngoài, trả focus; tắt JavaScript vẫn đọc được danh sách chú thích; quét trợ năng 0 lỗi; kiểm thử plugin đạt.

**Tiến độ (25/10/2026):** xong — theme 0.3.0, plugin 0.7.0. Làm khác đặc tả một chút cho bền hơn: **không lưu vị trí dấu chú thích**; lúc dựng trang, máy tìm đúng từ được chú thích (chữ đậm đầu mỗi mục "Chú thích") trong lời chính, trọn từ, lần xuất hiện đầu, từ dài trước — nên dùng được cho mọi bài (cả bài sửa tay, bài chuyển từ site cũ), không cần đọc lại trang nguồn hay làm lại bài. Chú thích không ghi từ chỉ có ở danh sách. Từ là liên kết tới mục chú thích (tắt JavaScript vẫn nhảy tới được); JavaScript mở `<dialog>`: ô trượt từ dưới lên trên điện thoại, ô cạnh từ trên máy tính, có dòng "Nguồn: …" của chú thích. Dị bản chưa gắn từ (chỉ tô chỗ khác). Thử trên trình duyệt: Enter mở, Esc đóng, focus trở về từ. "Xem trong từ điển": khi có T-044.

#### T-083 · Dị bản — A
**Đọc:** `25` mục 4.1. **Chấp nhận:** nhãn "Dị bản 1, 2…"; chữ khác lời chính được tô (so theo âm tiết), đọc được khi không phân biệt màu (gạch chân hoặc chữ đậm kèm theo); quét trợ năng 0 lỗi.

**Tiến độ (25/10/2026):** xong — khối Thơ đứng sau tiêu đề "Dị bản" có nhãn "Dị bản 1, 2…", viền trái; chữ khác lời chính tô nền và gạch chân (so âm tiết, bỏ qua chữ hoa, dấu câu; cùng số dòng thì so từng dòng, khác số dòng thì so cả bài).

#### T-084 · Thể thơ — A
**Đọc:** `25` mục 4.1. **Chấp nhận:** nhận đúng lục bát, song thất lục bát, câu 3–4–5 chữ, một dòng trên bộ mẫu kiểm thử (tổng hợp); trình bày theo thể; lọc danh sách theo thể.

**Tiến độ (25/10/2026):** xong — `inc/the-tho.php`: phân loại "Thể thơ" (`/the-tho/luc-bat/`…) máy gắn khi lưu bài, bài đã có gắn dần; nhãn thể thơ cạnh nhãn thể loại; lục bát và song thất lục bát thụt câu lục (Q-006 vẫn để hỏi người thử).

#### T-085 · Công cụ đọc — A
**Đọc:** `25` mục 4.1, 4.8; D-063. **Chấp nhận:** "Aa" 3 mức và sáng/tối nhớ trên máy (try/catch); trình chiếu toàn màn hình, phím mũi tên; nút "▶ Nghe" chỉ hiện khi máy có giọng tiếng Việt, có dòng "Giọng đọc máy", không tự phát; JavaScript thêm vào trang bài ≤ 15 KB.

**Tiến độ (25/10/2026):** xong — hàng nút "Aa" (vừa / lớn / rất lớn), "◐" sáng / tối, "Trình chiếu", "▶ Nghe" trong khối Trích dẫn · Chia sẻ; lựa chọn nhớ trong bộ nhớ trình duyệt (try/catch, không cookie), áp ngay trong `<head>` nên không nháy. Trình chiếu: `<dialog>` toàn màn hình, hai dòng một lần, mũi tên / nút để chuyển, Esc thoát. Nghe: Web Speech, chỉ hiện khi máy có giọng tiếng Việt, mỗi dòng một câu (ngắt cuối dòng), có dòng "Giọng đọc máy", bấm lần nữa để dừng. Cả tệp JavaScript trang bài ~15 KB (phần T-085 ~5 KB). Thử trên trình duyệt: cỡ chữ, sáng / tối còn sau khi tải lại; trình chiếu mở, mũi tên sang đoạn sau, Esc thoát; máy không có giọng Việt thì không có nút Nghe.

#### T-086 · Gắn Wikidata — N+A
**Đọc:** `25` mục 4.2, D-061. **Chấp nhận:** hàng chờ Xác nhận (Đúng / Sai / Bỏ qua) cho từ được chú thích và đáp án câu đố; chỉ thực thể đã xác nhận được dùng; ảnh chỉ giấy phép mở, lưu tác giả và giấy phép; nhịp gọi API lịch sự, có điểm dừng.

**Tiến độ (25/10/2026):** xong phần máy — `inc/wikidata.php`: mỗi lượt 5 phút xét dần mục từ và đáp án câu đố (cả bài chưa đăng), tìm thực thể có nhãn / tên khác tiếng Việt trùng đúng, bỏ trang định hướng, lấy tọa độ, ảnh, bài Wikipedia tiếng Việt; 1 giây một yêu cầu, User-Agent của dự án, lỗi mạng thì dừng tới lượt sau, không hỏi lại mục đã xét. Trang **Xác nhận Wikidata** (Đúng / Sai / Bỏ qua, có ảnh xem trước). "Đúng" thì ảnh Commons giấy phép mở (CC0, phạm vi công cộng, CC BY, CC BY-SA) được tải bản 480 px về thư viện ảnh, ghi tác giả và giấy phép; giấy phép khác thì không dùng ảnh. Kiểm thử bằng API giả. Việc của người sáng lập: xác nhận dần trong trang đó.

#### T-087 · Chú thích địa danh — A
**Chấp nhận:** ô chú thích có ảnh (ghi tác giả, giấy phép), bản đồ tĩnh nhẹ (bấm mới mở bản đồ ngoài), "Bài khác nhắc tới …"; trang không chậm hơn ngân sách `20` mục 12.

**Tiến độ (25/10/2026):** xong — với từ có thực thể đã xác nhận, ô chạm và trang mục từ có ảnh kèm dòng "Ảnh: <tác giả> · <giấy phép> · Wikimedia Commons", bản đồ nhỏ Việt Nam (`assets/ban-do-vn.svg`, 7 KB, đường biên Natural Earth 1:50m — phạm vi công cộng — **có quần đảo Hoàng Sa, Trường Sa**; sinh bằng `scripts/sinh-ban-do.mjs`) với chấm đúng tọa độ, "Mở bản đồ lớn ↗" (OpenStreetMap), "Đọc thêm trên Wikipedia ↗", và "Xem trong từ điển (N bài có từ này)". Ảnh, bản đồ chỉ tải khi mở ô / trang mục từ (`loading="lazy"`, nằm trong `<template>`), nên trang bài không nặng thêm.

#### T-088 · Ảnh đáp án câu đố — A
**Chấp nhận:** đáp án đã xác nhận có ảnh nhỏ kèm ghi công; đáp án không có ảnh thì không để chỗ trống.

**Tiến độ (25/10/2026):** xong — khối "Đáp án" có ảnh kèm ghi công và liên kết Wikipedia khi đáp án đã xác nhận; không có thì không thêm gì.

#### T-089 · Vùng — N+A
**Đọc:** `25` mục 4.2. **Chấp nhận:** gợi ý vùng từ ghi chú phương ngữ và địa danh đã xác nhận; duyệt hàng loạt; không gắn vùng khi chưa duyệt.

**Tiến độ (25/10/2026):** xong — `inc/goi-y.php`: 7 vùng của từ vựng dự án (Bắc Bộ, Bắc / Trung / Nam Trung Bộ, Tây Nguyên, Nam Bộ, phổ biến toàn quốc); gợi ý từ lời giải của chú thích nhắc miền, tỉnh, phương ngữ (Nghệ, Huế, Nam Bộ…) và từ tọa độ địa danh đã xác nhận (chia gần đúng theo vĩ độ, kinh độ); trang **Gợi ý vùng, chủ đề** (gợi ý đánh dấu sẵn kèm lý do, bỏ dấu cái sai, "Đồng ý" cả trang); một bài nhiều vùng; bài đã duyệt không hiện lại. Dòng "Vùng: …" cuối bài.

#### T-090 · Bài dài — A
**Đọc:** `25` mục 4.1. **Chấp nhận:** bài > 40 dòng chia đoạn, mục lục, số dòng, "Đọc toàn bài", nhớ chỗ đọc trên máy; "Vè cậu Hai Miêng" (990 dòng) mở nhanh trên điện thoại.

**Tiến độ (25/10/2026):** xong — bài từ 41 dòng: dòng tóm tắt (số dòng, số đoạn, phút đọc ~200 âm tiết/phút), mục lục đoạn (thu gọn), chia đoạn theo khổ (không có khổ thì ~20 dòng), số dòng bên lề mỗi 5 dòng; JavaScript thu gọn các đoạn bắt đầu sau dòng 30 và thêm nút "Đọc toàn bài (còn N dòng)" (tắt JavaScript thì hiện đủ), nhớ đoạn đang đọc, lần sau có "Đọc tiếp từ đoạn N". Dựng trang ở máy chủ, không tải thêm gì. Kiểm "Vè cậu Hai Miêng" trên site thật sau khi gộp.

#### T-091 · Bộ chủ đề riêng — N+A
**Đọc:** `25` mục 4.4, 4.5; D-061. **Việc:** người sáng lập duyệt danh sách chủ đề (agent soạn đề xuất từ số liệu kho) và bảng ánh xạ từ chủ đề, thẻ của nguồn; gồm nhóm tục ngữ kinh nghiệm, lối hát (hát ru, hò, lý, hát đối), loại câu đố. **Chấp nhận:** gợi ý chủ đề cho mọi bài đã đọc trang; không chép nguyên tên chủ đề, thẻ của nguồn ra trang khi chưa được chọn vào bộ của dự án.

**Tiến độ (25/10/2026):** phần máy xong — bộ chủ đề của dự án tạo sẵn: 11 chủ đề của từ vựng kiểm soát + nhóm "Kinh nghiệm (tục ngữ)" (thời tiết, mùa vụ, chăn nuôi, ăn uống – sức khỏe, đối nhân xử thế), "Lối hát" (hát ru, hò, lý, hát đối), "Loại câu đố" (đồ vật, con vật, cây cối – hoa quả, thiên nhiên, con người – sinh hoạt) như `25` mục 4.4. Gợi ý: chủ đề của nguồn (19 chủ đề, chỉ dùng nội bộ) qua **bảng ánh xạ** máy đề xuất; lời ru → Hát ru, dòng đầu "hò" → Hò, tên "Lý…" → Lý; tục ngữ theo từ khóa → nhóm kinh nghiệm. Dòng "Chủ đề: …" cuối bài. **Còn chờ người sáng lập:** xem và lưu bảng ánh xạ (đầu trang Gợi ý vùng, chủ đề) — lưu rồi thì chuyển DONE; đối nhân xử thế, hát đối chưa có gợi ý tự động, gắn tay khi cần.
**DONE theo D-077 (26/10/2026):** phần máy xong; việc duyệt của người sáng lập làm dần trong Bàn biên tập, không chặn task sau.


#### T-092 · Bài gần gũi, mô-típ — A
**Chấp nhận:** 3–5 thẻ cuối bài theo mô-típ, chú thích, địa danh, chủ đề; trang mô-típ do người sáng lập đặt tên/duyệt; không lặp chính bài đang xem.
**Tiến độ (26/10/2026):** xong — phân loại **Mô-típ** (`/mo-tip/<tên>/`, `inc/mo-tip.php`, plugin 0.32.0). Trang **Gợi ý mô-típ**: máy gom bài có dòng đầu bắt đầu bằng cùng một cụm chữ (2–4 âm tiết, từ 3 bài; cụm dài hơn thay cụm ngắn khi giữ ≥ 80% số bài), điền sẵn tên là chính cụm chữ; người sáng lập sửa tên, bỏ bài không hợp, bấm Tạo — hoặc "Không phải mô-típ"; bài mới cùng cụm được gợi ý thêm vào mô-típ đã có. Máy không tự gắn, không viết lời giới thiệu (ô Mô tả do người sáng lập viết). Khối "Đọc tiếp" (T-122) cộng điểm theo mô-típ (6), chủ đề (4), mục từ chung — từ khó, địa danh có ở 2–20 bài (3), vùng (2); nhãn "Cùng mô-típ …", "Cùng nhắc tới …". Cuối bài có dòng "Mô-típ: …". Gợi ý theo nghĩa ("thề bằng điều không thể") không làm: máy không đoán nghĩa — người sáng lập tạo mô-típ đó ở trang Mô-típ và gắn bài trong trình soạn.

#### T-093 · Bạn có biết? — N+A
**Chấp nhận:** khuôn ghép từ chú thích có sẵn, kèm nguồn; người sáng lập duyệt hoặc sửa trước khi lên trang; máy không tự viết sự kiện.

**Tiến độ (25/10/2026):** xong — mỗi ngày máy ghép thêm tới 8 câu chờ duyệt theo khuôn "… "[từ]" là [mệnh đề đầu của lời giải], và có trong câu "[tên bài]"?" từ mục từ đang được dùng (chỉ dùng chữ có sẵn), kèm nguồn của chú thích; trang **Bạn có biết?** sửa lời, chọn Duyệt / Bỏ, tự viết câu mới; trang chủ hiện 4 câu đã duyệt, xoay theo tuần. Việc của người sáng lập: duyệt, sửa câu cho tự nhiên.

#### T-094 · Trang thể loại — N+A
**Chấp nhận:** mỗi thể loại một trang (giới thiệu do người sáng lập viết, bài tiêu biểu, lối vào theo chủ đề và vùng).

**Tiến độ (25/10/2026):** xong — trang `/the-loai/<tên>/`: lời giới thiệu (mô tả của thể loại — người sáng lập viết ở Ca dao – Tục ngữ → Thể loại → Sửa; máy không viết), **bài tiêu biểu** (đọc nhiều nhất 30 ngày, chưa có lượt đọc thì bài mới), lối vào theo chủ đề và vùng đã duyệt, tổng số bài, rồi danh sách bài. Việc của người sáng lập: viết vài dòng giới thiệu cho từng thể loại.

#### T-095 · Câu một dòng — A
**Đọc:** D-079 — làm cho một mục "Tục ngữ – Thành ngữ".
**Chấp nhận:** trang bài thành ngữ, tục ngữ chữ to, không khung lời thơ; chỉ mục A–Z và theo chủ đề.
**Tiến độ (26/10/2026):** xong — trang bài: lời chính chỉ một dòng hiện như một câu (lớp `cadao-cau`: chữ to hơn lời thơ, không thụt treo, xuống dòng cân; theo cả cỡ chữ người đọc chọn) — theme 0.19.0. Trang Tục ngữ – Thành ngữ (`/the-loai/tuc-ngu/`): chỉ mục **A–Z** theo chữ đầu của tên bài (Đ sau D; chỉ chữ có bài; `?chu=…`, lọc phía máy chủ, noindex, canonical về danh sách), nhóm **Chủ đề** mở sẵn; mỗi câu một dòng kèm **nghĩa ngắn** khi đã có Hiểu nhanh (máy không viết nghĩa). Chữ đầu lưu cùng bài (`_cadao_chu_dau`) — plugin 0.33.0.

#### T-096 · Câu đố — A
**Chấp nhận:** trang bài vẫn hiện đáp án (D-058); trang "Đố vui" 10 câu một lượt, chọn theo loại, điểm nhớ trên máy; câu đố cùng đáp án hiện cạnh nhau.

#### T-097 · Bài phân tích của người đọc — A
**Đọc:** `25` mục 4.6, D-062. **Chấp nhận:** loại bài "Cảm nhận – phân tích" gắn với bài ca dao; trang soạn đơn giản cho người đọc, gửi vào Chờ duyệt; cam kết tự viết + đồng ý CC BY 4.0; hiện dưới bài ca dao và có trang riêng; "Bài phân tích của dự án" có nhãn riêng; bài ca dao có ≥ 1 bài phân tích đã duyệt thì bỏ `noindex`.

**Tiến độ (25/10/2026):** xong — `inc/phan-tich.php`: loại bài **Cảm nhận – phân tích** (`/phan-tich/<tên>/`); trang **Viết cảm nhận** (`/viet-cam-nhan/?bai=<id>`, `noindex`): tiêu đề, bài viết (dòng trống tách đoạn, `**đậm**`, dòng bắt đầu bằng `>` là trích lời; mọi chữ được thoát HTML), bút danh, "Bạn là" (học sinh / giáo viên / người yêu ca dao), cam kết tự viết, đồng ý CC BY 4.0 → Chờ duyệt. Dưới bài ca dao: "Cảm nhận, phân tích (N)" và "Viết cảm nhận của bạn về câu này". Trang bài phân tích: tác giả, 4 dòng đầu của bài ca dao kèm liên kết, nội dung, "Báo vi phạm". Người sáng lập viết trong trang quản trị, đánh dấu "Bài phân tích của dự án". Bài ca dao có ≥ 1 bài phân tích đã đăng: bỏ `noindex` (bộ lọc `cadao_co_gia_tri_rieng`).

#### T-118 · An toàn vận hành — A
**Đọc:** D-073; báo cáo review 26/10/2026 (phần Backend). **Việc:** điểm đếm lượt đọc và sự kiện (`/cadao/v1/luot-doc`, `/cadao/v1/su-kien`) chặn cùng máy gửi lặp ở phía máy chủ; IP người gửi chỉ lấy từ header Cloudflare khi yêu cầu thật sự đến từ dải IP Cloudflare; không lộ phiên bản WordPress; cách quay lại bản trước khi một PR làm hỏng site.
**Chấp nhận:** cùng máy gửi lại cùng lượt đọc / sự kiện trong ngày không được cộng; tự đặt header `CF-Connecting-IP` khi gửi thẳng tới máy chủ không né được; không lưu IP (chỉ dấu băm tạm, hết hạn sau một ngày), không cookie; không còn thẻ `generator`; có hướng dẫn quay lại bản trước cho người không dùng dòng lệnh.
**Tiến độ (26/10/2026):** xong — plugin 0.24.0. `cadao_ip_nguoi_gui()` (dải IP Cloudflare 26/10/2026, IPv4 + IPv6) dùng chung với chống rác form người đọc; `cadao_lan_dau_trong_ngay()`: dấu băm (IP + khóa + ngày) trong transient một ngày — lượt đọc theo bài, sự kiện theo loại + khóa (bài và kiểu, chuỗi tìm, mục từ). Gỡ `wp_generator` và chuỗi generator trong nguồn tin. `tu-cap-nhat.sh` ghi các bản đã lên site vào `/home/cadao/.cap-nhat-lich-su`; hướng dẫn `docs/huong-dan/quay-lai-ban-truoc.md` (nút Revert trên GitHub, hoặc `NHANH=<mã>`). Kiểm thử: 6 ca mới.

#### T-119 · Dòng "lời và mức kiểm" — A
**Đọc:** D-072; `20` (phần WordPress); `24` mục 5; trang Phương pháp (T-046).
**Việc:** ngay dưới lời (trước Hiểu nhanh) một dòng do máy tính: mặc định "Lời theo bản lưu truyền phổ biến · chưa đối chiếu sách"; khi người biên soạn tự điền hộp Nguồn: "Đã đối chiếu: <tên nguồn>, tr. N" (thêm ô **Trang** vào hộp Nguồn) hoặc "Đã đối chiếu: <tên nguồn> ↗" khi có địa chỉ; liên kết "Vì sao?" tới trang Phương pháp khi trang đó đã đăng. Cùng thông tin trong kết quả Tìm (nhãn ngắn) và dòng trích dẫn (chỉ thêm nguồn đối chiếu khi có).
**Chấp nhận:** máy không bao giờ hiện dạng "Đã đối chiếu" khi hộp Nguồn trống; bài tự động không có nguồn tự điền (D-057) nên luôn ở dạng mặc định; không dùng từ cấm (`AGENTS.md`); không chỉ dựa vào màu; đọc được khi tắt JavaScript; kiểm thử cho hai dạng và cho trường hợp trang Phương pháp chưa đăng; `npm run quet:wordpress` đạt.
**Tiến độ (26/10/2026):** xong — plugin 0.25.0, theme 0.14.0 (`inc/muc-kiem.php`). Hộp nguồn cũ thực ra là nguồn của **chú thích chép**, nên không dùng làm căn cứ: thêm hộp riêng **"Lời đã đối chiếu với"** (tên sách hoặc trang web, trang, địa chỉ), hộp cũ đổi tên "Nguồn của chú thích". Dòng chèn sau khối Thơ đầu tiên (ưu tiên 25, sau Hiểu nhanh của theme ở 20 nên đứng giữa lời và Hiểu nhanh); bài chưa có khối Thơ thì không có dòng. "Vì sao?" / "Đối chiếu thế nào?" dẫn tới `/phuong-phap/#muc-kiem` khi trang đã đăng; bản nháp trang Phương pháp do máy soạn (chưa đăng, chưa sửa đoạn đó) được thêm mục "Dòng nhỏ dưới lời nói gì". Tìm ghi "· chưa đối chiếu sách" / "· đã đối chiếu"; trích dẫn thêm "lời đối chiếu với …" chỉ khi có. Kiểm thử: 13 ca mới; quét: 2 phép thử trên trình duyệt (vị trí giữa lời và Hiểu nhanh, dạng mặc định).

#### T-120 · Điều hướng và danh sách bài — A
**Đọc:** `20` (thanh điều hướng dưới, phần WordPress); D-073.
**Việc:** menu trên: "Kho" đổi thành "Tất cả"; mục "Thể loại" mở danh sách thể loại; thể loại chưa có bài không hiện trên menu; thanh điều hướng dưới cố định cho điện thoại: Trang chủ · Tìm · Tất cả · Đã lưu (máy tính giữ menu trên); trang Tất cả (`/kho/`) mặc định xếp A–Z (có lựa chọn "Mới thêm"), mỗi mục có 2 dòng đầu của lời, thể loại, vùng, tiêu đề mục là `h3` dưới một `h2`; lọc dạng chip theo thể loại, thể thơ, vùng, chủ đề (tham số trên đường dẫn, chạy được khi tắt JavaScript); trang thể loại trống có lối sang Tìm và thể loại có bài; chân trang chia hai cột trên điện thoại.
**Chấp nhận:** 360 px không tràn ngang; vùng chạm ≥ 44 px; thanh dưới không che nội dung, nút hay focus; kiểm thử cho thứ tự A–Z, bộ lọc, ẩn thể loại trống; `npm run quet:wordpress` đạt.
**Tiến độ (26/10/2026):** xong — plugin 0.26.0 (`inc/danh-sach.php`), theme 0.15.0. Menu: Tất cả · Thể loại ▾ (thể loại 0 bài tự ẩn) · Từ điển · Tìm; trên điện thoại menu trên chỉ còn Thể loại, Từ điển, thanh dưới (Trang chủ · Tìm · Tất cả · Đã lưu) có `aria-current`, vạch trên, `scroll-padding` để focus không bị che. Trang Tất cả tên "Tất cả bài". Lọc dùng tham số riêng `?loai=`, `?the=`, `?mien=`, `?chude=` (tên phân loại làm WordPress coi trang là trang thể loại); A–Z theo khóa bỏ dấu `_cadao_ten_xep` ("đ" sau "d", cùng thứ tự trên MySQL và SQLite; bài cũ lập khóa một lần khi nâng plugin). Chip đang chọn có ✓ và `aria-current`; Vùng, Chủ đề thu gọn. Mục: tiêu đề h3 dưới h2 "N bài", 2 dòng đầu (bỏ dòng trùng tiêu đề), thể loại · vùng. Trang trống: lối sang Tìm và thể loại có bài. Chân trang: danh sách, hai cột trên điện thoại.

#### T-121 · Cuối bài, Tìm, Bàn biên tập — A
**Đọc:** D-062, D-073; `docs/huong-dan/doc-so-lieu.md`.
**Việc:** cuối bài: Chủ đề, Vùng lên ngay sau Chú thích; Góp ý, Cảm nghĩ, Viết cảm nhận gộp thành một khối "Bạn muốn góp gì?" với 3 lựa chọn, đoạn quyền riêng tư nói một lần; tiêu đề "Cảm nhận, phân tích" chỉ hiện khi đã có bài được duyệt. Tìm: ghi tổng số bài tìm được; khi không ra, gợi ý bỏ bớt từ, xem Từ điển, gửi câu còn thiếu. Bàn biên tập và ô "việc chờ bạn": đưa lên đầu 3 nhóm tác động lớn (bài đọc nhiều chưa có Hiểu nhanh, đóng góp người đọc chờ duyệt, Có thể trùng), các hàng chờ khác thu vào "Khi rảnh".
**Chấp nhận:** form giữ đủ trường, chống rác, cam kết như T-045, T-097 (kiểm thử cũ vẫn đạt); không thêm cookie, không hỏi thêm dữ liệu cá nhân; `npm run quet:wordpress` đạt.
**Tiến độ (26/10/2026):** xong — cuối bài: Chủ đề, Vùng ngay sau chú thích (plugin, thay khối của mẫu trang); "Cảm nhận, phân tích (N)" chỉ khi có bài được duyệt; khối **"Bạn muốn góp gì?"** (Góp ý cho bài này · Cảm nghĩ ngắn · Bài cảm nhận, phân tích), form góp ý và form cảm nghĩ đều thu gọn nên đoạn quyền riêng tư chỉ hiện ở form đang mở; mẫu trang bỏ form bình luận riêng. Tìm: "N bài" hoặc "20 trên N bài — gõ thêm chữ để thu hẹp"; khi không ra gợi ý gõ ít chữ, có dấu hay không, tất cả bài, từ điển, và nói rõ câu tìm không ra được ghi lại (không có form "gửi câu còn thiếu" riêng — sự kiện tìm không ra đã làm việc đó). Bàn biên tập: "Làm trước" (báo lỗi, Hiểu nhanh, góp ý, cảm nghĩ, bài phân tích, Có thể trùng) và "Khi rảnh" thu gọn; ô trên Bảng tin chỉ liệt kê việc Làm trước.

#### T-122 · "Đọc tiếp" cuối bài, bản tối giản — A
**Đọc:** D-075, D-073; `docs/20` (cuối bài); báo cáo `docs/e2e-review/REPORT.md` (L-05).
**Việc:** khối "Đọc tiếp" sau Chủ đề, Vùng, trước "Bạn muốn góp gì?": bài đã đăng cùng chủ đề (2 điểm mỗi chủ đề chung), cùng vùng (1 điểm), bằng điểm thì bài mới hơn trước; chưa đủ 3 bài thì thêm bài cùng thể loại đăng ngay trước / sau bài này. Mỗi bài: tên, hai dòng đầu, nhãn vì sao. Không có bài nào thì không có khối.
**Chấp nhận:** 3–5 bài, không lặp bài đang xem, không có bản nháp; cả ô là một liên kết cao ≥ 44 px; không JavaScript; `npm run test:wordpress`, `npm run quet:wordpress` đạt. Không làm mô-típ, trang mô-típ (phần còn lại của T-092).
**Tiến độ (26/10/2026):** xong — `inc/doc-tiep.php` (plugin 0.29.0), CSS `.cadao-doc-tiep` (theme 0.16.0); kiểm thử E2E L-05 bỏ đánh dấu "lỗi đã biết".
**Sửa trình bày (27/10/2026):** lớp `.cadao-doc-tiep` trùng với nút "Đọc tiếp từ đoạn N" của bài dài (T-090), nên trên site khối thành một hình bầu dục viền son, tiêu đề nằm cạnh danh sách. Nút đổi sang `.cadao-doc-tiep-doan`; khối theo nhịp "Bạn muốn góp gì?" (kẻ trên, tiêu đề cỡ vừa) và danh sách trang Tất cả (tên màu son, lời chữ có chân, nhãn nhạt, dấu ›). Tên bài trùng dòng đầu thì đoạn trích bắt đầu từ dòng hai (không lặp tên), mỗi dòng một hàng. Plugin 0.33.1, theme 0.19.1; thêm kiểm thử đoạn trích và một phép thử trình duyệt chặn lỗi tái diễn.

#### T-123 · Tự gắn câu trùng một dị bản đã ghi — A
**Đọc:** D-074, D-065; `docs/25` mục 4.9; `docs/24` mục 4.
**Việc:** khi so một câu mới trong kho, lời trùng 100% một dị bản đã ghi (dấu vân tay (1) hoặc (2)) của bài / câu có sẵn → gắn làm dị bản ("Đã gộp", `gop_lam = di_ban`), mọi thể loại; một lần sau nâng cấp, so lại các cặp loại này đang chờ (cặp khác của câu vừa gắn rời hàng chờ; cặp "Để sau" giữ nguyên).
**Chấp nhận:** câu như vậy không vào hàng "Có thể trùng", bài không có dị bản lặp; "Tách ra" đưa câu về "mới" và so lại không tự gắn nữa; cặp hai bài, bài viết tay không đổi; `npm run test:wordpress` đạt.
**Tiến độ (26/10/2026):** xong — `cadao_trung_kiem_kho`, `cadao_trung_tu_gan_di_ban_cu` trong `inc/trung.php` (plugin 0.29.0).

#### T-124 · Khoá cửa quản trị — A
**Đọc:** D-076; báo cáo `docs/e2e-review/REPORT.md` (L-16, L-03, L-01); AGENTS.md (không thu IP, không đổi cấu hình máy chủ).
**Chấp nhận:** `POST /xmlrpc.php` không trả phương thức nào; người ngoài không đọc được `/wp/v2/users`, `?author=1` không chuyển tới `/author/<tên>/`; sai tên hay sai mật khẩu cùng một câu báo; sai 5 lần trong 15 phút thì tạm khoá (theo tài khoản, không theo IP); `?ly_do=<chữ bất kỳ>` không hiện trên trang.
**Tiến độ (26/10/2026):** xong — `inc/an-toan.php`, bảng `CADAO_BAO_GUI` (`inc/nguoi-doc.php`); kiểm thử E2E L-16, L-03, L-01 thành kiểm thử bảo vệ. Việc của người vận hành (không phải mã): đổi mật khẩu, xác thực hai lớp, tên hiển thị khác tên đăng nhập.

#### T-125 · Tục ngữ, thành ngữ vào hàng Chờ duyệt — A
**Đọc:** D-076; `docs/24` mục 7 (Nguồn tự động); báo cáo E2E (L-15).
**Chấp nhận:** mỗi lượt tạo bài lấy xoay vòng theo loại của nguồn; trong thành ngữ – tục ngữ, câu đã quyết loại đi trước và thành bài đúng thể loại; Bàn biên tập có dòng "Kho đang có" đủ các thể loại (kể cả 0 bài).
**Tiến độ (26/10/2026):** xong — `cadao_kho_tao_bai_cho_duyet` (`inc/kho.php`), `cadao_kho_dang_co` (`inc/ban-bien-tap.php`). Bài vẫn ở Chờ duyệt; người sáng lập bấm Đăng.

#### T-126 · Người đọc bớt tắc — A
**Đọc:** D-076; `docs/20`; báo cáo E2E (L-02, L-04, L-06, L-07, L-08).
**Chấp nhận:** nhóm lọc Vùng, Chủ đề có mũi tên và số lựa chọn; danh sách 30 bài/trang, số trang ≥ 44 px; trang 404 đúng giọng site (ô tìm, lối sang Tất cả bài, Từ điển, không ảnh mẫu); kết quả tìm hiện tên bài + dòng khớp, tên không còn mã HTML; bài chỉ có chữ rời rạc xếp cuối, ở nhóm riêng; `npm run quet:wordpress` đạt.
**Tiến độ (26/10/2026):** xong — `inc/tim.php`, `assets/tim.js`, `inc/danh-sach.php` ([cadao_404]), theme `templates/404.html`, CSS; kiểm thử E2E L-02, L-04, L-06, L-07, L-08 (câu không có trong kho) thành kiểm thử bảo vệ. Không làm: chịu lỗi gõ (L-09).

#### T-127 · Canonical — A
**Chấp nhận:** trang chủ, Tất cả bài, trang phân loại có `<link rel="canonical">`; trang 2, 3… trỏ về chính nó; trang lọc, đổi cách xếp trỏ về danh sách không lọc; bài, trang tĩnh để WordPress tự in (không in hai lần).
**Tiến độ (26/10/2026):** xong — `cadao_canonical` (`inc/seo.php`).

#### T-128 · Một mục "Tục ngữ – Thành ngữ" — A
**Đọc:** D-079.
**Chấp nhận:** một thể loại "Tục ngữ – Thành ngữ" ở `/tuc-ngu/`; bài "Thành ngữ" cũ chuyển sang (giữ thể loại khác), mô tả nối lại; `/thanh-ngu/<tên>/`, `/the-loai/thanh-ngu/` chuyển hướng 301; không còn trang, hàng chờ "Tách thành ngữ – tục ngữ"; chống trùng coi là một mục; menu một mục.
**Tiến độ (26/10/2026):** xong — `inc/tuc-ngu-thanh-ngu.php` thay `inc/tach-loai.php` (plugin 0.31.0), menu theme 0.18.0. Dữ liệu `loai_tach` cũ trong kho để yên, không đọc.

#### T-048 · Duyệt 30–50 bài, thử với 5 người — N
**Đọc:** `11`; `docs/luu-tru/24-ke-hoach-beta.md` mục 2 (dùng nhiệm vụ N1–N6, không chấm ngưỡng 80%).
**Chấp nhận:** 30–50 bài trên bản xem trước; 5 người đã thử; vướng mắc thành task; Q-006 chốt (đã chốt D-092, 02/11/2026: thụt câu lục).

#### T-129 · Kiểm "site thật đúng bản" — A
**Đọc:** D-080; kế hoạch `docs/nghien-cuu/ke-hoach-thuc-hien-2026-10-27.md` việc 2. **Việc:** GitHub Action chạy sau mỗi lần gộp vào `main` và mỗi ngày: đọc `ca-dao.js?ver=` và `style.css?ver=` trên phongdao.com, so với `CADAO_PHIEN_BAN` và dòng `Version:` của theme trên `main`; lệch quá 30 phút thì mở (hoặc cập nhật) một issue "Site thật lệch bản". Chỉ đọc site. **Chấp nhận:** có kiểm thử cho phần so sánh; thử với số sai thì báo lệch; không ghi gì lên máy chủ.
**Tiến độ (27/10/2026):** xong — `scripts/kiem-site-that.mjs` (`npm run kiem-site-that`), `scripts/lib/phien-ban.mjs` (kiểm thử trong `npm run kiem-tra`), workflow `.github/workflows/kiem-site-that.yml` chạy mỗi giờ và chạy tay được: lệch quá 30 phút sau lần đổi plugin/theme gần nhất thì mở issue "Site thật lệch bản" (không mở trùng), khớp lại thì tự đóng. Thử trên site thật: "ĐÚNG BẢN plugin 0.33.1, theme 0.19.1".

#### T-130 · E2E chạy được ở mọi nơi — A
**Việc:** `tests/e2e/` nhận biến môi trường cho đường dẫn trình duyệt (thay `channel: 'chrome'` khi có) và cho yêu cầu API đi qua proxy; gỡ dấu "lỗi đã biết" của các lỗi đã sửa (L-10, các lỗi D-076). **Chấp nhận:** `npm run e2e` chạy được trên máy người sáng lập và trong môi trường agent; 0 kiểm thử hỏng ngoài dự kiến trên site đúng bản.
**Tiến độ (27/10/2026):** xong — biến `E2E_TRINH_DUYET`, `E2E_PROXY` (`tests/e2e/README.md`); bỏ dấu "lỗi đã biết" của L-10; câu tìm "đêm qua" (kho nay có nhiều bài mở đầu "Đêm qua") thay bằng "đêm qua trời sáng", thêm kiểm thử các bài có tên mở đầu bằng cụm vừa gõ đứng trước. Chạy trên phongdao.com 0.33.1: 89 đạt như mong đợi, 0 hỏng ngoài dự kiến.

#### T-131 · Đối chiếu lời với sách có bản quét — N+A
**Đọc:** D-080 (3), D-072, `docs/12`. **Việc:** (a) lấy văn bản hai sách theo cách Wikimedia cho phép (không cào hàng loạt), lưu ngoài repo công khai; đo số bài khớp. (b) Trang quản trị "Đối chiếu với sách": mỗi dòng một cặp bài ↔ ảnh trang quét, nút "Đúng, tr. N" / "Không khớp"; bấm mới điền hộp "Lời đã đối chiếu với". **Chấp nhận:** máy không tự điền; lời sách không vào repo công khai; lời khác thì đề xuất dị bản, không sửa lời chính.

#### T-132 · Tìm chịu gõ sai — A
**Việc:** khi 0 kết quả hoặc kết quả yếu, so gần đúng từng âm tiết đã bỏ dấu, hiện "Có phải bạn tìm …?". **Chấp nhận:** E2E L-09 đạt; câu không có trong kho không ra bài lạc đề.

#### T-133 · Bộ câu "Có trong SGK" — N+A
**Việc:** ô trong trình soạn: bộ sách, lớp, bài học (và trang khi người sáng lập đã thấy); trang lọc "Có trong SGK", nối Trình chiếu. **Chấp nhận:** máy không tự đánh dấu; chỉ hiện điều người sáng lập điền.

#### T-134 · Người soát lời tình nguyện — N+A
**Việc (sau khi mở):** dùng form "Gửi số trang" sẵn có; ghi bút danh người soát khi được duyệt. Không tài khoản, không dữ liệu cá nhân.

#### T-135 · Cờ "Nghi không phải dân gian" — N+A
**Đọc:** D-008, `docs/20` mục 4.1 và 10. **Việc:** ô bật cờ và câu căn cứ trong trình soạn; nhãn xám dưới lời; lọc trong danh sách. **Chấp nhận:** máy chỉ gợi ý, không tự gắn cờ; tắt JavaScript vẫn đọc được; quét trợ năng đạt.

#### T-136 · Khối "Biên soạn" cuối bài — A
**Việc:** ô cài đặt "Người biên soạn" (bút danh, D-080 (6)); khối cuối bài chỉ hiện khi ô có chữ, liên kết tới trang Phương pháp; thêm `Organization` (và `Person` khi có tên) vào dữ liệu có cấu trúc. **Chấp nhận:** để trống thì không có khối; JSON-LD hợp lệ; quét trợ năng đạt.

#### T-137 · Trang chủ đề, vùng "đọc liền" — N+A
**Việc:** trên trang chủ đề, vùng mỗi câu hiện kèm Hiểu nhanh và chú thích địa danh; đoạn giới thiệu do người sáng lập viết; chỉ lập chỉ mục khi đủ câu có Hiểu nhanh. **Chấp nhận:** máy không viết giới thiệu; tắt JavaScript vẫn đọc được.

#### T-138 · 50 câu được tìm nhiều — N+A
**Việc:** danh sách đề xuất (câu thường được tìm, câu tìm không ra trên Bảng tin, tục ngữ phổ biến trong kho), mỗi câu có lý do; nháp Hiểu nhanh theo lô 10 câu (chờ duyệt); hướng dẫn "Duyệt 10 câu trong 30 phút"; bài có Hiểu nhanh thì tiêu đề trang thêm "nghĩa và cách dùng". **Chấp nhận:** agent không tự khẳng định câu có trong SGK; không đổi đường dẫn; mục tiêu ≥ 50 câu đã đăng trước khi mở (nên có, không chặn).

### Mốc C · Mở lặng lẽ

#### T-049 · Tên miền — N
**Chấp nhận:** ghi D-xxx, xóa Q-005 khỏi câu hỏi mở (và khỏi `trang-thai.json`).
**Tiến độ (26/10/2026):** xong — D-070 (phongdao.com; cadao.sdigi.net chuyển hướng 301). Q-005 đóng cùng PR của T-118.

#### T-050 · Ảnh xem trước, noindex, SEO cơ bản — A
**Đọc:** `20` mục 7 (FR-4.2); `24` mục 5; `25` mục 4.3 (ảnh chia sẻ, gộp T-060) và 4.6 (quy tắc noindex mới, D-062); D-072.
**Chấp nhận:** mỗi bài đang đăng có ảnh 1200×630 (lời trích tối đa 4 dòng, thể loại, dòng mức kiểm D-072, phongdao.com; dấu đúng với "Ngưỡng", "Thuở", "Quyện"), sinh trên máy chủ và lưu lại, sinh lại khi lời đổi; thẻ `og:*` và meta description (Hiểu nhanh, hoặc 2 dòng đầu + thể loại; trang thể loại, chủ đề: câu đầu của mô tả); `noindex` theo `cadao_co_gia_tri_rieng()`, trang phân loại dưới 3 bài cũng `noindex`; sitemap của WordPress chỉ gồm trang được lập chỉ mục (không có Đã lưu, Viết cảm nhận, mục từ chép, bài `noindex`); JSON-LD (`CreativeWork` cho bài, `DefinedTerm` cho mục từ, `CollectionPage` cho phân loại, `BreadcrumbList`, `WebSite` + `SearchAction` trỏ `/tim/?q=`); canonical về phongdao.com. Không đổi `blog_public` (mở cho Google là H5, T-053).
**Tiến độ (26/10/2026):** xong — plugin 0.27.0 (`inc/seo.php`, `inc/anh-chia-se.php`). Ảnh 1200×630 vẽ bằng GD trên máy chủ với phông TTF tự lưu (`assets/phong/`: Literata, Be Vietnam Pro, OFL, cắt còn Latin + tiếng Việt, ~280 KB): thể loại, 4 dòng đầu (câu lục thụt lề, dòng quá dài cắt "…"), dòng mức kiểm, tên miền; lưu `uploads/cadao-anh/<id>-<dấu băm>.png`, nội dung đổi thì sinh lại và xoá ảnh cũ; có ảnh chung cho trang khác. Máy không vẽ được chữ (GD không FreeType) thì bỏ ảnh êm. Thẻ: meta description (Hiểu nhanh, hoặc 2 dòng đầu + thể loại + chú thích / số dị bản; phân loại: câu đầu của mô tả), `og:*`, `twitter:card`. JSON-LD: `CreativeWork` (`isBasedOn` chỉ khi người biên soạn đã đối chiếu — D-072), `DefinedTerm` (lời giải chép không đưa vào), `CollectionPage`, `BreadcrumbList`, `WebSite` + `SearchAction`. noindex: phân loại dưới 3 bài, danh sách đã lọc / đổi cách xếp, trang Tìm có câu tìm. Sitemap: bài có giá trị riêng, bỏ Đã lưu, Viết cảm nhận, góp ý, người dùng, phân loại dưới 3 bài, mục từ chép. Canonical: WordPress tự ghi theo phongdao.com. Playground không vẽ được chữ bằng GD nên phần vẽ kiểm bằng PHP thật: `npm run test:anh` (CI chạy trong job wordpress); đã xem ảnh bằng mắt.

#### T-051 · Đã lưu — A
**Ghi chú (25/10/2026):** Câu hôm nay, Vừa bổ sung chuyển sang T-043; cỡ chữ, sáng/tối, trình chiếu chuyển sang T-085. Phần còn lại: "Đã lưu".
**Chấp nhận (cũ, đọc theo ghi chú trên):** câu hôm nay chọn lúc build; "Vừa được bổ sung" từ lịch sử Git; "Đã lưu" lưu trên máy (`localStorage`, bọc try/catch, trang vẫn chạy khi bị chặn); chế độ trình chiếu toàn màn hình, chữ lớn, phím mũi tên sang bài kế; nút cỡ chữ "Aa" (3 mức) và nút sáng/tối, nhớ trên máy (`20` mục 1); không làm tăng JS ban đầu của trang tác phẩm quá ngân sách.

**Tiến độ (25/10/2026):** xong — plugin 0.16.0 (`inc/da-luu.php`, `assets/da-luu.js`). Hàng nút dưới lời có **☆ Lưu** (nút bật / tắt, `aria-pressed`; chỉ hiện khi trình duyệt cho dùng bộ nhớ) và liên kết **Bài đã lưu (N)**. Danh sách nằm trong `localStorage` (khóa `cadao-da-luu`: mã bài, tên, đường dẫn, thể loại, dòng đầu), không gửi về máy chủ. Trang **Đã lưu** (`/da-luu/`, `noindex`) dựng danh sách trên máy người đọc, có nút **Bỏ lưu** từng bài (focus sang bài kế), lời nhắc khi trống, báo khi trình duyệt chặn bộ nhớ, và lời giải thích quyền riêng tư. Chân trang có "Bài đã lưu". `ca-dao.js` vẫn dưới 21 KB. Sửa kèm: nút cần JavaScript (Chia sẻ) trước đây vẫn hiện khi tắt JavaScript — nay ẩn đúng.

#### T-052 · Đo lường 3 sự kiện — A
**Đọc:** `21` mục 7. **Chấp nhận:** không cookie, không lưu IP; đúng 3 sự kiện `tim_khong_ket_qua`, `trich_dan_sao_chep`, `chu_giai_mo`; hướng dẫn đọc số liệu hằng tuần.

**Tiến độ (25/10/2026):** xong — `inc/do-luong.php`. `POST /wp-json/cadao/v1/su-kien` nhận đúng 3 sự kiện (sự kiện khác: 400) và kiểm từng loại: **tìm không ra** — trang Tìm gửi khi kết quả trống đứng yên 2 giây (không tính lúc đang gõ dở; mở `/tim/?q=…` từ ô tìm ở trang khác cũng tính), máy chủ tìm lại và chỉ ghi khi thật sự không ra; chuỗi được chuẩn hóa (chữ thường, tối đa 80 ký tự), chuỗi trông như email / số điện thoại bị bỏ, tối đa 300 chuỗi mới mỗi ngày, giữ một năm. **Sao chép trích dẫn** — bài đang đăng + kiểu (thông dụng, APA 7, chỉ lời). **Mở chú giải** — mục từ (`tu_kho`) được chạm. Bảng `wp_cadao_su_kien` chỉ có ngày, loại, khóa, số. Không cookie, không IP; máy dò và người biên soạn đang đăng nhập không tính; mỗi sự kiện gửi một lần mỗi lần xem trang. Ô Bảng tin **"người đọc cần gì (30 ngày)"** kèm cách đọc; hướng dẫn `docs/huong-dan/doc-so-lieu.md`.

#### T-098 · Nghĩa thành ngữ, tục ngữ — N+A
**Đọc:** `25` mục 4.4, 4.5; D-061 (giấy phép A). **Chấp nhận:** khối nghĩa đen / nghĩa bóng / dùng khi / ví dụ / đồng nghĩa – trái nghĩa; phần từ Wiktionary ghi "Nguồn: Wiktionary ↗ · CC BY-SA" ngay dưới; phần do dự án viết không mang CC BY-SA.

#### T-099 · Chú giải từ sách hết bản quyền, Hán Việt — N+A
**Đọc:** `25` mục 4.4, 4.5. **Chấp nhận:** trích đoạn ghi tên sách, tác giả, năm, liên kết Wikisource; chỉ khớp chắc chắn; người sáng lập duyệt; chữ Hán kèm âm Hán Việt.

**Bổ sung (D-080, 27/10/2026):** thêm lớp "Giải nghĩa trong sách năm 1897" từ *Tục ngữ, cổ ngữ, gia ngôn* (Wikisource, có bản quét), ghi nguồn và trang tại chỗ, giữ chính tả xưa, chỉ ở câu người sáng lập đã duyệt; làm sau bước (a) của T-131.
#### T-100 · Trợ giúp viết Hiểu nhanh — A
**Ghi chú (26/10/2026, D-073):** bỏ phụ thuộc T-098 — ca dao không cần nghĩa Wiktionary; nghĩa Wiktionary thêm vào khi T-098 xong. Mục tiêu trước khi mở: ≥ 50 bài có Hiểu nhanh.
**Chấp nhận:** trình soạn hiện chú thích của bài (và nghĩa Wiktionary khi có) cạnh ô Hiểu nhanh; nháp máy nằm ở ô riêng ghi rõ "Nháp máy — chưa duyệt", không bao giờ tự vào đoạn trích, chỉ lên trang khi người sáng lập bấm dùng (sửa được trước khi lưu); dấu "có hỗ trợ AI" do người sáng lập đặt khi dùng nháp, máy không đặt thay; nháp do agent soạn theo lô cho danh sách bài người sáng lập chọn (ghi lên máy chủ chỉ khi người sáng lập đồng ý cho lần đó — `AGENTS.md`); Bàn biên tập có hàng "Viết Hiểu nhanh" đếm N/50.
**Tiến độ (26/10/2026):** xong — plugin 0.28.0 (`inc/hieu-nhanh.php`). Hộp **Viết Hiểu nhanh** trong trình soạn: chú thích của bài; nháp máy (meta ẩn `_cadao_nhap_hieu_nhanh`, chỉ đọc, ghi "Nháp máy — chưa duyệt"), nút "Dùng nháp này" chép vào ô Tóm tắt và đánh dấu ô "có hỗ trợ của AI" ngay trước mắt người biên soạn (bỏ được), chỉ lên trang khi họ bấm Cập nhật; "Xoá nháp máy khi lưu". Ô "có hỗ trợ của AI" (`_cadao_ho_tro_ai`) trước đây chưa có trong trình soạn — nay người biên soạn tự bật/tắt; lưu không qua hộp (lưu nhanh, REST, máy) không đổi dấu. Trang **Viết Hiểu nhanh**: X/50, bài đọc nhiều 30 ngày trước, cột nháp. Bàn biên tập: hàng "Viết Hiểu nhanh (đã có X/50)" ở Làm trước. Lệnh agent: `wp cadao hieu-nhanh-ds [--so=] [--ids=]`, `wp cadao hieu-nhanh-nhap <tệp.json>` (chỉ bài đang đăng chưa có Hiểu nhanh; không đụng ô Tóm tắt, trạng thái, dấu AI). Nháp không ra trang, mô tả, REST công khai. Nghĩa Wiktionary thêm khi T-098 xong. Hướng dẫn: `docs/huong-dan/viet-hieu-nhanh.md`.

#### T-101 · Gộp bài đã đăng bị trùng — N+A
**Đọc:** `25` mục 4.9 (ý 49, thay ý 17). **Việc:** mỗi đêm quét các bài đã đăng, đưa cặp nghi trùng vào hàng "Có thể trùng"; khi người sáng lập gộp: chọn bài chính, bài kia thành dị bản của bài chính, đường dẫn cũ chuyển hướng 301 về bài chính, lượt đọc cộng dồn, cảm nghĩ và bài phân tích chuyển sang bài chính, bài phụ vào thùng rác (không xóa hẳn). **Chấp nhận:** người sáng lập duyệt từng lần; sau khi gộp, link cũ mở đúng bài chính; không mất cảm nghĩ, bài phân tích, lượt đọc nào.

**Tiến độ (25/10/2026):** xong — plugin 0.15.0 (`inc/gop-bai.php`). Cặp bài–bài trong trang **Có thể trùng** có hai nút **Gộp: giữ "<tên bài>"** (người sáng lập chọn bài nào giữ lại). Khi gộp: lời và dị bản của bài phụ thành dị bản của bài chính (đúng từng ký tự, lời giống hệt thì bỏ); chú thích tự viết (không có dòng Nguồn) giữ ở bài chính (ô `_cadao_chu_thich_them`, hoặc nối vào cuối nếu bài chính đã sửa tay); Hiểu nhanh (kèm dấu "có hỗ trợ AI"), nguồn đã ghi, "hợp khi" lấy từ bài phụ nếu bài chính chưa có; chủ đề, vùng, tình huống, mục từ gộp lại; lượt đọc cộng dồn theo ngày; cảm nghĩ và bài phân tích chuyển sang bài chính; câu trong kho của bài phụ thành "Đã gộp" vào bài chính (bài chính chưa sửa tay thì nhận cả chú thích có nguồn); tên cũ ghi vào `_wp_old_slug` nên đường dẫn cũ chuyển 301 về bài chính; bài phụ vào thùng rác (khôi phục được). Mỗi đêm máy so lại các bài đã đăng (tối đa 60 giây mỗi đêm) để đưa cặp mới vào hàng chờ.

#### T-102 · Bối cảnh vè — N+A
**Chấp nhận:** khối bối cảnh (sự kiện, năm, nơi chốn, nhân vật qua Wikidata đã xác nhận); tóm tắt do người sáng lập viết hoặc duyệt; máy không tự viết sự kiện.

#### T-103 · Đồng dao — A
**Đọc:** `25` mục 4.4. **Chấp nhận:** khối "Cách chơi" (ghi nguồn Wikipedia CC BY-SA khi lấy từ đó); trang trò chơi; chế độ "Đọc cùng bé" mỗi lần một dòng, chữ to, không tự phát âm thanh.

**Tiến độ (25/10/2026):** xong — plugin 0.17.0 (`inc/dong-dao.php`, `assets/dong-dao.js`). Phân loại **Trò chơi** (`tro_choi`, `/tro-choi/<tên>/`): mỗi trò chơi có số người, độ tuổi, đồ dùng, các bước (mỗi dòng một bước), nguồn và giấy phép khi chép từ nơi khác. Nút **Lấy mục "Cách chơi" từ Wikipedia** (trang sửa trò chơi): máy chép nguyên văn mục "Cách chơi" / "Luật chơi" của bài Wikipedia tiếng Việt người sáng lập gõ tên, ghi nguồn đúng bài đó và CC BY-SA 4.0; chỉ nhận vi.wikipedia.org; máy không tự viết cách chơi. Bài đồng dao gắn trò chơi có khối **Cách chơi: <tên>** sau hàng nút (dòng "Nguồn: Wikipedia — <bài> ↗ · CC BY-SA 4.0" khi chép); trang trò chơi có Cách chơi rồi các bài; trang thể loại Đồng dao có lối vào "Theo trò chơi"; trang Nguồn liệt kê nguồn của cách chơi. **Đọc cùng bé** (chỉ bài đồng dao): mỗi lần một dòng, chữ rất to, chạm vào chữ / mũi tên / nút sang dòng, nút "Vỗ tay theo chữ" tô từng chữ theo nhịp (0,7 giây), không âm thanh, Esc thoát; tệp JavaScript riêng chỉ tải ở bài đồng dao. Việc của người sáng lập: tạo trò chơi (Ca dao – Tục ngữ → Trò chơi), lấy cách chơi từ Wikipedia hoặc tự viết, gắn bài đồng dao vào trò chơi.

#### T-104 · Bản đồ ca dao — A
**Chấp nhận:** bản đồ các địa danh đã xác nhận, chạm ra bài; tải nhẹ trên điện thoại; không gọi dịch vụ bản đồ khi người đọc chưa mở trang bản đồ.

**Tiến độ (25/10/2026):** xong — `inc/ban-do.php`. Trang **Bản đồ ca dao** (`/ban-do/`): ảnh SVG tự lưu (7 KB, có Hoàng Sa, Trường Sa) với điểm các địa danh đã xác nhận trên Wikidata có tọa độ và có bài đang đăng; điểm là liên kết (≥ 24 px, tên đọc được) tới mục của địa danh trong danh sách dưới bản đồ (từ Bắc vào Nam, mỗi nơi các bài nhắc tới, "Xem trong từ điển"); điểm gần nhau (< 7% bề rộng) gộp thành một điểm có số. Không JavaScript, không gọi dịch vụ bản đồ nào kể cả khi mở trang (đi xa hơn tiêu chí). Liên kết tới bản đồ: trang chủ ("Xem ca dao trên bản đồ"), chân trang, trang mục từ có tọa độ ("Xem trên bản đồ ca dao"). Bản đồ đầy dần khi người sáng lập xác nhận thêm Wikidata (25/10/2026: 24 địa danh).

#### T-105 · Giọng đọc sinh sẵn — N+A
**Đọc:** `25` mục 2.4, 4.8; D-063. **Chấp nhận:** VieNeu-TTS v3 Turbo trong tài khoản `cadao`, chạy ban đêm ưu tiên thấp; người sáng lập nghe 30 bài đủ thể loại và đạt trước khi sinh cả kho; giọng theo vùng; có dòng "Giọng đọc máy"; chỉ tải khi bấm nghe; bài sửa lời tự sinh lại.

#### T-106 · Giọng riêng — N
**Chấp nhận:** người được thu ký giấy đồng ý ghi phạm vi dùng; không dùng giọng có sẵn mang tên giống người thật nổi tiếng.

#### T-107 · Tìm "Tục ngữ phong dao" — N
**Việc:** tìm bản quét trên Gallica, Google Sách, thư viện; agent soạn hướng dẫn tìm khi cần. **Chấp nhận:** có đường dẫn bản quét rõ đọc được, hoặc ghi lại nơi đã tìm.

#### T-140 · Sao lưu hằng ngày — N
**Tiến độ (27/10/2026):** xong — người sáng lập xác nhận FlashPanel sao lưu hằng ngày; quyết không thử khôi phục (D-090). Trước ngày chuyển (T-157), người sáng lập xác nhận có bản sao lưu đêm trước.

#### T-053 · Mở công khai lặng lẽ — N (Human Gate H5)
**Chấp nhận:** 100–150 bài `cong_khai`, 0 LỖI; thư đã gửi Ca dao Mẹ (T-034); trang Phương pháp và thông báo quyền riêng tư đã duyệt; người sáng lập quyết định mở, không thông báo rộng.
**Ghi chú (D-077, D-090, D-091):** điều kiện bắt buộc còn: sao lưu hằng ngày (T-140, xong), đã chuyển sang V2 (T-157) và quyết định của người sáng lập; các điều trên nên có, không chặn. Mở ngay sau khi chuyển (D-083).

### V2 · Xây mới (D-082..D-091)

Mọi task V2 đọc AGENTS.md và `docs/26`; task trang người đọc đọc thêm `docs/27`; task nguồn, kho, quản trị đọc `docs/28`; công cụ nhập và ngày chuyển đọc `docs/29`. Mã ở `wordpress/v2/`; mỗi PR tăng phiên bản plugin / theme V2; chạy `npm run kiem-tra`, `npm run test:v2`, `npm run quet:v2`; PR kèm link `npm run xem-truoc:v2`.

#### T-139 · Tài liệu V2 — A
**Chấp nhận:** D-082..D-091 trong `docs/01`; `docs/00` bản V2 (bản MVP ở `docs/luu-tru/00-tai-lieu-nen-mvp.md`); `docs/26`–`29`; ba nghiên cứu V2 ở `docs/nghien-cuu/`; backlog, `docs/02`, `trang-thai.json`, `viec-tiep-theo` nhận nhóm V2; AGENTS.md cập nhật sự kiện đo (D-088) và quy tắc xây V2; `npm run kiem-tra` đạt.
**Tiến độ (27/10/2026):** xong — chỉ tài liệu và công cụ backlog, không đổi mã plugin, theme.

#### T-165 · Bộ thiết kế V2 — N+A
**Chấp nhận:** người sáng lập chọn bảng màu và bản mẫu tham khảo (D-092); `design/v2/` có mã nguồn, token, đặc tả, `qa/`; `node source/build.mjs` và `python -X utf8 source/package-handoff.py` chạy được; 0 lỗi kiểm tĩnh, 0 cặp màu dưới ngưỡng tương phản, 0 trang tràn ngang ở 320 / 390 / 1440; bản dựng, ảnh chia sẻ và hai bản mẫu HTML không vào repo.
**Tiến độ (02/11/2026):** xong — bản chốt 2.1 (D-092, D-093): mọi lựa chọn đã chốt; kiểm thêm "mỗi câu một dòng" và "nhãn nút không gãy" ở 320 / 360 / 390 / 768 / 1440 đạt, 13 luồng tương tác đạt. **Bổ sung (02/11/2026, D-094):** nhóm màn biên tập B00–B05 thành bản chốt cho phần biên tập; 21 màn Bàn biên tập kiểu cũ (nhóm A) giữ để tham khảo, không làm.

#### Đợt 1 · Nền (T-141..T-145)
- **T-141 (xong 02/11/2026):** plugin `Phongdao — Thư viện Văn học Dân gian Việt Nam` 0.1.0 (tự nạp `Phongdao`, 5 module khung: cài đặt, REST `phongdao/v1/suc-khoe`, khối, `wp phongdao phien-ban`, menu Phongdao; lớp miền đầu tiên `MienDuLieuBoDau` chuyển từ MVP), theme khối `Phongdao` 0.1.0; `test:v2` (PHP thuần + Playground PHP 8.3), `quet:v2`, `xem-truoc:v2`, workflow `v2.yml`. Yêu cầu gốc: đo phiên bản PHP của máy chủ trước khi chọn cú pháp (`docs/26` mục 2); `npm run test:v2` chạy bộ kiểm PHP thuần < 5 giây và Playground; `xem-truoc:v2` mở được plugin + theme V2 với dữ liệu mẫu; job CI `v2`. **Chấp nhận:** cây thư mục đúng `docs/26` mục 3; MVP không đổi; CI xanh.
- **T-142:** **Chấp nhận:** đủ loại bài, phân loại, các phần của bài và bảng việc ở `docs/26` mục 4 (D-094); mọi lần ghi một phần đi qua `LuuPhan` + `KiemPhan`, ghi một dòng nhật ký, hoàn tác được; kiểm thử khẳng định ràng buộc lằn ranh mục 4.3 và `docs/28` mục 6 (nháp không vào bài, lời không nhận người làm AI, nguồn không tự điền, địa chỉ gốc máy không ra HTML khách); `CapChatLuong` cho đúng L0–L3 (bài phân tích không tính); SQL chạy trên SQLite và MySQL.
  **Tiến độ (02/11/2026):** xong — plugin V2 0.3.0. `HaTang\KhaiBao`: loại bài `tac_pham` (không trình soạn khối, không REST, không bản sửa), `tuyen_tap`, `phan_tich`, `gop_y`, `ho_so_nguon` (ẩn); 7 phân loại; khoá meta `_phongdao_<phần>` cho 18 phần của tác phẩm và 3 phần của tuyển tập; đường dẫn `/<thể loại>/<tên>/` (đường dẫn khác 301 về đây). `HaTang\CaiDat` bước 2: bảng `wp_cadao_viec`, 5 thể loại cố định; nâng cấp chạy ở `init` (máy chủ chép tệp, không bấm Kích hoạt). `MienDuLieu\Phan`, `KiemPhan` (lằn ranh khi lưu), `CapChatLuong`, `BanChu`; `KhoLuu\PhanBai`, `KhoLuu\Viec`; `DichVu\LuuPhan` (lưu / xoá / khôi phục một phần, mỗi lần một dòng nhật ký, hỏng giữa chừng thì trả lại). Bỏ thư mục `quan-tri/`. Kiểm thử: PHP thuần 83 phép (thử tắt một lằn ranh thì phép kiểm hỏng), Playground thêm 27 phép. MySQL: bảng dùng cùng dạng `dbDelta` như các bảng MVP đang chạy trên máy chủ; chưa chạy thử riêng trên MySQL. Hồ sơ nguồn (trường, chính sách hiện) làm ở T-143; trang người đọc đọc dữ liệu thật làm tiếp theo.
- **T-143:** **Chấp nhận:** bộ đọc Ca dao Mẹ và chống trùng cho cùng kết quả với MVP trên mọi ca kiểm thử của MVP; câu mới theo `docs/28` mục 3.3; kho không ra trang người đọc.
  **Tiến độ (02/11/2026, phần 1/3 — plugin 0.7.0):** kho `wp_cadao_kho`, bộ đọc Ca dao Mẹ (`DichVu\NhapNguon\CadaoMe`, bộ tách lời `MienDuLieu\TrichHtml` — chuyển nguyên MVP kèm ca kiểm, chạy trên nguồn giả), nguồn tự động (`NguonTuDong`) giữ nguyên tên tuỳ chọn `cadao_nguon_tu_dong`, tên lịch `cadao_thu_thap` / `cadao_tao_bai_hang_ngay` và cấu trúc bảng kho của MVP để sau ngày chuyển máy đọc chạy tiếp đúng điểm dừng; hồ sơ nguồn (loại bài ẩn, `MienDuLieu\HoSoNguon`), hồ sơ Ca dao Mẹ mặc định (chú thích chép ẩn, xin phép "chưa"); lệnh `wp phongdao nguon [<id>]`. **Phần 2/3 (plugin 0.8.0):** chống trùng (`MienDuLieu\VanTay` chuyển nguyên, 10/10 ca kiểm MVP đạt; `DichVu\ChongTrung` giữ ba bảng và khoá mục của MVP nên mọi quyết định cũ mang sang): lời y hệt cùng thể loại / trùng 100% một dị bản đã ghi → tự gắn ("Tách ra" được), giống một phần → việc "Có thể trùng" trong Hộp việc; câu mới (`DichVu\UngVien`): mỗi ngày máy chọn câu đã đọc trang thành việc "Câu mới" (xoay vòng thể loại, bỏ câu đang chờ quyết trùng, bỏ câu mang cờ nguồn chọn bỏ qua) — không tạo bài; Đăng / Sửa rồi đăng / Bỏ; Đăng chép lời, dị bản, chú thích (chỉ khi nguồn bật Chép chú thích), câu hỏi, đáp án câu đố với người làm máy chép + hồ sơ nguồn, không điền trang / đường dẫn; kho đổi thì tác phẩm chỉ cập nhật phần còn hoàn toàn do máy chép. Lệnh `wp phongdao cau-moi`. Còn: gợi ý chủ đề / vùng / mô-típ / Wikidata (phần 3).
- **T-144:** **Chấp nhận:** bộ truy vấn có dấu / không dấu / sai một chữ (gồm L-09) đạt; câu không có trong kho không ra bài lạc đề; kho có câu thì hiện "đang chờ biên soạn" mà không lộ lời kho.
  **Tiến độ (02/11/2026, plugin 0.6.0):** chỉ mục `wp_cadao_tim` (giữ cấu trúc MVP) dựng từ các phần đã lưu: phần chính tên + lời + dị bản, phần phụ ý nghĩa + nghĩa + chú thích; ghi lại khi lưu phần, đổi tên, đổi trạng thái; dựng lại theo lô (WP-Cron, `wp phongdao tim --dung-lai`). Thuật toán xếp hạng chuyển nguyên từ MVP (`MienDuLieu\ChiMuc`, ca kiểm MVP chuyển theo) và trả cờ khớp cả cụm; gõ sai một chữ cái → "Có phải bạn tìm …?" (từ vựng âm tiết của chỉ mục). Trang `/tim/` (trang cố định do plugin khai báo, không tạo trang trong cơ sở dữ liệu; `?s=` chuyển về đây; trang kết quả `noindex`): thẻ lớn khi khớp cả câu, nhóm theo thể loại, Từ điển, nhóm "không liền nhau"; kết quả hiện khi gõ qua REST `phongdao/v1/tim` (cùng HTML). Trang 404 có ô tìm điền sẵn chữ từ đường dẫn. Còn: "Câu này đang chờ biên soạn" + "Mong có câu này" khi kho có câu (làm cùng nguồn vào), đo `tim_khong_ket_qua` (làm cùng đo lường).
- **T-145:** **Chấp nhận:** theo `docs/29` mục 2–4 — kiểm kê đối chiếu đủ với mã MVP; nhập → hoàn tác → nhập cho cùng kết quả trên dữ liệu mẫu; "trước = sau" từng ký tự; báo cáo không chép lời.
  **Tiến độ (03/11/2026, phần 1/3 — plugin 0.11.0):** module `DichVu\NhapDuLieuCu`. `KiemKe` đếm từng dòng bảng `docs/29` mục 2 bằng truy vấn trực tiếp (chạy được khi MVP còn bật lẫn khi V2 đã thay chỗ) và liệt kê khoá meta / term meta / tuỳ chọn `cadao_` chưa có trong bảng; `wp phongdao kiem-ke`. `TachBai` (PHP thuần) đọc nội dung bài đúng như theme MVP: khối Thơ đầu = lời, khối Thơ sau "Dị bản" = dị bản, danh sách `cadao-chu-thich` (lớp `cadao-chep` = máy chép), "Đáp án", dòng in đậm sau lời của câu đố; bài chưa có khối Thơ lấy lời từ khối Đoạn văn theo đúng quy tắc nút "Đưa lời vào khối Thơ" (không đổi chữ; dòng chú giữ riêng); khối lạ → "Cần xem" kèm lý do. `DungPhan` (PHP thuần) dựng các phần: người làm so với câu trong kho (câu chính + câu đã gộp), không đoán; nguồn chỉ từ ô người điền (hồ sơ mới cho mỗi tên nguồn, đối chiếu → hồ sơ sách + trang); Hiểu nhanh → ý nghĩa giữ dấu AI; nháp máy → việc chờ; mọi phần qua `KiemPhan` (gọi `nhap_cu`); "trước = sau" từng ký tự (chỉ khác khoảng trắng / Unicode → ghi chú); tên đuôi `-2` → thêm chữ của dòng sau. `NhapThu` = `wp phongdao nhap --thu`; tệp chỉ đọc `wordpress/v2/cong-cu/thu-nhap.php` (`wp eval-file`, giao dịch huỷ ở cuối) cho site thật khi được đồng ý. Kiểm: 28 phép PHP thuần; Playground mới `tests/chuyen-doi/` — plugin MVP tạo 10 bài mẫu bằng mã MVP (từ kho, sửa tay, câu gộp, câu đố, tục ngữ có Hiểu nhanh AI, nháp, chờ duyệt, vè trong Đoạn văn, nguồn người điền + đối chiếu, tên đuôi số), chạy tệp chạy thử khi MVP bật (không ghi dòng nào), rồi V2 thay chỗ và kiểm cùng kết quả. **Còn:** nhập thật / hoàn tác (`wp_cadao_chuyen_doi`, thẻ "Cần xem" trong Hộp việc), mục từ, trò chơi, tình huống → tuyển tập, mô-típ, Bạn có biết, cài đặt, bảng 301.
  **Tiến độ (03/11/2026, phần 2/3 — plugin 0.12.0):** `NhapLam` = `wp phongdao nhap --lam [--lo=50]` / `--hoan-tac`. Mỗi bài MVP thành tác phẩm giữ ID (đổi loại bài, trạng thái — chờ duyệt có người sửa thành nháp —, tên đường dẫn đuôi số + `_wp_old_slug`, thể loại), các phần lưu qua `LuuPhan` gọi `nhap_cu` (mỗi phần một dòng nhật ký, giờ = lúc sửa cuối trên MVP, người = người sửa cuối), nháp → việc "Nháp ý nghĩa", lý do cần xem → thẻ mới **Cần xem** trong Hộp việc (nút "Đã xem, ổn rồi", mở bài); bài chờ duyệt máy tạo không mang, câu kho trở lại "mới" để thành Câu mới. Mỗi bài một giao dịch; giá trị cũ (nội dung, ngày, tên, thể loại, câu kho) và hồ sơ tạo mới ghi vào `wp_cadao_chuyen_doi`; hoàn tác đặt lại, xoá phần `_phongdao_*`, việc / nhật ký do bước nhập tạo, hồ sơ mới. Bước cài 7: site có bài MVP đã đăng → trạng thái "chờ nhập", khách nhận trang "Thư viện đang được nâng cấp" (503, Retry-After) tới khi nhập xong. Kiểm Playground chuyển đổi: 11 bài mẫu, các phần đúng kế hoạch nhập thử, trang tác phẩm hiện lời / dị bản gộp / chú thích, cảm nghĩ vẫn nối bài, Hộp việc có thẻ Cần xem; chạy lại không đổi gì; hoàn tác trả dữ liệu MVP về đúng như trước; nhập → hoàn tác → nhập cho cùng kết quả.
  **Tiến độ (03/11/2026, phần 3/3 — plugin 0.13.0):** `NhapPhanLoai` chạy một lần sau khi mọi bài đã nhập (cùng giao dịch, ghi bảng chuyển đổi để hoàn tác): `tu_kho` đổi tên tại chỗ thành `muc_tu` (giữ ID, `/tu-dien/<tên>/` như cũ) + term meta V2 (`docs/26` mục 4.2); chú thích của bài nối `muc_tu_id` theo khoá MVP (từ + lời giải); trò chơi → `_phongdao_cach_choi` (chép từ Wikipedia = máy chép, hồ sơ "Wikipedia tiếng Việt"); mô-típ → `_phongdao_mo_tip_dau`; tình huống có bài → tuyển tập loại `tinh_huong` (lời dẫn, mục theo tên bài, ghi chú "Hợp khi…", đăng khi ≥ 2 bài như MVP); "Bạn có biết" đã duyệt → phần `ban_co_biet`; quyết định Wikidata → việc `wikidata` đã quyết. Tên nguồn người điền trùng tên nguồn tự động (vd. "Ca dao Mẹ") dùng hồ sơ có sẵn. `HaTang\ChuyenHuong`: khi 404, `/tinh-huong/<tên>/` → tuyển tập, `/the-tho/<tên>/` → `/hinh-thuc/<tên>/`, `/the-loai/thanh-ngu/` → Tục ngữ – Thành ngữ, `/thanh-ngu/<tên>/` và `/bai/<tên>/` → tác phẩm (tên đổi, bài đã gộp: `_wp_old_slug` của WordPress). Báo cáo nhập thử thêm dòng "Sau các bài". Bộ chủ đề, ánh xạ chủ đề, mô-típ đã đặt / bỏ qua: giữ tuỳ chọn MVP cùng tên. **T-145 đủ phần máy làm**; chạy thử trên site thật (chỉ đọc) chờ người sáng lập đồng ý — thuộc T-156.

#### Đợt 2 · Trang đọc (T-146..T-151)
**Chấp nhận chung:** đúng `docs/27` và bộ thiết kế `design/v2/` (D-092) từng màn hình (thứ tự thành phần, trạng thái trống / lỗi); mỗi mẫu thể loại ≤ 200 KB, JavaScript trang bài ≤ 21 KB, tuyển tập 30 câu ≤ 300 KB; 0 lỗi axe; tắt JavaScript vẫn đọc được; câu đố có nút "Hiện đáp án" bằng `<details>` (D-087); Thông tin tư liệu mặc định đúng D-068 + D-078 + D-081 (liên kết nguồn ẩn không có trong HTML gửi người đọc); khối Biên soạn chỉ hiện khi có bút danh (T-136 cũ); trang chủ đề / vùng không có giới thiệu do máy viết (T-137 cũ); bối cảnh vè không do máy viết (T-102 cũ); nghĩa từ Wiktionary ghi "Nguồn: Wiktionary ↗ · CC BY-SA" ngay dưới (T-098 cũ); Đố vui 10 câu một lượt, nhớ trên máy, không chấm điểm (T-096 cũ); đúng 5 sự kiện đo (D-088); kiểm liên kết ra ngoài trong CI.

**Tiến độ (02/11/2026) — khung giao diện người đọc:** theme V2 lấy CSS và `fit.js` từ `design/v2` bằng `npm run dong-bo:giao-dien-v2` (có chế độ tối theo cài đặt máy), phông woff2 cắt gọn của MVP; khối `cadao/dau-trang` (đầu trang + thanh thể loại), `cadao/chan-trang` (chân trang + thanh dưới), `cadao/tac-pham` (thẻ lời, nhãn mức kiểm, Ý nghĩa, công cụ đọc, Từ khó, Dị bản, giải nghĩa tục ngữ, Lời này từ đâu?, Ảnh để chia sẻ) đọc dữ liệu mẫu; `doc.js` (sáng / tối, cỡ chữ, sao chép, Lưu, Trích dẫn, Chia sẻ). Còn: Đọc tiếp, Bạn muốn góp gì?, Trình chiếu, Đọc cùng bé, ảnh PNG, phần riêng của vè (đoạn, bối cảnh) và đồng dao (trò chơi); trên trang chủ: Đọc theo dịp, Tuyển tập nổi bật (tự hiện khi có tuyển tập), Bạn có biết?, Đọc nhiều tuần này (khi có lượt đọc). **Tiến độ (02/11/2026) — trang đọc dữ liệu thật (plugin 0.4.0, theme 0.3.0):** trang tác phẩm đọc các phần đã lưu (`DichVu\DungTrang\DocBai` lấy dữ liệu, `TrangTacPham` dựng — không đọc địa chỉ gốc máy đã lấy; nguồn chú thích máy chép ẩn, nguồn người biên soạn điền thì hiện; “Đã đối chiếu” chỉ khi có sách); câu đố có Gợi ý và “Hiện đáp án” (`<details>`); máy gắn thể thơ vào Hình thức khi lưu lời (chuyển thuật toán MVP kèm ca kiểm); mẫu theme `single-tac_pham`, `archive-tac_pham`, `taxonomy`; khối `cadao/danh-sach`: Tất cả `/kho/` có bộ lọc bằng form GET (thể loại, chủ đề, hình thức, nơi lưu truyền, A–Z / mới thêm — tắt JavaScript vẫn lọc), trang thể loại / chủ đề / vùng / mô-típ / hình thức, 30 bài mỗi trang, phân trang. Bản xem trước có 6 tác phẩm mẫu tổng hợp (lưu qua đường lưu có kiểm lằn ranh); bỏ hai trang mẫu cũ. **Trang chủ (plugin 0.5.0, theme 0.4.0):** mẫu `front-page`, khối `cadao/trang-chu`: tên thư viện, ô tìm, “Bạn là” (chỉ đổi thứ tự khối, nhớ trên máy, ẩn khi tắt JavaScript), ba lối tìm, Câu hôm nay từ bài thật (giữ cách MVP: mỗi ngày một bài 1–4 câu, ai mở cũng thấy cùng câu; danh sách đệm 6 giờ, làm mới khi lời hoặc trạng thái bài đổi), Bắt đầu từ đâu, Đọc theo thể loại với số bài thật (thể loại 0 bài ẩn), Vừa bổ sung, dải Phương pháp. **Sửa lỗi (02/11/2026, theme 0.2.1):** trên CI, khoảng 3 / 9 lần quét thấy trang bài mẫu tràn ngang 16 px ở khổ điện thoại lúc vừa tải — vế tục ngữ / câu lời vượt mép vì `fit.js` chỉ co chữ lại một lần khi `document.fonts.ready` xong, phông của lời nạp ở đợt sau thì không co nữa. Nay co lại sau mỗi đợt nạp phông (`loadingdone`); `quet:v2` đo tràn ngang hai lần (vừa tải, sau khi nạp phông), mỗi lần chờ hai khung hình như trình duyệt vẽ (fit.js co chữ trước khi vẽ; trước đây phép quét đo giữa hai khung hình — trạng thái không hiện trên màn hình), và in phần tử gây tràn.
**Tiến độ (03/11/2026) — Tuyển tập, Từ điển, Nguồn (plugin 0.14.0, theme 0.7.0):** khối `cadao/tuyen-tap` (R11: lời dẫn, số câu · phút đọc, mục lục khi chia nhóm, mỗi câu có lời, ghi chú "Hợp khi…" với tuyển tập Tình huống, Ý nghĩa, nhãn mức kiểm gọn, nút sao chép, Xem bài; tuyển tập khác) và `cadao/ds-tuyen-tap` (R10: `/tuyen-tap/`, lọc theo loại bằng đường dẫn, trạng thái trống). Công khai khi đã đăng, có lời dẫn và ≥ 8 câu đã đăng; **tuyển tập Tình huống giữ ngưỡng 2 câu như MVP** để các trang tình huống đang hiện không biến mất khi chuyển; chưa đủ thì khách nhận 404, người biên soạn vẫn xem. Trang cố định `/tu-dien/` (R13, che trang MVP cùng tên): tìm không dấu (form GET), 29 chữ cái tiếng Việt (bỏ dấu thanh, giữ ă â ê ô ơ ư đ), mặc định 30 mục từ hay gặp; trang mục từ là trang phân loại `/tu-dien/<tên>/`. Mã ngắn `[cadao_nguon]` (trong trang Nguồn, Chính sách thu thập dữ liệu mang từ MVP) đọc nguồn tự động và hồ sơ nguồn V2. Bản xem trước có mục từ và tuyển tập mẫu; `quet:v2` thêm 5 trang. **Còn mã ngắn MVP trong trang tĩnh:** `[cadao_da_luu]`, `[cadao_ban_do]`, `[cadao_viet_phan_tich]` — làm cùng các trang Đã lưu, Bản đồ, Viết cảm nhận (trang cố định cùng đường dẫn sẽ che trang MVP).
**Tiến độ (03/11/2026) — Người đọc góp (plugin 0.15.0, theme 0.8.0):** trang cố định `/gop/` (R22, khối `cadao/gop`): một form cho mọi loại — ở quê nói khác, góp ý chú thích, báo lỗi, gửi số trang, cảm nghĩ (khi gắn bài), viết cảm nhận, báo vi phạm (chỉ từ liên kết dưới cảm nghĩ); chạy không cần JavaScript. `DichVu\NguoiDoc\NhanGui`: ô bẫy, ≤ 5 lần / giờ (dấu băm địa chỉ mạng + giờ trong bộ nhớ tạm một giờ, `MienDuLieu\DiaChiMang` chỉ tin CF-Connecting-IP khi yêu cầu đến từ Cloudflare), Turnstile khi có khoá (tuỳ chọn `cadao_turnstile_*` giữ tên MVP; mã Cloudflare chỉ nạp ở trang có form). Góp ý → `gop_y` chờ duyệt (khoá meta MVP); cảm nghĩ → bình luận chờ duyệt **không ghi địa chỉ mạng, trình duyệt, email** (đường bình luận mặc định của WordPress đóng với tác phẩm); bài cảm nhận → `phan_tich` chờ duyệt. Mỗi lần gửi thành thẻ **Người đọc gửi** trong Hộp việc: cảm nghĩ, bài cảm nhận có "Hiện trên trang" (cần quyền duyệt); góp ý có "Đã xử lý". Trang bài: khối "Bạn muốn góp gì?" (6 lối) và cảm nghĩ đã duyệt kèm "Báo vi phạm". Mã ngắn `[cadao_viet_phan_tich]` của trang "Viết cảm nhận" MVP hiện form mới. Còn: trang bài cảm nhận (R23), Đã lưu, Bản đồ.
**Tiến độ (03/11/2026) — Đã lưu, bài cảm nhận (plugin 0.16.0, theme 0.9.0):** nút Lưu và trang cố định `/da-luu/` (R21, che trang MVP cùng tên) dùng **cùng khoá bộ nhớ trình duyệt và dạng dữ liệu với MVP** (`cadao-da-luu`, `{id, ten, url, loai, dong}`) — bài người đọc lưu trước khi chuyển vẫn còn (cùng tên miền, bài giữ ID); trang đọc danh sách trên máy, lọc thể loại, bỏ lưu (tiêu điểm sang nút kế), chỉ nhận đường dẫn của chính trang, chữ đặt bằng `textContent`, không gửi gì về máy chủ; tắt JavaScript hoặc bộ nhớ bị chặn thì báo lý do. Trang bài cảm nhận `/phan-tich/<tên>/` (R23): người viết (bút danh · vai, hoặc "Bài phân tích của thư viện"), nội dung, tác phẩm đang bàn, Báo vi phạm; cuối trang bài liệt kê bài cảm nhận đã duyệt. Còn: Bản đồ (`[cadao_ban_do]`).
**Tiến độ (03/11/2026) — Bản đồ (plugin 0.17.0, theme 0.10.0):** trang cố định `/ban-do/` (R15, che trang MVP cùng tên — hết trang tĩnh MVP còn mã ngắn chưa có ở V2): danh sách địa danh đã xác nhận Bắc → Nam (nhãn "Được nhắc đến", mô tả Wikidata chỉ khi là tiếng Việt, các bài nhắc đến) và bản đồ Việt Nam tự lưu của MVP (`assets/ban-do-vn.svg`, có Hoàng Sa, Trường Sa) với điểm là liên kết tới mục trong danh sách, điểm gần nhau gộp; không gọi dịch vụ bản đồ, không cần JavaScript. Đọc toạ độ từ `_phongdao_wikidata` (bước nhập chép từ `_cadao_wd`).
**Tiến độ (03/11/2026) — Nguồn & cài đặt (plugin 0.18.0; T-155 phần wp-admin):** menu Phongdao → trang Nguồn & cài đặt, chỉ quyền `cadao_nguon` (Quản trị; Biên tập thấy lối sang Hộp việc). Ba thẻ: **Nguồn tự động** (bật / tắt, số câu mới mỗi ngày 1–50, loại lấy, chép chú thích, bỏ câu thô tục; số câu trong kho; 8 dòng nhật ký máy gần nhất), **Hồ sơ nguồn** (danh sách; sửa tên, loại, đường dẫn, tác giả, năm, giấy phép, ghi công, xin phép, chính sách hiện, chép chú thích, ghi chú — qua `HoSoNguon::chuan`, lựa chọn lạ thành "chưa rõ"), **Chống rác** (khoá Turnstile, khoá bí mật để trống thì giữ, không hiện lại). Form admin-post có nonce và kiểm quyền; lời báo sau khi lưu giữ một phút theo người dùng (không đặt chữ trong đường dẫn). Còn: bút danh biên soạn, bộ chủ đề và ánh xạ, sửa tại chỗ trang tuyển tập / mục từ / trò chơi.
**Tiến độ (03/11/2026) — Đo lường, an toàn (plugin 0.19.0, theme 0.11.0):** chuyển MVP `luot-doc.php`, `do-luong.php`, `an-toan.php`. Ba bảng giữ cấu trúc MVP (`wp_cadao_luot_doc`, `wp_cadao_gioi_thieu` — trang giới thiệu theo ngày, `wp_cadao_su_kien`; bước cài 8) nên số liệu nối liền qua ngày chuyển. REST `phongdao/v1/luot-doc/{id}`, `/su-kien`: lượt đọc khi trang bài hiện 5 giây, mỗi máy một lần mỗi ngày (bộ nhớ trình duyệt + dấu băm tạm địa chỉ mạng, không lưu), bỏ máy dò, chỉ ghi tên miền giới thiệu; sự kiện sao chép trích dẫn, chia sẻ, mở chú giải (bài + kiểu), tìm không ra (khi tải cả trang Tìm, không ghi lúc đang gõ; bỏ chuỗi giống email, số điện thoại). `assets/do-luong.js` chỉ nạp cho khách (người biên soạn đăng nhập không tính); `doc.js` vẫn không gọi máy chủ — chỉ phát sự kiện `phongdao:su-kien` trong trang. Hiện: số dưới tiêu đề bài (lượt đọc ≥ 10, chia sẻ, trích dẫn ≥ 3), "Đọc nhiều tuần này" trên trang chủ khi ≥ 3 bài, Số liệu tuần (7 ngày, bài đọc nhiều, người đọc đến từ, câu tìm chưa có). `HaTang\AnToan`: tắt XML-RPC, pingback; không lộ tên đăng nhập (REST danh sách người dùng, trang tác giả, oEmbed, sơ đồ trang); câu báo đăng nhập sai chung; tạm khoá 15 phút sau 5 lần sai, đếm theo tài khoản (cùng khoá MVP), không theo địa chỉ mạng. Sửa `docs/29` mục 2: `wp_cadao_gioi_thieu` là bảng trang giới thiệu của lượt đọc, không phải "Bạn có biết".
**Tiến độ (03/11/2026) — SEO (plugin 0.20.0; chuyển MVP `seo.php` theo quy tắc V2):** `GiaoTiep\Seo\Module`. Lập chỉ mục tác phẩm L2+ (có Ý nghĩa, hoặc nghĩa đen + nghĩa bóng), tuyển tập công khai, thể loại, trò chơi có bài, chủ đề / vùng / mô-típ / hình thức có ≥ 3 tác phẩm L2, mục từ có lời giải do dự án viết (lời giải chép → noindex như MVP); trang lọc, sắp xếp, tìm → noindex. Tiêu đề tác phẩm L2 "… — nghĩa và cách dùng"; tuyển tập "…: N câu có giải nghĩa" khi mọi câu có ý nghĩa. Mô tả (từ Ý nghĩa; chưa có thì hai dòng đầu + thể loại), thẻ og:*, JSON-LD (WebSite + SearchAction, Organization, CreativeWork — `isBasedOn` chỉ khi đã đối chiếu sách, DefinedTerm, CollectionPage, BreadcrumbList), canonical trang danh sách, sơ đồ trang chỉ gồm trang lập chỉ mục được (bỏ người dùng, bài viết, tệp, trang Đã lưu / Viết cảm nhận / Tìm của MVP); không lộ phiên bản WordPress. Không đổi `blog_public` (H5). Còn: ảnh chia sẻ PNG cho og:image, CSP.
**Tiến độ (03/11/2026) — Vè dài, chạy thử nhập trên site thật (plugin 0.21.0, theme 0.12.0):** chạy `cong-cu/thu-nhap.php` trên site thật lần 1 (người sáng lập đồng ý, chỉ đọc, giao dịch huỷ ở cuối; kết quả ở `docs/29` mục 4): "trước = sau" không lệch bài nào, nhưng 7 bài vè nhiều phần (tiêu đề phụ trên mỗi khối Thơ) không tìm được lời. Sửa công cụ nhập: các phần nối thành lời + phần `doan` có tiêu đề; dòng chú trước lời → `boi_canh.chu` (thêm trường `chu`); dòng "(Nguồn: …)" → "Cần xem", không tự gắn; "Dị bản" bị nút MVP chèn trên tiêu đề phụ → đúng dị bản; ô Nguồn không đường dẫn → hồ sơ loại "chưa rõ"; báo cáo ghi mỗi bài một lần cho mỗi lý do, ghi chú "Bạn có biết" đúng mã bài, cột canh theo ký tự. Trang bài: `MienDuLieu\ChiaDoan` (thuật toán chia đoạn của MVP) — bài từ 41 dòng có tóm tắt "N dòng · M đoạn · khoảng K phút đọc", mục lục, số dòng mỗi 5 dòng, đoạn sau dòng 30 thu gọn sau "Đọc toàn bài", "Đọc tiếp từ đoạn N" nhớ trên máy cùng khoá MVP (`cadao-doc-den-<id>`); bài có phần Đoạn hiện tiêu đề đoạn; bối cảnh hiện trên lời; chép lời gồm mọi khổ. Tấm sửa "Bối cảnh", "Chia đoạn" (chỉ bài vè). Vè mẫu thành bài dài 48 dòng, có trong quét.
**Tiến độ (03/11/2026) — Ảnh chia sẻ PNG (plugin 0.22.0, theme 0.13.0; thay MVP `anh-chia-se.php`):** `DichVu\AnhChiaSe\VeAnh` vẽ bằng GD đúng bộ thiết kế (3 khổ, 6 nền, hoạ tiết vẽ bằng mã, lời co vừa khung, bài dài 4 dòng đầu + "…", kèm ý nghĩa, câu đố "Lời giải ở …" không lộ đáp án, cờ "Nghi không phải dân gian"), phông TTF tự lưu (OFL). Thẻ og:image + twitter:card: ảnh ngang nền mặc định của thể loại, lưu `uploads/phongdao-anh/`, tự sinh lại khi lời / ý nghĩa / đường dẫn đổi; trang khác ảnh chung. Nút "Tải ảnh" thành form GET (chạy cả khi tắt JavaScript) → `/?phongdao_anh=<id>` trả PNG tải về, đệm 300 tệp; chỉ tác phẩm đã đăng, `noindex`; bấm tính vào `chia_se` (kiểu `tai_anh`, không thêm sự kiện). Máy không vẽ được chữ (PHP Playground) → không thẻ ảnh, nút chỉ nhắc chụp màn hình. CI cài GD để bộ kiểm thuần vẽ thử.
**Tiến độ (03/11/2026) — CSP, "Mong có câu này" (plugin 0.23.0, theme 0.14.0):** đầu trả lời bảo vệ theo `docs/26` mục 11 — Referrer-Policy, nosniff; khách có CSP không mã nội tuyến (đoạn sáng / tối của theme cho phép bằng dấu băm, cấu hình đo lường thành khối JSON, bỏ liên kết bỏ qua tự chèn của WordPress vì theme đã có); `quet:v2` kiểm có đầu CSP và không bị chặn gì. Sự kiện thứ năm `mong_co_cau` (D-088): Tìm không ra mà kho có câu chứa đủ các cặp âm tiết của cụm tìm (dùng bảng cặp âm tiết của chống trùng, không đọc lời kho ra trang) → "Câu này đang chờ biên soạn" + nút "Mong có câu này" (chỉ hiện khi có JavaScript; bấm → "Đã ghi nhận, cảm ơn bạn"); Số liệu tuần có mục "Người đọc mong có" theo tên câu trong kho.
**Tiến độ (03/11/2026) — Gợi ý chủ đề, vùng trong Hộp việc (plugin 0.24.0; thay trang "Gợi ý vùng, chủ đề" của MVP):** `MienDuLieu\GoiY` chuyển nguyên quy tắc MVP (vùng từ lời giải chú thích và toạ độ địa danh đã xác nhận; chủ đề từ chủ đề nguồn qua bảng ánh xạ đã duyệt `cadao_anh_xa_chu_de`, lối hát theo lời, kinh nghiệm của tục ngữ) và bộ 7 vùng, 4 nhóm chủ đề (bước CSDL 9 tạo mục thiếu). `DichVu\GoiY`: mỗi gợi ý một thẻ "Gợi ý chủ đề, vùng" kèm lý do (Đồng ý gắn · Để sau · Bỏ — máy không gợi ý lại); tính khi lưu lời, chú thích và một lượt theo lô cho mọi bài. Nhập dữ liệu cũ: tắt gợi ý khi đang nhập, nhập xong chạy một lượt; bài đã duyệt gợi ý trên MVP (`_cadao_goi_y_xong`) thành việc đã quyết cho cả bài; hoàn tác gỡ cả thẻ gợi ý máy tạo. Còn: mô-típ, Wikidata.
**Tiến độ (03/11/2026) — Chuẩn bị ngày chuyển (plugin 0.25.0):** `wp phongdao doi-chieu [--duong-dan]` cho bước 4 (`docs/29` mục 5): bước nhập lưu bản kiểm kê MVP lúc bắt đầu (`phongdao_kiem_ke_truoc`, hoàn tác xoá), lệnh in trước → sau cùng định nghĩa (đếm cả bài không mang, nên không mất gì thì khớp từng dòng) và thử 301 các đường dẫn cũ (bài đổi tên, đổi thể loại, tình huống cũ). `kiem-site-that` đọc được phiên bản V2 (`do-luong.js`, `giao-dien.css`) để không báo lệch sai sau ngày chuyển.
**Tiến độ (03/11/2026) — Đọc tiếp, Trình chiếu, Đọc cùng bé (plugin 0.26.0, theme 0.15.0; bù tính năng MVP đang có):** `DichVu\DungTrang\DocTiep` chuyển MVP `doc-tiep.php` (cùng mô-típ 6, chủ đề 4, mục từ có ở 2–20 bài 3, vùng 2; thiếu thì cùng thể loại đăng gần đó; mỗi thẻ có lý do), khối sau "Lời này từ đâu?". `che.js` (doc.js tải khi người đọc bấm lần đầu, ngoài ngân sách JavaScript mỗi trang): khung toàn màn hình dùng chung (bộ thiết kế `fullscreen-mode`) — Trình chiếu trang bài (mỗi lần hai câu như MVP; nút ở cột công cụ, trên điện thoại trong hộp Cỡ chữ), Trình chiếu cả bộ ở tuyển tập, Đọc cùng bé ở bài đồng dao (một dòng mỗi lần, chạm sang dòng, "Vỗ tay theo chữ" không âm thanh, tắt chuyển động khi máy đặt giảm chuyển động); phím mũi tên, Esc, giữ Tab trong khung; nút ẩn khi tắt JavaScript; `quet:v2` mở thử cả ba (axe trong khung, Esc trả tiêu điểm). Nút Nghe (giọng máy) của MVP không chuyển: `docs/27` để Nghe tới khi có giọng đọc sinh sẵn (đợt 5). Báo cáo chạy thử: ghi chú "Bạn có biết" không lặp mã bài.
**Tiến độ (03/11/2026) — Mục từ, địa danh như MVP (plugin 0.27.0):** `GiaoTiep\Khoi\DiaDanh` chuyển MVP `wikidata.php` (phần hiện), `sua-muc-tu.php`: trang mục từ có ảnh Commons đã tải về site (ghi công tác giả · giấy phép · Wikimedia Commons) hoặc ảnh riêng / ẩn ảnh người biên soạn chọn, bản đồ nhỏ có chấm (bản đồ tự lưu), "Mở bản đồ lớn", "Đọc thêm trên Wikipedia"; khối Từ khó trên trang bài có "Xem trong Từ điển · N bài" cùng ảnh, bản đồ của địa danh; đáp án câu đố đã xác nhận có ảnh (tra việc Wikidata theo khoá MVP `dap:`). Ảnh nằm trên site nên hợp CSP. Còn: xác nhận Wikidata mới trong Hộp việc, gợi ý mô-típ.
**Tiến độ (03/11/2026) — Nghe giọng máy, ô chú thích, sửa mục từ (plugin 0.28.0, theme 0.16.0):** nút **Nghe** trên thẻ lời (D-095, như MVP: giọng tiếng Việt có sẵn trên máy, chỉ hiện khi máy có giọng, không tự phát, bấm lại để dừng). Chạm từ khó mở **ô chú thích** ngay chỗ đọc (nội dung lấy từ khối Từ khó, gồm lối sang Từ điển, ảnh, bản đồ; tắt JavaScript thì nhảy xuống khối như cũ). **Sửa mục từ** ngay trên trang mục từ (`GiaoTiep\BienTap\SuaMucTu`, thay trang Sửa mục từ của MVP): lời giải (sửa chữ → người viết), nguồn lời giải chỉ người điền, ảnh riêng từ Thư viện ảnh kèm ghi công, ẩn ảnh Wikidata; form gửi thẳng, chỉ người biên tập thấy, mỗi lần lưu một dòng lịch sử; trang mục từ hiện nguồn lời giải người điền.

**Tiến độ (04/11/2026) — kiểm thử đầu-cuối:** `npm run e2e:v2` (`scripts/e2e-v2.mjs`, chạy trên CI sau `quet:v2`) đi từng thao tác như người dùng thật trên bản xem trước — 13 bước người đọc, 7 bước người biên soạn (docs/26 mục 13); ảnh chụp bước hỏng vào artifact `anh-chup-v2`. `-- --site <địa chỉ>` chạy phần người đọc trên site thật ở chế độ chỉ đọc (bước 4 ngày chuyển, docs/29). Phần khởi động Playground, tìm Chrome, đăng nhập tách ra `scripts/lib/playground-v2.mjs`, `quet:v2` dùng chung.

**Tiến độ (04/11/2026) — ngày chuyển:** chạy thử nhập lần 3 đạt (docs/29 mục 4); PR chuyển đổi chỉ thêm `wordpress/nguon-trien-khai` (`v2`) và đặt 1.0.0 — máy chủ lấy plugin, theme từ `wordpress/v2/`, coi trang "đang được nâng cấp" là ổn, làm mới quy tắc đường dẫn sau mỗi lần thay mã. Hai việc trong chấp nhận của ngày chuyển dời sang việc dọn dữ liệu cũ để ngày chuyển hoàn lại dễ: đưa `wordpress/v2/` lên chỗ cũ (xoá MVP, đổi lệnh npm mặc định) và chuyển `docs/20`, `docs/24` vào `docs/luu-tru/`.

#### Đợt 3 · Biên tập (T-152..T-156)
**Chấp nhận chung:** đúng `docs/28` và màn mẫu nhóm B của `design/v2` (D-094); không bước dựng JavaScript, `bien-tap.js` chỉ nạp cho người có quyền biên tập (≤ 30 KB gzip); HTML gửi khách không có nút Sửa hay dòng nguồn gốc nội bộ; mọi route kiểm quyền + nonce; 6 lằn ranh của `docs/28` mục 6 có kiểm thử; máy chỉ gợi ý cờ nghi (T-135 cũ), không tự đánh dấu SGK (T-133 cũ); không còn trang quản trị con nào của MVP trong V2; Gutenberg không mở cho tác phẩm. **T-156:** người sáng lập dùng thử trên điện thoại và trả lời "phần biên tập dùng được" (agent không tự đánh `DONE` khi chưa có câu trả lời); `e2e:v2` đạt; `thu-nhap` trên site thật (được đồng ý) cho "trước = sau" đạt trừ "Cần xem" đã giải thích.
**Tiến độ (02/11/2026, plugin 0.9.0) — nền biên tập (T-152) và sửa trang bài (một phần T-154):** quyền riêng (`cadao_soan`, `cadao_duyet`, `cadao_nguon`, `cadao_cong_tac`; vai Cộng tác); REST `phongdao/v1` lưu một phần (`POST /tac-pham/{id}/phan/{phan}`, nhận form, máy chủ dựng giá trị phần — `GiaoTiep\BienTap\TamSua`), đổi trạng thái (Đăng / Ẩn / nháp, Đăng cần quyền duyệt), hoàn tác; chế độ sửa trên trang bài (`CheDoSua`): dải "Đang sửa" (Xem như người đọc, Đăng / Ẩn), nút Sửa trên khối, dòng nguồn gốc của từng phần, khối Lịch sử, tấm sửa lời (+ đối chiếu sách), ý nghĩa (chưa đánh "đã duyệt" → nháp trong Hộp việc), nghĩa đen / bóng / dùng khi, câu hỏi / gợi ý / đáp án câu đố, từ khó, dị bản (mục máy chép không đổi thì giữ nguyên, đổi thì thành người viết), cờ nghi; `assets/bien-tap.js` (JavaScript thuần, chỉ người biên soạn, Hoàn tác 10 giây); trang người đăng nhập `Cache-Control: private, no-store`. **Phần 2 (plugin 0.10.0, theme 0.6.0):** bốn trang `/bien-tap/…` (trang cố định, phải đăng nhập với quyền biên tập, `noindex`): **Hộp việc** (lọc theo loại, Duyệt từng thẻ, thẻ câu mới / nháp ý nghĩa / có thể trùng đủ thông tin để quyết; nút quyết là form gửi `admin-post.php` có nonce riêng từng việc — tắt JavaScript vẫn quyết được; `DichVu\XepViec\QuyetViec`), **Tác phẩm** (mọi trạng thái, tìm, lọc thiếu ý nghĩa / chưa chủ đề / có cờ / bản nháp / đã ẩn), **Thêm bài** (máy nhận thể thơ, báo trùng khi dán lời — chỉ báo không chặn; Lưu nháp / Đăng ngay; nguồn người gõ), **Số liệu tuần** (bản đầu: số bài, việc chờ, kho, theo thể loại, theo cấp L0–L3, việc nên làm); đầu trang của người biên soạn có "Hộp việc (số việc)", thanh dưới Đọc · Hộp việc · Tác phẩm · Thêm. Còn: phân loại (chủ đề / vùng / tuyển tập) trên trang bài, sửa tại chỗ các trang khác, Nguồn & cài đặt, số đọc / tìm trong Số liệu tuần (cùng đo lường).

#### T-157 · Ngày chuyển — N+A
**Đọc:** `docs/29` mục 5. **Chấp nhận:** site thật chạy plugin, theme 1.0.0; số tác phẩm, Hiểu nhanh, lượt đọc, cảm nghĩ, phân tích, mục từ, trò chơi trước / sau khớp; 20 đường dẫn cũ ra 301 đúng; người sáng lập kiểm trên điện thoại; `docs/20`, `docs/24` vào `docs/luu-tru/`; lệnh npm mặc định trỏ V2.

#### Đợt 5 · Sau khi mở (T-158..T-164)
**Chấp nhận chung:** thể loại chỉ lên menu khi qua cổng (D-091); truyện chỉ từ sách hết bản quyền người sáng lập đã kiểm (H3); T-158 đạt tiêu chí cũ của T-131 (máy không tự điền "Lời đã đối chiếu với"; lời sách không vào repo công khai) và T-099; T-159 đạt tiêu chí cũ của T-133; T-160 đạt tiêu chí cũ của T-105 (nghe thử 30 bài trước khi sinh cả kho, ghi "Giọng đọc máy"); T-163 chỉ xuất các phần đã lưu của tác phẩm đã đăng, nguồn có quyền; T-164 chỉ chạy khi người sáng lập đồng ý (H6).

#### Nâng cấp quản trị V2 (T-166..T-172; D-096)

**T-166 — kế hoạch:** `docs/01`, `00`, `02`, `22`, `26`, `28`, `30` và hướng dẫn agent thống nhất hướng admin chính; ghi rõ phần D-094 còn hiệu lực và giao diện nào đang chạy. Không thay mã, dữ liệu hay cấu hình máy chủ.

**T-167 — danh sách và nền:** đọc `docs/30` mục 2, 4. Có bản xem trước admin trên máy tính và điện thoại trước khi thay đường sửa chính; danh sách `tac_pham` tìm/lọc/phân trang toàn kho theo trạng thái và phần thiếu, số đếm độc lập giới hạn trang; quyền từng vai, liên kết sang bài/việc; không mở ô meta thô. Kiểm trang khách và truy vấn khi dữ liệu lớn.

**T-168 — Hộp việc:** đọc `docs/30` mục 2 và kết quả T-176/T-177. Chờ/Để sau/Xong/Bỏ đều tìm lại được; bộ lọc và số đếm đúng toàn kho; mở thẻ thấy bài/nguồn liên quan; quyết một thẻ qua `QuyetViec`, có kiểm quyền, nonce và nhật ký. Kế thừa phần sửa ở giao diện hiện chạy, không viết lại lỗi cũ trong màn mới; không tự chuyển thẻ thành bài viết.

**T-169 — sửa tác phẩm:** đọc `docs/30` mục 2, 4 và `docs/28` mục 6. Biểu mẫu theo phần gọi `LuuPhan`/`KiemPhan`, giữ nguồn gốc và nháp chờ duyệt; sửa cùng một phần qua admin và trang đọc cho kết quả tương đương; `post_content`/chỉ mục được cập nhật; người không đủ quyền không gửi trực tiếp được.

**T-170 — lịch sử:** đọc `docs/30` mục 4. So trước/sau từng phần; khôi phục tạo sự kiện mới và từ chối khi có thay đổi mới hơn; quyết việc, đổi trạng thái và hồ sơ nguồn có dấu vết tương ứng. Hoàn tác 10 giây vẫn dùng được cho thao tác đủ điều kiện.

**T-171 — thao tác lô:** đọc hợp đồng `docs/30` mục 3. Mỗi lô chốt phạm vi, xem trước, kiểm quyền và phiên bản từng mục, báo thành công/bỏ qua/xung đột/lỗi, chạy lại không áp dụng trùng; kiểm riêng duyệt nhóm gợi ý có ngoại lệ, thêm/gỡ/thay thế phân loại, sửa nội dung từng hàng, ẩn/thùng rác và đổi trạng thái việc. Không tự quyết sự thật văn hoá hay xoá vĩnh viễn.

**T-172 — nghiệm thu và chuyển lối vào:** người sáng lập chạy ba kịch bản `docs/30` mục 5 trên máy tính và điện thoại; ghi thời gian, số thao tác, lỗi; `kiem-tra`, `test:v2`, `quet:v2` và luồng E2E đạt. Sau đó mới chuyển đường tắt/đường cũ; có cách quay lại giao diện cũ nếu phát hiện lỗi. Không tự đánh `DONE` trước phản hồi dùng thử của người sáng lập.

#### Cải tiến tổng thể sau review V2 (T-173..T-183)

**T-173 — kế hoạch chung:** `docs/31` nối các phát hiện đã xác minh và đề xuất đã đối chiếu với `docs/30`, backlog, trạng thái và hướng dẫn agent. Phân biệt việc làm ngay, việc cần đo/thử và ý tưởng cần Human Gate. Chỉ đổi tài liệu, không sửa mã, dữ liệu hay máy chủ.

**T-174 — trang người đọc:** đọc `docs/31` mục 2 và `docs/e2e-review/v2/REPORT.md` L-01/L-02/L-05/L-06. Đo lại trước khi sửa; kiểm vùng chạm Bản đồ, dung lượng ảnh, đường xử lý câu đố thiếu Ý nghĩa, tràn ngang ở 320 px và lối Quyền riêng tư; quét trợ năng và kiểm trang không JavaScript. Không thay chính sách dữ liệu bằng cách sửa đường dẫn.

**T-174.1 (05/11/2026):** đọc lại trang Bản đồ site thật (HTTP 200): 13 điểm còn vùng chạm 28 px, chưa có điểm 44 px. Bản vá giữ chấm nhìn thấy 28 px, mở vùng liên kết 44 px; điểm gần nhau được gộp để vùng chạm không chồng ở khổ 320 px. Có ca 20 toạ độ kiểm thử chỉ dùng số, không tạo địa danh giả. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 262 Playground + 55 chuyển đổi; `quet:v2` 25 trang × 3 chế độ + 7 trang biên tập, 0 lỗi; `e2e:v2` 21 bước, 0 lỗi/bỏ qua. Chưa kiểm lại bản vá trên site thật vì không ghi lên máy chủ. **T-174 còn mở:** ảnh, đường Ý nghĩa câu đố, tràn ngang và lối Quyền riêng tư.

**T-174.2 (06/11/2026):** đo lại bằng trình duyệt trên site thật `/ve-du-an/` tại 320 px: `scrollWidth=323`, nút sáng/tối chạm mép phải 322,656 px. Thử giảm mỗi bên đệm của nút Tìm 2 px ngay trong phiên trình duyệt cho `scrollWidth=320`, nút sáng/tối kết thúc ở 318,656 px; bản vá CSS áp dụng ở ≤359 px, sửa nguồn `design/v2/source/styles.css` rồi đồng bộ theme. Cổng `quet:v2` nay quét thêm bốn trang tĩnh ở 320 px sáng/tối. Bản quét trên fixture trước khi sửa CSS đạt nên không lấy fixture thay bằng chứng tái hiện trên site thật. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 262 Playground + 55 chuyển đổi; `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 21 bước, 0 lỗi/bỏ qua. **Chưa đóng T-174:** ảnh, câu đố, Quyền riêng tư và kiểm site sau triển khai.

**T-174.3 (06/11/2026):** đo lại bằng trang thật: ảnh chú thích trong `/dong-dao/con-cong-hay-mua/` tải bản 500 px 136.791 byte; ba ảnh trong `/ca-dao/em-toi-buon-ngu-buon-nghe/` tải bản gốc tổng 294.787 byte, đều không có `srcset`. Bản thu nhỏ 150 px đã có trên site, tương ứng 20.079 và tổng 18.465 byte (HEAD chỉ đọc). Trang bài chọn cỡ `thumbnail` từ ID tệp đính kèm; trang mục từ giữ cỡ `medium`; ghi công giữ nguyên. Theo số tổng trang trong báo cáo review và số HEAD hiện tại, **ước tính** hai trang còn 179.356 và 178.448 byte; chưa phải phép đo sau triển khai. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 263 Playground + 55 chuyển đổi; `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 21 bước, 0 lỗi/bỏ qua. Chưa ghi lên máy chủ hoặc xác nhận ngân sách trang thật sau khi triển khai. **T-174 vẫn `IN_PROGRESS`:** đường Ý nghĩa câu đố, Quyền riêng tư và kiểm lại site thật; cần kiểm ảnh cắt 150 px bằng mắt trên site sau triển khai.

**T-174.4 (07/11/2026):** danh sách Thiếu ý nghĩa có câu đố chưa có ý nghĩa, nhưng trang câu đố trong chế độ sửa đã bỏ nút Thêm ý nghĩa khi dựng khối Gợi ý/Đáp án. Trả lại khối thêm Ý nghĩa chỉ cho người biên soạn; khách vẫn không thấy lời nhắc thiếu ý nghĩa hay đáp án lộ sẵn. Playground và E2E kiểm từ bộ lọc tới nút sửa và trang khách. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 278 Playground + 55 chuyển đổi (Node 24.18.0, PHP 8.4.12); `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 23 bước đạt, 0 lỗi, 0 bỏ qua. **T-174 còn mở:** lối Quyền riêng tư và đo ngân sách/hình cắt trên site sau triển khai; không tự viết nội dung chính sách.

**Kiểm sau PR #110 (06/11/2026, chỉ đọc):** site tự cập nhật plugin 1.0.7; GET hai trang trên đều HTTP 200 và HTML đã trỏ tới đúng bốn ảnh `-150x150.jpg` trên site. Chưa đo lại toàn bộ tài nguyên bằng cùng phép đo ngân sách của báo cáo review, nên chưa gọi ngưỡng 200 KiB là đạt; chưa xem hình cắt trong bố cục sau triển khai.

**T-174.5 (07/11/2026, chỉ đọc site thật):** hai trang `/dong-dao/con-cong-hay-mua/` và `/ca-dao/em-toi-buon-ngu-buon-nghe/` trả HTTP 200. Trình duyệt mới ở 390 px, chỉ cho GET/HEAD, chờ tải/phông và 1,2 giây; cộng thân tài nguyên theo cùng cách nén của `quet:v2`: lần lượt **182.832** và **181.774 byte**, dưới mức 204.800 byte; JavaScript mỗi trang 9.267 byte, dưới 21 KiB. Một ảnh và ba ảnh tương ứng đều tải bản 150 × 150 px; ảnh chụp trang tại máy cho thấy ảnh hiện trong khung, chưa có dấu hiệu vỡ bố cục. Đây là phép đo trang thật sau bản vá ảnh, không phải ước tính HEAD. `/quyen-rieng-tu/` vẫn HTTP 404; `docs/27` mục 8.3 giao nội dung trang tĩnh cho người sáng lập, nên T-174 vẫn `IN_PROGRESS` chờ nội dung được người sáng lập duyệt và kiểm trang sau khi đăng. Không chuyển hướng sang Chính sách thu thập dữ liệu vì hai trang có mục đích khác nhau.

**T-175 — cập nhật an toàn:** đọc `docs/31` mục 2, D-090 và báo cáo E2E L-07..L-09. Trên bản sao, diễn tập lỗi giữa lúc thay thư mục, quay về bản phần mềm trước; đối chiếu thứ tự xử lý dữ liệu trong hướng dẫn, không đặt cổng thử khôi phục bản sao lưu FlashPanel. Kiểm smoke khách/biên tập và workflow khi thay đầu vào bộ kiểm. Không ghi lên máy chủ nếu chưa được người sáng lập đồng ý cho lần đó.

**Bằng chứng (05/11/2026, nhánh T-175):** script cập nhật giữ bản cũ tới sau kiểm trang chủ và tự phục hồi khi `mv` plugin/theme hoặc kiểm trang thất bại; `tests/deploy-rollback.sh` diễn tập ba lỗi trên bản sao. Hướng dẫn đặt hoàn tác dữ liệu trước Revert khi quay V2 → MVP. Workflow V2 nhận thay đổi từ `scripts/**`, `tests/e2e-v2/**`, mã triển khai, `.node-version` và `package-lock.json`. `kiem-tra` đạt; `test:v2` 237 PHP + 255 Playground + 55 chuyển đổi đạt; `quet:v2` 25 trang × 3 chế độ + 7 trang biên tập, 0 lỗi; `e2e:v2` 21 bước, 0 lỗi, 0 bỏ qua. Chưa ghi lên máy chủ hoặc diễn tập trên site thật.

**T-176 — Hộp việc đang chạy:** đọc `docs/31` mục 2 và báo cáo E2E L-03/L-04. Thử thẻ cuối, phân trang/số đếm khi hàng nghìn thẻ, tìm lại Để sau, Hoàn tác hợp lệ và phím tắt có ngoại lệ khi ô nhập/focus đang hoạt động. Dùng cùng hợp đồng trạng thái cho màn admin kế tiếp.

**T-176.1 (06/11/2026):** Hộp việc cũ chỉ tải 500 thẻ rồi lấy độ dài mảng làm tổng, nên thẻ sau mốc này không tìm được và số đếm thấp hơn dữ liệu thật. Bản vá đếm bằng truy vấn bảng việc, tải 30 thẻ mỗi trang, duyệt từng thẻ bằng vị trí thật trong tập đã lọc; có đường xem lại Để sau và không lặp nút Để sau trên thẻ đã hoãn. Kiểm thử tạo hơn 1.000 thẻ kỹ thuật, kiểm tổng, lọc loại, trang và thẻ cuối, rồi dọn dữ liệu thử. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 265 Playground + 55 chuyển đổi; `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 21 bước đạt, 0 lỗi, 0 bỏ qua. **T-176 còn mở:** Hoàn tác quyết thẻ và phím tắt; màn admin mới dùng hợp đồng này sau.

**T-176.2 (06/11/2026):** màn Duyệt từng thẻ có J/K để chuyển thẻ và 1/2/3 để chọn ba nút quyết đầu; phím tắt không chạy khi focus ở ô nhập, liên kết, nút hay hộp thoại, và không nhận phím có modifier. Hướng dẫn chỉ hiện khi JavaScript hoạt động; form nút chính vẫn gửi được khi tắt JavaScript. E2E trong Playground kiểm chuyển đi/về, đúng nút được chọn, và ngoại lệ focus, không gửi quyết định thật ở bước kiểm phím. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 265 Playground + 55 chuyển đổi; `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 22 bước đạt, 0 lỗi, 0 bỏ qua. **T-176 còn mở:** Hoàn tác quyết thẻ và kiểm trạng thái hết việc sau thẻ cuối; màn admin mới dùng hợp đồng này sau.

**T-176.3 (06/11/2026):** khi quyết thẻ duy nhất trong bộ lọc, Hộp việc trả trạng thái hết lượt kèm lối về danh sách; khi vị trí thẻ cũ đã vượt cuối do danh sách đổi, trả trạng thái đến cuối và nút duyệt lại từ đầu, không hiện lặp thẻ trước. E2E quyết nháp cuối trong bộ lọc; kiểm thử Playground thử vị trí vượt cuối sau khi có hơn 1.000 thẻ kỹ thuật. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 265 Playground + 55 chuyển đổi; `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 22 bước đạt, 0 lỗi, 0 bỏ qua. **T-176 còn mở:** Hoàn tác quyết thẻ; màn admin mới dùng hợp đồng trạng thái này sau.

**T-176.4 (07/11/2026):** Hoàn tác nhanh 10 giây cho các quyết định chỉ đổi trạng thái thẻ: Để sau (trừ cặp nghi trùng), Đã xem của thẻ Cần xem, Bỏ nháp ý nghĩa và Bỏ gợi ý. Form dùng nonce, kiểm đúng người quyết, thời hạn, phiên và trạng thái trên máy chủ; nhật ký Hoàn tác được ghi trong cùng giao dịch. Quyết định đã tác động bài, kho, cặp nghi trùng hoặc nội dung người đọc gửi được báo rõ là chưa có Hoàn tác nhanh, để tránh phục hồi một nửa. Kiểm `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 271 Playground + 55 chuyển đổi; `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 22 bước đạt, 0 lỗi, 0 bỏ qua. **T-176 còn mở:** Hoàn tác an toàn cho từng loại quyết định có tác động phụ; chưa gọi task DONE.

**T-177 — nhất quán quyết định:** đọc `docs/31` mục 2 và `docs/26`. Thử hai phiên quyết cùng thẻ, phiên bản cũ, DB ghi lỗi và yêu cầu gửi lại; kết quả chỉ có một quyết định hợp lệ, không tách rời bài/việc/nhật ký; báo xung đột rõ cho người biên tập.

**T-177.1 đã làm (05/11/2026):** số phiên thẻ và cập nhật có điều kiện trong giao dịch cho quyết một thẻ của Hộp việc hiện chạy; thử yêu cầu cũ sau Để sau và lỗi ghi phần. `kiem-tra` đạt; `test:v2` 237 PHP + 258 Playground + 55 chuyển đổi đạt; `quet:v2` 25 trang × 3 chế độ + 7 trang biên tập, 0 lỗi; `e2e:v2` 21 bước, 0 lỗi, 0 bỏ qua. **T-177 còn mở:** kiểm lỗi DB ở mọi nhánh quyết (đăng câu mới, gộp, gợi ý, người đọc gửi), ghi trực tiếp phần bài/trạng thái và chạy lại yêu cầu qua hai phiên thực; không chuyển sang `DONE` trước các ca này.

**T-177.2 (05/11/2026):** kiểm giá trị trả về của bước cập nhật bản chữ sau khi lưu phần, báo lỗi và hoàn tác meta/nhật ký khi bước đó thất bại; quyết định cặp câu trả lỗi ghi thay vì báo thành công. Thử ép lỗi DB ở cả hai đường trong Playground. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 260 Playground + 55 chuyển đổi; `quet:v2` 25 trang × 3 chế độ + 7 trang biên tập, 0 lỗi; `e2e:v2` 21 bước, 0 lỗi/bỏ qua. **Chưa chốt T-177:** các đường đăng câu từ kho, gộp, gợi ý và đổi trạng thái nội dung người đọc gửi còn cần rà thao tác ghi và kiểm lỗi.

**T-177.3 (07/11/2026):** đường gộp cặp câu chỉ nhận câu kho còn Mới, chưa thành bài; cập nhật trạng thái có điều kiện và đòi đúng một dòng, kiểm lỗi bước chuyển các câu đã gộp theo và bước ghi quyết định cặp. Khi quyết từ Hộp việc, lỗi ở bất kỳ bước này đều trả lỗi và huỷ cùng giao dịch: thẻ, câu kho, cặp và chỉ mục không tách rời. Playground ép lỗi ba bước ghi, đối chiếu trạng thái sau rollback; ca gộp tự động hiện có vẫn đạt. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 274 Playground + 55 chuyển đổi; `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 22 bước đạt, 0 lỗi, 0 bỏ qua. **T-177 còn mở:** kiểm lỗi đồng bộ phần bài và chỉ mục khi gộp, đường đăng câu từ kho, gợi ý và trạng thái nội dung người đọc gửi; chưa gọi task DONE.

**T-177.4 (07/11/2026):** khi đăng câu từ kho, kiểm kết quả gắn thể loại, đổi trạng thái câu kho có điều kiện, chuyển bản nháp sang Đăng và đóng thẻ; nhánh Bỏ kiểm kết quả ghi kho và đóng thẻ. Đường quyết từ Hộp việc huỷ giao dịch nếu một bước này lỗi; Playground ép lỗi ghi kho và phát hành bài, đối chiếu bài/kho/thẻ sau rollback, thêm ca Bỏ lỗi ghi kho. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 277 Playground + 55 chuyển đổi; `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 22 bước đạt, 0 lỗi, 0 bỏ qua. **T-177 còn mở:** kiểm lỗi gắn phân loại, đồng bộ phần bài/chỉ mục sau gộp, gợi ý và trạng thái nội dung người đọc gửi; chưa gọi task DONE.

**T-177.5 (07/11/2026):** đường quyết gộp cặp nay kiểm kết quả đồng bộ phần bài; lưu dị bản lỗi thì trả lỗi để giao dịch huỷ cùng thẻ, câu kho, cặp và nhật ký. Bài nhập tay không có hàng kho gốc vẫn được gộp vì không có phần máy cần đồng bộ. Playground ép lỗi ghi dị bản và kiểm đường bài nhập tay. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 280 Playground + 55 chuyển đổi (PHP 8.4.12, Node 24.18.0); `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 23 bước đạt, 0 lỗi, 0 bỏ qua. **T-177 còn mở:** gộp tự động chưa có giao dịch bao toàn bước, lỗi ghi chỉ mục sau gộp, gợi ý và trạng thái nội dung người đọc gửi còn cần ca ép lỗi; không coi lát cắt này là hoàn tất task.

**T-177.6 (07/11/2026):** ép lỗi khi dựng lại chỉ mục vân tay bài trong quyết định gộp cho thấy chỉ mục cũ từ 4 dòng về 0 dù thẻ/kho đã rollback; ca tìm trùng phía sau cũng hỏng. Chỉ mục mới nay được dựng đủ dưới khoá tạm rồi mới thay bản đang dùng; kiểm lỗi truy vấn xoá, ghi vân tay và cặp âm, và bỏ khoá tạm khỏi kết quả so trùng. Playground ép lỗi ba bước ghi, xác nhận thẻ/kho/cặp không xác nhận nửa chừng, vân tay cũ còn đủ và không sót khoá tạm. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 283 Playground + 55 chuyển đổi (PHP 8.4.12, Node 24.18.0); `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 23 bước đạt, 0 lỗi, 0 bỏ qua. **T-177 còn mở:** cần thử lỗi ở bước xoá bản cũ/đổi khoá sau khi bản tạm đã dựng, gộp tự động chưa có giao dịch bao toàn bước, và các đường gợi ý/nội dung người đọc gửi; không gọi task DONE.

**T-177.7 (07/11/2026):** ép lỗi đổi khoá bản vân tay mới sau khi đã dựng xong làm mất chỉ mục bài đích và gây lỗi dây chuyền ở các phép so trùng. Nay chuyển bản cũ sang khoá giữ tạm, kích hoạt bản mới rồi mới dọn bản cũ; nếu kích hoạt lỗi, khôi phục khoá cũ và huỷ bản mới. Playground kiểm thẻ/kho/cặp, số vân tay cũ và không còn hàng tạm sau lỗi. `kiem-tra` 145 task, 0 lỗi; `test:v2` 237 PHP + 284 Playground + 55 chuyển đổi (PHP 8.4.12, Node 24.18.0); `quet:v2` 29 trang × 3 chế độ + 4 trang tĩnh × 2 chế độ 320 px + 7 trang biên tập, 0 lỗi; `e2e:v2` 23 bước đạt, 0 lỗi, 0 bỏ qua. **T-177 còn mở:** lỗi dọn bản cũ sau khi kích hoạt có thể để lại hàng ẩn cần xử lý; gộp tự động chưa có giao dịch bao toàn bước, các đường gợi ý/nội dung người đọc gửi và thử hai phiên thực vẫn chưa được đối soát. Không gọi task DONE.

**T-178 — đối soát:** đọc `docs/31` mục 2 và `docs/29`. Lập bảng ID cho chênh 630/631, kiểm bài vè nhiều đoạn, đường 301 và trạng thái task cũ theo bằng chứng triển khai; ghi rõ sai khác còn mở. Chỉ đọc site và dữ liệu; không sửa bài hoặc đóng thẻ để ép khớp số.

**T-178.1 · ảnh chụp công khai chỉ đọc (07/11/2026):** `npm run kiem-site-that` thấy plugin V2 1.0.17, theme 1.0.2. GET cả 24 trang `/kho/` cho 692 thẻ bài, đúng số đếm trên trang nhưng chỉ 690 đường dẫn khác nhau: hai đường dẫn `/ca-dao/cai-co-lan-loi-bo-ao/` (hai thẻ cùng trang 3) và `/ca-dao/hom-qua-anh-den-choi-nha/` (hai thẻ cùng trang 11) xuất hiện hai lần. Chưa có ID của từng thẻ để kết luận là hai bài khác nhau hay cùng bài lặp ở truy vấn; không đổi đường dẫn hoặc gộp bài (H6). Con số 630 đường dẫn duy nhất của review 04–05/11 và 631 bài trong báo cáo ngày chuyển là **hai thời điểm/định nghĩa khác**; số hiện tại 692 không giải thích chênh lịch sử.

GET 63 trang vè đang công khai: 32 có Mục lục, trong đó 26 chỉ là đoạn đánh số máy chia khi bài dài; sáu trang có **tiêu đề đoạn** là ID 364, 374, 377, 380, 382, 391 (ID từ shortlink công khai). Bài thứ bảy có phần `doan` theo báo cáo nhập 04/11 chưa thể xác định từ trang khách: có thể khác trạng thái hoặc không còn tiêu đề hiển thị, **chưa kết luận**. Ba GET đường cũ đại diện đạt 301 đúng đích: `/the-loai/thanh-ngu/` → `/the-loai/tuc-ngu/`, `/bai/som-rua-cua/` và `/thanh-ngu/som-rua-cua/` → `/tuc-ngu/som-rua-cua/`. Chưa có danh sách 15 ánh xạ đổi tên ngày chuyển để kiểm đủ 15/15; cần đối chiếu ID/trạng thái và `_wp_old_slug` bằng quyền **chỉ đọc** từ dữ liệu WordPress hoặc báo cáo ngày chuyển, không lấy phỏng đoán từ URL hiện tại. Các trạng thái READY cũ T-143..T-157 tiếp tục giữ nguyên cho tới khi đối soát từng tiêu chí và bằng chứng; không dùng chúng để suy V2 chưa triển khai.

**T-179 — nguồn và nội dung máy:** đọc `docs/31` mục 2, `docs/12`, `docs/28`, `docs/30`. Người có quyền thấy trang nguồn, căn cứ, quyền, bài/phần chịu ảnh hưởng và lịch sử trước/sau; có sửa/ẩn phần và tạm dừng việc nền với nhật ký. Không tự chấp thuận quyền mới, sửa lời đã đăng hoặc xoá dữ liệu gốc.

**T-180 — bàn làm việc và chất lượng:** đọc `docs/31` mục 2. Tổng vào/ra, tuổi việc, 10 việc cần làm và danh sách thiếu/nghi trùng/nguồn chưa rõ lấy từ dữ liệu toàn kho; người biên tập chọn 20–30 bài thử làm sâu và ghi thời gian/giá trị, không tự điền nội dung văn hoá.

**T-181 — tìm kiếm:** đọc `docs/31` mục 2 và `docs/27`. Dùng tập truy vấn đại diện và kho thử 1.000–10.000 bài để bắt bỏ sót/thứ tự sai do chặn 500 ứng viên; so kết quả và thời gian trước/sau, giữ tìm không dấu. Không thay hạ tầng tìm kiếm khi chưa có số đo.

**T-182 — hiệu năng:** đọc `docs/31` mục 2. Đo truy vấn, thời gian, dung lượng Bản đồ/Số liệu tuần/ảnh trên mẫu lớn; tối ưu điểm nghẽn đã xác minh, kiểm lại kết quả và ngân sách trang đọc.

**T-183 — thử sản phẩm:** đọc `docs/31` mục 3–4. Dùng dữ liệu đã duyệt để thử lối chủ đề, bài viết dài, bộ câu cho giáo viên và tìm dị bản; ghi tác vụ người dùng và gánh biên tập. Chỉ triển khai thay đổi UX lớn, mô hình dữ liệu, nguồn hoặc thông tin cá nhân sau Human Gate tương ứng.

**Lưu ý trạng thái:** T-143..T-157 là backlog xây mới trước ngày chuyển, có mục ghi `READY` dù `docs/02` và site thật đã ghi nhận phần triển khai. Nhóm T-166..T-183 theo dõi **nâng cấp mới**, không dùng các trạng thái cũ để suy ra chức năng đã có. T-178 đối soát backlog lịch sử theo bằng chứng, không đổi trạng thái hàng loạt theo suy đoán.

## 4. User story

| ID | Vai trò | Nhu cầu | Mục đích | Task |
|---|---|---|---|---|
| US-01 | Giáo viên | Thấy Bản tham chiếu và nguồn gốc ngay đầu trang | Đưa vào giáo án, đề thi | T-040 |
| US-02 | Giáo viên, học sinh | Chạm vào từ khó để xem nghĩa tại chỗ | Hiểu mà không rời trang | T-040 |
| US-03 | Giáo viên | Xem các dị bản và chỗ khác nhau | Giải thích vì sao có nhiều bản | T-040, T-063 |
| US-04 | Giáo viên, sinh viên | Sao chép dòng trích dẫn chuẩn | Ghi nguồn đúng | T-042 |
| US-05 | Giáo viên | Xem các cách hiểu khác nhau, mỗi cách có căn cứ | Dạy theo hướng mở | T-040 |
| US-06 | Giáo viên | Chiếu một bài lên màn hình lớp học | Dạy trên lớp | T-051 |
| US-07 | Người đọc | Biết lời lấy từ đâu và đã kiểm đến mức nào | Tin đúng mức | T-040, T-046 |
| US-08 | Người trẻ | Tìm câu theo tình huống, cảm xúc | Tìm câu nói hộ lòng mình | T-041, T-043 |
| US-09 | Người trẻ | Chia sẻ một câu qua Zalo/Facebook, link hiện ảnh đẹp có nguồn | Đăng mạng xã hội | T-042, T-050 |
| US-10 | Mọi người | Gõ không dấu vẫn tìm được | Tìm nhanh trên điện thoại | T-041 |
| US-11 | Người có sách | Gửi số trang để bài được đối chiếu | Góp phần nâng độ tin cậy | T-045 |
| US-12 | Người ghi hộ | Gửi một câu ông bà còn nhớ, ghi rõ người kể, không cần tài khoản | Góp phần gìn giữ | T-045 |
| US-13 | Người biên soạn | Thêm nhanh một câu từ điện thoại | Ghi lại ngay khi nghe | T-037 |
| US-14 | Người biên soạn | Biết hôm nay nên làm gì mà không dùng dòng lệnh | Dùng tốt 5 giờ/tuần | T-047 |
| US-15 | Người vận hành | Build thất bại khi dữ liệu phạm lằn ranh | Không bao giờ xuất bản dữ liệu hỏng | T-035 |

## 5. Definition of Done (mọi task phần mềm)

- [ ] Đạt mọi tiêu chí chấp nhận của task.
- [ ] `npm run kiem-tra` đạt; khi đã có Astro, lệnh này bao gồm `astro check` và build.
- [ ] PR có link bản xem trước; chạy đúng trên điện thoại (màn hình 360 px) và máy tính.
- [ ] Đạt WCAG 2.2 AA cho phần đã làm: bàn phím, focus nhìn thấy và không bị che, vùng chạm, tương phản, nhãn cho trình đọc màn hình (`23` mục 3).
- [ ] Không thêm cookie, theo dõi, dịch vụ hay thư viện mới ngoài những gì task cho phép.
- [ ] Không đổi dữ liệu trong `content/` (trừ task biên soạn và T-039) và không đổi từ vựng kiểm soát.
- [ ] Tài liệu liên quan được cập nhật trong cùng PR nếu hành vi khác đặc tả.
- [ ] Commit do AI agent tạo có dòng `AI-Agent: <công cụ/mô hình>` (D-032).
