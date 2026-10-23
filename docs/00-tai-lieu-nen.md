# 00 · Tài liệu nền

| | |
|---|---|
| Phiên bản | 1.1 · 05/10/2026 (sau review trước triển khai) |
| Trạng thái | **Ổn định.** Đổi nội dung phải ghi vào `01-nhat-ky-quyet-dinh.md` |
| Đọc khi | Trước mọi việc khác. Người mới và AI agent đọc tài liệu này đầu tiên |
| Nguồn gốc | Hợp nhất 4 bản ý tưởng (Meta AI, GPT-5.6 Sol, Qwen3.8, Opus 5.5) và 1 bản review song song của GPT |

Tài liệu này trả lời: làm gì, cho ai, theo nguyên tắc nào, trong phạm vi nào, đo bằng gì. Chi tiết "làm thế nào" nằm ở các tài liệu số 10 trở đi.

---

## 1. Vấn đề

Người tìm ca dao tục ngữ trên mạng gặp sáu vấn đề lặp lại:

1. **Rác SEO và quảng cáo.** Nhiều trang mỏng, popup, quảng cáo chèn giữa lời thơ.
2. **Chép lại nhau, sai chữ, không nguồn.** Không ai biết bản nào đáng tin.
3. **Xóa bỏ dị bản.** Một bản được đưa ra như "bản đúng duy nhất".
4. **Giải nghĩa nông hoặc bịa.** Từ cổ, phương ngữ, điển tích hầu như không được chú giải.
5. **Dữ liệu khô.** Không bối cảnh diễn xướng, không âm thanh, không liên kết giữa các bài.
6. **Không tìm được theo nhu cầu thật.** Người dùng biết mình cảm thấy gì ("nhớ mẹ") nhưng lời ca dao dùng ngôn ngữ cổ nên tìm từ khóa không ra.

## 2. Định vị

- **Định vị:** kho ca dao tục ngữ đáng tin cậy, dễ hiểu và giàu ngữ cảnh nhất; nơi đọc, hiểu, trích dẫn và chia sẻ **đúng nguồn**.
- **Câu định vị công khai:** "Đọc một câu. Hiểu một đời sống."
- **Lời hứa:** "Mỗi câu có nguồn. Mỗi dị bản được tôn trọng. Mỗi cách hiểu đều nói rõ căn cứ."
- **Ba trụ sản phẩm** (dùng làm trọng tài: mỗi tính năng phải phục vụ ít nhất một trụ):

| TIN | HIỂU | CẢM |
|---|---|---|
| Nguồn, mức bằng chứng, lịch sử sửa đổi | Chú giải, dị bản, bối cảnh, cách hiểu | Tình huống sống, chữ đẹp, giọng người thật |

- **Tiêu chí duyệt giao diện:** Chuẩn · Sống · Đẹp.
- **Lợi thế bền vững:** không phải AI hay giao diện, mà là bộ dữ liệu: đồ thị dị bản + nguồn gốc + từ điển dùng chung + cách hiểu có căn cứ + tình huống + giọng người thật + lịch sử sửa đổi. Website chỉ là một giao diện trên bộ dữ liệu đó.

## 3. Người dùng

**Phát hiện then chốt:** cả hai nhóm đều sợ sai — giáo viên sợ dạy sai, người trẻ sợ chia sẻ sai. Vì vậy nhãn bằng chứng là tính năng xương sống, xuất hiện ở mọi nơi: trang bài, kết quả tìm, thẻ chia sẻ, dòng trích dẫn.

