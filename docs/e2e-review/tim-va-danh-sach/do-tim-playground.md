# Đo đường tìm người đọc — dữ liệu kỹ thuật tổng hợp

14/11/2026. Mỗi truy vấn: một lượt làm nóng, trung vị 5 lượt; đối chiếu độc lập tổng, 20 bài đầu và trang cuối. CSDL cục bộ riêng, không phải site thật; số thời gian không thay thế đo trên máy chủ.

## T-203 · Tìm đã gửi trên tác phẩm tổng hợp (SQLite/Playground)
| Cỡ kho | Truy vấn | Kết quả trọn chữ | Trang cuối | Trung vị ms | SQL | MB tăng | Đúng oracle |
|---:|---|---:|---:|---:|---:|---:|---|
| 1000 | Một chữ cái (vừa gõ) | 0 | 1 | 45.2 | 1 | 0.03 | ĐÚNG |
| 1000 | Chữ phổ biến nhất, không dấu | 585 | 30 | 40.4 | 1 | 0.15 | ĐÚNG |
| 1000 | Chữ phổ biến, có dấu | 445 | 23 | 45.9 | 1 | 0.09 | ĐÚNG |
| 1000 | Hai chữ phổ biến | 328 | 17 | 42.8 | 1 | 0.09 | ĐÚNG |
| 1000 | Ba chữ của bài mới, không dấu | 72 | 4 | 46.6 | 2 | 0.17 | ĐÚNG |
| 1000 | Ba chữ của bài mới, có dấu | 53 | 3 | 48.0 | 2 | 0.25 | ĐÚNG |
| 1000 | Cả dòng của bài mới | 6 | 1 | 45.6 | 2 | 0.65 | ĐÚNG |
| 5000 | Một chữ cái (vừa gõ) | 0 | 1 | 215.3 | 1 | 0.08 | ĐÚNG |
| 5000 | Chữ phổ biến nhất, không dấu | 3013 | 151 | 194.9 | 1 | 0.53 | ĐÚNG |
| 5000 | Chữ phổ biến, có dấu | 2305 | 116 | 225.5 | 1 | 0.54 | ĐÚNG |
| 5000 | Hai chữ phổ biến | 1766 | 89 | 446.2 | 2 | 1.90 | ĐÚNG |
| 5000 | Ba chữ của bài mới, không dấu | 333 | 17 | 294.8 | 4 | 0.49 | ĐÚNG |
| 5000 | Ba chữ của bài mới, có dấu | 333 | 17 | 256.6 | 4 | 0.49 | ĐÚNG |
| 5000 | Cả dòng của bài mới | 80 | 4 | 250.6 | 2 | 0.44 | ĐÚNG |
| 10000 | Một chữ cái (vừa gõ) | 0 | 1 | 532.3 | 1 | 0.14 | ĐÚNG |
| 10000 | Chữ phổ biến nhất, không dấu | 6010 | 301 | 417.1 | 1 | 1.05 | ĐÚNG |
| 10000 | Chữ phổ biến, có dấu | 4602 | 231 | 1460.4 | 1 | 1.05 | ĐÚNG |
| 10000 | Hai chữ phổ biến | 3510 | 176 | 2172.9 | 2 | 3.48 | ĐÚNG |
| 10000 | Ba chữ của bài mới, không dấu | 280 | 14 | 941.2 | 4 | 0.71 | ĐÚNG |
| 10000 | Ba chữ của bài mới, có dấu | 279 | 14 | 372.3 | 4 | 0.72 | ĐÚNG |
| 10000 | Cả dòng của bài mới | 128 | 7 | 338.8 | 3 | 0.73 | ĐÚNG |
T-203: đường tìm người đọc ĐÚNG oracle ở mọi truy vấn và trang cuối.
