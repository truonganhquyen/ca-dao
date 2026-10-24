# 22 · Backlog triển khai

| | |
|---|---|
| Phiên bản | 2.0 · 23/10/2026 (hướng v2, D-035..D-047) |
| Trạng thái | **Sống.** Cập nhật cột trạng thái ở mục 1 sau mỗi task/PR; enum: `READY`, `IN_PROGRESS`, `REVIEW`, `BLOCKED`, `DONE`, `DEFERRED` |
| Đọc khi | Nhận việc. Mỗi task ghi rõ cần đọc tài liệu nào |
| Ai làm | **N** = người sáng lập · **A** = AI agent hoặc dev · **N+A** = A làm, N duyệt và quyết định |
| Thay cho | Backlog v1 (T-001..T-304, P0-Core/P0.5, các cổng đếm hồ sơ): `docs/luu-tru/22-backlog-v1.md` |

**Cách nhận một task (cho cả người và AI agent):**
1. Nếu không được giao task cụ thể, đọc `docs/02-trang-thai-hien-tai.md`, `docs/03-quy-trinh-thuc-thi.md` và chạy `npm run viec-tiep-theo`.
2. Nhận task `READY` **hoặc task được `npm run viec-tiep-theo` suy ra là READY** vì mọi phụ thuộc đã `DONE` và Q-xxx trong cột "Chặn" đã được giải quyết.
3. Đọc các tài liệu trong "Đọc".
4. Làm trên một nhánh riêng `task/T-xxx-<mo-ta-ngan>`; mỗi task một PR.
5. Khi bắt đầu đổi trạng thái thành `IN_PROGRESS`. Khi implementation + tiêu chí chấp nhận + `npm run kiem-tra` đã đạt, đổi task thành `DONE` trong commit cuối của nhánh rồi mở PR.
6. Mô tả PR theo `.github/pull_request_template.md`; luôn kèm **link bản xem trước** — người sáng lập duyệt bằng mắt trên điện thoại, không đọc mã (D-035).

---

## 1. Bảng tổng

Mã task v2 bắt đầu từ T-031 để không trùng với backlog v1 đã lưu trữ.

