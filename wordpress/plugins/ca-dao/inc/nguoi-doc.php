<?php
/**
 * Người đọc góp phần (T-045, D-062, docs/25 mục 4.6): form "Góp ý cho bài này" (ở quê nói khác / góp ý chú thích /
 * báo lỗi / có sách: gửi số trang / báo vi phạm) và "Cảm nghĩ của bạn" (bình luận WordPress). Mọi thứ vào Chờ duyệt,
 * không tự hiện. Không bắt tên thật, email, số điện thoại; không cookie (bỏ cookie bình luận của WordPress). Chống rác:
 * ô bẫy ẩn, giới hạn số lần gửi mỗi giờ (giữ dấu băm của địa chỉ mạng trong bộ nhớ tạm một giờ, không lưu IP), và
 * Cloudflare Turnstile khi người sáng lập dán khóa vào trang "Chống rác" (khóa không gửi cho agent).
 */

defined( 'ABSPATH' ) || exit;

const CADAO_LOAI_GOP_Y   = 'gop_y';
const CADAO_TRANG_RAC    = 'cadao-chong-rac';
const CADAO_GUI_MOI_GIO  = 5;
const CADAO_KIEU_GOP_Y   = array(
	'di_ban'   => 'Ở quê tôi câu này nói khác',
	'chu_thich' => 'Góp ý chú thích',
	'bao_loi'  => 'Báo lỗi',
	'so_trang' => 'Tôi có sách: gửi tên sách, số trang',
	'vi_pham'  => 'Báo vi phạm (cảm nghĩ, bài phân tích)',
);

/** Thông báo quyền riêng tư ngắn, đặt ngay trong mọi form (Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15). */
function cadao_thong_bao_rieng_tu(): string {
	return '<p class="cadao-rieng-tu">Không cần tên thật, email hay số điện thoại. Những gì bạn gửi chỉ người biên soạn đọc, được lưu trên máy chủ của dự án (đặt ở nước ngoài, qua Cloudflare), chỉ dùng để sửa và bổ sung kho; theo Luật Bảo vệ dữ liệu cá nhân (91/2025/QH15). Muốn xin gỡ điều đã gửi: gửi một "Báo lỗi" ghi "xin gỡ" và ngày bạn gửi.</p>';
}

// ---------- Chống rác ----------

function cadao_turnstile(): array {
	return array( 'site' => (string) get_option( 'cadao_turnstile_site', '' ), 'bi_mat' => (string) get_option( 'cadao_turnstile_bi_mat', '' ) );
}

/** Ô Turnstile (chỉ khi đã có khóa) và ô bẫy ẩn — chèn vào mọi form người đọc gửi. */
function cadao_o_chong_rac(): string {
	$t = cadao_turnstile();
	return '<p class="cadao-bay" aria-hidden="true"><label>Đừng điền ô này <input type="text" name="cadao_web" tabindex="-1" autocomplete="off"></label></p>'
		. ( '' !== $t['site'] ? '<div class="cf-turnstile" data-sitekey="' . esc_attr( $t['site'] ) . '" data-language="vi"></div>' : '' );
}

/**
 * Kiểm một lần gửi: ô bẫy trống, chưa quá số lần mỗi giờ, Turnstile đạt (khi có khóa).
 *
 * @return true|string true hoặc lời báo lỗi cho người đọc
 */
function cadao_kiem_gui( array $du_lieu ) {
	if ( '' !== trim( (string) ( $du_lieu['cadao_web'] ?? '' ) ) ) {
		return 'Không gửi được.'; // ô bẫy có chữ: máy tự động
	}
	$khoa = 'cadao_gui_' . substr( wp_hash( cadao_ip_nguoi_gui() . gmdate( 'YmdH' ) ), 0, 20 ); // chỉ dấu băm, hết hạn sau một giờ
	$so   = (int) get_transient( $khoa );
	if ( $so >= CADAO_GUI_MOI_GIO ) {
		return 'Bạn đã gửi nhiều lần trong giờ này. Xin thử lại sau ít phút.';
	}
	$t = cadao_turnstile();
	if ( '' !== $t['bi_mat'] ) {
		$tl = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', array( 'timeout' => 10, 'body' => array( 'secret' => $t['bi_mat'], 'response' => (string) ( $du_lieu['cf-turnstile-response'] ?? '' ) ) ) );
		$j  = is_wp_error( $tl ) ? null : json_decode( wp_remote_retrieve_body( $tl ), true );
		if ( empty( $j['success'] ) ) {
			return 'Chưa qua bước kiểm tra chống tin rác. Xin thử lại.';
		}
	}
	set_transient( $khoa, $so + 1, HOUR_IN_SECONDS );
	return true;
}

