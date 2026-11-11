# 30 · Kế hoạch nâng cấp quản trị và biên tập V2

| | |
|---|---|
| Ngày lập | 05/11/2026 |
| Trạng thái | **Kế hoạch triển khai; chưa phải chức năng đã có** |
| Quyết định | D-096; điều chỉnh hướng giao diện của D-094, giữ mô hình dữ liệu và các lằn ranh |
| Phạm vi | Plugin V2 và giao diện quản trị cho người có quyền; không đổi trang đọc của khách |
| Căn cứ | `docs/e2e-review/v2/REPORT.md`, đối chiếu đề xuất duyệt và mã V2 ngày 05/11/2026 |
| Lộ trình chung | `docs/31-ke-hoach-cai-tien-sau-review-v2.md` — các ưu tiên ngoài quản trị và phụ thuộc P0 của Hộp việc |

## 1. Quyết định sản phẩm

**wp-admin là nơi quản trị nội dung chính.** Người biên tập mở danh sách Tác phẩm để tìm, lọc, sửa, rà dữ liệu thiếu và xử lý hàng loạt; mở Hộp việc để quyết các đề xuất, góp ý và việc cần xem. Nút Sửa trên trang đọc tiếp tục là lối sửa nhanh một bài, dùng cùng quy tắc lưu. Chỉ chuyển các đường `/bien-tap/…` sang trang admin tương ứng sau khi chức năng trên điện thoại đạt tương đương và liên kết cũ được kiểm.

Các lỗi Hộp việc đang chạy và nhất quán quyết định thuộc T-176/T-177 trong `docs/31`; màn admin T-168 kế thừa nghiệp vụ quyết và các bản sửa đã có, tự kiểm từng hành động trong admin. Theo D-097, T-168 không chờ các ca lỗi hiếm chống quyết trùng đã bỏ. Ưu tiên lỗi trang đọc, cập nhật an toàn, đối soát dữ liệu và tìm kiếm được điều phối cùng nhánh quản trị trong kế hoạch chung.

Tác phẩm đã là loại bài `tac_pham` của WordPress. Các phần có cấu trúc nằm trong post meta của tác phẩm, phân loại nằm trong taxonomy, hồ sơ nguồn là loại bài ẩn, việc chờ và nhật ký nằm trong `wp_cadao_viec`. **Kế hoạch này không đổi loại bài, khoá meta, bảng việc, URL tác phẩm hay chính sách nguồn.** Không biến mỗi thẻ Hộp việc thành một bài viết; không đưa tác phẩm vào ô nội dung tự do của Gutenberg. Hiển thị menu WordPress chỉ là bước đầu; biểu mẫu vẫn phải ghi qua dịch vụ kiểm và lưu của Phongdao.

Hộp việc là **hàng chờ quyết định**, không phải danh mục toàn bộ kho. Một tác phẩm có thể có nhiều việc đang chờ. Danh sách Tác phẩm phải tìm được cả bài không có việc, bài đã đăng, bản nháp, bài đã ẩn và mục trong thùng rác để người có quyền khôi phục.

## 2. Màn hình và công việc hằng ngày

