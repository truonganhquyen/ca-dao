# 28 · Luồng nội dung và Bàn biên tập V2

| | |
|---|---|
| Phiên bản | 1.0 · 27/10/2026 (D-084, D-085, D-086) |
| Trạng thái | **Đã duyệt.** Đổi hành vi đã chốt phải ghi D-xxx |
| Đọc khi | Mọi task chạm nguồn, kho, chống trùng, quản trị (đợt 1 và 3) |
| Đọc kèm | `26` (dữ liệu, REST, quyền), `12` (chính sách nguồn), AGENTS.md (lằn ranh) |
| Người dùng chính | Người sáng lập — người biên soạn duy nhất, dưới 10 giờ/tuần, chủ yếu trên điện thoại, không đọc mã |

## 1. Nguyên tắc

1. **Máy làm nặng, người quyết nhanh.** Máy thu thập, chống trùng, gợi ý, tách lớp, xếp việc; agent soạn nháp; người biên soạn quyết. Mỗi thẻ việc là **một quyết định**, đủ thông tin để quyết ngay trên thẻ.
2. **Một nơi.** Mọi việc chờ người nằm ở Hộp việc; không phải nhớ trang nào có việc.
3. **Không chặn vì lý do biên tập (D-054).** Trình soạn nhắc (thiếu nghĩa, nghi trùng, thiếu nguồn), không chặn Đăng — trừ lỗi kỹ thuật và lằn ranh.
4. **Lằn ranh trong mã, không trong lời dặn** (mục 11).
5. **Điện thoại trước.** Mọi màn hình dùng được một tay ở 390 px; máy tính có phím tắt.

## 2. Vòng đời nội dung

```text
Nguồn tự động ──► Kho thu thập ──► Chống trùng ──┬─► trùng hệt / trùng một dị bản đã ghi → tự gắn (xem được, "Tách ra")
(Ca dao Mẹ…)     (riêng tư)                       ├─► có thể trùng → thẻ "Có thể trùng"
                                                  └─► mới → ỨNG VIÊN (thẻ việc, chưa phải tác phẩm)
Nhập tay ─────────────────────────────────────────► Soạn tác phẩm (nháp)
Người đọc gửi ────────────────────────────────────► thẻ "Người đọc gửi"
Agent soạn nháp theo lô ──────────────────────────► lớp nhap (ai_nhap) → thẻ "Nháp nghĩa"

ỨNG VIÊN ── Đăng ──► TÁC PHẨM đã đăng (lớp may_chep đã duyệt)
                     └─ máy gợi ý chủ đề / vùng / mô-típ / Wikidata / Bạn có biết → thẻ "Gợi ý"
TÁC PHẨM ── hậu kiểm: báo lỗi, góp ý, "mong có câu này", đọc nhiều chưa L2 → thẻ việc
```

Trạng thái tác phẩm: `nhap` (người đang soạn) · `publish` (đã đăng) · `an` (ẩn khỏi người đọc, giữ dữ liệu). Không còn hàng "Chờ duyệt" hàng loạt do máy tạo (D-086).

## 3. Nguồn vào

### 3.1. Nguồn tự động

- Cấu hình ở màn hình **Nguồn** (quyền `cadao_nguon`): bật / tắt, lịch lấy, giới hạn mỗi lượt, thể loại lấy, **"Chép chú thích"** (tắt được ngay khi nguồn yêu cầu, D-055), hồ sơ nguồn gắn kèm.
- Ca dao Mẹ: bộ đọc tất định chuyển từ MVP (khớp từng ký tự, lịch sự với máy chủ nguồn). Đợt 5 thêm Wikisource (hai sách 1897, 1901 — D-080).
- Kho thu thập là dữ liệu riêng tư: không lên trang, không vào repo công khai (AGENTS.md, KHÔNG BAO GIỜ 14).

### 3.2. Hồ sơ nguồn

