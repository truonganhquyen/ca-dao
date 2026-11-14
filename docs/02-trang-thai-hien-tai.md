# 02 · Trạng thái hiện tại

| | |
|---|---|
| Phiên bản | 3.5 · 14/11/2026 (tìm đúng chữ và thứ tự danh sách; lịch sử đến 11/11/2026: `docs/luu-tru/02-trang-thai-den-11-11-2026.md`) |
| Mục đích | Trang tổng quan ngắn cho người sáng lập và AI agent |
| Nguồn máy đọc | `du-an/trang-thai.json` (`npm run kiem-nhat-quan` đối chiếu với `docs/01`, `docs/22`) |

> Tệp này mô tả **thực tế hiện tại**, không phải kế hoạch. Sau mỗi task đã gộp, agent cập nhật tệp này cùng `du-an/trang-thai.json` và trạng thái trong `docs/22-backlog-trien-khai.md`. Lịch sử từng việc đã làm nằm ở `docs/22` mục 3 và lịch sử Git; không chép lại ở đây.

## Đang chạy

- **phongdao.com chạy V2** (Thư viện Văn học Dân gian Việt Nam) từ 04/11/2026: WordPress trên VPS, máy chủ tự cập nhật sau mỗi PR gộp vào `main` (`docs/26` mục 15). **Site còn chặn Google** (công tắc toàn site, Human Gate H5 — người sáng lập mở khi quyết).
- **Trang người đọc** (`docs/27`): trang chủ, danh sách theo thể loại, chủ đề, vùng; trang bài cho năm thể loại (lời mỗi câu một dòng, nhãn mức kiểm, Ý nghĩa, từ khó chạm để xem, dị bản, "Lời này từ đâu?", trích dẫn, chia sẻ, ảnh để chia sẻ, trình chiếu, cỡ chữ, Đọc cùng bé); Tìm (gõ không dấu, gợi ý gõ sai); Từ điển, Bản đồ, Tuyển tập, Khám phá, Một câu ngẫu nhiên; Đã lưu; Góp (dị bản, chú thích, báo lỗi, số trang, cảm nghĩ, bài cảm nhận).
- **Form Góp gọn theo loại** (D-108, plugin 1.0.58/theme 1.0.7): chỉ hiện ô phù hợp, thông tin phụ thu gọn; từ bài giữ loại/bài/đối tượng báo vi phạm; đổi loại không mất chữ. Gửi lỗi dựng lại đúng chữ trong phản hồi riêng tư, kể cả khi tắt JavaScript; gửi số trang không ép viết ghi chú. Consent, chống rác và hàng chờ duyệt giữ nguyên. [Chi tiết và kiểm thử](35-form-gop-gon-va-giu-chu.md).
- **Khu biên tập trong wp-admin** (`docs/28` mục 4), sau đợt 1–2 (10–11/11/2026) và bước đầu đợt 3:
  - **Menu theo ba dòng việc:** Hôm nay · Bài mới · Hộp việc · Bổ sung · Tác phẩm · Danh mục · Nguồn & cài đặt.
  - **Hôm nay** (trước là Bàn làm việc): 3 việc gấp nhất, ba ô Bài mới · Hộp việc · Bổ sung có nút bắt đầu, Nên làm tuần này, số liệu tuần thu gọn (nhớ 1 giờ); đăng nhập vào thẳng đây. Trang chào mừng WordPress có khung "Phongdao hôm nay".
  - **Bổ sung:** bài đọc nhiều chưa có Ý nghĩa, danh sách ưu tiên có nút Bắt đầu; **gợi ý chủ đề, vùng của máy** duyệt theo nhóm (một chủ đề / vùng: bỏ dấu ngoại lệ, Đồng ý) hoặc theo bài — không còn là thẻ trong Hộp việc.
  - **Hộp việc** kiểu hộp thư, chỉ việc cần người quyết (có thể trùng, nháp chờ duyệt, người đọc gửi, cần xem): danh sách và thẻ cạnh nhau, **quyết không tải lại trang**, tự sang thẻ kế, Hoàn tác 10 giây, phím tắt (nút ghi số phím), **sửa vài chữ trong nháp rồi Duyệt ngay trên thẻ**; điện thoại: mở thẻ chỉ còn "← Danh sách · Thẻ N / M", nút quyết dính đáy màn.
  - **Thanh dưới trên điện thoại** ở mọi màn Phongdao: Hôm nay · Hộp việc · Bổ sung · Bài mới.
  - **Tác phẩm:** tab trạng thái kèm số, nút lọc nhanh (Thiếu ý nghĩa, Có cờ), "Thêm bộ lọc" (còn thiếu theo thể loại, xếp bài đọc nhiều trước), mỗi hàng Sửa · Xem · Ẩn; điện thoại dạng thẻ ba dòng; chọn hàng loạt, Làm theo lô. Đọc từ **bảng tóm tắt bài** (mỗi bài một dòng tính sẵn) nên mở nhanh cả khi kho lớn.
  - **Sửa tác phẩm** (D-109, plugin 1.0.59): mục lục toàn bài, trạng thái từng phần chưa lưu và thanh lưu theo phần; phần khôi phục mở trên cả điện thoại. Từ khó/dị bản gọn theo mục, có lọc và Thêm ở đầu; thứ tự phần theo thể loại. Phân loại không cuộn lồng; người có quyền chọn/bỏ thể thơ hoặc quay lại máy, sửa lời vẫn giữ lựa chọn người; có lịch sử/kiểm phiên. Thêm mục cạnh bộ chọn không tự gắn vào bài; menu Danh mục mở năm bộ thuật ngữ hiện có. Quản lý trạng thái bài là nhóm riêng. Ảnh báo giới hạn tệp sớm và phân biệt chọn với lưu. "Bài tiếp theo" chỉ tự sang khi không còn phần khác chưa lưu. Cờ có công tắc, Đổi thể loại vẫn có Lịch sử và chuyển hướng cũ. [Phạm vi, kiểm thử](36-toi-uu-trang-sua-va-danh-muc.md), [hướng dẫn thao tác](huong-dan/sua-day-du-va-phan-loai.md).
  - **Bài mới (Kho):** lọc nhanh **Máy gợi ý hôm nay** (máy thôi tạo thẻ Câu mới hằng ngày), **Để dành**, **Đề nghị đăng** (thay "Đưa vào Hộp việc"); lọc theo mong có, có chú thích, không cờ, câu ngắn / dài; Xem trước và đăng một mạch, màn kết quả có "Xem bài · Ẩn nếu nhầm"; **nhập câu từ tệp** (vào Kho qua chống trùng, không đăng thẳng); **Thêm tác phẩm** ngay trong wp-admin; điện thoại dạng thẻ.
  - **Thao tác hàng loạt:** có xem trước, kết quả từng mục.
  - **Nguồn & cài đặt** (Quản trị): nguồn tự động, **thêm hồ sơ nguồn bằng tay**, **đề nghị nguồn tự động mới** (thành việc cho người làm kỹ thuật), máy đang làm, **Cài đặt chung** (chống rác, giờ máy chạy, trạng thái chặn Google chỉ đọc).
  - **Đối chiếu sách:** nút riêng ở trang Sửa tác phẩm — chọn sách hoặc "+ Sách mới" ngay tại chỗ (vai Biên tập làm được), trang, bản quét; "Sửa lời theo sách" hoặc "Ghi bản trong sách làm dị bản". Chỉ người điền.
  - Sửa nhanh trên trang đọc vẫn dùng (nút Sửa, tấm sửa, dải "Đang sửa" có thêm lối **"Mở trang sửa đầy đủ"**). Các trang `/bien-tap/…` đã gộp về wp-admin: đường dẫn cũ chuyển hướng 301 sang Hộp việc, Tác phẩm, Thêm tác phẩm, Hôm nay; lối gốc WordPress của tác phẩm (danh sách, sửa, thêm mới) mở màn Phongdao, menu WordPress không ẩn.
