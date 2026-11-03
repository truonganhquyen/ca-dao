# 29 · Chuyển dữ liệu và chuyển đổi sang V2

| | |
|---|---|
| Phiên bản | 1.1 · 02/11/2026 (D-083, D-089, D-090; đích dữ liệu theo D-094) |
| Trạng thái | **Đã duyệt.** Đổi phạm vi dữ liệu mang sang phải ghi D-xxx |
| Đọc khi | T-145 (công cụ nhập), T-157 (ngày chuyển), T-164 (dọn dữ liệu cũ) |
| Đọc kèm | `26` mục 4 (mô hình V2: phần của bài trong post meta, bảng việc), AGENTS.md (KHÔNG BAO GIỜ 5, 14, 15; Human Gate H2, H5, H6) |

## 1. Mục tiêu và nguyên tắc

Sau khi chuyển, site chạy hoàn toàn bằng V2, **không mất một quyết định nào của người biên soạn**, rồi mở cho Google.

1. **Chỉ mang dữ liệu người làm** (D-089). Phần máy lấy từ nguồn (lời, dị bản, chú thích, câu hỏi, đáp án) nhập lại từ kho có cấu trúc.
2. **Không đoán.** Bài nào không tách được → hàng "Cần xem" kèm lý do, không bịa, không bỏ qua im lặng.
3. **Không xoá dữ liệu cũ khi chuyển.** Meta, bảng, nội dung MVP nằm nguyên trong cơ sở dữ liệu tới khi người sáng lập cho dọn (≥ 2 tuần, T-164, H6).
4. **Hoàn tác được** bằng lệnh, ngoài bản sao lưu hằng ngày (D-090).
5. **Chạy thử trước, chạy thật sau**, mỗi lần chạm site thật có đồng ý của người sáng lập cho lần đó (AGENTS.md, KHÔNG BAO GIỜ 15).

## 2. Kiểm kê dữ liệu người làm

Việc đầu tiên của T-145: agent đọc toàn bộ mã MVP (`wordpress/plugins/ca-dao/inc/`, theme) và **đối chiếu bảng dưới với mọi khoá meta, term meta, tuỳ chọn, bảng, loại bài đang có**. Thứ gì chưa có trong bảng phải được thêm vào (mang hoặc ghi rõ vì sao không mang) trước khi viết công cụ nhập; lệnh `wp phongdao kiem-ke` in số lượng từng dòng trên site thật.

