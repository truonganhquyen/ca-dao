# 11 · Hướng dẫn biên soạn

| | |
|---|---|
| Phiên bản | 2.0 · 23/10/2026 (biên soạn qua CMS, nội dung từ đường ống thu thập; D-036, D-039, D-042, D-045) |
| Trạng thái | **Ổn định** |
| Đọc khi | Trước khi duyệt bài đầu tiên; mỗi khi phân vân cách viết |
| Dành cho | Người biên soạn (làm việc trong CMS ở `/admin`). AI agent đọc để biết giới hạn của việc soạn nháp |

Mục tiêu của một bài: người đọc **biết** lời lấy từ đâu và đã kiểm đến đâu, **hiểu** được trong 10 giây, và **cảm** được bài ca dao gắn với đời sống của mình. Bài được công khai sớm và **cải tiến dần** — không cần hoàn hảo ngay (D-039).

---

## 1. Quy trình cho một bài (mục tiêu 10–15 phút)

Cách bấm trong CMS (đăng nhập, Thêm nhanh, công khai, thêm vào tình huống, đọc lỗi): `docs/huong-dan/bien-soan-cms.md`.

Đường ống (T-039) đã chuẩn bị sẵn bài nháp: lời lấy nguyên văn từ nguồn có URL, bản phổ biến nhất được đề xuất làm bản tham chiếu, cụm từ gợi ý chú giải, nghĩa ngắn và mô tả tình huống do AI soạn nháp (mang `NHAP_AI`). Việc của người biên soạn là **đọc, sửa, quyết định**.

| Bước | Việc trong CMS | Thời gian |
|---|---|---|
| 1 | Mở bài nháp từ danh sách (hoặc Bàn biên tập). Đọc lời; mở link nguồn để so nhanh | 2 phút |
| 2 | Lời có chỗ nghi sai chữ? Ghi `ghi_chu`, **không tự sửa lời** khi chưa có nguồn khác. Chọn bản tham chiếu nếu có nhiều bản; viết `ly_do_chon` một câu | 2 phút |
| 3 | Đọc nghĩa ngắn và mô tả tình huống AI soạn: sửa câu nào chưa ổn; xóa câu nào không có căn cứ | 3 phút |
| 4 | Chú giải: giữ cụm từ thật sự khó với học sinh lớp 7; với mục từ mới, đọc ý của chú thích tham chiếu rồi **viết lại bằng lời của mình** (mục 5) | 4 phút |
| 5 | Thêm bài vào một tình huống nếu thật sự hợp (trong bộ "Tình huống" của CMS) | 1 phút |
| 6 | Đánh dấu **"Tôi đã đọc và đồng ý phần AI soạn"** (`nhap_ai_da_duyet`), đổi trạng thái sang **Công khai**, lưu | 1 phút |
| 7 | Mở bản xem trước trên điện thoại sau vài phút; có lỗi kiểm tra thì GitHub gửi email và Bàn biên tập hiện lý do | 1 phút |

**Thêm nhanh:** nghe ai đọc một câu hay mà kho chưa có, dùng bộ "Thêm nhanh" (tiêu đề, lời, vùng, nguồn nếu có) để lưu nháp ngay; làm giàu sau. Bài chưa có nguồn gốc nào thì chưa công khai được (R21) — khi đó ghi người kể vào `nguoi_ke` và chờ nguồn hoặc đóng góp.

**Bài công khai còn thiếu phần** (chưa có chú giải, chưa thuộc tình huống…) vẫn lên trang, kèm dòng "Bài đang được bổ sung". Bài chưa có nghĩa ngắn và chú giải/tình huống thì tự mang `noindex` — Google chưa thấy cho tới khi bài có giá trị riêng.

## 2. Chọn bài cho MVP (100–150 bài)

Kho thô có hàng nghìn bài; chỉ xuất bản có chọn lọc (D-045). Thứ tự ưu tiên:

| Tầng | Nội dung | Vì sao |
|---|---|---|
| T1 | Ca dao, tục ngữ trong SGK Ngữ văn hiện hành (3 bộ sách) | Giáo viên là kênh vào mạnh nhất |
| T2 | Bài cho 20 tình huống ra mắt (`data/tinh-huong-khoi-dau.csv`, cột `ra_mat = co`), mỗi tình huống ≥ 2 bài | Tìm theo cảm xúc cần tình huống có bài |
| T3 | Bài theo dịp sắp tới: Tết Đinh Mùi 2027, 8/3, Vu Lan | Mở công khai trước Tết |
| T4 | Trọn chùm mô-típ tiêu biểu ("Thân em như…", "Chiều chiều…", "Công cha… nghĩa mẹ…") | Cho thấy dị bản và mô-típ |

