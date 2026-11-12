# 02 · Trạng thái hiện tại

| | |
|---|---|
| Phiên bản | 3.0 · 11/11/2026 (viết lại gọn sau D-107; trạng thái chi tiết đến 11/11/2026: `docs/luu-tru/02-trang-thai-den-11-11-2026.md`) |
| Mục đích | Trang tổng quan ngắn cho người sáng lập và AI agent |
| Nguồn máy đọc | `du-an/trang-thai.json` (`npm run kiem-nhat-quan` đối chiếu với `docs/01`, `docs/22`) |

> Tệp này mô tả **thực tế hiện tại**, không phải kế hoạch. Sau mỗi task đã gộp, agent cập nhật tệp này cùng `du-an/trang-thai.json` và trạng thái trong `docs/22-backlog-trien-khai.md`. Lịch sử từng việc đã làm nằm ở `docs/22` mục 3 và lịch sử Git; không chép lại ở đây.

## Đang chạy

- **phongdao.com chạy V2** (Thư viện Văn học Dân gian Việt Nam; plugin 1.0.49, theme 1.0.3) từ 04/11/2026: WordPress trên VPS, máy chủ tự cập nhật sau mỗi PR gộp vào `main` (`docs/26` mục 15). **Site còn chặn Google** (công tắc toàn site, Human Gate H5 — người sáng lập mở khi quyết).
- **Trang người đọc** (`docs/27`): trang chủ, danh sách theo thể loại, chủ đề, vùng; trang bài cho năm thể loại (lời mỗi câu một dòng, nhãn mức kiểm, Ý nghĩa, từ khó chạm để xem, dị bản, "Lời này từ đâu?", trích dẫn, chia sẻ, ảnh để chia sẻ, trình chiếu, cỡ chữ, Đọc cùng bé); Tìm (gõ không dấu, gợi ý gõ sai); Từ điển, Bản đồ, Tuyển tập, Khám phá, Một câu ngẫu nhiên; Đã lưu; Góp (dị bản, chú thích, báo lỗi, số trang, cảm nghĩ, bài cảm nhận).
- **Khu biên tập trong wp-admin** (`docs/28` mục 4), sau đợt 1–2 (10–11/11/2026) và bước đầu đợt 3:
  - **Menu theo ba dòng việc:** Hôm nay · Bài mới · Hộp việc · Bổ sung · Tác phẩm · Nguồn & cài đặt.
  - **Hôm nay** (trước là Bàn làm việc): 3 việc gấp nhất, ba ô Bài mới · Hộp việc · Bổ sung có nút bắt đầu, Nên làm tuần này, số liệu tuần thu gọn (nhớ 1 giờ); đăng nhập vào thẳng đây. Trang chào mừng WordPress có khung "Phongdao hôm nay".
  - **Bổ sung:** bài đọc nhiều chưa có Ý nghĩa, danh sách ưu tiên có nút Bắt đầu; **gợi ý chủ đề, vùng của máy** duyệt theo nhóm (một chủ đề / vùng: bỏ dấu ngoại lệ, Đồng ý) hoặc theo bài — không còn là thẻ trong Hộp việc.
  - **Hộp việc** kiểu hộp thư, chỉ việc cần người quyết (có thể trùng, nháp chờ duyệt, người đọc gửi, cần xem): danh sách và thẻ cạnh nhau, **quyết không tải lại trang**, tự sang thẻ kế, Hoàn tác 10 giây, phím tắt (nút ghi số phím), **sửa vài chữ trong nháp rồi Duyệt ngay trên thẻ**; điện thoại: mở thẻ chỉ còn "← Danh sách · Thẻ N / M", nút quyết dính đáy màn.
  - **Thanh dưới trên điện thoại** ở mọi màn Phongdao: Hôm nay · Hộp việc · Bổ sung · Bài mới.
  - **Tác phẩm:** tab trạng thái kèm số, nút lọc nhanh (Thiếu ý nghĩa, Có cờ), "Thêm bộ lọc" (còn thiếu theo thể loại, xếp bài đọc nhiều trước), mỗi hàng Sửa · Xem · Ẩn; điện thoại dạng thẻ ba dòng; chọn hàng loạt, Làm theo lô. Đọc từ **bảng tóm tắt bài** (mỗi bài một dòng tính sẵn) nên mở nhanh cả khi kho lớn.
  - **Sửa tác phẩm:** một màn hai cột, "Bài tiếp theo"; phần chưa có gom thành hàng "Thêm: …"; cờ "Có thể không phải dân gian" là công tắc ở cột bên (bật cần lý do và nguồn); **Đổi thể loại** (người có quyền duyệt; đường cũ tự chuyển 301, có Lịch sử); điện thoại: trạng thái và Đăng / Ẩn ngay dưới tên bài.
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

- Người sáng lập đã chốt (11/11/2026):
  - ba dòng việc **Bài mới · Hộp việc · Bổ sung** (D-103);
  - khu quản trị (D-104);
  - trang đọc và nội dung (D-105);
  - mọi tác phẩm đã đăng được lập chỉ mục khi site mở cho Google (D-106).
- Task: T-189 (khu quản trị), T-190 (trang đọc), T-191 (Google), T-192 (gỡ mã cũ). Thứ tự làm: `docs/32` mục 8. Bước 1 (sửa nhanh, menu mới, Hôm nay — T-189.1) bước 2 (bảng tóm tắt bài, Tác phẩm gọn, bản điện thoại — T-189.2) bước 3 (Bài mới — T-189.3; Bổ sung duyệt gợi ý, Hộp việc chỉ việc cần quyết — T-189.4) bước 4 (Hộp việc không tải lại trang, điện thoại, trang sửa gọn, cờ công tắc, đổi thể loại — T-189.5) bước 5 (Nguồn & cài đặt, Đối chiếu sách — T-189.6) và bước 6 (gộp `/bien-tap/` về wp-admin — T-189.7) đã làm. Bước Google (T-191) **tạm dừng theo người sáng lập (12/11/2026)**; làm trước trang bài ba tầng và "Cách hiểu của bạn đọc" (T-190).
- Báo cáo căn cứ: `docs/e2e-review/v2-quan-tri-2/BAO-CAO.md`.

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

## Lệnh điều hành

```bash
npm run viec-tiep-theo
npm run kiem-tra
npm run kiem-site-that      # site thật có đúng bản của main không (chỉ đọc)
```
