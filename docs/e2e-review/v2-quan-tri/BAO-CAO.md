# Trinh sát quản trị và biên tập Phongdao

| | |
|---|---|
| Ngày | 10/11/2026 |
| Bản phần mềm | plugin 1.0.37, theme 1.0.2 (nhánh main, cùng bản đang chạy trên phongdao.com) |
| Người làm | Claude Code, đóng vai quản trị viên và biên tập viên |
| Ảnh minh hoạ | thư mục `anh/` cạnh tệp này (dữ liệu mẫu, không phải lời thật) |
| Sau báo cáo | Người sáng lập chốt mục 8: 1, 2, 3 đồng ý, 4 không (D-100..D-102). Lỗi mục 5 và đợt 1 mục 7 làm ở plugin 1.0.38 (T-187); ảnh là hiện trạng trước khi sửa |

## 1. Cách làm và giới hạn

- **Không đụng site thật.** Dựng một bản site chạy trên máy với đúng mã đang chạy trên phongdao.com, nạp dữ liệu mẫu cỡ vận hành:
  - 47 tác phẩm đủ năm thể loại, đủ trạng thái (đã đăng, nháp, chờ, đã ẩn);
  - 163 câu trong Kho, vài câu có cờ;
  - 48 thẻ Hộp việc đủ sáu loại: câu mới, nháp ý nghĩa, có thể trùng, cần xem, gợi ý chủ đề và vùng, người đọc gửi;
  - hai tài khoản: quản trị và biên tập viên.
- **Đi qua 24 màn** theo ba cách xem: quản trị trên máy tính, quản trị trên điện thoại (390 px), biên tập viên trên máy tính. Mỗi màn được chụp ảnh và đo số nút, số ô nhập, chiều dài trang.
- **Làm 16 luồng việc thật bằng trình duyệt tự động.** Mỗi luồng đếm số thao tác (bấm hoặc chọn, không tính gõ chữ). Các luồng gồm:
  - đăng câu mới, viết Ý nghĩa, duyệt gợi ý;
  - lấy câu từ Kho rồi đăng, ẩn bài, khôi phục bản trước;
  - mở bốn loại thẻ, thêm bài, mở menu trên điện thoại.
- **Giới hạn:**
  - Dữ liệu là dữ liệu mẫu.
  - Máy chạy tại chỗ nhanh hơn host thật, nên thời gian chỉ để tham khảo.
  - Chưa thử vai Cộng tác.
  - Chưa thử trên điện thoại thật và bàn phím thật.

## 2. Kết luận ngắn

Phần nghiệp vụ bên dưới đúng và an toàn: có xem trước, Hoàn tác, Lịch sử và khôi phục, và mọi quyết định do người bấm. Cảm giác "phức tạp, nhiều bước, không logic" là có thật, nhưng nó đến từ **cách sắp xếp giao diện**, không phải từ nghiệp vụ:

1. **Hai giao diện song song cho cùng một việc.**
   - Hai nơi đó là wp-admin và các trang `/bien-tap/…` trên site đọc.
   - Hai bên khác nhau về khả năng, cách gọi và hình thức. Mỗi bên thiếu thứ bên kia có.
2. **Menu xếp theo kỹ thuật, không theo việc hằng ngày.** Ví dụ: "Lô thao tác", "Sửa tác phẩm" (một màn trống), mục "Phongdao" mở ra Nguồn & cài đặt.
3. **Chỗ ra quyết định không thấy đủ nội dung.**
   - Hộp việc trong wp-admin chỉ có tên thẻ.
   - Màn sửa bài gập kín mọi phần, viết Ý nghĩa mà không thấy lời.
   - Quyết xong thì vào ngõ cụt, phải tự quay về danh sách.
4. **Mọi việc trên nhiều mục đều qua "Làm theo lô" ba bước**, kể cả ẩn một bài: chọn thao tác, xem trước, áp dụng.
5. **wp-admin trên điện thoại gần như không dùng được.** Mỗi tác phẩm chiếm gần một màn hình, phần đầu trang chỉ có bộ lọc.

**Điểm sáng cần giữ và lấy làm chuẩn:**
- màn **Duyệt từng thẻ** trên điện thoại: thấy đủ nội dung, nút lớn, quyết xong tự sang thẻ sau, có phím tắt;
- **tấm sửa ngay trên trang đọc**.

## 3. Bản đồ hiện trạng: việc nào làm ở đâu

