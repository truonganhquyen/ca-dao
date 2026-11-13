# Bàn giao cải tiến biên tập và ảnh minh hoạ

Ngày 13/11/2026 · theo phạm vi người sáng lập chốt ở [kế hoạch](33-ke-hoach-cai-tien-bien-tap-va-anh.md).

**Phần mềm plugin 1.0.57/theme 1.0.6 đã qua bốn cổng; pilot nội dung thật còn chờ người quyết.** Đây là báo cáo phần mềm và QA mẫu; kết quả dùng thử người thật ghi vào phiếu riêng.

## 1. Thay đổi và lợi ích

| Phần | Hành vi sau sửa | Lợi ích thực tế cần dùng |
|---|---|---|
| Giữ chữ | Lỗi nhập dựng lại đúng dữ liệu; các phần chưa lưu giữ theo tab/phiên tối đa 30 phút, có cảnh báo rời bài | Lấy lại công đang viết sau lỗi hoặc sau khi lưu phần khác |
| Bản mở cũ | Lưu phần bắt buộc gửi phiên, đọc/so/ghi trong khóa chung; xung đột giữ chữ và cho đối chiếu | Người biên soạn chủ động quyết bản sẽ dùng |
| Ảnh minh hoạ | Một ảnh tuỳ chọn cho cả năm thể loại; chọn/tải, xem trước, lưu, khách thấy, thay/bỏ/khôi phục; ghi công theo attachment | Hoàn tất tác vụ tệp ảnh bị thiếu trước đây; phân biệt với ô chữ Hình ảnh trong bài |
| Nhãn và Hiểu thêm | Giải thích đáp án đúng tên; báo phần đã lưu và trạng thái bài; form cách hiểu mở theo nhu cầu, giữ consent/duyệt/no-JS | Dễ biết kết quả thao tác, bớt cuộn qua form trống |
| Khôi phục và duyệt nháp | Dùng cùng khóa bài trong giao dịch; lỗi ghi rollback; caller đang có giao dịch không mở COMMIT lồng | Giữ bản mới và bản chữ tổng hợp khi thao tác gần đồng thời |

Không đặt ảnh/nguồn/nghĩa thành điều kiện bắt buộc để đăng. Bốn hạng mục người sáng lập đã loại giữ nguyên: parser và nhãn Video; token nguồn; mở nguồn văn bản/nguồn cờ/xem trước sách; restore hoặc census DB/ảnh. Không đổi Google, quyền nguồn, dữ liệu văn hoá thật hoặc cấu hình máy chủ.

## 2. Bằng chứng phần mềm

