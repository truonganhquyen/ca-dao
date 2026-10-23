# 10 · Đặc tả dữ liệu

| | |
|---|---|
| Phiên bản schema | 1.1 · 05/10/2026 |
| Trạng thái | **Ổn định cho GĐ0.** Chỉnh một lần sau 10 hồ sơ thật (T-006), rồi **đóng băng thành v1** (mục 12) |
| Thực thi bởi | `scripts/kiem-tra-du-lieu.mjs` (quy tắc R01–R19) · `scripts/lib/neo-chu-giai.mjs` · `taxonomy/tu-vung-kiem-soat.yaml` · `templates/*.yaml` |
| Đọc khi | Biên soạn, viết mã đọc dữ liệu, viết công cụ, sửa schema |

Tài liệu này và `scripts/kiem-tra-du-lieu.mjs` phải luôn khớp nhau. Đổi một bên thì đổi bên kia trong cùng một commit, kèm fixture trong `tests/fixtures/`.

---

## 1. Sáu nguyên tắc mô hình hóa

1. **Tách Tác phẩm khỏi Dị bản.** Tác phẩm là đơn vị khái niệm; dị bản là một văn bản cụ thể có nguồn. Mỗi tác phẩm có ≥ 1 dị bản và đúng một **bản đại diện** (giao diện gọi là "Bản tham chiếu").
2. **Chú giải là con trỏ.** "Từ này, ở dòng này, của dị bản này → mục từ X". Giải nghĩa viết một lần trong Từ điển dùng chung.
3. **Mọi khẳng định có nguồn và mức bằng chứng.** Áp dụng cho lời, giải nghĩa, cách hiểu, âm thanh.
4. **Một sự thật, một nơi lưu.** Cái gì suy ra được lúc build thì không lưu (mức của tác phẩm, danh sách bài theo mô-típ, tình huống của một tác phẩm).
5. **Có tầng ngôn ngữ đời thường.** `mo_ta_tinh_huong` và "Hợp khi…" viết như người thường nói, phục vụ tìm kiếm và nhóm người trẻ.
6. **Người khai thứ dễ nhớ, máy suy ra phần còn lại.** Con người không nhập dữ liệu mà máy suy ra được chắc chắn (vị trí âm tiết, mức của tác phẩm, danh sách bài theo mô-típ…). Khi máy không chắc, máy báo lỗi để người chỉ rõ, không tự đoán.

## 2. Sơ đồ thực thể

```mermaid
erDiagram
  TAC_PHAM ||--|{ DI_BAN : "gồm (trong cùng tệp)"
  DI_BAN ||--o{ CHU_GIAI : "có (trong tệp tác phẩm)"
  CHU_GIAI }o--|| MUC_TU : "trỏ tới"
  TAC_PHAM }o--o{ MO_TIP : "thuộc"
  TINH_HUONG }o--o{ TAC_PHAM : "liệt kê, kèm Hợp khi"
  DI_BAN }o--o{ NGUON : "dẫn từ"
  MUC_TU }o--o{ NGUON : "dẫn từ"
  GHI_AM }o--|| DI_BAN : "ghi lại"
  TAC_PHAM }o--|| NGUOI_DONG_GOP : "biên soạn bởi"
  DONG_GOP }o--o| TAC_PHAM : "liên quan (kho riêng tư)"
```

## 3. Thư mục và quy ước mã

```text
content/
├── tac-pham/        cd-0142.yaml, tn-0007.yaml …   (dị bản và chú giải nằm TRONG tệp tác phẩm)
├── muc-tu/          lx-thai-son.yaml …
├── mo-tip/          mt-than-em-nhu.yaml …
├── nguon/           src-kho-tang-ca-dao-nguoi-viet.yaml …
├── tinh-huong/      sit-nho-me.yaml …
├── ghi-am/          au-0001.yaml …                   (chỉ siêu dữ liệu)
└── nguoi-dong-gop/  ctb-0001.yaml …                  (chỉ tên hiển thị)
redirects.yaml       slug cũ → slug mới
```

