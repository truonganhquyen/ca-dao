#!/usr/bin/env node
// Sinh tệp từ mô tả schema một nguồn (T-036, docs/21 mục 4):
//   src/sinh-ra/tu-vung.ts   — mã + nhãn từ vựng kiểm soát (taxonomy/tu-vung-kiem-soat.yaml), kiểu TypeScript
//   src/sinh-ra/schema.ts    — schema Zod cho 7 collection của Astro (chỉ hình dạng; quy tắc R01–R22 ở validator)
//   public/admin/config.yml  — cấu hình Sveltia CMS (D-036)
//
// Dùng:  node scripts/sinh-tu-schema.mjs          ghi lại các tệp sinh ra
//        node scripts/sinh-tu-schema.mjs --kiem   chỉ so sánh; tệp lệch với nguồn → LỖI, thoát mã 1 (một phần của `npm run kiem-tra`)
import fs from 'node:fs';
import path from 'node:path';
import { parse, stringify } from 'yaml';
import { SCHEMA, TRUONG_HIEN_THI } from './lib/schema.mjs';
import { CMS, COLLECTION, CHUNG, RIENG, THU_TU, THU_GON, MAC_DINH, THEM_NHANH } from './lib/giao-dien-cms.mjs';
import { TODO } from './lib/suy-ra.mjs';

const KIEM = process.argv.includes('--kiem');
const TAXONOMY = 'taxonomy/tu-vung-kiem-soat.yaml';
const REPO = 'truonganhquyen/ca-dao';
const loiSinh = []; // ô thiếu nhãn, thứ tự thiếu ô… — sinh lỗi thay vì lặng lẽ sinh cấu hình thiếu
const DAU_TEP = 'TỆP SINH TỰ ĐỘNG bởi scripts/sinh-tu-schema.mjs — không sửa tay.\n'
  + 'Sửa scripts/lib/schema.mjs, scripts/lib/giao-dien-cms.mjs hoặc taxonomy/tu-vung-kiem-soat.yaml rồi chạy `npm run sinh:schema`.';

// Chỉ mã còn dùng; mã `ngung_dung` không được chọn mới (dữ liệu còn dùng thì validator báo R02).
const taxRaw = parse(fs.readFileSync(TAXONOMY, 'utf8'));
const TU_VUNG = Object.fromEntries(Object.entries(taxRaw).map(([nhom, ds]) =>
  [nhom, (ds || []).filter(x => !x.ngung_dung).map(x => ({ ma: x.ma, nhan: x.nhan }))]));
const tenKieu = nhom => nhom.split('_').map(p => p[0].toUpperCase() + p.slice(1)).join('');
const q = s => JSON.stringify(s);

// ---------- 1. Từ vựng → TypeScript ----------
function sinhTuVung() {
  const dong = [`// ${DAU_TEP.replace(/\n/g, '\n// ')}`, '', 'export const TU_VUNG = {'];
  for (const [nhom, ds] of Object.entries(TU_VUNG)) {
    dong.push(`  ${nhom}: [`, ...ds.map(x => `    { ma: ${q(x.ma)}, nhan: ${q(x.nhan)} },`), '  ],');
  }
  dong.push('} as const;', '', '/** Mã còn dùng của từng nhóm, dạng bộ (tuple) cho z.enum(). */', 'export const MA_TU_VUNG = {');
  for (const [nhom, ds] of Object.entries(TU_VUNG)) dong.push(`  ${nhom}: [${ds.map(x => q(x.ma)).join(', ')}],`);
  // Cờ của loại nguồn — tinhNhan() cần biết nguồn in / tư liệu điền dã (docs/10 mục 5)
  const coNguon = co => (taxRaw.loai_nguon || []).filter(x => x[co] && !x.ngung_dung).map(x => q(x.ma)).join(', ');
  dong.push('} as const;', '', '/** Loại nguồn là nguồn in (sách, tạp chí, luận văn — kể cả bản quét). */',
    `export const LOAI_NGUON_IN: readonly string[] = [${coNguon('in_an')}];`,
    '/** Loại nguồn là tư liệu điền dã. */',
    `export const LOAI_NGUON_DIEN_DA: readonly string[] = [${coNguon('dien_da')}];`);
  dong.push('', 'export type NhomTuVung = keyof typeof TU_VUNG;',
    'export type MaTuVung<N extends NhomTuVung> = (typeof MA_TU_VUNG)[N][number];', '');
  for (const nhom of Object.keys(TU_VUNG)) dong.push(`export type ${tenKieu(nhom)} = MaTuVung<${q(nhom)}>;`);
  return dong.join('\n') + '\n';
}

