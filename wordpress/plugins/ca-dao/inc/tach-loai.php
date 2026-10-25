<?php
/**
 * Tách thành ngữ và tục ngữ (T-080, docs/25 mục 4.4): Ca dao Mẹ gộp hai loại trong một nhóm; máy GỢI Ý loại cho từng
 * câu, người sáng lập duyệt hàng loạt. Không câu nào đổi loại khi chưa duyệt. Bài đã đăng đổi thể loại thì đường dẫn
 * cũ (/tuc-ngu/… ↔ /thanh-ngu/…) chuyển hướng 301 sang đường dẫn mới.
 *
 * Gợi ý: có trong danh mục Wiktionary (CC BY-SA; chỉ lưu dấu vân tay, dùng nội bộ, không hiện ra trang) thì theo đó,
 * nhưng chỉ khi độ dài câu cùng chỉ một hướng (danh mục của Wiktionary lẫn cả từ đơn); còn lại theo cấu trúc câu:
 * nhiều vế, câu dài, hai vế cách nhau dấu phẩy → tục ngữ (chắc); có từ nối "thì, là, mới…" → nghiêng về tục ngữ;
 * cụm ngắn ≤ 4 âm tiết → nghiêng về thành ngữ; còn lại chưa rõ.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRANG_TACH_LOAI = 'cadao-tach-loai';
const CADAO_LOAI_TACH       = array( 'thanh_ngu' => 'Thành ngữ', 'tuc_ngu' => 'Tục ngữ' );
const CADAO_WIKTIONARY_DANH_MUC = array(
	'thanh_ngu' => 'Thể loại:Thành ngữ tiếng Việt',
	'tuc_ngu'   => 'Thể loại:Tục ngữ tiếng Việt',
);

/** Tải lại danh mục thành ngữ, tục ngữ của Wiktionary tiếng Việt (vài yêu cầu, nhịp 1 giây). */
function cadao_wiktionary_cap_nhat(): bool {
	$ra = array( 'ngay' => gmdate( 'Y-m-d' ) );
	foreach ( CADAO_WIKTIONARY_DANH_MUC as $loai => $ten ) {
		$ra[ $loai ] = array();
		$tiep        = '';
		for ( $i = 0; $i < 20; $i++ ) {
			$url = add_query_arg(
				array_filter( array( 'action' => 'query', 'list' => 'categorymembers', 'cmtitle' => $ten, 'cmlimit' => 500, 'cmnamespace' => 0, 'format' => 'json', 'cmcontinue' => $tiep ) ),
				'https://vi.wiktionary.org/w/api.php'
			);
			$tl  = wp_remote_get( $url, array( 'timeout' => 20, 'user-agent' => CADAO_UA ) );
			$j   = is_wp_error( $tl ) ? null : json_decode( wp_remote_retrieve_body( $tl ), true );
			if ( ! isset( $j['query']['categorymembers'] ) ) {
				return false; // giữ danh mục cũ, lần sau thử lại
			}
			foreach ( $j['query']['categorymembers'] as $m ) {
				$ra[ $loai ][ cadao_van_tay( (string) $m['title'] )['vt1'] ] = 1;
			}
			$tiep = (string) ( $j['continue']['cmcontinue'] ?? '' );
			if ( '' === $tiep ) {
				break;
			}
			sleep( (int) apply_filters( 'cadao_nhip_giay', 1 ) );
		}
	}
	update_option( 'cadao_wiktionary', $ra, false );
	return true;
}

add_action( 'cadao_wiktionary_hang_tuan', 'cadao_wiktionary_cap_nhat' );
add_action(
	'init',
	static function () {
		if ( ! wp_next_scheduled( 'cadao_wiktionary_hang_tuan' ) ) {
			wp_schedule_event( time() + 5 * MINUTE_IN_SECONDS, 'weekly', 'cadao_wiktionary_hang_tuan' );
		}
	}
);

/**
 * Gợi ý loại cho một câu thành ngữ – tục ngữ.
 *
 * @return array{loai: string, muc: string, ly_do: string} loai: thanh_ngu | tuc_ngu | '' ; muc: chac | nghieng | chua
 */