| Loại | Mã | Ví dụ | Ghi chú |
|---|---|---|---|
| Ca dao | `cd-####` | `cd-0142` | Tiền tố phải khớp `the_loai` |
| Tục ngữ | `tn-####` | `tn-0007` | |
| Thành ngữ | `tng-####` | `tng-0015` | |
| Đồng dao | `dd-####` | `dd-0003` | |
| Vè | `ve-####` | `ve-0001` | |
| Câu đố | `cdo-####` | `cdo-0001` | |
| Mục từ | `lx-<slug>` | `lx-thai-son` | |
| Mô-típ | `mt-<slug>` | `mt-than-em-nhu` | |
| Nguồn | `src-<slug>` | `src-kho-tang-ca-dao-nguoi-viet` | |
| Tình huống | `sit-<slug>` | `sit-nho-me` | |
| Ghi âm | `au-####` | `au-0088` | |
| Người đóng góp | `ctb-####` | `ctb-0012` | |
| Đóng góp (riêng tư) | `sub-YYYY-####` | `sub-2026-0031` | Không nằm trong kho mã |

**Quy tắc:**
- `id` trùng tên tệp. Bất biến; không đổi, không tái sử dụng. Số thứ tự không mang nghĩa.
- `slug`: bỏ dấu, chữ thường, nối gạch ngang, ≤ 60 ký tự, duy nhất trong tác phẩm. Trùng thì thêm `-2`. Đổi slug thì thêm dòng vào `redirects.yaml`.
- Ngày viết dạng `"YYYY-MM-DD"` trong ngoặc kép.

## 4. Đặc tả trường

Ký hiệu cột Bắt buộc: **✔** luôn bắt buộc · **KĐ** bắt buộc từ trạng thái `cho_kiem_dinh` · trống = không bắt buộc.

### 4.1. Tác phẩm (`content/tac-pham/<id>.yaml`)

| Trường | Kiểu | Bắt buộc | Ràng buộc / mô tả |
|---|---|---|---|
| `id` | chuỗi | ✔ | Mục 3 |
| `slug` | chuỗi | ✔ | Mục 3 |
| `the_loai` | mã | ✔ | Từ vựng `the_loai` |
| `the_tho` | mã | KĐ nếu ca dao | Từ vựng `the_tho` |
| `tieu_de` | chuỗi | ✔ | Thường là dòng đầu của bản đại diện |
| `ban_dai_dien` | id dị bản | ✔ | Phải là dị bản có trong tệp; mức A/B từ KĐ (R05) |
| `ly_do_chon` | chuỗi | KĐ | Một câu: vì sao chọn bản này |
| `di_ban` | danh sách | ✔ (≥ 1) | Mục 4.2 |
| `chu_giai` | danh sách | | Mục 4.3 |
| `nghia_ngan` | chuỗi | KĐ | ≤ 40 từ (nên ≤ 25); dùng cho "Hiểu trong 10 giây", kết quả tìm, thẻ, mô tả SEO |
| `dien_giai` | danh sách | KĐ (≥ 1) | Mỗi mục: `goc_nhin` ✔, `noi_dung` ✔ (Markdown), `nguon` (≥ 1 từ KĐ) |
| `nghia_den` | chuỗi | KĐ nếu tục ngữ | |
| `nghia_bong` | chuỗi | | Tục ngữ có nghĩa bóng thì điền |
| `linh_vuc` | danh sách mã | KĐ nếu tục ngữ (≥ 1) | Từ vựng `linh_vuc` |
| `goc_kiem_chung` | đối tượng | | Tục ngữ kinh nghiệm: `ket_luan` ✔ (từ vựng `ket_luan_kiem_chung`), `noi_dung`, `nguon` |
| `am_luat` | đối tượng | | `nhip`, `van` (danh sách), `ghi_chu`. Chỉ điền khi người biên soạn tự kiểm |
| `dien_xuong` | đối tượng | | `hinh_thuc` (danh sách mã `hinh_thuc_dien_xuong`), `nguoi_the_hien`, `boi_canh`. Không rõ thì `khong_ro`, không bịa |
| `mo_ta_tinh_huong` | danh sách chuỗi | KĐ (≥ 2) | Câu đời thường, ngôi thứ hai |
| `cam_xuc` | danh sách mã | | ≤ 5; từ vựng `cam_xuc` |
| `chu_de` | danh sách mã | KĐ (≥ 1) | ≤ 3; từ vựng `chu_de` |
| `mo_tip` | danh sách id | | Trỏ `mo-tip/` |
| `lien_quan` | danh sách id | | Trỏ `tac-pham/`; người biên soạn chọn, không tự động |
| `sgk` | danh sách | | Mỗi mục: `bo_sach` ✔ (từ vựng `sgk_bo_sach`), `lop` ✔ (1–12), `bai` |
| `canh_bao_noi_dung` | chuỗi | | Bài phản ánh định kiến xưa: giải thích bối cảnh (`11` mục 7) |
| `xac_thuc_dan_gian` | mã | | Mặc định `binh_thuong`; khác thì cần căn cứ (R13) |
| `can_cu_xac_thuc_dan_gian` | chuỗi | khi cờ ≠ `binh_thuong` | Căn cứ cụ thể: tác giả, lần xuất hiện đầu… |
| `tranh_luan` | đúng/sai | | Mặc định `false` |
| `ghi_chu_tranh_luan` | chuỗi | khi `tranh_luan: true` | Điều gì đang được bàn, các bên dẫn nguồn nào |
| `trang_thai` | mã | ✔ | `nhap` · `cho_kiem_dinh` · `da_xuat_ban` · `an` |
| `bien_soan` | đối tượng | ✔ | `nguoi` ✔ (ctb-…), `ngay_tao` ✔, `ngay_kiem_dinh` (KĐ) |

