# 20 · Đặc tả giao diện

> **Lưu ý (D-071, 26/10/2026):** site Astro và CMS Sveltia đã gỡ khỏi repo. Chỗ nào nhắc `content/`, YAML, `templates/`, `config/`, Astro hay `/admin` là lịch sử; quy tắc nội dung và nguồn vẫn còn hiệu lực trên WordPress.

| | |
|---|---|
| Phiên bản | 2.0 · 23/10/2026 (UX v2: nhãn trung thực, nguồn gốc ở lớp 1, thanh điều hướng dưới, công cụ cho giáo viên) |
| Trạng thái | **Ổn định cho Mốc A–C.** Tên miền đã chốt: phongdao.com (D-070). Dòng "lời và mức kiểm" dưới lời: D-072 |
| Đọc khi | Làm bất kỳ trang, thành phần, câu chữ giao diện nào |
| Liên quan | `10` (dữ liệu hiển thị, mục 5 và 10) · `21` (cách dựng) · `23` (tiêu chí kiểm thử) · `25` (gói nâng cấp 25/10/2026 trên WordPress: chạm từ khó, dị bản, thể thơ, công cụ đọc, trang chủ, thể loại, người đọc góp phần, giọng đọc) |

Tinh thần: **"Một trang sách cổ được chăm chút, đặt trong một chiếc điện thoại hiện đại"**. Lời thơ là nhân vật chính. Mọi màn hình thiết kế cho màn hình hẹp trước.

**Nguyên tắc chi phối:** mô hình dữ liệu giàu, trải nghiệm đọc mặc định đơn giản — và **trang luôn nói thật bài này đã được kiểm đến đâu** (D-039).

---

## 1. Ngôn ngữ thị giác

- Nhiều khoảng trắng; không ảnh nền sau chữ.
- Không phủ biểu tượng văn hóa sáo mòn (trống đồng, cây tre, nón lá) khắp giao diện. Bản sắc Việt qua chữ, nhịp, khoảng trắng; điểm nhấn văn hóa tinh tế: **nhãn bằng chứng thiết kế như một con triện nhỏ** (viền mảnh, màu theo nhãn).
- Không popup, không banner, không tự phát âm thanh, không đếm ngược, không hộp "đăng ký nhận tin" chặn màn hình.
- Tiêu chí duyệt mỗi màn hình: **Chuẩn** (thông tin đúng, nói rõ mức kiểm) · **Sống** (gần đời thường) · **Đẹp** (chữ, khoảng trắng).

**Luật giao diện cho người và AI agent khi dựng trang công khai:**

| Nên | Không |
|---|---|
| Nội dung trước, ít khung viền và thanh công cụ | Thẩm mỹ bảng điều khiển/SaaS: lưới thẻ dày đặc, chỉ số, biểu đồ trang trí |
| Bề rộng đọc có giới hạn: lời thơ và văn bản tối đa ~38em, căn giữa trên màn hình rộng | Văn bản tràn hết chiều ngang màn hình lớn |
| Một hành động chính cho mỗi vùng | Nhiều nút cùng trọng số đứng cạnh nhau |
| Phân tách bằng khoảng trắng, rồi đường kẻ mảnh; bóng đổ chỉ khi thật cần | Bóng đổ, gradient, bo góc lớn làm mặc định |
| Chuyển động nhẹ, ngắn, tôn trọng `prefers-reduced-motion`; chuyển trang bằng View Transitions khi trình duyệt hỗ trợ (không hỗ trợ vẫn chạy bình thường) | Hiệu ứng trang trí, cuộn kéo dài |
| Dùng thẻ (card) cho danh sách tình huống, kết quả tìm | Bọc mọi khối nội dung trong thẻ |
| Thành phần mang tên miền nghiệp vụ (mục 14) | Giao diện trông như thư viện thành phần mặc định |

### 1.1. Màu (token)

