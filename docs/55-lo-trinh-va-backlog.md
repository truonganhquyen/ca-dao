# 55 · Lộ trình tổng và backlog ưu tiên — Chương trình nâng cấp lớn V2

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Căn cứ (D-127, 21/11/2026)** — kế hoạch đang dùng: [57](57-ke-hoach-chot.md); nơi khác nhau, 57 thắng. Nguyên trạng lúc lập: **Đề xuất, chờ người sáng lập duyệt** ([56](56-goi-quyet-dinh.md)). Thay lộ trình và backlog ở [47](47-ke-hoach-tong-core-next.md), [48](48-lo-trinh-va-backlog.md). Mã việc ở đây (E1.2.3…) là mã tạm; khi duyệt, agent chuyển thành T-xxx trong `docs/22` (việc E0.1.2). **Trước đó không ai nhận việc từ tệp này** |
| Bộ tài liệu | [50 tầm nhìn](50-tai-dinh-vi-chien-luoc.md) · [51 rà soát quyết định](51-danh-gia-lai-quyet-dinh.md) · [52 chất lượng nội dung](52-chien-luoc-chat-luong-noi-dung.md) · [53 trải nghiệm](53-thiet-ke-lai-trai-nghiem.md) · [54 kiến trúc](54-kien-truc-dich-va-chuyen-doi.md) · **55 lộ trình, backlog** · [56 gói quyết định](56-goi-quyet-dinh.md) |

**Ai:** **N** người sáng lập · **A** agent · **N+A** agent làm, người sáng lập quyết · **Nhóm** nhóm biên tập (52 mục 7).
**Ưu tiên:** **P0** làm trước, gỡ chặn hoặc giảm rủi ro · **P1** giá trị cao · **P2** nên làm · **P3** khi đủ điều kiện.
**Công sức** (agent, ước lượng): **S** ≤ 1 tuần · **M** 1–3 tuần · **L** 3–6 tuần · **XL** > 6 tuần.
**Tác động:** Cao / TB / Thấp. **Gate:** Human Gate (H1–H9 — 51 R-25) hoặc cổng bản mẫu PG1–PG4 (53 mục 7).

---

## 1. Tổng quan

| Giai đoạn | Tên | Tuần (từ ngày duyệt) | Mục tiêu | Cổng ra (tóm tắt) |
|---|---|---|---|---|
| **GĐ0** | Khởi động an toàn | 1–3 | Quyết định, trang thử, thử khôi phục, quyền nguồn, số đo nền, tuyển nhóm | Có trang thử và biên bản khôi phục; thư đã gửi; số đo nền |
| **GĐ1** | Xưởng biên tập và chuẩn tham chiếu | 3–12 | Máy, người, quy trình làm được ~200 bài / tuần | 1.000 bài L2 (S1); bộ đánh giá đạt; lỗi lớn ≤ 3% |
| **GĐ2** | Trải nghiệm tham chiếu | 1–22 (nghiên cứu từ tuần 1; xây từ tuần ~10) | Bản mẫu → thử → duyệt → xây → bật dần | PG1–PG4; mẫu mới cho hạng A của năm thể loại |
| **GĐ3** | Mạng tri thức | 10–26 | Bảng quan hệ, hồ sơ thực thể, bài viết, Từ điển của thư viện, họ dị bản, Atlas | 200 hồ sơ, 20 bài viết, hiệu năng trong ngân sách |
| **GĐ4** | Học và Chơi | 20–32 | PhongDao Play v1, Học (EduKit bản nhẹ), Bộ của tôi, PWA, bộ sưu tập thử nghiệm | Thử thật với phụ huynh, giáo viên |
| **GĐ5** | Hệ sinh thái mở | từ tuần 30, theo cổng | Dữ liệu mở, nhúng, API, Creator Studio, tuyến di sản, ấn phẩm, lưu trữ sống | Mỗi sản phẩm có điều kiện khởi động riêng (mục 8) |

```text
Tuần        1   4   8   12  16  20  24  28  32
GĐ0         ███
GĐ1           █████████
GĐ2 (UX)    ██████████ → xây ████████████
GĐ3                   ██████████████████
GĐ4                               ████████████
GĐ5                                       ▶ theo cổng
Nội dung    ─────── vòng biên tập hằng tuần chạy suốt ───────▶
```

Các nhánh chạy song song được vì khác tệp và khác người: nội dung do Nhóm làm, giao diện cần người sáng lập duyệt bản mẫu, kiến trúc do agent làm. Nhiều PR mở cùng lúc thì xếp chồng (D-053). Gộp theo mức rủi ro (51 R-23).

## 2. GĐ0 · Khởi động an toàn (tuần 1–3)

