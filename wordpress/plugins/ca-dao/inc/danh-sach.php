<?php
/**
 * Điều hướng và danh sách bài (T-120, D-073). Trang Tất cả (/kho/) và trang phân loại: xếp A–Z mặc định ("Mới thêm"
 * qua ?xep=moi); lọc dạng chip theo thể loại, thể thơ, vùng, chủ đề — tham số riêng trên đường dẫn (?loai=ve&mien=…;
 * không dùng tên phân loại vì WordPress sẽ coi trang Tất cả là trang thể loại), chạy cả khi tắt JavaScript; mỗi mục
 * có 2 dòng đầu của lời, thể loại, vùng; menu không hiện thể loại chưa có bài; trang trống có lối đi tiếp.
 * A–Z theo khóa xếp bỏ dấu lưu cùng bài (_cadao_ten_xep): cùng một thứ tự trên MySQL và SQLite, "đ" sau "d".
 */

defined( 'ABSPATH' ) || exit;

/** Phân loại lọc được → tên hiện, tham số trên đường dẫn. */
const CADAO_LOC = array(
	'the_loai' => array( 'Thể loại', 'loai' ),
	'the_tho'  => array( 'Thể thơ', 'the' ),
	'vung'     => array( 'Vùng', 'mien' ),
	'chu_de'   => array( 'Chủ đề', 'chude' ),
);

/** Khóa xếp A–Z: chữ thường, bỏ dấu; "đ" xếp sau mọi chữ "d" như bảng chữ cái tiếng Việt. */
function cadao_khoa_xep( string $ten ): string {
	$ten = mb_strtolower( cadao_nfc( wp_strip_all_tags( $ten ) ) );
	return trim( cadao_bo_dau( str_replace( 'đ', 'd{', $ten ) ) );
}

add_action( 'save_post_' . CADAO_LOAI_BAI, static fn( $id, $bai ) => update_post_meta( $id, '_cadao_ten_xep', cadao_khoa_xep( $bai->post_title ) ), 10, 2 );

// Bài có từ trước: lập khóa xếp một lần mỗi phiên bản plugin
add_action(
	'init',
	static function () {
		if ( get_option( 'cadao_ten_xep_phien_ban' ) === CADAO_PHIEN_BAN || wp_installing() ) {
			return;
		}
		global $wpdb;
		foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = %s", CADAO_LOAI_BAI ) ) as $r ) {
			update_post_meta( (int) $r->ID, '_cadao_ten_xep', cadao_khoa_xep( $r->post_title ) );
		}
		update_option( 'cadao_ten_xep_phien_ban', CADAO_PHIEN_BAN, false );
	},
	40
);

/**
 * Bộ lọc đang chọn trên đường dẫn (phân loại → slug), trừ phân loại của chính trang.
 *
 * @return array<string, string>
 */
function cadao_loc_dang( string $bo = '' ): array {
	$dang = array();
	foreach ( CADAO_LOC as $tax => $c ) {
		$v = $tax === $bo ? '' : sanitize_title( wp_unslash( (string) ( $_GET[ $c[1] ] ?? '' ) ) );
		if ( '' !== $v ) {
			$dang[ $tax ] = $v;
		}
	}
	return $dang;
}

const CADAO_DS_MOI_TRANG = 30;

/** Truy vấn chính của trang Tất cả hoặc trang phân loại bài? */
function cadao_la_truy_van_ds( WP_Query $q ): bool {
	return $q->is_main_query() && ! is_admin() && ( $q->is_post_type_archive( CADAO_LOAI_BAI ) || $q->is_tax( array_keys( CADAO_LOC ) ) );
}

/** Cách xếp đang chọn: 'az' (mặc định) hoặc 'moi'. */
function cadao_xep_ds(): string {
	return 'moi' === sanitize_key( wp_unslash( $_GET['xep'] ?? '' ) ) ? 'moi' : 'az';
}

