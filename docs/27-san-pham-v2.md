# 27 · Sản phẩm cho người đọc V2

| | |
|---|---|
| Phiên bản | 1.0 · 27/10/2026 (D-084, D-085, D-087, D-088) |
| Trạng thái | **Đã duyệt.** Đổi hướng thẩm mỹ hay hành vi đã chốt là Human Gate H4, ghi D-xxx |
| Đọc khi | Mọi task chạm trang người đọc (đợt 2, nhóm V2 trong `docs/22`) |
| Đọc kèm | `26` (kỹ thuật: khối, mẫu, REST), `28` (nội dung đến từ đâu), `docs/20` mục 1, 10, 11 (token, câu chữ, trợ năng — vẫn áp dụng) |
| Căn cứ | `docs/nghien-cuu/v2-ux-ui-2026-10-27.md`, khảo sát 27/10, báo cáo E2E 26/10, bản đề xuất *Phongdao Next Generation* mục 7–9 (S01–S18) |

Tinh thần: **"Một trang sách cổ được chăm chút, đặt trong một chiếc điện thoại hiện đại."** Hướng mỹ thuật **Sách Việt đương đại** (chữ, giấy nhẹ, son trầm, khoảng trắng, ít trang trí). Minh hoạ chọn lọc chỉ ở đồng dao, truyện, tuyển tập khi có ảnh có quyền; phong cách "atlas" chỉ ở bản đồ và chuyên đề vùng.

## 1. Nguyên tắc

1. **Đơn vị nhỏ, tập hợp lớn.** Mọi trang tập hợp ghép từ trang tác phẩm; mỗi câu liên kết về trang riêng, trích dẫn được; nghĩa viết một lần.
2. **Trả lời trước, mở sâu sau.** Màn hình đầu (390 px) trả lời câu người đọc đang hỏi; phần sâu trong `<details>`.
3. **Mỗi câu một nhãn đúng.** Thể loại ở mọi thẻ; câu có cờ "Nghi không phải dân gian" mang cờ ở mọi nơi.
4. **Nguồn đặt ngay chỗ khẳng định.** Dòng mức kiểm ngay dưới lời; nguồn bản kể ngay dưới tên truyện; "chưa kiểm chứng" ngay cạnh điều được kể. Phần nào ẩn là do chính sách hiện (D-085), không do bố cục.
5. **Lối vào theo vai, không theo dõi.** Lựa chọn của người đọc nằm trên máy họ.
6. **Một hành động chính mỗi vùng.** Trang bài: Trích dẫn. Tuyển tập: Trình chiếu. Câu đố: Hiện đáp án. Trò chơi: Đọc cùng bé.
7. **Không độn chữ.** Khối không có dữ liệu thì không hiện, kể cả tiêu đề; không ảnh khung trống; không trang đích rỗng.
8. **Nhẹ và sạch là tính năng.** Mục 9.

## 2. Tên, khẩu hiệu, câu chữ

- Tên: **Thư viện Văn học Dân gian Việt Nam**; tên ngắn ở đầu trang điện thoại: **Phongdao**; khẩu hiệu "Đọc một câu. Hiểu một đời sống."
- Tên xuất hiện ở: đầu, chân trang, `<title>` mặc định, trích dẫn (thông dụng, APA 7), JSON-LD `WebSite` / `Organization`, ảnh chia sẻ chung, trang Về dự án.
- Câu chữ và từ cấm: `docs/20` mục 10 và AGENTS.md (không "bản gốc", "bản đúng", "bản chuẩn", "bản sai", "AI giải thích", "câu hay nhất"; bản đại diện gọi là "Bản tham chiếu"). Không dùng mã cấp L0–L3 hay "mức A/B" trên giao diện.
- Văn bản dự án tự viết đặt dấu theo SGK (hoà, thuỷ — D-038); lời trích giữ như nguồn.

## 3. Kiến trúc thông tin

