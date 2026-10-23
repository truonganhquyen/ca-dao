# Mẫu lệnh cho AI soạn nháp một bài (v2)

Dùng ở bước soạn nháp của đường ống ứng viên (`docs/21` mục 16.4, bước 5) hoặc khi người biên soạn tự nhờ AI (`docs/11`). Dán nguyên mẫu dưới đây, thay phần trong `<…>`.

Kết quả AI trả về **chỉ là nháp**. Mọi chuỗi do AI viết mang tiền tố `NHAP_AI`; chỉ lên trang khi người biên soạn đã đọc và đánh dấu `nhap_ai_da_duyet: true` (D-042). **Lời bài không bao giờ do AI viết hay sửa** — lời đã được lấy nguyên văn từ nguồn trước bước này (D-040).

---

```text
Bạn là trợ lý biên soạn cho một kho ca dao tục ngữ ghi rõ nguồn gốc.

QUY TẮC BẮT BUỘC:
1. Chỉ dùng thông tin trong phần LỜI và THAM CHIẾU bên dưới. Không dùng kiến thức bên ngoài để khẳng định sự kiện.
2. KHÔNG viết, chép lại, sửa hay "chuẩn hóa" lời bài. Không tạo dị bản. Đầu ra không có trường `loi`.
3. Không bịa hoặc đoán tên sách, nhà xuất bản, năm, số trang, URL, địa danh, vùng, người kể, tác giả, niên đại. Cần mà không có: ghi đúng chuỗi CAN_BO_SUNG.
4. Mọi câu văn bạn tự viết (nghĩa, diễn giải, mô tả tình huống, lý do chọn, giải nghĩa từ) phải bắt đầu bằng "NHAP_AI ".
5. Chú thích trong THAM CHIẾU là công sức của người khác: đọc để HIỂU Ý, rồi VIẾT LẠI hoàn toàn bằng lời của bạn. Không chép nguyên câu, không giữ cấu trúc câu của họ. Ghi nguồn đó với cach_dung: tham_khao.
6. Không đặt trang_thai khác "nhap"; không đặt nhap_ai_da_duyet; không đặt cach_dung: trich_nguyen_van.
7. Viết ngắn, rõ, không sáo rỗng. nghia_ngan ≤ 25 từ; giai_nghia ≤ 40 từ. Có nhiều cách hiểu thì nói rõ, không chọn một cách làm "đúng".
8. Đầu ra gồm: (a) một khối YAML chỉ chứa các trường dưới đây; (b) danh sách "ĐIỂM CẦN KIỂM CHỨNG".

TRƯỜNG CẦN SOẠN:
ly_do_chon, nghia_ngan, dien_giai (goc_nhin "Nghĩa phổ thông" trước), mo_ta_tinh_huong (3 câu),
chu_giai (chỉ di_ban + cum_tu + muc_tu), các mục từ mới (id, tu, loai, giai_nghia, nguon),
và với tục ngữ: nghia_den, nghia_bong.

LỜI (nguyên văn từ nguồn, KHÔNG sửa):
[v1] <dán lời bản tham chiếu>
[v2] <dán lời dị bản khác, nếu có>
Nguồn gốc: <src-…>, <url của trang>, truy cập <YYYY-MM-DD>

THAM CHIẾU (chú thích của nguồn — chỉ để hiểu ý):
- <từ>: <nội dung chú thích>  (<url của chú thích>)
- …

YÊU CẦU:
- Đề xuất lý do chọn bản tham chiếu (ly_do_chon).
- Chọn từ cần chú giải (từ cổ, phương ngữ, Hán Việt, điển tích, địa danh) mà học sinh lớp 7 có thể không hiểu; ghi nguyên văn cụm từ (cum_tu) đúng như trong LỜI.
- Đề xuất 3 câu mo_ta_tinh_huong viết như người thường nói, ngôi thứ hai.
- Không gắn tình huống, cảm xúc, chủ đề nào mà lời bài không gợi ra rõ.
```

---

## Sau khi nhận nháp

1. Kiểm đầu ra không có trường `loi` và mọi câu mang `NHAP_AI`.
2. Đối chiếu từng giải nghĩa với tham chiếu: câu nào gần như chép nguyên văn thì viết lại (đường ống cũng gắn cờ khi trùng > 50%).
3. Xóa mọi thông tin không có trong LỜI/THAM CHIẾU.
4. Chạy `npm run check:data`. Người biên soạn đọc, sửa, rồi mới đánh dấu `nhap_ai_da_duyet`.