add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( ! cadao_la_truy_van_ds( $q ) ) {
			return;
		}
		$q->set( 'posts_per_page', CADAO_DS_MOI_TRANG ); // mỗi bài chỉ 2 dòng: ít trang hơn (báo cáo E2E, L-07)
		$tq = array();
		foreach ( cadao_loc_dang( $q->is_tax() ? (string) ( $q->get_queried_object()->taxonomy ?? '' ) : '' ) as $tax => $slug ) {
			$tq[] = array( 'taxonomy' => $tax, 'field' => 'slug', 'terms' => $slug );
		}
		if ( $tq ) {
			$q->set( 'tax_query', array_merge( (array) $q->get( 'tax_query' ), $tq ) );
		}
		if ( 'moi' === cadao_xep_ds() ) {
			$q->set( 'orderby', array( 'date' => 'DESC', 'ID' => 'DESC' ) );
		} else {
			// Bài chưa có khóa xếp (vừa nhập thẳng vào cơ sở dữ liệu) vẫn hiện, xếp theo tiêu đề
			$q->set( 'meta_query', array( 'relation' => 'OR', 'cadao_xep' => array( 'key' => '_cadao_ten_xep', 'compare' => 'EXISTS' ), array( 'key' => '_cadao_ten_xep', 'compare' => 'NOT EXISTS' ) ) );
			$q->set( 'orderby', array( 'cadao_xep' => 'ASC', 'title' => 'ASC', 'ID' => 'ASC' ) );
		}
	}
);

add_filter( 'post_type_archive_title', static fn( $ten, $loai ) => CADAO_LOAI_BAI === $loai ? 'Tất cả bài' : $ten, 10, 2 );

/**
 * Đường dẫn danh sách với bộ lọc $dang (phân loại → slug) và cách xếp; luôn về trang 1.
 *
 * @param array<string, string> $dang
 */
function cadao_url_ds( string $goc, array $dang, string $xep ): string {
	$tham = array();
	foreach ( array_filter( $dang ) as $tax => $slug ) {
		$tham[ CADAO_LOC[ $tax ][1] ] = $slug;
	}
	$tham += 'moi' === $xep ? array( 'xep' => 'moi' ) : array();
	return $tham ? add_query_arg( $tham, $goc ) : $goc; // slug đã an toàn cho đường dẫn
}

/**
 * Khối lọc và xếp, kèm tiêu đề "N bài" (h2) đứng trên các mục (h3).
 *
 * @param array<string, string> $dang  bộ lọc đang chọn (phân loại → slug)
 * @param string                $bo    phân loại của chính trang (trang thể loại thì không lọc lại theo thể loại)
 */
function cadao_html_loc_ds( string $goc, array $dang, string $xep, int $tong, string $bo = '' ): string {
	$nhom = '';
	foreach ( CADAO_LOC as $tax => list( $ten ) ) {
		if ( $tax === $bo || ! taxonomy_exists( $tax ) ) {
			continue;
		}
		$ds = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
		if ( is_wp_error( $ds ) || ( count( $ds ) < 2 && empty( $dang[ $tax ] ) ) ) {
			continue; // một lựa chọn thì lọc không có ích
		}
		$chip = '';
		foreach ( $ds as $t ) {
			$chon  = ( $dang[ $tax ] ?? '' ) === $t->slug;
			$moi   = array( $tax => $chon ? '' : $t->slug ) + $dang;
			$chip .= '<li><a class="cadao-chip" href="' . esc_url( cadao_url_ds( $goc, $moi, $xep ) ) . '"' . ( $chon ? ' aria-current="true"' : '' ) . '>'
				. ( $chon ? '<span aria-hidden="true">✓ </span>' : '' ) . esc_html( $t->name ) . ' <span class="cadao-chip-so">' . esc_html( number_format_i18n( $t->count ) ) . '</span>'
				. ( $chon ? '<span class="screen-reader-text"> (đang chọn, bấm để bỏ)</span>' : '' ) . '</a></li>';
		}
		$ul = '<ul class="cadao-chips" aria-label="' . esc_attr( $ten ) . '">' . $chip . '</ul>';
		// Vùng, chủ đề nhiều lựa chọn: thu gọn, mở sẵn khi đang lọc theo nhóm đó
		$nhom .= in_array( $tax, array( 'vung', 'chu_de' ), true )
			? '<details class="cadao-loc-nhom"' . ( empty( $dang[ $tax ] ) ? '' : ' open' ) . '><summary>' . esc_html( $ten ) . ' <span class="cadao-chip-so">(' . esc_html( number_format_i18n( count( $ds ) ) ) . ')</span></summary>' . $ul . '</details>'
			: '<div class="cadao-loc-nhom"><p class="cadao-loc-ten">' . esc_html( $ten ) . '</p>' . $ul . '</div>';
	}
	$xep_html = '<p class="cadao-xep">Xếp: '
		. ( 'az' === $xep ? '<strong aria-current="true">A–Z</strong>' : '<a href="' . esc_url( cadao_url_ds( $goc, $dang, 'az' ) ) . '">A–Z</a>' ) . ' · '
		. ( 'moi' === $xep ? '<strong aria-current="true">Mới thêm</strong>' : '<a href="' . esc_url( cadao_url_ds( $goc, $dang, 'moi' ) ) . '">Mới thêm</a>' ) . '</p>';
	$bo_loc = array_filter( $dang ) ? ' <a class="cadao-bo-loc" href="' . esc_url( cadao_url_ds( $goc, array(), $xep ) ) . '">Bỏ lọc</a>' : '';
	return ( $nhom ? '<nav class="cadao-loc" aria-label="Lọc danh sách bài">' . $nhom . '</nav>' : '' )
		. $xep_html . '<h2 class="cadao-ds-tieu">' . esc_html( sprintf( '%s bài', number_format_i18n( $tong ) ) ) . $bo_loc . '</h2>';
}

