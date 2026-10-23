# 21 · Kiến trúc kỹ thuật

| | |
|---|---|
| Phiên bản | 1.2 · 06/10/2026 |
| Trạng thái | **Ổn định cho GĐ0–GĐ1.** Mục 9 (tìm ngữ nghĩa) chỉ làm khi qua cổng |
| Đọc khi | Khởi tạo kho mã, thêm thư viện, đổi cách build, triển khai |
| Nguyên tắc chi phối | Tĩnh trước, động sau · không máy chủ · không cookie · < 10 USD/tháng |

> Phiên bản cụ thể của Astro, MiniSearch và giới hạn gói miễn phí của từng dịch vụ có thể đã thay đổi. **Kiểm tra tài liệu chính thức hiện hành trước khi cài đặt**; ghi phiên bản đã chọn vào `01`.

---

## 1. Ngăn xếp công nghệ

| Thành phần | Chọn | Lý do | Thay thế |
|---|---|---|---|
| Khung web | **Astro** + TypeScript, xuất trang tĩnh | Content collections có schema Zod; ít JavaScript; "đảo" tương tác | Eleventy, Hugo |
| CSS | **Tailwind CSS 4**, cấu hình theo token `20` mục 1.1 (biến CSS) | Không tốn JS lúc chạy; agent quen dùng | CSS thuần với biến |
| Đảo tương tác | **TypeScript thuần** + `<dialog>`, thuộc tính `popover`, `<details>` của HTML | Giữ JS < 50 KB; không cần React runtime (D-030) | Preact nếu một đảo vượt quá khả năng xử lý gọn bằng TS thuần — ghi D-xxx trước khi thêm |
| Dữ liệu | YAML trong Git (GitHub) + một bản sao kho thứ hai | Lịch sử sửa đổi, sao lưu, duyệt thay đổi miễn phí | Phương án B (mục 12) |
| Kiểm tra dữ liệu | `scripts/kiem-tra-du-lieu.mjs` (Node), chạy **trước** build | Một nơi định nghĩa quy tắc R01–R19 (D-019) | — |
| Tìm từ khóa | **MiniSearch** trên trình duyệt, chỉ mục JSON sinh lúc build | Tự kiểm soát chuẩn hóa tiếng Việt, khớp gần đúng, trọng số theo trường | Pagefind — xem lại nếu chỉ mục nén > 300 KB (`mục 14`) |
| Thẻ chia sẻ | Canvas trên trình duyệt | Không máy chủ | Thư viện chụp DOM |
| Ảnh xem trước (OG) | Sinh lúc build (ví dụ Satori + resvg) | Không máy chủ | Ảnh tĩnh chung |
| Lưu trữ web | **Cloudflare Pages** | Build tự động khi đẩy mã; lỗi dữ liệu thì không triển khai | Netlify, GitHub Pages |
| Âm thanh | Internet Archive (bản gốc + bản phát); thêm Cloudflare R2 khi cần tốc độ phát | Bền, miễn phí | Chỉ R2 |
| Form đóng góp | Tally hoặc Google Forms → bảng tính riêng tư | Không máy chủ, không dữ liệu cá nhân trong Git | Worker + R2 tự xây (P2) |
| Đo lường | Công cụ không cookie, có sự kiện tùy chỉnh (Umami tự lưu trữ, GoatCounter, Plausible) | Tôn trọng quyền riêng tư; đo được Trích dẫn, Tạo thẻ | Cloudflare Web Analytics (không sự kiện tùy chỉnh) |
| Tìm ngữ nghĩa (theo cổng) | Cloudflare Worker + vector tính sẵn | Gần như miễn phí ở quy mô nhỏ | Vectorize, máy chủ nhỏ |
| Phát hành dữ liệu | GitHub Releases + Zenodo (DOI) | Trích dẫn học thuật | — |
| Cơ sở dữ liệu | **Chưa dùng** | Xem ngưỡng ở mục 11 | Postgres/Supabase |

## 2. Cấu trúc kho mã

