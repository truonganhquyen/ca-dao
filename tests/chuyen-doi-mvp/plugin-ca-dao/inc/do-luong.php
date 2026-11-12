<?php
/**
 * Đo lường không cookie (T-052, D-047): đúng ba sự kiện, cộng số theo ngày, không lưu IP, không định danh.
 * - tim_khong_ket_qua: chuỗi tìm (đã chuẩn hóa) không ra kết quả → biên soạn bài nào tiếp theo. Chuỗi trông như email,
 *   số điện thoại thì bỏ; chuỗi tìm giữ một năm.
 * - trich_dan_sao_chep: bài + kiểu (thông dụng, APA, chỉ lời) → North Star.
 * - chu_giai_mo: mục từ được chạm mở → mục từ nào cần viết kỹ.
 * - chia_se (D-069): bài + kiểu (gửi qua hộp chia sẻ của máy, sao chép liên kết) → số chia sẻ dưới tiêu đề bài; mỗi máy
 *   một lần mỗi ngày mỗi bài (đánh dấu trong bộ nhớ trình duyệt).
 * Người biên soạn đang đăng nhập không tính (như lượt đọc, D-064). Mỗi máy một lần mỗi ngày cho cùng sự kiện và khóa
 * (dấu băm tạm của IP, không lưu IP — T-118). Số liệu xem ở Bảng tin.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_SU_KIEN_PHIEN_BAN = 1;
const CADAO_SU_KIEN           = array( 'tim_khong_ket_qua', 'trich_dan_sao_chep', 'chu_giai_mo', 'chia_se' );
const CADAO_KIEU_CHIA_SE      = array(
	'web_share'         => 'gửi qua hộp chia sẻ',
	'sao_chep_lien_ket' => 'sao chép liên kết',
);
const CADAO_KIEU_SAO_CHEP     = array(
	'thong_dung' => 'kèm nguồn, thông dụng',
	'apa'        => 'kèm nguồn, APA 7',
	'chi_loi'    => 'chỉ lời',
);

function cadao_su_kien_bang(): string {
	global $wpdb;
	return $wpdb->prefix . 'cadao_su_kien';
}

function cadao_su_kien_tao_bang(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta(
		'CREATE TABLE ' . cadao_su_kien_bang() . ' (
		ngay date NOT NULL,
		loai varchar(30) NOT NULL,
		khoa varchar(190) NOT NULL,
		so int(10) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (ngay,loai,khoa),
		KEY loai (loai,ngay)
		) ' . $wpdb->get_charset_collate() . ';'
	);
	update_option( 'cadao_su_kien_phien_ban', CADAO_SU_KIEN_PHIEN_BAN );
}

add_action(
	'plugins_loaded',
	static function () {
		if ( (int) get_option( 'cadao_su_kien_phien_ban' ) !== CADAO_SU_KIEN_PHIEN_BAN ) {
			cadao_su_kien_tao_bang();
		}
	}
);

/** Chuỗi tìm để ghi: chữ thường, gọn khoảng trắng, tối đa 80 ký tự; '' nếu quá ngắn hoặc trông như email, số điện thoại. */
function cadao_su_kien_chuoi_tim( string $q ): string {
	$q = mb_substr( trim( (string) preg_replace( '/\s+/u', ' ', mb_strtolower( wp_strip_all_tags( $q ) ) ) ), 0, 80 );
	if ( mb_strlen( $q ) < 2 || str_contains( $q, '@' ) || preg_match( '/\d{6,}/', (string) preg_replace( '/[\s.\-]/', '', $q ) ) ) {
		return '';
	}
	return $q;
}

/**
 * Ghi một sự kiện (đã kiểm). $du: q (tìm), bai + kieu (sao chép), muc (chú giải).
 *
 * @return bool đã ghi hay chưa
 */
