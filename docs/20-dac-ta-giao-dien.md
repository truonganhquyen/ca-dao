# 20 · Đặc tả giao diện

| | |
|---|---|
| Phiên bản | 1.1 · 05/10/2026 |
| Trạng thái | **Ổn định cho GĐ0.5–GĐ1.** Mục 7 (P0.5) chờ Q-005 (tên miền) |
| Đọc khi | Làm bất kỳ trang, thành phần, câu chữ giao diện nào |
| Liên quan | `10` (dữ liệu hiển thị) · `21` (cách dựng) · `23` (tiêu chí kiểm thử) |

Tinh thần: **"Một trang sách cổ được chăm chút, đặt trong một chiếc điện thoại hiện đại"** — một kho lưu trữ văn hóa số với trải nghiệm đọc hiện đại. Lời thơ là nhân vật chính. Mọi màn hình thiết kế cho màn hình hẹp trước.

**Nguyên tắc chi phối: mô hình dữ liệu giàu, trải nghiệm đọc mặc định đơn giản.** Dữ liệu có nhiều lớp không có nghĩa là trang hiện mọi lớp cùng lúc.

---

## 1. Ngôn ngữ thị giác

- Nhiều khoảng trắng; không ảnh nền sau chữ.
- Không phủ biểu tượng văn hóa sáo mòn (trống đồng, cây tre, nón lá) khắp giao diện; nếu dùng thì như dấu nhấn rất nhỏ. Chữ đẹp mới là "chất Việt".
- Không popup, không banner, không tự phát âm thanh, không đếm ngược, không hộp "đăng ký nhận tin" chặn màn hình.
- Tiêu chí duyệt mỗi màn hình: **Chuẩn** (thông tin đúng, có nguồn) · **Sống** (gần đời thường) · **Đẹp** (chữ, khoảng trắng).

**Luật giao diện cho người và AI agent khi dựng trang công khai:**

| Nên | Không |
|---|---|
| Nội dung trước, ít khung viền và thanh công cụ | Thẩm mỹ bảng điều khiển/SaaS: lưới thẻ dày đặc, chỉ số, biểu đồ trang trí |
| Bề rộng đọc có giới hạn: lời thơ và văn bản tối đa ~38em, căn giữa trên màn hình rộng | Văn bản tràn hết chiều ngang màn hình lớn |
| Một hành động chính cho mỗi vùng | Nhiều nút cùng trọng số đứng cạnh nhau |
| Phân tách bằng khoảng trắng, rồi đường kẻ mảnh; bóng đổ chỉ khi thật cần | Bóng đổ, gradient, bo góc lớn làm mặc định |
| Chuyển động nhẹ, ngắn, tôn trọng `prefers-reduced-motion` | Hiệu ứng trang trí, cuộn kéo dài |
| Bản sắc Việt qua chữ, nhịp, khoảng trắng; họa tiết chỉ là dấu nhấn nhỏ | Trống đồng, tre, nón lá phủ khắp trang |
| Dùng thẻ (card) cho danh sách tình huống, kết quả tìm | Bọc mọi khối nội dung trong thẻ |
| Thành phần mang tên miền nghiệp vụ (mục 14) | Giao diện trông như thư viện thành phần mặc định |

### 1.1. Màu (token)

Giá trị khởi đầu; độ tương phản với nền đã tính theo công thức WCAG (≥ 4,5 là đạt AA cho chữ thường; ngưỡng này không đổi ở WCAG 2.2). Đổi giá trị thì tính lại.

| Token | Sáng | Tương phản | Tối | Tương phản | Dùng cho |
|---|---|---|---|---|---|
| `--giay` | `#FBF8F1` | — | `#1B1A18` | — | Nền |
| `--muc` | `#22201C` | 15,3 | `#EDE6D6` | 14,0 | Chữ chính, lời thơ |
| `--son` | `#9E2A22` | 7,1 | `#E8776B` | 6,0 | Liên kết, điểm nhấn, viền focus |
| `--nhat` | `#5F594F` | 6,5 | `#ABA496` | 7,0 | Chữ phụ, nguồn |
| `--nhan-a` | `#3F5A2C` | 7,3 | `#9CC07F` | 8,5 | Nhãn "Đã đối chiếu tư liệu" |
| `--nhan-b` | `#2B5580` | 7,3 | `#8DB6E3` | 8,2 | Nhãn "Có tư liệu điền dã" |
| `--nhan-c` | `#7A5A12` | 6,0 | `#D9B56A` | 8,9 | Nhãn "Cộng đồng ghi nhận" |
| `--nhan-co` | `#5E5E5E` | 6,1 | `#B0B0B0` | 8,0 | Nhãn "Nghi không phải dân gian" |

