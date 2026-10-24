# 12 · Chính sách nguồn

| | |
|---|---|
| Phiên bản | 2.0 · 23/10/2026 (nguồn web, Ca dao Mẹ, cách dùng chú thích: D-037, D-045, D-046) |
| Trạng thái | **Ổn định.** Danh sách tư liệu ở mục 8 **chưa xác minh** |
| Đọc khi | Trước khi tạo tệp `content/nguon/`; khi thu thập từ một trang web; khi dùng chú thích của người khác |

> Phần pháp lý dưới đây là định hướng thực hành, **không phải tư vấn pháp lý**. Trước khi hợp tác với tổ chức hoặc mở rộng quy mô, nên hỏi người có chuyên môn về sở hữu trí tuệ tại Việt Nam.

---

## 1. Nguyên tắc

1. **Mọi câu có nguồn gốc ghi lại được.** Lời lên trang khi biết nó đến từ đâu: một trang in (có số trang), một bản quét, một trang web (có URL, ngày truy cập, bản lưu trữ), một người kể hoặc một đóng góp. Không có nguồn gốc thì không công khai (R21).
2. **Nhãn nói thật mức kiểm.** Lời lấy từ trang web là "Lời phổ biến – chưa đối chiếu" (nhãn D) cho tới khi được đối chiếu với tư liệu (nhãn A). Nhãn do máy tính từ nguồn, không ai nhập tay (D-041).
3. **Thông tin thư mục chỉ ghi khi nhìn thấy.** Tác giả, tên sách, NXB, năm, lần in, trang chỉ ghi khi nhìn thấy trên bản in hoặc bản quét của chính lần in đó. Không lấy từ trí nhớ, trang web khác hay AI.
4. **Truy về nguồn sớm nhất có thể.** Trang web là đầu mối; đối chiếu được với tư liệu in thì nâng nhãn.
5. **Không biết thì ghi `CAN_BO_SUNG`.**

## 2. Thứ bậc nguồn

| Bậc | Loại | Dùng để | Nhãn lời |
|---|---|---|---|
| 1 | Công trình nghiên cứu, tuyển tập có biên soạn khoa học (bản in hoặc bản quét) | Đối chiếu lời | A |
| 2 | Từ điển (tiếng Việt, Hán Việt, phương ngữ) | Nguồn cho mục từ | — |
| 3 | Giáo trình văn học dân gian, sách giáo khoa | Đối chiếu lời; nguồn cho diễn giải | A |
| 4 | Tư liệu điền dã có phiếu hoặc ghi âm, có đồng ý | Lời theo người kể | B |
| 5 | Tạp chí chuyên ngành, luận văn (kể cả PDF có số trang) | Đối chiếu lời, diễn giải | A |
| 6 | Trang web chuyên về ca dao (**Ca dao Mẹ là nguồn đầu mối chính cho MVP**, D-046) | Lời (nhãn D), tham chiếu ý nghĩa chú thích | D |
| 7 | Đóng góp cộng đồng | Dị bản | C |
| — | AI | **Không bao giờ là nguồn** | — |

## 3. Cách ghi nguồn

- Mỗi tư liệu một tệp `content/nguon/src-<slug>.yaml` (mẫu: `templates/nguon.yaml`).
- **Sách, tạp chí, luận văn, bản quét:** số trang ghi ở **chỗ dùng** (`nguon[].trang`). Hai lần in khác nhau → hai tệp nguồn, ghi rõ `tai_ban`. `da_xac_minh_ban_in: true` chỉ khi đã đối chiếu **toàn bộ** thông tin thư mục (R17).
- **Trang web:** tệp nguồn ghi trang chủ ở `url`; mỗi chỗ dùng ghi `url` của **đúng trang** đã lấy, `ngay_truy_cap`, và `ban_luu_tru` (bản lưu Wayback Machine) cho lời của bài công khai (R22). Trang gốc có mất thì vẫn chứng minh được.
- Ảnh chụp trang sách làm bằng chứng nội bộ lưu ở nơi riêng tư, không đưa vào kho công khai nếu sách còn bản quyền.

## 4. Dùng nội dung của người khác

> **Từ D-055 (24/10/2026):** người sáng lập cho phép **chép chú thích, chú giải có ghi công** ("Theo …" + liên kết về trang gốc), không chờ văn bản cho phép; thư cho Ca dao Mẹ gửi khi phù hợp. Bảng dưới là chính sách trước đó, giữ để tham khảo rủi ro.

