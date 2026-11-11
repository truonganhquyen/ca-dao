# Trinh sát quản trị lần 2 (sau đợt 2)

Ngày 11/11/2026, plugin 1.0.42. Đóng vai người biên tập và quản trị, đi lần lượt từng việc thường làm trên máy tính (1280 px) và điện thoại (390 px). Mục đích: tìm chỗ còn vướng trước khi chốt phạm vi đợt 3.

## 1. Cách làm và giới hạn

- **Nơi thử:** bản chạy thử tại máy (WordPress Playground), cùng mã đang chạy trên phongdao.com.
- **Dữ liệu thử:** tự ghép từ danh sách từ, không phải lời dân gian thật.
  - 50 tác phẩm: đã đăng, nháp, ẩn; có bài có Ý nghĩa, có bài chưa.
  - 70 câu kho: có câu mang cờ, câu dài, câu có chú thích.
  - 46 việc chờ, đủ sáu loại: Câu mới, Nháp ý nghĩa, Gợi ý chủ đề, Cần xem, Có thể trùng, Người đọc gửi.
- **Hai vai:** Quản trị (`admin`) và Biên tập (`bientap`, vai Editor của WordPress).
- **Đã làm khoảng 40 bước:**
  - mở Bàn làm việc, bấm **Bắt đầu duyệt**, quyết liền 8 thẻ;
  - xem từng loại việc;
  - làm nhanh 3 gợi ý;
  - lọc Thiếu ý nghĩa, viết Ý nghĩa liền 3 bài;
  - chọn 3 câu ở Kho rồi đăng một mạch;
  - Thêm tác phẩm, Thao tác hàng loạt, Nguồn & cài đặt;
  - Trang quản trị WordPress, danh sách gốc của WordPress;
  - các trang `/bien-tap/`, trang đọc ở chế độ sửa;
  - trên điện thoại: Bàn làm việc, Hộp việc (mở thẻ, quyết), Tác phẩm, Sửa, Kho (xem trước Đăng), menu.
- **Kết quả kỹ thuật:** 0 lỗi trang, 0 lỗi JavaScript.
- **Giới hạn:** chưa đo trên site thật. Số liệu quy mô thật lấy từ khảo sát 05/11: khoảng 630 bài đã đăng (610 thiếu Ý nghĩa), khoảng 500 thẻ chờ (480 là gợi ý chủ đề), hơn 15.000 câu kho.

## 2. Kết luận ngắn

**Đợt 1 và 2 đã gỡ được phần lớn ma sát trên máy tính.**

| Việc | Trước đợt 1 | Bây giờ |
|---|---|---|
| Quyết thẻ liên tục (wp-admin) | 5 thao tác mỗi thẻ, qua 3 màn | **9 bấm cho 8 thẻ** (Bắt đầu duyệt rồi mỗi thẻ 1 bấm, tự sang thẻ kế) |
| Lấy câu từ Kho rồi đăng | 13 thao tác, 2 lần xem trước, dễ đăng nhầm | **5 bấm cho 3 câu**, một màn xem trước |
| Viết Ý nghĩa cho bài thiếu | 7 thao tác mỗi bài, không thấy lời | **8 bấm cho 3 bài liền**, lời luôn hiện, tự sang bài kế |
| Đồng ý 5 gợi ý | 11 thao tác qua 3 màn | 7 bấm (đánh dấu 5 → Đồng ý → OK), hoặc 5 bấm từng thẻ |
| Ẩn một bài | 7 thao tác | 2 bấm (Sửa bài → Ẩn) |
| Hộp việc wp-admin trên điện thoại | 11.848 px | **4.382 px** (5 màn cho 30 thẻ) |

**Còn năm chỗ vướng lớn:**

1. **Điện thoại vẫn là hạng hai.**
   - Tác phẩm dài 21 màn, Kho dài 23 màn cho 30 mục. Màn đầu tiên toàn ô lọc (ảnh 18, 20). Hai màn này chưa đổi từ lần trước.
   - Mở một thẻ ở Hộp việc: nút quyết nằm sát mép dưới màn đầu (ảnh 17).