**Không có** trường `xac_thuc` và `tinh_huong` ở cấp tác phẩm (D-007, D-010). Trường lạ bị từ chối (R16).

### 4.2. Dị bản (phần tử của `di_ban`)

| Trường | Kiểu | Bắt buộc | Ràng buộc / mô tả |
|---|---|---|---|
| `id` | chuỗi | ✔ | `v1`, `v2`… duy nhất trong tác phẩm |
| `loi` | chuỗi nhiều dòng | ✔ | Một dòng thơ một dòng văn bản; NFC; không khoảng trắng thừa đầu/cuối dòng; **giữ đúng bản in** |
| `vung` | danh sách mã | ✔ (≥ 1) | Từ vựng `vung` |
| `dia_danh` | chuỗi | | Đúng như người kể / sách gọi, kể cả tên cũ |
| `nguon` | danh sách | ✔ (có thể rỗng) | Mỗi mục: `ref` ✔ (src-…), `trang`, `ghi_chu` |
| `xac_thuc` | mã | ✔ | A · B · C · D (mục 5) |
| `nguoi_ke` | đối tượng | | `the_he` ("sinh khoảng 1940"), `quan_he` ("bà nội người đóng góp"). Không tên thật |
| `dong_gop` | chuỗi | mức C: ✔ | `sub-YYYY-####` |
| `ghi_chu` | chuỗi | | |

### 4.3. Chú giải (phần tử của `chu_giai`)

| Trường | Kiểu | Bắt buộc | Mô tả |
|---|---|---|---|
| `di_ban` | id | ✔ | Dị bản chứa cụm từ |
| `cum_tu` | chuỗi | ✔ | Gõ đúng như trong lời (không phân biệt hoa thường, bỏ qua dấu câu dính kèm) |
| `dong` | số nguyên | chỉ khi cần | Khai khi cụm từ xuất hiện ở nhiều dòng |
| `lan` | số nguyên | chỉ khi cần | Khai khi cụm từ vẫn lặp trong phạm vi đã chọn (cả dị bản, hoặc dòng `dong`) |
| `muc_tu` | id | ✔ | lx-… |
| `nghia_tai_cho` | chuỗi | | ≤ 40 từ; nghĩa riêng trong bài này nếu khác nghĩa chung |

**Cách khai thường gặp:** `{ di_ban: v1, cum_tu: "Thái Sơn", muc_tu: lx-thai-son }` — không cần đếm dòng hay âm tiết.

