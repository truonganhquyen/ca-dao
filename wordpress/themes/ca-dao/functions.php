<?php
/**
 * Theme con "Kho tàng Ca dao" (T-072, docs/20): màu, phông, trang bài. Dữ liệu và chức năng nằm ở plugin ca-dao;
 * theme chỉ lo cách hiển thị.
 */

defined( 'ABSPATH' ) || exit;

/** Lục bát: thụt lề câu lục so với câu bát (docs/20 mục 1.2). Đổi sau khi hỏi người thử (Q-006, Mốc B). */
const CADAO_LUC_BAT_THUT_LE = true;

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'ca-dao', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
	}
);
add_action(
	'after_setup_theme',
	static function () {
		add_editor_style( 'style.css' );
	}
);

/**
 * Bài lục bát? Đa số cặp dòng (lẻ, chẵn) có 6 rồi 8 âm tiết. Âm tiết đếm theo khoảng trắng, như site cũ.
 *
 * @param string[] $dong các dòng (chữ thường, không thẻ)
 */
function cadao_la_luc_bat( array $dong ): bool {
	$dong = array_values( array_filter( $dong, static fn( $d ) => '' !== trim( $d ) ) );
	if ( count( $dong ) < 2 ) {
		return false;
	}
	$dem   = static fn( $d ) => count( preg_split( '/\s+/u', trim( $d ), -1, PREG_SPLIT_NO_EMPTY ) );
	$cap   = intdiv( count( $dong ), 2 );
	$trung = 0;
	for ( $i = 0; $i < $cap; $i++ ) {
		$trung += ( 6 === $dem( $dong[ 2 * $i ] ) && 8 === $dem( $dong[ 2 * $i + 1 ] ) ) ? 1 : 0;
	}
	return $trung * 4 >= $cap * 3; // ≥ 3/4 số cặp
}

/**
 * Khối Thơ ngoài trang: mỗi dòng một <span class="cadao-dong">; bài lục bát thì câu lục mang thêm "cadao-luc".
 * Chỉ đổi cách trình bày; chữ giữ nguyên (đã thoát HTML khi lưu).
 */
add_filter(
	'render_block_core/verse',
	static function ( $html ) {
		if ( ! preg_match( '~^(\s*<pre\b[^>]*>)(.*)(</pre>\s*)$~s', $html, $m ) ) {
			return $html;
		}
		$dong = preg_split( '~<br\s*/?>|\n~i', $m[2] );
		$luc  = CADAO_LUC_BAT_THUT_LE && cadao_la_luc_bat( array_map( static fn( $d ) => wp_strip_all_tags( html_entity_decode( $d ) ), $dong ) );
		$ra   = '';
		foreach ( $dong as $i => $d ) {
			$ra .= '<span class="cadao-dong' . ( $luc && 0 === $i % 2 ? ' cadao-luc' : '' ) . '">' . ( '' === trim( $d ) ? '&nbsp;' : $d ) . '</span>';
		}
		return $m[1] . $ra . $m[3];
	}
);

/** "Hiểu nhanh" (đoạn trích do dự án viết) ngay dưới lời, trước dị bản và chú thích (docs/20 mục 4.1, lớp 1). */
add_filter(
	'the_content',
	static function ( $noi_dung ) {
		if ( ! is_singular( 'cdtn' ) || get_the_ID() !== get_queried_object_id() || doing_filter( 'get_the_excerpt' ) || ! has_excerpt() ) {
			return $noi_dung;
		}
		$khoi = '<section class="cadao-hieu-nhanh" aria-labelledby="cadao-hieu-nhanh"><h2 id="cadao-hieu-nhanh">Hiểu nhanh</h2>'
			. wpautop( wp_kses_post( get_post()->post_excerpt ) ) . '</section>';
		$vt   = strpos( $noi_dung, '</pre>' );
		return false === $vt ? $khoi . $noi_dung : substr_replace( $noi_dung, '</pre>' . $khoi, $vt, strlen( '</pre>' ) );
	},
	20
);