/** [cadao_loc_ds]: khối lọc của trang đang xem (dùng trong mẫu trang Tất cả, trang phân loại). */
add_shortcode(
	'cadao_loc_ds',
	static function () {
		global $wp_query;
		$bo   = is_tax( array_keys( CADAO_LOC ) ) ? get_queried_object()->taxonomy : '';
		$goc  = $bo ? (string) get_term_link( get_queried_object() ) : (string) get_post_type_archive_link( CADAO_LOAI_BAI );
		return cadao_html_loc_ds( $goc, cadao_loc_dang( $bo ), cadao_xep_ds(), (int) $wp_query->found_posts, $bo );
	}
);

/** Dưới tiêu đề mỗi mục trong danh sách: 2 dòng đầu của lời (bỏ dòng trùng tiêu đề), thể loại, vùng. */
function cadao_html_muc_ds( WP_Post $bai ): string {
	$dong = array_values( array_filter( explode( "\n", cadao_loi_cac_khoi_tho( $bai->post_content )[0] ?? '' ), static fn( $d ) => '' !== trim( $d ) ) );
	$chu  = static fn( $s ) => mb_strtolower( trim( preg_replace( '/[^\p{L}\p{N} ]+/u', '', wp_strip_all_tags( $s ) ) ) );
	if ( $dong && $chu( $dong[0] ) === $chu( get_the_title( $bai ) ) ) {
		array_shift( $dong );
	}
	$vung = get_the_terms( $bai, 'vung' );
	$phu  = array_merge( array( cadao_ten_the_loai( $bai ) ), $vung && ! is_wp_error( $vung ) ? wp_list_pluck( $vung, 'name' ) : array() );
	return ( $dong ? '<p class="cadao-ds-loi">' . implode( '<br>', array_map( 'esc_html', array_slice( $dong, 0, 2 ) ) ) . ( count( $dong ) > 2 ? '…' : '' ) . '</p>' : '' )
		. '<p class="cadao-ds-phu">' . esc_html( implode( ' · ', $phu ) ) . '</p>';
}

// Đang dựng danh sách bài của trang Tất cả, trang phân loại (khối Mẫu bài có lớp cadao-ds-bai)? Đánh dấu trong lúc dựng.
add_filter(
	'pre_render_block',
	static function ( $truoc, $khoi ) {
		if ( 'core/post-template' === $khoi['blockName'] && str_contains( (string) ( $khoi['attrs']['className'] ?? '' ), 'cadao-ds-bai' ) ) {
			$GLOBALS['cadao_dang_dung_ds'] = true;
		}
		return $truoc;
	},
	10,
	2
);
add_filter(
	'render_block_core/post-template',
	static function ( $html ) {
		$GLOBALS['cadao_dang_dung_ds'] = false;
		return $html;
	}
);

