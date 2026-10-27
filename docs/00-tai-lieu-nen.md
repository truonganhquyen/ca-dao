# 00 · Tài liệu nền — V2

| | |
|---|---|
| Phiên bản | 3.0 · 27/10/2026 (đại tu V2: D-082..D-088) |
| Trạng thái | **Ổn định.** Đổi nội dung phải ghi vào `01-nhat-ky-quyet-dinh.md` |
| Đọc khi | Trước mọi việc khác. Người mới và AI agent đọc tài liệu này đầu tiên |
| Thay cho | Bản 2.0 (MVP, 23/10/2026) — lưu ở `docs/luu-tru/00-tai-lieu-nen-mvp.md` |
| Căn cứ | Bản đề xuất *Phongdao Next Generation v1.0* (27/10/2026); `docs/nghien-cuu/v2-*-2026-10-27.md`; khảo sát đối thủ 27/10; báo cáo E2E 26/10 |

Tài liệu này trả lời: làm gì, cho ai, theo nguyên tắc nào, trong phạm vi nào, đo bằng gì. Chi tiết "làm thế nào": kiến trúc ở `26`, giao diện ở `27`, đặc tả từng đợt ở `28` trở đi, task ở `22`.

---

## 1. Vấn đề

Người tìm văn học dân gian trên mạng gặp sáu vấn đề lặp lại:

1. **Rác SEO và quảng cáo.** Trang mỏng, popup, quảng cáo chèn giữa lời; có site còn chèn liên kết cá cược ở chân trang (khảo sát 27/10).
2. **Chép lại nhau, sai chữ, không nguồn.** Không ai biết bản nào đáng tin.
3. **Xoá bỏ dị bản.** Một bản được đưa ra như "bản đúng duy nhất".
4. **Giải nghĩa nông hoặc bịa.** Từ cổ, phương ngữ, điển tích hầu như không được chú giải; sự tích được kể chắc chắn mà không nguồn.
5. **Dữ liệu khô.** Không bối cảnh, không liên kết giữa các bài.
6. **Không tìm được theo nhu cầu thật.** Người đọc biết mình cần gì ("ca dao về mẹ", "nghĩa là gì", "trò chơi cho bé 5 tuổi") nhưng kho chỉ là danh sách tên bài.

## 2. Định vị

- **Tên:** **Thư viện Văn học Dân gian Việt Nam** — phongdao.com (D-082; trước đây "Kho tàng Ca dao Tục ngữ Việt Nam").
- **Định vị:** nơi đọc, hiểu, trích dẫn và chia sẻ văn học dân gian Việt Nam **dễ chịu nhất trên điện thoại** và **trung thực nhất về nguồn gốc**. Phi lợi nhuận, không quảng cáo.
- **Câu định vị công khai:** "Đọc một câu. Hiểu một đời sống."
- **Lời hứa (D-039):** **"Mỗi câu ghi rõ lấy từ đâu, và đã được kiểm đến mức nào."**
- **Ba trụ sản phẩm** (trọng tài: mỗi tính năng phục vụ ít nhất một trụ):

| TIN | HIỂU | CẢM |
|---|---|---|
| Dòng "lời và mức kiểm" dưới lời; mỗi lớp nội dung có hồ sơ nguồn và người làm; không sửa âm thầm; lịch sử sửa | Trả lời trước (nghĩa trong 10 giây), chú thích chạm-là-hiện, nghĩa đen / bóng / dùng khi, dị bản, bối cảnh | Chữ đẹp, trang nhẹ, tuyển tập đọc liền, trò chơi, đố vui, trình chiếu, giọng đọc khi đủ chất lượng |