| ID | Việc | Ai | Ưu tiên | Công sức | Phụ thuộc | Nghiệm thu |
|---|---|---|---|---|---|---|
| **E0.1 · Ghi quyết định và kế hoạch** ||||||| 
| E0.1.1 | Ghi D-xxx cho mỗi QĐ được duyệt ở 56; thêm "Thay / sửa một phần bởi D-xxx" vào dòng cũ | A | P0 | S | Người sáng lập trả lời 56 | Mỗi QĐ duyệt có đúng một D; `kiem-tra` đạt |
| E0.1.2 | Đưa việc GĐ0–GĐ1 vào `docs/22` (READY); GĐ2–GĐ4 BLOCKED theo phụ thuộc; GĐ5 DEFERRED; gộp việc cũ trùng (mục 10) | A | P0 | S | E0.1.1 | `viec-tiep-theo` ra đúng việc đầu GĐ0; không việc GĐ5 nào READY |
| E0.1.3 | Sửa AGENTS.md (H7–H9, gộp theo rủi ro, làm rõ G-1–G-3, ngân sách theo loại trang, sổ sự kiện) và `docs/00`, `26`, `27`, `28` theo D mới | A | P0 | S | E0.1.1 | Tài liệu khớp quyết định; một review độc lập (D-048) vì đổi luật chơi |
| **E0.2 · An toàn dữ liệu** ||||||| 
| E0.2.1 | Trang thử riêng tư: bản sao CSDL và tệp, chặn lập chỉ mục, khoá truy cập, tự cập nhật theo nhánh | N (A hướng dẫn) | P0 | S | — | Trang thử chạy đúng phiên bản `main`; khách ngoài không vào được |
| E0.2.2 | **Thử khôi phục** bản sao lưu FlashPanel lên trang thử; đối chiếu số bài, số phần, nhật ký | N+A | P0 | S | E0.2.1 | Biên bản: thời gian khôi phục, số liệu khớp |
| E0.2.3 | Đo nền hiệu năng trên trang thử (trang bài, danh sách, Tìm, Hôm nay, Số liệu) với dữ liệu thật | A | P1 | S | E0.2.1 | Bảng số đo trong `docs/22` (không chứa dữ liệu kho) |
| **E0.3 · Quyền nguồn** ||||||| 
| E0.3.1 | Gửi thư cho Ca dao Mẹ (mẫu `docs/12` mục 7; hỏi thêm về dữ liệu mở, sản phẩm phái sinh) | N | P0 | S | — | Thư đã gửi; ngày ghi ở `docs/02` |
| E0.3.2 | Áp dụng lựa chọn tạm thời ở QĐ-B1: bật ghi nguồn tại chỗ, hoặc ẩn chú thích chép (đổi cài đặt hồ sơ nguồn, không đổi mã) | N bấm, A kiểm chỉ đọc | P0 | S | QĐ-B1 | HTML khách đúng lựa chọn; không lộ liên kết nguồn ẩn (D-078) |
| E0.3.3 | Sửa trang Chính sách thu thập dữ liệu cho đúng thực tế (thiếu danh sách trang đã thu thập — D-117 (2)) | N (A soạn nháp câu chữ) | P1 | S | — | Trang không hứa điều không có |
| **E0.4 · Đo nền** ||||||| 
| E0.4.1 | Nối Google Search Console (người sáng lập xác minh tên miền); xuất số tổng hợp định kỳ | N | P1 | S | — | Có số lượt hiển thị, lượt nhấp theo trang |
| E0.4.2 | Bảng độ phủ theo thể loại ở màn Số liệu (46 KT-11) | A | P0 | S | — | Năm thể loại × đã đăng · phần riêng · L2 · thiếu · cờ; khớp đếm trên kho thử |
| E0.4.3 | Đo trùng toàn kho trên trang thử: số cặp theo mức giống | A | P1 | M | E0.2.1 | Báo cáo số cặp; không ghi gì vào dữ liệu |
| E0.4.4 | Chuẩn so sánh trải nghiệm (UX-0, 53 mục 7) — cũng là E2.1 | N+A | P0 | S | — | Báo cáo 6 tác vụ trên site hiện tại, 8–10 người |
| **E0.5 · Nhóm biên tập** ||||||| 
| E0.5.1 | Mô tả vai, cam kết giờ, ghi công; trang "Tham gia biên soạn" (người sáng lập viết, liên hệ ngoài site) | N (A soạn khung) | P0 | S | QĐ-A2 | Trang đăng; không thu dữ liệu cá nhân trên site |
| E0.5.2 | Tuyển 4–6 cộng tác viên; bài kiểm 20 mục | N | P0 | M | E0.5.1 | ≥ 3 người qua bài kiểm (S1), hoặc ghi nhận chạy S0 |

**Cổng ra GĐ0:**

- Quyết định đã ghi.
- Trang thử chạy và đã thử khôi phục thành công.
- Thư đã gửi; lựa chọn tạm thời về chú thích đã áp dụng.
- Có bảng độ phủ, báo cáo trùng, chuẩn so sánh trải nghiệm.
- Đã chốt kịch bản nhóm (S0 / S1 / S2).

## 3. GĐ1 · Xưởng biên tập và chuẩn tham chiếu (tuần 3–12)

