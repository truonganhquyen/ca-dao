# 22 · Backlog triển khai

| | |
|---|---|
| Phiên bản | 2.1 · 25/10/2026 (thêm gói nâng cấp D-059..D-065, `docs/25`) |
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
| T-034 | Gửi thư xin phép dùng chú thích cho Ca dao Mẹ | N | — | — | READY |
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
| T-080 | Tách thành ngữ và tục ngữ (máy gợi ý, người duyệt) | N+A | T-073 | — | REVIEW |
| T-081 | Đếm lượt đọc không cookie, "Đọc nhiều", thống kê trên Bảng tin | A | T-071 | — | DONE |
| T-109 | Nhận ra bài trùng: "dấu vân tay" của lời, các mức giống (D-065) | A | T-073 | — | DONE |
| T-110 | Chống trùng khi lấy từ nguồn: gắn vào bài có sẵn, hàng "Có thể trùng" | N+A | T-109 | — | DONE |
| T-111 | Báo trùng khi viết bài tay (chỉ báo, không chặn) | A | T-109 | — | DONE |
| T-042 | Trích dẫn 2 định dạng + chia sẻ (trên WordPress) | A | T-072 | — | DONE |
| **Mốc B · Thử nhỏ** ||||||
| T-041 | Tìm kiếm tức thì, gõ không dấu, tìm cả trong chú thích (WordPress) | N+A | T-072 | — | DONE |
| T-043 | Trang chủ mới (Câu hôm nay, Vừa bổ sung, Đọc nhiều, Bạn có biết?) + trang tình huống | A | T-072, T-081, T-093 | — | DONE |
| T-044 | Từ điển chú giải: trang từng từ, "Các bài có từ này", lọc theo vùng | A | T-073 | — | DONE |
| T-045 | Góp ý, dị bản, cảm nghĩ ngắn của người đọc; chống rác; quyền riêng tư; "Gửi số trang" | N+A | T-075 | — | READY |
| T-046 | Trang Phương pháp, Nguồn, Về dự án | N+A | T-075 | — | READY |
| T-047 | Bàn biên tập: gom các hàng chờ (Chờ duyệt, xác nhận, người đọc gửi) trong wp-admin | A | T-071 | — | READY |
| T-082 | Chạm vào từ khó, nghĩa hiện ngay (lưu vị trí chú thích) | A | T-073 | — | DONE |
| T-083 | Dị bản đánh số, tô chỗ khác lời chính | A | T-072 | — | DONE |
| T-084 | Nhận thể thơ, trình bày theo thể | A | T-072 | — | DONE |
| T-085 | Công cụ đọc: cỡ chữ, sáng/tối, trình chiếu, nghe bằng giọng có sẵn của điện thoại | A | T-072 | — | DONE |
| T-086 | Gắn Wikidata: hàng chờ xác nhận, ảnh Commons có ghi tác giả | N+A | T-073 | — | DONE |
| T-087 | Chú thích địa danh: bản đồ nhỏ, ảnh, "Bài khác nhắc tới…" | A | T-082, T-086 | — | DONE |
| T-088 | Câu đố: ảnh đáp án | A | T-086 | — | DONE |
| T-089 | Tự gợi ý vùng cho bài (phương ngữ, địa danh) | N+A | T-073 | — | DONE |
| T-090 | Vè và bài dài: chia đoạn, mục lục, đọc tiếp | A | T-072 | — | DONE |
| T-091 | Bộ chủ đề riêng của dự án (gồm nhóm tục ngữ kinh nghiệm, lối hát, loại câu đố), máy gợi ý | N+A | T-073 | — | REVIEW |
| T-092 | "Bài gần gũi" cuối bài và trang mô-típ | A | T-091 | — | BLOCKED |
| T-093 | "Bạn có biết?" ghép từ chú thích | N+A | T-073 | — | DONE |
| T-094 | Trang riêng cho từng thể loại | N+A | T-072 | — | DONE |
| T-095 | Thành ngữ – tục ngữ: trình bày câu một dòng, chỉ mục A–Z | A | T-080 | — | BLOCKED |
| T-096 | Câu đố: nhóm theo đáp án và theo loại, trang "Đố vui" | A | T-091 | — | BLOCKED |
| T-097 | Bài cảm nhận, phân tích của người đọc (cần duyệt); bài có phân tích được duyệt thì lên Google | A | T-045 | — | BLOCKED |
| T-048 | Duyệt 30–50 bài, thử với 5 người | N | T-073, T-076, T-041..T-046, T-082, T-085 | — | BLOCKED |
| **Mốc C · Mở lặng lẽ** ||||||
| T-049 | Chốt tên miền | N | — | — | READY |
| T-050 | Ảnh chia sẻ tự sinh từng bài, noindex theo giá trị riêng, SEO cơ bản | A | T-076 | Q-005 | BLOCKED |
| T-051 | "Đã lưu": bài yêu thích nhớ trên máy (Câu hôm nay, Vừa bổ sung → T-043; cỡ chữ, sáng/tối, trình chiếu → T-085) | A | T-072 | — | READY |
| T-052 | Đo lường không cookie: tìm không ra, sao chép trích dẫn, mở chú giải | A | T-076, T-081, T-041 | — | BLOCKED |
| T-098 | Thành ngữ – tục ngữ: nghĩa đen, nghĩa bóng, "dùng khi", đồng nghĩa (Wiktionary, CC BY-SA) | N+A | T-080 | — | BLOCKED |
| T-099 | Chú giải từ sách hết bản quyền; chữ Hán Việt, điển tích của thành ngữ | N+A | T-082 | — | BLOCKED |
| T-100 | Trợ giúp viết "Hiểu nhanh" (gợi ý; nháp máy chờ duyệt) | A | T-098 | — | BLOCKED |
| T-101 | Gộp bài đã đăng bị trùng: bài chính + dị bản, chuyển hướng, dồn lượt đọc và bài người đọc | N+A | T-109 | — | BLOCKED |
| T-102 | Vè: khối "Bối cảnh" (sự kiện, năm, nơi chốn, nhân vật) | N+A | T-086 | — | BLOCKED |
| T-103 | Đồng dao: cách chơi, chế độ "Đọc cùng bé" | A | T-085 | — | BLOCKED |
| T-104 | Bản đồ ca dao theo vùng | A | T-086, T-089 | — | BLOCKED |
| T-105 | Giọng đọc sinh sẵn theo miền (VieNeu-TTS), phiên nghe thử 30 bài | N+A | T-085, T-089 | — | BLOCKED |
| T-106 | Giọng riêng của dự án, thu từ người đồng ý | N | T-105 | — | BLOCKED |
| T-107 | Tìm bản quét "Tục ngữ phong dao" (Nguyễn Văn Ngọc, 1928) | N | — | — | READY |
| T-053 | Đủ 100–150 bài, mở công khai lặng lẽ | N | T-034, T-048..T-052 | — | BLOCKED |
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

