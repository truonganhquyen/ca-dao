<?php
// Chỉ stdin/stdout trong thư mục thử riêng; không ghi lời hoặc truy vấn vào repo/log.
require dirname( __DIR__, 2 ) . '/wordpress/v2/plugins/ca-dao/src/tu-nap.php';
use Phongdao\MienDuLieu\SoKhopLoi;
$d = json_decode( stream_get_contents( STDIN ), true, 512, JSON_THROW_ON_ERROR );
$ra = array();
foreach ( $d['rows'] as $r ) {
	$ra[] = 'prepare' === $d['mode']
		? array( 'loi' => SoKhopLoi::chu( $r ), 'loi_kd' => SoKhopLoi::chu( $r, true ), 'plan' => SoKhopLoi::truy_van( $r ) )
		: SoKhopLoi::so( $r[0], $r[1] );
}
echo json_encode( $ra, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR );
