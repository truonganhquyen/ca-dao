# Báo cáo khảo sát E2E — phongdao.com

| | |
|---|---|
| Ngày | 26/10/2026 (plugin 0.28.0, WordPress 7.1.3) |
| Cách làm | Playwright + Chrome, máy tính 1366 px và điện thoại 390 px; axe-core WCAG 2.2 AA; đọc mã trong `wordpress/` để tìm nguyên nhân |
| Kịch bản | `tests/e2e/` — chạy lại: `npm run e2e` (không ghi gì lên site), `npm run e2e:quan-tri` (cần `E2E_USER`, `E2E_PASS`) |
| Kết quả lần chạy cuối | Người đọc + tìm + bảo mật: 90 đạt (trong đó 22 kiểm thử "lỗi đã biết" vẫn đúng là còn lỗi), 2 bỏ qua theo thiết bị. Quản trị: 9/9 đạt |
| Ảnh | `docs/e2e-review/screenshots/` (trang công khai); `screenshots/admin/` (quản trị — đã `.gitignore`, không đưa lên repo công khai) |

## Tóm tắt

Phần mềm **chắc chắn hơn nội dung**. Mọi trang công khai qua quét trợ năng WCAG 2.2 AA không lỗi nào, không lỗi JavaScript, không tràn ngang trên điện thoại. Tìm kiếm có dấu và không dấu cho kết quả như nhau. Trang quản trị không có lỗi PHP, mỗi trang mở trong khoảng 1,3–1,8 giây. Luồng thêm, sửa, Chờ duyệt, xóa, chống trùng, góp ý của người đọc đều chạy đúng.

Ba điểm yếu lớn nhất:

1. **Nội dung mỏng so với tên site.** Có 171 bài: 151 ca dao, 20 vè, **0 tục ngữ, 0 thành ngữ**. Chỉ 1 bài có Hiểu nhanh và 0 bài có lời đã đối chiếu sách. Trong khi đó kho thu thập có hơn 16.000 câu, 2.265 câu đã được tách thành ngữ / tục ngữ.
2. **Cửa đăng nhập quản trị dễ bị dò.** Tên đăng nhập bị lộ qua 3 đường. XML-RPC còn bật, không có giới hạn số lần đăng nhập sai. Mật khẩu quản trị cũng đã được gửi dạng chữ thường trong yêu cầu khảo sát này.
3. **Người đọc dễ hết đường đi.** Cuối bài không có bài liên quan. Hai nhóm lọc Vùng, Chủ đề trông như tiêu đề rỗng. Kết quả tìm không cho thấy chữ người đọc đã gõ. Trang 404 vẫn là mẫu mặc định của theme.

---

## Cập nhật tình trạng (27/10/2026)

Báo cáo dưới đây giữ nguyên như lúc khảo sát (26/10/2026, plugin 0.28.0) làm hồ sơ; ảnh trong `screenshots/` là ảnh **lúc còn lỗi**. Bảng này là tình trạng sau khi đã sửa, kiểm lại trên phongdao.com (plugin 0.33.2) bằng `npm run e2e` và các kiểm tra riêng; lỗi nào còn thì kiểm thử `test.fail` trong `tests/e2e/` vẫn đánh dấu.

| # | Tình trạng | Ghi chú |
|---|---|---|
| L-01 | Đã sửa | `?ma=` thay `?ly_do=` (T-124) |
| L-02 | Đã sửa | nhóm lọc Vùng, Chủ đề có mũi tên (T-126) |
| L-03 | Đã sửa | REST users trả 404; `?author=`, thông báo đăng nhập (T-124). Còn việc của người sáng lập: Hồ sơ → tên hiển thị khác tên đăng nhập |
| L-04 | Đã sửa | kết quả tìm hiện tên bài và dòng khớp (T-126) |
| L-05 | Đã sửa | "Đọc tiếp" cuối bài (T-122, T-092) |
| L-06 | Đã sửa | trang "Không tìm thấy" riêng (T-126) |
| L-07 | Đã sửa | 30 bài mỗi trang, số trang to (T-126) |
| L-08 | Sửa một phần | câu không có trong kho không còn ra bài lạc đề; "lục bát" vẫn ra bài vè trước |
| L-09 | Chưa sửa | T-132 (READY): chịu gõ sai một chữ, "Có phải bạn tìm …?" |
| L-10 | Đã sửa | `/hello-world/` trả 404 |
| L-11 | Chưa sửa | `readme.html`, `license.txt` vẫn trả 200 (cấu hình máy chủ) |
| L-12 | Chưa sửa | chưa có Content-Security-Policy, Referrer-Policy |
| L-13 | Chưa sửa | chưa có header đệm trang; OPcache, Cloudflare là việc của người vận hành |
| L-14 | Chưa sửa | `/ca-dao/cong-cha-nhu-nui-thai-son/` vẫn chuyển 301 sang `…-2` (đổi đường dẫn bài đã đăng là H6) |
| L-15 | Phần máy đã xong | tục ngữ, thành ngữ vào hàng Chờ duyệt (T-125, T-128); site chưa có bài tục ngữ nào đã đăng — chờ người sáng lập duyệt |
| L-16 | Đã sửa | XML-RPC tắt (T-124) |
| L-17, L-20 | Chưa kiểm lại | số "Có thể trùng" đếm cả cặp mồ côi; nhận mật khẩu yếu |

Việc tiếp theo cho các mục "Chưa sửa" nằm trong `docs/nghien-cuu/ke-hoach-thuc-hien-2026-10-27.md` và `docs/22` (T-129..T-138).

## 1. Phạm vi và an toàn

