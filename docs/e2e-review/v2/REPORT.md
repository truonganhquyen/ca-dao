# Báo cáo review E2E độc lập — Phongdao V2

| Mục | Kết quả |
|---|---|
| Ngày | 04/11/2026, giờ Việt Nam; hai đợt trước và sau khi triển khai 1.0.3 |
| Bản khảo sát ban đầu | Plugin 1.0.2 / theme 1.0.0; commit `922f636` |
| Bản chốt review | Plugin 1.0.3 / theme 1.0.1; commit `2f66a58`; nhánh `review/e2e-v2` |
| Vai trò | Reviewer độc lập; không viết mã sản phẩm, không sửa nội dung thật, không tự review phần triển khai của phiên này |
| Công cụ | Chrome 156.0.8078.80 mô phỏng viewport/chạm, Playwright, Puppeteer của bộ kiểm repo, axe-core WCAG 2.2 AA; WordPress Playground với dữ liệu tổng hợp; Node 24.18, PHP 8.4.12 |
| Khổ màn hình | 320, 360, 390, 768, 1440 px; sáng/tối. Biên tập: 390 trước, rồi 1440. Một lượt không JavaScript: 55 trang ở 390 px |
| Ma trận giao diện | **820 lượt đo: 792 đạt, 28 hỏng** theo hai điều kiện axe và không tràn ngang. Gồm 730 lượt bản đầu + 90 lượt kiểm lại; 28 là lượt lỗi lặp theo khổ/chế độ, không phải 28 lỗi riêng |
| Luồng có sẵn | Site thật **12 đạt / 0 hỏng / 2 bỏ qua**; Playground **21 đạt / 0 hỏng / 0 bỏ qua** |
| Luồng biên tập bổ sung | **20 đạt / 0 hỏng / 0 bỏ qua** ở lượt cuối; ngoài ra 18 quyết định Hộp việc trên fixture hợp lệ và kiểm ba cấu hình quyền |
| Tổng của ba nhóm định lượng trên | **845 đạt / 28 hỏng / 2 bỏ qua**, tổng 875 lượt. Không cộng kiểm thử đơn vị, đếm bài, hay bước chỉ thu quan sát vào tổng này |
| An toàn | Site thật chỉ khách, GET/HEAD, tuần tự, giãn điều hướng ≥ 1,1 giây; yêu cầu đo lường bị chặn. Không đăng nhập, không gửi đóng góp, không dùng `tai-khoan.env`, không SSH |
| Bằng chứng | [Ảnh công khai](screenshots/); ảnh Playground giữ riêng ở `docs/e2e-review/screenshots/admin/v2/`, không nằm trong PR; [kịch bản và cách chạy](../../../tests/e2e-v2/README.md) |

## Tóm tắt cho người sáng lập

Các luồng đọc chính đã dùng được: tìm, lưu, sao chép, trích dẫn, ảnh chia sẻ, đọc cùng bé, trình chiếu và đọc không JavaScript. Sửa bài, lưu nháp, đăng/ẩn bài và phân quyền đã chạy được trên bản mẫu. Có năm điểm cần lưu ý trước khi mở rộng người đọc:

1. **Bản đồ còn khó bấm trên điện thoại.** Các chấm nhỏ và sát nhau; kiểm trợ năng báo lỗi tại 320, 360 và 768 px.
2. **Một số bài tải nặng hơn mức đã chốt.** Hai bài kiểm lại bằng phiên mới tải khoảng 289 và 444 KiB; ảnh minh hoạ là phần tăng lớn. JavaScript vẫn nằm trong ngân sách.
3. **Quyết một thẻ trong Hộp việc chưa có nút Hoàn tác 10 giây.** Hoàn tác khi sửa một phần bài hoạt động, nhưng chưa có cơ chế tương ứng sau quyết thẻ. Các phím tắt đã hứa cho máy tính cũng chưa hoạt động.
4. **Cách quay lại bản trước cần chắc hơn.** Script có khoảng hở khi thay thư mục thất bại giữa chừng; hướng dẫn bấm Revert chưa cảnh báo đủ sớm về trường hợp quay từ V2 về MVP cần hoàn tác dữ liệu trước.
5. **Còn chỗ cần đối chiếu sau chuyển đổi.** Kho công khai cho 630 bài, tài liệu ngày chuyển ghi 631; tìm được sáu bài vè có tiêu đề đoạn, chưa xác định bài thứ bảy. Đây là chênh lệch cần giải thích, chưa phải bằng chứng mất dữ liệu.

Trong lúc review, một PR khác đã đưa Khám phá, Chủ đề và Mô-típ lên bản 1.0.3. Những liên kết lỗi thấy ở 1.0.2 **không còn được tính là lỗi mở** trong báo cáo này. Đã kiểm lại trang và liên kết bằng bộ E2E mới.

