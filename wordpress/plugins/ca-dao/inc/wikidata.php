<?php
/**
 * Gắn Wikidata (T-086, D-061, docs/25 mục 4.2): với mỗi mục từ (từ được chú thích) và mỗi đáp án câu đố, máy tìm thực
 * thể Wikidata có nhãn / tên gọi khác tiếng Việt trùng đúng, bỏ trang định hướng, đưa vào hàng chờ "Xác nhận" (Đúng /
 * Sai / Bỏ qua). CHỈ thực thể người sáng lập bấm "Đúng" mới được dùng: tọa độ, mô tả, bài Wikipedia, ảnh Commons — ảnh
 * chỉ khi giấy phép mở (CC0, phạm vi công cộng, CC BY, CC BY-SA), tải bản nhỏ về thư viện ảnh của site, ghi tác giả và
 * giấy phép dưới ảnh. Wikidata là CC0. Gọi API tuần tự, ≥ 1 giây một yêu cầu, User-Agent ghi tên dự án, có điểm dừng.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRANG_WIKIDATA = 'cadao-wikidata';
const CADAO_WD_PHIEN_BAN   = 1;

function cadao_wd_bang(): string {
	global $wpdb;
	return $wpdb->prefix . 'cadao_xac_nhan';
}

function cadao_wd_tao_bang(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	// khoa: 'tu:<id mục từ>' | 'dap:<md5 đáp án>'; trang_thai: cho | dung | sai | bo | khong_thay
	dbDelta(
		'CREATE TABLE ' . cadao_wd_bang() . " (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		khoa varchar(64) NOT NULL,
		nhan varchar(190) NOT NULL DEFAULT '',
		wd varchar(20) NOT NULL DEFAULT '',
		du_lieu longtext NOT NULL,
		trang_thai varchar(16) NOT NULL DEFAULT 'cho',
		ngay datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY khoa (khoa),
		KEY trang_thai (trang_thai)
		) {$wpdb->get_charset_collate()};"
	);
	update_option( 'cadao_wd_phien_ban', CADAO_WD_PHIEN_BAN );
}

add_action(
	'plugins_loaded',
	static function () {
		if ( (int) get_option( 'cadao_wd_phien_ban' ) !== CADAO_WD_PHIEN_BAN ) {
			cadao_wd_tao_bang();
		}
	}
);

/** Một yêu cầu tới API của Wikimedia (JSON), giữ nhịp. null khi lỗi (lần sau thử lại). */
function cadao_wd_goi( string $url, array $tham ): ?array {
	static $lan_cuoi = 0.0;
	$cho = $lan_cuoi + (float) apply_filters( 'cadao_nhip_giay', 1 ) - microtime( true );
	if ( $cho > 0 ) {
		usleep( (int) ( $cho * 1e6 ) );
	}
	$lan_cuoi = microtime( true );
	$tl       = wp_remote_get( add_query_arg( $tham + array( 'format' => 'json' ), $url ), array( 'timeout' => 20, 'user-agent' => CADAO_UA ) );
	if ( is_wp_error( $tl ) || 200 !== (int) wp_remote_retrieve_response_code( $tl ) ) {
		return null;
	}
	$j = json_decode( wp_remote_retrieve_body( $tl ), true );
	return is_array( $j ) ? $j : null;
}

/** Đáp án câu đố: chuẩn hóa để dùng chung giữa các bài cùng đáp án. */
function cadao_khoa_dap_an( string $dap_an ): string {
	return 'dap:' . md5( cadao_chuan_loi( $dap_an ) );
}

/**
 * Tìm ứng viên Wikidata cho một nhãn: nhãn / tên khác tiếng Việt trùng đúng (không phân biệt hoa thường), không phải
 * trang định hướng. Kèm tọa độ (P625), ảnh (P18), bài Wikipedia tiếng Việt.
 *
 * @return array|null|false ứng viên; null = không thấy; false = lỗi mạng (lần sau thử lại)
 */
