# 42 · Quản trị làm lại (D-121)

| | |
|---|---|
| Phiên bản | 1.0 · 18/11/2026 |
| Trạng thái | **Đang làm.** Đợt 1 (T-217) xong; đợt 2–4 (T-218..T-220) chờ làm theo thứ tự |
| Quyết định | D-121 — người sáng lập duyệt bản xem trước quản trị và ba điều chốt ngày 18/11/2026 |
| Bản xem trước | Bản tĩnh ngoài kho mã, trên máy người sáng lập: `output/xem-truoc-quan-tri/index.html` (không đưa vào repo) |
| Đọc khi | Làm bất kỳ màn nào trong wp-admin của Phongdao |

## 1. Vì sao làm lại

- Menu lẫn mười mục của WordPress (Bài viết, Trang, Bình luận, Plugin…) với việc của Phongdao; mục Phongdao chữ nhỏ, không có số việc.
- Màn Sửa tác phẩm dài 2.400–4.400 px; mục lục 16 nút; phân loại là sáu ô gập; mỗi phần một nút lưu.
- Bổ sung là một trang dài gộp năm việc khác nhau.
- Nhiều đoạn giải thích dài; nút nào cũng viền giống nhau; trạng thái chỉ là chữ.
- Trên điện thoại: bảng rộng, nút nhỏ, phải cuộn rất xa mới tới nút lưu.
- Màn Hôm nay mở chậm khi kho lớn: đo trên kho thử 16.000 bài mất **84 giây, 32.079 câu SQL** (mục 3.3).

## 2. Nguyên tắc và khung chung (đợt 1)

1. **Một màn — một việc chính — một nút chính** (nút tím đậm); nút phụ viền nhạt, nút ít dùng chỉ là chữ.
2. **Thấy ngay tình trạng:** thẻ màu cho trạng thái (xanh lá đã đăng, xám nháp, vàng cần xem, đỏ có thể trùng); phần còn thiếu là thẻ nét đứt.
3. **Ít chữ:** mỗi màn một câu nói màn làm gì; giải thích dài gập lại.
4. **Tab thay trang dài;** danh sách là hàng đọc được lời, nút ở cuối hàng (đợt 3–4).
5. **Máy chỉ kiểm, nhắc, gợi ý — người quyết:** máy không sửa lời, không sửa nguồn, không tự đăng, không tự bật cờ, không tự gắn chủ đề.
6. **Điện thoại:** thanh dưới bốn việc chính và "Thêm"; nút lưu nổi ở đáy màn sửa (đợt 2).

**Bộ kiểu chung** — `wordpress/v2/plugins/ca-dao/assets/admin-chung.css`:

| Tầng | Lớp | Áp ở | Gồm |
|---|---|---|---|
| Menu | `body.phongdao-menu` | Mọi màn wp-admin của người có quyền biên soạn | Menu trái cùng màu, mục Phongdao có biểu tượng, số việc |
| Khung | `body.pq` | Màn Phongdao (`page=phongdao…`) | Nền, chữ Be Vietnam Pro, cỡ tiêu đề, màu liên kết, nút chính / phụ |
| Thành phần | `pq-*` | Trong màn | `pq-dau` (đầu màn), `pq-the` + `luc` / `vang` / `do` / `xanh` / `tim` (thẻ trạng thái), `pq-thieu` (phần còn thiếu), `pq-khoi` (khối), `pq-luoi ba` / `hai` (lưới), `pq-viec` (ô việc, số to `pq-so-lon`), `pq-gap` (việc gấp), `pq-so-lieu` (ô số), `pq-tien-do` (thanh tiến độ), `pq-ds-phan` / `pq-ds-muc` / `pq-ds-viec` (danh sách), `pq-gon` (khối thu gọn), `pq-duong` (đường dẫn về) |

Màn riêng (`admin-sua.css`, `admin-tac-pham.css`, `admin-viec.css`…) giữ kiểu riêng; đợt sau chuyển dần sang lớp `pq-*` và bỏ kiểu cũ của màn mình chạm.

**Menu** (`GiaoTiep/QuanTri/Module.php`):

