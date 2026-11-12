<?php
/**
 * Tìm kiếm (T-041, docs/25 mục 4.3, docs/20 mục 8): chỉ mục bỏ dấu tự làm trong cơ sở dữ liệu của site —
 * không cần cài thêm dịch vụ (Meilisearch để sau, khi kho đủ lớn). Mỗi bài đang đăng có hai bản chữ: có dấu (đã chuẩn
 * hóa kiểu bỏ dấu cũ → mới: "hoà" → "hòa") và không dấu. Truy vấn có dấu ưu tiên khớp có dấu; truy vấn không dấu khớp bản
 * không dấu. Trọng số: tiêu đề và lời ×3, Hiểu nhanh và chú thích ×1. Trang /tim/?q=… đọc được khi tắt JavaScript;
 * có JavaScript thì kết quả hiện khi gõ.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TIM_PHIEN_BAN = 1;

function cadao_tim_bang(): string {
	global $wpdb;
	return $wpdb->prefix . 'cadao_tim';
}

function cadao_tim_tao_bang(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta(
		'CREATE TABLE ' . cadao_tim_bang() . " (
		post_id bigint(20) unsigned NOT NULL,
		chinh longtext NOT NULL,
		chinh_kd longtext NOT NULL,
		phu longtext NOT NULL,
		phu_kd longtext NOT NULL,
		PRIMARY KEY  (post_id)
		) {$wpdb->get_charset_collate()};"
	);
	update_option( 'cadao_tim_phien_ban', CADAO_TIM_PHIEN_BAN );
	update_option( 'cadao_tim_sau', 0, false ); // làm chỉ mục cho bài đã có
}

add_action(
	'plugins_loaded',
	static function () {
		if ( (int) get_option( 'cadao_tim_phien_ban' ) !== CADAO_TIM_PHIEN_BAN ) {
			cadao_tim_tao_bang();
		}
	}
);

/** Bỏ dấu để so khớp: "Công cha" → "cong cha", "Đình" → "dinh" (docs/25 mục 4.3). */
function cadao_bo_dau_tim( string $s ): string {
	return trim( mb_strtolower( cadao_bo_dau( cadao_nfc( $s ) ) ) );
}

/**
 * Chuẩn hóa có dấu: kiểu bỏ dấu cũ → kiểu mới, CHỈ ở âm tiết mở và không sau "q" (docs/25 mục 4.3):
 * "hoà" → "hòa", "thuỷ" → "thủy"; "toàn", "quý", "loẹt" giữ nguyên. Chỉ dùng trong chỉ mục.
 */
function cadao_chuan_hoa_co_dau( string $s ): string {
	static $bang = array( 'oà' => 'òa', 'oá' => 'óa', 'oả' => 'ỏa', 'oã' => 'õa', 'oạ' => 'ọa', 'oè' => 'òe', 'oé' => 'óe', 'oẻ' => 'ỏe', 'oẽ' => 'õe', 'oẹ' => 'ọe', 'uỳ' => 'ùy', 'uý' => 'úy', 'uỷ' => 'ủy', 'uỹ' => 'ũy', 'uỵ' => 'ụy' );
	$s = mb_strtolower( cadao_nfc( $s ) );
	return preg_replace_callback( '/(?<!q)(' . implode( '|', array_keys( $bang ) ) . ')(?=$|[^\p{L}])/u', static fn( $m ) => $bang[ $m[1] ], $s );
}

/** Chữ để đánh chỉ mục / so khớp: bỏ dấu câu, gộp khoảng trắng, chuẩn hóa có dấu. */
function cadao_chu_tim( string $s ): string {
	return cadao_chuan_hoa_co_dau( cadao_chuan_loi( $s ) );
}

/** Đánh chỉ mục một bài (chỉ bài đang đăng). */
function cadao_tim_ghi( int $id ): void {
	global $wpdb;
	$bai = get_post( $id );
	if ( ! $bai || CADAO_LOAI_BAI !== $bai->post_type || 'publish' !== $bai->post_status ) {
		$wpdb->delete( cadao_tim_bang(), array( 'post_id' => $id ) );
		return;
	}
	$loi   = implode( "\n", cadao_loi_cac_khoi_tho( $bai->post_content ) );
	$chinh = cadao_chu_tim( get_the_title( $bai ) . "\n" . $loi );
	$phu   = cadao_chu_tim( $bai->post_excerpt . "\n" . implode( "\n", array_map( static fn( $c ) => $c['tu'] . ' ' . $c['noi_dung'], cadao_chu_thich_cua_bai( $bai->post_content ) ) ) );
	// Thêm khoảng trắng hai đầu để so theo trọn âm tiết (" ca " không khớp "cao")
	$wpdb->replace( cadao_tim_bang(), array( 'post_id' => $id, 'chinh' => " $chinh ", 'chinh_kd' => ' ' . cadao_bo_dau_tim( $chinh ) . ' ', 'phu' => " $phu ", 'phu_kd' => ' ' . cadao_bo_dau_tim( $phu ) . ' ' ) );
}

