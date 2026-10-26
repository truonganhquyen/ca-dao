<?php
/**
 * Gộp hai bài đã có bị trùng (T-101, ý 49, D-065, docs/25 mục 4.9). Người sáng lập chọn bài chính trong trang
 * "Có thể trùng"; bài kia (bài phụ) thành dị bản của bài chính. Giữ mọi thứ người đọc đã đóng góp: lượt đọc cộng dồn,
 * cảm nghĩ và bài phân tích chuyển sang bài chính; chủ đề, vùng, tình huống, mục từ gộp lại; Hiểu nhanh, chú thích tự
 * viết, nguồn đã ghi của bài phụ được giữ nếu bài chính chưa có. Đường dẫn cũ của bài phụ chuyển hướng 301 về bài chính
 * (cơ chế _wp_old_slug của WordPress). Bài phụ vào thùng rác, không xóa hẳn. Mỗi đêm máy quét lại các bài đã đăng.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gộp bài $phu vào bài $chinh.
 *
 * @return bool
 */
function cadao_gop_hai_bai( int $chinh, int $phu ): bool {
	global $wpdb;
	$bc = get_post( $chinh );
	$bp = get_post( $phu );
	if ( ! $bc || ! $bp || $chinh === $phu || CADAO_LOAI_BAI !== $bc->post_type || CADAO_LOAI_BAI !== $bp->post_type || 'trash' === $bc->post_status ) {
		return false;
	}
	// 1. Lời và dị bản của bài phụ thành dị bản của bài chính (lời giống hệt thì không thêm)
	$cua_chinh = array_map( 'cadao_chuan_loi', cadao_loi_cac_khoi_tho( $bc->post_content ) );
	foreach ( cadao_loi_cac_khoi_tho( $bp->post_content ) as $loi ) {
		if ( ! in_array( cadao_chuan_loi( $loi ), $cua_chinh, true ) ) {
			cadao_them_di_ban( $chinh, $loi );
			$cua_chinh[] = cadao_chuan_loi( $loi );
		}
	}
	// 2. Chú thích người biên soạn viết của bài phụ (không có dòng Nguồn): giữ ở bài chính
	$co_ct = array_map( static fn( $c ) => mb_strtolower( $c['tu'] ) . '|' . $c['noi_dung'], cadao_chu_thich_cua_bai( get_post( $chinh )->post_content ) );
	$them  = array_values( array_filter( cadao_chu_thich_cua_bai( $bp->post_content ), static fn( $c ) => '' === $c['nguon_ten'] && empty( $c['chep'] ) && ! in_array( mb_strtolower( $c['tu'] ) . '|' . $c['noi_dung'], $co_ct, true ) ) );
	if ( $them ) {
		$cu = (array) ( get_post_meta( $chinh, '_cadao_chu_thich_them', true ) ?: array() );
		update_post_meta( $chinh, '_cadao_chu_thich_them', array_merge( $cu, array_map( static fn( $c ) => array( 'tu' => $c['tu'], 'noi_dung' => $c['noi_dung'] ), $them ) ) );
		if ( ! cadao_bai_lam_moi_tu_kho( $chinh ) ) { // bài chính đã sửa tay: chèn danh sách vào cuối
			$li = implode( '', array_map( static fn( $c ) => "<!-- wp:list-item -->\n<li><strong>" . esc_html( $c['tu'] ) . '</strong>: ' . esc_html( $c['noi_dung'] ) . "</li>\n<!-- /wp:list-item -->\n", $them ) );
			$nd = get_post( $chinh )->post_content;
			$nd .= ( str_contains( $nd, cadao_khoi_tieu_de( 'Chú thích' ) ) ? '' : "\n\n" . cadao_khoi_tieu_de( 'Chú thích' ) ) . "\n\n<!-- wp:list {\"className\":\"cadao-chu-thich\"} -->\n<ul class=\"wp-block-list cadao-chu-thich\">\n$li</ul>\n<!-- /wp:list -->";
			wp_update_post( array( 'ID' => $chinh, 'post_content' => $nd ) );
		}
	}
	// 3. Hiểu nhanh, nguồn đã ghi, dấu "có hỗ trợ AI": giữ nếu bài chính chưa có
	if ( '' === trim( get_post( $chinh )->post_excerpt ) && '' !== trim( $bp->post_excerpt ) ) {
		wp_update_post( array( 'ID' => $chinh, 'post_excerpt' => $bp->post_excerpt ) );
		if ( get_post_meta( $phu, '_cadao_ho_tro_ai', true ) ) {
			update_post_meta( $chinh, '_cadao_ho_tro_ai', 1 );
		}
	}
	if ( '' === (string) get_post_meta( $chinh, 'nguon_ten', true ) && '' !== (string) get_post_meta( $phu, 'nguon_ten', true ) ) {
		foreach ( array( 'nguon_ten', 'nguon_url', 'ngay_truy_cap' ) as $k ) {
			update_post_meta( $chinh, $k, get_post_meta( $phu, $k, true ) );
		}
	}
	// 4. Chủ đề, vùng, tình huống, mục từ: gộp lại
	foreach ( array( 'chu_de', 'vung', 'tinh_huong', 'tu_kho' ) as $tax ) {
		$ds = wp_get_object_terms( $phu, $tax, array( 'fields' => 'ids' ) );
		if ( $ds && ! is_wp_error( $ds ) ) {
			wp_set_object_terms( $chinh, $ds, $tax, true );
		}
	}
	if ( '' === (string) get_post_meta( $chinh, 'hop_khi', true ) && '' !== (string) get_post_meta( $phu, 'hop_khi', true ) ) {
		update_post_meta( $chinh, 'hop_khi', get_post_meta( $phu, 'hop_khi', true ) );
	}
	// 5. Lượt đọc cộng dồn
	$ld = cadao_luot_doc_bang();
	foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT ngay, so FROM $ld WHERE post_id = %d", $phu ) ) as $r ) {
		$wpdb->query( $wpdb->prepare( "INSERT INTO $ld (post_id, ngay, so) VALUES (%d, %s, %d) ON DUPLICATE KEY UPDATE so = so + %d", $chinh, $r->ngay, (int) $r->so, (int) $r->so ) );
	}
	$wpdb->delete( $ld, array( 'post_id' => $phu ) );
	// 6. Cảm nghĩ, bài phân tích chuyển sang bài chính
	$bl = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d", $phu ) );
	$wpdb->update( $wpdb->comments, array( 'comment_post_ID' => $chinh ), array( 'comment_post_ID' => $phu ) );
	clean_comment_cache( array_map( 'intval', $bl ) );
	wp_update_comment_count_now( $chinh );
	wp_update_comment_count_now( $phu );
	foreach ( get_posts( array( 'post_type' => CADAO_LOAI_PHAN_TICH, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_cadao_bai', 'meta_value' => $phu ) ) as $pt ) {
		update_post_meta( $pt, '_cadao_bai', $chinh );
	}
	// 7. Câu trong kho của bài phụ: gắn vào bài chính (bài chính chưa sửa tay thì nhận cả chú thích của nguồn đó)
	$wpdb->update( cadao_kho_bang(), array( 'trang_thai' => 'da_gop', 'gop_vao' => 'bai:' . $chinh, 'gop_lam' => 'di_ban', 'post_id' => 0 ), array( 'post_id' => $phu ) );
	$wpdb->update( cadao_kho_bang(), array( 'gop_vao' => 'bai:' . $chinh ), array( 'gop_vao' => 'bai:' . $phu ) );
	cadao_bai_lam_moi_tu_kho( $chinh );
	// 8. Đường dẫn cũ → bài chính (301): WordPress tự chuyển khi tên cũ nằm trong _wp_old_slug của bài chính
	$ten_cu = $bp->post_name ?: sanitize_title( $bp->post_title );
	if ( '' !== $ten_cu && ! in_array( $ten_cu, get_post_meta( $chinh, '_wp_old_slug' ), true ) ) {
		add_post_meta( $chinh, '_wp_old_slug', $ten_cu );
	}
	update_post_meta( $phu, '_cadao_gop_vao', $chinh );
	// 9. Hàng chờ: các cặp có bài phụ đã xong; bài phụ vào thùng rác (khôi phục được)
	$t = cadao_trung_bang();
	$wpdb->query( $wpdb->prepare( "UPDATE $t SET trang_thai = 'da_gop' WHERE ((a = %s AND b = %s) OR (a = %s AND b = %s))", 'bai:' . $phu, 'bai:' . $chinh, 'bai:' . $chinh, 'bai:' . $phu ) );
	wp_trash_post( $phu );
	cadao_van_tay_bai( $chinh );
	return true;
}

// Mỗi đêm: so lại các bài đã đăng với nhau (bài mới, bài sửa tay sau lần so trước) — đưa cặp nghi trùng vào hàng chờ
add_action(
	'cadao_tao_bai_hang_ngay',
	static function () {
		global $wpdb;
		$han = microtime( true ) + 60;
		foreach ( $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' ORDER BY ID", CADAO_LOAI_BAI ) ) as $id ) {
			if ( microtime( true ) > $han ) {
				break;
			}
			cadao_trung_kiem_bai( (int) $id );
		}
	},
	20
);
