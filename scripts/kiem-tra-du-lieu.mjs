#!/usr/bin/env node
// Kiểm tra dữ liệu — Kho tàng Ca dao Tục ngữ Việt Nam (schema 2.0, hướng v2)
// Đặc tả: docs/10-dac-ta-du-lieu.md, mục 9 "Quy tắc kiểm tra" (mã R01–R22).
// Hai tầng (D-043):
//   LỖI  — chặn build: lỗi kỹ thuật hoặc phạm lằn ranh (D-040). Thoát mã 1, bản cũ trên mạng vẫn chạy.
//   NHẮC — không chặn: phần nên bổ sung; hiện trong báo cáo sức khỏe / Bàn biên tập.
//
// Dùng:  node scripts/kiem-tra-du-lieu.mjs [--content content] [--taxonomy taxonomy/tu-vung-kiem-soat.yaml] [--suc-khoe] [--json]
//        --suc-khoe: in thêm báo cáo sức khỏe dữ liệu (luôn thoát mã 0). --json: báo cáo dạng JSON.
// Cần:   npm i yaml
//
// Quy ước khi sửa tệp này: mỗi quy tắc mới phải có mã Rxx, được ghi trong docs/10 mục 9,
// và có ít nhất một fixture trong tests/fixtures/.

import fs from 'node:fs';
import path from 'node:path';
import { parse } from 'yaml';
import { phanGiaiNeo, khopMucTu, tachDong } from './lib/neo-chu-giai.mjs';
import {
  TODO, AI_DRAFT, TRANG_THAI_CONG_KHAI, NGUONG_TRANG_TINH_HUONG, SO_TU_TRICH_TOI_DA, QUYEN_DUNG_DUOC,
  tinhNhan, coNguonGoc, coGiaTriRieng,
} from './lib/suy-ra.mjs';

// ---------- Tham số ----------
const args = process.argv.slice(2);
const opt = (name, def) => { const i = args.indexOf(name); return i >= 0 ? args[i + 1] : def; };
const CONTENT = opt('--content', 'content');
const TAXONOMY = opt('--taxonomy', 'taxonomy/tu-vung-kiem-soat.yaml');
const SUC_KHOE = args.includes('--suc-khoe');
const JSON_OUT = args.includes('--json');

// ---------- Báo cáo ----------
const errors = [];
const reminders = [];
const err = (rule, file, msg) => errors.push(`LỖI      [${rule}] ${file}: ${msg}`);
const nhac = (rule, file, msg) => reminders.push(`NHẮC     [${rule}] ${file}: ${msg}`);

// ---------- Hằng ----------
const LEVEL = { nhap: 0, an: 0, [TRANG_THAI_CONG_KHAI]: 1 };  // 'an' (ẩn) không lên trang nên được nới như nháp
const PUBLIC = 1;                // từ mức này trở lên áp dụng quy tắc "công khai"
const SLUG = /^[a-z0-9]+(-[a-z0-9]+)*$/;
const DATE = /^\d{4}-\d{2}-\d{2}$/;

// ---------- Đọc tệp ----------
function readYaml(file) {
  let raw;
  try { raw = fs.readFileSync(file, 'utf8'); } catch (e) { err('R01', file, `không đọc được (${e.message})`); return null; }
  if (raw !== raw.normalize('NFC')) err('R01', file, 'văn bản không ở dạng Unicode dựng sẵn (NFC). Chuyển về NFC trước khi lưu.');
  // Ký tự Latin-1 hiếm gặp trong tiếng Việt chuẩn: dấu hiệu dữ liệu mã cũ TCVN3/VNI bị chuyển sai.
  const legacy = raw.match(/[¡-¿×÷]/g);
  if (legacy) nhac('R01', file, `có ký tự nghi là mã cũ TCVN3/VNI: ${[...new Set(legacy)].join(' ')}`);
  try { return parse(raw); } catch (e) { err('R01', file, `YAML không hợp lệ: ${e.message.split('\n')[0]}`); return null; }
}

// ---------- Từ vựng kiểm soát ----------
const taxRaw = readYaml(TAXONOMY) || {};
const TAX = {};
for (const [group, items] of Object.entries(taxRaw)) {
  TAX[group] = new Set();
  const seen = new Set();
  for (const it of items || []) {
    if (!/^[a-z0-9_]+$/.test(it.ma) && !(group === 'xac_thuc' && /^[A-D]$/.test(it.ma))) {
      err('R15', TAXONOMY, `mã "${it.ma}" trong nhóm ${group} sai quy ước (chỉ chữ thường không dấu, số, gạch dưới)`);
    }
    if (seen.has(it.ma)) err('R15', TAXONOMY, `mã "${it.ma}" lặp trong nhóm ${group}`);
    seen.add(it.ma);
    if (!it.ngung_dung) TAX[group].add(it.ma);
  }
}
const PREFIX_TO_GENRE = Object.fromEntries((taxRaw.the_loai || []).map(x => [x.tien_to_id, x.ma]));
const PRINT_SOURCES = new Set((taxRaw.loai_nguon || []).filter(x => x.in_an).map(x => x.ma));
const FIELD_SOURCES = new Set((taxRaw.loai_nguon || []).filter(x => x.dien_da).map(x => x.ma));

// ---------- Ngôn ngữ mô tả schema ----------
const str = (o = {}) => ({ t: 'str', ...o });
const int = (o = {}) => ({ t: 'int', ...o });
const bool = (o = {}) => ({ t: 'bool', ...o });
const date = (o = {}) => ({ t: 'date', ...o });
const list = (of, o = {}) => ({ t: 'list', of, ...o });
const obj = (fields, o = {}) => ({ t: 'obj', fields, ...o });
// Tùy chọn: req: true (thiếu là LỖI) | 'pub' (nên có khi công khai, thiếu là NHẮC R10) · enumOf · pattern · maxWords · maxLen
//           ref (tên bộ sưu tập) · min · minPub (NHẮC R10) · max · num (str chấp nhận số) · todoOk (int/date chấp nhận CAN_BO_SUNG)

