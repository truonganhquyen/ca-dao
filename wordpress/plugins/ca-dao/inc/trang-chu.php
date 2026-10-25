<?php
/**
 * Trang chủ (T-043), "Bạn có biết?" (T-093), trang thể loại (T-094), tình huống — docs/25 mục 4.3.
 *
 * "Bạn có biết?": máy GHÉP theo khuôn từ chú thích có sẵn ("… [từ] là [mệnh đề đầu của lời giải], và có trong câu
 * [tên bài]?"), kèm nguồn của chú thích; người sáng lập duyệt, sửa hoặc tự viết; chỉ câu đã duyệt mới lên trang.
 * Máy không tự viết sự kiện. Tình huống ("Hôm nay bạn đang…"): người biên soạn gắn bài vào tình huống và viết "Hợp khi…";
 * trang tình huống chỉ có khi đủ 2 bài đang đăng (NGUONG_TRANG_TINH_HUONG). Giới thiệu thể loại do người sáng lập viết.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRANG_BCB         = 'cadao-ban-co-biet';
const CADAO_NGUONG_TINH_HUONG = 2;

// ---------- Tình huống ----------

add_action(
	'init',
	static function () {
		register_taxonomy(
			'tinh_huong',
			CADAO_LOAI_BAI,
			array(
				'labels'            => array( 'name' => 'Tình huống', 'singular_name' => 'Tình huống', 'add_new_item' => 'Thêm tình huống', 'separate_items_with_commas' => 'Ví dụ: Biết ơn cha mẹ, Khi nhớ mẹ' ),
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'hierarchical'      => false,
				'rewrite'           => array( 'slug' => 'tinh-huong' ),
			)
		);
		register_post_meta(
			CADAO_LOAI_BAI,
			'hop_khi',
			array( 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => static fn() => current_user_can( 'edit_posts' ) )
		);
	},
	11
);

// Tình huống của site cũ (content/tinh-huong, qua du-lieu/tinh-huong.json): tạo một lần
add_action(
	'init',
	static function () {
		if ( get_option( 'cadao_tinh_huong_cu' ) ) {
			return;
		}
		$tep = CADAO_THU_MUC . '/du-lieu/tinh-huong.json';
		foreach ( ( is_readable( $tep ) ? json_decode( (string) file_get_contents( $tep ), true )['tinh_huong'] ?? array() : array() ) as $t ) {
			if ( ! term_exists( $t['slug'], 'tinh_huong' ) ) {
				wp_insert_term( $t['ten'], 'tinh_huong', array( 'slug' => $t['slug'], 'description' => $t['mo_dau'] ) );
			}
		}
		update_option( 'cadao_tinh_huong_cu', 1 );
	},
	13
);

add_action(
	'add_meta_boxes_' . CADAO_LOAI_BAI,
	static function () {
		add_meta_box( 'cadao_hop_khi', '"Hợp khi…" (trang tình huống)', 'cadao_hop_hop_khi', CADAO_LOAI_BAI, 'side' );
	}
);

function cadao_hop_hop_khi( WP_Post $bai ): void {
	wp_nonce_field( 'cadao_luu_hop_khi', 'cadao_hop_khi_nonce' );
	echo '<p><label for="cadao_hop_khi" class="screen-reader-text">Hợp khi</label><textarea class="widefat" rows="2" id="cadao_hop_khi" name="hop_khi" placeholder="Ví dụ: bạn muốn nói lời biết ơn cha mẹ dịp Vu Lan">' . esc_textarea( (string) get_post_meta( $bai->ID, 'hop_khi', true ) ) . '</textarea></p>';
	echo '<p class="description">Một câu do bạn viết, hiện dưới bài trên trang tình huống. Gắn bài vào tình huống ở ô "Tình huống".</p>';
}

add_action(
	'save_post_' . CADAO_LOAI_BAI,
	static function ( $id ) {
		if ( isset( $_POST['cadao_hop_khi_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['cadao_hop_khi_nonce'] ), 'cadao_luu_hop_khi' ) && current_user_can( 'edit_post', $id ) ) {
			update_post_meta( $id, 'hop_khi', sanitize_text_field( wp_unslash( $_POST['hop_khi'] ?? '' ) ) );
		}
	}
);

/** Tình huống đủ bài để có trang (đếm bài đang đăng). */
function cadao_tinh_huong_du_bai(): array {
	$ds = get_terms( array( 'taxonomy' => 'tinh_huong', 'hide_empty' => true ) );
	return array_values( array_filter( is_wp_error( $ds ) ? array() : $ds, static fn( $t ) => (int) $t->count >= CADAO_NGUONG_TINH_HUONG ) );
}

