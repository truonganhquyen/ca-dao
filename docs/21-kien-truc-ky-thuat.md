# 21 · Kiến trúc kỹ thuật

| | |
|---|---|
| Phiên bản | 2.1 · 24/10/2026 (Astro 7, Cloudflare Workers Static Assets: D-049; CMS Sveltia, kho thô, đường ống thu thập: D-036, D-045, D-047) |
| Trạng thái | **Ổn định cho Mốc A–C.** Mục 9 (tìm ngữ nghĩa) chỉ làm khi qua cổng |
| Đọc khi | Khởi tạo kho mã, thêm thư viện, đổi cách build, triển khai, CMS, thu thập dữ liệu |
| Nguyên tắc chi phối | Tĩnh trước, động sau · không máy chủ (trừ Worker nhỏ cho đăng nhập CMS) · không cookie · < 10 USD/tháng cho hạ tầng |

> Phiên bản cụ thể của Astro, Tailwind, Sveltia CMS, MiniSearch và giới hạn gói miễn phí của từng dịch vụ có thể đã thay đổi. **Kiểm tra tài liệu chính thức hiện hành trước khi cài đặt**; ghi phiên bản đã chọn vào `01`. Chi phí công cụ AI (Codex, Claude) không nằm trong ngân sách hạ tầng.

---

## 1. Ngăn xếp công nghệ

| Thành phần | Chọn | Lý do | Thay thế |
|---|---|---|---|
| Khung web | **Astro 7** + TypeScript strict, xuất trang tĩnh (phiên bản ghim: D-049) | Content collections có schema Zod; ít JavaScript; Fonts API có sẵn; đội Astro về Cloudflare từ 01/2026 | Eleventy |
| CSS | **Tailwind CSS 4**, cấu hình theo token `20` mục 1.1 (biến CSS, `light-dark()`) | Không tốn JS lúc chạy | CSS thuần với biến |
| Đảo tương tác | **TypeScript thuần** + `<dialog>`, thuộc tính `popover`, `<details>` | Giữ JS < 50 KB; không cần React (D-030) | Preact nếu một đảo vượt quá khả năng xử lý gọn — ghi D-xxx trước |
| Dữ liệu | YAML trong Git (repo công khai) | Lịch sử sửa đổi, sao lưu, duyệt thay đổi miễn phí | — |
| Biên tập | **Sveltia CMS** (ghim phiên bản) ở `/admin`, ghi YAML vào Git qua GitHub; đăng nhập qua một Worker OAuth nhỏ (D-036) | Người sáng lập không dùng Git/YAML; sửa được trên điện thoại | Pages CMS (dịch vụ có sẵn) |
| Kiểm tra dữ liệu | `scripts/kiem-tra-du-lieu.mjs` (Node), chạy **trước** build | Một nơi định nghĩa quy tắc R01–R22 (D-019) | — |
| Dữ liệu suy ra | `scripts/lib/suy-ra.mjs` (nhãn, dị bản hiển thị, chuỗi hiển thị, noindex) | Một nơi tính, dùng chung cho kiểm tra và build (D-041) | — |
| Tìm từ khóa | **MiniSearch** trên trình duyệt, chỉ mục JSON sinh lúc build | Tự kiểm soát chuẩn hóa tiếng Việt, khớp gần đúng, trọng số theo trường | Pagefind — xem lại nếu chỉ mục nén > 300 KB |
| Ảnh xem trước (OG) | Sinh lúc build (ví dụ Satori + resvg) | Không máy chủ | Ảnh tĩnh chung |
| Lưu trữ web | **Cloudflare Workers Static Assets** + Workers Builds (đã chọn ở T-035, D-049; cấu hình `wrangler.jsonc`) | Build tự động; lỗi dữ liệu thì không triển khai; bản xem trước mỗi nhánh | Netlify, GitHub Pages |
| Kho thô | **Repo GitHub riêng tư `truonganhquyen/ca-dao-kho-tho`** + GitHub Actions theo lịch (mục 16) | Thu thập toàn bộ lời mà không công bố (D-045) | Cloudflare R2 |
| Form góp ý | Tally (hoặc Google Forms) → bảng tính riêng tư | Không máy chủ, không dữ liệu cá nhân trong Git | — |
| Đo lường | Công cụ không cookie (Cloudflare Web Analytics cho lượt xem; GoatCounter hoặc tương đương cho 3 sự kiện) | Tôn trọng quyền riêng tư | — |
| Bàn biên tập | Trang tĩnh `/ban-bien-tap` sinh từ `npm run health:json`, khóa bằng Cloudflare Access (gói miễn phí) | Người sáng lập biết việc cần làm mà không dùng dòng lệnh (D-047) | — |
| Tìm ngữ nghĩa (theo cổng) | Cloudflare Worker + vector tính sẵn | Gần như miễn phí ở quy mô nhỏ | — |
| Phát hành dữ liệu | GitHub Releases + Zenodo (DOI) | Trích dẫn học thuật | — |
| Cơ sở dữ liệu | **Chưa dùng** | Xem ngưỡng ở mục 11 | Postgres |

