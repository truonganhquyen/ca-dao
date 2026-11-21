# 49 · Quyết định cần duyệt và câu hỏi mở — V2 Core và Next

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Lịch sử (21/11/2026)** — định hướng, kiến trúc, lộ trình đã thay bởi `docs/50`–`57`; kế hoạch đang dùng: [57](57-ke-hoach-chot.md). Nguyên trạng lúc lập: **Chờ người sáng lập trả lời.** Mỗi QĐ được duyệt thành một dòng D-xxx ở `docs/01` (agent ghi ở giai đoạn 0 — [47](47-ke-hoach-tong-core-next.md) mục 6). Q-008 đang mở ở `docs/01` không đổi |
| Đọc khi | Người sáng lập duyệt kế hoạch; agent cần biết điều gì đã hoặc chưa được quyết |
| Bộ tài liệu | [45 kiểm kê, đối chiếu](45-kiem-ke-va-doi-chieu.md) · [46 kiến trúc, khuyến nghị](46-kien-truc-va-khuyen-nghi.md) · [47 kế hoạch tổng](47-ke-hoach-tong-core-next.md) · [48 lộ trình, backlog](48-lo-trinh-va-backlog.md) · **49 quyết định, câu hỏi mở (tệp này)** |

---

## 1. Tóm tắt để trả lời nhanh

Cách trả lời gọn nhất: **"Đồng ý các khuyến nghị"**, hoặc ghi số QĐ muốn chọn khác (vd. "QĐ-5 chọn B"). Chỉ cần QĐ-1 đến QĐ-7 để bắt đầu V2 Core; QĐ-9 đến QĐ-11 dùng cho NEXT, chưa gấp.

| QĐ | Câu hỏi | Khuyến nghị | Gate | Cần cho |
|---|---|---|---|---|
| QĐ-1 | Có thêm loại bài, bảng mới theo đặc tả nội dung không? | **Không** — dùng mô hình đang có | H1 | Mọi việc |
| QĐ-2 | Mục từ có chia loại Từ ngữ · Địa danh · Nhân vật · Điển tích không? | **Có, bốn loại**, người chọn | H1, H4 nhỏ | Giai đoạn 2 |
| QĐ-3 | Bài viết có nối với tác phẩm không? | **Có**, chỉ hiện bài đã có quyền | H1, H4 | Giai đoạn 2 |
| QĐ-4 | Ghi chú chung không gắn từ có tách khỏi "Chuyện quanh bài" không? | **Có**, thành hàng "Ghi chú" | H4 nhỏ | Giai đoạn 1 |
| QĐ-5 | Vè: Ý nghĩa vẫn đứng trước lời? | **Giữ như hiện nay**, rút gọn Ý nghĩa dài | H4 | — |
| QĐ-6 | Đố vui lấy câu nào? | **Chỉ câu đố đã có giải thích do người viết hoặc đã duyệt** | — | Giai đoạn 3 |
| QĐ-7 | Chạy vòng nháp Ý nghĩa hằng tuần? | **Có, 30 bài mỗi lô** | H2 | Giai đoạn 1 |
| QĐ-8 | Thêm "Bài viết" vào menu chính? | **Chưa** — khi có từ 10 bài đã có quyền và đã nối | H4 | Giai đoạn 2 |
| QĐ-9 | Khi nào mở thử sản phẩm NEXT? | Khi có **≥ 300 bài có phần riêng**, mỗi thể loại ≥ 30, đã quyết hướng tài chính, đã gửi thư Ca dao Mẹ | — | NEXT |
| QĐ-10 | Dữ liệu nào được đưa ra ngoài trang đọc? | **Chỉ lời, phần thư viện viết, phần bạn đọc góp đã duyệt, phần nguồn mở có ghi công** | H3 | NEXT |
| QĐ-11 | Thử sản phẩm NEXT theo thứ tự nào? | **Dữ liệu mở → "Bộ của tôi" → khung nhúng → đối tác → API → ghi âm** | H4 | NEXT |
| QĐ-12 | Ghi lại danh sách việc không làm để agent không tự mở lại? | **Có** | — | Mọi việc |

## 2. Chi tiết từng quyết định

### QĐ-1 · Giữ mô hình dữ liệu V2 — H1
- **A (khuyến nghị):** không thêm loại bài hay bảng theo đặc tả nội dung; mọi nhu cầu ánh xạ vào thực thể đang có ([46](46-kien-truc-va-khuyen-nghi.md) mục 2.1); chỉ thêm hai trường nếu QĐ-2, QĐ-3 được duyệt.
- **B:** làm theo đặc tả mục 6.1 — loại bài `editorial_article`, `knowledge_entry`, `source_record`, bảng `work_version`, `content_relation`.
- **Vì sao A:** mỗi thực thể đề xuất đã có chỗ lưu tương đương ([45](45-kiem-ke-va-doi-chieu.md) mục 3.4); D-110, D-114 đã hoãn mở rộng nền nội dung vì chi phí; một người vận hành không giữ nổi hai mô hình song song.
- **Nếu chọn B:** mở lại D-110, D-114; nhiều tuần mã, di chuyển dữ liệu, màn sửa mới.

