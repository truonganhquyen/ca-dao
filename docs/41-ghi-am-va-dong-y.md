# 41 · Ghi âm và đồng ý

| | |
|---|---|
| Phiên bản | 1.1 · 05/10/2026 |
| Trạng thái | **Nháp.** Mẫu đồng ý ở mục 3 **cần người có chuyên môn pháp lý xem lại** trước khi dùng rộng rãi |
| Đọc khi | Trước bản ghi âm đầu tiên (T-209); trước chiến dịch (T-302) |

Ghi âm là lớp "CẢM" quý nhất của dự án và cũng nhạy cảm nhất: giọng nói, tên, nơi sống của người kể là **dữ liệu cá nhân**. Vì vậy: chỉ giọng người thật, luôn có đồng ý hai lớp, gỡ vô điều kiện khi được yêu cầu.

> Định hướng thực hành, không phải tư vấn pháp lý. Việt Nam có quy định về bảo vệ dữ liệu cá nhân (Nghị định 13/2023/NĐ-CP và luật chuyên ngành có hiệu lực từ năm 2026 — cần kiểm tra yêu cầu cụ thể trước khi thu thập).

---

## 1. Nguyên tắc

1. **Chỉ giọng người thật.** Không TTS, không giọng tổng hợp, không "nhịp mõ" (D-014).
2. **Đồng ý hai lớp:** người gửi (thường là người ghi hộ: con cháu, học sinh…) cấp phép cho bản ghi; **người được ghi âm** (hoặc người đại diện hợp pháp) đồng ý cho đăng.
   Người được ghi âm có thể chọn **chỉ dùng để xác minh, không phát công khai**: bản ghi vẫn giúp dị bản đạt mức B nhưng không có nút Nghe. Trường dữ liệu cho lựa chọn này (`cong_khai`) sẽ thêm trước bản ghi âm đầu tiên (T-209, `21` mục 14); cho tới lúc đó **không nhận** bản ghi chỉ-để-xác-minh.
3. **Thu thập tối thiểu:** không cần tên thật; thế hệ ("sinh khoảng 1940") và vùng là đủ.
4. **Gỡ vô điều kiện trong 7 ngày** khi người được ghi âm hoặc gia đình yêu cầu.
5. **Không phán xét giọng.** Không có giọng địa phương "đúng" hay "sai"; không sửa, không chế giễu.

## 2. Quy chuẩn kỹ thuật

| Hạng mục | Quy định |
|---|---|
| Thiết bị | Điện thoại là đủ; phòng yên tĩnh, cách miệng 20–30 cm |
| Độ dài | Mỗi bản ghi một bài, tối đa 3 phút |
| Định dạng nhận | Bất kỳ định dạng phổ biến; **giữ nguyên bản gốc** |
| Bản phát | MP3 hoặc AAC ~96–128 kbps, chuẩn hóa âm lượng, cắt khoảng lặng đầu/cuối |
| Lưu trữ | Bản gốc: Internet Archive + ổ riêng. Bản phát: Internet Archive (thêm R2 khi cần) |
| Gửi tệp | Kiểm giới hạn dung lượng của dịch vụ form; không đủ thì dùng thư mục tải lên riêng (P2: Worker + R2) |
| Bản chép lời | Chép đúng như nghe, kể cả khác bản in; lưu ở `ban_chep` |

**Duyệt âm thanh:** nghe hết; kiểm đúng bài, không tiếng ồn quá mức, có hồ sơ đồng ý. Ghi âm hợp lệ thường đưa dị bản tương ứng lên mức B.

**Quy trình:** nhận tệp → kiểm hồ sơ đồng ý → lưu đồng ý vào kho riêng tư, cấp `ma_dong_y` → ghi SHA-256 của tệp gốc vào bảng tính riêng tư → tải lên Internet Archive → tạo `content/ghi-am/au-####.yaml` (mẫu: `templates/ghi-am.yaml`) → nếu lời khác các dị bản đã có thì tạo dị bản mới mức B.

