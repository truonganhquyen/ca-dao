# Khảo sát các kho văn học dân gian và kế hoạch nâng cấp phongdao.com

| | |
|---|---|
| Ngày khảo sát | 26–27/10/2026 (mở trang ngày 27/10/2026) |
| Người làm | Claude Code (agent), theo yêu cầu của người sáng lập |
| Phạm vi | 9 site, ứng dụng Việt Nam; 4 site nước ngoài để so; phongdao.com |
| Cách làm | Playwright + Chromium, điện thoại 390 px và máy tính 1366 px; axe-core WCAG 2.2 AA; `npm run e2e` trên phongdao.com (chỉ đọc); công cụ tìm kiếm của phiên cho phần thứ hạng |
| Lằn ranh | Không chép lời, chú thích, giải nghĩa, ảnh của site khác. Ảnh chụp chỉ lưu ở thư mục tạm của phiên, không vào repo. Không ghi điều chưa thấy tận mắt; ước lượng ghi rõ cách tính |

## 0. Cách làm và giới hạn

**Mở trang như người đọc.** Mỗi site chỉ mở vài trang, cách nhau vài giây. Đã đọc `robots.txt` của từng site trước khi mở. Không đăng nhập, không gửi form. Có dùng ô tìm của site như người đọc thường (truy vấn GET), trừ chỗ `robots.txt` chặn đường tìm.

**Không vượt chặn.** Những trang dưới đây chặn trình duyệt tự động, nên không khảo sát:

- luatminhkhue.vn: trang "xác minh bảo mật" của Cloudflare. Site này có trong bảng thứ hạng (mục 3.3) vì xuất hiện trong kết quả tìm, nhưng không có hồ sơ.
- Ô tìm của hoatieu.vn: báo "truy cập có vẻ không hợp lệ".
- Trang chi tiết của Verhalenbank (kho truyện dân gian Hà Lan): thử thách chống máy tự động.
- Ô tìm của Wikisource, Wiktionary: `robots.txt` cấm đường tìm. Wikimedia còn trả lỗi 429 (quá nhiều yêu cầu) hai lần, nên chỉ mở rất ít trang.

**Số đo thời gian.** Mọi trang đi qua proxy của môi trường agent, đặt ở nước ngoài. Thời gian tuyệt đối chậm hơn người đọc ở Việt Nam: báo cáo E2E ngày 26/10 đo phongdao.com tải xong trong 0,6–1,0 s, còn lần này là 2,5–3,4 s. Chỉ nên so **giữa các site với nhau**. Dung lượng, số yêu cầu, quảng cáo, lỗi trợ năng thì không phụ thuộc vị trí.

**Thứ hạng tìm kiếm.** Công cụ tìm của phiên tìm từ khu vực Mỹ, **không phải Google Việt Nam**. Bảng ở mục 3.3 ghi đúng những tên miền đã thấy trong danh sách kết quả. Cần kiểm lại trên Google Việt Nam trước khi dùng làm căn cứ lớn.

**Ước lượng.** Quy mô của site ghi theo số site tự công bố, hoặc nhân số trang phân trang với số bài mỗi trang (đã đếm). Mỗi chỗ ghi rõ cách tính.

---

## 1. Tóm tắt cho người sáng lập

### 5 phát hiện quan trọng nhất

1. **Site thật đang chạy bản plugin cũ.** Site tải tệp của plugin bản 0.28.0, trong khi repo đã ở 0.33.0; theme thì đã đúng bản mới (0.19.0). Vì vậy các bản sửa đã gộp vẫn **chưa lên site**:
   - khoá cửa quản trị: XML-RPC vẫn bật, tên đăng nhập vẫn lộ qua REST, vẫn chèn được chữ lạ vào trang bài qua địa chỉ;
   - khối "Đọc tiếp", trang 404 riêng, canonical cho trang danh sách;
   - gộp "Tục ngữ – Thành ngữ", trang mô-típ, dòng "[N nguồn tham khảo đã ẩn]".

   Máy chủ tự cập nhật có vẻ đã dừng cập nhật plugin. Bộ kiểm E2E chạy ngày 27/10 xác nhận: 11 kiểm thử hỏng đúng ở các chỗ này.
2. **Đối thủ về tìm kiếm là các trang văn mẫu, không phải kho ca dao.** Với 13 câu và chủ đề quen, kết quả tìm gần như toàn là trang văn mẫu, lời giải bài tập:
   - loigiaihay.com có mặt ở 12/13 lần tìm, hoatieu.vn và luatminhkhue.vn ở 11/13;
   - Ca dao Mẹ và Thi viện ở 0/13 (chỉ hiện khi lọc riêng tên miền).

   Người tìm một câu quen phần lớn đang hỏi "câu này nghĩa là gì". Trang văn mẫu trả lời bằng bài dài, nặng quảng cáo (một trang bài của Lời giải hay tải 8 MB, 370 yêu cầu).
3. **Chưa site Việt Nam nào nói lời lấy từ sách nào, trang nào.**
   - Ca dao Mẹ không ghi nguồn từng bài; Thi viện ẩn nguồn ("[Thông tin 2 nguồn tham khảo đã được ẩn]"); trang văn mẫu không nhắc nguồn.
   - Site nước ngoài làm được: Aozora Bunko ghi tên sách gốc, nhà xuất bản, lần in dùng để nhập; kho thơ cổ Trung Quốc ghi sách tham khảo tới số trang; VWML ghi nguồn, trang, người hát, nơi hát cho từng bản.
   - Trên Wikisource tiếng Việt có hai sách thuộc phạm vi công cộng, có **bản quét từng trang**: *Câu hát góp* (Huỳnh Tịnh Của, bản in 1901) và *Tục ngữ, cổ ngữ, gia ngôn* (1897). Đây là đường ngắn nhất để nhiều bài có dòng "Đã đối chiếu: …, tr. N".
4. **phongdao.com hơn hẳn ở cách đọc, thua xa ở lượng bài.**
   - Trang bài nhẹ nhất trong các site Việt Nam đã đo: 177 KB, 12 yêu cầu, 0 quảng cáo, 0 lỗi trợ năng. Ca dao Mẹ khoảng 1 MB, Thi viện 3,2 MB kèm quảng cáo.
   - Site có những thứ không ai khác có: chạm từ để xem nghĩa, dòng mức kiểm, trích dẫn, trình chiếu.
   - Nhưng chỉ có 171 bài, 0 tục ngữ, và Hiểu nhanh có ở rất ít bài. Ca dao Mẹ khoảng 16.500 mục; Thi viện 13.285 bài thơ dân gian.
5. **Ca dao Mẹ mạnh ở kho và ô tìm, yếu trên điện thoại.**
   - Ô tìm chịu gõ không dấu và gõ sai.
   - Dấu chú thích chỉ rộng khoảng 12×9 px. Chạm vào thì trang nhảy xuống danh sách chú thích cuối trang, lẫn chú thích của các bài liên quan.
   - Trang tục ngữ chỉ có một dòng chữ, không giải nghĩa.
   - Mỗi bài có nút Thích, khung bình luận của Facebook: người đọc bị theo dõi bởi bên thứ ba.

### 5 việc nên làm trước

1. **Đưa site lên đúng bản plugin, rồi chạy lại bộ kiểm E2E** (bạn khoảng 30–60 phút trong FlashPanel; agent soạn hướng dẫn và kiểm lại). Kèm một kiểm tra tự động báo khi site thật lệch bản với repo.
2. **Đăng 50 câu tục ngữ, thành ngữ quen nhất trước.** Phần máy đưa tục ngữ vào hàng Chờ duyệt đã có trong repo nhưng cũng nằm trong bản plugin chưa lên site, nên việc này đi sau việc 1. Agent lập danh sách câu được tìm nhiều và soạn nháp Hiểu nhanh theo lô 10 câu. Bạn đọc, sửa, bấm Đăng: khoảng 2–3 giờ mỗi tuần.
3. **Khối nghĩa ngắn cho tục ngữ**: nghĩa đen, nghĩa bóng, dùng khi nào, câu gần nghĩa, trả lời đúng câu hỏi "nghĩa là gì" trong 10 giây, không văn mẫu, không quảng cáo.
4. **Đối chiếu lời với sách hết bản quyền có bản quét** (*Câu hát góp*, *Tục ngữ, cổ ngữ, gia ngôn*): máy tìm câu khớp và mở sẵn trang quét, bạn nhìn tận mắt rồi bấm xác nhận. Cần bạn quyết (câu hỏi Q3).
5. **Tìm chịu gõ sai** ("Có phải bạn tìm…?"), như Ca dao Mẹ đã làm được.

---

## 2. Danh sách site đã khảo sát

Ngày truy cập: 27/10/2026 cho mọi site (riêng phần tìm kiếm của mục 3.3: 26–27/10/2026).

