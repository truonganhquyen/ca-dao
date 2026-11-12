# 33 · Kế hoạch cải tiến biên tập và ảnh minh hoạ

**Ngày 12/11/2026 · bắt đầu tự động triển khai 13/11/2026 theo lệnh người sáng lập.**

Tiến độ: giữ chữ (plugin 1.0.53) đã đạt 255 PHP + 523 WordPress + 55 chuyển đổi, quét 0 lỗi, E2E 47 bước/0 lỗi/0 bỏ qua. Chống ghi đè (plugin 1.0.54) đạt 255 PHP + 529 WordPress + 55 chuyển đổi, quét 0 lỗi, E2E 48 bước/0 lỗi/0 bỏ qua. Hai tiến trình PHP trên MySQL 8.4 local: một lưu, một bị từ chối do phiên cũ; chỉ một lần ghi lịch sử. Ảnh minh hoạ (plugin 1.0.55) đạt 255 PHP + 541 WordPress + 55 chuyển đổi, quét 0 lỗi, E2E 49 bước/0 lỗi/0 bỏ qua; browser local bổ sung đã kiểm ghi công, chọn/tải/thay/bỏ/khôi phục, tệp hỏng/quá giới hạn và lỗi đọc attachment. Nhãn/trạng thái trống đang thực hiện; pilot nội dung thật chưa nghiệm thu. Không dùng kết quả phần mềm để đánh dấu nội dung thật đã được duyệt.

Mục tiêu là làm việc biên soạn tin cậy hơn, thêm được ảnh minh hoạ vào tác phẩm và làm trang đọc gọn hơn. Giữ WordPress V2, ba dòng việc Bài mới · Hộp việc · Bổ sung và dịch vụ lưu dùng chung.

Kế hoạch này thay thế thứ tự triển khai trong [đề xuất ưu tiên](audits/2026-11-12-priority-proposals/DE-XUAT-UU-TIEN.md) **đối với đợt việc này**, theo bốn ngoại lệ người sáng lập nêu ngày 12/11. Các báo cáo audit trước đó giữ làm bằng chứng lịch sử, không còn là danh sách bắt buộc phải sửa hết.

Baseline lúc lập kế hoạch: `main`, `f04be9538b8323c7d3a63a89bf5a3958026d0ae3`, plugin 1.0.52/theme 1.0.5. Khi bắt đầu làm mã phải đọc lại `main`, diff và backlog hiện tại, không dùng baseline này thay kiểm tra thực tế.

## 1. Phạm vi đã loại khỏi đợt này

| Mục không làm | Cách thực hiện theo quyết định mới |
|---|---|
| Bộ trích lời và lỗi nhãn “Video” | Không sửa parser, không mở chiến dịch dò/sửa lời tự động; người sáng lập tự dọn thủ công. Không đặt bản sửa parser làm phụ thuộc của các đợt bên dưới |
| Token `[cadao_nguon#]` trên hai trang nguồn/chính sách | Giữ nguyên chủ ý admin. Không sửa token, shortcode, nội dung trang hoặc cache để mở danh sách nguồn cho độc giả |
| Hiện nguồn người nhập; kiểm nguồn cờ và câu xem trước | Không nối nguồn dị bản/cờ ra trang đọc; không sửa kiểm `ho_so=0`, câu xem trước đối chiếu hoặc chính sách nguồn trong đợt này |
| Diễn tập phục hồi DB/ảnh, kiểm sức khoẻ toàn kho trước bulk | Không làm restore, census DB, rà orphan/slug toàn kho hoặc yêu cầu các kết quả này để bắt đầu/nghiệm thu kế hoạch |

Nguồn và hồ sơ hiện có vẫn giữ trong dữ liệu nội bộ cho người biên soạn; không xoá metadata, không mở thêm nguồn công khai và không tự thay đổi các chỗ đang hiển thị. Phần nhãn ở đợt 3 không được kéo việc đối chiếu/nguồn/cờ đã loại trở lại phạm vi.