Giá trị khởi đầu; độ tương phản với nền tính theo công thức WCAG (≥ 4,5 là đạt AA cho chữ thường). Đổi giá trị thì tính lại. Viết bằng biến CSS và `light-dark()`.

| Token | Sáng | Tương phản | Tối | Tương phản | Dùng cho |
|---|---|---|---|---|---|
| `--giay` | `#FBF8F1` | — | `#1B1A18` | — | Nền |
| `--muc` | `#22201C` | 15,3 | `#EDE6D6` | 14,0 | Chữ chính, lời thơ |
| `--son` | `#9E2A22` | 7,1 | `#E8776B` | 6,0 | Liên kết, điểm nhấn, viền focus |
| `--nhat` | `#5F594F` | 6,5 | `#ABA496` | 7,0 | Chữ phụ, nguồn |
| `--nhan-a` | `#3F5A2C` | 7,3 | `#9CC07F` | 8,5 | Nhãn "Đã đối chiếu tư liệu" |
| `--nhan-b` | `#2B5580` | 7,3 | `#8DB6E3` | 8,2 | Nhãn "Ghi từ người kể" |
| `--nhan-c` | `#7A5A12` | 6,0 | `#D9B56A` | 8,9 | Nhãn "Cộng đồng gửi" |
| `--nhan-d` | `#665A48` | 6,3 | `#B8AB93` | 7,7 | Nhãn "Lời phổ biến – chưa đối chiếu" (mới ở v2, đo 23/10/2026) |
| `--nhan-co` | `#5E5E5E` | 6,1 | `#B0B0B0` | 8,0 | Nhãn "Nghi không phải dân gian" |

Chế độ tối theo cài đặt hệ thống (`prefers-color-scheme`) có từ Mốc A; **nút đổi sáng/tối** (◐) có ở Mốc C, lựa chọn lưu ở `localStorage` (tùy chọn giao diện, không phải theo dõi).

### 1.2. Chữ

| Vai trò | Phông | Cỡ trên điện thoại | Ghi chú |
|---|---|---|---|
| Lời thơ | Phông có chân hỗ trợ tốt tiếng Việt (ví dụ Literata hoặc Noto Serif) | ≥ 22 px, giãn dòng 1,7 | |
| Giao diện, chú giải, nghĩa | Be Vietnam Pro | ≥ 17 px (chữ phụ ≥ 15 px), giãn dòng ≥ 1,5 | Có người đọc lớn tuổi; không khóa phóng to trình duyệt |

- Tự lưu trữ phông (Fonts API của Astro, cấu hình trong `astro.config.mjs`), chỉ bộ ký tự Latin + tiếng Việt, WOFF2, `font-display: swap`.
- Kiểm dấu chồng ("ẫ", "ỡ", "ự", "Ngưỡng", "Thuở", "Quyện") ở cỡ nhỏ và lớn.
- `text-wrap: balance` cho tiêu đề, `text-wrap: pretty` cho đoạn văn; không tự xuống dòng giữa dòng thơ.
- Lục bát: mặc định thụt lề câu lục so với câu bát, đặt bằng một biến cấu hình (`LUC_BAT_THUT_LE` trong `src/lib/cau-hinh.ts`; Q-006, hỏi 5 người thử ở Mốc B).
- **Nút cỡ chữ "Aa"** (Mốc C): ba mức, nhớ trên máy — cho người đọc lớn tuổi.

## 2. Sơ đồ trang

```text
/                               Trang chủ
├── /tim?q=…                    Kết quả tìm kiếm
├── /ca-dao/{slug}              Trang tác phẩm  (tương tự /tuc-ngu/, /thanh-ngu/)
├── /tinh-huong/ , /tinh-huong/{slug}
├── /tu-dien/ , /tu-dien/{slug}
├── /da-luu                     Đã lưu (trên máy, Mốc C)
├── /phuong-phap , /nguon , /ve-du-an , /quyen-rieng-tu
├── /dong-gop                   Trang giới thiệu + liên kết form
└── (Sau) /ngau-nhien, /a-z, /chu-de/{slug}, /ca-dao/{slug}/so-sanh, /du-lieu-mo
```