| Dữ liệu | Chỗ lưu ở MVP | Đích ở V2 |
|---|---|---|
| Tác phẩm đã đăng, nháp người viết (ID, tên, đường dẫn, ngày, người đăng, trạng thái) | bài `cdtn` (`publish`, `draft` do người tạo) | `tac_pham`, **giữ ID** (bình luận, lượt đọc, phân tích vẫn nối đúng) |
| Lời, dị bản, chú thích, câu hỏi, đáp án của bài chưa sửa tay | kho (`wp_cadao_kho.loi`, `du_lieu`) + `post_content` | phần `loi`, `di_ban`, `chu_thich`, `dap_an` (`may_chep`, hồ sơ Ca dao Mẹ) — **nhập từ kho** |
| Lời, dị bản đã sửa tay | `post_content` lệch `_cadao_bam` | phần `loi`, `di_ban` người làm `nguoi_viet`, tách từ HTML; không tách được → "Cần xem" |
| Dị bản, chú thích thêm tay | `_cadao_di_ban_them`, `_cadao_chu_thich_them` | phần `di_ban`, `chu_thich` (`nguoi_viet`) |
| Câu đã gộp vào bài | kho `gop_vao`, `gop_lam` | dị bản / chú thích của tác phẩm; quyết định gộp giữ trong kho |
| Hiểu nhanh (V2 gọi Ý nghĩa) | `post_excerpt` + `_cadao_ho_tro_ai` | phần `y_nghia` (`ai_da_duyet` khi có dấu, ngược lại `nguoi_viet`) |
| Nháp Hiểu nhanh của máy | `_cadao_nhap_hieu_nhanh` | việc chờ "Nháp ý nghĩa" trong `wp_cadao_viec` (không vào bài) |
| Nguồn của chú thích do người điền | `nguon_ten`, `nguon_url`, `ngay_truy_cap` | hồ sơ nguồn (mỗi nguồn khác nhau một hồ sơ, loại bài ẩn `ho_so_nguon`) + `nguon` của mục chú thích người viết |
| Lời đã đối chiếu với | `doi_chieu_ten`, `doi_chieu_trang`, `doi_chieu_url` | hồ sơ nguồn loại sách + `doi_chieu` của phần `loi` |
| Địa chỉ gốc máy đã lấy | `_cadao_url_goc`, `_cadao_ngay_lay`, `_cadao_nguon_id`, `_cadao_ma_goc` | phần nội bộ `nguon_may` (không ra HTML người đọc) |
| "Hợp khi…" + tình huống | `hop_khi`, phân loại `tinh_huong` | tuyển tập loại `tinh_huong`, ghi chú của mục (meta `_phongdao_muc`) |
| Thể loại, chủ đề, vùng đã gán | phân loại `the_loai`, `chu_de`, `vung` | giữ (vùng = vùng lưu truyền) |
| Mô-típ (tên, mô tả, bài) | phân loại `mo_tip`, `_cadao_mo_tip_dau`, tuỳ chọn `cadao_mo_tip_bo*` | `mo_tip` |
| Thể thơ | phân loại `the_tho` | `hinh_thuc` (máy nhận lại; lối hát người đã gán giữ) |
| Mục từ (lời giải, nguồn lời giải, loại, Wikidata đã xác nhận, ảnh riêng, ẩn ảnh, ghi công) | phân loại `tu_kho` + term meta (`_cadao_nguon_*`, `_cadao_wd`, `_cadao_wd_xet`, `_cadao_anh_*`, `_cadao_an_anh`), bảng `wp_cadao_xac_nhan` | `muc_tu` + term meta; quyết định Đúng / Sai thành việc Wikidata đã quyết trong `wp_cadao_viec` |
| Trò chơi | phân loại `tro_choi` + term meta `_cadao_cc_*` | `tro_choi` + term meta |
| Bạn có biết đã duyệt | tuỳ chọn `cadao_ban_co_biet` | phần `ban_co_biet` — người làm `nguoi_viet`, không dấu AI (máy ghép theo khuôn từ lời giải có sẵn, không dùng AI; người sáng lập đã duyệt); mỗi bài một câu, câu thứ hai trở đi giữ ở dữ liệu MVP và ghi trong báo cáo |
| Giới thiệu thể loại, mô tả chủ đề / vùng | mô tả phân loại | giữ |
| Bộ chủ đề, bảng ánh xạ chủ đề nguồn → dự án | tuỳ chọn `cadao_bo_chu_de`, `cadao_anh_xa_chu_de` | cài đặt V2 |
| Quyết định trùng ("Là bài khác", "Để sau"), cặp âm tiết | `wp_cadao_trung`, `wp_cadao_van_tay`, `wp_cadao_cap_am` | giữ bảng |
| Gộp tác phẩm đã đăng (đường dẫn cũ) | `_wp_old_slug`, `_cadao_gop_vao` | giữ + bảng chuyển hướng |
| Cấu hình nguồn tự động ("Chép chú thích", "Ghi nguồn cho chú thích", lịch) | tuỳ chọn `cadao_nguon_tu_dong` | hồ sơ nguồn + Nguồn tự động V2 |
| Góp ý, dị bản, số trang, báo lỗi của người đọc | bài `gop_y` + meta | giữ `gop_y` |
| Cảm nghĩ | bình luận | giữ (V2 nhận cảm nghĩ qua form riêng, không ghi địa chỉ mạng; cảm nghĩ gửi qua form bình luận MVP có địa chỉ mạng WordPress tự ghi — xoá các địa chỉ đó khi dọn dữ liệu cũ, cần người sáng lập đồng ý) |
| Bài phân tích, bút danh | bài `phan_tich`, `_cadao_but_danh` | giữ |
| Lượt đọc, trang giới thiệu, sự kiện | `wp_cadao_luot_doc`, `wp_cadao_gioi_thieu`, `wp_cadao_su_kien` | giữ bảng |
| Trang tĩnh (Phương pháp, Nguồn, Về dự án, Chính sách thu thập dữ liệu, …) | `page` | giữ; agent đề xuất sửa tên dự án, người sáng lập duyệt |
| Ảnh đã tải lên | thư viện ảnh | giữ |
| Người dùng, khoá Turnstile, cài đặt WordPress | bảng WordPress, tuỳ chọn `cadao_turnstile_*` | giữ |
| Mã bài cũ của site Astro, đường dẫn `/thanh-ngu/` | `_cadao_ma_site_cu`, tuỳ chọn `cadao_site_cu` | bảng chuyển hướng 301 |

