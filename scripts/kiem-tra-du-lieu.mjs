#!/usr/bin/env node
// Kiểm tra dữ liệu — Kho tàng Ca dao Tục ngữ Việt Nam
// Đặc tả: docs/10-dac-ta-du-lieu.md, mục 9 "Quy tắc kiểm tra" (mã R01–R19).
// Có LỖI -> thoát mã 1 -> build dừng, bản cũ trên mạng vẫn chạy.
//
// Dùng:  node scripts/kiem-tra-du-lieu.mjs [--content content] [--taxonomy taxonomy/tu-vung-kiem-soat.yaml] [--suc-khoe] [--json]
//        --suc-khoe: in thêm báo cáo sức khỏe dữ liệu (luôn thoát mã 0). --json: báo cáo dạng JSON.
// Cần:   npm i yaml
//
// Quy ước khi sửa tệp này: mỗi quy tắc mới phải có mã Rxx, được ghi trong docs/10 mục 9,
// và có ít nhất một fixture lỗi trong tests/fixtures/.

import fs from 'node:fs';
import path from 'node:path';
import { parse } from 'yaml';
import { phanGiaiNeo, khopMucTu, tachDong } from './lib/neo-chu-giai.mjs';

// ---------- Tham số ----------
const args = process.argv.slice(2);
const opt = (name, def) => { const i = args.indexOf(name); return i >= 0 ? args[i + 1] : def; };
const CONTENT = opt('--content', 'content');
const TAXONOMY = opt('--taxonomy', 'taxonomy/tu-vung-kiem-soat.yaml');
const SUC_KHOE = args.includes('--suc-khoe');
const JSON_OUT = args.includes('--json');

// ---------- Báo cáo ----------
const errors = [];
const warnings = [];
const err = (rule, file, msg) => errors.push(`LỖI      [${rule}] ${file}: ${msg}`);
const warn = (rule, file, msg) => warnings.push(`CẢNH BÁO [${rule}] ${file}: ${msg}`);

// ---------- Hằng ----------
const TODO = 'CAN_BO_SUNG';      // thông tin còn thiếu, người biên soạn phải điền
const AI_DRAFT = 'NHAP_AI';      // văn bản do AI soạn nháp, người biên soạn phải viết lại
const LEVEL = { nhap: 0, an: 0, cho_kiem_dinh: 1, da_xuat_ban: 2 };  // 'an' (ẩn) không lên trang nên được nới như nháp
const STRICT = 1;                // từ mức này trở lên áp dụng quy tắc "xuất bản"
const SLUG = /^[a-z0-9]+(-[a-z0-9]+)*$/;
const DATE = /^\d{4}-\d{2}-\d{2}$/;

// ---------- Đọc tệp ----------
function readYaml(file) {
  let raw;
  try { raw = fs.readFileSync(file, 'utf8'); } catch (e) { err('R01', file, `không đọc được (${e.message})`); return null; }
  if (raw !== raw.normalize('NFC')) err('R01', file, 'văn bản không ở dạng Unicode dựng sẵn (NFC). Chuyển về NFC trước khi lưu.');
  // Ký tự Latin-1 hiếm gặp trong tiếng Việt chuẩn: dấu hiệu dữ liệu mã cũ TCVN3/VNI bị chuyển sai.
  const legacy = raw.match(/[\u00A1-\u00BF\u00D7\u00F7]/g);
  if (legacy) warn('R01', file, `có ký tự nghi là mã cũ TCVN3/VNI: ${[...new Set(legacy)].join(' ')}`);
  try { return parse(raw); } catch (e) { err('R01', file, `YAML không hợp lệ: ${e.message.split('\n')[0]}`); return null; }
}

