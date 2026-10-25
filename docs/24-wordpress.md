# 24 · WordPress: thiết kế và chuyển đổi

| | |
|---|---|
| Phiên bản | 1.0 · 24/10/2026 (D-054, D-055, D-056) |
| Trạng thái | **Đang làm.** Site Astro (`docs/21`) vẫn chạy tới khi T-076 xong |
| Đọc khi | Làm T-071..T-076, hoặc bất kỳ việc gì trên WordPress. Gói nâng cấp 25/10/2026 (giao diện, nguồn nội dung, từng thể loại, lượt đọc, người đọc góp phần, giọng đọc): `25` |
| Mục tiêu | Người sáng lập đăng bài như WordPress quen thuộc; phần lớn nội dung tự lấy từ các nguồn cấu hình trong trang quản trị, vào **Chờ duyệt**, xem rồi bấm **Đăng** |

## 1. Luồng nội dung

```text
Nguồn tự động (cấu hình trong wp-admin)        Kho thu thập (bảng riêng)            Bài viết
───────────────────────────────────────        ─────────────────────────            ────────
Ca dao Mẹ · bật · 20 bài/ngày     ──lấy theo lịch──►  lời nguyên văn, dị bản,  ──mỗi ngày N bài──►  Chờ duyệt ──bấm Đăng──► trên trang
(nguồn khác: thêm bộ đọc)                         chú thích, địa chỉ trang
                                                                                    Nhập tay ──────────► Đăng ngay / Nháp
```

- **Nhập tay:** "Ca dao – Tục ngữ → Thêm mới": gõ tiêu đề, dán lời vào khối thơ, chọn thể loại → **Đăng**. Mọi ô khác tùy chọn.
- **Tự động:** bài vào trạng thái **Chờ duyệt** (Pending Review có sẵn của WordPress). Duyệt từng bài hoặc chọn nhiều bài → Sửa hàng loạt → Đã xuất bản.
- **Không chặn đăng vì lý do biên tập** (D-054). Không có "nhãn mức kiểm"; **lời ca dao, tục ngữ không ghi nguồn**; dòng **"Nguồn: … ↗"** chỉ hiện dưới chú thích, lời giải chép từ nguồn (tùy chọn) hoặc khi người viết tự điền hộp Nguồn (D-057).

## 2. Thành phần (mã trong repo này)

```text
wordpress/
├── plugins/ca-dao/          plugin của dự án — mọi chức năng riêng nằm ở đây
│   ├── ca-dao.php           khai báo plugin, nạp các phần
│   ├── inc/trich.php        trích lời tất định — khớp từng ký tự với bộ trích JavaScript của kho thô (100/100 bài mẫu)
│   ├── inc/loai-bai.php     loại bài, phân loại, hộp Nguồn, dòng "Nguồn: …"
│   ├── inc/kho.php          bảng kho thu thập, tạo bài Chờ duyệt từ kho
│   ├── inc/nguon-tu-dong.php trang cấu hình nguồn, lịch chạy
│   ├── inc/doc-cadao-me.php bộ đọc Ca dao Mẹ (trích tất định, không qua AI)
│   ├── inc/trang-quan-tri.php trang "Kho thu thập" (cột "Đã có dị bản, chú thích", bộ lọc "chỉ hiện câu đã đọc xong")
│   ├── inc/trich-dan.php    hộp Trích dẫn (2 kiểu), nút Chia sẻ trên trang bài (T-042)
│   ├── inc/luot-doc.php     lượt đọc không cookie, "Đọc nhiều tuần này", ô Bảng tin (T-081, D-064)
│   ├── inc/trung.php        chống trùng: dấu vân tay, mức giống, chỉ mục, gộp, tách, làm dần (T-109, T-110)
│   ├── inc/trang-trung.php  trang "Có thể trùng", ô "Có thể trùng" trong trình soạn (T-110, T-111)
│   ├── inc/tach-loai.php    gợi ý tách thành ngữ / tục ngữ, trang duyệt hàng loạt, chuyển hướng đường dẫn cũ (T-080)
│   ├── inc/site-cu.php      chuyển bài công khai của site Astro cũ, qua bước chống trùng (T-076)
│   ├── inc/the-tho.php      nhận thể thơ theo số âm tiết, phân loại "Thể thơ" để lọc (T-084)
│   ├── du-lieu/site-cu.json bài công khai của site cũ (sinh bằng `npm run xuat:site-cu` từ `content/`)
│   ├── assets/ca-dao.js     JavaScript duy nhất của trang bài (sao chép, chia sẻ, gửi lượt đọc; ~6 KB)
│   └── assets/soan-trung.js ô "Có thể trùng" trong trình soạn khối (chỉ trong trang quản trị)
├── themes/ca-dao/           theme con của Twenty Twenty-Five (T-072)
├── blueprint.json           dựng WordPress thử tại máy bằng Playground
└── tests/                   kiểm thử chạy trong Playground (kiem-thu.php, blueprint-kiem-thu.json)
```

