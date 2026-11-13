# Editor đơn giản và sửa URL tác phẩm

Ngày 13/11/2026, người sáng lập tạm hoãn đề xuất mở rộng nền nội dung và yêu cầu làm trước hai phần: editor đơn giản, sửa URL tác phẩm (D-110). Tài liệu này ghi phạm vi thực hiện; kết quả kiểm cập nhật sau khi chạy thực tế.

## Phạm vi

Giữ trang Sửa tác phẩm hiện tại và cơ chế lưu từng phần. Dùng editor sẵn có trong WordPress, không chuyển sang màn Gutenberg toàn bài hoặc thêm plugin editor.

| Phần | Nghiệm thu |
|---|---|
| Editor | Đậm, nghiêng, gạch chân, chèn/bỏ liên kết, màu đỏ/xanh/tím và chữ nhẹ, bỏ định dạng, hoàn tác/làm lại |
| Vùng nhập | Ý nghĩa/giải thích đáp án, Nghĩa đen, Nghĩa bóng, Đáp án, Gợi ý, lời giới thiệu Bối cảnh, lời giải từng từ khó |
| Dữ liệu | Chữ thuần giữ trường cũ; HTML tuỳ chọn nằm ở `dinh_dang` trong cùng phần/mục; kiểm chữ khớp nhau, lọc thẻ/link/lớp màu được phép; không nhận script, ảnh/nhúng hoặc CSS tuỳ ý |
| Lưu | Cùng form, quyền, dấu phiên và lịch sử; nháp Ý nghĩa không tự hiện; duyệt nháp giữ định dạng khi chữ không đổi; khôi phục lấy lại cả hai |
| Không JavaScript | Sửa và lưu chữ thuần được; sửa chữ sẽ bỏ định dạng cũ để tránh hiện sai nội dung. Sửa nhanh trang đọc theo cùng quy tắc |
| Hiệu năng | Chỉ nạp editor trong wp-admin Sửa tác phẩm; khởi tạo vùng đang mở, lời giải từng mục khi tập trung vào ô, không bung sẵn hàng chục editor |
| URL | Mục Đường dẫn trong mục lục; URL đang dùng, ô sửa tên cuối, dự kiến URL mới; phần thể loại dùng thao tác Đổi thể loại hiện có |
| Quyết định URL | Quản trị có quyền sửa bài, nonce đúng, dấu phiên đúng và đánh dấu xác nhận mới lưu; không đổi tên/lời/trạng thái/ID |
| URL trùng/cũ | Chặn tên đang dùng, tên cũ thuộc bài khác, tên dành riêng và URL đầy đủ; bài đang dùng chung đường dẫn phải qua thao tác tách hiện có |
| Chuyển hướng | Giữ đường đầy đủ trước mỗi lần đổi và trỏ tới ID hiện tại bằng 301 khi đường cũ không còn tồn tại; không ghi đè trang đang dùng, không chuyển khách tới bài nháp/ẩn |
| Lỗi và xung đột | Không ghi đè bản mới; giữ đầu vào lỗi, hiển thị URL đang lưu để đối chiếu; lỗi ghi tên/chuyển hướng/lịch sử phải rollback cùng giao dịch |
| Trợ năng | Kiểm bàn phím, điện thoại, tương phản màu sáng/tối, không tràn ngang; no-JS vẫn lưu được |

Lời và dị bản vẫn là văn bản theo dòng. Dùng khi và Hình ảnh trong bài vẫn theo cấu trúc từng dòng/cặp hiện có. Chức năng định dạng không thay nội dung văn hoá, người làm hoặc nguồn bằng suy đoán.

Không làm trong đợt này: chuyển màn biên soạn sang WordPress gốc, SEO plugin, loại nội dung mới, sách/chương/tác giả, chọn tác phẩm làm dị bản hoặc bảng quan hệ. Bốn ngoại lệ nguồn/lời/phục hồi trước đây và quyền mở Google giữ nguyên.

## Cách dùng

1. Mở Sửa đầy đủ, mở phần diễn giải cần sửa. Với từ khó, mở mục rồi đặt con trỏ vào Lời giải.
2. Bôi chọn chữ và dùng thanh công cụ. Chữ nhẹ dùng màu của giao diện, vẫn bảo đảm dễ đọc. Lưu riêng phần đang sửa; các phần khác chưa lưu vẫn được giữ theo tab.
3. Muốn đổi URL, mở Đường dẫn tác phẩm từ mục lục. Nhập tên cuối đường dẫn, xem dự kiến, đánh dấu xác nhận rồi Lưu đường dẫn.
4. Nếu báo bản đã đổi, đối chiếu URL đang lưu với tên đang nhập; chỉ sau khi đã đối chiếu mới nhận mốc mới để lưu tiếp.

## Kiểm thử và kết quả

Mở rộng chính `test:v2` (PHP thuần + WordPress), `quet:v2`, `e2e:v2`; chạy thêm `kiem-tra`. Ghi/lỗi/khôi phục và URL thật chỉ thử trong WordPress mẫu bằng chữ kỹ thuật. Phiên bản sản phẩm dự kiến: plugin 1.0.60, theme 1.0.8.

Chưa có kết quả cuối; chưa phát hành và chưa thay đổi nội dung/URL tác phẩm thật trong lượt này.