Ảnh minh hoạ là luồng riêng: ghi công ảnh theo giấy phép đang áp dụng vẫn đi cùng ảnh khi cần, không đồng nghĩa mở nguồn văn bản hay trang danh sách nguồn. Không đổi giấy phép/quyền sử dụng thay người phụ trách.

Đợt này không có migration, nhập/sửa/xoá/gộp dữ liệu hàng loạt. Kiểm giao dịch trên DB thử bằng dữ liệu kỹ thuật là kiểm phần mềm cho chống ghi đè, không phải diễn tập phục hồi hay kiểm toàn kho production.

## 2. Kết quả cần đạt và thứ tự thực hiện

| Đợt | Kết quả cụ thể | Lợi ích | Phụ thuộc | Chủ trì |
|---|---|---|---|---|
| **1 · Giữ công biên tập** | Giữ chữ khi lỗi/lưu phần khác; hàng nhập dở báo thiếu; tab cũ không ghi đè âm thầm | Không phải gõ lại, giảm sửa chồng khi dùng hai tab/hai người | Không phụ thuộc bốn mục đã loại | Dev; CTV thử hai tài khoản |
| **2 · Thêm được ảnh vào bài** | Chọn/tải → gắn tác phẩm → xem trước → lưu → khách thấy đúng ảnh; thay/bỏ ảnh có lịch sử | Hoàn tất tác vụ đang bị chặn; dùng được ảnh minh hoạ đúng ngữ cảnh | Dùng cơ chế giữ chữ và chống ghi đè của đợt 1 | Dev; người phụ trách chọn ảnh/quyền |
| **3 · Rõ thao tác, gọn trang đọc** | Nhãn lưu/lỗi dễ hiểu, tên phần đúng thể loại; form Hiểu thêm trống mở theo nhu cầu | Ít lưu nhầm, bớt cuộn qua phần trống, vẫn góp nội dung thuận tiện | Làm trên luồng đã ổn định; không sửa nguồn/cờ/xem trước đối chiếu | Dev; CTV thử tác vụ |
| **4 · Rà mẫu và làm sâu nội dung** | Rà quan hệ/gợi ý có chủ đích; 10 bài đầu, hai bài mỗi thể loại; một tuyển tập thực | Kiểm giá trị sử dụng và tải công biên tập trước tăng số lượng | Nội dung nháp chuẩn bị song song; nghiệm thu luồng dùng ảnh sau đợt 2 | Hai CTV/người biên soạn; dev sửa lỗi tác vụ thuộc phạm vi |
| **5 · Tổng nghiệm thu và bàn giao** | Bộ hồi quy bổ sung, bốn cổng đạt, bằng chứng thao tác, hướng dẫn ngắn và danh sách còn lại | Có kết quả review được, phân biệt sửa mã xong với người dùng đã nghiệm thu | Các phần mềm và mẫu nội dung thuộc đợt được bàn giao | Dev + người dùng thử |

Không lấy việc các finding ngoài phạm vi vẫn còn làm lý do dừng toàn bộ kế hoạch. Không hạ hoặc tắt kiểm thử đang bắt buộc.

## 3. Đợt 1 — giữ chữ và chống ghi đè

### 3.1. Giữ dữ liệu nhập

Làm trước trên Sửa tác phẩm trong wp-admin; sửa nhanh trên trang đọc dùng cùng quy tắc kiểm/lưu, có cách báo lỗi và giữ chữ tương ứng.

