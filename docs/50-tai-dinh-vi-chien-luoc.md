# 50 · Tái định vị chiến lược và tầm nhìn — Chương trình nâng cấp lớn V2

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Căn cứ (D-127, 21/11/2026)** — kế hoạch đang dùng: [57](57-ke-hoach-chot.md); nơi khác nhau, 57 thắng. Nguyên trạng lúc lập: **Đề xuất, chờ người sáng lập duyệt** ([56](56-goi-quyet-dinh.md)). Không thay quyết định nào trong `docs/01` cho tới khi được duyệt và ghi D-xxx mới |
| Quan hệ với 45–49 | [45](45-kiem-ke-va-doi-chieu.md) vẫn là **căn cứ kiểm kê**. Bộ 50–56 **thay phần định hướng, kiến trúc, lộ trình** của 46–49 (người sáng lập đánh giá 46–49 quá bảo thủ, 21/11/2026). 46–49 giữ nguyên làm lịch sử, không ghi đè |
| Bộ tài liệu | **50 tầm nhìn** · [51 rà soát quyết định](51-danh-gia-lai-quyet-dinh.md) · [52 chất lượng nội dung](52-chien-luoc-chat-luong-noi-dung.md) · [53 trải nghiệm](53-thiet-ke-lai-trai-nghiem.md) · [54 kiến trúc](54-kien-truc-dich-va-chuyen-doi.md) · [55 lộ trình, backlog](55-lo-trinh-va-backlog.md) · [56 gói quyết định](56-goi-quyet-dinh.md) |

---

## 1. Tóm tắt điều hành

PhongDao có **một nền kỹ thuật tốt hơn hẳn nội dung nó đang chứa**. Mã V2 đã có lưu theo từng phần, nguồn và người làm cho mỗi phần, lịch sử và hoàn tác, chống trùng, trợ năng WCAG 2.2 AA, tự cập nhật có quay lại. Nhưng trong khoảng 16.300 bài đã đăng, chỉ **215 bài (1,3%)** có phần do thư viện tự làm thêm nên đủ điều kiện lên Google. Từ điển có **4 mục** lời giải do thư viện tự viết. Gần như mọi chú thích đang hiện được chép từ một nguồn duy nhất, chưa có thư đồng ý, và không ghi nguồn tại chỗ.

Bộ 45–49 đã đề xuất hoàn thiện từng bước trong khuôn khổ cũ: một người biên soạn, 30 bài mỗi tuần, không mô hình mới. Khuôn khổ đó **không đưa PhongDao tới mục tiêu người sáng lập đặt ra**. Với nhịp 30 bài mỗi tuần, cần khoảng mười năm để biên tập một phần năm kho.

Chương trình mới đặt cược vào năm thay đổi:

1. **Tinh hoa trước, rộng sau.** Chọn một **Bộ tham chiếu** khoảng 3.000 tác phẩm theo nhu cầu đọc, giá trị văn hoá và chương trình học. Trong đó có 500 bài hạng A được làm tới mức sách chuyên khảo. Đưa bộ này lên chuẩn tham chiếu trong 6–9 tháng. Phần còn lại là **Kho lưu trữ** trung thực, đọc được, không quảng bá như bài đã biên tập.
2. **Xưởng biên tập có AI và nhiều tầng người duyệt.** Máy soạn nháp có căn cứ, tự kiểm lỗi. Người duyệt theo vai: cộng tác viên, biên tập viên, thẩm định. Chất lượng kiểm bằng lấy mẫu. Năng lực mục tiêu: khoảng 200 bài mỗi tuần (mỗi bài 3–5 mục duyệt) thay vì 30. Điểm nghẽn thật là **giờ người duyệt**, nên chương trình cần một nhóm biên tập, không chỉ phần mềm (52).
3. **Mạng tri thức.** Thêm một bảng quan hệ có kiểu và có bằng chứng; hồ sơ nhân vật, địa danh, điển tích; bài khảo cứu và bình giảng nối với tác phẩm. Một bài sẽ dẫn người đọc tới bối cảnh, hình tượng, những bài cùng mạch, thay cho "cùng chủ đề" chung chung (54).
4. **Thiết kế lại trải nghiệm đọc theo chuẩn tham chiếu.** Trang hiện tại đúng và gọn nhưng thiên về công cụ. Lời chưa phải trung tâm cảm xúc, mời góp lấn đọc, Đọc tiếp yếu. Thiết kế lại năm mẫu thể loại, trang hồ sơ, bài khảo cứu, tuyển tập, Khám phá, trang chủ — **qua bản mẫu và thử với người dùng trước khi viết mã** (53).
5. **Hệ sinh thái mở theo cổng.** PhongDao Play, EduKit, Creator Studio, khung nhúng / API, tuyến di sản, ấn phẩm văn hoá đều nằm trong lộ trình, xếp theo điều kiện khởi động. Không ý tưởng nào bị gạt bỏ vĩnh viễn (55, mục 8).