// ---------- 2. Schema → Zod ----------
function zod(spec, lui) {
  const thut = '  '.repeat(lui);
  let z;
  switch (spec.t) {
    case 'str':
      z = spec.enumOf ? `z.enum(MA_TU_VUNG.${spec.enumOf})`
        : spec.num ? 'z.union([z.string(), z.number()])'
        : 'z.string()';
      break;
    case 'int':
      z = spec.todoOk ? `z.union([z.number().int(), z.literal(${q(TODO)})])` : 'z.number().int()';
      break;
    case 'bool': z = 'z.boolean()'; break;
    case 'date': z = spec.todoOk ? `z.union([ngay, z.literal(${q(TODO)})])` : 'ngay'; break;
    case 'list': z = `z.array(${zod({ ...spec.of, req: true }, lui)})`; break;
    case 'obj':
      z = `z.object({\n${Object.entries(spec.fields).map(([k, s]) => `${thut}  ${k}: ${zod(s, lui + 1)},`).join('\n')}\n${thut}})`;
      break;
    default: throw new Error(`kiểu lạ: ${spec.t}`);
  }
  // Thiếu trường, để trống (YAML `truong:` → null) hoặc chuỗi rỗng (CMS ghi "" cho ô bỏ trống) chỉ hợp lệ khi
  // không bắt buộc; validator cũng coi "" là không có. Ở đây đưa "" về null để mã Astro chỉ gặp một kiểu "không có".
  return spec.req === true ? z : `trong(${z})`;
}

function sinhZod() {
  const dong = [
    `// ${DAU_TEP.replace(/\n/g, '\n// ')}`,
    '// Chỉ mô tả hình dạng để có kiểu trong mã Astro; quy tắc nghiệp vụ R01–R22 nằm ở scripts/kiem-tra-du-lieu.mjs (D-019).',
    "import { z } from 'astro/zod';",
    "import { MA_TU_VUNG } from './tu-vung';",
    '',
    '// Ô không bắt buộc: CMS ghi "" khi bỏ trống — coi như không có (validator cũng vậy).',
    'const trong = <T extends z.ZodType>(t: T) => z.preprocess(v => (v === \x27\x27 ? null : v), t.nullish());',
    '// Ngày dạng YYYY-MM-DD. Astro đọc ngày không có ngoặc kép thành Date; đưa về chuỗi như validator thấy.',
    'const ngay = z.union([z.string(), z.date()]).transform(v => (typeof v === \'string\' ? v : v.toISOString().slice(0, 10)));',
    '',
    'export const SCHEMA_ZOD = {',
  ];
  for (const [coll, spec] of Object.entries(SCHEMA)) dong.push(`  ${q(coll)}: ${zod({ ...spec, req: true }, 1)},`);
  dong.push('};', '', 'export type TenCollection = keyof typeof SCHEMA_ZOD;',
    'export type DuLieu<C extends TenCollection> = z.infer<(typeof SCHEMA_ZOD)[C]>;');
  return dong.join('\n') + '\n';
}

// ---------- 3. Schema → cấu hình Sveltia CMS ----------
// Hình dạng (kiểu ô, bắt buộc, danh sách chọn, quan hệ) từ schema.mjs; nhãn, gợi ý, thứ tự, mặc định từ giao-dien-cms.mjs.
const luaChon = nhom => TU_VUNG[nhom].map(x => ({ label: x.nhan, value: x.ma }));

