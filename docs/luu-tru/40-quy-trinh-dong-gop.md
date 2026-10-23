# 40 · Quy trình đóng góp

| | |
|---|---|
| Phiên bản | 1.1 · 05/10/2026 |
| Trạng thái | **Ổn định.** Ngưỡng ở mục 4 chỉnh sau 50 bài gửi đầu tiên |
| Đọc khi | Làm form (T-115), script so khớp (T-202); trước mỗi buổi duyệt hằng tuần |

Nguyên tắc chung: **mở để thu thập, chặt để xuất bản.** Đóng góp không bao giờ sửa trực tiếp văn bản chính; mọi đóng góp đi vào kho dưới dạng dị bản mức C, rồi chỉ được nâng mức bằng bằng chứng.

**Ai đóng góp thật sự:** người giữ tri thức (ông bà, người địa phương như "Bác Bảy") thường **không** tự điền form. Người gửi là **người ghi hộ**: con cháu, học sinh, sinh viên, giáo viên. Vì vậy form và lời kêu gọi viết cho người ghi hộ — **"Hãy ghi lại một câu ông bà bạn còn nhớ."** — và luôn tách *người kể* khỏi *người gửi*.

---

## 1. Năm nguyên tắc

1. **Tách lớp, không trộn.** Đóng góp → dị bản C trong khối "Cộng đồng ghi nhận". Không thay Bản tham chiếu.
2. **Bằng chứng trước, uy tín sau.** Nâng mức dựa vào nguồn in, ghi âm, xác nhận độc lập có yếu tố kiểm được; không dựa vào "người này có uy tín" hay số lượt đồng ý.
3. **Ma sát thấp khi gửi, kiểm soát chặt khi nâng mức.** Gửi trong vài phút, không cần tài khoản.
4. **Minh bạch.** Quy trình công khai ở `/phuong-phap`; người gửi biết đóng góp của mình đi đâu.
5. **Ghi công theo ý người gửi:** tên thật, bút danh hoặc ẩn danh.

## 2. Đặc tả form

Dịch vụ form có sẵn (Tally hoặc Google Forms), không tài khoản. Nút "Ở quê bạn câu này nói khác?" trên trang tác phẩm mở form với mã tác phẩm điền sẵn.

| # | Câu hỏi | Kiểu | Bắt buộc | Gợi ý trong ô |
|---|---|---|---|---|
| 1 | Bạn muốn gửi gì? | Chọn 1: dị bản / bài chưa có trong kho / báo lỗi / nguồn sách / ghi âm | ✔ | |
| 2 | Mã tác phẩm | Ẩn, điền sẵn | | |
| 3 | Lời bạn biết | Đoạn văn | ✔ (trừ báo lỗi) | "Ghi đúng như bạn nhớ, mỗi câu một dòng" |
| 4 | Bạn nghe ở đâu? | Văn bản ngắn | ✔ (dị bản) | "Tên làng/xã, tỉnh mà người kể quen gọi, kể cả tên cũ" |
| 5 | Ai là người kể, và người đó là gì với bạn? | Văn bản ngắn | ✔ (dị bản) | "Ví dụ: bà nội tôi, sinh khoảng 1940" — không cần tên |
| 5b | Bạn nghe/ghi lại khi nào? | Văn bản ngắn | | "Ví dụ: Tết 2026; hồi nhỏ, khoảng 1995" |
| 5c | Người kể có đồng ý cho gửi không? Muốn được ghi thế nào? | Chọn 1: đồng ý, ghi tên người kể / đồng ý, ẩn danh (chỉ ghi vùng, thế hệ) / chưa hỏi được | ✔ (dị bản) | "Chưa hỏi được" thì dự án chỉ lưu nội bộ, không công bố |
| 6 | Trong hoàn cảnh nào? | Chọn nhiều + khác | | Hát ru, hò khi làm việc, hát đối đáp, nói thường, đọc trong sách… |
| 7 | Câu này có trong sách nào không? | Văn bản ngắn | | Tên sách, năm, trang |
| 8 | Ảnh chụp trang sách | Tệp ảnh | ✔ nếu khai có sách | |
| 9 | Báo lỗi: sai ở đâu? | Đoạn văn | ✔ (báo lỗi) | "Chữ nào, dòng nào, đúng ra là gì, theo nguồn nào" |
| 10 | Tên hiển thị khi ghi công | Văn bản ngắn | | Để trống = "Một người đóng góp" |
| 11 | Hiển thị tên như thế nào? | Chọn 1: tên thật / bút danh / ẩn danh | ✔ | |
| 12 | Email để nhận phản hồi | Email | | "Không bắt buộc. Không bao giờ công khai" |
| 13 | Đồng ý giấy phép | Đánh dấu | ✔ | "Tôi đồng ý chia sẻ nội dung này theo giấy phép [Q-003]" |