**Giữ nguyên tuyệt đối:** ba lằn ranh (AI không viết lời và nguồn; nguồn không bịa; dữ liệu cá nhân và ghi âm phải có đồng ý); không quảng cáo, không theo dõi người đọc; người duyệt mọi nội dung máy soạn trước khi công khai; sao lưu, quay lại, kiểm thử; lịch sử quyết định.

**Cần người sáng lập quyết trước khi bắt đầu** ([56](56-goi-quyet-dinh.md)):

- tầm nhìn và tên gọi chương trình;
- **năng lực biên tập**: mở nhóm cộng tác viên, ngân sách;
- **chính sách chú thích chép từ Ca dao Mẹ**: gửi thư, ghi nguồn lại hoặc ẩn, thay dần;
- cách chạy AI và ngân sách;
- mở lại 26 quyết định hoặc quy tắc cũ ([51](51-danh-gia-lai-quyet-dinh.md));
- kiến trúc tri thức.

## 2. Chẩn đoán thẳng thắn

| # | Phát hiện | Bằng chứng | Hệ quả |
|---|---|---|---|
| 1 | Thương hiệu "thư viện" đi trước nội dung | 215 / ~16.300 bài lên Google (sơ đồ trang 21/11: ca dao 133, tục ngữ 61, vè 10, câu đố 7, đồng dao 4); 4 mục từ có lời giải tự viết; 8 tuyển tập; 3 bài cảm nhận | Google và người đọc thấy một kho chép lại; giá trị riêng chưa đủ dày để cạnh tranh |
| 2 | Phụ thuộc một nguồn, có rủi ro quyền | Lời và chú thích lấy từ Ca dao Mẹ; chép chú thích có ghi công (D-055) rồi bỏ dòng ghi nguồn tại chỗ (D-068); thư xin phép chưa gửi (T-034); người sáng lập giữ nguyên ngày 15/11 (D-117 (2)) | Chặn dữ liệu mở, API, sách, mọi hợp tác; rủi ro uy tín khi site lớn lên |
| 3 | Năng lực biên tập là điểm nghẽn, không phải phần mềm | Một người, dưới 10 giờ mỗi tuần (D-035); các việc nội dung chờ người sáng lập từ đầu tháng 11 (`docs/02`) | Mọi lộ trình chỉ thêm tính năng sẽ dừng ở cùng chỗ |
| 4 | Đầu tư nghiêng về khu quản trị | Quản trị làm lại bốn lần trong 15 ngày (D-100, D-103/104, D-121 bốn đợt); trang đọc chủ yếu được tinh gọn (D-114, D-119) | Người biên soạn có công cụ tốt nhưng trải nghiệm người đọc ít tiến lên về chiều sâu |
| 5 | Trải nghiệm đúng nhưng chưa đáng nhớ | Ảnh chụp 21/11 (53, mục 2): lời nhỏ so với trang, nút tím nổi hơn lời; Đọc tiếp toàn "cùng chủ đề" rộng, trống ở Vè cô Giang; khối mời góp 6–7 lối ở cuối mọi bài; trang thể loại ca dao dài 10.336 px trên điện thoại | Người đọc tìm được câu nhưng ít lý do ở lại, quay lại, giới thiệu |
| 6 | Chất lượng câu chữ không đều | Ý nghĩa dùng tính từ chung chung, mở đầu nhắc lại câu, trích dài trong phần Nghĩa; nhận định lịch sử không dẫn nguồn ở vè (53, mục 2) | Mâu thuẫn với lời hứa "đã được kiểm đến mức nào" |
| 7 | Mạng tri thức chưa có | Không quan hệ do người chọn ngoài tuyển tập và gần nghĩa tục ngữ; loại mục từ (địa danh, nhân vật) được mô tả nhưng chưa làm (45, L1–L3) | Không có lối đi theo hình tượng, nhân vật, điển tích — đúng chỗ người yêu văn hoá và người nghiên cứu cần |
| 8 | Một số quyết định cũ được đặt cho giai đoạn đã qua | Không trang thử riêng (D-073 (5)), không thử khôi phục (D-090), hoãn đo hiệu năng (D-098), tự gộp mọi PR (D-066) | Ổn khi kho nhỏ; rủi ro khi sắp đổi mô hình dữ liệu và giao diện trên 16.000 bài đang chạy |