const words = s => s.trim().split(/\s+/).filter(Boolean).length;
const isObj = v => v !== null && typeof v === 'object' && !Array.isArray(v);

function check(v, spec, ctx, where) {
  const missing = v === undefined || v === null || v === '';
  if (missing) {
    if (spec.req === true) err('R02', ctx.file, `thiếu trường bắt buộc "${where}"`);
    else if (spec.req === 'pub' && ctx.level >= PUBLIC) nhac('R10', ctx.file, `nên có "${where}" khi công khai`);
    return;
  }
  switch (spec.t) {
    case 'str': {
      if (typeof v === 'number' && spec.num) v = String(v);
      if (typeof v !== 'string') return err('R02', ctx.file, `"${where}" phải là chuỗi`);
      if (spec.pattern && v !== TODO && !spec.pattern.test(v)) err('R02', ctx.file, `"${where}" = "${v}" sai định dạng`);
      if (spec.maxLen && v.length > spec.maxLen) err('R02', ctx.file, `"${where}" dài quá ${spec.maxLen} ký tự`);
      if (spec.enumOf && !(TAX[spec.enumOf] || new Set()).has(v)) err('R02', ctx.file, `"${where}" = "${v}" không có trong từ vựng "${spec.enumOf}" (hoặc đã ngừng dùng)`);
      if (spec.maxWords && words(v) > spec.maxWords) err('R07', ctx.file, `"${where}" có ${words(v)} từ, tối đa ${spec.maxWords}`);
      if (spec.ref && v !== TODO) ctx.refs.push({ coll: spec.ref, id: v, where });
      return;
    }
    case 'int':
      if (spec.todoOk && v === TODO) return;
      if (!Number.isInteger(v)) err('R02', ctx.file, `"${where}" phải là số nguyên`);
      else if ((spec.minVal !== undefined && v < spec.minVal) || (spec.maxVal !== undefined && v > spec.maxVal)) err('R02', ctx.file, `"${where}" = ${v} ngoài khoảng cho phép`);
      return;
    case 'bool':
      if (typeof v !== 'boolean') err('R02', ctx.file, `"${where}" phải là true/false`);
      return;
    case 'date':
      if (spec.todoOk && v === TODO) return;
      if (typeof v !== 'string' || !DATE.test(v)) err('R02', ctx.file, `"${where}" phải có dạng YYYY-MM-DD (đặt trong ngoặc kép)`);
      return;
    case 'list': {
      if (!Array.isArray(v)) return err('R02', ctx.file, `"${where}" phải là danh sách`);
      if (spec.min !== undefined && v.length < spec.min) err('R02', ctx.file, `"${where}" cần ít nhất ${spec.min} mục`);
      if (spec.minPub !== undefined && ctx.level >= PUBLIC && v.length < spec.minPub) nhac('R10', ctx.file, `"${where}" nên có ít nhất ${spec.minPub} mục khi công khai`);
      if (spec.max !== undefined && v.length > spec.max) err('R02', ctx.file, `"${where}" tối đa ${spec.max} mục`);
      v.forEach((item, i) => check(item, spec.of, ctx, `${where}[${i}]`));
      return;
    }
    case 'obj': {
      if (!isObj(v)) return err('R02', ctx.file, `"${where}" phải là một đối tượng`);
      for (const k of Object.keys(v)) if (!(k in spec.fields)) err('R16', ctx.file, `trường lạ "${where ? where + '.' : ''}${k}" (không có trong đặc tả)`);
      for (const [k, s] of Object.entries(spec.fields)) check(v[k], s, ctx, where ? `${where}.${k}` : k);
      return;
    }
  }
}

