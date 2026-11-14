<?php

namespace Phongdao\DichVu\ChiMucTim;

use Phongdao\DichVu\TomTat\TomTat;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\ChiMuc;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/** Tìm đã gửi và gợi ý gõ dở dùng chung chỉ mục; ba phạm vi tách biệt, phân trang toàn bộ (D-112). */
final class TimDoc {

	public static function loc( array $vao ): array {
		$loai = is_string( $vao['loai'] ?? '' ) ? ( $vao['loai'] ?? '' ) : '';
		return array(
			'khop' => in_array( $vao['khop'] ?? '', array( 'dung', 'khong_dau', 'go_do' ), true ) ? $vao['khop'] : 'dung',
			'loai' => isset( TheLoai::DS[ $loai ] ) ? $loai : '',
			'trang' => max( 1, min( 1000000, is_scalar( $vao['trang'] ?? 1 ) ? (int) ( $vao['trang'] ?? 1 ) : 1 ) ),
		);
	}

	private static function co_sql( array $p ): string {
		global $wpdb;
		[ $cot, $ds, $dau ] = $p;
		return '(' . implode( ' AND ', array_map( static fn( string $s ): string => $wpdb->prepare( "i.$cot LIKE BINARY %s", ( $dau ? '' : '%' ) . $wpdb->esc_like( $s ) . '%' ), $ds ) ) . ')';
	}

	private static function du_chu( string $q, bool $tron, bool $dau ): string {
		global $wpdb;
		$cot = $dau ? array( 'chinh', 'phu' ) : array( 'chinh_kd', 'phu_kd' );
		return implode( ' AND ', array_map( static fn( string $s ): string => $wpdb->prepare( "(i.{$cot[0]} LIKE BINARY %s OR i.{$cot[1]} LIKE BINARY %s)", '%' . $wpdb->esc_like( $s ) . '%', '%' . $wpdb->esc_like( $s ) . '%' ), ChiMuc::mau( $q, $tron ) ) );
	}

	public static function tim( string $q, array $vao = array(), bool $dang_go = false, int $so = 20 ): array {
		global $wpdb;
		$so = max( 1, $so );
		$loc = self::loc( $vao );
		$chu = ChiMuc::chu( $q );
		$kd = ChiMuc::khong_dau( $chu );
		$tron = ! $dang_go && 'go_do' !== $loc['khop'];
		$phep = ChiMuc::phep_so( $q, $tron );
		$ra = array( 'ds' => array(), 'tong' => 0, 'trang' => 1, 'nhom' => array( 'dung' => 0, 'khong_dau' => 0, 'go_do' => 0 ), 'loai' => array() );
		if ( ! $phep ) { return $ra; }
		TomTat::xong();
		$cot = implode( ', ', array_map( static fn( string $k, array $p ): string => self::co_sql( $p ) . " AS $k", array_keys( $phep ), $phep ) );
		$du_kd = self::du_chu( $kd, true, false );
		$du_dau = self::du_chu( $chu, true, true );
		$sql = 'SELECT i.post_id, LENGTH(i.chinh_kd) AS dai, COALESCE(t.uu_tien_doc, 0) AS uu_tien_doc, t.the_loai, '
			. "$cot, ($du_kd) AS tron_kd, ($du_dau) AS tron_dau FROM " . ChiMucTim::bang() . " i JOIN {$wpdb->posts} p ON p.ID = i.post_id LEFT JOIN " . TomTat::bang() . ' t ON t.post_id = i.post_id WHERE '
			. $wpdb->prepare( 'p.post_type = %s AND p.post_status = %s AND ', KhaiBao::TAC_PHAM, 'publish' ) . self::du_chu( $kd, false, false );
		$hang = $wpdb->get_results( $sql, ARRAY_A ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- mọi mẫu đã prepare
		$ung = array();
		$ngoac = (bool) preg_match( '/^["“].+["”]$/u', trim( $q ) );
		foreach ( $hang as $r ) {
			$nhom = ! $r['tron_kd'] ? 'go_do' : ( $chu !== $kd && ! $r['tron_dau'] ? 'khong_dau' : 'dung' );
			if ( $ngoac && ! $r['lc'] && ! $r['lp'] ) { continue; }
			if ( '' === $loc['loai'] || $loc['loai'] === $r['the_loai'] ) { ++$ra['nhom'][ $nhom ]; }
			if ( ! $dang_go && $nhom !== $loc['khop'] ) { continue; }
			$l = (string) $r['the_loai'];
			$ra['loai'][ $l ] = ( $ra['loai'][ $l ] ?? 0 ) + 1;
			if ( '' === $loc['loai'] || $loc['loai'] === $l ) { $ung[] = $r; }
		}
		$ra['tong'] = count( $ung );
		$ra['trang'] = min( $loc['trang'], max( 1, (int) ceil( $ra['tong'] / $so ) ) );
		$kq = ChiMuc::xep_co( $ung, $q, $ra['trang'] * $so, static function ( array $ids ) use ( $wpdb ): array {
			$ra = array();
			foreach ( $wpdb->get_results( 'SELECT post_id, chinh_kd, phu_kd FROM ' . ChiMucTim::bang() . ' WHERE post_id IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')', ARRAY_A ) ?: array() as $r ) {
				$ra[ (int) $r['post_id'] ] = $r;
			}
			return $ra;
		}, 100, $tron );
		$ra['ds'] = array_slice( $kq['ds'], ( $ra['trang'] - 1 ) * $so, $so );
		return $ra;
	}
}