| Task | Tên | Ai | Phụ thuộc | Chặn | Trạng thái |
|---|---|---|---|---|---|
| **Chuẩn bị · Tài khoản, tài liệu v2, thư cho nguồn** ||||||
| T-031 | Chốt gói quyết định D-035..D-047 | N | — | — | DONE |
| T-032 | Tài khoản GitHub (repo `ca-dao` công khai, `ca-dao-kho-tho` riêng tư — xong 24/10) và Cloudflare; cho Codex truy cập | N | — | — | IN_PROGRESS |
| T-033 | Cập nhật tài liệu, công cụ kiểm tra, mẫu theo hướng v2 | A | T-031 | — | DONE |
| T-034 | Gửi thư xin phép dùng chú thích cho Ca dao Mẹ | N | — | — | READY |
| **Mốc A · Chạy được** ||||||
| T-035 | Khởi tạo Astro 6 + Tailwind 4 + CI + triển khai Cloudflare | A | T-032, T-033 | — | BLOCKED |
| T-036 | Mô tả schema một nguồn → Zod + cấu hình CMS | A | T-035 | — | BLOCKED |
| T-037 | CMS Sveltia: đăng nhập GitHub, form tiếng Việt, "Thêm nhanh", duyệt nháp AI | A | T-036 | — | BLOCKED |
| T-038 | Thu thập kho thô từ cadao.me | A | T-032, T-033 | — | BLOCKED |
| T-039 | Đường ống ứng viên: kho thô → bài nháp trong content/ | A | T-036, T-038 | — | BLOCKED |
| T-040 | Trang tác phẩm đọc theo lớp, nhãn tự tính, nguồn gốc, sáng/tối, in, thanh điều hướng dưới | A | T-035 | — | BLOCKED |
| T-041 | Tìm kiếm: chỉ mục, chuẩn hóa tiếng Việt, giao diện | A | T-040 | — | BLOCKED |
| T-042 | Trích dẫn 2 định dạng + chia sẻ | A | T-040 | — | BLOCKED |
| **Mốc B · Thử nhỏ** ||||||
| T-043 | Trang tình huống + trang chủ | A | T-040 | — | BLOCKED |
| T-044 | Từ điển tự sinh | A | T-040 | — | BLOCKED |
| T-045 | Form góp ý + thông báo quyền riêng tư + "Gửi số trang" | N+A | T-040 | — | BLOCKED |
| T-046 | Trang Phương pháp, Nguồn, Về dự án | N+A | T-040 | — | BLOCKED |
| T-047 | Bàn biên tập | A | T-035 | — | BLOCKED |
| T-048 | Duyệt 30–50 bài, thử với 5 người | N | T-039, T-041..T-046 | — | BLOCKED |
| **Mốc C · Mở lặng lẽ** ||||||
| T-049 | Chốt tên miền | N | — | — | READY |
| T-050 | Ảnh xem trước từng bài, noindex tự động, SEO cơ bản | A | T-040 | Q-005 | BLOCKED |
| T-051 | Câu hôm nay, Vừa bổ sung, Đã lưu, Trình chiếu, cỡ chữ, nút sáng/tối | A | T-040 | — | BLOCKED |
| T-052 | Đo lường không cookie, 3 sự kiện | A | T-040 | — | BLOCKED |
| T-053 | Đủ 100–150 bài, mở công khai lặng lẽ | N | T-034, T-048..T-052 | — | BLOCKED |
| **Sau · Cải tiến liên tục** ||||||
| T-060 | Thẻ chia sẻ dạng ảnh (2 mẫu × 2 khổ) | A | T-050 | — | DEFERRED |
| T-061 | Thứ tự tình huống theo dịp | A | T-043 | — | DEFERRED |
| T-062 | Bộ bài cho tiết học từ "Đã lưu", in cả bộ | A | T-051 | — | DEFERRED |
| T-063 | So sánh dị bản | A | T-040 | — | DEFERRED |
| T-064 | Tìm kiếm mở rộng sang kho thô ("xem tại Ca dao Mẹ") | A | T-038, T-041 | — | DEFERRED |
| T-065 | Đọc ngoại tuyến, thêm vào màn hình chính | A | T-040 | — | DEFERRED |
| T-066 | Bản phát hành dữ liệu mở + DOI | A | T-053 | — | DEFERRED |
| T-067 | Thử nghiệm ghi âm cộng đồng | N | T-053 | — | DEFERRED |
| T-068 | Tìm theo ý (ngữ nghĩa), khi qua cổng recall@5 | A | T-041 | — | DEFERRED |
| T-069 | Mời hội đồng cố vấn | N | T-053 | — | DEFERRED |

Không có task cho cơ sở dữ liệu, công cụ quản trị riêng hay monorepo (D-047). Task `DEFERRED` chỉ mở khi người sáng lập chọn ở nhịp hằng tháng sau Mốc C.

## 2. Mốc và điều kiện xong

Thay cho các cổng đếm hồ sơ của v1 (D-044). Agent được tự chuyển `giai_doan` trong `du-an/trang-thai.json` sang mốc kế tiếp khi mọi task A của mốc hiện tại đã `DONE` và điều kiện dưới đây đạt; **riêng mở công khai (T-053) là Human Gate H5.**