| Thành phần | Được | Không được |
|---|---|---|
| **Lời** ca dao, tục ngữ | Lời là di sản dân gian, không thuộc sở hữu của ai: trích nguyên văn, **ghi công từng bài** và liên kết về trang đã lấy | Trình bày như thể dự án tự sưu tầm |
| **Chú thích, giải nghĩa, lời bình** | Đọc để hiểu ý rồi **viết lại bằng lời của dự án**, ghi "Tham khảo: …" (`cach_dung: tham_khao`) | Chép nguyên văn — trừ khi nguồn **cho phép bằng văn bản** (`ban_quyen: duoc_phep`) hoặc là định nghĩa rất ngắn ≤ 15 từ (`cach_dung: trich_nguyen_van`, R20) |
| **Ảnh** | Liên kết tới trang có ảnh | Tải về, đăng lại |
| **Cách tuyển chọn, sắp xếp, phân loại, thẻ** | Dùng nội bộ làm gợi ý khi duyệt | Sao chép cấu trúc danh mục hay bộ thẻ |
| **Văn bản CC BY-SA** (Wikipedia, Wiktionary) | Đọc tham khảo, dẫn link | Đưa vào dữ liệu — sẽ buộc dự án đổi giấy phép (D-037) |

Ghi chú:
- `robots.txt` cho phép thu thập nghĩa là được **truy cập**, không có nghĩa là được **đăng lại**.
- `content/` nằm trong kho mã **công khai**: bài nháp cũng là công bố. Vì vậy R20 chặn chép nguyên văn ở mọi trạng thái, và **kho thô chỉ nằm trong repo riêng tư** (D-045).
- Theo hiểu biết hiện có, Luật Sở hữu trí tuệ Việt Nam có điều khoản riêng về tác phẩm văn học nghệ thuật dân gian, yêu cầu người sử dụng dẫn chiếu xuất xứ và giữ gìn giá trị đích thực — trùng với nguyên tắc của dự án; cần kiểm tra văn bản luật hiện hành.

## 5. Ma trận giấy phép của dự án (D-037)

| Nội dung | Giấy phép | Ghi chú |
|---|---|---|
| Lời ca dao, tục ngữ | Di sản dân gian, không thuộc sở hữu của dự án | Luôn ghi nguồn gốc |
| Chú giải, diễn giải, mô tả tình huống, dữ liệu do dự án viết | **CC BY 4.0** | Giáo viên dùng lại chỉ cần ghi nguồn |
| Chú thích trích nguyên văn từ nguồn khác (`trich_nguyen_van`) | Theo giấy phép/sự cho phép của nguồn đó | **Không** thuộc CC BY của dự án; đánh dấu riêng trong bản phát hành dữ liệu |
| Mã nguồn | MIT | |
| Ghi âm cộng đồng | CC BY 4.0 | Kèm đồng ý của người thể hiện |

## 6. Hồ sơ nguồn: Ca dao Mẹ (cadao.me)

Kiểm trực tiếp ngày 23/10/2026. Tệp: `content/nguon/src-ca-dao-me.yaml`.

| Hạng mục | Thực tế |
|---|---|
| Vận hành | Dự án độc lập, phi lợi nhuận; nhóm 11 thành viên (+4 cũ); cảm ơn học giả An Chi hỗ trợ học thuật. Bài sớm nhất đăng 01/2013 |
| Quy mô (ước tính) | ~16.500 mục (ca dao ~11.800; thành ngữ–tục ngữ ~3.500; đồng dao, vè, câu đố); ~9.000 chú thích |
| Nguồn từng bài | **Không có** (chỉ ghi người đăng, ngày đăng) → lời mang nhãn D |
| Dị bản | Có ở một số bài, không ghi vùng hay nguồn |
| Chú thích | Không dẫn nguồn; site tự ghi "không dám đảm bảo tất cả những chú thích, chú giải ở đây là chính xác" |
| Phân loại | Gộp thành ngữ với tục ngữ; có bài trào phúng hiện đại và nội dung thô tục xếp chung với ca dao truyền thống |
| Bản quyền | "© 2026 Ca dao Mẹ", không có giấy phép tái sử dụng → `ban_quyen: chua_ro` |
| Kỹ thuật | WordPress; API công khai cho ca dao; các loại khác đọc theo sitemap; `robots.txt` không chặn |
| Liên hệ | Form tại `cadao.me/dong-gop` |