- **Lằn ranh trong mã, có kiểm thử khẳng định** (`docs/28` mục 6):
  - máy không viết lời hay nguồn, không tự đăng, không đặt dấu duyệt;
  - kho thô không ra trang người đọc;
  - không thu dữ liệu cá nhân của người đọc.

## Đang làm: đợt 3 (`docs/32`)

- **Công tắc "Có từ ngữ thô tục" đã làm** (D-111, plugin 1.0.61/theme 1.0.9): người biên soạn bật tay ở cột bên trang Sửa tác phẩm (có kiểm phiên, Lịch sử). Bài đang bật: trang đọc, thẻ bài, kết quả Tìm, tuyển tập che tên và lời sau lời báo "Bài có từ ngữ thô tục - cân nhắc khi xem." với nút "Hiển thị lời" (tắt JavaScript vẫn mở được); không vào trang chủ, Câu hôm nay, Một câu ngẫu nhiên, Đọc tiếp; ảnh và mô tả khi chia sẻ link không in lời. Lời không bị sửa. Chưa có bài nào được bật: người biên soạn tự chọn bài.
- **Editor đơn giản và sửa URL tác phẩm đã làm** (D-110, plugin 1.0.60/theme 1.0.8): thanh đậm/nghiêng/gạch chân, liên kết, màu và chữ nhẹ cho các ô diễn giải/lời giải ngay trong màn sửa hiện tại; chữ và định dạng cùng qua lưu, nháp, kiểm phiên và lịch sử. Quản trị sửa tên cuối URL từ mục lục Đường dẫn, có xác nhận, kiểm trùng/phiên và chuyển hướng cũ. Bốn cổng CI đạt, E2E 66/66; site đã đúng phiên bản, kiểm wp-admin thật chỉ đọc trên 3 tác phẩm × 390/1440 px đạt, quét 0 lỗi, không phát sinh phần chưa lưu hoặc đổi URL. [Phạm vi, hướng dẫn và bằng chứng](37-editor-don-gian-va-url.md). Đề xuất mở rộng nền nội dung tạm hoãn; SEO plugin, loại nội dung và quan hệ mới chưa triển khai. Pilot nội dung thật vẫn chờ người biên soạn.