### QĐ-2 · Loại mục từ — H1, H4 nhỏ
- **A (khuyến nghị):** bốn loại Từ ngữ · Địa danh · Nhân vật · Điển tích; người chọn ở tấm sửa mục từ; máy chỉ gợi ý "Địa danh" cho mục có toạ độ Wikidata đã xác nhận; Từ điển lọc theo loại; trang tác phẩm có dòng "Trong bài có: …"; Đọc tiếp xếp "cùng điển tích" ngay sau "cùng tuyển tập".
- **B:** ba loại như `docs/26` đã ghi (không có Điển tích).
- **C:** không làm; sửa `docs/26`, `docs/27` cho khớp mã.
- **Việc của người sáng lập:** chọn loại khi gặp mục từ, không phải làm hết kho; mục chưa chọn vẫn hiện như Từ ngữ.

### QĐ-3 · Bài viết nối tác phẩm — H1, H4
- **A (khuyến nghị):** dùng 46 bài viết đang có; hộp "Tác phẩm được bàn tới" ở màn sửa bài viết; trang tác phẩm có hàng "Bài viết về bài này" (tối đa 3) **chỉ với bài viết đã tích "Đã có quyền đăng"**; ba nhóm bài viết; mẫu trang bài viết có Người viết, Biên tập, ngày sửa, các tác phẩm trong bài.
- **B:** tạo loại bài riêng cho bài nghiên cứu.
- **C:** để sau.
- **Câu phụ cần trả lời nếu chọn A:** (1) tên ba nhóm — đề xuất **Khảo cứu · Bình giảng · Chuyên đề** ("Cảm nhận" giữ cho bài của bạn đọc); (2) dòng "Người viết" có hiện tên tác giả bài viết (thường là người ngoài dự án) không — đề xuất: có, khi bạn đã có quyền đăng và tự gõ tên.

### QĐ-4 · Hàng "Ghi chú" — H4 nhỏ
- **A (khuyến nghị):** chú thích chung không gắn vào chữ nào trong lời hiện ở hàng mở "Ghi chú"; "Chuyện quanh bài" chỉ còn giai thoại, điển tích thật; tục ngữ giữ "Nguồn gốc".
- **B:** giữ như hiện nay (chú thích chung nằm ở "Chuyện quanh bài").
- **Vì sao A:** hiện một ghi chú về cách chép khác của một chữ đang hiện dưới tên "Chuyện quanh bài" (trang "Thò tay mà ngắt cọng ngò"), tên hàng hứa điều khác với nội dung.

### QĐ-5 · Vè: Ý nghĩa trước lời — H4
- **A (khuyến nghị):** giữ như hiện nay (Bối cảnh và Ý nghĩa của vè đứng trước lời dài); Ý nghĩa của vè nên 2–3 câu; bài dài hơn dùng danh sách "Ý nghĩa dài" ở Bổ sung để rút gọn.
- **B:** đưa Ý nghĩa xuống sau lời, giữ Bối cảnh trước lời.
- **Vì sao A:** lời vè rất dài trên điện thoại; người đọc cần biết bài kể gì trước khi đọc 50 dòng; chỗ chưa ổn là Ý nghĩa dài, không phải vị trí.

### QĐ-6 · Đố vui lấy câu nào
- **A (khuyến nghị):** chỉ câu đố đã đăng có "Giải thích đáp án" do người viết hoặc AI đã được duyệt, chưa bật "Có từ ngữ thô tục", không có cờ; trang tự ẩn khi dưới 10 câu; không cho Google lập chỉ mục trang Đố vui (từng câu đố vẫn có trang riêng).
- **B:** mọi câu đố đã đăng trừ câu đã bật "Có từ ngữ thô tục".
- **Vì sao A:** công tắc thô tục chưa được bật cho câu đố nào; trong 765 câu có những câu tục giảng thanh; trang này dành cho phụ huynh và trẻ.

### QĐ-7 · Vòng nháp Ý nghĩa hằng tuần — H2
- **A (khuyến nghị):** mỗi tuần một lô 30 bài (đọc nhiều, tìm nhiều trước; câu đố là Giải thích đáp án); agent soạn nháp theo Sổ tay biên tập ở ngoài kho mã; bạn nạp tệp — việc nạp chính là đồng ý cho lần đó — rồi duyệt hoặc bỏ từng thẻ; tuần nào bận thì bỏ qua.
- **B:** lô 50 (mức trần hiện có).
- **C:** chưa chạy.
- **Vì sao A:** đây là việc tăng nội dung lên Google nhanh nhất mà đường đã làm sẵn; 30 thẻ × ≤ 60 giây vừa một buổi.