function cadao_wd_tim( string $nhan ) {
	$j = cadao_wd_goi( 'https://www.wikidata.org/w/api.php', array( 'action' => 'wbsearchentities', 'search' => $nhan, 'language' => 'vi', 'uselang' => 'vi', 'type' => 'item', 'limit' => 7 ) );
	if ( null === $j ) {
		return false;
	}
	$muon = mb_strtolower( cadao_nfc( trim( $nhan ) ) );
	foreach ( (array) ( $j['search'] ?? array() ) as $x ) {
		$khop = mb_strtolower( cadao_nfc( (string) ( $x['match']['text'] ?? '' ) ) ) === $muon && 'vi' === ( $x['match']['language'] ?? '' );
		if ( ! $khop || preg_match( '/định hướng|disambiguation|Wikimedia/iu', (string) ( $x['description'] ?? '' ) ) ) {
			continue;
		}
		$e = cadao_wd_goi( 'https://www.wikidata.org/w/api.php', array( 'action' => 'wbgetentities', 'ids' => $x['id'], 'props' => 'claims|sitelinks|descriptions|labels', 'languages' => 'vi', 'sitefilter' => 'viwiki' ) );
		if ( null === $e ) {
			return false;
		}
		$the = $e['entities'][ $x['id'] ] ?? array();
		$p31 = array_map( static fn( $c ) => $c['mainsnak']['datavalue']['value']['id'] ?? '', $the['claims']['P31'] ?? array() );
		if ( in_array( 'Q4167410', $p31, true ) ) {
			continue; // trang định hướng
		}
		$td   = $the['claims']['P625'][0]['mainsnak']['datavalue']['value'] ?? null;
		$wiki = $the['sitelinks']['viwiki']['title'] ?? '';
		return array(
			'wd'      => (string) $x['id'],
			'nhan_wd' => (string) ( $the['labels']['vi']['value'] ?? $x['label'] ?? '' ),
			'mo_ta'   => (string) ( $the['descriptions']['vi']['value'] ?? $x['description'] ?? '' ),
			'toa_do'  => $td ? array( round( (float) $td['latitude'], 5 ), round( (float) $td['longitude'], 5 ) ) : null,
			'anh'     => (string) ( $the['claims']['P18'][0]['mainsnak']['datavalue']['value'] ?? '' ),
			'wiki'    => '' !== $wiki ? 'https://vi.wikipedia.org/wiki/' . rawurlencode( str_replace( ' ', '_', $wiki ) ) : '',
		);
	}
	return null;
}

/** Ghi kết quả tìm vào hàng chờ. */
function cadao_wd_ghi( string $khoa, string $nhan, $ung_vien ): void {
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			'INSERT IGNORE INTO ' . cadao_wd_bang() . ' (khoa, nhan, wd, du_lieu, trang_thai, ngay) VALUES (%s, %s, %s, %s, %s, %s)',
			$khoa,
			mb_substr( $nhan, 0, 190 ),
			$ung_vien['wd'] ?? '',
			wp_json_encode( $ung_vien ?: array(), JSON_UNESCAPED_UNICODE ),
			$ung_vien ? 'cho' : 'khong_thay',
			current_time( 'mysql' )
		)
	);
}

/**
 * Một lượt làm dần: mục từ chưa xét, rồi đáp án câu đố chưa xét.
 *
 * @return int số nhãn đã xét
 */
function cadao_wd_lam_dan( int $giay = 60 ): int {
	global $wpdb;
	$han = microtime( true ) + $giay;
	$so  = 0;
	$bang = cadao_wd_bang();
	// Mục từ (từ được chú thích)
	$ds = get_terms( array( 'taxonomy' => 'tu_kho', 'hide_empty' => false, 'number' => 40, 'meta_query' => array( array( 'key' => '_cadao_wd_xet', 'compare' => 'NOT EXISTS' ) ) ) );
	foreach ( is_wp_error( $ds ) ? array() : $ds as $t ) {
		if ( microtime( true ) > $han ) {
			return $so;
		}
		$uv = cadao_wd_tim( $t->name );
		if ( false === $uv ) {
			return $so; // lỗi mạng: dừng, lượt sau làm tiếp
		}
		cadao_wd_ghi( 'tu:' . $t->term_id, $t->name, $uv );
		update_term_meta( $t->term_id, '_cadao_wd_xet', gmdate( 'Y-m-d' ) );
		++$so;
	}
	// Đáp án câu đố
	$bai = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => array( 'publish', 'pending', 'draft', 'future' ), 'numberposts' => 200, 'tax_query' => array( array( 'taxonomy' => 'the_loai', 'field' => 'slug', 'terms' => 'cau-do' ) ), 'meta_query' => array( array( 'key' => '_cadao_wd_xet', 'compare' => 'NOT EXISTS' ) ) ) );
	foreach ( $bai as $b ) {
		if ( microtime( true ) > $han ) {
			return $so;
		}
		$dap = cadao_dap_an_cua_bai( $b->post_content );
		if ( '' !== $dap && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $bang WHERE khoa = %s", cadao_khoa_dap_an( $dap ) ) ) ) {
			$uv = cadao_wd_tim( $dap );
			if ( false === $uv ) {
				return $so;
			}
			cadao_wd_ghi( cadao_khoa_dap_an( $dap ), $dap, $uv );
			++$so;
		}
		update_post_meta( $b->ID, '_cadao_wd_xet', gmdate( 'Y-m-d' ) );
	}
	return $so;
}