function cadao_goi_y_loai( string $loi ): array {
	$v   = cadao_van_tay( $loi );
	$w   = get_option( 'cadao_wiktionary', array() );
	$am  = $v['so_am'];
	$chu = ' ' . cadao_chuan_loi( $loi ) . ' ';
	if ( isset( $w['thanh_ngu'][ $v['vt1'] ] ) && $am <= 6 ) {
		return array( 'loai' => 'thanh_ngu', 'muc' => 'chac', 'ly_do' => 'có trong danh mục thành ngữ của Wiktionary' );
	}
	if ( isset( $w['tuc_ngu'][ $v['vt1'] ] ) && $am >= 5 ) {
		return array( 'loai' => 'tuc_ngu', 'muc' => 'chac', 'ly_do' => 'có trong danh mục tục ngữ của Wiktionary' );
	}
	if ( $v['so_dong'] >= 2 ) {
		return array( 'loai' => 'tuc_ngu', 'muc' => 'chac', 'ly_do' => 'câu nhiều vế' );
	}
	if ( $am >= 8 ) {
		return array( 'loai' => 'tuc_ngu', 'muc' => 'chac', 'ly_do' => 'câu dài, trọn ý' );
	}
	if ( $am >= 6 && str_contains( $loi, ',' ) ) {
		return array( 'loai' => 'tuc_ngu', 'muc' => 'chac', 'ly_do' => 'hai vế' );
	}
	if ( preg_match( '/ (thì|là|mới|hơn|thà|chớ|đừng|chẳng bằng|không bằng) /u', $chu ) ) {
		return array( 'loai' => 'tuc_ngu', 'muc' => 'nghieng', 'ly_do' => 'có từ nối (thì, là, mới, hơn…): thường là câu trọn ý' );
	}
	if ( $am <= 4 ) {
		return array( 'loai' => 'thanh_ngu', 'muc' => 'nghieng', 'ly_do' => 'cụm ngắn, chưa thành câu' );
	}
	return array( 'loai' => '', 'muc' => 'chua', 'ly_do' => '' );
}

/**
 * Ghi quyết định của người sáng lập cho một câu: kho nhớ loại; bài đã tạo đổi thể loại (thay Tục ngữ / Thành ngữ,
 * giữ các thể loại khác). $loai = '' để bỏ quyết định (bài giữ thể loại hiện có).
 */
function cadao_tach_loai_quyet( int $kho_id, string $loai ): bool {
	global $wpdb;
	$m = $wpdb->get_row( $wpdb->prepare( 'SELECT id, loai, du_lieu, post_id FROM ' . cadao_kho_bang() . ' WHERE id = %d', $kho_id ) );
	if ( ! $m || 'thanh_ngu_tuc_ngu' !== $m->loai || ( '' !== $loai && ! isset( CADAO_LOAI_TACH[ $loai ] ) ) ) {
		return false;
	}
	$d = json_decode( $m->du_lieu, true ) ?: array();
	if ( '' === $loai ) {
		unset( $d['loai_tach'] );
	} else {
		$d['loai_tach'] = $loai;
	}
	$wpdb->update( cadao_kho_bang(), array( 'du_lieu' => wp_json_encode( $d, JSON_UNESCAPED_UNICODE ) ), array( 'id' => $kho_id ) );
	if ( $m->post_id && '' !== $loai && get_post( (int) $m->post_id ) ) {
		$giu = array_values( array_diff( wp_get_object_terms( (int) $m->post_id, 'the_loai', array( 'fields' => 'slugs' ) ), array( 'tuc-ngu', 'thanh-ngu' ) ) );
		wp_set_object_terms( (int) $m->post_id, array_merge( array( 'thanh_ngu' === $loai ? 'thanh-ngu' : 'tuc-ngu' ), $giu ), 'the_loai' );
		clean_post_cache( (int) $m->post_id );
	}
	return true;
}

/** Thể loại (slug) cho bài tạo từ một câu kho: thành ngữ – tục ngữ đã được duyệt tách thì theo quyết định. */
function cadao_the_loai_tu_kho( object $muc ): ?string {
	if ( 'thanh_ngu_tuc_ngu' === $muc->loai ) {
		$tach = ( json_decode( $muc->du_lieu, true ) ?: array() )['loai_tach'] ?? '';
		if ( isset( CADAO_LOAI_TACH[ $tach ] ) ) {
			return 'thanh_ngu' === $tach ? 'thanh-ngu' : 'tuc-ngu';
		}
	}
	return CADAO_THE_LOAI_TU_LOAI_GOC[ $muc->loai ] ?? null;
}

// ---------- Đường dẫn cũ sau khi đổi thể loại: chuyển hướng 301 ----------