**Không chọn cho MVP:** bài trào phúng hiện đại, bài nhắc nhân vật/sự kiện thế kỷ 20, nội dung thô tục (đường ống gắn cờ sẵn). Bài có định kiến xưa chọn được nếu có `canh_bao_noi_dung` và không gắn tình huống.

Sau khi mở công khai: hằng tháng sắp xếp lại theo **truy vấn không có kết quả**, tình huống còn thiếu bài và bài được xem nhiều mà còn nhãn D (ưu tiên tìm nguồn in để nâng nhãn).

## 3. Chọn bản tham chiếu

Thứ tự ưu tiên khi có nhiều bản:

1. Bản có nhãn A/B (đã đối chiếu tư liệu / ghi từ người kể).
2. Bản thống nhất giữa SGK và nhiều nguồn.
3. Bản xuất hiện ở nhiều nguồn độc lập nhất (lưu ý: các trang web hay chép lại nhau — năm trang giống nhau không bằng một bản in).
4. Bản trọn vẹn hơn (đủ các cặp câu) khi các bản khác chỉ là trích đoạn.

`ly_do_chon` là **một câu nêu căn cứ**, ví dụ: "Bản phổ biến nhất trong các nguồn đã thấy." Không viết "bản đúng", "bản gốc", "bản chuẩn".

## 4. Viết nghĩa

**`nghia_ngan`** (khối "Hiểu trong 10 giây", kết quả tìm, mô tả trên Google):
- Một câu, nên ≤ 25 từ, tối đa 40.
- Nói **bài này nói gì**, không nói "bài này dạy ta bài học sâu sắc".
- Có nhiều cách hiểu: nêu phần các cách hiểu cùng chấp nhận; nếu không có phần chung, viết thẳng "Câu này có nhiều cách hiểu: …". Không chọn một cách hiểu làm "đúng" khi tài liệu chưa thống nhất.

**`dien_giai`** (nghĩa và cách hiểu):
- Mỗi góc nhìn một mục. Góc nhìn đầu tiên luôn là "Nghĩa phổ thông".
- Dựa vào ý của nguồn nào thì ghi nguồn đó (`cach_dung: tham_khao`). Góc nhìn chuyên môn (văn hóa học, ngôn ngữ học…) chỉ thêm khi có tài liệu nói như vậy.
- Cách hiểu còn tranh luận: "Có ý kiến cho rằng… (nguồn)", cân nhắc `tranh_luan: true`.

**Tục ngữ:** điền `nghia_den`; có nghĩa bóng thì điền `nghia_bong`. Tục ngữ kinh nghiệm có thể thêm `goc_kiem_chung`; để `chua_ro` khi chưa có nguồn khoa học dẫn được.

## 5. Chú giải và mục từ

**Phép thử chú giải:** học sinh lớp 7 có thể không hiểu hoặc hiểu sai từ này không? Có thì chú giải. Chú giải: từ cổ, phương ngữ, Hán Việt, điển tích, địa danh, nhân vật, phong tục, đồ vật cũ. Không chú giải từ phổ thông.

**Cách khai:** gõ cụm từ đúng như trong lời: `{ di_ban: v1, cum_tu: "Thái Sơn", muc_tu: lx-thai-son }`. Chỉ khi máy báo "xuất hiện N lần" mới thêm `dong` hoặc `lan`.

**Trước khi tạo mục từ mới:** tìm trong danh sách mục từ (kể cả `bien_the`). Đã có thì trỏ tới; nghĩa trong bài khác nghĩa chung thì dùng `nghia_tai_cho`.

**Viết giải nghĩa từ chú thích tham chiếu (D-046):**
- Đọc chú thích của Ca dao Mẹ (hoặc nguồn khác) để **hiểu ý**, rồi **viết lại bằng lời của mình**; ghi nguồn với `cach_dung: tham_khao`. Trang sẽ hiện "Tham khảo: Ca dao Mẹ ↗".
- **Chép nguyên văn** (`cach_dung: trich_nguyen_van`) chỉ khi: nguồn đã cho phép bằng văn bản, **hoặc** là định nghĩa rất ngắn (≤ 15 từ) mà cách viết đúng nào cũng gần như trùng. Máy chặn trường hợp khác (R20) — kể cả ở bài nháp, vì kho mã là công khai.
- Không bao giờ chép ảnh hay đoạn bình dài.
- `giai_nghia` ≤ 40 từ. `giai_nghia_day_du` cho trang từ điển: viết bằng lời của dự án.

## 6. Âm luật và diễn xướng

