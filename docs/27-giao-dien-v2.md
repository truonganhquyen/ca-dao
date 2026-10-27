# 27 · Đặc tả giao diện V2

| | |
|---|---|
| Phiên bản | 1.0 · 27/10/2026 |
| Trạng thái | **Đã duyệt (D-087).** Thay đổi hướng thẩm mỹ hay hành vi đã chốt là Human Gate H4 |
| Đọc khi | Task V2.2 trở đi (T-153..T-156), T-140 (đổi tên), mọi giao diện mới |
| Căn cứ | `docs/nghien-cuu/v2-ux-ui-2026-10-27.md` (học từ vanvn.net), khảo sát 27/10, báo cáo E2E 26/10 |
| Giữ từ `docs/20` | Mục 1 (ngôn ngữ thị giác, token màu, phông), mục 10 (câu chữ, từ cấm), mục 11 (trợ năng). Các mục khác của `docs/20` mô tả MVP đang chạy; mục 4.1 (nhãn A–D, "đọc theo lớp" thời Astro) là lịch sử |

Tinh thần giữ nguyên: **"Một trang sách cổ được chăm chút, đặt trong một chiếc điện thoại hiện đại."** Hướng mỹ thuật: **Sách Việt đương đại** (chữ, giấy nhẹ, son trầm, khoảng trắng); minh hoạ chọn lọc chỉ ở đồng dao, truyện, tuyển tập khi có ảnh có quyền; "atlas" chỉ ở bản đồ và chuyên đề vùng.

## 1. Nguyên tắc UX

1. **Đơn vị nhỏ, tập hợp lớn.** Mọi trang tập hợp ghép từ bài đơn; mỗi câu trong đó liên kết về trang riêng, trích dẫn được. Nghĩa viết một lần.
2. **Trả lời trước, mở sâu sau.** Màn hình đầu (390 px) trả lời câu người đọc đang hỏi; phần sâu trong `<details>`.
3. **Mỗi câu một nhãn đúng.** Thể loại hiện ở mọi thẻ; câu có cờ "Nghi không phải dân gian" mang cờ ở mọi nơi.
4. **Nguồn đặt ngay chỗ khẳng định.** Dòng mức kiểm ngay dưới lời; nguồn bản kể ngay dưới tên truyện; "chưa kiểm chứng" ngay cạnh điều được kể. Phần nào ẩn với người đọc là do chính sách hiện (D-084), không do bố cục.
5. **Lối vào theo vai, không theo dõi.** Lựa chọn lưu trên máy người đọc.
6. **Một hành động chính mỗi vùng.** Trang bài: Trích dẫn. Tuyển tập: Trình chiếu. Đố vui: Hiện đáp án. Trò chơi: Đọc cùng bé.
7. **Không độn chữ.** Khối không có dữ liệu thì không hiện, kể cả tiêu đề; không ảnh khung trống.
8. **Nhẹ và sạch là tính năng.** Mục 8.

## 2. Tên và nhận diện

- Tên hiển thị: **Thư viện Văn học Dân gian Việt Nam**; tên ngắn ở đầu trang điện thoại: **Phongdao**; khẩu hiệu "Đọc một câu. Hiểu một đời sống." (D-082).
- Đổi ở: tiêu đề site (Cài đặt → Tổng quan, người sáng lập bấm), đầu trang, chân trang, `<title>` mặc định, dòng trích dẫn (thông dụng, APA 7: tên site), JSON-LD `WebSite`/`Organization`, ảnh chia sẻ chung, trang Về dự án, Chính sách thu thập dữ liệu, blueprint Playground (T-140).
- Không đổi: tên miền, đường dẫn, tên plugin / theme / mã định danh.

## 3. Kiến trúc thông tin

```text
Đầu trang (máy tính)
  Phongdao · Đọc ▾ · Khám phá ▾ · Cho lớp học · Tra cứu ▾ · [ô tìm] · ☆ Đã lưu

Đọc ▾        Ca dao · Tục ngữ – Thành ngữ · Đồng dao & Trò chơi · Vè · Câu đố · (Truyện)
             — chỉ thể loại đã qua cổng (D-088); thể loại 0 bài tự ẩn
Khám phá ▾   Tuyển tập · Chủ đề · Theo dịp · Vùng & Bản đồ · Mô-típ · Một câu ngẫu nhiên
Cho lớp học  (chỉ hiện khi ≥ 3 bài học có bộ câu SGK) Chọn lớp & bộ sách · Bộ câu · Trình chiếu · Phiếu in
Tra cứu ▾    Tìm · Từ điển · Hay bị nhầm là ca dao · Phương pháp & nguồn
(Nghe)       chỉ khi giọng đọc sinh sẵn qua phiên nghe thử (T-105 → T-160)

Thanh dưới (điện thoại, 56 px, không che focus)
  Trang chủ · Tìm · Khám phá · Đã lưu

Chân trang
  Về dự án · Phương pháp · Chính sách thu thập dữ liệu · Quyền riêng tư · Góp tư liệu · Báo lỗi
  Giấy phép CC BY · Không quảng cáo, không theo dõi
```

