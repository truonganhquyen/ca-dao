# 52 · Chiến lược chất lượng nội dung và mở rộng năng lực biên tập

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Căn cứ (D-127, 21/11/2026)** — kế hoạch đang dùng: [57](57-ke-hoach-chot.md); nơi khác nhau, 57 thắng. Nguyên trạng lúc lập: **Đề xuất, chờ người sáng lập duyệt** ([56](56-goi-quyet-dinh.md) nhóm B, E) |
| Bộ tài liệu | [50 tầm nhìn](50-tai-dinh-vi-chien-luoc.md) · [51 rà soát quyết định](51-danh-gia-lai-quyet-dinh.md) · **52 chất lượng nội dung** · [53 trải nghiệm](53-thiet-ke-lai-trai-nghiem.md) · [54 kiến trúc](54-kien-truc-dich-va-chuyen-doi.md) · [55 lộ trình, backlog](55-lo-trinh-va-backlog.md) · [56 gói quyết định](56-goi-quyet-dinh.md) |
| Ràng buộc giữ nguyên | Ba lằn ranh; nội dung máy soạn chỉ công khai sau khi người duyệt; không bịa nguồn; không đưa kho thô, lời hay chú thích của nguồn vào kho mã công khai |

---

## 1. Mục tiêu

Đưa **Bộ tham chiếu khoảng 3.000 tác phẩm** lên chuẩn tham chiếu trong 6–9 tháng. Đưa **500 bài hạng A** lên mức có đối chiếu sách và thẩm định trong 18 tháng. Giữ **Kho lưu trữ** (phần còn lại của khoảng 16.300 bài) trung thực, sạch lỗi trình bày, đã chống trùng, an toàn cho trẻ.

Cách làm: **máy soạn nháp có căn cứ, người duyệt theo vai, chất lượng đo bằng kiểm mẫu.** Không dùng cách làm "AI viết rồi đăng", cũng không dùng "một người viết tay từng bài".

## 2. Mô hình chất lượng

### 2.1. Cấp biên tập (thay định nghĩa L0–L3 hiện hành)

| Cấp | Tên cho người đọc (đề xuất, thử ở 53) | Điều kiện | Lập chỉ mục | Lối vào nổi bật |
|---|---|---|---|---|
| **L0** | Lưu trữ | Có lời, thể loại; chưa rà | Không | Không |
| **L1** | Lưu trữ · đã rà | Lời sạch lỗi trình bày; trùng đã xử lý; cờ thô tục và "có thể không phải dân gian" đã rà; thể loại và nhãn phụ đã xác nhận | Không | Không (riêng câu đố L1 an toàn được vào Play) |
| **L2** | Đã biên tập | Đạt **chuẩn tham chiếu** (2.2) | Có | Có |
| **L3** | Tham chiếu | L2 + lời đã đối chiếu sách (tr. N) + thẩm định giải nghĩa | Có | Ưu tiên |
| **L4** | Hồ sơ đặc biệt | L3 + bài bình giảng hoặc khảo cứu đã nối, hoặc ảnh / âm thanh có quyền | Có | Ưu tiên |

Cấp do **máy tính từ dữ liệu** (giống `MienDuLieu\CapChatLuong` hiện nay), không ai nhập tay. Định nghĩa mới thay L0–L3 của `docs/00` mục 6 khi được duyệt (51, R-06).

### 2.2. Chuẩn tham chiếu (L2) — tiêu chí

**Chung cho mọi thể loại:**

1. Lời đã rà: không lỗi trình bày (bộ kiểm hiện có), không trùng chưa xử lý, đúng thể loại và nhãn phụ.
2. Cờ đã rà: thô tục; "có thể không phải dân gian" (có lý do khi bật).
3. **Ý nghĩa** đạt Sổ tay biên tập:
   - 2–3 câu, khoảng 30–60 chữ;
   - nói nghĩa ngay, không nhắc lại câu;
   - không tính từ đánh giá sáo rỗng;
   - nhiều cách hiểu thì ghi rõ "có cách hiểu…";
   - không khẳng định sự kiện khi không có nguồn.
