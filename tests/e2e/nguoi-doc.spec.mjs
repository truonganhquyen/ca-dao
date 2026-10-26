// Vai người đọc: trang chủ → tìm → lọc → trang bài → lưu, trích dẫn → 404. Chạy trên máy tính và điện thoại 390 px.
// Lỗi đã biết đánh dấu test.fail(true, 'L-xx …') — xem docs/e2e-review/REPORT.md; khi sửa xong, bỏ dòng test.fail.
import { test, expect } from '@playwright/test';
import { chanDem, chup, ngheLoi, quetAxe, BAI } from './chung.mjs';

test.beforeEach(async ({ context }) => { await chanDem(context); });

const TRANG = [
  ['/', 'trang-chu'], ['/kho/', 'tat-ca'], ['/tim/', 'tim'], ['/tu-dien/', 'tu-dien'], ['/ban-do/', 'ban-do'],
  ['/da-luu/', 'da-luu'], ['/the-loai/ca-dao/', 'the-loai'], ['/vung/bac-bo/', 'vung'], [BAI.docNhieu, 'bai'],
  [BAI.coDiBan, 'bai-di-ban'], ['/phuong-phap/', 'phuong-phap'], ['/chinh-sach-du-lieu/', 'chinh-sach'],
];

test.describe('Các trang chính', () => {
  for (const [duong, ten] of TRANG) {
    test(`${ten}: 200, một h1, không lỗi JS, không tràn ngang, WCAG 2.2 AA`, async ({ page }, ti) => {
      const loi = ngheLoi(page);
      const r = await page.goto(duong);
      expect(r.status()).toBe(200);
      await expect(page.locator('h1')).toHaveCount(1);
      expect(await page.evaluate(() => document.documentElement.scrollWidth - innerWidth)).toBeLessThanOrEqual(1);
      expect(await quetAxe(page)).toEqual([]);
      expect(loi).toEqual([]);
      await chup(page, ten, ti, { toanTrang: true });
    });
  }

  test('hiệu năng: trang chủ, trang bài phản hồi < 1 s, tải < 400 KB', async ({ page }) => {
    for (const duong of ['/', BAI.docNhieu]) {
      await page.goto(duong);
      const t = await page.evaluate(() => {
        const n = performance.getEntriesByType('navigation')[0];
        return { ttfb: n.responseStart, tai: performance.getEntriesByType('resource').reduce((s, r) => s + (r.transferSize || 0), n.transferSize || 0) };
      });
      test.info().annotations.push({ type: 'đo', description: `${duong}: TTFB ${Math.round(t.ttfb)} ms, ${Math.round(t.tai / 1024)} KB` });
      expect(t.ttfb).toBeLessThan(1000);
      expect(t.tai).toBeLessThan(400 * 1024);
    }
  });
});

test.describe('Tìm', () => {
  test('ô tìm ở trang chủ → trang Tìm có kết quả đúng (gõ không dấu)', async ({ page }) => {
    await page.goto('/');
    const o = page.getByRole('main').getByRole('searchbox').first();
    await o.fill('cong cha nhu nui');
    await o.press('Enter');
    await expect(page).toHaveURL(/\/tim\/\?q=/);
    await expect(page.locator('.cadao-tim-kq')).toContainText('Công cha như núi Thái Sơn');
  });

  test('gõ dần trên trang Tìm: kết quả hiện ngay, có dấu và không dấu như nhau', async ({ page }) => {
    await page.goto('/tim/');
    const o = page.locator('#cadao-tim-o');
    await o.pressSequentially('ba cô đội gạo', { delay: 60 });
    await expect(page.locator('.cadao-tim-kq')).toContainText('Ba cô đội gạo lên chùa');
    await o.fill('');
    await o.pressSequentially('ba co doi gao', { delay: 60 });
    await expect(page.locator('.cadao-tim-kq')).toContainText('Ba cô đội gạo lên chùa');
  });

  test('không có kết quả: báo rõ, gợi ý cách tìm khác', async ({ page }, ti) => {
    await page.goto('/tim/?q=' + encodeURIComponent('xyzqwv'));
    await expect(page.locator('.cadao-tim-khong')).toContainText('Chưa tìm thấy');
    await expect(page.locator('.cadao-tim-khong a[href*="/kho/"]')).toBeVisible();
    await chup(page, 'tim-rong', ti);
  });

  test('L-04 kết quả tìm cho thấy chữ người đọc đã gõ (tìm theo tên bài vè)', async ({ page }, ti) => {
    test.fail(true, 'L-04: kết quả chỉ hiện dòng đầu; tìm "bang ninh" ra "Cầu Ngu, Trung Lễ xưa nay", không thấy tên bài');
    await page.goto('/tim/');
    await page.locator('#cadao-tim-o').pressSequentially('bang ninh', { delay: 60 });
    await expect(page.locator('.cadao-tim-kq')).toContainText('1 bài');
    await chup(page, 'tim-khong-thay-ten-bai', ti);
    await expect(page.locator('.cadao-tim-kq')).toContainText('Bang Ninh');
  });

  test('tắt JavaScript: trang Tìm vẫn trả kết quả', async ({ browser, baseURL }) => {
    const ctx = await browser.newContext({ javaScriptEnabled: false, baseURL });
    const page = await ctx.newPage();
    await page.goto('/tim/?q=cong+cha');
    await expect(page.locator('.cadao-tim-kq')).toContainText('Công cha như núi Thái Sơn');
    await ctx.close();
  });
});