- Khi validation thất bại, dựng lại đúng dữ liệu người vừa nhập, đặt lỗi ở ô/hàng liên quan, giữ phần đó mở và đưa focus tới lỗi.
- Hàng danh sách trống hoàn toàn có thể bỏ qua. Hàng đã điền Từ mà thiếu Lời giải phải báo thiếu và giữ hàng, không trả “Không có gì thay đổi” rồi làm mất chữ.
- Khi lưu một phần, chữ chưa lưu ở phần khác phải còn sau tải lại. Dùng bộ đệm theo tab/phiên cho các trường nội dung đã cho phép; dọn phần đã lưu, hết hạn hoặc khi đăng xuất. Không đưa bản nháp vào bộ nhớ lưu lâu dài dùng chung giữa tài khoản.
- Cảnh báo trước thao tác rời bài/tải lại khi vẫn còn chữ chưa lưu, gồm lối “lưu xong sang bài tiếp theo”. Không bắt mọi lần lưu bình thường phải xác nhận thêm.
- Bộ đệm không chứa mật khẩu, cookie, nonce hoặc nội dung của người khác. Dữ liệu lỗi trả về từ server chỉ đọc lại được trong đúng ngữ cảnh người dùng/bài/phần, có thời hạn và được escape trước khi hiển thị.
- Giữ việc lưu từng phần; chưa chuyển toàn bộ màn sửa thành một biểu mẫu lớn hoặc thêm “lưu tất cả”. Đường server vẫn giữ đầu vào của form bị lỗi khi JavaScript không chạy; khả năng đọc không JavaScript của trang công khai phải giữ.

**Nghiệm thu:** điền hai phần rồi lưu một phần không làm mất chữ phần còn lại trong chế độ làm việc có JavaScript; trường quá 4.000 ký tự còn nguyên để sửa; hàng điền dở không biến mất; lưu thành công không phục hồi nhầm dữ liệu cũ; hai tài khoản không đọc được bộ đệm của nhau.

### 3.2. Chặn bản mở cũ ghi đè

- Khi mở phần, nhận diện phiên/nội dung đã nhìn thấy. Gửi dấu nhận diện đó cùng thao tác lưu trực tiếp ở wp-admin và sửa nhanh.
- Dịch vụ lưu chung phải kiểm bản đã thấy với bản hiện tại trong cùng thao tác ghi có bảo vệ giao dịch/khóa thích hợp; kiểm ở JavaScript hoặc đọc trước rồi ghi ngoài khóa là chưa đủ.
- Nếu phần đã đổi: không ghi đè, báo rõ có bản mới, giữ chữ đang sửa và cho mở bản hiện tại để đối chiếu. Không tự hợp nhất lời/diễn giải của hai người.
- Phân biệt cùng phần với khác phần: sửa Ý nghĩa không vô cớ làm bản mở Từ khó bị xung đột. Kiểm cả nội dung tổng hợp và nhật ký khi hai phần được ghi gần nhau.
- Rà toàn bộ đường gọi trực tiếp và đường nội bộ có liên quan. Giữ các bảo vệ phiên của Hộp việc, đổi trạng thái và phân loại đã có; không thêm đường bỏ qua kiểm phiên cho client cũ.
- Ưu tiên tận dụng meta và cơ chế giao dịch hiện có, chưa mở bảng dữ liệu mới. Mọi thay đổi contract của đường đang sửa phải được cập nhật tài liệu cùng PR.

**Nghiệm thu:** A/B mở cùng phần → A lưu → B gửi bản cũ bị từ chối và giữ chữ; thực hiện bằng hai tab cùng tài khoản và hai tài khoản. Có ca gửi gần đồng thời trên DB thử phù hợp để kiểm thao tác ghi, không dùng hai tab lưu tuần tự làm bằng chứng duy nhất về tính nguyên tử. Có ca khác phần, gửi lại sau lỗi, lỗi ghi và khôi phục lịch sử.

**Vùng mã dự kiến:** `GiaoTiep/QuanTri/SuaTacPham.php`, `GiaoTiep/BienTap/TamSua.php`, `GiaoTiep/BienTap/Module.php`, `DichVu/LuuPhan/LuuPhan.php`, `KhoLuu/PhanBai.php`, JavaScript admin/sửa nhanh. Các đường dẫn PHP tính từ `wordpress/v2/plugins/ca-dao/src/`.

## 4. Đợt 2 — hoàn thiện ảnh minh hoạ

### 4.1. Phạm vi phiên bản đầu