- **Đối chiếu repo ↔ site:** route, loại bài (`cdtn`, `gop_y`, `phan_tich`), phân loại (`the_loai`, `chu_de`, `vung`, `the_tho`, `tu_kho`, `tinh_huong`, `tro_choi`), REST `cadao/v1/*`, 10 trang quản trị của plugin — đều khớp với `docs/02` và mã.
- **Không làm bẩn số liệu:** mọi yêu cầu `luot-doc` và `su-kien` bị chặn trong trình duyệt khảo sát. Vì vậy các câu tìm thử, sai chính tả, rỗng không vào ô "câu tìm không ra", và lượt xem không cộng vào "Đọc nhiều".
- **Bản ghi thử:** 1 bài `[E2E-TEST]` (mỗi lần chạy), 1 góp ý `[E2E-TEST]`, 1 tài khoản `e2e-test-…` (Thành viên, không gửi thư). Bài thử chỉ ở **Bản nháp → Chờ duyệt, không bao giờ bấm Đăng** (AGENTS.md: máy không tự đăng bài). Tất cả đã xóa hẳn. Đã kiểm lại sau lần chạy cuối: 0 bài, 0 góp ý, 0 tài khoản thử còn sót; thùng rác trống; hàng "Có thể trùng" chỉ còn các cặp thật.
- **Không làm:**
  - Không tạo chủ đề, vùng, thể loại, tình huống thử (AGENTS.md cấm thêm giá trị vào bộ chủ đề, vùng).
  - Không bấm duyệt hay gộp trong Kho thu thập, Có thể trùng, Wikidata, Bạn có biết?, Gợi ý vùng – chủ đề, Tách thành ngữ (đều là dữ liệu thật, H2/H6).
  - Không thử dò mật khẩu: chỉ 1 lần sai mật khẩu với tài khoản thật lúc khám phá; kịch bản tự động chỉ thử với tên không tồn tại.
  - Không sửa mã ứng dụng.
- Báo cáo **không** ghi email, mật khẩu hay tên đăng nhập quản trị, và không chép lời từ kho thô (repo công khai).

## 2. Bản đồ luồng đã đi

### Người đọc (máy tính + điện thoại 390 px)

```
Trang chủ ─┬─ ô Tìm ──────────► /tim/?q=…  (gõ dần qua API; tắt JS vẫn ra kết quả)
           │                       ├─ có dấu / không dấu / HOA / dấu câu thừa / khoảng trắng thừa ✔
           │                       ├─ sai chính tả ✘ (L-09) · câu không có trong kho ra bài lạc đề ✘ (L-08)
           │                       └─ rỗng: "Chưa tìm thấy…" + lối sang Tất cả, Từ điển ✔
           ├─ Câu hôm nay, Bạn có biết?, Đọc nhiều, Vừa bổ sung → trang bài ✔
           ├─ Theo thể loại → /the-loai/ca-dao/, /the-loai/ve/ ✔
           ├─ Theo vùng → /vung/bac-bo/ … ✔ · Bản đồ → /ban-do/ ✔
           └─ thanh dưới (điện thoại): Trang chủ · Tìm · Tất cả · Đã lưu ✔ (không che chân trang)
Tất cả (/kho/) ── chip Thể loại, Thể thơ ✔ · nhóm Vùng, Chủ đề (thu gọn, không thấy mũi tên ✘ L-02)
               └─ Xếp A–Z / Mới thêm ✔ · phân trang 18 trang, số trang nhỏ ✘ (L-07)
Trang bài ── lời (khối Thơ) · dòng "Lời theo bản lưu truyền phổ biến…" + "Vì sao?" ✔
          ├─ chạm từ có chú thích → ô nghĩa; Esc đóng, focus trở về ✔
          ├─ Trích dẫn (tên bài, phongdao.com) ✔ · ☆ Lưu → /da-luu/ → bỏ lưu ✔
          ├─ Chia sẻ, Aa, sáng/tối, Trình chiếu: có nút (không bấm thử) · Dị bản ✔ · Hiểu nhanh (1 bài) ✔
          ├─ Chủ đề, Vùng ✔ · bài liên quan ✘ (L-05)
          └─ Bạn muốn góp gì? → Góp ý (gửi thật 1 lần, đã xóa) ✔ · Cảm nghĩ · Viết cảm nhận
Từ điển (/tu-dien/) ── lọc vùng / từ cổ / Hán Việt, chữ cái ✔
404 ── mã 404 đúng, có ô tìm; giao diện mặc định của theme ✘ (L-06)
Trang tĩnh: Phương pháp, Nguồn, Về dự án, Chính sách thu thập dữ liệu ✔
```

### Quản trị

```
Đăng nhập (wp-login.php) ✔ ── thông báo phân biệt "sai tên" / "sai mật khẩu" ✘ (L-03)
Bảng tin ── việc chờ bạn, lượt đọc 7 ngày, tình trạng site ✔
Ca dao – Tục ngữ
  ├─ Bàn biên tập (Làm trước: Viết Hiểu nhanh 49 · Có thể trùng 10–11; Khi rảnh: 4) ✔ cả trên điện thoại
  ├─ Thêm bài mới ── gõ tiêu đề, dán lời của một bài đã có vào khối Thơ
  │     ├─ API trùng báo đúng bài "Ba cô đội gạo lên chùa" ✔ → Lưu nháp ✔ → nhãn "Có thể trùng" trong danh sách ✔
  │     ├─ trình soạn trên điện thoại 390 px mở được ✔ (ô Có thể trùng, Hiểu nhanh nằm trong thanh bên / "Hộp Meta" đang đóng)
  │     ├─ Sửa tiêu đề + chuyển Chờ duyệt ✔ → Xem trước ✔ → người ngoài không đọc được qua REST ✔
  │     └─ Thùng rác → Xóa vĩnh viễn ✔ → không còn cặp trùng nào trỏ tới bài thử ✔
  ├─ Tất cả bài, Thể loại, Chủ đề, Vùng, Thể thơ, Từ điển, Tình huống, Trò chơi (xem, không tạo) ✔
  ├─ Góp ý của người đọc ── góp ý gửi từ trang bài (không đăng nhập) vào Chờ duyệt ✔ → xóa hẳn ✔
  ├─ Cảm nhận – phân tích, Bình luận (xem) ✔
  ├─ Nguồn tự động, Kho thu thập, Có thể trùng, Tách thành ngữ – tục ngữ, Xác nhận Wikidata,
  │  Gợi ý vùng – chủ đề, Bạn có biết?, Chống rác, Viết Hiểu nhanh (mở, đo thời gian, xem — không bấm quyết) ✔
Tài khoản ── thêm Thành viên thử (không gửi thư) ✔ → xóa ✔ · mật khẩu "123456" được nhận sau một ô xác nhận (L-20)
Cài đặt / Tình trạng site ── chặn công cụ tìm kiếm (đúng, chờ H5), OPcache tắt, còn theme thừa
```

