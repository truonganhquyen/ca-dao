<?php

namespace Phongdao\DichVu\UngVien;

use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Dấu "Để dành" / "Đề nghị đăng" trên câu kho (D-104, docs/32 mục 3.3): câu người muốn quyết sau nằm lại Kho (dòng Bài
 * mới), lọc lại được; người chỉ có quyền soạn đánh "Đề nghị đăng" cho người có quyền duyệt. Thay nút "Đưa vào Hộp việc".
 *
 * Lưu trong bảng việc (loại `danh_dau`, trạng thái riêng `danh` — không phải việc chờ, không vào Hộp việc, không tính
 * vào số việc), nên có sẵn ai đánh, lúc nào; bỏ dấu ghi người bỏ (trạng thái `bo`). Không đăng, không sửa lời, không bỏ
 * câu: chỉ là dấu.
 */
final class DanhDau {

	public const VIEC = 'danh_dau';
	public const DANH = 'danh';
	/** Loại dấu => chữ. */
	public const LOAI = array(
		'de_danh' => 'Để dành',
		'de_nghi' => 'Đề nghị đăng',
	);

	/** Loại dấu người hiện tại đánh: có quyền duyệt → Để dành; chỉ soạn → Đề nghị đăng. */
	public static function loai_cua( int $nguoi ): string {
		return user_can( $nguoi, 'cadao_duyet' ) ? 'de_danh' : 'de_nghi';
	}

	/**
	 * Đánh dấu một câu kho. Câu phải còn mới, có lời, chưa gắn bài; đã có dấu thì giữ dấu cũ (người có quyền duyệt đánh
	 * "Để dành" lên câu đang "Đề nghị đăng" thì đổi thành Để dành).
	 *
	 * @return array{ok:bool,moi:bool,chu:string}
	 */
	public static function danh( int $kho_id, int $nguoi, string $loai = '' ): array {
		$loai = isset( self::LOAI[ $loai ] ) ? $loai : self::loai_cua( $nguoi );
		$m    = Kho::lay( $kho_id );
		$ly   = UngVien::chua_chon_duoc( $m );
		if ( '' !== $ly ) {
			return array( 'ok' => false, 'moi' => false, 'chu' => $ly );
		}
		$co = self::cua( $kho_id );
		if ( $co ) {
			if ( $co['loai'] === $loai || 'de_nghi' === $loai ) {
				return array( 'ok' => true, 'moi' => false, 'chu' => 'Câu #' . $kho_id . ' đã có dấu "' . self::LOAI[ $co['loai'] ] . '".' );
			}
			self::bo( $kho_id, $nguoi );
		}
		$id = Viec::tao(
			array(
				'loai'           => self::VIEC,
				'trang_thai'     => self::DANH,
				'doi_tuong_loai' => 'kho',
				'doi_tuong_id'   => $kho_id,
				'phan'           => $loai,
				'noi_dung'       => array( 'loai' => $loai ),
				'nguoi_tao'      => (string) $nguoi,
			)
		);
		return $id
			? array( 'ok' => true, 'moi' => true, 'chu' => 'Đã đánh dấu "' . self::LOAI[ $loai ] . '" cho câu #' . $kho_id . '. Câu nằm lại Bài mới, lọc lại được.' )
			: array( 'ok' => false, 'moi' => false, 'chu' => 'Chưa ghi được dấu. Thử lại sau.' );
	}

	/** Bỏ dấu (ghi người bỏ, lúc bỏ). @return bool đã có dấu để bỏ */
	public static function bo( int $kho_id, int $nguoi ): bool {
		global $wpdb;
		return (bool) $wpdb->query( $wpdb->prepare( 'UPDATE ' . Viec::bang() . " SET trang_thai = 'bo', quyet = 'bo_dau', nguoi_quyet = %d, quyet_luc = %s, phien = phien + 1 WHERE loai = %s AND doi_tuong_loai = 'kho' AND doi_tuong_id = %d AND trang_thai = %s", $nguoi, gmdate( 'Y-m-d H:i:s' ), self::VIEC, $kho_id, self::DANH ) );
	}

	/** @return array{id:int,loai:string,nguoi:int,luc:string}|null dấu đang có của một câu */
	public static function cua( int $kho_id ): ?array {
		return self::cua_trang( array( $kho_id ) )[ $kho_id ] ?? null;
	}

	/**
	 * Dấu của đúng các câu trên trang (một truy vấn).
	 *
	 * @param int[] $ids
	 * @return array<int,array{id:int,loai:string,nguoi:int,luc:string}>
	 */
	public static function cua_trang( array $ids ): array {
		global $wpdb;
		$ids = array_values( array_filter( array_map( 'intval', $ids ) ) );
		if ( ! $ids ) {
			return array();
		}
		$ra = array();
		foreach ( $wpdb->get_results( $wpdb->prepare( 'SELECT id, doi_tuong_id, phan, nguoi_tao, tao_luc FROM ' . Viec::bang() . " WHERE loai = %s AND doi_tuong_loai = 'kho' AND trang_thai = %s AND doi_tuong_id IN (" . implode( ',', $ids ) . ') ORDER BY id', self::VIEC, self::DANH ) ) ?: array() as $h ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- mã đã ép số
			$ra[ (int) $h->doi_tuong_id ] = array( 'id' => (int) $h->id, 'loai' => (string) $h->phan, 'nguoi' => (int) $h->nguoi_tao, 'luc' => (string) $h->tao_luc );
		}
		return $ra;
	}

	/** Điều kiện SQL "câu kho (bí danh k) có dấu $loai" ('' = có dấu bất kỳ). */
	public static function sql_co( string $loai = '' ): string {
		global $wpdb;
		return 'EXISTS (SELECT 1 FROM ' . Viec::bang() . " d WHERE d.loai = '" . self::VIEC . "' AND d.doi_tuong_loai = 'kho' AND d.doi_tuong_id = k.id AND d.trang_thai = '" . self::DANH . "'" . ( isset( self::LOAI[ $loai ] ) ? $wpdb->prepare( ' AND d.phan = %s', $loai ) : '' ) . ')';
	}

	/** Số câu còn mới đang mang dấu $loai (Hôm nay: số đề nghị đăng). */
	public static function dem( string $loai ): int {
		global $wpdb;
		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Kho::bang() . " k WHERE k.trang_thai = 'moi' AND k.post_id = 0 AND " . self::sql_co( $loai ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- điều kiện đã prepare
	}
}