```text
Đầu trang (máy tính)
  Phongdao · Đọc ▾ · Khám phá ▾ · Cho lớp học · Tra cứu ▾ · [ô tìm] · ☆ Đã lưu

Đọc ▾        Tất cả · Ca dao · Tục ngữ – Thành ngữ · Đồng dao & Trò chơi · Vè · Câu đố · (Truyện)
             — chỉ thể loại đã qua cổng (D-091); thể loại 0 bài tự ẩn
Khám phá ▾   Tuyển tập · Chủ đề · Theo dịp · Vùng & Bản đồ · Mô-típ · Một câu ngẫu nhiên
Cho lớp học  chỉ hiện khi ≥ 3 bài học có bộ câu SGK (đợt 5)
Tra cứu ▾    Tìm · Từ điển · Hay bị nhầm là ca dao · Phương pháp & nguồn
(Nghe)       chỉ khi giọng đọc sinh sẵn qua phiên nghe thử (đợt 5)

Thanh dưới (điện thoại, 56 px, không che focus, ẩn khi in)
  Trang chủ · Tìm · Khám phá · Đã lưu

Chân trang
  Về dự án · Phương pháp · Nguồn · Chính sách thu thập dữ liệu · Quyền riêng tư · Góp tư liệu · Báo lỗi
  Giấy phép CC BY · Không quảng cáo, không theo dõi
```

**Đường dẫn:**

| Trang | Đường dẫn |
|---|---|
| Tác phẩm | `/<thể loại>/<tên>/` — `ca-dao`, `tuc-ngu`, `dong-dao`, `ve`, `cau-do`; sau: `truyen`, `hat-ru`, `ho`, `ly` |
| Tất cả | `/kho/` (giữ) |
| Thể loại | `/the-loai/<thể loại>/` |
| Chủ đề, vùng, mô-típ, hình thức | `/chu-de/<tên>/`, `/vung/<tên>/`, `/mo-tip/<tên>/`, `/hinh-thuc/<tên>/` |
| Tuyển tập | `/tuyen-tap/`, `/tuyen-tap/<tên>/` |
| Từ điển, mục từ | `/tu-dien/`, `/tu-dien/<tên>/` |
| Trò chơi | `/tro-choi/`, `/tro-choi/<tên>/` |
| Đố vui, Hay bị nhầm, Bản đồ, Tìm, Đã lưu, Một câu ngẫu nhiên | `/do-vui/`, `/hay-bi-nham/`, `/ban-do/`, `/tim/`, `/da-luu/`, `/ngau-nhien/` |
| Cho lớp học (đợt 5) | `/lop-hoc/`, `/lop-hoc/<lớp>/` |
| Bài phân tích | `/phan-tich/<tên>/` |

Đường dẫn MVP đổi khi chuyển (`/tinh-huong/…` → tuyển tập, `/the-tho/…` → `/hinh-thuc/…`, đuôi `-2`) đều chuyển hướng 301 (D-089).

## 4. Trang chủ (S01)

**Mục tiêu:** người mới chọn được lối vào trong 10 giây; đến một bài hữu ích trong ≤ 3 lần chạm.

Thứ tự (chỉ hiện khối có dữ liệu):
1. Tên + khẩu hiệu + ô tìm ("Gõ câu bạn nhớ, gõ không dấu cũng được").
2. **Bạn là:** (Học sinh) (Giáo viên) (Phụ huynh) (Yêu văn hoá) — chọn một lần, nhớ trên máy, bỏ qua được; không chọn = thứ tự mặc định.
3. **Câu hôm nay:** một tác phẩm L2 (đổi mỗi ngày, không lặp trong 30 ngày): lời + nghĩa ngắn + "Đọc ›".
4. **Lối vào:** Hiểu nghĩa tục ngữ · Theo dịp (dịp gần nhất: Tết, Rằm tháng Giêng, 8/3, Vu Lan, Trung thu, 20/11…) · Cùng bé chơi · Bộ câu cho tiết học (khi Cho lớp học mở).
5. **Tuyển tập** nổi bật (3–5 thẻ: tên · số câu).
6. **Bạn có biết?** (1–2 câu đã duyệt, liên kết về bài).
7. **Đọc theo thể loại** (thẻ có số bài; ẩn thể loại 0 bài).
8. Đọc nhiều tuần này · Vừa bổ sung.
9. "Cách chúng tôi biên soạn ›" (trang Phương pháp).

