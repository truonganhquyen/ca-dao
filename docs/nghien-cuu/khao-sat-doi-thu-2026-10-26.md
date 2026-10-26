# Khảo sát các kho văn học dân gian và kế hoạch nâng cấp phongdao.com

| | |
|---|---|
| Ngày | 26/10/2026 |
| Trạng thái | **Bản khung, chưa xong.** Mạng của phiên khảo sát chặn mọi site cần mở (mục 0). Phần đối thủ còn trống; chưa có kết luận |
| Người làm | Claude Code (agent), theo yêu cầu của người sáng lập |
| Phạm vi | Site Việt Nam về ca dao, tục ngữ, thành ngữ, hò, vè, đồng dao, câu đố, hát ru, dân ca, kho văn học, thơ; 3–5 site nước ngoài để so; phongdao.com |
| Lằn ranh | Không chép lời, chú thích, giải nghĩa, ảnh của site khác. Không lưu ảnh chụp site khác vào repo. Không ghi điều chưa thấy tận mắt. Chỉ mở trang như người đọc thường |

## 0. Tiến độ và giới hạn của lần khảo sát này

**Mạng bị chặn.** Môi trường chạy agent chỉ cho ra ngoài tới một số địa chỉ. Ngày 26/10/2026, mọi yêu cầu tới các site cần khảo sát đều bị từ chối, cả bằng trình duyệt Playwright lẫn công cụ đọc trang:

- cadao.me, thivien.net, vi.wikisource.org, vi.wiktionary.org;
- phongdao.com;
- poetryfoundation.org, gushiwen.cn, aozora.gr.jp.

Chỉ công cụ tìm kiếm của phiên chạy được. Công cụ này trả về danh sách kết quả (tên trang, địa chỉ) và một đoạn tóm tắt. Nó tìm từ khu vực Mỹ, **không phải Google Việt Nam**.

**Hệ quả:**

- Chưa mở được site nào, nên mục 2–4 chưa liệt kê site đã khảo sát. Theo yêu cầu, site chưa mở được thì không đưa vào danh sách.
- Phần phongdao.com (mục 3.1) lấy từ repo, `docs/e2e-review/REPORT.md` (26/10/2026) và `docs/02`. Chưa đo lại trên site thật.
- Phần SEO có bằng chứng từ công cụ tìm kiếm (mục 3.2). Đó là những gì thật sự thấy trong danh sách kết quả, chưa mở trang nào.

**Để làm tiếp:** người sáng lập mở rộng quyền mạng của môi trường (xem câu hỏi Q1 ở mục 8), rồi agent điền mục 2–7.

## 1. Tóm tắt cho người sáng lập

*Chờ dữ liệu đối thủ.* Phát hiện sơ bộ duy nhất có bằng chứng:

- **Trên công cụ tìm kiếm của phiên, đối thủ về thứ hạng không phải Ca dao Mẹ hay Thi viện mà là các trang văn mẫu, lời giải bài tập** (hoatieu.vn, loigiaihay.com, luatminhkhue.vn, vietjack.com…). Với 8 câu quen, Ca dao Mẹ và Thi viện không có mặt trong khoảng 9 kết quả đầu. Hai site này chỉ hiện khi lọc riêng tên miền. Cần đối chiếu trên Google Việt Nam trước khi dựa vào phát hiện này (mục 3.2).

5 phát hiện quan trọng nhất: *chưa có.*
5 việc nên làm trước: *chưa có.*

## 2. Danh sách site đã khảo sát

*Chưa có site nào mở được (mục 0).*

| # | Site | Địa chỉ | Ngày truy cập | Lý do chọn |
|---|---|---|---|---|
| | | | | |

Tiêu chí chọn sẽ dùng: lượng nội dung; thứ hạng tìm kiếm cho các câu quen; độ tin cậy, cách ghi nguồn; mức được dùng trong trường học; có tính năng đáng học.

## 3. Hồ sơ từng site

Mỗi hồ sơ theo 8 mục: (1) nội dung, (2) luồng người dùng, (3) tìm kiếm, (4) tính năng đặc biệt, (5) UX/UI điện thoại 390 px và máy tính, (6) SEO, (7) mô hình vận hành, (8) điểm mạnh nên học, điểm yếu nên tránh.