| # | Site | Địa chỉ | Lý do chọn | Độ sâu |
|---|---|---|---|---|
| 0 | phongdao.com (đối chiếu) | <https://phongdao.com/> | Site của dự án | Sâu, kèm E2E |
| 1 | Ca dao Mẹ | <https://cadao.me/> | Kho chuyên ca dao lớn nhất; nguồn đầu mối của dự án (D-046) | Sâu |
| 2 | Thi viện | <https://www.thivien.net/> | Kho thơ lớn, có hơn 13.000 bài thơ dân gian, chú thích, ghi âm, bài phân tích | Sâu |
| 3 | Lời giải hay | <https://loigiaihay.com/> | Có mặt nhiều nhất trong kết quả tìm (12/13); gắn chương trình SGK | Vừa |
| 4 | Hoatieu | <https://hoatieu.vn/> | Có mặt 11/13; văn mẫu, mô hình gói trả phí | Vừa |
| 5 | VietJack | <https://vietjack.com/> | Trang "Ý nghĩa câu…" theo khuôn có cấu trúc | Vừa |
| 6 | VOH, mục Sống đẹp | <https://voh.com.vn/song-dep/> | Đài phát thanh có loạt bài giải nghĩa tục ngữ | Nhẹ |
| 7 | Wiktionary tiếng Việt | <https://vi.wiktionary.org/> | Từ điển mở có mục tục ngữ, thành ngữ (CC BY-SA) | Vừa |
| 8 | Wikisource tiếng Việt | <https://vi.wikisource.org/> | Sách hết bản quyền có bản quét, gồm sách ca dao, tục ngữ | Vừa |
| 9 | Ứng dụng trên Google Play | <https://play.google.com/> | 3 ứng dụng ca dao, tục ngữ (chỉ đọc trang giới thiệu, không cài) | Nhẹ |
| 10 | Poetry Foundation (Mỹ) | <https://www.poetryfoundation.org/> | Kho thơ lớn, có ghi âm, hướng dẫn đọc, bộ bài cho trường học | Vừa |
| 11 | Cổ văn đảo (Trung Quốc; tên cũ gushiwen.cn) | <https://www.guwendao.net/> | Thơ cổ có dịch, chú thích, thưởng tích, sách tham khảo tới số trang | Vừa |
| 12 | Aozora Bunko (Nhật) | <https://www.aozora.gr.jp/> | Thư viện tác phẩm hết bản quyền do tình nguyện viên nhập, soát; ghi rõ sách gốc | Vừa |
| 13 | VWML, chỉ mục Roud (Anh) | <https://www.vwml.org/> | Chỉ mục dân ca: mỗi bài hàng nghìn bản ghi, có nguồn, nơi, người hát | Vừa |

Không mở được, không có hồ sơ: luatminhkhue.vn, verhalenbank.nl (lý do ở mục 0).

---

## 3. Hồ sơ từng site

Tám mục: (1) nội dung, (2) luồng người dùng, (3) tìm kiếm, (4) tính năng đặc biệt, (5) UX/UI, (6) SEO, (7) vận hành, (8) nên học / nên tránh. Số đo ở bảng 3.2, thứ hạng ở bảng 3.3.

### 3.1. Hồ sơ

#### phongdao.com (đối chiếu)

- **(1) Nội dung.** Ngày 27/10, trang Tất cả ghi **171 bài**: 150 ca dao, 20 vè, 0 tục ngữ – thành ngữ. Thể thơ: lục bát 123, tự do 30, câu bốn chữ 15, câu năm chữ 3. Các lớp của một bài: lời; dị bản đánh số, tô chữ khác; chú thích chạm-là-hiện; Hiểu nhanh; dòng "Lời theo bản lưu truyền phổ biến · chưa đối chiếu sách"; chủ đề, vùng; từ điển; ảnh Commons. Độ dày (E2E 26/10): 101/171 bài có chú thích, 46/171 có dị bản, Hiểu nhanh rất ít, 0 bài đã đối chiếu sách. Giấy phép: phần tự viết CC BY 4.0.
- **(2) Luồng.** Chưa có luồng từ Google vì site còn chặn lập chỉ mục (chờ quyết định mở). Đếm trên điện thoại:
  - tìm một câu: chạm "Tìm" ở thanh dưới, gõ, chạm kết quả — 2 chạm;
  - duyệt chủ đề: Tất cả → mở nhóm Chủ đề → chọn chủ đề → chạm bài — 4 chạm;
  - học để dạy: Hiểu nhanh ngay dưới lời (0 chạm, khi bài có); "Trích dẫn" → "Sao chép kèm nguồn" (2 chạm); "Trình chiếu" (1 chạm).
- **(3) Tìm.** Gõ không dấu ra như có dấu. Gõ sai một chữ thì 0 kết quả (thử "cong cha nhu nui thai sonn"). Câu chưa có trong kho thì 0 kết quả, có lối sang Tất cả và Từ điển. Bản đang chạy trên site vẫn còn lỗi xếp hạng: bài vè dài lọt vào kết quả lạc đề (E2E).
- **(4) Tính năng.** Từ điển, bản đồ, Wikidata (người xác nhận), Bạn có biết?, trích dẫn (thông dụng, APA 7), trình chiếu, Đọc cùng bé, Đã lưu trên máy, góp ý và bài phân tích của người đọc (qua Chờ duyệt), ảnh chia sẻ, giọng đọc có sẵn của máy.
- **(5) UX/UI.** Điện thoại 390 px: tên bài, lời và Hiểu nhanh nằm trọn màn hình đầu, chữ lời lớn, lục bát thụt câu lục; thanh dưới 4 nút. 0 vi phạm axe, không tràn ngang, không quảng cáo, không lớp phủ. Trang bài 177 KB, 12 yêu cầu, không gọi tên miền ngoài.
- **(6) SEO.** Tiêu đề "<tên bài> – Kho tàng Ca dao Tục ngữ Việt Nam", mô tả riêng, JSON-LD `CreativeWork` + `BreadcrumbList`, canonical, ảnh chia sẻ. Trang chủ và trang Tất cả chưa có canonical vì bản sửa chưa lên site. Toàn site `noindex` tới khi mở.
- **(7) Vận hành.** Một người, dưới 10 giờ/tuần; máy lấy bài từ nguồn vào Chờ duyệt; không quảng cáo, không tài trợ (Q-008 còn mở).
- **(8) Kết quả `npm run e2e` (27/10, chỉ đọc).** 92 kiểm thử:
  - 70 đạt như mong đợi;
  - 6 lỗi đã biết vẫn còn: xếp hạng tìm, gõ sai, tệp mặc định lộ, thiếu CSP, không bộ nhớ đệm, chưa có tục ngữ;
  - 1 lỗi đã biết đã hết: bài mẫu "Hello world!" đã xoá;
  - 13 hỏng: 11 vì site chạy plugin 0.28.0 (XML-RPC, lộ tên đăng nhập, chèn chữ qua `?ly_do=`, Đọc tiếp, 404, tên bài trong API, kết quả lạc đề); 2 là ngưỡng tốc độ (1,02 s so với 1 s), có thể do proxy, chưa kết luận.

  Bộ kiểm cần sửa nhỏ mới chạy được trong môi trường agent: dùng Chromium cài sẵn thay Chrome, và cho yêu cầu API đi qua proxy.

#### Ca dao Mẹ (cadao.me)

- **(1) Nội dung.**
  - **Quy mô (ước lượng):** khoảng 16.540 mục = 827 trang × 20 bài (đã đếm 20 bài/trang). Theo thể loại: ca dao khoảng 11.860 (593 trang), thành ngữ – tục ngữ khoảng 3.520 (176), câu đố khoảng 760 (38), đồng dao khoảng 320 (16), vè khoảng 140 (7). Chú thích khoảng 9.200 (460 trang × 20).
  - **Các lớp của một bài:** lời; dị bản đánh số ngay dưới lời (không tô chỗ khác, không ghi vùng hay nguồn); chú thích có ảnh; chủ đề, thẻ; người đăng; "Bạn có biết?" (mới thấy, `docs/25` mục 2.2 ghi là chưa có).
  - **Không có:** nguồn từng bài; giải nghĩa cả câu (trang "Có công mài sắt có ngày nên kim" chỉ có đúng một dòng); ghi âm.
  - **Bản quyền:** "© 2026 Ca dao Mẹ", không giấy phép. Trang Hệ thống chú thích tự ghi không dám đảm bảo mọi chú thích chính xác.
