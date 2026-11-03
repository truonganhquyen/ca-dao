# 28 · Luồng nội dung và biên tập V2

| | |
|---|---|
| Phiên bản | 1.1 · 02/11/2026 (D-084, D-085, D-086; sửa trên trang đọc + Hộp việc: D-094) |
| Trạng thái | **Đã duyệt.** Đổi hành vi đã chốt phải ghi D-xxx |
| Đọc khi | Mọi task chạm nguồn, kho, chống trùng, biên tập (đợt 1 và 3) |
| Đọc kèm | `26` (dữ liệu, REST, quyền), `12` (chính sách nguồn), `design/v2` nhóm B (màn mẫu), AGENTS.md (lằn ranh) |
| Người dùng chính | Người sáng lập — người biên soạn duy nhất, dưới 10 giờ/tuần, chủ yếu trên điện thoại, không đọc mã |

## 1. Nguyên tắc

1. **Máy làm nặng, người quyết nhanh.** Máy thu thập, chống trùng, gợi ý, tách phần, xếp việc; agent soạn nháp; người biên soạn quyết. Mỗi thẻ việc là **một quyết định**, đủ thông tin để quyết ngay trên thẻ.
2. **Một nơi.** Mọi việc chờ người nằm ở Hộp việc; không phải nhớ trang nào có việc.
3. **Không chặn vì lý do biên tập (D-054).** Chế độ sửa nhắc (thiếu nghĩa, nghi trùng, thiếu nguồn), không chặn Đăng — trừ lỗi kỹ thuật và lằn ranh.
4. **Lằn ranh trong mã, không trong lời dặn** (mục 6).
5. **Điện thoại trước.** Mọi màn hình dùng được một tay ở 390 px; máy tính có phím tắt.
6. **Sửa ở chỗ đọc.** Người biên soạn sửa trên chính trang người đọc thấy; không có màn quản lý riêng cho từng loại nội dung (D-094).

## 2. Vòng đời nội dung

```text
Nguồn tự động ──► Kho thu thập ──► Chống trùng ──┬─► trùng hệt / trùng một dị bản đã ghi → tự gắn (xem được, "Tách ra")
(Ca dao Mẹ…)     (riêng tư)                       ├─► có thể trùng → thẻ "Có thể trùng"
                                                  └─► mới → thẻ "CÂU MỚI" (chưa phải tác phẩm)
Nhập tay ─────────────────────────────────────────► Thêm bài (nháp) → sửa trên trang bài
Người đọc gửi ────────────────────────────────────► thẻ "Người đọc gửi"
Agent soạn nháp theo lô ──────────────────────────► việc chờ (nháp nằm ở bảng việc) → thẻ "Nháp ý nghĩa"

CÂU MỚI ── Đăng ──► TÁC PHẨM đã đăng (lời, chú thích máy chép vào bài)
                     └─ máy gợi ý chủ đề / vùng / mô-típ / Wikidata / Bạn có biết → thẻ "Gợi ý"
TÁC PHẨM ── hậu kiểm: báo lỗi, góp ý, "mong có câu này", đọc nhiều chưa L2 → thẻ việc
```

Trạng thái tác phẩm: `nhap` (người đang soạn) · `publish` (đã đăng) · `an` (ẩn khỏi người đọc, giữ dữ liệu). Không còn hàng "Chờ duyệt" hàng loạt do máy tạo (D-086). Mọi phần nằm trong bài là phần người đã quyết; nháp chưa duyệt chỉ nằm ở bảng việc (`docs/26` mục 4.3).

## 3. Nguồn vào

### 3.1. Nguồn tự động

- Cấu hình ở trang **Nguồn & cài đặt** trong wp-admin (quyền `cadao_nguon`): bật / tắt, lịch lấy, giới hạn mỗi lượt, thể loại lấy, **"Chép chú thích"** (tắt được ngay khi nguồn yêu cầu, D-055), hồ sơ nguồn gắn kèm.
- Ca dao Mẹ: bộ đọc tất định chuyển từ MVP (khớp từng ký tự, lịch sự với máy chủ nguồn). Đợt 5 thêm Wikisource (hai sách 1897, 1901 — D-080).
- Kho thu thập là dữ liệu riêng tư: không lên trang, không vào repo công khai (AGENTS.md, KHÔNG BAO GIỜ 14).