## 2. Cấu trúc kho mã

```text
ca-dao/                      (repo CÔNG KHAI: github.com/truonganhquyen/ca-dao)
├── AGENTS.md  CLAUDE.md  README.md
├── docs/                    tài liệu (docs/huong-dan/: hướng dẫn từng bước cho người sáng lập; docs/luu-tru/: tài liệu v1.2 không còn dùng hằng ngày)
├── content/                 dữ liệu công khai (đặc tả: docs/10) — kể cả bài nháp
├── taxonomy/                từ vựng kiểm soát
├── templates/               tệp mẫu YAML + lệnh AI
├── data/                    CSV làm việc (tình huống khởi đầu, truy vấn thử)
├── du-an/trang-thai.json    trạng thái máy đọc
├── scripts/
│   ├── kiem-tra-du-lieu.mjs       quy tắc R01–R22 + báo cáo sức khỏe (--suc-khoe)
│   ├── kiem-nhat-quan.mjs         backlog / trạng thái / câu hỏi mở khớp nhau
│   ├── lib/neo-chu-giai.mjs       phân giải neo chú giải — dùng chung cho kiểm tra và build
│   ├── lib/suy-ra.mjs             dữ liệu suy ra — dùng chung cho kiểm tra và build
│   ├── lib/backlog.mjs            đọc trạng thái và backlog
│   ├── kiem-lien-ket.mjs          kiểm liên kết nội bộ trong dist/ (kiểm thử: tests/lien-ket/)
│   ├── lib/schema.mjs             (T-036) mô tả schema một nguồn → Zod + cấu hình CMS
│   ├── ung-vien/                  (T-039) đường ống kho thô → bài nháp
│   └── tao-chi-muc-tim.mjs        (T-041) chỉ mục tìm kiếm
├── tests/fixtures/          bộ dữ liệu kiểm thử (tests/lien-ket/: bản build mẫu cho bộ kiểm liên kết)
├── src/                     mã Astro: trang, thành phần, đảo tương tác; src/styles/global.css: token màu + Tailwind
├── public/                  tệp tĩnh chép nguyên vào dist/
├── public/admin/            Sveltia CMS (cấu hình sinh tự động ở T-036)
├── astro.config.mjs  tsconfig.json  wrangler.jsonc  .node-version
├── config/lich-tinh-huong.yaml
└── redirects.yaml

ca-dao-kho-tho/              (repo RIÊNG TƯ: github.com/truonganhquyen/ca-dao-kho-tho — mục 16; không bao giờ trộn vào repo công khai)
```

## 3. Môi trường, build và triển khai

Ba môi trường: **Cục bộ** · **Xem trước** (mỗi nhánh/PR) · **Chính thức** (chỉ từ `main`).

