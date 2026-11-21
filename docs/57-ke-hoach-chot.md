# 57 · Kế hoạch chốt — chương trình nâng cấp V2 (bản thực thi)

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Kế hoạch đang dùng** (D-127, D-128, D-129, D-130). Giai đoạn 0 được duyệt về nguyên tắc; Q-010, Q-011 đã trả lời (D-130). Bộ [45](45-kiem-ke-va-doi-chieu.md)–[56](56-goi-quyet-dinh.md) là lịch sử và căn cứ; nơi khác nhau, tài liệu này thắng. Các giai đoạn sau mở theo cổng ở mục 9 |
| Đọc khi | Mọi việc thuộc chương trình nâng cấp: số đo nền, trang thử, nghiên cứu trải nghiệm, pilot biên tập, PoC kiến trúc, discovery sản phẩm |
| Việc trong `docs/22` | T-229 → T-238 (nhóm "V2 · Nâng cấp lớn — Giai đoạn 0") |

---

## 1. Đã quyết

1. **Định hướng bộ 50–56 được chấp thuận về nguyên tắc;** chưa phê duyệt toàn bộ khuyến nghị, chưa triển khai hàng loạt (D-127).
2. **Chỉ Giai đoạn 0 được mở:**
   - tài liệu;
   - số đo nền;
   - trang thử và sao lưu;
   - nghiên cứu trải nghiệm;
   - pilot biên tập có kiểm soát;
   - PoC kiến trúc trên trang thử.

   Chỉ việc trong phạm vi đã duyệt mới ở trạng thái READY. NEXT chỉ ở mức discovery (D-127, D-130).
3. **Luật, schema, SEO, quyền nội dung và production** do người sáng lập duyệt theo mức rủi ro (mục 10). Agent chỉ tự gộp thay đổi mức thấp (D-127).
4. **3.000 bài sau 6 tháng là mục tiêu mở rộng có điều kiện.** Mục tiêu theo ba mức, chốt lại sau pilot (D-127).
5. **Chú thích chép từ Ca dao Mẹ giữ như hiện tại;** nhóm Phong Dao thay dần (D-128).
6. **Nguyên tắc phương pháp** (D-129):
   - chất lượng hai trục, đánh giá theo giá trị thực chất;
   - AI đánh giá bằng bộ dữ liệu chuẩn, không khoá cứng mô hình, giá hay năng suất;
   - schema chỉ khoá sau PoC với dữ liệu thật;
   - bản mẫu trải nghiệm làm sớm, độc lập với Xưởng biên tập.
7. **Q-010 — đồng ý:** PoC kiến trúc trên trang thử với dữ liệu sao chép từ production, **chỉ sau khi** trang thử đạt danh sách kiểm cách ly và an toàn (mục 8.1), có sao lưu và đã thử khôi phục; không thay đổi production (D-130).
8. **Q-011 — chọn (a):** dùng AI ngay trong phiên làm việc để thử quy trình biên tập. API chỉ dùng khi có đề xuất benchmark định lượng được duyệt riêng. Chỉ báo cáo chi phí, token, so sánh mô hình khi có số đo thật (D-130).
9. **Pilot hai đợt:** 30–50 bài kiểm quy trình, rồi mở rộng tới 100–200 bài **chỉ khi đợt 1 đạt** và người sáng lập đồng ý. Không chạy theo sản lượng khi chất lượng chưa bảo đảm (D-130).
10. **Tách bạch bài đã xuất bản, đã biên tập, đủ điều kiện lập chỉ mục.** Theo dõi tác động SEO của pilot mà **không đổi luật lập chỉ mục** — D-117 giữ nguyên (D-130).

## 2. Điều chỉnh so với 50–56

| Ở 50–56 | Bản chốt |
|---|---|
| [52](52-chien-luoc-chat-luong-noi-dung.md) mục 2: một thang L0–L4; L2 có tiêu chí cứng (30–60 chữ, ≥ 2 quan hệ, chú giải mọi từ cổ) | **Hai trục** Hoàn thiện (H0–H3) và Tin cậy (T0–T3); H2 xét theo **"đủ cho bài này"**, không đếm chữ, quan hệ, phần (mục 3) |
| [50](50-tai-dinh-vi-chien-luoc.md) mục 7, 52 mục 11: 1.000 bài sau 3 tháng, 3.000 sau 6 tháng | Ba mức **MVP / Cơ sở / Mở rộng** cho 3 / 6 / 12 / 18 tháng; đặt lại sau pilot (mục 4.3) |
| 50, 52: "215 bài lên Google" dùng như số bài chất lượng | **"Đủ điều kiện lập chỉ mục" là trạng thái kỹ thuật, không phải chất lượng**; H2 đo riêng bằng phiếu duyệt (mục 3.4, 4) |
| 52 mục 6.5–6.6: mô hình mặc định, bảng chi phí, ngưỡng cứng | Chỉ là giả định chưa kiểm. Benchmark trong phiên **không** báo cáo chi phí, token, so sánh mô hình; benchmark API chỉ khi được duyệt riêng (mục 5.2) |
| 52 mục 7: năng suất ~3–4 phút / bài, ~200 bài / tuần | **Đo bằng pilot hai đợt** trước khi đặt mục tiêu (mục 5.3) |
| [54](54-kien-truc-dich-va-chuyen-doi.md): bảng quan hệ, `ho_so`, di chuyển M1–M9 | Là **giả thuyết thiết kế**; PoC trên trang thử với dữ liệu thật; khoá schema sau PoC (H1) (mục 6) |
| [55](55-lo-trinh-va-backlog.md): Xưởng biên tập trước, bản mẫu song song | Bản mẫu trải nghiệm **bắt đầu tuần 1**, không phụ thuộc Xưởng hay schema (mục 7) |
| 55: đưa việc GĐ0–GĐ1 vào READY | **Chỉ GĐ0**; T-185 (phần thiết kế cũ còn thiếu) tạm dừng |
| 56 B1: gửi thư + bật ghi nguồn / ẩn chú thích | **Giữ như hiện tại**, thay dần (D-128) |
| 56: 31 mục cần duyệt | Không còn quyết định cần ngay; các quyết định tiếp theo hỏi tại cổng (mục 11) |