| Mốc | Xong khi | Ước tính |
|---|---|---|
| **Chuẩn bị** | Gói D-035..D-047 nằm trong repo (T-033); có repo GitHub công khai, repo riêng tư cho kho thô và tài khoản Cloudflare (T-032) | Tuần cuối 10/2026 |
| **A · Chạy được** | Người sáng lập thêm một câu từ điện thoại qua `/admin` và thấy nó trên bản xem trước sau vài phút; 5–10 bài (mức nhãn bất kỳ) hiện đủ lớp 1, tìm không dấu và trích dẫn chạy được; kho thô có dữ liệu; `npm run kiem-tra` gồm `astro check` + build | Cuối 11/2026 |
| **B · Thử nhỏ** | 30–50 bài trên bản xem trước; trang tình huống, từ điển, form góp ý, trang Phương pháp chạy; 5 người dùng thật đã thử (2 giáo viên, 2 người trẻ, 1 người am hiểu văn hóa dân gian), vướng mắc đã thành task; Q-006 đã chốt | Giữa–cuối 12/2026 |
| **C · Mở lặng lẽ** | Tên miền; 100–150 bài công khai; ảnh xem trước từng bài; thư đã gửi Ca dao Mẹ; người sáng lập quyết định mở (không thông báo rộng) | Trước Tết Đinh Mùi 2027 (đầu 02/2027) |

Cổng tự động luôn áp dụng: `npm run kiem-tra` đạt (validator tầng LỖI, fixture, kiểm nhất quán; từ T-035 thêm `astro check`, build, kiểm liên kết), quét trợ năng và ngân sách JS (từ T-040).

## 3. Chi tiết task

### Chuẩn bị

#### T-032 · Tài khoản và đẩy kho mã — N
**Việc:** tạo tài khoản GitHub; repo **công khai** `truonganhquyen/ca-dao` cho kho mã này (bảo vệ nhánh `main` miễn phí với repo công khai; chặn agent đẩy thẳng, cho phép tài khoản người sáng lập/CMS); repo **riêng tư** `truonganhquyen/ca-dao-kho-tho` cho kho thô (T-038); tài khoản Cloudflare; bật xác thực hai lớp cho cả hai. Đẩy nhánh `main` và nhánh T-033 lên. Agent soạn hướng dẫn từng bước khi người sáng lập cần.
**Chấp nhận:** Codex Cloud truy cập được cả hai repo; CI chạy trên PR đầu tiên.
**Tiến độ (24/10/2026):** đã có 2 repo, đã đẩy `main` và nhánh T-033 (commit dùng email ẩn `…@users.noreply.github.com`), đã bật bảo vệ nhánh `main`. Còn: tài khoản Cloudflare, cho phép Codex truy cập 2 repo.

#### T-033 · Tài liệu và công cụ kiểm tra v2 — A
**Đọc:** `01` (D-035..D-047). **Việc:** áp dụng gói quyết định vào `AGENTS.md`, `docs/`, validator, thư viện suy ra, từ vựng, mẫu, fixture; chuyển tài liệu không còn dùng vào `docs/luu-tru/`.
**Chấp nhận:** `npm run kiem-tra` đạt; fixture phủ mọi quy tắc tầng LỖI có thể kiểm bằng `--content`; không còn tài liệu đang dùng nhắc tới cổng/trạng thái cũ.

#### T-034 · Thư cho Ca dao Mẹ — N
**Việc:** gửi thư thông báo + xin phép dùng chú thích qua form `cadao.me/dong-gop` (bản nháp thư trong `docs/12` mục 7). Có văn bản đồng ý thì đổi `ban_quyen` của `src-ca-dao-me` sang `duoc_phep` và ghi D-xxx.
**Chấp nhận:** thư đã gửi trước T-053; kết quả (đồng ý / từ chối / im lặng) ghi vào `ghi_chu` của nguồn.

### Mốc A · Chạy được

#### T-035 · Khởi tạo Astro + CI + triển khai — A
**Đọc:** `21` mục 1–3, `AGENTS.md`.
**Việc:** Astro 6 xuất tĩnh, TypeScript strict, Tailwind CSS 4 cấu hình từ token `20` mục 1.1; trang "Sắp ra mắt"; mở rộng `npm run kiem-tra` thêm `astro check`, `astro build`, kiểm liên kết nội bộ trong `dist/`; CI dùng `npm ci`; triển khai Cloudflare với bản xem trước cho mỗi nhánh/PR (người sáng lập bấm kết nối theo hướng dẫn); ghi phiên bản Astro, Tailwind vào `01`.
**Chấp nhận:**
- [ ] `npm run kiem-tra` gồm `astro check` + build; dữ liệu có LỖI thì build thất bại với thông báo đọc được.
- [ ] Mỗi PR có link bản xem trước.
- [ ] Không thêm thư viện nào ngoài Astro, Tailwind, `yaml` (adapter Cloudflare chỉ khi thật cần).