Không có task cho cơ sở dữ liệu, công cụ quản trị riêng hay monorepo (D-047). Task `DEFERRED` chỉ mở khi người sáng lập chọn ở nhịp hằng tháng sau Mốc C.

## 2. Mốc và điều kiện xong

Thay cho các cổng đếm hồ sơ của v1 (D-044). Agent được tự chuyển `giai_doan` trong `du-an/trang-thai.json` sang mốc kế tiếp khi mọi task A của mốc hiện tại đã `DONE` và điều kiện dưới đây đạt; **riêng mở công khai (T-053) là Human Gate H5.**

| Mốc | Xong khi | Ước tính |
|---|---|---|
| **Chuẩn bị** | Gói D-035..D-047 nằm trong repo (T-033); có repo GitHub công khai, repo riêng tư cho kho thô và tài khoản Cloudflare (T-032) | Tuần cuối 10/2026 |
| **A · Chạy được** | (D-056) WordPress chạy trên hosting thật; người sáng lập đăng một bài thủ công từ điện thoại trong vài phút; nguồn tự động Ca dao Mẹ (cấu hình trong trang quản trị) đổ bài vào **Chờ duyệt** mỗi ngày, duyệt là lên trang; trang bài hiện lời, dị bản, chú thích (kèm "Nguồn: …" khi chép từ nguồn); nội dung cũ đã chuyển, site Astro ngừng | Cuối 11/2026 |
| **B · Thử nhỏ** | 30–50 bài trên bản xem trước; trang tình huống, từ điển, form góp ý, trang Phương pháp chạy; đọc dễ hơn (chạm từ khó, dị bản tô chỗ khác, thể thơ, công cụ đọc), tìm kiếm, trang thể loại, bài gần gũi, cảm nghĩ và bài phân tích của người đọc (`docs/25` mục 6, đợt 2–4); 5 người dùng thật đã thử (2 giáo viên, 2 người trẻ, 1 người am hiểu văn hóa dân gian), vướng mắc đã thành task; Q-006 đã chốt | Giữa–cuối 12/2026 |
| **C · Mở lặng lẽ** | Tên miền; 100–150 bài công khai; ảnh xem trước từng bài; thư đã gửi Ca dao Mẹ; người sáng lập quyết định mở (không thông báo rộng) | Trước Tết Đinh Mùi 2027 (đầu 02/2027) |

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
**Chấp nhận:** thư đã gửi trước T-053; kết quả (đồng ý / từ chối / im lặng) ghi vào `ghi_chu` của nguồn.

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