Chế độ tối theo cài đặt hệ thống (`prefers-color-scheme`) có từ P0-Core vì chỉ là đổi token; **nút đổi** thủ công là P0.5, lựa chọn lưu ở `localStorage` (tùy chọn giao diện, không phải theo dõi).

### 1.2. Chữ

| Vai trò | Phông | Cỡ trên điện thoại | Ghi chú |
|---|---|---|---|
| Lời thơ | Phông có chân hỗ trợ tốt tiếng Việt (ví dụ Literata hoặc Noto Serif) | ≥ 22 px, giãn dòng 1,7 | |
| Giao diện, chú giải, nghĩa | Be Vietnam Pro | ≥ 17 px (chữ phụ ≥ 15 px), giãn dòng ≥ 1,5 | Có người đọc lớn tuổi; không khóa phóng to trình duyệt |

- Tự lưu trữ phông, chỉ bộ ký tự Latin + tiếng Việt, WOFF2, `font-display: swap`.
- Kiểm dấu chồng ("ẫ", "ỡ", "ự", "Ngưỡng", "Thuở", "Quyện") ở cỡ nhỏ và lớn.
- Lục bát: mặc định thụt lề câu lục so với câu bát, đặt bằng một biến cấu hình (Q-006 thử với beta).

## 2. Sơ đồ trang

```text
/                               Trang chủ
├── /tim?q=…                    Kết quả tìm kiếm
├── /ca-dao/{slug}              Trang tác phẩm  (tương tự /tuc-ngu/, /thanh-ngu/)
│   └── /ca-dao/{slug}/so-sanh  So sánh dị bản (P1)
├── /tinh-huong/ , /tinh-huong/{slug}
├── /chu-de/{slug} , /the-loai/{slug}
├── /tu-dien/ , /tu-dien/{slug}
├── /a-z , /ngau-nhien
├── /mo-tip/{slug} , /sgk/{bo-sach}/{lop} , /vung/{slug}      (P1)
├── /nhung-nguoi-giu-lua                                      (P1)
├── /phuong-phap , /nguon , /chinh-sach-ai , /giay-phep , /quyen-rieng-tu , /ve-du-an
├── /du-lieu-mo                                               (P1)
└── /dong-gop                   Trang giới thiệu + liên kết form
```

- **Thanh trên (điện thoại):** tên dự án · nút tìm · menu.
- **Menu:** Tình huống · Chủ đề · Từ điển · Đóng góp · Về dự án.
- **Chân trang:** Phương pháp · Nguồn · Chính sách AI · Giấy phép · Quyền riêng tư · Liên hệ.

## 3. Trang chủ

Thứ tự khối:
1. Câu định vị "Đọc một câu. Hiểu một đời sống." + ô tìm lớn, gợi ý trong ô: "Gõ câu bạn nhớ, hoặc điều bạn đang cảm thấy…"
2. **"Hôm nay bạn đang…"**: 8–12 thẻ tình huống. P0-Core: thứ tự cố định (`mac_dinh` trong `config/lich-tinh-huong.yaml`). P0.5: đổi theo dịp (Tết, 8/3, Vu Lan, 20/10, 20/11…).
3. **"Câu ca dao hôm nay"** (P0.5): một bài xoay vòng theo ngày, chọn lúc build; có nút Tạo thẻ.
4. Duyệt theo chủ đề.
5. Một đoạn ngắn: dự án kiểm chứng nội dung thế nào + liên kết `/phuong-phap`.
6. Lời mời đóng góp.

## 4. Trang tác phẩm (P0-Core)

Mục tiêu: trong 10 giây đầu, người dùng thấy lời thơ đẹp, biết mức đáng tin, hiểu được từ khó mà không rời trang.

### 4.1. Đọc theo lớp