**Đường dẫn:** giữ mọi đường dẫn đang có (`/ca-dao/<tên>/`, `/tuc-ngu/<tên>/`, `/chu-de/…`, `/vung/…`, `/mo-tip/…`, `/tu-dien/…`, `/tro-choi/…`, `/kho/`, `/tim/`, `/ban-do/`, `/da-luu/`). Thêm: `/tuyen-tap/`, `/tuyen-tap/<tên>/`, `/lop-hoc/`, `/lop-hoc/<lớp>/`, `/hay-bi-nham/`, `/do-vui/`; sau: `/truyen/<tên>/`.

## 4. Màn hình

Phác ở 390 px; máy tính dùng cột đọc 38em, thêm cột phụ cho mục lục khi trang dài. Mỗi màn hình nêu trạng thái trống / lỗi.

### 4.1. Trang chủ (S01)

Thứ tự (chỉ hiện khối có dữ liệu):
1. Tên + khẩu hiệu + ô tìm ("Tìm một câu, gõ không dấu").
2. **Bạn là:** (Học sinh)(Giáo viên)(Phụ huynh)(Yêu văn hoá) — chọn một lần, nhớ trên máy (`localStorage`, bọc try/catch), bỏ qua được.
3. **Câu hôm nay** (bài L2): lời + nghĩa ngắn + "Đọc ›".
4. **Lối vào:** Hiểu nghĩa tục ngữ · Theo dịp (gần nhất: Tết, Vu Lan, 20/11…) · Cùng bé chơi · Bộ câu cho tiết học (khi "Cho lớp học" đã mở).
5. **Tuyển tập** nổi bật (tên · số câu).
6. **Đọc theo thể loại** (số bài; ẩn thể loại 0 bài).
7. Đọc nhiều tuần này · Vừa bổ sung.
8. "Cách chúng tôi biên soạn ›".

Vai chỉ đổi **thứ tự** khối: Giáo viên → Bộ câu cho tiết học, tuyển tập lên đầu; Phụ huynh → Cùng bé chơi, đồng dao, câu đố; Học sinh → ô tìm, Hiểu nghĩa tục ngữ; Yêu văn hoá → tuyển tập, vùng, bản đồ. Không vai nào bị giấu nội dung. Tắt JavaScript: thứ tự mặc định.

### 4.2. Trang bài theo thể loại (S04–S08)

| Thể loại | Màn hình đầu | Thu gọn / sau |
|---|---|---|
| **Ca dao** | Lời (lục bát thụt câu lục, Q-006) → dòng mức kiểm → Hiểu nhanh → thanh hành động | Dị bản · Chú thích · Thông tin tư liệu → Thuộc tuyển tập → Đọc tiếp → Góp |
| **Tục ngữ – Thành ngữ** | Câu chữ to một dòng → dòng mức kiểm → **Nghĩa** (Hiểu nhanh, ≤ 2 câu) | ▸ Nghĩa đen · nghĩa bóng ▸ Dùng khi (ví dụ đã biên tập) ▸ Câu gần nghĩa / trái nghĩa (liên kết) → hành động |
| **Vè, bài dài** | Tiêu đề → số dòng, thời gian đọc → mục lục (đóng trên điện thoại) | Các đoạn (nhớ chỗ đọc) → chú giải → Bối cảnh (khi có nguồn; không gán giả) → dị bản |
| **Đồng dao** | Thẻ thông số trò chơi (người · tuổi · nơi · thời lượng; thiếu ô nào bỏ ô đó) → **[Đọc cùng bé]** → Lời | Cách chơi (các bước đánh số, nguồn + giấy phép ngay dưới) → Dị bản ở các vùng → Thông tin tư liệu → Trò chơi cùng tuổi |
| **Câu đố** | Câu hỏi → **Đáp án hiện sẵn** (D-058) → giải thích (nếu có) | "Đố bạn bè ›" mở chế độ Đố vui (mục 4.5) → câu đố cùng đáp án |
| **Truyện** (V2.3) | Tên → "Bản kể theo: <sách>, <năm>, tr. N · Kể lại: <bút danh>" → thời gian, độ tuổi, số đoạn → [Kể cho bé] [Mục lục] | Các đoạn → Nhân vật → Chú thích → Ý nghĩa (biên soạn viết) → Bản kể khác → Thông tin tư liệu |