- **Đơn vị nhỏ, tập hợp lớn.** Mỗi câu, mỗi bài một trang riêng, trích dẫn được. Mọi trang tập hợp (tuyển tập, chủ đề, vùng, lớp học, kết quả tìm) ghép từ trang đơn — nghĩa viết một lần, hiện ở mọi nơi. Đây là khác biệt với các trang "bài tổng hợp" viết tay (vanvn.net, trang văn mẫu).
- **Quan hệ với Ca dao Mẹ (cadao.me):** nguồn đầu mối chính (D-046); dự án không cạnh tranh về số lượng; luôn ghi nhận ở trang Chính sách thu thập dữ liệu; thư xin phép dùng chú thích nên gửi trước khi mở (T-034).
- **Lợi thế bền vững:** bộ dữ liệu có nguồn gốc rõ từng lớp, được nâng mức kiểm dần, cùng trải nghiệm đọc tốt. Website là một giao diện trên bộ dữ liệu đó.

## 3. Người dùng

**Phát hiện then chốt:** mọi nhóm đều sợ sai — giáo viên sợ dạy sai, người trẻ sợ chia sẻ sai, phụ huynh sợ kể sai cho con. Vì vậy mức kiểm và nguồn đứng ngay chỗ khẳng định.

| Người dùng | Mô tả | Thành công nghĩa là |
|---|---|---|
| **Cô Hạnh** (chính) | Giáo viên Ngữ văn THCS; soạn bài buổi tối trên điện thoại | Dưới 3 phút có bộ 5 câu theo bài học, trình chiếu được, có dòng trích dẫn |
| **Minh** (chính) | 24 tuổi, xa quê, chia sẻ dịp Tết, Vu Lan; gõ không dấu | Dưới 30 giây tìm được câu ưng ý và chia sẻ kèm nguồn |
| **Học sinh** | Cần nghĩa câu tục ngữ, từ cổ để làm bài | Thấy nghĩa trong 10 giây từ trang chủ hoặc kết quả tìm |
| **Phụ huynh** | Muốn đọc, chơi cùng con | Chọn được trò chơi đồng dao theo tuổi, số người trong 1 phút |
| **Người yêu văn hoá** | Muốn lần theo dị bản, vùng, địa danh | Đi từ bài sang bối cảnh, bản đồ, bài khác có căn cứ |
| **Người có sách, người ghi hộ** (đóng góp) | Có SGK, tuyển tập; nhớ lời ông bà | Gửi số trang trong 2 phút; gửi một câu kèm người kể, nơi, sự đồng ý trong 5 phút |
| **Người sáng lập** (người biên soạn duy nhất) | Dưới 10 giờ/tuần, không đọc mã | Duyệt một nháp trong ≤ 60 giây trên điện thoại; mọi việc chờ ở một nơi; biết tuần này nên làm gì |

## 4. Nguyên tắc trọng tài

1. **Trung thực hơn hoàn hảo (D-039).** Xuất bản sớm, nói thật mức độ hoàn thiện; không chặn đăng vì lý do biên tập (D-054).
2. **Mỗi lớp nội dung ghi rõ ai làm và theo nguồn nào.** Không biết thì để trống và nói "chưa rõ"; không đoán.
3. **Không có bản đúng duy nhất.** Lời chính là **Bản tham chiếu**; dị bản lưu riêng, ghi nơi, người kể khi có căn cứ.
4. **Lời giữ nguyên như nguồn.** Chuẩn hoá chỉ diễn ra trong chỉ mục tìm. Không sửa âm thầm; mọi thay đổi vào nhật ký.
5. **Máy làm nặng, người quyết nhanh.** Máy thu thập, tách lớp, chống trùng, gợi ý, soạn nháp; người biên soạn quyết. AI không bao giờ là nguồn.
6. **Trả lời trước, mở sâu sau.** Màn hình đầu trả lời câu người đọc đang hỏi; phần sâu thu gọn.
7. **Điện thoại trước; nhẹ là tính năng.** Trang bài ≤ 200 KB, JavaScript trang bài ≤ 21 KB, tắt JavaScript vẫn đọc được, 0 lỗi WCAG 2.2 AA.
8. **Không quảng cáo, không theo dõi, không liên kết rác.** Không cookie đo lường, không thu IP, không đăng nhập cho người đọc.
9. **Dữ liệu mở, có phiên bản, trích dẫn được** — chỉ phần dự án viết hoặc nguồn có giấy phép mở.
10. **Mọi tính năng mới qua hai cửa:** (a) có giúp tìm / hiểu / tin / bảo tồn tốt hơn không, có thêm gánh duyệt không; (b) một người, khoảng 5 giờ/tuần, duy trì được 2 năm không.

