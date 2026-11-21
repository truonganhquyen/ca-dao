# 51 · Đánh giá lại quyết định, quy tắc và cổng cũ

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Căn cứ (D-127, 21/11/2026)** — kế hoạch đang dùng: [57](57-ke-hoach-chot.md); nơi khác nhau, 57 thắng. Nguyên trạng lúc lập: **Đề xuất, chờ người sáng lập duyệt.** Không sửa, không xoá dòng nào trong `docs/01`, AGENTS.md hay `docs/00` cho tới khi được duyệt. Khi duyệt: mỗi mục được chấp thuận thành một D-xxx mới ghi "Thay / Sửa một phần D-yyy"; dòng cũ giữ nguyên làm lịch sử (quy tắc của `docs/01`) |
| Bộ tài liệu | [50 tầm nhìn](50-tai-dinh-vi-chien-luoc.md) · **51 rà soát quyết định** · [52 chất lượng nội dung](52-chien-luoc-chat-luong-noi-dung.md) · [53 trải nghiệm](53-thiet-ke-lai-trai-nghiem.md) · [54 kiến trúc](54-kien-truc-dich-va-chuyen-doi.md) · [55 lộ trình, backlog](55-lo-trinh-va-backlog.md) · [56 gói quyết định](56-goi-quyet-dinh.md) |

---

## 1. Cách đánh giá

Mỗi quyết định, quy tắc hay cổng được hỏi bốn câu:

1. **Còn phục vụ tầm nhìn mới** (thư viện tham chiếu, nhóm biên tập, mạng tri thức, hệ sinh thái) không?
2. **Giữ nó tốn gì** — thời gian, chất lượng, cơ hội?
3. **Bỏ nó mất gì** — dữ liệu, quyền, riêng tư, an toàn, niềm tin?
4. **Có đường chuyển đổi an toàn** không — làm dần, quay lại được, không mất dữ liệu?

| Kết luận | Nghĩa |
|---|---|
| **GIỮ** | Đúng với tầm nhìn mới; không đổi |
| **GIỮ, làm rõ** | Giữ nguyên tinh thần; ghi rõ cách hiểu để không cản việc mới |
| **SỬA** | Giữ một phần; đổi phạm vi, ngưỡng hay cách làm |
| **THAY** | Bỏ hướng cũ, thay bằng hướng mới có đường chuyển đổi |
| **THÊM** | Quy tắc hay cổng mới cho rủi ro mới |
| **LỊCH SỬ** | Đã hết hiệu lực hoặc đã hoàn tất; không cần hành động |

**Kết quả:** 26 mục đề xuất mở lại (mục 3: 18 SỬA · 5 THAY · 1 THÊM · 2 SỬA có điều kiện); 8 mục giữ và làm rõ (mục 4); phần bảo vệ cốt lõi giữ nguyên (mục 2).

## 2. Giữ nguyên — bảo vệ cốt lõi

| Quy tắc / quyết định | Vì sao giữ |
|---|---|
| **Ba lằn ranh** (AGENTS.md; D-040 nới bởi D-054): AI không viết lời, dị bản, nguồn, số trang, vùng, địa danh, tác giả, niên đại; nguồn không bịa; dữ liệu cá nhân, ghi âm phải có đồng ý | Là lý do để tin thư viện; càng mở rộng càng cần |
| AGENTS.md KHÔNG BAO GIỜ 1, 3, 4, 5, 7, 9, 10, 11, 12, 14, 15 | Bảo vệ nội dung văn hoá, kiểm thử, riêng tư, câu chữ, kho thô, máy chủ |
| KHÔNG BAO GIỜ 2 — văn bản máy soạn chỉ lên site sau khi người duyệt | Giữ; mục 4 làm rõ "người" là ai |
| Human Gate H1–H6 | Giữ; thêm H7–H9 (R-25) |
| Không quảng cáo, không cookie theo dõi, không thu IP (`docs/00` mục 5; D-064) | Định vị và pháp lý; R-17 chỉ đổi cách thêm sự kiện tổng hợp |
| Nội dung người đọc gửi luôn qua duyệt; không bình luận mở (D-062) | Kiểm duyệt là bắt buộc khi mở rộng cộng đồng |
| Bảo mật quản trị (D-076, T-118, T-124), CSP | Không đổi |
| Phần của bài lưu trong bài, người làm và nguồn từng phần, lịch sử, hoàn tác (D-084 (2), D-094 (1)(3)(5)) | Nền tốt nhất của V2; mô hình mới xây trên nó |
| Ba dòng việc Bài mới · Hộp việc · Bổ sung (D-103, D-104, D-121) | Mở rộng thành Xưởng biên tập, không thay |
| Tìm (D-067, D-112, D-113), tuyển tập (D-116), góp và ghi công (D-122, D-123, D-124), Từ điển nối bài (D-125, D-126) | Đã chạy tốt; mở rộng trong kế hoạch mới |
| Bài viết của tác giả khác `noindex` tới khi có quyền (D-126 (5)) | Đúng về quyền |
| Bản tham chiếu, dị bản, từ cấm trong giao diện (D-004, D-005, KHÔNG BAO GIỜ 10) | Cốt lõi của "không có bản đúng duy nhất" |
| Bốn cổng kiểm, tăng phiên bản, PR có bản xem trước, review độc lập (D-048), xếp chồng PR (D-053) | Giữ; R-23 chỉ đổi cách gộp theo mức rủi ro |

