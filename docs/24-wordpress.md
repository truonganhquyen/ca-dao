# 24 · WordPress: thiết kế và chuyển đổi

| | |
|---|---|
| Phiên bản | 1.0 · 24/10/2026 (D-054, D-055, D-056) |
| Trạng thái | **Xong (T-076, T-117).** Site Astro đã gỡ khỏi repo (D-071); WordPress là site duy nhất |
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
- **Không chặn đăng vì lý do biên tập** (D-054). Không có huy hiệu mức kiểm; dưới lời có **một dòng "lời và mức kiểm" do máy tính** (D-072, T-119): mặc định "chưa đối chiếu sách", chỉ thành "Lời đã đối chiếu với …" khi người biên soạn tự điền hộp **Lời đã đối chiếu với** (meta `doi_chieu_ten`, `doi_chieu_trang`, `doi_chieu_url`; hộp nguồn cũ là nguồn của chú thích chép); **lời ca dao, tục ngữ không ghi nguồn web**; dòng **"Nguồn: … ↗"** chỉ hiện dưới chú thích, lời giải chép từ nguồn (tùy chọn) hoặc khi người viết tự điền hộp Nguồn (D-057).

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
│   ├── inc/an-toan.php      khoá cửa quản trị: XML-RPC tắt, không lộ tên đăng nhập, tạm khoá khi sai nhiều (T-124)
│   ├── inc/mo-tip.php       phân loại Mô-típ, trang Gợi ý mô-típ (gom theo cụm mở đầu, người sáng lập đặt tên) (T-092)
│   ├── inc/doc-tiep.php     "Đọc tiếp" cuối bài: 3–5 bài gần gũi — mô-típ, chủ đề, mục từ chung, vùng, thể loại (T-122, T-092)
│   ├── inc/gop-bai.php      gộp hai bài đã đăng bị trùng; quét lại bài đã đăng mỗi đêm (T-101)
│   ├── inc/da-luu.php       nút ☆ Lưu, trang "Đã lưu" (/da-luu/) — danh sách nằm trên máy người đọc (T-051)
│   ├── inc/do-luong.php     đo 3 sự kiện không cookie, ô "người đọc cần gì" trên Bảng tin (T-052)
│   ├── inc/dong-dao.php     trò chơi, khối "Cách chơi" (lấy từ Wikipedia có nguồn), nút "Đọc cùng bé" (T-103)
│   ├── inc/ban-do.php       trang "Bản đồ ca dao" (/ban-do/): điểm địa danh đã xác nhận (T-104)
│   ├── inc/khoi-tho.php     lời dán vào khối Đoạn văn → đề xuất đưa vào khối Thơ, trang "Lời chưa vào khối Thơ" (T-112)
│   ├── inc/ban-bien-tap.php trang "Bàn biên tập": mọi hàng chờ, số đếm, nút Mở; ô "việc chờ bạn" trên Bảng tin (T-047)
│   ├── inc/sua-muc-tu.php   trang Sửa mục từ: nguồn của lời giải, ảnh riêng / ẩn ảnh Wikidata (T-113)
│   ├── inc/chinh-sach-du-lieu.php  trang "Chính sách thu thập dữ liệu"; dọn dòng Nguồn dưới chú thích chép (D-068, T-114)
│   ├── inc/so-lieu.php      dòng 👁 đọc · ↗ chia sẻ · ❝ trích dẫn dưới tiêu đề bài, 👁 trong danh sách (D-069, T-115)
│   ├── inc/tuc-ngu-thanh-ngu.php  một mục "Tục ngữ – Thành ngữ": gộp thể loại khi nâng cấp, chuyển hướng /thanh-ngu/… (T-128, D-079)
│   ├── inc/site-cu.php      chuyển bài công khai của site Astro cũ, qua bước chống trùng (T-076)
│   ├── inc/the-tho.php      nhận thể thơ theo số âm tiết, phân loại "Thể thơ" để lọc (T-084)
│   ├── inc/tu-dien.php      từ điển: mục từ theo chú thích, trang /tu-dien/ (T-044)
│   ├── inc/wikidata.php     hàng chờ "Xác nhận Wikidata", ảnh Commons giấy phép mở, bản đồ nhỏ (T-086..T-088)
│   ├── inc/goi-y.php        gợi ý vùng, chủ đề; bộ chủ đề của dự án; bảng ánh xạ chủ đề của nguồn (T-089, T-091)
│   ├── inc/tim.php          tìm kiếm: chỉ mục bỏ dấu tự làm, trang /tim/, REST (T-041, D-067)
│   ├── inc/trang-chu.php    trang chủ, "Bạn có biết?", tình huống, trang thể loại (T-043, T-093, T-094)
│   ├── inc/nguoi-doc.php    góp ý, cảm nghĩ (bình luận), chống rác, trang "Chống rác" (Turnstile) (T-045)
│   ├── inc/phan-tich.php    bài cảm nhận, phân tích của người đọc; trang "Viết cảm nhận" (T-097)
│   ├── inc/trang-du-an.php  trang Phương pháp, Nguồn (tự sinh), Về dự án ở dạng bản nháp; liên kết chân trang (T-046)
│   ├── du-lieu/tinh-huong.json tình huống và lịch dịp của site cũ (sinh bằng `npm run xuat:site-cu`)
│   ├── assets/tim.js        kết quả hiện khi gõ trên trang Tìm
│   ├── assets/ban-do-vn.svg bản đồ nhỏ Việt Nam có Hoàng Sa, Trường Sa (sinh bằng `scripts/sinh-ban-do.mjs`)
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
- **Trang chủ (T-043):** mã ngắn `[cadao_trang_chu]` trong `front-page.html`: câu định vị, ô tìm lớn, Câu hôm nay, Bạn có biết?, Hôm nay bạn đang… (tình huống đủ 2 bài), Đọc nhiều tuần này (`[cadao_doc_nhieu]`, khi có từ 3 bài được đọc trong 7 ngày), Vừa bổ sung, theo thể loại, theo vùng. **Trang thể loại (T-094):** giới thiệu (mô tả của thể loại), bài tiêu biểu, chủ đề, vùng, rồi danh sách. **Tình huống:** `/tinh-huong/<tên>/` (mẫu `taxonomy-tinh_huong.html`), trường `hop_khi`.
- **Đọc dễ hơn (T-082..T-084):** nhãn thể thơ cạnh nhãn thể loại; lục bát, song thất lục bát thụt câu lục; từ có chú thích trong lời chính là liên kết gạch chân chấm (chạm mở ô chú thích, tắt JavaScript thì nhảy tới mục chú thích); dị bản có nhãn "Dị bản N", chữ khác lời chính tô nền và gạch chân. Tất cả làm lúc dựng trang (`functions.php`), nội dung bài không đổi.
- **Người đọc góp phần (T-045, T-097):** cuối bài: "Cảm nhận, phân tích (N)", "Góp ý cho bài này" (4 nút, một form), "Cảm nghĩ của bạn" (bình luận luôn chờ duyệt, không email, không cookie). Mọi thứ vào Chờ duyệt. Chống rác: ô bẫy, 5 lần / giờ, Turnstile khi có khóa.
- **Tìm (T-041):** trang `/tim/` (thanh đầu trang có "Tìm"): ô tìm, kết quả hiện khi gõ, gõ không dấu vẫn ra, tìm cả trong chú thích; nhóm "Từ điển" khi khớp tên mục từ (D-067).
- **Vùng, chủ đề (T-089, T-091):** trang **Gợi ý vùng, chủ đề** (bảng ánh xạ chủ đề của nguồn → chủ đề của dự án; danh sách bài với gợi ý và lý do); cuối bài có "Chủ đề: …", "Vùng: …" (chỉ những gì đã duyệt).
- **Từ điển, địa danh (T-044, T-086..T-088):** trang `/tu-dien/` và trang mục từ (theme dùng `taxonomy.html`; plugin thêm nguồn, ảnh, bản đồ vào mô tả); ô chạm từ có ảnh, bản đồ nhỏ, "Xem trong từ điển"; khối Đáp án có ảnh. Chỉ thực thể Wikidata người sáng lập đã xác nhận mới hiện.
- **Công cụ đọc (T-085), bài dài (T-090):** "Aa", "◐", "Trình chiếu", "▶ Nghe" (chỉ khi máy có giọng tiếng Việt; "Giọng đọc máy") cạnh Trích dẫn · Chia sẻ; cỡ chữ và sáng / tối nhớ trên máy, áp trong `<head>` (thuộc tính `data-co-chu`, `data-che-do` trên `<html>`). Bài từ 41 dòng: tóm tắt, mục lục, đoạn, số dòng bên lề, "Đọc toàn bài", "Đọc tiếp từ đoạn N".
- **Trích dẫn, chia sẻ (T-042):** hộp `<details>` "Trích dẫn" — kiểu thông dụng và APA 7 (`20` mục 6), nút "Sao chép kèm nguồn" / "Chỉ sao chép lời", ô "Kèm đáp án / chú thích" khi bài có phần chép từ nguồn; nút "Chia sẻ" (Web Share, không có thì sao chép liên kết). Ngày truy cập theo ngày bấm.
- **Tục ngữ – Thành ngữ (D-079, T-128 — plugin 0.31.0):** một thể loại "Tục ngữ – Thành ngữ" ở `/tuc-ngu/`; câu nhóm "thành ngữ – tục ngữ" của nguồn thành bài trong mục này. Bản trước (T-080) có trang tách hai loại với máy gợi ý; đã bỏ vì ranh giới mờ, máy dễ xếp sai. Khi nâng cấp, bài "Thành ngữ" chuyển sang mục gộp; `/thanh-ngu/<tên>/`, `/the-loai/thanh-ngu/` chuyển hướng 301.
- **Số liệu của bài (T-115, D-069):** dưới tiêu đề: 👁 lượt đọc (≥ 10) · ↗ lượt chia sẻ (≥ 3) · ❝ lượt trích dẫn (≥ 3), số rút gọn 1,2K; danh sách bài chỉ 👁. Chia sẻ = sự kiện `chia_se` (gửi xong qua hộp chia sẻ, hoặc sao chép liên kết).
- **Sửa mục từ (T-113):** Từ điển → Sửa mục từ có ô **Nguồn của lời giải** (xóa khi tự viết lại) và **Ảnh** (chọn từ Thư viện ảnh kèm ghi công, hoặc ẩn ảnh Wikidata). Trường `_cadao_anh_rieng`, `_cadao_anh_ghi_cong`, `_cadao_an_anh`.
- **Bàn biên tập (T-047):** mục đầu của menu Ca dao – Tục ngữ: mọi hàng chờ theo thứ tự nên làm (báo lỗi của người đọc trước), số đếm, "làm gì", nút Mở; ô "việc chờ bạn" ở đầu Bảng tin.
- **Lời vào khối Thơ (T-112):** mọi tính năng của trang bài đọc lời từ khối Thơ. Bài có lời trong khối Đoạn văn: trình soạn báo và có nút "Đưa lời vào khối Thơ"; trang **Lời chưa vào khối Thơ** liệt kê bài cũ, xem trước, bấm Chuyển. Không đổi chữ; dòng chú (in nghiêng / trong ngoặc) giữ là đoạn văn; có bản sửa đổi để hoàn tác.
- **Đồng dao (T-103):** bài gắn trò chơi có khối "Cách chơi" (thông tin, các bước, dòng Nguồn khi chép từ Wikipedia); nút "Đọc cùng bé" (một dòng, chữ rất to, vỗ tay tô chữ, không âm thanh — `assets/dong-dao.js`, chỉ tải ở bài đồng dao). Trang sửa trò chơi có nút lấy mục Cách chơi từ Wikipedia tiếng Việt.
- **Bản đồ ca dao (T-104):** `/ban-do/`, mã ngắn `[cadao_ban_do]`: SVG tự lưu + điểm là liên kết tới danh sách địa danh từ Bắc vào Nam; không JavaScript, không dịch vụ ngoài.
- **Đã lưu (T-051):** nút **☆ Lưu** trong hàng nút dưới lời nhớ bài vào bộ nhớ trình duyệt (không gửi về máy chủ); trang `/da-luu/` liệt kê, bỏ lưu từng bài.
- **Đo lường (T-052, D-047):** `POST /wp-json/cadao/v1/su-kien` — đúng 3 sự kiện `tim_khong_ket_qua`, `trich_dan_sao_chep`, `chu_giai_mo`, cộng số theo ngày; không cookie, không IP; máy dò, người biên soạn đăng nhập không tính. Ô Bảng tin "người đọc cần gì (30 ngày)"; cách đọc: `docs/huong-dan/doc-so-lieu.md`.
- **Lượt đọc (T-081, D-064):** trang bài gửi `POST /wp-json/cadao/v1/luot-doc/<id>` khi đã hiện 5 giây trên màn hình, mỗi máy một lần mỗi ngày (đánh dấu trong `localStorage`, không cookie); người đang đăng nhập có quyền sửa bài không tính; máy dò tự khai tên (Googlebot…, trình duyệt không giao diện) không tính. "N lượt đọc" hiện khi từ 10 lượt. Ô "lượt đọc 7 ngày" trên Bảng tin: tổng theo ngày, bài đọc nhiều, người đọc đến từ đâu (chỉ tên miền).
- Kiểm: `npm run quet:wordpress` (axe WCAG 2.2 A/AA, 360 px sáng/tối và máy tính, không tràn ngang; thử trên trình duyệt: "Sao chép kèm nguồn" ra đúng chữ, lượt đọc gửi một lần sau 5 giây, tải lại không gửi thêm, tắt JavaScript không gửi, không cookie; ☆ Lưu → trang Đã lưu → Bỏ lưu; sự kiện sao chép, mở chú giải, tìm không ra gửi đúng một lần; Đọc cùng bé: dòng, chạm, vỗ tay, Esc, quét trợ năng khi mở; bản đồ: điểm ≥ 24 px, chạm tới đúng địa danh, không tải gì từ nơi khác) — CI chạy ở job "wordpress".