## 3. Chất lượng hai trục (thay [52](52-chien-luoc-chat-luong-noi-dung.md) mục 2)

### 3.1. Trục Hoàn thiện — người đọc hiểu và cảm được bài tới đâu

| Mức | Tên | Điều kiện |
|---|---|---|
| **H0** | Lưu trữ | Có lời và thể loại |
| **H1** | Đã rà | Lời không lỗi trình bày; trùng đã xử lý; cờ an toàn (thô tục, có thể không phải dân gian) đã rà; thể loại đúng |
| **H2** | Đã biên tập | **Người đọc phổ thông hiểu được bài nhờ nội dung do thư viện viết hoặc duyệt** — người duyệt kết luận theo 3.3 |
| **H3** | Đào sâu | H2 + ít nhất một phần **làm người đọc hiểu thêm điều mới**: hình ảnh và biểu tượng, dị bản đã gom, bối cảnh có nguồn, câu gần nghĩa, quan hệ có lý do, bình giảng; người thẩm định hoặc biên tập viên công nhận |

### 3.2. Trục Tin cậy — lời và nhận định có căn cứ tới đâu

| Mức | Tên | Điều kiện |
|---|---|---|
| **T0** | Chưa rõ | Không ghi nhận được nguồn lời |
| **T1** | Bản lưu truyền | Có nguồn thu thập ghi nhận; chưa đối chiếu sách — mức của gần hết kho hiện nay |
| **T2** | Đã đối chiếu | Lời đối chiếu với ít nhất một sách hay tư liệu, có số trang, do người đã thấy sách điền |
| **T3** | Đối chiếu nhiều nguồn | ≥ 2 nguồn độc lập, hoặc T2 + thẩm định; mọi nhận định lịch sử có dẫn chứng |

Hai trục độc lập. Ví dụ: H3 · T1 (giải nghĩa sâu, lời chưa đối chiếu sách); H1 · T2 (lời đối chiếu kỹ, chưa giải nghĩa).

### 3.3. "Đủ cho bài này" — câu hỏi người duyệt trả lời ở mức H2

1. Người đọc phổ thông đọc xong có hiểu bài nói gì không? (Câu đố: hiểu vì sao đáp án đúng. Đồng dao: biết đọc, chơi thế nào khi có nguồn. Vè: biết bài kể chuyện gì.)
2. Chỗ nào người đọc có thể không hiểu (từ cổ, phương ngữ, địa danh, nhân vật, điển tích) đã được giải thích chưa? **Không có chỗ khó thì không cần chú giải.**
3. Có nhận định nào thiếu căn cứ, sai, sáo rỗng, nhắc lại câu không?
4. Phần nào thừa, trùng hay làm rối (vd. Hình ảnh trong bài nhắc lại Ý nghĩa) đã gọn chưa?

Mỗi câu có lựa chọn "không cần với bài này". Không ngưỡng số chữ, số quan hệ, số phần.

### 3.4. Bốn trạng thái khác nhau — không suy cái này từ cái kia

| Trạng thái | Là gì | Ai / cái gì quyết |
|---|---|---|
| **Đã xuất bản** | Bài ở trạng thái đăng, người đọc xem được | Người có quyền bấm đăng |
| **Đã biên tập (≥ H2)** | Kết luận **chất lượng** theo 3.3 | Người duyệt, ghi trên phiếu duyệt (pilot: phiếu ngoài kho mã; lưu trong dữ liệu là quyết định schema sau pilot) |
| **Đủ điều kiện lập chỉ mục** | Trạng thái **kỹ thuật** theo luật D-117: bài đã đăng có ít nhất một "phần riêng" (`MienDuLieu\PhanRieng`). **Không phải đánh giá chất lượng** | Máy tính từ dữ liệu |
| **Google đã lập chỉ mục** | Google thật sự đưa URL vào chỉ mục | Google; chỉ đo qua Search Console |

**Quan hệ giữa chúng:**

- Một bài đủ điều kiện lập chỉ mục có thể **chưa** đạt H2 (vd. chỉ có một chú thích người viết).
- Một bài đạt H2 thường đủ điều kiện, vì thư viện đã viết phần riêng. Nhưng có thể không, khi người duyệt kết luận bài không cần thêm phần nào.
- **Không dùng số bài đủ điều kiện làm số bài chất lượng.**

