<?php
/**
 * Trợ giúp viết "Hiểu nhanh" (T-100, D-073; lằn ranh D-059, AGENTS.md). Hiểu nhanh là ô Tóm tắt (đoạn trích) của bài;
 * bài có Hiểu nhanh mới được Google lập chỉ mục. Mục tiêu trước khi mở: ≥ 50 bài.
 * - Trình soạn có hộp "Viết Hiểu nhanh": chú thích của bài để tham khảo; nháp máy (nếu có) ở ô riêng ghi rõ
 *   "Nháp máy — chưa duyệt", không bao giờ tự vào ô Tóm tắt. Người biên soạn bấm "Dùng nháp này" thì nháp chép vào ô
 *   Tóm tắt để sửa, và ô "có hỗ trợ của AI" được đánh dấu ngay trước mắt họ (bỏ được nếu đã viết lại hết); chỉ khi họ
 *   bấm Cập nhật thì mới lên trang.
 * - Nháp do agent soạn theo lô cho danh sách bài người sáng lập chọn, nạp bằng `wp cadao hieu-nhanh-nhap <tệp.json>`
 *   (chỉ ghi vào ô nháp nội bộ; ghi lên máy chủ khi người sáng lập đồng ý cho lần đó). Nháp không bao giờ hiện ra
 *   trang, không vào chỉ mục tìm, không ra REST công khai.
 * - Trang "Viết Hiểu nhanh": bài chưa có, đọc nhiều trước; Bàn biên tập có hàng đếm N/50.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_HN_MUC_TIEU = 50;
const CADAO_TRANG_HN    = 'cadao-hieu-nhanh';
const CADAO_HN_NHAP     = '_cadao_nhap_hieu_nhanh'; // gạch dưới đầu: meta ẩn, không ra REST

/** Số bài đang đăng đã có Hiểu nhanh. */
function cadao_hn_da_co(): int {
	global $wpdb;
	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND post_excerpt <> ''", CADAO_LOAI_BAI ) );
}

/**
 * Bài đang đăng chưa có Hiểu nhanh, đọc nhiều trong 30 ngày trước.
 *
 * @return array<int, array{id: int, ten: string, luot: int, co_nhap: bool}>
 */
function cadao_hn_can_viet( int $so = 50 ): array {
	global $wpdb;
	$ds = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT p.ID, p.post_title, COALESCE(SUM(l.so), 0) AS luot FROM {$wpdb->posts} p LEFT JOIN " . cadao_luot_doc_bang() . " l ON l.post_id = p.ID AND l.ngay >= %s
			WHERE p.post_type = %s AND p.post_status = 'publish' AND p.post_excerpt = '' GROUP BY p.ID, p.post_title ORDER BY luot DESC, p.post_title ASC LIMIT %d",
			cadao_luot_doc_tu_ngay( 30 ),
			CADAO_LOAI_BAI,
			$so
		)
	);
	return array_map( static fn( $r ) => array( 'id' => (int) $r->ID, 'ten' => (string) $r->post_title, 'luot' => (int) $r->luot, 'co_nhap' => '' !== (string) get_post_meta( (int) $r->ID, CADAO_HN_NHAP, true ) ), $ds );
}

/**
 * Chú thích của bài, dạng chữ (để người viết, và agent soạn nháp, tham khảo).
 *
 * @return string[]
 */