test.describe('Danh sách, lọc', () => {
  test('lọc theo thể loại bằng chip: Vè → 20 bài, có Bỏ lọc', async ({ page }) => {
    await page.goto('/kho/');
    await page.locator('.cadao-chip[href*="loai=ve"]').click();
    await expect(page).toHaveURL(/loai=ve/);
    await expect(page.locator('.cadao-ds-tieu')).toContainText('20 bài');
    await expect(page.locator('.cadao-bo-loc')).toBeVisible();
  });

  test('L-02 nhóm lọc Vùng, Chủ đề có dấu hiệu mở được (mũi tên)', async ({ page }, ti) => {
    test.fail(true, 'L-02: summary display:flex làm mất mũi tên ▸ — trông như tiêu đề rỗng');
    await page.goto('/kho/');
    const tom = page.locator('details.cadao-loc-nhom > summary').first();
    await chup(page, 'loc-vung-chu-de', ti);
    const coDau = await tom.evaluate(s => {
      const cs = getComputedStyle(s);
      const sau = getComputedStyle(s, '::after').content;
      const truoc = getComputedStyle(s, '::before').content;
      return cs.display === 'list-item' || (sau && sau !== 'none' && sau !== 'normal') || (truoc && truoc !== 'none' && truoc !== 'normal') || !!s.querySelector('svg');
    });
    expect(coDau).toBe(true);
  });

  test('mở nhóm Vùng rồi lọc Bắc Bộ', async ({ page }) => {
    await page.goto('/kho/');
    await page.locator('details.cadao-loc-nhom > summary', { hasText: 'Vùng' }).click();
    await page.locator('.cadao-chip', { hasText: 'Bắc Bộ' }).click();
    await expect(page).toHaveURL(/mien=bac-bo/);
    await expect(page.locator('.cadao-ds-tieu')).toContainText(/\d+ bài/);
  });

  test('L-07 phân trang: số trang đủ lớn để chạm (≥ 24 px)', async ({ page }, ti) => {
    test.fail(true, 'L-07: số trang 1 2 3 … 18 là chữ nhỏ ~10×20 px; 171 bài chia 18 trang');
    await page.goto('/kho/');
    const so = page.locator('.wp-block-query-pagination-numbers a').first();
    await so.scrollIntoViewIfNeeded();
    await chup(page, 'phan-trang', ti);
    const hop = await so.boundingBox();
    expect(Math.min(hop.width, hop.height)).toBeGreaterThanOrEqual(24);
  });
});

