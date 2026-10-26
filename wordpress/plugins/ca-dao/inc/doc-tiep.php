<?php
/**
 * "Đọc tiếp" cuối bài — bài gần gũi (T-092, T-122; docs/25 ý 4): 3–5 bài đã đăng cùng mô-típ, cùng chủ đề, cùng nhắc
 * một mục từ (từ khó, địa danh được chú thích), cùng vùng (mô-típ, chủ đề, vùng do người sáng lập duyệt); thiếu thì thêm bài
 * cùng thể loại đăng gần đó. Mỗi bài: tên, hai dòng đầu và một nhãn vì sao được gợi ý. Chỉ dùng phân loại có sẵn, không
 * gợi ý theo nghĩa; không JavaScript.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_DOC_TIEP_TOI_DA    = 5;
const CADAO_DOC_TIEP_TOI_THIEU = 3;

/** Điểm của mỗi phân loại chung (mô-típ gần nhất); nhãn lấy theo phân loại điểm cao nhất mà hai bài cùng có. */
const CADAO_DOC_TIEP_DIEM = array( 'mo_tip' => 6, 'chu_de' => 4, 'tu_kho' => 3, 'vung' => 2 );

/** Mục từ dùng để nối bài: có ở 2–20 bài (từ có ở quá nhiều bài thì không nói lên hai bài gần nhau). */
const CADAO_DOC_TIEP_MUC_TU_TOI_DA = 20;

/**
 * Bài nên đọc tiếp sau $bai, gần nhất trước: cộng điểm theo từng phân loại chung (CADAO_DOC_TIEP_DIEM); bằng điểm thì
 * bài mới hơn trước. Chưa đủ 3 bài thì thêm bài cùng thể loại đăng ngay trước (rồi ngay sau) bài này.
 *
 * @return array<int, array{bai: WP_Post, nhan: string}>
 */