**Giữ nguyên trong Giai đoạn 0:** luật lập chỉ mục (D-117), cấp L0–L3 trong mã, nhãn trên trang người đọc.

## 4. Số liệu nền và chỉ số

### 4.1. Định nghĩa

| Chỉ số | Định nghĩa | Đo ở đâu |
|---|---|---|
| **Đã xuất bản** | Tác phẩm `tac_pham` trạng thái đăng | Màn Số liệu, CSDL trang thử; bộ đếm theo thể loại ở trang chủ |
| **Đã biên tập (≥ H2)** | 3.4 | Phiếu duyệt pilot; mẫu đánh giá lại trong số bài đủ điều kiện |
| **Đủ điều kiện lập chỉ mục** | 3.4 | Danh sách tính sẵn `Seo\Module::ds_lap` |
| **Trong sơ đồ trang** | URL trong `wp-sitemap.xml` — theo thiết kế **bằng** "đủ điều kiện"; đo cả hai để phát hiện lệch | GET công khai |
| **Google đã lập chỉ mục** | 3.4 | **Chỉ Search Console**; chưa có thì ghi "chưa đo" |
| **Tin cậy T0–T3** | 3.2 | Máy tính từ dữ liệu đối chiếu |
| **Năng suất** | Thời gian người duyệt / bài theo thể loại, vai; tỷ lệ nháp sửa nặng | Pilot |
| **Chất lượng** | Lỗi nghiêm trọng / lớn / nhỏ trên bài đã kiểm | Pilot (kiểm 100%), sau đó kiểm mẫu |
| **Dùng** | Lượt dùng tin cậy mỗi tuần (sao chép trích dẫn + chia sẻ), tìm không ra | 5 sự kiện hiện có |

### 4.2. Số liệu nền (21/11/2026)

**Đã xuất bản:**

- **16.453 bài**: ca dao 11.764 · tục ngữ – thành ngữ 3.487 · câu đố 765 · đồng dao 299 · vè 138.
- Nguồn: bộ đếm trang chủ, GET chiều 21/11.

**Các số cũ:**

| Số | Ngày, nguồn |
|---|---|
| 16.285 | 15/11, CSDL (D-117) |
| 16.303 | 19/11, đường dẫn riêng (`docs/02`) |
| 16.450 | 20/11, trang chủ |

Các số này khác nhau do ngày đo và cách đếm; bài mới vẫn được đăng hằng ngày. T-230 đối soát một lần trên CSDL trang thử, cùng một mốc giờ.

**Đủ điều kiện lập chỉ mục = trong sơ đồ trang (tác phẩm):**

- **216** (~1,3%): ca dao 133 · tục ngữ 61 · vè 10 · câu đố 7 · đồng dao 5.
- Kiểm 7 trang mẫu: bài trong sơ đồ không có `noindex`; bài ngoài sơ đồ có `noindex, follow`.
- **Đối soát 215 → 216:** lúc đo đầu phiên có 215 (đồng dao 4). Lúc 14:23 ngày 21/11 (+07:00, `lastmod` trong sơ đồ trang) **một bài đồng dao được sửa, có thêm phần riêng nên tự vào sơ đồ trang** theo luật hiện hành. Các thể loại khác không đổi.
- Đây là **số đếm sống**: số nền luôn ghi kèm giờ đo.

**Các số khác:**

| Chỉ số | Giá trị | Ngày, nguồn |
|---|---|---|
| URL khác trong sơ đồ trang | 55 (trang 8 · tuyển tập 8 · bài cảm nhận 3 · thể loại 5 · chủ đề 14 · vùng 6 · hình thức 7 · mục từ 4) | 21/11, GET |
| **Đã biên tập (≥ H2)** | **chưa đo** — không suy từ số đủ điều kiện | Pilot đợt 1 đánh giá lại 10 bài trong số 216; con số cho toàn kho có sau khi lưu H2 trong dữ liệu (quyết định sau pilot) |
| Google đã lập chỉ mục | **chưa đo** | Cần Search Console (T-230) |
| Bài viết (`post`) | 46, `noindex` chờ quyền | 21/11, REST công khai |
| Bài có Ý nghĩa người duyệt | khoảng 130 (15/11), chưa đo lại | D-117; T-230 |
| Lượt dùng tin cậy / tuần | chưa tổng hợp | T-230 |

### 4.3. Mục tiêu theo ba mức — **đặt lại sau pilot đợt 2**

**MVP** (sàn, người sáng lập + AI) / **Cơ sở** (3–4 cộng tác viên) / **Mở rộng** (nhóm đầy đủ). "↻" = đặt khi có số đo.

