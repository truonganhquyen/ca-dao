<?php
/**
 * Bài cảm nhận, phân tích của người đọc (T-097, D-062, docs/25 mục 4.6): loại bài "Cảm nhận – phân tích" gắn với một
 * bài ca dao. Người đọc gửi qua trang soạn đơn giản (/viet-cam-nhan/?bai=<id>) → Chờ duyệt → người sáng lập đăng thì
 * hiện dưới bài ca dao ("Cảm nhận, phân tích (N)") và có trang riêng /phan-tich/<tên>/. Người sáng lập tự viết được
 * "Bài phân tích của dự án" (nhãn riêng). Bài ca dao có ≥ 1 bài phân tích đã đăng thì được tính là có giá trị riêng.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_LOAI_PHAN_TICH = 'phan_tich';
const CADAO_VAI_TRO        = array( 'hoc_sinh' => 'Học sinh', 'giao_vien' => 'Giáo viên', 'yeu_ca_dao' => 'Người yêu ca dao' );

add_action(
	'init',
	static function () {
		register_post_type(
			CADAO_LOAI_PHAN_TICH,
			array(
				'labels'       => array( 'name' => 'Cảm nhận – phân tích', 'singular_name' => 'Bài cảm nhận', 'add_new_item' => 'Viết bài phân tích', 'edit_item' => 'Sửa bài phân tích', 'all_items' => 'Cảm nhận – phân tích' ),
				'public'       => true,
				'show_in_rest' => true,
				'show_in_menu' => 'edit.php?post_type=' . CADAO_LOAI_BAI,
				'supports'     => array( 'title', 'editor', 'author', 'revisions' ),
				'rewrite'      => array( 'slug' => 'phan-tich', 'with_front' => false ),
				'has_archive'  => false,
			)
		);
		foreach ( array( '_cadao_bai' => 'integer', '_cadao_but_danh' => 'string', '_cadao_vai_tro' => 'string', '_cadao_du_an' => 'boolean' ) as $k => $kieu ) {
			register_post_meta( CADAO_LOAI_PHAN_TICH, $k, array( 'type' => $kieu, 'single' => true, 'show_in_rest' => false, 'auth_callback' => static fn() => current_user_can( 'edit_posts' ) ) );
		}
	},
	11
);

/** Bài phân tích đã đăng của một bài ca dao. */
function cadao_phan_tich_cua_bai( int $bai ): array {
	return get_posts( array( 'post_type' => CADAO_LOAI_PHAN_TICH, 'post_status' => 'publish', 'numberposts' => 30, 'meta_key' => '_cadao_bai', 'meta_value' => $bai, 'orderby' => 'date', 'order' => 'ASC' ) );
}

// Có bài phân tích đã duyệt: bài ca dao có giá trị riêng → được Google đánh chỉ mục (D-062)
add_filter( 'cadao_co_gia_tri_rieng', static fn( $co, $bai ) => $co || (bool) cadao_phan_tich_cua_bai( (int) $bai->ID ), 10, 2 );

/** Dòng tác giả của bài phân tích: bút danh · vai trò, hoặc nhãn "Bài phân tích của dự án". */
function cadao_dong_tac_gia( WP_Post $p ): string {
	if ( get_post_meta( $p->ID, '_cadao_du_an', true ) ) {
		return '<span class="cadao-nhan-du-an">Bài phân tích của dự án</span>';
	}
	$ten = (string) get_post_meta( $p->ID, '_cadao_but_danh', true ) ?: 'Một bạn đọc';
	$vt  = CADAO_VAI_TRO[ (string) get_post_meta( $p->ID, '_cadao_vai_tro', true ) ] ?? '';
	return esc_html( $ten . ( $vt ? ' · ' . $vt : '' ) );
}

/** Lời người đọc gõ → khối: dòng trống tách đoạn; **đậm**; dòng bắt đầu bằng ">" là trích lời. Mọi chữ được thoát HTML. */
function cadao_van_ban_phan_tich( string $chu ): string {
	$khoi = array();
	foreach ( preg_split( "/\n\s*\n/u", str_replace( "\r", '', trim( $chu ) ) ) as $doan ) {
		$dong = array_map( 'trim', explode( "\n", $doan ) );
		$dam  = static fn( $s ) => preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', esc_html( $s ) );
		if ( $dong && array_reduce( $dong, static fn( $c, $d ) => $c && str_starts_with( $d, '>' ), true ) ) {
			$noi    = implode( '<br>', array_map( static fn( $d ) => $dam( ltrim( substr( $d, 1 ) ) ), $dong ) );
			$khoi[] = "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><!-- wp:paragraph -->\n<p>$noi</p>\n<!-- /wp:paragraph --></blockquote>\n<!-- /wp:quote -->";
		} elseif ( '' !== implode( '', $dong ) ) {
			$khoi[] = "<!-- wp:paragraph -->\n<p>" . implode( '<br>', array_map( $dam, $dong ) ) . "</p>\n<!-- /wp:paragraph -->";
		}
	}
	return implode( "\n\n", $khoi );
}