```text
kho-ca-dao/
├── AGENTS.md  CLAUDE.md  README.md
├── docs/                    bộ tài liệu này
├── content/                 dữ liệu (đặc tả: docs/10)
├── taxonomy/                từ vựng kiểm soát
├── templates/               tệp mẫu YAML + lệnh AI
├── data/                    CSV làm việc (ưu tiên, tình huống, truy vấn thử)
├── scripts/
│   ├── kiem-tra-du-lieu.mjs       quy tắc R01–R19 + báo cáo sức khỏe (--suc-khoe)
│   ├── lib/neo-chu-giai.mjs       phân giải neo chú giải — dùng chung cho kiểm tra và build
│   ├── test-fixtures.mjs          kiểm thử chính công cụ kiểm tra
│   ├── tao-chi-muc-tim.mjs        (GĐ1) chỉ mục tìm kiếm
│   ├── so-khop-dong-gop.py        (GĐ2) so khớp đóng góp, docs/40
│   ├── danh-gia-ngu-nghia.py      (GĐ2) đo recall@5, docs/23
│   └── tao-embedding.py           (theo cổng) vector tìm ngữ nghĩa
├── tests/fixtures/          bộ dữ liệu lỗi mẫu
├── src/                     mã Astro: trang, thành phần, đảo tương tác
├── public/fonts/            phông WOFF2 tự lưu trữ
├── config/lich-tinh-huong.yaml    thứ tự thẻ tình huống theo dịp
└── redirects.yaml
```

## 3. Môi trường, build và triển khai

Ba môi trường, không hơn: **Cục bộ** · **Xem trước** (mỗi nhánh/PR, Cloudflare Pages tự tạo) · **Chính thức** (chỉ từ `main`).

| Ở đâu | Chạy | Kết quả |
|---|---|---|
| Cục bộ | `npm run check:data` → `npm run dev` | Xem trước trên máy |
| Mỗi PR (CI + bản xem trước) | `check:data` → `test:fixtures` → `astro check` → `astro build` → kiểm liên kết nội bộ trong `dist/` | Bản xem trước có đường dẫn riêng; lỗi bất kỳ bước nào → PR không gộp được |
| `main` | Như trên, chạy lại toàn bộ | Triển khai chính thức. Lỗi → **không triển khai**, bản cũ vẫn chạy |

Lệnh build trên Cloudflare Pages:
```bash
npm run check:data && npm run test:fixtures && astro build
```

- **Bảo vệ nhánh `main`:** không đẩy trực tiếp; chỉ gộp qua PR đã qua CI. Không sửa gì trực tiếp trên môi trường chính thức.
- Biên soạn nội dung cũng đi qua PR (người sáng lập tự duyệt được); AI agent luôn làm trên nhánh riêng.
- Mỗi quý: tạo bản phát hành dữ liệu (mục 10).

## 4. Content collections

- Mỗi thư mục trong `content/` là một collection, nạp bằng glob loader cho tệp `.yaml`.
- Schema Zod viết **theo** `docs/10`, chủ yếu để có kiểu dữ liệu trong mã. Quy tắc nghiệp vụ (R01–R19) chỉ nằm trong `scripts/kiem-tra-du-lieu.mjs` để không có hai nơi định nghĩa (D-019).
- Chỉ tạo trang cho tác phẩm `da_xuat_ban`; tác phẩm khác vẫn được kiểm nhưng không xuất hiện ở đâu trên site, chỉ mục, sitemap.
- Dữ liệu suy ra (`10` mục 10) tính trong một module dùng chung, ví dụ `src/lib/du-lieu.ts`, để mọi trang dùng cùng một cách tính.
- Vị trí chú giải **luôn** lấy từ `phanGiaiNeo()` trong `scripts/lib/neo-chu-giai.mjs` (import trực tiếp vào Astro). Không viết bộ phân giải thứ hai.

## 5. Tìm kiếm từ khóa (P0-Core)

**Chỉ mục** (`scripts/tao-chi-muc-tim.mjs`, chạy lúc build) gồm tác phẩm đã xuất bản, tình huống đã tạo trang, mục từ đang được dùng.