**Lớp 1 — mở sẵn, theo đúng thứ tự** (đủ để đọc, hiểu, tin, trích dẫn):
1. **Nhãn thể loại + nhãn bằng chứng** (chữ + biểu tượng). Có cờ: nhãn xám "Nghi không phải dân gian" hoặc dòng "Đang có tranh luận". Có `canh_bao_noi_dung`: khung cảnh báo ngay dưới lời.
2. **Lời Bản tham chiếu.** Từ có chú giải gạch chân chấm; chạm là hiện (mục 5).
3. **Hành động:** Trích dẫn (hành động chính) · Góp ý · Nghe (chỉ khi có ghi âm, P0.5) · Tạo thẻ (P0.5).
4. **"Hiểu trong 10 giây"**: `nghia_ngan`.
5. **Dòng nguồn ngắn:** "Đối chiếu theo: *[sách]*, tr. [x]" + liên kết tới khối Nguồn đầy đủ.
6. **Dị bản:** "Có N dị bản" + vùng của từng dị bản; chạm để mở danh sách (chỉ A/B).

**Lớp 2 — gọn, chạm để mở** (mặc định thu gọn, trừ dòng thẻ tình huống):
7. **"Dùng khi…"**: thẻ các tình huống chứa bài (hiện sẵn dạng một dòng thẻ nhỏ).
8. **Nghĩa và các cách hiểu** (`dien_giai`; nhiều góc nhìn thì mỗi góc một mục, có nguồn; tục ngữ có nghĩa đen/bóng, góc kiểm chứng).
9. **Bối cảnh diễn xướng.**
10. **Âm luật.**
11. **Cùng mô-típ / Bài liên quan** (tối đa 5; P1).
12. **Nguồn và lịch sử biên soạn** đầy đủ: mọi nguồn có số trang; người biên soạn; ngày kiểm định; liên kết lịch sử sửa đổi.
13. **Cộng đồng ghi nhận** (dị bản C), với câu "chưa được đối chiếu".
14. Lời mời: **"Ở quê bạn câu này nói khác?"** → form (điền sẵn mã).

Khối không có dữ liệu thì ẩn hẳn, không hiện tiêu đề rỗng. Thu gọn dùng `<details>`/`<summary>` để không cần JavaScript.

### 4.2. Khung trang (điện thoại)

```text
┌─────────────────────────────────┐
│ ☰  Kho tàng Ca dao          🔍 │
├─────────────────────────────────┤
│ CA DAO · ✓ Đã đối chiếu tư liệu │
│                                 │
│   [lời bản tham chiếu, dòng 1]  │
│ [dòng 2 — từ khó gạch chân chấm]│
│   [dòng 3]                      │
│ [dòng 4]                        │
│                                 │
│ [❝ Trích dẫn]      Góp ý · ▶ Nghe│
├─────────────────────────────────┤
│ Hiểu trong 10 giây              │
│ [nghia_ngan]                    │
│ Đối chiếu theo: [sách], tr. [x] ›│
│ 3 dị bản: Bắc Bộ · Nam Bộ…     ›│
├─────────────────────────────────┤  ← hết lớp 1
│ Dùng khi: (tình huống) (…)      │
│ ▸ Nghĩa và các cách hiểu        │
│ ▸ Bối cảnh diễn xướng           │
│ ▸ Nguồn và lịch sử              │
│ ▸ Cộng đồng ghi nhận (2)        │
├─────────────────────────────────┤
│ Ở quê bạn câu này nói khác?     │
└─────────────────────────────────┘
```
(Lời trong khung chỉ minh họa bố cục.)

### 4.3. Yêu cầu chức năng

| ID | Yêu cầu |
|---|---|
| FR-1.1 | Lời hiển thị đúng từng dòng của dị bản; không tự xuống dòng giữa chữ có dấu |
| FR-1.2 | Từ có chú giải là `<button>` có gạch chân chấm; chạm/nhấn mở ô chú giải; điện thoại: bảng trượt từ dưới lên; máy tính: popover |
| FR-1.3 | Ô chú giải: từ, loại, `giai_nghia` (hoặc `nghia_tai_cho`), nguồn, liên kết "Xem thêm" tới `/tu-dien/{slug}`; **không che câu thơ đang đọc** |
| FR-1.4 | Tắt JavaScript vẫn đọc được: chú giải hiện dạng chú thích cuối khối lời, liên kết neo hai chiều |
| FR-1.5 | Nhãn bằng chứng luôn có chữ + biểu tượng; chạm vào hiện câu giải thích và liên kết `/phuong-phap` |
| FR-1.6 | Nút Nghe chỉ hiện khi có ghi âm; trình phát gọn; hiện người thể hiện (hoặc "Ẩn danh"), vùng, loại; **không tự phát**; có liên kết bản chép lời |
| FR-1.7 | Mỗi trang có JSON-LD (`21` mục 8) |
| FR-1.8 | Lớp 1 (mục 4.1) hiện đầy đủ trong khung nhìn đầu tiên và màn hình kế tiếp trên điện thoại 360 px với bài ≤ 8 dòng |