Vai chỉ đổi **thứ tự** khối 4–8: Giáo viên → Bộ câu cho tiết học, tuyển tập; Phụ huynh → Cùng bé chơi, đồng dao, câu đố; Học sinh → Hiểu nghĩa tục ngữ, tìm; Yêu văn hoá → tuyển tập, vùng, bản đồ. Không vai nào bị giấu nội dung. Tắt JavaScript: thứ tự mặc định.

**Trạng thái:** thiếu bài L2 → bỏ Câu hôm nay; không tuyển tập công khai → bỏ khối 5. Lỗi dữ liệu → vẫn giữ ô tìm và thể loại.
**Nghiệm thu:** người thử tìm được nghĩa *Ăn cháo đá bát* ≤ 10 giây từ trang chủ; trang ≤ 200 KB; 0 lỗi axe.

## 5. Danh sách (S02, S03)

### 5.1. Tất cả (`/kho/`)

Lời dẫn ngắn → chip lọc (thể loại, hình thức, chủ đề, vùng; mỗi chip có số; số 0 ẩn) → sắp xếp A–Z / Mới thêm → danh sách thẻ bài (tên, 2 dòng đầu, nhãn thể loại, nghĩa ngắn nếu có, 👁 khi ≥ 10 lượt) → phân trang 30 bài, số trang ≥ 44 px. Điện thoại: bộ lọc thu gọn, ghi rõ đang lọc gì. Trang đã lọc `noindex`.

### 5.2. Thể loại (`/the-loai/<thể loại>/`)

Giới thiệu do người sáng lập viết (cách đọc thể loại) → bài tiêu biểu (người chọn; thiếu thì L2 đọc nhiều) → lối vào riêng (tục ngữ: hàng chữ cái A B C D Đ…; đồng dao: Theo trò chơi; câu đố: Đố vui, theo chủ đề đáp án; vè: theo sự kiện khi có bối cảnh) → chủ đề phổ biến → danh sách liên tục. Thiếu giới thiệu: chỉ tên thể loại, không câu độn.

### 5.3. Chủ đề, vùng, mô-típ, hình thức

Dạng "đọc liền": mô tả do người viết (nếu có) → danh sách thẻ có lời + nghĩa ngắn + chú thích địa danh (vùng) → tuyển tập liên quan. Lập chỉ mục khi ≥ 3 tác phẩm L2. Vùng nói rõ "nơi lưu truyền"; bản đồ dẫn sang "được nhắc đến".

## 6. Trang tác phẩm theo thể loại (S04–S09)

Thanh hành động chung: [❝ Trích dẫn] [↗ Chia sẻ] [☆ Lưu] [▣ Trình chiếu] [Aa]; Trích dẫn là hành động chính. Dưới tiêu đề: 👁 lượt đọc · ↗ chia sẻ · ❝ trích dẫn khi đủ ngưỡng (D-069).

Phần cuối chung (theo thứ tự): Chủ đề · Vùng · Thuộc tuyển tập ("Câu này có trong: … ›") → **Thông tin tư liệu** (mục 6.7) → Biên soạn (bút danh khi có, liên kết Phương pháp) → Đọc tiếp (3–5 bài: cùng mô-típ, chủ đề, từ, địa danh, vùng; mỗi bài có lý do) → **Bạn muốn góp gì?** (mục 8.1) → Cảm nghĩ đã duyệt, bài phân tích đã duyệt.

Bài thiếu phần: "Bài đang được bổ sung. Bạn biết thêm? Góp ý" — máy không tự điền.

### 6.1. Ca dao (S04)