Thanh hành động: [❝ Trích dẫn] [↗ Chia sẻ] [☆ Lưu] [▣ Trình chiếu]; Trích dẫn là hành động chính.

Bài thiếu phần: "Bài đang được bổ sung. Bạn biết thêm? Góp ý" — không để khối trống, không để máy tự điền.

### 4.3. Thông tin tư liệu (S18, D-084)

Khối thu gọn cuối bài, mỗi lớp một dòng; dòng nào hiện là theo cài đặt chính sách hiện (mặc định đúng như hiện nay):

```text
Lời       Theo bản lưu truyền phổ biến · chưa đối chiếu sách · Vì sao?
Nghĩa     Biên soạn: <bút danh> · có hỗ trợ của AI, đã duyệt 27/10/2026
Chú thích 3 mục · nguồn tham khảo đã ẩn (Chính sách thu thập dữ liệu)
Ảnh       Wikimedia Commons · <tác giả> · CC BY-SA 4.0
```

Tên hiển thị của khối: "Thông tin tư liệu" hoặc "Lời này từ đâu?" — người sáng lập chọn trên bản xem trước (U4). Khối thay dòng "[N nguồn tham khảo đã ẩn]" nhưng giữ nghĩa của D-078, D-081: liên kết nguồn đã ẩn **không có trong HTML** gửi người đọc; người biên soạn đăng nhập thấy thêm liên kết. Ghi công giấy phép mở vẫn ngay tại chỗ (dưới ảnh, dưới đoạn chép).

### 4.4. Tuyển tập "đọc liền" (S12)

```text
Trang chủ › Tuyển tập
TUYỂN TẬP
Ca dao, tục ngữ về mẹ
18 câu · 6 phút · cập nhật <ngày> · Biên soạn: <bút danh>
Lời dẫn (người biên soạn viết, 2–4 câu)
[▣ Trình chiếu cả bộ] [⎙ In]          ← hành động chính
Mục lục ▾
1  CA DAO                              ← nhãn thể loại từng câu
   Công cha như núi Thái Sơn…          ← chạm từ xem nghĩa
   Nghĩa: …                            ← Hiểu nhanh của bài
   Lời phổ biến · chưa đối chiếu sách
   [❝] [☆]                  Xem bài ›
2  TỤC NGỮ …
HAY BỊ NHẦM                            ← chỉ câu có cờ, kèm lý do
TRONG SÁCH GIÁO KHOA                   ← chỉ khi người biên soạn đã thấy trang sách
Tuyển tập khác ›
```

- Chưa đủ bài (dưới 8 câu đã đăng) hoặc chưa có lời dẫn → chưa công khai.
- `<title>` dạng "Ca dao, tục ngữ về mẹ: 18 câu có giải nghĩa" chỉ khi mọi câu đã có Hiểu nhanh; ngược lại "Ca dao, tục ngữ về mẹ".
- 10 tuyển tập đầu (D-087): mẹ, cha, thầy cô, quê hương, học tập, lao động, tình yêu, Tết, đoàn kết, thiên nhiên. Máy đề xuất danh sách câu từ chủ đề đã duyệt; người sáng lập chọn, xếp, viết lời dẫn.
- Trang bài có dòng "Câu này có trong: <tuyển tập> ›".

### 4.5. Đố vui (S08)

```text
ĐỐ VUI · Con vật · 3/10
  Con gì ăn no / Bụng to mắt híp…
[ Gợi ý ]              ← <details>
[ Hiện đáp án ]        ← <details>, hành động chính
  Đáp án: … · Vì sao: …
[ Câu khác › ]   Xem bài đầy đủ ›
```

10 câu một lượt, chọn theo chủ đề đáp án / độ khó; không chấm điểm, không bảng xếp hạng, không phạt trẻ; "đã đố tới câu nào" nhớ trên máy. Tắt JavaScript vẫn dùng được.

### 4.6. Trò chơi (`/tro-choi/`)

Lọc theo độ tuổi, số người, trong nhà / ngoài trời, cần đồ dùng hay không (dữ liệu `tro_choi`: số người, độ tuổi, đồ dùng đã có; V2 thêm nơi chơi, thời lượng). Không suy diễn luật chơi; cách chơi do người sáng lập viết hoặc chép có nguồn (T-103).

### 4.7. Cho lớp học (S16, V2.3)