function cadao_su_kien_ghi( string $loai, array $du ): bool {
	global $wpdb;
	$t = cadao_su_kien_bang();
	if ( 'tim_khong_ket_qua' === $loai ) {
		$khoa = cadao_su_kien_chuoi_tim( (string) ( $du['q'] ?? '' ) );
		if ( '' === $khoa ) {
			return false;
		}
		$kq = cadao_tim_ket_qua( $khoa );
		if ( $kq['bai'] || $kq['muc_tu'] ) { // thật sự không ra mới ghi
			return false;
		}
		// Chặn tràn bảng: tối đa 300 chuỗi khác nhau mỗi ngày
		$moi = ! $wpdb->get_var( $wpdb->prepare( "SELECT 1 FROM $t WHERE ngay = %s AND loai = %s AND khoa = %s", wp_date( 'Y-m-d' ), $loai, $khoa ) );
		if ( $moi && 300 <= (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $t WHERE ngay = %s AND loai = %s", wp_date( 'Y-m-d' ), $loai ) ) ) {
			return false;
		}
	} elseif ( 'trich_dan_sao_chep' === $loai ) {
		$bai  = get_post( (int) ( $du['bai'] ?? 0 ) );
		$kieu = (string) ( $du['kieu'] ?? '' );
		if ( ! $bai || CADAO_LOAI_BAI !== $bai->post_type || 'publish' !== $bai->post_status || ! isset( CADAO_KIEU_SAO_CHEP[ $kieu ] ) ) {
			return false;
		}
		$khoa = $bai->ID . ':' . $kieu;
	} elseif ( 'chia_se' === $loai ) {
		$bai  = get_post( (int) ( $du['bai'] ?? 0 ) );
		$kieu = (string) ( $du['kieu'] ?? '' );
		if ( ! $bai || CADAO_LOAI_BAI !== $bai->post_type || 'publish' !== $bai->post_status || ! isset( CADAO_KIEU_CHIA_SE[ $kieu ] ) ) {
			return false;
		}
		$khoa = $bai->ID . ':' . $kieu;
	} elseif ( 'chu_giai_mo' === $loai ) {
		$muc = get_term( (int) ( $du['muc'] ?? 0 ), 'tu_kho' );
		if ( ! $muc || is_wp_error( $muc ) ) {
			return false;
		}
		$khoa = (string) $muc->term_id;
	} else {
		return false;
	}
	if ( ! cadao_lan_dau_trong_ngay( $loai . ':' . $khoa ) ) {
		return false; // cùng máy, cùng sự kiện, cùng ngày: không cộng thêm (T-118)
	}
	$wpdb->query( $wpdb->prepare( "INSERT INTO $t (ngay, loai, khoa, so) VALUES (%s, %s, %s, 1) ON DUPLICATE KEY UPDATE so = so + 1", wp_date( 'Y-m-d' ), $loai, $khoa ) );
	return true;
}

/**
 * Tổng theo khoa của một sự kiện trong N ngày gần đây, nhiều nhất trước.
 *
 * @return array<string, int>
 */
function cadao_su_kien_nhieu( string $loai, int $so_ngay = 30, int $so = 10 ): array {
	global $wpdb;
	$ds = $wpdb->get_results( $wpdb->prepare( 'SELECT khoa, SUM(so) AS tong FROM ' . cadao_su_kien_bang() . ' WHERE loai = %s AND ngay >= %s GROUP BY khoa ORDER BY tong DESC, khoa LIMIT %d', $loai, cadao_luot_doc_tu_ngay( $so_ngay ), $so ) );
	return array_combine( array_map( static fn( $r ) => (string) $r->khoa, $ds ), array_map( static fn( $r ) => (int) $r->tong, $ds ) ) ?: array();
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'cadao/v1',
			'/su-kien',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true', // người đọc không đăng nhập
				'callback'            => static function ( WP_REST_Request $yc ) {
					$loai = (string) $yc->get_param( 'loai' );
					if ( ! in_array( $loai, CADAO_SU_KIEN, true ) ) {
						return new WP_Error( 'cadao_su_kien', 'Không có sự kiện này.', array( 'status' => 400 ) );
					}
					if ( cadao_la_may_do( (string) $yc->get_header( 'user_agent' ) ) || current_user_can( 'edit_posts' ) ) {
						return new WP_REST_Response( array( 'tinh' => false ), 200 );
					}
					$du = array_map( static fn( $v ) => is_scalar( $v ) ? (string) $v : '', array_intersect_key( (array) $yc->get_params(), array_flip( array( 'q', 'bai', 'kieu', 'muc' ) ) ) );
					return new WP_REST_Response( array( 'tinh' => cadao_su_kien_ghi( $loai, $du ) ), 200 );
				},
			)
		);
	}
);

