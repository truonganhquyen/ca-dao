// Vai quản trị: đăng nhập → đi các trang của plugin → thêm / sửa / chuyển Chờ duyệt / xóa một bài thử → góp ý của
// người đọc tới hàng chờ rồi xóa → thêm / xóa một tài khoản thử → số liệu trên Bảng tin.
//
// AN TOÀN (AGENTS.md):
// - Chỉ tạo bản ghi có tiền tố "[E2E-TEST]" và tự xóa hẳn ở cuối (kể cả khi bước giữa hỏng — xem afterAll).
// - KHÔNG BAO GIỜ bấm Đăng (máy không tự đăng bài); bài thử chỉ ở Bản nháp / Chờ duyệt.
// - Không tạo giá trị trong bộ chủ đề, vùng, thể loại; không đụng bài, kho, nguồn thật.
// - Mật khẩu chỉ đọc từ biến môi trường E2E_USER / E2E_PASS, không ghi vào tệp nào.
// - Ảnh chụp quản trị lưu ở docs/e2e-review/screenshots/admin/ (đã .gitignore: có thể chứa kho thô, email).
import { test, expect, request as yeuCau } from '@playwright/test';
import { chanDem, chup, BAI } from './chung.mjs';

const NHAN = '[E2E-TEST]';
const DAU = `${NHAN} Bài thử tự động ${new Date().toISOString().slice(0, 16)}`;
const ANH = { quanTri: true };

test.skip(!process.env.E2E_USER || !process.env.E2E_PASS, 'Cần E2E_USER và E2E_PASS (xem tests/e2e/README.md)');
test.describe.configure({ mode: 'serial' });

/** @type {import('@playwright/test').Page} */
let qt;
let idBai = 0;
let soTrungTruoc = -1;

/** Chờ trình soạn khối sẵn sàng, tắt hướng dẫn chào mừng. */
async function choTrinhSoan(trang) {
  await trang.waitForFunction(() => window.wp?.data?.select('core/editor')?.getCurrentPostId?.(), null, { timeout: 60_000 });
  await trang.evaluate(() => {
    try { wp.data.dispatch('core/preferences').set('core/edit-post', 'welcomeGuide', false); } catch { /* bản cũ */ }
  });
  await trang.keyboard.press('Escape').catch(() => {});
}

/** Lưu qua trình soạn, chờ lưu xong. */
async function luu(trang) {
  await trang.evaluate(() => wp.data.dispatch('core/editor').savePost());
  await trang.waitForFunction(() => !wp.data.select('core/editor').isSavingPost() && !wp.data.select('core/editor').isAutosavingPost());
  const loi = await trang.evaluate(() => wp.data.select('core/editor').didPostSaveRequestFail());
  expect(loi, 'lưu bài thất bại').toBe(false);
}

/** Số cặp "Có thể trùng" đang chờ (số trên menu). */
async function soTrung(trang) {
  await trang.goto('/wp-admin/edit.php?post_type=cdtn&page=cadao-co-the-trung');
  const t = await trang.locator('.subsubsub, .wrap').first().innerText();
  return Number((t.match(/Chờ bạn quyết \((\d+)\)/) || [])[1] ?? -1);
}

test.beforeAll(async ({ browser, baseURL }) => {
  const ctx = await browser.newContext({ baseURL, viewport: { width: 1366, height: 900 }, locale: 'vi-VN' });
  await chanDem(ctx);
  qt = await ctx.newPage();
});

test.afterAll(async () => {
  // Dọn sạch mọi bản ghi [E2E-TEST] còn sót (bài, góp ý, tài khoản), kể cả khi các bước trên hỏng giữa chừng.
  if (!qt) return;
  try {
    await qt.goto('/wp-admin/');
    const con = await qt.evaluate(async nhan => {
      const ra = [];
      for (const kieu of ['cdtn']) {
        const ds = await wp.apiFetch({ path: `/wp/v2/${kieu}?status=draft,pending,trash,private&search=${encodeURIComponent(nhan)}&_fields=id,title&per_page=50&context=edit` }).catch(() => []);
        for (const b of ds) {
          if (b.title?.raw?.startsWith(nhan)) { await wp.apiFetch({ path: `/wp/v2/${kieu}/${b.id}?force=true`, method: 'DELETE' }); ra.push(`${kieu}#${b.id}`); }
        }
      }
      const nd = await wp.apiFetch({ path: '/wp/v2/users?search=e2e-test&context=edit&_fields=id,username' }).catch(() => []);
      for (const u of nd) {
        if (u.username.startsWith('e2e-test-')) { await wp.apiFetch({ path: `/wp/v2/users/${u.id}?force=true&reassign=1`, method: 'DELETE' }); ra.push(`user#${u.id}`); }
      }
      return ra;
    }, NHAN);
    if (con.length) console.log('Dọn thêm:', con.join(', '));
    await qt.goto(`/wp-admin/edit.php?post_type=gop_y&s=E2E-TEST&post_status=all`);
    if (await qt.locator('#the-list tr.type-gop_y').count()) console.log('CẦN DỌN TAY: còn góp ý [E2E-TEST] trong Góp ý của người đọc');
  } catch (e) { console.log('Dọn cuối lỗi:', e.message); }
  await qt.context().close();
});