add_action(
	'save_post_' . CADAO_LOAI_BAI,
	static function ( $id ) {
		if ( ! wp_is_post_revision( $id ) && ! wp_is_post_autosave( $id ) ) {
			cadao_tim_ghi( (int) $id );
		}
	},
	28
);
add_action(
	'transition_post_status',
	static function ( $moi, $cu, $bai ) {
		if ( CADAO_LOAI_BAI === $bai->post_type && $moi !== $cu ) {
			cadao_tim_ghi( (int) $bai->ID );
		}
	},
	10,
	3
);

// Bài đã có: làm chỉ mục một lần (làm dần)
add_action(
	'cadao_thu_thap',
	static function () {
		global $wpdb;
		$sau = (int) get_option( 'cadao_tim_sau', -1 );
		if ( $sau < 0 ) {
			return;
		}
		$ds = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND ID > %d ORDER BY ID LIMIT 500", CADAO_LOAI_BAI, $sau ) );
		foreach ( $ds as $id ) {
			cadao_tim_ghi( (int) $id );
		}
		update_option( 'cadao_tim_sau', $ds ? (int) end( $ds ) : -1, false );
	},
	11
);

/** Số âm tiết thêm tối đa giữa các chữ tìm để vẫn tính là "gần nhau" (báo cáo E2E, L-08). */
const CADAO_TIM_CACH_TOI_DA = 3;

/**
 * Mọi chữ tìm có nằm gần nhau trong $chu không: một đoạn liền có đủ các chữ, dài tối đa số chữ + 3 âm tiết. Chữ cuối
 * khớp phần đầu âm tiết (đang gõ dở). Bài vè dài chứa đủ chữ thông dụng ở rải rác các dòng thì không tính.
 *
 * @param string[] $tu chữ tìm (đã bỏ khoảng trắng hai đầu), chữ cuối có thể gõ dở
 */
function cadao_tim_gan_nhau( string $chu, array $tu ): bool {
	$tu = array_values( array_filter( $tu, static fn( $t ) => '' !== $t ) );
	$n  = count( $tu );
	if ( ! $n ) {
		return false;
	}
	$am   = explode( ' ', trim( $chu ) );
	$thay = array(); // chữ tìm thứ j → vị trí âm tiết gần nhất đã gặp
	foreach ( $am as $i => $a ) {
		foreach ( $tu as $j => $t ) {
			if ( $j === $n - 1 ? str_starts_with( $a, $t ) : $a === $t ) {
				$thay[ $j ] = $i;
			}
		}
		if ( count( $thay ) === $n && $i - min( $thay ) + 1 <= $n + CADAO_TIM_CACH_TOI_DA ) {
			return true;
		}
	}
	return false;
}

/**
 * Tìm bài. Điểm: cả cụm liền nhau > các chữ gần nhau > các chữ rời rạc (bài dài chứa đủ chữ ở nhiều dòng — xếp cuối,
 * ghi 'roi' để trang tìm hiện ở nhóm riêng); bằng nhau thì bài ngắn hơn trước.
 *
 * @return array<int, array{id: int, diem: int, roi: bool}> giống nhất trước
 */