| Ở đâu | Chạy | Kết quả |
|---|---|---|
| Cục bộ | `npm run kiem-tra` → `npm run dev` | Xem trước trên máy |
| Mỗi PR (CI + bản xem trước) | `npm run kiem-tra` = dữ liệu → fixture → kiểm thử bộ kiểm liên kết → nhất quán → `astro check` → `astro build` → kiểm liên kết nội bộ trong `dist/` | Bản xem trước có đường dẫn riêng; lỗi bất kỳ bước nào → PR không gộp được |
| `main` | Như trên | Triển khai chính thức. Lỗi → **không triển khai**, bản cũ vẫn chạy |

- **Triển khai (T-035, D-049):** Cloudflare **Workers Builds** nối với repo `ca-dao` (người sáng lập kết nối một lần: `docs/huong-dan/ket-noi-cloudflare.md`). Lệnh build `npm run build` = `check:data` rồi `astro build` — có LỖI dữ liệu thì dừng trước khi build, không triển khai. Nhánh `main` → `npx wrangler deploy` (chính thức); nhánh khác → `npx wrangler preview` (mặc định của Workers Builds, bản thử nghiệm mở; tạo bản xem trước, link hiện trong bình luận của Cloudflare ở PR). `wrangler.jsonc` chỉ phục vụ `dist/` (`not_found_handling: 404-page`), không có mã máy chủ; khối `previews: {}` bắt buộc cho `wrangler preview`. Phiên bản Node lấy từ `.node-version`, CI dùng cùng tệp.
- **Mã:** mọi thay đổi qua PR; người sáng lập duyệt bằng cách mở **link bản xem trước** trên điện thoại rồi bấm gộp — không đọc mã (D-035). Claude Code review độc lập trước đó.
- **Nội dung:** người sáng lập lưu bài trong CMS → commit thẳng vào `main` (D-036) → CI kiểm → tự triển khai; có LỖI thì bản cũ vẫn chạy, GitHub gửi email, Bàn biên tập hiện lý do.
- **Bảo vệ nhánh `main`:** chặn agent đẩy thẳng (agent luôn làm trên nhánh riêng); cho phép tài khoản người sáng lập/CMS. Repo công khai nên tính năng này miễn phí.
- **PR của đường ống ứng viên (T-039)** chỉ chứa bài `nhap` mới (không hiện trên site) → được tự gộp khi CI đạt.

## 4. Content collections

- Mỗi thư mục trong `content/` là một collection, nạp bằng glob loader cho tệp `.yaml`.
- Mô tả schema **một nguồn** (`scripts/lib/schema.mjs`, T-036) sinh ra: schema Zod (để có kiểu trong mã), cấu hình Sveltia CMS, kiểu TypeScript cho từ vựng. Quy tắc nghiệp vụ R01–R22 chỉ nằm trong `scripts/kiem-tra-du-lieu.mjs` (D-019).
- Chỉ tạo trang cho tác phẩm `cong_khai`; tác phẩm khác vẫn được kiểm nhưng không xuất hiện ở đâu trên site, chỉ mục, sitemap.
- Dữ liệu suy ra (`10` mục 10) lấy từ `scripts/lib/suy-ra.mjs`; vị trí chú giải lấy từ `phanGiaiNeo()` trong `scripts/lib/neo-chu-giai.mjs` (import trực tiếp vào Astro). Không viết bản tính thứ hai.

## 5. Tìm kiếm từ khóa

**Chỉ mục** (`scripts/tao-chi-muc-tim.mjs`, chạy lúc build) gồm tác phẩm công khai, tình huống đã tạo trang, mục từ đang được dùng.

| Trường | Trọng số |
|---|---|
| Lời bản tham chiếu (mọi dòng), `tieu_de` | ×3 |
| `mo_ta_tinh_huong`, `tu_khoa` và tiêu đề của các tình huống chứa bài | ×2 |
| `nghia_ngan`, tên mục từ được chú giải | ×1 |

