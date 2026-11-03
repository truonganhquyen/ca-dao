<?php

namespace Phongdao\MienDuLieu;

/**
 * Đo lường không cookie (chuyển từ MVP luot-doc.php, do-luong.php; D-047, D-064, D-069): lượt đọc và bốn sự kiện, cộng
 * số theo ngày, không lưu địa chỉ mạng, không định danh. PHP thuần: kiểm và làm sạch dữ liệu gửi lên.
 */
final class DoLuong {

	/** Sự kiện => [khoá được phép (null = chữ tự do đã kiểm)]. */
	public const SU_KIEN = array(
		'tim_khong_ket_qua'  => null,
		'trich_dan_sao_chep' => array( 'thong_dung', 'apa', 'chi_loi' ),
		'chu_giai_mo'        => null,
		'chia_se'            => array( 'web_share', 'sao_chep_lien_ket', 'tai_anh' ),
	);

	/** Máy dò, trình xem trước liên kết: không tính. */
	public static function la_may_do( string $ua ): bool {
		return '' === $ua || 1 === preg_match( '/bot\b|bot\/|crawl|spider|slurp|headless|lighthouse|preview|facebookexternalhit|zalo.*(link|preview)/i', $ua );
	}

	/** Chuỗi tìm để ghi: chữ thường, gọn khoảng trắng, ≤ 80 ký tự; '' khi quá ngắn hoặc trông như email, số điện thoại. */
	public static function chuoi_tim( string $q ): string {
		$q = mb_substr( trim( (string) preg_replace( '/\s+/u', ' ', mb_strtolower( strip_tags( $q ) ) ) ), 0, 80 );
		if ( mb_strlen( $q ) < 2 || str_contains( $q, '@' ) || preg_match( '/\d{6,}/', (string) preg_replace( '/[\s.\-]/', '', $q ) ) ) {
			return '';
		}
		return $q;
	}

	/** Tên miền trang giới thiệu hợp lệ, không phải chính site; bỏ www., m.; còn lại ''. */
	public static function ten_mien( string $tu, string $site ): string {
		$tu = strtolower( substr( trim( $tu ), 0, 190 ) );
		if ( ! preg_match( '/^[a-z0-9.-]+\.[a-z]{2,}$/', $tu ) || strtolower( $site ) === $tu ) {
			return '';
		}
		return (string) preg_replace( '/^(www|m)\./', '', $tu );
	}

	/**
	 * Khoá ghi của một sự kiện: "bai:kieu", chuỗi tìm, hoặc chữ được chạm; '' = không ghi.
	 *
	 * @param array{bai?:int|string,kieu?:string,q?:string,muc?:string} $du
	 */
	public static function khoa( string $loai, array $du ): string {
		if ( ! array_key_exists( $loai, self::SU_KIEN ) ) {
			return '';
		}
		if ( 'tim_khong_ket_qua' === $loai ) {
			return self::chuoi_tim( (string) ( $du['q'] ?? '' ) );
		}
		if ( 'chu_giai_mo' === $loai ) {
			$m = mb_substr( trim( (string) preg_replace( '/\s+/u', ' ', strip_tags( (string) ( $du['muc'] ?? '' ) ) ) ), 0, 60 );
			return mb_strlen( $m ) >= 1 && ! str_contains( $m, '@' ) ? mb_strtolower( $m ) : '';
		}
		$bai  = (int) ( $du['bai'] ?? 0 );
		$kieu = (string) ( $du['kieu'] ?? '' );
		return $bai > 0 && in_array( $kieu, self::SU_KIEN[ $loai ], true ) ? $bai . ':' . $kieu : '';
	}
}
