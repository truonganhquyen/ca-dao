<?php
/**
 * Ảnh chia sẻ PNG (DichVu\AnhChiaSe\VeAnh): nội dung ảnh (câu đố không lộ đáp án, bài dài chỉ 4 dòng đầu), khổ, nền,
 * chữ vừa khung. Dữ liệu tổng hợp. Máy không có GD + FreeType thì bỏ qua phần vẽ (CI bắt buộc có).
 */

declare(strict_types=1);

use Phongdao\DichVu\AnhChiaSe\VeAnh;

$tp = static fn( array $them ): array => $them + array( 'the_loai' => 'ca-dao', 'ten_the_loai' => 'Ca dao', 'kieu' => 'lucbat', 'loi' => array( 'Dòng mẫu một của bài này', 'Dòng mẫu hai dài hơn chút cho vừa' ), 'url' => 'https://phongdao.test/ca-dao/bai-mau/' );

$nd = VeAnh::noi_dung( $tp( array( 'loi' => array_merge( array( 'Dòng 1', '' ), array_map( static fn( $i ) => "Dòng $i", range( 2, 12 ) ) ), 'y_nghia' => array( 'chu' => 'Ý nghĩa mẫu.' ) ) ) );
kt( 'ảnh: bài trên 8 dòng chỉ lấy 4 dòng đầu + "…", bỏ dòng trống; đường dẫn ngắn không có dấu / cuối', array( 'Dòng 1', 'Dòng 2', 'Dòng 3', 'Dòng 4', '…' ) === $nd['dong'] && 'phongdao.test/ca-dao/bai-mau' === $nd['duong'] && 'phongdao.test' === $nd['ten_mien'] && 'Ý nghĩa mẫu.' === $nd['y_nghia'] && $nd['luc_bat'], $nd );
$nd_do = VeAnh::noi_dung( $tp( array( 'the_loai' => 'cau-do', 'ten_the_loai' => 'Câu đố', 'kieu' => '', 'y_nghia' => array( 'chu' => 'Lộ đáp án' ), 'dap_an' => 'Đáp án mẫu' ) ) );
kt( 'ảnh câu đố: không có ý nghĩa, không có đáp án (không lộ lời giải)', $nd_do['cau_do'] && '' === $nd_do['y_nghia'] && ! str_contains( (string) json_encode( $nd_do, JSON_UNESCAPED_UNICODE ), 'Đáp án mẫu' ) && ! str_contains( (string) json_encode( $nd_do, JSON_UNESCAPED_UNICODE ), 'Lộ đáp án' ) );
kt( 'bài có cờ "Nghi không phải dân gian": ảnh ghi thêm cạnh thể loại', 'Ca dao · Nghi không phải dân gian' === VeAnh::noi_dung( $tp( array( 'co_nghi' => 'Lý do mẫu' ) ) )['the_loai'] && 'Ca dao' === VeAnh::noi_dung( $tp( array() ) )['the_loai'] );
kt( 'bấm Tải ảnh tính vào sự kiện chia_se sẵn có (kiểu tai_anh)', '7:tai_anh' === \Phongdao\MienDuLieu\DoLuong::khoa( 'chia_se', array( 'bai' => '7', 'kieu' => 'tai_anh' ) ) );
kt( 'nền theo thể loại: nền đầu là nền của thẻ og:image; thể loại lạ dùng nền ca dao', 'xanh' === VeAnh::nen_cua( 've' )[0] && VeAnh::nen_cua( 'la' ) === VeAnh::nen_cua( 'ca-dao' ) && array() === array_diff( array_merge( ...array_values( VeAnh::NEN_THEO_LOAI ) ), array_keys( VeAnh::NEN ) ) );

kt( 'máy kiểm vẽ được chữ (GD + FreeType) — bắt buộc trên CI', VeAnh::duoc() || false === getenv( 'CI' ) );
if ( VeAnh::duoc() ) {
	$co = static function ( string $png ): ?array {
		$s = getimagesizefromstring( $png );
		return $s ? array( $s[0], $s[1], $s['mime'] ) : null;
	};
	$nd   = VeAnh::noi_dung( $tp( array( 'y_nghia' => array( 'chu' => 'Một câu giải nghĩa tổng hợp đủ dài để xuống dòng trên ảnh vuông.' ) ) ) );
	$ngang = (string) VeAnh::ve( $nd, 'ngang', 'tim' );
	kt( 'ảnh ngang 1200 × 630 PNG (thẻ og:image)', array( 1200, 630, 'image/png' ) === $co( $ngang ) );
	kt( 'ảnh vuông 1080 × 1080 kèm ý nghĩa; dọc 1080 × 1350', array( 1080, 1080, 'image/png' ) === $co( (string) VeAnh::ve( $nd, 'vuong', 'giay', true ) ) && array( 1080, 1350, 'image/png' ) === $co( (string) VeAnh::ve( $nd_do, 'doc', 'dem' ) ) );
	$anh = imagecreatefromstring( $ngang );
	$rgb = imagecolorat( $anh, 2, 2 );
	kt( 'nền đúng màu của bộ thiết kế (góc trên trái = màu đầu của dải màu)', abs( ( $rgb >> 16 & 255 ) - 0x35 ) <= 3 && abs( ( $rgb >> 8 & 255 ) - 0x20 ) <= 3 && abs( ( $rgb & 255 ) - 0x5F ) <= 3, dechex( $rgb ) );
	$sang = 0;
	for ( $x = 72; $x < 900; $x += 3 ) { // chữ lời (trắng) nằm ở giữa ảnh
		for ( $y = 150; $y < 450; $y += 3 ) {
			$sang += ( imagecolorat( $anh, $x, $y ) & 255 ) > 200 ? 1 : 0;
		}
	}
	kt( 'có vẽ lời (nhiều điểm sáng ở vùng giữa)', $sang > 300, $sang );
	kt( 'khổ, nền lạ hoặc bài không có lời: không vẽ', null === VeAnh::ve( $nd, 'tron', 'tim' ) && null === VeAnh::ve( $nd, 'ngang', 'cam' ) && null === VeAnh::ve( array( 'dong' => array() ) + $nd, 'ngang', 'tim' ) );
	$dai = str_repeat( 'Dòng rất dài ', 30 );
	$cat = VeAnh::cat( $dai, VeAnh::phong( 'literata-400' ), 40, 1000 );
	kt( 'dòng quá dài bị cắt "…" cho vừa bề rộng', str_ends_with( $cat, '…' ) && VeAnh::rong( $cat, VeAnh::phong( 'literata-400' ), 40 ) <= 1000 && null !== VeAnh::ve( VeAnh::noi_dung( $tp( array( 'loi' => array( $dai ) ) ) ), 'ngang', 'tim' ) );
}
