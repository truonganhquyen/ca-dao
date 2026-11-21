<?php

namespace Phongdao\MienDuLieu;

/** Chỉ chuẩn hoá bản tìm; không thay lời lưu. Điểm là độ giống đoạn, không phải xác suất trùng. */
final class SoKhopLoi {

	public static function chu( string $s, bool $bo_dau = false ): string {
		$s = strtr( BoDau::nfc( $s ), array( 'Ð' => 'Đ', 'ð' => 'đ', "\u{FEFF}" => '', "\u{00AD}" => '', "\u{200B}" => '', "\u{200C}" => '', "\u{200D}" => '', "\u{2060}" => '' ) );
		$s = BoDau::chuan_hoa_co_dau( $s );
		if ( $bo_dau ) { $s = BoDau::bo_dau( $s ); }
		$s = (string) preg_replace( '/[^\p{L}\p{M}\p{N}]+/u', ' ', $s );
		return trim( (string) preg_replace( '/\s+/u', ' ', $s ) );
	}

	public static function tu( string $s ): array {
		$s = self::chu( $s, true );
		return '' === $s ? array() : explode( ' ', $s );
	}

	/** Các nhánh gom trong một multi-search; không làm mờ truy vấn 1–3 tiếng. */
	public static function truy_van( string $q ): array {
		$chu = self::chu( $q ); $kd = self::chu( $q, true ); $tu = self::tu( $q );
		if ( ! $tu ) { return array(); }
		$ra = array( array( 'q' => $chu, 'attributesToSearchOn' => array( 'loi' ) ), array( 'q' => $kd, 'attributesToSearchOn' => array( 'loi_kd' ) ) );
		if ( count( $tu ) >= 4 && count( $tu ) <= 16 ) {
			foreach ( $tu as $i => $t ) {
				$thu = $tu; unset( $thu[ $i ] );
				$ra[] = array( 'q' => implode( ' ', $thu ), 'attributesToSearchOn' => array( 'loi_kd' ), 'matchingStrategy' => 'all' );
			}
		}
		return $ra;
	}

	/** Bắt đầu tự do trong bài, nhưng phải tiêu thụ hết câu tìm; tránh khớp một mẩu nhỏ của truy vấn. */
	private static function gia_doan( array $a, array $b ): float {
		$truoc = array_fill( 0, count( $b ) + 1, 0 );
		foreach ( $a as $i => $x ) {
			$dong = array( $i + 1 );
			foreach ( $b as $j => $y ) { $dong[] = min( $truoc[ $j + 1 ] + 1, $dong[ $j ] + 1, $truoc[ $j ] + (int) ( $x !== $y ) ); }
			$truoc = $dong;
		}
		return min( $truoc ) / max( 1, count( $a ) );
	}

	public static function so( string $q, string $loi ): ?array {
		$chinh = self::chu( $q ); $dich = self::chu( $loi );
		$a = self::tu( $q ); $b = self::tu( $loi );
		if ( ! $a || ! $b ) { return null; }
		if ( $chinh === $dich ) { return array( 'khop' => true, 'diem' => 100, 'cap_do' => 'y_het', 'ly_do' => 'Trùng toàn bộ lời sau chuẩn hoá chữ và trình bày.' ); }
		if ( $a === $b ) { return array( 'khop' => false, 'diem' => 99, 'cap_do' => 'gan_y_het', 'ly_do' => 'Trùng toàn bộ khi bỏ dấu; cần đối chiếu dấu chữ.' ); }
		// Giới hạn chi phí; bài dài so cặp tiếng, không cắt lời rồi kết luận trùng toàn văn.
		if ( count( $a ) > 64 || count( $b ) > 4096 ) {
			$cap = static function ( array $tu ): array { $ra = array(); for ( $i = 1; $i < count( $tu ); ++$i ) { $ra[ $tu[ $i - 1 ] . ' ' . $tu[ $i ] ] = true; } return $ra; };
			$ca = $cap( $a ); $cb = $cap( $b ); $chung = count( array_intersect_key( $ca, $cb ) );
			$j = $chung / max( 1, count( $ca + $cb ) );
			if ( $j < .5 ) { return null; }
			return array( 'khop' => false, 'diem' => min( 99, (int) floor( 100 * $j ) ), 'cap_do' => 'di_ban', 'ly_do' => 'Nhiều cặp tiếng giống nhau; cần đối chiếu toàn bài dài.' );
		}
		$gia = self::gia_doan( $a, $b );
		if ( $gia > ( count( $a ) < 4 ? 0 : .25 ) ) { return null; }
		$doan = count( $a ) < count( $b ) * .75;
		return array( 'khop' => false, 'diem' => min( 99, (int) floor( 100 * ( 1 - $gia ) ) ), 'cap_do' => $doan ? 'lien_quan' : 'di_ban', 'ly_do' => $doan ? 'Câu nhập khớp một đoạn của bài; chưa phải trùng toàn văn.' : 'Lời gần giống; cần đối chiếu các chữ khác nhau.' );
	}
}
