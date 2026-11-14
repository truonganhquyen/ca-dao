# 27 · Sản phẩm cho người đọc V2

| | |
|---|---|
| Phiên bản | 1.3 · 11/11/2026 (đợt 3: D-105 trang bài ba tầng, tục ngữ, câu đố, ca dao hình ảnh, cách hiểu của bạn đọc, từ khó; D-106 lập chỉ mục) · 1.2 · 02/11/2026 (D-084, D-085, D-087, D-088; bảng tím – lục, bộ thiết kế D-092; mỗi câu một dòng, Ý nghĩa, nhãn mức kiểm, ảnh chia sẻ D-093) |
| Trạng thái | **Đã duyệt.** Đổi hướng thẩm mỹ hay hành vi đã chốt là Human Gate H4, ghi D-xxx |
| Đọc khi | Mọi task chạm trang người đọc (đợt 2, nhóm V2 trong `docs/22`) |
| Đọc kèm | `26` (kỹ thuật: khối, mẫu, REST), `28` (nội dung đến từ đâu), `32` (kế hoạch đợt 3, mục 4); câu chữ giao diện và trợ năng ở phụ lục A, B cuối tài liệu này |
| Căn cứ | `docs/nghien-cuu/v2-ux-ui-2026-10-27.md`, khảo sát 27/10, báo cáo E2E 26/10, bản đề xuất *Phongdao Next Generation* mục 7–9 (S01–S18) |
| Bộ thiết kế | **`design/v2/`** (D-092): 66 màn, 203 trạng thái, token, đặc tả từng màn. Dựng: `cd design/v2 && node source/build.mjs`, mở `exports/PHONGDAO_V2_Prototype.html`. Về hình ảnh, bộ thiết kế là chuẩn; về hành vi và nội dung, tài liệu này thắng khi hai bên lệch |

Tinh thần: **"Một trang sách cổ được chăm chút, đặt trong một chiếc điện thoại hiện đại."** Hướng mỹ thuật **tím – lục** (D-092): giấy sáng, chữ Literata, tím cho thương hiệu và hành động, lục cho nghĩa và mức kiểm, thẻ trắng bo góc, khoảng trắng, ít trang trí. Minh hoạ chọn lọc chỉ ở đồng dao, truyện, tuyển tập khi có ảnh có quyền; phong cách "atlas" chỉ ở bản đồ và chuyên đề vùng.

## 1. Nguyên tắc

1. **Đơn vị nhỏ, tập hợp lớn.** Mọi trang tập hợp ghép từ trang tác phẩm; mỗi câu liên kết về trang riêng, trích dẫn được; nghĩa viết một lần.
2. **Trả lời trước, mở sâu sau.** Màn hình đầu (390 px) trả lời câu người đọc đang hỏi; phần sâu trong `<details>`.
3. **Mỗi câu một nhãn đúng.** Thể loại ở mọi thẻ; câu có cờ "Nghi không phải dân gian" mang cờ ở mọi nơi.
4. **Nguồn đặt ngay chỗ khẳng định.** Dòng mức kiểm ngay dưới lời; nguồn bản kể ngay dưới tên truyện; "chưa kiểm chứng" ngay cạnh điều được kể. Phần nào ẩn là do chính sách hiện (D-085), không do bố cục.
5. **Lối vào theo vai, không theo dõi.** Lựa chọn của người đọc nằm trên máy họ.
6. **Một hành động chính mỗi vùng.** Trang bài: Trích dẫn. Tuyển tập: Trình chiếu. Câu đố: Hiện đáp án. Trò chơi: Đọc cùng bé.
7. **Không độn chữ.** Khối không có dữ liệu thì không hiện, kể cả tiêu đề; không ảnh khung trống; không trang đích rỗng.
8. **Nhẹ và sạch là tính năng.** Mục 9.
9. **Mỗi câu một dòng (D-093).** Ở mọi màn hình (trang bài, Câu hôm nay, tuyển tập, ảnh chia sẻ, trình chiếu, Đọc cùng bé), mỗi câu của lời nằm trên một dòng: cỡ chữ lời tự co cho vừa khung, trần là cỡ người đọc chọn, sàn 15 px (14 px khi màn hẹp hơn 360 px). Dưới sàn: tục ngữ ngắt theo vế (sau dấu phẩy, trước gạch ngang), thể khác xuống dòng có thụt treo. Các khổ cùng một bài dùng chung một cỡ. Nhãn nút không xuống dòng.
10. **Tên gọi dễ hiểu.** "Hiểu nhanh" hiển thị là **Ý nghĩa** (D-093); trong mã là phần `y_nghia` (D-094; tài liệu cũ gọi lớp `hieu_nhanh`).

## 2. Tên, khẩu hiệu, câu chữ

- Tên: **Thư viện Văn học Dân gian Việt Nam**; tên ngắn ở đầu trang điện thoại: **Phongdao**; khẩu hiệu "Đọc một câu. Hiểu một đời sống."
- Tên xuất hiện ở: đầu, chân trang, `<title>` mặc định, trích dẫn (thông dụng, APA 7), JSON-LD `WebSite` / `Organization`, ảnh chia sẻ chung, trang Về dự án.
- Câu chữ và từ cấm: phụ lục A và AGENTS.md (không "bản gốc", "bản đúng", "bản chuẩn", "bản sai", "AI giải thích", "câu hay nhất"; bản đại diện gọi là "Bản tham chiếu"). Không dùng mã cấp L0–L3 hay "mức A/B" trên giao diện.
- Văn bản dự án tự viết đặt dấu theo SGK (hoà, thuỷ — D-038); lời trích giữ như nguồn.

## 3. Kiến trúc thông tin