**Theme `ca-dao`** (T-072, theme con của Twenty Twenty-Five, theo `20` mục 1, 4, 11):
- Màu: biến `--cadao-*` dùng `light-dark()` — sáng/tối theo cài đặt máy; bảng màu của `theme.json` trỏ vào các biến đó (giữ tên `base`, `contrast`, `accent-*` của theme cha).
- Phông tự lưu trữ trong `assets/fonts`: Be Vietnam Pro 400/600 (giao diện) và Literata 400 (lời), chỉ bộ Latin + tiếng Việt, WOFF2, kèm giấy phép OFL. Bề rộng nội dung 40rem (đơn vị rem: mọi khối thẳng một cột).
- Trang bài (`templates/single-cdtn.html`): nhãn thể loại → tiêu đề → lời (22 px, giãn dòng 1,7) → **Hiểu nhanh** (đoạn trích, chèn ngay dưới lời) → (câu đố: câu hỏi, đáp án hiện sẵn — D-058) → **Trích dẫn · Chia sẻ · N lượt đọc** (plugin chèn trước tiêu đề Dị bản đầu tiên) → Dị bản → Chú thích → "Nguồn: …". Mỗi dòng thơ một khối; dòng dài xuống dòng thì phần tràn thụt treo. **Lục bát** (≥ 3/4 cặp dòng 6–8 âm tiết) thụt lề câu lục — `CADAO_LUC_BAT_THUT_LE` trong `functions.php` (Q-006).
- Trang chủ, kho, thể loại: danh sách tiêu đề bài mới; trang chủ thêm "Đọc nhiều tuần này" (mã ngắn `[cadao_doc_nhieu]`, chỉ hiện khi đã có từ 3 bài được đọc trong 7 ngày). Trang chủ đầy đủ: T-043.
- **Đọc dễ hơn (T-082..T-084):** nhãn thể thơ cạnh nhãn thể loại; lục bát, song thất lục bát thụt câu lục; từ có chú thích trong lời chính là liên kết gạch chân chấm (chạm mở ô chú thích, tắt JavaScript thì nhảy tới mục chú thích); dị bản có nhãn "Dị bản N", chữ khác lời chính tô nền và gạch chân. Tất cả làm lúc dựng trang (`functions.php`), nội dung bài không đổi.
- **Trích dẫn, chia sẻ (T-042):** hộp `<details>` "Trích dẫn" — kiểu thông dụng và APA 7 (`20` mục 6), nút "Sao chép kèm nguồn" / "Chỉ sao chép lời", ô "Kèm đáp án / chú thích" khi bài có phần chép từ nguồn; nút "Chia sẻ" (Web Share, không có thì sao chép liên kết). Ngày truy cập theo ngày bấm.
- **Tách thành ngữ – tục ngữ (T-080):** trang **Ca dao – Tục ngữ → Tách thành ngữ – tục ngữ**, chia bốn nhóm: máy đã chắc / máy nghiêng về / máy chưa rõ / bạn đã quyết; nút "Đồng ý gợi ý", "Là thành ngữ", "Là tục ngữ". Gợi ý: danh mục Wiktionary (tải mỗi tuần, chỉ lưu dấu vân tay) khi độ dài câu cùng hướng; câu nhiều vế, từ 8 âm tiết, hai vế cách dấu phẩy → tục ngữ (chắc); có từ nối "thì, là, mới, hơn…" → nghiêng về tục ngữ; ≤ 4 âm tiết → nghiêng về thành ngữ. Chưa duyệt thì bài vẫn vào Tục ngữ như trước. Đổi thể loại thì đường dẫn cũ (`/tuc-ngu/<tên>/` ↔ `/thanh-ngu/<tên>/`) chuyển hướng 301 (mọi bài, kể cả đổi tay).
- **Lượt đọc (T-081, D-064):** trang bài gửi `POST /wp-json/cadao/v1/luot-doc/<id>` khi đã hiện 5 giây trên màn hình, mỗi máy một lần mỗi ngày (đánh dấu trong `localStorage`, không cookie); người đang đăng nhập có quyền sửa bài không tính; máy dò tự khai tên (Googlebot…, trình duyệt không giao diện) không tính. "N lượt đọc" hiện khi từ 10 lượt. Ô "lượt đọc 7 ngày" trên Bảng tin: tổng theo ngày, bài đọc nhiều, người đọc đến từ đâu (chỉ tên miền).
- Kiểm: `npm run quet:wordpress` (axe WCAG 2.2 A/AA, 360 px sáng/tối và máy tính, không tràn ngang; thử trên trình duyệt: "Sao chép kèm nguồn" ra đúng chữ, lượt đọc gửi một lần sau 5 giây, tải lại không gửi thêm, tắt JavaScript không gửi, không cookie) — CI chạy ở job "wordpress".

