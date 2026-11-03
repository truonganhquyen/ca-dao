<?php

namespace Phongdao\HaTang;

defined( 'ABSPATH' ) || exit;

/**
 * Đường dẫn MVP đổi khi chuyển sang V2 → 301 về chỗ mới (docs/29 mục 3 quy tắc 5, D-089). Chỉ xét khi trang không tồn
 * tại (404), nên không đè đường dẫn nào đang chạy. Tên bài đổi (đuôi -2…) và bài đã gộp do WordPress chuyển hướng
 * theo _wp_old_slug; bài vào nhầm thể loại do KhaiBao::ve_duong_dan_chuan.
 */
final class ChuyenHuong implements Module {

	public function dang_ky(): void {
		add_action(
			'template_redirect',
			static function (): void {
				if ( ! is_404() ) {
					return;
				}
				$toi = self::dich( (string) wp_parse_url( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ) );
				if ( null !== $toi ) {
					wp_safe_redirect( $toi, 301 );
					exit;
				}
			},
			5
		);
	}

	/** Đích mới của một đường dẫn MVP; null = không phải đường dẫn cũ đã biết. */
	public static function dich( string $duong ): ?string {
		$duong = '/' . trim( $duong, '/' ) . '/';
		// Tình huống → tuyển tập cùng tên đường dẫn
		if ( preg_match( '~^/tinh-huong/([^/]+)/$~', $duong, $m ) ) {
			$tt = get_page_by_path( sanitize_title( $m[1] ), OBJECT, KhaiBao::TUYEN_TAP );
			return $tt && 'publish' === $tt->post_status ? (string) get_permalink( $tt ) : null;
		}
		// Thể thơ → hình thức
		if ( preg_match( '~^/the-tho/([^/]+)/$~', $duong, $m ) ) {
			$link = get_term_link( sanitize_title( $m[1] ), 'hinh_thuc' );
			return is_wp_error( $link ) ? null : $link;
		}
		// Thể loại "Thành ngữ" cũ đã gộp vào Tục ngữ – Thành ngữ (D-079)
		if ( preg_match( '~^/the-loai/thanh-ngu/~', $duong ) ) {
			$link = get_term_link( 'tuc-ngu', KhaiBao::THE_LOAI );
			return is_wp_error( $link ) ? null : $link;
		}
		// Bài dưới tiền tố cũ: /thanh-ngu/<tên>/, /bai/<tên>/ (đường dẫn gốc của loại bài MVP)
		if ( preg_match( '~^/(?:thanh-ngu|bai)/([^/]+)/$~', $duong, $m ) ) {
			$bai = get_page_by_path( sanitize_title( $m[1] ), OBJECT, KhaiBao::TAC_PHAM );
			return $bai && 'publish' === $bai->post_status ? (string) get_permalink( $bai ) : null;
		}
		return null;
	}
}