#### T-036 · Schema một nguồn → Zod + cấu hình CMS — A
**Đọc:** `10` mục 4, 9; `21` mục 4.
**Việc:** chuyển mô tả schema (`SCHEMA` trong `scripts/kiem-tra-du-lieu.mjs`) sang `scripts/lib/schema.mjs` để validator dùng chung; viết script sinh (1) schema Zod cho 7 collection, (2) cấu hình Sveltia CMS, (3) kiểu TypeScript cho từ vựng từ `taxonomy/tu-vung-kiem-soat.yaml`. Quy tắc nghiệp vụ R01–R22 **không** viết lại trong Zod.
**Chấp nhận:**
- [ ] `astro check` qua với dữ liệu hiện có; fixture vẫn đạt.
- [ ] Đổi một giá trị trong tệp từ vựng → chạy lại script → kiểu và cấu hình CMS đổi theo; `kiem-tra` báo lỗi nếu tệp sinh ra lệch với nguồn.

#### T-037 · CMS Sveltia — A
**Đọc:** `21` mục 12; `11`.
**Việc:** Sveltia CMS (ghim phiên bản) ở `/admin`; đăng nhập GitHub qua một Worker OAuth nhỏ; nhãn tiếng Việt, ví dụ trong từng ô; form tác phẩm theo thứ tự **Lời → Nguồn → Nghĩa → Chú giải → Tình huống → Nâng cao** (âm luật, diễn xướng, kiểm chứng: thu gọn); bộ "**Thêm nhanh**" (tiêu đề, lời, vùng, url nguồn) tạo bài `nhap`; ô đánh dấu `nhap_ai_da_duyet` ghi rõ "Tôi đã đọc và đồng ý mọi phần do AI soạn"; quan hệ tới mục từ, nguồn, mô-típ dạng chọn từ danh sách; hướng dẫn 1 trang cho người sáng lập (gồm cách thêm bài vào tình huống — quan hệ lưu ở tệp tình huống, D-010).
**Chấp nhận:** người sáng lập tạo một bài từ điện thoại tới `cong_khai` trên bản xem trước mà không thấy YAML; lỗi kiểm tra hiện được cho người sáng lập (email CI + Bàn biên tập).

#### T-038 · Kho thô từ cadao.me — A
**Đọc:** `21` mục 16; `12` mục 4; D-045, D-046.
**Việc:** theo đặc tả `21` mục 16: thu thập **toàn bộ lời** (và dị bản), danh sách từ có chú thích, bản tham chiếu chú thích vào **repo riêng tư**; chạy nhỏ giọt bằng GitHub Action có điểm dừng; cập nhật hằng tháng.
**Chấp nhận:**
- [ ] Không một byte nào của kho thô vào repo công khai.
- [ ] Lời lưu nguyên văn (so khớp từng ký tự với trang gốc trên mẫu 20 bài), không qua AI.
- [ ] Nhịp ≤ 1 yêu cầu/3 giây, dừng khi gặp 429/5xx, User-Agent ghi tên dự án.
- [ ] Không tải ảnh; báo cáo số mục theo loại.