/** Đáp án (lời giải) trong nội dung một câu đố: đoạn trong khối "Đáp án". */
function cadao_dap_an_cua_bai( string $noi_dung ): string {
	return preg_match( '~<details\b[^>]*>\s*<summary>Đáp án</summary>(.*?)</details>~s', $noi_dung, $m ) ? trim( cadao_van_ban( $m[1] ) ) : '';
}

add_action(
	'cadao_thu_thap',
	static function () {
		cadao_wd_lam_dan( defined( 'WP_CLI' ) && WP_CLI ? 90 : 15 );
	},
	9
);

/** Ảnh Commons: chỉ giấy phép mở. Tải bản rộng 480 px về thư viện ảnh, ghi tác giả, giấy phép. */
function cadao_wd_tai_anh( string $tep ): ?array {
	$j  = cadao_wd_goi( 'https://commons.wikimedia.org/w/api.php', array( 'action' => 'query', 'titles' => 'File:' . $tep, 'prop' => 'imageinfo', 'iiprop' => 'url|extmetadata', 'iiurlwidth' => 480 ) );
	$ii = $j ? ( array_values( $j['query']['pages'] ?? array() )[0]['imageinfo'][0] ?? null ) : null;
	if ( ! $ii ) {
		return null;
	}
	$m        = $ii['extmetadata'] ?? array();
	$giay_phep = trim( wp_strip_all_tags( (string) ( $m['LicenseShortName']['value'] ?? '' ) ) );
	if ( ! preg_match( '/^(CC0|CC[ -]BY(-SA)?( [0-9.]+)?|Public domain|PD\b|PD-)/i', $giay_phep ) ) {
		return null; // giấy phép không mở (hoặc không rõ): không dùng
	}
	$tac_gia = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) ( $m['Artist']['value'] ?? '' ) ) ) ) ?: 'Không rõ tác giả';
	$anh     = array( 'tac_gia' => $tac_gia, 'giay_phep' => $giay_phep, 'giay_phep_url' => esc_url_raw( (string) ( $m['LicenseUrl']['value'] ?? '' ) ), 'trang' => esc_url_raw( (string) ( $ii['descriptionurl'] ?? '' ) ), 'id' => 0, 'url' => '' );
	$nguon   = (string) ( $ii['thumburl'] ?? $ii['url'] ?? '' );
	if ( '' === $nguon ) {
		return null;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tam = download_url( $nguon, 30 );
	if ( is_wp_error( $tam ) ) {
		return null;
	}
	$id = media_handle_sideload( array( 'name' => sanitize_file_name( wp_basename( (string) wp_parse_url( $nguon, PHP_URL_PATH ) ) ), 'tmp_name' => $tam ), 0, $tep, array( 'post_excerpt' => cadao_ghi_cong_anh( $anh ) ) );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tam );
		return null;
	}
	update_post_meta( $id, '_cadao_anh_commons', $anh );
	$anh['id']  = (int) $id;
	$anh['url'] = (string) wp_get_attachment_url( $id );
	return $anh;
}

/** Dòng ghi công dưới ảnh: "Ảnh: <tác giả> · <giấy phép> · Wikimedia Commons". */
function cadao_ghi_cong_anh( array $anh ): string {
	return 'Ảnh: ' . $anh['tac_gia'] . ' · ' . $anh['giay_phep'] . ' · Wikimedia Commons';
}

