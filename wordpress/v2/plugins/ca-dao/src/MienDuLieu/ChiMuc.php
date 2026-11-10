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
	 * Dòng chỉ mục của một bài (ChiMucTim::ghi): phần chính, phần phụ, mỗi phần bản có dấu và không dấu, khoảng trắng
	 * hai đầu để so trọn âm tiết (" ca " không khớp "cao"). Phần chính không có chữ nào thì không đánh chỉ mục.
	 *
	 * @return array{chinh:string,chinh_kd:string,phu:string,phu_kd:string}|null
	 */
	public static function dong_chi_muc( string $chinh, string $phu ): ?array {
		$chinh = self::chu( $chinh );
		$kd    = self::khong_dau( $chinh );
		if ( '' === $kd ) {
			return null;
		}
		$phu = self::chu( $phu );
		return array( 'chinh' => " $chinh ", 'chinh_kd' => " $kd ", 'phu' => " $phu ", 'phu_kd' => ' ' . self::khong_dau( $phu ) . ' ' );
	}

	/**
	 * Các phép so chuỗi con mà điểm của xep() cần, để CSDL tính sẵn cho mọi ứng viên (ChiMucTim::tim): cờ => [cột,
	 * chuỗi, đầu]. Cờ đúng khi cột chứa MỌI chuỗi (đầu = true: cột bắt đầu bằng chuỗi). lc/lp: cả cụm liền ở phần
	 * chính/phụ; hc/hp: đủ các chữ; dau: khớp từ đầu tên; gõ có dấu thêm dc/dp (cả cụm đúng dấu), hd (đủ chữ đúng dấu).
	 *
	 * @return array<string,array{0:string,1:string[],2:bool}> rỗng khi truy vấn không có chữ
	 */
	public static function phep_so( string $q ): array {
		$co_dau = self::chu( $q );
		$kd     = self::khong_dau( $co_dau );
		if ( '' === $kd ) {
			return array();
		}
		$tu = self::mau( $kd );
		$ra = array(
			'lc'  => array( 'chinh_kd', array( " $kd" ), false ),
			'lp'  => array( 'phu_kd', array( " $kd" ), false ),
			'hc'  => array( 'chinh_kd', $tu, false ),
			'hp'  => array( 'phu_kd', $tu, false ),
			'dau' => array( 'chinh_kd', array( " $kd" ), true ),
		);
		if ( $co_dau !== $kd ) { // gõ có dấu: khớp đúng dấu được ưu tiên
			$ra['dc'] = array( 'chinh', array( " $co_dau" ), false );
			$ra['dp'] = array( 'phu', array( " $co_dau" ), false );
			$ra['hd'] = array( 'chinh', self::mau( $co_dau ), false );
		}
		return $ra;
	}

	/**
	 * Cờ của một dòng chỉ mục tính bằng PHP (cùng nghĩa với SQL ChiMucTim::tim tính).
	 *
	 * @param array<string,string> $r dòng chỉ mục
	 * @return array<string,bool>
	 */
	public static function co( array $r, array $phep ): array {
		$ra = array();
		foreach ( $phep as $ten => [ $cot, $ds, $dau ] ) {
			$ra[ $ten ] = array_reduce( $ds, static fn( bool $c, string $s ): bool => $c && ( $dau ? str_starts_with( $r[ $cot ], $s ) : str_contains( $r[ $cot ], $s ) ), true );
		}
		return $ra;
	}

	/** @return array{0:bool,1:bool} còn phải đọc chữ phần chính / phần phụ để biết các chữ có gần nhau không */
	public static function can_gan( array $co ): array {
		return array( ! $co['lc'] && $co['hc'], ! $co['lp'] && $co['hp'] );
	}

	/**
	 * Điểm của một bài từ cờ: cả cụm liền > các chữ gần nhau > rời rạc; đúng dấu, khớp từ đầu tên được cộng; bằng điểm
	 * thì bài ngắn trước. $gan_c/$gan_p chỉ có nghĩa khi can_gan() cho phần đó là đúng.
	 *
	 * @return array{0:int,1:bool} [điểm, rời rạc]
	 */
	public static function diem( array $co, bool $gan_c, bool $gan_p, int $dai ): array {
		[ $can_c, $can_p ] = self::can_gan( $co );
		$gan_c             = $gan_c && $can_c;
		$d                 = $co['lc'] ? 300 : ( $gan_c ? 150 : 0 );
		$d                += $co['lp'] ? 100 : ( $gan_p && $can_p ? 50 : 0 );
		if ( isset( $co['dc'] ) ) {
			$d += $co['dc'] ? 200 : ( $gan_c && $co['hd'] ? 80 : 0 );
			$d += $co['dp'] ? 60 : 0;
		}
		$d  += $co['dau'] ? 40 : 0;
		$roi = $d <= 0; // có đủ chữ nhưng rời rạc
		return array( ( $roi ? 10 : $d ) * 1000 - min( 999, intdiv( $dai, 50 ) ), $roi );
	}

	/**
	 * Xếp các hàng ứng viên (đã chứa mọi chữ tìm không dấu ở phần chính hoặc phụ).
	 *
	 * @param array<int,array{post_id:int|string,chinh:string,chinh_kd:string,phu:string,phu_kd:string}> $hang
	 * @return array{ds:array<int,array{id:int,diem:int,roi:bool,lien:bool}>,tong:int} giống nhất trước (bằng điểm: thứ tự
	 *         của $hang); lien = cả cụm liền ở phần chính
	 */
	public static function xep( array $hang, string $q, int $so = 20 ): array {
		$phep = self::phep_so( $q );
		if ( ! $phep ) {
			return array( 'ds' => array(), 'tong' => 0 );
		}
		$tron = array_map( 'trim', $phep['hc'][1] );
		$diem = array();
		$roi  = array();
		$lien = array();
		foreach ( $hang as $r ) {
			$co                = self::co( $r, $phep );
			[ $can_c, $can_p ] = self::can_gan( $co );
			[ $d, $rr ]        = self::diem( $co, $can_c && self::gan_nhau( $r['chinh_kd'], $tron ), $can_p && self::gan_nhau( $r['phu_kd'], $tron ), strlen( $r['chinh_kd'] ) );
			$id                = (int) $r['post_id'];
			$diem[ $id ]       = $d;
			$roi[ $id ]        = $rr;
			$lien[ $id ]       = $co['lc'];
		}
		arsort( $diem ); // PHP ≥ 8: sắp ổn định, bằng điểm giữ thứ tự hàng
		$ds = array();
		foreach ( array_slice( $diem, 0, max( 0, $so ), true ) as $id => $d ) {
			$ds[] = array( 'id' => (int) $id, 'diem' => $d, 'roi' => $roi[ $id ], 'lien' => $lien[ $id ] );
		}
		return array( 'ds' => $ds, 'tong' => count( $diem ) );
	}

	/**
	 * Như xep() nhưng từ cờ CSDL tính sẵn cho MỌI ứng viên (không lấy chữ từng bài): duyệt theo điểm cận trên, chỉ đọc
	 * chữ (qua $doc, từng lô) của bài còn phải xét chữ gần nhau mà còn có thể lọt vào $so bài đầu; dừng khi không bài
	 * nào còn lại vượt được bài thứ $so. Cùng tập ứng viên (thứ tự id tăng) thì ra đúng kết quả của xep().
	 *
	 * @param array<int,array<string,int|string>> $hang post_id, dai (độ dài chinh_kd) và một cột 0/1 cho mỗi cờ của phep_so()
	 * @param callable(int[]):array<int,array{chinh_kd:string,phu_kd:string}> $doc chữ của các bài theo id
	 * @return array{ds:array<int,array{id:int,diem:int,roi:bool,lien:bool}>,tong:int,doc:int} doc = số bài đã đọc chữ
	 */
	public static function xep_co( array $hang, string $q, int $so, callable $doc, int $lo = 100 ): array {
		$phep = self::phep_so( $q );
		if ( ! $phep ) {
			return array( 'ds' => array(), 'tong' => 0, 'doc' => 0 );
		}
		$tron = array_map( 'trim', $phep['hc'][1] );
		// Cận trên của mỗi bài (coi như các chữ gần nhau); cờ dùng thẳng giá trị 0/1 của CSDL ("0" là sai)
		$tren = array();
		$theo = array();
		foreach ( $hang as $r ) {
			$id          = (int) $r['post_id'];
			$theo[ $id ] = $r;
			$tren[ $id ] = self::diem( $r, true, true, (int) $r['dai'] )[0];
		}
		ksort( $tren );
		arsort( $tren ); // ổn định: bằng điểm thì id nhỏ trước, như xep()
		$dinh = array(); // $so bài tốt nhất đã biết đúng điểm, tốt trước
		$them = static function ( int $id, int $d, bool $roi, bool $lien ) use ( &$dinh, $so ): void {
			$dinh[] = array( 'id' => $id, 'diem' => $d, 'roi' => $roi, 'lien' => $lien );
			usort( $dinh, static fn( array $a, array $b ): int => $b['diem'] <=> $a['diem'] ?: $a['id'] <=> $b['id'] );
			if ( count( $dinh ) > $so ) {
				array_pop( $dinh );
			}
		};
		$cho  = array();
		$da   = 0;
		$giai = static function () use ( &$cho, &$da, $doc, $tron, $theo, $them ): void {
			if ( ! $cho ) {
				return;
			}
			$chu = $doc( $cho );
			$da += count( $cho );
			foreach ( $cho as $id ) {
				$c                 = $chu[ $id ] ?? array( 'chinh_kd' => '', 'phu_kd' => '' );
				[ $can_c, $can_p ] = self::can_gan( $theo[ $id ] );
				[ $d, $roi ]       = self::diem( $theo[ $id ], $can_c && self::gan_nhau( $c['chinh_kd'], $tron ), $can_p && self::gan_nhau( $c['phu_kd'], $tron ), (int) $theo[ $id ]['dai'] );
				$them( $id, $d, $roi, (bool) $theo[ $id ]['lc'] );
			}
			$cho = array();
		};
		foreach ( $tren as $id => $d ) {
			// Cận trên không vượt được bài thứ $so (bài sau còn thấp hơn, bài thứ $so chỉ tốt lên): dừng
			$cuoi = $so > 0 && count( $dinh ) >= $so ? end( $dinh ) : null;
			if ( $so <= 0 || ( $cuoi && ( $d < $cuoi['diem'] || ( $d === $cuoi['diem'] && $id > $cuoi['id'] ) ) ) ) {
				break;
			}
			[ $can_c, $can_p ] = self::can_gan( $theo[ $id ] );
			if ( ! $can_c && ! $can_p ) { // cờ đủ để biết đúng điểm
				$them( $id, $d, self::diem( $theo[ $id ], false, false, (int) $theo[ $id ]['dai'] )[1], (bool) $theo[ $id ]['lc'] );
				continue;
			}
			$cho[] = $id;
			if ( count( $cho ) >= $lo ) {
				$giai();
			}
		}
		$giai();
		return array( 'ds' => $dinh, 'tong' => count( $hang ), 'doc' => $da );
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