## 3. Đề xuất mở lại (26 mục)

Cột "QĐ" trỏ tới mục cần duyệt ở [56](56-goi-quyet-dinh.md).

### 3.1. Vận hành và định hướng

| # | Hiện hành | Đề xuất | Loại | Vì sao | Chuyển đổi | Gate | QĐ |
|---|---|---|---|---|---|---|---|
| R-01 | D-035; `docs/00` mục 4 nguyên tắc 10: mọi tính năng phải để "một người, khoảng 5 giờ/tuần, duy trì được 2 năm" | Lập kế hoạch theo **năng lực biên tập đã cam kết** (giờ duyệt mỗi tuần của cả nhóm); mỗi tính năng ghi rõ cần bao nhiêu giờ vận hành | THAY | Với một người, chất lượng ở quy mô 16.000 bài là không thể (52 mục 7) | Kịch bản S0 / S1 / S2; số mục tiêu đổi theo kịch bản được chọn | — | A2 |
| R-02 | D-082: định vị "nơi đọc, hiểu, trích dẫn, chia sẻ dễ chịu nhất trên điện thoại" | **"Thư viện tham chiếu"** cho văn học dân gian Việt Nam; dễ chịu trên điện thoại là điều kiện | SỬA | Định hướng nội dung, trải nghiệm, sản phẩm (50 mục 3) | Sửa `docs/00` mục 2; câu chữ công khai do người sáng lập viết | H4 | A1 |
| R-03 | D-039, D-054 (2): xuất bản sớm mọi thứ; D-045 → D-099, D-101: đăng hàng loạt do người bấm; `docs/00` mục 5 lại ghi "xuất bản hàng loạt — không bao giờ" (mâu thuẫn) | **Hai tầng xuất bản:** Bộ tham chiếu (đã biên tập) và Kho lưu trữ (lời trung thực, chưa biên tập). Vẫn không chặn đăng; Kho lưu trữ trình bày khác, không vào Câu hôm nay, trang chủ, Play; đăng hàng loạt vào tầng Lưu trữ | SỬA | Trung thực với người đọc về mức hoàn thiện; tập trung lối vào vào bài đã làm | Tầng tính từ dữ liệu (52 mục 2); sửa dòng mâu thuẫn ở `docs/00` | H4 | E1 |
| R-04 | D-046 (2), D-055, D-057, D-068, D-078, D-081, D-117 (2): chép chú thích Ca dao Mẹ có ghi công rồi bỏ ghi nguồn tại chỗ; thư chưa gửi; giữ như cũ | **Chính sách chú thích nguồn ngoài:** (1) gửi thư T-034 trong giai đoạn 0; (2) ngay lập tức hoặc bật lại ghi nguồn tại chỗ cho chú thích chép, hoặc ẩn chú thích chép khỏi trang công khai (giữ nội bộ); (3) chương trình thay chú thích cho Bộ tham chiếu (52 mục 5); (4) nguồn từ chối → tắt "Chép chú thích", ẩn toàn bộ | THAY | Rủi ro quyền và uy tín tăng theo quy mô; chặn dữ liệu mở, API, sách, hợp tác; trái tinh thần lời hứa nguồn | Công tắc "Ghi nguồn cho chú thích" và chính sách hiện theo hồ sơ nguồn đã có (D-085) — đổi cài đặt, không đổi mã; thay dần theo bài | H3 | B1 |
| R-05 | D-106 → D-117 (1): lập chỉ mục mọi bài có "phần riêng" | Giữ nguyên tắc; **nâng ngưỡng** "phần riêng" thành chuẩn tham chiếu tối thiểu (Ý nghĩa đạt Sổ tay đã duyệt, hoặc từ 2 phần riêng); mở lập chỉ mục cho hồ sơ thực thể và bài viết đã có quyền | SỬA | Khi xưởng AI chạy, một Ý nghĩa mỏng không được biến thành hàng nghìn trang mỏng | Sửa `MienDuLieu\PhanRieng`; theo dõi Search Console | H4 | E1 |
| R-06 | D-084 (4), `docs/00` mục 6: cấp L0–L3 chỉ nội bộ, không hiện cho người đọc | Định nghĩa lại cấp (52 mục 2) và **thử** một nhãn mức biên tập gọn cho người đọc (Lưu trữ · Đã biên tập · Tham chiếu) trong bản mẫu | SỬA | Người đọc cần biết bài đã được làm tới đâu, nhất là ở tầng Lưu trữ | Chỉ hiện khi qua thử người dùng | H4 | D3 |
| R-07 | D-013: chuyên gia không tự động "đã xác minh"; bằng chứng trước uy tín sau | Giữ tinh thần; **thêm bước Thẩm định** cho bài hạng A: người thẩm định ghi tên hoặc bút danh, ngày, phạm vi (giải nghĩa, bối cảnh); tách khỏi mức bằng chứng của lời | SỬA | Bài tham chiếu cần người chịu trách nhiệm học thuật; giáo viên tin hơn | Vai "Thẩm định" mới (54 mục 4.6) | — | E4 |

