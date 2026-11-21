# 54 · Kiến trúc đích và phương án chuyển đổi

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Căn cứ (D-127, 21/11/2026)** — kế hoạch đang dùng: [57](57-ke-hoach-chot.md); nơi khác nhau, 57 thắng. Nguyên trạng lúc lập: **Đề xuất, chờ người sáng lập duyệt** ([56](56-goi-quyet-dinh.md) nhóm C; Human Gate H1, H6). Thay phần kiến trúc của [46](46-kien-truc-va-khuyen-nghi.md) |
| Bộ tài liệu | [50 tầm nhìn](50-tai-dinh-vi-chien-luoc.md) · [51 rà soát quyết định](51-danh-gia-lai-quyet-dinh.md) · [52 chất lượng nội dung](52-chien-luoc-chat-luong-noi-dung.md) · [53 trải nghiệm](53-thiet-ke-lai-trai-nghiem.md) · **54 kiến trúc** · [55 lộ trình, backlog](55-lo-trinh-va-backlog.md) · [56 gói quyết định](56-goi-quyet-dinh.md) |
| Đọc kèm | Hiện trạng kỹ thuật: [45](45-kiem-ke-va-doi-chieu.md) mục 2; `docs/26` |

---

## 1. Yêu cầu từ tầm nhìn mới

| # | Yêu cầu | Đo bằng |
|---|---|---|
| Y1 | Quan hệ **có kiểu, có bằng chứng, có trạng thái** (máy gợi ý / người duyệt) giữa tác phẩm, hồ sơ thực thể, bài viết, ảnh / âm thanh, nguồn ngoài; truy vấn được hai chiều | Một bài đọc ra mọi quan hệ đã duyệt trong ≤ 2 truy vấn có chỉ mục |
| Y2 | **Hồ sơ thực thể** (nhân vật, địa danh, điển tích, sự kiện) có trang riêng, văn bản dài, nguồn, toạ độ, tên khác | Lập chỉ mục được; sửa được trên điện thoại |
| Y3 | **Bài viết** (khảo cứu, bình giảng, chuyên đề) có người viết, biên tập, thẩm định, thư mục, nối nhiều tác phẩm | Nối hai chiều; đúng luật quyền (D-126) |
| Y4 | **Họ dị bản** giữa các bài đã đăng; gộp có xem trước, có hoàn tác | Không mất đường dẫn, lượt đọc, phần người làm |
| Y5 | **Xưởng biên tập**: vai, hàng duyệt theo vai và hạng, kiểm mẫu, ghi vết AI | 5–10 người duyệt cùng lúc không đè nhau |
| Y6 | Quyền ảnh, âm thanh có cấu trúc; đồng ý ghi âm | Kênh tái dùng lọc được bằng máy |
| Y7 | Kênh ngoài trang đọc (dữ liệu mở, khung nhúng, API, Play) đi qua một lớp lọc | Kiểm thử khẳng định không lộ phần chưa có quyền |
| Y8 | Hiệu năng ở 16.000–100.000 bài, 150.000 quan hệ | Ngân sách mục 7, đo trên trang thử |
| Y9 | Bảo trì được bởi agent và nhóm nhỏ | Không plugin nội dung bên ngoài; một nơi khai báo mỗi loại dữ liệu; kiểm thử PHP thuần + Playground |
| Y10 | Không đổi đường dẫn tác phẩm; mọi thay đổi có đường quay lại | 301 cho mọi đường đổi; công tắc cho mọi luồng đọc mới |

## 2. Ba phương án tổng thể

