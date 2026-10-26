<?php
/**
 * Loại bài "Ca dao – Tục ngữ", phân loại, hộp Nguồn, dòng "Nguồn: …" (docs/24 mục 3, D-054).
 * Lời nằm trong nội dung (khối Thơ); "Hiểu nhanh" nằm ở đoạn trích. Mọi ô ngoài tiêu đề và lời đều tùy chọn.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_LOAI_BAI = 'cdtn';

/** Thể loại → đoạn đầu của đường dẫn bài (/ca-dao/<ten>/, /tuc-ngu/<ten>/…), như site cũ. */
const CADAO_THE_LOAI = array(
	'ca-dao'    => 'Ca dao',
	'tuc-ngu'   => 'Tục ngữ',
	'thanh-ngu' => 'Thành ngữ',
	'dong-dao'  => 'Đồng dao',
	've'        => 'Vè',
	'cau-do'    => 'Câu đố',
);

add_action( 'init', 'cadao_dang_ky_loai_bai' );

function cadao_dang_ky_loai_bai() {
	register_post_type(
		CADAO_LOAI_BAI,
		array(
			'labels'        => array(
				'name'               => 'Ca dao – Tục ngữ',
				'singular_name'      => 'Bài',
				'menu_name'          => 'Ca dao – Tục ngữ',
				'add_new'            => 'Thêm bài',
				'add_new_item'       => 'Thêm bài mới',
				'edit_item'          => 'Sửa bài',
				'new_item'           => 'Bài mới',
				'view_item'          => 'Xem bài',
				'search_items'       => 'Tìm bài',
				'not_found'          => 'Chưa có bài nào',
				'not_found_in_trash' => 'Thùng rác trống',
				'all_items'          => 'Tất cả bài',
			),
			'public'        => true,
			'show_in_rest'  => true, // trình soạn khối (Gutenberg)
			'menu_icon'     => 'dashicons-book-alt',
			'menu_position' => 5,
			'has_archive'   => 'kho',
			'supports'      => array( 'title', 'editor', 'excerpt', 'revisions', 'author', 'custom-fields' ),
			// Bài mới mở sẵn một khối Thơ để dán lời
			'template'      => array( array( 'core/verse', array( 'placeholder' => 'Lời: mỗi dòng thơ một dòng (Shift + Enter để xuống dòng)' ) ) ),
			// Đường dẫn gốc /bai/<ten>/; đường dẫn hiển thị theo thể loại (/ca-dao/<ten>/…) do quy tắc riêng bên dưới.
			// Không dùng add_rewrite_tag trong slug: WordPress sinh quy tắc tệp đính kèm bỏ mất ngoặc quanh
			// danh sách thể loại, quy tắc đó đứng trước và nuốt mọi đường dẫn /ca-dao/… (đã gặp khi thử tại máy).
			'rewrite'       => array( 'slug' => 'bai', 'with_front' => false ),
		)
	);

	$chung = array( 'show_in_rest' => true, 'show_admin_column' => true, 'hierarchical' => true );
	register_taxonomy(
		'the_loai',
		CADAO_LOAI_BAI,
		$chung + array(
			'labels'  => array( 'name' => 'Thể loại', 'singular_name' => 'Thể loại', 'add_new_item' => 'Thêm thể loại' ),
			'rewrite' => array( 'slug' => 'the-loai' ),
		)
	);
	register_taxonomy(
		'chu_de',
		CADAO_LOAI_BAI,
		$chung + array(
			'labels'  => array( 'name' => 'Chủ đề', 'singular_name' => 'Chủ đề', 'add_new_item' => 'Thêm chủ đề' ),
			'rewrite' => array( 'slug' => 'chu-de' ),
		)
	);
	register_taxonomy(
		'vung',
		CADAO_LOAI_BAI,
		$chung + array(
			'labels'  => array( 'name' => 'Vùng', 'singular_name' => 'Vùng', 'add_new_item' => 'Thêm vùng' ),
			'rewrite' => array( 'slug' => 'vung' ),
		)
	);

	foreach ( array( 'nguon_ten', 'nguon_url', 'ngay_truy_cap' ) as $khoa ) {
		register_post_meta(
			CADAO_LOAI_BAI,
			$khoa,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'nguon_url' === $khoa ? 'esc_url_raw' : 'sanitize_text_field',
				'auth_callback'     => static fn() => current_user_can( 'edit_posts' ),
			)
		);
	}

	// /ca-dao/<ten>/, /tuc-ngu/<ten>/… → bài (đứng đầu để không quy tắc nào khác chen trước)
	add_rewrite_rule( '^(' . implode( '|', array_keys( CADAO_THE_LOAI ) ) . ')/([^/]+)/?$', 'index.php?post_type=' . CADAO_LOAI_BAI . '&name=$matches[2]', 'top' );

	// Cập nhật plugin (đổi quy tắc đường dẫn) mà không tắt bật lại: tự làm mới quy tắc một lần
	if ( get_option( 'cadao_quy_tac_duong_dan' ) !== CADAO_PHIEN_BAN ) {
		flush_rewrite_rules( false );
		update_option( 'cadao_quy_tac_duong_dan', CADAO_PHIEN_BAN );
	}
}