- Mỗi trường đánh chỉ mục **hai lần**: bản có dấu đã chuẩn hóa và bản không dấu. Truy vấn có dấu ưu tiên khớp có dấu; truy vấn không dấu khớp bản không dấu.
- Khớp gần đúng (sai 1 ký tự) với từ dài ≥ 4 ký tự sau khi bỏ dấu.
- Chỉ đưa vào chỉ mục chuỗi đi qua `chuoiHienThi()` (không `CAN_BO_SUNG`, không nháp AI chưa duyệt).
- Truy vấn không có kết quả: ghi sự kiện `tim_khong_ket_qua` chỉ với chuỗi truy vấn, không định danh.

**Hàm chuẩn hóa:**

```js
// Bỏ dấu để so khớp: "Công cha" → "cong cha", "Đình" → "dinh"
export function boDau(s) {
  return s.normalize('NFD').replace(/[̀-ͯ]/g, '')
    .replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase().trim();
}

// Chuẩn hóa có dấu: đưa kiểu bỏ dấu cũ về kiểu mới, CHỈ ở âm tiết mở (không phụ âm cuối)
// và không sau "q": "hoà" → "hòa", "thuỷ" → "thủy"; "toàn", "quý", "loẹt" giữ nguyên.
const BANG_DAU = [['oà','òa'],['oá','óa'],['oả','ỏa'],['oã','õa'],['oạ','ọa'],
                  ['oè','òe'],['oé','óe'],['oẻ','ỏe'],['oẽ','õe'],['oẹ','ọe'],
                  ['uỳ','ùy'],['uý','úy'],['uỷ','ủy'],['uỹ','ũy'],['uỵ','ụy']];
const MAP = Object.fromEntries(BANG_DAU);
const RE = new RegExp(`(?<!q)(${BANG_DAU.map(([c]) => c).join('|')})(?=$|[^\\p{L}])`, 'gu');
export function chuanHoaCoDau(s) {
  return s.normalize('NFC').toLowerCase().replace(RE, m => MAP[m]);
}
```

Đã thử (kiểm lại 23/10/2026): hoà→hòa, thuỷ→thủy, khoẻ→khỏe, huỷ→hủy; giữ nguyên: toàn, hoàng, quý, quả, loẹt, thuyền. **Không** thay chuỗi đơn thuần kiểu `replaceAll("oà","òa")`: sẽ biến "toàn" thành "tòan". Hàm này chỉ dùng **trong chỉ mục**; chiều chuẩn hóa không ảnh hưởng cách viết hiển thị (văn bản dự án tự viết theo SGK — "hoà", D-038). Đưa các ca trên vào kiểm thử đơn vị ở T-041, cùng truy vấn `q03`, `q04` trong `data/truy-van-thu.csv`.

## 6. Đảo tương tác (JavaScript phía trình duyệt)

| Đảo | Tải khi | Ghi chú | Mốc |
|---|---|---|---|
| Chú giải | Trang tác phẩm hiển thị | `<dialog>` (điện thoại) / `popover` (máy tính); không JS vẫn có chú thích cuối khối lời | A |
| Trích dẫn | Bấm nút | Clipboard API | A |
| Chia sẻ | Bấm nút | Web Share API; không có thì sao chép link | A |
| Tìm kiếm | Chạm ô tìm | Tải chỉ mục lúc này | A |
| Lưu, cỡ chữ, sáng/tối | Luôn | Rất nhỏ; `localStorage` bọc try/catch | C |
| Trình chiếu | Bấm nút | Fullscreen API + phím mũi tên | C |
| Trình tạo thẻ | Bấm "Tạo thẻ" | Nạp phông bằng `FontFace` trước khi vẽ | Sau |

Tổng JS ban đầu < 50 KB (`20` mục 12).

## 7. Đo lường

Không cookie, không lưu IP, không công cụ quảng cáo. Chỉ đo thứ dùng để ra quyết định (D-047):

