<?php
/**
 * SEO cơ bản (T-050, D-073): meta description, Open Graph (ảnh chia sẻ — anh-chia-se.php), JSON-LD, sitemap chỉ gồm
 * trang được lập chỉ mục, noindex cho trang mỏng. Không đổi blog_public: mở cho Google là quyết định của người sáng
 * lập (H5, T-053); WordPress tự tắt sitemap khi site còn chặn công cụ tìm kiếm.
 * Bài chỉ được lập chỉ mục khi có giá trị riêng (cadao_co_gia_tri_rieng: Hiểu nhanh, hoặc bài phân tích đã duyệt).
 */

defined( 'ABSPATH' ) || exit;

/** Phân loại có trang danh sách bài; dưới 3 bài thì noindex, không vào sitemap. */
const CADAO_PHAN_LOAI_DS = array( 'the_loai', 'the_tho', 'vung', 'chu_de', 'tinh_huong' );
const CADAO_IT_NHAT_BAI  = 3;

/** Cắt mô tả ≤ 160 ký tự ở ranh giới từ. */
function cadao_cat_mo_ta( string $s, int $toi_da = 160 ): string {
	$s = trim( (string) preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $s ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
	if ( mb_strlen( $s ) <= $toi_da ) {
		return $s;
	}
	$s = mb_substr( $s, 0, $toi_da - 1 );
	return rtrim( (string) preg_replace( '/\s+\S*$/u', '', $s ), ' ,.;:–—' ) . '…';
}

/** Mô tả của một bài: Hiểu nhanh; chưa có thì 2 dòng đầu, thể loại, có chú thích / dị bản. */
function cadao_mo_ta_bai( WP_Post $bai ): string {
	if ( '' !== trim( $bai->post_excerpt ) ) {
		return cadao_cat_mo_ta( $bai->post_excerpt );
	}
	$khoi = cadao_loi_cac_khoi_tho( $bai->post_content );
	$dong = array_values( array_filter( explode( "\n", $khoi[0] ?? '' ), static fn( $d ) => '' !== trim( $d ) ) );
	$them = array();
	if ( preg_match( '/class="[^"]*\bcadao-chu-thich[" ]/', $bai->post_content ) ) {
		$them[] = 'Có chú thích từ khó.';
	}
	if ( count( $khoi ) > 1 ) {
		$them[] = sprintf( 'Có %d dị bản.', count( $khoi ) - 1 );
	}
	return cadao_cat_mo_ta( implode( ' / ', array_slice( $dong, 0, 2 ) ) . ( count( $dong ) > 2 ? '…' : '' ) . ' — ' . cadao_ten_the_loai( $bai ) . '. ' . implode( ' ', $them ) );
}

/** Mô tả của trang đang xem ('' nếu không có gì đáng ghi). */
function cadao_mo_ta_trang(): string {
	if ( is_singular( CADAO_LOAI_BAI ) ) {
		return cadao_mo_ta_bai( get_queried_object() );
	}
	if ( is_front_page() ) {
		return 'Kho tàng ca dao, tục ngữ Việt Nam: lời giữ nguyên văn, chú thích từ khó, dị bản, cách trích dẫn. Phi lợi nhuận, không quảng cáo, đọc tốt trên điện thoại.';
	}
	if ( is_post_type_archive( CADAO_LOAI_BAI ) ) {
		return sprintf( 'Tất cả %s bài ca dao, tục ngữ, vè, đồng dao, câu đố — xếp A–Z, lọc theo thể loại, thể thơ, vùng, chủ đề.', number_format_i18n( (int) wp_count_posts( CADAO_LOAI_BAI )->publish ) );
	}
	if ( is_tax() || is_category() || is_tag() ) {
		$t   = get_queried_object();
		$dau = trim( (string) preg_split( '/(?<=[.!?])\s/u', trim( wp_strip_all_tags( (string) $t->description ) ), 2 )[0] );
		return cadao_cat_mo_ta( '' !== $dau ? $dau : sprintf( '%s — %s bài: lời giữ nguyên văn, chú thích từ khó, dị bản.', $t->name, number_format_i18n( (int) $t->count ) ) );
	}
	if ( is_singular() ) {
		$p = get_queried_object();
		return cadao_cat_mo_ta( '' !== trim( $p->post_excerpt ) ? $p->post_excerpt : wp_trim_words( strip_shortcodes( $p->post_content ), 40, '' ) );
	}
	return '';
}

/** Thẻ meta description, Open Graph, Twitter (Zalo, Facebook đọc og:*). */
function cadao_the_chia_se(): string {
	$mo_ta = cadao_mo_ta_trang();
	$bai   = is_singular( CADAO_LOAI_BAI ) ? get_queried_object() : null;
	$anh   = $bai && function_exists( 'cadao_anh_chia_se' ) ? cadao_anh_chia_se( $bai ) : null;
	$anh   = $anh ?? ( function_exists( 'cadao_anh_chung' ) ? cadao_anh_chung() : null );
	$ten   = $bai ? cadao_giai_ma( wp_strip_all_tags( get_the_title( $bai ) ) ) : wp_get_document_title();
	$url   = is_singular() ? (string) get_permalink( get_queried_object() ) : ( is_tax() || is_category() || is_tag() ? (string) get_term_link( get_queried_object() ) : ( is_post_type_archive( CADAO_LOAI_BAI ) ? (string) get_post_type_archive_link( CADAO_LOAI_BAI ) : home_url( '/' ) ) );
	$the   = array(
		array( 'property', 'og:type', $bai ? 'article' : 'website' ),
		array( 'property', 'og:site_name', get_bloginfo( 'name' ) ),
		array( 'property', 'og:locale', 'vi_VN' ),
		array( 'property', 'og:title', $ten ),
		array( 'property', 'og:url', $url ),
	);
	if ( '' !== $mo_ta ) {
		array_unshift( $the, array( 'name', 'description', $mo_ta ) );
		$the[] = array( 'property', 'og:description', $mo_ta );
	}
	if ( $anh ) {
		$the[] = array( 'property', 'og:image', $anh['url'] );
		$the[] = array( 'property', 'og:image:width', (string) $anh['rong'] );
		$the[] = array( 'property', 'og:image:height', (string) $anh['cao'] );
		$the[] = array( 'property', 'og:image:alt', $bai ? 'Lời: ' . cadao_cat_mo_ta( implode( ' / ', array_slice( array_filter( explode( "\n", cadao_loi_cua_bai( $bai ) ) ), 0, 2 ) ), 120 ) : get_bloginfo( 'name' ) );
		$the[] = array( 'name', 'twitter:card', 'summary_large_image' );
	}
	return implode( "\n", array_map( static fn( $x ) => sprintf( '<meta %s="%s" content="%s">', $x[0], esc_attr( $x[1] ), esc_attr( $x[2] ) ), $the ) ) . "\n";
}

/** Dữ liệu có cấu trúc (schema.org) của trang đang xem. */
function cadao_json_ld(): array {
	$trang_chu = array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => home_url( '/' ) );
	$vet       = static function ( array $cap ) use ( $trang_chu ) {
		$ds = array( $trang_chu );
		foreach ( array_values( $cap ) as $i => list( $ten, $url ) ) {
			$ds[] = array( '@type' => 'ListItem', 'position' => $i + 2, 'name' => $ten ) + ( $url ? array( 'item' => $url ) : array() );
		}
		return array( '@type' => 'BreadcrumbList', 'itemListElement' => $ds );
	};
	$site = array( '@type' => 'WebSite', '@id' => home_url( '/#site' ), 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ), 'inLanguage' => 'vi' );
	$ra   = array();
	if ( is_front_page() ) {
		$ra[] = $site + array(
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array( '@type' => 'EntryPoint', 'urlTemplate' => home_url( '/tim/' ) . '?q={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);
	} elseif ( is_singular( CADAO_LOAI_BAI ) ) {
		$bai = get_queried_object();
		$the = get_the_terms( $bai, 'the_loai' );
		$cd  = get_the_terms( $bai, 'chu_de' );
		$tp  = array(
			'@type'        => 'CreativeWork',
			'name'         => cadao_giai_ma( wp_strip_all_tags( get_the_title( $bai ) ) ),
			'url'          => get_permalink( $bai ),
			'genre'        => cadao_ten_the_loai( $bai ),
			'inLanguage'   => 'vi',
			'text'         => cadao_loi_cua_bai( $bai ),
			'isPartOf'     => array( '@id' => home_url( '/#site' ) ),
			'dateModified' => get_post_modified_time( 'c', true, $bai ),
		);
		if ( '' !== trim( $bai->post_excerpt ) ) {
			$tp['abstract'] = wp_strip_all_tags( $bai->post_excerpt );
		}
		if ( $cd && ! is_wp_error( $cd ) ) {
			$tp['keywords'] = implode( ', ', wp_list_pluck( $cd, 'name' ) );
		}
		$mk = function_exists( 'cadao_muc_kiem' ) ? cadao_muc_kiem( $bai->ID ) : array( 'da_doi_chieu' => false );
		if ( $mk['da_doi_chieu'] ) { // chỉ nguồn người biên soạn đã tự đối chiếu (D-072)
			$tp['isBasedOn'] = array( '@type' => 'CreativeWork', 'name' => $mk['ten'] ) + ( '' !== $mk['url'] ? array( 'url' => $mk['url'] ) : array() );
		}
		$ra[] = $tp;
		$ra[] = $vet( array( $the && ! is_wp_error( $the ) ? array( $the[0]->name, get_term_link( $the[0] ) ) : array( 'Tất cả bài', get_post_type_archive_link( CADAO_LOAI_BAI ) ), array( $tp['name'], '' ) ) );
	} elseif ( is_tax( 'tu_kho' ) ) {
		$t    = get_queried_object();
		$chep = get_term_meta( $t->term_id, '_cadao_chep', true ) || '' !== (string) get_term_meta( $t->term_id, '_cadao_nguon_ten', true );
		$tu   = get_page_by_path( 'tu-dien' );
		$ra[] = array( '@type' => 'DefinedTerm', 'name' => $t->name, 'url' => get_term_link( $t ), 'inDefinedTermSet' => $tu ? get_permalink( $tu ) : home_url( '/tu-dien/' ) )
			+ ( $chep || '' === trim( $t->description ) ? array() : array( 'description' => cadao_cat_mo_ta( $t->description, 300 ) ) ); // lời giải chép không đưa vào
		$ra[] = $vet( array( array( 'Từ điển', $tu ? get_permalink( $tu ) : home_url( '/tu-dien/' ) ), array( $t->name, '' ) ) );
	} elseif ( is_tax( CADAO_PHAN_LOAI_DS ) ) {
		$t    = get_queried_object();
		$ra[] = array( '@type' => 'CollectionPage', 'name' => $t->name, 'url' => get_term_link( $t ), 'isPartOf' => array( '@id' => home_url( '/#site' ) ) ) + ( '' !== cadao_mo_ta_trang() ? array( 'description' => cadao_mo_ta_trang() ) : array() );
		$ra[] = $vet( array( array( 'Tất cả bài', get_post_type_archive_link( CADAO_LOAI_BAI ) ), array( $t->name, '' ) ) );
	} elseif ( is_post_type_archive( CADAO_LOAI_BAI ) ) {
		$ra[] = array( '@type' => 'CollectionPage', 'name' => 'Tất cả bài', 'url' => get_post_type_archive_link( CADAO_LOAI_BAI ), 'isPartOf' => array( '@id' => home_url( '/#site' ) ), 'description' => cadao_mo_ta_trang() );
		$ra[] = $vet( array( array( 'Tất cả bài', '' ) ) );
	}
	if ( $ra && ! is_front_page() ) {
		$ra[] = $site;
	}
	return $ra ? array( '@context' => 'https://schema.org', '@graph' => $ra ) : array();
}

add_action(
	'wp_head',
	static function () {
		echo cadao_the_chia_se(); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong hàm
		$ld = cadao_json_ld();
		if ( $ld ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	},
	3
);

// ---------- Canonical cho trang không phải bài (T-127): trang chủ, Tất cả, trang phân loại ----------

/**
 * Địa chỉ canonical của trang chủ và trang danh sách (bài, trang tĩnh: WordPress tự in). Trang lọc (?loai=, ?mien=…),
 * cách xếp (?xep=) trỏ về danh sách không lọc; trang 2, 3… trỏ về chính nó (không có bộ lọc).
 */
function cadao_canonical(): string {
	if ( is_singular() || is_404() ) {
		return ''; // bài, trang tĩnh (kể cả trang chủ tĩnh): WordPress đã in rel_canonical
	}
	if ( is_front_page() && ! is_paged() ) {
		return home_url( '/' );
	}
	if ( is_post_type_archive( CADAO_LOAI_BAI ) ) {
		$goc = get_post_type_archive_link( CADAO_LOAI_BAI );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$goc = get_term_link( get_queried_object() );
	} else {
		return '';
	}
	if ( ! $goc || is_wp_error( $goc ) ) {
		return '';
	}
	$loc   = ( function_exists( 'cadao_loc_dang' ) && cadao_loc_dang() ) || isset( $_GET['xep'] );
	$trang = (int) get_query_var( 'paged' );
	return ( $trang > 1 && ! $loc ) ? trailingslashit( $goc ) . user_trailingslashit( 'page/' . $trang, 'paged' ) : $goc;
}

add_action(
	'wp_head',
	static function () {
		$url = cadao_canonical();
		if ( '' !== $url ) {
			echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
		}
	},
	4
);

// ---------- Không lập chỉ mục: trang phân loại mỏng, danh sách đã lọc, trang Tìm có câu tìm ----------
add_filter(
	'wp_robots',
	static function ( $robots ) {
		$mong = is_tax( CADAO_PHAN_LOAI_DS ) && (int) get_queried_object()->count < CADAO_IT_NHAT_BAI;
		$loc  = ( is_post_type_archive( CADAO_LOAI_BAI ) || is_tax( CADAO_PHAN_LOAI_DS ) ) && ( ( function_exists( 'cadao_loc_dang' ) && cadao_loc_dang() ) || isset( $_GET['xep'] ) );
		$tim  = is_page( 'tim' ) && '' !== trim( (string) wp_unslash( $_GET['q'] ?? '' ) );
		if ( $mong || $loc || $tim ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}
);

// ---------- Sitemap: chỉ trang được lập chỉ mục ----------

/** Mã các bài có giá trị riêng (Hiểu nhanh, hoặc bài phân tích đã duyệt) — đúng quy tắc noindex. */
function cadao_bai_co_gia_tri_rieng(): array {
	global $wpdb;
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND post_excerpt <> ''", CADAO_LOAI_BAI ) );
	if ( defined( 'CADAO_LOAI_PHAN_TICH' ) ) {
		$ids = array_merge( $ids, $wpdb->get_col( $wpdb->prepare( "SELECT m.meta_value FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id WHERE m.meta_key = '_cadao_bai' AND p.post_type = %s AND p.post_status = 'publish'", CADAO_LOAI_PHAN_TICH ) ) );
	}
	$ids = array_unique( array_map( 'intval', $ids ) );
	return array_values( array_filter( $ids, static fn( $id ) => ( $p = get_post( $id ) ) && 'publish' === $p->post_status && CADAO_LOAI_BAI === $p->post_type && cadao_co_gia_tri_rieng( $p ) ) );
}

add_filter( 'wp_sitemaps_add_provider', static fn( $nha, $ten ) => 'users' === $ten ? false : $nha, 10, 2 );
add_filter(
	'wp_sitemaps_post_types',
	static function ( $ds ) {
		unset( $ds['post'], $ds['attachment'] );
		if ( defined( 'CADAO_LOAI_GOP_Y' ) ) {
			unset( $ds[ CADAO_LOAI_GOP_Y ] );
		}
		return $ds;
	}
);
add_filter(
	'wp_sitemaps_posts_query_args',
	static function ( $args, $loai ) {
		if ( CADAO_LOAI_BAI === $loai ) {
			$args['post__in'] = cadao_bai_co_gia_tri_rieng() ?: array( 0 );
		} elseif ( 'page' === $loai ) {
			$args['post__not_in'] = array_filter( array( (int) get_option( 'cadao_trang_da_luu' ), (int) get_option( 'cadao_trang_viet_phan_tich' ) ) );
		}
		return $args;
	},
	10,
	2
);
add_filter(
	'wp_sitemaps_taxonomies',
	static fn( $ds ) => array_intersect_key( $ds, array_flip( array_merge( CADAO_PHAN_LOAI_DS, array( 'tu_kho', 'tro_choi' ) ) ) )
);
add_filter(
	'wp_sitemaps_taxonomies_query_args',
	static function ( $args, $tax ) {
		if ( in_array( $tax, CADAO_PHAN_LOAI_DS, true ) ) {
			$du = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true, 'fields' => 'id=>count' ) );
			$args['include'] = array_keys( array_filter( is_wp_error( $du ) ? array() : $du, static fn( $so ) => $so >= CADAO_IT_NHAT_BAI ) ) ?: array( 0 );
		} elseif ( 'tu_kho' === $tax ) { // mục từ có lời giải chép từ nguồn: noindex (tu-dien.php)
			$args['meta_query'] = array(
				array( 'key' => '_cadao_chep', 'compare' => 'NOT EXISTS' ),
				array(
					'relation' => 'OR',
					array( 'key' => '_cadao_nguon_ten', 'compare' => 'NOT EXISTS' ),
					array( 'key' => '_cadao_nguon_ten', 'value' => '' ),
				),
			);
		}
		return $args;
	},
	10,
	2
);