| Persona | Mô tả | Thành công nghĩa là |
|---|---|---|
| **Cô Hạnh** (chính, nhóm A) | 38 tuổi, giáo viên Ngữ văn THCS ở thị xã; soạn giáo án buổi tối, tra nhanh trên điện thoại | Trong **dưới 1 phút** có văn bản đáng tin + dòng trích dẫn để dán + một chi tiết thú vị để kể trên lớp |
| **Sinh viên Ngữ văn** (phụ, nhóm A) | Làm tiểu luận, cần dị bản, nguồn, dữ liệu | Tải được dữ liệu có DOI, trích dẫn được |
| **Minh** (chính, nhóm B) | 24 tuổi, văn phòng, xa quê, đăng Story dịp Tết, Vu Lan; gõ không dấu | Trong **dưới 30 giây** tìm được câu ưng ý và tạo ảnh đẹp có nguồn |
| **Phụ huynh ở xa** (phụ, nhóm B) | Muốn dạy con câu hát ru "đúng như ông bà hát" | Nghe được giọng thật, hiểu nghĩa trong **10 giây** |
| **Bác Bảy** (người giữ tri thức) | 65 tuổi, Nam Bộ, nhớ nhiều dị bản và cách hát; **không tự điền form**, sợ bị lợi dụng giọng nói | Câu bác nhớ được ghi lại đúng, ghi công theo ý bác, gỡ được khi bác muốn |
| **Người ghi hộ** (người đóng góp) | Con cháu, học sinh, sinh viên, giáo viên ghi lại lời hoặc giọng của người giữ tri thức | Gửi trên điện thoại **trong 5 phút**, kèm người kể, quan hệ, nơi, thời điểm và sự đồng ý của người kể |

Thông điệp kêu gọi đóng góp hướng tới người ghi hộ: **"Hãy ghi lại một câu ông bà bạn còn nhớ."** — không phải "hãy đóng góp dữ liệu".

User story đầy đủ: `22-backlog-trien-khai.md`.

## 4. Mười nguyên tắc trọng tài

1. **Sâu hơn rộng.** 100 hồ sơ chuẩn hơn 10.000 câu không nguồn. Không xuất bản "mục nhẹ".
2. **Mọi khẳng định có nguồn.** Không biết thì ghi "chưa rõ".
3. **Không có bản đúng duy nhất.** Mỗi tác phẩm có một **Bản tham chiếu** kèm lý do chọn.
4. **Lời đã đối chiếu là bất biến.** Chuẩn hóa chỉ diễn ra trong chỉ mục tìm kiếm.
5. **AI để tìm và soạn nháp từ nguồn đã cung cấp; không bao giờ xuất bản.**
6. **Tĩnh trước, động sau.**
7. **Điện thoại trước; tắt JavaScript vẫn đọc được.**
8. **Không quảng cáo, không theo dõi.**
9. **Dữ liệu mở, có phiên bản, trích dẫn được.**
10. **Mọi tính năng mới phải qua hai cửa:**
    - Bốn câu hỏi: có giúp tìm / hiểu / tin / bảo tồn tốt hơn không? Một người vận hành được không? Có thêm gánh kiểm duyệt không? Có làm dữ liệu tốt hơn không?
    - Câu hỏi bền vững: một người, 10 giờ/tuần, duy trì được 2 năm không?

## 5. Phạm vi

**Trong phạm vi năm đầu:** ca dao và tục ngữ người Việt (trọng tâm); thành ngữ chọn lọc (có trong SGK hoặc hay bị nhầm với tục ngữ).
**Sau:** đồng dao (P1); vè, câu đố (P2).
**Ngoài phạm vi:** văn học dân gian các dân tộc thiểu số (cần chuyên gia, bản dịch và sự đồng thuận của cộng đồng sở hữu; chỉ xem xét khi có đối tác); ký âm dân ca; truyện cổ tích; thơ hiện đại. "Ca dao mới", "ca dao chế" chỉ xuất hiện khi gắn cờ "Nghi không phải dân gian".

### Những thứ không làm