### 3.2. Hồ sơ nguồn

Mỗi nguồn (Ca dao Mẹ, Wikipedia, Wiktionary, Commons, Wikidata, sách, người kể, người đọc, người biên soạn) có: loại, tên, tác giả, năm, giấy phép, mẫu ghi công, trạng thái xin phép, chính sách hiện (tại chỗ / Thông tin tư liệu / ẩn), "Chép chú thích". Thiếu thông tin thì để trống, hiện "chưa rõ". **Trạng thái xin phép và giấy phép chỉ người sáng lập đổi** (H3); đổi chính sách hiện → trang nhắc "Đổi ở đây là đổi chính sách — cần ghi quyết định".

### 3.3. Câu mới (ứng viên)

- Máy chọn câu từ kho để đưa lên Hộp việc mỗi ngày (mặc định 10, chỉnh được): ưu tiên câu người đọc **mong có** hoặc **tìm không ra** mà kho có, rồi xoay vòng theo thể loại (tục ngữ – thành ngữ, đồng dao, câu đố, vè, ca dao), câu có chú thích trước.
- Thẻ "Câu mới": lời, dị bản, chú thích (kèm nguồn), thể loại, gợi ý chủ đề / vùng, kết quả chống trùng.
- Hành động: **Đăng** (tạo tác phẩm đã đăng; lời, dị bản, chú thích máy chép vào bài với người làm `may_chep`) · **Sửa rồi đăng** (tạo bài nháp, mở trang bài ở chế độ sửa) · **Để sau** · **Bỏ** (ghi lý do; kho giữ, không hiện lại trừ khi người bấm "Xem lại").

### 3.4. Chống trùng

Giữ thuật toán MVP (dấu vân tay cả lời, cặp âm tiết, bốn mức: y hệt / gần như y hệt / có thể là dị bản / khác; D-065, D-074):
- y hệt, cùng thể loại → tự gắn vào tác phẩm có sẵn;
- trùng đúng một dị bản đã ghi → tự gắn làm dị bản;
- mọi lần tự gắn xem được ở Nguồn & cài đặt → Kho → Đã gộp, bấm "Tách ra" được;
- có thể trùng → thẻ "Có thể trùng": Cùng một bài · Gộp làm dị bản · Là bài khác · Để sau;
- hai tác phẩm đã đăng trùng nhau → thẻ "Gộp tác phẩm": chọn tác phẩm giữ lại; tác phẩm kia thành dị bản, đường dẫn cũ 301, lượt đọc, cảm nghĩ, phân tích, tuyển tập dồn sang; tác phẩm kia vào thùng rác (khôi phục được);
- Thêm bài và tấm sửa Lời: dải "Có thể trùng với …" ngay dưới ô Lời, chỉ báo, không chặn.

### 3.5. Nháp của agent

- Agent lập danh sách đề xuất (câu tìm nhiều, mong có, đọc nhiều chưa L2), soạn nháp **theo lô** cho ý nghĩa, nghĩa đen / bóng / dùng khi, gợi ý câu đố, Bạn có biết — ngoài site, rồi nạp bằng `wp phongdao nhap-nhap <tệp.json>` **khi người sáng lập đồng ý cho lần đó** (AGENTS.md).
- Mỗi nháp thành **một việc chờ** trong bảng việc (người tạo `may`, dấu AI bật); chỉ cho tác phẩm đã đăng; không đụng phần đã có trong bài; không bao giờ cho lời, dị bản. Nháp chỉ vào bài khi người có quyền bấm Duyệt (thành `ai_da_duyet`).
- Agent không tự khẳng định câu có trong SGK, không viết lời dẫn tuyển tập, không viết sự kiện bối cảnh, cách chơi, lý do cờ nghi.

## 4. Biên tập — năm nơi và sửa tại chỗ (D-094)

Không có ứng dụng quản trị riêng. Người biên soạn đăng nhập thì **trang người đọc thành chỗ sửa**; phần riêng chỉ còn năm nơi, cùng giao diện tím – lục của trang đọc. Màn mẫu: nhóm B của `design/v2` (B00 tổng quan, B01 Hộp việc, B02 trang bài ở chế độ sửa, B03 Tác phẩm, B04 Thêm bài, B05 Số liệu tuần). Kỹ thuật: `docs/26` mục 7.