2. **Gợi ý chủ đề chiếm gần hết Hộp việc.**
   - Mỗi gợi ý là một thẻ; một bài có nhiều thẻ (ảnh 04).
   - Trên site thật, 480 / 500 thẻ là gợi ý.
3. **Trang Sửa tác phẩm còn nhiều khung trống.** 8–10 khung "Sửa … · Chưa có" cao bằng nhau phải cuộn qua. Trên điện thoại, nút Đăng / Ẩn nằm sau toàn bộ các phần (ảnh 08, 19).
4. **Vẫn còn ba lối cho cùng một việc.**
   - Thêm tác phẩm văng ra giao diện trang đọc (ảnh 11).
   - Trang quản trị WordPress vẫn là trang chào mừng (ảnh 12).
   - Danh sách và màn sửa gốc của WordPress cho tác phẩm vẫn mở được (ảnh 13).
   - Các trang `/bien-tap/` (Hộp việc, Duyệt từng thẻ, Tác phẩm, Số liệu tuần) vẫn chạy song song. Nút "Hộp việc" trên trang đọc mở bản cũ (ảnh 14).
5. **Nhiều chỗ nhỏ gây khựng:**
   - mã máy hiện ra ("cờ: tho_tuc");
   - thanh ở đáy màn che hàng cuối (ảnh 06);
   - sau khi Đăng không có lối lùi nhanh;
   - không có lối mở thẻ trùng từ Kho;
   - màn xem trước từ Kho mang tên "Thao tác hàng loạt" (ảnh 10).

**Điểm tốt nên giữ:**
- Bắt đầu duyệt, tự sang thẻ kế, Hoàn tác 10 giây, phím J/K và 1/2/3.
- Kho đăng một mạch.
- Viết Ý nghĩa liền mạch qua "Bài tiếp theo".
- Trang sửa hai cột trên máy tính.
- Vai Biên tập không thấy Nguồn & cài đặt.
- Chưa có lỗi trang nào.

## 3. Chỗ vướng cụ thể đã gặp khi thử