- **(2) Luồng (điện thoại).**
  - Tìm: kính lúp → gõ → chạm kết quả — 2 chạm.
  - Chủ đề: trình đơn → mở "Ca dao – Dân ca" → chọn chủ đề → chạm bài — 4 chạm; trang chủ đề xếp theo thời gian, 46 trang, không lọc, không xếp A–Z.
  - Học để dạy: không có trích dẫn, trình chiếu, in. Muốn đọc chú thích phải nhảy xuống cuối trang rồi bấm "^" quay lại.
- **(3) Tìm.** Tìm tức thì (Meilisearch ở `search.cadao.me`). Thử ngày 27/10: "cong cha nhu nui" và "co cong mai sat" ra như có dấu; "cong cha nhu nui thai sonn" vẫn ra đúng bài đầu. Xếp hạng hợp lý. Không ra thì chỉ báo "Không tìm ra kết quả nào.", không gợi ý.
- **(4) Tính năng.** Trang từng chú thích có "Những nội dung có dùng chú thích này" và form "Đóng góp phiên bản tốt hơn" (xin nguồn, ảnh); "Bạn có biết?"; "Một số bài ngẫu nhiên"; mây thẻ; RSS. `docs/12` (23/10) ghi có API công khai cho ca dao; lần này chưa kiểm lại.
- **(5) UX/UI.**
  - Điện thoại: lời hiện ngay đầu trang. Dấu chú thích rộng khoảng 12×9 px. Chạm vào thì trang nhảy tới danh sách chú thích cuối trang (nền tối), lẫn chú thích của các bài trong khối "Cùng thể loại".
  - Mỗi bài trong danh sách có nút Thích của Facebook, cuối bài có khung bình luận Facebook. Trang bài khoảng 1 MB, 111 yêu cầu, gọi 14 tên miền ngoài; 4 loại lỗi axe (44 chỗ tương phản thấp, 25 liên kết không tên).
  - Máy tính: cột nội dung hẹp khoảng 460 px giữa ảnh nền lớn.
  - Trang chủ là dòng bài mới đăng, không chọn lọc. Ngày 27/10, cả trang đầu là những câu có từ thô tục: người đọc lần đầu, giáo viên sẽ thấy ngay.
- **(6) SEO.** Tiêu đề rõ ("Bài ca dao: … | Ca dao Mẹ"; "Thành ngữ – tục ngữ: … | Ca dao Mẹ"). Trang bài không có mô tả, không có ảnh chia sẻ; mỗi trang có 44–50 thẻ H1; có JSON-LD `Article`, `BreadcrumbList`. Không có mặt trong 13 lần tìm (mục 3.3).
- **(7) Vận hành.** Dự án độc lập, phi lợi nhuận, 11 thành viên hiện tại; có học giả hỗ trợ; không quảng cáo; trang Facebook có khoảng 6.400 người theo dõi; nhận đóng góp chú thích qua form.
- **(8) Nên học:** ô tìm chịu sai chính tả; trang từng chú thích liệt kê mọi bài dùng nó; form góp ý chú thích xin nguồn. **Nên tránh:** chú thích khó chạm trên điện thoại; trang tục ngữ một dòng; trang chủ không chọn lọc; plugin Facebook; nhiều H1.

#### Thi viện (thivien.net)

- **(1) Nội dung.**
  - **Quy mô (site tự công bố):** mục "Khuyết danh Việt Nam" có 14.075 bài; nhóm "Thơ dân gian" 13.285 bài, gồm ca dao 12.104, tục ngữ 778, vè – đồng dao 548. Toàn site 114.087 bài thơ, 5.310 tác/dịch giả (dòng chữ trong ô tìm).
  - **Các lớp của một bài:** đường dẫn thể loại và nhóm chủ đề; thể thơ; thời kỳ; từ khoá kèm số bài; chấm sao; người đăng, ngày đăng (bài khảo sát đăng năm 2007).
  - **Ghi âm:** bài khảo sát có ghi âm "Hát ru" dài 1:59.
  - **Chú thích:** đánh số, có toạ độ cho địa danh, ảnh, "Có 8 nội dung dùng giải nghĩa", "Có 8 bài cùng chú thích".
  - **Bài phân tích, cảm nhận** của người đọc nằm ngay dưới bài.
  - **Nguồn:** ẩn ("[Thông tin 2 nguồn tham khảo đã được ẩn]"). Bản quyền: "© 2004-2026 Vanachi".
- **(2) Luồng (ước lượng theo trình đơn đã thấy).** Tìm theo nội dung: trình đơn → Thơ → Tìm thơ… → điền ô Nội dung → Tìm → chạm kết quả, khoảng 6 chạm. Duyệt chủ đề: Khuyết danh Việt Nam → Thơ dân gian → Ca dao → nhóm chủ đề → bài, khoảng 6–7 chạm. Học để dạy: có "In bài thơ"; không có trích dẫn.
- **(3) Tìm.** Thử trên trang Tìm thơ:
  - "Công cha như núi" (có dấu): ra 2 bài;
  - "cong cha nhu nui" (không dấu): 0 kết quả;
  - thêm một chữ gõ sai: 0 kết quả;
  - tìm theo nội dung "có công mài sắt": ra nhiều bài, xếp theo A–Z, câu đúng đứng thứ 4.
- **(4) Tính năng.** Ghi âm; chú thích có bản đồ, ảnh; bài phân tích của người đọc; nhóm theo chủ đề; diễn đàn; "Bình luận nhanh" mở cho khách.
- **(5) UX/UI.**
  - Điện thoại: màn hình đầu **không thấy lời**. Dưới thanh tìm là một khoảng trống cao (chỗ quảng cáo); hộp nổi "Bạn bị lạc trong Thi Viện…" che phần dưới.
  - Đầu trang là khung "Bình luận nhanh" với các bình luận không liên quan tới bài.
  - Trang bài 3,2 MB, 98 yêu cầu, khoảng 25 yêu cầu tới mạng quảng cáo; 7 loại lỗi axe, có lỗi nghiêm trọng (ảnh không có chữ thay thế, ô chọn không nhãn).
- **(6) SEO.** Tiêu đề "Bài thơ: <tên> (Khuyết danh Việt Nam)", mô tả, canonical, JSON-LD `CreativeWorkSeries`. Ca dao bị xếp là "Bài thơ" của "Khuyết danh". Không có mặt trong 13 lần tìm.
- **(7) Vận hành.** Cộng đồng thành viên đăng bài, dịch, phân tích; có quảng cáo; đăng nhập bằng Google.
- **(8) Nên học:** ghi âm gắn từng bài; chú thích dùng chung có toạ độ; "bài cùng chú thích". **Nên tránh:** ẩn nguồn; bình luận mở không duyệt; tìm không chịu không dấu; quảng cáo đẩy lời xuống dưới.

#### Lời giải hay (loigiaihay.com)

- **(1) Nội dung.**
  - Trang "4000+ Ca dao tục ngữ, thành ngữ Việt Nam chọn lọc" có chỉ mục theo chữ cái và theo chủ đề. Trang đầu có khoảng 300 liên kết tới trang từng câu (đếm); con số 4000+ là số site tự ghi.
  - **Trang "Ý nghĩa câu…":** thể loại, nhóm, một câu chuyện kèm theo.
  - **Trang bài văn ("Em hãy nêu cảm nhận…"):** dàn ý và 4 bài mẫu, gắn bài SGK Văn 7.
  - Kết quả tìm còn có cả loạt trang "<thành ngữ> là gì? Ý nghĩa thành ngữ…".
  - Không ghi nguồn lời, không dị bản.
- **(2) Luồng.** Từ Google vào thẳng trang câu: nghĩa có ngay (0 chạm) nhưng phải cuộn qua quảng cáo. Chủ đề: trang chỉ mục → chủ đề → câu, 2 chạm.
- **(3) Tìm.** Ô tìm dùng Google tìm trong site (cse.google.com); không thử.
- **(4) Tính năng.** Chấm điểm, "Báo lỗi – Góp ý", bình luận, ứng dụng riêng, liên kết khoá học.
- **(5) UX/UI.** Điện thoại: dải quảng cáo ứng dụng trên cùng, banner khoá học, khối "QUẢNG CÁO" trước nội dung. Trang bài 8,2 MB, 370 yêu cầu, khoảng 103 yêu cầu quảng cáo, 96 tên miền ngoài; tải xong khoảng 10 s (qua proxy); 7 loại lỗi axe.
- **(6) SEO.** Tiêu đề khớp đúng câu người ta gõ; mô tả đủ dài; JSON-LD `Article`; có mặt 12/13 lần tìm.
- **(7) Vận hành.** Doanh nghiệp giáo dục: quảng cáo, khoá học, ứng dụng.
- **(8) Nên học:** gắn câu với bài học trong SGK; chỉ mục A–Z và chủ đề cho người học. **Nên tránh:** bài mẫu dài để học sinh chép; trang quá nặng.

#### Hoatieu (hoatieu.vn)