```text
Đầu trang (máy tính)
  Phongdao · Khám phá ▾ · Cho lớp học · Tra cứu ▾ · [ô tìm] · ☆ Đã lưu
  — menu "Đọc ▾" bỏ, thanh thể loại thay (D-093)

Khám phá ▾   Tuyển tập · Chủ đề · Theo dịp · Vùng & Bản đồ · Mô-típ · Một câu ngẫu nhiên
Cho lớp học  chỉ hiện khi ≥ 3 bài học có bộ câu SGK (đợt 5)
Tra cứu ▾    Tìm · Từ điển · Hay bị nhầm là ca dao · Phương pháp & nguồn
(Nghe)       mục Nghe khi giọng đọc sinh sẵn qua phiên nghe thử (đợt 5); trước đó trang bài có nút Nghe giọng máy (D-095)

Thanh thể loại (D-092; dưới đầu trang, viên thuốc, trượt ngang trên điện thoại, ẩn khi in)
  Trang chủ · Tất cả · Ca dao · Tục ngữ – Thành ngữ · Đồng dao · Vè · Câu đố · (Truyện) · Tuyển tập
  — chỉ thể loại đã qua cổng (D-091); thể loại 0 bài tự ẩn

Thanh dưới (điện thoại, 56 px, không che focus, ẩn khi in)
  Trang chủ · Tìm · Khám phá · Đã lưu
  — Khám phá mở trang riêng `/kham-pha/` (D-093): ô tìm, ô thể loại, lối đọc, tra cứu, về thư viện;
    có đường dẫn, nút Quay lại của máy hoạt động, chạy khi tắt JavaScript

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
| Đố vui, Hay bị nhầm, Bản đồ, Tìm, Đã lưu, Một câu ngẫu nhiên, Khám phá | `/do-vui/`, `/hay-bi-nham/`, `/ban-do/`, `/tim/`, `/da-luu/`, `/ngau-nhien/`, `/kham-pha/` |
| Cho lớp học (đợt 5) | `/lop-hoc/`, `/lop-hoc/<lớp>/` |
| Bài phân tích | `/phan-tich/<tên>/` |

Đường dẫn MVP đổi khi chuyển (`/tinh-huong/…` → tuyển tập, `/the-tho/…` → `/hinh-thuc/…`, đuôi `-2`) đều chuyển hướng 301 (D-089).

## 4. Trang chủ (S01)

**Mục tiêu:** người mới chọn được lối vào trong 10 giây; đến một bài hữu ích trong ≤ 3 lần chạm.

Bố cục (D-092): máy tính hai cột — trái là khối 1 và 2, phải là 3 thẻ lối tìm (Tìm khi nhớ vài chữ → Tìm, Tìm theo vùng → Bản đồ, Tìm theo chủ đề → Chủ đề); điện thoại một cột. Câu hôm nay là thẻ giữa trang. Không có "Đọc theo tâm trạng".

Thứ tự (chỉ hiện khối có dữ liệu):
1. Tên + khẩu hiệu + ô tìm ("Gõ câu bạn nhớ, gõ không dấu cũng được").
2. **Bạn là:** (Học sinh) (Giáo viên) (Phụ huynh) (Yêu văn hoá) — chọn một lần, nhớ trên máy, bỏ qua được; không chọn = thứ tự mặc định.
3. **Câu hôm nay:** một tác phẩm L2 (đổi mỗi ngày, không lặp trong 30 ngày): lời + nhãn mức kiểm + Ý nghĩa + [Đọc bài ›] [☆ Lưu]; nút Sao chép ở góc thẻ.
4. **Lối vào** ("Bắt đầu từ đâu?", thẻ có biểu tượng): Hiểu nghĩa tục ngữ · Theo dịp · Cùng bé chơi · Bộ câu cho tiết học (khi Cho lớp học mở); kèm lưới **Đọc theo dịp** (ô Tết, Rằm tháng Giêng, 8/3, Vu Lan, Trung thu, 20/11…, dịp gần nhất đầu; mỗi ô là tuyển tập loại dịp, có số câu).
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

Lời dẫn ngắn → chip lọc (thể loại, hình thức, chủ đề, vùng; mỗi chip có số; số 0 ẩn) → sắp xếp **Có giải nghĩa trước** (mặc định) / Mới thêm / Đọc nhiều tuần này / A–Z (D-112) → danh sách thẻ bài (tên, 2 dòng đầu, nhãn thể loại, nghĩa ngắn nếu có, 👁 khi ≥ 10 lượt) → phân trang 30 bài, số trang ≥ 44 px. Điện thoại: bộ lọc thu gọn, ghi rõ đang lọc gì. Trang đã lọc `noindex`.

**D-112 (14/11/2026):** Kho, thể loại, chủ đề, vùng, mô-típ, hình thức có cùng bộ chọn thứ tự. Mặc định phần giúp hiểu trước, rồi chú thích/đáp án, rồi các bài còn lại; trong nhóm, ngày đăng mới trước, cùng giờ ID giảm dần. Đây là độ đầy đủ phục vụ đọc, không xác nhận sự thật. Xem quy tắc theo thể loại và nghiệm thu ở [docs/39](39-tim-va-sap-xep-danh-sach.md). Từ điển giữ A–Z; tuyển tập giữ thứ tự người biên soạn.

### 5.2. Thể loại (`/the-loai/<thể loại>/`)

Giới thiệu do người sáng lập viết (cách đọc thể loại) → bài tiêu biểu (người chọn; thiếu thì L2 đọc nhiều) → lối vào riêng (tục ngữ: hàng chữ cái A B C D Đ…; đồng dao: Theo trò chơi; câu đố: Đố vui, theo chủ đề đáp án; vè: theo sự kiện khi có bối cảnh) → chủ đề phổ biến → danh sách liên tục. Thiếu giới thiệu: chỉ tên thể loại, không câu độn.

### 5.3. Chủ đề, vùng, mô-típ, hình thức

Dạng "đọc liền": mô tả do người viết (nếu có) → danh sách thẻ có lời + nghĩa ngắn + chú thích địa danh (vùng) → tuyển tập liên quan. Lập chỉ mục khi ≥ 3 tác phẩm L2. Vùng nói rõ "nơi lưu truyền"; bản đồ dẫn sang "được nhắc đến".

## 6. Trang tác phẩm theo thể loại (S04–S09)

Bố cục chung (D-092, D-093): đầu trang căn giữa (nhãn thể loại; "Bản tham chiếu · N dị bản" là liên kết mở khối Dị bản; tên; số liệu) → **thẻ lời**: hàng đầu thẻ "Lời" + nút **Sao chép** (chép lời giữ thụt câu lục, kèm "— <thể loại> · Thư viện Văn học Dân gian Việt Nam" và đường dẫn bài; sự kiện `trich_dan_sao_chep`), lời (số dòng ngoài lề trên máy tính, ẩn trên điện thoại), **nhãn mức kiểm** (mục 6.0), **Ý nghĩa** nền lục nhạt → các khối thẻ có nền nhạt theo vai (mục 9.1). Công cụ đọc là **cột dính bên phải** trên máy tính: [❝ Trích dẫn] [☆ Lưu] [↗ Chia sẻ] [▣ Trình chiếu] + cỡ chữ Vừa / Lớn / Rất lớn; trên điện thoại thành hàng 4 nút dưới thẻ lời [Trích dẫn] [Lưu] [Chia sẻ] [Cỡ chữ] (hộp Cỡ chữ có sáng / tối và Trình chiếu; màn hẹp hơn 320 px chỉ còn biểu tượng). **Chia sẻ** mở hộp: Sao chép liên kết · Chia sẻ qua ứng dụng (khi máy hỗ trợ) · Tạo ảnh để chia sẻ. Trích dẫn là hành động chính. Dưới tiêu đề: 👁 lượt đọc · ↗ chia sẻ · ❝ trích dẫn khi đủ ngưỡng (D-069).

**6.0. Nhãn mức kiểm (D-093, thay câu chữ D-072).** Nói điều có, chi tiết nằm sau một chạm: "📖 Bản lưu truyền phổ biến ⓘ" hoặc "✓ Đã đối chiếu: <sách>, tr. N ⓘ" (nền lục, chỉ người biên soạn điền). ⓘ mở hộp nhỏ (`<details>`, chạy khi tắt JavaScript): "Lời ghi theo cách người Việt thường đọc, hát hiện nay. Thư viện đang đối chiếu dần với sách in; khi xong, nhãn này ghi tên sách và số trang." + "Có sách? Gửi số trang" · "Lời này từ đâu?" · "Cách biên soạn". Câu "chưa đối chiếu với sách in" vẫn ghi đầy đủ trong khối **Lời này từ đâu?**, trong trích dẫn và trên ảnh chia sẻ dùng nhãn ngắn "Bản lưu truyền phổ biến".

Phần cuối chung (theo thứ tự, D-093): Chủ đề · Vùng · Thuộc tuyển tập (nhãn liên kết) → **Lời này từ đâu?** (mục 6.7) → Biên soạn (bút danh khi có, liên kết Phương pháp) → **Đọc tiếp** (3–5 thẻ, nền theo thể loại: cùng mô-típ, chủ đề, từ, địa danh, vùng; mỗi thẻ có lý do) → **Ảnh để chia sẻ** (mục 10.1) → **Bạn muốn góp gì?** (mục 8.1, lưới 6 nút) → Cảm nghĩ đã duyệt, bài phân tích đã duyệt. Khối "Trích dẫn nhanh" bỏ (D-093): nút Sao chép trong thẻ lời và nút Trích dẫn (hộp thông dụng / APA / chỉ lời) thay.

**Người biên soạn đăng nhập** (D-093, D-094): trang bài thành chế độ sửa — dải "Đang sửa" (trạng thái, [Xem như người đọc]), mỗi khối có nút **Sửa** mở tấm sửa (Ý nghĩa: ô chữ, ô "Có hỗ trợ của AI", ô "đã duyệt", [Lưu] [Huỷ]); lưu qua REST `phongdao/v1` (quyền biên tập + nonce), ghi lịch sử. Chi tiết: `docs/28` mục 4.2. Người đọc không nhận các nút này trong HTML.

**Đợt 3 (D-105, `docs/32` mục 4.1):** trang bài sắp lại ba tầng — **Đọc** (lời → Ý nghĩa của ban biên tập hoặc khối nghĩa theo thể loại → Từ khó → Dị bản) · **Hiểu thêm** (một khối có thẻ kèm số: Cách hiểu của bạn đọc · Bài cảm nhận · Cảm nghĩ; mỗi thẻ có nút viết) · **Đọc thêm** (cùng chủ đề, mô-típ, tuyển tập, "Lời này từ đâu?", ảnh để chia sẻ). **Đã làm (plugin 1.0.50, theme 1.0.4):** tầng Đọc là khung lời + Ý nghĩa (tục ngữ: Giải nghĩa chi tiết, Dùng khi) + Từ khó + Dị bản; tầng **Hiểu thêm** (tiêu đề nhỏ "Hiểu thêm") có ba phần kèm số — tắt JavaScript thì ba phần nằm liền nhau, mỗi phần một tiêu đề; bật JavaScript thành ba thẻ (phím mũi tên, Home, End), mở sẵn phần nhiều bài nhất, liên kết tới một phần thì mở phần đó; Bài cảm nhận hiện tên, hai dòng đầu, người viết (bài dài vẫn ở trang riêng); mỗi phần có nút viết ("Viết cảm nhận", "Viết cảm nghĩ" mở trang Góp; Cách hiểu có form ngay tại chỗ); tầng **Đọc thêm** gom nhãn phân loại, tuyển tập, Đọc tiếp, "Lời này từ đâu?", ảnh để chia sẻ; cuối trang còn "Bạn muốn góp gì?".

Bài chưa có Ý nghĩa: lời mời ấm thay câu cũ (vd. "Câu này chưa có lời giải nghĩa. Bạn hiểu câu này thế nào? Viết vài dòng để người đọc sau hiểu rõ hơn.") và nút **Chia sẻ cách hiểu** mở form nhanh ngay trên trang (mục 8.1); máy không tự điền. Chưa có nơi lưu truyền: "Ở quê bạn có câu này không? Góp" thay "chưa ghi nhận" (D-105).

**Cải tiến theo `docs/33` (plugin 1.0.56, theme 1.0.6):** form Cách hiểu nằm trong mục **Chia sẻ cách hiểu** thu gọn bằng HTML `details`; nội dung bạn đọc đã được hiện nằm trước mục mở form. Gửi lỗi/thành công tự mở để thấy phản hồi; liên kết thẳng tới form vẫn mở được, giữ bàn phím, consent, chống rác và duyệt. Ba thẻ Hiểu thêm không đổi. Lối vào Tuyển tập/Theo dịp trên Khám phá nói rõ đang chuẩn bị khi chưa có bộ công khai; danh mục giữ đường đọc từ kho. Không đổi việc hiện nguồn văn bản trong đợt này.

### 6.1. Ca dao (S04)

Lời (lục bát thụt câu lục — D-092; từ có chú thích nền tô vàng; từ có chú thích là nút chạm, popover nghĩa + nguồn theo chính sách) → nhãn mức kiểm (mục 6.0) → **Ý nghĩa** (2–3 câu ngắn, nền lục nhạt; "Có hỗ trợ của AI, đã được người biên soạn duyệt" khi có dấu) → thanh hành động → **Từ khó** (chú thích dạng ô: từ – lời giải, liên kết mục từ) → ▸ Dị bản (đánh số, tô chữ khác; nơi, người kể khi có) → Bạn có biết? → phần cuối chung.
**Nghiệm thu:** lời hiện đúng từng ký tự như phần `loi`; trích dẫn đúng; tắt JavaScript vẫn đọc lời, chú thích ở cuối khối.

**Đợt 3 (D-105):** bài vốn có nhiều cách hiểu đã được biết đến thì ban biên tập viết thành "Cách hiểu 1 / Cách hiểu 2" trong khối Ý nghĩa; khối tuỳ chọn **"Hình ảnh trong bài"** (mỗi dòng: hình ảnh → điều được nói tới) cho bài có ẩn dụ, dùng cả cho đồng dao. Cách hiểu của bạn đọc ở tầng Hiểu thêm, tách rõ với Ý nghĩa. **Đã làm (plugin 1.0.51):** khối "Hình ảnh trong bài" (ca dao, đồng dao) — một ô chữ, mỗi dòng "hình ảnh → điều được nói tới", hiện ngay sau Ý nghĩa.

### 6.2. Tục ngữ – Thành ngữ (S05)

**Đợt 3 (D-105) — chuẩn mới:** câu ngắn (tối đa 2 vế, khoảng 60 chữ cái) là tiêu đề chữ to, **từ khó được tô cả trong câu tiêu đề**; câu dài (từ 3 dòng hoặc hơn khoảng 60 chữ cái) hiện như ca dao (tên là dòng đầu, lời đủ trong khung lời). Khối **Nghĩa**: **Nghĩa đen** (theo chữ) · **Nghĩa bóng** (bài học, hiện to nhất) · **Dùng khi** (ví dụ đã biên tập) → **Câu gần nghĩa · trái nghĩa** (liên kết) → thanh hành động → phần cuối chung. Bài tục ngữ mới không có ô Ý nghĩa riêng; bài cũ đã có Ý nghĩa vẫn hiện như cũ. Danh sách, thẻ: 2 dòng đầu rồi "…". **Đã làm (plugin 1.0.51):** câu dài nhận theo số dòng (≥ 3) hoặc số chữ cái (> 60); khối **Nghĩa** gom Nghĩa đen · Nghĩa bóng (chữ to nhất) · Dùng khi, thuộc tầng Đọc; ở chế độ sửa và trang Sửa tác phẩm, tục ngữ chưa có Ý nghĩa không còn ô "Thêm ý nghĩa" (bài cũ có Ý nghĩa vẫn hiện, vẫn sửa được). Câu gần nghĩa · trái nghĩa: chưa làm.

### 6.3. Vè và bài dài (S06)

Tiêu đề → số dòng · thời gian đọc → [Mục lục ▾] (đóng trên điện thoại) → các đoạn có tiêu đề (nhớ chỗ đang đọc trên máy, "Đọc toàn bài") → chú giải → ▸ **Bối cảnh** (sự kiện, năm, nơi, nhân vật — chỉ khi có nguồn; không gán giả) → dị bản → phần cuối chung.
**Nghiệm thu:** một bài dài thực tế và một bài rất dài (> 200 dòng) cuộn mượt, mục lục bàn phím được.

### 6.4. Đồng dao (S07)

Thẻ thông số trò chơi (👥 số người · 🎂 độ tuổi · 📍 nơi chơi · ⏱ thời lượng · đồ dùng; thiếu ô nào bỏ ô đó; luôn chữ + biểu tượng) → **[♪ Đọc cùng bé]** (một dòng một lần, chữ rất to, vỗ tay theo chữ, không âm thanh, Esc thoát) → Lời → **Cách chơi** (các bước đánh số; "Nguồn cách chơi: … · giấy phép" ngay dưới) → ▸ Dị bản ở các vùng → phần cuối chung → Trò chơi cùng tuổi.
Trẻ không cần đăng nhập, không thu dữ liệu; không tự phát âm thanh.

### 6.5. Câu đố (S08)

Câu hỏi (chữ lớn) → [Gợi ý] (`<details>`, khi có) → **[Hiện đáp án]** (`<details>`, hành động chính; tắt JavaScript vẫn mở được) → trong đó: đáp án, ảnh đáp án (Commons, ghi công, khi có), **Giải thích đáp án** (D-105: vì sao đáp án là như vậy — mỗi dòng tả gì, chơi chữ, từ cổ; chỗ lưu của Ý nghĩa, người viết; nằm trong khối ẩn để không lộ đáp án) → "Đố bạn bè ›" (vào Đố vui với chủ đề này) → câu đố cùng đáp án → phần cuối chung. **Đã làm (plugin 1.0.51):** "Giải thích đáp án" nằm trong khối "Hiện đáp án" sau đáp án; tấm sửa, trang Sửa tác phẩm, "Lời này từ đâu?" gọi đúng tên.
Đáp án nằm trong HTML (máy tìm kiếm vẫn đọc được), chỉ thu gọn khi hiển thị (D-087 (3), thay phần D-058).

### 6.6. Truyện (S09, đợt 5)

Tên → "Bản kể theo: <sách>, <năm>, tr. N · Kể lại: <bút danh>" → thời gian đọc · từ N tuổi · số đoạn → [Kể cho bé] [Mục lục ▾] → các đoạn (nhớ chỗ đọc) → ▸ Nhân vật → ▸ Chú thích → ▸ Ý nghĩa (biên soạn viết, tách khỏi truyện) → ▸ Bản kể khác → phần cuối chung. Cảnh báo bối cảnh lịch sử khi cần, đặt ngay đầu. Chỉ mở qua cổng thể loại; nguồn chỉ là sách hết bản quyền người sáng lập đã kiểm.

### 6.7. Lời này từ đâu? (S18, D-085, tên chốt D-093)

Khối thu gọn cuối bài, mỗi phần một dòng; dòng nào hiện là theo chính sách hiện của hồ sơ nguồn (mặc định như hiện nay):

```text
Lời       Bản lưu truyền phổ biến, chưa đối chiếu với sách in · Có sách? Gửi số trang
Ý nghĩa   Biên soạn: <bút danh> · có hỗ trợ của AI, đã duyệt <ngày>
Chú thích 3 mục · nguồn tham khảo đã ẩn (Chính sách thu thập dữ liệu)
Cách chơi Wikipedia — <bài> · CC BY-SA 4.0
Ảnh       Wikimedia Commons · <tác giả> · CC BY-SA 4.0
```

Tên khối: **"Lời này từ đâu?"** (D-093), nền giấy đất nhạt, thu gọn mặc định; liên kết "Lời này từ đâu?" trong ⓘ mở khối. Liên kết nguồn đã ẩn không có trong HTML gửi người đọc; người biên soạn đăng nhập thấy thêm "Chỉ người biên soạn thấy dòng này · Nguồn đã ẩn: …". Ghi công giấy phép mở vẫn ngay tại chỗ (dưới ảnh, dưới đoạn chép).

### 6.8. Bài có từ ngữ thô tục (D-111)

Người biên soạn bật công tắc "Có từ ngữ thô tục" ở trang Sửa tác phẩm; máy không bật. Bài đang bật:

- **Trang bài:** tiêu đề "<Thể loại> có từ ngữ thô tục", đường dẫn vị trí "Bài có từ ngữ thô tục"; lời, công cụ đọc và các tầng sau nằm trong một khối đóng sẵn: lời báo **"Bài có từ ngữ thô tục - cân nhắc khi xem."** + nút **"Hiển thị lời"** (mở rồi thành "Ẩn lời"). Phần tử details: tắt JavaScript vẫn mở được, bàn phím và trình đọc màn hình đọc lời báo trước; chữ thật không bị làm mờ mà không hiện đến khi mở. Người biên soạn ở chế độ sửa thấy khối mở sẵn, tên thật.
- **Thẻ bài** (danh sách, Từ điển, Tìm), **kết quả khớp cả câu**, **mục tuyển tập:** tên, lời, ý nghĩa trong cùng kiểu khối đóng; nút Đọc không đọc to tên bài.
- **Không vào** Vừa bổ sung, Câu hôm nay, Một câu ngẫu nhiên, Đọc tiếp; không có nút Đọc cùng bé.
- **Chia sẻ link:** ảnh xem trước là ảnh chung của thư viện, mô tả trung tính. Tên trên tab trình duyệt và dữ liệu có cấu trúc giữ nguyên.
- Lời không bao giờ bị sửa, che chữ hay thay chữ.

## 7. Khám phá và tra cứu

### 7.1. Tuyển tập (S12)

```text
Trang chủ › Tuyển tập
TUYỂN TẬP · <loại: Chủ đề / Theo dịp / Tình huống / Bài học>
Ca dao, tục ngữ về mẹ
18 câu · 6 phút · cập nhật <ngày> · Biên soạn: <bút danh>
Lời dẫn (người biên soạn viết, 2–4 câu)
[▣ Trình chiếu cả bộ] [⧉ Sao chép cả bộ] [⎙ In]   ← Trình chiếu là hành động chính
Mục lục ▾ (theo nhóm)
1  CA DAO                               ← nhãn thể loại từng câu
   Công cha như núi Thái Sơn…           ← chạm từ xem nghĩa
   Ý nghĩa: …                           ← Ý nghĩa của bài
   Hợp khi: …                           ← ghi chú của mục (nếu có)
   Bản lưu truyền phổ biến              ← nhãn mức kiểm gọn, nút ⧉ Sao chép ở góc mục
   [☆ Lưu]                    Xem bài ›