// Turnstile: chỉ tải khi người đọc bắt đầu điền form (ca-dao.js), và chỉ khi đã có khóa
add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( '' !== cadao_turnstile()['site'] ) {
			wp_add_inline_script( 'ca-dao', 'window.cadaoCauHinh=window.cadaoCauHinh||{};window.cadaoCauHinh.turnstile=1;', 'before' );
		}
	},
	20
);

add_action(
	'admin_menu',
	static function () {
		add_submenu_page( 'edit.php?post_type=' . CADAO_LOAI_BAI, 'Chống rác', 'Chống rác', 'manage_options', CADAO_TRANG_RAC, 'cadao_trang_chong_rac' );
	},
	16
);

function cadao_trang_chong_rac(): void {
	$t = cadao_turnstile();
	echo '<div class="wrap"><h1>Chống rác cho form người đọc</h1>';
	cadao_hien_bao();
	echo '<p>Form góp ý, cảm nghĩ, bài phân tích luôn có ô bẫy ẩn và giới hạn ' . CADAO_GUI_MOI_GIO . ' lần gửi mỗi giờ. Muốn chặn chắc hơn, bật <strong>Cloudflare Turnstile</strong> (miễn phí): vào Cloudflare → Turnstile → Add widget, tên miền <code>' . esc_html( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) . '</code>, chế độ "Managed", rồi dán hai khóa vào đây. <strong>Không gửi khóa bí mật cho ai, kể cả agent.</strong></p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'cadao_chong_rac' );
	echo '<input type="hidden" name="action" value="cadao_chong_rac"><table class="form-table" role="presentation"><tbody>';
	echo '<tr><th scope="row"><label for="cadao-ts-site">Site key</label></th><td><input class="regular-text" id="cadao-ts-site" name="site" value="' . esc_attr( $t['site'] ) . '"></td></tr>';
	echo '<tr><th scope="row"><label for="cadao-ts-bi-mat">Secret key</label></th><td><input class="regular-text" type="password" id="cadao-ts-bi-mat" name="bi_mat" value="" placeholder="' . ( '' !== $t['bi_mat'] ? 'đã lưu — để trống thì giữ nguyên' : '' ) . '" autocomplete="off"></td></tr>';
	echo '</tbody></table>';
	submit_button( 'Lưu' );
	echo '</form><p class="description">Muốn tắt Turnstile: xóa Site key rồi Lưu.</p></div>';
}

add_action(
	'admin_post_cadao_chong_rac',
	static function () {
		cadao_kiem_quyen( 'cadao_chong_rac' );
		$site = sanitize_text_field( wp_unslash( $_POST['site'] ?? '' ) );
		update_option( 'cadao_turnstile_site', $site, false );
		$bi_mat = sanitize_text_field( wp_unslash( $_POST['bi_mat'] ?? '' ) );
		if ( '' === $site ) {
			update_option( 'cadao_turnstile_bi_mat', '', false );
		} elseif ( '' !== $bi_mat ) {
			update_option( 'cadao_turnstile_bi_mat', $bi_mat, false );
		}
		cadao_ve( CADAO_TRANG_RAC, '' !== $site ? 'Đã lưu. Turnstile bật cho các form.' : 'Đã tắt Turnstile.' );
	}
);

// ---------- Góp ý cho bài (loại bài nội bộ gop_y, luôn Chờ duyệt) ----------