| # | Màn | Thấy gì | Ảnh |
|---|---|---|---|
| 1 | Hộp việc, thẻ Câu mới | Cờ của nguồn in mã máy: "cờ: tho_tuc" | 17 |
| 2 | Hộp việc, Tác phẩm, Kho (máy tính) | Thanh ở đáy màn che hàng cuối danh sách. Bấm ô chọn hàng thứ ba trúng thanh, nên chỉ chọn được 2 / 3 thẻ. Thanh có hai nút xanh cùng lúc (Đồng ý, Làm theo lô…) | 06 |
| 3 | Hộp việc, Gợi ý | Mỗi gợi ý một thẻ: bài "Thử 107" có hai thẻ liền nhau, phải đọc lời hai lần | 04 |
| 4 | Hộp việc, Câu mới | Tên câu chính là dòng đầu, nên dòng đầu lặp hai lần ở danh sách và ở thẻ | 02 |
| 5 | Hộp việc, Nháp ý nghĩa | Chữ nháp (phần cần duyệt) nhỏ hơn lời; không ghi ai viết; muốn sửa một chữ trước khi duyệt phải sang trang sửa | 05 |
| 6 | Sau khi Đăng | Lời báo "Quyết định này không có Hoàn tác nhanh", không có lối Xem bài hay Ẩn ngay | 03 |
| 7 | Thẻ việc | Hai lối mở bài lặp nhau ("Mở bài →" và "Sửa bài / Xem bài trên trang đọc"); dòng "Trạng thái: Chờ" thừa | 04, 05 |
| 8 | Hộp việc (điện thoại) | Mở một thẻ mà hai hàng tab và ô tìm vẫn chiếm nửa màn; nút quyết đầu tiên ở 775 / 844 px | 16, 17 |
| 9 | Tác phẩm (máy tính) | Bộ lọc 6 ô chiếm 2 hàng; không có tab trạng thái kèm số (danh sách gốc WordPress lại có); chữ "Sửa bài" lúc nằm cạnh tên, lúc rơi xuống dòng; không có Xem / Ẩn ngay trên hàng | 07 |
| 10 | Tác phẩm, Kho (điện thoại) | Màn đầu toàn ô lọc; 30 mục dài 21 và 23 màn | 18, 20 |
| 11 | Sửa tác phẩm | 8–10 khung "Sửa … · Chưa có" cao bằng nhau; khung "Việc đang chờ (0)" vẫn chiếm chỗ; chữ "Sửa ý nghĩa · Chưa có" thật ra là thêm mới | 08 |
| 12 | Sửa tác phẩm (điện thoại) | Trạng thái và nút Đăng / Ẩn nằm sau mọi phần (y = 1.148 px với bài 2 dòng) | 19 |
| 13 | Sửa tác phẩm | Menu Phongdao bên trái không mở, không tô mục nào, nên mất định hướng | 08 |
| 14 | Sửa tác phẩm | Không đổi được thể loại ở đâu cả. Câu kho bị máy xếp nhầm thể loại thì đăng xong không sửa được | 08 |
| 15 | Kho | Câu "Quyết trùng trước" không có lối mở thẻ Có thể trùng | 09 |
| 16 | Xem trước Đăng từ Kho | Tiêu đề "Thao tác hàng loạt", chữ "Lô #179"; lối về là "Các lô của bạn" chứ không phải Kho; đoạn giải thích 4 dòng; nút "Lưu bỏ chọn trên trang này" dù chỉ có một trang; cột "Lúc này: Chưa dùng" | 10 |
| 17 | Thêm tác phẩm | Mở trang ngoài wp-admin (giao diện trang đọc, mất menu trái) | 11 |
| 18 | Trang quản trị WordPress | `/wp-admin/` vẫn là trang chào mừng WordPress; mục "Trang quản trị" đứng trên Phongdao | 12 |
| 19 | Danh sách gốc WordPress | Mở được danh sách tác phẩm gốc, có "Thêm Bài Viết", sửa nhanh, hành động hàng loạt của WordPress: lối thứ ba. Bấm tên bài mở màn sửa gốc chỉ có ô tên, tác giả, bình luận, không có lời, ý nghĩa… | 13 |
| 20 | Trang đọc, chế độ sửa | Nút "Hộp việc" trên đầu trang mở bản cũ `/bien-tap/`; dải "Đang sửa" không có lối "Mở trang sửa đầy đủ" | 14 |
| 21 | `/bien-tap/` | Hộp việc (18 màn trên điện thoại), Duyệt từng thẻ, Tác phẩm, Số liệu tuần vẫn chạy song song với wp-admin | — |
| 22 | Bình luận WordPress | Duyệt hay bỏ một cảm nghĩ ở màn Bình luận thì thẻ Hộp việc tương ứng không tự đóng, thành thẻ "ma" | — |
| 23 | Bàn làm việc | Màn mở chậm nhất (2,6 giây tại máy) vì tính Số liệu tuần mỗi lần mở; site thật hơn 600 bài sẽ chậm hơn | — |
| 24 | Chữ | Khó hiểu: "Chưa dùng", "Lúc này", "Phần và cờ", "Mọi mức", "Nguồn Lời", "nguồn: mau" (mã nguồn khi chưa có hồ sơ); thiếu khoảng trắng "Làm theo lô— đã chọn 2" | 06, 09, 10 |

## 4. Đề xuất cho đợt 3

Mỗi mục có số để duyệt nhanh, ví dụ "duyệt 1.1–1.10, 2.1–2.4, bỏ 3.5". Cỡ: nhỏ (dưới 1 ngày), vừa (1–3 ngày), lớn (hơn 3 ngày). Lằn ranh giữ nguyên:
- máy không viết lời hay nguồn;
- không tự đăng, không đặt dấu duyệt;
- đăng nhiều câu luôn qua màn xem trước;
- không ẩn menu WordPress.