Chọn lớp và bộ sách (nhớ trên máy) → các bài học có bộ câu → [Trình chiếu] [Phiếu in] [Sao chép trích dẫn cả bộ]. Chỉ câu người biên soạn đã thấy trong SGK (T-133 → T-159); không tự gán SGK. Phiếu in A4: lời, Hiểu nhanh, chú thích, dòng trích dẫn, không màu nền. "Chưa thấy bài học bạn cần? Gửi tên sách, trang ›".

### 4.8. Tìm (S14)

- Khớp đúng một câu: thẻ lớn đầu tiên (lời + Hiểu nhanh + nhãn + "Đọc bài").
- Nhóm theo thể loại, có số đếm; câu có cờ nghi vẫn hiện, kèm cờ.
- "Có phải bạn tìm …?" (T-132).
- Không ra: gợi ý chủ đề gần; nếu kho có câu này: "Câu này đang chờ biên soạn" (không hiện lời kho thô) + nút **"Mong có câu này"** (sự kiện `mong_co_cau`, D-087 (8)).
- Lỗi JavaScript: quay về form truyền thống `/tim/?q=`.

### 4.9. Từ điển, Hay bị nhầm, Bản đồ, Ngẫu nhiên

- **Mục từ:** từ → lời giải → nguồn lời giải → ảnh Commons (ghi công) → các bài có từ này (thẻ có nhãn) → từ cùng vùng. Không ảnh: không chừa khoảng trống.
- **`/hay-bi-nham/`:** danh sách câu có cờ "Nghi không phải dân gian", mỗi câu kèm lý do người biên soạn viết và nguồn của lý do (T-135 → T-151).
- **Bản đồ:** giữ T-104 (không dịch vụ ngoài); ghi rõ quan hệ "được nhắc đến" (địa danh trong lời) khác "lưu truyền" (vùng).
- **Một câu ngẫu nhiên:** chỉ bài L2 đã đăng, không lặp trong phiên; nút "Câu khác"; dữ liệu thiếu thì về `/kho/`.

## 5. Bàn biên tập (S17 phần duyệt, D-086)

```text
┌──────────────────────────────┐
│ Việc 3/27 · Nháp Hiểu nhanh  │
│ "Ăn quả nhớ kẻ trồng cây"    │
│ đọc 41 lần tuần này          │
│ Nháp (AI, chưa duyệt):       │
│ …                            │  ← chạm để sửa tại chỗ
│ Chú thích của bài: …         │
│ [ Để sau ] [ Sửa ] [ ĐĂNG ]  │  ← ≥ 48 px; máy tính 1/2/3
│ ☑ Có hỗ trợ của AI           │  ← người duyệt tự bỏ nếu viết lại hết
└──────────────────────────────┘
```

- Hộp việc: lọc theo loại; báo lỗi của người đọc đứng đầu.
- Mỗi thẻ luôn kèm chú thích, nguồn liên quan và nhãn AI khi có.
- Trình soạn có cấu trúc: ô Lời (một dòng thơ một dòng, nhận thể thơ ngay), Dị bản (nguồn / nơi / người kể), Chú thích (bảng từ – lời giải – nguồn, gợi ý từ Từ điển), Nghĩa (theo thể loại), Đối chiếu (ảnh trang quét bên cạnh, "Lời khớp trang này"); dải báo trùng ngay dưới ô Lời.
- Trang quản trị cũng đạt WCAG 2.2 AA.

## 6. Hệ thống thiết kế

### 6.1. Token

