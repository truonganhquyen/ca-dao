<?php
/**
 * Chống trùng trong trang quản trị (D-065): trang "Có thể trùng" (T-110) và ô "Có thể trùng" trong trình soạn (T-111).
 * Chỉ báo, không chặn đăng (D-054). Mọi quyết định do người sáng lập bấm; không có gì bị xóa (bài vào thùng rác).
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRANG_TRUNG = 'cadao-co-the-trung';

add_action(
	'admin_menu',
	static function () {
		$so = cadao_trung_dem_cho();
		add_submenu_page(
			'edit.php?post_type=' . CADAO_LOAI_BAI,
			'Có thể trùng',
			'Có thể trùng' . ( $so ? ' <span class="awaiting-mod">' . (int) $so . '</span>' : '' ),
			'manage_options',
			CADAO_TRANG_TRUNG,
			'cadao_trang_trung'
		);
	},
	11
);

/** Một bên của cặp: tiêu đề, lời, thể loại, trạng thái, liên kết. */
function cadao_the_muc( array $m ): string {
	$lien_ket = array();
	if ( $m['sua'] ) {
		$lien_ket[] = '<a href="' . esc_url( $m['sua'] ) . '">' . ( 'kho' === $m['loai'] ? 'Xem trong kho' : 'Mở bài' ) . '</a>';
	}
	if ( $m['xem'] ) {
		$lien_ket[] = '<a href="' . esc_url( $m['xem'] ) . '">Xem trên trang</a>';
	}
	if ( $m['nguon'] ) {
		$lien_ket[] = '<a href="' . esc_url( $m['nguon'] ) . '" target="_blank" rel="noopener">Trang gốc ↗</a>';
	}
	return '<strong>' . esc_html( $m['tieu_de'] ) . '</strong><div style="white-space:pre-line;margin:.25em 0">' . esc_html( wp_trim_words( $m['loi'], 60 ) ) . '</div>'
		. '<span class="description">' . esc_html( $m['the_loai'] . ' · ' . $m['trang_thai'] ) . '</span><br>' . implode( ' · ', $lien_ket );
}

