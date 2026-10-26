<?php
/**
 * Trích dẫn và chia sẻ trên trang bài (T-042, docs/20 mục 6–7, viết lại theo D-054, D-057).
 * Hộp "Trích dẫn" mở hai kiểu (thông dụng, APA 7) và hai nút "Sao chép kèm nguồn" / "Chỉ sao chép lời"; nút "Chia sẻ"
 * dùng Web Share, máy không có thì sao chép liên kết. "Nguồn" ở đây là trang của site — lời không ghi nguồn ngoài
 * (D-057); chú thích, đáp án chép kèm thì mang theo dòng "Nguồn: …" của chúng. Tắt JavaScript: hộp vẫn mở được và
 * hiện chữ trích dẫn để chọn tay; các nút cần JavaScript thì ẩn.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TEN_DU_AN = 'Kho tàng Ca dao Tục ngữ Việt Nam';

/** Lời chính của bài: khối Thơ đầu tiên, mỗi dòng một dòng, đã giải mã HTML. Không có khối Thơ thì ''. */
function cadao_loi_cua_bai( WP_Post $bai ): string {
	foreach ( parse_blocks( $bai->post_content ) as $khoi ) {
		if ( 'core/verse' === $khoi['blockName'] ) {
			$h = preg_replace( '~<br\s*/?>~i', "\n", (string) $khoi['innerHTML'] );
			$h = cadao_giai_ma( wp_strip_all_tags( $h ) );
			return trim( implode( "\n", array_map( 'cadao_cat', explode( "\n", str_replace( "\r", '', $h ) ) ) ), "\n" );
		}
	}
	return '';
}

/** Tên thể loại đầu tiên của bài; chưa chọn thì "Ca dao" (như đường dẫn). */
function cadao_ten_the_loai( WP_Post $bai ): string {
	$the = get_the_terms( $bai, 'the_loai' );
	return ( $the && ! is_wp_error( $the ) ) ? $the[0]->name : CADAO_THE_LOAI['ca-dao'];
}

/**
 * Hai kiểu trích dẫn, dạng văn bản thường. Ngày truy cập đặt trong {ngay} để trang bọc thẻ (JavaScript đổi theo ngày bấm).
 *
 * @return array{thong_dung: string, apa: string}
 */
function cadao_mau_trich_dan( WP_Post $bai ): array {
	$ten = cadao_giai_ma( wp_strip_all_tags( get_the_title( $bai ) ) );
	$the = cadao_ten_the_loai( $bai );
	$url = get_permalink( $bai );
	return array(
		'thong_dung' => sprintf( '"%s" (%s). %s. Truy cập tại: %s, ngày {ngay}.', $ten, $the, CADAO_TEN_DU_AN, $url ),
		'apa'        => sprintf( '%s. (%s). %s [%s]. %s', CADAO_TEN_DU_AN, get_the_date( 'Y', $bai ), $ten, $the, $url ),
	);
}