### Gói 1. Sửa nhanh chỗ khựng hằng ngày (cả gói khoảng 2–3 ngày)

**1.1 Cờ hiện bằng chữ** · nhỏ
- *Làm gì:* thẻ Câu mới ghi "có từ thô tục", "nghi là câu hiện đại"… thay vì mã máy.
- *Mục đích:* đọc là hiểu, không phải đoán.

**1.2 Thanh chọn nhiều không che danh sách** · nhỏ
- *Làm gì:* trên máy tính, thanh chuyển lên đầu danh sách, dính ở trên khi cuộn. Trên điện thoại vẫn ở đáy màn nhưng chừa chỗ để không che. Chỉ một nút chính màu xanh.
- *Mục đích:* hết bấm nhầm hàng cuối.

**1.3 Lối lùi sau khi Đăng** · nhỏ
- *Làm gì:* lời báo thành "Đã đăng 'Tên bài'. **Xem bài** · **Ẩn nếu nhầm**". Ẩn chỉ cần một bấm.
- *Mục đích:* thay câu "không có Hoàn tác nhanh"; lỡ tay thì gỡ ngay.

**1.4 Thẻ việc gọn** · nhỏ
- *Làm gì:*
  - bỏ dòng "Trạng thái: Chờ" và lối "Mở bài →" trùng;
  - câu mới không lặp dòng đầu;
  - nháp ý nghĩa ghi người viết, chữ nháp to và rõ như phần chính.
- *Mục đích:* thấy ngay phần cần quyết.

**1.5 Kho: mở thẻ trùng** · nhỏ
- *Làm gì:* "Quyết trùng trước" thành liên kết mở đúng thẻ Có thể trùng.
- *Mục đích:* không phải đi tìm thẻ.

**1.6 Màn xem trước từ Kho dễ hiểu** · nhỏ
- *Làm gì:*
  - tiêu đề "Xem trước: đăng 3 câu từ Kho", lối "← Về Kho";
  - giải thích còn 2 dòng;
  - ẩn "Lưu bỏ chọn" khi chỉ có một trang;
  - đổi "Lúc này / Chưa dùng" thành "Tình trạng / Chưa đăng".
- *Mục đích:* người không biết chữ "lô" vẫn hiểu mình đang làm gì.

**1.7 Chữ dễ hiểu** · nhỏ
- *Làm gì:*
  - "Phần và cờ" → "Còn thiếu, có cờ";
  - "Mọi mức" → "Không lọc";
  - "Nguồn Lời" → "Nguồn";
  - "Sửa ý nghĩa · Chưa có" → "Thêm ý nghĩa";
  - hồ sơ nguồn chưa có tên thì ghi "nguồn chưa đặt tên" thay mã.

**1.8 Menu không mất dấu** · nhỏ
- *Làm gì:* ở trang Sửa tác phẩm và màn xem trước, menu Phongdao mở và tô đúng mục người vừa đến (Tác phẩm, Kho hay Hộp việc).

**1.9 Bàn làm việc mở nhanh** · nhỏ
- *Làm gì:* Số liệu tuần chỉ tính khi bấm mở khối, hoặc nhớ kết quả trong 1 giờ.
- *Mục đích:* mở Bàn làm việc nhanh, kể cả khi site có hàng nghìn bài.

**1.10 Bình luận và Hộp việc khớp nhau** · nhỏ
- *Làm gì:* duyệt hay bỏ cảm nghĩ ở màn Bình luận của WordPress thì thẻ Hộp việc tương ứng tự đóng, ghi "đã xử lý ở Bình luận".
- *Mục đích:* hết thẻ "ma".

### Gói 2. Hộp việc (cả gói khoảng 1 tuần)

