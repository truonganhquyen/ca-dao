# Kế hoạch thực hiện theo ưu tiên — phongdao.com

| | |
|---|---|
| Ngày | 27/10/2026 |
| Dựa trên | `docs/nghien-cuu/khao-sat-doi-thu-2026-10-27.md` (khảo sát 14 site, E2E phongdao.com ngày 27/10) |
| Trạng thái | **Đề xuất, chờ người sáng lập duyệt.** Chưa có việc nào trong đây được thêm vào `docs/22` |
| Thời gian | 6 tuần tới (tới khoảng giữa 12/2026), rồi sau khi mở |
| Ngân sách | Người sáng lập khoảng 3–5 giờ mỗi tuần (D-035); mọi phần mã do agent làm |

## 1. Cách xếp ưu tiên

1. **P0 — an toàn và đúng bản.** Site thật phải chạy đúng mã đã gộp; cửa quản trị phải khoá. Chưa xong P0 thì mọi việc khác chỉ là trên giấy.
2. **P1 — niềm tin và câu được tìm.** Đưa lên những câu người ta tìm nhiều nhất, có giải nghĩa; cho người đọc thấy ai biên soạn.
3. **P2 — trước khi mở công khai.** Giữ lời hứa về nguồn, trả lời đúng "nghĩa là gì", trang chủ đề đọc liền, tìm chịu gõ sai.
4. **P3 — sau khi mở.** Ghi âm, dị bản có nguồn, dữ liệu mở; chọn theo số liệu người đọc.

Trong cùng một mức:

- việc gỡ nút thắt cho nhiều việc khác làm trước;
- việc tác động cao, công thấp làm trước;
- việc tốn ít giờ của bạn làm trước.

Việc chạm Human Gate chờ bạn trả lời; việc không bị chặn thì agent làm ngay.

## 2. Tóm tắt: 16 việc theo thứ tự

| # | Việc | Mức | Tuần | Ai | Giờ của bạn | Chờ quyết định |
|---|---|---|---|---|---|---|
| 1 | Cho site chạy đúng bản plugin (khởi động lại PHP, kiểm lại) | P0 | 1 | Bạn + agent | 0,25–1 h | Q1 |
| 2 | Kiểm tự động "site thật đúng bản", báo khi lệch | P0 | 1 | Agent | 0 | — (phần trên máy chủ: Q1) |
| 3 | Bộ kiểm E2E chạy được ở mọi nơi, cập nhật kỳ vọng | P0 | 1 | Agent | 0 | — |
| 4 | An toàn tài khoản quản trị (nếu chưa làm) | P0 | 1 | Bạn | 0,5 h | — |
| 5 | 50 câu được tìm nhiều, mỗi tuần một lô 10 câu có Hiểu nhanh | P1 | 1–6 | Agent soạn nháp, bạn duyệt | 2 h/tuần | Q2 |
| 6 | Khối "Biên soạn" cuối bài + dữ liệu có cấu trúc | P1 | 1–2 | Agent; bạn gõ tên | 0,25 h | Q6 |
| 7 | Tiêu đề, mô tả nói rõ trang có nghĩa, cách dùng | P1 | 1–2 | Agent | 0 | — |
| 8 | Khối nghĩa cho tục ngữ – thành ngữ | P2 | 2–4 | Agent; bạn duyệt | (trong việc 5) | — |
| 9 | Tìm chịu gõ sai, "Có phải bạn tìm …?" | P2 | 2–3 | Agent | 0 | — |
| 10 | Đối chiếu lời với sách có bản quét (Wikisource) | P2 | 2–6 | Agent; bạn xác nhận | 1 h/tuần | Q3 |
| 11 | Trang chủ đề, vùng "đọc liền" | P2 | 3–5 | Agent; bạn viết giới thiệu | 0,5 h/chủ đề | — |
| 12 | Cờ "Nghi không phải dân gian" | P2 | 3–5 | Agent; bạn viết căn cứ | 0,5 h/tuần | — |
| 13 | Bộ câu "Có trong SGK" | P2 | 4–6 | Bạn đánh dấu; agent làm trang | 1 h/tuần | — |
| 14 | Bộ nhớ đệm trang (sau khi việc 2 xong) | P2 | 3 | Bạn | 0,5 h | — |
| 15 | Thư cho Ca dao Mẹ | P2 | 2 | Bạn | 0,5 h | Q5 |
| 16 | Giải nghĩa xưa từ sách 1897 | P2 | 5–6 | Agent; bạn duyệt | 0,5 h/tuần | Q4 |