function nhanO(coll, duong, ten) {
  const n = RIENG[coll]?.[duong] ?? CHUNG[ten];
  if (!n) loiSinh.push(`${coll}: ô "${duong}" chưa có nhãn trong scripts/lib/giao-dien-cms.mjs`);
  return n ?? { nhan: ten };
}

function truongCms(coll, duong, spec) {
  const ten = duong.split('.').at(-1);
  const n = nhanO(coll, duong, ten);
  const f = { name: ten, label: n.nhan };
  if (n.goi_y) f.hint = n.goi_y;
  // Sveltia mặc định coi mọi ô là bắt buộc: luôn ghi rõ. Danh sách bắt buộc nhưng được rỗng → không bắt buộc nhập.
  f.required = spec.req === true && !(spec.t === 'list' && !spec.min);
  const conCua = o => Object.entries(o.fields).map(([k, s]) => truongCms(coll, `${duong}.${k}`, s));
  switch (spec.t) {
    case 'str':
      if (spec.enumOf) Object.assign(f, { widget: 'select', options: luaChon(spec.enumOf) });
      else if (spec.ref) Object.assign(f, quanHe(spec.ref));
      else f.widget = spec.dai ? 'text' : 'string';
      if (spec.pattern) f.pattern = [spec.pattern.source, 'Sai định dạng — xem gợi ý dưới ô'];
      break;
    case 'int':
      Object.assign(f, { widget: 'number', value_type: 'int' });
      if (spec.minVal !== undefined) f.min = spec.minVal;
      if (spec.maxVal !== undefined) f.max = spec.maxVal;
      break;
    case 'bool': f.widget = 'boolean'; break;
    case 'date': Object.assign(f, { widget: 'datetime', format: 'YYYY-MM-DD', time_format: false }); break;
    case 'obj':
      Object.assign(f, { widget: 'object', fields: conCua(spec) });
      if (THU_GON.has(`${coll}:${duong}`)) f.collapsed = true;
      break;
    case 'list': {
      const con = spec.of;
      if (con.t === 'str' && con.enumOf) Object.assign(f, { widget: 'select', multiple: true, options: luaChon(con.enumOf) });
      else if (con.t === 'str' && con.ref) Object.assign(f, quanHe(con.ref), { multiple: true });
      else if (con.t === 'obj') Object.assign(f, { widget: 'list', fields: conCua(con) });
      else Object.assign(f, { widget: 'list', field: truongCms(coll, `${duong}.muc`, { ...con, req: true }) });
      if (spec.min) f.min = spec.min;
      if (spec.max) f.max = spec.max;
      // Danh sách bắt buộc nhưng được rỗng (ví dụ nguồn của dị bản lúc nháp): luôn ghi ra, kể cả []
      if (spec.req === true && !spec.min) f.default = [];
      break;
    }
    default: throw new Error(`kiểu lạ: ${spec.t}`);
  }
  const macDinh = MAC_DINH[coll]?.[duong];
  if (macDinh !== undefined) f.default = macDinh;
  return f;
}

function quanHe(coll) {
  const hienThi = TRUONG_HIEN_THI[coll];
  return { widget: 'relation', collection: coll, value_field: 'id', search_fields: [...new Set(['id', hienThi])], display_fields: [hienThi] };
}

/** Ô cấp trên cùng theo THU_TU; thiếu hoặc thừa ô so với schema → lỗi sinh. */
function oTheoThuTu(coll, spec, thuTu) {
  const coTrongSchema = Object.keys(spec.fields);
  for (const k of coTrongSchema) if (!thuTu.includes(k)) loiSinh.push(`${coll}: ô "${k}" chưa có trong THU_TU của giao-dien-cms.mjs`);
  for (const k of thuTu) if (!coTrongSchema.includes(k)) loiSinh.push(`${coll}: THU_TU có ô lạ "${k}"`);
  return thuTu.filter(k => coTrongSchema.includes(k)).map(k => truongCms(coll, k, spec.fields[k]));
}

const chungCollection = coll => ({
  folder: `content/${coll}`, extension: 'yaml', format: 'yaml', create: true,
  identifier_field: 'id', slug: '{{fields.id}}', editor: { preview: false },
});

