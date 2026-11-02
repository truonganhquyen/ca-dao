<?php
/**
 * Kiểm thử V2 trong WordPress Playground (plugin + theme V2 đã bật): `npm run test:v2`.
 * Dữ liệu tổng hợp — không chứa nội dung thật của nguồn (repo này công khai).
 */

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

use Phongdao\HaTang\CaiDat;
use Phongdao\HaTang\KhoiDong;

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

// ---------- Bàn biên tập (menu) ----------
global $menu;
$menu = is_array( $menu ) ? $menu : array();
( new \Phongdao\GiaoTiep\QuanTri\Module() )->them_menu();
kt( 'menu quản trị có mục Phongdao', in_array( 'phongdao', array_column( $menu, 2 ), true ) );

// ---------- Theme ----------
$mau = array_map( static fn( $t ) => $t->slug, get_block_templates() );
kt( 'theme có mẫu index và page', in_array( 'index', $mau, true ) && in_array( 'page', $mau, true ), $mau );
$dau = do_blocks( '<!-- wp:template-part {"slug":"dau-trang","theme":"ca-dao"} /-->' );
kt( 'phần đầu trang hiện tên site', str_contains( $dau, esc_html( get_bloginfo( 'name' ) ) ), substr( $dau, 0, 300 ) );
kt( 'không in mã emoji (không gọi máy chủ ngoài)', false === has_action( 'wp_head', 'print_emoji_detection_script' ) );

$tong = $GLOBALS['v2_dat'] + $GLOBALS['v2_hong'];
ra( "\nKẾT QUẢ: " . ( $GLOBALS['v2_hong'] ? "HỎNG {$GLOBALS['v2_hong']} / $tong" : "ĐẠT $tong" ) );