**Máy phân giải** (`scripts/lib/neo-chu-giai.mjs`, dùng chung cho công cụ kiểm tra **và** cho build — không viết bộ phân giải thứ hai) tìm cụm từ theo **trọn âm tiết** và trả về `{dong, am_tiet_dau, so_am_tiet}`. Lỗi R06 khi: không tìm thấy; xuất hiện nhiều lần mà chưa khai `dong`/`lan` (**không được tự chọn lần đầu**); `dong` vượt số dòng; `lan` lớn hơn số lần xuất hiện. Cảnh báo R06 khi cụm từ không giống `tu`/`bien_the` của mục từ được trỏ tới (dấu hiệu trỏ nhầm). Sửa lời làm neo hỏng thì build báo ngay.

### 4.4. Mục từ (`content/muc-tu/lx-<slug>.yaml`)

| Trường | Kiểu | Bắt buộc | Mô tả |
|---|---|---|---|
| `id` | chuỗi | ✔ | |
| `tu` | chuỗi | ✔ | Dạng hiển thị |
| `bien_the` | danh sách | | Cách viết khác |
| `loai` | mã | ✔ | Từ vựng `loai_muc_tu` |
| `vung` | danh sách mã | phương ngữ: ✔ | |
| `giai_nghia` | chuỗi | ✔ | ≤ 40 từ; hiện trong ô chú giải |
| `giai_nghia_day_du` | Markdown | | Trang `/tu-dien/<slug>` |
| `nguon` | danh sách | ✔ (có thể rỗng khi nháp) | Từ điển, sách tham khảo |
| `xem_them` | danh sách id | | Mục từ liên quan |

### 4.5. Mô-típ (`content/mo-tip/mt-<slug>.yaml`)

`id` ✔ · `ten` ✔ · `mo_ta` ✔ (Markdown) · `nguon`. Danh sách tác phẩm **không** lưu ở đây; tính lúc build từ `mo_tip` của tác phẩm.

### 4.6. Nguồn (`content/nguon/src-<slug>.yaml`)

| Trường | Kiểu | Bắt buộc | Mô tả |
|---|---|---|---|
| `id` | chuỗi | ✔ | |
| `loai` | mã | ✔ | Từ vựng `loai_nguon` (sách, tạp chí, luận văn là "nguồn in") |
| `tac_gia` | danh sách | ✔ (≥ 1) | Ghi "(chủ biên)" nếu có |
| `ten` | chuỗi | ✔ | |
| `nxb`, `nam` | chuỗi / số | nguồn in: ✔ | Chưa biết thì `CAN_BO_SUNG` |
| `noi_xb`, `tap`, `tai_ban`, `isbn`, `url` | chuỗi | | `tai_ban` = lần in thực sự đang dùng |
| `ban_quyen` | mã | ✔ | `cong_cong` · `co_ban_quyen` · `duoc_phep` · `chua_ro` (xử lý như có bản quyền) |
| `da_xac_minh_ban_in` | đúng/sai | ✔ | `true` chỉ khi người biên soạn đã đối chiếu thông tin thư mục trên bản in (R17) |
| `ghi_chu` | chuỗi | | |

### 4.7. Ghi âm (`content/ghi-am/au-####.yaml`)

| Trường | Kiểu | Bắt buộc | Mô tả |
|---|---|---|---|
| `id` | chuỗi | ✔ | |
| `tac_pham`, `di_ban` | id | ✔ | Dị bản phải có trong tác phẩm (R12) |
| `loai` | mã | ✔ | Từ vựng `loai_ghi_am` |
| `nguoi_the_hien` | đối tượng | ✔ | `kieu_hien_thi` ✔; `ten_hien_thi` (✔ nếu không ẩn danh), `the_he`, `vung`, `dia_danh` |
| `nguoi_ghi` | id | | ctb-… |
| `ngay_ghi`, `dia_diem` | | | |
| `tep` | đối tượng | ✔ | `url_phat` ✔, `url_luu_tru` ✔, `thoi_luong` (giây) |
| `giay_phep` | mã | ✔ | Mặc định `cc_by_4_0` |
| `ma_dong_y` | chuỗi | ✔ | Mã hồ sơ đồng ý trong kho riêng tư (`41`) |
| `ban_chep` | chuỗi | | Lời chép đúng như nghe (dùng cho trợ năng) |
| `xac_thuc` | mã | ✔ | Thường là B |

