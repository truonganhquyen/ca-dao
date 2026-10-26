<?php
/**
 * Mô-típ (T-092, docs/25 ý 4): công thức mở đầu nhiều bài dùng chung ("Thân em như…", "Chiều chiều…", "Nghe vẻ nghe
 * ve…"). Máy chỉ GỢI Ý: gom các bài có dòng đầu bắt đầu bằng cùng một cụm chữ (đúng chữ có trong lời, không đoán nghĩa);
 * người sáng lập đặt tên (máy điền sẵn chính cụm chữ đó), bỏ bài không hợp rồi bấm tạo. Lời giới thiệu mô-típ do người
 * sáng lập viết (ô Mô tả của phân loại), máy không viết. Trang mô-típ: /mo-tip/<tên>/.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRANG_MO_TIP  = 'cadao-mo-tip';
const CADAO_MO_TIP_IT_NHAT = 3; // một cụm mở đầu cần ít nhất ngần này bài mới thành gợi ý
const CADAO_MO_TIP_AM      = array( 2, 3, 4 ); // độ dài cụm mở đầu (âm tiết) được xét

add_action(
	'init',
	static function () {
		register_taxonomy(
			'mo_tip',
			CADAO_LOAI_BAI,
			array(
				'labels'            => array( 'name' => 'Mô-típ', 'singular_name' => 'Mô-típ', 'add_new_item' => 'Thêm mô-típ', 'edit_item' => 'Sửa mô-típ', 'search_items' => 'Tìm mô-típ' ),
				'hierarchical'      => false,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'mo-tip' ),
			)
		);
	},
	9 // trước cadao_dang_ky_loai_bai: lần làm mới quy tắc đường dẫn có cả /mo-tip/
);

/** Cụm mở đầu của một lời: k âm tiết đầu của dòng đầu (chữ thường, bỏ dấu câu); rỗng nếu dòng không dài hơn k. */
function cadao_mo_tip_dau( string $loi, int $k ): string {
	$dong = '';
	foreach ( explode( "\n", $loi ) as $d ) {
		if ( '' !== trim( $d ) ) {
			$dong = $d;
			break;
		}
	}
	$am = array_values( array_filter( explode( ' ', cadao_chuan_loi( $dong ) ), static fn( $a ) => '' !== $a ) );
	return count( $am ) > $k ? implode( ' ', array_slice( $am, 0, $k ) ) : '';
}

/** Tên gợi ý cho một cụm: đúng các chữ đó, viết hoa chữ đầu, thêm "…". */
function cadao_mo_tip_ten_goi_y( string $dau ): string {
	return mb_strtoupper( mb_substr( $dau, 0, 1 ) ) . mb_substr( $dau, 1 ) . '…';
}

/**
 * Bài (đã đăng hoặc Chờ duyệt) theo cụm mở đầu, cho mọi độ dài trong CADAO_MO_TIP_AM. Lưu đệm 12 giờ; xoá khi lưu bài.
 *
 * @return array<string, int[]> cụm → mã bài
 */
function cadao_mo_tip_cum(): array {
	$cum = get_transient( 'cadao_mo_tip_cum' );
	if ( is_array( $cum ) ) {
		return $cum;
	}
	$cum = array();
	$ids = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => array( 'publish', 'pending' ), 'numberposts' => -1, 'fields' => 'ids', 'no_found_rows' => true ) );
	foreach ( $ids as $id ) {
		$loi = cadao_loi_cac_khoi_tho( (string) get_post_field( 'post_content', $id ) )[0] ?? '';
		foreach ( CADAO_MO_TIP_AM as $k ) {
			$dau = cadao_mo_tip_dau( $loi, $k );
			if ( '' !== $dau ) {
				$cum[ $dau ][] = (int) $id;
			}
		}
	}
	set_transient( 'cadao_mo_tip_cum', $cum, 12 * HOUR_IN_SECONDS );
	return $cum;
}
add_action( 'save_post_' . CADAO_LOAI_BAI, static fn() => delete_transient( 'cadao_mo_tip_cum' ) );
add_action( 'set_object_terms', static fn() => delete_transient( 'cadao_mo_tip_cum' ) );

/** Mô-típ đã tạo từ một cụm mở đầu (ô _cadao_mo_tip_dau), nếu có. */
function cadao_mo_tip_cua_dau( string $dau ): ?WP_Term {
	$ds = get_terms( array( 'taxonomy' => 'mo_tip', 'hide_empty' => false, 'number' => 1, 'meta_key' => '_cadao_mo_tip_dau', 'meta_value' => $dau ) );
	return ( $ds && ! is_wp_error( $ds ) ) ? $ds[0] : null;
}