function cadao_hn_chu_thich( WP_Post $bai ): array {
	if ( ! preg_match_all( '~<(ul|ol)[^>]*class="[^"]*\bcadao-chu-thich\b[^"]*"[^>]*>(.*?)</\1>~s', $bai->post_content, $ds ) ) {
		return array();
	}
	$ra = array();
	foreach ( $ds[2] as $khoi ) {
		preg_match_all( '~<li[^>]*>(.*?)</li>~s', $khoi, $li );
		foreach ( $li[1] as $x ) {
			$ra[] = trim( html_entity_decode( wp_strip_all_tags( $x ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		}
	}
	return array_values( array_filter( $ra ) );
}

/**
 * Nạp một lô nháp (agent soạn): [{id, nhap}]. Chỉ ghi vào ô nháp nội bộ của bài đang đăng chưa có Hiểu nhanh;
 * không đụng ô Tóm tắt, trạng thái hay dấu "có hỗ trợ của AI".
 *
 * @param array<int, array{id?: int, nhap?: string}> $lo
 * @return array{da_nap: int, bo_qua: int}
 */
function cadao_hn_nap_lo( array $lo ): array {
	$ra = array( 'da_nap' => 0, 'bo_qua' => 0 );
	foreach ( $lo as $x ) {
		$bai  = get_post( (int) ( $x['id'] ?? 0 ) );
		$nhap = mb_substr( trim( sanitize_textarea_field( (string) ( $x['nhap'] ?? '' ) ) ), 0, 600 );
		if ( ! $bai || CADAO_LOAI_BAI !== $bai->post_type || 'publish' !== $bai->post_status || '' !== trim( $bai->post_excerpt ) || '' === $nhap ) {
			++$ra['bo_qua'];
			continue;
		}
		update_post_meta( $bai->ID, CADAO_HN_NHAP, $nhap );
		update_post_meta( $bai->ID, '_cadao_nhap_hn_luc', wp_date( 'Y-m-d' ) );
		++$ra['da_nap'];
	}
	return $ra;
}

// ---------- Hộp "Viết Hiểu nhanh" trong trình soạn ----------
add_action(
	'add_meta_boxes_' . CADAO_LOAI_BAI,
	static function () {
		add_meta_box( 'cadao_hieu_nhanh', 'Viết Hiểu nhanh', 'cadao_hop_hieu_nhanh', CADAO_LOAI_BAI, 'side', 'high' );
	}
);

function cadao_hop_hieu_nhanh( WP_Post $bai ): void {
	wp_nonce_field( 'cadao_luu_hn', 'cadao_hn_nonce' );
	$nhap = (string) get_post_meta( $bai->ID, CADAO_HN_NHAP, true );
	$ct   = cadao_hn_chu_thich( $bai );
	echo '<p class="description">Hiểu nhanh là ô <strong>Tóm tắt</strong> (thanh bên, mục Bài viết): 2–3 câu nói câu này nghĩa là gì, dùng khi nào. Bài có Hiểu nhanh mới được Google tìm thấy.</p>';
	if ( $ct ) {
		echo '<details><summary>Chú thích của bài (' . (int) count( $ct ) . ')</summary><ul style="list-style:disc;margin-left:1.2em">';
		foreach ( $ct as $x ) {
			echo '<li>' . esc_html( $x ) . '</li>';
		}
		echo '</ul></details>';
	}
	if ( '' !== $nhap ) {
		echo '<p><label for="cadao-hn-nhap"><strong>Nháp máy — chưa duyệt</strong></label><br><textarea id="cadao-hn-nhap" class="widefat" rows="5" readonly>' . esc_textarea( $nhap ) . '</textarea></p>'
			. '<p><button type="button" class="button" onclick="cadaoDungNhapHN()">Dùng nháp này</button></p>'
			. '<p class="description">Bấm dùng: nháp chép vào ô Tóm tắt để bạn đọc, sửa; ô "có hỗ trợ của AI" bên dưới được đánh dấu. Chỉ khi bạn bấm Cập nhật thì mới lên trang.</p>'
			. '<p class="cadao-hn-bao" role="status" aria-live="polite"></p>'
			. '<p><label><input type="checkbox" name="cadao_hn_xoa_nhap" value="1"> Xoá nháp máy khi lưu</label></p>';
		// Nhỏ, chỉ ở trang sửa bài: chép nháp vào ô Tóm tắt (trình soạn khối, hoặc trình soạn cổ điển)
		echo "<script>function cadaoDungNhapHN(){var n=document.getElementById('cadao-hn-nhap').value;var d=window.wp&&wp.data&&wp.data.dispatch&&wp.data.dispatch('core/editor');if(d&&d.editPost){d.editPost({excerpt:n});}else{var e=document.getElementById('excerpt');if(e){e.value=n;}}document.getElementById('cadao-ho-tro-ai').checked=true;document.querySelector('.cadao-hn-bao').textContent='Đã chép vào ô Tóm tắt. Đọc, sửa rồi bấm Cập nhật.';}</script>";
	}
	echo '<p><label><input type="checkbox" id="cadao-ho-tro-ai" name="cadao_ho_tro_ai" value="1"' . checked( (bool) get_post_meta( $bai->ID, '_cadao_ho_tro_ai', true ), true, false ) . '> Phần giải nghĩa có hỗ trợ của AI</label><br><span class="description">Đánh dấu khi dùng nháp máy. Trang ghi: "Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt."</span></p>';
}

/** Lưu hộp Viết Hiểu nhanh: dấu "có hỗ trợ của AI" theo đúng ô người biên soạn chọn; xoá nháp khi họ yêu cầu. */
function cadao_luu_hop_hieu_nhanh( int $id ): void {
	if ( ! isset( $_POST['cadao_hn_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cadao_hn_nonce'] ), 'cadao_luu_hn' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	if ( ! empty( $_POST['cadao_ho_tro_ai'] ) ) {
		update_post_meta( $id, '_cadao_ho_tro_ai', 1 );
	} else {
		delete_post_meta( $id, '_cadao_ho_tro_ai' );
	}
	if ( ! empty( $_POST['cadao_hn_xoa_nhap'] ) ) {
		delete_post_meta( $id, CADAO_HN_NHAP );
		delete_post_meta( $id, '_cadao_nhap_hn_luc' );
	}
}
add_action( 'save_post_' . CADAO_LOAI_BAI, 'cadao_luu_hop_hieu_nhanh' );

// ---------- Trang "Viết Hiểu nhanh" và hàng trên Bàn biên tập ----------
function cadao_trang_hieu_nhanh(): void {
	$co = cadao_hn_da_co();
	echo '<div class="wrap"><h1>Viết Hiểu nhanh</h1><p><strong>Đã có ' . esc_html( number_format_i18n( $co ) ) . ' / ' . (int) CADAO_HN_MUC_TIEU . ' bài</strong> có Hiểu nhanh (mục tiêu trước khi mở cho Google). Mỗi tuần khoảng 10 bài; bài đọc nhiều đứng trước.</p>';
	echo '<p class="description">Mở bài, viết 2–3 câu vào ô Tóm tắt rồi bấm Cập nhật. Bài có nháp máy: đọc nháp trong hộp "Viết Hiểu nhanh", bấm "Dùng nháp này", sửa cho đúng ý mình.</p>';
	$ds = cadao_hn_can_viet( 100 );
	if ( ! $ds ) {
		echo '<p>Mọi bài đang đăng đã có Hiểu nhanh.</p></div>';
		return;
	}
	echo '<table class="widefat striped" style="max-width:52rem"><thead><tr><th>Bài</th><th>Lượt đọc 30 ngày</th><th>Nháp máy</th></tr></thead><tbody>';
	foreach ( $ds as $b ) {
		echo '<tr><td><a href="' . esc_url( (string) get_edit_post_link( $b['id'] ) ) . '">' . esc_html( $b['ten'] ) . '</a></td><td>' . esc_html( number_format_i18n( $b['luot'] ) ) . '</td><td>' . ( $b['co_nhap'] ? 'Có — chờ bạn đọc' : '—' ) . '</td></tr>';
	}
	echo '</tbody></table></div>';
}

add_action(
	'admin_menu',
	static fn() => add_submenu_page( 'edit.php?post_type=' . CADAO_LOAI_BAI, 'Viết Hiểu nhanh', 'Viết Hiểu nhanh', 'edit_others_posts', CADAO_TRANG_HN, 'cadao_trang_hieu_nhanh' ),
	21
);

add_filter(
	'cadao_hang_cho',
	static function ( array $ds ) {
		$co   = cadao_hn_da_co();
		$ds[] = array(
			'khoa' => 'hieu_nhanh',
			'ten'  => sprintf( 'Viết Hiểu nhanh (đã có %d/%d bài)', $co, CADAO_HN_MUC_TIEU ),
			'so'   => max( 0, CADAO_HN_MUC_TIEU - $co ),
			'viec' => 'Bài chưa có Hiểu nhanh thì Google không tìm thấy. Mỗi tuần khoảng 10 bài, bài đọc nhiều trước; bài có nháp máy chỉ cần đọc, sửa.',
			'url'  => admin_url( 'edit.php?post_type=' . CADAO_LOAI_BAI . '&page=' . CADAO_TRANG_HN ),
		);
		return $ds;
	}
);

// ---------- Lệnh cho agent (WP-CLI) ----------
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Danh sách bài cần Hiểu nhanh (JSON, mỗi dòng một bài): mã, tên, thể loại, lời, chú thích — để soạn nháp.
	 * [--so=<n>] số bài (mặc định 30); [--ids=<1,2,3>] chỉ các bài người sáng lập chọn.
	 */
	WP_CLI::add_command(
		'cadao hieu-nhanh-ds',
		static function ( $tham, $tuy ) {
			$ids = isset( $tuy['ids'] ) ? array_map( 'intval', explode( ',', (string) $tuy['ids'] ) ) : array_column( cadao_hn_can_viet( (int) ( $tuy['so'] ?? 30 ) ), 'id' );
			foreach ( $ids as $id ) {
				$bai = get_post( $id );
				if ( $bai && CADAO_LOAI_BAI === $bai->post_type && '' === trim( $bai->post_excerpt ) ) {
					WP_CLI::line( wp_json_encode( array( 'id' => $id, 'ten' => $bai->post_title, 'the_loai' => cadao_ten_the_loai( $bai ), 'loi' => cadao_loi_cua_bai( $bai ), 'chu_thich' => cadao_hn_chu_thich( $bai ) ), JSON_UNESCAPED_UNICODE ) );
				}
			}
		}
	);
	/** Nạp nháp từ tệp JSON [{"id": 1, "nhap": "…"}] vào ô nháp nội bộ (không đụng ô Tóm tắt). */
	WP_CLI::add_command(
		'cadao hieu-nhanh-nhap',
		static function ( $tham ) {
			$lo = json_decode( (string) file_get_contents( (string) ( $tham[0] ?? '' ) ), true );
			if ( ! is_array( $lo ) ) {
				WP_CLI::error( 'Tệp không phải JSON dạng [{"id": …, "nhap": "…"}].' );
			}
			$kq = cadao_hn_nap_lo( $lo );
			WP_CLI::success( sprintf( 'Đã nạp %d nháp, bỏ qua %d (bài không có, chưa đăng hoặc đã có Hiểu nhanh).', $kq['da_nap'], $kq['bo_qua'] ) );
		}
	);
}