| Màn | Người biên tập làm được | Điều kiện thiết kế |
|---|---|---|
| Tổng quan Phongdao | Thấy số việc chờ theo loại, bài thiếu phần, lô có lỗi; đi thẳng vào danh sách đã lọc | Số đếm lấy từ toàn bộ dữ liệu, ghi thời điểm cập nhật; không dùng giới hạn danh sách làm tổng |
| Tác phẩm | Tìm ID/tên/lời, lọc trạng thái, thể loại, chủ đề, mức hoàn thiện, cờ và nguồn; chọn nhiều bài; xem thùng rác | Phân trang ở máy chủ; cột ID, trạng thái, phần thiếu, nguồn, lần sửa cuối; chỉ tải dữ liệu cần cho trang |
| Kho | Tự tìm/lọc câu đã thu thập theo nguồn, thể loại, trạng thái; xem căn cứ và tự chọn câu đưa vào Hộp việc | Kho là danh sách tư liệu riêng, không phải Hộp việc. Nút chỉ tạo thẻ Câu mới đang chờ, không đăng, gộp, duyệt hay sửa lời; kiểm quyền, nonce, trạng thái câu và thẻ trùng trước khi ghi. Chọn nhiều câu hoặc mọi câu khớp bộ lọc tạo lô "Đưa vào Hộp việc" có xem trước (D-099) |
| Sửa tác phẩm | Sửa Lời, Ý nghĩa, Từ khó, Dị bản, phần theo thể loại, phân loại, nguồn và xem lịch sử | Biểu mẫu theo phần, hiển thị nguồn gốc từng phần, báo việc đang chờ; lưu qua `LuuPhan`/`KiemPhan`; liên kết xem trước trang đọc |
| Hộp việc | Xem Chờ, Để sau, Xong, Bỏ; tìm, lọc, quyết từng thẻ hoặc nhóm cùng loại | Số lượng chính xác; việc đã hoãn tìm lại được; một thẻ mở được tác phẩm liên quan |
| Nguồn và danh mục | Xem hồ sơ nguồn, mục từ, địa danh, phân loại và quan hệ | Giữ quyền `cadao_nguon` cho thay đổi chính sách nguồn; không mở trường nội bộ qua giao diện mặc định |
| Lịch sử | Xem khác biệt trước/sau, người và thời điểm; khôi phục một phần hoặc một lô hợp lệ | Khôi phục là lần ghi mới; không ghi đè thay đổi mới hơn |

Màn máy tính ưu tiên bảng và bộ lọc; màn điện thoại ưu tiên danh sách gọn, mở từng bản ghi và nút hành động rõ. Không cần tái tạo toàn bộ wp-admin theo bộ thiết kế trang người đọc. Màn nhóm B trong `design/v2/` là mẫu tham khảo cho luồng sửa nhanh cũ; giao diện admin mới cần được duyệt bằng bản xem trước riêng trước khi thay lối vào đang dùng.

## 3. Hợp đồng thao tác hàng loạt

| Thao tác | Đầu vào và xem trước | Khi áp dụng |
|---|---|---|
| Duyệt gợi ý phân loại | Chỉ chọn các thẻ cùng loại; xem bài, gợi ý, lý do; bỏ chọn ngoại lệ | Người có quyền `cadao_duyet` quyết từng thẻ; ghi người quyết và kết quả từng mục; không tự gán vùng/nguồn chưa có căn cứ |
| Sửa phân loại | Chọn bài, chọn **Thêm / Gỡ / Thay thế**, giá trị có sẵn; mặc định **Không đổi** | Kiểm quyền từng bài, cập nhật chỉ mục và lịch sử; không tạo mục chủ đề/vùng mới qua màn này |
| Sửa nhiều phần nội dung | Danh sách bài cùng một phần, mỗi hàng có giá trị riêng và đối chiếu trước/sau | Người biên tập duyệt từng giá trị; qua cùng dịch vụ lưu một phần; không có ô ghi đè một lời/ý nghĩa lên mọi bài |
| Ẩn / đưa vào thùng rác | Xem ID, trạng thái, số liên kết và việc còn mở; xác nhận tập đã chọn | Chỉ người đủ quyền thực hiện; cập nhật chỉ mục, quan hệ và nhật ký; có đường khôi phục từ thùng rác; xoá vĩnh viễn là hành động riêng do người sáng lập quyết (H6) |
| Để sau / Bỏ đề xuất | Chọn việc cùng loại, xem lý do và đối tượng | Đổi trạng thái việc, giữ dấu vết; không xoá tác phẩm hoặc tư liệu liên quan |