Một ảnh minh hoạ **tuỳ chọn** cho mỗi tác phẩm, áp dụng cả năm thể loại, chọn từ Thư viện WordPress hoặc tải ngay tại màn Sửa tác phẩm. Đây là giới hạn phạm vi đầu để làm đủ luồng, không phải yêu cầu thay cấu trúc dữ liệu lớn. Không làm album, trình dàn trang, ảnh AI tự sinh hoặc kho ảnh bên ngoài.

Tận dụng phần `media` đã có (`attachment_id`, `loai`); chỉ thay mục ảnh đang quản lý, giữ các mục media loại khác nếu dữ liệu đã có. Mô tả/chú thích/ghi công dùng dữ liệu attachment và cơ chế phù hợp hiện có; nếu cần đổi cấu trúc lớn thì đưa phương án cụ thể qua H1, không tự mở rộng giữa chừng.

### 4.2. Luồng phải đi hết

1. Mở mục **Ảnh minh hoạ**, phân biệt với phần chữ **Hình ảnh trong bài**.
2. Chọn ảnh sẵn hoặc tải ảnh hợp lệ; báo rõ tải lên Thư viện và gắn vào bài là hai trạng thái khác nhau.
3. Xem ảnh, mô tả ảnh, chú thích/ghi công theo quyền áp dụng. Không tự chọn quyền sử dụng, tác giả hoặc xuất xứ.
4. Lưu qua dịch vụ chung, dùng bảo vệ phiên của đợt 1; có thông báo thành công hoặc lỗi giữ nguyên phần chữ đang làm.
5. Trang đọc nhận đúng dữ liệu ảnh qua mapper và render. Xem như khách để kiểm ảnh đã gắn, không chỉ kiểm màn Thư viện.
6. Thay hoặc bỏ liên kết ảnh có lịch sử/khôi phục. Bỏ ảnh khỏi bài không tự xoá tệp gốc trong Thư viện.

### 4.3. Quy tắc hiển thị và quyền

- Không bắt buộc có ảnh mới được đăng bài.
- Ảnh có ích cho nội dung, không tự khớp thực thể chỉ theo tên. Người biên soạn chọn ảnh.
- Chuẩn bị bản xem trước 390/1440 px cho các thể loại. Phương án đầu: ảnh nằm sau phần lời/nghĩa chính; ảnh lộ đáp án câu đố nằm trong khối đáp án đang thu gọn. Không dùng ảnh đáp án làm ảnh hiện bên ngoài khối đó hoặc ảnh chia sẻ trước khi có quyết định phù hợp.
- Ảnh nháp không hiện qua trang bài công khai. Không coi URL trực tiếp của tệp uploads là kho riêng tư.
- Kiểm capability sửa bài và quyền tải/chọn ảnh ở server; không tự cấp quyền rộng cho vai Cộng tác chỉ để vượt màn bị chặn.
- Ghi công ảnh bắt buộc theo giấy phép áp dụng vẫn ở đúng chỗ; không nối nguồn lời/dị bản/cờ ra công khai qua PR ảnh.
- Tải mã media của WordPress ở màn quản trị cần dùng; trang đọc chỉ nhận phần mã tối thiểu. Giữ ngân sách JavaScript 21 KB và ngân sách trang hiện hành; đo cả ảnh thực, không chỉ fixture không có ảnh.
- Có kích thước bố cục, ảnh đáp ứng, mô tả cho trợ năng và lazy loading ở vị trí phù hợp. Kiểm định dạng/dung lượng theo cấu hình thật của runtime thử, không hứa ảnh lớn đều tải được từ phép thử PNG 1×1.

**Nghiệm thu:** chọn ảnh có sẵn; tải ảnh mới; ảnh lỗi/quá giới hạn; quyền không đủ; hết phiên; thay/bỏ/khôi phục; nháp so với đã đăng; hai tab sửa ảnh; guest thấy ảnh và ghi công cần thiết; câu đố chưa mở không bị lộ đáp án qua cách bố trí ảnh; tắt JavaScript vẫn đọc được nội dung.