#### T-046 · Trang Phương pháp, Nguồn, Về dự án — N+A
**Đọc:** `docs/luu-tru/50-ban-nhap-trang-cong-khai.md` mục 1, 5; `12`.
**Chấp nhận:** Phương pháp giải thích 4 nhãn, lời hứa mới, cách dùng AI, ghi công Ca dao Mẹ; `/nguon` sinh tự động từ `content/nguon/` (chỉ nguồn đang được dùng); N duyệt câu chữ.

#### T-047 · Bàn biên tập — A
**Việc (viết lại 25/10/2026 cho WordPress):** một trang trong wp-admin gom các hàng chờ: bài Chờ duyệt, xác nhận Wikidata (T-086), vùng (T-089), thể loại tách (T-080), chủ đề (T-091), "Có thể trùng" (T-110, T-111, T-101), bài và góp ý người đọc gửi (T-045, T-097), mỗi hàng có số đếm và nút duyệt hàng loạt. Phần dưới đây là đặc tả cũ cho site Astro, giữ làm tham khảo.
**Chấp nhận (cũ):** trang `/ban-bien-tap` (noindex, khóa bằng Cloudflare Access) dựng từ `npm run health:json`: nháp AI chờ duyệt, bài công khai đang noindex, mục từ thiếu nguồn, tham chiếu web thiếu bản lưu trữ, chú thích trích nguyên văn chờ phép, tình huống chưa đủ bài, lỗi build gần nhất. Người sáng lập không cần dòng lệnh.

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

#### T-092 · Bài gần gũi, mô-típ — A
**Chấp nhận:** 3–5 thẻ cuối bài theo mô-típ, chú thích, địa danh, chủ đề; trang mô-típ do người sáng lập đặt tên/duyệt; không lặp chính bài đang xem.

#### T-093 · Bạn có biết? — N+A
**Chấp nhận:** khuôn ghép từ chú thích có sẵn, kèm nguồn; người sáng lập duyệt hoặc sửa trước khi lên trang; máy không tự viết sự kiện.

**Tiến độ (25/10/2026):** xong — mỗi ngày máy ghép thêm tới 8 câu chờ duyệt theo khuôn "… "[từ]" là [mệnh đề đầu của lời giải], và có trong câu "[tên bài]"?" từ mục từ đang được dùng (chỉ dùng chữ có sẵn), kèm nguồn của chú thích; trang **Bạn có biết?** sửa lời, chọn Duyệt / Bỏ, tự viết câu mới; trang chủ hiện 4 câu đã duyệt, xoay theo tuần. Việc của người sáng lập: duyệt, sửa câu cho tự nhiên.

#### T-094 · Trang thể loại — N+A
**Chấp nhận:** mỗi thể loại một trang (giới thiệu do người sáng lập viết, bài tiêu biểu, lối vào theo chủ đề và vùng).

