# 25 · Nâng cấp giao diện và nguồn nội dung (gói 25/10/2026)

| | |
|---|---|
| Phiên bản | 1.1 · 25/10/2026 (D-059..D-065) — 1.1 thêm chống trùng (ý 46–49, mục 4.9) |
| Trạng thái | **Đã duyệt, đang triển khai.** Người sáng lập duyệt 51 mục (48 ý + 3 quyết định A–C), để sau 1 ý |
| Đọc khi | Làm T-080..T-111, hoặc T-041, T-043..T-045, T-047, T-050..T-052, T-076 (các task cũ đã được viết lại theo gói này) |
| Nguồn | Phiên nghiên cứu 25/10/2026: mổ xẻ trang Ca dao Mẹ, đo trên kho của site, xem các nguồn mở và các bộ chuyển văn bản thành giọng nói tiếng Việt. Người sáng lập duyệt từng ý trên trang đề xuất (riêng tư, <https://claude.ai/artifact/QhtBLmtuVoFBAHkWJAbmWq>) |

Số trong ngoặc như (ý 7) là số của ý trên trang đề xuất; cột "Task" ở mục 3 cho biết ý nằm ở task nào trong `22`.

## 1. Phạm vi

Ba hướng nâng cấp site WordPress (`24`): **cách đọc một bài** (chạm từ khó, dị bản, thể thơ, công cụ đọc, giọng đọc), **nguồn nội dung** (Wikidata, Commons, Wiktionary, sách hết bản quyền, bộ chủ đề riêng, bài trùng), và **những gì riêng cho từng thể loại** (thành ngữ – tục ngữ, đồng dao, vè, câu đố, hát ru – hò – lý). Thêm: đếm lượt đọc, cảm nghĩ và bài phân tích của người đọc (luôn qua Chờ duyệt), và **chống trùng** khi lấy từ nhiều nguồn và khi viết bài tay (mục 4.9).

Không đổi các lằn ranh cũ: AI không viết lời, dị bản, nguồn (`AGENTS.md`); lời và dị bản không ghi nguồn, phần chép từ nguồn có dòng "Nguồn: …" tùy chọn (D-057, D-058).

## 2. Số liệu nghiên cứu (25/10/2026)

### 2.1. Kho của site

Đo trên các bài máy đã đọc trang gốc (ca dao đọc trước nên có nhiều hơn):

| Thể loại | Trong kho | Đã đọc trang | Đặc điểm |
|---|---|---|---|
| Ca dao – dân ca | 11.844 | 1.092 | Thường 2 dòng, dài nhất 46; 68% có chú thích; 9% có dị bản; 66% lục bát. Chủ đề nguồn có "Ru con" |
| Thành ngữ – tục ngữ | 3.369 | 59 | Thường 1 dòng, dài nhất 4; 66% có chú thích. Ca dao Mẹ gộp hai loại; site đang xếp hết vào "Tục ngữ" |
| Đồng dao | 304 | 59 | Thường 9 dòng, dài nhất 28; câu 3–4 chữ; gắn trò chơi; 59% có chú thích |
| Vè | 131 | 48 | Thường 30 dòng; "Vè cậu Hai Miêng" 990 dòng, 97 chú thích; 97% có chú thích; 11/48 thuộc chủ đề "Lịch sử" |
| Câu đố | 749 | 48 | Thường 2 dòng; bài nào cũng có đáp án; câu hỏi "Là gì? / Là cái gì? / Là con gì? / Là cây gì?"; nguồn chia theo đồ vật, con người – sinh hoạt, thiên nhiên, cây cối, con vật |

Chú thích (mẫu 964 bài): 1.444 chú thích khác nhau, 188 dùng ở từ 2 bài; 109 ghi phương ngữ hoặc từ cổ, 85 ghi gốc Hán Việt; nhắc vùng: Nam Bộ 89, Trung Bộ 46, Nghệ 45, Huế 33, Bắc Bộ 11. Dùng lại nhiều nhất là từ địa phương (Đàng 14 bài, Đặng 14, Canh 12, Mô 9…), rồi địa danh, đồ vật, cây – con, nhân vật, phong tục.

### 2.2. Ca dao Mẹ trình bày một bài thế nào

Dấu chú thích đặt sau đúng từ, rê chuột hiện nghĩa (không dùng được trên điện thoại); chú thích có ảnh (một số từ Wikimedia), bản đồ cho địa danh, liên kết sang chú thích khác; trang chú thích có "Những bài dùng chú thích này" và "Đóng góp diễn giải tốt hơn"; chủ đề và thẻ; hai danh sách bài liên quan dài, in nguyên lời; tìm kiếm tức thì (Meilisearch); bình luận Facebook; "Một số bài ngẫu nhiên". Không có "Bạn có biết?" (Wikipedia tiếng Việt có: 4 câu hỏi, thay theo tuần). Đồng dao không có cách chơi.

### 2.3. Nguồn mở

| Nguồn | Dùng được gì | Giấy phép | Đo được |
|---|---|---|---|
| Wikidata | Tọa độ, ảnh, mô tả tiếng Việt, bài Wikipedia của địa danh, nhân vật, cây, con, đồ vật | CC0 | Thử 60 từ có chú thích: khớp đúng 14, có lần khớp sai ("Thọ Xương" ra niên hiệu nhà Liêu) → **phải có người xác nhận**. Thử 29 đáp án câu đố: 9 khớp, đều có ảnh |
| Wikimedia Commons | Ảnh | Theo từng ảnh (PD, CC BY, CC BY-SA) | Ảnh chùa Thiên Mụ (Lưu Ly) thuộc phạm vi công cộng |
| Wiktionary tiếng Việt | Nghĩa và câu đồng nghĩa của 657 thành ngữ, 325 tục ngữ | CC BY-SA | Gần như không có nghĩa địa phương ("mô" chỉ có nghĩa "đống đất") |
| Wikisource tiếng Việt | *Đại Nam Quấc âm tự vị* (Huỳnh Tịnh Của, 1895), *Hán Việt tự điển* (Thiều Chửu), *Việt Nam phong tục* (Phan Kế Bính, 1915), *Từ điển Việt–Bồ–La* (1651) | Hết bản quyền | |
| Wikipedia tiếng Việt | Bài về trò chơi gắn đồng dao: Rồng rắn lên mây, Nu na nu nống, Chi chi chành chành, Ô ăn quan, Kéo co, Bịt mắt bắt dê | CC BY-SA | |
| *Tục ngữ phong dao* (Nguyễn Văn Ngọc, 1928) | Bộ sưu tập lớn | Hết bản quyền | Không có trên Wikisource, Internet Archive; Gallica chặn máy tìm tự động |

### 2.4. Giọng đọc tiếng Việt mã nguồn mở

Chất lượng theo bảng so sánh độc lập ViTTS-Bench (<https://github.com/yoonjae26/vietnamese-tts>, cùng bộ câu thử; "đọc sai" = tỉ lệ chữ nghe lại không khớp); giấy phép kiểm trên Hugging Face ngày 25/10/2026.

| Bộ đọc | Đọc sai | Tốc độ (RTF) | Giấy phép | Kết luận |
|---|---|---|---|---|
| **VieNeu-TTS v3 Turbo** | 3,3% | 0,1 trên GPU; ~0,5 trên CPU (theo tác giả) | Apache-2.0 (mô hình, giọng có sẵn); bộ mã hóa âm MOSS Apache-2.0 | **Chọn.** 25 giọng chia Bắc, Trung, Nam; nhân bản giọng từ 3–8 giây thu âm |
| Piper, giọng vais1000 | 5,7% | 0,04 (CPU) | Dữ liệu CC BY 4.0 | Dự phòng: rất nhanh, một giọng, câu khó đọc sai 39% |
| F5-TTS bản 1000 giờ | 3,0% | 0,2 | CC BY-NC-SA | Không dùng (phi thương mại) |
| IndexTTS-2 | 1,8% | > 1 | Cần xin phép | Không hợp VPS (GPU 9 GB) |
| MMS (Meta), viXTTS, VietTTS | 6,9–12% | | Phi thương mại | Không dùng |

Mẫu VieNeu đọc "Bao giờ cạn lạch Đồng Nai" (giọng Hải Đăng, Mai Anh, Quang Sơn) có trên trang đề xuất.

## 3. Danh mục đã duyệt

| Ý | Tên | Task |
|---|---|---|
| 1 | Chạm vào từ khó, nghĩa hiện ngay | T-082 |
| 2 | Dị bản đánh số, tô chỗ khác | T-083 |
| 3 | Chú thích địa danh có bản đồ nhỏ và ảnh | T-087 |
| 4 | "Bài gần gũi" và trang mô-típ | T-092 |
| 5 | Từ điển chú giải, lọc theo vùng | T-044 |
| 6 | Bản đồ ca dao theo vùng | T-104 |
| 7 | "Bạn có biết?" | T-093 |
| 8 | Tìm kiếm tức thì, gõ không dấu | T-041 |
| 9 | Ảnh chia sẻ tự sinh | T-050 |
| 10 | Công cụ đọc: cỡ chữ, sáng/tối, trích dẫn, trình chiếu | T-085 (trích dẫn: T-042) |
| 11 | Góp ý của người đọc | T-045 |
| 12 | Trang chủ mới | T-043 |
| 13 | Gắn Wikidata (máy gợi ý, người xác nhận) | T-086 |
| 14 | Giải nghĩa thành ngữ, tục ngữ từ Wiktionary | T-098 |
| 15 | Chú giải từ sách hết bản quyền | T-099 |
| 16 | Tự gợi ý vùng | T-089 |
| 17 | Gộp bài trùng thành dị bản — thay bởi ý 49 | T-101 |
| 18 | Trợ giúp viết "Hiểu nhanh" | T-100 |
| 19 | Tìm bản quét *Tục ngữ phong dao* | T-107 |
| 20 | Dị bản và ghi âm từ người đọc | T-045 (dị bản), T-067 (ghi âm, để sau) |
| 21 | Bỏ phần lọc thô tục — **để sau**, ghi chú "Gắn cờ nội dung có chứa từ thô tục" | T-108 (DEFERRED) |
| 22 | Nhận thể thơ, trình bày theo thể | T-084 |
| 23 | Trang riêng cho từng thể loại | T-094 |
| 24 | Tách thành ngữ và tục ngữ (thay bởi D-078: một mục "Tục ngữ – Thành ngữ") | T-080, T-128 |
| 25 | Nghĩa đen, nghĩa bóng, "dùng khi" | T-098 |
| 26 | Thành ngữ – tục ngữ: câu một dòng, chỉ mục A–Z | T-095 |
| 27 | Thành ngữ Hán Việt, điển tích | T-099 |
| 28 | Nhóm tục ngữ kinh nghiệm | T-091 |
| 29 | Đồng dao: cách chơi | T-103 |
| 30 | Đồng dao: "Đọc cùng bé" | T-103 |
| 31 | Vè: đọc bài dài | T-090 |
| 32 | Vè: khối "Bối cảnh" | T-102 |
| 33 | Câu đố: ảnh đáp án | T-088 |
| 34 | Câu đố: trang "Đố vui" | T-096 |
| 35 | Câu đố: nhóm theo đáp án, theo loại | T-096 |
| 36 | Hát ru, hò, lý | T-091 |
| 37 | Đếm và hiện lượt đọc | T-081 |
| 38 | Thống kê cho quản trị | T-081 (từ khóa tìm không ra: T-052) |
| 39 | Cảm nghĩ ngắn dưới bài | T-045 |
| 40 | Bài cảm nhận, phân tích của người đọc | T-097 |
| 41 | Chống thư rác, bảo vệ người viết | T-045 |
| 42 | Bài có phân tích được duyệt thì được đưa lên Google | T-097 |
| 43 | Nghe bằng giọng có sẵn trên điện thoại | T-085 |
| 44 | Giọng đọc sinh sẵn theo miền | T-105 |
| 45 | Giọng riêng của dự án, thu từ người đồng ý | T-106 |
| 46 | Nhận ra bài trùng | T-109 |
| 47 | Chống trùng khi lấy từ nguồn | T-110 |
| 48 | Báo trùng khi viết bài tay | T-111 |
| 49 | Gộp bài đã đăng bị trùng | T-101 |
| A | Ghi giấy phép riêng cho phần chép từ Wiktionary | D-061 |
| B | Bộ chủ đề riêng của dự án | D-061, T-091 |
| C | Dùng ảnh Wikimedia Commons | D-061 |

## 4. Đặc tả

### 4.1. Trang bài và cách đọc

- **Chạm vào từ khó (ý 1).** Bộ đọc lưu vị trí dấu chú thích trong lời và dị bản (`vi_tri_chu_thich`: mã, dòng, cột — bộ trích đã tính, hiện bỏ đi) cùng từ được chú thích. Trang bài: từ có chú thích là nút gạch chân chấm ngay trong lời; chạm mở ô trượt từ dưới lên (điện thoại) hoặc ô cạnh từ (máy tính) gồm từ, lời giải, "Nguồn: …" (D-057), liên kết "Xem trong từ điển". Danh sách chú thích dưới lời giữ nguyên; tắt JavaScript vẫn đọc được (`20` FR-1.2, FR-1.4). Bài đã đăng tự cập nhật nếu chưa sửa tay (cơ chế dấu băm của T-073).
- **Dị bản (ý 2).** Nhãn "Dị bản 1, 2…", ngăn cách rõ; chữ khác lời chính được tô (so từng âm tiết bằng máy). Phiên bản so sánh cạnh nhau đầy đủ vẫn là T-063.
- **Thể thơ (ý 22).** Máy nhận thể theo số âm tiết từng dòng: lục bát, song thất lục bát, câu 3–4–5 chữ, câu một dòng, tự do; lưu vào bài (trường ẩn), trình bày theo thể (lục bát thụt câu lục — Q-006; song thất lục bát theo lối quen; câu ngắn không thụt); danh sách lọc được theo thể.
- **Công cụ đọc (ý 10, 43).** Nút "Aa" ba cỡ chữ và nút sáng/tối, nhớ trên máy; chế độ trình chiếu chữ lớn cho lớp học; nút "▶ Nghe" dùng giọng tiếng Việt có sẵn trên máy (Web Speech), ngắt cuối dòng, ẩn khi máy không có giọng Việt; dòng nhỏ "Giọng đọc máy". Sao chép trích dẫn: T-042.
- **Vè dài (ý 31).** Bài trên 40 dòng: chia đoạn (theo khổ, không có khổ thì khoảng 20 dòng), mục lục đoạn, số dòng nhỏ bên lề, thời gian đọc, hiện vài chục dòng đầu rồi "Đọc toàn bài", nhớ chỗ đang đọc trên máy.

### 4.2. Địa danh, ảnh, vùng, bản đồ

- **Gắn Wikidata (ý 13, C).** Với mỗi từ được chú thích (và đáp án câu đố), máy tìm thực thể Wikidata trùng nhãn tiếng Việt, bỏ trang định hướng, đưa vào hàng chờ **Xác nhận** trong trang quản trị (Đúng / Sai / Bỏ qua). Chỉ thực thể đã xác nhận mới dùng: tọa độ, ảnh Commons (chỉ giấy phép mở; dưới ảnh ghi tác giả và giấy phép), mô tả, bài Wikipedia. Lưu theo mã chú thích để mọi bài dùng chung.
- **Chú thích địa danh (ý 3).** Ô chú thích có ảnh nhỏ, bản đồ tĩnh nhẹ (bấm mới mở bản đồ lớn bên ngoài), dòng "Bài khác nhắc tới …".
- **Ảnh đáp án câu đố (ý 33).** Như trên, cho đáp án đã xác nhận.
  *Đã làm (T-086..T-088, 25/10/2026):* bản đồ nhỏ là ảnh SVG tự lưu (không gọi dịch vụ bản đồ ngoài khi đọc), đường biên Natural Earth, thể hiện quần đảo Hoàng Sa và Trường Sa; ảnh Commons tải bản nhỏ về site (không lấy trực tiếp từ Wikimedia khi người đọc mở trang).
- **Vùng (ý 16).** Máy gợi ý vùng của bài từ chú thích ghi phương ngữ (Nam Bộ, Trung Bộ, Nghệ, Huế…) và từ địa danh đã xác nhận; người sáng lập duyệt hàng loạt; một bài có thể nhiều vùng. Phân loại `vung` đã có sẵn trong plugin.
- **Bản đồ ca dao (ý 6).** Trang bản đồ Việt Nam với điểm các địa danh đã xác nhận; chạm điểm ra các bài. Làm khi đủ địa danh.

  *Đã làm (T-104, 25/10/2026):* `/ban-do/` dùng lại ảnh SVG của bản đồ nhỏ, điểm là liên kết tới danh sách địa danh dưới bản đồ; không JavaScript, không dịch vụ bản đồ ngoài; đầy dần khi xác nhận thêm Wikidata.

### 4.3. Tìm, khám phá, trang chủ

- **Tìm kiếm (ý 8).** Ô tìm đầu trang, kết quả hiện khi gõ, gõ không dấu vẫn ra, tìm cả trong chú thích. Phương án chính: Meilisearch (FlashPanel có sẵn ứng dụng; người sáng lập bấm cài), chỉ mục cập nhật khi bài đăng/sửa. Cần thử "d" ra "đ" trước khi chốt; không đạt thì dùng chỉ mục bỏ dấu tự làm. *Đã làm (T-041, D-067):* chỉ mục bỏ dấu tự làm trước, không cần cài Meilisearch.
- **Từ điển (ý 5).** Mỗi chú thích (gộp theo mã) một trang: từ, lời giải, nguồn, "Các bài có từ này"; trang tổng có lọc Nam Bộ / Trung Bộ / Bắc Bộ / từ cổ / Hán Việt (lấy từ ghi chú trong lời giải).
- **Bài gần gũi (ý 4).** Cuối bài 3–5 thẻ: cùng mô-típ, cùng chú thích, cùng địa danh, cùng chủ đề (bộ chủ đề riêng); mỗi thẻ hai dòng đầu và một nhãn. Trang mô-típ (ví dụ "thề bằng điều không thể"): máy gợi ý cụm, tên mô-típ do người sáng lập đặt hoặc duyệt (`content/mo-tip` có khung từ v2). Bản tối giản đã làm (T-122, D-075, 26/10/2026): khối "Đọc tiếp" theo chủ đề, vùng, thể loại; mô-típ, chú thích, địa danh và trang mô-típ vẫn tạm dừng (D-073).
- **"Bạn có biết?" (ý 7).** Mỗi tuần 3–4 câu hỏi ngắn trên trang chủ, ghép theo khuôn từ chú thích có sẵn ("… [từ] [mệnh đề đầu của lời giải], và có trong câu [tên bài]?"), kèm nguồn; người sáng lập sửa hoặc tự viết; **máy không tự viết sự kiện**.
- **Trang chủ (ý 12).** "Câu hôm nay" chữ lớn, "Vừa bổ sung", "Đọc nhiều tuần này" (T-081), lối vào theo thể loại và theo vùng, ô tìm, "Bạn có biết?". Không khung mạng xã hội, không mây thẻ.
- **Trang thể loại (ý 23).** Mỗi thể loại: vài dòng giới thiệu do người sáng lập viết, cách đọc, bài tiêu biểu, lối vào theo chủ đề và vùng.
- **Ảnh chia sẻ (ý 9).** Ảnh vuông và 1200×630 tự sinh: lời trên nền giấy, thể loại, tên miền; khuôn riêng cho câu ngắn. Gộp T-060 vào T-050.

### 4.4. Theo thể loại

- **Thay bởi D-078 (26/10/2026):** không tách nữa — một mục "Tục ngữ – Thành ngữ" (T-128); đoạn dưới là lịch sử.
- **Tách thành ngữ và tục ngữ (ý 24).** Máy gợi ý loại cho mỗi câu trong nhóm "thành ngữ – tục ngữ" của nguồn: có trong danh mục Wiktionary thì theo đó; còn lại theo cấu trúc (cụm từ cố định, chưa thành câu, thường 4 chữ đối → thành ngữ; câu trọn ý, có chủ – vị, đúc kết kinh nghiệm → tục ngữ); không chắc thì để chờ. Người sáng lập duyệt hàng loạt trong Kho thu thập và trong danh sách bài; bài đã đăng đổi thể loại theo.
  *Đã làm (T-080, 25/10/2026):* danh mục Wiktionary lẫn cả từ đơn ("a la hỉ" nằm trong "Tục ngữ tiếng Việt") và xếp vài câu sách giáo khoa gọi là tục ngữ vào thành ngữ, nên chỉ theo danh mục khi độ dài câu cùng hướng. Thử trên 200 câu đã đọc của kho: ~55% máy chắc, ~25% nghiêng, ~20% chưa rõ.
- **Nghĩa (ý 25).** Bài thành ngữ, tục ngữ có khối nghĩa: nghĩa đen, nghĩa bóng, "Dùng khi…", một câu ví dụ, câu đồng nghĩa / trái nghĩa (liên kết sang nhau). Nghĩa từ Wiktionary ghi "Nguồn: Wiktionary ↗ · CC BY-SA" ngay dưới; phần còn lại người sáng lập viết, hoặc máy soạn nháp chờ duyệt (T-100).
- **Câu một dòng (ý 26).** Trang bài: câu chữ to, không khung lời thơ. Danh sách: chỉ mục A–Z và theo chủ đề, mỗi dòng một câu kèm nghĩa ngắn.
- **Hán Việt, điển tích (ý 27).** Thành ngữ gốc Hán: giải từng chữ theo *Hán Việt tự điển* (Thiều Chửu), kèm chuyện điển tích nếu có (liên kết Wikipedia). Chỉ giải chữ khi chắc chắn.
- **Nhóm kinh nghiệm (ý 28), lối hát (ý 36), loại câu đố (ý 35).** Đều là nhóm trong **bộ chủ đề riêng** (mục 4.5): tục ngữ thời tiết, mùa vụ, chăn nuôi, ăn uống – sức khỏe, đối nhân xử thế; hát ru, hò, lý, hát đối; câu đố đồ vật, con vật, cây cối – hoa quả, thiên nhiên, con người – sinh hoạt. Tùy chọn "Góc nhìn ngày nay" do người sáng lập viết cho vài tục ngữ.
- **Đồng dao (ý 29, 30).** Khối "Cách chơi" (số người, độ tuổi, đồ dùng, các bước) từ bài Wikipedia tương ứng (ghi nguồn, CC BY-SA) hoặc do người sáng lập, người đọc viết; trang trò chơi liệt kê các bài. Chế độ "Đọc cùng bé": mỗi lần một dòng, chữ thật to, chạm sang dòng, nhịp vỗ tay theo chữ; không tự phát âm thanh.

  *Đã làm (T-103, 25/10/2026):* phân loại Trò chơi với khối Cách chơi; nút chép mục Cách chơi / Luật chơi từ Wikipedia tiếng Việt (nguồn đúng bài, CC BY-SA 4.0); người đọc góp cách chơi qua nút Góp ý dưới bài, người sáng lập chép vào trò chơi; Đọc cùng bé một dòng, vỗ tay tô chữ, không âm thanh.
- **Vè: bối cảnh (ý 32).** Vè kể chuyện thật có khối bối cảnh: sự kiện, năm, nơi chốn, nhân vật (qua Wikidata đã xác nhận), tóm tắt do người sáng lập viết hoặc duyệt. **Máy không tự viết sự kiện lịch sử.**
- **Câu đố (ý 34, 35).** Trang bài vẫn hiện đáp án sẵn (D-058). Trang "Đố vui" riêng: mỗi lần một câu, "Xem đáp án", 10 câu một lượt, chọn theo loại; điểm nhớ trên máy. Các câu đố cùng đáp án hiện cạnh nhau.

### 4.5. Nguồn nội dung

- **Bộ chủ đề riêng (B).** Bộ chủ đề của dự án (vài chục chủ đề, chia nhóm), không chép nguyên phân loại của Ca dao Mẹ; máy gợi ý chủ đề cho từng bài từ chủ đề, thẻ của nguồn (lưu nội bộ trong kho) qua một bảng ánh xạ; người sáng lập duyệt bảng và duyệt hàng loạt. Phân loại `chu_de` đã có trong plugin.
- **Wiktionary (ý 14, A).** Khớp tên bài với mục thành ngữ / tục ngữ của Wiktionary; lấy nghĩa, đồng nghĩa; ghi giấy phép CC BY-SA ngay dưới đoạn chép; phần còn lại của site giữ CC BY.
- **Sách hết bản quyền (ý 15).** Chú giải từ cổ, từ Nam Bộ, từ Hán Việt, phong tục bằng trích đoạn trên Wikisource; ghi tên sách, tác giả, năm, liên kết trang Wikisource; giữ chính tả xưa, có thể kèm cách viết nay. Chỉ dùng khi khớp chắc chắn; người sáng lập duyệt.
- **Gộp bài trùng (ý 17).** Thay bởi ý 46–49, xem mục 4.9.
- **"Hiểu nhanh" (ý 18).** Trình soạn hiện cạnh ô đoạn trích: chú thích của nguồn, nghĩa Wiktionary (nếu có). Tùy chọn máy soạn nháp: luôn ghi là nháp, chỉ lên trang khi người sáng lập duyệt (như quy tắc nháp AI cũ).
- ***Tục ngữ phong dao* (ý 19).** Việc của người: tìm bản quét trên Gallica, Google Sách, thư viện; agent soạn hướng dẫn tìm nếu cần.

### 4.6. Người đọc góp phần

- **Góp ý, dị bản, cảm nghĩ ngắn (ý 11, 20, 39).** Trên trang bài: "Ở quê bạn câu này nói khác?" (gửi dị bản), "Góp ý chú thích", "Báo lỗi", và ô "Cảm nghĩ của bạn" (bình luận WordPress, luôn chờ duyệt). Mọi thứ vào hàng Chờ duyệt; không bắt email; có nút báo vi phạm. Form chạy ngay trong WordPress, thay cho dịch vụ ngoài (Tally) trong T-045 cũ.
- **Bài phân tích (ý 40).** Loại bài mới "Cảm nhận – phân tích", gắn với một bài ca dao: tiêu đề, nội dung (đoạn, in đậm, trích lời), bút danh, tùy chọn "học sinh / giáo viên / người yêu ca dao". Người đọc gửi qua trang soạn đơn giản → Chờ duyệt → duyệt xong hiện dưới bài ca dao ("Cảm nhận, phân tích (N)") và có trang riêng. Người sáng lập đăng được "Bài phân tích của dự án" với nhãn riêng.
- **Bảo vệ (ý 41).** Cloudflare Turnstile và một ô bẫy ẩn; giới hạn số lần gửi theo giờ; không bắt tên thật, email, số điện thoại; người gửi tích "Bài do tôi tự viết" và đồng ý cho đăng theo CC BY 4.0; thông báo quyền riêng tư ngay trong form (Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15) và cách xin gỡ. Khóa Turnstile do người sáng lập dán vào trang cấu hình của plugin, không gửi cho agent.
- **Google (ý 42).** Bài có ít nhất một bài phân tích đã duyệt được tính là có giá trị riêng (bỏ `noindex`); trang bài phân tích được đánh chỉ mục, có liên kết chuẩn về bài ca dao.

### 4.7. Lượt đọc

- **Đếm (ý 37).** Plugin tự đếm: cộng khi trang hiện trên màn hình vài giây (máy dò không chạy JavaScript thì không tính), mỗi máy một lần mỗi ngày (đánh dấu trên máy, không cookie), không lưu địa chỉ IP; lưu theo bài theo ngày. Trang bài hiện "N lượt đọc" khi từ 10 lượt; trang chủ "Đọc nhiều tuần này".
- **Thống kê (ý 38).** Ô trên Bảng tin: lượt đọc 7 ngày, bài đọc nhiều, người đọc đến từ đâu (chỉ tên trang giới thiệu). Từ khóa tìm không ra kết quả: T-052. Thống kê sâu hơn (Umami) chỉ khi cần.

### 4.8. Giọng đọc

- **Giọng điện thoại (ý 43).** Mục 4.1; làm trước, là phương án dự phòng về sau.
- **Giọng sinh sẵn (ý 44).** VieNeu-TTS v3 Turbo cài trong tài khoản `cadao` trên VPS (môi trường Python riêng), sinh file nén (Opus hoặc MP3) cho từng bài, chạy ban đêm, ưu tiên thấp (không ảnh hưởng các site khác trên VPS). Ước tính cả kho (~16.400 mục, ~30 giờ đọc): 15–30 giờ máy; 400–450 MB; mỗi câu ca dao ~15 KB; chỉ tải khi bấm nghe. Giọng theo vùng của bài: Nam Bộ giọng Nam, Huế – Nghệ giọng Trung, còn lại giọng Bắc. Ngắt nghỉ cuối dòng; lục bát ngắt nhịp chẵn; đồng dao chậm theo nhịp. **Trước khi sinh cả kho:** người sáng lập nghe 30 bài đủ thể loại trong trang quản trị (Đạt / Sai); bài sai thì sửa cách ngắt hoặc tắt giọng cho bài đó. Bài mới, bài sửa lời tự sinh lại.
- **Giọng riêng (ý 45).** Một người đồng ý (người sáng lập hoặc người kể chuyện quê) thu 5–10 giây mỗi giọng; có giấy đồng ý ghi phạm vi dùng; VieNeu nhân bản thành giọng của dự án. Không dùng các giọng có sẵn mang tên giống người thật nổi tiếng (ví dụ "Phạm Tuyên").

### 4.9. Chống trùng (ý 46–49, D-065)

Nguyên tắc: **máy chỉ báo và đề nghị, người sáng lập quyết; không chặn đăng (D-054); không tự gộp, không xóa bài nào** (bài phụ chỉ vào thùng rác).

Đo trên kho ngày 25/10/2026 (12.328 câu có lời, một nguồn): 9 câu trùng y hệt (có câu ở hai thể loại: "Rộng đồng thì gió thổi luôn" vừa là ca dao vừa là câu đố); 1 câu chỉ khác cách bỏ dấu; 8 câu chính là dị bản đã ghi của câu khác; 3 cặp giống ≥ 80%; 96 cặp giống 50–80% ("Yêu nhau cau bảy bổ ba…" ↔ "Thương nhau cau sáu bổ ba…", 69%); 274 dòng đầu dùng chung cho nhiều bài khác nhau ("Nghe vẻ nghe ve" mở đầu 14 bài vè, "Làm trai cho đáng nên trai" 10 bài); 2 bài nhập tay không trùng y hệt câu nào. Khi thêm nguồn thứ hai, phần lớn câu nổi tiếng sẽ có ở cả hai.

- **Nhận ra bài trùng (ý 46).** Mỗi bài, dị bản, câu trong kho có hai dấu vân tay của lời: (1) chuẩn hóa — NFC, chữ thường, bỏ dấu câu, gộp khoảng trắng; (2) bỏ thêm dấu thanh và đổi đ → d (bắt "hoà / hòa", gõ thiếu dấu). Mức giống đo bằng tỉ lệ cặp âm tiết liền nhau chung nhau trên bản (2). Bốn mức: **trùng y hệt** (dấu 1 bằng nhau); **gần như y hệt** (dấu 2 bằng nhau, hoặc ≥ 90%); **có thể là dị bản** (50–90%, hoặc trùng một dị bản đã ghi của bài khác); **khác**. Câu một dòng chỉ xét hai mức đầu (câu ngắn dễ giống tình cờ); câu ngắn nằm trọn trong bài dài là "liên quan", không phải trùng. Cùng lời khác thể loại vẫn là trùng (để chọn thể loại). **Không bao giờ so tiêu đề hay dòng đầu.**
- **Khi lấy từ nguồn (ý 47).** Câu mới vào kho (mọi nguồn) được so với bài đã có và các câu trong kho. Trùng y hệt / gần như y hệt: không tạo bài mới, gắn vào bài có sẵn ("Đã gộp"); chú thích, dị bản mới của nguồn kia được bổ sung (bài chưa sửa tay tự bổ sung; dòng "Nguồn" riêng cho phần chép từ nguồn đó). Có thể là dị bản: hàng **"Có thể trùng"** trong Bàn biên tập với Gộp làm dị bản / Là bài khác / Để sau; máy tạo bài hằng ngày bỏ qua câu đang chờ quyết. Làm trước khi thêm nguồn thứ hai và trước khi chuyển bài cũ từ site Astro.
- **Khi viết tay (ý 48).** Trong trình soạn, sau khi dán/gõ lời (ngừng gõ một chút) và khi bấm Đăng: ô bên cạnh liệt kê bài có thể trùng kèm mức giống, nút **Mở bài này** · **Thêm làm dị bản** (lời vừa gõ thành dị bản của bài cũ; bản nháp mới vào thùng rác, khôi phục được 30 ngày) · **Vẫn đăng bài mới** (đăng bình thường). Danh sách bài có nhãn "Có thể trùng".
- **Đã làm (T-109..T-111, 25/10/2026):** `24` mục 4. Chặt hơn đặc tả một chút theo đúng nguyên tắc "máy báo, người quyết": máy chỉ **tự gắn** khi lời giống hệt (dấu vân tay (1) hoặc (2) bằng nhau) và cùng thể loại; "gần như y hệt" vì khác vài chữ (≥ 90%) cũng vào hàng chờ, vì vài chữ khác có thể là một dị bản thật. Từ D-074 (26/10/2026): lời trùng đúng một dị bản đã ghi thì máy tự gắn làm dị bản (mọi thể loại), không vào hàng chờ. Mức giống khi so trực tiếp dùng mọi cặp âm tiết chung; lệnh thống kê dùng đúng cách đếm của phép đo (bỏ cặp quá phổ biến khi tìm ứng viên).
- **Gộp bài đã đăng (ý 49, thay ý 17).** Mỗi đêm quét bài đã đăng, cặp nghi trùng vào hàng "Có thể trùng". Gộp: chọn bài chính; bài kia thành dị bản; đường dẫn cũ chuyển hướng 301; lượt đọc cộng dồn; cảm nghĩ, bài phân tích chuyển sang bài chính; bài phụ vào thùng rác.

## 5. Lằn ranh

- **AI:** không viết lời, dị bản, nguồn; không tự viết sự kiện (Bạn có biết?, bối cảnh vè); nháp giải nghĩa chỉ lên trang khi người sáng lập duyệt.
- **Máy gợi ý, người xác nhận:** thực thể Wikidata, vùng, thể loại tách, chủ đề, gộp bài trùng, chú giải từ sách cũ — không có gì tự hiện ra trang khi chưa được xác nhận (trừ khi người sáng lập bật duyệt hàng loạt cho một nhóm).
- **Quyền:** ảnh chỉ từ Commons với giấy phép mở, ghi tác giả và giấy phép; không tải ảnh, không chép phân loại của Ca dao Mẹ; Wiktionary và Wikipedia ghi CC BY-SA tại chỗ.
- **Không lọc từ tục** (D-060): không chặn, không ẩn; gắn cờ trong quản trị để sau (T-108).
- **Chống trùng** (D-065): máy báo, người quyết; không chặn đăng, không tự gộp, không xóa; không so tiêu đề hay dòng đầu.
- **Dữ liệu cá nhân:** không cookie theo dõi, không lưu IP; người đọc gửi bài không phải khai tên thật, email, số điện thoại.
- **Giọng:** luôn ghi "Giọng đọc máy"; không nhân bản giọng người không đồng ý; giọng máy không thay hát ru, hò, lý thật (ghi âm thật: T-067).

## 6. Kế hoạch triển khai

Mỗi PR gom các task liên quan của một đợt; agent tự gộp khi kiểm tra đạt (D-066; `03` mục 7.1: nhiều PR mở cùng lúc thì xếp chồng). Máy chủ tự cập nhật sau mỗi lần gộp (T-078). Mức công: **nhỏ** vài giờ, **vừa** 1–2 ngày, **lớn** vài ngày.

| Đợt | Mục tiêu | Task (mức công) | Việc của người sáng lập |
|---|---|---|---|
| **1 · Nền dữ liệu** (Mốc A) | Xong Mốc A; tách loại trước khi hàng nghìn câu thành bài; chống trùng trước khi chuyển bài cũ và trước nguồn thứ hai; bắt đầu có số liệu | Theo thứ tự: T-042 trích dẫn và chia sẻ (nhỏ, đã có), T-080 tách thành ngữ – tục ngữ (vừa), T-081 lượt đọc (nhỏ), T-109 nhận ra bài trùng (vừa), T-110 chống trùng khi lấy từ nguồn (vừa), T-111 báo trùng khi viết tay (nhỏ), T-076 chuyển bài cũ và ngừng site Astro (vừa, đã có) | Duyệt hàng loạt thể loại tách; quyết các cặp "Có thể trùng" |
| **2 · Đọc dễ hơn** (Mốc B) | Mỗi bài đã có đọc tốt hơn ngay | T-082 chạm từ (vừa), T-083 dị bản (nhỏ), T-084 thể thơ (nhỏ), T-085 công cụ đọc + giọng điện thoại (nhỏ), T-090 vè dài (vừa) | Xem bản xem trước |
| **3 · Địa danh, vùng, chủ đề** (Mốc B) | Ảnh, bản đồ, vùng, bộ chủ đề | T-086 Wikidata (vừa), T-087 thẻ địa danh (vừa), T-088 ảnh đáp án (nhỏ), T-089 vùng (nhỏ), T-091 bộ chủ đề (vừa), T-044 từ điển (vừa) | Xác nhận thực thể Wikidata; duyệt bảng chủ đề; duyệt vùng |
| **4 · Khám phá và cộng đồng** (Mốc B) | Đi từ bài này sang bài khác; người đọc góp phần | T-041 tìm kiếm (vừa), T-092 bài gần gũi (vừa), T-093 Bạn có biết? (nhỏ), T-043 trang chủ (vừa), T-094 trang thể loại (nhỏ), T-095 câu một dòng (nhỏ), T-096 câu đố (vừa), T-045 góp ý + cảm nghĩ + chống rác (vừa), T-097 bài phân tích (vừa) | Tạo khóa Turnstile (Meilisearch không cần nữa — D-067); viết giới thiệu thể loại; duyệt bài người đọc gửi |
| **5 · Nội dung sâu** (Mốc C) | Nghĩa, chú giải, bối cảnh, giọng đọc | T-098 Wiktionary (vừa), T-099 sách cũ + Hán Việt (lớn), T-100 Hiểu nhanh (vừa), T-101 gộp bài đã đăng bị trùng (vừa), T-102 bối cảnh vè (vừa), T-103 đồng dao (vừa), T-104 bản đồ (lớn), T-105 giọng sinh sẵn (lớn), T-106 giọng riêng (nhỏ), T-050 ảnh chia sẻ (vừa), T-051, T-052 | Nghe thử 30 bài; thu giọng mẫu; tìm *Tục ngữ phong dao* (T-107, lúc nào cũng được) |

**Điều chỉnh 26/10/2026 (D-073, sau báo cáo review):** tạm dừng tới sau cổng mở T-092, T-095, T-096, T-102, T-105; kéo T-050 và T-100 lên Mốc B; thêm T-118..T-121 (an toàn vận hành, dòng mức kiểm D-072, điều hướng và danh sách bài, cuối bài – Tìm – Bàn biên tập). Thứ tự còn lại: T-119 → T-120 → T-121 → T-050 → T-100.

**Điều chỉnh 26/10/2026 (D-077):** gỡ tạm dừng của D-073 — T-092, T-095, T-096, T-102, T-105 quay lại kế hoạch; việc biên tập của người sáng lập không chặn mở. T-095, T-098 chờ Q-009 (gộp tục ngữ – thành ngữ).

Phụ thuộc chính: T-109 cần cho T-110, T-111, T-101, T-076; T-082 cần cho T-087; T-086 cần cho T-087, T-088, T-102, T-104; T-091 cần cho T-092, T-096; T-045 cần cho T-097; T-080 cần cho T-095, T-098; T-085 và T-089 cần cho T-105. Chi tiết và tiêu chí chấp nhận từng task ở `22` mục 3.

## 7. Để sau

- **Ý 21 — gắn cờ (không chặn) bài có từ tục**, bỏ ô "Không tự tạo bài khi có cờ Thô tục" và bỏ mặc định đó khỏi nguồn mới: T-108 (DEFERRED), làm khi người sáng lập mở.
- **Ghi âm của người đọc** (phần ghi âm của ý 20): T-067 (DEFERRED), cần người được ghi đồng ý.
- **So sánh dị bản cạnh nhau đầy đủ:** T-063 (DEFERRED); bản cơ bản là T-083.
