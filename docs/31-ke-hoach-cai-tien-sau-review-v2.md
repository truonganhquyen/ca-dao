# 31 · Kế hoạch cải tiến tổng thể sau review V2

| | |
|---|---|
| Ngày | 05/11/2026 |
| Trạng thái | **Kế hoạch triển khai; các mục ghi điều kiện chưa phải quyết định xây chức năng** |
| Phạm vi | An toàn vận hành, biên tập, nguồn, chất lượng dữ liệu, tìm kiếm, hiệu năng và trải nghiệm người đọc |
| Căn cứ | `docs/e2e-review/v2/REPORT.md`; đối chiếu bản đề xuất 0.1–4.4 và hai bản review người dùng cung cấp tại workspace (chưa thuộc PR này); khảo sát quản trị và mã V2 ngày 05/11/2026 |
| Kế hoạch chuyên sâu | `docs/luu-tru/30-ke-hoach-quan-tri-bien-tap-v2.md` — nhánh quản trị theo D-096 (đã làm; tiếp theo là đợt 3, `docs/32`) |

## 1. Kết quả cần đạt

1. **Vận hành tin cậy:** cập nhật và quay lại bản trước được diễn tập; một thẻ chỉ có một quyết định hợp lệ; việc hoãn tìm lại được; số đếm và tài liệu trạng thái khớp thực tế.
2. **Biên tập có căn cứ:** người quyết thấy nguồn, phần sẽ thay đổi và lịch sử; có thể sửa, duyệt hoặc khôi phục mà không cần can thiệp cơ sở dữ liệu. Mọi quyết định nội dung và quyền vẫn thuộc con người.
3. **Kho hữu ích hơn:** chọn bài có nhu cầu đọc để bổ sung Ý nghĩa, từ khó và đối chiếu; tìm và khám phá không bỏ sót vì giới hạn ứng viên; trang đọc đạt trợ năng và ngân sách tải.
4. **Đầu tư có điều kiện:** chỉ mở nguồn, thể loại, lớp học, cộng đồng hay bản đồ mở rộng khi có căn cứ nội dung, quyền và khả năng vận hành. Giữ WordPress và mô hình dữ liệu V2; xem xét đổi kiến trúc chỉ sau phép đo.

Đây là **kế hoạch tổng**. D-096 và `docs/luu-tru/30-ke-hoach-quan-tri-bien-tap-v2.md` quyết định hướng quản trị; bản đề xuất 0.1–4.4 là đầu vào để xếp việc, **không tự biến mọi ý tưởng trong bản đó thành quyết định D-xxx**. Các hạng mục cần H1–H6 chỉ tiến tới bước nghiên cứu/bản mẫu trong phạm vi chưa cần quyết định; không chốt dữ liệu văn hoá, quyền nguồn, giao diện lớn hoặc mở công khai thay người sáng lập.

## 2. Danh mục ưu tiên có thể giao việc

