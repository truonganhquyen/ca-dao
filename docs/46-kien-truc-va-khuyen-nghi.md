# 46 · Kiến trúc đích và khuyến nghị sản phẩm — V2 Core và Next

| | |
|---|---|
| Ngày | 21/11/2026 |
| Trạng thái | **Lịch sử (21/11/2026)** — định hướng, kiến trúc, lộ trình đã thay bởi `docs/50`–`57`; kế hoạch đang dùng: [57](57-ke-hoach-chot.md). Nguyên trạng lúc lập: **Đề xuất, chờ người sáng lập duyệt.** Mỗi mục KT-x chỉ thành quyết định khi người sáng lập đồng ý ở [49](49-quyet-dinh-va-cau-hoi-mo.md) và được ghi D-xxx ở `docs/01` |
| Đọc khi | Thiết kế hoặc triển khai bất kỳ việc nào ở [48](48-lo-trinh-va-backlog.md) |
| Bộ tài liệu | [45 kiểm kê, đối chiếu](45-kiem-ke-va-doi-chieu.md) · **46 kiến trúc, khuyến nghị (tệp này)** · [47 kế hoạch tổng](47-ke-hoach-tong-core-next.md) · [48 lộ trình, backlog](48-lo-trinh-va-backlog.md) · [49 quyết định, câu hỏi mở](49-quyet-dinh-va-cau-hoi-mo.md) |
| Đọc kèm | `docs/26` (kỹ thuật V2), `docs/27` (trang người đọc), `docs/28` (biên tập), AGENTS.md (lằn ranh) |

---

## 1. Nguyên tắc chọn

1. **Kế thừa trước, mở rộng nhỏ sau, xây mới cuối cùng.** Với mỗi đề xuất, hỏi theo thứ tự: chỗ lưu nào đã có? thêm một trường có đủ không? có phải Human Gate H1 không?
2. **Nội dung quyết tốc độ, không phải phần mềm.** Một tính năng chỉ đáng làm khi không tăng gánh duyệt hoặc có sẵn nội dung để hiện (`docs/00` mục 4, nguyên tắc 10).
3. **Quan hệ do người chọn, lưu ở một phía, phía kia máy tính ngược** (nguyên tắc của D-010; tuyển tập đang làm đúng cách này). Máy chỉ gợi ý, người bấm.
4. **Không thêm bảng khi post meta, term meta, bảng tóm tắt và bộ đệm là đủ** (D-094, D-110). Không cài plugin ngoài cho phần nội dung (ACF, Pods, plugin SEO, plugin "bài liên quan"): mỗi plugin là thêm một mô hình dữ liệu phải giữ đồng bộ.
5. **Quyền và nguồn đã là dữ liệu của từng phần.** Lọc phần nào được hiện, được dùng lại bằng máy từ `nguoi_lam` và hồ sơ nguồn; không thêm cờ nhập tay.
6. **Trống thì ẩn, chỉ hứa điều đã có** (`docs/27` mục 1.7; D-119 (8)).
7. **Lằn ranh không đổi:** AI không viết lời, nguồn, vùng, địa danh, điển tích; máy không tự gắn, không tự đăng, không đặt dấu duyệt.

## 2. Kiến trúc nội dung đích — V2 Core

```text
                 Hồ sơ nguồn (ho_so_nguon): sách · web · người kể · người đọc · wiki
                 giấy phép · xin phép · chính sách hiện · chép chú thích
                        ▲ nguồn của từng phần, từng mục
                        │
Tuyển tập ──mục──►  TÁC PHẨM (tac_pham) ◄──"Tác phẩm được bàn tới"── Bài viết (post)  [MỚI: nối]
(lời dẫn, thứ tự)   lời (Bản tham chiếu, đối chiếu sách) · dị bản        nhóm: Khảo cứu · Bình giảng · Chuyên đề
                    Ý nghĩa / nghĩa · Hình ảnh trong bài · Chuyện quanh bài
                    chú thích ──nối người chọn──► Mục từ (Từ điển)
                    đoạn · bối cảnh · đáp án · media · cờ           loại [MỚI]: Từ ngữ · Địa danh · Nhân vật · Điển tích
                        │                                          (Wikidata đã xác nhận, ảnh, toạ độ → Bản đồ)
                        ├─ thể loại · chủ đề · vùng lưu truyền · mô-típ · hình thức
                        └─ trò chơi (đồng dao: cách chơi, độ tuổi, số người)
```

