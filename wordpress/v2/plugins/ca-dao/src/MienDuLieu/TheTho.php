<?php

namespace Phongdao\MienDuLieu;

/**
 * Thể thơ máy nhận theo số âm tiết từng dòng (chuyển nguyên thuật toán MVP inc/the-tho.php): lục bát, song thất lục bát,
 * câu 3 / 4 / 5 chữ, câu một dòng, tự do. Đếm âm tiết thuần cơ học, không phải nhận định văn hoá; gán vào phân loại
 * hinh_thuc (docs/26 mục 4.2). Lối hát (hát ru, hò, lý…) do người gán, máy không đụng.
 */
final class TheTho {

	public const DS = array(
		'luc-bat'           => 'Lục bát',
		'song-that-luc-bat' => 'Song thất lục bát',
		'ba-chu'            => 'Câu ba chữ',
		'bon-chu'           => 'Câu bốn chữ',
		'nam-chu'           => 'Câu năm chữ',
		'mot-dong'          => 'Câu một dòng',
		'tu-do'             => 'Tự do',
	);

	/** Số âm tiết của một dòng (đếm theo khoảng trắng, bỏ dấu câu đứng riêng). */
	public static function so_am_tiet( string $dong ): int {
		$chu = trim( (string) preg_replace( '/[^\p{L}\p{M}\p{N}\s]/u', ' ', BoDau::nfc( $dong ) ) );
		return '' === $chu ? 0 : count( preg_split( '/\s+/u', $chu, -1, PREG_SPLIT_NO_EMPTY ) ?: array() );
	}

	/**
	 * @param string[] $dong các câu của lời (dòng trống bỏ qua)
	 * @return string khoá của DS
	 */
	public static function nhan( array $dong ): string {
		$so = array_values( array_filter( array_map( array( self::class, 'so_am_tiet' ), $dong ) ) );
		$n  = count( $so );
		if ( $n <= 1 ) {
			return 'mot-dong';
		}
		// Song thất lục bát: khổ 7 – 7 – 6 – 8 (từ 3/4 số khổ). Xét trước lục bát vì khổ có cặp 6 – 8.
		if ( $n >= 4 ) {
			$kho  = intdiv( $n, 4 );
			$dung = 0;
			for ( $i = 0; $i < $kho; $i++ ) {
				$dung += array( 7, 7, 6, 8 ) === array_slice( $so, 4 * $i, 4 ) ? 1 : 0;
			}
			if ( $dung * 4 >= $kho * 3 ) {
				return 'song-that-luc-bat';
			}
		}
		// Lục bát: các cặp dòng 6 – 8 (từ 3/4 số cặp)
		$cap  = intdiv( $n, 2 );
		$dung = 0;
		for ( $i = 0; $i < $cap; $i++ ) {
			$dung += ( 6 === $so[ 2 * $i ] && 8 === $so[ 2 * $i + 1 ] ) ? 1 : 0;
		}
		if ( $dung * 4 >= $cap * 3 ) {
			return 'luc-bat';
		}
		// Câu ngắn đều: từ 80% số dòng có cùng 3, 4 hoặc 5 âm tiết
		foreach ( array( 3 => 'ba-chu', 4 => 'bon-chu', 5 => 'nam-chu' ) as $k => $the ) {
			if ( count( array_filter( $so, static fn( int $x ): bool => $x === $k ) ) * 5 >= $n * 4 ) {
				return $the;
			}
		}
		return 'tu-do';
	}
}
