<?php
/**
 * Kiểm thử vẽ ảnh chia sẻ (T-050) bằng PHP thật có GD + FreeType: `npm run test:anh`.
 * WordPress Playground không vẽ được chữ bằng GD, nên phần vẽ kiểm ở đây; phần chọn nội dung, thẻ og:*, ảnh cũ / mới
 * kiểm trong kiem-thu.php. Lời trong tệp này là chữ tổng hợp để thử dấu, không phải lời thật (repo công khai).
 */

define( 'ABSPATH', __DIR__ );
define( 'CADAO_THU_MUC', dirname( __DIR__ ) . '/plugins/ca-dao' );
function add_action() {}
function wp_mkdir_p( $d ) {
	return is_dir( $d ) || mkdir( $d, 0777, true );
}
require CADAO_THU_MUC . '/inc/anh-chia-se.php';

$dat  = 0;
$hong = 0;
function kt( string $ten, bool $dk ): void {
	global $dat, $hong;
	$dk ? ++$dat : ++$hong;
	echo ( $dk ? 'ĐẠT   ' : 'HỎNG  ' ) . $ten . "\n";
}

kt( 'GD có FreeType, vẽ được chữ với phông của dự án', cadao_anh_ve_duoc() );
$phong = cadao_anh_phong( 'literata-400' );
foreach ( array( 'literata-400', 'bvp-400', 'bvp-600' ) as $p ) {
	kt( "phông $p: có đủ chữ có dấu (khác bề rộng ô trống)", cadao_anh_rong( 'Ngưỡng Thuở Quyện Đ', cadao_anh_phong( $p ), 30 ) > 0 && cadao_anh_rong( 'ưỡởệĐ', cadao_anh_phong( $p ), 30 ) !== cadao_anh_rong( "\u{FFFF}\u{FFFF}\u{FFFF}\u{FFFF}\u{FFFF}", cadao_anh_phong( $p ), 30 ) );
}
$cat = cadao_anh_cat( str_repeat( 'chữ dài ', 80 ), cadao_anh_phong( 'bvp-400' ), 17, 400 );
kt( 'cắt chữ cho vừa bề rộng, thêm "…"', '…' === mb_substr( $cat, -1 ) && cadao_anh_rong( $cat, cadao_anh_phong( 'bvp-400' ), 17 ) <= 400 );
kt( 'chữ ngắn giữ nguyên', 'Ngưỡng' === cadao_anh_cat( 'Ngưỡng', $phong, 30, 1000 ) );

$tam = sys_get_temp_dir() . '/cadao-anh-thu-' . getmypid();
$nd  = array(
	'loai'     => 'Ca dao',
	'dong'     => array( 'Ngưỡng thuở quyện thử dòng sáu', 'Ngưỡng thuở quyện thử dòng tám chữ đây', 'Dòng ba thử', 'Dòng bốn thử' ),
	'con'      => true,
	'luc_bat'  => true,
	'muc_kiem' => 'Lời theo bản lưu truyền phổ biến · chưa đối chiếu sách',
	'ten_mien' => 'phongdao.com',
);
$tep = $tam . '/thu.png';
kt( 'vẽ ảnh và lưu tệp (tự tạo thư mục)', cadao_anh_ve( $nd, $tep ) && file_exists( $tep ) );
$co = getimagesize( $tep );
kt( 'ảnh PNG 1200×630', $co && 1200 === $co[0] && 630 === $co[1] && 'image/png' === $co['mime'] );
$anh  = imagecreatefrompng( $tep );
$giay = imagecolorat( $anh, 600, 20 );
$dem  = static function ( int $x0, int $y0, int $x1, int $y1 ) use ( $anh, $giay ): int {
	$n = 0;
	for ( $y = $y0; $y < $y1; $y += 2 ) {
		for ( $x = $x0; $x < $x1; $x += 2 ) {
			$n += imagecolorat( $anh, $x, $y ) !== $giay ? 1 : 0;
		}
	}
	return $n;
};
kt( 'có chữ ở vùng lời, vùng thể loại, vùng chân', $dem( 88, 150, 1112, 480 ) > 2000 && $dem( 88, 70, 600, 115 ) > 100 && $dem( 88, 520, 1112, 580 ) > 300 );
kt( 'lề phải để trống (chữ không tràn ra mép)', 0 === $dem( 1130, 140, 1200, 480 ) );
kt( 'vạch màu sơn ở mép trái', imagecolorat( $anh, 5, 300 ) === imagecolorallocate( $anh, 0x9e, 0x2a, 0x22 ) );
$nd['dong'] = array( str_repeat( 'Dòng rất dài để thử co chữ ', 6 ) );
kt( 'một dòng rất dài: vẫn vẽ được, chữ không tràn ra mép', cadao_anh_ve( $nd, $tam . '/dai.png' ) && 0 === ( static function () use ( $tam ) {
	$a = imagecreatefrompng( $tam . '/dai.png' );
	$g = imagecolorat( $a, 600, 20 );
	$n = 0;
	for ( $y = 140; $y < 480; $y += 2 ) {
		for ( $x = 1140; $x < 1200; $x += 2 ) {
			$n += imagecolorat( $a, $x, $y ) !== $g ? 1 : 0;
		}
	}
	return $n;
} )() );
if ( getenv( 'CADAO_GIU_ANH' ) ) {
	copy( $tep, getenv( 'CADAO_GIU_ANH' ) ); // xem bằng mắt
}
array_map( 'unlink', glob( $tam . '/*' ) );
rmdir( $tam );

echo "Kiểm thử ảnh chia sẻ: $dat đạt, $hong hỏng.\n";
echo $hong ? "KẾT QUẢ: HỎNG\n" : "KẾT QUẢ: ĐẠT\n";
exit( $hong ? 1 : 0 );
