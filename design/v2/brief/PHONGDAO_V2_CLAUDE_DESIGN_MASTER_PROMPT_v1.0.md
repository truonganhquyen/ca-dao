# PHONGDAO V2 — MASTER PROMPT CHO CLAUDE DESIGN

**Phiên bản:** 1.0 · 27/10/2026  
**Mục đích:** Thiết kế UX/UI có thể xem và thao tác trước khi triển khai theme WordPress V2.  
**Repo đối chiếu:** https://github.com/truonganhquyen/ca-dao  
**Bản chụp tài liệu:** nhánh `main`, commit `44f34b0f5328e7858998fb14468d74296c8c268b`, thời điểm commit 19:43 ngày 27/10/2026, giờ Việt Nam.

## Cách giao việc

1. Tạo dự án trong https://claude.ai/design/ và đính kèm tệp này, hoặc dán toàn bộ nội dung từ **BẮT ĐẦU PROMPT** tới **KẾT THÚC PROMPT**.
2. Phần prompt đã tổng hợp đủ yêu cầu để bắt đầu nếu công cụ không đọc được GitHub. Có thể đính kèm thêm các tài liệu gốc liệt kê tại mục 2 để kiểm tra chi tiết. Không gửi tài khoản quản trị, kho thô hoặc khoá dịch vụ.
3. Yêu cầu đầu ra là nguyên mẫu tương tác, bộ thành phần và tài liệu bàn giao. Duyệt bố cục, chữ và các luồng trên nguyên mẫu trước khi đưa thiết kế sang dev làm theme.
4. Các trang thuộc đợt 5 được thiết kế trong một khu riêng. Không dùng nguyên mẫu để kết luận rằng tính năng đó đã đủ điều kiện xuất hiện trên site thật.

---

# BẮT ĐẦU PROMPT

Bạn là người phụ trách Product Design, UX, Information Architecture, thiết kế trải nghiệm đọc tiếng Việt và thiết kế công cụ biên tập của dự án **Phongdao V2 — Thư viện Văn học Dân gian Việt Nam**.

Hãy **thực hiện trọn bộ thiết kế và dựng nguyên mẫu tương tác**, từ hệ thống thiết kế, các trang công khai, chế độ đọc, đến Bàn biên tập. Tôi cần nhìn thấy và dùng thử website trước khi dev triển khai theme WordPress. Không dừng ở một bản phân tích, moodboard, wireframe bằng chữ hoặc một trang chủ đẹp.

## 1. Kết quả tôi cần

Tạo ra một bộ UX/UI thống nhất để tôi có thể:

- Mở bản thiết kế ở điện thoại và máy tính, đi qua các hành trình chính.
- Xem toàn bộ chiều dài trang, trạng thái mở/đóng và trường hợp thiếu nội dung.
- So sánh hai bảng màu đã được cho phép thử.
- Nhận ra ngay thiết kế nào dành cho bản ra mắt V2 và thiết kế nào thuộc đợt mở rộng.
- Duyệt bố cục, kiểu chữ, thứ bậc hành động và cách điều hướng trước khi dev làm theme.
- Giao bộ thành phần, kích thước, hành vi và trạng thái cho dev mà không cần dev tự đoán từ ảnh.

**Phạm vi làm việc:** thiết kế và nguyên mẫu. Mọi thao tác Đăng, Gộp, Duyệt, Gửi, đổi chính sách, lấy nguồn trong nguyên mẫu chỉ chạy trên dữ liệu mô phỏng. Không kết nối ghi vào WordPress thật, không sửa repo, không triển khai site, không gửi nội dung cho bên thứ ba.

Nếu môi trường của bạn tạo được code nguyên mẫu, hãy dùng nó để thể hiện tương tác. Phân biệt code nguyên mẫu với mã production WordPress. Không tự chuyển nền tảng sản phẩm sang framework của nguyên mẫu.

## 2. Nguồn sự thật và cách xử lý mâu thuẫn

### 2.1. Tài liệu gốc

Các đường dẫn dưới đây được ghim vào commit đã rà để tránh việc thiết kế dựa trên những bản thay đổi ở các thời điểm khác nhau:

