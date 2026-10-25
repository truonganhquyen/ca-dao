<?php
/**
 * Kho thu thập: bảng riêng trong cơ sở dữ liệu, chứa mọi mục bộ đọc lấy về (lời nguyên văn, dị bản, chú thích,
 * địa chỉ trang). Kho không hiện ra trang; mỗi ngày một số mục được tạo thành bài **Chờ duyệt** (D-054).
 */

defined( 'ABSPATH' ) || exit;

const CADAO_KHO_PHIEN_BAN = 2; // 2: cột doc_trang (T-073)

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
		doc_trang varchar(16) NOT NULL DEFAULT '',
		post_id bigint(20) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (id),
		UNIQUE KEY nguon_ma (nguon_id, ma_goc),
		KEY trang_thai (trang_thai),
		KEY doc_trang (nguon_id, doc_trang),
		KEY url (url(191))
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

// Nâng cấp dữ liệu bài một lần (sau khi loại bài đã đăng ký)
add_action(
	'init',
	static function () {
		$phien = (int) get_option( 'cadao_du_lieu_phien_ban' );
		if ( $phien < 2 ) {
			cadao_bo_nguon_tu_dien();
		}
		if ( $phien < 3 ) {
			// T-073: nguồn Ca dao Mẹ lấy thêm thành ngữ–tục ngữ, đồng dao, vè, câu đố (người sáng lập yêu cầu 25/10/2026)
			foreach ( cadao_nguon_tat_ca() as $n ) {
				if ( 'cadao_me' === ( $n['kieu'] ?? '' ) ) {
					$n['loai_lay'] = array_values( array_unique( array_merge( (array) $n['loai_lay'], array_keys( cadao_bo_doc()['cadao_me']['loai'] ) ) ) );
					cadao_nguon_luu( $n );
				}
			}
			update_option( 'cadao_du_lieu_phien_ban', 3 );
		}
	},
	20
);

/**
 * D-057: bài tạo từ kho trước 0.2.0 có ô nguồn máy tự điền cho lời. Bỏ các ô đó (địa chỉ gốc chuyển sang ô nội bộ);
 * bài mà người duyệt đã sửa tên nguồn thì để nguyên.
 *
 * @return int số bài đã bỏ nguồn
 */
function cadao_bo_nguon_tu_dien(): int {
	$so = 0;
	$ds = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_cadao_nguon_id' ) );
	foreach ( $ds as $id ) {
		$nguon = cadao_nguon_lay( (string) get_post_meta( $id, '_cadao_nguon_id', true ) );
		$url   = (string) get_post_meta( $id, 'nguon_url', true );
		if ( '' === $url || ! $nguon || get_post_meta( $id, 'nguon_ten', true ) !== ( $nguon['dong_ghi_nguon'] ?: $nguon['ten'] ) ) {
			continue;
		}
		update_post_meta( $id, '_cadao_url_goc', $url );
		update_post_meta( $id, '_cadao_ngay_lay', get_post_meta( $id, 'ngay_truy_cap', true ) );
		foreach ( array( 'nguon_ten', 'nguon_url', 'ngay_truy_cap' ) as $k ) {
			delete_post_meta( $id, $k );
		}
		++$so;
	}
	return $so;
}

/**
 * Lưu (hoặc cập nhật) một mục vào kho. Chỉ các trường có trong $muc được ghi; dị bản, chú thích đã đọc từ trang
 * HTML giữ nguyên khi API lấy lại. Nguồn sửa bài (ngay_sua_goc đổi) thì đánh dấu đọc lại trang (doc_trang = '').
 * Bài đã tạo từ mục này được cập nhật theo kho nếu chưa ai sửa tay (cadao_kho_cap_nhat_bai).
 *
 * doc_trang: '' = chưa đọc trang HTML, 'YYYY-MM-DD' = đã đọc, 'loi' / 'khong_thay' = không đọc được.
 *
 * @param array{ma_goc: string|int, url: string, loai: string, tieu_de: string, loi: string, di_ban?: array, chu_thich?: array, chu_de_goc?: array, co?: array, ngay_sua_goc?: string} $muc
 * @return string 'moi' | 'cap_nhat' | 'giu_nguyen'
 */
