// Lớp dữ liệu cho các trang: nạp content collections và tính dữ liệu suy ra.
// Mọi phép suy ra gọi lại scripts/lib/suy-ra.mjs và scripts/lib/neo-chu-giai.mjs — không viết bản tính thứ hai
// (AGENTS.md, docs/10 mục 10).
import { execFileSync } from 'node:child_process';
import { getCollection } from 'astro:content';
import { tinhNhan, diBanHienThi, chuoiHienThi, coGiaTriRieng, TRANG_THAI_CONG_KHAI } from '../../scripts/lib/suy-ra.mjs';
import { phanGiaiNeo } from '../../scripts/lib/neo-chu-giai.mjs';
import { TU_VUNG, LOAI_NGUON_IN, LOAI_NGUON_DIEN_DA, type NhomTuVung } from '../sinh-ra/tu-vung';
import type { DuLieu } from '../sinh-ra/schema';
import { laMaMau, THU_MUC_MAU } from './moi-truong';

export type TacPham = DuLieu<'tac-pham'>;
export type DiBan = TacPham['di_ban'][number];
export type ThamChieuNguon = DiBan['nguon'][number];
export type Nguon = DuLieu<'nguon'>;
export type MucTu = DuLieu<'muc-tu'>;
export type Nhan = 'A' | 'B' | 'C' | 'D';

/** Nhãn người đọc của một mã từ vựng. */
export function nhanCua(nhom: NhomTuVung, ma: string | null | undefined): string {
  const ds = TU_VUNG[nhom] as readonly { ma: string; nhan: string }[];
  return ds.find(x => x.ma === ma)?.nhan ?? '';
}

/** Đường dẫn của trang tác phẩm theo thể loại (docs/20 mục 2). */
const TIEN_TO: Record<string, string> = {
  ca_dao: 'ca-dao', tuc_ngu: 'tuc-ngu', thanh_ngu: 'thanh-ngu', dong_dao: 'dong-dao', ve: 've', cau_do: 'cau-do',
};
export const duongDanTacPham = (tp: Pick<TacPham, 'the_loai' | 'slug'>) => `/${TIEN_TO[tp.the_loai]}/${tp.slug}/`;
export const tienToTheLoai = (theLoai: string) => TIEN_TO[theLoai];

const theoMa = <T extends { id: string }>(ds: { data: T }[]) => new Map(ds.map(x => [x.data.id, x.data]));

let khoCache: Awaited<ReturnType<typeof napKho>> | undefined;

async function napKho() {
  const [tacPham, nguon, mucTu, tinhHuong, ghiAm, nguoi] = await Promise.all([
    getCollection('tac-pham'), getCollection('nguon'), getCollection('muc-tu'),
    getCollection('tinh-huong'), getCollection('ghi-am'), getCollection('nguoi-dong-gop'),
  ]);
  return {
    tacPham: theoMa(tacPham), nguon: theoMa(nguon), mucTu: theoMa(mucTu),
    tinhHuong: tinhHuong.map(x => x.data), ghiAm: ghiAm.map(x => x.data), nguoi: theoMa(nguoi),
  };
}

export async function layKho() {
  khoCache ??= await napKho();
  return khoCache;
}

/** Ngày sửa gần nhất của tệp theo lịch sử Git. Bản sao Git nông (shallow) cho ngày sai → không hiện. */
function ngayCapNhat(tep: string): string | null {
  try {
    const nong = execFileSync('git', ['rev-parse', '--is-shallow-repository'], { encoding: 'utf8' }).trim();
    if (nong !== 'false') return null;
    return execFileSync('git', ['log', '-1', '--format=%cs', '--', tep], { encoding: 'utf8' }).trim() || null;
  } catch {
    return null;
  }
}

export const ngayVN = (iso: string | null | undefined) => {
  const m = String(iso ?? '').match(/^(\d{4})-(\d{2})-(\d{2})/);
  return m ? `${m[3]}/${m[2]}/${m[1]}` : '';
};

export interface ChuGiaiDaGiai {
  so: number;               // số thứ tự trong khối lời (1, 2, …)
  dong: number;             // dòng, từ 1
  amTietDau: number;        // âm tiết, từ 0
  soAmTiet: number;
  cumTu: string;
  tu: string;
  loai: string;
  giaiNghia: string;        // nghia_tai_cho nếu có, không thì giai_nghia của mục từ
  nguon: { ten: string; url: string | null; cachDung: 'tham_khao' | 'trich_nguyen_van' | null }[];
  coAi: boolean;            // phần giải nghĩa có hỗ trợ AI (đã duyệt)
}