| Tiêu chí | **A · Kế thừa tối thiểu** (như 46) | **B · Mở rộng có cấu trúc trong WordPress** (khuyến nghị) | **C · Tách dịch vụ** (giao diện headless, cơ sở dữ liệu đồ thị, máy tìm ngoài) |
|---|---|---|---|
| Cách làm | Thêm loại cho mục từ, danh sách tác phẩm trên bài viết; quan hệ nằm rải trong meta | Một bảng quan hệ; loại bài hồ sơ; cấu trúc bài viết; mở rộng bảng việc, bảng tóm tắt | Giao diện riêng (Astro / Next.js) đọc REST; Neo4j hoặc tương tự; Meilisearch / Typesense |
| Đáp ứng Y1–Y7 | Một phần: Y1 kém (không có kiểu, bằng chứng, trạng thái chung); Y2 gượng ép; Y4 không | Đủ | Đủ |
| Công sức | S–M | M–L (khoảng 10–14 tuần agent cho cả nhóm) | XL (viết lại giao diện, đồng bộ dữ liệu, vận hành thêm dịch vụ) |
| Di chuyển dữ liệu | Không | Nhỏ, theo bước mở rộng – chuyển – thu hẹp | Lớn, hai nơi lưu |
| Hiệu năng | Tốt ở hiện tại; truy vấn ngược qua meta chậm khi nhiều quan hệ | Tốt: chỉ mục hai chiều, đệm theo bài | Rất tốt nhưng không cần ở quy mô này |
| Bảo trì | Dễ lúc đầu; nợ kỹ thuật tăng theo mỗi loại quan hệ mới | Một bảng, một danh mục quan hệ, một cửa ghi | Khó cho một nhóm nhỏ; thêm máy chủ, theo dõi, bảo mật |
| Quay lại | Dễ | Dễ (công tắc, bước mở rộng không phá dữ liệu cũ) | Khó |
| Kết luận | Không đủ cho tầm nhìn mới | **Chọn** | Không làm; xét lại khi có số đo vượt ngưỡng (mục 7) |

## 3. Mô hình đích (phương án B)

```text
              ho_so_nguon (sách, web, người kể, wiki) ◄── bằng chứng của quan hệ, nguồn của từng phần
                                   ▲
 Tuyển tập ──mục──►  TÁC PHẨM (tac_pham)  ◄─────────────┐
 (giữ meta)          lời · dị bản (có mã riêng)          │
                     nghĩa · chú giải ──► Mục từ (Từ điển: nghĩa của chữ)
                     đoạn · bối cảnh · media             │       │ la_ho_so
                            │                            │       ▼
                            │ phân loại (giữ):           │   HỒ SƠ THỰC THỂ (ho_so) [MỚI]
                            │ thể loại · chủ đề · vùng ·  │   nhân vật · địa danh · điển tích · sự kiện
                            │ mô-típ · hình thức · trò chơi
                            ▼                            │
              wp_cadao_lien_ket [MỚI] — quan hệ có kiểu, bằng chứng, trạng thái
                 tác phẩm ⇄ tác phẩm (họ dị bản, lời đáp, gần / trái nghĩa)
                 tác phẩm → hồ sơ (nhắc tới, dùng điển tích, ghi nhận tại)
                 bài viết → tác phẩm / hồ sơ (bàn về)   media → tác phẩm / hồ sơ (minh hoạ, tư liệu)
                 mọi loại → nguồn ngoài (đọc thêm)
                            ▲
              BÀI VIẾT (post) [CẤU TRÚC MỚI] — khảo cứu · bình giảng · chuyên đề; /bai-viet/<tên>/
```

**Nguyên tắc phân vai:**

- Phân loại (thể loại, chủ đề, vùng, mô-típ, hình thức, trò chơi, mục từ) **giữ là taxonomy** — đó là các mặt lọc.
- Quan hệ có ý nghĩa riêng, cần bằng chứng hay người duyệt thì **vào bảng quan hệ**.
- Nội dung dài có trang riêng (hồ sơ, bài viết) là **loại bài**.

## 4. Thành phần

### 4.1. Tác phẩm và bản lời — giữ, thêm mã dị bản

- Giữ toàn bộ phần của bài trong post meta (D-094).
- Thêm **mã ổn định** cho từng mục `di_ban`, để quan hệ, trích dẫn, đối chiếu trỏ đúng một bản. Mục cũ được gán mã ở một bước nâng cấp chạy lại được.
- Dữ liệu cũ không đổi.