**Tiến độ (25/10/2026):** xong — trang `/the-loai/<tên>/`: lời giới thiệu (mô tả của thể loại — người sáng lập viết ở Ca dao – Tục ngữ → Thể loại → Sửa; máy không viết), **bài tiêu biểu** (đọc nhiều nhất 30 ngày, chưa có lượt đọc thì bài mới), lối vào theo chủ đề và vùng đã duyệt, tổng số bài, rồi danh sách bài. Việc của người sáng lập: viết vài dòng giới thiệu cho từng thể loại.

#### T-095 · Câu một dòng — A
**Chấp nhận:** trang bài thành ngữ, tục ngữ chữ to, không khung lời thơ; chỉ mục A–Z và theo chủ đề.

#### T-096 · Câu đố — A
**Chấp nhận:** trang bài vẫn hiện đáp án (D-058); trang "Đố vui" 10 câu một lượt, chọn theo loại, điểm nhớ trên máy; câu đố cùng đáp án hiện cạnh nhau.

#### T-097 · Bài phân tích của người đọc — A
**Đọc:** `25` mục 4.6, D-062. **Chấp nhận:** loại bài "Cảm nhận – phân tích" gắn với bài ca dao; trang soạn đơn giản cho người đọc, gửi vào Chờ duyệt; cam kết tự viết + đồng ý CC BY 4.0; hiện dưới bài ca dao và có trang riêng; "Bài phân tích của dự án" có nhãn riêng; bài ca dao có ≥ 1 bài phân tích đã duyệt thì bỏ `noindex`.

#### T-048 · Duyệt 30–50 bài, thử với 5 người — N
**Đọc:** `11`; `docs/luu-tru/24-ke-hoach-beta.md` mục 2 (dùng nhiệm vụ N1–N6, không chấm ngưỡng 80%).
**Chấp nhận:** 30–50 bài trên bản xem trước; 5 người đã thử; vướng mắc thành task; Q-006 chốt.

### Mốc C · Mở lặng lẽ

#### T-049 · Tên miền — N
**Chấp nhận:** ghi D-xxx, xóa Q-005 khỏi câu hỏi mở (và khỏi `trang-thai.json`).

#### T-050 · Ảnh xem trước, noindex, SEO cơ bản — A — **chặn bởi Q-005**
**Đọc:** `21` mục 8; `25` mục 4.3 (ảnh chia sẻ, gộp T-060) và 4.6 (quy tắc noindex mới, D-062).
**Chấp nhận:** mỗi bài công khai có ảnh 1200×630 sinh lúc build (lời, nhãn, tên miền; dấu đúng với "Ngưỡng", "Thuở", "Quyện"); `noindex` theo `coGiaTriRieng()`; sitemap chỉ gồm trang được đánh chỉ mục; JSON-LD, canonical, 301 từ `redirects.yaml`.

#### T-051 · Đã lưu — A
**Ghi chú (25/10/2026):** Câu hôm nay, Vừa bổ sung chuyển sang T-043; cỡ chữ, sáng/tối, trình chiếu chuyển sang T-085. Phần còn lại: "Đã lưu".
**Chấp nhận (cũ, đọc theo ghi chú trên):** câu hôm nay chọn lúc build; "Vừa được bổ sung" từ lịch sử Git; "Đã lưu" lưu trên máy (`localStorage`, bọc try/catch, trang vẫn chạy khi bị chặn); chế độ trình chiếu toàn màn hình, chữ lớn, phím mũi tên sang bài kế; nút cỡ chữ "Aa" (3 mức) và nút sáng/tối, nhớ trên máy (`20` mục 1); không làm tăng JS ban đầu của trang tác phẩm quá ngân sách.

#### T-052 · Đo lường 3 sự kiện — A
**Đọc:** `21` mục 7. **Chấp nhận:** không cookie, không lưu IP; đúng 3 sự kiện `tim_khong_ket_qua`, `trich_dan_sao_chep`, `chu_giai_mo`; hướng dẫn đọc số liệu hằng tuần.

