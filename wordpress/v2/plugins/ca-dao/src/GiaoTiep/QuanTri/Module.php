<?php

namespace Phongdao\GiaoTiep\QuanTri;

defined( 'ABSPATH' ) || exit;

/**
 * Mục menu "Phongdao" trong wp-admin (D-100, D-103; docs/32 mục 3.2): ba dòng việc — **Hôm nay** (mở khi bấm "Phongdao"
 * và ngay sau khi đăng nhập), **Bài mới** (Kho), **Hộp việc** (kèm số việc chờ), **Bổ sung**, rồi Tác phẩm, Nguồn & cài
 * đặt (chỉ Quản trị). "Sửa tác phẩm" và "Thao tác hàng loạt" là màn con (mở từ danh sách, từ Hôm nay, từ màn kết quả),
 * không nằm trên menu; khi mở, menu Phongdao vẫn mở và tô đúng mục người vừa đến. Không ẩn menu nào của WordPress (D-100).
 */
final class Module implements \Phongdao\HaTang\Module {

	public const TRANG = 'phongdao';

	public function dang_ky(): void {
		add_action( 'admin_menu', array( $this, 'them_menu' ) );
		add_filter( 'login_redirect', array( self::class, 'sau_dang_nhap' ), 10, 3 );
		add_filter( 'submenu_file', array( self::class, 'muc_dang_mo' ) );
		add_filter( 'parent_file', array( self::class, 'menu_dang_mo' ) );
		add_action( 'admin_bar_menu', array( self::class, 'them_moi' ), 80 );
		add_action( 'load-index.php', array( self::class, 'trang_chao' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'nap_chung' ) );
		add_action( 'admin_footer', array( self::class, 'thanh_duoi' ) );
		add_filter( 'admin_body_class', array( self::class, 'lop_than' ) );
		add_action( 'transition_comment_status', array( self::class, 'binh_luan_doi' ), 10, 3 );
		NguonCaiDat::dang_ky();
		SuaTacPham::dang_ky();
		DanhSachKho::dang_ky();
		DanhSachTacPham::dang_ky();
		\Phongdao\DichVu\UngVien\NhapTep::dang_ky();
		ThemTacPham::dang_ky();
		BoSung::dang_ky();
		TrangLo::dang_ky();
	}