Lời (lục bát thụt câu lục — Q-006 mặc định; từ có chú thích là nút chạm, popover nghĩa + nguồn theo chính sách) → dòng mức kiểm ("Lời theo bản lưu truyền phổ biến · chưa đối chiếu sách · Vì sao?" hoặc "Lời đã đối chiếu với <sách>, tr. N") → **Hiểu nhanh** (≤ 2 câu; "Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt" khi có dấu) → thanh hành động → ▸ Dị bản (đánh số, tô chữ khác; nơi, người kể khi có) → ▸ Chú thích (danh sách từ – lời giải) → Bạn có biết? → phần cuối chung.
**Nghiệm thu:** lời hiện đúng từng ký tự như lớp `loi`; trích dẫn đúng; tắt JavaScript vẫn đọc lời, chú thích ở cuối khối.

### 6.2. Tục ngữ – Thành ngữ (S05)

Câu chữ to một dòng → dòng mức kiểm → **Nghĩa** (Hiểu nhanh, trong 10 giây) → ▸ Nghĩa đen · nghĩa bóng → ▸ Dùng khi (ví dụ đã biên tập) → ▸ Câu gần nghĩa / trái nghĩa (liên kết) → ▸ Giải nghĩa trong sách (đợt 5) → thanh hành động → phần cuối chung. Phần chép từ Wiktionary ghi "Nguồn: Wiktionary ↗ · CC BY-SA" ngay dưới. Thiếu nghĩa: dòng "Câu này chưa có giải nghĩa. Bạn giúp được? Góp ý".

### 6.3. Vè và bài dài (S06)

Tiêu đề → số dòng · thời gian đọc → [Mục lục ▾] (đóng trên điện thoại) → các đoạn có tiêu đề (nhớ chỗ đang đọc trên máy, "Đọc toàn bài") → chú giải → ▸ **Bối cảnh** (sự kiện, năm, nơi, nhân vật — chỉ khi có nguồn; không gán giả) → dị bản → phần cuối chung.
**Nghiệm thu:** một bài dài thực tế và một bài rất dài (> 200 dòng) cuộn mượt, mục lục bàn phím được.

### 6.4. Đồng dao (S07)

Thẻ thông số trò chơi (👥 số người · 🎂 độ tuổi · 📍 nơi chơi · ⏱ thời lượng · đồ dùng; thiếu ô nào bỏ ô đó; luôn chữ + biểu tượng) → **[♪ Đọc cùng bé]** (một dòng một lần, chữ rất to, vỗ tay theo chữ, không âm thanh, Esc thoát) → Lời → **Cách chơi** (các bước đánh số; "Nguồn cách chơi: … · giấy phép" ngay dưới) → ▸ Dị bản ở các vùng → phần cuối chung → Trò chơi cùng tuổi.
Trẻ không cần đăng nhập, không thu dữ liệu; không tự phát âm thanh.

### 6.5. Câu đố (S08)

Câu hỏi (chữ lớn) → [Gợi ý] (`<details>`, khi có) → **[Hiện đáp án]** (`<details>`, hành động chính; tắt JavaScript vẫn mở được) → trong đó: đáp án, ảnh đáp án (Commons, ghi công, khi có), giải thích (khi có) → "Đố bạn bè ›" (vào Đố vui với chủ đề này) → câu đố cùng đáp án → phần cuối chung.
Đáp án nằm trong HTML (máy tìm kiếm vẫn đọc được), chỉ thu gọn khi hiển thị (D-087 (3), thay phần D-058).

### 6.6. Truyện (S09, đợt 5)

Tên → "Bản kể theo: <sách>, <năm>, tr. N · Kể lại: <bút danh>" → thời gian đọc · từ N tuổi · số đoạn → [Kể cho bé] [Mục lục ▾] → các đoạn (nhớ chỗ đọc) → ▸ Nhân vật → ▸ Chú thích → ▸ Ý nghĩa (biên soạn viết, tách khỏi truyện) → ▸ Bản kể khác → phần cuối chung. Cảnh báo bối cảnh lịch sử khi cần, đặt ngay đầu. Chỉ mở qua cổng thể loại; nguồn chỉ là sách hết bản quyền người sáng lập đã kiểm.