## 5. Chú giải — hành vi

- Mở: chạm / Enter / Space. Đóng: chạm ra ngoài / Esc / nút Đóng. Đóng xong trả focus về từ vừa chạm.
- Một ô mở tại một thời điểm.
- ARIA: nút có `aria-expanded`, `aria-controls`; ô là `role="dialog"` có nhãn (điện thoại) hoặc vùng liên kết (máy tính).
- Ghi sự kiện `chu_giai_mo` (mã mục từ).

## 6. Trích dẫn

Nút Trích dẫn mở hộp chọn 2 định dạng, nút Sao chép, thông báo "Đã sao chép. Cảm ơn bạn đã ghi nguồn!". Ngày truy cập tự điền theo ngày bấm.

**Kiểu thông dụng:**
```text
"<dòng đầu>…" (<thể loại>). Bản tham chiếu, đối chiếu theo: <tác giả>, <tên sách>, <NXB>, <năm>, tr. <trang>. Truy cập tại: <URL>, ngày <dd/mm/yyyy>.
```
**Kiểu APA 7 (điều chỉnh cho tư liệu dân gian):**
```text
Kho tàng Ca dao Tục ngữ Việt Nam. (<năm xuất bản trang>). <Tiêu đề> [<Thể loại>, <id>]. <URL>
```
Chỉ trích dẫn từ dị bản A/B. BibTeX ở P1.

## 7. Thẻ chia sẻ (P0.5)

Mục tiêu: mỗi lần chia sẻ là một lần đưa một trích dẫn **đúng và có nguồn** ra mạng xã hội.

| ID | Yêu cầu |
|---|---|
| FR-4.1 | Nút Tạo thẻ mở trình tạo: xem trước, chọn mẫu, chọn khổ, tải về / chia sẻ |
| FR-4.2 | **2 mẫu khi ra mắt:** "Giấy dó" (nền ngà, chữ có chân, viền mảnh) và "Mực tàu" (nền tối, chữ sáng). Thêm mẫu ở P1 |
| FR-4.3 | 2 khổ: 1080×1350 (bài đăng dọc) và 1080×1920 (Story) |
| FR-4.4 | Thẻ **luôn tự động** có: lời, dòng thể loại ("Ca dao Việt Nam" / "Tục ngữ Việt Nam"), vùng nếu là dị bản địa phương, biểu tượng nhãn bằng chứng, tên miền + đường dẫn bài (chờ Q-005). **Không có cách nào bỏ dòng nguồn** |
| FR-4.5 | Không tạo thẻ cho: dị bản C/D; tác phẩm mang cờ nghi gán nhầm/phỏng tác; tác phẩm có `canh_bao_noi_dung` |
| FR-4.6 | > 8 dòng thì tự giảm cỡ chữ; > 12 dòng thì đề nghị chọn đoạn (tối thiểu một cặp lục bát trọn vẹn) và ghi "(trích)" |
| FR-4.7 | Tạo ảnh trên trình duyệt bằng Canvas; phông nạp trước bằng `FontFace` để không lỗi dấu |
| FR-4.8 | Điện thoại có hỗ trợ: Web Share API chia sẻ thẳng tệp ảnh, kèm văn bản là lời thơ (thay thế cho ảnh); không hỗ trợ thì tải về |
| FR-4.9 | Mã QR trỏ về trang bài: tùy chọn, mặc định tắt |

## 8. Tìm kiếm