- **`am_luat`:** chỉ điền khi tự kiểm từng tiếng. Không chép luật bằng trắc do AI sinh ra mà không kiểm.
- **`dien_xuong`:** chỉ ghi hình thức diễn xướng có căn cứ. Không rõ thì `khong_ro`. **Không bịa bối cảnh cho đủ trường.**

## 7. Bài có định kiến xưa

- **Giữ nguyên văn bản** như tư liệu.
- Viết `canh_bao_noi_dung`: giải thích bối cảnh xã hội, không phán xét áp đặt, không lờ đi.
- **Không** gắn vào tình huống (R11 chặn) — bài vẫn công khai được mà không cần tình huống.

## 8. Nghi không phải dân gian

Đặt `xac_thuc_dan_gian: nghi_gan_nham` hoặc `phong_tac_hien_dai` **chỉ khi có căn cứ cụ thể**, ghi ở `can_cu_xac_thuc_dan_gian`. Chưa chắc thì để `binh_thuong` và ghi chú nội bộ. Các bài trào phúng hiện đại trên trang web nguồn nếu được chọn thì phải mang cờ này kèm căn cứ.

## 9. Văn phong

| Nên | Không nên |
|---|---|
| Câu ngắn, rõ, như kể cho một người bạn hiểu biết | Văn mẫu: "Câu ca dao đã để lại cho chúng ta bài học vô cùng sâu sắc…" |
| "Có ý kiến cho rằng… (nguồn)" khi nêu cách hiểu khác | Khẳng định tuyệt đối khi giới nghiên cứu còn bàn |
| Giải thích bối cảnh xã hội xưa | Phán xét đạo đức áp đặt |
| Tầng đời thường: ngôi thứ hai, từ ngữ hằng ngày | Từ hàn lâm trong phần dành cho người trẻ |
| Ghi "chưa rõ" khi chưa rõ | Bịa bối cảnh cho đủ trường |
| "Bản tham chiếu", "dị bản", "lời phổ biến" | "Bản gốc", "bản đúng", "bản chuẩn", "bản sai" |
| Tôn trọng giọng địa phương | Chế giễu hoặc "sửa" phương ngữ |

**Đặt dấu** cho văn bản dự án tự viết: theo SGK, dấu ở âm chính — **hoà, thuỷ, khoẻ** (D-038). Lời trích luôn giữ nguyên như nguồn.

## 10. Quy tắc dùng AI

1. AI **chỉ** làm việc trên nội dung nguồn được đưa vào lệnh (lời từ kho thô, chú thích tham chiếu).
2. AI **không bao giờ** là nguồn. Không ghi "theo AI…" ở bất cứ đâu.
3. AI **không bao giờ** viết hay sửa lời, dị bản, tên sách, số trang, niên đại, địa danh, vùng (lằn ranh 1).
4. Mọi chuỗi AI viết mang tiền tố `NHAP_AI`. Chỉ lên trang khi người biên soạn đã đọc và đánh dấu `nhap_ai_da_duyet` (D-042); trang khi đó hiện "Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt".
5. AI không được đặt trạng thái khác `nhap`, không đặt `nhap_ai_da_duyet`, không đặt `cach_dung: trich_nguyen_van`.
6. Lệnh mẫu lưu ở `templates/prompt-soan-nhap.md`; sửa lệnh thì ghi vào `01`.

## 11. Danh sách kiểm trước khi công khai

Máy kiểm phần có mã R (tầng LỖI chặn; tầng NHẮC hiện ở Bàn biên tập); người kiểm phần còn lại. Bài được công khai khi các mục **in đậm** đạt — phần còn lại cải tiến dần.

- [ ] **Bản tham chiếu có nguồn gốc ghi rõ; nguồn web có url và ngày truy cập.** (R21, R22)
- [ ] **Đã đọc và đồng ý mọi phần AI soạn.** (R09)
- [ ] **Không có chú thích chép nguyên văn trái quy tắc.** (R20)
- [ ] **Bài có cảnh báo/cờ không nằm trong tình huống.** (R11)
- [ ] `nghia_ngan` một câu, không sáo rỗng, ≤ 40 từ. (R07, R10)
- [ ] Từ khó với học sinh lớp 7 có chú giải; mục từ có nguồn. (R06, R10)
- [ ] Không có khẳng định niên đại, tác giả, địa danh gốc, bối cảnh diễn xướng không có căn cứ.
- [ ] Thuộc ≥ 1 tình huống thật sự hợp (nếu có). (R10)
- [ ] Xem trước trên điện thoại: lời xuống dòng đúng, chú giải mở được, nhãn và dòng nguồn gốc đúng.
