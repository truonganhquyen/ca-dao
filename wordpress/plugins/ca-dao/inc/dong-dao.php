<?php
/**
 * Đồng dao (T-103, docs/25 mục 4.4, ý 29–30). Trò chơi dân gian là một phân loại riêng (`tro_choi`, /tro-choi/<tên>/).
 * Mỗi trò chơi có khối "Cách chơi" (số người, độ tuổi, đồ dùng, các bước) do người sáng lập viết, hoặc chép nguyên văn
 * mục "Cách chơi" / "Luật chơi" của bài Wikipedia tương ứng bằng nút "Lấy từ Wikipedia" — khi đó dòng
 * "Nguồn: Wikipedia — <bài> ↗ · CC BY-SA 4.0" trỏ đúng bài đã lấy (D-057). Máy không tự viết cách chơi.
 * Bài đồng dao gắn trò chơi hiện khối đó dưới lời; trang trò chơi liệt kê các bài.
 * "Đọc cùng bé" (assets/dong-dao.js): mỗi lần một dòng, chữ thật to, chạm sang dòng, tô chữ theo nhịp vỗ tay; không âm thanh.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRUONG_TRO_CHOI = array(
	'so_nguoi' => 'Số người',
	'do_tuoi'  => 'Độ tuổi',
	'do_dung'  => 'Đồ dùng',
);
const CADAO_GIAY_PHEP_WIKI  = 'CC BY-SA 4.0';

add_action(
	'init',
	static function () {
		register_taxonomy(
			'tro_choi',
			CADAO_LOAI_BAI,
			array(
				'labels'            => array( 'name' => 'Trò chơi', 'singular_name' => 'Trò chơi', 'add_new_item' => 'Thêm trò chơi', 'separate_items_with_commas' => 'Trò chơi dân gian đi cùng bài đồng dao, ví dụ: Rồng rắn lên mây' ),
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => false,
				'hierarchical'      => false,
				'rewrite'           => array( 'slug' => 'tro-choi' ),
			)
		);
	}
);

/** Một ô của trò chơi (đã cắt khoảng trắng). */
function cadao_tro_choi_o( int $term_id, string $khoa ): string {
	return trim( (string) get_term_meta( $term_id, '_cadao_cc_' . $khoa, true ) );
}

