# Phiếu duyệt pilot

| | |
|---|---|
| Trạng thái | **Bản v1 · 21/11/2026 · chờ người sáng lập duyệt** cùng [Sổ tay biên tập](so-tay-bien-tap.md) |
| Cách dùng | Mỗi bài pilot một dòng trong một bảng tính (Google Sheets hoặc Excel). Bảng đã điền **giữ ngoài kho mã**; trong kho mã chỉ ghi số tổng hợp |
| Không ghi | Lời, chú thích, tên thật, email hay liên lạc của bất kỳ ai. Người duyệt, người kiểm ghi bằng mã (vd. N1, K1) |

## Các cột

| Cột | Ai điền | Giá trị |
|---|---|---|
| `ma` | Agent | P001, P002, … |
| `dot` | Agent | 1 hoặc 2 |
| `duong_dan` | Agent | Đường dẫn công khai của bài |
| `the_loai` | Agent | ca-dao · tuc-ngu · cau-do · dong-dao · ve |
| `nhom` | Agent | `thu` (có nháp AI) · `doi_chung` (người viết, không AI) · `danh_gia_lai` (bài đã đủ điều kiện lập chỉ mục từ trước) |
| `cach_soan` | Agent | `S0` người viết; `S1`, `S2`… cách soạn có AI (mô tả ở báo cáo benchmark) |
| `truoc_muc` | Người duyệt | Mức trước khi sửa: H0 · H1 · H2 · H3 |
| `truoc_du_dieu_kien` | Agent | `co` · `khong` — đủ điều kiện lập chỉ mục trước khi sửa |
| `truoc_google` | Người sáng lập | `co` · `khong` · `chua_do` — theo Search Console |
| `phan_lam` | Người duyệt | Phần đã thêm hoặc sửa, cách nhau bằng dấu chấm phẩy: y_nghia; nghia_den; giai_thich; goi_y; tu_kho; boi_canh; hinh_anh; chuyen_quanh |
| `muc_sua` | Người duyệt | Với nháp AI: `khong_sua` · `sua_nhe` (đổi vài chữ, giữ ý) · `sua_nang` (đổi ý hoặc viết lại phần lớn) · `bo` (không dùng nháp). Với S0: để trống |
| `phut` | Người duyệt | Số phút, từ lúc mở bài tới lúc lưu xong phần cuối |
| `c1` … `c4` | Người duyệt | Bốn câu hỏi ở sổ tay mục 2: `dat` · `chua_dat` · `khong_can` |
| `ket_luan` | Người duyệt | `H2` · `H3` · `chua_dat` |
| `ly_do` | Người duyệt | Khi chưa đạt; hoặc "cần tra" |
| `nguoi_duyet` | Người duyệt | Mã người duyệt |
| `nguoi_kiem` | Người kiểm | Mã người kiểm — khác người duyệt |
| `loi_nt` | Người kiểm | Số lỗi nghiêm trọng; mô tả ngắn sau dấu hai chấm (vd. `1: sai nghĩa từ "…"`) |
| `loi_lon` | Người kiểm | Số lỗi lớn, cách ghi như trên |
| `loi_nho` | Người kiểm | Số lỗi nhỏ |
| `loi_quy_trinh` | Người duyệt hoặc người kiểm | Công cụ chặn, mất chữ, nháp nhầm bài… |
| `sau_du_dieu_kien` | Agent | `co` · `khong` — sau khi duyệt |
| `sau_google` | Người sáng lập | `co` · `khong` · `chua_do` — đo khi xong đợt 2 và sau 8 tuần |
| `ghi_chu` | Ai cũng được | Chỗ vướng, mệt, cần tra sách |

## Dòng đầu của bảng tính

Dán dòng này vào ô A1, rồi chọn Dữ liệu → Tách văn bản thành cột (dấu phẩy):

```text
ma,dot,duong_dan,the_loai,nhom,cach_soan,truoc_muc,truoc_du_dieu_kien,truoc_google,phan_lam,muc_sua,phut,c1,c2,c3,c4,ket_luan,ly_do,nguoi_duyet,nguoi_kiem,loi_nt,loi_lon,loi_nho,loi_quy_trinh,sau_du_dieu_kien,sau_google,ghi_chu
```

## Agent tổng hợp gì sau mỗi đợt

- Tỷ lệ đạt H2 và H3, theo nhóm (`thu`, `doi_chung`, `danh_gia_lai`) và theo thể loại.
- Lỗi theo loại trên tổng số bài; đối chiếu với cổng ở sổ tay mục 8.
- Số phút người duyệt (trung vị) theo thể loại và nhóm.
- Tỷ lệ không sửa, sửa nhẹ, sửa nặng, bỏ của nháp AI, theo cách soạn.
- Lỗi quy trình và chỗ vướng gặp nhiều nhất.

Không báo token, chi phí hay so sánh mô hình (`docs/57` mục 5.2).