function cadao_kho_luu( string $nguon_id, array $muc ): string {
	global $wpdb;
	$bang   = cadao_kho_bang();
	$ma_goc = (string) $muc['ma_goc'];
	$cu     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $bang WHERE nguon_id = %s AND ma_goc = %s", $nguon_id, $ma_goc ) );
	$d      = $cu ? ( json_decode( $cu->du_lieu, true ) ?: array() ) : array();
	foreach ( array( 'di_ban', 'chu_thich', 'chu_de_goc', 'co' ) as $k ) {
		$d[ $k ] = array_key_exists( $k, $muc ) ? array_values( (array) $muc[ $k ] ) : ( $d[ $k ] ?? array() );
	}
	foreach ( array( 'cau_hoi', 'dap_an' ) as $k ) {
		$d[ $k ] = array_key_exists( $k, $muc ) ? $muc[ $k ] : ( $d[ $k ] ?? null );
	}
	$hang = array(
		'url'          => (string) ( $muc['url'] ?? $cu->url ?? '' ),
		'loai'         => (string) ( $muc['loai'] ?? $cu->loai ?? '' ),
		'tieu_de'      => (string) ( $muc['tieu_de'] ?? $cu->tieu_de ?? '' ),
		'loi'          => (string) ( $muc['loi'] ?? $cu->loi ?? '' ),
		'du_lieu'      => wp_json_encode( $d, JSON_UNESCAPED_UNICODE ),
		'ngay_sua_goc' => (string) ( $muc['ngay_sua_goc'] ?? $cu->ngay_sua_goc ?? '' ),
	);
	if ( array_key_exists( 'doc_trang', $muc ) ) {
		$hang['doc_trang'] = (string) $muc['doc_trang'];
	} elseif ( $cu && $cu->ngay_sua_goc !== $hang['ngay_sua_goc'] ) {
		$hang['doc_trang'] = ''; // nguồn đã sửa bài: đọc lại trang để lấy dị bản, chú thích mới
	}
	if ( ! $cu ) {
		$wpdb->insert( $bang, $hang + array( 'nguon_id' => $nguon_id, 'ma_goc' => $ma_goc, 'ngay_lay' => current_time( 'mysql' ), 'trang_thai' => 'moi' ) );
		return 'moi';
	}
	$giong = true;
	foreach ( $hang as $k => $v ) {
		$giong = $giong && (string) $cu->$k === $v;
	}
	if ( $giong ) {
		return 'giu_nguyen';
	}
	$wpdb->update( $bang, $hang + array( 'ngay_lay' => current_time( 'mysql' ) ), array( 'id' => $cu->id ) );
	if ( $cu->post_id ) {
		cadao_kho_cap_nhat_bai( (int) $cu->id, $cu );
	}
	return 'cap_nhat';
}

/**
 * Bài tạo từ kho: cập nhật nội dung theo kho mới (thêm dị bản, chú thích…) nếu chưa ai sửa tay — tức nội dung bài
 * còn đúng như máy đã tạo. Bài đã sửa tay giữ nguyên.
 *
 * @param object $cu hàng kho trước khi cập nhật (để nhận ra bài tạo trước khi có dấu băm)
 */
function cadao_kho_cap_nhat_bai( int $kho_id, object $cu ): bool {
	global $wpdb;
	$muc   = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_kho_bang() . ' WHERE id = %d', $kho_id ) );
	$bai   = $muc && $muc->post_id ? get_post( (int) $muc->post_id ) : null;
	$nguon = $muc ? cadao_nguon_lay( $muc->nguon_id ) : null;
	if ( ! $bai || ! $nguon || 'trash' === $bai->post_status ) {
		return false;
	}
	$bam        = (string) get_post_meta( $bai->ID, '_cadao_bam', true );
	$con_nguyen = '' !== $bam ? md5( $bai->post_content ) === $bam : $bai->post_content === cadao_noi_dung_tu_kho( $cu, $nguon );
	if ( ! $con_nguyen ) {
		return false;
	}
	$moi = cadao_noi_dung_tu_kho( $muc, $nguon );
	if ( $moi !== $bai->post_content ) {
		wp_update_post( array( 'ID' => $bai->ID, 'post_content' => $moi ) );
	}
	update_post_meta( $bai->ID, '_cadao_bam', md5( get_post( $bai->ID )->post_content ) );
	return true;
}