/** Đường dẫn đúng nếu đoạn thể loại trong đường dẫn yêu cầu khác của bài (ví dụ /tuc-ngu/x/ → /thanh-ngu/x/); null nếu đã đúng. */
function cadao_duong_dan_dung( string $duong_yeu_cau, WP_Post $bai ): ?string {
	$dung  = get_permalink( $bai );
	$doan  = static fn( $p ) => explode( '/', trim( (string) wp_parse_url( $p, PHP_URL_PATH ), '/' ) )[0] ?? '';
	$co    = $doan( $duong_yeu_cau );
	$phai  = $doan( $dung );
	return ( isset( CADAO_THE_LOAI[ $co ] ) && isset( CADAO_THE_LOAI[ $phai ] ) && $co !== $phai ) ? $dung : null;
}

add_action(
	'template_redirect',
	static function () {
		if ( ! is_singular( CADAO_LOAI_BAI ) || is_preview() ) {
			return;
		}
		$toi = cadao_duong_dan_dung( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), get_queried_object() );
		if ( $toi ) {
			wp_safe_redirect( $toi, 301 );
			exit;
		}
	}
);

// ---------- Trang "Tách thành ngữ – tục ngữ" ----------

add_action(
	'admin_menu',
	static function () {
		add_submenu_page( 'edit.php?post_type=' . CADAO_LOAI_BAI, 'Tách thành ngữ – tục ngữ', 'Tách thành ngữ – tục ngữ', 'manage_options', CADAO_TRANG_TACH_LOAI, 'cadao_trang_tach_loai' );
	},
	12
);

/** Mọi câu thành ngữ – tục ngữ có lời, kèm gợi ý và quyết định (nếu có). */
function cadao_tach_loai_danh_sach(): array {
	global $wpdb;
	$ra = array();
	foreach ( $wpdb->get_results( 'SELECT id, tieu_de, loi, du_lieu, post_id, trang_thai FROM ' . cadao_kho_bang() . " WHERE loai = 'thanh_ngu_tuc_ngu' AND loi <> '' AND trang_thai <> 'bo_qua' ORDER BY id" ) as $m ) {
		$d    = json_decode( $m->du_lieu, true ) ?: array();
		$ra[] = array( 'm' => $m, 'goi_y' => cadao_goi_y_loai( $m->loi ), 'quyet' => $d['loai_tach'] ?? '' );
	}
	return $ra;
}