Sau khi mở (P3): ghi âm người thật, dị bản có nguồn và nơi, dữ liệu mở, người soát lời tình nguyện, đọc khi không có mạng, câu mỗi ngày, đố vui (mục 4.4).

## 3. Quyết định cần có

Câu hỏi đầy đủ, phương án và khuyến nghị ở mục 8 của báo cáo khảo sát.

| Câu hỏi | Chặn việc | Nên trả lời | Chưa trả lời thì agent vẫn làm được |
|---|---|---|---|
| Q1. Cách xử lý máy chủ | 1, phần máy chủ của 2 | Ngay | Phần kiểm từ bên ngoài của việc 2; soạn hướng dẫn từng bước |
| Q2. Dồn nội dung vào 50 câu được tìm nhiều | 5 | Tuần 1 | Lập danh sách đề xuất |
| Q3. Dùng hai sách trên Wikisource để đối chiếu; máy gợi ý, bạn bấm | 10 (phần trên site) | Tuần 2 | Đo xem bao nhiêu bài khớp, chỉ báo con số |
| Q4. Đưa lời giải sách 1897 lên trang | 16 | Tuần 4 | Không |
| Q5. Thư cho Ca dao Mẹ kèm đề nghị liên kết | 15 | Tuần 2 | Soạn lại thư |
| Q6. Cuối bài hiện bút danh, tên thật hay "Ban biên soạn" | 6 (phần hiện ra) | Tuần 1 | Làm ô cài đặt; khối chỉ hiện khi bạn điền |

## 4. Chi tiết từng việc

Mỗi việc ghi: vì sao (bằng chứng), làm gì, xong khi nào (đo được), liên quan backlog, Human Gate.

### 4.1. P0 — an toàn và đúng bản (tuần 1)

**Việc 1. Cho site chạy đúng bản plugin**

- **Vì sao.** Ngày 27/10, trang chủ tải `ca-dao.js?ver=0.28.0`, repo đã ở 0.33.0. E2E có 11 kiểm thử hỏng đúng ở các bản sửa chưa lên: XML-RPC, lộ tên đăng nhập, chèn chữ qua `?ly_do=`, Đọc tiếp, 404, kết quả tìm.
- **Giả thuyết (chưa kiểm trên máy chủ).** Theme trên site đã là 0.19.0, bản chỉ có ở commit mới nhất của `main`. Kịch bản `cap-nhat-plugin.sh` thay plugin và theme cùng lúc, nên tệp plugin mới nhiều khả năng đã nằm trên đĩa. Nhưng PHP vẫn chạy mã cũ đã nạp trong bộ nhớ đệm OPcache. OPcache được khuyên bật sau báo cáo E2E ngày 26/10; nếu nó không kiểm thời gian sửa tệp, mã cũ được giữ tới khi PHP khởi động lại. Số phiên bản của theme đọc thẳng từ tệp nên vẫn đúng; số của plugin nằm trong mã PHP nên còn cũ.
- **Bạn làm (khoảng 15 phút):**
  1. Trong FlashPanel, mục PHP: khởi động lại PHP, hoặc tắt rồi bật lại OPcache. Không thấy nút thì báo agent; agent soạn hướng dẫn có ảnh.
  2. Mở `view-source:https://phongdao.com/`, tìm `ca-dao.js?ver=`, báo agent con số thấy được.
  3. Nếu vẫn là 0.28.0: gửi agent 50 dòng cuối của `/home/cadao/cap-nhat.log` (theo Q1).
- **Agent làm:** soạn hướng dẫn có ảnh từng bước; sau khi bạn làm xong thì chạy lại E2E.
- **Xong khi:**
  - trang chủ tải đúng số phiên bản của `main`;
  - gửi yêu cầu POST tới `xmlrpc.php` không còn nhận được danh sách phương thức;
  - `/wp-json/wp/v2/users` không còn trả tên khi chưa đăng nhập;
  - E2E không còn kiểm thử hỏng ngoài dự kiến.
