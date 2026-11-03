<?php
/**
 * Khối cadao/them-bai: Thêm bài nhập tay /bien-tap/them/. Chỉ in cho người có quyền biên tập.
 */

defined( 'ABSPATH' ) || exit;

if ( current_user_can( 'cadao_soan' ) ) {
	echo \Phongdao\GiaoTiep\BienTap\ThemBai::hien(); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát bên trong.
}
