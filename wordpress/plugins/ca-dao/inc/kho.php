<?php
/**
 * Kho thu thập: bảng riêng trong cơ sở dữ liệu, chứa mọi mục bộ đọc lấy về (lời nguyên văn, dị bản, chú thích,
 * địa chỉ trang). Kho không hiện ra trang; mỗi ngày một số mục được tạo thành bài **Chờ duyệt** (D-054).
 */

defined( 'ABSPATH' ) || exit;

const CADAO_KHO_PHIEN_BAN = 1;

function cadao_kho_bang(): string {
	global $wpdb;
	return $wpdb->prefix . 'cadao_kho';
}

function cadao_kho_tao_bang() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$bang = cadao_kho_bang();
	dbDelta(
		"CREATE TABLE $bang (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		nguon_id varchar(64) NOT NULL,
		ma_goc varchar(64) NOT NULL,
		url varchar(500) NOT NULL DEFAULT '',
		loai varchar(32) NOT NULL DEFAULT '',
		tieu_de text NOT NULL,
		loi longtext NOT NULL,
		du_lieu longtext NOT NULL,
		ngay_sua_goc varchar(32) NOT NULL DEFAULT '',
		ngay_lay datetime NOT NULL,
		trang_thai varchar(16) NOT NULL DEFAULT 'moi',
		post_id bigint(20) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (id),
		UNIQUE KEY nguon_ma (nguon_id, ma_goc),
		KEY trang_thai (trang_thai)
		) {$wpdb->get_charset_collate()};"
	);
	update_option( 'cadao_kho_phien_ban', CADAO_KHO_PHIEN_BAN );
}

// Cập nhật plugin mà không bật lại: tự tạo/sửa bảng
add_action(
	'plugins_loaded',
	static function () {
		if ( (int) get_option( 'cadao_kho_phien_ban' ) !== CADAO_KHO_PHIEN_BAN ) {
			cadao_kho_tao_bang();
		}
	}
);

/**
 * Lưu (hoặc cập nhật) một mục vào kho. Mục đã thành bài thì không ghi đè bài — chỉ cập nhật kho.
 *
 * @param array{ma_goc: string|int, url: string, loai: string, tieu_de: string, loi: string, di_ban?: array, chu_thich?: array, chu_de_goc?: array, co?: array, ngay_sua_goc?: string} $muc
 * @return string 'moi' | 'cap_nhat' | 'giu_nguyen'
 */
function cadao_kho_luu( string $nguon_id, array $muc ): string {
	global $wpdb;
	$bang    = cadao_kho_bang();
	$ma_goc  = (string) $muc['ma_goc'];
	$du_lieu = wp_json_encode(
		array(
			'di_ban'     => array_values( $muc['di_ban'] ?? array() ),
			'chu_thich'  => array_values( $muc['chu_thich'] ?? array() ),
			'chu_de_goc' => array_values( $muc['chu_de_goc'] ?? array() ),
			'co'         => array_values( $muc['co'] ?? array() ),
			'cau_hoi'    => $muc['cau_hoi'] ?? null,
			'dap_an'     => $muc['dap_an'] ?? null,
		),
		JSON_UNESCAPED_UNICODE
	);
	$hang = array(
		'url'          => (string) $muc['url'],
		'loai'         => (string) $muc['loai'],
		'tieu_de'      => (string) $muc['tieu_de'],
		'loi'          => (string) $muc['loi'],
		'du_lieu'      => $du_lieu,
		'ngay_sua_goc' => (string) ( $muc['ngay_sua_goc'] ?? '' ),
	);
	$cu = $wpdb->get_row( $wpdb->prepare( "SELECT id, loi, du_lieu, ngay_sua_goc FROM $bang WHERE nguon_id = %s AND ma_goc = %s", $nguon_id, $ma_goc ) );
	if ( ! $cu ) {
		$wpdb->insert( $bang, $hang + array( 'nguon_id' => $nguon_id, 'ma_goc' => $ma_goc, 'ngay_lay' => current_time( 'mysql' ), 'trang_thai' => 'moi' ) );
		return 'moi';
	}
	if ( $cu->loi === $hang['loi'] && $cu->du_lieu === $hang['du_lieu'] && $cu->ngay_sua_goc === $hang['ngay_sua_goc'] ) {
		return 'giu_nguyen';
	}
	$wpdb->update( $bang, $hang + array( 'ngay_lay' => current_time( 'mysql' ) ), array( 'id' => $cu->id ) );
	return 'cap_nhat';
}