- **Giữ** `--giay`, `--muc`, `--son`, `--nhat`, các màu nhãn (`docs/20` mục 1.1, đã đo tương phản); phông Literata (lời, tiêu đề) + Be Vietnam Pro (giao diện) tự lưu trữ; bề rộng đọc 38em; chạm ≥ 44 px.
- **Thêm:** thang cỡ chữ 7 bậc (lời ca dao lớn hơn văn xuôi 1 bậc; câu tục ngữ 2 bậc); thang khoảng cách 8 bậc; màu nhãn từng thể loại (luôn chữ + biểu tượng, đo tương phản trước khi dùng).
- **Biến thể "bảng màu đề xuất"** (#F4EDE1 nền, #332C25 chữ, #9F443A son, #398578 xanh trầm) đặt cạnh bảng hiện tại trên bản xem trước V2.2; người sáng lập chọn bằng mắt (Q8). Đo trước: #332C25 trên #F4EDE1 = 11,8:1; #9F443A = 5,4:1; **#398578 = 3,76:1 → không dùng cho chữ thường**, chỉ cho chữ lớn hoặc thành phần giao diện (≥ 3:1), hoặc chỉnh đậm hơn. Bản tối của biến thể phải đo riêng.

### 6.2. Danh mục thành phần

| Thành phần (khối) | Trạng thái phải có |
|---|---|
| Thẻ bài (`cadao/the-bai`) | đủ / thiếu nghĩa / có cờ |
| Khối lời (`cadao/loi`) | thơ, câu một dòng, bài dài; có / không chú thích; không JS |
| Dòng mức kiểm (`cadao/muc-kiem`) | chưa đối chiếu / đã đối chiếu sách / đã đối chiếu web |
| Khối nghĩa (`cadao/nghia`) | chỉ Hiểu nhanh / đủ lớp / có AI hỗ trợ |
| Khối dị bản (`cadao/di-ban`) | 1 / nhiều / có nơi, người kể |
| Thẻ thông số trò chơi | thiếu ô nào bỏ ô đó |
| Khối đố (`cadao/thuc-hanh`) | trang bài (đáp án hiện) / Đố vui (chưa hiện, đã hiện) |
| Thẻ tuyển tập | — |
| Thanh hành động (`cadao/hanh-dong`) | trang bài / trong tuyển tập (gọn) |
| Chip lọc | chọn / không / 0 (ẩn) |
| Thông tin tư liệu (`cadao/tu-lieu`) | theo chính sách; người biên soạn đăng nhập |
| Trạng thái trống | "Bài đang được bổ sung. Bạn biết thêm? Góp ý" |
| Trạng thái lỗi (tìm, bản đồ, đố vui) | về dạng không JavaScript, có câu giải thích và lối đi tiếp |

Câu chữ và từ cấm: `docs/20` mục 10 và AGENTS.md (không "bản gốc", "bản đúng", "bản chuẩn", "bản sai", "AI giải thích", "câu hay nhất").

## 7. Trợ năng

WCAG 2.2 AA trên mọi mẫu thể loại và trang quản trị. Kiểm bàn phím cho: chạm từ, Đố vui, Đọc cùng bé, mục lục bài dài / truyện, chip lọc, chọn vai, Duyệt nhanh. `prefers-reduced-motion`: tắt nhịp vỗ tay chạy chữ, giữ tô chữ tĩnh. Focus không bị thanh trên / dưới che (2.4.11). Nhãn không chỉ dựa vào màu.

## 8. Ngân sách và sạch

| Chỉ số | Mục tiêu (kiểm trong CI) |
|---|---|
| Trang bài mỗi thể loại | ≤ 200 KB (hiện 177 KB) |
| JavaScript trang bài | ≤ 21 KB (AGENTS.md) |
| Tuyển tập 30 câu, không ảnh | ≤ 300 KB |
| Ảnh | tải lười, có kích thước khai báo; CLS ≤ 0,1 |
| LCP / INP (số đo thật, p75) | ≤ 2,5 s / ≤ 200 ms khi có dữ liệu |
| Lỗi axe | 0 |
| Liên kết ra ngoài | chỉ tên miền trong danh sách cho phép (Wikimedia, Wikisource, Wiktionary, Wikidata, nguồn đã ghi trong hồ sơ nguồn); CI báo lỗi khi xuất hiện tên miền khác |

Không quảng cáo, không khối liên kết, không đo bên thứ ba, không tự phát âm thanh, không popup.

## 9. Kiểm chứng với người dùng

Thử với 5 người (2 giáo viên, 1 phụ huynh, 2 học sinh hoặc người trẻ) trên điện thoại của họ, 15 phút mỗi người, trên bản xem trước V2.2; so với MVP và vanvn.net (T-048):

| Tác vụ | Đạt khi |
|---|---|
| Tìm nghĩa câu *Ăn cháo đá bát* | thấy nghĩa ≤ 10 giây từ trang chủ |
| Chuẩn bị 5 câu tục ngữ về lao động để chiếu lên lớp | vào trình chiếu ≤ 3 phút |
| Chọn một trò chơi cho 4 bé 5 tuổi trong nhà | ≤ 1 phút |
| "Câu này có phải ca dao không?" (một câu thơ hay bị nhầm) | thấy cờ và lý do |
| "Câu này lấy từ đâu, ai giải nghĩa?" | mở được Thông tin tư liệu, nói lại đúng |

## 10. Không học từ vanvn.net

Liên kết ra ngoài không liên quan; tra năm sinh, phong thuỷ, meme; bài văn dài viết lại nghĩa từng câu; tuyên bố "đã đối chiếu", "hàng đầu", số người dùng không kiểm được; mục lục lặp, đoạn nhắc lại, ảnh khung trống; cảnh báo "chưa kiểm chứng" để cuối bài; văn mẫu, dàn ý; truyện nước ngoài, thơ hiện đại để tăng số.
