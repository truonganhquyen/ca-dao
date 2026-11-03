<?php
/**
 * Khối cadao/tac-pham: trang tác phẩm đủ phần (docs/27 mục 6). Đọc tác phẩm đang xem (mẫu single-tac_pham);
 * thuộc tính "mau" vẽ dữ liệu mẫu tổng hợp (so với bộ thiết kế, kiểm thử). Người có quyền biên tập thấy chế độ sửa
 * (D-094); khách không bao giờ nhận phần sửa trong HTML.
 *
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$ma  = (string) ( $attributes['mau'] ?? '' );
$sua = null;
if ( '' !== $ma ) {
	$tp = \Phongdao\DichVu\DungTrang\DuLieuMau::lay( $ma );
} else {
	$bai = get_queried_object();
	$bai = $bai instanceof WP_Post ? $bai : get_post();
	$tp  = $bai instanceof WP_Post ? \Phongdao\DichVu\DungTrang\DocBai::tac_pham( $bai ) : null;
	if ( $tp ) {
		$tp['doc_tiep'] = \Phongdao\DichVu\DungTrang\DocTiep::cho( $bai ); // chỉ trang bài (không tính cho từng câu trong tuyển tập)
	}
	$sua = $tp ? \Phongdao\GiaoTiep\BienTap\CheDoSua::ngu_canh( $bai ) : null;
}
if ( $tp ) {
	echo \Phongdao\GiaoTiep\Khoi\TacPham::hien( $tp, $sua ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong TacPham.
}
