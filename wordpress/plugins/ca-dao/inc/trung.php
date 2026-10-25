<?php
/**
 * Chống trùng (D-065, docs/25 mục 4.9; T-109 nhận ra, T-110 khi lấy từ nguồn, T-111 khi viết tay): máy báo, người quyết.
 *
 * Mỗi lời (của bài, của câu trong kho, và từng dị bản) có hai dấu vân tay: (1) chuẩn hóa — NFC, chữ thường, bỏ dấu câu,
 * gộp khoảng trắng, bỏ xuống dòng; (2) bỏ thêm mọi dấu và đổi đ → d (bắt "hoà / hòa", gõ thiếu dấu). Mức giống = tỉ lệ
 * cặp âm tiết liền nhau chung nhau (Jaccard) trên bản (2) — đúng cách đo ngày 25/10/2026. Không so tiêu đề, không so
 * dòng đầu. Không tự xóa gì; không tự gộp hai bài với nhau. Câu trong kho chỉ tự gắn vào bài / câu có sẵn khi lời
 * giống hệt (kể cả chỉ khác dấu câu, chữ hoa, cách bỏ dấu) và cùng thể loại; mọi trường hợp khác vào hàng "Có thể trùng".
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRUNG_PHIEN_BAN = 1;
const CADAO_TRUNG_AM_TOI_THIEU = 6; // so gần giống chỉ với lời từ 6 âm tiết (câu ngắn dễ giống tình cờ)

/** Tên mức giống, để hiện cho người sáng lập. */
const CADAO_MUC_TRUNG = array(
	'y_het'     => 'Trùng y hệt',
	'gan_y_het' => 'Gần như y hệt',
	'di_ban'    => 'Có thể là dị bản',
	'lien_quan' => 'Liên quan',
);

function cadao_van_tay_bang(): string {
	global $wpdb;
	return $wpdb->prefix . 'cadao_van_tay';
}

function cadao_cap_am_bang(): string {
	global $wpdb;
	return $wpdb->prefix . 'cadao_cap_am';
}

function cadao_trung_bang(): string {
	global $wpdb;
	return $wpdb->prefix . 'cadao_trung';
}

function cadao_trung_tao_bang(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$bo_ma = $wpdb->get_charset_collate();
	// Dấu vân tay: muc = 'bai:<id bài>' | 'kho:<id câu trong kho>'; phan 0 = lời chính, 1… = dị bản
	dbDelta(
		'CREATE TABLE ' . cadao_van_tay_bang() . " (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		muc varchar(32) NOT NULL,
		phan smallint(5) unsigned NOT NULL DEFAULT 0,
		vt1 char(32) NOT NULL,
		vt2 char(32) NOT NULL,
		so_am smallint(5) unsigned NOT NULL DEFAULT 0,
		so_dong smallint(5) unsigned NOT NULL DEFAULT 0,
		so_cap smallint(5) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (id),
		UNIQUE KEY muc_phan (muc,phan),
		KEY vt1 (vt1),
		KEY vt2 (vt2)
		) $bo_ma;"
	);
	// Cặp âm tiết liền nhau của lời chính (từ 6 âm tiết): để tìm nhanh lời gần giống
	dbDelta(
		'CREATE TABLE ' . cadao_cap_am_bang() . " (
		cap int(10) unsigned NOT NULL,
		van_tay_id bigint(20) unsigned NOT NULL,
		PRIMARY KEY  (cap,van_tay_id),
		KEY van_tay_id (van_tay_id)
		) $bo_ma;"
	);
	// Hàng "Có thể trùng" và các quyết định: a = câu / bài mới, b = bài / câu có sẵn
	dbDelta(
		'CREATE TABLE ' . cadao_trung_bang() . " (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		a varchar(32) NOT NULL,
		b varchar(32) NOT NULL,
		muc varchar(16) NOT NULL DEFAULT '',
		giong tinyint(3) unsigned NOT NULL DEFAULT 0,
		ly_do varchar(190) NOT NULL DEFAULT '',
		trang_thai varchar(16) NOT NULL DEFAULT 'cho',
		ngay datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY cap (a,b),
		KEY trang_thai (trang_thai)
		) $bo_ma;"
	);
	update_option( 'cadao_trung_phien_ban', CADAO_TRUNG_PHIEN_BAN );
}

// Cập nhật plugin mà không bật lại: tự tạo bảng, rồi làm dần chỉ mục cho bài và kho đã có (mỗi phút một lượt)
add_action(
	'plugins_loaded',
	static function () {
		if ( (int) get_option( 'cadao_trung_phien_ban' ) !== CADAO_TRUNG_PHIEN_BAN ) {
			cadao_trung_tao_bang();
			update_option( 'cadao_trung_lam_dan', array( 'buoc' => 'bai', 'sau' => 0 ), false );
		}
	}
);

// ---------- Dấu vân tay, mức giống (T-109) ----------

/** Bản (1): NFC, chữ thường, bỏ dấu câu, gộp khoảng trắng (xuống dòng thành khoảng trắng). */
function cadao_chuan_loi( string $loi ): string {
	$s = mb_strtolower( cadao_nfc( $loi ) );
	$s = preg_replace( '/[^\p{L}\p{M}\p{N}\s]/u', ' ', $s );
	return trim( preg_replace( '/\s+/u', ' ', $s ) );
}

/** Bản (2): bỏ mọi dấu, đ → d. */
function cadao_bo_dau( string $s ): string {
	if ( class_exists( 'Normalizer' ) ) {
		$d = Normalizer::normalize( $s, Normalizer::FORM_D );
		$s = is_string( $d ) ? $d : $s;
	}
	$s = preg_replace( '/\p{Mn}/u', '', $s );
	return str_replace( array( 'đ', 'Đ' ), array( 'd', 'D' ), $s );
}

