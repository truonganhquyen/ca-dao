<?php
/**
 * Từ điển chú giải (T-044, docs/25 mục 4.3): mỗi chú thích (cùng từ, cùng lời giải — tức cùng một chú thích của nguồn
 * dùng ở nhiều bài) là một mục từ ở /tu-dien/<từ>/, liệt kê mọi bài có từ này. Mục từ lấy từ danh sách "Chú thích"
 * trong bài (chép từ nguồn khi nguồn bật "Chép chú thích", D-055; hoặc người biên soạn viết), kèm đúng dòng "Nguồn: …"
 * của danh sách đó (D-057). Trang /tu-dien/ liệt kê theo chữ cái, lọc theo vùng / từ cổ / Hán Việt theo lời giải.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TU_DIEN_LOC = array(
	'nam-bo'   => array( 'Nam Bộ', '/Nam Bộ|miền Nam|Nam Kỳ|Lục tỉnh/u' ),
	'trung-bo' => array( 'Trung Bộ', '/Trung Bộ|miền Trung|Nghệ|Huế|Quảng (Nam|Ngãi|Bình|Trị)|Bình Định|Thanh Hóa|Thanh Hoá/u' ),
	'bac-bo'   => array( 'Bắc Bộ', '/Bắc Bộ|miền Bắc|Bắc Kỳ/u' ),
	'tu-co'    => array( 'Từ cổ', '/từ cổ|tiếng cổ|nghĩa cổ|ngày xưa|thời xưa/iu' ),
	'han-viet' => array( 'Hán Việt', '/Hán Việt|chữ Hán|gốc Hán/u' ),
);

add_action(
	'init',
	static function () {
		register_taxonomy(
			'tu_kho',
			CADAO_LOAI_BAI,
			array(
				'labels'       => array( 'name' => 'Từ điển', 'singular_name' => 'Mục từ', 'edit_item' => 'Sửa mục từ', 'search_items' => 'Tìm mục từ' ),
				'public'       => true,
				'show_in_rest' => true,
				'hierarchical' => false,
				'meta_box_cb'  => false, // máy gắn theo danh sách chú thích của bài
				'rewrite'      => array( 'slug' => 'tu-dien' ),
			)
		);
	},
	11
);

/**
 * Các chú thích trong nội dung một bài: mỗi mục { tu, noi_dung, nguon_ten, nguon_url } — nguồn là dòng
 * "Nguồn: …" ngay sau danh sách chứa mục đó (trống nếu người biên soạn tự viết).
 *
 * @return array<int, array{tu: string, noi_dung: string, nguon_ten: string, nguon_url: string}>
 */
function cadao_chu_thich_cua_bai( string $noi_dung ): array {
	$ra   = array();
	$khoi = array_values( array_filter( parse_blocks( $noi_dung ), static fn( $k ) => null !== $k['blockName'] ) );
	foreach ( $khoi as $i => $k ) {
		if ( 'core/list' !== $k['blockName'] || ! str_contains( (string) ( $k['attrs']['className'] ?? '' ), 'cadao-chu-thich' ) ) {
			continue;
		}
		$nguon = array( '', '' );
		$sau   = $khoi[ $i + 1 ] ?? null;
		if ( $sau && str_contains( (string) ( $sau['attrs']['className'] ?? '' ), 'cadao-chu-thich-nguon' ) && preg_match( '~<a href="([^"]+)"[^>]*>(.*?)<span~s', $sau['innerHTML'], $a ) ) {
			$nguon = array( trim( cadao_giai_ma( wp_strip_all_tags( $a[2] ) ) ), cadao_giai_ma( $a[1] ) );
		}
		$html = $k['innerHTML'] . implode( '', array_column( $k['innerBlocks'], 'innerHTML' ) );
		preg_match_all( '~<li\b[^>]*>\s*<strong>(.*?)</strong>\s*:\s*(.*?)</li>~s', $html, $li, PREG_SET_ORDER );
		foreach ( $li as $o ) {
			$tu = trim( cadao_giai_ma( wp_strip_all_tags( $o[1] ) ) );
			$nd = trim( cadao_giai_ma( wp_strip_all_tags( $o[2] ) ) );
			if ( '' !== $tu && '' !== $nd ) {
				$ra[] = array( 'tu' => $tu, 'noi_dung' => $nd, 'nguon_ten' => $nguon[0], 'nguon_url' => $nguon[1] );
			}
		}
	}
	return $ra;
}

/** Khóa của một mục từ: cùng từ (không phân biệt hoa thường) và cùng lời giải. */
function cadao_khoa_muc_tu( string $tu, string $noi_dung ): string {
	return md5( mb_strtolower( cadao_nfc( trim( $tu ) ) ) . '|' . cadao_nfc( trim( $noi_dung ) ) );
}

