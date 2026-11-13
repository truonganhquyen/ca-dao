# 28 · Luồng nội dung và biên tập V2

| | |
|---|---|
| Phiên bản | 1.3 · 11/11/2026 (ba dòng việc D-103; đợt 3 D-104, D-105 — `docs/32`) · 1.2 · 05/11/2026 (hướng quản trị admin chính: D-096; dữ liệu và lằn ranh giữ D-094) |
| Trạng thái | **Đã duyệt.** Đổi hành vi đã chốt phải ghi D-xxx |
| Đọc khi | Mọi task chạm nguồn, kho, chống trùng, biên tập (đợt 1 và 3) |
| Đọc kèm | `26` (dữ liệu, REST, quyền), `12` (chính sách nguồn), `32` (kế hoạch đợt 3), AGENTS.md (lằn ranh); mục 4, 7 cũ và kế hoạch D-096 ở `docs/luu-tru/` |
| Người dùng chính | Người sáng lập — người biên soạn duy nhất, dưới 10 giờ/tuần, chủ yếu trên điện thoại, không đọc mã |

## 1. Nguyên tắc

1. **Máy làm nặng, người quyết nhanh.** Máy thu thập, chống trùng, gợi ý, tách phần, xếp việc; agent soạn nháp; người biên soạn quyết. Mỗi thẻ việc là **một quyết định**, đủ thông tin để quyết ngay trên thẻ.
2. **Một nơi, ba dòng việc.** Mọi việc biên tập ở wp-admin, chia Bài mới · Hộp việc · Bổ sung (D-103); Hộp việc chỉ chứa việc cần người quyết, luôn ngắn.
3. **Không chặn vì lý do biên tập (D-054).** Chế độ sửa nhắc (thiếu nghĩa, nghi trùng, thiếu nguồn), không chặn Đăng — trừ lỗi kỹ thuật và lằn ranh.
4. **Lằn ranh trong mã, không trong lời dặn** (mục 6).
5. **Điện thoại trước.** Mọi màn hình dùng được một tay ở 390 px; máy tính có phím tắt.
6. **Sửa có ngữ cảnh.** Người biên soạn có thể sửa nhanh trên trang người đọc thấy (kể cả phân loại, thể loại từ đợt 3); quản lý toàn kho và sửa có cấu trúc trong wp-admin. Hai lối dùng chung quy tắc lưu.

## 2. Vòng đời nội dung

```text
Nguồn tự động ──► Kho thu thập ──► Chống trùng ──┬─► trùng hệt / trùng một dị bản đã ghi → tự gắn (xem được, "Tách ra")
(Ca dao Mẹ…)     (riêng tư)                       ├─► có thể trùng → thẻ "Có thể trùng"
                                                  └─► mới → nằm trong Kho; người chọn ở "Bài mới" (đợt 3: không còn thẻ Câu mới hằng ngày)
Nhập tay ─────────────────────────────────────────► Thêm bài (nháp) → sửa trên trang bài
Người đọc gửi ────────────────────────────────────► thẻ "Người đọc gửi"
Agent soạn nháp theo lô ──────────────────────────► việc chờ (nháp nằm ở bảng việc) → thẻ "Nháp ý nghĩa"

CÂU MỚI ── Đăng ──► TÁC PHẨM đã đăng (lời, chú thích máy chép vào bài)
                     └─ máy gợi ý chủ đề / vùng / mô-típ / Wikidata / Bạn có biết → "Bổ sung" (đợt 3: đề xuất trên bài, duyệt theo nhóm)
TÁC PHẨM ── hậu kiểm: báo lỗi, góp ý, "mong có câu này", đọc nhiều chưa L2 → thẻ việc
```

