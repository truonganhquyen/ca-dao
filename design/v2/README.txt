PHONGDAO V2 — BỘ THIẾT KẾ UX/UI BẢN CHỐT 2.1 — 02/11/2026
Bản 1.0 (28/10/2026) đổi lớp nhìn theo D-092 (bảng tím – lục, bỏ "Đọc theo tâm trạng", thụt câu lục) và chốt theo D-093 (mỗi câu một dòng, Ý nghĩa, nhãn mức kiểm, Sao chép, Ảnh để chia sẻ, Khám phá là trang riêng).
Từ D-096 (05/11/2026), nhóm màn biên tập B00–B05 là mẫu tham khảo cho giao diện đang chạy D-094. Kế hoạch nâng cấp quản trị chính trong wp-admin ở docs/30; cần duyệt bản xem trước admin riêng trước khi chuyển lối vào, không coi nhóm B là đặc tả admin mới.

DỰNG VÀ MỞ
Chạy từ thư mục design/v2 (Node, không cần npm install):
  node source/build.mjs                 sinh dist/ và exports/PHONGDAO_V2_Prototype.html
  python -X utf8 source/package-handoff.py
                                        sinh đặc tả, token, no-JS, bản in, ảnh chia sẻ, qa/ và exports/PHONGDAO_V2_Handoff.html (cần lxml, Pillow)
  PD_EARLY=1 node source/build.mjs      bản tám màn ★ (sau đó dựng lại bản đủ)
dist/, exports/, social/ do máy sinh, không lưu vào repo.
Mở exports/PHONGDAO_V2_Prototype.html bằng Chrome/Edge/Firefox: chọn màn, trạng thái, 390 / 1440, sáng/tối, "So sáng / tối".

CHỈNH SỬA
source/data.mjs — registry, fixture, tên trạng thái
source/common.mjs — thành phần chung: khung site, thanh thể loại, lời, mức kiểm, cột công cụ, khối cuối bài
source/views-core.mjs — trang chủ, ca dao, tục ngữ, đồng dao, tuyển tập, tìm, Hôm nay, Duyệt nhanh, D01–D03
source/views-extra.mjs — phần còn lại R/A/F, D04
source/styles.css — token sáng/tối ở đầu tệp; lớp giao diện tím – lục ở cuối tệp
source/runtime.js, extra-runtime.js — tương tác mô phỏng
source/fit.js — mỗi câu một dòng: co cỡ chữ lời vừa khung (D-093); dùng lại được cho theme production
source/viewer.html — công cụ duyệt, chỉ dùng ở prototype
assets/ — phông WOFF tự lưu trữ (OFL), bản đồ SVG
brief/ — brief gốc của bản 1.0

GÓI TÀI LIỆU
tokens/ — CSS/JSON và bảng chuyển sang theme.json (không phải theme production).
docs/handoff.html — đặc tả, ánh xạ WordPress, giới hạn, lựa chọn.
docs/screen-registry.json, screen-specs.json, components.json, journeys.json, coverage.csv, review-decisions.csv, asset-credits.csv.
qa/ — kết quả kiểm tĩnh, tương phản, kích thước, tràn ngang; không phải chứng nhận WCAG/production.

RANH GIỚI
Không triển khai, không gửi form ra mạng, không ghi WordPress.
Role/capability trong prototype là mô phỏng; không coi là bảo mật. Không đưa bundle gồm admin lên site công khai.
Audio, scan, ảnh có quyền và corpus thật chưa được bàn giao. Mục đợt 5 chưa được bật cho ra mắt.
Chưa có thử người dùng, đo thời gian chính thức, in vật lý hoặc số đo field performance.