/** Đường dẫn bài theo thể loại đầu tiên của bài; chưa chọn thể loại thì dùng ca-dao. */
add_filter(
	'post_type_link',
	static function ( $link, $post ) {
		if ( CADAO_LOAI_BAI !== $post->post_type || ! str_contains( $link, '/bai/' ) ) {
			return $link; // bài chưa đăng: WordPress dùng ?p=…, giữ nguyên
		}
		$the  = get_the_terms( $post, 'the_loai' );
		$doan = ( $the && ! is_wp_error( $the ) && array_key_exists( $the[0]->slug, CADAO_THE_LOAI ) ) ? $the[0]->slug : 'ca-dao';
		return str_replace( '/bai/', '/' . $doan . '/', $link );
	},
	10,
	2
);

function cadao_tao_the_loai_mac_dinh() {
	foreach ( CADAO_THE_LOAI as $slug => $ten ) {
		if ( ! term_exists( $slug, 'the_loai' ) ) {
			wp_insert_term( $ten, 'the_loai', array( 'slug' => $slug ) );
		}
	}
}

// ---------- Hộp "Nguồn" trong trang sửa bài ----------
add_action(
	'add_meta_boxes_' . CADAO_LOAI_BAI,
	static function () {
		add_meta_box( 'cadao_nguon', 'Nguồn của chú thích (tùy chọn)', 'cadao_hop_nguon', CADAO_LOAI_BAI, 'side' );
	}
);

function cadao_hop_nguon( WP_Post $bai ) {
	wp_nonce_field( 'cadao_luu_nguon', 'cadao_nguon_nonce' );
	$o = static fn( $k ) => esc_attr( (string) get_post_meta( $bai->ID, $k, true ) );
	?>
	<p><label for="cadao_nguon_ten">Tên nguồn</label><br>
		<input class="widefat" id="cadao_nguon_ten" name="nguon_ten" value="<?php echo $o( 'nguon_ten' ); ?>" placeholder="Ví dụ: Ca dao Mẹ"></p>
	<p><label for="cadao_nguon_url">Địa chỉ trang (nếu có)</label><br>
		<input class="widefat" id="cadao_nguon_url" name="nguon_url" type="url" value="<?php echo $o( 'nguon_url' ); ?>" placeholder="https://"></p>
	<p><label for="cadao_ngay_truy_cap">Ngày truy cập</label><br>
		<input class="widefat" id="cadao_ngay_truy_cap" name="ngay_truy_cap" type="date" value="<?php echo $o( 'ngay_truy_cap' ); ?>"></p>
	<p class="description">Thường để trống: lời ca dao, tục ngữ không cần ghi nguồn. Điền khi bài có chú thích, lời giải chép từ nơi khác — trang sẽ hiện "Nguồn: …". Đã tự so lời với sách? Điền ở hộp "Lời đã đối chiếu với".</p>
	<?php
}

add_action(
	'save_post_' . CADAO_LOAI_BAI,
	static function ( $id ) {
		if ( ! isset( $_POST['cadao_nguon_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cadao_nguon_nonce'] ), 'cadao_luu_nguon' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		update_post_meta( $id, 'nguon_ten', sanitize_text_field( wp_unslash( $_POST['nguon_ten'] ?? '' ) ) );
		update_post_meta( $id, 'nguon_url', esc_url_raw( wp_unslash( $_POST['nguon_url'] ?? '' ) ) );
		update_post_meta( $id, 'ngay_truy_cap', sanitize_text_field( wp_unslash( $_POST['ngay_truy_cap'] ?? '' ) ) );
	}
);

// ---------- Dòng "Nguồn: …" cuối bài (D-054: thay huy hiệu mức kiểm) ----------
function cadao_dong_nguon( int $id ): string {
	$ten = (string) get_post_meta( $id, 'nguon_ten', true );
	$url = (string) get_post_meta( $id, 'nguon_url', true );
	if ( '' === $ten && '' === $url ) {
		return '';
	}
	$nhan = '' !== $ten ? $ten : wp_parse_url( $url, PHP_URL_HOST );
	$chu  = '' !== $url
		? sprintf( '<a href="%s" rel="noopener">%s <span aria-hidden="true">↗</span></a>', esc_url( $url ), esc_html( $nhan ) )
		: esc_html( $nhan );
	return '<p class="cadao-nguon">Nguồn: ' . $chu . '</p>';
}

/**
 * Các trang gốc máy đã lấy bài này từ đó (D-068, D-078): địa chỉ giữ nội bộ ở _cadao_url_goc, cộng trang gốc của
 * các câu đã gộp vào bài (làm dị bản). Mỗi địa chỉ đếm một lần.
 *
 * @return array<int, array{ten: string, url: string}>
 */
function cadao_nguon_an( int $id ): array {
	global $wpdb;
	$ra   = array();
	$them = static function ( string $url, string $nguon_id ) use ( &$ra ): void {
		$url = esc_url_raw( $url );
		if ( '' === $url || isset( $ra[ $url ] ) ) {
			return;
		}
		$n          = cadao_nguon_lay( $nguon_id );
		$ten        = $n ? ( $n['dong_ghi_nguon'] ?: $n['ten'] ) : '';
		$ra[ $url ] = array( 'ten' => '' !== (string) $ten ? (string) $ten : (string) wp_parse_url( $url, PHP_URL_HOST ), 'url' => $url );
	};
	$them( (string) get_post_meta( $id, '_cadao_url_goc', true ), (string) get_post_meta( $id, '_cadao_nguon_id', true ) );
	$bang = cadao_kho_bang();
	foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT url, nguon_id FROM $bang WHERE trang_thai = 'da_gop' AND ( gop_vao = %s OR gop_vao IN ( SELECT CONCAT( 'kho:', id ) FROM $bang WHERE post_id = %d ) ) ORDER BY id", 'bai:' . $id, $id ) ) ?: array() as $m ) {
		$them( (string) $m->url, (string) $m->nguon_id );
	}
	return array_values( $ra );
}