### 3.1. phongdao.com (đối chiếu)

**Nguồn bằng chứng:** mã trong `wordpress/`, `docs/02` (bản 2.3, 26/10/2026), `docs/e2e-review/REPORT.md` (khảo sát E2E 26/10/2026, plugin 0.28.0). Một số lỗi trong báo cáo E2E đã sửa sau đó (T-122, T-124..T-127, T-095); số bài, số bài có Hiểu nhanh hiện nay **chưa xác minh** vì không mở được site.

**(1) Nội dung**

- **Quy mô** (đếm trong báo cáo E2E, 26/10/2026): 171 bài — 151 ca dao, 20 vè; 0 tục ngữ, thành ngữ, đồng dao, câu đố. Từ đó T-125 đã cho tục ngữ, thành ngữ vào hàng Chờ duyệt; số đã đăng chưa xác minh.
- **Các lớp của một bài** (theo mã và `docs/02`):

  | Lớp | Có không | Ghi chú |
  |---|---|---|
  | Lời | ✔ | Khối Thơ; lục bát thụt câu lục; câu một dòng chữ to (T-095) |
  | Dị bản | ✔ | Đánh số, tô chữ khác lời chính (T-083). E2E: 46/171 bài |
  | Chú thích từ khó | ✔ | Chạm vào từ là hiện nghĩa (T-082). E2E: 101/171 bài |
  | Giải nghĩa (Hiểu nhanh) | một phần | Người biên soạn viết; E2E: 1/171 bài |
  | Bối cảnh | ✘ | Bối cảnh vè: T-102 READY, chưa làm |
  | Nguồn, xuất xứ | một phần | Lời không ghi nguồn (D-057). Chú thích chép từ nguồn tự động không còn dòng Nguồn (D-068); cuối bài "[N nguồn tham khảo đã ẩn]" (D-078) |
  | Mức kiểm chứng | ✔ | Dòng "Lời theo bản lưu truyền phổ biến · chưa đối chiếu sách" dưới lời (D-072); E2E: 0/171 bài đã đối chiếu sách |
  | Vùng miền | ✔ | Máy gợi ý, người duyệt (T-089); bản đồ `/ban-do/` |
  | Ghi âm | ✘ | Chỉ giọng máy có sẵn trên điện thoại (T-085); ghi âm thật để sau (T-067) |

- **Giấy phép:** phần dự án tự viết CC BY 4.0, mã MIT (D-037); ảnh Commons và đoạn chép Wikipedia ghi tác giả, giấy phép tại chỗ (D-061).
- **Chính tả:** lời giữ như nguồn; văn bản dự án đặt dấu theo SGK (D-038). Chưa kiểm mẫu.

**(2) Luồng người dùng** — đếm theo bản đồ luồng của báo cáo E2E, chưa đếm lại trên site:

| Luồng | Đường đi | Số lần chạm (ước lượng) |
|---|---|---|
| Từ Google vào một câu | Chưa áp dụng: site đang chặn lập chỉ mục, chờ quyết định mở (H5) | — |
| Tìm một câu | Thanh dưới "Tìm" → gõ → chạm kết quả | 2 chạm + gõ |
| Duyệt theo chủ đề | "Tất cả" → mở nhóm Chủ đề → chọn chủ đề → chạm bài | 4 |
| Học để dạy | Trang bài → "Trích dẫn" → "Sao chép kèm nguồn"; hoặc "Trình chiếu" | 2 (trích dẫn), 1 (trình chiếu) |

**(3) Tìm kiếm** (`inc/tim.php`, báo cáo E2E):

- Gõ không dấu: ✔ (chỉ mục có dấu và không dấu).
- Sai chính tả: ✘ — gõ nhầm một chữ là không ra bài (E2E L-09; mã chưa có phần sửa lỗi gõ).
- Xếp hạng: chữ liền nhau xếp trước, bài chỉ có chữ rải rác ở nhóm riêng cuối (T-126).
- Khi không ra: câu báo tử tế, lối sang Tất cả, Từ điển; chưa có "Có phải bạn tìm…?".
- Tìm cả trong chú thích, từ điển; kết quả hiện tên bài, dòng khớp và dòng mức kiểm.