#### T-039 · Đường ống ứng viên — A
**Đọc:** `21` mục 16; `11`; `templates/prompt-soan-nhap.md`.
**Việc:** nhận danh sách mục tiêu (dòng đầu hoặc mã kho thô); với mỗi mục: tìm trong kho thô, gom bản trùng/gần trùng, đề xuất bản phổ biến nhất làm `v1`; tạo `content/tac-pham/<id>.yaml` trạng thái `nhap` với nguồn `src-ca-dao-me` (url, ngày truy cập, bản lưu Wayback nếu lưu được); gợi ý cụm từ cần chú giải từ danh sách từ có chú thích; bước **soạn nháp AI** (nghĩa ngắn, mô tả tình huống, giải nghĩa mục từ viết lại theo ý — `cach_dung: tham_khao`) chạy trong phiên agent theo mẫu lệnh, không cần khóa API riêng; **kiểm chống chép** (độ trùng n-gram giữa nháp và chú thích gốc, vượt ngưỡng thì gắn cờ); kiểm trùng với `content/` (R19).
**Chấp nhận:** từ 10 dòng đầu mục tiêu sinh 10 bài `nhap` có lời khớp kho thô từng ký tự; `npm run kiem-tra` đạt; không bài nào `cong_khai` hay `nhap_ai_da_duyet: true` do máy đặt; lời không mang `NHAP_AI`.

#### T-040 · Trang tác phẩm — A
**Đọc:** `20` mục 4–5, 10–12, 14; `10` mục 5, 10.
**Việc:** trang `/ca-dao/{slug}`, `/tuc-ngu/{slug}`, `/thanh-ngu/{slug}` cho bài `cong_khai`, đọc theo lớp (`20` mục 4.1); nhãn từ `tinhNhan()`, dị bản từ `diBanHienThi()`, chuỗi qua `chuoiHienThi()` (cùng `scripts/lib/suy-ra.mjs`); vị trí chú giải từ `phanGiaiNeo()`; khối Nguồn gốc ở lớp 1; dòng "có hỗ trợ AI" khi `nhap_ai_da_duyet`; `noindex` khi chưa có giá trị riêng; sáng/tối theo hệ thống; bản in; thanh điều hướng dưới trên điện thoại.
**Chấp nhận:**
- [ ] Bài nhãn D hiện nhãn "Lời phổ biến – chưa đối chiếu" và dòng nguồn gốc; không có chữ nào trong danh sách từ cấm.
- [ ] Không còn chuỗi `CAN_BO_SUNG` hay `NHAP_AI` nào lộ ra trang.
- [ ] Tắt JavaScript: đọc được lời, chú giải (cuối khối lời), nguồn gốc.
- [ ] Đạt `20` FR-1.8 trên màn hình 360 px; WCAG 2.2 AA cho phần đã làm.

#### T-041 · Tìm kiếm — A
**Đọc:** `21` mục 5, `20` mục 8, `23` mục 4.
**Chấp nhận:** chỉ mục sinh lúc build, tải trễ khi chạm ô tìm; `boDau` và `chuanHoaCoDau` có kiểm thử đơn vị với các ca trong `21` mục 5; truy vấn kiểu `khong_dau`, `tu_khoa`, `dau_kieu_cu` trong `data/truy-van-thu.csv` có đáp án trong 5 kết quả đầu (khi đáp án đã công khai); trang không kết quả đúng `20` mục 8.

#### T-042 · Trích dẫn và chia sẻ — A
**Đọc:** `20` mục 6–7.
**Chấp nhận:** 2 định dạng; nút "Sao chép kèm nguồn" (mặc định) và "Chỉ sao chép lời"; bài nhãn D trích dẫn kèm "(lời lưu truyền, chưa đối chiếu bản in)" và ghi công Ca dao Mẹ; ngày truy cập tự điền; nút Chia sẻ dùng Web Share (văn bản + URL), không hỗ trợ thì sao chép link.

### Mốc B · Thử nhỏ

#### T-043 · Tình huống và trang chủ — A
**Chấp nhận:** trang tình huống chỉ tạo khi có ≥ `NGUONG_TRANG_TINH_HUONG` (2) bài công khai; mỗi bài hiện lời rút gọn ≤ 4 dòng + "Hợp khi…"; trang chủ: câu định vị, ô tìm lớn, thẻ tình huống theo `mac_dinh` trong `config/lich-tinh-huong.yaml`.