- **Backlog:** T-078. **Gate:** chạm cấu hình máy chủ, chỉ bạn làm hoặc bạn đồng ý cho lần đó (`AGENTS.md`, KHÔNG BAO GIỜ 15).

**Việc 2. Không để lệch bản lần nữa**

- **Vì sao.** Lần lệch này kéo dài nhiều giờ (từ khi bản 0.29.0 gộp chiều 26/10 tới lúc đo sáng 27/10) mà không ai biết; người sáng lập tin là bản sửa an toàn đã lên.
- **Agent làm:**
  - (a) Một GitHub Action chạy sau mỗi lần gộp và mỗi ngày một lần. Nó đọc số `ver=` trên trang chủ và so với `CADAO_PHIEN_BAN` trên `main`; lệch quá 30 phút thì mở một issue "Site thật lệch bản". Chỉ đọc site, không ghi.
  - (b) Đề xuất sửa `cap-nhat-plugin.sh`: sau khi thay tệp, bắt PHP nạp lại mã (cách cụ thể tuỳ quyền của tài khoản `cadao`), rồi kiểm trang chủ báo đúng phiên bản mới; sai thì trả bản cũ và ghi lỗi như hiện nay.
- **Xong khi:** Action có kiểm thử; thử cố ý so với một số sai thì issue được mở. Kịch bản mới chỉ chạy trên máy chủ sau khi bạn đồng ý.
- **Backlog:** đề xuất mới (ĐX-1), nối T-078. **Gate:** (b) chạm máy chủ → Q1.

**Việc 3. Bộ kiểm E2E chạy được ở mọi nơi**

- **Vì sao.** Kịch bản `tests/e2e/` gắn với Chrome cài trên máy và gọi API không qua proxy, nên trong môi trường agent phải viết cấu hình tạm mới chạy được. Lỗi L-10 ("Hello world!") đã hết nhưng kiểm thử vẫn đánh dấu là lỗi đã biết.
- **Agent làm:**
  - thêm biến môi trường cho đường dẫn trình duyệt và proxy;
  - gỡ dấu lỗi đã biết của L-10, và của các lỗi đã sửa khi việc 1 xong;
  - ghi cách chạy vào `tests/e2e/README.md`.
- **Xong khi:** `npm run e2e` chạy được trên máy bạn và trong môi trường agent; 0 kiểm thử hỏng ngoài dự kiến.
- **Backlog:** đề xuất mới (ĐX-2). **Gate:** không. E2E chỉ đọc; nhóm quản trị vẫn cần bạn đưa mật khẩu qua biến môi trường.

**Việc 4. An toàn tài khoản quản trị**

- Nếu chưa làm (đã ghi ở `docs/02`): đổi mật khẩu quản trị, bật xác thực hai lớp, đặt tên hiển thị khác tên đăng nhập.
- Khoảng 30 phút. Việc 1 chỉ khoá cửa bằng mã; mật khẩu cũ đã từng đi qua kênh chữ thường.

### 4.2. P1 — niềm tin và câu được tìm (tuần 1–2, kéo dài tới tuần 6)

**Việc 5. 50 câu được tìm nhiều, mỗi tuần một lô 10 câu**

- **Vì sao.** 5 trong 13 câu quen đã tìm thử là tục ngữ – thành ngữ, mà site đang có 0 bài thể loại này. Bài không có Hiểu nhanh thì không được lập chỉ mục, nên khi mở cũng không hiện trên Google.
- **Agent làm:**
  - lập danh sách đề xuất: các câu trong 13 lần tìm, câu tìm không ra trên Bảng tin, tục ngữ phổ biến trong kho. Mỗi câu ghi lý do chọn. Agent không tự khẳng định câu nào có trong SGK;
  - mỗi tuần soạn nháp Hiểu nhanh cho 10 câu (luôn ở Chờ duyệt, ghi rõ "chưa duyệt");
  - viết hướng dẫn một trang "Duyệt 10 câu trong 30 phút".