Trạng thái tác phẩm: `nhap` (người đang soạn) · `publish` (đã đăng) · `an` (ẩn khỏi người đọc, giữ dữ liệu). Không còn hàng "Chờ duyệt" hàng loạt do máy tạo (D-086). Mọi phần nằm trong bài là phần người đã quyết; nháp chưa duyệt chỉ nằm ở bảng việc (`docs/26` mục 4.3).

## 3. Nguồn vào

### 3.1. Nguồn tự động

- Cấu hình ở trang **Nguồn & cài đặt** trong wp-admin (quyền `cadao_nguon`): bật / tắt, lịch lấy, giới hạn mỗi lượt, thể loại lấy, **"Chép chú thích"** (tắt được ngay khi nguồn yêu cầu, D-055), hồ sơ nguồn gắn kèm.
- Ca dao Mẹ: bộ đọc tất định chuyển từ MVP (khớp từng ký tự, lịch sự với máy chủ nguồn). Đợt 5 thêm Wikisource (hai sách 1897, 1901 — D-080).
- Kho thu thập là dữ liệu riêng tư: không lên trang, không vào repo công khai (AGENTS.md, KHÔNG BAO GIỜ 14).

### 3.2. Hồ sơ nguồn

Mỗi nguồn (Ca dao Mẹ, Wikipedia, Wiktionary, Commons, Wikidata, sách, người kể, người đọc, người biên soạn) có: loại, tên, tác giả, năm, giấy phép, mẫu ghi công, trạng thái xin phép, chính sách hiện (tại chỗ / Thông tin tư liệu / ẩn), "Chép chú thích". Thiếu thông tin thì để trống, hiện "chưa rõ". **Trạng thái xin phép và giấy phép chỉ người sáng lập đổi** (H3); đổi chính sách hiện → trang nhắc "Đổi ở đây là đổi chính sách — cần ghi quyết định".

**Đợt 3 (D-104, đã làm — plugin 1.0.48):** hồ sơ có thêm trường "Nhà xuất bản"; thêm hồ sơ nguồn bằng tay trong Nguồn & cài đặt (`HoSo::them`, ghi nhật ký người tạo); vai Biên tập thêm được hồ sơ sách (cả ngay trong tấm Đối chiếu sách); chính sách hiện với người đọc, trạng thái xin phép, giấy phép vẫn chỉ Quản trị đổi (mục 6.4). Nhập câu từ tệp (câu gõ từ một cuốn sách) vào Kho gắn với một hồ sơ, qua chống trùng, không đăng thẳng.

### 3.3. Câu mới (ứng viên)

- Máy chọn câu từ kho để đưa lên Hộp việc mỗi ngày (mặc định 10, chỉnh được): ưu tiên câu người đọc **mong có** hoặc **tìm không ra** mà kho có, rồi xoay vòng theo thể loại (tục ngữ – thành ngữ, đồng dao, câu đố, vè, ca dao), câu có chú thích trước.
- Thẻ "Câu mới": lời, dị bản, chú thích (kèm nguồn), thể loại, gợi ý chủ đề / vùng, kết quả chống trùng.
- Hành động: **Đăng** (tạo tác phẩm đã đăng; lời, dị bản, chú thích máy chép vào bài với người làm `may_chep`) · **Sửa rồi đăng** (tạo bài nháp, mở trang bài ở chế độ sửa) · **Để sau** · **Bỏ** (ghi lý do; kho giữ, không hiện lại trừ khi người bấm "Xem lại").

**Đợt 3 (D-104, đã làm — plugin 1.0.45):** máy thôi tạo thẻ Câu mới mỗi ngày; lịch 06:00 ghi danh sách **"Máy gợi ý hôm nay"** (câu "Mong có" trước, rồi xoay vòng thể loại) — một bộ lọc trong Bài mới; đăng một mạch (D-101). Câu muốn quyết sau đánh dấu **"Để dành"** (người chỉ có quyền soạn: **"Đề nghị đăng"**; dấu lưu ở bảng việc, loại `danh_dau`, không phải việc chờ) và nằm lại Kho. Thẻ Câu mới đang mở lúc chuyển (CSDL bước 13) đóng "Chuyển về Bài mới", câu đánh dấu Để dành. **Nhập câu từ tệp** (`DichVu\UngVien\NhapTep`): câu gõ từ sách, cách nhau một dòng trống, vào Kho nguồn "Nhập từ tệp" kèm hồ sơ người chọn, qua chống trùng, không đăng thẳng; khi đăng, lời là người viết, nguồn là hồ sơ đó.

