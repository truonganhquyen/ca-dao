<?php

namespace Phongdao\GiaoTiep\QuanTri;

defined( 'ABSPATH' ) || exit;

/**
 * Mục menu "Phongdao" trong wp-admin (D-094, D-100; docs/28 mục 4.7): xếp theo việc hằng ngày — Bàn làm việc (mở khi
 * bấm "Phongdao" và ngay sau khi đăng nhập), Hộp việc (kèm số việc chờ), Tác phẩm, Kho, Nguồn & cài đặt (chỉ Quản trị),
 * Thao tác hàng loạt. "Sửa tác phẩm" là màn con mở từ danh sách, không nằm trên menu.
 */
final class Module implements \Phongdao\HaTang\Module {

	public const TRANG = 'phongdao';

	public function dang_ky(): void {
		add_action( 'admin_menu', array( $this, 'them_menu' ) );
		add_filter( 'login_redirect', array( self::class, 'sau_dang_nhap' ), 10, 3 );
		add_filter( 'submenu_file', array( self::class, 'muc_dang_mo' ) );
		add_filter( 'parent_file', array( self::class, 'menu_dang_mo' ) );
		add_action( 'admin_bar_menu', array( self::class, 'them_moi' ), 80 );
		NguonCaiDat::dang_ky();
		SuaTacPham::dang_ky();
		DanhSachKho::dang_ky();
		DanhSachTacPham::dang_ky();
		TrangLo::dang_ky();
	}