- Phongdao: Hôm nay · Bài mới (số câu máy gợi ý hôm nay) · Hộp việc (số việc chờ) · Bổ sung · Tác phẩm · Tuyển tập · Danh mục · Nguồn & cài đặt.
- **Điều chốt 1:** người biên soạn (không có quyền quản trị) không thấy mục gốc WordPress — Bảng tin, Bài viết, Thư viện ảnh, Trang, Bình luận, Giao diện, Plugin, Tài khoản, Hồ sơ, Công cụ, Cài đặt. Mở `/wp-admin/` thì vào Hôm nay. Thanh trên cùng bỏ lối thêm bài viết, trang, ảnh, bình luận; "Thêm mới" mở Thêm tác phẩm. Hồ sơ cá nhân vẫn mở từ tên ở góc trên và từ Tất cả mục.
- Người quản trị thấy các mục đó gom dưới **"WordPress"** cuối menu, mỗi mục gốc một dòng; màn "WordPress" kê cả màn con (Menu, Tuỳ biến, màn cài đặt của plugin khác…).
- Chỉ đổi menu, không đổi quyền: mỗi màn gốc tự kiểm quyền như cũ. Mục của plugin khác giữ nguyên. Người không có quyền biên soạn (vai Cộng tác) giữ menu WordPress như cũ.

**Thanh dưới điện thoại:** Hôm nay · Bài mới · Hộp việc · Bổ sung · **Thêm**. "Thêm" mở màn **Tất cả mục** (`phongdao-muc`): mọi màn Phongdao theo nhóm (Việc hằng ngày, Kho bài, Theo dõi, Thiết lập), Xem site, WordPress với người quản trị.

## 3. Hôm nay và Số liệu (đợt 1)

### 3.1. Hôm nay (`phongdao-ban-lam-viec`, giữ đường dẫn cũ)

1. Đầu màn: "Hôm nay", ngày, số việc đang chờ, nút phụ "＋ Thêm tác phẩm".
2. **Việc gấp** gom một dòng đỏ, chỉ hiện khi có: báo lỗi / vi phạm của người đọc, đường dẫn dùng chung, câu nghi trùng chờ quyết.
3. **Ba việc chính**, mỗi việc một số to, một câu và một nút:
   - Câu mới chờ đăng — số câu máy gợi ý hôm nay (không có thì mọi câu Kho chưa đăng) → "Đăng nhanh →"; câu được đề nghị đăng (người có quyền duyệt).
   - Việc cần quyết — số việc trong Hộp việc, dòng theo loại (cặp có thể trùng, nháp Ý nghĩa…) → "Bắt đầu duyệt →" mở thẻ cần làm trước nhất.
   - Bài cần bổ sung — bài đã đăng chưa có Ý nghĩa → "Bắt đầu bổ sung →".
4. **Tuần này** (7 ngày): bài đã đăng, bài được viết Ý nghĩa (theo nhật ký sửa), nội dung bạn đọc gửi, lượt đọc; thanh "Bài lên Google" (bài có phần thư viện tự viết, D-117) trên số bài đã đăng; lối sang Số liệu.
5. **Đang làm dở:** ba bài (hoặc tuyển tập) người đang xem vừa lưu, mới nhất trước, ghi phần lưu sau cùng; bấm tên để mở lại.
6. **Việc khác trong tuần** (thu gọn): các việc không gấp theo thứ tự ưu tiên cũ (mong có, ý nghĩa bài đọc nhiều, câu tìm chưa có, nháp, gợi ý chủ đề, cần xem, thẻ quá 30 ngày…), lối Số liệu và Lịch sử thao tác hàng loạt.

Không đặt chỉ tiêu điền kín cả kho; không chọn bài hay viết nội dung thay người biên soạn.

### 3.2. Số liệu (`phongdao-so-lieu`)

Các bảng kê trước nằm thu gọn ở cuối Hôm nay, nay ở màn riêng, chỉ tính khi mở: kho theo thể loại và cấp chất lượng, lượt đọc 7 ngày (bài đọc nhiều, người đọc đến từ, câu tìm chưa có, mong có), ngoại lệ dữ liệu, hồ sơ nguồn chưa rõ, Hộp việc vào / ra và tuổi thẻ. Đường dẫn cũ `/bien-tap/so-lieu/` chuyển về đây. Menu tô Hôm nay.

### 3.3. Tốc độ

Đo bằng `npm run do:hom-nay:v2 -- 16000` (Playground SQLite; kho thử 16.000 tác phẩm đã đăng, 8.000 câu kho, 8 hồ sơ nguồn thiếu giấy phép, 40 thẻ Hộp việc; trung vị 3 lần, bộ nhớ đệm xoá trước mỗi lần).

