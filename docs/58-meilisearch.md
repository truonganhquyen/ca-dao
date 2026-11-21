# 58 · Meilisearch — tìm gần và kiểm trùng

Ngày 21/11/2026 · D-131 · T-239 · đã bật trên site thật với plugin 1.0.92.

Người sáng lập chốt Meilisearch làm hướng chính sau PoC. Tích hợp nhỏ trong plugin hiện có, không tạo màn quản trị hay luồng biên tập mới. Đây là phạm vi riêng đã được yêu cầu, không mở rộng PoC schema của `docs/57`.

## Phạm vi nghiệm thu

- Tìm hiện tại giữ nguyên bộ lọc, phân trang, từ điển và gợi ý khi gõ. Khi gửi câu tìm chưa có cụm liền trong lời/dị bản, thêm tối đa năm bài gần trên trang đầu, ghi rõ là gợi ý; kết quả rời trong chú thích không chặn nhóm này. Không nới cụm trong ngoặc kép. Cụm dưới bốn tiếng đang có kết quả thường không thêm nhóm gần.
- Thêm tác phẩm và đóng góp dùng chung chuẩn hoá và đối chiếu. Chỉ khớp toàn bộ lời có dấu sau chuẩn hoá mới là khớp hệt; bỏ dấu hoặc gần giống không chặn như khớp hệt. Đoạn trích phân biệt với toàn văn. Người biên soạn vẫn quyết dị bản, nguồn, thể loại và đăng bài.
- Lõi chống trùng tự động của kho giữ nguyên; chưa thay luật tự nối kho hoặc các quyết định trùng đã lưu. Meilisearch chưa thay mọi nơi tìm trong quản trị.
- NFC, thường hoá, Ð/ð ↔ Đ/đ, dấu kiểu cũ/mới, khoảng trắng đặc biệt, ký tự định dạng ẩn; chỉ trong bản tìm, giữ nguyên lời lưu.
- Mỗi lần tìm gần tối đa một HTTP multi-search (4–20 nhánh, 30 ứng viên/nhánh). Hai nhánh tra băm toàn văn có dấu/không dấu đi riêng, không bị bài chung vài chữ đẩy ra ngoài top tìm gần. Đọc lại lời và quyền từ WordPress, kiểm hash bản chữ rồi mới đối chiếu. Điểm là giống đoạn, không phải xác suất trùng.
- Hai chỉ mục public/staff. Public loại nháp, mật khẩu, cờ thô tục ngay khi xuất, và kiểm lại lúc trả kết quả. Staff chỉ gọi khi có quyền soạn. Không gửi email, bút danh, nguồn/hồ sơ cá nhân vào engine; khoá chỉ phía máy chủ.
- Khi sửa/ẩn/xoá/đổi thể loại, hàng đợi bền chặn sử dụng chỉ mục cũ tới khi xác nhận cả hai index. Hàng đợi kho/góp riêng không làm dừng gợi ý công khai. WP-Cron thử lại; không coi HTTP 202 là đã hoàn tất. Khoá chống hai worker chạy chồng. Dựng lại dùng index mới, chuyển tên sau khi ghi xong.
- Engine lỗi/chưa dựng/đang đồng bộ: trang tìm vẫn dùng tìm hiện tại; Thêm bài báo chưa kiểm được đầy đủ; góp bài dùng cơ chế báo lỗi và giữ chữ hiện có. Không coi lỗi là không trùng.

## Cấu hình và thao tác vận hành

Chỉ chạy trên trang thử hoặc lần triển khai đã được người vận hành cho phép. Không đăng ký dịch vụ, không đổi DNS. Meilisearch ghim `v1.57.0`, chỉ lắng nghe loopback, tắt telemetry, dữ liệu lưu bền, tự khởi động lại; không mở cổng ra Internet. Giới hạn CPU/RAM theo máy thật sau kiểm tải; snapshot PoC không phải cấu hình sizing.

Gói Compose ở `wordpress/may-chu/meilisearch/compose.yaml` để người vận hành xem và chạy nếu máy **đã có Docker Compose**. Giới hạn mẫu 1 GiB/2 CPU phải đối chiếu tài nguyên còn lại của WordPress; chưa đo tải thật. Đặt biến khoá trong môi trường dịch vụ riêng, không commit tệp `.env`. Không chạy `docker compose config` vào log/chụp màn vì có thể hiện khoá. Không tự cài Docker hay thay cấu hình máy chủ. Máy WordPress chạy trong container cần thiết kế kết nối nội bộ riêng; mẫu loopback này dành cho PHP chạy trực tiếp trên host.

Trong cấu hình WordPress ngoài repo, đặt `PHONGDAO_MEILI_BAT=true`, `PHONGDAO_MEILI_URL` dạng `http://127.0.0.1:7700`, `PHONGDAO_MEILI_KEY` là khoá riêng đủ mạnh. Tạo khoá trực tiếp tại máy chủ, không gửi qua chat, không đưa vào repo. Bản đầu chỉ chấp nhận loopback IPv4; PHP và Meili phải cùng host/network namespace.

