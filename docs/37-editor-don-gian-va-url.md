# Editor đơn giản và sửa URL tác phẩm

Ngày 13/11/2026, người sáng lập tạm hoãn đề xuất mở rộng nền nội dung và yêu cầu làm trước hai phần: editor đơn giản, sửa URL tác phẩm (D-110). Tài liệu này ghi phạm vi thực hiện; kết quả kiểm cập nhật sau khi chạy thực tế.

## Phạm vi

Giữ trang Sửa tác phẩm hiện tại và cơ chế lưu từng phần. Dùng editor sẵn có trong WordPress, không chuyển sang màn Gutenberg toàn bài hoặc thêm plugin editor.

| Phần | Nghiệm thu |
|---|---|
| Editor | Đậm, nghiêng, gạch chân, chèn/bỏ liên kết, màu đỏ/xanh/tím và chữ nhẹ, bỏ định dạng, hoàn tác/làm lại |
| Vùng nhập | Ý nghĩa/giải thích đáp án, Nghĩa đen, Nghĩa bóng, Đáp án, Gợi ý, lời giới thiệu Bối cảnh, lời giải từng từ khó |
| Dữ liệu | Chữ thuần giữ trường cũ; HTML tuỳ chọn nằm ở `dinh_dang` trong cùng phần/mục; kiểm chữ khớp nhau, lọc thẻ/link/lớp màu được phép; không nhận script, ảnh/nhúng hoặc CSS tuỳ ý |
| Lưu | Cùng form, quyền, dấu phiên và lịch sử; nháp Ý nghĩa không tự hiện; duyệt nháp giữ định dạng khi chữ không đổi; khôi phục lấy lại cả hai |
| Không JavaScript | Sửa và lưu chữ thuần được; sửa chữ sẽ bỏ định dạng cũ để tránh hiện sai nội dung. Sửa nhanh trang đọc theo cùng quy tắc |
| Hiệu năng | Chỉ nạp editor trong wp-admin Sửa tác phẩm; khởi tạo vùng đang mở, lời giải từng mục khi tập trung vào ô, không bung sẵn hàng chục editor |
| URL | Mục Đường dẫn trong mục lục; URL đang dùng, ô sửa tên cuối, dự kiến URL mới; phần thể loại dùng thao tác Đổi thể loại hiện có |
| Quyết định URL | Quản trị có quyền sửa bài, nonce đúng, dấu phiên đúng và đánh dấu xác nhận mới lưu; không đổi tên/lời/trạng thái/ID |
| URL trùng/cũ | Chặn tên đang dùng, tên cũ thuộc bài khác, tên dành riêng và URL đầy đủ; bài đang dùng chung đường dẫn phải qua thao tác tách hiện có |
| Chuyển hướng | Giữ đường đầy đủ trước mỗi lần đổi và trỏ tới ID hiện tại bằng 301 khi đường cũ không còn tồn tại; không ghi đè trang đang dùng, không chuyển khách tới bài nháp/ẩn |
| Lỗi và xung đột | Không ghi đè bản mới; giữ đầu vào lỗi, hiển thị URL đang lưu để đối chiếu; lỗi ghi tên/chuyển hướng/lịch sử phải rollback cùng giao dịch |
| Trợ năng | Kiểm bàn phím, điện thoại, tương phản màu sáng/tối, không tràn ngang; no-JS vẫn lưu được |

Lời và dị bản vẫn là văn bản theo dòng. Dùng khi và Hình ảnh trong bài vẫn theo cấu trúc từng dòng/cặp hiện có. Chức năng định dạng không thay nội dung văn hoá, người làm hoặc nguồn bằng suy đoán.

Không làm trong đợt này: chuyển màn biên soạn sang WordPress gốc, SEO plugin, loại nội dung mới, sách/chương/tác giả, chọn tác phẩm làm dị bản hoặc bảng quan hệ. Bốn ngoại lệ nguồn/lời/phục hồi trước đây và quyền mở Google giữ nguyên.

## Cách dùng

1. Mở Sửa đầy đủ, mở phần diễn giải cần sửa. Với từ khó, mở mục rồi đặt con trỏ vào Lời giải.
2. Bôi chọn chữ và dùng thanh công cụ. Chữ nhẹ dùng màu của giao diện, vẫn bảo đảm dễ đọc. Lưu riêng phần đang sửa; các phần khác chưa lưu vẫn được giữ theo tab.
3. Muốn đổi URL, mở Đường dẫn tác phẩm từ mục lục. Nhập tên cuối đường dẫn, xem dự kiến, đánh dấu xác nhận rồi Lưu đường dẫn.
4. Nếu báo bản đã đổi, đối chiếu URL đang lưu với tên đang nhập; chỉ sau khi đã đối chiếu mới nhận mốc mới để lưu tiếp.

