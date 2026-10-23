// Dữ liệu suy ra — dùng chung cho công cụ kiểm tra (scripts/kiem-tra-du-lieu.mjs) và cho build Astro.
// Đặc tả: docs/10-dac-ta-du-lieu.md mục 5 và 10 (D-041, D-043). Không viết bản tính thứ hai ở nơi khác.
//
// Nguyên tắc: thứ gì máy tính được từ dữ liệu thì không ai nhập tay — nhãn bằng chứng của dị bản,
// dị bản nào được hiện, bài nào đã có "giá trị riêng" (được Google đánh chỉ mục).

export const TODO = 'CAN_BO_SUNG';             // thông tin còn thiếu: trang ẩn trường đó
export const AI_DRAFT = 'NHAP_AI';             // văn bản AI soạn nháp: chỉ lên trang khi nhap_ai_da_duyet: true
export const TRANG_THAI_CONG_KHAI = 'cong_khai';
export const NGUONG_TRANG_TINH_HUONG = 2;      // trang tình huống chỉ được tạo khi có ≥ 2 tác phẩm công khai
export const SO_TU_TRICH_TOI_DA = 15;          // R20: chú thích chép nguyên văn từ nguồn chưa cho phép, tối đa 15 từ
export const QUYEN_DUNG_DUOC = new Set(['cong_cong', 'duoc_phep']);

const coChu = (s, dau) => typeof s === 'string' && s.includes(dau);
const coTrang = n => n?.trang !== undefined && n.trang !== null && String(n.trang).trim() !== '' && !coChu(String(n.trang), TODO);

/**
 * Nhãn bằng chứng của một dị bản (D-041), ưu tiên A > B > C > D:
 *   A — nguồn in (sách, tạp chí, luận văn; kể cả bản quét) đã xác minh, có số trang
 *   B — tư liệu điền dã được phép công bố, hoặc có tệp ghi âm trỏ tới dị bản
 *   C — có mã đóng góp cộng đồng
 *   D — còn lại (kể cả lời lấy từ trang web): "Lời phổ biến · chưa đối chiếu"
 * @param {object} diBan  phần tử di_ban
 * @param {{layNguon: (id: string) => object|undefined, laNguonIn: (loai: string) => boolean,
 *          laNguonDienDa: (loai: string) => boolean, coGhiAm: boolean}} ctx
 * @returns {'A'|'B'|'C'|'D'}
 */
export function tinhNhan(diBan, ctx) {
  const refs = (diBan?.nguon || []).map(n => ({ n, s: ctx.layNguon(n?.ref) })).filter(x => x.s);
  if (refs.some(({ n, s }) => ctx.laNguonIn(s.loai) && s.da_xac_minh_ban_in === true && coTrang(n))) return 'A';
  if (ctx.coGhiAm || refs.some(({ s }) => ctx.laNguonDienDa(s.loai) && QUYEN_DUNG_DUOC.has(s.ban_quyen))) return 'B';
  if (diBan?.dong_gop) return 'C';
  return 'D';
}

/** Dị bản có nguồn gốc ghi rõ không (R21): ít nhất một tham chiếu nguồn, hoặc mã đóng góp. */
export const coNguonGoc = diBan => (diBan?.nguon || []).some(n => n?.ref) || Boolean(diBan?.dong_gop);

/**
 * Chuỗi để hiển thị: null nếu rỗng, còn CAN_BO_SUNG, hoặc còn nháp AI chưa duyệt.
 * Nháp AI đã duyệt: bỏ tiền tố NHAP_AI (dấu vết vẫn nằm trong dữ liệu; trang hiện dòng "có hỗ trợ AI").
 */
export function chuoiHienThi(s, daDuyetAi) {
  if (typeof s !== 'string' || !s.trim() || coChu(s, TODO)) return null;
  if (coChu(s, AI_DRAFT)) return daDuyetAi ? s.replace(/NHAP_AI\s*/g, '').trim() : null;
  return s;
}

/**
 * Tác phẩm đã có "giá trị riêng" ngoài lời chưa (D-043): nghĩa ngắn hiển thị được, cộng ít nhất
 * một chú giải hoặc một tình huống. Chưa có thì trang công khai mang noindex (người đọc vẫn xem được).
 */
export function coGiaTriRieng(tacPham, soTinhHuong) {
  const nghia = chuoiHienThi(tacPham?.nghia_ngan, tacPham?.nhap_ai_da_duyet === true);
  return Boolean(nghia) && ((tacPham?.chu_giai || []).length > 0 || soTinhHuong > 0);
}

/**
 * Dị bản được hiện trên trang tác phẩm: bản tham chiếu luôn hiện (kèm nhãn của nó);
 * dị bản khác chỉ hiện khi mức A/B (khối chính) hoặc C (khối "Cộng đồng ghi nhận"); D ở lại nội bộ.
 * @param {object} tacPham
 * @param {(diBan: object) => 'A'|'B'|'C'|'D'} nhanCua
 * @returns {{diBan: object, nhan: string, khoi: 'tham_chieu'|'chinh'|'cong_dong'}[]}
 */
export function diBanHienThi(tacPham, nhanCua) {
  const out = [];
  for (const v of tacPham?.di_ban || []) {
    const nhan = nhanCua(v);
    if (v.id === tacPham.ban_dai_dien) out.unshift({ diBan: v, nhan, khoi: 'tham_chieu' });
    else if (nhan === 'A' || nhan === 'B') out.push({ diBan: v, nhan, khoi: 'chinh' });
    else if (nhan === 'C') out.push({ diBan: v, nhan, khoi: 'cong_dong' });
  }
  return out;
}
