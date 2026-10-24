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
import { TODO } from './lib/suy-ra.mjs';

const KIEM = process.argv.includes('--kiem');
const TAXONOMY = 'taxonomy/tu-vung-kiem-soat.yaml';
const REPO = 'truonganhquyen/ca-dao';
const DAU_TEP = 'TỆP SINH TỰ ĐỘNG bởi scripts/sinh-tu-schema.mjs — không sửa tay.\n'
  + 'Sửa scripts/lib/schema.mjs hoặc taxonomy/tu-vung-kiem-soat.yaml rồi chạy `npm run sinh:schema`.';

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
  dong.push('} as const;', '', 'export type NhomTuVung = keyof typeof TU_VUNG;',
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
  // Thiếu trường hoặc để trống (YAML `truong:` → null) chỉ hợp lệ khi không bắt buộc; validator đã kiểm trước build.
  return spec.req === true ? z : `${z}.nullish()`;
}

function sinhZod() {
  const dong = [
    `// ${DAU_TEP.replace(/\n/g, '\n// ')}`,
    '// Chỉ mô tả hình dạng để có kiểu trong mã Astro; quy tắc nghiệp vụ R01–R22 nằm ở scripts/kiem-tra-du-lieu.mjs (D-019).',
    "import { z } from 'astro/zod';",
    "import { MA_TU_VUNG } from './tu-vung';",
    '',
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
// Nhãn tiếng Việt, ví dụ trong từng ô, thứ tự trường và bộ "Thêm nhanh" thuộc T-037.
function truongCms(name, spec) {
  const f = { name, label: name };
  const batBuoc = spec.req === true && !(spec.t === 'list' && !spec.min);
  f.required = batBuoc;
  switch (spec.t) {
    case 'str':
      if (spec.enumOf) Object.assign(f, { widget: 'select', options: TU_VUNG[spec.enumOf].map(x => ({ label: x.nhan, value: x.ma })) });
      else if (spec.ref) Object.assign(f, quanHe(spec.ref));
      else f.widget = spec.dai ? 'text' : 'string';
      if (spec.pattern) f.pattern = [spec.pattern.source, 'Sai định dạng'];
      break;
    case 'int':
      Object.assign(f, { widget: 'number', value_type: 'int' });
      if (spec.minVal !== undefined) f.min = spec.minVal;
      if (spec.maxVal !== undefined) f.max = spec.maxVal;
      break;
    case 'bool': f.widget = 'boolean'; break;
    case 'date': Object.assign(f, { widget: 'datetime', format: 'YYYY-MM-DD', time_format: false }); break;
    case 'obj': Object.assign(f, { widget: 'object', fields: Object.entries(spec.fields).map(([k, s]) => truongCms(k, s)) }); break;
    case 'list': {
      const con = spec.of;
      if (con.t === 'str' && con.enumOf) Object.assign(f, { widget: 'select', multiple: true, options: TU_VUNG[con.enumOf].map(x => ({ label: x.nhan, value: x.ma })) });
      else if (con.t === 'str' && con.ref) Object.assign(f, quanHe(con.ref), { multiple: true });
      else if (con.t === 'obj') Object.assign(f, { widget: 'list', fields: Object.entries(con.fields).map(([k, s]) => truongCms(k, s)) });
      else Object.assign(f, { widget: 'list', field: truongCms('muc', { ...con, req: true }) });
      if (spec.min) f.min = spec.min;
      if (spec.max) f.max = spec.max;
      // Danh sách bắt buộc nhưng được rỗng (ví dụ nguồn của dị bản lúc nháp): luôn ghi ra, kể cả []
      if (spec.req === true && !spec.min) f.default = [];
      break;
    }
    default: throw new Error(`kiểu lạ: ${spec.t}`);
  }
  return f;
}

function quanHe(coll) {
  const hienThi = TRUONG_HIEN_THI[coll];
  return { widget: 'relation', collection: coll, value_field: 'id', search_fields: [...new Set(['id', hienThi])], display_fields: [hienThi] };
}

function sinhCms() {
  const cauHinh = {
    backend: { name: 'github', repo: REPO, branch: 'main' },
    // Sveltia bắt buộc có thư mục tệp; dự án không đăng ảnh (D-046) nên thư mục này để trống.
    media_folder: 'public/tep',
    collections: Object.entries(SCHEMA).map(([coll, spec]) => ({
      name: coll,
      label: coll,
      folder: `content/${coll}`,
      extension: 'yaml',
      format: 'yaml',
      create: true,
      identifier_field: 'id',
      slug: '{{fields.id}}',
      summary: `{{fields.id}} · {{fields.${TRUONG_HIEN_THI[coll]}}}`,
      fields: Object.entries(spec.fields).map(([k, s]) => truongCms(k, s)),
    })),
  };
  return `# ${DAU_TEP.replace(/\n/g, '\n# ')}\n` + stringify(cauHinh, { lineWidth: 0 });
}

// ---------- Ghi hoặc kiểm ----------
const TEP = {
  'src/sinh-ra/tu-vung.ts': sinhTuVung(),
  'src/sinh-ra/schema.ts': sinhZod(),
  'public/admin/config.yml': sinhCms(),
};

let lech = 0;
for (const [tep, noiDung] of Object.entries(TEP)) {
  const hienCo = fs.existsSync(tep) ? fs.readFileSync(tep, 'utf8') : null;
  if (KIEM) {
    if (hienCo !== noiDung) { lech++; console.log(`LỖI      [sinh] ${tep} lệch với scripts/lib/schema.mjs / ${TAXONOMY} — chạy \`npm run sinh:schema\` rồi commit.`); }
  } else if (hienCo !== noiDung) {
    fs.mkdirSync(path.dirname(tep), { recursive: true });
    fs.writeFileSync(tep, noiDung);
    console.log(`Đã ghi ${tep}`);
  }
}
if (KIEM) console.log(`Kiểm tệp sinh: ${Object.keys(TEP).length} tệp — ${lech} lệch.`);
process.exit(lech ? 1 : 0);
