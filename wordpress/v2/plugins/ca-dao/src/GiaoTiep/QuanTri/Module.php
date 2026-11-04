<?php

namespace Phongdao\GiaoTiep\QuanTri;

defined( 'ABSPATH' ) || exit;

/**
 * Mục menu "Phongdao" trong wp-admin (D-094; docs/28 mục 4.7): trang Nguồn & cài đặt (chỉ Quản trị) và đường tắt tới
 * Hộp việc cho mọi người biên soạn.
 */
final class Module implements \Phongdao\HaTang\Module {

	public const TRANG = 'phongdao';

	public function dang_ky(): void {
		add_action( 'admin_menu', array( $this, 'them_menu' ) );
		NguonCaiDat::dang_ky();
	}

	public function them_menu(): void {
		add_menu_page( 'Phongdao', 'Phongdao', 'cadao_soan', self::TRANG, array( $this, 'hien' ), 'dashicons-book-alt', 3 );
		// Đường tắt tới Hộp việc: chuyển ở bước load-… (trước khi wp-admin in đầu trang) — chuyển trong hàm hiện trang thì
		// đầu trang đã gửi, trình duyệt chỉ thấy cảnh báo "headers already sent". Hàm hiện trang chỉ còn là liên kết dự phòng.
		$moc = add_submenu_page( self::TRANG, 'Hộp việc', 'Hộp việc', 'cadao_soan', 'phongdao-hop-viec', static function (): void {
			echo '<div class="wrap"><h1>Hộp việc</h1><p><a href="' . esc_url( home_url( '/bien-tap/' ) ) . '">' . esc_html__( 'Mở Hộp việc', 'ca-dao' ) . '</a></p></div>';
		} );
		if ( $moc ) {
			add_action( 'load-' . $moc, array( self::class, 'toi_hop_viec' ) );
		}
	}

	public static function toi_hop_viec(): void {
		wp_safe_redirect( home_url( '/bien-tap/' ) );
		exit;
	}

	public function hien(): void {
		if ( ! current_user_can( NguonCaiDat::QUYEN ) ) {
			echo '<div class="wrap"><h1>Phongdao</h1><p>' . esc_html__( 'Việc biên tập làm ngay trên trang đọc và ở Hộp việc.', 'ca-dao' ) . ' <a href="' . esc_url( home_url( '/bien-tap/' ) ) . '">' . esc_html__( 'Mở Hộp việc', 'ca-dao' ) . '</a></p></div>';
			return;
		}
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc để chọn thẻ, hồ sơ
		echo NguonCaiDat::hien( sanitize_key( wp_unslash( $_GET['the'] ?? '' ) ), (int) ( $_GET['ho_so'] ?? 0 ), NguonCaiDat::bao() ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong NguonCaiDat
	}
}