**Không mang** (máy tạo lại hoặc không còn dùng): bài `cdtn` trạng thái Chờ duyệt do máy tạo (câu trong kho thành ứng viên); gợi ý chưa duyệt (`_cadao_goi_y`, máy gợi ý lại); dấu băm `_cadao_bam`; `loai_tach` trong kho; tuỳ chọn số phiên bản bảng cũ; chỉ mục tìm (dựng lại); ảnh chia sẻ (sinh lại); trang sinh bởi mã (`cadao_trang_*` trỏ trang — khai báo lại theo trang có sẵn).

## 3. Quy tắc nhập

1. **Bài có câu trong kho và chưa sửa tay** (`md5(post_content) = _cadao_bam`): dựng các phần từ hàng kho có `post_id` = bài, cộng các hàng `gop_vao` bài đó (dị bản, chú thích theo cách đã gộp ở MVP).
2. **Bài đã sửa tay hoặc không có câu trong kho** (nhập tay, 16 bài vè, 2 bài từ site Astro): tách từ HTML — khối Thơ đầu = lời; khối Thơ sau "Dị bản" = dị bản; danh sách `cadao-chu-thich` = chú thích (`cadao-chep` → `may_chep`, còn lại `nguoi_viet`); khối Chi tiết "Đáp án" = đáp án. Lời trong khối Đoạn văn (bài chưa chuyển sang khối Thơ) tách theo dòng.
   - **Vè nhiều phần** (tiêu đề phụ ngay trên mỗi khối Thơ, ví dụ "Vè cá (…)" bảy phần): các khối Thơ nối thành lời, mỗi tiêu đề thành một mục của phần `doan` (chỉ ở vè; thể loại khác → "Cần xem"). Trang MVP chỉ coi khối Thơ đầu là lời; trang V2 hiện đủ các phần, có tiêu đề, mục lục.
   - **Dòng chú đứng trước lời** (in nghiêng hoặc trong ngoặc, ví dụ "(Bài vè tả trận đánh ở …)") → `boi_canh.chu`, người viết; năm, nơi, sự kiện không điền.
   - **Dòng "(Nguồn: …)" trong nội dung bài** → "Cần xem" kèm đủ chữ; không tự gắn vào lời (D-057). Người sáng lập ghi ở "Lời đã đối chiếu với" nếu đúng.
   - **"Dị bản" đứng đầu bài, trên tiêu đề phụ đầu tiên** (nút thêm dị bản của MVP chèn nhầm chỗ ở bài nhiều phần): khối Thơ dưới "Dị bản" là dị bản (trùng ô `_cadao_di_ban_them`), các phần là lời; "Cần xem".
   - Ô Nguồn của bài không có đường dẫn → hồ sơ nguồn loại "chưa rõ" (không đoán là trang web).