| Nơi | Đường dẫn | Việc |
|---|---|---|
| Hộp việc | `/bien-tap/` | mọi việc chờ một quyết định (mục 4.1) |
| Tác phẩm | `/bien-tap/tac-pham/` | tìm, lọc theo thiếu gì; bấm là mở trang bài ở chế độ sửa (mục 4.3) |
| Thêm bài | `/bien-tap/them/` | dán lời, máy nhận thể thơ, báo trùng (mục 4.4) |
| Số liệu tuần | `/bien-tap/so-lieu/` | một trang số liệu kèm việc nên làm (mục 4.6) |
| Nguồn & cài đặt | wp-admin → Phongdao | ít dùng: hồ sơ nguồn, nguồn tự động, kho, máy đang làm, cài đặt (mục 4.7) |

Khi đăng nhập: đầu trang có nút **Hộp việc (số việc)** thay "Đã lưu"; trên điện thoại, ở các trang biên tập và trang bài đang sửa, thanh dưới là **Đọc · Hộp việc · Tác phẩm · Thêm**. Bốn trang `/bien-tap/…` phải đăng nhập, `noindex`.

> **Tên gọi (D-093):** phần `y_nghia` (trước gọi `hieu_nhanh`) hiển thị là **"Ý nghĩa"** ở mọi nơi ("Nháp ý nghĩa", "Bài thiếu ý nghĩa").

### 4.1. Hộp việc

```text
┌──────────────────────────────┐
│ Hộp việc 27   [Duyệt từng thẻ]│
│ Tuần này: 10 bài nên có ý    │  ← đọc / tìm nhiều chưa L2
│ nghĩa · 4 câu mong có · 2 báo│
│ lỗi · Số liệu tuần           │
│ [Tất cả 27][Báo lỗi 2][Mong  │  ← lọc theo loại
│ có 4][Nháp ý nghĩa 8]…       │
│ ──────────────────────────── │
│ ! Báo lỗi · Dòng mẫu một…    │  ← mỗi thẻ đủ thông tin để quyết
│   "Chú thích gắn nhầm dòng"  │
│   [Sửa ngay] [Không phải lỗi]│
│ ★ Mong có · "câu đang tìm"   │
│   [Tạo bài từ kho] [Để sau]  │
│ ✎ Nháp ý nghĩa · …           │
│   [Duyệt] [Sửa] [Bỏ nháp]    │
└──────────────────────────────┘
```

**Thứ tự ưu tiên của thẻ:** báo lỗi của người đọc → mong có câu này (kho có) → nháp ý nghĩa của bài đọc / tìm nhiều → câu mới từ nguồn → có thể trùng → người đọc gửi khác → gợi ý → Wikidata → cần xem (sau chuyển dữ liệu). Lọc theo loại. Hết việc: "Hết việc rồi" + đường tắt tới bài thiếu ý nghĩa.

**Duyệt từng thẻ** (thay "Duyệt nhanh" của kế hoạch cũ): một thẻ một màn, thanh tiến độ "Thẻ 3 / 27", nút ≥ 44 px dùng một tay; máy tính phím 1 / 2 / 3 cho ba nút, J / K thẻ sau / trước. Mỗi thẻ có "Mở bài" để sửa kỹ trên trang bài.