| Chỉ số | Nền 21/11 | 3 tháng | 6 tháng | 12 tháng | 18 tháng |
|---|---|---|---|---|---|
| Bài **đã biên tập (≥ H2)** | chưa đo | 300 / 600 / 1.000 | 600 / 1.500 / 3.000 | 1.200 / 3.000 / 5.000 | 1.800 / 4.500 / 7.000 |
| Bài **≥ T2** | chưa đo (rất ít) | 0 / 20 / 50 | 20 / 50 / 200 | 100 / 200 / 500 | 200 / 400 / 800 |
| Câu đố đã rà an toàn, có giải thích | 7 có phần riêng | 200 / 400 / 765 | 400 / 765 / 765 | 765 | 765 |
| Hồ sơ thực thể (chỉ sau khi khoá schema) | 0 | 0 (PoC) | 0 / 50 / 200 | 50 / 200 / 1.000 | 150 / 500 / 1.500 |
| Bài viết có quyền, đã nối | 0 | 0 | 0 / 5 / 20 | 5 / 20 / 50 | 10 / 40 / 100 |
| Lỗi lớn qua kiểm (nghiêm trọng luôn = 0) | chưa đo | ≤ 3% / 2% / 1% | ≤ 3% / 2% / 1% | ≤ 2% / 1,5% / 1% | ≤ 2% / 1% / 1% |
| Thử tác vụ trải nghiệm đạt | UX-0 | PG2: ≥ 80% / 85% / 90% | site thật: ≥ 80% / 85% / 90% | ↻ | ↻ |
| Lượt dùng tin cậy / tuần | đo ở T-230 | ↻ | ↻ | ↻ | ↻ |

**Chỉ theo dõi, không đặt mục tiêu:**

- **Đủ điều kiện lập chỉ mục, trong sơ đồ trang:** phụ thuộc luật D-117. Không đổi luật trong Giai đoạn 0, không đuổi theo số trang.
- **Tỷ lệ Google đã lập chỉ mục / trong sơ đồ trang:** báo theo Search Console.

## 5. Pilot biên tập và benchmark AI (T-233, T-234, T-235, T-238)

**Mục đích:** đo thật quy trình, chất lượng, năng suất **trước khi** đặt mục tiêu hay xây Xưởng biên tập.

Dùng **công cụ hiện có**, không đổi mã production:

- Bổ sung → Nháp Ý nghĩa theo lô → Hộp việc;
- Sửa tác phẩm;
- giới hạn 50 nháp chờ (D-117 (3)) giữ nguyên.

### 5.1. Sổ tay biên tập v1 (T-235)

Nội dung:

- câu hỏi "đủ cho bài này" (3.3);
- ranh giới các phần;
- cách viết điều chưa chắc;
- quy tắc nháp AI (dẫn chiếu `docs/huong-dan/nhap-y-nghia-theo-lo.md`);
- **phiếu duyệt**: H2 / H3, lỗi theo loại, thời gian.

Người sáng lập duyệt trước khi pilot chạy.

### 5.2. Hai loại benchmark — báo cáo khác nhau

| | **Benchmark trong phiên làm việc** (T-233; đã duyệt — Q-011 (a)) | **Benchmark qua API** (chỉ khi được duyệt riêng) |
|---|---|---|
| Cách chạy | AI soạn nháp ngay trong phiên làm việc của agent, trên bộ dữ liệu chuẩn 60 bài (12 mỗi thể loại; đầu ra tham chiếu do người viết hoặc duyệt; 20% chấm đôi) | Gọi API theo lô bằng khoá người sáng lập tạo, có trần chi |
| Đo và báo cáo | Chất lượng nháp so với đầu ra tham chiếu (duyệt không sửa / sửa nhẹ / sửa nặng / bỏ), loại lỗi, thời gian người duyệt; so **các cách soạn** (lời nhắc, lượng ngữ cảnh) | Như bên trái, cộng token và chi phí thật từ số liệu sử dụng của API, so nhiều mô hình |
| **Không báo cáo** | Token, chi phí, so sánh mô hình — không đo được chính xác trong phiên | — |
| Khi nào | Tuần 2–3, sau Sổ tay | Khi người sáng lập duyệt một đề xuất cụ thể (mục đích, mô hình, số bài, trần chi) |

Các con số chi phí và năng suất ở [52](52-chien-luoc-chat-luong-noi-dung.md) mục 6.5, 7 là **giả định chưa kiểm**, không dùng làm kết quả.

### 5.3. Pilot hai đợt

| | **Đợt 1 — kiểm quy trình** (T-234) | **Đợt 2 — mở rộng** (T-238) |
|---|---|---|
| Số bài | 30–50, gồm 10 bài đối chứng do người viết không dùng nháp AI | Thêm tới tổng 100–200, gồm 10–20 bài đối chứng |
| Mẫu | Phân tầng 5 thể loại; ngắn / dài; đọc nhiều / ngẫu nhiên; 10 bài trong số 216 bài đủ điều kiện để đánh giá lại theo H2 | Như đợt 1 |
| Mục tiêu | Quy trình chạy trọn: tải lô → soạn nháp → nạp → duyệt → hiển thị đúng; phiếu duyệt dùng được; **kiểm 100%** bởi người thứ hai | Đo năng suất, chất lượng ở cỡ mẫu đủ để đặt mục tiêu ba mức; kiểm 100% |
| Cổng sang bước sau | **0 lỗi nghiêm trọng**; lỗi lớn ≤ 5% số bài; ≥ 70% bài nhắm H2 đạt H2 theo phiếu; không còn lỗi chặn trong quy trình; Sổ tay và cách soạn đã sửa theo phát hiện; **người sáng lập đồng ý mở đợt 2** | Báo cáo cho các quyết định sau pilot (mục 11) |
| Không đạt | Sửa Sổ tay, cách soạn, quy trình; chạy lại một đợt ≤ 30 bài; **không mở đợt 2** | Dừng mở rộng; báo cáo nguyên nhân |