**Vùng mã dự kiến:** `TamSua`, `SuaTacPham`, dịch vụ lưu phần, bộ kiểm phần `media`, `DocBai`, `TrangTacPham`, khối trang đọc và lịch sử. `SoSanh` hiện bỏ ID attachment khỏi phần so chữ: bổ sung cách nhận ra ảnh trước/sau mà không đổi ý nghĩa của lịch sử các phần khác.

## 5. Đợt 3 — rõ thao tác và làm gọn trạng thái trống

### 5.1. Nhãn và phản hồi

- Dùng đúng tên phần theo thể loại, đặc biệt “Giải thích đáp án” thay nhãn Ý nghĩa chung bên trong form câu đố.
- Thông báo chỉ rõ phần đã lưu, kết quả là nháp hay khách thấy theo hành vi đang có; lỗi dùng tên trường tiếng Việt và hướng sửa.
- Không tự biến mọi phần thành một quy trình duyệt mới; không sửa câu xem trước sách, kiểm nguồn cờ hoặc mở nguồn người nhập đã bị loại.
- Rà quyền thực tế của người thử và viết hướng dẫn thao tác theo quyền; không sửa vai tài khoản production trong lượt lập kế hoạch.

### 5.2. Hiểu thêm và tuyển tập chưa có nội dung

- Khi chưa có đóng góp được duyệt, thử trạng thái ngắn kèm nút/mục mở form “Chia sẻ cách hiểu”; không trải sẵn form dài.
- Khi có nội dung đã duyệt, để độc giả đọc được nội dung đó trước. Giữ các loại đóng góp, consent và cơ chế duyệt.
- Ưu tiên thành phần HTML hoạt động khi tắt JavaScript; giữ bàn phím, focus và vùng chạm. Không thêm tracking để đo tác vụ.
- Với tuyển tập chưa có nội dung thực, lời mời phải trung thực; khi đã có bộ được duyệt, dùng module tuyển tập hiện có. Không tạo post type mới.
- Dựng bản xem trước cụ thể rồi xử lý H4 nếu thay đổi đáng kể hành vi đã chốt; không dùng bước này để chặn các bản sửa giữ chữ/chống ghi đè độc lập.

**Nghiệm thu:** 390/1440 px, có/không có đóng góp, có/không JavaScript; đường gửi góp vẫn dễ tìm và không xuất hiện trước duyệt; không có nút dẫn vào trải nghiệm được quảng bá như đã có trong khi vẫn trống.

## 6. Đợt 4 — rà mẫu và pilot 10 bài

CTV có thể chọn bài, đối chiếu và soạn nháp từ đầu. Người có quyền biên tập quyết nội dung và đăng; agent không tự xác nhận hoặc xuất bản thay.

| Thể loại | Số bài đầu | Trọng tâm | Lợi ích cần kiểm |
|---|---:|---|---|
| Ca dao | 2 | Ý nghĩa ngắn, từ khó, dị bản, hình tượng; ảnh tuỳ chọn | Người đọc hiểu ý và phân biệt các phần |
| Tục ngữ | 2 | Nghĩa đen/bóng/dùng khi phù hợp từng bài | Hiểu và chọn được tình huống dùng |
| Đồng dao | 2 | Cách đọc; cách chơi chỉ khi có căn cứ | Phụ huynh/giáo viên dùng được, không phải đoán luật chơi |
| Vè | 2 | Bối cảnh có căn cứ và chia đoạn | Theo được bài dài; nhập mốc dòng ít nhầm |
| Câu đố | 2 | Đáp án, gợi ý, giải thích và ảnh đúng thời điểm | Tự giải trước, hiểu lý do sau mở đáp án |

Nguồn được kiểm và lưu nội bộ theo cách người biên soạn làm; không đặt điều kiện phải công khai nguồn hoặc làm bốn hạng mục đã loại mới được nghiệm thu pilot. Người sáng lập tự xử lý nguyên văn cần dọn trong mẫu.