### 4.2. Bảng quan hệ `wp_cadao_lien_ket`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | BIGINT UNSIGNED, khoá chính | |
| `tu_loai`, `tu_id` | VARCHAR(20), BIGINT | `tac_pham` · `ho_so` · `bai_viet` · `media` · `muc_tu` |
| `quan_he` | VARCHAR(32) | Mã trong danh mục `MienDuLieu\QuanHe` (mục 5) |
| `den_loai`, `den_id` | VARCHAR(20), BIGINT | Như trên, thêm `ho_so_nguon` cho "đọc thêm" |
| `thu_tu` | SMALLINT | Sắp xếp khi hiển thị |
| `ghi_chu` | VARCHAR(300) | "Vì sao liên quan" — người viết |
| `bang_chung` | JSON (LONGTEXT) | `{ho_so, trang?, url?, ghi_chu?}`; bắt buộc với một số loại quan hệ |
| `trang_thai` | VARCHAR(10) | `goi_y` (máy, không công khai) · `duyet` · `bac` |
| `nguoi_tao`, `tao_luc`, `duyet_boi`, `duyet_luc` | | Máy hay người; ai duyệt |
| `phien` | INT | Chống ghi đè như bảng việc |

- **Chỉ mục:** `(tu_loai, tu_id, quan_he, trang_thai)`, `(den_loai, den_id, quan_he, trang_thai)`; khoá duy nhất `(tu_loai, tu_id, quan_he, den_loai, den_id)`.
- Quan hệ đối xứng lưu một dòng theo thứ tự chuẩn (ID nhỏ trước).
- **Cửa ghi duy nhất** `DichVu\LienKet` (kiểu `LuuPhan`):
  - kiểm danh mục, quyền, bằng chứng bắt buộc, lằn ranh (máy chỉ ghi `goi_y`);
  - ghi nhật ký vào `wp_cadao_viec` để có Lịch sử và Hoàn tác;
  - xoá đệm của các bài liên quan.
- Trang người đọc chỉ đọc `duyet`. Không REST mặc định. REST biên tập có quyền và nonce.
- **Thay thế:**
  - phần `gan_nghia` → quan hệ `gan_nghia`, `trai_nghia` (mục 6, M2–M7);
  - `boi_canh.nhan_vat` → quan hệ `nhac_toi` tới hồ sơ nhân vật;
  - danh sách tác phẩm của bài viết → `ban_ve`.
  - `tuyen_tap.muc` **giữ** trong meta: danh sách có thứ tự, nhóm, ghi chú; chỉ thuộc một tuyển tập.

### 4.3. Hồ sơ thực thể — hai cách, khuyến nghị B2

| | **B1 · Mở rộng mục từ** (taxonomy `muc_tu` thêm loại, văn bản dài trong term meta) | **B2 · Loại bài `ho_so`** (khuyến nghị) |
|---|---|---|
| Ý | Một kho cho cả nghĩa của chữ và thực thể | Tách **Từ điển** (nghĩa của chữ, hàng nghìn mục ngắn) khỏi **Hồ sơ** (thực thể, vài trăm đến vài nghìn trang dài) |
| Trang | `/tu-dien/<tên>/` cho cả nhân vật, nơi chốn (tên đường dẫn lệch nghĩa) | `/nhan-vat/<tên>/`, `/dia-danh/<tên>/`, `/dien-tich/<tên>/`, `/su-kien/<tên>/` (một loại bài, đường dẫn theo loại — như tác phẩm theo thể loại) |
| Văn bản dài, ảnh, nguồn | Gượng: term meta, không có trình soạn tốt | Trình soạn khối cho thân bài; trường có cấu trúc ở cột bên; dùng chung lối sửa với bài viết |
| Nối với bài | Qua gắn term (rẻ) | Qua bảng quan hệ (`nhac_toi`, `dung_dien_tich`, `ghi_nhan_tai`) — có kiểu, có bằng chứng |
| Công sức | M | M–L |
| Rủi ro | Lẫn hai khái niệm; khó mở rộng | Thêm một loại bài phải giữ (sơ đồ trang, tìm, quyền) |