### 4.8. Tình huống (`content/tinh-huong/sit-<slug>.yaml`)

| Trường | Kiểu | Bắt buộc | Mô tả |
|---|---|---|---|
| `id`, `slug` | chuỗi | ✔ | Trang: `/tinh-huong/<slug>` |
| `tieu_de` | chuỗi | ✔ | "Khi nhớ mẹ" |
| `nhom` | mã | ✔ | Từ vựng `nhom_tinh_huong` |
| `mo_dau` | chuỗi | ✔ | 50–100 từ, giọng gần gũi |
| `tac_pham` | danh sách | ✔ | Mỗi mục: `ref` ✔, `hop_khi` ✔ ("Hợp khi bạn…") |
| `tu_khoa` | danh sách | | Từ đồng nghĩa cho tìm kiếm |

Đây là **nơi duy nhất** lưu quan hệ tác phẩm–tình huống (D-010). Trang chỉ được tạo khi có ≥ 3 tác phẩm đã xuất bản (R11). Tác phẩm mang cờ nghi gán nhầm/phỏng tác hoặc có `canh_bao_noi_dung` không được gắn vào tình huống mang tính khuyên dùng.

### 4.9. Người đóng góp (`content/nguoi-dong-gop/ctb-####.yaml`)

`id` ✔ · `ten_hien_thi` ✔ · `kieu_hien_thi` ✔ · `vai_tro` ✔ (≥ 1, từ vựng `vai_tro`) · `gioi_thieu`.
**Không bao giờ** có email, số điện thoại, địa chỉ, tên thật của người chọn ẩn danh.

### 4.10. Đóng góp (kho riêng tư, ngoài Git)

Lưu ở bảng tính riêng tư (`40-quy-trinh-dong-gop.md` mục 3). Chỉ mã `sub-…` xuất hiện trong kho công khai, ở trường `dong_gop` của dị bản.

## 5. Bằng chứng, trạng thái và cờ

| Trục | Giá trị | Điều kiện máy kiểm (R08) | Hiển thị |
|---|---|---|---|
| Bằng chứng (dị bản) | **A** Đã đối chiếu tư liệu | ≥ 1 nguồn in có `trang`; từ KĐ, nguồn đó phải `da_xac_minh_ban_in: true` (R17) | Nhãn + biểu tượng |
| | **B** Có tư liệu điền dã | ≥ 1 nguồn `dien_da`/`ghi_am`, hoặc có tệp `ghi-am/` trỏ tới dị bản | Nhãn + biểu tượng |
| | **C** Cộng đồng ghi nhận | Có `dong_gop` | Chỉ trong khối "Cộng đồng ghi nhận" |
| | **D** Chưa đủ nguồn | — | **Không bao giờ hiển thị** |
| Quy trình (tác phẩm) | `nhap` → `cho_kiem_dinh` → `da_xuat_ban`; `an` | Từ `cho_kiem_dinh`: mọi quy tắc "KĐ" áp dụng | Chỉ `da_xuat_ban` lên trang |
| Cờ tranh luận | `tranh_luan` | Cần `ghi_chu_tranh_luan` (R14) | Dòng cảnh báo + các cách hiểu song song |
| Cờ xác thực dân gian | `binh_thuong` · `nghi_gan_nham` · `phong_tac_hien_dai` | Khác `binh_thuong` cần căn cứ (R13) | Nhãn xám "Nghi không phải dân gian" + căn cứ |

**Ba trục độc lập, không gộp thành một trạng thái:** quy trình (`trang_thai`), bằng chứng (`xac_thuc` của dị bản), và quyền sử dụng. Quyền **không** là một trường trên tác phẩm, vì lời ca dao là di sản dân gian và phần chú giải do dự án tự viết; quyền được kiểm ở đúng chỗ phát sinh rủi ro:
- **Nguồn** (`ban_quyen`): quyết định được dùng tư liệu đến đâu (`12` mục 4). Ba khái niệm tách bạch: *nguồn tìm thấy* (chỉ là đầu mối, nằm trong `data/`, không phải `content/nguon/`) ≠ *nguồn được phép tái sử dụng* (`ban_quyen`) ≠ *nguồn đủ mạnh để xác minh* (`loai` + `da_xac_minh_ban_in` + điều kiện mức A/B).
- **Tư liệu điền dã chưa công bố** (phiếu, ghi chép của đối tác): lời lấy từ đó chỉ được xuất bản khi nguồn có `ban_quyen: cong_cong` hoặc `duoc_phep` (R18).
- **Ghi âm:** bắt buộc có hồ sơ đồng ý (`ma_dong_y`, R02).