4. **Chú giải:** mọi từ cổ, Hán Việt, phương ngữ, địa danh, nhân vật trong lời có chú giải do thư viện viết, có trích nguồn (từ điển, sách, nguồn mở). Không còn chú thích chép từ nguồn chưa đồng ý hiện trên trang (mục 5).
5. **Quan hệ:** ít nhất 2 quan hệ đã duyệt (mô-típ, chủ đề hẹp, hồ sơ thực thể, câu gần nghĩa, cùng họ dị bản).
6. **Nguồn:** dòng nguồn trung thực; mọi phần có người làm và nguồn (đã có trong mô hình).

**Thêm theo thể loại:**

| Thể loại | Bắt buộc thêm ở L2 | Thêm ở L3 / hạng A |
|---|---|---|
| Ca dao | Hình ảnh trong bài khi có ẩn dụ; dị bản trong kho đã gom | Đối chiếu sách; mô-típ; bình giảng khi có |
| Tục ngữ – thành ngữ | Nhãn phụ tục ngữ / thành ngữ; Nghĩa đen khi nghĩa bóng khác xa; "Dùng khi" có ví dụ tự viết | Câu gần nghĩa · trái nghĩa; nguồn gốc khi có căn cứ |
| Đồng dao | Độ tuổi phù hợp; nhịp hoặc cách đọc khi có nguồn | Cách chơi có nguồn; ghi âm có đồng ý |
| Vè | Bối cảnh có nguồn cho **mọi** khẳng định lịch sử; chia đoạn | Nhân vật, địa danh nối hồ sơ; khảo cứu lịch sử |
| Câu đố | Đáp án đã kiểm; Giải thích đáp án; an toàn cho trẻ đã rà | Gợi ý; ảnh đáp án có quyền |

### 2.3. Sổ tay biên tập

Một tài liệu duy nhất, **chuẩn chung cho người duyệt và cho lời nhắc của AI** (nhóm việc E1.4 ở 55). Nội dung:

- ranh giới từng phần: viết gì, không viết gì;
- văn phong;
- cách viết điều chưa chắc;
- bài phản ánh chuẩn mực cũ (tả bối cảnh, không bình đạo lý);
- địa danh theo thời điểm (địa giới thay đổi nhiều sau năm 2025);
- khi nào bật cờ;
- danh sách nguồn được trích.

Mỗi lần sửa Sổ tay có số phiên bản; lời nhắc AI ghi số phiên bản Sổ tay nó dùng.

## 3. Phân tầng 16.000 bài

### 3.1. Điểm ưu tiên biên tập (0–100, máy tính, ghi ở bảng tóm tắt)

| Thành phần | Trọng số | Dữ liệu |
|---|---|---|
| Nhu cầu | 40 | Lượt đọc 90 ngày (thang log), số lần tìm khớp, "Mong có câu này", lượt hiển thị trên Search Console (khi người sáng lập nối) |
| Giá trị văn hoá | 30 | Có trong SGK (người đã thấy sách điền), có trong sách đối chiếu, số dị bản và số nguồn ghi nhận, thuộc mô-típ trọng tâm |
| Cân bằng | 15 | Bù thể loại và chủ đề còn mỏng (vè, đồng dao, câu đố) |
| Sẵn sàng | 15 | Đã có một phần (Ý nghĩa, chú giải tự viết, đối chiếu) |
| Trừ | — | Trùng chưa xử lý; cờ nghi; thô tục (vào hàng riêng) |

Hạng A do **người sáng lập và người thẩm định chọn** từ đầu danh sách điểm cao, cộng danh sách bài kinh điển. Máy không tự đưa bài vào hạng A.

### 3.2. Phân bổ mục tiêu

| Thể loại | Tổng (trang chủ 20/11) | Bộ tham chiếu (L2+) | Trong đó hạng A | Kho lưu trữ (L1) |
|---|---|---|---|---|
| Ca dao | 11.762 | ~1.150 | 220 | phần còn lại |
| Tục ngữ – thành ngữ | 3.487 | ~800 | 150 | phần còn lại |
| Câu đố | 765 | 765 (toàn bộ) | 50 | — |
| Đồng dao | 298 | ~150 | 40 | phần còn lại |
| Vè | 138 | 138 (toàn bộ) | 40 | — |
| **Cộng** | **16.450** | **~3.000** | **500** | **~13.450** |