| Phần | Trước (ms · câu SQL) | Sau (ms · câu SQL) |
|---|---:|---:|
| **Cả màn Hôm nay** | **84.204 · 32.079** | **453 · 33** |
| Số liệu theo cấp (nay ở màn Số liệu) | 80.121 · 32.021 | 72 · 12 |
| Ngoại lệ dữ liệu, kèm hồ sơ nguồn chưa rõ (nay ở màn Số liệu) | 1.839 · 28 | 482 · 11 |
| Hồ sơ nguồn chưa rõ | 1.856 · 21 | 263 · 4 |
| Việc nên làm | 142 · 20 | 154 · 19 |
| Ba việc chính | 82 · 9 | 99 · 9 |
| Tuần này (mới) | — | 210 · 9 |
| Đang làm dở (mới) | — | 3 · 1 |
| Cả màn Số liệu (mới) | — | 604 · 40 |

Hôm nay mở nhanh hơn khoảng 180 lần; màn Số liệu (gồm mọi bảng kê) dưới một giây.

Cách làm nhanh:

- Số liệu theo cấp, số bài thiếu Ý nghĩa đọc từ bảng tóm tắt bài bằng một truy vấn gộp (trước: đọc phần của từng bài).
- Bảng kê nặng rời Hôm nay sang màn Số liệu.
- Hồ sơ nguồn chưa rõ: đếm bài dùng của mọi hồ sơ thiếu trong một lượt quét (trước: hai lượt quét cho mỗi hồ sơ).
- Số dùng chung (việc chờ theo loại, câu gợi ý, đề nghị đăng) chỉ hỏi cơ sở dữ liệu một lần mỗi lượt mở.
- Danh sách bài lên Google nạp thông tin mọi bài ứng viên một lần khi tính lại.

## 4. Đợt sau

| Đợt | Việc | Gồm |
|---|---|---|
| 2 | T-218 Sửa tác phẩm | Hai cột, khung "Đăng" luôn bên phải (trạng thái, đăng / ẩn, xem bài); **điều chốt 2:** một nút "Lưu thay đổi" lưu mọi phần đã sửa, Lịch sử vẫn ghi từng phần; phân loại thành chip, **điều chốt 3:** chủ đề máy gợi ý là chip nét đứt, bấm mới gắn; "Bài này còn thiếu" để biết làm gì tiếp; thanh lưu nổi ở đáy trên điện thoại; bỏ mục lục 16 nút và kiểu cũ |
| 3 | T-219 Danh sách | Tác phẩm (tab trạng thái, lọc nhanh, hàng gọn có thẻ trạng thái và phần còn thiếu); Bài mới (tab Gợi ý hôm nay · Để dành · Đề nghị đăng, hàng đọc được lời, nút Đăng cuối hàng, thanh chọn nhiều nổi); Đăng nhanh (chủ đề gợi ý chip nét đứt, phím tắt); Hộp việc (ba lựa chọn viết rõ chữ, so hai bản tô chỗ khác); Tuyển tập (mỗi bộ một hàng, ghi còn thiếu gì để công khai) |
| 4 | T-220 Bổ sung, Thêm | Bổ sung chia năm tab (Thiếu Ý nghĩa, Cần sửa, Gợi ý chủ đề / vùng, Đối chiếu sách, Nháp theo lô), tiến độ lên Google một dòng; Thêm tác phẩm báo trùng ngay dưới ô, một nút Đăng ngay |

Mỗi đợt: một PR có link bản xem trước, `npm run quet:v2` đạt, dọn mã và kiểu cũ của màn mình chạm, cập nhật `docs/02`, `docs/22`.

## 5. Giữ nguyên

Dữ liệu, quyền theo vai, Lịch sử và khôi phục, kiểm trùng, nháp AI chờ duyệt, lời báo khi lưu. Đây là đổi cách trình bày và cách thao tác, không đổi điều máy được phép làm.

## 6. Đã dọn ở đợt 1

- Khung "Phongdao hôm nay" ở trang chào mừng WordPress (người biên soạn vào thẳng Hôm nay; người quản trị mở Bảng tin như WordPress gốc).
- Ba ô cũ, "3 việc gấp nhất", "Nên làm tuần này" của Hôm nay; khối "Thử làm sâu 20–30 bài".
- Bộ nhớ Số liệu tuần 1 giờ (số liệu nay tính nhanh, không cần nhớ).
- Kiểu cũ của Hôm nay và khung trang chào mừng trong `admin-ban.css`.