Chỉ có **hai chỗ mới**, đều là trường thêm vào thực thể đang có: loại của mục từ (term meta) và danh sách tác phẩm trên bài viết (post meta). Không loại bài mới, không bảng mới.

### 2.1. Bảng ánh xạ đề xuất → kiến trúc

ĐT-ND mục 6.1 yêu cầu lập bảng "thực thể hiện có → trường mới → di chuyển dữ liệu → QA" trước khi viết mã. Đây là bảng đó.

| Nhu cầu (ĐT-ND) | Thực thể dùng | Trường mới | Di chuyển dữ liệu | QA chính |
|---|---|---|---|---|
| Bài khảo cứu, bình giảng, chuyên đề | `post` (46 bài đang có) | `_phongdao_tac_pham`: danh sách `{tac_pham_id, ghi_chu?}`; ba chuyên mục WordPress | Không; người nối dần | Bài chưa tích "Đã có quyền đăng" không hiện ở trang tác phẩm; luật lập chỉ mục D-126 không đổi |
| Hồ sơ điển tích, nhân vật, địa danh | `muc_tu` | `_phongdao_loai_muc` ∈ `tu` · `dia_danh` · `nhan_vat` · `dien_tich`; thiếu = `tu` | Không; máy gợi ý "Địa danh" cho mục có toạ độ Wikidata, người xác nhận | Máy không ghi loại; lọc Từ điển; mục từ theo loại qua `quet:v2` |
| Nhân vật, điển tích trong một bài | `chu_thich.muc_tu_id` → mục từ có loại | Không | Không | Bài không nối mục từ có loại thì không hiện gì |
| Ghi chú chung không gắn từ | `chu_thich` có `tu` rỗng | Không | Không | Hiện thành hàng "Ghi chú", tách khỏi "Chuyện quanh bài" |
| Thể loại phụ, lối hát, lối chơi | `hinh_thuc`, nhóm Lối hát của chủ đề | Không | Người gán mục | — |
| Nguồn thư mục, số trang | `ho_so_nguon` + `nguon.trang` từng lần dùng | Không (ngày kiểm liên kết: để sau) | — | — |
| Dị bản có nơi, người kể, năm | `di_ban` | Không | — | — |
| Tuyển tập có người tuyển, ngày cập nhật | `tuyen_tap` + khối Biên soạn | Bút danh người biên soạn trong Cài đặt (đã thiết kế, T-185) | — | Để trống thì không hiện |
| Trạng thái quyền | `ho_so_nguon`, tệp ảnh | Không ở tác phẩm (D-027) | — | — |

## 3. Quyết định kiến trúc đề xuất (KT-1 … KT-12)

Mỗi mục: **chọn** · **không chọn** · **vì sao** · **chi phí** · **gate**. Thứ tự thực hiện ở [48](48-lo-trinh-va-backlog.md).

### KT-1 · Giữ mô hình dữ liệu V2, không thêm loại bài hay bảng của ĐT-ND
- **Chọn:** ánh xạ ở mục 2.1. **Không chọn:** `editorial_article`, `knowledge_entry`, `source_record`, `work_version`, bảng `content_relation`.
- **Vì sao:** mỗi thực thể đề xuất đã có chỗ lưu tương đương (45, mục 3.4); D-094, D-110, D-114 đã từ chối mở rộng nền nội dung vì chi phí. Thêm loại bài nghĩa là thêm màn sửa, quyền, REST, chỉ mục tìm, sơ đồ trang, kiểm thử — cho một người vận hành.
- **Chi phí:** 0. **Gate:** H1 (khẳng định lại hướng).