Giữ ít plugin bên thứ ba (mỗi plugin là một nguy cơ bảo mật). Dự kiến chỉ thêm: chống spam form, SEO (T-050). Sao lưu do FlashPanel làm.

## 3. Mô hình dữ liệu

| WordPress | Nội dung |
|---|---|
| Loại bài `cdtn` ("Ca dao – Tục ngữ") | Mỗi câu / bài một mục; đường dẫn `/ca-dao/<ten>/`, `/tuc-ngu/<ten>/`… theo thể loại đầu tiên (chưa chọn thì `ca-dao`); đường dẫn gốc `/bai/<ten>/` vẫn dẫn tới bài. Quy tắc đường dẫn là `add_rewrite_rule` đứng đầu — không dùng `add_rewrite_tag` trong slug (WordPress sinh quy tắc tệp đính kèm sai, nuốt mọi `/ca-dao/…`) |
| Tiêu đề | Thường là dòng đầu |
| Nội dung | **Khối Thơ** (core/verse) chứa lời; phần "Dị bản" (khối Thơ); phần "Chú thích" (danh sách, mỗi mục ghi "Theo …" khi chép từ nguồn) |
| Đoạn trích (excerpt) | "Hiểu nhanh" — một câu giải nghĩa (tùy chọn) |
| Phân loại `the_loai` | Ca dao · Tục ngữ · Thành ngữ · Đồng dao · Vè · Câu đố |
| Phân loại `chu_de`, `vung` | Chủ đề, vùng (tùy chọn) |
| Trường `nguon_ten`, `nguon_url`, `ngay_truy_cap` | Hộp Nguồn của bài nhập tay (tùy chọn) → dòng "Nguồn: …" cuối bài |
| Trường `_cadao_url_goc`, `_cadao_ngay_lay` | Trang gốc và ngày lấy của bài tự động — nội bộ, không hiện ra trang |
| Trường `_cadao_ma_goc`, `_cadao_nguon_id` | Mã bài trên nguồn và nguồn tự động đã lấy — để không lấy trùng |
| Cột kho `gop_vao`, `gop_lam`; trạng thái kho `da_gop` | Câu đã gắn vào bài / câu khác (`bai:<id>` hoặc `kho:<id>`): `trung` = cùng lời (bổ sung dị bản, chú thích của nguồn đó), `di_ban` = lời thành một dị bản. "Tách ra" trả lại thành câu mới |
| Bảng `wp_cadao_van_tay`, `wp_cadao_cap_am` | Chỉ mục chống trùng: dấu vân tay (1), (2) của lời chính và từng dị bản; cặp âm tiết liền nhau của lời chính (từ 6 âm tiết) |
| Bảng `wp_cadao_trung` | Hàng "Có thể trùng" và quyết định của người sáng lập (chờ, để sau, là bài khác, đã gộp) |
| Trường `_cadao_di_ban_them` | Dị bản thêm bằng tay hoặc qua "Thêm làm dị bản" (giữ khi bài được làm lại từ kho) |
| Bảng `wp_cadao_luot_doc` | Lượt đọc: (bài, ngày) → số lượt. Không lưu IP, không lưu ai đọc |
| Bảng `wp_cadao_gioi_thieu` | (ngày, tên miền trang giới thiệu) → số lượt; vào thẳng ghi "(vào thẳng)" |