add_filter(
	'render_block_core/post-title',
	static function ( $html, $khoi, $the ) {
		$id = (int) ( $the->context['postId'] ?? 0 );
		if ( ! $id || empty( $GLOBALS['cadao_dang_dung_ds'] ) || CADAO_LOAI_BAI !== get_post_type( $id ) ) {
			return $html;
		}
		return $html . cadao_html_muc_ds( get_post( $id ) );
	},
	20, // sau dòng 👁 của số liệu (ưu tiên 10)
	3
);

/** Menu: liên kết tới thể loại chưa có bài thì không hiện. */
function cadao_an_the_loai_trong( string $html, array $khoi ): string {
	if ( ! preg_match( '~/the-loai/([a-z0-9-]+)/?$~', (string) ( $khoi['attrs']['url'] ?? '' ), $m ) ) {
		return $html;
	}
	$t = get_term_by( 'slug', $m[1], 'the_loai' );
	return $t && $t->count > 0 ? $html : '';
}
add_filter( 'render_block_core/navigation-link', 'cadao_an_the_loai_trong', 10, 2 );

/** [cadao_ds_trong]: trang danh sách không có bài — lối sang Tìm và các thể loại đang có bài. */
add_shortcode(
	'cadao_ds_trong',
	static function () {
		$co = get_terms( array( 'taxonomy' => 'the_loai', 'hide_empty' => true ) );
		$li = '';
		foreach ( is_wp_error( $co ) ? array() : $co as $t ) {
			$li .= '<li><a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a> (' . esc_html( number_format_i18n( $t->count ) ) . ' bài)</li>';
		}
		return '<div class="cadao-ds-trong"><p><strong>Chưa có bài nào ở đây.</strong> Kho đang được bổ sung dần. Bạn có thể <a href="' . esc_url( home_url( '/tim/' ) ) . '">tìm theo vài chữ trong câu</a>'
			. ( $li ? ', hoặc xem:</p><ul>' . $li . '</ul>' : '.</p>' ) . '</div>';
	}
);

/**
 * [cadao_404]: trang không tìm thấy (mẫu 404 của theme; báo cáo E2E, L-06) — ô tìm điền sẵn chữ lấy từ đường dẫn
 * (đường dẫn bài là lời bỏ dấu: /ca-dao/cong-cha-nhu-nui/ → "cong cha nhu nui"), lối sang Tất cả bài, Từ điển.
 */
add_shortcode(
	'cadao_404',
	static function () {
		$duong = (string) wp_parse_url( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
		$cuoi  = basename( untrailingslashit( $duong ) );
		$goi_y = preg_replace( '/-\d+$/', '', sanitize_title( rawurldecode( $cuoi ) ) ); // bỏ đuôi -2
		$goi_y = mb_substr( trim( str_replace( '-', ' ', (string) $goi_y ) ), 0, 80 );
		return '<p>Câu này có thể đã đổi đường dẫn. Thử tìm vài chữ trong câu, có dấu hay không dấu đều được.</p>'
			. '<form class="cadao-tim" role="search" method="get" action="' . esc_url( home_url( '/tim/' ) ) . '">'
			. '<label for="cadao-404-o" class="screen-reader-text">Tìm câu ca dao, tục ngữ</label>'
			. '<input id="cadao-404-o" type="search" name="q" value="' . esc_attr( $goi_y ) . '" placeholder="Gõ vài chữ trong câu" enterkeyhint="search">'
			. '<button type="submit">Tìm</button></form>'
			. '<ul class="cadao-404-loi">'
			. '<li><a href="' . esc_url( home_url( '/kho/' ) ) . '">Tất cả bài</a></li>'
			. '<li><a href="' . esc_url( home_url( '/tu-dien/' ) ) . '">Từ điển</a></li>'
			. '<li><a href="' . esc_url( home_url( '/' ) ) . '">Trang chủ</a></li></ul>';
	}
);