/** Người sáng lập bấm "Đúng": lấy ảnh (nếu giấy phép mở), lưu cho mục từ / đáp án. */
function cadao_wd_xac_nhan( int $id, string $quyet ): bool {
	global $wpdb;
	$bang = cadao_wd_bang();
	$r    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $bang WHERE id = %d", $id ) );
	if ( ! $r || ! in_array( $quyet, array( 'dung', 'sai', 'bo', 'cho' ), true ) ) {
		return false;
	}
	$d = json_decode( $r->du_lieu, true ) ?: array();
	if ( 'dung' === $quyet && ! empty( $d['anh'] ) && empty( $d['anh_da_tai'] ) ) {
		$d['anh_da_tai'] = cadao_wd_tai_anh( $d['anh'] ) ?: false;
	}
	$wpdb->update( $bang, array( 'trang_thai' => $quyet, 'du_lieu' => wp_json_encode( $d, JSON_UNESCAPED_UNICODE ) ), array( 'id' => $id ) );
	if ( str_starts_with( $r->khoa, 'tu:' ) ) {
		$tid = (int) substr( $r->khoa, 3 );
		if ( 'dung' === $quyet ) {
			update_term_meta( $tid, '_cadao_wd', $d + array( 'wd' => $r->wd ) );
		} else {
			delete_term_meta( $tid, '_cadao_wd' );
		}
	}
	return true;
}

/** Dữ liệu Wikidata đã xác nhận của một đáp án câu đố (null nếu chưa). */
function cadao_wd_cua_dap_an( string $dap_an ): ?array {
	global $wpdb;
	$r = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_wd_bang() . " WHERE khoa = %s AND trang_thai = 'dung'", cadao_khoa_dap_an( $dap_an ) ) );
	return $r ? ( json_decode( $r->du_lieu, true ) ?: null ) : null;
}

/** Dữ liệu Wikidata đã xác nhận của một mục từ (null nếu chưa). */
function cadao_wd_cua_muc_tu( int $term_id ): ?array {
	$d = get_term_meta( $term_id, '_cadao_wd', true );
	return is_array( $d ) && $d ? $d : null;
}

// ---------- Trang "Xác nhận Wikidata" ----------

add_action(
	'admin_menu',
	static function () {
		global $wpdb;
		$so = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . cadao_wd_bang() . " WHERE trang_thai = 'cho'" );
		add_submenu_page( 'edit.php?post_type=' . CADAO_LOAI_BAI, 'Xác nhận Wikidata', 'Xác nhận Wikidata' . ( $so ? ' <span class="awaiting-mod">' . $so . '</span>' : '' ), 'manage_options', CADAO_TRANG_WIKIDATA, 'cadao_trang_wikidata' );
	},
	13
);