## 1. Phạm vi, cách đọc kết quả

- Đã đọc AGENTS.md, CLAUDE.md, tài liệu nền/trạng thái/quy trình/backlog liên quan, docs/26–29, báo cáo MVP và thiết kế V2. Tài liệu là tiêu chí đối chiếu; yêu cầu **chỉ review, không sửa, không gộp PR** của lần này được giữ nguyên.
- Ma trận đầu: 40 bài chọn giả ngẫu nhiên (8 mỗi thể loại, seed 20261104), 7 ID Cần xem, 26 trang khác; mỗi trang 5 khổ × 2 chế độ. Đợt sau kiểm lại 9 trang × 10 tổ hợp. Ma trận đo trạng thái ban đầu, không có nghĩa mọi hộp mở đã được quét trên mọi khổ.
- Lấy hết 21 trang Kho, sau đó đọc 630 đường dẫn bài công khai để đếm phần hiển thị, SEO, lỗi tải và dấu hiệu kỹ thuật. Chi tiết thô giữ ở thư mục kết quả bị Git bỏ qua.
- Không có lỗi JavaScript hay vi phạm CSP trên các trang trả 200 trong lượt quét; đều có header CSP; không thấy yêu cầu ra máy chủ ngoài từ trang Phongdao. Lỗi console 404 ở các đường dẫn không tồn tại được phân biệt với lỗi thực thi JavaScript. Các lỗi mạng do chính reviewer chặn POST đo lường không quy cho sản phẩm.
- Từng nguồn/bài có nhãn AI, mức kiểm, nguồn hay Wikidata chỉ được **quan sát**, không xác nhận đúng sai hoặc quyền sử dụng. Không suy ra nhãn AI là sai khi không có hồ sơ biên tập để đối chiếu.
- Web Playground gặp màn xác minh truy cập. Đã dùng WordPress Playground CLI tại máy với cùng blueprint và mã V2. Không vượt màn xác minh, không dùng tài khoản site thật. Các cảnh báo tải bản dịch của Playground là hạn chế môi trường, không được ghi thành lỗi PHP của plugin.

## 2. Người đọc: đã đi những luồng nào