- **Thanh trên (điện thoại):** tên dự án · (Mốc C) Aa · ◐. Gọn, không che focus.
- **Thanh điều hướng dưới (điện thoại), trong tầm ngón cái:** 🔍 Tìm · ✦ Tình huống · ☆ Đã lưu · ≡ Thêm (Từ điển, Phương pháp, Đóng góp, Về dự án). Thay menu ba gạch.
- **Máy tính:** thanh trên có ô tìm và các mục trên; không có thanh dưới.
- **Chân trang:** Phương pháp · Nguồn · Quyền riêng tư · Về dự án · Liên hệ.

## 3. Trang chủ

Thứ tự khối:
1. Câu định vị "Đọc một câu. Hiểu một đời sống." + ô tìm lớn, gợi ý trong ô: "Gõ câu bạn nhớ, hoặc điều bạn đang cảm thấy…"
2. **"Hôm nay bạn đang…"**: 8–12 thẻ tình huống theo `mac_dinh` trong `config/lich-tinh-huong.yaml` (Mốc B); theo dịp ở "Sau" (T-061).
3. **"Câu hôm nay"** (Mốc C): một bài xoay vòng theo ngày, chọn lúc build.
4. **"Vừa được bổ sung"** (Mốc C): 5 bài mới công khai hoặc vừa được nâng nhãn — cho thấy kho đang được làm tiếp.
5. Một đoạn ngắn: lời hứa "Mỗi câu ghi rõ lấy từ đâu, và đã được kiểm đến mức nào" + liên kết `/phuong-phap`.
6. Lời mời đóng góp: "Bạn có sách in? Giúp chúng tôi đối chiếu."

## 4. Trang tác phẩm

Mục tiêu: trong 10 giây đầu, người dùng thấy lời thơ đẹp, biết lời lấy từ đâu và đã kiểm đến đâu, hiểu được từ khó mà không rời trang.

### 4.1. Đọc theo lớp

**Lớp 1 — mở sẵn, theo đúng thứ tự** (đủ để đọc, hiểu, biết nguồn gốc, trích dẫn):
1. **Nhãn thể loại + nhãn bằng chứng** (chữ + biểu tượng, tính bằng `tinhNhan()`). Có cờ: nhãn xám "Nghi không phải dân gian" hoặc dòng "Đang có tranh luận". Có `canh_bao_noi_dung`: khung cảnh báo ngay dưới lời.
2. **Lời Bản tham chiếu.** Từ có chú giải gạch chân chấm; chạm là hiện (mục 5).
3. **Hành động:** **Sao chép trích dẫn** (hành động chính) · Chia sẻ · Lưu (Mốc C) · Trình chiếu (Mốc C).
4. **"Hiểu trong 10 giây"**: `nghia_ngan` (qua `chuoiHienThi()`; không có thì ẩn khối).
5. **Khối Nguồn gốc** — luôn hiện, đây là khác biệt cốt lõi của dự án:
   - Nhãn A: "Đối chiếu theo: *[sách]*, tr. [x]".
   - Nhãn D từ trang web: "Lời tham khảo từ *Ca dao Mẹ* (cadao.me) ↗, truy cập [ngày]. Chưa đối chiếu với sách in." + liên kết **"Bạn có sách? Gửi số trang"**.
   - Liên kết tới khối Nguồn và lịch sử đầy đủ (lớp 2).
6. **Dị bản:** "Có N dị bản" + vùng của từng dị bản; chạm để mở danh sách (chỉ dị bản `diBanHienThi()` cho phép).

