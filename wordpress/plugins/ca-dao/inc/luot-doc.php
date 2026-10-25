<?php
/**
 * Lượt đọc tự đếm (T-081, D-064, docs/25 mục 4.7): trang bài gửi một lượt khi đã hiện trên màn hình 5 giây (máy dò
 * không chạy JavaScript thì không tính), mỗi máy một lần mỗi ngày (đánh dấu trong bộ nhớ của trình duyệt, không cookie).
 * Không lưu địa chỉ IP: chỉ cộng số theo bài theo ngày, và tên trang giới thiệu (không đường dẫn) theo ngày.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_LUOT_DOC_PHIEN_BAN = 1;

function cadao_luot_doc_bang(): string {
	global $wpdb;
	return $wpdb->prefix . 'cadao_luot_doc';
}

function cadao_gioi_thieu_bang(): string {
	global $wpdb;
	return $wpdb->prefix . 'cadao_gioi_thieu';
}

function cadao_luot_doc_tao_bang(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$bo_ma = $wpdb->get_charset_collate();
	dbDelta(
		'CREATE TABLE ' . cadao_luot_doc_bang() . " (
		post_id bigint(20) unsigned NOT NULL,
		ngay date NOT NULL,
		so int(10) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (post_id,ngay),
		KEY ngay (ngay)
		) $bo_ma;"
	);
	dbDelta(
		'CREATE TABLE ' . cadao_gioi_thieu_bang() . " (
		ngay date NOT NULL,
		ten_mien varchar(190) NOT NULL,
		so int(10) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (ngay,ten_mien)
		) $bo_ma;"
	);
	update_option( 'cadao_luot_doc_phien_ban', CADAO_LUOT_DOC_PHIEN_BAN );
}

// Cập nhật plugin mà không bật lại: tự tạo bảng
add_action(
	'plugins_loaded',
	static function () {
		if ( (int) get_option( 'cadao_luot_doc_phien_ban' ) !== CADAO_LUOT_DOC_PHIEN_BAN ) {
			cadao_luot_doc_tao_bang();
		}
	}
);

/** Cộng một lượt đọc cho bài, hôm nay (giờ của site); $tu = tên miền trang giới thiệu, '' nếu vào thẳng. */
function cadao_luot_doc_cong( int $id, string $tu = '' ): void {
	global $wpdb;
	$ngay = wp_date( 'Y-m-d' );
	$wpdb->query( $wpdb->prepare( 'INSERT INTO ' . cadao_luot_doc_bang() . ' (post_id, ngay, so) VALUES (%d, %s, 1) ON DUPLICATE KEY UPDATE so = so + 1', $id, $ngay ) );
	$wpdb->query( $wpdb->prepare( 'INSERT INTO ' . cadao_gioi_thieu_bang() . ' (ngay, ten_mien, so) VALUES (%s, %s, 1) ON DUPLICATE KEY UPDATE so = so + 1', $ngay, '' !== $tu ? $tu : '(vào thẳng)' ) );
}

/** Tổng lượt đọc của một bài. */
function cadao_luot_doc_tong( int $id ): int {
	global $wpdb;
	return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT SUM(so) FROM ' . cadao_luot_doc_bang() . ' WHERE post_id = %d', $id ) );
}

/** Ngày bắt đầu của "N ngày gần đây" (tính cả hôm nay). */
function cadao_luot_doc_tu_ngay( int $so_ngay ): string {
	return wp_date( 'Y-m-d', time() - ( $so_ngay - 1 ) * DAY_IN_SECONDS );
}

/**
 * Bài đọc nhiều trong N ngày gần đây (chỉ bài đang đăng).
 *
 * @return array<int, int> id bài → số lượt
 */
function cadao_luot_doc_nhieu( int $so_ngay = 7, int $so_bai = 5 ): array {
	global $wpdb;
	$ds = $wpdb->get_results(
		$wpdb->prepare(
			'SELECT l.post_id, SUM(l.so) AS so FROM ' . cadao_luot_doc_bang() . " l JOIN {$wpdb->posts} p ON p.ID = l.post_id
			WHERE l.ngay >= %s AND p.post_status = 'publish' AND p.post_type = %s GROUP BY l.post_id ORDER BY so DESC, l.post_id ASC LIMIT %d",
			cadao_luot_doc_tu_ngay( $so_ngay ),
			CADAO_LOAI_BAI,
			$so_bai
		)
	);
	return array_column( array_map( static fn( $r ) => array( (int) $r->post_id, (int) $r->so ), $ds ), 1, 0 );
}

/** @return array<string, int> ngày (Y-m-d) → tổng lượt, đủ N ngày gần đây (ngày không có lượt = 0) */
function cadao_luot_doc_theo_ngay( int $so_ngay = 7 ): array {
	global $wpdb;
	$ra = array();
	for ( $i = $so_ngay - 1; $i >= 0; $i-- ) {
		$ra[ wp_date( 'Y-m-d', time() - $i * DAY_IN_SECONDS ) ] = 0;
	}
	$ds = $wpdb->get_results( $wpdb->prepare( 'SELECT ngay, SUM(so) AS so FROM ' . cadao_luot_doc_bang() . ' WHERE ngay >= %s GROUP BY ngay', cadao_luot_doc_tu_ngay( $so_ngay ) ) );
	foreach ( $ds as $r ) {
		$ra[ $r->ngay ] = (int) $r->so;
	}
	return $ra;
}