/** Khối "Cách chơi" của một trò chơi; rỗng nếu chưa có gì. $lien_ket: thêm liên kết tới trang trò chơi (trên trang bài). */
function cadao_khoi_cach_choi( WP_Term $t, bool $lien_ket = true ): string {
	$buoc = array_values( array_filter( array_map( 'trim', explode( "\n", cadao_tro_choi_o( $t->term_id, 'buoc' ) ) ), 'strlen' ) );
	$tt   = '';
	foreach ( CADAO_TRUONG_TRO_CHOI as $k => $nhan ) {
		$v = cadao_tro_choi_o( $t->term_id, $k );
		if ( '' !== $v ) {
			$tt .= '<div><dt>' . esc_html( $nhan ) . '</dt><dd>' . esc_html( $v ) . '</dd></div>';
		}
	}
	$lk = $lien_ket ? '<p><a href="' . esc_url( (string) get_term_link( $t ) ) . '">' . esc_html( $t->count > 1 ? sprintf( 'Các bài đồng dao của trò chơi này (%d)', $t->count ) : 'Xem trang của trò chơi này' ) . '</a></p>' : '';
	if ( ! $buoc && '' === $tt ) {
		return $lien_ket ? '<section class="cadao-cach-choi"><p>Trò chơi: <a href="' . esc_url( (string) get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a></p></section>' : '';
	}
	$id    = 'cadao-cc-' . $t->term_id;
	$nguon = '';
	$ten_n = cadao_tro_choi_o( $t->term_id, 'nguon_ten' );
	if ( '' !== $ten_n ) {
		$url   = cadao_tro_choi_o( $t->term_id, 'nguon_url' );
		$gp    = cadao_tro_choi_o( $t->term_id, 'giay_phep' );
		$nguon = '<p class="cadao-nguon-cach-choi">Nguồn: ' . ( '' !== $url ? '<a href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( $ten_n ) . ' <span aria-hidden="true">↗</span></a>' : esc_html( $ten_n ) )
			. ( '' !== $gp ? ' · ' . ( CADAO_GIAY_PHEP_WIKI === $gp ? '<a href="https://creativecommons.org/licenses/by-sa/4.0/deed.vi" rel="noopener">' . esc_html( $gp ) . '</a>' : esc_html( $gp ) ) : '' ) . '</p>';
	}
	return '<section class="cadao-cach-choi" aria-labelledby="' . $id . '"><h2 id="' . $id . '" class="cadao-cc-tieu-de">' . esc_html( 'Cách chơi: ' . $t->name ) . '</h2>'
		. ( '' !== $tt ? '<dl class="cadao-cc-thong-tin">' . $tt . '</dl>' : '' )
		. ( $buoc ? '<ol class="cadao-cc-buoc"><li>' . implode( '</li><li>', array_map( 'esc_html', $buoc ) ) . '</li></ol>' : '' )
		. $nguon . $lk . '</section>';
}

// Trang bài: khối Cách chơi của (các) trò chơi, sau hàng nút, trước Dị bản / Chú thích
add_filter(
	'the_content',
	static function ( $noi_dung ) {
		if ( ! is_singular( CADAO_LOAI_BAI ) || get_the_ID() !== get_queried_object_id() || doing_filter( 'get_the_excerpt' ) ) {
			return $noi_dung;
		}
		$ds = get_the_terms( get_the_ID(), 'tro_choi' );
		if ( ! $ds || is_wp_error( $ds ) ) {
			return $noi_dung;
		}
		return cadao_chen_hanh_dong( $noi_dung, implode( '', array_map( 'cadao_khoi_cach_choi', $ds ) ) );
	},
	31
);

// Trang trò chơi: Cách chơi rồi danh sách bài
add_filter(
	'term_description',
	static function ( $mo_ta, $term_id, $taxonomy ) {
		if ( 'tro_choi' !== $taxonomy || is_admin() ) {
			return $mo_ta;
		}
		$t = get_term( (int) $term_id, 'tro_choi' );
		return $mo_ta . cadao_khoi_cach_choi( $t, false ) . '<h2 class="cadao-tu-dien-bai">' . esc_html( sprintf( 'Bài đồng dao của trò chơi này (%d)', (int) $t->count ) ) . '</h2>';
	},
	10,
	3
);

// ---------- Lấy mục "Cách chơi" từ Wikipedia tiếng Việt (chép nguyên văn, người sáng lập bấm) ----------

/** Tên bài Wikipedia tiếng Việt từ tên hoặc đường dẫn vi.wikipedia.org/wiki/…; '' nếu là đường dẫn khác. */
function cadao_wiki_ten_bai( string $s ): string {
	$s = trim( $s );
	if ( preg_match( '~^https?://vi\.(?:m\.)?wikipedia\.org/wiki/([^?#]+)~i', $s, $m ) ) {
		$s = rawurldecode( $m[1] );
	} elseif ( preg_match( '~^https?://~i', $s ) ) {
		return '';
	}
	return trim( str_replace( '_', ' ', $s ) );
}

/** Số thứ tự mục "Cách chơi" / "Luật chơi" trong danh sách mục của bài (API parse, prop=sections); null nếu không có. */
function cadao_wiki_tim_muc( array $muc ): ?string {
	foreach ( $muc as $m ) {
		if ( preg_match( '/^(cách|luật|lối)\s+chơi\b|^cách thức chơi/iu', trim( wp_strip_all_tags( (string) ( $m['line'] ?? '' ) ) ) ) ) {
			return (string) $m['index'];
		}
	}
	return null;
}

/**
 * Chữ của một mục Wikipedia (HTML do API trả): mỗi đoạn, mỗi ý một dòng; bỏ tiêu đề mục, chỉ số chú thích [1], bảng, hình.
 *
 * @return string[]
 */
function cadao_wiki_van_ban( string $html ): array {
	$html = (string) preg_replace(
		array( '~<sup\b[^>]*class="[^"]*reference[^"]*"[^>]*>.*?</sup>~is', '~<div class="mw-heading[^"]*">.*?</div>~is', '~<h([1-6])\b.*?</h\1>~is', '~<(table|figure|style|script)\b.*?</\1>~is', '~<div class="(?:thumb|hatnote|navbox|reflist)[^"]*">.*?</div>~is' ),
		'',
		$html
	);
	$html = (string) preg_replace( '~</(p|li|dd|dt|div)>|<br\s*/?>~i', "\n", $html );
	$chu  = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$ra   = array();
	foreach ( explode( "\n", $chu ) as $d ) {
		$d = trim( (string) preg_replace( array( '/\[\d+\]/', '/[ \t\x{00A0}]+/u' ), array( '', ' ' ), $d ) );
		if ( '' !== $d ) {
			$ra[] = $d;
		}
	}
	return $ra;
}

/**
 * Lấy mục Cách chơi của một bài Wikipedia tiếng Việt.
 *
 * @return array{buoc: string[], ten: string, url: string}|WP_Error
 */
function cadao_wiki_cach_choi( string $nhap ) {
	$ten = cadao_wiki_ten_bai( $nhap );
	if ( '' === $ten ) {
		return new WP_Error( 'cadao_wiki', 'Cần tên bài hoặc đường dẫn bài Wikipedia tiếng Việt (vi.wikipedia.org).' );
	}
	$api = 'https://vi.wikipedia.org/w/api.php';
	$j   = cadao_wd_goi( $api, array( 'action' => 'parse', 'page' => $ten, 'prop' => 'sections', 'redirects' => 1 ) );
	if ( empty( $j['parse']['title'] ) ) {
		return new WP_Error( 'cadao_wiki', sprintf( 'Không mở được bài "%s" trên Wikipedia tiếng Việt.', $ten ) );
	}
	$ten = (string) $j['parse']['title'];
	$muc = cadao_wiki_tim_muc( (array) ( $j['parse']['sections'] ?? array() ) );
	if ( null === $muc ) {
		return new WP_Error( 'cadao_wiki', sprintf( 'Bài "%s" không có mục "Cách chơi" hay "Luật chơi". Bạn tự viết vào ô Các bước nhé.', $ten ) );
	}
	$t    = cadao_wd_goi( $api, array( 'action' => 'parse', 'page' => $ten, 'section' => $muc, 'prop' => 'text', 'disableeditsection' => 1, 'disabletoc' => 1 ) );
	$buoc = cadao_wiki_van_ban( (string) ( $t['parse']['text']['*'] ?? '' ) );
	if ( ! $buoc ) {
		return new WP_Error( 'cadao_wiki', 'Không đọc được mục Cách chơi lúc này, thử lại sau.' );
	}
	return array( 'buoc' => $buoc, 'ten' => $ten, 'url' => 'https://vi.wikipedia.org/wiki/' . rawurlencode( str_replace( ' ', '_', $ten ) ) );
}

// ---------- Ô nhập trong trang quản trị (Ca dao – Tục ngữ → Trò chơi) ----------

/** Các ô của trò chơi: [khóa, nhãn, kiểu, gợi ý]. */
function cadao_o_tro_choi(): array {
	return array(
		array( 'so_nguoi', 'Số người', 'text', 'Ví dụ: 6–10 bạn' ),
		array( 'do_tuoi', 'Độ tuổi', 'text', 'Ví dụ: 4–10 tuổi' ),
		array( 'do_dung', 'Đồ dùng', 'text', 'Ví dụ: không cần; hoặc một sợi dây' ),
		array( 'buoc', 'Các bước (mỗi dòng một bước)', 'textarea', '' ),
		array( 'nguon_ten', 'Nguồn (khi chép từ nơi khác)', 'text', 'Để trống nếu bạn tự viết' ),
		array( 'nguon_url', 'Đường dẫn nguồn', 'url', '' ),
		array( 'giay_phep', 'Giấy phép của nguồn', 'text', 'Ví dụ: CC BY-SA 4.0' ),
	);
}

add_action(
	'tro_choi_edit_form_fields',
	static function ( $t ) {
		foreach ( cadao_o_tro_choi() as [ $k, $nhan, $kieu, $goi_y ] ) {
			$v = cadao_tro_choi_o( $t->term_id, $k );
			echo '<tr class="form-field"><th scope="row"><label for="cadao-cc-' . esc_attr( $k ) . '">' . esc_html( $nhan ) . '</label></th><td>'
				. ( 'textarea' === $kieu
					? '<textarea id="cadao-cc-' . esc_attr( $k ) . '" name="cadao_cc[' . esc_attr( $k ) . ']" rows="8">' . esc_textarea( $v ) . '</textarea>'
					: '<input type="' . esc_attr( $kieu ) . '" id="cadao-cc-' . esc_attr( $k ) . '" name="cadao_cc[' . esc_attr( $k ) . ']" value="' . esc_attr( $v ) . '" placeholder="' . esc_attr( $goi_y ) . '">' )
				. '</td></tr>';
		}
		echo '<tr class="form-field"><th scope="row"><label for="cadao-cc-wiki">Lấy từ Wikipedia</label></th><td>'
			. '<input type="text" id="cadao-cc-wiki" name="cadao_cc_wiki" value="' . esc_attr( cadao_tro_choi_o( $t->term_id, 'wiki' ) ) . '" placeholder="Tên bài hoặc đường dẫn, ví dụ: Rồng rắn lên mây">'
			. '<p><button type="submit" name="cadao_cc_lay_wiki" value="1" class="button">Lấy mục "Cách chơi" từ Wikipedia</button></p>'
			. '<p class="description">Máy chép nguyên văn mục "Cách chơi" (hoặc "Luật chơi") của bài đó vào ô Các bước và ghi nguồn đúng bài, giấy phép CC BY-SA 4.0. Bạn đọc lại, sửa nếu cần rồi bấm Cập nhật.</p></td></tr>';
	}
);

add_action(
	'tro_choi_add_form_fields',
	static function () {
		echo '<div class="form-field"><p>Sau khi thêm, bấm vào tên trò chơi để điền số người, độ tuổi, đồ dùng, các bước — hoặc lấy mục "Cách chơi" từ Wikipedia.</p></div>';
	}
);

add_action(
	'edited_tro_choi',
	static function ( $term_id ) {
		if ( ! current_user_can( 'manage_categories' ) || ! isset( $_POST['cadao_cc'] ) ) { // nonce: WordPress đã kiểm ở form sửa
			return;
		}
		$nhap = (array) wp_unslash( $_POST['cadao_cc'] );
		foreach ( cadao_o_tro_choi() as [ $k, , $kieu ] ) {
			$v = (string) ( $nhap[ $k ] ?? '' );
			update_term_meta( $term_id, '_cadao_cc_' . $k, 'textarea' === $kieu ? sanitize_textarea_field( $v ) : ( 'url' === $kieu ? esc_url_raw( $v ) : sanitize_text_field( $v ) ) );
		}
		$wiki = sanitize_text_field( wp_unslash( $_POST['cadao_cc_wiki'] ?? '' ) );
		update_term_meta( $term_id, '_cadao_cc_wiki', $wiki );
		if ( empty( $_POST['cadao_cc_lay_wiki'] ) ) {
			return;
		}
		$kq = cadao_wiki_cach_choi( $wiki );
		if ( is_wp_error( $kq ) ) {
			set_transient( 'cadao_cc_bao_' . get_current_user_id(), array( 'error', $kq->get_error_message() ), 60 );
			return;
		}
		update_term_meta( $term_id, '_cadao_cc_buoc', implode( "\n", $kq['buoc'] ) );
		update_term_meta( $term_id, '_cadao_cc_nguon_ten', 'Wikipedia — ' . $kq['ten'] );
		update_term_meta( $term_id, '_cadao_cc_nguon_url', $kq['url'] );
		update_term_meta( $term_id, '_cadao_cc_giay_phep', CADAO_GIAY_PHEP_WIKI );
		set_transient( 'cadao_cc_bao_' . get_current_user_id(), array( 'success', sprintf( 'Đã chép %d dòng từ mục Cách chơi của bài "%s". Bạn đọc lại và sửa nếu cần.', count( $kq['buoc'] ), $kq['ten'] ) ), 60 );
	}
);

add_action(
	'admin_notices',
	static function () {
		$bao = get_transient( 'cadao_cc_bao_' . get_current_user_id() );
		if ( is_array( $bao ) ) {
			delete_transient( 'cadao_cc_bao_' . get_current_user_id() );
			echo '<div class="notice notice-' . esc_attr( $bao[0] ) . ' is-dismissible"><p>' . esc_html( $bao[1] ) . '</p></div>';
		}
	}
);

// ---------- Đọc cùng bé: nút trong hàng công cụ (chỉ bài đồng dao), tệp JavaScript riêng ----------

function cadao_la_dong_dao( WP_Post $bai ): bool {
	return has_term( 'dong-dao', 'the_loai', $bai );
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! is_singular( CADAO_LOAI_BAI ) || ! cadao_la_dong_dao( get_queried_object() ) ) {
			return;
		}
		wp_enqueue_script( 'ca-dao-dong-dao', plugins_url( 'assets/dong-dao.js', CADAO_THU_MUC . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
);