| Việc | wp-admin | Trang `/bien-tap/` và trang đọc | Nhận xét |
|---|---|---|---|
| Xem và quyết việc chờ | Hộp việc: bảng, chỉ có tên thẻ | Hộp việc và Duyệt từng thẻ: thẻ đủ nội dung | Cảm nghĩ người đọc còn hiện thêm ở menu Bình luận của WordPress, thành ba hộp thư cho một việc |
| Danh sách bài | Phongdao → Tác phẩm | `/bien-tap/tac-pham/` | Danh sách gốc của WordPress vẫn mở được qua đường dẫn, thành ba danh sách |
| Sửa bài | Sửa tác phẩm: có phân loại, khôi phục bản trước | Tấm sửa trên trang: có nút Đăng, Ẩn bài | wp-admin không đổi được trạng thái; trang đọc không sửa được phân loại, không chọn được bản cũ để khôi phục (chỉ có Hoàn tác 10 giây) |
| Thêm bài | **Không có**: nút "Thêm mới" ở thanh trên không có mục Tác phẩm | `/bien-tap/them/` | |
| Số liệu, việc nên làm | Bàn làm việc (7 việc nên làm) | Số liệu tuần (3 việc nên làm, khác danh sách) | Trang Dashboard mặc định của WordPress cũng còn |
| Kho | Kho | — | |
| Tuyển tập, chủ đề, vùng, mô-típ | **Không có** | **Không có** (mục từ sửa được ở trang mục từ) | Nằm trong phần thiết kế chưa xây |
| Việc hàng loạt | menu "Lô thao tác" | — | |

## 4. Số đo luồng việc

Thao tác ở đây là số lần bấm hoặc chọn, không tính gõ chữ.

| Việc | wp-admin trên máy tính | Trang đọc, điện thoại |
|---|---|---|
| Đăng 1 câu mới | 5 thao tác qua 3 màn. Quyết xong phải quay về danh sách, lọc lại, mở thẻ kế | **1 bấm mỗi thẻ** (Duyệt từng thẻ), tự sang thẻ sau |
| Viết Ý nghĩa cho 1 bài thiếu | 7 thao tác. **Không thấy lời bài khi viết** | 5 thao tác. Tấm sửa che phần lớn lời |
| Đồng ý 5 gợi ý chủ đề | 11 thao tác qua 3 màn: lọc → chọn → chọn thao tác → xem trước → áp dụng | 6 thao tác (5 lần Đồng ý) |
| Lấy 5 câu từ Kho rồi đăng | 13 thao tác, 2 lần xem trước, 4 màn. **Kết quả đăng 15 câu**, xem lỗi 4 ở mục 5 | — |
| Ẩn 1 bài | 7 thao tác (lọc, chọn, lô, xem trước, áp dụng) | **2** (nút Ẩn bài trên dải Đang sửa) |
| Khôi phục bản trước | 3 | không có |
| Thêm bài | không có | 2 thao tác cùng phần gõ |

**Chiều dài trang trên điện thoại (390 px):**
- Tác phẩm: 18.530 px cho 30 bài, khoảng 22 lần vuốt.
- Kho: 19.378 px.
- Hộp việc trong wp-admin: 11.848 px.
- Màn đầu tiên của Hộp việc và Tác phẩm chỉ có bộ lọc và khối "Làm theo lô", chưa thấy việc nào (ảnh 12, 13).

## 5. Lỗi cụ thể, sửa nhanh được

1. **Lời trong thẻ việc ở wp-admin dính liền, không xuống dòng** ("…bài mẫuDòng thứ hai…"). Gặp ở thẻ Câu mới, Nháp ý nghĩa, Có thể trùng (ảnh 05, 06).
2. **Giờ ở Hộp việc wp-admin chậm 7 tiếng.** Cột "Tạo lúc" in giờ UTC, trong khi Tác phẩm và Lô in giờ Việt Nam.
3. **Lịch "Chọn câu mới hằng ngày — 06:00" thật ra chạy lúc 13:00 giờ Việt Nam**, vì lịch đặt theo giờ máy chủ (UTC) (ảnh 14). Nên xem dòng "Lần chạy tới" trên site thật để xác nhận.
4. **Từ Kho sang Hộp việc dễ đăng nhầm** (ảnh 09, 10).
   - Sau "Đưa 5 câu vào Hộp việc", nút "Mở Hộp việc: thẻ Câu mới" mở **mọi** thẻ Câu mới và không chọn sẵn 5 câu vừa đưa.
   - Nếu chọn "Mọi thẻ khớp bộ lọc", cả câu cũ cũng vào lô Đăng (thử: 15 câu thay vì 5). Vẫn có bước xem trước, nhưng rất dễ nhầm.
