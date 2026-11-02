<?php

namespace Phongdao\MienDuLieu;

/**
 * Dấu vân tay lời và mức giống (chuyển nguyên MVP inc/trung.php; D-065, D-074): máy báo, người quyết.
 * Mỗi lời có hai dấu vân tay: (1) chuẩn hoá — NFC, chữ thường, bỏ dấu câu, gộp khoảng trắng, bỏ xuống dòng; (2) bỏ thêm
 * mọi dấu, đ → d (bắt "hoà / hòa", gõ thiếu dấu). Mức giống = tỉ lệ cặp âm tiết liền nhau chung nhau (Jaccard) trên
 * bản (2). Không so tiêu đề, không so dòng đầu.
 */
final class VanTay {

	/** So gần giống chỉ với lời từ 6 âm tiết (câu ngắn dễ giống tình cờ). */
	public const AM_TOI_THIEU = 6;

	/** Mức giống (thứ tự = độ mạnh), tên hiện cho người biên soạn. */
	public const MUC = array(
		'y_het'     => 'Trùng y hệt',
		'gan_y_het' => 'Gần như y hệt',
		'di_ban'    => 'Có thể là dị bản',
		'lien_quan' => 'Liên quan',
	);

	/** Bản (1): NFC, chữ thường, bỏ dấu câu, gộp khoảng trắng (xuống dòng thành khoảng trắng). */
	public static function chuan_loi( string $loi ): string {
		$s = mb_strtolower( BoDau::nfc( $loi ) );
		$s = (string) preg_replace( '/[^\p{L}\p{M}\p{N}\s]/u', ' ', $s );
		return trim( (string) preg_replace( '/\s+/u', ' ', $s ) );
	}

	/** @return array{vt1:string,vt2:string,so_am:int,so_dong:int,cap:int[],so_cap:int} */
	public static function cua( string $loi ): array {
		$c   = self::chuan_loi( $loi );
		$k   = BoDau::bo_dau( $c );
		$am  = '' === $k ? array() : explode( ' ', $k );
		$cap = array();
		for ( $i = 0, $n = count( $am ) - 1; $i < $n; $i++ ) {
			$cap[ crc32( $am[ $i ] . ' ' . $am[ $i + 1 ] ) ] = true;
		}
		return array(
			'vt1'     => md5( $c ),
			'vt2'     => md5( $k ),
			'so_am'   => count( $am ),
			'so_dong' => count( array_filter( explode( "\n", $loi ), static fn( string $d ): bool => '' !== trim( $d ) ) ),
			'cap'     => array_keys( $cap ),
			'so_cap'  => count( $cap ),
		);
	}

	/**
	 * Mức giống của lời $a so với lời $b ($chung = số cặp âm tiết chung; $b_di_ban: $b là một dị bản đã ghi).
	 *
	 * @return array{0:string,1:int,2:string} [mức, % giống, lý do] — mức: y_het | gan_y_het | di_ban | lien_quan | khac
	 */
	public static function xep( array $a, array $b, int $chung, bool $b_di_ban = false ): array {
		if ( $a['vt1'] === $b['vt1'] ) {
			return $b_di_ban ? array( 'di_ban', 100, 'trùng một dị bản đã ghi' ) : array( 'y_het', 100, '' );
		}
		if ( $a['vt2'] === $b['vt2'] ) {
			return $b_di_ban ? array( 'di_ban', 100, 'trùng một dị bản đã ghi (khác cách bỏ dấu)' ) : array( 'gan_y_het', 100, 'chỉ khác dấu câu, chữ hoa hoặc cách bỏ dấu' );
		}
		if ( $b_di_ban || $chung < 1 || $a['so_am'] < self::AM_TOI_THIEU || $b['so_am'] < self::AM_TOI_THIEU ) {
			return array( 'khac', 0, '' );
		}
		$j     = $chung / max( 1, $a['so_cap'] + $b['so_cap'] - $chung );
		$giong = (int) floor( $j * 100 + 1e-9 );
		$nho   = min( $a['so_cap'], $b['so_cap'] );
		$lon   = max( $a['so_cap'], $b['so_cap'] );
		if ( $chung / max( 1, $nho ) >= 0.9 && $nho / max( 1, $lon ) <= 0.6 ) {
			return array( 'lien_quan', $giong, 'câu ngắn nằm trong bài dài' );
		}
		if ( $j >= 0.9 ) {
			return array( 'gan_y_het', $giong, 'khác vài chữ' );
		}
		if ( min( $a['so_dong'], $b['so_dong'] ) <= 1 ) {
			return array( 'khac', $giong, '' ); // câu một dòng: chỉ xét y hệt, gần như y hệt
		}
		return $j >= 0.5 ? array( 'di_ban', $giong, '' ) : array( 'khac', $giong, '' );
	}

	/** Mức giống của hai lời (không cần chỉ mục). */
	public static function do_giong( string $a, string $b, bool $b_di_ban = false ): array {
		$va = self::cua( $a );
		$vb = self::cua( $b );
		return self::xep( $va, $vb, count( array_intersect( $va['cap'], $vb['cap'] ) ), $b_di_ban );
	}
}