/**
 * Dấu vân tay của một lời.
 *
 * @return array{vt1: string, vt2: string, so_am: int, so_dong: int, cap: int[], so_cap: int}
 */
function cadao_van_tay( string $loi ): array {
	$c   = cadao_chuan_loi( $loi );
	$k   = cadao_bo_dau( $c );
	$am  = '' === $k ? array() : explode( ' ', $k );
	$cap = array();
	for ( $i = 0, $n = count( $am ) - 1; $i < $n; $i++ ) {
		$cap[ crc32( $am[ $i ] . ' ' . $am[ $i + 1 ] ) ] = true;
	}
	return array(
		'vt1'     => md5( $c ),
		'vt2'     => md5( $k ),
		'so_am'   => count( $am ),
		'so_dong' => count( array_filter( explode( "\n", $loi ), static fn( $d ) => '' !== trim( $d ) ) ),
		'cap'     => array_keys( $cap ),
		'so_cap'  => count( $cap ),
	);
}

/**
 * Mức giống của lời $a so với lời $b (dấu vân tay như cadao_van_tay; $chung = số cặp âm tiết chung).
 * $b_di_ban: $b là một dị bản đã ghi của bài / câu khác.
 *
 * @return array{0: string, 1: int, 2: string} [mức, % giống, lý do] — mức: y_het | gan_y_het | di_ban | lien_quan | khac
 */
function cadao_xep_muc( array $a, array $b, int $chung, bool $b_di_ban = false ): array {
	if ( $a['vt1'] === $b['vt1'] ) {
		return $b_di_ban ? array( 'di_ban', 100, 'trùng một dị bản đã ghi' ) : array( 'y_het', 100, '' );
	}
	if ( $a['vt2'] === $b['vt2'] ) {
		return $b_di_ban ? array( 'di_ban', 100, 'trùng một dị bản đã ghi (khác cách bỏ dấu)' ) : array( 'gan_y_het', 100, 'chỉ khác dấu câu, chữ hoa hoặc cách bỏ dấu' );
	}
	if ( $b_di_ban || $chung < 1 || $a['so_am'] < CADAO_TRUNG_AM_TOI_THIEU || $b['so_am'] < CADAO_TRUNG_AM_TOI_THIEU ) {
		return array( 'khac', 0, '' );
	}
	$j     = $chung / max( 1, $a['so_cap'] + $b['so_cap'] - $chung );
	$giong = (int) floor( $j * 100 + 1e-9 );
	$nho   = min( $a['so_cap'], $b['so_cap'] );
	$lon   = max( $a['so_cap'], $b['so_cap'] );
	if ( $chung / max( 1, $nho ) >= 0.9 && $nho / max( 1, $lon ) <= 0.6 ) {
		return array( 'lien_quan', $giong, 'câu ngắn nằm trong bài dài' );
	}
	if ( $j >= 0.9 ) {
		return array( 'gan_y_het', $giong, 'khác vài chữ' );
	}
	if ( min( $a['so_dong'], $b['so_dong'] ) <= 1 ) {
		return array( 'khac', $giong, '' ); // câu một dòng: chỉ xét y hệt, gần như y hệt
	}
	return $j >= 0.5 ? array( 'di_ban', $giong, '' ) : array( 'khac', $giong, '' );
}

/** Mức giống của hai lời (không cần chỉ mục). */
function cadao_do_giong( string $a, string $b, bool $b_di_ban = false ): array {
	$va = cadao_van_tay( $a );
	$vb = cadao_van_tay( $b );
	return cadao_xep_muc( $va, $vb, count( array_intersect( $va['cap'], $vb['cap'] ) ), $b_di_ban );
}

// ---------- Chỉ mục ----------

/** Lời của các khối Thơ cấp ngoài cùng của một nội dung bài: phần tử 0 = lời chính, còn lại = dị bản. */
function cadao_loi_cac_khoi_tho( string $noi_dung ): array {
	$ra = array();
	foreach ( parse_blocks( $noi_dung ) as $khoi ) {
		if ( 'core/verse' === $khoi['blockName'] ) {
			$l = cadao_loi_tu_html_tho( (string) $khoi['innerHTML'] );
			if ( '' !== $l ) {
				$ra[] = $l;
			}
		}
	}
	return $ra;
}

/** HTML của khối Thơ → lời (mỗi dòng một dòng, đã giải mã). */
function cadao_loi_tu_html_tho( string $html ): string {
	$h = preg_replace( '~<br\s*/?>~i', "\n", $html );
	$h = cadao_giai_ma( wp_strip_all_tags( $h ) );
	return trim( implode( "\n", array_map( 'cadao_cat', explode( "\n", str_replace( "\r", '', $h ) ) ) ), "\n" );
}

function cadao_van_tay_xoa( string $muc ): void {
	global $wpdb;
	$vt  = cadao_van_tay_bang();
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $vt WHERE muc = %s", $muc ) );
	if ( $ids ) {
		$wpdb->query( 'DELETE FROM ' . cadao_cap_am_bang() . ' WHERE van_tay_id IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')' );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $vt WHERE muc = %s", $muc ) );
	}
}

/**
 * Ghi lại dấu vân tay của một mục.
 *
 * @param string[] $cac_loi phần tử 0 = lời chính, còn lại = dị bản
 */