| Trường | Trọng số |
|---|---|
| Lời bản tham chiếu, `tieu_de` | ×3 |
| `mo_ta_tinh_huong`, `tu_khoa` và tiêu đề của các tình huống chứa bài | ×2 |
| `nghia_ngan`, tên mục từ được chú giải | ×1 |

- Mỗi trường đánh chỉ mục **hai lần**: bản có dấu đã chuẩn hóa và bản không dấu. Truy vấn có dấu ưu tiên khớp có dấu; truy vấn không dấu khớp bản không dấu.
- Khớp gần đúng (sai 1 ký tự) với từ dài ≥ 4 ký tự sau khi bỏ dấu.
- Kết quả trả về chỉ là id; nội dung hiển thị lấy từ dữ liệu đã có trong chỉ mục.
- Truy vấn không có kết quả: ghi sự kiện `tim_khong_ket_qua` chỉ với chuỗi truy vấn, không định danh.

**Hàm chuẩn hóa:**

```js
// Bỏ dấu để so khớp: "Công cha" → "cong cha", "Đình" → "dinh"
export function boDau(s) {
  return s.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
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

Đã thử với: hoà→hòa, thuỷ→thủy, khoẻ→khỏe, huỷ→hủy; giữ nguyên: toàn, hoàng, quý, quả, loẹt, thuyền. **Không** thay chuỗi đơn thuần kiểu `replaceAll("oà","òa")`: sẽ biến "toàn" thành "tòan". Đưa các ca trên vào kiểm thử đơn vị ở T-107, cùng truy vấn `q03`, `q04` trong `data/truy-van-thu.csv`.

## 6. Đảo tương tác (JavaScript phía trình duyệt)

| Đảo | Tải khi | Ghi chú | Lớp |
|---|---|---|---|
| Chú giải | Trang tác phẩm hiển thị | `<dialog>` (điện thoại) / `popover` (máy tính); không JS vẫn có chú thích cuối khối lời | P0-Core |
| Trích dẫn | Bấm nút | Clipboard API | P0-Core |
| Tìm kiếm | Chạm ô tìm | Tải chỉ mục lúc này | P0-Core |
| Trình tạo thẻ | Bấm "Tạo thẻ" | Nạp phông bằng `FontFace` trước khi vẽ | P0.5 |
| Trình phát âm thanh | Bấm "Nghe" | `<audio>` gốc, không thư viện | P0.5 |
| Nút đổi sáng/tối | Luôn | Rất nhỏ | P0.5 |

Tổng JS ban đầu < 50 KB (`20` mục 12).

## 7. Đo lường

Không cookie, không lưu IP, không công cụ quảng cáo. Chỉ đo thứ dùng để ra quyết định.

**P0-Core — 7 sự kiện:**

| Sự kiện | Thuộc tính | Dùng để quyết định |
|---|---|---|
| `tim` | độ dài truy vấn, có dấu hay không | Người dùng tìm thế nào |
| `tim_bam_ket_qua` | vị trí kết quả | Chất lượng xếp hạng |
| `tim_khong_ket_qua` | chuỗi truy vấn (không định danh) | Biên soạn bài nào tiếp theo |
| `trich_dan_sao_chep` | mã tác phẩm, định dạng | North Star |
| `nguon_mo` | mã tác phẩm | Người đọc có kiểm chứng không |
| `chu_giai_mo` | mã mục từ | Chú giải có được dùng không; mục từ nào cần viết kỹ |
| `bao_loi_bat_dau` | mã tác phẩm | Chất lượng nội dung |

**Từ P0.5 trở đi** (chỉ thêm khi tính năng tương ứng đã có): `the_tao`, `the_chia_se`, `am_thanh_phat`, `du_lieu_tai`, `tinh_huong_mo`, `di_ban_so_sanh`. Không đo bật/tắt chế độ tối hay đóng/mở từng mục nhỏ.

North Star (`00` mục 8) = `trich_dan_sao_chep` + `the_tao` + `du_lieu_tai` mỗi tuần (hai thành phần sau tính từ khi có tính năng).

## 8. SEO kỹ thuật

| Hạng mục | Quy định |
|---|---|
| Tiêu đề trang | `{tieu_de} – {Thể loại}, nghĩa, dị bản \| Kho tàng Ca dao` |
| Mô tả | `nghia_ngan` |
| JSON-LD | Tác phẩm: `CreativeWork` (`genre`, `inLanguage: "vi"`, `citation` trỏ nguồn, `license`). Mục từ: `DefinedTerm` trong `DefinedTermSet`. Tình huống: `CollectionPage` |
| Canonical | Mỗi trang tự trỏ canonical về chính nó; trang so sánh trỏ về trang tác phẩm |
| URL | Ổn định theo `slug`; đổi slug thì 301 qua `redirects.yaml` |
| Ngày cập nhật | Hiện trên trang và `dateModified` trong JSON-LD, lấy từ lần sửa nội dung cuối |
| Liên kết nội bộ | Tác phẩm ↔ tình huống ↔ mục từ ↔ chủ đề, sinh từ dữ liệu |
| Search Console | Đăng ký từ ngày ra mắt; truy vấn thật bổ sung danh sách ưu tiên |
| Sitemap | Sinh tự động, chỉ trang đã xuất bản |
| Trang mỏng | Danh mục < 3 mục: `noindex` |
| Chuyển hướng | 301 theo `redirects.yaml` |

Chiến lược SEO dựa trên chất lượng trang, không dựa trên số lượng trang. Dữ liệu có cấu trúc và DOI phục vụ **đúng nghĩa và lưu trữ học thuật**, không phải "lá chắn SEO": nếu trang khác chép nội dung, lợi thế thật của dự án là nguồn gốc, quan hệ nguồn–dị bản–mục từ, trích dẫn ổn định, bộ dữ liệu có phiên bản và uy tín.

## 9. Tìm ngữ nghĩa (chỉ khi qua cổng)

**Cổng (D-015):** từ mốc 150 hồ sơ, mỗi khi thêm 50 hồ sơ, chạy `scripts/danh-gia-ngu-nghia.py` trên ≥ 50 truy vấn thật có đáp án do người vận hành chọn. Bật khi **recall@5 ≥ 70%** (≥ 1 đáp án đúng trong 5 kết quả đầu).

**Kiến trúc:**
1. **Lúc build** (`tao-embedding.py`): với mỗi tác phẩm, ghép `tieu_de` + `nghia_ngan` + từng câu `mo_ta_tinh_huong` + tiêu đề các tình huống chứa bài. **Không dùng lời thơ.** Tạo vector bằng một mô hình nhúng đa ngữ hỗ trợ tốt tiếng Việt (chọn bằng thử nghiệm trên bộ truy vấn; ứng viên: BGE-M3, multilingual-e5). Lượng tử int8, lưu một tệp nhị phân.
2. **Lúc chạy:** Worker nhận truy vấn → tạo vector truy vấn **bằng đúng mô hình lúc build** (kiểm tra danh mục mô hình của nền tảng) → cosine với toàn bộ vector → 10 mã + điểm.
3. **Trình duyệt** hiển thị bằng dữ liệu đã có trong chỉ mục từ khóa.

**Quy tắc:**
- Gọi tìm ngữ nghĩa khi truy vấn ≥ 4 từ hoặc tìm từ khóa không có kết quả.
- Dưới ngưỡng điểm → "chưa tìm thấy", không trả "gần nhất".
- Lỗi hoặc chậm > 2 giây → quay về kết quả từ khóa, không báo lỗi.
- **Không sinh văn bản trả lời.** Kết quả luôn là tác phẩm có thật.
- Mô hình có tiền tố `query:` / `passage:` (dòng e5) thì làm đúng theo tài liệu mô hình.

## 10. Lưu trữ, sao lưu, phát hành dữ liệu

| Dữ liệu | Nơi chính | Sao lưu |
|---|---|---|
| `content/`, mã, tài liệu | GitHub | Bản sao kho thứ hai (GitLab hoặc ổ riêng), đồng bộ hằng tuần |
| Bản phát hành dữ liệu | GitHub Releases | Zenodo (DOI), hằng quý |
| Âm thanh gốc | Internet Archive | Ổ riêng |
| Bảng tính riêng tư (đóng góp, hồ sơ đồng ý) | Google Sheets hoặc tương đương, quyền truy cập hạn chế | Xuất hằng tháng, lưu mã hóa |
| Ảnh chụp trang sách (bằng chứng nội bộ) | Thư mục riêng tư | Như trên |

**Bản phát hành dữ liệu:** một JSON gộp các tác phẩm `da_xuat_ban` + mọi tệp chúng tham chiếu; loại bỏ dị bản D và mọi thứ trong kho riêng tư; kèm README (phiên bản schema, giấy phép, cách trích dẫn) và **nhật ký thay đổi** so với bản trước (tác phẩm/dị bản/nguồn thêm mới, sửa ghi nguồn, gộp trùng), sinh từ lịch sử Git. Đặt tên theo quý: `2027.1`, `2027.2`…

**Tệp nhị phân** (ảnh chụp trang sách, bản quét, PDF, âm thanh gốc) **không** đưa vào Git thường. Lưu ở nơi ghi trong bảng trên, và ghi **SHA-256** của mỗi tệp gốc vào bảng tính riêng tư để phát hiện tệp bị đổi hoặc hỏng khi khôi phục.

## 11. Khi nào chuyển sang cơ sở dữ liệu

Chỉ khi xảy ra một trong ba điều (D-003):
- hơn 2 biên tập viên làm việc thường xuyên;
- hơn 100 đóng góp mỗi tháng;
- việc duyệt cần chạy theo thời gian thực.

Khi đó schema YAML chuyển vào Postgres mà không cần thiết kế lại; trang công khai vẫn có thể tĩnh.

## 12. Phương án B (chờ Q-002)

Nếu người sáng lập không muốn làm việc trực tiếp với Git/YAML:
- **B1 — Bảng tính:** biên soạn trong Google Sheets/Airtable theo cột khớp `docs/10`; script xuất ra YAML; commit tự động; mọi bước sau giữ nguyên.
- **B2 — CMS chạy trên Git** (ví dụ Sveltia CMS, Decap CMS): có giao diện form, vẫn ghi YAML vào Git.

Cả hai giữ nguyên kiến trúc tĩnh và công cụ kiểm tra. Thử trên 10 hồ sơ mẫu ở GĐ0 trước khi chọn (T-007).

## 13. Kiểm tra trước khi chốt dịch vụ

- [ ] Giới hạn build/tháng và dung lượng của Cloudflare Pages.
- [ ] Giới hạn tệp tải lên của dịch vụ form (ảnh chụp trang sách, tệp ghi âm).
- [ ] Hạn mức Worker và dịch vụ suy luận (khi làm tìm ngữ nghĩa).
- [ ] Điều khoản lưu trữ của Internet Archive cho tệp âm thanh.
- [ ] Bật xác thực hai lớp cho mọi tài khoản dịch vụ; ghi chủ sở hữu vào `30` mục 5.

## 14. Đã thiết kế, chưa xây — điều kiện kích hoạt

Những thứ dưới đây hợp lý về lâu dài nhưng **không làm trong MVP** (D-031). AI agent không được tự bắt đầu chúng.

| Hạng mục | Hình dung | Chỉ bắt đầu khi |
|---|---|---|
| **Công cụ biên tập riêng (Admin workbench)** | Không phải CRUD. Màn hình chính là hàng đợi "hôm nay cần xử lý gì" và màn hình duyệt đặt cạnh nhau: trang nguồn/bản quét, văn bản trích, ứng viên, hồ sơ đã có, nghi trùng, quyền, cảnh báo kiểm tra; hành động: từ chối / cần bổ sung / gộp làm dị bản / tạo mới / duyệt. Giao diện dày thông tin, ưu tiên bàn phím, luôn hiện nguồn gốc và trạng thái. Đọc/ghi tệp trong Git, chưa cần cơ sở dữ liệu. Có thể dùng React + Vite + TanStack Table vì không chịu ngân sách JS của trang công khai | Duyệt hằng tuần vượt 2 giờ vì thao tác tệp, **hoặc** có hàng đợi ứng viên từ đường ống nạp tư liệu, **hoặc** có biên tập viên thứ hai không dùng Git |
| **Đường ống nạp tư liệu bán tự động** | nguồn → bản thô (bất biến) → trích xuất/OCR → AI tạo **ứng viên** (mang `NHAP_AI`) → chuẩn hóa → so trùng/so dị bản → kiểm quyền → người duyệt → hồ sơ trong `content/`. Ứng viên nằm ở thư mục riêng (ví dụ `ung-vien/`), **build không bao giờ đọc** | Có ≥ 20 hồ sơ đã duyệt làm dữ liệu chuẩn để đo xem phần nào đáng tự động hóa, **và** số liệu GĐ0 cho thấy nhập liệu (không phải tìm/xác minh nguồn) là nút thắt |
| **Rights Gate xanh/vàng/đỏ cho nguồn** | Thêm trường đánh giá quyền tái sử dụng cho tư liệu được nạp tự động | Cùng lúc với đường ống nạp tư liệu |
| **Monorepo** (`apps/web`, `apps/admin`, `packages/schema`, `packages/validation`…) | Tránh nhân đôi schema, kiểm tra, token khi có ứng dụng thứ hai | Khi bắt đầu Admin workbench |
| **Trường `cong_khai` cho ghi âm** | Ghi âm chỉ dùng để xác minh, không phát công khai | Trước bản ghi âm đầu tiên (T-209) |
| **Gộp/loại bỏ mã tác phẩm trùng** | Danh sách mã đã loại, kiểm không tái sử dụng | Khi gặp ca trùng thật đầu tiên (R19) |
| **Đổi sang Pagefind** | Tìm kiếm trên HTML đã build | Chỉ mục MiniSearch nén > 300 KB hoặc tìm chậm trên điện thoại tầm trung |
| **Cơ sở dữ liệu** | Xem mục 11 | Ngưỡng ở mục 11 |

## 15. Quy trình phát triển với AI agent

Quy trình phát triển là **hạ tầng làm phần mềm**, không phải thành phần runtime của website (D-033).

```text
GitHub repo
   │
   ├── Codex Cloud
   │     planner + implementer chính
   │
   └── Claude Code
         reviewer độc lập