Giữ ít plugin bên thứ ba (mỗi plugin là một nguy cơ bảo mật). Dự kiến chỉ thêm: chống spam form, SEO (T-050). Sao lưu do FlashPanel làm.

## 3. Mô hình dữ liệu

| WordPress | Nội dung |
|---|---|
| Loại bài `cdtn` ("Ca dao – Tục ngữ") | Mỗi câu / bài một mục; đường dẫn `/ca-dao/<ten>/`, `/tuc-ngu/<ten>/`… theo thể loại đầu tiên (chưa chọn thì `ca-dao`); đường dẫn gốc `/bai/<ten>/` vẫn dẫn tới bài. Quy tắc đường dẫn là `add_rewrite_rule` đứng đầu — không dùng `add_rewrite_tag` trong slug (WordPress sinh quy tắc tệp đính kèm sai, nuốt mọi `/ca-dao/…`) |
| Tiêu đề | Thường là dòng đầu |
| Nội dung | **Khối Thơ** (core/verse) chứa lời; phần "Dị bản" (khối Thơ); phần "Chú thích" (danh sách, mỗi mục ghi "Theo …" khi chép từ nguồn) |
| Đoạn trích (excerpt) | "Hiểu nhanh" — một câu giải nghĩa (tùy chọn) |
| Phân loại `the_loai` | Ca dao · Tục ngữ · Thành ngữ · Đồng dao · Vè · Câu đố |
| Phân loại `chu_de`, `vung` | Chủ đề (bộ của dự án, có nhóm Kinh nghiệm, Lối hát, Loại câu đố), vùng (7 vùng của từ vựng dự án); máy gợi ý, người sáng lập duyệt (T-089, T-091). Trường `_cadao_goi_y` (gợi ý chờ duyệt), `_cadao_goi_y_xong`; tùy chọn `cadao_anh_xa_chu_de` (bảng ánh xạ đã duyệt) |
| Trường `nguon_ten`, `nguon_url`, `ngay_truy_cap` | Hộp Nguồn của bài nhập tay (tùy chọn) → dòng "Nguồn: …" cuối bài |
| Trường `_cadao_url_goc`, `_cadao_ngay_lay` | Trang gốc và ngày lấy của bài tự động — nội bộ, không hiện ra trang |
| Trường `_cadao_ma_goc`, `_cadao_nguon_id` | Mã bài trên nguồn và nguồn tự động đã lấy — để không lấy trùng |
| Cột kho `gop_vao`, `gop_lam`; trạng thái kho `da_gop` | Câu đã gắn vào bài / câu khác (`bai:<id>` hoặc `kho:<id>`): `trung` = cùng lời (bổ sung dị bản, chú thích của nguồn đó), `di_ban` = lời thành một dị bản. "Tách ra" trả lại thành câu mới |
| Bảng `wp_cadao_van_tay`, `wp_cadao_cap_am` | Chỉ mục chống trùng: dấu vân tay (1), (2) của lời chính và từng dị bản; cặp âm tiết liền nhau của lời chính (từ 6 âm tiết) |
| Bảng `wp_cadao_trung` | Hàng "Có thể trùng" và quyết định của người sáng lập (chờ, để sau, là bài khác, đã gộp) |
| Trường `_cadao_di_ban_them` | Dị bản thêm bằng tay hoặc qua "Thêm làm dị bản" (giữ khi bài được làm lại từ kho) |
| Phân loại `tu_kho` ("Từ điển", `/tu-dien/<từ>/`) | Mục từ = một chú thích (cùng từ, cùng lời giải); mô tả = lời giải; trường `_cadao_khoa`, `_cadao_nguon_ten`, `_cadao_nguon_url` (dòng Nguồn của chú thích), `_cadao_wd` (Wikidata đã xác nhận) |
| Bảng `wp_cadao_xac_nhan` | Hàng chờ "Xác nhận Wikidata": `tu:<mục từ>` hoặc `dap:<đáp án>`, mã thực thể, tọa độ, ảnh, bài Wikipedia; chờ / đúng / sai / bỏ qua |
| Phân loại `tro_choi` ("Trò chơi", `/tro-choi/<tên>/`) | Trò chơi dân gian của bài đồng dao; trường `_cadao_cc_so_nguoi`, `_cadao_cc_do_tuoi`, `_cadao_cc_do_dung`, `_cadao_cc_buoc`, `_cadao_cc_nguon_ten`, `_cadao_cc_nguon_url`, `_cadao_cc_giay_phep`, `_cadao_cc_wiki` |
| Phân loại `the_tho` | Thể thơ máy nhận (T-084) |
| Bảng `wp_cadao_tim` | Chỉ mục tìm (bài đang đăng): tiêu đề + lời, Hiểu nhanh + chú thích; mỗi phần có dấu (chuẩn hóa) và không dấu |
| Loại bài `gop_y` (nội bộ) | Góp ý của người đọc, luôn Chờ duyệt: loại, bài, nơi nghe, người kể, tên hiển thị |
| Loại bài `phan_tich` (`/phan-tich/<tên>/`) | Bài cảm nhận, phân tích: bài ca dao (`_cadao_bai`), bút danh, vai trò, "của dự án" |
| Tùy chọn `cadao_turnstile_site`, `cadao_turnstile_bi_mat` | Khóa Turnstile người sáng lập dán (không ghi vào repo) |
| Bảng `wp_cadao_su_kien` | Đo lường: (ngày, sự kiện, khóa) → số lần. Khóa: chuỗi tìm không ra / `bài:kiểu` / mã mục từ. Không IP, không định danh |
| Bảng `wp_cadao_luot_doc` | Lượt đọc: (bài, ngày) → số lượt. Không lưu IP, không lưu ai đọc |
| Bảng `wp_cadao_gioi_thieu` | (ngày, tên miền trang giới thiệu) → số lượt; vào thẳng ghi "(vào thẳng)" |

