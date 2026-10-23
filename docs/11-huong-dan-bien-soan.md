# 11 · Hướng dẫn biên soạn

| | |
|---|---|
| Phiên bản | 1.1 · 05/10/2026 |
| Trạng thái | **Ổn định**, trừ mục 9 (chờ Q-004) |
| Đọc khi | Trước khi biên soạn hồ sơ đầu tiên; mỗi khi phân vân cách viết |
| Dành cho | Người biên soạn. AI agent đọc để biết giới hạn của việc soạn nháp |

Mục tiêu của một hồ sơ: người đọc **tin** được lời, **hiểu** được trong 10 giây, và **cảm** được bài ca dao gắn với đời sống của mình.

---

## 1. Quy trình cho một hồ sơ (mục tiêu ~40 phút)

| Bước | Việc | Thời gian |
|---|---|---|
| 1 | Chọn bài tiếp theo trong `data/danh-sach-uu-tien.csv` | 2 phút |
| 2 | Thu thập: đối chiếu lời trong **≥ 2 nguồn in**, ghi số trang; chụp ảnh trang làm bằng chứng nội bộ (không đưa lên kho công khai) | 10 phút |
| 3 | (Tùy chọn) Dùng AI soạn nháp **từ chính các đoạn trích đã thu thập**, theo `templates/prompt-soan-nhap.md` | 3 phút |
| 4 | Kiểm chứng từng khẳng định; xóa mọi thứ không có nguồn; viết lại mọi chuỗi `NHAP_AI` bằng lời của dự án | 15 phút |
| 5 | Viết `mo_ta_tinh_huong`; chọn chủ đề, cảm xúc, mô-típ; thêm tác phẩm vào tệp tình huống kèm "Hợp khi…" | 5 phút |
| 6 | `npm run check:data`; xem trước trên điện thoại | 3 phút |
| 7 | Đặt `cho_kiem_dinh`. **Đọc lại sau ít nhất 24 giờ** với "mắt tươi", rồi đặt `da_xuat_ban` và điền `ngay_kiem_dinh` | 2 phút |

**Bấm giờ 10 hồ sơ đầu theo từng phần**, ghi vào các cột tương ứng của `data/danh-sach-uu-tien.csv`:

| Cột | Gồm | Bước |
|---|---|---|
| `phut_tim_nguon` | Tìm sách, tìm đúng trang, tìm dị bản | 1–2 |
| `phut_xac_minh` | Đối chiếu từng chữ giữa các nguồn, xác minh thông tin thư mục | 2, 4 |
| `phut_nhap_lieu` | Tạo tệp YAML, dị bản, nguồn | 3–4 |
| `phut_chu_giai` | Chú giải, mục từ, nghĩa, tầng đời thường | 4–5 |
| `phut_qa` | Kiểm tra máy, xem trước, đọc lại sau 24 giờ | 6–7 |

Tổng trung bình quyết định kịch bản lộ trình (`00` mục 7). **Tỉ lệ giữa các phần quyết định nên tối ưu gì trước:** nếu tìm nguồn + xác minh chiếm phần lớn, ưu tiên giải bài toán nguồn (sắp sẵn sách, danh mục trang) thay vì tối ưu công cụ nhập liệu.

## 2. Chọn bản tham chiếu

Thứ tự ưu tiên khi có nhiều dị bản mức A/B:

1. Bản thống nhất giữa sách giáo khoa hiện hành và các tuyển tập đã đối chiếu.
2. Bản xuất hiện trong nhiều tuyển tập uy tín nhất.
3. Bản trọn vẹn hơn (đủ các cặp câu) khi các bản khác chỉ là trích đoạn.

`ly_do_chon` là **một câu nêu căn cứ**, ví dụ: "Bản phổ biến nhất, thống nhất giữa SGK và hai tuyển tập đã đối chiếu." Không viết "bản đúng", "bản gốc", "bản chuẩn".

Chưa có dị bản nào đạt A/B thì tác phẩm ở lại `nhap`. Bài chỉ sống trong truyền miệng chưa có ghi âm nằm trong hàng đợi chờ chiến dịch ghi âm.

## 3. Viết nghĩa