| Ưu tiên | Kết quả | Task | Tiêu chí nghiệm thu trọng tâm |
|---|---|---|---|
| **P0** | Đóng lỗi người đọc còn mở từ E2E: vùng chạm Bản đồ, ảnh trang bài vượt ngân sách; sửa đường “Thiếu ý nghĩa” của câu đố, tràn ngang 320 px và lối Quyền riêng tư theo đặc tả | T-174 | Kiểm lại trên dữ liệu đủ dày và khổ 320/360/390/768; axe WCAG 2.2 AA, trang bài ≤ 200 KiB theo cách đo repo; bộ lọc dẫn tới thao tác thật. Không đổi chính sách riêng tư khi sửa đường dẫn |
| **P0** | Cập nhật và quay lại bản phần mềm an toàn, hướng dẫn V2↔MVP rõ thứ tự; trigger CI bao phủ đầu vào bộ kiểm | T-175 | Diễn tập lỗi ngay sau bước chuyển thư mục trên bản sao, bản cũ còn dùng/khôi phục được; smoke gồm trang chủ, bài và biên tập; đối chiếu hướng dẫn quay dữ liệu. D-090 không đặt cổng thử khôi phục bản sao lưu FlashPanel; thay công cụ kiểm phải kích hoạt workflow tương ứng |
| **P0** | Hộp việc hiện có không mất thẻ khỏi tầm nhìn; sửa màn Duyệt từng thẻ trắng sau thẻ cuối; đếm/phân trang toàn kho, Để sau/đưa lại, Hoàn tác quyết định, phím tắt bàn phím | T-176 | Thử với hàng nghìn thẻ; mọi trạng thái tìm lại được; hai màn đếm cùng tập ra cùng số; thẻ cuối trả trạng thái hết việc; Hoàn tác đúng loại quyết định và báo trường hợp không thể hoàn tác; phím tắt không kích hoạt khi đang nhập liệu |
| **P0** | Quyết việc và đổi trạng thái bài an toàn khi bấm đồng thời hoặc ghi thất bại | T-177 | Chỉ một quyết định thắng trên cùng phiên bản; cập nhật nội dung, trạng thái và nhật ký nhất quán; thử hai phiên, lỗi DB và chạy lại yêu cầu trên bản thử. D-097 bỏ phần triển khai thêm cho hai ca lỗi hiếm chống quyết trùng, ghi rủi ro còn lại |
| **P0** | Đối soát trạng thái dự án và dữ liệu sau chuyển đổi | T-178 | Danh sách ID giải thích chênh 630/631, vè có Đoạn và các đường chuyển 301; cập nhật backlog theo bằng chứng triển khai; không sửa bài để ép khớp số, không tự đóng thẻ Cần xem |
| **P1** | Quản trị nội dung chính trong wp-admin, Hộp việc, lịch sử và thao tác lô | T-167..T-172 | Theo `docs/luu-tru/30-ke-hoach-quan-tri-bien-tap-v2.md`; dùng chung dịch vụ, quyền từng đối tượng, kiểm xung đột; bản xem trước và dùng thử trước khi đổi lối vào |
| **P1** | Màn Kho riêng để người quản trị tìm và tự chọn câu đưa vào Hộp việc | T-184 | Theo `docs/luu-tru/30-ke-hoach-quan-tri-bien-tap-v2.md`; chỉ tạo thẻ Câu mới đúng một lần cho câu đủ điều kiện, không đăng hay quyết nội dung thay người |
| **P1** | Hồ sơ nguồn và nội dung máy đưa vào có thể kiểm, ẩn và truy vết | T-179 | Hiện căn cứ/đường dẫn nguồn và phần chịu ảnh hưởng trước quyết; nhật ký trước/sau của hồ sơ nguồn; phân quyền đổi chính sách; sửa/ẩn/gỡ từng phần theo lằn ranh và quyền hiện có, không tự đổi giấy phép |
| **P1** | Bàn làm việc và chất lượng kho: việc cần làm ngắn, danh sách ngoại lệ, thí điểm nội dung chuyên sâu | T-180 | Số việc vào/ra, tuổi việc và 10 việc nên làm tính từ dữ liệu hiện có; lọc bài thiếu, nghi trùng, nguồn chưa rõ; người biên tập chọn 20–30 bài để thử, đo thời gian và tác vụ hiểu/tra nguồn; không đặt chỉ tiêu điền kín cả kho |
| **P1** | Tìm kiếm đủ và ổn định khi kho lớn | T-181 | Tập truy vấn đại diện và kho mẫu 1.000–10.000 bài; tổng và thứ tự không phụ thuộc 500 ứng viên chưa sắp; đo thời gian và truy vấn DB trước/sau, giữ thuật toán tìm không dấu |
| **P2** | Đo rồi tối ưu Bản đồ, Số liệu tuần, ảnh và kho nội dung | T-182 | Có số truy vấn/thời gian/dung lượng ở mẫu lớn; chỉ tối ưu đường nghẽn đo được; bản đồ và số liệu không truy vấn N+1; ảnh chọn kích thước phù hợp |
| **P2** | Thử nhỏ lối đọc theo chủ đề, bài viết dài và công cụ cho giáo viên | T-183 | Bản mẫu dùng dữ liệu đã duyệt; người dùng hoàn thành tác vụ tìm câu, hiểu nghĩa, xem nguồn, in/chiếu; không thêm phân loại hoặc thay hành vi đọc lớn khi chưa có quyết định H4 |