## 4. Nguồn tự động

Trang **Ca dao – Tục ngữ → Nguồn tự động**. Mỗi nguồn: tên · kiểu bộ đọc · địa chỉ · loại lấy · bật/tắt · **số bài Chờ duyệt mỗi ngày** · tên ghi nguồn · **chép chú thích, lời giải** (bật/tắt, D-055) · **ghi nguồn cho chú thích** (bật/tắt, D-057) · bỏ qua bài gắn cờ (thô tục, trào phúng hiện đại — D-060: không lọc từ tục, ô "Thô tục" sẽ bỏ ở T-108; hiện để trống) · nút **Lấy ngay**.

Kiểu bộ đọc: `cadao_me` (T-073) — (1) tên chủ đề, (2) ca dao qua API, (3) thành ngữ–tục ngữ, đồng dao, vè, câu đố qua sitemap, (4) đọc trang HTML: dị bản, chú thích, câu hỏi và đáp án câu đố, lời của các loại ngoài ca dao. Mỗi trang có bài chính và ~20 bài liên quan; bài liên quan chỉ nhận khi là ca dao và lời trùng từng ký tự với lời API (bài dài chỉ hiện đoạn trích). Mục đã thành bài được đọc trước; còn lại xen kẽ các loại. **Chỉ tạo bài Chờ duyệt từ mục đã đọc trang** (bài có sẵn dị bản, chú thích). Mỗi lượt tạo bài **xoay vòng theo loại** (mỗi loại một bài rồi sang loại kế; trong thành ngữ – tục ngữ, câu đã quyết loại đi trước — T-125, plugin 0.29.0), để loại nhập kho sau không phải chờ hết ca dao. **Bài tạo từ kho mà chưa ai sửa tay** (nội dung còn đúng như máy tạo, nhận ra bằng dấu băm `_cadao_bam`) tự cập nhật khi kho có dị bản, chú thích mới; bài đã sửa tay giữ nguyên. Kiểm mẫu: 23/23 bài (đủ 5 loại) khớp từng ký tự với trang gốc; bộ trích trang khớp bộ trích JavaScript của kho thô trên 53 bài, 120 chú thích. Sau đó: `wordpress_api` (site WordPress bất kỳ). Site có cấu trúc riêng cần một bộ đọc mới — việc của agent; trang cấu hình chỉ chọn và chỉnh bộ đọc đã có.