**`nghia_ngan`** (hiện ở khối "Hiểu trong 10 giây", kết quả tìm, thẻ chia sẻ):
- Một câu, nên ≤ 25 từ, tối đa 40.
- Nói **bài này nói gì**, không nói "bài này dạy ta bài học sâu sắc".
- Có nhiều cách hiểu: `nghia_ngan` nêu phần các cách hiểu cùng chấp nhận; nếu không có phần chung, viết thẳng "Câu này có nhiều cách hiểu: …" và để chi tiết ở `dien_giai`. Không chọn một cách hiểu làm "đúng" khi tài liệu chưa thống nhất.

**`dien_giai`** (nghĩa và cách hiểu):
- Mỗi góc nhìn một mục, có nguồn. Góc nhìn đầu tiên luôn là "Nghĩa phổ thông".
- Góc nhìn khác (văn hóa học, ngôn ngữ học…) chỉ thêm khi có tài liệu nói như vậy.
- Cách hiểu còn tranh luận: ghi "Có ý kiến cho rằng… (nguồn)", và cân nhắc đặt `tranh_luan: true`.

**Tục ngữ:** điền `nghia_den`; có nghĩa bóng thì điền `nghia_bong`. Tục ngữ kinh nghiệm (thời tiết, nông nghiệp, sức khỏe) có thể thêm `goc_kiem_chung`; để `chua_ro` khi chưa có nguồn khoa học dẫn được.

## 4. Viết tầng đời thường

**`mo_ta_tinh_huong`** (≥ 2 câu): viết như người thường nói, ngôi thứ hai.
- Tốt: "Khi bạn muốn nói lời biết ơn cha mẹ mà không biết diễn đạt thế nào."
- Không tốt: "Thể hiện đạo lý hiếu nghĩa trong văn hóa truyền thống."

**"Hợp khi…"** (trong tệp tình huống): một câu cho biết bài này hợp với hoàn cảnh nào **trong tình huống đó**.
- Chỉ viết khi bài **thật sự hợp**. Không ghép gượng để tình huống đủ 3 bài.
- Ví dụ khớp gượng cần tránh: ghép "Công cha như núi Thái Sơn" với "thương mẹ nhưng vừa cãi nhau" chỉ vì cùng nói về cha mẹ.

## 5. Chú giải và mục từ

**Phép thử chú giải:** học sinh lớp 7 có thể không hiểu hoặc hiểu sai từ này không? Có thì chú giải.
Chú giải: từ cổ, phương ngữ, Hán Việt, điển tích, địa danh, nhân vật, phong tục, đồ vật cũ. Không chú giải từ phổ thông ("chiều", "đứng") trừ khi có nghĩa riêng.

**Cách khai chú giải:** gõ cụm từ đúng như trong lời: `{ di_ban: v1, cum_tu: "Thái Sơn", muc_tu: lx-thai-son }`. Không cần đếm dòng hay âm tiết. Chỉ khi máy báo "xuất hiện N lần" mới thêm `dong` (cụm từ ở nhiều dòng) hoặc `lan` (lặp trong cùng dòng).

**Trước khi tạo mục từ mới:** tìm trong `content/muc-tu/` (kể cả `bien_the`). Đã có thì trỏ tới; nghĩa trong bài khác nghĩa chung thì dùng `nghia_tai_cho`.

**`giai_nghia`** ≤ 40 từ, có nguồn từ điển hoặc sách tham khảo. **`giai_nghia_day_du`** cho trang từ điển: có thể dài hơn, vẫn phải có nguồn.

## 6. Âm luật và diễn xướng

- **`am_luat`:** chỉ điền khi người biên soạn tự kiểm từng tiếng (ví dụ cách ngắt nhịp, cặp vần). Không chép luật bằng trắc do AI sinh ra mà không kiểm.
- **`dien_xuong`:** chỉ ghi hình thức diễn xướng có căn cứ. Không rõ thì `khong_ro`. **Không bịa bối cảnh cho đủ trường.**

## 7. Bài có định kiến xưa

Một số ca dao, tục ngữ phản ánh định kiến (giới, vùng miền, nghề nghiệp, dân tộc):
- **Giữ nguyên văn bản** như tư liệu.
- Viết `canh_bao_noi_dung`: giải thích bối cảnh xã hội, không phán xét áp đặt, không lờ đi.
- **Không** gắn vào tình huống mang tính khuyên dùng (máy kiểm R11); **không** cho tạo thẻ chia sẻ (giao diện kiểm, `20` mục 7).

