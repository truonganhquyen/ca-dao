// TỆP SINH TỰ ĐỘNG bởi scripts/sinh-tu-schema.mjs — không sửa tay.
// Sửa scripts/lib/schema.mjs hoặc taxonomy/tu-vung-kiem-soat.yaml rồi chạy `npm run sinh:schema`.

export const TU_VUNG = {
  the_loai: [
    { ma: "ca_dao", nhan: "Ca dao" },
    { ma: "tuc_ngu", nhan: "Tục ngữ" },
    { ma: "thanh_ngu", nhan: "Thành ngữ" },
    { ma: "dong_dao", nhan: "Đồng dao" },
    { ma: "ve", nhan: "Vè" },
    { ma: "cau_do", nhan: "Câu đố" },
  ],
  the_tho: [
    { ma: "luc_bat", nhan: "Lục bát" },
    { ma: "luc_bat_bien_the", nhan: "Lục bát biến thể" },
    { ma: "song_that_luc_bat", nhan: "Song thất lục bát" },
    { ma: "van_4", nhan: "Thể bốn chữ" },
    { ma: "van_5", nhan: "Thể năm chữ" },
    { ma: "hop_the", nhan: "Hợp thể" },
  ],
  trang_thai: [
    { ma: "nhap", nhan: "Nháp" },
    { ma: "cong_khai", nhan: "Công khai" },
    { ma: "an", nhan: "Ẩn" },
  ],
  xac_thuc: [
    { ma: "A", nhan: "Đã đối chiếu tư liệu" },
    { ma: "B", nhan: "Ghi từ người kể" },
    { ma: "C", nhan: "Cộng đồng gửi" },
    { ma: "D", nhan: "Lời phổ biến – chưa đối chiếu" },
  ],
  cach_dung: [
    { ma: "tham_khao", nhan: "Tham khảo" },
    { ma: "trich_nguyen_van", nhan: "Trích nguyên văn" },
  ],
  xac_thuc_dan_gian: [
    { ma: "binh_thuong", nhan: "Bình thường" },
    { ma: "nghi_gan_nham", nhan: "Nghi gán nhầm" },
    { ma: "phong_tac_hien_dai", nhan: "Phỏng tác hiện đại" },
  ],
  vung: [
    { ma: "bac_bo", nhan: "Bắc Bộ" },
    { ma: "bac_trung_bo", nhan: "Bắc Trung Bộ" },
    { ma: "trung_trung_bo", nhan: "Trung Trung Bộ" },
    { ma: "nam_trung_bo", nhan: "Nam Trung Bộ" },
    { ma: "tay_nguyen", nhan: "Tây Nguyên" },
    { ma: "nam_bo", nhan: "Nam Bộ" },
    { ma: "toan_quoc", nhan: "Phổ biến toàn quốc" },
    { ma: "khong_ro", nhan: "Chưa rõ vùng" },
  ],
  chu_de: [
    { ma: "gia_dinh", nhan: "Gia đình" },
    { ma: "tinh_yeu_hon_nhan", nhan: "Tình yêu, hôn nhân" },
    { ma: "que_huong_dat_nuoc", nhan: "Quê hương, đất nước" },
    { ma: "lao_dong_san_xuat", nhan: "Lao động, sản xuất" },
    { ma: "thien_nhien_thoi_tiet", nhan: "Thiên nhiên, thời tiết" },
    { ma: "ung_xu_dao_duc", nhan: "Ứng xử, đạo đức" },
    { ma: "than_phan_con_nguoi", nhan: "Thân phận con người" },
    { ma: "phe_phan_xa_hoi", nhan: "Phê phán xã hội" },
    { ma: "hai_huoc_cham_biem", nhan: "Hài hước, châm biếm" },
    { ma: "hoc_tap_thay_tro", nhan: "Học tập, thầy trò" },
    { ma: "dia_danh_lich_su", nhan: "Địa danh, lịch sử" },
  ],
  cam_xuc: [
    { ma: "thuong_nho", nhan: "Thương nhớ" },
    { ma: "biet_on", nhan: "Biết ơn" },
    { ma: "tu_hao", nhan: "Tự hào" },
    { ma: "xot_xa", nhan: "Xót xa" },
    { ma: "hon_doi", nhan: "Hờn dỗi" },
    { ma: "vui_tuoi", nhan: "Vui tươi" },
    { ma: "che_gieu", nhan: "Chế giễu" },
    { ma: "khuyen_ran", nhan: "Khuyên răn" },
    { ma: "an_ui", nhan: "An ủi" },
    { ma: "hy_vong", nhan: "Hy vọng" },
    { ma: "say_dam", nhan: "Say đắm" },
    { ma: "chua_chat", nhan: "Chua chát" },
  ],
  linh_vuc: [
    { ma: "thoi_tiet", nhan: "Thời tiết" },
    { ma: "nong_nghiep", nhan: "Nông nghiệp" },
    { ma: "ung_xu", nhan: "Ứng xử" },
    { ma: "gia_dinh", nhan: "Gia đình" },
    { ma: "xa_hoi", nhan: "Xã hội" },
    { ma: "suc_khoe", nhan: "Sức khỏe" },
    { ma: "kinh_nghiem_song", nhan: "Kinh nghiệm sống" },
  ],
  ket_luan_kiem_chung: [
    { ma: "co_co_so", nhan: "Có cơ sở" },
    { ma: "mot_phan", nhan: "Đúng một phần" },
    { ma: "chua_ro", nhan: "Chưa rõ" },
    { ma: "khong_dung", nhan: "Không đúng theo hiểu biết hiện nay" },
  ],
  hinh_thuc_dien_xuong: [
    { ma: "hat_ru", nhan: "Hát ru" },
    { ma: "ho_lao_dong", nhan: "Hò lao động" },
    { ma: "hat_doi_dap", nhan: "Hát đối đáp" },
    { ma: "ly", nhan: "Lý" },
    { ma: "dong_dao_tro_choi", nhan: "Đồng dao, trò chơi" },
    { ma: "noi_thuong", nhan: "Nói thường" },
    { ma: "khong_ro", nhan: "Chưa rõ" },
  ],
  loai_muc_tu: [
    { ma: "tu_co", nhan: "Từ cổ" },
    { ma: "phuong_ngu", nhan: "Phương ngữ" },
    { ma: "han_viet", nhan: "Hán Việt" },
    { ma: "dien_tich", nhan: "Điển tích" },
    { ma: "dia_danh", nhan: "Địa danh" },
    { ma: "nhan_vat", nhan: "Nhân vật" },
    { ma: "phong_tuc", nhan: "Phong tục" },
    { ma: "do_vat", nhan: "Đồ vật" },
  ],
  loai_nguon: [
    { ma: "sach", nhan: "Sách" },
    { ma: "tap_chi", nhan: "Tạp chí" },
    { ma: "luan_van", nhan: "Luận văn, luận án" },
    { ma: "dien_da", nhan: "Phiếu điền dã" },
    { ma: "ghi_am", nhan: "Bản ghi âm" },
    { ma: "web", nhan: "Trang web" },
    { ma: "dong_gop", nhan: "Đóng góp cộng đồng" },
  ],
  ban_quyen: [
    { ma: "cong_cong", nhan: "Thuộc phạm vi công cộng" },
    { ma: "co_ban_quyen", nhan: "Có bản quyền" },
    { ma: "duoc_phep", nhan: "Được phép sử dụng" },
    { ma: "chua_ro", nhan: "Chưa rõ" },
  ],
  nhom_tinh_huong: [
    { ma: "gia_dinh", nhan: "Gia đình" },
    { ma: "tinh_yeu", nhan: "Tình yêu" },
    { ma: "que_huong", nhan: "Quê hương" },
    { ma: "hoc_tap_lao_dong", nhan: "Học tập, lao động" },
    { ma: "ung_xu", nhan: "Ứng xử" },
    { ma: "thoi_tiet_mua_vu", nhan: "Thời tiết, mùa vụ" },
    { ma: "than_phan", nhan: "Thân phận" },
    { ma: "dip_le", nhan: "Dịp lễ" },
    { ma: "vui", nhan: "Vui" },
  ],
  loai_ghi_am: [
    { ma: "doc", nhan: "Đọc" },
    { ma: "hat_ru", nhan: "Hát ru" },
    { ma: "ho", nhan: "Hò" },
    { ma: "ly", nhan: "Lý" },
    { ma: "hat_doi_dap", nhan: "Hát đối đáp" },
    { ma: "khac", nhan: "Khác" },
  ],
  giay_phep: [
    { ma: "cc_by_4_0", nhan: "CC BY 4.0" },
    { ma: "cc_by_sa_4_0", nhan: "CC BY-SA 4.0" },
  ],
  kieu_hien_thi: [
    { ma: "ten_that", nhan: "Tên thật" },
    { ma: "but_danh", nhan: "Bút danh" },
    { ma: "an_danh", nhan: "Ẩn danh" },
  ],
  vai_tro: [
    { ma: "bien_soan", nhan: "Biên soạn" },
    { ma: "co_van", nhan: "Cố vấn" },
    { ma: "dong_gop", nhan: "Đóng góp" },
    { ma: "ghi_am", nhan: "Người ghi âm" },
    { ma: "nguoi_the_hien", nhan: "Người thể hiện" },
  ],
  sgk_bo_sach: [
    { ma: "ket_noi_tri_thuc", nhan: "Kết nối tri thức với cuộc sống" },
    { ma: "chan_troi_sang_tao", nhan: "Chân trời sáng tạo" },
    { ma: "canh_dieu", nhan: "Cánh Diều" },
  ],
} as const;