Mỗi nguồn (Ca dao Mẹ, Wikipedia, Wiktionary, Commons, Wikidata, sách, người kể, người đọc, người biên soạn) có: loại, tên, tác giả, năm, giấy phép, mẫu ghi công, trạng thái xin phép, chính sách hiện (tại chỗ / Thông tin tư liệu / ẩn), "Chép chú thích". Thiếu thông tin thì để trống, hiện "chưa rõ". **Trạng thái xin phép và giấy phép chỉ người sáng lập đổi** (H3); đổi chính sách hiện → màn hình nhắc "Đổi ở đây là đổi chính sách — cần ghi quyết định".

### 3.3. Ứng viên

- Máy chọn ứng viên từ kho để đưa lên Hộp việc mỗi ngày (mặc định 10, chỉnh được): ưu tiên câu người đọc **mong có** hoặc **tìm không ra** mà kho có, rồi xoay vòng theo thể loại (tục ngữ – thành ngữ, đồng dao, câu đố, vè, ca dao), câu có chú thích trước.
- Thẻ ứng viên: lời, dị bản, chú thích (kèm nguồn), thể loại, gợi ý chủ đề / vùng, kết quả chống trùng.
- Hành động: **Đăng** (tạo tác phẩm đã đăng, lớp `may_chep` thành `da_duyet`) · **Sửa rồi đăng** (mở Soạn) · **Để sau** · **Bỏ** (ghi lý do; kho giữ, không hiện lại trừ khi người bấm "Xem lại").

### 3.4. Chống trùng

Giữ thuật toán MVP (dấu vân tay cả lời, cặp âm tiết, bốn mức: y hệt / gần như y hệt / có thể là dị bản / khác; D-065, D-074):
- y hệt, cùng thể loại → tự gắn vào tác phẩm có sẵn;
- trùng đúng một dị bản đã ghi → tự gắn làm dị bản;
- mọi lần tự gắn xem được ở Nguồn → Kho → Đã gộp, bấm "Tách ra" được;
- có thể trùng → thẻ "Có thể trùng": Cùng một bài · Gộp làm dị bản · Là bài khác · Để sau;
- hai tác phẩm đã đăng trùng nhau → thẻ "Gộp tác phẩm": chọn tác phẩm giữ lại; tác phẩm kia thành dị bản, đường dẫn cũ 301, lượt đọc, cảm nghĩ, phân tích, tuyển tập dồn sang; tác phẩm kia vào thùng rác (khôi phục được);
- trình soạn: dải "Có thể trùng với …" ngay dưới ô Lời, chỉ báo, không chặn.

### 3.5. Nháp của agent

- Agent lập danh sách đề xuất (câu tìm nhiều, mong có, đọc nhiều chưa L2), soạn nháp **theo lô** cho Hiểu nhanh, nghĩa đen / bóng / dùng khi, gợi ý câu đố, Bạn có biết — ngoài site, rồi nạp bằng `wp phongdao nhap-nhap <tệp.json>` **khi người sáng lập đồng ý cho lần đó** (AGENTS.md).
- Nháp vào lớp trạng thái `nhap`, người làm `ai_nhap`; chỉ cho tác phẩm đã đăng; không đụng lớp đã duyệt; không bao giờ vào lớp lời, dị bản.
- Agent không tự khẳng định câu có trong SGK, không viết lời dẫn tuyển tập, không viết sự kiện bối cảnh, cách chơi, lý do cờ nghi.

## 4. Bàn biên tập — các màn hình

Một mục menu **Phongdao** mở ứng dụng. Thanh dưới trên điện thoại: Hôm nay · Duyệt · Tác phẩm · Thêm.

### 4.1. Hôm nay