2  TỤC NGỮ …
HAY BỊ NHẦM                             ← chỉ câu có cờ, kèm lý do
Tuyển tập khác ›
```

- Công khai khi có lời dẫn và ≥ 8 câu đã đăng (agent có thể chỉnh ngưỡng, ghi trong PR).
- `<title>` "…: N câu có giải nghĩa" chỉ khi mọi câu có Ý nghĩa.
- "Sao chép cả bộ" chép mọi câu (đánh số, giữ thụt câu lục, mỗi câu kèm đường dẫn bài) và tên tuyển tập, để dán vào giáo án.
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

**D-112 (14/11/2026, thay quy tắc nhóm/thẻ mạnh ở trên):** bấm Tìm so trọn chữ; gợi ý đang gõ dùng đầu chữ và được báo rõ. Kết quả đúng dấu, mở rộng không dấu, khớp đầu chữ là các phạm vi riêng kèm số. Một từ không có thẻ nổi bật; chỉ trọn tên/dòng lời duy nhất mới có. Danh sách giữ thứ tự liên quan xuyên thể loại, có lọc và phân trang 20 bài bằng GET, đủ tới trang cuối; mỗi thẻ đánh dấu/trích đúng chỗ khớp và nêu phần khớp. Chữ và công tắc che của D-111 giữ nguyên. Chi tiết: [docs/39](39-tim-va-sap-xep-danh-sach.md).

**D-113:** bộ lọc Tìm dùng hai ô chọn có nhãn Cách khớp và Thể loại, chung form GET với từ khoá và nút Áp dụng. Máy tính hiện cùng một hàng; điện thoại thu vào Bộ lọc có tóm tắt, huy hiệu đếm lựa chọn khác mặc định. Đặt lại giữ từ khoá, về trang 1; không có kết quả thì mở bộ lọc và chỉ lối sang phạm vi còn bài. Mục tiêu khối lọc mặc định ≤ 100 px tại 390 px, dùng được bằng bàn phím và không JavaScript. Chi tiết: [docs/40](40-bo-loc-tim-gon.md).

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

Danh sách câu có cờ **"Có thể không phải dân gian"** (D-105; trước gọi "Nghi không phải dân gian"), mỗi câu kèm lý do người biên soạn viết và nguồn của lý do. Trang công khai khi có ≥ 5 câu.

### 7.9. Cho lớp học (S16, đợt 5)

Chọn lớp và bộ sách (nhớ trên máy) → bài học có bộ câu → [Trình chiếu] [Phiếu in] [Sao chép trích dẫn cả bộ]. Chỉ câu người biên soạn đã thấy trong SGK. Phiếu in A4: lời, Ý nghĩa, chú thích, trích dẫn, không màu nền. "Chưa thấy bài học bạn cần? Gửi tên sách, trang ›".

## 8. Người đọc góp và trang dự án

### 8.1. Bạn muốn góp gì? (S17)

Một khối cuối bài, chọn loại: Ở quê nói khác (dị bản) · Góp ý chú thích · Báo lỗi · Gửi số trang (tên sách, trang) · Cảm nghĩ ngắn · Viết cảm nhận (bài phân tích). Ô: nội dung → xuất xứ (nơi, người kể — không bắt buộc) → cam kết tự viết, đồng ý CC BY 4.0 → gửi. Không bắt tên thật, email, số điện thoại; ô bẫy + giới hạn mỗi giờ + Turnstile khi có khoá; báo lỗi vẫn giữ chữ đã gõ. Mọi thứ vào hàng chờ, không công bố tự động. Ghi âm không mở ở V2 (cần hồ sơ đồng ý, lằn ranh 3).

**D-108 — form Góp gọn:** liên kết từ bài chọn sẵn loại và giữ bài; bộ chọn thu gọn, mở được bằng bàn phím. `/gop/` trực tiếp mở bộ chọn, không chọn hộ. Chỉ hiện nhóm đúng loại: sách/trang ở Gửi số trang; tiêu đề và cam kết tự viết ở Bài cảm nhận; nơi nghe/người kể thu gọn ở dị bản; bút danh và vai tuỳ chọn thu gọn. Một ô nội dung dùng nhãn/hướng dẫn/nút gửi theo loại. Consent vẫn hiện rõ, mặc định chưa chọn; phần xử lý nội dung chi tiết thu gọn. CSS thực hiện đổi nhóm, không cần JavaScript. POST gửi tại `/gop/`; lỗi vẽ lại các ô đã nhập bằng HTML escape, HTTP 422 và `private, no-store`, không ghi nháp lỗi vào URL/cookie/DB; gửi thành công chuyển hướng 303 theo luồng cũ. Chi tiết và kiểm thử: [docs/35](35-form-gop-gon-va-giu-chu.md). Không đổi form Cách hiểu trên trang bài.

**Đợt 3 (D-105):** thêm loại **Cách hiểu của bạn** — form nhanh ngay trên trang bài (một ô chữ có giới hạn độ dài, bút danh tuỳ chọn, cam kết tự viết + CC BY 4.0, cùng chống rác); vào Hộp việc chờ duyệt; được Hiện thì nằm ở tầng Hiểu thêm dưới Ý nghĩa, ghi bút danh và ngày. Ban biên tập có thể dựa vào một cách hiểu để tự viết lại Ý nghĩa và ghi công; máy không tự chép. Không tính vào cấp chất lượng. **Đã làm (plugin 1.0.50):** bài chưa có Ý nghĩa thay lời "đang được bổ sung" bằng "Câu này chưa có lời giải nghĩa. Bạn hiểu câu này thế nào? Viết vài dòng để người đọc sau hiểu rõ hơn." + nút **"Chia sẻ cách hiểu"** (tới form ở phần Cách hiểu); form: một ô chữ tối đa 1.000 ký tự, bút danh tuỳ chọn, "Tôi tự viết" và đồng ý CC BY 4.0 bắt buộc, ô bẫy, Turnstile khi có khoá, 5 lần gửi mỗi giờ như trang Góp; không có ô tên thật, email, số điện thoại. Gửi xong quay lại đúng phần Cách hiểu với lời cảm ơn. Cách hiểu không có ở trang Góp. Mỗi cách hiểu đã hiện có "Báo vi phạm".

### 8.2. Đã lưu (`/da-luu/`)

☆ Lưu trên trang bài; danh sách nằm trên máy người đọc (`localStorage`, bọc try/catch); trang Đã lưu: lọc theo thể loại, Bỏ lưu, [Trình chiếu cả danh sách]; trống: hướng dẫn lưu; nói rõ xoá dữ liệu trình duyệt là mất danh sách; site không biết ai lưu gì.

### 8.3. Phương pháp, Nguồn, Về dự án, Chính sách thu thập dữ liệu, Quyền riêng tư (S18)

Trang tĩnh do người sáng lập viết (mang từ MVP, sửa tên dự án). Phương pháp có mục "Dòng nhỏ dưới lời nói gì" (đích của "Vì sao?") và cách đọc "Lời này từ đâu?". Không hứa điều site chưa làm.

### 8.4. 404 và lỗi

Trang 404 riêng: ô tìm điền sẵn chữ từ đường dẫn, gợi ý tuyển tập. Lỗi tìm, bản đồ, đố vui: quay về dạng không JavaScript, có câu giải thích và lối đi tiếp.

## 9. Hệ thống thiết kế, trợ năng, ngân sách

### 9.1. Token (D-092, D-093)

- **Bảng tím – lục** (giá trị đầy đủ, bản tối và tỷ lệ tương phản: `design/v2/tokens/`, `design/v2/qa/contrast.json`): giấy `#FEFCF8`, thẻ `#FFFFFF`, mực `#14120F`, chữ phụ `#5A5652`; **tím** `#5E3AAE` (thương hiệu, nút chính, liên kết, mục đang chọn; nền nhạt `#EDE6FB`); **lục** `#1F6B4A` (Ý nghĩa, nhãn "đã đối chiếu"; nền nhạt `#E6F2E9`); **tô vàng** `#FFF3B0` (từ có chú thích); lỗi `#A1201A`; viền điều khiển `#8C8478` (≥ 3:1). Mọi cặp chữ ≥ 4,5:1 ở cả sáng và tối. Ảnh chia sẻ nền tím đậm `#4B2F8A` cố định. Không dùng màu làm tín hiệu duy nhất.
- **Nền khối theo vai** (D-093; để mắt tách khối, không ngập chữ — mỗi khối vẫn có tiêu đề): vàng `#FFF8E1` (Từ khó, Cách chơi) · tím `#F6F2FD` (Dị bản, Câu gần nghĩa) · lục `#EFF7F1` (Giải nghĩa) · xanh `#EEF4FB` (Dùng khi, Bạn muốn góp gì?) · đất `#F7F2E9` (Lời này từ đâu?, Cảm nghĩ). Thẻ lời giữ nền trắng để tập trung. Ô bên trong khối nền nhạt dùng nền trắng.
- **Màu thể loại** (D-093; nền thẻ, nhãn trong danh sách, Đọc tiếp, ô thể loại): ca dao tím · tục ngữ lục · đồng dao vàng đất `#8A5A00` · vè xanh `#2B5580` · câu đố mận `#A3245E`, mỗi màu có nền nhạt tương ứng. Bản tối có bộ riêng.
- **Giữ** Literata (lời, tiêu đề) + Be Vietnam Pro (giao diện) tự lưu trữ; bề rộng đọc 38em (trang tác phẩm 1040 px gồm cột công cụ 232 px); chạm ≥ 44 px. **Nút** (D-093): cao 44 px, chữ 15 px đậm, biểu tượng 18 px, nhãn không xuống dòng; nút tìm chính 48 px. Hàng nút cuối khối dùng lưới cột đều thay vì xếp tràn.
- **Hình khối:** nút và ô nhập bo 10 px; thẻ bo 14 px, bóng nhẹ; nhãn, chip, thanh thể loại tròn hẳn; logo chữ P trắng trong ô vuông tím.
- **Thang:** cỡ chữ 7 bậc (15 / 17 / 22 / 28 / 36 / 48 / 64; lời ca dao lớn hơn văn xuôi 1 bậc, câu tục ngữ 2 bậc); khoảng cách 8 bậc (4 … 64); màu nhãn từng thể loại (luôn chữ + biểu tượng).