- Người sáng lập đã chốt (11/11/2026):
  - ba dòng việc **Bài mới · Hộp việc · Bổ sung** (D-103);
  - khu quản trị (D-104);
  - trang đọc và nội dung (D-105);
  - mọi tác phẩm đã đăng được lập chỉ mục khi site mở cho Google (D-106).
- Task: T-189 (khu quản trị), T-190 (trang đọc), T-191 (Google), T-192 (gỡ mã cũ). Thứ tự làm: `docs/32` mục 8. Bước 1 (sửa nhanh, menu mới, Hôm nay — T-189.1) bước 2 (bảng tóm tắt bài, Tác phẩm gọn, bản điện thoại — T-189.2) bước 3 (Bài mới — T-189.3; Bổ sung duyệt gợi ý, Hộp việc chỉ việc cần quyết — T-189.4) bước 4 (Hộp việc không tải lại trang, điện thoại, trang sửa gọn, cờ công tắc, đổi thể loại — T-189.5) bước 5 (Nguồn & cài đặt, Đối chiếu sách — T-189.6) và bước 6 (gộp `/bien-tap/` về wp-admin — T-189.7) đã làm. Bước Google (T-191) **tạm dừng theo người sáng lập (12/11/2026)**; làm trước trang bài ba tầng và "Cách hiểu của bạn đọc" (T-190). Trang bài ba tầng (Đọc · Hiểu thêm · Đọc thêm) và Cách hiểu của bạn đọc (form nhanh trên trang, duyệt ở Hộp việc, hiện ở Hiểu thêm) đã làm — T-190.1, plugin 1.0.50, theme 1.0.4. Tục ngữ (khối Nghĩa, câu dài hiện như ca dao, từ khó trong câu tiêu đề), câu đố "Giải thích đáp án" trong "Hiện đáp án", cờ "Có thể không phải dân gian" kèm "Vì sao?", từ khó so khớp không phân biệt hoa thường / kiểu bỏ dấu, lần đầu trong bài, nhắc khi không thấy trong lời; ca dao "Hình ảnh trong bài"; Sửa phân loại, Đổi thể loại, Đối chiếu sách ngay trên trang đọc (chế độ sửa); lời mời "Ở quê bạn có câu này không? Góp" — T-190.2, plugin 1.0.52, theme 1.0.5. Phần trang đọc của đợt 3 đã xong. Gỡ mã bản cũ (T-192): theme, kiểm thử, kịch bản, job CI của MVP và bộ khảo sát Playwright đã gỡ; plugin MVP chỉ còn ở `tests/chuyen-doi-mvp/` cho kiểm thử chuyển đổi.
- Báo cáo căn cứ: `docs/e2e-review/v2-quan-tri-2/BAO-CAO.md`.