## 4. Nguồn tự động

Trang **Ca dao – Tục ngữ → Nguồn tự động**. Mỗi nguồn: tên · kiểu bộ đọc · địa chỉ · loại lấy · bật/tắt · **số bài Chờ duyệt mỗi ngày** · tên ghi nguồn · **chép chú thích, lời giải** (bật/tắt, D-055) · **ghi nguồn cho chú thích** (bật/tắt, D-057) · bỏ qua bài gắn cờ (thô tục, trào phúng hiện đại — D-060: không lọc từ tục, ô "Thô tục" sẽ bỏ ở T-108; hiện để trống) · nút **Lấy ngay**.

Kiểu bộ đọc: `cadao_me` (T-073) — (1) tên chủ đề, (2) ca dao qua API, (3) thành ngữ–tục ngữ, đồng dao, vè, câu đố qua sitemap, (4) đọc trang HTML: dị bản, chú thích, câu hỏi và đáp án câu đố, lời của các loại ngoài ca dao. Mỗi trang có bài chính và ~20 bài liên quan; bài liên quan chỉ nhận khi là ca dao và lời trùng từng ký tự với lời API (bài dài chỉ hiện đoạn trích). Mục đã thành bài được đọc trước; còn lại xen kẽ các loại. **Chỉ tạo bài Chờ duyệt từ mục đã đọc trang** (bài có sẵn dị bản, chú thích). **Bài tạo từ kho mà chưa ai sửa tay** (nội dung còn đúng như máy tạo, nhận ra bằng dấu băm `_cadao_bam`) tự cập nhật khi kho có dị bản, chú thích mới; bài đã sửa tay giữ nguyên. Kiểm mẫu: 23/23 bài (đủ 5 loại) khớp từng ký tự với trang gốc; bộ trích trang khớp bộ trích JavaScript của kho thô trên 53 bài, 120 chú thích. Sau đó: `wordpress_api` (site WordPress bất kỳ). Site có cấu trúc riêng cần một bộ đọc mới — việc của agent; trang cấu hình chỉ chọn và chỉnh bộ đọc đã có.

**Lịch sự với máy chủ nguồn** (như `21` mục 16.2): tuần tự, ≥ 3 giây giữa hai yêu cầu API và ≥ 5 giây giữa hai trang HTML, User-Agent ghi tên dự án, gặp 429/5xx thì dừng tới lượt sau, lưu điểm dừng. Lịch: mỗi 5 phút một lượt — tối đa ~20 giây khi chạy trong yêu cầu web (WP-Cron, nút "Lấy ngay"), 120 giây khi chạy bằng cron thật của máy chủ (~20 trang mỗi lượt); tạo bài Chờ duyệt mỗi ngày một lần. Cuối lượt chỉ ghi điểm dừng và nhật ký lên cấu hình mới nhất (không đè thay đổi người quản trị vừa lưu).

**Chống trùng giữa các nguồn** (D-065, `25` mục 4.9; T-109, T-110 — plugin 0.5.0): `_cadao_ma_goc` chỉ chống lấy lại cùng một bài của cùng nguồn. Mỗi câu đọc xong trang gốc được so cả lời với bài đã có và các câu khác trong kho: **lời giống hệt** (kể cả chỉ khác dấu câu, chữ hoa, cách bỏ dấu) **và cùng thể loại** → tự gắn vào bài / câu có sẵn (Kho thu thập → tab "Đã gộp", bấm "Tách ra" nếu sai); bài đích chưa sửa tay được làm lại, có thêm dị bản, chú thích của nguồn kia và dòng "Nguồn" riêng. Mọi trường hợp khác — khác thể loại, khác vài chữ (≥ 90%), có thể là dị bản (50–90%), trùng một dị bản đã ghi — vào trang **Có thể trùng** (menu Ca dao – Tục ngữ, có số đếm; Bảng tin cũng báo) với "Gộp: là cùng một bài" / "Gộp làm dị bản" / "Là bài khác" / "Để sau"; máy tạo bài hằng ngày bỏ qua câu đang chờ. Khi nâng cấp, plugin làm dần chỉ mục cho bài và kho đã có rồi so từng câu (mỗi lượt 5 phút) — dọn luôn trùng sẵn có. `wp cadao trung-thong-ke` chạy lại phép đo 25/10/2026 (chỉ đọc).