#### T-098 · Nghĩa thành ngữ, tục ngữ — N+A
**Đọc:** `25` mục 4.4, 4.5; D-061 (giấy phép A). **Chấp nhận:** khối nghĩa đen / nghĩa bóng / dùng khi / ví dụ / đồng nghĩa – trái nghĩa; phần từ Wiktionary ghi "Nguồn: Wiktionary ↗ · CC BY-SA" ngay dưới; phần do dự án viết không mang CC BY-SA.

#### T-099 · Chú giải từ sách hết bản quyền, Hán Việt — N+A
**Đọc:** `25` mục 4.4, 4.5. **Chấp nhận:** trích đoạn ghi tên sách, tác giả, năm, liên kết Wikisource; chỉ khớp chắc chắn; người sáng lập duyệt; chữ Hán kèm âm Hán Việt.

#### T-100 · Trợ giúp viết Hiểu nhanh — A
**Chấp nhận:** trình soạn hiện chú thích nguồn và nghĩa Wiktionary cạnh ô đoạn trích; nháp máy luôn ghi là nháp, chỉ lên trang khi người sáng lập duyệt.

#### T-101 · Gộp bài đã đăng bị trùng — N+A
**Đọc:** `25` mục 4.9 (ý 49, thay ý 17). **Việc:** mỗi đêm quét các bài đã đăng, đưa cặp nghi trùng vào hàng "Có thể trùng"; khi người sáng lập gộp: chọn bài chính, bài kia thành dị bản của bài chính, đường dẫn cũ chuyển hướng 301 về bài chính, lượt đọc cộng dồn, cảm nghĩ và bài phân tích chuyển sang bài chính, bài phụ vào thùng rác (không xóa hẳn). **Chấp nhận:** người sáng lập duyệt từng lần; sau khi gộp, link cũ mở đúng bài chính; không mất cảm nghĩ, bài phân tích, lượt đọc nào.

#### T-102 · Bối cảnh vè — N+A
**Chấp nhận:** khối bối cảnh (sự kiện, năm, nơi chốn, nhân vật qua Wikidata đã xác nhận); tóm tắt do người sáng lập viết hoặc duyệt; máy không tự viết sự kiện.

#### T-103 · Đồng dao — A
**Đọc:** `25` mục 4.4. **Chấp nhận:** khối "Cách chơi" (ghi nguồn Wikipedia CC BY-SA khi lấy từ đó); trang trò chơi; chế độ "Đọc cùng bé" mỗi lần một dòng, chữ to, không tự phát âm thanh.

#### T-104 · Bản đồ ca dao — A
**Chấp nhận:** bản đồ các địa danh đã xác nhận, chạm ra bài; tải nhẹ trên điện thoại; không gọi dịch vụ bản đồ khi người đọc chưa mở trang bản đồ.

#### T-105 · Giọng đọc sinh sẵn — N+A
**Đọc:** `25` mục 2.4, 4.8; D-063. **Chấp nhận:** VieNeu-TTS v3 Turbo trong tài khoản `cadao`, chạy ban đêm ưu tiên thấp; người sáng lập nghe 30 bài đủ thể loại và đạt trước khi sinh cả kho; giọng theo vùng; có dòng "Giọng đọc máy"; chỉ tải khi bấm nghe; bài sửa lời tự sinh lại.

#### T-106 · Giọng riêng — N
**Chấp nhận:** người được thu ký giấy đồng ý ghi phạm vi dùng; không dùng giọng có sẵn mang tên giống người thật nổi tiếng.

#### T-107 · Tìm "Tục ngữ phong dao" — N
**Việc:** tìm bản quét trên Gallica, Google Sách, thư viện; agent soạn hướng dẫn tìm khi cần. **Chấp nhận:** có đường dẫn bản quét rõ đọc được, hoặc ghi lại nơi đã tìm.

#### T-053 · Mở công khai lặng lẽ — N (Human Gate H5)
**Chấp nhận:** 100–150 bài `cong_khai`, 0 LỖI; thư đã gửi Ca dao Mẹ (T-034); trang Phương pháp và thông báo quyền riêng tư đã duyệt; người sáng lập quyết định mở, không thông báo rộng.

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
