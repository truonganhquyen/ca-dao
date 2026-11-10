<?php

namespace Phongdao\DichVu\XepViec;

use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Số liệu thẻ Hộp việc cho Bàn làm việc (T-180): vào / ra theo tuần, tuổi thẻ đang mở, thẻ chờ lâu nhất, nội dung người
 * đọc gửi theo loại. Chỉ đếm thẻ cần người quyết (các loại trong QuyetViec::LOAI), không đếm dòng nhật ký hay lô.
 * Mọi mốc giờ là UTC như cột tao_luc / quyet_luc.
 */
final class SoViec {

	private const MO = array( Viec::CHO, Viec::DE_SAU );

	private static function trong( array $ds ): string {
		global $wpdb;
		return implode( ',', array_map( static fn( string $v ): string => $wpdb->prepare( '%s', $v ), $ds ) );
	}

	private static function luc( int $giay ): string {
		return gmdate( 'Y-m-d H:i:s', $giay );
	}

	/**
	 * Thẻ tạo (vào) và thẻ đã quyết xong hoặc bỏ (ra) trong 7 ngày qua và 7 ngày trước đó, theo loại.
	 *
	 * @return array<string,array{vao:int,ra:int,vao_truoc:int,ra_truoc:int}> mọi loại thẻ, kể cả loại 0
	 */
	public static function vao_ra( int $bay_gio ): array {
		global $wpdb;
		$loai = self::trong( array_keys( QuyetViec::LOAI ) );
		$xong = self::trong( array( Viec::XONG, Viec::BO ) );
		$moc  = array( self::luc( $bay_gio - 14 * DAY_IN_SECONDS ), self::luc( $bay_gio - 7 * DAY_IN_SECONDS ), self::luc( $bay_gio + 1 ) );
		$ra   = array_fill_keys( array_keys( QuyetViec::LOAI ), array( 'vao' => 0, 'ra' => 0, 'vao_truoc' => 0, 'ra_truoc' => 0 ) );
		foreach ( array( array( 'tao_luc', '', 'vao' ), array( 'quyet_luc', " AND trang_thai IN ($xong)", 'ra' ) ) as [ $cot, $them, $ten ] ) {
			foreach ( array( array( 1, 2, $ten ), array( 0, 1, $ten . '_truoc' ) ) as [ $a, $b, $khoa ] ) {
				$hang = $wpdb->get_results( $wpdb->prepare( 'SELECT loai, COUNT(*) AS so FROM ' . Viec::bang() . " WHERE loai IN ($loai)$them AND $cot >= %s AND $cot < %s GROUP BY loai", $moc[ $a ], $moc[ $b ] ), ARRAY_A ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL -- danh sách đã prepare
				foreach ( $hang as $h ) {
					$ra[ $h['loai'] ][ $khoa ] = (int) $h['so'];
				}
			}
		}
		return $ra;
	}

	/** @return array{tong:int,duoi_1:int,tu_1_7:int,tu_7_30:int,tren_30:int} thẻ đang mở (Chờ, Để sau) theo tuổi */
	public static function tuoi( int $bay_gio ): array {
		global $wpdb;
		$dk  = 'FROM ' . Viec::bang() . ' WHERE loai IN (' . self::trong( array_keys( QuyetViec::LOAI ) ) . ') AND trang_thai IN (' . self::trong( self::MO ) . ')';
		$tu  = static fn( int $ngay ): int => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) $dk AND tao_luc >= %s", self::luc( $bay_gio - $ngay * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL -- danh sách đã prepare
		$tong = (int) $wpdb->get_var( "SELECT COUNT(*) $dk" ); // phpcs:ignore WordPress.DB.PreparedSQL -- danh sách đã prepare
		[ $n1, $n7, $n30 ] = array( $tu( 1 ), $tu( 7 ), $tu( 30 ) );
		return array( 'tong' => $tong, 'duoi_1' => $n1, 'tu_1_7' => $n7 - $n1, 'tu_7_30' => $n30 - $n7, 'tren_30' => $tong - $n30 );
	}

	/** Thẻ đang mở lâu nhất. @return array<int,array<string,mixed>> */
	public static function lau_nhat( int $so = 5 ): array {
		global $wpdb;
		$ds = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Viec::bang() . ' WHERE loai IN (' . self::trong( array_keys( QuyetViec::LOAI ) ) . ') AND trang_thai IN (' . self::trong( self::MO ) . ') ORDER BY tao_luc ASC, id ASC LIMIT %d', $so ), ARRAY_A ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL -- danh sách đã prepare
		return array_map( array( Viec::class, 'doc' ), $ds );
	}

	/** Thẻ "Người đọc gửi" đang chờ theo loại gửi (bao_loi, vi_pham, di_ban, cam_nghi…). @return array<string,int> */
	public static function nguoi_doc_cho(): array {
		global $wpdb;
		$ra = array();
		foreach ( $wpdb->get_col( $wpdb->prepare( 'SELECT noi_dung FROM ' . Viec::bang() . ' WHERE loai = %s AND trang_thai = %s', 'nguoi_doc', Viec::CHO ) ) ?: array() as $nd ) {
			$loai        = (string) ( json_decode( (string) $nd, true )['loai'] ?? '' );
			$ra[ $loai ] = ( $ra[ $loai ] ?? 0 ) + 1;
		}
		return $ra;
	}
}