**Trường của `ho_so`** (khai báo trong danh mục phần, cùng cách `MienDuLieu\Phan`):

- `loai`;
- `ten_khac` (danh sách);
- `tom_tat` (≤ 80 chữ, người viết, nguồn);
- thân bài (trình soạn khối);
- `thoi_gian` (chữ, kèm độ chính xác);
- `toa_do` (vĩ độ, kinh độ, nguồn, độ tin cậy);
- `wikidata` (đã xác nhận);
- `anh` (tệp + quyền);
- `nguon` (danh sách);
- `tham_dinh` (người, ngày).

**Quan hệ với Từ điển:** mục từ dẫn sang hồ sơ qua quan hệ `la_ho_so`. Ô chú giải trên trang bài có thêm "Xem hồ sơ". Đường dẫn mục từ không đổi.

**Lập chỉ mục hồ sơ:** khi có tóm tắt do thư viện viết, có nguồn, và có ít nhất một quan hệ đã duyệt.

### 4.4. Họ dị bản và gộp tác phẩm

- **Họ** = nhóm các bài nối với nhau bằng quan hệ `di_ban_cua` (đối xứng).
- Máy gợi ý từ dấu vân tay (thuật toán D-065 có sẵn) với trạng thái `goi_y`; người duyệt theo lô.
- **Bản đại diện của họ:** meta `_phongdao_dai_dien_ho` trên một bài, do người chọn. Mặc định là bài có cấp cao nhất.
- Mỗi bài trong họ hiện "Các bản khác trong kho".
- **Thẻ canonical giữ nguyên** ở bước đầu. Bài tầng Lưu trữ vốn đã `noindex`. Đổi canonical là quyết định SEO riêng, sau khi có số đo.
- **Gộp** (H6; người bấm theo lô có xem trước):
  - lời bài phụ thành mục dị bản của bài chính, giữ nguồn của bài phụ;
  - phần người làm chuyển sang khi bài chính chưa có;
  - lượt đọc cộng dồn;
  - bài phụ vào thùng rác (khôi phục được), đường dẫn cũ chuyển hướng 301;
  - ghi nhật ký;
  - **Hoàn tác** trả lại đủ.

### 4.5. Bài viết

- Dùng loại `post` hiện có (46 bài).
- **Đường dẫn `/bai-viet/<tên>/`**, chuyển hướng 301 cho 46 đường cũ (H6).
- **Phân loại:** taxonomy `loai_bai_viet` với Khảo cứu · Bình giảng · Chuyên đề — tên do người sáng lập chốt. Không dùng chuyên mục chung của WordPress, để tách khỏi bài cũ chưa phân loại.
- **Meta:**
  - `tac_gia_bai` (chữ người biên soạn nhập);
  - `bien_tap`;
  - `tham_dinh`;
  - `tai_lieu` (danh sách hồ sơ nguồn + trang);
  - ô quyền hiện có `_phongdao_bai_viet_cho_google`, giữ nghĩa "đã có quyền đăng" — điều kiện để hiện ở trang tác phẩm và để lập chỉ mục.
- **Quan hệ** `ban_ve` tới tác phẩm, hồ sơ.
- Thân bài dùng trình soạn khối. Có khối "thẻ tác phẩm" để chèn một bài vào giữa bài viết — dùng lại thẻ bài có sẵn.
- `phan_tich` (bài cảm nhận của bạn đọc) giữ nguyên, tách nhãn.

### 4.6. Xưởng biên tập — mở rộng bảng việc, không bảng mới

