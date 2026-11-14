# Đo đường tìm người đọc — dữ liệu kỹ thuật tổng hợp

14/11/2026. Mỗi truy vấn: một lượt làm nóng, trung vị 5 lượt; đối chiếu độc lập tổng, 20 bài đầu và trang cuối. CSDL cục bộ riêng, không phải site thật; số thời gian không thay thế đo trên máy chủ.

## T-203 · Tìm đã gửi trên tác phẩm tổng hợp (MySQL/MariaDB 8.4.3)
| Cỡ kho | Truy vấn | Kết quả trọn chữ | Trang cuối | Trung vị ms | SQL | MB tăng | Đúng oracle |
|---:|---|---:|---:|---:|---:|---:|---|
| 1000 | Một chữ cái (vừa gõ) | 0 | 1 | 14.4 | 1 | 0.66 | ĐÚNG |
| 1000 | Chữ phổ biến nhất, không dấu | 585 | 30 | 13.9 | 1 | 0.59 | ĐÚNG |
| 1000 | Chữ phổ biến, có dấu | 445 | 23 | 15.4 | 1 | 0.43 | ĐÚNG |
| 1000 | Hai chữ phổ biến | 328 | 17 | 10.3 | 1 | 0.33 | ĐÚNG |
| 1000 | Ba chữ của bài mới, không dấu | 72 | 4 | 13.8 | 2 | 0.25 | ĐÚNG |
| 1000 | Ba chữ của bài mới, có dấu | 53 | 3 | 24.8 | 2 | 0.22 | ĐÚNG |
| 1000 | Cả dòng của bài mới | 6 | 1 | 18.8 | 2 | 0.06 | ĐÚNG |
| 5000 | Một chữ cái (vừa gõ) | 0 | 1 | 64.2 | 1 | 3.20 | ĐÚNG |
| 5000 | Chữ phổ biến nhất, không dấu | 3013 | 151 | 61.7 | 1 | 2.83 | ĐÚNG |
| 5000 | Chữ phổ biến, có dấu | 2305 | 116 | 154.1 | 1 | 2.29 | ĐÚNG |
| 5000 | Hai chữ phổ biến | 1766 | 89 | 113.4 | 2 | 1.62 | ĐÚNG |
| 5000 | Ba chữ của bài mới, không dấu | 333 | 17 | 112.8 | 4 | 0.69 | ĐÚNG |
| 5000 | Ba chữ của bài mới, có dấu | 333 | 17 | 114.8 | 4 | 0.69 | ĐÚNG |
| 5000 | Cả dòng của bài mới | 80 | 4 | 83.5 | 2 | 0.38 | ĐÚNG |
| 10000 | Một chữ cái (vừa gõ) | 0 | 1 | 671.8 | 1 | 6.40 | ĐÚNG |
| 10000 | Chữ phổ biến nhất, không dấu | 6010 | 301 | 320.9 | 1 | 5.63 | ĐÚNG |
| 10000 | Chữ phổ biến, có dấu | 4602 | 231 | 299.1 | 1 | 4.56 | ĐÚNG |
| 10000 | Hai chữ phổ biến | 3510 | 176 | 677.1 | 2 | 3.21 | ĐÚNG |
| 10000 | Ba chữ của bài mới, không dấu | 280 | 14 | 281.2 | 4 | 0.76 | ĐÚNG |
| 10000 | Ba chữ của bài mới, có dấu | 279 | 14 | 329.9 | 4 | 0.76 | ĐÚNG |
| 10000 | Cả dòng của bài mới | 128 | 7 | 305.4 | 3 | 0.55 | ĐÚNG |
T-203: đường tìm người đọc ĐÚNG oracle ở mọi truy vấn và trang cuối.