function cadao_tim( string $q, int $so = 20, ?int &$tong = null ): array {
	global $wpdb;
	$co_dau = cadao_chu_tim( $q );
	$kd     = cadao_bo_dau_tim( $co_dau );
	$tong = 0;
	if ( '' === $kd ) {
		return array();
	}
	$co_dau_that = $co_dau !== $kd; // người đọc gõ có dấu
	// Các từ trọn âm tiết; từ cuối có thể đang gõ dở: khớp phần đầu âm tiết
	$mau    = static function ( string $chu ): array {
		$tu = explode( ' ', $chu );
		$cuoi = array_pop( $tu );
		return array_merge( array_map( static fn( $t ) => " $t ", $tu ), array( " $cuoi" ) );
	};
	$tu     = $mau( $kd );
	$tu_cd  = $mau( $co_dau );
	$tron   = array_map( 'trim', $tu );
	$cum    = " $kd";
	$cum_cd = " $co_dau";
	$co_het = static fn( string $chu, array $ds ) => array_reduce( $ds, static fn( $c, $t ) => $c && str_contains( $chu, $t ), true );
	$giong  = static fn( $s ) => '%' . $wpdb->esc_like( $s ) . '%';
	$diem   = array();
	$roi    = array();
	// Ứng viên: chứa mọi từ (không dấu) ở phần chính hoặc phần phụ
	$dk = implode( ' AND ', array_map( static fn( $t ) => $wpdb->prepare( '(chinh_kd LIKE BINARY %s OR phu_kd LIKE BINARY %s)', $giong( $t ), $giong( $t ) ), $tu ) );
	foreach ( $wpdb->get_results( 'SELECT post_id, chinh, chinh_kd, phu, phu_kd FROM ' . cadao_tim_bang() . " WHERE $dk LIMIT 500" ) as $r ) {
		$lien_c = str_contains( $r->chinh_kd, $cum );
		$gan_c  = ! $lien_c && $co_het( $r->chinh_kd, $tu ) && cadao_tim_gan_nhau( $r->chinh_kd, $tron );
		$lien_p = str_contains( $r->phu_kd, $cum );
		$gan_p  = ! $lien_p && $co_het( $r->phu_kd, $tu ) && cadao_tim_gan_nhau( $r->phu_kd, $tron );
		$d  = $lien_c ? 300 : ( $gan_c ? 150 : 0 );
		$d += $lien_p ? 100 : ( $gan_p ? 50 : 0 );
		if ( $co_dau_that ) { // gõ có dấu: khớp đúng dấu được ưu tiên
			$d += str_contains( $r->chinh, $cum_cd ) ? 200 : ( $gan_c && $co_het( $r->chinh, $tu_cd ) ? 80 : 0 );
			$d += str_contains( $r->phu, $cum_cd ) ? 60 : 0;
		}
		$d += str_starts_with( $r->chinh_kd, $cum ) ? 40 : 0; // khớp từ đầu tiêu đề
		$id = (int) $r->post_id;
		if ( $d <= 0 ) { // có đủ chữ nhưng rời rạc
			$d          = 10;
			$roi[ $id ] = true;
		}
		$diem[ $id ] = $d * 1000 - min( 999, intdiv( strlen( $r->chinh_kd ), 50 ) ); // bằng điểm: bài ngắn trước
	}
	arsort( $diem );
	$tong = count( $diem ); // tổng số bài khớp (trong tối đa 500 ứng viên)
	$diem = array_slice( $diem, 0, $so, true );
	return array_map( static fn( $id, $d ) => array( 'id' => $id, 'diem' => $d, 'roi' => isset( $roi[ $id ] ) ), array_keys( $diem ), $diem );
}

/** Mục từ trong từ điển có tên khớp truy vấn (nhóm riêng phía trên). */
function cadao_tim_muc_tu( string $q, int $so = 5 ): array {
	$kd = cadao_bo_dau_tim( cadao_chu_tim( $q ) );
	if ( mb_strlen( $kd ) < 2 ) {
		return array();
	}
	// Tên mục từ đã bỏ dấu → id: lưu đệm một giờ, xóa khi mục từ thay đổi
	$ten = get_transient( 'cadao_tim_muc_tu' );
	if ( ! is_array( $ten ) ) {
		$ten = array();
		foreach ( get_terms( array( 'taxonomy' => 'tu_kho', 'hide_empty' => true, 'number' => 0 ) ) ?: array() as $t ) {
			$ten[ cadao_bo_dau_tim( cadao_chu_tim( $t->name ) ) ][] = (int) $t->term_id;
		}
		set_transient( 'cadao_tim_muc_tu', $ten, HOUR_IN_SECONDS );
	}
	return array_values( array_filter( array_map( static fn( $id ) => get_term( $id, 'tu_kho' ), array_slice( $ten[ $kd ] ?? array(), 0, $so ) ), static fn( $t ) => $t && ! is_wp_error( $t ) ) );
}

foreach ( array( 'created_tu_kho', 'edited_tu_kho', 'delete_tu_kho', 'set_object_terms' ) as $viec ) {
	add_action( $viec, static fn() => delete_transient( 'cadao_tim_muc_tu' ) );
}