**Cách dùng (D-046):** lời lấy triệt để vào kho thô, xuất bản chọn lọc, ghi "Lời tham khảo từ Ca dao Mẹ" kèm link; chú thích tham chiếu ý nghĩa để viết lại; không lấy ảnh; phân loại chỉ dùng nội bộ. Trang bài có liên kết "Xem thêm chú thích tại Ca dao Mẹ" — dự án là hàng xóm, không phải đối thủ.

## 7. Thư gửi Ca dao Mẹ (T-034, bản nháp)

Gửi qua form `cadao.me/dong-gop`, trước khi mở công khai.

> Kính gửi Ban quản trị Ca dao Mẹ,
>
> Tôi là [tên], đang xây dựng [tên dự án], một trang ca dao tục ngữ phi lợi nhuận, không quảng cáo, tập trung vào trải nghiệm đọc trên điện thoại cho giáo viên và người trẻ.
>
> Ca dao Mẹ là kho tư liệu chúng tôi trân trọng nhất. Dự án tham khảo lời các bài từ Ca dao Mẹ, ghi công và đặt liên kết về trang gốc ở từng bài.
>
> Chúng tôi trân trọng xin phép được sử dụng các chú thích của Ca dao Mẹ, có ghi rõ nguồn và liên kết ở từng chú thích. Nếu Ban quản trị đồng ý, chúng tôi rất mong được trao đổi thêm về khả năng hợp tác lâu dài.
>
> Trân trọng,
> [tên] · [email liên hệ của dự án]

Có văn bản đồng ý: đổi `ban_quyen` của `src-ca-dao-me` sang `duoc_phep`, ghi phạm vi đồng ý vào `ghi_chu` và D-xxx (Human Gate H3). Bị từ chối: gỡ phần đã dùng theo yêu cầu, chuyển sang nguồn khác.

## 8. Tư liệu để nâng nhãn (CHƯA XÁC MINH)

Không cần sách giấy cho MVP; các nguồn dưới đây giúp nâng bài từ D lên A dần dần. **Phải xác minh** thông tin thư mục trên bản in/bản quét thật trước khi đặt `da_xac_minh_ban_in: true`.

| Tư liệu | Dùng cho | Ghi chú cần kiểm |
|---|---|---|
| Luận văn, tạp chí khoa học về ca dao, tục ngữ (PDF truy cập mở ở thư viện số các trường) | Đối chiếu lời (A), diễn giải | Có số trang; ghi `loai: luan_van`/`tap_chi` |
| Nguyễn Văn Ngọc, *Tục ngữ phong dao* | Lời, tư liệu cũ | Tác giả mất 1942 — nhiều khả năng thuộc phạm vi công cộng; **chưa tìm thấy bản quét trực tuyến**, cần tìm ở archive.org, Gallica, thư viện số |
| Huỳnh Tịnh Của, *Đại Nam quấc âm tự vị* | Từ cổ (mục từ) | Cuối thế kỷ 19; khả năng cao thuộc phạm vi công cộng |
| Thiều Chửu, *Hán Việt tự điển* | Hán Việt, điển tích | Tác giả mất 1954; cần xác minh |
| Nguyễn Xuân Kính, Phan Đăng Nhật (chủ biên), *Kho tàng ca dao người Việt* | Lời ca dao (A) | Còn bản quyền: chỉ trích lời + số trang. Tệp: `src-kho-tang-ca-dao-nguoi-viet` |
| Vũ Ngọc Phan, *Tục ngữ ca dao dân ca Việt Nam* | Lời; diễn giải | Tái bản nhiều lần; các tài liệu thiết kế ghi năm khác nhau — ghi theo lần in cầm trên tay. Tệp: `src-vu-ngoc-phan-tuc-ngu-ca-dao-dan-ca` |
| Sách giáo khoa Ngữ văn, 3 bộ hiện hành | Đối chiếu lời; trường `sgk` | Học liệu điện tử của NXB Giáo dục có thay đổi từ 2025 — kiểm nền tảng hiện hành |
| "Gửi số trang" của người dùng (T-045) | Đối chiếu lời (A) | Người biên soạn kiểm ảnh trang rồi mới thêm nguồn |

## 9. Khi phát hiện nguồn sai

1. Sửa hoặc gỡ tham chiếu nguồn sai; nhãn tự tính lại (có thể từ A về D). Ghi lý do trong commit.
2. Nếu bản tham chiếu không còn nguồn gốc nào: chuyển tác phẩm về `an` cho tới khi sửa xong (R21 chặn công khai).
3. Báo lỗi đến từ người dùng được ưu tiên xử lý trước mọi đóng góp khác.