function cadao_van_tay_ghi( string $muc, array $cac_loi ): void {
	global $wpdb;
	cadao_van_tay_xoa( $muc );
	foreach ( array_values( $cac_loi ) as $phan => $loi ) {
		$v = cadao_van_tay( (string) $loi );
		if ( ! $v['so_am'] ) {
			continue;
		}
		$wpdb->insert(
			cadao_van_tay_bang(),
			array( 'muc' => $muc, 'phan' => $phan, 'vt1' => $v['vt1'], 'vt2' => $v['vt2'], 'so_am' => min( 65535, $v['so_am'] ), 'so_dong' => min( 65535, $v['so_dong'] ), 'so_cap' => min( 65535, $v['so_cap'] ) )
		);
		if ( 0 === $phan && $v['so_am'] >= CADAO_TRUNG_AM_TOI_THIEU ) {
			$id = (int) $wpdb->insert_id;
			foreach ( array_chunk( $v['cap'], 500 ) as $lo ) {
				$wpdb->query( 'INSERT IGNORE INTO ' . cadao_cap_am_bang() . ' (cap, van_tay_id) VALUES ' . implode( ',', array_map( static fn( $c ) => '(' . (int) $c . ',' . $id . ')', $lo ) ) );
			}
		}
	}
}

/** Chỉ mục cho một bài (bài trong thùng rác, bản nháp tự động: bỏ khỏi chỉ mục). */
function cadao_van_tay_bai( int $id ): void {
	$bai = get_post( $id );
	if ( ! $bai || CADAO_LOAI_BAI !== $bai->post_type || in_array( $bai->post_status, array( 'trash', 'auto-draft', 'inherit' ), true ) ) {
		cadao_van_tay_xoa( 'bai:' . $id );
		return;
	}
	cadao_van_tay_ghi( 'bai:' . $id, cadao_loi_cac_khoi_tho( $bai->post_content ) );
}

/** Chỉ mục cho một câu trong kho: chỉ câu "mới" (chưa thành bài, chưa gộp, chưa bỏ qua) và đã có lời. */
function cadao_van_tay_kho( int $kho_id ): void {
	global $wpdb;
	$m = $wpdb->get_row( $wpdb->prepare( 'SELECT id, loi, du_lieu, trang_thai FROM ' . cadao_kho_bang() . ' WHERE id = %d', $kho_id ) );
	if ( ! $m || 'moi' !== $m->trang_thai || '' === $m->loi ) {
		cadao_van_tay_xoa( 'kho:' . $kho_id );
		return;
	}
	$d = json_decode( $m->du_lieu, true ) ?: array();
	cadao_van_tay_ghi( 'kho:' . $kho_id, array_merge( array( $m->loi ), array_map( 'strval', (array) ( $d['di_ban'] ?? array() ) ) ) );
}

/**
 * Bài và câu trong kho có lời giống $loi, giống nhất trước. Không so tiêu đề hay dòng đầu.
 *
 * @param string[] $tru mục bỏ qua ('bai:12', 'kho:34')
 * @return array<int, array{muc: string, phan: int, cap_do: string, giong: int, ly_do: string}>
 */
function cadao_tim_trung( string $loi, array $tru = array(), int $so = 10 ): array {
	global $wpdb;
	$v = cadao_van_tay( $loi );
	if ( ! $v['so_am'] ) {
		return array();
	}
	$vt   = cadao_van_tay_bang();
	$tot  = array();
	$xet  = static function ( object $h, int $chung ) use ( $v, $tru, &$tot ) {
		if ( in_array( $h->muc, $tru, true ) ) {
			return;
		}
		$b = array( 'vt1' => $h->vt1, 'vt2' => $h->vt2, 'so_am' => (int) $h->so_am, 'so_dong' => (int) $h->so_dong, 'so_cap' => (int) $h->so_cap );
		[ $cap_do, $giong, $ly_do ] = cadao_xep_muc( $v, $b, $chung, (int) $h->phan > 0 );
		if ( 'khac' === $cap_do ) {
			return;
		}
		$hang = array_search( $cap_do, array_keys( CADAO_MUC_TRUNG ), true );
		$cu   = $tot[ $h->muc ] ?? null;
		if ( ! $cu || $hang < $cu['hang'] || ( $hang === $cu['hang'] && $giong > $cu['giong'] ) ) {
			$tot[ $h->muc ] = array( 'muc' => $h->muc, 'phan' => (int) $h->phan, 'cap_do' => $cap_do, 'giong' => $giong, 'ly_do' => $ly_do, 'hang' => $hang );
		}
	};
	// 1. Giống hệt (cả dị bản đã ghi)
	foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT muc, phan, vt1, vt2, so_am, so_dong, so_cap FROM $vt WHERE vt1 = %s OR vt2 = %s LIMIT 50", $v['vt1'], $v['vt2'] ) ) as $h ) {
		$xet( $h, 0 );
	}
	// 2. Gần giống: lời chính chung nhiều cặp âm tiết
	if ( $v['so_am'] >= CADAO_TRUNG_AM_TOI_THIEU && $v['cap'] ) {
		$trong = implode( ',', array_map( 'intval', $v['cap'] ) );
		$ds    = $wpdb->get_results( 'SELECT t.muc, t.phan, t.vt1, t.vt2, t.so_am, t.so_dong, t.so_cap, COUNT(*) AS chung FROM ' . cadao_cap_am_bang() . " c JOIN $vt t ON t.id = c.van_tay_id WHERE c.cap IN ($trong) GROUP BY c.van_tay_id HAVING chung >= 3 ORDER BY chung DESC LIMIT 60" );
		foreach ( $ds as $h ) {
			$xet( $h, (int) $h->chung );
		}
	}
	usort( $tot, static fn( $x, $y ) => array( $x['hang'], -$x['giong'] ) <=> array( $y['hang'], -$y['giong'] ) );
	return array_map( static function ( $x ) {
		unset( $x['hang'] );
		return $x;
	}, array_slice( $tot, 0, $so ) );
}

