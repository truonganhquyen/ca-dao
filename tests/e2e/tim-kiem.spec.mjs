// Chất lượng tìm kiếm qua API /wp-json/cadao/v1/tim (không qua giao diện, không ghi sự kiện "tìm không ra").
// Lỗi đã biết đánh dấu test.fail — xem docs/e2e-review/REPORT.md.
import { test, expect } from '@playwright/test';
import { timApi } from './chung.mjs';

const tieuDe = kq => kq.bai.map(b => b.tieu_de);

test.describe('Tìm: đúng', () => {
  const CAP = [ // có dấu / không dấu / viết hoa / kiểu bỏ dấu cũ phải cho cùng kết quả
    ['Công cha', 'cong cha'], ['tát nước đầu đình', 'tat nuoc dau dinh'], ['BA CÔ ĐỘI GẠO', 'ba co doi gao'],
    ['hoà', 'hòa'], ['thuỷ', 'thủy'], ['Đồng Nai', 'dong nai'],
  ];
  for (const [a, b] of CAP) {
    test(`"${a}" và "${b}" ra cùng số bài`, async ({ request }) => {
      const [x, y] = [await timApi(request, a), await timApi(request, b)];
      expect(x.tong).toBeGreaterThan(0);
      expect(x.tong).toBe(y.tong);
    });
  }

  const DUNG = [
    ['cong cha nhu nui thai son', 'Công cha như núi Thái Sơn'],
    ['ba co doi gao', 'Ba cô đội gạo lên chùa'],
    ['đêm qua', 'Đêm qua trời sáng trăng rằm'],
    ['Ba cô đội gạo lên chùa,', 'Ba cô đội gạo lên chùa'], // dấu câu thừa
    ['Ba  cô   đội', 'Ba cô đội gạo lên chùa'],           // khoảng trắng thừa
  ];
  for (const [q, mong] of DUNG) {
    test(`"${q}" → kết quả đầu là "${mong}"`, async ({ request }) => {
      expect(tieuDe(await timApi(request, q))[0]).toBe(mong);
    });
  }

  test('chuỗi lạ (HTML, SQL, ký tự đại diện, rất dài) không gây lỗi', async ({ request }) => {
    for (const q of ['<script>alert(1)</script>', "' OR 1=1 --", '%', '_', 'a'.repeat(600)]) {
      const r = await request.get('/wp-json/cadao/v1/tim', { params: { q } });
      expect(r.status()).toBe(200);
      const kq = await r.json();
      expect(Array.isArray(kq.bai)).toBe(true);
    }
  });

  test('phản hồi dưới 1,5 s', async ({ request }) => {
    const t0 = Date.now();
    await timApi(request, 'con cò');
    expect(Date.now() - t0).toBeLessThan(1500);
  });
});

test.describe('Tìm: lỗi đã biết', () => {
  test('L-09 gõ sai một chữ vẫn ra bài (ba co doi gai → Ba cô đội gạo lên chùa)', async ({ request }) => {
    test.fail(true, 'L-09: không chịu lỗi gõ; kết quả đầu là "Vè cá"');
    expect(tieuDe(await timApi(request, 'ba co doi gai'))[0]).toBe('Ba cô đội gạo lên chùa');
  });

  test('L-08 câu không có trong kho thì không trả bài lạc đề', async ({ request }) => {
    test.fail(true, 'L-08: các bài vè dài chứa mọi chữ nên khớp gần như mọi câu tìm');
    const kq = await timApi(request, 'ăn quả nhớ kẻ trồng cây');
    // hoặc không có bài nào, hoặc bài đầu thực sự chứa cụm từ
    const dau = kq.bai[0];
    expect(!dau || /nhớ kẻ trồng cây/i.test(`${dau.tieu_de} ${dau.dong_dau} ${dau.dong_khop}`)).toBe(true);
  });

  test('L-08 gõ hai chữ có dấu thì bài có đúng cụm đó đứng trước bài vè dài', async ({ request }) => {
    test.fail(true, 'L-08: "lục bát" ra Vè cá, Vè bánh, Vè nói láo trước');
    const ds = (await timApi(request, 'tháng sáu')).bai.slice(0, 3).map(b => b.tieu_de);
    expect(ds.filter(t => t.startsWith('Vè'))).toHaveLength(0);
  });

  test('L-04 API trả tiêu đề dạng chữ thường, không mã HTML', async ({ request }) => {
    test.fail(true, 'L-04: get_the_title() trả "&#8211;"');
    const kq = await timApi(request, 'bang ninh');
    expect(kq.bai.length).toBeGreaterThan(0);
    for (const b of kq.bai) expect(b.tieu_de).not.toMatch(/&#\d+;|&[a-z]+;/);
  });

  test('L-15 câu tục ngữ quen thuộc có trong kho', async ({ request }) => {
    test.fail(true, 'L-15: site chưa có bài thành ngữ, tục ngữ nào (171 bài: 151 ca dao, 20 vè)');
    for (const q of ['uống nước nhớ nguồn', 'có công mài sắt có ngày nên kim', 'ăn quả nhớ kẻ trồng cây']) {
      expect((await timApi(request, q)).tong, q).toBeGreaterThan(0);
    }
  });
});