**Lỗi nghiêm trọng:**

- sai nghĩa;
- bịa sự kiện hay nguồn;
- sửa sai lời;
- lộ thô tục ở chỗ cho trẻ.

**Lỗi lớn:**

- nhận định thiếu căn cứ;
- chú giải sai;
- sai thể loại.

### 5.4. Đo trong pilot

- **Thời gian người duyệt mỗi bài** theo thể loại và vai (nhật ký thẻ + phiếu tự ghi).
- **Mức sửa nháp:** khoảng cách nháp → bản cuối; tỷ lệ duyệt không sửa / sửa nhẹ / sửa nặng / bỏ.
- **Kết luận H2 / H3** theo phiếu. Tin cậy (T) không đổi do pilot, trừ khi có đối chiếu sách thật.
- **Kiểm 100%** bởi người thứ hai; lỗi theo loại.
- **Cảm nhận người duyệt:** mệt, vướng.
- **Không** báo cáo token, chi phí, so sánh mô hình (mục 5.2).

### 5.5. Trạng thái bài pilot và theo dõi SEO

1. Mỗi bài pilot ghi **bốn trạng thái** (3.4) lúc trước và lúc sau. Danh sách mã bài giữ ngoài kho mã.
2. **Không đổi luật lập chỉ mục.** Bài pilot có thêm phần riêng sẽ tự vào sơ đồ trang theo luật đang chạy (D-117). Đó là hệ quả của luật hiện hành, không phải đổi chính sách.
3. **Nhóm so sánh:** số bài tương đương, cùng thể loại, cùng mức lượt đọc, chưa biên tập.
4. **Search Console:** trạng thái lập chỉ mục, lượt hiển thị, lượt nhấp của bài pilot và nhóm so sánh. Đo ba lần: lúc bắt đầu, khi xong đợt 2, sau 8 tuần.
5. Đề xuất đổi luật lập chỉ mục (vd. chỉ bài ≥ H2) là **quyết định riêng sau pilot**, mức rủi ro cao.

### 5.6. An toàn

- Nháp chỉ công khai khi người bấm duyệt — giữ nguyên.
- AI chỉ nhận: lời đã đăng, phần thư viện viết, nguồn được phép.
- Không gửi dữ liệu cá nhân; không dùng chú thích chép làm nguyên liệu viết lại.
- Tệp lô và phiếu duyệt giữ ngoài kho mã.

### 5.7. Kết quả nghiệm thu

- **Đợt 1:** quy trình chạy trọn; 30–50 bài đã duyệt, lên site; báo cáo kiểm 100%; kết luận cổng.
- **Đợt 2** (nếu mở):
  - tổng 100–200 bài;
  - báo cáo năng suất theo thể loại (bài / giờ người duyệt);
  - tỷ lệ lỗi, tỷ lệ đạt H2 / H3;
  - so với nhóm đối chứng;
  - Sổ tay v1.1;
  - khuyến nghị mục tiêu ba mức và phạm vi Xưởng biên tập;
  - báo cáo SEO lần 1.

## 6. PoC kiến trúc trên trang thử (T-236)

**Điều kiện bắt đầu** (Q-010 — D-130):

- trang thử đạt **toàn bộ** danh sách kiểm 8.1;
- có bản sao lưu đêm trước và **đã thử khôi phục** (T-231);
- mã PoC ở nhánh riêng, chỉ chạy trên trang thử, **không gộp vào `main`** khi chưa khoá schema;
- **không thay đổi production**.

| Phần | Làm gì | Đo |
|---|---|---|
| Bảng quan hệ | Tạo bảng theo [54](54-kien-truc-dich-va-chuyen-doi.md) mục 4.2; điền lùi từ dữ liệu thật: câu gần / trái nghĩa, chú giải đã nối mục từ có toạ độ, cặp nghi trùng | Số dòng; thời gian truy vấn hai chiều trên trang bài; danh mục quan hệ có diễn tả được nhu cầu của **30 bài thật** (gồm Vè cô Giang, bài nhiều dị bản) không |
| Hồ sơ thực thể | 10–20 hồ sơ thật (nhân vật, địa danh, điển tích) theo **cả hai cách**: loại bài riêng (B2) và mục từ mở rộng (B1) | Thời gian tạo một hồ sơ + 5 quan hệ; chất lượng trang; mức trùng với Từ điển |
| Di chuyển | Mở rộng → ghi kép → điền lùi → đọc mới sau công tắc ([54](54-kien-truc-dich-va-chuyen-doi.md) M1–M4) | Thời gian chạy; chạy lại được; hoàn tác được |
| Hiệu năng | Năm loại trang với dữ liệu thật | Truy vấn thêm, thời gian phản hồi không đệm |

**Tiêu chí để đề xuất khoá schema v1** (người sáng lập duyệt — H1):

- danh mục quan hệ diễn tả ≥ 90% nhu cầu của 30 bài mẫu;
- trang bài thêm ≤ 2 truy vấn có chỉ mục, phản hồi không đệm ≤ 800 ms trên trang thử;
- mọi bước di chuyển chạy lại được, hoàn tác được;
- tạo một hồ sơ + 5 quan hệ ≤ 10 phút;
- chọn B1 / B2 bằng số đo.