function cadao_trang_wikidata(): void {
	global $wpdb;
	$bang = cadao_wd_bang();
	$tabs = array( 'cho' => 'Chờ bạn xác nhận', 'dung' => 'Đúng', 'sai' => 'Sai, bỏ qua' );
	$tab  = sanitize_key( wp_unslash( $_GET['tab'] ?? 'cho' ) );
	$tab  = isset( $tabs[ $tab ] ) ? $tab : 'cho';
	echo '<div class="wrap"><h1>Xác nhận Wikidata</h1>';
	cadao_hien_bao();
	echo '<p>Máy tìm trên Wikidata thực thể có tên tiếng Việt trùng với từ được chú thích hoặc đáp án câu đố. Máy có thể khớp sai (ví dụ một địa danh trùng tên với một niên hiệu), nên <strong>chỉ thực thể bạn bấm "Đúng" mới được dùng</strong>: ảnh (chỉ ảnh có giấy phép mở, ghi tác giả), tọa độ trên bản đồ nhỏ, mô tả, bài Wikipedia. Bấm "Sai" nếu không phải; "Bỏ qua" nếu không chắc.</p><ul class="subsubsub">';
	$i = 0;
	foreach ( $tabs as $k => $ten ) {
		$dk  = 'sai' === $k ? "trang_thai IN ('sai','bo')" : $wpdb->prepare( 'trang_thai = %s', $k );
		$dem = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bang WHERE $dk" );
		echo ( $i++ ? ' | ' : '' ) . '<li><a ' . ( $tab === $k ? 'class="current" ' : '' ) . 'href="' . esc_url( cadao_url_trang( CADAO_TRANG_WIKIDATA, array( 'tab' => $k ) ) ) . '">' . esc_html( $ten ) . ' <span class="count">(' . $dem . ')</span></a></li>';
	}
	echo '</ul><div style="clear:both"></div>';
	$dk = 'sai' === $tab ? "trang_thai IN ('sai','bo')" : $wpdb->prepare( 'trang_thai = %s', $tab );
	$ds = $wpdb->get_results( "SELECT * FROM $bang WHERE $dk ORDER BY id LIMIT 100" );
	if ( ! $ds ) {
		echo '<p>Không có mục nào.</p></div>';
		return;
	}
	foreach ( $ds as $r ) {
		$d    = json_decode( $r->du_lieu, true ) ?: array();
		$cua  = '';
		if ( str_starts_with( $r->khoa, 'tu:' ) ) {
			$t   = get_term( (int) substr( $r->khoa, 3 ), 'tu_kho' );
			$cua = $t && ! is_wp_error( $t ) ? 'Từ được chú thích: <strong>' . esc_html( $t->name ) . '</strong> — ' . esc_html( wp_trim_words( $t->description, 30 ) ) . ' <a href="' . esc_url( get_term_link( $t ) ) . '">(' . (int) $t->count . ' bài)</a>' : '';
		} else {
			$cua = 'Đáp án câu đố: <strong>' . esc_html( $r->nhan ) . '</strong>';
		}
		echo '<div class="card" style="max-width:none;margin:12px 0;padding:12px 16px"><p style="margin-top:0">' . $cua . '</p>';
		echo '<p>Wikidata: <a href="' . esc_url( 'https://www.wikidata.org/wiki/' . $r->wd ) . '" target="_blank" rel="noopener"><strong>' . esc_html( $d['nhan_wd'] ?? $r->nhan ) . '</strong> (' . esc_html( $r->wd ) . ') ↗</a> — ' . esc_html( $d['mo_ta'] ?? '' ) . '</p><p class="description">'
			. ( ! empty( $d['toa_do'] ) ? 'Có tọa độ (' . esc_html( implode( ', ', $d['toa_do'] ) ) . '). ' : '' )
			. ( ! empty( $d['anh'] ) ? 'Có ảnh trên Commons. ' : 'Không có ảnh. ' )
			. ( ! empty( $d['wiki'] ) ? '<a href="' . esc_url( $d['wiki'] ) . '" target="_blank" rel="noopener">Bài Wikipedia ↗</a>' : '' ) . '</p>';
		if ( ! empty( $d['anh'] ) && 'cho' === $tab ) {
			echo '<p><img src="' . esc_url( 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode( $d['anh'] ) . '?width=160' ) . '" alt="" width="160" loading="lazy" style="height:auto;border-radius:4px"></p>';
		}
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:flex;flex-wrap:wrap;gap:8px">';
		wp_nonce_field( 'cadao_wd_quyet' );
		echo '<input type="hidden" name="action" value="cadao_wd_quyet"><input type="hidden" name="id" value="' . (int) $r->id . '"><input type="hidden" name="tab" value="' . esc_attr( $tab ) . '">';
		if ( 'dung' !== $tab ) {
			echo '<button class="button button-primary" name="quyet" value="dung">Đúng</button>';
		}
		if ( 'sai' !== $tab ) {
			echo '<button class="button" name="quyet" value="sai">Sai</button>';
		}
		echo 'cho' === $tab ? '<button class="button" name="quyet" value="bo">Bỏ qua</button>' : '<button class="button" name="quyet" value="cho">Đưa lại vào hàng chờ</button>';
		echo '</form></div>';
	}
	echo '</div>';
}

add_action(
	'admin_post_cadao_wd_quyet',
	static function () {
		cadao_kiem_quyen( 'cadao_wd_quyet' );
		$q = sanitize_key( wp_unslash( $_POST['quyet'] ?? '' ) );
		cadao_wd_xac_nhan( (int) ( $_POST['id'] ?? 0 ), $q );
		cadao_ve( CADAO_TRANG_WIKIDATA, array( 'dung' => 'Đã xác nhận: đúng.', 'sai' => 'Đã ghi: sai.', 'bo' => 'Đã bỏ qua.', 'cho' => 'Đã đưa lại vào hàng chờ.' )[ $q ] ?? 'Đã ghi.', array( 'tab' => sanitize_key( wp_unslash( $_POST['tab'] ?? 'cho' ) ) ) );
	}
);

// ---------- Hiển thị (T-087, T-088): ảnh có ghi công, bản đồ nhỏ, Wikipedia — chỉ thực thể đã xác nhận ----------

/** Khung của bản đồ nhỏ assets/ban-do-vn.svg [tây, đông, bắc, nam] — phải khớp KHUNG trong scripts/sinh-ban-do.mjs. */
const CADAO_BAN_DO_KHUNG = array( 102.0, 117.6, 23.6, 8.0 );