| Không làm | Lý do | Xem xét lại khi |
|---|---|---|
| Tài khoản người dùng, đăng nhập | Bảo mật, dữ liệu cá nhân, chi phí vận hành | Có ≥ 3 người vận hành |
| Bình luận, lượt thích, mạng xã hội | Kiểm duyệt nuốt hết thời gian một người | Không bao giờ ở dạng mở |
| Chatbot; AI tự sinh nội dung công khai | Một lỗi ảo giác phá lợi thế duy nhất là niềm tin | Không bao giờ |
| Đọc ca dao bằng giọng máy (TTS) | Vô hồn, xóa chất địa phương, dễ bị nhầm là tư liệu | Không bao giờ |
| App iOS/Android | Gấp đôi công sức; web tốt trên điện thoại là đủ | Có nhu cầu ngoại tuyến rõ |
| Nhập/cào hàng loạt | Thành thêm một trang rác; rủi ro bản quyền | Không bao giờ ở dạng không kiểm chứng |
| Bản đồ tương tác | Tốn công, địa danh chưa chuẩn | Có ≥ 500 dị bản có vùng rõ |
| Điểm thưởng, huy hiệu, bảng xếp hạng | Lệch trọng tâm, biến di sản thành cuộc đua | Có đối tác giáo dục cần |
| Bản tiếng Anh | Dịch ca dao là việc của chuyên gia | Có cộng tác viên dịch thuật |
| CMS tự xây | Git + YAML đủ cho một người | Xem ngưỡng ở `21-kien-truc-ky-thuat.md` |
| API công khai, feed gợi ý | Chưa cần; tốn bảo trì | Sau bản phát hành dữ liệu thứ hai |
| Quảng cáo, liên kết tiếp thị | Mâu thuẫn với lý do tồn tại | Không bao giờ |

## 6. Phạm vi MVP

**Nguyên tắc: mô hình dữ liệu giàu, trải nghiệm đọc mặc định đơn giản.** MVP chia hai lớp. **P0-Core** chứng minh giá trị cốt lõi (tin, hiểu, trích dẫn) và là điều kiện để mời người dùng thử. **P0.5** làm sản phẩm hoàn chỉnh hơn, phải xong trước ra mắt công khai nhưng **không được chặn** preview và beta. Tất cả chạy trên trang tĩnh, không máy chủ. Lời hứa "tìm theo cảm xúc" được giữ từ đầu bằng **trang tình huống** và **câu mô tả đời thường**; tìm ngữ nghĩa chỉ bật khi qua cổng chất lượng.

| Lớp | Hạng mục |
|---|---|
| **P0-Core** | 1. Trang chủ có ô tìm + danh sách tình huống (tĩnh) · 2. Tìm kiếm: có dấu, không dấu, khớp gần đúng; nội dung, tình huống, mục từ · 3. Trang tác phẩm, đọc theo lớp (`20` mục 4) với "Hiểu trong 10 giây" · 4. Chú giải chạm-là-hiện · 5. Nguồn hiển thị ngay dưới lời · 6. Dị bản + khối "Cộng đồng ghi nhận" · 7. Trích dẫn 2 định dạng · 8. Trang tình huống cơ bản · 9. Duyệt theo chủ đề, thể loại, từ điển · 10. Liên kết báo lỗi + gửi dị bản · 11. Trang Phương pháp, Nguồn, Về dự án · 12. Kiểm tra dữ liệu + triển khai ổn định · 13. Đo lường tối thiểu (`21` mục 7) |
| **P0.5** (trước ra mắt công khai) | Thẻ chia sẻ (2 mẫu, 2 khổ) · ảnh OG riêng từng bài · chế độ sáng/tối có nút đổi · ngẫu nhiên, câu hôm nay, A–Z · lịch tình huống theo dịp · trình phát âm thanh · trang Chính sách AI, Giấy phép, Quyền riêng tư · đo lường mở rộng |

**P1 (sau ra mắt):** so sánh dị bản; trang mô-típ, SGK, vùng; trang "Những người giữ lửa"; bản phát hành dữ liệu có DOI; hội đồng cố vấn.
**Theo cổng:** tìm ngữ nghĩa (recall@5 ≥ 70%).
**P2:** chiến dịch ghi âm; ghi âm trên web; trang "Thường bị nhầm là ca dao"; chế độ lớp học; hợp tác trường đại học.
**Theo điều kiện kích hoạt** (`21` mục 14): công cụ biên tập riêng (Admin workbench), đường ống nạp tư liệu bán tự động.