### KT-2 · Bài viết là `post`, nối tác phẩm bằng một danh sách trên bài viết
- **Chọn:**
  - Lưu ở bài viết: `_phongdao_tac_pham` = danh sách `{tac_pham_id, ghi_chu?}` (ghi chú "vì sao liên quan", ≤ 160 ký tự). Ưu tiên đi qua đường lưu có Lịch sử như `tuyen_tap.muc` (khai thêm loại bài `post` trong `MienDuLieu\Phan`, kiểm bằng `KiemPhan`), miễn là không đụng `post_content` của bài viết; nếu không được thì một hộp sửa riêng có nonce, kiểm quyền, ghi một dòng `sua` vào bảng việc.
  - Màn sửa bài viết: hộp **Phongdao** gồm ô "Đã có quyền đăng" (đã có, D-126) và "Tác phẩm được bàn tới" (tìm bài đã đăng, dán đường dẫn, số ID — dùng lại bộ chọn của màn Tuyển tập).
  - Chỉ mục ngược: một tuỳ chọn không tự nạp, `tac_pham_id → [post_id]`, dựng lại khi lưu, bỏ vào thùng rác, đổi trạng thái hay đổi ô quyền của bài viết.
  - Trang tác phẩm: hàng mở **"Bài viết về bài này"**, tối đa 3 bài, mỗi bài: tên, nhóm, ghi chú; **chỉ** bài viết đã đăng và đã tích "Đã có quyền đăng".
  - Nhóm bài viết: ba chuyên mục WordPress (đề xuất: Khảo cứu · Bình giảng · Chuyên đề); "Cảm nhận" vẫn là bài của bạn đọc (`phan_tich`), không lẫn.
  - Mẫu trang bài viết (`single.html`): nhãn nhóm, "Người viết" (chữ người biên soạn nhập, không lấy tài khoản WordPress), "Biên tập: <bút danh>" (KT-11), ngày đăng và ngày sửa, khối "Các tác phẩm trong bài" bằng thẻ bài có sẵn, mục lục thu gọn khi bài có từ 4 tiêu đề.
- **Không chọn:** loại bài riêng cho bài nghiên cứu; lưu quan hệ ở phía tác phẩm (hai nơi cùng một sự thật); plugin "bài liên quan" tự động.
- **Vì sao:** 46 bài viết đã có, ô quyền đã có, mẫu trang đã có đầu/chân trang (D-125). Chỉ thiếu sợi nối. Chỉ hiện bài đã có quyền để không quảng bá nội dung chưa được phép và không dẫn người đọc sang trang `noindex`.
- **Chi phí:** M. **Gate:** H1 (trường mới) + H4 (hàng mới trên trang tác phẩm, mẫu bài viết mới); quyền từng bài viết vẫn do người sáng lập tích (D-126).

### KT-3 · Mục từ có loại: Từ ngữ · Địa danh · Nhân vật · Điển tích
- **Chọn:**
  - Term meta `_phongdao_loai_muc`; thiếu = `tu` (không phải sửa dữ liệu cũ). Người có `cadao_soan` chọn ở tấm sửa mục từ (`BienTap/SuaMucTu`), lưu có nhật ký.
  - Máy gợi ý "Địa danh" cho mục có toạ độ Wikidata đã xác nhận, hiện thành danh sách ở Bổ sung (cạnh "Gợi ý Từ điển"); người bấm Đồng ý từng mục hoặc cả nhóm. Không gợi ý Nhân vật, Điển tích (không có căn cứ máy đọc được).
  - Từ điển: chip lọc "Tất cả · Từ ngữ · Địa danh · Nhân vật · Điển tích" bằng tham số GET, có số mục, ẩn loại 0; chạy khi tắt JavaScript.
  - Trang mục từ: nhãn loại dưới tên; Địa danh giữ bản đồ nhỏ đang có; Điển tích và Nhân vật dùng khối "Câu có chữ …" đang có (D-126), đổi tiêu đề theo loại.
  - Trang tác phẩm: một dòng "Trong bài có: …" liệt kê mục từ loại Nhân vật / Điển tích / Địa danh mà chú thích của bài **đã được người nối** (tối đa 6, dẫn sang mục từ). Vè có lợi nhất.
  - Đọc tiếp: lý do theo loại ("Cùng điển tích …", "Cùng nhắc tới nhân vật …", "Cùng nhắc tới nơi …") thay câu chung "Cùng nhắc tới …".
- **Không chọn:** loại bài "hồ sơ tri thức" riêng; máy tự xếp loại theo chữ; ô `boi_canh.nhan_vat` (KT-4).
- **Vì sao:** `docs/26` mục 4.2 đã mô tả trường này (người sáng lập đã duyệt tài liệu) nhưng mã chưa làm (45, L1–L2). Từ điển, đường nối từ khó → mục từ và gợi ý nối (D-125, D-126) đã chạy; thêm một trường là có ngay hồ sơ điển tích, nhân vật, địa danh dùng lại ở nhiều bài.
- **Chi phí:** M. **Gate:** H1 (thêm trường, thêm giá trị `dien_tich` ngoài ba loại đã ghi) + H4 nhỏ (lọc, dòng "Trong bài có").