function cadao_trang_trung(): void {
	global $wpdb;
	$bang = cadao_trung_bang();
	$tab  = sanitize_key( wp_unslash( $_GET['tab'] ?? 'cho' ) );
	$tabs = array( 'cho' => 'Chờ bạn quyết', 'de_sau' => 'Để sau', 'da_quyet' => 'Đã quyết' );
	$tab  = isset( $tabs[ $tab ] ) ? $tab : 'cho';
	echo '<div class="wrap"><h1>Có thể trùng</h1>';
	cadao_hien_bao();
	echo '<p>Máy so <strong>cả lời</strong> (không so tiêu đề hay dòng đầu) của câu mới lấy từ nguồn và của bài với các bài, các câu đã có. '
		. 'Lời giống hệt và cùng thể loại thì máy đã tự gắn vào bài có sẵn; lời trùng đúng một dị bản đã ghi thì máy đã tự gắn làm dị bản của bài đó — xem<a href="' . esc_url( cadao_url_trang( CADAO_TRANG_KHO, array( 'tt' => 'da_gop' ) ) ) . '">Kho thu thập → Đã gộp</a>, bấm "Tách ra" nếu máy gắn sai. '
		. 'Các cặp dưới đây cần bạn quyết. Câu đang chờ quyết thì máy chưa tạo thành bài. Không có gì bị xóa.</p>';
	$lam = get_option( 'cadao_trung_lam_dan', array( 'buoc' => 'xong' ) );
	if ( 'xong' !== ( $lam['buoc'] ?? 'xong' ) ) {
		echo '<div class="notice notice-info inline"><p>Máy đang so các bài và câu đã có trong kho (làm dần mỗi 5 phút) — danh sách sẽ dài thêm.</p></div>';
	}
	$dem = array(
		'cho'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bang WHERE trang_thai = 'cho'" ),
		'de_sau'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bang WHERE trang_thai = 'de_sau'" ),
		'da_quyet' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bang WHERE trang_thai IN ('da_gop','khac')" ),
	);
	echo '<ul class="subsubsub">';
	$i = 0;
	foreach ( $tabs as $k => $ten ) {
		echo ( $i++ ? ' | ' : '' ) . '<li><a ' . ( $tab === $k ? 'class="current" ' : '' ) . 'href="' . esc_url( cadao_url_trang( CADAO_TRANG_TRUNG, array( 'tab' => $k ) ) ) . '">' . esc_html( $ten ) . ' <span class="count">(' . (int) $dem[ $k ] . ')</span></a></li>';
	}
	echo '</ul><div style="clear:both"></div>';

	$dk = 'da_quyet' === $tab ? "trang_thai IN ('da_gop','khac') ORDER BY id DESC" : $wpdb->prepare( 'trang_thai = %s ORDER BY id ASC', $tab );
	$ds = $wpdb->get_results( "SELECT * FROM $bang WHERE $dk LIMIT 50" );
	if ( ! $ds ) {
		echo '<p>' . ( 'cho' === $tab ? 'Không có cặp nào chờ bạn quyết.' : 'Chưa có cặp nào.' ) . '</p></div>';
		return;
	}
	foreach ( $ds as $r ) {
		$a = cadao_mo_ta_muc( $r->a );
		$b = cadao_mo_ta_muc( $r->b );
		if ( ! $a['co'] || ! $b['co'] ) {
			cadao_trung_dat( $r->a, $r->b, 'bo' ); // một bên đã vào thùng rác hoặc đã bị xóa
			continue;
		}
		$nhan = CADAO_MUC_TRUNG[ $r->muc ] ?? $r->muc;
		echo '<div class="card" style="max-width:none;margin:12px 0;padding:12px 16px">';
		echo '<p style="margin-top:0"><strong>' . esc_html( $nhan ) . '</strong>' . ( $r->giong ? ' · giống ' . (int) $r->giong . '%' : '' ) . ( $r->ly_do ? ' · ' . esc_html( $r->ly_do ) : '' ) . '</p>';
		echo '<div style="display:flex;flex-wrap:wrap;gap:16px"><div style="flex:1 1 260px"><p class="description" style="margin:0">' . ( 'kho' === $a['loai'] ? 'Câu mới lấy từ nguồn' : 'Bài' ) . '</p>' . cadao_the_muc( $a ) . '</div>';
		echo '<div style="flex:1 1 260px"><p class="description" style="margin:0">Bài / câu đã có</p>' . cadao_the_muc( $b ) . '</div></div>';
		if ( 'da_quyet' === $tab ) {
			echo '<p class="description">' . ( 'da_gop' === $r->trang_thai ? 'Đã gộp.' : 'Bạn đã chọn: là bài khác.' ) . '</p></div>';
			continue;
		}
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:8px;display:flex;flex-wrap:wrap;gap:8px">';
		wp_nonce_field( 'cadao_trung_quyet' );
		echo '<input type="hidden" name="action" value="cadao_trung_quyet"><input type="hidden" name="id" value="' . (int) $r->id . '"><input type="hidden" name="tab" value="' . esc_attr( $tab ) . '">';
		if ( 'kho' === $a['loai'] ) {
			if ( in_array( $r->muc, array( 'y_het', 'gan_y_het' ), true ) ) {
				echo '<button class="button button-primary" name="viec" value="trung">Gộp: là cùng một bài</button>';
			}
			echo '<button class="button' . ( 'di_ban' === $r->muc ? ' button-primary' : '' ) . '" name="viec" value="di_ban">Gộp làm dị bản</button>';
		}
		echo '<button class="button" name="viec" value="khac">Là bài khác</button>';
		echo 'de_sau' === $tab ? '<button class="button" name="viec" value="cho">Đưa lại vào hàng chờ</button>' : '<button class="button" name="viec" value="de_sau">Để sau</button>';
		if ( 'bai' === $a['loai'] && 'bai' === $b['loai'] ) { // hai bài đã có (T-101): chọn bài chính
			echo '<button class="button button-primary" name="viec" value="gop_giu_b">' . esc_html( 'Gộp: giữ "' . wp_trim_words( $b['tieu_de'], 8 ) . '"' ) . '</button>';
			echo '<button class="button" name="viec" value="gop_giu_a">' . esc_html( 'Gộp: giữ "' . wp_trim_words( $a['tieu_de'], 8 ) . '"' ) . '</button>';
			echo '<span class="description" style="flex-basis:100%">Bài kia thành dị bản của bài được giữ; lượt đọc, cảm nghĩ, bài phân tích chuyển sang; đường dẫn cũ tự chuyển về; bài kia vào thùng rác.</span>';
		}
		echo '</form></div>';
	}
	echo '</div>';
}