| ID | Việc | Ai | Ưu tiên | Công sức | Phụ thuộc | Nghiệm thu |
|---|---|---|---|---|---|---|
| **E1.1 · Cấp, hạng, điểm ưu tiên** (KTĐ-7) ||||||| 
| E1.1.1 | Cấp mới (52 mục 2.1), cột `tang`, `diem_uu_tien`, `so_quan_he` ở bảng tóm tắt; bước CSDL; tính lại theo lô | A | P0 | M | E0.1.2, E0.2.2 | Tính khớp trên kho thử; diễn tập trên trang thử; Số liệu hiện phân bố theo cấp và hạng |
| E1.1.2 | Ngưỡng "phần riêng" mới (51 R-05) sau công tắc; **mô phỏng** số trang lên / xuống Google trước khi bật | A | P1 | S | E1.1.1 | Báo cáo mô phỏng; người sáng lập bật |
| E1.1.3 | Danh sách Bộ tham chiếu đề xuất theo điểm; người sáng lập chọn hạng A | N+A | P0 | S | E1.1.1 | Danh sách hạng A đã chốt (chỉ mã bài trong CSDL, không chép lời vào repo) |
| **E1.2 · Xưởng biên tập v1** (KTĐ-6) ||||||| 
| E1.2.1 | Cột bảng việc `vai_can`, `tang`, `nguoi_nhan`, `mau_kiem`; quyền `cadao_tham_dinh`, vai Thẩm định | A | P0 | M | E0.1.2, E0.2.2 | Bước CSDL chạy lại được, diễn tập trên trang thử; kiểm quyền từng vai |
| E1.2.2 | Hàng duyệt theo vai và hạng; giữ thẻ có hạn; trả thẻ quá hạn | A | P0 | M | E1.2.1 | Hai người không nhận cùng một thẻ; thẻ quá hạn tự về hàng |
| E1.2.3 | Màn duyệt nhiều phần: lời, phần hiện có, nháp, cờ trình kiểm, nguồn trích; Duyệt · Sửa rồi duyệt · Trả lại · Đẩy lên thẩm định; phím tắt; điện thoại | A | P0 | L | E1.2.2 | Trung vị ≤ 4 phút / bài hạng B (đo thật); `quet:v2` 0 lỗi; mọi lần lưu qua `LuuPhan` |
| E1.2.4 | Kiểm mẫu 10% (ngẫu nhiên theo tầng), người thứ hai, ghi lỗi theo loại | A | P1 | M | E1.2.3 | Mỗi tuần có mẫu; lỗi ghi đúng loại; vượt ngưỡng thì báo |
| E1.2.5 | Số đo Xưởng: năng suất, tỷ lệ sửa nặng, lỗi theo người, thể loại, phiên bản lời nhắc | A | P1 | S | E1.2.4 | Màn Số liệu có khối Xưởng |
| **E1.3 · Đường AI và bộ đánh giá** (52 mục 6) ||||||| 
| E1.3.1 | Bộ chạy ngoài WordPress (phương án P1): đọc tệp lô, gọi API theo lô, ghi tệp nháp có ghi vết; khoá ở máy chạy | A | P0 | M | QĐ-E2 | Chạy 50 bài thử; tệp gửi không có dữ liệu cá nhân; chi phí đo được bằng đếm token |
| E1.3.2 | Chín trình kiểm (PHP thuần, chạy lúc nạp; bản nhẹ ở bộ chạy) | A | P0 | M | — | Mỗi trình kiểm ≥ 2 ca dương, ≥ 2 ca âm; chặn đúng |
| E1.3.3 | Nạp nháp nhiều phần vào Xưởng (mở rộng "Nháp theo lô", D-117) | A | P0 | M | E1.2.3, E1.3.2 | Xem trước; mỗi phần một quyết định; không gì vào bài khi chưa duyệt |
| E1.3.4 | Bộ đánh giá 200 bài (40 mỗi thể loại), đầu ra tham chiếu do người viết; báo cáo theo mô hình | N+A | P0 | M | E1.4.1 | Đạt ngưỡng 52 mục 6.6 **trước khi chạy quá 500 bài** |
| **E1.4 · Sổ tay và máy nhắc** ||||||| 
| E1.4.1 | Sổ tay biên tập v1 (52 mục 2.3) | A soạn, N duyệt | P0 | S | — | Một tệp ở `docs/huong-dan/`; không chép lời, chú thích nguồn; có số phiên bản |
| E1.4.2 | Năm máy nhắc ([46](46-kien-truc-va-khuyen-nghi.md) KT-5) + nhắc từ sáo rỗng, nhận định không nguồn | A | P1 | S | E1.4.1 | Ca dương / âm; "Bỏ qua" được nhớ; không sửa chữ |
| **E1.5 · An toàn và phân loại** ||||||| 
| E1.5.1 | Gợi ý thô tục (danh sách từ + AI) vào Bổ sung; người xác nhận (51 R-09) | A | P0 | S | QĐ-E6 | 765 câu đố có kết quả gợi ý; máy không tự bật |
| E1.5.2 | Nhãn phụ tục ngữ / thành ngữ: trường, gợi ý, xác nhận, chỉ hiện khi đã xác nhận (51 R-08) | A | P1 | S | QĐ-B2 | Không đổi đường dẫn; Lịch sử ghi |
| **E1.6 · Chú thích tự viết** (52 mục 5) ||||||| 
| E1.6.1 | Quy tắc hiển thị: lời giải thư viện viết thay chú thích chép cùng chữ; không xoá dữ liệu | A | P0 | S | — | Bài có cả hai: khách chỉ thấy lời giải thư viện; người biên soạn thấy cả hai |
| E1.6.2 | Danh sách chữ cần chú giải theo bài (chỉ chữ) cho lô AI | A | P0 | S | — | Tệp lô không chứa lời giải của nguồn |
| E1.6.3 | Hồ sơ nguồn cho từ điển hết thời hạn bảo hộ; quy ước trích (mục, trang) | N+A | P1 | S | — | Người duyệt kiểm được từng trích dẫn |
| **E1.7 · Chương trình nội dung đợt 1** (52 mục 10) ||||||| 
| E1.7.1 | Đính chính 215 bài hiện có theo Sổ tay | Nhóm | P0 | M | E1.4.1 | 215 bài đạt chuẩn mới hoặc ghi lý do |
| E1.7.2 | Câu đố an toàn có lời giải: 765 câu rà thô tục; ≥ 400 có Giải thích đáp án | Nhóm | P0 | L | E1.5.1, E1.3.3 | Bộ câu đố an toàn sẵn cho Play |
| E1.7.3 | Vè có bối cảnh: 138 bài | Nhóm | P1 | L | E1.3.3 | Mọi khẳng định lịch sử có nguồn |
| E1.7.4 | Tục ngữ trong trường học: ~300 câu | Nhóm | P1 | L | E1.5.2 | Nhãn phụ, Ý nghĩa, Dùng khi |
| **E1.8 · Sổ đăng ký sự kiện** (51 R-17) ||||||| 
| E1.8.1 | Sổ sự kiện trong `docs/26`; khung đếm tổng hợp dùng hạ tầng hiện có | A | P1 | S | QĐ-A5 | Không cookie, không IP, không định danh; kiểm riêng tư trong `test:v2` |

**Cổng ra GĐ1:**