- **Bảng `wp_cadao_viec` thêm cột** (một bước nâng cấp CSDL):
  - `vai_can` — vai cần để duyệt: `cong_tac` / `bien_tap` / `tham_dinh`;
  - `tang` — hạng A / B / C;
  - `nguoi_nhan` — người đang giữ thẻ, có hạn giữ;
  - `mau_kiem` — đánh dấu thẻ kiểm mẫu.
- Chỉ mục mới `(trang_thai, vai_can, uu_tien)`.
- **Ghi vết AI** nằm trong `noi_dung`: mô hình, phiên bản lời nhắc, phiên bản Sổ tay, nguồn trích, độ tự tin.
- **Quyền mới** `cadao_tham_dinh`, vai "Thẩm định". Vai "Cộng tác" và quyền `cadao_cong_tac` đã khai sẵn.
- **Nháp nhiều phần:** một thẻ có thể mang Ý nghĩa, chú giải, gợi ý phân loại, gợi ý quan hệ. Duyệt từng phần hay duyệt cả thẻ — mỗi phần vẫn đi qua `LuuPhan` / `LienKet`.

### 4.7. Cấp, hạng, điểm ưu tiên — tính sẵn

Bảng `wp_cadao_tom_tat` thêm các cột sau, tính lại khi lưu và mỗi đêm:

- `tang`;
- `diem_uu_tien` (52 mục 3.1);
- `so_quan_he`;
- cấp theo định nghĩa mới (52 mục 2.1).

Danh sách công khai, Xưởng, Số liệu đọc từ đây. Nguồn sự thật vẫn là dữ liệu của bài.

### 4.8. Quyền ảnh, âm thanh

- **Meta tệp `_phongdao_quyen`:**
  - giấy phép;
  - nguồn (hồ sơ nguồn hoặc URL);
  - tác giả;
  - người giữ quyền;
  - mã hồ sơ đồng ý (ghi âm);
  - giới hạn (chỉ trang này / không xuất ra ngoài);
  - dòng ghi công.
- Đọc cả `_phongdao_anh_ghi_cong` cũ. Đúng tinh thần D-027 (quyền ở nguồn và tư liệu, không ở tác phẩm).

### 4.9. Tìm

- Mở rộng chỉ mục `wp_cadao_tim` cho:
  - hồ sơ thực thể (tên, tên khác, tóm tắt);
  - bài viết (tên, đoạn mở);
  - từ đồng nghĩa (bảng nhỏ trong tuỳ chọn);
  - tên cũ địa danh (lấy từ `ten_khac` của hồ sơ).
- Giữ thuật toán bỏ dấu, gõ sai (D-067, D-112).
- **Ngưỡng chuyển sang máy tìm ngoài:** độ trễ p95 trên trang thử với dữ liệu thật > 300 ms, hoặc cần khớp gần đúng xuyên 100.000 mục.

### 4.10. Lớp tái dùng `TaiDung` và đọc mở

- `MienDuLieu\TaiDung` (PHP thuần, có kiểm thử) quyết phần nào được đưa ra ngoài trang đọc. Luật ở [46](46-kien-truc-va-khuyen-nghi.md) mục 5.1, cập nhật cho hồ sơ, bài viết, media.
- Mọi kênh dùng lớp này: dữ liệu mở (lệnh WP-CLI xuất tệp có phiên bản), REST đọc mở `phongdao/v1/mo/…` (đệm, giới hạn tần suất ở Cloudflare — không lưu IP), Play, khung nhúng.

### 4.11. Trang ứng dụng (Play, Học, Xưởng biên tập)

- **ES module, Web Components, không bundler mặc định.** Trình duyệt hiện đại chạy module trực tiếp nên vẫn giữ "không bước dựng".
- Ngân sách riêng (53 mục 8).
- Dữ liệu qua REST đọc đã lọc `TaiDung`.
- Tiến độ người chơi lưu trên máy, bọc lỗi (không tài khoản, 51 R-20).
- PWA và đọc ngoại tuyến ở chặng 2.

## 5. Danh mục quan hệ (bản đầu)