/** Thứ tự thẻ tình huống hôm nay: dịp trong lịch (config/lich-tinh-huong.yaml) đưa lên đầu, rồi thứ tự mặc định. */
function cadao_tinh_huong_hom_nay(): array {
	$tep  = CADAO_THU_MUC . '/du-lieu/tinh-huong.json';
	$lich = is_readable( $tep ) ? ( json_decode( (string) file_get_contents( $tep ), true )['lich'] ?? array() ) : array();
	$hom  = wp_date( 'Y-m-d' );
	$dau  = array();
	foreach ( (array) ( $lich['dip'] ?? array() ) as $d ) {
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $d['tu'] ) && $d['tu'] <= $hom && $hom <= $d['den'] ) {
			$dau = array_merge( $dau, (array) $d['dua_len_dau'] );
		}
	}
	$thu_tu = array_values( array_unique( array_merge( $dau, (array) ( $lich['mac_dinh'] ?? array() ) ) ) );
	$ds     = cadao_tinh_huong_du_bai();
	usort(
		$ds,
		static function ( $a, $b ) use ( $thu_tu ) {
			$ia = array_search( $a->slug, $thu_tu, true );
			$ib = array_search( $b->slug, $thu_tu, true );
			return array( false === $ia ? 999 : $ia, -$a->count ) <=> array( false === $ib ? 999 : $ib, -$b->count );
		}
	);
	return array_slice( $ds, 0, 8 );
}

// Trang tình huống chưa đủ bài: không có trang (404), không để trang rỗng
add_action(
	'template_redirect',
	static function () {
		if ( is_tax( 'tinh_huong' ) && (int) get_queried_object()->count < CADAO_NGUONG_TINH_HUONG ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
		}
	}
);

/** Lời rút gọn ≤ $so dòng. */
function cadao_loi_rut_gon( WP_Post $bai, int $so = 4 ): string {
	$dong = array_values( array_filter( explode( "\n", cadao_loi_cac_khoi_tho( $bai->post_content )[0] ?? '' ), static fn( $d ) => '' !== trim( $d ) ) );
	return implode( "\n", array_slice( $dong, 0, $so ) ) . ( count( $dong ) > $so ? '…' : '' );
}

// ---------- "Bạn có biết?" (T-093) ----------

/** Mệnh đề đầu của lời giải: tới dấu chấm, chấm phẩy hoặc ngoặc đầu tiên; bỏ chữ hoa đầu câu. */
function cadao_menh_de_dau( string $giai ): string {
	$s = trim( preg_split( '/[.;:(\n]/u', $giai, 2 )[0] );
	if ( mb_strlen( $s ) < 6 || mb_strlen( $s ) > 120 ) {
		return '';
	}
	$dau = mb_substr( $s, 0, 1 );
	$s2  = mb_substr( $s, 1, 1 );
	return ( $s2 === mb_strtolower( $s2 ) ? mb_strtolower( $dau ) : $dau ) . mb_substr( $s, 1 ); // giữ hoa nếu là tên riêng (chữ thứ hai cũng hoa)
}

function cadao_bcb_ds(): array {
	$ds = get_option( 'cadao_ban_co_biet', array() );
	return is_array( $ds ) ? $ds : array();
}

function cadao_bcb_luu( array $ds ): void {
	update_option( 'cadao_ban_co_biet', array_values( $ds ), false );
}

/**
 * Ghép thêm câu "Bạn có biết?" chờ duyệt từ mục từ đang được dùng (mỗi mục từ một câu, không trùng câu đã có).
 *
 * @return int số câu mới
 */
