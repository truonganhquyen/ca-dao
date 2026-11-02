<?php

namespace Phongdao\MienDuLieu;

/**
 * Chuẩn hoá chữ cho chỉ mục tìm và so khớp. Chuyển nguyên thuật toán từ MVP
 * (inc/trich.php cadao_nfc, inc/trung.php cadao_bo_dau, inc/tim.php cadao_bo_dau_tim, cadao_chuan_hoa_co_dau).
 * Miền dữ liệu: không gọi hàm WordPress, kiểm bằng PHP thuần.
 */
final class BoDau {

	/** Kiểu đặt dấu cũ → mới ở âm tiết mở, không sau "q" (docs/25 mục 4.3). */
	private const KIEU_CU = array(
		'oà' => 'òa',
		'oá' => 'óa',
		'oả' => 'ỏa',
		'oã' => 'õa',
		'oạ' => 'ọa',
		'oè' => 'òe',
		'oé' => 'óe',
		'oẻ' => 'ỏe',
		'oẽ' => 'õe',
		'oẹ' => 'ọe',
		'uỳ' => 'ùy',
		'uý' => 'úy',
		'uỷ' => 'ủy',
		'uỹ' => 'ũy',
		'uỵ' => 'ụy',
	);

	/** Unicode dựng sẵn (NFC); giữ nguyên khi máy thiếu phần mở rộng intl. */
	public static function nfc( string $s ): string {
		if ( class_exists( 'Normalizer' ) ) {
			$n = \Normalizer::normalize( $s, \Normalizer::FORM_C );
			if ( is_string( $n ) ) {
				return $n;
			}
		}
		return $s;
	}

	/** Bỏ mọi dấu thanh và dấu phụ; đ → d. */
	public static function bo_dau( string $s ): string {
		if ( class_exists( 'Normalizer' ) ) {
			$d = \Normalizer::normalize( $s, \Normalizer::FORM_D );
			$s = is_string( $d ) ? $d : $s;
		}
		$s = (string) preg_replace( '/\p{Mn}/u', '', $s );
		return str_replace( array( 'đ', 'Đ' ), array( 'd', 'D' ), $s );
	}

	/** Chữ không dấu, chữ thường, đã cắt khoảng trắng hai đầu: so khớp khi người đọc gõ không dấu. */
	public static function cho_tim( string $s ): string {
		return trim( mb_strtolower( self::bo_dau( self::nfc( $s ) ) ) );
	}

	/** "hoà" → "hòa", "thuỷ" → "thủy"; "toàn", "quý", "loẹt" giữ nguyên. Chỉ dùng trong chỉ mục, không sửa lời. */
	public static function chuan_hoa_co_dau( string $s ): string {
		$s = mb_strtolower( self::nfc( $s ) );
		return (string) preg_replace_callback(
			'/(?<!q)(' . implode( '|', array_keys( self::KIEU_CU ) ) . ')(?=$|[^\p{L}])/u',
			static fn( array $m ): string => self::KIEU_CU[ $m[1] ],
			$s
		);
	}
}
