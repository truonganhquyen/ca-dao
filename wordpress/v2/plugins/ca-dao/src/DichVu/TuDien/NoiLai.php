<?php

namespace Phongdao\DichVu\TuDien;

use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\GiaoTiep\BienTap\TamSua;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\Phan;

defined( 'ABSPATH' ) || exit;

/**
 * Từ khó đã mất nối Từ điển (D-125). Trước bản sửa, sửa lời giải một từ khó làm rơi số mục từ của mục đó, dù bài vẫn gắn
 * mục từ ấy. Nên một mục "mất nối" nhận ra chắc chắn: từ khó không có số mục từ, và trong các mục từ bài đang gắn có
 * đúng một mục cùng chữ (không phân biệt hoa thường) chưa được từ khó nào khác của bài dùng. Máy chỉ lập danh sách;
 * người bấm "Nối lại" thì mới ghi, qua đường lưu phần bài (có Lịch sử, khôi phục được). Không đụng lời giải, nguồn.
 */
final class NoiLai {

	/**
	 * @return list<array{bai:int,i:int,tu:string,muc:int}> mỗi dòng là một từ khó có thể nối lại
	 */
	public static function ds(): array {
		global $wpdb;
		// Chỉ bài có gắn mục từ; một truy vấn cho phần Từ khó, một truy vấn cho mục từ đang gắn
		$hang = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT m.post_id, m.meta_value FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id
				WHERE m.meta_key = %s AND p.post_type = %s AND p.post_status IN ('publish','draft','pending','private','future')
				AND EXISTS ( SELECT 1 FROM {$wpdb->term_relationships} r JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = r.term_taxonomy_id WHERE r.object_id = m.post_id AND tt.taxonomy = 'muc_tu' )
				ORDER BY m.post_id",
				Phan::khoa( 'chu_thich' ),
				KhaiBao::TAC_PHAM
			)
		) ?: array();
		if ( ! $hang ) {
			return array();
		}
		$gan = array();
		$tt  = wp_get_object_terms( array_map( static fn( $h ): int => (int) $h->post_id, $hang ), 'muc_tu', array( 'fields' => 'all_with_object_id' ) );
		foreach ( is_array( $tt ) ? $tt : array() as $t ) {
			$gan[ (int) $t->object_id ][ (int) $t->term_id ] = (string) $t->name;
		}
		$ra = array();
		foreach ( $hang as $h ) {
			$bai = (int) $h->post_id;
			$v   = maybe_unserialize( $h->meta_value );
			$ds  = self::muc( $v );
			foreach ( self::trong_bai( $v, $gan[ $bai ] ?? array() ) as $i => $muc ) {
				$ra[] = array( 'bai' => $bai, 'i' => $i, 'tu' => (string) ( $ds[ $i ]['tu'] ?? '' ), 'muc' => $muc );
			}
		}
		return $ra;
	}

	/**
	 * Từ khó nối lại được trong một bài: vị trí mục => số mục từ.
	 *
	 * @param array<int,string> $gan mục từ bài đang gắn (số => tên)
	 * @return array<int,int>
	 */
	public static function trong_bai( mixed $gia_tri, array $gan ): array {
		$ds = self::muc( $gia_tri );
		if ( ! $ds || ! $gan ) {
			return array();
		}
		$da_dung = array_flip( array_filter( array_map( static fn( $m ): int => (int) ( $m['muc_tu_id'] ?? 0 ), $ds ) ) );
		$ra      = array();
		foreach ( $ds as $i => $m ) {
			if ( ! empty( $m['muc_tu_id'] ) || '' === trim( (string) ( $m['tu'] ?? '' ) ) ) {
				continue;
			}
			$khop = array_keys( array_filter( $gan, static fn( string $ten, int $id ): bool => ! isset( $da_dung[ $id ] ) && TamSua::cung_chu( $ten, (string) $m['tu'] ), ARRAY_FILTER_USE_BOTH ) );
			if ( 1 === count( $khop ) && ! in_array( $khop[0], $ra, true ) ) {
				$ra[ $i ] = (int) $khop[0];
			}
		}
		return $ra;
	}

	/**
	 * Nối lại các dòng người đã chọn ("bài:vị trí"). Mỗi bài một lần lưu phần Từ khó; điều kiện được kiểm lại lúc ghi.
	 *
	 * @param list<string> $chon
	 * @return array{bai:int,muc:int,loi:int}
	 */
	public static function noi( array $chon, int $nguoi ): array {
		$theo_bai = array();
		foreach ( $chon as $c ) {
			if ( preg_match( '/^(\d+):(\d+)$/', (string) $c, $m ) ) {
				$theo_bai[ (int) $m[1] ][ (int) $m[2] ] = true;
			}
		}
		$kq = array( 'bai' => 0, 'muc' => 0, 'loi' => 0 );
		foreach ( $theo_bai as $bai => $vi_tri ) {
			$gia_tri = PhanBai::lay( $bai, 'chu_thich' );
			$gan     = array();
			$tt      = get_the_terms( $bai, 'muc_tu' );
			foreach ( is_array( $tt ) ? $tt : array() as $t ) {
				$gan[ (int) $t->term_id ] = (string) $t->name;
			}
			$noi = array_intersect_key( self::trong_bai( $gia_tri, $gan ), $vi_tri );
			if ( ! $noi || null === $gia_tri ) {
				continue;
			}
			$ds = self::muc( $gia_tri );
			foreach ( $noi as $i => $muc ) {
				$ds[ $i ]['muc_tu_id'] = $muc;
			}
			$moi = isset( $gia_tri['muc'] ) ? array( 'muc' => $ds ) + $gia_tri : $ds;
			$r   = LuuPhan::luu( $bai, 'chu_thich', $moi, array( 'nguoi' => $nguoi, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => current_user_can( 'cadao_duyet' ) ) );
			if ( $r['ok'] && $r['doi'] ) {
				++$kq['bai'];
				$kq['muc'] += count( $noi );
			} elseif ( ! $r['ok'] ) {
				++$kq['loi'];
			}
		}
		return $kq;
	}

	/** @return list<array> danh sách mục của phần Từ khó (dạng {muc: […]} hoặc danh sách) */
	private static function muc( mixed $gia_tri ): array {
		if ( ! is_array( $gia_tri ) ) {
			return array();
		}
		$ds = $gia_tri['muc'] ?? $gia_tri;
		return is_array( $ds ) && array_is_list( $ds ) ? array_values( array_filter( $ds, 'is_array' ) ) : array();
	}
}