**Thứ tự P0:** T-175 và T-174 xử lý rủi ro triển khai/trang đọc; T-176 và T-177 xử lý Hộp việc; T-178 chốt bằng chứng dữ liệu và tracker. Các việc này có thể làm thành PR nhỏ độc lập. Theo D-097, T-168 dùng nghiệp vụ và các bản sửa đã có của T-176/T-177, kiểm riêng hành động admin và tiến hành sau T-167/T-176; không đợi phần ca lỗi hiếm chống quyết trùng đã bỏ. Các việc P1 độc lập tiếp tục theo phụ thuộc kỹ thuật, chỉ Human Gate tương ứng cần quyết định của người sáng lập.

## 3. Đối chiếu toàn bộ đề xuất 0.1–4.4

| Mục đề xuất | Cách đưa vào kế hoạch |
|---|---|
| **0.1, 0.2** Duyệt từng thẻ, số đếm/phân trang | **Làm P0:** T-176; giao diện admin kế thừa ở T-168 |
| **0.3** Ý nghĩa câu đố | **Làm P0:** T-174; giữ quy tắc Ý nghĩa do người duyệt |
| **0.4** Cập nhật máy chủ | **Làm P0:** T-175, gồm diễn tập phục hồi |
| **0.5** Tài liệu trạng thái | **Làm P0:** T-178, đối chiếu site và backlog cũ |
| **1.1, 1.2** Hộp việc và hồ sơ bài trong admin | **Đã chốt hướng D-096:** T-167..T-169; `docs/luu-tru/30-ke-hoach-quan-tri-bien-tap-v2.md` |
| **1.3** Bảng điều khiển máy | **Thu gọn P1:** T-179 xem lịch, lỗi, hạn mức và tạm dừng từng việc nền; nút chạy thử/chạy ngay/gỡ lô chỉ thêm sau khi chứng minh phạm vi và đường khôi phục, không gỡ bài đã đăng bằng thao tác máy |
| **1.4** Sửa/ẩn/gỡ nội dung máy đưa vào | **Làm P1 theo quyền từng phần:** T-179 và T-169; kiểm nguồn/giấy phép trước hành động; xoá nội dung gốc đã đăng thuộc H6 |
| **1.5, 1.6** Duyệt nhóm, lịch sử/khôi phục | **Đã nằm trong D-096:** T-170, T-171; xem trước, ngoại lệ, nhật ký từng mục |
| **1.7** Điện thoại biên tập | **Điều kiện nghiệm thu:** T-172 với thiết bị thật và bộ gõ; không chỉ dựa trên giả lập. Trên bản thử, kiểm luồng người góp chứng cứ → biên tập viên sửa nháp → người có quyền duyệt bằng vai mẫu trước khi thêm giao việc/phân công cho đội hai người |
| **1.8** Bài viết dài/Blog | **Thử P2:** T-183 kiểm trước việc bài WordPress/`phan_tich` có hiện đủ thân bài; sửa thiếu sót xác minh được trước, rồi mới xét mục lục, thanh tiến độ, media |
| **2.1** Thêm nguồn thu thập | **Chờ H3 cho từng nguồn:** nghiên cứu bộ đọc và thử đọc trên dữ liệu được phép; không làm công cụ thu thập tuỳ ý hoặc nối Google Sheets ngay |
| **2.2** Gợi ý chính tả | **Thử P2 nếu có mẫu lỗi:** chỉ gắn cờ, giữ nguyên lời/dị bản và tiếng địa phương; D-038 đã chốt quy ước đặt dấu cho văn bản dự án tự viết |
| **2.3** Sắp xếp theo chủ đề | **Thử P2:** T-183 với chủ đề đã duyệt; thay mặc định hoặc thêm trang tổng hợp lớn cần H4 và nội dung người biên tập viết |
| **2.4** Hò–Ru–Lý | **Đường dài hạn:** T-161; mô hình mới qua H1, nguồn/quyền qua H3, không chèn vào đợt sửa lỗi |
| **2.5** SGK | **Đường đã có:** T-159; thử bộ câu nhỏ với giáo viên, SGK và số trang chỉ người đã thấy sách xác nhận |
| **3.1** Bàn luận nhiều loại | **Hoãn phần mở rộng:** trước hết đo khả năng duyệt cảm nghĩ/`phan_tich` đang có; thêm loại nội dung hoặc “Nâng thành Ý nghĩa” cần luồng biên tập và H1/H4 nếu đổi mô hình/hành vi |
| **3.2** Ghi công người góp | **Hoãn tài khoản, email và theo dõi cá nhân:** giữ bút danh/ghi công theo đồng ý; mọi cách liên hệ hoặc hồ sơ người góp cần quyết định quyền riêng tư, không thu email theo AGENTS.md hiện hành |
| **3.3** Mời góp đúng chỗ | **Thử có giới hạn sau P1:** một lời mời tại bài thiếu phần, đi qua Hộp việc hiện có; không mở chiến dịch tạo hàng chờ vượt sức duyệt |
| **3.4** Góc giáo viên với mã lớp | **Hoãn:** tài khoản/lớp và dữ liệu học sinh vượt hướng sản phẩm hiện tại; cần H4 và quyết định dữ liệu cá nhân trước khi thiết kế |
| **4.1** Trò chơi chữ | **Chọn lọc sau thử nội dung:** giữ Đố vui hiện có; trò nhỏ không đăng nhập có thể thử nếu có bài đã duyệt, không tạo điểm thưởng/bảng xếp hạng người đọc |
| **4.2** So câu và dị bản | **Thử P2:** T-183 dùng tên “Tìm câu và dị bản gần nhất”; không gọi bản nào “đúng”, không đưa kho thô lên trang công khai |
| **4.3** Bản đồ văn hoá | **Sửa vùng chạm P0:** T-174; bản đồ nhiều lớp/34 tỉnh là ý tưởng dài hạn cần nguồn quan hệ và H1/H4, phân biệt địa danh trong lời với vùng lưu truyền |
| **4.4** Truyện và ghi âm | **Đường dài hạn:** T-162/T-160; nguồn, quyền và đồng ý ghi âm qua H3; không dùng kế hoạch này để mở thể loại |