## Kiểm thử và kết quả

**Đã phát hành và xác minh trên phongdao.com ngày 13/11/2026:** plugin **1.0.60**, theme **1.0.8**, qua [PR #173](https://github.com/truonganhquyen/ca-dao/pull/173), commit `1a27837`. Giữ nguyên phạm vi thu gọn; agent không sửa nội dung hoặc URL tác phẩm thật.

| Kiểm tra | Kết quả |
|---|---|
| `npm run kiem-tra` | Đạt, 163 task, 1 câu hỏi mở, 0 lỗi nhất quán |
| `npm run test:v2` | Đạt trên CI bản chốt; PHP thuần 264. Lượt local ghi nhận thêm 588 kiểm tra WordPress và 55 chuyển đổi; không tính các lượt lỗi trước đó là đạt |
| `npm run quet:v2` | CI đạt, 0 lỗi; 36 trang × 3 chế độ, 12 trang tĩnh × 2 chế độ 320 px, khu biên tập/quản trị và bài 36 mục × 6 cỡ; có kiểm editor/URL mở ở 390/1440 px |
| `npm run e2e:v2` | CI đạt 66 bước, 0 lỗi, 0 bỏ qua; gồm gõ/đậm/link/đổi màu/lưu/khách xem, khôi phục bản nhập, lọc nội dung nhúng lạ, URL/nonce/phiên/301 và URL không JavaScript |
| Kiểm riêng trên trình duyệt mẫu | Cả 5 luồng mới đạt; nút thật có nhãn/trạng thái và Space chỉ thực hiện một lần; không tự báo chưa lưu khi mở editor, bấm lưu không phát sinh hộp thoại rời trang |
| `npm run kiem-site-that` | Đúng plugin 1.0.60/theme 1.0.8 sau hệ thống tự cập nhật |
| wp-admin thật, chỉ đọc | Các bài #11272, #2949, #15486 × 390/1440 px: mở editor, nút có tên, không lồng điều khiển, ô URL/phiên/xác nhận đúng; 0 phần chưa lưu, không tràn ngang, quét 0 lỗi, giá trị URL không đổi |

[CI của bản chốt đã đạt](https://github.com/truonganhquyen/ca-dao/actions/runs/43441110580). Cây `wordpress/v2` của commit gộp trùng bản đã kiểm (`cd905a2bb43fe38a4122bc2176a750860e601e54`). Các phép ghi và khôi phục chạy trên WordPress mẫu bằng chữ kỹ thuật; không thử ghi định dạng hoặc đổi URL của tác phẩm thật. Kiểm production chỉ cho phép POST đăng nhập, sau đó GET/HEAD; không lưu tài khoản/cookie hoặc nội dung văn hoá thật vào hồ sơ kết quả. Kết quả phần mềm không thay cho thử tác vụ/nội dung thật của người biên soạn.

**Các lượt không tính đạt:** lượt đầu editor chạy trước khi WordPress nạp TinyMCE; lượt quét tiếp phát hiện 18 lỗi tên nút/lồng điều khiển của toolbar. Đã sửa thứ tự khởi tạo, dùng nút thật có tên/trạng thái/focus và kiểm lại bàn phím. Lượt E2E đầu sai cách mở phần chưa có nội dung và đọc nhầm thuộc tính `form.action` bị ô ẩn che; đã sửa kịch bản để đi qua mục lục và gửi đúng action, giữ nguyên các khẳng định quyền/nonce/phiên. Một lượt chẩn đoán no-JS dùng cách chờ không phù hợp không tính nghiệm thu; kiểm lại đủ 5 luồng mới đạt. Không bỏ kiểm thử, không nới ngưỡng và không tắt quy tắc trợ năng.

Hồ sơ cục bộ ở `output/playwright/editor-url/`: log CI và các lượt lỗi, kết quả kiểm riêng, ảnh mẫu kỹ thuật và `live-result.json`. Chưa có số đo tác vụ người thật để kết luận mức tăng năng suất; pilot T-198 vẫn chờ người biên soạn. Việc mở Google và quyền/nội dung văn hoá tiếp tục do người sáng lập quyết.
