import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const state = JSON.parse(fs.readFileSync(path.join(root, 'du-an', 'trang-thai.json'), 'utf8'));
const text = fs.readFileSync(path.join(root, 'docs', '22-backlog-trien-khai.md'), 'utf8');

const phaseMap = {
  GD0: 'GĐ0',
  'GD0.5': 'GĐ0.5',
  GD0_5: 'GĐ0.5',
  GD1: 'GĐ1',
  GD2: 'GĐ2',
  GD3: 'GĐ3'
};
const wantedPhase = phaseMap[state.giai_doan];
if (!wantedPhase) {
  console.error(`Không nhận diện được giai đoạn: ${state.giai_doan}`);
  process.exit(1);
}

let currentPhase = null;
const tasks = [];
for (const rawLine of text.split(/\r?\n/)) {
  const line = rawLine.trim();
  const phaseMatch = line.match(/^\| \*\*(GĐ(?:0\.5|0|1|2|3) ·[^*]+)\*\* \|/);
  if (phaseMatch) {
    currentPhase = phaseMatch[1].split(' · ')[0];
    continue;
  }
  const match = line.match(/^\| (T-\d+) \| ([^|]+) \| ([^|]+) \| ([^|]+) \| ([^|]+) \| ([^|]+) \| ([^|]+) \|$/);
  if (!match) continue;
  const [, id, ten, ai, lop, phuThuoc, chan, trangThai] = match.map(v => v.trim());
  tasks.push({ id, ten, ai, lop, phuThuoc, chan, trangThai, phase: currentPhase });
}

const byId = new Map(tasks.map(t => [t.id, t]));
const openQuestions = new Set(state.quyet_dinh_dang_mo ?? []);

function taskNumber(id) {
  return Number(id.replace('T-', ''));
}

function expandDependencies(raw) {
  if (!raw || raw === '—') return [];
  const result = new Set();
  for (const range of raw.matchAll(/T-(\d+)\.\.T-(\d+)/g)) {
    const a = Number(range[1]);
    const b = Number(range[2]);
    for (let n = a; n <= b; n++) result.add(`T-${String(n).padStart(3, '0')}`);
  }
  for (const single of raw.matchAll(/T-(\d+)/g)) {
    result.add(`T-${String(Number(single[1])).padStart(3, '0')}`);
  }
  return [...result];
}

function unresolvedBlockers(raw) {
  if (!raw || raw === '—') return [];
  return [...raw.matchAll(/Q-\d+/g)].map(m => m[0]).filter(q => openQuestions.has(q));
}

function inferredReady(task) {
  if (!['READY', 'BLOCKED'].includes(task.trangThai)) return false;
  if (!/A/.test(task.ai)) return false;
  if (unresolvedBlockers(task.chan).length) return false;
  const deps = expandDependencies(task.phuThuoc);
  return deps.every(id => byId.get(id)?.trangThai === 'DONE');
}

const rank = { Core: 0, '0.5': 1, P1: 2, P2: 3, 'Cổng': 4 };
const ready = tasks
  .filter(t => t.phase === wantedPhase)
  .filter(inferredReady)
  .sort((a, b) => (rank[a.lop] ?? 99) - (rank[b.lop] ?? 99) || taskNumber(a.id) - taskNumber(b.id));

if (!ready.length) {
  console.log('KHÔNG CÓ CÔNG VIỆC KỸ THUẬT SẴN SÀNG');
  console.log('');
  console.log(`Giai đoạn hiện tại: ${state.giai_doan}`);
  console.log('Kiểm tra task dành cho người sáng lập, dependency và Human Gate trong backlog.');
  process.exit(2);
}

const task = ready[0];
console.log('CÔNG VIỆC KỸ THUẬT TIẾP THEO');
console.log('');
console.log(`${task.id} · ${task.ten}`);
console.log(`Giai đoạn: ${state.giai_doan}`);
console.log(`Lớp: ${task.lop}`);
console.log(`Ai làm: ${task.ai}`);
console.log(`Phụ thuộc: ${task.phuThuoc}`);
console.log(`Chặn: ${task.chan}`);
console.log(`Trạng thái trong bảng: ${task.trangThai}`);
console.log('Trạng thái suy ra: READY');
console.log('');
console.log(`Lý do: dependency đã đủ và không có Q-xxx đang mở chặn task trong ${state.giai_doan}.`);
console.log('Đọc chi tiết task và tiêu chí chấp nhận trong docs/22-backlog-trien-khai.md trước khi sửa.');
