<?php

namespace Phongdao\GiaoTiep\QuanTri;

defined( 'ABSPATH' ) || exit;

/**
 * Mục menu "Phongdao" trong wp-admin (D-100, D-103; làm lại D-121, docs/42 mục 2): ba dòng việc — **Hôm nay** (mở khi
 * bấm "Phongdao" và ngay sau khi đăng nhập), **Bài mới** (Kho, kèm số câu máy gợi ý), **Hộp việc** (kèm số việc chờ),
 * **Bổ sung**, rồi Tác phẩm, Tuyển tập, Danh mục, Nguồn & cài đặt (chỉ Quản trị). "Sửa tác phẩm", "Thao tác hàng loạt",
 * "Số liệu", "Tất cả mục" là màn con, không nằm trên menu; khi mở, menu Phongdao vẫn mở và tô đúng mục người vừa đến.
 * Menu gốc WordPress (Bài viết, Trang, Bình luận, Giao diện, Plugin…): người biên soạn không thấy; người quản trị thấy
 * gom dưới một mục "WordPress" cuối menu (D-121 thay ý "không ẩn menu nào" của D-100). Màn gốc vẫn tự kiểm quyền.
 */
final class Module implements \Phongdao\HaTang\Module {

	public const TRANG = 'phongdao';

	/** Mục gốc WordPress gom khỏi menu trái (D-121): đường dẫn => tên. */
	public const MUC_WP = array(
		'index.php'               => 'Bảng tin',
		'edit.php'                => 'Bài viết',
		'upload.php'              => 'Thư viện ảnh',
		'edit.php?post_type=page' => 'Trang',
		'edit-comments.php'       => 'Bình luận',
		'themes.php'              => 'Giao diện',
		'plugins.php'             => 'Plugin',
		'users.php'               => 'Tài khoản',
		'profile.php'             => 'Hồ sơ',
		'tools.php'               => 'Công cụ',
		'options-general.php'     => 'Cài đặt',
	);

	/** @var array<string,array{ten:string,quyen:string,con:array}> mục gốc đã gom trong lượt này, kèm màn con */
	private static array $muc_wp = array();

