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

// ---------- Menu Phongdao (Nguồn & cài đặt, D-094) ----------
global $menu;
$menu = is_array( $menu ) ? $menu : array();
( new \Phongdao\GiaoTiep\QuanTri\Module() )->them_menu();
kt( 'menu quản trị có mục Phongdao', in_array( 'phongdao', array_column( $menu, 2 ), true ) );
// Hộp việc trong wp-admin: chuyển sang /bien-tap/ ở bước load-… (trước khi in đầu trang), không trong hàm hiện trang
kt( 'mục Hộp việc trong wp-admin chuyển trang trước khi in đầu trang (không "headers already sent")', false !== has_action( 'load-' . get_plugin_page_hookname( 'phongdao-hop-viec', 'phongdao' ), array( \Phongdao\GiaoTiep\QuanTri\Module::class, 'toi_hop_viec' ) ) );

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
kt( 'câu đố: Gợi ý và "Hiện đáp án" thu gọn (tắt JavaScript vẫn mở được), không nhắc thiếu ý nghĩa', str_contains( $h, '<details class="answer"><summary>Hiện đáp án</summary>' ) && str_contains( $h, '<summary>Gợi ý</summary>' ) && ! str_contains( $h, 'Bài đang được bổ sung ý nghĩa' ) && ! str_contains( $h, 'data-tam="y_nghia"' ) );
wp_set_current_user( $ai_ql );
$get_truoc = $_GET;
$_GET = array( 'loc' => 'thieu-y-nghia' );
$ds_thieu_cau_do = \Phongdao\GiaoTiep\BienTap\DsTacPham::hien();
$_GET = $get_truoc;
$h_sua_cau_do = $xem( $mau_id['cau-do-mau'] );
kt( 'câu đố từ danh sách Thiếu ý nghĩa mở được nút Thêm ý nghĩa khi người biên soạn sửa', str_contains( $ds_thieu_cau_do, esc_html( get_the_title( $mau_id['cau-do-mau'] ) ) ) && str_contains( $h_sua_cau_do, 'data-tam="y_nghia"' ) && str_contains( $h_sua_cau_do, 'Thêm ý nghĩa' ) );
wp_set_current_user( 0 );
$h = $xem( $mau_id['tuc-ngu-mau-ngan'] );
kt( 'tục ngữ thật: câu là tiêu đề, Giải nghĩa chi tiết, Dùng khi 2 ví dụ', str_contains( $h, '<h1 class="read-title"><span class="verse-box"><span class="verse proverb"' ) && str_contains( $h, 'Giải nghĩa chi tiết' ) && 2 === substr_count( (string) strstr( (string) strstr( $h, '<ul class="uses">' ), '</ul>', true ), '<li>' ) );
$h = $xem( $mau_id['cau-mau-thu-hai'] );
kt( 'bài chưa có ý nghĩa: nói "đang bổ sung", không tự điền', str_contains( $h, 'Bài đang được bổ sung ý nghĩa' ) && ! str_contains( $h, 'class="meaning"' ) );
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
kt( 'đường dẫn /tim/, /bien-tap/…/ là trang cố định của plugin (không cần trang trong cơ sở dữ liệu)', $luat_co_dinh( 'tim/' ) && $luat_co_dinh( 'bien-tap/' ) && $luat_co_dinh( 'bien-tap/them/' ) && null === get_page_by_path( 'tim' ) );
$mau = array_map( static fn( $t ) => $t->slug, get_block_templates() );
kt( 'theme có mẫu trang Tìm và trang 404', in_array( 'trang-tim', $mau, true ) && in_array( '404', $mau, true ) );
$tim_id = static fn( string $q ): array => array_column( \Phongdao\DichVu\ChiMucTim\ChiMucTim::tim( $q )['ds'], 'id' );
kt( 'chỉ mục dựng từ các phần đã lưu: gõ không dấu ra bài, khớp cả cụm', $mau_id['dong-mau-mot-cua-bai'] === ( $tim_id( 'dong mau mot cua bai nay' )[0] ?? 0 ) && ( \Phongdao\DichVu\ChiMucTim\ChiMucTim::tim( 'dong mau mot cua bai nay' )['ds'][0]['lien'] ?? false ) );
kt( 'tìm cả trong chú thích và ý nghĩa', in_array( $mau_id['dong-mau-mot-cua-bai'], $tim_id( 'chu thich tong hop' ), true ) && in_array( $mau_id['tuc-ngu-mau-ngan'], $tim_id( 'giai nghia tong hop' ), true ) );
wp_update_post( array( 'ID' => $mau_id['cau-do-mau'], 'post_status' => 'draft' ) );
$an = ! in_array( $mau_id['cau-do-mau'], $tim_id( 'cau do mau dong hai' ), true );
wp_update_post( array( 'ID' => $mau_id['cau-do-mau'], 'post_status' => 'publish' ) );
kt( 'bài thôi đăng ra khỏi kết quả; đăng lại thì có', $an && in_array( $mau_id['cau-do-mau'], $tim_id( 'cau do mau dong hai' ), true ) );
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
$kq = $rest( "/tac-pham/$tp1/trang-thai", array( 'trang_thai' => 'private' ) );
kt( 'Ẩn bài: bài thôi hiện với người đọc, lịch sử ghi lại', ( $kq->get_data()['ok'] ?? false ) && 'private' === get_post_status( $tp1 ) && 'trang_thai' === ( Viec::lich_su( 'post', $tp1, 1 )[0]['phan'] ?? '' ) );
$rest( "/tac-pham/$tp1/trang-thai", array( 'trang_thai' => 'publish' ) );
kt( 'Đăng lại (cần quyền duyệt)', 'publish' === get_post_status( $tp1 ) );

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
kt( 'theme có mẫu bốn trang biên tập', array() === array_diff( array( 'trang-bien-tap', 'trang-bien-tap-tac-pham', 'trang-bien-tap-them', 'trang-bien-tap-so-lieu' ), $mau ) );
wp_set_current_user( 0 );
kt( 'khách: các khối trang biên tập không in gì', '' === trim( do_blocks( '<!-- wp:cadao/hop-viec /--><!-- wp:cadao/ds-tac-pham /--><!-- wp:cadao/them-bai /--><!-- wp:cadao/so-lieu-tuan /-->' ) ) );
kt( 'khách: REST kiểm trùng bị chặn (401)', 401 === $rest( '/trung/kiem', array( 'loi' => 'x' ) )->get_status() );
kt( 'khách: đầu trang có "Đã lưu", không có Hộp việc', str_contains( do_blocks( '<!-- wp:cadao/dau-trang /-->' ), 'Đã lưu' ) && ! str_contains( do_blocks( '<!-- wp:cadao/dau-trang /-->' ), 'Hộp việc' ) );
wp_set_current_user( $ai_ql );
kt( 'người biên soạn: đầu trang có "Hộp việc" kèm số việc chờ', str_contains( do_blocks( '<!-- wp:cadao/dau-trang /-->' ), 'Hộp việc <span class="dem">' . Viec::dem_cho() . '</span>' ) );
$h = do_blocks( '<!-- wp:cadao/hop-viec /-->' );
kt( 'Hộp việc: tiêu đề, lọc, form có nonce và phiên thẻ', str_contains( $h, 'Hộp việc <span class="dem-lon">' . Viec::dem_cho() . '</span>' ) && str_contains( $h, 'class="loc-viec"' ) && str_contains( $h, 'action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"' ) && str_contains( $h, 'name="_wpnonce"' ) && str_contains( $h, 'name="phien" value="0"' ) && str_contains( $h, 'name="quyet" value="duyet"' ), substr( $h, 0, 300 ) );
kt( 'Hộp việc: thẻ nháp ý nghĩa ghi rõ chưa duyệt; thẻ có thể trùng đặt hai lời cạnh nhau', str_contains( $h, 'AI · chưa duyệt' ) && str_contains( $h, 'class="hai-cot"' ) );
$get_cu = $_GET;
$dem_cu = Viec::dem_cho();
$ids_nhieu = array();
for ( $i = 0; $i < 1010; $i++ ) {
	$ids_nhieu[] = Viec::tao( array( 'loai' => 'can_xem', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'noi_dung' => array( 'ly_do' => array() ), 'nguoi_tao' => 'kiem_thu' ) );
}
$_GET = array();
$h_nhieu = \Phongdao\GiaoTiep\BienTap\HopViec::hien();
$so_trang = (int) ceil( ( $dem_cu + 1010 ) / 30 );
$_GET = array( 'loai' => 'can_xem' );
$h_loc_nhieu = \Phongdao\GiaoTiep\BienTap\HopViec::hien();
$dem_loc_nhieu = Viec::dem( Viec::CHO, 'can_xem' );
$_GET = array( 'trang' => (string) $so_trang );
$h_cuoi = \Phongdao\GiaoTiep\BienTap\HopViec::hien();
$_GET = array( 'tung' => '1', 'vi' => (string) ( $dem_cu + 1009 ) );
$h_the_cuoi = \Phongdao\GiaoTiep\BienTap\HopViec::hien();
$_GET = array( 'loai' => 'can_xem', 'tung' => '1', 'vi' => (string) $dem_loc_nhieu );
$h_het_luot = \Phongdao\GiaoTiep\BienTap\HopViec::hien();
$kiem_trang = array(
	'so_tao'       => 1010 === count( array_filter( $ids_nhieu ) ),
	'tong'         => $dem_cu + 1010 === Viec::dem_cho(),
	'tieu_de'      => str_contains( $h_nhieu, 'Hộp việc <span class="dem-lon">' . ( $dem_cu + 1010 ) . '</span>' ),
	'trang_dau'    => 30 === substr_count( $h_nhieu, 'class="the-viec ' ) && str_contains( $h_nhieu, 'Trang 1 / ' . $so_trang ),
	'loc_loai'     => $dem_loc_nhieu >= 1010 && str_contains( $h_loc_nhieu, 'Cần xem <span class="dem">' . $dem_loc_nhieu . '</span>' ) && 30 === substr_count( $h_loc_nhieu, 'data-loai="can_xem"' ),
	'trang_cuoi'   => str_contains( $h_cuoi, 'Trang ' . $so_trang . ' / ' . $so_trang ) && ! str_contains( $h_cuoi, '>Trang sau</a>' ),
	'the_cuoi'     => str_contains( $h_the_cuoi, 'Thẻ ' . ( $dem_cu + 1010 ) . ' / ' . ( $dem_cu + 1010 ) ) && ! str_contains( $h_the_cuoi, '>Thẻ sau</a>' ),
	'het_luot'     => str_contains( $h_het_luot, 'Đã đến cuối lượt duyệt' ) && str_contains( $h_het_luot, 'Duyệt lại từ đầu' ) && ! str_contains( $h_het_luot, 'class="the-viec ' ),
);
kt( 'Hộp việc hơn 1.000 thẻ: số đếm đủ, lọc loại đúng, trang đầu tối đa 30, đến được trang và thẻ cuối', ! in_array( false, $kiem_trang, true ), $kiem_trang );
$id_de_sau = Viec::tao( array( 'loai' => 'can_xem', 'trang_thai' => Viec::DE_SAU, 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $tp1, 'noi_dung' => array( 'ly_do' => array() ), 'nguoi_tao' => 'kiem_thu' ) );
$_GET = array( 'tt' => Viec::DE_SAU, 'loai' => 'can_xem' );
$h_de_sau = \Phongdao\GiaoTiep\BienTap\HopViec::hien();
kt( 'Hộp việc: tìm lại Để sau và không có nút Để sau lặp', $id_de_sau > 0 && str_contains( $h_de_sau, 'Để sau <span class="dem">' . Viec::dem( Viec::DE_SAU ) . '</span>' ) && str_contains( $h_de_sau, 'id="viec-' . $id_de_sau . '"' ) && ! str_contains( $h_de_sau, 'name="quyet" value="de_sau"' ) );
foreach ( $ids_nhieu as $id_nhieu ) {
	Viec::xoa( $id_nhieu );
}
Viec::xoa( $id_de_sau );
$_GET = $get_cu;
$v_nhap = Viec::mo( 'post', $tp1, 'nhap_y_nghia' )[0];
$q      = QuyetViec::quyet( $v_nhap['id'], 'duyet', $ai_ql, true );
kt( 'quyết "Duyệt" nháp ý nghĩa: vào bài với dấu AI, việc đóng', $q['ok'] && 'Ý nghĩa nháp mẫu.' === PhanBai::lay( $tp1, 'y_nghia' )['chu'] && NguoiLam::AI_DA_DUYET === PhanBai::lay( $tp1, 'y_nghia' )['nguoi_lam'] && array() === Viec::mo( 'post', $tp1, 'nhap_y_nghia' ), $q );
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
$h_hoan = \Phongdao\GiaoTiep\BienTap\HopViec::hien();
$_GET = $get_cu;
$q_phuc = QuyetViec::hoan_tac( $v_hoan, $da_quyet_hoan['phien'], $ai_ql );
$sau_hoan = Viec::lay( $v_hoan );
$ls_hoan = Viec::lich_su( 'viec', $v_hoan );
kt( 'Hoàn tác quyết thẻ đổi trạng thái: có nút 10 giây, trả về Chờ và ghi nhật ký mới', $q_hoan['ok'] && str_contains( $h_hoan, 'Hoàn tác quyết định thẻ' ) && $q_phuc['ok'] && Viec::CHO === $sau_hoan['trang_thai'] && 2 === $sau_hoan['phien'] && '' === $sau_hoan['quyet'] && 1 === count( $ls_hoan ) && 'hoan_tac' === $ls_hoan[0]['quyet'] && Viec::XONG === $ls_hoan[0]['truoc']['trang_thai'] && Viec::CHO === $ls_hoan[0]['sau']['trang_thai'], array( 'q' => $q_phuc, 'viec' => $sau_hoan ) );
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
$q1 = QuyetViec::quyet( (int) ( $gy_v[1]['id'] ?? 0 ), 'dong_y', $ai_ql, true );
$q2 = QuyetViec::quyet( (int) ( $gy_v[0]['id'] ?? 0 ), 'bo', $ai_ql, true );
kt( 'Đồng ý → gắn vùng; Bỏ → không gắn; tính lại không tạo lại thẻ đã quyết', $q1['ok'] && $q2['ok'] && has_term( 'nam-bo', 'vung', $gy_bai ) && ! has_term( 'hat-ru', 'chu_de', $gy_bai ) && 0 === \Phongdao\DichVu\GoiY\GoiY::ghi( $gy_bai ) && array() === Viec::mo( 'post', $gy_bai, 'goi_y' ), array( $q1, $q2 ) );
$gy_bai2 = wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => 'Ví dầu đã duyệt mẫu', 'post_name' => 'vi-dau-da-duyet-mau' ) );
wp_set_object_terms( $gy_bai2, 'ca-dao', KhaiBao::THE_LOAI );
Viec::tao( array( 'loai' => 'goi_y', 'trang_thai' => Viec::XONG, 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $gy_bai2, 'noi_dung' => array( 'loai' => '*', 'ma' => '*' ), 'nguoi_tao' => 'nhap_cu', 'quyet' => 'da_duyet_mvp' ) );
LuuPhan::luu( $gy_bai2, 'loi', array( 'dong' => array( 'Ví dầu mẫu đã duyệt', 'Dòng hai mẫu' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_gy );
kt( 'bài đã duyệt gợi ý trên bản cũ (việc đã quyết "*"): không gợi ý lại', array() === Viec::mo( 'post', $gy_bai2, 'goi_y' ) );
wp_delete_post( $gy_bai, true );
wp_delete_post( $gy_bai2, true );
$h = do_blocks( '<!-- wp:cadao/ds-tac-pham /-->' );
kt( 'trang Tác phẩm: có cả bản nháp, lọc theo thiếu ý nghĩa / bản nháp / có cờ, ô tìm', str_contains( $h, 'Bản nháp' ) && str_contains( $h, 'Thiếu ý nghĩa' ) && str_contains( $h, 'name="q"' ) && str_contains( $h, esc_html( get_the_title( $tp1 ) ) ) );
$_GET['loc'] = 'ban-nhap';
$h           = do_blocks( '<!-- wp:cadao/ds-tac-pham /-->' );
unset( $_GET['loc'] );
kt( 'lọc "Bản nháp": chỉ bài chưa đăng', str_contains( $h, 'tt-nhap' ) && ! str_contains( $h, 'tt-ok' ) );
$them = ThemBai::tao( array( 'the_loai' => 'tuc-ngu', 'loi' => "Câu thêm tay mẫu một dòng\r\n", 'ten' => '', 'nguon' => 'https://example.org/sach-mau', 'dang' => false ), $ai_ql );
$pt   = PhanBai::lay( $them['bai'], 'loi' );
kt( 'Thêm bài: bản nháp, tên lấy dòng đầu, lời người viết, nguồn là đường dẫn người gõ', $them['ok'] && 'draft' === get_post_status( $them['bai'] ) && 'Câu thêm tay mẫu một dòng' === get_the_title( $them['bai'] ) && NguoiLam::NGUOI_VIET === $pt['nguoi_lam'] && 'https://example.org/sach-mau' === ( $pt['nguon']['url'] ?? '' ) && has_term( 'tuc-ngu', KhaiBao::THE_LOAI, $them['bai'] ), $them );
kt( 'Thêm bài: không có lời → báo, không tạo bài', ! ThemBai::tao( array( 'the_loai' => 'ca-dao', 'loi' => " \n ", 'ten' => '', 'nguon' => '', 'dang' => false ), $ai_ql )['ok'] );
$kt = ThemBai::kiem( "Dòng một mẫu\nDòng hai mẫu" );
kt( 'Thêm bài: máy nhận thể thơ, báo trùng y hệt với tác phẩm có sẵn (kèm đường dẫn)', 2 === $kt['so_dong'] && '' !== $kt['the_tho'] && 'Trùng y hệt' === ( $kt['trung'][0]['muc'] ?? '' ) && (string) get_permalink( $tp1 ) === ( $kt['trung'][0]['url'] ?? '' ), $kt );
$h = do_blocks( '<!-- wp:cadao/them-bai /-->' );
kt( 'trang Thêm bài: form gửi admin-post có nonce, 5 thể loại, Lưu nháp / Đăng ngay', str_contains( $h, 'data-them-bai' ) && str_contains( $h, 'name="_wpnonce"' ) && 5 === substr_count( $h, 'name="the_loai"' ) && str_contains( $h, 'Đăng ngay' ) );
$h = do_blocks( '<!-- wp:cadao/so-lieu-tuan /-->' );
kt( 'Số liệu tuần: số bài, việc chờ, theo thể loại, theo cấp L0–L3, việc nên làm', str_contains( $h, 'tác phẩm đã đăng' ) && str_contains( $h, 'việc đang chờ' ) && str_contains( $h, 'Theo cấp chất lượng' ) && str_contains( $h, 'Nên làm tuần này' ) );

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
$kq = \Phongdao\DichVu\XepViec\QuyetViec::quyet( (int) $v_bl['id'], 'duyet', $ai_ql, true );
wp_set_current_user( 0 );
$h = TacPham::hien( (array) \Phongdao\DichVu\DungTrang\DocBai::tac_pham( get_post( $bai_g ) ) );
kt( 'người biên soạn bấm Hiện → cảm nghĩ lên trang kèm tên hiển thị và "Báo vi phạm"', $kq['ok'] && str_contains( $h, 'Cảm nghĩ mẫu của bạn đọc.' ) && str_contains( $h, 'Bút danh mẫu' ) && str_contains( $h, 'loai=vi_pham' ), $kq );
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
kt( 'trang bài liệt kê bài cảm nhận đã duyệt (bài chờ duyệt thì không)', str_contains( $h, 'Cảm nhận – phân tích' ) && str_contains( $h, 'Một cách đọc bài mẫu' ) && ! str_contains( $h, 'Cảm nhận mẫu</a>' ) );

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
kt( 'Số liệu tuần: lượt đọc 7 ngày, bài đọc nhiều, người đọc đến từ, câu tìm chưa có', str_contains( $h_sl = \Phongdao\GiaoTiep\BienTap\SoLieuTuan::hien(), 'Lượt đọc 7 ngày: 3' ) && str_contains( $h_sl, 'facebook.com' ) && str_contains( $h_sl, 'câu chưa có mẫu' ), $h_sl );
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

// ---------- Nguồn & cài đặt (wp-admin) ----------
$NC  = \Phongdao\GiaoTiep\QuanTri\NguonCaiDat::class;
$cu  = \Phongdao\DichVu\NhapNguon\NguonTuDong::lay( 'cadao-me' );
$NC::luu_nguon( 'cadao-me', array( 'bat' => '1', 'so_bai_moi_ngay' => '99', 'loai_lay' => array( 'ca_dao', 'la' ), 'bo_tho_tuc' => '1' ) );
$moi = \Phongdao\DichVu\NhapNguon\NguonTuDong::lay( 'cadao-me' );
kt( 'Nguồn tự động: bật / tắt, số câu mỗi ngày giới hạn 1–50, chỉ nhận loại đã biết, tắt chép chú thích, bỏ câu thô tục', true === $moi['bat'] && 50 === $moi['so_bai_moi_ngay'] && array( 'ca_dao' ) === $moi['loai_lay'] && false === $moi['chep_chu_thich'] && array( 'tho_tuc' ) === $moi['bo_qua_co'], $moi );
\Phongdao\DichVu\NhapNguon\NguonTuDong::luu( $cu );
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
