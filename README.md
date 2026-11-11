# Thư viện Văn học Dân gian Việt Nam

**Đọc một câu. Hiểu một đời sống.**
**Mỗi câu ghi rõ lấy từ đâu, và đã được kiểm đến mức nào.**

Website chạy trên **WordPress** (phongdao.com). Kho mã này chứa mã nguồn của site (plugin và theme), công cụ kiểm thử, công cụ cho AI agent và tài liệu điều hành.

## Đọc gì trước

| Bạn là | Bắt đầu từ |
|---|---|
| **Người sáng lập** | `docs/02-trang-thai-hien-tai.md` (đang ở đâu, việc của bạn) |
| **Dev, AI agent** | `AGENTS.md` (luật chơi), `docs/00-tai-lieu-nen.md`, `docs/22-backlog-trien-khai.md`; V2: `docs/26-kien-truc-v2.md` (kỹ thuật, máy chủ), `docs/27-san-pham-v2.md` (trang người đọc), `docs/28-ban-bien-tap-v2.md` (nguồn, biên tập), `docs/32-ke-hoach-dot-3.md` (đợt 3); tài liệu cũ ở `docs/luu-tru/` |
| **Người biên soạn** | `docs/huong-dan/` |

## Cấu trúc

```
wordpress/
  v2/plugins/ca-dao/   plugin V2 (site đang chạy): dữ liệu, nguồn, chống trùng, tìm, biên tập, khu quản trị
  v2/themes/ca-dao/    theme khối V2: giao diện, mẫu trang
  v2/tests/            kiểm thử V2 (PHP thuần + WordPress Playground)
  may-chu/             kịch bản máy chủ (docs/26 mục 15)
  plugins/, themes/, tests/   mã MVP cũ — giữ cho kiểm thử chuyển đổi, gỡ ở T-192
design/v2/             bộ thiết kế UX/UI V2
cloudflare/chuyen-huong.js   Worker chuyển hướng địa chỉ cũ sang phongdao.com (301)
scripts/               công cụ kiểm thử, quét trợ năng, bản xem trước, backlog
docs/                  tài liệu: nhật ký quyết định, trạng thái, backlog, đặc tả
du-an/trang-thai.json  trạng thái dự án cho máy đọc
```

## Lệnh

```bash
npm ci                        # cài công cụ kiểm thử
npm run kiem-tra              # kiểm nhất quán backlog và trạng thái
npm run test:v2               # kiểm thử V2 (PHP thuần + Playground; Node ≥ 24.18)
npm run quet:v2               # quét trợ năng WCAG 2.2 AA, ngân sách trang
npm run e2e:v2                # kiểm thử đầu-cuối trong Chrome
npm run xem-truoc:v2          # link bản xem trước cho nhánh đã đẩy
npm run kiem-site-that        # site thật có đúng bản của main không (chỉ đọc)
npm run viec-tiep-theo        # task kỹ thuật READY tiếp theo
```

## Triển khai

- Gộp PR vào `main` là site tự cập nhật sau vài phút: máy chủ lấy plugin và theme V2 từ `main` (xem `docs/26-kien-truc-v2.md` mục 15).
- Worker `cloudflare/chuyen-huong.js` được Cloudflare triển khai từ `main`.

## Lằn ranh

Lời ca dao không do máy viết. Nguồn không bịa. Kho thô không vào repo này. Không thông tin cá nhân trong kho mã. Chi tiết: `AGENTS.md`.

## Giấy phép

Phần do dự án tự viết dùng CC BY 4.0, như ghi ở trang "Về dự án" của site. Phần chép từ Wikipedia, Wiktionary giữ CC BY-SA và được ghi tại chỗ. Ghi công từng nguồn: `docs/12-chinh-sach-nguon.md`.