// ---------- Mục: bài hay câu trong kho ----------

/** @return array{0: string, 1: int} ['bai'|'kho', id] */
function cadao_tach_muc( string $muc ): array {
	[ $loai, $id ] = array_pad( explode( ':', $muc, 2 ), 2, '0' );
	return array( $loai, (int) $id );
}

/** Nhóm thể loại để so (thành ngữ và tục ngữ chung một nhóm: nguồn gộp hai loại). */
function cadao_nhom_the_loai( string $muc ): string {
	global $wpdb;
	[ $loai, $id ] = cadao_tach_muc( $muc );
	if ( 'kho' === $loai ) {
		$slug = CADAO_THE_LOAI_TU_LOAI_GOC[ (string) $wpdb->get_var( $wpdb->prepare( 'SELECT loai FROM ' . cadao_kho_bang() . ' WHERE id = %d', $id ) ) ] ?? 'ca-dao';
	} else {
		$the  = get_the_terms( $id, 'the_loai' );
		$slug = ( $the && ! is_wp_error( $the ) ) ? $the[0]->slug : 'ca-dao';
	}
	return in_array( $slug, array( 'thanh-ngu', 'tuc-ngu' ), true ) ? 'tn-tn' : $slug;
}

/** Bài ứng với một mục: 'bai:<id>' → id; 'kho:<id>' → bài đã tạo từ câu đó (0 nếu chưa). */
function cadao_bai_cua_muc( string $muc ): int {
	global $wpdb;
	[ $loai, $id ] = cadao_tach_muc( $muc );
	return 'bai' === $loai ? $id : (int) $wpdb->get_var( $wpdb->prepare( 'SELECT post_id FROM ' . cadao_kho_bang() . ' WHERE id = %d', $id ) );
}

/** Mô tả một mục cho trang quản trị và trình soạn. */
function cadao_mo_ta_muc( string $muc ): array {
	global $wpdb;
	[ $loai, $id ] = cadao_tach_muc( $muc );
	if ( 'kho' === $loai ) {
		$m = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_kho_bang() . ' WHERE id = %d', $id ) );
		if ( ! $m ) {
			return array( 'co' => false );
		}
		if ( $m->post_id ) {
			return cadao_mo_ta_muc( 'bai:' . $m->post_id );
		}
		$ten_loai = cadao_bo_doc()['cadao_me']['loai'][ $m->loai ] ?? $m->loai;
		return array( 'co' => true, 'loai' => 'kho', 'id' => $id, 'tieu_de' => $m->tieu_de ?: strtok( $m->loi, "\n" ), 'loi' => $m->loi, 'the_loai' => $ten_loai, 'trang_thai' => 'Trong Kho thu thập, chưa thành bài', 'nguon' => $m->url, 'sua' => cadao_url_trang( CADAO_TRANG_KHO, array( 'tim' => strtok( $m->loi, "\n" ) ) ), 'xem' => '' );
	}
	$bai = get_post( $id );
	if ( ! $bai || 'trash' === $bai->post_status ) {
		return array( 'co' => false );
	}
	$loi = cadao_loi_cac_khoi_tho( $bai->post_content )[0] ?? '';
	return array( 'co' => true, 'loai' => 'bai', 'id' => $id, 'tieu_de' => get_the_title( $bai ), 'loi' => $loi, 'the_loai' => cadao_ten_the_loai( $bai ), 'trang_thai' => get_post_status_object( $bai->post_status )->label ?? $bai->post_status, 'nguon' => '', 'sua' => (string) get_edit_post_link( $id, 'url' ), 'xem' => 'publish' === $bai->post_status ? get_permalink( $bai ) : '' );
}

// ---------- Hàng "Có thể trùng" ----------

/** Ghi một cặp vào hàng chờ (đã có — kể cả theo chiều ngược lại — thì giữ quyết định cũ). */
function cadao_trung_ghi_cho( string $a, string $b, string $cap_do, int $giong, string $ly_do ): void {
	global $wpdb;
	// Cặp đã ghi theo chiều ngược lại (bên kia là câu mới, chờ quyết): không ghi thêm
	if ( $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . cadao_trung_bang() . ' WHERE a = %s AND b = %s', $b, $a ) ) ) {
		return;
	}
	$wpdb->query( $wpdb->prepare( 'INSERT IGNORE INTO ' . cadao_trung_bang() . " (a, b, muc, giong, ly_do, trang_thai, ngay) VALUES (%s, %s, %s, %d, %s, 'cho', %s)", $a, $b, $cap_do, $giong, $ly_do, current_time( 'mysql' ) ) );
}

/** Đặt quyết định cho một cặp: 'khac' (là bài khác), 'de_sau', 'da_gop', 'bo' (một bên đã vào thùng rác). */
function cadao_trung_dat( string $a, string $b, string $trang_thai ): void {
	global $wpdb;
	$t = cadao_trung_bang();
	if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE a = %s AND b = %s", $a, $b ) ) && $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE a = %s AND b = %s", $b, $a ) ) ) {
		[ $a, $b ] = array( $b, $a ); // cặp đã ghi theo chiều ngược lại
	}
	$co = $wpdb->update( $t, array( 'trang_thai' => $trang_thai ), array( 'a' => $a, 'b' => $b ) );
	if ( ! $co && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE a = %s AND b = %s", $a, $b ) ) ) {
		$wpdb->insert( cadao_trung_bang(), array( 'a' => $a, 'b' => $b, 'trang_thai' => $trang_thai, 'ngay' => current_time( 'mysql' ) ) );
	}
}

