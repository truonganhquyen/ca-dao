<?php

namespace Phongdao\DichVu\DungTrang;

use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\Phan;

defined( 'ABSPATH' ) || exit;

/**
 * "Đọc tiếp" cuối trang bài (docs/27 mục 6; chuyển từ MVP doc-tiep.php): 3–5 bài đã đăng cùng mô-típ, cùng chủ đề,
 * cùng nhắc một mục từ, cùng vùng — mỗi bài kèm lý do; thiếu thì thêm bài cùng thể loại đăng gần đó. Chỉ dùng phân
 * loại có sẵn, không gợi ý theo nghĩa. Bỏ bài có từ ngữ thô tục (D-111).
 */
final class DocTiep {

	public const TOI_DA = 5;

	public const TOI_THIEU = 3;

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
		$cua     = static function ( int $id, string $tax ): array {
			$ds = get_the_terms( $id, $tax );
			$ds = is_array( $ds ) ? $ds : array();
			return 'muc_tu' === $tax ? array_values( array_filter( $ds, static fn( $t ) => $t->count >= 2 && $t->count <= self::MUC_TU_TOI_DA ) ) : $ds;
		};
		$cua_bai = array();
		foreach ( array_keys( self::DIEM ) as $tax ) {
			$cua_bai[ $tax ] = taxonomy_exists( $tax ) ? wp_list_pluck( $cua( $bai->ID, $tax ), 'term_id' ) : array();
		}
		$chung = array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'no_found_rows' => true, 'ignore_sticky_posts' => true );
		// Bỏ bài có từ ngữ thô tục (D-111) bằng PHP trên tập ứng viên nhỏ, không thêm điều kiện meta vào truy vấn
		$bi_che = static fn( int $id ): bool => metadata_exists( 'post', $id, Phan::khoa( 'tho_tuc' ) );
		$diem  = array();
		$ly_do = array();
		$ra    = array();
		$hoi   = array( 'relation' => 'OR' );
		foreach ( array_filter( $cua_bai ) as $tax => $ids ) {
			$hoi[] = array( 'taxonomy' => $tax, 'field' => 'term_id', 'terms' => $ids );
		}
		if ( count( $hoi ) > 1 ) {
			$ung_vien = get_posts( $chung + array( 'post__not_in' => array( $bai->ID ), 'tax_query' => $hoi, 'numberposts' => 80, 'orderby' => 'ID', 'order' => 'DESC' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery -- tối đa 80 bài
			$ten      = array(
				'mo_tip' => static fn( $t ) => 'Cùng mô-típ ' . $t->name,
				'chu_de' => static fn( $t ) => 'Cùng chủ đề ' . $t->name,
				'muc_tu' => static fn( $t ) => 'Cùng nhắc tới "' . $t->name . '"',
				'vung'   => static fn( $t ) => 'Cùng vùng ' . $t->name,
			);
			foreach ( $ung_vien as $p ) {
				if ( $bi_che( $p->ID ) ) {
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
			foreach ( array_slice( array_keys( $diem ), 0, self::TOI_DA ) as $id ) {
				$the = DocBai::the( get_post( $id ) );
				if ( $the && empty( $the['tho_tuc'] ) ) { // không gợi ý bài có từ ngữ thô tục (D-111)
					$ra[] = $the + array( 'ly_do' => $ly_do[ $id ] );
				}
			}
		}
		$thieu = self::TOI_THIEU - count( $ra );
		$tl    = wp_list_pluck( $cua( $bai->ID, KhaiBao::THE_LOAI ), 'term_id' );
		if ( $thieu > 0 && $tl ) {
			global $wpdb;
			$bo  = array_merge( array( $bai->ID ), array_column( $ra, 'id' ) );
			$gan = array();
			// Bài cùng thể loại đăng ngay trước, rồi ngay sau bài này
			foreach ( array( array( '<', 'DESC' ), array( '>', 'ASC' ) ) as [ $huong, $thu_tu ] ) {
				$ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT p.ID FROM {$wpdb->posts} p JOIN {$wpdb->term_relationships} r ON r.object_id = p.ID JOIN {$wpdb->term_taxonomy} t ON t.term_taxonomy_id = r.term_taxonomy_id WHERE p.post_type = %s AND p.post_status = 'publish' AND t.term_id = %d AND p.ID $huong %d AND p.ID NOT IN (" . implode( ',', array_map( 'intval', $bo ) ) . ") ORDER BY p.ID $thu_tu LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL -- $huong, $thu_tu là hằng; danh sách bỏ là số nguyên
						KhaiBao::TAC_PHAM,
						(int) $tl[0],
						$bai->ID,
						$thieu - count( $gan ) + 5 // dư vài bài để bù bài bị che
					)
				);
				foreach ( array_map( 'intval', $ids ) as $x ) {
					if ( count( $gan ) < $thieu && ! $bi_che( $x ) ) {
						$gan[] = $x;
					}
				}
				if ( count( $gan ) >= $thieu ) {
					break;
				}
			}
			foreach ( $gan as $id ) {
				$the = DocBai::the( get_post( $id ) );
				if ( $the && empty( $the['tho_tuc'] ) ) { // không gợi ý bài có từ ngữ thô tục (D-111)
					$ra[] = $the + array( 'ly_do' => 'Cùng thể loại ' . mb_strtolower( $the['ten_the_loai'] ) );
				}
			}
		}
		return $ra;
	}
}