```

### 15.1 GitHub là control plane

- `main` là nguồn sự thật đã được review.
- Mỗi task một branch/PR.
- Backlog, phase, cổng chất lượng và trạng thái nằm trong repo; không phụ thuộc lịch sử chat của agent.
- CI chạy `npm run kiem-tra` trên PR và `main` (D-034).
- Khi có `package-lock.json`, môi trường tự động dùng `npm ci`; trước đó có thể tạm `npm install`.

### 15.2 Codex Cloud

Mặc định làm bốn vai trò:

1. đọc trạng thái và cổng hiện tại;
2. tự chọn task `READY` theo `docs/03-quy-trinh-thuc-thi.md`;
3. phân rã và triển khai task trong phạm vi cho phép;
4. chạy kiểm tra, cập nhật trạng thái và mở PR.

Codex không được vượt Human Gate trong `AGENTS.md`.

### 15.3 Claude Code

Mặc định **không sửa code**. Claude đọc branch/PR và review độc lập theo `CLAUDE.md`, kết luận bằng một trong:

- `APPROVE`;
- `REQUEST CHANGES`;
- `HUMAN DECISION REQUIRED`.

Finding `BLOCKER`/`MAJOR` được chuyển lại cho Codex sửa trên cùng PR; người sáng lập không phải tự chuyển thành task kỹ thuật mới trừ khi đó là Human Gate.

### 15.4 Trạng thái không nằm trong chat

Các phiên AI có thể bị ngắt hoặc thay model. Vì vậy mọi trạng thái cần tiếp tục công việc phải nằm trong Git:

- `docs/02-trang-thai-hien-tai.md` — cho người đọc;
- `du-an/trang-thai.json` — cho máy đọc;
- `docs/22-backlog-trien-khai.md` — task và dependency;
- `docs/04-cong-chat-luong.md` — điều kiện chuyển phase;
- lịch sử commit/PR — dấu vết thay đổi.

Không dùng bộ nhớ hội thoại của Codex/Claude làm source-of-truth.
