<?php
/**
 * Chính sách thu thập dữ liệu (D-068, 26/10/2026). Người sáng lập quyết: bỏ dòng "Nguồn: …" dưới chú thích, đáp án chép
 * từ nguồn tự động (cadao.me) — cả bài, mục từ đã đăng — và nêu các nguồn ở một trang riêng (/chinh-sach-du-lieu/).
 * Ghi công theo giấy phép mở (ảnh Commons, cách chơi Wikipedia, CC BY-SA) vẫn ở ngay tại chỗ.
 * Phần dọn chạy dần (mỗi lượt lấy bài 5 phút, tối đa 25 giây): bài chưa sửa tay được làm lại từ kho; bài đã sửa tay
 * chỉ bỏ đúng khối "Nguồn: …" trỏ về trang nguồn đó, không đổi chữ nào khác (WordPress lưu bản sửa đổi). Danh sách
 * chú thích chép mang lớp cadao-chep (không hiện ra trang) để mục từ chép vẫn noindex.
 */

defined( 'ABSPATH' ) || exit;

/** Tên miền của các nguồn tự động đang tắt "Ghi nguồn cho chú thích". */
function cadao_mien_khong_ghi_nguon(): array {
	$ra = array();
	foreach ( cadao_nguon_tat_ca() as $n ) {
		$h = (string) wp_parse_url( (string) ( $n['url'] ?? '' ), PHP_URL_HOST );
		if ( '' !== $h && empty( $n['ghi_cong'] ) ) {
			$ra[] = preg_replace( '/^www\./', '', strtolower( $h ) );
		}
	}
	return array_values( array_unique( $ra ) );
}

/** Bỏ khối "Nguồn: …" trỏ về các tên miền đã cho; danh sách chú thích ngay trước nó được đánh dấu cadao-chep. */
function cadao_bo_dong_nguon_chep( string $noi_dung, array $mien ): string {
	$khoi  = parse_blocks( $noi_dung );
	$doi   = false;
	$truoc = null; // khối có tên gần nhất phía trước
	foreach ( $khoi as $i => $k ) {
		if ( null === $k['blockName'] ) {
			continue;
		}
		if ( 'core/paragraph' === $k['blockName'] && str_contains( (string) ( $k['attrs']['className'] ?? '' ), 'cadao-chu-thich-nguon' ) && preg_match( '~<a href="([^"]+)"~', $k['innerHTML'], $a ) ) {
			$h = preg_replace( '/^www\./', '', strtolower( (string) wp_parse_url( html_entity_decode( $a[1] ), PHP_URL_HOST ) ) );
			if ( in_array( $h, $mien, true ) ) {
				$lop = (string) ( $khoi[ $truoc ]['attrs']['className'] ?? '' );
				if ( null !== $truoc && 'core/list' === $khoi[ $truoc ]['blockName'] && str_contains( $lop, 'cadao-chu-thich' ) && ! str_contains( $lop, 'cadao-chep' ) ) {
					$khoi[ $truoc ]['attrs']['className'] = $lop . ' cadao-chep';
					$doi_lop                              = static fn( $s ) => is_string( $s ) ? (string) preg_replace( '~(<ul\b[^>]*class="[^"]*\bcadao-chu-thich)(?=["\s])~', '$1 cadao-chep', $s, 1 ) : $s;
					$khoi[ $truoc ]['innerHTML']          = $doi_lop( $khoi[ $truoc ]['innerHTML'] );
					$khoi[ $truoc ]['innerContent']       = array_map( $doi_lop, $khoi[ $truoc ]['innerContent'] );
				}
				if ( isset( $khoi[ $i - 1 ] ) && null === $khoi[ $i - 1 ]['blockName'] && '' === trim( $khoi[ $i - 1 ]['innerHTML'] ) ) {
					unset( $khoi[ $i - 1 ] ); // khoảng trống trước khối đã bỏ
				}
				unset( $khoi[ $i ] );
				$doi = true;
				continue;
			}
		}
		$truoc = $i;
	}
	return $doi ? serialize_blocks( array_values( $khoi ) ) : $noi_dung;
}