### 6.7. Thông tin tư liệu (S18, D-085)

Khối thu gọn cuối bài, mỗi lớp một dòng; dòng nào hiện là theo chính sách hiện của hồ sơ nguồn (mặc định như hiện nay):

```text
Lời       Theo bản lưu truyền phổ biến · chưa đối chiếu sách · Vì sao?
Nghĩa     Biên soạn: <bút danh> · có hỗ trợ của AI, đã duyệt <ngày>
Chú thích 3 mục · nguồn tham khảo đã ẩn (Chính sách thu thập dữ liệu)
Cách chơi Wikipedia — <bài> · CC BY-SA 4.0
Ảnh       Wikimedia Commons · <tác giả> · CC BY-SA 4.0
```

Tên khối: "Thông tin tư liệu" hoặc "Lời này từ đâu?" — người sáng lập chọn trên bản xem trước. Liên kết nguồn đã ẩn không có trong HTML gửi người đọc; người biên soạn đăng nhập thấy thêm "Chỉ người biên soạn thấy dòng này · Nguồn đã ẩn: …". Ghi công giấy phép mở vẫn ngay tại chỗ (dưới ảnh, dưới đoạn chép).

## 7. Khám phá và tra cứu

### 7.1. Tuyển tập (S12)

```text
Trang chủ › Tuyển tập
TUYỂN TẬP · <loại: Chủ đề / Theo dịp / Tình huống / Bài học>
Ca dao, tục ngữ về mẹ
18 câu · 6 phút · cập nhật <ngày> · Biên soạn: <bút danh>
Lời dẫn (người biên soạn viết, 2–4 câu)
[▣ Trình chiếu cả bộ] [⎙ In]           ← hành động chính
Mục lục ▾ (theo nhóm)
1  CA DAO                               ← nhãn thể loại từng câu
   Công cha như núi Thái Sơn…           ← chạm từ xem nghĩa
   Nghĩa: …                             ← Hiểu nhanh của bài
   Hợp khi: …                           ← ghi chú của mục (nếu có)
   Lời phổ biến · chưa đối chiếu sách
   [❝] [☆]                    Xem bài ›
2  TỤC NGỮ …
HAY BỊ NHẦM                             ← chỉ câu có cờ, kèm lý do
Tuyển tập khác ›
```

- Công khai khi có lời dẫn và ≥ 8 câu đã đăng (agent có thể chỉnh ngưỡng, ghi trong PR).
- `<title>` "…: N câu có giải nghĩa" chỉ khi mọi câu có Hiểu nhanh.
- Trang `/tuyen-tap/`: nhóm theo loại; "Theo dịp" xếp dịp gần nhất lên đầu.
- 10 tuyển tập đầu (D-087): mẹ, cha, thầy cô, quê hương, học tập, lao động, tình yêu, Tết, đoàn kết, thiên nhiên. Tình huống của MVP ("Hôm nay bạn đang…") thành tuyển tập loại "Tình huống".
- Tuyển tập 30 câu ≤ 300 KB.

### 7.2. Một câu ngẫu nhiên (S15)

Một tác phẩm L2 → nghĩa → [Câu khác] → "Xem đầy đủ ›". Không lặp trong phiên (nhớ trên máy); không chọn bài có cờ nghi cho vị trí này. Thiếu dữ liệu: chuyển về `/kho/`.

### 7.3. Tìm (S14)

