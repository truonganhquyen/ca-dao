<?php
/**
 * Kiểm thử V2 trong WordPress Playground (plugin + theme V2 đã bật): `npm run test:v2`.
 * Dữ liệu tổng hợp — không chứa nội dung thật của nguồn (repo này công khai).
 */

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

use Phongdao\DichVu\DungTrang\DuLieuMau;
use Phongdao\DichVu\DungTrang\DuLieuTrangChu;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\GiaoTiep\Khoi\TacPham;
use Phongdao\HaTang\CaiDat;
use Phongdao\HaTang\KhaiBao;
use Phongdao\HaTang\KhoiDong;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\CapChatLuong;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\Phan;
use Phongdao\MienDuLieu\TheLoai;

// Playground không in đầu ra của bước runPHP: ghi từng dòng kết quả ra tệp (kể cả khi lỗi giữa chừng)
const V2_TEP_KQ = '/repo/tests/.ket-qua.txt';
file_put_contents( V2_TEP_KQ, '' );
function ra( string $dong ): void {
	file_put_contents( V2_TEP_KQ, $dong . "\n", FILE_APPEND );
}
register_shutdown_function(
	static function (): void {
		$loi = error_get_last();
		if ( $loi && in_array( $loi['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR ), true ) ) {
			ra( "LỖI PHP: {$loi['message']} ({$loi['file']}:{$loi['line']})" );
		}
	}
);
$GLOBALS['v2_dat']  = 0;
$GLOBALS['v2_hong'] = 0;
function kt( string $ten, bool $dk, $chi_tiet = null ): void {
	if ( $dk ) {
		++$GLOBALS['v2_dat'];
		ra( "ĐẠT   $ten" );
		return;
	}
	++$GLOBALS['v2_hong'];
	ra( "HỎNG  $ten" . ( null !== $chi_tiet ? ' — ' . var_export( $chi_tiet, true ) : '' ) );
}

ra( '# Playground · PHP ' . PHP_VERSION . ' · WordPress ' . get_bloginfo( 'version' ) );

// ---------- Plugin, theme ----------
kt( 'plugin V2 đang bật', is_plugin_active( 'ca-dao/ca-dao.php' ) );
$du_lieu = get_plugin_data( WP_PLUGIN_DIR . '/ca-dao/ca-dao.php', false, false );
kt( 'plugin tên "Phongdao — Thư viện Văn học Dân gian Việt Nam"', 'Phongdao — Thư viện Văn học Dân gian Việt Nam' === $du_lieu['Name'], $du_lieu['Name'] );
kt( 'CADAO_PHIEN_BAN khớp dòng Version: của plugin', CADAO_PHIEN_BAN === $du_lieu['Version'], array( CADAO_PHIEN_BAN, $du_lieu['Version'] ) );
kt( 'theme Phongdao đang dùng, là theme khối', 'Phongdao' === wp_get_theme()->get( 'Name' ) && wp_is_block_theme() );

// ---------- Module ----------
kt( 'plugins_loaded đã chạy, module đã đăng ký', did_action( 'plugins_loaded' ) > 0 );
$khong_dung = array_values( array_filter( KhoiDong::MODULE, static fn( string $lop ): bool => ! is_subclass_of( $lop, \Phongdao\HaTang\Module::class ) ) );
kt( 'mọi module trong KhoiDong cài đặt HaTang\Module', array() === $khong_dung, $khong_dung );
kt( 'kích hoạt ghi phiên bản lược đồ', CaiDat::PHIEN_BAN_CSDL === (int) get_option( CaiDat::TUY_CHON ), get_option( CaiDat::TUY_CHON ) );

// ---------- REST phongdao/v1 ----------
$may_chu = rest_get_server();
kt( 'route phongdao/v1/suc-khoe đã đăng ký', isset( $may_chu->get_routes()['/phongdao/v1/suc-khoe'] ) );
wp_set_current_user( 0 );
$tl = rest_do_request( new WP_REST_Request( 'GET', '/phongdao/v1/suc-khoe' ) );
kt( 'khách không đọc được suc-khoe (kiểm quyền)', in_array( $tl->get_status(), array( 401, 403 ), true ), $tl->get_status() );
$quan_tri = get_users( array( 'role' => 'administrator', 'number' => 1 ) )[0] ?? null;
wp_set_current_user( $quan_tri ? $quan_tri->ID : 0 );
$tl = rest_do_request( new WP_REST_Request( 'GET', '/phongdao/v1/suc-khoe' ) );
kt( 'quản trị đọc được suc-khoe, đúng phiên bản', 200 === $tl->get_status() && CADAO_PHIEN_BAN === ( $tl->get_data()['phien_ban'] ?? null ), $tl->get_data() );

// ---------- Menu Phongdao (D-094, D-100: xếp theo việc hằng ngày) ----------
global $menu, $submenu;
$menu    = is_array( $menu ) ? $menu : array();
$submenu = is_array( $submenu ) ? $submenu : array();
( new \Phongdao\GiaoTiep\QuanTri\Module() )->them_menu();
$BLV = \Phongdao\GiaoTiep\QuanTri\BanLamViec::TRANG;
kt( 'menu quản trị có mục Phongdao, bấm vào mở Hôm nay', in_array( $BLV, array_column( $menu, 2 ), true ) );
kt( 'menu Phongdao theo ba dòng việc: Hôm nay · Bài mới · Hộp việc · Bổ sung · Tác phẩm · Nguồn & cài đặt (Quản trị); Sửa tác phẩm, Thao tác hàng loạt không nằm trên menu', array( $BLV, \Phongdao\GiaoTiep\QuanTri\DanhSachKho::TRANG, \Phongdao\GiaoTiep\QuanTri\DanhSachViec::TRANG, \Phongdao\GiaoTiep\QuanTri\BoSung::TRANG, \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::TRANG, 'phongdao' ) === array_column( $submenu[ $BLV ] ?? array(), 2 ) && array( 'Hôm nay', 'Bài mới' ) === array_slice( array_column( $submenu[ $BLV ] ?? array(), 0 ), 0, 2 ) && 'Bổ sung' === ( $submenu[ $BLV ][3][0] ?? '' ), array_column( $submenu[ $BLV ] ?? array(), 2 ) );
kt( 'Thao tác hàng loạt vẫn mở được bằng đường dẫn cũ (màn con)', false !== has_action( get_plugin_page_hookname( \Phongdao\GiaoTiep\QuanTri\TrangLo::TRANG, '' ) ) );
kt( 'menu wp-admin có trang Tác phẩm, Hộp việc thật; Sửa tác phẩm vẫn mở được (màn con)', false !== has_action( get_plugin_page_hookname( \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::TRANG, $BLV ) ) && false !== has_action( get_plugin_page_hookname( \Phongdao\GiaoTiep\QuanTri\DanhSachViec::TRANG, $BLV ) ) && false !== has_action( get_plugin_page_hookname( \Phongdao\GiaoTiep\QuanTri\SuaTacPham::TRANG, '' ) ) );
$muc_hv = array_values( array_filter( $submenu[ $BLV ] ?? array(), static fn( array $m ): bool => \Phongdao\GiaoTiep\QuanTri\DanhSachViec::TRANG === $m[2] ) )[0][0] ?? '';
kt( 'mục Hộp việc trên menu kèm số việc đang chờ', ( 0 === Viec::dem_hop_viec() && 'Hộp việc' === $muc_hv ) || str_contains( $muc_hv, 'awaiting-mod' ), $muc_hv );
$ql_dn = wp_get_current_user();
kt( 'đăng nhập xong người biên soạn vào Bàn làm việc; có đích riêng thì giữ; người không có quyền biên soạn không đổi', str_contains( \Phongdao\GiaoTiep\QuanTri\Module::sau_dang_nhap( admin_url(), '', $ql_dn ), 'page=' . $BLV ) && str_contains( \Phongdao\GiaoTiep\QuanTri\Module::sau_dang_nhap( admin_url(), admin_url(), $ql_dn ), 'page=' . $BLV ) && admin_url( 'edit.php' ) === \Phongdao\GiaoTiep\QuanTri\Module::sau_dang_nhap( admin_url( 'edit.php' ), admin_url( 'edit.php' ), $ql_dn ) && admin_url() === \Phongdao\GiaoTiep\QuanTri\Module::sau_dang_nhap( admin_url(), '', new WP_User( 0 ) ) );
// Như WordPress chạy: bộ lọc parent_file rồi get_admin_page_parent() (màn ẩn nằm trong $submenu['']) — menu phải còn mở
require_once ABSPATH . 'wp-admin/includes/plugin.php';
global $pagenow, $plugin_page, $parent_file;
$cu_mn = array( $pagenow, $plugin_page, $parent_file, $submenu );
$pagenow     = 'admin.php';
$plugin_page = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::TRANG;
$_GET        = array( 'page' => $plugin_page );
$parent_file = \Phongdao\GiaoTiep\QuanTri\Module::menu_dang_mo( '' );
get_admin_page_parent();
$cha_wp      = $parent_file;
[ $pagenow, $plugin_page, $parent_file, $submenu ] = $cu_mn;
kt( 'Sửa tác phẩm: sau bước tìm cha của WordPress, menu Phongdao vẫn là mục cha (không bị đặt lại thành rỗng)', $BLV === $cha_wp, $cha_wp );
$_GET = array( 'page' => \Phongdao\GiaoTiep\QuanTri\SuaTacPham::TRANG );
kt( 'mở Sửa tác phẩm thì menu Phongdao mở, tô mục Tác phẩm', \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::TRANG === \Phongdao\GiaoTiep\QuanTri\Module::muc_dang_mo( null ) && $BLV === \Phongdao\GiaoTiep\QuanTri\Module::menu_dang_mo( '' ) );
$lo_menu = Viec::tao( array( 'loai' => Viec::LO, 'trang_thai' => Viec::LO_XEM, 'doi_tuong_loai' => 'lo', 'doi_tuong_id' => 0, 'noi_dung' => array( 'thao_tac' => 'kho_dang', 'doi_tuong' => 'kho', 'muc' => array(), 'bo' => array() ), 'nguoi_tao' => (string) get_current_user_id() ) );
$_GET = array( 'page' => \Phongdao\GiaoTiep\QuanTri\TrangLo::TRANG, 'lo' => (string) $lo_menu );
$mo_lo_kho = \Phongdao\GiaoTiep\QuanTri\Module::muc_dang_mo( null );
$mo_lo_cha = \Phongdao\GiaoTiep\QuanTri\Module::menu_dang_mo( '' );
$_GET = array( 'page' => \Phongdao\GiaoTiep\QuanTri\TrangLo::TRANG );
$mo_lo_ds = \Phongdao\GiaoTiep\QuanTri\Module::muc_dang_mo( null );
$_GET = array( 'page' => \Phongdao\GiaoTiep\QuanTri\DanhSachKho::TRANG );
$mo_kho = \Phongdao\GiaoTiep\QuanTri\Module::muc_dang_mo( 'x' );
Viec::xoa( (int) $lo_menu );
kt( 'mở màn xem trước lô từ Kho thì menu Phongdao mở, tô Bài mới; danh sách các lô tô Hôm nay; màn có trên menu không đổi', $BLV === $mo_lo_cha && \Phongdao\GiaoTiep\QuanTri\DanhSachKho::TRANG === $mo_lo_kho && $BLV === $mo_lo_ds && 'x' === $mo_kho, array( $mo_lo_kho, $mo_lo_ds, $mo_kho ) );
$_GET = array();

// ---------- Mô hình dữ liệu: loại bài, phân loại, phần của bài, bảng việc (D-094, docs/26 mục 4) ----------
$thieu = array_values( array_filter( array( KhaiBao::TAC_PHAM, KhaiBao::TUYEN_TAP, KhaiBao::PHAN_TICH, KhaiBao::GOP_Y, KhaiBao::HO_SO_NGUON ), static fn( $l ) => ! post_type_exists( $l ) ) );
kt( 'đủ loại bài: tác phẩm, tuyển tập, bài phân tích, góp ý, hồ sơ nguồn', array() === $thieu, $thieu );
kt( 'tác phẩm: không trình soạn khối, không REST mặc định, không bản sửa (lịch sử ở bảng việc)', ! post_type_supports( KhaiBao::TAC_PHAM, 'editor' ) && ! get_post_type_object( KhaiBao::TAC_PHAM )->show_in_rest && ! post_type_supports( KhaiBao::TAC_PHAM, 'revisions' ) );
kt( 'hồ sơ nguồn, góp ý không công khai', ! get_post_type_object( KhaiBao::HO_SO_NGUON )->public && ! get_post_type_object( KhaiBao::GOP_Y )->public );
$thieu = array_values( array_filter( array_keys( KhaiBao::PHAN_LOAI ), static fn( $t ) => ! is_object_in_taxonomy( KhaiBao::TAC_PHAM, $t ) ) );
kt( 'đủ 7 phân loại của tác phẩm (thể loại, chủ đề, vùng, mô-típ, hình thức, mục từ, trò chơi)', array() === $thieu, $thieu );
$thieu = array_values( array_filter( array_keys( TheLoai::DS ), static fn( $ma ) => ! term_exists( $ma, KhaiBao::THE_LOAI ) ) );
kt( 'đã tạo 5 thể loại cố định', array() === $thieu, $thieu );
$meta  = get_registered_meta_keys( 'post', KhaiBao::TAC_PHAM );
$thieu = array_values( array_filter( Phan::ds( 'tac_pham' ), static fn( $p ) => ! isset( $meta[ Phan::khoa( $p ) ] ) || false !== $meta[ Phan::khoa( $p ) ]['show_in_rest'] ) );
kt( 'mọi phần của tác phẩm có khoá meta đã khai báo, không mở qua REST', array() === $thieu, $thieu );
global $wpdb;
$wpdb->suppress_errors( true );
$dem_bang = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Viec::bang() );
$wpdb->suppress_errors( false );
kt( 'đã tạo bảng wp_cadao_viec', null !== $dem_bang, $wpdb->last_error );

$id = wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_title' => 'Dòng mẫu một', 'post_name' => 'dong-mau-mot', 'post_status' => 'publish' ) );
wp_set_object_terms( $id, 'ca-dao', KhaiBao::THE_LOAI );
clean_post_cache( $id );
$url = (string) get_permalink( $id );
kt( 'đường dẫn tác phẩm /<thể loại>/<tên>/', str_ends_with( $url, '/ca-dao/dong-mau-mot/' ), $url );
kt( 'đường dẫn tác phẩm mở đúng bài (luật viết lại)', $id === url_to_postid( $url ), url_to_postid( $url ) );

$ai_ql  = $quan_tri ? $quan_tri->ID : 1;
$nc     = array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI );
$loi    = array( 'dong' => array( 'Dòng mẫu một của bài', 'Có "ngoặc" và \\ gạch, <b>thẻ</b>' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET );
$kq     = LuuPhan::luu( $id, 'loi', $loi, $nc );
$da_luu = PhanBai::lay( $id, 'loi' );
kt( 'lưu lời: vào bài, giữ đúng từng ký tự (cả ", \\, <)', $kq['ok'] && $kq['doi'] && $loi['dong'] === ( $da_luu['dong'] ?? null ), array( $kq, $da_luu ) );
kt( 'lưu lời: ghi người sửa, giờ sửa', $ai_ql === ( $da_luu['sua_boi'] ?? null ) && (bool) preg_match( '/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', (string) ( $da_luu['sua_luc'] ?? '' ) ) );
$nk = Viec::lay( $kq['viec'] );
kt( 'mỗi lần lưu một dòng nhật ký: phần, trước (chưa có), sau, người', $nk && Viec::SUA === $nk['loai'] && Viec::XONG === $nk['trang_thai'] && 'loi' === $nk['phan'] && null === $nk['truoc'] && $loi['dong'] === $nk['sau']['dong'] && $ai_ql === $nk['nguoi_quyet'], $nk );
$than = (string) get_post_field( 'post_content', $id );
kt( 'bản chữ post_content có lời, chữ được thoát', str_contains( $than, 'Dòng mẫu một của bài' ) && ! str_contains( $than, '<b>' ), $than );

$dem = count( Viec::lich_su( 'post', $id ) );
$kq  = LuuPhan::luu( $id, 'loi', $loi, $nc );
kt( 'lưu lại y như cũ: không ghi, không thêm nhật ký', $kq['ok'] && ! $kq['doi'] && count( Viec::lich_su( 'post', $id ) ) === $dem );
$yn_ai = array( 'chu' => 'Ý nghĩa mẫu.', 'ai' => true, 'nguoi_lam' => NguoiLam::AI_DA_DUYET );
$kq    = LuuPhan::luu( $id, 'y_nghia', $yn_ai, $nc );
kt( 'ý nghĩa có hỗ trợ AI, không có quyền duyệt: không vào bài, không ghi nhật ký', ! $kq['ok'] && null === PhanBai::lay( $id, 'y_nghia' ) && count( Viec::lich_su( 'post', $id ) ) === $dem, $kq );
$kq_duyet = LuuPhan::luu( $id, 'y_nghia', $yn_ai, array( 'duyet' => true ) + $nc );
$yn       = PhanBai::lay( $id, 'y_nghia' );
kt( 'người có quyền duyệt bấm: vào bài, ghi người duyệt và giờ duyệt', $kq_duyet['ok'] && $ai_ql === ( $yn['duyet_boi'] ?? null ) && ! empty( $yn['duyet_luc'] ), $yn );
kt( 'cấp chất lượng: lời + thể loại + ý nghĩa = L2', 2 === CapChatLuong::tinh( PhanBai::tat_ca( $id, KhaiBao::TAC_PHAM ), 'ca-dao' ) );

$kq = LuuPhan::luu( $id, 'y_nghia', array( 'chu' => 'Ý nghĩa sửa.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc );
$ht = LuuPhan::khoi_phuc( $kq['viec'], array( 'duyet' => true ) + $nc );
kt( 'hoàn tác: về đúng bản trước (kể cả người duyệt), ghi thêm một dòng "khoi_phuc"', $ht['ok'] && $yn === PhanBai::lay( $id, 'y_nghia' ) && 'khoi_phuc' === ( Viec::lay( $ht['viec'] )['quyet'] ?? '' ), $ht );
$kq = LuuPhan::luu( $id, 'y_nghia', array( 'chu' => 'Ý nghĩa sửa lần hai.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc );
$ht = LuuPhan::khoi_phuc( $kq['viec'], $nc );
kt( 'khôi phục bản có hỗ trợ AI cũng cần quyền duyệt', ! $ht['ok'] && 'Ý nghĩa sửa lần hai.' === ( PhanBai::lay( $id, 'y_nghia' )['chu'] ?? '' ), $ht );
$kq = LuuPhan::luu( $id, 'y_nghia', null, $nc );
$nk = Viec::lay( $kq['viec'] );
kt( 'xoá một phần: bỏ khỏi bài, nhật ký ghi "xoa" kèm bản trước', $kq['ok'] && null === PhanBai::lay( $id, 'y_nghia' ) && 'xoa' === $nk['quyet'] && 'Ý nghĩa sửa lần hai.' === ( $nk['truoc']['chu'] ?? '' ), $nk );

$kq = LuuPhan::luu( $id, 'chu_thich', array( array( 'tu' => 'mẫu', 'loi_giai' => 'Lời giải mẫu.', 'nguoi_lam' => NguoiLam::MAY_CHEP, 'nguon' => array( 'ho_so' => 1, 'trang' => '3' ) ) ), array( 'nguoi' => 0, 'goi' => KiemPhan::GOI_MAY ) );
kt( 'máy điền trang của nguồn → không lưu', ! $kq['ok'] && null === PhanBai::lay( $id, 'chu_thich' ), $kq );
$kq = LuuPhan::luu( $id, 'nguon_may', array( 'nguon_id' => 'nguon-mau', 'url_goc' => 'https://example.org/goc' ), array( 'nguoi' => 0, 'goi' => KiemPhan::GOI_MAY ) );
kt( 'máy ghi địa chỉ gốc đã lấy (nội bộ), không vào bản chữ', $kq['ok'] && ! str_contains( (string) get_post_field( 'post_content', $id ), 'example.org' ), $kq );
kt( 'phần riêng của thể loại khác → không lưu (nghĩa đen cho ca dao)', ! LuuPhan::luu( $id, 'nghia_den', array( 'chu' => 'x', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc )['ok'] );
$trang = wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'Trang mẫu', 'post_status' => 'publish' ) );
kt( 'trang tĩnh không có phần → không lưu', ! LuuPhan::luu( $trang, 'loi', $loi, $nc )['ok'] );

kt( 'lần sửa trực tiếp không thành việc chờ', array() === Viec::ds_cho() );
$v   = Viec::tao( array( 'loai' => 'nhap_y_nghia', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $id, 'phan' => 'y_nghia', 'noi_dung' => array( 'nhap' => 'Nháp “mẫu” \\ ổn' ), 'nguoi_tao' => 'may', 'uu_tien' => 5 ) );
$cho = Viec::ds_cho();
kt( 'việc chờ: thêm và đọc lại đúng (chữ có dấu, ngoặc, dấu \\)', $v > 0 && 1 === count( $cho ) && 'Nháp “mẫu” \\ ổn' === ( $cho[0]['noi_dung']['nhap'] ?? '' ), $cho );
kt( 'nháp ở bảng việc không vào bài', null === PhanBai::lay( $id, 'y_nghia' ) );

// ---------- Theme ----------
$mau = array_map( static fn( $t ) => $t->slug, get_block_templates() );
kt( 'theme có mẫu index và page', in_array( 'index', $mau, true ) && in_array( 'page', $mau, true ), $mau );
$dau = do_blocks( '<!-- wp:template-part {"slug":"dau-trang","theme":"ca-dao","tagName":"div"} /-->' );
kt( 'phần đầu trang (template part) hiện logo Phongdao, không lồng hai thẻ header', str_contains( $dau, '<span>Phongdao</span>' ) && 1 === substr_count( $dau, '<header' ), substr( $dau, 0, 200 ) );
kt( 'không in mã emoji (không gọi máy chủ ngoài)', false === has_action( 'wp_head', 'print_emoji_detection_script' ) );

// ---------- Giao diện người đọc: khối cadao/* ----------
$dang_ky = WP_Block_Type_Registry::get_instance();
$co_khoi = array_values( array_filter( array( 'cadao/dau-trang', 'cadao/chan-trang', 'cadao/tac-pham', 'cadao/danh-sach' ), static fn( $k ) => ! $dang_ky->is_registered( $k ) ) );
kt( 'đã đăng ký khối cadao/dau-trang, cadao/chan-trang, cadao/tac-pham, cadao/danh-sach', array() === $co_khoi, $co_khoi );
$mau  = array_map( static fn( $t ) => $t->slug, get_block_templates() );
$thieu = array_values( array_diff( array( 'front-page', 'single-tac_pham', 'archive-tac_pham', 'taxonomy' ), $mau ) );
kt( 'theme có mẫu trang chủ, trang tác phẩm, Tất cả, trang phân loại', array() === $thieu, $thieu );

wp_set_current_user( 0 );
$h    = do_blocks( '<!-- wp:cadao/tac-pham {"mau":"ca-dao"} /-->' );
$chinh = (string) strstr( (string) strstr( $h, 'class="work-main"' ), 'class="work-rail', true );
kt( 'trang ca dao: một h1 đúng tên bài', 1 === substr_count( $h, '<h1' ) && str_contains( $h, '<h1 class="read-title">Dòng mẫu một của bài</h1>' ) );
kt( 'thẻ lời: 4 câu, mỗi câu một .line, có data-fit và --dai (mỗi câu một dòng)', 4 === substr_count( $chinh, 'class="line"' ) && str_contains( $chinh, 'data-fit' ) && str_contains( $chinh, '--dai:' ) );
kt( 'từ có chú thích “chút” là liên kết tới khối Từ khó', str_contains( $chinh, '<a class="word" href="#chu-thich"' ) );
kt( 'nút Sao chép trong thẻ lời có đường dẫn bài', str_contains( $chinh, 'data-action="copy-loi"' ) && str_contains( $chinh, 'data-url="' ) );
kt( 'nhãn mức kiểm “Bản lưu truyền phổ biến” + ⓘ (không tự đặt “đã đối chiếu”)', str_contains( $chinh, 'Bản lưu truyền phổ biến' ) && str_contains( $chinh, 'class="vi-sao"' ) && ! str_contains( $chinh, 'pill checked' ) );
kt( 'khối Ý nghĩa có ghi chú hỗ trợ AI đã duyệt', str_contains( $chinh, '<h2>Ý nghĩa</h2>' ) && str_contains( $chinh, 'Có hỗ trợ của AI, đã được người biên soạn duyệt.' ) );
$moi_id = array_map( static fn( $id ) => substr_count( $h, 'id="' . $id . '"' ), array( 'chu-thich' => 'chu-thich', 'di-ban' => 'di-ban', 'tu-lieu' => 'tu-lieu', 'anh-chia-se' => 'anh-chia-se' ) );
kt( 'có đúng một khối Từ khó, Dị bản, Lời này từ đâu?, Ảnh để chia sẻ', array( 1, 1, 1, 1 ) === array_values( $moi_id ), $moi_id );
kt( 'ảnh chia sẻ: 3 khổ, 3 nền, có ô Kèm ý nghĩa', 3 === substr_count( $h, 'name="kho-mau-ca-dao"' ) && 3 === substr_count( $h, 'name="nen-mau-ca-dao"' ) && str_contains( $h, 'name="yn-mau-ca-dao"' ) );
kt( 'công cụ đọc: Trích dẫn, Lưu, Chia sẻ, Trình chiếu, Cỡ chữ', str_contains( $h, 'data-action="cite"' ) && str_contains( $h, 'data-action="save"' ) && str_contains( $h, 'data-action="share"' ) && str_contains( $h, 'data-action="present"' ) );
kt( 'người đọc không nhận giao diện sửa, không thấy nguồn đã ẩn', ! preg_match( '/nut-sua|editor-strip|dai-bien-tap|tam-sua|Nguồn đã ẩn:/', $h ) );

$tn = do_blocks( '<!-- wp:cadao/tac-pham {"mau":"tuc-ngu"} /-->' );
kt( 'tục ngữ: câu là tiêu đề, dạng câu một dòng, có khối Nghĩa (Nghĩa đen · Nghĩa bóng · Dùng khi)', str_contains( $tn, '<h1 class="read-title"><span class="verse-box"><span class="verse proverb"' ) && str_contains( $tn, 'id="nghia"><h2>Nghĩa</h2>' ) && str_contains( $tn, '<h3>Dùng khi</h3>' ) );
kt( 'tục ngữ không có dị bản, từ khó thì không hiện khối (không độn)', ! str_contains( $tn, 'id="di-ban"' ) && ! str_contains( $tn, 'id="chu-thich"' ) );
kt( 'mã mẫu lạ: khối không in gì', '' === trim( do_blocks( '<!-- wp:cadao/tac-pham {"mau":"khong-co"} /-->' ) ) );

$doc = DuLieuMau::lay( 'ca-dao' );
$doc['tieu_de']           = '<script>alert(1)</script>';
$doc['loi'][0]            = '<img src=x onerror=alert(1)>';
$doc['y_nghia']['chu']    = '"><script>alert(2)</script>';
$doc['chu_thich'][0]['tu'] = '<b>chút</b>';
$doc['url']               = 'javascript:alert(3)';
$doc_hai                  = TacPham::hien( $doc );
kt( 'chữ lạ trong dữ liệu được thoát: không có thẻ script, img, đường dẫn javascript:', ! preg_match( '/<script|<img|javascript:/i', $doc_hai ) && str_contains( $doc_hai, '&lt;script&gt;' ) );

$dau = do_blocks( '<!-- wp:cadao/dau-trang /-->' );
kt( 'đầu trang: logo, ô tìm tới /tim/, thanh thể loại 8 mục, nút sáng / tối', str_contains( $dau, 'class="brand"' ) && str_contains( $dau, 'action="' . esc_url( home_url( '/tim/' ) ) . '"' ) && 8 === substr_count( (string) strstr( $dau, 'genre-bar-inner' ), '<a ' ) && str_contains( $dau, 'data-action="theme"' ) );
$chan = do_blocks( '<!-- wp:cadao/chan-trang /-->' );
kt( 'chân trang + thanh dưới điện thoại 4 mục', str_contains( $chan, 'class="site-footer"' ) && 4 === substr_count( (string) strstr( $chan, 'bottom-nav' ), '<a ' ) );

// ---------- Trang đọc từ dữ liệu thật (các phần đã lưu) ----------
$mau_id = \Phongdao\DichVu\DungTrang\GieoMau::gieo( $ai_ql );
kt( 'tạo 6 tác phẩm mẫu tổng hợp qua đường lưu có kiểm lằn ranh', 6 === count( array_filter( $mau_id ) ), $mau_id );
kt( 'máy gắn thể thơ khi lưu lời: lục bát, câu bốn chữ', has_term( 'luc-bat', 'hinh_thuc', $mau_id['dong-mau-mot-cua-bai'] ) && has_term( 'bon-chu', 'hinh_thuc', $mau_id['dong-dao-mau'] ) );
LuuPhan::luu( $mau_id['dong-mau-mot-cua-bai'], 'nguon_may', array( 'nguon_id' => 'nguon-mau', 'url_goc' => 'https://nguon-an.example/bai-goc' ), array( 'nguoi' => 0, 'goi' => KiemPhan::GOI_MAY ) );
/** Vẽ khối như khi khách mở trang của một bài (truy vấn chính = bài đó). */
$xem = static function ( int $id ): string {
	$GLOBALS['wp_query']     = new WP_Query( array( 'p' => $id, 'post_type' => KhaiBao::TAC_PHAM ) );
	$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
	$GLOBALS['post']         = $GLOBALS['wp_query']->post;
	return do_blocks( '<!-- wp:cadao/tac-pham /-->' );
};
wp_set_current_user( 0 );
$h = $xem( $mau_id['dong-mau-mot-cua-bai'] );
kt( 'trang bài thật: tiêu đề, 4 câu lục bát, nhãn "Ca dao · Lục bát"', str_contains( $h, '<h1 class="read-title">Dòng mẫu một của bài</h1>' ) && 4 === substr_count( (string) strstr( (string) strstr( $h, 'class="work-main"' ), 'class="work-rail', true ), 'class="line"' ) && str_contains( $h, 'verse lucbat' ) && str_contains( $h, 'Ca dao · Lục bát' ), substr( $h, 0, 600 ) );
kt( 'trang bài thật: Ý nghĩa có ghi chú hỗ trợ AI đã duyệt', str_contains( $h, 'Một câu giải nghĩa tổng hợp để xem khối Ý nghĩa.' ) && str_contains( $h, 'Có hỗ trợ của AI, đã được người biên soạn duyệt.' ) );
kt( 'trang bài thật: Từ khó 2 mục, từ "chút" trong lời dẫn tới Từ khó', 2 === substr_count( $h, '<div class="term">' ) && str_contains( $h, '<a class="word" href="#chu-thich"' ) );
kt( 'trang bài thật: dị bản gạch dưới chữ khác', str_contains( $h, '<ins>khác</ins>' ) );
kt( '"Lời này từ đâu?": tách chú thích chép từ nguồn (nguồn ẩn) với chú thích người viết', str_contains( $h, '1 mục chép từ nguồn tham khảo · nguồn đã ẩn' ) && str_contains( $h, '1 mục người biên soạn viết' ) );
kt( 'trang bài thật: chủ đề dẫn tới trang chủ đề, nút Sao chép mang đường dẫn bài', str_contains( $h, 'href="' . esc_url( (string) get_term_link( 'lao-dong', 'chu_de' ) ) . '"' ) && str_contains( $h, 'data-url="' . esc_url( (string) get_permalink( $mau_id['dong-mau-mot-cua-bai'] ) ) . '"' ) );
kt( 'khách không nhận địa chỉ gốc máy đã lấy, người làm, giao diện sửa', ! str_contains( $h, 'nguon-an.example' ) && ! str_contains( $h, 'may_chep' ) && ! preg_match( '/nut-sua|dai-bien-tap|tam-sua/', $h ) );
kt( 'chưa đối chiếu sách → nhãn "Bản lưu truyền phổ biến"', str_contains( $h, 'Bản lưu truyền phổ biến' ) && ! str_contains( $h, 'pill checked' ) );
$h = $xem( $mau_id['cau-do-mau'] );
kt( 'câu đố: Gợi ý và "Hiện đáp án" thu gọn (tắt JavaScript vẫn mở được), không nhắc thiếu ý nghĩa', str_contains( $h, '<details class="answer"><summary>Hiện đáp án</summary>' ) && str_contains( $h, '<summary>Gợi ý</summary>' ) && ! str_contains( $h, 'Bài đang được bổ sung ý nghĩa' ) && ! str_contains( $h, 'Câu này chưa có lời giải nghĩa' ) && ! str_contains( $h, 'data-tam="y_nghia"' ) );
wp_set_current_user( $ai_ql );
$get_truoc = $_GET;
$_GET = array( 'muc' => 'thieu_giai_thich' );
$ds_thieu_cau_do = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::hien();
$_GET = $get_truoc;
$h_sua_cau_do = $xem( $mau_id['cau-do-mau'] );
kt( 'câu đố từ danh sách "Câu đố thiếu giải thích đáp án" (wp-admin Tác phẩm) mở được nút Thêm giải thích đáp án khi người biên soạn sửa', str_contains( $ds_thieu_cau_do, esc_html( get_the_title( $mau_id['cau-do-mau'] ) ) ) && str_contains( $h_sua_cau_do, 'data-tam="y_nghia"' ) && str_contains( $h_sua_cau_do, 'Thêm giải thích đáp án' ) );
wp_set_current_user( 0 );
$h = $xem( $mau_id['tuc-ngu-mau-ngan'] );
kt( 'tục ngữ thật: câu là tiêu đề, khối Nghĩa có Nghĩa bóng (hiện to nhất), Dùng khi 2 ví dụ', str_contains( $h, '<h1 class="read-title"><span class="verse-box"><span class="verse proverb"' ) && str_contains( $h, 'id="nghia"><h2>Nghĩa</h2>' ) && str_contains( $h, '<div class="nghia-bong"><h3>Nghĩa bóng</h3>' ) && 2 === substr_count( (string) strstr( (string) strstr( $h, '<ul class="uses">' ), '</ul>', true ), '<li>' ) );
$h = $xem( $mau_id['cau-mau-thu-hai'] );
$ch_form = (string) strstr( (string) strstr( $h, '<form id="cach-hieu-form"' ), '</form>', true );
kt( 'bài chưa có ý nghĩa: mời "Bạn hiểu câu này thế nào?" kèm nút "Chia sẻ cách hiểu" tới form nhanh ở tầng Hiểu thêm; không tự điền', str_contains( $h, 'Câu này chưa có lời giải nghĩa. Bạn hiểu câu này thế nào? Viết vài dòng để người đọc sau hiểu rõ hơn.' ) && str_contains( $h, 'href="#cach-hieu-form"' ) && str_contains( $h, 'Chia sẻ cách hiểu' ) && '' !== $ch_form && ! str_contains( $h, 'Bài đang được bổ sung ý nghĩa' ) && ! str_contains( $h, 'class="meaning"' ) );
kt(
	'form "Chia sẻ cách hiểu": gửi admin-post có nonce, một ô chữ tối đa 1.000 ký tự, bút danh tuỳ chọn, cam kết tự viết và đồng ý CC BY 4.0 bắt buộc, ô bẫy; không có ô tên thật, email, số điện thoại, nơi nghe',
	str_contains( $ch_form, 'action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"' ) && str_contains( $ch_form, 'name="_wpnonce"' ) && str_contains( $ch_form, 'name="loai" value="cach_hieu"' ) && str_contains( $ch_form, 'name="bai" value="' . $mau_id['cau-mau-thu-hai'] . '"' )
	&& str_contains( $ch_form, 'name="noi_dung" rows="4" maxlength="1000" required' ) && str_contains( $ch_form, 'name="ten" maxlength="80"' ) && str_contains( $ch_form, 'name="tu_viet" value="1" required' ) && str_contains( $ch_form, 'name="dong_y" value="1" required' ) && str_contains( $ch_form, 'CC BY 4.0' ) && str_contains( $ch_form, 'name="web"' )
	&& ! preg_match( '/type="email"|type="tel"|name="email"|name="noi_nghe"|name="nguoi_ke"/', $ch_form ),
	$ch_form
);
$vi_tri = static fn( string $kim ): int => (int) strpos( $h, $kim );
kt(
	'trang bài ba tầng: Đọc (lời, Ý nghĩa, Từ khó, Dị bản) → Hiểu thêm (Cách hiểu của bạn đọc · Bài cảm nhận · Cảm nghĩ, mỗi phần có số và nút viết) → Đọc thêm (phân loại, Đọc tiếp, Lời này từ đâu?, ảnh chia sẻ) → Bạn muốn góp gì?',
	$vi_tri( 'class="work-main"' ) < $vi_tri( 'id="hieu-them"' ) && $vi_tri( 'id="hieu-them"' ) < $vi_tri( 'id="doc-them"' ) && $vi_tri( 'id="doc-them"' ) < $vi_tri( 'class="work-tags"' ) && $vi_tri( 'class="work-tags"' ) < $vi_tri( 'id="tu-lieu"' ) && $vi_tri( 'id="tu-lieu"' ) < $vi_tri( 'id="gop"' )
	&& 3 === substr_count( $h, 'class="ht-phan"' ) && str_contains( $h, '<h3 class="ht-ten">Cách hiểu của bạn đọc <span class="ht-so">0</span></h3>' ) && str_contains( $h, '<h3 class="ht-ten">Bài cảm nhận <span class="ht-so">' ) && str_contains( $h, '<h3 class="ht-ten">Cảm nghĩ <span class="ht-so">' )
	&& str_contains( $h, 'role="tablist" aria-label="Hiểu thêm" data-chi-js hidden' ) && str_contains( $h, 'loai=phan_tich&#038;bai=' . $mau_id['cau-mau-thu-hai'] ) && str_contains( $h, 'loai=cam_nghi&#038;bai=' . $mau_id['cau-mau-thu-hai'] ) && str_contains( $h, 'Viết cảm nhận' ) && str_contains( $h, 'Viết cảm nghĩ' )
);
$h_doc = $xem( $mau_id['dong-mau-mot-cua-bai'] );
kt( 'tầng Đọc: Từ khó, Dị bản ngay sau lời, trước Hiểu thêm', (int) strpos( $h_doc, 'id="chu-thich"' ) < (int) strpos( $h_doc, 'id="di-ban"' ) && (int) strpos( $h_doc, 'id="di-ban"' ) < (int) strpos( $h_doc, 'id="hieu-them"' ) && (int) strpos( $h_doc, 'class="work-main"' ) < (int) strpos( $h_doc, 'id="chu-thich"' ) );
$h_tn = $xem( $mau_id['tuc-ngu-mau-ngan'] );
kt( 'tục ngữ: khối Nghĩa (Nghĩa đen · Nghĩa bóng · Dùng khi) thuộc tầng Đọc, trước Hiểu thêm', str_contains( $h_tn, 'id="nghia"' ) && (int) strpos( $h_tn, 'id="nghia"' ) < (int) strpos( $h_tn, 'id="hieu-them"' ) && (int) strpos( $h_tn, 'Dùng khi' ) < (int) strpos( $h_tn, 'id="hieu-them"' ) && str_contains( $h_tn, 'id="hieu-them"' ) );
$HT = \Phongdao\GiaoTiep\Khoi\HieuThem::class;
$c_gia = array( 'id' => 1, 'ten' => 'Bạn đọc', 'chu' => 'Chữ', 'ngay' => '01/01/2026' );
$ht_mo = $HT::hien( $mau_id['cau-mau-thu-hai'], array( 'cach_hieu' => array(), 'cam_nghi' => array( $c_gia, $c_gia ) ) );
$ht_gui = $HT::hien( $mau_id['cau-mau-thu-hai'], array( 'cach_hieu' => array(), 'cam_nghi' => array( $c_gia, $c_gia ) ), array( 'gui' => 'da_gui' ) );
$ht_loi = $HT::hien( $mau_id['cau-mau-thu-hai'], array(), array( 'gui' => 'loi', 'ma' => 'thieu_ch' ) );
kt( 'Hiểu thêm: mở sẵn phần nhiều bài nhất (Cảm nghĩ), vừa gửi cách hiểu thì mở phần Cách hiểu kèm lời cảm ơn; gửi thiếu thì báo đúng lỗi cạnh form', str_contains( $ht_mo, 'data-mo="2"' ) && str_contains( $ht_mo, 'id="ht-nut-cam-nghi" aria-controls="cam-nghi" aria-selected="true"' ) && str_contains( $ht_gui, 'data-mo="0"' ) && str_contains( $ht_gui, 'Cách hiểu đã tới người biên soạn' ) && str_contains( $ht_loi, 'Xin viết cách hiểu của bạn' ), array( substr( $ht_mo, 0, 400 ) ) );
update_option( 'cadao_turnstile_site', 'khoa-site-thu', false );
$ht_ts = $HT::form( $mau_id['cau-mau-thu-hai'] );
delete_option( 'cadao_turnstile_site' );
kt( 'form cách hiểu có ô chống rác Turnstile khi người quản trị đã dán khoá', str_contains( $ht_ts, 'class="cf-turnstile" data-sitekey="khoa-site-thu"' ) );

// ---------- Trang đọc theo thể loại và từ khó (D-105, docs/32 mục 4.2, 4.5, 4.6, 4.9) ----------
$viec_moc_tl = (int) $wpdb->get_var( 'SELECT MAX(id) FROM ' . Viec::bang() );
$nc_tl = array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
$bai_tl = static function ( string $ten, string $the_loai, array $dong ) use ( $nc_tl ): int {
	$id = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => $ten, 'post_name' => sanitize_title( $ten ) ) );
	wp_set_object_terms( $id, $the_loai, KhaiBao::THE_LOAI );
	LuuPhan::luu( $id, 'loi', array( 'dong' => $dong, 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_tl );
	return $id;
};
// Từ khó: lần đầu trong cả bài, không phân biệt hoa thường, kiểu bỏ dấu
$tk_bai = $bai_tl( 'Bài từ khó thử', 'ca-dao', array( 'Trèo lên cây Bưởi hái hoa', 'Bước xuống vườn cà hái nụ tầm xuân', 'Hái hoa rồi lại thuỷ chung' ) );
LuuPhan::luu( $tk_bai, 'chu_thich', array( array( 'tu' => 'hái hoa', 'loi_giai' => 'Lời giải thử một.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), array( 'tu' => 'Tầm Xuân', 'loi_giai' => 'Lời giải thử hai.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), array( 'tu' => 'thủy chung', 'loi_giai' => 'Lời giải thử ba.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), array( 'tu' => 'chữ không có', 'loi_giai' => 'Chú thích chung thử.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nc_tl );
wp_set_current_user( 0 );
$h_tk = $xem( $tk_bai );
kt(
	'từ khó trong lời: mỗi từ chỉ đánh dấu lần đầu trong cả bài ("hái hoa" ở dòng 3 không đánh dấu lại), không phân biệt hoa thường ("Tầm Xuân" khớp "tầm xuân"), kiểu bỏ dấu ("thủy" khớp "thuỷ"); khách không thấy lời nhắc kiểm chính tả',
	3 === substr_count( $h_tk, '<a class="word"' ) && 1 === substr_count( $h_tk, 'Xem chú thích từ hái hoa' ) && str_contains( $h_tk, '>tầm xuân</a>' ) && str_contains( $h_tk, '>thuỷ chung</a>' ) && ! str_contains( $h_tk, 'kiểm lại chính tả' ),
	substr( (string) strstr( $h_tk, 'class="verse' ), 0, 900 )
);
wp_set_current_user( $ai_ql );
$h_tk_bt = $xem( $tk_bai );
$yc_tk   = new WP_REST_Request( 'POST', '/phongdao/v1/tac-pham/' . $tk_bai . '/phan/chu_thich' );
$yc_tk->set_body_params( array( 'muc' => array( array( 'tu' => 'hái hoa', 'loi_giai' => 'Lời giải thử một.' ), array( 'tu' => 'trèo cao', 'loi_giai' => 'Gõ sai thử.' ) ) ) );
$yc_tk->set_param( 'id', $tk_bai );
$yc_tk->set_param( 'phan', 'chu_thich' );
$kq_tk = \Phongdao\GiaoTiep\BienTap\Module::luu_phan( $yc_tk );
$_GET  = array( 'id' => (string) $tk_bai );
$h_tk_sua = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
$_GET  = array();
kt(
	'từ không có trong lời: người biên soạn được nhắc "Không thấy từ … trong lời, kiểm lại chính tả" (trang đọc ở chế độ sửa, lời báo khi lưu, trang Sửa tác phẩm); vẫn lưu được',
	str_contains( $h_tk_bt, 'Không thấy từ «chữ không có» trong lời, kiểm lại chính tả.' ) && $kq_tk['ok'] && str_contains( $kq_tk['thong_bao'], 'Không thấy từ «trèo cao» trong lời, kiểm lại chính tả.' ) && 2 === count( PhanBai::lay( $tk_bai, 'chu_thich' )['muc'] ?? array() ) && str_contains( $h_tk_sua, 'Không thấy từ «trèo cao» trong lời, kiểm lại chính tả.' ),
	$kq_tk
);
// Tục ngữ: từ khó tô cả trong câu tiêu đề; câu dài hiện như ca dao; bài mới không có ô Ý nghĩa riêng
$tn_ngan = $bai_tl( 'Tục ngữ ngắn từ khó thử', 'tuc-ngu', array( 'Ăn quả nhớ kẻ trồng cây thử' ) );
LuuPhan::luu( $tn_ngan, 'chu_thich', array( array( 'tu' => 'kẻ trồng cây', 'loi_giai' => 'Người làm ra.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nc_tl );
LuuPhan::luu( $tn_ngan, 'nghia_bong', array( 'chu' => 'Nghĩa bóng thử: nhớ ơn người làm ra.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_tl );
$tn_dai = $bai_tl( 'Tục ngữ dài thử', 'tuc-ngu', array( 'Dòng tục ngữ dài thứ nhất', 'Dòng tục ngữ dài thứ hai', 'Dòng tục ngữ dài thứ ba' ) );
wp_set_current_user( 0 );
$h_tn_ngan = $xem( $tn_ngan );
$h_tn_dai  = $xem( $tn_dai );
kt( 'tục ngữ ngắn: câu là tiêu đề, từ khó được tô ngay trong câu tiêu đề; có Nghĩa bóng thì không mời chia sẻ cách hiểu', str_contains( (string) strstr( (string) strstr( $h_tn_ngan, '<h1 class="read-title">' ), '</h1>', true ), '<a class="word" href="#chu-thich" aria-label="Xem chú thích từ kẻ trồng cây">kẻ trồng cây</a>' ) && ! str_contains( $h_tn_ngan, 'Câu này chưa có lời giải nghĩa' ) );
kt( 'tục ngữ dài (từ 3 dòng): hiện như ca dao — tên là dòng đầu, cỡ tiêu đề thường; lời đủ trong khung lời, không phải kiểu câu là tiêu đề', str_contains( $h_tn_dai, '<h1 class="read-title">Dòng tục ngữ dài thứ nhất</h1>' ) && ! str_contains( $tn_dai_chinh = (string) strstr( (string) strstr( $h_tn_dai, '<header class="work-head">' ), 'class="work-rest"', true ), 'verse proverb' ) && 3 === substr_count( $tn_dai_chinh, '<span class="line">' ), substr( (string) strstr( $h_tn_dai, '<h1' ), 0, 300 ) );
wp_set_current_user( $ai_ql );
$h_tn_sua = $xem( $tn_dai );
$_GET     = array( 'id' => (string) $tn_dai );
$h_tn_adm = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
$_GET     = array();
kt( 'tục ngữ mới ở chế độ sửa: không có ô Ý nghĩa riêng (chỉ Nghĩa đen, Nghĩa bóng, Dùng khi) — trang đọc và trang Sửa tác phẩm', ! str_contains( $h_tn_sua, 'Thêm ý nghĩa' ) && str_contains( $h_tn_sua, 'Thêm nghĩa bóng' ) && str_contains( $h_tn_sua, 'Thêm &quot;Dùng khi&quot;' ) && ! str_contains( $h_tn_adm, 'id="phan-y_nghia"' ) && str_contains( $h_tn_adm, 'id="phan-nghia_bong"' ) );
// Câu đố: "Giải thích đáp án" nằm trong khối ẩn "Hiện đáp án", sau đáp án
$cd_bai = $bai_tl( 'Câu đố giải thích thử', 'cau-do', array( 'Mình tròn thân trắng thử', 'Đố là cái gì thử' ) );
LuuPhan::luu( $cd_bai, 'dap_an', array( 'chu' => 'Đáp án thử', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_tl );
$cd_trong = $xem( $cd_bai );
$_GET     = array( 'id' => (string) $cd_bai );
$cd_adm   = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
$_GET     = array();
LuuPhan::luu( $cd_bai, 'y_nghia', array( 'chu' => 'Giải thích thử: dòng một tả hình, dòng hai hỏi.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_tl );
wp_set_current_user( 0 );
$cd_h   = $xem( $cd_bai );
$cd_an  = (string) strstr( (string) strstr( $cd_h, '<details class="answer">' ), '</details>', true );
kt(
	'câu đố: phần giải nghĩa hiện là "Giải thích đáp án", nằm trong khối ẩn "Hiện đáp án" sau đáp án (không lộ trước khi đoán); "Lời này từ đâu?" cũng gọi là Giải thích đáp án; chế độ sửa có "Thêm giải thích đáp án"',
	str_contains( $cd_an, '<h2>Đáp án</h2>' ) && str_contains( $cd_an, '<h2>Giải thích đáp án</h2>' ) && (int) strpos( $cd_an, 'Đáp án thử' ) < (int) strpos( $cd_an, 'Giải thích thử' ) && 1 === substr_count( $cd_h, 'Giải thích thử: dòng một' ) && ! str_contains( $cd_h, '<h2>Ý nghĩa</h2>' ) && str_contains( $cd_h, '<dt>Giải thích đáp án</dt>' )
	&& str_contains( $cd_trong, 'Thêm giải thích đáp án' ) && str_contains( $cd_trong, 'Sửa giải thích đáp án' ) && str_contains( $cd_adm, 'Thêm giải thích đáp án' ),
	$cd_an
);
// Cờ: "Có thể không phải dân gian" kèm "Vì sao?" (lý do, nguồn người viết ghi)
wp_set_current_user( $ai_ql );
LuuPhan::luu( $tk_bai, 'co_nghi', array( 'ly_do' => 'Lý do thử: thấy in trong tập thơ của một tác giả.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_tl );
wp_set_current_user( 0 );
$co_h  = $xem( $tk_bai );
$co_ds = \Phongdao\GiaoTiep\Khoi\DanhSach::the( (array) \Phongdao\DichVu\DungTrang\DocBai::the( get_post( $tk_bai ) ) );
kt( 'cờ: trang đọc ghi "Có thể không phải dân gian" kèm "Vì sao?" mở lý do; thẻ danh sách ghi cùng nhãn, "Vì sao?" dẫn tới lý do trên trang bài; không còn chữ "Nghi không phải dân gian"', str_contains( $co_h, '<p class="flag">Có thể không phải dân gian</p><details class="disclosure vi-sao" id="vi-sao"><summary>Vì sao?</summary>' ) && str_contains( $co_h, 'Lý do thử: thấy in trong tập thơ' ) && str_contains( $co_ds, 'Có thể không phải dân gian · <a href="' . esc_url( get_permalink( $tk_bai ) . '#vi-sao' ) . '">Vì sao?</a>' ) && ! str_contains( $co_h . $co_ds, 'Nghi không phải dân gian' ) );
// Ca dao "Hình ảnh trong bài" (hình ảnh → điều được nói tới), người viết; chỉ ca dao, đồng dao
wp_set_current_user( $ai_ql );
$ha_bai = $bai_tl( 'Ca dao hình ảnh thử', 'ca-dao', array( 'Thân em như tấm lụa đào thử', 'Phất phơ giữa chợ biết vào tay ai thử' ) );
$ha_sua = $xem( $ha_bai );
$kq_ha  = LuuPhan::luu( $ha_bai, 'hinh_anh', array( 'chu' => "tấm lụa đào → thân phận người con gái\nphất phơ giữa chợ -> không biết số phận về đâu", 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_tl );
$kq_ha_tn = LuuPhan::luu( $tn_ngan, 'hinh_anh', array( 'chu' => 'không hợp → tục ngữ', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_tl );
$kq_ha_may = LuuPhan::luu( $ha_bai, 'hinh_anh', array( 'chu' => 'máy chép → không được', 'nguoi_lam' => NguoiLam::MAY_CHEP ), array( 'nguoi' => 0, 'goi' => KiemPhan::GOI_MAY ) );
$h_tn_sua2 = $xem( $tn_ngan );
wp_set_current_user( 0 );
$ha_h = $xem( $ha_bai );
$ha_k = (string) strstr( (string) strstr( $ha_h, 'id="hinh-anh"' ), '</section>', true );
kt(
	'ca dao: khối tuỳ chọn "Hình ảnh trong bài" — mỗi dòng một cặp hình ảnh → điều được nói tới (nhận cả "->"), sau Ý nghĩa, trước Từ khó / Hiểu thêm; chỉ ca dao, đồng dao; máy không tự chép; chế độ sửa có nút Thêm, tục ngữ thì không',
	$kq_ha['ok'] && ! $kq_ha_tn['ok'] && ! $kq_ha_may['ok'] && str_contains( $ha_k, '<dt>tấm lụa đào</dt><dd><span aria-hidden="true">→ </span>thân phận người con gái</dd>' ) && str_contains( $ha_k, '<dt>phất phơ giữa chợ</dt>' ) && (int) strpos( $ha_h, 'id="hinh-anh"' ) < (int) strpos( $ha_h, 'id="hieu-them"' )
	&& str_contains( $ha_sua, 'id="hinh-anh" data-chi-sua' ) && str_contains( $ha_sua, 'Sửa &quot;Hình ảnh trong bài&quot;' ) && ! str_contains( $h_tn_sua2, 'id="hinh-anh"' ),
	array( $kq_ha, $kq_ha_tn, $kq_ha_may, $ha_k )
);
// Sửa phân loại, đổi thể loại ngay trên trang đọc (chế độ sửa): cùng đường lưu wp-admin, gợi ý của máy chỉ đề xuất
$t_vung = get_terms( array( 'taxonomy' => 'vung', 'hide_empty' => false, 'number' => 1 ) );
$t_vung = is_array( $t_vung ) && $t_vung ? $t_vung[0] : null;
$v_gy_tl = $t_vung ? Viec::tao( array( 'loai' => 'goi_y', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $ha_bai, 'phan' => 'vung', 'noi_dung' => array( 'loai' => 'vung', 'ma' => $t_vung->slug, 'ly_do' => 'thử' ), 'nguoi_tao' => 'may' ) ) : 0;
$ha_khach = $ha_h;
wp_set_current_user( $ai_ql );
$ha_bt = $xem( $ha_bai );
$pl_tam = (string) strstr( (string) strstr( $ha_bt, '<dialog class="tam-sua" id="tam-phan-loai"' ), '</dialog>', true );
$tl_tam = (string) strstr( (string) strstr( $ha_bt, '<dialog class="tam-sua" id="tam-the-loai"' ), '</dialog>', true );
kt(
	'trang đọc (chế độ sửa): dòng nhãn có "Sửa phân loại" (chip chủ đề, nơi lưu truyền, mô-típ — gửi thẳng đường lưu wp-admin, có nonce, quay lại trang đọc; gợi ý máy ghi "máy gợi ý", chưa đánh dấu) và "Đổi thể loại" (người có quyền duyệt; báo đường dẫn đổi, đường cũ tự chuyển); khách không có',
	str_contains( $ha_bt, 'data-tam="phan-loai"' ) && str_contains( $ha_bt, 'data-tam="the-loai"' ) && str_contains( $pl_tam, 'name="action" value="phongdao_sua_phan_loai"' ) && str_contains( $pl_tam, 'name="trang_doc" value="1"' ) && str_contains( $pl_tam, 'data-gui-thuong' ) && str_contains( $pl_tam, 'name="tax" value="chu_de"' ) && str_contains( $pl_tam, '<legend>Nơi lưu truyền</legend>' ) && str_contains( $pl_tam, 'name="_wpnonce"' )
	&& ( ! $t_vung || ( str_contains( $pl_tam, esc_html( $t_vung->name ) . '&nbsp;· máy gợi ý' ) && ! has_term( $t_vung->term_id, 'vung', $ha_bai ) ) )
	&& str_contains( $tl_tam, 'name="action" value="phongdao_doi_the_loai"' ) && str_contains( $tl_tam, 'đường cũ tự chuyển' ) && ! str_contains( $ha_khach, 'tam-phan-loai' ) && ! str_contains( $ha_khach, 'data-gui-thuong' ),
	substr( $pl_tam, 0, 600 )
);
// Người chỉ soạn: vai Biên tập (sửa được bài) nhưng không có quyền duyệt
$cs_tl = (int) wp_insert_user( array( 'user_login' => 'chisoan-trang-doc', 'user_pass' => wp_generate_password(), 'role' => 'editor' ) );
( new WP_User( $cs_tl ) )->add_cap( 'cadao_duyet', false );
wp_set_current_user( $cs_tl );
$ha_cs = $xem( $ha_bai );
wp_set_current_user( $ai_ql );
kt( 'người chỉ soạn: có "Sửa phân loại", không có "Đổi thể loại"', str_contains( $ha_cs, 'data-tam="phan-loai"' ) && ! str_contains( $ha_cs, 'id="tam-the-loai"' ) && ! str_contains( $ha_cs, 'data-tam="the-loai"' ) );
$dc_tam = (string) strstr( (string) strstr( $ha_bt, '<dialog class="tam-sua" id="tam-doi-chieu"' ), '</dialog>', true );
$loi_tam = (string) strstr( (string) strstr( $ha_bt, '<dialog class="tam-sua" id="tam-loi"' ), '</dialog>', true );
kt(
	'trang đọc (chế độ sửa): nút riêng "Đối chiếu sách" cạnh nhãn mức kiểm mở tấm riêng (sách hoặc + Sách mới, trang, bản quét, giống / sửa theo sách / ghi làm dị bản) — cùng đường lưu trang Sửa tác phẩm, quay lại trang đọc; chưa chọn sách thì không điền sẵn; ô Sửa lời không còn phần đối chiếu; khách không có',
	str_contains( $ha_bt, 'data-tam="doi-chieu"' ) && str_contains( $dc_tam, 'name="action" value="phongdao_doi_chieu_sach"' ) && str_contains( $dc_tam, 'name="trang_doc" value="1"' ) && str_contains( $dc_tam, 'data-gui-thuong' ) && str_contains( $dc_tam, '+ Sách mới' ) && str_contains( $dc_tam, 'value="di_ban"' ) && str_contains( $dc_tam, '<option value="0">— Chọn sách —</option>' ) && ! str_contains( $dc_tam, '" selected' ) && str_contains( $dc_tam, 'name="trang" value=""' )
	&& '' !== $loi_tam && ! str_contains( $loi_tam, 'doi_chieu[ho_so]' ) && ! str_contains( $ha_khach, 'tam-doi-chieu' ),
	substr( $dc_tam, 0, 500 )
);
if ( $v_gy_tl ) {
	Viec::xoa( $v_gy_tl );
}
kt( 'người đọc: bài chưa có nơi lưu truyền thì mời "Ở quê bạn có câu này không? Góp" (form Ở quê nói khác của bài), không ghi "chưa ghi nhận"', str_contains( $ha_khach, 'Ở quê bạn có câu này không? Góp</a>' ) && str_contains( $ha_khach, 'loai=di_ban&#038;bai=' . $ha_bai ) && ! str_contains( $ha_khach, 'chưa ghi nhận' ) );
foreach ( array( $tk_bai, $tn_ngan, $tn_dai, $cd_bai, $ha_bai ) as $x ) {
	wp_delete_post( $x, true );
}
// Thẻ máy tự tạo cho các bài thử (gợi ý, có thể trùng): dọn để số đếm của Hộp việc ở phần sau không lệch
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Viec::bang() . " WHERE id > %d AND trang_thai IN ('cho', 'de_sau')", $viec_moc_tl ) );
kt( 'trang không phải tác phẩm: khối không in gì', '' === trim( $xem( $trang ) ) );

// ---------- Danh sách: Tất cả, thể loại ----------
// Như truy vấn thật: /kho/ có post_type; /the-loai/<ma>/ chỉ có tham số phân loại (pre_get_posts đặt loại bài sau khi WordPress đã xếp loại trang)
$ds = static function ( array $tham, bool $tat_ca ): string {
	$q = new WP_Query( $tham + ( $tat_ca ? array( 'post_type' => KhaiBao::TAC_PHAM ) : array() ) + array( 'posts_per_page' => 30, 'orderby' => 'title', 'order' => 'ASC' ) );
	if ( $tat_ca ) {
		$q->is_post_type_archive = true;
		$q->is_archive           = true;
	}
	return \Phongdao\GiaoTiep\Khoi\DanhSach::hien( $q );
};
$tong = (int) wp_count_posts( KhaiBao::TAC_PHAM )->publish;
$h    = $ds( array(), true );
kt( 'Tất cả: một thẻ cho mỗi tác phẩm đã đăng, có số bài', $tong === substr_count( $h, '<article class="card g-' ) && str_contains( $h, $tong . ' tác phẩm' ), $tong );
kt( 'Tất cả: bộ lọc là form GET (tắt JavaScript vẫn lọc): thể loại, chủ đề, hình thức, nơi lưu truyền, sắp xếp', str_contains( $h, '<form method="get"' ) && 5 === preg_match_all( '/<select id="loc-[a-z_]+" name="(the_loai|chu_de|hinh_thuc|vung|sap)"/', $h ) );
kt( 'thẻ bài: thể loại, tên dẫn tới bài, hai dòng đầu', str_contains( $h, '<span class="tag">Câu đố</span>' ) && str_contains( $h, '<a href="' . esc_url( (string) get_permalink( $mau_id['cau-do-mau'] ) ) . '">Câu đố mẫu</a>' ) && str_contains( $h, '<span class="dong">Câu đố mẫu dòng hai</span>' ) );
$h = $ds( array( 'chu_de' => 'khong-co-chu-de-nay' ), true );
kt( 'lọc không ra bài: báo rõ, có nút Xoá lọc', str_contains( $h, 'Chưa có bài khớp các bộ lọc' ) && str_contains( $h, 'Xoá lọc' ) );
$h = $ds( array( 'posts_per_page' => 2 ), true );
kt( 'phân trang: trang đang xem đánh dấu, các trang khác là liên kết', str_contains( $h, '<nav class="pagination" aria-label="Phân trang">' ) && str_contains( $h, 'aria-current="page">1</span>' ) && str_contains( $h, 'aria-label="Trang 2"' ) );
$h = $ds( array( KhaiBao::THE_LOAI => 'ca-dao' ), false );
$so_ca_dao = count( get_posts( array( 'post_type' => KhaiBao::TAC_PHAM, 'numberposts' => -1, 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => KhaiBao::THE_LOAI, 'field' => 'slug', 'terms' => 'ca-dao' ) ) ) ) );
kt( 'trang thể loại: tiêu đề, đường dẫn về Kho, chỉ bài của thể loại', str_contains( $h, '<h1>Ca dao</h1>' ) && str_contains( $h, 'Đọc theo thể loại' ) && str_contains( $h, 'Kho tác phẩm' ) && $so_ca_dao === substr_count( $h, '<article class="card g-ca-dao"' ) && ! str_contains( $h, 'g-cau-do' ), $so_ca_dao );
$h = \Phongdao\GiaoTiep\Khoi\DanhSach::the( array( 'id' => 1, 'tieu_de' => '<script>x</script>', 'url' => 'javascript:alert(1)', 'the_loai' => 'ca-dao', 'ten_the_loai' => 'Ca dao', 'dong' => array( '<b>a</b>' ), 'y_nghia' => '', 'co_nghi' => false ) );
kt( 'thẻ bài: chữ lạ được thoát, không có đường dẫn javascript:', ! preg_match( '/<script|<b>|javascript:/i', $h ) );
wp_reset_query();

// ---------- Trang chủ ----------
kt( 'đã đăng ký khối cadao/trang-chu', $dang_ky->is_registered( 'cadao/trang-chu' ) );
delete_transient( 'phongdao_cau_hom_nay_ds' );
$h = do_blocks( '<!-- wp:cadao/trang-chu /-->' );
kt( 'trang chủ: tên thư viện, ô tìm tới /tim/, 3 lối tìm', str_contains( $h, '<h1>Thư viện Văn học Dân gian Việt Nam</h1>' ) && str_contains( $h, 'action="' . esc_url( home_url( '/tim/' ) ) . '"' ) && 3 === substr_count( $h, 'class="entry-card"' ) );
kt( '"Bạn là" ẩn sẵn (cần JavaScript; tắt JavaScript không có nút chết), 4 vai + Bỏ qua', str_contains( $h, '<div class="role-bar" hidden>' ) && 4 === substr_count( $h, 'data-action="role"' ) && str_contains( $h, 'data-action="role-reset"' ) );
$hom_nay = (string) strstr( (string) strstr( $h, '<section class="today-card"' ), '</section>', true );
preg_match( '/<a class="btn primary" href="([^"]+)">Đọc bài/', $hom_nay, $doc_bai );
$id_hom_nay = isset( $doc_bai[1] ) ? url_to_postid( html_entity_decode( $doc_bai[1] ) ) : 0;
$so_cau     = substr_count( $hom_nay, 'class="line"' );
kt( 'Câu hôm nay: một tác phẩm thật 1–4 câu, có Đọc bài, Lưu, Sao chép kèm thể loại', $id_hom_nay > 0 && KhaiBao::TAC_PHAM === get_post_type( $id_hom_nay ) && $so_cau >= 1 && $so_cau <= 4 && str_contains( $hom_nay, 'data-action="save"' ) && str_contains( $hom_nay, 'data-action="copy-loi"' ) && str_contains( $hom_nay, 'data-the-loai="' ), array( $id_hom_nay, $so_cau ) );
kt( 'Câu hôm nay: nhãn mức kiểm dẫn tới "Lời này từ đâu?" của chính bài', str_contains( $hom_nay, 'href="' . esc_url( (string) get_permalink( $id_hom_nay ) . '#tu-lieu' ) . '"' ) );
kt( 'Câu hôm nay: gọi lại trong ngày cho cùng bài', str_contains( do_blocks( '<!-- wp:cadao/trang-chu /-->' ), $doc_bai[1] ?? 'khong-co' ) );
$the_loai = (string) strstr( (string) strstr( $h, '<div class="tiles five">' ), '</div>', true );
kt( 'Đọc theo thể loại: một ô cho mỗi thể loại có bài, kèm số bài thật', count( DuLieuTrangChu::so_bai_the_loai() ) === substr_count( $the_loai, 'class="tile g-' ) && str_contains( $the_loai, DuLieuTrangChu::so_bai_the_loai()['ca-dao'] . ' bài' ) );
kt( 'Vừa bổ sung: 2 thẻ bài mới nhất', 2 === substr_count( (string) strstr( $h, 'data-home-block="8"' ), '<article class="card' ) );
kt( 'chưa có tuyển tập: không hiện Đọc theo dịp, Tuyển tập nổi bật (không độn)', ! str_contains( $h, '<h2>Đọc theo dịp</h2>' ) && ! str_contains( $h, 'Tuyển tập nổi bật' ) );
kt( 'các khối đổi thứ tự theo vai có đánh dấu data-home-block', str_contains( $h, 'data-home-block="4"' ) && str_contains( $h, 'data-home-block="7"' ) && str_contains( $h, 'id="role-sections"' ) );
set_transient( 'phongdao_cau_hom_nay_ds', array( 1 ), HOUR_IN_SECONDS );
LuuPhan::luu( $mau_id['cau-mau-thu-hai'], 'loi', array( 'dong' => array( 'Câu mẫu thứ hai sáu chữ', 'Câu tám chữ mẫu viết cho đủ dòng', 'Thêm dòng mẫu' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI ) );
kt( 'lưu lời thì danh sách Câu hôm nay được làm mới', false === get_transient( 'phongdao_cau_hom_nay_ds' ) );

// ---------- Tìm ----------
global $wp_rewrite;
$luat_co_dinh = static fn( string $duong ): bool => (bool) array_filter( $wp_rewrite->wp_rewrite_rules(), static fn( $dich, $mau ) => str_contains( (string) $dich, 'phongdao_trang=' ) && preg_match( '#' . $mau . '#', $duong ), ARRAY_FILTER_USE_BOTH );
kt( 'đường dẫn /tim/, /bien-tap/…/ là trang cố định của plugin (không cần trang trong cơ sở dữ liệu; /bien-tap/ chỉ còn để chuyển hướng về wp-admin)', $luat_co_dinh( 'tim/' ) && $luat_co_dinh( 'bien-tap/' ) && $luat_co_dinh( 'bien-tap/them/' ) && null === get_page_by_path( 'tim' ) );
$mau = array_map( static fn( $t ) => $t->slug, get_block_templates() );
kt( 'theme có mẫu trang Tìm và trang 404', in_array( 'trang-tim', $mau, true ) && in_array( '404', $mau, true ) );
$tim_id = static fn( string $q ): array => array_column( \Phongdao\DichVu\ChiMucTim\ChiMucTim::tim( $q )['ds'], 'id' );
kt( 'chỉ mục dựng từ các phần đã lưu: gõ không dấu ra bài, khớp cả cụm', $mau_id['dong-mau-mot-cua-bai'] === ( $tim_id( 'dong mau mot cua bai nay' )[0] ?? 0 ) && ( \Phongdao\DichVu\ChiMucTim\ChiMucTim::tim( 'dong mau mot cua bai nay' )['ds'][0]['lien'] ?? false ) );
kt( 'tìm cả trong chú thích và ý nghĩa', in_array( $mau_id['dong-mau-mot-cua-bai'], $tim_id( 'chu thich tong hop' ), true ) && in_array( $mau_id['tuc-ngu-mau-ngan'], $tim_id( 'giai nghia tong hop' ), true ) );
wp_update_post( array( 'ID' => $mau_id['cau-do-mau'], 'post_status' => 'draft' ) );
$an = ! in_array( $mau_id['cau-do-mau'], $tim_id( 'cau do mau dong hai' ), true );
wp_update_post( array( 'ID' => $mau_id['cau-do-mau'], 'post_status' => 'publish' ) );
kt( 'bài thôi đăng ra khỏi kết quả; đăng lại thì có', $an && in_array( $mau_id['cau-do-mau'], $tim_id( 'cau do mau dong hai' ), true ) );
// Kho lớn (T-181): 1.200 dòng chỉ mục tổng hợp (tests/do-tim/kho-thu.php, id từ 900001) đổ thẳng vào bảng; SQL cờ
// của ChiMucTim::tim phải ra đúng như xếp MỌI dòng của bảng bằng PHP, không còn chặn 500 ứng viên
require_once '/repo/tests/do-tim/kho-thu.php';
$TIM     = \Phongdao\DichVu\ChiMucTim\ChiMucTim::class;
$kho_tim = do_tim_kho( 1200 );
foreach ( array_chunk( $kho_tim, 200 ) as $lo_tim ) {
	$gt_tim = array();
	foreach ( $lo_tim as [ $id_t, $chinh_t, $phu_t ] ) {
		$h_t      = \Phongdao\MienDuLieu\ChiMuc::dong_chi_muc( $chinh_t, $phu_t );
		$gt_tim[] = $wpdb->prepare( '(%d, %s, %s, %s, %s)', 900000 + $id_t, $h_t['chinh'], $h_t['chinh_kd'], $h_t['phu'], $h_t['phu_kd'] );
	}
	$wpdb->query( 'INSERT INTO ' . $TIM::bang() . ' (post_id, chinh, chinh_kd, phu, phu_kd) VALUES ' . implode( ',', $gt_tim ) );
}
$bang_tim = $wpdb->get_results( 'SELECT post_id, chinh, chinh_kd, phu, phu_kd FROM ' . $TIM::bang() . ' ORDER BY post_id', ARRAY_A );
$tv_tim   = do_tim_truy_van( $kho_tim );
$sai_tim  = array();
foreach ( array_merge( array_values( $tv_tim['ds'] ), array( 'anh em người', 'mà', 'Cà', 'em ơi', 'dong mau' ) ) as $q_t ) {
	$mau_t = \Phongdao\MienDuLieu\ChiMuc::mau( \Phongdao\MienDuLieu\ChiMuc::khong_dau( \Phongdao\MienDuLieu\ChiMuc::chu( $q_t ) ) );
	$ung_t = array_values( array_filter( $bang_tim, static fn( array $r ): bool => array_reduce( $mau_t, static fn( bool $c, string $t ): bool => $c && ( str_contains( $r['chinh_kd'], $t ) || str_contains( $r['phu_kd'], $t ) ), true ) ) );
	$dung_t = \Phongdao\MienDuLieu\ChiMuc::xep( $ung_t, $q_t, 20 );
	$that  = $TIM::tim( $q_t, 20 );
	if ( $dung_t['ds'] !== $that['ds'] || $dung_t['tong'] !== $that['tong'] || $TIM::dem( $q_t ) !== $dung_t['tong'] ) {
		$sai_tim[] = array( $q_t, $dung_t['tong'], $that['tong'], array_column( $dung_t['ds'], 'id' ), array_column( $that['ds'], 'id' ) );
	}
}
$q_nhieu = $tv_tim['ds']['Một chữ cái (vừa gõ)'];
$tong_nh = $TIM::tim( $q_nhieu )['tong'];
$q_moi   = $tv_tim['ds']['Cả dòng của bài mới'];
kt( 'tìm kho lớn: tổng và 20 kết quả đầu (đúng thứ tự) như xếp mọi dòng của bảng — SQL cờ chạy đúng trên CSDL', array() === $sai_tim, $sai_tim );
kt( 'tìm kho lớn: hơn 500 ứng viên thì đếm đủ (không dừng ở 500), trang Tìm hiện tổng có dấu chấm hàng nghìn', $tong_nh > 500 && str_contains( \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( $q_nhieu ), '20 trên ' . number_format_i18n( $tong_nh ) . ' bài' ), $tong_nh );
kt( 'tìm kho lớn: bài mới nhất (id lớn nhất) có cả dòng là kết quả đầu, khớp cả cụm', 900000 + $tv_tim['moi'] === ( $TIM::tim( $q_moi )['ds'][0]['id'] ?? 0 ) && $TIM::tim( $q_moi )['ds'][0]['lien'] );
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $TIM::bang() . ' WHERE post_id > %d', 900000 ) );
wp_set_current_user( 0 );
$h = \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 'dong mau mot cua bai nay' );
kt( 'trang Tìm: khớp cả câu thì thẻ lớn đầu trang, có Đọc bài', str_contains( $h, 'result-primary' ) && str_contains( $h, 'Khớp cả câu · Ca dao' ) && str_contains( $h, esc_url( (string) get_permalink( $mau_id['dong-mau-mot-cua-bai'] ) ) ) );
$h = \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 'mau' );
kt( 'trang Tìm: kết quả nhóm theo thể loại, có số bài', str_contains( $h, '<h2>Ca dao <span class="count">' ) && str_contains( $h, '<h2>Câu đố <span class="count">' ) && (bool) preg_match( '/id="search-status"[^>]*>\d+ bài</', $h ), substr( $h, 0, 400 ) );
$h = \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 'cau do mau dong haj' );
kt( 'gõ sai một chữ cái: "Có phải bạn tìm …?" dẫn tới truy vấn đã sửa', str_contains( $h, 'Có phải bạn tìm' ) && str_contains( $h, 'q=cau%20do%20mau%20dong%20hai' ) && str_contains( $h, 'Chưa tìm thấy câu phù hợp' ), $h );
kt( 'trang Tìm chưa gõ: lối bắt đầu; truy vấn lạ: báo rõ, có lối sang Tất cả, Góp', str_contains( \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( '' ), 'Bạn có thể bắt đầu từ đây' ) && str_contains( \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 'zzqxw' ), 'Góp câu đang tìm' ) );
kt( 'chữ lạ trong truy vấn được thoát', ! str_contains( \Phongdao\GiaoTiep\Khoi\Tim::trang( '"><script>alert(1)</script>' ), '<script>alert' ) );
$yc = new WP_REST_Request( 'GET', '/phongdao/v1/tim' );
$yc->set_param( 'q', 'tuc ngu mau' );
$tl = rest_do_request( $yc );
kt( 'REST tìm khi gõ: khách gọi được, trả cùng HTML kết quả', 200 === $tl->get_status() && str_contains( (string) ( $tl->get_data()['html'] ?? '' ), 'Tục ngữ mẫu ngắn' ), $tl->get_status() );
\Phongdao\DichVu\ChiMucTim\ChiMucTim::bat_dau_dung_lai();
$so_lo = \Phongdao\DichVu\ChiMucTim\ChiMucTim::dung_lai_lo();
kt( 'dựng lại chỉ mục theo lô: đủ mọi tác phẩm đã đăng, sau đó vẫn tìm ra', (int) wp_count_posts( KhaiBao::TAC_PHAM )->publish === $so_lo && in_array( $mau_id['ve-mau'], $tim_id( 've mau dong mot' ), true ), $so_lo );
$_SERVER['REQUEST_URI'] = '/ca-dao/cong-thu-nhu-nui/';
$h404 = do_blocks( '<!-- wp:cadao/khong-thay /-->' );
kt( 'trang 404: ô tìm gửi tới /tim/ điền sẵn chữ từ đường dẫn, lối sang Tất cả', str_contains( $h404, 'action="' . esc_url( home_url( '/tim/' ) ) . '"' ) && str_contains( $h404, 'value="cong thu nhu nui"' ) && str_contains( $h404, esc_url( home_url( '/kho/' ) ) ), $h404 );

// ---------- Nguồn vào: hồ sơ, nguồn tự động, bộ đọc Ca dao Mẹ trên nguồn giả (ca kiểm MVP) ----------
use Phongdao\DichVu\NhapNguon\NguonTuDong;
use Phongdao\KhoLuu\HoSo;
use Phongdao\KhoLuu\Kho;

$hs_me = HoSo::theo_khoa( 'cadao-me' );
$hs_tr = $hs_me ? HoSo::truong( $hs_me->ID ) : array();
kt( 'hồ sơ nguồn Ca dao Mẹ: chú thích chép ẩn với người đọc (như hiện nay), xin phép "chưa"', $hs_me && 'an' === ( $hs_tr['chinh_sach_hien'] ?? '' ) && 'chua' === ( $hs_tr['xin_phep'] ?? '' ) && true === ( $hs_tr['chep_chu_thich'] ?? null ), $hs_tr );
$me = NguonTuDong::lay( 'cadao-me' );
kt( 'nguồn tự động Ca dao Mẹ có sẵn ở trạng thái TẮT, gắn hồ sơ nguồn', $me && empty( $me['bat'] ) && $hs_me && $hs_me->ID === (int) $me['ho_so'] );
kt( 'lịch lấy nguồn mỗi 5 phút đã hẹn (cùng tên lịch MVP)', (bool) wp_next_scheduled( NguonTuDong::LICH_LAY ) && (bool) wp_next_scheduled( NguonTuDong::LICH_NGAY ) );
$t6_lich = NguonTuDong::sau_6_gio();
wp_clear_scheduled_hook( NguonTuDong::LICH_NGAY );
wp_schedule_event( ( new DateTimeImmutable( 'tomorrow 13:00', wp_timezone() ) )->getTimestamp(), 'daily', NguonTuDong::LICH_NGAY );
NguonTuDong::hen_lai_lich_ngay();
$t_hen_lai = (int) wp_next_scheduled( NguonTuDong::LICH_NGAY );
NguonTuDong::hen_lai_lich_ngay();
kt( 'chọn câu mới hằng ngày đúng 06:00 giờ site: lịch cũ 13:00 (06:00 UTC) được hẹn lại, lịch đã đúng giờ thì giữ', '06:00' === wp_date( 'H:i', $t6_lich ) && $t6_lich > time() && $t6_lich - time() <= DAY_IN_SECONDS && $t6_lich === $t_hen_lai && $t_hen_lai === (int) wp_next_scheduled( NguonTuDong::LICH_NGAY ), array( wp_date( 'd/m H:i', $t6_lich ), wp_date( 'd/m H:i', $t_hen_lai ) ) );
kt( 'tên nguồn tự động: mã đã cấu hình ra tên; nguồn chưa có tên ghi "nguồn chưa đặt tên", không in mã máy', 'Ca dao Mẹ' === NguonTuDong::ten( 'cadao-me' ) && 'nguồn chưa đặt tên' === NguonTuDong::ten( 'mau-la' ) );
$G      = 'https://mau.example';
$tr_cd1 = '<article id="post-1" class="post"><h1 class="post-title entry-title"><a href="' . $G . '/cd-1/">Dòng một mẫu</a></h1>'
	. '<div class="entry-content"><p>Dòng một mẫu<a class="footnote" data-ref-id="71" href="#note-71">1</a><br>Dòng hai mẫu</p></div>'
	. '<section class="variants"><ul><li class="variant"><p>Dị một mẫu<br>Dị hai mẫu<a class="footnote" data-ref-id="72">2</a></p></li></ul></section>'
	. '<aside class="post-data"><ul class="post-categories"><li><a href="#">Chủ đề mẫu</a></li></ul></aside></article>'
	. '<article id="post-2"><h1 class="post-title"><a href="' . $G . '/cd-2/">Bài hai</a></h1><div class="entry-content"><p>Bài hai dòng một<br>Bài hai dòng hai</p></div>'
	. '<section class="variants"><ul><li class="variant"><p>Bài hai dị bản</p></li></ul></section></article>'
	. '<article id="post-3"><h1 class="post-title"><a href="' . $G . '/cd-3/">Bài ba</a></h1><div class="entry-content"><p>Bài ba dòng một…</p></div></article>'
	. '<section class="ref"><ol><li data-ref-id="71" id="note-71"><dl><dt>từ mẫu</dt><dd><p>Nghĩa &amp; mẫu.</p></dd></dl></li></ol></section>';
$api    = wp_json_encode(
	array(
		array( 'id' => 1, 'link' => "$G/cd-1/", 'title' => array( 'rendered' => 'Dòng một mẫu' ), 'content' => array( 'rendered' => '<p>Dòng một mẫu<a class="footnote" data-ref-id="71">1</a><br>Dòng hai mẫu</p>' ), 'categories' => array( 5 ), 'modified' => '2026-01-01T00:00:00' ),
		array( 'id' => 2, 'link' => "$G/cd-2/", 'title' => array( 'rendered' => 'Bài hai' ), 'content' => array( 'rendered' => '<p>Bài hai dòng một<br>Bài hai dòng hai</p>' ), 'categories' => array(), 'modified' => '2026-01-01T00:00:00' ),
		array( 'id' => 3, 'link' => "$G/cd-3/", 'title' => array( 'rendered' => 'Bài ba' ), 'content' => array( 'rendered' => '<p>Bài ba dòng một<br>Bài ba dòng hai</p>' ), 'categories' => array(), 'modified' => '2026-01-01T00:00:00' ),
	)
);
$trang_gia = array(
	"$G/wp-json/wp/v2/categories?per_page=100&_fields=id,name" => wp_json_encode( array( array( 'id' => 5, 'name' => 'Chủ đề mẫu' ) ) ),
	"$G/wp-json/wp/v2/posts?per_page=100&page=1&orderby=id&order=asc&_fields=id,link,title,content,categories,modified" => $api,
	"$G/ve-sitemap.xml"        => "<urlset><url><loc>$G/ve/ve-mau/</loc><lastmod>2026-01-02T00:00:00+00:00</lastmod></url><url><loc>$G/ve/</loc></url></urlset>",
	"$G/cau-do-sitemap.xml"    => "<urlset><url><loc>$G/cau-do/do-mau/</loc></url></urlset>",
	"$G/cd-1/"                 => $tr_cd1,
	"$G/cd-3/"                 => '<article id="post-3"><h1 class="post-title"><a href="' . $G . '/cd-3/">Bài ba</a></h1><div class="entry-content"><p>Bài ba dòng một<br>Bài ba dòng hai</p></div></article>',
	"$G/ve/ve-mau/"            => '<article id="post-50"><h1 class="post-title"><a href="' . $G . '/ve/ve-mau/">Vè mẫu</a></h1><div class="entry-content"><p>Vè mẫu câu một<br>Vè mẫu câu hai</p></div></article>',
	"$G/cau-do/do-mau/"        => '<article id="post-60"><h1 class="post-title"><a href="' . $G . '/cau-do/do-mau/">Đố mẫu</a></h1><div class="entry-content"><p>Mình tròn mẫu<br>Đố là gì mẫu</p><div class="q">Là cái gì?</div><div class="a"><p>Cái mẫu</p></div></div></article>',
	"$G/chu-thich/chi-tiet/72" => '<li data-ref-id="72" id="note-72"><dl><dt></dt><dd>Chú thích chi tiết mẫu.</dd></dl></li>',
);
$GLOBALS['v2_da_hoi'] = array();
add_filter( 'phongdao_nhip_giay', '__return_zero' );
add_filter(
	'pre_http_request',
	static function ( $tra, $tham, $url ) use ( $trang_gia ) {
		$GLOBALS['v2_da_hoi'][] = array( $url, $tham['user-agent'] ?? '' );
		$co                     = array_key_exists( $url, $trang_gia );
		return array( 'headers' => array( 'x-wp-totalpages' => '1' ), 'body' => $co ? $trang_gia[ $url ] : '', 'response' => array( 'code' => $co ? 200 : 404, 'message' => '' ), 'cookies' => array(), 'filename' => null );
	},
	10,
	3
);
NguonTuDong::luu( array( 'id' => 'me-thu', 'ten' => 'Mẹ thử', 'url' => $G, 'bat' => true, 'loai_lay' => array( 'ca_dao', 've', 'cau_do' ) ) + NguonTuDong::mac_dinh() );
$kq_doc = NguonTuDong::chay( 'me-thu', 60 );
$k      = static function ( string $url ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Kho::bang() . ' WHERE nguon_id = %s AND url = %s', 'me-thu', $url ) );
};
$h1 = $k( "$G/cd-1/" );
kt( 'đọc nguồn: lời ca dao là lời API, dị bản lấy từ trang', $h1 && "Dòng một mẫu\nDòng hai mẫu" === $h1->loi && array( "Dị một mẫu\nDị hai mẫu" ) === ( Kho::du_lieu( $h1 )['di_ban'] ?? null ), $kq_doc );
kt( 'đọc nguồn: chú thích theo thứ tự, giải mã HTML; chú thích thiếu lấy ở trang chi tiết', $h1 && array( array( 'ma' => 71, 'tu' => 'từ mẫu', 'noi_dung' => 'Nghĩa & mẫu.' ), array( 'ma' => 72, 'tu' => '', 'noi_dung' => 'Chú thích chi tiết mẫu.' ) ) === ( Kho::du_lieu( $h1 )['chu_thich'] ?? null ) );
$h2 = $k( "$G/cd-2/" );
kt( 'bài liên quan đủ lời: nhận dị bản ngay, không tải trang riêng', $h2 && array( 'Bài hai dị bản' ) === ( Kho::du_lieu( $h2 )['di_ban'] ?? null ) && ! in_array( "$G/cd-2/", array_column( $GLOBALS['v2_da_hoi'], 0 ), true ) );
$h3 = $k( "$G/cd-3/" );
kt( 'bài liên quan chỉ là đoạn trích: không nhận, tải trang riêng', $h3 && in_array( "$G/cd-3/", array_column( $GLOBALS['v2_da_hoi'], 0 ), true ) );
$hv = $k( "$G/ve/ve-mau/" );
kt( 'vè lấy qua sitemap: lời, tiêu đề từ trang, mã gốc là đường dẫn; bỏ trang danh mục', $hv && 've' === $hv->loai && "Vè mẫu câu một\nVè mẫu câu hai" === $hv->loi && 'Vè mẫu' === $hv->tieu_de && '/ve/ve-mau' === $hv->ma_goc && null === $k( "$G/ve/" ) );
$hd = $k( "$G/cau-do/do-mau/" );
kt( 'câu đố: câu hỏi và đáp án tách khỏi lời', $hd && 'Cái mẫu' === ( Kho::du_lieu( $hd )['dap_an'] ?? '' ) );
kt( 'lịch sự với nguồn: User-Agent ghi tên dự án, không tải ảnh', ! array_filter( $GLOBALS['v2_da_hoi'], static fn( $y ) => ! str_contains( $y[1], 'KhoTangCaDao' ) || preg_match( '/\.(jpe?g|png|webp|gif)$/i', $y[0] ) ) );
kt( 'chạy lại khi không có gì mới: không đọc lại trang; nhật ký lượt có ghi', 0 === NguonTuDong::chay( 'me-thu', 60 )['trang'] && ! empty( NguonTuDong::lay( 'me-thu' )['nhat_ky'] ) );
Kho::luu( 'me-thu', array( 'ma_goc' => 3, 'ngay_sua_goc' => '2026-02-01' ) );
kt( 'nguồn sửa bài: đọc lại trang ở lượt sau', '' === $k( "$G/cd-3/" )->doc_trang );
kt( 'kho không ra trang người đọc: tìm không thấy lời chỉ có trong kho', array() === \Phongdao\DichVu\ChiMucTim\ChiMucTim::tim( 'minh tron mau' )['ds'] && ! str_contains( \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 'Mình tròn mẫu' ), 'Mình tròn' ) );
$h_cho = \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 'Mình tròn mẫu' );
kt( 'tìm không ra mà kho có câu chứa cụm: "Câu này đang chờ biên soạn" + nút Mong có câu này (ẩn khi không có JavaScript); không hiện lời kho', str_contains( $h_cho, 'Câu này đang chờ biên soạn' ) && 1 === preg_match( '/data-action="mong-co-cau" data-kho="\d+" hidden/', $h_cho ) && ! str_contains( $h_cho, 'Đố là' ) && ! str_contains( \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 'zzqxw mẫu lạ' ), 'chờ biên soạn' ), $h_cho );
remove_all_filters( 'pre_http_request' );
remove_all_filters( 'phongdao_nhip_giay' );

// ---------- Câu mới, Đăng từ kho, chống trùng (ca kiểm MVP T-110, D-074) ----------
use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\UngVien\UngVien;

$me_thu = NguonTuDong::lay( 'me-thu' );
$so_moi = UngVien::chon( $me_thu, 10 );
$cho_cd = Viec::mo( 'kho', (int) $h1->id, 'cau_moi' );
kt( 'câu mới: máy chọn câu đã đọc trang thành việc "Câu mới" (không tạo bài), xoay vòng có cả câu đố', $so_moi >= 3 && 1 === count( $cho_cd ) && (bool) Viec::mo( 'kho', (int) $hd->id, 'cau_moi' ) && 'moi' === Kho::lay( (int) $h1->id )->trang_thai, $so_moi );
kt( 'chọn lại: câu đã có việc đang mở thì không thêm lần nữa', 0 === UngVien::chon( $me_thu, 10 ) );
$dang = UngVien::dang( (int) $h1->id, array( 'nguoi' => $ai_ql ) );
$tp1  = $dang['bai'];
$p1   = PhanBai::tat_ca( $tp1, KhaiBao::TAC_PHAM );
kt( 'Đăng: tác phẩm đã đăng, đúng thể loại, kho ghi "đã thành tác phẩm", việc đóng', $dang['ok'] && 'publish' === get_post_status( $tp1 ) && has_term( 'ca-dao', KhaiBao::THE_LOAI, $tp1 ) && 'da_tao_bai' === Kho::lay( (int) $h1->id )->trang_thai && (int) Kho::lay( (int) $h1->id )->post_id === $tp1 && array() === Viec::mo( 'kho', (int) $h1->id, 'cau_moi' ), $dang );
kt( 'Đăng: lời đúng từng ký tự như kho, người làm "máy chép"; dị bản, chú thích chép (nguồn bật Chép chú thích)', array( 'Dòng một mẫu', 'Dòng hai mẫu' ) === ( $p1['loi']['dong'] ?? null ) && NguoiLam::MAY_CHEP === ( $p1['loi']['nguoi_lam'] ?? '' ) && 1 === count( $p1['di_ban']['muc'] ?? array() ) && 2 === count( $p1['chu_thich']['muc'] ?? array() ), $p1 );
kt( 'Đăng: máy không điền nguồn (trang, đường dẫn); địa chỉ gốc giữ nội bộ', ! isset( $p1['loi']['nguon']['trang'] ) && ! isset( $p1['loi']['nguon']['url'] ) && "$G/cd-1/" === ( $p1['nguon_may']['url_goc'] ?? '' ) );
$h = $xem( $tp1 );
kt( 'trang bài từ kho: khách không thấy địa chỉ trang gốc; chú thích chép ghi "nguồn đã ẩn"', ! str_contains( $h, 'mau.example' ) && str_contains( $h, 'mục chép từ nguồn tham khảo · nguồn đã ẩn' ), substr( $h, 0, 300 ) );
$dang_do = UngVien::dang( (int) $hd->id, array( 'nguoi' => $ai_ql ) );
$h       = $xem( $dang_do['bai'] );
kt( 'Đăng câu đố: câu hỏi, đáp án (lời giải) thu gọn sau "Hiện đáp án"', $dang_do['ok'] && str_contains( $h, '<p class="riddle-q">Là cái gì?</p>' ) && str_contains( $h, 'Hiện đáp án' ) && str_contains( $h, 'Cái mẫu' ), $dang_do );
NguonTuDong::luu( array( 'id' => 'khong-chep', 'ten' => 'Không chép', 'url' => 'https://khac.example', 'chep_chu_thich' => false ) + NguonTuDong::mac_dinh() );
Kho::luu( 'khong-chep', array( 'ma_goc' => 9, 'doc_trang' => '2026-10-25', 'url' => 'https://khac.example/9/', 'loai' => 'ca_dao', 'tieu_de' => 'Không chép', 'loi' => "Câu nguồn khác dòng một riêng\nCâu nguồn khác dòng hai riêng nhé", 'chu_thich' => array( array( 'ma' => 1, 'tu' => 'riêng', 'noi_dung' => 'Giải mẫu.' ) ) ) );
$kc  = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Kho::bang() . ' WHERE nguon_id = %s', 'khong-chep' ) );
$dkc = UngVien::dang( (int) $kc->id, array( 'nguoi' => $ai_ql ) );
kt( 'nguồn tắt "Chép chú thích": Đăng không chép chú thích (D-055)', $dkc['ok'] && null === PhanBai::lay( $dkc['bai'], 'chu_thich' ), $dkc );

Kho::luu( 'me-thu', array( 'ma_goc' => 900, 'url' => "$G/trung-loi/", 'loai' => 'ca_dao', 'loi' => "Dòng một mẫu,\nDÒNG HAI MẪU!", 'di_ban' => array( "Dị bản kiểm lỗi\nDòng kiểm lỗi" ) ) );
$kho_gop_loi = $k( "$G/trung-loi/" );
$vt_gop_loi = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . ChongTrung::bang( 'van_tay' ) . ' WHERE muc = %s', 'kho:' . $kho_gop_loi->id ) );
$di_ban_gop_loi = PhanBai::lay( $tp1, 'di_ban' );
$chan_gop_tu_dong = static function ( $kiem, $bai, $khoa ) use ( $tp1 ) {
	return $bai === $tp1 && $khoa === \Phongdao\MienDuLieu\Phan::khoa( 'di_ban' ) ? false : $kiem;
};
add_filter( 'update_post_metadata', $chan_gop_tu_dong, 10, 3 );
$kq_gop_tu_dong = ChongTrung::kiem_kho( (int) $kho_gop_loi->id );
remove_filter( 'update_post_metadata', $chan_gop_tu_dong, 10 );
$kho_gop_sau_loi = Kho::lay( (int) $kho_gop_loi->id );
$vt_gop_sau_loi = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . ChongTrung::bang( 'van_tay' ) . ' WHERE muc = %s', 'kho:' . $kho_gop_loi->id ) );
kt( 'gộp tự động lỗi: câu kho và chỉ mục còn nguyên, đưa vào việc nghi trùng', 'cho_quyet' === $kq_gop_tu_dong && 'moi' === $kho_gop_sau_loi->trang_thai && $vt_gop_loi === $vt_gop_sau_loi && $di_ban_gop_loi === PhanBai::lay( $tp1, 'di_ban' ) && ChongTrung::dang_cho( (int) $kho_gop_loi->id ) && 1 === count( Viec::mo( 'kho', (int) $kho_gop_loi->id, 'co_the_trung' ) ), array( 'ket_qua' => $kq_gop_tu_dong, 'trang_thai' => $kho_gop_sau_loi->trang_thai, 'van_tay' => array( $vt_gop_loi, $vt_gop_sau_loi ) ) );
$wpdb->delete( Viec::bang(), array( 'doi_tuong_loai' => 'kho', 'doi_tuong_id' => (int) $kho_gop_loi->id ) );
$wpdb->delete( ChongTrung::bang( 'trung' ), array( 'a' => 'kho:' . $kho_gop_loi->id ) );
ChongTrung::xoa( 'kho:' . $kho_gop_loi->id );
$wpdb->delete( Kho::bang(), array( 'id' => (int) $kho_gop_loi->id ) );

Kho::luu( 'me-thu', array( 'ma_goc' => 901, 'doc_trang' => '2026-10-26', 'url' => "$G/trung/", 'loai' => 'ca_dao', 'tieu_de' => 'Trùng', 'loi' => "Dòng một mẫu,\nDÒNG HAI MẪU!", 'di_ban' => array( "Dị bản từ nguồn khác\nDị bản dòng hai" ) ) );
$tr = $k( "$G/trung/" );
kt( 'chống trùng: lời y hệt (khác dấu câu, chữ hoa), cùng thể loại → tự gắn vào tác phẩm có sẵn', 'da_gop' === $tr->trang_thai && 'bai:' . $tp1 === $tr->gop_vao, $tr );
kt( 'tự gắn: dị bản mới của câu kia vào tác phẩm (phần dị bản còn do máy chép)', 2 === count( PhanBai::lay( $tp1, 'di_ban' )['muc'] ?? array() ) );
ChongTrung::tach( (int) $tr->id );
kt( 'Tách ra: câu về "mới", cặp ghi "là bài khác", dị bản đã thêm rút lại', 'moi' === $k( "$G/trung/" )->trang_thai && in_array( 'bai:' . $tp1, ChongTrung::da_khac( 'kho:' . $tr->id ), true ) && 1 === count( PhanBai::lay( $tp1, 'di_ban' )['muc'] ?? array() ) );
Kho::luu( 'me-thu', array( 'ma_goc' => 902, 'doc_trang' => '2026-10-26', 'url' => "$G/la-di-ban/", 'loai' => 've', 'tieu_de' => 'Là dị bản', 'loi' => "Dị một mẫu\nDị hai mẫu" ) );
kt( 'chống trùng: lời trùng 100% một dị bản đã ghi (khác thể loại vẫn được) → tự gắn làm dị bản (D-074)', 'da_gop' === $k( "$G/la-di-ban/" )->trang_thai && 'di_ban' === $k( "$G/la-di-ban/" )->gop_lam );
Kho::luu( 'me-thu', array( 'ma_goc' => 903, 'doc_trang' => '2026-10-26', 'url' => "$G/gan/", 'loai' => 'ca_dao', 'tieu_de' => 'Gần', 'loi' => "Dòng một mẫu thêm\nDòng hai mẫu" ) );
$gan  = $k( "$G/gan/" );
$vgan = Viec::mo( 'kho', (int) $gan->id, 'co_the_trung' );
kt( 'chống trùng: giống một phần → việc "Có thể trùng" trong Hộp việc, câu vẫn ở kho', 'moi' === $gan->trang_thai && 1 === count( $vgan ) && 'bai:' . $tp1 === ( $vgan[0]['noi_dung']['b'] ?? '' ) && ChongTrung::dang_cho( (int) $gan->id ), $vgan );
kt( 'câu đang chờ quyết trùng không được chọn làm câu mới', ! in_array( (int) $gan->id, array_map( static fn( $v ) => $v['doi_tuong_id'], Viec::mo( 'kho', (int) $gan->id, 'cau_moi' ) ), true ) && 0 === UngVien::chon( array( 'loai_lay' => array( 'ca_dao' ) ) + $me_thu, 10 ) );
$di_ban_truoc = PhanBai::lay( $tp1, 'di_ban' );
$so_sua_truoc = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . Viec::bang() . " WHERE loai = 'sua' AND doi_tuong_loai = 'post' AND doi_tuong_id = %d", $tp1 ) );
$chan_di_ban_gop = static function ( $kiem, $bai, $khoa ) use ( $tp1 ) {
	return $bai === $tp1 && $khoa === \Phongdao\MienDuLieu\Phan::khoa( 'di_ban' ) ? false : $kiem;
};
add_filter( 'update_post_metadata', $chan_di_ban_gop, 10, 3 );
$q_loi_dong_bo = \Phongdao\DichVu\XepViec\QuyetViec::quyet( $vgan[0]['id'], 'di_ban', $ai_ql, true, $vgan[0]['phien'] );
remove_filter( 'update_post_metadata', $chan_di_ban_gop, 10 );
$gan_sau_dong_bo = Kho::lay( (int) $gan->id );
kt( 'lỗi đồng bộ dị bản khi gộp: phần bài, nhật ký, thẻ và câu kho cùng trở về trước', ! $q_loi_dong_bo['ok'] && $di_ban_truoc === PhanBai::lay( $tp1, 'di_ban' ) && $so_sua_truoc === (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . Viec::bang() . " WHERE loai = 'sua' AND doi_tuong_loai = 'post' AND doi_tuong_id = %d", $tp1 ) ) && Viec::CHO === Viec::lay( $vgan[0]['id'] )['trang_thai'] && 0 === Viec::lay( $vgan[0]['id'] )['phien'] && 'moi' === $gan_sau_dong_bo->trang_thai && '' === $gan_sau_dong_bo->gop_vao && ChongTrung::dang_cho( (int) $gan->id ), $q_loi_dong_bo );
$bai_nhap_tay = wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'draft', 'post_title' => 'Bài kiểm nhập tay' ) );
Kho::luu( 'me-thu', array( 'ma_goc' => 904, 'loai' => 'ca_dao', 'loi' => 'Dòng kiểm gộp nhập tay' ) );
$kho_nhap_tay = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . Kho::bang() . ' WHERE nguon_id = %s AND ma_goc = %s', 'me-thu', '904' ) );
$gop_nhap_tay = ChongTrung::gop( 'kho:' . $kho_nhap_tay, 'bai:' . $bai_nhap_tay, 'di_ban' );
kt( 'gộp vào bài nhập tay không đòi hàng kho gốc để đồng bộ', $gop_nhap_tay && 'da_gop' === Kho::lay( $kho_nhap_tay )->trang_thai && 'bai:' . $bai_nhap_tay === Kho::lay( $kho_nhap_tay )->gop_vao );
Kho::luu( 'me-thu', array( 'ma_goc' => 1, 'di_ban' => array( "Dị một mẫu\nDị hai mẫu", "Dị ba mới\nDị bốn mới" ) ) );
kt( 'kho đổi: tác phẩm nhận dị bản mới (phần còn do máy chép)', 2 === count( PhanBai::lay( $tp1, 'di_ban' )['muc'] ?? array() ) );
$db_nguoi = PhanBai::lay( $tp1, 'di_ban' )['muc'];
$db_nguoi[] = array( 'dong' => array( 'Dị bản người biên soạn thêm' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET );
LuuPhan::luu( $tp1, 'di_ban', $db_nguoi, array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI ) );
Kho::luu( 'me-thu', array( 'ma_goc' => 1, 'di_ban' => array( "Dị một mẫu\nDị hai mẫu" ) ) );
kt( 'kho đổi: phần người đã sửa giữ nguyên (máy không ghi đè)', 3 === count( PhanBai::lay( $tp1, 'di_ban' )['muc'] ?? array() ) );
$bo_id = (int) $hv->id;
$chan_bo = static function ( $sql ) { return preg_match( '/^UPDATE\s+`?' . preg_quote( Kho::bang(), '/' ) . '`?\s+SET\s+`?trang_thai/i', $sql ) ? 'CA_DAO_KIEM_THU_LOI_BO_CAU_KHO' : $sql; };
add_filter( 'query', $chan_bo );
$bo_loi = UngVien::bo( $bo_id, $ai_ql, 'thử lỗi ghi' );
remove_filter( 'query', $chan_bo );
kt( 'Bỏ câu mới khi lỗi ghi kho: trả lỗi, câu còn Mới và thẻ còn mở', ! $bo_loi && 'moi' === Kho::lay( $bo_id )->trang_thai && (bool) Viec::mo( 'kho', $bo_id, 'cau_moi' ) );
kt( 'Bỏ câu mới: câu ở lại kho (bỏ qua), việc đóng', UngVien::bo( $bo_id, $ai_ql, 'không hợp' ) && 'bo_qua' === Kho::lay( $bo_id )->trang_thai && array() === Viec::mo( 'kho', $bo_id, 'cau_moi' ) );

// ---------- Biên tập trên trang đọc (D-094): quyền, chế độ sửa, lưu qua REST, hoàn tác ----------
$ql = get_role( 'administrator' );
$bt = get_role( 'editor' );
kt( 'quyền riêng: Quản trị đủ; Biên tập sửa, duyệt nhưng không quyền nguồn; có vai Cộng tác', $ql->has_cap( 'cadao_soan' ) && $ql->has_cap( 'cadao_duyet' ) && $ql->has_cap( 'cadao_nguon' ) && $bt->has_cap( 'cadao_soan' ) && $bt->has_cap( 'cadao_duyet' ) && ! $bt->has_cap( 'cadao_nguon' ) && (bool) get_role( 'cadao_cong_tac' ) );
wp_set_current_user( 0 );
$h = $xem( $tp1 );
kt( 'khách: không có dải "Đang sửa", nút Sửa, tấm sửa, lịch sử, dòng nguồn gốc nội bộ', ! preg_match( '/dai-bien-tap|nut-sua|tam-sua|id="lich-su"|goc-nguon/', $h ) );
$doc_gia = wp_insert_user( array( 'user_login' => 'docgia', 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ) );
wp_set_current_user( $doc_gia );
kt( 'người đọc có tài khoản (không quyền biên tập): không thấy phần sửa', ! str_contains( $xem( $tp1 ), 'dai-bien-tap' ) );
wp_set_current_user( $ai_ql );
$h = $xem( $tp1 );
kt( 'người biên soạn: dải "Đang sửa", nút Sửa trên khối, tấm sửa lời / ý nghĩa / từ khó / dị bản, lịch sử', str_contains( $h, 'class="dai-bien-tap' ) && str_contains( $h, 'data-tam="loi"' ) && str_contains( $h, 'id="tam-loi"' ) && str_contains( $h, 'id="tam-y_nghia"' ) && str_contains( $h, 'id="tam-chu_thich"' ) && str_contains( $h, 'id="tam-di_ban"' ) && str_contains( $h, 'id="lich-su"' ), substr( $h, 0, 300 ) );
kt( 'người biên soạn: dòng nguồn gốc (máy chép từ…) và nguồn đã ẩn vẫn thấy trong tấm sửa', str_contains( $h, 'Lời: máy chép' ) && ! str_contains( $h, 'id="tam-nghia_den"' ) );
$rest = static function ( string $duong, array $than ) {
	$yc = new WP_REST_Request( 'POST', '/phongdao/v1' . $duong );
	$yc->set_body_params( $than );
	return rest_do_request( $yc );
};
$ctx_d = array( 'ho_so' => array(), 'sach' => array(), 'duyet' => true );
$tam_d = \Phongdao\GiaoTiep\BienTap\TamSua::ve( 'y_nghia', null, $ctx_d );
$tam_s = \Phongdao\GiaoTiep\BienTap\TamSua::ve( 'y_nghia', null, array( 'duyet' => false ) + $ctx_d );
kt( 'tấm sửa Ý nghĩa (D-102): hai nút "Lưu nháp" / "Lưu và hiện cho người đọc" thay ô "đã duyệt"; người không có quyền duyệt chỉ có "Lưu nháp"; phần khác vẫn một nút Lưu', str_contains( $tam_d, 'name="duyet" value="1"' ) && str_contains( $tam_d, 'name="duyet" value=""' ) && str_contains( $tam_d, 'Lưu và hiện cho người đọc' ) && ! str_contains( $tam_d, 'Tôi đã đọc và duyệt' ) && ! str_contains( $tam_s, 'Lưu và hiện cho người đọc' ) && ! str_contains( $tam_s, 'name="duyet"' ) && str_contains( $tam_s, 'Lưu nháp' ) && str_contains( \Phongdao\GiaoTiep\BienTap\TamSua::ve( 'loi', null, $ctx_d ), '>Lưu</button>' ) );
$kq = $rest( "/tac-pham/$tp1/phan/y_nghia", array( 'chu' => 'Ý nghĩa nháp mẫu.', 'ai' => '1' ) );
kt( 'lưu ý nghĩa chưa đánh "đã duyệt": thành nháp trong Hộp việc, bài chưa đổi', 200 === $kq->get_status() && ( $kq->get_data()['ok'] ?? false ) && null === PhanBai::lay( $tp1, 'y_nghia' ) && 1 === count( Viec::mo( 'post', $tp1, 'nhap_y_nghia' ) ), $kq->get_data() );
$kq = $rest( "/tac-pham/$tp1/phan/y_nghia", array( 'chu' => 'Ý nghĩa đã duyệt mẫu.', 'ai' => '1', 'duyet' => '1' ) );
$yn = PhanBai::lay( $tp1, 'y_nghia' );
kt( 'lưu ý nghĩa đã duyệt (có hỗ trợ AI): vào bài, ghi người duyệt', ( $kq->get_data()['ok'] ?? false ) && 'Ý nghĩa đã duyệt mẫu.' === ( $yn['chu'] ?? '' ) && NguoiLam::AI_DA_DUYET === ( $yn['nguoi_lam'] ?? '' ) && $ai_ql === ( $yn['duyet_boi'] ?? 0 ), $kq->get_data() );
$ct_cu = PhanBai::lay( $tp1, 'chu_thich' )['muc'];
$hang  = array();
foreach ( $ct_cu as $i => $m ) {
	$hang[ $i ] = array( 'goc' => (string) $i, 'tu' => $m['tu'] ?? '', 'loi_giai' => $m['loi_giai'], 'nguon' => array( 'ho_so' => (string) ( $m['nguon']['ho_so'] ?? 0 ), 'trang' => '', 'url' => '' ) );
}
$kq = $rest( "/tac-pham/$tp1/phan/chu_thich", array( 'muc' => $hang ) );
kt( 'gửi lại từ khó không đổi gì: "Không có gì thay đổi", mục máy chép giữ nguyên', ( $kq->get_data()['ok'] ?? false ) && ! ( $kq->get_data()['doi'] ?? true ) && $ct_cu === PhanBai::lay( $tp1, 'chu_thich' )['muc'], $kq->get_data() );
$hang[0]['loi_giai'] = 'Lời giải người biên soạn sửa.';
$hang[1]['xoa']      = '1';
$hang['m1']          = array( 'goc' => '', 'tu' => 'mới', 'loi_giai' => 'Chú thích mới.', 'nguon' => array( 'ho_so' => '0', 'trang' => '', 'url' => '' ) );
$kq = $rest( "/tac-pham/$tp1/phan/chu_thich", array( 'muc' => $hang ) );
$ct = PhanBai::lay( $tp1, 'chu_thich' )['muc'] ?? array();
kt( 'sửa một mục máy chép → người viết; xoá mục; thêm mục mới không tự điền nguồn', ( $kq->get_data()['ok'] ?? false ) && 2 === count( $ct ) && NguoiLam::NGUOI_VIET === $ct[0]['nguoi_lam'] && 'Lời giải người biên soạn sửa.' === $ct[0]['loi_giai'] && ! isset( $ct[1]['nguon'] ) && NguoiLam::NGUOI_VIET === $ct[1]['nguoi_lam'], $ct );
HoSo::tao( 'sach-mau', 'Sách mẫu', array( 'loai' => 'sach' ) );
$sach = HoSo::theo_khoa( 'sach-mau' );
$kq   = $rest( "/tac-pham/$tp1/phan/loi", array( 'dong' => "Dòng một mẫu\nDòng hai mẫu", 'doi_chieu' => array( 'ho_so' => (string) $sach->ID, 'trang' => '12', 'url' => '' ) ) );
$loi1 = PhanBai::lay( $tp1, 'loi' );
wp_set_current_user( 0 );
$h_khach = $xem( $tp1 );
wp_set_current_user( $ai_ql );
kt( 'thêm đối chiếu sách (người điền) mà không đổi lời: lời vẫn là "máy chép"; trang hiện "Đã đối chiếu: Sách mẫu, tr. 12"', ( $kq->get_data()['ok'] ?? false ) && NguoiLam::MAY_CHEP === $loi1['nguoi_lam'] && $sach->ID === ( $loi1['doi_chieu']['ho_so'] ?? 0 ) && str_contains( $h_khach, 'Đã đối chiếu: Sách mẫu, tr. 12' ), $kq->get_data() );
$kq = $rest( "/tac-pham/$tp1/phan/loi", array( 'dong' => "Dòng một mẫu\nDòng hai mẫu sửa", 'doi_chieu' => array( 'ho_so' => (string) $sach->ID, 'trang' => '12', 'url' => '' ) ) );
kt( 'sửa chữ trong lời: người làm thành "người viết"', ( $kq->get_data()['ok'] ?? false ) && NguoiLam::NGUOI_VIET === PhanBai::lay( $tp1, 'loi' )['nguoi_lam'] );
$viec_sua = (int) ( $kq->get_data()['viec'] ?? 0 );
$kq       = rest_do_request( new WP_REST_Request( 'POST', "/phongdao/v1/viec/$viec_sua/hoan-tac" ) );
kt( 'hoàn tác qua REST: lời về bản trước', ( $kq->get_data()['ok'] ?? false ) && array( 'Dòng một mẫu', 'Dòng hai mẫu' ) === PhanBai::lay( $tp1, 'loi' )['dong'], $kq->get_data() );
$kq = $rest( "/tac-pham/$tp1/trang-thai", array( 'trang_thai' => 'private', 'trang_thai_cu' => 'publish' ) );
kt( 'Ẩn bài: bài thôi hiện với người đọc, lịch sử ghi lại', ( $kq->get_data()['ok'] ?? false ) && 'private' === get_post_status( $tp1 ) && 'trang_thai' === ( Viec::lich_su( 'post', $tp1, 1 )[0]['phan'] ?? '' ) );
$kq_cu = $rest( "/tac-pham/$tp1/trang-thai", array( 'trang_thai' => 'draft', 'trang_thai_cu' => 'publish' ) );
kt( 'đổi trạng thái từ màn cũ bị chặn, không ghi thêm nhật ký', ! ( $kq_cu->get_data()['ok'] ?? false ) && 'private' === get_post_status( $tp1 ) && 'an' === ( Viec::lich_su( 'post', $tp1, 1 )[0]['quyet'] ?? '' ) );
$rest( "/tac-pham/$tp1/trang-thai", array( 'trang_thai' => 'publish', 'trang_thai_cu' => 'private' ) );
kt( 'Đăng lại (cần quyền duyệt)', 'publish' === get_post_status( $tp1 ) );
$so_lich_su_trang_thai = count( array_filter( Viec::lich_su( 'post', $tp1, 100 ), static fn( $v ) => 'trang_thai' === $v['phan'] ) );
$da_chan_trang_thai = false;
$chan_trang_thai = static function ( $sql ) use ( $wpdb, &$da_chan_trang_thai ) {
	if ( ! $da_chan_trang_thai && preg_match( '/^UPDATE\s+`?' . preg_quote( $wpdb->posts, '/' ) . '`?\s+SET\b(?=.*\bpost_status\b)/is', $sql ) ) {
		$da_chan_trang_thai = true;
		return 'CA_DAO_KIEM_THU_LOI_DOI_TRANG_THAI';
	}
	return $sql;
};
add_filter( 'query', $chan_trang_thai );
$kq_loi_trang_thai = $rest( "/tac-pham/$tp1/trang-thai", array( 'trang_thai' => 'private', 'trang_thai_cu' => 'publish' ) );
remove_filter( 'query', $chan_trang_thai );
kt( 'lỗi ghi trạng thái: bài và nhật ký giữ nguyên', $da_chan_trang_thai && ! ( $kq_loi_trang_thai->get_data()['ok'] ?? false ) && 'publish' === get_post_status( $tp1 ) && $so_lich_su_trang_thai === count( array_filter( Viec::lich_su( 'post', $tp1, 100 ), static fn( $v ) => 'trang_thai' === $v['phan'] ) ) );
$da_chan_nhat_ky_tt = false;
$chan_nhat_ky_tt = static function ( $sql ) use ( &$da_chan_nhat_ky_tt ) {
	if ( ! $da_chan_nhat_ky_tt && preg_match( '/^INSERT\s+INTO\s+`?' . preg_quote( Viec::bang(), '/' ) . '`?\s*\(/i', $sql ) ) {
		$da_chan_nhat_ky_tt = true;
		return 'CA_DAO_KIEM_THU_LOI_GHI_NHAT_KY_TRANG_THAI';
	}
	return $sql;
};
add_filter( 'query', $chan_nhat_ky_tt );
$kq_loi_nhat_ky_tt = $rest( "/tac-pham/$tp1/trang-thai", array( 'trang_thai' => 'private', 'trang_thai_cu' => 'publish' ) );
remove_filter( 'query', $chan_nhat_ky_tt );
kt( 'lỗi ghi nhật ký trạng thái: huỷ thay đổi bài, thử lại được đúng một lần', $da_chan_nhat_ky_tt && ! ( $kq_loi_nhat_ky_tt->get_data()['ok'] ?? false ) && 'publish' === get_post_status( $tp1 ) && $so_lich_su_trang_thai === count( array_filter( Viec::lich_su( 'post', $tp1, 100 ), static fn( $v ) => 'trang_thai' === $v['phan'] ) ) && ( $rest( "/tac-pham/$tp1/trang-thai", array( 'trang_thai' => 'private', 'trang_thai_cu' => 'publish' ) )->get_data()['ok'] ?? false ) && 'private' === get_post_status( $tp1 ) );
$rest( "/tac-pham/$tp1/trang-thai", array( 'trang_thai' => 'publish', 'trang_thai_cu' => 'private' ) );

// Vè dài: đoạn có tiêu đề, bối cảnh, mục lục, "Đọc toàn bài"; tấm Chia đoạn / Bối cảnh chỉ ở bài vè
$ve = $mau_id['ve-mau'];
wp_set_current_user( 0 );
$h_ve = $xem( $ve );
kt(
	'vè dài: bối cảnh trên lời, tóm tắt "48 dòng · 3 đoạn", mục lục, tiêu đề đoạn, mốc #cadao-doan-N như MVP',
	str_contains( $h_ve, 'class="boi-canh"' ) && str_contains( $h_ve, 'Bối cảnh tổng hợp để xem cách hiện một bài vè dài.' ) && str_contains( $h_ve, '48 dòng · 3 đoạn · khoảng 2 phút đọc' ) && str_contains( $h_ve, '<summary>Mục lục</summary>' )
		&& 3 === substr_count( $h_ve, '<h2 class="doan-ten">' ) && str_contains( $h_ve, 'id="cadao-doan-3"' ) && str_contains( $h_ve, 'data-bai="' . $ve . '"' ) && str_contains( $h_ve, '· dòng 17–32' ),
	substr( $h_ve, 0, 600 )
);
kt( 'vè dài: chỉ đoạn bắt đầu sau dòng 30 có dấu thu gọn; số dòng mỗi 5 dòng; chỗ ngắt khổ giữ lại', 1 === substr_count( $h_ve, 'data-sau' ) && str_contains( $h_ve, '<span class="ln" aria-hidden="true">05</span>' ) && ! str_contains( $h_ve, '<span class="ln" aria-hidden="true">04</span>' ) && 4 === substr_count( $h_ve, '<div class="kho">' ) );
kt( 'vè dài: lời đủ 48 dòng (không dòng nào bị bỏ; khối Ảnh để chia sẻ bên dưới chỉ lấy 4 dòng đầu)', 48 === substr_count( (string) strstr( $h_ve, 'id="anh-chia-se"', true ), '<span class="line">' ) );
$h_dd = $xem( $mau_id['dong-dao-mau'] );
$h_cd = $xem( $mau_id['dong-mau-mot-cua-bai'] );
kt( 'Đọc cùng bé chỉ ở bài đồng dao (nút ẩn khi không có JavaScript); Trình chiếu ở cột công cụ đọc', str_contains( $h_dd, 'data-action="kids" data-chi-js hidden' ) && ! str_contains( $h_cd, 'data-action="kids"' ) && str_contains( $h_cd, 'data-action="present"' ) );
kt( 'Đọc tiếp: bài cùng chủ đề kèm lý do, đủ tối thiểu 3 bài (thêm bài cùng thể loại), không có chính bài này', str_contains( $h_cd, 'id="doc-tiep"' ) && str_contains( $h_cd, 'Cùng chủ đề Lao động' ) && str_contains( $h_cd, '/ca-dao/cau-mau-thu-hai/' ) && 3 <= substr_count( (string) strstr( (string) strstr( $h_cd, 'id="doc-tiep"' ), 'id="anh-chia-se"', true ), '<article class="card' ) && ! str_contains( (string) strstr( (string) strstr( $h_cd, 'id="doc-tiep"' ), 'id="anh-chia-se"', true ), 'dong-mau-mot-cua-bai/"' ), substr( (string) strstr( $h_cd, 'id="doc-tiep"' ), 0, 800 ) );
wp_set_current_user( $ai_ql );
$h_ve = $xem( $ve );
kt( 'người biên soạn mở vè: nút và tấm "Chia đoạn", "Bối cảnh"; bài ca dao không có hai tấm này', str_contains( $h_ve, 'id="tam-doan"' ) && str_contains( $h_ve, 'id="tam-boi_canh"' ) && str_contains( $h_ve, 'lời hiện có 48 dòng' ) && ! str_contains( $xem( $tp1 ), 'id="tam-doan"' ) );
$kq = $rest( "/tac-pham/$ve/phan/doan", array( 'muc' => array( array( 'tieu_de' => 'Phần sau', 'tu_dong' => '25', 'den_dong' => '48' ), array( 'tieu_de' => 'Phần đầu', 'tu_dong' => '1', 'den_dong' => '24' ), '__i__' => array( 'tieu_de' => '', 'tu_dong' => '', 'den_dong' => '' ) ) ) );
kt( 'sửa đoạn: xếp theo dòng đầu, bỏ hàng mẫu; trang hiện đúng hai đoạn', ( $kq->get_data()['ok'] ?? false ) && array( 'Phần đầu', 'Phần sau' ) === array_column( PhanBai::lay( $ve, 'doan' )['muc'] ?? array(), 'tieu_de' ) && 2 === substr_count( $xem( $ve ), '<h2 class="doan-ten">' ), $kq->get_data() );
$kq = $rest( "/tac-pham/$ve/phan/doan", array( 'muc' => array( array( 'tieu_de' => 'Lệch', 'tu_dong' => '9', 'den_dong' => '3' ) ) ) );
kt( 'đoạn có dòng cuối trước dòng đầu: không lưu, báo lỗi', ! ( $kq->get_data()['ok'] ?? true ) && 2 === count( PhanBai::lay( $ve, 'doan' )['muc'] ?? array() ), $kq->get_data() );
$bc_cu = PhanBai::lay( $ve, 'boi_canh' );
$kq    = $rest( "/tac-pham/$ve/phan/boi_canh", array( 'chu' => $bc_cu['chu'] ?? '', 'su_kien' => '', 'nam' => '', 'noi' => '', 'nguon' => array( 'ho_so' => '0', 'trang' => '', 'url' => '' ) ) );
kt( 'gửi lại bối cảnh không đổi gì: "Không có gì thay đổi"', ( $kq->get_data()['ok'] ?? false ) && ! ( $kq->get_data()['doi'] ?? true ), $kq->get_data() );
$kq = $rest( "/tac-pham/$ve/phan/boi_canh", array( 'chu' => $bc_cu['chu'] ?? '', 'nam' => '1900', 'noi' => 'Nơi mẫu' ) );
kt( 'thêm năm, nơi cho bối cảnh: lưu, trang hiện "1900 · Nơi mẫu"', ( $kq->get_data()['ok'] ?? false ) && '1900' === ( PhanBai::lay( $ve, 'boi_canh' )['nam'] ?? '' ) && str_contains( $xem( $ve ), '1900 · Nơi mẫu' ), $kq->get_data() );
wp_set_current_user( $doc_gia );
kt( 'người không có quyền biên tập: REST lưu phần bị chặn (403)', 403 === $rest( "/tac-pham/$tp1/phan/y_nghia", array( 'chu' => 'x', 'duyet' => '1' ) )->get_status() && 'Ý nghĩa đã duyệt mẫu.' === PhanBai::lay( $tp1, 'y_nghia' )['chu'] );
wp_set_current_user( 0 );
kt( 'khách: REST lưu phần bị chặn (401)', 401 === $rest( "/tac-pham/$tp1/phan/y_nghia", array( 'chu' => 'x', 'duyet' => '1' ) )->get_status() );
$cong_tac = wp_insert_user( array( 'user_login' => 'congtac', 'user_pass' => wp_generate_password(), 'role' => 'cadao_cong_tac' ) );
wp_set_current_user( $cong_tac );
kt( 'vai Cộng tác: không sửa được tác phẩm trên trang', 403 === $rest( "/tac-pham/$tp1/phan/loi", array( 'dong' => 'x' ) )->get_status() );
wp_set_current_user( $ai_ql );

// ---------- Bốn trang biên tập: Hộp việc, Tác phẩm, Thêm bài, Số liệu tuần ----------
use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\GiaoTiep\BienTap\ThemBai;

$mau = array_map( static fn( $t ) => $t->slug, get_block_templates() );
$khoi_dk = WP_Block_Type_Registry::get_instance();
kt( 'trang biên tập /bien-tap/ đã gộp về wp-admin: theme không còn mẫu trang biên tập, plugin không còn khối Hộp việc / Tác phẩm / Thêm bài / Số liệu tuần ngoài trang đọc', array() === array_intersect( array( 'trang-bien-tap', 'trang-bien-tap-tac-pham', 'trang-bien-tap-them', 'trang-bien-tap-so-lieu' ), $mau ) && ! $khoi_dk->is_registered( 'cadao/hop-viec' ) && ! $khoi_dk->is_registered( 'cadao/ds-tac-pham' ) && ! $khoi_dk->is_registered( 'cadao/them-bai' ) && ! $khoi_dk->is_registered( 'cadao/so-lieu-tuan' ) );
$TCD = \Phongdao\GiaoTiep\TrangCoDinh\Module::class;
$dq  = array(
	'hop_viec' => $TCD::dich_quan_tri( 'bien-tap', array() ),
	'the'      => $TCD::dich_quan_tri( 'bien-tap', array( 'viec' => '42', 'loai' => 'nguoi_doc', 'tt' => 'de_sau', 'tung' => '1' ) ),
	'tp_thieu' => $TCD::dich_quan_tri( 'bien-tap/tac-pham', array( 'loc' => 'thieu-y-nghia', 'q' => 'trèo cao' ) ),
	'tp_nhap'  => $TCD::dich_quan_tri( 'bien-tap/tac-pham', array( 'loc' => 'ban-nhap' ) ),
	'them'     => $TCD::dich_quan_tri( 'bien-tap/them', array() ),
	'so_lieu'  => $TCD::dich_quan_tri( 'bien-tap/so-lieu', array() ),
	'la'       => $TCD::dich_quan_tri( 'bien-tap', array( 'viec' => 'abc', 'tt' => 'xong' ) ),
);
kt(
	'đường dẫn /bien-tap/… cũ chuyển sang màn wp-admin tương ứng: Hộp việc (giữ thẻ, loại việc, Để sau), Tác phẩm (thiếu ý nghĩa, bản nháp, chuỗi tìm), Thêm tác phẩm, Hôm nay; tham số lạ bỏ qua',
	admin_url( 'admin.php?page=phongdao-hop-viec' ) === $dq['hop_viec']
	&& str_contains( $dq['the'], 'page=phongdao-hop-viec' ) && str_contains( $dq['the'], 'viec=42' ) && str_contains( $dq['the'], 'loai=nguoi_doc' ) && str_contains( $dq['the'], 'tt=de_sau' ) && ! str_contains( $dq['the'], 'tung=' )
	&& str_contains( $dq['tp_thieu'], 'page=phongdao-tac-pham' ) && str_contains( $dq['tp_thieu'], 'muc=thieu_y_nghia' ) && str_contains( $dq['tp_thieu'], 'q=tr%C3%A8o' )
	&& str_contains( $dq['tp_nhap'], 'tt=draft' ) && ! str_contains( $dq['tp_nhap'], 'muc=' )
	&& admin_url( 'admin.php?page=phongdao-them' ) === $dq['them'] && admin_url( 'admin.php?page=phongdao-ban-lam-viec' ) === $dq['so_lieu']
	&& admin_url( 'admin.php?page=phongdao-hop-viec' ) === $dq['la'],
	$dq
);
$QTM = \Phongdao\GiaoTiep\QuanTri\Module::class;
$lg  = array(
	'ds'      => $QTM::dich_loi_goc( 'edit.php', array( 'post_type' => KhaiBao::TAC_PHAM ) ),
	'ds_rac'  => $QTM::dich_loi_goc( 'edit.php', array( 'post_type' => KhaiBao::TAC_PHAM, 'action' => 'trash' ) ),
	'ds_bai'  => $QTM::dich_loi_goc( 'edit.php', array() ),
	'moi'     => $QTM::dich_loi_goc( 'post-new.php', array( 'post_type' => KhaiBao::TAC_PHAM ) ),
	'moi_bai' => $QTM::dich_loi_goc( 'post-new.php', array() ),
	'sua'     => $QTM::dich_loi_goc( 'post.php', array( 'post' => (string) $tp1, 'action' => 'edit' ) ),
	'sua_rac' => $QTM::dich_loi_goc( 'post.php', array( 'post' => (string) $tp1, 'action' => 'trash' ) ),
	'sua_ho'  => $QTM::dich_loi_goc( 'post.php', array( 'post' => (string) ( $bai_thuong = (int) wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'Bài thường thử' ) ) ), 'action' => 'edit' ) ),
);
wp_delete_post( $bai_thuong, true );
kt(
	'lối gốc WordPress của tác phẩm (danh sách, sửa, thêm mới) chuyển sang màn Phongdao; thao tác khác (thùng rác) và loại bài khác giữ nguyên',
	admin_url( 'admin.php?page=phongdao-tac-pham' ) === $lg['ds'] && '' === $lg['ds_rac'] && '' === $lg['ds_bai']
	&& \Phongdao\GiaoTiep\QuanTri\ThemTacPham::url() === $lg['moi'] && '' === $lg['moi_bai']
	&& \Phongdao\GiaoTiep\QuanTri\SuaTacPham::url( $tp1 ) === $lg['sua'] && '' === $lg['sua_rac'] && '' === $lg['sua_ho'],
	$lg
);
wp_set_current_user( 0 );
kt( 'khách: REST kiểm trùng bị chặn (401)', 401 === $rest( '/trung/kiem', array( 'loi' => 'x' ) )->get_status() );
kt( 'khách: đầu trang có "Đã lưu", không có Hộp việc', str_contains( do_blocks( '<!-- wp:cadao/dau-trang /-->' ), 'Đã lưu' ) && ! str_contains( do_blocks( '<!-- wp:cadao/dau-trang /-->' ), 'Hộp việc' ) );
wp_set_current_user( $ai_ql );
$dau_bt = do_blocks( '<!-- wp:cadao/dau-trang /-->' );
kt( 'người biên soạn: đầu trang có "Hộp việc" kèm số việc cần quyết, mở màn Hộp việc trong wp-admin', str_contains( $dau_bt, 'Hộp việc <span class="dem">' . Viec::dem_hop_viec() . '</span>' ) && str_contains( $dau_bt, 'href="' . esc_url( admin_url( 'admin.php?page=phongdao-hop-viec' ) ) . '"' ) && ! str_contains( $dau_bt, 'href="' . esc_url( home_url( '/bien-tap/' ) ) . '"' ) );
$v_nhap_ai = Viec::tao( array( 'loai' => 'nhap_y_nghia', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'phan' => 'y_nghia', 'noi_dung' => array( 'chu' => 'Nháp máy soạn để xem thẻ.', 'ai' => true ), 'nguoi_tao' => 'kiem_thu' ) );
$v_tr_hai  = Viec::tao( array( 'loai' => 'co_the_trung', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'noi_dung' => array( 'a' => 'bai:' . $tp1, 'b' => 'bai:' . $mau_id['cau-mau-thu-hai'], 'giong' => 80 ), 'nguoi_tao' => 'kiem_thu' ) );
$h         = \Phongdao\GiaoTiep\BienTap\HopViec::the( Viec::lay( $v_nhap_ai ) );
$h_tr_hai  = \Phongdao\GiaoTiep\BienTap\HopViec::the( Viec::lay( $v_tr_hai ) );
Viec::xoa( $v_nhap_ai );
Viec::xoa( $v_tr_hai );
kt( 'Hộp việc: thẻ có form gửi admin-post kèm nonce và phiên; thẻ nháp ý nghĩa ghi rõ chưa duyệt; thẻ có thể trùng đặt hai lời cạnh nhau', str_contains( $h, 'action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"' ) && str_contains( $h, 'name="_wpnonce"' ) && str_contains( $h, 'name="phien" value="0"' ) && str_contains( $h, 'name="quyet" value="duyet"' ) && str_contains( $h, 'AI · chưa duyệt' ) && str_contains( $h_tr_hai, 'class="hai-cot"' ), substr( $h, 0, 300 ) );
$get_cu = $_GET;
$dem_cu = Viec::dem_cho();
$ids_nhieu = array();
for ( $i = 0; $i < 1010; $i++ ) {
	$ids_nhieu[] = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'noi_dung' => array( 'ly_do' => array() ), 'nguoi_tao' => 'kiem_thu' ) );
}
$_GET = array();
$dem_loc_nhieu = Viec::dem( Viec::CHO, 'can_xem' );
kt( 'Hộp việc hơn 1.000 thẻ: số đếm đủ (danh sách, lọc loại, phân trang ở wp-admin bên dưới)', 1010 === count( array_filter( $ids_nhieu ) ) && $dem_cu + 1010 === Viec::dem_cho() && $dem_loc_nhieu >= 1010 );
$id_de_sau = Viec::tao( array( 'loai' => 'can_xem', 'trang_thai' => Viec::DE_SAU, 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'noi_dung' => array( 'ly_do' => array() ), 'nguoi_tao' => 'kiem_thu' ) );
$_GET = array( 'tt' => Viec::DE_SAU, 'loai' => 'can_xem' );
$h_de_sau = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
kt( 'Hộp việc: tìm lại Để sau (tab có số), thẻ Để sau nằm trong danh sách', $id_de_sau > 0 && str_contains( $h_de_sau, 'Để sau <span class="phongdao-chip-so">' . Viec::dem( Viec::DE_SAU ) . '</span>' ) && str_contains( $h_de_sau, 'viec=' . $id_de_sau ), substr( $h_de_sau, 0, 400 ) );
$_GET = array( 'tt' => Viec::CHO, 'loai' => 'can_xem' );
$truy_van_viec_truoc = $wpdb->num_queries;
$admin_viec = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
$truy_van_viec = $wpdb->num_queries - $truy_van_viec_truoc;
$admin_tim = Viec::tim_quan_tri( Viec::CHO, 'can_xem', '', 1 );
kt( 'wp-admin Hộp việc: hơn 1.000 thẻ đếm đủ, 30 dòng, lọc loại và phân trang', $admin_tim['tong'] === $dem_loc_nhieu && 30 === count( $admin_tim['ds'] ) && str_contains( $admin_viec, '<strong>' . $dem_loc_nhieu . '</strong> thẻ khớp' ) && 30 === substr_count( $admin_viec, '<li class="phongdao-hv-dong' ) && str_contains( $admin_viec, 'Trang sau' ) );
kt( 'wp-admin Hộp việc: số truy vấn không tăng theo 30 dòng', $truy_van_viec <= 20, $truy_van_viec );
$_GET = array( 'tt' => Viec::DE_SAU, 'viec' => (string) $id_de_sau );
$admin_chi_tiet = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
kt( 'wp-admin Hộp việc: mở thẻ Để sau có form quyết cũ, nonce, phiên và không có nút Để sau', str_contains( $admin_chi_tiet, 'id="viec-' . $id_de_sau . '"' ) && str_contains( $admin_chi_tiet, 'name="action" value="' . \Phongdao\GiaoTiep\BienTap\HopViec::HANH_DONG . '"' ) && str_contains( $admin_chi_tiet, 'name="_wpnonce"' ) && str_contains( $admin_chi_tiet, 'name="phien" value="0"' ) && ! str_contains( $admin_chi_tiet, 'name="quyet" value="de_sau"' ) );
$_GET = array( 'q' => (string) $id_de_sau, 'tt' => 'tat_ca' );
kt( 'wp-admin Hộp việc: tìm ID của thẻ Để sau trong mọi trạng thái', str_contains( \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien(), 'Cần xem #' . $id_de_sau ) );
wp_set_current_user( 0 );
kt( 'wp-admin Hộp việc: khách không đọc được thẻ', ! str_contains( \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien(), 'Cần xem #' . $id_de_sau ) );
wp_set_current_user( $ai_ql );
$v_gio = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'noi_dung' => array( 'ly_do' => array() ), 'nguoi_tao' => 'kiem_thu', 'tao_luc' => '2026-01-01 00:00:00' ) );
$_GET  = array( 'tt' => Viec::CHO, 'q' => (string) $v_gio );
$mui_cu = get_option( 'timezone_string' );
update_option( 'timezone_string', 'Asia/Ho_Chi_Minh' );
$h_gio = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
update_option( 'timezone_string', $mui_cu );
kt( 'wp-admin Hộp việc: giờ tạo thẻ (lưu UTC) in theo giờ site (giờ Việt Nam: 00:00 UTC là 07:00)', str_contains( $h_gio, '01/01/2026 07:00' ) && ! str_contains( $h_gio, '01/01/2026 00:00' ) );
QuyetViec::quyet( $v_gio, 'xong', $ai_ql, true );
$_GET = array( 'tt' => Viec::CHO, 'loai' => 'can_xem', 'viec' => (string) $v_gio );
$h_da_quyet = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
kt( 'wp-admin Hộp việc: thẻ đã quyết ghi nhãn quyết định (không in mã, không in phiên), có lối Sửa bài và "Thẻ tiếp theo" cùng bộ lọc', str_contains( $h_da_quyet, 'Đã quyết: <strong>Đã xem, ổn rồi</strong>' ) && ! str_contains( $h_da_quyet, 'Phiên ' ) && str_contains( $h_da_quyet, 'page=phongdao-sua-tac-pham' ) && str_contains( $h_da_quyet, 'Thẻ tiếp theo' ) && str_contains( $h_da_quyet, 'loai=can_xem' ) && ! str_contains( $h_da_quyet, 'viec=' . $v_gio . '">Thẻ tiếp' ) );
// ---------- Hộp việc kiểu hộp thư (T-188): danh sách + thẻ, tự sang thẻ kế, Hoàn tác, làm nhanh nhiều thẻ ----------
$HV   = \Phongdao\GiaoTiep\BienTap\HopViec::class;
$DSV  = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::class;
$ht   = array();
foreach ( array( 'Lý do thử hộp thư một', 'Lý do thử hộp thư hai', 'Lý do thử hộp thư ba' ) as $ld_ht ) {
	$ht[] = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'noi_dung' => array( 'ly_do' => array( $ld_ht ) ), 'nguoi_tao' => 'kiem_thu', 'uu_tien' => 99 ) );
}
$ht_n   = Viec::tao( array( 'loai' => 'nhap_y_nghia', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'phan' => 'y_nghia', 'noi_dung' => array( 'chu' => 'Nháp thử hộp thư.' ), 'nguoi_tao' => 'kiem_thu' ) );
$hien_hv = static function ( array $g ) use ( $DSV ): string {
	$_GET = $g;
	return $DSV::hien();
};
$loc_ht = array( 'tt' => 'cho', 'loai' => 'can_xem' );
$_GET   = $loc_ht;
$h_ht   = $DSV::hien();
kt( 'Hộp việc hộp thư: tab trạng thái và loại việc có số; mỗi thẻ trong danh sách có hai dòng nội dung; màn rộng mở sẵn thẻ đầu bên phải, form quyết gửi kèm đích là thẻ kế', str_contains( $h_ht, 'aria-label="Trạng thái thẻ"' ) && str_contains( $h_ht, 'Chờ <span class="phongdao-chip-so">' . number_format_i18n( Viec::dem( Viec::CHO ) ) . '</span>' ) && str_contains( $h_ht, 'aria-current="page">Cần xem <span class="phongdao-chip-so">' . number_format_i18n( Viec::dem( Viec::CHO, 'can_xem' ) ) . '</span>' ) && str_contains( $h_ht, '<span class="phongdao-hv-tom">Lý do thử hộp thư hai</span>' ) && str_contains( $h_ht, '<div class="phongdao-hv-phai" data-tu-chon>' ) && str_contains( $h_ht, 'id="viec-' . $ht[0] . '"' ) && str_contains( $h_ht, 'name="tiep" value="' . esc_attr( $DSV::url( $loc_ht, 1, $ht[1] ) ) . '"' ) && str_contains( $h_ht, '<li class="phongdao-hv-dong dang-chon">' ) && str_contains( $h_ht, 'data-hv="sau"' ) && ! str_contains( $h_ht, 'data-hv="truoc"' ) && str_contains( $h_ht, 'Thẻ 1 / ' . Viec::dem( Viec::CHO, 'can_xem' ) ) );
kt( 'Hộp việc hộp thư: thanh nổi theo loại việc — Để sau làm nhanh có hộp xác nhận; thẻ Cần xem không có Bỏ; không chọn loại thì nhắc chọn loại', str_contains( $h_ht, 'name="nhanh" value="viec_de_sau" class="button" data-hoi="Để sau %d thẻ cần xem đã chọn?' ) && ! str_contains( $h_ht, 'name="nhanh" value="viec_bo"' ) && str_contains( $hien_hv( array( 'tt' => 'cho' ) ), 'Chọn một loại việc ở trên để làm nhanh nhiều thẻ.' ) && str_contains( $hien_hv( array( 'tt' => 'cho', 'loai' => 'nhap_y_nghia' ) ), 'name="nhanh" value="viec_bo"' ) );
$_GET   = $loc_ht + array( 'viec' => (string) $ht[1] );
$h_ht2  = $DSV::hien();
kt( 'Hộp việc hộp thư: chọn một thẻ thì thẻ đó mở bên phải (màn hẹp chỉ hiện thẻ đó), có Thẻ trước / Thẻ sau (J/K) và vị trí thẻ', str_contains( $h_ht2, '<div class="phongdao-hv co-chon">' ) && str_contains( $h_ht2, 'id="viec-' . $ht[1] . '"' ) && ! str_contains( $h_ht2, 'id="viec-' . $ht[0] . '"' ) && str_contains( $h_ht2, 'data-hv="truoc" href="' . esc_url( $DSV::url( $loc_ht, 1, $ht[0] ) ) . '"' ) && str_contains( $h_ht2, 'data-hv="sau" href="' . esc_url( $DSV::url( $loc_ht, 1, $ht[2] ) ) . '"' ) && str_contains( $h_ht2, 'Thẻ 2 / ' ) && str_contains( $h_ht2, 'class="phongdao-hv-ve"' ) && str_contains( $h_ht2, 'name="tiep" value="' . esc_attr( $DSV::url( $loc_ht, 1, $ht[2] ) ) . '"' ) );
// Quyết qua form (admin-post): đúng đích thì sang thẻ kế kèm "vua" và Hoàn tác; đích lạ bị bỏ; quyết hỏng thì ở lại
$chan_die_ht = static fn() => static function ( $chu ): void {
	throw new \RuntimeException( 'wp_die: ' . ( is_string( $chu ) ? $chu : 'lỗi' ) );
};
$chan_di_ht = static function ( $vi_tri ): string {
	throw new \RuntimeException( 'redirect: ' . $vi_tri );
};
add_filter( 'wp_die_handler', $chan_die_ht );
add_filter( 'wp_redirect', $chan_di_ht );
$gui_ht = static function ( string $ham, string $nonce, array $than ) use ( $HV ): string {
	$_POST    = $than + array( '_wpnonce' => wp_create_nonce( $nonce ) );
	$_REQUEST = $_POST;
	try {
		$HV::$ham();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$ve_ht   = $DSV::url( $loc_ht, 1, $ht[0] );
$phien_0 = Viec::lay( $ht[0] )['phien'];
$r_ht    = $gui_ht( 'nhan_form', $HV::HANH_DONG . $ht[0], array( 'viec' => (string) $ht[0], 'quyet' => 'de_sau', 'phien' => (string) $phien_0, 'tiep' => $DSV::url( $loc_ht, 1, $ht[1] ), '_wp_http_referer' => $ve_ht ) );
$v_ht0   = Viec::lay( $ht[0] );
$r_sai   = $gui_ht( 'nhan_form', $HV::HANH_DONG . $ht[0], array( 'viec' => (string) $ht[0], 'quyet' => 'xong', 'phien' => (string) $phien_0, 'tiep' => $DSV::url( $loc_ht, 1, $ht[1] ), '_wp_http_referer' => $ve_ht ) );
$r_la    = $gui_ht( 'nhan_form', $HV::HANH_DONG . $ht[2], array( 'viec' => (string) $ht[2], 'quyet' => 'de_sau', 'phien' => (string) Viec::lay( $ht[2] )['phien'], 'tiep' => 'https://evil.example/wp-admin/admin.php?page=phongdao-hop-viec', '_wp_http_referer' => $DSV::url( $loc_ht, 1, $ht[2] ) ) );
kt( 'Hộp việc hộp thư: quyết xong tự sang thẻ kế (đích form gửi kèm), có "vua" để xem lại và Hoàn tác; quyết hỏng thì ở lại thẻ đó; đích ngoài Hộp việc bị bỏ', str_contains( $r_ht, 'redirect: ' ) && str_contains( $r_ht, 'viec=' . $ht[1] ) && str_contains( $r_ht, 'vua=' . $ht[0] ) && str_contains( $r_ht, 'hoan_viec=' . $ht[0] ) && Viec::DE_SAU === $v_ht0['trang_thai'] && str_contains( $r_sai, 'viec=' . $ht[0] ) && ! str_contains( $r_sai, 'vua=' ) && ! str_contains( $r_la, 'evil.example' ) && str_contains( $r_la, 'viec=' . $ht[2] ), array( $r_ht, $r_sai, $r_la ) );
$_GET   = $loc_ht + array( 'viec' => (string) $ht[1], 'da' => 'Đã để sau.', 'vua' => (string) $ht[0], 'hoan_viec' => (string) $ht[0], 'hoan_phien' => (string) $v_ht0['phien'] );
$h_ht3  = $DSV::hien();
$r_hoan = $gui_ht( 'nhan_hoan_tac', $HV::HOAN_TAC . $ht[0], array( 'viec' => (string) $ht[0], 'phien' => (string) $v_ht0['phien'], 'tiep' => $DSV::url( $loc_ht, 1, $ht[0] ), '_wp_http_referer' => $DSV::url( $loc_ht, 1, $ht[1] ) ) );
kt( 'Hộp việc hộp thư: sau khi quyết, thẻ kế mở kèm lời báo, lối "Xem lại thẻ vừa quyết" và nút Hoàn tác 10 giây; Hoàn tác đưa về đúng thẻ vừa quyết, thẻ trở lại Chờ', str_contains( $h_ht3, 'Đã để sau.' ) && str_contains( $h_ht3, 'href="' . esc_url( $DSV::url( $loc_ht, 1, $ht[0] ) ) . '">Xem lại thẻ vừa quyết</a>' ) && str_contains( $h_ht3, 'name="action" value="' . $HV::HOAN_TAC . '"' ) && str_contains( $h_ht3, 'data-hv-hoan' ) && str_contains( $h_ht3, 'id="viec-' . $ht[1] . '"' ) && str_contains( $r_hoan, 'viec=' . $ht[0] ) && Viec::CHO === Viec::lay( $ht[0] )['trang_thai'], array( $r_hoan ) );
remove_filter( 'wp_die_handler', $chan_die_ht );
remove_filter( 'wp_redirect', $chan_di_ht );
// Làm nhanh nhiều thẻ từ thanh nổi: vài thẻ đã chọn thì làm ngay (ghi thành lô), "mọi thẻ khớp bộ lọc" thì qua xem trước
$TLH  = \Phongdao\GiaoTiep\QuanTri\TrangLo::class;
add_filter( 'wp_die_handler', $chan_die_ht );
add_filter( 'wp_redirect', $chan_di_ht );
$gui_nhanh = static function ( array $than ) use ( $TLH ): string {
	$_POST    = $than + array( '_wpnonce' => wp_create_nonce( 'phongdao_lo_tao' ), 'tt' => 'cho', 'loai' => 'can_xem', 'q' => '' );
	$_REQUEST = $_POST;
	try {
		$TLH::tao();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$r_nh   = $gui_nhanh( array( 'nhanh' => 'viec_de_sau', 'pham_vi' => 'chon', 'ids' => array( (string) $ht[0], (string) $ht[1] ) ) );
$bao_nh = get_transient( 'phongdao_lo_bao_' . $ai_ql );
$_GET   = $loc_ht;
$h_nh   = $DSV::hien();
$r_nhl  = $gui_nhanh( array( 'nhanh' => 'viec_de_sau', 'pham_vi' => 'loc', 'ids' => array() ) );
$lo_nhl = Viec::ds_lo( $ai_ql, 1 )[0] ?? array();
remove_filter( 'wp_die_handler', $chan_die_ht );
remove_filter( 'wp_redirect', $chan_di_ht );
$_POST = $_REQUEST = array();
kt( 'Hộp việc hộp thư: "Để sau" làm nhanh 2 thẻ đã chọn chạy ngay, về Hộp việc, lời báo có lối xem kết quả từng thẻ; "mọi thẻ khớp bộ lọc" thì mở màn xem trước, chưa đổi gì', str_contains( $r_nh, 'page=phongdao-hop-viec' ) && ! str_contains( $r_nh, 'page=phongdao-lo' ) && Viec::DE_SAU === Viec::lay( $ht[0] )['trang_thai'] && Viec::DE_SAU === Viec::lay( $ht[1] )['trang_thai'] && str_contains( (string) ( $bao_nh['chu'] ?? '' ), 'Để sau: 2 thẻ xong' ) && (int) ( $bao_nh['lo'] ?? 0 ) > 0 && str_contains( $h_nh, 'Xem kết quả từng thẻ' ) && str_contains( $r_nhl, 'page=phongdao-lo&lo=' ) && Viec::LO_XEM === ( $lo_nhl['trang_thai'] ?? '' ) && Viec::CHO === ( Viec::lay( (int) ( $lo_nhl['noi_dung']['muc'][0][0] ?? 0 ) )['trang_thai'] ?? '' ), array( $r_nh, $bao_nh, $r_nhl ) );
\Phongdao\DichVu\XuLyLo\Lo::huy( (int) ( $lo_nhl['id'] ?? 0 ), $ai_ql );
foreach ( array_merge( $ht, array( $ht_n ) ) as $id_ht ) {
	Viec::xoa( (int) $id_ht );
}
$_GET = array();
wp_set_current_user( 0 );
wp_set_current_user( $ai_ql );
$_GET = array( 'id' => (string) $tp1 );
$admin_sua = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
$kiem_admin_sua = array( 'form' => str_contains( $admin_sua, 'name="action" value="phongdao_sua_phan"' ), 'loi' => str_contains( $admin_sua, 'id="phan-loi"' ), 'y_nghia' => str_contains( $admin_sua, 'id="phan-y_nghia"' ), 'goc' => str_contains( $admin_sua, 'class="goc-nguon no-print"' ) && str_contains( $admin_sua, 'Lời:' ), 'viec' => ( array() === Viec::mo( 'post', $tp1 ) ) !== str_contains( $admin_sua, 'Việc đang chờ' ), 'xem' => str_contains( $admin_sua, 'Xem trên trang đọc' ), 'co' => str_contains( $admin_sua, 'id="phan-co_nghi"' ) && str_contains( $admin_sua, 'Cờ "Có thể không phải dân gian"' ) );
kt( 'wp-admin Sửa tác phẩm: form theo phần dùng trường cũ, có nguồn gốc, việc chờ (chỉ khi có), công tắc cờ ở cột bên và bản xem trước', ! in_array( false, $kiem_admin_sua, true ), $kiem_admin_sua );
$loi_tp1  = (array) ( PhanBai::lay( $tp1, 'loi' )['dong'] ?? array() );
$tt_tp1   = get_post_status( $tp1 );
$kiem_tt  = array(
	'loi_hien'  => str_contains( $admin_sua, 'class="phongdao-loi-xem"' ) && ( ! $loi_tp1 || str_contains( $admin_sua, esc_html( (string) $loi_tp1[0] ) ) ),
	'trang_thai' => str_contains( $admin_sua, 'class="phongdao-trang-thai"' ) && str_contains( $admin_sua, 'name="action" value="phongdao_doi_trang_thai"' ) && str_contains( $admin_sua, 'name="trang_thai_cu" value="' . $tt_tp1 . '"' ),
	'nut'       => 'publish' === $tt_tp1 ? str_contains( $admin_sua, 'Ẩn khỏi người đọc' ) && ! str_contains( $admin_sua, '>Đăng</button>' ) : str_contains( $admin_sua, '>Đăng</button>' ),
	'hai_nut'   => str_contains( $admin_sua, 'Lưu và hiện cho người đọc' ) && str_contains( $admin_sua, 'Lưu phần này' ),
);
kt( 'wp-admin Sửa tác phẩm: lời luôn hiện ở đầu, có nút đổi trạng thái theo trạng thái hiện tại (Đăng chỉ khi chưa đăng), Ý nghĩa có hai nút lưu', ! in_array( false, $kiem_tt, true ), $kiem_tt );
// Đổi trạng thái qua nút trên trang sửa: cùng dịch vụ dải "Đang sửa" — ghi nhật ký, chặn khi bài đã đổi ở phiên khác
$yc_tt = new WP_REST_Request( 'POST', '/phongdao/v1/tac-pham/' . $tp1 . '/trang-thai' );
$yc_tt->set_param( 'id', $tp1 );
$yc_tt->set_param( 'trang_thai', 'private' );
$yc_tt->set_param( 'trang_thai_cu', 'draft' === $tt_tp1 ? 'publish' : 'draft' );
$tt_cu_sai = \Phongdao\GiaoTiep\BienTap\Module::trang_thai( $yc_tt );
kt( 'đổi trạng thái với trạng thái cũ đã lỗi thời bị từ chối, bài giữ nguyên', ! $tt_cu_sai['ok'] && $tt_tp1 === get_post_status( $tp1 ), $tt_cu_sai );
$khoa_loi_sua = 'phongdao_sua_loi_' . $tp1 . '_' . $ai_ql;
set_transient( $khoa_loi_sua, 'Phần thử chưa hợp lệ.', MINUTE_IN_SECONDS );
$_GET['ket_qua'] = 'loi';
kt( 'wp-admin Sửa tác phẩm: hiện lỗi kiểm dữ liệu đúng một lần', str_contains( \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien(), 'Phần thử chưa hợp lệ.' ) && false === get_transient( $khoa_loi_sua ) );
$ND = \Phongdao\GiaoTiep\BienTap\NhapDo::class;
$TS = \Phongdao\GiaoTiep\BienTap\TamSua::class;
$vao_do = array( 'muc' => array( 'm123' => array( 'goc' => '', 'tu' => 'Từ kỹ thuật', 'loi_giai' => str_repeat( 'a', 4001 ) . '<script>mau</script>' ) ), 'action' => 'khong_giu', '_wpnonce' => 'khong_giu' );
kt( 'giữ form: bỏ action/nonce, giữ nguyên đầu vào vượt giới hạn để người soạn sửa', ! isset( $ND::loc( $vao_do )['action'], $ND::loc( $vao_do )['_wpnonce'] ) && $vao_do['muc'] === $ND::loc( $vao_do )['muc'] );
$ve_do = $ND::giu( $tp1, 'chu_thich', $vao_do );
wp_set_current_user( 0 );
kt( 'vé đầu vào lỗi không đọc được ở tài khoản khác', null === $ND::lay( $ve_do, $tp1, 'chu_thich' ) );
wp_set_current_user( $ai_ql );
kt( 'vé đầu vào lỗi ràng buộc đúng bài và phần', null === $ND::lay( $ve_do, $tp1 + 1, 'chu_thich' ) && null === $ND::lay( $ve_do, $tp1, 'di_ban' ) );
$do_doc = $ND::lay( $ve_do, $tp1, 'chu_thich' );
kt( 'vé form đọc đúng dữ liệu một lần, lần sau không đọc lại', $ND::loc( $vao_do ) === $do_doc && null === $ND::lay( $ve_do, $tp1, 'chu_thich' ) );
$phan_truoc_do = PhanBai::lay( $tp1, 'chu_thich' );
$_GET = array( 'id' => (string) $tp1, 'ket_qua' => 'loi', 'phan' => 'chu_thich', 'nhap_do' => $ND::giu( $tp1, 'chu_thich', $vao_do ) );
$h_do = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
kt( 'server dựng lại hàng mới và đầu vào dài khi lỗi, escape HTML, không ghi phần bài', str_contains( $h_do, 'data-giu-loi="1"' ) && str_contains( $h_do, 'Từ kỹ thuật' ) && str_contains( $h_do, str_repeat( 'a', 4001 ) . '&lt;script&gt;mau&lt;/script&gt;' ) && ! str_contains( $h_do, '<script>mau</script>' ) && $phan_truoc_do === PhanBai::lay( $tp1, 'chu_thich' ) );
kt( 'hàng từ khó điền dở báo thiếu, hàng trống/xoá không chặn lưu', 1 === count( $TS::loi_form( 'chu_thich', array( 'muc' => array( array( 'tu' => 'Từ đang nhập' ) ) ) ) ) && array() === $TS::loi_form( 'chu_thich', array( 'muc' => array( array(), array( 'tu' => 'Bỏ', 'xoa' => '1' ) ) ) ) );
$_GET = array( 'id' => (string) $tp1 );
wp_set_current_user( 0 );
kt( 'wp-admin Sửa tác phẩm: khách không nhận form và nguồn gốc nội bộ', ! str_contains( \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien(), 'phongdao_sua_phan' ) );
wp_set_current_user( $ai_ql );

// ---------- wp-admin: sửa phân loại tay, khối Lịch sử (T-169) ----------
$LPL  = \Phongdao\DichVu\LuuPhan\LuuPhanLoai::class;
$pl_a = (int) wp_insert_term( 'Chủ đề thử A', 'chu_de' )['term_id'];
$pl_b = (int) wp_insert_term( 'Chủ đề thử B', 'chu_de' )['term_id'];
$pl_v = (int) wp_insert_term( 'Vùng thử', 'vung' )['term_id'];
$pl_h = (int) wp_insert_term( 'Lối hát thử', 'hinh_thuc' )['term_id'];
$cd_truoc = $LPL::dang_gan( $tp1, 'chu_de' );
$kq_pl    = $LPL::luu( $tp1, 'chu_de', array_merge( $cd_truoc, array( $pl_b, $pl_a, $pl_a ) ), $cd_truoc, $ai_ql );
$nk_pl    = Viec::lay( $kq_pl['viec'] );
kt( 'sửa phân loại tay: gắn hai chủ đề có sẵn, ghi nhật ký trước/sau kèm tên và người', $kq_pl['ok'] && $kq_pl['doi'] && has_term( $pl_a, 'chu_de', $tp1 ) && has_term( $pl_b, 'chu_de', $tp1 ) && Viec::SUA === ( $nk_pl['loai'] ?? '' ) && 'chu_de' === $nk_pl['phan'] && $cd_truoc === $nk_pl['truoc']['term_ids'] && in_array( 'Chủ đề thử A', $nk_pl['sau']['ten'], true ) && $ai_ql === $nk_pl['nguoi_quyet'], array( $kq_pl, $nk_pl ) );
$cd_sau  = $LPL::dang_gan( $tp1, 'chu_de' );
$kq_pl_0 = $LPL::luu( $tp1, 'chu_de', array_reverse( $cd_sau ), $cd_sau, $ai_ql );
kt( 'sửa phân loại tay: chọn lại đúng tập đang gắn thì không ghi gì', $kq_pl_0['ok'] && ! $kq_pl_0['doi'] && 0 === $kq_pl_0['viec'], $kq_pl_0 );
$kq_pl_cu = $LPL::luu( $tp1, 'chu_de', $cd_truoc, $cd_truoc, $ai_ql );
kt( 'sửa phân loại tay: màn hình cũ (bài vừa đổi ở nơi khác) bị từ chối, không ghi đè', ! $kq_pl_cu['ok'] && $cd_sau === $LPL::dang_gan( $tp1, 'chu_de' ) && str_contains( implode( ' ', $kq_pl_cu['loi'] ), 'Tải lại trang' ), $kq_pl_cu );
$so_cd   = (int) wp_count_terms( array( 'taxonomy' => 'chu_de', 'hide_empty' => false ) );
$kq_pl_l = $LPL::luu( $tp1, 'chu_de', array_merge( $cd_sau, array( $pl_v ) ), $cd_sau, $ai_ql );
$kq_pl_k = $LPL::luu( $tp1, 'chu_de', array_merge( $cd_sau, array( 999999 ) ), $cd_sau, $ai_ql );
kt( 'sửa phân loại tay: mục của bộ khác hoặc không có thì từ chối, không tạo mục mới', ! $kq_pl_l['ok'] && ! $kq_pl_k['ok'] && $cd_sau === $LPL::dang_gan( $tp1, 'chu_de' ) && $so_cd === (int) wp_count_terms( array( 'taxonomy' => 'chu_de', 'hide_empty' => false ) ) && ! has_term( $pl_v, 'vung', $tp1 ), array( $kq_pl_l, $kq_pl_k ) );
$kq_pl_tl = $LPL::luu( $tp1, KhaiBao::THE_LOAI, array(), array(), $ai_ql );
$kq_pl_mt = $LPL::luu( $tp1, 'muc_tu', array(), array(), $ai_ql );
$kq_pl_tc = $LPL::luu( $tp1, 'tro_choi', array(), array(), $ai_ql );
kt( 'sửa phân loại tay: không sửa thể loại (đổi đường dẫn), mục từ; trò chơi chỉ cho đồng dao', ! $kq_pl_tl['ok'] && ! $kq_pl_mt['ok'] && ! $kq_pl_tc['ok'] && has_term( 'ca-dao', KhaiBao::THE_LOAI, $tp1 ) && $LPL::sua_duoc( 'tro_choi', 'dong-dao' ), array( $kq_pl_tl, $kq_pl_tc ) );
$dd_pl   = $mau_id['dong-dao-mau'];
$ht_truoc = $LPL::dang_gan( $dd_pl, 'hinh_thuc' );
$kq_ht   = $LPL::luu( $dd_pl, 'hinh_thuc', array( $pl_h ), $ht_truoc, $ai_ql );
$bon_chu = get_term_by( 'slug', 'bon-chu', 'hinh_thuc' );
$kq_ht_m = $LPL::luu( $dd_pl, 'hinh_thuc', array( $pl_h, (int) $bon_chu->term_id ), array( $pl_h ), $ai_ql );
kt( 'sửa phân loại tay: hình thức gắn lối hát, giữ thể thơ máy tính; không gắn tay thể thơ', $kq_ht['ok'] && has_term( $pl_h, 'hinh_thuc', $dd_pl ) && has_term( 'bon-chu', 'hinh_thuc', $dd_pl ) && array( $pl_h ) === $LPL::dang_gan( $dd_pl, 'hinh_thuc' ) && ! $kq_ht_m['ok'], array( $kq_ht, $kq_ht_m ) );
$kq_ht_go = $LPL::luu( $dd_pl, 'hinh_thuc', $ht_truoc, array( $pl_h ), $ai_ql );
kt( 'sửa phân loại tay: gỡ lối hát vẫn giữ thể thơ máy tính', $kq_ht_go['ok'] && ! has_term( $pl_h, 'hinh_thuc', $dd_pl ) && has_term( 'bon-chu', 'hinh_thuc', $dd_pl ), $kq_ht_go );
$so_ls_pl     = count( Viec::lich_su( 'post', $tp1, 500 ) );
$da_chan_pl   = false;
$chan_pl      = static function ( $sql ) use ( $wpdb, &$da_chan_pl ) {
	if ( ! $da_chan_pl && preg_match( '/^INSERT\s+INTO\s+`?' . preg_quote( $wpdb->term_relationships, '/' ) . '`?/i', $sql ) ) {
		$da_chan_pl = true;
		return 'CA_DAO_KIEM_THU_LOI_GAN_PHAN_LOAI';
	}
	return $sql;
};
add_filter( 'query', $chan_pl );
$kq_pl_loi = $LPL::luu( $tp1, 'vung', array_merge( $LPL::dang_gan( $tp1, 'vung' ), array( $pl_v ) ), $LPL::dang_gan( $tp1, 'vung' ), $ai_ql );
remove_filter( 'query', $chan_pl );
clean_object_term_cache( $tp1, KhaiBao::TAC_PHAM );
kt( 'lỗi gắn phân loại: huỷ cả nhật ký, bài giữ nguyên', $da_chan_pl && ! $kq_pl_loi['ok'] && ! has_term( $pl_v, 'vung', $tp1 ) && $so_ls_pl === count( Viec::lich_su( 'post', $tp1, 500 ) ), $kq_pl_loi );
$_GET     = array( 'id' => (string) $tp1, 'phan_loai' => 'chu_de' );
$admin_pl = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
$kiem_pl  = array(
	'form'      => str_contains( $admin_pl, 'name="action" value="phongdao_sua_phan_loai"' ) && str_contains( $admin_pl, 'name="tax" value="chu_de"' ) && str_contains( $admin_pl, 'name="tax" value="vung"' ),
	'mo_lai'    => str_contains( $admin_pl, 'id="phan-loai-chu_de" open' ),
	'da_chon'   => str_contains( $admin_pl, 'name="chon[]" value="' . $pl_a . '" checked=' ) && ! str_contains( $admin_pl, 'name="chon[]" value="' . $pl_v . '" checked=' ),
	'da_thay'   => str_contains( $admin_pl, 'name="da_thay[]" value="' . $pl_a . '"' ),
	'chi_xem'   => ! str_contains( $admin_pl, 'name="tax" value="the_loai"' ) && ! str_contains( $admin_pl, 'name="tax" value="muc_tu"' ) && ! str_contains( $admin_pl, 'id="phan-loai-tro_choi"' ) && ( current_user_can( 'cadao_duyet' ) ? str_contains( $admin_pl, 'value="phongdao_doi_the_loai"' ) : ! str_contains( $admin_pl, 'value="phongdao_doi_the_loai"' ) ),
	'lich_su'   => str_contains( $admin_pl, 'id="lich-su"' ) && str_contains( $admin_pl, 'Chủ đề: sửa' ) && str_contains( $admin_pl, 'thêm Chủ đề thử' ),
);
kt( 'wp-admin Sửa tác phẩm: khối Phân loại chọn mục có sẵn, giữ tập đã thấy; thể loại không sửa trong khối phân loại (chỉ qua "Đổi thể loại" của người có quyền duyệt), mục từ chỉ xem; có Lịch sử', ! in_array( false, $kiem_pl, true ), $kiem_pl );
$_GET     = array( 'id' => (string) $dd_pl );
$admin_dd = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
kt( 'wp-admin Sửa tác phẩm: đồng dao có Trò chơi; thể thơ máy tính chỉ hiện để xem', str_contains( $admin_dd, 'id="phan-loai-tro_choi"' ) && str_contains( $admin_dd, 'Thể thơ máy tự tính từ lời' ) && ! str_contains( $admin_dd, 'name="chon[]" value="' . (int) $bon_chu->term_id . '"' ) );
kt( 'Lịch sử trên trang đọc (chế độ sửa): ghi mục thêm, gỡ của phân loại', str_contains( \Phongdao\GiaoTiep\BienTap\CheDoSua::lich_su( \Phongdao\GiaoTiep\BienTap\CheDoSua::ngu_canh( get_post( $tp1 ) ) ), 'Chủ đề: sửa' ) );
$_GET = array( 'id' => (string) $tp1 );
wp_set_current_user( $doc_gia );
$admin_dg = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
wp_set_current_user( 0 );
$admin_khach = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
kt( 'wp-admin Sửa tác phẩm: người đọc, khách không nhận form phân loại hay lịch sử', ! str_contains( $admin_dg . $admin_khach, 'phongdao_sua_phan_loai' ) && ! str_contains( $admin_dg . $admin_khach, 'id="lich-su"' ) );
// Gửi thẳng tới admin-post: người không đủ quyền, hoặc thiếu nonce, bị chặn trước khi ghi
$chan_die = static fn() => static function ( $chu ): void {
	throw new \RuntimeException( 'wp_die: ' . ( is_string( $chu ) ? $chu : 'lỗi' ) );
};
add_filter( 'wp_die_handler', $chan_die );
$gui_pl = static function ( int $nguoi, bool $nonce ) use ( $tp1, $cd_sau ): string {
	wp_set_current_user( $nguoi );
	// Gửi hợp lệ thì gỡ hết chủ đề (tập đã thấy đúng bản hiện tại)
	$_POST = $_REQUEST = array( 'id' => (string) $tp1, 'tax' => 'chu_de', 'chon' => array(), 'da_thay' => array_map( 'strval', $cd_sau ) );
	if ( $nonce ) {
		$_POST['_wpnonce'] = $_REQUEST['_wpnonce'] = wp_create_nonce( 'phongdao_sua_phan_loai_' . $tp1 . '_chu_de' );
	}
	try {
		\Phongdao\GiaoTiep\QuanTri\SuaTacPham::luu_phan_loai();
		return 'không chặn';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$gui_dg = $gui_pl( $doc_gia, true );
$gui_kn = $gui_pl( $ai_ql, false );
remove_filter( 'wp_die_handler', $chan_die );
$_POST = $_REQUEST = array();
kt( 'wp-admin sửa phân loại: người đọc gửi thẳng bị chặn; thiếu nonce bị chặn; bài không đổi', str_starts_with( $gui_dg, 'wp_die' ) && str_starts_with( $gui_kn, 'wp_die' ) && $LPL::dang_gan( $tp1, 'chu_de' ) === $cd_sau, array( $gui_dg, $gui_kn ) );
wp_set_current_user( $ai_ql );
$LPL::luu( $tp1, 'chu_de', $cd_truoc, $LPL::dang_gan( $tp1, 'chu_de' ), $ai_ql );
foreach ( array( array( $pl_a, 'chu_de' ), array( $pl_b, 'chu_de' ), array( $pl_v, 'vung' ), array( $pl_h, 'hinh_thuc' ) ) as [ $t_pl, $tax_pl ] ) {
	wp_delete_term( $t_pl, $tax_pl );
}
kt( 'sửa phân loại tay: trả bài thử về chủ đề cũ', $cd_truoc === $LPL::dang_gan( $tp1, 'chu_de' ) );
$_GET = array();

// ---------- wp-admin Kho: tự chọn câu đưa vào Hộp việc (T-184) ----------
$DSK      = \Phongdao\GiaoTiep\QuanTri\DanhSachKho::class;
$bang_kho = Kho::bang();
$wpdb->query( 'START TRANSACTION' );
for ( $i = 1; $i <= 1050; $i++ ) {
	$wpdb->insert( $bang_kho, array( 'nguon_id' => 'kho-thu', 'ma_goc' => 'kt-' . $i, 'url' => 'https://example.org/kho-thu/' . $i . '/', 'loai' => 0 === $i % 3 ? 'cau_do' : 'ca_dao', 'tieu_de' => 'Câu kho thử ' . $i, 'loi' => "Câu kho thử $i dòng một\nCâu kho thử $i dòng hai", 'du_lieu' => '{}', 'ngay_lay' => current_time( 'mysql' ), 'trang_thai' => 'moi', 'doc_trang' => '2026-11-09' ) );
}
$wpdb->query( 'COMMIT' );
$kt_ids = array_map( 'intval', $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $bang_kho WHERE nguon_id = %s ORDER BY id", 'kho-thu' ) ) );
$_GET   = array( 'nguon' => 'kho-thu' );
$q_kho  = $wpdb->num_queries;
$h_kho  = $DSK::hien();
$q_kho  = $wpdb->num_queries - $q_kho;
kt( 'wp-admin Kho: hơn 1.000 câu đếm đủ, 30 dòng mới lấy trước, có Trang sau và nút Để dành trên từng dòng', 1050 === count( $kt_ids ) && str_contains( $h_kho, '<strong>1050</strong> câu khớp' ) && 30 === substr_count( $h_kho, 'data-nhan="Câu"' ) && str_contains( $h_kho, 'Trang sau' ) && 30 === substr_count( $h_kho, 'value="phongdao_kho_danh_dau"' ) && strpos( $h_kho, 'Câu kho thử 1050' ) < strpos( $h_kho, 'Câu kho thử 1049' ) );
kt( 'wp-admin Kho: số truy vấn không tăng theo 30 dòng', $q_kho <= 20, $q_kho );
kt( 'wp-admin Kho: thanh "Chọn tất cả trên trang này" ngay trên bảng, ẩn khi không có JavaScript, đếm theo số câu còn đưa vào được; ô chọn hết không gửi lên máy chủ', str_contains( $h_kho, '<div class="phongdao-chon-het" hidden data-tong="' . $DSK::dem_chon( array( 'nguon' => 'kho-thu', 'q' => '', 'loai' => '', 'tt' => 'moi', 'the' => 'tat_ca' ) ) . '" data-don-vi="câu">' ) && str_contains( $h_kho, '<input type="checkbox" class="phongdao-chon-het-o">' ) && strpos( $h_kho, 'phongdao-chon-het' ) < strpos( $h_kho, '<table class="widefat' ) );
$_GET   = array( 'nguon' => 'kho-thu', 'loai' => 'cau_do' );
$h_loai = $DSK::hien();
$_GET   = array( 'nguon' => 'kho-thu', 'q' => 'thử 1049 dòng' );
$h_tim  = $DSK::hien();
$_GET   = array( 'q' => (string) $kt_ids[5], 'tt' => 'tat_ca' );
$h_id   = $DSK::hien();
$_GET   = array( 'nguon' => 'kho-thu', 'tt' => 'da_tao_bai' );
$h_tt   = $DSK::hien();
kt( 'wp-admin Kho: lọc thể loại, trạng thái; tìm chữ trong lời, tìm ID', str_contains( $h_loai, '<strong>350</strong> câu khớp' ) && str_contains( $h_tim, '<strong>1</strong> câu khớp' ) && str_contains( $h_tim, 'Câu kho thử 1049' ) && str_contains( $h_id, '<strong>1</strong> câu khớp' ) && str_contains( $h_id, 'Câu kho thử 6' ) && str_contains( $h_tt, 'Không có câu nào khớp' ) );
$k1  = $kt_ids[0];
$d_1 = UngVien::dua_vao_hop_viec( $k1, $ai_ql );
$d_2 = UngVien::dua_vao_hop_viec( $k1, $ai_ql );
$t_1 = Viec::mo( 'kho', $k1, 'cau_moi' );
kt( 'Kho: đưa một câu vào Hộp việc tạo đúng một thẻ Câu mới (người chọn, đứng trước câu máy chọn); bấm lại không thêm thẻ', $d_1['ok'] && $d_1['moi'] && $d_2['ok'] && ! $d_2['moi'] && $d_1['viec'] === $d_2['viec'] && 1 === count( $t_1 ) && (string) $ai_ql === $t_1[0]['nguoi_tao'] && UngVien::UU_TIEN_CHON_TAY === $t_1[0]['uu_tien'] && $ai_ql === (int) ( $t_1[0]['noi_dung']['chon_boi'] ?? 0 ) && 'moi' === Kho::lay( $k1 )->trang_thai && 0 === (int) Kho::lay( $k1 )->post_id, array( $d_1, $d_2, $t_1 ) );
$_GET = array( 'nguon' => 'kho-thu', 'the' => 'co' );
$h_co = $DSK::hien();
kt( 'wp-admin Kho: câu đã có thẻ hiện liên kết thẻ, không còn nút; lọc "Đang có thẻ Câu mới"', str_contains( $h_co, '<strong>1</strong> câu khớp' ) && str_contains( $h_co, 'Thẻ Câu mới #' . $d_1['viec'] ) && ! str_contains( $h_co, 'name="id" value="' . $k1 . '"' ) );
kt( 'Hộp việc: thẻ Câu mới chọn tay ghi "người chọn từ Kho"', str_contains( \Phongdao\GiaoTiep\BienTap\HopViec::the( Viec::lay( $d_1['viec'] ) ), 'người chọn từ Kho' ) );
$wpdb->update( $bang_kho, array( 'trang_thai' => 'bo_qua' ), array( 'id' => $kt_ids[1] ) );
$wpdb->update( $bang_kho, array( 'trang_thai' => 'da_tao_bai', 'post_id' => $tp1 ), array( 'id' => $kt_ids[2] ) );
$wpdb->update( $bang_kho, array( 'loi' => '' ), array( 'id' => $kt_ids[3] ) );
$k_bo   = UngVien::dua_vao_hop_viec( $kt_ids[1], $ai_ql );
$k_bai  = UngVien::dua_vao_hop_viec( $kt_ids[2], $ai_ql );
$k_rong = UngVien::dua_vao_hop_viec( $kt_ids[3], $ai_ql );
$k_khon = UngVien::dua_vao_hop_viec( 999999999, $ai_ql );
$_GET   = array( 'q' => (string) $kt_ids[2], 'tt' => 'tat_ca' );
$h_bai  = $DSK::hien();
kt( 'Kho: câu đã bỏ, đã thành bài, chưa có lời, không có thì không tạo thẻ; dòng đã thành bài không có nút', ! $k_bo['ok'] && ! $k_bai['ok'] && ! $k_rong['ok'] && ! $k_khon['ok'] && array() === Viec::mo( 'kho', $kt_ids[1], 'cau_moi' ) && array() === Viec::mo( 'kho', $kt_ids[2], 'cau_moi' ) && array() === Viec::mo( 'kho', $kt_ids[3], 'cau_moi' ) && str_contains( $h_bai, 'Bài #' . $tp1 ) && ! str_contains( $h_bai, 'value="phongdao_kho_danh_dau"' ), array( $k_bo, $k_bai, $k_rong ) );
ChongTrung::ghi_cho( 'kho:' . $kt_ids[4], 'kho:' . $kt_ids[5], 'gan_giong', 80, 'Cặp thử chờ quyết.' );
$k_trung = UngVien::dua_vao_hop_viec( $kt_ids[4], $ai_ql );
$_GET    = array( 'q' => (string) $kt_ids[4] );
$h_trung = $DSK::hien();
kt( 'Kho: câu đang chờ quyết trùng không đưa vào được, dòng báo "Đang chờ quyết trùng"', ! $k_trung['ok'] && str_contains( $k_trung['thong_bao'], 'Có thể trùng' ) && array() === Viec::mo( 'kho', $kt_ids[4], 'cau_moi' ) && str_contains( $h_trung, 'Đang chờ quyết trùng' ) && ! str_contains( $h_trung, 'value="phongdao_kho_danh_dau"' ), $k_trung );
$wpdb->delete( ChongTrung::bang( 'trung' ), array( 'a' => 'kho:' . $kt_ids[4] ) );
foreach ( Viec::mo( 'kho', $kt_ids[4], 'co_the_trung' ) as $v_tr ) {
	Viec::xoa( $v_tr['id'] );
}
$may_kt  = UngVien::chon( array( 'id' => 'kho-thu', 'bo_qua_co' => array(), 'loai_lay' => array( 'ca_dao' ) ), 2 );
$the_may = array_merge( Viec::mo( 'kho', $kt_ids[4], 'cau_moi' ), Viec::mo( 'kho', $kt_ids[6], 'cau_moi' ) );
kt( 'máy chọn hằng ngày đi chung đường tạo thẻ: đúng 2 thẻ mới của máy, bỏ câu người đã chọn', 2 === $may_kt && 2 === count( $the_may ) && 'may' === $the_may[0]['nguoi_tao'] && 40 === $the_may[1]['uu_tien'] && 1 === count( Viec::mo( 'kho', $k1, 'cau_moi' ) ), array( $may_kt, $the_may ) );
// Gửi thẳng tới admin-post: quyền, nonce, đường quay lại
$chan_die_kho = static fn() => static function ( $chu ): void {
	throw new \RuntimeException( 'wp_die: ' . ( is_string( $chu ) ? $chu : 'lỗi' ) );
};
$chan_di_kho = static function ( $vi_tri ): string {
	throw new \RuntimeException( 'redirect: ' . $vi_tri );
};
add_filter( 'wp_die_handler', $chan_die_kho );
add_filter( 'wp_redirect', $chan_di_kho );
$gui_kho = static function ( int $nguoi, int $kho, bool $nonce, string $quay_lai = '' ) use ( $DSK ): string {
	wp_set_current_user( $nguoi );
	$_POST = $_REQUEST = array( 'id' => (string) $kho, 'quay_lai' => $quay_lai );
	if ( $nonce ) {
		$_POST['_wpnonce'] = $_REQUEST['_wpnonce'] = wp_create_nonce( $DSK::HANH_DONG . '_' . $kho );
	}
	try {
		$DSK::dua_vao();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$k8      = $kt_ids[7];
$ve_kho  = $DSK::url( array( 'nguon' => 'kho-thu' ), 3 );
$r_dg    = $gui_kho( $doc_gia, $k8, true );
$r_kn    = $gui_kho( $ai_ql, $k8, false );
$the_k8  = Viec::mo( 'kho', $k8, 'cau_moi' );
$r_ok    = $gui_kho( $ai_ql, $k8, true, $ve_kho );
$bao_ok  = get_transient( 'phongdao_kho_bao_' . $ai_ql );
$r_la    = $gui_kho( $ai_ql, $k8, true, 'https://evil.example/' );
$bao_la  = get_transient( 'phongdao_kho_bao_' . $ai_ql );
remove_filter( 'wp_die_handler', $chan_die_kho );
remove_filter( 'wp_redirect', $chan_di_kho );
$_POST   = $_REQUEST = array();
kt( 'wp-admin Kho: người đọc gửi thẳng bị chặn; thiếu nonce bị chặn; không có thẻ', str_starts_with( $r_dg, 'wp_die' ) && str_starts_with( $r_kn, 'wp_die' ) && array() === $the_k8, array( $r_dg, $r_kn ) );
kt( 'wp-admin Kho: gửi đúng tạo thẻ, quay về đúng trang lọc; đường quay lại lạ thì về Kho; bấm lại báo đã có', 'redirect: ' . $ve_kho === $r_ok && ! empty( $bao_ok['ok'] ) && 1 === count( Viec::mo( 'kho', $k8, 'cau_moi' ) ) && 'redirect: ' . $DSK::url() === $r_la && ! empty( $bao_la['ok'] ) && str_contains( (string) $bao_la['chu'], 'đã có trong Hộp việc' ), array( $r_ok, $r_la, $bao_ok, $bao_la ) );
$_GET   = array( 'nguon' => 'kho-thu' );
$h_bao  = $DSK::hien();
kt( 'wp-admin Kho: báo kết quả một lần, có lối mở thẻ', str_contains( $h_bao, 'đã có trong Hộp việc' ) && str_contains( $h_bao, 'Mở thẻ' ) && ! str_contains( $DSK::hien(), 'Mở thẻ' ) );
wp_set_current_user( $doc_gia );
$h_dg = $DSK::hien();
wp_set_current_user( 0 );
$h_kh = $DSK::hien();
wp_set_current_user( $ai_ql );
kt( 'wp-admin Kho: người đọc, khách không thấy dữ liệu kho', ! str_contains( $h_dg . $h_kh, 'Câu kho thử' ) && ! str_contains( $h_dg . $h_kh, 'example.org/kho-thu' ) );
// ---------- Đăng hàng loạt từ Kho (D-099): chọn nhiều câu → Hộp việc → Đăng theo lô ----------
$TL     = \Phongdao\GiaoTiep\QuanTri\TrangLo::class;
$LOK    = \Phongdao\DichVu\XuLyLo\Lo::class;
$soan_k = (int) wp_insert_user( array( 'user_login' => 'soankho', 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ) );
( new WP_User( $soan_k ) )->add_cap( 'cadao_soan' );
wp_set_current_user( $ai_ql );
$_GET    = array( 'nguon' => 'kho-thu' );
$h_lo_k  = $DSK::hien();
$_GET    = array( 'q' => (string) $k1 );
$h_lo_k1 = $DSK::hien();
$dem_k   = $DSK::dem_chon( $DSK::loc_tu( array( 'nguon' => 'kho-thu' ) ) );
kt( 'Kho chọn nhiều: có khối "Đăng hoặc để dành nhiều câu"; ô chọn gắn form lô ở câu đưa vào được, câu đã có thẻ không có ô; đếm đúng câu còn đưa vào được', str_contains( $h_lo_k, 'value="' . $TL::TAO_KHO . '"' ) && str_contains( $h_lo_k, 'Đăng hoặc để dành nhiều câu' ) && str_contains( $h_lo_k, 'name="ids[]" value="' . $kt_ids[1049] . '" form="phongdao-kho-lo"' ) && 30 === preg_match_all( '#name="ids\[\]" value="\d+" form="phongdao-kho-lo"#', $h_lo_k ) && ! str_contains( $h_lo_k1, 'name="ids[]"' ) && 1043 === $dem_k, $dem_k );
add_filter( 'wp_die_handler', $chan_die_kho );
add_filter( 'wp_redirect', $chan_di_kho );
$gui_tk = static function ( int $nguoi, array $post, bool $nonce = true ) use ( $TL ): string {
	wp_set_current_user( $nguoi );
	$_POST = $_REQUEST = $post;
	if ( $nonce ) {
		$_POST['_wpnonce'] = $_REQUEST['_wpnonce'] = wp_create_nonce( $TL::TAO_KHO );
	}
	try {
		$TL::tao_kho();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$chon_5    = array( (string) $kt_ids[10], (string) $kt_ids[11], (string) $kt_ids[12], (string) $k1, (string) $kt_ids[1] );
$r_tk_dg   = $gui_tk( $doc_gia, array( 'pham_vi' => 'chon', 'ids' => $chon_5, 'loc_nguon' => 'kho-thu' ) );
$r_tk_kn   = $gui_tk( $soan_k, array( 'pham_vi' => 'chon', 'ids' => $chon_5, 'loc_nguon' => 'kho-thu' ), false );
$r_tk      = $gui_tk( $soan_k, array( 'pham_vi' => 'chon', 'ids' => $chon_5, 'loc_nguon' => 'kho-thu' ) );
$lo_k      = Viec::ds_lo( $soan_k, 1 )[0] ?? array();
$r_tk_loc  = $gui_tk( $soan_k, array( 'pham_vi' => 'loc', 'loc_nguon' => 'kho-thu', 'loc_q' => 'Câu kho thử 104' ) );
$lo_kl     = Viec::ds_lo( $soan_k, 1 )[0] ?? array();
$r_tk_rong = $gui_tk( $soan_k, array( 'pham_vi' => 'chon', 'ids' => array( (string) $k1 ), 'loc_nguon' => 'kho-thu' ) );
$bao_rong  = get_transient( 'phongdao_lo_bao_' . $soan_k );
remove_filter( 'wp_die_handler', $chan_die_kho );
remove_filter( 'wp_redirect', $chan_di_kho );
$_POST = $_REQUEST = array();
kt( 'Kho chọn nhiều qua admin-post: người đọc, thiếu nonce bị chặn; người biên soạn tạo lô xem trước chỉ gồm câu đưa vào được (3/5); "mọi câu khớp bộ lọc" chốt đủ 11 câu; không còn câu nào thì báo và về Kho', str_starts_with( $r_tk_dg, 'wp_die' ) && str_starts_with( $r_tk_kn, 'wp_die' ) && str_contains( $r_tk, 'page=phongdao-lo&lo=' ) && 'kho' === ( $lo_k['noi_dung']['doi_tuong'] ?? '' ) && array( $kt_ids[10], $kt_ids[11], $kt_ids[12] ) === array_map( static fn( array $m ): int => (int) $m[0], $lo_k['noi_dung']['muc'] ?? array() ) && Viec::LO_XEM === ( $lo_k['trang_thai'] ?? '' ) && str_contains( $r_tk_loc, 'page=phongdao-lo&lo=' ) && 'loc' === ( $lo_kl['noi_dung']['pham_vi'] ?? '' ) && 11 === count( $lo_kl['noi_dung']['muc'] ?? array() ) && str_contains( $r_tk_rong, 'page=phongdao-kho' ) && empty( $bao_rong['ok'] ) && str_contains( (string) ( $bao_rong['chu'] ?? '' ), 'Không còn câu nào' ), array( $r_tk, $r_tk_loc, $r_tk_rong, $bao_rong ) );
wp_set_current_user( $soan_k );
$_GET = array( 'lo' => (string) $lo_k['id'] );
$h_xk = $TL::hien();
kt( 'màn Lô (Kho): tiêu đề "Xem trước: đưa vào Hộp việc 3 câu từ Kho", lối "← Về Kho"; xem trước hiện lời, thể loại, nguồn, tình trạng từng câu ("Chưa đăng") và nút "Đưa 3 câu vào Hộp việc"; một trang thì không có "Lưu bỏ chọn"; chưa tạo thẻ', str_contains( $h_xk, 'Câu kho thử 11 dòng một' ) && str_contains( $h_xk, '<h1>Xem trước: đưa vào Hộp việc 3 câu từ Kho</h1>' ) && str_contains( $h_xk, '← Về Kho</a>' ) && str_contains( $h_xk, 'Đưa 3 câu vào Hộp việc' ) && str_contains( $h_xk, 'Chưa đăng' ) && str_contains( $h_xk, 'data-nhan="Tình trạng"' ) && ! str_contains( $h_xk, 'Lúc này' ) && ! str_contains( $h_xk, 'Lưu bỏ chọn' ) && ! str_contains( $h_xk, 'Thao tác hàng loạt' ) && str_contains( $h_xk, 'example.org/kho-thu/11/' ) && array() === Viec::mo( 'kho', $kt_ids[10], 'cau_moi' ) );
$wpdb->update( $bang_kho, array( 'trang_thai' => 'bo_qua' ), array( 'id' => $kt_ids[12] ) );
$ad_k  = $LOK::ap_dung( (int) $lo_k['id'], $soan_k, false );
$the_k = Viec::mo( 'kho', $kt_ids[10], 'cau_moi' );
$h_kk  = $TL::hien();
$ad_k2 = $LOK::ap_dung( (int) $lo_k['id'], $soan_k, false );
kt( 'lô Kho: 2 câu thành thẻ Câu mới như bấm từng câu (người chọn, ưu tiên chọn tay); câu đổi sau lúc xem trước báo xung đột; xong thì chỉ lối sang Hộp việc; chạy lại bị từ chối', $ad_k['ok'] && array( 'thanh_cong' => 2, 'bo_qua' => 0, 'xung_dot' => 1, 'loi' => 0 ) === $ad_k['dem'] && 1 === count( $the_k ) && (string) $soan_k === $the_k[0]['nguoi_tao'] && UngVien::UU_TIEN_CHON_TAY === $the_k[0]['uu_tien'] && 1 === count( Viec::mo( 'kho', $kt_ids[11], 'cau_moi' ) ) && array() === Viec::mo( 'kho', $kt_ids[12], 'cau_moi' ) && str_contains( $h_kk, 'Mở Hộp việc: mọi thẻ Câu mới' ) && ! str_contains( $h_kk, 'câu vừa đưa vào</button>' ) && str_contains( $h_kk, 'Câu kho thử 13' ) && ! $ad_k2['ok'], array( $ad_k, $ad_k2 ) );
// Người tạo lô có thêm quyền duyệt (như vai Biên tập): màn kết quả mới có nút đăng
wp_set_current_user( $soan_k );
wp_get_current_user()->add_cap( 'cadao_duyet' );
$_GET = array( 'lo' => (string) $lo_k['id'] );
$h_kq = $TL::hien();
wp_get_current_user()->remove_cap( 'cadao_duyet' );
preg_match_all( '/<input type="hidden" name="ids\[\]" value="(\d+)">/', $h_kq, $m_kq );
$ids_kq  = array_map( 'intval', $m_kq[1] );
$the_vua = array_map( 'intval', array_merge( array_column( $the_k, 'id' ), array_column( Viec::mo( 'kho', $kt_ids[11], 'cau_moi' ), 'id' ) ) );
sort( $ids_kq );
sort( $the_vua );
kt( 'lô Kho xong: người có quyền duyệt có nút "Xem trước và đăng 2 câu vừa đưa vào" gửi đúng 2 thẻ lô vừa tạo, không lẫn thẻ Câu mới cũ; lối sang Hộp việc ghi rõ "mọi thẻ"', str_contains( $h_kq, 'Xem trước và đăng 2 câu vừa đưa vào' ) && $the_vua === $ids_kq && 2 === count( $ids_kq ) && str_contains( $h_kq, 'value="cau_moi_dang"' ) && str_contains( $h_kq, 'Mở Hộp việc: mọi thẻ Câu mới' ), array( $ids_kq, $the_vua ) );
$ad_kl = $LOK::ap_dung( (int) $lo_kl['id'], $soan_k, false );
kt( 'lô Kho "mọi câu khớp bộ lọc": 11 câu thành 11 thẻ Câu mới, không câu nào lên trang', $ad_kl['ok'] && 11 === $ad_kl['dem']['thanh_cong'] && 'moi' === Kho::lay( $kt_ids[1048] )->trang_thai && 0 === (int) Kho::lay( $kt_ids[1048] )->post_id, $ad_kl );
// Đăng theo lô ở Hộp việc
$dang_kho = array_map( static fn( int $i ): int => $kt_ids[ $i ], array( 10, 11, 103, 1039, 1040, 1041, 1042, 1043, 1044, 1045, 1046, 1047, 1048 ) );
$the_dang = array_map( static fn( int $k ): int => (int) ( Viec::mo( 'kho', $k, 'cau_moi' )[0]['id'] ?? 0 ), $dang_kho );
$wpdb->update( $bang_kho, array( 'du_lieu' => wp_json_encode( array( 'co' => array( 'tho_tuc' ) ) ) ), array( 'id' => $kt_ids[1048] ) );
ChongTrung::ghi_cho( 'kho:' . $kt_ids[1047], 'kho:' . $kt_ids[1046], 'gan_giong', 80, 'Cặp thử chờ quyết.' );
$ct_k  = (int) ( Viec::mo( 'kho', $kt_ids[1047], 'co_the_trung' )[0]['id'] ?? 0 );
$t_co  = $the_dang[12];
$t_tr  = $the_dang[11];
$lo_dq = $LOK::tao( 'cau_moi_dang', $the_dang, 'chon', array( 'tt' => 'cho', 'loai' => 'cau_moi', 'q' => '' ), $soan_k );
$lo_dl = $LOK::tao( 'cau_moi_dang', array( $ct_k ), 'chon', array(), $ai_ql );
$lo_d  = $LOK::tao( 'cau_moi_dang', $the_dang, 'chon', array( 'tt' => 'cho', 'loai' => 'cau_moi', 'q' => '' ), $ai_ql );
$v_d   = Viec::lay( (int) $lo_d['lo'] );
kt( 'lô Đăng: cần quyền duyệt; chỉ cho thẻ Câu mới; câu có cờ của nguồn và câu đang chờ quyết trùng để sẵn bỏ chọn', 13 === count( array_filter( $the_dang ) ) && $ct_k > 0 && ! $lo_dq['ok'] && str_contains( $lo_dq['loi'], 'quyền duyệt' ) && ! $lo_dl['ok'] && str_contains( $lo_dl['loi'], 'Câu mới từ nguồn' ) && $lo_d['ok'] && 13 === count( $v_d['noi_dung']['muc'] ) && array( min( $t_co, $t_tr ), max( $t_co, $t_tr ) ) === array_map( 'intval', $v_d['noi_dung']['bo'] ), array( $lo_dq, $lo_dl, $v_d['noi_dung']['bo'] ?? null ) );
wp_set_current_user( $ai_ql );
$_GET = array( 'lo' => (string) $lo_d['lo'] );
$h_xd = $TL::hien();
kt( 'màn Lô (Đăng): xem trước hiện lời và cờ bằng chữ, câu chờ quyết trùng "sẽ bỏ qua", ô câu có cờ bỏ dấu sẵn, nút "Đăng 11 câu"; chưa đăng gì', str_contains( $h_xd, 'Câu kho thử 1049 dòng một' ) && str_contains( $h_xd, 'Cờ: có từ thô tục' ) && str_contains( $h_xd, 'Đang chờ quyết trùng (sẽ bỏ qua)' ) && str_contains( $h_xd, 'id="lo-giu-' . $t_co . '">' ) && str_contains( $h_xd, 'id="lo-giu-' . $the_dang[0] . '" checked' ) && str_contains( $h_xd, 'Đăng 11 câu' ) && 'moi' === Kho::lay( $kt_ids[10] )->trang_thai && 0 === (int) Kho::lay( $kt_ids[10] )->post_id );
$bc_d = $LOK::bo_chon( (int) $lo_d['lo'], $the_dang, array_values( array_diff( $the_dang, array( $t_co ) ) ), $ai_ql );
\Phongdao\DichVu\XepViec\QuyetViec::quyet( $the_dang[2], 'de_sau', $ai_ql, true );
$ad_d  = $LOK::ap_dung( (int) $lo_d['lo'], $ai_ql, true );
$kq_d  = (array) Viec::lay( (int) $lo_d['lo'] )['sau']['ket_qua'];
$bai_d = array_map( static fn( int $i ): int => (int) Kho::lay( $kt_ids[ $i ] )->post_id, array( 10, 11, 1039 ) );
$p_d   = PhanBai::tat_ca( $bai_d[0], KhaiBao::TAC_PHAM );
kt( 'lô Đăng: 10 câu thành tác phẩm Đã đăng như bấm Đăng từng thẻ (lời máy chép đúng kho, thẻ đóng "dang", ghi người quyết); câu bỏ dấu, câu chờ quyết trùng, thẻ đổi sau lúc xem trước không đăng', $bc_d['ok'] && 1 === $bc_d['bo'] && $ad_d['ok'] && array( 'thanh_cong' => 10, 'bo_qua' => 2, 'xung_dot' => 1, 'loi' => 0 ) === $ad_d['dem'] && 'publish' === get_post_status( $bai_d[0] ) && 'publish' === get_post_status( $bai_d[2] ) && $bai_d[0] !== $bai_d[1] && 'da_tao_bai' === Kho::lay( $kt_ids[10] )->trang_thai && array( 'Câu kho thử 11 dòng một', 'Câu kho thử 11 dòng hai' ) === ( $p_d['loi']['dong'] ?? null ) && NguoiLam::MAY_CHEP === ( $p_d['loi']['nguoi_lam'] ?? '' ) && Viec::XONG === Viec::lay( $the_dang[0] )['trang_thai'] && 'dang' === Viec::lay( $the_dang[0] )['quyet'] && $ai_ql === Viec::lay( $the_dang[0] )['nguoi_quyet'] && 'moi' === Kho::lay( $kt_ids[1048] )->trang_thai && Viec::CHO === Viec::lay( $t_co )['trang_thai'] && Viec::CHO === Viec::lay( $t_tr )['trang_thai'] && 'moi' === Kho::lay( $kt_ids[1047] )->trang_thai && 'xung_dot' === ( $kq_d[ $the_dang[2] ][0] ?? '' ) && 'moi' === Kho::lay( $kt_ids[103] )->trang_thai && str_contains( (string) ( $kq_d[ $the_dang[0] ][1] ?? '' ), 'Bài #' . $bai_d[0] ), array( $ad_d, $kq_d ) );
$h_kd = $TL::hien();
kt( 'màn Lô (Đăng) xong: kết quả từng câu có liên kết bài vừa đăng; chỉ lối ẩn / thùng rác ở Tác phẩm', str_contains( $h_kd, 'Thành công 10' ) && str_contains( $h_kd, esc_url( \Phongdao\GiaoTiep\QuanTri\SuaTacPham::url( $bai_d[0] ) ) ) && str_contains( $h_kd, 'Bài đã đăng hiện ở' ) && str_contains( $h_kd, 'Câu đang chờ quyết trùng' ) );
$_GET    = array( 'loai' => 'cau_moi' );
$h_hv_ql = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
wp_set_current_user( $soan_k );
$h_hv_s  = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
wp_set_current_user( $ai_ql );
kt( 'Hộp việc: thanh "Chọn tất cả trên trang này" nằm trong form lô, trước danh sách, đếm mọi thẻ khớp bộ lọc', (bool) preg_match( '#<form method="post" id="phongdao-hv-lo" class="phongdao-lo-form"[^>]*>.*<div class="phongdao-chon-het" hidden data-tong="\d+" data-don-vi="thẻ">.*<ul class="phongdao-hv-ds">#s', $h_hv_ql ) );
kt( 'Hộp việc: lệnh "Đăng (thẻ Câu mới từ nguồn)" trong Làm theo lô chỉ có với người có quyền duyệt', str_contains( $h_hv_ql, 'value="cau_moi_dang"' ) && ! str_contains( $h_hv_s, 'value="cau_moi_dang"' ) && str_contains( $h_hv_s, 'value="viec_de_sau"' ) );
kt( 'Hộp việc: thanh nổi (ẩn tới khi đánh dấu thẻ) có nút "Làm theo lô…" mở khối lô', str_contains( $h_hv_ql, '<div class="phongdao-thanh-noi" hidden data-don-vi="thẻ">' ) && str_contains( $h_hv_ql, 'data-mo-lo>Làm theo lô…</button>' ) && str_contains( $h_hv_ql, 'phongdao-thanh-noi-bo' ) );
// ---------- Kho "Xem trước và đăng" một mạch (D-101): không qua Hộp việc, vẫn một màn xem trước, vẫn do người bấm ----------
$kd = array_map( static fn( int $i ): int => $kt_ids[ $i ], array( 20, 21, 22, 23, 24, 25 ) );
// Chữ thử khác hẳn nhau và khác các câu "Câu kho thử …" đã đăng ở trên, để máy so trùng lúc đăng không thấy giống
foreach ( array( "Alpha bravo charlie delta
Echo foxtrot golf hotel", "India juliet kilo lima
Mike november oscar papa", "Quebec romeo sierra tango
Uniform victor whiskey xray", "Mười chín hai mươi
Ba mươi bốn mươi", "Đỏ cam vàng lục
Lam chàm tím hồng", "Một hai ba bốn năm
Sáu bảy tám chín mười" ) as $i => $loi_kd ) {
	$wpdb->update( $bang_kho, array( 'loi' => $loi_kd, 'tieu_de' => strtok( $loi_kd, "
" ) ), array( 'id' => $kd[ $i ] ) );
	ChongTrung::van_tay_kho( $kd[ $i ] );
}
$wpdb->update( $bang_kho, array( 'du_lieu' => wp_json_encode( array( 'co' => array( 'hien_dai' ) ) ) ), array( 'id' => $kd[2] ) );
ChongTrung::ghi_cho( 'kho:' . $kd[3], 'kho:' . $kt_ids[30], 'gan_giong', 80, 'Cặp thử chờ quyết (Kho đăng).' );
$_GET   = array( 'nguon' => 'kho-thu' );
wp_set_current_user( $soan_k );
$h_kd_s = $DSK::hien();
wp_set_current_user( $ai_ql );
$h_kd_q = $DSK::hien();
kt( 'Kho: người có quyền duyệt có "Xem trước và đăng" ở khối lô, thanh nổi và từng dòng (nút nhỏ, hết nút chính trên từng dòng); người chỉ biên soạn chỉ có "Đề nghị đăng" (không còn "Đưa vào Hộp việc")', str_contains( $h_kd_q, 'name="lam" value="dang" class="button button-primary">Xem trước và đăng</button>' ) && str_contains( $h_kd_q, '<button type="submit" form="phongdao-kho-lo" name="lam" value="dang" class="button button-primary">Xem trước và đăng</button>' ) && substr_count( $h_kd_q, 'name="mot" value=' ) === preg_match_all( '#name="ids\[\]" value="\d+" form="phongdao-kho-lo"#', $h_kd_q ) && str_contains( $h_kd_q, '" class="button button-small" aria-label="Xem trước và đăng: câu #' ) && str_contains( $h_kd_q, '<div class="phongdao-thanh-noi" hidden data-don-vi="câu">' ) && ! str_contains( $h_kd_q, 'class="button button-primary" aria-label="Đưa vào Hộp việc' ) && ! str_contains( $h_kd_s, 'value="dang"' ) && ! str_contains( $h_kd_s, 'name="mot"' ) && str_contains( $h_kd_s, '<summary>Đề nghị đăng nhiều câu' ) && str_contains( $h_kd_s, 'name="lam" value="de_danh" class="button button-primary">Đề nghị đăng</button>' ) && ! str_contains( $h_kd_s, 'Đưa vào Hộp việc' ) && ! str_contains( $h_kd_q, '>Đưa vào Hộp việc' ) );
// ---------- Bài mới (T-189.3): Để dành / Đề nghị đăng, Máy gợi ý hôm nay, bộ lọc, nhập câu từ tệp ----------
$DD    = \Phongdao\DichVu\UngVien\DanhDau::class;
$dd_a  = $kd[0];
$dd_b  = $kd[1];
$cho_0 = Viec::dem_cho();
$dd_s  = $DD::danh( $dd_a, $soan_k );
$dd_q  = $DD::danh( $dd_b, $ai_ql );
$dd_q2 = $DD::danh( $dd_a, $ai_ql );
$_GET  = array( 'nguon' => 'kho-thu', 'danh' => 'de_nghi' );
$h_dn  = $DSK::hien();
$_GET  = array( 'nguon' => 'kho-thu', 'danh' => 'de_danh' );
$h_dd  = $DSK::hien();
$_GET  = array( 'tt' => 'tat_ca' );
$h_hv_dd = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
kt( 'Bài mới: người chỉ soạn đánh "Đề nghị đăng", người có quyền duyệt đánh "Để dành" (đè lên đề nghị); câu nằm lại Kho, lọc lại được, ghi người đánh; không phải việc chờ, không vào Hộp việc, không tạo thẻ Câu mới', $dd_s['moi'] && $dd_q['moi'] && $dd_q2['moi'] && 'de_danh' === ( $DD::cua( $dd_a )['loai'] ?? '' ) && $ai_ql === ( $DD::cua( $dd_a )['nguoi'] ?? 0 ) && 'de_danh' === ( $DD::cua( $dd_b )['loai'] ?? '' ) && $cho_0 === Viec::dem_cho() && array() === Viec::mo( 'kho', $dd_a, 'cau_moi' ) && str_contains( $h_dd, 'data-nhan="ID">' . $dd_a ) && str_contains( $h_dd, 'data-nhan="ID">' . $dd_b ) && ! str_contains( $h_dn, 'data-nhan="ID">' . $dd_a ) && ! str_contains( $h_hv_dd, 'danh_dau' ) && str_contains( $h_dd, '<strong>Để dành</strong>' ) && str_contains( $h_dd, '>Bỏ dấu</button>' ) && 'moi' === Kho::lay( $dd_a )->trang_thai, array( $dd_s, $dd_q, $dd_q2 ) );
$dd_bo = $DD::bo( $dd_b, $ai_ql );
kt( 'Bài mới: bỏ dấu ghi người bỏ; câu còn nguyên trong Kho', $dd_bo && null === $DD::cua( $dd_b ) && 'moi' === Kho::lay( $dd_b )->trang_thai );
$DD::bo( $dd_a, $ai_ql );
// Máy gợi ý hôm nay: bộ lọc đọc đúng danh sách máy ghi
$goi_cu = get_option( UngVien::GOI_Y );
update_option( UngVien::GOI_Y, array( 'ngay' => wp_date( 'Y-m-d' ), 'ids' => array( $kd[2], $kd[5] ) ), false );
$_GET   = array( 'goi_y' => 'hom_nay', 'tt' => 'tat_ca' );
$h_goi  = $DSK::hien();
update_option( UngVien::GOI_Y, $goi_cu, false );
kt( 'Bài mới: bộ lọc "Máy gợi ý hôm nay" chỉ hiện đúng các câu máy gợi ý, có lọc nhanh kèm số', str_contains( $h_goi, '<strong>2</strong> câu khớp' ) && str_contains( $h_goi, 'data-nhan="ID">' . $kd[2] ) && str_contains( $h_goi, 'Máy gợi ý hôm nay <span class="phongdao-chip-so">2</span>' ), substr( $h_goi, 0, 400 ) );
$_GET   = array( 'nguon' => 'kho-thu', 'tt' => 'tat_ca', 'q' => (string) $kd[2], 'dac' => 'khong_co' );
$h_kco  = $DSK::hien();
$_GET   = array( 'nguon' => 'kho-thu', 'tt' => 'tat_ca', 'q' => (string) $kd[3], 'dac' => 'khong_co' );
$h_kco2 = $DSK::hien();
$_GET   = array( 'nguon' => 'kho-thu', 'tt' => 'tat_ca', 'q' => (string) $kd[3], 'dac' => 'ngan' );
$h_ngan = $DSK::hien();
$_GET   = array( 'nguon' => 'kho-thu', 'tt' => 'tat_ca', 'q' => (string) $kd[3], 'dac' => 'dai' );
$h_dai  = $DSK::hien();
$_GET   = array();
kt( 'Bài mới: lọc "không có cờ" bỏ câu có cờ của nguồn; lọc câu ngắn / dài theo số dòng', str_contains( $h_kco, '<strong>0</strong> câu khớp' ) && str_contains( $h_kco2, '<strong>1</strong> câu khớp' ) && str_contains( $h_ngan, '<strong>1</strong> câu khớp' ) && str_contains( $h_dai, '<strong>0</strong> câu khớp' ) );
// Nhập câu từ tệp: tách theo dòng trống, vào Kho qua chống trùng, không đăng; nhập lại thì bỏ qua câu đã có
$NT     = \Phongdao\DichVu\UngVien\NhapTep::class;
$hs_nt  = (int) wp_insert_post( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'post_title' => 'Sách thử nhập tệp' ) );
$chu_nt = "\u{FEFF}Zeta eta theta iota\r\nKappa lambda mu nu\r\n\r\n\r\nXi omicron pi rho\nSigma tau upsilon phi\n";
$tach   = $NT::tach( $chu_nt );
$bai_0  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", KhaiBao::TAC_PHAM ) );
$nt_1   = $NT::nhap( $chu_nt, $hs_nt, 'ca_dao', $soan_k );
$nt_2   = $NT::nhap( $chu_nt, $hs_nt, 'ca_dao', $soan_k );
$nt_sai = $NT::nhap( "   \n\n ", $hs_nt, 'ca_dao', $soan_k );
$m_nt   = Kho::lay( $nt_1['ids'][0] ?? 0 );
$p_nt   = $m_nt ? UngVien::phan_tu_kho( $m_nt ) : array();
kt( 'Nhập câu từ tệp: tách câu theo dòng trống (giữ từng dòng, bỏ BOM), vào Kho nguồn "Nhập từ tệp" kèm hồ sơ người chọn; không tạo bài nào; nhập lại thì bỏ qua câu đã có; chữ rỗng thì báo', array( array( 'Zeta eta theta iota', 'Kappa lambda mu nu' ), array( 'Xi omicron pi rho', 'Sigma tau upsilon phi' ) ) === $tach && $nt_1['ok'] && 2 === $nt_1['them'] && 0 === $nt_2['them'] && 2 === $nt_2['da_co'] && ! $nt_sai['ok'] && $m_nt && $NT::NGUON === $m_nt->nguon_id && 'moi' === $m_nt->trang_thai && "Zeta eta theta iota\nKappa lambda mu nu" === $m_nt->loi && $hs_nt === (int) ( Kho::du_lieu( $m_nt )['ho_so'] ?? 0 ) && $bai_0 === (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", KhaiBao::TAC_PHAM ) ) && 'Nhập từ tệp' === NguonTuDong::ten( $NT::NGUON ), array( $tach, $nt_1, $nt_2 ) );
kt( 'Nhập câu từ tệp: khi đăng, lời là chữ người gõ (người viết, không phải máy chép), nguồn đúng hồ sơ người chọn — máy không tự điền', NguoiLam::NGUOI_VIET === ( $p_nt['loi']['nguoi_lam'] ?? '' ) && $hs_nt === (int) ( $p_nt['loi']['nguon']['ho_so'] ?? 0 ) && ! isset( $p_nt['chu_thich'] ), $p_nt );
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Kho::bang() . ' WHERE nguon_id = %s', $NT::NGUON ) );
wp_delete_post( $hs_nt, true );
// Thẻ Câu mới đang mở chuyển về Kho với dấu Để dành (bước nâng cấp 13), không mất câu
$cm_kq  = UngVien::dua_vao_hop_viec( $kd[1], $soan_k );
// Các thẻ Câu mới khác của bộ kiểm còn dùng ở sau: cất tạm (giữ trạng thái), chạy bước chuyển, rồi trả lại
foreach ( array( 'cho', 'de_sau' ) as $tt_cat ) {
	$wpdb->query( $wpdb->prepare( 'UPDATE ' . Viec::bang() . " SET trang_thai = %s WHERE loai = 'cau_moi' AND trang_thai = %s AND id <> %d", 'tam_' . $tt_cat, $tt_cat, (int) $cm_kq['viec'] ) );
}
$cm_so  = \Phongdao\HaTang\CaiDat::buoc_13();
$cm_v   = Viec::lay( (int) $cm_kq['viec'] );
$cm_lai = \Phongdao\HaTang\CaiDat::buoc_13();
foreach ( array( 'cho', 'de_sau' ) as $tt_cat ) {
	$wpdb->query( $wpdb->prepare( 'UPDATE ' . Viec::bang() . " SET trang_thai = %s WHERE loai = 'cau_moi' AND trang_thai = %s", $tt_cat, 'tam_' . $tt_cat ) );
}
kt( 'chuyển đổi: thẻ Câu mới đang mở đóng ("Chuyển về Bài mới"), câu đánh dấu Để dành (ghi người đã chọn), câu còn mới trong Kho; chạy lại không làm gì thêm', $cm_kq['moi'] && $cm_so >= 1 && Viec::XONG === $cm_v['trang_thai'] && 'chuyen_kho' === $cm_v['quyet'] && 'de_danh' === ( $DD::cua( $kd[1] )['loai'] ?? '' ) && $soan_k === ( $DD::cua( $kd[1] )['nguoi'] ?? 0 ) && 'moi' === Kho::lay( $kd[1] )->trang_thai && 0 === $cm_lai, array( $cm_kq, $cm_so, $cm_v ) );
$DD::bo( $kd[1], $ai_ql );
// Thêm tác phẩm trong wp-admin: cùng biểu mẫu (kiểm trùng khi dán lời), lưu xong mở trang Sửa tác phẩm
wp_set_current_user( $soan_k );
$h_them = \Phongdao\GiaoTiep\QuanTri\ThemTacPham::hien();
wp_set_current_user( $ai_ql );
kt( 'Thêm tác phẩm trong wp-admin: biểu mẫu gửi cùng đường lưu của Thêm bài, có kiểm trùng khi dán lời (data-them-bai), người chỉ soạn không có "Đăng ngay"; lối từ Tác phẩm, Bài mới, thanh trên cùng', str_contains( $h_them, 'data-them-bai' ) && str_contains( $h_them, 'name="tu" value="quan_tri"' ) && str_contains( $h_them, 'value="' . \Phongdao\GiaoTiep\BienTap\ThemBai::HANH_DONG . '"' ) && str_contains( $h_them, 'id="them-phan-tich"' ) && ! str_contains( $h_them, 'Đăng ngay' ) && str_contains( $h_kd_q, esc_url( \Phongdao\GiaoTiep\QuanTri\ThemTacPham::url() ) ) );
add_filter( 'wp_die_handler', $chan_die_kho );
add_filter( 'wp_redirect', $chan_di_kho );
$r_kd_s   = $gui_tk( $soan_k, array( 'pham_vi' => 'chon', 'lam' => 'dang', 'ids' => array_map( 'strval', $kd ), 'loc_nguon' => 'kho-thu' ) );
$bao_kd_s = get_transient( 'phongdao_lo_bao_' . $soan_k );
$r_kd     = $gui_tk( $ai_ql, array( 'pham_vi' => 'chon', 'lam' => 'dang', 'ids' => array_map( 'strval', $kd ), 'loc_nguon' => 'kho-thu' ) );
$lo_kd    = Viec::ds_lo( $ai_ql, 1 )[0] ?? array();
$r_kd1    = $gui_tk( $ai_ql, array( 'pham_vi' => 'loc', 'mot' => (string) $kd[0], 'ids' => array_map( 'strval', $kd ), 'loc_nguon' => 'kho-thu' ) );
$lo_kd1   = Viec::ds_lo( $ai_ql, 1 )[0] ?? array();
remove_filter( 'wp_die_handler', $chan_die_kho );
remove_filter( 'wp_redirect', $chan_di_kho );
$_POST = $_REQUEST = array();
$muc_ma = static fn( array $lo ): array => array_map( static fn( array $m ): int => (int) $m[0], (array) ( $lo['noi_dung']['muc'] ?? array() ) );
kt( 'Kho "Xem trước và đăng" qua admin-post: cần quyền duyệt; tạo lô "Đăng từ Kho" ở bước xem trước, câu có cờ và câu chờ quyết trùng để sẵn bỏ chọn; nút trên từng dòng tạo lô đúng một câu (bỏ qua ô đã chọn và phạm vi); mỗi lượt 25 câu', str_contains( $r_kd_s, 'page=phongdao-kho' ) && str_contains( (string) ( $bao_kd_s['chu'] ?? '' ), 'quyền duyệt' ) && str_contains( $r_kd, 'page=phongdao-lo&lo=' ) && 'kho_dang' === ( $lo_kd['noi_dung']['thao_tac'] ?? '' ) && $kd === $muc_ma( $lo_kd ) && array( $kd[2], $kd[3] ) === array_map( 'intval', $lo_kd['noi_dung']['bo'] ?? array() ) && Viec::LO_XEM === ( $lo_kd['trang_thai'] ?? '' ) && str_contains( $r_kd1, 'page=phongdao-lo&lo=' ) && 'kho_dang' === ( $lo_kd1['noi_dung']['thao_tac'] ?? '' ) && array( $kd[0] ) === $muc_ma( $lo_kd1 ) && 'chon' === ( $lo_kd1['noi_dung']['pham_vi'] ?? '' ) && array() === Viec::mo( 'kho', $kd[0], 'cau_moi' ) && 25 === $LOK::moi_luot( 'kho_dang' ) && $LOK::MOI_LUOT === $LOK::moi_luot( 'cau_moi_dang' ), array( $r_kd_s, $bao_kd_s, $lo_kd['noi_dung'] ?? null, $lo_kd1['noi_dung'] ?? null ) );
$LOK::huy( (int) $lo_kd1['id'], $ai_ql );
$_GET  = array( 'lo' => (string) $lo_kd['id'] );
$h_xkd = $TL::hien();
kt( 'màn Lô (Đăng từ Kho): tiêu đề "Xem trước: đăng N câu từ Kho"; xem trước hiện lời và cờ bằng chữ, câu chờ quyết trùng "sẽ bỏ qua", câu có cờ bỏ dấu sẵn, nhắc cân nhắc cờ "Có thể không phải dân gian" cho câu nghi hiện đại (máy không tự bật), nút "Đăng 4 câu"; chưa tạo thẻ, chưa đăng', str_contains( $h_xkd, '<h1>Xem trước: đăng ' . count( $kd ) . ' câu từ Kho</h1>' ) && str_contains( $h_xkd, 'Alpha bravo charlie delta' ) && str_contains( $h_xkd, 'Cờ: nghi là câu hiện đại' ) && str_contains( $h_xkd, 'Đang chờ quyết trùng (sẽ bỏ qua)' ) && str_contains( $h_xkd, 'id="lo-giu-' . $kd[2] . '">' ) && str_contains( $h_xkd, 'id="lo-giu-' . $kd[0] . '" checked' ) && str_contains( $h_xkd, 'Đăng 4 câu' ) && str_contains( $h_xkd, 'mỗi câu đánh dấu thành một bài Đã đăng' ) && str_contains( $h_xkd, 'cân nhắc bật cờ “Có thể không phải dân gian”' ) && ! str_contains( $h_xkd, 'tho_tuc' ) && ! str_contains( $h_xkd, 'hien_dai' ) && str_contains( $h_xkd, 'Đăng: câu #' . $kd[0] ) && str_contains( $h_xkd, 'mỗi lượt 25 mục' ) && array() === Viec::mo( 'kho', $kd[0], 'cau_moi' ) && 'moi' === Kho::lay( $kd[0] )->trang_thai );
// Thẻ Câu mới ghi cờ của nguồn bằng chữ; Kho: "Quyết trùng trước" mở thẳng thẻ Có thể trùng (báo cáo trinh sát lần 2, 1.1, 1.5)
wp_set_current_user( $ai_ql );
$the_co = \Phongdao\GiaoTiep\BienTap\HopViec::the( array( 'id' => 0, 'loai' => 'cau_moi', 'doi_tuong_loai' => 'kho', 'doi_tuong_id' => $kd[2], 'trang_thai' => Viec::CHO, 'phien' => 0, 'noi_dung' => array(), 'nguoi_tao' => 'may' ), false );
// Máy chống trùng đã mở thẻ "Có thể trùng" cho cặp chờ quyết; chưa có thì tạo một thẻ thử. Nút mở thẻ mở sớm nhất.
$tao_tr = ! Viec::mo( 'kho', $kd[3], 'co_the_trung' );
if ( $tao_tr ) {
	Viec::tao( array( 'loai' => 'co_the_trung', 'doi_tuong_loai' => 'kho', 'doi_tuong_id' => $kd[3], 'noi_dung' => array( 'a' => 'kho:' . $kd[3], 'b' => 'kho:' . $kt_ids[30], 'giong' => 80 ), 'nguoi_tao' => 'may' ) );
}
$v_tr   = min( array_map( static fn( array $x ): int => (int) $x['id'], Viec::mo( 'kho', $kd[3], 'co_the_trung' ) ) );
$_GET   = array( 'nguon' => 'kho-thu', 'q' => (string) $kd[3] );
$h_tr   = $DSK::hien();
$_GET   = array( 'lo' => (string) $lo_kd['id'] );
if ( $tao_tr ) {
	Viec::xoa( $v_tr );
}
kt( 'thẻ Câu mới ghi "Cờ của nguồn: nghi là câu hiện đại" (không in mã máy); Kho: câu chờ quyết trùng có nút "Quyết trùng trước" mở đúng thẻ Có thể trùng', str_contains( $the_co, 'Cờ của nguồn: nghi là câu hiện đại' ) && ! str_contains( $the_co, 'hien_dai' ) && str_contains( $h_tr, esc_url( \Phongdao\GiaoTiep\QuanTri\DanhSachViec::url( array( 'loai' => 'co_the_trung' ), 1, $v_tr ) ) . '">Quyết trùng trước</a>' ), $the_co );
// Sau khi Đăng một câu mới ở Hộp việc: "Đã đăng “Tên bài”." kèm Xem bài · Ẩn nếu nhầm; Ẩn một bấm, có Lịch sử, về lại Hộp việc
$k_dg = $kt_ids[900];
$wpdb->update( $bang_kho, array( 'loi' => "Kilo papa zulu oscar\nNovember yankee echo golf", 'tieu_de' => 'Kilo papa zulu oscar', 'du_lieu' => wp_json_encode( array() ) ), array( 'id' => $k_dg ) );
ChongTrung::van_tay_kho( $k_dg );
$t_dg   = UngVien::dua_vao_hop_viec( $k_dg, $ai_ql );
$v_dg   = Viec::lay( (int) $t_dg['viec'] );
$bat_dg = static function ( $vi_tri ): string {
	throw new \RuntimeException( 'redirect: ' . $vi_tri );
};
add_filter( 'wp_redirect', $bat_dg );
$gui_dg = static function ( callable $ham, string $nonce, array $than ): string {
	$_POST    = $than + array( '_wpnonce' => wp_create_nonce( $nonce ) );
	$_REQUEST = $_POST;
	try {
		$ham();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$tiep_dg = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::url( array( 'loai' => 'cau_moi' ) );
$r_dg    = $gui_dg( array( \Phongdao\GiaoTiep\BienTap\HopViec::class, 'nhan_form' ), \Phongdao\GiaoTiep\BienTap\HopViec::HANH_DONG . $v_dg['id'], array( 'viec' => (string) $v_dg['id'], 'quyet' => 'dang', 'phien' => (string) $v_dg['phien'], 'tiep' => $tiep_dg, '_wp_http_referer' => $tiep_dg ) );
$bai_dg  = (int) Kho::lay( $k_dg )->post_id;
$q_dg    = array();
wp_parse_str( (string) wp_parse_url( substr( $r_dg, strlen( 'redirect: ' ) ), PHP_URL_QUERY ), $q_dg );
$_GET    = $q_dg;
$h_dg    = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
$r_an    = $gui_dg( array( \Phongdao\GiaoTiep\QuanTri\SuaTacPham::class, 'doi_trang_thai' ), 'phongdao_doi_trang_thai_' . $bai_dg, array( 'id' => (string) $bai_dg, 'trang_thai' => 'private', 'trang_thai_cu' => 'publish', 'quay_lai' => $tiep_dg ) );
$_POST   = $_REQUEST = array();
$_GET    = array( 'lo' => (string) $lo_kd['id'] );
remove_filter( 'wp_redirect', $bat_dg );
kt( 'Hộp việc: Đăng câu mới → lời báo "Đã đăng “Tên bài”." kèm Xem bài và Ẩn nếu nhầm (không còn câu "không có Hoàn tác nhanh"); Ẩn một bấm thì bài thành Đã ẩn, có Lịch sử, về lại Hộp việc kèm lời báo', $bai_dg && str_contains( $r_dg, 'dang_bai=' . $bai_dg ) && 'Đã đăng “Kilo papa zulu oscar”.' === ( $q_dg['da'] ?? '' ) && str_contains( $h_dg, 'class="phongdao-hv-lui"' ) && str_contains( $h_dg, '>Ẩn nếu nhầm</button>' ) && str_contains( $h_dg, esc_url( (string) get_permalink( $bai_dg ) ) . '">Xem bài</a>' ) && ! str_contains( $h_dg, 'không có Hoàn tác nhanh' ) && 'private' === get_post_status( $bai_dg ) && str_contains( $r_an, 'page=phongdao-hop-viec' ) && str_contains( rawurldecode( $r_an ), 'Đã ẩn “Kilo papa zulu oscar”' ) && array_filter( Viec::lich_su( 'post', $bai_dg ), static fn( array $h ): bool => 'trang_thai' === ( $h['phan'] ?? '' ) ), array( $r_dg, $r_an, $q_dg ) );
wp_delete_post( $bai_dg, true );
$the_kd5 = UngVien::dua_vao_hop_viec( $kd[5], $soan_k );
$wpdb->update( $bang_kho, array( 'trang_thai' => 'bo_qua' ), array( 'id' => $kd[4] ) );
$gh_kd  = $LOK::giu_het( (int) $lo_kd['id'], $ai_ql );
$ad_kd  = $LOK::ap_dung( (int) $lo_kd['id'], $ai_ql, true );
$kq_kd  = (array) Viec::lay( (int) $lo_kd['id'] )['sau']['ket_qua'];
$bai_kd = array_map( static fn( int $k ): int => (int) Kho::lay( $k )->post_id, $kd );
$p_kd   = PhanBai::tat_ca( $bai_kd[0], KhaiBao::TAC_PHAM );
$v_kd0  = Viec::lay( (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'kho' AND doi_tuong_id = %d AND loai = 'cau_moi'", $kd[0] ) ) );
$v_kd5  = Viec::lay( (int) $the_kd5['viec'] );
kt( 'lô Đăng từ Kho: mỗi câu thành tác phẩm Đã đăng qua thẻ Câu mới như bấm Đăng (lời máy chép đúng kho, thẻ đóng "dang", ghi người quyết); câu có cờ người đánh dấu lại thì đăng; câu đã có thẻ đăng qua đúng thẻ đó; câu chờ quyết trùng bỏ qua dù đánh dấu; câu đổi sau lúc xem trước báo xung đột', $the_kd5['moi'] && $gh_kd['ok'] && $ad_kd['ok'] && array( 'thanh_cong' => 4, 'bo_qua' => 1, 'xung_dot' => 1, 'loi' => 0 ) === $ad_kd['dem'] && 'publish' === get_post_status( $bai_kd[0] ) && 'publish' === get_post_status( $bai_kd[1] ) && 'publish' === get_post_status( $bai_kd[2] ) && 'publish' === get_post_status( $bai_kd[5] ) && 'da_tao_bai' === Kho::lay( $kd[0] )->trang_thai && array( 'Alpha bravo charlie delta', 'Echo foxtrot golf hotel' ) === ( $p_kd['loi']['dong'] ?? null ) && NguoiLam::MAY_CHEP === ( $p_kd['loi']['nguoi_lam'] ?? '' ) && $v_kd0 && Viec::XONG === $v_kd0['trang_thai'] && 'dang' === $v_kd0['quyet'] && $ai_ql === $v_kd0['nguoi_quyet'] && Viec::XONG === $v_kd5['trang_thai'] && 'dang' === $v_kd5['quyet'] && 1 === count( $wpdb->get_col( $wpdb->prepare( 'SELECT id FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'kho' AND doi_tuong_id = %d AND loai = 'cau_moi'", $kd[5] ) ) ) && 'moi' === Kho::lay( $kd[3] )->trang_thai && 0 === $bai_kd[3] && array() === Viec::mo( 'kho', $kd[3], 'cau_moi' ) && str_contains( (string) ( $kq_kd[ $kd[3] ][1] ?? '' ), 'chờ quyết trùng' ) && 'xung_dot' === ( $kq_kd[ $kd[4] ][0] ?? '' ) && 0 === $bai_kd[4] && str_contains( (string) ( $kq_kd[ $kd[0] ][1] ?? '' ), 'Bài #' . $bai_kd[0] ), array( $ad_kd, $kq_kd, $bai_kd ) );
$h_kdx = $TL::hien();
kt( 'màn Lô (Đăng từ Kho) xong: kết quả có liên kết bài vừa đăng, nút "Về Kho" đúng bộ lọc lúc chọn, lối quyết thẻ "Có thể trùng" ở Hộp việc; mỗi bài vừa đăng có Xem bài · Ẩn nếu nhầm', str_contains( $h_kdx, 'Bài vừa đăng' ) && str_contains( $h_kdx, 'class="phongdao-hv-lui"' ) && str_contains( $h_kdx, 'Thành công 4' ) && str_contains( $h_kdx, esc_url( \Phongdao\GiaoTiep\QuanTri\SuaTacPham::url( $bai_kd[0] ) ) ) && str_contains( $h_kdx, '>Về Kho</a>' ) && str_contains( $h_kdx, esc_url( $DSK::url( array( 'nguon' => 'kho-thu' ) ) ) ) && str_contains( $h_kdx, 'loai=co_the_trung' ) );
$wpdb->delete( ChongTrung::bang( 'trung' ), array( 'a' => 'kho:' . $kd[3] ) );
foreach ( $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $bang_kho WHERE nguon_id = %s AND trang_thai = 'da_tao_bai' AND post_id > 0", 'kho-thu' ) ) ?: array() as $p_xoa ) {
	if ( (int) $p_xoa !== (int) $tp1 ) {
		wp_delete_post( (int) $p_xoa, true );
	}
}
$wpdb->delete( ChongTrung::bang( 'trung' ), array( 'a' => 'kho:' . $kt_ids[1047] ) );
kt( 'Đăng hàng loạt: dọn bài thử, giữ bài dùng chung', 'publish' === get_post_status( $tp1 ) && null === get_post( $bai_d[0] ) );
$_GET = array();
$wpdb->query( 'DELETE FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'kho' AND doi_tuong_id IN (" . implode( ',', $kt_ids ) . ')' );
$wpdb->delete( $bang_kho, array( 'nguon_id' => 'kho-thu' ) );
kt( 'wp-admin Kho: dọn câu thử', 0 === (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $bang_kho WHERE nguon_id = %s", 'kho-thu' ) ) );

// ---------- Lịch sử và khôi phục, dấu vết (T-170) ----------
$LS     = \Phongdao\DichVu\LuuPhan\LichSu::class;
$ncq    = array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
$yn_goc = PhanBai::lay( $tp1, 'y_nghia' );
$s_1    = LuuPhan::luu( $tp1, 'y_nghia', array( 'chu' => 'Ý nghĩa thử lịch sử một.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq );
$s_2    = LuuPhan::luu( $tp1, 'y_nghia', array( 'chu' => 'Ý nghĩa thử lịch sử hai.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq );
$k_cu   = $LS::khoi_phuc( $s_1['viec'], $ai_ql, true );
kt( 'khôi phục: lần sửa cũ (đã có lần sửa mới hơn) bị từ chối, phần giữ nguyên', ! $k_cu['ok'] && 'Ý nghĩa thử lịch sử hai.' === PhanBai::lay( $tp1, 'y_nghia' )['chu'], $k_cu );
$r_cu = rest_do_request( new WP_REST_Request( 'POST', '/phongdao/v1/viec/' . $s_1['viec'] . '/hoan-tac' ) );
kt( 'Hoàn tác (REST) một lần sửa cũ cũng bị từ chối, không ghi đè lần sửa mới hơn', ! ( $r_cu->get_data()['ok'] ?? false ) && 'Ý nghĩa thử lịch sử hai.' === PhanBai::lay( $tp1, 'y_nghia' )['chu'], $r_cu->get_data() );
$k_moi = $LS::khoi_phuc( $s_2['viec'], $ai_ql, true );
$nk_km = Viec::lay( $k_moi['viec'] );
kt( 'khôi phục lần sửa mới nhất: về bản trước, ghi một lần sửa mới "khôi phục" trỏ về lần đó', $k_moi['ok'] && $k_moi['doi'] && 'Ý nghĩa thử lịch sử một.' === PhanBai::lay( $tp1, 'y_nghia' )['chu'] && 'khoi_phuc' === $nk_km['quyet'] && $s_2['viec'] === (int) ( $nk_km['noi_dung']['tu_viec'] ?? 0 ), array( $k_moi, $nk_km ) );
$k_lai = $LS::khoi_phuc( $s_2['viec'], $ai_ql, true );
kt( 'khôi phục: bấm lại cùng dòng (đã có lần khôi phục mới hơn) bị từ chối', ! $k_lai['ok'] && 'Ý nghĩa thử lịch sử một.' === PhanBai::lay( $tp1, 'y_nghia' )['chu'], $k_lai );
$t_ls = (int) wp_insert_term( 'Chủ đề lịch sử thử', 'chu_de' )['term_id'];
$cd_0 = $LPL::dang_gan( $tp1, 'chu_de' );
$pl_1 = $LPL::luu( $tp1, 'chu_de', array_merge( $cd_0, array( $t_ls ) ), $cd_0, $ai_ql );
$kp_1 = $LS::khoi_phuc( $pl_1['viec'], $ai_ql, true );
$kp_2 = $LS::khoi_phuc( $pl_1['viec'], $ai_ql, true );
kt( 'khôi phục phân loại: gỡ mục vừa gắn, ghi "khôi phục"; bấm lại bị từ chối', $kp_1['ok'] && $cd_0 === $LPL::dang_gan( $tp1, 'chu_de' ) && 'khoi_phuc' === Viec::lay( $kp_1['viec'] )['quyet'] && ! $kp_2['ok'], array( $kp_1, $kp_2 ) );
$_GET = array( 'id' => (string) $tp1 );
$h_ls = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
kt( 'Lịch sử wp-admin: Xem trước/sau đánh dấu dòng bỏ / thêm bằng chữ; nút Khôi phục chỉ ở lần sửa mới nhất của mỗi phần', str_contains( $h_ls, 'Xem trước/sau' ) && str_contains( $h_ls, '<span class="screen-reader-text">bỏ: </span>' ) && str_contains( $h_ls, '<span class="screen-reader-text">thêm: </span>' ) && str_contains( $h_ls, 'name="viec" value="' . $kp_1['viec'] . '"' ) && str_contains( $h_ls, 'name="viec" value="' . $k_moi['viec'] . '"' ) && ! str_contains( $h_ls, 'name="viec" value="' . $s_1['viec'] . '"' ) && ! str_contains( $h_ls, 'name="viec" value="' . $pl_1['viec'] . '"' ) );
PhanBai::ghi( $tp1, 'y_nghia', array( 'chu' => 'Sửa ngoài lối thử.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ) );
clean_post_cache( $tp1 );
kt( 'khôi phục: phần đã đổi ngoài lối sửa (không có nhật ký) thì từ chối', str_contains( $LS::ly_do_khong( Viec::lay( $k_moi['viec'] ) ), 'thay đổi mới hơn' ) && ! $LS::khoi_phuc( $k_moi['viec'], $ai_ql, true )['ok'] );
// Gửi thẳng nút khôi phục: quyền, nonce
$chan_die_ls = static fn() => static function ( $chu ): void {
	throw new \RuntimeException( 'wp_die: ' . ( is_string( $chu ) ? $chu : 'lỗi' ) );
};
$chan_di_ls = static function ( $vi_tri ): string {
	throw new \RuntimeException( 'redirect: ' . $vi_tri );
};
add_filter( 'wp_die_handler', $chan_die_ls );
add_filter( 'wp_redirect', $chan_di_ls );
$gui_kp = static function ( int $nguoi, int $viec, bool $nonce ): string {
	wp_set_current_user( $nguoi );
	$_POST = $_REQUEST = array( 'viec' => (string) $viec );
	if ( $nonce ) {
		$_POST['_wpnonce'] = $_REQUEST['_wpnonce'] = wp_create_nonce( 'phongdao_khoi_phuc_' . $viec );
	}
	try {
		\Phongdao\GiaoTiep\QuanTri\SuaTacPham::khoi_phuc();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$kp_dg = $gui_kp( $doc_gia, $kp_1['viec'], true );
$kp_kn = $gui_kp( $ai_ql, $kp_1['viec'], false );
$kp_cd = $LPL::dang_gan( $tp1, 'chu_de' );
$kp_ok = $gui_kp( $ai_ql, $kp_1['viec'], true );
remove_filter( 'wp_die_handler', $chan_die_ls );
remove_filter( 'wp_redirect', $chan_di_ls );
$_POST = $_REQUEST = array();
wp_set_current_user( $ai_ql );
kt( 'nút khôi phục: người đọc, thiếu nonce bị chặn trước khi ghi; gửi đúng thì khôi phục rồi quay về Lịch sử', str_starts_with( $kp_dg, 'wp_die' ) && str_starts_with( $kp_kn, 'wp_die' ) && $cd_0 === $kp_cd && str_contains( $kp_ok, 'ket_qua=da_khoi_phuc' ) && str_ends_with( $kp_ok, '#lich-su' ) && in_array( $t_ls, $LPL::dang_gan( $tp1, 'chu_de' ), true ), array( $kp_dg, $kp_kn, $kp_ok ) );
$LPL::luu( $tp1, 'chu_de', $cd_0, $LPL::dang_gan( $tp1, 'chu_de' ), $ai_ql );
wp_delete_term( $t_ls, 'chu_de' );
PhanBai::ghi( $tp1, 'y_nghia', $yn_goc );
clean_post_cache( $tp1 );
// Dấu vết trạng thái: từ màn có sẵn của WordPress, nút Đăng / Ẩn của Phongdao, xoá vĩnh viễn
$bai_tt = wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Bài thử dấu vết trạng thái', 'post_name' => 'bai-thu-dau-vet' ) );
$tt_ls  = static fn(): array => array_values( array_map( static fn( array $r ): string => $r['quyet'], array_filter( Viec::lich_su( 'post', $bai_tt, 50 ), static fn( array $r ): bool => 'trang_thai' === $r['phan'] ) ) );
$tt_tao = $tt_ls();
wp_trash_post( $bai_tt );
wp_untrash_post( $bai_tt );
$tt_rac = $tt_ls();
kt( 'dấu vết trạng thái: tạo bài không ghi; thùng rác rồi lấy ra từ màn WordPress đều ghi, kèm người', array() === $tt_tao && array( 'ra_thung_rac', 'thung_rac' ) === $tt_rac && $ai_ql === ( Viec::lich_su( 'post', $bai_tt, 1 )[0]['nguoi_quyet'] ?? 0 ), $tt_rac );
$rest( "/tac-pham/$bai_tt/trang-thai", array( 'trang_thai' => 'publish', 'trang_thai_cu' => get_post_status( $bai_tt ) ) );
$tt_dang = $tt_ls();
kt( 'dấu vết trạng thái: nút Đăng của Phongdao chỉ ghi một dòng (không trùng)', 3 === count( $tt_dang ) && 'dang' === $tt_dang[0] && 'publish' === get_post_status( $bai_tt ), $tt_dang );
wp_delete_post( $bai_tt, true );
$tt_xoa = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'post' AND doi_tuong_id = %d AND quyet = 'xoa_vinh_vien'", $bai_tt ), ARRAY_A );
kt( 'xoá vĩnh viễn: còn dòng nhật ký ghi tên bài và trạng thái cuối', $tt_xoa && str_contains( (string) $tt_xoa['truoc'], 'Bài thử dấu vết trạng thái' ) && str_contains( (string) $tt_xoa['truoc'], 'publish' ) );
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'post' AND doi_tuong_id = %d", $bai_tt ) );
// Dấu vết hồ sơ nguồn, cài đặt nguồn
$NCD   = \Phongdao\GiaoTiep\QuanTri\NguonCaiDat::class;
$hs_ls = (int) wp_insert_post( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'post_title' => 'Hồ sơ thử lịch sử' ) );
$NCD::luu_ho_so( $hs_ls, array( 'ten' => 'Hồ sơ thử lịch sử', 'loai' => 'sach', 'chep_chu_thich' => '' ) );
$NCD::luu_ho_so( $hs_ls, array( 'ten' => 'Hồ sơ thử lịch sử', 'loai' => 'sach', 'chep_chu_thich' => '' ) );
$ls_hs = Viec::lich_su( 'ho_so', $hs_ls, 10 );
$td_hs = $ls_hs ? \Phongdao\MienDuLieu\SoSanh::truong_doi( (array) $ls_hs[0]['truoc'], (array) $ls_hs[0]['sau'] ) : array();
kt( 'dấu vết hồ sơ nguồn: đổi thì ghi trước/sau và người; lưu y như cũ thì không ghi', 1 === count( $ls_hs ) && $ai_ql === $ls_hs[0]['nguoi_quyet'] && array( 'Loại: (trống) → sach' ) === $td_hs, array( $ls_hs, $td_hs ) );
kt( 'trang hồ sơ nguồn hiện Lịch sử thay đổi', str_contains( $NCD::hien( 'ho_so', $hs_ls, '' ), 'Loại: (trống) → sach' ) );
wp_delete_post( $hs_ls, true );
$n_thu = NguonTuDong::lay( 'me-thu' );
$p_thu = array( 'bat' => $n_thu['bat'] ? '1' : '', 'so_bai_moi_ngay' => (string) $n_thu['so_bai_moi_ngay'], 'loai_lay' => $n_thu['loai_lay'], 'chep_chu_thich' => $n_thu['chep_chu_thich'] ? '1' : '', 'bo_tho_tuc' => in_array( 'tho_tuc', (array) $n_thu['bo_qua_co'], true ) ? '1' : '' );
$NCD::luu_nguon( 'me-thu', array( 'so_bai_moi_ngay' => (string) ( 50 === (int) $n_thu['so_bai_moi_ngay'] ? 49 : (int) $n_thu['so_bai_moi_ngay'] + 1 ) ) + $p_thu );
$NCD::luu_nguon( 'me-thu', $p_thu );
$ls_ng = array_values( array_filter( Viec::lich_su( 'nguon', 0, 50 ), static fn( array $r ): bool => 'me-thu' === $r['phan'] ) );
kt( 'dấu vết cài đặt nguồn: đổi số câu mỗi ngày rồi đổi lại — hai dòng có người; cài đặt về như cũ', 2 === count( $ls_ng ) && $ai_ql === $ls_ng[0]['nguoi_quyet'] && (int) $n_thu['so_bai_moi_ngay'] === (int) NguonTuDong::lay( 'me-thu' )['so_bai_moi_ngay'] && str_contains( $NCD::hien( 'nguon', 0, '' ), 'Số câu mới mỗi ngày: ' ), $ls_ng );
$_GET = array();

// ---------- Xử lý lô: Hộp việc (T-171) ----------
$LO     = \Phongdao\DichVu\XuLyLo\Lo::class;
$bai_lo = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Bài thử lô', 'post_name' => 'bai-thu-lo' ) );
$gy_ids = array();
$gy_t   = array();
for ( $i = 1; $i <= 5; $i++ ) {
	$gy_t[ $i ]   = (int) wp_insert_term( 'Chủ đề lô ' . $i, 'chu_de', array( 'slug' => 'chu-de-lo-' . $i ) )['term_id'];
	$gy_ids[ $i ] = Viec::tao( array( 'loai' => 'goi_y', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $bai_lo, 'phan' => 'chu_de', 'noi_dung' => array( 'loai' => 'chu_de', 'ma' => 'chu-de-lo-' . $i, 'ly_do' => 'Lý do thử ' . $i ), 'nguoi_tao' => 'may', 'uu_tien' => 15 ) );
}
$lo_1  = $LO::tao( 'goi_y_dong_y', array_values( $gy_ids ), 'chon', array( 'tt' => 'cho', 'loai' => 'goi_y', 'q' => '' ), $ai_ql );
$lo_v1 = Viec::lay( $lo_1['lo'] );
kt( 'lô: bước xem trước chốt 5 thẻ kèm phiên, chưa đổi gì, không lẫn vào Hộp việc', $lo_1['ok'] && Viec::LO_XEM === $lo_v1['trang_thai'] && 5 === count( $lo_v1['noi_dung']['muc'] ) && 5 === count( array_filter( $gy_ids, static fn( int $i ): bool => Viec::CHO === Viec::lay( $i )['trang_thai'] ) ) && ! in_array( $lo_1['lo'], Viec::ids_quan_tri( 'tat_ca', '', '', 5000 ), true ), $lo_1 );
$bc_1 = $LO::bo_chon( $lo_1['lo'], array_values( $gy_ids ), array( $gy_ids[1], $gy_ids[2], $gy_ids[3] ), $ai_ql );
QuyetViec::quyet( $gy_ids[3], 'de_sau', $ai_ql, true ); // thẻ 3 đổi sau lúc xem trước
$ad_1 = $LO::ap_dung( $lo_1['lo'], $ai_ql, true );
$kq_1 = (array) Viec::lay( $lo_1['lo'] )['sau']['ket_qua'];
kt( 'duyệt nhóm gợi ý có hai ngoại lệ: 2 thành công, 2 bỏ qua (ngoại lệ), 1 xung đột (đổi sau lúc xem trước); chỉ gắn chủ đề của thẻ thành công', $bc_1['ok'] && 2 === $bc_1['bo'] && $ad_1['ok'] && 0 === $ad_1['con'] && array( 'thanh_cong' => 2, 'bo_qua' => 2, 'xung_dot' => 1, 'loi' => 0 ) === $ad_1['dem'] && has_term( $gy_t[1], 'chu_de', $bai_lo ) && has_term( $gy_t[2], 'chu_de', $bai_lo ) && ! has_term( $gy_t[3], 'chu_de', $bai_lo ) && ! has_term( $gy_t[4], 'chu_de', $bai_lo ) && 'xung_dot' === ( $kq_1[ $gy_ids[3] ][0] ?? '' ) && Viec::LO_XONG === Viec::lay( $lo_1['lo'] )['trang_thai'], array( $ad_1, $kq_1 ) );
kt( 'lô: mỗi thẻ thành công ghi người quyết và quyết định như bấm tay; ngoại lệ còn ở Chờ', 'dong_y' === Viec::lay( $gy_ids[1] )['quyet'] && $ai_ql === Viec::lay( $gy_ids[1] )['nguoi_quyet'] && Viec::CHO === Viec::lay( $gy_ids[4] )['trang_thai'] && Viec::DE_SAU === Viec::lay( $gy_ids[3] )['trang_thai'] );
$ad_lai = $LO::ap_dung( $lo_1['lo'], $ai_ql, true );
kt( 'lô đã xong: chạy lại bị từ chối, không áp dụng trùng', ! $ad_lai['ok'] && str_contains( $ad_lai['loi'], 'xong' ) && 2 === count( array_intersect( array( $gy_t[1], $gy_t[2] ), wp_get_object_terms( $bai_lo, 'chu_de', array( 'fields' => 'ids' ) ) ) ), $ad_lai );
$chi_soan = (int) wp_insert_user( array( 'user_login' => 'chisoan', 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ) );
( new WP_User( $chi_soan ) )->add_cap( 'cadao_soan' );
$cs_truoc = get_current_user_id();
$yn_truoc = PhanBai::lay( $tp1, 'y_nghia' );
wp_set_current_user( $chi_soan );
$yc_cs = new WP_REST_Request( 'POST', '/phongdao/v1/tac-pham/' . $tp1 . '/phan/y_nghia' );
$yc_cs->set_body_params( array( 'chu' => 'Thử người chỉ soạn bấm hiện.', 'duyet' => '1' ) );
$yc_cs->set_param( 'id', $tp1 );
$yc_cs->set_param( 'phan', 'y_nghia' );
$kq_cs = \Phongdao\GiaoTiep\BienTap\Module::luu_phan( $yc_cs );
wp_set_current_user( $cs_truoc );
$the_cs = array_values( array_filter( Viec::mo( 'post', $tp1, 'nhap_y_nghia' ), static fn( array $v ): bool => 'Thử người chỉ soạn bấm hiện.' === ( $v['noi_dung']['chu'] ?? '' ) ) );
foreach ( $the_cs as $v ) {
	Viec::xoa( (int) $v['id'] );
}
kt( 'người không có quyền duyệt gửi "hiện cho người đọc": vẫn chỉ thành nháp, bài không đổi', ! empty( $kq_cs['ok'] ) && str_contains( (string) ( $kq_cs['thong_bao'] ?? '' ), 'nháp' ) && 1 === count( $the_cs ) && $yn_truoc === PhanBai::lay( $tp1, 'y_nghia' ), $kq_cs );
$lo_q = $LO::tao( 'goi_y_dong_y', array( $gy_ids[4] ), 'chon', array(), $chi_soan );
$cx   = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $bai_lo, 'noi_dung' => array( 'ly_do' => array() ), 'nguoi_tao' => 'kiem_thu' ) );
$lo_l = $LO::tao( 'viec_de_sau', array( $gy_ids[4], $cx ), 'chon', array(), $ai_ql );
kt( 'lô: Đồng ý gợi ý cần quyền duyệt; chọn lẫn hai loại thẻ bị từ chối', ! $lo_q['ok'] && str_contains( $lo_q['loi'], 'quyền duyệt' ) && ! $lo_l['ok'] && str_contains( $lo_l['loi'], 'cùng một loại' ), array( $lo_q, $lo_l ) );
$nd_id = Viec::tao( array( 'loai' => 'nguoi_doc', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => 999999999, 'noi_dung' => array( 'loai' => 'gop_y' ), 'nguoi_tao' => 'kiem_thu' ) );
$lo_nd = $LO::tao( 'viec_bo', array( $nd_id ), 'chon', array(), $ai_ql );
$ad_nd = $LO::ap_dung( $lo_nd['lo'], $ai_ql, true );
kt( 'lô Bỏ: nội dung người đọc gửi không bỏ theo lô (bỏ qua, quyết từng thẻ)', $ad_nd['ok'] && 1 === $ad_nd['dem']['bo_qua'] && Viec::CHO === Viec::lay( $nd_id )['trang_thai'], $ad_nd );
// Lô lớn: 120 thẻ Cần xem, chạy từng lượt 50, phiên chặn hai lượt chạy cùng lúc
$cx_ids = array( $cx );
for ( $i = 1; $i < 120; $i++ ) {
	$cx_ids[] = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $bai_lo, 'noi_dung' => array( 'ly_do' => array() ), 'nguoi_tao' => 'kiem_thu' ) );
}
$ids_loc = Viec::ids_quan_tri( Viec::CHO, 'can_xem', (string) $bai_lo, $LO::TOI_DA );
$lo_lon  = $LO::tao( 'viec_de_sau', $ids_loc, 'loc', array( 'tt' => 'cho', 'loai' => 'can_xem', 'q' => (string) $bai_lo ), $ai_ql );
// Trang xem trước nhiều trang: 3 ngoại lệ ở trang 2 → nút "Đánh dấu cả 3 thẻ … ở trang khác"; giu_het chỉ chủ lô
$muc_lon = array_map( static fn( array $m ): int => (int) $m[0], Viec::lay( $lo_lon['lo'] )['noi_dung']['muc'] );
$LO::bo_chon( $lo_lon['lo'], array_slice( $muc_lon, 40, 3 ), array(), $ai_ql );
$_GET    = array( 'lo' => (string) $lo_lon['lo'] );
$h_lon1  = \Phongdao\GiaoTiep\QuanTri\TrangLo::hien();
$gh_khac = $LO::giu_het( $lo_lon['lo'], $chi_soan );
$gh_ok   = $LO::giu_het( $lo_lon['lo'], $ai_ql );
$h_lon2  = \Phongdao\GiaoTiep\QuanTri\TrangLo::hien();
$_GET    = array();
kt( 'xem trước lô nhiều trang: thanh "Chọn tất cả" dùng ô giu[]; còn mục bỏ dấu ở trang khác thì có nút "Đánh dấu cả 3 thẻ đang bỏ dấu ở trang khác"; nút chính đếm sẵn số còn đánh dấu ở trang khác; giu_het chỉ chủ lô, đánh dấu lại mọi trang, chưa áp dụng gì', str_contains( $h_lon1, 'data-o="giu[]"' ) && str_contains( $h_lon1, 'name="lam" value="giu_het"' ) && str_contains( $h_lon1, 'Đánh dấu cả 3 thẻ đang bỏ dấu ở trang khác' ) && str_contains( $h_lon1, 'data-mau="Áp dụng cho %d mục" data-ngoai="87"' ) && str_contains( $h_lon1, 'Áp dụng cho 117 mục' ) && ! $gh_khac['ok'] && $gh_ok['ok'] && 0 === $gh_ok['bo'] && array() === Viec::lay( $lo_lon['lo'] )['noi_dung']['bo'] && Viec::LO_XEM === Viec::lay( $lo_lon['lo'] )['trang_thai'] && ! str_contains( $h_lon2, 'value="giu_het"' ) && str_contains( $h_lon2, 'data-ngoai="90"' ), array( $gh_khac, $gh_ok ) );
$luot    = array();
$luot[]  = $LO::ap_dung( $lo_lon['lo'], $ai_ql, true );
$v_lon   = Viec::lay( $lo_lon['lo'] );
$chan    = Viec::nhan_lo( $lo_lon['lo'], $v_lon['phien'] - 1 );
$luot[]  = $LO::ap_dung( $lo_lon['lo'], $ai_ql, true );
$luot[]  = $LO::ap_dung( $lo_lon['lo'], $ai_ql, true );
kt( 'lô lớn (mọi thẻ khớp bộ lọc): 120 thẻ chạy 3 lượt 50/50/20, đều Để sau; lượt mang phiên cũ bị chặn', 120 === count( $ids_loc ) && $lo_lon['ok'] && 'loc' === Viec::lay( $lo_lon['lo'] )['noi_dung']['pham_vi'] && array( 70, 20, 0 ) === array_column( $luot, 'con' ) && Viec::LO_DANG === $v_lon['trang_thai'] && ! $chan && 120 === $luot[2]['dem']['thanh_cong'] && 120 === count( array_filter( $cx_ids, static fn( int $i ): bool => Viec::DE_SAU === Viec::lay( $i )['trang_thai'] ) ), array_column( $luot, 'dem' ) );
// Giao diện, quyền
$_GET   = array( 'tt' => 'cho', 'loai' => 'goi_y' );
$h_hv   = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
$_GET   = array( 'tt' => 'xong' );
$h_hv_x = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
kt( 'Hộp việc wp-admin: thẻ Chờ có ô chọn, khối Làm theo lô với hai phạm vi; thẻ đã xong không có', str_contains( $h_hv, 'name="ids[]" value="' . $gy_ids[4] . '"' ) && str_contains( $h_hv, 'Làm theo lô' ) && str_contains( $h_hv, 'Mọi thẻ khớp bộ lọc' ) && str_contains( $h_hv, 'value="goi_y_dong_y"' ) && ! str_contains( $h_hv_x, 'Làm theo lô' ) );
$lo_x  = $LO::tao( 'viec_de_sau', array( $gy_ids[4], $gy_ids[5] ), 'chon', array(), $ai_ql );
$_GET  = array( 'lo' => (string) $lo_x['lo'] );
$h_lx  = \Phongdao\GiaoTiep\QuanTri\TrangLo::hien();
$_GET  = array( 'lo' => (string) $lo_1['lo'] );
$h_l1  = \Phongdao\GiaoTiep\QuanTri\TrangLo::hien();
wp_set_current_user( $chi_soan );
$h_khac = \Phongdao\GiaoTiep\QuanTri\TrangLo::hien();
wp_set_current_user( $doc_gia );
$h_dg_l = \Phongdao\GiaoTiep\QuanTri\TrangLo::hien();
wp_set_current_user( $ai_ql );
kt( 'màn Lô: xem trước có ô Áp dụng từng mục và nút Áp dụng; lô xong có thống kê, kết quả từng mục; người khác, người đọc không xem được', str_contains( $h_lx, 'name="giu[]" value="' . $gy_ids[5] . '"' ) && str_contains( $h_lx, 'Áp dụng cho 2 mục' ) && str_contains( $h_l1, 'Thành công 2' ) && str_contains( $h_l1, 'Xung đột 1' ) && str_contains( $h_l1, 'Thẻ đã đổi sau lúc xem trước.' ) && str_contains( $h_khac, 'người khác tạo' ) && ! str_contains( $h_dg_l, 'Lô #' ) );
// Gửi thẳng admin-post: quyền, nonce; Huỷ lô
$chan_die_lo = static fn() => static function ( $chu ): void {
	throw new \RuntimeException( 'wp_die: ' . ( is_string( $chu ) ? $chu : 'lỗi' ) );
};
$chan_di_lo = static function ( $vi_tri ): string {
	throw new \RuntimeException( 'redirect: ' . $vi_tri );
};
add_filter( 'wp_die_handler', $chan_die_lo );
add_filter( 'wp_redirect', $chan_di_lo );
$gui_lo = static function ( int $nguoi, string $hanh_dong, array $than, string $nonce ): string {
	wp_set_current_user( $nguoi );
	$_POST = $_REQUEST = $than + ( '' === $nonce ? array() : array( '_wpnonce' => wp_create_nonce( $nonce ) ) );
	try {
		\Phongdao\GiaoTiep\QuanTri\TrangLo::$hanh_dong();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$g_dg  = $gui_lo( $doc_gia, 'tao', array( 'thao_tac' => 'viec_de_sau', 'ids' => array( (string) $gy_ids[5] ) ), 'phongdao_lo_tao' );
$g_kn  = $gui_lo( $ai_ql, 'tao', array( 'thao_tac' => 'viec_de_sau', 'ids' => array( (string) $gy_ids[5] ) ), '' );
$g_ok  = $gui_lo( $ai_ql, 'tao', array( 'thao_tac' => 'viec_de_sau', 'pham_vi' => 'chon', 'ids' => array( (string) $gy_ids[5] ), 'tt' => 'cho', 'loai' => 'goi_y', 'q' => '' ), 'phongdao_lo_tao' );
$g_huy = $gui_lo( $ai_ql, 'lam', array( 'lo' => (string) $lo_x['lo'], 'lam' => 'huy' ), 'phongdao_lo_' . $lo_x['lo'] );
$lo_g  = $LO::tao( 'viec_de_sau', array( $gy_ids[5] ), 'chon', array(), $ai_ql );
$LO::bo_chon( (int) $lo_g['lo'], array( $gy_ids[5] ), array(), $ai_ql );
$g_gh  = $gui_lo( $ai_ql, 'lam', array( 'lo' => (string) $lo_g['lo'], 'lam' => 'giu_het', 'tren_trang' => array(), 'giu' => array() ), 'phongdao_lo_' . $lo_g['lo'] );
$v_gh  = Viec::lay( (int) $lo_g['lo'] );
$LO::huy( (int) $lo_g['lo'], $ai_ql );
kt( 'lô qua admin-post: nút "Đánh dấu cả … ở trang khác" (lam=giu_het) đánh dấu lại mọi mục, quay về trang xem trước, chưa áp dụng gì', $lo_g['ok'] && str_contains( $g_gh, 'lo=' . $lo_g['lo'] ) && array() === $v_gh['noi_dung']['bo'] && Viec::LO_XEM === $v_gh['trang_thai'] && Viec::CHO === Viec::lay( $gy_ids[5] )['trang_thai'], array( $lo_g, $g_gh, $v_gh['noi_dung']['bo'] ?? null ) );
remove_filter( 'wp_die_handler', $chan_die_lo );
remove_filter( 'wp_redirect', $chan_di_lo );
$_POST = $_REQUEST = array();
wp_set_current_user( $ai_ql );
kt( 'lô qua admin-post: người đọc, thiếu nonce bị chặn; tạo đúng thì sang màn xem trước; Huỷ lô thì dừng, thẻ giữ nguyên', str_starts_with( $g_dg, 'wp_die' ) && str_starts_with( $g_kn, 'wp_die' ) && str_contains( $g_ok, 'page=phongdao-lo&lo=' ) && str_contains( $g_huy, 'lo=' . $lo_x['lo'] ) && Viec::LO_HUY === Viec::lay( $lo_x['lo'] )['trang_thai'] && Viec::CHO === Viec::lay( $gy_ids[5] )['trang_thai'], array( $g_dg, $g_kn, $g_ok, $g_huy ) );
$_GET = array();
wp_delete_post( $bai_lo, true );
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Viec::bang() . " WHERE (doi_tuong_loai = 'post' AND doi_tuong_id IN (%d, %d)) OR loai = %s", $bai_lo, 999999999, Viec::LO ) );
foreach ( $gy_t as $t_lo ) {
	wp_delete_term( $t_lo, 'chu_de' );
}

// ---------- Xử lý lô: Tác phẩm — phân loại, ẩn, thùng rác (T-171) ----------
$tp_lo = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$tp_lo[ $i ] = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Bài lô tác phẩm ' . $i, 'post_name' => 'bai-lo-tac-pham-' . $i ) );
	wp_set_object_terms( $tp_lo[ $i ], 'ca-dao', KhaiBao::THE_LOAI );
}
$t_x = (int) wp_insert_term( 'Chủ đề lô tác phẩm', 'chu_de' )['term_id'];
$t_y = (int) wp_insert_term( 'Chủ đề lô khác', 'chu_de' )['term_id'];
$t_c = (int) wp_insert_term( 'Trò chơi lô thử', 'tro_choi' )['term_id'];
wp_set_object_terms( $tp_lo[2], $t_x, 'chu_de' ); // bài 2 đã có mục này
$lb = $LO::tao( 'pl_them', array_values( $tp_lo ), 'chon', array(), $ai_ql, array( 'tax' => 'chu_de', 'term' => $t_x ) );
$_GET = array( 'lo' => (string) $lb['lo'] );
$h_lb = \Phongdao\GiaoTiep\QuanTri\TrangLo::hien();
$LO::bo_chon( $lb['lo'], array_values( $tp_lo ), array( $tp_lo[1], $tp_lo[2], $tp_lo[3] ), $ai_ql ); // ngoại lệ: bài 4
$LPL::luu( $tp_lo[3], 'chu_de', array( $t_y ), array(), $ai_ql ); // bài 3 đổi sau lúc xem trước
$ab = $LO::ap_dung( $lb['lo'], $ai_ql, true );
kt( 'lô Thêm phân loại: 1 thành công, bài đã có thì bỏ qua, ngoại lệ bỏ qua, bài đổi sau lúc xem trước thì xung đột; bài thành công ghi Lịch sử', $lb['ok'] && str_contains( $h_lb, 'Đang có (Chủ đề)' ) && array( 'thanh_cong' => 1, 'bo_qua' => 2, 'xung_dot' => 1, 'loi' => 0 ) === $ab['dem'] && has_term( $t_x, 'chu_de', $tp_lo[1] ) && ! has_term( $t_x, 'chu_de', $tp_lo[3] ) && ! has_term( $t_x, 'chu_de', $tp_lo[4] ) && 'chu_de' === ( Viec::lich_su( 'post', $tp_lo[1], 1 )[0]['phan'] ?? '' ), array( $lb, $ab ) );
$lg = $LO::tao( 'pl_go', array( $tp_lo[1], $tp_lo[2] ), 'chon', array(), $ai_ql, array( 'tax' => 'chu_de', 'term' => $t_x ) );
$ag = $LO::ap_dung( $lg['lo'], $ai_ql, true );
$lt = $LO::tao( 'pl_thay', array( $tp_lo[3] ), 'chon', array(), $ai_ql, array( 'tax' => 'chu_de', 'term' => $t_x ) );
$at = $LO::ap_dung( $lt['lo'], $ai_ql, true );
kt( 'lô Gỡ, Thay thế phân loại', 2 === $ag['dem']['thanh_cong'] && ! has_term( $t_x, 'chu_de', $tp_lo[1] ) && ! has_term( $t_x, 'chu_de', $tp_lo[2] ) && 1 === $at['dem']['thanh_cong'] && array( $t_x ) === $LPL::dang_gan( $tp_lo[3], 'chu_de' ), array( $ag, $at ) );
$l_tl = $LO::tao( 'pl_them', array( $tp_lo[1] ), 'chon', array(), $ai_ql, array( 'tax' => KhaiBao::THE_LOAI, 'term' => (int) get_term_by( 'slug', 'ca-dao', KhaiBao::THE_LOAI )->term_id ) );
$l_tc = $LO::tao( 'pl_them', array( $tp_lo[1] ), 'chon', array(), $ai_ql, array( 'tax' => 'tro_choi', 'term' => $t_c ) );
$a_tc = $LO::ap_dung( $l_tc['lo'], $ai_ql, true );
kt( 'lô phân loại: thể loại không sửa theo lô; trò chơi trên bài không phải đồng dao thì bỏ qua', ! $l_tl['ok'] && $l_tc['ok'] && 1 === $a_tc['dem']['bo_qua'] && ! has_term( $t_c, 'tro_choi', $tp_lo[1] ), array( $l_tl, $a_tc ) );
wp_update_post( array( 'ID' => $tp_lo[4], 'post_status' => 'private' ) );
$la = $LO::tao( 'bai_an', array( $tp_lo[1], $tp_lo[4] ), 'chon', array(), $ai_ql );
$aa = $LO::ap_dung( $la['lo'], $ai_ql, true );
kt( 'lô Ẩn bài: bài đã đăng thành Đã ẩn, có dấu vết người làm; bài đã ẩn thì bỏ qua', 1 === $aa['dem']['thanh_cong'] && 1 === $aa['dem']['bo_qua'] && 'private' === get_post_status( $tp_lo[1] ) && 'an' === ( Viec::lich_su( 'post', $tp_lo[1], 1 )[0]['quyet'] ?? '' ) && $ai_ql === ( Viec::lich_su( 'post', $tp_lo[1], 1 )[0]['nguoi_quyet'] ?? 0 ), $aa );
$lr_q = $LO::tao( 'thung_rac', array( $tp_lo[2] ), 'chon', array(), $chi_soan );
$lr   = $LO::tao( 'thung_rac', array( $tp_lo[2], $tp_lo[3] ), 'chon', array(), $ai_ql );
$_GET = array( 'lo' => (string) $lr['lo'] );
$h_lr = \Phongdao\GiaoTiep\QuanTri\TrangLo::hien();
$ar   = $LO::ap_dung( $lr['lo'], $ai_ql, true );
$ll   = $LO::tao( 'lay_ra', array( $tp_lo[2], $tp_lo[1] ), 'chon', array(), $ai_ql );
$al   = $LO::ap_dung( $ll['lo'], $ai_ql, true );
kt( 'lô thùng rác cần quyền duyệt; xem trước hiện việc mở, tuyển tập; vào thùng rác rồi lấy ra (thành nháp); bài không ở thùng rác thì bỏ qua', ! $lr_q['ok'] && str_contains( $h_lr, 'Trong tuyển tập' ) && str_contains( $h_lr, 'Việc mở' ) && 2 === $ar['dem']['thanh_cong'] && 'trash' === get_post_status( $tp_lo[3] ) && 1 === $al['dem']['thanh_cong'] && 1 === $al['dem']['bo_qua'] && 'draft' === get_post_status( $tp_lo[2] ) && 'ra_thung_rac' === ( Viec::lich_su( 'post', $tp_lo[2], 1 )[0]['quyet'] ?? '' ), array( $ar, $al ) );
$_GET   = array( 'q' => 'Bài lô tác phẩm', 'tt' => 'tat_ca' );
$h_dstp = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::hien();
$ids_tp = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::ids_loc( array( 'q' => 'Bài lô tác phẩm', 'tt' => 'tat_ca' ), $LO::TOI_DA );
kt( 'danh sách Tác phẩm: thanh nổi (ẩn tới khi đánh dấu bài) có nút "Làm theo lô…"', str_contains( $h_dstp, '<div class="phongdao-thanh-noi" hidden data-don-vi="bài">' ) && str_contains( $h_dstp, 'data-mo-lo>Làm theo lô…</button>' ) );
kt( 'danh sách Tác phẩm: thanh "Chọn tất cả trên trang này" trước bảng, đếm mọi bài khớp bộ lọc', (bool) preg_match( '#<div class="phongdao-chon-het" hidden data-tong="(\d+)" data-don-vi="bài">#', $h_dstp, $m_ch ) && count( $ids_tp ) === (int) $m_ch[1] && strpos( $h_dstp, 'phongdao-chon-het' ) < strpos( $h_dstp, '<table class="widefat' ), $m_ch ?? null );
$ids_rac = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::ids_loc( array( 'q' => 'Bài lô tác phẩm', 'tt' => 'trash' ), $LO::TOI_DA );
kt( 'danh sách Tác phẩm: ô chọn, khối Làm theo lô có mục phân loại có sẵn; "mọi bài khớp bộ lọc" ở tab Tất cả lấy đủ bài trừ thùng rác (như WordPress), tab Thùng rác lấy bài trong thùng rác', str_contains( $h_dstp, 'name="ids[]" value="' . $tp_lo[1] . '"' ) && str_contains( $h_dstp, 'value="phongdao_lo_tao_bai"' ) && str_contains( $h_dstp, 'value="chu_de:' . $t_x . '"' ) && str_contains( $h_dstp, 'value="bai_an"' ) && 3 === count( array_intersect( $ids_tp, $tp_lo ) ) && ! in_array( $tp_lo[3], $ids_tp, true ) && in_array( $tp_lo[3], $ids_rac, true ), array( $ids_tp, $ids_rac ) );
add_filter( 'wp_die_handler', $chan_die_lo );
add_filter( 'wp_redirect', $chan_di_lo );
$g_tp    = $gui_lo( $ai_ql, 'tao_bai', array( 'thao_tac' => 'pl_them', 'muc_pl' => 'chu_de:' . $t_y, 'pham_vi' => 'loc', 'loc_q' => 'Bài lô tác phẩm', 'loc_tt' => 'tat_ca' ), 'phongdao_lo_tao_bai' );
$g_tp_vc = $gui_lo( $ai_ql, 'tao_bai', array( 'thao_tac' => 'viec_de_sau', 'ids' => array( (string) $tp_lo[1] ) ), 'phongdao_lo_tao_bai' );
$g_tp_dg = $gui_lo( $doc_gia, 'tao_bai', array( 'thao_tac' => 'bai_an', 'ids' => array( (string) $tp_lo[1] ) ), 'phongdao_lo_tao_bai' );
remove_filter( 'wp_die_handler', $chan_die_lo );
remove_filter( 'wp_redirect', $chan_di_lo );
$_POST = $_REQUEST = array();
wp_set_current_user( $ai_ql );
$lo_tp = Viec::ds_lo( $ai_ql, 1 )[0] ?? array();
kt( 'lô Tác phẩm qua admin-post: "mọi bài khớp bộ lọc" ở tab Tất cả chốt đủ 3 bài (bài trong thùng rác không tính) và sang xem trước; thao tác của Hộp việc bị từ chối; người đọc bị chặn', str_contains( $g_tp, 'page=phongdao-lo&lo=' ) && 'loc' === ( $lo_tp['noi_dung']['pham_vi'] ?? '' ) && 3 === count( $lo_tp['noi_dung']['muc'] ?? array() ) && str_contains( $g_tp_vc, 'page=phongdao-tac-pham' ) && str_starts_with( $g_tp_dg, 'wp_die' ), array( $g_tp, $g_tp_vc, $g_tp_dg ) );
$_GET = array();
foreach ( $tp_lo as $b_lo ) {
	wp_delete_post( $b_lo, true );
}
$wpdb->query( 'DELETE FROM ' . Viec::bang() . " WHERE (doi_tuong_loai = 'post' AND doi_tuong_id IN (" . implode( ',', array_map( 'intval', $tp_lo ) ) . ')) OR loai = \'' . Viec::LO . "'" );
wp_delete_term( $t_x, 'chu_de' );
wp_delete_term( $t_y, 'chu_de' );
wp_delete_term( $t_c, 'tro_choi' );

// ---------- Xử lý lô: sửa một phần theo từng hàng (T-171) ----------
$sp = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$sp[ $i ] = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Bài sửa từng hàng ' . $i, 'post_name' => 'bai-sua-tung-hang-' . $i ) );
	wp_set_object_terms( $sp[ $i ], 'ca-dao', KhaiBao::THE_LOAI );
}
LuuPhan::luu( $sp[1], 'y_nghia', array( 'chu' => 'Ý nghĩa cũ của bài một.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq );
$ls  = $LO::tao( 'sua_phan', array_values( $sp ), 'chon', array(), $ai_ql, array( 'phan' => 'y_nghia' ) );
$lg1 = $LO::luu_gia_tri( $ls['lo'], array( $sp[1] => 'Ý nghĩa mới của bài một.', $sp[2] => '', $sp[3] => 'Ý nghĩa mới của bài ba.' ), array_values( $sp ), array_values( $sp ), array( 'duyet' => true ), $ai_ql );
$_GET = array( 'lo' => (string) $ls['lo'] );
$h_ls = \Phongdao\GiaoTiep\QuanTri\TrangLo::hien();
LuuPhan::luu( $sp[3], 'y_nghia', array( 'chu' => 'Ai đó vừa viết cho bài ba.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq ); // đổi sau lúc xem trước
$as = $LO::ap_dung( $ls['lo'], $ai_ql, true );
kt( 'lô sửa từng hàng (Ý nghĩa, đã duyệt): mỗi bài một ô riêng có so trước/sau; ô trống bỏ qua; bài đổi sau lúc xem trước xung đột; bài thành công qua đường lưu của nút Sửa, có Lịch sử', $ls['ok'] && $lg1['ok'] && str_contains( $h_ls, 'name="gia_tri[' . $sp[1] . ']"' ) && str_contains( $h_ls, 'thêm: </span>Ý nghĩa mới của bài một.' ) && array( 'thanh_cong' => 1, 'bo_qua' => 1, 'xung_dot' => 1, 'loi' => 0 ) === $as['dem'] && 'Ý nghĩa mới của bài một.' === ( PhanBai::lay( $sp[1], 'y_nghia' )['chu'] ?? '' ) && 'Ai đó vừa viết cho bài ba.' === ( PhanBai::lay( $sp[3], 'y_nghia' )['chu'] ?? '' ) && null === PhanBai::lay( $sp[2], 'y_nghia' ) && 'y_nghia' === ( Viec::lich_su( 'post', $sp[1], 1 )[0]['phan'] ?? '' ), array( $lg1, $as ) );
$ln = $LO::tao( 'sua_phan', array( $sp[2] ), 'chon', array(), $ai_ql, array( 'phan' => 'y_nghia' ) );
$LO::luu_gia_tri( $ln['lo'], array( $sp[2] => 'Nháp ý nghĩa bài hai.' ), array( $sp[2] ), array( $sp[2] ), array(), $ai_ql );
$an = $LO::ap_dung( $ln['lo'], $ai_ql, true );
kt( 'lô sửa từng hàng không đánh "đã duyệt": Ý nghĩa thành nháp trong Hộp việc, người đọc chưa thấy', 1 === $an['dem']['thanh_cong'] && null === PhanBai::lay( $sp[2], 'y_nghia' ) && 1 === count( Viec::mo( 'post', $sp[2], 'nhap_y_nghia' ) ), $an );
$ld = $LO::tao( 'sua_phan', array( $sp[2] ), 'chon', array(), $ai_ql, array( 'phan' => 'dung_khi' ) );
$LO::luu_gia_tri( $ld['lo'], array( $sp[2] => 'Ví dụ thử.' ), array( $sp[2] ), array( $sp[2] ), array(), $ai_ql );
$ad = $LO::ap_dung( $ld['lo'], $ai_ql, true );
$l_loi = $LO::tao( 'sua_phan', array( $sp[2] ), 'chon', array(), $ai_ql, array( 'phan' => 'loi' ) );
$l_ai  = $LO::tao( 'sua_phan', array( $sp[1] ), 'chon', array(), $chi_soan, array( 'phan' => 'y_nghia' ) );
$g_ai  = $LO::luu_gia_tri( $l_ai['lo'], array( $sp[1] => 'Thử không có quyền duyệt.' ), array( $sp[1] ), array( $sp[1] ), array( 'duyet' => true ), $chi_soan );
kt( 'lô sửa từng hàng: phần không dùng cho thể loại thì bỏ qua; lời không sửa theo lô; người không có quyền duyệt không đánh "đã duyệt" được', 1 === $ad['dem']['bo_qua'] && null === PhanBai::lay( $sp[2], 'dung_khi' ) && ! $l_loi['ok'] && $g_ai['ok'] && empty( Viec::lay( $l_ai['lo'] )['noi_dung']['tham_so']['duyet'] ), array( $ad, $l_loi, $g_ai ) );
$_GET = array();
foreach ( $sp as $b_sp ) {
	wp_delete_post( $b_sp, true );
}
$wpdb->query( 'DELETE FROM ' . Viec::bang() . " WHERE (doi_tuong_loai = 'post' AND doi_tuong_id IN (" . implode( ',', array_map( 'intval', $sp ) ) . ')) OR loai = \'' . Viec::LO . "'" );

// ---------- Sửa xong quay về đúng danh sách đang lọc (T-172, kịch bản dùng thử 1) ----------
$SUA     = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::class;
$ds_loc  = add_query_arg( array( 'page' => 'phongdao-tac-pham', 'tt' => 'tat_ca', 'muc' => 'thieu_y_nghia', 'nguon' => 'tat_ca', 'paged' => 1 ), admin_url( 'admin.php' ) );
$_GET    = array( 'tt' => 'tat_ca', 'muc' => 'thieu_y_nghia' );
$h_ds_ve = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::hien();
$_GET    = array( 'id' => (string) $tp1, 've' => $ds_loc );
$h_sua_v = $SUA::hien();
$_GET    = array( 'id' => (string) $tp1, 've' => 'https://evil.example/wp-admin/admin.php?page=phongdao-tac-pham' );
$h_sua_l = $SUA::hien();
add_filter( 'wp_die_handler', $chan_die_lo );
add_filter( 'wp_redirect', $chan_di_lo );
wp_set_current_user( $ai_ql );
$_POST = $_REQUEST = array( 'id' => (string) $tp1, 'tax' => 'chu_de', 'chon' => array_map( 'strval', $LPL::dang_gan( $tp1, 'chu_de' ) ), 'da_thay' => array_map( 'strval', $LPL::dang_gan( $tp1, 'chu_de' ) ), 've' => $ds_loc, '_wpnonce' => wp_create_nonce( 'phongdao_sua_phan_loai_' . $tp1 . '_chu_de' ) );
try {
	$SUA::luu_phan_loai();
	$r_ve = 'không dừng';
} catch ( \RuntimeException $e ) {
	$r_ve = $e->getMessage();
}
remove_filter( 'wp_die_handler', $chan_die_lo );
remove_filter( 'wp_redirect', $chan_di_lo );
$_POST = $_REQUEST = array();
$_GET  = array();
kt( 'sửa xong quay về đúng danh sách đang lọc: nút Sửa bài mang địa chỉ danh sách, trang sửa có lối về và giữ lối đó sau khi lưu; địa chỉ lạ bị bỏ', str_contains( $h_ds_ve, 've=' . rawurlencode( $ds_loc ) ) && str_contains( $h_sua_v, 'đúng bộ lọc vừa xem' ) && str_contains( $h_sua_v, 'name="ve" value="' . esc_attr( $ds_loc ) . '"' ) && ! str_contains( $h_sua_l, 'đúng bộ lọc vừa xem' ) && str_contains( $r_ve, 've=' . rawurlencode( $ds_loc ) ), array( 'ds' => str_contains( $h_ds_ve, 've=' . rawurlencode( $ds_loc ) ), 'lien_sua' => preg_match( '/page=phongdao-sua-tac-pham[^"]*/', $h_ds_ve, $m_ve ) ? $m_ve[0] : '', 've_mong' => rawurlencode( $ds_loc ), 'sua' => str_contains( $h_sua_v, 'đúng bộ lọc vừa xem' ), 'an' => str_contains( $h_sua_v, 'name="ve" value="' . esc_attr( $ds_loc ) . '"' ), 'la' => ! str_contains( $h_sua_l, 'đúng bộ lọc vừa xem' ), 've' => $r_ve ) );
// ---------- Nguồn & cài đặt: thêm hồ sơ, đề nghị nguồn, Cài đặt chung; Đối chiếu sách (T-189.6) ----------
$HSK   = \Phongdao\KhoLuu\HoSo::class;
$hs_bt = $HSK::them( 'Sách thử biên tập', array( 'tac_gia' => 'Người soạn thử', 'nha_xuat_ban' => 'NXB thử', 'nam' => '1999', 'giay_phep' => 'CC BY', 'xin_phep' => 'dong_y', 'chinh_sach_hien' => 'tai_cho' ), false, $ai_ql );
$hs_qt = $HSK::them( 'Trang thử quản trị', array( 'loai' => 'web', 'url' => 'https://example.org/trang-thu/', 'giay_phep' => 'CC BY-SA 4.0', 'xin_phep' => 'khong_can' ), true, $ai_ql );
$hs_rg = $HSK::them( '  ', array(), true, $ai_ql );
$t_bt  = $HSK::truong( $hs_bt['id'] );
$t_qt  = $HSK::truong( $hs_qt['id'] );
kt( 'thêm hồ sơ nguồn: vai Biên tập chỉ tạo hồ sơ sách với tên, người biên soạn, nhà xuất bản, năm (giấy phép, xin phép, chính sách hiện để trống cho Quản trị); Quản trị điền mọi trường; không tên thì báo; ghi nhật ký người tạo', $hs_bt['ok'] && 'sach' === ( $t_bt['loai'] ?? '' ) && 'NXB thử' === ( $t_bt['nha_xuat_ban'] ?? '' ) && '' === (string) ( $t_bt['giay_phep'] ?? '' ) && '' === (string) ( $t_bt['xin_phep'] ?? '' ) && '' === (string) ( $t_bt['chinh_sach_hien'] ?? '' ) && $hs_qt['ok'] && 'web' === ( $t_qt['loai'] ?? '' ) && 'khong_can' === ( $t_qt['xin_phep'] ?? '' ) && ! $hs_rg['ok'] && 'them' === ( Viec::lich_su( 'ho_so', $hs_bt['id'], 1 )[0]['quyet'] ?? '' ), array( $t_bt, $t_qt ) );
$dn_sai = $NCD::de_nghi_nguon( array( 'url' => 'khong-phai-url' ), $ai_ql );
$dn_hop = Viec::dem_hop_viec();
$dn     = $NCD::de_nghi_nguon( array( 'url' => 'https://example.org/nguon-de-nghi/', 'ly_do' => 'Có nhiều ca dao vùng miền', 'xin_phep' => 'chua' ), $ai_ql );
$dn_v   = Viec::ds_cho( 50, $NCD::DE_NGHI );
$h_ng   = $NCD::hien( 'nguon', 0, '' );
$h_ch   = $NCD::hien( 'chong_rac', 0, '' );
$h_hs   = $NCD::hien( 'ho_so', 0, '' );
kt( 'Nguồn & cài đặt: đề nghị nguồn tự động mới thành việc cho người làm kỹ thuật (không vào Hộp việc, không bật gì), địa chỉ sai thì báo; Cài đặt chung gom chống rác, giờ máy chạy, trạng thái Google chỉ đọc (đường dẫn cũ "Chống rác" mở đúng thẻ); Hồ sơ nguồn có "+ Thêm hồ sơ nguồn" không giá trị mặc định', ! $dn_sai['ok'] && $dn['ok'] && $dn_hop === Viec::dem_hop_viec() && 'https://example.org/nguon-de-nghi/' === ( end( $dn_v )['noi_dung']['url'] ?? '' ) && str_contains( $h_ng, 'example.org/nguon-de-nghi/' ) && str_contains( $h_ch, 'nav-tab-active" href="' ) && str_contains( $h_ch, 'Cài đặt chung' ) && str_contains( $h_ch, 'Chống rác' ) && str_contains( $h_ch, 'Google' ) && str_contains( $h_ch, 'máy không đổi công tắc này' ) && ! str_contains( $h_ch, 'name="blog_public"' ) && str_contains( $h_hs, '+ Thêm hồ sơ nguồn' ) && str_contains( $h_hs, 'value="phongdao_luu_them_ho_so"' ) && ! str_contains( $h_hs, 'id="ths-xin_phep" name="xin_phep"><option value="">chưa rõ</option><option value="chua" selected' ), array( 'dn' => $dn, 'sai' => $dn_sai, 'hop' => array( $dn_hop, Viec::dem_hop_viec() ), 'dn_v' => $dn_v, 'ng' => str_contains( $h_ng, 'example.org/nguon-de-nghi/' ), 'ch' => array( str_contains( $h_ch, 'Cài đặt chung' ), str_contains( $h_ch, 'Chống rác' ), str_contains( $h_ch, 'máy không đổi công tắc này' ), str_contains( $h_ch, 'nav-tab-active" href="' ) ), 'hs' => array( str_contains( $h_hs, '+ Thêm hồ sơ nguồn' ), str_contains( $h_hs, 'value="phongdao_luu_them_ho_so"' ) ) ) );
foreach ( $dn_v as $v_dn ) {
	Viec::xoa( (int) $v_dn['id'] );
}
// Đối chiếu sách: nút riêng ở trang Sửa tác phẩm; giữ lời / sửa theo sách / ghi làm dị bản; thêm sách tại chỗ; chỉ người điền
$bai_dc = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Bài đối chiếu thử', 'post_name' => 'bai-doi-chieu-thu' ) );
wp_set_object_terms( $bai_dc, 'ca-dao', KhaiBao::THE_LOAI );
LuuPhan::luu( $bai_dc, 'loi', array( 'dong' => array( 'Dòng đối chiếu một', 'Dòng đối chiếu hai' ), 'nguoi_lam' => NguoiLam::MAY_CHEP ), array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true ) );
$_GET   = array( 'id' => (string) $bai_dc );
$h_dc   = $SUA::hien();
$_GET   = array();
$dc_giu = $SUA::luu_doi_chieu( $bai_dc, array( 'ho_so' => (string) $hs_bt['id'], 'trang' => '12', 'lam' => 'giu' ), $ai_ql );
$l_giu  = PhanBai::lay( $bai_dc, 'loi' );
$dc_moi_s = $SUA::luu_doi_chieu( $bai_dc, array( 'ho_so' => '0', 'sach_moi' => array( 'ten' => 'Sách của người chỉ soạn' ), 'lam' => 'giu' ), $chi_soan );
$dc_moi = $SUA::luu_doi_chieu( $bai_dc, array( 'ho_so' => '0', 'sach_moi' => array( 'ten' => 'Sách mới tại chỗ', 'tac_gia' => 'Người soạn', 'nha_xuat_ban' => 'NXB', 'nam' => '2001' ), 'trang' => '7', 'lam' => 'di_ban', 'loi_sach' => "Dòng trong sách một\nDòng trong sách hai" ), $ai_ql );
$db_dc  = (array) ( PhanBai::lay( $bai_dc, 'di_ban' )['muc'] ?? array() );
$dc_sua = $SUA::luu_doi_chieu( $bai_dc, array( 'ho_so' => (string) $hs_bt['id'], 'trang' => '13', 'lam' => 'sua', 'loi_sach' => "Dòng theo sách một\nDòng theo sách hai" ), $ai_ql );
$l_sua  = PhanBai::lay( $bai_dc, 'loi' );
// Lưu lời qua ô Sửa lời không gửi phần đối chiếu: đối chiếu giữ nguyên
$giu_dc = \Phongdao\GiaoTiep\BienTap\TamSua::doc( 'loi', array( 'dong' => "Dòng theo sách một\nDòng theo sách hai" ), $l_sua );
kt( 'Đối chiếu sách: nút riêng ở trang Sửa tác phẩm, ô Sửa lời không còn phần đối chiếu; "giữ" ghi sách, trang, lời và người làm giữ nguyên', str_contains( $h_dc, '<summary class="button">Đối chiếu sách</summary>' ) && str_contains( $h_dc, 'Người đọc sẽ thấy' ) && ! str_contains( $h_dc, 'name="doi_chieu[ho_so]"' ) && $dc_giu['ok'] && $hs_bt['id'] === (int) ( $l_giu['doi_chieu']['ho_so'] ?? 0 ) && '12' === (string) ( $l_giu['doi_chieu']['trang'] ?? '' ) && array( 'Dòng đối chiếu một', 'Dòng đối chiếu hai' ) === $l_giu['dong'] && NguoiLam::MAY_CHEP === ( $l_giu['nguoi_lam'] ?? '' ), array( $dc_giu, $l_giu ) );
kt( 'Đối chiếu sách: "+ Sách mới" tạo hồ sơ loại sách (người chỉ soạn không được); "ghi làm dị bản" thêm dị bản người viết ghi nguồn là sách, trang, lời đang đăng giữ; "sửa theo sách" đổi lời (người viết) kèm đối chiếu; lưu lời không gửi đối chiếu thì giữ đối chiếu', ! $dc_moi_s['ok'] && $dc_moi['ok'] && 1 === count( $db_dc ) && array( 'Dòng trong sách một', 'Dòng trong sách hai' ) === $db_dc[0]['dong'] && NguoiLam::NGUOI_VIET === $db_dc[0]['nguoi_lam'] && '7' === (string) ( $db_dc[0]['nguon']['trang'] ?? '' ) && 'sach' === ( $HSK::truong( (int) ( $db_dc[0]['nguon']['ho_so'] ?? 0 ) )['loai'] ?? '' ) && $dc_sua['ok'] && array( 'Dòng theo sách một', 'Dòng theo sách hai' ) === $l_sua['dong'] && NguoiLam::NGUOI_VIET === $l_sua['nguoi_lam'] && '13' === (string) ( $l_sua['doi_chieu']['trang'] ?? '' ) && '13' === (string) ( $giu_dc['gia_tri']['doi_chieu']['trang'] ?? '' ), array( $dc_moi, $db_dc, $dc_sua, $l_sua ) );
wp_delete_post( $bai_dc, true );
foreach ( array( $hs_bt['id'], $hs_qt['id'], (int) ( $db_dc[0]['nguon']['ho_so'] ?? 0 ) ) as $x_hs ) {
	if ( $x_hs ) {
		wp_delete_post( (int) $x_hs, true );
	}
}
// ---------- Trang sửa gọn, cờ thành công tắc, đổi thể loại kèm 301 (T-189.5) ----------
$bai_dtl = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Bài đổi thể loại thử', 'post_name' => 'bai-doi-the-loai-thu' ) );
wp_set_object_terms( $bai_dtl, 'cau-do', KhaiBao::THE_LOAI );
LuuPhan::luu( $bai_dtl, 'loi', array( 'dong' => array( 'Dòng đổi thể loại một', 'Dòng đổi thể loại hai' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq );
LuuPhan::luu( $bai_dtl, 'dap_an', array( 'chu' => 'Đáp án giữ lại', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq );
$_GET     = array( 'id' => (string) $bai_dtl );
$h_gon    = $SUA::hien();
$duong_cu = (string) wp_make_link_relative( (string) get_permalink( $bai_dtl ) );
$dtl_q    = $SUA::doi_the_loai_bai( $bai_dtl, 'ca-dao', $chi_soan );
$dtl      = $SUA::doi_the_loai_bai( $bai_dtl, 'ca-dao', $ai_ql );
clean_post_cache( $bai_dtl );
$duong_moi = (string) wp_make_link_relative( (string) get_permalink( $bai_dtl ) );
$ls_dtl    = array_values( array_filter( Viec::lich_su( 'post', $bai_dtl, 10 ), static fn( array $r ): bool => 'the_loai' === $r['phan'] ) );
kt( 'Sửa tác phẩm gọn: phần chưa có gom thành hàng "Thêm: …" (bấm mới mở ô), cờ "Có thể không phải dân gian" là công tắc ở cột bên (không nằm trong danh sách phần), người có quyền duyệt có "Đổi thể loại" kèm lời báo đường dẫn đổi', str_contains( $h_gon, '<p class="phongdao-sua-them"><strong>Thêm:</strong>' ) && str_contains( $h_gon, 'data-mo-phan="y_nghia"' ) && str_contains( $h_gon, '<section class="phongdao-sua-co" id="phan-co_nghi">' ) && ! str_contains( $h_gon, '<details class="phongdao-sua-phan chua-co" id="phan-co_nghi"' ) && str_contains( $h_gon, 'Đang tắt.' ) && str_contains( $h_gon, 'value="phongdao_doi_the_loai"' ) && str_contains( $h_gon, 'đường cũ tự chuyển sang đường mới' ) );
kt( 'Đổi thể loại: chỉ người có quyền duyệt; đường dẫn theo thể loại mới, đường cũ dẫn về đúng bài (chuyển 301 về đường chuẩn), Lịch sử ghi trước / sau và người đổi; phần riêng của thể loại cũ (đáp án) vẫn giữ', ! $dtl_q['ok'] && $dtl['ok'] && 'ca-dao' === \Phongdao\DichVu\DungTrang\DocBai::the_loai( get_post( $bai_dtl ) ) && str_starts_with( $duong_cu, '/cau-do/' ) && str_starts_with( $duong_moi, '/ca-dao/' ) && $bai_dtl === url_to_postid( home_url( $duong_cu ) ) && 'Đáp án giữ lại' === ( PhanBai::lay( $bai_dtl, 'dap_an' )['chu'] ?? '' ) && 1 === count( $ls_dtl ) && 'cau-do' === ( $ls_dtl[0]['truoc']['the_loai'] ?? '' ) && 'ca-dao' === ( $ls_dtl[0]['sau']['the_loai'] ?? '' ) && $ai_ql === (int) $ls_dtl[0]['nguoi_quyet'] && 'ca-dao' === ( \Phongdao\DichVu\TomTat\TomTat::lay( $bai_dtl )['the_loai'] ?? '' ), array( $dtl_q, $dtl, $duong_cu, $duong_moi, $ls_dtl ) );
// Cờ: bật thiếu nguồn thì không lưu; bật đủ lý do và nguồn thì bật; Tắt cờ thì gỡ
$bat_co = static function ( $vi_tri ): string {
	throw new \RuntimeException( 'redirect: ' . $vi_tri );
};
add_filter( 'wp_redirect', $bat_co );
$gui_co = static function ( array $than ) use ( $SUA, $bai_dtl ): string {
	$_POST = $_REQUEST = $than + array( 'id' => (string) $bai_dtl, 'phan' => 'co_nghi', '_wpnonce' => wp_create_nonce( 'phongdao_sua_phan_' . $bai_dtl . '_co_nghi' ) );
	try {
		$SUA::luu();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$r_co_thieu = $gui_co( array( 'ly_do' => 'Câu thử có thể là thơ hiện đại.' ) );
$co_thieu   = PhanBai::lay( $bai_dtl, 'co_nghi' );
$r_co_du    = $gui_co( array( 'ly_do' => 'Câu thử có thể là thơ hiện đại.', 'nguon' => array( 'ghi_chu' => 'Sách thử, tr. 12' ) ) );
$co_du      = PhanBai::lay( $bai_dtl, 'co_nghi' );
$r_co_tat   = $gui_co( array( 'ly_do' => '' ) );
$co_tat     = PhanBai::lay( $bai_dtl, 'co_nghi' );
$_POST      = $_REQUEST = array();
remove_filter( 'wp_redirect', $bat_co );
kt( 'Cờ "Có thể không phải dân gian": bật mà thiếu nguồn thì báo, không lưu; đủ lý do và nguồn thì bật (người viết); "Tắt cờ" gỡ cờ', null === $co_thieu && str_contains( $r_co_thieu, 'ket_qua=loi' ) && is_array( $co_du ) && 'Câu thử có thể là thơ hiện đại.' === ( $co_du['ly_do'] ?? '' ) && null === $co_tat, array( $r_co_thieu, $co_du, $r_co_tat ) );
wp_delete_post( $bai_dtl, true );
$_GET = array();
// ---------- Sửa tác phẩm một màn hai cột, "Bài tiếp theo" trong danh sách đã lọc (T-188) ----------
$_GET   = array( 'id' => (string) $tp1 );
$h_hc   = $SUA::hien();
$co_yn  = null !== PhanBai::lay( $tp1, 'y_nghia' );
kt( 'Sửa tác phẩm hai cột: bên trái lời rồi các phần (phần đã có mở sẵn, "Sửa lời" gập vì lời đã hiện ở đầu); bên phải trạng thái, việc chờ, phân loại, lịch sử', str_contains( $h_hc, '<div class="phongdao-sua-bo"><div class="phongdao-sua-chinh"><section class="phongdao-loi-xem">' ) && strpos( $h_hc, '<aside class="phongdao-sua-ben"' ) < strrpos( $h_hc, 'class="phongdao-trang-thai"' ) && strpos( $h_hc, '<div class="phongdao-sua-dau-dt"><div class="phongdao-trang-thai">' ) < strpos( $h_hc, '<div class="phongdao-sua-bo">' ) && ( array() === Viec::mo( 'post', $tp1 ) ? ! str_contains( $h_hc, 'Việc đang chờ' ) : strpos( $h_hc, '<aside class="phongdao-sua-ben"' ) < strpos( $h_hc, 'Việc đang chờ' ) ) && strpos( $h_hc, '<aside class="phongdao-sua-ben"' ) < strpos( $h_hc, 'id="phan-loai"' ) && strpos( $h_hc, '<aside class="phongdao-sua-ben"' ) < strpos( $h_hc, 'id="lich-su"' ) && str_contains( $h_hc, '<details class="phongdao-sua-phan" id="phan-loi">' ) && ( ! $co_yn || str_contains( $h_hc, '<details class="phongdao-sua-phan" id="phan-y_nghia" open>' ) ) && ! str_contains( $h_hc, 'data-bai-sau' ), array( $co_yn ) );
$ds_tat  = add_query_arg( array( 'page' => 'phongdao-tac-pham', 'tt' => 'tat_ca' ), admin_url( 'admin.php' ) );
$ids_tat = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::ids_loc( array( 'tt' => 'tat_ca' ), 1000 );
$p_hc    = (int) ( $ids_tat[0] ?? 0 );
$sau_hc  = (int) ( $ids_tat[1] ?? 0 );
$_GET    = array( 'id' => (string) $p_hc, 've' => $ds_tat );
$h_hc2   = $SUA::hien();
kt( 'Sửa tác phẩm: đến từ danh sách đã lọc thì có "Bài tiếp theo" đúng bài kế (giữ lối về danh sách), mỗi form phần mang bài kế, có tuỳ chọn lưu xong tự sang bài tiếp', $sau_hc > 0 && str_contains( $h_hc2, 'href="' . esc_url( $SUA::url( $sau_hc, $ds_tat ) ) . '" data-bai-sau>' ) && str_contains( $h_hc2, '<input type="hidden" name="bai_sau" value="' . $sau_hc . '">' ) && str_contains( $h_hc2, 'id="phongdao-sang-tiep"' ), array( $p_hc, $sau_hc ) );
// Lưu một phần với "tự sang bài tiếp": mở bài kế; không chọn thì ở lại, vẫn mang lối sang bài kế
$bcb_cu = PhanBai::lay( $p_hc, 'ban_co_biet' );
add_filter( 'wp_die_handler', $chan_die_lo );
add_filter( 'wp_redirect', $chan_di_lo );
$luu_hc = static function ( array $them ) use ( $SUA, $p_hc, $sau_hc, $ds_tat ): string {
	$_POST    = array( 'id' => (string) $p_hc, 'phan' => 'ban_co_biet', 'chu' => 'Bạn có biết thử chuyển bài.', 've' => $ds_tat, 'bai_sau' => (string) $sau_hc, '_wpnonce' => wp_create_nonce( 'phongdao_sua_phan_' . $p_hc . '_ban_co_biet' ) ) + $them;
	$_REQUEST = $_POST;
	try {
		$SUA::luu();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$r_sang = $luu_hc( array( 'sang' => '1' ) );
$r_o    = $luu_hc( array() );
remove_filter( 'wp_die_handler', $chan_die_lo );
remove_filter( 'wp_redirect', $chan_di_lo );
$_POST = $_REQUEST = array();
$_GET  = array();
LuuPhan::luu( $p_hc, 'ban_co_biet', $bcb_cu, array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true ) );
kt( 'Sửa tác phẩm: lưu xong có "tự sang bài tiếp" thì mở bài kế (giữ lối về danh sách); không chọn thì ở lại bài này, lối sang bài kế còn', str_contains( $r_sang, 'redirect: ' ) && str_contains( $r_sang, 'id=' . $sau_hc ) && str_contains( $r_sang, 'ket_qua=da_luu_sang' ) && str_contains( $r_sang, 've=' ) && str_contains( $r_o, 'id=' . $p_hc ) && str_contains( $r_o, 'bai_sau=' . $sau_hc ) && ! str_contains( $r_o, 'da_luu_sang' ), array( $r_sang, $r_o ) );

// ---------- Bảng điều khiển máy, căn cứ nguồn trước khi quyết (T-179) ----------
$DK = \Phongdao\DichVu\May\DieuKhien::class;
NguonTuDong::luu( array( 'id' => 'dk-thu', 'ten' => 'Nguồn thử điều khiển', 'bat' => true, 'so_bai_moi_ngay' => 2 ) + NguonTuDong::mac_dinh() );
$dk_kho = array();
for ( $i = 1; $i <= 2; $i++ ) {
	$wpdb->insert( Kho::bang(), array( 'nguon_id' => 'dk-thu', 'ma_goc' => 'dk-' . $i, 'url' => 'https://example.org/dk-thu/' . $i . '/', 'loai' => 'ca_dao', 'tieu_de' => 'Câu điều khiển thử ' . $i, 'loi' => "Câu điều khiển thử $i dòng một\nCâu điều khiển thử $i dòng hai", 'du_lieu' => '{}', 'ngay_lay' => current_time( 'mysql' ), 'trang_thai' => 'moi', 'doc_trang' => '2026-11-09' ) );
	$dk_kho[] = (int) $wpdb->insert_id;
}
$the_dk = static fn(): int => count( array_merge( ...array_map( static fn( int $k ): array => Viec::mo( 'kho', $k, 'cau_moi' ), $dk_kho ) ) );
$ng_cu  = NguonTuDong::tat_ca(); // các nguồn thử khác tắt tạm trong ca này, trả nguyên sau
foreach ( array_keys( $ng_cu ) as $id_ng ) {
	if ( 'dk-thu' !== $id_ng ) {
		NguonTuDong::luu( array( 'bat' => false ) + $ng_cu[ $id_ng ] );
	}
}
$goi_dk  = static fn(): int => count( array_intersect( UngVien::goi_y_hom_nay()['ids'], $dk_kho ) );
delete_option( UngVien::GOI_Y );
$dk_1   = $DK::dat( 'chon_cau', true, $ai_ql, 'Thử tạm dừng' );
UngVien::chon_hang_ngay();
$dk_dung = $goi_dk();
$dk_2    = $DK::dat( 'chon_cau', false, $ai_ql );
UngVien::chon_hang_ngay();
$dk_chay = $goi_dk();
$dk_the  = $the_dk();
$dk_ngay = UngVien::goi_y_hom_nay()['ngay'];
$nk_dk   = array_values( array_filter( Viec::lich_su( 'may', 0, 20 ), static fn( array $r ): bool => 'chon_cau' === $r['phan'] ) );
$ng_nay  = NguonTuDong::tat_ca();
update_option( NguonTuDong::TUY_CHON, array( 'dk-thu' => $ng_nay['dk-thu'] ) + $ng_cu, false ); // trả các nguồn khác về nguyên trạng
kt( 'bảng điều khiển máy: tạm dừng "chọn câu mới hằng ngày" thì lượt theo lịch không gợi ý câu nào; chạy lại thì gợi ý đúng hạn mức vào danh sách "Máy gợi ý hôm nay" (D-104: không tạo thẻ Câu mới); mỗi lần bấm ghi nhật ký kèm lý do', $dk_1['ok'] && 0 === $dk_dung && $dk_2['ok'] && 2 === $dk_chay && 0 === $dk_the && wp_date( 'Y-m-d' ) === $dk_ngay && 2 === count( $nk_dk ) && 'chay_lai' === $nk_dk[0]['quyet'] && 'tam_dung' === $nk_dk[1]['quyet'] && 'Thử tạm dừng' === ( $nk_dk[1]['noi_dung']['ly_do'] ?? '' ) && $ai_ql === $nk_dk[1]['nguoi_quyet'], array( $dk_dung, $dk_chay, $nk_dk ) );
$gy_sau_cu = get_option( \Phongdao\DichVu\GoiY\GoiY::SAU, -1 );
update_option( \Phongdao\DichVu\GoiY\GoiY::SAU, 0, false );
wp_clear_scheduled_hook( \Phongdao\DichVu\GoiY\GoiY::VIEC_NEN );
$DK::dat( 'goi_y', true, $ai_ql );
$gy_lo = \Phongdao\DichVu\GoiY\GoiY::lo();
$gy_giu = (int) get_option( \Phongdao\DichVu\GoiY\GoiY::SAU, -1 );
$DK::dat( 'goi_y', false, $ai_ql );
kt( 'tạm dừng gợi ý theo lô: lô không chạy, giữ chỗ dừng; chạy lại thì hẹn lô kế tiếp', 0 === $gy_lo && 0 === $gy_giu && (bool) wp_next_scheduled( \Phongdao\DichVu\GoiY\GoiY::VIEC_NEN ), array( $gy_lo, $gy_giu ) );
wp_clear_scheduled_hook( \Phongdao\DichVu\GoiY\GoiY::VIEC_NEN );
update_option( \Phongdao\DichVu\GoiY\GoiY::SAU, $gy_sau_cu, false );
$DK::dat( 'doc_nguon', true, $ai_ql );
$tt_dk = $DK::trang_thai();
$h_may = $NCD::hien( 'may', 0, '' );
$DK::dat( 'doc_nguon', false, $ai_ql );
kt( 'thẻ "Máy đang làm": ba việc nền, trạng thái tạm dừng, hạn mức từng nguồn, nút Chạy lại, nhật ký; chỉ mục tìm chỉ xem', 3 === count( $tt_dk ) && $tt_dk['doc_nguon']['tam_dung'] && str_contains( $tt_dk['chon_cau']['han_muc'], 'Nguồn thử điều khiển: 2 câu/ngày' ) && str_contains( $h_may, 'Đang tạm dừng' ) && str_contains( $h_may, '>Chạy lại</button>' ) && str_contains( $h_may, 'Nhật ký tạm dừng, chạy lại' ) && str_contains( $h_may, 'Dựng lại chỉ mục tìm' ) && ! $DK::dang_dung( 'doc_nguon' ), $tt_dk );
// Lịch hằng ngày không tạo thẻ nữa (D-104): thẻ Câu mới tạo bằng lệnh / người chọn vẫn có lối xem trang gốc
$the_moi = Viec::mo( 'kho', $dk_kho[0], 'cau_moi' )[0] ?? Viec::lay( (int) UngVien::dua_vao_hop_viec( $dk_kho[0], $ai_ql )['viec'] );
kt( 'thẻ Câu mới có lối xem trang gốc máy đã lấy (chỉ người biên soạn thấy thẻ)', $the_moi && str_contains( \Phongdao\GiaoTiep\BienTap\HopViec::the( $the_moi ), 'https://example.org/dk-thu/1/' ) );
$_GET  = array( 'id' => (string) $tp1 );
$h_goc = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
kt( 'trang Sửa tác phẩm hiện trang gốc máy đã lấy cho người biên soạn', str_contains( $h_goc, 'xem trang gốc' ) && str_contains( $h_goc, esc_url( "$G/cd-1/" ) ) );
$_GET = array();
add_filter( 'wp_die_handler', $chan_die_lo );
add_filter( 'wp_redirect', $chan_di_lo );
$gui_may = static function ( int $nguoi, bool $nonce ) use ( $NCD ): string {
	wp_set_current_user( $nguoi );
	$_POST = $_REQUEST = array( 'viec' => 'goi_y', 'lam' => 'dung' ) + ( $nonce ? array( '_wpnonce' => wp_create_nonce( 'phongdao_luu_may' ) ) : array() );
	try {
		$NCD::nhan_may();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$m_bt = $gui_may( $chi_soan, true );
$m_ok = $gui_may( $ai_ql, true );
remove_filter( 'wp_die_handler', $chan_die_lo );
remove_filter( 'wp_redirect', $chan_di_lo );
$_POST = $_REQUEST = array();
wp_set_current_user( $ai_ql );
$m_dung = $DK::dang_dung( 'goi_y' );
$DK::dat( 'goi_y', false, $ai_ql );
wp_clear_scheduled_hook( \Phongdao\DichVu\GoiY\GoiY::VIEC_NEN );
kt( 'tạm dừng qua admin-post: chỉ người có quyền nguồn; gửi đúng thì tạm dừng và quay về thẻ Máy đang làm', str_starts_with( $m_bt, 'wp_die' ) && str_contains( $m_ok, 'the=may' ) && $m_dung, array( $m_bt, $m_ok ) );
$hs_ah = (int) wp_insert_post( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'post_title' => 'Sách thử ảnh hưởng' ) );
$NCD::luu_ho_so( $hs_ah, array( 'ten' => 'Sách thử ảnh hưởng', 'loai' => 'sach' ) );
$bai_ah = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Bài thử ảnh hưởng', 'post_name' => 'bai-thu-anh-huong' ) );
wp_set_object_terms( $bai_ah, 'ca-dao', KhaiBao::THE_LOAI );
$kq_ah = LuuPhan::luu( $bai_ah, 'loi', array( 'dong' => array( 'Dòng thử ảnh hưởng một', 'Dòng thử ảnh hưởng hai' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET, 'doi_chieu' => array( 'ho_so' => $hs_ah, 'trang' => '3' ) ), $ncq );
$ah    = $NCD::anh_huong( $hs_ah );
kt( 'hồ sơ nguồn: đếm bài và phần chép từ / đối chiếu với hồ sơ trước khi đổi chính sách; trang hồ sơ hiện khối "Bài chịu ảnh hưởng"', $kq_ah['ok'] && 1 === $ah['tong'] && 1 === ( $ah['phan']['loi'] ?? 0 ) && str_contains( $NCD::hien( 'ho_so', $hs_ah, '' ), 'Bài chịu ảnh hưởng:</strong> 1 bài' ), array( $kq_ah, $ah ) );
wp_delete_post( $bai_ah, true );
wp_delete_post( $hs_ah, true );
$wpdb->query( 'DELETE FROM ' . Viec::bang() . " WHERE doi_tuong_loai = 'kho' AND doi_tuong_id IN (" . implode( ',', $dk_kho ) . ')' );
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Kho::bang() . ' WHERE nguon_id = %s', 'dk-thu' ) );
$ds_ng = NguonTuDong::tat_ca();
unset( $ds_ng['dk-thu'] );
update_option( NguonTuDong::TUY_CHON, $ds_ng, false );
foreach ( $ids_nhieu as $id_nhieu ) {
	Viec::xoa( $id_nhieu );
}
Viec::xoa( $id_de_sau );
$da_xong = Viec::tao( array( 'loai' => 'can_xem', 'trang_thai' => Viec::XONG, 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'noi_dung' => array( 'ly_do' => array() ), 'nguoi_tao' => 'kiem_thu', 'quyet' => 'xong' ) );
$da_bo = Viec::tao( array( 'loai' => 'can_xem', 'trang_thai' => Viec::BO, 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'noi_dung' => array( 'ly_do' => array() ), 'nguoi_tao' => 'kiem_thu', 'quyet' => 'bo' ) );
$_GET = array( 'tt' => Viec::XONG, 'q' => (string) $da_xong );
$h_xong_admin = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
$_GET = array( 'tt' => Viec::BO, 'q' => (string) $da_bo );
$h_bo_admin = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
$_GET = array( 'tt' => 'tat_ca', 'q' => get_the_title( $tp1 ) );
$h_tim_admin = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
kt( 'wp-admin Hộp việc: Xong, Bỏ và tìm tên bài không mất thẻ', str_contains( $h_xong_admin, 'Cần xem #' . $da_xong ) && str_contains( $h_bo_admin, 'Cần xem #' . $da_bo ) && str_contains( $h_tim_admin, 'Cần xem #' . $da_xong ) );
$_GET = array( 'viec' => (string) $da_xong );
kt( 'wp-admin Hộp việc: thẻ đã đóng còn xem được nhưng không hiện nút quyết', str_contains( \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien(), 'Đã quyết: <strong>' ) && ! str_contains( \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien(), 'name="quyet"' ) );
Viec::xoa( $da_xong );
Viec::xoa( $da_bo );
$_GET = $get_cu;
$v_nhap = Viec::mo( 'post', $tp1, 'nhap_y_nghia' )[0];
$q      = QuyetViec::quyet( $v_nhap['id'], 'duyet', $ai_ql, true );
kt( 'quyết "Duyệt" nháp ý nghĩa: vào bài với dấu AI, việc đóng', $q['ok'] && 'Ý nghĩa nháp mẫu.' === PhanBai::lay( $tp1, 'y_nghia' )['chu'] && NguoiLam::AI_DA_DUYET === PhanBai::lay( $tp1, 'y_nghia' )['nguoi_lam'] && array() === Viec::mo( 'post', $tp1, 'nhap_y_nghia' ), $q );
$nghia_truoc_hoan = PhanBai::lay( $tp1, 'y_nghia' );
$v_duyet_hoan = Viec::tao( array( 'loai' => 'nhap_y_nghia', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'phan' => 'y_nghia', 'noi_dung' => array( 'chu' => 'Ý nghĩa thử hoàn tác.', 'ai' => false ), 'nguoi_tao' => 'kiem_thu' ) );
$q_duyet_hoan = QuyetViec::quyet( $v_duyet_hoan, 'duyet', $ai_ql, true, 0 );
$v_duyet_sau = Viec::lay( $v_duyet_hoan );
$_GET = array( 'da' => 'Đã duyệt.', 'hoan_viec' => (string) $v_duyet_hoan, 'hoan_phien' => (string) $v_duyet_sau['phien'] );
$h_duyet_hoan = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
$_GET = $get_cu;
$q_phuc_nghia = QuyetViec::hoan_tac( $v_duyet_hoan, $v_duyet_sau['phien'], $ai_ql );
kt( 'Duyệt ý nghĩa: Hoàn tác 10 giây khôi phục phần bài cũ, mở lại thẻ và ghi nhật ký', $q_duyet_hoan['ok'] && str_contains( $h_duyet_hoan, 'Hoàn tác trong 10 giây' ) && $q_phuc_nghia['ok'] && $nghia_truoc_hoan === PhanBai::lay( $tp1, 'y_nghia' ) && Viec::CHO === Viec::lay( $v_duyet_hoan )['trang_thai'] && 2 === Viec::lay( $v_duyet_hoan )['phien'] && 1 === count( Viec::lich_su( 'viec', $v_duyet_hoan ) ), array( $q_duyet_hoan, $q_phuc_nghia ) );
$bai_nghia_doi = wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'draft', 'post_title' => 'Bài mẫu kiểm Hoàn tác', 'post_name' => 'bai-mau-kiem-hoan-tac' ) );
wp_set_object_terms( $bai_nghia_doi, 'ca-dao', KhaiBao::THE_LOAI );
$v_nghia_doi = Viec::tao( array( 'loai' => 'nhap_y_nghia', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $bai_nghia_doi, 'phan' => 'y_nghia', 'noi_dung' => array( 'chu' => 'Ý nghĩa mẫu trước sửa.', 'ai' => false ), 'nguoi_tao' => 'kiem_thu' ) );
$q_nghia_doi = QuyetViec::quyet( $v_nghia_doi, 'duyet', $ai_ql, true, 0 );
$sua_nghia_doi = LuuPhan::luu( $bai_nghia_doi, 'y_nghia', array( 'chu' => 'Ý nghĩa mẫu sửa tiếp.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true ) );
$q_phuc_doi = QuyetViec::hoan_tac( $v_nghia_doi, 1, $ai_ql );
kt( 'Ý nghĩa đã sửa tiếp: Hoàn tác cũ từ chối, giữ bài và thẻ', $q_nghia_doi['ok'] && $sua_nghia_doi['ok'] && ! $q_phuc_doi['ok'] && 'Ý nghĩa mẫu sửa tiếp.' === ( PhanBai::lay( $bai_nghia_doi, 'y_nghia' )['chu'] ?? '' ) && Viec::XONG === Viec::lay( $v_nghia_doi )['trang_thai'] );
wp_delete_post( $bai_nghia_doi, true );
kt( 'quyết lại việc đã quyết → báo đã quyết; lựa chọn lạ → báo lỗi', ! QuyetViec::quyet( $v_nhap['id'], 'duyet', $ai_ql, true )['ok'] && ! QuyetViec::quyet( $vgan[0]['id'], 'xoa-het', $ai_ql, true )['ok'] );
$v_phien = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'nguoi_tao' => 'kiem_thu' ) );
$phien_cu = Viec::lay( $v_phien )['phien'];
$q_truoc = QuyetViec::quyet( $v_phien, 'de_sau', $ai_ql, true, $phien_cu );
$q_cu = QuyetViec::quyet( $v_phien, 'xong', $ai_ql, true, $phien_cu );
$phien_moi = Viec::lay( $v_phien );
kt( 'hai phiên cùng một thẻ: lượt cũ bị chặn dù trạng thái Để sau vẫn mở', $q_truoc['ok'] && ! $q_cu['ok'] && Viec::DE_SAU === $phien_moi['trang_thai'] && 1 === $phien_moi['phien'] && 'de_sau' === $phien_moi['quyet'] );
kt( 'tải lại đúng phiên mới thì quyết được, phiên tăng đúng một lần', QuyetViec::quyet( $v_phien, 'xong', $ai_ql, true, $phien_moi['phien'] )['ok'] && 2 === Viec::lay( $v_phien )['phien'] );
$v_hoan = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'nguoi_tao' => 'kiem_thu' ) );
$q_hoan = QuyetViec::quyet( $v_hoan, 'xong', $ai_ql, true, 0 );
$da_quyet_hoan = Viec::lay( $v_hoan );
$_GET = array( 'da' => 'Đã đánh dấu xem xong.', 'hoan_viec' => (string) $v_hoan, 'hoan_phien' => (string) $da_quyet_hoan['phien'] );
$h_hoan = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
$_GET = $get_cu;
$q_phuc = QuyetViec::hoan_tac( $v_hoan, $da_quyet_hoan['phien'], $ai_ql );
$sau_hoan = Viec::lay( $v_hoan );
$ls_hoan = Viec::lich_su( 'viec', $v_hoan );
kt( 'Hoàn tác quyết thẻ đổi trạng thái: có nút 10 giây, trả về Chờ và ghi nhật ký mới', $q_hoan['ok'] && str_contains( $h_hoan, 'Hoàn tác trong 10 giây' ) && $q_phuc['ok'] && Viec::CHO === $sau_hoan['trang_thai'] && 2 === $sau_hoan['phien'] && '' === $sau_hoan['quyet'] && 1 === count( $ls_hoan ) && 'hoan_tac' === $ls_hoan[0]['quyet'] && Viec::XONG === $ls_hoan[0]['truoc']['trang_thai'] && Viec::CHO === $ls_hoan[0]['sau']['trang_thai'], array( 'q' => $q_phuc, 'viec' => $sau_hoan ) );
kt( 'Hoàn tác cũ hoặc quyết từ phiên cũ không được chạy lại', ! QuyetViec::hoan_tac( $v_hoan, $da_quyet_hoan['phien'], $ai_ql )['ok'] && ! QuyetViec::quyet( $v_hoan, 'xong', $ai_ql, true, 0 )['ok'] );
$v_hoan_de_sau = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'nguoi_tao' => 'kiem_thu' ) );
$q_de_sau = QuyetViec::quyet( $v_hoan_de_sau, 'de_sau', $ai_ql, true, 0 );
$q_xong_de_sau = QuyetViec::quyet( $v_hoan_de_sau, 'xong', $ai_ql, true, 1 );
$q_phuc_de_sau = QuyetViec::hoan_tac( $v_hoan_de_sau, 2, $ai_ql );
kt( 'Hoàn tác thẻ đã Để sau rồi đánh dấu xong trả về Để sau, không tự đưa về Chờ', $q_de_sau['ok'] && $q_xong_de_sau['ok'] && $q_phuc_de_sau['ok'] && Viec::DE_SAU === Viec::lay( $v_hoan_de_sau )['trang_thai'] );
$v_het_han = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'nguoi_tao' => 'kiem_thu' ) );
QuyetViec::quyet( $v_het_han, 'xong', $ai_ql, true, 0 );
$wpdb->update( Viec::bang(), array( 'quyet_luc' => gmdate( 'Y-m-d H:i:s', time() - 11 ) ), array( 'id' => $v_het_han ) );
kt( 'Hoàn tác quá 10 giây hoặc khác người quyết bị chặn, không đổi thẻ', ! QuyetViec::hoan_tac( $v_het_han, 1, $ai_ql )['ok'] && ! QuyetViec::hoan_tac( $v_het_han, 1, $ai_ql + 1 )['ok'] && Viec::XONG === Viec::lay( $v_het_han )['trang_thai'] );
$v_loi_hoan = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'nguoi_tao' => 'kiem_thu' ) );
QuyetViec::quyet( $v_loi_hoan, 'xong', $ai_ql, true, 0 );
$da_chan_hoan = false;
$chan_nhat_ky_hoan = static function ( $sql ) use ( &$da_chan_hoan ) {
	if ( ! $da_chan_hoan && preg_match( '/^INSERT\s+INTO\s+`?' . preg_quote( Viec::bang(), '/' ) . '`?\s*\(/i', $sql ) ) {
		$da_chan_hoan = true;
		return 'CA_DAO_KIEM_THU_LOI_GHI_NHAT_KY_HOAN_TAC';
	}
	return $sql;
};
add_filter( 'query', $chan_nhat_ky_hoan );
$q_loi_hoan = QuyetViec::hoan_tac( $v_loi_hoan, 1, $ai_ql );
remove_filter( 'query', $chan_nhat_ky_hoan );
kt( 'lỗi ghi nhật ký Hoàn tác: thẻ và phiên giữ nguyên, không có nhật ký nửa chừng', $da_chan_hoan && ! $q_loi_hoan['ok'] && Viec::XONG === Viec::lay( $v_loi_hoan )['trang_thai'] && 1 === Viec::lay( $v_loi_hoan )['phien'] && array() === Viec::lich_su( 'viec', $v_loi_hoan ), $q_loi_hoan );
$v_khac = Viec::tao( array( 'loai' => 'co_the_trung', 'trang_thai' => Viec::DE_SAU, 'phien' => 1, 'quyet' => 'de_sau', 'truoc' => array( 'trang_thai' => Viec::CHO ), 'nguoi_quyet' => $ai_ql, 'quyet_luc' => gmdate( 'Y-m-d H:i:s' ), 'nguoi_tao' => 'kiem_thu' ) );
kt( 'Để sau cặp câu có tác động phụ không nhận Hoàn tác trạng thái đơn thuần', ! QuyetViec::co_the_hoan_tac( Viec::lay( $v_khac ), $ai_ql, 1 ) && ! QuyetViec::hoan_tac( $v_khac, 1, $ai_ql )['ok'] );
$v_loi = Viec::tao( array( 'loai' => 'nhap_y_nghia', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'phan' => 'y_nghia', 'noi_dung' => array( 'chu' => 'Ý nghĩa thử ghi lỗi.', 'ai' => false ), 'nguoi_tao' => 'kiem_thu' ) );
$nghia_truoc = PhanBai::lay( $tp1, 'y_nghia' );
$so_nhat_ky = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . Viec::bang() . " WHERE loai = 'sua' AND doi_tuong_loai = 'post' AND doi_tuong_id = %d", $tp1 ) );
$chan_ghi = static function ( $kiem, $bai, $khoa ) use ( $tp1 ) { return $bai === $tp1 && $khoa === \Phongdao\MienDuLieu\Phan::khoa( 'y_nghia' ) ? false : $kiem; };
add_filter( 'update_post_metadata', $chan_ghi, 10, 3 );
$q_loi = QuyetViec::quyet( $v_loi, 'duyet', $ai_ql, true, 0 );
remove_filter( 'update_post_metadata', $chan_ghi, 10 );
$so_nhat_ky_sau = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . Viec::bang() . " WHERE loai = 'sua' AND doi_tuong_loai = 'post' AND doi_tuong_id = %d", $tp1 ) );
kt( 'ghi phần thất bại: giữ bài, việc chờ và nhật ký như trước', ! $q_loi['ok'] && $nghia_truoc === PhanBai::lay( $tp1, 'y_nghia' ) && Viec::CHO === Viec::lay( $v_loi )['trang_thai'] && 0 === Viec::lay( $v_loi )['phien'] && $so_nhat_ky === $so_nhat_ky_sau, array( 'q' => $q_loi, 'truoc' => $nghia_truoc, 'sau' => PhanBai::lay( $tp1, 'y_nghia' ), 'viec' => Viec::lay( $v_loi ), 'nhat_ky' => array( $so_nhat_ky, $so_nhat_ky_sau ) ) );
$v_loi_ban_chu = Viec::tao( array( 'loai' => 'nhap_y_nghia', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'phan' => 'y_nghia', 'noi_dung' => array( 'chu' => 'Ý nghĩa thử lỗi bản chữ.', 'ai' => false ), 'nguoi_tao' => 'kiem_thu' ) );
$noi_dung_truoc = get_post( $tp1 )->post_content;
$da_chan_ban_chu = false;
$chan_ban_chu = static function ( $sql ) use ( $wpdb, &$da_chan_ban_chu ) {
	if ( ! $da_chan_ban_chu && preg_match( '/^UPDATE\s+`?' . preg_quote( $wpdb->posts, '/' ) . '`?\s+SET\b/i', $sql ) ) {
		$da_chan_ban_chu = true;
		return 'CA_DAO_KIEM_THU_LOI_GHI_BAN_CHU';
	}
	return $sql;
};
add_filter( 'query', $chan_ban_chu );
$q_loi_ban_chu = QuyetViec::quyet( $v_loi_ban_chu, 'duyet', $ai_ql, true, 0 );
remove_filter( 'query', $chan_ban_chu );
kt( 'lỗi ghi bản chữ: huỷ phần bài, nhật ký và quyết thẻ cùng lượt', $da_chan_ban_chu && ! $q_loi_ban_chu['ok'] && $nghia_truoc === PhanBai::lay( $tp1, 'y_nghia' ) && $noi_dung_truoc === get_post( $tp1 )->post_content && Viec::CHO === Viec::lay( $v_loi_ban_chu )['trang_thai'] && $so_nhat_ky === (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . Viec::bang() . " WHERE loai = 'sua' AND doi_tuong_loai = 'post' AND doi_tuong_id = %d", $tp1 ) ), array( 'q' => $q_loi_ban_chu, 'da_chan' => $da_chan_ban_chu ) );
kt( 'người không có quyền duyệt không duyệt được nháp', ! QuyetViec::quyet( Viec::tao( array( 'loai' => 'nhap_y_nghia', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'phan' => 'y_nghia', 'noi_dung' => array( 'chu' => 'x' ), 'nguoi_tao' => 'may' ) ), 'duyet', $ai_ql, false )['ok'] );
$chan_cap_trung = static function ( $sql ) use ( $wpdb ) { return preg_match( '/^UPDATE\s+`?' . preg_quote( $wpdb->prefix . 'cadao_trung', '/' ) . '`?\s+SET\b/i', $sql ) ? 'CA_DAO_KIEM_THU_LOI_GHI_TRUNG' : $sql; };
add_filter( 'query', $chan_cap_trung );
$q_loi_trung = QuyetViec::quyet( $vgan[0]['id'], 'khac', $ai_ql, true, $vgan[0]['phien'] );
remove_filter( 'query', $chan_cap_trung );
kt( 'lỗi ghi quyết định cặp câu: thẻ vẫn chờ và chưa đổi cặp', ! $q_loi_trung['ok'] && Viec::CHO === Viec::lay( $vgan[0]['id'] )['trang_thai'] && ChongTrung::dang_cho( (int) $gan->id ), $q_loi_trung );
$vt_gan = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . ChongTrung::bang( 'van_tay' ) . ' WHERE muc = %s', 'kho:' . $gan->id ) );
$vt_bai = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . ChongTrung::bang( 'van_tay' ) . ' WHERE muc = %s', 'bai:' . $tp1 ) );
$loi_gop = array(
	'đổi câu kho'       => '/^UPDATE\s+`?' . preg_quote( Kho::bang(), '/' ) . '`?\s+SET\s+`?trang_thai/i',
	'chuyển câu liên đới' => '/^UPDATE\s+`?' . preg_quote( Kho::bang(), '/' ) . '`?\s+SET\s+`?gop_vao/i',
	'xoá vân tay câu kho' => '/^DELETE\s+FROM\s+`?' . preg_quote( ChongTrung::bang( 'van_tay' ), '/' ) . '`?\s+WHERE\b/i',
	'ghi cặp'          => '/^UPDATE\s+`?' . preg_quote( ChongTrung::bang( 'trung' ), '/' ) . '`?\s+SET\s+`?trang_thai/i',
	'đổi khoá chỉ mục bài' => '/^UPDATE\s+`?' . preg_quote( ChongTrung::bang( 'van_tay' ), '/' ) . '`?\s+SET\b(?=.*\bWHERE\b.*tam:)/is',
);
foreach ( $loi_gop as $buoc => $mau_sql ) {
	$da_chan = false;
	$chan_gop = static function ( $sql ) use ( $mau_sql, $buoc, &$da_chan ) {
		if ( ( ! $da_chan || 'đổi khoá chỉ mục bài' === $buoc ) && preg_match( $mau_sql, $sql ) ) {
			$da_chan = true;
			return 'CA_DAO_KIEM_THU_LOI_GOP_CAP_CAU';
		}
		return $sql;
	};
	add_filter( 'query', $chan_gop );
	$q_loi_gop = QuyetViec::quyet( $vgan[0]['id'], 'cung', $ai_ql, true, $vgan[0]['phien'] );
	remove_filter( 'query', $chan_gop );
	$gan_sau_loi = Kho::lay( (int) $gan->id );
	$vt_sau_loi = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . ChongTrung::bang( 'van_tay' ) . ' WHERE muc = %s', 'kho:' . $gan->id ) );
	$tam_sau_loi = (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . ChongTrung::bang( 'van_tay' ) . " WHERE muc LIKE 'tam:%' OR muc LIKE 'cu:%'" );
	kt( "lỗi $buoc khi gộp: thẻ, câu kho, cặp và chỉ mục cùng trở về trước", $da_chan && ! $q_loi_gop['ok'] && Viec::CHO === Viec::lay( $vgan[0]['id'] )['trang_thai'] && 0 === Viec::lay( $vgan[0]['id'] )['phien'] && 'moi' === $gan_sau_loi->trang_thai && '' === $gan_sau_loi->gop_vao && ChongTrung::dang_cho( (int) $gan->id ) && $vt_gan === $vt_sau_loi && $vt_bai === (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . ChongTrung::bang( 'van_tay' ) . ' WHERE muc = %s', 'bai:' . $tp1 ) ) && 0 === $tam_sau_loi, array( 'q' => $q_loi_gop, 'da_chan' => $da_chan, 'tam' => $tam_sau_loi ) );
}
foreach ( array(
	'ghi vân tay bài' => '/^INSERT\s+INTO\s+`?' . preg_quote( ChongTrung::bang( 'van_tay' ), '/' ) . '`?/i',
	'ghi cặp âm bài' => '/^INSERT\s+IGNORE\s+INTO\s+`?' . preg_quote( ChongTrung::bang( 'cap_am' ), '/' ) . '`?/i',
) as $buoc => $mau_sql ) {
	$da_chan = false;
	$chan_chi_muc = static function ( $sql ) use ( $mau_sql, &$da_chan ) {
		if ( preg_match( $mau_sql, $sql ) ) {
			$da_chan = true;
			return 'CA_DAO_KIEM_THU_LOI_CHI_MUC_GOP';
		}
		return $sql;
	};
	add_filter( 'query', $chan_chi_muc );
	$q_loi_chi_muc = QuyetViec::quyet( $vgan[0]['id'], 'cung', $ai_ql, true, $vgan[0]['phien'] );
	remove_filter( 'query', $chan_chi_muc );
	$m_sau = Kho::lay( (int) $gan->id );
	$v_sau = Viec::lay( $vgan[0]['id'] );
	$cho_sau = ChongTrung::dang_cho( (int) $gan->id );
	$vt_gan_sau = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . ChongTrung::bang( 'van_tay' ) . ' WHERE muc = %s', 'kho:' . $gan->id ) );
	$vt_bai_sau = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . ChongTrung::bang( 'van_tay' ) . ' WHERE muc = %s', 'bai:' . $tp1 ) );
	$tam_con = (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . ChongTrung::bang( 'van_tay' ) . " WHERE muc LIKE 'tam:%' OR muc LIKE 'cu:%'" );
	kt( "lỗi $buoc khi gộp: không xác nhận nửa chừng", $da_chan && ! $q_loi_chi_muc['ok'] && Viec::CHO === $v_sau['trang_thai'] && 0 === $v_sau['phien'] && 'moi' === $m_sau->trang_thai && '' === $m_sau->gop_vao && $cho_sau && $vt_gan === $vt_gan_sau && $vt_bai === $vt_bai_sau && 0 === $tam_con, array( 'q' => $q_loi_chi_muc, 'da_chan' => $da_chan, 'viec' => array( $v_sau['trang_thai'], $v_sau['phien'] ), 'kho' => array( $m_sau->trang_thai, $m_sau->gop_vao ), 'cho' => $cho_sau, 'van_tay_kho' => array( $vt_gan, $vt_gan_sau ), 'van_tay_bai' => array( $vt_bai, $vt_bai_sau ), 'tam' => $tam_con ) );
}
$q = QuyetViec::quyet( $vgan[0]['id'], 'khac', $ai_ql, true );
kt( 'quyết "Là bài khác": cặp ghi lại, câu kho hết chờ, việc đóng', $q['ok'] && ! ChongTrung::dang_cho( (int) $gan->id ) && array() === Viec::mo( 'kho', (int) $gan->id, 'co_the_trung' ) );
$bai_gioi_han = wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'draft', 'post_title' => 'Bài kiểm giới hạn chỉ mục' ) );
$vt_ngan = \Phongdao\MienDuLieu\VanTay::cua( 'Hai chữ' );
foreach ( array( 'tam:kiem', 'cu:kiem' ) as $muc_tam ) {
	for ( $phan_tam = 0; $phan_tam < 30; ++$phan_tam ) {
		$wpdb->insert( ChongTrung::bang( 'van_tay' ), array( 'muc' => $muc_tam, 'phan' => $phan_tam, 'vt1' => $vt_ngan['vt1'], 'vt2' => $vt_ngan['vt2'], 'so_am' => $vt_ngan['so_am'], 'so_dong' => $vt_ngan['so_dong'], 'so_cap' => $vt_ngan['so_cap'] ) );
	}
}
$ghi_gioi_han = ChongTrung::ghi( 'bai:' . $bai_gioi_han, array( 'Hai chữ' ) );
$so_tam_gioi_han = (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . ChongTrung::bang( 'van_tay' ) . " WHERE muc IN ('tam:kiem', 'cu:kiem')" );
$tim_gioi_han = array_column( ChongTrung::tim( 'Hai chữ' ), 'muc' );
kt( 'hàng chỉ mục tạm không chiếm giới hạn 50 kết quả và che bài thật', $ghi_gioi_han && 60 === $so_tam_gioi_han && in_array( 'bai:' . $bai_gioi_han, $tim_gioi_han, true ), array( 'so_tam' => $so_tam_gioi_han, 'tim' => $tim_gioi_han ) );
ChongTrung::xoa( 'tam:kiem' );
ChongTrung::xoa( 'cu:kiem' );
wp_delete_post( $bai_gioi_han, true );
$v_cd2 = Viec::mo( 'kho', (int) $h2->id, 'cau_moi' )[0] ?? null;
$so_bai_cd2 = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $wpdb->posts . ' WHERE post_type = %s', KhaiBao::TAC_PHAM ) );
$loi_dang = array(
	'ghi trạng thái câu kho' => '/^UPDATE\s+`?' . preg_quote( Kho::bang(), '/' ) . '`?\s+SET\s+`?trang_thai/i',
	'chuyển bài sang Đăng' => '/^UPDATE\s+`?' . preg_quote( $wpdb->posts, '/' ) . '`?\s+SET\b(?=.*\bpost_status\b)(?=.*\bpublish\b)/is',
);
foreach ( $loi_dang as $buoc => $mau_sql ) {
	$da_chan_dang = false;
	$chan_dang = static function ( $sql ) use ( $mau_sql, &$da_chan_dang ) {
		if ( ! $da_chan_dang && preg_match( $mau_sql, $sql ) ) {
			$da_chan_dang = true;
			return 'CA_DAO_KIEM_THU_LOI_DANG_CAU_KHO';
		}
		return $sql;
	};
	add_filter( 'query', $chan_dang );
	$q_loi_dang = $v_cd2 ? QuyetViec::quyet( $v_cd2['id'], 'dang', $ai_ql, true, $v_cd2['phien'] ) : array( 'ok' => true );
	remove_filter( 'query', $chan_dang );
	$kho_cd2 = Kho::lay( (int) $h2->id );
	$so_bai_sau = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $wpdb->posts . ' WHERE post_type = %s', KhaiBao::TAC_PHAM ) );
	kt( "lỗi $buoc khi Đăng: huỷ bài, kho và quyết thẻ", $da_chan_dang && ! $q_loi_dang['ok'] && 'moi' === $kho_cd2->trang_thai && 0 === (int) $kho_cd2->post_id && Viec::CHO === Viec::lay( $v_cd2['id'] )['trang_thai'] && 0 === Viec::lay( $v_cd2['id'] )['phien'] && $so_bai_cd2 === $so_bai_sau, array( 'q' => $q_loi_dang, 'da_chan' => $da_chan_dang ) );
}
$q     = $v_cd2 ? QuyetViec::quyet( $v_cd2['id'], 'sua', $ai_ql, true ) : array( 'ok' => false );
kt( 'quyết "Sửa rồi đăng" câu mới: tạo bản nháp, mở trang bài để sửa tiếp', $q['ok'] && 'draft' === get_post_status( (int) Kho::lay( (int) $h2->id )->post_id ) && str_contains( (string) $q['chuyen'], 'preview' ), $q );
// Gợi ý chủ đề, vùng (thẻ Hộp việc): máy gợi ý kèm lý do khi lưu lời, chú thích; Đồng ý gắn; Bỏ thì không gợi ý lại
kt( 'bộ vùng, chủ đề của dự án có sẵn (bước cài đặt 9)', (bool) term_exists( 'nam-bo', 'vung' ) && (bool) term_exists( 'hat-ru', 'chu_de' ) && (bool) term_exists( 'kn-thoi-tiet', 'chu_de' ) );
$nc_gy  = array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
$gy_bai = wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Ví dầu mẫu gợi ý', 'post_name' => 'vi-dau-mau-goi-y' ) );
wp_set_object_terms( $gy_bai, 'ca-dao', KhaiBao::THE_LOAI );
LuuPhan::luu( $gy_bai, 'loi', array( 'dong' => array( 'Ví dầu mẫu dòng một', 'Dòng hai mẫu gợi ý' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_gy );
LuuPhan::luu( $gy_bai, 'chu_thich', array( array( 'tu' => 'bến mẫu', 'loi_giai' => 'Bến sông mẫu ở Cần Thơ.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nc_gy );
$gy_v  = Viec::mo( 'post', $gy_bai, 'goi_y' );
$gy_ma = array_map( static fn( $v ) => $v['noi_dung']['loai'] . ':' . $v['noi_dung']['ma'], $gy_v );
kt( 'lưu lời, chú thích → thẻ gợi ý: Hát ru (lời có "ví dầu"), vùng Nam Bộ (chú thích nhắc Cần Thơ); mỗi gợi ý một thẻ', array( 'chu_de:hat-ru', 'vung:nam-bo' ) === $gy_ma, $gy_ma );
$the_gy = \Phongdao\GiaoTiep\BienTap\HopViec::the( $gy_v[1] ?? array( 'id' => 0, 'loai' => 'x' ) );
kt( 'thẻ gợi ý: "Vùng: Nam Bộ — tên bài", lời, lý do máy gợi ý, nút Đồng ý', str_contains( $the_gy, 'Vùng: Nam Bộ — Ví dầu mẫu gợi ý' ) && str_contains( $the_gy, 'Cần Thơ' ) && str_contains( $the_gy, 'Đồng ý' ), $the_gy );
$chan_dong_viec = static function ( $sql ) {
	return preg_match( '/^UPDATE\s+`?' . preg_quote( Viec::bang(), '/' ) . '`?\s+SET\s+trang_thai\s*=\s*[\'\"]xong[\'\"]/i', $sql ) ? 'CA_DAO_KIEM_THU_LOI_DONG_VIEC' : $sql;
};
add_filter( 'query', $chan_dong_viec );
$q_gy_loi = QuyetViec::quyet( (int) ( $gy_v[1]['id'] ?? 0 ), 'dong_y', $ai_ql, true, 0 );
remove_filter( 'query', $chan_dong_viec );
kt( 'gợi ý: lỗi đóng thẻ huỷ gắn vùng và dọn đệm phân loại', ! $q_gy_loi['ok'] && Viec::CHO === Viec::lay( $gy_v[1]['id'] )['trang_thai'] && 0 === Viec::lay( $gy_v[1]['id'] )['phien'] && ! has_term( 'nam-bo', 'vung', $gy_bai ), $q_gy_loi );
$q1 = QuyetViec::quyet( (int) ( $gy_v[1]['id'] ?? 0 ), 'dong_y', $ai_ql, true );
$q2 = QuyetViec::quyet( (int) ( $gy_v[0]['id'] ?? 0 ), 'bo', $ai_ql, true );
kt( 'Đồng ý → gắn vùng; Bỏ → không gắn; tính lại không tạo lại thẻ đã quyết', $q1['ok'] && $q2['ok'] && has_term( 'nam-bo', 'vung', $gy_bai ) && ! has_term( 'hat-ru', 'chu_de', $gy_bai ) && 0 === \Phongdao\DichVu\GoiY\GoiY::ghi( $gy_bai ) && array() === Viec::mo( 'post', $gy_bai, 'goi_y' ), array( $q1, $q2 ) );
$v_gy_sau = Viec::lay( (int) $gy_v[1]['id'] );
$_GET = array( 'da' => 'Đã gắn vùng.', 'hoan_viec' => (string) $v_gy_sau['id'], 'hoan_phien' => (string) $v_gy_sau['phien'] );
$h_gy_hoan = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
$_GET = $get_cu;
$q_phuc_gy = QuyetViec::hoan_tac( $v_gy_sau['id'], $v_gy_sau['phien'], $ai_ql );
kt( 'Đồng ý gợi ý: Hoàn tác có nút, gỡ đúng phân loại vừa gắn và mở lại thẻ', str_contains( $h_gy_hoan, 'Hoàn tác trong 10 giây' ) && $q_phuc_gy['ok'] && ! has_term( 'nam-bo', 'vung', $gy_bai ) && Viec::CHO === Viec::lay( $v_gy_sau['id'] )['trang_thai'] && 2 === Viec::lay( $v_gy_sau['id'] )['phien'], $q_phuc_gy );
$q_gy_lai = QuyetViec::quyet( $v_gy_sau['id'], 'dong_y', $ai_ql, true, 2 );
wp_set_object_terms( $gy_bai, array(), 'vung', false );
kt( 'phân loại bị sửa tiếp: không Hoàn tác gợi ý bằng bản cũ', $q_gy_lai['ok'] && ! QuyetViec::hoan_tac( $v_gy_sau['id'], 3, $ai_ql )['ok'] && Viec::XONG === Viec::lay( $v_gy_sau['id'] )['trang_thai'] );
$gy_bai2 = wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Ví dầu đã duyệt mẫu', 'post_name' => 'vi-dau-da-duyet-mau' ) );
wp_set_object_terms( $gy_bai2, 'ca-dao', KhaiBao::THE_LOAI );
Viec::tao( array( 'loai' => 'goi_y', 'trang_thai' => Viec::XONG, 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $gy_bai2, 'noi_dung' => array( 'loai' => '*', 'ma' => '*' ), 'nguoi_tao' => 'nhap_cu', 'quyet' => 'da_duyet_mvp' ) );
LuuPhan::luu( $gy_bai2, 'loi', array( 'dong' => array( 'Ví dầu mẫu đã duyệt', 'Dòng hai mẫu' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_gy );
kt( 'bài đã duyệt gợi ý trên bản cũ (việc đã quyết "*"): không gợi ý lại', array() === Viec::mo( 'post', $gy_bai2, 'goi_y' ) );
wp_delete_post( $gy_bai, true );
wp_delete_post( $gy_bai2, true );
$tp_get_cu = $_GET;
$_GET      = array();
$h         = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::hien();
kt( 'wp-admin Tác phẩm: có tab Bản nháp, lọc nhanh Thiếu ý nghĩa, ô tìm, bài đã đăng', str_contains( $h, 'Bản nháp' ) && str_contains( $h, 'Thiếu ý nghĩa' ) && str_contains( $h, 'name="q"' ) && str_contains( $h, esc_html( get_the_title( $tp1 ) ) ) );
$_GET = array( 'tt' => 'draft' );
$h    = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::hien();
$_GET = $tp_get_cu;
kt( 'wp-admin Tác phẩm, tab "Bản nháp": không có bài đã đăng', ! str_contains( $h, esc_html( get_the_title( $tp1 ) ) ) );
$them = ThemBai::tao( array( 'the_loai' => 'tuc-ngu', 'loi' => "Câu thêm tay mẫu một dòng\r\n", 'ten' => '', 'nguon' => 'https://example.org/sach-mau', 'dang' => false ), $ai_ql );
$pt   = PhanBai::lay( $them['bai'], 'loi' );
kt( 'Thêm bài: bản nháp, tên lấy dòng đầu, lời người viết, nguồn là đường dẫn người gõ', $them['ok'] && 'draft' === get_post_status( $them['bai'] ) && 'Câu thêm tay mẫu một dòng' === get_the_title( $them['bai'] ) && NguoiLam::NGUOI_VIET === $pt['nguoi_lam'] && 'https://example.org/sach-mau' === ( $pt['nguon']['url'] ?? '' ) && has_term( 'tuc-ngu', KhaiBao::THE_LOAI, $them['bai'] ), $them );
kt( 'Thêm bài: không có lời → báo, không tạo bài', ! ThemBai::tao( array( 'the_loai' => 'ca-dao', 'loi' => " \n ", 'ten' => '', 'nguon' => '', 'dang' => false ), $ai_ql )['ok'] );
$ds_get_cu = $_GET;
$ds_lop = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::class;
$_GET = array( 'q' => (string) $tp1, 'tt' => 'publish' );
$ds_bai = $ds_lop::hien();
kt( 'wp-admin Tác phẩm: tìm ID dẫn tới bài và việc admin, hiện trạng thái và bộ lọc có nhãn', str_contains( $ds_bai, 'name="page" value="phongdao-tac-pham"' ) && str_contains( $ds_bai, 'Tìm ID, tên hoặc lời' ) && str_contains( $ds_bai, 'data-nhan="ID">' . $tp1 ) && str_contains( $ds_bai, 'Đã đăng' ) && str_contains( $ds_bai, get_permalink( $tp1 ) ) && str_contains( $ds_bai, 'page=phongdao-hop-viec' ) && str_contains( $ds_bai, 'viec=' ), substr( $ds_bai, 0, 350 ) );
$_GET = array( 'q' => (string) $tp1, 'muc' => 'thieu_y_nghia' );
kt( 'wp-admin Tác phẩm: lọc thiếu ý nghĩa không trả bài đã có phần', str_contains( $ds_lop::hien(), '<strong>0</strong> tác phẩm khớp' ) );
$_GET = array( 'q' => (string) $tp1, 'the_loai' => 'khong-co-the-loai' );
$ds_sai_the_loai = $ds_lop::hien();
kt( 'wp-admin Tác phẩm: lọc thể loại loại bài không khớp', str_contains( $ds_sai_the_loai, '<strong>0</strong> tác phẩm khớp' ), substr( $ds_sai_the_loai, -350 ) );
$_GET = array( 'q' => (string) $tp1, 'nguon' => 'may:me-thu' );
kt( 'wp-admin Tác phẩm: lọc theo nguồn máy đã lưu', str_contains( $ds_lop::hien(), 'data-nhan="ID">' . $tp1 ) );
$_GET = array( 'q' => (string) $them['bai'], 'tt' => 'draft', 'nguon' => 'nguoi' );
kt( 'wp-admin Tác phẩm: lọc nguồn người điền, không nhầm nguồn máy', str_contains( $ds_lop::hien(), 'data-nhan="ID">' . $them['bai'] ) );
wp_set_current_user( 0 );
$_GET = array( 'q' => (string) $tp1 );
kt( 'wp-admin Tác phẩm: khách không đọc được danh sách', ! str_contains( $ds_lop::hien(), 'data-nhan="ID">' ) );
wp_set_current_user( $ai_ql );
$ds_dem_goc = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $wpdb->posts . ' WHERE post_type = %s AND post_status = %s', KhaiBao::TAC_PHAM, 'draft' ) );
$ds_ids = array();
for ( $i = 0; $i < 1005; ++$i ) {
	$wpdb->insert( $wpdb->posts, array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'draft', 'post_title' => '[KIEM-THU-DS] ' . ( 1004 === $i ? 'Tên riêng 1004' : $i ), 'post_content' => 1004 === $i ? 'Cụm kiểm lời riêng 1004' : 'Dữ liệu thử danh sách', 'post_date' => current_time( 'mysql' ), 'post_modified' => current_time( 'mysql' ) ) );
	$ds_ids[] = (int) $wpdb->insert_id;
}
wp_cache_flush();
$_GET = array( 'tt' => 'draft' );
$ds_truy_van_truoc = $wpdb->num_queries;
$ds_nhieu = $ds_lop::hien();
$ds_truy_van = $wpdb->num_queries - $ds_truy_van_truoc;
kt( 'wp-admin Tác phẩm: hơn 1.000 bài vẫn đếm thật, chỉ tải 30 dòng và có trang sau', str_contains( $ds_nhieu, '<strong>' . ( $ds_dem_goc + 1005 ) . '</strong> tác phẩm khớp' ) && 30 === substr_count( $ds_nhieu, 'data-nhan="ID"' ) && str_contains( $ds_nhieu, 'Trang sau' ), array( 'truy_van' => $ds_truy_van, 'can' => $ds_dem_goc + 1005, 'tong' => preg_match( '#<strong>(\d+)</strong> tác phẩm khớp#', $ds_nhieu, $m_dsn ) ? $m_dsn[1] : '?', 'id' => substr_count( $ds_nhieu, 'data-nhan="ID"' ), 'sau' => str_contains( $ds_nhieu, 'Trang sau' ) ) );
kt( 'wp-admin Tác phẩm: số truy vấn không tăng theo 30 dòng', $ds_truy_van <= 50, $ds_truy_van );
$_GET = array( 'tt' => 'draft', 'paged' => '34' );
kt( 'wp-admin Tác phẩm: bài sau mốc 1.000 còn trong trang phân trang', str_contains( $ds_lop::hien(), 'Trang 34 /' ) );
$_GET = array( 'tt' => 'draft', 'q' => (string) $ds_ids[0] );
kt( 'wp-admin Tác phẩm: tìm ID của bài thử trong kho lớn', str_contains( $ds_lop::hien(), 'data-nhan="ID">' . $ds_ids[0] ) );
$_GET = array( 'tt' => 'draft', 'q' => 'Tên riêng 1004' );
kt( 'wp-admin Tác phẩm: tìm theo tên trong kho lớn', str_contains( $ds_lop::hien(), 'data-nhan="ID">' . $ds_ids[1004] ) );
$_GET = array( 'tt' => 'draft', 'q' => 'Cụm kiểm lời riêng 1004' );
kt( 'wp-admin Tác phẩm: tìm theo lời trong kho lớn', str_contains( $ds_lop::hien(), 'data-nhan="ID">' . $ds_ids[1004] ) );
$wpdb->update( $wpdb->posts, array( 'post_status' => 'trash' ), array( 'ID' => $ds_ids[0] ) );
wp_cache_flush();
$_GET = array( 'tt' => 'trash', 'q' => (string) $ds_ids[0] );
kt( 'wp-admin Tác phẩm: thùng rác tìm được bài nhưng không mở sửa thô', str_contains( $ds_lop::hien(), 'data-nhan="ID">' . $ds_ids[0] ) && str_contains( $ds_lop::hien(), 'Thùng rác' ) );
$_GET = array( 'viec' => (string) $v_hoan_de_sau );
kt( 'liên kết thẻ từ danh sách mở đúng thẻ trong Hộp việc', str_contains( \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien(), 'id="viec-' . $v_hoan_de_sau . '"' ) );
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->posts . ' WHERE post_type = %s AND post_title LIKE %s', KhaiBao::TAC_PHAM, '[KIEM-THU-DS]%' ) );
wp_cache_flush();
$_GET = $ds_get_cu;
$kt = ThemBai::kiem( "Dòng một mẫu\nDòng hai mẫu" );
kt( 'Thêm bài: máy nhận thể thơ, báo trùng y hệt với tác phẩm có sẵn (kèm đường dẫn)', 2 === $kt['so_dong'] && '' !== $kt['the_tho'] && 'Trùng y hệt' === ( $kt['trung'][0]['muc'] ?? '' ) && (string) get_permalink( $tp1 ) === ( $kt['trung'][0]['url'] ?? '' ), $kt );
$h = \Phongdao\GiaoTiep\QuanTri\ThemTacPham::hien();
kt( 'wp-admin Thêm tác phẩm: form gửi admin-post có nonce, 5 thể loại, Lưu nháp / Đăng ngay', str_contains( $h, 'data-them-bai' ) && str_contains( $h, 'name="_wpnonce"' ) && 5 === substr_count( $h, 'name="the_loai"' ) && str_contains( $h, 'Đăng ngay' ) );

// ---------- Tuyển tập, Từ điển, Nguồn ----------
wp_set_current_user( 0 );
$tc = \Phongdao\DichVu\DungTrang\GieoMau::gieo_tra_cuu( $ai_ql );
$tt = get_post( $tc['tuyen_tap'] );
kt( 'tuyển tập mẫu loại Tình huống: có lời dẫn, đủ ngưỡng 2 câu → công khai', $tt && \Phongdao\GiaoTiep\Khoi\TuyenTap::cong_khai( $tt ) );
$h = \Phongdao\GiaoTiep\Khoi\TuyenTap::hien( $tt );
kt( 'tuyển tập: nút Trình chiếu cả bộ (hành động chính; ẩn khi không có JavaScript)', str_contains( $h, 'data-action="present-set" data-chi-js hidden' ) );
kt( 'trang tuyển tập: lời dẫn, số câu, mỗi câu có lời, ghi chú "Hợp khi", nút sao chép, Xem bài', str_contains( $h, 'Tuyển tập · Tình huống' ) && str_contains( $h, 'Lời dẫn tổng hợp' ) && substr_count( $h, 'class="collection-item' ) >= 5 && str_contains( $h, 'Hợp khi:' ) && str_contains( $h, 'data-action="copy-loi"' ) && str_contains( $h, 'Xem bài' ), $h );
$tt2 = wp_insert_post( array( 'post_type' => KhaiBao::TUYEN_TAP, 'post_status' => 'publish', 'post_title' => 'Tuyển tập thiếu', 'post_name' => 'tuyen-tap-thieu' ) );
LuuPhan::luu( $tt2, 'muc', array( array( 'tac_pham_id' => $tp1 ) ), array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI ) );
kt( 'tuyển tập thiếu lời dẫn hoặc chưa đủ 8 câu: chưa công khai, không có trong danh mục', ! \Phongdao\GiaoTiep\Khoi\TuyenTap::cong_khai( get_post( $tt2 ) ) && ! str_contains( \Phongdao\GiaoTiep\Khoi\TuyenTap::danh_muc( '' ), 'Tuyển tập thiếu' ) );
$h = \Phongdao\GiaoTiep\Khoi\TuyenTap::danh_muc( '' );
kt( 'danh mục tuyển tập: lọc theo loại bằng đường dẫn, thẻ tuyển tập công khai', str_contains( $h, 'Gửi một lời nhắn (mẫu)' ) && str_contains( $h, '?loai=dip' ) && str_contains( $h, 'Cho một tình huống' ) );
kt( 'danh mục lọc loại chưa có tuyển tập: trạng thái trống có lối sang kho', str_contains( \Phongdao\GiaoTiep\Khoi\TuyenTap::danh_muc( 'dip' ), 'Chưa có tuyển tập ở đây' ) );
$h = \Phongdao\GiaoTiep\Khoi\TuDien::hien( '', '' );
kt( 'Từ điển: form tìm GET, 29 chữ cái, mục từ hay gặp có đường dẫn /tu-dien/<tên>/', str_contains( $h, 'name="q"' ) && 29 === substr_count( $h, 'name="chu"' ) && str_contains( $h, '/tu-dien/chut/' ) && str_contains( $h, 'Hay gặp trong lời' ), $h );
kt( 'Từ điển: tìm không dấu ra mục từ; theo chữ cái C ra "chút"; chữ cái khác thì trống có lối sang Tìm', str_contains( \Phongdao\GiaoTiep\Khoi\TuDien::hien( 'chut', '' ), '/tu-dien/chut/' ) && str_contains( \Phongdao\GiaoTiep\Khoi\TuDien::hien( '', 'C' ), '/tu-dien/chut/' ) && str_contains( \Phongdao\GiaoTiep\Khoi\TuDien::hien( '', 'Ư' ), 'Chưa có mục từ phù hợp' ) );
kt( 'chữ cái đầu tiếng Việt: bỏ dấu thanh, giữ ă â ê ô ơ ư đ', array( 'A', 'Ă', 'Â', 'Đ', 'Ơ', 'U' ) === array_map( array( \Phongdao\GiaoTiep\Khoi\TuDien::class, 'chu_dau' ), array( 'ánh', 'ắt', 'ấm', 'đò', 'ở', 'ủ' ) ) );
$h = do_shortcode( '[cadao_nguon]' );
kt( 'mã ngắn [cadao_nguon] của trang Nguồn (mang từ MVP) hiện danh sách nguồn, không còn chữ mã thô', ! str_contains( $h, '[cadao_nguon]' ) && '' !== trim( wp_strip_all_tags( $h ) ), $h );

// ---------- Người đọc góp ----------
wp_set_current_user( 0 );
$_SERVER['REMOTE_ADDR'] = '203.0.113.5';
$bai_g = $mau_id['dong-mau-mot-cua-bai'];
$NG    = \Phongdao\DichVu\NguoiDoc\NhanGui::class;
$g1    = $NG::luu( array( 'loai' => 'di_ban', 'bai' => $bai_g, 'noi_dung' => "Dòng quê mẫu một\nDòng quê mẫu hai", 'noi_nghe' => 'Làng mẫu', 'dong_y' => '1' ) );
$gy    = get_post( $g1['id'] );
kt( 'góp "ở quê nói khác" → góp ý chờ duyệt, khoá meta như MVP, thẻ Người đọc gửi trong Hộp việc', $g1['ok'] && $gy && 'gop_y' === $gy->post_type && 'pending' === $gy->post_status && 'di_ban' === get_post_meta( $gy->ID, '_cadao_loai', true ) && $bai_g === (int) get_post_meta( $gy->ID, '_cadao_bai', true ) && 1 === count( Viec::mo( 'post', $gy->ID, 'nguoi_doc' ) ), $g1 );
$g2 = $NG::luu( array( 'loai' => 'cam_nghi', 'bai' => $bai_g, 'noi_dung' => 'Cảm nghĩ mẫu của bạn đọc.', 'ten' => 'Bút danh mẫu', 'dong_y' => '1' ) );
$bl = get_comment( $g2['id'] );
kt( 'cảm nghĩ → bình luận chờ duyệt, không ghi địa chỉ mạng, trình duyệt, email', $g2['ok'] && $bl && '0' === (string) $bl->comment_approved && '' === $bl->comment_author_IP && '' === $bl->comment_agent && '' === $bl->comment_author_email && 'Bút danh mẫu' === $bl->comment_author, $bl );
kt( 'bài cảm nhận ngắn quá → chưa gửi; đủ dài → bài chờ duyệt', 'thieu_pt' === $NG::luu( array( 'loai' => 'phan_tich', 'bai' => $bai_g, 'tieu_de' => 'T', 'noi_dung' => 'ngắn', 'tu_viet' => 1, 'dong_y' => 1 ) )['ma'] && 'pending' === get_post_status( $NG::luu( array( 'loai' => 'phan_tich', 'bai' => $bai_g, 'tieu_de' => 'Cảm nhận mẫu', 'noi_dung' => str_repeat( 'Chữ cái mẫu ', 30 ), 'tu_viet' => 1, 'dong_y' => 1 ) )['id'] ) );
kt( 'ô bẫy có chữ → không lưu', 'bay' === $NG::luu( array( 'loai' => 'bao_loi', 'noi_dung' => 'x', 'dong_y' => 1, 'web' => 'http://rac' ) )['ma'] );
$NG::luu( array( 'loai' => 'bao_loi', 'noi_dung' => 'Lỗi mẫu bốn', 'dong_y' => 1 ) );
$NG::luu( array( 'loai' => 'bao_loi', 'noi_dung' => 'Lỗi mẫu năm', 'dong_y' => 1 ) );
kt( 'quá 5 lần gửi trong giờ từ một địa chỉ → chặn; địa chỉ khác vẫn gửi được', 'nhieu' === $NG::luu( array( 'loai' => 'bao_loi', 'noi_dung' => 'Lỗi mẫu sáu', 'dong_y' => 1 ) )['ma'] && ( $_SERVER['REMOTE_ADDR'] = '203.0.113.6' ) && $NG::luu( array( 'loai' => 'bao_loi', 'noi_dung' => 'Lỗi mẫu khác máy', 'dong_y' => 1 ) )['ok'] );
kt( 'đường bình luận mặc định của WordPress đóng với tác phẩm (cảm nghĩ chỉ qua form của thư viện)', ! comments_open( $bai_g ) );
$h = \Phongdao\GiaoTiep\Khoi\GopTuLieu::hien( 'so_trang', $bai_g, '', '', 0 );
kt( 'trang Góp: form gửi admin-post có nonce, chọn sẵn loại, ô bẫy, thông báo riêng tư, có Cảm nghĩ khi gắn bài', str_contains( $h, 'name="_wpnonce"' ) && str_contains( $h, 'value="so_trang" required checked' ) && str_contains( $h, 'name="web"' ) && str_contains( $h, 'không lưu địa chỉ mạng' ) && str_contains( $h, 'value="cam_nghi"' ) && ! str_contains( $h, 'value="vi_pham"' ), $h );
kt( 'trang Góp không gắn bài: không có Cảm nghĩ; lời báo lỗi tra theo mã (mã lạ → lời chung)', ! str_contains( \Phongdao\GiaoTiep\Khoi\GopTuLieu::hien( '', 0, '', '', 0 ), 'value="cam_nghi"' ) && str_contains( \Phongdao\GiaoTiep\Khoi\GopTuLieu::hien( '', 0, 'loi', '<b>tu-che</b>', 0 ), 'Chưa gửi được, xin thử lại.' ) );
$h = TacPham::hien( (array) \Phongdao\DichVu\DungTrang\DocBai::tac_pham( get_post( $bai_g ) ) );
kt( 'trang bài: khối "Bạn muốn góp gì?" có 6 lối góp; cảm nghĩ chưa duyệt không hiện', 6 === substr_count( $h, 'class="gop-nut"' ) && ! str_contains( $h, 'Cảm nghĩ mẫu của bạn đọc.' ) );
wp_set_current_user( $ai_ql );
$v_bl = Viec::mo( 'comment', $g2['id'], 'nguoi_doc' )[0] ?? array();
$hv   = \Phongdao\GiaoTiep\BienTap\HopViec::the( $v_bl );
$hv2  = \Phongdao\GiaoTiep\BienTap\HopViec::the( Viec::mo( 'post', $gy->ID, 'nguoi_doc' )[0] ?? array( 'id' => 0, 'loai' => 'x' ) );
kt( 'Hộp việc: thẻ cảm nghĩ có "Hiện trên trang"; thẻ góp ý có "Đã xử lý", không có "Hiện"', str_contains( $hv, 'Hiện trên trang' ) && str_contains( $hv, 'Cảm nghĩ mẫu của bạn đọc.' ) && str_contains( $hv2, 'Đã xử lý' ) && ! str_contains( $hv2, 'Hiện trên trang' ), array( $hv, $hv2 ) );
$bl_truoc = get_comment( $g2['id'] )->comment_approved;
add_filter( 'query', $chan_dong_viec );
$q_bl_loi = QuyetViec::quyet( (int) $v_bl['id'], 'duyet', $ai_ql, true, 0 );
remove_filter( 'query', $chan_dong_viec );
kt( 'cảm nghĩ: lỗi đóng thẻ huỷ công khai và dọn đệm bình luận', ! $q_bl_loi['ok'] && $bl_truoc === get_comment( $g2['id'] )->comment_approved && Viec::CHO === Viec::lay( $v_bl['id'] )['trang_thai'] && 0 === Viec::lay( $v_bl['id'] )['phien'], $q_bl_loi );
$kq = \Phongdao\DichVu\XepViec\QuyetViec::quyet( (int) $v_bl['id'], 'duyet', $ai_ql, true );
wp_set_current_user( 0 );
$h = TacPham::hien( (array) \Phongdao\DichVu\DungTrang\DocBai::tac_pham( get_post( $bai_g ) ) );
kt( 'người biên soạn bấm Hiện → cảm nghĩ lên trang kèm tên hiển thị và "Báo vi phạm"', $kq['ok'] && str_contains( $h, 'Cảm nghĩ mẫu của bạn đọc.' ) && str_contains( $h, 'Bút danh mẫu' ) && str_contains( $h, 'loai=vi_pham' ), $kq );
// Cách hiểu của bạn đọc (D-105): form nhanh trên trang → Hộp việc → người bấm Hiện mới lên tầng Hiểu thêm
$_SERVER['REMOTE_ADDR'] = '203.0.113.9';
$ch_thieu = $NG::luu( array( 'loai' => 'cach_hieu', 'bai' => $bai_g, 'noi_dung' => 'Cách hiểu chưa cam kết.', 'dong_y' => '1' ) );
$ch_ko_bai = \Phongdao\MienDuLieu\GopNguoiDoc::kiem( array( 'loai' => 'cach_hieu', 'noi_dung' => 'x', 'tu_viet' => '1', 'dong_y' => '1' ) )[0];
$ch = $NG::luu( array( 'loai' => 'cach_hieu', 'bai' => $bai_g, 'noi_dung' => str_repeat( 'Cách hiểu mẫu của bạn đọc. ', 60 ), 'ten' => 'Bút danh cách hiểu', 'tu_viet' => '1', 'dong_y' => '1', 'email' => 'ai@example.org' ) );
$ch_bl = get_comment( $ch['id'] );
$v_ch  = Viec::mo( 'comment', $ch['id'], 'nguoi_doc' )[0] ?? array();
kt(
	'cách hiểu: thiếu cam kết tự viết → chưa gửi; không gắn bài → chưa gửi; đủ → bình luận loại cach_hieu chờ duyệt, cắt ở 1.000 ký tự, chỉ giữ bút danh (không email, địa chỉ mạng, trình duyệt), thẻ Người đọc gửi loại Cách hiểu',
	'thieu_ch' === $ch_thieu['ma'] && 'bai' === $ch_ko_bai && $ch['ok'] && $ch_bl && 'cach_hieu' === $ch_bl->comment_type && '0' === (string) $ch_bl->comment_approved && 1000 === mb_strlen( $ch_bl->comment_content ) && '' === $ch_bl->comment_author_email && '' === $ch_bl->comment_author_IP && '' === $ch_bl->comment_agent && 'Bút danh cách hiểu' === $ch_bl->comment_author && 'cach_hieu' === ( $v_ch['noi_dung']['loai'] ?? '' ),
	array( $ch_thieu, $ch_ko_bai, $ch )
);
kt( 'trang Góp không có loại Cách hiểu (chỉ gửi bằng form nhanh trên trang bài)', ! str_contains( \Phongdao\GiaoTiep\Khoi\GopTuLieu::hien( '', $bai_g, '', '', 0 ), 'value="cach_hieu"' ) );
$h = TacPham::hien( (array) \Phongdao\DichVu\DungTrang\DocBai::tac_pham( get_post( $bai_g ) ) );
kt( 'cách hiểu chưa duyệt không hiện trên trang', ! str_contains( $h, 'Cách hiểu mẫu của bạn đọc.' ) );
wp_set_current_user( $ai_ql );
$hv_ch = \Phongdao\GiaoTiep\BienTap\HopViec::the( $v_ch );
kt( 'Hộp việc: thẻ "Cách hiểu" có "Hiện trên trang"; nhắc muốn dùng làm gốc cho Ý nghĩa thì tự viết lại và ghi công (máy không chép)', str_contains( $hv_ch, 'Cách hiểu — ' ) && str_contains( $hv_ch, 'Hiện trên trang' ) && str_contains( $hv_ch, 'Cách hiểu mẫu của bạn đọc.' ) && str_contains( $hv_ch, 'có tham khảo cách hiểu của bạn đọc Bút danh cách hiểu' ) && str_contains( $hv_ch, 'page=phongdao-sua-tac-pham' ), $hv_ch );
$cap_ch    = static fn(): ?int => \Phongdao\MienDuLieu\CapChatLuong::tinh( PhanBai::tat_ca( $bai_g, KhaiBao::TAC_PHAM ), \Phongdao\DichVu\DungTrang\DocBai::the_loai( get_post( $bai_g ) ) );
$cap_truoc = $cap_ch();
$yn_truoc  = PhanBai::lay( $bai_g, 'y_nghia' );
$kq_ch     = QuyetViec::quyet( (int) $v_ch['id'], 'duyet', $ai_ql, true );
wp_set_current_user( 0 );
$h    = TacPham::hien( (array) \Phongdao\DichVu\DungTrang\DocBai::tac_pham( get_post( $bai_g ) ) );
$h_ht = (string) strstr( (string) strstr( $h, 'id="hieu-them"' ), 'id="doc-them"', true );
kt(
	'bấm Hiện → cách hiểu lên tầng Hiểu thêm (dưới Ý nghĩa), ghi bút danh và ngày, có "Báo vi phạm"; không lẫn vào Ý nghĩa hay Cảm nghĩ; Ý nghĩa và cấp chất lượng không đổi',
	$kq_ch['ok'] && str_contains( $h_ht, 'Cách hiểu mẫu của bạn đọc.' ) && str_contains( $h_ht, 'Bút danh cách hiểu · ' ) && str_contains( $h_ht, 'vi_pham=' . $ch['id'] ) && str_contains( $h_ht, 'Cách hiểu của bạn đọc <span class="ht-so">1</span>' )
	&& ! str_contains( (string) strstr( $h, 'id="hieu-them"', true ), 'Cách hiểu mẫu' ) && ! in_array( (int) $ch['id'], array_column( \Phongdao\DichVu\DungTrang\DocBai::cam_nghi( $bai_g ), 'id' ), true ) && $cap_truoc === $cap_ch() && $yn_truoc === PhanBai::lay( $bai_g, 'y_nghia' ),
	$kq_ch
);
// Người duyệt sửa chữ và tên hiển thị của cách hiểu / cảm nghĩ ngay trên thẻ trước khi hiện; bản người đọc gửi giữ trong nhật ký
$_SERVER['REMOTE_ADDR'] = '203.0.113.10';
wp_set_current_user( 0 );
$ch2   = $NG::luu( array( 'loai' => 'cach_hieu', 'bai' => $bai_g, 'noi_dung' => 'Cach hieu go sai chinh ta, so dien thoai 0900000000.', 'ten' => 'Ten that Nguyen Van A', 'tu_viet' => '1', 'dong_y' => '1' ) );
$cn2   = $NG::luu( array( 'loai' => 'cam_nghi', 'bai' => $bai_g, 'noi_dung' => 'Cảm nghĩ thử để sửa tên.', 'ten' => 'Tên thật cần bỏ', 'dong_y' => '1' ) );
$v_ch2 = Viec::mo( 'comment', $ch2['id'], 'nguoi_doc' )[0] ?? array();
$v_cn2 = Viec::mo( 'comment', $cn2['id'], 'nguoi_doc' )[0] ?? array();
wp_set_current_user( $chi_soan );
$the_cs2 = \Phongdao\GiaoTiep\BienTap\HopViec::the( $v_ch2 );
wp_set_current_user( $ai_ql );
$the_ch2 = \Phongdao\GiaoTiep\BienTap\HopViec::the( $v_ch2 );
$kq_ch2  = QuyetViec::quyet( (int) $v_ch2['id'], 'duyet', $ai_ql, true, -1, 'Cách hiểu đã sửa chính tả, bỏ số điện thoại.', 'Bạn đọc ở Huế' );
$kq_cn2  = QuyetViec::quyet( (int) $v_cn2['id'], 'duyet', $ai_ql, true, -1, '', '   ' );
$bl_ch2  = get_comment( $ch2['id'] );
$bl_cn2  = get_comment( $cn2['id'] );
$tv_ch2  = Viec::lay( (int) $v_ch2['id'] );
wp_set_current_user( 0 );
$h_sua_nd = TacPham::hien( (array) \Phongdao\DichVu\DungTrang\DocBai::tac_pham( get_post( $bai_g ) ) );
kt(
	'Hộp việc: người có quyền duyệt sửa chữ và tên hiển thị của cách hiểu / cảm nghĩ ngay trên thẻ rồi bấm Hiện (tên để trống → "Một bạn đọc"); trang chỉ hiện bản đã sửa; bản người đọc gửi giữ trong nhật ký của thẻ; người không có quyền duyệt không có ô sửa',
	str_contains( $the_ch2, 'name="chu" form="hv-form-' . $v_ch2['id'] . '"' ) && str_contains( $the_ch2, 'name="ten" form="hv-form-' . $v_ch2['id'] . '" maxlength="80" value="Ten that Nguyen Van A"' ) && ! str_contains( $the_cs2, 'name="ten" form=' )
	&& $kq_ch2['ok'] && str_contains( $kq_ch2['thong_bao'], 'bản người đọc gửi giữ trong nhật ký' ) && 'Cách hiểu đã sửa chính tả, bỏ số điện thoại.' === $bl_ch2->comment_content && 'Bạn đọc ở Huế' === $bl_ch2->comment_author && '1' === (string) $bl_ch2->comment_approved && 'cach_hieu' === $bl_ch2->comment_type
	&& 'Cach hieu go sai chinh ta, so dien thoai 0900000000.' === ( $tv_ch2['truoc']['goc']['chu'] ?? '' ) && 'Ten that Nguyen Van A' === ( $tv_ch2['truoc']['goc']['ten'] ?? '' )
	&& $kq_cn2['ok'] && 'Một bạn đọc' === $bl_cn2->comment_author && 'Cảm nghĩ thử để sửa tên.' === $bl_cn2->comment_content
	&& str_contains( $h_sua_nd, 'Cách hiểu đã sửa chính tả' ) && str_contains( $h_sua_nd, 'Bạn đọc ở Huế' ) && ! str_contains( $h_sua_nd, '0900000000' ) && ! str_contains( $h_sua_nd, 'Ten that Nguyen Van A' ) && ! str_contains( $h_sua_nd, 'Tên thật cần bỏ' ),
	array( $kq_ch2, $kq_cn2, $tv_ch2['truoc'] ?? null )
);
// Người đọc gửi (T-177.11): gửi lại cùng nội dung không thành hai bản; lỗi tạo thẻ không để nội dung nằm ngoài Hộp việc;
// nội dung đã đổi ở màn khác của WordPress thì thẻ không làm ngược (không kéo từ thùng rác ra đăng)
$_SERVER['REMOTE_ADDR'] = '203.0.113.7';
$dem_gop  = static fn(): int => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", KhaiBao::GOP_Y ) );
$gop_lap  = array( 'loai' => 'bao_loi', 'bai' => $bai_g, 'noi_dung' => 'Lỗi mẫu gửi hai lần', 'dong_y' => '1' );
$gop_truoc = $dem_gop();
$lap_1    = $NG::luu( $gop_lap );
$lap_2    = $NG::luu( $gop_lap );
kt( 'người đọc bấm gửi hai lần cùng nội dung → cả hai lần báo đã gửi, chỉ một bài góp và một thẻ', $lap_1['ok'] && $lap_2['ok'] && $lap_1['id'] === $lap_2['id'] && $gop_truoc + 1 === $dem_gop() && 1 === count( Viec::mo( 'post', $lap_1['id'], 'nguoi_doc' ) ), array( $lap_1, $lap_2 ) );
$chan_tao_viec = static fn( $sql ) => preg_match( '/^INSERT\s+INTO\s+`?' . preg_quote( Viec::bang(), '/' ) . '`?\s/i', $sql ) ? 'CA_DAO_KIEM_THU_LOI_TAO_VIEC' : $sql;
$gop_loi       = array( 'loai' => 'bao_loi', 'bai' => $bai_g, 'noi_dung' => 'Lỗi mẫu khi thẻ chưa ghi được', 'dong_y' => '1' );
$gop_truoc     = $dem_gop();
add_filter( 'query', $chan_tao_viec );
$loi_tao = $NG::luu( $gop_loi );
remove_filter( 'query', $chan_tao_viec );
$gop_giua = $dem_gop();
$lai_tao  = $NG::luu( $gop_loi );
kt( 'lỗi tạo thẻ Người đọc gửi → báo chưa gửi được, không còn bài góp nằm ngoài Hộp việc; gửi lại thì vào đủ bài và thẻ', ! $loi_tao['ok'] && 'loi' === $loi_tao['ma'] && $gop_truoc === $gop_giua && $lai_tao['ok'] && $gop_truoc + 1 === $dem_gop() && 1 === count( Viec::mo( 'post', $lai_tao['id'], 'nguoi_doc' ) ), array( $loi_tao, $gop_truoc, $gop_giua, $lai_tao ) );
$_SERVER['REMOTE_ADDR'] = '203.0.113.8';
$nd_the   = static fn( string $loai, int $id ): array => Viec::mo( $loai, $id, 'nguoi_doc' )[0] ?? array( 'id' => 0 );
$c_duyet  = $NG::luu( array( 'loai' => 'cam_nghi', 'bai' => $bai_g, 'noi_dung' => 'Cảm nghĩ mẫu đã duyệt ở màn Bình luận.', 'dong_y' => '1' ) );
$c_rac    = $NG::luu( array( 'loai' => 'cam_nghi', 'bai' => $bai_g, 'noi_dung' => 'Cảm nghĩ mẫu đã vào thùng rác ở màn khác.', 'dong_y' => '1' ) );
$p_rac    = $NG::luu( array( 'loai' => 'phan_tich', 'bai' => $bai_g, 'tieu_de' => 'Cảm nhận mẫu vào thùng rác', 'noi_dung' => str_repeat( 'Chữ mẫu thùng rác ', 30 ), 'tu_viet' => 1, 'dong_y' => 1 ) );
$g_xoa    = $NG::luu( array( 'loai' => 'bao_loi', 'bai' => $bai_g, 'noi_dung' => 'Lỗi mẫu đã bị xoá ở màn khác', 'dong_y' => '1' ) );
$v_cd     = $nd_the( 'comment', $c_duyet['id'] );
$v_cr     = $nd_the( 'comment', $c_rac['id'] );
$v_pr     = $nd_the( 'post', $p_rac['id'] );
$v_gx     = $nd_the( 'post', $g_xoa['id'] );
wp_set_current_user( $ai_ql );
// Đổi trạng thái bình luận ngoài màn Bình luận (thẳng trong CSDL, không qua móc của WordPress): Hộp việc vẫn phải tự vệ.
// Đổi ở màn Bình luận thì thẻ tự đóng — kiểm riêng ở dưới (báo cáo trinh sát lần 2, mục 1.10).
$doi_bl = static function ( int $id, string $tt ): void {
	global $wpdb;
	$wpdb->update( $wpdb->comments, array( 'comment_approved' => $tt ), array( 'comment_ID' => $id ) );
	clean_comment_cache( $id );
};
$doi_bl( (int) $c_duyet['id'], '1' );
$doi_bl( (int) $c_rac['id'], 'trash' );
wp_trash_post( $p_rac['id'] );
wp_delete_post( $g_xoa['id'], true );
$q_cd  = QuyetViec::quyet( (int) $v_cd['id'], 'duyet', $ai_ql, true, 0 );
$q_cr  = QuyetViec::quyet( (int) $v_cr['id'], 'duyet', $ai_ql, true, 0 );
$q_pr  = QuyetViec::quyet( (int) $v_pr['id'], 'duyet', $ai_ql, true, 0 );
$q_gx  = QuyetViec::quyet( (int) $v_gx['id'], 'xong', $ai_ql, true, 0 );
kt( 'cảm nghĩ đã duyệt ở nơi khác (không qua màn Bình luận) → bấm Hiện chỉ đóng thẻ, báo đã ở trạng thái này', $q_cd['ok'] && str_contains( $q_cd['thong_bao'], 'từ trước' ) && Viec::XONG === Viec::lay( $v_cd['id'] )['trang_thai'] && '1' === (string) get_comment( $c_duyet['id'] )->comment_approved, $q_cd );
kt( 'cảm nghĩ, bài cảm nhận đã vào thùng rác ở màn khác → bấm Hiện bị từ chối, không tự lấy ra đăng, thẻ vẫn chờ', ! $q_cr['ok'] && str_contains( implode( ' ', $q_cr['loi'] ), 'thùng rác' ) && 'trash' === (string) get_comment( $c_rac['id'] )->comment_approved && Viec::CHO === Viec::lay( $v_cr['id'] )['trang_thai'] && ! $q_pr['ok'] && 'trash' === get_post_status( $p_rac['id'] ) && Viec::CHO === Viec::lay( $v_pr['id'] )['trang_thai'], array( $q_cr, $q_pr ) );
$q_cr2 = QuyetViec::quyet( (int) $v_cr['id'], 'bo', $ai_ql, true, Viec::lay( $v_cr['id'] )['phien'] );
$q_gx2 = QuyetViec::quyet( (int) $v_gx['id'], 'bo', $ai_ql, true, Viec::lay( $v_gx['id'] )['phien'] );
kt( 'nội dung đã bị xoá ở màn khác → Đã xử lý báo rõ, Bỏ đóng thẻ; cảm nghĩ trong thùng rác → Bỏ chỉ đóng thẻ', ! $q_gx['ok'] && str_contains( implode( ' ', $q_gx['loi'] ), 'đã bị xoá' ) && $q_gx2['ok'] && Viec::BO === Viec::lay( $v_gx['id'] )['trang_thai'] && $q_cr2['ok'] && Viec::BO === Viec::lay( $v_cr['id'] )['trang_thai'] && 'trash' === (string) get_comment( $c_rac['id'] )->comment_approved, array( $q_gx, $q_gx2, $q_cr2 ) );
$q_cu  = QuyetViec::quyet( (int) $v_cd['id'], 'bo', $ai_ql, true, 0 );
kt( 'màn cũ (phiên đã qua) bấm Bỏ cảm nghĩ vừa đóng thẻ → bị chặn, cảm nghĩ vẫn hiện', ! $q_cu['ok'] && '1' === (string) get_comment( $c_duyet['id'] )->comment_approved, $q_cu );
wp_set_comment_status( $c_duyet['id'], 'trash' );
// Bình luận và Hộp việc khớp nhau: duyệt hay bỏ cảm nghĩ ở màn Bình luận thì thẻ tự đóng, ghi "Đã xử lý ở Bình luận"
$_SERVER['REMOTE_ADDR'] = '203.0.113.31';
$c_bl1 = $NG::luu( array( 'loai' => 'cam_nghi', 'bai' => $bai_g, 'noi_dung' => 'Cảm nghĩ mẫu duyệt ở màn Bình luận.', 'dong_y' => '1' ) );
$_SERVER['REMOTE_ADDR'] = '203.0.113.32';
$c_bl2 = $NG::luu( array( 'loai' => 'cam_nghi', 'bai' => $bai_g, 'noi_dung' => 'Cảm nghĩ mẫu đánh dấu tin rác ở màn Bình luận.', 'dong_y' => '1' ) );
$_SERVER['REMOTE_ADDR'] = '203.0.113.33';
$c_bl3 = $NG::luu( array( 'loai' => 'cam_nghi', 'bai' => $bai_g, 'noi_dung' => 'Cảm nghĩ mẫu đang được quyết ở Hộp việc.', 'dong_y' => '1' ) );
$v_bl1 = $nd_the( 'comment', $c_bl1['id'] );
kt( 'ba cảm nghĩ thử (Bình luận ↔ Hộp việc) gửi được, mỗi cảm nghĩ một thẻ', $c_bl1['ok'] && $c_bl2['ok'] && $c_bl3['ok'] && $c_bl1['id'] && $c_bl2['id'] && $c_bl3['id'], array( $c_bl1, $c_bl2, $c_bl3 ) );
if ( $c_bl1['id'] && $c_bl2['id'] && $c_bl3['id'] ) {
	$v_bl2 = $nd_the( 'comment', $c_bl2['id'] );
	$v_bl3 = $nd_the( 'comment', $c_bl3['id'] );
	wp_set_comment_status( $c_bl1['id'], 'approve' );
	wp_set_comment_status( $c_bl2['id'], 'spam' );
	Viec::giu( (int) $v_bl3['id'], (int) $v_bl3['phien'] );
	wp_set_comment_status( $c_bl3['id'], 'approve' );
	$s_bl1 = Viec::lay( (int) $v_bl1['id'] );
	$s_bl2 = Viec::lay( (int) $v_bl2['id'] );
	$s_bl3 = Viec::lay( (int) $v_bl3['id'] );
	$q_bl1 = QuyetViec::quyet( (int) $v_bl1['id'], 'bo', $ai_ql, true, (int) $v_bl1['phien'] );
	$_GET  = array( 'page' => \Phongdao\GiaoTiep\QuanTri\DanhSachViec::TRANG, 'tt' => 'tat_ca', 'viec' => (string) $v_bl1['id'] );
	$h_bl1 = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
	$_GET  = array();
	kt( 'duyệt / đánh tin rác một cảm nghĩ ở màn Bình luận → thẻ Hộp việc tự đóng, ghi "Đã xử lý ở Bình luận" và người bấm; thẻ đang được quyết ở Hộp việc không bị đụng; màn cũ không quyết chồng', Viec::XONG === $s_bl1['trang_thai'] && 'binh_luan' === $s_bl1['quyet'] && $ai_ql === (int) $s_bl1['nguoi_quyet'] && Viec::XONG === $s_bl2['trang_thai'] && 'binh_luan' === $s_bl2['quyet'] && 'dang_lam' === $s_bl3['trang_thai'] && ! $q_bl1['ok'] && '1' === (string) get_comment( $c_bl1['id'] )->comment_approved && str_contains( $h_bl1, 'Đã xử lý ở Bình luận' ), array( $s_bl1, $s_bl2, $s_bl3, $q_bl1 ) );
	foreach ( array( $c_bl1, $c_bl2, $c_bl3 ) as $c_x ) {
		wp_delete_comment( (int) $c_x['id'], true );
	}
	Viec::xoa( (int) $v_bl3['id'] );
}
wp_set_current_user( 0 );
kt( 'mã ngắn [cadao_viet_phan_tich] của trang "Viết cảm nhận" (mang từ MVP) hiện form với loại bài cảm nhận chọn sẵn', str_contains( do_shortcode( '[cadao_viet_phan_tich]' ), 'value="phan_tich" required checked' ) );

// ---------- Đã lưu, bài cảm nhận ----------
$h = do_blocks( '<!-- wp:cadao/da-luu /-->' );
kt( 'trang Đã lưu: khung cho danh sách trên máy người đọc, báo khi tắt JavaScript, lọc 5 thể loại; mã trang chỉ nạp ở trang này', str_contains( $h, 'data-da-luu' ) && str_contains( $h, '<noscript>' ) && 6 === substr_count( $h, '<option' ) && wp_script_is( 'cadao-da-luu-view-script', 'enqueued' ), $h );
$vj = (string) file_get_contents( WP_PLUGIN_DIR . '/ca-dao/khoi/da-luu/view.js' );
kt( 'Đã lưu đọc khoá "cadao-da-luu" của MVP (bài lưu trước khi chuyển vẫn còn), không gọi máy chủ, không dùng innerHTML', str_contains( $vj, "'cadao-da-luu'" ) && ! preg_match( '/fetch\(|XMLHttpRequest|sendBeacon|innerHTML|document\.cookie/', $vj ) );
$pt = get_post( $tc2 = \Phongdao\DichVu\DungTrang\GieoMau::gieo_tra_cuu( $ai_ql )['phan_tich'] );
$h  = \Phongdao\GiaoTiep\Khoi\PhanTich::hien( $pt );
kt( 'trang bài cảm nhận: người viết (bút danh · vai), nội dung có trích lời, tác phẩm đang bàn, Báo vi phạm', str_contains( $h, 'Bút danh mẫu · Giáo viên' ) && str_contains( $h, '<blockquote' ) && str_contains( $h, 'Tác phẩm đang bàn' ) && str_contains( $h, 'loai=vi_pham' ), $h );
$h = TacPham::hien( (array) \Phongdao\DichVu\DungTrang\DocBai::tac_pham( get_post( $bai_g ) ) );
$bd = \Phongdao\GiaoTiep\Khoi\BanDo::diem();
kt( 'Bản đồ: địa danh đã xác nhận có toạ độ trong khung, có bài đang đăng; toạ độ đổi thành vị trí trên ảnh', 1 === count( $bd ) && 'bến Mẫu Bắc' === $bd[0]['muc']->name && $bd[0]['x'] > 0 && $bd[0]['x'] < 100 && $bd[0]['y'] > 0 && $bd[0]['y'] < 100, $bd );
$toa_do_day = array();
for ( $i = 0; $i < 20; $i++ ) {
	$toa_do_day[] = array( 'x' => 3 + ( $i % 5 ) * 7, 'y' => 4 + intdiv( $i, 5 ) * 7 );
}
$nhom_day = \Phongdao\GiaoTiep\Khoi\BanDo::nhom_diem( $toa_do_day );
$tach_vung_cham = true;
foreach ( $nhom_day as $i => $a ) {
	foreach ( array_slice( $nhom_day, $i + 1 ) as $b ) {
		if ( abs( $a['x'] - $b['x'] ) * 2.56 < 44 && abs( $a['y'] - $b['y'] ) * 2.56 < 44 ) {
			$tach_vung_cham = false;
		}
	}
}
kt( 'Bản đồ dày điểm ở màn 320 px: không mất điểm, vùng chạm 44 px không chồng, ở trong mép ảnh', $tach_vung_cham && 20 === array_sum( array_map( static fn( $n ) => count( $n['ds'] ), $nhom_day ) ) && 10 <= min( array_column( $nhom_day, 'x' ) ) && 10 <= min( array_column( $nhom_day, 'y' ) ) && 90 >= max( array_column( $nhom_day, 'x' ) ) && 90 >= max( array_column( $nhom_day, 'y' ) ), $nhom_day );
$dd2 = (int) wp_insert_term( 'nơi ngoài khung', 'muc_tu' )['term_id'];
wp_set_object_terms( $mau_id['ve-mau'], $dd2, 'muc_tu', true );
update_term_meta( $dd2, '_phongdao_wikidata', array( 'wd' => 'Q00', 'toa_do' => array( 48.8, 2.3 ) ) );
// Trang lối đọc của menu Khám phá (từng 404 trên site thật): có đường dẫn, mẫu theme, nội dung; liên kết tới trang chưa có thì ẩn
$KP = \Phongdao\GiaoTiep\Khoi\KhamPha::class;
$thieu_kp = array_values( array_filter( array( 'kham-pha', 'chu-de', 'mo-tip', 'ngau-nhien', 'hay-bi-nham' ), static fn( $m ) => ! isset( \Phongdao\GiaoTiep\TrangCoDinh\Module::TRANG[ $m ] ) || ! file_exists( get_theme_file_path( 'templates/trang-' . $m . '.html' ) ) ) );
kt( 'Khám phá, Chủ đề, Mô-típ, Một câu ngẫu nhiên, Hay bị nhầm: có đường dẫn và mẫu theme', array() === $thieu_kp, $thieu_kp );
$h_kp = $KP::hien( 'kham-pha' );
kt( 'trang Khám phá: ô tìm gửi /tim/, lối đọc tới Chủ đề, Mô-típ, Một câu ngẫu nhiên', str_contains( $h_kp, 'Bạn muốn đọc gì?' ) && str_contains( $h_kp, 'action="' . esc_url( home_url( '/tim/' ) ) . '"' ) && str_contains( $h_kp, home_url( '/chu-de/' ) ) && str_contains( $h_kp, home_url( '/mo-tip/' ) ) && str_contains( $h_kp, home_url( '/ngau-nhien/' ) ), substr( $h_kp, 0, 600 ) );
kt( 'trang Chủ đề liệt kê chủ đề có bài, kèm số bài', str_contains( $KP::hien( 'chu-de' ), 'Lao động' ) && str_contains( $KP::hien( 'chu-de' ), (string) get_term_link( 'lao-dong', 'chu_de' ) ) );
$h_nn = $KP::hien( 'ngau-nhien' );
kt( 'Một câu ngẫu nhiên: một thẻ bài, "Câu khác", "Xem đầy đủ"', str_contains( $h_nn, 'class="card' ) && str_contains( $h_nn, 'Câu khác' ) && str_contains( $h_nn, 'Xem đầy đủ' ), substr( $h_nn, 0, 400 ) );
kt( 'Hay bị nhầm dưới 5 câu: không công khai, menu không có liên kết', ! $KP::hay_bi_nham_cong_khai() && ! \Phongdao\GiaoTiep\Khoi\Ve::co( 'hay-bi-nham' ) && ! str_contains( \Phongdao\GiaoTiep\Khoi\KhungTrang::dau_trang(), '/hay-bi-nham/' ) );
$rt_truoc = \Phongdao\GiaoTiep\Khoi\Ve::co( 'rieng-tu' ) || str_contains( \Phongdao\GiaoTiep\Khoi\KhungTrang::chan_trang(), '/quyen-rieng-tu/' ) || str_contains( $h_kp, '/quyen-rieng-tu/' );
$rt_id    = wp_insert_post( array( 'post_type' => 'page', 'post_name' => 'quyen-rieng-tu', 'post_title' => 'Quyền riêng tư', 'post_status' => 'publish', 'post_content' => 'Trang thử.' ) );
$rt_sau   = \Phongdao\GiaoTiep\Khoi\Ve::co( 'rieng-tu' ) && str_contains( \Phongdao\GiaoTiep\Khoi\KhungTrang::chan_trang(), '/quyen-rieng-tu/' );
wp_delete_post( $rt_id, true );
kt( 'chưa có trang Quyền riêng tư: chân trang, Khám phá không trỏ tới; đăng trang rồi thì có', ! $rt_truoc && $rt_sau );
$hbd = \Phongdao\GiaoTiep\Khoi\BanDo::hien();
kt( 'Bản đồ: điểm ngoài khung Việt Nam bị bỏ; ảnh bản đồ tự lưu có Hoàng Sa, Trường Sa; điểm là liên kết tới danh sách; ghi rõ "nhắc đến", không phải nơi lưu truyền', 1 === count( \Phongdao\GiaoTiep\Khoi\BanDo::diem() ) && str_contains( $hbd, 'ban-do-vn.svg' ) && str_contains( $hbd, 'Hoàng Sa và Trường Sa' ) && str_contains( $hbd, 'href="#dd-' ) && str_contains( $hbd, 'không đồng nghĩa là nơi lưu truyền' ) && ! preg_match( '#https?://(?!' . preg_quote( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '#' ) . ')#', str_replace( 'https://www.w3.org', '', $hbd ) ), $hbd );
kt( 'Bản đồ: liên kết điểm có vùng chạm 44 px, chấm nhìn thấy giữ 28 px', str_contains( $hbd, 'width:44px;height:44px' ) && str_contains( $hbd, 'width:28px;height:28px' ) );

// Mục từ, địa danh (như MVP): ảnh Commons có ghi công, bản đồ nhỏ, Wikipedia; Từ khó nối Từ điển; ảnh đáp án câu đố
$DD    = \Phongdao\GiaoTiep\Khoi\DiaDanh::class;
$t_bmb = get_term_by( 'slug', 'ben-mau-bac', 'muc_tu' );
$q_bmb = new WP_Query( array( 'muc_tu' => 'ben-mau-bac' ) );
$h_bmb = \Phongdao\GiaoTiep\Khoi\DanhSach::hien( $q_bmb );
kt( 'trang mục từ địa danh đã xác nhận: bản đồ nhỏ có chấm, liên kết bản đồ lớn', str_contains( $h_bmb, 'class="ban-do-nho"' ) && str_contains( $h_bmb, 'Mở bản đồ lớn' ) && str_contains( $h_bmb, 'ban-do-vn.svg' ), substr( $h_bmb, 0, 600 ) );
$anh_wd = array( 'anh_da_tai' => array( 'url' => home_url( '/wp-content/uploads/mau.jpg' ), 'tac_gia' => 'Tác giả mẫu', 'giay_phep' => 'CC BY-SA 4.0', 'giay_phep_url' => 'https://creativecommons.org/licenses/by-sa/4.0/', 'trang' => 'https://commons.wikimedia.org/wiki/File:Mau.jpg' ), 'nhan_wd' => 'bến Mẫu Bắc', 'wiki' => 'https://vi.wikipedia.org/wiki/Mau' );
$h_anh  = $DD::html( $anh_wd );
kt( 'ảnh Commons: ảnh trên site, ghi "Ảnh: tác giả · giấy phép · Wikimedia Commons", có Wikipedia; không ảnh thì không chừa chỗ', str_contains( $h_anh, 'src="' . home_url( '/wp-content/uploads/mau.jpg' ) ) && str_contains( $h_anh, 'Ảnh: Tác giả mẫu · ' ) && str_contains( $h_anh, 'Wikimedia Commons</a>' ) && str_contains( $h_anh, 'Đọc thêm trên Wikipedia' ) && '' === $DD::html( array() ), $h_anh );
$kich_da_xin = array();
$ban_anh_thu = static function ( $cu, $id, $kich ) use ( &$kich_da_xin ) {
	if ( 424242 !== $id ) {
		return $cu;
	}
	$kich_da_xin[] = $kich;
	return array( home_url( '/wp-content/uploads/mau-' . ( 'thumbnail' === $kich ? '150x150' : '300x225' ) . '.jpg' ), 'thumbnail' === $kich ? 150 : 300, 'thumbnail' === $kich ? 150 : 225, true );
};
add_filter( 'image_downsize', $ban_anh_thu, 10, 3 );
$anh_co_id = $anh_wd;
$anh_co_id['anh_da_tai']['id'] = 424242;
$h_anh_bai = $DD::html( $anh_co_id, 'thumbnail' );
$h_anh_muc = $DD::html( $anh_co_id );
remove_filter( 'image_downsize', $ban_anh_thu, 10 );
kt( 'ảnh đã tải: bài dùng cỡ nhỏ, trang mục từ dùng cỡ vừa, giữ nguyên dòng ghi công', array( 'thumbnail', 'medium' ) === $kich_da_xin && str_contains( $h_anh_bai, '/mau-150x150.jpg' ) && str_contains( $h_anh_muc, '/mau-300x225.jpg' ) && str_contains( $h_anh_bai, 'Wikimedia Commons</a>' ) && ! str_contains( $h_anh_bai, 'src="' . $anh_wd['anh_da_tai']['url'] ), array( 'bai' => $h_anh_bai, 'muc' => $h_anh_muc, 'kich' => $kich_da_xin ) );
$wd_cu = get_term_meta( $t_bmb->term_id, '_phongdao_wikidata', true );
update_term_meta( $t_bmb->term_id, '_phongdao_wikidata', $anh_co_id + (array) $wd_cu );
update_term_meta( $t_bmb->term_id, '_phongdao_anh', array( 'an' => true ) );
kt( 'người biên soạn ẩn ảnh Wikidata: không hiện ảnh, vẫn có bản đồ', ! str_contains( $DD::html( (array) $DD::cua_muc_tu( $t_bmb->term_id ) ), '<figure' ) && str_contains( $DD::html( (array) $DD::cua_muc_tu( $t_bmb->term_id ) ), 'ban-do-nho' ) );
delete_term_meta( $t_bmb->term_id, '_phongdao_anh' );
LuuPhan::luu( $mau_id['dong-dao-mau'], 'chu_thich', array( array( 'tu' => 'bến Mẫu Bắc', 'loi_giai' => 'Địa danh tổng hợp ở phía bắc để xem bản đồ.', 'muc_tu_id' => (int) $t_bmb->term_id, 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true ) );
add_filter( 'image_downsize', $ban_anh_thu, 10, 3 );
$tp_dd = \Phongdao\DichVu\DungTrang\DocBai::tac_pham( get_post( $mau_id['dong-dao-mau'] ) );
$h_tk  = \Phongdao\GiaoTiep\Khoi\TacPham::hien( (array) $tp_dd );
kt( 'Từ khó nối mục từ: ảnh nhỏ và ghi công, liên kết Từ điển và bản đồ', str_contains( $h_tk, 'Xem trong Từ điển · ' ) && str_contains( $h_tk, (string) get_term_link( $t_bmb ) ) && str_contains( $h_tk, '/mau-150x150.jpg' ) && str_contains( $h_tk, 'Tác giả mẫu' ) && str_contains( $h_tk, 'ban-do-nho' ), substr( (string) strstr( $h_tk, 'id="chu-thich"' ), 0, 900 ) );
update_term_meta( $t_bmb->term_id, '_phongdao_wikidata', $wd_cu );
Viec::tao( array( 'loai' => 'wikidata', 'trang_thai' => Viec::XONG, 'quyet' => 'dung', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => 0, 'noi_dung' => array( 'khoa' => 'dap:' . md5( \Phongdao\MienDuLieu\VanTay::chuan_loi( 'Đáp án mẫu' ) ), 'nhan' => 'đáp án mẫu', 'wd' => 'Q0', 'du_lieu' => $anh_co_id + array( 'toa_do' => array( 21.0, 105.8 ) ) ), 'nguoi_tao' => 'nhap_cu' ) );
$tp_do = \Phongdao\DichVu\DungTrang\DocBai::tac_pham( get_post( $mau_id['cau-do-mau'] ) );
kt( 'đáp án câu đố đã xác nhận trên Wikidata: ảnh nhỏ và ghi công trong khối đáp án, không bản đồ', str_contains( (string) $tp_do['dap_an_them'], '/mau-150x150.jpg' ) && str_contains( (string) $tp_do['dap_an_them'], 'Tác giả mẫu' ) && ! str_contains( (string) $tp_do['dap_an_them'], 'ban-do-nho' ) && str_contains( \Phongdao\GiaoTiep\Khoi\TacPham::hien( (array) $tp_do ), 'Tác giả mẫu' ), $tp_do['dap_an_them'] ?? null );
remove_filter( 'image_downsize', $ban_anh_thu, 10 );
kt( 'nút Nghe giọng máy (D-095) trên thẻ lời: ẩn sẵn, chỉ hiện khi máy có giọng tiếng Việt', str_contains( $h_tk, 'data-action="nghe" aria-pressed="false" hidden' ) );
// Sửa mục từ trên trang mục từ (thay trang Sửa mục từ của MVP)
$SMT = \Phongdao\GiaoTiep\BienTap\SuaMucTu::class;
$u_cu = get_current_user_id();
wp_set_current_user( 0 );
$smt_khach = $SMT::form( $t_bmb );
wp_set_current_user( $ai_ql );
kt( 'khối Sửa mục từ chỉ người biên soạn thấy', '' === $smt_khach && str_contains( $SMT::form( $t_bmb ), 'id="sua-muc-tu"' ) && str_contains( $SMT::form( $t_bmb ), 'name="giai"' ) );
$hs_smt = \Phongdao\KhoLuu\HoSo::theo_khoa( 'sach-mau' );
$bao1   = $SMT::luu( $t_bmb->term_id, array( 'giai' => 'Lời giải người biên soạn viết lại.', 'ho_so' => (string) ( $hs_smt ? $hs_smt->ID : 0 ), 'url' => '', 'anh' => '', 'ghi_cong' => '', 'an' => '1' ), $ai_ql );
$lg_smt = get_term_meta( $t_bmb->term_id, '_phongdao_loi_giai', true );
clean_term_cache( $t_bmb->term_id, 'muc_tu' );
kt( 'sửa lời giải → người viết, nguồn người điền, ẩn ảnh Wikidata; ghi lịch sử', 'Đã lưu mục từ.' === $bao1 && 'Lời giải người biên soạn viết lại.' === get_term( $t_bmb->term_id )->description && 'nguoi_viet' === ( $lg_smt['nguoi_lam'] ?? '' ) && ( $hs_smt ? $hs_smt->ID : 0 ) === ( $lg_smt['nguon']['ho_so'] ?? 0 ) && ! empty( get_term_meta( $t_bmb->term_id, '_phongdao_anh', true )['an'] ) && 1 === count( Viec::lich_su( 'term', $t_bmb->term_id ) ), array( $bao1, $lg_smt ) );
kt( 'gửi lại không đổi gì: không ghi; ảnh riêng không phải ảnh trong Thư viện ảnh: báo, không lưu', 'Không có gì thay đổi.' === $SMT::luu( $t_bmb->term_id, array( 'giai' => 'Lời giải người biên soạn viết lại.', 'ho_so' => (string) ( $hs_smt ? $hs_smt->ID : 0 ), 'an' => '1' ), $ai_ql ) && str_starts_with( $SMT::luu( $t_bmb->term_id, array( 'giai' => 'x', 'anh' => 'https://example.org/la.jpg' ), $ai_ql ), 'Chưa lưu' ) && 'Lời giải người biên soạn viết lại.' === get_term( $t_bmb->term_id )->description );
$h_smt = \Phongdao\GiaoTiep\Khoi\DanhSach::hien( new WP_Query( array( 'muc_tu' => 'ben-mau-bac' ) ) );
kt( 'trang mục từ: nguồn lời giải người điền hiện; ảnh Wikidata đã ẩn', ! $hs_smt || str_contains( $h_smt, 'Nguồn lời giải: ' . $hs_smt->post_title ), substr( $h_smt, 0, 500 ) );
wp_set_current_user( $u_cu );
kt( 'trang bài: tầng Hiểu thêm, phần Bài cảm nhận liệt kê bài đã duyệt kèm tên, hai dòng đầu, người viết (bài chờ duyệt thì không)', str_contains( $h, '<h3 class="ht-ten">Bài cảm nhận <span class="ht-so">' ) && str_contains( $h, 'Một cách đọc bài mẫu</a></h4><p class="ht-tom">' ) && ! str_contains( $h, 'Cảm nhận mẫu</a>' ) );

// ---------- Đo lường, an toàn ----------
wp_set_current_user( 0 );
$_SERVER['REMOTE_ADDR'] = '203.0.113.20';
$rest = static function ( string $duong, array $tham, string $ua = 'Mozilla/5.0 (iPhone)' ) {
	$yc = new WP_REST_Request( 'POST', '/phongdao/v1' . $duong );
	$yc->set_header( 'user_agent', $ua );
	$yc->set_body_params( $tham );
	return rest_do_request( $yc )->get_data();
};
$dl1 = $mau_id['ve-mau'];
kt( 'lượt đọc: lần đầu trong ngày tính, gửi lại không tính, máy dò không tính; chỉ ghi tên miền giới thiệu', true === ( $rest( '/luot-doc/' . $dl1, array( 'tu' => 'www.facebook.com' ) )['tinh'] ?? null ) && false === ( $rest( '/luot-doc/' . $dl1, array() )['tinh'] ?? null ) && false === ( $rest( '/luot-doc/' . $mau_id['cau-do-mau'], array(), 'Googlebot/2.1' )['tinh'] ?? null ) && 1 === \Phongdao\DichVu\DoLuong\DoLuong::so_lieu( $dl1 )['doc'] && isset( \Phongdao\DichVu\DoLuong\DoLuong::nhieu_nhat( 'gioi_thieu' )['facebook.com'] ) );
kt( 'sự kiện: sao chép trích dẫn ghi theo bài + kiểu; kiểu lạ, bài không phải tác phẩm → không ghi', true === ( $rest( '/su-kien', array( 'loai' => 'trich_dan_sao_chep', 'bai' => $dl1, 'kieu' => 'apa' ) )['ghi'] ?? null ) && false === ( $rest( '/su-kien', array( 'loai' => 'trich_dan_sao_chep', 'bai' => $dl1, 'kieu' => 'x' ) )['ghi'] ?? null ) && false === ( $rest( '/su-kien', array( 'loai' => 'chia_se', 'bai' => $tt2, 'kieu' => 'web_share' ) )['ghi'] ?? null ) && 1 === \Phongdao\DichVu\DoLuong\DoLuong::so_lieu( $dl1 )['trich'] );
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone)'; // không có trình duyệt = máy dò, không ghi
\Phongdao\DichVu\DoLuong\Module::tim_khong_ra( 'câu chưa có mẫu' );
\Phongdao\DichVu\DoLuong\Module::tim_khong_ra( 'ten@mien.vn' );
kt( 'tìm không ra: ghi chuỗi tìm (bỏ chuỗi giống email) để biết nên biên soạn câu nào', array( 'câu chưa có mẫu' ) === array_keys( \Phongdao\DichVu\DoLuong\DoLuong::nhieu_nhat( 'tim_khong_ket_qua' ) ) );
$rest( '/luot-doc/' . $mau_id['dong-dao-mau'], array() );
$rest( '/luot-doc/' . $mau_id['cau-do-mau'], array() );
kt( 'trang chủ: "Đọc nhiều tuần này" hiện khi đã có từ 3 bài được đọc trong 7 ngày', str_contains( do_blocks( '<!-- wp:cadao/trang-chu /-->' ), 'Đọc nhiều tuần này' ) );
kt( 'Số liệu tuần: lượt đọc 7 ngày, bài đọc nhiều, người đọc đến từ, câu tìm chưa có', str_contains( $h_sl = \Phongdao\GiaoTiep\BienTap\SoLieuTuan::doc_7_ngay( 3 ), 'Lượt đọc 7 ngày: 3' ) && str_contains( $h_sl, 'facebook.com' ) && str_contains( $h_sl, 'câu chưa có mẫu' ), $h_sl );
\Phongdao\DichVu\DoLuong\Module::nap();
kt( 'mã đo lường nạp cho khách; mã đọc trang (doc.js) không gọi máy chủ, chỉ phát sự kiện trong trang', wp_script_is( 'phongdao-do-luong', 'enqueued' ) && ! preg_match( '/fetch\(|sendBeacon|XMLHttpRequest/', (string) file_get_contents( get_theme_file_path( 'assets/doc.js' ) ) ) && str_contains( (string) file_get_contents( get_theme_file_path( 'assets/doc.js' ) ), 'phongdao:su-kien' ) );
for ( $i = 0; $i < 5; $i++ ) {
	\Phongdao\HaTang\AnToan::ghi_sai( 'admin' );
}
$kq_dn = \Phongdao\HaTang\AnToan::xac_thuc( null, 'admin' );
delete_transient( \Phongdao\HaTang\AnToan::khoa( 'admin' ) );
kt( 'đăng nhập sai 5 lần → tạm khoá 15 phút (đếm theo tài khoản, không theo địa chỉ mạng); sai mật khẩu → câu báo chung', is_wp_error( $kq_dn ) && 'cadao_tam_khoa' === $kq_dn->get_error_code() && 'cadao_dang_nhap_sai' === \Phongdao\HaTang\AnToan::xac_thuc( new WP_Error( 'incorrect_password', 'x' ), 'admin' )->get_error_code() );
kt( 'XML-RPC tắt; khách không thấy danh sách người dùng qua REST', ! apply_filters( 'xmlrpc_enabled', true ) && ! array_filter( array_keys( rest_get_server()->get_routes() ), static fn( $r ) => str_starts_with( $r, '/wp/v2/users' ) ) );

$dau_kh = apply_filters( 'wp_headers', array(), $GLOBALS['wp'] );
wp_set_current_user( $ai_ql );
$dau_bt = apply_filters( 'wp_headers', array(), $GLOBALS['wp'] );
wp_set_current_user( 0 );
$csp = (string) ( $dau_kh['Content-Security-Policy'] ?? '' );
kt(
	'đầu trả lời: Referrer-Policy, nosniff; khách có CSP không cho mã nội tuyến (chỉ đoạn sáng / tối của theme theo dấu băm), không máy chủ ngoài trừ Turnstile; người biên soạn không nhận CSP',
	'strict-origin-when-cross-origin' === ( $dau_kh['Referrer-Policy'] ?? '' ) && 'nosniff' === ( $dau_kh['X-Content-Type-Options'] ?? '' ) && str_contains( $csp, "script-src 'self'" ) && ! str_contains( $csp, "'unsafe-inline' https" ) && ! preg_match( "/script-src[^;]*'unsafe-inline'/", $csp )
		&& str_contains( $csp, "'sha256-" . base64_encode( hash( 'sha256', PHONGDAO_MA_GIAO_DIEN, true ) ) . "'" ) && str_contains( $csp, "object-src 'none'" ) && ! isset( $dau_bt['Content-Security-Policy'] ) && isset( $dau_bt['Referrer-Policy'] ),
	$csp
);

// ---------- SEO ----------
$SEO  = \Phongdao\GiaoTiep\Seo\Module::class;
$l2   = $mau_id['dong-mau-mot-cua-bai'];
$xem  = static function ( array $q ): void { // giả lập trang đang xem
	$GLOBALS['wp_query']     = new WP_Query( $q );
	$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
};
kt( 'tác phẩm có Ý nghĩa là L2 (lập chỉ mục); chưa có ý nghĩa thì không', $SEO::l2( $l2 ) && ! $SEO::l2( $mau_id['ve-mau'] ) && in_array( $l2, $SEO::ds_l2(), true ) && ! in_array( $mau_id['ve-mau'], $SEO::ds_l2(), true ) );
$xem( array( 'p' => $mau_id['ve-mau'], 'post_type' => KhaiBao::TAC_PHAM ) );
$r_ko = $SEO::robots( array() );
$xem( array( 'p' => $l2, 'post_type' => KhaiBao::TAC_PHAM ) );
$r_co = $SEO::robots( array() );
$td   = $SEO::tieu_de( array( 'title' => 'x' ) );
kt( 'tác phẩm chưa L2 → noindex, follow; L2 → lập chỉ mục, tiêu đề "— nghĩa và cách dùng"', ! empty( $r_ko['noindex'] ) && empty( $r_co['noindex'] ) && str_ends_with( $td['title'], '— nghĩa và cách dùng' ), array( $r_ko, $r_co, $td ) );
ob_start();
$SEO::dau_trang();
$dau = (string) ob_get_clean();
kt( 'đầu trang tác phẩm: mô tả từ Ý nghĩa, thẻ chia sẻ og:*, dữ liệu có cấu trúc CreativeWork + đường dẫn', str_contains( $dau, '<meta name="description" content="Một câu giải nghĩa tổng hợp' ) && str_contains( $dau, 'og:title' ) && str_contains( $dau, '"@type":"CreativeWork"' ) && str_contains( $dau, '"BreadcrumbList"' ) && ! str_contains( $dau, 'isBasedOn' ), $dau );
// Ảnh chia sẻ: PHP của Playground không vẽ được chữ (GD không FreeType) → không có thẻ og:image, nút Tải ảnh chỉ nhắc
$ANH  = \Phongdao\DichVu\AnhChiaSe\Module::class;
$ve_d = \Phongdao\DichVu\AnhChiaSe\VeAnh::duoc();
kt( 'ảnh chia sẻ: thẻ og:image, twitter:card chỉ khi máy vẽ được ' . ( $ve_d ? '(máy này vẽ được)' : '(máy này không)' ), $ve_d === str_contains( $dau, 'property="og:image"' ) && $ve_d === str_contains( $dau, 'name="twitter:card"' ) );
[ $ma404 ]                      = $ANH::phan_hoi( array( 'phongdao_anh' => '999999' ) );
$nhap_anh                       = wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'draft', 'post_title' => 'Nháp ảnh' ) );
[ $ma_nhap ]                    = $ANH::phan_hoi( array( 'phongdao_anh' => (string) $nhap_anh ) );
[ $ma_anh, $dau_tl, $than_anh ] = $ANH::phan_hoi( array( 'phongdao_anh' => (string) $l2, "kho-$l2" => 'vuong', "nen-$l2" => 'khong-co', "yn-$l2" => '1' ) );
kt(
	'tải ảnh: bài không có, bài nháp → 404; bài đã đăng → tệp PNG tải về (máy vẽ được) hoặc 503 nhắc chụp màn hình',
	404 === $ma404 && 404 === $ma_nhap && ( $ve_d ? 200 === $ma_anh && 'image/png' === $dau_tl['Content-Type'] && str_contains( $dau_tl['Content-Disposition'], '-vuong.png' ) && str_starts_with( $than_anh, "\x89PNG" ) : 503 === $ma_anh && str_contains( $than_anh, 'chụp màn hình' ) ) && 'noindex' === $dau_tl['X-Robots-Tag'],
	array( $ma404, $ma_nhap, $ma_anh, $dau_tl )
);
wp_delete_post( $nhap_anh, true );
$acs = \Phongdao\GiaoTiep\Khoi\TacPham::hien( (array) \Phongdao\DichVu\DungTrang\DocBai::tac_pham( get_post( $l2 ) ) );
kt( 'khối Ảnh để chia sẻ: máy vẽ được → form GET tới máy chủ (chạy cả khi tắt JavaScript); không → nút nhắc', $ve_d ? str_contains( $acs, '<form class="acs-chon" method="get"' ) && str_contains( $acs, 'name="phongdao_anh" value="' . $l2 . '"' ) : str_contains( $acs, 'data-action="download-card"' ) && ! str_contains( $acs, 'name="phongdao_anh"' ) );
kt( 'mô tả bài chưa có ý nghĩa: hai dòng đầu + thể loại, không hứa điều trang không có', str_ends_with( $SEO::mo_ta_tac_pham( $mau_id['ve-mau'] ), '— Vè.' ) && mb_strlen( $SEO::cat( str_repeat( 'chữ ', 80 ) ) ) <= 160 );
$t_chut = get_term_by( 'slug', 'chut', 'muc_tu' );
$tl_ca  = get_term_by( 'slug', 'ca-dao', KhaiBao::THE_LOAI );
$t_ld   = get_term_by( 'name', 'Lao động', 'chu_de' );
$truoc  = $SEO::term_lap( $t_chut );
update_term_meta( $t_chut->term_id, '_phongdao_loi_giai', array( 'nguoi_lam' => 'nguoi_viet' ) );
kt( 'phân loại: thể loại có bài → lập chỉ mục; mục từ chỉ khi lời giải do dự án viết; chủ đề cần ≥ 3 tác phẩm L2', $SEO::term_lap( $tl_ca ) && ! $truoc && $SEO::term_lap( get_term( $t_chut->term_id ) ) && ! $SEO::term_lap( $t_ld ) );
$sd = $SEO::so_do_bai( array(), KhaiBao::TAC_PHAM );
kt( 'sơ đồ trang: tác phẩm chỉ gồm bài L2; mục từ chỉ gồm mục lập chỉ mục được', $sd['post__in'] === $SEO::ds_l2() && in_array( $t_chut->term_id, $SEO::so_do_phan_loai( array(), 'muc_tu' )['include'], true ) );
$xem( array( 'post_type' => KhaiBao::TAC_PHAM ) );

// ---------- Bàn làm việc (T-180) ----------
$SV   = \Phongdao\DichVu\XepViec\SoViec::class;
$BLV  = \Phongdao\GiaoTiep\QuanTri\BanLamViec::class;
$gio  = time();
$luc  = static fn( float $ngay ): string => gmdate( 'Y-m-d H:i:s', (int) ( $gio - $ngay * DAY_IN_SECONDS ) );
$vr_0 = $SV::vao_ra( $gio )['can_xem'];
$tu_0 = $SV::tuoi( $gio );
$nd_0 = $SV::nguoi_doc_cho();
$the_blv = array();
foreach ( array( 0.5, 3, 10, 40 ) as $ngay ) { // thẻ đang mở ở bốn tuổi
	$the_blv[] = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $bai_lo, 'noi_dung' => array( 'ly_do' => array() ), 'nguoi_tao' => 'kiem_thu', 'tao_luc' => $luc( $ngay ) ) );
}
$the_blv[] = Viec::tao( array( 'loai' => 'can_xem', 'trang_thai' => Viec::XONG, 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $bai_lo, 'nguoi_tao' => 'kiem_thu', 'tao_luc' => $luc( 9 ), 'quyet' => 'xong', 'nguoi_quyet' => $ai_ql, 'quyet_luc' => $luc( 2 ) ) );
$the_blv[] = Viec::tao( array( 'loai' => 'nguoi_doc', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => 999999998, 'noi_dung' => array( 'loai' => 'bao_loi', 'bai' => 0 ), 'nguoi_tao' => 'nguoi_doc', 'tao_luc' => $luc( 1.5 ) ) );
$vr_1 = $SV::vao_ra( $gio )['can_xem'];
$tu_1 = $SV::tuoi( $gio );
$nd_1 = $SV::nguoi_doc_cho();
kt( 'bàn làm việc: thẻ vào / ra 7 ngày qua và 7 ngày trước đó theo loại (thẻ 10 ngày tuổi tính tuần trước, thẻ đóng 2 ngày trước tính "ra")', 2 === $vr_1['vao'] - $vr_0['vao'] && 2 === $vr_1['vao_truoc'] - $vr_0['vao_truoc'] && 1 === $vr_1['ra'] - $vr_0['ra'] && $vr_1['ra_truoc'] === $vr_0['ra_truoc'], array( $vr_0, $vr_1 ) );
kt( 'bàn làm việc: tuổi thẻ đang mở chia bốn khoảng, cộng đủ; thẻ đã đóng không tính', 5 === $tu_1['tong'] - $tu_0['tong'] && 1 === $tu_1['duoi_1'] - $tu_0['duoi_1'] && 2 === $tu_1['tu_1_7'] - $tu_0['tu_1_7'] && 1 === $tu_1['tu_7_30'] - $tu_0['tu_7_30'] && 1 === $tu_1['tren_30'] - $tu_0['tren_30'] && $tu_1['tong'] === $tu_1['duoi_1'] + $tu_1['tu_1_7'] + $tu_1['tu_7_30'] + $tu_1['tren_30'], array( $tu_0, $tu_1 ) );
kt( 'bàn làm việc: thẻ mở lâu nhất đứng đầu; báo lỗi của người đọc đếm riêng', $the_blv[3] === ( $SV::lau_nhat( 1 )[0]['id'] ?? 0 ) && 1 === ( $nd_1['bao_loi'] ?? 0 ) - ( $nd_0['bao_loi'] ?? 0 ) );
$nl = $BLV::viec_nen_lam( $gio );
kt( 'bàn làm việc: tối đa 10 việc nên làm, báo lỗi người đọc đứng đầu, mỗi việc mở một danh sách trong wp-admin', count( $nl ) <= 10 && $nl && str_contains( $nl[0]['chu'], 'báo lỗi' ) && ! array_filter( $nl, static fn( array $x ): bool => ! str_contains( $x['url'], 'admin.php?page=phongdao' ) || $x['so'] < 1 ), $nl );
kt( 'bàn làm việc: đếm ngoại lệ theo đúng bộ lọc của danh sách Tác phẩm (không dừng ở số mỗi trang)', \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::dem_loc( array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia' ) ) === count( \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::ids_loc( array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia' ), 5000 ) ) );
$hs_ncr  = (int) wp_insert_post( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'post_title' => 'Sách thử bàn làm việc' ) );
$NCD::luu_ho_so( $hs_ncr, array( 'ten' => 'Sách thử bàn làm việc', 'loai' => 'sach' ) );
$ncr_0   = array_values( array_filter( $BLV::nguon_chua_ro(), static fn( array $n ): bool => $hs_ncr === $n['id'] ) );
$bai_ncr = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Bài thử nguồn chưa rõ', 'post_name' => 'bai-thu-nguon-chua-ro' ) );
wp_set_object_terms( $bai_ncr, 'ca-dao', KhaiBao::THE_LOAI );
LuuPhan::luu( $bai_ncr, 'loi', array( 'dong' => array( 'Dòng thử nguồn chưa rõ một', 'Dòng thử nguồn chưa rõ hai' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET, 'doi_chieu' => array( 'ho_so' => $hs_ncr, 'trang' => '5' ) ), $ncq );
$ncr = array_values( array_filter( $BLV::nguon_chua_ro(), static fn( array $n ): bool => $hs_ncr === $n['id'] ) );
wp_delete_post( $bai_ncr, true );
wp_delete_post( $hs_ncr, true );
kt( 'bàn làm việc: hồ sơ nguồn chưa bài nào dùng thì không báo; có bài dùng mà thiếu giấy phép thì vào "nguồn chưa rõ", kèm số bài', array() === $ncr_0 && 1 === count( $ncr ) && array( 'giấy phép' ) === $ncr[0]['thieu'] && 1 === $ncr[0]['bai'], array( $ncr_0, $ncr ) );
wp_set_current_user( $chi_soan );
$h_blv = $BLV::hien( $gio );
wp_set_current_user( 0 );
$h_khach = $BLV::hien( $gio );
wp_set_current_user( $ai_ql );
kt( 'trang Hôm nay: người biên soạn xem được 3 việc gấp, ba dòng việc, nên làm và các khối thu gọn, không lộ nút đổi dữ liệu; khách không xem được', str_contains( $h_blv, '<h1>Hôm nay</h1>' ) && str_contains( $h_blv, 'việc gấp nhất' ) && strpos( $h_blv, 'việc gấp nhất' ) < strpos( $h_blv, 'class="phongdao-ban-so"' ) && str_contains( $h_blv, 'báo lỗi' ) && strpos( $h_blv, 'báo lỗi' ) < strpos( $h_blv, 'class="phongdao-ban-so"' ) && str_contains( $h_blv, 'Lịch sử thao tác hàng loạt' ) && str_contains( $h_blv, 'Nên làm tuần này' ) && str_contains( $h_blv, 'Việc vào, ra' ) && str_contains( $h_blv, 'Tuổi thẻ đang mở' ) && str_contains( $h_blv, 'Ngoại lệ dữ liệu' ) && str_contains( $h_blv, 'Thử làm sâu 20–30 bài' ) && ! str_contains( $h_blv, '<form' ) && str_contains( $h_khach, 'không có quyền' ) );
$dau_blv = Viec::tim_quan_tri( Viec::CHO, '', '', 1, 1 )['ds'][0] ?? null;
kt( 'trang Hôm nay: ba ô Bài mới · Hộp việc · Bổ sung, mỗi ô một nút bắt đầu; nút "Bắt đầu duyệt (N việc)" mở thẻ cần làm trước nhất ở Hộp việc; ngoại lệ, việc vào/ra, tuổi thẻ và Số liệu tuần thu gọn ở cuối', $dau_blv && str_contains( $h_blv, 'class="phongdao-ban-so"' ) && 3 === substr_count( $h_blv, 'class="phongdao-ban-o"' ) && str_contains( $h_blv, '<h3>Bài mới</h3>' ) && str_contains( $h_blv, '<h3>Hộp việc</h3>' ) && str_contains( $h_blv, '<h3>Bổ sung</h3>' ) && str_contains( $h_blv, esc_url( \Phongdao\GiaoTiep\QuanTri\BoSung::url() ) . '">Bắt đầu bổ sung</a>' ) && str_contains( $h_blv, esc_url( \Phongdao\GiaoTiep\QuanTri\DanhSachKho::url( UngVien::goi_y_hom_nay()['ids'] ? array( 'goi_y' => 'hom_nay' ) : array() ) ) . '">Chọn câu để đăng</a>' ) && str_contains( $h_blv, 'href="' . esc_url( \Phongdao\GiaoTiep\QuanTri\DanhSachViec::url( array(), 1, (int) $dau_blv['id'] ) ) . '">Bắt đầu duyệt (' . number_format_i18n( Viec::dem_hop_viec() ) . ' việc)</a>' ) && strpos( $h_blv, 'Bắt đầu duyệt' ) < strpos( $h_blv, 'Nên làm tuần này' ) && str_contains( $h_blv, '<details class="phongdao-ban-gon"><summary>Việc vào, ra và tuổi thẻ</summary>' ) && str_contains( $h_blv, 'Theo cấp chất lượng' ) && str_contains( $h_blv, 'Lượt đọc 7 ngày' ) && strpos( $h_blv, 'phongdao-ban-gon' ) < strpos( $h_blv, 'Ngoại lệ dữ liệu' ) );
// Số liệu tuần nhớ 1 giờ (Hôm nay mở nhanh): lần mở sau đọc bản đã nhớ, ghi giờ tính
$nho_sl = get_transient( \Phongdao\GiaoTiep\QuanTri\BanLamViec::NHO_SO_LIEU );
wp_set_current_user( $chi_soan );
$h_blv2 = $BLV::hien( $gio );
wp_set_current_user( $ai_ql );
kt( 'trang Hôm nay: Số liệu tuần tính một lần rồi nhớ trong 1 giờ, lần mở sau ghi "Tính lúc …"', is_array( $nho_sl ) && str_contains( (string) ( $nho_sl['html'] ?? '' ), 'Theo cấp chất lượng' ) && str_contains( $h_blv2, 'Tính lúc ' ) && str_contains( $h_blv2, 'tính lại sau 1 giờ' ) );
// Khung "Phongdao hôm nay" ở trang chào mừng WordPress: ba số và nút bắt đầu; khách không thấy
wp_set_current_user( $chi_soan );
$h_kwp = $BLV::khung_wp();
wp_set_current_user( 0 );
$h_kwp0 = $BLV::khung_wp();
wp_set_current_user( $ai_ql );
kt( 'trang chào mừng WordPress: khung "Phongdao hôm nay" có ba ô và nút bắt đầu, lối sang Hôm nay; khách không thấy; móc gắn ở /wp-admin/', str_contains( $h_kwp, 'Phongdao hôm nay' ) && 3 === substr_count( $h_kwp, 'class="phongdao-ban-o"' ) && str_contains( $h_kwp, 'Mở màn Hôm nay đầy đủ' ) && '' === $h_kwp0 && false !== has_action( 'load-index.php', array( \Phongdao\GiaoTiep\QuanTri\Module::class, 'trang_chao' ) ) );
// Bổ sung: danh sách ưu tiên, nút Bắt đầu mở bài đầu ở trang sửa kèm lối về đúng danh sách (để sang bài kế)
wp_set_current_user( $chi_soan );
$h_bs  = \Phongdao\GiaoTiep\QuanTri\BoSung::hien();
$uu_bs = \Phongdao\GiaoTiep\QuanTri\BoSung::uu_tien();
wp_set_current_user( 0 );
$h_bs0 = \Phongdao\GiaoTiep\QuanTri\BoSung::hien();
wp_set_current_user( $ai_ql );
$thieu_bs = array( 'tt' => 'publish', 'muc' => 'thieu_y_nghia', 'xep' => 'doc' );
$dau_bs   = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::ids_loc( $thieu_bs, 1 )[0] ?? 0;
// ---------- Hộp việc: sửa rồi duyệt nháp ngay trên thẻ; thanh dưới điện thoại (T-189.5) ----------
$bai_ns = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Bài nháp sửa thử', 'post_name' => 'bai-nhap-sua-thu' ) );
wp_set_object_terms( $bai_ns, 'ca-dao', KhaiBao::THE_LOAI );
LuuPhan::luu( $bai_ns, 'loi', array( 'dong' => array( 'Dòng nháp sửa một', 'Dòng nháp sửa hai' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq );
$v_ns = Viec::tao( array( 'loai' => 'nhap_y_nghia', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $bai_ns, 'noi_dung' => array( 'chu' => 'Nháp máy soạn có lỗi chính tả.', 'ai' => true ), 'nguoi_tao' => 'may' ) );
wp_set_current_user( $chi_soan );
$the_ns_s = \Phongdao\GiaoTiep\BienTap\HopViec::the( Viec::lay( $v_ns ) );
wp_set_current_user( $ai_ql );
$the_ns_q = \Phongdao\GiaoTiep\BienTap\HopViec::the( Viec::lay( $v_ns ) );
$q_ns     = QuyetViec::quyet( $v_ns, 'duyet', $ai_ql, true, (int) Viec::lay( $v_ns )['phien'], 'Nháp đã sửa chính tả trên thẻ.' );
$yn_ns    = PhanBai::lay( $bai_ns, 'y_nghia' );
kt( 'Hộp việc: người có quyền duyệt sửa vài chữ trong nháp ngay trên thẻ (ô gắn form quyết) rồi Duyệt — bài nhận bản đã sửa, giữ dấu "có hỗ trợ AI" như nháp, Lịch sử ghi "đã sửa"; người chỉ soạn chỉ đọc nháp', str_contains( $the_ns_q, 'name="chu" form="hv-form-' . $v_ns . '"' ) && str_contains( $the_ns_q, 'id="hv-form-' . $v_ns . '"' ) && ! str_contains( $the_ns_s, 'name="chu"' ) && $q_ns['ok'] && str_contains( $q_ns['thong_bao'], 'Đã sửa và duyệt' ) && 'Nháp đã sửa chính tả trên thẻ.' === ( $yn_ns['chu'] ?? '' ) && ! empty( $yn_ns['ai'] ) && NguoiLam::AI_DA_DUYET === ( $yn_ns['nguoi_lam'] ?? '' ) && ! empty( Viec::lay( $v_ns )['truoc']['da_sua'] ), array( $q_ns, $yn_ns ) );
wp_delete_post( $bai_ns, true );
$_GET = array( 'page' => \Phongdao\GiaoTiep\QuanTri\DanhSachViec::TRANG );
ob_start();
\Phongdao\GiaoTiep\QuanTri\Module::thanh_duoi();
$h_duoi = (string) ob_get_clean();
$_GET = array( 'page' => \Phongdao\GiaoTiep\QuanTri\SuaTacPham::TRANG );
ob_start();
\Phongdao\GiaoTiep\QuanTri\Module::thanh_duoi();
$h_duoi_sua = (string) ob_get_clean();
$_GET = array();
kt( 'thanh dưới điện thoại: bốn lối Hôm nay · Hộp việc · Bổ sung · Bài mới, tô mục đang mở (Sửa tác phẩm thuộc Tác phẩm, không có trên thanh dưới); chỉ trên màn Phongdao', 4 === substr_count( $h_duoi, '<a href=' ) && str_contains( $h_duoi, 'page=phongdao-hop-viec" aria-current="page"' ) && str_contains( $h_duoi, '>Bổ sung</span>' ) && 0 === substr_count( $h_duoi_sua, 'aria-current' ) );
// ---------- Bổ sung: duyệt gợi ý chủ đề, vùng theo nhóm / theo bài; Hộp việc chỉ việc cần quyết (T-189.4) ----------
$BS    = \Phongdao\GiaoTiep\QuanTri\BoSung::class;
$t_bs  = wp_insert_term( 'Chủ đề thử bổ sung', 'chu_de', array( 'slug' => 'chu-de-thu-bo-sung' ) );
$bs_b  = array();
$bs_v  = array();
foreach ( array( 'một', 'hai', 'ba' ) as $i_bs => $so_bs ) {
	$bs_b[ $i_bs ] = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Bài gợi ý thử ' . $so_bs, 'post_name' => 'bai-goi-y-thu-' . $i_bs ) );
	wp_set_object_terms( $bs_b[ $i_bs ], 'ca-dao', KhaiBao::THE_LOAI );
	$bs_v[ $i_bs ] = Viec::tao( array( 'loai' => 'goi_y', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $bs_b[ $i_bs ], 'noi_dung' => array( 'loai' => 'chu_de', 'ma' => 'chu-de-thu-bo-sung', 'ly_do' => 'từ thử ' . $so_bs ), 'nguoi_tao' => 'may' ) );
}
$_GET     = array();
$h_hv_bs  = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
$_GET     = array( 'loai' => 'goi_y' );
$h_hv_gy  = \Phongdao\GiaoTiep\QuanTri\DanhSachViec::hien();
$_GET     = array();
kt( 'Hộp việc chỉ việc cần quyết: gợi ý chủ đề, vùng không nằm trong danh sách, tab, số việc của Hộp việc (vẫn mở được bằng đường dẫn cũ loai=goi_y)', ! str_contains( $h_hv_bs, 'viec=' . $bs_v[0] . '"' ) && ! str_contains( $h_hv_bs, 'loai=goi_y' ) && str_contains( $h_hv_gy, 'viec=' . $bs_v[0] ) && Viec::dem_hop_viec() === Viec::dem_cho() - Viec::dem( Viec::CHO, 'goi_y' ) );
$nhom_bs = $BS::nhom_goi_y()['chu_de:chu-de-thu-bo-sung'] ?? null;
$h_bs_c  = $BS::hien();
$_GET    = array( 'page' => $BS::TRANG, 'xem' => 'goi_y', 'nhom' => 'chu_de:chu-de-thu-bo-sung' );
$h_bs_n  = $BS::hien();
$_GET    = array( 'page' => $BS::TRANG, 'xem' => 'goi_y', 'bai' => (string) $bs_b[1] );
$h_bs_b  = $BS::hien();
$_GET    = array();
kt( 'Bổ sung: gợi ý gom theo nhóm (một chủ đề kèm số); màn nhóm liệt kê từng bài, đánh dấu sẵn, kèm lý do máy dựa vào; màn theo bài có mọi gợi ý của bài; người có quyền duyệt có "Đồng ý các mục đã chọn"', $nhom_bs && 3 === count( $nhom_bs['ids'] ) && str_contains( $h_bs_c, 'Gợi ý chủ đề, vùng của máy' ) && ( str_contains( $h_bs_c, 'nhom=chu_de%3Achu-de-thu-bo-sung' ) || str_contains( $h_bs_c, 'nhom=chu_de:chu-de-thu-bo-sung' ) ) && 3 === substr_count( $h_bs_n, 'name="ids[]"' ) && 3 === substr_count( $h_bs_n, '" checked>' ) && str_contains( $h_bs_n, 'Máy dựa vào: từ thử hai' ) && str_contains( $h_bs_n, 'Đồng ý các mục đã chọn' ) && 1 === substr_count( $h_bs_b, 'name="ids[]"' ) && str_contains( $h_bs_b, 'Bài gợi ý thử hai' ), array( $nhom_bs ) );
// Đồng ý hai gợi ý (bỏ dấu ngoại lệ thứ ba), rồi Bỏ gợi ý còn lại: mỗi lần là một lô, máy không tự gắn
$bat_bs = static function ( $vi_tri ): string {
	throw new \RuntimeException( 'redirect: ' . $vi_tri );
};
add_filter( 'wp_redirect', $bat_bs );
$gui_bs = static function ( array $than ) use ( $BS ): string {
	$_POST = $_REQUEST = $than + array( 'action' => $BS::HANH_DONG, '_wpnonce' => wp_create_nonce( $BS::HANH_DONG ), 've' => $BS::url_goi_y( array( 'nhom' => 'chu_de:chu-de-thu-bo-sung' ) ) );
	try {
		$BS::nhan_goi_y();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$r_bs_dy = $gui_bs( array( 'lam' => 'dong_y', 'ids' => array( (string) $bs_v[0], (string) $bs_v[1] ) ) );
$bao_bs  = get_transient( 'phongdao_lo_bao_' . $ai_ql );
$r_bs_bo = $gui_bs( array( 'lam' => 'bo', 'ids' => array( (string) $bs_v[2] ) ) );
$_POST   = $_REQUEST = array();
remove_filter( 'wp_redirect', $bat_bs );
kt( 'Bổ sung: "Đồng ý các mục đã chọn" gắn đúng chủ đề cho đúng các bài đã chọn (bài bỏ dấu không gắn), ghi thành lô có kết quả từng bài, về lại màn nhóm; "Bỏ" đóng gợi ý còn lại, không gắn', str_contains( $r_bs_dy, 'page=phongdao-bo-sung' ) && has_term( 'chu-de-thu-bo-sung', 'chu_de', $bs_b[0] ) && has_term( 'chu-de-thu-bo-sung', 'chu_de', $bs_b[1] ) && ! has_term( 'chu-de-thu-bo-sung', 'chu_de', $bs_b[2] ) && Viec::XONG === Viec::lay( $bs_v[0] )['trang_thai'] && Viec::BO === Viec::lay( $bs_v[2] )['trang_thai'] && str_contains( (string) ( $bao_bs['chu'] ?? '' ), 'Đã đồng ý 2 gợi ý' ) && (int) ( $bao_bs['lo'] ?? 0 ) > 0 && ! isset( $BS::nhom_goi_y()['chu_de:chu-de-thu-bo-sung'] ), array( $r_bs_dy, $r_bs_bo, $bao_bs ) );
foreach ( $bs_b as $id_bs ) {
	wp_delete_post( $id_bs, true );
}
if ( is_array( $t_bs ) ) {
	wp_delete_term( (int) $t_bs['term_id'], 'chu_de' );
}
// ---------- Bảng tóm tắt bài (T-189.2, docs/32 mục 3.9) ----------
$TT   = \Phongdao\DichVu\TomTat\TomTat::class;
$tt_b = array();
foreach ( array( 'ca-dao' => 'Ca dao tóm tắt thử', 'tuc-ngu' => 'Tục ngữ tóm tắt thử', 'cau-do' => 'Câu đố tóm tắt thử' ) as $tl_tt => $ten_tt ) {
	$tt_b[ $tl_tt ] = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => $ten_tt, 'post_name' => sanitize_title( $ten_tt ) ) );
	wp_set_object_terms( $tt_b[ $tl_tt ], $tl_tt, KhaiBao::THE_LOAI );
	LuuPhan::luu( $tt_b[ $tl_tt ], 'loi', array( 'dong' => array( $ten_tt . ' dòng một', $ten_tt . ' dòng hai' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq );
}
LuuPhan::luu( $tt_b['tuc-ngu'], 'nghia_bong', array( 'chu' => 'Nghĩa bóng thử của bảng tóm tắt.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq );
LuuPhan::luu( $tt_b['cau-do'], 'dap_an', array( 'chu' => 'Đáp án thử', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq );
$d_cd = $TT::lay( $tt_b['ca-dao'] );
$d_tn = $TT::lay( $tt_b['tuc-ngu'] );
$d_cq = $TT::lay( $tt_b['cau-do'] );
kt( 'bảng tóm tắt: lưu một phần thì dòng của bài ghi ngay — thể loại, trạng thái, cấp, còn thiếu theo thể loại (ca dao thiếu Ý nghĩa; tục ngữ có nghĩa bóng thì không thiếu Ý nghĩa nhưng thiếu nghĩa đen; câu đố thiếu giải thích, không nằm trong "thiếu Ý nghĩa")', $d_cd && 'ca-dao' === $d_cd['the_loai'] && 'publish' === $d_cd['trang_thai'] && 1 === (int) $d_cd['cap'] && in_array( 'y_nghia', $TT::thieu( $d_cd ), true ) && in_array( 'tu_kho', $TT::thieu( $d_cd ), true ) && $d_tn && ! in_array( 'y_nghia', $TT::thieu( $d_tn ), true ) && in_array( 'nghia_tn', $TT::thieu( $d_tn ), true ) && $d_cq && ! in_array( 'y_nghia', $TT::thieu( $d_cq ), true ) && ! in_array( 'dap_an', $TT::thieu( $d_cq ), true ) && in_array( 'giai_thich', $TT::thieu( $d_cq ), true ), array( $d_cd, $d_tn, $d_cq ) );
LuuPhan::luu( $tt_b['ca-dao'], 'y_nghia', array( 'chu' => 'Ý nghĩa thử của bảng tóm tắt.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq );
LuuPhan::luu( $tt_b['ca-dao'], 'co_nghi', array( 'ly_do' => 'Lý do thử của bảng tóm tắt.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ncq );
wp_update_post( array( 'ID' => $tt_b['tuc-ngu'], 'post_status' => 'private' ) );
wp_set_object_terms( $tt_b['cau-do'], 'dong-dao', KhaiBao::THE_LOAI );
$d_cd2 = $TT::lay( $tt_b['ca-dao'] );
$d_tn2 = $TT::lay( $tt_b['tuc-ngu'] );
$d_cq2 = $TT::lay( $tt_b['cau-do'] );
kt( 'bảng tóm tắt: thêm Ý nghĩa → hết "thiếu Ý nghĩa", lên cấp 2; bật cờ → có cờ; đổi trạng thái, đổi thể loại → dòng đổi theo trong cùng lượt', ! in_array( 'y_nghia', $TT::thieu( $d_cd2 ), true ) && 2 === (int) $d_cd2['cap'] && 1 === (int) $d_cd2['co'] && 'private' === $d_tn2['trang_thai'] && 'dong-dao' === $d_cq2['the_loai'] && in_array( 'y_nghia', $TT::thieu( $d_cq2 ), true ), array( $d_cd2, $d_tn2, $d_cq2 ) );
// Dựng lại toàn bộ: xoá sạch bảng rồi dựng lại cho đủ mọi tác phẩm (trừ bản nháp tự động)
$wpdb->query( 'DELETE FROM ' . $TT::bang() );
$n_dl   = $TT::dung_lai_ngay();
$n_bai  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status <> 'auto-draft'", KhaiBao::TAC_PHAM ) );
$n_dong = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $TT::bang() );
kt( 'bảng tóm tắt: dựng lại toàn bộ (bước nâng cấp chạy lại được, lệnh) cho đủ mọi tác phẩm, dòng đúng như lúc lưu', $n_dl === $n_bai && $n_dong === $n_bai && $d_cd2['thieu'] === ( $TT::lay( $tt_b['ca-dao'] )['thieu'] ?? '' ), array( $n_dl, $n_bai, $n_dong ) );
// Danh sách Tác phẩm đọc từ bảng tóm tắt: lọc "câu đố thiếu đáp án", "tục ngữ thiếu nghĩa", có cờ; tab có số; hàng Sửa · Xem · Ẩn
$DSTP2 = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::class;
wp_set_object_terms( $tt_b['cau-do'], 'cau-do', KhaiBao::THE_LOAI );
$TT::xong(); // dòng của bài vừa đổi thể loại ghi xong trước khi đặt lượt đọc thử
$wpdb->update( $TT::bang(), array( 'doc_7' => 5 ), array( 'post_id' => $tt_b['cau-do'] ) );
$ids_cf  = $DSTP2::ids_loc( array( 'q' => 'tóm tắt thử', 'tt' => 'tat_ca', 'muc' => 'co_co' ), 50 );
$ids_gt  = $DSTP2::ids_loc( array( 'q' => 'tóm tắt thử', 'tt' => 'publish', 'muc' => 'thieu_giai_thich' ), 50 );
$ids_ntn = $DSTP2::ids_loc( array( 'q' => 'tóm tắt thử', 'tt' => 'tat_ca', 'muc' => 'thieu_nghia_tn' ), 50 );
$ids_doc = $DSTP2::ids_loc( array( 'q' => 'tóm tắt thử', 'tt' => 'publish', 'xep' => 'doc' ), 50 );
$_GET   = array( 'q' => 'tóm tắt thử', 'tt' => 'tat_ca' );
$h_tp2  = $DSTP2::hien();
$_GET   = array();
kt( 'danh sách Tác phẩm: lọc theo bảng tóm tắt (có cờ, câu đố thiếu giải thích, tục ngữ thiếu nghĩa đen / bóng), xếp bài đọc nhiều trước', array( $tt_b['ca-dao'] ) === $ids_cf && array( $tt_b['cau-do'] ) === $ids_gt && array( $tt_b['tuc-ngu'] ) === $ids_ntn && $tt_b['cau-do'] === ( $ids_doc[0] ?? 0 ), array( $ids_cf, $ids_gt, $ids_ntn, $ids_doc ) );
kt( 'danh sách Tác phẩm: tab trạng thái kèm số (Tất cả · Đã đăng · Bản nháp · Đã ẩn · Thùng rác), nút lọc nhanh Thiếu ý nghĩa / Có cờ, bộ lọc còn lại trong "Thêm bộ lọc"; mỗi hàng Sửa · Xem · Ẩn (Ẩn chỉ với bài đã đăng, gửi form riêng ngoài bảng); điện thoại có dòng 2 (trạng thái · thể loại) và dòng 3 (còn thiếu, cờ)', str_contains( $h_tp2, 'aria-label="Trạng thái bài"' ) && 5 === substr_count( $h_tp2, 'class="phongdao-chip"' ) && str_contains( $h_tp2, '>Thùng rác <span class="phongdao-chip-so">' ) && str_contains( $h_tp2, '">Thiếu ý nghĩa</a>' ) && str_contains( $h_tp2, 'class="phongdao-tp-nhanh"' ) && str_contains( $h_tp2, '<summary>Thêm bộ lọc</summary>' ) && str_contains( $h_tp2, 'aria-label="Sửa bài #' . $tt_b['ca-dao'] . '">Sửa</a>' ) && str_contains( $h_tp2, 'form="phongdao-an-' . $tt_b['ca-dao'] . '"' ) && str_contains( $h_tp2, '<form id="phongdao-an-' . $tt_b['ca-dao'] . '"' ) && ! str_contains( $h_tp2, 'form="phongdao-an-' . $tt_b['tuc-ngu'] . '"' ) && str_contains( $h_tp2, '<span class="phongdao-tp-dong2">Đã đăng · Ca dao</span>' ) && str_contains( $h_tp2, 'Thiếu: Giải thích đáp án' ) && str_contains( $h_tp2, '<strong>Có cờ</strong>' ) );
// Nút Ẩn trên hàng: đi đúng đường nút Ẩn (người bấm, có Lịch sử), về lại đúng danh sách kèm lời báo
$bat_tp = static function ( $vi_tri ): string {
	throw new \RuntimeException( 'redirect: ' . $vi_tri );
};
add_filter( 'wp_redirect', $bat_tp );
$ve_tp = $DSTP2::url_loc( array( 'q' => 'tóm tắt thử', 'tt' => 'tat_ca' ) );
$_POST = $_REQUEST = array( 'id' => (string) $tt_b['ca-dao'], 'trang_thai' => 'private', 'trang_thai_cu' => 'publish', 'quay_lai' => $ve_tp, '_wpnonce' => wp_create_nonce( 'phongdao_doi_trang_thai_' . $tt_b['ca-dao'] ) );
try {
	\Phongdao\GiaoTiep\QuanTri\SuaTacPham::doi_trang_thai();
	$r_an_tp = 'không dừng';
} catch ( \RuntimeException $e ) {
	$r_an_tp = $e->getMessage();
}
$_POST = $_REQUEST = array();
remove_filter( 'wp_redirect', $bat_tp );
kt( 'danh sách Tác phẩm: bấm Ẩn trên hàng → bài Đã ẩn, có Lịch sử, bảng tóm tắt đổi theo, về lại đúng danh sách kèm lời báo', 'private' === get_post_status( $tt_b['ca-dao'] ) && 'private' === ( $TT::lay( $tt_b['ca-dao'] )['trang_thai'] ?? '' ) && str_contains( $r_an_tp, 'page=phongdao-tac-pham' ) && str_contains( rawurldecode( $r_an_tp ), 'Đã ẩn “Ca dao tóm tắt thử”' ), $r_an_tp );
foreach ( $tt_b as $id_tt ) {
	wp_delete_post( $id_tt, true );
}
kt( 'bảng tóm tắt: xoá hẳn bài thì xoá dòng', null === $TT::lay( $tt_b['ca-dao'] ) );
kt( 'màn Bổ sung: danh sách ưu tiên có số đúng bộ lọc Tác phẩm; "Bắt đầu" mở bài đầu ở trang sửa kèm lối về danh sách đã lọc; không có form; khách không xem được', str_contains( $h_bs, '<h1>Bổ sung</h1>' ) && str_contains( $h_bs, 'Danh sách ưu tiên' ) && \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::dem_loc( $thieu_bs ) === $uu_bs[0][1] && ( ! $dau_bs || $uu_bs[0][3] === \Phongdao\GiaoTiep\QuanTri\SuaTacPham::url( (int) $dau_bs, \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::url_loc( $thieu_bs ) ) ) && ! str_contains( $h_bs, '<form' ) && str_contains( $h_bs0, 'không có quyền' ), $uu_bs );
foreach ( $the_blv as $id_blv ) {
	Viec::xoa( (int) $id_blv );
}

// ---------- Đường dẫn dùng chung (T-178) ----------
// Thêm bài "đăng ngay" từng đăng bằng wp_publish_post (không kiểm trùng tên): hai bài cùng câu đầu chung một địa chỉ,
// người đọc chỉ mở được bài đăng sau. Nay tên riêng ngay từ nháp; dữ liệu cũ đã trùng thì báo, người quản trị bấm mới tách.
$DSTP = \Phongdao\GiaoTiep\QuanTri\DanhSachTacPham::class;
$DD   = \Phongdao\DichVu\LuuPhan\DuongDan::class;
wp_set_current_user( $ai_ql );
$tb_1 = ThemBai::tao( array( 'the_loai' => 'ca-dao', 'loi' => "Câu trùng tên mẫu đầu dòng\nDòng sau của bản thứ nhất", 'ten' => '', 'nguon' => '', 'dang' => true ), $ai_ql );
$tb_2 = ThemBai::tao( array( 'the_loai' => 'ca-dao', 'loi' => "Câu trùng tên mẫu đầu dòng\nDòng sau khác hẳn của bản thứ hai", 'ten' => '', 'nguon' => '', 'dang' => true ), $ai_ql );
$ten_tr = (string) get_post_field( 'post_name', $tb_1['bai'] );
kt( 'Thêm bài đăng ngay hai bài cùng câu đầu → hai đường dẫn khác nhau, người đọc mở được cả hai', $tb_1['ok'] && $tb_2['ok'] && 'publish' === get_post_status( $tb_2['bai'] ) && $ten_tr . '-2' === get_post_field( 'post_name', $tb_2['bai'] ) && $tb_1['bai'] === url_to_postid( get_permalink( $tb_1['bai'] ) ) && $tb_2['bai'] === url_to_postid( get_permalink( $tb_2['bai'] ) ) && ! isset( $DD::trung()[ $ten_tr ] ), array( $tb_1, $tb_2, $ten_tr, get_post_field( 'post_name', $tb_2['bai'] ) ) );
// Dữ liệu cũ: bài 2 (đăng sau) mang đúng tên của bài 1
$wpdb->update( $wpdb->posts, array( 'post_date' => '2026-11-01 09:00:00', 'post_date_gmt' => '2026-11-01 02:00:00' ), array( 'ID' => $tb_1['bai'] ) );
$wpdb->update( $wpdb->posts, array( 'post_name' => $ten_tr, 'post_date' => '2026-11-08 09:00:00', 'post_date_gmt' => '2026-11-08 02:00:00' ), array( 'ID' => $tb_2['bai'] ) );
clean_post_cache( $tb_1['bai'] );
clean_post_cache( $tb_2['bai'] );
$nhom_tr = $DD::trung()[ $ten_tr ] ?? array();
kt( 'hai bài đã đăng cùng tên đường dẫn: phát hiện cả hai; bài người đọc mở được đúng là bài WordPress trả cho địa chỉ đó (đăng sau)', 2 === count( $nhom_tr ) && $tb_2['bai'] === $nhom_tr[0]['id'] && $nhom_tr[0]['mo_duoc'] && ! $nhom_tr[1]['mo_duoc'] && $tb_2['bai'] === url_to_postid( home_url( '/ca-dao/' . $ten_tr . '/' ) ), $nhom_tr );
$h_tr_ql = $DSTP::bao_trung();
wp_set_current_user( $chi_soan );
$h_tr_bs = $DSTP::bao_trung();
add_filter( 'wp_die_handler', $chan_die_lo );
add_filter( 'wp_redirect', $chan_di_lo );
$gui_tach = static function ( int $nguoi, int $id ) use ( $DSTP ): string {
	wp_set_current_user( $nguoi );
	$_POST = $_REQUEST = array( 'id' => (string) $id, '_wpnonce' => wp_create_nonce( $DSTP::TACH . '_' . $id ) );
	try {
		$DSTP::tach();
		return 'không dừng';
	} catch ( \RuntimeException $e ) {
		return $e->getMessage();
	}
};
$t_bs = $gui_tach( $chi_soan, $tb_1['bai'] );
$ten_bs = (string) get_post_field( 'post_name', $tb_1['bai'] );
$t_ql = $gui_tach( $ai_ql, $tb_1['bai'] );
remove_filter( 'wp_die_handler', $chan_die_lo );
remove_filter( 'wp_redirect', $chan_di_lo );
$_POST = $_REQUEST = array();
wp_set_current_user( $ai_ql );
clean_post_cache( $tb_1['bai'] );
$ten_rieng = (string) get_post_field( 'post_name', $tb_1['bai'] );
$nk_tr     = array_values( array_filter( Viec::lich_su( 'post', $tb_1['bai'], 20 ), static fn( array $r ): bool => 'duong_dan' === $r['phan'] ) );
$bao_tr    = $DSTP::bao_trung();
kt( 'trang Tác phẩm báo đường dẫn dùng chung, nói bài nào người đọc không mở được; nút tách chỉ người quản trị thấy', str_contains( $h_tr_ql, '#' . $tb_1['bai'] ) && str_contains( $h_tr_ql, 'người đọc không mở được bài này' ) && str_contains( $h_tr_ql, 'Cho bài này đường dẫn riêng' ) && str_contains( $h_tr_bs, '#' . $tb_1['bai'] ) && ! str_contains( $h_tr_bs, 'Cho bài này đường dẫn riêng' ), array( $h_tr_ql, $h_tr_bs ) );
kt( 'người biên soạn không tách được; người quản trị bấm thì bài kia có đường dẫn riêng, người đọc mở được cả hai, Lịch sử ghi trước/sau', str_starts_with( $t_bs, 'wp_die' ) && $ten_tr === $ten_bs && str_starts_with( $t_ql, 'redirect:' ) && str_starts_with( $ten_rieng, $ten_tr . '-' ) && $tb_1['bai'] === url_to_postid( get_permalink( $tb_1['bai'] ) ) && $tb_2['bai'] === url_to_postid( get_permalink( $tb_2['bai'] ) ) && ! isset( $DD::trung()[ $ten_tr ] ) && 1 === count( $nk_tr ) && $ten_tr === $nk_tr[0]['truoc']['post_name'] && $ten_rieng === $nk_tr[0]['sau']['post_name'] && $ai_ql === $nk_tr[0]['nguoi_quyet'] && str_contains( \Phongdao\GiaoTiep\BienTap\CheDoSua::mo_ta( $nk_tr[0] ), 'Đường dẫn: cho đường dẫn riêng' ) && str_contains( $bao_tr, 'nay có đường dẫn riêng' ), array( $t_bs, $t_ql, $ten_rieng, $nk_tr ) );
kt( 'tách lại bài không còn trùng → không đổi gì', ! $DD::tach( $tb_1['bai'], $ai_ql )['ok'] && $ten_rieng === get_post_field( 'post_name', $tb_1['bai'] ) );
wp_delete_post( $tb_1['bai'], true );
wp_delete_post( $tb_2['bai'], true );

// ---------- Nguồn & cài đặt (wp-admin) ----------
$NC  = \Phongdao\GiaoTiep\QuanTri\NguonCaiDat::class;
$cu  = \Phongdao\DichVu\NhapNguon\NguonTuDong::lay( 'cadao-me' );
$NC::luu_nguon( 'cadao-me', array( 'bat' => '1', 'so_bai_moi_ngay' => '99', 'loai_lay' => array( 'ca_dao', 'la' ), 'bo_tho_tuc' => '1' ) );
$moi = \Phongdao\DichVu\NhapNguon\NguonTuDong::lay( 'cadao-me' );
kt( 'Nguồn tự động: bật / tắt, số câu mỗi ngày giới hạn 1–50, chỉ nhận loại đã biết, tắt chép chú thích, bỏ câu thô tục', true === $moi['bat'] && 50 === $moi['so_bai_moi_ngay'] && array( 'ca_dao' ) === $moi['loai_lay'] && false === $moi['chep_chu_thich'] && array( 'tho_tuc' ) === $moi['bo_qua_co'], $moi );
$may_bat = \Phongdao\DichVu\May\DieuKhien::trang_thai();
\Phongdao\DichVu\NhapNguon\NguonTuDong::luu( $cu );
$moi_nguon = \Phongdao\DichVu\NhapNguon\NguonTuDong::tat_ca();
update_option( \Phongdao\DichVu\NhapNguon\NguonTuDong::TUY_CHON, array_map( static fn( array $n ): array => array( 'bat' => false ) + $n, $moi_nguon ), false );
$may_tat = \Phongdao\DichVu\May\DieuKhien::trang_thai();
$h_may_tat = $NC::hien( 'may', 0, '' );
update_option( \Phongdao\DichVu\NhapNguon\NguonTuDong::TUY_CHON, $moi_nguon, false );
kt( 'Máy đang làm: đọc nguồn / chọn câu báo "Không có nguồn nào bật" khi mọi nguồn tắt, có nguồn bật thì không; gợi ý theo lô không phụ thuộc nguồn', false === $may_bat['doc_nguon']['khong_nguon'] && false === $may_bat['chon_cau']['khong_nguon'] && true === $may_tat['doc_nguon']['khong_nguon'] && true === $may_tat['chon_cau']['khong_nguon'] && false === $may_tat['goi_y']['khong_nguon'] && str_contains( $h_may_tat, 'Không có nguồn nào bật' ) );
$hs_id = (int) \Phongdao\KhoLuu\HoSo::theo_khoa( 'cadao-me' )->ID;
$hs_cu = \Phongdao\KhoLuu\HoSo::truong( $hs_id );
$NC::luu_ho_so( $hs_id, array( 'ten' => 'Ca dao Mẹ', 'loai' => 'web', 'url' => 'javascript:alert(1)', 'xin_phep' => 'tu_dat', 'chinh_sach_hien' => 'tu_lieu', 'giay_phep' => 'Chưa rõ' ) );
$hs = \Phongdao\KhoLuu\HoSo::truong( $hs_id );
kt( 'Hồ sơ nguồn: lựa chọn ngoài danh sách thành trống ("chưa rõ"), đường dẫn lạ bị bỏ, không thêm giá trị mặc định', '' === $hs['xin_phep'] && '' === $hs['url'] && 'tu_lieu' === $hs['chinh_sach_hien'] && false === $hs['chep_chu_thich'], $hs );
update_post_meta( $hs_id, \Phongdao\KhoLuu\HoSo::META, wp_slash( $hs_cu ) );
$NC::luu_turnstile( array( 'site' => 'khoa-site-mau', 'bi_mat' => 'khoa-bi-mat-mau' ) );
$NC::luu_turnstile( array( 'site' => 'khoa-site-mau', 'bi_mat' => '' ) );
$giu = get_option( 'cadao_turnstile_bi_mat' );
$NC::luu_turnstile( array( 'site' => '' ) );
kt( 'Chống rác: khoá bí mật để trống thì giữ; xoá Site key thì tắt cả hai (tên tuỳ chọn giữ của MVP)', 'khoa-bi-mat-mau' === $giu && '' === get_option( 'cadao_turnstile_site' ) && '' === get_option( 'cadao_turnstile_bi_mat' ) );
$h = $NC::hien( 'nguon', 0, '' ) . $NC::hien( 'ho_so', 0, '' ) . $NC::hien( 'chong_rac', 0, '' );
kt( 'trang Nguồn & cài đặt: ba thẻ, form có nonce, danh sách hồ sơ, không hiện khoá bí mật', str_contains( $h, 'Số câu mới mỗi ngày' ) && str_contains( $h, 'Ca dao Mẹ' ) && str_contains( $h, 'Không gửi khoá bí mật cho ai' ) && substr_count( $h, 'name="_wpnonce"' ) >= 2 && ! str_contains( $h, 'khoa-bi-mat-mau' ) );
kt( 'chỉ Quản trị có quyền Nguồn & cài đặt; Biên tập không', get_role( 'administrator' )->has_cap( 'cadao_nguon' ) && ! get_role( 'editor' )->has_cap( 'cadao_nguon' ) );

do_action( 'wp_enqueue_scripts' );
kt( 'theme nạp giao-dien.css, fit.js, doc.js (hoãn, cuối trang)', wp_style_is( 'phongdao-giao-dien', 'enqueued' ) && wp_script_is( 'phongdao-fit', 'enqueued' ) && wp_script_is( 'phongdao-doc', 'enqueued' ) );

$tong = $GLOBALS['v2_dat'] + $GLOBALS['v2_hong'];
ra( "\nKẾT QUẢ: " . ( $GLOBALS['v2_hong'] ? "HỎNG {$GLOBALS['v2_hong']} / $tong" : "ĐẠT $tong" ) );