## 7. Lộ trình

Số hồ sơ không bao giờ là mục tiêu cao hơn chất lượng. Mỗi mốc có **cổng**; chưa đạt cổng thì chưa sang bước sau, kể cả khi đã đến ngày. Giả định ~10 giờ/tuần, bắt đầu 05/10/2026.

| Bước | Hồ sơ | Kết quả | Cổng |
|---|---|---|---|
| **GĐ0 · Chứng minh mô hình dữ liệu** | 10 thật | Chốt Q-001..Q-003; kho mã; kiểm tra dữ liệu chạy; 10 hồ sơ đi hết vòng; đo thời gian theo từng phần; sửa điểm đau khi nhập liệu; token giao diện; 30 truy vấn thử | **Cổng 1:** 10 hồ sơ qua checklist; có số liệu thời gian 5 phần; schema chỉnh xong và **đóng băng v1** |
| **GĐ0.5 · Lát cắt dọc** | 3 trong số 10 | Một đường đi hoàn chỉnh: YAML → kiểm tra → Astro → trang tác phẩm, chú giải, nguồn, dị bản, tìm kiếm, trích dẫn → bản xem trước | **Cổng 1.5:** một người mới tìm → đọc → hiểu → xem chú giải, nguồn, dị bản → sao chép trích dẫn mà không cần biết cấu trúc nội bộ |
| **GĐ1 · Lõi sản phẩm + phản hồi sớm** | 15–20 → 25–30 → 60 | Xây P0-Core. **Preview kín** ở 15–20 hồ sơ (2–3 giáo viên, 2–3 người trẻ, 1 người am hiểu văn hóa dân gian). **Beta khả dụng** ở 25–30 hồ sơ. Sau đó mở rộng nội dung tới 60 hồ sơ, 20 tình huống; xây P0.5 | **Cổng 2** (ở beta 25–30): nhiệm vụ thử đạt `24` mục 6; đạt ngân sách hiệu năng và WCAG 2.2 AA |
| **GĐ2 · Ra mắt công khai** | ~120 | P0.5 xong; bộ trang công khai; sao lưu đã thử khôi phục; P1 bắt đầu; duyệt đóng góp theo lô; 3–5 cố vấn | **Cổng 3:** ≥ 120 hồ sơ qua checklist; 0 lỗi kiểm tra; báo cáo sức khỏe không còn nợ ở hồ sơ đã xuất bản; lỗi beta đã xử lý |
| **GĐ3 · Cộng đồng, chiều sâu** | 120 → 300 → 500 | Tìm ngữ nghĩa khi qua cổng; chiến dịch "Giọng bà, giọng mẹ" dịp Vu Lan 2027; một khoa Ngữ văn; công cụ biên tập/nạp tư liệu khi đạt điều kiện | **Cổng 4:** 300 hồ sơ; duyệt đóng góp gọn trong 2 giờ/tuần |

**Mốc thời gian dự kiến** — chọn kịch bản theo tổng số phút/hồ sơ đo ở GĐ0:

| Kịch bản | Điều kiện | Lát cắt dọc | Preview (15–20) | Beta (25–30) | 60 hồ sơ | Ra mắt (~120) | 300 hồ sơ |
|---|---|---|---|---|---|---|---|
| Cơ sở | ≤ 45 phút/hồ sơ | 26/10–08/11/2026 | cuối 11/2026 | giữa 12/2026 | cuối 1/2027 | ~giữa 3/2027 | ~7/2027 |
| Thận trọng | 60–75 phút/hồ sơ | 26/10–15/11/2026 | đầu 12/2026 | đầu 1/2027 | giữa 2/2027 | ~5/2027 | ~11/2027 |

So với bản 1.0, ra mắt công khai lùi khoảng 6–8 tuần để đổi lấy lát cắt dọc và hai vòng thử với người dùng thật trước khi làm hàng loạt.