/** Bài người sáng lập đã bỏ ra khỏi một cụm (không gợi ý lại). */
function cadao_mo_tip_da_bo( string $dau ): array {
	return array_map( 'intval', (array) ( get_option( 'cadao_mo_tip_bo', array() )[ $dau ] ?? array() ) );
}

/**
 * Gợi ý mô-típ: cụm mở đầu có từ 3 bài chưa xếp vào mô-típ của cụm đó; cụm dài hơn thay cụm ngắn khi giữ được ≥ 80% số
 * bài ("thân em như" thay "thân em"). Cụm đã bỏ qua không gợi ý lại. Nhiều bài trước.
 *
 * @return array<int, array{dau: string, ten: string, bai: int[], mo_tip: ?WP_Term}>
 */
function cadao_mo_tip_goi_y(): array {
	$cum     = cadao_mo_tip_cum();
	$bo_qua  = (array) get_option( 'cadao_mo_tip_bo_qua', array() );
	$ra      = array();
	$con_cum = array(); // cụm → các cụm dài hơn nó đúng một âm tiết
	foreach ( array_keys( $cum ) as $d ) {
		$vt = strrpos( (string) $d, ' ' );
		if ( false !== $vt && substr_count( (string) $d, ' ' ) >= 2 ) {
			$con_cum[ substr( (string) $d, 0, $vt ) ][] = (string) $d;
		}
	}
	foreach ( $cum as $dau => $ids ) {
		$dau = (string) $dau;
		$ids = array_values( array_unique( $ids ) );
		if ( count( $ids ) < CADAO_MO_TIP_IT_NHAT || in_array( $dau, $bo_qua, true ) ) {
			continue;
		}
		// Cụm dài hơn một âm tiết giữ gần đủ số bài thì cụm này nhường cho nó
		foreach ( $con_cum[ $dau ] ?? array() as $d2 ) {
			if ( count( array_unique( $cum[ $d2 ] ) ) >= 0.8 * count( $ids ) && ! in_array( $d2, $bo_qua, true ) ) {
				continue 2;
			}
		}
		$mt  = cadao_mo_tip_cua_dau( $dau );
		$con = array_values( array_diff( $ids, cadao_mo_tip_da_bo( $dau ), $mt ? get_objects_in_term( (int) $mt->term_id, 'mo_tip' ) : array() ) );
		// Mô-típ chưa có: cần đủ 3 bài; đã có: gợi ý thêm cả một bài mới
		if ( $con && ( $mt || count( $con ) >= CADAO_MO_TIP_IT_NHAT ) ) {
			$ra[] = array( 'dau' => $dau, 'ten' => $mt ? $mt->name : cadao_mo_tip_ten_goi_y( $dau ), 'bai' => $con, 'mo_tip' => $mt );
		}
	}
	usort( $ra, static fn( $a, $b ) => count( $b['bai'] ) <=> count( $a['bai'] ) ?: strcmp( $a['dau'], $b['dau'] ) );
	return $ra;
}

/**
 * Người sáng lập quyết một gợi ý: $chon là bài xếp vào mô-típ, các bài còn lại của cụm ghi là "bỏ ra". Mô-típ chưa có
 * thì tạo với tên $ten (người sáng lập đã sửa hoặc giữ tên gợi ý).
 *
 * @param int[] $chon
 * @return WP_Term|WP_Error
 */
function cadao_mo_tip_tao( string $dau, string $ten, array $chon ) {
	$mt = cadao_mo_tip_cua_dau( $dau );
	if ( ! $mt ) {
		$ten = trim( $ten );
		if ( '' === $ten ) {
			return new WP_Error( 'cadao_ten', 'Xin đặt tên cho mô-típ.' );
		}
		$co  = get_term_by( 'name', $ten, 'mo_tip' );
		$moi = $co ? array( 'term_id' => $co->term_id ) : wp_insert_term( $ten, 'mo_tip' );
		if ( is_wp_error( $moi ) ) {
			return $moi;
		}
		update_term_meta( (int) $moi['term_id'], '_cadao_mo_tip_dau', $dau );
		$mt = get_term( (int) $moi['term_id'], 'mo_tip' );
	}
	$cua_cum = array_values( array_unique( cadao_mo_tip_cum()[ $dau ] ?? array() ) );
	$chon    = array_values( array_intersect( array_map( 'intval', $chon ), $cua_cum ) );
	foreach ( $chon as $id ) {
		wp_set_object_terms( $id, (int) $mt->term_id, 'mo_tip', true );
	}
	$bo          = get_option( 'cadao_mo_tip_bo', array() );
	$bo[ $dau ]  = array_values( array_unique( array_merge( cadao_mo_tip_da_bo( $dau ), array_diff( $cua_cum, $chon, get_objects_in_term( (int) $mt->term_id, 'mo_tip' ) ) ) ) );
	update_option( 'cadao_mo_tip_bo', $bo, false );
	delete_transient( 'cadao_mo_tip_cum' );
	return $mt;
}