// ---------- Từ vựng kiểm soát ----------
const taxRaw = readYaml(TAXONOMY) || {};
const TAX = {};
const TAX_ITEMS = {};
for (const [group, items] of Object.entries(taxRaw)) {
  TAX[group] = new Set();
  TAX_ITEMS[group] = {};
  for (const it of items || []) {
    if (!/^[a-z0-9_]+$/.test(it.ma) && !(group === 'xac_thuc' && /^[A-D]$/.test(it.ma))) {
      err('R15', TAXONOMY, `mã "${it.ma}" trong nhóm ${group} sai quy ước (chỉ chữ thường không dấu, số, gạch dưới)`);
    }
    if (TAX[group].has(it.ma)) err('R15', TAXONOMY, `mã "${it.ma}" lặp trong nhóm ${group}`);
    if (!it.ngung_dung) TAX[group].add(it.ma);
    TAX_ITEMS[group][it.ma] = it;
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
// Tùy chọn: req: true | 'pub' (bắt buộc từ mức cho_kiem_dinh) · enumOf · pattern · maxWords · maxLen
//           ref (tên bộ sưu tập) · min · minPub · max · num (str chấp nhận số) · todoOk (int/date chấp nhận CAN_BO_SUNG)

const words = s => s.trim().split(/\s+/).filter(Boolean).length;
const isObj = v => v !== null && typeof v === 'object' && !Array.isArray(v);

function check(v, spec, ctx, where) {
  const missing = v === undefined || v === null || v === '';
  if (missing) {
    if (spec.req === true || (spec.req === 'pub' && ctx.level >= STRICT)) err('R02', ctx.file, `thiếu trường bắt buộc "${where}"`);
    return;
  }
  switch (spec.t) {
    case 'str': {
      if (typeof v === 'number' && spec.num) v = String(v);
      if (typeof v !== 'string') return err('R02', ctx.file, `"${where}" phải là chuỗi`);
      if (spec.pattern && v !== TODO && !spec.pattern.test(v)) err('R02', ctx.file, `"${where}" = "${v}" sai định dạng`);
      if (spec.maxLen && v.length > spec.maxLen) err('R02', ctx.file, `"${where}" dài quá ${spec.maxLen} ký tự`);
      if (spec.enumOf && !(TAX[spec.enumOf] || new Set()).has(v)) err('R02', ctx.file, `"${where}" = "${v}" không có trong từ vựng "${spec.enumOf}"`);
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
      if (spec.minPub !== undefined && ctx.level >= STRICT && v.length < spec.minPub) err('R10', ctx.file, `"${where}" cần ít nhất ${spec.minPub} mục khi chờ kiểm định / xuất bản`);
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
const NGUON_REF = obj({ ref: str({ req: true, ref: 'nguon' }), trang: str({ num: true }), ghi_chu: str() });

const DI_BAN = obj({
  id: str({ req: true, pattern: /^v\d+$/ }),
  loi: str({ req: true }),
  vung: list(str({ enumOf: 'vung' }), { req: true, min: 1 }),
  dia_danh: str(),
  nguon: list(NGUON_REF, { req: true }),
  xac_thuc: str({ req: true, enumOf: 'xac_thuc' }),
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
    trang_thai: str({ req: true, enumOf: 'trang_thai' }),
    bien_soan: obj({
      nguoi: str({ req: true, ref: 'nguoi-dong-gop' }),
      ngay_tao: date({ req: true }),
      ngay_kiem_dinh: date({ req: 'pub' }),
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
  }),
  'mo-tip': obj({ id: str({ req: true }), ten: str({ req: true }), mo_ta: str({ req: true }), nguon: list(NGUON_REF) }),
  'nguon': obj({
    id: str({ req: true }),
    loai: str({ req: true, enumOf: 'loai_nguon' }),
    tac_gia: list(str(), { req: true, min: 1 }),
    ten: str({ req: true }),
    nxb: str(), nam: int({ todoOk: true }), noi_xb: str(), tap: str({ num: true }), tai_ban: str({ num: true }),
    isbn: str(), url: str(),
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
    xac_thuc: str({ req: true, enumOf: 'xac_thuc' }),
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
const published = id => DB['tac-pham'].get(id)?.data.trang_thai === 'da_xuat_ban';

// ---------- Tiện ích ----------
function findMarkers(v, where = '', out = []) {
  if (typeof v === 'string') { if (v.includes(TODO) || v.includes(AI_DRAFT)) out.push(where || '(gốc)'); }
  else if (Array.isArray(v)) v.forEach((x, i) => findMarkers(x, `${where}[${i}]`, out));
  else if (isObj(v)) for (const [k, x] of Object.entries(v)) findMarkers(x, where ? `${where}.${k}` : k, out);
  return out;
}
const linesOf = tachDong;

// ---------- Kiểm tra từng tệp theo schema ----------
const ALL_REFS = [];
for (const [coll, items] of Object.entries(DB)) {
  for (const { data, file } of items.values()) {
    const ctx = { file, refs: [], level: coll === 'tac-pham' ? levelOf(data) : (coll === 'ghi-am' ? STRICT : 0) };
    check(data, SCHEMA[coll], ctx, '');
    ALL_REFS.push(...ctx.refs.map(r => ({ ...r, file, owner: { coll, id: data.id } })));
  }
}

// R04 — tham chiếu phải tồn tại
for (const r of ALL_REFS) if (!DB[r.coll]?.has(r.id)) err('R04', r.file, `"${r.where}" trỏ tới "${r.id}" không tồn tại trong ${r.coll}/`);

// ---------- Quy tắc riêng cho tác phẩm ----------
const slugSeen = new Map();
const situationOf = new Map(); // id tác phẩm -> [id tình huống]
for (const { data: s } of DB['tinh-huong'].values()) for (const t of s.tac_pham || []) {
  if (!situationOf.has(t.ref)) situationOf.set(t.ref, []);
  situationOf.get(t.ref).push(s.id);
}

for (const { data: w, file } of DB['tac-pham'].values()) {
  const lv = levelOf(w);
  const strictOr = (rule, msg) => (lv >= STRICT ? err : warn)(rule, file, msg);

  // R03 — slug duy nhất; tiền tố mã khớp thể loại
  if (w.slug) { if (slugSeen.has(w.slug)) err('R03', file, `slug "${w.slug}" trùng với ${slugSeen.get(w.slug)}`); else slugSeen.set(w.slug, file); }
  const prefix = String(w.id).split('-')[0];
  if (PREFIX_TO_GENRE[prefix] && w.the_loai && PREFIX_TO_GENRE[prefix] !== w.the_loai) err('R03', file, `tiền tố "${prefix}" không khớp thể loại "${w.the_loai}"`);

  // Trường bắt buộc theo thể loại
  if (lv >= STRICT && w.the_loai === 'ca_dao' && !w.the_tho) err('R02', file, 'ca dao cần "the_tho"');
  if (lv >= STRICT && w.the_loai === 'tuc_ngu') {
    if (!w.nghia_den) err('R02', file, 'tục ngữ cần "nghia_den"');
    if (!(w.linh_vuc || []).length) err('R02', file, 'tục ngữ cần ít nhất 1 "linh_vuc"');
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
        if (!l.trim()) warn('R01', file, `dị bản ${v.id}, dòng ${i + 1}: dòng trống giữa lời`);
      });
    }
    // R08 — điều kiện của từng mức bằng chứng
    const srcs = (v.nguon || []).map(n => ({ n, s: DB.nguon.get(n.ref)?.data })).filter(x => x.s);
    if (v.xac_thuc === 'A') {
      const ok = srcs.filter(({ n, s }) => PRINT_SOURCES.has(s.loai) && n.trang !== undefined && n.trang !== '' && n.trang !== TODO);
      if (!ok.length) strictOr('R08', `dị bản ${v.id} mức A cần ít nhất 1 nguồn in (sách/tạp chí/luận văn) kèm số trang`);
      if (lv >= STRICT) for (const { s } of ok) if (!s.da_xac_minh_ban_in) err('R17', file, `dị bản ${v.id} mức A dựa trên nguồn "${s.id}" chưa xác minh thông tin thư mục trên bản in`);
    }
    if (v.xac_thuc === 'B') {
      const field = srcs.filter(({ s }) => FIELD_SOURCES.has(s.loai));
      const hasRec = [...DB['ghi-am'].values()].some(({ data: a }) => a.tac_pham === w.id && a.di_ban === v.id);
      if (!field.length && !hasRec) strictOr('R08', `dị bản ${v.id} mức B cần nguồn điền dã/ghi âm hoặc một bản ghi trong ghi-am/`);
      // R18 — lời lấy từ tư liệu điền dã chưa công bố: phải được phép mới xuất bản
      if (lv >= STRICT && field.length && !hasRec && !field.some(({ s }) => ['cong_cong', 'duoc_phep'].includes(s.ban_quyen))) {
        err('R18', file, `dị bản ${v.id} dựa trên tư liệu điền dã chưa được phép công bố (ban_quyen của nguồn phải là cong_cong hoặc duoc_phep)`);
      }
    }
    if (v.xac_thuc === 'C' && !v.dong_gop) strictOr('R08', `dị bản ${v.id} mức C cần mã đóng góp "dong_gop"`);
  }

  // R05 — bản tham chiếu (bản đại diện)
  const rep = variants.get(w.ban_dai_dien);
  if (w.ban_dai_dien && !rep) err('R05', file, `ban_dai_dien "${w.ban_dai_dien}" không phải dị bản có trong tệp`);
  if (rep && !['A', 'B'].includes(rep.xac_thuc)) strictOr('R05', `bản tham chiếu "${rep.id}" đang ở mức ${rep.xac_thuc}; phải là A hoặc B`);
  if (lv >= STRICT && rep && (w.xac_thuc_dan_gian ?? 'binh_thuong') !== 'binh_thuong' && w.trang_thai === 'da_xuat_ban') {
    warn('R13', file, 'tác phẩm mang cờ nghi gán nhầm/phỏng tác: sẽ hiển thị nhãn xám, không tạo thẻ chia sẻ, không vào trang tình huống');
  }

  // R06 — neo chú giải
  for (const [i, c] of (w.chu_giai || []).entries()) {
    if (!isObj(c)) continue;
    const v = variants.get(c.di_ban);
    if (!v) { err('R06', file, `chu_giai[${i}] trỏ dị bản "${c.di_ban}" không tồn tại`); continue; }
    if (typeof v.loi !== 'string' || c.cum_tu === undefined) continue;
    const kq = phanGiaiNeo(v.loi, c);
    if (!kq.ok) { err('R06', file, `chu_giai[${i}] (${c.di_ban}): ${kq.thong_bao}`); continue; }
    const mt = DB['muc-tu'].get(c.muc_tu)?.data;
    if (mt && !khopMucTu(c.cum_tu, mt)) warn('R06', file, `chu_giai[${i}]: cụm "${c.cum_tu}" không giống mục từ ${c.muc_tu} ("${mt.tu}") — kiểm lại có trỏ nhầm mục từ không`);
  }

  // R07 — khuyến nghị độ dài
  if (typeof w.nghia_ngan === 'string' && words(w.nghia_ngan) > 25 && words(w.nghia_ngan) <= 40) warn('R07', file, `nghia_ngan có ${words(w.nghia_ngan)} từ; nên ≤ 25 để vừa thẻ chia sẻ`);

  // R09 — dấu CAN_BO_SUNG / NHAP_AI
  const marks = findMarkers(w);
  if (marks.length) {
    if (lv >= STRICT) marks.forEach(m => err('R09', file, `còn ${TODO}/${AI_DRAFT} ở "${m}"`));
    else warn('R09', file, `${marks.length} chỗ còn ${TODO}/${AI_DRAFT} (cho phép ở trạng thái nháp)`);
  }
  if (lv >= STRICT) {
    // Tệp được tác phẩm này dùng tới cũng phải sạch dấu
    const used = ALL_REFS.filter(r => r.owner.coll === 'tac-pham' && r.owner.id === w.id && ['muc-tu', 'nguon', 'mo-tip'].includes(r.coll));
    for (const r of used) {
      const target = DB[r.coll].get(r.id);
      if (!target) continue;
      const m = findMarkers(target.data);
      if (m.length) err('R09', file, `dùng ${r.coll}/${r.id} còn ${TODO}/${AI_DRAFT} ở: ${m.join(', ')}`);
    }
  }

  // R10 — tác phẩm chờ kiểm định / xuất bản phải có ít nhất 1 tình huống (tình huống giữ quan hệ, xem docs/10 mục 4.8)
  if (lv >= STRICT && !(situationOf.get(w.id) || []).length) err('R10', file, 'chưa thuộc tình huống nào (thêm vào tac_pham của một tệp tinh-huong/)');

  // R13, R14 — cờ cần căn cứ
  if ((w.xac_thuc_dan_gian ?? 'binh_thuong') !== 'binh_thuong' && !w.can_cu_xac_thuc_dan_gian) err('R13', file, 'cờ xác thực dân gian khác "binh_thuong" cần "can_cu_xac_thuc_dan_gian"');
  if (w.tranh_luan === true && !w.ghi_chu_tranh_luan) err('R14', file, '"tranh_luan: true" cần "ghi_chu_tranh_luan"');
}

// ---------- R19 — nghi trùng tác phẩm ----------
// Lời giống hệt (bỏ dấu, bỏ dấu câu) ở hai tác phẩm khác nhau -> lỗi. Dòng đầu bản tham chiếu trùng -> cảnh báo
// (nhiều bài ca dao dùng chung dòng mở đầu, nên chỉ để người biên soạn xem lại).
const boDau = s => String(s).normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D')
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
    if (d && dongDauDaThay.has(d)) { warn('R19', file, `dòng đầu bản tham chiếu trùng với ${dongDauDaThay.get(d)} — kiểm xem có phải cùng một tác phẩm không`); nghiTrung.push([w.id, dongDauDaThay.get(d)]); }
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
  if (s.da_xac_minh_ban_in === true && findMarkers(s).length) err('R09', file, 'đánh dấu đã xác minh bản in nhưng còn CAN_BO_SUNG');
}

// ---------- Tình huống ----------
for (const { data: s, file } of DB['tinh-huong'].values()) {
  const pub = (s.tac_pham || []).filter(t => published(t.ref));
  const flagged = (s.tac_pham || []).filter(t => (DB['tac-pham'].get(t.ref)?.data.xac_thuc_dan_gian ?? 'binh_thuong') !== 'binh_thuong');
  for (const t of flagged) err('R11', file, `${t.ref} mang cờ nghi gán nhầm/phỏng tác, không được gắn vào tình huống`);
  const warned = (s.tac_pham || []).filter(t => (DB['tac-pham'].get(t.ref)?.data.canh_bao_noi_dung || '').trim());
  for (const t of warned) err('R11', file, `${t.ref} có cảnh báo nội dung, không được gắn vào tình huống`);
  if (pub.length < 3) warn('R11', file, `mới có ${pub.length} tác phẩm đã xuất bản; trang tình huống chỉ được tạo khi có ≥ 3`);
  else if (findMarkers(s).length) err('R09', file, `trang sẽ được tạo nhưng còn ${TODO}/${AI_DRAFT} ở: ${findMarkers(s).join(', ')}`);
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
  const dem = (arr, f) => arr.reduce((m, x) => { const k = f(x); m[k] = (m[k] || 0) + 1; return m; }, {});
  const variants = works.flatMap(w => (w.di_ban || []).filter(isObj).map(v => ({ w, v })));
  const repOf = w => (w.di_ban || []).find(v => v?.id === w.ban_dai_dien);
  const printSrcs = v => (v?.nguon || []).filter(n => PRINT_SOURCES.has(DB.nguon.get(n.ref)?.data.loai) && n.trang && n.trang !== TODO);
  const coMarker = x => findMarkers(x).length > 0;
  const maLoi = (list, ma) => list.filter(l => l.includes(`[${ma}]`)).length;
  return {
    ngay: new Date().toISOString().slice(0, 10),
    tac_pham: { tong: works.length, theo_trang_thai: dem(works, w => w.trang_thai ?? 'khong_ro') },
    con_dau_nhap: {
      tac_pham_con_NHAP_AI: works.filter(w => JSON.stringify(w).includes(AI_DRAFT)).length,
      tac_pham_con_CAN_BO_SUNG: works.filter(w => JSON.stringify(w).includes(TODO)).length,
    },
    bang_chung: {
      di_ban_theo_muc: dem(variants, x => x.v.xac_thuc ?? 'khong_ro'),
      ban_tham_chieu_chua_dat_A_B: works.filter(w => !['A', 'B'].includes(repOf(w)?.xac_thuc)).length,
      ban_tham_chieu_duoi_2_nguon_in: works.filter(w => printSrcs(repOf(w)).length < 2).length,
    },
    no_du_lieu: {
      di_ban_chua_ro_vung: variants.filter(x => (x.v.vung || []).includes('khong_ro')).length,
      muc_tu_chua_co_nguon: [...DB['muc-tu'].values()].filter(x => !(x.data.nguon || []).length || coMarker(x.data)).length,
      nguon_chua_xac_minh_ban_in: [...DB.nguon.values()].filter(x => !x.data.da_xac_minh_ban_in).length,
      nguon_chua_ro_ban_quyen: [...DB.nguon.values()].filter(x => x.data.ban_quyen === 'chua_ro').length,
      tinh_huong_duoi_3_bai_xuat_ban: [...DB['tinh-huong'].values()].filter(x => (x.data.tac_pham || []).filter(t => published(t.ref)).length < 3).length,
    },
    van_de_ky_thuat: {
      neo_chu_giai_loi: maLoi(errors, 'R06'),
      nghi_trung: nghiTrung.map(([a, b]) => `${a} ~ ${b}`),
      tong_loi: errors.length,
      tong_canh_bao: warnings.length,
    },
  };
}

function inSucKhoe(r) {
  const dong = (nhan, gt) => console.log(`  ${nhan.padEnd(42, ' ')} ${typeof gt === 'object' ? JSON.stringify(gt) : gt}`);
  console.log(`\n=== SỨC KHỎE DỮ LIỆU · ${r.ngay} ===`);
  console.log('Tác phẩm'); dong('Tổng', r.tac_pham.tong); dong('Theo trạng thái', r.tac_pham.theo_trang_thai);
  console.log('Còn dấu nháp'); dong('Tác phẩm còn NHAP_AI', r.con_dau_nhap.tac_pham_con_NHAP_AI); dong('Tác phẩm còn CAN_BO_SUNG', r.con_dau_nhap.tac_pham_con_CAN_BO_SUNG);
  console.log('Bằng chứng'); dong('Dị bản theo mức', r.bang_chung.di_ban_theo_muc); dong('Bản tham chiếu chưa đạt A/B', r.bang_chung.ban_tham_chieu_chua_dat_A_B); dong('Bản tham chiếu dưới 2 nguồn in có trang', r.bang_chung.ban_tham_chieu_duoi_2_nguon_in);
  const NHAN = { di_ban_chua_ro_vung: 'Dị bản chưa rõ vùng', muc_tu_chua_co_nguon: 'Mục từ chưa có nguồn / còn nháp',
    nguon_chua_xac_minh_ban_in: 'Nguồn chưa xác minh bản in', nguon_chua_ro_ban_quyen: 'Nguồn chưa rõ bản quyền',
    tinh_huong_duoi_3_bai_xuat_ban: 'Tình huống dưới 3 bài đã xuất bản' };
  console.log('Nợ dữ liệu'); for (const [k, v] of Object.entries(r.no_du_lieu)) dong(NHAN[k] ?? k, v);
  console.log('Vấn đề kỹ thuật'); dong('Neo chú giải lỗi', r.van_de_ky_thuat.neo_chu_giai_loi); dong('Nghi trùng', r.van_de_ky_thuat.nghi_trung.length ? r.van_de_ky_thuat.nghi_trung.join('; ') : 0); dong('Lỗi / cảnh báo', `${r.van_de_ky_thuat.tong_loi} / ${r.van_de_ky_thuat.tong_canh_bao}`);
}

// ---------- Kết quả ----------
if (SUC_KHOE && JSON_OUT) { console.log(JSON.stringify(sucKhoe(), null, 2)); process.exit(0); }
const counts = Object.entries(DB).map(([k, m]) => `${k}: ${m.size}`).join(' · ');
for (const w of warnings) console.log(w);
for (const e of errors) console.log(e);
console.log(`\nĐã kiểm: ${counts}`);
console.log(`Kết quả: ${errors.length} lỗi, ${warnings.length} cảnh báo.`);
if (SUC_KHOE) { inSucKhoe(sucKhoe()); process.exit(0); }
process.exit(errors.length ? 1 : 0);