**Ba lằn ranh (D-040, nới bởi D-054)** — máy chặn, không ngoại lệ:
1. AI không bao giờ viết lời, dị bản, nguồn, số trang, vùng, địa danh, tác giả, niên đại. Lớp lời và dị bản không nhận người làm là AI.
2. Nguồn ghi đúng sự thật, không bịa; nhãn mức kiểm do máy tính từ dữ liệu, không nhập tay; máy không tự đặt "đã đối chiếu", "đã duyệt", "có hỗ trợ AI", không tự đăng.
3. Dữ liệu cá nhân và ghi âm phải có đồng ý.

## 5. Phạm vi

| Nhóm | Khi nào | Ghi chú |
|---|---|---|
| Ca dao – dân ca, Tục ngữ – Thành ngữ, Đồng dao (+ trò chơi), Vè, Câu đố | Đang có; mẫu trang riêng ở V2.2 | Năm thể loại lõi |
| Hát ru, hò, lý (dạng văn bản) | V2.3, qua cổng thể loại | Giọng máy không thay diễn xướng (D-063) |
| Truyện dân gian Việt Nam | V2.3, qua cổng thể loại | Chỉ từ sách hết bản quyền có bản quét mà người sáng lập đã kiểm quyền; không chép bản kể hiện đại |
| Sử thi, thần thoại chuyên sâu; văn học dân gian các dân tộc thiểu số | Ngoài V2 | Cần chuyên gia và đồng thuận của cộng đồng |
| Thơ hiện đại, truyện nước ngoài, văn mẫu, "ca dao chế" | Ngoài phạm vi | Câu hay bị nhầm chỉ xuất hiện khi gắn cờ "Nghi không phải dân gian" |

**Cổng mở một thể loại mới (D-088):** có mẫu trang đọc riêng qua quét trợ năng; ≥ 20 bài đã đăng, trong đó ≥ 10 bài ở cấp L2; lời có hồ sơ nguồn và quyền đã ghi. Thể loại chưa qua cổng không lên menu.

### Những thứ không làm

| Không làm | Lý do | Xem xét lại khi |
|---|---|---|
| Tài khoản, đăng nhập cho người đọc | Bảo mật, dữ liệu cá nhân | Có ≥ 3 người vận hành |
| Bình luận mở, lượt thích, mạng xã hội | Kiểm duyệt nuốt thời gian | Không bao giờ ở dạng mở (cảm nghĩ, phân tích vẫn qua Chờ duyệt) |
| Chatbot; AI sinh nội dung công khai khi người đọc mở trang | Một lỗi ảo giác phá niềm tin | Không bao giờ |
| App iOS/Android | Web tốt trên điện thoại là đủ | Có nhu cầu ngoại tuyến rõ (T-065) |
| Xuất bản hàng loạt từ kho | Thành trang sao chép (D-045) | Không bao giờ |
| Headless, Next.js, Meilisearch, Elasticsearch | Không có bằng chứng cần | Có log và đo thấy nghẽn thật |
| Điểm thưởng, bảng xếp hạng, chấm điểm trẻ | Lệch trọng tâm | Có đối tác giáo dục cần |
| Tra năm sinh, phong thuỷ, meme để kéo lượt xem | Lạc định vị | Không bao giờ |
| Quảng cáo, liên kết tiếp thị, khối liên kết chân trang | Mâu thuẫn với lý do tồn tại | Không bao giờ |

Đã đổi so với bản MVP: **giọng đọc máy** được làm, luôn ghi "Giọng đọc máy" (D-063); **bản đồ** đã có, không gọi dịch vụ ngoài (T-104); **công cụ quản trị riêng** nay là Bàn biên tập V2 nằm trong plugin WordPress (D-085, D-086).

