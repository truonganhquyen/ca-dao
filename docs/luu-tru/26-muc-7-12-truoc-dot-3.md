# 26 · Mục 7 và 12 cũ (trước đợt 3)

> Lưu trữ ngày 11/11/2026 (D-107). Nguyên văn hai mục của `docs/26-kien-truc-v2.md` trước đợt 3: mục 7 mô tả quản trị theo D-094 và đích D-096 (đã làm ở T-166..T-188); mục 12 là bảng chuyển mã MVP sang V2 (đã chuyển 04/11/2026). Tài liệu đang dùng: `docs/26`, `docs/28`, `docs/32`.

## 7. Biên tập: hiện trạng và hướng nâng cấp admin

**Hiện trạng D-094:** không có ứng dụng quản trị riêng. Màn mẫu nhóm B của `design/v2` (B01–B05) mô tả các đường đang dùng; chi tiết ở `docs/28` mục 4. Các dòng dưới đây mô tả hiện trạng, không phải màn admin đích của D-096.

- **Chế độ sửa trên trang đọc.** Khi người xem có quyền `cadao_soan`, các khối `cadao/*` in thêm nút **Sửa** và dải "Đang sửa" (trạng thái bài, "Xem như người đọc", Ẩn / Đăng); khách không bao giờ nhận các phần này trong HTML. Nút Sửa mở **tấm sửa** (`<dialog>`: trượt từ dưới lên trên điện thoại, cột phải trên máy tính), lưu một phần qua `PUT /tac-pham/{id}/phan/{phan}`, rồi khối được vẽ lại từ máy chủ — người biên soạn thấy đúng như người đọc sẽ thấy. Trang tuyển tập, mục từ, trò chơi, mô-típ, chủ đề, vùng, thể loại làm cùng cách.
- **Bốn trang biên tập** do plugin đăng ký đường dẫn và mẫu khối (`register_block_template`): `/bien-tap/` (Hộp việc), `/bien-tap/tac-pham/`, `/bien-tap/them/`, `/bien-tap/so-lieu/`; khối `cadao/hop-viec`, `cadao/ds-tac-pham`, `cadao/them-bai`, `cadao/so-lieu-tuan`. Chưa đăng nhập → chuyển tới trang đăng nhập rồi quay lại; không đủ quyền → 403 tiếng Việt. Luôn `noindex`, không có trong sitemap.
- **Mã sửa:** một tệp JavaScript thuần `bien-tap.js` (không bước dựng, không thư viện), chỉ nạp khi người xem có quyền `cadao_soan`; ngân sách riêng ≤ 30 KB gzip, không tính vào ngân sách trang bài của người đọc. Không có JavaScript thì các trang biên tập vẫn đọc được và các quyết định chính của Hộp việc vẫn gửi được bằng form.
- **Bộ nhớ đệm:** trang của người đăng nhập trả `Cache-Control: private, no-store`; đệm transient của khối chỉ dùng cho khách; lưu một phần xoá đệm của bài đó.
- **Nguồn & cài đặt:** một trang PHP trong wp-admin (menu "Phongdao"), form gửi qua `admin-post.php` có nonce và kiểm quyền: hồ sơ nguồn, nguồn tự động, kho (tìm, Đã gộp, "Tách ra"), máy đang làm, cài đặt. Menu "Phongdao" còn có đường tắt tới Hộp việc.
- **wp-admin hiện tại:** ẩn với vai Biên tập các menu không cần (Bài viết, Bình luận gốc, Công cụ…); liên kết "Sửa" của tác phẩm trong WordPress (thanh quản trị, `get_edit_post_link`) trỏ về trang bài; `post.php` của `tac_pham` chuyển hướng về trang bài.
- Điện thoại trước (390 px), dùng một tay; máy tính có phím tắt ở Hộp việc. Đạt WCAG 2.2 AA như trang người đọc (cùng `quet:v2`).

**Đích D-096 (`docs/30`, đang triển khai — đã có đăng hàng loạt từ Kho qua lô `kho_dua_vao` và `cau_moi_dang` (D-099, `DichVu\XuLyLo\Lo`), Bàn làm việc (việc nên làm, vào/ra, tuổi thẻ, ngoại lệ dữ liệu — `GiaoTiep\QuanTri\BanLamViec`, `DichVu\XepViec\SoViec`), Tác phẩm, Hộp việc, Sửa tác phẩm gồm phân loại, Lịch sử so trước/sau và khôi phục, Kho tự chọn câu, Lô thao tác trên Hộp việc và Tác phẩm qua `DichVu\XuLyLo\Lo`):** menu Phongdao trong wp-admin có Tác phẩm, Hộp việc, Nguồn và các danh mục. `tac_pham` đã có `show_ui`, nhưng `show_in_menu` đang tắt; bật danh sách phải kèm cột, lọc, phân trang, kiểm quyền và biểu mẫu riêng. Không mở sửa trực tiếp post meta mảng qua ô Trường tuỳ chỉnh hay REST mặc định. Trang sửa có cấu trúc gọi cùng `LuuPhan`/`KiemPhan` với lối sửa trên trang đọc. Hộp việc truy mọi trạng thái của `wp_cadao_viec`, có đếm đúng, lọc và phân trang; các thao tác lô gọi cùng dịch vụ quyết việc hoặc lưu phần, kiểm quyền/phiên bản từng mục và ghi kết quả từng mục. Lịch sử cho phép so sánh, khôi phục bằng sự kiện mới. Các đường `/bien-tap/…` giữ tới khi admin đạt tương đương trên điện thoại rồi mới chuyển lối vào.