Kho lưu trữ: **100% lên L1** trong 6 tháng. Phần lớn việc ở mức này do máy làm (lỗi trình bày, chống trùng, gợi ý cờ); người duyệt theo lô.

## 4. Đa dạng hoá nguồn và đối chiếu

| Hướng | Cách làm | Ràng buộc |
|---|---|---|
| Sách hết thời hạn bảo hộ | Hai sách 1897, 1901 trên Wikisource (D-080, T-158); *Tục ngữ phong dao* 1928 (T-107); các sách khác có tác giả mất đủ lâu (luật Việt Nam: đời tác giả + 50 năm — kiểm từng sách) | Đọc qua bản quét; nhận dạng chữ (OCR), người so từng dòng với ảnh trang (51, G-2) |
| Sách hiện đại còn bản quyền | Chỉ dùng để **đối chiếu và ghi số trang** (đã có "Đã đối chiếu: <sách>, tr. N"); người có sách điền | Không chép lời giải, chú thích của sách |
| Người đọc có sách | "Gửi số trang" (đã có), người soát lời tình nguyện (T-134) | Ghi công theo đồng ý; chưa đối chiếu thì giữ nhãn |
| Đối tác học thuật | Thư viện, khoa văn, viện nghiên cứu: xin quyền dùng tư liệu, mời thẩm định | Thoả thuận bằng văn bản (H3, H7) |
| Nguồn mở | Wikipedia, Wiktionary (CC BY-SA, ghi tại chỗ — D-061), Wikidata (CC0), Commons | Giữ giấy phép và ghi công tại chỗ |

## 5. Chú thích: thay dần chú thích chép

**Hiện trạng.** Phần lớn từ khó trên site là chú thích máy chép từ Ca dao Mẹ, không hiện nguồn tại chỗ (D-068), chưa có thư đồng ý. Từ điển chỉ có 4 mục lời giải do thư viện tự viết được lập chỉ mục.

**Chương trình thay chú thích** (nhóm việc E1.6, E3.4 ở 55):

1. **Danh sách từ cần chú giải** của mỗi bài lấy từ chữ đã được đánh dấu (chỉ **chữ**, là sự kiện) — không lấy lời giải của nguồn.
2. AI soạn nháp lời giải **từ nguồn được phép**: từ điển hết thời hạn bảo hộ, Wiktionary (giữ CC BY-SA), Wikipedia cho thực thể, sách đã đối chiếu. Mỗi lời giải kèm trích dẫn (sách, mục, trang).
3. Máy kiểm: bản nháp **không được trùng chữ quá ngưỡng** với chú thích chép. Điều này ngăn việc "viết lại" văn bản có quyền.
4. Người duyệt kiểm trích dẫn → lời giải thành `nguoi_viet` / `ai_da_duyet` → nối vào mục từ (Từ điển).
5. **Quy tắc hiển thị:** khi một chữ đã có lời giải do thư viện viết, chú thích chép cho cùng chữ không hiện nữa. Dữ liệu cũ **không bị xoá**, vẫn giữ nội bộ để đối chiếu.
6. Thứ tự: hạng A → Bộ tham chiếu → chữ xuất hiện ở nhiều bài nhất (mỗi lời giải dùng được cho hàng trăm bài qua Từ điển).

Song song: gửi thư (T-034), và chọn ngay một trong hai — bật lại ghi nguồn tại chỗ, hoặc ẩn chú thích chép ([51](51-danh-gia-lai-quyet-dinh.md) R-04; [56](56-goi-quyet-dinh.md) B1).

## 6. Xưởng biên tập có AI

### 6.1. Dòng chảy