- Ô tìm ở mọi trang; kết quả hiện khi gõ (JavaScript), tắt JavaScript thì gửi form `/tim/?q=`.
- Khớp đúng một câu: thẻ lớn đầu tiên (lời + nghĩa + nhãn + "Đọc bài").
- Nhóm theo thể loại (Ca dao 4 · Tục ngữ 2 · Từ điển 1); câu có cờ nghi vẫn hiện, kèm cờ; nhóm cuối "Có các chữ này nhưng không liền nhau".
- Gõ sai: "Có phải bạn tìm *…*?".
- Không ra: gợi ý chủ đề gần, tuyển tập gần; nếu kho có câu này: "Câu này đang chờ biên soạn" (không hiện lời kho) + nút **[Mong có câu này]** (sự kiện `mong_co_cau`, D-088; bấm rồi thì nút thành "Đã ghi nhận, cảm ơn bạn").
- **Nghiệm thu:** bộ truy vấn tiếng Việt có dấu / không dấu / sai một chữ (gồm L-09) cho kết quả đúng; không timeout trên dữ liệu hiện tại.

### 7.4. Từ điển và mục từ (S11)

`/tu-dien/`: ô tìm → hàng chữ cái A–Z → lọc (từ cổ, Hán Việt, phương ngữ, địa danh, vùng). Mục từ: từ → lời giải → nguồn lời giải (theo chính sách) → ảnh Commons (ghi công; không ảnh thì không chừa chỗ) → bản đồ nhỏ (địa danh đã xác nhận, có Hoàng Sa, Trường Sa) → các bài có từ này (thẻ có nhãn) → từ cùng vùng.

### 7.5. Bản đồ (S13)

Chọn vùng → bản đồ SVG tự lưu (không dịch vụ ngoài) → danh sách điểm Bắc → Nam → mỗi nơi các bài nhắc tới ("được nhắc đến"). Điện thoại: danh sách trước, bản đồ sau. Không tải bản đồ được vẫn duyệt danh sách.

### 7.6. Đố vui (`/do-vui/`)

```text
ĐỐ VUI · Con vật · 3/10
  Con gì ăn no / Bụng to mắt híp…
[ Gợi ý ]              ← <details>
[ Hiện đáp án ]        ← <details>, hành động chính
  Đáp án: … · Vì sao: …
[ Câu khác › ]   Xem bài đầy đủ ›
```

10 câu một lượt, chọn theo chủ đề đáp án (con vật, đồ vật, cây cối…) và độ khó; không chấm điểm, không bảng xếp hạng, không phạt trẻ; "đã đố tới câu nào" nhớ trên máy. Tắt JavaScript: một trang 10 câu, mỗi câu một `<details>`.

### 7.7. Trò chơi (`/tro-choi/`)

Lọc theo độ tuổi, số người, trong nhà / ngoài trời, cần đồ dùng hay không; thẻ trò chơi có thông số; trang trò chơi: Cách chơi rồi các bài đồng dao đi kèm. Không suy diễn luật chơi.

### 7.8. Hay bị nhầm là ca dao (`/hay-bi-nham/`)

Danh sách câu có cờ "Nghi không phải dân gian", mỗi câu kèm lý do người biên soạn viết và nguồn của lý do. Trang công khai khi có ≥ 5 câu.

### 7.9. Cho lớp học (S16, đợt 5)

Chọn lớp và bộ sách (nhớ trên máy) → bài học có bộ câu → [Trình chiếu] [Phiếu in] [Sao chép trích dẫn cả bộ]. Chỉ câu người biên soạn đã thấy trong SGK. Phiếu in A4: lời, Hiểu nhanh, chú thích, trích dẫn, không màu nền. "Chưa thấy bài học bạn cần? Gửi tên sách, trang ›".

## 8. Người đọc góp và trang dự án

### 8.1. Bạn muốn góp gì? (S17)

Một khối cuối bài, chọn loại: Ở quê nói khác (dị bản) · Góp ý chú thích · Báo lỗi · Gửi số trang (tên sách, trang) · Cảm nghĩ ngắn · Viết cảm nhận (bài phân tích). Ô: nội dung → xuất xứ (nơi, người kể — không bắt buộc) → cam kết tự viết, đồng ý CC BY 4.0 → gửi. Không bắt tên thật, email, số điện thoại; ô bẫy + giới hạn mỗi giờ + Turnstile khi có khoá; báo lỗi vẫn giữ chữ đã gõ. Mọi thứ vào hàng chờ, không công bố tự động. Ghi âm không mở ở V2 (cần hồ sơ đồng ý, lằn ranh 3).