// ---------- Trang soạn cho người đọc: /viet-cam-nhan/?bai=<id> ----------

add_shortcode(
	'cadao_viet_phan_tich',
	static function () {
		$bai = get_post( (int) ( $_GET['bai'] ?? 0 ) );
		$bao = sanitize_key( wp_unslash( $_GET['phan_tich'] ?? '' ) );
		if ( 'da_gui' === $bao ) {
			return '<p class="cadao-bao-gui" role="status"><strong>Cảm ơn bạn!</strong> Bài đã tới người biên soạn; sẽ hiện dưới bài ca dao khi được duyệt.</p>';
		}
		if ( ! $bai || CADAO_LOAI_BAI !== $bai->post_type || 'publish' !== $bai->post_status ) {
			return '<p>Mở một bài ca dao rồi bấm "Viết cảm nhận của bạn" để viết về bài đó.</p>';
		}
		$ra  = $bao ? '<p class="cadao-bao-gui" role="alert">' . esc_html( cadao_bao_gui_tu_ma() ) . '</p>' : '';
		$ra .= '<p>Bạn viết về: <a href="' . esc_url( get_permalink( $bai ) ) . '"><strong>' . esc_html( get_the_title( $bai ) ) . '</strong></a></p>';
		$ra .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="cadao-form">' . wp_nonce_field( 'cadao_phan_tich', '_wpnonce', true, false );
		$ra .= '<input type="hidden" name="action" value="cadao_phan_tich"><input type="hidden" name="bai" value="' . (int) $bai->ID . '">';
		$ra .= '<p><label for="cadao-pt-tieu">Tiêu đề <span aria-hidden="true">*</span></label><input id="cadao-pt-tieu" name="tieu_de" required maxlength="150"></p>';
		$ra .= '<p><label for="cadao-pt-nd">Bài viết <span aria-hidden="true">*</span><span class="cadao-goi-y-o">Để một dòng trống giữa các đoạn. Viết <code>**chữ đậm**</code> để in đậm. Dòng bắt đầu bằng <code>&gt;</code> là trích lời ca dao.</span></label><textarea id="cadao-pt-nd" name="noi_dung" rows="14" required minlength="200" maxlength="20000"></textarea></p>';
		$ra .= '<p><label for="cadao-pt-ten">Bút danh (tùy chọn)<span class="cadao-goi-y-o">Không cần tên thật; để trống: "Một bạn đọc"</span></label><input id="cadao-pt-ten" name="but_danh" maxlength="80"></p>';
		$ra .= '<p><label for="cadao-pt-vt">Bạn là (tùy chọn)</label><select id="cadao-pt-vt" name="vai_tro"><option value="">—</option>';
		foreach ( CADAO_VAI_TRO as $k => $ten ) {
			$ra .= '<option value="' . esc_attr( $k ) . '">' . esc_html( $ten ) . '</option>';
		}
		$ra .= '</select></p>';
		$ra .= '<p><label><input type="checkbox" name="tu_viet" value="1" required> Bài do tôi tự viết <span aria-hidden="true">*</span></label></p>';
		$ra .= '<p><label><input type="checkbox" name="dong_y" value="1" required> Tôi đồng ý cho đăng theo giấy phép CC BY 4.0 (người khác được dùng lại, có ghi bút danh) <span aria-hidden="true">*</span></label></p>';
		return $ra . cadao_o_chong_rac() . cadao_thong_bao_rieng_tu() . '<p><button type="submit">Gửi bài</button></p></form>';
	}
);

add_action( 'admin_post_cadao_phan_tich', 'cadao_nhan_phan_tich' );
add_action( 'admin_post_nopriv_cadao_phan_tich', 'cadao_nhan_phan_tich' );

