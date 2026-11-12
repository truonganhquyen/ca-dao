<?php
/**
 * "Đã lưu" (T-051, docs/20 mục 1): nút ☆ Lưu trên trang bài nhớ bài vào bộ nhớ của trình duyệt (localStorage, không
 * cookie, không gửi về máy chủ — site không biết ai lưu gì). Trang "Đã lưu" (/da-luu/) đọc danh sách đó trên máy người
 * đọc. Trình duyệt chặn bộ nhớ thì nút không hiện, trang báo lý do.
 */

defined( 'ABSPATH' ) || exit;

/** Đường dẫn trang "Đã lưu" ('' nếu chưa có). */
function cadao_url_da_luu(): string {
	$id = (int) get_option( 'cadao_trang_da_luu' );
	return $id && 'publish' === get_post_status( $id ) ? (string) get_permalink( $id ) : '';
}

/** [cadao_da_luu]: danh sách bài đã lưu (JavaScript dựng từ bộ nhớ trình duyệt). */
add_shortcode(
	'cadao_da_luu',
	static function () {
		return '<div class="cadao-da-luu">'
			. '<noscript><p>Trang này cần JavaScript: danh sách bài đã lưu nằm trong trình duyệt của bạn, không nằm trên máy chủ.</p></noscript>'
			. '<p class="cadao-da-luu-so" role="status" aria-live="polite"></p>'
			. '<p class="cadao-da-luu-trong" hidden>Bạn chưa lưu bài nào. Trên trang một bài, bấm <strong>☆ Lưu</strong> để giữ bài đó ở đây.</p>'
			. '<p class="cadao-da-luu-chan" hidden>Trình duyệt đang chặn bộ nhớ (thường do chế độ ẩn danh hoặc cài đặt riêng tư) nên không lưu được bài.</p>'
			. '<ol class="cadao-da-luu-ds"></ol>'
			. '<p class="cadao-da-luu-ghi-chu">Danh sách chỉ nằm trong trình duyệt trên máy này — site không biết bạn lưu gì. Đổi máy, dùng chế độ ẩn danh hay xóa dữ liệu trình duyệt thì danh sách không còn.</p>'
			. '</div>';
	}
);

/** Trang "Đã lưu" (/da-luu/) tạo một lần. */
add_action(
	'init',
	static function () {
		if ( get_option( 'cadao_trang_da_luu' ) || wp_installing() ) {
			return;
		}
		$co = get_page_by_path( 'da-luu', OBJECT, 'page' );
		$id = $co ? $co->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Đã lưu', 'post_name' => 'da-luu', 'post_content' => "<!-- wp:shortcode -->\n[cadao_da_luu]\n<!-- /wp:shortcode -->" ) );
		update_option( 'cadao_trang_da_luu', (int) $id, false );
	},
	30
);

add_filter( 'wp_robots', static fn( $r ) => is_page( (int) get_option( 'cadao_trang_da_luu' ) ) ? $r + array( 'noindex' => true ) : $r );

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! is_page( (int) get_option( 'cadao_trang_da_luu' ) ) ) {
			return;
		}
		wp_enqueue_script( 'ca-dao-da-luu', plugins_url( 'assets/da-luu.js', CADAO_THU_MUC . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
);