function cadao_bcb_ghep( int $so = 8 ): int {
	$ds  = cadao_bcb_ds();
	$co  = array_flip( array_column( $ds, 'tu_kho' ) );
	$moi = 0;
	$tus = get_terms( array( 'taxonomy' => 'tu_kho', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 300 ) );
	foreach ( is_wp_error( $tus ) ? array() : $tus as $t ) {
		if ( $moi >= $so || isset( $co[ $t->term_id ] ) ) {
			continue;
		}
		$md   = cadao_menh_de_dau( $t->description );
		$bai  = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'numberposts' => 1, 'tax_query' => array( array( 'taxonomy' => 'tu_kho', 'terms' => $t->term_id ) ) ) );
		if ( '' === $md || ! $bai ) {
			continue;
		}
		// Khuôn ghép: chỉ dùng từ, lời giải có sẵn và tên bài — máy không thêm sự kiện
		$ds[] = array(
			'id'        => wp_generate_password( 8, false ),
			'chu'       => sprintf( '… "%s" là %s, và có trong câu "%s"?', $t->name, $md, get_the_title( $bai[0] ) ),
			'tu_kho'    => (int) $t->term_id,
			'bai'       => (int) $bai[0]->ID,
			'nguon_ten' => (string) get_term_meta( $t->term_id, '_cadao_nguon_ten', true ),
			'nguon_url' => (string) get_term_meta( $t->term_id, '_cadao_nguon_url', true ),
			'trang_thai' => 'cho',
			'luc'       => current_time( 'mysql' ),
		);
		++$moi;
	}
	cadao_bcb_luu( $ds );
	return $moi;
}

add_action(
	'cadao_tao_bai_hang_ngay',
	static function () {
		if ( count( array_filter( cadao_bcb_ds(), static fn( $x ) => 'cho' === $x['trang_thai'] ) ) < 8 ) {
			cadao_bcb_ghep( 8 );
		}
	}
);

/** 3–4 câu đã duyệt của tuần này (xoay vòng theo tuần). */
function cadao_bcb_tuan_nay( int $so = 4 ): array {
	$duyet = array_values( array_filter( cadao_bcb_ds(), static fn( $x ) => 'duyet' === $x['trang_thai'] ) );
	if ( ! $duyet ) {
		return array();
	}
	$tuan = (int) wp_date( 'oW' );
	$dau  = ( $tuan * $so ) % count( $duyet );
	$ra   = array();
	for ( $i = 0; $i < min( $so, count( $duyet ) ); $i++ ) {
		$ra[] = $duyet[ ( $dau + $i ) % count( $duyet ) ];
	}
	return $ra;
}

add_action(
	'admin_menu',
	static function () {
		$so = count( array_filter( cadao_bcb_ds(), static fn( $x ) => 'cho' === $x['trang_thai'] ) );
		add_submenu_page( 'edit.php?post_type=' . CADAO_LOAI_BAI, 'Bạn có biết?', 'Bạn có biết?' . ( $so ? ' <span class="awaiting-mod">' . $so . '</span>' : '' ), 'manage_options', CADAO_TRANG_BCB, 'cadao_trang_bcb' );
	},
	15
);

function cadao_trang_bcb(): void {
	echo '<div class="wrap"><h1>Bạn có biết?</h1>';
	cadao_hien_bao();
	echo '<p>Mỗi tuần trang chủ hiện 3–4 câu đã duyệt. Máy chỉ <strong>ghép theo khuôn</strong> từ chú thích có sẵn (từ, mệnh đề đầu của lời giải, tên bài) — sửa lời cho tự nhiên rồi bấm "Duyệt", hoặc "Bỏ". Bạn cũng tự viết được ở ô cuối trang. Máy không tự viết sự kiện.</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'cadao_bcb' );
	echo '<input type="hidden" name="action" value="cadao_bcb"><table class="widefat striped"><thead><tr><th>Câu</th><th>Bài, nguồn</th><th>Trạng thái</th></tr></thead><tbody>';
	foreach ( cadao_bcb_ds() as $x ) {
		if ( 'bo' === $x['trang_thai'] ) {
			continue;
		}
		$bai = $x['bai'] ? get_post( $x['bai'] ) : null;
		echo '<tr><td><label class="screen-reader-text" for="bcb-' . esc_attr( $x['id'] ) . '">Câu</label><textarea class="large-text" rows="2" id="bcb-' . esc_attr( $x['id'] ) . '" name="chu[' . esc_attr( $x['id'] ) . ']">' . esc_textarea( $x['chu'] ) . '</textarea></td>';
		echo '<td>' . ( $bai ? '<a href="' . esc_url( get_permalink( $bai ) ) . '">' . esc_html( get_the_title( $bai ) ) . '</a>' : '—' ) . ( $x['nguon_ten'] ? '<br><span class="description">Nguồn: ' . esc_html( $x['nguon_ten'] ) . '</span>' : '' ) . '</td>';
		echo '<td><select name="tt[' . esc_attr( $x['id'] ) . ']" aria-label="Trạng thái">';
		foreach ( array( 'cho' => 'Chờ duyệt', 'duyet' => 'Duyệt (lên trang)', 'bo' => 'Bỏ' ) as $k => $ten ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $x['trang_thai'], $k, false ) . '>' . esc_html( $ten ) . '</option>';
		}
		echo '</select></td></tr>';
	}
	echo '<tr><td colspan="3"><label for="bcb-moi"><strong>Tự viết câu mới</strong> (bắt đầu bằng "…", kết thúc bằng "?"):</label><textarea class="large-text" rows="2" id="bcb-moi" name="moi"></textarea></td></tr>';
	echo '</tbody></table>';
	submit_button( 'Lưu' );
	echo '</form><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'cadao_bcb_ghep' );
	echo '<input type="hidden" name="action" value="cadao_bcb_ghep">';
	submit_button( 'Ghép thêm câu từ chú thích', 'secondary' );
	echo '</form></div>';
}