### 3.2. Nội dung và dữ liệu

| # | Hiện hành | Đề xuất | Loại | Vì sao | Chuyển đổi | Gate | QĐ |
|---|---|---|---|---|---|---|---|
| R-08 | D-079: gộp hẳn tục ngữ – thành ngữ, bỏ mọi phân loại tách | Giữ một mục, một đường dẫn; **thêm nhãn phụ** Tục ngữ · Thành ngữ · Chưa xác định, máy gợi ý, người xác nhận, chỉ hiện khi đã xác nhận | SỬA | Chương trình Ngữ văn dạy phân biệt hai loại; với AI gợi ý và người duyệt, gánh việc nhỏ hơn hồi 26/10 | Không đổi đường dẫn; nhãn là phân loại `hinh_thuc` hoặc trường riêng (54) | H1 | B2 |
| R-09 | D-111: chỉ người bật "Có từ ngữ thô tục"; máy không dò | **Máy gợi ý** (danh sách từ + AI), người xác nhận; câu đố và đồng dao phải rà xong trước khi mở Play, EduKit | SỬA | Sản phẩm cho trẻ cần an toàn ở quy mô; người không thể đọc hết 765 câu đố | Gợi ý vào Bổ sung, không tự bật | — | E6 |
| R-10 | D-094 (4) chỉ một bảng mới; D-110 hoãn loại nội dung mới và bảng quan hệ; D-114 không mô hình dữ liệu mới | **Cho phép cấu trúc chuyên dụng khi có lý do và kế hoạch di chuyển:** bảng quan hệ có kiểu `wp_cadao_lien_ket`, loại bài hồ sơ thực thể, cấu trúc bài viết (54) | THAY | Mạng tri thức là một trong năm đặt cược; chắp vá bằng meta rời sẽ thành nợ kỹ thuật khi có hàng nghìn quan hệ | Mở rộng – chuyển – thu hẹp, có trang thử, quay lại được (54 mục 6) | H1 | C1–C4 |
| R-11 | D-117 (3): nháp AI theo lô 30–50 bài, tối đa 50 nháp chờ | Kích thước lô và trần hàng chờ **theo năng lực người duyệt** (vd. 60 mục mỗi người mỗi tuần); nháp nhiều phần trong một lô | SỬA | Trần 50 là của kịch bản một người | Tham số trong cài đặt Xưởng biên tập | H2 | E2 |

### 3.3. Trải nghiệm và giao diện