// ---------- Trang "Gợi ý mô-típ" ----------

add_action(
	'admin_menu',
	static function () {
		add_submenu_page( 'edit.php?post_type=' . CADAO_LOAI_BAI, 'Gợi ý mô-típ', 'Gợi ý mô-típ', 'manage_options', CADAO_TRANG_MO_TIP, 'cadao_trang_mo_tip' );
	},
	13
);

function cadao_trang_mo_tip(): void {
	$ds = cadao_mo_tip_goi_y();
	echo '<div class="wrap"><h1>Gợi ý mô-típ</h1>';
	cadao_hien_bao();
	echo '<p>Máy gom các bài có <strong>dòng đầu bắt đầu bằng cùng một cụm chữ</strong> (từ ' . (int) CADAO_MO_TIP_IT_NHAT . ' bài). Máy không đoán nghĩa: bạn đặt tên (sửa tên gợi ý nếu muốn, ví dụ "Công cha… nghĩa mẹ…"), bỏ dấu bài không hợp rồi bấm <strong>Tạo mô-típ</strong>. '
		. 'Mô-típ có trang riêng và đưa các bài cùng mô-típ lên đầu khối "Đọc tiếp" cuối bài. Lời giới thiệu mô-típ: viết ở <a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=mo_tip&post_type=' . CADAO_LOAI_BAI ) ) . '">Mô-típ</a> → Sửa → Mô tả.</p>';
	if ( ! $ds ) {
		echo '<p>Không có gợi ý mới.</p></div>';
		return;
	}
	foreach ( $ds as $i => $g ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="card" style="max-width:none;margin:12px 0;padding:12px 16px">';
		wp_nonce_field( 'cadao_mo_tip' );
		echo '<input type="hidden" name="action" value="cadao_mo_tip"><input type="hidden" name="dau" value="' . esc_attr( $g['dau'] ) . '">';
		if ( $g['mo_tip'] ) {
			echo '<p style="margin-top:0"><strong>Bài mới hợp mô-típ "' . esc_html( $g['ten'] ) . '"</strong> · mở đầu "' . esc_html( $g['dau'] ) . '"</p>';
		} else {
			echo '<p style="margin-top:0"><label for="cadao-mt-' . (int) $i . '"><strong>' . count( $g['bai'] ) . ' bài mở đầu "' . esc_html( $g['dau'] ) . '"</strong> — tên mô-típ: </label><input id="cadao-mt-' . (int) $i . '" name="ten" class="regular-text" value="' . esc_attr( $g['ten'] ) . '"></p>';
		}
		echo '<ul style="columns:2 20rem">';
		foreach ( $g['bai'] as $id ) {
			echo '<li><label><input type="checkbox" name="chon[]" value="' . (int) $id . '" checked> ' . esc_html( get_the_title( $id ) ) . '</label> <span class="description">' . esc_html( get_post_status_object( (string) get_post_status( $id ) )->label ?? '' ) . '</span></li>';
		}
		echo '</ul><p style="display:flex;gap:8px;flex-wrap:wrap"><button class="button button-primary" name="viec" value="tao">' . ( $g['mo_tip'] ? 'Thêm vào mô-típ' : 'Tạo mô-típ' ) . '</button>';
		echo $g['mo_tip'] ? '' : '<button class="button" name="viec" value="bo_qua">Không phải mô-típ</button>';
		echo '</p></form>';
	}
	echo '</div>';
}

add_action(
	'admin_post_cadao_mo_tip',
	static function () {
		cadao_kiem_quyen( 'cadao_mo_tip' );
		$dau = sanitize_text_field( wp_unslash( $_POST['dau'] ?? '' ) );
		if ( 'bo_qua' === sanitize_key( wp_unslash( $_POST['viec'] ?? '' ) ) ) {
			$bq   = (array) get_option( 'cadao_mo_tip_bo_qua', array() );
			$bq[] = $dau;
			update_option( 'cadao_mo_tip_bo_qua', array_values( array_unique( $bq ) ), false );
			cadao_ve( CADAO_TRANG_MO_TIP, 'Đã bỏ qua cụm "' . $dau . '".' );
		}
		$kq = cadao_mo_tip_tao( $dau, sanitize_text_field( wp_unslash( $_POST['ten'] ?? '' ) ), array_map( 'intval', (array) wp_unslash( $_POST['chon'] ?? array() ) ) );
		cadao_ve( CADAO_TRANG_MO_TIP, is_wp_error( $kq ) ? $kq->get_error_message() : 'Đã lưu mô-típ "' . $kq->name . '".' );
	}
);
