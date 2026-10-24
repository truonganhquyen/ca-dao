# 00 · Tài liệu nền

| | |
|---|---|
| Phiên bản | 2.0 · 23/10/2026 (hướng v2: D-035..D-047) |
| Trạng thái | **Ổn định.** Đổi nội dung phải ghi vào `01-nhat-ky-quyet-dinh.md` |
| Đọc khi | Trước mọi việc khác. Người mới và AI agent đọc tài liệu này đầu tiên |
| Nguồn gốc | v1: hợp nhất 4 bản ý tưởng và 1 bản review. v2: phiên đánh giá khả thi cùng Claude Code, 23/10/2026 |

Tài liệu này trả lời: làm gì, cho ai, theo nguyên tắc nào, trong phạm vi nào, đo bằng gì. Chi tiết "làm thế nào" nằm ở các tài liệu số 10 trở đi.

---

## 1. Vấn đề

Người tìm ca dao tục ngữ trên mạng gặp sáu vấn đề lặp lại:

1. **Rác SEO và quảng cáo.** Nhiều trang mỏng, popup, quảng cáo chèn giữa lời thơ.
2. **Chép lại nhau, sai chữ, không nguồn.** Không ai biết bản nào đáng tin.
3. **Xóa bỏ dị bản.** Một bản được đưa ra như "bản đúng duy nhất".
4. **Giải nghĩa nông hoặc bịa.** Từ cổ, phương ngữ, điển tích hầu như không được chú giải.
5. **Dữ liệu khô.** Không bối cảnh, không liên kết giữa các bài.
6. **Không tìm được theo nhu cầu thật.** Người dùng biết mình cảm thấy gì ("nhớ mẹ") nhưng lời ca dao dùng ngôn ngữ cổ nên tìm từ khóa không ra.

## 2. Định vị

- **Định vị:** nơi đọc, hiểu, trích dẫn và chia sẻ ca dao tục ngữ **dễ chịu nhất trên điện thoại**, và **trung thực nhất về nguồn gốc**.
- **Câu định vị công khai:** "Đọc một câu. Hiểu một đời sống."
- **Lời hứa (D-039):** **"Mỗi câu ghi rõ lấy từ đâu, và đã được kiểm đến mức nào."**
- **Ba trụ sản phẩm** (dùng làm trọng tài: mỗi tính năng phải phục vụ ít nhất một trụ):

| TIN | HIỂU | CẢM |
|---|---|---|
| Nguồn gốc từng bài, nhãn mức kiểm do máy tính, lịch sử sửa đổi | Hiểu trong 10 giây, chú giải chạm-là-hiện, dị bản, cách hiểu | Tình huống sống, chữ đẹp, chia sẻ, giọng người thật (sau) |

- **Tiêu chí duyệt giao diện:** Chuẩn · Sống · Đẹp.
- **Quan hệ với Ca dao Mẹ (cadao.me):** kho trực tuyến lớn nhất đã có (~16.500 mục, từ 2013) và là **nguồn đầu mối chính** của dự án (D-046). Dự án **không cạnh tranh về số lượng**; khác biệt nằm ở: nguồn gốc và nhãn từng bài, "Hiểu trong 10 giây", tìm theo tình huống, tục ngữ có nghĩa đen/bóng, trích dẫn và trình chiếu cho giáo viên, nội dung được lọc, lộ trình nâng nhãn bằng sách. Luôn ghi công và liên kết về Ca dao Mẹ.
- **Lợi thế bền vững:** không phải AI hay giao diện, mà là bộ dữ liệu có nguồn gốc rõ ràng, được nâng mức kiểm dần theo thời gian, cùng trải nghiệm đọc tốt. Website chỉ là một giao diện trên bộ dữ liệu đó.

## 3. Người dùng

**Phát hiện then chốt:** cả hai nhóm đều sợ sai — giáo viên sợ dạy sai, người trẻ sợ chia sẻ sai. Vì vậy nhãn mức kiểm và nguồn gốc xuất hiện ở mọi nơi: trang bài, kết quả tìm, ảnh xem trước, dòng trích dẫn.