| # | Hiện hành | Đề xuất | Loại | Vì sao | Chuyển đổi | Gate | QĐ |
|---|---|---|---|---|---|---|---|
| R-12 | D-105 (8), D-119, D-093 (10): bố cục trang tác phẩm hiện hành | **Thiết kế lại qua bản mẫu** (53): giữ "lời trước, không hộp rỗng, câu đố không lộ đáp án"; mở lại thứ tự, mật độ, hồ sơ bài, vị trí Ý nghĩa của vè, mời góp | SỬA | Trang đúng nhưng thiên về công cụ, chưa thành trải nghiệm tham chiếu (53 mục 2) | Mẫu mới chạy song song theo hạng bài, công tắc quay lại | H4 | D1 |
| R-13 | D-093 (1): mỗi câu một dòng, co chữ tới 15 px (14 px dưới 360 px) | Sàn cỡ chữ lời **18 px** trên điện thoại; dưới sàn thì xuống dòng có thụt treo thay vì co tiếp; thử với người lớn tuổi | SỬA | Vè và câu dài bị co nhỏ trên điện thoại (ảnh 21/11) | Một thông số trong `fit.js` / CSS; thử A/B trong bản mẫu | H4 | D2 |
| R-14 | D-087 (1), D-093 (3): kiến trúc thông tin Khám phá ▾ · Cho lớp học · Tra cứu ▾ | **Kiến trúc thông tin mới** sau thử nghiệm (53 mục 5): Đọc · Khám phá · Học · Chơi · Tra cứu | THAY | Sản phẩm mới (Atlas, Play, EduKit, bài viết) cần chỗ rõ ràng | Đường dẫn cũ giữ; menu đổi theo công tắc | H4 | D1 |
| R-15 | D-092 (1)(5): hướng thị giác tím – lục, bố cục bản mẫu 02/11 | **Mở thử hai hướng** (giữ phông Literata / Be Vietnam Pro): A "Giấy và mực" (ít màu, chữ là nhân vật chính) và B "Tím – lục tinh chỉnh" | SỬA | Nút tím nổi hơn lời; cần cảm xúc văn chương (53 mục 4) | Token trong `design/v2`; đồng bộ sang theme bằng lệnh đã có | H4 | D2 |
| R-16 | D-123: lời mời "Góp một câu" ở đầu trang, trang chủ, tìm không ra, cuối mọi bài; D-119 (6) | Giữ chức năng; **giảm độ nổi** trên trang đọc (một dòng), giữ mạnh ở tìm không ra và trang Góp; thử trong bản mẫu | SỬA | Khối mời góp 6–7 lối cạnh tranh với việc đọc (ảnh 21/11) | Đổi khối, không đổi dữ liệu | H4 | D1 |

### 3.4. Sản phẩm, đo lường, tài khoản, AI

| # | Hiện hành | Đề xuất | Loại | Vì sao | Chuyển đổi | Gate | QĐ |
|---|---|---|---|---|---|---|---|
| R-17 | D-064, D-069, D-088; AGENTS.md KHÔNG BAO GIỜ 8: đúng 5 sự kiện, thêm phải có D-xxx | **Sổ đăng ký sự kiện** duyệt một lần: chỉ sự kiện tổng hợp, không định danh, không cookie, không IP; thêm sự kiện mới chỉ cần vào sổ và qua kiểm riêng tư; vẫn cấm theo dõi cá nhân | SỬA | Play, EduKit, Khám phá cần biết cái gì có ích mà không xin quyết định cho từng sự kiện | Sổ ở `docs/26` mục 11; mã đếm dùng hạ tầng hiện có | — | A5 |
| R-18 | `docs/00` mục 5: không tài khoản, đăng nhập cho người đọc (xem lại khi ≥ 3 người vận hành) | Người đọc **vẫn không cần tài khoản**; mở tài khoản cho cộng tác viên (đã là người dùng WordPress) và, ở chặng 3, cho giáo viên dùng EduKit với dữ liệu tối thiểu | SỬA có điều kiện | Nhóm biên tập và lớp học cần danh tính; người đọc thì không | Gate mới H9 trước khi mở cho giáo viên, học sinh | H9 | F3 |
| R-19 | `docs/00` mục 5: chatbot, AI sinh nội dung công khai — "không bao giờ" | **Có điều kiện, chặng 3:** "Hỏi thư viện" chỉ trả lời từ nội dung đã duyệt, mỗi câu trả lời dẫn tới bài, từ chối khi không có căn cứ, gắn nhãn AI; qua bộ đánh giá trích dẫn đúng ≥ 95%. Giữ "không bao giờ" cho việc AI sáng tác lời dân gian | SỬA có điều kiện | Người nghiên cứu và giáo viên có nhu cầu tra cứu tự nhiên; rủi ro kiểm soát được bằng truy xuất và đánh giá | Cổng tìm ngữ nghĩa D-015 giữ làm điều kiện con | H8 | F4 |
| R-20 | `docs/00` mục 5: không điểm thưởng, bảng xếp hạng, chấm điểm trẻ | **Trò chơi hoá phục vụ học:** tiến độ cá nhân lưu trên máy, huy hiệu không so sánh, thử thách lớp do giáo viên tạo; vẫn không bảng xếp hạng công khai cho trẻ, không chuỗi ngày ép quay lại, không thưởng vật chất | SỬA | PhongDao Play và EduKit cần động lực học, không cần cạnh tranh | Không tài khoản ở bản đầu (lưu trên máy) | H4 (+ H9 khi có lớp) | F2 |
| R-21 | `docs/00` mục 5: không ứng dụng iOS / Android | Chưa làm ứng dụng gốc; làm **PWA** (thêm vào màn hình chính, đọc ngoại tuyến bộ đã lưu — T-065) ở chặng 2; xét ứng dụng khi có số đo | SỬA | Giáo viên, phụ huynh dùng ngoại tuyến trên lớp, ở quê | Không đổi kiến trúc đọc | H4 | F5 |
| R-22 | D-091: cổng mở thể loại mới (≥ 20 bài, ≥ 10 L2, mẫu trang riêng) trước khi có bất kỳ trang nào | Thêm chế độ **"Bộ sưu tập thử nghiệm"**: nhãn thử nghiệm, không lên menu chính, `noindex`, để làm thử hò, lý, hát ru, truyện sớm hơn; cổng cũ giữ cho việc lên menu | SỬA | Mở rộng có kiểm soát, học từ dữ liệu thật | Một cờ trên thể loại | H1 | F6 |