## 3. Danh sách lỗi

Mức độ: **Cao** = ảnh hưởng an toàn hoặc mục tiêu sản phẩm, nên làm ngay; **TB** = người đọc gặp thường xuyên hoặc rủi ro có thật; **Thấp** = nên sửa khi tiện.

| # | Lỗi | Mức | Loại |
|---|---|---|---|
| L-16 | XML-RPC còn bật (`system.multicall`) — đường dò mật khẩu hàng loạt | **Cao** | Bảo mật |
| L-03 | Lộ tên đăng nhập quản trị (REST users, `?author=1`, thông báo đăng nhập) | **Cao** (khi cộng L-16 và mật khẩu đã lộ) | Bảo mật |
| L-15 | Không có tục ngữ, thành ngữ; Hiểu nhanh 1/171 bài | **Cao** | Nội dung |
| L-01 | Ai cũng chèn được chữ bất kỳ vào khung báo lỗi trên trang bài qua `?ly_do=` | TB | Bảo mật |
| L-02 | Nhóm lọc Vùng, Chủ đề không có mũi tên mở — trông như tiêu đề rỗng | TB | UX |
| L-04 | Kết quả tìm chỉ hiện dòng đầu, không hiện tên bài | TB | UX / tìm |
| L-05 | Cuối bài không có bài liên quan | TB | UX / nội dung |
| L-08 | Bài vè dài khớp gần như mọi câu tìm, đẩy bài đúng xuống | TB | Tìm |
| L-13 | Không bộ nhớ đệm trang, OPcache tắt; TTFB 0,4–0,7 s | TB | Hiệu năng |
| L-06 | Trang 404 là mẫu mặc định của theme (ảnh cây 115 KB, nút "Tìm kiếm" bị cắt) | TB | UX |
| L-07 | Phân trang 18 trang, số trang nhỏ khó chạm | Thấp | UX / trợ năng |
| L-09 | Sai một chữ là không ra bài | Thấp | Tìm |
| L-10 | Bài mẫu "Hello world!" và bình luận mẫu còn công khai | Thấp | Nội dung |
| L-11 | `readme.html`, `license.txt` trả 200 | Thấp | Bảo mật |
| L-12 | Thiếu Content-Security-Policy, Referrer-Policy | Thấp | Bảo mật |
| L-14 | Bài nổi tiếng nhất có đường dẫn đuôi `-2` | Thấp | SEO (H6) |
| L-17 | Số "Có thể trùng" đếm cả cặp mồ côi tới khi trang được mở | Thấp | Quản trị |
| L-20 | Nhận mật khẩu yếu cho tài khoản mới | Thấp | Bảo mật |

### L-16 · XML-RPC còn bật — **Cao**

- **Hiện tượng:** `GET /xmlrpc.php` trả 405 (trông như đã tắt), nhưng `POST` vẫn trả danh sách phương thức, gồm `system.multicall` và `pingback.*`. `system.multicall` cho thử hàng trăm mật khẩu trong một yêu cầu, vượt mọi giới hạn theo yêu cầu. Pingback còn có thể bị dùng để dội tải sang site khác.
- **Tái hiện:** `curl -X POST -H 'Content-Type: text/xml' -d '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>' https://phongdao.com/xmlrpc.php`
- **Mã:** không có trong plugin. Sửa: `add_filter( 'xmlrpc_enabled', '__return_false' );` và gỡ `xmlrpc_methods`, hoặc chặn `/xmlrpc.php` ở Apache / Cloudflare. Site không dùng XML-RPC (người sáng lập soạn bằng trình soạn khối).
- **Kiểm thử:** `bao-mat.spec.mjs` › L-16.

### L-03 · Lộ tên đăng nhập quản trị — **Cao** (khi cộng L-16)

- **Hiện tượng:** có ba đường cho biết tên đăng nhập:
  - `GET /wp-json/wp/v2/users` trả tên;
  - `/?author=1` chuyển tới `/author/<tên-đăng-nhập>/`;
  - đăng nhập sai báo "Tên người dùng X không được đăng ký" khác với "Mật khẩu bạn đã nhập cho tên người dùng X không chính xác".

  Biết tên, kẻ dò chỉ còn phải đoán mật khẩu. Site lại chưa giới hạn số lần đăng nhập sai, và mật khẩu quản trị đã đi qua kênh chữ thường (yêu cầu khảo sát này).
- **Tái hiện:** mở ba địa chỉ trên trong trình duyệt ẩn danh.
- **Mã:** không có trong plugin. Sửa trong plugin, ví dụ một tệp `inc/an-toan.php`:
  - bỏ route `/wp/v2/users` với người chưa đăng nhập (`rest_endpoints`);
  - chuyển `?author=` và trang tác giả về trang chủ (`template_redirect`);
  - gộp thông báo đăng nhập sai thành một câu (`login_errors`).

  Đặt thêm tên hiển thị khác tên đăng nhập.