/** Ảnh, bản đồ nhỏ, liên kết của một thực thể Wikidata đã xác nhận (HTML; rỗng nếu không có gì). */
function cadao_html_dia_danh( array $wd ): string {
	$ra  = '';
	$anh = $wd['anh_da_tai'] ?? null;
	if ( is_array( $anh ) && ! empty( $anh['url'] ) ) {
		$gp  = $anh['giay_phep_url'] ? '<a href="' . esc_url( $anh['giay_phep_url'] ) . '" rel="noopener">' . esc_html( $anh['giay_phep'] ) . '</a>' : esc_html( $anh['giay_phep'] );
		$ra .= '<figure class="cadao-anh-wd"><img src="' . esc_url( $anh['url'] ) . '" alt="' . esc_attr( $wd['nhan_wd'] ?? '' ) . '" loading="lazy" decoding="async">'
			. '<figcaption>Ảnh: ' . esc_html( $anh['tac_gia'] ) . ' · ' . $gp . ' · <a href="' . esc_url( $anh['trang'] ) . '" rel="noopener">Wikimedia Commons</a></figcaption></figure>';
	}
	if ( ! empty( $wd['toa_do'] ) ) {
		[ $lat, $lon ] = $wd['toa_do'];
		[ $tay, $dong, $bac, $nam ] = CADAO_BAN_DO_KHUNG;
		if ( $lon >= $tay && $lon <= $dong && $lat <= $bac && $lat >= $nam ) {
			$ra .= '<div class="cadao-ban-do-nho" role="img" aria-label="' . esc_attr( 'Vị trí của ' . ( $wd['nhan_wd'] ?? '' ) . ' trên bản đồ Việt Nam' ) . '"><img src="' . esc_url( plugins_url( 'assets/ban-do-vn.svg', CADAO_THU_MUC . '/ca-dao.php' ) ) . '" alt="" loading="lazy">'
				. '<span class="cadao-cham" style="left:' . round( ( $lon - $tay ) / ( $dong - $tay ) * 100, 2 ) . '%;top:' . round( ( $bac - $lat ) / ( $bac - $nam ) * 100, 2 ) . '%"></span></div>';
		}
		$ra .= '<p class="cadao-lien-ket-wd"><a href="' . esc_url( sprintf( 'https://www.openstreetmap.org/?mlat=%1$s&mlon=%2$s#map=11/%1$s/%2$s', $lat, $lon ) ) . '" rel="noopener">Mở bản đồ lớn <span aria-hidden="true">↗</span></a></p>';
	}
	if ( ! empty( $wd['wiki'] ) ) {
		$ra .= '<p class="cadao-lien-ket-wd"><a href="' . esc_url( $wd['wiki'] ) . '" rel="noopener">Đọc thêm trên Wikipedia <span aria-hidden="true">↗</span></a></p>';
	}
	return $ra;
}

// Trang mục từ: ảnh, bản đồ, Wikipedia (sau lời giải và nguồn)
add_filter(
	'cadao_muc_tu_them',
	static function ( $html, $term_id ) {
		$wd = cadao_wd_cua_muc_tu( (int) $term_id );
		return $html . ( $wd ? cadao_html_dia_danh( $wd ) : '' );
	},
	10,
	2
);

/** Cho theme: dữ liệu thêm của một chú thích trong bài (mục từ, số bài, phần địa danh đã xác nhận). */
function cadao_them_cho_chu_thich( string $tu, string $noi_dung ): array {
	$t = cadao_muc_tu( array( 'tu' => $tu, 'noi_dung' => $noi_dung ), false );
	if ( ! $t ) {
		return array();
	}
	$wd = cadao_wd_cua_muc_tu( (int) $t->term_id );
	return array( 'url' => (string) get_term_link( $t ), 'so_bai' => (int) $t->count, 'them' => $wd ? cadao_html_dia_danh( $wd ) : '' );
}

/** Cho theme: ảnh, bản đồ của một đáp án câu đố đã xác nhận (rỗng nếu chưa). */
function cadao_them_cho_dap_an( string $dap_an ): string {
	$wd = cadao_wd_cua_dap_an( $dap_an );
	return $wd ? cadao_html_dia_danh( array_intersect_key( $wd, array( 'anh_da_tai' => 1, 'nhan_wd' => 1, 'wiki' => 1 ) ) ) : '';
}