	public function dang_ky(): void {
		add_action( 'admin_menu', array( $this, 'them_menu' ) );
		add_filter( 'login_redirect', array( self::class, 'sau_dang_nhap' ), 10, 3 );
		add_filter( 'submenu_file', array( self::class, 'muc_dang_mo' ) );
		add_filter( 'parent_file', array( self::class, 'menu_dang_mo' ) );
		add_action( 'admin_bar_menu', array( self::class, 'them_moi' ), 80 );
		add_action( 'load-index.php', array( self::class, 'vao_bang_tin' ) );
		add_action( 'admin_menu', array( self::class, 'gon_menu_wp' ), 999 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'nap_chung' ) );
		add_action( 'admin_footer', array( self::class, 'thanh_duoi' ) );
		add_filter( 'admin_body_class', array( self::class, 'lop_than' ) );
		// Lối gốc WordPress của tác phẩm (danh sách, màn sửa, thêm mới) chuyển sang màn Phongdao; không ẩn menu nào (D-100, D-104)
		add_action( 'load-edit.php', array( self::class, 'chuyen_loi_goc' ) );
		add_action( 'load-post.php', array( self::class, 'chuyen_loi_goc' ) );
		add_action( 'load-post-new.php', array( self::class, 'chuyen_loi_goc' ) );
		add_action( 'transition_comment_status', array( self::class, 'binh_luan_doi' ), 10, 3 );
		NguonCaiDat::dang_ky();
		SuaTacPham::dang_ky();
		add_action( 'wp_ajax_phongdao_them_muc', array( DanhMuc::class, 'ajax' ) );
		DanhSachKho::dang_ky();
		DanhSachTacPham::dang_ky();
		\Phongdao\DichVu\UngVien\NhapTep::dang_ky();
		ThemTacPham::dang_ky();
		BoSung::dang_ky();
		TrangLo::dang_ky();
		SuaTuyenTap::dang_ky();
		NapNhap::dang_ky();
		DonChuDe::dang_ky();
	}

	public function them_menu(): void {
		$ban = static function (): void {
			echo BanLamViec::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		};
		$cha = BanLamViec::TRANG;
		add_menu_page( 'Phongdao', 'Phongdao', 'cadao_soan', $cha, $ban, 'dashicons-book-alt', 3 );
		add_submenu_page( $cha, 'Hôm nay', 'Hôm nay', 'cadao_soan', $cha, $ban );
		$tep_goc = dirname( __DIR__, 3 ) . '/ca-dao.php';
		$goi_y   = count( \Phongdao\DichVu\UngVien\UngVien::goi_y_hom_nay()['ids'] );
		$kho = add_submenu_page( $cha, 'Bài mới', 'Bài mới' . ( $goi_y ? ' <span class="phongdao-menu-so">' . number_format_i18n( $goi_y ) . '</span>' : '' ), 'cadao_soan', DanhSachKho::TRANG, static function (): void {
			echo DanhSachKho::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $kho ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $kho, $tep_goc ): void {
				if ( $trang === $kho ) {
					wp_enqueue_style( 'phongdao-admin-tac-pham', plugins_url( 'assets/admin-tac-pham.css', $tep_goc ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_style( 'phongdao-admin-kho', plugins_url( 'assets/admin-kho.css', $tep_goc ), array( 'phongdao-admin-tac-pham' ), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-lo', plugins_url( 'assets/admin-lo.js', $tep_goc ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
					wp_enqueue_style( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.css', $tep_goc ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-chon', plugins_url( 'assets/admin-chon.js', $tep_goc ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
					wp_enqueue_script( 'phongdao-admin-dang-nhanh', plugins_url( 'assets/admin-dang-nhanh.js', $tep_goc ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
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
		// Tuyển tập (D-116): danh sách và màn soạn một bộ
		$tt = add_submenu_page( $cha, 'Tuyển tập', 'Tuyển tập', 'cadao_soan', SuaTuyenTap::TRANG, static function (): void {
			echo SuaTuyenTap::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $tt ) {
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $tt ): void {
				if ( $trang === $tt ) {
					wp_enqueue_style( 'phongdao-admin-ban', plugins_url( 'assets/admin-ban.css', dirname( __DIR__, 3 ) . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN );
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
					\Phongdao\GiaoTiep\BienTap\AnhMinhHoa::nap_ma();
					wp_enqueue_script( 'phongdao-giu-nhap', plugins_url( 'assets/giu-nhap.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
					wp_enqueue_style( 'phongdao-giu-nhap', plugins_url( 'assets/giu-nhap.css', $tep ), array(), CADAO_PHIEN_BAN );
					wp_enqueue_script( 'phongdao-admin-sua', plugins_url( 'assets/admin-sua.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
					EditorDonGian::nap();
				}
			} );
		}
		// Nguồn & cài đặt giữ đường dẫn admin.php?page=phongdao (hồ sơ nguồn, máy đang làm… trỏ tới đây); chỉ Quản trị
		add_submenu_page( $cha, 'Quản lý danh mục', 'Danh mục', 'manage_categories', DanhMuc::TRANG, static function (): void { echo DanhMuc::hien(); } );
		// Dọn chủ đề (D-114): màn con của Danh mục, ẩn khỏi menu
		$dcd = add_submenu_page( '', 'Dọn chủ đề', 'Dọn chủ đề', 'manage_categories', DonChuDe::TRANG, static function (): void {
			echo DonChuDe::hien(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		} );
		if ( $dcd ) {
			add_action( 'load-' . $dcd, static function (): void {
				global $title;
				$title = 'Dọn chủ đề'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- màn ẩn khỏi menu không có tiêu đề cho thẻ <title>
			} );
			add_action( 'admin_enqueue_scripts', static function ( string $trang ) use ( $dcd ): void {
				if ( $trang === $dcd ) {
					wp_enqueue_style( 'phongdao-admin-ban', plugins_url( 'assets/admin-ban.css', dirname( __DIR__, 3 ) . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN );
				}
			} );
		} // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị
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
		// Màn con: Số liệu (bảng kê nặng tách khỏi Hôm nay), Tất cả mục (mục "Thêm" ở thanh dưới điện thoại)
		foreach ( array( array( SoLieu::TRANG, 'Số liệu', array( SoLieu::class, 'hien' ) ), array( TatCaMuc::TRANG, 'Tất cả mục', array( TatCaMuc::class, 'hien' ) ) ) as [ $ma, $ten, $ham ] ) {
			$moc = add_submenu_page( '', $ten, $ten, 'cadao_soan', $ma, static function () use ( $ham ): void {
				echo $ham(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
			} );
			if ( $moc ) {
				add_action( 'load-' . $moc, static function () use ( $ten ): void {
					global $title;
					$title = $ten; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- màn ẩn khỏi menu không có tiêu đề cho thẻ <title>
				} );
			}
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

	/**
	 * "Thêm mới → Tác phẩm" ở thanh trên cùng, mở màn Thêm tác phẩm trong wp-admin (có kiểm trùng khi dán lời); bấm thẳng
	 * "Thêm mới" cũng tới đó. Người biên soạn (không quản trị) không thấy lối thêm bài viết, trang, ảnh và bình luận của
	 * WordPress (D-121).
	 */
	public static function them_moi( \WP_Admin_Bar $thanh ): void {
		$moi = $thanh->get_node( 'new-content' );
		if ( ! current_user_can( 'cadao_soan' ) || ! $moi ) {
			return;
		}
		$thanh->add_node( array( 'href' => ThemTacPham::url() ) + (array) $moi );
		$thanh->add_node(
			array(
				'parent' => 'new-content',
				'id'     => 'new-tac-pham',
				'title'  => 'Tác phẩm',
				'href'   => ThemTacPham::url(),
			)
		);
		if ( ! current_user_can( 'manage_options' ) ) {
			foreach ( array( 'new-post', 'new-page', 'new-media', 'comments' ) as $nut ) {
				$thanh->remove_node( $nut );
			}
		}
	}

	/** Mục gốc WordPress đã gom khỏi menu trái trong lượt này (cho màn "WordPress", "Tất cả mục"). */
	public static function muc_wp(): array {
		return self::$muc_wp;
	}

	/**
	 * Menu chỉ còn việc của Phongdao (D-121): bỏ các mục gốc WordPress khỏi menu trái của người biên soạn; với người quản
	 * trị, gom chúng (cùng màn con) dưới một mục "WordPress" cuối menu. Chỉ đổi menu, không đổi quyền: mỗi màn gốc vẫn tự
	 * kiểm quyền như cũ. Người không có quyền biên soạn (Cộng tác…) giữ menu WordPress như cũ.
	 */
	public static function gon_menu_wp(): void {
		global $menu, $submenu;
		self::$muc_wp = array();
		if ( ! current_user_can( 'cadao_soan' ) || ! is_array( $menu ) ) {
			return;
		}
		foreach ( $menu as $k => $m ) {
			$slug = (string) ( $m[2] ?? '' );
			if ( isset( self::MUC_WP[ $slug ] ) ) {
				self::$muc_wp[ $slug ] = array( 'ten' => self::MUC_WP[ $slug ], 'quyen' => (string) ( $m[1] ?? 'read' ), 'con' => is_array( $submenu[ $slug ] ?? null ) ? array_values( $submenu[ $slug ] ) : array() );
				unset( $menu[ $k ], $submenu[ $slug ] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- gom menu gốc
			} elseif ( str_starts_with( $slug, 'separator' ) && 'separator-last' !== $slug ) {
				unset( $menu[ $k ] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- vạch ngăn thừa sau khi gom
			}
		}
		// Theo thứ tự quen của WordPress (Bảng tin, Bài viết, Thư viện ảnh, Trang…), không theo thứ tự đăng ký
		self::$muc_wp = array_merge( array_intersect_key( self::MUC_WP, self::$muc_wp ), self::$muc_wp );
		if ( ! self::$muc_wp || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		add_menu_page( 'WordPress', 'WordPress', 'manage_options', TatCaMuc::TRANG_WP, static function (): void {
			echo TatCaMuc::hien_wp(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lớp hiển thị thoát giá trị động
		}, 'dashicons-wordpress', 100 );
		foreach ( self::$muc_wp as $slug => $g ) {
			add_submenu_page( TatCaMuc::TRANG_WP, $g['ten'], $g['ten'], $g['quyen'], $slug );
		}
		if ( isset( $submenu[ TatCaMuc::TRANG_WP ][0] ) ) {
			$submenu[ TatCaMuc::TRANG_WP ][0][0] = 'Tất cả màn WordPress'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- đổi tên mục đầu tự thêm
		}
	}

	/**
	 * Mục menu nên tô cho một màn con (ẩn khỏi menu): Sửa tác phẩm → Tác phẩm; màn lô → nơi tạo lô (Kho → Bài mới, Tác
	 * phẩm, Hộp việc), danh sách các lô → Hôm nay. '' khi không phải màn con của Phongdao.
	 */
	public static function muc_cha(): string {
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc để tô menu
		$trang = sanitize_key( (string) ( $_GET['page'] ?? '' ) );
		if ( \Phongdao\HaTang\KhaiBao::TAC_PHAM === ( $_GET['post_type'] ?? '' ) && DanhMuc::duoc( sanitize_key( (string) ( $_GET['taxonomy'] ?? '' ) ) ) ) {
			return DanhMuc::TRANG;
		}
		if ( SuaTacPham::TRANG === $trang ) {
			return DanhSachTacPham::TRANG;
		}
		if ( ThemTacPham::TRANG === $trang ) {
			return DanhSachKho::TRANG;
		}
		if ( DonChuDe::TRANG === $trang ) {
			return DanhMuc::TRANG;
		}
		if ( SoLieu::TRANG === $trang ) {
			return BanLamViec::TRANG;
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

	/**
	 * Đích Phongdao cho lối gốc WordPress của tác phẩm (báo cáo trinh sát lần 2 mục 5.3): `edit.php?post_type=tac_pham` →
	 * Tác phẩm; `post.php?post=…&action=edit` của tác phẩm → Sửa tác phẩm; `post-new.php?post_type=tac_pham` → Thêm tác
	 * phẩm. '' = không chuyển (loại bài khác, thao tác khác như thùng rác, khôi phục của WordPress).
	 *
	 * @param array<string,mixed> $q
	 */
	public static function dich_loi_goc( string $man, array $q ): string {
		if ( 'edit.php' === $man && \Phongdao\HaTang\KhaiBao::TAC_PHAM === ( $q['post_type'] ?? '' ) && empty( $q['action'] ) && empty( $q['action2'] ) ) {
			return admin_url( 'admin.php?page=' . DanhSachTacPham::TRANG );
		}
		if ( 'post-new.php' === $man && \Phongdao\HaTang\KhaiBao::TAC_PHAM === ( $q['post_type'] ?? '' ) ) {
			return ThemTacPham::url();
		}
		$id = absint( $q['post'] ?? 0 );
		if ( 'post.php' === $man && 'edit' === ( $q['action'] ?? '' ) && $id && \Phongdao\HaTang\KhaiBao::TAC_PHAM === get_post_type( $id ) ) {
			return SuaTacPham::url( $id );
		}
		return '';
	}

	public static function chuyen_loi_goc(): void {
		$dich = self::dich_loi_goc( (string) ( $GLOBALS['pagenow'] ?? '' ), wp_unslash( $_GET ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc để chuyển hướng
		if ( '' !== $dich && 'GET' === ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
			wp_safe_redirect( $dich );
			exit;
		}
	}

	/** Màn hiện tại là một màn Phongdao (khung giao diện chung, thanh dưới trên điện thoại). */
	public static function la_man_phongdao(): bool {
		$trang = sanitize_key( (string) ( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		return current_user_can( 'cadao_soan' ) && str_starts_with( $trang, 'phongdao' );
	}

	/** Bộ kiểu chung (menu ở mọi màn wp-admin của người biên soạn; khung, nút, thẻ, thanh dưới ở màn Phongdao). */
	public static function nap_chung(): void {
		if ( current_user_can( 'cadao_soan' ) ) {
			wp_enqueue_style( 'phongdao-admin-chung', plugins_url( 'assets/admin-chung.css', dirname( __DIR__, 3 ) . '/ca-dao.php' ), array( 'dashicons' ), CADAO_PHIEN_BAN );
		}
	}

	/** `phongdao-menu`: mọi màn wp-admin của người biên soạn; `pq`: màn Phongdao (khung chung, thanh dưới điện thoại). */
	public static function lop_than( $lop ): string {
		return (string) $lop . ( current_user_can( 'cadao_soan' ) ? ' phongdao-menu' : '' ) . ( self::la_man_phongdao() ? ' pq phongdao-co-thanh-duoi' : '' );
	}

	/**
	 * Người biên soạn (không quản trị) mở `/wp-admin/` thì vào Hôm nay: trang chào mừng WordPress không còn trên menu của họ
	 * (D-121). Người quản trị vẫn mở được Bảng tin từ mục "WordPress".
	 */
	public static function vao_bang_tin(): void {
		if ( current_user_can( 'cadao_soan' ) && ! current_user_can( 'manage_options' ) && 'GET' === ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
			wp_safe_redirect( BanLamViec::url() );
			exit;
		}
	}

	/**
	 * Thanh dưới cố định trên điện thoại (D-121): Hôm nay · Bài mới · Hộp việc · Bổ sung · Thêm (Tất cả mục), tô mục đang
	 * mở (màn con tô mục cha; màn khác tô "Thêm"). Chỉ là liên kết; máy tính không hiện (CSS).
	 */
	public static function thanh_duoi(): void {
		if ( ! self::la_man_phongdao() ) {
			return;
		}
		$trang = sanitize_key( (string) ( $_GET['page'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$dang  = '' !== self::muc_cha() ? self::muc_cha() : $trang;
		$dang  = in_array( $dang, array( BanLamViec::TRANG, DanhSachKho::TRANG, DanhSachViec::TRANG, BoSung::TRANG ), true ) ? $dang : TatCaMuc::TRANG;
		$cho   = \Phongdao\KhoLuu\Viec::dem_hop_viec();
		$o     = '';
		foreach (
			array(
				array( BanLamViec::TRANG, 'Hôm nay', 'dashicons-admin-home', '' ),
				array( DanhSachKho::TRANG, 'Bài mới', 'dashicons-plus-alt', '' ),
				array( DanhSachViec::TRANG, 'Hộp việc', 'dashicons-yes-alt', $cho ? '<span class="phongdao-thanh-duoi-so">' . esc_html( number_format_i18n( $cho ) ) . '</span>' : '' ),
				array( BoSung::TRANG, 'Bổ sung', 'dashicons-edit', '' ),
				array( TatCaMuc::TRANG, 'Thêm', 'dashicons-menu', '' ),
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