| Loại thẻ | Thông tin trên thẻ | Hành động |
|---|---|---|
| Câu mới từ nguồn | lời, dị bản, chú thích + nguồn, thể loại, kết quả chống trùng | Đăng · Sửa rồi đăng (mở trang bài nháp ở chế độ sửa) · Để sau · Bỏ |
| Nháp ý nghĩa, nháp nghĩa (nghĩa đen / bóng / dùng khi, gợi ý câu đố, Bạn có biết) | lời, chú thích, nháp, dấu AI | Duyệt (chép vào bài, giữ dấu AI) · Sửa · Để sau · Bỏ nháp |
| Có thể trùng | hai lời đặt cạnh nhau, chỗ khác tô | Cùng một bài · Gộp làm dị bản · Là bài khác · Để sau |
| Gộp tác phẩm (hai bài đã đăng trùng) | hai bài, số lượt đọc, cảm nghĩ, tuyển tập của mỗi bài | Chọn bài giữ lại → xác nhận · Là bài khác |
| Gợi ý chủ đề / vùng / mô-típ / đối chiếu sách | bài, gợi ý, lý do — chủ đề, vùng: mỗi gợi ý (một mục cho một bài) một thẻ, đã làm (`DichVu\GoiY`); Bỏ thì máy không gợi ý lại | Đồng ý · Sửa · Bỏ (chủ đề, vùng: Đồng ý · Để sau · Bỏ) |
| Wikidata | từ, thực thể, mô tả, ảnh | Đúng · Sai · Bỏ qua |
| Người đọc gửi (dị bản, góp ý, số trang, báo lỗi) | nội dung, bài liên quan | Áp dụng (mở đúng tấm sửa trên trang bài) · Đã xử lý · Rác |
| Cảm nghĩ, bài phân tích | nội dung | Đăng · Bỏ |
| Mong có câu này | câu, số lần, câu khớp trong kho | Tạo bài từ kho · Để sau |
| Báo lỗi | lỗi, bài | Sửa ngay (mở tấm sửa) · Không phải lỗi |
| Cần xem (sau chuyển dữ liệu) | bài, lý do không tách được | Mở bài · Đã xem |

Mỗi lần bấm ghi nhật ký (ai, lúc nào, trước, sau). Mục tiêu: **≤ 60 giây mỗi thẻ** trên điện thoại. **Hoàn tác** thẻ vừa quyết trong 10 giây. Không có JavaScript thì nút chính của mỗi thẻ vẫn gửi được bằng form.

### 4.2. Trang bài ở chế độ sửa

Trang bài người đọc thấy, thêm:
- **Dải "Đang sửa"** trên đầu: trạng thái (Nháp / Đã đăng / Ẩn), lần sửa cuối; [Xem như người đọc] (ẩn mọi nút sửa, nhớ trên máy); [Đăng] khi bài là nháp, [Ẩn bài] khi đã đăng.
- **Nút Sửa trên từng khối**, mở **tấm sửa** cho đúng phần đó: trên điện thoại trượt từ dưới lên, trên máy tính là cột phải; [Huỷ] [Lưu]; lưu xong khối vẽ lại từ máy chủ, thấy đúng như người đọc sẽ thấy.
- **Dòng nguồn gốc nhỏ** dưới mỗi phần (chỉ người biên soạn thấy): "Lời: máy chép từ Ca dao Mẹ, 12/10/2026 · chưa sửa tay", "người viết", "máy chép · nguồn đã ẩn".
- **Lịch sử** (khối cuối bài, chỉ người biên soạn thấy): mỗi lần sửa, duyệt, đăng; khôi phục được một bản cũ.

| Tấm sửa | Nội dung | Ghi chú |
|---|---|---|
| Lời | ô nhiều dòng, một câu một dòng; máy nhận thể thơ ngay ("lục bát · 4 dòng · không có dòng lệch"); dải "Có thể trùng với …"; nhóm "Lời đã đối chiếu với": sách (chọn hồ sơ nguồn), trang, đường dẫn bản quét | Sửa lời đã đăng: nhắc "sửa sẽ ghi lịch sử, phần lời thành người viết; không sửa theo trí nhớ"; đối chiếu chỉ người điền, máy không tự điền |
| Ý nghĩa | nháp máy (nếu có) ở ô riêng "Nháp máy · chưa duyệt" + [Dùng nháp này]; ô 2–3 câu; ☐ Có hỗ trợ của AI; ☐ Tôi đã đọc và duyệt phần này | Lưu khi chưa đánh "đã duyệt" → thành việc "Nháp ý nghĩa", người đọc chưa thấy |
| Nghĩa (tục ngữ – thành ngữ) | nghĩa đen, nghĩa bóng, dùng khi, câu gần / trái nghĩa (chọn tác phẩm); ô AI như trên | |
| Từ khó | danh sách: từ trong lời (máy báo neo được vào dòng nào), lời giải, nguồn; gợi ý từ Từ điển; nối mục từ | Mục máy chép hiện "máy chép · <chính sách hiện của nguồn>" |
| Dị bản | danh sách: lời, nơi, người kể (cần đồng ý), năm, nguồn; xếp lên / xuống; thêm, xoá | |
| Theo thể loại | vè: đoạn, bối cảnh; đồng dao: trò chơi; câu đố: đáp án, gợi ý, ảnh đáp án; truyện (đợt 5): bản kể, đoạn, nhân vật | |
| Chủ đề, vùng, tuyển tập | chip chủ đề (gợi ý máy hiện kèm lý do, bấm để đồng ý); nơi lưu truyền chỉ thêm kèm nguồn; mô-típ; hình thức (lối hát); tuyển tập + ghi chú "Hợp khi…" | |
| Cờ, SGK | cờ "Nghi không phải dân gian" + lý do + nguồn; SGK: bộ sách, lớp, bài, trang (chỉ khi đã thấy sách) | Máy chỉ gợi ý cờ |