## 4. Lộ trình phối hợp với kế hoạch quản trị

| Mốc | Sản phẩm và vận hành | Biên tập, dữ liệu và kỹ thuật |
|---|---|---|
| **30 ngày** | Đóng lỗi E2E ảnh/bản đồ/câu đố; sửa Hộp việc đang chạy; dựng danh sách admin đầu tiên khi nền P0 ổn | Diễn tập cập nhật/rollback; chống quyết trùng; đối soát số bài, 301 và tracker; ghi số việc vào/ra từng tuần |
| **90 ngày** | Hộp việc và trang sửa admin dùng được; nguồn/nhật ký rõ; tìm kiếm ổn định trên kho mẫu lớn | Lịch sử/khôi phục, dashboard việc ngắn, danh sách ngoại lệ; thử 20–30 bài hiểu sâu do người biên tập chọn; đo truy vấn |
| **6 tháng** | Xử lý lô có xem trước và khôi phục; thử lối chủ đề/bộ câu giáo viên hoặc bài viết dài nếu tác vụ thật cần | Tối ưu theo số đo; quyết từng nguồn/thể loại/mô hình cộng đồng ở Human Gate tương ứng; mở rộng chỉ khi đội ngũ xử lý nổi |

**Cổng kiểm:** mỗi task có ca kiểm đúng rủi ro, `npm run kiem-tra`, `npm run test:v2`, `npm run quet:v2` và E2E liên quan. Bản xem trước dùng dữ liệu thử; thay máy chủ cần diễn tập ở bản sao trước khi đưa lên site. Số đo từ review ngày 04–05/11 là mốc khảo sát, phải đo lại trước khi tuyên bố lỗi đã đóng hoặc mục tiêu đã đạt. Mở lập chỉ mục vẫn là H5 của người sáng lập.