Chạy `wp phongdao meili --dung-lai`; sau đó `wp phongdao meili` phải báo sẵn sàng. Lệnh dựng lại cũng xử lý hàng đợi phát sinh trong quá trình dựng. WP-Cron tự đồng bộ các lần sửa sau đó; có thể chạy `wp phongdao meili --dong-bo` khi cần. Các lệnh chỉ ghi chỉ mục/hàng đợi, không sửa lời hay trạng thái tác phẩm.

Khoá `phongdao_meili_khoa` không tự hết hạn để tránh hai lượt ghi chồng. Nếu tiến trình bị dừng cứng: kiểm chắc không còn worker/CLI đang chạy, rồi người vận hành xoá riêng option khoá và chạy đồng bộ lại. Build lỗi trước khi đổi index giữ index hoạt động cũ; chỉ mục dựng dở cần dọn riêng theo tên của lượt lỗi. Hàng đợi chỉ xoá bằng điều kiện token khớp, nên lượt sửa mới không bị lượt cũ xác nhận nhầm.

Hoàn tác: đặt `PHONGDAO_MEILI_BAT=false`; các đường tìm/so trùng trở về hành vi trước. Không cần hoàn tác nội dung. Dừng dịch vụ sau đó nếu không còn dùng; giữ dữ liệu chỉ mục để điều tra khi cần. Khi bật lại phải dựng lại toàn bộ trước để bù thay đổi xảy ra trong lúc tắt.

## Kiểm chứng

Các kiểm thử WordPress nằm trong `npm run test:v2`: mặc định tắt, hai phạm vi, mật khẩu/ẩn, task thất bại, thử lại, hàng đợi, một HTTP multi-search, kiểm hash lời hiện tại, quyền staff, thông báo admin. Kiểm thuần: chuẩn hoá và phân biệt toàn văn/đoạn/khác dấu/câu dài.

Chạy đủ `kiem-tra`, `test:v2`, `quet:v2`, `e2e:v2`. Bản thử engine thật lấy lời công khai vào thư mục riêng ngoài repo, chỉ lưu số đo; kết quả và giới hạn bổ sung sau khi chạy. Truy vấn sinh biến đổi kỹ thuật chưa thay thế bộ nhãn do con người đánh giá, không suy độ chính xác toàn kho từ vài tác phẩm.

Để bật site thật cần: bộ đo đại diện, kiểm engine thật; CI đủ; thử dựng/đồng bộ/ẩn/xoá/dị bản; kiểm service, cron, quyền, hết kết nối; xác nhận cấu hình triển khai cụ thể. Chưa có bằng chứng thì ghi chưa đạt, không suy từ mã đã viết.

### Kết quả hồi quy engine thật — 21/11/2026

Chạy `scripts/poc-search/validate-meili.py` với binary Meilisearch 1.57.0, dùng đúng lớp PHP `SoKhopLoi` của plugin qua stdin. 100 tác phẩm công khai trong sơ đồ trang: ca dao 48, tục ngữ 30, vè 10, câu đố 7, đồng dao 5; tổng 138 phần lời/dị bản. 952 ca (toàn văn, NFD, không dấu, khoảng trắng Unicode, Ð/Đ, một dòng, cuối bài, thêm/bớt/thay một tiếng): bài đích top 5 **952/952**, top 1 **951/952**. Một ca âm không trả bài. Một HTTP multi-search mỗi ca. SHA256 dữ liệu `9ee735cbe3f6de611f5ae757a354ece7d1063625bfdc54dc006528d6d1bc14cb`; mã PHP `40acca5484d2a01d9befd10d1bc27e8c71a9ad8b906042b0b9d8c621a52e1590`.

Đây là hồi quy kỹ thuật: truy vấn sinh tự động, tìm trong 138 phần của mẫu, **chưa tìm trong toàn kho**, chưa có nhãn liên quan độc lập của người đọc, chưa đo một từ phổ biến, truy vấn theo ý nghĩa hay tải đồng thời. Thời gian script gồm tạo tiến trình PHP nên không dùng làm độ trễ sản phẩm. Các luồng WordPress đang kiểm bằng HTTP giả lập có engine trả kết quả lỗi/cũ; chưa phải diễn tập dịch vụ + WordPress trên máy chủ thật. Không công bố “100% chính xác” hoặc nghiệm thu production từ con số này.

