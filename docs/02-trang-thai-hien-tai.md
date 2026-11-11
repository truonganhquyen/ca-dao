# 02 · Trạng thái hiện tại

| | |
|---|---|
| Phiên bản | 3.0 · 11/11/2026 (viết lại gọn sau D-107; trạng thái chi tiết đến 11/11/2026: `docs/luu-tru/02-trang-thai-den-11-11-2026.md`) |
| Mục đích | Trang tổng quan ngắn cho người sáng lập và AI agent |
| Nguồn máy đọc | `du-an/trang-thai.json` (`npm run kiem-nhat-quan` đối chiếu với `docs/01`, `docs/22`) |

> Tệp này mô tả **thực tế hiện tại**, không phải kế hoạch. Sau mỗi task đã gộp, agent cập nhật tệp này cùng `du-an/trang-thai.json` và trạng thái trong `docs/22-backlog-trien-khai.md`. Lịch sử từng việc đã làm nằm ở `docs/22` mục 3 và lịch sử Git; không chép lại ở đây.

## Đang chạy

- **phongdao.com chạy V2** (Thư viện Văn học Dân gian Việt Nam; plugin 1.0.43, theme 1.0.2) từ 04/11/2026: WordPress trên VPS, máy chủ tự cập nhật sau mỗi PR gộp vào `main` (`docs/26` mục 15). **Site còn chặn Google** (công tắc toàn site, Human Gate H5 — người sáng lập mở khi quyết).
- **Trang người đọc** (`docs/27`): trang chủ, danh sách theo thể loại, chủ đề, vùng; trang bài cho năm thể loại (lời mỗi câu một dòng, nhãn mức kiểm, Ý nghĩa, từ khó chạm để xem, dị bản, "Lời này từ đâu?", trích dẫn, chia sẻ, ảnh để chia sẻ, trình chiếu, cỡ chữ, Đọc cùng bé); Tìm (gõ không dấu, gợi ý gõ sai); Từ điển, Bản đồ, Tuyển tập, Khám phá, Một câu ngẫu nhiên; Đã lưu; Góp (dị bản, chú thích, báo lỗi, số trang, cảm nghĩ, bài cảm nhận).
- **Khu biên tập trong wp-admin** (`docs/28` mục 4), sau đợt 1–2 (10–11/11/2026) và bước đầu đợt 3:
  - **Menu theo ba dòng việc:** Hôm nay · Bài mới · Hộp việc · Bổ sung · Tác phẩm · Nguồn & cài đặt.
  - **Hôm nay** (trước là Bàn làm việc): 3 việc gấp nhất, ba ô Bài mới · Hộp việc · Bổ sung có nút bắt đầu, Nên làm tuần này, số liệu tuần thu gọn (nhớ 1 giờ); đăng nhập vào thẳng đây. Trang chào mừng WordPress có khung "Phongdao hôm nay".
  - **Bổ sung** (bản đầu): bài đọc nhiều chưa có Ý nghĩa, danh sách ưu tiên có nút Bắt đầu.
  - **Hộp việc** kiểu hộp thư: danh sách và thẻ cạnh nhau, quyết xong tự sang thẻ kế, Hoàn tác 10 giây, phím tắt, làm nhanh nhiều thẻ.
  - **Tác phẩm:** lọc, chọn hàng loạt, Làm theo lô.
  - **Sửa tác phẩm:** một màn hai cột, "Bài tiếp theo".
  - **Bài mới (Kho):** Xem trước và đăng một mạch; sau khi Đăng có "Xem bài · Ẩn nếu nhầm".
  - **Thao tác hàng loạt:** có xem trước, kết quả từng mục.
  - **Nguồn & cài đặt** (Quản trị).
  - Sửa nhanh trên trang đọc vẫn dùng. Các trang `/bien-tap/` còn chạy tới khi đợt 3 chuyển về wp-admin.
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
- Task: T-189 (khu quản trị), T-190 (trang đọc), T-191 (Google), T-192 (gỡ mã cũ). Thứ tự làm: `docs/32` mục 8. Đang làm bước 1 (sửa nhanh, menu mới, Hôm nay — T-189.1).
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
