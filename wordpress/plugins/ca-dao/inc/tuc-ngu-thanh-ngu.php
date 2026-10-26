<?php
/**
 * Một mục "Tục ngữ – Thành ngữ" (D-078, Q-009): ranh giới hai loại mờ, người khó phân biệt, máy dễ xếp sai — nên
 * không tách nữa. Thể loại gộp giữ đường dẫn /tuc-ngu/ (bài tục ngữ không đổi địa chỉ). Bài từng ở "Thành ngữ" chuyển
 * sang mục gộp một lần khi nâng cấp; đường dẫn cũ /thanh-ngu/<tên>/ và /the-loai/thanh-ngu/ chuyển hướng 301.
 * Thay T-080 (trang "Tách thành ngữ – tục ngữ", máy gợi ý loại).
 */

defined( 'ABSPATH' ) || exit;

/** Đoạn thể loại cũ trong đường dẫn → thể loại hiện nay (đường dẫn cũ vẫn mở được, rồi chuyển hướng). */
const CADAO_THE_LOAI_CU = array( 'thanh-ngu' => 'tuc-ngu' );

/** Thể loại (slug) cho bài tạo từ một câu kho. */
function cadao_the_loai_tu_kho( object $muc ): ?string {
	return CADAO_THE_LOAI_TU_LOAI_GOC[ $muc->loai ] ?? null;
}

// ---------- Nâng cấp một lần: gộp "Thành ngữ" vào "Tục ngữ – Thành ngữ" ----------

/** Gộp thể loại thanh-ngu vào tuc-ngu: bài chuyển sang (giữ thể loại khác), mô tả nối vào, thể loại cũ bỏ đi. */
function cadao_gop_tuc_ngu_thanh_ngu(): void {
	$dich = get_term_by( 'slug', 'tuc-ngu', 'the_loai' );
	if ( ! $dich ) {
		$moi  = wp_insert_term( CADAO_THE_LOAI['tuc-ngu'], 'the_loai', array( 'slug' => 'tuc-ngu' ) );
		$dich = is_wp_error( $moi ) ? null : get_term( (int) $moi['term_id'], 'the_loai' );
	}
	if ( ! $dich ) {
		return;
	}
	$cu   = get_term_by( 'slug', 'thanh-ngu', 'the_loai' );
	$mo_ta = trim( (string) $dich->description );
	if ( $cu ) {
		$ids = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => 'the_loai', 'terms' => (int) $cu->term_id ) ) ) );
		foreach ( $ids as $id ) {
			$giu = array_values( array_diff( wp_get_object_terms( $id, 'the_loai', array( 'fields' => 'slugs' ) ), array( 'thanh-ngu', 'tuc-ngu' ) ) );
			wp_set_object_terms( $id, array_merge( array( 'tuc-ngu' ), $giu ), 'the_loai' );
			clean_post_cache( $id );
		}
		$them  = trim( (string) $cu->description );
		$mo_ta = '' === $them ? $mo_ta : trim( $mo_ta . "\n\n" . $them ); // giữ lời giới thiệu người sáng lập đã viết
		wp_delete_term( (int) $cu->term_id, 'the_loai' );
	}
	$doi = array( 'description' => $mo_ta );
	if ( 'Tục ngữ' === $dich->name ) { // tên mặc định cũ; người sáng lập đã đổi tên thì giữ
		$doi['name'] = CADAO_THE_LOAI['tuc-ngu'];
	}
	wp_update_term( (int) $dich->term_id, 'the_loai', $doi );
	// Bộ gợi ý loại của T-080 không còn dùng: thôi tải danh mục Wiktionary hằng tuần
	wp_clear_scheduled_hook( 'cadao_wiktionary_hang_tuan' );
	delete_option( 'cadao_wiktionary' );
	update_option( 'cadao_gop_tn', 1, false );
}

add_action(
	'init',
	static function () {
		if ( ! get_option( 'cadao_gop_tn' ) && taxonomy_exists( 'the_loai' ) && ! wp_installing() ) {
			cadao_gop_tuc_ngu_thanh_ngu();
		}
	},
	25
);

// ---------- Đường dẫn cũ: chuyển hướng 301 ----------

/** Đường dẫn đúng nếu đoạn thể loại trong đường dẫn yêu cầu khác của bài (ví dụ /thanh-ngu/x/ → /tuc-ngu/x/); null nếu đã đúng. */
function cadao_duong_dan_dung( string $duong_yeu_cau, WP_Post $bai ): ?string {
	$dung = get_permalink( $bai );
	$doan = static fn( $p ) => explode( '/', trim( (string) wp_parse_url( $p, PHP_URL_PATH ), '/' ) )[0] ?? '';
	$co   = $doan( $duong_yeu_cau );
	$phai = $doan( $dung );
	return ( ( isset( CADAO_THE_LOAI[ $co ] ) || isset( CADAO_THE_LOAI_CU[ $co ] ) ) && isset( CADAO_THE_LOAI[ $phai ] ) && $co !== $phai ) ? $dung : null;
}

add_action(
	'template_redirect',
	static function () {
		$duong = (string) wp_parse_url( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
		// Trang thể loại cũ /the-loai/thanh-ngu/… → trang mục gộp
		if ( preg_match( '~^/the-loai/(' . implode( '|', array_map( 'preg_quote', array_keys( CADAO_THE_LOAI_CU ) ) ) . ')(/|$)~', $duong, $m ) ) {
			$toi = get_term_link( CADAO_THE_LOAI_CU[ $m[1] ], 'the_loai' );
			if ( ! is_wp_error( $toi ) ) {
				wp_safe_redirect( $toi, 301 );
				exit;
			}
		}
		if ( ! is_singular( CADAO_LOAI_BAI ) || is_preview() ) {
			return;
		}
		$toi = cadao_duong_dan_dung( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), get_queried_object() );
		if ( $toi ) {
			wp_safe_redirect( $toi, 301 );
			exit;
		}
	},
	1
);