| Persona | Mô tả | Thành công nghĩa là |
|---|---|---|
| **Cô Hạnh** (chính, nhóm A) | 38 tuổi, giáo viên Ngữ văn THCS ở thị xã; soạn giáo án buổi tối, tra nhanh trên điện thoại | Trong **dưới 1 phút** có văn bản + dòng trích dẫn ghi rõ nguồn gốc để dán + một chi tiết thú vị để kể trên lớp; chiếu được lên màn hình lớp |
| **Minh** (chính, nhóm B) | 24 tuổi, văn phòng, xa quê, đăng Story dịp Tết, Vu Lan; gõ không dấu | Trong **dưới 30 giây** tìm được câu ưng ý và chia sẻ qua Zalo/Facebook kèm nguồn |
| **Phụ huynh ở xa** (phụ) | Muốn dạy con câu ca dao, hiểu nghĩa từ khó | Hiểu nghĩa trong **10 giây** |
| **Người có sách** (người đóng góp) | Giáo viên, sinh viên có SGK hoặc tuyển tập | Gửi tên sách + số trang trong 2 phút để bài được đối chiếu |
| **Người ghi hộ** (người đóng góp) | Con cháu ghi lại lời ông bà | Gửi trên điện thoại **trong 5 phút**, kèm người kể, nơi, sự đồng ý |
| **Người sáng lập** (người biên soạn duy nhất) | Dưới 10 giờ/tuần, không dùng Git/YAML | Thêm, duyệt, công khai một bài từ điện thoại; biết hôm nay nên làm gì mà không dùng dòng lệnh |

## 4. Mười nguyên tắc trọng tài

1. **Trung thực hơn hoàn hảo (D-039).** Xuất bản sớm, nói thật mức độ hoàn thiện; cải tiến dần. Không chờ hoàn hảo mới ra mắt.
2. **Mọi câu ghi rõ nguồn gốc; mọi khẳng định nói rõ mức kiểm.** Không biết thì ghi "chưa rõ".
3. **Không có bản đúng duy nhất.** Mỗi tác phẩm có một **Bản tham chiếu** kèm lý do chọn.
4. **Lời giữ nguyên như nguồn.** Chuẩn hóa chỉ diễn ra trong chỉ mục tìm kiếm.
5. **AI để tìm và soạn nháp; người duyệt mới lên trang; AI không bao giờ là nguồn.**
6. **Tĩnh trước, động sau.**
7. **Điện thoại trước; tắt JavaScript vẫn đọc được.**
8. **Không quảng cáo, không theo dõi.**
9. **Dữ liệu mở, có phiên bản, trích dẫn được.**
10. **Mọi tính năng mới phải qua hai cửa:**
    - Bốn câu hỏi: có giúp tìm / hiểu / tin / bảo tồn tốt hơn không? Một người vận hành được không? Có thêm gánh kiểm duyệt không? Có làm dữ liệu tốt hơn không?
    - Câu hỏi bền vững: một người, khoảng 5 giờ/tuần, duy trì được 2 năm không?

**Ba lằn ranh (D-040)** — máy chặn, không ngoại lệ:
1. AI không bao giờ viết lời, dị bản, nguồn, số trang, vùng, tác giả.
2. Nhãn mức kiểm luôn khớp dữ liệu (máy tính, không nhập tay); bài công khai có ít nhất một nguồn gốc ghi rõ.
3. Dữ liệu cá nhân và ghi âm phải có đồng ý.

## 5. Phạm vi

**Trong phạm vi năm đầu:** ca dao và tục ngữ người Việt (trọng tâm); thành ngữ chọn lọc (có trong SGK hoặc hay bị nhầm với tục ngữ).
**Sau:** đồng dao (Q-007); vè, câu đố.
**Ngoài phạm vi:** văn học dân gian các dân tộc thiểu số (cần chuyên gia và sự đồng thuận của cộng đồng sở hữu); ký âm dân ca; truyện cổ tích; thơ hiện đại. "Ca dao mới", "ca dao chế", bài trào phúng hiện đại chỉ xuất hiện khi gắn cờ "Nghi không phải dân gian".