```text
┌──────────────────────────────┐
│ TUẦN NÀY                     │
│ ▸ 10 bài nên có nghĩa        │  ← đọc / tìm nhiều chưa L2
│ ▸ 4 câu người đọc mong có    │
│ ▸ 3 báo lỗi                  │
│ [ Bắt đầu duyệt (27) ]       │  ← vào Duyệt nhanh
│ ──────────────────────────── │
│ HỘP VIỆC         lọc: Tất cả▾│
│ ! Báo lỗi · "Thân em…"       │
│ ★ Mong có · "Ăn cây nào…"    │
│ ✎ Nháp nghĩa · 8             │
│ ＋ Ứng viên · 10             │
│ ⇄ Có thể trùng · 3           │
│ ? Gợi ý · 12                 │
│ ✉ Người đọc gửi · 2          │
│ ──────────────────────────── │
│ KHO ĐANG CÓ / THIẾU          │
│ Ca dao 154 (L2: 9) · Tục ngữ │
│ 4 (L2: 1) · …  Kho: 16.400   │
│ MÁY ĐANG LÀM: lấy nguồn ✓    │
└──────────────────────────────┘
```

**Thứ tự ưu tiên của thẻ:** báo lỗi của người đọc → mong có câu này (kho có) → nháp nghĩa của bài đọc / tìm nhiều → ứng viên → có thể trùng → người đọc gửi khác → gợi ý → Wikidata. Lọc theo loại.

### 4.2. Duyệt nhanh

```text
┌──────────────────────────────┐
│ Việc 3/27 · Nháp Hiểu nhanh  │
│ "Ăn quả nhớ kẻ trồng cây"    │
│ Tục ngữ · đọc 41 lần tuần này│
│ ──────────────────────────── │
│ Nháp (AI, chưa duyệt):       │
│ Hưởng thành quả thì nhớ công │  ← chạm để sửa tại chỗ
│ người làm ra nó.             │
│ Chú thích của bài: —         │
│ ──────────────────────────── │
│ [ Để sau ] [ Sửa ] [ ĐĂNG ]  │  ← ≥ 48 px; máy tính 1 / 2 / 3, J / K
│ ☑ Có hỗ trợ của AI           │  ← người duyệt tự bỏ nếu viết lại hết
└──────────────────────────────┘
```

| Loại thẻ | Thông tin trên thẻ | Hành động |
|---|---|---|
| Ứng viên | lời, dị bản, chú thích + nguồn, thể loại, kết quả chống trùng | Đăng · Sửa · Để sau · Bỏ |
| Nháp nghĩa (Hiểu nhanh, nghĩa đen / bóng / dùng khi, gợi ý câu đố, Bạn có biết) | lời, chú thích, nháp, dấu AI | Đăng (= duyệt lớp) · Sửa · Để sau · Bỏ nháp |
| Có thể trùng | hai lời đặt cạnh nhau, chỗ khác tô | Cùng một bài · Gộp làm dị bản · Là bài khác · Để sau |
| Gợi ý chủ đề / vùng / mô-típ | tác phẩm, gợi ý, lý do | Đồng ý · Sửa · Bỏ |
| Wikidata | từ, thực thể, mô tả, ảnh | Đúng · Sai · Bỏ qua |
| Người đọc gửi (dị bản, góp ý, số trang, báo lỗi) | nội dung, bài liên quan | Áp dụng (mở Soạn đúng chỗ) · Đã xử lý · Bỏ (rác) |
| Cảm nghĩ, bài phân tích | nội dung | Đăng · Bỏ |
| Mong có câu này | câu, số lần, câu khớp trong kho | Tạo ứng viên ngay · Để sau |
| Báo lỗi | lỗi, bài | Sửa (mở Soạn) · Không phải lỗi |

Mỗi lần bấm ghi nhật ký (ai, lúc nào, trước, sau). Mục tiêu: **≤ 60 giây mỗi thẻ** trên điện thoại. Hoàn tác thẻ vừa quyết trong 10 giây ("Hoàn tác").

### 4.3. Tác phẩm (soạn có cấu trúc)

