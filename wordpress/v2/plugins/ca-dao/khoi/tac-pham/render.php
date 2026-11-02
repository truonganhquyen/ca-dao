<?php
/**
 * Khối cadao/tac-pham: trang tác phẩm đủ phần. Tạm đọc dữ liệu mẫu theo thuộc tính "mau".
 *
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$tp = \Phongdao\DichVu\DungTrang\DuLieuMau::lay( (string) ( $attributes['mau'] ?? '' ) );
if ( $tp ) {
	echo \Phongdao\GiaoTiep\Khoi\TacPham::hien( $tp ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong TacPham.
}