### KT-4 · Nhân vật trong bài suy từ chú thích, không làm ô nhập riêng
- **Chọn:** "Nhân vật trong bài" = mục từ loại Nhân vật mà chú thích của bài đã nối (KT-3). Giữ trường `boi_canh.nhan_vat` trong schema, không xoá, ghi rõ trong `docs/26` là chưa dùng.
- **Vì sao:** một cách nối duy nhất (chú thích → mục từ) thay vì hai; không phải di chuyển dữ liệu.
- **Chi phí:** S (nằm trong KT-3). **Gate:** không riêng.

### KT-5 · Máy nhắc chất lượng mới ở Bổ sung → Cần sửa
- **Chọn:** thêm năm nhóm vào `MienDuLieu\KiemTrinhBay` và `DichVu\CanSua` (cùng cơ chế "Bỏ qua" `_phongdao_bo_nhac`), **chỉ nhắc, không sửa chữ**; lời và đáp án không bị xét.

| Nhóm | Dấu hiệu máy đọc | Lời nhắc (người quyết) |
|---|---|---|
| Ghi chú có thể là dị bản | Chú thích không gắn từ, mở đầu "Có bản", "Có nơi", "Bản khác", "Sách khác", "Có sách", "Cũng có câu" | Cân nhắc ghi thành Dị bản (khi có đủ lời và nguồn) hoặc giữ làm Ghi chú |
| Địa giới có thể đã đổi | Chú thích có "nay là", "nay thuộc", "ngày nay là", "hiện nay thuộc" | Ghi tên theo thời điểm của nguồn, kèm năm; địa giới đổi nhiều sau năm 2025 |
| Hình ảnh trong bài nhắc lại Ý nghĩa | Trùng từ 60% âm tiết trở lên với Ý nghĩa | Giữ từng cặp "hình ảnh → điều được nói tới", bỏ phần đã nói |
| Có thể là thơ của tác giả | Chú thích hoặc Ý nghĩa có "nhà thơ", "tác giả", "bài thơ", "thơ của" | Xét bật cờ "Có thể không phải dân gian" (nhóm `tac_gia` hiện chỉ xét Ý nghĩa) |
| Tên tuyển tập lệch số câu | Tên có số ("Top 10", "20 câu") khác số câu đã đăng trong bộ | Sửa tên hoặc sửa bộ (hiện ở cột "Còn thiếu gì" của màn Tuyển tập) |

- **Vì sao:** đúng các lỗi ĐT-ND tìm thấy (45, mục 3.2); khung "máy nhắc lỗi trình bày, không chặn" đã được duyệt ở D-119 (9).
- **Chi phí:** S. **Gate:** không.

### KT-6 · Chú thích chung không gắn từ hiện thành hàng "Ghi chú"
- **Chọn:** hàng mở "Ghi chú" riêng cho mục `chu_thich` có `tu` rỗng; "Chuyện quanh bài" chỉ còn phần `chuyen_quanh` thật; tục ngữ giữ hàng "Nguồn gốc" cho mục trùng cả câu. Màn sửa giữ nút "Chuyển sang Chuyện quanh bài".
- **Vì sao:** hiện người đọc thấy ghi chú về một cách chép khác dưới tên "Chuyện quanh bài" (45, mục 3.2) — tên hàng hứa giai thoại, điển tích.
- **Chi phí:** S. **Gate:** H4 nhỏ (đổi một phần của D-119 (7)).

### KT-7 · Không làm bảng quan hệ chung; đối đáp và "Đọc thêm" ngoài để sau
- **Không chọn bây giờ:** bảng `content_relation`; quan hệ đối đáp giữa hai bài; danh sách liên kết đọc thêm ra ngoài.
- **Điều kiện mở lại:** đối đáp — người sáng lập có sẵn ít nhất 10 cặp có căn cứ (khi đó mở rộng `gan_nghia` sang ca dao với quan hệ `dap`, không bảng mới); đọc thêm ngoài — có ít nhất 10 liên kết đáng giới thiệu, mỗi liên kết là một hồ sơ nguồn loại web (tên miền tự vào danh sách cho phép, `docs/27` mục 9.4); bảng chung — có từ 3 loại quan hệ người chọn mới với hơn 200 cặp và cần truy vấn hai chiều mà meta cộng bộ đệm không làm được.
- **Chi phí:** 0. **Gate:** H1 khi mở lại.