- **Kiểm thử:** `bao-mat.spec.mjs` › L-03.

### L-15 · Nội dung mỏng so với tên site — **Cao** (mục tiêu sản phẩm)

- **Hiện tượng (đếm từ 171 trang bài):**
  - **Thể loại:** 151 ca dao, 20 vè; thành ngữ, tục ngữ, đồng dao, câu đố đều 0 bài, dù thể loại đã có trong quản trị.
  - **Độ sâu từng bài:** 101/171 có Chú thích; 46/171 có Dị bản; **1/171** có Hiểu nhanh; 0/171 có lời đã đối chiếu sách.
  - **Tìm thử:** "uống nước nhớ nguồn", "có công mài sắt có ngày nên kim" → 0 bài.
  - **Kho thu thập:** 16.130 câu "Mới", 13.453 câu đã đọc trang gốc xong; trang Tách thành ngữ – tục ngữ có 2.265 câu "Bạn đã quyết".
  - **Hàng Chờ duyệt** đang trống, dù nguồn đặt 20 bài/ngày.
- **Vì sao quan trọng:** tên site và Google đều hứa "tục ngữ". Sitemap chỉ nhận bài có Hiểu nhanh (đang có 1 bài). Khi mở cửa (H5), site sẽ gần như vô hình.
- **Cần làm rõ (cho agent):** vì sao câu đã tách thành tục ngữ / thành ngữ chưa thành bài Chờ duyệt (`inc/kho.php` `cadao_kho_tao_bai_cho_duyet`, `inc/nguon-tu-dong.php:163`). Có thể máy đang chọn câu theo thứ tự nên ca dao luôn đứng trước, hoặc câu chờ tách loại bị bỏ qua.
- **Kiểm thử:** `tim-kiem.spec.mjs` › L-15.

### L-01 · Chèn chữ vào trang bài qua `?ly_do=` — TB

- **Hiện tượng:** sau khi gửi góp ý lỗi, site đọc lý do từ địa chỉ trang. Ai cũng tạo được liên kết như `phongdao.com/ca-dao/…/?gop_y=loi&ly_do=Tài khoản của bạn bị khóa, gọi 0900000000`, và câu đó hiện trong khung báo của site, đúng màu và phông của site. Không chạy được mã (đã `esc_html`), nhưng đủ để lừa đảo, mượn uy tín một site văn hoá phi lợi nhuận.
- **Ảnh:** `screenshots/may-tinh-chen-chu-ly-do.png`
- **Mã:** `wordpress/plugins/ca-dao/inc/nguoi-doc.php:148`, `wordpress/plugins/ca-dao/inc/phan-tich.php:83`. Sửa: chuyển `ly_do` thành **mã lỗi** (`?gop_y=loi&ma=gioi_han`) và tra câu báo trong một bảng cố định ở PHP.
- **Kiểm thử:** `bao-mat.spec.mjs` › L-01.

### L-02 · Nhóm lọc Vùng, Chủ đề trông như tiêu đề rỗng — TB

- **Hiện tượng:** trang Tất cả (`/kho/`, cả máy tính và điện thoại): dưới "Vùng" và "Chủ đề" không có gì. Thật ra đó là `<details>` thu gọn, nhưng `summary { display: flex }` làm mất mũi tên ▸ của trình duyệt, nên không ai biết bấm được. Hai bộ lọc giàu nhất bị ẩn.
- **Ảnh:** `screenshots/may-tinh-loc-vung-chu-de.png`, `screenshots/dien-thoai-tat-ca.png`
- **Mã:** `wordpress/themes/ca-dao/style.css:980` (`.cadao-loc-nhom summary`); HTML ở `wordpress/plugins/ca-dao/inc/danh-sach.php:136`. Sửa: thêm `summary::after { content: "▾" }` (xoay khi `[open]`), kèm số lựa chọn: "Vùng (7) ▾".
- **Kiểm thử:** `nguoi-doc.spec.mjs` › L-02.

### L-04 · Kết quả tìm không hiện tên bài — TB

- **Hiện tượng:** mỗi kết quả chỉ hiện **dòng đầu** của lời. Tìm "bang ninh" ra 1 bài, hiện là "Cầu Ngu, Trung Lễ xưa nay · Vè". Người đọc không thấy chữ mình gõ, vì nó nằm ở tên bài "Vè Nghĩa quân Bang Ninh – Hạ Thành Hà Tĩnh". Thêm nữa, API trả tiêu đề còn mã HTML (`&#8211;`), nên nếu sau này JavaScript dùng `tieu_de` thì sẽ in nguyên "&#8211;".
- **Ảnh:** `screenshots/may-tinh-tim-khong-thay-ten-bai.png`
- **Mã:** `wordpress/plugins/ca-dao/assets/tim.js:54` (`b.dong_dau || b.tieu_de`), `wordpress/plugins/ca-dao/inc/tim.php:206` (`get_the_title`). Sửa:
  - Hiện **tên bài** (đậm) + **dòng có chữ khớp** (tô sáng). `dong_khop` đã có sẵn trong API.
  - Trả `html_entity_decode( get_the_title() )` hoặc `$p->post_title`.
- **Kiểm thử:** `nguoi-doc.spec.mjs` › L-04; `tim-kiem.spec.mjs` › L-04.

### L-05 · Cuối bài không có bài liên quan — TB