**Chọn phạm vi:** giao diện luôn nói rõ “các mục đã chọn trên trang này” hay “mọi mục khớp bộ lọc”. Phạm vi thứ hai cần xem trước danh sách có phân trang và số lượng trước khi áp dụng. Mỗi lô có mã, người thực hiện, bộ lọc/tập ID đã chốt, thời điểm và kết quả `thành công / bỏ qua / xung đột / lỗi` cho từng mục. Chạy lại cùng lô không được áp dụng trùng. Bài đã đổi sau lúc xem trước phải được bỏ qua và báo xung đột, không ghi đè âm thầm. Xử lý theo lô có giới hạn, tiếp tục được sau gián đoạn; không phụ thuộc vào việc mở một trang chứa mọi bản ghi.

**Duyệt nội dung, xuất bản và xoá là ba quyết định khác nhau.** Máy chỉ đề xuất; thao tác hàng loạt không tự đăng bài (riêng lô **Đăng thẻ Câu mới** — D-099 — do người có quyền duyệt bấm sau bước xem trước hiện lời, cờ và nguồn; câu có cờ để sẵn bỏ chọn, câu chờ quyết trùng bị bỏ qua), không tự đặt dấu đã duyệt/có hỗ trợ AI, không tự quyết nguồn, bản quyền, địa danh, dị bản, gộp bài hay xoá vĩnh viễn. Mức thiếu nội dung là bộ lọc và lời nhắc, không thành cổng chặn đăng mới (D-039, D-054).

## 4. Hợp đồng kỹ thuật

1. **Một đường ghi:** giao diện admin, sửa nhanh trên trang đọc và xử lý lô gọi cùng lớp nghiệp vụ. Kiểm `cadao_soan`/`cadao_duyet`/`cadao_nguon` và quyền trên từng đối tượng tại máy chủ; nonce chỉ chống gửi nhầm, không thay quyền. Không cấp quyền sửa `_phongdao_*` trực tiếp qua REST mặc định hay ô “Trường tuỳ chỉnh”.
2. **Nguồn dữ liệu giữ nguyên:** `tac_pham` + taxonomy + post meta có cấu trúc, `ho_so_nguon`, `wp_cadao_viec`. `post_content` vẫn là bản chữ dẫn xuất; mọi lần sửa phần cập nhật bản chữ và chỉ mục tìm theo quy tắc hiện có.
3. **Truy vấn có phân trang:** danh sách việc phải truy được mọi trạng thái và đếm chính xác theo bộ lọc; thêm chỉ mục sau khi đo truy vấn trên bản thử, tránh N+1 khi hiện nguồn, phần thiếu và việc liên quan. Bộ đếm không dựa trên `LIMIT 500/1000`.
4. **Lịch sử có thể dùng:** lưu giá trị trước/sau và phiên bản đối tượng cho thao tác sửa, quyết việc, đổi trạng thái, nguồn và lô; khôi phục một phần chỉ khi phiên bản hiện tại còn phù hợp, ghi sự kiện khôi phục mới. Hoàn tác 10 giây là tiện ích nhanh, không thay lịch sử dài hạn.
5. **Triển khai tăng dần:** thêm menu và trang admin sau quyền; giữ các đường sửa hiện có hoạt động trong lúc kiểm tương đương. Chuyển đường tắt/đường cũ cuối cùng, có cách quay lại giao diện cũ nếu phát hiện lỗi; không chuyển dữ liệu của tác phẩm hay việc.
6. **Kho do người quản trị chọn:** danh sách kho dùng đếm và phân trang thật, hiện nguồn gốc và trạng thái đã có; nút “Đưa vào Hộp việc” chỉ có cho câu mới chưa gắn bài và chưa có thẻ Câu mới đang mở. Máy chủ kiểm lại quyền, nonce, phiên/trạng thái khi bấm; bấm lại không tạo thẻ đôi. Người biên soạn tự chọn câu, người có quyền duyệt quyết trong Hộp việc; không tự đưa câu lên trang đọc hay xoá dữ liệu kho.

## 5. Lộ trình và điều kiện nghiệm thu