Việc tạo trang admin **không đòi chuyển dữ liệu** từ bảng việc sang bài WordPress. Nếu cần chỉ mục hoặc bản ghi tiến độ lô, đo trước trên bản thử và bổ sung bằng bước nâng cấp CSDL có thể chạy lại; không thay loại bài, URL, khoá meta hay hồ sơ nguồn chỉ để dựng giao diện. Kiểm thử vai và quyền từng đối tượng, tác phẩm đã sửa đồng thời, chạy lại lô, khách không thấy dữ liệu nội bộ, bộ lọc toàn kho và trang điện thoại nằm trong tiêu chí T-167..T-172.

## 12. Từ mã MVP sang V2

| Tệp MVP (`inc/`) | V2 | Cách |
|---|---|---|
| `doc-cadao-me.php`, `trich.php` | `NhapNguon\CadaoMe` | **chuyển nguyên thuật toán** + kiểm thử |
| `trung.php` | `ChongTrung`, `MienDuLieu\VanTay` | **chuyển nguyên** + kiểm thử |
| `tim.php` | `ChiMucTim`, `MienDuLieu\BoDau` | chuyển thuật toán, dựng chỉ mục từ các phần, thêm gõ sai |
| `do-luong.php`, `luot-doc.php`, `so-lieu.php` | `DoLuong`, `LuotDoc`, trang Số liệu tuần | chuyển; thêm `mong_co_cau` |
| `an-toan.php` | `HaTang\AnToan` | chuyển |
| `anh-chia-se.php` | `AnhChiaSe` | chuyển (cùng phông) |
| `seo.php` | `Seo` | viết lại theo cấp L2 |
| `the-tho.php`; nhận lục bát, tô dị bản, gắn nút chạm, chia đoạn trong `functions.php` của theme | `MienDuLieu\TheTho`, `ToChoKhac`, `ChiaDoan`, khối `loi`, `di-ban`, `doan` | chuyển thuật toán sang PHP thuần |
| `kho.php`, `nguon-tu-dong.php` | `KhoLuu\Kho`, `UngVien`, trang Nguồn & cài đặt | giữ bảng; **bỏ tạo bài Chờ duyệt hằng ngày**, thay bằng thẻ "Câu mới" trong Hộp việc |
| `gop-bai.php` | `GopBai` | viết lại trên các phần, giữ hành vi (dị bản, lượt đọc, cảm nghĩ, phân tích dồn sang, 301) |
| `goi-y.php`, `mo-tip.php`, `wikidata.php` | `GoiY`, `NhapNguon\Wikidata` | chuyển logic gợi ý; mỗi gợi ý là một việc trong `wp_cadao_viec` |
| `dong-dao.php` | phân loại `tro_choi`, `NhapNguon\Wikipedia`, khối `thuc-hanh`, Đọc cùng bé | chuyển |
| `nguoi-doc.php`, `phan-tich.php` | `NguoiDoc` | chuyển (ô bẫy, giới hạn, Turnstile) |
| `doc-tiep.php` | `DocTiep` | chuyển (mô-típ trước, rồi chủ đề, từ, địa danh, vùng) |
| `ban-do.php` | khối `ban-do` | chuyển (SVG tự lưu, không dịch vụ ngoài) |
| `da-luu.php`, `trich-dan.php` | khối `hanh-dong`, trang Đã lưu | chuyển; trích dẫn mang tên mới |
| `tu-dien.php`, `sua-muc-tu.php` | phân loại `muc_tu`, sửa trên trang mục từ | viết lại |
| `loai-bai.php`, `danh-sach.php`, `trang-chu.php`, `muc-kiem.php` | khai báo dữ liệu, khối, mẫu theme | viết lại |
| `ban-bien-tap.php`, `trang-quan-tri.php`, `trang-trung.php`, `hieu-nhanh.php` | `GiaoTiep\BienTap`, `XepViec`, `LuuPhan` | viết lại; giữ lệnh nạp nháp AI theo lô (`wp phongdao nhap-nhap`, nháp thành việc chờ) |
| `khoi-tho.php` | — | **bỏ** (không còn Gutenberg cho tác phẩm) |
| `tuc-ngu-thanh-ngu.php`, `site-cu.php` | `HaTang\ChuyenHuong` | **bỏ**, chỉ giữ bảng chuyển hướng 301 |
| `chinh-sach-du-lieu.php`, `trang-du-an.php` | — | **bỏ** mã sinh trang; nội dung trang tĩnh mang sang (`docs/29`) |

