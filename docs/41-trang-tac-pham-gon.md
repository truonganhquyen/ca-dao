# Trang tác phẩm gọn — D-119

Người sáng lập duyệt bản xem trước lần 2 ngày 16/11/2026 (bản tĩnh dựng từ trang thật, lưu ngoài kho mã tại `output/xem-truoc-trang-tac-pham/`) và chốt làm theo. Tài liệu này gom mọi điều đã chốt từ ba vòng rà soát (trang tục ngữ; trang tác phẩm năm thể loại; 271 trang và 16 trang người đọc khác) thành năm việc, làm lần lượt, mỗi việc một PR có bản xem trước.

## 1. Vì sao

Đo trên site thật ở 390 px ngày 16/11/2026:

- Trang bài dài 3.000–6.100 px cho bài 15–60 chữ; lời chỉ chiếm 2–9% chiều dài.
- 84% bài có tiêu đề trùng dòng đầu của lời; tục ngữ ngắn có khung lời trống (chỉ còn nút Sao chép và nhãn).
- Trang chưa có giải nghĩa có 5–6 lời mời góp; "Chia sẻ cách hiểu" hiện hai lần ở 40% trang.
- Từ khó dài gấp 10–40 lần lời ở nhiều bài (Hát bội Quy Nhơn: 17 chữ lời, khoảng 700 chữ chú thích); giai thoại, điển tích nằm trong Từ khó.
- Nhãn mức kiểm, nút ⓘ và khối "Lời này từ đâu?" nói cùng một chuyện ở ba chỗ.
- 53% thẻ Đọc tiếp có lý do "cùng thể loại"; 65% trang chỉ có loại thẻ này.
- Tục ngữ có bốn ô nghĩa ở màn nhập (Ý nghĩa, Nghĩa đen, Nghĩa bóng, Dùng khi); trong mẫu 64 câu chỉ 3, 4, 2 câu dùng ba ô sau. Màn Sửa tác phẩm có 12–14 mục cho mỗi bài.

Bản xem trước lần 2 ngắn hơn 32–63% mà không bỏ nội dung (phần dài thu gọn, bấm là mở).

## 2. Điều đã chốt

1. **Khung lời**: nhãn thể loại trong khung (chỉ giữ thể thơ có nghĩa với người đọc: Lục bát, Song thất lục bát), viền trên mang màu thể loại. Bài ngắn — tục ngữ câu ngắn, ca dao và câu đố từ 4 dòng trở xuống — lấy lời làm tiêu đề trang (`h1`), không in tên riêng. Bài dài giữ tên rồi tới lời; số dòng chỉ hiện từ 8 dòng.
2. **Chú thích kiểu sách** thay khối Từ khó trên trang người đọc: nằm ngay dưới lời trong khung lời; số nhỏ trên từ khớp số chú thích; khi có JavaScript mỗi mục gọn hai dòng kèm "thêm", quá bốn mục thì phần sau thu vào "Xem thêm N chú thích"; tắt JavaScript hiện đủ. Bài chia đoạn có nhiều chú thích: chú thích ngay sau từng đoạn, thu gọn. Chạm vào từ trong lời vẫn mở định nghĩa đầy đủ.
3. **Một dòng nguồn** ở chân khung lời: trạng thái ("Bản lưu truyền phổ biến · chưa đối chiếu sách in" hoặc "Đã đối chiếu · sách, trang") bên trái, "Nguồn" bên phải; mở ra là giải thích, bảng nguồn (lời, ý nghĩa, chú thích) và "Có sách? Gửi số trang". Thay nhãn + ⓘ + khối "Lời này từ đâu?".
4. **Sao chép** vào chung thanh công cụ (sau Trích dẫn); khung lời không còn nút riêng.
5. **Hiểu bài ngay sau khung lời**, trước thanh công cụ: Ý nghĩa (quá 8 dòng thì thu gọn "Đọc hết"); tục ngữ một khối **Nghĩa** (Nghĩa đen khi có, Ý nghĩa, Nghĩa bóng và Dùng khi của bài cũ vẫn hiện); đồng dao **Cách chơi** khi bài gắn trò chơi có cách chơi; vè **Bối cảnh** và **Ý nghĩa** trước lời dài. Câu tục ngữ hai vế hiện một dòng; lời đã lưu giữ nguyên.
6. **Một lời mời góp** theo thể loại ở cuối trang. Bài chưa có nghĩa: một dòng "Chưa có giải nghĩa · Góp cách hiểu". Khối Hiểu thêm chỉ hiện khi đã có bài của bạn đọc; câu đố không có Hiểu thêm. Bỏ viên "Nơi lưu truyền" khi chưa có vùng.
7. **Phần phụ thành hàng mở một dòng**: Chuyện quanh bài (tục ngữ gọi Nguồn gốc), Dị bản, Câu gần nghĩa · trái nghĩa, Hình ảnh trong bài, Ảnh để chia sẻ.
8. **Đọc tiếp** tối đa 3 dòng gọn, lý do thật; bài cùng tuyển tập, dị bản, cùng mô-típ trước; không lấp "cùng thể loại". Câu đố: nút **Đố tiếp** và "Cả bộ". Thẻ danh sách không in lại dòng trùng tên. Nhãn mô-típ hiện ở trang bài.
9. **Nhập liệu**: tục ngữ nhập Ý nghĩa và Nghĩa đen (tuỳ chọn); ô Nghĩa bóng, Dùng khi chỉ hiện ở bài cũ đã có. Màn Sửa tác phẩm gọn (Lời · Nghĩa · Phân loại, phần khác gom lại). Thẻ **Đăng nhanh** ở Bài mới. Máy kiểm lỗi trình bày khi lưu (nhắc lại câu, thiếu dấu, quá dài, viết hoa giữa câu) — chỉ nhắc, không chặn. Bổ sung có thẻ **Cần sửa**.