**Vòng đời tác phẩm và ai được chuyển:**

| Từ → đến | Ai | Điều kiện máy kiểm |
|---|---|---|
| (mới) → `nhap` | Người biên soạn hoặc AI agent | Schema hợp lệ; dấu `NHAP_AI`/`CAN_BO_SUNG` được phép |
| `nhap` → `cho_kiem_dinh` | **Chỉ người biên soạn** | Mọi quy tắc "KĐ" (mục 9) |
| `cho_kiem_dinh` → `da_xuat_ban` | **Chỉ người biên soạn**, sau ≥ 24 giờ | Như trên + `ngay_kiem_dinh` |
| `da_xuat_ban` → `an` | Người biên soạn | Không (ẩn ngay khi phát hiện sai, không làm hỏng build) |
| bất kỳ → xóa tệp / gộp hai tác phẩm | **Chỉ người biên soạn** | Thêm `redirects.yaml`; mã cũ không tái sử dụng |

`NHAP_AI` và `CAN_BO_SUNG` là **dấu máy đọc được** trong dữ liệu, không phải trạng thái riêng: trạng thái suy ra từ dấu, nên không thể "quên cập nhật trạng thái". Đóng góp chưa duyệt (`moi`, `tu_choi`…) sống trong bảng tính riêng tư (`40` mục 3), không trong `content/`.

**Mức của tác phẩm** = mức của bản đại diện (suy ra lúc build).
**Thẻ chia sẻ và trích dẫn mặc định** chỉ dùng dị bản A/B của tác phẩm mang cờ `binh_thuong` và không có `canh_bao_noi_dung`.
**Chỉ người biên soạn** được đặt mức A/B và trạng thái `cho_kiem_dinh` / `da_xuat_ban`.

## 6. Dấu CAN_BO_SUNG và NHAP_AI

| Dấu | Nghĩa | Ở trạng thái `nhap` | Từ `cho_kiem_dinh` |
|---|---|---|---|
| `CAN_BO_SUNG` | Thông tin còn thiếu, phải lấy từ bản in / người kể | Cảnh báo | **Lỗi** (R09) |
| `NHAP_AI` (tiền tố) | Văn bản do AI soạn nháp, người biên soạn phải viết lại | Cảnh báo | **Lỗi** (R09) |

Quy tắc R09 cũng áp cho mục từ, nguồn, mô-típ mà một tác phẩm KĐ dùng tới, và cho tình huống sắp được tạo trang.

## 7. Từ vựng kiểm soát

Tất cả nằm trong `taxonomy/tu-vung-kiem-soat.yaml`. Mỗi giá trị có `ma` (máy đọc) và `nhan` (người đọc).

- Mã chỉ gồm chữ thường không dấu, số, gạch dưới; ngoại lệ duy nhất: A/B/C/D (R15).
- Mã đã dùng không đổi, không tái sử dụng; bỏ thì thêm `ngung_dung: true`.
- Chỉ thêm giá trị khi ≥ 3 tác phẩm cần tới, và ghi vào `01`.

| Nhóm | Số giá trị v1 |
|---|---|
| `the_loai`, `the_tho`, `trang_thai`, `xac_thuc`, `xac_thuc_dan_gian` | 6, 6, 4, 4, 3 |
| `vung`, `chu_de`, `cam_xuc`, `linh_vuc` | 8, 11, 12, 7 |
| `ket_luan_kiem_chung`, `hinh_thuc_dien_xuong`, `loai_muc_tu` | 4, 7, 8 |
| `loai_nguon`, `ban_quyen`, `nhom_tinh_huong`, `loai_ghi_am` | 7, 4, 9, 6 |
| `giay_phep`, `kieu_hien_thi`, `vai_tro`, `sgk_bo_sach` | 2, 3, 5, 3 |

