<?php

namespace Phongdao\KhoLuu;

defined( 'ABSPATH' ) || exit;

/**
 * Bảng `wp_cadao_viec` — bảng mới duy nhất của V2 (D-094, docs/26 mục 4.4): việc chờ người quyết và
 * nhật ký mọi lần sửa, quyết (ai, lúc nào, trước, sau). Nháp chưa duyệt chỉ nằm ở đây, không vào bài.
 * SQL chạy được trên MySQL và SQLite (Playground).
 */
final class Viec {

	/** Một lần sửa trực tiếp một phần của bài (trạng thái xong ngay). */
	public const SUA = 'sua';

	public const CHO    = 'cho';
	public const DE_SAU = 'de_sau';
	public const XONG   = 'xong';
	public const BO     = 'bo';

	private const JSON = array( 'noi_dung', 'truoc', 'sau' );

	public static function bang(): string {
		global $wpdb;
		return $wpdb->prefix . 'cadao_viec';
	}

	/** Câu lệnh cho dbDelta (HaTang\CaiDat). */
	public static function sql_tao( string $bang, string $bo_ma ): string {
		return "CREATE TABLE $bang (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		loai varchar(32) NOT NULL,
		trang_thai varchar(16) NOT NULL DEFAULT 'cho',
		phien bigint(20) unsigned NOT NULL DEFAULT 0,
		doi_tuong_loai varchar(16) NOT NULL DEFAULT '',
		doi_tuong_id bigint(20) unsigned NOT NULL DEFAULT 0,
		phan varchar(32) NOT NULL DEFAULT '',
		noi_dung longtext NULL,
		uu_tien smallint(5) NOT NULL DEFAULT 0,
		nguoi_tao varchar(32) NOT NULL DEFAULT '',
		tao_luc datetime NOT NULL,
		quyet varchar(32) NOT NULL DEFAULT '',
		truoc longtext NULL,
		sau longtext NULL,
		nguoi_quyet bigint(20) unsigned NOT NULL DEFAULT 0,
		quyet_luc datetime NULL,
		PRIMARY KEY  (id),
		KEY cho (trang_thai,uu_tien),
		KEY doi_tuong (doi_tuong_loai,doi_tuong_id)
		) $bo_ma;";
	}

	/** @return int mã việc vừa thêm; 0 khi không ghi được */
	public static function tao( array $hang ): int {
		global $wpdb;
		$hang = array_merge(
			array(
				'trang_thai'     => self::CHO,
				'phien'          => 0,
				'doi_tuong_loai' => '',
				'doi_tuong_id'   => 0,
				'phan'           => '',
				'uu_tien'        => 0,
				'nguoi_tao'      => '',
				'tao_luc'        => gmdate( 'Y-m-d H:i:s' ),
				'quyet'          => '',
				'nguoi_quyet'    => 0,
			),
			$hang
		);
		foreach ( self::JSON as $cot ) {
			$hang[ $cot ] = array_key_exists( $cot, $hang ) && null !== $hang[ $cot ] ? self::json( $hang[ $cot ] ) : null;
		}
		return false === $wpdb->insert( self::bang(), $hang ) ? 0 : (int) $wpdb->insert_id;
	}

	public static function lay( int $id ): ?array {
		global $wpdb;
		$hang = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::bang() . ' WHERE id = %d', $id ), ARRAY_A );
		return $hang ? self::doc( $hang ) : null;
	}

	/** Chỉ dùng để trả lại khi lưu hỏng giữa chừng (không ghi nửa chừng). */
	public static function xoa( int $id ): void {
		global $wpdb;
		$wpdb->delete( self::bang(), array( 'id' => $id ), array( '%d' ) );
	}

	/** Việc đang chờ, ưu tiên cao trước. */
	public static function ds_cho( int $so = 50, string $loai = '' ): array {
		global $wpdb;
		$dk = $loai ? $wpdb->prepare( ' AND loai = %s', $loai ) : '';
		$ds = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::bang() . " WHERE trang_thai = %s{$dk} ORDER BY uu_tien DESC, id ASC LIMIT %d", self::CHO, $so ), ARRAY_A );
		return array_map( array( self::class, 'doc' ), $ds ?: array() );
	}

	/** Số việc đang chờ. */
	public static function dem_cho(): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::bang() . ' WHERE trang_thai = %s', self::CHO ) );
	}

	/** Việc còn mở (chờ, để sau) của một đối tượng, theo loại việc (rỗng = mọi loại). */
	public static function mo( string $doi_tuong_loai, int $doi_tuong_id, string $loai = '' ): array {
		global $wpdb;
		$dk = $loai ? $wpdb->prepare( ' AND loai = %s', $loai ) : '';
		$ds = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::bang() . " WHERE doi_tuong_loai = %s AND doi_tuong_id = %d AND trang_thai IN ('cho','de_sau'){$dk} ORDER BY id ASC", $doi_tuong_loai, $doi_tuong_id ), ARRAY_A );
		return array_map( array( self::class, 'doc' ), $ds ?: array() );
	}

	/** Mọi việc một loại của một đối tượng, mọi trạng thái (để không tạo lại việc người đã quyết). */
	public static function cua( string $doi_tuong_loai, int $doi_tuong_id, string $loai ): array {
		global $wpdb;
		$ds = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::bang() . ' WHERE doi_tuong_loai = %s AND doi_tuong_id = %d AND loai = %s ORDER BY id ASC', $doi_tuong_loai, $doi_tuong_id, $loai ), ARRAY_A );
		return array_map( array( self::class, 'doc' ), $ds ?: array() );
	}

	/** Đóng một việc: trạng thái xong / bỏ / để sau, hành động đã chọn, người quyết, lúc quyết. */
	public static function dong( int $id, string $trang_thai, string $quyet, int $nguoi ): bool {
		global $wpdb;
		return 1 === $wpdb->query( $wpdb->prepare(
			'UPDATE ' . self::bang() . " SET trang_thai = %s, quyet = %s, nguoi_quyet = %d, quyet_luc = %s, phien = phien + 1 WHERE id = %d AND trang_thai IN ('cho','de_sau','dang_lam')",
			$trang_thai, $quyet, $nguoi, gmdate( 'Y-m-d H:i:s' ), $id
		) );
	}

	/** Giữ một phiên thẻ trong giao dịch; chỉ một yêu cầu được quyền thực hiện tác dụng phụ. */
	public static function giu( int $id, int $phien ): bool {
		global $wpdb;
		return 1 === $wpdb->query( $wpdb->prepare(
			'UPDATE ' . self::bang() . " SET trang_thai = 'dang_lam' WHERE id = %d AND phien = %d AND trang_thai IN ('cho','de_sau')",
			$id, $phien
		) );
	}

	/** Lịch sử của một đối tượng (bài, phân loại): việc đã quyết, mới nhất trước. */
	public static function lich_su( string $doi_tuong_loai, int $doi_tuong_id, int $so = 50 ): array {
		global $wpdb;
		$ds = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . self::bang() . ' WHERE doi_tuong_loai = %s AND doi_tuong_id = %d AND trang_thai = %s ORDER BY id DESC LIMIT %d', $doi_tuong_loai, $doi_tuong_id, self::XONG, $so ),
			ARRAY_A
		);
		return array_map( array( self::class, 'doc' ), $ds ?: array() );
	}

	private static function doc( array $hang ): array {
		foreach ( array( 'id', 'doi_tuong_id', 'uu_tien', 'nguoi_quyet', 'phien' ) as $cot ) {
			$hang[ $cot ] = (int) $hang[ $cot ];
		}
		foreach ( self::JSON as $cot ) {
			$hang[ $cot ] = null === $hang[ $cot ] ? null : json_decode( $hang[ $cot ], true );
		}
		return $hang;
	}

	private static function json( mixed $v ): string {
		return (string) wp_json_encode( $v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
}