```text
1 Chọn lô ── máy xếp theo điểm ưu tiên và hạng; người điều phối chọn chủ đề lô (một mô-típ, một bài học…)
2 Soạn nháp ── AI đọc: lời (bất biến), thể loại, phần đã có, chữ cần chú giải, trích đoạn từ điển được phép, Sổ tay
               AI trả: Ý nghĩa · chú giải (kèm trích dẫn) · Hình ảnh / Dùng khi / Giải thích đáp án · gợi ý phân loại,
               nhãn phụ, cờ, quan hệ (mô-típ, thực thể) · danh sách "nhận định cần nguồn" · độ tự tin
3 Máy kiểm ── 9 trình kiểm (6.2); lỗi chặn thì trả về, lỗi nhắc thì gắn cờ cho người
4 Hàng duyệt ── theo vai và hạng: Cộng tác viên (L1) → Biên tập viên (L2) → Thẩm định (L3, hạng A)
5 Người quyết ── Duyệt · Sửa rồi duyệt · Trả lại · Đẩy lên thẩm định; một phím; ≤ 4 phút mỗi bài hạng B
6 Ghi ── qua LuuPhan (lằn ranh trong mã); dấu AI, người duyệt, ngày, phiên bản lời nhắc, mô hình, nguồn trích
7 Kiểm mẫu ── 10% bài L2 mỗi tuần sang người thứ hai; 100% hạng A qua thẩm định
8 Phản hồi ── lỗi kiểm mẫu, báo lỗi của người đọc → sửa Sổ tay, lời nhắc, đào tạo lại
```

### 6.2. Chín trình kiểm tự động (trước khi người thấy)

| # | Kiểm | Xử lý |
|---|---|---|
| 1 | Lời không đổi (so dấu vân tay) | Chặn |
| 2 | Tên riêng, năm, nơi chốn mới không có trong lời hay nguồn trích | Gắn cờ "nhận định cần nguồn" |
| 3 | Trích dẫn trỏ tới nguồn trong danh sách được phép và mục có thật | Chặn nếu không |
| 4 | Quy tắc viết đang có (`KiemTrinhBay`: độ dài, nhắc lại câu, nêu tác giả) | Nhắc |
| 5 | Từ đánh giá sáo rỗng | Nhắc |
| 6 | Trùng chữ với chú thích chép vượt ngưỡng | Chặn |
| 7 | Từ cấm trong giao diện | Chặn |
| 8 | Câu đố: giải thích không lọt ra trường công khai khác | Chặn |
| 9 | Dấu hiệu thô tục, nhạy cảm | Gắn cờ cho người |

### 6.3. Dữ liệu gửi cho dịch vụ AI

- **Gửi:** lời đã đăng (di sản dân gian, đã công khai), phần thư viện tự viết, trích đoạn nguồn được phép, Sổ tay.
- **Không gửi:** dữ liệu cá nhân, bản người đọc gửi chưa duyệt, lời giải và chú thích của nguồn chưa đồng ý, kho thô chưa đăng.
- Khoá API không đặt trên máy chủ ở bước đầu (6.4, phương án P1).

### 6.4. Cách chạy (người sáng lập chọn — [56](56-goi-quyet-dinh.md) E2)

| Phương án | Cách | Ưu | Nhược | Khi nào |
|---|---|---|---|---|
| **P1 · Tệp (khuyến nghị bước đầu)** | Tải lô từ Xưởng → agent chạy ngoài WordPress, gọi API theo lô (Batches, giảm 50% giá) → kiểm → tệp nháp → nạp vào Xưởng | Không khoá trên máy chủ; dùng tiếp đường "Nháp theo lô" đã có (D-117); dễ đo | Có bước tay mỗi lô | Ba tháng đầu |
| P2 · Plugin gọi API | Khoá trong `wp-config.php`, hạn mức chi tiêu, WP-Cron theo lô | Liên tục, ít thao tác | Khoá trên máy chủ; cần theo dõi chi phí | Khi lưu lượng ổn định (`docs/41` mục 6 đang chờ đúng quyết định này) |
| P3 · Agent chạy theo lịch | Agent được quản lý, chạy định kỳ, đẩy nháp qua REST có quyền | Tự động hoàn toàn | Thêm hạ tầng, khó gỡ lỗi | Chặng 2 nếu cần |