- **(1) Nội dung.** Trang "Top 7 mẫu giải thích câu tục ngữ…": dàn ý, sơ đồ tư duy, nhiều bài mẫu dài ngắn; ghi "Lớp 7", "Dạng tài liệu: Văn mẫu"; không nguồn lời.
- **(2) Luồng.** Từ Google vào: hộp mua gói Pro và mục lục đứng trước nội dung.
- **(3) Tìm.** Ô tìm của site chặn trình duyệt tự động; không thử được.
- **(4) Tính năng.** Gói Hoatieu Pro 99.000 đ: "KHÔNG quảng cáo", tải tệp nhanh, "Tiện ích AI"; tư vấn qua Zalo; chấm điểm.
- **(5) UX/UI.** Điện thoại: hộp quảng bá gói Pro chiếm gần nửa màn hình đầu; nút Zalo, gọi điện nổi bên phải. Trang bài 1,5 MB (máy tính 3,8 MB), 131 yêu cầu, có Microsoft Clarity (ghi lại thao tác người đọc); 5 loại lỗi axe; thẻ `html` không khai ngôn ngữ.
- **(6) SEO.** Tiêu đề kiểu "Top 7 mẫu…"; JSON-LD có `AggregateRating`; có mặt 11/13 lần tìm.
- **(7) Vận hành.** Quảng cáo và gói trả phí bỏ quảng cáo.
- **(8) Nên tránh:** mọi thứ trong mô hình này (mục 7). **Nên học:** họ biết đúng câu nào học sinh đang tìm; danh sách câu trong bài học là tín hiệu tốt để chọn câu làm trước.

#### VietJack (vietjack.com)

- **(1) Nội dung.** Loạt trang "Ý nghĩa <câu> (chi tiết nhất)" theo một khuôn cố định:
  1. thể loại, nhóm chủ đề;
  2. giải thích từ ngữ, hình ảnh;
  3. nghĩa đen, nghĩa bóng;
  4. ý nghĩa, bài học;
  5. trường hợp áp dụng (trong văn học, trong giáo dục);
  6. câu liên quan.

  Có trang tổng "1000+ Ca dao, tục ngữ, thành ngữ" và "56+ thành ngữ bắt đầu bằng A". Không nguồn lời.
- **(2) Luồng.** Từ Google vào: nghĩa có ngay sau phần quảng bá đề thi.
- **(3) Tìm.** Google tìm trong site; không thử.
- **(4) Tính năng.** Liên kết câu liên quan; quảng bá khoá học, tài liệu.
- **(5) UX/UI.** Trang bài 1,4 MB, 106 yêu cầu, khoảng 9 yêu cầu quảng cáo; 4 loại lỗi axe.
- **(6) SEO.** Tiêu đề "Ý nghĩa <câu> (chi tiết nhất)"; có mặt 5/13 lần tìm.
- **(7) Vận hành.** Doanh nghiệp giáo dục.
- **(8) Nên học:** khuôn "nghĩa đen · nghĩa bóng · dùng khi" rất hợp tục ngữ, nhưng nên làm ngắn, có nguồn và có người duyệt. **Nên tránh:** khuôn lặp làm hàng loạt.

#### VOH, mục Sống đẹp (voh.com.vn)

- **(1)** Bài giải thích một câu tục ngữ: mục lục, nghĩa, "tiếng Anh là gì", ý nghĩa, câu cùng chủ đề; danh sách "100 câu… về …". Không nguồn lời.
- **(5)** Trang 1,8 MB (máy tính 3,2 MB, 231 yêu cầu, khoảng 62 yêu cầu quảng cáo). Lần đo trên điện thoại chậm bất thường (57 s), chỉ một lần đo, không kết luận.
- **(6)** Có mặt 4/13 lần tìm.
- **(7)** Đài phát thanh có quảng cáo.

#### Wiktionary tiếng Việt (vi.wiktionary.org)

- **(1) Nội dung.** Mục "có công mài sắt có ngày nên kim" ghi là tục ngữ; nghĩa một dòng; phiên âm IPA theo ba giọng Hà Nội, Huế, Sài Gòn; sửa lần cuối 5 năm trước. Tên mục phải gõ đúng: thêm dấu phẩy là 404. Quy mô theo đo của dự án ngày 25/10 (`docs/25` mục 2.3): 657 thành ngữ, 325 tục ngữ. Giấy phép CC BY-SA 4.0.
- **(3) Tìm.** Không thử (`robots.txt` chặn đường tìm).
- **(5) UX/UI.** Nhẹ (405 KB), không quảng cáo; 1 lỗi axe (ô không nhãn).
- **(6) SEO.** Không có mặt trong 13 lần tìm; không có mô tả trang.
- **(8) Nên học:** phát âm theo ba vùng; giấy phép mở. **Nên tránh:** nghĩa một dòng không nguồn.

#### Wikisource tiếng Việt (vi.wikisource.org)

- **(1) Nội dung.**
  - ***Câu hát góp*** (Huỳnh Tịnh Của, bản in thứ hai, 1901): chép theo từng trang, mỗi trang liên kết tới bản quét (`Mục lục:Cau_hat_gop.pdf`, `Trang:Cau_hat_gop.pdf/N`). Hộp giấy phép ghi tác phẩm phát hành trước 1931, thuộc phạm vi công cộng trên toàn thế giới.
  - ***Tục ngữ, cổ ngữ, gia ngôn*** (1897, cùng tác giả): chia theo chữ cái, có bản quét (`Mục lục:Tục_ngữ,_cổ_ngữ,_gia_ngôn.djvu`).
  - Hai sách này **chưa có** trong danh mục nguồn mở của `docs/25` mục 2.3.
- **(3)** Không thử tìm (`robots.txt`).
- **(8) Nên học:** gắn chữ với trang quét; trạng thái soát của từng trang.

#### Ứng dụng di động (Google Play, chỉ đọc trang giới thiệu)

| Ứng dụng | Nhà phát triển | Nội dung tự giới thiệu | Đánh giá, lượt tải | Ghi chú |
|---|---|---|---|---|
| Ca dao – Tục ngữ Việt Nam | HaDoSolution | Hơn 17.000 câu; đọc khi không có mạng; mỗi lần mở xáo ngẫu nhiên, vuốt để đánh dấu "Đã xem" | 4,6★ (39 đánh giá), 10 N+ | Chứa quảng cáo |
| Ca Dao – Tục Ngữ – Thành Ngữ | Aregames | Hơn 10.000 ca dao, hơn 7.000 tục ngữ thành ngữ; A–Z | 3,9★ (826), 100 N+ | Chứa quảng cáo |
| Bắt Ca Dao, Đào Tục Ngữ | WePlay VN | Trò chơi ghép chữ, có giải nghĩa sau mỗi đáp án | 3,7★ (566), 100 N+ | Chứa quảng cáo |

Nên học: nhu cầu đọc khi không có mạng và "mỗi ngày một câu" là có thật. Nên tránh: làm ứng dụng riêng (`docs/00`: web tốt trên điện thoại là đủ).

#### Poetry Foundation (Mỹ)

- **(1)** Trang bài: lời thơ, nút nghe ghi âm, "Poem Guide" (bài hướng dẫn đọc), dòng "Copyright Credit". Bài nằm trong các bộ chủ đề, trong đó có bộ "Common Core State Standards Text Exemplars" (bài mẫu theo chuẩn chương trình học).
- **(3)** Tìm có bộ lọc chủ đề, dịp, ngày lễ; chưa đọc được kết quả (trang tải kết quả bằng JavaScript), **chưa xác minh**.
- **(4)** Sáng/tối, liên kết "See a problem on this page?" (báo lỗi), ứng dụng riêng.
- **(5)** Trang bài 2 MB, 157 yêu cầu; 2 loại lỗi axe.
- **(6)** `robots.txt` có dòng tín hiệu nội dung: cho tìm kiếm, cấm dùng để huấn luyện AI.
- **(8) Nên học:** bộ bài theo chương trình học cho giáo viên; nút báo lỗi ngay trên trang; ghi âm.

#### Cổ văn đảo (guwendao.net, tên cũ gushiwen.cn, Trung Quốc)

- **(1)** Trang một bài: nguyên văn; khối "dịch và chú thích"; "thưởng tích"; "hoàn cảnh sáng tác"; "phân tích ngắn". Cuối khối dịch có **"tài liệu tham khảo"**: tên sách, chủ biên, nhà xuất bản, năm và số trang. Tiêu đề trang ghi có bản phiên âm và bản đọc. Trang tác giả đếm số bài, số câu nổi tiếng.
- **(5)** Bản điện thoại rất nhẹ: 115 KB, 17 yêu cầu, không quảng cáo; có lời mời mở ứng dụng. 6 loại lỗi axe.
- **(8) Nên học:** mỗi lớp giải thích có tên riêng, có sách tham khảo tới trang; tách "nghĩa" khỏi "bình".