### KT-8 · Giữ mô hình trạng thái chất lượng hiện có
- **Chọn:** nhãn nguồn do máy tính từ dữ liệu (D-041, D-072, D-093); cờ "Có thể không phải dân gian" có lý do; dấu AI + người duyệt + ngày duyệt; quyền ở hồ sơ nguồn và tệp ảnh.
- **Không chọn:** trạng thái quyền ở cấp tác phẩm (D-027); nhãn "chuyên gia thẩm định" (D-013); ô trạng thái nhập tay song song với dữ liệu.
- **Chi phí:** 0. **Gate:** không.

### KT-9 · Vè giữ Bối cảnh và Ý nghĩa trước lời
- **Chọn:** giữ D-119 (5); Ý nghĩa của vè đặt trước lời dài nên ngắn (2–3 câu theo quy tắc viết); bài quá dài dùng danh sách "Ý nghĩa dài" đã có ở Bổ sung để người rút gọn.
- **Không chọn:** đưa Ý nghĩa xuống sau lời cho mọi vè (ĐT-ND mục 1.1).
- **Vì sao:** lời vè dài (Vè cô Giang riêng lời đã khoảng 1.260 px trên điện thoại — `docs/22`, T-214); người đọc cần biết bài kể chuyện gì trước khi đọc; vấn đề thật là Ý nghĩa quá dài, không phải vị trí.
- **Chi phí:** 0 (việc nội dung). **Gate:** H4 nếu người sáng lập muốn đổi.

### KT-10 · Điều hướng: chưa thêm "Bài viết" vào menu chính
- **Chọn:** lối vào bài viết trước hết từ trang tác phẩm (KT-2); trang Khám phá thêm mục "Bài viết" khi có từ 3 bài đã có quyền và đã nối; menu chính khi có từ 10 bài.
- **Vì sao:** chỉ hứa điều đã có (D-119 (8)); 46 bài hiện chưa rõ quyền.
- **Chi phí:** S. **Gate:** H4.

### KT-11 · Khối Biên soạn, bảng độ phủ — hai việc nhỏ dùng lại dữ liệu có sẵn
- **Khối Biên soạn:** bút danh người biên soạn ở Nguồn & cài đặt → Cài đặt chung; hiện cuối trang tác phẩm, tuyển tập ("Biên soạn: …, cập nhật …"), bài viết ("Biên tập: …"); để trống thì không hiện; liên kết trang Phương pháp. Đã duyệt từ D-080 (6), nằm trong T-185.
- **Bảng độ phủ theo thể loại** ở màn Số liệu: hàng là năm thể loại; cột là đã đăng · có phần riêng (lên Google) · L2 trở lên · thiếu Ý nghĩa (câu đố: thiếu giải thích; tục ngữ: thiếu nghĩa) · có cờ. Đọc từ `wp_cadao_tom_tat` (`the_loai`, `cap`, `thieu`, `co`) và danh sách bài lập chỉ mục đã đệm (`Seo\Module::ds_lap()`); không truy vấn meta từng bài, không cột mới.
- **Chi phí:** S mỗi việc. **Gate:** không.

### KT-12 · Đố vui chỉ lấy câu đố đã có người biên soạn
- **Chọn:** trang `/do-vui/` theo thiết kế (`docs/27` mục 7.6), nhưng chỉ lấy câu đố đã đăng **có Giải thích đáp án do người viết hoặc AI đã được duyệt**, không bật "Có từ ngữ thô tục", không có cờ; nhóm theo chủ đề nhóm "Loại câu đố"; trang tự ẩn khi dưới 10 câu đủ điều kiện; `noindex` (trang tương tác ghép từ bài đơn, Google đã có từng bài). Trò chơi `/tro-choi/` lọc theo đúng các ô dữ liệu đang có (số người, độ tuổi, đồ dùng) — "nơi chơi", "thời lượng" trong thiết kế chưa có trường, để sau.
- **Không chọn:** lấy ngẫu nhiên cả 765 câu đố.
- **Vì sao:** câu đố tục giảng thanh chưa được đánh dấu (công tắc thô tục chờ người sáng lập); trang này phục vụ phụ huynh và trẻ.
- **Chi phí:** M. **Gate:** không (thiết kế đã duyệt) — riêng quy tắc chọn câu nên được người sáng lập xác nhận (49, QĐ-6).