/** Mục từ (term) theo khóa; tạo mới nếu chưa có. */
function cadao_muc_tu( array $ct, bool $tao = true ): ?WP_Term {
	$khoa = cadao_khoa_muc_tu( $ct['tu'], $ct['noi_dung'] );
	$ds   = get_terms( array( 'taxonomy' => 'tu_kho', 'hide_empty' => false, 'number' => 1, 'meta_key' => '_cadao_khoa', 'meta_value' => $khoa ) );
	if ( $ds && ! is_wp_error( $ds ) ) {
		return $ds[0];
	}
	if ( ! $tao ) {
		return null;
	}
	$slug = sanitize_title( $ct['tu'] );
	$mau  = $slug;
	for ( $n = 2; term_exists( $slug, 'tu_kho' ); $n++ ) {
		$slug = "$mau-$n"; // cùng từ, lời giải khác: mục từ khác
	}
	$moi = wp_insert_term( $ct['tu'], 'tu_kho', array( 'slug' => $slug, 'description' => $ct['noi_dung'] ) );
	if ( is_wp_error( $moi ) ) {
		return null;
	}
	add_term_meta( $moi['term_id'], '_cadao_khoa', $khoa, true );
	add_term_meta( $moi['term_id'], '_cadao_nguon_ten', $ct['nguon_ten'], true );
	add_term_meta( $moi['term_id'], '_cadao_nguon_url', $ct['nguon_url'], true );
	return get_term( $moi['term_id'], 'tu_kho' );
}

/** Gắn bài vào các mục từ theo danh sách chú thích của bài. */
function cadao_gan_tu_dien( int $id ): void {
	$bai = get_post( $id );
	if ( ! $bai || CADAO_LOAI_BAI !== $bai->post_type ) {
		return;
	}
	$ids = array();
	foreach ( cadao_chu_thich_cua_bai( $bai->post_content ) as $ct ) {
		$t = cadao_muc_tu( $ct );
		if ( $t ) {
			$ids[] = (int) $t->term_id;
		}
	}
	wp_set_object_terms( $id, array_values( array_unique( $ids ) ), 'tu_kho' );
}

add_action(
	'save_post_' . CADAO_LOAI_BAI,
	static function ( $id ) {
		if ( ! wp_is_post_revision( $id ) && ! wp_is_post_autosave( $id ) ) {
			cadao_gan_tu_dien( (int) $id );
		}
	},
	26
);

// Bài đã có: gắn mục từ một lần (làm dần, mỗi lượt 200 bài)
add_action(
	'cadao_thu_thap',
	static function () {
		global $wpdb;
		$sau = (int) get_option( 'cadao_tu_dien_sau', 0 );
		if ( $sau < 0 ) {
			return;
		}
		$ds = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash','auto-draft','inherit') AND ID > %d ORDER BY ID LIMIT 200", CADAO_LOAI_BAI, $sau ) );
		foreach ( $ds as $id ) {
			cadao_gan_tu_dien( (int) $id );
		}
		update_option( 'cadao_tu_dien_sau', $ds ? (int) end( $ds ) : -1, false );
	},
	8
);

