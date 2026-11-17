<?php

namespace Phongdao\DichVu\CanSua;

use Phongdao\HaTang\KhaiBao;
use Phongdao\HaTang\Module;
use Phongdao\MienDuLieu\KiemTrinhBay;
use Phongdao\MienDuLieu\Phan;

defined( 'ABSPATH' ) || exit;

/**
 * Danh sách "Cần sửa" ở Bổ sung (D-119, docs/41 mục 2 điều 9): máy rà nội dung đã đăng theo quy tắc viết
 * (MienDuLieu\KiemTrinhBay) — Ý nghĩa, từ khó, lời, nơi lưu truyền — rồi xếp theo loại lỗi. Máy chỉ đánh dấu: không sửa
 * chữ, không sửa lời. Người sửa ở màn Sửa tác phẩm hoặc bấm "Bỏ qua"; bỏ qua ghi theo đúng bản chữ lúc bấm, chữ đổi thì
 * bài hiện lại. Kết quả quét giữ trong transient, làm mới khi phần bài, trạng thái hay nơi lưu truyền đổi.
 */
final class CanSua implements Module {

	public const TAM = 'phongdao_can_sua';

	/** Post meta nội bộ: mã lỗi => dấu của bản chữ lúc người bấm "Bỏ qua". Không phải phần của bài, không ra trang đọc. */
	public const BO = '_phongdao_bo_nhac';

	public function dang_ky(): void {
		$xoa = static function (): void {
			delete_transient( self::TAM );
		};
		add_action( 'phongdao_da_luu_phan', $xoa );
		add_action(
			'transition_post_status',
			static function ( string $moi, string $cu, \WP_Post $bai ) use ( $xoa ): void {
				if ( $moi !== $cu && KhaiBao::TAC_PHAM === $bai->post_type ) {
					$xoa();
				}
			},
			10,
			3
		);
		add_action(
			'set_object_terms',
			static function ( $id, $terms, $tt_ids, string $tax ) use ( $xoa ): void {
				if ( 'vung' === $tax ) {
					$xoa();
				}
			},
			10,
			4
		);
	}