/** Những mục người sáng lập đã nói "là bài khác" với $muc (cả hai chiều). */
function cadao_trung_da_khac( string $muc ): array {
	global $wpdb;
	$t = cadao_trung_bang();
	return array_merge(
		$wpdb->get_col( $wpdb->prepare( "SELECT b FROM $t WHERE a = %s AND trang_thai = 'khac'", $muc ) ),
		$wpdb->get_col( $wpdb->prepare( "SELECT a FROM $t WHERE b = %s AND trang_thai = 'khac'", $muc ) )
	);
}

/** Số cặp đang chờ quyết. */
function cadao_trung_dem_cho(): int {
	global $wpdb;
	return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . cadao_trung_bang() . " WHERE trang_thai = 'cho'" );
}

// ---------- Khi lấy từ nguồn (T-110) ----------

/**
 * So một câu "mới" trong kho với bài đã có và các câu khác trong kho.
 * Giống hệt (kể cả chỉ khác dấu câu, chữ hoa, cách bỏ dấu) và cùng thể loại → tự gắn vào bài / câu có sẵn ("Đã gộp").
 * Khác thể loại, khác vài chữ, có thể là dị bản, trùng một dị bản đã ghi → hàng "Có thể trùng".
 *
 * @return string 'khong' | 'da_gop' | 'cho_quyet'
 */
function cadao_trung_kiem_kho( int $kho_id ): string {
	global $wpdb;
	$m = $wpdb->get_row( $wpdb->prepare( 'SELECT id, loi, trang_thai FROM ' . cadao_kho_bang() . ' WHERE id = %d', $kho_id ) );
	if ( ! $m || 'moi' !== $m->trang_thai || '' === $m->loi ) {
		return 'khong';
	}
	$khoa = 'kho:' . $kho_id;
	foreach ( cadao_tim_trung( $m->loi, array_merge( array( $khoa ), cadao_trung_da_khac( $khoa ) ), 5 ) as $t ) {
		if ( 'lien_quan' === $t['cap_do'] ) {
			continue;
		}
		$cung_loai = cadao_nhom_the_loai( $khoa ) === cadao_nhom_the_loai( $t['muc'] );
		if ( $cung_loai && 0 === $t['phan'] && 100 === $t['giong'] && in_array( $t['cap_do'], array( 'y_het', 'gan_y_het' ), true ) ) {
			cadao_trung_gop( $khoa, $t['muc'], 'trung' );
			return 'da_gop';
		}
		$ly_do = $cung_loai ? $t['ly_do'] : trim( 'khác thể loại' . ( $t['ly_do'] ? '; ' . $t['ly_do'] : '' ) );
		cadao_trung_ghi_cho( $khoa, $t['muc'], $t['cap_do'], $t['giong'], $ly_do );
		$tt = $wpdb->get_var( $wpdb->prepare( 'SELECT trang_thai FROM ' . cadao_trung_bang() . ' WHERE a = %s AND b = %s', $khoa, $t['muc'] ) );
		if ( in_array( $tt, array( 'cho', 'de_sau' ), true ) ) {
			return 'cho_quyet';
		}
	}
	return 'khong';
}

/** Sau khi kho lưu một câu: cập nhật chỉ mục; câu đã đọc trang gốc thì so ngay. */
function cadao_trung_sau_khi_luu( int $kho_id ): void {
	global $wpdb;
	if ( ! $kho_id || (int) get_option( 'cadao_trung_phien_ban' ) !== CADAO_TRUNG_PHIEN_BAN ) {
		return; // bảng chưa tạo (đang nâng cấp)
	}
	cadao_van_tay_kho( $kho_id );
	if ( preg_match( '/^\d{4}-/', (string) $wpdb->get_var( $wpdb->prepare( 'SELECT doc_trang FROM ' . cadao_kho_bang() . ' WHERE id = %d', $kho_id ) ) ) ) {
		cadao_trung_kiem_kho( $kho_id );
	}
}

/** Câu trong kho có đang chờ người sáng lập quyết không (máy tạo bài hằng ngày bỏ qua câu đó). */
function cadao_trung_dang_cho( int $kho_id ): bool {
	global $wpdb;
	return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . cadao_trung_bang() . " WHERE a = %s AND trang_thai IN ('cho','de_sau') LIMIT 1", 'kho:' . $kho_id ) );
}

/**
 * Gắn câu kho $a vào bài / câu $b. $lam = 'trung' (cùng lời: bổ sung dị bản, chú thích của nguồn kia) hoặc
 * 'di_ban' (lời của $a thành một dị bản của $b). Bài đích chưa ai sửa tay thì làm lại từ kho; đã sửa tay thì chỉ chèn
 * thêm khối Thơ dị bản (đúng từng ký tự) khi $lam = 'di_ban'. Không xóa gì.
 */
