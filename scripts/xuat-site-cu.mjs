#!/usr/bin/env node
// Xuất bài CÔNG KHAI của site Astro cũ (content/) sang wordpress/plugins/ca-dao/du-lieu/site-cu.json để plugin chuyển
// vào WordPress (T-076, docs/24 mục 7). Máy chép đúng những gì người biên soạn đã nhập — không thêm, không đoán:
//   lời của bản tham chiếu và các dị bản có nguồn; tên nguồn, địa chỉ, ngày truy cập người biên soạn đã điền;
//   nghĩa ngắn và chú giải chỉ khi không phải nháp AI, hoặc là nháp AI đã được duyệt (bỏ tiền tố NHAP_AI, đánh dấu
//   ho_tro_ai để trang ghi "có hỗ trợ của AI"). Bỏ qua mọi chuỗi CAN_BO_SUNG. Bài nháp (hồ sơ khung) không chuyển.
// Dùng: npm run xuat:site-cu   ·   npm run xuat:site-cu -- --kiem (báo lỗi nếu tệp JSON chưa khớp content/)
import fs from 'node:fs';
import path from 'node:path';
import YAML from 'yaml';

const doc = (thu_muc) => Object.fromEntries(
  fs.readdirSync(path.join('content', thu_muc)).filter(t => t.endsWith('.yaml'))
    .map(t => { const d = YAML.parse(fs.readFileSync(path.join('content', thu_muc, t), 'utf8')); return [d.id ?? t.replace(/\.yaml$/, ''), d]; }),
);
const nguon = doc('nguon');
const mucTu = doc('muc-tu');
const THE_LOAI = { ca_dao: 'ca-dao', tuc_ngu: 'tuc-ngu', thanh_ngu: 'thanh-ngu', dong_dao: 'dong-dao', ve: 've', cau_do: 'cau-do' };

/** Chuỗi người biên soạn đã duyệt: trả [chữ, có hỗ trợ AI] hoặc null nếu chưa có / nháp AI chưa duyệt. */
const daDuyet = (chu, duyetAi) => {
  const s = String(chu ?? '').trim();
  if (!s || s.includes('CAN_BO_SUNG')) return null;
  if (s.startsWith('NHAP_AI')) return duyetAi ? [s.replace(/^NHAP_AI\s*/, ''), true] : null;
  return [s, false];
};

const bai = [];
for (const [id, tp] of Object.entries(doc('tac-pham')).sort()) {
  if (tp.trang_thai !== 'cong_khai') continue;
  const chinh = tp.di_ban.find(d => d.id === tp.ban_dai_dien) ?? tp.di_ban[0];
  const ref = chinh.nguon?.[0];
  const src = ref ? nguon[ref.ref] : null;
  let hoTroAi = false;
  const nghia = daDuyet(tp.nghia_ngan, tp.nhap_ai_da_duyet);
  if (nghia?.[1]) hoTroAi = true;
  const chuThich = [];
  for (const cg of tp.chu_giai ?? []) {
    if (cg.di_ban && cg.di_ban !== chinh.id) continue;
    const tai = daDuyet(cg.nghia_tai_cho, tp.nhap_ai_da_duyet);
    const mt = cg.muc_tu ? mucTu[cg.muc_tu] : null;
    const gn = tai ?? (mt ? daDuyet(mt.giai_nghia, mt.nhap_ai_da_duyet) : null);
    if (!gn) continue;
    if (gn[1]) hoTroAi = true;
    chuThich.push({ tu: cg.cum_tu, noi_dung: gn[0] });
  }
  bai.push({
    ma: id,
    slug: tp.slug ?? '',
    tieu_de: tp.tieu_de,
    the_loai: THE_LOAI[tp.the_loai] ?? 'ca-dao',
    loi: String(chinh.loi).replace(/\r/g, '').replace(/\n+$/, ''),
    di_ban: tp.di_ban.filter(d => d !== chinh && (d.nguon ?? []).length).map(d => String(d.loi).replace(/\r/g, '').replace(/\n+$/, '')),
    nguon: src ? { ten: src.ten, url: (ref.url || (src.loai === 'web' ? src.url : '')) ?? '', ngay_truy_cap: String(ref.ngay_truy_cap ?? '') } : null,
    hieu_nhanh: nghia?.[0] ?? '',
    chu_thich: chuThich,
    ho_tro_ai: hoTroAi,
  });
}

// Tình huống (content/tinh-huong: tên, đường dẫn, lời mở đầu đã viết) và lịch dịp (config/lich-tinh-huong.yaml; bỏ dịp
// chưa có ngày) cho trang chủ (T-043)
const tinhHuong = Object.values(doc('tinh-huong')).map(t => ({ slug: t.slug, ten: t.tieu_de, mo_dau: daDuyet(t.mo_dau, t.nhap_ai_da_duyet)?.[0] ?? '' }));
const lich = YAML.parse(fs.readFileSync(path.join('config', 'lich-tinh-huong.yaml'), 'utf8'));
const coNgay = d => /^\d{4}-\d{2}-\d{2}$/.test(String(d));
const slugTH = id => String(id).replace(/^sit-/, '');
const lichRa = {
  mac_dinh: (lich.mac_dinh ?? []).map(slugTH),
  dip: (lich.dip ?? []).filter(d => coNgay(d.tu) && coNgay(d.den)).map(d => ({ ten: d.ten, tu: String(d.tu), den: String(d.den), dua_len_dau: (d.dua_len_dau ?? []).map(slugTH) })),
};

const goc = path.join('wordpress', 'plugins', 'ca-dao', 'du-lieu');
const tep = {
  'site-cu.json': JSON.stringify({ ghi_chu: 'Sinh bằng scripts/xuat-site-cu.mjs từ content/ (bài công khai của site Astro cũ, T-076). Không sửa tay.', bai }, null, 2) + '\n',
  'tinh-huong.json': JSON.stringify({ ghi_chu: 'Sinh bằng scripts/xuat-site-cu.mjs từ content/tinh-huong và config/lich-tinh-huong.yaml (T-043). Không sửa tay.', tinh_huong: tinhHuong, lich: lichRa }, null, 2) + '\n',
};
if (process.argv.includes('--kiem')) {
  for (const [ten, chu] of Object.entries(tep)) {
    const cu = fs.existsSync(path.join(goc, ten)) ? fs.readFileSync(path.join(goc, ten), 'utf8') : '';
    if (cu !== chu) { console.log(`LỖI      [site cũ] ${ten} chưa khớp content/, config/ — chạy npm run xuat:site-cu`); process.exit(1); }
  }
  console.log(`Site cũ: ${bai.length} bài công khai, ${tinhHuong.length} tình huống — tệp chuyển khớp content/.`);
} else {
  fs.mkdirSync(goc, { recursive: true });
  for (const [ten, chu] of Object.entries(tep)) fs.writeFileSync(path.join(goc, ten), chu);
  console.log(`Đã xuất ${bai.length} bài công khai, ${tinhHuong.length} tình huống, lịch ${lichRa.dip.length} dịp → ${goc}`);
}
