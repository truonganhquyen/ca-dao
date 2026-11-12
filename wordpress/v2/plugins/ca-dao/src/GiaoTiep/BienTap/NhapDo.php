<?php

namespace Phongdao\GiaoTiep\BienTap;

defined( 'ABSPATH' ) || exit;

/** Đầu vào chưa lưu: vé một lần theo người/phiên/bài/phần, không phải bản nội dung được duyệt. */
final class NhapDo {

	public static function phien(): string {
		return get_current_user_id() . '-' . substr( hash( 'sha256', wp_get_session_token() ), 0, 20 );
	}

	/** Chỉ giữ các trường của form phần; không mang nonce, action, thông tin đăng nhập hay lối chuyển trang. */
	public static function loc( array $vao ): array {
		$ra = array_intersect_key( $vao, array_flip( array( 'chu', 'dong', 'ai', 'duyet', 'ly_do', 'nguon', 'doi_chieu', 'muc', 'su_kien', 'nam', 'noi', 'phien_phan', 'attachment_id' ) ) );
		return $ra;
	}

	public static function giu( int $bai, string $phan, array $vao ): string {
		$ve = bin2hex( random_bytes( 16 ) );
		set_transient( self::khoa( $ve ), array( 'bai' => $bai, 'phan' => $phan, 'vao' => self::loc( $vao ) ), 30 * MINUTE_IN_SECONDS );
		return $ve;
	}

	public static function lay( string $ve, int $bai, string $phan ): ?array {
		if ( ! preg_match( '/^[a-f0-9]{32}$/', $ve ) ) {
			return null;
		}
		$khoa = self::khoa( $ve );
		$cu = get_transient( $khoa );
		if ( ! is_array( $cu ) || $bai !== $cu['bai'] || $phan !== $cu['phan'] ) {
			return null;
		}
		delete_transient( $khoa );
		return $cu['vao'];
	}

	private static function khoa( string $ve ): string {
		return 'phongdao_nhap_do_' . hash( 'sha256', self::phien() . ':' . $ve );
	}

	/** Chỉ để dựng lại form bị lỗi: tuyệt đối không ghi kết quả này vào phần bài. */
	public static function de_hien( string $phan, array $vao, ?array $cu ): array {
		$ra = $cu ?? array();
		if ( in_array( $phan, array( 'chu_thich', 'di_ban', 'doan' ), true ) ) {
			$ra['muc'] = array();
			foreach ( (array) ( $vao['muc'] ?? array() ) as $k => $m ) {
				if ( '__i__' === (string) $k || ! is_array( $m ) ) {
					continue;
				}
				$goc = (string) ( $m['goc'] ?? '' );
				$hang = '' === $goc ? array() : ( $cu['muc'][ (int) $goc ] ?? array() );
				foreach ( array( 'tu', 'loi_giai', 'noi', 'nguoi_ke', 'nam', 'tieu_de', 'tu_dong', 'den_dong' ) as $ten ) {
					if ( isset( $m[ $ten ] ) && is_scalar( $m[ $ten ] ) ) {
						$hang[ $ten ] = (string) $m[ $ten ];
					}
				}
				if ( isset( $m['dong'] ) && is_string( $m['dong'] ) ) {
					$hang['dong'] = explode( "\n", str_replace( "\r", '', $m['dong'] ) );
				}
				if ( isset( $m['nguon'] ) && is_array( $m['nguon'] ) ) {
					$hang['nguon'] = $m['nguon'];
				}
				$hang['_goc_form'] = $goc;
				$hang['xoa'] = ! empty( $m['xoa'] );
				$ra['muc'][] = $hang;
			}
			return $ra;
		}
		foreach ( array( 'chu', 'ly_do', 'su_kien', 'nam', 'noi', 'attachment_id' ) as $ten ) {
			if ( isset( $vao[ $ten ] ) && is_scalar( $vao[ $ten ] ) ) {
				$ra[ $ten ] = (string) $vao[ $ten ];
			}
		}
		if ( isset( $vao['dong'] ) && is_string( $vao['dong'] ) ) {
			$ra['dong'] = explode( "\n", str_replace( "\r", '', $vao['dong'] ) );
		}
		foreach ( array( 'nguon', 'doi_chieu' ) as $ten ) {
			if ( isset( $vao[ $ten ] ) && is_array( $vao[ $ten ] ) ) {
				$ra[ $ten ] = $vao[ $ten ];
			}
		}
		$ra['ai'] = ! empty( $vao['ai'] );
		return $ra;
	}
}