Ghi âm **không** nhận qua form chung khi chưa có hồ sơ đồng ý hai lớp; chọn "ghi âm" ở câu 1 thì chuyển tới hướng dẫn ở `41`.

**Phép thử:** một người lớn tuổi không rành công nghệ điền xong trên điện thoại trong 5 phút.

## 3. Bảng tính hàng đợi (riêng tư)

| Cột | Ý nghĩa |
|---|---|
| `id` | `sub-YYYY-####`, cấp khi duyệt |
| `ngay_gui`, `loai`, `tac_pham_lien_quan` | Từ form |
| `noi_dung`, `dia_danh`, `nguoi_ke`, `boi_canh`, `nguon_in`, `anh_chung_cu` | Từ form |
| `ten_hien_thi`, `kieu_hien_thi`, `email` | Của **người gửi**. Riêng tư; chỉ tên hiển thị được công khai khi người gửi đồng ý |
| `nguoi_ke_quan_he`, `thoi_diem`, `dong_y_nguoi_ke` | Của **người kể** (câu 5, 5b, 5c). `chua_hoi` → không công bố |
| `trang` | Đường dẫn trang nơi người dùng bấm gửi (điền sẵn) |
| `ket_qua_so_khop` | Script điền (mục 4) |
| `trang_thai` | Đóng góp: `moi` · `dang_duyet` · `xuat_ban_C` · `da_nang_muc` · `tu_choi` · `trung` · `nghi_rac`. **Báo lỗi:** `moi` → `da_phan_loai` → `chap_nhan` / `tu_choi` → `da_sua` (ghi mã commit ở `ly_do`) → `dong` (đã phản hồi nếu có email) |
| `ly_do` | Lý do từ chối hoặc ghi chú duyệt |
| `da_phan_hoi` | Đã gửi thư (nếu có email) |

## 4. Script so khớp (`scripts/so-khop-dong-gop.py`, T-202)

Chạy mỗi tuần trên bản xuất CSV của bảng tính:

1. **Chuẩn hóa:** NFC, chữ thường, bỏ dấu câu; tạo thêm bản không dấu.
2. **Tách âm tiết** theo khoảng trắng.
3. **Tìm ứng viên:** độ tương đồng Jaccard trên tập cặp âm tiết liền kề (bigram) của bản không dấu, so với mọi dị bản trong `content/tac-pham/`.
4. **Xếp hạng:** 3 ứng viên cao nhất, ghi vào `ket_qua_so_khop`.
5. **Gợi ý nhãn:**
   - ≥ 0,9 với một dị bản đã có → `trung`
   - 0,4 – 0,9 → "dị bản của <id>"
   - < 0,4 → "bài mới"
   - < 4 âm tiết, có liên kết, hoặc lặp ký tự bất thường → `nghi_rac`
6. Ngưỡng là điểm khởi đầu; chỉnh sau 50 bài gửi đầu tiên (ghi vào `01`).

Script **chỉ gợi ý**, không bao giờ sửa `content/`. Quyết định luôn do người duyệt.

## 5. Duyệt hình thức hằng tuần (1–2 giờ, theo lô)

Với mỗi bài gửi, kiểm:
- Có phải văn học dân gian không (không phải thơ có tác giả, không phải câu chế)?
- Lời đủ ý, đọc được không?
- Địa danh, người kể đủ để ghi nhận không?
- Có nội dung xúc phạm, quảng cáo, thông tin cá nhân của người khác không?

**Đạt** → cấp mã `sub-…`, tạo dị bản mức C trong tệp tác phẩm (`dong_gop: sub-…`, `nguoi_ke` theo câu 5, **không** ghi tên thật nếu người gửi hoặc người kể chọn ẩn danh); cập nhật bảng tính. Câu 5c là "chưa hỏi được" → chỉ lưu trong bảng tính, không tạo dị bản công khai.
**Bài chưa có trong kho** → thêm vào danh sách ưu tiên; không tạo tác phẩm mới chỉ từ một bài gửi (tác phẩm cần bản tham chiếu A/B).
**Báo lỗi** → xử lý trước mọi loại khác; mỗi báo lỗi gắn được với `tac_pham_lien_quan` + `trang` và đi hết vòng đời ở mục 3. Báo lỗi được chấp nhận có thể chuyển thành issue trên GitHub để theo dõi cùng mã nguồn (người dùng không cần tài khoản GitHub).
**Không đạt** → ghi lý do; gửi thư nếu có email.

