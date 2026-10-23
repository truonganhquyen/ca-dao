# 24 · Kế hoạch preview và beta

| | |
|---|---|
| Phiên bản | 1.1 · 05/10/2026 |
| Trạng thái | **Ổn định** |
| Thời điểm | Preview kín ở 15–20 hồ sơ (T-119); beta khả dụng ở 25–30 hồ sơ (T-120). Mốc ngày: `00` mục 7 |
| Đọc khi | Chuẩn bị mời người thử; trước khi quyết định qua Cổng 2 |

Mục tiêu: **biết sớm người dùng thật có làm được việc của họ không**, khi sửa còn rẻ. Không hỏi "bạn có thích không"; quan sát xem họ có tìm được, hiểu được, nhận ra độ chắc chắn, tìm được nguồn và trích dẫn được không.

---

## 1. Hai vòng thử

| | Preview kín (T-119) | Beta khả dụng (T-120) |
|---|---|---|
| Khi | 15–20 hồ sơ đã xuất bản, P0-Core chạy được | 25–30 hồ sơ, lỗi của preview đã sửa |
| Ai | 2–3 giáo viên Ngữ văn · 2–3 người trẻ không chuyên văn · 1 người am hiểu văn hóa dân gian | 10–20 giáo viên · 5–10 người trẻ · 2–3 người ghi hộ (cháu ghi lời ông bà) |
| Cách | Ngồi cạnh hoặc gọi video, quan sát từng nhiệm vụ | Gửi đường dẫn + nhiệm vụ, người thử tự làm, điền phiếu; quan sát trực tiếp 3–5 người |
| Thời gian | 1 tuần | 2–3 tuần |
| Kết quả | Danh sách lỗi chặn → task sửa | Quyết định qua **Cổng 2** |

Không công khai đường dẫn; gắn `noindex` cho toàn site trong cả hai vòng. Người am hiểu văn hóa dân gian trong preview đọc kỹ 5–10 hồ sơ để bắt sai sót nội dung, không chỉ thử giao diện.

## 2. Nhiệm vụ thử

Giao từng nhiệm vụ, không hướng dẫn cách làm, bấm giờ, ghi chỗ người thử dừng lại hay hỏi.

| # | Nhiệm vụ | Ai | Thành công khi | Thời gian mục tiêu |
|---|---|---|---|---|
| N1 | "Bạn cần đưa câu [dòng đầu một bài có trong kho] vào đề kiểm tra. Lấy lời và dòng nguồn để dán vào giáo án." | Giáo viên | Dán được trích dẫn đúng | < 1 phút |
| N2 | "Học sinh hỏi: từ [từ có chú giải] nghĩa là gì?" | Giáo viên | Mở được chú giải, đọc được nguồn | < 20 giây |
| N3 | "Bài này đáng tin đến mức nào? Lời này lấy từ đâu?" | Mọi người | Nói đúng ý nghĩa nhãn bằng chứng và chỉ ra nguồn | < 30 giây |
| N4 | "Bài này có cách nói khác không? Ở vùng nào?" | Mọi người | Mở được danh sách dị bản, đọc được vùng | < 30 giây |
| N5 | "Bạn đang nhớ nhà. Tìm một câu nói đúng tâm trạng." | Người trẻ | Mở được một bài mà họ thấy hợp | < 1 phút |
| N6 | "Bà bạn nói câu này khác. Hãy gửi cách bà nói cho dự án." | Người ghi hộ | Gửi xong form trên điện thoại, có người kể và nơi | < 5 phút |
| N7 | (Chỉ beta, khi có P0.5) "Sắp tới Vu Lan, bạn muốn đăng một câu ca dao về cha mẹ lên Story." | Người trẻ | Có ảnh thẻ chia sẻ | < 30 giây |

Preview dùng N1–N6; beta dùng N1–N6 (+ N7 nếu thẻ chia sẻ đã có).

## 3. Phiếu phản hồi (sau khi làm nhiệm vụ)

1. Bạn có tin lời ca dao trên trang này không? Vì sao? (thang 1–5 + một câu)
2. Nhãn bằng chứng (ví dụ "Đã đối chiếu tư liệu") có giúp bạn tin hơn không? Bạn hiểu nó nghĩa là gì?
3. Có chỗ nào bạn thấy rối, chậm hoặc khó đọc trên điện thoại?
4. (Giáo viên) Bạn có dùng trang này khi soạn bài không? Còn thiếu gì để bạn dùng?
5. (Người trẻ) Bạn có muốn chia sẻ một câu từ trang này không? Nếu không, vì sao?
6. Lục bát trình bày như hiện tại dễ đọc không, hay căn giữa dễ đọc hơn? (cho xem hai bản; dùng để chốt Q-006)
7. (Người am hiểu văn hóa dân gian) Bạn thấy sai sót nội dung nào? Phần giải nghĩa nào chưa thỏa đáng?

## 4. Ghi nhận

- Một bảng tính: người thử (mã, không tên), nhiệm vụ, thời gian, thành công/không, chỗ vướng, trích lời người thử.
- Lỗi phát hiện được → task mới trong `22`, đánh dấu `preview` hoặc `beta`.
- Truy vấn người thử gõ mà không ra kết quả → thêm vào `data/truy-van-thu.csv`.
- Sai sót nội dung → xử lý như báo lỗi (`40` mục 3), ưu tiên trước mọi việc khác.

## 5. Mẫu tin nhắn mời

> Chào [tên], mình đang làm một trang ca dao tục ngữ phi lợi nhuận, không quảng cáo, mỗi câu đều có nguồn sách và giải nghĩa từ khó. Trước khi mở cho mọi người, mình muốn nhờ [cô/anh/chị/bạn] dùng thử khoảng 15 phút trên điện thoại và cho mình vài nhận xét thẳng thắn. Nếu được, mình gửi đường dẫn và vài việc nhỏ cần thử nhé. Cảm ơn nhiều!

## 6. Tiêu chí qua Cổng 2 (đánh giá ở beta khả dụng)

- [ ] ≥ 80% giáo viên hoàn thành N1 trong < 1 phút.
- [ ] ≥ 80% người thử hoàn thành N3 và N4.
- [ ] ≥ 80% người trẻ hoàn thành N5 trong < 1 phút.
- [ ] ≥ 2/3 người ghi hộ hoàn thành N6.
- [ ] Điểm tin cậy trung bình (câu hỏi 1) ≥ 4/5.
- [ ] Không còn lỗi nào chặn N1–N6; sai sót nội dung từ preview đã sửa.
- [ ] Đạt "P0-Core đã xong" ở `23` mục 7.
- [ ] Q-006 đã chốt.

Chưa đạt thì sửa và thử lại với người mới; không mở rộng lên 60 hồ sơ khi trang tác phẩm còn vấn đề cơ bản.
