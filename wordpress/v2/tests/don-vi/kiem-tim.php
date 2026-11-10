<?php
/**
 * Lõi tìm (MienDuLieu\ChiMuc): ca kiểm chuyển từ MVP (wordpress/tests/kiem-thu.php, D-067, L-08) + gợi ý gõ sai (L-09).
 * Bộ lọc ứng viên của SQL (mọi chữ có trong phần chính hoặc phụ) được mô phỏng bằng loc().
 */

declare(strict_types=1);

use Phongdao\MienDuLieu\ChiMuc;

/** Một dòng chỉ mục như ChiMucTim::ghi dựng. */
$hang = static function ( int $id, string $chinh, string $phu = '' ): array {
	$c = ChiMuc::chu( $chinh );
	$p = ChiMuc::chu( $phu );
	return array( 'post_id' => $id, 'chinh' => " $c ", 'chinh_kd' => ' ' . ChiMuc::khong_dau( $c ) . ' ', 'phu' => " $p ", 'phu_kd' => ' ' . ChiMuc::khong_dau( $p ) . ' ' );
};
$loc = static function ( array $ds, string $q ): array {
	$mau = ChiMuc::mau( ChiMuc::khong_dau( ChiMuc::chu( $q ) ) );
	return array_values( array_filter( $ds, static fn( array $r ): bool => array_reduce( $mau, static fn( bool $c, string $t ): bool => $c && ( str_contains( $r['chinh_kd'], $t ) || str_contains( $r['phu_kd'], $t ) ), true ) ) );
};
$kho = array(
	$hang( 1, "Tìm thử hoà thuận\nTìm thử hoà thuận chữ9901\nchữ9902 cao chữ9903" ),
	$hang( 2, "Tìm thử thủy chung\nTìm thử thủy chung chữ9904\nchữ9905 chữ9906" ),
	$hang( 3, "Tìm thử chú thích\nchữ9907 chữ9908\nchữ9909", 'chữ9907 Từ khóa riêng zênhthử trong chú thích.' ),
	$hang( 11, "Tìm dấu một\nchữ9911 mà chữ9912" ),
	$hang( 12, "Tìm dấu hai\nchữ9911 ma chữ9912" ),
	$hang( 21, "Tìm gần thử một\nzqa zqb chữ12500\nchữ12501 chữ12502" ),
	$hang( 22, "Tìm gần thử hai\nzqa chữ12510 zqb\nchữ12511 chữ12512" ),
	$hang( 23, "Tìm gần thử ba\nzqa " . implode( ' ', range( 12520, 12549 ) ) . "\n" . implode( ' ', range( 12550, 12559 ) ) . ' zqb' ),
);
$ids = static fn( string $q ): array => array_column( ChiMuc::xep( $loc( $kho, $q ), $q )['ds'], 'id' );

kt( 'chuẩn hoá: thường, bỏ dấu câu, kiểu bỏ dấu cũ → mới', 'hòa thuận thủy' === ChiMuc::chu( '  Hoà, thuận —  THUỶ! ' ) && 'hoa thuan thuy' === ChiMuc::khong_dau( ChiMuc::chu( 'Hoà thuận thuỷ' ) ) );
kt( 'mẫu so khớp: chữ trọn âm tiết, chữ cuối gõ dở', array( ' cong ', ' cha' ) === ChiMuc::mau( 'cong cha' ) );
kt( 'tìm: kiểu bỏ dấu cũ khớp kiểu mới ("hoà thuận"), có dấu mới, không dấu', in_array( 1, $ids( 'hoà thuận' ), true ) && in_array( 1, $ids( 'hòa thuận' ), true ) && in_array( 1, $ids( 'hoa thuan' ), true ) );
kt( 'tìm: gõ không dấu ("thuy chung")', in_array( 2, $ids( 'thuy chung' ), true ) );
kt( 'tìm: đang gõ dở chữ cuối vẫn ra', in_array( 2, $ids( 'tim thu thuy ch' ), true ) );
kt( 'tìm: so trọn âm tiết ("ca" không khớp "cao")', ! in_array( 1, $ids( 'ca chữ9903' ), true ) && in_array( 1, $ids( 'cao chữ9903' ), true ) );
kt( 'tìm: tìm cả trong chú thích, có dấu và không dấu', in_array( 3, $ids( 'zênhthử' ), true ) && in_array( 3, $ids( 'zenhthu' ), true ) );
$thu = $ids( 'chữ9911 mà' );
kt( 'tìm: gõ có dấu thì bài đúng dấu đứng trước', array_search( 11, $thu, true ) < array_search( 12, $thu, true ), $thu );
kt( 'tìm: chữ gần nhau (≤ 3 âm tiết chen giữa) / rời rạc', ChiMuc::gan_nhau( ' zqa x y zqb ', array( 'zqa', 'zqb' ) ) && ! ChiMuc::gan_nhau( ' zqa a b c d e zqb ', array( 'zqa', 'zqb' ) ) && ChiMuc::gan_nhau( ' zqb a zqa ', array( 'zqa', 'zq' ) ) );
$tg  = ChiMuc::xep( $loc( $kho, 'zqa zqb' ), 'zqa zqb' )['ds'];
$vt  = static fn( int $id ) => array_search( $id, array_column( $tg, 'id' ), true );
kt( 'tìm: cụm liền trước, chữ gần sau, bài dài có chữ rời rạc xếp cuối và mang cờ "rời"', false !== $vt( 23 ) && $vt( 21 ) < $vt( 22 ) && $vt( 22 ) < $vt( 23 ) && $tg[ $vt( 23 ) ]['roi'] && ! $tg[ $vt( 22 ) ]['roi'], $tg );
kt( 'tìm: khớp cả cụm ở phần chính là kết quả mạnh (thẻ lớn); chữ gần nhau thì không', $tg[0]['lien'] && 21 === $tg[0]['id'] && ! $tg[ $vt( 22 ) ]['lien'] );
kt( 'tìm: truy vấn trống hoặc chỉ dấu câu → không có gì', array( 'ds' => array(), 'tong' => 0 ) === ChiMuc::xep( $kho, ' ?! ' ) );
$it = ChiMuc::xep( $loc( $kho, 'tim thu' ), 'tim thu', 2 );
kt( 'tìm: giới hạn số kết quả, vẫn đếm đủ tổng', 2 === count( $it['ds'] ) && count( $loc( $kho, 'tim thu' ) ) === $it['tong'] && $it['tong'] > 2, $it['tong'] );

