<?php

namespace Phongdao\MienDuLieu;

/**
 * Tìm từ khó trong lời (D-105, docs/32 mục 4.9). So khớp không phân biệt hoa thường; coi các kiểu bỏ dấu ("hoà" / "hòa",
 * "thuỷ" / "thủy") và bộ mã chữ (dựng sẵn / tổ hợp) như nhau: mỗi tiếng được chuẩn hoá thành chữ cái không dấu thanh + dấu
 * thanh. Mỗi từ chỉ đánh dấu lần đầu trong cả bài (kể cả câu tiêu đề của tục ngữ). Hàm thuần, không gọi WordPress.
 */
final class TuKho {

	/** Nguyên âm mang dấu thanh: chữ => [nguyên âm gốc, dấu thanh 1–5 (huyền, sắc, hỏi, ngã, nặng)]. */
	private const THANH = array(
		'a' => 'àáảãạ',
		'ă' => 'ằắẳẵặ',
		'â' => 'ầấẩẫậ',
		'e' => 'èéẻẽẹ',
		'ê' => 'ềếểễệ',
		'i' => 'ìíỉĩị',
		'o' => 'òóỏõọ',
		'ô' => 'ồốổỗộ',
		'ơ' => 'ờớởỡợ',
		'u' => 'ùúủũụ',
		'ư' => 'ừứửữự',
		'y' => 'ỳýỷỹỵ',
	);

	/** Dấu thanh tổ hợp (U+0300 huyền, U+0301 sắc, U+0309 hỏi, U+0303 ngã, U+0323 nặng). */
	private const TO_HOP = array( "\u{0300}" => 1, "\u{0301}" => 2, "\u{0309}" => 3, "\u{0303}" => 4, "\u{0323}" => 5, "\u{0340}" => 1, "\u{0341}" => 2 );

	/** @var array<string,array{0:string,1:int}>|null */
	private static ?array $bang = null;

	/** Bảng chữ có dấu thanh → [gốc, thanh]. */
	private static function bang(): array {
		if ( null === self::$bang ) {
			self::$bang = array();
			foreach ( self::THANH as $goc => $ds ) {
				foreach ( mb_str_split( $ds ) as $i => $c ) {
					self::$bang[ $c ] = array( $goc, $i + 1 );
				}
			}
		}
		return self::$bang;
	}

	/** Dựng sẵn (NFC) khi máy có intl; không có thì giữ nguyên, dấu thanh tổ hợp vẫn được nhận ở khoa(). */
	private static function nfc( string $s ): string {
		return class_exists( '\Normalizer' ) ? (string) ( \Normalizer::normalize( $s, \Normalizer::FORM_C ) ?: $s ) : $s;
	}

	/** Khoá so khớp của một tiếng: chữ thường, bỏ dấu thanh, ghi dấu thanh ở cuối ("Hoà", "hòa" → "hoa2"... cùng khoá). */
	public static function khoa( string $tieng ): string {
		$bang  = self::bang();
		$thanh = 0;
		$ra    = '';
		foreach ( mb_str_split( mb_strtolower( self::nfc( $tieng ) ) ) as $c ) {
			if ( isset( self::TO_HOP[ $c ] ) ) {
				$thanh = self::TO_HOP[ $c ];
			} elseif ( isset( $bang[ $c ] ) ) {
				$ra   .= $bang[ $c ][0];
				$thanh = $bang[ $c ][1];
			} else {
				$ra .= $c;
			}
		}
		return $ra . ( $thanh ? (string) $thanh : '' );
	}

	/**
	 * Tách chuỗi thành các tiếng (dãy chữ cái, kể cả dấu tổ hợp) kèm vị trí ký tự.
	 *
	 * @return list<array{0:int,1:int,2:string}> [vị trí đầu, vị trí sau cuối, khoá]
	 */
	public static function tieng( string $s ): array {
		$ra = array();
		if ( preg_match_all( '/[\p{L}\p{M}]+/u', $s, $m, PREG_OFFSET_CAPTURE ) ) {
			foreach ( $m[0] as [ $chu, $byte ] ) {
				$dau  = mb_strlen( substr( $s, 0, $byte ) );
				$ra[] = array( $dau, $dau + mb_strlen( $chu ), self::khoa( $chu ) );
			}
		}
		return $ra;
	}

	/**
	 * Lần đầu mỗi từ khó xuất hiện trong cả bài (các dòng theo thứ tự). Từ dài so trước; chỗ đã đánh dấu không đánh dấu
	 * lồng. Từ không có trong lời thì không có trong kết quả (xem thieu()).
	 *
	 * @param list<string> $dong
	 * @param list<string> $tu
	 * @return array<int,list<array{0:int,1:int}>> chỉ số dòng => các đoạn [đầu, sau cuối] (ký tự), xếp theo vị trí
	 */
	public static function tim( array $dong, array $tu ): array {
		$tu = array_values( array_unique( array_filter( array_map( 'trim', $tu ), 'strlen' ) ) );
		usort( $tu, static fn( string $a, string $b ): int => mb_strlen( $b ) <=> mb_strlen( $a ) );
		$tieng = array_map( array( self::class, 'tieng' ), array_values( $dong ) );
		$ra    = array();
		foreach ( $tu as $t ) {
			$can = array_column( self::tieng( $t ), 2 );
			$n   = count( $can );
			if ( ! $n ) {
				continue;
			}
			foreach ( $tieng as $i => $ds ) {
				$thay = false;
				for ( $j = 0; $j + $n <= count( $ds ); $j++ ) {
					if ( array_column( array_slice( $ds, $j, $n ), 2 ) !== $can ) {
						continue;
					}
					$doan = array( $ds[ $j ][0], $ds[ $j + $n - 1 ][1] );
					$chen = false;
					foreach ( $ra[ $i ] ?? array() as [ $a, $b ] ) {
						$chen = $chen || ( $doan[0] < $b && $a < $doan[1] );
					}
					if ( ! $chen ) {
						$ra[ $i ][] = $doan;
						$thay       = true;
						break;
					}
				}
				if ( $thay ) {
					break;
				}
			}
		}
		ksort( $ra );
		foreach ( $ra as &$ds ) {
			usort( $ds, static fn( array $a, array $b ): int => $a[0] <=> $b[0] );
		}
		unset( $ds );
		return $ra;
	}

	/**
	 * Từ khó không thấy trong lời (để nhắc "Không thấy từ này trong lời, kiểm lại chính tả"; vẫn cho lưu).
	 *
	 * @param list<string> $dong
	 * @param list<string> $tu
	 * @return list<string>
	 */
	public static function thieu( array $dong, array $tu ): array {
		$ra = array();
		foreach ( array_values( array_unique( array_filter( array_map( 'trim', $tu ), 'strlen' ) ) ) as $t ) {
			if ( ! self::tim( $dong, array( $t ) ) ) {
				$ra[] = $t;
			}
		}
		return $ra;
	}
}