## 6. V2 là gì

| Trục | MVP | V2 |
|---|---|---|
| **Luồng nội dung** | Một bài = một khối HTML; nguồn rải 6 chỗ; Hiểu nhanh = ô Tóm tắt | **Tác phẩm nhiều lớp** (D-083): lời, dị bản, nghĩa, chú thích, bối cảnh, thực hành, media là lớp có cấu trúc; mỗi mục có hồ sơ nguồn, người làm, trạng thái, lịch sử. Chính sách hiện nguồn là **cài đặt** (D-084) |
| **Giao diện** | Một mẫu trang bài; phần riêng chèn bằng PHP; trang chủ là mã ngắn | **Trang đọc theo thể loại** dựng từ khối `cadao/*`; **lối vào theo nhu cầu** (Đọc · Khám phá · Cho lớp học · Tra cứu); tuyển tập đọc liền; Thông tin tư liệu (D-087) |
| **Quản trị** | 13 trang con, mỗi trang một kiểu | **Một Bàn biên tập** (D-086): Hộp việc, Duyệt nhanh, trình soạn có cấu trúc, kế hoạch tuần |

```text
Nguồn (Ca dao Mẹ · Wikisource · người đọc · nhập tay)
  → Kho thu thập (riêng tư, không lên trang)
  → Máy: tách lớp · chống trùng · gợi ý phân loại · tìm trang sách quét khớp lời
  → Agent: nháp nghĩa theo lô (ai_nhap, chờ duyệt)
  → Hộp việc: mỗi thẻ một quyết định → người sáng lập Đăng · Sửa · Để sau
  → Trang đọc dựng từ lớp đã duyệt
  → Hậu kiểm: góp ý, báo lỗi, tìm không ra → quay lại Hộp việc
```

**Cấp chất lượng (nội bộ, máy tính, không hiện cho người đọc):** L0 có lời · L1 có thể loại và dòng mức kiểm · L2 có Hiểu nhanh, khối nghĩa đã duyệt hoặc bài phân tích đã duyệt · L3 có thêm đối chiếu sách, bối cảnh hoặc media có quyền. Dùng để xếp việc, chọn bài nổi bật và quyết lập chỉ mục (L2 trở lên). Không chặn đăng.

## 7. Lộ trình

Giả định khoảng 5 giờ/tuần của người sáng lập; mọi phần mã do agent làm. Mốc ngày là ước tính, không phải cam kết công khai.

| Đường | Kết quả | Ước tính |
|---|---|---|
| **Mốc C · Mở lặng lẽ** | Sao lưu hằng ngày + thử khôi phục; người sáng lập quyết mở (H5). Nên có: ~50 câu có Hiểu nhanh, thư đã gửi Ca dao Mẹ | Trước Tết Đinh Mùi 2027 |
| **V2.0 · Nền** | Mô hình lớp, hồ sơ nguồn, nhật ký, chuyển dữ liệu, chính sách hiện nguồn thành cài đặt. Giao diện người đọc **không đổi** | Song song, bắt đầu ngay |
| **V2.1 · Bàn biên tập** | Hộp việc, Duyệt nhanh, trình soạn có cấu trúc, nghĩa tục ngữ, cờ nghi, đối chiếu sách | Sau V2.0 |
| **V2.2 · Trang đọc** | Khối theo lớp, theme 1.0, mẫu 5 thể loại, trang chủ lối vào, tuyển tập, Thông tin tư liệu, Đố vui | Sau V2.1 |
| **V2.3 · Mở rộng** | Thể loại mới qua cổng, giọng đọc sinh sẵn, Cho lớp học, dữ liệu mở | Theo số liệu sau khi mở |

Mỗi đợt V2 có một đặc tả ngắn được người sáng lập duyệt trước khi làm (`docs/28` cho V2.0). Mỗi đợt để lại site chạy được; không đổi đường dẫn.