### Những thứ không làm

| Không làm | Lý do | Xem xét lại khi |
|---|---|---|
| Tài khoản người dùng, đăng nhập | Bảo mật, dữ liệu cá nhân, chi phí vận hành | Có ≥ 3 người vận hành |
| Bình luận, lượt thích, mạng xã hội | Kiểm duyệt nuốt hết thời gian một người | Không bao giờ ở dạng mở |
| Chatbot; AI tự sinh nội dung công khai | Một lỗi ảo giác phá niềm tin | Không bao giờ |
| Đọc ca dao bằng giọng máy (TTS) | Vô hồn, dễ bị nhầm là tư liệu | Không bao giờ |
| App iOS/Android | Gấp đôi công sức; web tốt trên điện thoại là đủ | Có nhu cầu ngoại tuyến rõ (thử trước bằng T-065) |
| **Xuất bản hàng loạt** | Thành thêm một trang sao chép; Google phạt cả tên miền | Không bao giờ. **Thu thập** toàn bộ lời vào kho thô riêng tư thì được; **xuất bản** chọn lọc, mỗi bài có giá trị riêng (D-045) |
| Chép chú thích, ảnh, cách phân loại của trang khác | Là công sức có bản quyền của người khác | Khi có văn bản cho phép (D-046) |
| Bản đồ tương tác | Tốn công, địa danh chưa chuẩn | Có ≥ 500 dị bản có vùng rõ |
| Điểm thưởng, huy hiệu, bảng xếp hạng | Lệch trọng tâm | Có đối tác giáo dục cần |
| Bản tiếng Anh | Dịch ca dao là việc của chuyên gia | Có cộng tác viên dịch thuật |
| Công cụ quản trị tự xây | CMS chạy trên Git + Bàn biên tập là đủ (D-036, D-047) | Có biên tập viên thứ hai thường xuyên |
| Quảng cáo, liên kết tiếp thị | Mâu thuẫn với lý do tồn tại | Không bao giờ |

## 6. MVP theo mốc (D-044)

**Nguyên tắc:** mô hình dữ liệu giàu, trải nghiệm đọc mặc định đơn giản. Không có cổng đếm hồ sơ; mỗi mốc là một trải nghiệm phải chạy được. Chi tiết task và điều kiện xong: `22` mục 1–2.

| Mốc | Tính năng |
|---|---|
| **A · Chạy được** | Khung site + CMS ("Thêm nhanh", duyệt nháp AI) · kho thô + đường ống ứng viên · trang tác phẩm đọc theo lớp với nhãn và nguồn gốc · tìm kiếm không dấu · trích dẫn + chia sẻ · sáng/tối tự động · bản in |
| **B · Thử nhỏ** | Tình huống + trang chủ · từ điển · form góp ý + "Gửi số trang" + thông báo quyền riêng tư · trang Phương pháp, Nguồn, Về dự án · Bàn biên tập · 30–50 bài, thử với 5 người |
| **C · Mở lặng lẽ** | Tên miền · ảnh xem trước từng bài · noindex tự động · Câu hôm nay, Vừa bổ sung, Đã lưu, Trình chiếu · đo lường 3 sự kiện · 100–150 bài |
| **Sau** | Thẻ chia sẻ dạng ảnh · lịch tình huống theo dịp · bộ bài cho tiết học · so sánh dị bản · tìm kiếm mở rộng sang kho thô · đọc ngoại tuyến · dữ liệu mở có DOI · ghi âm · tìm theo ý · cố vấn |

## 7. Lộ trình

Giả định khoảng 5 giờ/tuần của người sáng lập; mọi phần mã do agent làm. Mốc ngày là ước tính, không phải cam kết công khai.