**Khi viết tay (T-111):** trình soạn có ô **Có thể trùng** ở cột bên (tab Bài) và trong bước xác nhận khi bấm Đăng: sau khi dán / gõ lời (ngừng gõ 1,2 giây) máy liệt kê bài có lời giống kèm mức giống, nút **Mở bài này**, **Thêm làm dị bản** (lời vừa gõ thành dị bản của bài cũ, đúng từng ký tự; bản nháp vào thùng rác), **Vẫn đăng bài mới** (máy ghi là bài khác, không báo nữa). Không chặn đăng. Bài trùng bài khác có nhãn "Có thể trùng" trong danh sách bài.

## 5. Những điều vẫn giữ

- **Lời chép nguyên văn bằng máy, không qua AI**; AI không viết lời, dị bản, nguồn.
- **Lời không ghi nguồn** (D-057); không tự điền nguồn cho bài nhập tay.
- **Chú thích, lời giải** chỉ chép khi công tắc của nguồn đang bật (D-055); dòng "Nguồn: … ↗" dưới đó trỏ đúng trang đã lấy, tắt được bằng công tắc "Ghi nguồn cho chú thích" (D-057).
- **Không tải ảnh** của nguồn; không chép cách phân loại, bộ thẻ ra trang.
- **Google:** bài chưa có "Hiểu nhanh" (đoạn trích do dự án viết) mang `noindex` — lời và chú thích chép từ nguồn không tính là giá trị riêng (chính sách spam của Google về nội dung sao chép). Người đọc vẫn xem được. Từ D-062: bài có ít nhất một bài phân tích của người đọc đã duyệt cũng được tính là có giá trị riêng (T-097).
- Không thông tin cá nhân trong nội dung.

## 6. Phát triển và kiểm thử tại máy

WordPress Playground (PHP 8.5 — như máy chủ — chạy bằng WebAssembly, cơ sở dữ liệu SQLite, có `intl`) — không cần cài PHP/MySQL. Cần Node ≥ 24.18.

```bash
npm run test:wordpress            # kiểm thử plugin (CI chạy ở job "wordpress"); thêm -- --mang để thử bộ đọc trên cadao.me thật
npm run quet:wordpress           # quét trợ năng theme (WCAG 2.2 AA) trên bài mẫu tổng hợp; -- --anh <thư-mục> lưu ảnh chụp
npx @wp-playground/cli@3.1.57 server --port=9411 --blueprint=wordpress/blueprint.json \
  --mount-dir wordpress/plugins/ca-dao /wordpress/wp-content/plugins/ca-dao \
  --mount-dir wordpress/themes/ca-dao /wordpress/wp-content/themes/ca-dao
# mở http://127.0.0.1:9411/wp-login.php — tài khoản admin / password
```

Trên Windows (Git Bash) đặt `MSYS_NO_PATHCONV=1` để Git Bash không đổi `/wordpress/…` thành đường dẫn Windows. Bước `runPHP` của Playground không in ra màn hình: kiểm thử ghi kết quả vào `wordpress/tests/.ket-qua.txt`.

**Bản xem trước cho PR (D-035):** `npm run xem-truoc:wordpress` in một link playground.wordpress.net dựng WordPress ngay trên trình duyệt (máy tính hoặc điện thoại) và cài plugin từ nhánh hiện tại trên GitHub — nhánh phải đã đẩy lên. Mỗi lần mở là một site mới, trống; đóng tab là mất. API của Ca dao Mẹ cho phép playground.wordpress.net gọi tới (CORS), nên "Lấy ngay" có thể chạy ở đó; chạy chắc chắn là ở `npm run test:wordpress -- --mang`.

## 7. Chuyển đổi (T-076 — xong 25/10/2026)