add_action(
	'admin_post_cadao_trung_quyet',
	static function () {
		global $wpdb;
		cadao_kiem_quyen( 'cadao_trung_quyet' );
		$r    = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_trung_bang() . ' WHERE id = %d', (int) ( $_POST['id'] ?? 0 ) ) );
		$viec = sanitize_key( wp_unslash( $_POST['viec'] ?? '' ) );
		if ( ! $r ) {
			cadao_ve( CADAO_TRANG_TRUNG, 'Không thấy cặp này.' );
		}
		$bao = 'Đã ghi.';
		if ( in_array( $viec, array( 'gop_giu_a', 'gop_giu_b' ), true ) ) {
			$ia  = cadao_tach_muc( $r->a )[1];
			$ib  = cadao_tach_muc( $r->b )[1];
			$bao = ( 'gop_giu_b' === $viec ? cadao_gop_hai_bai( $ib, $ia ) : cadao_gop_hai_bai( $ia, $ib ) ) ? 'Đã gộp hai bài.' : 'Không gộp được hai bài này.';
		} elseif ( in_array( $viec, array( 'trung', 'di_ban' ), true ) ) {
			$bao = cadao_trung_gop( $r->a, $r->b, $viec ) ? ( 'di_ban' === $viec ? 'Đã gộp làm dị bản.' : 'Đã gộp vào bài có sẵn.' ) : 'Không gộp được cặp này.';
		} elseif ( 'khac' === $viec ) {
			cadao_trung_dat( $r->a, $r->b, 'khac' );
			if ( 'kho' === cadao_tach_muc( $r->a )[0] ) {
				cadao_trung_kiem_kho( cadao_tach_muc( $r->a )[1] ); // so tiếp với bài khác (nếu có)
			}
			$bao = 'Đã ghi: là bài khác.';
		} elseif ( in_array( $viec, array( 'de_sau', 'cho' ), true ) ) {
			cadao_trung_dat( $r->a, $r->b, $viec );
			$bao = 'de_sau' === $viec ? 'Đã để sau.' : 'Đã đưa lại vào hàng chờ.';
		}
		cadao_ve( CADAO_TRANG_TRUNG, $bao, array( 'tab' => sanitize_key( wp_unslash( $_POST['tab'] ?? 'cho' ) ) ) );
	}
);

// ---------- Trình soạn (T-111): ô "Có thể trùng" ----------

add_action(
	'enqueue_block_editor_assets',
	static function () {
		$man = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $man || CADAO_LOAI_BAI !== $man->post_type ) {
			return;
		}
		wp_enqueue_script( 'ca-dao-soan-trung', plugins_url( 'assets/soan-trung.js', CADAO_THU_MUC . '/ca-dao.php' ), array( 'wp-plugins', 'wp-element', 'wp-data', 'wp-api-fetch', 'wp-components', 'wp-editor', 'wp-edit-post', 'wp-blocks' ), CADAO_PHIEN_BAN, true );
	}
);