| Bản sửa | Bằng chứng kiểm thử | Trạng thái review |
|---|---|---|
| Giữ chữ | 255 PHP + 523 WP + 55 chuyển đổi; quét 0 lỗi; E2E 47/0/0 | [Đã gộp #165](https://github.com/truonganhquyen/ca-dao/pull/165) |
| Chặn tab cũ | 255 PHP + 529 WP + 55 chuyển đổi; quét 0 lỗi; E2E 48/0/0 | [Đã gộp #166](https://github.com/truonganhquyen/ca-dao/pull/166) |
| Ảnh trọn luồng | 255 PHP + 541 WP + 55 chuyển đổi; quét 0 lỗi; E2E 49/0/0. CI cuối kiểm thêm lỗi đọc attachment | [Đã gộp #167](https://github.com/truonganhquyen/ca-dao/pull/167) |
| Nhãn, form và phiếu pilot | Local 255 PHP + 545 WP + 55 chuyển đổi, quét 0 lỗi; CI E2E 50/0/0 | [Đã gộp #168](https://github.com/truonganhquyen/ca-dao/pull/168) |
| Khóa khôi phục/duyệt | Local 255 PHP + 550 WP + 55 chuyển đổi; quét 0 lỗi; CI E2E 50/0/0. Hai ca hai tiến trình MySQL đạt sau sửa | [Bản chốt #169](https://github.com/truonganhquyen/ca-dao/pull/169) |

E2E ghi theo thứ tự đạt/lỗi/bỏ qua. Bộ chuyển đổi 55 ở bảng là kết quả local có log, không suy số riêng của nó từ nhãn xanh CI. Không hạ cổng hoặc bỏ các ca đang bắt buộc.

Đã xem ảnh phần Hiểu thêm ở 390/1440 px, có/không JavaScript. Bản mẫu 390 px có JavaScript: khối cao 343 px khi gập form và 1.132 px khi mở; bàn phím mở được và còn đủ hai consent. Số đo/ảnh ở `T-198/reader-final-views.json` và các ảnh cùng tên; chưa phải số đo năng suất người biên soạn.

Các ca ghi dùng dữ liệu [UX-TEST] tại WordPress 7.1.4/MySQL 8.4 local hoặc Playground. Hai tiến trình PHP với hai kết nối DB là bằng chứng thao tác ghi, bổ sung cho ca trình duyệt hai tab/tài khoản.

Hai lỗi phát hiện khi chốt caller đã tái hiện trước sửa: khôi phục cũ ghi đè A vừa lưu; duyệt nháp B sau A sửa phần lời làm bản chữ tổng hợp thiếu phần của A dù meta còn. Sau sửa: khôi phục cũ bị từ chối, A còn nguyên; duyệt B và sửa A khác phần giữ cả hai phần trong meta và bản chữ. Đây là kiểm môi trường thử, không phải kiểm DB production.

Browser ảnh đã kiểm chọn ảnh có sẵn, tải ảnh mới, alt/chú thích/ghi công tới khách, nháp/đã đăng, thay/bỏ/khôi phục, lỗi đọc attachment giữ ID, ảnh câu đố đóng/mở và no-JS. Runtime local báo giới hạn 2 MB: JPEG hỏng và PNG 3 MB bị chặn. Chưa đo giới hạn upload production. Mẫu ảnh kỹ thuật 960×540; trang bài mẫu có ảnh khoảng 157–161 KB, JavaScript khách 9.5–9.6 KB trong ngân sách 200/21 KB; không suy ra mọi ảnh lớn đều đạt.

Bằng chứng local ở 'docs/audits/2026-11-13-implementation/': log từng cổng trong T-194..T-198, JSON/browser và ảnh năm thể loại 390/1440 no-JS. Thư mục này được ignore và chỉ có trong workspace; review từ xa dùng log/ảnh artifact của các lượt CI trong PR. Không đưa kho thô hoặc thông tin tài khoản thật vào Git.

## 3. Mười URL QA công khai

Thu bằng trình duyệt khách lúc 2026-11-12T19:51:52.250Z; chặn mọi yêu cầu ngoài GET/HEAD trước điều hướng. Có 10/10 bài HTTP 200, không nhận điều khiển biên tập. Mẫu lấy hai URL đầu mỗi danh sách thể loại để kiểm kỹ thuật, chưa là mười bài pilot được người biên soạn chọn và chưa đánh giá phù hợp cho trẻ.

| Mẫu | Thể loại | Bài | HTTP | Nội dung |
|---|---|---|---:|---|
| M1 | Ca dao | [Mở bài](https://phongdao.com/ca-dao/a-di-da-phat-chua-chat-cho-nen/) | 200 | Chờ người biên soạn |
| M2 | Ca dao | [Mở bài](https://phongdao.com/ca-dao/ac-an-dua-bat-co-chiu-toi/) | 200 | Chờ người biên soạn |
| M3 | Tục ngữ | [Mở bài](https://phongdao.com/tuc-ngu/a-em-du-nhu-bu-nac-day/) | 200 | Chờ người biên soạn |
| M4 | Tục ngữ | [Mở bài](https://phongdao.com/tuc-ngu/a-em-du-nhu-tru-mot-bin/) | 200 | Chờ người biên soạn |
| M5 | Đồng dao | [Mở bài](https://phongdao.com/dong-dao/a-a-chung-chung/) | 200 | Chờ người biên soạn |
| M6 | Đồng dao | [Mở bài](https://phongdao.com/dong-dao/a-be-xe-dat-de-di-ia/) | 200 | Chờ người biên soạn |
| M7 | Vè | [Mở bài](https://phongdao.com/ve/bo-chi-em-oi-di-cho/) | 200 | Chờ người biên soạn |
| M8 | Vè | [Mở bài](https://phongdao.com/ve/chem-dau-thang-trich/) | 200 | Chờ người biên soạn |
| M9 | Câu đố | [Mở bài](https://phongdao.com/cau-do/ach-ui-mot-hoi-rat-doi-lau/) | 200 | Chờ người biên soạn |
| M10 | Câu đố | [Mở bài](https://phongdao.com/cau-do/ai-la-ai-hu-ben-song/) | 200 | Chờ người biên soạn |

Một bài tục ngữ M3 có 'scrollWidth=391' ở khung 390 px sau khi phông ổn định. Bộ dò thăm dò đặt ngưỡng 0 px nên JSON giữ 'technicalPassed=false'; cổng quét của repo dùng dung sai 1 px. Ghi nhận này còn nguyên trong 'T-198/overflow-result.json', không đổi cổng để qua kết quả và không coi đó là phê duyệt ngữ cảnh. Khám phá/Tuyển tập mở HTTP 200 và không ghi nhận tràn ngang trong mẫu.

## 4. Hai mươi quan hệ

20/20 đích mở HTTP 200. Kiểm địa chỉ hoạt động; người biên soạn còn cần quyết quan hệ/gợi ý có đúng ngữ cảnh, thích hợp độc giả dự định phục vụ hay cần sửa. Chưa tự sửa quan hệ, nguồn, lời hoặc phân loại trên site.

| # | Từ mẫu | Đích | Loại quan hệ | HTTP | Ngữ cảnh |
|---|---|---|---|---:|---|
| 1 | M1 | [Mở quan hệ](https://phongdao.com/ca-dao/anh-ra-song-cai-tu-ai-cho-mat-than/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 2 | M1 | [Mở quan hệ](https://phongdao.com/ca-dao/than-em-lay-khach-khong-suy/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 3 | M2 | [Mở quan hệ](https://phongdao.com/ca-dao/lich-su-chi-do-ma-lam-niem-trang-gio/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 4 | M2 | [Mở quan hệ](https://phongdao.com/ca-dao/nghe-tin-em-co-me-gia/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 5 | M3 | [Mở quan hệ](https://phongdao.com/tuc-ngu/ac-nhu-cho/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 6 | M3 | [Mở quan hệ](https://phongdao.com/tuc-ngu/hong-hong-nhu-lon-chuc-cuoi/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 7 | M4 | [Mở quan hệ](https://phongdao.com/chu-de/gia-dinh/) | Chủ đề | 200 | Chờ kiểm ngữ cảnh |
| 8 | M4 | [Mở quan hệ](https://phongdao.com/ca-dao/tieu-kia-den-o-chua-ta/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 9 | M5 | [Mở quan hệ](https://phongdao.com/dong-dao/buoi-sang-ngu-day/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 10 | M5 | [Mở quan hệ](https://phongdao.com/dong-dao/troi-mua-lam-ram/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 11 | M6 | [Mở quan hệ](https://phongdao.com/dong-dao/chuon-chuon-co-canh-thi-bay/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 12 | M6 | [Mở quan hệ](https://phongdao.com/dong-dao/coc-coc-keng-keng/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 13 | M7 | [Mở quan hệ](https://phongdao.com/ve/ve-cho-luong/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 14 | M7 | [Mở quan hệ](https://phongdao.com/ve/ve-con-gai-lang-sau/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 15 | M8 | [Mở quan hệ](https://phongdao.com/ve/phu-boi-gio-ga/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 16 | M8 | [Mở quan hệ](https://phongdao.com/ve/ve-cac-loai-qua/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 17 | M9 | [Mở quan hệ](https://phongdao.com/cau-do/mom-o-dinh-dau-hay-xo-hay-xien/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 18 | M9 | [Mở quan hệ](https://phongdao.com/cau-do/mui-mit-hat-hong/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 19 | M10 | [Mở quan hệ](https://phongdao.com/cau-do/bon-chan-chong-chong/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |
| 20 | M10 | [Mở quan hệ](https://phongdao.com/cau-do/nha-co-ba-an-com-truoc/) | Bài liên quan | 200 | Chờ kiểm ngữ cảnh |

Mẫu này chủ yếu kiểm Đọc tiếp và một chủ đề; chưa thay cho đối chiếu toàn bộ mục từ, địa danh hoặc sự phù hợp trẻ em. Các tuyến từ khó/địa danh đã có bằng chứng audit trước vẫn là căn cứ chọn mẫu sâu hơn, không phải xác nhận nội dung ở lượt này.

## 5. Phần người biên soạn và người thử còn phải làm

[Phiếu pilot 10 bài, một tuyển tập và tác vụ 5–8 người](huong-dan/pilot-10-bai.md) đã chuẩn bị, gồm trường quyết định nội dung/quyền ảnh, cấu trúc tuyển tập hiện có và bảng ghi thời gian/nhập lại/lưu nhầm/quay màn. Các ô người thật chưa có dữ liệu. Một tuyển tập thực chưa được agent tạo hoặc đăng trên production.

Người có quyền chốt mẫu, lời/nguồn/nghĩa/ngữ cảnh, ảnh thật và mạch tuyển tập rồi tự đăng khi muốn. Human Gate H2 của [AGENTS.md](../AGENTS.md) áp dụng: “đăng bài thay người biên soạn” và xác nhận sự thật biên tập là việc của con người. Phần mềm hỗ trợ quyết định, không tự đánh dấu nội dung đã duyệt. Chưa có số đo để kết luận tăng năng suất CTV.

## 6. Dùng và kiểm phiên bản

Xem [hướng dẫn giữ chữ, bản mới và ảnh](huong-dan/bien-tap-an-toan-va-anh.md). Với site thật, chạy 'npm run kiem-site-that' trên main để đối chiếu phiên bản; phép kiểm đó chỉ đọc. Ghi kết quả cụ thể sau khi bản cuối đã được gộp và hệ thống cập nhật hiện có nhận mã. Không dùng kết quả Playground để xác nhận upload, dữ liệu hoặc nội dung production.