- Một ô duy nhất ở đầu mọi trang; trang kết quả `/tim?q=…`.
- Kết quả cập nhật theo phím gõ sau 150 ms; dùng được hoàn toàn bằng bàn phím.
- Mỗi kết quả: dòng đầu (in đậm phần khớp), thể loại, nhãn bằng chứng, `nghia_ngan`. Tối đa 20, có "Xem thêm".
- Kết quả trang tình huống và mục từ hiện thành nhóm riêng phía trên khi khớp mạnh ("Tình huống: Khi nhớ mẹ").
- **Không có kết quả:** "Chưa tìm thấy câu này trong kho đã kiểm chứng. Có thể bạn muốn xem những tình huống dưới đây, hoặc gửi cho chúng tôi câu bạn đang tìm." + 6 tình huống phổ biến + liên kết form.
- Không bao giờ hiển thị văn bản do AI sinh ra; "lý do khớp" chỉ là nhãn hoặc câu "Hợp khi…" do người viết.

## 9. Các trang khác

| Trang | Nội dung | Ghi chú |
|---|---|---|
| Tình huống | Tiêu đề, `mo_dau`, danh sách bài (lời rút gọn ≤ 4 dòng + "Hợp khi…") | Chỉ tạo khi ≥ 3 bài đã xuất bản |
| Chủ đề, thể loại (P0-Core); A–Z (P0.5) | Danh sách bài | Trang < 3 mục gắn `noindex` |
| Từ điển `/tu-dien/{slug}` | Từ, loại, giải nghĩa đầy đủ, nguồn, mọi bài có từ này | |
| Ngẫu nhiên (P0.5) | Chuyển tới một bài đã xuất bản | Làm bằng danh sách sinh lúc build |
| Phương pháp | Giải thích nhãn A/B/C, cờ, quy trình kiểm chứng | Bản nháp ở `50` |

## 10. Câu chữ giao diện

| Vị trí | Nội dung |
|---|---|
| Ô tìm | "Gõ câu bạn nhớ, hoặc điều bạn đang cảm thấy…" |
| Nhãn A | "✓ Đã đối chiếu tư liệu" — giải thích: "Lời này đã được đối chiếu với [tên sách], trang [x]." |
| Nhãn B | "◉ Có tư liệu điền dã" — "Lời này được ghi lại trực tiếp từ người kể ở [vùng]." |
| Nhãn C | "◌ Cộng đồng ghi nhận" — "Lời này do cộng đồng gửi, chưa được đối chiếu với tư liệu." |
| Cờ nghi gán nhầm | "Nghi không phải dân gian" — "Câu này thường được gọi là ca dao, nhưng [căn cứ]." |
| Cờ tranh luận | "Các nhà nghiên cứu còn có những cách hiểu khác nhau về [điểm]." |
| Tên bản đại diện | "Bản tham chiếu" |
| Khối nghĩa ngắn | "Hiểu trong 10 giây" |
| Sao chép trích dẫn | "Đã sao chép. Cảm ơn bạn đã ghi nguồn!" |
| Khối mức C | "Những lời dưới đây do cộng đồng gửi, chưa được đối chiếu với tư liệu. Bạn biết nguồn in của chúng? Hãy cho chúng tôi biết." |
| Lời mời đóng góp | "Ở quê bạn câu này nói khác?" |

**Từ cấm trong giao diện:** "bản gốc", "bản đúng", "bản chuẩn", "bản sai", "AI giải thích", "câu hay nhất".

## 11. Trợ năng (WCAG 2.2 AA)

- `<html lang="vi">`; tiêu đề trang theo thứ bậc đúng.
- Mọi điều khiển dùng được bằng bàn phím, viền focus rõ (`--son`), và **không bị thanh trên cố định che khi nhận focus** (WCAG 2.2, 2.4.11).
- Vùng chạm tối thiểu 44×44 px.
- Nhãn không chỉ dựa vào màu: luôn có chữ + biểu tượng.
- Ghi âm có bản chép lời; ảnh thẻ có văn bản thay thế là lời thơ.
- Tôn trọng `prefers-reduced-motion`.
- HTML ngữ nghĩa trước ARIA: `<button>`, `<dialog>`, `<details>`, `<nav>`, `<main>`; ARIA chỉ bổ sung khi HTML không đủ.
- Trợ năng nằm trong Definition of Done của mọi task (`22` mục 6), không để cuối dự án.

## 12. Ngân sách hiệu năng