function cadao_doc_tiep( WP_Post $bai ): array {
	$cua = static function ( int $id, string $tax ): array {
		$ds = get_the_terms( $id, $tax );
		$ds = ( $ds && ! is_wp_error( $ds ) ) ? $ds : array();
		return 'tu_kho' === $tax ? array_values( array_filter( $ds, static fn( $t ) => $t->count >= 2 && $t->count <= CADAO_DOC_TIEP_MUC_TU_TOI_DA ) ) : $ds;
	};
	$cua_bai = array();
	foreach ( array_keys( CADAO_DOC_TIEP_DIEM ) as $tax ) {
		$cua_bai[ $tax ] = taxonomy_exists( $tax ) ? wp_list_pluck( $cua( $bai->ID, $tax ), 'term_id' ) : array();
	}
	$chung = array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'no_found_rows' => true, 'ignore_sticky_posts' => true );
	$diem  = array();
	$nhan  = array();
	$ra    = array();
	$hoi   = array( 'relation' => 'OR' );
	foreach ( array_filter( $cua_bai ) as $tax => $ids ) {
		$hoi[] = array( 'taxonomy' => $tax, 'field' => 'term_id', 'terms' => $ids );
	}
	if ( count( $hoi ) > 1 ) {
		$ung_vien = get_posts( $chung + array( 'post__not_in' => array( $bai->ID ), 'tax_query' => $hoi, 'numberposts' => 80, 'orderby' => 'ID', 'order' => 'DESC' ) );
		$ten_nhan = array(
			'mo_tip' => static fn( $t ) => 'Cùng mô-típ ' . $t->name,
			'chu_de' => static fn( $t ) => 'Cùng chủ đề ' . $t->name,
			'tu_kho' => static fn( $t ) => 'Cùng nhắc tới "' . $t->name . '"',
			'vung'   => static fn( $t ) => 'Cùng vùng ' . $t->name,
		);
		foreach ( $ung_vien as $p ) {
			$d = 0;
			foreach ( $cua_bai as $tax => $ids ) {
				if ( ! $ids ) {
					continue;
				}
				$chung_tax = array_values( array_filter( $cua( $p->ID, $tax ), static fn( $t ) => in_array( $t->term_id, $ids, true ) ) );
				$d        += CADAO_DOC_TIEP_DIEM[ $tax ] * count( $chung_tax );
				if ( $chung_tax && ! isset( $nhan[ $p->ID ] ) ) { // CADAO_DOC_TIEP_DIEM xếp từ gần nhất
					$nhan[ $p->ID ] = $ten_nhan[ $tax ]( $chung_tax[0] );
				}
			}
			if ( $d > 0 ) {
				$diem[ $p->ID ] = $d;
			}
		}
		$moi_hon = array_flip( wp_list_pluck( $ung_vien, 'ID' ) ); // đã xếp mới trước
		uksort( $diem, static fn( $a, $b ) => array( $diem[ $b ], $moi_hon[ $a ] ) <=> array( $diem[ $a ], $moi_hon[ $b ] ) );
		foreach ( array_slice( array_keys( $diem ), 0, CADAO_DOC_TIEP_TOI_DA ) as $id ) {
			$ra[] = array( 'bai' => get_post( $id ), 'nhan' => $nhan[ $id ] );
		}
	}
	$thieu = CADAO_DOC_TIEP_TOI_THIEU - count( $ra );
	$the   = wp_list_pluck( $cua( $bai->ID, 'the_loai' ), 'term_id' );
	if ( $thieu > 0 && $the ) {
		$bo  = array_merge( array( $bai->ID ), array_map( static fn( $x ) => $x['bai']->ID, $ra ) );
		$tl  = array( array( 'taxonomy' => 'the_loai', 'field' => 'term_id', 'terms' => $the ) );
		$gan = array();
		foreach ( array( array( '<', 'DESC' ), array( '>', 'ASC' ) ) as [ $huong, $thu_tu ] ) {
			$loc = static function ( $where ) use ( $bai, $huong ) {
				global $wpdb;
				return $where . " AND {$wpdb->posts}.ID $huong " . (int) $bai->ID;
			};
			add_filter( 'posts_where', $loc );
			$gan = array_merge( $gan, get_posts( $chung + array( 'post__not_in' => $bo, 'tax_query' => $tl, 'numberposts' => $thieu - count( $gan ), 'orderby' => 'ID', 'order' => $thu_tu, 'suppress_filters' => false ) ) );
			remove_filter( 'posts_where', $loc );
			if ( count( $gan ) >= $thieu ) {
				break;
			}
		}
		foreach ( $gan as $p ) {
			$ra[] = array( 'bai' => $p, 'nhan' => 'Cùng thể loại ' . mb_strtolower( cadao_ten_the_loai( $p ) ) );
		}
	}
	return $ra;
}

/** Khối "Đọc tiếp" (rỗng khi không có bài nào để gợi ý). */
function cadao_khoi_doc_tiep( WP_Post $bai ): string {
	$ds = cadao_doc_tiep( $bai );
	if ( ! $ds ) {
		return '';
	}
	$ra = '<section class="cadao-doc-tiep" aria-labelledby="cadao-doc-tiep-tieu"><h2 id="cadao-doc-tiep-tieu">Đọc tiếp</h2><ul>';
	foreach ( $ds as $x ) {
		$ra .= '<li><a href="' . esc_url( get_permalink( $x['bai'] ) ) . '"><strong>' . esc_html( get_the_title( $x['bai'] ) ) . '</strong>'
			. '<span class="cadao-doc-tiep-loi">' . esc_html( str_replace( "\n", ' / ', cadao_loi_rut_gon( $x['bai'], 2 ) ) ) . '</span>'
			. '<span class="cadao-tim-phu">' . esc_html( $x['nhan'] ) . '</span></a></li>';
	}
	return $ra . '</ul></section>';
}

// Sau Chủ đề, Vùng (34) và "Cảm nhận, phân tích" (35), trước "Bạn muốn góp gì?" (40)
add_filter(
	'the_content',
	static function ( $noi_dung ) {
		if ( ! is_singular( CADAO_LOAI_BAI ) || get_the_ID() !== get_queried_object_id() || doing_filter( 'get_the_excerpt' ) ) {
			return $noi_dung;
		}
		return $noi_dung . cadao_khoi_doc_tiep( get_post() );
	},
	36
);