## 4. Khuyến nghị sản phẩm V2 Core theo người dùng

| Người dùng (`docs/00` mục 3) | Đang có | Nên thêm, theo thứ tự | Đo bằng |
|---|---|---|---|
| Học sinh, người đọc phổ thông | Tìm không dấu, Ý nghĩa, trích dẫn | Ý nghĩa cho câu được tìm, đọc nhiều (vòng nội dung) | Bài có phần riêng; `tim_khong_ket_qua` giảm |
| Giáo viên (Cô Hạnh) | Tuyển tập, Trình chiếu, Đã lưu | Sao chép cả bộ; khối Biên soạn; sau đó Cho lớp học và phiếu in | `trich_dan_sao_chep`; thử tác vụ `docs/27` mục 12 |
| Phụ huynh | Đọc cùng bé, Cách chơi | Trò chơi có lọc theo tuổi; Đố vui từ câu đã duyệt | Thử tác vụ "chọn trò chơi ≤ 1 phút" |
| Người yêu văn hoá, nghiên cứu | Dị bản, dòng nguồn, Bản đồ, Từ điển | Loại mục từ (nhân vật, điển tích, địa danh); bài viết nối tác phẩm; về sau đối chiếu sách Wikisource | Số mục từ có loại; số bài viết đã nối |
| Người góp | Góp một câu, Ở quê nói khác, ghi công theo đồng ý | Giữ; không mở thêm lối khi Hộp việc chưa vơi | Tuổi thẻ Người đọc gửi |
| Người biên soạn | Bài mới · Hộp việc · Bổ sung, Cần sửa, nháp theo lô | Sổ tay biên tập; máy nhắc mới; bảng độ phủ | Thời gian duyệt một thẻ ≤ 60 giây (`docs/00` mục 8) |

## 5. Kiến trúc NEXT (chỉ hoạch định, chưa triển khai)

### 5.1. Nguyên tắc

1. WordPress vẫn là nguồn sự thật; không chép kho sang ứng dụng khác; mỗi kênh dẫn về trang tác phẩm.
2. **Một lớp lọc dùng lại duy nhất** (đề xuất tên `MienDuLieu\TaiDung`, PHP thuần, có kiểm thử), tính từ dữ liệu đang có:

| Được đưa ra ngoài | Không được đưa ra ngoài |
|---|---|
| Lời và dị bản (di sản dân gian), luôn kèm đường dẫn bài và dòng nguồn | Phần `may_chep` từ hồ sơ có `xin_phep` khác "đồng ý" / "không cần" — hiện là chú thích chép từ Ca dao Mẹ |
| Phần `nguoi_viet`, `ai_da_duyet` (CC BY 4.0 của dự án) | Mọi thứ còn trong bảng việc (nháp, gợi ý, bản người đọc gửi chưa duyệt) |
| Phần `doc_gia_gop` đã duyệt (người gửi đã đồng ý CC BY 4.0) | `nguon_may`, địa chỉ gốc máy đã lấy |
| Phần chép từ nguồn mở khi hồ sơ có giấy phép mở, giữ giấy phép và ghi công tại chỗ | Bài có "Có từ ngữ thô tục" (trừ kênh người lớn chọn), bài chưa đăng, ảnh chưa có ghi công |

3. **Thu hồi:** kênh đọc thẳng từ site (khung nhúng, liên kết, QR) tự cập nhật khi bài sửa hoặc ẩn; kênh xuất tệp có số phiên bản, ngày và danh sách mục đã gỡ.
4. Không tài khoản người đọc, không cookie, không thu IP. Mọi số đo mới phải có D-xxx (AGENTS.md, KHÔNG BAO GIỜ 8).
5. Dịch vụ tách riêng chỉ khi có lý do đo được về tải, bảo mật hay vòng đời (giống ĐT-NEXT 12.1.5).

### 5.2. Thứ tự kênh, từ rủi ro thấp đến cao