**Lớp 2 — gọn, chạm để mở** (mặc định thu gọn, trừ dòng thẻ tình huống):
7. **"Dùng khi…"**: thẻ các tình huống chứa bài (một dòng thẻ nhỏ).
8. **Nghĩa và các cách hiểu** (`dien_giai`; tục ngữ có nghĩa đen/bóng, góc kiểm chứng).
9. **Bối cảnh diễn xướng.**
10. **Âm luật.**
11. **Nguồn và lịch sử**: mọi nguồn (số trang hoặc URL + ngày truy cập + bản lưu trữ); người biên soạn; **"Cập nhật lần cuối [ngày]"** và liên kết lịch sử sửa đổi trên GitHub.
12. **Cộng đồng ghi nhận** (dị bản C), với câu "chưa được đối chiếu".
13. **"Xem thêm chú thích tại Ca dao Mẹ ↗"** khi bài lấy từ Ca dao Mẹ.
14. Dòng nhỏ khi bài dùng nháp AI đã duyệt: **"Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt."**
15. Bài còn thiếu phần (không có nghĩa ngắn, chú giải hoặc tình huống): **"Bài đang được bổ sung. Bạn biết thêm? Góp ý"**.
16. Lời mời: **"Ở quê bạn câu này nói khác?"** → form (điền sẵn mã).

Khối không có dữ liệu thì ẩn hẳn, không hiện tiêu đề rỗng. Thu gọn dùng `<details>`/`<summary>` để không cần JavaScript. Không bao giờ để lộ chuỗi `CAN_BO_SUNG` hay tiền tố `NHAP_AI`.

### 4.2. Khung trang (điện thoại)

```text
┌─────────────────────────────────┐
│ Kho tàng Ca dao            Aa ◐ │
├─────────────────────────────────┤
│ CA DAO · ○ Lời phổ biến –       │
│           chưa đối chiếu      ⓘ │
│                                 │
│   [lời bản tham chiếu, dòng 1]  │
│ [dòng 2 — từ khó gạch chân chấm]│
│   [dòng 3]                      │
│ [dòng 4]                        │
│                                 │
│ [❝ Sao chép trích dẫn]  ↗  ☆  ⛶ │
├─────────────────────────────────┤
│ Hiểu trong 10 giây              │
│ [nghia_ngan]                    │
│ Lời tham khảo từ Ca dao Mẹ ↗,   │
│ truy cập [ngày]. Chưa đối chiếu │
│ với sách in. Bạn có sách? ›     │
│ 2 dị bản · Bắc Bộ · Nam Bộ     ›│
├─────────────────────────────────┤  ← hết lớp 1
│ Dùng khi: (tình huống) (…)      │
│ ▸ Nghĩa và các cách hiểu        │
│ ▸ Nguồn và lịch sử · cập nhật … │
├─────────────────────────────────┤
│ Ở quê bạn câu này nói khác?     │
├─────────────────────────────────┤
│  🔍 Tìm  ✦ Tình huống ☆ Đã lưu ≡│  ← thanh dưới
└─────────────────────────────────┘
```
(Lời trong khung chỉ minh họa bố cục.)

### 4.3. Yêu cầu chức năng

| ID | Yêu cầu |
|---|---|
| FR-1.1 | Lời hiển thị đúng từng dòng của dị bản; không tự xuống dòng giữa chữ có dấu |
| FR-1.2 | Từ có chú giải là `<button>` có gạch chân chấm; chạm/nhấn mở ô chú giải; điện thoại: bảng trượt từ dưới lên (tối đa ~45% chiều cao, không che dòng đang đọc); máy tính: popover |
| FR-1.3 | Ô chú giải: từ, loại, giải nghĩa (hoặc `nghia_tai_cho`), nguồn ("Tham khảo: …" / "Theo …"), liên kết "Xem thêm" tới `/tu-dien/{slug}` |
| FR-1.4 | Tắt JavaScript vẫn đọc được: chú giải hiện dạng chú thích cuối khối lời, liên kết neo hai chiều |
| FR-1.5 | Nhãn bằng chứng luôn có chữ + biểu tượng; chạm vào hiện câu giải thích (mục 10) và liên kết `/phuong-phap` |
| FR-1.7 | Mỗi trang có JSON-LD (`21` mục 8) |
| FR-1.8 | Lớp 1 (mục 4.1) hiện đầy đủ trong khung nhìn đầu tiên và màn hình kế tiếp trên điện thoại 360 px với bài ≤ 8 dòng |
| FR-1.9 | Khối Nguồn gốc luôn hiện ở lớp 1, với mọi nhãn; bài nhãn D luôn có câu "Chưa đối chiếu với sách in" và lối "Gửi số trang" |
| FR-1.10 | Trang của bài chưa có giá trị riêng (`coGiaTriRieng()` sai) mang `<meta name="robots" content="noindex">` và không vào sitemap |
| FR-1.11 | Bản in (`@media print`): chỉ lời, nghĩa ngắn, nguồn gốc, URL; không thanh điều hướng, không nút |
| FR-1.12 | Thanh điều hướng dưới không che phần tử đang nhận focus (WCAG 2.2, 2.4.11); ẩn khi in |