	public function them_menu(): void {
		$ban = static function (): void {
			echo BanLamViec::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		};
		$cha = BanLamViec::TRANG;
		$moc_ban = add_menu_page( 'Phongdao', 'Phongdao', 'cadao_soan', $cha, $ban, 'dashicons-book-alt', 3 );
		if ( $moc_ban ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $moc_ban ): void {
				if ( $trang === $moc_ban ) {
					wp_enqueue_style( 'phongdao-admin-ban', plugins_url( 'assets/admin-ban.css', dirname( __DIR__, 3 ) . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN );
				}
			} );
		}
		add_submenu_page( $cha, 'Hôm nay', 'Hôm nay', 'cadao_soan', $cha, $ban );
		$tep_goc = dirname( __DIR__, 3 ) . '/ca-dao.php';
		$kho = add_submenu_page( $cha, 'Bài mới', 'Bài mới', 'cadao_soan', DanhSachKho::TRANG, static function (): void {
			echo DanhSachKho::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $kho ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $kho, $tep_goc ): void {
				if ( $trang === $kho ) {
					wp_enqueue_style( 'phongdao-admin-tac-pham', plugins_url( 'assets/admin-tac-pham.css', $tep_goc ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_style( 'phongdao-admin-kho', plugins_url( 'assets/admin-kho.css', $tep_goc ), array( 'phongdao-admin-tac-pham' ), CADAO_PHIEN_BAN );
					wp_enqueue_style( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.css', $tep_goc ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.js', $tep_goc ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
				}
			} );
		}
		$cho = \Phongdao\KhoLuu\Viec::dem_hop_viec();
		$moc = add_submenu_page( $cha, 'Hộp việc', 'Hộp việc' . ( $cho ? ' <span class="awaiting-mod count-' . $cho . '"><span class="pending-count">' . number_format_i18n( $cho ) . '</span></span>' : '' ), 'cadao_soan', DanhSachViec::TRANG, static function (): void {
			echo DanhSachViec::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $moc ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $moc ): void {
				if ( $trang === $moc ) {
					$tep = dirname( __DIR__, 3 ) . '/ca-dao.php';
					wp_enqueue_style( 'phongdao-admin-viec', plugins_url( 'assets/admin-viec.css', $tep ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_style( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.css', $tep ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
					wp_enqueue_script( 'phongdao-admin-hop-viec', plugins_url( 'assets/admin-hop-viec.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
				}
			} );
		}
		$bs = add_submenu_page( $cha, 'Bổ sung', 'Bổ sung', 'cadao_soan', BoSung::TRANG, static function (): void {
			echo BoSung::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $bs ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $bs, $tep_goc ): void {
				if ( $trang === $bs ) {
					wp_enqueue_style( 'phongdao-admin-ban', plugins_url( 'assets/admin-ban.css', $tep_goc ), array(), CADAO_PHIEN_BAN );
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
					wp_enqueue_style( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.css', $tep ), array(), CADAO_PHIEN_BAN );
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
		// Nguồn & cài đặt giữ đường dẫn admin.php?page=phongdao (hồ sơ nguồn, máy đang làm… trỏ tới đây); chỉ Quản trị
		add_submenu_page( $cha, 'Nguồn & cài đặt', 'Nguồn &amp; cài đặt', NguonCaiDat::QUYEN, self::TRANG, array( $this, 'hien' ) );
		// Màn con: Thêm tác phẩm (mở từ Bài mới, Tác phẩm, "Thêm mới → Tác phẩm")
		$them = add_submenu_page( '', 'Thêm tác phẩm', 'Thêm tác phẩm', 'cadao_soan', ThemTacPham::TRANG, static function (): void {
			echo ThemTacPham::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $them ) {
			add_action( 'load-' . $them, static function (): void {
				global $title;
				$title = 'Thêm tác phẩm'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- màn ẩn khỏi menu không có tiêu đề cho thẻ <title>
			} );
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $them ): void {
				if ( $trang === $them ) {
					ThemTacPham::nap_ma();
				}
			} );
		}
		// Màn con (đường dẫn cũ giữ): Lịch sử thao tác hàng loạt mở từ Hôm nay, từ màn kết quả; xem trước lô mở từ danh sách
		$lo = add_submenu_page( '', 'Thao tác hàng loạt', 'Thao tác hàng loạt', 'cadao_soan', TrangLo::TRANG, static function (): void {
			echo TrangLo::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $lo ) {
			add_action( 'load-' . $lo, static function (): void {
				global $title;
				$title = 'Thao tác hàng loạt'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- màn ẩn khỏi menu không có tiêu đề cho thẻ <title>
			} );
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $lo ): void {
				if ( $trang === $lo ) {
					$tep = dirname( __DIR__, 3 ) . '/ca-dao.php';
					wp_enqueue_style( 'phongdao-admin-tac-pham', plugins_url( 'assets/admin-tac-pham.css', $tep ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_style( 'phongdao-admin-lo', plugins_url( 'assets/admin-lo.css', $tep ), array( 'phongdao-admin-tac-pham' ), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-lo', plugins_url( 'assets/admin-lo.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
					wp_enqueue_style( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.css', $tep ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
				}
			} );
		}
	}

	/** Đăng nhập xong (không có đích riêng) thì người biên soạn vào Hôm nay, không vào trang chào mừng WordPress. */
	public static function sau_dang_nhap( string $dich, string $yeu_cau, $nguoi ): string {
		$mac_dinh = '' === $yeu_cau || in_array( untrailingslashit( $yeu_cau ), array( untrailingslashit( admin_url() ), untrailingslashit( admin_url( 'index.php' ) ) ), true );
		if ( $mac_dinh && $nguoi instanceof \WP_User && $nguoi->has_cap( 'cadao_soan' ) ) {
			return admin_url( 'admin.php?page=' . BanLamViec::TRANG );
		}
		return $dich;
	}

	/** "Thêm mới → Tác phẩm" ở thanh trên cùng, mở màn Thêm tác phẩm trong wp-admin (có kiểm trùng khi dán lời). */
	public static function them_moi( \WP_Admin_Bar $thanh ): void {
		if ( current_user_can( 'cadao_soan' ) && $thanh->get_node( 'new-content' ) ) {
			$thanh->add_node(
				array(
					'parent' => 'new-content',
					'id'     => 'new-tac-pham',
					'title'  => 'Tác phẩm',
					'href'   => ThemTacPham::url(),
				)
			);
		}
	}

	/**
	 * Mục menu nên tô cho một màn con (ẩn khỏi menu): Sửa tác phẩm → Tác phẩm; màn lô → nơi tạo lô (Kho → Bài mới, Tác
	 * phẩm, Hộp việc), danh sách các lô → Hôm nay. '' khi không phải màn con của Phongdao.
	 */
	public static function muc_cha(): string {
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc để tô menu
		$trang = sanitize_key( (string) ( $_GET['page'] ?? '' ) );
		if ( SuaTacPham::TRANG === $trang ) {
			return DanhSachTacPham::TRANG;
		}
		if ( ThemTacPham::TRANG === $trang ) {
			return DanhSachKho::TRANG;
		}
		if ( TrangLo::TRANG !== $trang ) {
			return '';
		}
		$lo = absint( $_GET['lo'] ?? 0 );
		// phpcs:enable
		$v  = $lo ? \Phongdao\KhoLuu\Viec::lay( $lo ) : null;
		$dt = $v && \Phongdao\KhoLuu\Viec::LO === $v['loai'] ? (string) ( $v['noi_dung']['doi_tuong'] ?? 'viec' ) : '';
		return array( 'kho' => DanhSachKho::TRANG, 'post' => DanhSachTacPham::TRANG, 'viec' => DanhSachViec::TRANG )[ $dt ] ?? BanLamViec::TRANG;
	}

	/**
	 * Màn con (Sửa tác phẩm, Thao tác hàng loạt) giữ menu Phongdao mở. WordPress gọi `get_admin_page_parent()` ngay sau bộ
	 * lọc này; màn ẩn nằm trong `$submenu['']` nên hàm đó đặt lại cha thành rỗng (menu đóng). Quyền vào màn đã được kiểm
	 * xong lúc này, nên gỡ đúng màn đang mở khỏi `$submenu['']` để cha đã lọc được giữ.
	 */
	public static function menu_dang_mo( $cha ) {
		if ( '' === self::muc_cha() ) {
			return $cha;
		}
		global $submenu;
		$trang = sanitize_key( (string) ( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc để tô menu
		if ( isset( $submenu[''] ) && is_array( $submenu[''] ) ) {
			$submenu[''] = array_values( array_filter( $submenu[''], static fn( $m ): bool => ! is_array( $m ) || ( $m[2] ?? '' ) !== $trang ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- chỉ bỏ màn ẩn đang mở khỏi danh sách tìm cha
		}
		return BanLamViec::TRANG;
	}

	/** Màn hiện tại là một màn Phongdao (có thanh dưới trên điện thoại) hay trang chào mừng WordPress. */
	public static function la_man_phongdao(): bool {
		$trang = sanitize_key( (string) ( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		return current_user_can( 'cadao_soan' ) && ( str_starts_with( $trang, 'phongdao' ) || ( '' === $trang && 'index.php' === ( $GLOBALS['pagenow'] ?? '' ) ) );
	}

	public static function nap_chung(): void {
		if ( self::la_man_phongdao() ) {
			wp_enqueue_style( 'phongdao-admin-chung', plugins_url( 'assets/admin-chung.css', dirname( __DIR__, 3 ) . '/ca-dao.php' ), array( 'dashicons' ), CADAO_PHIEN_BAN );
		}
	}

	public static function lop_than( $lop ): string {
		return (string) $lop . ( self::la_man_phongdao() ? ' phongdao-co-thanh-duoi' : '' );
	}

	/**
	 * Thanh dưới cố định trên điện thoại (báo cáo trinh sát lần 2 mục 5.5): Hôm nay · Hộp việc · Bổ sung · Bài mới, tô mục
	 * đang mở (màn con tô mục cha). Chỉ là liên kết; máy tính không hiện (CSS).
	 */
	public static function thanh_duoi(): void {
		if ( ! self::la_man_phongdao() ) {
			return;
		}
		$trang = sanitize_key( (string) ( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$dang  = '' !== self::muc_cha() ? self::muc_cha() : $trang;
		$cho   = \Phongdao\KhoLuu\Viec::dem_hop_viec();
		$o     = '';
		foreach (
			array(
				array( BanLamViec::TRANG, 'Hôm nay', 'dashicons-calendar-alt', '' ),
				array( DanhSachViec::TRANG, 'Hộp việc', 'dashicons-yes-alt', $cho ? '<span class="phongdao-thanh-duoi-so">' . esc_html( number_format_i18n( $cho ) ) . '</span>' : '' ),
				array( BoSung::TRANG, 'Bổ sung', 'dashicons-edit', '' ),
				array( DanhSachKho::TRANG, 'Bài mới', 'dashicons-plus-alt', '' ),
			) as [ $ma, $chu, $hinh, $so ]
		) {
			$o .= '<a href="' . esc_url( admin_url( 'admin.php?page=' . $ma ) ) . '"' . ( $ma === $dang ? ' aria-current="page"' : '' ) . '><span class="dashicons ' . esc_attr( $hinh ) . '" aria-hidden="true"></span><span>' . esc_html( $chu ) . ( '' === $so ? '' : ' ' . $so ) . '</span></a>';
		}
		echo '<nav class="phongdao-thanh-duoi" aria-label="Phongdao: đổi màn nhanh">' . $o . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- đã thoát từng phần
	}

	/** Màn con đánh dấu đúng mục người vừa đến (Tác phẩm, Bài mới, Hộp việc hay Hôm nay). */
	public static function muc_dang_mo( $muc ) {
		$cha = self::muc_cha();
		return '' !== $cha ? $cha : $muc;
	}

	/**
	 * Trang chào mừng WordPress (`/wp-admin/`): khung "Phongdao hôm nay" ở đầu (ba số, nút bắt đầu), giữ nguyên phần còn
	 * lại của WordPress (D-104).
	 */
	public static function trang_chao(): void {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return;
		}
		add_action( 'admin_enqueue_scripts', static function (): void {
			wp_enqueue_style( 'phongdao-admin-ban', plugins_url( 'assets/admin-ban.css', dirname( __DIR__, 3 ) . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN );
		} );
		add_action( 'admin_notices', static function (): void {
			echo BanLamViec::khung_wp(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
	}

	/**
	 * Duyệt hay bỏ (thùng rác, tin rác) một cảm nghĩ ở màn Bình luận của WordPress thì thẻ "Người đọc gửi" đang
	 * mở của bình luận đó tự đóng, ghi "Đã xử lý ở Bình luận" và người bấm (báo cáo trinh sát lần 2, mục 1.10). Thẻ đang
	 * được quyết ở Hộp việc (trạng thái "đang làm") không bị đụng.
	 */
	public static function binh_luan_doi( $moi, $cu, $bl ): void {
		if ( ! $bl instanceof \WP_Comment || $moi === $cu || ! in_array( (string) $moi, array( 'approved', 'spam', 'trash' ), true ) ) {
			return;
		}
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			'UPDATE ' . \Phongdao\KhoLuu\Viec::bang() . " SET trang_thai = %s, quyet = 'binh_luan', nguoi_quyet = %d, quyet_luc = %s, truoc = %s, phien = phien + 1 WHERE doi_tuong_loai = 'comment' AND doi_tuong_id = %d AND loai = 'nguoi_doc' AND trang_thai IN ('cho','de_sau')",
			\Phongdao\KhoLuu\Viec::XONG,
			get_current_user_id(),
			gmdate( 'Y-m-d H:i:s' ),
			(string) wp_json_encode( array( 'trang_thai' => \Phongdao\KhoLuu\Viec::CHO, 'binh_luan' => (string) $moi ) ),
			(int) $bl->comment_ID
		) );
	}

	public function hien(): void {
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc để chọn thẻ, hồ sơ
		echo NguonCaiDat::hien( sanitize_key( wp_unslash( $_GET['the'] ?? '' ) ), (int) ( $_GET['ho_so'] ?? 0 ), NguonCaiDat::bao() ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong NguonCaiDat
	}
}