| Chặng | Task | Kết quả kiểm được |
|---|---|---|
| Nền admin | T-167 | Menu Phongdao rõ vai trò; danh sách Tác phẩm đầy đủ trạng thái, bộ lọc/phân trang/số đếm đúng; liên kết tới bài và việc; không mở sửa meta thô |
| Hàng chờ | T-168 | Hộp việc trong admin có Chờ/Để sau/Xong/Bỏ, tìm và phân trang toàn kho; quyết một thẻ bằng nghiệp vụ cũ; trang điện thoại dùng được |
| Sửa có cấu trúc | T-169 | Sửa một bài trong admin qua cùng dịch vụ với sửa trên trang đọc; đối chiếu phần, nguồn gốc và nháp chờ duyệt đúng; khách không thấy dữ liệu nội bộ |
| Lịch sử và khôi phục | T-170 | So trước/sau, khôi phục có kiểm xung đột và nhật ký mới; hồ sơ nguồn có dấu vết; bài sửa tiếp không bị ghi đè |
| Xử lý lô | T-171 | Duyệt nhóm gợi ý, sửa phân loại, sửa nhiều phần, ẩn/thùng rác và đổi trạng thái việc theo hợp đồng mục 3; kết quả từng mục, chạy lại an toàn |
| Chuyển lối vào, nghiệm thu | T-172 | Người sáng lập thử ba kịch bản dưới đây trên máy tính và điện thoại; liên kết cũ đi đúng màn; cổng kiểm tự động đạt |
| Kho để tự chọn câu | T-184 | Từ danh sách kho, người quản trị lọc được câu và đưa một câu đủ điều kiện vào Hộp việc đúng một lần; quyết định xuất bản vẫn ở Hộp việc |

**Ba kịch bản dùng thử:** (1) tìm toàn bộ bài thiếu Ý nghĩa, sửa ba bài và quay về đúng bộ lọc; (2) duyệt một nhóm gợi ý có hai ngoại lệ, kiểm kết quả từng bài; (3) ẩn một nhóm bài thử, khôi phục một mục và kiểm lịch sử. Dùng dữ liệu thử có nhãn, dọn sạch sau kiểm; không quyết nội dung văn hoá thật thay người biên tập. Ghi thời gian, số thao tác và lỗi của từng kịch bản; mục tiêu là giảm thao tác so với đường đang dùng, chưa tuyên bố “tối ưu” trước khi đo.

Trên bản thử, kiểm thêm quyền và bàn giao bằng vai mẫu: người góp chứng cứ tạo nháp, biên tập viên xử lý, người có quyền duyệt ra quyết định. Chỉ thêm màn phân công và hạn xử lý nếu quy trình hai người thực sự cần sau khi T-177 đã xử lý xung đột.

**30 ngày:** hoàn tất nền admin, danh sách và hàng chờ. **90 ngày:** sửa có cấu trúc, lịch sử và xử lý lô được thử trên bản xem trước. **6 tháng:** hoàn thiện các màn nguồn, danh mục, báo cáo chất lượng và phân công nếu đội có từ hai người; chỉ mở rộng khi số liệu dùng thật cho thấy cần. Các mốc là thứ tự đầu tư, phụ thuộc nguồn lực và kết quả thử, không phải cam kết lịch triển khai.

## 6. Ranh giới triển khai

- Đây là kế hoạch tài liệu. Website đang chạy vẫn theo D-094 cho tới khi từng task hoàn tất, kiểm thử và được đưa lên máy chủ.
- Không viết lại WordPress, không thay mô hình tác phẩm/bảng việc để có màn admin, không tạo `post` mới cho mỗi công việc.
- Không tự thay đổi nguồn, quyền sử dụng, lời, dị bản, trạng thái đã duyệt hay dữ liệu thật khi kiểm thử. H1/H2/H3/H5/H6 của `AGENTS.md` vẫn áp dụng cho hành động sau này.
- Sau mỗi chặng: `npm run kiem-tra`, `npm run test:v2`, `npm run quet:v2`; thử quyền theo vai, trang khách, điện thoại và trường hợp dữ liệu lớn. Trước khi chuyển lối vào chính phải có bản xem trước và kết quả dùng thử của người biên tập.