- Xưởng chạy với ≥ 3 người duyệt (S1).
- Bộ đánh giá đạt ngưỡng.
- **1.000 bài L2** (S1; S0: 400).
- Tỷ lệ lỗi lớn qua kiểm mẫu ≤ 3%; lỗi nghiêm trọng 0.
- 765 câu đố đã rà thô tục.
- Bài hạng A đã làm không còn hiện chú thích chép.

## 4. GĐ2 · Trải nghiệm tham chiếu (tuần 1–22)

| ID | Việc | Ai | Ưu tiên | Công sức | Phụ thuộc | Nghiệm thu |
|---|---|---|---|---|---|---|
| **E2.1** | Chuẩn so sánh UX-0 (= E0.4.4) | N+A | P0 | S | — | Báo cáo chuẩn so sánh |
| **E2.2 · Khái niệm** ||||||| 
| E2.2.1 | Bản mẫu thô: ca dao, vè, câu đố × hướng A và B; hai phương án IA | A | P0 | M | E2.1 | Bản mẫu ngoài kho mã (như bản xem trước D-119, D-121) |
| E2.2.2 | Buổi chọn PG1 với người sáng lập và 3 người thử | N | P0 | S | E2.2.1 | **PG1**: chọn hướng thị giác, IA để làm tiếp |
| **E2.3 · Bản mẫu bấm được** ||||||| 
| E2.3.1 | Đủ màn: năm mẫu thể loại, Hồ sơ bài, hồ sơ thực thể, bài viết, tuyển tập, trang chủ, Khám phá, danh sách; điện thoại và máy tính; nội dung thật của 10 bài | A | P0 | L | PG1 | Dựng bằng công cụ `design/v2`; ngoài kho mã |
| E2.3.2 | Thử 8–10 người, 6 tác vụ (53 mục 7) | N+A | P0 | M | E2.3.1 | **PG2** đạt ngưỡng; tác vụ không đạt thì sửa và thử lại |
| **E2.4 · Kiểm kỹ thuật và duyệt** ||||||| 
| E2.4.1 | Rà trợ năng bản mẫu (tương phản, bàn phím, trình đọc màn hình, 200%); ước ngân sách; danh sách dữ liệu cần có | A | P0 | S | E2.3.2 | **PG3** |
| E2.4.2 | Người sáng lập duyệt; ghi D-xxx (H4) | N | P0 | S | E2.4.1 | **PG4** |
| **E2.5 · Hệ thiết kế** ||||||| 
| E2.5.1 | Token, thành phần, đặc tả mới trong `design/v2`; đồng bộ sang theme (`dong-bo:giao-dien-v2`) | A | P0 | M | PG4 | `test:v2` báo khớp bộ thiết kế |
| **E2.6 · Mẫu tác phẩm tham chiếu** (sau công tắc theo thể loại, hạng) ||||||| 
| E2.6.1 | Khung chung, công tắc biến thể, Nghĩa một câu, thanh "Dùng bài này", ghi chú lề trên máy tính | A | P0 | L | E2.5.1 | Bật / tắt theo thể loại ngay; tắt JavaScript vẫn đọc; ≤ 200 KB, JavaScript ≤ 21 KB |
| E2.6.2 | Ca dao (Hình ảnh và biểu tượng, chip mô-típ) | A | P0 | M | E2.6.1 | Quét, E2E mẫu ca dao |
| E2.6.3 | Tục ngữ – thành ngữ (nhãn phụ, Dùng khi, Mạng câu) | A | P0 | M | E2.6.1, E1.5.2 | Như trên |
| E2.6.4 | Câu đố (gợi ý từng bước, đáp án, giải thích, ảnh sau đáp án, Đố tiếp) | A | P0 | M | E2.6.1 | Không lộ đáp án ở thẻ, mô tả, dữ liệu có cấu trúc, ảnh chia sẻ (giữ D-114) |
| E2.6.5 | Đồng dao (thẻ Chơi cùng bé) | A | P1 | M | E2.6.1 | Thiếu ô nào bỏ ô đó |
| E2.6.6 | Vè (thẻ bối cảnh có nguồn, mục lục dính, nhân vật và nơi chốn, sàn chữ 18 px) | A | P0 | M | E2.6.1 | Bài > 200 dòng cuộn mượt; mục lục dùng được bằng bàn phím |
| **E2.7 · Niềm tin trên trang** ||||||| 
| E2.7.1 | Hồ sơ bài: lời từ đâu, ai viết, ai duyệt, thẩm định, ngày, cách trích dẫn, giấy phép | A | P0 | M | E2.6.1 | Không lộ nguồn ẩn, `nguon_may` (D-078, `docs/28` mục 6) |
| E2.7.2 | Lịch sử sửa đổi công khai (chỉ phần công khai, từ nhật ký) | A | P1 | M | E2.7.1 | Không lộ tên người, chỉ bút danh đã đồng ý |
| E2.7.3 | Khối Biên soạn và bút danh trong Cài đặt (phần T-185, D-080 (6)) | A | P1 | S | — | Trống thì không hiện |
| E2.7.4 | Nhãn mức biên tập cho người đọc (nếu QĐ-D3 duyệt) | A | P2 | S | QĐ-D3 | Như bản mẫu đã thử |
| **E2.8 · Kiến trúc thông tin, trang chủ, danh sách** ||||||| 
| E2.8.1 | Menu và thanh dưới theo IA đã duyệt; đường dẫn cũ giữ | A | P0 | M | PG4 | Mục chưa đủ nội dung chưa hiện |
| E2.8.2 | Trang chủ mới (≤ 3.000 px trên điện thoại) | A | P1 | M | E2.8.1 | Quét; không lối vào lặp |
| E2.8.3 | Danh sách thể loại, chủ đề: Bộ tham chiếu trước, lọc theo mức biên tập, mô-típ, nhãn phụ | A | P1 | M | E1.1.1 | Phân trang toàn tập; trang lọc `noindex` |
| E2.8.4 | Mời góp một dòng trên trang đọc; trang Góp làm trung tâm (51 R-16) | A | P1 | S | PG4 | Luồng góp hiện có không đổi dữ liệu |
| **E2.9 · Kiểm trợ năng và ngân sách theo loại trang** ||||||| 
| E2.9.1 | Ca kiểm phóng 200% trong `quet:v2` | A | P1 | S | — | Không mất nội dung, không tràn ngang |
| E2.9.2 | Ngân sách theo loại trang (53 mục 8) trong `quet:v2` | A | P1 | S | QĐ-C6 | Vượt ngân sách là LỖI |
| **E2.10 · Bật dần và đo** ||||||| 
| E2.10.1 | Bật theo thứ tự 53 mục 9; mỗi bước 1–2 tuần | N+A | P0 | M | E2.6.x | Mỗi bước có số đo trước / sau |
| E2.10.2 | Thử lại 5 tác vụ với người dùng trên site thật | N+A | P1 | S | E2.10.1 | ≥ 85% tác vụ đạt |
| E2.10.3 | Dọn mẫu cũ sau một tháng ổn định | A | P2 | S | E2.10.2 | Mã chết đã xoá; quay lại bằng commit cũ |