## 6. Nâng và hạ mức

| Từ | Lên | Điều kiện (chỉ cần một) |
|---|---|---|
| C | A | Người vận hành hoặc cố vấn đối chiếu được với nguồn in, ghi số trang |
| C | B | Có ghi âm từ người kể gốc kèm hồ sơ đồng ý; **hoặc** phiếu điền dã theo mẫu từ đối tác; **hoặc** ≥ 2 người gửi độc lập (khác email, khác thời điểm) cùng vùng văn hóa, cùng lời, **và** có thêm một yếu tố kiểm được: người kể có thế hệ và địa danh cụ thể, ghi âm, hoặc cố vấn phụ trách mảng xác nhận |
| bất kỳ | cờ nghi gán nhầm | Có căn cứ cụ thể là sáng tác hiện đại hoặc gán nhầm (`11` mục 8) |
| bất kỳ | hạ mức | Phát hiện sai nguồn, số trang không khớp, ghi âm không có đồng ý hợp lệ |

**Không bao giờ:** nâng mức vì số lượt đồng ý; nâng mức vì người gửi là chuyên gia; coi 100 người gửi giống nhau là mức A.

Mọi lần nâng/hạ mức ghi lý do trong nội dung commit.

## 7. Cam kết thời gian (công khai)

| Loại | Thời gian |
|---|---|
| Báo lỗi | ≤ 7 ngày |
| Dị bản, nguồn sách | ≤ 30 ngày |
| Ghi âm | ≤ 30 ngày |
| Ca cần cố vấn | ≤ 60 ngày |

## 8. Ghi công

- Trên trang tác phẩm: "Đóng góp bởi [tên hiển thị] · [vùng]" dưới dị bản C/B tương ứng, theo `kieu_hien_thi`.
- Trang "Những người giữ lửa" (P1): danh sách tên hiển thị, vai trò, số đóng góp đã xuất bản; **không** xếp hạng, **không** bảng thành tích.
- Người đóng góp nhiều được mời vào nhóm hiệu đính tình nguyện (đề xuất, không tự xuất bản).

## 9. Mẫu thư phản hồi

**Đã nhận và xuất bản (mức C):**
> Cảm ơn bạn đã gửi cách nói ở [địa danh] cho câu "[dòng đầu]". Lời bạn gửi đã có trên trang, trong mục "Cộng đồng ghi nhận": [đường dẫn]. Nếu sau này bạn tìm thấy câu này trong một cuốn sách, hoặc ghi âm được người thân đọc/hát, hãy gửi thêm để chúng tôi có thể đối chiếu.

**Trùng với bản đã có:**
> Cảm ơn bạn! Cách nói bạn gửi đã có trong kho: [đường dẫn]. Nếu bạn có thể ghi âm người thân đọc hoặc hát câu này bằng giọng quê, đó sẽ là tư liệu rất quý. Hướng dẫn ở đây: [đường dẫn].

**Cần thêm thông tin:**
> Cảm ơn bạn đã gửi "[dòng đầu]". Để ghi nhận, chúng tôi cần biết thêm bạn nghe câu này ở vùng nào và từ ai (không cần tên, ví dụ "bà ngoại, sinh khoảng 1945"). Bạn có thể trả lời thư này.

**Chưa thể ghi nhận:**
> Cảm ơn bạn đã gửi "[dòng đầu]". Câu này có dấu hiệu là [sáng tác có tác giả / câu mới phổ biến gần đây], nên chưa đưa vào kho ca dao. Nếu bạn biết nguồn cho thấy câu này là dân gian, hãy cho chúng tôi biết.

## 10. Chống spam và khiếu nại

- Không xuất bản tự động; script đánh dấu `nghi_rac`; bật chống bot của dịch vụ form.
- Địa chỉ liên hệ công khai ở `/ve-du-an`.
- Yêu cầu gỡ tên: xử lý trong 7 ngày, không cần lý do.
- Khiếu nại bản quyền: gỡ tạm trong khi xem xét (`30` mục 3).
