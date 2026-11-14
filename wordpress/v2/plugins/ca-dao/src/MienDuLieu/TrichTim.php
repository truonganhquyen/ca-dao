<?php

namespace Phongdao\MienDuLieu;

/** Đánh dấu trên chính chữ đã lưu; không viết lại lời, không đưa HTML của dữ liệu vào thẻ. */
final class TrichTim {

	public static function danh_dau( string $s, string $q, bool $rong = false, bool $go = false ): string {
		$tu = explode( ' ', ChiMuc::chu( $q ) );
		if ( $rong ) { $tu = array_map( array( ChiMuc::class, 'khong_dau' ), $tu ); }
		preg_match_all( '/[\p{L}\p{M}\p{N}]+/u', $s, $m, PREG_OFFSET_CAPTURE );
		$ra = ''; $sau = 0;
		$esc = static fn( string $x ): string => htmlspecialchars( $x, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
		foreach ( $m[0] as [ $chu, $dau ] ) {
			$c = ChiMuc::chu( $chu );
			if ( $rong ) { $c = ChiMuc::khong_dau( $c ); }
			$co = in_array( $c, $tu, true ) || ( $go && '' !== end( $tu ) && str_starts_with( $c, end( $tu ) ) );
			$ra .= $esc( substr( $s, $sau, $dau - $sau ) ) . ( $co ? '<mark>' . $esc( $chu ) . '</mark>' : $esc( $chu ) );
			$sau = $dau + strlen( $chu );
		}
		return $ra . $esc( substr( $s, $sau ) );
	}

	public static function trich( array $phan, string $ten, string $q, bool $rong = false, bool $go = false ): array {
		$ds = array( array( 'tên', $ten ) );
		foreach ( $phan['loi']['dong'] ?? array() as $dong ) { $ds[] = array( 'lời', $dong ); }
		foreach ( $phan['di_ban']['muc'] ?? array() as $m ) {
			foreach ( $m['dong'] ?? array() as $dong ) { $ds[] = array( 'dị bản', $dong ); }
		}
		foreach ( array( 'y_nghia', 'nghia_den', 'nghia_bong', 'dung_khi' ) as $p ) { $ds[] = array( 'giải nghĩa', (string) ( $phan[ $p ]['chu'] ?? '' ) ); }
		foreach ( $phan['chu_thich']['muc'] ?? array() as $m ) { $ds[] = array( 'chú thích', ( $m['tu'] ?? '' ) . ' ' . ( $m['loi_giai'] ?? '' ) ); }
		$tot = -1; $chon = array( 'lời', implode( ' ', array_slice( $phan['loi']['dong'] ?? array(), 0, 2 ) ) );
		$chuan = $rong ? ChiMuc::khong_dau( ChiMuc::chu( $q ) ) : ChiMuc::chu( $q );
		foreach ( $ds as $x ) {
			$html = self::danh_dau( (string) $x[1], $q, $rong, $go );
			$n = substr_count( $html, '<mark>' );
			if ( ! $n ) { continue; }
			$c = ChiMuc::chu( (string) $x[1] );
			if ( $rong ) { $c = ChiMuc::khong_dau( $c ); }
			$diem = $n + ( str_contains( " $c ", " $chuan" . ( $go ? '' : ' ' ) ) ? 100 : 0 ) + ( 'tên' === $x[0] ? 0 : 1 );
			if ( $diem > $tot ) { $tot = $diem; $chon = $x; }
		}
		$s = (string) $chon[1];
		// Giữ cửa sổ quanh chữ khớp, kể cả khi chữ ở cuối một chú thích dài.
		if ( mb_strlen( $s ) > 240 ) {
			preg_match( '/<mark>(.*?)<\/mark>/u', self::danh_dau( $s, $q, $rong, $go ), $m );
			$tu = html_entity_decode( $m[1] ?? '', ENT_QUOTES, 'UTF-8' );
			$dau = max( 0, (int) mb_stripos( $s, $tu ) - 70 );
			$s = ( $dau ? '… ' : '' ) . mb_substr( $s, $dau, 220 ) . ( mb_strlen( $s ) > $dau + 220 ? ' …' : '' );
		}
		return array( 'nhan' => 'Khớp trong ' . $chon[0], 'html' => self::danh_dau( $s, $q, $rong, $go ) );
	}

	public static function ca_cau( array $phan, string $ten, string $q ): bool {
		$chu = ChiMuc::chu( $q );
		if ( count( explode( ' ', $chu ) ) < 2 ) { return false; }
		foreach ( array_merge( array( $ten ), $phan['loi']['dong'] ?? array() ) as $dong ) {
			if ( $chu === ChiMuc::chu( $dong ) || ChiMuc::khong_dau( $chu ) === ChiMuc::khong_dau( ChiMuc::chu( $dong ) ) && $chu === ChiMuc::khong_dau( $chu ) ) { return true; }
		}
		return false;
	}
}