### 9.2. Thành phần (khối)

| Thành phần | Trạng thái phải có |
|---|---|
| Thẻ bài | đủ / thiếu nghĩa / có cờ; 2 dòng đầu mỗi dòng một hàng, quá dài thì "…" |
| Khối lời | thơ, câu một dòng, bài dài; có / không chú thích; mỗi câu một dòng (co chữ vừa khung); không JavaScript (CSS ước lượng theo độ dài dòng) |
| Nhãn mức kiểm | bản lưu truyền phổ biến / đã đối chiếu sách / đã đối chiếu web; ⓘ mở, đóng |
| Khối Ý nghĩa | chỉ Ý nghĩa / đủ các phần nghĩa / có AI hỗ trợ / chưa có / người biên soạn đang sửa |
| Nút Sao chép trong thẻ | máy tính có chữ / điện thoại chỉ biểu tượng / lỗi clipboard (chọn chữ để chép tay) |
| Ảnh để chia sẻ | 3 khổ × 3 nền theo thể loại; kèm / không ý nghĩa; câu đố không lộ đáp án |
| Khối dị bản | 1 / nhiều / có nơi, người kể |
| Thẻ thông số trò chơi | thiếu ô nào bỏ ô đó |
| Khối đố | chưa mở / đã mở; có / không gợi ý, ảnh, giải thích |
| Thẻ tuyển tập | — |
| Thanh hành động | trang bài / trong tuyển tập (gọn) / Đã lưu |
| Chip lọc | chọn / không / 0 (ẩn) |
| Lời này từ đâu? | theo chính sách / người biên soạn đăng nhập |
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