### 3.5. Quy trình kỹ thuật và an toàn

| # | Hiện hành | Đề xuất | Loại | Vì sao | Chuyển đổi | Gate | QĐ |
|---|---|---|---|---|---|---|---|
| R-23 | D-066: agent tự gộp mọi PR khi kiểm tra và CI đạt | **Gộp theo mức rủi ro:** thấp (sửa lỗi, tài liệu, công cụ nội bộ) tự gộp; trung bình (giao diện người đọc) sau khi người sáng lập xem bản xem trước; cao (di chuyển dữ liệu, mô hình, lập chỉ mục, quyền, đường dẫn) cần người sáng lập duyệt + review độc lập (D-048) + diễn tập trên trang thử | SỬA | Chương trình có nhiều thay đổi rủi ro cao trên 16.000 bài đang chạy | Ghi mức rủi ro trong mẫu PR | — | A4 |
| R-24 | D-073 (5): không trang thử riêng, Playground là đủ; D-090: bỏ thử khôi phục; D-098: hoãn đo hiệu năng kho lớn | **Trang thử riêng tư** có bản sao dữ liệu thật (`noindex`, khoá truy cập); **thử khôi phục** trước giai đoạn có di chuyển dữ liệu rồi mỗi quý; **đo hiệu năng** trên trang thử trước khi đưa bảng quan hệ và mẫu trang mới | THAY | Playground không có 16.000 bài; di chuyển dữ liệu mà chưa từng khôi phục là rủi ro mất dữ liệu | Người sáng lập tạo trang thử và lịch sao lưu trong FlashPanel (AGENTS.md, KHÔNG BAO GIỜ 15) | H6 (bảo vệ dữ liệu gốc) | A3 |
| R-25 | Human Gate H1–H6 | **Thêm H7** thương mại (thu tiền, hợp đồng, pháp nhân, thoả thuận dữ liệu); **H8** AI tương tác công khai với người đọc; **H9** tài khoản, dữ liệu trẻ em và học sinh | THÊM | Ba loại rủi ro mới của chặng 2–3 | Sửa AGENTS.md mục Human Gate | — | A6 |
| R-26 | AGENTS.md "LUÔN LUÔN": JavaScript tối thiểu, trang bài ≤ 21 KB; D-030, D-094 (2), `docs/26` mục 1: không React, không bước dựng, không thư viện ngoài | **Giữ** cho trang đọc (≤ 21 KB, tắt JavaScript vẫn đọc); **ngân sách riêng** cho trang ứng dụng (Play, Creator, Xưởng biên tập): ≤ 80 KB nén, ES module, Web Components, không framework nặng; bước dựng chỉ khi chứng minh cần, CI so bản dựng | SỬA | Trò chơi, trình tạo phiếu cần tương tác hơn trang đọc | Ngân sách theo loại trang trong `quet:v2` | H4 | C6 |

