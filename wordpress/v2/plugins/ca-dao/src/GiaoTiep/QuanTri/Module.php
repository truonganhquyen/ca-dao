<?php

namespace Phongdao\GiaoTiep\QuanTri;

defined( 'ABSPATH' ) || exit;

/**
 * Mục menu "Phongdao" mở Bàn biên tập (ứng dụng React ở quan-tri/, T-152; docs/28 mục 4).
 */
final class Module implements \Phongdao\HaTang\Module {

	public const TRANG = 'phongdao';

	public function dang_ky(): void {
		add_action( 'admin_menu', array( $this, 'them_menu' ) );
	}

	public function them_menu(): void {
		add_menu_page( 'Phongdao', 'Phongdao', 'edit_posts', self::TRANG, array( $this, 'hien' ), 'dashicons-book-alt', 3 );
	}

	public function hien(): void {
		echo '<div class="wrap"><h1>' . esc_html__( 'Bàn biên tập Phongdao', 'ca-dao' ) . '</h1>';
		echo '<div id="phongdao-ung-dung"><p>' . esc_html__( 'Bàn biên tập đang được xây (đợt 3).', 'ca-dao' ) . '</p></div></div>';
	}
}
