<?php
/**
 * Dữ liệu mẫu của trang người đọc (DichVu\DungTrang\DuLieuMau): đủ phần, chữ thuần (không thẻ HTML), không gọi WordPress.
 */

declare(strict_types=1);

use Phongdao\DichVu\DungTrang\DuLieuMau;

kt( 'tự nạp DichVu\DungTrang\DuLieuMau (không cần WordPress)', class_exists( DuLieuMau::class ) );
kt( 'có bài mẫu ca dao và tục ngữ', array( 'ca-dao', 'tuc-ngu' ) === DuLieuMau::ds() );
kt( 'mã lạ trả null', null === DuLieuMau::lay( 'khong-co' ) );

$can   = array( 'ma', 'the_loai', 'ten_the_loai', 'hinh_thuc', 'kieu', 'tieu_de', 'loi', 'doi_chieu', 'y_nghia', 'chu_thich', 'di_ban', 'chu_de', 'vung', 'tuyen_tap', 'so_lieu' );
$thieu = array();
$the   = array();
foreach ( DuLieuMau::ds() as $ma ) {
	$tp = DuLieuMau::lay( $ma );
	foreach ( $can as $k ) {
		if ( ! array_key_exists( $k, $tp ) ) {
			$thieu[] = "$ma.$k";
		}
	}
	array_walk_recursive(
		$tp,
		static function ( $v ) use ( &$the, $ma ): void {
			if ( is_string( $v ) && $v !== strip_tags( $v ) ) {
				$the[] = "$ma: $v";
			}
		}
	);
}
kt( 'mỗi bài mẫu đủ phần cho trang tác phẩm', array() === $thieu, $thieu );
kt( 'dữ liệu mẫu là chữ thuần, không thẻ HTML (HTML do lớp vẽ sinh và thoát)', array() === $the, $the );
$ca_dao = DuLieuMau::lay( 'ca-dao' );
kt( 'ca dao mẫu: lục bát 4 dòng, có chú thích “chút”, 1 dị bản', 'lucbat' === $ca_dao['kieu'] && 4 === count( $ca_dao['loi'] ) && 'chút' === $ca_dao['chu_thich'][0]['tu'] && 1 === count( $ca_dao['di_ban'] ) );
kt( 'dữ liệu mẫu không tự đặt “đã đối chiếu” (doi_chieu = null)', null === $ca_dao['doi_chieu'] && null === DuLieuMau::lay( 'tuc-ngu' )['doi_chieu'] );