| Mã | Từ → Đến | Nghĩa | Nhãn hiển thị (thuận / ngược) | Bằng chứng | Ai tạo |
|---|---|---|---|---|---|
| `di_ban_cua` | tác phẩm ⇄ tác phẩm | Cùng họ dị bản | Các bản khác | Không bắt buộc | Máy gợi ý, người duyệt |
| `dap_loi` | tác phẩm → tác phẩm | Lời đáp trong cuộc hát | Lời đáp / Lời hỏi | Khuyến nghị | Người |
| `gan_nghia`, `trai_nghia` | tác phẩm ⇄ tác phẩm | Câu gần nghĩa, trái nghĩa | Gần nghĩa / Trái nghĩa | Không | Người (chuyển từ phần cũ) |
| `cung_hinh_anh` | tác phẩm ⇄ tác phẩm | Cùng một hình ảnh, khác mô-típ | Cùng hình ảnh … | Không | Máy gợi ý, người duyệt |
| `nhac_toi` | tác phẩm → hồ sơ | Lời nhắc tới người, nơi, sự vật | Nhắc tới / Được nhắc trong | Không (vị trí trong lời) | Máy gợi ý từ chú giải đã nối, người duyệt |
| `dung_dien_tich` | tác phẩm → hồ sơ (điển tích) | Dùng điển tích | Điển tích / Có trong | Khuyến nghị | Người |
| `ghi_nhan_tai` | tác phẩm → hồ sơ (địa danh) | Nơi sưu tầm, lưu truyền — **khác** "nhắc tới" | Ghi nhận tại / Lời ghi nhận ở đây | **Bắt buộc** | Người |
| `ban_ve` | bài viết → tác phẩm / hồ sơ | Bài viết bàn về | Bài viết về bài này / Bàn về | Không | Người |
| `lien_quan` | hồ sơ ⇄ hồ sơ | Nhân vật – nơi chốn – sự kiện | Liên quan (kèm ghi chú) | Khuyến nghị | Người |
| `la_ho_so` | mục từ → hồ sơ | Nghĩa của chữ trỏ tới một thực thể | Xem hồ sơ | Không | Người (máy gợi ý) |
| `minh_hoa`, `tu_lieu` | media → tác phẩm / hồ sơ | Ảnh minh hoạ; tư liệu (ảnh trang sách, bản ghi) | Minh hoạ / Tư liệu | Quyền media bắt buộc | Người |
| `doc_them` | mọi loại → hồ sơ nguồn (web) | Tài liệu đọc thêm, không phải chứng cứ | Đọc thêm | Ngày kiểm liên kết | Người |

Thêm mã mới = sửa danh mục + kiểm thử + ghi chú ở `docs/26`. Không cần bảng mới.

## 6. Chiến lược di chuyển

**Nguyên tắc:**

1. **Mở rộng → chuyển → thu hẹp.** Thêm cấu trúc mới không phá cấu trúc cũ. Ghi kép trong thời gian chuyển. Chỉ bỏ cái cũ sau khi cái mới chạy ổn ít nhất một phiên bản.
2. **Bước nâng cấp đánh số, chạy lại được** (`HaTang\CaiDat`, như hiện nay).
3. **Diễn tập trên trang thử có bản sao dữ liệu thật trước.** Có số đo thời gian chạy, số dòng, lỗi.
4. **Sao lưu đêm trước** (người sáng lập xác nhận) và **đã thử khôi phục ít nhất một lần** (51 R-24).
5. **Công tắc** cho mọi luồng đọc mới. Bật theo thể loại, theo hạng.
6. Mỗi bước có **lệnh hoàn tác** hoặc cách quay lại ghi trước.
7. Theo dõi sau khi bật: lỗi PHP, thời gian trang, Search Console, báo lỗi của người đọc.