| Nhóm | Bằng chứng và kết quả |
|---|---|
| Trang chủ | Câu hôm nay, các lối khám phá, chọn “Bạn là” và nhớ sau tải lại đạt. Ảnh [1440 sáng](screenshots/047-1440-light.png), [1440 tối](screenshots/047-1440-dark.png) thuộc đợt 1 |
| Khám phá và danh sách | Kho phân trang hết 630 bài; năm trang thể loại. Khám phá/Chủ đề/Mô-típ bản 1.0.3 trả 200, 5 khổ × sáng/tối không lỗi axe/tràn. [Khám phá 390](screenshots/new-khampha-390-light.png). Chưa kiểm mọi tổ hợp nhiều bộ lọc và mọi trang phân loại con |
| Trang bài | Mỗi thể loại 8 bài trong ma trận; Ý nghĩa/Từ khó/Dị bản/Lời này từ đâu, đọc tiếp có lý do, công cụ đọc. Chạm chú thích mở tại chỗ. Không thấy dòng nguồn tự điền rõ ràng; không thể suy ra cài đặt bật/tắt nguồn từ HTML công khai |
| Sao chép, trích dẫn, chia sẻ | Sao chép có đường dẫn; ba định dạng trích dẫn có nội dung; đổi APA, Esc đóng và trả focus; hộp chia sẻ có URL bài. Chia sẻ hệ điều hành phụ thuộc thiết bị, không gửi đi ứng dụng thật |
| Lưu và thiết lập | Lưu → Đã lưu → Bỏ lưu đạt. Cỡ chữ Rất lớn và sáng/tối được giữ sau tải lại |
| Ảnh chia sẻ | Đã bấm Tải ảnh, mở được PNG ở ba khổ, đổi ba nền; kiểm bằng mắt không cắt chữ ở mẫu. [Ngang](screenshots/share-ngang.png), [vuông](screenshots/share-vuong.png), [dọc](screenshots/share-doc.png). [Ảnh câu đố](screenshots/share-riddle.png) không có đáp án |
| Trình chiếu | Mở từ Cỡ chữ trên điện thoại, mũi tên chuyển 1/3 → 2/3; Esc thoát, focus về nút Cỡ chữ |
| Đồng dao | Đọc cùng bé: chạm chuyển dòng, Vỗ tay tô chữ; không âm thanh, Esc thoát. [Ảnh](screenshots/kids-390.png) |
| Nghe | Đối chiếu giọng Việt của máy với trạng thái nút; không tự phát. Không đánh giá chất lượng giọng trên nhiều hệ điều hành |
| Câu đố | Đáp án ban đầu ẩn, bấm mở được. Ảnh chia sẻ không lộ đáp án. Chưa có mẫu đủ bằng chứng để kiểm cả gợi ý, ảnh và Wikipedia của đáp án trên site thật |
| Tìm | Có dấu/không dấu cùng 86 kết quả cho “công cha”; gõ dở cập nhật; Enter ra trang kết quả. Chuỗi dài cho 3 bài; chuỗi không có cho trạng thái rỗng. `co^ng` trả kết quả nhưng chưa chứng minh tương đương chuẩn hoá VNI. Không bấm Mong có câu này. [Rỗng](screenshots/search-empty.png) |
| Từ điển | Bấm chữ B → `?chu=B`, tiêu đề “Bắt đầu bằng B”. Mục Bậu có 8 bài; danh sách dẫn tới bài. Mục Công có ảnh và ghi công; mục An Thái có thông tin địa danh. [Từ điển](screenshots/detail-tudien.png), [Công](screenshots/detail-tudiencong2.png), [An Thái](screenshots/detail-tudienanthai.png). Không xác nhận ảnh đúng thực thể thay người biên soạn |
| Bản đồ | Hiện bản đồ tự lưu và danh sách, không gọi dịch vụ ngoài; có lỗi vùng chạm L-01 |
| Tuyển tập | Trạng thái rỗng có nội dung và lối đi; trình chiếu tuyển tập bỏ qua vì chưa có dữ liệu thật. Luồng có tuyển tập mẫu đạt trong E2E Playground |
| Góp | Chỉ xem. Có nhãn, đồng ý sử dụng, thông báo riêng tư và trường bẫy không nhận Tab; không gửi. Phép đo `getClientRects()` riêng lẻ không dùng kết luận ô bẫy bị lộ vì phần tử có thể đặt ngoài vùng nhìn. [Ảnh form](screenshots/detail-gop.png) |
| Trang tĩnh | Về dự án, Phương pháp, Nguồn, Chính sách dữ liệu có nội dung; lỗi tràn 320 ở L-05. Quyền riêng tư riêng chưa có: L-06 |
| Không JavaScript | 55 trang: mẫu 40 + 7 Cần xem + 8 trang chính đều 200, có nội dung chính. Bộ E2E xác nhận lời vẫn đọc được, nút cần JS ẩn |
| SEO | 630 bài đều có title, description, canonical, og:image; noindex hiện diện là **đúng**. Đã tải/mở PNG thực tế ở mẫu ảnh chia sẻ; không tải lại toàn bộ 630 og:image |
| Bàn phím và tương phản | Axe ở 5 khổ, sáng/tối; Esc/focus ở hộp trích dẫn, tấm sửa, trình chiếu; phím mũi tên đã thử. Không coi đây là kiểm chứng toàn bộ thứ tự Tab trên mọi trang hoặc thử bằng trình đọc màn hình |

### Vè dài và xuống dòng

Đã mở Mục lục, chọn đoạn cuối, tải lại thấy “Đọc tiếp từ đoạn…” trên sáu bài dưới đây; mở toàn bài cho đủ số dòng DOM. Dòng đếm theo câu có chữ, không tính số dòng trang trí.

| Đường dẫn | Tổng dòng | Hiện ban đầu | Sau mở toàn bài |
|---|---:|---:|---:|
| `/ve/ve-con-kien/` | 52 | 52 | 52 |
| `/ve/ve-noi-nguoc/` | 225 | 30 | 225 |
| `/ve/ve-banh/` | 275 | 89 | 275 |
| `/ve/ve-ca/` | 492 | 67 | 492 |
| `/ve/ve-chim/` | 115 | 32 | 115 |
| `/ve/ve-noi-lao/` | 72 | 72 | 72 |

“Sau dòng 30” hiện được mã hiểu là **ẩn các đoạn bắt đầu sau dòng 30**, giữ nguyên đoạn đang mở; vì vậy có bài hiện hết 52/72 dòng ngay từ đầu. Quy tắc này ghi rõ ở `wordpress/v2/plugins/ca-dao/src/MienDuLieu/ChiaDoan.php:11,17,74`. Không ghi thành mất dòng; người duyệt sản phẩm cần biết bộ E2E mang tên “sau dòng 30” không kiểm đúng ngưỡng 30 câu hiển thị.

Các câu gãy đo được trong mẫu:

| Bài | Khổ | Câu trong lời | Số dòng hiển thị | Cỡ chữ |
|---|---:|---:|---:|---:|
| `/ca-dao/mot-thuong-toc-bo-duoi-ga/` | 320 | 4 | 2 | 14 px |
| `/cau-do/guom-vang-hai-luoi-guom-vang/` | 320 | 2 | 2 | 14 px |
| Cùng bài câu đố trên | 360 | 2 | 2 | 15 px |

