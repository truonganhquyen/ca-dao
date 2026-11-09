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

	/** Số thẻ theo loại của một trạng thái, không phụ thuộc giới hạn danh sách. */
	public static function dem_loai( string $trang_thai ): array {
		global $wpdb;
		$hang = $wpdb->get_results( $wpdb->prepare( 'SELECT loai, COUNT(*) AS so FROM ' . self::bang() . ' WHERE trang_thai = %s GROUP BY loai', $trang_thai ), ARRAY_A );
		return array_column( $hang ?: array(), 'so', 'loai' );
	}

	/** Số thẻ khớp trạng thái và loại (loại rỗng = mọi loại). */
	public static function dem( string $trang_thai, string $loai = '' ): int {
		global $wpdb;
		$sql  = 'SELECT COUNT(*) FROM ' . self::bang() . ' WHERE trang_thai = %s';
		$tham = array( $trang_thai );
		if ( '' !== $loai ) {
			$sql   .= ' AND loai = %s';
			$tham[] = $loai;
		}
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $tham ) );
	}

	/** Một trang thẻ; ORDER BY ổn định khi cùng độ ưu tiên. */
	public static function trang( string $trang_thai, string $loai, int $so, int $bo_qua ): array {
		global $wpdb;
		$sql  = 'SELECT * FROM ' . self::bang() . ' WHERE trang_thai = %s';
		$tham = array( $trang_thai );
		if ( '' !== $loai ) {
			$sql   .= ' AND loai = %s';
			$tham[] = $loai;
		}
		$sql   .= ' ORDER BY uu_tien DESC, id ASC LIMIT %d OFFSET %d';
		$tham[] = max( 1, min( 50, $so ) );
		$tham[] = max( 0, $bo_qua );
		$ds     = $wpdb->get_results( $wpdb->prepare( $sql, $tham ), ARRAY_A );
		return array_map( array( self::class, 'doc' ), $ds ?: array() );
	}

	/** Danh sách hàng chờ trong wp-admin: số đếm và trang dùng chung đúng điều kiện lọc. */
	public static function tim_quan_tri( string $trang_thai, string $loai, string $tim, int $trang, int $moi_trang = 30 ): array {
		global $wpdb;
		$hop_le = array( self::CHO, self::DE_SAU, self::XONG, self::BO );
		if ( ! in_array( $trang_thai, array_merge( array( 'tat_ca' ), $hop_le ), true ) ) {
			$trang_thai = self::CHO;
		}
		$dk = "v.loai <> 'sua' AND v.trang_thai IN ('cho','de_sau','xong','bo')";
		$tham = array();
		if ( 'tat_ca' !== $trang_thai ) {
			$dk .= ' AND v.trang_thai = %s';
			$tham[] = $trang_thai;
		}
		if ( '' !== $loai ) {
			$dk .= ' AND v.loai = %s';
			$tham[] = $loai;
		}
		$noi = '';
		if ( '' !== $tim ) {
			if ( ctype_digit( $tim ) ) {
				$dk .= ' AND (v.id = %d OR v.doi_tuong_id = %d)';
				$tham[] = (int) $tim;
				$tham[] = (int) $tim;
			} else {
				$noi = ' LEFT JOIN ' . $wpdb->posts . " p ON v.doi_tuong_loai = 'post' AND p.ID = v.doi_tuong_id"
					. ' LEFT JOIN ' . $wpdb->comments . " c ON v.doi_tuong_loai = 'comment' AND c.comment_ID = v.doi_tuong_id"
					. ' LEFT JOIN ' . Kho::bang() . " k ON v.doi_tuong_loai = 'kho' AND k.id = v.doi_tuong_id";
				$dk .= ' AND (v.noi_dung LIKE %s OR p.post_title LIKE %s OR c.comment_content LIKE %s OR k.tieu_de LIKE %s OR k.loi LIKE %s)';
				$like = '%' . $wpdb->esc_like( $tim ) . '%';
				$tham = array_merge( $tham, array_fill( 0, 5, $like ) );
			}
		}
		$tu = ' FROM ' . self::bang() . ' v' . $noi . ' WHERE ' . $dk;
		$sql_dem = 'SELECT COUNT(*)' . $tu;
		$tong = (int) $wpdb->get_var( $tham ? $wpdb->prepare( $sql_dem, $tham ) : $sql_dem );
		$so = max( 1, min( 50, $moi_trang ) );
		$bo = ( max( 1, $trang ) - 1 ) * $so;
		$thu_tu = in_array( $trang_thai, array( self::CHO, self::DE_SAU ), true ) ? 'v.uu_tien DESC, v.id ASC' : 'v.id DESC';
		$ds = $wpdb->get_results( $wpdb->prepare( 'SELECT v.*' . $tu . ' ORDER BY ' . $thu_tu . ' LIMIT %d OFFSET %d', array_merge( $tham, array( $so, $bo ) ) ), ARRAY_A );
		return array( 'tong' => $tong, 'ds' => array_map( array( self::class, 'doc' ), $ds ?: array() ) );
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
	public static function dong( int $id, string $trang_thai, string $quyet, int $nguoi, string $trang_thai_truoc = '', array $chi_tiet_truoc = array() ): bool {
		global $wpdb;
		$truoc = '' === $trang_thai_truoc && ! $chi_tiet_truoc ? '' : ', truoc = %s';
		$tham  = array( $trang_thai, $quyet, $nguoi, gmdate( 'Y-m-d H:i:s' ) );
		if ( $truoc ) {
			$tham[] = self::json( array( 'trang_thai' => $trang_thai_truoc ) + $chi_tiet_truoc );
		}
		$tham[] = $id;
		return 1 === $wpdb->query( $wpdb->prepare(
			'UPDATE ' . self::bang() . " SET trang_thai = %s, quyet = %s, nguoi_quyet = %d, quyet_luc = %s{$truoc}, phien = phien + 1 WHERE id = %d AND trang_thai IN ('cho','de_sau','dang_lam')",
			$tham
		) );
	}

	/** Trả đúng phiên quyết về trạng thái trước; bản ghi nhật ký hoàn tác được tạo trong cùng giao dịch ở lớp dịch vụ. */
	public static function hoan_tac_quyet( int $id, int $phien, string $trang_thai, int $nguoi, string $ve ): bool {
		global $wpdb;
		return 1 === $wpdb->query( $wpdb->prepare(
			'UPDATE ' . self::bang() . ' SET trang_thai = %s, quyet = %s, nguoi_quyet = 0, quyet_luc = NULL, truoc = NULL, phien = phien + 1 WHERE id = %d AND phien = %d AND trang_thai = %s AND nguoi_quyet = %d',
			$ve, '', $id, $phien, $trang_thai, $nguoi
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

	/**
	 * Lịch sử của một đối tượng (bài, phân loại, hồ sơ nguồn): việc đã quyết, mới nhất trước. Mặc định chỉ việc "xong";
	 * màn Lịch sử truyền thêm "bỏ" để thấy cả đề xuất đã bỏ.
	 *
	 * @param string[] $trang_thai
	 */
	public static function lich_su( string $doi_tuong_loai, int $doi_tuong_id, int $so = 50, array $trang_thai = array( self::XONG ) ): array {
		global $wpdb;
		$tt = array_values( array_intersect( $trang_thai, array( self::XONG, self::BO ) ) ) ?: array( self::XONG );
		$ds = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . self::bang() . ' WHERE doi_tuong_loai = %s AND doi_tuong_id = %d AND trang_thai IN (' . implode( ',', array_fill( 0, count( $tt ), '%s' ) ) . ') ORDER BY id DESC LIMIT %d', array_merge( array( $doi_tuong_loai, $doi_tuong_id ), $tt, array( $so ) ) ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);
		return array_map( array( self::class, 'doc' ), $ds ?: array() );
	}

	/** Mã dòng sửa mới nhất của một phần (hay một bộ phân loại) của bài; 0 khi chưa có. */
	public static function moi_nhat_sua( int $bai, string $phan ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT MAX(id) FROM ' . self::bang() . ' WHERE loai = %s AND doi_tuong_loai = %s AND doi_tuong_id = %d AND phan = %s', self::SUA, 'post', $bai, $phan ) );
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