**Cổng ra GĐ2:**

- PG1–PG4 đạt.
- Mẫu mới bật cho hạng A của năm thể loại, rồi toàn thể loại.
- Tốc độ, lỗi, Search Console không xấu đi.
- Thử trên site thật đạt ≥ 85% tác vụ.

**Quay lại:** tắt biến thể theo thể loại ngay. Đường dẫn, `<title>`, dữ liệu có cấu trúc không đổi.

## 5. GĐ3 · Mạng tri thức (tuần 10–26)

| ID | Việc | Ai | Ưu tiên | Công sức | Phụ thuộc | Nghiệm thu |
|---|---|---|---|---|---|---|
| **E3.1 · Bảng quan hệ** (54 M1–M4, M7; KTĐ-2) ||||||| 
| E3.1.1 | Bảng `wp_cadao_lien_ket`, danh mục `MienDuLieu\QuanHe`, cửa ghi `DichVu\LienKet`; lằn ranh "máy chỉ ghi gợi ý" có kiểm thử khẳng định | A | P0 | M | QĐ-C1, E0.2.2 | Bước CSDL chạy lại được; diễn tập trên trang thử |
| E3.1.2 | Ghi kép `gan_nghia` (M2) | A | P1 | S | E3.1.1 | Hai nơi khớp sau mỗi lần lưu |
| E3.1.3 | Điền lùi và gợi ý (M3): `gan_nghia`, `nhac_toi` từ chú giải đã nối, `di_ban_cua` từ đo trùng | A | P0 | M | E3.1.1, E0.4.3 | Báo cáo số dòng; gợi ý không ra trang người đọc; có lệnh gỡ |
| E3.1.4 | Duyệt quan hệ trong Xưởng (theo lô, theo hạng) | A | P0 | M | E3.1.3, E1.2.3 | Người duyệt quyết cả nhóm có xem trước |
| E3.1.5 | Đọc mới sau công tắc (M4): Mạng liên kết, Đọc tiếp | A | P0 | M | E3.1.4 | Bật / tắt theo thể loại; chỉ đọc `duyet` |
| E3.1.6 | Thu hẹp (M7) | A | P2 | S | E3.1.5 + 1 phiên bản | Đọc tương thích một phiên bản |
| **E3.2 · Hồ sơ thực thể** (M5; KTĐ-3) ||||||| 
| E3.2.1 | Loại bài `ho_so`, đường dẫn theo loại, trường, luật lập chỉ mục, sơ đồ trang | A | P0 | M | QĐ-C2 | REST mặc định tắt; khách không thấy nháp |
| E3.2.2 | Màn sửa hồ sơ (trình soạn khối + cột bên trường có cấu trúc); điện thoại | A | P0 | M | E3.2.1 | Lưu có Lịch sử; quét 0 lỗi |
| E3.2.3 | Mẫu trang hồ sơ (53 mục 6.7) | A | P0 | M | E3.2.1, PG4 | Quét; bản đồ nhỏ chỉ khi toạ độ đã kiểm |
| E3.2.4 | Nháp hồ sơ từ mục từ có toạ độ Wikidata đã xác nhận; quan hệ `la_ho_so` | A | P1 | S | E3.2.1 | Chỉ nháp; người đăng |
| E3.2.5 | 200 hồ sơ đầu tiên (nhân vật, địa danh của vè; điển tích thường gặp) | Nhóm | P0 | L | E3.2.2 | Có tóm tắt tự viết, nguồn, quan hệ |
| **E3.3 · Bài viết** (M6; KTĐ-4) ||||||| 
| E3.3.1 | Phân loại `loai_bai_viet`, meta (người viết, biên tập, thẩm định, tài liệu), hộp sửa | A | P0 | M | QĐ-C3 | Ô quyền hiện có giữ nghĩa |
| E3.3.2 | Đường dẫn `/bai-viet/` + 301 cho 46 bài (H6) | A | P1 | S | E3.3.1 | Mọi đường cũ trả 301 đúng; diễn tập trên trang thử |
| E3.3.3 | Quan hệ `ban_ve`; hàng "Bài viết về bài này" (chỉ bài đã có quyền) | A | P0 | M | E3.1.1, E3.3.1 | Bỏ tích quyền → hàng biến mất ngay |
| E3.3.4 | Mẫu bài viết; khối thẻ tác phẩm chèn trong bài | A | P1 | M | E3.3.1, PG4 | Quét; ≤ 250 KB |
| E3.3.5 | 20 bài khảo cứu / bình giảng đầu tiên (người viết hoặc bài có quyền) | Nhóm | P1 | L | E3.3.3 | Có thư mục; có thẩm định với bài khảo cứu |
| **E3.4 · Từ điển của thư viện** ||||||| 
| E3.4.1 | Loại mục từ (Từ ngữ, Địa danh, Nhân vật, Điển tích) và lọc Từ điển | A | P1 | M | E3.2.1 | Lọc bằng GET, tắt JavaScript chạy |
| E3.4.2 | Chương trình lời giải tự viết (lô AI có trích dẫn, người duyệt) | Nhóm | P0 | L | E1.6.x | 2.000 lời giải (6 tháng — 52 mục 11) |
| **E3.5 · Họ dị bản và gộp** (M8; KTĐ-5) ||||||| 
| E3.5.1 | Màn họ dị bản (từ gợi ý đo trùng); chọn bản đại diện | A | P1 | M | E3.1.3 | Người quyết theo lô |
| E3.5.2 | Công cụ gộp có xem trước và hoàn tác (H6 từng lô) | A | P1 | L | E3.5.1 | Hoàn tác trả đủ bài, 301, phần, lượt đọc; diễn tập trên trang thử |
| **E3.6 · Khám phá, Atlas** ||||||| 
| E3.6.1 | Khám phá bốn ống kính (53 mục 6.11) | A | P1 | M | E3.2.3, PG4 | Mỗi ống kính có danh sách khi tắt JavaScript |
| E3.6.2 | Bản đồ hai lớp: "được nhắc đến" và "ghi nhận tại" | A | P2 | M | E3.2.5 | Hai lớp không trộn; danh sách thay bản đồ |
| **E3.7 · Tìm** ||||||| 
| E3.7.1 | Chỉ mục cho hồ sơ, bài viết; đồng nghĩa; tên cũ địa danh; lọc mức biên tập | A | P1 | M | E3.2.1 | Bộ truy vấn đại diện đạt; p95 ≤ 300 ms trên trang thử |
| **E3.8 · Quyền media** (KTĐ-8) ||||||| 
| E3.8.1 | Meta quyền có cấu trúc, màn sửa, đọc ghi công cũ | A | P2 | S | — | Kênh tái dùng lọc được theo quyền |
| **E3.9 · Đối chiếu sách** (T-158; 52 mục 4) ||||||| 
| E3.9.1 | Bộ đọc Wikisource cho hai sách 1897, 1901; nhận dạng chữ cho bản quét hết thời hạn bảo hộ; màn đối chiếu ảnh trang ↔ lời; người xác nhận từng cặp (51 G-2) | A + Nhóm | P1 | L | E1.2.3 | Không ghi "Đã đối chiếu" khi người chưa bấm; 200 bài hạng A đã đối chiếu sau 12 tháng |

