<?php

namespace Phongdao\MienDuLieu;

/**
 * Lõi tìm (chuyển nguyên thuật toán MVP inc/tim.php, D-067; docs/26 mục 8): mỗi bài có phần chính (tên, lời, dị bản)
 * và phần phụ (ý nghĩa, nghĩa, chú thích), mỗi phần một bản có dấu đã chuẩn hoá và một bản không dấu. So trọn âm tiết,
 * chữ cuối khớp phần đầu âm tiết (đang gõ dở). Điểm: cả cụm liền > các chữ gần nhau > các chữ rời rạc ("rời").
 * Thêm ở V2: gợi ý khi gõ sai một chữ cái (L-09).
 */
final class ChiMuc {

	/** Số âm tiết chen giữa tối đa để các chữ tìm vẫn tính là gần nhau (L-08). */
	public const CACH_TOI_DA = 3;

	/** Chữ để đánh chỉ mục / so khớp: thường, NFC, bỏ dấu câu, gộp khoảng trắng, kiểu bỏ dấu cũ → mới ("hoà" → "hòa"). */
	public static function chu( string $s ): string {
		$s = mb_strtolower( BoDau::nfc( $s ) );
		$s = trim( (string) preg_replace( '/\s+/u', ' ', (string) preg_replace( '/[^\p{L}\p{M}\p{N}\s]/u', ' ', $s ) ) );
		return BoDau::chuan_hoa_co_dau( $s );
	}

	public static function khong_dau( string $chu ): string {
		return trim( mb_strtolower( BoDau::bo_dau( $chu ) ) );
	}

	/**
	 * Mẫu so khớp của truy vấn (đã qua chu() / khong_dau()): các chữ trọn âm tiết " a ", chữ cuối " b" (đang gõ dở).
	 *
	 * @return string[]
	 */
	public static function mau( string $chu ): array {
		$tu   = explode( ' ', $chu );
		$cuoi = array_pop( $tu );
		return array_merge( array_map( static fn( string $t ): string => " $t ", $tu ), array( " $cuoi" ) );
	}

	/**
	 * Mọi chữ tìm nằm gần nhau trong $chu: một đoạn liền có đủ các chữ, dài tối đa số chữ + CACH_TOI_DA âm tiết.
	 *
	 * @param string[] $tu chữ tìm, chữ cuối có thể gõ dở
	 */
	public static function gan_nhau( string $chu, array $tu ): bool {
		$tu = array_values( array_filter( $tu, static fn( string $t ): bool => '' !== $t ) );
		$n  = count( $tu );
		if ( ! $n ) {
			return false;
		}
		$thay = array();
		foreach ( explode( ' ', trim( $chu ) ) as $i => $a ) {
			foreach ( $tu as $j => $t ) {
				if ( $j === $n - 1 ? str_starts_with( $a, $t ) : $a === $t ) {
					$thay[ $j ] = $i;
				}
			}
			if ( count( $thay ) === $n && $i - min( $thay ) + 1 <= $n + self::CACH_TOI_DA ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Xếp các hàng ứng viên (đã chứa mọi chữ tìm không dấu ở phần chính hoặc phụ).
	 *
	 * @param array<int,array{post_id:int|string,chinh:string,chinh_kd:string,phu:string,phu_kd:string}> $hang
	 * @return array{ds:array<int,array{id:int,diem:int,roi:bool,lien:bool}>,tong:int} giống nhất trước; lien = cả cụm liền ở phần chính
	 */
	public static function xep( array $hang, string $q, int $so = 20 ): array {
		$co_dau = self::chu( $q );
		$kd     = self::khong_dau( $co_dau );
		if ( '' === $kd ) {
			return array( 'ds' => array(), 'tong' => 0 );
		}
		$co_dau_that = $co_dau !== $kd;
		$tu          = self::mau( $kd );
		$tu_cd       = self::mau( $co_dau );
		$tron        = array_map( 'trim', $tu );
		$cum         = " $kd";
		$cum_cd      = " $co_dau";
		$co_het      = static fn( string $chu, array $ds ): bool => array_reduce( $ds, static fn( bool $c, string $t ): bool => $c && str_contains( $chu, $t ), true );
		$diem        = array();
		$roi         = array();
		$lien        = array();
		foreach ( $hang as $r ) {
			$lien_c = str_contains( $r['chinh_kd'], $cum );
			$gan_c  = ! $lien_c && $co_het( $r['chinh_kd'], $tu ) && self::gan_nhau( $r['chinh_kd'], $tron );
			$lien_p = str_contains( $r['phu_kd'], $cum );
			$gan_p  = ! $lien_p && $co_het( $r['phu_kd'], $tu ) && self::gan_nhau( $r['phu_kd'], $tron );
			$d      = $lien_c ? 300 : ( $gan_c ? 150 : 0 );
			$d     += $lien_p ? 100 : ( $gan_p ? 50 : 0 );
			if ( $co_dau_that ) { // gõ có dấu: khớp đúng dấu được ưu tiên
				$d += str_contains( $r['chinh'], $cum_cd ) ? 200 : ( $gan_c && $co_het( $r['chinh'], $tu_cd ) ? 80 : 0 );
				$d += str_contains( $r['phu'], $cum_cd ) ? 60 : 0;
			}
			$d += str_starts_with( $r['chinh_kd'], $cum ) ? 40 : 0; // khớp từ đầu tên
			$id = (int) $r['post_id'];
			if ( $lien_c ) {
				$lien[ $id ] = true;
			}
			if ( $d <= 0 ) { // có đủ chữ nhưng rời rạc
				$d          = 10;
				$roi[ $id ] = true;
			}
			$diem[ $id ] = $d * 1000 - min( 999, intdiv( strlen( $r['chinh_kd'] ), 50 ) ); // bằng điểm: bài ngắn trước
		}
		arsort( $diem );
		$tong = count( $diem );
		$ds   = array();
		foreach ( array_slice( $diem, 0, $so, true ) as $id => $d ) {
			$ds[] = array( 'id' => (int) $id, 'diem' => $d, 'roi' => isset( $roi[ $id ] ), 'lien' => isset( $lien[ $id ] ) );
		}
		return array( 'ds' => $ds, 'tong' => $tong );
	}

	/**
	 * Âm tiết không dấu trong từ vựng của chỉ mục cách $tu đúng một chữ cái (thay, thêm, bớt), dùng nhiều trước.
	 *
	 * @param array<string,int> $tu_vung âm tiết không dấu => số lần gặp
	 * @return string[]
	 */
	public static function tu_gan( string $tu, array $tu_vung, int $so = 5 ): array {
		$ra = array();
		if ( '' === $tu ) {
			return $ra;
		}
		foreach ( $tu_vung as $am => $dem ) {
			$am = (string) $am;
			if ( $am !== $tu && abs( strlen( $am ) - strlen( $tu ) ) <= 1 && 1 === levenshtein( $am, $tu ) ) {
				$ra[ $am ] = $dem;
			}
		}
		arsort( $ra );
		return array_slice( array_keys( $ra ), 0, $so );
	}
}
