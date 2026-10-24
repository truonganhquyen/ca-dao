// Đọc trạng thái dự án và bảng backlog — dùng chung cho viec-tiep-theo.mjs và kiem-nhat-quan.mjs.
// Nguồn: du-an/trang-thai.json (máy đọc) và docs/22-backlog-trien-khai.md mục 1 (bảng tổng).
import fs from 'node:fs';
import path from 'node:path';

export const TRANG_THAI_TASK = new Set(['READY', 'IN_PROGRESS', 'REVIEW', 'BLOCKED', 'DONE', 'DEFERRED']);

/** Tên giai đoạn trong tiêu đề bảng backlog, theo mã trong trang-thai.json. */
export const TEN_GIAI_DOAN = { CHUAN_BI: 'Chuẩn bị', MOC_A: 'Mốc A', MOC_B: 'Mốc B', MOC_C: 'Mốc C', SAU: 'Sau' };

export function docTrangThai(root = process.cwd()) {
  return JSON.parse(fs.readFileSync(path.join(root, 'du-an', 'trang-thai.json'), 'utf8'));
}

/**
 * Bảng tổng của backlog: | Task | Tên | Ai | Phụ thuộc | Chặn | Trạng thái |
 * Dòng tiêu đề giai đoạn: | **Mốc A · Chạy được** | … |
 */
export function docBacklog(root = process.cwd()) {
  const full = fs.readFileSync(path.join(root, 'docs', '22-backlog-trien-khai.md'), 'utf8');
  // Chỉ đọc mục 1 "Bảng tổng": các bảng khác (mốc, user story) có dòng in đậm dễ bị hiểu nhầm là tiêu đề giai đoạn
  const text = full.split(/^## 1\. Bảng tổng/m)[1]?.split(/^## /m)[0] ?? '';
  const tasks = [];
  const giaiDoanLa = [];
  let phase = null;
  for (const rawLine of text.split(/\r?\n/)) {
    const line = rawLine.trim();
    const phaseMatch = line.match(/^\| \*\*([^*·]+?) · [^*]+\*\* \|/);
    if (phaseMatch) {
      phase = phaseMatch[1].trim();
      if (!Object.values(TEN_GIAI_DOAN).includes(phase)) giaiDoanLa.push(phase);
      continue;
    }
    const m = line.match(/^\| (T-\d+) \| ([^|]+) \| ([^|]+) \| ([^|]+) \| ([^|]+) \| ([^|]+) \|$/);
    if (!m) continue;
    const [, id, ten, ai, phuThuoc, chan, trangThai] = m.map(v => v.trim());
    tasks.push({ id, ten, ai, phuThuoc, chan, trangThai, phase });
  }
  return { tasks, giaiDoanLa };
}

export function phuThuocCua(raw) {
  if (!raw || raw === '—') return [];
  const result = new Set();
  for (const range of raw.matchAll(/T-(\d+)\.\.T-(\d+)/g)) {
    for (let n = Number(range[1]); n <= Number(range[2]); n++) result.add(`T-${String(n).padStart(3, '0')}`);
  }
  for (const single of raw.matchAll(/T-(\d+)/g)) result.add(`T-${String(Number(single[1])).padStart(3, '0')}`);
  return [...result];
}

export const cauHoiTrong = raw => (!raw || raw === '—') ? [] : [...raw.matchAll(/Q-\d+/g)].map(m => m[0]);

/** Số quyết định (D-xxx) xuất hiện ở đầu hàng của bảng trong docs/01 nhiều hơn một lần. Hai PR cùng lấy một số → trùng. */
export function maQuyetDinhTrung(nhatKy) {
  const dem = new Map();
  for (const m of nhatKy.matchAll(/^\| (D-\d+) \|/gm)) dem.set(m[1], (dem.get(m[1]) ?? 0) + 1);
  return [...dem].filter(([, n]) => n > 1).map(([ma]) => ma);
}