add_action(
	'admin_post_cadao_bcb',
	static function () {
		cadao_kiem_quyen( 'cadao_bcb' );
		$chu = (array) wp_unslash( $_POST['chu'] ?? array() );
		$tt  = (array) wp_unslash( $_POST['tt'] ?? array() );
		$ds  = cadao_bcb_ds();
		foreach ( $ds as &$x ) {
			if ( isset( $chu[ $x['id'] ] ) ) {
				$x['chu'] = sanitize_textarea_field( $chu[ $x['id'] ] );
			}
			if ( isset( $tt[ $x['id'] ] ) && in_array( $tt[ $x['id'] ], array( 'cho', 'duyet', 'bo' ), true ) ) {
				$x['trang_thai'] = $tt[ $x['id'] ];
			}
		}
		unset( $x );
		$moi = sanitize_textarea_field( wp_unslash( $_POST['moi'] ?? '' ) );
		if ( '' !== trim( $moi ) ) {
			$ds[] = array( 'id' => wp_generate_password( 8, false ), 'chu' => $moi, 'tu_kho' => 0, 'bai' => 0, 'nguon_ten' => '', 'nguon_url' => '', 'trang_thai' => 'duyet', 'luc' => current_time( 'mysql' ) );
		}
		cadao_bcb_luu( $ds );
		cadao_ve( CADAO_TRANG_BCB, 'Đã lưu.' );
	}
);

add_action(
	'admin_post_cadao_bcb_ghep',
	static function () {
		cadao_kiem_quyen( 'cadao_bcb_ghep' );
		cadao_ve( CADAO_TRANG_BCB, sprintf( 'Máy ghép thêm %d câu chờ duyệt.', cadao_bcb_ghep( 8 ) ) );
	}
);

// ---------- Trang chủ (T-043) ----------

/** "Câu hôm nay": mỗi ngày một bài ngắn (≤ 4 dòng) đang đăng, chọn theo ngày (ai mở cũng thấy cùng câu). */
function cadao_cau_hom_nay(): ?WP_Post {
	global $wpdb;
	$ds = get_transient( 'cadao_cau_hom_nay_ds' );
	if ( ! is_array( $ds ) ) {
		$ds = array();
		foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' ORDER BY ID", CADAO_LOAI_BAI ) ) as $p ) {
			$loi = cadao_loi_cac_khoi_tho( $p->post_content )[0] ?? '';
			$so  = count( array_filter( explode( "\n", $loi ), static fn( $d ) => '' !== trim( $d ) ) );
			if ( $so >= 1 && $so <= 4 ) {
				$ds[] = (int) $p->ID;
			}
		}
		set_transient( 'cadao_cau_hom_nay_ds', $ds, 6 * HOUR_IN_SECONDS );
	}
	return $ds ? get_post( $ds[ abs( crc32( wp_date( 'Y-m-d' ) ) ) % count( $ds ) ] ) : null;
}