Xảy ra cả sáng/tối; [ca dao](screenshots/000-320-light.png), [câu đố](screenshots/038-320-light.png). Docs/27 mục 1 cho phép xuống dòng ở sàn 14/15 px với thụt treo, nên không tự coi mọi trường hợp này là lỗi. Không thấy tràn ngang ở các trang bài trong ma trận. Phép đo này không bảo đảm mọi câu trong toàn bộ 630 bài đã được đo tại cả năm khổ.

### Đường dẫn cũ

- Đường dẫn được xác định từ báo cáo MVP: `/ca-dao/cong-cha-nhu-nui-thai-son-2/` → **301** → `/ca-dao/cong-cha-nhu-nui-thai-son-nghia/` → 200.
- `/thanh-ngu/an-qua-nho-ke-trong-cay/` → 301 → `/ca-dao/an-qua-nho-ke-trong-cay/` → 200.
- Các phép thử hình dạng `/tinh-huong/hat-ru/` và `/bai/khong-ton-tai-review/` trả trang 404 có nội dung, không trắng. Không dùng hai đường dẫn chưa có bằng chứng lịch sử này để kết luận chuyển hướng thật bị mất.
- **Chưa xác nhận đủ 15/15.** Docs/29 mục 5 ghi tổng 15 đường dẫn đã thử, nhưng không chứa danh sách ánh xạ cụ thể như mô tả nhiệm vụ. Không đoán 14 tên còn lại và không đọc bảng chuyển hướng trên máy chủ. Kiểm thử chuyển đổi trong Playground có xác nhận các dạng đường dẫn cũ, nhưng không thay bằng chứng 15 bài thật.

## 3. Nội dung: quan sát kỹ thuật, không duyệt văn bản

| Thể loại | Bài công khai qua Kho | Có Ý nghĩa | Có Từ khó | Có Dị bản |
|---|---:|---:|---:|---:|
| Ca dao | 482 | 6 | 338 | 137 |
| Tục ngữ – Thành ngữ | 32 | 1 | 21 | 3 |
| Vè | 52 | 0 | 32 | 7 |
| Đồng dao | 32 | 0 | 15 | 5 |
| Câu đố | 32 | 0 | 5 | 0 |
| **Tổng** | **630** | **7** | **411** | **152** |

Số phần đếm theo khối **đã hiện cho khách**, không phải số trường có dữ liệu trong CSDL. Tục ngữ đặt lời ở tiêu đề lớn; không ghi nhầm thành lời trống vì không có `.verse-card .line`. Không phát hiện bài không có lời theo cách hiển thị tương ứng. Mẫu 40 không thấy dị bản trùng toàn bộ lời chính theo chuẩn hoá khoảng trắng, neo chú thích trỏ tới ID không tồn tại, ký tự thay thế hay chuỗi HTML thô. Điều này chưa chứng minh mọi chú thích đều đúng từ, đúng nội dung.

Có 10 nhóm title giống nhau ở các đường dẫn khác nhau, gồm các nhóm Cái bống, Con cò bay lả, Đố ai đếm được lá rừng, Em tôi buồn ngủ, Làm trai, Nu na nu nống, Sông sâu, Thân em, Vè nói ngược, Vè trái cây. Chưa coi đây là lỗi hay đề nghị gộp: có thể là các bản khác nhau cùng tên. Việc phân biệt hoặc gộp thuộc người biên soạn.