3. **Kiểm "trước = sau"** với mọi bài: lời dựng từ phần `loi` khớp **từng ký tự** lời đang hiện trên trang MVP (`cadao_loi_tu_html_tho`), số dị bản, số chú thích, đáp án, Hiểu nhanh khớp. Không khớp → "Cần xem".
4. **"Cần xem"**: bài vẫn được nhập với những phần chắc chắn, giữ trạng thái đăng, thêm một thẻ "Cần xem" vào Hộp việc; báo cáo ghi mã bài, lý do, đường dẫn sửa. Người sáng lập sửa ngay trên trang bài khi rảnh.
5. **Đường dẫn:** giữ tên đường dẫn, trừ đuôi `-2`, `-3`… (người sáng lập cho phép, D-089) → tên mới thêm chữ phân biệt; thể loại, tình huống, thể thơ đổi tiền tố như `docs/27` mục 3. Mọi đường dẫn đổi ghi vào bảng chuyển hướng 301, kể cả các 301 đã có ở MVP.
6. **Trạng thái duyệt:** mỗi phần mang đúng trạng thái người sáng lập đã đặt trên MVP (Hiểu nhanh đã lên trang = đã duyệt, vào bài; dấu AI giữ nguyên; nháp máy thành việc chờ); không phần nào được nâng trạng thái khi nhập.
7. Mọi bước ghi nhật ký (bảng việc, người tạo `nhap_cu`); bảng tạm `wp_cadao_chuyen_doi` ghi giá trị cũ của mọi thứ bị đổi tại chỗ (loại bài, tên đường dẫn, phân loại) để hoàn tác.

## 4. Công cụ

| Lệnh | Việc | Ghi lên CSDL |
|---|---|---|
| `wp phongdao kiem-ke` | đếm từng dòng của bảng mục 2 | không |
| `wp phongdao nhap --thu` | chạy toàn bộ quy tắc trong bộ nhớ, in báo cáo: số bài theo cách nhập, số "Cần xem" theo lý do, kết quả "trước = sau", danh sách đường dẫn sẽ đổi | không |
| `wp phongdao nhap --lam` | nhập theo lô qua hàng đợi; dừng giữa chừng chạy lại được, kết quả như nhau | có |
| `wp phongdao nhap --hoan-tac` | đặt lại loại bài, tên đường dẫn, phân loại theo `wp_cadao_chuyen_doi`; xoá các khoá `_phongdao_*` và dữ liệu bảng việc do bước nhập tạo | có |
| `wp phongdao don-dep --thu / --lam` | (T-164) xoá meta, bảng, tuỳ chọn MVP đã mang sang | có |

Báo cáo không chép lời vào repo; chỉ mã bài, số lượng, lý do. Công cụ chạy được trong Playground trên bộ dữ liệu mẫu của kiểm thử MVP (bài mẫu, mọi ca: chưa sửa tay, sửa tay, gộp, vè trong khối Đoạn văn, câu đố, đồng dao có trò chơi, tục ngữ có Hiểu nhanh và dấu AI, tình huống, mục từ có Wikidata). Kiểm thử bắt buộc: nhập → hoàn tác → nhập cho cùng kết quả.

**Chạy thử trên site thật trước ngày chuyển:** bản V2 chưa có trên máy chủ, nên công cụ `--thu` và `kiem-ke` cũng có dạng tệp chạy một lần chỉ đọc (`wordpress/v2/cong-cu/thu-nhap.php`, `wp eval-file`), agent chạy trên máy chủ khi người sáng lập đồng ý cho lần đó.

**Lần 1 (03/11/2026, người sáng lập đồng ý):** 631 bài (611 đăng, 20 chờ duyệt máy tạo không mang); "trước = sau" không lệch bài nào (chỉ khác khoảng trắng: lời 9, dị bản 11, chú thích 16, ý nghĩa 3). Phát hiện: 7 bài vè nhiều phần không tìm được lời, 3 bài vè có dòng chú bối cảnh, 2 bài có dòng "(Nguồn: …)", 1 bài có "Dị bản" chèn trên tiêu đề phụ → thêm các quy tắc ở mục 3.2. Còn "Cần xem" đúng ý: nguồn bài không gắn chú thích nào (2), thể loại "thơ truyền tụng" không còn ở V2 (1), ca dao có dòng in đậm và đáp án (1).

## 5. Ngày chuyển (T-157)