function cadao_trang_tach_loai(): void {
	$ds   = cadao_tach_loai_danh_sach();
	$tabs = array( 'chac' => 'Máy đã chắc', 'nghieng' => 'Máy nghiêng về', 'chua' => 'Máy chưa rõ', 'da_quyet' => 'Bạn đã quyết' );
	$tab  = sanitize_key( wp_unslash( $_GET['tab'] ?? 'chac' ) );
	$tab  = isset( $tabs[ $tab ] ) ? $tab : 'chac';
	$nhom = array_fill_keys( array_keys( $tabs ), array() );
	foreach ( $ds as $x ) {
		$nhom[ $x['quyet'] ? 'da_quyet' : $x['goi_y']['muc'] ][] = $x;
	}
	$w = get_option( 'cadao_wiktionary', array() );
	echo '<div class="wrap"><h1>Tách thành ngữ – tục ngữ</h1>';
	cadao_hien_bao();
	echo '<p>Nguồn gộp thành ngữ và tục ngữ trong một nhóm. Máy gợi ý loại cho từng câu; <strong>không câu nào đổi khi bạn chưa duyệt</strong>. '
		. 'Chọn các câu rồi bấm <strong>Đồng ý gợi ý</strong> (hoặc chọn loại khác). Bài đã đăng đổi thể loại thì đường dẫn cũ tự chuyển sang đường dẫn mới. '
		. 'Thành ngữ: cụm từ cố định, chưa thành câu ("ăn cháo đá bát"). Tục ngữ: câu trọn ý, đúc kết kinh nghiệm ("có công mài sắt có ngày nên kim").</p>';
	echo '<p class="description">Danh mục Wiktionary: ' . ( isset( $w['ngay'] ) ? esc_html( sprintf( '%d thành ngữ, %d tục ngữ (cập nhật %s)', count( $w['thanh_ngu'] ?? array() ), count( $w['tuc_ngu'] ?? array() ), $w['ngay'] ) ) : 'chưa tải (máy tự tải trong vài phút)' ) . '. Câu chưa đọc trang gốc thì chưa có lời, chưa hiện ở đây.</p><ul class="subsubsub">';
	$i = 0;
	foreach ( $tabs as $k => $ten ) {
		echo ( $i++ ? ' | ' : '' ) . '<li><a ' . ( $tab === $k ? 'class="current" ' : '' ) . 'href="' . esc_url( cadao_url_trang( CADAO_TRANG_TACH_LOAI, array( 'tab' => $k ) ) ) . '">' . esc_html( $ten ) . ' <span class="count">(' . count( $nhom[ $k ] ) . ')</span></a></li>';
	}
	echo '</ul><div style="clear:both"></div>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'cadao_tach_loai' );
	echo '<input type="hidden" name="action" value="cadao_tach_loai"><input type="hidden" name="tab" value="' . esc_attr( $tab ) . '"><p style="display:flex;flex-wrap:wrap;gap:8px">';
	if ( 'da_quyet' !== $tab ) {
		echo '<button class="button button-primary" name="viec" value="dong_y">Đồng ý gợi ý cho câu đã chọn</button>';
	}
	echo '<button class="button" name="viec" value="thanh_ngu">Là thành ngữ</button><button class="button" name="viec" value="tuc_ngu">Là tục ngữ</button>';
	if ( 'da_quyet' === $tab ) {
		echo '<button class="button" name="viec" value="bo">Bỏ quyết định</button>';
	}
	echo '</p><table class="widefat striped"><thead><tr><td class="check-column"><input type="checkbox" aria-label="Chọn tất cả" onclick="document.querySelectorAll(\'.cadao-chon\').forEach(c=>c.checked=this.checked)"></td><th>Câu</th><th>' . ( 'da_quyet' === $tab ? 'Bạn đã chọn' : 'Máy gợi ý' ) . '</th><th>Bài</th></tr></thead><tbody>';
	foreach ( array_slice( $nhom[ $tab ], 0, 300 ) as $x ) {
		$m = $x['m'];
		$g = $x['goi_y'];
		echo '<tr><th class="check-column"><input class="cadao-chon" type="checkbox" name="chon[]" value="' . (int) $m->id . '" aria-label="Chọn"></th>';
		echo '<td style="white-space:pre-line">' . esc_html( $m->loi ) . '</td><td>';
		echo $x['quyet'] ? '<strong>' . esc_html( CADAO_LOAI_TACH[ $x['quyet'] ] ) . '</strong>' : ( $g['loai'] ? '<strong>' . esc_html( CADAO_LOAI_TACH[ $g['loai'] ] ) . '</strong><br><span class="description">' . esc_html( $g['ly_do'] ) . '</span>' : '<span class="description">Chưa rõ — bạn chọn</span>' );
		echo '</td><td>' . ( $m->post_id && get_post( (int) $m->post_id ) ? '<a href="' . esc_url( (string) get_edit_post_link( (int) $m->post_id ) ) . '">' . esc_html( cadao_ten_the_loai( get_post( (int) $m->post_id ) ) ) . '</a>' : '<span class="description">Chưa thành bài</span>' ) . '</td></tr>';
	}
	if ( ! $nhom[ $tab ] ) {
		echo '<tr><td colspan="4">Không có câu nào.</td></tr>';
	}
	echo '</tbody></table>';
	if ( count( $nhom[ $tab ] ) > 300 ) {
		echo '<p class="description">Hiện 300 câu đầu; duyệt xong các câu này thì các câu sau hiện tiếp.</p>';
	}
	echo '</form></div>';
}

add_action(
	'admin_post_cadao_tach_loai',
	static function () {
		cadao_kiem_quyen( 'cadao_tach_loai' );
		$viec = sanitize_key( wp_unslash( $_POST['viec'] ?? '' ) );
		$so   = 0;
		foreach ( array_map( 'intval', (array) wp_unslash( $_POST['chon'] ?? array() ) ) as $id ) {
			global $wpdb;
			$loi  = (string) $wpdb->get_var( $wpdb->prepare( 'SELECT loi FROM ' . cadao_kho_bang() . ' WHERE id = %d', $id ) );
			$loai = 'dong_y' === $viec ? cadao_goi_y_loai( $loi )['loai'] : ( 'bo' === $viec ? '' : $viec );
			if ( ( '' !== $loai || 'bo' === $viec ) && cadao_tach_loai_quyet( $id, $loai ) ) {
				++$so;
			}
		}
		cadao_ve( CADAO_TRANG_TACH_LOAI, "Đã ghi $so câu.", array( 'tab' => sanitize_key( wp_unslash( $_POST['tab'] ?? 'chac' ) ) ) );
	}
);