"Lời này từ đâu?" hiện đúng như người đọc thấy, theo chính sách hiện của hồ sơ nguồn. Nhắc (không chặn Đăng): thiếu ý nghĩa, thiếu chủ đề, nghi trùng, chú thích không neo được vào lời.

### 4.3. Tác phẩm

Danh sách mọi tác phẩm: ô tìm (gõ không dấu được), lọc Tất cả · Thiếu ý nghĩa · Nháp AI · Chưa chủ đề · Có cờ · Bản nháp, xếp theo đọc nhiều tuần này; mỗi dòng: thể loại, tên, hai dòng đầu, trạng thái, lượt đọc. Bấm một dòng mở trang bài ở chế độ sửa. Không còn trình soạn Gutenberg cho tác phẩm (D-086).

### 4.4. Thêm bài

Chọn thể loại → dán lời (mỗi dòng một câu; "không sửa theo trí nhớ") → máy nhận thể thơ và kiểm trùng ngay (giống hệt / gần giống: liên kết tới bài có sẵn, [Gộp làm dị bản] [Vẫn thêm bài mới] — không chặn) → [Lưu nháp] hoặc [Đăng]. Lưu xong: "Mở trang bài để sửa tiếp" (thêm ý nghĩa, chú thích, chủ đề). Đăng một bài nhập tay từ điện thoại trong vài phút (điều kiện Mốc A giữ nguyên).

### 4.5. Sửa tại chỗ ở các trang khác

Cùng cách nút Sửa + tấm sửa; trang danh sách của mỗi loại có nút **+ Thêm** khi người biên soạn đăng nhập.

- **Tuyển tập** (trang tuyển tập): tên, loại (chủ đề, dịp, tình huống, bài học), đối tượng, lời dẫn (người viết), nhóm, danh sách tác phẩm (tìm để thêm, kéo / lên xuống để xếp, ghi chú "Hợp khi…" từng mục), trạng thái. Máy đề xuất câu từ chủ đề đã duyệt; người chọn. Hiện số câu đã có ý nghĩa.
- **Mục từ** (trang mục từ): từ, loại (từ, địa danh, nhân vật), lời giải, nguồn lời giải, Wikidata (xác nhận ở thẻ Hộp việc), ảnh (Commons theo Wikidata hoặc ảnh riêng, ghi công; ẩn ảnh được); các bài có từ này tự hiện.
- **Trò chơi** (trang trò chơi): thông số, các bước, nguồn, giấy phép; nút "Lấy mục Cách chơi từ Wikipedia" (chỉ vi.wikipedia.org, ghi nguồn đúng bài, CC BY-SA); máy không tự viết cách chơi.
- **Mô-típ** (trang mô-típ): nhóm bài cùng cụm mở đầu do máy gom; người đặt tên, bỏ bài không hợp, viết mô tả.
- **Chủ đề, vùng, thể loại** (trang của chúng): mô tả, giới thiệu, bài tiêu biểu. Bảng ánh xạ chủ đề nguồn → dự án ở Nguồn & cài đặt.

### 4.6. Số liệu tuần

Một trang: lượt đọc, câu tìm không ra, câu mong có, lần sao chép, lần chia sẻ; **Nên làm tuần này** (vd. "Viết ý nghĩa cho 10 bài đọc nhiều" → mở danh sách đã lọc; "4 câu mong có đã có trong kho" → Hộp việc; "từ được chạm nhiều → viết lời giải kỹ hơn"); bài đọc nhiều; số theo thể loại và theo cấp L0–L3. Không định danh người đọc.