**2.1 Gợi ý chủ đề, vùng gom theo bài** · vừa
- *Làm gì:*
  - mỗi bài một thẻ: lời một lần, rồi các gợi ý dạng nút chọn ("Chủ đề: Kinh nghiệm thời tiết ✓", "Chủ đề: Kinh nghiệm mùa vụ ✓");
  - hai nút **Đồng ý các mục đã chọn** và **Bỏ cả**;
  - máy vẫn không tự gắn.
- *Mục đích:* 480 thẻ gợi ý gộp còn ít hơn nhiều. Đọc lời một lần cho nhiều gợi ý; đây là nút thắt lớn nhất của Hộp việc trên site thật.

**2.2 Duyệt trên điện thoại một tay** · vừa
- *Làm gì:* khi mở một thẻ, ẩn tab và ô tìm, chỉ còn "← Danh sách · Thẻ 3 / 36". Thanh nút quyết dính đáy màn, có Trước / Sau.
- *Mục đích:* quyết liên tục trên điện thoại như Duyệt từng thẻ cũ, nhưng trong wp-admin.

**2.3 Sửa rồi duyệt nháp ngay trên thẻ** · vừa
- *Làm gì:* thẻ Nháp ý nghĩa có ô chữ chứa sẵn bản nháp. Sửa vài chữ rồi bấm "Duyệt" (lưu qua đường lưu hiện có, có Lịch sử).
- *Mục đích:* không phải sang trang sửa chỉ để chỉnh một chữ.

**2.4 Đầu Hộp việc gọn** · nhỏ
- *Làm gì:* một hàng tab loại việc kèm số (trên điện thoại cuộn ngang). Trạng thái (Chờ, Để sau, Đã xong) thành ô chọn nhỏ cạnh ô tìm.
- *Mục đích:* thấy thẻ ngay ở màn đầu, trên cả máy tính lẫn điện thoại.

**2.5 Nút quyết ghi số phím tắt** · nhỏ
- *Làm gì:* trên máy tính, nút ghi kèm số phím: "1 Đăng", "2 Sửa rồi đăng"…
- *Mục đích:* nhớ phím không cần đọc hướng dẫn.

### Gói 3. Tác phẩm, Sửa, Thêm (cả gói khoảng 1 tuần)

**3.1 Danh sách Tác phẩm gọn** · vừa
- *Làm gì:*
  - tab trạng thái kèm số: Tất cả · Đã đăng · Nháp · Đã ẩn · Thùng rác;
  - nút lọc nhanh "Thiếu ý nghĩa", "Có cờ"; bộ lọc còn lại gập trong "Thêm bộ lọc";
  - mỗi hàng có **Sửa · Xem · Ẩn** ngay trên hàng.
- *Mục đích:* việc hay làm chỉ một bấm, màn đầu thấy bài ngay.

**3.2 Tác phẩm và Kho trên điện thoại** · vừa
- *Làm gì:* mỗi mục một thẻ ba dòng (tên; trạng thái và thể loại; còn thiếu gì hoặc cờ). Bộ lọc gập lại.
- *Mục đích:* 30 mục từ 21–23 màn xuống khoảng 6–8 màn.

**3.3 Sửa tác phẩm gọn** · vừa
- *Làm gì:*
  - các phần "Chưa có" gom thành một hàng nút "Thêm: Ý nghĩa · Bạn có biết · Từ khó · Dị bản · …"; bấm mới mở ô;
  - ẩn "Việc đang chờ" khi không có việc;
  - trên điện thoại, trạng thái và nút Đăng / Ẩn lên ngay dưới tên bài.
- *Mục đích:* ít cuộn; nút quan trọng ở chỗ dễ thấy.

**3.4 Thêm tác phẩm ngay trong wp-admin** · vừa
- *Làm gì:* cùng biểu mẫu và cùng kiểm trùng như bây giờ, chỉ chuyển vào wp-admin.
- *Mục đích:* không văng sang giao diện trang đọc giữa lúc làm việc.

