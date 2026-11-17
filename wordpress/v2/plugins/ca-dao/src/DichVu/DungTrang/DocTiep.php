<?php

namespace Phongdao\DichVu\DungTrang;

use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\Phan;

defined( 'ABSPATH' ) || exit;

/**
 * "Đọc tiếp" cuối trang bài (docs/27 mục 6, D-119 điều 8): tối đa 3 bài đã đăng, mỗi bài kèm lý do thật — bài kề trong
 * cùng tuyển tập trước, rồi cùng mô-típ, cùng chủ đề, cùng nhắc một mục từ, cùng vùng. Không lấp bằng "cùng thể loại":
 * không có bài gần thì trả rỗng (trang bài mời xem các bài cùng thể loại). Chỉ dùng phân loại và tuyển tập có sẵn,
 * không gợi ý theo nghĩa. Bỏ bài có từ ngữ thô tục (D-111). Câu đố: "Đố tiếp" (do_tiep) — câu đố kế tiếp, cùng chủ đề
 * trước.
 */
final class DocTiep {

	public const TOI_DA = 3;

	/** Bài cùng tuyển tập lấy trước, tối đa chừng này khi còn bài gần theo phân loại (để thấy cả lý do khác). */
	public const TUYEN_TAP_TRUOC = 2;

	/** Điểm mỗi phân loại chung, gần nhất trước; lý do lấy theo phân loại điểm cao nhất hai bài cùng có. */
	public const DIEM = array(
		'mo_tip' => 6,
		'chu_de' => 4,
		'muc_tu' => 3,
		'vung'   => 2,
	);

	/** Mục từ dùng để nối bài: có ở 2–20 bài (từ có ở quá nhiều bài không nói lên hai bài gần nhau). */
	public const MUC_TU_TOI_DA = 20;

	/** @return list<array<string,mixed>> thẻ bài (DocBai::the) + 'ly_do' */
	public static function cho( \WP_Post $bai ): array {
		$the = static function ( int $id, string $ly_do ): ?array {
			$p = get_post( $id );
			if ( ! $p || KhaiBao::TAC_PHAM !== $p->post_type || 'publish' !== $p->post_status || self::bi_che( $id ) ) {
				return null;
			}
			$c = DocBai::the( $p );
			return $c && empty( $c['tho_tuc'] ) ? $c + array( 'ly_do' => $ly_do ) : null; // không gợi ý bài có từ ngữ thô tục (D-111)
		};
		// Cùng tuyển tập: các bài kề sau rồi kề trước bài này trong bộ
		$tt = array();
		foreach ( DocBai::tuyen_tap_co( $bai->ID ) as $bo ) {
			$i = (int) array_search( $bai->ID, $bo['ids'], true );
			foreach ( array_merge( array_slice( $bo['ids'], $i + 1 ), array_reverse( array_slice( $bo['ids'], 0, $i ) ) ) as $id ) {
				if ( count( $tt ) >= self::TOI_DA ) {
					break 2;
				}
				if ( $id !== $bai->ID && ! isset( $tt[ $id ] ) ) {
					$c = $the( $id, 'Cùng tuyển tập ' . $bo['ten'] );
					if ( $c ) {
						$tt[ $id ] = $c;
					}
				}
			}
		}
		$pl = array();
		foreach ( self::theo_phan_loai( $bai, array_keys( $tt ) ) as $id => $ly_do ) {
			if ( count( $pl ) >= self::TOI_DA ) {
				break;
			}
			$c = $the( $id, $ly_do );
			if ( $c ) {
				$pl[] = $c;
			}
		}
		$tt  = array_values( $tt );
		$dau = array_slice( $tt, 0, $pl ? self::TUYEN_TAP_TRUOC : self::TOI_DA );
		return array_slice( array_merge( $dau, $pl, array_slice( $tt, count( $dau ) ) ), 0, self::TOI_DA );
	}