### 8.2. Đã lưu (`/da-luu/`)

☆ Lưu trên trang bài; danh sách nằm trên máy người đọc (`localStorage`, bọc try/catch); trang Đã lưu: lọc theo thể loại, Bỏ lưu, [Trình chiếu cả danh sách]; trống: hướng dẫn lưu; nói rõ xoá dữ liệu trình duyệt là mất danh sách; site không biết ai lưu gì.

### 8.3. Phương pháp, Nguồn, Về dự án, Chính sách thu thập dữ liệu, Quyền riêng tư (S18)

Trang tĩnh do người sáng lập viết (mang từ MVP, sửa tên dự án). Phương pháp có mục "Dòng nhỏ dưới lời nói gì" (đích của "Vì sao?") và cách đọc "Thông tin tư liệu". Không hứa điều site chưa làm.

### 8.4. 404 và lỗi

Trang 404 riêng: ô tìm điền sẵn chữ từ đường dẫn, gợi ý tuyển tập. Lỗi tìm, bản đồ, đố vui: quay về dạng không JavaScript, có câu giải thích và lối đi tiếp.

## 9. Hệ thống thiết kế, trợ năng, ngân sách

### 9.1. Token

- **Giữ** `--giay`, `--muc`, `--son`, `--nhat`, màu nhãn (`docs/20` mục 1.1, đã đo tương phản); Literata (lời, tiêu đề) + Be Vietnam Pro (giao diện) tự lưu trữ; bề rộng đọc 38em; chạm ≥ 44 px (nút chính ≥ 48 px).
- **Thêm:** thang cỡ chữ 7 bậc (lời ca dao lớn hơn văn xuôi 1 bậc; câu tục ngữ 2 bậc); thang khoảng cách 8 bậc; màu nhãn từng thể loại (luôn chữ + biểu tượng).
- **Biến thể "bảng màu đề xuất"** để so trên bản xem trước: #F4EDE1 nền, #332C25 chữ (11,8:1), #9F443A son (5,4:1), #398578 xanh trầm (**3,76:1 — không dùng cho chữ thường**, chỉ chữ ≥ 24 px hoặc thành phần ≥ 3:1, hoặc làm đậm hơn). Bản tối đo riêng. Người sáng lập chọn bằng mắt.

### 9.2. Thành phần (khối)

| Thành phần | Trạng thái phải có |
|---|---|
| Thẻ bài | đủ / thiếu nghĩa / có cờ |
| Khối lời | thơ, câu một dòng, bài dài; có / không chú thích; không JavaScript |
| Dòng mức kiểm | chưa đối chiếu / đã đối chiếu sách / đã đối chiếu web |
| Khối nghĩa | chỉ Hiểu nhanh / đủ lớp / có AI hỗ trợ / chưa có |
| Khối dị bản | 1 / nhiều / có nơi, người kể |
| Thẻ thông số trò chơi | thiếu ô nào bỏ ô đó |
| Khối đố | chưa mở / đã mở; có / không gợi ý, ảnh, giải thích |
| Thẻ tuyển tập | — |
| Thanh hành động | trang bài / trong tuyển tập (gọn) / Đã lưu |
| Chip lọc | chọn / không / 0 (ẩn) |
| Thông tin tư liệu | theo chính sách / người biên soạn đăng nhập |
| Trạng thái trống, lỗi | câu giải thích + lối đi tiếp |

### 9.3. Trợ năng