Danh sách: tìm, lọc theo thể loại / cấp / thiếu gì (chưa nghĩa, chưa chủ đề, chưa vùng, có cờ), sắp xếp theo đọc nhiều. Thay hẳn trình soạn Gutenberg cho tác phẩm (D-086).

Trang soạn, từ trên xuống:

| Vùng | Nội dung | Ghi chú |
|---|---|---|
| Đầu | tên (máy đề xuất từ dòng đầu), thể loại, trạng thái, [Lưu nháp] [Đăng / Cập nhật] [Xem trang] | Đăng được dù thiếu phần (D-054) |
| Lời | ô nhiều dòng, một dòng thơ một dòng; máy nhận thể thơ ngay; dải "Có thể trùng với …" | Sửa lời đã đăng: hỏi xác nhận, ghi nhật ký, người làm thành `nguoi_viet` |
| Mức kiểm | "Lời đã đối chiếu với": sách (hồ sơ nguồn), trang, đường dẫn | Chỉ người điền; máy không tự điền |
| Dị bản | danh sách; mỗi dị bản: lời, nơi, người kể, năm, nguồn | Kéo để xếp |
| Nghĩa | Hiểu nhanh; tục ngữ: nghĩa đen, nghĩa bóng, dùng khi, câu gần / trái nghĩa (chọn tác phẩm); ô "Có hỗ trợ của AI" người tự bật / tắt | Nháp AI hiện ở ô riêng "Nháp máy — chưa duyệt" với nút "Dùng nháp này" |
| Chú thích | bảng từ – lời giải – nguồn; gợi ý từ Từ điển; nối mục từ | Lớp `may_chep` hiện nhãn "chép từ <nguồn>" |
| Theo thể loại | vè: đoạn, bối cảnh; đồng dao: trò chơi; câu đố: câu hỏi, đáp án, gợi ý, ảnh đáp án; truyện (đợt 5): bản kể, đoạn, nhân vật | |
| Phân loại | chủ đề, vùng, mô-típ, hình thức (lối hát), tuyển tập | Gợi ý máy hiện kèm lý do |
| Cờ, SGK | cờ "Nghi không phải dân gian" + lý do + nguồn; SGK: bộ sách, lớp, bài, trang (chỉ khi đã thấy sách) | Máy chỉ gợi ý cờ |
| Tư liệu | xem trước khối Thông tin tư liệu đúng như người đọc thấy | |
| Lịch sử | nhật ký các lớp | |

Nhắc (không chặn): thiếu Hiểu nhanh, thiếu chủ đề, nghi trùng, chú thích không neo được vào lời.

### 4.4. Tuyển tập

Tạo / sửa: tên, loại (chủ đề, dịp, tình huống, bài học), đối tượng, lời dẫn (người viết), nhóm, danh sách tác phẩm (tìm để thêm, kéo để xếp, ghi chú "Hợp khi…" từng mục), trạng thái. Máy đề xuất danh sách câu từ chủ đề đã duyệt; người chọn. Hiện số câu đã có Hiểu nhanh.

### 4.5. Từ điển

Mục từ: từ, loại (từ, địa danh, nhân vật), lời giải, nguồn lời giải, Wikidata (đã xác nhận / chờ), ảnh (Commons theo Wikidata hoặc ảnh riêng từ Thư viện ảnh, ghi công; ẩn ảnh được), các bài có từ này. Hàng xác nhận Wikidata cũng có ở Duyệt nhanh.

### 4.6. Trò chơi, mô-típ, chủ đề, vùng, thể loại

- **Trò chơi:** thông số, các bước, nguồn, giấy phép; nút "Lấy mục Cách chơi từ Wikipedia" (chỉ vi.wikipedia.org, ghi nguồn đúng bài, CC BY-SA); máy không tự viết cách chơi.
- **Mô-típ:** nhóm bài cùng cụm mở đầu do máy gom; người đặt tên, bỏ bài không hợp, viết mô tả.
- **Chủ đề, vùng:** bộ của dự án; bảng ánh xạ chủ đề nguồn → dự án; mô tả.
- **Thể loại:** giới thiệu, bài tiêu biểu.