- **Bạn làm:** chọn câu; đọc, sửa, đánh dấu "có hỗ trợ AI" nếu dùng nháp, bấm Đăng. Khoảng 2 giờ mỗi tuần.
- **Xong khi:** hết tuần 2 có ít nhất 10 câu tục ngữ – thành ngữ đã đăng, có Hiểu nhanh; hết tuần 6 có ít nhất 50.
- **Phụ thuộc:** việc 1 (tục ngữ chỉ vào hàng Chờ duyệt khi site chạy plugin mới).
- **Backlog:** T-125, T-100, T-048. **Gate:** H2 — bạn duyệt nháp máy và bấm Đăng; máy không đăng.

**Việc 6. Khối "Biên soạn" cuối bài**

- **Vì sao.** vanvn.net có tác giả ký tên, hồ sơ, dữ liệu có cấu trúc `Person`; phongdao.com chưa cho biết ai chịu trách nhiệm. Giáo viên sợ dạy sai (`docs/00` mục 3).
- **Agent làm:** ô cài đặt "Người biên soạn" trong trang quản trị. Khối cuối bài chỉ hiện khi ô có chữ, gồm tên đã điền và liên kết tới trang Phương pháp. Thêm `Organization` (và `Person` khi có tên) vào dữ liệu có cấu trúc.
- **Bạn làm:** gõ bút danh, tên thật hoặc "Ban biên soạn" theo Q6; 15 phút.
- **Xong khi:** mọi trang bài có khối; dữ liệu có cấu trúc hợp lệ; quét trợ năng đạt.
- **Backlog:** đề xuất mới (ĐX-8), nối T-046, T-050. **Gate:** dữ liệu cá nhân — chỉ hiện điều bạn chọn (KHÔNG BAO GIỜ 9).

**Việc 7. Tiêu đề, mô tả hợp ý định tìm**

- **Vì sao.** Người tìm hỏi "nghĩa là gì"; trang văn mẫu và vanvn.net đặt đúng chữ đó vào tiêu đề.
- **Agent làm:** bài đã có Hiểu nhanh hoặc khối nghĩa thì tiêu đề thêm "nghĩa và cách dùng", mô tả lấy từ Hiểu nhanh. Bài chưa có thì giữ như cũ, không hứa điều trang không có.
- **Xong khi:** kiểm thử tiêu đề theo hai trường hợp; không đổi đường dẫn.
- **Backlog:** T-050 (tiếp). **Gate:** không. Tiêu đề không phải lời ca dao; không đổi đường dẫn nên không chạm H6.

### 4.3. P2 — trước khi mở công khai (tuần 2–6)

**Việc 8. Khối nghĩa cho tục ngữ – thành ngữ**

- **Làm:** khối gồm nghĩa đen, nghĩa bóng, "dùng khi", câu gần nghĩa, câu trái nghĩa (liên kết sang nhau), 3–4 dòng.
- **Nguồn:** phần lấy từ Wiktionary ghi "Nguồn: Wiktionary ↗ · CC BY-SA" ngay dưới. Phần còn lại bạn viết, hoặc duyệt nháp máy.
- **Xong khi:** đạt tiêu chí của T-098 trong `docs/22`; 50 câu ở việc 5 đều có khối này.
- **Gate:** H2 (nháp máy chờ duyệt); D-061 (giấy phép).

**Việc 9. Tìm chịu gõ sai**

- **Làm:** khi không có kết quả hoặc kết quả yếu, so gần đúng từng âm tiết đã bỏ dấu và hiện "Có phải bạn tìm …?". Ca dao Mẹ đã làm được (ra đúng bài khi gõ thừa một chữ).
- **Xong khi:** kiểm thử L-09 ("ba co doi gai" ra "Ba cô đội gạo lên chùa") đạt; câu không có trong kho không ra bài lạc đề.
- **Backlog:** đề xuất mới (ĐX-4), nối T-126. **Gate:** không.

**Việc 10. Đối chiếu lời với sách có bản quét**

