<?php

namespace Phongdao\GiaoTiep\QuanTri;

defined( 'ABSPATH' ) || exit;

/**
 * Hai màn chỉ gồm lối đi (D-121, docs/42 mục 2):
 * - **Tất cả mục** (`phongdao-muc`): mục "Thêm" ở thanh dưới điện thoại — mọi màn Phongdao theo nhóm, kèm phần WordPress
 *   với người quản trị.
 * - **WordPress** (`phongdao-wordpress`, chỉ người quản trị): mục cuối menu trái, gom các màn gốc của WordPress (Bài viết,
 *   Trang, Giao diện, Plugin, Cài đặt…) cùng các màn con của chúng. Người biên soạn không có mục này (menu chỉ còn việc
 *   của Phongdao); các màn gốc vẫn tự kiểm quyền như cũ.
 */
final class TatCaMuc {

	public const TRANG = 'phongdao-muc';

	public const TRANG_WP = 'phongdao-wordpress';

	public static function url(): string {
		return admin_url( 'admin.php?page=' . self::TRANG );
	}

	/** Địa chỉ một mục menu WordPress (màn gốc `*.php` hoặc màn của plugin `?page=`). */
	public static function url_muc( string $slug, string $cha = '' ): string {
		if ( str_ends_with( explode( '?', $slug )[0], '.php' ) ) {
			return admin_url( $slug );
		}
		return str_contains( $cha, '.php' ) ? add_query_arg( 'page', $slug, admin_url( $cha ) ) : admin_url( 'admin.php?page=' . $slug );
	}

	/** @return array<string,array<int,array{0:string,1:string,2:string}>> nhóm => [chữ, đường dẫn, mô tả] */
	public static function nhom_phongdao(): array {
		$nhom = array(
			'Việc hằng ngày' => array(
				array( 'Hôm nay', BanLamViec::url(), 'ba việc chính, việc gấp, đang làm dở' ),
				array( 'Bài mới', DanhSachKho::url(), 'câu trong Kho chờ đăng, Đăng nhanh' ),
				array( 'Hộp việc', DanhSachViec::url( array() ), 'việc cần người quyết' ),
				array( 'Bổ sung', BoSung::url(), 'làm bài đã đăng đầy đủ hơn' ),
			),
			'Kho bài'        => array(
				array( 'Tác phẩm', admin_url( 'admin.php?page=' . DanhSachTacPham::TRANG ), 'mọi bài, lọc theo trạng thái và phần còn thiếu' ),
				array( 'Tuyển tập', SuaTuyenTap::url(), 'bộ câu theo chủ đề, dịp' ),
				array( 'Thêm tác phẩm', ThemTacPham::url(), 'dán lời, máy kiểm trùng ngay' ),
			),
			'Theo dõi'       => array(
				array( 'Số liệu', SoLieu::url(), 'kho theo cấp, ngoại lệ dữ liệu, việc vào / ra' ),
				array( 'Lịch sử thao tác hàng loạt', TrangLo::url(), 'các lô đã chạy, kết quả từng mục' ),
			),
			'Thiết lập'      => array(),
		);
		if ( current_user_can( 'manage_categories' ) ) {
			$nhom['Thiết lập'][] = array( 'Danh mục', admin_url( 'admin.php?page=' . DanhMuc::TRANG ), 'chủ đề, vùng, mô-típ, hình thức' );
		}
		if ( current_user_can( NguonCaiDat::QUYEN ) ) {
			$nhom['Thiết lập'][] = array( 'Nguồn & cài đặt', admin_url( 'admin.php?page=' . Module::TRANG ), 'hồ sơ nguồn, máy thu thập, cài đặt' );
		}
		$nhom['Thiết lập'][] = array( 'Hồ sơ của bạn', admin_url( 'profile.php' ), 'tên hiển thị, mật khẩu' );
		return $nhom;
	}

	private static function ds( array $muc ): string {
		return '<ul class="pq-ds-muc">' . implode( '', array_map( static fn( array $m ): string => '<li><a href="' . esc_url( $m[1] ) . '">' . esc_html( $m[0] ) . '</a>' . ( '' === ( $m[2] ?? '' ) ? '' : '<span>' . esc_html( $m[2] ) . '</span>' ) . '</li>', $muc ) ) . '</ul>';
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem trang này.</p></div>';
		}
		$o = '';
		foreach ( self::nhom_phongdao() as $ten => $muc ) {
			$o .= $muc ? '<section class="pq-khoi"><h2>' . esc_html( $ten ) . '</h2>' . self::ds( $muc ) . '</section>' : '';
		}
		$o .= '<section class="pq-khoi"><h2>Trang người đọc</h2>' . self::ds( array( array( 'Xem site ↗', home_url( '/' ), 'trang chủ phongdao như người đọc thấy' ) ) ) . '</section>';
		if ( current_user_can( 'manage_options' ) && Module::muc_wp() ) {
			$o .= '<section class="pq-khoi"><h2>WordPress (người quản trị)</h2>' . self::ds( array( array( 'Mọi màn của WordPress', admin_url( 'admin.php?page=' . self::TRANG_WP ), 'bài viết, trang, giao diện, plugin, tài khoản, cài đặt' ) ) ) . '</section>';
		}
		return '<div class="wrap pq-man phongdao-tat-ca-muc"><div class="pq-dau"><div class="pq-dau-chu"><h1>Tất cả mục</h1><p>Bốn việc chính nằm ở thanh dưới; các mục còn lại ở đây.</p></div></div><div class="pq-luoi hai">' . $o . '</div></div>';
	}

	/** Màn "WordPress": mọi màn gốc WordPress đã gom khỏi menu trái, mỗi nhóm kèm màn con người này được mở. */
	public static function hien_wp(): string {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền xem trang này.</p></div>';
		}
		$o = '';
		foreach ( Module::muc_wp() as $slug => $g ) {
			$muc = array();
			foreach ( $g['con'] ?: array( array( $g['ten'], $g['quyen'], $slug ) ) as $c ) {
				$chu = trim( wp_strip_all_tags( (string) preg_replace( '#\s*<span.*$#s', '', (string) ( $c[0] ?? '' ) ) ) ); // bỏ số đếm (Cập nhật 0, Giao diện 0…)
				if ( '' !== $chu && current_user_can( (string) ( $c[1] ?? 'manage_options' ) ) ) {
					$muc[] = array( $chu, self::url_muc( (string) $c[2], $slug ), '' );
				}
			}
			$o .= $muc ? '<section class="pq-khoi"><h2><a href="' . esc_url( self::url_muc( $slug ) ) . '">' . esc_html( $g['ten'] ) . '</a></h2>' . self::ds( $muc ) . '</section>' : '';
		}
		return '<div class="wrap pq-man phongdao-tat-ca-muc"><div class="pq-dau"><div class="pq-dau-chu"><h1>WordPress</h1><p>Các màn gốc của WordPress, gom khỏi menu để menu chỉ còn việc của Phongdao. Người biên soạn không thấy mục này.</p></div></div>'
			. ( '' === $o ? '<p>Không có màn nào.</p>' : '<div class="pq-luoi ba">' . $o . '</div>' ) . '</div>';
	}
}