## 3. Mẫu văn bản đồng ý (nháp, chờ xem pháp lý)

> **Đồng ý chia sẻ bản ghi âm**
>
> Tôi, người được ghi âm (hoặc người đại diện hợp pháp), đồng ý để bản ghi âm giọng đọc/hát của tôi được đăng trên Kho tàng Ca dao Tục ngữ Việt Nam và chia sẻ theo giấy phép Creative Commons Ghi công 4.0 (CC BY 4.0).
>
> Tôi muốn được ghi là: ☐ Tên: ________ ☐ Bút danh: ________ ☐ Ẩn danh (chỉ ghi vùng và thế hệ)
>
> Vùng/quê: ________ Năm sinh (khoảng): ________
>
> Tôi hiểu rằng tôi hoặc gia đình có thể yêu cầu gỡ bản ghi âm bất cứ lúc nào bằng cách liên hệ địa chỉ trên trang "Về dự án"; yêu cầu sẽ được thực hiện trong 7 ngày.
>
> Người được ghi âm: ________ Người ghi âm: ________ Ngày: ________

Có thể thay bằng **lời đồng ý nói ở đầu tệp**: người được ghi âm tự nói rõ nội dung trên.

**Lưu hồ sơ đồng ý:** ảnh/bản quét hoặc đoạn âm thanh đồng ý lưu ở kho riêng tư (`30` mục 4), đặt tên theo `ma_dong_y`. **Không bao giờ** đưa vào kho công khai.

## 4. Gỡ bỏ

1. Nhận yêu cầu (email, form, tin nhắn) — không cần lý do.
2. Trong 7 ngày: xóa tệp `content/ghi-am/au-####.yaml`, gỡ tệp trên Internet Archive và R2; nếu dị bản chỉ dựa vào bản ghi này để đạt mức B thì hạ về D.
3. Ghi vào bảng tính riêng tư: ngày nhận, ngày gỡ.
4. Trả lời người yêu cầu.

## 5. Chiến dịch "Giọng bà, giọng mẹ" (P2)

**Thời điểm:** dịp người trẻ về quê — thử nghiệm nhỏ dịp **Tết 2027** (10–20 bản từ người quen, T-209); chiến dịch công khai dịp **Vu Lan 2027** (T-302).

**Lời kêu gọi:** ghi lại giọng ông bà, cha mẹ hát ru hoặc đọc ca dao bằng giọng quê, trước khi những giọng ấy mất đi.

**Tài liệu chiến dịch:**
- Hướng dẫn 1 trang (mục 6).
- Mẫu bài đăng mạng xã hội.
- Thẻ cảm ơn: "Giọng của [tên/ẩn danh], [vùng]" kèm đường dẫn tới bài.

**Chỉ số:** ghi âm hợp lệ (mục tiêu 20 sau chiến dịch đầu); tỉ lệ có hồ sơ đồng ý = 100%.

## 6. Hướng dẫn 1 trang cho người ghi âm (nháp)

> **Ghi lại giọng bà, giọng mẹ**
>
> 1. **Hỏi trước.** Nói với ông bà, cha mẹ bạn muốn ghi lại câu hát ru / câu ca dao để lưu giữ, và có thể được đăng công khai. Chỉ ghi khi họ đồng ý.
> 2. **Chọn chỗ yên tĩnh.** Tắt tivi, quạt. Đặt điện thoại cách miệng một gang tay.
> 3. **Ghi lời đồng ý trước.** Ví dụ: "Tôi là [tên hoặc 'bà nội'], quê ở [nơi], đồng ý cho đăng bản ghi này."
> 4. **Mỗi bài một tệp,** không quá 3 phút. Hát/đọc tự nhiên, không cần "chuẩn".
> 5. **Gửi** qua [đường dẫn], kèm: quê ở đâu, năm sinh khoảng bao nhiêu, muốn ghi tên hay ẩn danh.
>
> Bạn hoặc gia đình có thể yêu cầu gỡ bất cứ lúc nào.