### 3.4. Chống trùng

Giữ thuật toán MVP (dấu vân tay cả lời, cặp âm tiết, bốn mức: y hệt / gần như y hệt / có thể là dị bản / khác; D-065, D-074):
- y hệt, cùng thể loại → tự gắn vào tác phẩm có sẵn;
- trùng đúng một dị bản đã ghi → tự gắn làm dị bản;
- mọi lần tự gắn xem được ở Nguồn & cài đặt → Kho → Đã gộp, bấm "Tách ra" được;
- có thể trùng → thẻ "Có thể trùng": Cùng một bài · Gộp làm dị bản · Là bài khác · Để sau;
- hai tác phẩm đã đăng trùng nhau → thẻ "Gộp tác phẩm": chọn tác phẩm giữ lại; tác phẩm kia thành dị bản, đường dẫn cũ 301, lượt đọc, cảm nghĩ, phân tích, tuyển tập dồn sang; tác phẩm kia vào thùng rác (khôi phục được);
- Thêm bài và tấm sửa Lời: dải "Có thể trùng với …" ngay dưới ô Lời, chỉ báo, không chặn.

### 3.5. Nháp của agent

- Agent lập danh sách đề xuất (câu tìm nhiều, mong có, đọc nhiều chưa L2), soạn nháp **theo lô** cho ý nghĩa, nghĩa đen / bóng / dùng khi, gợi ý câu đố, Bạn có biết — ngoài site, rồi nạp bằng `wp phongdao nhap-nhap <tệp.json>` **khi người sáng lập đồng ý cho lần đó** (AGENTS.md).
- Mỗi nháp thành **một việc chờ** trong bảng việc (người tạo `may`, dấu AI bật); chỉ cho tác phẩm đã đăng; không đụng phần đã có trong bài; không bao giờ cho lời, dị bản. Nháp chỉ vào bài khi người có quyền bấm Duyệt (thành `ai_da_duyet`).
- Agent không tự khẳng định câu có trong SGK, không viết lời dẫn tuyển tập, không viết sự kiện bối cảnh, cách chơi, lý do cờ nghi.

## 4. Nơi làm việc: ba dòng việc (D-100, D-103, D-104)

**wp-admin là nơi làm việc**; sửa nhanh trên trang đọc giữ. Việc chia ba dòng (D-103):

| Dòng | Gồm | Màn |
|---|---|---|
| **Bài mới** | Câu máy thu trong Kho; câu máy gợi ý hôm nay; bài thêm tay; tín hiệu người đọc (mong có, tìm không thấy) để xếp thứ tự | Kho (lọc, chọn nhiều, Xem trước và đăng — D-101), Thêm tác phẩm |
| **Hộp việc** | Chỉ việc cần người quyết: Có thể trùng, nháp chờ duyệt, Người đọc gửi (báo lỗi, vi phạm, dị bản, góp ý, số trang, cảm nghĩ, cách hiểu, bài cảm nhận, cách hiểu), Cần xem | Hộp việc: danh sách + thẻ, tự sang thẻ kế, Hoàn tác 10 giây, phím tắt; cảm nghĩ, cách hiểu: người có quyền duyệt sửa được nội dung và tên hiển thị ngay trên thẻ rồi bấm "Hiện trên trang", bản người đọc gửi giữ trong nhật ký của thẻ (plugin 1.0.52) |
| **Bổ sung** | Bài đã đăng còn thiếu (Ý nghĩa — bài đọc nhiều trước; nghĩa tục ngữ; đáp án, giải thích câu đố; từ khó, dị bản, hình ảnh, đối chiếu sách); gợi ý chủ đề, vùng của máy | Danh sách ưu tiên + trang sửa (lưu xong sang bài kế); duyệt gợi ý theo nhóm hoặc theo bài |

