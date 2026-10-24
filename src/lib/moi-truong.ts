// Bản đang build là bản chính thức hay bản xem trước (D-050).
// Cloudflare Workers Builds đặt WORKERS_CI_BRANCH = tên nhánh; nhánh main → bản chính thức.
// KHO_MAU=0/1 ghi đè (ví dụ để thử bản chính thức trên máy: KHO_MAU=0 npm run build).

const nhanh = process.env.WORKERS_CI_BRANCH;
const ghiDe = process.env.KHO_MAU;

/** Bản chính thức: chỉ dữ liệu thật trong content/. */
export const LA_BAN_CHINH_THUC = ghiDe === '0' || (ghiDe !== '1' && nhanh === 'main');

/** Bộ mẫu giao diện (dữ liệu giả, lời chữ mẫu) có được dựng thêm không: bản xem trước, CI, máy cục bộ. */
export const CO_BO_MAU = !LA_BAN_CHINH_THUC;

export const THU_MUC_MAU = 'tests/fixtures/mau-giao-dien/content';

/** Mã của bộ mẫu: id tác phẩm cd-91xx/tn-91xx… — dùng để gắn băng "Trang mẫu giao diện". */
export const laMaMau = (id: string) => /^[a-z]+-91\d\d$/.test(id);