#### Aozora Bunko (Nhật)

- **(1)** Mỗi tác phẩm có một "thẻ thư mục":
  - **"Sách gốc":** tên sách, nhà xuất bản, ngày in lần đầu, **lần in đã dùng để nhập**; thêm "sách gốc của sách gốc";
  - **người nhập, người soát** (tình nguyện viên);
  - tệp tải về (văn bản, XHTML) kèm ngày đăng, ngày sửa;
  - mã phân loại thư viện; ghi chú khác biệt giữa hai bản in.
- **(5)** Thẻ 298 KB; trang đọc 201 KB, 5 yêu cầu.
- **(8) Nên học:** ghi sách gốc tới lần in; tách vai "nhập" và "soát"; tệp mở để tải.

#### VWML, chỉ mục Roud (Anh)

- **(1)** Bài số Roud 54 có 1.995 bản ghi, lọc được theo dạng (ghi âm, sách, bản thảo, tờ in rời, tạp chí), nội dung (chữ, âm thanh), nơi in, nước. Mỗi bản ghi có: mã, tên, dòng đầu, số Roud, dạng, nguồn kèm số trang ("version f"), người hát, nơi. Xếp được theo năm, người hát, nơi, người sưu tầm.
- **(5)** Trang kết quả 2,2 MB; hỏi đồng ý cookie trước khi bật đo lường.
- **(8) Nên học:** một bài là một cụm nhiều bản ghi, mỗi bản ghi có nguồn tới trang, người, nơi — đúng mô hình "tác phẩm – dị bản" của dự án (D-004).

### 3.2. Số đo trang bài trên điện thoại 390 px (27/10/2026)

Đo một lần mỗi trang, qua proxy (mục 0). "Yêu cầu quảng cáo" đếm theo mẫu tên miền mạng quảng cáo (ước lượng). "Lỗi axe" là số loại quy tắc WCAG 2.2 AA bị vi phạm.

| Site (trang đo) | Tải xong | Dung lượng | Yêu cầu | Tên miền ngoài | Yêu cầu quảng cáo | Đo lường bên thứ ba | Lỗi axe |
|---|---|---|---|---|---|---|---|
| phongdao.com (bài) | 2,9 s | 177 KB | 12 | 0 | 0 | không | 0 |
| Ca dao Mẹ (bài) | 2,8 s | 1.062 KB | 111 | 14 | 0 | Google Analytics, Facebook | 4 |
| Thi viện (bài) | 6,4 s | 3.209 KB | 98 | 21 | 25 | Google, Facebook | 7 |
| Lời giải hay (bài) | 10,3 s | 8.233 KB | 370 | 96 | 103 | Google, Facebook, Pangle, Yahoo | 7 |
| Hoatieu (bài) | 9,9 s | 1.479 KB | 131 | 15 | 7 | Google, Microsoft Clarity, Bing | 5 |
| VietJack (bài) | 6,0 s | 1.373 KB | 106 | 21 | 9 | Google, Facebook | 4 |
| VOH (bài) | 57 s (bất thường) | 1.823 KB | 107 | 7 | 10 | (máy tính: Google, Clarity, Yahoo, Bing) | 1 |
| Wiktionary (mục từ) | 1,3 s | 405 KB | 25 | 1 | 0 | không | 1 |
| Poetry Foundation (bài) | 3,7 s | 1.958 KB | 157 | 13 | 6 | Google, Facebook | 2 |
| Cổ văn đảo (bài) | 4,6 s | 115 KB | 17 | 3 | 0 | không | 6 |
| Aozora Bunko (trang đọc) | 2,3 s | 201 KB | 5 | 0 | 0 | không | 1 |
| VWML (trang kết quả) | 2,6 s | 2.240 KB | 130 | 6 | 0 (2 yêu cầu bị mẫu đếm nhầm) | Google Tag Manager, mặc định từ chối cookie | 0 |

Trang chủ, trang danh sách cùng xu hướng: phongdao.com 167–168 KB, 11 yêu cầu; Ca dao Mẹ 0,86–1,4 MB, khoảng 100 yêu cầu; Thi viện 2–2,9 MB, khoảng 21 yêu cầu quảng cáo. Trang chỉ mục của Lời giải hay 2,9 MB, 181 yêu cầu.

### 3.3. Hiện diện trong kết quả tìm kiếm (26–27/10/2026)

Công cụ tìm của phiên, khu vực Mỹ, khoảng 9 kết quả đầu (mục 0). Số trong ngoặc là số kết quả của tên miền đó.

| Câu tìm | Tên miền trong kết quả |
|---|---|
| công cha như núi thái sơn ca dao | luatminhkhue (4), hcmussh.edu.vn (PDF), fptshop, loigiaihay, hoatieu, mytour |
| có công mài sắt có ngày nên kim | hoatieu (4), luatminhkhue (2), prepedu, voh, loigiaihay |
| bầu ơi thương lấy bí cùng | hoatieu (3), loigiaihay (2), voh, luatminhkhue, luatduonggia, vietjack |
| gió đưa cành trúc la đà | loigiaihay (4), hoatieu (3), luatduonggia, vietjack |
| thân em như tấm lụa đào | luatduonggia (2), hoatieu, lawnet, luatminhkhue, loigiaihay, vietjack, toplist, mytour |
| ăn quả nhớ kẻ trồng cây tục ngữ | hoatieu (5), loigiaihay (2), luatminhkhue, hoanghamobile |
| rồng rắn lên mây đồng dao lời | luatminhkhue (3), baonghean (3), loigiaihay (2), thegioididong |
| trong đầm gì đẹp bằng sen | loigiaihay (4), hoatieu (2), vnexpress, mytour, doctailieu |
| uống nước nhớ nguồn tục ngữ ý nghĩa | luatminhkhue (5), hoatieu (3), loigiaihay |
| thương người như thể thương thân | loigiaihay (5), luatminhkhue (2), hoatieu, vietcetera |
| ca dao về mẹ | dienmaycholon (2), voh (2), fptshop, hoatieu, luatminhkhue, vietjack, toplist |
| ca dao tục ngữ về thầy cô | hoatieu (2), hoanghamobile (2), luatminhkhue (2), fptshop, loigiaihay, voh |
| ăn cháo đá bát nghĩa là gì | loigiaihay (5), luatminhkhue (2), vietjack (2) |

**Tổng:** loigiaihay.com có mặt 12/13 lần; hoatieu.vn 11/13; luatminhkhue.vn 11/13; vietjack.com 5/13; voh.com.vn 4/13; cadao.me, thivien.net, Wiktionary, Wikisource 0/13.

Khi lọc riêng tên miền (cadao.me, thivien.net, Wikisource, Wiktionary) với câu "công cha như núi thái sơn", công cụ trả về trang bài của Thi viện, các trang "Phân tích…", "Cảm nhận…" của người đọc Thi viện, trang "Giải nghĩa Thái Sơn", và trang bài của Ca dao Mẹ.

**Đọc kết quả:**

- 10/13 lần tìm là một câu cụ thể. Trang trả lời là trang "giải thích, phân tích, văn mẫu", phục vụ học sinh làm bài.
- Hai lần tìm theo chủ đề ("ca dao về mẹ", "về thầy cô") ra các bài tổng hợp của trang bán hàng, trang tin.
- Muốn có mặt ở đây, trang bài của phongdao.com phải có phần chữ riêng trả lời "nghĩa là gì" (Hiểu nhanh, khối nghĩa). Đó cũng là điều kiện để trang được lập chỉ mục (D-043, T-050).

---

## 4. Ma trận so sánh

Ký hiệu: ✔ có, làm tốt · ◐ một phần · ✘ không · ? chưa xác minh · – không áp dụng.
Cột: **PĐ** phongdao.com · **CDM** Ca dao Mẹ · **TV** Thi viện · **LGH** Lời giải hay · **HT** Hoatieu · **VJ** VietJack · **VOH** · **Wkt** Wiktionary · **Wks** Wikisource · **App** ứng dụng Google Play.

### 4.1. Site Việt Nam