- **Sách:** *Câu hát góp* (Huỳnh Tịnh Của, bản in 1901) và *Tục ngữ, cổ ngữ, gia ngôn* (1897) trên Wikisource tiếng Việt. Cả hai có bản quét từng trang và thuộc phạm vi công cộng.
- **Bước a (không chờ Q3).** Agent lấy văn bản theo cách Wikimedia cho phép (tải tệp quét hoặc bản xuất, không cào hàng loạt), rồi đo xem bao nhiêu bài đang đăng có câu khớp. Agent chỉ báo con số. Lời từ sách không vào repo công khai: nằm ở kho riêng tư hoặc cơ sở dữ liệu WordPress (KHÔNG BAO GIỜ 14).
- **Bước b (sau Q3 và một quyết định mới nới D-072).** Trang quản trị "Đối chiếu với sách": mỗi dòng là một cặp bài ↔ ảnh trang quét. Hai nút: "Đúng, tr. N" và "Không khớp". Chỉ khi bạn bấm thì hộp "Lời đã đối chiếu với" mới được điền; máy không tự điền. Lời trong sách khác lời phổ biến thì đề xuất thêm dị bản qua đường thu thập, không sửa lời chính.
- **Bạn làm:** khoảng 1 giờ mỗi tuần, 20–30 bài.
- **Xong khi:** ít nhất 30 bài có dòng "Đã đối chiếu: …, tr. N" trước khi mở (gợi ý).
- **Backlog:** T-099 (mở rộng), đề xuất mới (ĐX-3). **Gate:** H3 (nguồn mới); H2 (xác nhận); cần quyết định mới cho D-072 → Q3.

**Việc 11. Trang chủ đề, vùng "đọc liền"**

- **Làm:** trên `/chu-de/<tên>/` và `/vung/<tên>/`, mỗi câu hiện kèm Hiểu nhanh và chú thích địa danh ngay trong danh sách (như đã làm cho mục Tục ngữ – Thành ngữ). Đầu trang là đoạn giới thiệu bạn viết.
- **Lập chỉ mục:** chỉ khi trang có đủ câu đã có Hiểu nhanh; ngưỡng do agent đặt và ghi trong PR.
- **Vì sao.** vanvn.net trả lời truy vấn "ca dao về Huế" bằng một trang đọc liền; phongdao.com có sẵn dữ liệu để làm điều đó mà không cần bài văn dài.
- **Bạn làm:** 0,5 giờ cho mỗi chủ đề; làm trước 3–5 chủ đề, vùng được tìm nhiều (mẹ, thầy cô, Huế, xứ Nghệ…).
- **Backlog:** T-094, T-095 (mở rộng), đề xuất mới (ĐX-9). **Gate:** máy không viết đoạn giới thiệu (H2).

**Việc 12. Cờ "Nghi không phải dân gian"**

- **Làm:** ô bật cờ trong trình soạn, kèm một câu căn cứ bạn viết. Trên trang hiện nhãn xám dưới lời: "Câu này thường được gọi là ca dao, nhưng …". Danh sách lọc được theo cờ.
- **Vì sao.** vanvn.net có hẳn mục "câu hay bị nhầm là ca dao"; học sinh hay trích nhầm thơ có tác giả. Cờ đã có đặc tả ở `docs/20` mục 4.1 và 10 (D-008), chưa làm trên WordPress.
- **Xong khi:** cờ, nhãn, bộ lọc chạy; tắt JavaScript vẫn đọc được; quét trợ năng đạt.
- **Backlog:** đề xuất mới (ĐX-7). **Gate:** H2. Lằn ranh 1–2: chỉ ghi tác giả, tên sách khi có căn cứ đã thấy. Máy có thể gợi ý câu nghi, không tự gắn cờ.

**Việc 13. Bộ câu "Có trong SGK"**

- **Bạn làm:** đánh dấu câu có trong SGK, ghi bộ sách, lớp, bài học; thêm số trang khi đã thấy tận mắt.
- **Agent làm:** trang lọc "Có trong SGK", nối với Trình chiếu.
- **Backlog:** T-062 (một phần), đề xuất mới (ĐX-5). **Gate:** lằn ranh 2 — không ghi sách, trang chưa thấy.

**Việc 14. Bộ nhớ đệm trang**

- **Làm:** bật đệm trang cho người chưa đăng nhập (lỗi L-13), **sau** khi việc 2 xong.
- **Lý do phải đợi:** đệm mới chính là thứ có thể giữ mã, trang cũ. Kịch bản cập nhật phải biết xoá đệm sau mỗi lần thay bản.
- **Bạn làm:** 30 phút trong FlashPanel hoặc Cloudflare, theo hướng dẫn agent soạn.

