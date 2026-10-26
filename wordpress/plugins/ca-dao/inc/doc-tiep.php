<?php
/**
 * "Đọc tiếp" cuối bài, bản tối giản (D-075; thay một phần T-092 đang tạm dừng — không có trang mô-típ): 3–5 bài đã
 * đăng cùng chủ đề, cùng vùng (đã duyệt); thiếu thì thêm bài cùng thể loại đăng gần đó. Mỗi bài: tên, hai dòng đầu và
 * một nhãn vì sao được gợi ý. Chỉ dùng phân loại có sẵn, không gợi ý theo nghĩa; không JavaScript.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_DOC_TIEP_TOI_DA    = 5;
const CADAO_DOC_TIEP_TOI_THIEU = 3;

/**
 * Bài nên đọc tiếp sau $bai, gần nhất trước: cùng chủ đề (2 điểm mỗi chủ đề chung), cùng vùng (1 điểm); bằng điểm thì
 * bài mới hơn trước. Chưa đủ 3 bài thì thêm bài cùng thể loại đăng ngay trước (rồi ngay sau) bài này.
 *
 * @return array<int, array{bai: WP_Post, nhan: string}>
 */
function cadao_doc_tiep( WP_Post $bai ): array {
	$cua = static function ( int $id, string $tax ): array {
		$ds = get_the_terms( $id, $tax );
		return ( $ds && ! is_wp_error( $ds ) ) ? $ds : array();
	};
	$chu_de = wp_list_pluck( $cua( $bai->ID, 'chu_de' ), 'term_id' );
	$vung   = wp_list_pluck( $cua( $bai->ID, 'vung' ), 'term_id' );
	$chung  = array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'no_found_rows' => true, 'ignore_sticky_posts' => true );
	$diem   = array();
	$nhan   = array();
	$ra     = array();
	if ( $chu_de || $vung ) {
		$hoi = array( 'relation' => 'OR' );
		foreach ( array( 'chu_de' => $chu_de, 'vung' => $vung ) as $tax => $ids ) {
			if ( $ids ) {
				$hoi[] = array( 'taxonomy' => $tax, 'field' => 'term_id', 'terms' => $ids );
			}
		}
		$ung_vien = get_posts( $chung + array( 'post__not_in' => array( $bai->ID ), 'tax_query' => $hoi, 'numberposts' => 60, 'orderby' => 'ID', 'order' => 'DESC' ) );
		foreach ( $ung_vien as $p ) {
			$cd = array_values( array_filter( $cua( $p->ID, 'chu_de' ), static fn( $t ) => in_array( $t->term_id, $chu_de, true ) ) );
			$vu = array_values( array_filter( $cua( $p->ID, 'vung' ), static fn( $t ) => in_array( $t->term_id, $vung, true ) ) );
			$diem[ $p->ID ] = 2 * count( $cd ) + count( $vu );
			$nhan[ $p->ID ] = $cd ? 'Cùng chủ đề ' . $cd[0]->name : 'Cùng vùng ' . $vu[0]->name;
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