| Mốc | Kết quả | Ước tính |
|---|---|---|
| Chuẩn bị | Tài liệu v2 trong repo; tài khoản GitHub, Cloudflare; repo riêng tư cho kho thô | Cuối 10/2026 |
| A · Chạy được | Người sáng lập thêm bài từ điện thoại, thấy trên bản xem trước; 5–10 bài; kho thô có dữ liệu | Cuối 11/2026 |
| B · Thử nhỏ | 30–50 bài; 5 người dùng thật đã thử | Giữa–cuối 12/2026 |
| C · Mở lặng lẽ | 100–150 bài công khai; mở không thông báo rộng | Trước Tết Đinh Mùi 2027 (đầu 02/2027) |
| Sau | Nhịp đều: nội dung hằng tuần, một tính năng mỗi tháng; bài tiếp theo chọn theo truy vấn không có kết quả | Từ 02/2027 |

## 8. Chỉ số

**North Star — Lượt dùng tin cậy mỗi tuần** = lượt sao chép trích dẫn (+ lượt chia sẻ/tải dữ liệu khi có tính năng).

| Nhóm | Chỉ số | Mục tiêu 6 tháng sau khi mở |
|---|---|---|
| Nội dung | Bài công khai | 300 |
| | Tỉ lệ bài có nhãn A/B (đã đối chiếu) | Tăng dần mỗi tháng |
| | Bài đang noindex (chưa có giá trị riêng) | Giảm dần |
| Khám phá | Lượt tìm không có kết quả | Giảm mỗi tháng; là danh sách bài cần làm tiếp |
| | Lượt mở chú giải | Theo dõi |
| Cộng đồng | "Gửi số trang" được duyệt | Theo dõi |
| | Giáo viên dùng thường xuyên | 10–20 |

**Không tối ưu:** lượt xem trang, thời gian ở lại, tổng số bài, số trang được index.

## 9. Rủi ro chính

| Rủi ro | Khả năng | Ảnh hưởng | Giảm thiểu |
|---|---|---|---|
| Người sáng lập kiệt sức | Cao | Rất cao | Không đọc mã; CMS trên điện thoại; Bàn biên tập; không hứa tiến độ công khai |
| Lời chép từ web bị sai chữ | Cao | Cao | Nhãn "chưa đối chiếu" trung thực; nút báo lỗi và "Gửi số trang"; ưu tiên đối chiếu bài được xem nhiều |
| Ảo giác AI lọt vào dữ liệu | TB | Cao | Ba lằn ranh; `NHAP_AI` chỉ lên trang khi người duyệt; lời không bao giờ qua AI |
| Bị coi là bản sao của Ca dao Mẹ | TB | Cao | Xuất bản chọn lọc; mỗi bài có giá trị riêng hoặc noindex; ghi công và liên kết; thư thông báo + xin phép (T-034) |
| Ca dao Mẹ phản đối việc dùng lời | Thấp–TB | TB | Thư sớm; lời là di sản dân gian; sẵn sàng gỡ và thay nguồn khác |
| Phụ thuộc một nguồn đầu mối | TB | TB | Kho thô ghi nguồn gốc từng bài; bổ sung luận văn, bản quét sách thuộc phạm vi công cộng |
| Vi phạm bản quyền chú thích | Thấp | Cao | Viết lại theo ý; chép nguyên văn chỉ khi được phép hoặc ≤ 15 từ (R20) |
| Dịch vụ miễn phí đổi chính sách | TB | TB | Trang tĩnh, chuyển nhà trong một buổi |

## 10. Bản đồ tài liệu

| Số | Tài liệu | Cần khi |
|---|---|---|
| 01 | Nhật ký quyết định + câu hỏi còn mở | Luôn |
| 02–03 | Trạng thái hiện tại, quy trình thực thi | Mỗi phiên làm việc |
| 10–12 | Dữ liệu, biên soạn, nguồn | Mọi việc chạm `content/` |
| 20–23 | Giao diện, kỹ thuật, backlog, kiểm thử | Mọi việc chạm mã |
| `luu-tru/` | Tài liệu v1.2 không còn dùng hằng ngày (cổng, beta, vận hành, đóng góp, ghi âm, cố vấn, trang công khai) | Khi task trỏ tới |
