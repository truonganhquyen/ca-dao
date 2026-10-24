# Hướng dẫn · Biên soạn trong CMS (một trang)

| | |
|---|---|
| Cho ai | Người sáng lập — không cần Git, YAML hay dòng lệnh |
| Mở ở đâu | **https://ca-dao.truonganhquyen.workers.dev/admin/** — trên điện thoại hay máy tính đều được. Nên "Thêm vào màn hình chính" |
| Lưu là gì | Mỗi lần bấm **Lưu**, CMS ghi thẳng vào repo `ca-dao` (nhánh `main`); vài phút sau trang tự cập nhật |
| Quy trình đầy đủ | `docs/11-huong-dan-bien-soan.md` (chọn bài, viết nghĩa, chú giải, quy tắc AI) |

## Đăng nhập

Bấm **Đăng nhập bằng GitHub** (Sign in with GitHub). Lần đầu GitHub hỏi cho phép ứng dụng `Kho tàng Ca dao – CMS` → **Authorize**. Dùng máy lạ thì đăng xuất khi xong (ảnh đại diện góc trên → Đăng xuất).

## Thêm nhanh một bài (1–2 phút)

Nghe hay đọc được một câu mà kho chưa có:

1. Mở bộ **Thêm nhanh (bài nháp)** → **Tạo bài viết mới**.
2. Điền **Tiêu đề** (thường là dòng đầu), chọn **Thể loại**.
3. **Lời**: mỗi dòng thơ một dòng, gõ hoặc dán **đúng như nguồn**. Chọn **Vùng** (chưa biết: để "Chưa rõ vùng").
4. **Lời lấy từ đâu** → **Thêm**: chọn nguồn (mặc định Ca dao Mẹ), dán **địa chỉ của đúng trang** đã lấy; ngày truy cập tự điền hôm nay. Nghe người kể thì bỏ qua nguồn, ghi vào **Người kể** (không ghi tên thật).
5. **Mã bài**: tiền tố theo thể loại + 4 chữ số chưa dùng (`cd-0004`, `tn-0002`…). Xem số đã dùng trong bộ **Tác phẩm**. **Đường dẫn**: chữ không dấu nối gạch.
6. **Lưu**. Bài ở trạng thái **Nháp**, chưa lên trang. Làm giàu sau trong bộ **Tác phẩm**.

## Làm giàu và công khai một bài (10–15 phút)

Mở bài trong bộ **Tác phẩm**. Các phần theo thứ tự: **Lời và nguồn → Nghĩa → Chú giải → Tình huống → Duyệt và trạng thái → Nâng cao**. Dưới mỗi ô có gợi ý.

- **Không sửa lời** khi chưa có nguồn khác; nghi sai chữ thì ghi vào "Ghi chú về lời".
- **Nghĩa, "Dùng khi nào"** do AI soạn nháp bắt đầu bằng `NHAP_AI`: đọc, sửa câu chưa ổn, xóa câu không có căn cứ. Không cần xóa chữ `NHAP_AI` — trang tự bỏ khi bạn duyệt.
- **Chú giải từ khó**: gõ **Cụm từ** đúng như trong lời, chọn **Mục từ** trong Từ điển (chưa có thì tạo trong bộ **Từ điển** trước, giải nghĩa bằng lời của mình).
- Xong: đánh dấu **"Tôi đã đọc và đồng ý mọi phần do AI soạn"**, đổi **Trạng thái** sang **Công khai**, **Lưu**. Vài phút sau, mở trang của bài trên điện thoại để xem.

Bài **chưa có nguồn** (lời không ghi lấy từ đâu) thì **không công khai được** — máy chặn.

## Thêm bài vào một tình huống

Quan hệ bài–tình huống chỉ lưu ở **tình huống** (không ở bài). Mở bộ **Tình huống** → chọn tình huống (hoặc **Tạo mới**) → **Bài trong tình huống** → **Thêm** → chọn **Bài** và viết một câu **"Hợp khi…"** → **Lưu**. Bài có **cảnh báo nội dung** hoặc **cờ nghi không phải dân gian** không được đưa vào tình huống (máy chặn). Trang tình huống chỉ hiện khi có từ 2 bài công khai.

## Khi lưu mà có lỗi

Máy kiểm mọi lần lưu. Có **lỗi** thì trang **không** cập nhật — **bản cũ vẫn chạy, không mất gì**:

- GitHub gửi email "Kiểm tra repo … failed". Mở email → **View workflow run** → bấm vào bước **Chạy cổng kiểm tra**: dòng bắt đầu bằng **`LỖI`** ghi tên tệp và điều cần sửa (ví dụ thiếu nguồn, mã trùng).
- Sửa lại trong CMS rồi **Lưu**. Không rõ thì chụp màn hình dòng `LỖI` gửi cho agent.
- (Mốc B) Bàn biên tập `/ban-bien-tap` sẽ liệt kê lỗi và việc cần làm, không cần mở email.

Dòng **`NHẮC`** không chặn: chỉ là phần nên bổ sung dần.

## Những việc hỏi agent trước

- **Xóa, gộp hay đổi mã** một bài, nguồn, mục từ đã có — muốn gỡ một bài khỏi trang ngay thì đổi Trạng thái sang **Ẩn**.
- Đổi **Bản quyền** của một nguồn sang "Được phép" (cần văn bản đồng ý), hoặc dùng "Trích nguyên văn" cho chú thích.
- Đánh dấu **"Đã đối chiếu thông tin trên bản in"** chỉ khi chính bạn đã xem bản in hoặc bản quét.