Rà khoảng 20 quan hệ tác phẩm/mục từ/địa danh/bài liên quan và các điểm vào dành cho trẻ trong phạm vi mẫu để tìm chỗ gợi ý sai ngữ cảnh; ghi danh sách cho người biên soạn quyết. Đây là QA mẫu cho trải nghiệm, không phải census, migration taxonomy hay sức khoẻ toàn DB.

Tạo **một tuyển tập** từ bài thực đã duyệt, dùng module sẵn có. Không ràng buộc hai bộ 10–15 bài khi mẫu đồng dao mới có hai bài. Chỉ mở rộng tới 34 bài/hai tuyển tập sau khi có nội dung và thời gian phù hợp.

Thử tác vụ với 5–8 người tự nguyện, ghi số lần nhập lại, lưu nhầm, quay màn, thời gian/bài và chỗ không biết làm tiếp. Không thu thông tin cá nhân hoặc thêm sự kiện đo lường mới. Kết quả nhóm nhỏ là bằng chứng tác vụ, không đại diện toàn bộ độc giả.

## 7. Chia bản sửa để review

| Bản sửa dự kiến | Đầu ra review được | Phụ thuộc | Ngày công dev dự trù |
|---|---|---|---:|
| **Chuẩn bị** | Đối chiếu baseline/caller; ghi ca tái hiện và mẫu nghiệm thu; đưa việc vào backlog khi bắt đầu triển khai | Không cần DB production hoặc restore | 0,5–1 |
| **Giữ chữ** | Server giữ đầu vào lỗi, bộ đệm phần chưa lưu, cảnh báo rời bài và kiểm hàng điền dở | Chuẩn bị | 2–3 |
| **Chống ghi đè** | Phiên phần bài, kiểm ghi chung, thông báo xung đột và hồi quy hai phiên | Giữ chữ để lỗi xung đột không làm mất công đang nhập | 2–4 |
| **Ảnh minh hoạ trọn luồng** | Admin + kiểm/lưu + trang đọc + lịch sử + E2E; một nhánh cho cả luồng | Hai bản sửa đợt 1 | 4–6 |
| **Nhãn rõ** | Câu chữ theo thể loại, phản hồi lưu/lỗi; hướng dẫn quyền | Đi cùng các đường đã sửa; không chạm phần nguồn bị loại | 0,5–1 |
| **Trạng thái trống gọn** | Bản mẫu và mã Hiểu thêm/tuyển tập, kiểm trợ năng | Luồng góp hiện có; bản xem trước cụ thể | 1–2 |
| **Chốt nghiệm thu** | Sửa lỗi thuộc phạm vi phát hiện qua QA/pilot, bốn gate và hướng dẫn bàn giao | Các đầu ra cần giao của đợt | 1–2 |

Tổng dự trù **11–19 ngày công dev**, thêm **2–4 ngày dự phòng**, chưa tính công biên tập nội dung, thời gian review/CI chờ hoặc quyền triển khai. Đây là ước lượng lập kế hoạch cho giả định 1 dev + 2 CTV từ V5, không phải cam kết ngày lịch. Cập nhật sau đợt 1; chưa ấn định sáu tuần hoặc một ngày ra mắt khi chưa biết quỹ giờ thực tế.

Giữ tối đa một việc kỹ thuật chính đang làm. Các sửa nhãn nhỏ có thể gom vào bản sửa liên quan, không bắt buộc tạo PR riêng. Đợt ảnh có thể chia commit nội bộ để review nhưng chỉ coi hoàn tất khi guest thấy ảnh; không chốt “xong” chỉ ở bước tải vào Thư viện.

## 8. Kiểm thử và điều kiện bàn giao

