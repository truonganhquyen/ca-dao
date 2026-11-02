<?php
/**
 * Kiểm thử V2 trong WordPress Playground (plugin + theme V2 đã bật): `npm run test:v2`.
 * Dữ liệu tổng hợp — không chứa nội dung thật của nguồn (repo này công khai).
 */

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

use Phongdao\DichVu\DungTrang\DuLieuMau;
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
$thieu = array_values( array_diff( array( 'single-tac_pham', 'archive-tac_pham', 'taxonomy' ), $mau ) );
kt( 'theme có mẫu trang tác phẩm, Tất cả, trang phân loại', array() === $thieu, $thieu );

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
kt( 'câu đố: Gợi ý và "Hiện đáp án" thu gọn (tắt JavaScript vẫn mở được), không nhắc thiếu ý nghĩa', str_contains( $h, '<details class="answer"><summary>Hiện đáp án</summary>' ) && str_contains( $h, '<summary>Gợi ý</summary>' ) && ! str_contains( $h, 'Bài đang được bổ sung ý nghĩa' ) );
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

do_action( 'wp_enqueue_scripts' );
kt( 'theme nạp giao-dien.css, fit.js, doc.js (hoãn, cuối trang)', wp_style_is( 'phongdao-giao-dien', 'enqueued' ) && wp_script_is( 'phongdao-fit', 'enqueued' ) && wp_script_is( 'phongdao-doc', 'enqueued' ) );

$tong = $GLOBALS['v2_dat'] + $GLOBALS['v2_hong'];
ra( "\nKẾT QUẢ: " . ( $GLOBALS['v2_hong'] ? "HỎNG {$GLOBALS['v2_hong']} / $tong" : "ĐẠT $tong" ) );