### QĐ-8 · Menu "Bài viết" — H4
- **A (khuyến nghị):** chưa thêm vào menu chính; trang Khám phá có mục "Bài viết" khi có từ 3 bài đã có quyền và đã nối; menu chính khi có từ 10 bài.
- **B:** thêm ngay.

### QĐ-9 · Ngưỡng mở thử NEXT
- **A (khuyến nghị):** ≥ 300 bài có phần riêng (đích sẵn có ở `docs/00` mục 8), mỗi thể loại ≥ 30; đã quyết hướng tài chính, pháp nhân; đã gửi thư cho Ca dao Mẹ hoặc quy tắc loại trừ chú thích chép (QĐ-10) đã chạy.
- **B:** 150 tác phẩm mẫu như tài liệu NEXT đề xuất.

### QĐ-10 · Quy tắc dùng lại dữ liệu ngoài trang đọc — H3
- **A (khuyến nghị):** mọi sản phẩm ngoài trang đọc (dữ liệu mở, phiếu in, khung nhúng, API, tuyến di sản) chỉ được dùng lời và dị bản (kèm đường dẫn bài), phần thư viện viết, phần bạn đọc góp đã duyệt, phần chép từ nguồn mở có ghi công; **không** dùng chú thích chép từ nguồn chưa đồng ý (hiện là Ca dao Mẹ). Máy tính từ dữ liệu người làm và hồ sơ nguồn đang có; không có ô đánh dấu tay.
- **Câu phụ:** bản dữ liệu mở đầu tiên chứa lời của **mọi** bài đã đăng hay **chỉ bài có phần riêng**? Đề xuất: chỉ bài có phần riêng — phần lớn lời được lấy về từ Ca dao Mẹ; xuất hàng loạt cả kho dễ bị xem là chép lại bộ sưu tập của họ; nên hỏi ý trong thư.

### QĐ-11 · Thứ tự thử NEXT — H4
- **A (khuyến nghị):** dữ liệu mở → "Bộ của tôi" từ Đã lưu (phiếu in, ảnh, trình chiếu; không tài khoản) → khung nhúng "Câu hôm nay", "Đố một câu" → tuyến di sản, triển lãm với đối tác → API cho đối tác → ghi âm cộng đồng.
- **B:** theo tài liệu NEXT — Nhúng và Creator Studio trước, tuyến di sản khi có đối tác.
- **Vì sao A:** ba bước đầu không cần tài khoản, đối tác hay số đo mới; Creator Studio có tài khoản tổ chức trái nguyên tắc "không tài khoản người đọc" và cần quyết hướng tài chính.

### QĐ-12 · Ghi nhận những việc không làm
- **A (khuyến nghị):** ghi một D-xxx liệt kê: bảng quan hệ chung và các loại bài mới của đặc tả nội dung; trạng thái quyền ở cấp tác phẩm; chip thể loại riêng cho thành ngữ; nhãn "chuyên gia thẩm định"; trợ lý nghiên cứu, chatbot AI; tài khoản, thuê bao, watermark; AR, 3D, ứng dụng điện thoại; microsite theo đơn; cuộc thi sáng tạo; giọng AI giả giọng vùng; điểm thưởng, bảng xếp hạng. Mỗi mục kèm điều kiện xem lại (nếu có) như [46](46-kien-truc-va-khuyen-nghi.md) KT-7.
- **Vì sao:** để agent và người review sau không tự mở lại khi đọc lại hai tài liệu đề xuất.

## 3. Bảy trang cần người sáng lập xem (việc C1.5)

Máy và agent không sửa các trang này; chỉ người biên soạn quyết (H2).