- **Hiện tượng:** sau Chú thích, Chủ đề là khối góp ý rồi hết trang. Người đọc phải quay lại hoặc dùng thanh dưới, không có "đọc tiếp".
- **Ảnh:** `screenshots/dien-thoai-cuoi-bai.png`
- **Mã:** `wordpress/themes/ca-dao/templates/single-cdtn.html` (không có khối liên quan). Dữ liệu đã đủ để làm: cùng chủ đề, cùng vùng, cùng từ trong từ điển (`tu_kho`), và dị bản / cặp "Có thể là dị bản" đã quyết.
- **Kiểm thử:** `nguoi-doc.spec.mjs` › L-05.

### L-08 · Bài vè dài khớp gần như mọi câu tìm — TB

- **Hiện tượng:** điều kiện ứng viên là "chứa mọi chữ ở bất kỳ đâu". Bài vè hàng trăm dòng chứa gần đủ mọi chữ thông dụng, nên:
  - "ba co doi gao" ra "Vè cá", "Vè nói ngược" ngay sau bài đúng;
  - "lục bát" ra 3 bài vè;
  - "ăn quả nhớ kẻ trồng cây" (không có trong kho) lại ra "Con tôi buồn ngủ buồn nghê";
  - "hạ thành" ra 16 bài.

  Người đọc tưởng kho có câu mình tìm.
- **Mã:** `wordpress/plugins/ca-dao/inc/tim.php:123–162` (`cadao_tim`). Sửa nhỏ:
  - chỉ nhận ứng viên khi các chữ **đứng gần nhau** (trong một dòng, hoặc cách ≤ 3 âm tiết);
  - chấm điểm theo độ dài bài (chia cho số âm tiết);
  - khi chỉ khớp rời rạc, hiện dưới mục "Có các chữ này nhưng không liền nhau".
- **Kiểm thử:** `tim-kiem.spec.mjs` › L-08 (2 kiểm thử).

### L-13 · Không bộ nhớ đệm, OPcache tắt — TB

- **Hiện tượng:** mọi trang công khai do PHP dựng lại mỗi lượt. TTFB đo được 0,40–0,67 s, trang quản trị 1,0–1,9 s. Tình trạng site báo "Bộ nhớ đệm Opcode không được bật". Header trả `Server: Apache`, không qua Cloudflare, nên không có lớp đệm hay chống tấn công nào phía trước. Hiện tải còn nhẹ (165 KB, 10 yêu cầu mỗi trang), nhưng khi có lượt chia sẻ đột biến trên Facebook / Zalo, máy chủ một lõi sẽ nghẽn.
- **Sửa (người vận hành, không phải mã):**
  - bật OPcache trong FlashPanel (PHP);
  - bật proxy Cloudflare (đám mây cam) với quy tắc đệm HTML cho người chưa đăng nhập, hoặc một plugin đệm trang tĩnh.

  Lượt đọc vẫn đếm đúng vì đếm bằng JavaScript (`luot-doc`), không đếm phía máy chủ. Khi bật Cloudflare, các kiểm tra IP Cloudflare trong `inc/luot-doc.php` đã sẵn sàng.
- **Kiểm thử:** `bao-mat.spec.mjs` › L-13; `nguoi-doc.spec.mjs` › "hiệu năng".

### L-06 · Trang 404 là mẫu mặc định — TB

- **Hiện tượng:**
  - **Hình ảnh:** ảnh "cây totara trên sườn núi" của Twenty Twenty-Five (tải thêm khoảng 115 KB);
  - **Câu chữ:** "Vui lòng thử tìm kiếm bằng biểu mẫu bên dưới", ô "Nhập tại đây…" — không cùng giọng với site;
  - **Nút Tìm:** trên điện thoại, nút "Tìm kiếm" vỡ dòng, chỉ thấy chữ "kiếm";
  - **Đường đi tiếp:** không có lối sang Tất cả, Từ điển, bài ngẫu nhiên.
- **Ảnh:** `screenshots/dien-thoai-404.png`, `screenshots/may-tinh-404.png`
- **Mã:** theme con chưa có `wordpress/themes/ca-dao/templates/404.html`. Sửa: thêm tệp này, dùng lại `[cadao_tim]` và vài liên kết, ví dụ câu "Câu này có thể đã đổi đường dẫn — thử tìm vài chữ trong câu".
- **Kiểm thử:** `nguoi-doc.spec.mjs` › L-06.

### L-07 · Phân trang khó dùng — Thấp

- **Hiện tượng:** 171 bài chia 10 bài / trang thành 18 trang; số trang "1 2 3 … 18" là chữ nhỏ, khoảng 10×20 px (dưới 24 px của WCAG 2.5.8 và dưới 44 px của quy ước dự án). Mẫu `archive-cdtn.html` đặt `perPage: 20` nhưng `inherit: true` nên vẫn theo cài đặt 10 bài của WordPress.
- **Ảnh:** `screenshots/dien-thoai-phan-trang.png`
- **Mã:** `wordpress/themes/ca-dao/templates/archive-cdtn.html:10`, CSS phân trang trong `style.css`. Sửa: 30–50 bài mỗi trang (mỗi bài chỉ 2 dòng); nút "Trang sau" và số trang cao ≥ 44 px.
- **Kiểm thử:** `nguoi-doc.spec.mjs` › L-07.

### L-09 · Không chịu lỗi gõ — Thấp

- **Hiện tượng:** "ba co doi gai" (gõ nhầm "gạo") ra "Vè cá", không ra bài đúng. "bacodoigao" (gõ liền) ra 0 bài.
- **Sửa:** khi 0 kết quả hoặc kết quả yếu, thử lại với chữ cuối bỏ một ký tự, hoặc so khoảng cách sửa ≤ 1 trên từng âm tiết của chỉ mục đã bỏ dấu; hiện "Có phải bạn tìm …?". Làm sau L-08.
- **Kiểm thử:** `tim-kiem.spec.mjs` › L-09.