function cadao_trung_gop( string $a, string $b, string $lam ): bool {
	global $wpdb;
	[ $loai_a, $id_a ] = cadao_tach_muc( $a );
	if ( 'kho' !== $loai_a || $a === $b ) {
		return false; // gộp hai bài đã đăng: T-101
	}
	$m = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_kho_bang() . ' WHERE id = %d', $id_a ) );
	if ( ! $m || $m->post_id ) {
		return false;
	}
	// Đích là câu kho đã thành bài: gắn thẳng vào bài
	$bai = cadao_bai_cua_muc( $b );
	$dich = $bai ? 'bai:' . $bai : $b;
	$wpdb->update( cadao_kho_bang(), array( 'trang_thai' => 'da_gop', 'gop_vao' => $dich, 'gop_lam' => $lam ), array( 'id' => $id_a ) );
	// Các câu đã gộp vào $a đi theo $a sang đích mới
	$wpdb->update( cadao_kho_bang(), array( 'gop_vao' => $dich ), array( 'gop_vao' => $a ) );
	cadao_van_tay_xoa( $a );
	cadao_trung_dat( $a, $b, 'da_gop' );
	if ( $bai && ! cadao_bai_lam_moi_tu_kho( $bai ) && 'di_ban' === $lam ) {
		cadao_them_di_ban( $bai, $m->loi );
	}
	if ( $bai ) {
		cadao_van_tay_bai( $bai );
	}
	return true;
}

/** Tách một câu đã gộp ra lại (người sáng lập bấm "Tách ra"): câu thành "mới", cặp ghi "là bài khác". */
function cadao_trung_tach( int $kho_id ): bool {
	global $wpdb;
	$m = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_kho_bang() . ' WHERE id = %d', $kho_id ) );
	if ( ! $m || 'da_gop' !== $m->trang_thai ) {
		return false;
	}
	$wpdb->update( cadao_kho_bang(), array( 'trang_thai' => 'moi', 'gop_vao' => '', 'gop_lam' => '' ), array( 'id' => $kho_id ) );
	$wpdb->query( $wpdb->prepare( 'UPDATE ' . cadao_trung_bang() . " SET trang_thai = 'khac' WHERE a = %s AND trang_thai = 'da_gop'", 'kho:' . $kho_id ) );
	cadao_trung_dat( 'kho:' . $kho_id, $m->gop_vao, 'khac' );
	cadao_van_tay_kho( $kho_id );
	$bai = cadao_bai_cua_muc( $m->gop_vao );
	if ( $bai ) {
		cadao_bai_lam_moi_tu_kho( $bai );
		cadao_van_tay_bai( $bai );
	}
	return true;
}

/**
 * Bài tạo từ kho mà chưa ai sửa tay (nội dung khớp dấu băm): làm lại nội dung từ kho (gồm câu đã gộp, dị bản thêm).
 *
 * @return bool true nếu bài là bài từ kho chưa sửa tay (đã làm lại)
 */
function cadao_bai_lam_moi_tu_kho( int $bai_id ): bool {
	global $wpdb;
	$muc   = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_kho_bang() . ' WHERE post_id = %d ORDER BY id LIMIT 1', $bai_id ) );
	$bai   = get_post( $bai_id );
	$nguon = $muc ? cadao_nguon_lay( $muc->nguon_id ) : null;
	if ( ! $muc || ! $bai || ! $nguon || 'trash' === $bai->post_status ) {
		return false;
	}
	$bam = (string) get_post_meta( $bai_id, '_cadao_bam', true );
	if ( '' === $bam || md5( $bai->post_content ) !== $bam ) {
		return false;
	}
	$moi = cadao_noi_dung_tu_kho( $muc, $nguon );
	if ( $moi !== $bai->post_content ) {
		wp_update_post( array( 'ID' => $bai_id, 'post_content' => $moi ) );
	}
	update_post_meta( $bai_id, '_cadao_bam', md5( get_post( $bai_id )->post_content ) );
	return true;
}

/**
 * Thêm một dị bản (lời giữ đúng từng ký tự) vào bài: lưu ở ô _cadao_di_ban_them (bài từ kho làm lại vẫn giữ); bài đã
 * sửa tay thì chèn khối Thơ vào phần "Dị bản" (chưa có thì tạo).
 */
function cadao_them_di_ban( int $bai_id, string $loi ): bool {
	$bai = get_post( $bai_id );
	if ( ! $bai || '' === trim( $loi ) ) {
		return false;
	}
	$da   = array_map( 'cadao_chuan_loi', cadao_loi_cac_khoi_tho( $bai->post_content ) );
	$them = (array) ( get_post_meta( $bai_id, '_cadao_di_ban_them', true ) ?: array() );
	if ( ! in_array( cadao_chuan_loi( $loi ), array_merge( $da, array_map( 'cadao_chuan_loi', $them ) ), true ) ) {
		$them[] = $loi;
		update_post_meta( $bai_id, '_cadao_di_ban_them', $them );
	}
	if ( cadao_bai_lam_moi_tu_kho( $bai_id ) || in_array( cadao_chuan_loi( $loi ), $da, true ) ) {
		return true;
	}
	wp_update_post( array( 'ID' => $bai_id, 'post_content' => cadao_chen_di_ban( $bai->post_content, $loi ) ) );
	return true;
}

/** Chèn một khối Thơ dị bản vào nội dung: cuối phần "Dị bản" nếu có, không thì trước "Chú thích" / dòng Nguồn / cuối bài. */
function cadao_chen_di_ban( string $noi_dung, string $loi ): string {
	$tho     = cadao_khoi_tho( $loi );
	$tieu_de = cadao_khoi_tieu_de( 'Dị bản' );
	$vt      = strpos( $noi_dung, $tieu_de );
	$tu      = false === $vt ? 0 : $vt + strlen( $tieu_de );
	$moc     = array();
	foreach ( array( '<!-- wp:heading', '<!-- wp:paragraph {"className":"cadao-chu-thich-nguon"}' ) as $chu ) {
		$p = strpos( $noi_dung, $chu, $tu );
		if ( false !== $p ) {
			$moc[] = $p;
		}
	}
	$chen = false === $vt ? $tieu_de . "\n\n" . $tho : $tho;
	if ( ! $moc ) {
		return rtrim( $noi_dung ) . "\n\n" . $chen;
	}
	$p = min( $moc );
	return substr( $noi_dung, 0, $p ) . $chen . "\n\n" . substr( $noi_dung, $p );
}

