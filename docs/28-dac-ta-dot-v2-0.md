# 28 · Đặc tả đợt V2.0 — Nền

| | |
|---|---|
| Phiên bản | 1.0 · 27/10/2026 |
| Trạng thái | **Đã duyệt cùng D-083..D-085, D-088.** Đổi phạm vi đợt phải ghi D-xxx |
| Task | T-141, T-142, T-143, T-144, T-145 (A); T-146 (N, sao lưu); T-147 (N+A, chạy trên site thật) |
| Đọc kèm | `26` (kiến trúc), `24` (mã MVP, máy chủ), AGENTS.md |
| Việc của người sáng lập | ~1 giờ: đọc báo cáo `--thu` (T-143); bật sao lưu + thử khôi phục (T-146); đồng ý cho lần chạy thật (T-147) |

## 1. Mục tiêu

Sau V2.0, mọi bài `cdtn` có **lớp** có cấu trúc trong `wp_cadao_lop`, mỗi mục có hồ sơ nguồn, người làm, trạng thái, lịch sử; chính sách hiện nguồn là cài đặt. **Người đọc không thấy gì khác:** HTML trang bài, trang danh sách, tìm, trích dẫn, ảnh chia sẻ, sitemap, `noindex` y như trước. Đây là nền cho V2.1 (Bàn biên tập) và V2.2 (trang đọc).

## 2. Không làm trong V2.0

- Không đổi giao diện người đọc, theme, menu, đường dẫn.
- Không thêm trang quản trị mới (trừ báo cáo "Cần xem" tối thiểu và mục Cài đặt chính sách hiện).
- Không xoá mã `inc/` nào (chỉ thêm đường ghi song song).
- Không xoá dữ liệu cũ (`post_content`, meta `_cadao_*`, `loai_tach` trong kho).
- Không đổi lời, dị bản của bài nào; không đăng, không đổi trạng thái bài.
- Không chạy `--lam` trên site thật trước T-146 và khi chưa có đồng ý cho lần đó.

## 3. Chi tiết từng task

### T-141 · Khung mã V2