| Bước | Việc | Rủi ro | Quay lại |
|---|---|---|---|
| M0 | Trang thử; sao lưu; thử khôi phục; đo nền hiệu năng với dữ liệu thật | — | — |
| M1 | **Mở rộng:** tạo bảng quan hệ; thêm cột bảng việc, bảng tóm tắt; mã dị bản | Thấp | Bảng, cột mới không được đọc; bước gỡ xoá được |
| M2 | **Ghi kép:** `gan_nghia` ghi cả phần cũ và bảng mới; Lịch sử ghi đủ | Thấp | Tắt ghi bảng mới |
| M3 | **Điền lùi:** `gan_nghia` → quan hệ đã duyệt; gợi ý `nhac_toi` từ chú giải đã nối mục từ; gợi ý `di_ban_cua` từ đo trùng (đều `goi_y`, không công khai). Chạy lại được, có báo cáo số dòng | TB | Xoá các dòng do bước này tạo (đánh dấu nguồn tạo) |
| M4 | **Đọc mới sau công tắc:** Mạng liên kết, Đọc tiếp đọc từ bảng (chỉ `duyet`) | TB | Tắt công tắc → về cách cũ |
| M5 | **Hồ sơ thực thể:** đăng ký `ho_so`, đường dẫn theo loại; tạo **nháp** hồ sơ từ mục từ có toạ độ đã xác nhận (không tự đăng) | TB | Gỡ đăng ký loại bài; nháp giữ, không công khai |
| M6 | **Bài viết:** meta, phân loại, đường dẫn `/bai-viet/` + 301 cho 46 bài (H6) | TB | Trả cấu trúc đường dẫn cũ; 301 ngược |
| M7 | **Thu hẹp:** `gan_nghia` thôi ghi phần cũ (giữ đọc tương thích một phiên bản) | Thấp | Bật lại ghi kép |
| M8 | **Họ dị bản và gộp** (H6 từng lô có xem trước) | Cao | Mỗi lần gộp có hoàn tác: khôi phục bài phụ, gỡ 301, trả phần |
| M9 | **Cấp mới, hạng, điểm ưu tiên** (độc lập với M1–M8, làm sớm ở GĐ1 — 55 E1.1.1): tính lại toàn kho theo lô | Thấp | Giữ cột cũ một phiên bản; tính lại được |

**Giờ chạy:** bước nặng (M3, M8, M9) chạy theo lô trong giờ ít người đọc, có con trỏ lô và khoá (cơ chế việc nền đang có).

## 7. Hiệu năng

| Hạng mục | Ước lượng | Ngân sách / cách giữ |
|---|---|---|
| Số dòng quan hệ | ~50.000 cho Bộ tham chiếu; ~150.000 toàn kho kể cả gợi ý | Chỉ mục hai chiều; truy vấn theo bài < 5 ms |
| Trang tác phẩm | +2 truy vấn có chỉ mục (từ / tới), đệm theo bài, xoá đệm khi quan hệ đổi | Thời gian phản hồi không đệm ≤ 800 ms trên trang thử với dữ liệu thật; khách dùng trang đã đệm |
| Trang hồ sơ, Atlas | Danh sách tác phẩm phân trang 30; số đếm mỗi hồ sơ tính sẵn mỗi đêm | Không GROUP BY lớn lúc người đọc mở trang |
| Hàng duyệt Xưởng | Chỉ mục `(trang_thai, vai_can, uu_tien)` | Mở hàng ≤ 1 giây với 10.000 thẻ |
| Tìm | Thêm ~5.000 dòng hồ sơ, bài viết | p95 ≤ 300 ms trên trang thử; vượt thì xét máy tìm ngoài |
| Đệm | Đệm transient theo bài (đã có). Xét bộ nhớ đệm đối tượng (Redis) nếu FlashPanel có | Người sáng lập bật (cấu hình máy chủ — AGENTS.md, KHÔNG BAO GIỜ 15) |

**Đo:** mở rộng `npm run do:tim:v2` và `scripts/do-hom-nay-v2.mjs` để chạy trên trang thử; ghi số đo trước / sau mỗi bước di chuyển.

## 8. Bảo trì