/** Số mục trong kho theo trạng thái (cho trang quản trị); không tính mục chưa có lời (chờ đọc trang). */
function cadao_kho_dem( ?string $nguon_id = null ): array {
	global $wpdb;
	$bang = cadao_kho_bang();
	$sql  = "SELECT trang_thai, COUNT(*) AS so FROM $bang WHERE loi <> ''" . ( $nguon_id ? $wpdb->prepare( ' AND nguon_id = %s', $nguon_id ) : '' ) . ' GROUP BY trang_thai';
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
 * Chú thích và lời giải (đáp án câu đố) chỉ chép khi nguồn bật "Chép chú thích" (D-055); dưới phần chép là một dòng
 * "Nguồn: …" về trang gốc khi nguồn bật "Ghi nguồn cho chú thích" (D-057). Lời, dị bản không ghi nguồn.
 */
function cadao_noi_dung_tu_kho( object $muc, array $nguon ): string {
	$d     = json_decode( $muc->du_lieu, true ) ?: array();
	$khoi  = array( cadao_khoi_tho( $muc->loi ) );
	if ( ! empty( $d['cau_hoi'] ) ) {
		$khoi[] = cadao_khoi_doan( '<strong>' . esc_html( $d['cau_hoi'] ) . '</strong>' );
	}
	$chep = ! empty( $nguon['chep_chu_thich'] );
	if ( $chep && ! empty( $d['dap_an'] ) ) {
		$khoi[] = "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>Đáp án</summary>"
			. cadao_khoi_doan( esc_html( $d['dap_an'] ) ) . "</details>\n<!-- /wp:details -->";
	}
	if ( ! empty( $d['di_ban'] ) ) {
		$khoi[] = cadao_khoi_tieu_de( 'Dị bản' );
		foreach ( $d['di_ban'] as $loi ) {
			$khoi[] = cadao_khoi_tho( (string) $loi );
		}
	}
	if ( $chep && ! empty( $d['chu_thich'] ) ) {
		$muc_li = '';
		foreach ( $d['chu_thich'] as $c ) {
			$tu      = trim( (string) ( $c['tu'] ?? '' ) );
			$muc_li .= "<!-- wp:list-item -->\n<li>" . ( '' !== $tu ? '<strong>' . esc_html( $tu ) . '</strong>: ' : '' )
				. esc_html( (string) $c['noi_dung'] ) . "</li>\n<!-- /wp:list-item -->\n";
		}
		$khoi[] = cadao_khoi_tieu_de( 'Chú thích' );
		$khoi[] = "<!-- wp:list {\"className\":\"cadao-chu-thich\"} -->\n<ul class=\"wp-block-list cadao-chu-thich\">\n$muc_li</ul>\n<!-- /wp:list -->";
	}
	if ( $chep && ! empty( $nguon['ghi_cong'] ) && ( ! empty( $d['chu_thich'] ) || ! empty( $d['dap_an'] ) ) ) {
		$khoi[] = cadao_khoi_doan( sprintf( 'Nguồn: <a href="%s" rel="noopener">%s <span aria-hidden="true">↗</span></a>', esc_url( $muc->url ), esc_html( $nguon['dong_ghi_nguon'] ?: $nguon['ten'] ) ), 'cadao-chu-thich-nguon' );
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
	if ( '' === $muc->loi ) {
		return new WP_Error( 'cadao_chua_doc', 'Mục này chưa đọc trang nên chưa có lời; máy sẽ đọc ở lượt sau.' );
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
				// Lời không ghi nguồn (D-057); địa chỉ gốc giữ nội bộ để cập nhật bài từ nguồn sau này
				'_cadao_url_goc'  => $muc->url,
				'_cadao_ngay_lay' => substr( $muc->ngay_lay, 0, 10 ),
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
	update_post_meta( $id, '_cadao_bam', md5( get_post( $id )->post_content ) ); // nhận ra bài chưa ai sửa tay
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
 * Tạo tối đa $so bài Chờ duyệt từ kho của một nguồn: mục mới đã đọc trang, bỏ qua mục mang cờ nguồn đã chọn bỏ qua.
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
		// Chỉ mục đã đọc trang (có dị bản, chú thích) và có lời
		$ds = $wpdb->get_results( $wpdb->prepare( "SELECT id, loai, du_lieu FROM $bang WHERE nguon_id = %s AND trang_thai = 'moi' AND doc_trang LIKE '2%%' AND loi <> '' AND id > %d ORDER BY id ASC LIMIT 50", $nguon['id'], $sau ) );
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