export interface ThongTinNguon {
  nguon: Nguon;
  thamChieu: ThamChieuNguon;
  laWeb: boolean;
  laIn: boolean;
  // Giá trị đã qua chuoiHienThi(): CAN_BO_SUNG và chuỗi rỗng thành null — chỉ những giá trị này được in ra trang
  trang: string | null;
  tacGia: string[];
  nxb: string | null;
  nam: number | null;
}

/** Giá trị của nguồn để hiện: CAN_BO_SUNG, rỗng → null (nguồn không bao giờ do AI soạn nên không có NHAP_AI). */
const giaTri = (v: unknown) => (v === null || v === undefined ? null : (chuoiHienThi(String(v), false) as string | null));

/** Mọi thứ trang tác phẩm cần, đã tính sẵn. */
export async function thongTinTacPham(tp: TacPham) {
  const kho = await layKho();
  const daDuyetAi = tp.nhap_ai_da_duyet === true;
  const hien = (s: string | null | undefined) => chuoiHienThi(s, daDuyetAi) as string | null;
  const coGhiAm = (diBanId: string) => kho.ghiAm.some(a => a.tac_pham === tp.id && a.di_ban === diBanId);
  const nhanDiBan = (v: DiBan) => tinhNhan(v, {
    layNguon: (id: string) => kho.nguon.get(id),
    laNguonIn: (loai: string) => LOAI_NGUON_IN.includes(loai),
    laNguonDienDa: (loai: string) => LOAI_NGUON_DIEN_DA.includes(loai),
    coGhiAm: coGhiAm(v.id),
  }) as Nhan;

  const dsHien = diBanHienThi(tp, nhanDiBan as (d: object) => Nhan) as { diBan: DiBan; nhan: Nhan; khoi: 'tham_chieu' | 'chinh' | 'cong_dong' }[];
  const thamChieu = dsHien.find(x => x.khoi === 'tham_chieu')!;
  const tinhHuong = kho.tinhHuong.filter(t => t.tac_pham.some(r => r.ref === tp.id));

  const thongTinNguon = (v: DiBan): ThongTinNguon[] => v.nguon.flatMap(n => {
    const s = kho.nguon.get(n.ref);
    return s ? [{
      nguon: s, thamChieu: n, laWeb: s.loai === 'web', laIn: LOAI_NGUON_IN.includes(s.loai),
      trang: giaTri(n.trang), tacGia: s.tac_gia.map(giaTri).filter((x): x is string => Boolean(x)),
      nxb: giaTri(s.nxb), nam: typeof s.nam === 'number' ? s.nam : null,
    }] : [];
  });

  // Chú giải của bản tham chiếu, theo thứ tự xuất hiện trong lời
  const chuGiai: ChuGiaiDaGiai[] = (tp.chu_giai ?? [])
    .filter(c => c.di_ban === thamChieu.diBan.id)
    .flatMap(c => {
      const viTri = phanGiaiNeo(thamChieu.diBan.loi, { cum_tu: c.cum_tu, dong: c.dong ?? undefined, lan: c.lan ?? undefined }) as
        { ok: true; dong: number; am_tiet_dau: number; so_am_tiet: number } | { ok: false };
      const mt = kho.mucTu.get(c.muc_tu);
      if (!viTri.ok || !mt) return []; // validator (R06, R04) đã chặn trước build
      const mtDuyet = mt.nhap_ai_da_duyet === true;
      const tai = c.nghia_tai_cho ? chuoiHienThi(c.nghia_tai_cho, daDuyetAi) : null;
      const chung = chuoiHienThi(mt.giai_nghia, mtDuyet);
      const giaiNghia = (tai ?? chung) as string | null;
      if (!giaiNghia) return [];
      return [{
        so: 0, dong: viTri.dong, amTietDau: viTri.am_tiet_dau, soAmTiet: viTri.so_am_tiet, cumTu: c.cum_tu,
        tu: mt.tu, loai: nhanCua('loai_muc_tu', mt.loai), giaiNghia,
        nguon: mt.nguon.flatMap(n => {
          const s = kho.nguon.get(n.ref);
          return s ? [{ ten: tenNguon(s), url: n.url ?? s.url ?? null, cachDung: n.cach_dung ?? null }] : [];
        }),
        coAi: (tai ? c.nghia_tai_cho : mt.giai_nghia)?.includes('NHAP_AI') ?? false,
      }];
    })
    .sort((a, b) => a.dong - b.dong || a.amTietDau - b.amTietDau)
    .map((c, i) => ({ ...c, so: i + 1 }));

  const nghiaNgan = hien(tp.nghia_ngan);
  const dienGiai = (tp.dien_giai ?? []).flatMap(d => {
    const nd = hien(d.noi_dung);
    return nd ? [{ gocNhin: hien(d.goc_nhin) ?? '', noiDung: nd }] : [];
  });
  const moTaTinhHuong = (tp.mo_ta_tinh_huong ?? []).map(hien).filter((x): x is string => Boolean(x));
  const tep = `${laMaMau(tp.id) ? THU_MUC_MAU : 'content'}/tac-pham/${tp.id}.yaml`;
  const tatCaNguon = dsHien.flatMap(x => thongTinNguon(x.diBan).map(n => ({ ...n, diBanId: x.diBan.id })));

  // Có chuỗi AI đã duyệt nào hiện ra trang không (dòng "có hỗ trợ AI")
  const coAi = daDuyetAi && [tp.nghia_ngan, ...(tp.dien_giai ?? []).map(d => d.noi_dung), ...(tp.mo_ta_tinh_huong ?? [])]
    .some(s => typeof s === 'string' && s.includes('NHAP_AI')) || chuGiai.some(c => c.coAi);

  return {
    tp,
    laMau: laMaMau(tp.id),
    theLoai: nhanCua('the_loai', tp.the_loai),
    thamChieu: { ...thamChieu, nguon: thongTinNguon(thamChieu.diBan) },
    diBanChinh: dsHien.filter(x => x.khoi === 'chinh').map(x => ({ ...x, nguon: thongTinNguon(x.diBan) })),
    diBanCongDong: dsHien.filter(x => x.khoi === 'cong_dong'),
    chuGiai,
    nghiaNgan,
    nghiaDen: hien(tp.nghia_den),
    nghiaBong: hien(tp.nghia_bong),
    dienGiai,
    gocKiemChung: tp.goc_kiem_chung ? {
      ketLuan: nhanCua('ket_luan_kiem_chung', tp.goc_kiem_chung.ket_luan),
      noiDung: hien(tp.goc_kiem_chung.noi_dung),
    } : null,
    dienXuong: tp.dien_xuong ? {
      hinhThuc: (tp.dien_xuong.hinh_thuc ?? []).map(h => nhanCua('hinh_thuc_dien_xuong', h)).filter(Boolean),
      nguoiTheHien: hien(tp.dien_xuong.nguoi_the_hien),
      boiCanh: hien(tp.dien_xuong.boi_canh),
    } : null,
    amLuat: tp.am_luat ? { nhip: hien(tp.am_luat.nhip), van: (tp.am_luat.van ?? []).map(hien).filter(Boolean), ghiChu: hien(tp.am_luat.ghi_chu) } : null,
    moTaTinhHuong,
    tinhHuong: tinhHuong.map(t => ({ tieuDe: t.tieu_de, hopKhi: hien(t.tac_pham.find(r => r.ref === tp.id)?.hop_khi) })),
    canhBao: hien(tp.canh_bao_noi_dung),
    coNghi: tp.xac_thuc_dan_gian && tp.xac_thuc_dan_gian !== 'binh_thuong'
      ? { nhan: nhanCua('xac_thuc_dan_gian', tp.xac_thuc_dan_gian), canCu: hien(tp.can_cu_xac_thuc_dan_gian) } : null,
    tranhLuan: tp.tranh_luan ? hien(tp.ghi_chu_tranh_luan) : null,
    coAi,
    thieuPhan: !nghiaNgan || (chuGiai.length === 0 && tinhHuong.length === 0),
    noindex: laMaMau(tp.id) || !coGiaTriRieng(tp, tinhHuong.length),
    tatCaNguon,
    tuCaDaoMe: tatCaNguon.find(n => n.nguon.id === 'src-ca-dao-me' && n.thamChieu.url)?.thamChieu.url ?? null,
    nguoiBienSoan: kho.nguoi.get(tp.bien_soan.nguoi)?.ten_hien_thi ?? null,
    capNhat: ngayCapNhat(tep),
    tep,
  };
}

/** Tên ngắn của nguồn để hiện trong câu ("Ca dao Mẹ — Kho tàng…" → "Ca dao Mẹ"). */
export const tenNguon = (s: Pick<Nguon, 'ten'>) => s.ten.split(' — ')[0]!;
export const tenMien = (url: string | null | undefined) => {
  try { return url ? new URL(url).hostname.replace(/^www\./, '') : ''; } catch { return ''; }
};

export async function tacPhamCongKhai() {
  const kho = await layKho();
  return [...kho.tacPham.values()].filter(tp => tp.trang_thai === TRANG_THAI_CONG_KHAI);
}