/** Kết quả cho trang và cho JavaScript. */
function cadao_tim_ket_qua( string $q ): array {
	$bai = array();
	$tong = 0;
	foreach ( cadao_tim( $q, 20, $tong ) as $x ) {
		$p = get_post( $x['id'] );
		if ( ! $p ) {
			continue;
		}
		$loi   = cadao_loi_cac_khoi_tho( $p->post_content )[0] ?? '';
		$dong  = array_values( array_filter( explode( "\n", $loi ), static fn( $d ) => '' !== trim( $d ) ) );
		$kd    = cadao_bo_dau_tim( cadao_chu_tim( $q ) );
		$khop  = '';
		foreach ( $dong as $d ) { // dòng giữa bài có chứa phần khớp
			if ( str_contains( cadao_bo_dau_tim( cadao_chu_tim( $d ) ), $kd ) ) {
				$khop = $d;
				break;
			}
		}
		$bai[] = array( 'tieu_de' => cadao_giai_ma( wp_strip_all_tags( get_the_title( $p ) ) ), 'roi' => $x['roi'], 'url' => get_permalink( $p ), 'the_loai' => cadao_ten_the_loai( $p ), 'muc_kiem' => function_exists( 'cadao_muc_kiem' ) ? cadao_muc_kiem( $p->ID )['ngan'] : '', 'dong_dau' => $dong[0] ?? '', 'dong_khop' => $khop && $khop !== ( $dong[0] ?? '' ) ? $khop : '', 'hieu_nhanh' => wp_trim_words( $p->post_excerpt, 24 ), 'so_lieu' => function_exists( 'cadao_html_so_lieu' ) ? cadao_html_so_lieu( $p->ID, array( 'doc' ), 'cadao-so-lieu-ds' ) : '' );
	}
	$muc_tu = array_map( static fn( $t ) => array( 'ten' => $t->name, 'url' => get_term_link( $t ), 'giai' => wp_trim_words( $t->description, 20 ), 'so_bai' => (int) $t->count ), cadao_tim_muc_tu( $q ) );
	return array( 'q' => $q, 'bai' => $bai, 'tong' => max( $tong, count( $bai ) ), 'muc_tu' => $muc_tu );
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'cadao/v1',
			'/tim',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'args'                => array( 'q' => array( 'type' => 'string', 'required' => true ) ),
				'callback'            => static fn( WP_REST_Request $yc ) => cadao_tim_ket_qua( mb_substr( (string) $yc['q'], 0, 120 ) ),
			)
		);
	}
);

/** "N bài", hoặc "20 trên N bài — gõ thêm chữ để thu hẹp" khi chỉ hiện một phần. */
function cadao_tim_dem( int $hien, int $tong ): string {
	return $tong > $hien ? sprintf( '%d trên %d bài — gõ thêm chữ để thu hẹp', $hien, $tong ) : sprintf( '%d bài', $hien );
}