### 6.5. Mô hình và chi phí (ước lượng bậc độ lớn)

**Giả định cho một bài, gọi một lần cho mọi phần:**

| Thành phần | Số token |
|---|---|
| Sổ tay và lời dặn (lưu đệm) | ~4.000 |
| Dữ liệu bài và trích đoạn từ điển (không đệm) | ~1.500 |
| Đầu ra, gồm phần suy nghĩ ở mức effort thấp – trung bình | ~1.600 |

Tiếng Việt có thể tốn nhiều token hơn; phải đo lại bằng đếm token trên 200 bài thử.

| Mô hình (giá 11/2026, USD / 1 triệu token vào / ra) | Một lượt cho Bộ tham chiếu 3.000 bài | Một lượt cho toàn kho ~16.300 bài | Ghi chú |
|---|---|---|---|
| Claude Opus 5.5 (4 / 20) | ~115 USD; ~60 USD qua Batches | ~630 USD; ~320 USD qua Batches | Mặc định đề xuất cho Ý nghĩa, chú giải |
| Claude Sonnet 5.5 (2 / 10) | ~60 USD; ~30 USD qua Batches | ~320 USD; ~160 USD qua Batches | Chọn nếu bộ đánh giá cho thấy chất lượng giữ được |
| Claude Haiku 5.5 (0,10 / 0,50) | ~3 USD | ~16 USD | Hợp với phân loại, gợi ý cờ, kiểm phụ |

Cộng thêm khoảng 50% cho lượt kiểm phụ và chạy lại. **Kết luận: chi phí máy ở mức vài trăm USD cho cả kho — không phải điểm nghẽn. Điểm nghẽn là giờ người duyệt (mục 7).** Chọn mô hình theo kết quả bộ đánh giá (6.6). Mô hình rẻ hơn là quyết định của người sáng lập, không phải mặc định.

### 6.6. Bộ đánh giá trước khi chạy lớn

| Hạng mục | Nội dung |
|---|---|
| Mẫu | 200 bài, 40 mỗi thể loại; chọn từ bài đã có Ý nghĩa người duyệt và bài hạng A |
| Đầu ra tham chiếu | Người viết, có nguồn |
| Chấm điểm | Theo Sổ tay: đúng nghĩa · có căn cứ · văn phong · độ dài · an toàn |

**Cổng trước khi chạy lớn hơn 500 bài:**

- ≥ 60% bản nháp duyệt không cần sửa;
- ≥ 85% duyệt sau khi sửa nhẹ;
- lỗi nghiêm trọng (sai nghĩa, bịa sự kiện, bịa nguồn) ≤ 1%.

Chạy lại bộ đánh giá mỗi khi đổi mô hình, lời nhắc hay phiên bản Sổ tay.

### 6.7. Ghi vết và nhãn

Mỗi phần được duyệt lưu, trong nhật ký bảng việc:

- dấu `ai`;
- người duyệt, ngày duyệt;
- mô hình, phiên bản lời nhắc, phiên bản Sổ tay;
- nguồn trích.

Người đọc thấy dòng "Có hỗ trợ của AI, đã được người biên soạn duyệt" (đã có). Không có nội dung máy soạn nào công khai khi chưa có người bấm duyệt.

## 7. Nhóm biên tập và năng lực

### 7.1. Vai

| Vai | Làm | Quyền (54 mục 4.6) | Ai |
|---|---|---|---|
| Tổng biên tập | Chính sách, Sổ tay, chọn hạng A, giải quyết bất đồng, kiểm mẫu cuối | Quản trị | Người sáng lập |
| Thẩm định | Duyệt L3, hạng A, bài khảo cứu; ký tên hoặc bút danh | Mới: `cadao_tham_dinh` | Giảng viên, nhà nghiên cứu, giáo viên giỏi (cộng tác) |
| Biên tập viên | Duyệt L2; sửa nháp; nối quan hệ; viết bình giảng | `cadao_duyet` | Cộng tác viên đã qua kèm; biên tập viên bán thời gian (khi có tài trợ) |
| Cộng tác viên | Duyệt L1 (cờ, nhãn phụ, trình bày); soát lời với ảnh sách; đề xuất | `cadao_cong_tac` (đã khai) | Sinh viên văn, sư phạm; người yêu văn hoá |