**(4) Tính năng đặc biệt:** từ điển chú giải (`/tu-dien/`); bản đồ ca dao; Wikidata (ảnh Commons, bản đồ nhỏ, người xác nhận); mô-típ (T-092); "Bạn có biết?"; trích dẫn hai kiểu (thông dụng, APA 7); trình chiếu chữ lớn cho lớp học; "Đọc cùng bé" cho đồng dao; Đã lưu trên máy; góp ý, cảm nghĩ, bài phân tích của người đọc (qua Chờ duyệt); ảnh chia sẻ 1200×630 từng bài; lượt đọc không cookie. Chưa có: đố vui (T-096), dữ liệu mở (T-066), giọng sinh sẵn (T-105).

**(5) UX/UI** (báo cáo E2E): quét WCAG 2.2 AA 12 trang × 2 thiết bị, 0 vi phạm; không tràn ngang ở 390 px; chip, nút ≥ 44 px; thanh dưới trên điện thoại; không quảng cáo, không popup; mỗi trang khoảng 165 KB, 10 yêu cầu, TTFB 0,40–0,67 s, chưa có bộ nhớ đệm trang (L-13).

**(6) SEO** (`inc/seo.php`): tiêu đề, mô tả riêng từng trang; JSON-LD `CreativeWork`, `BreadcrumbList`, `WebSite` + `SearchAction`, `DefinedTerm` cho mục từ; canonical (T-127); sitemap chỉ gồm bài có Hiểu nhanh hoặc bài phân tích đã duyệt; phân loại dưới 3 bài thì `noindex`. Cả site đang chặn Google tới khi người sáng lập quyết định mở (H5). Bài nổi tiếng nhất có đường dẫn đuôi `-2` (L-14, H6).

**(7) Vận hành:** một người, dưới 10 giờ/tuần; phi lợi nhuận, không quảng cáo; nguồn tự động đổ bài Chờ duyệt mỗi ngày; tài trợ là câu hỏi còn mở (Q-008).

**(8) Mạnh / yếu sơ bộ:** mạnh ở trải nghiệm đọc trên điện thoại, trợ năng, trung thực về mức kiểm, công cụ cho giáo viên; yếu ở độ dày nội dung (Hiểu nhanh, tục ngữ) và chưa hiện trên Google. So với đối thủ: *chờ dữ liệu.*

### 3.2. Bằng chứng từ công cụ tìm kiếm (26/10/2026)

**Cách làm:** gõ từng câu vào công cụ tìm kiếm của phiên, đếm tên miền trong khoảng 9 kết quả đầu. Chưa mở trang nào trong số này. Công cụ tìm từ khu vực Mỹ, không phải Google Việt Nam; thứ hạng thật ở Việt Nam có thể khác.

| Câu tìm | Tên miền trong ~9 kết quả đầu (số kết quả) | Ca dao Mẹ | Thi viện |
|---|---|---|---|
| công cha như núi thái sơn ca dao | luatminhkhue.vn (4), hcmussh.edu.vn (1, tệp PDF), fptshop.com.vn, loigiaihay.com, hoatieu.vn, mytour.vn (1 mỗi trang) | ✘ | ✘ |
| có công mài sắt có ngày nên kim | hoatieu.vn (4), luatminhkhue.vn (2), prepedu.com, voh.com.vn, loigiaihay.com (1 mỗi trang) | ✘ | ✘ |
| bầu ơi thương lấy bí cùng | hoatieu.vn (3), loigiaihay.com (2), voh.com.vn, luatminhkhue.vn, luatduonggia.vn, vietjack.com (1 mỗi trang) | ✘ | ✘ |
| gió đưa cành trúc la đà | loigiaihay.com (4), hoatieu.vn (3), luatduonggia.vn, vietjack.com (1 mỗi trang) | ✘ | ✘ |
| thân em như tấm lụa đào | luatduonggia.vn (2), hoatieu.vn, lawnet.vn, luatminhkhue.vn, loigiaihay.com, vietjack.com, toplist.vn, mytour.vn (1 mỗi trang) | ✘ | ✘ |
| ăn quả nhớ kẻ trồng cây tục ngữ | hoatieu.vn (5), loigiaihay.com (2), luatminhkhue.vn, hoanghamobile.com (1 mỗi trang) | ✘ | ✘ |
| rồng rắn lên mây đồng dao lời | luatminhkhue.vn (3), baonghean.vn (3), loigiaihay.com (2), thegioididong.com (1) | ✘ | ✘ |
| trong đầm gì đẹp bằng sen | loigiaihay.com (4), hoatieu.vn (2), vnexpress.net, mytour.vn, doctailieu.com (1 mỗi trang) | ✘ | ✘ |