function cadao_nhan_phan_tich(): void {
	$p   = wp_unslash( $_POST );
	$kq  = wp_verify_nonce( (string) ( $p['_wpnonce'] ?? '' ), 'cadao_phan_tich' ) ? cadao_luu_phan_tich( $p ) : CADAO_BAO_GUI['cu'];
	$url = get_permalink( (int) get_option( 'cadao_trang_viet_phan_tich' ) ) ?: home_url( '/viet-cam-nhan/' );
	wp_safe_redirect( add_query_arg( array( 'bai' => (int) ( $p['bai'] ?? 0 ) ) + ( is_int( $kq ) ? array( 'phan_tich' => 'da_gui' ) : array( 'phan_tich' => 'loi', 'ma' => cadao_ma_bao_gui( $kq ) ) ), $url ) );
	exit;
}

/**
 * Lưu một bài phân tích người đọc gửi, ở Chờ duyệt.
 *
 * @return int|string id bài, hoặc lời báo lỗi cho người đọc
 */
function cadao_luu_phan_tich( array $p ) {
	$bai = get_post( (int) ( $p['bai'] ?? 0 ) );
	if ( ! $bai || CADAO_LOAI_BAI !== $bai->post_type ) {
		return CADAO_BAO_GUI['bai'];
	}
	$tieu = sanitize_text_field( (string) ( $p['tieu_de'] ?? '' ) );
	$nd   = sanitize_textarea_field( (string) ( $p['noi_dung'] ?? '' ) );
	if ( '' === $tieu || mb_strlen( $nd ) < 200 || empty( $p['tu_viet'] ) || empty( $p['dong_y'] ) ) {
		return CADAO_BAO_GUI['thieu_pt'];
	}
	$kiem = cadao_kiem_gui( $p );
	if ( true !== $kiem ) {
		return $kiem;
	}
	$vt = sanitize_key( (string) ( $p['vai_tro'] ?? '' ) );
	$id = wp_insert_post(
		array(
			'post_type'    => CADAO_LOAI_PHAN_TICH,
			'post_status'  => 'pending',
			'post_title'   => $tieu,
			'post_content' => cadao_van_ban_phan_tich( $nd ),
			'post_author'  => 0,
			'meta_input'   => array( '_cadao_bai' => $bai->ID, '_cadao_but_danh' => sanitize_text_field( (string) ( $p['but_danh'] ?? '' ) ), '_cadao_vai_tro' => isset( CADAO_VAI_TRO[ $vt ] ) ? $vt : '', '_cadao_du_an' => false ),
		)
	);
	return is_wp_error( $id ) ? CADAO_BAO_GUI['loi'] : (int) $id;
}

// Trang "Viết cảm nhận" tạo một lần
add_action(
	'init',
	static function () {
		if ( get_option( 'cadao_trang_viet_phan_tich' ) || wp_installing() ) {
			return;
		}
		$co = get_page_by_path( 'viet-cam-nhan' );
		$id = $co ? $co->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Viết cảm nhận', 'post_name' => 'viet-cam-nhan', 'post_content' => "<!-- wp:shortcode -->\n[cadao_viet_phan_tich]\n<!-- /wp:shortcode -->" ) );
		update_option( 'cadao_trang_viet_phan_tich', (int) $id, false );
	},
	30
);
add_filter( 'wp_robots', static fn( $r ) => is_page( (int) get_option( 'cadao_trang_viet_phan_tich' ) ) ? $r + array( 'noindex' => true ) : $r );

// ---------- Hiển thị ----------

// Dưới bài ca dao: "Cảm nhận, phân tích (N)" — chỉ khi đã có bài được duyệt; lời mời viết nằm trong khối "Bạn muốn góp gì?" (T-121)
add_filter(
	'the_content',
	static function ( $noi_dung ) {
		if ( ! is_singular( CADAO_LOAI_BAI ) || get_the_ID() !== get_queried_object_id() || doing_filter( 'get_the_excerpt' ) ) {
			return $noi_dung;
		}
		$ds = cadao_phan_tich_cua_bai( get_the_ID() );
		if ( ! $ds ) {
			return $noi_dung;
		}
		$ra = '<section class="cadao-phan-tich" aria-labelledby="cadao-pt-tieu-de"><h2 id="cadao-pt-tieu-de">Cảm nhận, phân tích (' . count( $ds ) . ')</h2><ul class="cadao-tim-ds">';
		foreach ( $ds as $p ) {
			$ra .= '<li><a href="' . esc_url( get_permalink( $p ) ) . '"><strong>' . esc_html( get_the_title( $p ) ) . '</strong></a><br><span class="cadao-tim-phu">' . cadao_dong_tac_gia( $p ) . '</span></li>';
		}
		return $noi_dung . $ra . '</ul></section>';
	},
	35
);

