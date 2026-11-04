<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\HaTang\KhaiBao;

defined( 'ABSPATH' ) || exit;

/**
 * Khung trang người đọc: đầu trang + thanh thể loại, chân trang + thanh dưới điện thoại (docs/27 mục 3; D-092, D-093).
 */
final class KhungTrang {

	/** Thanh thể loại thay menu "Đọc ▾" (D-093). Thể loại chưa qua cổng hoặc 0 bài sẽ tự ẩn khi có dữ liệu. */
	private const THE_LOAI = array(
		'trang-chu' => 'Trang chủ',
		'kho'       => 'Tất cả',
		'ca-dao'    => 'Ca dao',
		'tuc-ngu'   => 'Tục ngữ – Thành ngữ',
		'dong-dao'  => 'Đồng dao',
		've'        => 'Vè',
		'cau-do'    => 'Câu đố',
		'tuyen-tap' => 'Tuyển tập',
	);

	/** Mục đang xem trên thanh thể loại: trang chủ, Tất cả, thể loại (trang thể loại hoặc thể loại của tác phẩm). */
	public static function dang_xem(): string {
		if ( is_front_page() ) {
			return 'trang-chu';
		}
		if ( is_post_type_archive( KhaiBao::TAC_PHAM ) ) {
			return (string) get_query_var( KhaiBao::THE_LOAI ) ?: 'kho';
		}
		if ( is_tax( KhaiBao::THE_LOAI ) ) {
			return (string) get_queried_object()->slug;
		}
		if ( is_singular( KhaiBao::TAC_PHAM ) ) {
			return (string) DocBai::the_loai( get_queried_object() );
		}
		return is_post_type_archive( KhaiBao::TUYEN_TAP ) || is_singular( KhaiBao::TUYEN_TAP ) ? 'tuyen-tap' : '';
	}

	/** Người xem có quyền biên tập (đầu trang, thanh dưới đổi theo màn mẫu nhóm B). */
	private static function bien_tap(): bool {
		return current_user_can( 'cadao_soan' );
	}

	public static function dau_trang(): string {
		$dang  = self::dang_xem();
		$menu  = '';
		$nhom  = array(
			'Khám phá' => array( 'tuyen-tap' => 'Tuyển tập', 'chu-de' => 'Chủ đề', 'theo-dip' => 'Theo dịp', 'ban-do' => 'Vùng & Bản đồ', 'mo-tip' => 'Mô-típ', 'ngau-nhien' => 'Một câu ngẫu nhiên', 'kham-pha' => 'Mọi lối khám phá' ),
			'Tra cứu'  => array( 'tim' => 'Tìm', 'tu-dien' => 'Từ điển', 'hay-bi-nham' => 'Hay bị nhầm là ca dao', 'phuong-phap' => 'Phương pháp & nguồn' ),
		);
		foreach ( $nhom as $ten => $muc ) {
			$menu .= '<details class="nav-item"><summary>' . $ten . '</summary><div class="nav-panel">';
			foreach ( $muc as $ma => $chu ) {
				$menu .= Ve::co( $ma ) ? Ve::lien_ket( $ma, $chu ) : '';
			}
			$menu .= '</div></details>';
		}
		$thanh = '';
		foreach ( self::THE_LOAI as $ma => $chu ) {
			$thanh .= Ve::lien_ket( $ma, $chu, '', $ma === $dang ? ' aria-current="page"' : '' );
		}
		return '<a class="skip" href="#main">Bỏ qua điều hướng</a>'
			. '<header class="site-header">' . Ve::brand()
			. '<nav class="desktop-nav" aria-label="Điều hướng chính">' . $menu . '</nav>'
			. '<form class="header-search" role="search" action="' . esc_url( Ve::url( 'tim' ) ) . '"><label class="sr-only" for="header-q">Tìm trong thư viện</label><input id="header-q" name="q" placeholder="Tìm câu, từ…" type="search"></form>'
			. ( self::bien_tap()
				? Ve::lien_ket( 'bien-tap', Ve::icon( 'check' ) . ' Hộp việc <span class="dem">' . \Phongdao\KhoLuu\Viec::dem_cho() . '</span>', 'btn compact ghost header-saved' )
				: Ve::lien_ket( 'da-luu', Ve::icon( 'save' ) . ' Đã lưu', 'btn compact ghost header-saved' ) )
			. Ve::lien_ket( 'tim', Ve::icon( 'search' ) . ' Tìm', 'btn compact ghost mobile-search' )
			. '<button type="button" class="btn compact" data-action="theme" aria-label="Đổi sáng tối">' . Ve::icon( 'sun' ) . '</button>'
			. '</header><nav class="genre-bar" aria-label="Thể loại"><div class="genre-bar-inner">' . $thanh . '</div></nav>';
	}

	public static function chan_trang(): string {
		$lien = '';
		foreach ( array( 've-du-an' => 'Về dự án', 'phuong-phap' => 'Phương pháp', 'nguon' => 'Nguồn', 'chinh-sach' => 'Chính sách thu thập dữ liệu', 'rieng-tu' => 'Quyền riêng tư', 'gop' => 'Góp tư liệu' ) as $ma => $chu ) {
			$lien .= Ve::co( $ma ) ? Ve::lien_ket( $ma, $chu ) : '';
		}
		$dang = self::dang_xem();
		$duoi = '';
		// Người biên soạn ở trang biên tập / trang bài: thanh dưới Đọc · Hộp việc · Tác phẩm · Thêm (màn mẫu B01–B04)
		$muc = self::bien_tap() && ( \Phongdao\GiaoTiep\TrangCoDinh\Module::la_bien_tap() || is_singular( KhaiBao::TAC_PHAM ) )
			? array( 'trang-chu' => array( 'Đọc', 'home' ), 'bien-tap' => array( 'Hộp việc', 'check' ), 'bt-tac-pham' => array( 'Tác phẩm', 'book' ), 'bt-them' => array( 'Thêm', 'edit' ) )
			: array( 'trang-chu' => array( 'Trang chủ', 'home' ), 'tim' => array( 'Tìm', 'search' ), 'kham-pha' => array( 'Khám phá', 'compass' ), 'da-luu' => array( 'Đã lưu', 'save' ) );
		$dang = array( 'bien-tap' => 'bien-tap', 'bien-tap/tac-pham' => 'bt-tac-pham', 'bien-tap/them' => 'bt-them' )[ \Phongdao\GiaoTiep\TrangCoDinh\Module::dang_xem() ] ?? $dang;
		foreach ( $muc as $ma => [ $chu, $hinh ] ) {
			$duoi .= Ve::lien_ket( $ma, Ve::icon( $hinh ) . '<span>' . $chu . '</span>', $ma === $dang ? 'active' : '', $ma === $dang ? ' aria-current="page"' : '' );
		}
		return '<footer class="site-footer"><p>' . Ve::brand() . '</p><p>Thư viện Văn học Dân gian Việt Nam<br>Mỗi câu ghi rõ lấy từ đâu, và đã được kiểm đến mức nào.</p>'
			. '<div class="footer-links">' . $lien . '</div><p>Phần dự án tự viết: CC BY 4.0. Nội dung nguồn khác theo giấy phép tương ứng.<br>Không quảng cáo, không theo dõi.</p></footer>'
			. '<nav class="bottom-nav" aria-label="Điều hướng nhanh">' . $duoi . '</nav>';
	}
}