**Lưu ý vùng:** từ giữa năm 2025 Việt Nam sắp xếp lại đơn vị hành chính cấp tỉnh và bỏ cấp huyện (kiểm tra lại chi tiết trước khi thiết kế form). Vì ca dao gắn với địa danh lịch sử: dùng **vùng văn hóa** làm trường phân loại, lưu địa danh **đúng như người kể gọi** ở `dia_danh`.

## 8. Chuẩn hóa văn bản tiếng Việt

- **Lưu trữ:** Unicode dựng sẵn (NFC). Tệp không NFC → lỗi R01. Ký tự nghi mã cũ TCVN3/VNI (khối Latin-1 U+00A1–U+00BF, ×, ÷) → cảnh báo R01.
- **Lời trích từ sách:** giữ nguyên kiểu bỏ dấu của bản in ("hoà" hay "hòa").
- **Văn bản dự án tự viết:** theo quy ước ở Q-004.
- **Tìm kiếm:** chuẩn hóa ở chỉ mục, không ở dữ liệu (`21` mục 5).

## 9. Quy tắc kiểm tra

`npm run check:data`. Có lỗi thì thoát mã 1, build dừng. "KĐ" = áp dụng khi tác phẩm ở `cho_kiem_dinh` hoặc `da_xuat_ban`; ở `nhap` và `an` (không lên trang) những quy tắc này chỉ là cảnh báo, để có thể ẩn một hồ sơ hỏng mà không làm hỏng build.

| Mã | Quy tắc | Mức |
|---|---|---|
| R01 | YAML hợp lệ, UTF-8, NFC; lời không khoảng trắng thừa, không tab; cảnh báo ký tự mã cũ và dòng trống | Lỗi |
| R02 | Trường bắt buộc, kiểu, định dạng, giá trị thuộc từ vựng; bắt buộc theo thể loại (ca dao cần `the_tho`; tục ngữ cần `nghia_den`, `linh_vuc`); nguồn in cần `nxb`, `nam`; mục từ phương ngữ cần `vung`; ghi âm không ẩn danh cần `ten_hien_thi` | Lỗi |
| R03 | `id` khớp tên tệp, đúng quy ước mã, không trùng; `slug` không trùng; tiền tố mã khớp `the_loai`; id dị bản không trùng | Lỗi |
| R04 | Mọi tham chiếu (`ref`, `muc_tu`, `mo_tip`, `lien_quan`, `nguoi`, `tac_pham`…) trỏ tới tệp có thật | Lỗi |
| R05 | `ban_dai_dien` là dị bản có thật; phải mức A/B | Lỗi (mức A/B: KĐ) |
| R06 | Neo chú giải phân giải được duy nhất (mục 4.3): tìm thấy, không mơ hồ, không vượt dòng/lần; cảnh báo khi cụm từ không giống mục từ được trỏ tới | Lỗi / cảnh báo |
| R07 | `nghia_ngan`, `giai_nghia`, `nghia_tai_cho` ≤ 40 từ; cảnh báo `nghia_ngan` > 25 từ | Lỗi / cảnh báo |
| R08 | Điều kiện của từng mức bằng chứng (mục 5) | KĐ |
| R09 | Không còn `CAN_BO_SUNG` / `NHAP_AI` (mục 6) | KĐ |
| R10 | Danh sách đủ số mục tối thiểu khi KĐ (`dien_giai`, nguồn của diễn giải, `mo_ta_tinh_huong`, `chu_de`); tác phẩm KĐ phải thuộc ≥ 1 tình huống | KĐ |
| R11 | Tình huống: cảnh báo khi < 3 tác phẩm đã xuất bản (trang không được tạo); lỗi khi gắn tác phẩm mang cờ nghi gán nhầm/phỏng tác hoặc có `canh_bao_noi_dung` | Cảnh báo / lỗi |
| R12 | Ghi âm trỏ đúng dị bản có thật | Lỗi |
| R13 | Cờ xác thực dân gian khác `binh_thuong` cần căn cứ | Lỗi |
| R14 | `tranh_luan: true` cần `ghi_chu_tranh_luan` | Lỗi |
| R15 | Mã trong từ vựng kiểm soát đúng quy ước, không trùng | Lỗi |
| R16 | Không có trường lạ ngoài đặc tả | Lỗi |
| R17 | Dị bản A của tác phẩm KĐ chỉ dựa trên nguồn đã `da_xac_minh_ban_in: true`; nguồn đánh dấu đã xác minh không được còn `CAN_BO_SUNG` | Lỗi |
| R18 | Dị bản B dựa trên tư liệu điền dã (không có tệp ghi âm) của tác phẩm KĐ: nguồn phải `cong_cong` hoặc `duoc_phep` | KĐ |
| R19 | Nghi trùng: lời một dị bản giống hệt (bỏ dấu, bỏ dấu câu) dị bản của tác phẩm khác → lỗi; dòng đầu bản tham chiếu trùng tác phẩm khác → cảnh báo | Lỗi / cảnh báo |

