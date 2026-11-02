<?php
/**
 * Kiểm thử V2 trong WordPress Playground (plugin + theme V2 đã bật): `npm run test:v2`.
 * Dữ liệu tổng hợp — không chứa nội dung thật của nguồn (repo này công khai).
 */

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

use Phongdao\DichVu\DungTrang\DuLieuMau;
use Phongdao\GiaoTiep\Khoi\TacPham;
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
$dau = do_blocks( '<!-- wp:template-part {"slug":"dau-trang","theme":"ca-dao","tagName":"div"} /-->' );
kt( 'phần đầu trang (template part) hiện logo Phongdao, không lồng hai thẻ header', str_contains( $dau, '<span>Phongdao</span>' ) && 1 === substr_count( $dau, '<header' ), substr( $dau, 0, 200 ) );
kt( 'không in mã emoji (không gọi máy chủ ngoài)', false === has_action( 'wp_head', 'print_emoji_detection_script' ) );

// ---------- Giao diện người đọc: khối cadao/* ----------
$dang_ky = WP_Block_Type_Registry::get_instance();
$co_khoi = array_values( array_filter( array( 'cadao/dau-trang', 'cadao/chan-trang', 'cadao/tac-pham' ), static fn( $k ) => ! $dang_ky->is_registered( $k ) ) );
kt( 'đã đăng ký khối cadao/dau-trang, cadao/chan-trang, cadao/tac-pham', array() === $co_khoi, $co_khoi );
kt( 'theme có mẫu tác phẩm (tac-pham-mau)', in_array( 'tac-pham-mau', array_map( static fn( $t ) => $t->slug, get_block_templates() ), true ) );

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
kt( 'công cụ đọc: Trích dẫn, Lưu, Chia sẻ, Cỡ chữ; không có nút chưa chạy (Trình chiếu)', str_contains( $h, 'data-action="cite"' ) && str_contains( $h, 'data-action="save"' ) && str_contains( $h, 'data-action="share"' ) && ! str_contains( $h, 'data-action="present"' ) );
kt( 'người đọc không nhận giao diện sửa, không thấy nguồn đã ẩn', ! preg_match( '/nut-sua|editor-strip|dai-bien-tap|tam-sua|Nguồn đã ẩn:/', $h ) );

$tn = do_blocks( '<!-- wp:cadao/tac-pham {"mau":"tuc-ngu"} /-->' );
kt( 'tục ngữ: câu là tiêu đề, dạng câu một dòng, có Giải nghĩa chi tiết và Dùng khi', str_contains( $tn, '<h1 class="read-title"><span class="verse-box"><span class="verse proverb"' ) && str_contains( $tn, 'Giải nghĩa chi tiết' ) && str_contains( $tn, 'Dùng khi' ) );
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

do_action( 'wp_enqueue_scripts' );
kt( 'theme nạp giao-dien.css, fit.js, doc.js (hoãn, cuối trang)', wp_style_is( 'phongdao-giao-dien', 'enqueued' ) && wp_script_is( 'phongdao-fit', 'enqueued' ) && wp_script_is( 'phongdao-doc', 'enqueued' ) );

$tong = $GLOBALS['v2_dat'] + $GLOBALS['v2_hong'];
ra( "\nKẾT QUẢ: " . ( $GLOBALS['v2_hong'] ? "HỎNG {$GLOBALS['v2_hong']} / $tong" : "ĐẠT $tong" ) );