| Chỉ số | Mục tiêu |
|---|---|
| LCP (4G mô phỏng, điện thoại tầm trung) | < 2,0 giây; phần lời thơ < 1,5 giây |
| CLS | < 0,05 |
| Dung lượng trang (không tính phông) | < 150 KB |
| JavaScript tải ban đầu | < 50 KB (chỉ chú giải, trích dẫn, tìm kiếm) |
| Chỉ mục tìm kiếm | Tải trễ khi người dùng chạm ô tìm |
| Trình tạo thẻ | Tải khi bấm "Tạo thẻ"; ảnh tạo trong < 2 giây |

## 13. So sánh dị bản (P1)

| ID | Yêu cầu |
|---|---|
| FR-2.1 | Chọn 2 dị bản A/B bất kỳ; mặc định so Bản tham chiếu với dị bản đầu tiên khác nó |
| FR-2.2 | So theo **âm tiết** (tiếng Việt viết tách âm tiết bằng khoảng trắng nên đây là đơn vị tự nhiên), ví dụ hàm so sánh theo từ của thư viện `diff` |
| FR-2.3 | Chỗ khác nhau đánh dấu bằng **màu + gạch chân/gạch ngang**, không chỉ bằng màu |
| FR-2.4 | Máy tính: hai cột song song. Điện thoại: xếp chồng, đồng bộ theo dòng |
| FR-2.5 | Đường dẫn chia sẻ được: `/ca-dao/{slug}/so-sanh?a=v1&b=v2`; canonical trỏ về trang tác phẩm |

Tiêu chí: hai bản giống hệt → "Hai bản giống nhau"; số dòng khác nhau vẫn so đúng; đường dẫn mở lại đúng cặp.

## 14. Danh mục thành phần

Thành phần mang tên nghiệp vụ; dựng bằng HTML ngữ nghĩa + Tailwind dùng token mục 1.1; đảo tương tác bằng TypeScript thuần với `<dialog>`/`popover` gốc (D-030).

| Thành phần | Dùng ở | Trạng thái phải xử lý | Lớp |
|---|---|---|---|
| `VerseBlock` | Trang tác phẩm, tình huống, thẻ | Ngắn / dài > 12 dòng; lục bát thụt lề; không JS | P0-Core |
| `GlossaryTerm` + `GlossSheet` | Trong `VerseBlock` | Đóng/mở; bảng trượt (điện thoại) / popover (máy tính); không JS → chú thích cuối khối | P0-Core |
| `EvidenceBadge` | Mọi nơi hiện một bài | A / B / C; cờ nghi gán nhầm; cờ tranh luận | P0-Core |
| `SourceLine` + `SourceTrail` | Trang tác phẩm | Dòng ngắn (lớp 1) / đầy đủ có lịch sử (lớp 2) | P0-Core |
| `CitationPanel` | Trang tác phẩm | Chọn định dạng; đã sao chép; chỉ A/B | P0-Core |
| `VariantList` | Trang tác phẩm | 1 dị bản / nhiều; khối C riêng | P0-Core |
| `SearchBox` + `SearchResults` | Mọi trang / `/tim` | Đang tải chỉ mục; có kết quả; nhóm tình huống/mục từ; không có kết quả | P0-Core |
| `SituationCard` / `SituationChip` | Trang chủ, tình huống, tác phẩm | Thẻ / chip | P0-Core |
| `ContentWarning` | Trang tác phẩm | Hiện khi có `canh_bao_noi_dung` | P0-Core |
| `ReportLink` | Trang tác phẩm, chân trang | Điền sẵn mã tác phẩm | P0-Core |
| `ShareCardBuilder` | Trang tác phẩm | 2 mẫu × 2 khổ; đang vẽ; không cho phép (C/D/cờ/cảnh báo) | P0.5 |
| `AudioPlayer` | Trang tác phẩm | Không tự phát; có bản chép lời | P0.5 |
| `VariantDiff` | `/so-sanh` | Giống nhau / khác nhau; số dòng khác nhau | P1 |

**Màn hình tham chiếu** (dựng thành nguyên mẫu code trong T-008/T-010, không cần bản vẽ riêng): Trang chủ · Tìm kiếm · Trang tác phẩm (máy tính) · Trang tác phẩm (điện thoại) · Trang tình huống.
