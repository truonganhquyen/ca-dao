# Góp một câu

Phạm vi đã được người sáng lập đồng ý, D-123, 20/11/2026. Một người đọc nhớ một câu có thể gửi ngay; quản trị xử lý tại **Hộp việc → Người đọc gửi**, không thêm menu hoặc hàng chờ.

## Người đọc

CTA **Góp một câu** ở đầu trang máy tính, điều hướng dưới điện thoại, trang chủ, tìm chưa có kết quả và cuối bài. Trang `/gop/` giữ thương hiệu và lối quay lại đọc; không điều hướng thể loại/thanh dưới làm phân tán.

Một ô **Lời bạn nhớ** ≤ 4.000 ký tự, giữ xuống dòng/NFC; không bắt người đọc chọn thể loại. Nguồn ≤ 2.000 ký tự và bút danh ≤ 80 nằm trong phần tuỳ chọn. Không yêu cầu người kể, vùng, tên thật, email hoặc tài khoản. Đồng ý sử dụng CC BY 4.0 theo luồng hiện hành và đồng ý hiển thị bút danh là hai lựa chọn riêng, không chọn hộ.

Tra gần giống sau khi ngừng gõ; tối đa hai bài công khai, mở xem trong thẻ mới để giữ lời đang nhập. Không tự xoá lời hoặc vô hiệu nút gửi. Gặp lời khớp: cảm ơn, mời xem bài và cho gửi bổ sung nguồn/lời khác. Gửi thành công nói rõ **đã nhận, chưa đăng**. Tắt JavaScript vẫn gửi được; quản trị vẫn đối chiếu. Lỗi gửi giữ nguyên thông tin và báo ngay cạnh form.

Các ý định báo lỗi, chú thích, số trang, cảm nhận ở phần riêng gập dưới form hoặc liên kết theo ngữ cảnh; giữ tương thích đường dẫn cũ.

## Kiểm trùng và riêng tư

Dùng `VanTay`: vân tay có dấu và không dấu; Jaccard cặp âm tiết liền nhau cho gần giống từ sáu âm tiết. Từ 30% chỉ là ngưỡng gợi ý gần chữ, không phải xác suất trùng hay quan hệ dị bản. Trên 70% vẫn nhận đóng góp vì một khác biệt nhỏ có thể có giá trị. Không đổi ngưỡng tự gộp nguồn hiện hành. So gần dựa chỉ mục cặp của lời chính; dị bản đã ghi được đối chiếu khớp toàn lời bằng vân tay, không hứa phát hiện mọi quan hệ ngữ nghĩa.

Đường khách `POST /phongdao/v1/gop/gan-giong` chỉ trả tên, URL, cờ khớp; lọc trong SQL **trước LIMIT**: đúng tác phẩm, Đăng, không mật khẩu/nhạy cảm. Không trả số lượng hay dấu hiệu của Kho, nháp hoặc đóng góp đang chờ. Không tái sử dụng REST quản trị. Giới hạn lời/yêu cầu và 30 lần/phút bằng dấu băm địa chỉ mạng theo phút, hết hạn; không lưu địa chỉ thô hay chữ tra vào transient. Gửi giữ chống rác hiện hành (bẫy, 5/giờ, Turnstile chỉ khi đã cấu hình). Không thêm dịch vụ, cookie hoặc sự kiện đo lường.

Quản trị so thêm Kho, bài nháp và bản gửi chờ; không dùng đóng góp chờ trong luồng tự gộp nguồn. Khớp **vân tay có dấu** thì chặn tạo bài mới/thêm dị bản y hệt, vẫn cho bổ sung nguồn. Khác dấu không bị tự gộp. Điểm gần không thay quyết định nội dung của người duyệt.

Mục nguồn đã có thẻ chờ từ trước cũng đối chiếu lại với bài đã nhận đóng góp trước khi đăng: nếu khớp lời thì giữ nguyên Kho/thẻ/nguồn và báo mở mục Kho để đối chiếu. Không tạo thêm tác phẩm hoặc tự gộp nguồn, chép chú thích vào bài bạn đọc đã được duyệt. Lượt đăng nguồn và duyệt đóng góp dùng cùng khoá giao dịch.