## 7. Nghiên cứu trải nghiệm và bản mẫu (T-232)

**Bắt đầu tuần 1, độc lập với Xưởng biên tập và schema.** Bản mẫu là HTML tĩnh **ngoài kho mã** (như bản xem trước D-119, D-121). Nội dung là tác phẩm thật đã đăng. Phần chưa có dữ liệu dùng mẫu ghi nhãn rõ.

| Hạng mục | Nội dung |
|---|---|
| **Bộ bài** | 12 bài thật: mỗi thể loại **một bài ít dữ liệu** và **một bài nhiều dữ liệu**; thêm 2 bài rất nhiều dữ liệu (Vè cô Giang; một ca dao nhiều dị bản) |
| **Màn** | Năm mẫu thể loại × ít / nhiều dữ liệu; Hồ sơ bài; hồ sơ thực thể (mẫu); bài viết (mẫu); tuyển tập; trang chủ; Khám phá; danh sách thể loại — điện thoại và máy tính |
| **Hai phương án thị giác** | A "Giấy và mực", B "Tím – lục tinh chỉnh" ([53](53-thiet-ke-lai-trai-nghiem.md) mục 4), cùng nội dung |
| **Trạng thái bắt buộc** | Bài chỉ có lời; chưa có nghĩa; câu đố có / không gợi ý; đồng dao có / không cách chơi; vè dài có / không bối cảnh; bài có từ ngữ thô tục; chữ "Rất lớn"; phóng 200% |
| **Chuẩn so sánh UX-0** | 8–10 người (giáo viên, học sinh, phụ huynh, người yêu văn hoá, 1–2 người trên 60 tuổi), 6 tác vụ trên **site hiện tại** ([53](53-thiet-ke-lai-trai-nghiem.md) mục 7) |
| **Cổng** | **PG1** (tuần 3): chọn hướng và IA. **PG2** (tuần 6): thử bản mẫu bấm được, cùng 6 tác vụ, ngưỡng ở 4.3 |

Xây trên production chỉ sau **PG4** (người sáng lập duyệt — H4), bật dần theo thể loại, có công tắc quay lại.

## 8. Giai đoạn 0 — việc, thứ tự, nghiệm thu (khoảng 6 tuần)

Hai luồng chạy song song từ tuần 1:

- **Chất lượng nội dung:** T-230 → T-235 → T-233 → T-234 → T-238.
- **Trải nghiệm người đọc:** T-232.

PoC (T-236) chạy sau khi trang thử đạt điều kiện an toàn (T-231).

| Việc | Tên | Ai | Phụ thuộc | Nghiệm thu |
|---|---|---|---|---|
| **T-229** | Kế hoạch chốt; quyết định D-127..D-130; backlog Giai đoạn 0; tạm dừng T-185 | A | — | PR được người sáng lập duyệt; `kiem-tra` đạt |
| **T-230** | Số đo nền thống nhất một mốc giờ (mục 4); nối Search Console | N+A | — | Bảng mục 4.2 đầy đủ, có giờ đo và nguồn; số cũ đối soát được; đo chỉ đọc |
| **T-231** | Trang thử riêng tư có bản sao dữ liệu; **đạt danh sách kiểm 8.1**; thử khôi phục một lần | N+A | — | Danh sách kiểm có bằng chứng; biên bản khôi phục (thời gian, số bài, số phần khớp) |
| **T-232** | Nghiên cứu trải nghiệm: UX-0, bản mẫu hai phương án bằng tác phẩm thật, PG1, PG2 | N+A | — | Báo cáo UX-0; bản mẫu; biên bản PG1; kết quả PG2 |
| **T-235** | Sổ tay biên tập v1 và phiếu duyệt | A soạn, N duyệt | — | Tệp ở `docs/huong-dan/`; người sáng lập duyệt |
| **T-233** | Benchmark trong phiên trên bộ dữ liệu chuẩn 60 bài | N+A | T-235 | Báo cáo theo 5.2 — không có số token, chi phí, so mô hình |
| **T-234** | Pilot đợt 1: 30–50 bài | N+A | T-233, T-235 | 5.7 — đợt 1; quyết định cổng của người sáng lập |
| **T-238** | Pilot đợt 2: tới tổng 100–200 bài (chỉ khi đợt 1 đạt) | N+A | T-234 | 5.7 — đợt 2; báo cáo SEO lần 1 |
| **T-236** | PoC kiến trúc trên trang thử | A | T-231 | Mục 6 — báo cáo và đề xuất schema v1 |
| **T-237** | Discovery NEXT: phỏng vấn 5–8 giáo viên, phụ huynh; rà đối tác; không viết mã | N+A | — | Ghi chép tổng hợp, ẩn danh; thẻ cơ hội |

### 8.1. Danh sách kiểm an toàn và cách ly trang thử (điều kiện của T-236)

**Người làm:**