/** @return array<string, int> tên miền giới thiệu → số lượt, N ngày gần đây */
function cadao_gioi_thieu_nhieu( int $so_ngay = 7, int $so = 5 ): array {
	global $wpdb;
	$ds = $wpdb->get_results( $wpdb->prepare( 'SELECT ten_mien, SUM(so) AS so FROM ' . cadao_gioi_thieu_bang() . ' WHERE ngay >= %s GROUP BY ten_mien ORDER BY so DESC LIMIT %d', cadao_luot_doc_tu_ngay( $so_ngay ), $so ) );
	return array_column( array_map( static fn( $r ) => array( $r->ten_mien, (int) $r->so ), $ds ), 1, 0 );
}

/** Máy dò có tự khai tên (Googlebot, Bingbot…, trình duyệt không giao diện) thì không tính. */
function cadao_la_may_do( string $ua ): bool {
	return '' === $ua || 1 === preg_match( '/bot\b|bot\/|crawl|spider|slurp|headless|lighthouse|preview|facebookexternalhit|zalo.*(link|preview)/i', $ua );
}

/** Tên miền trang giới thiệu hợp lệ (chữ, số, dấu chấm, gạch nối), không phải chính site; còn lại bỏ. */
function cadao_ten_mien_gioi_thieu( string $tu ): string {
	$tu = strtolower( substr( trim( $tu ), 0, 190 ) );
	if ( ! preg_match( '/^[a-z0-9.-]+\.[a-z]{2,}$/', $tu ) || wp_parse_url( home_url(), PHP_URL_HOST ) === $tu ) {
		return '';
	}
	return preg_replace( '/^(www|m)\./', '', $tu );
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'cadao/v1',
			'/luot-doc/(?P<id>\d+)',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true', // người đọc không đăng nhập
				'callback'            => static function ( WP_REST_Request $yc ) {
					$bai = get_post( (int) $yc['id'] );
					if ( ! $bai || CADAO_LOAI_BAI !== $bai->post_type || 'publish' !== $bai->post_status ) {
						return new WP_Error( 'cadao_khong_co', 'Không có bài này.', array( 'status' => 404 ) );
					}
					if ( cadao_la_may_do( (string) $yc->get_header( 'user_agent' ) ) ) {
						return new WP_REST_Response( array( 'tinh' => false ), 200 );
					}
					cadao_luot_doc_cong( $bai->ID, cadao_ten_mien_gioi_thieu( (string) ( $yc->get_param( 'tu' ) ?? '' ) ) );
					return new WP_REST_Response( array( 'tinh' => true ), 200 );
				},
			)
		);
	}
);

/** [cadao_doc_nhieu]: "Đọc nhiều tuần này" — chỉ hiện khi đã có từ 3 bài được đọc trong 7 ngày. */
add_shortcode(
	'cadao_doc_nhieu',
	static function () {
		$ds = cadao_luot_doc_nhieu( 7, 5 );
		if ( count( $ds ) < 3 ) {
			return '';
		}
		$li = '';
		foreach ( $ds as $id => $so ) {
			$li .= '<li><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></li>';
		}
		return '<section class="cadao-doc-nhieu" aria-labelledby="cadao-doc-nhieu"><h2 id="cadao-doc-nhieu">Đọc nhiều tuần này</h2><ol>' . $li . '</ol></section>';
	}
);

// Ô "Lượt đọc" trên Bảng tin: 7 ngày, bài đọc nhiều, trang giới thiệu
add_action(
	'wp_dashboard_setup',
	static function () {
		wp_add_dashboard_widget(
			'cadao_luot_doc',
			'Ca dao – Tục ngữ: lượt đọc 7 ngày',
			static function () {
				$ngay = cadao_luot_doc_theo_ngay( 7 );
				echo '<p style="font-size:1.3em"><strong>' . esc_html( number_format_i18n( array_sum( $ngay ) ) ) . '</strong> lượt đọc trong 7 ngày.</p><p class="description">';
				echo esc_html( implode( ' · ', array_map( static fn( $d, $so ) => wp_date( 'd/m', strtotime( $d ) ) . ': ' . $so, array_keys( $ngay ), $ngay ) ) ) . '</p>';
				$bai = cadao_luot_doc_nhieu( 7, 5 );
				if ( $bai ) {
					echo '<h3>Bài đọc nhiều</h3><ol>';
					foreach ( $bai as $id => $so ) {
						echo '<li><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a> — ' . esc_html( number_format_i18n( $so ) ) . '</li>';
					}
					echo '</ol>';
				}
				$tu = cadao_gioi_thieu_nhieu( 7, 5 );
				if ( $tu ) {
					echo '<h3>Người đọc đến từ</h3><ul>';
					foreach ( $tu as $ten => $so ) {
						echo '<li>' . esc_html( $ten ) . ' — ' . esc_html( number_format_i18n( $so ) ) . '</li>';
					}
					echo '</ul>';
				}
				echo '<p class="description">Đếm khi trang bài hiện 5 giây, mỗi máy một lần mỗi ngày; không cookie, không lưu địa chỉ IP. Lượt bạn xem khi đang đăng nhập không tính.</p>';
			}
		);
	}
);
