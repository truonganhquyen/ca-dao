<?php
/**
 * Khối cadao/hop-viec: Hộp việc /bien-tap/ (D-094). Chỉ in cho người có quyền biên tập (trang cũng chặn ở TrangCoDinh).
 */

defined( 'ABSPATH' ) || exit;

if ( current_user_can( 'cadao_soan' ) ) {
	echo \Phongdao\GiaoTiep\BienTap\HopViec::hien(); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát bên trong.
}