**Lằn ranh giữ nguyên:** máy và AI không viết, không sửa lời hay nguồn; không tự đăng, không tự bật cờ; mọi nháp chờ người duyệt. Câu tục ngữ hai vế chỉ đổi cách hiện. Máy không tự chuyển chữ giữa các phần.

## 3. Năm việc

| Việc | Nội dung | Phụ thuộc |
|---|---|---|
| T-212 | Khung lời mới cho người đọc: mục 1–5 của mục 2 (khung, chú thích kiểu sách, dòng nguồn, Sao chép vào thanh công cụ, hiểu bài ngay sau khung lời, hai vế một dòng, một dòng thay hộp mời góp) | — |
| T-213 | Một lời mời góp và hàng mở: mục 6–7 (Hiểu thêm khi có bài, câu đố bỏ Hiểu thêm, góp cuối theo thể loại, bỏ viên vùng trống, Dị bản / Hình ảnh / Ảnh chia sẻ thành hàng mở, Cách chơi đồng dao từ dữ liệu trò chơi, Bối cảnh vè) | T-212 |
| T-214 | Đọc tiếp gọn và lối vào: mục 8 (Đọc tiếp 3 dòng không lấp, cùng tuyển tập trước, Đố tiếp, thẻ danh sách không lặp, nhãn mô-típ) và lối vào trang chủ khớp nội dung thật | T-213 |
| T-215 | Phần Chuyện quanh bài và Câu gần nghĩa: phần dữ liệu mới `chuyen_quanh` (giai thoại, điển tích; người biên soạn chuyển từ chú thích), nhập và hiện Câu gần nghĩa · trái nghĩa (phần `gan_nghia` có sẵn); chế độ sửa trên trang đọc theo bố cục mới | T-214 |
| T-216 | Quản trị gọn: tục ngữ nhập Ý nghĩa + Nghĩa đen; Sửa tác phẩm gọn; Đăng nhanh; kiểm lỗi trình bày khi lưu; Bổ sung → Cần sửa | T-215 |

Thứ tự: làm trang người đọc trước (người đọc thấy ngay, không đổi dữ liệu), rồi dữ liệu mới, rồi màn nhập. Trong lúc T-212–T-214 chưa xong, chế độ sửa trên trang đọc và bài có từ ngữ thô tục giữ bố cục cũ; T-215 chuyển nốt.

## 4. Nghiệm thu chung

- 320 / 390 / 1440 px, sáng / tối, bật / tắt JavaScript: không tràn ngang, đọc được đủ nội dung khi tắt JavaScript; vùng chạm 44 px; bàn phím và nhãn cho trình đọc màn hình.
- `test:v2`, `quet:v2` (WCAG 2.2 AA, ngân sách trang bài ≤ 200 KB, JavaScript ≤ 21 KB nén), `e2e:v2` đạt; kiểm thử cũ về khối cũ được thay bằng kiểm thử bố cục mới, không bỏ trống.
- Đo lại chiều dài trang trên 7 trang mẫu (Chàng về Hồ, Ai làm anh phải xa em, Nằm gai nếm mật, Một chữ nên thầy, Chi chi vít vít, Vè cô Giang, Cái gì trong trắng ngoài xanh) sau T-214; mục tiêu ngắn hơn ít nhất 30% so với số đo ngày 16/11.
- Chỉ tiêu nội dung theo dõi tiếp (kế hoạch toàn diện): thẻ Đọc tiếp "cùng thể loại" dưới 15%; không trang nào mời góp hai lần; đăng một bài không cần sửa trong 3 thao tác.

## 5. Không làm trong đợt này

- Viết hay sửa nội dung thật (Ý nghĩa, chú thích, cách chơi, bối cảnh) — việc của người biên soạn theo quy tắc viết.
- Gọi dịch vụ AI từ máy chủ (chờ người sáng lập quyết).
- Đổi trang chủ ngoài phần lối vào; tuyển tập nổi bật; ngưỡng công khai tuyển tập; mở Google.

## 6. Còn chờ người sáng lập (không chặn)

- Plugin tự gọi AI soạn nháp: tài khoản, chi phí, gửi lời ra dịch vụ AI.
- Bộ "Các bài ca dao thô tục" có nằm ở Tuyển tập nổi bật trang chủ không.
- Ngưỡng công khai tuyển tập (8 câu hay 5 câu).
- Mục tiêu số bài có Ý nghĩa trước khi mở Google.