| Tiêu chí | PĐ | CDM | TV | LGH | HT | VJ | VOH | Wkt | Wks | App | Ghi chú |
|---|---|---|---|---|---|---|---|---|---|---|---|
| Quy mô lớn (≥ 5.000 câu) | ✘ | ✔ | ✔ | ◐ | ? | ◐ | ✘ | ✘ | ◐ | ✔ | PĐ 171 bài; CDM khoảng 16.540; TV 13.285 thơ dân gian; LGH tự ghi 4000+ |
| Đủ thể loại dân gian | ◐ | ✔ | ◐ | ◐ | ◐ | ◐ | ◐ | ◐ | ◐ | ◐ | PĐ đang có bài ở 2 thể (ca dao, vè) |
| Dị bản | ✔ | ◐ | ◐ | ✘ | ✘ | ✘ | ✘ | ✘ | – | ? | PĐ đánh số, tô chỗ khác; CDM đánh số, không tô; TV để thành bài riêng |
| Chú thích từ khó | ✔ | ◐ | ✔ | ◐ | ◐ | ✔ | ◐ | – | ◐ | ? | CDM nhiều nhưng trên điện thoại phải nhảy xuống cuối trang; TV có toạ độ, ảnh |
| Giải nghĩa cả câu | ◐ | ✘ | ◐ | ✔ | ✔ | ✔ | ✔ | ✔ | ◐ | ◐ | PĐ có Hiểu nhanh ở rất ít bài; TV qua bài của người đọc; Wks: sách 1897 có lời giải |
| Nguồn, xuất xứ của lời | ◐ | ✘ | ✘ | ✘ | ✘ | ✘ | ✘ | ✘ | ✔ | ✘ | PĐ có dòng mức kiểm, nguồn ẩn; TV ẩn nguồn; Wks gắn bản quét từng trang |
| Nói rõ mức kiểm chứng | ✔ | ✘ | ✘ | ✘ | ✘ | ✘ | ✘ | ✘ | ? | ✘ | Chỉ PĐ có dòng "chưa đối chiếu sách" |
| Vùng miền | ✔ | ◐ | ◐ | ✘ | ✘ | ✘ | ✘ | ◐ | ✘ | ? | PĐ vùng, bản đồ; CDM thẻ địa danh; Wkt phát âm 3 giọng |
| Ghi âm, nghe | ◐ | ✘ | ✔ | ✘ | ✘ | ✘ | ✘ | ✘ | ✘ | ? | PĐ giọng máy có sẵn; TV ghi âm hát ru ở bài khảo sát |
| Giấy phép tái sử dụng rõ | ✔ | ✘ | ✘ | ✘ | ✘ | ✘ | ✘ | ✔ | ✔ | ✘ | PĐ CC BY phần tự viết; Wkt CC BY-SA; Wks phạm vi công cộng |
| Tìm gõ không dấu | ✔ | ✔ | ✘ | ? | ? | ? | ? | ? | ? | ? | LGH, VJ dùng Google; HT chặn tự động |
| Tìm chịu gõ sai | ✘ | ✔ | ✘ | ? | ? | ? | ? | ? | ? | ? | CDM ra đúng bài khi gõ "sonn" |
| Gợi ý khi không ra | ◐ | ✘ | ✘ | ? | ? | ? | ? | ? | ? | ? | PĐ có lối sang Tất cả, Từ điển; chưa có "Có phải bạn tìm…?" |
| Trích dẫn sẵn | ✔ | ✘ | ✘ | ✘ | ✘ | ✘ | ✘ | ? | ? | ✘ | PĐ hai kiểu, có APA 7 |
| Dùng trên lớp (trình chiếu, in, gắn SGK) | ✔ | ✘ | ◐ | ◐ | ◐ | ◐ | ✘ | ✘ | ◐ | ✘ | PĐ trình chiếu; TV in bài; LGH, HT, VJ gắn bài học theo lớp |
| Lưu bài | ✔ | ✘ | ? | ✘ | ? | ✘ | ✘ | – | – | ✔ | PĐ lưu trên máy, không tài khoản |
| Người đọc đóng góp | ✔ | ✔ | ✔ | ◐ | ◐ | ✘ | ✘ | ✔ | ✔ | ✘ | PĐ qua Chờ duyệt; CDM form chú thích; TV đăng bài, phân tích |
| Dữ liệu mở, API | ✘ | ◐ | ✘ | ✘ | ✘ | ✘ | ✘ | ✔ | ✔ | ✘ | CDM có API theo `docs/12`, chưa kiểm lại |
| Lời trong màn hình đầu (390 px) | ✔ | ✔ | ✘ | ◐ | ◐ | ? | ? | ✔ | ? | – | TV: khoảng trống quảng cáo, hộp nổi |
| Không quảng cáo, không theo dõi bên thứ ba | ✔ | ◐ | ✘ | ✘ | ✘ | ✘ | ✘ | ✔ | ✔ | ✘ | CDM không quảng cáo nhưng có Facebook, Google Analytics |
| Trợ năng (axe trang bài) | ✔ | ◐ | ✘ | ✘ | ◐ | ◐ | ✔ | ✔ | ? | ? | Số loại lỗi: 0 / 4 / 7 / 7 / 5 / 4 / 1 / 1 |
| Trang nhẹ (< 500 KB) | ✔ | ✘ | ✘ | ✘ | ✘ | ✘ | ✘ | ✔ | ? | – | 177 KB so với 1–8 MB |
| Dữ liệu có cấu trúc | ✔ | ✔ | ✔ | ✔ | ✔ | ◐ | ✔ | ✘ | ? | – | Ai cũng có ít nhất `BreadcrumbList` hoặc `Article` |
| Có mặt khi tìm câu quen (13 lần) | – | ✘ | ✘ | ✔ | ✔ | ◐ | ◐ | ✘ | ✘ | – | PĐ đang chặn Google; LGH 12, HT 11, VJ 5, VOH 4 |

### 4.2. Site nước ngoài

Cột: **PF** Poetry Foundation · **CVĐ** Cổ văn đảo · **AZ** Aozora Bunko · **VWML**.

| Tiêu chí | PĐ | PF | CVĐ | AZ | VWML | Ghi chú |
|---|---|---|---|---|---|---|
| Nguồn của văn bản tới sách, trang | ◐ | ◐ | ✔ | ✔ | ✔ | PF "Copyright Credit"; CVĐ sách tham khảo tới trang; AZ lần in dùng để nhập; VWML nguồn tới trang |
| Lớp giải thích có tên, tách khỏi lời bình | ✔ | ✔ | ✔ | – | – | CVĐ: dịch – chú thích – thưởng tích – hoàn cảnh; PF Poem Guide |
| Ghi âm | ◐ | ✔ | ? | ✘ | ✔ | CVĐ ghi có bản đọc, chưa nghe thử |
| Bộ bài cho trường học | ◐ | ✔ | ? | ✘ | ✘ | PF theo chuẩn chương trình |
| Một tác phẩm, nhiều bản ghi có nguồn, nơi, người | ◐ | ✘ | ✘ | ◐ | ✔ | PĐ có dị bản nhưng chưa có nguồn, nơi cho từng dị bản |
| Vai tình nguyện rõ (nhập, soát) | ✘ | ✘ | ✘ | ✔ | ? | AZ ghi người nhập, người soát |
| Văn bản mở để tải | ✘ | ✘ | ✘ | ✔ | ◐ | |
| Không quảng cáo | ✔ | ◐ | ✔ | ✔ | ✔ | PF có một ít yêu cầu quảng cáo, đo lường |
| Báo lỗi ngay trên trang | ✔ | ✔ | ? | ? | ? | PĐ có "Báo lỗi" trong "Bạn muốn góp gì?" |

---

## 5. Phân tích khoảng trống của phongdao.com

### 5.1. phongdao.com hơn ở đâu (giữ và khuếch đại)

- **Đọc trên điện thoại.** Lời, tên bài, Hiểu nhanh nằm trọn màn hình đầu; lục bát thụt câu lục. Thi viện không cho thấy lời ở màn hình đầu; Lời giải hay, Hoatieu đặt quảng cáo, quảng bá trước nội dung.
- **Chú thích chạm ngay trong lời.** Ca dao Mẹ có nhiều chú thích hơn nhưng trên điện thoại khó chạm và làm mất chỗ đang đọc.
- **Nhẹ, sạch, dễ tiếp cận:** 177 KB, 0 quảng cáo, 0 theo dõi bên thứ ba, 0 lỗi axe. Đây là bằng chứng đo được cho nguyên tắc "không quảng cáo, không theo dõi" (`docs/00` mục 4).
- **Trung thực về mức kiểm.** Chỉ phongdao.com nói rõ "chưa đối chiếu sách".
- **Công cụ cho giáo viên:** trích dẫn hai kiểu, trình chiếu. Không site Việt Nam nào có.
- **Dị bản tô chỗ khác, từ điển, bản đồ.**

### 5.2. Thiếu ở đâu