Hai trang được so chữ với nguồn đã xác định: [Con công hay múa](https://cadao.me/dong-dao/con-cong-hay-mua/) khớp 13/13 dòng, [Ai bảo thương… trên trang An Thái](https://cadao.me/the/an-thai/) khớp 6/6 dòng sau chuẩn hoá Unicode/khoảng trắng. Đây là kiểm chép thiếu dòng ở hai mẫu, không xác nhận nguồn chuẩn hay quyền chép chú thích. Không đưa bản sao văn bản nguồn vào repo.

### Bảy bài Cần xem

| ID | Đường dẫn nhận được | Quan sát |
|---|---|---|
| 28 | `/ca-dao/con-vua-thi-lai-lam-vua-sai/` | 200; 4 dòng, có Dị bản |
| 709 | `/tuc-ngu/som-rua-cua/` | 200; lời ở tiêu đề, có Ý nghĩa và Từ khó |
| 407 | `/ve/ve-nghia-quan-bang-ninh-ha-thanh-ha-tinh/` | 200; 119 dòng |
| 409 | `/ve/ve-sai-dao/` | 200; 86 dòng |
| 103 | `/ca-dao/khong-chong-ma-chua-moi-ngoan/` | 200; 2 dòng, có Ý nghĩa và Dị bản |
| 382 | `/ve/ve-noi-nguoc/` | 200; 225 dòng, 10 đoạn có tiêu đề |
| 1105 | `/ca-dao/vi-dau-tinh-bau-muon-thoi/` | 200; 4 dòng, có Từ khó và Dị bản |

Không gỡ cờ, không sửa chữ, không đặt “đã duyệt” cho bất kỳ bài nào trong bảng. Không thấy cờ “Nghi không phải dân gian” trong lượt đếm công khai; không suy ra cờ đã được chuyển đầy đủ nếu chưa đối chiếu dữ liệu trước chuyển.

## 4. Các phát hiện còn mở

### (a) Lỗi phần mềm và vận hành

**L-01 · MAJOR — Chấm bản đồ không đủ vùng chạm, có lỗi WCAG 2.2 AA.**

- Nơi gặp: `/ban-do/`, 320/360/768 px, sáng và tối; bản 1.0.3.
- Tái hiện: mở trang, chạy axe với `wcag22aa`; tại 320 có 6 nút bị báo `target-size`, 360 có 3, 768 có 2. Các điểm `#dd-711`, `#dd-156` nằm trong nhóm bị báo.
- Thấy: chấm 28 × 28 px, khoảng cách ở khổ hẹp không đạt ngoại lệ khoảng trống của axe. Mong: vùng bấm ≥ 44 px theo dự án, nhóm điểm không chồng nhau.
- Ảnh: [các điểm ở 320 sáng](screenshots/map-points-320-light.png), [320 tối](screenshots/map-points-320-dark.png).
- Mã: `wordpress/v2/plugins/ca-dao/src/GiaoTiep/Khoi/BanDo.php:94` đặt cố định 28 px. Fixture ít điểm khiến quét Playground không bắt được mật độ thật.

**L-02 · MAJOR — Trang bài có ảnh vượt ngân sách 200 KiB.**

- Nơi gặp: `/dong-dao/con-cong-hay-mua/`, `/ca-dao/em-toi-buon-ngu-buon-nghe/`, phiên mới 390 × 844, sáng, bản 1.0.3.
- Tái hiện: mở bằng context mới, chờ load/phông và 1,2 giây; cộng body của tài nguyên tải được, gzip lại HTML/CSS/JS, dùng dung lượng sẵn nén cho ảnh/phông. Không tính PNG người dùng chủ động tải.
- Thấy: lần lượt **296.068 byte (289,1 KiB)** và **454.770 byte (444,1 KiB)**. JavaScript cùng **9.141 byte (8,9 KiB)**, đạt ≤ 21 KiB. Bài đối chứng `/ca-dao/rong-dong-mac-suc-chim-bay/` đạt 160.233 byte trong phiên mới.
- Mong: trang bài giữ ≤ 204.800 byte theo cách đo của repo. Đây là số gzip dựng lại, không phải tổng byte trên đường truyền HTTP/2; không quy lỗi cho bài đối chứng dựa trên lượt quét dài trước đó.
- Ảnh: [trang bài Đồng dao](screenshots/budget-con-cong-390.png); bằng chứng định lượng là các số và cách đo trên, ảnh màn hình không chứng minh số byte.
- Mã: `wordpress/v2/plugins/ca-dao/src/GiaoTiep/Khoi/DiaDanh.php:54` dùng ảnh gốc đã lưu, có lazy nhưng không chọn kích thước đáp ứng. Ảnh Công tải 136.791 byte; bài còn lại có ba ảnh tổng 294.787 byte. Chưa đưa ra bản sửa.

**L-03 · MAJOR — Quyết thẻ Hộp việc xong không có Hoàn tác 10 giây.**

- Nơi gặp: Playground `/bien-tap/`, 390 và 1440, sáng.
- Tái hiện: với thẻ mẫu, bấm Để sau; kiểm ngay thông báo và nút trong 10 giây. Đã thử các quyết định khác trên fixture: Đăng/Sửa/Để sau/Bỏ câu mới, Duyệt/Bỏ nháp, bốn quyết định trùng, Cần xem, gợi ý và cảm nghĩ.
- Thấy: thông báo “Đã để sau.”, thẻ rời danh sách, **0 nút Hoàn tác**. Mong: có khả năng hoàn tác quyết định vừa bấm theo docs/28:126; người dùng điện thoại có đường sửa thao tác nhầm.
- Ảnh riêng: `docs/e2e-review/screenshots/admin/v2/queue-no-undo-390.png` (không công khai).
- Mã: `wordpress/v2/plugins/ca-dao/src/GiaoTiep/BienTap/HopViec.php:31–40` chỉ chuyển về thông báo `da`; `assets/bien-tap.js:19–34` có Hoàn tác cho lưu phần bài nhưng không được nối vào quyết thẻ. **Không nhầm với Hoàn tác sửa lời: luồng đó đã đạt.**

**L-04 · MINOR — Phím tắt duyệt từng thẻ chưa hoạt động.**

- Nơi gặp: Playground `/bien-tap/?tung=1`, 1440, sáng.
- Tái hiện: có nút Thẻ sau, focus không nằm trong ô nhập, bấm J rồi 1. URL và thẻ không đổi; bấm Thẻ sau bằng chuột chuyển được. Mong: J/K chuyển thẻ, 1/2/3 quyết theo docs/28:110.
- Ảnh riêng: `docs/e2e-review/screenshots/admin/v2/queue-shortcuts-1440.png`.
- Mã: `wordpress/v2/plugins/ca-dao/src/GiaoTiep/BienTap/HopViec.php:60–61` có liên kết trước/sau; `assets/bien-tap.js` không có handler các phím này. Đây cũng là lệch đặc tả biên tập.

**L-05 · MINOR — Nút sáng/tối tràn mép ở trang tĩnh 320 px.**

- Nơi gặp: `/ve-du-an/`, `/phuong-phap/`, `/nguon/`, `/chinh-sach-du-lieu/`, 320, cả sáng/tối, còn ở 1.0.3.
- Tái hiện: viewport 320, đo `documentElement.scrollWidth`: 323. Nút sáng/tối có cạnh phải 322,65625 px. Mong: không tràn ngang ở 320.
- Ảnh: [Về dự án](screenshots/new-veduan-320-light.png), [tối](screenshots/new-veduan-320-dark.png).
- Vị trí thành phần: `wordpress/v2/plugins/ca-dao/src/GiaoTiep/Khoi/KhungTrang.php:68–75`. Chưa quy nguyên nhân cho một rule CSS cụ thể. Các liên kết trong thanh thể loại cuộn riêng không bị nhầm là phần gây tràn toàn trang.

**L-07 · MAJOR — Hoàn tác tự động không bao phủ lỗi giữa lúc thay thư mục.**

- Nơi gặp: review tĩnh `wordpress/may-chu/cap-nhat-plugin.sh:17,53–55,62–70`; không chạy trên máy chủ, khổ/chế độ không áp dụng.
- Tình huống tái hiện để kiểm ở môi trường vận hành riêng: sau khi đổi tên thư mục đang chạy thành `.ca-dao-cu`, cho lệnh chuyển thư mục mới thất bại. Với `set -e`, script thoát trước nhánh kiểm trang chủ; trap EXIT vẫn xoá `.ca-dao-cu`.
- Thấy trong mã: bản cũ có thể bị dọn trước khi phục hồi trong tình huống đó. Mong: phục hồi hoặc giữ bản cũ khi bất kỳ bước thay thư mục nào thất bại, rồi mới dọn sau xác nhận thành công.
- Bằng chứng là luồng điều khiển tệp, không có ảnh hoặc diễn tập lỗi máy chủ. Đây là rủi ro xác định từ mã, **không khẳng định sự cố đã xảy ra**. Mã nằm ngoài `wordpress/v2/` vì đây là công cụ triển khai dùng chung.

**L-08 · MINOR — Hướng dẫn Revert chưa tách sớm trường hợp V2 → MVP.**

- Nơi gặp: `docs/huong-dan/quay-lai-ban-truoc.md:13–21` và đoạn dưới Cách 2; khổ/chế độ/ảnh không áp dụng.
- Tái hiện bằng đọc hướng dẫn như người chưa quen Git: Cách 1 cho bấm Revert rồi Merge trước khi gặp cảnh báo hoàn tác dữ liệu nằm ở Cách 2.
- Mong: trước bước Revert, phân biệt quay giữa các bản V2 với quay qua mốc chuyển V2/MVP; trường hợp sau phải có thứ tự hoàn tác dữ liệu và kiểm bài theo docs/29. Phần cuối có nói mã không hoàn tác dữ liệu, nhưng chưa đủ để ngăn làm sai thứ tự.

**L-09 · MINOR — Thay đổi một số công cụ kiểm không kích hoạt workflow V2.**

- Nơi gặp: `.github/workflows/v2.yml:6–25`; review tĩnh, ảnh/khổ/chế độ không áp dụng.
- Tái hiện bằng đối chiếu paths: chỉ đổi `scripts/e2e-v2.mjs`, `scripts/lib/playground-v2.mjs`, `.node-version` hoặc `package-lock.json` không khớp danh sách chạy workflow này. Mong: các đầu vào của bộ kiểm V2 cũng kích hoạt kiểm V2.
- Không nói CI hiện thất bại: các lệnh đã chạy đạt tại máy. Điểm yếu là thay đổi công cụ có thể lọt qua trigger. Các kịch bản review bổ sung hiện chạy thủ công, chưa được nối vào CI; báo cáo không âm thầm sửa workflow.

### (b) Bất thường dữ liệu/đối chiếu sau chuyển đổi

**L-10 · MINOR — Chưa khớp số bài công khai và số vè nhiều phần với hồ sơ ngày chuyển.**

- Nơi gặp: Kho 21 trang, toàn bộ bài công khai; không phụ thuộc chế độ sáng/tối. Docs/29:90–107 ghi 631 bài, 7 bài có Đoạn; lượt đọc công khai cho 630 và tìm được 6 bài có tiêu đề đoạn.
- Tái hiện: theo hết phân trang Kho, khử trùng đường dẫn, đếm theo thể loại; tìm đoạn có tiêu đề trong 52 vè. Mong: có giải thích chênh lệch và xác định bài thứ bảy.
- Ảnh tham chiếu số thể loại: [trang chủ 1440](screenshots/047-1440-light.png). Không có quyền đọc trạng thái bài ẩn hay nhật ký thay đổi thật nên **chưa kết luận mất bài/đoạn**, cũng không đề xuất đăng lại hay đổi dữ liệu. Không gán nguyên nhân cho mã nhập khi chưa đủ bằng chứng.

### (c) Khác thiết kế/đặc tả

**L-06 · MINOR — Chưa có lối tới trang Quyền riêng tư riêng như docs/27.**

- Nơi gặp: chân trang ở 390/1440, sáng/tối; `/quyen-rieng-tu/` trả 404 có giao diện. Footer hiện có Về dự án/Phương pháp/Nguồn/Chính sách dữ liệu/Góp, không có Quyền riêng tư như đặc tả.
- Tái hiện: xem chân trang; mở đường dẫn trên. Mong: thống nhất trang và lối đi đã chốt, hoặc ghi rõ chủ đích thay bằng thông báo tại form. Không đánh giá đây là vi phạm pháp luật.
- Ảnh: [trang 404 Quyền riêng tư và chân trang](screenshots/privacy-404.png). `wordpress/v2/plugins/ca-dao/src/GiaoTiep/Khoi/Ve.php:36,86–100` ánh xạ đường dẫn và chỉ hiện liên kết khi trang đã đăng; footer dùng điều kiện đó ở `KhungTrang.php:81–82`. Phần Góp đã có thông báo riêng tư ngay tại form.

L-03/L-04 cũng là lệch docs/28. Token CSS V2 khớp bộ thiết kế theo cổng `dong-bo-giao-dien-v2 --kiem`. Đã đối chiếu mẫu bài R05 ở 320; không thấy căn cứ để báo lỗi căn lề chỉ từ câu mô tả khi ảnh mẫu dùng cùng bố cục. Chưa kiểm bằng mắt mọi trạng thái trong toàn bộ 72 màn thiết kế; không gọi kết quả này là nghiệm thu đầy đủ thiết kế.

### (d) Human Gate — chỉ liệt kê

- Quyết định mở Google vẫn do người sáng lập quyết; noindex hiện tại được giữ nguyên.
- Bảy thẻ Cần xem, nguồn/đối chiếu sách, nhãn AI/đã duyệt, Wikidata, quyền dùng nguồn: người biên soạn xác nhận.
- Các nhóm trùng tiêu đề hoặc khác số lượng cần đối chiếu trước khi quyết định gộp, ẩn, đăng lại hay đổi đường dẫn. Reviewer không quyết thay.

## 5. Biên tập, quản trị và kiểm tự động

20 bước bổ sung ở lượt cuối đạt: tách khách/quản trị, ý nghĩa chưa duyệt vào Hộp việc và chưa lộ cho khách, bỏ nháp, sửa lời/Hoàn tác, mười tấm sửa (Từ khó, Dị bản, Đoạn, Bối cảnh, Câu hỏi, Gợi ý, Đáp án, Nghĩa đen, Nghĩa bóng, Dùng khi), lịch sử/Xem như người đọc, nhận thể thơ/báo trùng/tên dòng đầu/Lưu nháp, Đăng/Ẩn, từ chối REST khách, lưu mục từ và axe trên năm trang ở hai khổ. Nguồn để trống không tự điền; ô đã duyệt không tự chọn trong lượt tạo nháp. Các dấu có sẵn của fixture được phân biệt với giá trị máy vừa tự gán.

18 quyết định thẻ trên fixture hợp lệ đều có hiệu lực tương ứng; Sửa rồi đăng mở bản nháp, các quyết định còn lại đóng hoặc để sau thẻ và có thông báo. Thẻ trước/sau dùng được. Bộ E2E có sẵn còn xác nhận góp báo lỗi vào Hộp việc và bấm “Đã xử lý” đóng thẻ. Chưa phủ mọi tổ hợp lọc Tác phẩm, thay ảnh riêng/ghi công và ẩn ảnh Wikidata qua lần lưu có thay đổi. Những việc này không được suy thành đã đạt chỉ vì trang mở được hoặc kiểm PHP đạt.

Quyền: admin có giao diện; editor thấy Lưu nháp/Đăng; vai Cộng tác không vào trang thêm/sửa; cấu hình chỉ có `cadao_soan` không thấy Đăng. Khách không có nút sửa, ghi REST không nonce trả 401. Trang đã đăng nhập có `Cache-Control: private, no-store`; kiểm phiên khách riêng không nhận nút sửa. Đây là bằng chứng ở Playground, chưa là phép thử cache tầng ngoài cho tài khoản thật — theo lằn ranh không đăng nhập site thật.

wp-admin có menu Phongdao/Nguồn & cài đặt/Hộp việc; không thấy menu riêng thừa của plugin MVP. Các menu Bài viết/Trang/Bình luận mặc định WordPress không tự coi là menu MVP. Không thấy Fatal/Warning/Deprecated trong các trang quản trị đã mở. Trường khoá bí mật Turnstile trống; chưa đặt khoá thật để thử. Không thay nguồn đang bật, giấy phép hay xác nhận nguồn trong trang quản trị.

| Lệnh | Kết quả thật |
|---|---|
| `npm run kiem-tra` | Đạt, 0 lỗi |
| `npm run test:v2` | Đạt: CSS khớp; 237 PHP thuần; 255 tích hợp WordPress; 55 chuyển đổi, gồm nhập → hoàn tác → nhập |
| `npm run quet:v2` | Đạt: 25 trang × 3 chế độ + 7 trang biên tập, 0 lỗi |
| `npm run e2e:v2` | Đạt: 21 bước, 0 lỗi, 0 bỏ qua |
| `npm run e2e:v2 -- --site https://phongdao.com` | Entry point tương ứng chạy với lớp giãn điều hướng: 12 đạt, 0 lỗi, 2 bỏ qua; chặn 3 yêu cầu ghi |
| Quét riêng site thật | 730 lượt bản đầu + 90 kiểm lại; lỗi L-01/L-05 còn tái hiện dù quét fixture đạt |

Lần thử bằng Node mặc định 22.20 đã báo bỏ qua Playground; **không tính là đạt**. Sau đó dùng Node 24.18, PHP có extension cần thiết và shim CLI tạm ngoài repo, chạy lại đến kết quả trên. Các lần kịch bản review bắt nhầm “Không có gì thay đổi” thành lỗi, bấm nút đang trong disclosure, hoặc chờ chưa đúng điều hướng đã được sửa trong **kịch bản review** và kiểm lại; không ghi chúng thành lỗi sản phẩm.

`kiem-site-that` đã đọc và đối chiếu mã: nhận diện marker V2, `do-luong.js` và `assets/giao-dien.css`; workflow có đường dẫn V2 trong phép tính thời gian triển khai. Cách nhận phiên bản còn phù hợp sau chuyển. Lần review xác nhận asset đang phục vụ 1.0.3/1.0.1 bằng trình duyệt, không chạy một lệnh kiểm máy chủ. `tu-cap-nhat.sh` và cập nhật plugin đã được đọc; kiểm trang chủ 200 chỉ chứng minh trang chủ trả lời, không chứng minh đầy đủ luồng bài/biên tập hay tương thích dữ liệu.

## 6. Việc nên làm tiếp và kết luận

1. Sửa vùng chạm bản đồ, giảm tải ảnh trang bài, bổ sung Hoàn tác quyết thẻ. Kiểm lại bằng dữ liệu đủ dày như site thật.
2. Bịt khoảng hở phục hồi khi thay thư mục thất bại; làm rõ hướng dẫn quay qua mốc MVP/V2, diễn tập ở bản sao phù hợp trước khi dựa vào nó khi có sự cố.
3. Đối chiếu 630/631, xác định vè thứ bảy, cung cấp danh sách 15 ánh xạ cũ để kiểm đủ 15/15 bằng GET. Không tự đổi nội dung để làm khớp số.
4. Hoàn thiện phím tắt, tràn 320, lối Quyền riêng tư và trigger CI. Hoàn tất các ca còn nêu rõ là chưa kiểm; dùng trình đọc màn hình/người dùng thực để bổ sung kiểm trợ năng.

**REQUEST CHANGES**

**Đã nên mở site cho Google chưa? Chưa.** Các luồng chính và bộ kiểm mẫu đạt, nhưng còn lỗi trợ năng với dữ liệu thật, trang vượt ngân sách, thiếu Hoàn tác biên tập và khoảng hở phục hồi vận hành. Cần sửa/kiểm lại các lỗi lớn và giải thích chênh lệch sau chuyển đổi trước khi đề nghị người sáng lập mở lập chỉ mục. Báo cáo này không phải quyết định mở Google và không thay đổi noindex.