add_action(
	'init',
	static function () {
		register_post_type(
			CADAO_LOAI_GOP_Y,
			array(
				'labels'       => array( 'name' => 'Góp ý của người đọc', 'singular_name' => 'Góp ý', 'all_items' => 'Góp ý của người đọc', 'edit_item' => 'Xem góp ý' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'edit.php?post_type=' . CADAO_LOAI_BAI,
				'supports'     => array( 'title', 'editor' ),
				'capability_type' => 'post',
			)
		);
	}
);

/** Form "Góp ý cho bài này" (tắt JavaScript vẫn gửi được). */
function cadao_form_gop_y( WP_Post $bai ): string {
	$bao  = sanitize_key( wp_unslash( $_GET['gop_y'] ?? '' ) );
	$chon = sanitize_key( wp_unslash( $_GET['gui'] ?? '' ) );
	$vi_pham = isset( $_GET['bao_vi_pham'] ) ? (int) $_GET['bao_vi_pham'] : 0;
	if ( $vi_pham ) {
		$chon = 'vi_pham';
	}
	$ra  = '<section class="cadao-gop-y" id="gop-y" aria-labelledby="cadao-gop-y-tieu"><h2 id="cadao-gop-y-tieu">Góp ý cho bài này</h2>';
	if ( 'da_gui' === $bao ) {
		$ra .= '<p class="cadao-bao-gui" role="status"><strong>Cảm ơn bạn!</strong> Góp ý đã tới người biên soạn; sẽ hiện trên trang khi được duyệt.</p>';
	} elseif ( $bao ) {
		$ra .= '<p class="cadao-bao-gui" role="alert">' . esc_html( rawurldecode( sanitize_text_field( wp_unslash( $_GET['ly_do'] ?? 'Chưa gửi được.' ) ) ) ) . '</p>';
	}
	$ra .= '<p class="cadao-gop-y-nut cadao-can-js" hidden>';
	foreach ( array( 'di_ban' => 'Ở quê bạn câu này nói khác?', 'chu_thich' => 'Góp ý chú thích', 'bao_loi' => 'Báo lỗi', 'so_trang' => 'Bạn có sách? Gửi số trang' ) as $k => $nhan ) {
		$ra .= '<a class="cadao-gop-y-mo" href="' . esc_url( add_query_arg( 'gui', $k, get_permalink( $bai ) ) . '#gop-y' ) . '" data-loai="' . esc_attr( $k ) . '">' . esc_html( $nhan ) . '</a> ';
	}
	$ra .= '</p><details class="cadao-gop-y-hop"' . ( $chon || $bao ? ' open' : '' ) . '><summary>Viết góp ý</summary>';
	$ra .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="cadao-form">';
	$ra .= wp_nonce_field( 'cadao_gop_y', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="cadao_gop_y"><input type="hidden" name="bai" value="' . (int) $bai->ID . '"><input type="hidden" name="vi_pham" value="' . (int) $vi_pham . '">';
	$ra .= '<fieldset><legend>Bạn muốn gửi gì? <span aria-hidden="true">*</span></legend>';
	foreach ( CADAO_KIEU_GOP_Y as $k => $nhan ) {
		if ( 'vi_pham' === $k && 'vi_pham' !== $chon ) {
			continue;
		}
		$ra .= '<label><input type="radio" name="loai" value="' . esc_attr( $k ) . '" required ' . checked( $chon, $k, false ) . '> ' . esc_html( $nhan ) . '</label>';
	}
	$ra .= '</fieldset>';
	$ra .= '<p><label for="cadao-gy-nd">Nội dung <span aria-hidden="true">*</span><span class="cadao-goi-y-o">Dị bản: ghi đúng như bạn nhớ, mỗi câu một dòng. Báo lỗi: chữ nào, dòng nào, đúng ra là gì. Sách: tên sách, năm, trang.</span></label><textarea id="cadao-gy-nd" name="noi_dung" rows="5" required maxlength="4000">' . ( $vi_pham ? esc_textarea( 'Cảm nghĩ / bài phân tích số ' . $vi_pham . ' vi phạm vì: ' ) : '' ) . '</textarea></p>';
	$ra .= '<p><label for="cadao-gy-noi">Bạn nghe ở đâu? (tùy chọn)<span class="cadao-goi-y-o">Tên làng, xã, tỉnh mà người kể quen gọi</span></label><input id="cadao-gy-noi" name="noi_nghe" maxlength="200"></p>';
	$ra .= '<p><label for="cadao-gy-ke">Ai kể, người đó là gì với bạn? (tùy chọn)<span class="cadao-goi-y-o">Ví dụ: bà nội tôi, sinh khoảng 1940 — không cần tên</span></label><input id="cadao-gy-ke" name="nguoi_ke" maxlength="200"></p>';
	$ra .= '<p><label for="cadao-gy-ten">Tên hiển thị nếu được ghi công (tùy chọn)<span class="cadao-goi-y-o">Bút danh cũng được; để trống: "Một bạn đọc"</span></label><input id="cadao-gy-ten" name="ten_hien_thi" maxlength="80"></p>';
	$ra .= '<p><label><input type="checkbox" name="dong_y" value="1" required> Tôi đồng ý để dự án dùng nội dung này theo giấy phép CC BY 4.0 <span aria-hidden="true">*</span></label></p>';
	$ra .= cadao_o_chong_rac() . cadao_thong_bao_rieng_tu() . '<p><button type="submit">Gửi góp ý</button></p></form></details></section>';
	return $ra;
}

add_action( 'admin_post_cadao_gop_y', 'cadao_nhan_gop_y' );
add_action( 'admin_post_nopriv_cadao_gop_y', 'cadao_nhan_gop_y' );

/** Nhận một góp ý: kiểm, lưu ở Chờ duyệt, về lại trang bài. */
function cadao_nhan_gop_y(): void {
	$p   = wp_unslash( $_POST );
	$bai = get_post( (int) ( $p['bai'] ?? 0 ) );
	$kq  = wp_verify_nonce( (string) ( $p['_wpnonce'] ?? '' ), 'cadao_gop_y' ) ? cadao_luu_gop_y( $p ) : 'Trang đã cũ, xin tải lại rồi gửi lại.';
	wp_safe_redirect( add_query_arg( is_int( $kq ) ? array( 'gop_y' => 'da_gui' ) : array( 'gop_y' => 'loi', 'ly_do' => rawurlencode( $kq ) ), $bai ? get_permalink( $bai ) : home_url( '/' ) ) . '#gop-y' );
	exit;
}

/**
 * Lưu một góp ý ở Chờ duyệt.
 *
 * @return int|string id góp ý, hoặc lời báo lỗi cho người đọc
 */
function cadao_luu_gop_y( array $p ) {
	$bai = get_post( (int) ( $p['bai'] ?? 0 ) );
	if ( ! $bai || CADAO_LOAI_BAI !== $bai->post_type ) {
		return 'Không thấy bài này.';
	}
	$loai = sanitize_key( $p['loai'] ?? '' );
	$nd   = trim( sanitize_textarea_field( (string) ( $p['noi_dung'] ?? '' ) ) );
	if ( ! isset( CADAO_KIEU_GOP_Y[ $loai ] ) || '' === $nd || empty( $p['dong_y'] ) ) {
		return 'Xin chọn loại góp ý, ghi nội dung và đánh dấu đồng ý.';
	}
	$kiem = cadao_kiem_gui( $p );
	if ( true !== $kiem ) {
		return $kiem;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => CADAO_LOAI_GOP_Y,
			'post_status'  => 'pending',
			'post_title'   => CADAO_KIEU_GOP_Y[ $loai ] . ' — ' . get_the_title( $bai ),
			'post_content' => $nd,
			'post_author'  => 0,
			'meta_input'   => array(
				'_cadao_loai'      => $loai,
				'_cadao_bai'       => $bai->ID,
				'_cadao_vi_pham'   => (int) ( $p['vi_pham'] ?? 0 ),
				'_cadao_noi_nghe'  => sanitize_text_field( (string) ( $p['noi_nghe'] ?? '' ) ),
				'_cadao_nguoi_ke'  => sanitize_text_field( (string) ( $p['nguoi_ke'] ?? '' ) ),
				'_cadao_ten'       => sanitize_text_field( (string) ( $p['ten_hien_thi'] ?? '' ) ),
			),
		)
	);
	return is_wp_error( $id ) ? 'Chưa gửi được, xin thử lại.' : (int) $id;
}

// Danh sách góp ý trong trang quản trị: loại, bài, người kể, nơi nghe
add_filter(
	'manage_' . CADAO_LOAI_GOP_Y . '_posts_columns',
	static fn( $cot ) => array_slice( $cot, 0, 2, true ) + array( 'cadao_bai' => 'Bài', 'cadao_them' => 'Nơi nghe · người kể · tên' ) + array_slice( $cot, 2, null, true )
);
add_action(
	'manage_' . CADAO_LOAI_GOP_Y . '_posts_custom_column',
	static function ( $cot, $id ) {
		if ( 'cadao_bai' === $cot ) {
			$b = (int) get_post_meta( $id, '_cadao_bai', true );
			echo $b ? '<a href="' . esc_url( (string) get_edit_post_link( $b ) ) . '">' . esc_html( get_the_title( $b ) ) . '</a>' : '—';
		} elseif ( 'cadao_them' === $cot ) {
			echo esc_html( implode( ' · ', array_filter( array( get_post_meta( $id, '_cadao_noi_nghe', true ), get_post_meta( $id, '_cadao_nguoi_ke', true ), get_post_meta( $id, '_cadao_ten', true ) ) ) ) ?: '—' );
		}
	},
	10,
	2
);

// ---------- Cảm nghĩ (bình luận WordPress): luôn chờ duyệt, không bắt email, không cookie ----------

add_action( 'init', static fn() => add_post_type_support( CADAO_LOAI_BAI, 'comments' ), 12 );
add_filter( 'comments_open', static fn( $mo, $id ) => CADAO_LOAI_BAI === get_post_type( $id ) ? 'publish' === get_post_status( $id ) : $mo, 10, 2 );
add_filter( 'pre_option_require_name_email', '__return_zero' ); // không bắt tên, email
remove_action( 'set_comment_cookies', 'wp_set_comment_cookies' ); // không cookie lưu tên người bình luận

add_filter(
	'pre_comment_approved',
	static function ( $duyet, $bl ) {
		return CADAO_LOAI_BAI === get_post_type( (int) $bl['comment_post_ID'] ) && ! current_user_can( 'moderate_comments' ) ? 0 : $duyet;
	},
	99,
	2
);

add_filter(
	'preprocess_comment',
	static function ( $bl ) {
		if ( CADAO_LOAI_BAI !== get_post_type( (int) $bl['comment_post_ID'] ) || is_user_logged_in() ) {
			return $bl;
		}
		$kiem = cadao_kiem_gui( wp_unslash( $_POST ) );
		if ( true !== $kiem ) {
			wp_die( esc_html( $kiem ), 'Chưa gửi được', array( 'response' => 429, 'back_link' => true ) );
		}
		$bl['comment_author']       = '' !== trim( $bl['comment_author'] ) ? $bl['comment_author'] : 'Một bạn đọc';
		$bl['comment_author_email'] = '';
		$bl['comment_author_url']   = '';
		return $bl;
	}
);

add_filter(
	'comment_form_defaults',
	static function ( $mac_dinh ) {
		if ( CADAO_LOAI_BAI !== get_post_type() ) {
			return $mac_dinh;
		}
		$mac_dinh['title_reply']          = 'Cảm nghĩ của bạn';
		$mac_dinh['label_submit']         = 'Gửi cảm nghĩ';
		$mac_dinh['comment_notes_before'] = '<p class="cadao-goi-y-o">Vài dòng cảm nghĩ về câu này. Cảm nghĩ chỉ hiện sau khi người biên soạn duyệt.</p>';
		$mac_dinh['comment_notes_after']  = cadao_o_chong_rac() . cadao_thong_bao_rieng_tu();
		$mac_dinh['comment_field']        = '<p class="comment-form-comment"><label for="comment">Cảm nghĩ <span aria-hidden="true">*</span></label><textarea id="comment" name="comment" rows="4" maxlength="2000" required></textarea></p>';
		$mac_dinh['fields']               = array( 'author' => '<p class="comment-form-author"><label for="author">Tên hiển thị (tùy chọn, bút danh cũng được)</label><input id="author" name="author" type="text" maxlength="80" autocomplete="nickname"></p>' );
		return $mac_dinh;
	}
);

// Cảm nghĩ đã duyệt: có liên kết "Báo vi phạm" (mở form góp ý loại vi phạm)
add_filter(
	'comment_text',
	static function ( $chu, $bl ) {
		if ( ! $bl || CADAO_LOAI_BAI !== get_post_type( (int) $bl->comment_post_ID ) || is_admin() ) {
			return $chu;
		}
		return $chu . '<p class="cadao-bao-vi-pham"><a href="' . esc_url( add_query_arg( 'bao_vi_pham', (int) $bl->comment_ID, get_permalink( (int) $bl->comment_post_ID ) ) . '#gop-y' ) . '">Báo vi phạm</a></p>';
	},
	10,
	2
);

// Form góp ý cuối bài (sau nội dung, trước Cảm nghĩ)
add_filter(
	'the_content',
	static function ( $noi_dung ) {
		if ( is_singular( CADAO_LOAI_BAI ) && get_the_ID() === get_queried_object_id() && ! doing_filter( 'get_the_excerpt' ) ) {
			$noi_dung .= cadao_form_gop_y( get_post() );
		}
		return $noi_dung;
	},
	40
);
