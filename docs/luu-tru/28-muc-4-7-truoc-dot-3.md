# 28 · Mục 4 và 7 cũ (trước đợt 3)

> Lưu trữ ngày 11/11/2026 (D-107). Nguyên văn mục 4 (giao diện D-094: bốn trang `/bien-tap/`, Hộp việc, Số liệu tuần; đích D-096) và mục 7 (nghiệm thu D-094, D-096) của `docs/28-ban-bien-tap-v2.md` trước đợt 3. Tài liệu đang dùng: `docs/28`, `docs/32`.

## 4. Biên tập — hiện trạng D-094 và đích D-096

**Giao diện đang chạy:** người biên soạn đăng nhập sửa ngay trên trang đọc; phần riêng có năm nơi dưới đây (D-094). Màn mẫu nhóm B của `design/v2` (B00–B05) mô tả giao diện này. **Đích nâng cấp:** wp-admin là nơi quản lý kho và biên tập chính, Hộp việc là hàng chờ trong đó; sửa trên trang đọc là lối nhanh (D-096). Đặc tả đích, thao tác lô và thứ tự chuyển ở `docs/30`; kỹ thuật ở `docs/26` mục 7. Các mục 4.1–4.7 tiếp tục mô tả luồng đang chạy cho tới khi từng chặng admin được nghiệm thu.

| Nơi | Đường dẫn | Việc |
|---|---|---|
| Hộp việc | `/bien-tap/` | mọi việc chờ một quyết định (mục 4.1) |
| Tác phẩm | `/bien-tap/tac-pham/` | tìm, lọc theo thiếu gì; bấm là mở trang bài ở chế độ sửa (mục 4.3) |
| Thêm bài | `/bien-tap/them/` | dán lời, máy nhận thể thơ, báo trùng (mục 4.4) |
| Số liệu tuần | `/bien-tap/so-lieu/` | một trang số liệu kèm việc nên làm (mục 4.6) |
| Nguồn & cài đặt | wp-admin → Phongdao | ít dùng: hồ sơ nguồn, nguồn tự động, kho, máy đang làm, cài đặt (mục 4.7) |

Hiện tại khi đăng nhập: đầu trang có nút **Hộp việc (số việc)** thay "Đã lưu"; trên điện thoại, ở các trang biên tập và trang bài đang sửa, thanh dưới là **Đọc · Hộp việc · Tác phẩm · Thêm**. Bốn trang `/bien-tap/…` phải đăng nhập, `noindex`. Sau khi chuyển lối vào theo D-096, các liên kết này phải mở đúng trang admin tương ứng, không mất vị trí bộ lọc hoặc việc đang xử lý.

> **Tên gọi (D-093):** phần `y_nghia` (trước gọi `hieu_nhanh`) hiển thị là **"Ý nghĩa"** ở mọi nơi ("Nháp ý nghĩa", "Bài thiếu ý nghĩa").

### 4.1. Hộp việc hiện tại

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

### 4.3. Tác phẩm hiện tại

Danh sách mọi tác phẩm: ô tìm (gõ không dấu được), lọc Tất cả · Thiếu ý nghĩa · Nháp AI · Chưa chủ đề · Có cờ · Bản nháp, xếp theo đọc nhiều tuần này; mỗi dòng: thể loại, tên, hai dòng đầu, trạng thái, lượt đọc. Bấm một dòng mở trang bài ở chế độ sửa. Không còn trình soạn Gutenberg cho tác phẩm (D-086).

Đích D-096: danh sách Tác phẩm trong wp-admin phải bao phủ mọi trạng thái và cả bài không có việc chờ; có ID, nguồn, phần thiếu, phân trang, chọn nhiều bài và trang sửa có cấu trúc. Chi tiết thao tác lô ở `docs/30` mục 3.

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

## 7. Nghiệm thu hiện có và nâng cấp D-096

- Người sáng lập duyệt một nháp ý nghĩa ≤ 60 giây trên điện thoại của mình (đo khi dùng thử).
- Mọi việc chờ nằm ở Hộp việc; không còn trang quản trị con nào của MVP. Đích D-096 bổ sung danh sách và biểu mẫu trong wp-admin theo `docs/30`.
- Sửa một phần (ý nghĩa, từ khó, dị bản) ngay trên trang bài từ điện thoại; lưu xong thấy khối mới đúng như người đọc thấy; hoàn tác được.
- Đăng một tác phẩm nhập tay từ điện thoại trong vài phút (điều kiện Mốc A giữ nguyên).
- HTML gửi khách không có nút Sửa, dải "Đang sửa", dòng nguồn gốc nội bộ hay địa chỉ gốc máy đã lấy.
- Kiểm thử khẳng định đủ 6 lằn ranh ở mục 6.
- `quet:v2` đạt trên Hộp việc (danh sách và từng thẻ), trang bài ở chế độ sửa (có tấm sửa mở), Tác phẩm, Thêm bài, Số liệu tuần; `e2e:v2` chạy luồng câu mới → đăng, nháp → duyệt, có thể trùng → gộp, người đọc gửi → áp dụng, sửa một phần trên trang → hoàn tác.

**Nghiệm thu đích D-096:** thêm ca kiểm cho danh sách admin toàn kho, Hộp việc mọi trạng thái, biểu mẫu dùng chung dịch vụ, sửa/duyệt/ẩn theo lô, lịch sử và khôi phục có xung đột. Chỉ chuyển các lối vào chính sau khi người sáng lập dùng thử ba kịch bản trong `docs/30` mục 5 trên máy tính và điện thoại.
