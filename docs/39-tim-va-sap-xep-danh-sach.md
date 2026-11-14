# Tìm đúng chữ và sắp xếp danh sách — D-112

Người sáng lập duyệt ngày 14/11/2026; gom trong một đợt T-203, plugin 1.0.62. Mã và nghiệm thu ở [PR #176](https://github.com/truonganhquyen/ca-dao/pull/176); chỉ gộp khi CI của head cuối đạt.

## Hành vi đã chốt

- Bấm Tìm hoặc mở URL: so trọn từng âm tiết, kể cả âm tiết cuối. Gõ có dấu: bài khớp đúng dấu trong phạm vi chính; bài khác dấu và bài chỉ khớp đầu chữ có phạm vi mở rộng riêng, kèm số. Không dấu vẫn tìm được các cách viết có dấu. Dấu ngoặc kép yêu cầu cụm liền.
- Khi đang gõ: tiền tố cuối giúp tìm gõ dở, có lời báo đây là gợi ý; gửi form xác nhận tìm trọn chữ. Không ghi thêm sự kiện đo lường.
- Giữ thứ tự liên quan xuyên thể loại; bộ lọc đếm toàn tập. Mỗi trang 20 tác phẩm, xem được đến trang cuối bằng liên kết GET; giữ truy vấn, kiểu khớp, thể loại. Tham số trang ngoài giới hạn được đưa về trang cuối.
- Một từ không có thẻ nổi bật. Chỉ nổi bật khi truy vấn nhiều từ khớp trọn tên hoặc một dòng lời của đúng một bài trong toàn tập đang xét và bài đó đứng đầu. Nếu chưa thể xác định duy nhất thì giữ danh sách bình thường.
- Thẻ trích từ đúng phần đã lưu, giữ chữ và đánh dấu trọn từ. Lý do: tên, lời, dị bản hoặc giải nghĩa/chú thích. Nội dung bị che theo D-111 vẫn nằm trong khối đóng.
- Danh sách tác phẩm mặc định Có giải nghĩa trước: (2) có phần giúp hiểu (ý nghĩa, nghĩa, hình ảnh, bối cảnh, lời giải nguồn; câu đố cần đáp án và giải thích); (1) có chú thích, hoặc câu đố chỉ có đáp án; (0) còn lại. Đây là độ đầy đủ phục vụ đọc, không phải xác nhận sự thật hay quyền nguồn.
- Trong nhóm: ngày đăng giảm dần, ID giảm dần. Đọc nhiều dùng số 7 ngày hiện có; A–Z dùng tên rồi ID. Bốn lựa chọn xuất hiện trên Kho và các trang phân loại tác phẩm. Trang Từ điển và thứ tự trong tuyển tập giữ nguyên.

## Kỹ thuật và lằn ranh

Giữ chỉ mục tìm và bảng tóm tắt hiện tại. CSDL bước 14 chỉ thêm cột dẫn xuất `uu_tien_doc` vào bảng tóm tắt, không thêm trường nội dung, không đổi bài. Bài mới/sửa phần cập nhật cột ngay theo đường tính tóm tắt sẵn có; bài cũ dựng lại theo lô khi kho lớn. Trong lúc dựng, bài chưa tính có ưu tiên 0 và vẫn tìm/đọc được. Có thể dựng lại bản tính sẵn từ phần bài, không mất quyết định biên tập.

Tìm lấy cờ của mọi ứng viên, chỉ đọc chữ để tính khoảng gần nhau khi còn cần; ưu tiên nội dung chỉ phân hạng điểm liên quan bằng nhau. Thẻ trên trang được nạp bộ nhớ đệm theo lô. Không quét meta từng bài để xếp danh sách mỗi lần mở trang.

Không thêm cookie, thu thập hay dịch vụ tìm bên ngoài; không sửa nguồn/lời/dị bản, không đăng/duyệt thay người, không đổi lập chỉ mục hay đường dẫn tác phẩm. Bộ đếm gợi ý và hiệu năng Hôm nay nằm ngoài đợt này.

## Nghiệm thu

- Ca từ một âm tiết: trọn từ, khác dấu, đầu từ không lẫn phạm vi; NFC và không dấu; cụm nhiều từ, gõ dở, dấu ngoặc kép, truy vấn lạ và HTML lạ.
- Nhiều bài khớp: không giả định một bài đại diện; bài khớp chính xác đứng trước bài mở rộng; giải nghĩa không lấn mức khớp; thứ tự xuyên thể loại.
- Trên ít nhất 36 tác phẩm thử: đếm đủ, trang không lặp, trang cuối và lọc thể loại, số liệu không lấy từ trang đầu. Bài nháp/ẩn và dữ liệu kho riêng không ra kết quả.
- Danh sách: đủ bốn lựa chọn, mặc định đúng ba nhóm, ngày/ID ổn định, thứ tự lượt đọc 7 ngày, lọc/phân trang giữ lựa chọn; dùng được bằng bàn phím và tắt JavaScript.
- Kiểm HTML trích/đánh dấu không đưa HTML nguồn vào trang, không làm lộ nội dung bị che D-111.
- Bốn cổng bắt buộc và đo tìm trên kho tổng hợp; ghi rõ kết quả thực tế, tách khỏi xác nhận biên tập. Bộ truy vấn nội dung thật tiếp tục do người biên soạn đánh giá, không dùng AI tự chấm sự thật văn hoá.

## Công cụ kiểm và phép đo

Phép đo `do:tim:v2` nay kiểm cả đường tìm người đọc mới trên tác phẩm tổng hợp thật trong CSDL thử (đúng tổng, thứ tự 20 bài đầu và trang cuối ở 1.000/5.000/10.000 bài). Bản `tests/do-tim/mysql.php` dùng CSDL riêng để đo, tạo hai bảng tạm cho bài/tóm tắt rồi huỷ theo kết nối; không trỏ vào site thật.

Mỗi lượt Playground có thư mục tạm riêng qua `tamPlayground`. Cách dọn cũ liệt kê mọi thư mục vừa xuất hiện trong TEMP có thể đụng site của lượt khác chạy đồng thời; cách mới chỉ dọn đường dẫn do chính lượt đó tạo và kiểm nằm dưới thư mục tạm. Cổng `test:v2` kiểm việc dọn lượt A không làm mất fixture của lượt B. Không thay ngưỡng tốc độ hay hạ kiểm thử.

Kết quả đo đường mới: **21/21 truy vấn đúng tổng, thứ tự trang đầu và trang cuối** ở cả MySQL 8.4.3 và Playground, trên ba cỡ kho. [Bảng MySQL](e2e-review/tim-va-danh-sach/do-tim-mysql.md), [bảng Playground](e2e-review/tim-va-danh-sach/do-tim-playground.md). Đây là kho tổng hợp, không chứng minh chất lượng xếp hạng nội dung thật; các quy tắc đúng từ/dấu, thẻ nổi bật, trích và lọc có kiểm hồi quy riêng.

Bộ E2E dùng Locator để chờ nút ổn định rồi bấm chuột, thay cách lấy toạ độ ngay sau scroll. Lượt CI đầu có hai lần bấm nút Lô không sang trang (ảnh vẫn ở form trước gửi); giữ nguyên các kiểm kết quả, giới hạn chờ và toàn bộ bước, gồm thao tác hàng loạt.

## Kết quả nghiệm thu 14/11/2026

- `kiem-tra`: 165 task, 1 câu hỏi mở, 0 lỗi.
- `test:v2`: 273 ca PHP thuần; 624 ca WordPress Playground và 55 ca chuyển đổi đạt. Tại máy dùng Node 24.18/PHP 8.4.12; không bỏ qua phần cần công cụ.
- `quet:v2`: 0 lỗi; 42 trang × 3 chế độ, 15 trang × 2 chế độ 320 px, các màn biên tập/admin và trang sửa 36 mục. JavaScript trang bài 9,6 KB, trang Tìm 10,5 KB, dưới 21 KB.
- `e2e:v2`: 67/67 bước, 0 lỗi, 0 bỏ qua trong [lượt CI đầy đủ](https://github.com/truonganhquyen/ca-dao/actions/runs/43577768693). Các lượt lỗi/hủy trước đó không tính là đạt. CI của head cuối tiếp tục là điều kiện gộp PR.
- Xem trực tiếp bản thử: tìm một từ không nổi bật; cụm trọn tiêu đề duy nhất có thẻ nổi bật; chữ khớp giữ nguyên và có đánh dấu; bộ chọn thứ tự ở trang thể loại đúng mặc định. Kiểm màn 320/390 px và máy tính, không tràn ngang.
- Dữ liệu tổng hợp kiểm đúng hành vi kỹ thuật; chưa thay thế đánh giá truy vấn nội dung thật của người biên soạn. Chưa coi các kết quả này là xác nhận phiên bản đang chạy trên site trước khi gộp.

Pilot nội dung thật vẫn ở T-198/H2; đợt này không xác nhận hay công khai thêm nội dung.