	/**
	 * Bài đã đăng theo từng loại lỗi (thứ tự KiemTrinhBay::NHOM), trừ lỗi người đã bỏ qua trên đúng bản chữ hiện tại.
	 *
	 * @return array<string,list<int>> mã lỗi => ID bài
	 */
	public static function ds(): array {
		$c = get_transient( self::TAM );
		if ( is_array( $c ) ) {
			return $c;
		}
		global $wpdb;
		$khoa = array( Phan::khoa( 'loi' ), Phan::khoa( 'y_nghia' ), Phan::khoa( 'chu_thich' ), self::BO );
		$hang = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT m.post_id, m.meta_key, m.meta_value FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id WHERE p.post_type = %s AND p.post_status = 'publish' AND p.post_password = '' AND m.meta_key IN (%s, %s, %s, %s)",
				KhaiBao::TAC_PHAM,
				...$khoa
			)
		) ?: array();
		$bai = array();
		foreach ( $hang as $h ) {
			$bai[ (int) $h->post_id ][ (string) $h->meta_key ] = maybe_unserialize( $h->meta_value );
		}
		$cd  = get_term_by( 'slug', 'cau-do', KhaiBao::THE_LOAI );
		$cd  = $cd ? array_flip( array_map( 'intval', (array) get_objects_in_term( $cd->term_id, KhaiBao::THE_LOAI ) ) ) : array();
		$ra  = array_fill_keys( array_keys( KiemTrinhBay::NHOM ), array() );
		foreach ( $bai as $id => $m ) {
			$loi = (array) ( $m[ Phan::khoa( 'loi' ) ]['dong'] ?? array() );
			$bo  = (array) ( $m[ self::BO ] ?? array() );
			$ma  = array();
			if ( is_array( $m[ Phan::khoa( 'y_nghia' ) ] ?? null ) ) {
				foreach ( KiemTrinhBay::y_nghia( (string) ( $m[ Phan::khoa( 'y_nghia' ) ]['chu'] ?? '' ), $loi ) as $n ) {
					// Câu đố: phần này là "Giải thích đáp án", được phép dài hơn
					if ( 'qua_dai' !== $n['ma'] || ! isset( $cd[ $id ] ) ) {
						$ma[] = $n['ma'];
					}
				}
			}
			$ma = array_merge( $ma, array_column( KiemTrinhBay::loi( $loi ), 'ma' ), array_column( KiemTrinhBay::chu_thich( (array) ( $m[ Phan::khoa( 'chu_thich' ) ]['muc'] ?? array() ) ), 'ma' ) );
			foreach ( $ma as $x ) {
				if ( ! isset( $bo[ $x ] ) || $bo[ $x ] !== self::dau( $id, $x ) ) {
					$ra[ $x ][] = $id;
				}
			}
		}
		foreach ( self::vung_mau_thuan() as $id ) {
			$bo = (array) get_post_meta( $id, self::BO, true );
			if ( ! isset( $bo['vung'] ) || $bo['vung'] !== self::dau( $id, 'vung' ) ) {
				$ra['vung'][] = $id;
			}
		}
		set_transient( self::TAM, $ra, 12 * HOUR_IN_SECONDS );
		return $ra;
	}

	/** Số bài cần sửa (mỗi bài đếm một lần). */
	public static function so_bai( array $ds ): int {
		return count( array_unique( array_merge( ...array_values( $ds ) ?: array( array() ) ) ) );
	}

	/** Bài gắn "Phổ biến toàn quốc" cùng một vùng cụ thể (docs/25 mục 4.2: một vùng cụ thể, hoặc toàn quốc, không cả hai). @return int[] */
	private static function vung_mau_thuan(): array {
		$tq = get_term_by( 'slug', 'toan-quoc', 'vung' );
		if ( ! $tq instanceof \WP_Term ) {
			return array();
		}
		$ra = array();
		foreach ( array_map( 'intval', (array) get_objects_in_term( $tq->term_id, 'vung' ) ) as $id ) {
			if ( 'publish' === get_post_status( $id ) && count( wp_get_object_terms( $id, 'vung', array( 'fields' => 'ids' ) ) ?: array() ) > 1 ) {
				$ra[] = $id;
			}
		}
		return $ra;
	}

	/** Dấu của bản chữ mà mã lỗi xét: chữ đổi thì dấu đổi, bài hiện lại trong danh sách. */
	public static function dau( int $id, string $ma ): string {
		$phan = KiemTrinhBay::NHOM[ $ma ][1] ?? '';
		$gt   = 'vung' === $phan
			? wp_get_object_terms( $id, 'vung', array( 'fields' => 'ids', 'orderby' => 'term_id' ) )
			: get_post_meta( $id, Phan::khoa( $phan ), true );
		return md5( (string) wp_json_encode( $gt ) );
	}

	/** "Bỏ qua" một lỗi của một bài (người biên soạn bấm). Không đổi nội dung bài, chỉ ghi dấu bản chữ lúc bấm. */
	public static function bo_qua( int $id, string $ma ): bool {
		if ( ! isset( KiemTrinhBay::NHOM[ $ma ] ) || KhaiBao::TAC_PHAM !== get_post_type( $id ) ) {
			return false;
		}
		$bo        = (array) get_post_meta( $id, self::BO, true );
		$bo[ $ma ] = self::dau( $id, $ma );
		update_post_meta( $id, self::BO, $bo );
		delete_transient( self::TAM );
		return true;
	}

	/** Đoạn ngắn cho người biên soạn thấy chỗ lỗi: Ý nghĩa, dòng lời đầu tiên có lỗi, hay từ khó dài nhất. */
	public static function trich( int $id, string $ma ): string {
		$phan = KiemTrinhBay::NHOM[ $ma ][1] ?? '';
		if ( 'y_nghia' === $phan ) {
			$chu = (string) ( get_post_meta( $id, Phan::khoa( 'y_nghia' ), true )['chu'] ?? '' );
			return ( 'qua_dai' === $ma ? KiemTrinhBay::so_chu( $chu ) . ' chữ · ' : '' ) . mb_strimwidth( $chu, 0, 160, '…' );
		}
		if ( 'loi' === $phan ) {
			foreach ( (array) ( get_post_meta( $id, Phan::khoa( 'loi' ), true )['dong'] ?? array() ) as $d ) {
				if ( array( $ma ) === array_intersect( array( $ma ), array_column( KiemTrinhBay::loi( array( (string) $d ) ), 'ma' ) ) ) {
					return (string) $d;
				}
			}
			return '';
		}
		if ( 'chu_thich' === $phan ) {
			$dai = '';
			foreach ( (array) ( get_post_meta( $id, Phan::khoa( 'chu_thich' ), true )['muc'] ?? array() ) as $m ) {
				$g = (string) ( $m['loi_giai'] ?? '' );
				if ( KiemTrinhBay::so_chu( $g ) > KiemTrinhBay::so_chu( $dai ) ) {
					$dai = trim( (string) ( $m['tu'] ?? '' ) . ': ' . $g, ': ' );
				}
			}
			return mb_strimwidth( $dai, 0, 160, '…' );
		}
		return implode( ', ', wp_get_object_terms( $id, 'vung', array( 'fields' => 'names' ) ) ?: array() );
	}
}