**Màn hiện có (plugin 1.0.44, sau đợt 2 và hai bước đầu đợt 3):** menu Hôm nay · Bài mới · Hộp việc · Bổ sung · Tác phẩm · Nguồn & cài đặt. **Hôm nay** (3 việc gấp nhất, ba ô theo ba dòng việc có nút bắt đầu, Nên làm tuần này, số liệu tuần thu gọn nhớ 1 giờ; trang chào mừng WordPress có khung "Phongdao hôm nay") · **Bài mới** = Kho (Xem trước và đăng một mạch) · Hộp việc (kiểu hộp thư; chỉ việc cần quyết — gợi ý chủ đề, vùng đã sang Bổ sung; sau khi Đăng có "Xem bài · Ẩn nếu nhầm") · **Bổ sung** (bài đọc nhiều chưa có Ý nghĩa, danh sách ưu tiên; duyệt gợi ý chủ đề, vùng theo nhóm hoặc theo bài — mỗi lần là một lô, plugin 1.0.46) · Tác phẩm (tab trạng thái kèm số, lọc nhanh, "Thêm bộ lọc", Sửa · Xem · Ẩn trên hàng, điện thoại dạng thẻ; đọc từ bảng tóm tắt bài) · Sửa tác phẩm (một màn hai cột, "Bài tiếp theo", hàng "Thêm: …", công tắc cờ, Đổi thể loại kèm 301; màn con) · Thao tác hàng loạt (xem trước, kết quả từng mục; màn con, mở từ Hôm nay) · Nguồn & cài đặt (Quản trị: nguồn tự động, hồ sơ nguồn, máy đang làm, chống rác). Trên trang đọc: nút Sửa từng khối, tấm sửa, dải "Đang sửa". Các trang `/bien-tap/` (Hộp việc, Duyệt từng thẻ, Tác phẩm, Số liệu tuần, Thêm bài) còn chạy tới khi đợt 3 chuyển về wp-admin.

**Đích đợt 3** (`docs/32`): menu **Hôm nay · Bài mới · Hộp việc · Bổ sung · Tác phẩm · Nguồn & cài đặt**; trang chào mừng WordPress có khung "Phongdao hôm nay"; câu máy chọn hằng ngày thành bộ lọc "Máy gợi ý hôm nay" trong Kho (thôi tạo thẻ Câu mới); câu kho để quyết sau đánh dấu "Để dành / Đề nghị đăng" thay "Đưa vào Hộp việc"; gợi ý chủ đề, vùng thành đề xuất trên bài; gộp `/bien-tap/` về wp-admin; đổi thể loại có chuyển hướng 301; Đối chiếu sách thành nút riêng, thêm sách tại chỗ; thêm hồ sơ nguồn, nhập câu từ tệp vào Kho; cờ "Có thể không phải dân gian" thành công tắc; sửa phân loại, đổi thể loại ngay trên trang đọc (chế độ sửa).

> **Tên gọi (D-093):** phần `y_nghia` (trước gọi `hieu_nhanh`) hiển thị là **"Ý nghĩa"** ở mọi nơi; với câu đố hiển thị là **"Giải thích đáp án"** (D-105).

### Trang sửa đầy đủ và danh mục (D-109)

