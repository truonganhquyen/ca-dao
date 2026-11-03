<?php
/**
 * Khối cadao/tuyen-tap: tuyển tập đang xem (mẫu single-tuyen_tap).
 */

defined( 'ABSPATH' ) || exit;

$tt = get_queried_object();
if ( $tt instanceof WP_Post && \Phongdao\HaTang\KhaiBao::TUYEN_TAP === $tt->post_type ) {
	echo \Phongdao\GiaoTiep\Khoi\TuyenTap::hien( $tt ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong TuyenTap.
}