## 3. Tầm nhìn

> **PhongDao là thư viện tham chiếu đáng tin nhất cho văn học dân gian Việt Nam: mỗi tác phẩm có lời được đối chiếu, nghĩa được biên tập, bối cảnh và mạng tri thức để đi xa hơn; đọc đẹp như một cuốn sách quý; và là nền dữ liệu mở cho học tập, sáng tạo, bảo tồn.**

**Định vị đề xuất:** từ "nơi đọc và chia sẻ dễ chịu nhất trên điện thoại" (`docs/00`) sang **"thư viện tham chiếu"** — nơi giáo viên, học sinh, người viết, nhà nghiên cứu, báo chí tìm tới khi cần biết một câu nói gì, có những bản nào, lấy từ đâu, gắn với ai, nơi nào, chuyện gì. "Dễ chịu trên điện thoại" trở thành điều kiện, không còn là khác biệt chính.

**Năm trụ** (mở rộng TIN – HIỂU – CẢM của `docs/00`):

| Trụ | Nghĩa | Thước đo chính |
|---|---|---|
| **TIN** | Lời đối chiếu được, nguồn minh bạch từng phần, sửa có lịch sử | % bài tham chiếu có đối chiếu sách; tỷ lệ lỗi qua kiểm mẫu |
| **HIỂU** | Nghĩa, chú giải, bối cảnh viết bởi người, có căn cứ | Số bài đạt chuẩn tham chiếu; thời gian tìm được nghĩa |
| **CẢM** | Đọc như sách quý: chữ, khoảng trắng, nhịp, giọng, hình, tiếng | Kết quả thử người dùng; lượt lưu, chia sẻ |
| **NỐI** | Từ một bài đi tới hình tượng, nhân vật, nơi chốn, điển tích, bài khảo cứu | % bài có ≥ 3 quan hệ đã duyệt; lượt đi tiếp mỗi phiên |
| **DÙNG** | Dữ liệu sạch quyền, dùng lại được cho dạy học, sáng tạo, trưng bày | Số bộ học liệu, lượt tải dữ liệu mở, đối tác |

## 4. Người dùng và việc họ cần làm

| Nhóm | Việc chính | PhongDao phải làm tốt hơn hiện nay |
|---|---|---|
| Giáo viên Ngữ văn | Soạn bài 10–15 phút: bộ câu theo bài học, nghĩa chuẩn xác, trình chiếu, phiếu in, trích dẫn | Bộ theo chương trình (EduKit), giải nghĩa thẩm định, phân biệt tục ngữ – thành ngữ |
| Học sinh | Hiểu nghĩa, làm bài, tra từ cổ | Trả lời trong 10 giây, ví dụ dùng, từ khó có nguồn |
| Phụ huynh | Đọc, chơi cùng con | Đồng dao có cách chơi thật, câu đố an toàn cho trẻ, PhongDao Play |
| Người yêu văn hoá | Cảm, đi sâu theo hình tượng, vùng, chuyện | Mạng tri thức, bình giảng, chuyên đề, bản đồ |
| Người nghiên cứu, người viết | Dị bản, nguồn in, số trang, bối cảnh, trích dẫn chuẩn | Hồ sơ bài, đối chiếu sách, khảo cứu có thư mục, xuất trích dẫn |
| Người góp, người giữ ký ức | Góp câu, dị bản, sách, ghi âm | Lối góp gọn, ghi công, quy trình đồng ý |
| Nhóm biên tập (mới) | Duyệt nhanh, đúng, có chuẩn | Xưởng biên tập, sổ tay, phân vai, kiểm mẫu |

## 5. Năm đặt cược chiến lược