/**
 * Một lượt dọn: bài, mục từ còn dòng Nguồn trỏ về nguồn đã tắt ghi nguồn.
 *
 * @return int số bài đã dọn
 */
function cadao_don_dong_nguon( float $han ): int {
	global $wpdb;
	$mien = cadao_mien_khong_ghi_nguon();
	$so   = 0;
	foreach ( $mien as $h ) {
		// Mục từ: bỏ nguồn đã ghi, giữ dấu "chép" (noindex)
		foreach ( $wpdb->get_col( $wpdb->prepare( "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = '_cadao_nguon_url' AND meta_value LIKE %s", '%' . $wpdb->esc_like( '//' . $h ) . '%' ) ) as $tid ) {
			update_term_meta( (int) $tid, '_cadao_chep', 1 );
			delete_term_meta( (int) $tid, '_cadao_nguon_ten' );
			delete_term_meta( (int) $tid, '_cadao_nguon_url' );
		}
		$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash','auto-draft','inherit') AND post_content LIKE %s AND post_content LIKE %s ORDER BY ID", CADAO_LOAI_BAI, '%cadao-chu-thich-nguon%', '%' . $wpdb->esc_like( '//' . $h ) . '%' ) );
		foreach ( $ids as $id ) {
			if ( microtime( true ) > $han ) {
				return $so;
			}
			$id = (int) $id;
			if ( ! cadao_bai_lam_moi_tu_kho( $id ) ) { // bài đã sửa tay: chỉ bỏ khối Nguồn
				$cu  = (string) get_post_field( 'post_content', $id );
				$moi = cadao_bo_dong_nguon_chep( $cu, array( $h ) );
				if ( $moi !== $cu ) {
					$kses = (bool) has_filter( 'content_save_pre', 'wp_filter_post_kses' );
					if ( $kses ) {
						kses_remove_filters(); // không lọc lại nội dung người biên soạn đã viết
					}
					wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( $moi ) ) );
					if ( $kses ) {
						kses_init_filters();
					}
				}
			}
			++$so;
		}
	}
	return $so;
}

// Nâng cấp một lần: tắt "Ghi nguồn cho chú thích" ở mọi nguồn tự động (bật lại được trong trang Nguồn tự động)
add_action(
	'plugins_loaded',
	static function () {
		if ( get_option( 'cadao_d068' ) ) {
			return;
		}
		foreach ( cadao_nguon_tat_ca() as $n ) {
			$n['ghi_cong'] = false;
			cadao_nguon_luu( $n );
		}
		update_option( 'cadao_d068', 1, false );
	},
	20
);

add_action( 'cadao_thu_thap', static fn() => cadao_don_dong_nguon( microtime( true ) + 25 ), 5 );

/** Câu chữ trang "Chính sách thu thập dữ liệu" (khối WordPress). Người sáng lập sửa được trong trình soạn. */
function cadao_noi_dung_chinh_sach_du_lieu(): string {
	$p = static fn( $s ) => "<!-- wp:paragraph -->\n<p>$s</p>\n<!-- /wp:paragraph -->";
	$h = static fn( $s ) => "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">$s</h2>\n<!-- /wp:heading -->";
	return implode(
		"\n\n",
		array(
			$p( 'Kho tàng Ca dao Tục ngữ Việt Nam lưu giữ và giới thiệu ca dao, tục ngữ, thành ngữ, đồng dao, vè, câu đố. Một phần nội dung được thu thập từ các trang web công khai liệt kê ở cuối trang này; phần còn lại do người biên soạn nhập từ sách và từ hiểu biết của mình.' ),
			$h( 'Chúng tôi lấy những gì' ),
			$p( '<strong>Lời ca dao, tục ngữ và dị bản</strong>: chép nguyên văn, không sửa chữ. Lời là của cộng đồng nên không ghi nguồn dưới lời.' ),
			$p( '<strong>Chú thích từ khó, đáp án câu đố</strong>: chép nguyên văn từ trang nguồn. Để trang đọc gọn, chúng tôi không đặt dòng nguồn dưới từng chú thích; các trang nguồn được ghi nhận ở danh sách dưới đây.' ),
			$p( 'Chúng tôi không tải ảnh và không chép cách phân loại của trang nguồn. Ảnh trên site lấy từ Wikimedia Commons với giấy phép mở, ghi tác giả và giấy phép ngay dưới ảnh; phần chép từ Wikipedia, Wiktionary giữ giấy phép CC BY-SA và được ghi ngay tại chỗ.' ),
			$h( 'Cách chúng tôi lấy' ),
			$p( 'Máy đọc từng trang một, chậm rãi, cách nhau vài giây, để không làm nặng trang nguồn. Mỗi câu lấy về được người biên soạn đọc lại trước khi đăng; câu trùng với bài đã có được gộp, không đăng lặp.' ),
			$h( 'Xin gỡ, xin sửa' ),
			$p( 'Chủ một trang nguồn muốn chúng tôi gỡ nội dung, hoặc ghi nguồn theo cách khác: xin gửi qua mục <strong>"Góp ý cho bài này"</strong> ở cuối bất kỳ bài nào (chọn "Báo lỗi"). Báo lỗi được đọc trước mọi việc khác.' ),
			$h( 'Các nguồn đã thu thập' ),
			"<!-- wp:shortcode -->\n[cadao_nguon]\n<!-- /wp:shortcode -->",
		)
	);
}