- Lập chỉ mục (D-106, khi đã mở công tắc chặn Google toàn site — H5): **mọi tác phẩm đã đăng**, không xét độ đầy đủ; tuyển tập đã công khai; trang thể loại; trang chủ đề / vùng / mô-típ có từ 3 tác phẩm đã đăng; mục từ có lời giải dự án viết hoặc nguồn mở. Trang lọc, tìm, bài nháp / ẩn: `noindex`.
- `<title>` và mô tả: tác phẩm có Ý nghĩa (tục ngữ: nghĩa bóng) "<tên> — nghĩa và cách dùng", mô tả từ Ý nghĩa; chưa có: tên + thể loại, mô tả từ lời — không hứa điều trang không có.
- Ảnh chia sẻ 1200×630 mỗi tác phẩm (thể loại, 4 dòng đầu, nhãn mức kiểm, phongdao.com), nền đầu tiên của thể loại; ảnh chung cho trang khác.

### 10.1. Ảnh để chia sẻ (D-093)

Khối cuối bài cho người đọc tạo ảnh đăng mạng xã hội: xem trước trực tiếp + chọn **khổ** (Ngang 1200×630 · Vuông 1080×1080 · Dọc 1080×1350) + **nền** (3 nền mỗi thể loại: ca dao Trống đồng · Giấy dó · Đêm sao; tục ngữ Đồng lúa · Giấy dó · Trống đồng; đồng dao Nắng · Giấy dó · Đồng lúa; vè Sông nước · Giấy dó · Trống đồng; câu đố Đêm sao · Nắng · Trống đồng) + ô **Kèm ý nghĩa** → [Tải ảnh] [Sao chép liên kết]. Hoạ tiết (trống đồng, mây, sóng, chấm, sao) vẽ bằng SVG của dự án, không ảnh ngoài, không ảnh AI. Ảnh luôn có thể loại, lời (mỗi câu một dòng, co chữ vừa khung; bài dài lấy 4 dòng đầu + "…"), tên thư viện, đường dẫn bài; câu có cờ ghi thêm "Nghi không phải dân gian"; **câu đố không lộ đáp án** ("Lời giải ở phongdao.com"). Máy chủ sinh PNG theo khổ và nền (đệm 300 ảnh gần nhất), tải về không cần JavaScript (form GET tới `/?phongdao_anh=<id>`); thẻ og:image dùng ảnh ngang, nền đầu của thể loại. Không thêm sự kiện đo mới: bấm Tải ảnh tính vào `chia_se`.
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
| "Câu này lấy từ đâu, ai giải nghĩa?" | mở Lời này từ đâu?, nói lại đúng |