| Sự kiện | Thuộc tính | Dùng để quyết định |
|---|---|---|
| `tim_khong_ket_qua` | chuỗi truy vấn (không định danh) | Biên soạn bài nào tiếp theo |
| `trich_dan_sao_chep` | mã tác phẩm, định dạng | North Star |
| `chu_giai_mo` | mã mục từ | Mục từ nào cần viết kỹ |

Thêm sự kiện chỉ khi có tính năng tương ứng và một quyết định cần đến nó (ghi D-xxx).

## 8. SEO kỹ thuật

| Hạng mục | Quy định |
|---|---|
| Tiêu đề trang | `{tieu_de} – {Thể loại}, nghĩa, dị bản \| Kho tàng Ca dao` |
| Mô tả | `nghia_ngan` (qua `chuoiHienThi()`) |
| Đánh chỉ mục | Trang tác phẩm chỉ được đánh chỉ mục khi `coGiaTriRieng()` đúng; còn lại `noindex` và không vào sitemap (D-043) |
| JSON-LD | Tác phẩm: `CreativeWork` (`genre`, `inLanguage: "vi"`, `citation` trỏ nguồn, `license`). Mục từ: `DefinedTerm`. Tình huống: `CollectionPage` |
| Canonical | Mỗi trang tự trỏ canonical về chính nó |
| URL | Ổn định theo `slug`; đổi slug thì 301 qua `redirects.yaml`. Dùng tên miền thật từ khi mở công khai (Q-005) |
| Ngày cập nhật | Hiện trên trang và `dateModified` trong JSON-LD, lấy từ lịch sử Git |
| Liên kết nội bộ | Tác phẩm ↔ tình huống ↔ mục từ, sinh từ dữ liệu; liên kết ra nguồn (Ca dao Mẹ) ở mỗi bài lấy từ đó |
| Search Console | Đăng ký từ ngày mở công khai |
| Trang mỏng | Danh mục < 3 mục: `noindex` |

Chiến lược SEO dựa trên chất lượng trang và giá trị riêng, không dựa trên số lượng trang — kho thô vài nghìn bài **không** được xuất bản hàng loạt (D-045): trang sao chép hàng loạt có thể khiến cả tên miền bị hạ hạng.

## 9. Tìm ngữ nghĩa (chỉ khi qua cổng, T-068)

**Cổng (D-015):** từ mốc 150 bài công khai, mỗi khi thêm 50 bài, chạy đánh giá trên ≥ 50 truy vấn thật có đáp án do người vận hành chọn. Bật khi **recall@5 ≥ 70%**.

**Kiến trúc:** lúc build, với mỗi tác phẩm ghép `tieu_de` + `nghia_ngan` + `mo_ta_tinh_huong` + tiêu đề các tình huống chứa bài (**không dùng lời thơ**), tạo vector bằng một mô hình nhúng đa ngữ (ứng viên: BGE-M3, multilingual-e5); lúc chạy, Worker tạo vector truy vấn **bằng đúng mô hình lúc build**, trả 10 mã + điểm; trình duyệt hiển thị bằng dữ liệu đã có trong chỉ mục từ khóa.

**Quy tắc:** gọi khi truy vấn ≥ 4 từ hoặc tìm từ khóa không có kết quả; dưới ngưỡng điểm → "chưa tìm thấy"; lỗi hoặc chậm > 2 giây → quay về kết quả từ khóa; **không sinh văn bản trả lời**.

## 10. Lưu trữ, sao lưu, phát hành dữ liệu

| Dữ liệu | Nơi chính | Sao lưu |
|---|---|---|
| `content/`, mã, tài liệu | GitHub (repo công khai) | Bản sao kho thứ hai, đồng bộ hằng tuần (sau Mốc C) |
| Kho thô | GitHub (repo riêng tư) | Có thể thu thập lại từ nguồn; giữ bản nén hằng quý |
| Bảng tính riêng tư (đóng góp) | Dịch vụ bảng tính, quyền truy cập hạn chế | Xuất hằng tháng, lưu mã hóa |
| Bản phát hành dữ liệu (Sau) | GitHub Releases | Zenodo (DOI), hằng quý |