## 8. Nghi không phải dân gian

Đặt `xac_thuc_dan_gian: nghi_gan_nham` hoặc `phong_tac_hien_dai` **chỉ khi có căn cứ cụ thể** (tác giả đã biết, lần xuất hiện đầu gần đây, bản dịch từ tiếng nước ngoài…), ghi ở `can_cu_xac_thuc_dan_gian`. Một lỗi ở loại nhãn này gây hại uy tín gấp nhiều lần; chưa chắc thì để `binh_thuong` và ghi chú nội bộ.

## 9. Văn phong

| Nên | Không nên |
|---|---|
| Câu ngắn, rõ, như kể cho một người bạn hiểu biết | Văn mẫu: "Câu ca dao đã để lại cho chúng ta bài học vô cùng sâu sắc…" |
| "Có ý kiến cho rằng… (nguồn)" khi nêu cách hiểu khác | Khẳng định tuyệt đối khi giới nghiên cứu còn bàn |
| Giải thích bối cảnh xã hội xưa | Phán xét đạo đức áp đặt |
| Tầng đời thường: ngôi thứ hai, từ ngữ hằng ngày | Từ hàn lâm trong phần dành cho người trẻ |
| Ghi "chưa rõ" khi chưa rõ | Bịa bối cảnh cho đủ trường |
| "Bản tham chiếu", "dị bản", "cộng đồng ghi nhận" | "Bản gốc", "bản đúng", "bản chuẩn", "bản sai" |
| Tôn trọng giọng địa phương | Chế giễu hoặc "sửa" phương ngữ |

**Quy ước bỏ dấu** cho văn bản dự án tự viết: **chờ Q-004.** Lời trích từ sách luôn giữ nguyên bản in.

## 10. Quy tắc dùng AI

1. AI **chỉ** làm việc trên đoạn nguồn người biên soạn đưa vào lệnh.
2. AI **không bao giờ** là nguồn. Không ghi "theo AI…" ở bất cứ đâu.
3. Mọi tên sách, số trang, niên đại, địa danh, vùng do AI đưa ra mà không có trong đoạn nguồn: **xóa**.
4. Mọi chuỗi AI viết mang tiền tố `NHAP_AI` cho tới khi người biên soạn viết lại. Máy chặn xuất bản nếu còn.
5. AI không được đặt mức A/B, không được đặt trạng thái khác `nhap`.
6. Lệnh mẫu lưu ở `templates/prompt-soan-nhap.md`; sửa lệnh thì ghi vào `01`.

## 11. Danh sách kiểm định trước khi xuất bản

Máy kiểm phần có mã R; người kiểm phần còn lại.

- [ ] Lời bản tham chiếu khớp từng chữ với ≥ 1 nguồn in, có số trang. (R08, R17)
- [ ] Mỗi dị bản có nguồn, vùng, mức bằng chứng. (R02, R08)
- [ ] Mọi từ học sinh lớp 7 có thể không hiểu đều có chú giải; mỗi chú giải trỏ mục từ có nguồn. (R06, R09)
- [ ] `nghia_ngan` một câu, không sáo rỗng, ≤ 40 từ. (R07)
- [ ] Mỗi cách hiểu trong `dien_giai` có nguồn; cách hiểu tranh luận được nói rõ. (R10, R14)
- [ ] Không có khẳng định niên đại, tác giả, địa danh gốc, bối cảnh diễn xướng không có căn cứ.
- [ ] ≥ 2 câu `mo_ta_tinh_huong` viết như người thường nói; thuộc ≥ 1 tình huống với "Hợp khi…" thật sự hợp. (R10)
- [ ] Bài có định kiến xưa có `canh_bao_noi_dung` và không nằm trong tình huống khuyên dùng. (R11)
- [ ] Không còn `CAN_BO_SUNG` / `NHAP_AI`. (R09)
- [ ] Xem trước trên điện thoại: lời xuống dòng đúng, chú giải mở được, thẻ chia sẻ đúng dấu.
- [ ] Đọc lại sau ≥ 24 giờ.