**3.5 Đổi thể loại của bài** · vừa · **cần bạn quyết**
- *Làm gì:* cho đổi thể loại ở trang sửa. Đường dẫn đổi theo thể loại mới; đường cũ tự chuyển hướng sang đường mới, người đọc và Google không bị mất lối.
- *Mục đích:* sửa được câu máy xếp nhầm thể loại.
- *Vì sao phải hỏi:* đây là đổi đường dẫn của bài đã đăng.

### Gói 4. Kho (khoảng 3–4 ngày)

**4.1 Lọc và xếp câu đáng đăng trước** · vừa
- *Làm gì:* lọc hoặc xếp theo "người đọc mong có", "có chú thích", "không cờ", "câu ngắn / dài".
- *Mục đích:* với hơn 15.000 câu, chọn câu đáng đăng trước thay vì lật từng trang theo ngày lấy.

**4.2 Câu máy chọn hằng ngày** · vừa · **cần bạn quyết**
- *Làm gì:* thôi tạo thẻ Câu mới mỗi ngày. Thay vào đó, Kho có bộ lọc "Máy gợi ý hôm nay", đăng một mạch như mọi câu kho.
- *Mục đích:* câu mới chỉ còn một lối (Kho); Hộp việc chỉ giữ việc thật sự cần quyết.

### Gói 5. Một nơi làm việc (lõi của đợt 3; khoảng 1–2 tuần)

**5.1 Gộp các trang `/bien-tap/` về wp-admin** · vừa · **cần bạn quyết**
- *Làm gì:*
  - Hộp việc, Duyệt từng thẻ, Tác phẩm, Số liệu tuần, Thêm bài chuyển thẳng sang màn wp-admin tương ứng;
  - nút Hộp việc trên đầu trang đọc mở bản wp-admin;
  - **giữ** nút Sửa và tấm sửa trên trang đọc.
- *Mục đích:* một nơi làm việc, hết phải nhớ việc nào làm ở đâu.

**5.2 Từ trang đọc sang trang sửa đầy đủ** · nhỏ
- *Làm gì:* dải "Đang sửa" trên trang đọc có thêm "Mở trang sửa đầy đủ".

**5.3 Lối gốc WordPress của tác phẩm chuyển sang Phongdao** · nhỏ
- *Làm gì:* danh sách và màn sửa gốc của WordPress cho tác phẩm chuyển thẳng sang Tác phẩm / Sửa tác phẩm của Phongdao. Không ẩn menu nào.
- *Mục đích:* không ai lỡ mở màn sửa gốc (ở đó chỉ có ô tên bài, không có lời, ý nghĩa…).

**5.4 Trang quản trị WordPress** · nhỏ · **cần bạn chọn**
- **(a)** đặt khung "Phongdao hôm nay" (4 ô số và Bắt đầu duyệt) ở đầu trang, giữ phần còn lại của WordPress;
- **(b)** mở `/wp-admin/` là vào thẳng Bàn làm việc.

**5.5 Thanh dưới trên điện thoại** · vừa
- *Làm gì:* trong wp-admin có thanh cố định Bàn làm việc · Hộp việc · Tác phẩm · Kho.
- *Mục đích:* đổi màn một chạm, không phải mở menu ☰.

## 5. Nên bỏ

- Các trang `/bien-tap/` trùng chức năng: Hộp việc, Duyệt từng thẻ, Tác phẩm, Số liệu tuần, Thêm bài (5.1, nếu bạn đồng ý). Giữ tấm sửa trên trang đọc.
- Lối gốc WordPress vào danh sách và trình sửa tác phẩm: chuyển hướng, không ẩn menu (5.3).
- Thẻ Câu mới do máy tạo mỗi ngày (4.2, nếu bạn đồng ý).
- Dòng và lối thừa trên thẻ việc (1.4); khung "Chưa có" ở trang sửa (3.3).
- Nút "Lưu bỏ chọn" khi chỉ có một trang (1.6). Câu "Mẹo: giữ Shift…" dài thì rút thành dấu ⓘ.

## 6. Cần bạn quyết

