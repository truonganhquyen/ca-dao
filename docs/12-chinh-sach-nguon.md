# 12 · Chính sách nguồn

| | |
|---|---|
| Phiên bản | 1.0 · 04/10/2026 |
| Trạng thái | **Ổn định.** Danh sách tư liệu ở mục 6 **chưa xác minh** |
| Đọc khi | Trước khi tạo tệp `content/nguon/` đầu tiên; khi dùng một tư liệu mới |

> Phần pháp lý dưới đây là định hướng thực hành, **không phải tư vấn pháp lý**. Trước khi hợp tác với tổ chức hoặc mở rộng quy mô, nên hỏi người có chuyên môn về sở hữu trí tuệ tại Việt Nam.

---

## 1. Nguyên tắc

1. **Nguồn là thứ người biên soạn đã cầm trên tay.** Thông tin thư mục (tác giả, tên sách, NXB, năm, lần in, trang) chỉ ghi khi nhìn thấy trên bản in hoặc bản quét của chính lần in đó. Không lấy từ trí nhớ, trang web khác hay AI.
2. **Truy về nguồn sớm nhất có thể.** Một trang web chép lại không phải là nguồn nếu có thể truy về tài liệu in trước đó.
3. **Không biết thì ghi `CAN_BO_SUNG`.** Máy sẽ chặn xuất bản cho tới khi điền.

## 2. Thứ bậc nguồn

| Bậc | Loại | Dùng để |
|---|---|---|
| 1 | Công trình nghiên cứu, tuyển tập có biên soạn khoa học | Mức A |
| 2 | Từ điển (tiếng Việt, Hán Việt, phương ngữ) | Nguồn cho mục từ |
| 3 | Giáo trình văn học dân gian, sách giáo khoa | Mức A cho lời; nguồn cho diễn giải |
| 4 | Tư liệu điền dã có phiếu hoặc ghi âm, có đồng ý | Mức B |
| 5 | Tạp chí chuyên ngành, luận văn | Mức A (lời), diễn giải |
| 6 | Bài báo, trang web có dẫn nguồn | Chỉ là đầu mối để truy về bậc 1–5 |
| 7 | Đóng góp cộng đồng | Mức C |
| — | AI | **Không bao giờ là nguồn** |

## 3. Cách ghi nguồn

- Mỗi tư liệu một tệp `content/nguon/src-<slug>.yaml` (mẫu: `templates/nguon.yaml`).
- Số trang ghi ở **chỗ dùng** (`nguon[].trang` của dị bản, diễn giải, mục từ), không ở tệp nguồn.
- Hai lần in khác nhau của cùng một sách có số trang khác nhau → hai tệp nguồn, ghi rõ `tai_ban`.
- `da_xac_minh_ban_in: true` chỉ khi đã đối chiếu **toàn bộ** thông tin thư mục trên bản in (R17).
- Ảnh chụp trang sách làm bằng chứng lưu ở thư mục riêng tư (`30` mục 4), không đưa vào kho công khai nếu sách còn bản quyền.

## 4. Bản quyền khi dùng tư liệu

| Được | Không được |
|---|---|
| Đối chiếu lời ca dao với tuyển tập, trích lời kèm tên sách và số trang | Sao chép chú thích, lời bình, lời giới thiệu, cách sắp xếp và phân loại của tuyển tập hiện đại — đây là sáng tạo của người biên soạn và có bản quyền |
| Viết giải nghĩa bằng lời của dự án, dẫn nguồn khi dựa vào ý của tài liệu khác | Chép nguyên văn giải nghĩa của sách hoặc từ điển còn bản quyền (trừ trích dẫn ngắn có ghi nguồn) |
| Dùng tư liệu thuộc phạm vi công cộng (sau khi xác minh) | Mặc định "dân gian thì mọi lớp đều tự do": văn bản dân gian, bản biên khảo, chú giải, bản ghi âm và hình ảnh có thể chịu các chế độ quyền khác nhau |

Theo hiểu biết hiện có, Luật Sở hữu trí tuệ Việt Nam có điều khoản riêng về tác phẩm văn học nghệ thuật dân gian, yêu cầu người sử dụng dẫn chiếu xuất xứ và giữ gìn giá trị đích thực của tác phẩm. Điều này trùng với nguyên tắc của dự án; cần kiểm tra văn bản luật hiện hành.