- Người sáng lập làm phần FlashPanel, Cloudflare, DNS (AGENTS.md, KHÔNG BAO GIỜ 15).
- Agent hướng dẫn, chạy lệnh trên trang thử **khi được đồng ý từng lần**, rồi kiểm.
- Bằng chứng ghi ở `docs/22` (T-231), **không chứa dữ liệu cá nhân, mật khẩu, khoá**.

| # | Nhóm | Yêu cầu | Bằng chứng |
|---|---|---|---|
| 1 | **Truy cập công khai** | Tên miền phụ riêng, không liên kết từ site chính; chặn mọi truy cập chưa xác thực (mật khẩu ở máy chủ web hoặc Cloudflare Access); `blog_public = 0`; đầu `X-Robots-Tag: noindex, nofollow`; không khai Search Console, không có sơ đồ trang công khai | Truy cập ẩn danh trả 401 / 403; không có nội dung trong phản hồi |
| 2 | **Kết nối production** | CSDL riêng (tên, người dùng, mật khẩu khác); `wp-config.php` riêng; `WP_HOME` / `WP_SITEURL` trỏ tên miền thử; thay URL trong bản sao; thư mục tải lên là **bản sao**, không dùng chung; tài khoản hệ thống và pool PHP riêng như production | Không truy vấn, tệp, REST hay lịch chạy nào trỏ về production; tìm chuỗi tên miền chính trong cấu hình thử ra 0 |
| 3 | **Tác vụ tự động** | Tắt cron hệ thống của trang thử, chỉ bật khi cần; tắt nguồn tự động (thu thập từ web), gọi Wikidata, mọi việc nền gọi ra ngoài; **không gắn tự cập nhật từ `main`** (cập nhật tay theo nhánh) | Danh sách sự kiện lịch chạy sau khi tắt; nhật ký không có yêu cầu ra ngoài |
| 4 | **Email** | Chặn mọi thư gửi ra (lọc `pre_wp_mail` trả về không gửi, hoặc tắt máy gửi thư) | Thử gửi một thư → không đi |
| 5 | **Tài khoản** | Chỉ người sáng lập (và agent khi được đồng ý) đăng nhập được; đặt lại mật khẩu mọi tài khoản trên bản sao; thu hồi mật khẩu ứng dụng; tạo khoá bí mật (salt) mới cho trang thử | Danh sách vai còn đăng nhập được; không phiên nào từ production dùng được |
| 6 | **Dữ liệu cá nhân** | Ẩn danh email và tên hiển thị của mọi người dùng trên bản sao (không xoá người dùng để giữ tác giả bài); xoá email, địa chỉ mạng, trình duyệt của người bình luận nếu có; bản gửi của người đọc (`gop_y`, bút danh chờ) chỉ giữ khi PoC cần, nếu không thì xoá trên bản sao; **không tải bản sao CSDL về máy cá nhân hay vào kho mã** (AGENTS.md, KHÔNG BAO GIỜ 9, 14) | Đếm email thật còn lại = 0 (trừ tài khoản người sáng lập nếu cần đăng nhập) |
| 7 | **Khoá dịch vụ** | Xoá hoặc thay bằng khoá thử: Turnstile, mọi khoá API hay thông tin máy gửi thư trong tuỳ chọn; không đặt khoá AI nào trên trang thử | Đọc lại các tuỳ chọn khoá: trống hoặc khoá thử |
| 8 | **Sao lưu và khôi phục** | Bản sao lưu FlashPanel đêm trước có; khôi phục vào trang thử; đối chiếu số bài, số phần, số dòng nhật ký với production cùng mốc giờ | Biên bản khôi phục |
| 9 | **Số liệu đọc** | Lượt đọc, sự kiện trên trang thử không gửi về production (cùng mã, CSDL riêng); không dùng số liệu trang thử để báo cáo | Kiểm bảng lượt đọc production không đổi khi thao tác trên trang thử |
| 10 | **Thu dọn** | Có lịch xoá trang thử và bản sao khi xong việc hoặc khi không dùng quá 30 ngày | Ghi ngày hết hạn |

**Kết quả thấy được khi xong Giai đoạn 0:**

1. **30–50 bài trên site được nâng cấp thật sau đợt 1**, tới 100–200 bài nếu đợt 2 được mở; có báo cáo chất lượng kiểm 100%.
2. Số liệu nền thống nhất; số Google đã lập chỉ mục.
3. Bản mẫu hai phương án với bài thật; kết quả thử với 8–10 người.
4. Năng suất, chất lượng đo thật — căn cứ đặt mục tiêu.
5. Báo cáo PoC và đề xuất schema có số đo.
6. Trang thử đạt danh sách kiểm, bản sao lưu đã thử khôi phục.
7. Ghi chép discovery cho NEXT.

## 9. Sau Giai đoạn 0 — mở theo cổng

Mỗi giai đoạn có ba lớp: **MVP** (nghiệm thu được) · **Cơ sở** · **Mở rộng**. Chi tiết việc ở [55](55-lo-trinh-va-backlog.md); phạm vi cắt theo kết quả Giai đoạn 0.