1. **5.1:** chuyển hẳn các trang `/bien-tap/` sang wp-admin (giữ sửa trên trang đọc)? Mình đề xuất: **có**.
2. **5.4:** Trang quản trị WordPress chọn **(a)** khung "Phongdao hôm nay" hay **(b)** vào thẳng Bàn làm việc? Mình đề xuất: **(a)**, vì không đụng tới cách WordPress chạy.
3. **3.5:** cho đổi thể loại kèm chuyển hướng đường dẫn cũ? Mình đề xuất: **có**, chỉ người có quyền duyệt.
4. **4.2:** câu máy chọn hằng ngày chuyển thành bộ lọc trong Kho, thôi tạo thẻ? Mình đề xuất: **có**.
5. **Phạm vi đợt 3:** làm cả năm gói, hay chỉ một phần?

## 7. Thứ tự làm đề xuất

1. **Gói 1** trước: nhỏ, gỡ chỗ khựng hằng ngày; có thể lên site trong 2–3 ngày.
2. **Gói 2** cùng **3.1, 3.3**: Hộp việc và trang sửa, chỗ bạn ở lâu nhất.
3. **3.2, 4, 5**: điện thoại hạng nhất và một nơi làm việc.

Mỗi phần một PR có bản xem trước để bạn bấm thử trước khi lên site.

## 8. Ảnh minh hoạ

| Ảnh | Nội dung |
|---|---|
| `anh/01-ban-lam-viec.png` | Bàn làm việc: bốn ô số, Bắt đầu duyệt, Nên làm tuần này |
| `anh/02-bat-dau-duyet.png` | Hộp việc hộp thư sau Bắt đầu duyệt; dòng đầu câu mới lặp hai lần |
| `anh/03-sau-quyet-1.png` | Sau Đăng: tự sang thẻ kế; lời báo "không có Hoàn tác nhanh" |
| `anh/04-hv-goi_y.png` | Gợi ý: một bài có nhiều thẻ; hai lối mở bài lặp nhau |
| `anh/05-hv-nhap_y_nghia.png` | Nháp ý nghĩa: chữ nháp nhỏ, không ghi người viết |
| `anh/06-lam-nhanh-thanh.png` | Thanh đáy màn che hàng thứ ba; hai nút xanh |
| `anh/07-tac-pham.png` | Tác phẩm: bộ lọc hai hàng, "Sửa bài" rơi dòng |
| `anh/08-sua.png` | Sửa tác phẩm hai cột: nhiều khung "Chưa có"; menu bên trái không tô mục |
| `anh/09-kho.png` | Kho: "Quyết trùng trước" không có lối mở thẻ |
| `anh/10-kho-xem-truoc.png` | Xem trước Đăng từ Kho: tiêu đề "Thao tác hàng loạt", chữ kỹ thuật |
| `anh/11-them.png` | Thêm tác phẩm văng sang giao diện trang đọc |
| `anh/12-dashboard-wp.png` | `/wp-admin/` vẫn là trang chào mừng WordPress |
| `anh/13-wp-tac-pham.png` | Danh sách gốc WordPress của tác phẩm vẫn mở được |
| `anh/14-bt-trang-doc.png` | Trang đọc ở chế độ sửa: nút Hộp việc mở bản cũ |
| `anh/15-dt-ban-lam-viec.png` | Điện thoại: Bàn làm việc (1,7 màn) |
| `anh/16-dt-hop-viec.png` | Điện thoại: Hộp việc, nửa màn đầu là tab |
| `anh/17-dt-hv-the.png` | Điện thoại: mở thẻ, nút quyết sát đáy; "cờ: tho_tuc" |
| `anh/18-dt-tac-pham.png` | Điện thoại: Tác phẩm, màn đầu toàn ô lọc |
| `anh/19-dt-sua.png` | Điện thoại: Sửa tác phẩm, khung "Chưa có" nối nhau |
| `anh/20-dt-kho.png` | Điện thoại: Kho, màn đầu toàn ô lọc |