**Khi lọc riêng tên miền** (cadao.me, thivien.net, vi.wikisource.org, vi.wiktionary.org) cho câu "công cha như núi thái sơn", công cụ trả về:

- thivien.net: trang bài xếp dưới "Khuyết danh Việt Nam", vài trang "Phân tích bài ca dao…" và "Cảm nhận…" của người đọc, một trang "Giải nghĩa Thái Sơn";
- cadao.me: trang bài `/cong-cha-nhu-nui-thai-son/`;
- không có kết quả từ Wikisource, Wiktionary tiếng Việt.

**Đọc sơ bộ (chờ kiểm trên Google Việt Nam):**

- Người tìm một câu quen phần lớn là học sinh cần giải thích, phân tích để làm bài. Trang trả lời họ là trang văn mẫu, không phải kho ca dao.
- Nếu đúng như vậy trên Google Việt Nam, đối thủ SEO thật của phongdao.com là các trang văn mẫu. Lợi thế có thể là: lời rõ, giải nghĩa ngắn do người viết, mức kiểm trung thực, không quảng cáo.

### 3.3. Site Việt Nam khác

*Chưa mở được (mục 0).*

### 3.4. Site nước ngoài để so

*Chưa mở được (mục 0).*

## 4. Ma trận so sánh

*Chờ dữ liệu đối thủ.* Hàng dự kiến: quy mô; số thể loại; dị bản; chú thích từ khó; giải nghĩa; bối cảnh; nguồn từng câu; mức kiểm chứng; vùng miền; ghi âm; giấy phép mở; tìm không dấu; chịu sai chính tả; gợi ý khi không ra; trích dẫn; chế độ lớp học; lưu bài; đóng góp của người đọc; API, dữ liệu mở; đọc tốt ở 390 px; không quảng cáo, popup; trợ năng; dữ liệu có cấu trúc; hiện trên Google cho câu quen.

## 5. Phân tích khoảng trống của phongdao.com

*Chờ dữ liệu đối thủ.* Nhóm: nội dung, tính năng, UX/UI, SEO, vận hành.

## 6. Kế hoạch nâng cấp

*Chờ dữ liệu đối thủ.* Ba tầng: làm ngay (≤ 2 tuần), trước khi mở công khai, sau khi mở. Mỗi đề xuất ghi: tác động (cao / vừa / thấp), công sức (S / M / L), ai làm và số giờ của người sáng lập, task liên quan (T-xxx hoặc đề xuất task mới), lằn ranh hay Human Gate chạm tới.

## 7. Những gì không nên làm

*Chờ dữ liệu đối thủ.*

## 8. Câu hỏi cần người sáng lập quyết

**Q1. Cho agent mở các site cần khảo sát bằng cách nào?**

| Phương án | Hệ quả |
|---|---|
| A. Đổi quyền mạng của môi trường sang mở rộng (hoặc thêm từng tên miền được phép), rồi chạy lại khảo sát trong phiên mới | Khảo sát đầy đủ như yêu cầu: mở từng site, đo thời gian tải bằng Playwright, chụp ảnh lưu ở thư mục tạm |
| B. Giữ mạng như cũ; agent chỉ làm từ kết quả tìm kiếm | Không mở được trang nào: không xác minh được site còn chạy, không đo được UX, tốc độ, trợ năng. Báo cáo yếu, nhiều ô "chưa xác minh" |

**Khuyến nghị:** A. Phương án B không đáp ứng yêu cầu "chỉ liệt kê site đã mở được".