**Bản phát hành dữ liệu** (T-066): JSON gộp các tác phẩm `cong_khai` + mọi tệp chúng tham chiếu; loại dị bản D không hiển thị và mọi thứ riêng tư; mục có `trich_nguyen_van` đánh dấu không thuộc CC BY; kèm README (phiên bản schema, giấy phép CC BY 4.0, cách trích dẫn) và nhật ký thay đổi sinh từ lịch sử Git.

## 11. Khi nào chuyển sang cơ sở dữ liệu

Chỉ khi xảy ra một trong ba điều (D-003): hơn 2 biên tập viên làm việc thường xuyên; hơn 100 đóng góp mỗi tháng; việc duyệt cần chạy theo thời gian thực. Khi đó schema YAML chuyển vào Postgres mà không cần thiết kế lại; trang công khai vẫn có thể tĩnh.

## 12. CMS (D-036)

Người sáng lập không làm việc trực tiếp với Git/YAML (Q-002). Chọn **CMS chạy trên Git** để giữ nguyên kiến trúc tĩnh và công cụ kiểm tra:

- **Sveltia CMS** (tương thích cấu hình Decap; còn ở bản 0.x → **ghim phiên bản**, nâng cấp có chủ đích): một tệp JS ở `/admin`, giao diện dùng được trên điện thoại, đăng nhập GitHub qua Worker OAuth nhỏ trên Cloudflare.
- **Cấu hình sinh tự động** từ mô tả schema (T-036): nhãn tiếng Việt, ví dụ trong từng ô, thứ tự Lời → Nguồn → Nghĩa → Chú giải → Tình huống → Nâng cao; quan hệ (mục từ, nguồn, mô-típ) dạng chọn từ danh sách.
- Bộ **"Thêm nhanh"** tạo bài `nhap` với tối thiểu trường; ô đánh dấu `nhap_ai_da_duyet`.
- **Dự phòng:** Pages CMS (dịch vụ có sẵn, cấu hình `.pages.yml`, có trường tham chiếu) nếu Sveltia không đáp ứng.

## 13. Kiểm tra trước khi chốt dịch vụ

- [ ] Giới hạn build/tháng và dung lượng của Cloudflare (Workers Static Assets / Pages).
- [ ] Hạn mức GitHub Actions cho repo riêng tư (kho thô chạy theo lịch).
- [ ] Phiên bản Sveltia CMS và cách đăng nhập GitHub hiện hành.
- [ ] Nơi đặt máy chủ và điều khoản dữ liệu của dịch vụ form (Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15, hiệu lực 01/01/2026).
- [ ] Bật xác thực hai lớp cho mọi tài khoản dịch vụ.

## 14. Đã thiết kế, chưa xây — điều kiện kích hoạt

AI agent không được tự bắt đầu những hạng mục dưới đây.

| Hạng mục | Chỉ bắt đầu khi |
|---|---|
| Công cụ quản trị riêng | Có biên tập viên thứ hai thường xuyên mà CMS + Bàn biên tập không đủ |
| Monorepo | Khi có ứng dụng thứ hai cần dùng chung schema |
| Trường `cong_khai` cho ghi âm (chỉ để xác minh) | Trước bản ghi âm đầu tiên (T-067) |
| Gộp/loại bỏ mã tác phẩm trùng | Khi gặp ca trùng thật đầu tiên (R19) |
| Đổi sang Pagefind | Chỉ mục MiniSearch nén > 300 KB hoặc tìm chậm trên điện thoại tầm trung |
| Cơ sở dữ liệu | Ngưỡng ở mục 11 |

Đường ống nạp tư liệu bán tự động **đã được kích hoạt** bởi D-045 — xem mục 16.

## 15. Quy trình phát triển với AI agent

Quy trình phát triển là **hạ tầng làm phần mềm**, không phải thành phần runtime của website (D-033).

