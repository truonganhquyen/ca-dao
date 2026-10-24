// Cấu hình giao diện có thể đổi mà không sửa thành phần.

/** Lục bát: thụt lề câu lục so với câu bát (Q-006 — mặc định thụt lề, hỏi 5 người thử ở Mốc B). */
export const LUC_BAT_THUT_LE = true;

/** Lời dài hơn ngần này dòng thì hiện trước LOI_DAI_HIEN_TRUOC dòng, phần còn lại trong "Xem tiếp". */
export const LOI_DAI_TU = 12;
export const LOI_DAI_HIEN_TRUOC = 8;

/** Form góp ý / gửi số trang (T-045). Chưa có form thì các lời mời hiện dạng chữ, không có liên kết. */
export const LIEN_KET_GOP_Y: string | null = null;

/** Lịch sử sửa đổi của một tệp trên GitHub. */
export const lichSuGitHub = (tep: string) => `https://github.com/truonganhquyen/ca-dao/commits/main/${tep}`;

/** Mục của thanh điều hướng dưới; mục nào chưa có trang thì chưa hiện (Tìm: T-041, Tình huống: T-043, Đã lưu: Mốc C). */
export const MUC_DIEU_HUONG: { nhan: string; bieuTuong: string; href: string; daCo: boolean }[] = [
  { nhan: 'Trang đầu', bieuTuong: '⌂', href: '/', daCo: true },
  { nhan: 'Tìm', bieuTuong: '⌕', href: '/tim/', daCo: false },
  { nhan: 'Tình huống', bieuTuong: '✦', href: '/tinh-huong/', daCo: false },
  { nhan: 'Đã lưu', bieuTuong: '☆', href: '/da-luu/', daCo: false },
];