| # | Trang | Việc cần quyết | Gợi ý |
|---|---|---|---|
| 1 | `/ca-dao/tho-tay-ma-ngat-cong-ngo/` | Một ghi chú về cách chép khác đang nằm ở "Chuyện quanh bài"; "Hình ảnh trong bài" lặp một phần Ý nghĩa | Ghi thành dị bản khi có đủ lời và nguồn, hoặc để hàng "Ghi chú" (QĐ-4) nhận; rút gọn Hình ảnh trong bài |
| 2 | `/ca-dao/o-day-co-nuoc-song-huong/` | Chú thích nói đoạn này từng được Nguyễn Bính đưa vào một bài thơ; chưa rõ ai vay của ai | Xét bật cờ "Có thể không phải dân gian"; nếu bật, ghi "Vì sao?" kèm nguồn |
| 3 | `/tuc-ngu/cau-doi-cu-phan-chu-nghe-dan/` | Tư liệu về nhân vật, câu đối nằm trong phần Nghĩa | Chuyển phần kể chuyện sang "Nguồn gốc"; nếu theo sách, ghi tên sách và trang |
| 4 | `/dong-dao/nhong-nhong-nhong-nhong/` | Chưa có cách đọc, cách chơi | Chỉ thêm khi có nguồn; không tự viết luật chơi |
| 5 | `/dong-dao/nha-toi-co-bay-anh-em/` | Bài tự gọi là "vè nói láo" | Có gắn một hình thức bổ sung (vd. "Nói láo") không; giữ thể loại đồng dao hay đổi |
| 6 | `/ve/ve-co-giang/` | Nhiều nhân vật lịch sử nằm trong chú thích | Khi có loại mục từ (QĐ-2): nối dần chú thích nhân vật vào mục từ loại Nhân vật |
| 7 | Tuyển tập "Top 10 câu tục ngữ nhân Ngày Nhà giáo Việt Nam 20/11" | Bộ đang có 11 câu | Đổi tên hoặc bỏ một câu |

Trang `/ve/ve-cau-doumer/` (Ý nghĩa đứng trước lời) đi theo QĐ-5: nếu giữ như hiện nay, chỉ cần rút gọn Ý nghĩa nếu dài quá 3 câu.

## 4. Câu hỏi mở (không chặn giai đoạn 1)

| # | Câu hỏi | Chặn việc | Ghi chú |
|---|---|---|---|
| Q-A | Trong 46 bài viết, bài nào đã có quyền đăng? | Hiển thị bài viết ở trang tác phẩm (C2.6) | Bạn tích dần ô "Đã có quyền đăng" ở màn sửa bài viết |
| Q-B | Khi nào gửi thư cho Ca dao Mẹ? Có hỏi thêm về dữ liệu mở, dùng lời trong sản phẩm khác không? | Mọi kênh NEXT | `docs/12` mục 7 đã có mẫu thư |
| Q-C | Hướng tài chính (Q-008 ở `docs/01`): chỉ phi lợi nhuận, nhận tài trợ có ghi nhãn, hay có dịch vụ thu phí qua một pháp nhân? | Sản phẩm có thu tiền | Q-008 giữ nguyên cho tới khi bạn quyết |
| Q-D | Có trường, thư viện, bảo tàng, điểm du lịch nào bạn quen sẵn sàng thử? | Tuyến di sản, triển lãm (N2.1) | Không có thì để Later |
| Q-E | Mỗi tuần dành được bao nhiêu giờ cho nội dung sâu (bài viết, điển tích, nhân vật)? | Nhịp giai đoạn 2 | Ước hiện tại: 1–2 giờ |
| Q-F | Ghi âm cộng đồng: muốn thử trong 6 tháng tới không? Có nhận giọng trẻ em không? | N3.1 | Cần hồ sơ đồng ý (lằn ranh 3) |
| Q-G | Ghi địa danh theo thời điểm: (1) giữ tên như nguồn, kèm "(theo địa giới năm …)"; hay (2) thêm "nay thuộc …" kèm năm cập nhật? | Sổ tay biên tập (C1.1) | Đề xuất (1): ít phải sửa lại khi địa giới đổi |

## 5. Giả định agent đã đặt (sửa nếu sai)

1. Hai tài liệu đề xuất là đầu vào để đánh giá, không lưu vào kho mã (kho công khai; có nhận định chưa kiểm).
2. Số liệu nội dung lấy từ D-117 (15/11), `docs/02` (19/11) và site thật đọc ngày 21/11; số bài có Ý nghĩa chưa đo lại.
3. Người sáng lập có khoảng 5 giờ mỗi tuần (D-035).
4. Không đổi đường dẫn, luật lập chỉ mục, bảng màu, phông, bố cục trang tác phẩm trong kế hoạch này.
5. "Bài viết" là 46 bài loại `post` trên site, phần lớn của tác giả khác (D-126).
6. Tài liệu trong kho mã không chép lời, chú thích hay dữ liệu kho từ site (AGENTS.md, KHÔNG BAO GIỜ 14); trang được nhắc bằng đường dẫn.
7. Ước lượng công sức là cho agent, không phải cam kết ngày.

## 6. Khi đã trả lời

Agent làm giai đoạn 0 ([48](48-lo-trinh-va-backlog.md), E0): ghi mỗi QĐ được duyệt thành một D-xxx ở `docs/01`; QĐ bị bác ghi lại ở mục 2 của tệp này là "không làm"; đưa việc vào `docs/22`; cập nhật `docs/02`, AGENTS.md; sửa tám chỗ lệch tài liệu – mã; mở một PR tài liệu, tự gộp khi kiểm tra đạt (D-066). Sau đó mới bắt đầu giai đoạn 1.
