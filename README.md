# Thư viện Văn học Dân gian Việt Nam

**Đọc một câu. Hiểu một đời sống.**
**Mỗi câu ghi rõ lấy từ đâu, và đã được kiểm đến mức nào.**

Website chạy trên **WordPress** (phongdao.com). Kho mã này chứa mã nguồn của site (plugin và theme), công cụ kiểm thử, công cụ cho AI agent và tài liệu điều hành.

## Đọc gì trước

| Bạn là | Bắt đầu từ |
|---|---|
| **Người sáng lập** | `docs/02-trang-thai-hien-tai.md` (đang ở đâu, việc của bạn) |
| **Dev, AI agent** | `AGENTS.md` (luật chơi), `docs/00-tai-lieu-nen.md`, `docs/24-wordpress.md`, `docs/22-backlog-trien-khai.md`; đại tu V2: `docs/26-kien-truc-v2.md`, `docs/27-giao-dien-v2.md`, `docs/28-dac-ta-dot-v2-0.md` |
| **Người biên soạn** | `docs/huong-dan/` |

## Cấu trúc

```
wordpress/
  plugins/ca-dao/      plugin: loại bài, thu thập, chống trùng, tìm, đo lường, trang quản trị
  themes/ca-dao/       theme con của Twenty Twenty-Five: giao diện, mẫu trang
  tests/               kiểm thử plugin (chạy trong WordPress Playground)
cloudflare/chuyen-huong.js   Worker chuyển hướng địa chỉ cũ sang phongdao.com (301)
scripts/               công cụ kiểm thử, quét trợ năng, bản xem trước, backlog
docs/                  tài liệu: nhật ký quyết định, trạng thái, backlog, đặc tả
du-an/trang-thai.json  trạng thái dự án cho máy đọc
```

## Lệnh

```bash
npm ci                        # cài công cụ kiểm thử
npm run kiem-tra              # kiểm nhất quán backlog và trạng thái
npm run test:wordpress        # kiểm thử plugin trong WordPress Playground (Node ≥ 24.18)
npm run quet:wordpress        # quét trợ năng WCAG 2.2 AA và thử trên trình duyệt
npm run xem-truoc:wordpress   # link bản xem trước cho nhánh đã đẩy
npm run viec-tiep-theo        # task kỹ thuật READY tiếp theo
```

## Triển khai

- Gộp PR vào `main` là site tự cập nhật sau vài phút: máy chủ lấy plugin và theme từ `main` (xem `docs/24-wordpress.md` mục 8).
- Worker `cloudflare/chuyen-huong.js` được Cloudflare triển khai từ `main`.

## Lằn ranh

Lời ca dao không do máy viết. Nguồn không bịa. Kho thô không vào repo này. Không thông tin cá nhân trong kho mã. Chi tiết: `AGENTS.md`.

## Giấy phép

Phần do dự án tự viết dùng CC BY 4.0, như ghi ở trang "Về dự án" của site. Phần chép từ Wikipedia, Wiktionary giữ CC BY-SA và được ghi tại chỗ. Ghi công từng nguồn: `docs/12-chinh-sach-nguon.md`.