**Việc 15. Thư cho Ca dao Mẹ**

- Bản nháp ở `docs/12` mục 7; thêm đề nghị liên kết qua lại nếu bạn chọn ở Q5. 30 phút.
- **Backlog:** T-034. **Gate:** H3.

**Việc 16. Giải nghĩa xưa từ sách 1897**

- Lớp riêng "Giải nghĩa trong sách năm 1897", ghi nguồn và trang tại chỗ, giữ chính tả cũ.
- Làm sau bước a của việc 10, chỉ cho câu bạn đã duyệt.
- **Backlog:** T-099. **Gate:** H3 → Q4.

### 4.4. P3 — sau khi mở

Chọn theo số liệu người đọc: câu tìm không ra, bài được trích dẫn nhiều.

| Việc | Backlog | Gate |
|---|---|---|
| Ghi âm người thật (hát ru, hò, lý) có hồ sơ đồng ý | T-067 | Lằn ranh 3 (đồng ý ghi âm) |
| Dị bản có nguồn, nơi, người kể; so sánh dị bản | T-063 | Không ghi nơi, người kể khi không có căn cứ |
| Dữ liệu mở có phiên bản | T-066 | H3 (giấy phép từng phần) |
| Người soát lời tình nguyện qua form "Gửi số trang", ghi bút danh | ĐX-6, nối T-045 | Không tài khoản, không dữ liệu cá nhân |
| Đọc khi không có mạng | T-065 | — |
| Câu mỗi ngày, bài theo dịp | T-061 | — |
| Đố vui gọn | T-096 | — |

## 5. Lịch tuần của người sáng lập

| Tuần | Việc của bạn | Giờ |
|---|---|---|
| 1 | Trả lời Q1–Q6 (30 phút); khởi động lại PHP, báo số phiên bản (15 phút); an toàn tài khoản nếu chưa làm (30 phút); duyệt lô 1 nếu việc 1 xong sớm (2 h) | 1,25–3,25 h |
| 2 | Duyệt lô 2 (2 h); gửi thư Ca dao Mẹ (30 phút); gõ tên cho khối Biên soạn (15 phút) | 2,75 h |
| 3 | Duyệt lô 3, kèm khối nghĩa (2 h); xác nhận đối chiếu 20 bài (1 h); giới thiệu 1 chủ đề (30 phút); bật bộ nhớ đệm (30 phút) | 4 h |
| 4 | Duyệt lô 4 (2 h); đối chiếu (1 h); căn cứ cho cờ nghi (30 phút); đánh dấu câu trong SGK (1 h) | 4,5 h |
| 5 | Duyệt lô 5 (2 h); đối chiếu (1 h); giới thiệu 1–2 chủ đề (30 phút–1 h); SGK (1 h) | 4,5–5 h |
| 6 | Thử với 5 người: 2 giáo viên, 2 người trẻ, 1 người am hiểu dân gian (2 h); xem số liệu; quyết mở hay chưa (30 phút) | 2,5 h |

Tuần nào bận: giảm lô xuống 5 câu, giữ việc 5 trước mọi việc P2.

## 6. Các đợt PR của agent

Gom theo D-066: một PR cho các việc liên quan của một đợt; agent tự gộp khi kiểm tra và CI đạt.

| Đợt | Tuần | Gồm | Ghi chú |
|---|---|---|---|
| 1 · Đúng bản | 1 | Việc 2a, 3; hướng dẫn cho việc 1 và 14 | Không đổi plugin; Action chỉ đọc site |
| 2 · Niềm tin | 1–2 | Việc 6, 7; danh sách đề xuất và hướng dẫn của việc 5 | Tăng số phiên bản plugin, theme |
| 3 · Hiểu và tìm | 2–4 | Việc 8, 9 | Kiểm thử L-09 thành kiểm thử bảo vệ |
| 4 · Khám phá | 3–5 | Việc 11, 12 | Quét trợ năng; tắt JavaScript vẫn đọc được |
| 5 · Nguồn | 2–6 | Việc 10 (bước a ngay; bước b sau Q3), 13, 16 (sau Q4) | Lời từ sách không vào repo công khai |

Việc 2b (sửa kịch bản trên máy chủ) đi riêng, chỉ áp dụng khi bạn đồng ý.