| Nhóm | Khoảng trống | Bằng chứng |
|---|---|---|
| Vận hành | Site thật chạy plugin 0.28.0, repo 0.33.0; các bản sửa an toàn chưa lên | `ca-dao.js?ver=0.28.0`; E2E 27/10 |
| Nội dung | 171 bài; 0 tục ngữ – thành ngữ trên site | Trang Tất cả 27/10 |
| Nội dung | Hiểu nhanh rất ít; trang không có phần chữ riêng thì không được lập chỉ mục | E2E 26/10; `inc/seo.php` |
| Nội dung | 0 bài đã đối chiếu sách | E2E 26/10 |
| Tìm | Không chịu gõ sai; Ca dao Mẹ làm được | Thử "…thai sonn" ở mục 3.1 |
| Tính năng | Chưa có ghi âm người thật (Thi viện có) | Mục 3.1 |
| SEO | Tiêu đề bài chưa nói trang có gì ("nghĩa", "giải thích") trong khi người tìm hỏi đúng điều đó | Mục 3.3 |
| SEO | Trang chủ, Tất cả chưa có canonical trên site thật (bản sửa chưa lên) | Số đo 27/10 |
| Vận hành | Một người làm; Ca dao Mẹ có 11 thành viên, Thi viện có cộng đồng | Trang Giới thiệu của hai site |

### 5.3. Điều chưa site nào làm tốt (cơ hội khác biệt)

1. **Lời có nguồn tới trang, kiểm được.** Ở Việt Nam không ai làm; ở nước ngoài thì Aozora, Cổ văn đảo, VWML đều làm. Hai sách phạm vi công cộng có bản quét trên Wikisource là đường làm nhanh nhất.
2. **Tục ngữ: nghĩa ngắn, đáng tin, không văn mẫu.** Trang văn mẫu dài, nặng, không nguồn; Ca dao Mẹ thì không giải nghĩa. Một khối nghĩa 3–4 dòng có người duyệt là chỗ trống thật.
3. **Dị bản có nguồn, nơi, người kể**, theo mô hình VWML. Site Việt Nam chỉ có dị bản trần.
4. **Đọc tốt và sạch trên điện thoại.** Đã có; cần giữ khi nội dung dày lên.
5. **Cho giáo viên:** trích dẫn, trình chiếu, bộ câu theo bài học — kiểu bộ bài theo chương trình của Poetry Foundation, nhưng chỉ ghi SGK, trang khi đã thấy tận mắt.
6. **Dữ liệu mở có phiên bản** (T-066). Không site Việt Nam nào có.

---

## 6. Kế hoạch nâng cấp

Cách ghi:

- **Tác động:** cao / vừa / thấp.
- **Công sức:** S vài giờ, M 1–2 ngày, L nhiều ngày (agent).
- **Giờ NSL:** số giờ người sáng lập phải bỏ ra.
- **Task:** mã trong `docs/22`, hoặc **ĐX-n** = đề xuất task mới, chưa đánh số. Đánh số T-xxx khi người sáng lập duyệt, tránh trùng số với PR khác.

### 6.1. Làm ngay (≤ 2 tuần)

| # | Việc | Tác động | Công sức | Ai · giờ NSL | Task | Lằn ranh, Human Gate |
|---|---|---|---|---|---|---|
| 1 | Đưa plugin trên máy chủ lên bản mới nhất; tìm vì sao tự cập nhật dừng ở 0.28.0 (theme vẫn cập nhật) | Cao — cửa quản trị vẫn mở | S | NSL làm trong FlashPanel theo hướng dẫn agent soạn · 0,5–1 h | T-078 (sửa lại) | Không ghi lên máy chủ khi NSL chưa đồng ý (`AGENTS.md`, KHÔNG BAO GIỜ 15) → **Q1** |
| 2 | Kiểm tra tự động "site thật đúng bản": sau mỗi lần gộp, so số phiên bản trên site với repo; lệch quá một giờ thì báo trong Bàn biên tập hoặc mở issue | Cao — lần này lệch mà không ai biết | S | Agent · 0 h | ĐX-1 | Chỉ đọc site, không ghi |
| 3 | Chạy lại `npm run e2e` sau khi cập nhật; gỡ `test.fail` của L-10 (đã hết lỗi); thêm biến môi trường để bộ kiểm chạy được với Chromium cài sẵn và proxy | Vừa | S | Agent · 0 h | ĐX-2 (bảo trì E2E) | Không |
| 4 | Danh sách 50 câu tục ngữ – thành ngữ, ca dao "được tìm nhiều": agent đề xuất từ hàng Chờ duyệt và các câu ở mục 3.3; NSL chọn, duyệt, đăng; agent soạn nháp Hiểu nhanh theo lô 10 câu. Làm sau việc 1, vì tục ngữ chỉ vào hàng Chờ duyệt khi site có plugin mới | Cao — mở đường lên Google | M | Agent + NSL · 2–3 h/tuần | T-125, T-100, T-048 | H2: NSL duyệt nháp máy, bấm Đăng; máy không đăng. Không khẳng định "có trong SGK" khi chưa thấy sách |
| 5 | Viết hướng dẫn một trang "Duyệt 10 câu tục ngữ trong 30 phút" | Vừa | S | Agent · đọc 10 phút | (đi kèm việc 4) | Không |

### 6.2. Trước khi mở công khai (H5)

| # | Việc | Tác động | Công sức | Ai · giờ NSL | Task | Lằn ranh, Human Gate |
|---|---|---|---|---|---|---|
| 6 | Khối nghĩa cho tục ngữ – thành ngữ: nghĩa đen · nghĩa bóng · dùng khi · câu gần nghĩa, trái nghĩa (liên kết sang nhau); 3–4 dòng, không văn mẫu | Cao | M | Agent; NSL duyệt · 1–2 h/tuần | T-098 | Phần chép từ Wiktionary ghi "Nguồn … · CC BY-SA" tại chỗ (D-061); nháp máy chờ duyệt (H2) |
| 7 | Đối chiếu lời với sách hết bản quyền có bản quét (*Câu hát góp* 1901; *Tục ngữ, cổ ngữ, gia ngôn* 1897): máy tìm câu khớp, hiện ảnh trang quét; NSL xem tận mắt rồi bấm "Đã đối chiếu, tr. N" | Cao — giữ lời hứa D-039, khác biệt lớn nhất | M–L | Agent; NSL · 1 h/tuần cho 20–30 bài | T-099 (mở rộng) + ĐX-3 | H3 (dùng nguồn mới); H2 (xác nhận); D-072 hiện ghi "máy không bao giờ tự đặt" → cần quyết định mới cho cách "máy gợi ý, người bấm" → **Q3** |
| 8 | Lời giải trong *Tục ngữ, cổ ngữ, gia ngôn* (1897) làm một lớp "giải nghĩa xưa", có nhãn năm và chính tả cũ, ghi nguồn tại chỗ | Vừa | M | Agent; NSL duyệt · 0,5 h/tuần | T-099 | H3 (chép giải nghĩa từ nguồn mới) → **Q4** |
| 9 | Tìm chịu gõ sai một chữ và "Có phải bạn tìm …?" khi 0 kết quả | Vừa | M | Agent · 0 h | ĐX-4 (nối T-126, lỗi L-09) | Không |
| 10 | Tiêu đề, mô tả hợp ý định tìm: bài có Hiểu nhanh hay khối nghĩa thì tiêu đề nói rõ "nghĩa, cách dùng"; bài chưa có thì giữ như cũ (không hứa điều trang không có) | Vừa | S | Agent · 0 h | T-050 (tiếp) | Không |
| 11 | Bật bộ nhớ đệm trang, OPcache (lỗi L-13 vẫn còn) | Vừa — chống nghẽn khi bài được chia sẻ mạnh | S | NSL · 0,5 h | (L-13) | Cấu hình máy chủ do NSL làm |
| 12 | Gửi thư cho Ca dao Mẹ (đã soạn ở `docs/12` mục 7), có thể kèm đề nghị liên kết qua lại | Vừa — giảm rủi ro chú thích chép | S | NSL · 0,5 h | T-034 | H3 → **Q5** |
| 13 | Bộ câu "Có trong SGK" cho giáo viên: NSL đánh dấu câu có trong SGK, ghi tên sách và trang khi đã thấy; agent làm trang lọc và nối với Trình chiếu | Vừa–cao cho giáo viên | M | Agent; NSL · 1 h/tuần | T-062 (một phần) + ĐX-5 | Lằn ranh 2: không ghi sách, trang chưa thấy tận mắt |

### 6.3. Sau khi mở