// ---------- Bài (khi lưu, khi viết tay — T-111) ----------

/** So một bài với các bài khác; cặp nghi trùng vào hàng chờ (để danh sách bài có nhãn "Có thể trùng"). */
function cadao_trung_kiem_bai( int $id ): void {
	$bai = get_post( $id );
	if ( ! $bai || in_array( $bai->post_status, array( 'trash', 'auto-draft', 'inherit' ), true ) ) {
		return;
	}
	$loi = cadao_loi_cac_khoi_tho( $bai->post_content )[0] ?? '';
	if ( '' === $loi ) {
		return;
	}
	$khoa = 'bai:' . $id;
	foreach ( cadao_tim_trung( $loi, array_merge( array( $khoa ), cadao_trung_da_khac( $khoa ) ), 3 ) as $t ) {
		if ( 'bai' === cadao_tach_muc( $t['muc'] )[0] && 'lien_quan' !== $t['cap_do'] && 0 === $t['phan'] ) {
			// Một cặp hai bài chỉ ghi một lần (bài mới hơn là a)
			[ , $id_b ] = cadao_tach_muc( $t['muc'] );
			[ $a, $b ]  = $id > $id_b ? array( $khoa, $t['muc'] ) : array( $t['muc'], $khoa );
			cadao_trung_ghi_cho( $a, $b, $t['cap_do'], $t['giong'], $t['ly_do'] );
		}
	}
}

add_action(
	'save_post_' . CADAO_LOAI_BAI,
	static function ( $id, $bai ) {
		if ( wp_is_post_revision( $id ) || wp_is_post_autosave( $id ) ) {
			return;
		}
		cadao_van_tay_bai( (int) $id );
		cadao_trung_kiem_bai( (int) $id );
	},
	20,
	2
);

add_action(
	'wp_trash_post',
	static function ( $id ) {
		if ( CADAO_LOAI_BAI === get_post_type( $id ) ) {
			global $wpdb;
			cadao_van_tay_xoa( 'bai:' . $id );
			$wpdb->query( $wpdb->prepare( 'UPDATE ' . cadao_trung_bang() . " SET trang_thai = 'bo' WHERE (a = %s OR b = %s) AND trang_thai IN ('cho','de_sau')", 'bai:' . $id, 'bai:' . $id ) );
		}
	}
);

/** Nhãn "Có thể trùng" trong danh sách bài. */
add_filter(
	'display_post_states',
	static function ( $nhan, $bai ) {
		global $wpdb;
		if ( CADAO_LOAI_BAI === $bai->post_type && $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . cadao_trung_bang() . " WHERE (a = %s OR b = %s) AND trang_thai IN ('cho','de_sau') LIMIT 1", 'bai:' . $bai->ID, 'bai:' . $bai->ID ) ) ) {
			$nhan['cadao_trung'] = 'Có thể trùng';
		}
		return $nhan;
	},
	10,
	2
);

// ---------- Làm dần: chỉ mục cho bài, kho đã có, rồi so từng câu (dọn trùng sẵn có) ----------

/**
 * Một lượt làm dần (WP-Cron mỗi phút cho tới khi xong).
 *
 * @return array{buoc: string, sau: int}
 */
function cadao_trung_lam_dan( int $giay = 20 ): array {
	global $wpdb;
	$han = microtime( true ) + $giay;
	$t   = get_option( 'cadao_trung_lam_dan', array( 'buoc' => 'xong', 'sau' => 0 ) );
	$kho = cadao_kho_bang();
	while ( 'xong' !== $t['buoc'] && microtime( true ) < $han ) {
		if ( 'bai' === $t['buoc'] ) {
			$ds = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash','auto-draft','inherit') AND ID > %d ORDER BY ID LIMIT 100", CADAO_LOAI_BAI, $t['sau'] ) );
			foreach ( $ds as $id ) {
				cadao_van_tay_bai( (int) $id );
				$t['sau'] = (int) $id;
			}
			if ( ! $ds ) {
				$t = array( 'buoc' => 'kho', 'sau' => 0 );
			}
		} elseif ( 'kho' === $t['buoc'] ) {
			$ds = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $kho WHERE trang_thai = 'moi' AND loi <> '' AND id > %d ORDER BY id LIMIT 200", $t['sau'] ) );
			foreach ( $ds as $id ) {
				cadao_van_tay_kho( (int) $id );
				$t['sau'] = (int) $id;
			}
			if ( ! $ds ) {
				$t = array( 'buoc' => 'so_bai', 'sau' => 0 );
			}
		} elseif ( 'so_bai' === $t['buoc'] ) {
			$ds = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash','auto-draft','inherit') AND ID > %d ORDER BY ID LIMIT 50", CADAO_LOAI_BAI, $t['sau'] ) );
			foreach ( $ds as $id ) {
				cadao_trung_kiem_bai( (int) $id );
				$t['sau'] = (int) $id;
			}
			if ( ! $ds ) {
				$t = array( 'buoc' => 'so_kho', 'sau' => 0 );
			}
		} else { // so_kho: chỉ câu đã đọc trang gốc (có dị bản, chú thích) — câu chưa đọc được so khi đọc xong
			$ds = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $kho WHERE trang_thai = 'moi' AND loi <> '' AND doc_trang LIKE '2%%' AND id > %d ORDER BY id LIMIT 50", $t['sau'] ) );
			foreach ( $ds as $id ) {
				cadao_trung_kiem_kho( (int) $id );
				$t['sau'] = (int) $id;
			}
			if ( ! $ds ) {
				$t = array( 'buoc' => 'xong', 'sau' => 0, 'luc' => current_time( 'mysql' ) );
			}
		}
		update_option( 'cadao_trung_lam_dan', $t, false );
	}
	return $t;
}

