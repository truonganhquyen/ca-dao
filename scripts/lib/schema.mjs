// Mô tả schema MỘT NGUỒN (T-036, docs/10 mục 4, docs/21 mục 4) — schema 2.0.
// Dùng chung cho:
//   - scripts/kiem-tra-du-lieu.mjs  (kiểm kiểu, trường bắt buộc, trường lạ: R02, R07, R10, R16)
//   - scripts/sinh-tu-schema.mjs    (sinh Zod cho Astro, cấu hình Sveltia CMS, kiểu TypeScript cho từ vựng)
// Quy tắc nghiệp vụ R01–R22 chỉ nằm trong scripts/kiem-tra-du-lieu.mjs (D-019); tệp này chỉ mô tả hình dạng dữ liệu.
// Đổi schema: theo docs/10 mục 12, rồi chạy `npm run sinh:schema`.

export const SLUG = /^[a-z0-9]+(-[a-z0-9]+)*$/;
export const DATE = /^\d{4}-\d{2}-\d{2}$/;

// ---------- Ngôn ngữ mô tả schema ----------
export const str = (o = {}) => ({ t: 'str', ...o });
export const int = (o = {}) => ({ t: 'int', ...o });
export const bool = (o = {}) => ({ t: 'bool', ...o });
export const date = (o = {}) => ({ t: 'date', ...o });
export const list = (of, o = {}) => ({ t: 'list', of, ...o });
export const obj = (fields, o = {}) => ({ t: 'obj', fields, ...o });
// Tùy chọn: req: true (thiếu là LỖI) | 'pub' (nên có khi công khai, thiếu là NHẮC R10) · enumOf · pattern · maxWords · maxLen
//           ref (tên bộ sưu tập) · min · minPub (NHẮC R10) · max · num (str chấp nhận số) · todoOk (int/date chấp nhận CAN_BO_SUNG)
//           minVal · maxVal (int) · dai (chuỗi nhiều dòng: ô soạn lớn trong CMS)