**Lịch sự với máy chủ nguồn** (như `21` mục 16.2): tuần tự, ≥ 3 giây giữa hai yêu cầu API và ≥ 5 giây giữa hai trang HTML, User-Agent ghi tên dự án, gặp 429/5xx thì dừng tới lượt sau, lưu điểm dừng. Lịch: mỗi 5 phút một lượt — tối đa ~20 giây khi chạy trong yêu cầu web (WP-Cron, nút "Lấy ngay"), 120 giây khi chạy bằng cron thật của máy chủ (~20 trang mỗi lượt); tạo bài Chờ duyệt mỗi ngày một lần. Cuối lượt chỉ ghi điểm dừng và nhật ký lên cấu hình mới nhất (không đè thay đổi người quản trị vừa lưu).

**Chống trùng giữa các nguồn** (D-065, `25` mục 4.9; T-109, T-110 — plugin 0.5.0): `_cadao_ma_goc` chỉ chống lấy lại cùng một bài của cùng nguồn. Mỗi câu đọc xong trang gốc được so cả lời với bài đã có và các câu khác trong kho: **lời giống hệt** (kể cả chỉ khác dấu câu, chữ hoa, cách bỏ dấu) **và cùng thể loại** → tự gắn vào bài / câu có sẵn (Kho thu thập → tab "Đã gộp", bấm "Tách ra" nếu sai); bài đích chưa sửa tay được làm lại, có thêm dị bản, chú thích của nguồn kia và dòng "Nguồn" riêng. **Lời trùng đúng một dị bản đã ghi** (mọi thể loại, D-074 — plugin 0.29.0) → tự gắn **làm dị bản** của bài / câu đó ("Là dị bản của: …", "Tách ra" được); các cặp loại này đang chờ được so lại một lần sau khi nâng cấp. Mọi trường hợp khác — khác thể loại hay khác vài chữ (≥ 90%) với lời chính, có thể là dị bản (50–90%) — vào trang **Có thể trùng** (menu Ca dao – Tục ngữ, có số đếm; Bảng tin cũng báo) với "Gộp: là cùng một bài" / "Gộp làm dị bản" / "Là bài khác" / "Để sau"; máy tạo bài hằng ngày bỏ qua câu đang chờ. Khi nâng cấp, plugin làm dần chỉ mục cho bài và kho đã có rồi so từng câu (mỗi lượt 5 phút) — dọn luôn trùng sẵn có. `wp cadao trung-thong-ke` chạy lại phép đo 25/10/2026 (chỉ đọc).