WCAG 2.2 AA trên mọi mẫu, kể cả trang quản trị. Kiểm bàn phím cho: chạm từ, Đố vui, Đọc cùng bé, mục lục, chip lọc, chọn vai, trình chiếu. `prefers-reduced-motion`: tắt nhịp vỗ tay chạy chữ, giữ tô chữ tĩnh. Focus không bị thanh trên / dưới che (2.4.11). HTML ngữ nghĩa trước ARIA.

### 9.4. Ngân sách và sạch

| Chỉ số | Mục tiêu (CI kiểm) |
|---|---|
| Trang tác phẩm mỗi thể loại | ≤ 200 KB |
| JavaScript trang tác phẩm | ≤ 21 KB |
| Tuyển tập 30 câu, không ảnh | ≤ 300 KB |
| Ảnh | tải lười, có kích thước; CLS ≤ 0,1 |
| LCP / INP (số đo thật p75, khi có) | ≤ 2,5 s / ≤ 200 ms |
| Lỗi axe | 0 |
| Liên kết ra ngoài | chỉ tên miền trong danh sách cho phép (Wikimedia, Wikipedia, Wiktionary, Wikidata, Wikisource, nguồn trong hồ sơ nguồn) |

Không quảng cáo, không khối liên kết, không đo bên thứ ba, không tự phát âm thanh, không popup.

## 10. SEO và chia sẻ

- Lập chỉ mục: tác phẩm L2+, tuyển tập đã công khai, trang thể loại, trang chủ đề / vùng / mô-típ có ≥ 3 tác phẩm L2, mục từ có lời giải dự án viết hoặc nguồn mở. Còn lại `noindex`. Bài phân tích không làm tác phẩm được lập chỉ mục (D-084).
- `<title>` và mô tả: tác phẩm L2 "<tên> — nghĩa và cách dùng", mô tả từ Hiểu nhanh; chưa L2: tên + thể loại, không hứa điều trang không có.
- Ảnh chia sẻ 1200×630 mỗi tác phẩm (thể loại, 4 dòng đầu, dòng mức kiểm, phongdao.com); ảnh chung cho trang khác.
- JSON-LD, sitemap, canonical: `docs/26` mục 10.

## 11. Đo lường

Đúng 5 sự kiện (D-088) + lượt đọc: `tim_khong_ket_qua`, `trich_dan_sao_chep`, `chu_giai_mo`, `chia_se`, `mong_co_cau`. Không cookie, không IP, không bên thứ ba; máy dò và người đăng nhập không tính. Số liệu chỉ để chọn việc tiếp theo (Hộp việc, Tuần này).

## 12. Thử với người dùng

5 người (2 giáo viên, 1 phụ huynh, 2 học sinh hoặc người trẻ) trên điện thoại của họ, 15 phút mỗi người, trên bản xem trước cuối đợt 3 (T-048):

| Tác vụ | Đạt khi |
|---|---|
| Tìm nghĩa câu *Ăn cháo đá bát* | thấy nghĩa ≤ 10 giây từ trang chủ |
| Chuẩn bị 5 câu tục ngữ về lao động để chiếu lên lớp | vào trình chiếu ≤ 3 phút |
| Chọn một trò chơi cho 4 bé 5 tuổi trong nhà | ≤ 1 phút |
| "Câu này có phải ca dao không?" (một câu thơ hay bị nhầm) | thấy cờ và lý do |
| "Câu này lấy từ đâu, ai giải nghĩa?" | mở Thông tin tư liệu, nói lại đúng |

Đạt ≥ 85% tác vụ; chỗ lúng túng thành task.

## 13. Không làm

Liên kết ra ngoài không liên quan; tra năm sinh, phong thuỷ, meme; bài văn dài viết lại nghĩa từng câu; tuyên bố "đã đối chiếu", "hàng đầu", số người dùng không kiểm được; mục lục lặp, đoạn nhắc lại, ảnh khung trống; cảnh báo "chưa kiểm chứng" để cuối bài; văn mẫu, dàn ý; truyện nước ngoài, thơ hiện đại để tăng số; tài khoản người đọc; bình luận mở; chấm điểm trẻ.