| # | Việc | Tác động | Công sức | Ai · giờ NSL | Task | Lằn ranh, Human Gate |
|---|---|---|---|---|---|---|
| 14 | Ghi âm người thật (hát ru, hò, lý) theo vùng, có hồ sơ đồng ý | Cao — Thi viện có, chưa ai làm có hồ sơ | L | NSL · tuỳ | T-067 | Lằn ranh 3: đồng ý ghi âm (H3) |
| 15 | Dị bản có nguồn, nơi, người kể; trang so sánh dị bản | Vừa–cao | L | Agent; NSL duyệt | T-063 | Không ghi nơi, người kể khi không có căn cứ; dữ liệu cá nhân cần đồng ý |
| 16 | Dữ liệu mở có phiên bản (phần dự án tự viết + lời đã đối chiếu sách phạm vi công cộng) | Vừa | M | Agent · 1 h duyệt | T-066 | H3: giấy phép từng phần; không phát hành lại chú thích chép từ nguồn chưa cho phép |
| 17 | "Người soát lời" tình nguyện: gửi số trang, ảnh trang sách qua form "Gửi số trang" sẵn có; ghi bút danh như vai "soát" của Aozora | Vừa | M | Agent; NSL duyệt | ĐX-6 (nối T-045) | Không tài khoản, không dữ liệu cá nhân; H2 |
| 18 | Đọc khi không có mạng, thêm vào màn hình chính (nhu cầu thấy ở các ứng dụng) | Vừa | M | Agent | T-065 | Không |
| 19 | Một câu mỗi ngày, bài theo dịp (Tết, Vu Lan, 20/11) | Vừa | S | Agent; NSL chọn | T-061 | Không |
| 20 | Đố vui gọn (câu đố) | Thấp–vừa | M | Agent | T-096 | Không |

---

## 7. Những gì không nên làm dù đối thủ có

| Việc đối thủ làm | Ai làm | Vì sao không |
|---|---|---|
| Văn mẫu dài, dàn ý, "Top N bài mẫu", sơ đồ tư duy | Hoatieu, Lời giải hay | Lệch định vị (hiểu trong 10 giây); máy soạn hàng loạt là đưa văn bản máy lên trang (KHÔNG BAO GIỜ 2); khó giữ chất lượng với một người |
| "Tiện ích AI" trả lời người đọc | Hoatieu (gói Pro) | `docs/00`: không chatbot, AI không tự sinh nội dung công khai |
| Quảng cáo, gói trả phí để bỏ quảng cáo | Hoatieu, Lời giải hay, Thi viện, ứng dụng | Trái lý do tồn tại (`docs/00` mục 5) |
| Nút Thích, bình luận Facebook, Microsoft Clarity | Ca dao Mẹ, Hoatieu, VOH | Theo dõi người đọc bởi bên thứ ba (KHÔNG BAO GIỜ 8) |
| Bình luận mở không duyệt | Thi viện | Rác hiện ngay đầu trang; phongdao.com giữ Chờ duyệt (D-062) |
| Bắt đăng nhập, đăng nhập bằng Google | Thi viện, Hoatieu | KHÔNG BAO GIỜ 8; `docs/00` |
| Đăng cả kho 16.000 câu cho bằng số lượng | (cạnh tranh với Ca dao Mẹ, ứng dụng) | D-045: xuất bản chọn lọc; trang mỏng làm hại cả tên miền |
| Ứng dụng di động riêng | 3 ứng dụng Play, Poetry Foundation, Cổ văn đảo | Gấp đôi công; web đọc được khi không có mạng (T-065) là đủ |
| Trò chơi ghép chữ | WePlay | Lệch trọng tâm; nếu có thì chỉ đố vui gọn (T-096) |
| "Tiếng Anh là gì" | VOH | `docs/00`: dịch ca dao là việc của chuyên gia |
| Trang danh sách "50+ câu về …" chép lại nhau | Trang tin, trang bán hàng | Trang mỏng; phongdao.com đã có trang chủ đề và `noindex` khi dưới 3 bài |
| Trang chủ là dòng bài mới không chọn lọc | Ca dao Mẹ | Người đọc lần đầu gặp nội dung ngẫu nhiên; trang chủ có "Câu hôm nay" là đúng hướng |
| Vượt chặn, thu thập nội dung của trang văn mẫu | — | Không vượt chặn; nội dung có bản quyền; không cần |

---

## 8. Câu hỏi cần người sáng lập quyết

**Q1. Đưa plugin trên máy chủ lên bản mới bằng cách nào?** (gấp)

| Phương án | Hệ quả |
|---|---|
| A. Bạn mở FlashPanel, làm theo hướng dẫn từng bước agent soạn (xem nhật ký tự cập nhật, chạy lại lệnh cập nhật) | Nhanh, bạn giữ toàn quyền máy chủ |
| B. Cho agent một lần truy cập máy chủ để xem nhật ký và sửa | Nhanh nhất nếu lỗi khó; cần bạn đồng ý cho lần đó |
| C. Tải tệp plugin từ GitHub, cài đè trong trang Plugin | Không cần đọc nhật ký, nhưng không biết vì sao tự cập nhật dừng |

**Khuyến nghị:** A; nếu không ra lỗi thì B. Dù chọn cách nào, làm thêm kiểm tra "site thật đúng bản" (ĐX-1).

**Q2. Trước khi mở, dồn sức nội dung vào đâu?**

| Phương án | Hệ quả |
|---|---|
| A. 50 câu tục ngữ – thành ngữ, ca dao được tìm nhiều, có Hiểu nhanh hoặc khối nghĩa | Đúng thứ người ta tìm (mục 3.3); mỗi trang có phần chữ riêng nên được lập chỉ mục |
| B. Làm dày ca dao đang có (chú thích, dị bản) | Trang đẹp hơn nhưng ít trang được tìm thấy |
| C. Mở ngay, bổ sung dần | Google chỉ thấy rất ít trang; thứ hạng ban đầu thấp |

**Khuyến nghị:** A, khoảng 10 câu mỗi tuần, agent soạn nháp.

**Q3. Dùng *Câu hát góp* (1901) và *Tục ngữ, cổ ngữ, gia ngôn* (1897) trên Wikisource làm sách đối chiếu?** (H3; cần quyết định mới nới cách làm của D-072)

| Phương án | Hệ quả |
|---|---|
| A. Có. Máy tìm câu khớp, mở sẵn trang quét; bạn xem rồi bấm "Đã đối chiếu, tr. N" | Nhiều bài có dòng đối chiếu nhanh; mỗi dòng vẫn do bạn bấm sau khi thấy tận mắt |
| B. Có, nhưng bạn tự mở sách và tự điền từng bài như hiện nay | Đúng D-072 hiện tại; chậm |
| C. Chưa dùng | Giữ nguyên; lời hứa "kiểm đến mức nào" vẫn chủ yếu là "chưa đối chiếu" |

**Khuyến nghị:** A. Lưu ý:

- Bản chép trên Wikisource có thể có lỗi gõ, nên luôn so với ảnh trang quét chứ không chỉ so với chữ đã chép.
- Lời trong sách 1901 có thể khác lời phổ biến hiện nay. Khi khác, bài nên có thêm dị bản theo sách, đưa vào qua đường thu thập khi bạn bật nguồn này (D-004), chứ không sửa lời chính.

**Q4. Có đưa lời giải của *Tục ngữ, cổ ngữ, gia ngôn* (1897) lên trang?** (H3)

| Phương án | Hệ quả |
|---|---|
| A. Có, thành một lớp riêng "Giải nghĩa trong sách năm 1897", ghi nguồn và trang tại chỗ, giữ chính tả cũ | Có thêm lớp nghĩa có nguồn rõ, không ai có; người đọc cần được báo là cách hiểu xưa |
| B. Chỉ dùng để bạn tham khảo khi viết Hiểu nhanh | An toàn, chậm hơn |

**Khuyến nghị:** A, cho những câu bạn đã duyệt.

**Q5. Thư cho Ca dao Mẹ có kèm đề nghị liên kết qua lại không?** (H3)

| Phương án | Hệ quả |
|---|---|
| A. Gửi thư xin phép chú thích, kèm đề nghị: phongdao.com liên kết về Ca dao Mẹ ở trang Nguồn; mời họ liên kết lại | Giảm rủi ro, có thêm người đọc |
| B. Chỉ xin phép chú thích | Như bản nháp hiện có |

**Khuyến nghị:** A. Gửi trước khi mở.

---

## Phụ lục. Cách làm lại khảo sát

- Kịch bản đo (Playwright + axe) và ảnh chụp nằm ở thư mục tạm của phiên, **không đưa vào repo**: ảnh chụp site khác không được lưu trong repo công khai.
- Chạy lại phần phongdao.com: `npm run e2e`. Trong môi trường agent cần cấu hình tạm (ĐX-2):
  - trỏ trình duyệt sang Chromium cài sẵn;
  - cho yêu cầu API đi qua proxy.
- Lần sau nên làm thêm:
  - kiểm thứ hạng trên Google Việt Nam bằng máy của người sáng lập (tìm 13 câu ở mục 3.3, ghi 10 kết quả đầu);
  - nghe thử ghi âm của Thi viện, Cổ văn đảo;
  - thử Poetry Foundation trên trình duyệt có JavaScript đầy đủ.