/** Số mục trong kho theo trạng thái (cho trang quản trị). */
function cadao_kho_dem( ?string $nguon_id = null ): array {
	global $wpdb;
	$bang = cadao_kho_bang();
	$sql  = "SELECT trang_thai, COUNT(*) AS so FROM $bang" . ( $nguon_id ? $wpdb->prepare( ' WHERE nguon_id = %s', $nguon_id ) : '' ) . ' GROUP BY trang_thai';
	$dem  = array( 'moi' => 0, 'da_tao_bai' => 0, 'bo_qua' => 0 );
	foreach ( $wpdb->get_results( $sql ) as $r ) {
		$dem[ $r->trang_thai ] = (int) $r->so;
	}
	return $dem;
}

/** Khối Thơ của trình soạn khối, giữ đúng từng dòng. */
function cadao_khoi_tho( string $loi ): string {
	$dong = array_map( 'esc_html', explode( "\n", $loi ) );
	return "<!-- wp:verse -->\n<pre class=\"wp-block-verse\">" . implode( '<br>', $dong ) . "</pre>\n<!-- /wp:verse -->";
}

function cadao_khoi_tieu_de( string $chu ): string {
	return "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">" . esc_html( $chu ) . "</h3>\n<!-- /wp:heading -->";
}

function cadao_khoi_doan( string $html, string $lop = '' ): string {
	$thuoc_tinh = $lop ? ' {"className":"' . esc_attr( $lop ) . '"}' : '';
	$lop_html   = $lop ? ' class="' . esc_attr( $lop ) . '"' : '';
	return "<!-- wp:paragraph$thuoc_tinh -->\n<p$lop_html>$html</p>\n<!-- /wp:paragraph -->";
}

/**
 * Nội dung bài từ một mục kho: lời (khối Thơ) → câu hỏi/đáp án (câu đố) → dị bản → chú thích.
 * Chú thích chỉ chép khi nguồn bật "Chép chú thích" (D-055), luôn kèm "Theo …" và liên kết về trang gốc.
 */
function cadao_noi_dung_tu_kho( object $muc, array $nguon ): string {
	$d     = json_decode( $muc->du_lieu, true ) ?: array();
	$khoi  = array( cadao_khoi_tho( $muc->loi ) );
	if ( ! empty( $d['cau_hoi'] ) ) {
		$khoi[] = cadao_khoi_doan( '<strong>' . esc_html( $d['cau_hoi'] ) . '</strong>' );
	}
	if ( ! empty( $d['dap_an'] ) ) {
		$khoi[] = "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>Đáp án</summary>"
			. cadao_khoi_doan( esc_html( $d['dap_an'] ) ) . "</details>\n<!-- /wp:details -->";
	}
	if ( ! empty( $d['di_ban'] ) ) {
		$khoi[] = cadao_khoi_tieu_de( 'Dị bản' );
		foreach ( $d['di_ban'] as $loi ) {
			$khoi[] = cadao_khoi_tho( (string) $loi );
		}
	}
	if ( ! empty( $nguon['chep_chu_thich'] ) && ! empty( $d['chu_thich'] ) ) {
		$theo   = sprintf( 'Theo <a href="%s" rel="noopener">%s</a>', esc_url( $muc->url ), esc_html( $nguon['ten'] ) );
		$muc_li = '';
		foreach ( $d['chu_thich'] as $c ) {
			$tu      = trim( (string) ( $c['tu'] ?? '' ) );
			$muc_li .= "<!-- wp:list-item -->\n<li>" . ( '' !== $tu ? '<strong>' . esc_html( $tu ) . '</strong>: ' : '' )
				. esc_html( (string) $c['noi_dung'] ) . "</li>\n<!-- /wp:list-item -->\n";
		}
		$khoi[] = cadao_khoi_tieu_de( 'Chú thích' );
		$khoi[] = "<!-- wp:list {\"className\":\"cadao-chu-thich\"} -->\n<ul class=\"wp-block-list cadao-chu-thich\">\n$muc_li</ul>\n<!-- /wp:list -->";
		$khoi[] = cadao_khoi_doan( $theo, 'cadao-chu-thich-nguon' );
	}
	return implode( "\n\n", $khoi );
}

/**
 * Tạo bài Chờ duyệt từ một mục kho. Bài đã có (cùng nguồn + mã gốc) thì không tạo lại.
 *
 * @return int|WP_Error id bài
 */