**Tuyển và giữ người:**

- **Tuyển:** khoa văn, trường sư phạm (giờ công tác xã hội), câu lạc bộ văn học, giáo viên nghỉ hưu, người đọc đã góp có chất lượng.
- **Vào nhóm:** đọc Sổ tay → làm bài kiểm 20 mục (khớp ≥ 90%) → 2 tuần kèm (bài nào cũng được người khác duyệt lại) → kiểm mẫu 10%.
- **Ghi công:** bút danh ở khối Biên soạn khi người đó đồng ý; trang "Nhóm biên soạn"; giấy xác nhận tình nguyện.
- **Riêng tư:** thông tin cá nhân của người trong nhóm không vào kho mã hay trang công khai, trừ bút danh họ chọn.

### 7.2. Năng suất (đo lại sau 2 tuần chạy thật)

| Loại bài (có nháp AI) | Thời gian người duyệt |
|---|---|
| Tục ngữ – thành ngữ, ca dao ngắn (hạng B) | 3–4 phút |
| Câu đố | 1,5–2 phút |
| Đồng dao | 5–8 phút |
| Vè | 15–25 phút |
| Thêm cho hạng A (đối chiếu sách, thẩm định) | 20–40 phút |
| Lớp L1 cho kho lưu trữ (theo lô, máy làm phần lớn) | ~10 giây / bài |
| Kiểm mẫu | +10–15% |

### 7.3. Ba kịch bản

| Kịch bản | Giờ duyệt / tuần | Bài L2 / tuần | Sau 3 tháng | Sau 6 tháng | Chi phí người |
|---|---|---|---|---|---|
| **S0** · Người sáng lập + AI | 2–3 | ~40 | ~400 | ~1.000 | 0 |
| **S1** · + 4–6 cộng tác viên (2–3 giờ / người) | 12–18 | ~200 (sau 4–6 tuần tuyển và kèm) | ~1.000 | ~3.000 | Ghi công; có thể quà, học bổng nhỏ |
| **S2** · S1 + 1 biên tập viên bán thời gian (20 giờ) + 2 thẩm định (4 giờ) | 40+ | ~400 | ~2.000 | ~5.000 + 200 bài hạng A | Cần ngân sách hoặc tài trợ (Q-008) |

Khuyến nghị: **bắt đầu S0 ngay, tuyển cho S1 trong giai đoạn 0–1, xét S2 khi có hướng tài chính.** Mục tiêu ở [50](50-tai-dinh-vi-chien-luoc.md) mục 7 theo S1.

## 8. Kiểm định chất lượng học thuật

| Hạng mục | Quy định |
|---|---|
| Kiểm mẫu | 10% bài L2 mỗi tuần, chọn ngẫu nhiên theo tầng (người duyệt × thể loại), do người thứ hai; 100% hạng A qua thẩm định |
| Phân loại lỗi | **Nghiêm trọng:** sai nghĩa, bịa sự kiện / nguồn, sửa sai lời, lộ thô tục ở chỗ cho trẻ. **Lớn:** nhận định thiếu căn cứ, chú giải sai, sai thể loại. **Nhỏ:** văn phong, độ dài, chính tả |
| Ngưỡng | Nghiêm trọng: 0 trong mẫu; Lớn ≤ 2%. Vượt ngưỡng → dừng lô của người duyệt đó, rà lại cả lô, đào tạo lại |
| Sửa công khai | Bài tham chiếu có "Lịch sử sửa đổi" người đọc xem được (ngày, phần đổi, lý do ngắn) — thử ở 53 |
| Báo lỗi | Báo lỗi của người đọc ưu tiên cao nhất trong Hộp việc (đã có); thời gian xử lý trung bình ≤ 7 ngày |
| Số đo | Tỷ lệ lỗi theo người, theo thể loại, theo phiên bản lời nhắc; tỷ lệ nháp sửa nặng |

## 9. Chống trùng và họ dị bản ở quy mô kho