| Tài liệu | Đường dẫn | Vai trò |
|---|---|---|
| Quy tắc dự án | [AGENTS.md](https://github.com/truonganhquyen/ca-dao/blob/44f34b0f5328e7858998fb14468d74296c8c268b/AGENTS.md) | Lằn ranh nội dung, quyền riêng tư, câu chữ, giới hạn kỹ thuật |
| Nền tảng V2 | [docs/00](https://github.com/truonganhquyen/ca-dao/blob/44f34b0f5328e7858998fb14468d74296c8c268b/docs/00-tai-lieu-nen.md) | Định vị, người dùng, phạm vi, năm đợt |
| Quyết định | [docs/01](https://github.com/truonganhquyen/ca-dao/blob/44f34b0f5328e7858998fb14468d74296c8c268b/docs/01-nhat-ky-quyet-dinh.md) | Đặc biệt D-069, D-072, D-079 và D-082–D-091 |
| Kỹ thuật V2 | [docs/26](https://github.com/truonganhquyen/ca-dao/blob/44f34b0f5328e7858998fb14468d74296c8c268b/docs/26-kien-truc-v2.md) | WordPress, mẫu theme, khối động, dữ liệu lớp, quyền |
| Sản phẩm người đọc | [docs/27](https://github.com/truonganhquyen/ca-dao/blob/44f34b0f5328e7858998fb14468d74296c8c268b/docs/27-san-pham-v2.md) | Thứ tự và hành vi trang công khai; tài liệu chính của phần người đọc |
| Bàn biên tập | [docs/28](https://github.com/truonganhquyen/ca-dao/blob/44f34b0f5328e7858998fb14468d74296c8c268b/docs/28-ban-bien-tap-v2.md) | Luồng nội dung và màn hình quản trị |
| Chuyển đổi | [docs/29](https://github.com/truonganhquyen/ca-dao/blob/44f34b0f5328e7858998fb14468d74296c8c268b/docs/29-chuyen-doi-v2.md) | Bảo trì, Cần xem, dữ liệu cũ, hoàn tác |
| Token kế thừa | [docs/20](https://github.com/truonganhquyen/ca-dao/blob/44f34b0f5328e7858998fb14468d74296c8c268b/docs/20-dac-ta-giao-dien.md) | Mục 1, 10, 11 còn áp dụng; phần mô tả màn hình cũ không thay docs/27 |
| Backlog | [docs/22](https://github.com/truonganhquyen/ca-dao/blob/44f34b0f5328e7858998fb14468d74296c8c268b/docs/22-backlog-trien-khai.md) | Đợt 2: T-146–151; đợt 3: T-152–156; đợt 5: T-158–164 |
| Nghiên cứu UX | [Nghiên cứu UX/UI](https://github.com/truonganhquyen/ca-dao/blob/44f34b0f5328e7858998fb14468d74296c8c268b/docs/nghien-cuu/v2-ux-ui-2026-10-27.md) | Giải thích lý do; bản chốt nằm ở docs/27 |
| Gói nâng cấp cũ | [docs/25](https://github.com/truonganhquyen/ca-dao/blob/44f34b0f5328e7858998fb14468d74296c8c268b/docs/25-nang-cap-giao-dien-noi-dung.md) | Chi tiết tính năng MVP được kế thừa khi không trái V2 |

Nếu truy cập được repo, đọc tài liệu chính trước khi dựng. Nếu không truy cập được, dùng bản tổng hợp trong prompt này, ghi rõ giới hạn đó trong ghi chú bàn giao; không giả vờ đã đọc nguồn.

### 2.2. Thứ tự ưu tiên

1. Yêu cầu thiết kế trong phiên này: có nguyên mẫu để duyệt sớm, trước khi làm theme.
2. Quyết định V2 mới nhất trong docs/01, đặc biệt D-082–D-091.
3. docs/00 và các đặc tả hiện hành docs/26–29 theo đúng lĩnh vực.
4. Token, quy tắc chữ và trợ năng của docs/20; chi tiết kế thừa từ docs/25 khi không mâu thuẫn.
5. Tài liệu nghiên cứu, báo cáo E2E và site MVP: căn cứ tham khảo, không được ghi đè bản chốt.

Các báo cáo nghiên cứu có đoạn đề xuất cũ như cải tiến tại chỗ, giữ Gutenberg dự phòng, tiếp tục dùng loại bài `cdtn` hoặc giữ menu quản trị cũ. Những đoạn đó đã bị quyết định V2 thay thế.

Không coi mọi đoạn trong tài liệu lịch sử là yêu cầu còn hiệu lực. Không coi danh sách task `READY` là bằng chứng tính năng đã có trên site.

### 2.3. Phân biệt ba loại quyết định

Trong ghi chú thiết kế, đánh dấu:

- **ĐÃ CHỐT:** bắt buộc giữ theo nguồn nêu trên.
- **ĐỀ XUẤT THỂ HIỆN:** chi tiết thị giác hoặc thao tác chưa có kích thước/cách trình bày cụ thể; được đề xuất nhưng phải ghi rõ.
- **CẦN CHỦ DỰ ÁN CHỌN:** hai phương án được phép thử, hoặc điểm chưa đủ đặc tả. Dựng cả phương án cần so, tiếp tục phần còn lại; không tự biến thành quyết định sản phẩm mới.

Các giá trị kích thước đề xuất trong prompt này là điểm bắt đầu của thiết kế, không phải quyết định D-xxx mới. Không tự sửa tài liệu gốc hoặc đánh dấu task triển khai đã hoàn tất.

## 3. Bối cảnh, người dùng và tiêu chuẩn thành công

### 3.1. Sản phẩm

- Tên đầy đủ: **Thư viện Văn học Dân gian Việt Nam**.
- Tên ngắn trên đầu trang điện thoại: **Phongdao**.
- Tên miền: **phongdao.com**.
- Khẩu hiệu: **Đọc một câu. Hiểu một đời sống.**
- Lời hứa: **Mỗi câu ghi rõ lấy từ đâu, và đã được kiểm đến mức nào.**
- Ba trụ: **TIN — HIỂU — CẢM**. Đây là nguyên tắc sản phẩm; không bắt buộc biến thành ba banner hoặc ba tab lớn.
- Phi lợi nhuận, không quảng cáo, không theo dõi, đọc tốt trên điện thoại.
- Mỗi câu/mỗi tác phẩm có trang riêng. Tuyển tập, chủ đề, lớp học ghép lại từ tác phẩm; nghĩa không được viết thành nhiều bản độc lập ở nhiều trang.

Năm thể loại lõi: **Ca dao; Tục ngữ – Thành ngữ; Đồng dao; Vè; Câu đố**. Hát ru, hò, lý và truyện thuộc đợt mở rộng có điều kiện.

### 3.2. Người dùng

| Người dùng | Nhu cầu cần thiết kế tốt |
|---|---|
| Giáo viên THCS | Tìm 5 câu theo bài học/chủ đề, có nghĩa và trích dẫn, trình chiếu trong ≤ 3 phút |
| Người trẻ | Gõ vài chữ hoặc không dấu, chọn câu và chia sẻ có nguồn trong khoảng 30 giây |
| Học sinh | Hiểu nghĩa câu tục ngữ trong ≤ 10 giây |
| Phụ huynh | Tìm trò chơi phù hợp tuổi, số trẻ, nơi chơi trong ≤ 1 phút |
| Người yêu văn hoá | Lần theo từ khó, dị bản, vùng lưu truyền, địa danh và tư liệu |
| Người có tư liệu | Gửi số trang trong khoảng 2 phút, gửi dị bản có xuất xứ dễ dàng |
| Người sáng lập | Một người biên soạn, khoảng 5 giờ/tuần, chủ yếu dùng điện thoại; duyệt một nháp ≤ 60 giây |

Người đọc lo dùng sai, dạy sai hoặc chia sẻ sai. Vì vậy chữ đẹp phải đi cùng mức kiểm rõ ràng, không biến mức kiểm thành một dấu xác nhận chất lượng chung chung.

### 3.3. Giới hạn nội dung và tính năng

Không thêm tài khoản người đọc, đăng ký, bình luận mở, lượt thích, bảng xếp hạng, điểm thưởng, chatbot, AI trả lời công khai, newsletter chặn màn hình, quảng cáo hoặc liên kết tiếp thị.

Không tạo một Ban biên tập nhiều cấp hay quy trình ký duyệt rườm rà. Hiện có một người biên soạn; ba vai phần mềm được khai sẵn để mở sau.

Không đưa nội dung học văn tổng quát, dàn ý, văn mẫu, phong thuỷ, tra năm sinh, meme, thơ hiện đại hoặc truyện nước ngoài vào menu để làm đầy site.

## 4. Phạm vi thiết kế và điều kiện xuất hiện

### 4.1. Bộ ra mắt V2

Phải thiết kế đầy đủ ngay trong bộ này:

- Hệ thống thiết kế, điều hướng, trang chủ, kho và mọi biến thể danh sách.
- Năm mẫu tác phẩm lõi, các lớp nội dung, toàn bộ công cụ đọc.
- Tuyển tập đọc liền, tìm, từ điển, bản đồ, đố vui, trò chơi, trang hay bị nhầm, ngẫu nhiên.
- Đã lưu, đóng góp, cảm nghĩ, bài phân tích đã duyệt, trang giới thiệu/chính sách, 404, bảo trì.
- Bàn biên tập: Hôm nay, Duyệt nhanh, soạn, quản lý và các trạng thái vận hành liên quan.

### 4.2. Bộ đợt 5 — thiết kế riêng, không trộn với bản ra mắt

- Cho lớp học, bộ câu SGK, phiếu in.
- Truyện, Kể cho bé, các thể loại văn bản hát ru/hò/lý khi đủ điều kiện.
- Giọng đọc sinh sẵn, mục Nghe, phiên kiểm giọng 30 bài.
- Đối chiếu sách quét/Wikisource và giải nghĩa trong sách.
- Dữ liệu mở có phiên bản: đã chốt khả năng xuất; trang tải công khai cụ thể chưa được chốt, nếu đề xuất phải ghi nhãn đề xuất.

### 4.3. Điều kiện không được làm sai trong bản thiết kế

| Thành phần | Điều kiện |
|---|---|
| Câu hôm nay | Chỉ chọn tác phẩm L2; không lặp trong 30 ngày; không đủ dữ liệu thì ẩn khối |
| Thể loại trong menu | Thể loại 0 bài ẩn; thể loại mới chỉ mở khi có mẫu riêng qua trợ năng, ≥ 20 bài đã đăng, ≥ 10 bài L2, hồ sơ nguồn và quyền đã ghi |
| Cho lớp học | Chỉ lên điều hướng khi có ≥ 3 bài học có bộ câu SGK; thuộc đợt 5 |
| Tuyển tập công khai | Mặc định có lời dẫn do người viết và ≥ 8 câu đã đăng; nguyên mẫu dùng ngưỡng 8, không tự đổi |
| Tiêu đề “N câu có giải nghĩa” | Chỉ dùng khi mọi câu trong tuyển tập có Hiểu nhanh |
| Hay bị nhầm là ca dao | Trang công khai khi có ≥ 5 câu được gắn cờ và có lý do |
| Nghe trên menu | Chỉ khi giọng sinh sẵn qua phiên nghe thử; không hiện như mục sẵn có ở bản ra mắt |
| Biên soạn | Chỉ hiện khi có bút danh; không bịa người phụ trách |
| Số lượt | Đọc ≥ 10; chia sẻ ≥ 3; trích dẫn ≥ 3. Dưới ngưỡng ẩn từng chỉ số, không hiện 0 |
| Các khối nội dung thiếu dữ liệu | Bỏ cả khối và tiêu đề; không chừa ảnh trống, không sinh câu độn |

L0–L3 là cấp nội bộ để tính từ dữ liệu; không dùng làm huy hiệu trên trang công khai. Không biến L2 thành nhãn “đã xác minh”. Chất lượng nghĩa và mức đối chiếu lời là hai việc khác nhau.

## 5. Hướng mỹ thuật và hệ thống thiết kế

### 5.1. Art direction đã chốt

**Sách Việt đương đại. “Một trang sách cổ được chăm chút, đặt trong một chiếc điện thoại hiện đại.”**

Lời và việc đọc đứng ở trung tâm. Bản sắc đến từ chữ, nhịp, dấu tiếng Việt, cách ngắt dòng, khoảng trắng và màu son trầm.

- Nền giấy nhẹ, chữ mực, đường kẻ mảnh, bố cục biên tập có nhịp.
- Dùng khung thẻ ở nơi cần gom kết quả hoặc lựa chọn. Không bọc mọi khối đọc trong hộp.
- Trên desktop, giữ cột đọc tập trung; có thể dùng cột phụ cho mục lục bài dài, nhưng không kéo lời thơ tràn ngang.
- Dùng icon nét nhất quán, có nhãn chữ; không dùng emoji làm hệ icon production.
- Không gradient trang trí, khối kính, bóng đổ dày, bo góc lớn khắp nơi, dashboard SaaS trên trang công khai hoặc hero chiếm toàn bộ màn hình trước khi thấy nội dung.
- Không phủ nón lá, trống đồng, tre, hoa sen hoặc giả giấy cũ cháy cạnh lên mọi màn hình.
- Minh hoạ chỉ chọn lọc ở tuyển tập, đồng dao, truyện và phải có quyền; thiếu ảnh thì dùng bố cục chỉ chữ hoàn chỉnh.
- Ngôn ngữ atlas chỉ dùng cho bản đồ/chuyên đề vùng.

### 5.2. Hai bảng màu cần so bằng thiết kế thật

**A — Bảng đang chốt, dùng làm bản triển khai thiết kế mặc định:**

| Token | Sáng | Tối | Công dụng |
|---|---|---|---|
| `--giay` | `#FBF8F1` | `#1B1A18` | Nền |
| `--muc` | `#22201C` | `#EDE6D6` | Chữ chính |
| `--son` | `#9E2A22` | `#E8776B` | Liên kết, nhấn, focus |
| `--nhat` | `#5F594F` | `#ABA496` | Chữ phụ và tư liệu |
| `--nhan-a` | `#3F5A2C` | `#9CC07F` | Đã đối chiếu tư liệu |
| `--nhan-b` | `#2B5580` | `#8DB6E3` | Ghi từ người kể |
| `--nhan-c` | `#7A5A12` | `#D9B56A` | Cộng đồng gửi |
| `--nhan-d` | `#665A48` | `#B8AB93` | Lời phổ biến, chưa đối chiếu |
| `--nhan-co` | `#5E5E5E` | `#B0B0B0` | Cờ nghi không phải dân gian |

Tên token a/b/c/d chỉ là định danh thiết kế. Không đưa chữ “A/B/C/D” hoặc phân hạng tương ứng ra giao diện người đọc. Câu chữ mức kiểm lấy theo V2, không bê bảng nhãn lịch sử vào mọi trang.

**B — Bảng được phép thử:** nền `#F4EDE1`, chữ `#332C25`, son `#9F443A`, xanh trầm `#398578`.

- Màu xanh `#398578` trên nền đề xuất không đạt 4,5:1 cho chữ thường; chỉ dùng ở thành phần phù hợp ngưỡng hoặc đề xuất màu xanh đậm hơn và đo lại.
- Bản tối của B chưa có token chốt; nếu thiết kế hãy ghi là đề xuất và kiểm tương phản riêng.
- So A/B trên **cùng dữ liệu, cùng bố cục** ở trang chủ, ca dao và tục ngữ, cả mobile và desktop. Không đổi đồng thời cả layout làm khó so màu.
- Tiếp tục hoàn thiện toàn bộ bộ thiết kế trên A, giữ B là biến thể để tôi chọn. Không coi lựa chọn mặc định là tôi đã duyệt thay màu.

### 5.3. Chữ, khoảng cách và bố cục

**Đã chốt:** Literata cho lời/tiêu đề; Be Vietnam Pro cho UI, nghĩa và chú giải; tự lưu trữ phông; bề rộng đọc tối đa khoảng **38em**; thân chữ UI ≥ 17 px; phụ chú ≥ 15 px; lời thơ ≥ 22 px, giãn dòng khoảng 1,7.

Đề xuất bộ khởi đầu, ghi rõ có thể tinh chỉnh bằng mắt:

- Thang chữ 7 bậc: **15 / 17 / 22 / 28 / 36 / 48 / 64 px**. Văn xuôi ở 17, lời ca dao ở 22, câu tục ngữ ở 28; tiêu đề co giãn theo khung nhìn. Không bắt tất cả màn hình dùng bậc 64.
- Thang khoảng cách 8 bậc: **4 / 8 / 12 / 16 / 24 / 32 / 48 / 64 px**.
- Mobile: lề 16–20 px; tablet 24–32 px; desktop có container rộng riêng cho danh sách và cột đọc 38em riêng cho tác phẩm.
- Điểm chuyển bố cục đề xuất: 768 và 1024 px; kiểm tính liên tục giữa các mốc, không thiết kế như ba ảnh tĩnh rời nhau.
- Nút chính ≥ 48 px; vùng chạm khác ≥ 44×44 px. Các nút icon vẫn có vùng chạm đủ lớn.
- Radius tiết chế, đường kẻ nhẹ; pill dành cho chip/lựa chọn, không áp cho mọi thành phần.

Thiết kế chuẩn tại **390×844** và **1440×1000**; kiểm thêm 320, 360, 768, 1024 px, zoom 200% và reflow tương đương 320 CSS px. Không tắt zoom.

Kiểm các chữ “ẫ, ỡ, ự, Ngưỡng, Thuở, Quyện”, dấu nặng, dấu ngã, chữ nghiêng và chữ đậm. Không cắt dấu ở nút, chip, tiêu đề hoặc khối thơ.

Lục bát mặc định thụt câu lục so với câu bát; làm bằng token. Q-006 chưa chọn lại hướng trình bày, hiện có mặc định nên không bị chặn. Có thể thêm một mẫu căn giữa vào trang so sánh để chủ dự án xem, không thay toàn hệ thống.

Giữ ngắt dòng của dữ liệu lời. Với dòng quá dài trên 320 px/cỡ chữ lớn, trình bày phương án wrap thị giác có thụt tiếp dòng để không mất chữ; ghi đây là đề xuất xử lý reflow cần kiểm. Không sửa nội dung nguồn để ép vừa layout, không dùng ảnh chụp lời và không cắt chữ.

### 5.4. Bộ thành phần bắt buộc

Tạo component có tên, thuộc tính và trạng thái dùng lại:

| Nhóm | Thành phần và biến thể |
|---|---|
| Khung site | Header desktop/mobile, menu Đọc/Khám phá/Tra cứu, bottom nav, footer, breadcrumb, skip link |
| Tìm và duyệt kho | Ô tìm, kết quả trực tiếp, kết quả khớp chính, nhóm kết quả, chip lọc có số, lọc thu gọn, sort, phân trang |
| Nội dung | Thẻ tác phẩm, thẻ tuyển tập, khối lời thơ/một dòng/bài dài, nhãn thể loại, cờ nghi, dòng mức kiểm |
| Đọc sâu | Hiểu nhanh, nghĩa nhiều lớp, từ chú thích, popover/sheet, danh sách chú thích, dị bản có tô khác, bối cảnh, mục lục |
| Tư liệu | Dòng nguồn tại chỗ, ghi công ảnh, Thông tin tư liệu, Biên soạn, tác phẩm thuộc tuyển tập, Đọc tiếp có lý do |
| Hành động | Trích dẫn, chia sẻ, lưu, trình chiếu, Aa, sáng/tối/hệ thống, in |
| Hoạt động | Thông số trò chơi, bước cách chơi, câu đố, gợi ý, đáp án thu gọn, Đọc cùng bé |
| Đóng góp | Chọn loại góp, form tương ứng, cam kết, lỗi, thành công chờ duyệt, giới hạn gửi |
| Trạng thái | Trống, thiếu một lớp, không kết quả, chờ biên soạn, đang tải, lỗi, thử lại, thông báo inline |
| Quản trị | Thẻ việc, trạng thái lớp, ô lời, hàng chú thích, so sánh trùng, nguồn nội bộ, dải nhắc, lịch sử, thanh hành động, hoàn tác |

Mỗi component tương tác cần normal, hover khi phù hợp, focus-visible, selected/expanded, loading, disabled có lý do, error/success. Không tạo biến thể vô nghĩa chỉ để đủ bảng.

## 6. Kiến trúc thông tin và điều hướng

### 6.1. Desktop

Header: **Phongdao · Đọc ▾ · Khám phá ▾ · Cho lớp học (có điều kiện) · Tra cứu ▾ · ô tìm · Đã lưu**.

| Mục | Mục con |
|---|---|
| Đọc | Tất cả; Ca dao; Tục ngữ – Thành ngữ; Đồng dao & Trò chơi; Vè; Câu đố; thể loại mới khi mở |
| Khám phá | Tuyển tập; Chủ đề; Theo dịp; Vùng & Bản đồ; Mô-típ; Một câu ngẫu nhiên |
| Tra cứu | Tìm; Từ điển; Hay bị nhầm là ca dao; Phương pháp & nguồn |

Cho lớp học và Nghe phải có mockup điều hướng “chưa mở/đã đủ điều kiện”, không dùng trạng thái disabled quảng bá tính năng tương lai trên site ra mắt.

Menu dùng được bằng click, bàn phím và chạm. Không chỉ mở khi hover. Không tạo megamenu quá lớn để chứa mọi taxonomy.

### 6.2. Mobile

- Header gọn: tên Phongdao, lối tìm dễ thấy và công cụ phù hợp ngữ cảnh. Không nhét toàn tên dài vào một dòng nhỏ.
- Bottom nav **56 px cộng safe area**: **Trang chủ · Tìm · Khám phá · Đã lưu**; active có chữ và hình, không chỉ màu.
- Khám phá mở được toàn bộ cấu trúc điều hướng còn lại. Thiết kế một bề mặt điều hướng rõ ràng. Hình thức sheet/toàn màn hình là đề xuất thể hiện; tài liệu chưa chốt URL `/kham-pha/`, không tự coi đó là route production.
- Tìm từ thanh dưới dẫn tới bề mặt tìm, có cách quay lại và giữ ngữ cảnh bài đang đọc.
- Không để bottom nav, thanh thao tác hoặc bàn phím che nút, lỗi form và focus. Tránh chồng nhiều thanh cố định.

### 6.3. Footer

Về dự án · Phương pháp · Nguồn · Chính sách thu thập dữ liệu · Quyền riêng tư · Góp tư liệu · Báo lỗi. Dòng giấy phép phù hợp: phần dự án tự viết CC BY 4.0, nội dung nguồn khác theo giấy phép tương ứng. Có câu “Không quảng cáo, không theo dõi”.

Không dùng tên “Kho tàng Ca dao Tục ngữ Việt Nam” làm tên hiện hành. Không có khối liên kết SEO ngoài chủ đề.

### 6.4. Route phải tôn trọng

| Loại trang | Route |
|---|---|
| Tác phẩm | `/ca-dao/<ten>/`, `/tuc-ngu/<ten>/`, `/dong-dao/<ten>/`, `/ve/<ten>/`, `/cau-do/<ten>/` |
| Kho/thể loại | `/kho/`, `/the-loai/<the-loai>/` |
| Tập hợp | `/chu-de/<ten>/`, `/vung/<ten>/`, `/mo-tip/<ten>/`, `/hinh-thuc/<ten>/` |
| Tuyển tập | `/tuyen-tap/`, `/tuyen-tap/<ten>/` |
| Từ điển | `/tu-dien/`, `/tu-dien/<ten>/` |
| Trò chơi | `/tro-choi/`, `/tro-choi/<ten>/` |
| Công cụ | `/tim/?q=...`, `/ban-do/`, `/do-vui/`, `/hay-bi-nham/`, `/ngau-nhien/`, `/da-luu/` |
| Phân tích | `/phan-tich/<ten>/` |
| Trang dự án đang có | `/phuong-phap/`, `/nguon/`, `/ve-du-an/`, `/chinh-sach-du-lieu/` |
| Mở rộng | `/lop-hoc/`, `/lop-hoc/<lop>/`, `/truyen/<ten>/`, sau đó `/hat-ru/`, `/ho/`, `/ly/` theo cấu trúc tác phẩm |

Quyền riêng tư và các lối góp có thể cần gắn với trang sẵn có; nếu chưa có route trong tài liệu, ghi trong handoff là cần nối route, không tự đổi URL cũ. `/tinh-huong/` chuyển về tuyển tập và `/the-tho/` về hình thức khi chuyển đổi; không dựng hai hệ điều hướng song song.

## 7. Danh mục màn hình công khai — phải dựng đủ

Các ID R dưới đây là **ID kiểm kê thiết kế của brief này**, không thay các mã S01–S18 trong hồ sơ gốc. Một mẫu có thể phục vụ nhiều URL nhưng phải có đủ biến thể được nêu.

### R01 — Trang chủ

**Mục tiêu:** chọn được lối vào trong 10 giây, tới bài hữu ích trong ≤ 3 lần chạm.

Giữ đúng trình tự mặc định:

1. Tên, khẩu hiệu, tìm lớn; gợi ý: **“Gõ câu bạn nhớ, gõ không dấu cũng được”**.
2. “Bạn là”: Học sinh / Giáo viên / Phụ huynh / Yêu văn hoá; bỏ qua được, đổi lại được.
3. Câu hôm nay: lời + nghĩa ngắn + Đọc.
4. Lối vào: Hiểu nghĩa tục ngữ / Theo dịp / Cùng bé chơi / Bộ câu cho tiết học khi đã mở.
5. Tuyển tập nổi bật 3–5 thẻ, có tên và số câu.
6. Bạn có biết? 1–2 nội dung đã duyệt, liên kết về tác phẩm.
7. Đọc theo thể loại, số bài.
8. Đọc nhiều tuần này; Vừa bổ sung.
9. Cách chúng tôi biên soạn.

Chọn vai chỉ đổi thứ tự **khối 4–8**. Giáo viên ưu tiên bộ câu/tuyển tập; phụ huynh ưu tiên cùng bé/đồng dao/câu đố; học sinh ưu tiên hiểu nghĩa; người yêu văn hoá ưu tiên tuyển tập/vùng/bản đồ. Không di chuyển khối tìm hoặc Câu hôm nay trái thứ tự chốt, không giấu nội dung theo vai. Lựa chọn chỉ nhớ trên máy.

Thiết kế ít nhất ba trường hợp: dữ liệu đầy đủ; chưa có Câu hôm nay/tuyển tập; đã chọn Phụ huynh. Trên desktop tạo nhịp bố cục đa dạng vừa phải; trên mobile không dùng carousel làm cách duy nhất thấy nội dung. Ô tìm không cắt placeholder như MVP.

### R02 — Tất cả `/kho/`

Lời dẫn ngắn → lọc Thể loại/Hình thức/Chủ đề/Vùng → A–Z hoặc Mới thêm → thẻ tác phẩm → phân trang **30 bài/trang**.

Thẻ: tên, 2 dòng đầu, nhãn thể loại, nghĩa ngắn nếu có, lượt đọc nếu đạt ngưỡng. Cờ nghi đi cùng tác phẩm.

Desktop có thể dùng vùng lọc bên cạnh; mobile lọc thu gọn có mũi tên, số bộ lọc và tóm tắt đang chọn. Chip có số, số 0 ẩn. Có bỏ từng lọc và xoá lọc. Giữ từ tìm và lựa chọn khi chuyển trang. Không dùng infinite scroll thay phân trang.

Trạng thái: chưa lọc; nhiều lọc; lọc không ra; trang cuối; đang tải khi cập nhật; lỗi và dùng lại form thường.

### R03 — Trang thể loại `/the-loai/<the-loai>/`

Giới thiệu do người viết → bài tiêu biểu → lối vào đặc thù → chủ đề phổ biến → danh sách.

Dựng đủ 5 biến thể: ca dao; tục ngữ có hàng chữ cái **A B C D Đ…**; đồng dao dẫn Theo trò chơi; câu đố dẫn Đố vui và chủ đề đáp án; vè dẫn sự kiện khi có bối cảnh. Không coi một lưới thẻ đổi màu là 5 thiết kế hoàn chỉnh.

Không có giới thiệu thì chỉ hiện tên. Chưa có lựa chọn tiêu biểu thì dùng L2 đọc nhiều; không tự bịa bài tiêu biểu.

### R04 — Chủ đề, vùng, mô-típ, hình thức

Dựng 4 biến thể của mẫu đọc liền: mô tả nếu có → từng tác phẩm có lời, nghĩa ngắn, nhãn → tuyển tập liên quan.

- Vùng: nhãn rõ **“Nơi lưu truyền”**, không suy từ địa danh trong lời.
- Địa danh: quan hệ **“Được nhắc đến”**, dẫn từ mục từ/bản đồ.
- Mô-típ: lời dẫn và tên do người biên soạn đặt, không khẳng định máy hiểu truyền thống dân gian.
- Hình thức: phân biệt thể thơ và lối hát trong nhãn khi dữ liệu có.

Đừng biến các trang này thành một loạt tên liên kết không có lời và nghĩa.

### R05 — Tác phẩm ca dao

Nhãn thể loại/hình thức, tên → lời Bản tham chiếu → **dòng mức kiểm ngay dưới lời** → **Hiểu nhanh ≤ 2 câu** → thanh hành động → Dị bản thu gọn → Chú thích thu gọn → Bạn có biết? khi có → phần cuối chung ở mục 8.

Lời đẹp, đọc được ngay, mặc định lục bát thụt câu lục. Từ có chú thích gạch chân chấm và chạm được; không biến cả dòng thành liên kết.

Hai dòng mức kiểm tiêu biểu:

- “Lời theo bản lưu truyền phổ biến · chưa đối chiếu sách · Vì sao?”
- “Lời đã đối chiếu với <tên sách>, tr. <N>” — chỉ khi dữ liệu đã được người xác nhận.

Trạng thái thiết kế: đủ lớp; chỉ có lời; không chú thích; đã đối chiếu; chưa đối chiếu; có AI hỗ trợ nghĩa đã duyệt; nhiều dị bản; cờ nghi; cảnh báo nội dung có căn cứ.

### R06 — Tác phẩm tục ngữ – thành ngữ

Câu chữ lớn theo bố cục câu một dòng, cho reflow khi cần → dòng mức kiểm → **Nghĩa/Hiểu nhanh** → Nghĩa đen/Nghĩa bóng thu gọn → Dùng khi thu gọn → Câu gần nghĩa/trái nghĩa → Giải nghĩa trong sách chỉ bộ đợt 5 → thanh hành động → phần cuối chung.

Trả lời trong 10 giây, không để nghĩa dưới một hero hoặc toolbar cao. Không thụt như lục bát. Không tách thành ngữ và tục ngữ thành hai mục sản phẩm.

Khi nghĩa trích Wiktionary: **“Nguồn: Wiktionary ↗ · CC BY-SA”** ngay dưới đoạn liên quan. Khi chưa có nghĩa: **“Câu này chưa có giải nghĩa. Bạn giúp được? Góp ý”**. Không tự viết nghĩa để lấp màn hình.

### R07 — Vè và bài rất dài

Tên → số dòng/thời gian đọc → mục lục đóng trên mobile → các đoạn → chú giải → Bối cảnh thu gọn → dị bản → phần cuối chung. Dòng mức kiểm vẫn gắn với lời theo nguyên tắc chung, không bị đẩy mất.

Có trạng thái đọc đoạn đầu, Đọc toàn bài, Đọc tiếp từ chỗ cũ, không có mục lục vì thiếu cấu trúc. Desktop có thể có mục lục phụ; mobile không che lời bằng thanh tiến độ dày.

Dựng dữ liệu thử 60 dòng và **trên 200 dòng**; kiểm thêm hình dung bài khoảng 990 dòng để bố cục không lệ thuộc bài ngắn. Số dòng và số đoạn do dữ liệu tính, không viết tay để trang đẹp.

Bối cảnh chỉ hiện sự kiện/năm/nơi/nhân vật có căn cứ. Thiếu thì ẩn, không kể lịch sử bằng AI.

### R08 — Đồng dao

Thẻ thông số trò chơi: số người/độ tuổi/nơi chơi/thời lượng/đồ dùng → **Đọc cùng bé** → lời → Cách chơi đánh số → nguồn/giấy phép cách chơi ngay dưới → Dị bản ở các vùng → phần cuối chung → Trò chơi cùng tuổi.

Thông số luôn có chữ, thiếu ô nào bỏ ô đó và tự cân bố cục. Dòng mức kiểm gắn với lời; công cụ Trích dẫn không cạnh tranh với hành động Đọc cùng bé của vùng trò chơi.

Thiết kế đồng dao có trò chơi và đồng dao chưa có trò chơi. Không tự gắn trò chơi cho mọi bài. Không tự phát âm thanh.

### R09 — Câu đố

Câu hỏi chữ lớn → mức kiểm gắn với văn bản khi có → Gợi ý nếu có → **Hiện đáp án** → vùng đáp án gồm chữ/ảnh có ghi công/giải thích khi có → Đố bạn bè → câu đố cùng đáp án → phần cuối chung.

Dựng đóng/mở đáp án; có/không gợi ý; có/không ảnh. Đáp án có trong HTML, mặc định thu gọn bằng `<details>`. Không chỉ ẩn bằng JavaScript và không cần đăng nhập để mở.

Trích dẫn, lưu vẫn có nhưng “Hiện đáp án” là hành động chính của khối đố. Không dùng thumbnail hoặc trích đoạn kết quả làm lộ đáp án trước khi mở.

### R10 — Danh mục tuyển tập

Nhóm theo **Chủ đề / Theo dịp / Tình huống / Bài học**. Theo dịp ưu tiên dịp gần nhất từ dữ liệu lịch, không mã hoá cứng một lễ vào bố cục.

Thẻ: tên, loại, số câu, thông tin đọc khi có. 10 nhóm nội dung đầu đã định hướng: **mẹ, cha, thầy cô, quê hương, học tập, lao động, tình yêu, Tết, đoàn kết, thiên nhiên**. Đây là danh mục để biên soạn, không phải bằng chứng đã có 10 tuyển tập công khai. Chỉ render những tuyển tập đủ điều kiện.

### R11 — Tuyển tập đọc liền

Breadcrumb → nhãn loại → tên → số câu/thời gian/cập nhật/bút danh khi có → lời dẫn 2–4 câu do người viết → **Trình chiếu cả bộ** và In → mục lục theo nhóm → các mục đánh số → Hay bị nhầm khi có câu mang cờ → tuyển tập khác.

Mỗi mục có: nhãn thể loại, lời có chạm chú thích, nghĩa dùng lại của bài, Hợp khi nếu có, dòng mức kiểm, trích dẫn/lưu gọn, Xem bài. Mỗi mục phải có liên kết về tác phẩm.

Thiết kế tuyển tập **8 câu và 30 câu**, nhóm ngắn/dài, câu chưa có nghĩa, có câu mang cờ. Không viết lại nghĩa trong tuyển tập. Bản in sạch có thể nhiều trang; không ép 30 câu vào một tờ A4.

### R12 — Tìm kiếm

Thiết kế đủ trạng thái:

1. Chưa gõ: trường tìm có nhãn, hướng dẫn ngắn, lối đi tiếp.
2. Đang gõ/đang tải, có thể tiếp tục sửa từ khoá; không che trang bằng overlay loading.
3. Khớp một câu rõ ràng: thẻ lớn trên cùng, lời + nghĩa + nhãn + Đọc bài.
4. Nhóm theo thể loại và Từ điển, có số kết quả.
5. Tên bài + dòng khớp, tô phần khớp; kết quả rải chữ ở nhóm cuối **“Có các chữ này nhưng không liền nhau”**.
6. Sai một chữ: **“Có phải bạn tìm …?”** có thể bấm. Ca kiểm theo tài liệu: `ba co doi gai` gợi lại truy vấn đúng đã có, không lấy bài dài lạc đề.
7. Không kết quả và kho cũng không có: gợi ý chủ đề/tuyển tập phù hợp, lối góp câu đang tìm.
8. Không kết quả công khai nhưng kho có: **“Câu này đang chờ biên soạn”**, nút **“Mong có câu này”** → **“Đã ghi nhận, cảm ơn bạn”**.
9. Lỗi mạng: giữ query, có thử lại và gửi form thường.

**Tuyệt đối không xuất lời, chú thích, dị bản hoặc trích đoạn kho thô ra bề mặt người đọc.** Chỉ hiển thị query của người dùng và thông báo chờ biên soạn, không lấy tên/dòng lời chưa công khai từ kho để làm gợi ý lộ dữ liệu.

Tìm có dấu/không dấu; form thường `/tim/?q=...` khi không JavaScript. Có thể dùng bàn phím để di chuyển kết quả, Enter mở, Esc đóng gợi ý, trạng thái số kết quả được thông báo nhẹ cho trình đọc màn hình.

### R13 — Danh mục từ điển

Tìm mục từ → hàng chữ cái tiếng Việt → lọc từ cổ/Hán Việt/phương ngữ/địa danh/vùng → danh sách từ có lời giải ngắn và lối mở.

Mobile cho hàng chữ cái xuống dòng hoặc cuộn có dấu hiệu; không có đích chạm li ti. Có trạng thái chữ cái không có mục và lọc không kết quả. Phân loại hiển thị có thể gom theo lối tra cứu, không tự thêm bộ taxonomy mới vào sản phẩm.

### R14 — Trang mục từ

Từ → lời giải → nguồn theo chính sách → ảnh có ghi công nếu có → bản đồ nhỏ nếu là địa danh đã xác nhận → các tác phẩm có từ này, có nhãn → từ cùng vùng.

Thiết kế từ thường, địa danh, nhân vật; không ảnh; không tọa độ. Ảnh và bản đồ không được tạo khoảng trống khi thiếu. Nguồn ẩn không có link ẩn trong DOM. Địa danh dùng câu “Các bài nhắc đến…”, không gán vùng lưu truyền.

### R15 — Bản đồ

Chọn vùng và danh sách địa danh Bắc → Nam, từng điểm dẫn tới các bài nhắc đến. Desktop có thể đặt bản đồ và danh sách cạnh nhau. **Mobile danh sách trước, bản đồ sau.**

SVG lưu tại site, dùng tài sản bản đồ có sẵn trong repo nếu truy cập được; thể hiện Hoàng Sa và Trường Sa. Không vẽ lại địa giới bằng AI, không nhúng Google Maps hoặc tải dịch vụ bản đồ ngoài khi mở trang.

Địa danh có thể chọn bằng danh sách và bàn phím; pin nhỏ không là cách duy nhất chọn. Mất SVG vẫn duyệt danh sách được. Nếu thiếu bản đồ chính xác trong môi trường thiết kế, để trạng thái thiếu asset có chú thích trong bản xem thiết kế, không tự tạo bản đồ có vẻ đúng.

### R16 — Đố vui

Chọn chủ đề đáp án và độ khó từ dữ liệu → lượt **10 câu** → tiến độ `3/10` → câu hỏi → gợi ý → hiện đáp án → Câu khác và Xem bài đầy đủ → hoàn thành lượt.

Nhớ tiến độ trên máy. Không chấm điểm, đồng hồ đếm ngược, bảng xếp hạng, phạt sai hoặc âm thanh tự phát. Khi không JavaScript: 10 câu trong một trang, mỗi câu một `<details>`. Không đủ dữ liệu cho bộ 10 câu: có trạng thái thiếu bộ phù hợp và lối chọn nhóm khác, không tạo thêm câu đố bằng AI.

### R17 — Danh sách trò chơi

Lọc độ tuổi, số người, trong nhà/ngoài trời, cần đồ dùng hay không → thẻ trò chơi có thông số và bài đồng dao liên quan.

Thiết kế rõ một ca dùng: **4 bé, 5 tuổi, trong nhà**. Không chọn sẵn thông số để khẳng định trò chơi phù hợp nếu dữ liệu chưa có; trong prototype ghi nhãn dữ liệu thử.

### R18 — Chi tiết trò chơi

Tên/thông số → **Cách chơi trước** → nguồn, giấy phép → các bài đồng dao đi kèm → Đọc cùng bé khi có lời liên quan → trò chơi tương tự.

Phân biệt trang này với trang tác phẩm đồng dao: đây là hướng dẫn chơi, một trò chơi có thể nối nhiều bài. Không tự suy ra luật chơi từ lời đồng dao.

### R19 — Hay bị nhầm là ca dao

Giới thiệu ngắn đúng thực tế → danh sách tác phẩm mang cờ **“Nghi không phải dân gian”** → lý do người biên soạn viết + nguồn của lý do → mở bài.

Cờ xuất hiện đồng nhất ở tìm, thẻ, tuyển tập và trang bài. Dựng biến thể nhiều loại lý do bằng dữ liệu thử rõ ràng. Không tự quy tác giả, niên đại hoặc kết luận về một câu có thật.

### R20 — Một câu ngẫu nhiên

Một bài L2 → nghĩa → **Câu khác** → Xem đầy đủ. Không lặp trong phiên; không chọn bài có cờ nghi. Thiếu dữ liệu chuyển về Kho, không tạo màn hình trống vô hạn.

### R21 — Đã lưu

Danh sách đã lưu trên máy → lọc thể loại → mở/bỏ lưu → **Trình chiếu cả danh sách**.

Trạng thái trống có hướng dẫn bấm Lưu; trạng thái một bài/nhiều bài; bài đã lưu không còn công khai; bộ nhớ trình duyệt bị chặn. Nói rõ “Danh sách được lưu trên trình duyệt này. Xoá dữ liệu trình duyệt sẽ làm mất danh sách.” Site không biết ai lưu gì. Không đưa nút đăng nhập/đồng bộ đám mây vào thiết kế.

### R22 — Góp tư liệu và khối “Bạn muốn góp gì?”

Một khối hợp nhất cuối bài, chọn loại:

- Ở quê nói khác.
- Góp ý chú thích.
- Báo lỗi.
- Gửi số trang.
- Cảm nghĩ ngắn.
- Viết cảm nhận / bài phân tích.

Hiện trường phù hợp với loại đang chọn; không dựng 6 form dài mở cùng lúc. Luồng chung: nội dung → xuất xứ không bắt buộc → cam kết tự viết và đồng ý CC BY 4.0 → Gửi → thông báo chờ biên soạn/duyệt.

Gửi số trang có tên sách/trang và thông tin người gửi chủ động cung cấp; không tự chọn sách hoặc điền nguồn. Với bài phân tích: tiêu đề, nội dung có định dạng đơn giản, bút danh không bắt buộc; trường vai trò chỉ nếu dùng lại tính năng đã có, không thành hồ sơ tài khoản.

Không yêu cầu email, số điện thoại, tên thật. Nơi/người kể chỉ ghi khi có căn cứ và sự đồng ý cần thiết; không khuyến khích đưa dữ liệu cá nhân không cần thiết lên trang. Không mở tải ghi âm trong V2.

Thiết kế: đang nhập, lỗi validation cạnh trường, lỗi mạng giữ nguyên chữ, gửi quá giới hạn, thử lại, Turnstile khi cấu hình, gửi thành công chờ duyệt. Không tự đưa cảm nghĩ lên bài ngay sau gửi. Có lối đọc quyền riêng tư/cách yêu cầu gỡ, không tạo kênh liên hệ giả.

### R23 — Bài cảm nhận/phân tích đã duyệt

Nhãn **“Cảm nhận – phân tích”** → tiêu đề → bút danh khi có → bài viết → liên kết tác phẩm đang bàn → thông tin biên soạn/giấy phép phù hợp → lối đọc bài liên quan.

Phân biệt lời tác phẩm trích dẫn với ý kiến người viết. Không trộn bài phân tích vào Hiểu nhanh. Phân tích đã duyệt không tự nâng cấp chất lượng tác phẩm lên L2.

### R24–R28 — Nhóm trang dự án

| ID | Trang | Yêu cầu thiết kế |
|---|---|---|
| R24 | Phương pháp | Mục lục gọn; “Dòng nhỏ dưới lời nói gì”; cách đọc Thông tin tư liệu; vai trò AI; liên kết từ Vì sao? tới đúng mục |
| R25 | Nguồn | Danh sách nguồn được phép hiện; loại nguồn và ghi công; không làm lộ hồ sơ nguồn nội bộ/URL đã ẩn |
| R26 | Về dự án | Định vị mới, lời hứa, phạm vi, cách đóng góp; không bịa hội đồng khoa học, số người dùng hay đối tác |
| R27 | Chính sách thu thập dữ liệu | Phân cấp văn bản dễ đọc, ghi nhận nguồn theo nội dung chủ dự án duyệt; không liệt kê dữ liệu riêng tư |
| R28 | Quyền riêng tư | Nêu rõ dữ liệu trên máy/đóng góp/chống rác theo bản chính sách được cung cấp; thiết kế cấu trúc, không tự soạn cam kết pháp lý mới |

Nội dung trang tĩnh do người sáng lập viết, mang từ MVP và sửa tên. Nếu chưa có nội dung trong tài liệu bàn giao, dùng nhãn nội dung chờ cung cấp trong chế độ thiết kế, không viết một chính sách có vẻ đã được ban hành.

### R29 — 404

Tiêu đề ngắn cùng giọng site → tìm được điền từ hữu ích lấy từ URL → gợi ý tuyển tập/Kho/Từ điển. Không dùng ảnh mặc định Twenty Twenty-Five. Nút Tìm không vỡ dòng. Thông báo không kết luận trang đã bị xoá nếu chưa biết.

### R30 — Bảo trì và lỗi hệ thống

Trang **“Thư viện đang được nâng cấp”** cho lúc chuyển đổi, trạng thái production 503 + Retry-After do dev xử lý; không giả tiến độ hoặc hứa thời gian kết thúc không có căn cứ. Lỗi tìm, bản đồ, đố vui có lối quay về dạng đọc cơ bản hoặc lối đi tiếp.

### R31 — Điều hướng Khám phá trên mobile

Bề mặt điều hướng đầy đủ tới tuyển tập/chủ đề/theo dịp/vùng/bản đồ/mô-típ/ngẫu nhiên, lối Đọc/Tra cứu và trang dự án khi cần. Có đóng/quay lại, trạng thái mục hiện tại, focus đúng. Thể hiện phương án không JavaScript bằng liên kết hoặc disclosure thường.

Đây là thiết kế cho một lối vào đã chốt; URL/hình thức cụ thể chưa chốt, phải ghi trong handoff.

## 8. Các lớp và tương tác dùng chung — phải nhìn thấy và thao tác được

### 8.1. Phần cuối chung của tác phẩm

Giữ đúng thứ tự:

1. Chủ đề · Vùng · Thuộc tuyển tập (“Câu này có trong: …”).
2. **Thông tin tư liệu**, thu gọn.
3. **Biên soạn**, bút danh khi có và liên kết Phương pháp.
4. **Đọc tiếp**, 3–5 bài kèm lý do: cùng mô-típ, chủ đề, từ, địa danh, vùng theo ưu tiên tài liệu.
5. **Bạn muốn góp gì?**.
6. Cảm nghĩ đã duyệt và bài phân tích đã duyệt.

Mỗi tác phẩm có thể không đủ mọi khối; khoảng trắng tự khép lại. Thông báo thiếu phần: **“Bài đang được bổ sung. Bạn biết thêm? Góp ý”**. Không tạo cảm giác lỗi hệ thống khi bài chỉ chưa được bổ sung.

### 8.2. Chạm từ xem nghĩa

- Từ trong lời có gạch chân chấm, dùng button/điểm gọi có ngữ nghĩa rõ.
- Desktop popover cạnh từ; mobile sheet không che dòng đang đọc, chiều cao ban đầu khoảng tối đa 45% màn hình là điểm xuất phát để kiểm, nội dung dài cuộn được.
- Ô gồm từ, loại khi có, nghĩa, nguồn theo chính sách, Xem trong từ điển, bài khác nhắc tới khi có dữ liệu.
- Chỉ một ô mở; chạm ngoài/Esc/nút Đóng đóng; trả focus về từ.
- Không JavaScript: chú thích cuối khối lời với neo qua lại.
- Ảnh/tọa độ chỉ dùng khi đã xác nhận và có quyền; không ảnh thì ô vẫn cân đối.

### 8.3. Dị bản

Đánh số; so với Bản tham chiếu và tô chỗ khác theo âm tiết. Kết hợp màu với gạch/đánh dấu, không chỉ màu. Nơi/người kể/năm/nguồn chỉ khi có căn cứ và được phép hiện.

Dựng 1 dị bản, nhiều dị bản, lời khác dài hơn/ngắn hơn, dị bản không có nơi hoặc nguồn. Không gọi dị bản là bản sai.

Màn hình so sánh hai bản riêng là mục phát triển sau trong tài liệu cũ; không tự biến nó thành route bắt buộc ra mắt V2. Nếu đề xuất thêm, ghi riêng ngoài phạm vi chốt.

### 8.4. Thông tin tư liệu và chính sách hiện nguồn

Khối thu gọn cuối bài, mỗi lớp một hàng. Ví dụ **cấu trúc**, không phải dữ liệu nguồn thật:

| Lớp | Thông tin được phép hiện |
|---|---|
| Lời | Theo bản lưu truyền phổ biến · chưa đối chiếu sách · Vì sao? |
| Nghĩa | Biên soạn: <bút danh> · có hỗ trợ của AI, đã duyệt <ngày> khi có dữ liệu |
| Chú thích | 3 mục · nguồn tham khảo đã ẩn · Chính sách thu thập dữ liệu |
| Cách chơi | Wikipedia — <bài> · CC BY-SA 4.0 |
| Ảnh | Wikimedia Commons · <tác giả> · <giấy phép đúng của ảnh> |

Ba chính sách hồ sơ nguồn: **tại chỗ / trong Thông tin tư liệu / ẩn với người đọc**. Mặc định tái tạo chính sách hiện hành: chú thích chép từ Ca dao Mẹ ẩn nguồn; ghi công nguồn mở tại chỗ; nguồn người biên soạn điền thì hiện theo dữ liệu/chính sách.

**Nguồn đã ẩn không có URL trong HTML, dữ liệu client, tooltip hoặc thuộc tính ẩn gửi khách.** Không thiết kế bằng cách gắn link rồi che CSS. Người biên soạn đăng nhập có thể thấy phần riêng “Chỉ người biên soạn thấy dòng này · Nguồn đã ẩn: …”. Làm riêng hai mẫu quyền xem, không gửi dữ liệu nội bộ rồi dùng vai ở client để che.

Ghi công CC BY/CC BY-SA của ảnh/đoạn chép phải ở ngay nơi sử dụng, không chỉ giấu hết cuối bài. Không gán CC BY 4.0 của dự án cho mọi nội dung chép.

Tên **Thông tin tư liệu** là bản mặc định trong thiết kế; dựng thêm cùng khối tên **Lời này từ đâu?** trên trang so sánh để chủ dự án chọn. Không hỏi lại để dừng công việc.

### 8.5. Thanh hành động và trích dẫn

Nhóm chung: **Trích dẫn · Chia sẻ · Lưu · Trình chiếu · Aa**; điều khiển sáng/tối ở vị trí nhất quán. Trang bài thông thường Trích dẫn là chính; câu đố/đồng dao có hành động chính theo vùng đã nêu.

Mobile cho wrap/thu gọn hợp lý, không dùng 5 nút cùng trọng số lớn chen ngang. Nếu gom công cụ phụ vào disclosure, vẫn để hành động chính và lưu dễ thấy. Vị trí thanh ở các mẫu mà docs/27 chưa chỉ rõ cần ghi là đề xuất; không đẩy lời/nghĩa quan trọng xuống dưới.

Trích dẫn mở panel:

- Kiểu thông dụng và APA 7.
- **Sao chép kèm nguồn** mặc định; **Chỉ sao chép lời** là lựa chọn phụ.
- “Nguồn” trong trích dẫn lời là trang Phongdao và tên dự án mới, không tự lộ nguồn đang ẩn.
- Có tuỳ chọn kèm đáp án/chú thích khi có; ghi công phần chép phải đi cùng phần được chọn. Không mặc định làm lộ đáp án trong luồng đố.
- Ngày truy cập theo ngày thao tác, năm theo dữ liệu; không ghi một ngày/năm cố định như sự thật.
- Thành công: **“Đã sao chép. Cảm ơn bạn đã ghi nguồn!”**.
- Clipboard lỗi hoặc không JavaScript: văn bản trích dẫn vẫn chọn/copy tay được, có hướng dẫn.

Mẫu thông dụng: `“<Tên>” (<Thể loại>). Thư viện Văn học Dân gian Việt Nam. Truy cập tại: <URL>, ngày <dd/mm/yyyy>.`

Mẫu APA kế thừa: `Thư viện Văn học Dân gian Việt Nam. (<năm đăng>). <Tên> [<Thể loại>]. <URL>`.

### 8.6. Chia sẻ, lưu và Aa

- Chia sẻ dùng hộp chia sẻ của máy; không hỗ trợ thì sao chép link. Có thành công, hủy, lỗi. Không nhúng SDK Facebook/Zalo.
- Lưu có trạng thái đã/chưa lưu, thông báo khi bộ nhớ bị chặn; không báo lưu thành công nếu thực tế thất bại.
- Aa ba mức: vừa/lớn/rất lớn. Sáng/tối/theo hệ thống, nhớ trên máy, không nháy giao diện khi nạp.
- Không tự thêm phép đo mới cho lưu/vai/cỡ chữ. Đúng 5 sự kiện của dự án và lượt đọc; đây là ràng buộc bàn giao, không cần phô tên sự kiện lên UI.

### 8.7. Trình chiếu

Dựng chế độ toàn màn hình cho một bài, tuyển tập và danh sách Đã lưu. Có chữ lớn, sáng/tối, chuyển câu/đoạn, tiến độ ngắn, thoát. Phím mũi tên chuyển, Esc thoát, focus trả về nơi mở. Có nút chạm đủ lớn trên mobile.

Nhãn thể loại/dòng mức kiểm/trích dẫn tối thiểu không bị làm mất ý nghĩa khi đưa câu lên lớp. Không biến thành một công cụ soạn slide mới. Chữ không vượt khung với câu dài; không bật tự chạy mặc định.

### 8.8. Đọc cùng bé

Một dòng một lần, chữ rất lớn, thao tác sang dòng/quay lại/thoát, nhịp vỗ tay theo chữ **không âm thanh**. Reduced motion tắt nhịp chuyển động và giữ tô tĩnh. Không xin quyền microphone/camera; không thu dữ liệu trẻ.

### 8.9. In và ảnh chia sẻ

- Bản in trang bài: lời, nghĩa, chú thích cần thiết, mức kiểm/nguồn công khai phù hợp, URL/trích dẫn; ẩn nav và nút, nền trắng.
- Bản in tuyển tập: ngắt trang có chủ đích, tránh tách một câu ngắn khỏi nghĩa/trích dẫn; cho nhiều trang.
- Thiết kế ảnh chia sẻ **1200×630**: thể loại, tối đa 4 dòng đầu, dòng mức kiểm, phongdao.com; bố cục riêng cho câu một dòng và thơ. Dựng cả ảnh chung cho trang khác.
- Không đặt chữ quá nhỏ khiến mức kiểm thành trang trí không đọc được. Tình huống cờ nghi/cảnh báo dùng cách xử lý kế thừa phù hợp, không tạo một ảnh ca ngợi như câu chắc chắn đáng tin.
- Trình tạo thẻ ảnh 2 mẫu/2 khổ tự chỉnh là ý tưởng để sau trong hồ sơ cũ, không tự thêm như tính năng ra mắt.

## 9. Bàn biên tập — phải thiết kế cùng bộ UX/UI

### 9.1. Khung quản trị

Một mục **Phongdao** trong WordPress mở ứng dụng. Desktop có điều hướng nội bộ rõ; mobile bottom nav **Hôm nay · Duyệt · Tác phẩm · Thêm**. Thêm mở các nhóm quản lý còn lại, không tạo 13 menu con MVP.

Công cụ này phục vụ một người thao tác nhanh, vẫn dùng token chữ/màu phù hợp nhưng có thể đậm tính công cụ hơn phần đọc. Không chép dashboard thương mại gồm biểu đồ trang trí.

Tác phẩm dùng trình soạn có cấu trúc, **thay hẳn Gutenberg**. Trang tĩnh vẫn dùng Gutenberg. Giao diện quản trị phải phù hợp React và các gói WordPress có sẵn; không yêu cầu thư viện UI mới để dev làm theo thiết kế.

Các ID A dưới đây kiểm kê mẫu thiết kế, không phải đề nghị thêm các màn quản trị độc lập vào menu.

### A01 — Hôm nay

Từ trên xuống:

1. **Tuần này**: bài nên có nghĩa, câu người đọc mong có, báo lỗi; mỗi dòng có hành động mở đúng việc.
2. **Bắt đầu duyệt (N)**.
3. **Hộp việc**, lọc loại, thẻ đủ ngữ cảnh để quyết.
4. **Kho đang có/thiếu**, theo thể loại và cấp nội bộ.
5. **Máy đang làm**, trạng thái lấy nguồn/lỗi gần nhất.

Thứ tự ưu tiên: báo lỗi → mong có mà kho có → nháp nghĩa bài đọc/tìm nhiều → ứng viên → có thể trùng → người đọc gửi khác → gợi ý → Wikidata.

Dựng nhiều việc, không việc, có lỗi máy, kho thiếu một thể loại. Số liệu là dữ liệu thử, có nhãn trong môi trường thiết kế, không dùng như thành tích dự án.

### A02 — Duyệt nhanh

Mỗi thẻ là một quyết định, có vị trí `3/27`, loại việc, tác phẩm, lý do được ưu tiên, dữ liệu nguồn/ngữ cảnh cần để quyết. Nút ≥ 48 px, dùng một tay. Desktop có 1/2/3, J/K khi hợp ngữ cảnh, phím tắt không kích hoạt lúc gõ form.

| Loại thẻ | Phải thấy | Hành động |
|---|---|---|
| Ứng viên | Lời, dị bản, chú thích kèm nguồn nội bộ, thể loại, chống trùng | Đăng; Sửa; Để sau; Bỏ có lý do |
| Nháp nghĩa | Lời, chú thích, nháp ghi “AI, chưa duyệt”, dấu hỗ trợ AI | Đăng nghĩa/duyệt lớp; Sửa; Để sau; Bỏ nháp |
| Có thể trùng | Hai lời và chỗ khác | Cùng một bài; Gộp làm dị bản; Là bài khác; Để sau |
| Gợi ý chủ đề/vùng/mô-típ | Nội dung, giá trị đề xuất, lý do | Đồng ý; Sửa; Bỏ |
| Wikidata | Từ, thực thể, mô tả, ảnh khi có | Đúng; Sai; Bỏ qua |
| Người đọc gửi | Nội dung và bài liên quan | Áp dụng mở đúng vùng soạn; Đã xử lý; Bỏ rác |
| Cảm nghĩ/phân tích | Toàn nội dung cần xem | Đăng; Bỏ |
| Mong có | Query, số lần, câu khớp trong kho riêng tư | Tạo ứng viên ngay; Để sau |
| Báo lỗi | Lỗi, tác phẩm, ngữ cảnh | Sửa; Không phải lỗi |

Sau quyết định có **Hoàn tác trong 10 giây**; trạng thái đang lưu tránh bấm đôi; lỗi giữ thẻ và phần vừa sửa; thẻ đã xử lý bởi phiên khác không được ghi đè im lặng.

Không đặt một nhãn “Đăng” mơ hồ cho cả tạo tác phẩm lẫn duyệt một lớp mà không cho biết hệ quả. Có thể giữ từ chính theo docs/28 và thêm phụ đề/nội dung ngữ cảnh.

Dấu “Có hỗ trợ của AI” và “Đã duyệt” thuộc quyết định người có quyền. Hiển thị trung thực dữ liệu đã lưu; với nội dung mới không tự tick để coi như người đã xác nhận. Dựng trạng thái để người duyệt chủ động xác nhận/bỏ dấu đúng thực tế, không để máy tự đổi dấu khi họ chưa quyết. Nếu cách trình bày checkbox trong mockup cũ gây hiểu nhầm, ghi chú rõ trong handoff thay vì tự thay chính sách.

### A03 — Danh sách tác phẩm

Tìm; lọc thể loại/cấp nội bộ/thiếu nghĩa/chủ đề/vùng/cờ; sắp theo đọc nhiều; trạng thái nháp/đã đăng/ẩn; vào Soạn. Desktop bảng hoặc hàng giàu thông tin, mobile thẻ/hàng gọn; không ép bảng 12 cột cuộn ngang.

### A04 — Soạn tác phẩm có cấu trúc

Giữ trật tự vùng sau, mobile không giấu phần quan trọng vào sidebar:

1. Tên, thể loại, trạng thái; Lưu nháp / Đăng hoặc Cập nhật / Xem trang.
2. **Lời**: mỗi dòng nguồn một dòng; thể thơ nhận được; **dải Có thể trùng ngay dưới ô Lời**.
3. **Mức kiểm**: Lời đã đối chiếu với — hồ sơ sách, trang, đường dẫn. Trống nếu chưa có, máy không tự điền.
4. **Dị bản**: lời, nơi, người kể, năm, nguồn; xếp lại được bằng kéo và nút lên/xuống dùng bàn phím.
5. **Nghĩa**: Hiểu nhanh; với tục ngữ có nghĩa đen/bóng/dùng khi/câu gần-trái nghĩa. Nháp AI nằm riêng “Nháp máy — chưa duyệt”, nút Dùng nháp này; không đè lên lớp đã duyệt.
6. **Chú thích**: từ, lời giải, nguồn, nối mục từ, trạng thái neo; lớp chép có nhãn “Chép từ <nguồn>”.
7. **Theo thể loại**: vè đoạn/bối cảnh; đồng dao trò chơi; câu đố câu hỏi/đáp án/gợi ý/ảnh; truyện chỉ bộ đợt 5.
8. **Phân loại**: chủ đề, vùng, mô-típ, hình thức, tuyển tập; gợi ý máy kèm lý do và thao tác người xác nhận.
9. **Cờ/SGK**: cờ nghi + lý do + nguồn; bộ sách/lớp/bài/trang khi đã thấy sách. Không suy từ AI.
10. **Tư liệu**: xem đúng phần người đọc thấy, tách nguồn nội bộ.
11. **Lịch sử**: thay đổi các lớp.

Thiếu Hiểu nhanh, chủ đề, nghi trùng, chú thích chưa neo là **nhắc**, không khoá Đăng vì lý do biên tập. Lỗi kỹ thuật/lằn ranh/quyền mới có thể chặn và phải chỉ rõ chỗ sửa.

Sửa lời đã đăng phải có xác nhận có ngữ cảnh và lịch sử trước/sau. Chuyển đi khi còn thay đổi chưa lưu có bảo vệ. Có trạng thái lưu thành công, lỗi, mất mạng, hết phiên, xung đột phiên bản. Đừng tự bổ sung cơ chế autosave có vẻ production nếu chưa chốt; có thể đề xuất riêng.

### A05 — Tuyển tập

Danh sách và editor: tên, loại, đối tượng, lời dẫn do người viết, nhóm, tìm tác phẩm để thêm, thứ tự, Hợp khi từng mục, trạng thái. Đếm tổng câu và câu có Hiểu nhanh; hiển thị điều kiện công khai 8 câu+lời dẫn. Có xem trước đọc liền.

Không sinh lời dẫn bằng AI hoặc sao chép nghĩa vào editor tuyển tập. Dữ liệu của bài đơn được tham chiếu. Kéo sắp xếp có nút thay thế dùng bàn phím.

### A06 — Từ điển/Wikidata

Danh sách, editor mục từ, nguồn lời giải, loại từ/địa danh/nhân vật, ảnh có ghi công/ẩn ảnh, tác phẩm dùng từ. Hàng xác nhận thực thể Đúng/Sai/Bỏ qua và lý do/đối chiếu đủ rõ. Trạng thái chưa xác nhận không bị trình bày như đã xác nhận.

### A07 — Trò chơi

Thông số, các bước, nguồn, giấy phép, bài đồng dao. Nút lấy **mục Cách chơi từ Wikipedia tiếng Việt** có nguồn đúng bài, xem nội dung lấy về và thao tác người quyết; không nút AI tạo luật chơi. Không tự cho mọi văn bản lấy về là của dự án.

### A08 — Mô-típ

Nhóm gợi ý từ cụm mở đầu, bài thành viên, lý do → người đặt tên, bỏ bài không hợp, viết mô tả → tạo/sửa mô-típ. Cho xem nhóm đã xử lý, không sinh tên có vẻ kết luận học thuật tự động.

### A09 — Chủ đề/vùng

Bộ của dự án, bảng ánh xạ chủ đề nguồn sang chủ đề dự án, mô tả, gợi ý có lý do, chọn để duyệt. Phân biệt “vùng lưu truyền” với địa danh. Không tự tạo giá trị taxonomy ngoài bộ đã chốt để lấp dropdown.

### A10 — Thể loại

Giới thiệu do người viết, bài tiêu biểu, số bài/cấp nội bộ, tình trạng xuất hiện menu. Không nhập lại L0–L3 bằng tay. Bộ ra mắt chỉ quản lý thể loại lõi; cổng mở thể loại mới nằm bộ đợt 5.

### A11 — Hồ sơ nguồn

Loại, tên, tác giả, năm, giấy phép, mẫu ghi công, trạng thái xin phép, chính sách hiện, Chép chú thích, ghi chú. Trống/chưa rõ là trạng thái hợp lệ, không tự điền thông tin thư mục.

Quản trị mới được đổi giấy phép/trạng thái xin phép. Đổi chính sách hiện phải có nhắc **“Đổi ở đây là đổi chính sách — cần ghi quyết định”** và chỗ đối chiếu quyết định theo quy trình dự án. Không tự đổi vì có nguyên mẫu đẹp hơn. Xem trước tác động với người đọc, có nguồn tại chỗ/trong tư liệu/ẩn.

### A12 — Nguồn tự động

Bật/tắt, lịch, giới hạn mỗi lượt, thể loại lấy, Chép chú thích, hồ sơ nguồn, lấy ngay, trạng thái cuối. Nút trong prototype chỉ mô phỏng; không gọi thu thập thật. Việc lấy dữ liệu vào kho không đồng nghĩa xuất bản.

### A13 — Kho thu thập

Tìm, lọc trạng thái/nguồn/thể loại; ứng viên; Đã gộp; xem quyết định tự gắn; **Tách ra**; Bỏ/Xem lại. Kho riêng tư, có nhãn rõ; không có hành động “xuất bản toàn bộ kho”.

### A14 — Máy đang làm

Hàng đợi, đang chạy/chờ/thành công/lỗi, lần thử, lỗi gần nhất, thời điểm. Người vận hành hiểu điều đang diễn ra mà không cần đọc stack trace; chi tiết kỹ thuật thu gọn khi cần. Không giả trạng thái thành công.

### A15 — Người đọc gửi

Danh sách đầy đủ của góp ý/dị bản/số trang/báo lỗi/cảm nghĩ/phân tích/mong có. Cùng việc với Hộp việc, không tạo hàng chờ thứ hai tách rời. Áp dụng mở đúng chỗ soạn; đăng cảm nghĩ/phân tích vẫn do người bấm. Không hiển thị email/IP không được thu.

### A16 — Số liệu

Tuần: lượt đọc, bài đọc nhiều, tìm không ra, mong có, trích dẫn, từ chạm nhiều, chia sẻ; theo thể loại/cấp nội bộ. Dùng bảng và chỉ số vừa đủ để dẫn hành động: “câu tìm không ra → thêm bài; từ chạm nhiều → viết nghĩa kỹ hơn”.

Không analytics danh tính người đọc, bản đồ IP, session replay, biểu đồ trang trí hoặc số liệu bên mạng xã hội. Có trạng thái chưa đủ dữ liệu, không dựng phần trăm tăng trưởng từ số 0.

### A17 — Cài đặt

Bút danh; số ứng viên/ngày; đường tắt chính sách nguồn; tên khối tư liệu; Turnstile; bảo trì. Khoá dịch vụ bị che và không đưa giá trị thật vào ảnh/mẫu. Cài đặt tuỳ chọn người duyệt khác người nhập dành cho khi có người thứ hai, không thành bước bắt buộc của hiện tại.

### A18 — Lịch sử

Ai, khi nào, đối tượng/lớp, hành động, trước/sau; xem tại tác phẩm và nguồn. Không hứa công khai mọi lịch sử nguồn nội bộ cho khách. Tách nhật ký xem lại với nút hoàn tác có điều kiện; không tạo khôi phục tuỳ ý mọi phiên bản nếu đặc tả chưa có.

### A19 — Gộp hai tác phẩm đã đăng

Hai tác phẩm, chỗ khác, chọn bài giữ lại, xem hệ quả: bài kia thành dị bản, URL cũ 301, lượt đọc/cảm nghĩ/phân tích/tuyển tập dồn sang, bài kia vào thùng rác khôi phục được. Có xác nhận rõ tác phẩm nào giữ; không nút “Dọn sạch tự động”.

Mobile xếp chồng nhưng vẫn so được hai lời; desktop cạnh nhau. Mọi quyết định gộp trong nguyên mẫu chỉ là mô phỏng.

### A20 — Cần xem sau chuyển dữ liệu

Danh sách bài có lý do chưa tách chắc chắn, link mở đúng vùng sửa; trạng thái đã đăng giữ như dữ liệu thật, không đổi thành lỗi công khai. Báo cáo chỉ ID/số lượng/lý do; không xuất kho thô vào báo cáo công khai.

Chạy nhập/hoàn tác là công cụ vận hành theo docs/29, không tự thêm dashboard migration điều khiển production. Thiết kế phần báo cáo/Cần xem và thông tin bảo trì; lệnh thật vẫn ngoài phạm vi nguyên mẫu.

### A21 — Quyền và phiên quản trị

Dựng trạng thái: không đủ quyền; phiên hết hạn giữ phần đang sửa để người dùng xử lý; lỗi quyền; đang đăng nhập bằng vai Quản trị/Biên tập/Cộng tác trong trình thử vai của prototype.

| Vai | Thấy/làm được |
|---|---|
| Quản trị | Toàn bộ, gồm nguồn, giấy phép, xin phép, cài đặt |
| Biên tập | Soạn, duyệt, quản lý nội dung theo quyền; không đổi hồ sơ nguồn/quyền sử dụng thuộc quản trị |
| Cộng tác | Gửi đối chiếu/soát lời khi mở; không Đăng |

Không tạo đăng ký tài khoản công khai. WordPress tiếp tục xử lý đăng nhập; không cần thiết kế lại hệ xác thực hoặc quản lý hosting.

## 10. Bộ mở rộng đợt 5 — thiết kế tách riêng

Tạo khu **“Đợt 5 — thiết kế chuẩn bị, chưa bật trên site ra mắt”** trong chỉ mục prototype. Nhãn này thuộc môi trường thiết kế, không thành banner tính năng chưa có trên site thật.

| ID | Bề mặt | Nội dung phải thể hiện |
|---|---|---|
| F01 | Cho lớp học `/lop-hoc/` | Chọn lớp/bộ sách nhớ trên máy; điều hướng bộ câu; chỉ hiện khi ≥ 3 bài học đủ dữ liệu |
| F02 | Lớp và bộ câu SGK | Bài học, lời/Hiểu nhanh dùng lại từ tác phẩm, bằng chứng SGK; Trình chiếu/Phiếu in/Sao chép trích dẫn cả bộ; thiếu bài → Gửi tên sách, trang |
| F03 | Phiếu in A4 | Lời, Hiểu nhanh, chú thích, trích dẫn; nền trắng; ngắt trang; một trang mẫu và bộ dài nhiều trang khi cần |
| F04 | Truyện | Tên → bản kể theo sách/năm/trang, bút danh kể lại khi có → thời gian/tuổi/số đoạn → Kể cho bé/mục lục → đoạn → nhân vật/chú thích/ý nghĩa/bản kể khác → phần cuối chung |
| F05 | Hát ru/hò/lý dạng văn bản | Mẫu thể loại và tác phẩm dùng lại hệ đọc; chỉ thêm khác biệt có căn cứ. Không giả video/audio diễn xướng; chưa qua cổng thì không lên menu |
| F06 | Nghe và phát giọng sinh sẵn | Bấm mới tải/phát; dừng/tiếp; “Giọng đọc máy”; giọng theo dữ liệu được duyệt; thiếu audio thì không để player rỗng. Hub/route riêng chưa chốt: ghi là đề xuất thể hiện |
| F07 | Đối chiếu sách — quản trị | Lời hiện tại và trang quét, đề xuất máy, người bấm xác nhận; tên sách/trang không tự điền như sự thật đã xác minh; desktop cạnh nhau, mobile đổi vùng xem vẫn giữ ngữ cảnh |
| F08 | Kiểm giọng — quản trị | Lô 30 bài đủ thể loại, nghe từng bài, Đạt/Sai, ghi lỗi ngắt nhịp, sửa hoặc tắt giọng bài; không sinh cả kho trước khi người nghe thử |
| F09 | Cổng thể loại — quản trị | Mẫu qua trợ năng, số bài đã đăng/L2, hồ sơ nguồn/quyền; trạng thái chưa đủ/đủ chờ người quyết, không tự bật vì đủ số |
| F10 | Dữ liệu mở | Khả năng xuất bản có phiên bản đã chốt; UI tải công khai chỉ là đề xuất. Nêu phạm vi lớp đã duyệt, tác phẩm đã đăng, nguồn có quyền; không xuất nguồn ẩn/kho thô/dữ liệu cá nhân |

Truyện chỉ từ sách hết bản quyền có bản quét **được chủ dự án kiểm quyền**, không lấy bản kể hiện đại rồi ghi thành dân gian. Độ tuổi và bối cảnh nhạy cảm chỉ dùng khi được biên tập, cảnh báo đặt ngay đầu nơi cần.

“Kể cho bé” có thể là bố cục đọc từng đoạn; hành vi cụ thể chưa đủ chốt như Đọc cùng bé, hãy ghi rõ phần đề xuất. Không tự biến thành TTS kể chuyện hoặc thu âm giọng trẻ.

Giọng máy không thay diễn xướng, không gắn giọng nhân bản người thật khi thiếu hồ sơ đồng ý. Phương án giọng có sẵn của máy từ MVP là tài sản kế thừa nếu dev cần giữ; không đồng nhất nó với mục Nghe sinh sẵn đã qua kiểm. Trạng thái nào xuất hiện ở bản ra mắt phải theo phạm vi V2 được chủ dự án xác nhận, không tự bật mục Nghe.

## 11. Dữ liệu để thiết kế — quy tắc bắt buộc

### 11.1. Không sáng tác dữ liệu văn hoá

Không tự viết hoặc nhớ lại lời ca dao, tục ngữ, dị bản, nguồn, sách/trang, vùng, địa danh, tác giả, niên đại, sự kiện, luật chơi. Không soạn giải nghĩa có vẻ đã được duyệt cho một câu thật để làm mockup.

Ưu tiên fixture có sẵn trong `wordpress/tests/mau-giao-dien.php`. Ví dụ lời tổng hợp của fixture:

> Dòng mẫu một của bài này  
> Dòng mẫu hai dài hơn chút cho vừa  
> Dòng mẫu ba của bài này  
> Dòng mẫu bốn dài hơn chút cho xong

Đây là chữ thử kiểu lục bát, **không phải ca dao thật**. Các mẫu “Tục ngữ mẫu ngắn một dòng”, “Câu đố mẫu dòng một”, “Đáp án mẫu”, “Khổ 1 dòng mẫu số 1” cũng chỉ là fixture.

Nguyên mẫu phải có nhãn **“Trang mẫu giao diện — dữ liệu thử”** trong môi trường xem thử. Nguồn thử dùng “Nguồn mẫu”, URL `example.org`, sách thử ghi rõ “Sách mẫu tổng hợp”, tuyệt đối không làm chúng trông như hồ sơ tư liệu thật.

Nếu có dữ liệu thật do tôi cung cấp hoặc dữ liệu công khai đã kiểm được, chỉ dùng nguyên văn, giữ dòng/chính tả và ghi xuất xứ trong hồ sơ mẫu. Không nâng mức kiểm hoặc thêm nghĩa/nguồn không có. Không coi câu xuất hiện trong một tài liệu ý tưởng là đã được duyệt làm nội dung site.

Không dùng lorem ipsum không dấu: cần kiểm tiếng Việt và độ dài thật. Có thể tạo câu hướng dẫn giao diện và mô tả trạng thái thử; không sáng tác nội dung dân gian.

### 11.2. Bộ mẫu tối thiểu

| Fixture | Mục tiêu kiểm |
|---|---|
| Thơ 4/8/16 dòng | Thụt lục bát, chạm từ, cỡ chữ, toolbar |
| Câu một dòng ngắn/dài | Nghĩa hiện sớm; reflow trên 320 px |
| Vè 60 và > 200 dòng | Mục lục, đọc tiếp, Đọc toàn bài; không vỡ trang |
| Đồng dao có/không trò chơi | Thông số đầy đủ/thiếu; cách chơi; Đọc cùng bé |
| Câu đố | Gợi ý/đáp án đóng/mở; không ảnh/có ảnh có quyền |
| Bài thiếu nghĩa, nguồn chưa rõ | Không sinh chữ độn; không xác minh giả |
| Bài có cờ, nhiều dị bản | Cờ đi theo mọi nơi; so chữ khác |
| Tuyển tập 8/30 câu | Mục lục, lặp nghĩa dùng chung, trình chiếu, in |
| Mục từ thường/địa danh | Không ảnh/không tọa độ; nguồn ẩn; danh sách liên quan |
| Nguồn tại chỗ/trong tư liệu/ẩn | Khách và người biên soạn thấy khác nhau đúng quyền |
| Thẻ nháp AI/ứng viên/trùng | Quyết định con người, lỗi lưu, hoàn tác |

Tài sản ảnh cần tên tác giả, giấy phép và nguồn thực. Nếu không có asset đủ quyền, dùng bản chỉ chữ; không vẽ một khung trống với nhãn “ảnh đẹp” vào trang cuối. Không dùng ảnh AI làm tư liệu lịch sử hoặc bản đồ chính xác.

## 12. Trợ năng, trạng thái và hiệu năng

### 12.1. Trợ năng

Thiết kế theo WCAG 2.2 AA cho cả người đọc và quản trị:

- Chữ thường tương phản ≥ 4,5:1; chữ lớn ≥ 3:1; thành phần/focus theo ngưỡng phù hợp. Đo trên nền thực ở cả sáng/tối.
- Nhãn không chỉ dựa vào màu; trường có label thật, không dùng placeholder thay label.
- Tab theo thứ tự đọc; skip link; focus rõ, không bị thanh cố định che.
- Enter/Space mở điều khiển, Esc đóng modal, trả focus đúng chỗ; modal mới quản focus theo ngữ nghĩa, không gắn role dialog cho mọi vùng thu gọn.
- Disclosure dùng `<details>/<summary>` khi phù hợp; thông báo trạng thái dùng aria-live nhẹ, không đọc lại cả trang khi gõ.
- Reduced motion không chuyển động vỗ tay/chạy chữ; không dùng nhấp nháy.
- Zoom 200%, reflow 320 CSS px, chữ lớn không mất chức năng.
- Bản đồ có danh sách thay thế, kéo thả có nút thay thế; phím tắt không cản nhập chữ.
- Không JavaScript vẫn đọc lời, chú thích, nghĩa, mở đáp án, dùng form tìm và các liên kết cơ bản.

### 12.2. Ma trận trạng thái

Mỗi màn hình áp dụng trạng thái thích hợp, không dựng skeleton cho nội dung server-rendered đã có chỉ để trang trông như SPA:

| Loại trạng thái | Ví dụ bắt buộc |
|---|---|
| Đủ dữ liệu | Trang đọc, tuyển tập, thẻ việc |
| Thiếu một lớp | Không nghĩa/ảnh/nguồn công khai/bối cảnh/trò chơi |
| Trống có ý nghĩa | Đã lưu trống, hết việc, chưa đủ bộ đố |
| Không kết quả | Kho lọc rỗng, tìm không ra, kho có nhưng chưa biên soạn |
| Đang xử lý | Tìm khi gõ, gửi form, lưu thẻ, lấy nguồn mô phỏng |
| Lỗi có phục hồi | Mạng, clipboard, bộ nhớ trình duyệt, SVG lỗi, hết phiên |
| Thành công | Copy, gửi chờ duyệt, quyết định và Hoàn tác |
| Quyền | Khách/người biên soạn; Quản trị/Biên tập/Cộng tác |
| Thiết bị | Mobile, desktop, tablet, bàn phím ảo, safe area |
| Chế độ | Sáng/tối, cỡ chữ lớn, reduced motion, không JavaScript, in |

### 12.3. Ràng buộc bàn giao production

- WordPress làm khung; plugin/theme mới trong `wordpress/v2/` cho đến ngày chuyển.
- Theme khối độc lập, `theme.json`; khối động render phía server; JavaScript thuần tối thiểu ở phần công khai. Không yêu cầu React ở trang đọc production.
- Quản trị React dùng thư viện WordPress có sẵn, thay Gutenberg cho tác phẩm.
- Trang tác phẩm ≤ **200 KB**; JavaScript trang tác phẩm ≤ **21 KB**; tuyển tập 30 câu không ảnh ≤ **300 KB**.
- Ảnh tải lười, có kích thước; CLS ≤ 0,1; mục tiêu LCP p75 ≤ 2,5 s và INP p75 ≤ 200 ms khi có số đo thực.
- Phông tự lưu, không Google Fonts runtime, không SDK mạng xã hội, không dịch vụ bản đồ ngoài tự tải.
- Không cookie đo lường, không thu IP/định danh người đọc, không analytics bên thứ ba.
- Chỉ 5 sự kiện `tim_khong_ket_qua`, `trich_dan_sao_chep`, `chu_giai_mo`, `chia_se`, `mong_co_cau` cùng lượt đọc; không thêm đo vai/lưu/độ tuổi.

Bạn phải thiết kế để dev có thể đạt các ngân sách đó. Nguyên mẫu có runtime riêng cần công bố giới hạn; không tuyên bố production đã đạt 21 KB chỉ vì ảnh thiết kế nhẹ. Chỉ ghi “đã đo/đã qua kiểm” khi có kết quả thực.

## 13. Những câu chữ và phân biệt phải giữ

| Tình huống | Dùng |
|---|---|
| Tên bản đang đọc | Bản tham chiếu |
| Nghĩa ngắn | Hiểu nhanh; riêng tục ngữ có thể dùng Nghĩa |
| Mức kiểm chưa đối chiếu | Lời theo bản lưu truyền phổ biến · chưa đối chiếu sách · Vì sao? |
| Nghĩa có AI đã duyệt | Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt |
| Thiếu nội dung | Bài đang được bổ sung. Bạn biết thêm? Góp ý |
| Không có nghĩa tục ngữ | Câu này chưa có giải nghĩa. Bạn giúp được? Góp ý |
| Nghi gán nhầm | Nghi không phải dân gian |
| Mong có | Mong có câu này → Đã ghi nhận, cảm ơn bạn |
| Nguồn theo lớp | Thông tin tư liệu; biến thể tên Lời này từ đâu? để chủ dự án chọn |
| Vùng của tác phẩm | Nơi lưu truyền |
| Địa danh trong lời | Được nhắc đến |

Không dùng **“bản gốc”, “bản đúng”, “bản chuẩn”, “bản sai”, “AI giải thích”, “câu hay nhất”** làm nhãn UI. Không dùng “đã xác minh”, “học thuật bảo chứng”, “hàng đầu” nếu dữ liệu không chứng minh. Không hiển thị mã L0–L3, A/B, `NHAP_AI`, `CAN_BO_SUNG` cho người đọc.

Văn bản dự án tự viết đặt dấu theo quy ước **hoà, thuỷ, khoẻ**. Lời trích nguyên văn giữ cách viết của nguồn.

## 14. Cách thực hiện để hoàn tất toàn bộ, có thể duyệt sớm

### Bước 1 — Kiểm kê trước khi dựng

Lập bảng phủ màn hình gồm R01–R31, các chế độ mục 8, A01–A21, F01–F10. Ghi nguồn tương ứng, trạng thái cần dựng, nhóm ra mắt/đợt 5. Việc này ngắn gọn, sau đó bắt đầu dựng ngay.

Nếu gặp mâu thuẫn lịch sử, xử theo mục 2. Nếu thiếu kích thước/cách trình bày, đề xuất phương án đơn giản phù hợp art direction. Không hỏi các câu đã có đáp án trong prompt. Không vì thiếu URL một hub mà dừng thiết kế những trang còn lại.

### Bước 2 — Dựng lát cắt có thể xem ngay

Ưu tiên 8 màn đại diện, mỗi màn mobile+desktop:

1. Trang chủ.
2. Ca dao có nghĩa/chú thích/dị bản.
3. Tục ngữ, nghĩa xuất hiện sớm.
4. Tuyển tập đọc liền.
5. Tìm có/không kết quả và Mong có.
6. Đồng dao + Đọc cùng bé.
7. Hôm nay.
8. Duyệt nhanh.

Kèm trang so A/B màu và bảng component. Đây là bản xem sớm để tôi đánh giá bố cục, không phải điểm kết thúc nhiệm vụ. Không tự kết luận “đã thiết kế toàn bộ” sau 8 màn này.

### Bước 3 — Hoàn tất phần ra mắt

Áp hệ thống đã dựng cho toàn bộ R, A, các panel và chế độ, trạng thái thiếu/lỗi, in và ảnh chia sẻ. Đảm bảo các đường đi chính thao tác được, liên kết chéo đúng ngữ cảnh.

### Bước 4 — Hoàn tất bộ đợt 5 riêng

Thiết kế F01–F10 với các điều kiện chưa mở. Những chỗ chưa đủ hành vi chốt phải ghi đề xuất, không chèn vào bản ra mắt.

### Bước 5 — Tự kiểm và bàn giao

Kiểm theo ma trận phủ và các hành trình mục 15. Nêu lỗi còn lại thật, không dùng một câu “đã tối ưu UX” thay bằng chứng. Không coi trang thiết kế là bằng chứng một tính năng production đã được xây.

Nếu giới hạn môi trường khiến không hoàn tất trong một lượt, lưu trạng thái bằng chỉ mục màn hình và danh sách còn thiếu, rồi tiếp tục cùng hệ thống. Không bỏ các màn còn lại, không tự thu hẹp yêu cầu thành landing page.

## 15. Hành trình bắt buộc nối được trong prototype

| ID | Hành trình | Điều kiện kiểm |
|---|---|---|
| J01 | Trang chủ → gõ không dấu → kết quả đúng → tục ngữ | Thấy nghĩa trong ≤ 10 giây khi thử với người dùng |
| J02 | Chủ đề lao động → chọn/lưu 5 câu → Đã lưu → trình chiếu | Vào trình chiếu trong ≤ 3 phút; không cần mở Cho lớp học đợt 5 |
| J03 | Cùng bé chơi → lọc 4 trẻ/5 tuổi/trong nhà → trò chơi → đồng dao → Đọc cùng bé | Chọn được trò phù hợp trong ≤ 1 phút với dữ liệu thử phù hợp |
| J04 | Ca dao → chạm từ → mục từ → bài khác nhắc đến | Đóng sheet trả đúng focus, không mất bài đang đọc |
| J05 | Bài → dòng mức kiểm/Vì sao? → Thông tin tư liệu → Phương pháp | Người thử nói đúng lời theo đâu, ai giải nghĩa, phần nào chưa đối chiếu |
| J06 | Tìm không thấy nhưng kho có → Mong có | Có xác nhận, không lộ lời kho |
| J07 | Bài → trích dẫn thông dụng/APA → copy | Có văn bản preview, thông báo thành công hoặc copy tay khi lỗi |
| J08 | Bài → gửi số trang/dị bản/cảm nghĩ | Giữ chữ khi lỗi, gửi xong chờ duyệt, không công khai tự động |
| J09 | Câu đố → gợi ý → hiện đáp án → Đố vui → hoàn tất lượt | Không tự lộ đáp án, không điểm hoặc phạt |
| J10 | Hôm nay → nháp nghĩa → sửa → người quyết duyệt → Hoàn tác | Mục tiêu ≤ 60 giây/thẻ, không máy tự duyệt |
| J11 | Ứng viên → Sửa → kiểm trùng → Đăng mô phỏng → xem trang đọc | Nhận rõ kho/ứng viên/tác phẩm; nhắc biên tập không khoá Đăng |
| J12 | Có thể trùng → so hai bài → chọn giữ → xác nhận gộp mô phỏng | Biết URL và dữ liệu liên quan chịu tác động |
| J13 | Nguồn → xem chính sách ẩn → preview khách và biên soạn | Khách không nhận URL nguồn nội bộ |
| J14 | Đợt 5: lớp/bộ sách → bộ câu → phiếu A4/trình chiếu | Chỉ gắn SGK khi đã có hồ sơ đúng; không tự gán sách |
| J15 | Bài dài → mục lục → đọc tiếp → cỡ chữ rất lớn → in | Không mất chữ, không tràn ngang, không lặp mục lục |

Các thời gian là tiêu chí cần đo khi người thật dùng thử, không phải thành tích được khẳng định sẵn. Kế hoạch chính thức là 5 người, 2 giáo viên + 1 phụ huynh + 2 học sinh/người trẻ, 15 phút/người; mục tiêu ≥ 85% tác vụ. Có thể dùng nguyên mẫu để phát hiện vướng mắc sớm; không giả là đã tổ chức thử.

## 16. Bộ bàn giao bắt buộc

### 16.1. Bản xem tương tác

- Một điểm mở rõ ràng và chỉ mục toàn bộ màn hình.
- Có đường xem **Người đọc / Bàn biên tập / Đợt 5 / Hệ thống thiết kế / So màu** trong công cụ duyệt thiết kế; các mục quản lý prototype này không xuất hiện trên site production.
- Duyệt được khung mobile và desktop, xem toàn trang, chuyển trạng thái, thực hiện J01–J15.
- Giao diện thực tế dùng tiếng Việt, không nhúng mã task hay ghi chú dev vào luồng người đọc.

### 16.2. Bộ thiết kế và token

- Màu sáng/tối, bảng A/B, thang chữ/khoảng cách, kích thước vùng chạm, bề rộng/lề/breakpoint, trạng thái focus, quy tắc icon/ảnh.
- Component và biến thể, auto layout/responsive nếu công cụ hỗ trợ; tên nghiệp vụ nhất quán.
- Token CSS/JSON hoặc định dạng xuất khả dụng có thể chuyển sang `theme.json`; không chỉ ảnh chụp token.

### 16.3. Đặc tả bàn giao cho dev

Mỗi mẫu có: ID, route/đích nối, mục tiêu, nguồn yêu cầu, thứ tự khối, trạng thái mặc định, tương tác, mobile/desktop, trống/lỗi, không JavaScript, in, quyền xem, điều kiện hiển thị.

Lập bảng ánh xạ sang mẫu WordPress và khối động hiện hành. Ví dụ:

| Thiết kế | Mẫu/khối phù hợp |
|---|---|
| Trang chủ | `front-page`; `loi-vao`, `vai`, `cau-hom-nay`, `ban-co-biet`, `danh-sach` |
| Các tác phẩm | `single-tac_pham-{the-loai}`; `loi`, `muc-kiem`, `nghia`, `chu-thich`, `di-ban`, `boi-canh`, `doan`, `thuc-hanh` |
| Phần cuối/hành động | `hanh-dong`, `tu-lieu`, `bien-soan`, `thuoc-tuyen-tap`, `doc-tiep`, `gop` |
| Kho/taxonomy | `archive-tac_pham`, `taxonomy-*`; `bo-loc`, `the-bai`, `danh-sach` |
| Tuyển tập | `archive-tuyen_tap`, `single-tuyen_tap`; `tuyen-tap`, `hanh-dong` |
| Tìm/bản đồ/đố vui | `tim`, `ban-do`, `do-vui` và mẫu/page tương ứng |
| Tĩnh/phân tích/lỗi | `page`, `single-phan_tich`, `404`; bảo trì do plugin |

Tên khối chính xác theo docs/26 khi dev triển khai; nếu một thiết kế cần khối mới phải ghi rõ đề xuất, không tự tạo nghĩa vụ kỹ thuật mới mà không báo.

### 16.4. Ma trận phủ và danh sách cần chọn

Bảng cuối gồm mỗi ID R/A/F + các chế độ chung, đã có mobile/desktop/trạng thái/luồng nào, đã kiểm bằng cách gì, còn thiếu gì. Không tính một trang chỉ có tên trong chỉ mục là đã thiết kế.

Danh sách chủ dự án cần chọn tối thiểu:

1. Bảng màu A hay B; mặc định làm trên A.
2. Tên khối Thông tin tư liệu hay Lời này từ đâu?; mặc định Thông tin tư liệu.
3. Lục bát giữ mặc định thụt câu lục; chỉ chọn lại khi xem mẫu và thử Q-006.
4. Cách mở Khám phá trên mobile/route chưa chốt, cách đặt công cụ ở mẫu chưa quy định vị trí cụ thể.
5. Những hành vi đợt 5 còn là đề xuất: hub Nghe, Kể cho bé, bề mặt dữ liệu mở.

Không thêm câu hỏi về đổi nền tảng, tài khoản người đọc, chatbot, quảng cáo hoặc tách tục ngữ/thành ngữ; các phần đó đã có quyết định.

### 16.5. File/export

Xuất file thiết kế có thể chỉnh sửa nếu môi trường hỗ trợ, code nguyên mẫu/tài nguyên cần để mở lại, token, đặc tả và bảng phủ. Nếu không hỗ trợ một định dạng như Figma, nói rõ và giao định dạng thực tế có thể dùng; không tạo một link/file giả hoặc tuyên bố đã xuất khi chưa có.

Tài sản có giấy phép phải kèm bảng ghi công. Dữ liệu thử phải nhận diện được. Không để API key, dữ liệu riêng tư, kho thô hoặc thông tin đăng nhập vào bất kỳ gói bàn giao nào.

## 17. Tự kiểm trước khi nói đã hoàn tất

- [ ] Có đủ R01–R31, A01–A21 và F01–F10, hoặc khai báo chính xác phần còn thiếu.
- [ ] Năm mẫu đọc có khác biệt nhiệm vụ, không chỉ đổi nhãn/màu.
- [ ] Trang chủ đúng thứ tự chốt; chọn vai chỉ đổi khối 4–8.
- [ ] Tên mới nhất quán ở header/footer/trích dẫn/ảnh chia sẻ/trang dự án.
- [ ] Lời, mức kiểm, nghĩa và hành động đúng thứ bậc; thông tin nguồn không bị đổi chính sách bằng thiết kế.
- [ ] Nguồn ẩn không được gửi cho khách; ghi công mở ở đúng chỗ.
- [ ] Không lộ lời kho, đáp án chưa mở, nháp AI chưa duyệt hoặc hồ sơ riêng tư.
- [ ] Cờ nghi đi theo tác phẩm ở mọi bề mặt.
- [ ] Tuyển tập dùng lại tác phẩm; vùng lưu truyền khác địa danh được nhắc đến.
- [ ] Đã thể hiện bài thiếu lớp, bài rất dài, không ảnh, lỗi tìm/lưu/gửi và trống.
- [ ] Các nút/trường dùng được trên 390 px, chữ lớn và bàn phím; không thanh cố định che focus.
- [ ] Có kiểm sáng/tối, tương phản, reduced motion, in và phương án không JavaScript.
- [ ] Bàn biên tập đủ ngữ cảnh quyết trong một thẻ; người quyết Đăng/Duyệt/Gộp, máy không làm thay.
- [ ] Gutenberg không xuất hiện như editor dự phòng cho tác phẩm.
- [ ] Các điều kiện menu/ra mắt/đợt 5 và ngưỡng số liệu không bị bỏ qua.
- [ ] Có prototype đi được J01–J15, không chỉ ảnh đẹp.
- [ ] Có token, component, spec, ánh xạ WordPress và ma trận phủ thực tế.
- [ ] Không tuyên bố đã test/đạt hiệu năng/được chủ dự án duyệt nếu chưa có bằng chứng.

**Bắt đầu dựng ngay. Ưu tiên để tôi nhìn thấy sớm tám màn đại diện, rồi hoàn thành toàn bộ bộ UX/UI trong cùng hệ thống. Mục tiêu cuối cùng là một thiết kế có thể duyệt, sửa bố cục và bàn giao cho dev trước khi code theme.**

# KẾT THÚC PROMPT

---

## Ghi chú rà soát đi kèm — không cần dán phần này nếu chỉ giao prompt

### Các điểm đã đối chiếu

| Vấn đề dễ làm sai khi giao thiết kế | Kết luận theo bản chốt |
|---|---|
| docs/20 và docs/25 còn chứa màn hình/hành vi cũ | Chỉ kế thừa phần còn hiệu lực; docs/27 và D-087 quyết định phần người đọc |
| Các bản nghiên cứu còn đề xuất nâng cấp tại chỗ | D-083 chốt viết mới trên WordPress, song song trong `wordpress/v2/`, chuyển một lần |
| Câu đố từng có đáp án hiện sẵn | D-087 đổi sang Hiện đáp án bằng details ở cả trang bài và Đố vui |
| Tục ngữ và thành ngữ từng được tách | D-079 gộp thành một mục Tục ngữ – Thành ngữ |
| Tình huống là hệ trang độc lập trong MVP | V2 gộp vào tuyển tập loại Tình huống |
| Nguồn được ẩn/hiện tuỳ ý bởi người thiết kế | D-085 chốt chính sách ở hồ sơ nguồn; link ẩn không có trong HTML gửi khách |
| Bài có phân tích được coi là đủ giá trị để lên Google theo tài liệu cũ | D-084 chốt bài phân tích không làm tác phẩm lên L2; nghĩa đã duyệt mới là điều kiện tương ứng |
| Bàn biên tập dùng lại màn Gutenberg và menu cũ | D-086 thay hẳn editor tác phẩm, một Hộp việc, không tự tạo bài Chờ duyệt hàng loạt từ kho |
| “Toàn bộ V2” bị hiểu là mọi tính năng lên menu từ ngày đầu | D-091 chia đợt; lớp học, giọng sinh sẵn, truyện, thể loại mới, sách quét thuộc đợt 5 |
| “18 màn hình” bị hiểu là 18 ảnh duy nhất | Đây là nhóm nghiệp vụ; phải tách trang danh sách/chi tiết, panel, trạng thái, quản trị và responsive |
| Duyệt thiết kế sớm bị biến thành thêm bộ cổng PRD nhiều chữ ký | Yêu cầu phiên này là tạo kết quả trực quan trước theme; không tự đổi mô hình vận hành một người |

### Phạm vi kiểm tra thực tế của phiên soạn brief

- Đã lấy và đọc các tài liệu liên quan từ repo ở commit ghim phía trên; đọc hướng dẫn dự án, quyết định V2, đặc tả người đọc, kỹ thuật, Bàn biên tập, chuyển đổi, backlog và nghiên cứu liên quan.
- Đã đọc HTML trang chủ thật ngày 27/10/2026. Phần tên trang đã là “Thư viện Văn học Dân gian Việt Nam”, trong khi nội dung đầu trang và chân trang vẫn có tên cũ. Đây là quan sát tại thời điểm kiểm tra, không phải yêu cầu giữ lại.
- Đã xem ảnh tham chiếu E2E trong repo. Ảnh đó thuộc báo cáo 26/10; bảng cập nhật của báo cáo ghi một số lỗi đã sửa ngày 27/10. Không dùng ảnh lịch sử để kết luận toàn bộ lỗi còn tồn tại trên site thật.
- Không thực hiện một cuộc thử tương tác E2E mới trên site thật trong phiên này; các hành trình trong brief là yêu cầu thiết kế và kiểm thử, không phải kết quả thử người dùng đã hoàn tất.
- Không sửa mã, tài liệu, trạng thái task, chính sách nguồn hoặc cấu hình site/repo. Đây là brief bàn giao UX/UI, chưa phải bộ thiết kế được chủ dự án phê duyệt.

### Bảng nối yêu cầu với nguồn chính

| Phần trong prompt | Nguồn chính |
|---|---|
| Định vị/người dùng/phạm vi | docs/00 mục 2–7; D-082, D-091 |
| Art direction, token, responsive | docs/27 mục 9; docs/20 mục 1, 11; D-087 |
| Điều hướng và route | docs/27 mục 2–3 |
| Trang chủ/danh sách | docs/27 mục 4–5 |
| Năm mẫu đọc và tư liệu | docs/27 mục 6; D-085, D-087 |
| Tuyển tập/tìm/tra cứu/trò chơi | docs/27 mục 7; docs/26 mục 8 |
| Đóng góp/đã lưu/trang tĩnh/lỗi | docs/27 mục 8; docs/25 phần còn hiệu lực |
| Công cụ đọc/trích dẫn | docs/27 mục 6, 9; docs/20, docs/25 phần kế thừa không mâu thuẫn V2 |
| Bàn biên tập/quyền/nguồn | docs/28; docs/26 mục 4–7; D-084–D-086 |
| Bảo trì/Cần xem | docs/29 mục 3–5 |
| Đợt 5 | docs/22 T-158–164; docs/27 mục 6.6, 7.9; D-091 |
| Bộ prototype/ma trận phủ/duyệt trước theme | Yêu cầu của chủ dự án trong phiên 27/10/2026; chi tiết tổ chức là đề xuất bàn giao |