add_action(
	'cadao_thu_thap', // lịch 5 phút của nguồn tự động; WP-Cron chạy trước lượt lấy bài
	static function () {
		if ( 'xong' !== ( get_option( 'cadao_trung_lam_dan', array( 'buoc' => 'xong' ) )['buoc'] ?? 'xong' ) ) {
			cadao_trung_lam_dan( cadao_giay_moi_luot() );
		}
	},
	5
);

// ---------- Thống kê (chỉ đọc): chạy lại phép đo 25/10/2026 bằng chính các hàm trên ----------

/**
 * Đếm trùng trong toàn kho (mọi trạng thái), như phép đo ngày 25/10/2026 (`docs/25` mục 4.9). Không ghi gì.
 *
 * @return array<string, int|array>
 */
function cadao_trung_thong_ke(): array {
	global $wpdb;
	$ds  = $wpdb->get_results( 'SELECT id, loi, du_lieu FROM ' . cadao_kho_bang() . " WHERE loi <> ''" );
	$v   = array();
	$vt1 = array();
	$vt2 = array();
	$db  = array(); // dấu (1) của dị bản → câu ghi dị bản đó
	$chi = array(); // cặp âm tiết → các câu
	foreach ( $ds as $r ) {
		$x            = cadao_van_tay( $r->loi );
		$v[ $r->id ]  = $x;
		$vt1[ $x['vt1'] ][] = $r->id;
		$vt2[ $x['vt2'] ][] = $r->id;
		foreach ( (array) ( json_decode( $r->du_lieu, true )['di_ban'] ?? array() ) as $l ) {
			$db[ cadao_van_tay( (string) $l )['vt1'] ][] = $r->id;
		}
		if ( $x['so_am'] >= CADAO_TRUNG_AM_TOI_THIEU ) {
			foreach ( $x['cap'] as $c ) {
				$chi[ $c ][] = $r->id;
			}
		}
	}
	$thua = static fn( $nhom ) => array_sum( array_map( static fn( $g ) => count( $g ) - 1, array_filter( $nhom, static fn( $g ) => count( $g ) > 1 ) ) );
	$y_het = $thua( $vt1 );
	$la_db = 0;
	foreach ( $v as $id => $x ) {
		if ( isset( $db[ $x['vt1'] ] ) && ! in_array( $id, $db[ $x['vt1'] ], true ) ) {
			++$la_db;
		}
	}
	$gan = array( '80' => 0, '50' => 0 );
	foreach ( $v as $id => $x ) {
		if ( $x['so_am'] < CADAO_TRUNG_AM_TOI_THIEU ) {
			continue;
		}
		$dem = array();
		foreach ( $x['cap'] as $c ) {
			if ( count( $chi[ $c ] ) > 200 ) {
				continue; // cặp quá phổ biến: bỏ khi tìm ứng viên (như phép đo)
			}
			foreach ( $chi[ $c ] as $j ) {
				if ( $j > $id ) {
					$dem[ $j ] = ( $dem[ $j ] ?? 0 ) + 1;
				}
			}
		}
		foreach ( $dem as $j => $chung ) {
			if ( $chung < 3 || $x['vt2'] === $v[ $j ]['vt2'] ) {
				continue;
			}
			$jac = $chung / ( $x['so_cap'] + $v[ $j ]['so_cap'] - $chung ); // như phép đo: chỉ đếm cặp không quá phổ biến
			if ( $jac >= 0.8 ) {
				++$gan['80'];
			} elseif ( $jac >= 0.5 ) {
				++$gan['50'];
			}
		}
	}
	$dau = array();
	foreach ( $ds as $r ) {
		$dau[ cadao_chuan_loi( (string) strtok( $r->loi, "\n" ) ) ][] = $r->id;
	}
	return array(
		'so_cau'        => count( $ds ),
		'y_het'         => $y_het,
		'chi_khac_dau'  => $thua( $vt2 ) - $y_het,
		'la_di_ban'     => $la_db,
		'giong_80'      => $gan['80'],
		'giong_50_80'   => $gan['50'],
		'dong_dau_chung' => count( array_filter( $dau, static fn( $g ) => count( $g ) > 1 ) ),
	);
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'cadao trung-thong-ke',
		static function () {
			foreach ( cadao_trung_thong_ke() as $k => $so ) {
				WP_CLI::line( "$k: $so" );
			}
			$lam = get_option( 'cadao_trung_lam_dan', array() );
			global $wpdb;
			WP_CLI::line( 'lam_dan: ' . wp_json_encode( $lam ) );
			foreach ( $wpdb->get_results( 'SELECT trang_thai, muc, COUNT(*) AS so FROM ' . cadao_trung_bang() . ' GROUP BY trang_thai, muc' ) as $r ) {
				WP_CLI::line( "hang_cho {$r->trang_thai} {$r->muc}: {$r->so}" );
			}
			WP_CLI::line( 'kho_da_gop: ' . (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . cadao_kho_bang() . " WHERE trang_thai = 'da_gop'" ) );
		}
	);
}