## 5. Chú giải — hành vi

- Mở: chạm / Enter / Space. Đóng: chạm ra ngoài / Esc / nút Đóng. Đóng xong trả focus về từ vừa chạm.
- Một ô mở tại một thời điểm.
- ARIA: nút có `aria-expanded`, `aria-controls`; ô là `role="dialog"` có nhãn (điện thoại) hoặc vùng liên kết (máy tính).
- Ghi sự kiện `chu_giai_mo` (mã mục từ, Mốc C).
- (Sau) Nút "Hiện nghĩa từ khó ngay dưới chữ" cho học sinh và khi trình chiếu.

## 6. Trích dẫn

Nút Trích dẫn mở hộp chọn 2 định dạng và **hai nút**: **"Sao chép kèm nguồn"** (mặc định, dán vào giáo án) và **"Chỉ sao chép lời"**; thông báo "Đã sao chép. Cảm ơn bạn đã ghi nguồn!". Ngày truy cập tự điền theo ngày bấm.

**Trên WordPress (T-042, viết lại theo D-054, D-057):** không còn nhãn bằng chứng; lời không ghi nguồn ngoài, "nguồn" của trích dẫn là trang của site. "Sao chép kèm nguồn" chép lời (khối Thơ đầu tiên) rồi trích dẫn theo kiểu đã chọn; "Chỉ sao chép lời" chép lời. Bài có đáp án, chú thích chép từ nguồn thì có ô "Kèm đáp án / chú thích": chép thêm phần đó và dòng "Nguồn: …" của nó.

**Kiểu thông dụng:**
```text
"<tiêu đề>" (<thể loại>). Kho tàng Ca dao Tục ngữ Việt Nam. Truy cập tại: <URL>, ngày <dd/mm/yyyy>.
```
**Kiểu APA 7 (điều chỉnh cho tư liệu dân gian; năm = năm đăng bài):**
```text
Kho tàng Ca dao Tục ngữ Việt Nam. (<năm>). <Tiêu đề> [<Thể loại>]. <URL>
```
Hộp Trích dẫn là `<details>`: tắt JavaScript vẫn mở được và chọn chữ tay; các nút cần JavaScript thì ẩn. BibTeX ở "Sau".

*Bản cho site Astro cũ (nhãn A/D, đối chiếu sách in) thay bởi phần trên khi chuyển WordPress (D-056).*

## 7. Chia sẻ và ảnh xem trước

Mục tiêu: mỗi lần chia sẻ đưa một trích dẫn **có ghi nguồn gốc** ra mạng xã hội. Zalo và Facebook là kênh chính ở Việt Nam.