**Khi viết tay (T-111):** trình soạn có ô **Có thể trùng** ở cột bên (tab Bài) và trong bước xác nhận khi bấm Đăng: sau khi dán / gõ lời (ngừng gõ 1,2 giây) máy liệt kê bài có lời giống kèm mức giống, nút **Mở bài này**, **Thêm làm dị bản** (lời vừa gõ thành dị bản của bài cũ, đúng từng ký tự; bản nháp vào thùng rác), **Vẫn đăng bài mới** (máy ghi là bài khác, không báo nữa). Không chặn đăng. Bài trùng bài khác có nhãn "Có thể trùng" trong danh sách bài.

**Gộp hai bài đã đăng (T-101 — plugin 0.15.0):** cặp bài–bài trong trang Có thể trùng có nút **Gộp: giữ "<tên bài>"** cho từng bên. Bài phụ thành dị bản của bài chính; chú thích tự viết, Hiểu nhanh, nguồn đã ghi được giữ nếu bài chính chưa có; chủ đề, vùng, tình huống gộp lại; lượt đọc cộng dồn; cảm nghĩ, bài phân tích chuyển sang bài chính; đường dẫn cũ chuyển hướng 301 (`_wp_old_slug`); bài phụ vào thùng rác. Mỗi đêm máy so lại các bài đã đăng.

