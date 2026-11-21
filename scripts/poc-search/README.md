# Đối chiếu Typesense / Meilisearch — thử độc lập

Phạm vi người sáng lập yêu cầu: tiếp tục so sánh hai máy tìm. Chỉ công cụ thử, không tích hợp hay triển khai production. Nhánh PoC không tự gộp vào main. Không phụ thuộc PoC bảng quan hệ/hồ sơ thực thể đang chờ quyết định.

- Cùng runner Linux, cùng 1.004 record: ba phần lời đọc từ hai trang công khai đã chỉ định, còn lại là fixture kỹ thuật. Không có bản sao DB hay kho thô.
- Bộ core giữ 17 ca của lượt Windows (15 ca có bài đích, 2 ca âm); thêm 7 ca kỹ thuật chưa tinh chỉnh riêng. Cùng hai họ tác phẩm nên không gọi đây là hold-out đại diện toàn kho.
- Ghim Typesense 30.2 và Meilisearch 1.57.0; artifact có image ID/phiên bản và SHA256 bộ đầu vào để kiểm cùng dữ liệu.
- Ba profile: engine cơ bản; chuẩn hoá; adapter tìm trong lời có dấu/không dấu + thử bỏ một tiếng khi truy vấn 4–16 tiếng rồi đối chiếu toàn truy vấn với đoạn tốt nhất.
- Mỗi nhánh lấy tối đa 20 record; cùng điều kiện visibility=public trước giới hạn. Engine không quyết định gộp hay xuất bản.
- Chỉ xuất số đo và mã ca. Không lưu/in câu tìm, lời, chú thích hay index DB vào repo/log/artifact. Các URL nguồn là công khai; runtime chỉ GET.
- Container chỉ mở trên loopback runner, khoá thử sinh trong bộ nhớ; Meili tắt telemetry. Dọn đúng container có nhãn của lượt chạy.
- Thời gian đơn lẻ của mẫu nhỏ không phải benchmark tải. Kết quả không phải nghiệm thu tích hợp WordPress hay độ chính xác toàn kho.

Chạy trên Linux có Docker và Python 3.13: `python scripts/poc-search/compare.py`. Workflow chỉ tự chạy khi đẩy đúng nhánh PoC; không có job deploy, không cần production secret. Không sửa các luật hay schema nội dung.
Đo thêm snapshot docker stats sau khởi động, sau lập hai chỉ mục và sau truy vấn. Đây là các điểm lấy mẫu, không phải RAM đỉnh hay kiểm tải. Thời gian profile gồm tất cả lượt gọi API và đối chiếu; không so tốc độ engine qua một lời gọi đầu tiên riêng lẻ.