| # | Đặt cược | Kết quả sau 6 tháng | Kết quả sau 18 tháng | Tài liệu |
|---|---|---|---|---|
| 1 | **Tinh hoa trước** — Bộ tham chiếu ~3.000 bài, hạng A 500 bài | 3.000 bài chuẩn tham chiếu (kịch bản S1), gồm toàn bộ 138 vè và 765 câu đố | 5.000+ bài; 500 bài hạng A có đối chiếu sách và thẩm định | 52 |
| 2 | **Xưởng biên tập có AI** | Năng lực ~200 bài/tuần; tỷ lệ lỗi lớn ≤ 2% qua kiểm mẫu | 5–10 người duyệt thường xuyên; quy trình ổn định | 52 |
| 3 | **Mạng tri thức** | Bảng quan hệ, 200 hồ sơ thực thể, 20 bài khảo cứu / bình giảng | 1.000 hồ sơ, 100 bài viết, bản đồ địa danh có kiểm | 54 |
| 4 | **Trải nghiệm tham chiếu** | Bản mẫu được thử với 10 người, duyệt; năm mẫu thể loại mới lên cho bài hạng A | Toàn site theo thiết kế mới; Khám phá, Atlas, bài viết | 53 |
| 5 | **Hệ sinh thái theo cổng** | PhongDao Play v1 và Học bản nhẹ đang thử với phụ huynh, 5 giáo viên (tháng 6–8) | Dữ liệu mở bản đầu; đối tác đầu tiên (trường / bảo tàng / du lịch); mô hình tài chính rõ | 55 |

## 6. Giữ · Cải tiến · Xây lại

| Nhóm | Giữ nguyên | Cải tiến | Thiết kế / xây lại |
|---|---|---|---|
| Nguyên tắc | Ba lằn ranh; không quảng cáo, không theo dõi; người duyệt nội dung máy soạn; giấy phép CC BY 4.0 phần tự viết | "Trung thực hơn hoàn hảo" thành hai tầng xuất bản (Tham chiếu / Lưu trữ); "người biên soạn" là bất kỳ người được phân vai, không chỉ người sáng lập | Giả định vận hành "một người, 5 giờ/tuần" → mô hình nhóm biên tập |
| Nền tảng | WordPress, plugin/theme riêng, tự cập nhật, Playground, bốn cổng kiểm | Trang thử có dữ liệu thật; thử khôi phục; chính sách gộp PR theo mức rủi ro | — |
| Dữ liệu | Phần của bài trong post meta, người làm và nguồn từng phần, lịch sử, chống trùng | Mục từ có loại; dữ liệu quyền ảnh có cấu trúc; tầng ưu tiên trong bảng tóm tắt | Bảng quan hệ có kiểu; hồ sơ thực thể; cấu trúc bài viết; họ dị bản liên bài |
| Nội dung | Lời giữ nguyên văn; đối chiếu sách do người điền | Quy tắc viết thành Sổ tay; máy nhắc chất lượng | Chương trình Bộ tham chiếu; thay chú thích chép; đa dạng hoá nguồn (sách hết bản quyền, đối tác) |
| Biên tập | Bài mới · Hộp việc · Bổ sung; Lịch sử, Hoàn tác | Nháp theo lô nhiều phần, có kiểm tự động | Xưởng biên tập: hàng duyệt theo vai, kiểm mẫu, thẩm định, số đo năng suất |
| Trải nghiệm | Lời trước, không hộp rỗng, câu đố không lộ đáp án, tắt JavaScript vẫn đọc | Tìm (đồng nghĩa, tên cũ, thực thể); tuyển tập; mời góp nhẹ hơn | Năm mẫu thể loại, hồ sơ bài, trang thực thể, bài khảo cứu, Khám phá / Atlas, trang chủ, PhongDao Play |

## 7. Chỉ số thành công

**Sao Bắc Đẩu kép:** (1) **Số tác phẩm đạt chuẩn tham chiếu** (phía cung, 52 mục 2) và (2) **Lượt dùng tin cậy mỗi tuần** — trích dẫn + chia sẻ + lưu (phía cầu, giữ từ `docs/00`).

| Chỉ số | Hiện nay | 3 tháng | 6 tháng | 12 tháng |
|---|---|---|---|---|
| Bài chuẩn tham chiếu (L2 mới trở lên) | 215 | 1.000 | 3.000 | 5.000 |
| Bài hạng A có đối chiếu sách + thẩm định | ~0 | 50 | 200 | 500 |
| Chú thích chép còn hiện ở bài thuộc Bộ tham chiếu | gần hết | −50% | 0 | 0 |
| Hồ sơ thực thể đã duyệt (nhân vật, địa danh, điển tích, mô-típ) | 0 (4 mục từ tự viết) | 50 | 200 | 1.000 |
| Bài khảo cứu / bình giảng có quyền, đã nối | 0 | 5 | 20 | 100 |
| Trang lập chỉ mục (tác phẩm + hồ sơ + bài viết) | ~270 | 1.200 | 3.500 | 6.000 |
| Tỷ lệ lỗi lớn qua kiểm mẫu | chưa đo | ≤ 3% | ≤ 2% | ≤ 1% |
| Thử người dùng: tác vụ đạt | chưa đo | ≥ 80% (bản mẫu) | ≥ 85% | ≥ 90% |
| Lượt dùng tin cậy / tuần | đo được | × 2 | × 4 | × 10 |