**Cổng ra GĐ3:**

- 200 hồ sơ đã duyệt; 20 bài viết có quyền và đã nối.
- ≥ 60% bài L2 có ≥ 3 quan hệ đã duyệt.
- Hiệu năng trong ngân sách (54 mục 7); không đường dẫn hỏng.
- Đọc tiếp có lý do cụ thể ở ≥ 90% bài L2.

## 6. GĐ4 · Học và Chơi (tuần 20–32)

| ID | Việc | Ai | Ưu tiên | Công sức | Phụ thuộc | Nghiệm thu |
|---|---|---|---|---|---|---|
| **E4.1 · PhongDao Play v1** ||||||| 
| E4.1.1 | Đố vui từ câu đố an toàn có giải thích: 10 câu / lượt, theo chủ đề, gợi ý, không điểm | A | P0 | M | E1.7.2, QĐ-C6 | Chơi bằng bàn phím; tắt JavaScript vẫn có trang 10 câu; `noindex` |
| E4.1.2 | Ghép vế tục ngữ (từ tục ngữ L2, hai vế đã xác nhận) | A | P1 | M | E1.7.4 | Câu sai vẫn dẫn về bài để học; không lưu dữ liệu cá nhân |
| E4.1.3 | Đồng dao chơi được (từ thẻ Chơi cùng bé) | A | P1 | S | E2.6.5 | Chỉ bài có cách chơi có nguồn |
| E4.1.4 | Tiến độ và huy hiệu không so sánh, lưu trên máy (51 R-20) | A | P2 | S | QĐ-F2 | Không bảng xếp hạng; không chuỗi ngày ép quay lại |
| **E4.2 · Học (EduKit bản nhẹ)** ||||||| 
| E4.2.1 | Tấm sửa SGK (phần `sgk` đã có schema; phần T-185) — chỉ người đã thấy sách điền | A | P1 | S | — | Máy không điền |
| E4.2.2 | Trang Học: bộ câu theo bài học, trình chiếu, phiếu in A4, câu hỏi thảo luận do người viết | A | P1 | M | E4.2.1, E1.7.4 | 5 giáo viên dùng trong tiết thật |
| **E4.3 · Bộ của tôi (Creator bản nhẹ)** ||||||| 
| E4.3.1 | Từ Đã lưu → phiếu in, ảnh, trình chiếu, sao chép kèm nguồn; "Sao chép cả bộ" ở tuyển tập | A | P1 | M | — | Không tài khoản; mỗi bài kèm đường dẫn và dòng nguồn |
| **E4.4 · Tuyển tập và chuyên đề** ||||||| 
| E4.4.1 | Mẫu tuyển tập mới (53 mục 6.9), người tuyển, ngày cập nhật | A | P1 | M | PG4, E2.7.3 | Quét; ≤ 300 KB cho 30 câu |
| **E4.5 · PWA** (51 R-21) ||||||| 
| E4.5.1 | Thêm vào màn hình chính; đọc ngoại tuyến Đã lưu và bộ Học | A | P2 | M | QĐ-F5 | Không bộ nhớ đệm trang người đăng nhập; gỡ được |
| **E4.6 · Bộ sưu tập thử nghiệm** (51 R-22) ||||||| 
| E4.6.1 | Chế độ thử nghiệm cho một thể loại mới (đề xuất: hát ru) từ nguồn sạch quyền | N+A | P3 | M | QĐ-F6 | Nhãn thử nghiệm, không lên menu, `noindex` |