```text
Người sáng lập ── CMS (/admin) ──────────────► main (nội dung)
                └─ mở link xem trước, bấm gộp ◄┐
GitHub repo ── Codex hoặc Claude Code (planner + implementer) → PR → CI (npm run kiem-tra)
            └─ Claude Code (reviewer độc lập) ─────────┘
```

- `main` là nguồn sự thật. Mỗi task một nhánh/PR; PR luôn có link bản xem trước.
- Backlog, mốc, trạng thái nằm trong repo (`docs/02`, `docs/22`, `du-an/trang-thai.json`); `npm run kiem-nhat-quan` báo khi chúng lệch nhau.
- Codex tự chọn task theo `docs/03`, không vượt Human Gate trong `AGENTS.md`.
- Codex và Claude Code ngang quyền (D-048): bên nào cũng triển khai hoặc review. Khi review, theo `CLAUDE.md` và kết luận `APPROVE` / `REQUEST CHANGES` / `HUMAN DECISION REQUIRED`. Người làm PR không tự review PR của mình.
- Mọi trạng thái cần để tiếp tục công việc nằm trong Git, không trong bộ nhớ hội thoại của agent.

## 16. Kho thô và đường ống thu thập (T-038, T-039)

Kích hoạt bởi D-045, D-046. Nguồn đầu mối chính: Ca dao Mẹ (`12` mục 6).

### 16.1. Kho thô (repo riêng tư)

```text
ca-dao-kho-tho/
├── nguon/cadao-me/
│   ├── bai.jsonl                  mỗi dòng một mục (lời, dị bản, siêu dữ liệu)
│   ├── chu-thich.jsonl            bản tham chiếu chú thích — CHỈ để đọc ý, không bao giờ sang repo công khai
│   ├── trang-thai-thu-thap.json   điểm dừng để chạy tiếp
│   └── bao-cao.md                 số mục theo loại, lỗi, lần chạy gần nhất
├── tu-tho-tuc.txt                 danh sách từ thô tục để gắn cờ (người sáng lập duyệt)
├── scripts/thu-thap-cadao-me.mjs
└── .github/workflows/thu-thap.yml lịch: hằng ngày trong đợt đầu, hằng tháng sau đó
```

**Trường của `bai.jsonl`:** `ma_goc` (id bài trên nguồn), `url`, `loai_goc` (ca dao / thành ngữ–tục ngữ / đồng dao / vè / câu đố), `tieu_de`, `loi` (nguyên văn, NFC, giữ xuống dòng), `di_ban` (danh sách lời nguyên văn), `chu_de_goc`, `the_goc` (chỉ dùng nội bộ làm gợi ý), `tu_co_chu_thich` (danh sách từ, không kèm lời giải), `ngay_dang_goc`, `ngay_sua_goc`, `ngay_thu_thap`, `bam` (SHA-256 của `loi`), `co` (gắn cờ, mục 16.3).

**Trường của `chu-thich.jsonl`:** `ma_goc`, `url`, `tu`, `noi_dung`, `ngay_thu_thap`. Không tải ảnh.

### 16.2. Cách thu thập Ca dao Mẹ