5. **Quyết một thẻ trong wp-admin xong là ngõ cụt** (ảnh 06).
   - Không có nút "Thẻ tiếp", không có liên kết tới bài vừa đăng.
   - Màn hiện chữ kỹ thuật: "Quyết định: dang", "Phiên 1".
6. **Trang Sửa tác phẩm không đổi được trạng thái** (Đăng, Ẩn, Về nháp). Muốn đổi phải ra trang đọc hoặc làm theo lô.
7. **Menu sai tên** (ảnh 02).
   - Mục đầu "Phongdao" mở Nguồn & cài đặt khi đăng nhập quản trị, nhưng mở Tác phẩm khi đăng nhập biên tập viên.
   - Mục "Sửa tác phẩm" trên menu mở một màn trống.
8. **"Máy đang làm" báo sai.** Mục "Đọc nguồn tự động" ghi "Chạy theo lịch" trong khi nguồn duy nhất đang tắt.
9. **Thẻ và trang sửa ghi mã nguồn** ("nguồn: mau") thay vì tên nguồn.
10. **Hai hộp thư cho cảm nghĩ người đọc:** vừa ở Hộp việc, vừa ở menu Bình luận của WordPress (có số đỏ).

## 6. Vì sao thấy phức tạp

- **Hai nhà, hai ngôn ngữ.**
  - Cùng là Hộp việc: ở trang đọc có thẻ đẹp, nhóm theo loại có số đếm, phím tắt; ở wp-admin là bảng chữ với ô chọn.
  - Cùng là sửa bài: hai màn khác nhau, mỗi màn làm được một nửa.
  - Người dùng phải nhớ việc nào làm ở đâu.
- **Menu theo cấu trúc dữ liệu.**
  - "Lô thao tác" là khái niệm kỹ thuật. "Sửa tác phẩm" là màn con, không phải mục menu.
  - Đăng nhập xong lại vào trang chào mừng của WordPress (ảnh 01), không vào Bàn làm việc.
  - Menu WordPress không dùng tới (Bài viết, Bình luận, Thư viện…) nằm lẫn với menu Phongdao.
- **Quyết mà không thấy:** danh sách thẻ không có nội dung, nên phải mở từng thẻ, quyết, quay lại, mở thẻ kế.
- **Lô cho mọi thứ.**
  - Khối "Làm theo lô" luôn hiện to ở đầu Hộp việc, Tác phẩm và Kho (ảnh 11), kể cả khi chưa chọn gì.
  - Việc nhỏ (ẩn một bài, đồng ý vài gợi ý) cũng phải qua màn xem trước riêng.
- **Trang sửa gập kín** (ảnh 07, 08).
  - Mười mấy khối đóng, không biết phần nào có gì; lời không hiện.
  - Mỗi phần một nút lưu riêng.
  - Ô "Tôi đã đọc và duyệt" dễ quên: quên đánh dấu thì Ý nghĩa của chính mình viết thành nháp chờ chính mình duyệt.
- **Chữ giải thích dài, nút chính quá nhiều.**
  - Đầu mỗi màn và màn xem trước lô có đoạn hướng dẫn 3–5 dòng.
  - Kho có 30 nút xanh "Đưa vào Hộp việc" trên một trang (ảnh 16).
- **Ba bảng số liệu:** Bàn làm việc, Số liệu tuần, và Dashboard WordPress. Danh sách "Nên làm tuần này" ở hai nơi không giống nhau.

## 7. Đề xuất: cách vận hành mới

### Nguyên tắc

1. **Một nơi làm việc, một kiểu thẻ.** wp-admin là nơi làm việc chính. Giao diện thẻ đang có ở `/bien-tap/` dùng chung cho cả hai nơi.
2. **Menu theo việc hằng ngày,** không theo dữ liệu.
3. **Thấy nội dung ngay nơi quyết định.**
4. **Việc nhỏ một bấm kèm Hoàn tác; việc lớn mới qua xem trước.** Đăng nhiều câu vẫn luôn qua xem trước.
5. **Quyết xong tự sang việc kế.**
6. **Điện thoại là hạng nhất,** không phải bản thu nhỏ của màn máy tính.