function cadao_kho_tao_bai( int $kho_id ) {
	global $wpdb;
	$bang = cadao_kho_bang();
	$muc  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $bang WHERE id = %d", $kho_id ) );
	if ( ! $muc ) {
		return new WP_Error( 'cadao_khong_co', 'Không có mục kho này.' );
	}
	$nguon = cadao_nguon_lay( $muc->nguon_id );
	if ( ! $nguon ) {
		return new WP_Error( 'cadao_khong_nguon', 'Nguồn tự động của mục này đã bị xóa.' );
	}
	$da_co = get_posts(
		array(
			'post_type'   => CADAO_LOAI_BAI,
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_query'  => array(
				array( 'key' => '_cadao_nguon_id', 'value' => $muc->nguon_id ),
				array( 'key' => '_cadao_ma_goc', 'value' => $muc->ma_goc ),
			),
		)
	);
	if ( $da_co ) {
		$wpdb->update( $bang, array( 'trang_thai' => 'da_tao_bai', 'post_id' => $da_co[0] ), array( 'id' => $muc->id ) );
		return (int) $da_co[0];
	}

	$id = wp_insert_post(
		array(
			'post_type'    => CADAO_LOAI_BAI,
			'post_status'  => 'pending', // Chờ duyệt (D-054)
			'post_title'   => $muc->tieu_de ?: strtok( $muc->loi, "\n" ),
			'post_content' => cadao_noi_dung_tu_kho( $muc, $nguon ),
			'post_author'  => (int) ( $nguon['nguoi_duyet'] ?? 0 ) ?: cadao_quan_tri_dau_tien(),
			'meta_input'   => array(
				'nguon_ten'       => $nguon['dong_ghi_nguon'] ?: $nguon['ten'],
				'nguon_url'       => $muc->url,
				'ngay_truy_cap'   => substr( $muc->ngay_lay, 0, 10 ),
				'_cadao_nguon_id' => $muc->nguon_id,
				'_cadao_ma_goc'   => $muc->ma_goc,
			),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return $id;
	}
	if ( array_key_exists( $muc->loai, CADAO_THE_LOAI_TU_LOAI_GOC ) ) {
		wp_set_object_terms( $id, CADAO_THE_LOAI_TU_LOAI_GOC[ $muc->loai ], 'the_loai' );
	}
	$wpdb->update( $bang, array( 'trang_thai' => 'da_tao_bai', 'post_id' => $id ), array( 'id' => $muc->id ) );
	return $id;
}

/** Loại trên nguồn → thể loại của dự án (thành ngữ–tục ngữ gộp ở nguồn: để Tục ngữ, người duyệt đổi nếu cần). */
const CADAO_THE_LOAI_TU_LOAI_GOC = array(
	'ca_dao'            => 'ca-dao',
	'thanh_ngu_tuc_ngu' => 'tuc-ngu',
	'dong_dao'          => 'dong-dao',
	've'                => 've',
	'cau_do'            => 'cau-do',
);

function cadao_quan_tri_dau_tien(): int {
	$ds = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID', 'orderby' => 'ID' ) );
	return $ds ? (int) $ds[0] : 0;
}

/**
 * Tạo tối đa $so bài Chờ duyệt từ kho của một nguồn: mục mới, bỏ qua mục mang cờ nguồn đã chọn bỏ qua.
 *
 * @return int số bài đã tạo
 */
function cadao_kho_tao_bai_cho_duyet( array $nguon, int $so ): int {
	global $wpdb;
	$bang  = cadao_kho_bang();
	$tao   = 0;
	$bo_co = $nguon['bo_qua_co'] ?? array();
	$loai  = $nguon['loai_lay'] ?? array();
	$sau   = 0; // lật trang theo id: mục vừa tạo bài rời khỏi danh sách "mới" nên OFFSET sẽ bỏ sót
	while ( $tao < $so ) {
		$ds = $wpdb->get_results( $wpdb->prepare( "SELECT id, loai, du_lieu FROM $bang WHERE nguon_id = %s AND trang_thai = 'moi' AND id > %d ORDER BY id ASC LIMIT 50", $nguon['id'], $sau ) );
		if ( ! $ds ) {
			break;
		}
		foreach ( $ds as $muc ) {
			$sau = (int) $muc->id;
			$co = ( json_decode( $muc->du_lieu, true ) ?: array() )['co'] ?? array();
			if ( ( $loai && ! in_array( $muc->loai, $loai, true ) ) || array_intersect( $co, $bo_co ) ) {
				continue; // để nguyên trong kho, người quản trị tự chọn nếu muốn
			}
			if ( ! is_wp_error( cadao_kho_tao_bai( (int) $muc->id ) ) ) {
				++$tao;
			}
			if ( $tao >= $so ) {
				break;
			}
		}
	}
	return $tao;
}