**Cổng ra GĐ4:**

- Play v1 được 20 phụ huynh, giáo viên dùng thử, ≥ 80% hài lòng.
- 5 giáo viên dùng Học trong tiết thật.
- Không thu dữ liệu cá nhân; ngân sách trang ứng dụng đạt.

## 7. GĐ5 · Hệ sinh thái mở (từ tuần 30, theo cổng)

| ID | Việc | Điều kiện khởi động | Gate | Công sức |
|---|---|---|---|---|
| E5.1 | Lớp `TaiDung` + dữ liệu mở có phiên bản (+ DOI; T-163, T-066) | Thư Ca dao Mẹ đã có trả lời hoặc chú thích chép đã thay / ẩn; ≥ 3.000 bài L2 | H3 | M |
| E5.2 | Khung nhúng "Câu hôm nay", "Đố một câu" | E5.1; Play v1 ổn định; ≥ 3 đối tác muốn nhúng | H4 | S–M |
| E5.3 | API đọc cho đối tác (khoá, giới hạn tần suất) | Có đối tác ký thoả thuận | H3, H7 | M |
| E5.4 | Creator Studio đầy đủ (tài khoản tổ chức) | Bộ của tôi có người dùng đều; quyết pháp nhân nếu thu phí | H7, H9 | L |
| E5.5 | Học cho trường (tài khoản giáo viên, lớp, thử thách lớp) | ≥ 100 bộ câu đã duyệt; quy trình dữ liệu học sinh | H7, H9 | L |
| E5.6 | Tuyến di sản thử với một đối tác | Có đối tác; từng điểm có nguồn và đã kiểm thực địa | H2, H7 | L |
| E5.7 | Ấn phẩm: tuyển tập in, bộ thẻ, sách điện tử | Pháp nhân hoặc nhà xuất bản đối tác; quyền từng thành phần sạch | H3, H7 | M |
| E5.8 | Lưu trữ sống: ghi âm có đồng ý (T-067, `docs/luu-tru/41`), người soát lời tình nguyện (T-134) | Hồ sơ đồng ý, nơi lưu riêng tư, người duyệt | H3 | L |
| E5.9 | "Hỏi thư viện" (AI có trích dẫn) | ≥ 5.000 bài L2; bộ đánh giá trích dẫn đúng ≥ 95%; cổng D-015 | H8 | L |
| E5.10 | Hướng tài chính, pháp nhân (Q-008) | Người sáng lập quyết | H7 | — |

## 8. Danh mục sản phẩm NEXT

Phân loại: **Next** (đã có lịch) · **Later** (có điều kiện) · **Explore** (nghiên cứu, thử nhỏ). Không mục nào bị coi là "không bao giờ", trừ những điều trái lằn ranh ở cuối mục.

| Sản phẩm | Giá trị | Tín hiệu thị trường (chưa kiểm) | Khả thi | Phụ thuộc chính | Thời điểm | Cách có thu (sau quyết định pháp nhân) | Xếp |
|---|---|---|---|---|---|---|---|
| PhongDao Play (đố vui, ghép vế, đồng dao) | Cao | Cao — học sinh, phụ huynh | Cao | Câu đố an toàn, tục ngữ L2 | GĐ4 | Miễn phí; gói chủ đề in về sau | **Next** |
| Trò chơi hoá học tập | TB–Cao | TB | Cao | Play | GĐ4–5 | — | **Next** |
| Học / EduKit | Cao | Cao — giáo viên (cần phỏng vấn xác nhận) | TB | Dữ liệu SGK người nhập | GĐ4 bản nhẹ; GĐ5 cho trường | Gói trường, tập huấn | **Next** / Later |
| Creator Studio | TB–Cao | TB | TB | Bộ của tôi, `TaiDung` | GĐ4 bản nhẹ; GĐ5 đầy đủ | Gói tổ chức | **Next** (nhẹ) / Later |
| Dữ liệu mở, API | Cao (sứ mệnh, học thuật) | TB | TB | Quyền chú thích, `TaiDung` | GĐ5 | Dữ liệu mở miễn phí; API có cam kết dịch vụ cho đối tác | Later |
| Khung nhúng | TB | Thấp–TB | Cao | `TaiDung`, Play | GĐ5 | Gói đối tác | Later |
| Tuyến di sản | Cao (khác biệt) | Chưa kiểm | TB | Hồ sơ địa danh kiểm thực địa, đối tác | GĐ5 | Dự án | Later / Explore |
| Triển lãm, microsite | TB | Chưa kiểm | TB | Trình chiếu, tuyển tập | GĐ5 | Dự án | Later |
| Ấn phẩm (tuyển tập in, bộ thẻ, sách điện tử) | Cao | TB | Cao | Bộ tham chiếu, quyền sạch | GĐ5 | Bán ấn phẩm | Later |
| Lưu trữ sống (ghi âm, ký ức, sưu tầm) | Rất cao (bảo tồn) | Không thương mại | TB | Đồng ý, lưu riêng tư | GĐ5 | Tài trợ | Later |
| Giọng đọc, nghe (T-160) | TB | TB | TB | Phiên nghe thử 30 bài | GĐ4–5 | — | Later |
| Hỏi thư viện (AI có trích dẫn) | TB–Cao | TB | TB | Bộ tham chiếu dày, bộ đánh giá | GĐ5+ | — | Explore |
| Truyện tương tác theo mô-típ | TB | Chưa kiểm | TB | Nội dung phóng tác có nhãn | Sau GĐ5 | — | Explore |
| Thể loại mới (hò, lý, hát ru, truyện) | Cao | TB | TB | Nguồn sạch quyền, cổng D-091 | GĐ4 thử nghiệm | — | **Next** (thử) |