| Nhóm | Bằng chứng tối thiểu |
|---|---|
| Không mất chữ | Hai phần đang viết; lỗi validation; hàng danh sách điền dở; đổi tab/tải lại/đi bài tiếp; dọn bộ đệm sau lưu và đổi tài khoản |
| Xung đột | Cùng/khác tài khoản; cùng/khác phần; gửi gần đồng thời trên DB thử; thiếu/sai phiên; thử lại; lịch sử/khôi phục; lỗi ghi không báo lưu thành công |
| Ảnh | Chọn/tải/gắn/hiện; nháp/đã đăng; thay/bỏ/khôi phục; quyền; lỗi tệp/phiên; câu đố; ảnh thực trong ngân sách |
| Nhãn và Hiểu thêm | Thông báo đúng tác động; dữ liệu rỗng/có sẵn; đóng góp còn chờ/đã duyệt; 390/1440 px, bàn phím và không JavaScript |
| Giữ phạm vi | Không diff parser/shortcode/trang chính sách để làm bốn mục đã loại; không mở nguồn văn bản, đổi quyền nguồn hoặc chạy bulk data; không tắt kiểm thử có sẵn |
| Pilot | 10 bài do người quyết; một tuyển tập thực; biên bản tác vụ và danh sách lỗi trong phạm vi; không tự đăng/duyệt nội dung thay con người |

Mở rộng các bộ kiểm đang chạy qua `npm run test:v2`, `npm run quet:v2`, `npm run e2e:v2`, cùng cổng `npm run kiem-tra`; không tạo bộ gate thay thế. Mẫu kỹ thuật đặt trong `wordpress/v2/tests/`, tích hợp browser vào kịch bản V2 hiện có; không đưa lời/nguồn thô hoặc thông tin cá nhân vào repo.

Mỗi bản sửa plugin/theme phải tăng phiên bản theo quy định, chạy đủ bốn cổng, xem diff, cập nhật tài liệu/trạng thái phù hợp và có link bản xem trước. Kết quả test local không được ghi thành nghiệm thu site hoặc nội dung thật.

Bàn giao gồm: bản xem trước, ảnh màn quan trọng, log bốn gate, cách tái hiện trước/sau, hướng dẫn một trang về giữ bản nháp/xung đột/ảnh, và danh sách việc còn lại. Kiểm bản đã triển khai chỉ thực hiện khi có lần triển khai được phép; không mở một dự án kiểm toàn server/DB để chặn kế hoạch này.

## 9. Trạng thái và ranh giới thực hiện

- Đã mở T-194..T-198 theo từng đầu ra trong backlog. T-194 sẵn sàng review sau đủ cổng; các phần sau tiếp tục theo phụ thuộc, không coi việc cũ đã DONE là bằng chứng lỗi mới đã được sửa.
- Lệnh tự động triển khai không thay người biên soạn quyết nội dung hoặc tự mở Google. Kiểm ghi/chạy browser dùng dữ liệu kỹ thuật ở môi trường thử.
- Nội dung, quyền ảnh, xoá/gộp/đổi đường dẫn bài đã đăng, schema lớn và UX lớn vẫn theo đúng Human Gate liên quan. Không yêu cầu chốt thêm để hoàn tất việc lập kế hoạch này.
- Google/SEO đang dừng, audio, AI nội bộ, nâng cấp tìm kiếm/bản đồ và làm giàu đồng loạt không thuộc đợt này.
- Các ghi nhận chưa xác minh về backup/DB vẫn là chưa xác minh; theo chỉ đạo mới, chúng không thành đầu việc hoặc cổng chặn của kế hoạch.

## 10. Căn cứ

- [Trinh sát biên soạn](audits/2026-11-12-editorial-journey/BAO-CAO.md): mất chữ, ghi đè, nhãn và thao tác theo thể loại.
- [Bổ sung ảnh](audits/2026-11-12-editorial-journey/BO-SUNG-ANH.md): thiếu luồng tệp ảnh minh hoạ.
- [Đề xuất ưu tiên](audits/2026-11-12-priority-proposals/DE-XUAT-UU-TIEN.md): đầu vào trước khi áp dụng bốn ngoại lệ.
- `docs/26`, `docs/27`, `docs/28`, `docs/32`: kiến trúc, trang đọc và biên tập hiện có; khi triển khai chỉ cập nhật phần thực sự thay đổi.
- Chỉ đạo trực tiếp ngày 12/11/2026 của người sáng lập trong cuộc trao đổi này quyết định phạm vi mới, được phản ánh tại mục 1.
