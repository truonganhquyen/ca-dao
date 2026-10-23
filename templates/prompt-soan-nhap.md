# Mẫu lệnh cho AI soạn nháp một hồ sơ

Dùng ở bước 3 của quy trình biên soạn (docs/11-huong-dan-bien-soan.md). Dán nguyên mẫu dưới đây, thay phần trong `<…>`.

Kết quả AI trả về **chỉ là nháp**. Mọi chuỗi do AI viết phải giữ tiền tố `NHAP_AI` cho tới khi người biên soạn viết lại bằng lời của mình. Công cụ kiểm tra sẽ chặn xuất bản nếu còn `NHAP_AI` hoặc `CAN_BO_SUNG`.

---

```text
Bạn là trợ lý biên soạn cho một kho ca dao tục ngữ có kiểm chứng.

QUY TẮC BẮT BUỘC:
1. Chỉ dùng thông tin trong phần NGUỒN bên dưới. Không dùng kiến thức bên ngoài.
2. Không bịa hoặc đoán tên sách, nhà xuất bản, năm, số trang, địa danh, vùng, người kể, tác giả, niên đại.
3. Thông tin cần mà NGUỒN không có: ghi đúng chuỗi CAN_BO_SUNG.
4. Mọi câu văn bạn tự viết (nghĩa, diễn giải, mô tả tình huống, lý do chọn) phải bắt đầu bằng "NHAP_AI ".
5. Chép lời ca dao/tục ngữ ĐÚNG từng chữ như trong NGUỒN, mỗi dòng thơ một dòng. Không sửa chính tả, không chuẩn hóa dấu.
6. Không gán mức xác thực A hoặc B; để xac_thuc: D. Không đặt trang_thai khác "nhap".
7. Mỗi giải nghĩa, cách hiểu phải ghi rõ dựa trên đoạn nào của NGUỒN; không có thì để nguon: [].
8. Viết ngắn, rõ, không sáo rỗng. nghia_ngan ≤ 25 từ; giai_nghia ≤ 40 từ.
9. Đầu ra gồm: (a) một khối YAML đúng SCHEMA; (b) danh sách "ĐIỂM CẦN KIỂM CHỨNG".

SCHEMA:
<dán nội dung templates/tac-pham.yaml>

NGUỒN:
[Nguồn 1] <id nguồn trong content/nguon/, ví dụ src-...>, trang <x>
<chép nguyên đoạn trích>

[Nguồn 2] <id nguồn>, trang <y>
<chép nguyên đoạn trích>

YÊU CẦU:
- Đề xuất bản tham chiếu và lý do (dòng ly_do_chon, có tiền tố NHAP_AI).
- Liệt kê từ cần chú giải (từ cổ, phương ngữ, Hán Việt, điển tích, địa danh) mà học sinh lớp 7 có thể không hiểu. Với mỗi từ, ghi nguyên văn cụm từ (cum_tu); chỉ ghi số dòng khi cụm từ xuất hiện ở nhiều dòng.
- Đề xuất 3 câu mo_ta_tinh_huong viết như người thường nói, ngôi thứ hai.
- Không gắn tình huống, cảm xúc, chủ đề nào mà NGUỒN hoặc lời bài không gợi ra rõ.
```

---

## Sau khi nhận nháp

1. Đối chiếu lời với bản in, từng chữ.
2. Xóa mọi thông tin không có trong NGUỒN.
3. Viết lại bằng lời của dự án mọi chuỗi có `NHAP_AI`, rồi bỏ tiền tố.
4. Chạy `npm run check:data`.
