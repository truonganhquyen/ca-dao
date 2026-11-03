<?php
/**
 * Khối cadao/so-lieu-tuan: Số liệu tuần /bien-tap/so-lieu/. Chỉ in cho người có quyền biên tập.
 */

defined( 'ABSPATH' ) || exit;

if ( current_user_can( 'cadao_soan' ) ) {
	echo \Phongdao\GiaoTiep\BienTap\SoLieuTuan::hien(); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát bên trong.
}