**Tìm đúng chữ và thứ tự danh sách đã làm** (D-112, plugin 1.0.62): tìm đã gửi so trọn chữ và đúng dấu, tách gợi ý gõ dở/khác dấu; giữ thứ tự liên quan, lọc thể loại và phân trang toàn tập; đánh dấu/trích đúng phần khớp. Một từ không có thẻ nổi bật. Kho và trang phân loại mặc định **Có giải nghĩa trước**, thêm Mới thêm / Đọc nhiều tuần này / A–Z. Bốn cổng đạt trên nhánh, E2E 67/67; chỉ gộp khi CI head cuối đạt, kiểm phiên bản site sau gộp. [Phạm vi và nghiệm thu](39-tim-va-sap-xep-danh-sach.md), [PR #176](https://github.com/truonganhquyen/ca-dao/pull/176). Pilot nội dung thật vẫn chờ người biên soạn.

## Đang chờ người sáng lập

- **Thử từng phần đợt 3** qua link bản xem trước trong mỗi PR (bấm thử trên điện thoại và máy tính).
- **Mở cho Google** (H5): khi bạn quyết. Sau D-106, mọi bài đã đăng lên Google, không chờ đủ Ý nghĩa.
- **Gửi thư cho Ca dao Mẹ** (`docs/12` mục 7): nên gửi trước khi mở cho Google.
- **Trang Quyền riêng tư:** nội dung do người sáng lập viết (`docs/27` mục 8.3). Liên kết ở chân trang tự ẩn tới khi trang được đăng.
- **Hai cặp tác phẩm chung một đường dẫn** (#1764, #1769): chọn bài giữ đường dẫn. Trang Tác phẩm có nút "Cho bài này đường dẫn riêng".
- **Chọn 20–30 bài làm sâu** (`docs/huong-dan/thu-lam-sau.md`): để biết một bài làm kỹ tốn bao lâu.
- **Khoá Turnstile** (tuỳ chọn, chặn tin rác chắc hơn): Cloudflare → Turnstile, dán khoá ở Nguồn & cài đặt → Chống rác. Không gửi khoá cho ai, kể cả agent.

## Công việc kỹ thuật tiếp theo

Chạy `npm run viec-tiep-theo` — máy suy ra từ backlog (`docs/22`), luôn đúng. Mục này **không liệt kê task bằng tay** để các PR không sửa chung một dòng (xem `docs/03` mục 7).

## Quyết định đang mở

Q-008 (tài trợ). Không câu nào chặn task kỹ thuật hiện tại.

## Tài liệu

- Đang dùng: `docs/00` (nền), `01` (quyết định), `02` (tệp này), `03` (quy trình), `12` (nguồn), `22` (backlog), `26` (kỹ thuật V2), `27` (sản phẩm), `28` (luồng nội dung, biên tập), `31` (cải tiến sau review, phần còn mở), `32` (đợt 3), `docs/huong-dan/` (hướng dẫn người sáng lập).
- Lưu trữ (không còn dùng hằng ngày, giữ nguyên văn): `docs/luu-tru/` — danh mục ở `docs/luu-tru/README.md`.
- Kế hoạch bổ sung theo phạm vi người sáng lập chốt ngày 12/11: [giữ chữ, chống ghi đè, ảnh minh hoạ và thử nội dung](33-ke-hoach-cai-tien-bien-tap-va-anh.md); bắt đầu triển khai 13/11. **Giữ chữ đã làm (plugin 1.0.53):** lỗi nhập trả lại đúng hàng/nội dung, phần chưa lưu được giữ theo tab/phiên tối đa 30 phút; rời trang có cảnh báo. Chống ghi đè đã làm (plugin 1.0.54): lưu phần so phiên trong giao dịch có khoá, giữ chữ và cho đối chiếu khi có bản mới. Ảnh minh hoạ đã bổ sung (plugin 1.0.55): Thư viện → xem trước → lưu → khách xem; thay/bỏ/khôi phục, ghi công và ảnh câu đố trong đáp án. Xem [hướng dẫn](huong-dan/bien-tap-an-toan-va-anh.md). Nhãn/form đã làm (plugin 1.0.56/theme 1.0.6); bản chốt 1.0.57 khoá khôi phục/duyệt đã qua bốn cổng và kiểm hai tiến trình MySQL. [Bàn giao và QA mẫu](34-ban-giao-bien-tap-va-anh.md), [phiếu pilot](huong-dan/pilot-10-bai.md). Phiên bản site kiểm trực tiếp bằng npm run kiem-site-that; pilot nội dung thật còn chờ người quyết. Bốn mục loại khỏi đợt giữ nguyên.

## Lệnh điều hành

```bash
npm run viec-tiep-theo
npm run kiem-tra
npm run kiem-site-that      # site thật có đúng bản của main không (chỉ đọc)
```