	/**
	 * Bài gần theo phân loại chung (mô-típ, chủ đề, mục từ, vùng), điểm cao và mới trước.
	 *
	 * @param int[] $bo bài đã chọn (bỏ qua)
	 * @return array<int,string> id => lý do
	 */
	private static function theo_phan_loai( \WP_Post $bai, array $bo ): array {
		$cua     = static function ( int $id, string $tax ): array {
			$ds = get_the_terms( $id, $tax );
			$ds = is_array( $ds ) ? $ds : array();
			return 'muc_tu' === $tax ? array_values( array_filter( $ds, static fn( $t ) => $t->count >= 2 && $t->count <= self::MUC_TU_TOI_DA ) ) : $ds;
		};
		$cua_bai = array();
		foreach ( array_keys( self::DIEM ) as $tax ) {
			$cua_bai[ $tax ] = taxonomy_exists( $tax ) ? wp_list_pluck( $cua( $bai->ID, $tax ), 'term_id' ) : array();
		}
		$hoi = array( 'relation' => 'OR' );
		foreach ( array_filter( $cua_bai ) as $tax => $ids ) {
			$hoi[] = array( 'taxonomy' => $tax, 'field' => 'term_id', 'terms' => $ids );
		}
		if ( count( $hoi ) < 2 ) {
			return array();
		}
		// Bỏ bài có từ ngữ thô tục bằng PHP trên tập ứng viên nhỏ, không thêm điều kiện meta vào truy vấn
		$ung_vien = get_posts( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'no_found_rows' => true, 'ignore_sticky_posts' => true, 'post__not_in' => array_merge( array( $bai->ID ), $bo ), 'tax_query' => $hoi, 'numberposts' => 80, 'orderby' => 'ID', 'order' => 'DESC' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery -- tối đa 80 bài
		$ten      = array(
			'mo_tip' => static fn( $t ) => 'Cùng mô-típ ' . $t->name,
			'chu_de' => static fn( $t ) => 'Cùng chủ đề ' . $t->name,
			'muc_tu' => static fn( $t ) => 'Cùng nhắc tới "' . $t->name . '"',
			'vung'   => static fn( $t ) => 'Cùng vùng ' . $t->name,
		);
		$diem  = array();
		$ly_do = array();
		foreach ( $ung_vien as $p ) {
			if ( self::bi_che( $p->ID ) ) {
				continue;
			}
			$d = 0;
			foreach ( $cua_bai as $tax => $ids ) {
				if ( ! $ids ) {
					continue;
				}
				$chung_tax = array_values( array_filter( $cua( $p->ID, $tax ), static fn( $t ) => in_array( $t->term_id, $ids, true ) ) );
				$d        += self::DIEM[ $tax ] * count( $chung_tax );
				if ( $chung_tax && ! isset( $ly_do[ $p->ID ] ) ) {
					$ly_do[ $p->ID ] = $ten[ $tax ]( $chung_tax[0] );
				}
			}
			if ( $d > 0 ) {
				$diem[ $p->ID ] = $d;
			}
		}
		$moi_hon = array_flip( wp_list_pluck( $ung_vien, 'ID' ) ); // đã xếp mới trước
		uksort( $diem, static fn( $a, $b ) => array( $diem[ $b ], $moi_hon[ $a ] ) <=> array( $diem[ $a ], $moi_hon[ $b ] ) );
		$ra = array();
		foreach ( array_keys( $diem ) as $id ) {
			$ra[ $id ] = $ly_do[ $id ];
		}
		return $ra;
	}

	/**
	 * Câu đố kế tiếp cho nút "Đố tiếp" (D-119): câu đố đăng sau câu này cùng chủ đề đầu tiên của câu, hết thì vòng lại từ
	 * đầu; không có câu cùng chủ đề thì câu đố kế tiếp bất kỳ. Bỏ câu có từ ngữ thô tục. '' khi không có câu đố nào khác.
	 */
	public static function do_tiep( \WP_Post $bai ): string {
		global $wpdb;
		$tl = get_term_by( 'slug', 'cau-do', KhaiBao::THE_LOAI );
		if ( ! $tl instanceof \WP_Term ) {
			return '';
		}
		$cd = get_the_terms( $bai, 'chu_de' );
		$cd = is_array( $cd ) && $cd ? (int) $cd[0]->term_id : 0;
		foreach ( array_unique( array( $cd, 0 ) ) as $chu_de ) {
			foreach ( array( '>', '<' ) as $huong ) {
				$noi = $chu_de ? " JOIN {$wpdb->term_relationships} r2 ON r2.object_id = p.ID JOIN {$wpdb->term_taxonomy} t2 ON t2.term_taxonomy_id = r2.term_taxonomy_id AND t2.term_id = " . (int) $chu_de : '';
				$ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT p.ID FROM {$wpdb->posts} p JOIN {$wpdb->term_relationships} r ON r.object_id = p.ID JOIN {$wpdb->term_taxonomy} t ON t.term_taxonomy_id = r.term_taxonomy_id AND t.term_id = %d$noi WHERE p.post_type = %s AND p.post_status = 'publish' AND p.ID $huong %d ORDER BY p.ID ASC LIMIT 6", // phpcs:ignore WordPress.DB.PreparedSQL -- $huong là hằng; $noi chỉ có số nguyên
						(int) $tl->term_id,
						KhaiBao::TAC_PHAM,
						$bai->ID
					)
				);
				foreach ( array_map( 'intval', $ids ) as $id ) {
					if ( ! self::bi_che( $id ) ) {
						return (string) get_permalink( $id );
					}
				}
			}
		}
		return '';
	}

	private static function bi_che( int $id ): bool {
		return metadata_exists( 'post', $id, Phan::khoa( 'tho_tuc' ) );
	}
}