Đạt ≥ 85% tác vụ; chỗ lúng túng thành task.

## 13. Không làm

Liên kết ra ngoài không liên quan; tra năm sinh, phong thuỷ, meme; bài văn dài viết lại nghĩa từng câu; tuyên bố "đã đối chiếu", "hàng đầu", số người dùng không kiểm được; mục lục lặp, đoạn nhắc lại, ảnh khung trống; cảnh báo "chưa kiểm chứng" để cuối bài; văn mẫu, dàn ý; truyện nước ngoài, thơ hiện đại để tăng số; tài khoản người đọc; bình luận mở; chấm điểm trẻ.

## Phụ lục A. Câu chữ giao diện

Chép từ `docs/luu-tru/20-dac-ta-giao-dien.md` mục 10 (11/11/2026, D-107), bỏ các dòng đã thay: nhãn mức A–D (thay bằng nhãn mức kiểm mục 6.0, D-093), "Hiểu trong 10 giây" (thành **Ý nghĩa**, D-093).

| Vị trí | Nội dung |
|---|---|
| Ô tìm | "Gõ câu bạn nhớ, hoặc điều bạn đang cảm thấy…" |
| Cờ gán nhầm | **"Có thể không phải dân gian"** — "Câu này thường được gọi là ca dao, nhưng [căn cứ]." (D-105) |
| Cờ tranh luận | "Các nhà nghiên cứu còn có những cách hiểu khác nhau về [điểm]." |
| Ghi công chú thích | "Tham khảo: [nguồn] ↗" (viết lại) · "Theo [nguồn] ↗" (trích nguyên văn) — chỉ khi chính sách nguồn cho hiện (D-068) |
| Hỗ trợ AI | "Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt." |
| Bài chưa có Ý nghĩa | Lời mời góp cách hiểu (mục 6, D-105) |
| Tên bản đại diện | "Bản tham chiếu" |
| Sao chép trích dẫn | "Đã sao chép. Cảm ơn bạn đã ghi nguồn!" |
| Lời mời đóng góp | "Ở quê bạn câu này nói khác?" · "Bạn có sách? Gửi số trang" · "Ở quê bạn có câu này không? Góp" |