Đã mở [PR tích hợp](https://github.com/truonganhquyen/ca-dao/pull/215), có bản xem trước WordPress với công tắc tắt. CI tại PR là bằng chứng từng phiên bản mã. PR PoC trước đã đóng và giữ lịch sử; không gộp nhánh thử có mã task trùng với pilot biên tập.

### Triển khai và kiểm tra site thật — đã hoàn tất

Người sáng lập đã cho truy cập FlashPanel để triển khai. PR #215 đã gộp; plugin 1.0.91 lên site, đủ bốn cổng trên bản chốt (PHP 308, WordPress 856, chuyển đổi 55; axe/ngân sách đạt; E2E 81 bước, 0 lỗi). Docker có sẵn; container riêng loopback, không cấp quyền Docker cho tài khoản website, giới hạn 1 GiB/2 CPU; khoá và bản sao lưu cấu hình/CSDL nằm ngoài web root.

Đã dựng chỉ mục thực tế và kiểm 100 tác phẩm (20 mỗi thể loại), tìm trên toàn chỉ mục: bài đích top 5 ở 792/795 ca, top 10 ở cả ba ca còn lại. Ba ca gồm hai truy vấn một/hai tiếng và một dòng có nhiều tác phẩm cùng lời. Bốn truy vấn người sáng lập nêu đều tìm đúng; mất kết nối báo chưa kiểm được; xoá/tạo lại **mục chỉ mục dẫn xuất** thành công, lời WordPress không đổi; mục riêng không có trong public, có trong staff. Không sửa trạng thái hoặc nội dung tác phẩm để thử.

Kiểm HTTP trang thật phát hiện kết quả rời trong chú thích ngăn gọi gợi ý gần. Đã tạm tắt công tắc, sửa điều kiện ở [PR #217](https://github.com/truonganhquyen/ca-dao/pull/217), chạy lại đủ cổng và bật lại trên plugin 1.0.92. [CI bản sửa](https://github.com/truonganhquyen/ca-dao/actions/runs/44908855120): PHP 308, WordPress 861, chuyển đổi 55; trợ năng/ngân sách đạt; E2E 81 bước, 0 lỗi, 0 bỏ qua, 4 lần bấm lại. `npm run kiem-site-that` xác nhận plugin 1.0.92/theme 1.0.20 đúng main.

Kiểm trình duyệt thật sau khi bật (21/11, khoảng 20:38 giờ Việt Nam):

- Phiên khách tìm câu thay một tiếng: bài gần câu nhớ đứng trước kết quả rời trong chú thích; câu có Ð/Đ cũng trả đúng liên kết bài.
- Trong Thêm tác phẩm: đoạn nhập một dòng báo Liên quan (83% độ giống gợi ý); đủ hai dòng nhưng khác chữ báo Có thể là dị bản (85%). Không coi các điểm này là xác suất trùng. Chỉ nhập để xem, đã xoá chữ thử; không lưu/đăng bài.
- Phiên khách ở Góp: toàn văn đã có hiện “Câu này đã có trên Phong Dao — xem bài”; câu thay một tiếng hiện “Có bài gần giống — xem”. Không gửi form, không thay đồng ý giấy phép hoặc ghi công.
- Dừng/khởi động lại đúng container để kiểm mất kết nối thật: quản trị báo chưa kiểm được, dữ liệu chỉ mục còn nguyên sau khởi động lại. API index không có khoá trả 401, listener chỉ ở loopback; không nới quyền Docker của tài khoản website.
- Xếp lại một mục chỉ mục lúc 20:39:23; tới 20:40:34 cron thật đã xử lý hết hàng đợi, hai chỉ mục sẵn sàng, không có lỗi đồng bộ. Không gọi lệnh đồng bộ tay giữa hai mốc này.
- GET trang Tìm không chạy JavaScript: ba truy vấn nhớ sai/khác Unicode/không dấu đều có liên kết đúng trong HTML; cụm trong ngoặc kép không tự nới, lọc Vè không trả gợi ý Ca dao, tìm một từ vẫn có danh sách thường. Đã xem bố cục 390 px, không tràn ngang.

Hồi quy sinh tự động chưa thay bộ nhãn độc lập của người đọc, chưa phải kiểm tải đồng thời. Hai truy vấn một/hai tiếng trong mẫu chưa có bài đích ở top 5, nên không bảo đảm một từ ngắn sẽ đưa đúng một tác phẩm mong muốn lên đầu.

### Cấu hình đã áp dụng và hoàn tác trên máy thật

Các tệp nằm trong `/home/cadao/.phongdao-meili/` ngoài web root: `meili.env` chứa khoá tự sinh tại máy, `constants.php` khai báo URL nội bộ và chỉ bật khi có tệp `enabled`, `cli.php` cho lượt dựng/kiểm riêng trước khi mở công khai. Tệp riêng quyền 600, thư mục 700. `wp-config.php` nạp helper trước `wp-settings.php`, đã sao lưu và kiểm cú pháp trước khi đổi. Container `phongdao-meilisearch` chạy bằng UID/GID của website, root filesystem chỉ đọc, không capabilities mới, bind volume riêng; image v1.57.0 đã ghi digest trong biên bản vận hành riêng.

Tắt nhanh bằng đổi tên `/home/cadao/.phongdao-meili/enabled` sang `enabled.off`; không cần thay `wp-config.php` hoặc phục hồi CSDL. Đồng bộ lại bằng WP-CLI với `--require=/home/cadao/.phongdao-meili/cli.php` trước khi tạo lại tệp bật. Không in `docker inspect` toàn bộ hoặc nội dung `meili.env` vào log, vì có khoá. Bản sao CSDL/cấu hình trước triển khai ở thư mục `backup/` riêng, không đưa vào repo. Các script kiểm cũng ở thư mục riêng, chỉ xuất số đo không có lời hay dữ liệu cá nhân.