Các lằn ranh giữ nguyên: máy không viết lời hay nguồn, không tự đăng, không tự đặt dấu duyệt.

### Menu mới (Phongdao)

1. **Bàn làm việc**: mở ngay khi đăng nhập, thay Dashboard WordPress.
2. **Hộp việc** (kèm số việc chờ).
3. **Tác phẩm**, có nút **Thêm tác phẩm**.
4. **Kho**.
5. **Tuyển tập và phân loại**: khi xây phần còn thiếu.
6. **Nguồn & cài đặt**: đặt cuối, gồm cả Máy đang làm và "Lịch sử thao tác hàng loạt" (thay mục "Lô thao tác").

Với vai biên tập viên, ẩn các menu WordPress không dùng; quản trị vẫn thấy đủ. "Sửa tác phẩm" chỉ mở từ danh sách, không nằm trên menu.

### Các màn chính

- **Bàn làm việc**
  - 4 ô số bấm được: Việc chờ, Câu mới, Thiếu ý nghĩa, Người đọc gửi.
  - Một nút lớn **"Bắt đầu duyệt (45 việc)"** mở chế độ từng thẻ theo thứ tự ưu tiên.
  - Bên dưới là "Nên làm tuần này". Bảng vào/ra và tuổi thẻ thu gọn xuống cuối trang.
  - Gộp Số liệu tuần vào đây.
- **Hộp việc**
  - Trên cùng là các tab loại việc có số, như bản điện thoại đang có.
  - Trên máy tính chia hai cột kiểu hộp thư: bên trái là danh sách thẻ, mỗi thẻ hiện 2 dòng nội dung (lời, hoặc "→ Chủ đề: Gia đình"); bên phải là thẻ đang chọn với đủ căn cứ và các nút quyết.
  - Quyết xong tự sang thẻ kế, có Hoàn tác 10 giây và phím tắt 1/2/3, J/K.
  - Khi đã chọn nhiều thẻ mới hiện một thanh nổi: "Đã chọn 5: Đồng ý · Để sau · Bỏ", kèm hộp xác nhận ngắn. Chỉ việc Đăng hàng loạt mới qua màn xem trước đầy đủ.
- **Sửa tác phẩm (một màn, hai cột)**
  - Bên trái: lời luôn hiện, có số dòng. Phần nào đã có thì hiện nội dung; chỉ phần "Chưa có" mới gập lại.
  - Bên phải (cố định khi cuộn):
    - trạng thái kèm nút Đăng / Ẩn / Về nháp;
    - phân loại dạng chip chọn nhanh;
    - nguồn (ghi tên nguồn), việc đang chờ, lịch sử rút gọn có nút khôi phục.
  - Hai nút rõ ràng thay ô đánh dấu: **"Lưu nháp"** và **"Lưu và hiện cho người đọc"** (chỉ người có quyền duyệt mới thấy nút này). Ô "Có hỗ trợ AI" giữ nguyên.
  - Khi đến từ một danh sách đã lọc (ví dụ Thiếu ý nghĩa) thì có nút **"Lưu và sang bài tiếp"**.
- **Tác phẩm**
  - Tab trạng thái có số: Tất cả · Đã đăng · Nháp · Chờ · Đã ẩn · Thùng rác.
  - Bộ lọc gọn trên một hàng, có chip "Thiếu ý nghĩa".
  - Mỗi hàng gọn, hiện dòng đầu của lời; thao tác ngay trên hàng: Sửa · Xem · Ẩn.
  - Thanh việc hàng loạt chỉ hiện khi đã chọn bài.
  - Trên điện thoại, mỗi bài là một thẻ ba dòng: tên; trạng thái và thể loại; phần còn thiếu.
- **Kho → đăng một mạch**
  - Chọn câu, rồi trên thanh nổi bấm "Đưa vào Hộp việc" hoặc "Xem trước và đăng" (nút sau chỉ cho người có quyền duyệt).
  - Chỉ còn một màn xem trước, rồi bấm Đăng. Mỗi câu vẫn đi đúng đường của nút Đăng và vẫn do người bấm.
  - Số thao tác giảm từ 13 xuống khoảng 5, và hết lỗi đăng nhầm câu cũ.
  - Bỏ nút xanh trên từng dòng, thay bằng thao tác nhỏ trên hàng.
