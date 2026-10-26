// Phần dùng chung cho các kịch bản E2E.
import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';

const goc = path.resolve(path.dirname(new URL(import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1')), '../..');
export const THU_MUC_ANH = path.join(goc, 'docs/e2e-review/screenshots');
// Ảnh trang quản trị có thể chứa kho thô, email: lưu riêng, không đưa vào repo công khai (.gitignore)
export const THU_MUC_ANH_QT = path.join(THU_MUC_ANH, 'admin');

/**
 * Chặn các yêu cầu đếm (lượt đọc, sự kiện đo lường) để khảo sát không làm bẩn số liệu thật
 * ("Đọc nhiều tuần này", "câu tìm không ra" trên Bảng tin).
 */
export async function chanDem(ctxHoacTrang) {
  await ctxHoacTrang.route(/\/wp-json\/cadao\/v1\/(luot-doc|su-kien)/, r => r.abort());
}

/** Chụp ảnh vào docs/e2e-review/screenshots/<dự án>-<tên>.png. */
export async function chup(trang, ten, testInfo, { toanTrang = false, quanTri = false } = {}) {
  const thuMuc = quanTri ? THU_MUC_ANH_QT : THU_MUC_ANH;
  fs.mkdirSync(thuMuc, { recursive: true });
  const tep = path.join(thuMuc, `${testInfo.project.name}-${ten}.png`);
  await trang.screenshot({ path: tep, fullPage: toanTrang });
  return tep;
}

/** Ghi lỗi JavaScript và lỗi console của trang. */
export function ngheLoi(trang) {
  const loi = [];
  trang.on('pageerror', e => loi.push(`pageerror: ${e.message}`));
  trang.on('console', m => { if (m.type() === 'error') loi.push(`console: ${m.text()}`); });
  return loi;
}

const axeNguon = fs.readFileSync(createRequire(import.meta.url).resolve('axe-core/axe.min.js'), 'utf8');
/** Chạy axe-core WCAG 2.2 A/AA, trả về danh sách vi phạm (id × số phần tử). */
export async function quetAxe(trang) {
  await trang.addScriptTag({ content: axeNguon });
  return trang.evaluate(async () => (await window.axe.run(document, { runOnly: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'] }))
    .violations.map(v => `${v.id} (${v.impact}) ×${v.nodes.length}: ${v.nodes[0].target.join(' ')}`));
}

/** Gọi API tìm của site. */
export async function timApi(request, q) {
  const r = await request.get('/wp-json/cadao/v1/tim', { params: { q } });
  return r.json();
}

// Bài mẫu dùng trong kịch bản (bài đang đăng trên phongdao.com, 26/10/2026)
export const BAI = {
  docNhieu: '/ca-dao/ba-co-doi-gao-len-chua/',   // có chú thích, đọc nhiều
  coDiBan: '/ca-dao/an-sung-nam-goc-cay-sung/',   // có dị bản
  coHieuNhanh: '/ca-dao/cong-cha-nhu-nui-thai-son-2/', // bài duy nhất có Hiểu nhanh
};