### L-10 · Bài mẫu "Hello world!" còn công khai — Thấp

- **Hiện tượng:** `/hello-world/` (và `/?p=1`) đang đăng, có bình luận của "A WordPress Commenter". `/feed/` chỉ có bài này, không có ca dao.
- **Sửa (người sáng lập):** xóa bài và bình luận mẫu. Agent không tự xóa bài đã đăng (H6). Nên cân nhắc cho bài ca dao vào RSS.
- **Kiểm thử:** `bao-mat.spec.mjs` › L-10.

### L-11, L-12, L-20 · Cấu hình bảo mật nhỏ — Thấp

- `readme.html` và `license.txt` trả 200 (L-11). Chặn ở Apache hoặc xóa sau mỗi lần cập nhật.
- Chưa có `Content-Security-Policy` (có thể bắt đầu ở chế độ `-Report-Only`), `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` (L-12). `X-XSS-Protection` đã lỗi thời, có thể bỏ.
- Trang Thêm người dùng nhận mật khẩu "123456" sau một ô "xác nhận dùng mật khẩu yếu" (L-20). Hiện không nguy hiểm vì chỉ có 1 tài khoản và đã tắt tự đăng ký.

### L-14 · Đường dẫn đuôi `-2` — Thấp (H6)

`/ca-dao/cong-cha-nhu-nui-thai-son/` chuyển 301 sang `/ca-dao/cong-cha-nhu-nui-thai-son-2/`, di chứng của lần gộp trùng. Đây lại là bài duy nhất có Hiểu nhanh và là câu nổi tiếng nhất. Nên đổi về đường dẫn sạch **trước khi mở cho Google**. Đổi đường dẫn bài đã đăng là H6: người sáng lập quyết.

### L-17 · Số "Có thể trùng" đếm cả cặp mồ côi — Thấp

- **Hiện tượng:** khi một bài bị xóa hẳn mà không qua thùng rác (ví dụ qua REST), cặp trùng của nó vẫn ở trạng thái "chờ". Trang Có thể trùng tự dọn các cặp này lúc hiển thị, nhưng con số được đếm **trước** khi dọn. Lần khảo sát thấy 10 → 12 → 11: hai cặp của bài thử đã xóa, cộng một cặp mới do máy thu thập thêm. Số trên menu, Bảng tin và Bàn biên tập có thể lệch tạm thời.
- **Mã:** `wordpress/plugins/ca-dao/inc/trang-trung.php:57–80` (đếm trước vòng dọn); `wordpress/plugins/ca-dao/inc/trung.php:611` chỉ móc `wp_trash_post`. Sửa: móc thêm `before_delete_post`, hoặc dọn trước khi đếm.

## 4. Đánh giá

### UX (người đọc)

**Tốt:**
- Thiết kế đọc tốt trên điện thoại: chữ lớn, lục bát thụt lề, thanh dưới 56 px.
- Chạm từ để xem nghĩa rất tự nhiên.
- Trích dẫn và Lưu dùng được (đã bấm thử); có nút Chia sẻ, Trình chiếu, cỡ chữ, sáng / tối (không bấm thử).
- Khối "Bạn muốn góp gì?" rõ ràng, không đòi email.
- Câu báo khi tìm không ra rất tử tế.

**Chưa tốt:**
- **Đi tiếp sau khi đọc** (L-05) và **lọc** (L-02, L-07) là hai chỗ người đọc bị "tắc".
- **Kết quả tìm** không cho thấy vì sao bài khớp (L-04).
- **Menu trên điện thoại** chỉ có "Thể loại ▾ · Từ điển"; Tìm và Tất cả nằm ở thanh dưới. Vậy là đủ, nhưng trang chủ trên điện thoại cắt chữ gợi ý trong ô tìm ("Tìm một câu: gõ vài chữ,").
- **Bản đồ** trên điện thoại khá nhỏ (khoảng 170 px), 19 điểm chạm dưới 24 px. Danh sách địa danh bên dưới bù lại được.
- **Trang 404** (L-06).

### Nội dung

**Tốt:**
- Lời rõ ràng, có dòng mức kiểm trung thực ("chưa đối chiếu sách").
- Chú thích dày ở các bài có chú thích.
- Từ điển đã có 200 mục.
- "Bạn có biết?" ghi nguồn.

**Thiếu (L-15):**
- tục ngữ / thành ngữ;
- Hiểu nhanh (1/171);
- lời đối chiếu sách (0/171);
- 70/171 bài không có chú thích nào, nghĩa là chỉ có lời trần.

Trang bài vì vậy chưa "sâu" như mục tiêu: phần lớn bài chỉ có lời + 1–2 chú thích. Điểm nghẽn là **thời gian của người biên soạn**, không phải phần mềm. Kho có hơn 16.000 câu, còn hàng Chờ duyệt đang trống.

### Hiệu năng

- Trang công khai: TTFB 0,40–0,67 s; tải xong 0,6–1,0 s; khoảng 165 KB, 10 yêu cầu. JavaScript ít, phông tự lưu trữ. Đạt.
- API tìm: dưới 1,5 s.
- Trang quản trị: 1,0–1,9 s.
- Chưa có lớp đệm (L-13), nên rủi ro nằm ở lúc tải tăng đột biến, không phải ở tải hằng ngày.

### SEO (site đang chặn Google theo quyết định, chờ H5)

**Đã có:**
- `<title>`, `meta description` riêng từng trang;
- `og:image` 1200×630 hoạt động (đã kiểm: trả ảnh PNG);
- JSON-LD `CreativeWork` + `BreadcrumbList` hợp lệ trên cả 171 bài;
- canonical trên trang bài và trang tĩnh.