| ID | Yêu cầu | Mốc |
|---|---|---|
| FR-4.1 | Nút **Chia sẻ** dùng Web Share API (văn bản = lời + "— <tiêu đề> (<thể loại>) · Kho tàng Ca dao Tục ngữ Việt Nam", kèm URL; D-057: không ghi nguồn ngoài cho lời) — hiện Zalo, Messenger… có sẵn trên máy; không hỗ trợ thì sao chép link | A |
| FR-4.2 | **Ảnh xem trước riêng từng bài** 1200×630 sinh lúc build: lời (trích tối đa 4 dòng), thể loại, **dòng mức kiểm** (D-072), tên miền + đường dẫn bài. Dán link vào Zalo/Facebook là thấy | B (T-050, D-073) |
| FR-4.3 | Không có ảnh xem trước dạng bài riêng cho bài mang cờ nghi gán nhầm/phỏng tác hoặc có `canh_bao_noi_dung` (dùng ảnh chung toàn site) | C |
| FR-4.4 | (Sau) Trình tạo thẻ ảnh 2 mẫu ("Giấy dó", "Mực tàu") × 2 khổ (1080×1350, 1080×1920); **nhãn và dòng nguồn gốc không tắt được**; không cho dị bản C, bài có cờ hoặc cảnh báo; tạo trên trình duyệt bằng Canvas, phông nạp trước bằng `FontFace` | Sau |

## 8. Tìm kiếm

- Một ô duy nhất ở đầu mọi trang (điện thoại: nút 🔍 ở thanh dưới mở ô tìm); trang kết quả `/tim?q=…`.
- Kết quả cập nhật theo phím gõ sau 150 ms; khớp cả dòng giữa bài; dùng được hoàn toàn bằng bàn phím.
- Mỗi kết quả: dòng đầu (in đậm phần khớp), thể loại, nhãn bằng chứng, `nghia_ngan`. Tối đa 20, có "Xem thêm".
- Kết quả trang tình huống và mục từ hiện thành nhóm riêng phía trên khi khớp mạnh ("Tình huống: Khi nhớ mẹ").
- Nhớ vài lần tìm gần đây trên máy (`localStorage`, bọc try/catch).
- **Không có kết quả:** "Chưa tìm thấy câu này trong kho. Có thể bạn muốn xem những tình huống dưới đây, hoặc gửi cho chúng tôi câu bạn đang tìm." + 6 tình huống phổ biến + liên kết form. (Sau, T-064: thêm "Có thể bạn tìm: [dòng đầu] — xem tại Ca dao Mẹ ↗" từ kho thô, khi người sáng lập đồng ý.)
- Không bao giờ hiển thị văn bản do AI sinh ra mà chưa duyệt; "lý do khớp" chỉ là nhãn hoặc câu "Hợp khi…" do người duyệt.

## 9. Các trang khác

| Trang | Nội dung | Mốc |
|---|---|---|
| Tình huống | Tiêu đề, `mo_dau`, danh sách bài (lời rút gọn ≤ 4 dòng + "Hợp khi…") | B — chỉ tạo khi ≥ 2 bài công khai |
| Từ điển `/tu-dien/{slug}` | Từ, loại, giải nghĩa đầy đủ, nguồn ("Tham khảo: Ca dao Mẹ ↗"), mọi bài có từ này | B |
| Phương pháp | Giải thích 4 nhãn, lời hứa, cách dùng AI, ghi công Ca dao Mẹ, cách "Gửi số trang" | B |
| Nguồn | Danh sách nguồn đang được dùng, sinh từ `content/nguon/` | B |
| Đã lưu `/da-luu` | Danh sách bài đã lưu trên máy; sao chép cả danh sách kèm nguồn | C |
| Trình chiếu | Toàn màn hình, nền tối hoặc sáng, chữ rất lớn, phím mũi tên sang bài kế; thoát bằng Esc | C |
| Ngẫu nhiên, A–Z, chủ đề, thể loại | — | Sau |

## 10. Câu chữ giao diện

