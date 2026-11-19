# Nhập nguồn mới vào Kho

Làm tại **Bài mới → Nhập tệp**. Nhập chỉ đưa tư liệu vào Kho; bạn chọn đăng ở bước sau.

1. Chọn **Hồ sơ nguồn** đã có; nếu chưa có, mở **Quản lý hồ sơ nguồn** để thêm. Không rõ thì giữ **Chưa rõ nguồn**.
2. Chọn tệp TXT/CSV UTF-8, tối đa 1 MB và 400 mục; hoặc mở **Hoặc dán chữ**. TXT dùng cho danh sách đơn giản: các mục cách nhau bằng dòng trống. Tệp trộn thể loại hoặc có dòng trống bên trong bài nên dùng **Tải mẫu CSV**.
3. Bấm **Xem trước nhập**. Đọc cách tách và dấu hiệu trùng, chọn thể loại chung hoặc từng mục, bỏ chọn mục chưa muốn đưa vào. Thiếu thể loại thì chọn lại tại đây; phần lựa chọn được giữ.
4. Bấm **Đưa các mục đã chọn vào Kho**. Máy làm mỗi lượt 25 mục. Có thể dừng và mở lại để tiếp tục; khi tắt JavaScript, bấm **Tiếp tục nhập** từng lượt.
5. Nhập xong, **Bài mới** lọc đúng lần nhập đó. **Xem để đăng / Tiếp tục đăng** mở thẻ đọc lại trước khi đăng; chọn nhiều mục để **Xem trước và đăng**. Mục nghi trùng có **Xem đối chiếu**; quyết xong trở về lần nhập.
6. Dòng **Hiện tại** cho biết đã đăng, chưa đăng, có lời khớp. **Biên nhận và kết quả từng mục** giữ kết quả lúc nhập; **Các lần nhập gần đây** mở lại công việc. Mục lỗi có nút xem trước và thử lại riêng; mục bỏ chọn có lối xem lại. Không chạy lại phần đã thành công.

CSV dùng dấu phẩy, với tiêu đề cột:

```text
ma_muc,loi,the_loai,trang,url
```

Chỉ `loi` bắt buộc trong tệp. Ô lời nhiều dòng đặt trong dấu ngoặc kép; dấu ngoặc kép trong lời ghi hai lần. `the_loai` nhận tên trong bộ chọn hoặc mã `ca_dao`, `thanh_ngu_tuc_ngu`, `dong_dao`, `ve`, `cau_do`. Có thể để trống thể loại rồi chọn ở bước xem trước. Một lần nhập dùng một hồ sơ nguồn. Mã mục, trang và URL chỉ điền khi có trong tư liệu; lời máy trích từ web cần URL từng mục, chọn cách nhận lời ở **Cách ghi tệp và nguồn gốc lời**.

Nhập lại cùng dữ liệu mở lần nhập cũ; đổi tên tệp không tạo thêm mục. Cùng lời từ hai hồ sơ vẫn giữ hai chứng cứ. Có lời khớp bài cũ thì chứng cứ được giữ trong Kho, không tự sửa bài công khai. Mã mục đã có nhưng lời/thể loại/vị trí thay đổi báo xung đột và giữ mục cũ; kiểm lại tư liệu trước khi nhập tiếp.

Chủ đề và vùng vẫn chọn ở Đăng nhanh/Bổ sung; không cần điền Ý nghĩa để đăng. [Hướng dẫn đăng nhiều câu](dang-hang-loat.md).
