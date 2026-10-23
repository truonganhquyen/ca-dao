# 13 · Danh sách ưu tiên nội dung

| | |
|---|---|
| Phiên bản | 1.0 · 04/10/2026 |
| Trạng thái | **Ổn định** (phương pháp). Danh sách thật được lập ở GĐ0 |
| Tệp làm việc | `data/danh-sach-uu-tien.csv` · `data/tinh-huong-khoi-dau.csv` |
| Đọc khi | Chọn bài để biên soạn; sắp xếp lại hằng tháng |

Câu hỏi tài liệu này trả lời: **biên soạn bài nào trước?** Với nhịp ~10 hồ sơ/tuần, thứ tự quyết định giá trị của từng giờ làm việc.

---

## 1. Bốn tầng ưu tiên cho 300 hồ sơ đầu

| Tầng | Nội dung | Cách xác định | Số lượng ước tính |
|---|---|---|---|
| **T1** | Ca dao, tục ngữ trong SGK Ngữ văn hiện hành (cả 3 bộ sách) | Rà mục lục và văn bản SGK các lớp | Kiểm đếm khi rà |
| **T2** | Những câu được tìm nhiều nhất | Gợi ý tự động của công cụ tìm kiếm, Google Trends; sau ra mắt: Search Console và truy vấn không có kết quả | ~100 |
| **T3** | Trọn chùm mô-típ tiêu biểu | "Thân em như…", "Chiều chiều…", "Công cha… nghĩa mẹ…", "Bầu ơi…" | ~50 |
| **T4** | Cân bằng vùng | Bổ sung để mỗi vùng văn hóa có đại diện | ~50 |

T1 trước vì giáo viên là kênh vào mạnh nhất và cũng là nhóm cần độ chính xác cao nhất.

## 2. Ràng buộc với trang tình huống

Ra mắt cần **20 trang tình huống, mỗi trang ≥ 3 tác phẩm đã xuất bản**, tức tối thiểu khoảng 60 lượt gắn. Khi chọn bài trong cùng một tầng, ưu tiên bài lấp đầy tình huống còn thiếu.

`data/tinh-huong-khoi-dau.csv` có 41 tình huống, 20 tình huống đánh dấu `ra_mat = co`. Khi tạo tệp, dùng `id = sit-<slug_de_xuat>` (ví dụ `sit-biet-on-cha-me`), để `config/lich-tinh-huong.yaml` trỏ đúng. Cột "Ví dụ gợi hướng" chỉ để gợi ý: mỗi bài vẫn phải qua quy trình biên soạn trước khi gắn.

Lưu ý: hai tình huống thuộc nhóm "Thân phận" (`ra_mat = khong`) chứa bài có thể cần `canh_bao_noi_dung`. Bài có cảnh báo không được gắn vào tình huống khuyên dùng (R11), nên các trang này cần thiết kế như trang **tìm hiểu**, không phải trang "dùng khi…". Quyết định hình thức trước khi mở chúng.

## 3. Cột của danh sách ưu tiên

| Cột | Ý nghĩa |
|---|---|
| `ma_du_kien` | Mã sẽ dùng (`cd-####`…); để trống tới khi bắt đầu biên soạn |
| `dong_dau` | Dòng đầu theo dạng phổ biến (chỉ để nhận diện, không phải dữ liệu) |
| `the_loai` | Mã thể loại |
| `tang` | `T1` · `T2` · `T3` · `T4` · `chua_xep` |
| `ly_do_uu_tien` | Một cụm ngắn: "SGK lớp 6", "tình huống Vu Lan", "mô-típ Thân em" |
| `nguon_du_kien` | Mã nguồn sẽ đối chiếu |
| `trang_thai` | `chua_bat_dau` · `nhap` · `cho_kiem_dinh` · `da_xuat_ban` · `tam_dung` |
| `phut_tim_nguon`, `phut_xac_minh`, `phut_nhap_lieu`, `phut_chu_giai`, `phut_qa` | Số phút thực tế theo từng phần (`11` mục 1); bắt buộc với 10 hồ sơ đầu, sau đó tùy chọn |
| `ghi_chu` | |

Danh sách mẫu hiện có 3 hồ sơ khung và 14 dòng gợi ý từ các bản ý tưởng, tất cả ở tầng `chua_xep`.

## 4. Nhịp cập nhật

- **Hằng tuần:** cập nhật `trang_thai` và các cột thời gian.
- **Hằng tháng:** sắp xếp lại theo truy vấn không có kết quả, tình huống còn thiếu bài, phản hồi của giáo viên beta.
- **Mỗi mốc 50 hồ sơ:** xem lại tỉ lệ tầng và cân bằng vùng. **Mốc 60:** rà từ vựng kiểm soát một lần (`10` mục 12).