/**
 * Dòng nguồn đã ẩn cuối bài (D-078). Người đọc chỉ thấy "[N nguồn tham khảo đã ẩn]"; người biên soạn (đăng nhập, sửa
 * được bài) thấy thêm liên kết về từng trang gốc. Liên kết chỉ được xuất ra HTML khi đúng người xem — không ẩn bằng CSS,
 * để Google và người đọc không thấy. Nguồn người biên soạn tự điền (cadao_dong_nguon) vẫn hiện như cũ.
 */
function cadao_dong_nguon_an( int $id ): string {
	$ds = cadao_nguon_an( $id );
	if ( ! $ds ) {
		return '';
	}
	$ra = '<p class="cadao-nguon-an">' . esc_html( sprintf( '[%d nguồn tham khảo đã ẩn]', count( $ds ) ) ) . '</p>';
	if ( current_user_can( 'edit_post', $id ) ) {
		$lk  = array_map( static fn( $x ) => sprintf( '<a href="%s" rel="noopener noreferrer">%s <span aria-hidden="true">↗</span></a>', esc_url( $x['url'] ), esc_html( $x['ten'] ) ), $ds );
		$ra .= '<p class="cadao-nguon-quan-tri">Chỉ người biên soạn thấy dòng này · Nguồn đã ẩn: ' . implode( ' · ', $lk ) . '</p>';
	}
	return $ra;
}

add_filter(
	'the_content',
	static function ( $noi_dung ) {
		// Theme khối (Twenty Twenty-Five…) không dùng vòng lặp cổ điển nên không kiểm in_the_loop()
		if ( is_singular( CADAO_LOAI_BAI ) && get_the_ID() === get_queried_object_id() && ! doing_filter( 'get_the_excerpt' ) ) {
			$noi_dung .= cadao_dong_nguon( get_the_ID() ) . cadao_dong_nguon_an( get_the_ID() );
		}
		return $noi_dung;
	}
);

/**
 * Bài chưa có "Hiểu nhanh" (đoạn trích do dự án viết) mang noindex — chính sách spam của Google về nội dung
 * chép lại (docs/24 mục 5). Lời và chú thích chép từ nguồn không tính là giá trị riêng. Người đọc vẫn xem được.
 */
function cadao_co_gia_tri_rieng( WP_Post $bai ): bool {
	// Bài có bài phân tích của người đọc đã duyệt cũng có giá trị riêng (D-062, phan-tich.php)
	return (bool) apply_filters( 'cadao_co_gia_tri_rieng', '' !== trim( $bai->post_excerpt ), $bai );
}

add_filter(
	'wp_robots',
	static function ( $robots ) {
		if ( is_singular( CADAO_LOAI_BAI ) && ! cadao_co_gia_tri_rieng( get_queried_object() ) ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
);

// Không lộ phiên bản WordPress trong <head> và nguồn tin (giúp máy quét lỗ hổng tự động — T-118)
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// Cột "Nguồn" trong danh sách bài
add_filter(
	'manage_' . CADAO_LOAI_BAI . '_posts_columns',
	static function ( $cot ) {
		$cot['cadao_nguon'] = 'Nguồn';
		return $cot;
	}
);
add_action(
	'manage_' . CADAO_LOAI_BAI . '_posts_custom_column',
	static function ( $cot, $id ) {
		if ( 'cadao_nguon' === $cot ) {
			echo esc_html( (string) get_post_meta( $id, 'nguon_ten', true ) ?: '—' );
		}
	},
	10,
	2
);