/** Mã còn dùng của từng nhóm, dạng bộ (tuple) cho z.enum(). */
export const MA_TU_VUNG = {
  the_loai: ["ca_dao", "tuc_ngu", "thanh_ngu", "dong_dao", "ve", "cau_do"],
  the_tho: ["luc_bat", "luc_bat_bien_the", "song_that_luc_bat", "van_4", "van_5", "hop_the"],
  trang_thai: ["nhap", "cong_khai", "an"],
  xac_thuc: ["A", "B", "C", "D"],
  cach_dung: ["tham_khao", "trich_nguyen_van"],
  xac_thuc_dan_gian: ["binh_thuong", "nghi_gan_nham", "phong_tac_hien_dai"],
  vung: ["bac_bo", "bac_trung_bo", "trung_trung_bo", "nam_trung_bo", "tay_nguyen", "nam_bo", "toan_quoc", "khong_ro"],
  chu_de: ["gia_dinh", "tinh_yeu_hon_nhan", "que_huong_dat_nuoc", "lao_dong_san_xuat", "thien_nhien_thoi_tiet", "ung_xu_dao_duc", "than_phan_con_nguoi", "phe_phan_xa_hoi", "hai_huoc_cham_biem", "hoc_tap_thay_tro", "dia_danh_lich_su"],
  cam_xuc: ["thuong_nho", "biet_on", "tu_hao", "xot_xa", "hon_doi", "vui_tuoi", "che_gieu", "khuyen_ran", "an_ui", "hy_vong", "say_dam", "chua_chat"],
  linh_vuc: ["thoi_tiet", "nong_nghiep", "ung_xu", "gia_dinh", "xa_hoi", "suc_khoe", "kinh_nghiem_song"],
  ket_luan_kiem_chung: ["co_co_so", "mot_phan", "chua_ro", "khong_dung"],
  hinh_thuc_dien_xuong: ["hat_ru", "ho_lao_dong", "hat_doi_dap", "ly", "dong_dao_tro_choi", "noi_thuong", "khong_ro"],
  loai_muc_tu: ["tu_co", "phuong_ngu", "han_viet", "dien_tich", "dia_danh", "nhan_vat", "phong_tuc", "do_vat"],
  loai_nguon: ["sach", "tap_chi", "luan_van", "dien_da", "ghi_am", "web", "dong_gop"],
  ban_quyen: ["cong_cong", "co_ban_quyen", "duoc_phep", "chua_ro"],
  nhom_tinh_huong: ["gia_dinh", "tinh_yeu", "que_huong", "hoc_tap_lao_dong", "ung_xu", "thoi_tiet_mua_vu", "than_phan", "dip_le", "vui"],
  loai_ghi_am: ["doc", "hat_ru", "ho", "ly", "hat_doi_dap", "khac"],
  giay_phep: ["cc_by_4_0", "cc_by_sa_4_0"],
  kieu_hien_thi: ["ten_that", "but_danh", "an_danh"],
  vai_tro: ["bien_soan", "co_van", "dong_gop", "ghi_am", "nguoi_the_hien"],
  sgk_bo_sach: ["ket_noi_tri_thuc", "chan_troi_sang_tao", "canh_dieu"],
} as const;