**Việc:**
- `spl_autoload_register` cho `Phongdao\` → `src/`; lớp `Module` với `dang_ky()`; `ca-dao.php` khởi động danh sách module sau khi nạp `inc/`.
- `HaTang\CoTinhNang`: tuỳ chọn `cadao_v2`, cờ `lop_doc`, `ban_bien_tap`, `trinh_soan`, `theme_v2`, `tuyen_tap`; mặc định tắt; hằng số `CADAO_V2_MAC_DINH_BAT` (chỉ đặt trong blueprint Playground / bản xem trước) bật mọi cờ.
- `HaTang\ChuyenDuLieu`: tuỳ chọn `cadao_csdl_phien_ban`, danh sách bước, lệnh `wp cadao chuyen-du-lieu --thu|--lam [--buoc=N] [--lo=200]`; ghi báo cáo vào `wp-content/uploads/cadao-bao-cao/` (không công khai: `.htaccess` chặn + `noindex`) và in ra màn hình.
- Bộ kiểm PHP thuần `wordpress/tests/don-vi/` + tệp chạy nhỏ; `npm run test:wordpress` chạy cả bộ này trước Playground.

**Chấp nhận:** site không đổi khi cờ tắt (kiểm thử HTML trang bài mẫu trước/sau giống từng byte, trừ số phiên bản tệp); bộ kiểm đơn vị chạy < 5 giây không cần WordPress; `npm run kiem-tra`, `test:wordpress`, `quet:wordpress` đạt; plugin tăng phiên bản.

### T-142 · Bảng và hồ sơ nguồn

**Việc:** tạo `wp_cadao_lop`, `wp_cadao_ho_so_nguon`, `wp_cadao_nhat_ky`, `wp_cadao_hang_doi` qua khung chuyển dữ liệu (bước 1); nạp hồ sơ nguồn ban đầu (`docs/26` mục 3.2) với `xin_phep` của `cadao-me` = `chua`, của Wikimedia = `khong_can`, `chinh_sach_hien` theo hiện trạng; nối nguồn tự động hiện có sang hồ sơ (`cadao_nguon…` → `ho_so_nguon_id`). Lớp `KhoLuu` đọc / ghi từng bảng; `DichVu\NhatKy` ghi mọi thay đổi lớp.

**Chấp nhận:** bước chạy lại nhiều lần cho cùng kết quả; SQL chạy được trên SQLite (Playground) và MySQL; không có giá trị nguồn nào được điền mặc định ngoài danh sách trên; kiểm thử khẳng định agent / WP-CLI không đổi được `xin_phep` sang `dong_y`.

### T-143 · Tách lớp

**Việc:**
- **Kho → lớp (bài mới):** đường tạo bài từ kho (`cadao_noi_dung_tu_kho`, `inc/kho.php`) ghi thêm lớp `loi`, `di_ban`, `chu_thich` (`may_chep`, hồ sơ của nguồn), `cau_hoi`, `dap_an` song song với thân bài.
- **Thân bài → lớp (bài cũ), bước 2 của khung:**

| Đọc từ | Thành lớp |
|---|---|
| khối Thơ đầu tiên | `loi` — so từng ký tự với `cadao_loi_tu_html_tho` |
| khối Thơ sau tiêu đề "Dị bản" + `_cadao_di_ban_them` | `di_ban` |
| danh sách `cadao-chu-thich cadao-chep` | `chu_thich`, `may_chep`, hồ sơ theo `_cadao_nguon_id` |
| `_cadao_chu_thich_them` | `chu_thich`, `nguoi_viet` |
| khối Chi tiết "Đáp án" + câu hỏi in đậm | `dap_an`, `cau_hoi` |
| `post_excerpt` | `hieu_nhanh` (`_cadao_ho_tro_ai = 1` → `ai_nhap_da_duyet`, ngược lại `nguoi_viet`), trạng thái `da_duyet` vì đã lên trang |
| hộp Nguồn của chú thích (`nguon_ten`, `nguon_url`, `ngay_truy_cap`) | `vi_tri_nguon` của các lớp `chu_thich` do người viết |
| `doi_chieu_*` | `vi_tri_nguon` của lớp `loi` |
| `_cadao_url_goc`, URL của câu đã gộp | `vi_tri_nguon` nội bộ của lớp `may_chep` |
| khối Thơ tách dòng sai, HTML lạ, bài đã sửa tay lệch `_cadao_bam` mà không nhận ra phần | giữ nguyên, **không tạo lớp**, ghi vào hàng "Cần xem" kèm lý do |

- Báo cáo `--thu`: tổng số bài; số tách trọn; số "Cần xem" theo lý do; 20 ví dụ đầu (mã bài, lý do), **không chép lời vào báo cáo trong repo**.
- **Kiểm "trước = sau":** với mọi bài tách trọn, lời dựng từ lớp `loi` khớp từng ký tự lời đọc từ thân bài cũ; số dị bản, số chú thích, đáp án, Hiểu nhanh khớp. Bộ kiểm chạy trên bộ bài mẫu của Playground và một bản xuất nội dung đã công khai (lấy qua REST công khai, chỉ trong CI / máy agent, không commit lời vào repo ngoài các bài mẫu đang có trong kiểm thử).

**Chấp nhận:** `--thu` không ghi gì; lớp `loi`, `di_ban` không bao giờ có `nguoi_lam` AI; không lớp nào có `vi_tri_nguon` mà dữ liệu cũ không có; "trước = sau" đạt 100% bài tách trọn; báo cáo nêu rõ số "Cần xem".

### T-144 · Ghi song song và thân bài sinh lại

**Việc:** mọi đường ghi hiện có ghi thêm lớp: tạo bài từ kho, nhận dị bản / chú thích mới từ kho, gộp bài (`gop-bai.php`), tự gắn dị bản (T-123), Hiểu nhanh (ô Tóm tắt, `wp cadao hieu-nhanh-nhap`), hộp nguồn, hộp "Lời đã đối chiếu với", cách chơi. `DichVu\DungTrang` sinh thân bài từ lớp — **chỉ khi cờ `lop_doc` bật** (trên site thật vẫn dùng thân bài cũ). Việc đêm: so thân bài với lớp; lệch → "Cần xem", không tự sửa.

**Chấp nhận:** với cờ tắt, mọi kiểm thử hiện có đạt không đổi; với cờ bật trong Playground, thân bài sinh lại cho HTML trang bài giống bản cũ (so sau khi chuẩn hoá khoảng trắng) trên mọi bài mẫu; mỗi lần ghi có dòng nhật ký (ai, lúc nào, trước, sau).

### T-145 · Chính sách hiện nguồn thành cài đặt

**Việc:** `MienDuLieu\ChinhSachHien` đọc cột `chinh_sach_hien` của hồ sơ nguồn + ngoại lệ "nguồn người biên soạn điền thì hiện" (D-081); thay logic rải ở `site-cu.php`, `muc-kiem.php`, `loai-bai.php` bằng một chỗ hỏi; mục Cài đặt (PHP tạm, trước khi có Bàn biên tập React) liệt kê từng hồ sơ nguồn với ba lựa chọn và dòng nhắc "Đổi ở đây là đổi chính sách — cần ghi quyết định".

**Chấp nhận:** mặc định cho HTML người đọc **giống từng byte** hiện trạng D-068 + D-078 + D-081 trên các ca kiểm thử: bài chép từ Ca dao Mẹ không có nguồn công khai (hiện "[N nguồn tham khảo đã ẩn]"); bài có nguồn người biên soạn điền (không còn dòng "đã ẩn"); người đăng nhập thấy liên kết; ghi công Commons / Wikipedia / Wiktionary tại chỗ; liên kết nguồn ẩn không có trong HTML gửi người đọc.

### T-146 · Sao lưu và thử khôi phục — người sáng lập

**Việc:** FlashPanel → Sao Lưu: hằng ngày, gồm site và cơ sở dữ liệu `cadao`, lưu ra ngoài VPS; thử khôi phục một lần (vào một thư mục / cơ sở dữ liệu thử, hoặc theo hướng dẫn agent soạn). Báo agent ngày bật và ngày thử.

**Chấp nhận:** sao lưu chạy ≥ 2 ngày liền; một lần khôi phục thử thành công; ghi ngày vào `docs/02`. Đây cũng là điều kiện mở công khai (D-073, D-077).

### T-147 · Chuyển dữ liệu trên site thật

**Việc:** (1) khi người sáng lập đồng ý, chạy `--thu` trên site thật (chỉ đọc) và trên bản sao cơ sở dữ liệu (nếu người sáng lập cung cấp); gửi báo cáo; (2) người sáng lập đọc báo cáo, quyết các bài "Cần xem" (hoặc để sau); (3) khi được đồng ý cho lần đó, chạy `--lam` theo lô; (4) bật cờ `lop_doc` (người sáng lập bấm, hoặc agent bằng WP-CLI khi được đồng ý); (5) `kiem-site-that` + `e2e` đạt.

**Chấp nhận:** "trước = sau" đạt trên site thật; E2E 0 hỏng ngoài dự kiến; số bài, số lượt đọc, đường dẫn không đổi; có đường quay lại (tắt cờ `lop_doc` là trang về thân bài cũ, vì thân bài cũ không bị xoá).

## 4. Thứ tự và PR

Xếp chồng theo D-053, một PR mỗi task (hoặc gom T-141 + T-142 nếu nhỏ, D-066): T-141 → T-142 → T-143 → T-144 → T-145. T-146 làm song song bất cứ lúc nào. T-147 sau T-145 và T-146. Mỗi PR: tăng phiên bản plugin; kèm link bản xem trước; mô tả đã làm / không làm / câu hỏi còn lại.

## 5. Rủi ro của đợt

| Rủi ro | Cách giảm |
|---|---|
| Bài sửa tay không tách được | Hàng "Cần xem", không đoán; thân bài cũ vẫn phục vụ |
| Máy chủ tự cập nhật đưa mã lên giữa đợt | Cờ tắt trên site thật; mỗi PR giữ HTML người đọc như cũ |
| Bảng mới chạy khác trên MySQL | Tránh SQL chỉ MySQL; chạy thử trên bản sao khi được đồng ý |
| Chép lời từ site thật vào repo khi làm kiểm thử | Bản xuất chỉ nằm trong CI / máy agent; repo chỉ giữ bài mẫu đã có (AGENTS.md, KHÔNG BAO GIỜ 14) |
| Đổi chính sách nguồn ngoài ý muốn | Kiểm thử "giống từng byte" ở T-145; đổi cài đặt cần D-xxx |
