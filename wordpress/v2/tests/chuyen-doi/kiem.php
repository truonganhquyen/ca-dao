<?php
/**
 * Phép thử chuyển đổi, bước 2 (docs/29 mục 4): V2 đã thay chỗ MVP trên cùng cơ sở dữ liệu, như ngày chuyển. Kiểm
 * kiểm kê, nhập thử (không ghi gì, đúng cách nhập từng loại bài, "trước = sau") và tệp chạy thử đã chạy ở bước 1
 * khi MVP còn bật. Dữ liệu tổng hợp.
 */

require_once '/wordpress/wp-load.php';

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\DichVu\NhapDuLieuCu\DuLieuCu;
use Phongdao\DichVu\NhapDuLieuCu\KiemKe;
use Phongdao\DichVu\NhapDuLieuCu\NhapLam;
use Phongdao\DichVu\NhapDuLieuCu\NhapThu;
use Phongdao\GiaoTiep\Khoi\TacPham;
use Phongdao\KhoLuu\HoSo;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;

const CD_TEP_KQ = '/repo/tests/.ket-qua-chuyen-doi.txt';
file_put_contents( CD_TEP_KQ, '' );
function ra( string $dong ): void {
	file_put_contents( CD_TEP_KQ, $dong . "\n", FILE_APPEND );
}
register_shutdown_function(
	static function (): void {
		$loi = error_get_last();
		if ( $loi && in_array( $loi['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR ), true ) ) {
			ra( "LỖI PHP: {$loi['message']} ({$loi['file']}:{$loi['line']})" );
		}
	}
);
$GLOBALS['cd_dat']  = 0;
$GLOBALS['cd_hong'] = 0;
function kt( string $ten, bool $dk, $chi_tiet = null ): void {
	if ( $dk ) {
		++$GLOBALS['cd_dat'];
		ra( "ĐẠT   [chuyển đổi] $ten" );
		return;
	}
	++$GLOBALS['cd_hong'];
	ra( "HỎNG  [chuyển đổi] $ten" . ( null !== $chi_tiet ? ' — ' . var_export( $chi_tiet, true ) : '' ) );
}
/** Các dòng giữa hai tiêu đề của một báo cáo. */
function doan( string $bc, string $tu, string $den ): string {
	$a = strpos( $bc, $tu );
	$b = false === $a ? false : strpos( $bc, $den, $a );
	return false === $a || false === $b ? '' : substr( $bc, $a, $b - $a );
}

global $wpdb;
$mvp = json_decode( (string) file_get_contents( '/repo/tests/.chuyen-doi-mvp.json' ), true ) ?: array();
$id  = $mvp['id'] ?? array();
kt( 'bước 1: tạo dữ liệu mẫu bằng mã MVP không lỗi', array() === ( $mvp['loi'] ?? array( 'không có tệp kết quả bước 1' ) ) && 14 === count( $id ) && ! empty( $mvp['the']['tu'] ), $mvp['loi'] ?? null );
kt( 'V2 đã thay chỗ MVP: plugin V2 chạy, bài MVP còn nguyên loại bài cũ', class_exists( \Phongdao\HaTang\KhoiDong::class ) && ! function_exists( 'cadao_kho_luu' ) && 'cdtn' === get_post_type( $id['a'] ?? 0 ) );
kt( 'chưa nhập xong: khách thấy trang "Thư viện đang được nâng cấp"', NhapLam::dang_nang_cap() );

// ---------- Tệp chạy thử trên site MVP ----------
$tn = (string) ( $mvp['thu_nhap'] ?? '' );
kt( 'tệp chạy thử (cong-cu/thu-nhap.php) chạy được khi MVP còn bật: in kiểm kê và nhập thử', str_contains( $tn, '== Kiểm kê dữ liệu MVP' ) && str_contains( $tn, '== Nhập thử' ), $tn );
kt( 'tệp chạy thử không ghi gì: số dòng mọi bảng và tuỳ chọn như trước', ! empty( $mvp['truoc'] ) && $mvp['truoc'] === $mvp['sau'], array_diff_assoc( (array) ( $mvp['sau'] ?? array() ), (array) ( $mvp['truoc'] ?? array() ) ) );
kt( 'báo cáo không chép lời', ! str_contains( $tn, 'Dòng mẫu một của bài' ) && ! str_contains( $tn, 'Lời giải' ) && ! str_contains( $tn, 'Ý nghĩa mẫu' ) );

// ---------- Kiểm kê ----------
$kk = KiemKe::dem();
$so = array();
foreach ( $kk['dong'] as [ , $viec, $n ] ) {
	$so[ $viec ] = $n;
}
$mong = array(
	'đã đăng'                                   => 13,
	'chờ duyệt (máy tạo)'                       => 1,
	'bài có dấu băm, chưa sửa tay'              => 5,
	'bài có dấu băm, đã sửa tay'                => 1,
	'bài không có dấu băm (nhập tay, site cũ)'  => 8,
	'dị bản thêm tay (_cadao_di_ban_them)'      => 1,
	'bài còn trong khối Đoạn văn (chưa có khối Thơ)' => 1,
	'câu đã gộp vào bài'                        => 1,
	'Hiểu nhanh đã lên trang'                   => 1,
	'có dấu "hỗ trợ AI"'                        => 1,
	'nháp Hiểu nhanh của máy'                   => 1,
	'bài có nguồn người điền'                   => 1,
	'bài có "Lời đã đối chiếu với"'             => 1,
	'bài có tên đuôi số (-2, -3…)'              => 1,
	'cảm nghĩ (bình luận trên bài)'             => 1,
);
kt( 'kiểm kê đếm đúng từng loại dữ liệu mẫu', array() === array_diff_assoc( $mong, $so ), array_intersect_key( $so, array_diff_assoc( $mong, $so ) ) );
ra( 'THÔNG TIN [chuyển đổi] khoá chưa có trong bảng kiểm kê: ' . wp_json_encode( $kk['la'], JSON_UNESCAPED_UNICODE ) );
kt( 'không có khoá meta bài MVP nào nằm ngoài bảng kiểm kê', array() === $kk['la']['meta bài'], $kk['la']['meta bài'] );
kt( 'kiểm kê chạy như nhau khi MVP còn bật và khi V2 đã thay chỗ', doan( $tn, '-- Tác phẩm --', '-- Khoá chưa có' ) === doan( implode( "\n", KiemKe::in() ), '-- Tác phẩm --', '-- Khoá chưa có' ) );

// ---------- Nhập thử ----------
$dem = static function () use ( $wpdb ): array {
	$ra = array();
	foreach ( preg_grep( '/^wp_/', $wpdb->get_col( 'SHOW TABLES' ) ) as $bang ) {
		$ra[ $bang ] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `$bang`" );
	}
	return $ra;
};
$truoc = $dem();
$bc    = NhapThu::chay( true );
kt( 'nhập thử không ghi gì vào cơ sở dữ liệu', $truoc === $dem(), array_diff_assoc( $dem(), $truoc ) );
kt( 'cách nhập: 4 từ kho chưa sửa, 1 đã sửa tay, 8 không có câu trong kho, 1 chờ duyệt máy tạo không mang', array( 'kho_chua_sua' => 4, 'sua_tay' => 1, 'khong_kho' => 8, 'khong_mang' => 1 ) === $bc['cach'], $bc['cach'] );
$cadao_me = (int) ( HoSo::theo_khoa( 'cadao-me' )?->ID ?? 0 );
$a        = $bc['bai'][ $id['a'] ] ?? array();
kt(
	'bài từ kho: lời, dị bản (kể cả câu đã gộp), chú thích là máy chép, gắn hồ sơ Ca dao Mẹ',
	$cadao_me && 'may_chep' === ( $a['phan']['loi']['nguoi_lam'] ?? '' ) && $cadao_me === ( $a['phan']['loi']['nguon']['ho_so'] ?? 0 ) && 2 === count( $a['phan']['di_ban']['muc'] ?? array() ) && array( 'may_chep' ) === array_values( array_unique( array_column( $a['phan']['di_ban']['muc'] ?? array(), 'nguoi_lam' ) ) ) && 'may_chep' === ( $a['phan']['chu_thich']['muc'][0]['nguoi_lam'] ?? '' ),
	$a['phan'] ?? null
);
kt( 'bài từ kho: trước = sau từng ký tự, không cần xem', array( 'khop' ) === array_values( array_unique( $a['so_sanh'] ?? array( 'x' ) ) ) && array() === ( $a['can_xem'] ?? array( 'x' ) ), $a );
kt( 'bài đã sửa tay lời → lời người viết', 'nguoi_viet' === ( $bc['bai'][ $id['b'] ]['phan']['loi']['nguoi_lam'] ?? '' ) && str_contains( implode( "\n", $bc['bai'][ $id['b'] ]['phan']['loi']['dong'] ?? array() ), 'Dòng đã sửa tay' ) );
$c = $bc['bai'][ $id['c'] ] ?? array();
kt( 'câu đố: câu hỏi, đáp án tách riêng, máy chép, trước = sau', 'Là cái gì?' === ( $c['phan']['cau_hoi']['chu'] ?? '' ) && 'Cái mẫu' === ( $c['phan']['dap_an']['chu'] ?? '' ) && 'khop' === ( $c['so_sanh']['dap_an'] ?? '' ) && array() === ( $c['can_xem'] ?? array( 'x' ) ), $c );
$d = $bc['bai'][ $id['d'] ] ?? array();
kt( 'tục ngữ: Hiểu nhanh có dấu AI → ý nghĩa ai_da_duyet, thể loại tục ngữ', array( 'tuc-ngu' ) === ( $d['the_loai'] ?? null ) && 'ai_da_duyet' === ( $d['phan']['y_nghia']['nguoi_lam'] ?? '' ) && true === ( $d['phan']['y_nghia']['ai'] ?? null ), $d['phan'] ?? null );
kt( 'nháp Hiểu nhanh → việc chờ, không vào bài', array( 'chu' => 'Nháp ý nghĩa mẫu.', 'ai' => true ) === ( $bc['bai'][ $id['n'] ]['nhap'] ?? null ) && ! isset( $bc['bai'][ $id['n'] ]['phan']['y_nghia'] ) );
kt( 'vè còn trong khối Đoạn văn → lời lấy theo dòng, đúng từng chữ', array( 'Vè mẫu dòng một', 'Vè mẫu dòng hai' ) === ( $bc['bai'][ $id['g'] ]['phan']['loi']['dong'] ?? null ) && array() === ( $bc['bai'][ $id['g'] ]['can_xem'] ?? array( 'x' ) ), $bc['bai'][ $id['g'] ] ?? null );
$h = $bc['bai'][ $id['h'] ] ?? array();
kt( 'nguồn người điền → hồ sơ mới + nguồn của chú thích người viết; đối chiếu → hồ sơ sách + trang', 'nguoi_viet' === ( $h['phan']['chu_thich']['muc'][0]['nguoi_lam'] ?? '' ) && 'https://example.org/sach-mau' === ( $h['phan']['chu_thich']['muc'][0]['nguon']['url'] ?? '' ) && '12' === ( $h['phan']['loi']['doi_chieu']['trang'] ?? '' ) && in_array( array( 'web', 'Sách mẫu người điền' ), $bc['ho_so_moi'], true ) && in_array( array( 'sach', 'Tuyển tập mẫu' ), $bc['ho_so_moi'], true ), $h );
kt( 'tên đường dẫn đuôi số → tên mới thêm chữ của dòng sau; bài cùng tên kia giữ tên', str_ends_with( (string) ( $mvp['ten_i2'] ?? '' ), '-2' ) && 'cau-trung-ten-mau-dong' === ( $bc['bai'][ $id['i2'] ]['ten_moi'] ?? null ) && array_key_exists( 'ten_moi', $bc['bai'][ $id['i1'] ] ?? array() ) && null === $bc['bai'][ $id['i1'] ]['ten_moi'], $bc['doi_ten'] );
$vp = $bc['bai'][ $id['vp'] ] ?? array();
kt(
	'vè nhiều phần: lời gồm mọi phần, mỗi tiêu đề phụ một đoạn, dòng chú đầu bài thành bối cảnh; dòng "(Nguồn: …)" cần xem, không gắn vào lời',
	array( 'Vè phần một dòng một', 'Vè phần một dòng hai', '', 'Vè phần hai dòng một', 'Vè phần hai dòng hai' ) === ( $vp['phan']['loi']['dong'] ?? null )
		&& array( array( 'tieu_de' => 'Vè mẫu (I)', 'tu_dong' => 1, 'den_dong' => 2 ), array( 'tieu_de' => 'Vè mẫu (II)', 'tu_dong' => 3, 'den_dong' => 4 ) ) === ( $vp['phan']['doan']['muc'] ?? null )
		&& '(Bối cảnh mẫu của vè)' === ( $vp['phan']['boi_canh']['chu'] ?? '' ) && ! isset( $vp['phan']['loi']['nguon'] ) && array( 'dòng «(Nguồn: Sách mẫu cuối bài)» không mang sang: nếu là sách đã đối chiếu, ghi ở Sửa lời' ) === ( $vp['can_xem'] ?? null ),
	$vp
);
$vd = $bc['bai'][ $id['vd'] ] ?? array();
kt(
	'vè được MVP chèn "Dị bản" lên trên tiêu đề phụ đầu: dị bản đúng là câu đã thêm, các phần vẫn là lời, cần xem',
	array( array( 'Dị bản gộp vào mẫu', 'Dòng hai dị bản gộp' ) ) === array_column( $vd['phan']['di_ban']['muc'] ?? array(), 'dong' ) && 2 === count( $vd['phan']['doan']['muc'] ?? array() ) && 'Vè đầu phần một' === ( $vd['phan']['loi']['dong'][0] ?? '' )
		&& in_array( 'tiêu đề «Dị bản» đứng trước lời: khối Thơ ngay dưới nhập làm dị bản', $vd['can_xem'] ?? array(), true ),
	$vd
);
$in = implode( "\n", NhapThu::in( $bc ) );
kt( 'nhập thử khi MVP còn bật và khi V2 đã thay chỗ cho cùng kết quả', doan( $tn, 'Cách nhập:', '-- Cần xem' ) === doan( $in, 'Cách nhập:', '-- Cần xem' ) && '' !== doan( $in, 'Cách nhập:', '-- Cần xem' ), array( doan( $tn, 'Cách nhập:', '-- Cần xem' ), doan( $in, 'Cách nhập:', '-- Cần xem' ) ) );
ra( "THÔNG TIN [chuyển đổi] báo cáo nhập thử:\n" . $in );

// ---------- Nhập thật → hoàn tác → nhập lại ----------
/** Các chỗ khác nhau giữa hai mảng: "đường.dẫn: trước ≠ sau" (tối đa 12). */
function cd_khac( mixed $a, mixed $b, string $duong = '' ): array {
	if ( is_array( $a ) && is_array( $b ) ) {
		$ra = array();
		foreach ( array_unique( array_merge( array_keys( $a ), array_keys( $b ) ) ) as $k ) {
			$ra = array_merge( $ra, cd_khac( $a[ $k ] ?? '(không có)', $b[ $k ] ?? '(không có)', $duong . '.' . $k ) );
			if ( count( $ra ) >= 12 ) {
				break;
			}
		}
		return array_slice( $ra, 0, 12 );
	}
	return $a === $b ? array() : array( ltrim( $duong, '.' ) . ': ' . wp_json_encode( $a, JSON_UNESCAPED_UNICODE ) . ' ≠ ' . wp_json_encode( $b, JSON_UNESCAPED_UNICODE ) );
}
/** Mọi giá trị hồ sơ nguồn (ID) đổi thành khoá hồ sơ: hồ sơ tạo lại sau hoàn tác có ID mới. */
function cd_khoa_ho_so( mixed $v ): mixed {
	if ( ! is_array( $v ) ) {
		return $v;
	}
	foreach ( $v as $k => $x ) {
		$v[ $k ] = 'ho_so' === $k && is_int( $x ) ? (string) get_post_field( 'post_name', $x ) : cd_khoa_ho_so( $x );
	}
	return $v;
}
// Phía MVP: những gì bước nhập đổi tại chỗ phải trở về đúng như cũ sau hoàn tác
$mvp_anh = static function () use ( $wpdb, $id ): array {
	$ra = array();
	foreach ( $id as $k => $pid ) {
		clean_post_cache( $pid );
		$p        = get_post( $pid );
		$ra[ $k ] = array( $p->post_type, $p->post_status, $p->post_name, md5( $p->post_content ), $p->post_excerpt, $p->post_date_gmt, $p->post_modified_gmt, DuLieuCu::the( $pid, 'the_loai' ), DuLieuCu::the( $pid, 'hinh_thuc' ), get_post_meta( $pid, '_wp_old_slug' ), count( preg_grep( '/^_phongdao_/', array_keys( get_post_meta( $pid ) ) ) ) );
	}
	$ra['kho']   = $wpdb->get_results( 'SELECT id, trang_thai, post_id, gop_vao FROM ' . Kho::bang() . ' ORDER BY id', ARRAY_A );
	$ra['viec']  = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Viec::bang() );
	$ra['ho_so'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'ho_so_nguon'" );
	$ra['tt']    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'tuyen_tap'" );
	$ra['the']   = cd_the();
	return $ra;
};
/** Phân loại và term meta V2 của các term mẫu (ID hồ sơ đổi thành khoá). */
function cd_the(): array {
	global $wpdb, $mvp;
	$ra = array();
	foreach ( (array) ( $mvp['the'] ?? array() ) as $k => $t ) {
		clean_term_cache( (int) $t );
		$meta     = array_filter( (array) get_term_meta( (int) $t ), static fn( $x, $kk ) => str_starts_with( (string) $kk, '_phongdao_' ), ARRAY_FILTER_USE_BOTH );
		$ra[ $k ] = array( $wpdb->get_var( $wpdb->prepare( "SELECT taxonomy FROM {$wpdb->term_taxonomy} WHERE term_id = %d", (int) $t ) ), cd_khoa_ho_so( array_map( static fn( $v ) => maybe_unserialize( $v[0] ), $meta ) ) );
	}
	return $ra;
}
// Phía V2: kết quả nhập (phần, phân loại, việc, nhật ký) — so giữa hai lần nhập
$v2_anh = static function () use ( $wpdb, $id ): array {
	$ra = array();
	foreach ( $id as $k => $pid ) {
		clean_post_cache( $pid );
		$p        = get_post( $pid );
		$ra[ $k ] = array( $p->post_type, $p->post_status, $p->post_name, DuLieuCu::the( $pid, 'the_loai' ), DuLieuCu::the( $pid, 'hinh_thuc' ), cd_khoa_ho_so( PhanBai::tat_ca( $pid, 'tac_pham' ) ) );
	}
	// Tuyển tập tạo lại sau hoàn tác có ID mới: so theo tên đường dẫn
	$tt         = static fn( $id ) => 'tuyen_tap' === get_post_type( (int) $id ) ? 'tt:' . get_post_field( 'post_name', (int) $id ) : $id;
	$ra['viec'] = array_map(
		static fn( $r ) => cd_khoa_ho_so( array( $r['loai'], $r['trang_thai'], $r['doi_tuong_loai'], $tt( $r['doi_tuong_id'] ), $r['phan'], $r['nguoi_tao'], $r['quyet'], json_decode( (string) $r['noi_dung'], true ), json_decode( (string) $r['truoc'], true ), json_decode( (string) $r['sau'], true ) ) ),
		$wpdb->get_results( 'SELECT * FROM ' . Viec::bang() . ' ORDER BY doi_tuong_id, loai, phan, id', ARRAY_A )
	);
	foreach ( get_posts( array( 'post_type' => 'tuyen_tap', 'post_status' => 'any', 'numberposts' => -1, 'orderby' => 'name' ) ) as $p ) {
		$ra[ 'tt:' . $p->post_name ] = array( $p->post_status, $p->post_title, PhanBai::tat_ca( $p->ID, 'tuyen_tap' ) );
	}
	$ra['the'] = cd_the();
	return $ra;
};
$s0  = $mvp_anh();
$kq  = NhapLam::chay();
kt( 'nhập thật: 13 bài nhập, 1 chờ duyệt máy tạo không mang, 3 bài có thẻ Cần xem, không lỗi, không còn bài', 13 === $kq['nhap'] && 1 === $kq['khong_mang'] && 3 === $kq['can_xem'] && array() === $kq['loi'] && 0 === $kq['con'], $kq );
kt( 'bài giữ ID, thành tác phẩm, giữ trạng thái; bài chờ duyệt máy tạo không mang', 'tac_pham' === get_post_type( $id['a'] ) && 'publish' === get_post_status( $id['a'] ) && 'cdtn' === get_post_type( $id['e'] ) );
$lech = array();
foreach ( $id as $k => $pid ) {
	if ( 'e' === $k ) {
		continue;
	}
	$mong = array_map( static fn( $p ) => cd_khoa_ho_so( $p ), $bc['bai'][ $pid ]['phan'] );
	$co   = array_filter( array_map( static fn( $p ) => null === $p ? null : cd_khoa_ho_so( KiemPhan::noi_dung( $p ) ), array_diff_key( PhanBai::tat_ca( $pid, 'tac_pham' ), array( 'ban_co_biet' => 1 ) ) ) ); // Bạn có biết: phần nhập sau các bài
	array_walk_recursive( $mong, static function ( &$x, $kk ) { $x = 'ho_so' === $kk ? '·' : $x; } );
	array_walk_recursive( $co, static function ( &$x, $kk ) { $x = 'ho_so' === $kk ? '·' : $x; } );
	if ( $mong != $co ) { // phpcs:ignore Universal.Operators.StrictComparisons -- thứ tự khoá không quan trọng
		$lech[ $k ] = array( $mong, $co );
	}
}
kt( 'các phần đã lưu đúng kế hoạch của bước nhập thử', array() === $lech, $lech );
$so_phan = array_sum( array_map( static fn( $k ) => 'e' === $k ? 0 : count( $bc['bai'][ $id[ $k ] ]['phan'] ), array_keys( $id ) ) );
kt( 'mỗi phần một dòng nhật ký, người tạo là bước nhập (+1 Bạn có biết, +3 phần tuyển tập)', $so_phan + 4 === (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . Viec::bang() . " WHERE loai = 'sua' AND nguoi_tao = %s", NhapLam::NGUOI_TAO ) ) );
kt( 'ý nghĩa có dấu AI giữ đúng người đã duyệt trên MVP', 'ai_da_duyet' === ( PhanBai::lay( $id['d'], 'y_nghia' )['nguoi_lam'] ?? '' ) && ( PhanBai::lay( $id['d'], 'y_nghia' )['duyet_boi'] ?? 0 ) > 0 );
kt( 'nháp ý nghĩa thành việc chờ; bài có khối ảnh có thẻ Cần xem kèm lý do', 1 === count( Viec::mo( 'post', $id['n'], 'nhap_y_nghia' ) ) && in_array( 'có khối «core/image»', Viec::mo( 'post', $id['k'], 'can_xem' )[0]['noi_dung']['ly_do'] ?? array(), true ) );
$m_e = $wpdb->get_row( $wpdb->prepare( 'SELECT trang_thai, post_id FROM ' . Kho::bang() . ' WHERE post_id = 0 AND tieu_de = %s', 'Dòng chờ duyệt mẫu' ) );
kt( 'câu của bài chờ duyệt máy tạo trở lại kho "mới" để thành thẻ Câu mới', $m_e && 'moi' === $m_e->trang_thai );
kt( 'tên đường dẫn đuôi số đổi tên mới, tên cũ vẫn chuyển hướng', 'cau-trung-ten-mau-dong' === get_post_field( 'post_name', $id['i2'] ) && in_array( (string) $mvp['ten_i2'], get_post_meta( $id['i2'], '_wp_old_slug' ), true ) );
kt( 'hồ sơ nguồn người điền và sách đối chiếu đã tạo', null !== HoSo::theo_khoa( NhapThu::khoa_ho_so( 'web', 'Sách mẫu người điền' ) ) && null !== HoSo::theo_khoa( NhapThu::khoa_ho_so( 'sach', 'Tuyển tập mẫu' ) ) );
$h_a = (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( TacPham::hien( (array) DocBai::tac_pham( get_post( $id['a'] ) ) ) ) ); // từ có chú thích, chữ khác của dị bản được bọc thẻ
kt( 'trang tác phẩm hiện lời, dị bản, chú thích đã nhập; đường dẫn /ca-dao/<tên>/ như cũ', str_contains( $h_a, 'Dòng mẫu một của bài' ) && str_contains( $h_a, 'Dòng gộp vào bài một' ) && str_contains( $h_a, 'Lời giải mẫu.' ) && str_contains( (string) get_permalink( $id['a'] ), '/ca-dao/' . get_post_field( 'post_name', $id['a'] ) . '/' ), $h_a );
$h_vp = (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( TacPham::hien( (array) DocBai::tac_pham( get_post( $id['vp'] ) ) ) ) );
kt( 'trang vè nhiều phần: bối cảnh, tiêu đề hai phần, đủ dòng như trang MVP', str_contains( $h_vp, '(Bối cảnh mẫu của vè)' ) && str_contains( $h_vp, 'Vè mẫu (I)' ) && str_contains( $h_vp, 'Vè mẫu (II)' ) && str_contains( $h_vp, 'Vè phần hai dòng hai' ), $h_vp );
kt( 'cảm nghĩ vẫn nối đúng bài (giữ ID)', 1 === (int) get_comments( array( 'post_id' => $id['a'], 'count' => true ) ) );
kt( 'nhập xong: khách không còn thấy trang nâng cấp', 'xong' === get_option( NhapLam::TRANG_THAI ) && ! NhapLam::dang_nang_cap() );
wp_set_current_user( 1 );
$hv = do_blocks( '<!-- wp:cadao/hop-viec /-->' );
kt( 'Hộp việc có thẻ Cần xem với nút "Đã xem, ổn rồi" và đường mở bài', str_contains( $hv, 'data-loai="can_xem"' ) && str_contains( $hv, 'Đã xem, ổn rồi' ) && str_contains( $hv, 'có khối «core/image»' ), $hv );
wp_set_current_user( 0 );

// Sau các bài: mục từ, trò chơi, mô-típ, tình huống, Bạn có biết, Wikidata, chuyển hướng
$the      = $mvp['the'];
$cadao_me = (int) ( HoSo::theo_khoa( 'cadao-me' )?->ID ?? 0 );
clean_term_cache( $the['tu'] );
$mu = get_term( $the['tu'] );
kt( 'mục từ giữ ID, thành phân loại muc_tu, đường dẫn /tu-dien/<tên>/ như cũ, lời giải vẫn là mô tả', $mu instanceof WP_Term && 'muc_tu' === $mu->taxonomy && 'Lời giải mẫu.' === $mu->description && str_contains( (string) get_term_link( $mu ), '/tu-dien/' ), $mu );
$lg = get_term_meta( $the['tu'], '_phongdao_loi_giai', true );
kt( 'lời giải chép từ nguồn → máy chép; nguồn tên "Ca dao Mẹ" dùng hồ sơ Ca dao Mẹ có sẵn (không tạo hồ sơ thứ hai)', 'may_chep' === ( $lg['nguoi_lam'] ?? '' ) && $cadao_me && $cadao_me === ( $lg['nguon']['ho_so'] ?? 0 ), $lg );
kt( 'Wikidata đã xác nhận của mục từ mang sang', 'Q1' === ( get_term_meta( $the['tu'], '_phongdao_wikidata', true )['wd'] ?? '' ) );
kt( 'chú thích của bài nối với mục từ của nó (chạm từ mở đúng mục từ)', $the['tu'] === ( PhanBai::lay( $id['a'], 'chu_thich' )['muc'][0]['muc_tu_id'] ?? 0 ) );
$cc = get_term_meta( $the['tc'], '_phongdao_cach_choi', true );
kt( 'trò chơi: cách chơi chép từ Wikipedia → máy chép, hồ sơ Wikipedia, tên bài, giấy phép, các bước', 'may_chep' === ( $cc['nguoi_lam'] ?? '' ) && array( 'Bước một mẫu', 'Bước hai mẫu' ) === ( $cc['buoc'] ?? null ) && 'Trò chơi mẫu' === ( $cc['nguon']['ghi_chu'] ?? '' ) && 'CC BY-SA 4.0' === ( $cc['giay_phep'] ?? '' ) && 'wiki-wikipedia-tieng-viet' === get_post_field( 'post_name', (int) ( $cc['nguon']['ho_so'] ?? 0 ) ), $cc );
kt( 'mô-típ: dấu nhóm mang sang, bài vẫn gắn mô-típ', 'dau-mau' === get_term_meta( $the['mt'], '_phongdao_mo_tip_dau', true ) && has_term( $the['mt'], 'mo_tip', $id['b'] ) );
$tt = get_page_by_path( 'tinh-huong-mau', OBJECT, 'tuyen_tap' );
$tm = $tt ? PhanBai::lay( $tt->ID, 'muc' ) : null;
kt(
	'tình huống → tuyển tập loại tình huống (đủ 2 bài nên đăng), lời dẫn, mục theo tên bài, ghi chú "Hợp khi…"',
	$tt && 'publish' === $tt->post_status && 'tinh_huong' === ( PhanBai::lay( $tt->ID, 'thong_tin' )['loai'] ?? '' ) && 'Lời dẫn mẫu của tình huống.' === ( PhanBai::lay( $tt->ID, 'loi_dan' )['chu'] ?? '' )
		&& array( $id['c'], $id['a'] ) === array_column( $tm['muc'] ?? array(), 'tac_pham_id' ) && 'Hợp khi mẫu.' === ( $tm['muc'][1]['ghi_chu'] ?? '' ),
	$tm
);
$bcb = PhanBai::lay( $id['a'], 'ban_co_biet' );
kt( '"Bạn có biết" đã duyệt → phần của bài, người viết, không gắn dấu AI; câu đang chờ không mang', 'nguoi_viet' === ( $bcb['nguoi_lam'] ?? '' ) && false === ( $bcb['ai'] ?? null ) && str_contains( (string) ( $bcb['chu'] ?? '' ), 'lời giải mẫu' ) && null === PhanBai::lay( $id['b'], 'ban_co_biet' ), $bcb );
$wdv = $wpdb->get_row( "SELECT * FROM " . Viec::bang() . " WHERE loai = 'wikidata'", ARRAY_A );
kt( 'quyết định Wikidata → việc đã quyết trong bảng việc', $wdv && 'xong' === $wdv['trang_thai'] && 'dung' === $wdv['quyet'] && 'term' === $wdv['doi_tuong_loai'] && $the['tu'] === (int) $wdv['doi_tuong_id'], $wdv );
kt(
	'đường dẫn cũ 301 về chỗ mới: /tinh-huong/…, /thanh-ngu/…, /bai/…, /the-loai/thanh-ngu/; đường dẫn lạ để 404',
	$tt && get_permalink( $tt ) === \Phongdao\HaTang\ChuyenHuong::dich( '/tinh-huong/tinh-huong-mau/' ) && get_permalink( $id['d'] ) === \Phongdao\HaTang\ChuyenHuong::dich( '/thanh-ngu/' . get_post_field( 'post_name', $id['d'] ) . '/' )
		&& get_permalink( $id['a'] ) === \Phongdao\HaTang\ChuyenHuong::dich( '/bai/' . get_post_field( 'post_name', $id['a'] ) ) && get_term_link( 'tuc-ngu', 'the_loai' ) === \Phongdao\HaTang\ChuyenHuong::dich( '/the-loai/thanh-ngu/' ) && null === \Phongdao\HaTang\ChuyenHuong::dich( '/khong-co-gi/' )
);

$s1 = $v2_anh();
$kq = NhapLam::chay();
kt( 'chạy lại khi đã nhập xong: không đổi gì', 0 === $kq['nhap'] && $v2_anh() === $s1 );
$ht = NhapLam::hoan_tac();
kt( 'hoàn tác: 13 bài, 1 câu kho, 3 hồ sơ nguồn (sách, nguồn người điền, Wikipedia)', array( 'bai' => 13, 'kho' => 1, 'ho_so' => 3 ) === $ht, $ht );
$s0b = $mvp_anh();
kt( 'hoàn tác: dữ liệu MVP trở về đúng như trước khi nhập (bài, thể loại, câu kho, không còn phần, việc, hồ sơ mới)', $s0b === $s0, cd_khac( $s0, $s0b ) );
kt( 'hoàn tác xong: khách lại thấy trang nâng cấp', NhapLam::dang_nang_cap() );
NhapLam::chay();
$s1b = $v2_anh();
kt( 'nhập → hoàn tác → nhập cho cùng kết quả', $s1b === $s1, cd_khac( $s1, $s1b ) );

$tong = $GLOBALS['cd_dat'] + $GLOBALS['cd_hong'];
ra( "\nKẾT QUẢ: " . ( $GLOBALS['cd_hong'] ? "HỎNG {$GLOBALS['cd_hong']} / $tong" : "ĐẠT $tong" ) );