## Quản trị

1. Một bản gửi `gop_y` chờ + một thẻ Người đọc gửi. Bản nguyên gửi, nguồn, bút danh, hai đồng ý, giấy phép hiện hành và giờ nhận nằm riêng tư. Mã lần gửi có chữ ký; gửi lại cùng lần không tạo thêm thẻ, người gửi khác giữ chứng cứ riêng.
2. Thẻ cho xem đủ lời, nguồn chưa đối chiếu, consent và các mục gần giống. Có thể xem bài đích khác theo số bài. Chọn thể loại khi đăng mới; gợi ý chủ đề chưa chọn hộ, có phần chọn thêm chủ đề/vùng hiện có. Vùng chỉ gắn khi người biên soạn có căn cứ. Không thêm từ vựng hoặc tự đoán thể loại/vùng.
3. Chọn **Đăng tác phẩm mới**, **Thêm dị bản**, **Bổ sung nguồn**, **Để sau** hoặc **Bỏ**. Thêm dị bản không đổi lời chính/phân loại bài đã có; nguồn chỉ bổ sung khi khớp lời chính/dị bản. Không xoá hay gộp tác phẩm cũ.
4. Nonce/quyền duyệt/phiên thẻ + snapshot bài đích; khoá giao dịch, tra trùng lại khi quyết. Lời, ghi công, trạng thái bản gửi, chỉ mục và đóng thẻ phải cùng thành công. Lỗi hoàn tác và làm sạch cache, giữ thẻ để thử lại. Hai lượt duyệt đóng góp tạo mới được tuần tự hoá để lượt sau thấy bài lượt trước.
5. Kết quả Đã xử lý ghi rõ hành động và có lối Sửa bài/Xem bài. Không tự động quyết nội dung, không đánh dấu nguồn đã kiểm.

## Nguồn và ghi công

Phần `dong_gop` dùng cơ chế meta/lịch sử sẵn có, không bảng mới. Mỗi chứng cứ gồm ID bản gửi, phần lời/dị bản, vân tay có dấu, bút danh được phép và nguồn người duyệt chọn công khai. Nhiều người góp cùng lời giữ nhiều chứng cứ. Không coi người đóng góp là tác giả hoặc người kể.

Ghi công ngay dưới đúng lời/dị bản đã dùng. Không hiện bút danh khi thiếu một trong hai: người gửi đồng ý và quản trị chọn công khai. Nguồn mang nhãn **Nguồn người góp cung cấp — chưa đối chiếu**; ô nguồn quản trị nhắc bỏ thông tin cá nhân. Không đưa bản gửi riêng tư, token hoặc tên không được công khai vào HTML khách. Sửa lời sau này làm vân tay thay đổi thì chứng cứ cũ không tự chuyển sang lời mới.

Trong **Sửa tác phẩm → Phần khác → Ghi công và nguồn bạn đọc**, người có quyền duyệt có thể ẩn bút danh, sửa nguồn công khai hoặc gỡ dòng ghi công. Form chỉ sửa chứng cứ đã có, giữ phần/vân tay/ID bản gửi tại máy chủ; không tự khôi phục tên đã ẩn. Mỗi lần lưu có phiên và lịch sử như các phần khác; bản gửi riêng tư được giữ.

## Nghiệm thu

Bốn cổng dự án hiện có; fixture tổng hợp kỹ thuật, không ghi dữ liệu thật để thử. Kiểm tra gửi/duyệt lặp, bản gửi cùng lời khác nguồn, lỗi giao dịch, bài đích đổi, quyền, phạm vi khách, ẩn danh, nguồn nhiều chứng cứ, JavaScript bật/tắt, bàn phím và điện thoại. Sau gộp phải kiểm phiên bản live; CI không đồng nghĩa đã triển khai.