// Chuỗi tìm cũ hơn một năm: xóa (mỗi đêm)
add_action(
	'cadao_tao_bai_hang_ngay',
	static function () {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . cadao_su_kien_bang() . " WHERE loai = 'tim_khong_ket_qua' AND ngay < %s", cadao_luot_doc_tu_ngay( 366 ) ) );
	},
	30
);

/** Ô "Người đọc cần gì" trên Bảng tin: 30 ngày, cách đọc hằng tuần. */
function cadao_su_kien_o_bang_tin(): void {
	$tim = cadao_su_kien_nhieu( 'tim_khong_ket_qua', 30, 10 );
	echo '<h3>Tìm mà không ra</h3>';
	if ( $tim ) {
		echo '<ol>';
		foreach ( $tim as $q => $so ) {
			echo '<li><a href="' . esc_url( add_query_arg( 'q', rawurlencode( $q ), home_url( '/tim/' ) ) ) . '">' . esc_html( $q ) . '</a> — ' . esc_html( number_format_i18n( $so ) ) . '</li>';
		}
		echo '</ol>';
	} else {
		echo '<p class="description">Chưa có.</p>';
	}
	$sc   = cadao_su_kien_nhieu( 'trich_dan_sao_chep', 30, 1000 );
	$kieu = array_fill_keys( array_keys( CADAO_KIEU_SAO_CHEP ), 0 );
	$bai  = array();
	foreach ( $sc as $k => $so ) {
		list( $id, $ki ) = array_pad( explode( ':', $k, 2 ), 2, '' );
		if ( isset( $kieu[ $ki ] ) ) {
			$kieu[ $ki ]     += $so;
			$bai[ (int) $id ] = ( $bai[ (int) $id ] ?? 0 ) + $so;
		}
	}
	arsort( $bai );
	$phan = array();
	foreach ( CADAO_KIEU_SAO_CHEP as $k => $ten ) {
		if ( $kieu[ $k ] ) {
			$phan[] = $ten . ': ' . number_format_i18n( $kieu[ $k ] );
		}
	}
	echo '<h3>Sao chép trích dẫn</h3><p><strong>' . esc_html( number_format_i18n( array_sum( $kieu ) ) ) . '</strong> lần' . ( $phan ? ' (' . esc_html( implode( ', ', $phan ) ) . ')' : '' ) . '.</p>';
	if ( $bai ) {
		echo '<ol>';
		foreach ( array_slice( $bai, 0, 5, true ) as $id => $so ) {
			echo '<li><a href="' . esc_url( (string) get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a> — ' . esc_html( number_format_i18n( $so ) ) . '</li>';
		}
		echo '</ol>';
	}
	$muc = cadao_su_kien_nhieu( 'chu_giai_mo', 30, 10 );
	$cs = cadao_su_kien_nhieu( 'chia_se', 30, 1000 );
	echo '<h3>Chia sẻ</h3><p><strong>' . esc_html( number_format_i18n( array_sum( $cs ) ) ) . '</strong> lần (gửi qua hộp chia sẻ của máy hoặc sao chép liên kết).</p>';
	echo '<h3>Từ được chạm xem nghĩa nhiều</h3>';
	if ( $muc ) {
		echo '<ol>';
		foreach ( $muc as $id => $so ) {
			$t = get_term( (int) $id, 'tu_kho' );
			if ( $t && ! is_wp_error( $t ) ) {
				echo '<li><a href="' . esc_url( (string) get_edit_term_link( $t, 'tu_kho' ) ) . '">' . esc_html( $t->name ) . '</a> — ' . esc_html( number_format_i18n( $so ) ) . '</li>';
			}
		}
		echo '</ol>';
	} else {
		echo '<p class="description">Chưa có.</p>';
	}
	echo '<p class="description"><strong>Đọc mỗi tuần:</strong> câu tìm không ra mà nhiều người gõ → nên biên soạn thêm bài đó; bài được sao chép trích dẫn nhiều → bài đang được dùng, kiểm lại cho thật chắc; từ được chạm nhiều → viết chú thích, mục từ kỹ hơn. Không cookie, không lưu IP, không biết ai làm; lượt khi bạn đang đăng nhập không tính.</p>';
}

add_action( 'wp_dashboard_setup', static fn() => wp_add_dashboard_widget( 'cadao_su_kien', 'Ca dao – Tục ngữ: người đọc cần gì (30 ngày)', 'cadao_su_kien_o_bang_tin' ) );