## 8. Chỉ số

**North Star — Lượt dùng tin cậy mỗi tuần** = lượt sao chép trích dẫn + thẻ chia sẻ có nguồn được tạo (từ khi có P0.5) + lượt tải dữ liệu (từ khi có bản phát hành).

| Nhóm | Chỉ số | Mục tiêu 6 tháng sau ra mắt công khai |
|---|---|---|
| Chất lượng | Hồ sơ đã xuất bản | 300 |
| | Báo lỗi xử lý trong cam kết | > 80% |
| | Hồ sơ phải hạ mức vì sai nguồn | Giảm dần |
| Khám phá | Lượt tìm có bấm vào kết quả | > 70% |
| | Lượt tìm không có kết quả | Giảm mỗi tháng |
| | Lượt xem có mở chú giải | 30–50% |
| | Lượt xem có mở khối Nguồn | Theo dõi (dấu hiệu người đọc kiểm chứng) |
| | Báo lỗi được gửi | Theo dõi; mỗi báo lỗi đi vào quy trình ở `40` mục 3 |
| Cộng đồng | Đóng góp xuất bản mức C / được nâng mức | 50 / 15 |
| | Ghi âm hợp lệ | 20 sau chiến dịch đầu |
| | Giáo viên trong nhóm thử | 10–20 |

**Không tối ưu:** lượt xem trang, thời gian ở lại, tổng số bài, số trang được index.

## 9. Rủi ro chính

| Rủi ro | Khả năng | Ảnh hưởng | Giảm thiểu |
|---|---|---|---|
| Người sáng lập kiệt sức | Cao | Rất cao | Trần 10 giờ/tuần; tĩnh, ít bảo trì; không hứa tiến độ công khai; hai cửa ở nguyên tắc 10 |
| Sai sót nội dung | TB | Rất cao | Đối chiếu ≥ 2 nguồn in; đọc lại sau 24 giờ; báo lỗi trên mọi trang; lịch sử Git công khai |
| Ảo giác AI lọt vào dữ liệu (kể cả từ chính các tài liệu thiết kế) | TB | Cao | Dấu `NHAP_AI` / `CAN_BO_SUNG` chặn xuất bản; không chép ví dụ nào trong các bản ý tưởng gốc |
| Rào cản Git/YAML | TB | TB | Phương án B (Q-002) |
| Tìm theo cảm xúc lúc đầu chưa đủ "phép màu" | TB | TB | Đầu tư trang tình huống; cổng ngữ nghĩa |
| Ít người đóng góp | Cao | TB | Kho chính không phụ thuộc cộng đồng; chiến dịch theo dịp; hợp tác một trường |
| Bị cào dữ liệu làm trang rác | Cao | Thấp–TB | Không cố chặn; giấy phép buộc ghi nguồn; giá trị nằm ở dị bản, ghi âm, cập nhật, DOI |
| Vi phạm bản quyền tuyển tập | Thấp–TB | Cao | Chỉ trích lời + số trang; tự viết chú giải; gỡ tạm khi có khiếu nại |
| Dịch vụ miễn phí đổi chính sách | TB | TB | Trang tĩnh, chuyển nhà trong một buổi |

## 10. Bản đồ tài liệu

| Số | Tài liệu | Cần khi |
|---|---|---|
| 01 | Nhật ký quyết định + câu hỏi còn mở | Luôn |
| 10–13 | Dữ liệu, biên soạn, nguồn, danh sách ưu tiên | GĐ0 |
| 20–24 | Giao diện, kỹ thuật, backlog, kiểm thử, preview và beta | GĐ0.5–GĐ1 |
| 30 | Sổ tay vận hành | Trước ra mắt công khai |
| 40–42 | Đóng góp, ghi âm, cố vấn | Trước khi mở đóng góp/ghi âm |
| 50 | Bản nháp các trang công khai | Trước ra mắt công khai |
