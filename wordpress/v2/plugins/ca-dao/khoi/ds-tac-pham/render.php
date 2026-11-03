<?php
/**
 * Khối cadao/ds-tac-pham: danh sách tác phẩm cho người biên soạn /bien-tap/tac-pham/. Chỉ in cho người có quyền biên tập.
 */

defined( 'ABSPATH' ) || exit;

if ( current_user_can( 'cadao_soan' ) ) {
	echo \Phongdao\GiaoTiep\BienTap\DsTacPham::hien(); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát bên trong.
}