- Mỗi loại dữ liệu khai báo **một nơi**: danh mục phần, danh mục quan hệ, danh mục loại hồ sơ. Kiểm thử PHP thuần cho danh mục và luật.
- **Một cửa ghi** cho mỗi loại (`LuuPhan`, `LienKet`); lằn ranh kiểm trong mã, có kiểm thử khẳng định — mở rộng 6 lằn ranh của `docs/28` mục 6 cho quan hệ và hồ sơ.
- **Không plugin nội dung bên ngoài** (ACF, Pods, plugin SEO, plugin bài liên quan).
- `docs/26` cập nhật trong cùng PR với mỗi bước. Phiên bản CSDL tăng theo bước.
- Mã chết dọn trong cùng PR (như cách D-121 làm).

## 9. Quyết định kiến trúc đề xuất

| Mã | Quyết định | Thay / sửa | QĐ ([56](56-goi-quyet-dinh.md)) |
|---|---|---|---|
| KTĐ-1 | Giữ WordPress, plugin / theme riêng; không giao diện headless, không cơ sở dữ liệu đồ thị (phương án B) | Khẳng định D-083 | C7 |
| KTĐ-2 | Bảng quan hệ có kiểu, có bằng chứng, có trạng thái; danh mục quan hệ mục 5 | Thay D-094 (4), D-110 phần quan hệ | C1 |
| KTĐ-3 | Hồ sơ thực thể là loại bài `ho_so` (B2), tách khỏi Từ điển | Thay D-114 phần mô hình | C2 |
| KTĐ-4 | Bài viết dùng `post`, đường dẫn `/bai-viet/`, cấu trúc meta, quan hệ `ban_ve` | Sửa D-126 (5) (giữ luật quyền) | C3 |
| KTĐ-5 | Họ dị bản qua quan hệ; gộp là thao tác người có hoàn tác | Mở rộng D-065, D-115 | C4 |
| KTĐ-6 | Xưởng biên tập dùng bảng việc mở rộng; vai Thẩm định | Mở rộng D-094, D-103 | C5 |
| KTĐ-7 | Cấp mới, hạng, điểm ưu tiên tính sẵn trong bảng tóm tắt | Thay D-084 (4) phần định nghĩa | C5 |
| KTĐ-8 | Quyền media có cấu trúc ở tệp đính kèm | Làm rõ D-027 | C5 |
| KTĐ-9 | `TaiDung` là cửa duy nhất cho mọi kênh ngoài trang đọc | Mới | C7 |
| KTĐ-10 | Trang ứng dụng dùng ES module, Web Components, ngân sách riêng | Sửa D-094 (2), D-030 | C6 |
| KTĐ-11 | Đo trên trang thử trước khi bật; ngưỡng chuyển sang máy tìm ngoài | Thay D-098; giữ D-067 làm điều kiện | C7 |

## 10. Rủi ro

| Rủi ro | Mức | Giảm thiểu |
|---|---|---|
| Bảng quan hệ đầy gợi ý máy chưa ai duyệt | TB | Trang người đọc chỉ đọc `duyet`; gợi ý có hạn sống, dọn định kỳ; Xưởng xếp gợi ý theo hạng |
| Trùng khái niệm Từ điển và Hồ sơ | TB | Định nghĩa rõ (4.3); quan hệ `la_ho_so`; Sổ tay hướng dẫn khi nào tạo hồ sơ |
| Đổi đường dẫn bài viết làm rơi thứ hạng | Thấp | 46 bài hiện `noindex`; 301 đầy đủ |
| Gộp nhầm | Cao | Người bấm, xem trước, hoàn tác, H6 từng lô; không gộp tự động |
| Di chuyển chạy lâu trên máy chủ chung | TB | Theo lô, giờ thấp điểm, đã đo trên trang thử |
| Mô hình phình theo ý tưởng | TB | Danh mục quan hệ chỉ thêm khi có nội dung thật (≥ 10 cặp) và người duyệt |