**Từ cấm trong giao diện:** "bản gốc", "bản đúng", "bản chuẩn", "bản sai", "AI giải thích", "câu hay nhất". Không dùng mã mức ("mức A/B", "L2") trên giao diện người đọc — chỉ dùng tên nhãn bằng lời thường.

## Phụ lục B. Trợ năng (WCAG 2.2 AA)

Chép từ `docs/luu-tru/20-dac-ta-giao-dien.md` mục 11 (11/11/2026, D-107); áp dụng cho trang người đọc và khu quản trị.

- `<html lang="vi">`; tiêu đề trang theo thứ bậc đúng.
- Mọi điều khiển dùng được bằng bàn phím, viền focus rõ, và **không bị thanh trên / thanh dưới cố định che khi nhận focus** (2.4.11).
- Vùng chạm tối thiểu 44×44 px.
- Nhãn không chỉ dựa vào màu: luôn có chữ + biểu tượng.
- Ảnh xem trước có văn bản thay thế là lời thơ.
- Tôn trọng `prefers-reduced-motion`.
- HTML ngữ nghĩa trước ARIA: `<button>`, `<dialog>`, `<details>`, `<nav>`, `<main>`; ARIA chỉ bổ sung khi HTML không đủ.
- Trợ năng nằm trong Definition of Done của mọi task (`docs/22` mục 5), không để cuối dự án.