/** Kết quả so cho trình soạn. */
function cadao_trung_cho_soan( string $loi, int $bai ): array {
	$tru = $bai ? array_merge( array( 'bai:' . $bai ), cadao_trung_da_khac( 'bai:' . $bai ) ) : array();
	$ra  = array();
	foreach ( cadao_tim_trung( $loi, $tru, 5 ) as $t ) {
		$m = cadao_mo_ta_muc( $t['muc'] );
		if ( ! $m['co'] || ( 'bai' === $m['loai'] && $m['id'] === $bai ) ) {
			continue;
		}
		$ra[] = array(
			'muc'      => $t['muc'],
			'loai'     => $m['loai'],
			'id'       => $m['id'],
			'tieu_de'  => $m['tieu_de'],
			'the_loai' => $m['the_loai'],
			'trang'    => $m['trang_thai'],
			'nhan'     => CADAO_MUC_TRUNG[ $t['cap_do'] ] ?? $t['cap_do'],
			'cap_do'   => $t['cap_do'],
			'giong'    => $t['giong'],
			'ly_do'    => $t['ly_do'],
			'sua'      => $m['sua'],
		);
	}
	return $ra;
}

add_action(
	'rest_api_init',
	static function () {
		$quyen = static fn() => current_user_can( 'edit_posts' );
		register_rest_route(
			'cadao/v1',
			'/trung',
			array(
				'methods'             => 'POST',
				'permission_callback' => $quyen,
				'callback'            => static function ( WP_REST_Request $yc ) {
					$loi = cadao_loi_tu_html_tho( (string) $yc->get_param( 'loi_html' ) );
					return array( 'co_loi' => '' !== $loi, 'ket_qua' => '' === $loi ? array() : cadao_trung_cho_soan( $loi, (int) $yc->get_param( 'bai' ) ) );
				},
			)
		);
		register_rest_route(
			'cadao/v1',
			'/them-di-ban',
			array(
				'methods'             => 'POST',
				'permission_callback' => static function ( WP_REST_Request $yc ) {
					$bai = (int) $yc->get_param( 'bai' );
					return current_user_can( 'edit_post', (int) $yc->get_param( 'dich' ) ) && ( ! $bai || current_user_can( 'delete_post', $bai ) );
				},
				'callback'            => static function ( WP_REST_Request $yc ) {
					$dich = (int) $yc->get_param( 'dich' );
					$bai  = (int) $yc->get_param( 'bai' );
					$loi  = cadao_loi_tu_html_tho( (string) $yc->get_param( 'loi_html' ) );
					if ( CADAO_LOAI_BAI !== get_post_type( $dich ) || '' === $loi || $dich === $bai ) {
						return new WP_Error( 'cadao_khong_them', 'Không thêm được dị bản.', array( 'status' => 400 ) );
					}
					cadao_them_di_ban( $dich, $loi );
					if ( $bai && CADAO_LOAI_BAI === get_post_type( $bai ) ) {
						wp_trash_post( $bai ); // bản nháp vừa viết vào thùng rác, khôi phục được
					}
					return array( 'sua' => get_edit_post_link( $dich, 'url' ) );
				},
			)
		);
		register_rest_route(
			'cadao/v1',
			'/khong-trung',
			array(
				'methods'             => 'POST',
				'permission_callback' => static fn( WP_REST_Request $yc ) => current_user_can( 'edit_post', (int) $yc->get_param( 'bai' ) ),
				'callback'            => static function ( WP_REST_Request $yc ) {
					$bai = (int) $yc->get_param( 'bai' );
					foreach ( (array) $yc->get_param( 'cac' ) as $muc ) {
						if ( preg_match( '/^(bai|kho):\d+$/', (string) $muc ) ) {
							cadao_trung_dat( 'bai:' . $bai, (string) $muc, 'khac' );
						}
					}
					return array( 'ok' => true );
				},
			)
		);
	}
);