### 4.7. Nguồn

Hồ sơ nguồn (mục 3.2) · Nguồn tự động (mục 3.1) · Kho (tìm, trạng thái, Đã gộp, "Tách ra") · Máy đang làm (hàng đợi, lỗi gần nhất).

### 4.8. Người đọc

Góp ý, dị bản, số trang, báo lỗi, cảm nghĩ, bài phân tích, "mong có câu này" — cùng các thẻ ở Hộp việc, xem dạng danh sách đầy đủ; khoá Turnstile (không gửi khoá cho ai, kể cả agent).

### 4.9. Số liệu

Mỗi tuần: lượt đọc, bài đọc nhiều, câu tìm không ra, câu mong có, trích dẫn sao chép, từ được chạm nhiều, chia sẻ; số theo thể loại và theo cấp L0–L3. Kèm câu gợi ý việc nên làm ("Câu tìm không ra → thêm bài; từ chạm nhiều → viết nghĩa kỹ hơn"). Không định danh người đọc.

### 4.10. Cài đặt

Bút danh người biên soạn (khối Biên soạn chỉ hiện khi có); số ứng viên mỗi ngày; chính sách hiện (đường tắt tới Nguồn); tên khối tư liệu ("Thông tin tư liệu" / "Lời này từ đâu?"); Turnstile; chế độ bảo trì (dùng lúc chuyển đổi).

## 5. Vai, quyền, nhật ký

| Vai | Quyền | Ghi chú |
|---|---|---|
| Quản trị | tất cả | người sáng lập |
| Biên tập | soạn, duyệt | mở khi có người thứ hai; bước "người duyệt khác người nhập" cho bài nổi bật và nội dung trẻ em bật được trong Cài đặt |
| Cộng tác | gửi đối chiếu, soát lời, không đăng | người soát lời tình nguyện (đợt 5) |

Nhật ký ghi mọi thay đổi lớp, trạng thái tác phẩm, hồ sơ nguồn, gộp, tách; xem được ở từng tác phẩm và từng nguồn.

## 6. Lằn ranh trong mã (có kiểm thử khẳng định)

1. Lớp lời, dị bản không nhận người làm là AI; nháp AI không bao giờ vào lớp lời.
2. Trạng thái "đã duyệt", dấu "có hỗ trợ AI", trạng thái đăng chỉ đổi bằng thao tác của người có quyền; không lệnh, lịch chạy hay bước nhập nào làm thay (trừ bước nhập dữ liệu cũ mang đúng trạng thái đã có trên MVP).
3. Không trường nguồn nào (sách, trang, đường dẫn, ngày truy cập, người kể, nơi, vùng, SGK) có giá trị mặc định hay do máy điền; máy chỉ gợi ý, người bấm.
4. Trạng thái xin phép, giấy phép của hồ sơ nguồn chỉ quản trị đổi.
5. Kho thô và lời từ kho không ra trang người đọc (kể cả kết quả tìm) khi chưa thành tác phẩm đã đăng.
6. Không thu email, số điện thoại, IP của người đọc; người gửi không bắt buộc tên.

## 7. Nghiệm thu chung (đợt 3)

- Người sáng lập duyệt một nháp Hiểu nhanh ≤ 60 giây trên điện thoại của mình (đo khi dùng thử).
- Mọi việc chờ nằm ở Hộp việc; không còn trang quản trị con nào của MVP.
- Đăng một tác phẩm nhập tay từ điện thoại trong vài phút (điều kiện Mốc A giữ nguyên).
- Kiểm thử khẳng định đủ 6 lằn ranh ở mục 6.
- `quet:v2` đạt trên Hôm nay, Duyệt nhanh, Soạn; `e2e:v2` chạy luồng ứng viên → đăng, nháp → duyệt, có thể trùng → gộp, người đọc gửi → áp dụng.