/** Bộ "Thêm nhanh": cùng thư mục tác phẩm, ít ô; ô ẩn điền phần bắt buộc còn lại; không liệt kê bài nào. */
function themNhanh() {
  const spec = SCHEMA['tac-pham'];
  const theoTen = (ds, ten) => ds.find(x => x.name === ten);
  const fields = THEM_NHANH.thu_tu.map(k => truongCms('tac-pham', k, spec.fields[k]));
  const diBan = theoTen(fields, 'di_ban');
  diBan.fields = [
    { name: 'id', widget: 'hidden', default: THEM_NHANH.an['di_ban.id'] },
    ...THEM_NHANH.di_ban.map(k => theoTen(diBan.fields, k)),
  ];
  const nguon = theoTen(diBan.fields, 'nguon');
  nguon.fields = THEM_NHANH.nguon.map(k => theoTen(nguon.fields, k));
  theoTen(nguon.fields, 'ngay_truy_cap').default = THEM_NHANH.mac_dinh['di_ban.nguon.ngay_truy_cap'];
  Object.assign(diBan, { min: 1, max: 1, default: [{ id: 'v1' }] });
  for (const [k, v] of Object.entries(THEM_NHANH.an)) if (!k.includes('.')) fields.push({ name: k, widget: 'hidden', default: v });
  return {
    name: THEM_NHANH.name, label: THEM_NHANH.nhan, label_singular: THEM_NHANH.mot, description: THEM_NHANH.mo_ta,
    ...chungCollection('tac-pham'),
    filter: { field: 'id', pattern: '^$' }, // id luôn có → bộ này không liệt kê bài nào, chỉ để tạo mới
    fields,
  };
}

function sinhCms() {
  const cauHinh = {
    backend: { name: 'github', repo: REPO, branch: 'main', base_url: CMS.base_url, commit_messages: CMS.commit_messages },
    site_url: CMS.site_url,
    display_url: CMS.site_url,
    media_folder: CMS.media_folder,
    collections: [
      themNhanh(),
      ...Object.entries(SCHEMA).map(([coll, spec]) => ({
        name: coll,
        label: COLLECTION[coll].nhan,
        label_singular: COLLECTION[coll].mot,
        description: COLLECTION[coll].mo_ta,
        ...chungCollection(coll),
        summary: `{{fields.${TRUONG_HIEN_THI[coll]}}} · {{fields.id}}`,
        fields: oTheoThuTu(coll, spec, THU_TU[coll]),
      })),
    ],
  };
  return `# ${DAU_TEP.replace(/\n/g, '\n# ')}\n` + stringify(cauHinh, { lineWidth: 0 });
}

// ---------- Ghi hoặc kiểm ----------
const TEP = {
  'src/sinh-ra/tu-vung.ts': sinhTuVung(),
  'src/sinh-ra/schema.ts': sinhZod(),
  'public/admin/config.yml': sinhCms(),
};

// Cấu hình chưa đủ (ô thiếu nhãn, thứ tự thiếu ô): không ghi gì, báo lỗi
if (loiSinh.length) {
  for (const l of loiSinh) console.log(`LỖI      [sinh] ${l}`);
  process.exit(1);
}

let lech = 0;
for (const [tep, noiDung] of Object.entries(TEP)) {
  const hienCo = fs.existsSync(tep) ? fs.readFileSync(tep, 'utf8') : null;
  if (KIEM) {
    if (hienCo !== noiDung) { lech++; console.log(`LỖI      [sinh] ${tep} lệch với scripts/lib/schema.mjs, giao-dien-cms.mjs hoặc ${TAXONOMY} — chạy \`npm run sinh:schema\` rồi commit.`); }
  } else if (hienCo !== noiDung) {
    fs.mkdirSync(path.dirname(tep), { recursive: true });
    fs.writeFileSync(tep, noiDung);
    console.log(`Đã ghi ${tep}`);
  }
}
if (KIEM) console.log(`Kiểm tệp sinh: ${Object.keys(TEP).length} tệp — ${lech} lệch.`);
process.exit(lech ? 1 : 0);