	public function them_menu(): void {
		$ban = static function (): void {
			echo BanLamViec::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		};
		$cha = BanLamViec::TRANG;
		add_menu_page( 'Phongdao', 'Phongdao', 'cadao_soan', $cha, $ban, 'dashicons-book-alt', 3 );
		add_submenu_page( $cha, 'Bàn làm việc', 'Bàn làm việc', 'cadao_soan', $cha, $ban );
		$cho = \Phongdao\KhoLuu\Viec::dem_cho();
		$moc = add_submenu_page( $cha, 'Hộp việc', 'Hộp việc' . ( $cho ? ' <span class="awaiting-mod count-' . $cho . '"><span class="pending-count">' . number_format_i18n( $cho ) . '</span></span>' : '' ), 'cadao_soan', DanhSachViec::TRANG, static function (): void {
			echo DanhSachViec::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $moc ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $moc ): void {
				if ( $trang === $moc ) {
					$tep = dirname( __DIR__, 3 ) . '/ca-dao.php';
					wp_enqueue_style( 'phongdao-admin-viec', plugins_url( 'assets/admin-viec.css', $tep ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
				}
			} );
		}
		$ds = add_submenu_page( $cha, 'Tác phẩm', 'Tác phẩm', 'cadao_soan', DanhSachTacPham::TRANG, static function (): void {
			echo DanhSachTacPham::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- mọi giá trị động được thoát trong lớp hiển thị
		} );
		if ( $ds ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $ds ): void {
				if ( $trang === $ds ) {
					$tep = dirname( __DIR__, 3 ) . '/ca-dao.php';
					wp_enqueue_style( 'phongdao-admin-tac-pham', plugins_url( 'assets/admin-tac-pham.css', $tep ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
				}
			} );
		}
		// Màn con, không lên menu: mở từ danh sách Tác phẩm, Hộp việc, Bàn làm việc
		$sua = add_submenu_page( '', 'Sửa tác phẩm', 'Sửa tác phẩm', 'cadao_soan', SuaTacPham::TRANG, static function (): void {
			echo SuaTacPham::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $sua ) {
			add_action( 'load-' . $sua, static function (): void {
				global $title;
				$title = 'Sửa tác phẩm'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- màn ẩn khỏi menu không có tiêu đề cho thẻ <title>
			} );
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $sua ): void {
				if ( $trang === $sua ) {
					$tep = dirname( __DIR__, 3 ) . '/ca-dao.php';
					wp_enqueue_style( 'phongdao-admin-sua', plugins_url( 'assets/admin-sua.css', $tep ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-sua', plugins_url( 'assets/admin-sua.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
				}
			} );
		}
		$kho = add_submenu_page( $cha, 'Kho', 'Kho', 'cadao_soan', DanhSachKho::TRANG, static function (): void {
			echo DanhSachKho::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $kho ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $kho ): void {
				if ( $trang === $kho ) {
					$tep = dirname( __DIR__, 3 ) . '/ca-dao.php';
					wp_enqueue_style( 'phongdao-admin-tac-pham', plugins_url( 'assets/admin-tac-pham.css', $tep ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_style( 'phongdao-admin-kho', plugins_url( 'assets/admin-kho.css', $tep ), array( 'phongdao-admin-tac-pham' ), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
				}
			} );
		}
		// Nguồn & cài đặt giữ đường dẫn admin.php?page=phongdao (hồ sơ nguồn, máy đang làm… trỏ tới đây); chỉ Quản trị
		add_submenu_page( $cha, 'Nguồn & cài đặt', 'Nguồn &amp; cài đặt', NguonCaiDat::QUYEN, self::TRANG, array( $this, 'hien' ) );
		$lo = add_submenu_page( $cha, 'Thao tác hàng loạt', 'Thao tác hàng loạt', 'cadao_soan', TrangLo::TRANG, static function (): void {
			echo TrangLo::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $lo ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $lo ): void {
				if ( $trang === $lo ) {
					$tep = dirname( __DIR__, 3 ) . '/ca-dao.php';
					wp_enqueue_style( 'phongdao-admin-tac-pham', plugins_url( 'assets/admin-tac-pham.css', $tep ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_style( 'phongdao-admin-lo', plugins_url( 'assets/admin-lo.css', $tep ), array( 'phongdao-admin-tac-pham' ), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-lo', plugins_url( 'assets/admin-lo.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
				}
			} );
		}
	}

	/** Đăng nhập xong (không có đích riêng) thì người biên soạn vào Bàn làm việc, không vào trang chào mừng WordPress. */
	public static function sau_dang_nhap( string $dich, string $yeu_cau, $nguoi ): string {
		$mac_dinh = '' === $yeu_cau || in_array( untrailingslashit( $yeu_cau ), array( untrailingslashit( admin_url() ), untrailingslashit( admin_url( 'index.php' ) ) ), true );
		if ( $mac_dinh && $nguoi instanceof \WP_User && $nguoi->has_cap( 'cadao_soan' ) ) {
			return admin_url( 'admin.php?page=' . BanLamViec::TRANG );
		}
		return $dich;
	}

	/** "Thêm mới → Tác phẩm" ở thanh trên cùng, mở màn Thêm bài (có kiểm trùng khi dán lời). */
	public static function them_moi( \WP_Admin_Bar $thanh ): void {
		if ( current_user_can( 'cadao_soan' ) && $thanh->get_node( 'new-content' ) ) {
			$thanh->add_node(
				array(
					'parent' => 'new-content',
					'id'     => 'new-tac-pham',
					'title'  => 'Tác phẩm',
					'href'   => home_url( '/bien-tap/them/' ),
				)
			);
		}
	}

	/** Màn Sửa tác phẩm (ẩn khỏi menu) giữ menu Phongdao mở. */
	public static function menu_dang_mo( $cha ) {
		$trang = sanitize_key( (string) ( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc để tô menu
		return SuaTacPham::TRANG === $trang ? BanLamViec::TRANG : $cha;
	}

	/** Màn Sửa tác phẩm (ẩn khỏi menu) đánh dấu mục "Tác phẩm" đang mở. */
	public static function muc_dang_mo( $muc ) {
		$trang = sanitize_key( (string) ( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc để tô menu
		return SuaTacPham::TRANG === $trang ? DanhSachTacPham::TRANG : $muc;
	}

	public function hien(): void {
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc để chọn thẻ, hồ sơ
		echo NguonCaiDat::hien( sanitize_key( wp_unslash( $_GET['the'] ?? '' ) ), (int) ( $_GET['ho_so'] ?? 0 ), NguonCaiDat::bao() ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong NguonCaiDat
	}
}