| # | Kênh | Dùng lại | Điều kiện mở | Gate |
|---|---|---|---|---|
| 1 | Xuất dữ liệu mở có phiên bản (CSV/JSON, giấy phép kèm theo) | T-163, T-066; `TaiDung` | Ngưỡng nội dung (49, QĐ-9); người sáng lập chọn phạm vi lời được xuất | H3 |
| 2 | "Bộ của tôi" trên máy người đọc: từ Đã lưu → phiếu in A4, ảnh, trình chiếu, sao chép kèm nguồn ("Creator Studio" không tài khoản) | Đã lưu, Trình chiếu, ảnh chia sẻ, T-062, phần phiếu in của T-159 | Có bộ tuyển tập và bài có Ý nghĩa đủ dày | H4 |
| 3 | Nhúng "Câu hôm nay", "Đố một câu" bằng khung nhúng (`/nhung/…`, `noindex`, không mã ngoài) | Khối Câu hôm nay, Đố vui | Đố vui chạy ổn; không đo lượt hiển thị (hoặc có D mới) | H4 |
| 4 | Đối tác địa điểm (tuyến di sản, triển lãm): tuyển tập theo địa điểm + mã QR trỏ trang có sẵn | Tuyển tập, mục từ Địa danh, Bản đồ | Có đối tác thật; từng điểm có nguồn và đã kiểm thực địa | H2, H4 |
| 5 | API đọc cho đối tác (khoá, giới hạn tần suất, chỉ phần `TaiDung` cho phép) | REST `phongdao/v1` | Có đối tác ký thoả thuận | H3, H4 |
| 6 | Ký ức, ghi âm cộng đồng (riêng tư mặc định, rút lại được) | Phần `media` `giong_nguoi`, `docs/luu-tru/41-ghi-am-va-dong-y.md`, T-067 | Hồ sơ đồng ý, nơi lưu riêng tư, người duyệt | H3, lằn ranh 3 |

**Sách và học liệu:** tuyển tập in (bản in, PDF) là bước tiếp của kênh 2; sách in có bán chờ Q-008 và rà quyền từng thành phần. **Trò chơi:** Đố vui (Core) và trò chơi đồng dao là "trò chơi hoá" duy nhất phù hợp — không điểm thưởng, không bảng xếp hạng, không chuỗi ngày (`docs/00` mục 5).

### 5.3. Không làm (NEXT)

Trợ lý nghiên cứu hay chatbot AI (`docs/00` mục 5: "không bao giờ"); tài khoản, thuê bao, watermark, CRM, thanh toán trước khi có pháp nhân (Q-008); AR, 3D, ứng dụng iOS / Android; microsite theo đơn; cuộc thi sáng tạo khi chưa có người kiểm duyệt; giọng AI giả giọng vùng hay người thật (D-063); bán hoặc cấp "độc quyền" phần đã CC BY.

## 6. Rủi ro kiến trúc

| Rủi ro | Mức | Cách giảm |
|---|---|---|
| Thêm trường mà không có người điền → trang có khối mới nhưng rỗng | TB | Ẩn khi trống; chỉ làm KT-2, KT-3 khi người sáng lập đồng ý dành giờ nối; đo bằng bảng độ phủ |
| Nối bài viết chưa có quyền | TB | Chỉ hiện bài đã tích "Đã có quyền đăng"; ô quyền chỉ người biên soạn đổi |
| Loại mục từ sai (vd. một địa danh bị xếp là nhân vật) | Thấp | Người chọn; có Lịch sử; mặc định là Từ ngữ |
| Máy nhắc quá nhiều, gây nhiễu | Thấp | Ngưỡng bảo thủ; "Bỏ qua" được nhớ; đếm số nhắc trước khi bật trên site thật |
| Chỉ mục ngược của bài viết lệch | Thấp | Dựng lại ở mọi móc lưu, thùng rác, đổi trạng thái; lệnh WP-CLI dựng lại toàn bộ |
| Kênh NEXT làm lộ phần chưa có quyền | Cao | Mọi kênh đi qua `TaiDung`; kiểm thử khẳng định chú thích chép từ Ca dao Mẹ không có trong bất kỳ đầu ra nào |
| Mở rộng làm phình mã khó bảo trì | TB | Không plugin ngoài; mỗi trường mới có kiểm thử PHP thuần + Playground; xoá mã chết trong cùng PR |