## 5. Ma trận giấy phép của dự án

| Nội dung | Giấy phép | Ghi chú |
|---|---|---|
| Lời ca dao, tục ngữ | Ghi là di sản dân gian, không thuộc sở hữu của dự án | Luôn dẫn xuất xứ, nguồn tư liệu |
| Chú giải, diễn giải, mô tả tình huống do dự án viết | **Chờ Q-003** (khuyến nghị CC BY-SA 4.0) | |
| Dữ liệu cấu trúc (YAML/JSON) | Như dòng trên | Thống nhất để đơn giản |
| Mã nguồn | MIT | |
| Ghi âm cộng đồng | CC BY 4.0 | Kèm đồng ý của người thể hiện (`41`) |
| Thẻ chia sẻ | Theo giấy phép nội dung trên thẻ | Thẻ luôn ghi nguồn |

## 6. Danh sách tư liệu khởi đầu (CHƯA XÁC MINH)

Đây là điểm khởi đầu từ các bản ý tưởng. **Phải xác minh** năm, NXB, lần tái bản trên bản in thật trước khi đặt `da_xac_minh_ban_in: true`. Các bản ý tưởng đã ghi năm khác nhau cho cùng một sách (ví dụ sách của Vũ Ngọc Phan: 1956 và 1965) — đó là lý do không được chép thông tin thư mục từ tài liệu thiết kế.

| Tư liệu | Dùng cho | Ghi chú cần kiểm | Tệp |
|---|---|---|---|
| Nguyễn Xuân Kính, Phan Đăng Nhật (chủ biên), *Kho tàng ca dao người Việt* | Lời ca dao (mức A) | Lần in đầu khoảng 1995, có bản bổ sung về sau | `src-kho-tang-ca-dao-nguoi-viet` |
| Nguyễn Xuân Kính và cộng sự, *Kho tàng tục ngữ người Việt* | Lời tục ngữ | Khoảng 2002 | (chưa tạo) |
| Vũ Ngọc Phan, *Tục ngữ ca dao dân ca Việt Nam* | Lời; diễn giải | In lần đầu những năm 1950, tái bản nhiều lần | `src-vu-ngoc-phan-tuc-ngu-ca-dao-dan-ca` |
| Nguyễn Văn Ngọc, *Tục ngữ phong dao* | Lời, tư liệu cũ | In lần đầu cuối thập niên 1920; có thể thuộc phạm vi công cộng — cần xác minh | (chưa tạo) |
| Các tuyển tập ca dao, dân ca theo vùng (Nghệ Tĩnh, Bình Trị Thiên, Nam Bộ…) | Dị bản địa phương | Ưu tiên khi làm cân bằng vùng | (chưa tạo) |
| Huỳnh Tịnh Của, *Đại Nam quấc âm tự vị* | Từ cổ | Cuối thế kỷ 19; khả năng cao thuộc phạm vi công cộng — cần xác minh | (chưa tạo) |
| Đào Duy Anh, *Hán Việt từ điển* | Hán Việt, điển tích | | (chưa tạo) |
| Hoàng Phê (chủ biên), *Từ điển tiếng Việt* | Nghĩa từ | | (chưa tạo) |
| Các từ điển phương ngữ (Nam Bộ, Huế, Nghệ…) | Phương ngữ | | (chưa tạo) |
| Giáo trình Văn học dân gian Việt Nam của các khoa Ngữ văn | Thể loại, diễn xướng, diễn giải | | (chưa tạo) |
| Sách giáo khoa Ngữ văn, 3 bộ hiện hành | Tầng ưu tiên 1; trường `sgk` | Rà mục lục từng lớp | (chưa tạo) |

## 7. Khi phát hiện nguồn sai

1. Hạ mức dị bản (A → D nếu không còn nguồn đúng) và ghi lý do trong commit.
2. Nếu dị bản đó là bản tham chiếu: chuyển tác phẩm về `an` cho tới khi sửa xong.
3. Báo lỗi đến từ người dùng được ưu tiên xử lý trước mọi đóng góp khác.