**Thiếu:**
- **Canonical:** trang chủ, `/kho/`, trang thể loại / vùng chưa có thẻ canonical (lọc `?loai=`, `?mien=` và `/page/N/` dễ sinh trang trùng).
- **Sitemap:** hiện là trang 404 vì đang chặn lập chỉ mục. Đó là bình thường, nhưng cần kiểm lại ngay sau khi mở.
- **Trang bài thiếu chữ riêng:** khi mở cửa, chỉ bài có Hiểu nhanh mới vào sitemap. Nếu không có thêm Hiểu nhanh, gần như không có trang nào để Google lập chỉ mục.
- **Đường dẫn:** `-2` (L-14).

### Bảo mật

**Tốt:**
- HSTS 2 năm, `nosniff`, `X-Frame-Options`;
- thư mục `uploads/`, `.git` bị chặn (403);
- tắt đăng ký tài khoản; chỉ 1 tài khoản Quản lý;
- WordPress, plugin đều mới nhất;
- form người đọc có nonce, ô bẫy, giới hạn 5 lần / giờ, không lưu IP;
- REST của plugin kiểm quyền đúng (`trung`, `them-di-ban`… cần `edit_posts`);
- bài Chờ duyệt không đọc được từ ngoài;
- các chuỗi lạ (HTML, SQL, `%`, `_`, 600 ký tự) gửi vào API tìm không gây lỗi.

**Yếu:**
- XML-RPC (L-16) + lộ tên đăng nhập (L-03) + không giới hạn đăng nhập sai + không xác thực hai lớp + **mật khẩu đã đi qua kênh chữ thường**;
- chèn chữ qua tham số (L-01);
- chưa có Turnstile;
- thiếu CSP (L-12).

### Trợ năng

Quét axe WCAG 2.2 A/AA trên 12 trang × 2 thiết bị: **0 vi phạm**. Bàn phím:
- Tab đầu tiên tới "Chuyển đến nội dung";
- ô chú thích đóng bằng Esc và trả focus về từ;
- chip, nút ≥ 44 px.

Ngoại lệ: số phân trang (L-07), điểm trên bản đồ, và nhóm lọc không có dấu hiệu mở (L-02, cũng là vấn đề trợ năng nhận thức).

### Quản trị

**Tốt:**
- Bàn biên tập gom việc rất tốt: "Làm trước / Khi rảnh", số đếm, nút Mở, dùng được trên điện thoại.
- Chống trùng hoạt động thật: dán lời của bài đã có → API báo đúng bài → danh sách gắn nhãn "Có thể trùng" → xóa bài thì cặp tự bỏ.

**Thao tác thừa / rủi ro:**
1. **Trên điện thoại, ô "Có thể trùng" và "Viết Hiểu nhanh" nằm trong thanh bên / "Hộp Meta" đang đóng.** Người sáng lập soạn trên điện thoại sẽ chỉ thấy cảnh báo trùng khi bấm Xuất bản (khung trước khi đăng). Nên hiện một dải báo ngay dưới tiêu đề khi có bài trùng.
2. **"Có thể trùng" có nhiều cặp "giống 100% · trùng một dị bản đã ghi"** (6/11 cặp lúc khảo sát) mà vẫn chờ người quyết. Có thể cho máy tự gắn như "giống hệt, cùng thể loại" (máy báo, người tách lại được). Đổi quy tắc này cần người sáng lập đồng ý.
3. **Thể loại "Câu đố, Đồng dao, Thành ngữ, Tục ngữ" có sẵn nhưng 0 bài**, nên menu tự ẩn. Hợp lý, nhưng người sáng lập không thấy được kho đang thiếu gì. Bàn biên tập nên có một dòng "Kho: 151 ca dao · 20 vè · 0 tục ngữ…".
4. **Trình soạn có sẵn một khối Thơ rỗng ("Hãy viết thơ…").** Nếu dán lời ra ngoài khối đó, sẽ có hai khối Thơ (đã thấy khi thử). Máy chỉ lấy khối Thơ đầu làm lời chính (`soan-trung.js` `loiHtml`), nên lời có thể bị bỏ sót khi so trùng.
5. **Thiếu kiểm tra dữ liệu khi lưu:** bài có thể lưu khi không có thể loại, không có khối Thơ. Chỉ báo, không chặn (theo D-054), nhưng nên có dòng nhắc trong Bàn biên tập: "N bài chưa có thể loại".

## 5. Đề xuất cải tiến và ý tưởng mới

Xếp theo **tác động / công sức** cho dự án một người, dưới 10 giờ mỗi tuần. Công sức: S = dưới 1 giờ, M = nửa ngày đến 1 ngày (agent), L = nhiều ngày. "Ai": NV = người vận hành / người sáng lập bấm, AG = agent làm theo quy trình PR.

### Làm ngay (tác động cao, công sức S)

| Việc | Lỗi | Ai | Công sức |
|---|---|---|---|
| Đổi mật khẩu quản trị (mật khẩu dài, trình quản lý mật khẩu), bật xác thực hai lớp | — | NV | S |
| Tắt XML-RPC, ẩn tên đăng nhập, gộp thông báo đăng nhập sai | L-16, L-03 | AG | S |
| Bật OPcache trong FlashPanel; cân nhắc bật proxy Cloudflare | L-13 | NV | S |
| Mũi tên cho nhóm lọc Vùng, Chủ đề | L-02 | AG | S |
| `ly_do` → mã lỗi | L-01 | AG | S |
| Xóa "Hello world!" và bình luận mẫu | L-10 | NV | S |

### Nên làm trước khi mở cho Google (tác động cao, công sức M)