1. **Đo trước:** chạy so dấu vân tay giữa mọi bài đã đăng trên trang thử. Báo cáo số cặp theo mức giống: y hệt · gần như y hệt · có thể là dị bản.
2. **Ba cách xử lý, người quyết theo lô có xem trước:**
   - Gộp: bài phụ thành dị bản, đường dẫn cũ chuyển hướng 301, giữ lượt đọc — H6, công cụ ở 54 mục 4.4.
   - Liên kết thành **họ dị bản**: không gộp; hai bài dẫn qua lại.
   - Giữ riêng.
3. **Bộ tham chiếu xử lý trùng trước;** kho lưu trữ xử lý theo lô dần.

## 10. Các chương trình nội dung 6 tháng đầu

| Chương trình | Phạm vi | Kết quả | Phục vụ |
|---|---|---|---|
| Đính chính 215 bài hiện có | Rà Ý nghĩa, chú giải theo Sổ tay | 215 bài đạt chuẩn mới | Niềm tin |
| Câu đố an toàn có lời giải | 765 câu đố: rà thô tục, Giải thích đáp án | Bộ câu đố cho PhongDao Play | Play, phụ huynh |
| Vè có bối cảnh | 138 vè: Bối cảnh có nguồn, chia đoạn, nhân vật | Vè là thế mạnh riêng của PhongDao | Nghiên cứu, học |
| Tục ngữ trong trường học | ~300 câu thường gặp trong chương trình (người có sách xác nhận) | Nền EduKit | Giáo viên |
| Ca dao tinh hoa theo mô-típ | 20 mô-típ trọng tâm × 25 bài | Atlas mô-típ, Đọc tiếp có lý do | Người yêu văn hoá |
| Đồng dao chơi được | ~150 bài có cách chơi có nguồn, độ tuổi | Đọc cùng bé, Play | Phụ huynh |
| Thay chú thích | Bộ tham chiếu | Từ điển của chính thư viện | Quyền, dữ liệu mở |

## 11. Chỉ số

| Chỉ số | Hiện nay | 3 tháng | 6 tháng |
|---|---|---|---|
| Bài L2 trở lên (S1) | 215 | 1.000 | 3.000 |
| Kho lưu trữ đạt L1 | chưa đo | 40% | 100% |
| Câu đố có giải thích đáp án và đã rà an toàn | 7 lên Google | 400 | 765 |
| Lời giải Từ điển do thư viện viết | 4 lên Google | 500 | 2.000 |
| Chú thích chép còn hiện ở bài thuộc Bộ tham chiếu | gần hết | −50% | 0 |
| Tỷ lệ lỗi lớn qua kiểm mẫu | chưa đo | ≤ 3% | ≤ 2% |
| Thời gian duyệt trung vị, bài hạng B | chưa đo | ≤ 5 phút | ≤ 4 phút |
| Người duyệt hoạt động hằng tuần | 1 | 3–4 | 5–7 |

## 12. Rủi ro

| Rủi ro | Mức | Giảm thiểu |
|---|---|---|
| Nháp AI trơn tru nhưng sai tinh vi (điển tích, lịch sử) | Cao | Trình kiểm 2, 3; "nhận định cần nguồn" bắt buộc người kiểm; thẩm định cho hạng A; kiểm mẫu |
| Người duyệt "bấm cho qua" | TB | Kiểm mẫu theo người; đo thời gian bất thường; giới hạn số bài mỗi phiên |
| Văn phong đồng loạt, nhạt | TB | Sổ tay có mẫu văn phong; bình giảng do người viết; luân phiên chủ đề lô |
| Viết lại chú thích có quyền một cách gián tiếp | Cao | Không gửi chú thích chép cho AI; trình kiểm 6; trích nguồn được phép |
| Cộng tác viên bỏ ngang | TB | Lô nhỏ; ghi công; cộng đồng nhóm; kịch bản S0 vẫn chạy |
| Lộ dữ liệu cá nhân trong lời nhắc AI | Thấp | Chỉ gửi nội dung đã đăng và nguồn được phép; kiểm tệp lô trước khi gửi |