| Vị trí | Nội dung |
|---|---|
| Ô tìm | "Gõ câu bạn nhớ, hoặc điều bạn đang cảm thấy…" |
| Nhãn A | "✓ Đã đối chiếu tư liệu" — giải thích: "Lời này đã được đối chiếu với [tên sách], trang [x]." |
| Nhãn B | "◉ Ghi từ người kể" — "Lời này được ghi lại trực tiếp từ người kể ở [vùng]." |
| Nhãn C | "◌ Cộng đồng gửi" — "Lời này do cộng đồng gửi, chưa được đối chiếu với tư liệu." |
| Nhãn D | "○ Lời phổ biến – chưa đối chiếu" — "Lời này được lưu truyền rộng rãi và lấy từ [nguồn]. Chúng tôi chưa đối chiếu với sách in. Bạn có sách? Hãy gửi số trang." |
| Cờ nghi gán nhầm | "Nghi không phải dân gian" — "Câu này thường được gọi là ca dao, nhưng [căn cứ]." |
| Cờ tranh luận | "Các nhà nghiên cứu còn có những cách hiểu khác nhau về [điểm]." |
| Nguồn gốc (web) | "Lời tham khảo từ [tên trang] ↗, truy cập [ngày]. Chưa đối chiếu với sách in." |
| Ghi công chú thích | "Tham khảo: [nguồn] ↗" (viết lại) · "Theo [nguồn] ↗" (trích nguyên văn) |
| Hỗ trợ AI | "Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt." |
| Bài còn thiếu | "Bài đang được bổ sung. Bạn biết thêm? Góp ý" |
| Tên bản đại diện | "Bản tham chiếu" |
| Khối nghĩa ngắn | "Hiểu trong 10 giây" |
| Sao chép trích dẫn | "Đã sao chép. Cảm ơn bạn đã ghi nguồn!" |
| Khối mức C | "Những lời dưới đây do cộng đồng gửi, chưa được đối chiếu với tư liệu. Bạn biết nguồn in của chúng? Hãy cho chúng tôi biết." |
| Lời mời đóng góp | "Ở quê bạn câu này nói khác?" · "Bạn có sách? Gửi số trang" |

**Từ cấm trong giao diện:** "bản gốc", "bản đúng", "bản chuẩn", "bản sai", "AI giải thích", "câu hay nhất". Không dùng "mức A/B" trên giao diện — chỉ dùng tên nhãn bằng lời thường.

## 11. Trợ năng (WCAG 2.2 AA)

- `<html lang="vi">`; tiêu đề trang theo thứ bậc đúng.
- Mọi điều khiển dùng được bằng bàn phím, viền focus rõ (`--son`), và **không bị thanh trên/thanh dưới cố định che khi nhận focus** (2.4.11).
- Vùng chạm tối thiểu 44×44 px.
- Nhãn không chỉ dựa vào màu: luôn có chữ + biểu tượng.
- Ảnh xem trước có văn bản thay thế là lời thơ.
- Tôn trọng `prefers-reduced-motion`.
- HTML ngữ nghĩa trước ARIA: `<button>`, `<dialog>`, `<details>`, `<nav>`, `<main>`; ARIA chỉ bổ sung khi HTML không đủ.
- Trợ năng nằm trong Definition of Done của mọi task (`22` mục 5), không để cuối dự án.

## 12. Ngân sách hiệu năng

| Chỉ số | Mục tiêu |
|---|---|
| LCP (4G mô phỏng, điện thoại tầm trung) | < 2,0 giây; phần lời thơ < 1,5 giây |
| CLS | < 0,05 |
| Dung lượng trang (không tính phông) | < 150 KB |
| JavaScript tải ban đầu | < 50 KB (chú giải, trích dẫn, chia sẻ, tìm kiếm, lưu) |
| Chỉ mục tìm kiếm | Tải trễ khi người dùng chạm ô tìm |
| Trình chiếu, trình tạo thẻ (Sau) | Tải khi bấm |

## 13. So sánh dị bản (Sau, T-063)