/** [cadao_trang_chu]: các khối của trang chủ. */
add_shortcode(
	'cadao_trang_chu',
	static function () {
		$ra = '<p class="cadao-dinh-vi">Ca dao, tục ngữ, đồng dao, vè, câu đố — đọc kèm nghĩa từ khó, dị bản và nguồn.</p>';
		$ra .= '<form class="cadao-tim cadao-tim-lon" role="search" method="get" action="' . esc_url( home_url( '/tim/' ) ) . '"><label for="cadao-tim-chu" class="screen-reader-text">Tìm câu ca dao, tục ngữ</label><input id="cadao-tim-chu" type="search" name="q" placeholder="Tìm một câu: gõ vài chữ, có dấu hay không dấu" enterkeyhint="search"><button type="submit">Tìm</button></form>';
		$cau = cadao_cau_hom_nay();
		if ( $cau ) {
			$ra .= '<section class="cadao-cau-hom-nay" aria-labelledby="cadao-chn"><h2 id="cadao-chn">Câu hôm nay</h2><p class="cadao-chn-loi">' . nl2br( esc_html( cadao_loi_rut_gon( $cau, 4 ) ) ) . '</p><p><a href="' . esc_url( get_permalink( $cau ) ) . '">Đọc bài, xem nghĩa <span aria-hidden="true">→</span></a> <span class="cadao-tim-phu">· ' . esc_html( cadao_ten_the_loai( $cau ) ) . '</span></p></section>';
		}
		$bcb = cadao_bcb_tuan_nay( 4 );
		if ( $bcb ) {
			$ra .= '<section class="cadao-bcb" aria-labelledby="cadao-bcb"><h2 id="cadao-bcb">Bạn có biết?</h2><ul>';
			foreach ( $bcb as $x ) {
				$ra .= '<li>' . ( $x['bai'] ? '<a href="' . esc_url( get_permalink( $x['bai'] ) ) . '">' . esc_html( $x['chu'] ) . '</a>' : esc_html( $x['chu'] ) )
					. ( $x['nguon_ten'] ? ' <span class="cadao-tim-phu">(Nguồn: ' . ( $x['nguon_url'] ? '<a href="' . esc_url( $x['nguon_url'] ) . '" rel="noopener">' . esc_html( $x['nguon_ten'] ) . '</a>' : esc_html( $x['nguon_ten'] ) ) . ')</span>' : '' ) . '</li>';
			}
			$ra .= '</ul></section>';
		}
		$th = cadao_tinh_huong_hom_nay();
		if ( $th ) {
			$ra .= '<section aria-labelledby="cadao-th"><h2 id="cadao-th">Hôm nay bạn đang…</h2><ul class="cadao-the">';
			foreach ( $th as $t ) {
				$ra .= '<li><a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a></li>';
			}
			$ra .= '</ul></section>';
		}
		$ra .= do_shortcode( '[cadao_doc_nhieu]' );
		$moi = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'numberposts' => 6 ) );
		if ( $moi ) {
			$ra .= '<section aria-labelledby="cadao-vbs"><h2 id="cadao-vbs">Vừa bổ sung</h2><ul class="cadao-tim-ds">';
			foreach ( $moi as $p ) {
				$ra .= '<li><a href="' . esc_url( get_permalink( $p ) ) . '"><strong>' . esc_html( get_the_title( $p ) ) . '</strong></a> <span class="cadao-tim-phu">· ' . esc_html( cadao_ten_the_loai( $p ) ) . '</span></li>';
			}
			$ra .= '</ul><p><a href="' . esc_url( home_url( '/kho/' ) ) . '">Xem tất cả bài <span aria-hidden="true">→</span></a></p></section>';
		}
		$loi_vao = static function ( string $tax, string $tieu ) {
			$ds = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true, 'parent' => 'chu_de' === $tax ? 0 : '' ) );
			if ( is_wp_error( $ds ) || ! $ds ) {
				return '';
			}
			$li = '';
			foreach ( $ds as $t ) {
				$li .= '<li><a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . ' <span class="cadao-tim-phu">(' . (int) $t->count . ')</span></a></li>';
			}
			return '<section aria-label="' . esc_attr( $tieu ) . '"><h2>' . esc_html( $tieu ) . '</h2><ul class="cadao-the">' . $li . '</ul></section>';
		};
		// Bản đồ ca dao (T-104): khi đã có địa danh được xác nhận
		global $wpdb;
		$ban_do = function_exists( 'cadao_url_ban_do' ) && cadao_url_ban_do() && $wpdb->get_var( "SELECT 1 FROM {$wpdb->termmeta} WHERE meta_key = '_cadao_wd' AND meta_value LIKE '%\"toa_do\";a:%' LIMIT 1" )
			? '<p><a href="' . esc_url( cadao_url_ban_do() ) . '">Xem ca dao trên bản đồ <span aria-hidden="true">→</span></a></p>' : '';
		return $ra . $loi_vao( 'the_loai', 'Theo thể loại' ) . $loi_vao( 'vung', 'Theo vùng' ) . $ban_do;
	}
);