Trang sửa có mục lục toàn bài, trạng thái phần chưa lưu và thanh lưu riêng phần đang làm. Từ khó/dị bản hiện tóm tắt, mở từng mục; phần được khôi phục từ bộ đệm luôn mở, không bị gập lại trên điện thoại. Thứ tự phần theo thể loại; Ý nghĩa cũ của tục ngữ giữ nguyên và có nhãn riêng. Bản nháp/đã ẩn không dùng nhãn hứa nội dung đã công khai; còn phần khác chưa lưu thì lưu xong vẫn ở lại bài.

Người có quyền duyệt chọn thể thơ theo bài, bỏ nhãn hoặc quay lại dùng nhận diện máy; sửa lời về sau không ghi đè lựa chọn người. Menu **Danh mục** sau Tác phẩm mở đúng các bộ Chủ đề/Vùng/Mô-típ/Hình thức/Trò chơi. Người có quyền quản lý bộ thêm mục cạnh bộ chọn mà không tải lại hay tự gắn vào bài. Giữ năm thể loại tác phẩm, lịch sử, quyền nội dung và chính sách nguồn. Chi tiết thao tác/kiểm thử ở [docs/36](36-toi-uu-trang-sua-va-danh-muc.md).

## 5. Vai, quyền, nhật ký

| Vai | Quyền | Ghi chú |
|---|---|---|
| Quản trị | tất cả | người sáng lập |
| Biên tập | sửa, duyệt, thêm hồ sơ sách (đợt 3) | mở khi có người thứ hai; bước "người duyệt khác người nhập" cho bài nổi bật và nội dung trẻ em bật được trong Cài đặt |
| Cộng tác | gửi đối chiếu, soát lời, không đăng | người soát lời tình nguyện (đợt 5) |

Nhật ký (bảng việc) ghi mọi lần sửa một phần, quyết một thẻ, đổi trạng thái tác phẩm, hồ sơ nguồn, gộp, tách; xem được ở khối Lịch sử của từng tác phẩm và ở từng nguồn.

## 6. Lằn ranh trong mã (có kiểm thử khẳng định)

1. Phần lời, dị bản không nhận người làm là AI; nháp AI không bao giờ vào lời; nháp chưa duyệt không vào bài.
2. Trạng thái "đã duyệt", dấu "có hỗ trợ AI", trạng thái đăng chỉ đổi bằng thao tác của người có quyền; không lệnh, lịch chạy hay bước nhập nào làm thay (trừ bước nhập dữ liệu cũ mang đúng trạng thái đã có trên MVP).
3. Không trường nguồn nào (sách, trang, đường dẫn, ngày truy cập, người kể, nơi, vùng, SGK) có giá trị mặc định hay do máy điền; máy chỉ gợi ý, người bấm.
4. Trạng thái xin phép, giấy phép của hồ sơ nguồn chỉ quản trị đổi.
5. Kho thô và lời từ kho không ra trang người đọc (kể cả kết quả tìm) khi chưa thành tác phẩm đã đăng; nút sửa, dòng nguồn gốc nội bộ, địa chỉ gốc máy đã lấy không có trong HTML gửi khách.
6. Không thu email, số điện thoại, IP của người đọc; người gửi không bắt buộc tên.

## 7. Nghiệm thu

- Kiểm thử khẳng định đủ 6 lằn ranh ở mục 6 (`test:v2`).
- HTML gửi khách không có nút Sửa, dải "Đang sửa", dòng nguồn gốc nội bộ hay địa chỉ gốc máy đã lấy (`quet:v2`).
- Sửa một phần (ý nghĩa, từ khó, dị bản) ngay trên trang bài từ điện thoại; lưu xong thấy khối mới đúng như người đọc thấy; hoàn tác được.
- `e2e:v2` chạy các luồng: Kho → xem trước → đăng; Hộp việc quyết liền, tự sang thẻ kế, Hoàn tác; nháp → duyệt → người đọc thấy; người đọc gửi → xử lý; sửa tác phẩm hai cột, sang bài kế; thao tác hàng loạt có xem trước.
- Nghiệm thu đợt 3: `docs/32` mục 9.
