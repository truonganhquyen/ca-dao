<?php
/**
 * Lời vào khối Thơ (T-112). Mọi thứ của trang bài — hộp Trích dẫn, ☆ Lưu, Trình chiếu, Nghe, tìm theo lời, so trùng,
 * chia đoạn bài dài — đọc lời từ khối Thơ. Bài dán từ trang web khác hay rơi vào khối Đoạn văn (25/10/2026: 16 bài vè
 * nhập tay). Máy đề xuất chuyển: đoạn văn chứa lời → khối Thơ (mỗi dòng một dòng; đoạn nhiều dòng cách nhau một dòng
 * trống), dòng chú in nghiêng hoặc nằm trong ngoặc giữ là đoạn văn, tiêu đề giữ nguyên. Chỉ đổi loại khối, không đổi
 * chữ nào: chữ sau khi chuyển khác chữ trước thì không đề xuất. Người sáng lập bấm "Chuyển" từng bài (hoặc chọn nhiều);
 * WordPress lưu bản sửa đổi nên hoàn tác được. Trình soạn có nút làm việc đó ngay khi đang soạn.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRANG_KHOI_THO = 'cadao-loi-khoi-tho';

/** Chữ của một đoạn HTML để so trước / sau (bỏ thẻ, khoảng trắng). */
function cadao_chu_de_so( string $html ): string {
	return (string) preg_replace( '/[\s\x{00A0}]+/u', '', html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/** Loại của một dòng trong đoạn văn: 'rong' | 'chu' (dòng chú: in nghiêng trọn dòng, hoặc nằm trong ngoặc) | 'loi'. */
function cadao_loai_dong( string $html ): string {
	$chu = trim( html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ), " \t\n\r\0\x0B\u{00A0}" );
	if ( '' === $chu ) {
		return 'rong';
	}
	if ( preg_match( '~^\s*<(em|i)\b[^>]*>.*</\1>\s*$~is', $html ) || preg_match( '/^\(.*\)[.,;:]?$/us', $chu ) ) {
		return 'chu';
	}
	return 'loi';
}

/**
 * Đề xuất chuyển lời vào khối Thơ cho bài chưa có khối Thơ nào.
 *
 * @return array{noi_dung: string, so_tho: int, so_dong: int, dau: string[], ghi_chu: string[]}|null null: không có gì để chuyển
 */
function cadao_de_xuat_khoi_tho( string $noi_dung ): ?array {
	$khoi = parse_blocks( $noi_dung );
	if ( in_array( 'core/verse', array_column( $khoi, 'blockName' ), true ) ) {
		return null;
	}
	$ra      = array();
	$cho     = array(); // các dòng lời đang gom thành một khối Thơ
	$nhieu   = false;   // đoạn vừa gom có nhiều dòng (để biết có cách khổ không)
	$so_tho  = 0;
	$so_dong = 0;
	$dau     = array();
	$ghi_chu = array();
	$xa      = static function () use ( &$cho, &$ra, &$so_tho, &$so_dong, &$dau, &$nhieu ) {
		if ( $cho ) {
			$ra[] = "<!-- wp:verse -->\n<pre class=\"wp-block-verse\">" . implode( '<br>', $cho ) . "</pre>\n<!-- /wp:verse -->";
			++$so_tho;
			$so_dong += count( array_filter( $cho, static fn( $d ) => '' !== $d ) );
			if ( ! $dau ) {
				$dau = array_slice( array_map( static fn( $d ) => trim( html_entity_decode( wp_strip_all_tags( $d ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ), $cho ), 0, 4 );
			}
			$cho   = array();
			$nhieu = false;
		}
	};
	foreach ( $khoi as $k ) {
		if ( null === $k['blockName'] && '' === trim( $k['innerHTML'] ) ) {
			continue; // khoảng trắng giữa các khối
		}
		if ( 'core/paragraph' !== $k['blockName'] ) {
			$xa();
			$ra[] = serialize_block( $k );
			continue;
		}
		$trong = (string) preg_replace( '~^\s*<p\b[^>]*>|</p>\s*$~i', '', trim( $k['innerHTML'] ) );
		// Mỗi đoạn: các nhóm dòng liền nhau cùng loại (lời / chú)
		$nhom = array();
		foreach ( preg_split( '~<br\s*/?>~i', $trong ) as $d ) {
			$loai = cadao_loai_dong( $d );
			if ( 'rong' === $loai ) {
				continue;
			}
			$d = trim( $d );
			if ( $nhom && end( $nhom )[0] === $loai ) {
				$nhom[ count( $nhom ) - 1 ][1][] = $d;
			} else {
				$nhom[] = array( $loai, array( $d ) );
			}
		}
		foreach ( $nhom as [ $loai, $dong ] ) {
			if ( 'chu' === $loai ) {
				$xa();
				$ra[]      = "<!-- wp:paragraph -->\n<p>" . implode( '<br>', $dong ) . "</p>\n<!-- /wp:paragraph -->";
				$ghi_chu[] = trim( html_entity_decode( wp_strip_all_tags( implode( ' ', $dong ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
				continue;
			}
			if ( $cho && ( $nhieu || count( $dong ) > 1 ) ) {
				$cho[] = ''; // hai đoạn nhiều dòng: cách một dòng trống (khổ mới)
			}
			array_push( $cho, ...$dong );
			$nhieu = count( $dong ) > 1;
		}
	}
	$xa();
	if ( ! $so_tho ) {
		return null;
	}
	$moi = implode( "\n\n", $ra );
	if ( cadao_chu_de_so( $moi ) !== cadao_chu_de_so( $noi_dung ) ) {
		return null; // không bao giờ đổi chữ
	}
	return array( 'noi_dung' => $moi, 'so_tho' => $so_tho, 'so_dong' => $so_dong, 'dau' => $dau, 'ghi_chu' => $ghi_chu );
}

/** Chuyển một bài (đã đề xuất được); WordPress lưu bản sửa đổi. */
function cadao_dua_vao_khoi_tho( int $id ): bool {
	$bai = get_post( $id );
	$dx  = $bai && CADAO_LOAI_BAI === $bai->post_type ? cadao_de_xuat_khoi_tho( $bai->post_content ) : null;
	if ( ! $dx ) {
		return false;
	}
	return ! is_wp_error( wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( $dx['noi_dung'] ) ), true ) );
}

/**
 * Bài chưa có khối Thơ mà có thể chuyển (trừ thùng rác).
 *
 * @return array<int, array> id => đề xuất
 */
function cadao_bai_chua_khoi_tho(): array {
	global $wpdb;
	$ra = array();
	foreach ( $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ('publish','pending','draft','future','private') AND post_content NOT LIKE %s ORDER BY ID DESC", CADAO_LOAI_BAI, '%<!-- wp:verse%' ) ) as $id ) {
		$dx = cadao_de_xuat_khoi_tho( (string) get_post_field( 'post_content', (int) $id ) );
		if ( $dx ) {
			$ra[ (int) $id ] = $dx;
		}
	}
	return $ra;
}

// ---------- Trang "Lời chưa vào khối Thơ" ----------

add_action(
	'admin_menu',
	static function () {
		$so = count( cadao_bai_chua_khoi_tho() );
		if ( $so || ( isset( $_GET['page'] ) && CADAO_TRANG_KHOI_THO === $_GET['page'] ) ) {
			add_submenu_page( 'edit.php?post_type=' . CADAO_LOAI_BAI, 'Lời chưa vào khối Thơ', 'Lời chưa vào khối Thơ' . ( $so ? ' <span class="awaiting-mod">' . $so . '</span>' : '' ), 'edit_others_posts', CADAO_TRANG_KHOI_THO, 'cadao_trang_khoi_tho' );
		}
	},
	14
);

function cadao_trang_khoi_tho(): void {
	$ds = cadao_bai_chua_khoi_tho();
	echo '<div class="wrap"><h1>Lời chưa vào khối Thơ</h1>';
	if ( isset( $_GET['da'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( 'Đã chuyển %d bài.', (int) $_GET['da'] ) ) . '</p></div>';
	}
	echo '<p>Những bài dưới đây có lời nằm trong khối Đoạn văn (thường do dán từ trang web khác), nên trang bài thiếu nút Trích dẫn, Lưu, Trình chiếu, Nghe; tìm theo lời và so trùng cũng chưa thấy bài. Máy đề xuất đưa lời vào khối Thơ: <strong>không đổi chữ nào</strong>, dòng chú in nghiêng hoặc trong ngoặc giữ là đoạn văn, tiêu đề giữ nguyên. Xem đề xuất rồi bấm <strong>Chuyển</strong>; muốn hoàn tác thì mở bài → Bản sửa đổi.</p>';
	if ( ! $ds ) {
		echo '<p><strong>Không còn bài nào.</strong></p></div>';
		return;
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'cadao_khoi_tho' );
	echo '<input type="hidden" name="action" value="cadao_khoi_tho">';
	echo '<p><button type="submit" class="button button-primary" name="tat_ca" value="1">Chuyển các bài đã chọn</button></p>';
	foreach ( $ds as $id => $dx ) {
		$bai = get_post( $id );
		echo '<div style="background:#fff;border:1px solid #dcdcde;padding:12px 16px;margin:12px 0;max-width:46rem">'
			. '<p style="margin-top:0"><label><input type="checkbox" name="bai[]" value="' . (int) $id . '" checked> <strong>' . esc_html( get_the_title( $bai ) ) . '</strong></label>'
			. ' <span class="description">(' . esc_html( get_post_status_object( $bai->post_status )->label ?? $bai->post_status ) . ')</span>'
			. ' · <a href="' . esc_url( (string) get_edit_post_link( $id ) ) . '">Mở bài</a></p>'
			. '<p>' . esc_html( sprintf( 'Lời: %d dòng trong %d khối Thơ.', $dx['so_dong'], $dx['so_tho'] ) ) . ( $dx['so_tho'] > 1 ? ' <span class="description">Bài có nhiều phần (ví dụ dưới các tiêu đề); phần đầu là lời chính.</span>' : '' ) . '</p>'
			. '<blockquote style="margin:0 0 8px;padding-left:12px;border-left:3px solid #c3c4c7">' . implode( '<br>', array_map( 'esc_html', $dx['dau'] ) ) . ( $dx['so_dong'] > count( $dx['dau'] ) ? '<br>…' : '' ) . '</blockquote>'
			. ( $dx['ghi_chu'] ? '<p class="description">Giữ là đoạn văn: ' . esc_html( implode( ' · ', array_map( static fn( $c ) => mb_strimwidth( $c, 0, 120, '…' ), $dx['ghi_chu'] ) ) ) . '</p>' : '' )
			. '<p style="margin-bottom:0"><button type="submit" class="button" name="mot" value="' . (int) $id . '">Chuyển bài này</button></p></div>';
	}
	echo '</form></div>';
}

add_action(
	'admin_post_cadao_khoi_tho',
	static function () {
		check_admin_referer( 'cadao_khoi_tho' );
		$ids = ! empty( $_POST['mot'] ) ? array( (int) $_POST['mot'] ) : array_map( 'intval', (array) ( $_POST['bai'] ?? array() ) );
		$da  = 0;
		foreach ( $ids as $id ) {
			if ( current_user_can( 'edit_post', $id ) && cadao_dua_vao_khoi_tho( $id ) ) {
				++$da;
			}
		}
		wp_safe_redirect( add_query_arg( array( 'post_type' => CADAO_LOAI_BAI, 'page' => CADAO_TRANG_KHOI_THO, 'da' => $da ), admin_url( 'edit.php' ) ) );
		exit;
	}
);

// Trình soạn: đề xuất cho nội dung đang soạn (nút "Đưa lời vào khối Thơ" trong ô bên cạnh — assets/soan-trung.js)
add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'cadao/v1',
			'/khoi-tho',
			array(
				'methods'             => 'POST',
				'permission_callback' => static fn() => current_user_can( 'edit_posts' ),
				'args'                => array( 'noi_dung' => array( 'type' => 'string', 'required' => true ) ),
				'callback'            => static fn( WP_REST_Request $yc ) => cadao_de_xuat_khoi_tho( (string) $yc['noi_dung'] ) ?? array( 'noi_dung' => '' ),
			)
		);
	}
);