// ---------- Trang thể loại (T-094): giới thiệu (người sáng lập viết), bài tiêu biểu, lối vào chủ đề / vùng ----------

add_filter(
	'term_description',
	static function ( $mo_ta, $term_id, $taxonomy ) {
		if ( 'the_loai' !== $taxonomy || is_admin() ) {
			return $mo_ta;
		}
		global $wpdb;
		$the = get_term( $term_id, 'the_loai' );
		$ids = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => 'the_loai', 'terms' => $term_id ) ) ) );
		if ( ! $ids ) {
			return $mo_ta;
		}
		// Bài tiêu biểu: đọc nhiều nhất (30 ngày) trong thể loại; chưa có lượt đọc thì bài mới nhất
		$trong = implode( ',', array_map( 'intval', $ids ) );
		$tieu  = $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM ' . cadao_luot_doc_bang() . " WHERE post_id IN ($trong) AND ngay >= %s GROUP BY post_id ORDER BY SUM(so) DESC LIMIT 5", cadao_luot_doc_tu_ngay( 30 ) ) );
		$tieu  = $tieu ?: array_slice( $ids, 0, 5 );
		$ra    = '<section class="cadao-the-loai-them" aria-labelledby="cadao-tieu-bieu"><h2 id="cadao-tieu-bieu">Bài tiêu biểu</h2><ul class="cadao-tim-ds">';
		foreach ( $tieu as $id ) {
			$p   = get_post( (int) $id );
			$ra .= '<li><a href="' . esc_url( get_permalink( $p ) ) . '"><strong>' . esc_html( get_the_title( $p ) ) . '</strong></a><br><span class="cadao-tim-phu">' . esc_html( str_replace( "\n", ' / ', cadao_loi_rut_gon( $p, 2 ) ) ) . '</span></li>';
		}
		$ra .= '</ul>';
		foreach ( array( 'tro_choi' => 'Theo trò chơi', 'chu_de' => 'Theo chủ đề', 'vung' => 'Theo vùng' ) as $tax => $ten ) {
			$dem = array();
			foreach ( wp_get_object_terms( $ids, $tax ) as $t ) {
				$dem[ $t->term_id ] = ( $dem[ $t->term_id ] ?? array( $t, 0 ) );
				++$dem[ $t->term_id ][1];
			}
			if ( $dem ) {
				$ra .= '<h2>' . esc_html( $ten ) . '</h2><ul class="cadao-the">';
				foreach ( $dem as [ $t ] ) {
					$ra .= '<li><a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a></li>';
				}
				$ra .= '</ul>';
			}
		}
		return $mo_ta . $ra . '</section><h2 class="cadao-tu-dien-bai">' . esc_html( sprintf( 'Tất cả %s (%d bài)', mb_strtolower( $the->name ), count( $ids ) ) ) . '</h2>';
	},
	10,
	3
);

// Trang tình huống: lời mở đầu (người biên soạn viết), mỗi bài lời rút gọn ≤ 4 dòng + "Hợp khi…"
add_filter(
	'term_description',
	static function ( $mo_ta, $term_id, $taxonomy ) {
		if ( 'tinh_huong' !== $taxonomy || is_admin() ) {
			return $mo_ta;
		}
		$ds = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'numberposts' => 50, 'tax_query' => array( array( 'taxonomy' => 'tinh_huong', 'terms' => $term_id ) ) ) );
		$ra = '<ul class="cadao-tinh-huong-ds">';
		foreach ( $ds as $p ) {
			$hop = (string) get_post_meta( $p->ID, 'hop_khi', true );
			$ra .= '<li><p class="cadao-th-loi"><a href="' . esc_url( get_permalink( $p ) ) . '">' . nl2br( esc_html( cadao_loi_rut_gon( $p, 4 ) ) ) . '</a></p>'
				. ( '' !== $hop ? '<p class="cadao-tim-phu">Hợp khi ' . esc_html( preg_replace( '/^hợp khi\s*/iu', '', $hop ) ) . '</p>' : '' ) . '</li>';
		}
		return $mo_ta . $ra . '</ul>';
	},
	10,
	3
);