// ---------- Schema các bộ sưu tập ----------
// Tham chiếu nguồn: sách dùng `trang`; trang web dùng `url` + `ngay_truy_cap` (+ `ban_luu_tru`, R22).
// `cach_dung` chỉ có nghĩa với chú thích / diễn giải: tham_khao (đã viết lại) | trich_nguyen_van (R20).
export const NGUON_REF = obj({
  ref: str({ req: true, ref: 'nguon' }),
  trang: str({ num: true }),
  url: str({ pattern: /^https?:\/\// }),
  ngay_truy_cap: date(),
  ban_luu_tru: str({ pattern: /^https?:\/\// }),
  cach_dung: str({ enumOf: 'cach_dung' }),
  ghi_chu: str(),
});

export const DI_BAN = obj({
  id: str({ req: true, pattern: /^v\d+$/ }),
  loi: str({ req: true, dai: true }),
  vung: list(str({ enumOf: 'vung' }), { req: true, min: 1 }),
  dia_danh: str(),
  nguon: list(NGUON_REF, { req: true }),
  nguoi_ke: obj({ the_he: str(), quan_he: str() }),
  dong_gop: str({ pattern: /^sub-\d{4}-\d{4}$/ }),
  ghi_chu: str(),
});

export const CHU_GIAI = obj({
  di_ban: str({ req: true }),
  dong: int({ minVal: 1 }),          // chỉ khai khi cụm từ lặp ở nhiều dòng
  cum_tu: str({ req: true }),        // gõ đúng như trong lời; máy tự tìm vị trí
  lan: int({ minVal: 1 }),           // chỉ khai khi cụm từ lặp trong phạm vi đã chọn
  muc_tu: str({ req: true, ref: 'muc-tu' }),
  nghia_tai_cho: str({ maxWords: 40 }),
});

export const SCHEMA = {
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
      noi_dung: str({ req: true, dai: true }),
      nguon: list(NGUON_REF, { req: true, minPub: 1 }),
    }), { req: 'pub', minPub: 1 }),
    nghia_den: str(),
    nghia_bong: str(),
    linh_vuc: list(str({ enumOf: 'linh_vuc' })),
    goc_kiem_chung: obj({ ket_luan: str({ req: true, enumOf: 'ket_luan_kiem_chung' }), noi_dung: str({ dai: true }), nguon: list(NGUON_REF) }),
    am_luat: obj({ nhip: str(), van: list(str()), ghi_chu: str() }),
    dien_xuong: obj({ hinh_thuc: list(str({ enumOf: 'hinh_thuc_dien_xuong' })), nguoi_the_hien: str(), boi_canh: str() }),
    mo_ta_tinh_huong: list(str(), { req: 'pub', minPub: 2 }),
    cam_xuc: list(str({ enumOf: 'cam_xuc' }), { max: 5 }),
    chu_de: list(str({ enumOf: 'chu_de' }), { req: 'pub', minPub: 1, max: 3 }),
    mo_tip: list(str({ ref: 'mo-tip' })),
    lien_quan: list(str({ ref: 'tac-pham' })),
    sgk: list(obj({ bo_sach: str({ req: true, enumOf: 'sgk_bo_sach' }), lop: int({ req: true, minVal: 1, maxVal: 12 }), bai: str() })),
    canh_bao_noi_dung: str({ dai: true }),
    xac_thuc_dan_gian: str({ enumOf: 'xac_thuc_dan_gian' }),
    can_cu_xac_thuc_dan_gian: str({ dai: true }),
    tranh_luan: bool(),
    ghi_chu_tranh_luan: str({ dai: true }),
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
    giai_nghia_day_du: str({ dai: true }),
    nguon: list(NGUON_REF, { req: true }),
    xem_them: list(str({ ref: 'muc-tu' })),
    nhap_ai_da_duyet: bool(),
  }),
  'mo-tip': obj({ id: str({ req: true }), ten: str({ req: true }), mo_ta: str({ req: true, dai: true }), nguon: list(NGUON_REF), nhap_ai_da_duyet: bool() }),
  'nguon': obj({
    id: str({ req: true }),
    loai: str({ req: true, enumOf: 'loai_nguon' }),
    tac_gia: list(str(), { req: true, min: 1 }),
    ten: str({ req: true }),
    nxb: str(), nam: int({ todoOk: true }), noi_xb: str(), tap: str({ num: true }), tai_ban: str({ num: true }),
    isbn: str(), url: str({ pattern: /^https?:\/\// }),
    ban_quyen: str({ req: true, enumOf: 'ban_quyen' }),
    da_xac_minh_ban_in: bool({ req: true }),
    ghi_chu: str({ dai: true }),
  }),
  'tinh-huong': obj({
    id: str({ req: true }),
    slug: str({ req: true, pattern: SLUG, maxLen: 60 }),
    tieu_de: str({ req: true }),
    nhom: str({ req: true, enumOf: 'nhom_tinh_huong' }),
    mo_dau: str({ req: true, dai: true }),
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
    ban_chep: str({ dai: true }),
  }),
  'nguoi-dong-gop': obj({
    id: str({ req: true }),
    ten_hien_thi: str({ req: true }),
    kieu_hien_thi: str({ req: true, enumOf: 'kieu_hien_thi' }),
    vai_tro: list(str({ enumOf: 'vai_tro' }), { req: true, min: 1 }),
    gioi_thieu: str({ dai: true }),
  }),
};

export const ID_PATTERN = {
  'tac-pham': /^(cd|tn|tng|dd|ve|cdo)-\d{4}$/,
  'muc-tu': /^lx-[a-z0-9]+(-[a-z0-9]+)*$/,
  'mo-tip': /^mt-[a-z0-9]+(-[a-z0-9]+)*$/,
  'nguon': /^src-[a-z0-9]+(-[a-z0-9]+)*$/,
  'tinh-huong': /^sit-[a-z0-9]+(-[a-z0-9]+)*$/,
  'ghi-am': /^au-\d{4}$/,
  'nguoi-dong-gop': /^ctb-\d{4}$/,
};

/** Trường dùng để hiện một hồ sơ khi chọn từ danh sách (ô quan hệ trong CMS). */
export const TRUONG_HIEN_THI = {
  'tac-pham': 'tieu_de',
  'muc-tu': 'tu',
  'mo-tip': 'ten',
  'nguon': 'ten',
  'tinh-huong': 'tieu_de',
  'ghi-am': 'id',
  'nguoi-dong-gop': 'ten_hien_thi',
};