/** Đường dẫn trang chính sách ('' nếu chưa đăng). */
function cadao_url_chinh_sach_du_lieu(): string {
	$id = (int) get_option( 'cadao_trang_chinh_sach' );
	return $id && 'publish' === get_post_status( $id ) ? (string) get_permalink( $id ) : '';
}

// Trang tạo một lần, đăng ngay (là nơi ghi nguồn thay cho dòng dưới chú thích)
add_action(
	'init',
	static function () {
		if ( get_option( 'cadao_trang_chinh_sach' ) || wp_installing() ) {
			return;
		}
		$co = get_page_by_path( 'chinh-sach-du-lieu', OBJECT, 'page' );
		$id = $co ? $co->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Chính sách thu thập dữ liệu', 'post_name' => 'chinh-sach-du-lieu', 'post_content' => cadao_noi_dung_chinh_sach_du_lieu() ) );
		update_option( 'cadao_trang_chinh_sach', (int) $id, false );
		// Bản nháp trang Phương pháp (chưa sửa câu này): theo D-068
		$pp = get_page_by_path( 'phuong-phap', OBJECT, 'page' );
		$doi = array(
			'Chú thích từ khó và đáp án câu đố chép từ nguồn luôn có dòng <strong>"Nguồn: …"</strong> dẫn về đúng trang đã lấy. Chú thích do dự án viết thì không có dòng đó. Ảnh minh hoạ chỉ lấy từ Wikimedia Commons với giấy phép mở, ghi tác giả và giấy phép ngay dưới ảnh.' => CADAO_CAU_PHUONG_PHAP_NGUON,
			'Lời giữ nguyên văn. Chú thích ghi nguồn. Máy chỉ gợi ý, người biên soạn quyết.' => CADAO_CAU_PHUONG_PHAP_DAU,
		);
		if ( $pp && 'publish' !== $pp->post_status && str_contains( $pp->post_content, array_key_first( $doi ) ) ) {
			wp_update_post( array( 'ID' => $pp->ID, 'post_content' => wp_slash( strtr( $pp->post_content, $doi ) ) ) );
		}
	},
	32
);

const CADAO_CAU_PHUONG_PHAP_NGUON = 'Chú thích từ khó và đáp án câu đố có thể được chép nguyên văn từ trang nguồn; các trang nguồn được ghi nhận ở trang <a href="/chinh-sach-du-lieu/">Chính sách thu thập dữ liệu</a>. Ảnh minh hoạ chỉ lấy từ Wikimedia Commons với giấy phép mở; ảnh và phần chép từ Wikipedia, Wiktionary ghi tác giả, giấy phép ngay tại chỗ.';
const CADAO_CAU_PHUONG_PHAP_DAU   = 'Lời giữ nguyên văn. Nguồn đã thu thập được ghi nhận công khai. Máy chỉ gợi ý, người biên soạn quyết.';