/** Khối "Trích dẫn · Chia sẻ" của một bài (rỗng nếu bài không có lời). */
function cadao_khoi_hanh_dong( WP_Post $bai ): string {
	$loi = cadao_loi_cua_bai( $bai );
	if ( '' === $loi ) {
		return '';
	}
	$ngay  = '<span class="cadao-ngay">' . esc_html( wp_date( 'd/m/Y' ) ) . '</span>';
	$trich = cadao_mau_trich_dan( $bai );
	$mau   = '';
	foreach ( array( 'thong_dung' => 'Thông dụng', 'apa' => 'APA 7' ) as $kieu => $nhan ) {
		$chu  = str_replace( '{ngay}', $ngay, esc_html( $trich[ $kieu ] ) );
		$mau .= '<p class="cadao-mau" data-kieu="' . esc_attr( $kieu ) . '"><span class="cadao-mau-nhan">' . esc_html( $nhan ) . ':</span> <span class="cadao-mau-chu">' . $chu . '</span></p>';
	}
	// Bài có phần chép từ nguồn (đáp án, chú thích): cho chọn chép kèm, cùng dòng "Nguồn: …" của phần đó
	$co_dap_an = str_contains( $bai->post_content, 'wp-block-details' );
	$co_chu    = 1 === preg_match( '/class="[^"]*\bcadao-chu-thich[" ]/', $bai->post_content ); // không tính dòng cadao-chu-thich-nguon
	$kem       = $co_dap_an || $co_chu
		? '<p class="cadao-can-js" hidden><label><input type="checkbox" class="cadao-kem"> Kèm ' . ( $co_dap_an && $co_chu ? 'đáp án và chú thích' : ( $co_dap_an ? 'đáp án' : 'chú thích' ) ) . '</label></p>'
		: '';
	return '<div class="cadao-hanh-dong" data-id="' . (int) $bai->ID . '" data-loi="' . esc_attr( $loi ) . '" data-ten="' . esc_attr( cadao_giai_ma( wp_strip_all_tags( get_the_title( $bai ) ) ) ) . '" data-the-loai="' . esc_attr( cadao_ten_the_loai( $bai ) ) . '" data-url="' . esc_url( get_permalink( $bai ) ) . '">'
		. '<details class="cadao-trich-dan"><summary><span aria-hidden="true">❝</span> Trích dẫn</summary><div class="cadao-trich-dan-hop">'
		. '<fieldset class="cadao-can-js" hidden><legend>Kiểu trích dẫn</legend>'
		. '<label><input type="radio" name="cadao-kieu" value="thong_dung" checked> Thông dụng</label> '
		. '<label><input type="radio" name="cadao-kieu" value="apa"> APA 7</label></fieldset>'
		. $mau . $kem
		. '<p class="cadao-nut cadao-can-js" hidden><button type="button" class="cadao-nut-chinh" data-viec="kem_nguon">Sao chép kèm nguồn</button> '
		. '<button type="button" data-viec="chi_loi">Chỉ sao chép lời</button></p>'
		. '</div></details>'
		. '<button type="button" class="cadao-chia-se cadao-can-js" hidden>Chia sẻ</button>'
		// Đã lưu (T-051): chỉ hiện khi trình duyệt cho dùng bộ nhớ
		. '<button type="button" class="cadao-luu" aria-pressed="false" hidden><span class="cadao-sao" aria-hidden="true">☆</span> Lưu</button>'
		. ( cadao_url_da_luu() ? '<a class="cadao-xem-da-luu" href="' . esc_url( cadao_url_da_luu() ) . '" hidden>Bài đã lưu</a>' : '' )
		// Công cụ đọc (T-085): cỡ chữ, sáng / tối (nhớ trên máy), trình chiếu, nghe bằng giọng có sẵn của máy (D-063)
		. '<span class="cadao-cong-cu cadao-can-js" role="group" aria-label="Công cụ đọc" hidden>'
		. '<button type="button" class="cadao-co-chu" aria-label="Cỡ chữ">Aa</button>'
		. '<button type="button" class="cadao-sang-toi" aria-label="Đổi sáng / tối"><span aria-hidden="true">◐</span></button>'
		. '<button type="button" class="cadao-trinh-chieu">Trình chiếu</button>'
		. ( function_exists( 'cadao_la_dong_dao' ) && cadao_la_dong_dao( $bai ) ? '<button type="button" class="cadao-doc-cung-be" hidden>Đọc cùng bé</button>' : '' ) // T-103, assets/dong-dao.js
		. '<button type="button" class="cadao-nghe" hidden>▶ Nghe</button><span class="cadao-giong-may" hidden>Giọng đọc máy</span>'
		. '</span>'
		. '<p class="cadao-bao" role="status" aria-live="polite"></p>'
		. '</div>';
}

/**
 * Chèn khối ngay sau lời và "Hiểu nhanh" (theme chèn ở ưu tiên 20), trước Dị bản, Chú thích (tiêu đề h2 đầu tiên);
 * bài không có các phần đó thì trước dòng "Nguồn: …" cuối bài, hoặc ở cuối.
 */
function cadao_chen_hanh_dong( string $noi_dung, string $khoi ): string {
	foreach ( array( '<h2 class="wp-block-heading', '<p class="cadao-nguon">' ) as $moc ) {
		$vt = strpos( $noi_dung, $moc );
		if ( false !== $vt ) {
			return substr_replace( $noi_dung, $khoi, $vt, 0 );
		}
	}
	return $noi_dung . $khoi;
}

add_filter(
	'the_content',
	static function ( $noi_dung ) {
		if ( ! is_singular( CADAO_LOAI_BAI ) || get_the_ID() !== get_queried_object_id() || doing_filter( 'get_the_excerpt' ) ) {
			return $noi_dung;
		}
		$khoi = cadao_khoi_hanh_dong( get_post() );
		return '' === $khoi ? $noi_dung : cadao_chen_hanh_dong( $noi_dung, $khoi );
	},
	30
);

/** Cỡ chữ, sáng / tối người đọc đã chọn: áp ngay trong <head> để trang không nháy (T-085; bộ nhớ trình duyệt, không cookie). */
add_action(
	'wp_head',
	static function () {
		wp_print_inline_script_tag( "try{var h=document.documentElement,c=localStorage.getItem('cadao-co-chu'),d=localStorage.getItem('cadao-che-do');if(c)h.setAttribute('data-co-chu',c);if(d)h.setAttribute('data-che-do',d);}catch(e){}" );
	},
	1
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! is_singular( CADAO_LOAI_BAI ) ) {
			return;
		}
		wp_enqueue_script( 'ca-dao', plugins_url( 'assets/ca-dao.js', CADAO_THU_MUC . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
		$cau_hinh = array( 'duAn' => CADAO_TEN_DU_AN );
		// Người biên soạn đang đăng nhập (duyệt bài) không tính lượt đọc (D-064)
		if ( 'publish' === get_post_status() && ! current_user_can( 'edit_posts' ) ) {
			$cau_hinh['luotDoc'] = array( 'id' => get_queried_object_id(), 'url' => rest_url( 'cadao/v1/luot-doc/' . get_queried_object_id() ) );
			$cau_hinh['suKien']  = rest_url( 'cadao/v1/su-kien' ); // đo lường (T-052)
		}
		wp_add_inline_script( 'ca-dao', 'window.cadaoCauHinh=' . wp_json_encode( $cau_hinh ) . ';', 'before' );
	}
);
