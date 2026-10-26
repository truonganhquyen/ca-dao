// Bảo mật, cấu hình nhìn từ ngoài (chỉ đọc; không thử đăng nhập vào tài khoản thật).
// Lỗi đã biết đánh dấu test.fail — xem docs/e2e-review/REPORT.md.
import { test, expect } from '@playwright/test';
import { chup, BAI } from './chung.mjs';

test('HTTPS: có HSTS, nosniff, chống nhúng khung', async ({ request }) => {
  const h = (await request.get('/')).headers();
  expect(h['strict-transport-security']).toBeTruthy();
  expect(h['x-content-type-options']).toBe('nosniff');
  expect(h['x-frame-options']).toBeTruthy();
});

test('L-16 XML-RPC tắt (không trả danh sách phương thức)', async ({ request }) => {
  // Đã sửa (plugin 0.29.0, inc/an-toan.php) — kiểm thử bảo vệ
  const r = await request.post('/xmlrpc.php', { headers: { 'content-type': 'text/xml' }, data: '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>' });
  expect(await r.text()).not.toContain('system.multicall');
});

test('thư mục không liệt kê tệp; .git bị chặn', async ({ request }) => {
  for (const p of ['/wp-content/uploads/', '/wp-includes/', '/.git/HEAD']) {
    expect((await request.get(p)).status(), p).toBe(403);
  }
});

test('site còn chặn Google tới khi người sáng lập quyết mở (H5)', async ({ page }) => {
  await page.goto('/');
  await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/);
});

test('L-03 không lộ tên đăng nhập (REST users, ?author=1, thông báo đăng nhập)', async ({ request }) => {
  // Đã sửa (plugin 0.29.0, inc/an-toan.php) — kiểm thử bảo vệ
  const u = await request.get('/wp-json/wp/v2/users');
  const ds = u.status() === 200 ? await u.json() : [];
  expect(Array.isArray(ds) ? ds.length : 0).toBe(0);
  const a = await request.get('/?author=1', { maxRedirects: 0 });
  expect(a.headers().location || '').not.toMatch(/\/author\//);
  // tên không tồn tại: thông báo không nên nói "tên người dùng … không được đăng ký"
  const dn = await request.post('/wp-login.php', { form: { log: `e2e-khong-co-${Date.now()}`, pwd: 'x' } });
  expect(await dn.text()).not.toMatch(/không được đăng ký/);
});

test('L-01 tham số ly_do không cho người ngoài chèn chữ vào trang bài', async ({ page }, ti) => {
  // Đã sửa (plugin 0.29.0: ?ma= thay ?ly_do=) — kiểm thử bảo vệ
  const chen = 'Tài khoản của bạn bị khóa, gọi 0900000000';
  await page.route(/\/wp-json\/cadao\/v1\/(luot-doc|su-kien)/, r => r.abort());
  await page.goto(`${BAI.docNhieu}?gop_y=loi&ly_do=${encodeURIComponent(chen)}#gop-y`);
  await chup(page, 'chen-chu-ly-do', ti);
  await expect(page.locator('.cadao-bao-gui')).not.toContainText(chen);
});

test('L-10 không còn bài mẫu "Hello world!" và bình luận mẫu', async ({ request }) => {
  test.fail(true, 'L-10: /hello-world/ (bài mẫu WordPress, bình luận của "A WordPress Commenter") đang công khai');
  expect((await request.get('/hello-world/', { maxRedirects: 0 })).status()).toBe(404);
});

test('L-11 không lộ tệp mặc định readme.html, license.txt', async ({ request }) => {
  test.fail(true, 'L-11: readme.html, license.txt trả 200');
  for (const p of ['/readme.html', '/license.txt']) expect((await request.get(p)).status(), p).not.toBe(200);
});

test('L-12 có Content-Security-Policy và Referrer-Policy', async ({ request }) => {
  test.fail(true, 'L-12: chưa có hai header này');
  const h = (await request.get('/')).headers();
  expect(h['content-security-policy'] || h['content-security-policy-report-only']).toBeTruthy();
  expect(h['referrer-policy']).toBeTruthy();
});

test('L-13 trang có bộ nhớ đệm (Cache-Control cho người đọc không đăng nhập)', async ({ request }) => {
  test.fail(true, 'L-13: không có header cache, không CDN; mỗi lượt xem PHP dựng lại trang (~0,4–0,7 s)');
  const h = (await request.get(BAI.docNhieu)).headers();
  expect(h['cache-control'] || '').toMatch(/max-age=\d+|s-maxage/);
});
