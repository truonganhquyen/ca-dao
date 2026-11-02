<?php

namespace Phongdao\GiaoTiep\QuanTri;

defined( 'ABSPATH' ) || exit;

/**
 * Mục menu "Phongdao" trong wp-admin: trang Nguồn & cài đặt và đường tắt tới Hộp việc (D-094; docs/28 mục 4.7, T-155).
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
		echo '<div class="wrap"><h1>' . esc_html__( 'Nguồn & cài đặt', 'ca-dao' ) . '</h1>';
		echo '<p>' . esc_html__( 'Trang này và Hộp việc đang được xây (đợt 3).', 'ca-dao' ) . '</p></div>';
	}
}
