// Phiên bản plugin, theme: đọc trong repo và trên trang của site thật (T-129, D-080).
import fs from 'node:fs';

/** Thư mục máy chủ lấy plugin, theme (như wordpress/may-chu/cap-nhat-plugin.sh): wordpress/v2 khi wordpress/nguon-trien-khai ghi "v2". */
export function thuMucTrienKhai(goc = '.') {
  let nguon = '';
  try { nguon = fs.readFileSync(`${goc}/wordpress/nguon-trien-khai`, 'utf8').trim(); } catch { /* chưa có: MVP */ }
  return nguon === 'v2' ? 'wordpress/v2' : 'wordpress';
}

/** Phiên bản trong repo của bản máy chủ đang lấy: plugin (CADAO_PHIEN_BAN) và theme (dòng Version: của style.css). */
export function phienBanRepo(goc = '.') {
  const d = `${goc}/${thuMucTrienKhai(goc)}`;
  const plugin = fs.readFileSync(`${d}/plugins/ca-dao/ca-dao.php`, 'utf8').match(/CADAO_PHIEN_BAN\s*=\s*'([0-9.]+)'/)?.[1] ?? null;
  const theme = fs.readFileSync(`${d}/themes/ca-dao/style.css`, 'utf8').match(/^Version:\s*([0-9.]+)/m)?.[1] ?? null;
  return { plugin, theme };
}

/**
 * Phiên bản site đang phục vụ, đọc từ ?ver= của tệp plugin, theme trong HTML một trang bài (khách xem):
 * MVP ca-dao.js, style.css; V2 (sau ngày chuyển, docs/luu-tru/29-chuyen-doi-v2.md) do-luong.js, assets/giao-dien.css.
 */
export function phienBanTrang(html) {
  const plugin = html.match(/plugins\/ca-dao\/assets\/(?:ca-dao|do-luong)\.js\?ver=([0-9.]+)/)?.[1] ?? null;
  const theme = html.match(/themes\/ca-dao\/(?:style|assets\/giao-dien)\.css\?ver=([0-9.]+)/)?.[1] ?? null;
  return { plugin, theme };
}

/** Các chỗ lệch giữa repo và site; mảng rỗng là khớp. Không đọc được số trên site cũng là lệch. */
export function chenhLech(repo, site) {
  return ['plugin', 'theme'].filter(k => repo[k] !== site[k]).map(k => `${k}: repo ${repo[k] ?? '?'}, site ${site[k] ?? 'không đọc được'}`);
}

/** Địa chỉ trang bài đầu tiên trong HTML trang Tất cả (/kho/). */
export function baiDauTien(html, goc) {
  const m = html.match(new RegExp(`href="(${goc.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}/(?:ca-dao|tuc-ngu|ve|dong-dao|cau-do)/[^"#?]+/)"`));
  return m?.[1] ?? null;
}