Số mục tiêu giả định có nhóm biên tập theo kịch bản S1 (52 mục 7). Nếu chỉ có người sáng lập (S0), chia mục tiêu nội dung cho khoảng 3.

## 8. Ba chặng

| Chặng | Thời gian (từ ngày duyệt) | Trọng tâm | Kết quả |
|---|---|---|---|
| **C1 · Nền chất lượng** | Tháng 0–6 | Điều kiện an toàn; Xưởng biên tập; Bộ tham chiếu đợt 1; bản mẫu → xây năm mẫu thể loại mới; bảng quan hệ | Bước tiến thấy rõ ở 1.000–3.000 bài; trải nghiệm mới ở bài hạng A |
| **C2 · Tri thức và học** | Tháng 6–18 | Hồ sơ thực thể, bài khảo cứu, Atlas, Khám phá mới; PhongDao Play, EduKit; dữ liệu mở | Thư viện tham chiếu đủ dày; sản phẩm học và chơi đầu tiên |
| **C3 · Hệ sinh thái** | Tháng 18–36 | API, khung nhúng, Creator Studio, tuyến di sản, ấn phẩm, lưu trữ sống (ghi âm) | Đối tác, mô hình tài chính bền vững (sau quyết định pháp nhân) |

## 9. Nguyên tắc không thương lượng và có thể thương lượng

**Không thương lượng:** ba lằn ranh; không nội dung máy soạn lên site khi chưa có người duyệt; không bịa nguồn; không quảng cáo; không bán dữ liệu cá nhân; không theo dõi định danh người đọc; sao lưu đã thử khôi phục trước mọi lần di chuyển dữ liệu; mọi thay đổi có đường quay lại; kiểm thử không bị làm yếu; lịch sử quyết định không bị xoá.

**Có thể thương lượng nếu có lý do và đường chuyển đổi** ([51](51-danh-gia-lai-quyet-dinh.md)): số bảng dữ liệu; loại nội dung; ngân sách JavaScript ngoài trang đọc; tài khoản cho người góp và giáo viên; trò chơi hoá có giới hạn; số sự kiện đo (không định danh); AI trả lời người đọc có trích dẫn; thứ tự các khối trên trang; bảng màu; cách trình bày lời; mức tự gộp PR.

## 10. Rủi ro chương trình

| Rủi ro | Mức | Giảm thiểu |
|---|---|---|
| Không tuyển được người duyệt | Cao | Kịch bản S0 vẫn chạy (AI + người sáng lập); tuyển qua trường sư phạm, khoa văn, câu lạc bộ; ghi công, chứng nhận; tìm tài trợ cho biên tập viên bán thời gian |
| Nháp AI kém, người duyệt mệt | TB | Bộ đánh giá 200 bài trước khi chạy lớn; trình kiểm tự động; Sổ tay; theo dõi tỷ lệ sửa nặng |
| Ca dao Mẹ phản đối | TB | Ghi nguồn lại ngay hoặc ẩn; công tắc chép chú thích; chương trình thay chú thích |
| Thiết kế lại làm vỡ thói quen, SEO | TB | Bản mẫu, thử người dùng, triển khai theo hạng bài, giữ đường dẫn, theo dõi Search Console, quay lại bằng công tắc |
| Di chuyển dữ liệu lỗi trên site thật | Cao | Trang thử có dữ liệu thật, thử khôi phục, mở rộng – chuyển – thu hẹp, lệnh hoàn tác (54, mục 6) |
| Quá nhiều việc song song cho người sáng lập | Cao | Một nhóm biên tập; gói quyết định gom một lần; chính sách gộp PR theo rủi ro |
| Thương mại hoá làm mờ sứ mệnh | TB | Thư viện miễn phí mãi; chỉ dịch vụ và sản phẩm phái sinh có thu, sau quyết định pháp nhân (Q-008) |