test('đăng nhập', async ({}, ti) => {
  await qt.goto('/wp-login.php', { waitUntil: 'networkidle' });
  await qt.waitForTimeout(500); // wp-login.php tự đặt focus vào ô tên sau 200 ms; điền sớm thì chữ rơi nhầm ô
  await qt.fill('#user_login', process.env.E2E_USER);
  await qt.fill('#user_pass', process.env.E2E_PASS);
  await expect(qt.locator('#user_login')).toHaveValue(process.env.E2E_USER);
  await Promise.all([qt.waitForURL(/\/wp-admin\//, { timeout: 60_000 }), qt.click('#wp-submit')]);
  await expect(qt.locator('#wpadminbar')).toBeVisible();
  await chup(qt, 'bang-tin', ti, { ...ANH, toanTrang: true });
});

test('mọi trang của plugin mở được, không lỗi PHP, dưới 5 s', async ({}, ti) => {
  const TRANG = ['cadao-ban-bien-tap', 'cadao-nguon-tu-dong', 'cadao-kho', 'cadao-co-the-trung', 'cadao-tach-loai',
    'cadao-wikidata', 'cadao-goi-y', 'cadao-ban-co-biet', 'cadao-chong-rac', 'cadao-hieu-nhanh'];
  const cham = [];
  for (const p of TRANG) {
    const t0 = Date.now();
    const r = await qt.goto(`/wp-admin/edit.php?post_type=cdtn&page=${p}`);
    const ms = Date.now() - t0;
    expect(r.status(), p).toBe(200);
    const chu = await qt.locator('#wpbody-content').innerText();
    expect(chu, `${p}: lỗi PHP`).not.toMatch(/(Warning|Notice|Deprecated|Fatal error):/);
    cham.push(`${p} ${ms} ms`);
    expect(ms, p).toBeLessThan(5000);
    await chup(qt, p, ti, { ...ANH, toanTrang: true });
  }
  ti.annotations.push({ type: 'đo', description: cham.join(' · ') });
});

test('thêm bài thử (Bản nháp) — dán lời của một bài đã có: máy báo "Có thể trùng"', async ({}, ti) => {
  soTrungTruoc = await soTrung(qt);
  // Lời chép nguyên từ bài đang đăng (không phải lời do máy viết), chỉ để thử chống trùng; bài thử bị xóa hẳn ở cuối
  const goc = await (await qt.request.get(`/wp-json/wp/v2/cdtn?slug=${BAI.docNhieu.split('/')[2]}&_fields=content`)).json();
  const loiHtml = (goc[0].content.rendered.match(/<pre class="wp-block-verse[^>]*>([\s\S]*?)<\/pre>/) || [])[1]
    .replace(/<(?!br\s*\/?>)[^>]+>/g, '').trim().replace(/(<br\s*\/?>)?\n/g, '<br>');
  await qt.goto('/wp-admin/post-new.php?post_type=cdtn');
  await choTrinhSoan(qt);
  // Gõ tiêu đề như người dùng (trong khung soạn)
  const khung = qt.frameLocator('iframe[name="editor-canvas"]');
  const oTieuDe = khung.locator('.editor-post-title__input, [aria-label="Thêm tiêu đề"]').first();
  await oTieuDe.click();
  await oTieuDe.pressSequentially(DAU, { delay: 10 });
  await qt.evaluate(html => wp.data.dispatch('core/block-editor').insertBlocks(wp.blocks.createBlock('core/verse', { content: html })), loiHtml);
  // Ô "Có thể trùng" gọi API trùng sau khi ngừng gõ; gọi trực tiếp để kiểm kết quả
  const kq = await qt.evaluate(html => wp.apiFetch({ path: '/cadao/v1/trung', method: 'POST', data: { loi_html: html, bai: 0 } }), loiHtml);
  expect(JSON.stringify(kq.ket_qua)).toContain('Ba cô đội gạo lên chùa');
  await luu(qt);
  idBai = await qt.evaluate(() => wp.data.select('core/editor').getCurrentPostId());
  expect(idBai).toBeGreaterThan(0);
  await qt.waitForTimeout(3000);
  await chup(qt, 'soan-bai-thu', ti, ANH);
  // Trong danh sách: bài thử là Bản nháp, có nhãn "Có thể trùng"
  await qt.goto('/wp-admin/edit.php?post_type=cdtn&post_status=draft');
  const hang = qt.locator(`#post-${idBai}`);
  await expect(hang).toContainText(NHAN);
  await expect(hang).toContainText('Có thể trùng');
  await chup(qt, 'danh-sach-ban-nhap', ti, ANH);
});

test('trình soạn trên điện thoại 390 px: mở được, thấy ô tiêu đề và nút lưu', async ({ browser, baseURL }, ti) => {
  test.skip(!idBai, 'chưa có bài thử');
  const ctx = await browser.newContext({ baseURL, storageState: await qt.context().storageState(), viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  const dt = await ctx.newPage();
  await dt.goto(`/wp-admin/post.php?post=${idBai}&action=edit`);
  await choTrinhSoan(dt);
  await expect(dt.frameLocator('iframe[name="editor-canvas"]').locator('.editor-post-title__input, [aria-label="Thêm tiêu đề"]').first()).toBeVisible();
  await dt.waitForTimeout(2000);
  await chup(dt, 'soan-dien-thoai', ti, ANH);
  await ctx.close();
});

test('sửa bài thử: đổi tiêu đề, chuyển Chờ duyệt (không Đăng), Bàn biên tập đếm được', async ({}, ti) => {
  test.skip(!idBai, 'chưa có bài thử');
  await qt.goto(`/wp-admin/post.php?post=${idBai}&action=edit`);
  await choTrinhSoan(qt);
  await qt.evaluate(t => wp.data.dispatch('core/editor').editPost({ title: t, status: 'pending' }), `${DAU} (đã sửa)`);
  await luu(qt);
  expect(await qt.evaluate(() => wp.data.select('core/editor').getEditedPostAttribute('status'))).toBe('pending');
  const lienKet = await qt.evaluate(() => wp.data.select('core/editor').getEditedPostPreviewLink());
  await qt.goto('/wp-admin/edit.php?post_type=cdtn&post_status=pending');
  await expect(qt.locator(`#post-${idBai}`)).toContainText('(đã sửa)');
  // Xem trước được (người sáng lập duyệt bằng mắt)
  const xem = await qt.goto(lienKet);
  expect(xem.status()).toBe(200);
  await expect(qt.locator('h1')).toContainText(NHAN);
  await chup(qt, 'xem-truoc-bai-thu', ti, { ...ANH, toanTrang: true });
  // Công khai không thấy bài Chờ duyệt
  const khach = await yeuCau.newContext({ baseURL: new URL(qt.url()).origin }); // không cookie đăng nhập
  expect([401, 403, 404]).toContain((await khach.get(`/wp-json/wp/v2/cdtn/${idBai}`)).status());
  await khach.dispose();
  await qt.goto('/wp-admin/edit.php?post_type=cdtn&page=cadao-ban-bien-tap');
  await chup(qt, 'ban-bien-tap-co-bai-cho', ti, ANH);
});

test('xóa bài thử: vào thùng rác rồi xóa hẳn; không còn cặp "Có thể trùng" nào trỏ tới bài thử', async ({}, ti) => {
  test.skip(!idBai, 'chưa có bài thử');
  await qt.goto('/wp-admin/edit.php?post_type=cdtn&post_status=pending');
  const hang = qt.locator(`#post-${idBai}`);
  await expect(hang).toContainText(NHAN); // chỉ xóa đúng bài thử
  await hang.hover();
  await hang.locator('a.submitdelete').click();
  await qt.goto('/wp-admin/edit.php?post_type=cdtn&post_status=trash');
  const rac = qt.locator(`#post-${idBai}`);
  await expect(rac).toContainText(NHAN);
  await rac.hover();
  await rac.locator('a.submitdelete').click(); // "Xóa vĩnh viễn"
  await expect(qt.locator(`#post-${idBai}`)).toHaveCount(0);
  // Máy thu thập chạy mỗi 5 phút có thể thêm cặp mới, nên không so tổng số mà kiểm không còn thẻ nào của bài thử
  await soTrung(qt);
  await expect(qt.locator('.card', { hasText: NHAN })).toHaveCount(0);
  idBai = 0;
});

test('góp ý của người đọc: gửi từ trang bài (không đăng nhập) → tới hàng chờ → xóa hẳn', async ({ browser, baseURL }, ti) => {
  const ctx = await browser.newContext({ baseURL, viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await chanDem(ctx);
  const doc = await ctx.newPage();
  await doc.goto(BAI.docNhieu);
  await doc.locator('details.cadao-gop-y-hop > summary').click();
  await doc.locator('input[name="loai"][value="bao_loi"]').check();
  await doc.locator('#cadao-gy-nd').fill(`${NHAN} góp ý thử tự động — sẽ bị xóa ngay`);
  await doc.locator('input[name="dong_y"]').check();
  await doc.getByRole('button', { name: 'Gửi góp ý' }).click();
  await expect(doc.locator('.cadao-bao-gui')).toContainText('Cảm ơn bạn');
  await chup(doc, 'gop-y-da-gui', ti);
  await ctx.close();

  await qt.goto(`/wp-admin/edit.php?post_type=gop_y&s=${encodeURIComponent('E2E-TEST')}&post_status=all`);
  const hang = qt.locator('#the-list tr', { hasText: 'Báo lỗi — Ba cô đội gạo lên chùa' }).first();
  await expect(hang).toBeVisible();
  await chup(qt, 'gop-y-hang-cho', ti, ANH);
  const id = (await hang.getAttribute('id')).replace('post-', '');
  await qt.goto(`/wp-admin/post.php?post=${id}&action=edit`);
  expect(await qt.content()).toContain(NHAN); // đúng góp ý thử
  await qt.goto(`/wp-admin/edit.php?post_type=gop_y&s=${encodeURIComponent('E2E-TEST')}&post_status=all`);
  await qt.locator(`#post-${id}`).hover();
  await qt.locator(`#post-${id} a.submitdelete`).click();
  await qt.goto('/wp-admin/edit.php?post_type=gop_y&post_status=trash');
  await qt.locator(`#post-${id}`).hover();
  await qt.locator(`#post-${id} a.submitdelete`).click();
  await expect(qt.locator(`#post-${id}`)).toHaveCount(0);
});

test('tài khoản: thêm một Thành viên thử (không gửi thư) rồi xóa', async ({}, ti) => {
  const ten = `e2e-test-${Date.now()}`;
  await qt.goto('/wp-admin/user-new.php');
  await qt.fill('#user_login', ten);
  await qt.fill('#email', `${ten}@example.invalid`);
  await qt.fill('#first_name', NHAN);
  // Mật khẩu yếu có được chấp nhận không (WordPress mặc định chỉ hỏi "xác nhận dùng mật khẩu yếu")
  await qt.locator('#pass1').fill('123456');
  const yeu = qt.locator('.pw-weak input[type="checkbox"], #pw-weak');
  const choYeu = await yeu.isVisible().catch(() => false);
  ti.annotations.push({ type: 'quan sát', description: choYeu ? 'Cho phép mật khẩu "123456" sau một ô xác nhận' : 'Không có ô xác nhận mật khẩu yếu' });
  if (choYeu) await yeu.check();
  await qt.locator('#send_user_notification').uncheck().catch(() => {});
  await qt.selectOption('#role', 'subscriber');
  await chup(qt, 'them-tai-khoan', ti, ANH);
  await qt.click('#createusersub');
  await qt.goto(`/wp-admin/users.php?s=${ten}`);
  const hang = qt.locator('#the-list tr', { hasText: ten });
  await expect(hang).toBeVisible();
  const id = (await hang.getAttribute('id')).replace('user-', '');
  await qt.goto(`/wp-admin/users.php?action=delete&user=${id}&_wpnonce=${await hang.locator('a.submitdelete').getAttribute('href').then(h => new URL(h, 'https://x').searchParams.get('_wpnonce'))}`);
  await qt.click('#submit');
  await qt.goto(`/wp-admin/users.php?s=${ten}`);
  await expect(qt.locator('#the-list tr', { hasText: ten })).toHaveCount(0);
});

test('Bảng tin: có ô lượt đọc, "người đọc cần gì", việc chờ', async ({}, ti) => {
  await qt.goto('/wp-admin/');
  await expect(qt.locator('#cadao_luot_doc')).toBeVisible();
  const chu = await qt.locator('#dashboard-widgets').innerText();
  expect(chu).toMatch(/lượt đọc/i);
  expect(chu).toMatch(/việc chờ bạn/i);
  await chup(qt, 'bang-tin-so-lieu', ti, { ...ANH, toanTrang: true });
});