// Trang bài phân tích: tác giả, bài ca dao (4 dòng đầu, liên kết), nội dung, báo vi phạm
add_filter(
	'the_content',
	static function ( $noi_dung ) {
		if ( ! is_singular( CADAO_LOAI_PHAN_TICH ) || get_the_ID() !== get_queried_object_id() ) {
			return $noi_dung;
		}
		$p   = get_post();
		$bai = get_post( (int) get_post_meta( $p->ID, '_cadao_bai', true ) );
		$dau = '<p class="cadao-pt-tac-gia">' . cadao_dong_tac_gia( $p ) . '</p>';
		if ( $bai ) {
			$dau .= '<blockquote class="cadao-pt-bai"><p>' . nl2br( esc_html( cadao_loi_rut_gon( $bai, 4 ) ) ) . '</p><p class="cadao-tim-phu">Bài: <a href="' . esc_url( get_permalink( $bai ) ) . '">' . esc_html( get_the_title( $bai ) ) . '</a> · ' . esc_html( cadao_ten_the_loai( $bai ) ) . '</p></blockquote>';
		}
		$cuoi = get_post_meta( $p->ID, '_cadao_du_an', true ) ? '' : '<p class="cadao-tim-phu">Bài của bạn đọc, đăng theo giấy phép CC BY 4.0.' . ( $bai ? ' <a href="' . esc_url( add_query_arg( 'bao_vi_pham', $p->ID, get_permalink( $bai ) ) . '#gop-y' ) . '">Báo vi phạm</a>' : '' ) . '</p>';
		return $dau . $noi_dung . $cuoi;
	}
);

// Trình soạn của người sáng lập: bài ca dao (mã), "Bài phân tích của dự án", bút danh, vai trò
add_action(
	'add_meta_boxes_' . CADAO_LOAI_PHAN_TICH,
	static function () {
		add_meta_box(
			'cadao_pt',
			'Bài ca dao, người viết',
			static function ( WP_Post $p ) {
				wp_nonce_field( 'cadao_luu_pt', 'cadao_pt_nonce' );
				$bai = (int) get_post_meta( $p->ID, '_cadao_bai', true );
				echo '<p><label for="cadao_pt_bai">Mã bài ca dao (số ID)</label><input class="widefat" id="cadao_pt_bai" name="cadao_pt_bai" type="number" value="' . esc_attr( (string) ( $bai ?: '' ) ) . '">' . ( $bai ? '<span class="description">' . esc_html( get_the_title( $bai ) ) . '</span>' : '' ) . '</p>';
				echo '<p><label><input type="checkbox" name="cadao_pt_du_an" value="1" ' . checked( (bool) get_post_meta( $p->ID, '_cadao_du_an', true ), true, false ) . '> Bài phân tích của dự án</label></p>';
				echo '<p><label for="cadao_pt_ten">Bút danh</label><input class="widefat" id="cadao_pt_ten" name="cadao_pt_ten" value="' . esc_attr( (string) get_post_meta( $p->ID, '_cadao_but_danh', true ) ) . '"></p>';
			},
			CADAO_LOAI_PHAN_TICH,
			'side'
		);
	}
);

add_action(
	'save_post_' . CADAO_LOAI_PHAN_TICH,
	static function ( $id ) {
		if ( ! isset( $_POST['cadao_pt_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cadao_pt_nonce'] ), 'cadao_luu_pt' ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		update_post_meta( $id, '_cadao_bai', (int) ( $_POST['cadao_pt_bai'] ?? 0 ) );
		update_post_meta( $id, '_cadao_du_an', ! empty( $_POST['cadao_pt_du_an'] ) );
		update_post_meta( $id, '_cadao_but_danh', sanitize_text_field( wp_unslash( $_POST['cadao_pt_ten'] ?? '' ) ) );
	}
);

// Trang "Viết cảm nhận": nạp Turnstile khi đã có khóa (trang này không có ca-dao.js)
add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( is_page( (int) get_option( 'cadao_trang_viet_phan_tich' ) ) && '' !== cadao_turnstile()['site'] ) {
			wp_enqueue_script( 'cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, array( 'strategy' => 'async' ) ); // phiên bản do Cloudflare quản lý
		}
	}
);
