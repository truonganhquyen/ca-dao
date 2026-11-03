<?php
/**
 * Phép thử chuyển đổi, bước 2 (docs/29 mục 4): V2 đã thay chỗ MVP trên cùng cơ sở dữ liệu, như ngày chuyển. Kiểm
 * kiểm kê, nhập thử (không ghi gì, đúng cách nhập từng loại bài, "trước = sau") và tệp chạy thử đã chạy ở bước 1
 * khi MVP còn bật. Dữ liệu tổng hợp.
 */

require_once '/wordpress/wp-load.php';

use Phongdao\DichVu\NhapDuLieuCu\KiemKe;
use Phongdao\DichVu\NhapDuLieuCu\NhapThu;
use Phongdao\KhoLuu\HoSo;

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
kt( 'bước 1: tạo dữ liệu mẫu bằng mã MVP không lỗi', array() === ( $mvp['loi'] ?? array( 'không có tệp kết quả bước 1' ) ) && 10 === count( $id ), $mvp['loi'] ?? null );
kt( 'V2 đã thay chỗ MVP: plugin V2 chạy, bài MVP còn nguyên loại bài cũ', class_exists( \Phongdao\HaTang\KhoiDong::class ) && ! function_exists( 'cadao_kho_luu' ) && 'cdtn' === get_post_type( $id['a'] ?? 0 ) );

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
	'đã đăng'                                   => 9,
	'chờ duyệt (máy tạo)'                       => 1,
	'bài có dấu băm, chưa sửa tay'              => 5,
	'bài có dấu băm, đã sửa tay'                => 1,
	'bài không có dấu băm (nhập tay, site cũ)'  => 4,
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
kt( 'cách nhập: 4 từ kho chưa sửa, 1 đã sửa tay, 4 không có câu trong kho, 1 chờ duyệt máy tạo không mang', array( 'kho_chua_sua' => 4, 'sua_tay' => 1, 'khong_kho' => 4, 'khong_mang' => 1 ) === $bc['cach'], $bc['cach'] );
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
kt( 'tên đường dẫn đuôi số → tên mới thêm chữ của dòng sau; bài cùng tên kia giữ tên', str_ends_with( (string) ( $mvp['ten_i2'] ?? '' ), '-2' ) && 'cau-trung-ten-mau-dong' === ( $bc['bai'][ $id['i2'] ]['ten_moi'] ?? null ) && null === ( $bc['bai'][ $id['i1'] ]['ten_moi'] ?? 'x' ), $bc['doi_ten'] );
$in = implode( "\n", NhapThu::in( $bc ) );
kt( 'nhập thử khi MVP còn bật và khi V2 đã thay chỗ cho cùng kết quả', doan( $tn, 'Cách nhập:', '-- Cần xem' ) === doan( $in, 'Cách nhập:', '-- Cần xem' ) && '' !== doan( $in, 'Cách nhập:', '-- Cần xem' ), array( doan( $tn, 'Cách nhập:', '-- Cần xem' ), doan( $in, 'Cách nhập:', '-- Cần xem' ) ) );
ra( "THÔNG TIN [chuyển đổi] báo cáo nhập thử:\n" . $in );

$tong = $GLOBALS['cd_dat'] + $GLOBALS['cd_hong'];
ra( "\nKẾT QUẢ: " . ( $GLOBALS['cd_hong'] ? "HỎNG {$GLOBALS['cd_hong']} / $tong" : "ĐẠT $tong" ) );
