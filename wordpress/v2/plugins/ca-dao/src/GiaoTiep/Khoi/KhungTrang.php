<?php

namespace Phongdao\GiaoTiep\Khoi;

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

	/** Mục đang xem: trang chủ, hoặc thể loại của tác phẩm (bài mẫu: theo tên đường dẫn "bai-mau-<thể loại>"). */
	public static function dang_xem(): string {
		if ( is_front_page() ) {
			return 'trang-chu';
		}
		$ten = (string) get_post_field( 'post_name', get_queried_object_id() );
		return str_starts_with( $ten, 'bai-mau-' ) ? substr( $ten, 8 ) : '';
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
				$menu .= Ve::lien_ket( $ma, $chu );
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
			. Ve::lien_ket( 'da-luu', Ve::icon( 'save' ) . ' Đã lưu', 'btn compact ghost header-saved' )
			. Ve::lien_ket( 'tim', Ve::icon( 'search' ) . ' Tìm', 'btn compact ghost mobile-search' )
			. '<button type="button" class="btn compact" data-action="theme" aria-label="Đổi sáng tối">' . Ve::icon( 'sun' ) . '</button>'
			. '</header><nav class="genre-bar" aria-label="Thể loại"><div class="genre-bar-inner">' . $thanh . '</div></nav>';
	}

	public static function chan_trang(): string {
		$lien = '';
		foreach ( array( 've-du-an' => 'Về dự án', 'phuong-phap' => 'Phương pháp', 'nguon' => 'Nguồn', 'chinh-sach' => 'Chính sách thu thập dữ liệu', 'rieng-tu' => 'Quyền riêng tư', 'gop' => 'Góp tư liệu' ) as $ma => $chu ) {
			$lien .= Ve::lien_ket( $ma, $chu );
		}
		$dang = self::dang_xem();
		$duoi = '';
		foreach ( array( 'trang-chu' => array( 'Trang chủ', 'home' ), 'tim' => array( 'Tìm', 'search' ), 'kham-pha' => array( 'Khám phá', 'compass' ), 'da-luu' => array( 'Đã lưu', 'save' ) ) as $ma => [ $chu, $hinh ] ) {
			$duoi .= Ve::lien_ket( $ma, Ve::icon( $hinh ) . '<span>' . $chu . '</span>', $ma === $dang ? 'active' : '', $ma === $dang ? ' aria-current="page"' : '' );
		}
		return '<footer class="site-footer"><p>' . Ve::brand() . '</p><p>Thư viện Văn học Dân gian Việt Nam<br>Mỗi câu ghi rõ lấy từ đâu, và đã được kiểm đến mức nào.</p>'
			. '<div class="footer-links">' . $lien . '</div><p>Phần dự án tự viết: CC BY 4.0. Nội dung nguồn khác theo giấy phép tương ứng.<br>Không quảng cáo, không theo dõi.</p></footer>'
			. '<nav class="bottom-nav" aria-label="Điều hướng nhanh">' . $duoi . '</nav>';
	}
}