/** Loại nguồn là nguồn in (sách, tạp chí, luận văn — kể cả bản quét). */
export const LOAI_NGUON_IN: readonly string[] = ["sach", "tap_chi", "luan_van"];
/** Loại nguồn là tư liệu điền dã. */
export const LOAI_NGUON_DIEN_DA: readonly string[] = ["dien_da", "ghi_am"];

export type NhomTuVung = keyof typeof TU_VUNG;
export type MaTuVung<N extends NhomTuVung> = (typeof MA_TU_VUNG)[N][number];

export type TheLoai = MaTuVung<"the_loai">;
export type TheTho = MaTuVung<"the_tho">;
export type TrangThai = MaTuVung<"trang_thai">;
export type XacThuc = MaTuVung<"xac_thuc">;
export type CachDung = MaTuVung<"cach_dung">;
export type XacThucDanGian = MaTuVung<"xac_thuc_dan_gian">;
export type Vung = MaTuVung<"vung">;
export type ChuDe = MaTuVung<"chu_de">;
export type CamXuc = MaTuVung<"cam_xuc">;
export type LinhVuc = MaTuVung<"linh_vuc">;
export type KetLuanKiemChung = MaTuVung<"ket_luan_kiem_chung">;
export type HinhThucDienXuong = MaTuVung<"hinh_thuc_dien_xuong">;
export type LoaiMucTu = MaTuVung<"loai_muc_tu">;
export type LoaiNguon = MaTuVung<"loai_nguon">;
export type BanQuyen = MaTuVung<"ban_quyen">;
export type NhomTinhHuong = MaTuVung<"nhom_tinh_huong">;
export type LoaiGhiAm = MaTuVung<"loai_ghi_am">;
export type GiayPhep = MaTuVung<"giay_phep">;
export type KieuHienThi = MaTuVung<"kieu_hien_thi">;
export type VaiTro = MaTuVung<"vai_tro">;
export type SgkBoSach = MaTuVung<"sgk_bo_sach">;