## 7. Cổng mở công khai (H5): gợi ý để bạn quyết

Không điều nào dưới đây là điều kiện bắt buộc (D-077). Đây là mức khuyến nghị để Google thấy được site ngay từ đầu:

- [ ] site chạy đúng bản; E2E 0 kiểm thử hỏng ngoài dự kiến; Action báo lệch đang chạy;
- [ ] sao lưu hằng ngày đã bật và đã thử khôi phục một lần (điều kiện đã có, D-073);
- [ ] khoảng 50 câu có Hiểu nhanh hoặc khối nghĩa, trong đó có tục ngữ – thành ngữ;
- [ ] vài chục bài có dòng "Đã đối chiếu";
- [ ] khối "Biên soạn" hiện trên trang bài;
- [ ] bạn tự tìm 13 câu ở mục 3.3 của báo cáo trên Google bằng điện thoại, ghi lại 10 kết quả đầu (10 phút): để có mốc so sánh sau khi mở.

## 8. Chỉ số theo dõi mỗi tuần

Agent ghi vào mô tả PR của mỗi đợt; bạn xem nhanh trên Bảng tin.

| Chỉ số | Hiện tại (27/10) | Mục tiêu tuần 6 |
|---|---|---|
| Site đúng bản | Không (0.28.0 / 0.33.0) | Có, liên tục |
| Kiểm thử E2E hỏng ngoài dự kiến | 13 | 0 |
| Bài đã đăng | 171 | khoảng 220 |
| Tục ngữ – thành ngữ đã đăng | 0 | ≥ 50 |
| Câu có Hiểu nhanh hoặc khối nghĩa | rất ít | ≥ 50 |
| Bài có dòng "Đã đối chiếu" | 0 | ≥ 30 |
| Trang bài: dung lượng, lỗi trợ năng | 177 KB, 0 | ≤ 300 KB, 0 |
| Giờ của bạn mỗi tuần | — | ≤ 5 |

## 9. Rủi ro và cách giảm

| Rủi ro | Cách giảm |
|---|---|
| Khởi động lại PHP không đủ, site vẫn lệch bản | Đọc nhật ký cập nhật (Q1 phương án B); việc 2 báo ngay khi lệch |
| Bạn không đủ giờ | Lô 5 câu thay vì 10; giữ việc 5 hơn mọi việc P2 |
| Bản chép trên Wikisource sai chữ | Luôn xác nhận bằng ảnh trang quét, không bằng chữ đã chép |
| Thứ hạng đo từ Mỹ không giống Google Việt Nam | Bạn tự kiểm 13 câu trên điện thoại trước khi mở (mục 7) |
| Ca dao Mẹ phản đối việc chép chú thích | Gửi thư sớm (việc 15); công tắc "Chép chú thích" tắt được ngay |
| Bộ nhớ đệm trang lại giữ bản cũ | Chỉ bật sau việc 2; kịch bản cập nhật xoá đệm sau mỗi lần thay |
| Cạnh tranh với trang văn mẫu dài | Không đua độ dài; đua đúng, gọn, có nguồn, nhẹ |

## 10. Không làm trong 6 tuần này

- Không thêm loại tính năng lớn ngoài danh sách trên (giọng sinh sẵn, đố vui, bản đồ mới…).
- Không viết bài văn dài cho từng chủ đề; không để máy viết bài như vậy.
- Không đăng hàng loạt từ kho để tăng số bài (D-045).
- Không quảng cáo, không nút mạng xã hội theo dõi người đọc, không bắt đăng nhập.

## 11. Khi bạn duyệt kế hoạch

Agent sẽ:

- ghi một quyết định mới vào `docs/01` (số tiếp theo, hiện là D-080) cho kế hoạch này; nếu bạn đồng ý Q3, ghi thêm phần nới D-072;
- thêm các đề xuất ĐX-1..ĐX-9 vào `docs/22` thành task, đánh số từ T-129 (theo số lớn nhất lúc đó), với tiêu chí xong ở mục 4;
- cập nhật `docs/02` và bắt đầu đợt 1.

Bạn chỉ cần trả lời: "Duyệt kế hoạch" kèm câu trả lời Q1–Q6, hoặc nói việc nào muốn bỏ hay đổi thứ tự.