| ID | Yêu cầu |
|---|---|
| FR-2.1 | Chọn 2 dị bản A/B bất kỳ; mặc định so Bản tham chiếu với dị bản đầu tiên khác nó |
| FR-2.2 | So theo **âm tiết** (tiếng Việt viết tách âm tiết bằng khoảng trắng nên đây là đơn vị tự nhiên) |
| FR-2.3 | Chỗ khác nhau đánh dấu bằng **màu + gạch chân/gạch ngang**, không chỉ bằng màu |
| FR-2.4 | Máy tính: hai cột song song. Điện thoại: xếp chồng, đồng bộ theo dòng |
| FR-2.5 | Đường dẫn chia sẻ được: `/ca-dao/{slug}/so-sanh?a=v1&b=v2`; canonical trỏ về trang tác phẩm |

## 14. Danh mục thành phần

Thành phần mang tên nghiệp vụ; dựng bằng HTML ngữ nghĩa + Tailwind dùng token mục 1.1; đảo tương tác bằng TypeScript thuần với `<dialog>`/`popover` gốc (D-030). Dữ liệu suy ra lấy từ `scripts/lib/suy-ra.mjs`.

| Thành phần | Dùng ở | Trạng thái phải xử lý | Mốc |
|---|---|---|---|
| `VerseBlock` | Trang tác phẩm, tình huống, trình chiếu | Ngắn / dài > 12 dòng; lục bát thụt lề; không JS; bản in | A |
| `GlossaryTerm` + `GlossSheet` | Trong `VerseBlock` | Đóng/mở; bảng trượt / popover; không JS → chú thích cuối khối; nguồn "Tham khảo/Theo" | A |
| `EvidenceBadge` | Mọi nơi hiện một bài | A / B / C / D; cờ nghi gán nhầm; cờ tranh luận | A |
| `ProvenanceBlock` + `SourceTrail` | Trang tác phẩm | Sách có trang / web có URL + ngày / người kể / đóng góp; lối "Gửi số trang"; đầy đủ có lịch sử (lớp 2) | A |
| `CitationPanel` | Trang tác phẩm | Hai định dạng; "kèm nguồn" / "chỉ lời"; đã sao chép; câu chữ nhãn D | A |
| `ShareButton` | Trang tác phẩm | Web Share có / không | A |
| `VariantList` | Trang tác phẩm | 1 dị bản / nhiều; khối C riêng | A |
| `BottomNav` | Mọi trang (điện thoại) | Mục đang chọn; không che focus; ẩn khi in | A |
| `SearchBox` + `SearchResults` | Mọi trang / `/tim` | Đang tải chỉ mục; có kết quả; nhóm tình huống/mục từ; không có kết quả | A |
| `AiNote`, `PartialNotice` | Trang tác phẩm | Hiện khi `nhap_ai_da_duyet` / khi bài còn thiếu phần | A |
| `ContentWarning` | Trang tác phẩm | Hiện khi có `canh_bao_noi_dung` | A |
| `SituationCard` / `SituationChip` | Trang chủ, tình huống, tác phẩm | Thẻ / chip | B |
| `ReportLink` | Trang tác phẩm, chân trang | Điền sẵn mã tác phẩm; loại "báo lỗi" / "dị bản" / "gửi số trang" | B |
| `SaveButton` + trang Đã lưu | Trang tác phẩm, `/da-luu` | Đã lưu / chưa; bộ nhớ trình duyệt bị chặn vẫn chạy | C |
| `PresentMode` | Trang tác phẩm, Đã lưu | Vào/thoát; sang bài kế | C |
| `TextSizeControl`, `ThemeToggle` | Thanh trên | Ba cỡ chữ; sáng/tối/theo hệ thống | C |
| `ShareCardBuilder` | Trang tác phẩm | 2 mẫu × 2 khổ; không cho phép (C/cờ/cảnh báo) | Sau |
| `VariantDiff` | `/so-sanh` | Giống nhau / khác nhau | Sau |

**Màn hình tham chiếu** (dựng thành nguyên mẫu code ở T-040, không cần bản vẽ riêng): Trang chủ · Tìm kiếm · Trang tác phẩm nhãn A và nhãn D (điện thoại, máy tính) · Trang tình huống.