- **Ca dao** (~11.800 mục): API WordPress công khai `GET /wp-json/wp/v2/posts?per_page=100&page=N&_fields=id,link,title,content,categories,tags,date,modified` — khoảng 120 lần gọi; tên chủ đề/thẻ lấy từ `/wp-json/wp/v2/categories` và `/tags`.
- **Thành ngữ–tục ngữ, đồng dao, vè, câu đố** (~4.700 mục): không có trong API → đọc danh sách từ sitemap (`thanh-ngu-tuc-ngu-sitemap*.xml`, `dong-dao-sitemap.xml`, `ve-sitemap.xml`, `cau-do-sitemap.xml`), rồi tải từng trang HTML.
- **Trích lời tất định, không qua AI:** lấy khối nội dung bài; `<br>` → xuống dòng; bỏ ký hiệu và khối chú thích (HTML của site có các lớp `footnote`, `ref-content`, `ret-note` — xác định bộ chọn chính xác khi làm, kiểm trên mẫu 20 bài); giải mã thực thể HTML; NFC; cắt khoảng trắng đầu/cuối dòng; **không** chuẩn hóa gì khác. Khối "Dị bản" → `di_ban`. Chú thích → `chu-thich.jsonl` + `tu_co_chu_thich`.
- **Lịch sự với máy chủ:** tuần tự, ≤ 1 yêu cầu/3 giây với API và ≤ 1 yêu cầu/5 giây với HTML; User-Agent `KhoTangCaDao-ThuThap/1.0 (+<URL dự án>)`; gặp 429/5xx thì dừng và để lần chạy sau; lưu điểm dừng; cập nhật hằng tháng chỉ lấy mục mới/sửa (`modified_after` của API, `lastmod` của sitemap).

### 16.3. Gắn cờ (không xóa)

- Chủ đề gốc "Trào phúng, phê phán đả kích", "Đấu tranh, phản kháng", "Lịch sử" → `can_xem`.
- Chứa từ trong `tu-tho-tuc.txt` → `tho_tuc`.
- Có năm 19xx/20xx hoặc thẻ tên nhân vật hiện đại → `hien_dai` (gợi ý; người biên soạn quyết, D-008).

Bài có cờ không được đường ống đề xuất cho MVP trừ khi người sáng lập chọn đích danh.

### 16.4. Đường ống ứng viên (T-039)

1. **Đầu vào:** danh sách mục tiêu (dòng đầu, hoặc `ma_goc`) — người sáng lập dán vào ô nhập của GitHub Action (`workflow_dispatch`) trên giao diện web, hoặc giao cho Codex.
2. **Tìm và gom:** chuẩn hóa (NFC, chữ thường, bỏ dấu câu, bỏ dấu) → so khớp Jaccard trên cặp âm tiết liền kề; ≥ 0,9 = cùng bản, 0,4–0,9 = ứng viên dị bản. Bản có nhiều mục giống hệt nhất làm `v1` (bản tham chiếu đề xuất); bản khác thành `v2…` (nhãn D, không hiện trên trang cho tới khi có nguồn tốt hơn).
3. **Tạo tệp** `content/tac-pham/<id>.yaml`, trạng thái `nhap`; mỗi dị bản có `nguon: [{ ref: src-ca-dao-me, url, ngay_truy_cap, ban_luu_tru }]`. Bản lưu Wayback (`https://web.archive.org/save/<url>`) làm theo khả năng, có giới hạn nhịp; lưu không được thì để trống (NHẮC R22).
4. **Gợi ý chú giải:** từ `tu_co_chu_thich` → `chu_giai` với `cum_tu` (kiểm bằng `phanGiaiNeo()`); mục từ chưa có thì tạo `content/muc-tu/` nháp.
5. **Soạn nháp AI** (trong phiên agent, theo `templates/prompt-soan-nhap.md`, không cần khóa API riêng): `nghia_ngan`, `mo_ta_tinh_huong`, `giai_nghia` viết lại theo ý chú thích tham chiếu — mọi chuỗi mang `NHAP_AI`, nguồn ghi `cach_dung: tham_khao`. **AI không chạm vào `loi`.**
6. **Chống chép:** so độ trùng cụm 3 âm tiết giữa nháp AI và `chu-thich.jsonl`; trùng > 50% thì liệt kê trong mô tả PR để người biên soạn viết lại.
7. **Kiểm trùng** với `content/` (R19) trước khi tạo; trùng thì báo, không tạo.
8. **Đầu ra:** một nhánh + PR "T-039: thêm N bài nháp" kèm báo cáo (cờ, nghi chép, bản lưu thất bại). Chỉ chứa bài `nhap` → tự gộp khi CI đạt. Máy **không bao giờ** đặt `cong_khai`, `nhap_ai_da_duyet` hay `trich_nguyen_van`.