- **Bài:** `npm run xuat:site-cu` chép các bài **công khai** của `content/tac-pham/` sang `wordpress/plugins/ca-dao/du-lieu/site-cu.json` (`kiem-tra` báo lỗi nếu tệp lệch `content/`). Chỉ chép những gì người biên soạn đã nhập: lời bản tham chiếu (nguyên văn), dị bản có nguồn, tên nguồn / địa chỉ / ngày truy cập đã điền, nghĩa ngắn → Hiểu nhanh, chú giải từ mục từ; nháp AI chỉ khi đã được duyệt (bỏ tiền tố, bài ghi "Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt."); bỏ mọi chuỗi `CAN_BO_SUNG`. Hai hồ sơ khung ở trạng thái nháp (`cd-0002`, `tn-0001`) không chuyển. Plugin nhập **một lần** sau khi chỉ mục chống trùng làm xong: bài trùng / gần trùng bài đã có vào WordPress ở dạng **Bản nháp** và hiện ở "Có thể trùng"; bài không trùng đăng như trên site cũ, giữ đường dẫn `/ca-dao/<tên>/`. Mục từ, tình huống, mô-típ chưa chuyển: dùng lại khi làm từ điển (T-044), trang tình huống (T-043).
- **Site Astro ngừng:** `wrangler.jsonc` không còn phục vụ `dist/`; Worker `cloudflare/chuyen-huong.js` chuyển hướng 301 mọi đường dẫn ở địa chỉ cũ (`ca-dao.truonganhquyen.workers.dev`) sang `https://cadao.sdigi.net` cùng đường dẫn. CMS Sveltia (`/admin`) không còn dùng — biên soạn trong WordPress. Mã Astro, `content/` và các công cụ kiểm dữ liệu vẫn ở repo (lưu trữ; `kiem-tra` vẫn chạy).

## 8. Máy chủ (T-074, T-075)

**cadao.sdigi.net** — VPS của người sáng lập (Ubuntu 24.04), quản lý bằng **FlashPanel** (gói miễn phí), web server Apache + PHP-FPM, MariaDB 10.11. Tên miền qua Cloudflare (proxy bật, chứng chỉ Let's Encrypt do FlashPanel cấp và gia hạn). VPS còn chạy **các site khác của người sáng lập**: chỉ đụng tới site cadao.

| Thành phần | Cách làm |
|---|---|
| Site, PHP 8.5, SSL, cài WordPress | Người sáng lập bấm trong FlashPanel |
| **Cách ly** | Gói miễn phí không có ô "Website Isolation" khi tạo site → *Cài Đặt → Update Owner* `cadao`: FlashPanel tạo tài khoản Linux riêng, pool PHP riêng, thêm vào nhóm `isolated` (ACL chặn vào `/home` của site khác) |
| Bổ sung cách ly | `cai-dat.sh` gỡ `cadao` khỏi nhóm `www-data` (nhóm đó gửi lệnh được tới pool PHP chung của các site khác), `wp-config.php` quyền 600 |
| Tiếng Việt, múi giờ, đường dẫn, plugin, cron | `wordpress/may-chu/cai-dat.sh` (chạy bằng root, chạy lại được) |
| **Tự cập nhật sau khi gộp PR** (T-078) | Cron mỗi 5 phút chạy `tu-cap-nhat.sh` bằng tài khoản `cadao`: thấy `main` có commit mới thì lấy `wordpress/plugins/ca-dao` và `wordpress/themes/ca-dao` của đúng commit đó (theme bật một lần; người quản trị đổi theme sau đó thì giữ), kiểm cú pháp PHP, thay thư mục một lần; trang chủ không trả 200 thì trả lại bản cũ và không thử lại commit đó. Nhật ký `/home/cadao/cap-nhat.log`. Chạy tay: `cd /home/cadao && sudo -u cadao bash bin/cap-nhat-plugin.sh` |
| Lịch chạy | `DISABLE_WP_CRON`; `/etc/cron.d/cadao-wordpress` chạy `wp cron event run --due-now` mỗi phút |
| WP-CLI | `sudo -u cadao /home/cadao/bin/wp <lệnh>` |
| Chặn công cụ tìm kiếm | `blog_public = 0` tới khi mở công khai (H5, T-053) — ô "Discourage search engines" của FlashPanel đã không có tác dụng, script đặt lại |
| Sao lưu | Người sáng lập: FlashPanel → *Sao Lưu*, hằng ngày, gồm site và cơ sở dữ liệu cadao, lưu ra ngoài VPS (Google Drive…) |

Agent vào VPS bằng khóa SSH riêng trên máy người sáng lập (không dùng mật khẩu). Không ghi IP, tài khoản hay thông tin các site khác vào repo.