Kiểm thử chính công cụ: `npm run test:fixtures` (mỗi quy tắc quan trọng có một bộ lỗi mẫu trong `tests/fixtures/`).

**Báo cáo sức khỏe dữ liệu:** `npm run health` (hoặc `npm run health:json`) in thêm: số tác phẩm theo trạng thái, số hồ sơ còn dấu nháp, dị bản theo mức, bản tham chiếu chưa đạt A/B hoặc dưới 2 nguồn in, các loại "nợ dữ liệu" (chưa rõ vùng, mục từ chưa có nguồn, nguồn chưa xác minh/chưa rõ quyền, tình huống thiếu bài), neo hỏng, nghi trùng. Chạy hằng tuần (`30` mục 1); đây là danh sách "hôm nay cần xử lý gì" cho tới khi có công cụ biên tập riêng.

## 10. Dữ liệu suy ra lúc build

| Dữ liệu | Suy ra từ |
|---|---|
| Mức bằng chứng của tác phẩm | `xac_thuc` của bản đại diện |
| Tình huống của một tác phẩm ("Dùng khi…") | Các tệp `tinh-huong/` liệt kê tác phẩm đó |
| Danh sách bài theo mô-típ | Trường `mo_tip` của các tác phẩm |
| Danh sách bài dùng một mục từ | Trường `chu_giai[].muc_tu` |
| Số dị bản hiển thị | Dị bản A/B (khối chính) và C (khối cộng đồng); D không bao giờ |

## 11. Xuất dữ liệu mở

Mỗi quý (`21` mục 10): một tệp JSON gộp gồm các tác phẩm `da_xuat_ban` và mọi tệp chúng tham chiếu; **loại bỏ** dị bản D, thông tin trong kho riêng tư, và chú thích nội bộ. Kèm `README` mô tả schema phiên bản, giấy phép, cách trích dẫn.

## 12. Quy trình đổi schema

**Đóng băng schema v1.** Sau khi 10 hồ sơ thật đi hết vòng nguồn → nhập liệu → dị bản → chú giải → kiểm tra → hiển thị (T-006, T-010), schema được chỉnh một lần theo các điểm đau thực tế rồi **đóng băng thành v1**. Từ đó chỉ đổi khi có **hồ sơ thật không biểu diễn hợp lý được** bằng schema hiện có. Không thêm trường, nhóm từ vựng hay giá trị chỉ vì "sau này có thể cần". Rà từ vựng kiểm soát một lần ở mốc 60 hồ sơ.

1. Ghi quyết định vào `01` (D-xxx), kèm mã hồ sơ thật cần thay đổi này.
2. Sửa tài liệu này, `scripts/kiem-tra-du-lieu.mjs`, `templates/`, và schema Zod (khi đã có Astro) trong cùng một commit.
3. Thêm hoặc sửa fixture trong `tests/fixtures/`; chạy `npm run test:fixtures`.
4. Đổi tên trường đã có dữ liệu: viết script chuyển đổi, chạy trên toàn bộ `content/`, không sửa tay từng tệp.
5. Tăng "Phiên bản schema" ở đầu tài liệu.