## 5. Những điều vẫn giữ

- **Lời chép nguyên văn bằng máy, không qua AI**; AI không viết lời, dị bản, nguồn.
- **Lời không ghi nguồn** (D-057); không tự điền nguồn cho bài nhập tay.
- **Chú thích, lời giải** chỉ chép khi công tắc của nguồn đang bật (D-055); dòng "Nguồn: … ↗" dưới đó trỏ đúng trang đã lấy, tắt được bằng công tắc "Ghi nguồn cho chú thích" (D-057). **Từ D-068 (26/10/2026):** "Ghi nguồn cho chú thích" mặc định tắt; bài, mục từ đã đăng được dọn dần (mỗi lượt lấy bài 5 phút, tối đa 25 giây); danh sách chú thích chép mang lớp `cadao-chep` (mục từ chép giữ `noindex`, trường `_cadao_chep`); nguồn ghi nhận ở trang `/chinh-sach-du-lieu/` (mã ngắn `[cadao_nguon]`). **Từ D-078:** cuối bài hiện "[N nguồn tham khảo đã ẩn]" (`cadao_dong_nguon_an`, `inc/loai-bai.php`); liên kết về trang gốc chỉ xuất ra HTML cho người sửa được bài. **Từ D-081:** bài có dòng "Nguồn: …" (hộp người biên soạn điền) thì không in dòng "[N … đã ẩn]"; dòng liên kết cho người sửa được bài vẫn in.
- **Không tải ảnh** của nguồn; không chép cách phân loại, bộ thẻ ra trang.
- **Google:** bài chưa có "Hiểu nhanh" (đoạn trích do dự án viết) mang `noindex` — lời và chú thích chép từ nguồn không tính là giá trị riêng (chính sách spam của Google về nội dung sao chép). Người đọc vẫn xem được. Từ D-062: bài có ít nhất một bài phân tích của người đọc đã duyệt cũng được tính là có giá trị riêng (T-097).
- Không thông tin cá nhân trong nội dung.