## 4. Giữ, làm rõ cách hiểu

| # | Quy tắc | Làm rõ |
|---|---|---|
| G-1 | KHÔNG BAO GIỜ 2, 4: "người biên soạn" duyệt, đăng | Là **người có vai được giao** (Biên tập, Thẩm định, Quản trị) — không chỉ người sáng lập. Máy và agent vẫn không duyệt, không đăng |
| G-2 | Lằn ranh 1: lời vào site qua "đường thu thập máy" hoặc người gõ | Đường thu thập máy gồm **nhận dạng chữ (OCR) từ bản quét sách hết bản quyền**, với điều kiện người so lại từng dòng với ảnh trang trước khi đăng |
| G-3 | KHÔNG BAO GIỜ 1: không giải nghĩa khẳng định mà không có căn cứ, không điển tích | AI **được soạn nháp** chú giải từ từ điển hết bản quyền và nguồn mở **kèm trích dẫn**; người duyệt kiểm trích dẫn; nhận định không có nguồn phải viết ở dạng "có cách hiểu cho rằng…" hoặc bị bỏ |
| G-4 | D-027: quyền kiểm ở nguồn, tư liệu, ghi âm; không có trạng thái quyền cho tác phẩm | Giữ; **thêm trường quyền có cấu trúc cho tệp ảnh, âm thanh** (đúng tinh thần D-027) |
| G-5 | D-037: CC BY 4.0 cho phần dự án tự viết | Giữ; ghi rõ hệ quả: người khác được dùng cả cho mục đích thương mại; giá trị có thu tiền của PhongDao nằm ở dịch vụ, biên tập, hợp tác — không ở độc quyền nội dung |
| G-6 | D-085: chính sách hiện theo hồ sơ nguồn | Giữ; đây là cơ chế để làm R-04 mà không cần sửa mã |
| G-7 | D-065, D-074: chống trùng | Giữ; mở rộng sang **họ dị bản giữa các bài đã đăng** và công cụ gộp có xem trước do người bấm (54 mục 4.4) |
| G-8 | D-086, D-094: không dùng trình soạn khối cho tác phẩm | Giữ cho tác phẩm; **bài viết, hồ sơ thực thể dùng trình soạn khối** (văn bản dài) |

## 5. Lịch sử — không cần hành động

Các quyết định gắn với Astro, Sveltia, MVP và ngày chuyển V2 (khoảng D-002 đến D-053, D-056, D-071, D-083, D-089) đã có ghi "Thay bởi" hoặc đã hoàn tất. Các nguyên tắc còn sống từ thời đó giữ nguyên: D-004 (tác phẩm – dị bản), D-005 (Bản tham chiếu), D-006 (chú giải trỏ Từ điển), D-008 (cờ nghi), D-015 (cổng tìm ngữ nghĩa), D-016 (Sao Bắc Đẩu), D-029 (WCAG 2.2), D-032 (dòng `AI-Agent:`), D-037 (giấy phép), D-038 (đặt dấu), D-048 (review độc lập), D-053 (xếp chồng PR).

## 6. Thủ tục khi được duyệt

1. Mỗi mục R-xx được duyệt → **một D-xxx mới** ở `docs/01`, nội dung ghi "Thay D-yyy" hoặc "Sửa một phần D-yyy (phần …)"; thêm vào dòng cũ chữ "**Thay / sửa một phần bởi D-xxx**" (đúng quy tắc "thêm dòng mới, không xoá dòng cũ").
2. Sửa các tài liệu nguồn sự thật bị ảnh hưởng trong cùng PR: AGENTS.md (R-17, R-25, R-26, G-1, G-2, G-3), `docs/00` (R-01, R-02, R-03, R-18–R-21), `docs/26` (R-10, R-17, R-24, R-26), `docs/27` (R-12–R-16), `docs/28` (R-07, R-09, R-11).
3. Mục bị bác → ghi ở mục 3 của tài liệu này là "giữ như cũ" kèm ngày; agent không đề xuất lại nếu không có bằng chứng mới.
4. Không gom quyết định của người sáng lập: mỗi D-xxx ghi đúng câu trả lời (AGENTS.md, KHÔNG BAO GIỜ 12).