test.describe('Trang bài', () => {
  test('lời, dòng mức kiểm, chú thích chạm-xem, Esc đóng và trả focus', async ({ page }, ti) => {
    await page.goto(BAI.docNhieu);
    await expect(page.locator('pre.wp-block-verse').first()).toContainText('Ba cô đội gạo lên chùa');
    await expect(page.locator('.cadao-muc-kiem')).toContainText(/Lời/);
    const tu = page.locator('a.cadao-tu').first();
    await tu.click();
    const o = page.locator('dialog.cadao-o-ct');
    await expect(o).toBeVisible();
    await expect(o.locator('.cadao-o-ct-nd')).not.toBeEmpty();
    await chup(page, 'chu-thich-mo', ti);
    await page.keyboard.press('Escape');
    await expect(o).toBeHidden();
    await expect(tu).toBeFocused();
  });

  test('Trích dẫn mở hộp có tên bài và phongdao.com', async ({ page }) => {
    await page.goto(BAI.docNhieu);
    await page.locator('details.cadao-trich-dan > summary').click();
    const hop = page.locator('.cadao-trich-dan-hop');
    await expect(hop).toContainText('Ba cô đội gạo lên chùa');
    await expect(hop).toContainText('phongdao.com');
  });

  test('Lưu bài → trang Đã lưu có bài; bỏ lưu được', async ({ page }) => {
    await page.goto(BAI.docNhieu);
    const nut = page.locator('.cadao-luu');
    await nut.click();
    await expect(nut).toHaveAttribute('aria-pressed', 'true');
    await page.goto('/da-luu/');
    await expect(page.locator('.cadao-da-luu')).toContainText('Ba cô đội gạo lên chùa');
    await page.goto(BAI.docNhieu);
    await page.locator('.cadao-luu').click();
    await expect(page.locator('.cadao-luu')).toHaveAttribute('aria-pressed', 'false');
  });

  test('bài có dị bản hiện mục Dị bản; bài có Hiểu nhanh hiện Hiểu nhanh', async ({ page }) => {
    await page.goto(BAI.coDiBan);
    await expect(page.getByRole('heading', { name: 'Dị bản' })).toBeVisible();
    await page.goto(BAI.coHieuNhanh);
    await expect(page.getByRole('heading', { name: 'Hiểu nhanh' })).toBeVisible();
  });

  test('L-05 cuối bài có gợi ý đọc tiếp (bài liên quan)', async ({ page }, ti) => {
    test.fail(true, 'L-05: trang bài không có "Bài liên quan / cùng chủ đề" — người đọc hết đường');
    await page.goto(BAI.docNhieu);
    await chup(page, 'cuoi-bai', ti, { toanTrang: true });
    const lienKetBai = await page.locator('main a[href*="/ca-dao/"], main a[href*="/ve/"]').evaluateAll(
      (as, chinh) => new Set(as.map(a => new URL(a.href).pathname).filter(p => p !== chinh)).size, BAI.docNhieu); // bài khác, không tính liên kết góp ý của chính bài
    expect(lienKetBai).toBeGreaterThanOrEqual(3);
  });

  test('ảnh chia sẻ og:image tải được, JSON-LD hợp lệ', async ({ page, request }) => {
    await page.goto(BAI.docNhieu);
    const anh = await page.locator('meta[property="og:image"]').getAttribute('content');
    const r = await request.get(anh);
    expect(r.status()).toBe(200);
    expect(r.headers()['content-type']).toContain('image/');
    const ld = await page.locator('script[type="application/ld+json"]').first().textContent();
    expect(() => JSON.parse(ld)).not.toThrow();
  });

  test('điện thoại: thanh dưới không che chân trang', async ({ page }, ti) => {
    test.skip(ti.project.name !== 'dien-thoai', 'chỉ điện thoại');
    await page.goto(BAI.docNhieu);
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    const thanh = await page.locator('.cadao-thanh-duoi').boundingBox();
    const cuoi = await page.locator('footer a').last().boundingBox();
    expect(cuoi.y + cuoi.height).toBeLessThanOrEqual(thanh.y + 1);
  });

  test('bàn phím: phím Tab đầu tiên tới liên kết "Chuyển đến nội dung"', async ({ page }, ti) => {
    test.skip(ti.project.name !== 'may-tinh', 'bàn phím trên máy tính');
    await page.goto(BAI.docNhieu);
    await page.keyboard.press('Tab');
    await expect(page.locator(':focus')).toHaveText(/Chuyển|nội dung|Bỏ qua/i);
  });
});

test.describe('404', () => {
  test('trang không có: mã 404, có ô tìm', async ({ page }, ti) => {
    const r = await page.goto('/khong-co-trang-nay-e2e/');
    expect(r.status()).toBe(404);
    await expect(page.getByRole('searchbox')).toBeVisible();
    await chup(page, '404', ti, { toanTrang: true });
  });

  test('L-06 trang 404 đúng giọng site: không ảnh mẫu của theme, nút Tìm không bị cắt, có lối đi tiếp', async ({ page }) => {
    test.fail(true, 'L-06: 404 là mẫu mặc định Twenty Twenty-Five (ảnh cây 115 KB, "Nhập tại đây…", nút "Tìm kiếm" vỡ dòng)');
    await page.goto('/khong-co-trang-nay-e2e/');
    await expect(page.locator('main img')).toHaveCount(0);
    await expect(page.locator('main a[href$="/kho/"]')).toBeVisible();
    const nut = page.locator('main button[type="submit"], main .wp-block-search__button').first();
    expect(await nut.evaluate(b => b.scrollHeight <= b.clientHeight + 1 && b.scrollWidth <= b.clientWidth + 1)).toBe(true);
  });
});
