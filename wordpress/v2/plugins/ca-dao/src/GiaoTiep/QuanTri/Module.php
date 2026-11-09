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
		add_action( 'load-toplevel_page_' . self::TRANG, array( $this, 'trang_dau' ) );
		$ds = add_submenu_page( self::TRANG, 'Tác phẩm', 'Tác phẩm', 'cadao_soan', DanhSachTacPham::TRANG, static function (): void {
			echo DanhSachTacPham::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- mọi giá trị động được thoát trong lớp hiển thị
		} );
		if ( $ds ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $ds ): void {
				if ( $trang === $ds ) {
					wp_enqueue_style( 'phongdao-admin-tac-pham', plugins_url( 'assets/admin-tac-pham.css', dirname( __DIR__, 3 ) . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN );
				}
			} );
		}
		$moc = add_submenu_page( self::TRANG, 'Hộp việc', 'Hộp việc', 'cadao_soan', DanhSachViec::TRANG, static function (): void {
			echo DanhSachViec::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $moc ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $moc ): void {
				if ( $trang === $moc ) {
					wp_enqueue_style( 'phongdao-admin-viec', plugins_url( 'assets/admin-viec.css', dirname( __DIR__, 3 ) . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN );
				}
			} );
		}
	}

	/** Biên tập mở menu Phongdao tới danh sách chính; Quản trị giữ trang Nguồn & cài đặt hiện có. */
	public function trang_dau(): void {
		if ( ! current_user_can( NguonCaiDat::QUYEN ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . DanhSachTacPham::TRANG ) );
			exit;
		}
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