## 6. Phát triển và kiểm thử tại máy

WordPress Playground (PHP 8.5 — như máy chủ — chạy bằng WebAssembly, cơ sở dữ liệu SQLite, có `intl`) — không cần cài PHP/MySQL. Cần Node ≥ 24.18.

```bash
npm run test:wordpress            # kiểm thử plugin (CI chạy ở job "wordpress"); thêm -- --mang để thử bộ đọc trên cadao.me thật
npm run test:anh                  # vẽ ảnh chia sẻ bằng PHP thật có GD + FreeType (Playground không vẽ được chữ); CADAO_PHP=<đường dẫn php>
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

**phongdao.com** (D-070; trước 26/10/2026 là cadao.sdigi.net, nay chuyển hướng 301) — VPS của người sáng lập (Ubuntu 24.04), quản lý bằng **FlashPanel** (gói miễn phí), web server Apache + PHP-FPM, MariaDB 10.11. Tên miền qua Cloudflare (proxy bật, chứng chỉ Let's Encrypt do FlashPanel cấp và gia hạn). VPS còn chạy **các site khác của người sáng lập**: chỉ đụng tới site cadao.

| Thành phần | Cách làm |
|---|---|
| Site, PHP 8.5, SSL, cài WordPress | Người sáng lập bấm trong FlashPanel |
| **Cách ly** | Gói miễn phí không có ô "Website Isolation" khi tạo site → *Cài Đặt → Update Owner* `cadao`: FlashPanel tạo tài khoản Linux riêng, pool PHP riêng, thêm vào nhóm `isolated` (ACL chặn vào `/home` của site khác) |
| Bổ sung cách ly | `cai-dat.sh` gỡ `cadao` khỏi nhóm `www-data` (nhóm đó gửi lệnh được tới pool PHP chung của các site khác), `wp-config.php` quyền 600 |
| Tiếng Việt, múi giờ, đường dẫn, plugin, cron | `wordpress/may-chu/cai-dat.sh` (chạy bằng root, chạy lại được) |
| **Tự cập nhật sau khi gộp PR** (T-078) | Cron mỗi 5 phút chạy `tu-cap-nhat.sh` bằng tài khoản `cadao`: thấy `main` có commit mới thì lấy `wordpress/plugins/ca-dao` và `wordpress/themes/ca-dao` của đúng commit đó (theme bật một lần; người quản trị đổi theme sau đó thì giữ), kiểm cú pháp PHP, thay thư mục một lần; trang chủ không trả 200 thì trả lại bản cũ và không thử lại commit đó. Nhật ký `/home/cadao/cap-nhat.log`; các bản đã lên site ghi ở `/home/cadao/.cap-nhat-lich-su` (50 bản gần nhất). Chạy tay: `cd /home/cadao && sudo -u cadao bash bin/cap-nhat-plugin.sh`. **Quay lại bản trước** (T-118): nút Revert của PR trên GitHub, hoặc `NHANH=<mã commit>` khi chạy tay — `docs/huong-dan/quay-lai-ban-truoc.md` |
| Lịch chạy | `DISABLE_WP_CRON`; `/etc/cron.d/cadao-wordpress` chạy `wp cron event run --due-now` mỗi phút |
| WP-CLI | `sudo -u cadao /home/cadao/bin/wp <lệnh>` |
| Chặn công cụ tìm kiếm | `blog_public = 0` tới khi mở công khai (H5, T-053) — ô "Discourage search engines" của FlashPanel đã không có tác dụng, script đặt lại |
| Sao lưu | Người sáng lập: FlashPanel → *Sao Lưu*, hằng ngày, gồm site và cơ sở dữ liệu cadao, lưu ra ngoài VPS (Google Drive…) |

Agent vào VPS bằng khóa SSH riêng trên máy người sáng lập (không dùng mật khẩu). Không ghi IP, tài khoản hay thông tin các site khác vào repo.