| Việc | Lỗi | Ai | Công sức |
|---|---|---|---|
| Khối "Đọc tiếp" cuối bài: cùng chủ đề / vùng / từ trong từ điển, 3–5 bài | L-05 | AG | M |
| Sửa xếp hạng tìm: chữ liền nhau, chia theo độ dài; kết quả hiện tên bài + dòng khớp | L-08, L-04 | AG | M |
| Trang 404 riêng, đúng giọng site | L-06 | AG | S–M |
| Danh sách 30–50 bài / trang, phân trang to | L-07 | AG | S |
| Tìm hiểu vì sao tục ngữ / thành ngữ chưa thành bài; cho hàng Chờ duyệt nhận cả thể loại này | L-15 | AG | M |
| Canonical cho trang chủ, danh sách, trang lọc | SEO | AG | S |
| Đổi đường dẫn `-2` của "Công cha như núi Thái Sơn" | L-14 | NV quyết (H6) | S |

### Ý tưởng tính năng mới (hợp nguồn lực ít)

| Ý tưởng | Tác động | Công sức | Ghi chú |
|---|---|---|---|
| **"Duyệt nhanh" trên điện thoại:** mỗi lần một bài Chờ duyệt / một nháp Hiểu nhanh, ba nút lớn Đăng · Sửa · Để sau | Cao — gỡ đúng điểm nghẽn thời gian | M | Người bấm Đăng vẫn là người sáng lập. Tận dụng nháp Hiểu nhanh của agent (`docs/huong-dan/viet-hieu-nhanh.md`) |
| **Ô "Kho đang thiếu gì"** trên Bàn biên tập: số bài theo thể loại, số bài không chú thích, không Hiểu nhanh; "câu tìm không ra" nhiều nhất kèm nút "tìm trong Kho thu thập" | Cao | S–M | Biến số liệu sẵn có thành việc cụ thể |
| **"Có phải bạn tìm…?"** khi 0 kết quả: tra Kho thu thập (chưa thành bài) và cho người đọc bấm "Mong có câu này" | Cao | M | Kho không hiện ra trang; chỉ hiện tên câu và đếm lượt mong, không hiện lời kho thô cho tới khi thành bài |
| **Bài theo dịp** (Tết, Trung thu, 20/11, Vu Lan) trên trang chủ từ `tinh-huong.json` / lịch đã có | TB | S | Có sẵn hạ tầng tình huống |
| **Tự gắn cặp "giống 100% một dị bản đã ghi"** | TB (giảm việc chờ) | S | Cần người sáng lập đồng ý đổi quy tắc |
| **RSS cho bài ca dao** (`/feed/` hiện chỉ có Hello world) | Thấp–TB | S | Người đọc trung thành, các nhóm Zalo / Facebook theo dõi bài mới |
| **Trang "Một câu mỗi ngày"** chia sẻ được (ảnh 1200×630 đã có) | TB | S | Kênh lan truyền tự nhiên, không quảng cáo |
| **Tìm theo vần / chữ cuối câu lục** (ví dụ mọi câu kết bằng "-ang") | TB (đặc thù ca dao, người học thích) | M | Chỉ mục đã có lời theo dòng |

**Không nên làm lúc này:** mở rộng thêm loại tính năng mới (đố vui, giọng đọc sinh sẵn…), đúng như D-073 đã tạm dừng. Mỗi giờ của người sáng lập đổ vào Hiểu nhanh và duyệt bài có tác động lớn hơn.

## 6. Top 5 việc nên làm ngay

1. **Khóa cửa quản trị (hôm nay).** Đổi mật khẩu quản trị (mật khẩu đã gửi dạng chữ thường trong yêu cầu khảo sát) và bật xác thực hai lớp. Nhờ agent tắt XML-RPC, ẩn tên đăng nhập, gộp thông báo đăng nhập sai (L-16, L-03). Khoảng 1 giờ tổng.
2. **Bật OPcache và bộ nhớ đệm trang** (FlashPanel, Cloudflare) trước khi có lượt chia sẻ lớn (L-13). Người vận hành, khoảng 30 phút.
3. **Gỡ chỗ "tắc" của người đọc trong một PR:** mũi tên nhóm lọc (L-02), khối "Đọc tiếp" cuối bài (L-05), danh sách 30–50 bài / trang (L-07), trang 404 riêng (L-06). Agent, khoảng 1 ngày.
4. **Sửa tìm kiếm:** xếp hạng theo chữ liền nhau, hiện tên bài + dòng khớp, sửa `ly_do` (L-08, L-04, L-01). Agent, khoảng nửa ngày.
5. **Mở đường cho tục ngữ và Hiểu nhanh (L-15).** Agent tìm vì sao 2.265 câu đã tách loại chưa thành bài Chờ duyệt. Người sáng lập nhờ agent soạn nháp Hiểu nhanh theo lô 10 bài mỗi tuần, chỉ đọc và sửa. Đây là việc quyết định site có "sâu" và có được Google thấy hay không.

## 7. Ghi chú cho agent sau

- Lỗi đã biết đều có kiểm thử `test.fail(true, 'L-xx …')` trong `tests/e2e/`. Sửa xong một lỗi thì kiểm thử báo "Expected to fail, but passed" → xóa dòng `test.fail` để nó thành kiểm thử bảo vệ.
- Human Gate liên quan:
  - **H6:** đổi đường dẫn `-2`; xóa "Hello world!".
  - **H2:** mọi nút duyệt, gộp trong hàng chờ.
  - **H5:** bỏ chặn Google.
  - Tự gắn cặp trùng 100% là đổi hành vi đã chốt (D-065): hỏi người sáng lập.
- Chưa khảo sát: luồng Turnstile (chưa có khóa); "Lấy ngay" của Nguồn tự động (ghi lên kho thật); gộp hai bài đã đăng (H6); đọc trên Safari / iOS thật (chỉ giả lập Chrome 390 px).