/** Dòng "Nguồn: …" của một mục từ (trống nếu người biên soạn tự viết). */
function cadao_nguon_muc_tu( int $term_id ): string {
	$ten = (string) get_term_meta( $term_id, '_cadao_nguon_ten', true );
	$url = (string) get_term_meta( $term_id, '_cadao_nguon_url', true );
	if ( '' === $ten ) {
		return '';
	}
	return '<p class="cadao-chu-thich-nguon">Nguồn: ' . ( $url ? '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $ten ) . ' <span aria-hidden="true">↗</span></a>' : esc_html( $ten ) ) . '</p>';
}

/** Trang mục từ: lời giải, nguồn, rồi danh sách bài (theme: taxonomy.html). */
add_filter(
	'term_description',
	static function ( $mo_ta, $term_id, $taxonomy ) {
		if ( 'tu_kho' !== $taxonomy || is_admin() ) {
			return $mo_ta;
		}
		$so = (int) ( get_term( $term_id, 'tu_kho' )->count ?? 0 );
		return $mo_ta . cadao_nguon_muc_tu( (int) $term_id ) . apply_filters( 'cadao_muc_tu_them', '', (int) $term_id )
			. '<h2 class="cadao-tu-dien-bai">' . esc_html( sprintf( '%d bài có từ này', $so ) ) . '</h2>';
	},
	10,
	3
);

// Mục từ chép từ nguồn: không có giá trị riêng → noindex (như bài chưa có Hiểu nhanh, docs/24 mục 5)
add_filter(
	'wp_robots',
	static function ( $robots ) {
		if ( is_tax( 'tu_kho' ) && '' !== (string) get_term_meta( get_queried_object_id(), '_cadao_nguon_ten', true ) ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
);

/** Nhóm theo chữ cái đầu (chữ Việt: Đ riêng, bỏ dấu thanh). */
function cadao_chu_cai_dau( string $tu ): string {
	$c = mb_strtoupper( mb_substr( trim( $tu ), 0, 1 ) );
	return 'Đ' === $c ? 'Đ' : ( preg_match( '/\p{L}/u', $c ) ? mb_strtoupper( mb_substr( cadao_bo_dau( $c ), 0, 1 ) ) : '#' );
}

/** [cadao_tu_dien]: trang /tu-dien/ — chữ cái, bộ lọc, các mục từ đang được dùng. */
add_shortcode(
	'cadao_tu_dien',
	static function () {
		$loc  = sanitize_key( wp_unslash( $_GET['loc'] ?? '' ) );
		$chu  = sanitize_text_field( wp_unslash( $_GET['chu'] ?? '' ) );
		$ds   = get_terms( array( 'taxonomy' => 'tu_kho', 'hide_empty' => true, 'orderby' => 'name', 'number' => 0 ) );
		$ds   = is_wp_error( $ds ) ? array() : $ds;
		if ( isset( CADAO_TU_DIEN_LOC[ $loc ] ) ) {
			$ds = array_values( array_filter( $ds, static fn( $t ) => preg_match( CADAO_TU_DIEN_LOC[ $loc ][1], $t->description ) ) );
		}
		$theo = array();
		foreach ( $ds as $t ) {
			$theo[ cadao_chu_cai_dau( $t->name ) ][] = $t;
		}
		uksort( $theo, static fn( $a, $b ) => strcmp( str_replace( 'Đ', 'D~', $a ), str_replace( 'Đ', 'D~', $b ) ) );
		if ( '' === $chu || ! isset( $theo[ $chu ] ) ) {
			$chu = (string) array_key_first( $theo );
		}
		$url = static fn( $them ) => esc_url( add_query_arg( $them, get_permalink() ) );
		$ra  = '<p class="cadao-tu-dien-tom">' . esc_html( sprintf( '%d mục từ đang được dùng trong các bài.', count( $ds ) ) ) . '</p>';
		$ra .= '<nav class="cadao-tu-dien-loc" aria-label="Lọc mục từ"><a href="' . $url( array( 'loc' => false, 'chu' => false ) ) . '"' . ( '' === $loc || ! isset( CADAO_TU_DIEN_LOC[ $loc ] ) ? ' aria-current="page"' : '' ) . '>Tất cả</a>';
		foreach ( CADAO_TU_DIEN_LOC as $k => [ $ten ] ) {
			$ra .= ' <a href="' . $url( array( 'loc' => $k, 'chu' => false ) ) . '"' . ( $loc === $k ? ' aria-current="page"' : '' ) . '>' . esc_html( $ten ) . '</a>';
		}
		$ra .= '</nav>';
		if ( ! $theo ) {
			return $ra . '<p>Chưa có mục từ nào.</p>';
		}
		$ra .= '<nav class="cadao-tu-dien-chu" aria-label="Chữ cái">';
		foreach ( array_keys( $theo ) as $c ) {
			$ra .= '<a href="' . $url( array( 'chu' => $c ) ) . '"' . ( $c === $chu ? ' aria-current="page"' : '' ) . '>' . esc_html( $c ) . '</a>';
		}
		$ra .= '</nav><h2>' . esc_html( $chu ) . '</h2><ul class="cadao-tu-dien-ds">';
		foreach ( $theo[ $chu ] as $t ) {
			$ra .= '<li><a href="' . esc_url( get_term_link( $t ) ) . '"><strong>' . esc_html( $t->name ) . '</strong></a> — ' . esc_html( wp_trim_words( $t->description, 18 ) ) . ' <span class="cadao-tu-dien-so">(' . (int) $t->count . ' bài)</span></li>';
		}
		return $ra . '</ul>';
	}
);

/** Trang "Từ điển" (/tu-dien/) tạo một lần; người sáng lập sửa lời giới thiệu được. */
add_action(
	'init',
	static function () {
		if ( get_option( 'cadao_trang_tu_dien' ) || wp_installing() ) {
			return;
		}
		$co = get_page_by_path( 'tu-dien' );
		$id = $co ? $co->ID : wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Từ điển',
				'post_name'    => 'tu-dien',
				'post_content' => "<!-- wp:paragraph -->\n<p>Nghĩa các từ khó, từ địa phương, địa danh trong ca dao, tục ngữ — chạm vào một từ để xem các bài có từ ấy.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[cadao_tu_dien]\n<!-- /wp:shortcode -->",
			)
		);
		update_option( 'cadao_trang_tu_dien', (int) $id, false );
	},
	30
);