| Bước | Ai | Việc | Thời gian |
|---|---|---|---|
| 0 | Agent | Ít nhất 2 ngày trước: chạy `kiem-ke` + `thu-nhap` trên site thật (được đồng ý), gửi báo cáo; sửa công cụ tới khi "trước = sau" đạt với mọi bài trừ "Cần xem" đã giải thích | — |
| 1 | Người sáng lập | Đọc báo cáo (số bài, số "Cần xem", đường dẫn đổi); chọn giờ chuyển; mở FlashPanel xác nhận có bản sao lưu đêm trước (D-090) | 15 phút |
| 2 | Agent | Gộp PR chuyển đổi (V2 vào `wordpress/plugins/ca-dao`, `wordpress/themes/ca-dao`, phiên bản 1.0.0). Máy chủ tự cập nhật trong ~5 phút. Khi chưa nhập xong, plugin V2 trả trang "Thư viện đang được nâng cấp" (503, `Retry-After`) cho khách chưa đăng nhập | — |
| 3 | Agent | `wp phongdao nhap --lam` (được đồng ý), theo dõi hàng đợi tới xong | 10–30 phút |
| 4 | Agent | `npm run kiem-site-that` (đúng 1.0.0), bộ kiểm chỉ đọc trên site thật (`e2e:v2` phần không ghi), so số liệu: số tác phẩm, ý nghĩa, lượt đọc, cảm nghĩ, phân tích, mục từ, trò chơi trước / sau; thử 20 đường dẫn cũ ra 301 đúng; mở vài trang bằng trình duyệt khách, console không có lỗi CSP (nếu có: Cloudflare đang chèn mã — tắt Rocket Loader / Web Analytics) | 15 phút |
| 5 | Người sáng lập | Trên điện thoại: trang chủ, 1 bài mỗi thể loại, một tuyển tập (tình huống cũ), tìm 3 câu, Từ điển, Hộp việc (quyết 3 thẻ), sửa một phần trên trang bài, thêm và đăng thử 1 bài | 20–30 phút |
| 6 | Người sáng lập | **Mở** (H5): Cài đặt → Đọc → bỏ chọn "Ngăn các công cụ tìm kiếm…" — ngay hoặc khi sẵn sàng | 1 phút |

**Nếu hỏng ở bước 3–5:** agent chạy `wp phongdao nhap --hoan-tac`, rồi gộp PR hoàn lại mã MVP; site về như trước chuyển. Sau khi mã MVP chạy lại: `wp option delete cadao_tim_phien_ban cadao_trung_phien_ban` để MVP tự dựng lại chỉ mục tìm và chỉ mục chống trùng (V2 đã ghi đè các dòng chỉ mục của bài cùng cấu trúc; các quyết định trùng không mất). Hỏng nặng hơn: khôi phục bản sao lưu đêm trước bằng FlashPanel (người sáng lập).

## 6. Sau khi chuyển

- **2 tuần quan sát:** agent theo dõi `kiem-site-that`, lỗi PHP (nếu được xem nhật ký), số liệu, các thẻ "Cần xem".
- **T-164 · Dọn dữ liệu cũ** (H6, người sáng lập đồng ý): `wp phongdao don-dep --thu` liệt kê thứ sẽ xoá; `--lam` sau khi đồng ý; gỡ module `NhapDuLieuCu` và bảng `wp_cadao_chuyen_doi`. Gồm cả thư mục ảnh chia sẻ của MVP `uploads/cadao-anh/` (V2 vẽ lại ở `uploads/phongdao-anh/`).
- `docs/20`, `docs/24` chuyển vào `docs/luu-tru/` trong PR chuyển đổi; `docs/26`–`29` thành tài liệu chính.

## 7. Rủi ro

| Rủi ro | Cách giảm |
|---|---|
| Sót một loại dữ liệu người làm | Kiểm kê từ mã trước khi viết công cụ; `kiem-ke` so số trước / sau |
| Lời bị đổi một ký tự | "Trước = sau" từng ký tự; không khớp → "Cần xem" |
| Bình luận, lượt đọc, phân tích lạc bài | Giữ ID bài; kiểm số trước / sau |
| Hàng đợi dừng giữa chừng | Bước nhập chạy lại được, kết quả như nhau |
| Bản sao lưu không khôi phục được | Không xoá dữ liệu cũ khi chuyển; lệnh hoàn tác; rủi ro đã được người sáng lập chấp nhận (D-090) |
| Đường dẫn chia sẻ cũ (Zalo, Facebook) gãy | Bảng 301 đầy đủ; thử 20 đường dẫn ở bước 4 |