### 4.7. Nguồn & cài đặt (wp-admin)

Trang PHP trong wp-admin, menu "Phongdao" (cùng đường tắt tới Hộp việc):
- **Hồ sơ nguồn** (mục 3.2) · **Nguồn tự động** (mục 3.1) · **Kho** (tìm, trạng thái, Đã gộp, "Tách ra") · **Máy đang làm** (việc nền, lần chạy, lỗi gần nhất).
- **Cài đặt:** bút danh người biên soạn (khối Biên soạn chỉ hiện khi có); số câu mới mỗi ngày; bộ chủ đề và bảng ánh xạ chủ đề nguồn → dự án; Turnstile (không gửi khoá cho ai, kể cả agent); chế độ bảo trì (dùng lúc chuyển đổi).
- Người dùng và vai: màn Người dùng có sẵn của WordPress.

## 5. Vai, quyền, nhật ký

| Vai | Quyền | Ghi chú |
|---|---|---|
| Quản trị | tất cả | người sáng lập |
| Biên tập | sửa, duyệt | mở khi có người thứ hai; bước "người duyệt khác người nhập" cho bài nổi bật và nội dung trẻ em bật được trong Cài đặt |
| Cộng tác | gửi đối chiếu, soát lời, không đăng | người soát lời tình nguyện (đợt 5) |

Nhật ký (bảng việc) ghi mọi lần sửa một phần, quyết một thẻ, đổi trạng thái tác phẩm, hồ sơ nguồn, gộp, tách; xem được ở khối Lịch sử của từng tác phẩm và ở từng nguồn.

## 6. Lằn ranh trong mã (có kiểm thử khẳng định)

1. Phần lời, dị bản không nhận người làm là AI; nháp AI không bao giờ vào lời; nháp chưa duyệt không vào bài.
2. Trạng thái "đã duyệt", dấu "có hỗ trợ AI", trạng thái đăng chỉ đổi bằng thao tác của người có quyền; không lệnh, lịch chạy hay bước nhập nào làm thay (trừ bước nhập dữ liệu cũ mang đúng trạng thái đã có trên MVP).
3. Không trường nguồn nào (sách, trang, đường dẫn, ngày truy cập, người kể, nơi, vùng, SGK) có giá trị mặc định hay do máy điền; máy chỉ gợi ý, người bấm.
4. Trạng thái xin phép, giấy phép của hồ sơ nguồn chỉ quản trị đổi.
5. Kho thô và lời từ kho không ra trang người đọc (kể cả kết quả tìm) khi chưa thành tác phẩm đã đăng; nút sửa, dòng nguồn gốc nội bộ, địa chỉ gốc máy đã lấy không có trong HTML gửi khách.
6. Không thu email, số điện thoại, IP của người đọc; người gửi không bắt buộc tên.

## 7. Nghiệm thu chung (đợt 3)

- Người sáng lập duyệt một nháp ý nghĩa ≤ 60 giây trên điện thoại của mình (đo khi dùng thử).
- Mọi việc chờ nằm ở Hộp việc; không còn trang quản trị con nào của MVP; ngoài Nguồn & cài đặt, không màn quản trị riêng nào.
- Sửa một phần (ý nghĩa, từ khó, dị bản) ngay trên trang bài từ điện thoại; lưu xong thấy khối mới đúng như người đọc thấy; hoàn tác được.
- Đăng một tác phẩm nhập tay từ điện thoại trong vài phút (điều kiện Mốc A giữ nguyên).
- HTML gửi khách không có nút Sửa, dải "Đang sửa", dòng nguồn gốc nội bộ hay địa chỉ gốc máy đã lấy.
- Kiểm thử khẳng định đủ 6 lằn ranh ở mục 6.
- `quet:v2` đạt trên Hộp việc (danh sách và từng thẻ), trang bài ở chế độ sửa (có tấm sửa mở), Tác phẩm, Thêm bài, Số liệu tuần; `e2e:v2` chạy luồng câu mới → đăng, nháp → duyệt, có thể trùng → gộp, người đọc gửi → áp dụng, sửa một phần trên trang → hoàn tác.