/** HTML danh sách kết quả (trang /tim/ khi tắt JavaScript; JavaScript dựng cùng dạng). */
function cadao_tim_html( array $kq ): string {
	if ( '' === trim( $kq['q'] ) ) {
		return '';
	}
	if ( ! $kq['bai'] && ! $kq['muc_tu'] ) {
		return '<div class="cadao-tim-khong"><p><strong>Chưa tìm thấy câu này trong kho.</strong> Thử gõ ít chữ hơn — vài chữ liền nhau trong câu là đủ, có dấu hay không dấu đều được. Hoặc xem <a href="' . esc_url( home_url( '/kho/' ) ) . '">tất cả bài</a>, <a href="' . esc_url( home_url( '/tu-dien/' ) ) . '">từ điển</a>.</p><p class="cadao-tim-phu">Kho đang được bổ sung dần. Câu tìm không ra được ghi lại (không kèm thông tin gì về bạn) để người biên soạn thêm vào.</p></div>';
	}
	$ra = '';
	if ( $kq['muc_tu'] ) {
		$ra .= '<h2 class="cadao-tim-nhom">Từ điển</h2><ul class="cadao-tim-ds">';
		foreach ( $kq['muc_tu'] as $m ) {
			$ra .= '<li><a href="' . esc_url( $m['url'] ) . '"><strong>' . esc_html( $m['ten'] ) . '</strong></a> — ' . esc_html( $m['giai'] ) . ' <span class="cadao-tim-phu">(' . (int) $m['so_bai'] . ' bài)</span></li>';
		}
		$ra .= '</ul>';
	}
	if ( $kq['bai'] ) {
		$ra   .= '<h2 class="cadao-tim-nhom">' . esc_html( cadao_tim_dem( count( $kq['bai'] ), (int) ( $kq['tong'] ?? 0 ) ) ) . '</h2>';
		$lien  = array_filter( $kq['bai'], static fn( $b ) => empty( $b['roi'] ) );
		$roi   = array_filter( $kq['bai'], static fn( $b ) => ! empty( $b['roi'] ) );
		$muc   = static function ( array $b ): string {
			// Tên bài (người đọc thấy chữ mình gõ khi nó nằm ở tên), rồi dòng có chữ khớp hoặc dòng đầu (báo cáo E2E, L-04)
			$dong = $b['dong_khop'] ? '… ' . $b['dong_khop'] : ( cadao_chuan_loi( $b['dong_dau'] ) !== cadao_chuan_loi( $b['tieu_de'] ) ? $b['dong_dau'] : '' );
			return '<li><a href="' . esc_url( $b['url'] ) . '"><strong>' . esc_html( $b['tieu_de'] ) . '</strong></a> <span class="cadao-tim-phu">· ' . esc_html( $b['the_loai'] ) . ( $b['muc_kiem'] ? ' · ' . esc_html( $b['muc_kiem'] ) : '' ) . '</span>' . $b['so_lieu']
				. ( '' !== $dong ? '<br><span class="cadao-tim-khop">' . esc_html( $dong ) . '</span>' : '' )
				. ( $b['hieu_nhanh'] ? '<br><span class="cadao-tim-phu">' . esc_html( $b['hieu_nhanh'] ) . '</span>' : '' ) . '</li>';
		};
		$ra .= $lien ? '<ul class="cadao-tim-ds">' . implode( '', array_map( $muc, $lien ) ) . '</ul>' : '';
		$ra .= $roi ? '<h3 class="cadao-tim-nhom cadao-tim-roi">Có các chữ này nhưng không liền nhau</h3><ul class="cadao-tim-ds">' . implode( '', array_map( $muc, $roi ) ) . '</ul>' : '';
	}
	return $ra;
}

/** [cadao_tim]: ô tìm và kết quả (trang "Tìm", /tim/). */
add_shortcode(
	'cadao_tim',
	static function () {
		$q = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
		return '<form class="cadao-tim" role="search" method="get" action="' . esc_url( get_permalink() ) . '">'
			. '<label for="cadao-tim-o" class="screen-reader-text">Tìm câu ca dao, tục ngữ</label>'
			. '<input id="cadao-tim-o" type="search" name="q" value="' . esc_attr( $q ) . '" placeholder="Gõ vài chữ trong câu, có dấu hay không dấu" autocomplete="off" enterkeyhint="search">'
			. '<button type="submit">Tìm</button></form>'
			. '<div class="cadao-tim-gan-day" hidden></div>'
			. '<div class="cadao-tim-kq" aria-live="polite">' . cadao_tim_html( cadao_tim_ket_qua( $q ) ) . '</div>';
	}
);

/** Trang "Tìm" (/tim/) tạo một lần; ô tìm mặc định của WordPress (?s=) chuyển về đây. */
add_action(
	'init',
	static function () {
		if ( get_option( 'cadao_trang_tim' ) || wp_installing() ) {
			return;
		}
		$co = get_page_by_path( 'tim' );
		$id = $co ? $co->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Tìm', 'post_name' => 'tim', 'post_content' => "<!-- wp:shortcode -->\n[cadao_tim]\n<!-- /wp:shortcode -->" ) );
		update_option( 'cadao_trang_tim', (int) $id, false );
	},
	30
);

add_action(
	'template_redirect',
	static function () {
		if ( is_search() && ! is_admin() ) {
			wp_safe_redirect( add_query_arg( 'q', rawurlencode( get_search_query( false ) ), home_url( '/tim/' ) ), 302 );
			exit;
		}
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! is_page( (int) get_option( 'cadao_trang_tim' ) ) ) {
			return;
		}
		wp_enqueue_script( 'ca-dao-tim', plugins_url( 'assets/tim.js', CADAO_THU_MUC . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
		$cau_hinh = array( 'api' => rest_url( 'cadao/v1/tim' ) );
		if ( ! current_user_can( 'edit_posts' ) ) { // đo "tìm không ra" (T-052); người biên soạn không tính
			$cau_hinh['suKien'] = rest_url( 'cadao/v1/su-kien' );
		}
		wp_add_inline_script( 'ca-dao-tim', 'window.cadaoTim=' . wp_json_encode( $cau_hinh ) . ';', 'before' );
	}
);