#### T-044 · Từ điển — A
**Chấp nhận:** `/tu-dien/` liệt kê mục từ đang được dùng; trang mục từ hiện giải nghĩa, nguồn ("Tham khảo: Ca dao Mẹ ↗" khi `cach_dung: tham_khao`, "Theo Ca dao Mẹ ↗" khi `trich_nguyen_van`), mọi bài có từ này.

#### T-045 · Form góp ý — N+A
**Đọc:** `docs/luu-tru/40-quy-trinh-dong-gop.md` mục 2–3.
**Chấp nhận:** form trên dịch vụ có sẵn (Tally), tối đa 5 câu bắt buộc; có **thông báo quyền riêng tư ngay trong form** (Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15, hiệu lực 01/01/2026; dịch vụ đặt máy chủ ở nước ngoài); nút "Ở quê bạn câu này nói khác?", "Báo lỗi" và **"Bạn có sách? Gửi số trang"** mở form với mã bài và URL điền sẵn; người ghi hộ điền xong trên điện thoại trong 5 phút.

#### T-046 · Trang Phương pháp, Nguồn, Về dự án — N+A
**Đọc:** `docs/luu-tru/50-ban-nhap-trang-cong-khai.md` mục 1, 5; `12`.
**Chấp nhận:** Phương pháp giải thích 4 nhãn, lời hứa mới, cách dùng AI, ghi công Ca dao Mẹ; `/nguon` sinh tự động từ `content/nguon/` (chỉ nguồn đang được dùng); N duyệt câu chữ.

#### T-047 · Bàn biên tập — A
**Chấp nhận:** trang `/ban-bien-tap` (noindex, khóa bằng Cloudflare Access) dựng từ `npm run health:json`: nháp AI chờ duyệt, bài công khai đang noindex, mục từ thiếu nguồn, tham chiếu web thiếu bản lưu trữ, chú thích trích nguyên văn chờ phép, tình huống chưa đủ bài, lỗi build gần nhất. Người sáng lập không cần dòng lệnh.

#### T-048 · Duyệt 30–50 bài, thử với 5 người — N
**Đọc:** `11`; `docs/luu-tru/24-ke-hoach-beta.md` mục 2 (dùng nhiệm vụ N1–N6, không chấm ngưỡng 80%).
**Chấp nhận:** 30–50 bài trên bản xem trước; 5 người đã thử; vướng mắc thành task; Q-006 chốt.

### Mốc C · Mở lặng lẽ

#### T-049 · Tên miền — N
**Chấp nhận:** ghi D-xxx, xóa Q-005 khỏi câu hỏi mở (và khỏi `trang-thai.json`).

#### T-050 · Ảnh xem trước, noindex, SEO cơ bản — A — **chặn bởi Q-005**
**Đọc:** `21` mục 8.
**Chấp nhận:** mỗi bài công khai có ảnh 1200×630 sinh lúc build (lời, nhãn, tên miền; dấu đúng với "Ngưỡng", "Thuở", "Quyện"); `noindex` theo `coGiaTriRieng()`; sitemap chỉ gồm trang được đánh chỉ mục; JSON-LD, canonical, 301 từ `redirects.yaml`.

#### T-051 · Câu hôm nay, Vừa bổ sung, Đã lưu, Trình chiếu, cỡ chữ, sáng/tối — A
**Chấp nhận:** câu hôm nay chọn lúc build; "Vừa được bổ sung" từ lịch sử Git; "Đã lưu" lưu trên máy (`localStorage`, bọc try/catch, trang vẫn chạy khi bị chặn); chế độ trình chiếu toàn màn hình, chữ lớn, phím mũi tên sang bài kế; nút cỡ chữ "Aa" (3 mức) và nút sáng/tối, nhớ trên máy (`20` mục 1); không làm tăng JS ban đầu của trang tác phẩm quá ngân sách.

#### T-052 · Đo lường 3 sự kiện — A
**Đọc:** `21` mục 7. **Chấp nhận:** không cookie, không lưu IP; đúng 3 sự kiện `tim_khong_ket_qua`, `trich_dan_sao_chep`, `chu_giai_mo`; hướng dẫn đọc số liệu hằng tuần.

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
