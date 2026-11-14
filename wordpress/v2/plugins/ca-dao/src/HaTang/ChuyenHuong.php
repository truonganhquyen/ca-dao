<?php

namespace Phongdao\HaTang;

defined( 'ABSPATH' ) || exit;

/**
 * Đường dẫn MVP đổi khi chuyển sang V2 → 301 về chỗ mới (docs/29 mục 3 quy tắc 5, D-089). Chỉ xét khi trang không tồn
 * tại (404), nên không đè đường dẫn nào đang chạy. Tên bài đổi (đuôi -2…) và bài đã gộp do WordPress chuyển hướng
 * theo _wp_old_slug; bài vào nhầm thể loại do KhaiBao::ve_duong_dan_chuan.
 */
final class ChuyenHuong implements Module {

	/** Tuỳ chọn (không tự nạp): đường cũ của mục phân loại đã gộp / bỏ (D-114) => {tax, id} của mục nhận, hoặc {duong}. */
	public const PHAN_LOAI = 'phongdao_chuyen_phan_loai';

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

	/**
	 * Ghi chuyển hướng cho đường cũ của một mục phân loại vừa gộp ($vao > 0: tới mục nhận) hoặc bỏ ($vao = 0: tới trang danh
	 * mục của bộ, vd. /chu-de/). Các đường cũ đang trỏ về mục vừa xoá cũng đổi sang đích mới, để không có chuyển hướng chết.
	 */
	public static function ghi_phan_loai( string $cu, string $tax, int $da_xoa, int $vao ): void {
		$cu   = '/' . trim( $cu, '/' ) . '/';
		$dich = $vao ? array( 'tax' => $tax, 'id' => $vao ) : array( 'duong' => '/' . str_replace( '_', '-', $tax ) . '/' );
		$pl   = get_option( self::PHAN_LOAI, array() );
		$pl   = is_array( $pl ) ? $pl : array();
		foreach ( $pl as $k => $d ) {
			if ( is_array( $d ) && (int) ( $d['id'] ?? 0 ) === $da_xoa && $tax === ( $d['tax'] ?? '' ) ) {
				$pl[ $k ] = $dich;
			}
		}
		if ( '//' !== $cu ) {
			$pl[ $cu ] = $dich;
		}
		update_option( self::PHAN_LOAI, $pl, false );
	}

	/** Đích mới của một đường dẫn MVP; null = không phải đường dẫn cũ đã biết. */
	public static function dich( string $duong ): ?string {
		$duong = '/' . trim( $duong, '/' ) . '/';
		$da_doi = \Phongdao\DichVu\LuuPhan\DuongDan::chuyen_cu( $duong );
		if ( null !== $da_doi ) { return $da_doi; }
		// Mục phân loại đã gộp hoặc bỏ ở màn Dọn chủ đề (D-114)
		$pl = get_option( self::PHAN_LOAI, array() );
		if ( is_array( $pl ) && isset( $pl[ $duong ] ) && is_array( $pl[ $duong ] ) ) {
			$d = $pl[ $duong ];
			if ( ! empty( $d['id'] ) ) {
				$link = get_term_link( (int) $d['id'], (string) ( $d['tax'] ?? '' ) );
				if ( ! is_wp_error( $link ) ) {
					return $link;
				}
			}
			return home_url( (string) ( $d['duong'] ?? '/' ) );
		}
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