// Gõ sai một chữ cái (L-09: "ba co doi gai" → "ba co doi gao")
$tu_vung = array( 'gao' => 9, 'gai' => 3, 'ga' => 4, 'gay' => 1, 'bao' => 7, 'doi' => 5 );
kt( 'gợi ý gõ sai: âm tiết cách một chữ cái, dùng nhiều trước', array( 'gao', 'ga', 'gai' ) === array_slice( ChiMuc::tu_gan( 'gaj', $tu_vung ), 0, 3 ), ChiMuc::tu_gan( 'gaj', $tu_vung ) );
kt( 'gợi ý gõ sai: không gợi ý chính nó, không gợi ý chữ xa hơn một chữ cái', ! in_array( 'gao', ChiMuc::tu_gan( 'gao', $tu_vung ), true ) && ! in_array( 'bao', ChiMuc::tu_gan( 'gai', $tu_vung ), true ) && array() === ChiMuc::tu_gan( '', $tu_vung ) );

// T-181: xếp từ cờ CSDL (xep_co, cách ChiMucTim::tim dùng) trùng xep() trên MỌI ứng viên của kho thử 1.500 bài
// (tổng hợp, tests/do-tim/kho-thu.php), chỉ đọc chữ của một phần ứng viên
require_once V2_GOC . '/tests/do-tim/kho-thu.php';
$kho_lon = do_tim_kho( 1500 );
$lon     = array();
foreach ( $kho_lon as [ $id, $c, $p ] ) {
	$h = ChiMuc::dong_chi_muc( $c, $p );
	if ( $h ) {
		$lon[ $id ] = array( 'post_id' => $id ) + $h;
	}
}
$tv_lon  = do_tim_truy_van( $kho_lon );
$q_lon   = array_values( $tv_lon['ds'] );
$q_lon   = array_merge( $q_lon, array( 'anh em người', 'người con', 'mà', 'ma', 'Cà', 'nhớ thương', 'em ơi', 'ăn cá', '?!' ) );
$sai_lon = array();
$doc_it  = false;
foreach ( $q_lon as $q ) {
	$phep = ChiMuc::phep_so( $q );
	$ung  = $loc( array_values( $lon ), $q );
	$co   = array_map( static fn( array $r ): array => array( 'post_id' => (string) $r['post_id'], 'dai' => (string) strlen( $r['chinh_kd'] ) ) + array_map( 'intval', ChiMuc::co( $r, $phep ) ), $phep ? $ung : array() );
	$doc  = static fn( array $ids ): array => array_intersect_key( $lon, array_flip( $ids ) );
	$a    = ChiMuc::xep( $ung, $q, 20 );
	foreach ( array( array( 20, 100 ), array( 20, 1 ), array( 1, 100 ), array( 0, 100 ) ) as [ $so, $lo ] ) {
		$b = ChiMuc::xep_co( $co, $q, $so, $doc, $lo );
		if ( array_slice( $a['ds'], 0, $so ) !== $b['ds'] || $a['tong'] !== $b['tong'] ) {
			$sai_lon[] = array( $q, $so, $lo );
		}
		if ( 20 === $so && 100 === $lo && $b['doc'] > 0 && $b['doc'] < count( $ung ) / 2 && count( $ung ) > 500 ) {
			$doc_it = true;
		}
	}
}
kt( 'tìm kho lớn: xếp từ cờ CSDL ra đúng tổng và đúng thứ tự như xếp mọi ứng viên (mọi truy vấn đại diện, mọi cỡ lô)', array() === $sai_lon && count( $q_lon ) >= 20, $sai_lon );
kt( 'tìm kho lớn: có truy vấn hơn 500 ứng viên mà chỉ phải đọc chữ dưới một nửa', $doc_it );
kt( 'tìm kho lớn: kho thử có truy vấn hơn 500 ứng viên và bài mới nhất có cụm lấy làm mẫu', count( $loc( array_values( $lon ), $tv_lon['ds']['Hai chữ phổ biến'] ) ) > 500 && in_array( $tv_lon['moi'], array_column( ChiMuc::xep( $loc( array_values( $lon ), $tv_lon['ds']['Cả dòng của bài mới'] ), $tv_lon['ds']['Cả dòng của bài mới'] )['ds'], 'id' ), true ) );
kt( 'dòng chỉ mục: phần chính không có chữ thì không đánh chỉ mục; có thì đệm khoảng trắng hai đầu', null === ChiMuc::dong_chi_muc( ' ?! ', 'có giải thích' ) && ' hòa thuận ' === ChiMuc::dong_chi_muc( 'Hoà, thuận', '' )['chinh'] && ' hoa thuan ' === ChiMuc::dong_chi_muc( 'Hoà, thuận', '' )['chinh_kd'] && '  ' === ChiMuc::dong_chi_muc( 'a', '' )['phu'] );
