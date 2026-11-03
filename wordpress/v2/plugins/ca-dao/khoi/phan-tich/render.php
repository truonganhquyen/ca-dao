<?php
/**
 * Khối cadao/phan-tich: bài cảm nhận đang xem (mẫu single-phan_tich).
 */

defined( 'ABSPATH' ) || exit;

$p = get_queried_object();
if ( $p instanceof WP_Post && \Phongdao\HaTang\KhaiBao::PHAN_TICH === $p->post_type ) {
	echo \Phongdao\GiaoTiep\Khoi\PhanTich::hien( $p ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong PhanTich.
}