// ---------- Schema các bộ sưu tập ----------
// Tham chiếu nguồn: sách dùng `trang`; trang web dùng `url` + `ngay_truy_cap` (+ `ban_luu_tru`, R22).
// `cach_dung` chỉ có nghĩa với chú thích / diễn giải: tham_khao (đã viết lại) | trich_nguyen_van (R20).
const NGUON_REF = obj({
  ref: str({ req: true, ref: 'nguon' }),
  trang: str({ num: true }),
  url: str({ pattern: /^https?:\/\// }),
  ngay_truy_cap: date(),
  ban_luu_tru: str({ pattern: /^https?:\/\// }),
  cach_dung: str({ enumOf: 'cach_dung' }),
  ghi_chu: str(),
});

const DI_BAN = obj({
  id: str({ req: true, pattern: /^v\d+$/ }),
  loi: str({ req: true }),
  vung: list(str({ enumOf: 'vung' }), { req: true, min: 1 }),
  dia_danh: str(),
  nguon: list(NGUON_REF, { req: true }),
  nguoi_ke: obj({ the_he: str(), quan_he: str() }),
  dong_gop: str({ pattern: /^sub-\d{4}-\d{4}$/ }),
  ghi_chu: str(),
});

const CHU_GIAI = obj({
  di_ban: str({ req: true }),
  dong: int({ minVal: 1 }),          // chỉ khai khi cụm từ lặp ở nhiều dòng
  cum_tu: str({ req: true }),        // gõ đúng như trong lời; máy tự tìm vị trí
  lan: int({ minVal: 1 }),           // chỉ khai khi cụm từ lặp trong phạm vi đã chọn
  muc_tu: str({ req: true, ref: 'muc-tu' }),
  nghia_tai_cho: str({ maxWords: 40 }),
});

const SCHEMA = {
  'tac-pham': obj({
    id: str({ req: true }),
    slug: str({ req: true, pattern: SLUG, maxLen: 60 }),
    the_loai: str({ req: true, enumOf: 'the_loai' }),
    the_tho: str({ enumOf: 'the_tho' }),
    tieu_de: str({ req: true }),
    ban_dai_dien: str({ req: true }),
    ly_do_chon: str({ req: 'pub' }),
    di_ban: list(DI_BAN, { req: true, min: 1 }),
    chu_giai: list(CHU_GIAI),
    nghia_ngan: str({ req: 'pub', maxWords: 40 }),
    dien_giai: list(obj({
      goc_nhin: str({ req: true }),
      noi_dung: str({ req: true }),
      nguon: list(NGUON_REF, { req: true, minPub: 1 }),
    }), { req: 'pub', minPub: 1 }),
    nghia_den: str(),
    nghia_bong: str(),
    linh_vuc: list(str({ enumOf: 'linh_vuc' })),
    goc_kiem_chung: obj({ ket_luan: str({ req: true, enumOf: 'ket_luan_kiem_chung' }), noi_dung: str(), nguon: list(NGUON_REF) }),
    am_luat: obj({ nhip: str(), van: list(str()), ghi_chu: str() }),
    dien_xuong: obj({ hinh_thuc: list(str({ enumOf: 'hinh_thuc_dien_xuong' })), nguoi_the_hien: str(), boi_canh: str() }),
    mo_ta_tinh_huong: list(str(), { req: 'pub', minPub: 2 }),
    cam_xuc: list(str({ enumOf: 'cam_xuc' }), { max: 5 }),
    chu_de: list(str({ enumOf: 'chu_de' }), { req: 'pub', minPub: 1, max: 3 }),
    mo_tip: list(str({ ref: 'mo-tip' })),
    lien_quan: list(str({ ref: 'tac-pham' })),
    sgk: list(obj({ bo_sach: str({ req: true, enumOf: 'sgk_bo_sach' }), lop: int({ req: true, minVal: 1, maxVal: 12 }), bai: str() })),
    canh_bao_noi_dung: str(),
    xac_thuc_dan_gian: str({ enumOf: 'xac_thuc_dan_gian' }),
    can_cu_xac_thuc_dan_gian: str(),
    tranh_luan: bool(),
    ghi_chu_tranh_luan: str(),
    nhap_ai_da_duyet: bool(),          // người biên soạn đã đọc và đồng ý phần nháp AI (D-042)
    trang_thai: str({ req: true, enumOf: 'trang_thai' }),
    bien_soan: obj({
      nguoi: str({ req: true, ref: 'nguoi-dong-gop' }),
      ngay_tao: date({ req: true }),
    }, { req: true }),
  }),
  'muc-tu': obj({
    id: str({ req: true }),
    tu: str({ req: true }),
    bien_the: list(str()),
    loai: str({ req: true, enumOf: 'loai_muc_tu' }),
    vung: list(str({ enumOf: 'vung' })),
    giai_nghia: str({ req: true, maxWords: 40 }),
    giai_nghia_day_du: str(),
    nguon: list(NGUON_REF, { req: true }),
    xem_them: list(str({ ref: 'muc-tu' })),
    nhap_ai_da_duyet: bool(),
  }),
  'mo-tip': obj({ id: str({ req: true }), ten: str({ req: true }), mo_ta: str({ req: true }), nguon: list(NGUON_REF), nhap_ai_da_duyet: bool() }),
  'nguon': obj({
    id: str({ req: true }),
    loai: str({ req: true, enumOf: 'loai_nguon' }),
    tac_gia: list(str(), { req: true, min: 1 }),
    ten: str({ req: true }),
    nxb: str(), nam: int({ todoOk: true }), noi_xb: str(), tap: str({ num: true }), tai_ban: str({ num: true }),
    isbn: str(), url: str({ pattern: /^https?:\/\// }),
    ban_quyen: str({ req: true, enumOf: 'ban_quyen' }),
    da_xac_minh_ban_in: bool({ req: true }),
    ghi_chu: str(),
  }),
  'tinh-huong': obj({
    id: str({ req: true }),
    slug: str({ req: true, pattern: SLUG, maxLen: 60 }),
    tieu_de: str({ req: true }),
    nhom: str({ req: true, enumOf: 'nhom_tinh_huong' }),
    mo_dau: str({ req: true }),
    tac_pham: list(obj({ ref: str({ req: true, ref: 'tac-pham' }), hop_khi: str({ req: true }) }), { req: true }),
    tu_khoa: list(str()),
    nhap_ai_da_duyet: bool(),
  }),
  'ghi-am': obj({
    id: str({ req: true }),
    tac_pham: str({ req: true, ref: 'tac-pham' }),
    di_ban: str({ req: true }),
    loai: str({ req: true, enumOf: 'loai_ghi_am' }),
    nguoi_the_hien: obj({
      kieu_hien_thi: str({ req: true, enumOf: 'kieu_hien_thi' }),
      ten_hien_thi: str(), the_he: str(), vung: str({ enumOf: 'vung' }), dia_danh: str(),
    }, { req: true }),
    nguoi_ghi: str({ ref: 'nguoi-dong-gop' }),
    ngay_ghi: date({ todoOk: true }),
    dia_diem: str(),
    tep: obj({ url_phat: str({ req: true }), url_luu_tru: str({ req: true }), thoi_luong: int() }, { req: true }),
    giay_phep: str({ req: true, enumOf: 'giay_phep' }),
    ma_dong_y: str({ req: true }),
    ban_chep: str(),
  }),
  'nguoi-dong-gop': obj({
    id: str({ req: true }),
    ten_hien_thi: str({ req: true }),
    kieu_hien_thi: str({ req: true, enumOf: 'kieu_hien_thi' }),
    vai_tro: list(str({ enumOf: 'vai_tro' }), { req: true, min: 1 }),
    gioi_thieu: str(),
  }),
};

const ID_PATTERN = {
  'tac-pham': /^(cd|tn|tng|dd|ve|cdo)-\d{4}$/,
  'muc-tu': /^lx-[a-z0-9]+(-[a-z0-9]+)*$/,
  'mo-tip': /^mt-[a-z0-9]+(-[a-z0-9]+)*$/,
  'nguon': /^src-[a-z0-9]+(-[a-z0-9]+)*$/,
  'tinh-huong': /^sit-[a-z0-9]+(-[a-z0-9]+)*$/,
  'ghi-am': /^au-\d{4}$/,
  'nguoi-dong-gop': /^ctb-\d{4}$/,
};

// ---------- Nạp dữ liệu ----------
const DB = {};
for (const coll of Object.keys(SCHEMA)) {
  DB[coll] = new Map();
  const dir = path.join(CONTENT, coll);
  if (!fs.existsSync(dir)) continue;
  for (const f of fs.readdirSync(dir).filter(f => f.endsWith('.yaml')).sort()) {
    const file = path.join(dir, f);
    const data = readYaml(file);
    if (!isObj(data)) { if (data !== null) err('R01', file, 'tệp phải chứa một đối tượng YAML'); continue; }
    const stem = f.replace(/\.yaml$/, '');
    if (data.id !== stem) err('R03', file, `id "${data.id}" không khớp tên tệp "${stem}"`);
    if (!ID_PATTERN[coll].test(String(data.id))) err('R03', file, `id "${data.id}" sai quy ước mã của "${coll}"`);
    if (DB[coll].has(data.id)) err('R03', file, `id "${data.id}" bị trùng`);
    DB[coll].set(data.id, { data, file });
  }
}

// Mức của từng tác phẩm (dùng cho quy tắc theo trạng thái)
const levelOf = w => LEVEL[w.trang_thai] ?? 0;
const published = id => DB['tac-pham'].get(id)?.data.trang_thai === TRANG_THAI_CONG_KHAI;

// ---------- Tiện ích ----------
function findMarkers(v, token, where = '', out = []) {
  if (typeof v === 'string') { if (v.includes(token)) out.push(where || '(gốc)'); }
  else if (Array.isArray(v)) v.forEach((x, i) => findMarkers(x, token, `${where}[${i}]`, out));
  else if (isObj(v)) for (const [k, x] of Object.entries(v)) findMarkers(x, token, where ? `${where}.${k}` : k, out);
  return out;
}
/** Mọi tham chiếu nguồn (đối tượng có `ref`) trong một tệp, kèm vị trí. */
function findSourceRefs(v, where = '', out = []) {
  if (Array.isArray(v)) v.forEach((x, i) => findSourceRefs(x, `${where}[${i}]`, out));
  else if (isObj(v)) {
    if (typeof v.ref === 'string' && v.ref.startsWith('src-')) out.push({ n: v, where });
    for (const [k, x] of Object.entries(v)) if (isObj(x) || Array.isArray(x)) findSourceRefs(x, where ? `${where}.${k}` : k, out);
  }
  return out;
}
const linesOf = tachDong;
const hasRecording = (workId, variantId) => [...DB['ghi-am'].values()].some(({ data: a }) => a.tac_pham === workId && a.di_ban === variantId);
const nhanCtx = (workId, variantId) => ({
  layNguon: id => DB.nguon.get(id)?.data,
  laNguonIn: loai => PRINT_SOURCES.has(loai),
  laNguonDienDa: loai => FIELD_SOURCES.has(loai),
  coGhiAm: hasRecording(workId, variantId),
});

// ---------- Kiểm tra từng tệp theo schema ----------
const ALL_REFS = [];
for (const [coll, items] of Object.entries(DB)) {
  for (const { data, file } of items.values()) {
    const ctx = { file, refs: [], level: coll === 'tac-pham' ? levelOf(data) : (coll === 'ghi-am' ? PUBLIC : 0) };
    check(data, SCHEMA[coll], ctx, '');
    ALL_REFS.push(...ctx.refs.map(r => ({ ...r, file, owner: { coll, id: data.id } })));
  }
}

// R04 — tham chiếu phải tồn tại
for (const r of ALL_REFS) if (!DB[r.coll]?.has(r.id)) err('R04', r.file, `"${r.where}" trỏ tới "${r.id}" không tồn tại trong ${r.coll}/`);

// ---------- R22 — nguồn gốc của tham chiếu tới trang web; R20 — chép nguyên văn chú thích ----------
const isPublicOwner = (coll, data) => coll === 'tac-pham' ? published(data.id) : false;
for (const [coll, items] of Object.entries(DB)) {
  for (const { data, file } of items.values()) {
    for (const { n, where } of findSourceRefs(data)) {
      const s = DB.nguon.get(n.ref)?.data;
      if (s?.loai !== 'web') continue;
      if (!n.url) err('R22', file, `"${where}" trỏ tới trang web "${n.ref}" nhưng thiếu "url" của đúng trang đã lấy`);
      if (!n.ngay_truy_cap) err('R22', file, `"${where}" trỏ tới trang web "${n.ref}" nhưng thiếu "ngay_truy_cap"`);
      if (!n.ban_luu_tru && isPublicOwner(coll, data) && where.startsWith('di_ban')) nhac('R22', file, `"${where}" chưa có "ban_luu_tru" (bản lưu Wayback Machine)`);
    }
  }
}
// Chú thích / diễn giải ghi `trich_nguyen_van` từ nguồn chưa cho phép: chỉ chấp nhận định nghĩa rất ngắn.
// Áp dụng ở MỌI trạng thái: content/ nằm trong kho mã công khai, bản nháp cũng là công bố (D-046).
const trichChuaPhep = refs => (refs || []).some(n => n?.cach_dung === 'trich_nguyen_van' && !QUYEN_DUNG_DUOC.has(DB.nguon.get(n.ref)?.data?.ban_quyen));
for (const { data: m, file } of DB['muc-tu'].values()) {
  if (!trichChuaPhep(m.nguon)) continue;
  if (typeof m.giai_nghia === 'string' && words(m.giai_nghia) > SO_TU_TRICH_TOI_DA) err('R20', file, `"giai_nghia" chép nguyên văn từ nguồn chưa cho phép có ${words(m.giai_nghia)} từ (tối đa ${SO_TU_TRICH_TOI_DA}); viết lại bằng lời của dự án (cach_dung: tham_khao) hoặc chờ nguồn cho phép`);
  if (typeof m.giai_nghia_day_du === 'string' && m.giai_nghia_day_du.trim()) err('R20', file, '"giai_nghia_day_du" không được chép nguyên văn từ nguồn chưa cho phép; viết lại bằng lời của dự án');
}
for (const { data: w, file } of DB['tac-pham'].values()) {
  for (const [i, d] of (w.dien_giai || []).entries()) {
    if (isObj(d) && trichChuaPhep(d.nguon) && typeof d.noi_dung === 'string' && words(d.noi_dung) > SO_TU_TRICH_TOI_DA) {
      err('R20', file, `dien_giai[${i}].noi_dung chép nguyên văn từ nguồn chưa cho phép có ${words(d.noi_dung)} từ (tối đa ${SO_TU_TRICH_TOI_DA}); viết lại bằng lời của dự án`);
    }
  }
}

// ---------- Quy tắc riêng cho tác phẩm ----------
const slugSeen = new Map();
const situationOf = new Map(); // id tác phẩm -> [id tình huống]
for (const { data: s } of DB['tinh-huong'].values()) for (const t of s.tac_pham || []) {
  if (!situationOf.has(t.ref)) situationOf.set(t.ref, []);
  situationOf.get(t.ref).push(s.id);
}
const labelOf = new Map();     // "tác phẩm/dị bản" -> A|B|C|D (dùng cho báo cáo sức khỏe)

for (const { data: w, file } of DB['tac-pham'].values()) {
  const lv = levelOf(w);
  const pub = lv >= PUBLIC;

  // R03 — slug duy nhất; tiền tố mã khớp thể loại
  if (w.slug) { if (slugSeen.has(w.slug)) err('R03', file, `slug "${w.slug}" trùng với ${slugSeen.get(w.slug)}`); else slugSeen.set(w.slug, file); }
  const prefix = String(w.id).split('-')[0];
  if (PREFIX_TO_GENRE[prefix] && w.the_loai && PREFIX_TO_GENRE[prefix] !== w.the_loai) err('R03', file, `tiền tố "${prefix}" không khớp thể loại "${w.the_loai}"`);

  // Trường nên có theo thể loại (NHẮC khi công khai)
  if (pub && w.the_loai === 'ca_dao' && !w.the_tho) nhac('R10', file, 'ca dao nên có "the_tho"');
  if (pub && w.the_loai === 'tuc_ngu') {
    if (!w.nghia_den) nhac('R10', file, 'tục ngữ nên có "nghia_den"');
    if (!(w.linh_vuc || []).length) nhac('R10', file, 'tục ngữ nên có ít nhất 1 "linh_vuc"');
  }

  // Dị bản
  const variants = new Map();
  for (const v of w.di_ban || []) {
    if (!isObj(v)) continue;
    if (variants.has(v.id)) err('R03', file, `dị bản "${v.id}" bị trùng`);
    variants.set(v.id, v);
    if (typeof v.loi === 'string') {
      const ls = linesOf(v.loi);
      ls.forEach((l, i) => {
        if (l !== l.trim()) err('R01', file, `dị bản ${v.id}, dòng ${i + 1}: có khoảng trắng thừa đầu/cuối dòng`);
        if (l.includes('\t')) err('R01', file, `dị bản ${v.id}, dòng ${i + 1}: có ký tự tab`);
        if (!l.trim()) nhac('R01', file, `dị bản ${v.id}, dòng ${i + 1}: dòng trống giữa lời`);
      });
      // Lằn ranh (D-040): lời không bao giờ do AI viết
      if (v.loi.includes(AI_DRAFT)) err('R09', file, `dị bản ${v.id}: lời không được mang dấu ${AI_DRAFT} — lời chỉ lấy nguyên văn từ nguồn hoặc do người biên soạn nhập`);
    }
    labelOf.set(`${w.id}/${v.id}`, tinhNhan(v, nhanCtx(w.id, v.id)));
    // R18 — lời lấy từ tư liệu điền dã chưa công bố: phải được phép mới lên trang
    if (pub && !hasRecording(w.id, v.id)) {
      const field = (v.nguon || []).map(n => DB.nguon.get(n?.ref)?.data).filter(s => s && FIELD_SOURCES.has(s.loai));
      if (field.some(s => !QUYEN_DUNG_DUOC.has(s.ban_quyen))) err('R18', file, `dị bản ${v.id} dựa trên tư liệu điền dã chưa được phép công bố (ban_quyen của nguồn phải là cong_cong hoặc duoc_phep)`);
    }
    if (v.dong_gop === undefined && (v.nguon || []).some(n => DB.nguon.get(n?.ref)?.data?.loai === 'dong_gop')) nhac('R10', file, `dị bản ${v.id} dẫn nguồn đóng góp nhưng chưa có mã "dong_gop"`);
  }

  // R05 — bản tham chiếu (bản đại diện) phải là dị bản có thật
  const rep = variants.get(w.ban_dai_dien);
  if (w.ban_dai_dien && !rep) err('R05', file, `ban_dai_dien "${w.ban_dai_dien}" không phải dị bản có trong tệp`);
  // R21 — bài công khai: bản tham chiếu phải có nguồn gốc ghi rõ (D-040)
  if (pub && rep && !coNguonGoc(rep)) err('R21', file, `bản tham chiếu "${rep.id}" chưa có nguồn gốc (cần ít nhất một tham chiếu nguồn hoặc mã đóng góp) — không được công khai`);

  // R06 — neo chú giải
  for (const [i, c] of (w.chu_giai || []).entries()) {
    if (!isObj(c)) continue;
    const v = variants.get(c.di_ban);
    if (!v) { err('R06', file, `chu_giai[${i}] trỏ dị bản "${c.di_ban}" không tồn tại`); continue; }
    if (typeof v.loi !== 'string' || c.cum_tu === undefined) continue;
    const kq = phanGiaiNeo(v.loi, c);
    if (!kq.ok) { err('R06', file, `chu_giai[${i}] (${c.di_ban}): ${kq.thong_bao}`); continue; }
    const mt = DB['muc-tu'].get(c.muc_tu)?.data;
    if (mt && !khopMucTu(c.cum_tu, mt)) nhac('R06', file, `chu_giai[${i}]: cụm "${c.cum_tu}" không giống mục từ ${c.muc_tu} ("${mt.tu}") — kiểm lại có trỏ nhầm mục từ không`);
  }

  // R07 — khuyến nghị độ dài
  if (typeof w.nghia_ngan === 'string' && words(w.nghia_ngan) > 25 && words(w.nghia_ngan) <= 40) nhac('R07', file, `nghia_ngan có ${words(w.nghia_ngan)} từ; nên ≤ 25 để vừa thẻ chia sẻ`);

  // R09 — nháp AI chưa duyệt không được lên trang; CAN_BO_SUNG chỉ làm ẩn trường đó
  const aiMarks = findMarkers(w, AI_DRAFT);
  const todoMarks = findMarkers(w, TODO);
  if (pub) {
    if (aiMarks.length && w.nhap_ai_da_duyet !== true) aiMarks.forEach(m => err('R09', file, `còn ${AI_DRAFT} ở "${m}" mà chưa duyệt (đặt nhap_ai_da_duyet: true sau khi đã đọc và đồng ý)`));
    if (todoMarks.length) nhac('R10', file, `${todoMarks.length} chỗ còn ${TODO} (trường đó sẽ ẩn trên trang): ${todoMarks.join(', ')}`);
    // Tệp được tác phẩm này dùng tới cũng không được để lộ nháp AI chưa duyệt
    // (nguồn còn NHAP_AI đã bị chặn ở phần "Nguồn" bên dưới)
    const used = ALL_REFS.filter(r => r.owner.coll === 'tac-pham' && r.owner.id === w.id && ['muc-tu', 'mo-tip'].includes(r.coll));
    for (const r of [...new Map(used.map(u => [`${u.coll}/${u.id}`, u])).values()]) {
      const target = DB[r.coll].get(r.id);
      if (!target) continue;
      const m = findMarkers(target.data, AI_DRAFT);
      if (m.length && target.data.nhap_ai_da_duyet !== true) err('R09', file, `dùng ${r.coll}/${r.id} còn ${AI_DRAFT} chưa duyệt ở: ${m.join(', ')}`);
      if (r.coll === 'muc-tu' && !(target.data.nguon || []).length) nhac('R10', file, `dùng mục từ ${r.id} chưa có nguồn`);
    }
  } else if (aiMarks.length || todoMarks.length) {
    nhac('R09', file, `${aiMarks.length + todoMarks.length} chỗ còn ${TODO}/${AI_DRAFT} (bình thường ở trạng thái nháp)`);
  }

  // R10 — chưa thuộc tình huống nào: chỉ NHẮC (D-043; bài có cảnh báo/cờ không được vào tình huống, xem R11)
  const soTinhHuong = (situationOf.get(w.id) || []).length;
  if (pub && !soTinhHuong) nhac('R10', file, 'chưa thuộc tình huống nào');
  if (pub && !coGiaTriRieng(w, soTinhHuong)) nhac('R10', file, 'chưa có "giá trị riêng" (nghĩa ngắn + chú giải hoặc tình huống): trang sẽ mang noindex');

  // R13, R14 — cờ cần căn cứ
  if ((w.xac_thuc_dan_gian ?? 'binh_thuong') !== 'binh_thuong' && !w.can_cu_xac_thuc_dan_gian) err('R13', file, 'cờ xác thực dân gian khác "binh_thuong" cần "can_cu_xac_thuc_dan_gian"');
  if (w.tranh_luan === true && !w.ghi_chu_tranh_luan) err('R14', file, '"tranh_luan: true" cần "ghi_chu_tranh_luan"');
}

// ---------- R19 — nghi trùng tác phẩm ----------
// Lời giống hệt (bỏ dấu, bỏ dấu câu) ở hai tác phẩm khác nhau -> lỗi. Dòng đầu bản tham chiếu trùng -> nhắc
// (nhiều bài ca dao dùng chung dòng mở đầu, nên chỉ để người biên soạn xem lại).
const boDau = s => String(s).normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D')
  .toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();
const loiDaThay = new Map();   // lời chuẩn hóa -> "tác phẩm/dị bản"
const dongDauDaThay = new Map();
const nghiTrung = [];
for (const { data: w, file } of DB['tac-pham'].values()) {
  for (const v of w.di_ban || []) {
    if (!isObj(v) || typeof v.loi !== 'string') continue;
    const k = boDau(v.loi);
    const truoc = loiDaThay.get(k);
    if (truoc && !truoc.startsWith(`${w.id}/`)) { err('R19', file, `lời dị bản ${v.id} giống hệt ${truoc}: gộp thành dị bản của một tác phẩm, hoặc ghi rõ khác biệt`); nghiTrung.push([`${w.id}/${v.id}`, truoc]); }
    else if (!truoc) loiDaThay.set(k, `${w.id}/${v.id}`);
  }
  const rep = (w.di_ban || []).find(v => v?.id === w.ban_dai_dien);
  if (rep && typeof rep.loi === 'string') {
    const d = boDau(tachDong(rep.loi)[0] || '');
    if (d && dongDauDaThay.has(d)) { nhac('R19', file, `dòng đầu bản tham chiếu trùng với ${dongDauDaThay.get(d)} — kiểm xem có phải cùng một tác phẩm không`); nghiTrung.push([w.id, dongDauDaThay.get(d)]); }
    else if (d) dongDauDaThay.set(d, w.id);
  }
}

// ---------- Mục từ ----------
for (const { data: m, file } of DB['muc-tu'].values()) {
  if (m.loai === 'phuong_ngu' && !(m.vung || []).length) err('R02', file, 'mục từ phương ngữ cần "vung"');
}

// ---------- Nguồn ----------
for (const { data: s, file } of DB.nguon.values()) {
  if (PRINT_SOURCES.has(s.loai) && (s.nxb === undefined || s.nam === undefined)) err('R02', file, 'nguồn in cần "nxb" và "nam" (chưa biết thì ghi CAN_BO_SUNG)');
  if (s.loai === 'web' && !s.url) err('R02', file, 'nguồn loại "web" cần "url" của trang chủ');
  if (s.da_xac_minh_ban_in === true && findMarkers(s, TODO).length) err('R17', file, 'đánh dấu đã xác minh bản in nhưng còn CAN_BO_SUNG');
  if (findMarkers(s, AI_DRAFT).length) err('R09', file, `thông tin nguồn không được do AI soạn (còn ${AI_DRAFT})`);
}

// ---------- Tình huống ----------
for (const { data: s, file } of DB['tinh-huong'].values()) {
  const pub = (s.tac_pham || []).filter(t => published(t.ref));
  const flagged = (s.tac_pham || []).filter(t => (DB['tac-pham'].get(t.ref)?.data.xac_thuc_dan_gian ?? 'binh_thuong') !== 'binh_thuong');
  for (const t of flagged) err('R11', file, `${t.ref} mang cờ nghi gán nhầm/phỏng tác, không được gắn vào tình huống`);
  const warned = (s.tac_pham || []).filter(t => (DB['tac-pham'].get(t.ref)?.data.canh_bao_noi_dung || '').trim());
  for (const t of warned) err('R11', file, `${t.ref} có cảnh báo nội dung, không được gắn vào tình huống`);
  if (pub.length < NGUONG_TRANG_TINH_HUONG) nhac('R11', file, `mới có ${pub.length} tác phẩm công khai; trang tình huống chỉ được tạo khi có ≥ ${NGUONG_TRANG_TINH_HUONG}`);
  else {
    const ai = findMarkers(s, AI_DRAFT);
    if (ai.length && s.nhap_ai_da_duyet !== true) err('R09', file, `trang sẽ được tạo nhưng còn ${AI_DRAFT} chưa duyệt ở: ${ai.join(', ')}`);
    const todo = findMarkers(s, TODO);
    if (todo.length) nhac('R10', file, `trang sẽ được tạo nhưng còn ${TODO} (ẩn trên trang) ở: ${todo.join(', ')}`);
  }
}

// ---------- Ghi âm ----------
for (const { data: a, file } of DB['ghi-am'].values()) {
  const w = DB['tac-pham'].get(a.tac_pham)?.data;
  if (w && !(w.di_ban || []).some(v => v?.id === a.di_ban)) err('R12', file, `di_ban "${a.di_ban}" không có trong ${a.tac_pham}`);
  if (a.nguoi_the_hien?.kieu_hien_thi !== 'an_danh' && !a.nguoi_the_hien?.ten_hien_thi) err('R12', file, 'người thể hiện không ẩn danh thì cần "ten_hien_thi"');
}

// ---------- Báo cáo sức khỏe dữ liệu (--suc-khoe) ----------
function sucKhoe() {
  const works = [...DB['tac-pham'].values()].map(x => x.data);
  const congKhai = works.filter(w => w.trang_thai === TRANG_THAI_CONG_KHAI);
  const dem = (arr, f) => arr.reduce((m, x) => { const k = f(x); m[k] = (m[k] || 0) + 1; return m; }, {});
  const variants = works.flatMap(w => (w.di_ban || []).filter(isObj).map(v => ({ w, v })));
  const nhanThamChieu = w => labelOf.get(`${w.id}/${w.ban_dai_dien}`) ?? 'khong_ro';
  const coDau = (x, dau) => findMarkers(x, dau).length > 0;
  const maLoi = (arr, ma) => arr.filter(l => l.includes(`[${ma}]`)).length;
  const muc = [...DB['muc-tu'].values()].map(x => x.data);
  const webRefs = works.flatMap(w => findSourceRefs(w.di_ban || []).filter(({ n }) => DB.nguon.get(n.ref)?.data?.loai === 'web').map(r => ({ w, ...r })));
  return {
    ngay: new Date().toISOString().slice(0, 10),
    tac_pham: { tong: works.length, theo_trang_thai: dem(works, w => w.trang_thai ?? 'khong_ro') },
    cong_khai: {
      theo_nhan_ban_tham_chieu: dem(congKhai, nhanThamChieu),
      chua_co_gia_tri_rieng_noindex: congKhai.filter(w => !coGiaTriRieng(w, (situationOf.get(w.id) || []).length)).length,
      chua_thuoc_tinh_huong: congKhai.filter(w => !(situationOf.get(w.id) || []).length).length,
    },
    con_dau_nhap: {
      tac_pham_con_NHAP_AI_chua_duyet: works.filter(w => coDau(w, AI_DRAFT) && w.nhap_ai_da_duyet !== true).length,
      tac_pham_con_CAN_BO_SUNG: works.filter(w => coDau(w, TODO)).length,
    },
    bang_chung: { di_ban_theo_nhan: dem(variants, x => labelOf.get(`${x.w.id}/${x.v.id}`) ?? 'khong_ro') },
    no_du_lieu: {
      di_ban_chua_ro_vung: variants.filter(x => (x.v.vung || []).includes('khong_ro')).length,
      muc_tu_chua_co_nguon: muc.filter(m => !(m.nguon || []).length || coDau(m, TODO)).length,
      muc_tu_trich_nguyen_van_cho_phep: muc.filter(m => trichChuaPhep(m.nguon)).length,
      tham_chieu_web_chua_co_ban_luu_tru: webRefs.filter(({ n }) => !n.ban_luu_tru).length,
      nguon_chua_ro_ban_quyen: [...DB.nguon.values()].filter(x => x.data.ban_quyen === 'chua_ro').length,
      tinh_huong_chua_du_bai: [...DB['tinh-huong'].values()].filter(x => (x.data.tac_pham || []).filter(t => published(t.ref)).length < NGUONG_TRANG_TINH_HUONG).length,
    },
    van_de_ky_thuat: {
      neo_chu_giai_loi: maLoi(errors, 'R06'),
      nghi_trung: nghiTrung.map(([a, b]) => `${a} ~ ${b}`),
      tong_loi: errors.length,
      tong_nhac: reminders.length,
    },
  };
}

function inSucKhoe(r) {
  const dong = (nhan, gt) => console.log(`  ${nhan.padEnd(44, ' ')} ${typeof gt === 'object' ? JSON.stringify(gt) : gt}`);
  console.log(`\n=== SỨC KHỎE DỮ LIỆU · ${r.ngay} ===`);
  console.log('Tác phẩm'); dong('Tổng', r.tac_pham.tong); dong('Theo trạng thái', r.tac_pham.theo_trang_thai);
  console.log('Công khai'); dong('Theo nhãn bản tham chiếu', r.cong_khai.theo_nhan_ban_tham_chieu);
  dong('Chưa có giá trị riêng (noindex)', r.cong_khai.chua_co_gia_tri_rieng_noindex); dong('Chưa thuộc tình huống', r.cong_khai.chua_thuoc_tinh_huong);
  console.log('Còn dấu nháp'); dong('Tác phẩm còn NHAP_AI chưa duyệt', r.con_dau_nhap.tac_pham_con_NHAP_AI_chua_duyet); dong('Tác phẩm còn CAN_BO_SUNG', r.con_dau_nhap.tac_pham_con_CAN_BO_SUNG);
  console.log('Bằng chứng'); dong('Dị bản theo nhãn (tự tính)', r.bang_chung.di_ban_theo_nhan);
  const NHAN = { di_ban_chua_ro_vung: 'Dị bản chưa rõ vùng', muc_tu_chua_co_nguon: 'Mục từ chưa có nguồn / còn thiếu',
    muc_tu_trich_nguyen_van_cho_phep: 'Mục từ trích nguyên văn, chờ phép', tham_chieu_web_chua_co_ban_luu_tru: 'Tham chiếu web chưa có bản lưu trữ',
    nguon_chua_ro_ban_quyen: 'Nguồn chưa rõ bản quyền', tinh_huong_chua_du_bai: `Tình huống dưới ${NGUONG_TRANG_TINH_HUONG} bài công khai` };
  console.log('Nợ dữ liệu'); for (const [k, v] of Object.entries(r.no_du_lieu)) dong(NHAN[k] ?? k, v);
  console.log('Vấn đề kỹ thuật'); dong('Neo chú giải lỗi', r.van_de_ky_thuat.neo_chu_giai_loi); dong('Nghi trùng', r.van_de_ky_thuat.nghi_trung.length ? r.van_de_ky_thuat.nghi_trung.join('; ') : 0); dong('Lỗi / nhắc', `${r.van_de_ky_thuat.tong_loi} / ${r.van_de_ky_thuat.tong_nhac}`);
}

// ---------- Kết quả ----------
if (SUC_KHOE && JSON_OUT) { console.log(JSON.stringify(sucKhoe(), null, 2)); process.exit(0); }
const counts = Object.entries(DB).map(([k, m]) => `${k}: ${m.size}`).join(' · ');
for (const r of reminders) console.log(r);
for (const e of errors) console.log(e);
console.log(`\nĐã kiểm: ${counts}`);
console.log(`Kết quả: ${errors.length} lỗi, ${reminders.length} nhắc.`);
if (SUC_KHOE) { inSucKhoe(sucKhoe()); process.exit(0); }
// `npm run build` chạy tệp này trước `astro build`: có LỖI thì dừng, không build, không triển khai (docs/21 mục 3).
if (errors.length) console.log('Dữ liệu có lỗi nên dừng ở đây: không build, không triển khai (bản cũ trên mạng vẫn chạy). Sửa các dòng LỖI ở trên rồi lưu lại.');
process.exit(errors.length ? 1 : 0);