- **Điện thoại:** dùng giao diện thẻ đang có làm bản điện thoại của khu quản trị, với thanh dưới Bàn làm việc · Hộp việc · Tác phẩm · Thêm.

### Lộ trình ba đợt

Mỗi đợt có bản xem trước để bạn bấm thử trước khi đưa lên site.

| Đợt | Nội dung | Cỡ |
|---|---|---|
| 1. Sửa lỗi, bỏ ma sát | 10 lỗi ở mục 5; đăng nhập vào Bàn làm việc; menu gọn; Thêm tác phẩm trong wp-admin; nút "Thẻ tiếp" và "Xem bài" sau khi quyết; khối lô chỉ hiện khi đã chọn; hiện lời và nút trạng thái ở trang sửa | nhỏ, khoảng 1 tuần |
| 2. Hộp việc kiểu hộp thư, trang sửa hai cột | Danh sách và thẻ cạnh nhau, tự sang thẻ kế, thao tác nhanh; trang sửa một màn | vừa, 1–2 tuần |
| 3. Một nhà cho mọi việc | Hợp nhất hai lối vào, Kho đăng một mạch, bản điện thoại dạng thẻ, gộp ba bảng số liệu | vừa đến lớn |

## 8. Cần người sáng lập quyết

1. **Một nhà:** wp-admin là nơi làm việc chính, giao diện thẻ dùng chung, điện thoại dùng bản thẻ; bỏ dần `/bien-tap/tac-pham/` và Số liệu tuần. Đây là thay đổi lớn về hướng giao diện.
2. **Kho "Xem trước và đăng" một mạch:** gộp hai bước vừa chốt hôm nay thành một. Vẫn có xem trước, vẫn do người bấm.
3. **Thay ô "Tôi đã đọc và duyệt" bằng hai nút** "Lưu nháp" và "Lưu và hiện cho người đọc".
4. **Ẩn menu WordPress không dùng** (Bài viết, Bình luận…) với vai biên tập viên.

Đợt 1 không cần chờ các quyết định trên.

## 9. Ảnh minh hoạ

| Ảnh | Nội dung |
|---|---|
| `anh/01-dang-nhap-vao-dashboard-wp.png` | Đăng nhập xong vào trang chào mừng WordPress, không vào Bàn làm việc |
| `anh/02-menu-phongdao-mo-nguon.png` | Mục "Phongdao" mở Nguồn & cài đặt; menu có "Sửa tác phẩm", "Lô thao tác" |
| `anh/03-hop-viec-admin.png` | Hộp việc wp-admin: khối lô to ở đầu, bảng chỉ có tên thẻ |
| `anh/04-duyet-tung-the.png` | Duyệt từng thẻ: kiểu nên lấy làm chuẩn |
| `anh/05-the-loi-dinh-dong.png` | Thẻ Nháp ý nghĩa: lời dính dòng |
| `anh/06-sau-khi-quyet-ngo-cut.png` | Sau khi Đăng: ngõ cụt, chữ kỹ thuật |
| `anh/07-sua-tac-pham-gap-kin.png` | Trang sửa: mọi phần gập kín, không có nút trạng thái |
| `anh/08-viet-y-nghia-khong-thay-loi.png` | Viết Ý nghĩa mà không thấy lời |
| `anh/09-kho-sang-hop-viec-15-the.png` | Từ Kho sang Hộp việc: 15 thẻ, không chọn sẵn 5 câu vừa đưa |
| `anh/10-xem-truoc-dang-15-cau.png` | Xem trước lô Đăng: 15 câu |
| `anh/11-tac-pham-khoi-lo-luon-hien.png` | Tác phẩm: khối lô luôn hiện, cột lặp chữ |
| `anh/12-dt-hop-viec-admin.png` | Điện thoại: màn đầu Hộp việc wp-admin toàn bộ lọc |
| `anh/13-dt-tac-pham-admin.png` | Điện thoại: một bài chiếm gần cả màn |
| `anh/14-may-dang-lam.png` | Máy đang làm: lịch 06:00 thật ra chạy 13:00; báo "Chạy theo lịch" khi nguồn đang tắt |
| `anh/15-dt-hop-viec-trang-doc.png` | Hộp việc trên trang đọc (điện thoại): thanh dưới gọn |
| `anh/16-kho.png` | Kho: 30 nút xanh trên một trang |
