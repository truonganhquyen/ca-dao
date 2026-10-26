# Khảo sát E2E phongdao.com (Playwright)

Kịch bản đã dùng cho báo cáo `docs/e2e-review/REPORT.md`. Chạy trên **site thật** (mặc định `https://phongdao.com`),
bằng Chrome đã cài trên máy (không tải trình duyệt riêng).

| Tệp | Nội dung | Ghi lên site? |
|---|---|---|
| `nguoi-doc.spec.mjs` | Người đọc, máy tính 1366 px và điện thoại 390 px: các trang chính (axe WCAG 2.2 AA, lỗi JS, tràn ngang), tìm, lọc, trang bài, lưu, trích dẫn, 404 | Không |
| `tim-kiem.spec.mjs` | Chất lượng tìm qua API: có dấu / không dấu, dấu câu, chuỗi lạ, sai chính tả, độ chính xác | Không |
| `bao-mat.spec.mjs` | Header, XML-RPC, lộ tên đăng nhập, tệp mặc định, chèn chữ qua tham số | Không (một lần đăng nhập sai với **tên không tồn tại**) |
| `quan-tri.spec.mjs` | Quản trị: đăng nhập, các trang plugin, thêm / sửa / Chờ duyệt / xóa bài thử, góp ý thử, tài khoản thử, Bảng tin | **Có** — chỉ bản ghi `[E2E-TEST]` / `e2e-test-…`, tự xóa hẳn |

## Chạy

```bash
npm ci
npm run e2e                     # người đọc + tìm + bảo mật (không ghi gì lên site)

# Quản trị (tạo rồi xóa bản ghi thử). Mật khẩu chỉ để trong biến môi trường, không ghi vào tệp:
E2E_USER=... E2E_PASS=... npm run e2e:quan-tri          # bash
$env:E2E_USER='...'; $env:E2E_PASS='...'; npm run e2e:quan-tri   # PowerShell

E2E_BASE_URL=http://127.0.0.1:9411 npm run e2e   # chạy với site khác (ví dụ Playground)
npx playwright show-report test-results/e2e       # xem dấu vết khi hỏng
```

Ảnh chụp: `docs/e2e-review/screenshots/` (người đọc) và `docs/e2e-review/screenshots/admin/` (quản trị — **đã
.gitignore** vì có thể chứa kho thô, email; không đưa lên repo công khai).

## Lỗi đã biết

Kiểm thử của lỗi đã biết đánh dấu `test.fail(true, 'L-xx …')`: bộ kiểm vẫn xanh khi lỗi còn đó, và **báo đỏ khi lỗi
đã được sửa** ("Expected to fail, but passed") — lúc đó xóa dòng `test.fail` để kiểm thử thành kiểm thử bảo vệ.

## An toàn

- Chặn mọi yêu cầu `/wp-json/cadao/v1/luot-doc` và `/su-kien` để không làm bẩn "Đọc nhiều", "câu tìm không ra".
- Không bao giờ bấm Đăng; bài thử chỉ ở Bản nháp / Chờ duyệt (AGENTS.md: máy không tự đăng bài).
- Không tạo giá trị chủ đề, vùng, thể loại; không đụng bài, kho, nguồn thật.
- `afterAll` của `quan-tri.spec.mjs` dọn mọi bài, tài khoản `[E2E-TEST]` còn sót kể cả khi bước giữa hỏng; góp ý
  thử còn sót thì in "CẦN DỌN TAY".
- Mỗi lần chạy `quan-tri` dùng 1 trong 5 lượt gửi góp ý mỗi giờ của IP máy chạy.