**Không làm dù ở chặng nào** (trái lằn ranh hoặc định vị):

- bán độc quyền phần đã CC BY;
- quảng cáo, bán dữ liệu người dùng;
- giọng AI giả giọng người thật hoặc giọng vùng mà không gắn nhãn;
- bảng xếp hạng công khai cho trẻ;
- AI sáng tác lời rồi trình bày như lời dân gian.

## 9. Chỉ số theo giai đoạn

| Giai đoạn | Chỉ số nghiệm thu chính |
|---|---|
| GĐ0 | Biên bản khôi phục; thư đã gửi; số đo nền (độ phủ, trùng, UX-0, Search Console) |
| GĐ1 | 1.000 bài L2 (S1); lỗi lớn ≤ 3%, nghiêm trọng 0; trung vị duyệt ≤ 4 phút; 765 câu đố đã rà; bộ đánh giá đạt |
| GĐ2 | PG1–PG4; tác vụ đạt ≥ 85% trên site thật; LCP p75 ≤ 2,5 giây; không giảm lượt dùng tin cậy |
| GĐ3 | 200 hồ sơ; 20 bài viết; ≥ 60% bài L2 có ≥ 3 quan hệ; p95 tìm ≤ 300 ms |
| GĐ4 | Play: 20 người thử, ≥ 80% hài lòng; Học: 5 giáo viên dùng thật; 0 dữ liệu cá nhân thu thêm |
| Chung (6 tháng) | 3.000 bài L2; trang lập chỉ mục ≥ 3.500; lượt dùng tin cậy / tuần × 4 |

## 10. Triển khai, quay lại và di chuyển — quy tắc chung

| Hạng mục | Quy tắc |
|---|---|
| Mức rủi ro PR | **Thấp:** tự gộp khi bốn cổng đạt. **Trung bình** (giao diện người đọc): người sáng lập xem bản xem trước. **Cao** (dữ liệu, mô hình, lập chỉ mục, quyền, đường dẫn): người sáng lập duyệt + review độc lập + diễn tập trên trang thử (51 R-23) |
| Di chuyển dữ liệu | Theo 54 mục 6: mở rộng → chuyển → thu hẹp; bước CSDL chạy lại được; sao lưu đêm trước; đã thử khôi phục |
| Bật tính năng | Công tắc theo thể loại, hạng; bật dần; đo trước / sau |
| Quay lại mã | Tự cập nhật giữ 50 bản; `NHANH=<mã>` hoặc revert (`docs/26` mục 15) |
| Quay lại dữ liệu | Lệnh hoàn tác của từng bước (54 mục 6); khôi phục bản sao lưu là đường cuối |
| SEO | Không đổi đường dẫn tác phẩm; mọi đường đổi có 301; theo dõi Search Console 2 tuần sau mỗi bước lớn |
| Kiểm thử | Mỗi việc thêm ca kiểm vào chính `test:v2`, `quet:v2`, `e2e:v2`; không tạo đường kiểm rời (AGENTS.md) |

## 11. Ánh xạ với việc đang có và với bộ 45–49

| Việc / mục cũ | Trong lộ trình mới |
|---|---|
| T-034 Thư Ca dao Mẹ | E0.3.1 |
| T-180 Thử làm sâu 20–30 bài; T-198 QA 10 bài | E1.3.4 (bộ đánh giá), E1.7.1 |
| T-183 Thử lối đọc chủ đề, bài viết dài, công cụ giáo viên | E2.3.2, E3.3, E4.2 |
| T-185 Phần thiết kế chưa làm | Đố vui → E4.1.1; Trò chơi lọc → E4.1.3; khối Biên soạn → E2.7.3; tấm sửa SGK → E4.2.1; sửa tại chỗ mô-típ, chủ đề → E3.6; gợi ý mô-típ, Wikidata → E3.1.3 |
| T-158 Đối chiếu sách Wikisource | E3.9.1 (chương trình nguồn, 52 mục 4) |
| T-159 Cho lớp học | E4.2 |
| T-160 Giọng đọc; T-161, T-162 thể loại mới, truyện | GĐ5 / E4.6 |
| T-163, T-066 Dữ liệu mở, DOI | E5.1 |
| T-062 Bộ bài từ Đã lưu | E4.3.1 |
| T-063 So sánh dị bản | E2.6 (khối Dị bản hai cột) |
| T-065 Ngoại tuyến | E4.5.1 |
| T-067 Ghi âm; T-134 Người soát lời | E5.8 |
| T-164 Dọn dữ liệu MVP | Giữ nguyên, sau GĐ0 (đã có trang thử) |
| 49 · QĐ-1 (không mô hình mới) | **Thay** bởi QĐ-C1–C4 ([56](56-goi-quyet-dinh.md)) |
| 49 · QĐ-2 (loại mục từ), QĐ-3 (bài viết nối) | Gộp vào E3.4.1, E3.3 |
| 49 · QĐ-4 (hàng Ghi chú), QĐ-5 (vè) | Xét lại trong bản mẫu (E2.6) |
| 49 · QĐ-6 (Đố vui), QĐ-7 (vòng nháp) | E4.1.1; E1.3 (thay vòng 30 bài / tuần bằng Xưởng) |
| 49 · QĐ-9–QĐ-12 | Thay bởi nhóm F ở [56](56-goi-quyet-dinh.md) và mục 8 |
| 48 · C1.1–C1.3 | E1.4.1, E1.4.2, E0.4.2 |

Khi đưa vào `docs/22` (E0.1.2): việc có trong cột "cũ" thì **sửa dòng cũ**, không thêm dòng trùng.
