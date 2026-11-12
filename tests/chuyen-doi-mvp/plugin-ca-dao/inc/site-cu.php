<?php
/**
 * Chuyển bài công khai của site Astro cũ vào WordPress (T-076, docs/24 mục 7): đọc du-lieu/site-cu.json (sinh từ
 * site Astro cũ, đã gỡ theo D-071), chạy MỘT lần sau khi chỉ mục chống trùng làm xong. Lời giữ nguyên văn.
 * Qua bước chống trùng (D-065): bài cũ có lời trùng / gần trùng một bài đã có thì vào WordPress ở dạng **Bản nháp**
 * và hiện ở trang "Có thể trùng" để người sáng lập quyết; không trùng thì đăng như trên site cũ.
 */

defined( 'ABSPATH' ) || exit;

/** Một bài của site cũ đã có trong WordPress chưa (theo mã cũ). */
function cadao_site_cu_bai( string $ma ): int {
	$ds = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_cadao_ma_site_cu', 'meta_value' => $ma ) );
	return $ds ? (int) $ds[0] : 0;
}

/**
 * Chuyển các bài trong $ds (mặc định: du-lieu/site-cu.json). Chạy lại không tạo trùng.
 *
 * @return array<string, string> mã cũ → kết quả
 */
function cadao_chuyen_site_cu( ?array $ds = null ): array {
	if ( null === $ds ) {
		$tep = CADAO_THU_MUC . '/du-lieu/site-cu.json';
		$ds  = is_readable( $tep ) ? ( json_decode( (string) file_get_contents( $tep ), true )['bai'] ?? array() ) : array();
	}
	$kq = array();
	foreach ( $ds as $b ) {
		if ( cadao_site_cu_bai( $b['ma'] ) ) {
			$kq[ $b['ma'] ] = 'đã có';
			continue;
		}
		// Chống trùng: so với các bài đã có (câu trong kho chưa thành bài sẽ tự so với bài này khi máy đọc tới)
		$trung = array_values( array_filter( cadao_tim_trung( $b['loi'], array(), 5 ), static fn( $t ) => 'bai' === cadao_tach_muc( $t['muc'] )[0] && 'lien_quan' !== $t['cap_do'] ) );
		$khoi  = array( cadao_khoi_tho( $b['loi'] ) );
		if ( $b['di_ban'] ) {
			$khoi[] = cadao_khoi_tieu_de( 'Dị bản' );
			foreach ( $b['di_ban'] as $l ) {
				$khoi[] = cadao_khoi_tho( $l );
			}
		}
		if ( $b['chu_thich'] ) {
			$li = '';
			foreach ( $b['chu_thich'] as $c ) {
				$li .= "<!-- wp:list-item -->\n<li><strong>" . esc_html( $c['tu'] ) . '</strong>: ' . esc_html( $c['noi_dung'] ) . "</li>\n<!-- /wp:list-item -->\n";
			}
			$khoi[] = cadao_khoi_tieu_de( 'Chú thích' );
			$khoi[] = "<!-- wp:list {\"className\":\"cadao-chu-thich\"} -->\n<ul class=\"wp-block-list cadao-chu-thich\">\n$li</ul>\n<!-- /wp:list -->";
		}
		$meta = array( '_cadao_ma_site_cu' => $b['ma'] );
		if ( ! empty( $b['nguon']['ten'] ) ) { // nguồn người biên soạn đã nhập trên site cũ (D-057: tùy chọn)
			$meta += array( 'nguon_ten' => $b['nguon']['ten'], 'nguon_url' => $b['nguon']['url'], 'ngay_truy_cap' => $b['nguon']['ngay_truy_cap'] );
		}
		if ( ! empty( $b['ho_tro_ai'] ) ) {
			$meta['_cadao_ho_tro_ai'] = 1;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => CADAO_LOAI_BAI,
				'post_status'  => $trung ? 'draft' : 'publish',
				'post_title'   => $b['tieu_de'],
				'post_name'    => $b['slug'],
				'post_content' => implode( "\n\n", $khoi ),
				'post_excerpt' => $b['hieu_nhanh'],
				'post_author'  => cadao_quan_tri_dau_tien(),
				'meta_input'   => $meta,
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			$kq[ $b['ma'] ] = 'lỗi: ' . $id->get_error_message();
			continue;
		}
		wp_set_object_terms( $id, $b['the_loai'], 'the_loai' );
		cadao_van_tay_bai( $id );
		foreach ( $trung as $t ) { // khi lưu bài, máy có thể đã ghi cặp này: chỉ thêm lý do
			$ly_do = trim( 'bài từ site cũ' . ( $t['ly_do'] ? '; ' . $t['ly_do'] : '' ) );
			cadao_trung_ghi_cho( 'bai:' . $id, $t['muc'], $t['cap_do'], $t['giong'], $ly_do );
			$GLOBALS['wpdb']->update( cadao_trung_bang(), array( 'ly_do' => $ly_do ), array( 'a' => 'bai:' . $id, 'b' => $t['muc'] ) );
		}
		$kq[ $b['ma'] ] = $trung ? 'bản nháp, có thể trùng bài ' . implode( ', ', array_column( $trung, 'muc' ) ) : 'đã đăng';
	}
	return $kq;
}

// Một lần, sau khi chỉ mục chống trùng làm xong (lịch 5 phút của nguồn tự động)
add_action(
	'cadao_thu_thap',
	static function () {
		if ( get_option( 'cadao_site_cu' ) || 'xong' !== ( get_option( 'cadao_trung_lam_dan', array( 'buoc' => 'xong' ) )['buoc'] ?? 'xong' ) ) {
			return;
		}
		update_option( 'cadao_site_cu', array( 'luc' => current_time( 'mysql' ), 'ket_qua' => cadao_chuyen_site_cu() ), false );
	},
	6
);

/** Dòng nhỏ cuối bài khi phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt (docs/20 mục 4.1, ý 14). */
add_filter(
	'the_content',
	static function ( $noi_dung ) {
		if ( is_singular( CADAO_LOAI_BAI ) && get_the_ID() === get_queried_object_id() && ! doing_filter( 'get_the_excerpt' ) && get_post_meta( get_the_ID(), '_cadao_ho_tro_ai', true ) ) {
			$noi_dung .= '<p class="cadao-ho-tro-ai">Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt.</p>';
		}
		return $noi_dung;
	},
	12
);
