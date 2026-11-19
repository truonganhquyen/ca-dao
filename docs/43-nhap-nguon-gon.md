# Nhập nguồn gọn trong Bài mới

Quyết định D-122, người sáng lập yêu cầu triển khai ngày 19/11/2026. Trạng thái triển khai theo T-222/T-223 trong `docs/22`. Giữ Bài mới · Hộp việc · Bổ sung; không thêm menu hay bảng dữ liệu.

## Luồng vận hành

1. **Bài mới → Nhập tệp**: chọn hồ sơ nguồn có sẵn hoặc Chưa rõ, tải TXT/CSV hay dán chữ. Mỗi lần tối đa 400 mục, tệp UTF-8 ≤ 1 MB. Tệp không vào thư viện media công khai.
2. **Xem trước nhập**: kiểm lời và cách tách, chọn thể loại theo mục hoặc áp dụng một loại chung, bỏ chọn ngoại lệ. Xem trước chỉ có bản chuẩn bị riêng tư trong bảng việc. Thiếu lời/thể loại thì giữ bản xem trước và lựa chọn để sửa.
3. **Đưa các mục đã chọn vào Kho**: mỗi lượt 25 mục, tự tiếp tục bằng JavaScript đang có hoặc bấm Tiếp tục khi tắt JavaScript. Khi xong mở lại Bài mới lọc đúng lần nhập; lịch sử thu gọn ở cùng màn.
4. **Xem để đăng / Tiếp tục đăng** hoặc chọn nhiều để xem trước và đăng qua đường hiện có. Nghi trùng mở đúng thẻ Hộp việc, quyết xong trở về lần nhập. Phần người đã sửa và các quyết định cũ được giữ.

## Định dạng

TXT: các mục cách nhau một dòng trống, mỗi dòng trong mục giữ nguyên. TXT không phân biệt được ngắt khổ với ranh giới tác phẩm; dùng CSV khi cần giữ dòng trống bên trong một bài.

CSV theo mẫu tải ngay tại biểu mẫu: `ma_muc,loi,the_loai,trang,url`. Chỉ `loi` bắt buộc trong tệp; thể loại có thể chọn ở bước xem trước. Ô lời nhiều dòng đặt trong dấu ngoặc kép. Thể loại nhận tên trong danh sách hoặc mã `ca_dao`, `thanh_ngu_tuc_ngu`, `dong_dao`, `ve`, `cau_do`. Mã mục, trang, URL chỉ ghi nếu có trong tư liệu; không suy đoán. Một hồ sơ nguồn cho một lần nhập.

Cách nhận lời phân biệt người cung cấp gõ/chép với máy trích từ trang nguồn; lời máy trích cần URL từng mục. Không nhận người làm là AI cho lời, không tự thêm dấu duyệt hay quyền dùng chú thích. Chủ đề/vùng vẫn là gợi ý người chọn trong Đăng nhanh/Bổ sung hiện có; không bắt điền Ý nghĩa trước khi đăng.

## Bảo toàn dữ liệu

- Nhận diện mục nhập xét hồ sơ, mã/vị trí nguồn và lời nguyên văn. Cùng lời ở hai hồ sơ vẫn có hai chứng cứ; vân tay chuẩn hoá chỉ dùng tìm trùng.
- Nạp lại cùng tệp/lựa chọn mở biên nhận cũ, đổi tên tệp không tạo lần nhập khác. Mục có cùng nhận diện nhưng lời/thể loại/vị trí đã đổi báo xung đột, không cập nhật đè. Mục kiểu cũ chỉ tái dùng khi lời và hồ sơ thật sự khớp, không chuyển khoá hoặc sửa lại dữ liệu cũ.
- Luồng nhập tệp mới chỉ ghi Kho và quan hệ: lời khớp có thể gắn với bài đã có nhưng không đồng bộ phần công khai. Khác dấu/chữ để người đối chiếu. Các chứng cứ chưa được người quyết cũng không vào bài khi nguồn máy đồng bộ về sau.
- Người quyết gộp dị bản qua thẻ mới cho phép bổ sung đúng phần đó, giữ hồ sơ/trang/URL/người làm từng mục và liên kết mục từ của chú thích không đổi. Phần người đã sửa được giữ; chỉ nối mục mới phù hợp.
- Dữ liệu mỗi lượt nhập và biên nhận chốt trong cùng giao dịch. Mỗi mục có savepoint để lỗi ghi có thể phục hồi không làm mất kết quả các mục khác; nếu giao dịch bị ngắt thì dừng cả lượt. Lỗi chốt biên nhận huỷ cả lượt đang làm. Phiên bảo vệ giữa các tab; thử lại là bản xem trước con chỉ chứa mục lỗi, giữ biên nhận cũ. Mục đã bỏ chọn cũng có thể mở lại qua bản xem trước con.

## Kết quả và làm tiếp

Kết quả nhập tách mới vào Kho, có từ trước, có lời khớp, cần đối chiếu, lỗi, xung đột và bỏ qua; nhắc rõ lượt nhập chưa đăng bài. Kết quả đăng ghi số bài thực sự đăng được trên tổng số mục của lô, kèm lỗi/xung đột/bỏ qua. Chi tiết từng mục có lối mở lại.

Đăng theo lô ở Kho dùng lại thẻ Câu mới đang mở như Đăng nhanh. Bản xem trước mới giữ dấu của lời/nguồn/dữ liệu, không chỉ trạng thái; dữ liệu đổi thì báo xung đột. Khi có lỗi đăng, tạo lô con từ đúng các mục lỗi qua xem trước mới, kiểm lại trạng thái/quyền/phiên và giữ kết quả gốc. Mục đã xử lý không được đăng lặp.

## Phạm vi nghiệm thu

Kiểm hồi quy WordPress bằng dữ liệu kỹ thuật: CSV nhiều dòng/thể loại, xem trước không ghi Kho/bài/thẻ, nhập lại/khác nguồn, lỗi từng mục/biên nhận, thử lại, lô tái dùng thẻ, dấu xem trước, gộp giữ nguồn và mục từ. E2E đi qua form nhập/xem trước/lọc/đăng và tắt JavaScript. Quét trợ năng màn nhập mở, xem trước, kết quả ở 390/1440 px trong `quet:v2`. Bốn cổng dự án và site thật phải được kiểm trước nghiệm thu.

Không nạp dữ liệu thật làm kiểm thử, không tự phục hồi/gộp/sửa các bài đã có. Nội dung của một nguồn mới và quyền sử dụng vẫn do người vận hành quyết. Bộ đọc web mới/OCR/dịch vụ nền nằm ngoài đợt này.