## 8. Chỉ số

**North Star — Lượt dùng tin cậy mỗi tuần** = lượt sao chép trích dẫn + lượt chia sẻ.

| Nhóm | Chỉ số | Đích sau V2.1 + 12 tuần |
|---|---|---|
| Thông lượng | Thời gian duyệt một nháp Hiểu nhanh trên điện thoại | ≤ 60 giây |
| Nội dung | Bài ở cấp L2 | ≥ 300 |
| Nội dung | Thể loại có bài đã đăng và qua cổng | 5 |
| Niềm tin | Mục lớp có hồ sơ nguồn hoặc ghi rõ "chưa rõ" | 100% |
| Niềm tin | Lời đã đối chiếu sách | ≥ 50 |
| Đọc | Trang bài | ≤ 200 KB, 0 lỗi axe |
| Quản trị | Số nơi phải vào để quyết việc | 1 |
| Người đọc | Câu "tìm không ra" có trong kho đã thành bài | ≥ 50% mỗi tháng |

**Không tối ưu:** lượt xem trang, thời gian ở lại, tổng số bài, số trang được lập chỉ mục.

## 9. Rủi ro chính

| Rủi ro | Mức | Giảm thiểu |
|---|---|---|
| Chuyển dữ liệu làm hỏng bài | Cao | Chỉ chạy sau sao lưu + thử khôi phục; chạy lại được; so từng ký tự lời; hàng "Cần xem" thay vì đoán; cờ tính năng |
| V2 kéo dài, MVP đứng yên | Cao | Mở không chờ V2; Hiểu nhanh chạy tiếp trên công cụ hiện có; mỗi đợt để lại site chạy được |
| Người sáng lập kiệt sức | Cao | Duyệt nhanh trên điện thoại; máy xếp việc; lô 5–10 câu/tuần |
| Ca dao Mẹ phản đối việc chép chú thích | TB | Gửi thư sớm (T-034); công tắc "Chép chú thích" tắt được ngay; chính sách hiện là cài đặt |
| Duyệt qua loa vì quá dễ | TB | Thẻ luôn kèm chú thích, nguồn; nhãn AI rõ; báo lỗi đứng đầu hộp |
| Mở thể loại làm loãng chất lượng | TB | Cổng thể loại; L2 làm thước đo |
| Bản dựng React quên cập nhật | TB | CI so bản dựng với mã nguồn |
| Đổi tên làm lệch trích dẫn cũ | Thấp | Tên miền không đổi; trích dẫn mới mang tên mới, đường dẫn giữ nguyên |

## 10. Bản đồ tài liệu

| Số | Tài liệu | Cần khi |
|---|---|---|
| 01 | Nhật ký quyết định + câu hỏi còn mở | Luôn |
| 02–03 | Trạng thái hiện tại, quy trình thực thi | Mỗi phiên làm việc |
| 12 | Chính sách nguồn | Mọi việc chạm nguồn, bản quyền, ghi công |
| 20 | Đặc tả giao diện đang chạy (token màu, phông vẫn dùng ở V2) | Sửa giao diện MVP |
| 22 | Backlog, mốc | Nhận việc |
| 24 | WordPress hiện tại (plugin, theme, máy chủ, kiểm thử) | Sửa mã MVP |
| 25 | Gói nâng cấp 25/10/2026 | Task trỏ tới |
| **26** | **Kiến trúc kỹ thuật V2** | Mọi task V2 |
| **27** | **Đặc tả giao diện V2** | Task V2.2 trở đi, mọi thay đổi giao diện mới |
| **28** | **Đặc tả đợt V2.0** | Task V2.0 |
| `nghien-cuu/` | Khảo sát, kế hoạch, nghiên cứu V2 (căn cứ) | Khi cần lý do |
| `huong-dan/` | Hướng dẫn cho người biên soạn | Viết hướng dẫn |
| `luu-tru/` | Tài liệu không còn dùng hằng ngày (bản MVP của tài liệu này, v1.2) | Khi task trỏ tới |
