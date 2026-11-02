<?php
/**
 * Khối cadao/danh-sach: danh sách tác phẩm của truy vấn chính (mẫu archive-tac_pham, taxonomy).
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;
if ( $wp_query instanceof WP_Query ) {
	echo \Phongdao\GiaoTiep\Khoi\DanhSach::hien( $wp_query ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong DanhSach.
}