| Giai đoạn | Cổng mở | MVP | Cơ sở | Mở rộng |
|---|---|---|---|---|
| **GĐ1 · Năng lực biên tập** | Báo cáo pilot đợt 2; người sáng lập chốt mục tiêu và phạm vi Xưởng | Sửa đúng nút thắt pilot chỉ ra trên công cụ hiện có | Xưởng v1: hàng duyệt theo vai, nháp nhiều phần, trình kiểm tự động, kiểm mẫu | Xưởng đầy đủ; AI chạy bằng plugin (nếu được duyệt) |
| **GĐ2 · Trải nghiệm tham chiếu** | PG4 (H4) | Mẫu mới cho ca dao và tục ngữ, bài ≥ H2, sau công tắc | Năm thể loại + Hồ sơ bài | Trang chủ, IA, danh sách, Khám phá |
| **GĐ3 · Mạng tri thức** | Báo cáo PoC; khoá schema v1 (H1) | Bảng quan hệ + Đọc tiếp có lý do | Hồ sơ thực thể (≥ 50) + bài viết nối tác phẩm | Atlas, họ dị bản và gộp, Tìm nâng cấp |
| **GĐ4 · Học và Chơi** | Câu đố đã rà an toàn; mẫu câu đố qua PG4 | Đố vui từ câu đố an toàn | + Ghép vế, Học bản nhẹ | + Bộ của tôi, PWA |
| **NEXT** | Discovery + quyết định sản phẩm + Q-008 khi có thu tiền | Chỉ discovery — **không viết mã** | Thẻ cơ hội có bằng chứng | Một pilot nhỏ được duyệt riêng |

Việc của GĐ1–GĐ4 chưa vào `docs/22`. Mỗi cổng mở thì agent đưa đúng phạm vi đã duyệt vào backlog.

## 10. Mức rủi ro và cách duyệt (D-127)

| Mức | Gồm | Cách duyệt |
|---|---|---|
| **Thấp** | Tài liệu; công cụ đo, báo cáo chỉ đọc; kiểm thử; việc chỉ chạy trên trang thử đã đạt 8.1 | Agent làm; gộp khi bốn cổng đạt; báo trong bản tin hai tuần |
| **Trung bình** | Thay đổi production **không** chạm luật, schema, SEO, quyền: sửa lỗi giao diện, màn quản trị nhỏ, công cụ biên tập | Người sáng lập xem bản xem trước rồi đồng ý gộp |
| **Cao** | Luật (AGENTS.md, Human Gate, D-xxx); schema và dữ liệu; SEO, lập chỉ mục; quyền nội dung, nguồn; đường dẫn; xoá, gộp; cấu hình production | Người sáng lập **duyệt bằng lời trước khi làm**; review độc lập (D-048); diễn tập trên trang thử khi chạm dữ liệu |

Nội dung do người duyệt trong pilot là việc biên tập bình thường của người biên soạn, không phải thay đổi production của agent.

## 11. Quyết định

**Đã trả lời:** Q-010 (đồng ý, có điều kiện), Q-011 (chọn (a)) — D-130. **Không còn quyết định nào cần ngay** để chạy Giai đoạn 0.

**Sẽ hỏi khi tới cổng:**

| Khi | Quyết định |
|---|---|
| Sau pilot đợt 1 (tuần ~4) | Mở đợt 2 hay sửa và chạy lại đợt 1 |
| Khi cần số đo định lượng về mô hình | Một đề xuất benchmark API cụ thể (mục đích, mô hình, số bài, trần chi) |
| Sau pilot đợt 2 (tuần ~6–7) | Mục tiêu ba mức; kịch bản nhóm; phạm vi Xưởng; cách chạy AI ở quy mô lớn; có lưu mức Hoàn thiện trong dữ liệu (H1); có đổi luật lập chỉ mục (SEO) |
| Sau PG1 / PG2 | Hướng thị giác; IA; nhãn mức cho người đọc; vị trí Ý nghĩa của vè |
| Sau PoC | Khoá schema v1; B1 hay B2 cho hồ sơ thực thể; đường dẫn bài viết `/bai-viet/` (H6) |
| Trước GĐ4 | Giới hạn trò chơi hoá; ngân sách JavaScript trang ứng dụng |
| Sau discovery | Sản phẩm NEXT nào được thử; hướng tài chính, pháp nhân (Q-008) trước bất kỳ khoản thu nào |
| Khi giai đoạn cần | Từng mục đổi luật ở [51](51-danh-gia-lai-quyet-dinh.md) — chỉ mục giai đoạn đó thật sự cần |

## 12. Việc của người sáng lập trong Giai đoạn 0 (khoảng 3–4 giờ / tuần, 6 tuần)

| Việc | Thời gian ước |
|---|---|
| Duyệt PR kế hoạch | 30 phút |
| Tạo trang thử theo 8.1; thử khôi phục cùng agent | 2–4 giờ (agent hướng dẫn từng bước) |
| Nối Search Console | 20 phút |
| Mời 8–10 người thử trải nghiệm, xếp lịch; dự buổi PG1 | ~4 giờ trong 3 tuần |
| Duyệt Sổ tay v1 và phiếu duyệt | ~1 giờ |
| Duyệt nháp pilot đợt 1; quyết cổng đợt 2 | 2–3 giờ / tuần × 2 tuần |
| Duyệt nháp pilot đợt 2 (nếu mở) | 2–3 giờ / tuần × 2 tuần |
| Gợi ý 5–8 giáo viên, phụ huynh cho discovery | ~1 giờ |
