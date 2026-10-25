<?php
/**
 * Trang quản trị: "Nguồn tự động", "Kho thu thập", ô "Chờ duyệt" trên Bảng tin.
 * Chỉ quản trị viên (manage_options) cấu hình nguồn; mọi thao tác có nonce.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRANG_NGUON = 'cadao-nguon-tu-dong';
const CADAO_TRANG_KHO   = 'cadao-kho';

add_action(
	'admin_menu',
	static function () {
		$cha = 'edit.php?post_type=' . CADAO_LOAI_BAI;
		add_submenu_page( $cha, 'Nguồn tự động', 'Nguồn tự động', 'manage_options', CADAO_TRANG_NGUON, 'cadao_trang_nguon' );
		add_submenu_page( $cha, 'Kho thu thập', 'Kho thu thập', 'manage_options', CADAO_TRANG_KHO, 'cadao_trang_kho' );
	}
);

function cadao_url_trang( string $trang, array $them = array() ): string {
	return add_query_arg( array( 'post_type' => CADAO_LOAI_BAI, 'page' => $trang ) + $them, admin_url( 'edit.php' ) );
}

function cadao_kiem_quyen( string $viec ): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Bạn không có quyền làm việc này.' );
	}
	check_admin_referer( $viec );
}

function cadao_ve( string $trang, string $bao, array $them = array() ): void {
	wp_safe_redirect( cadao_url_trang( $trang, array( 'cadao_bao' => rawurlencode( $bao ) ) + $them ) );
	exit;
}

function cadao_hien_bao(): void {
	if ( ! empty( $_GET['cadao_bao'] ) ) {
		printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( rawurldecode( sanitize_text_field( wp_unslash( $_GET['cadao_bao'] ) ) ) ) );
	}
}

function cadao_mo_ta_kq( array $kq ): string {
	if ( isset( $kq['bai'] ) ) {
		return sprintf( 'tạo %d bài chờ duyệt', $kq['bai'] );
	}
	return sprintf( '%d yêu cầu · %d trang đã đọc · %d mục mới · %d cập nhật · %s', $kq['yeu_cau'] ?? 0, $kq['trang'] ?? 0, $kq['moi'] ?? 0, $kq['cap_nhat'] ?? 0, $kq['ly_do'] ?? '' );
}

// ---------- Trang "Nguồn tự động" ----------
function cadao_trang_nguon(): void {
	$sua = isset( $_GET['sua'] ) ? sanitize_key( wp_unslash( $_GET['sua'] ) ) : null;
	echo '<div class="wrap"><h1 class="wp-heading-inline">Nguồn tự động</h1>';
	echo ' <a class="page-title-action" href="' . esc_url( cadao_url_trang( CADAO_TRANG_NGUON, array( 'sua' => 'moi' ) ) ) . '">Thêm nguồn</a><hr class="wp-header-end">';
	cadao_hien_bao();
	if ( null !== $sua ) {
		cadao_form_nguon( 'moi' === $sua ? null : cadao_nguon_lay( $sua ) );
		echo '</div>';
		return;
	}
	echo '<p>Máy tự lấy bài từ các nguồn đang <strong>bật</strong> (mỗi 5 phút một lượt ngắn) vào <a href="' . esc_url( cadao_url_trang( CADAO_TRANG_KHO ) ) . '">Kho thu thập</a>, rồi mỗi ngày tạo một số bài <strong>Chờ duyệt</strong>. Bạn xem bài ở <a href="' . esc_url( admin_url( 'edit.php?post_status=pending&post_type=' . CADAO_LOAI_BAI ) ) . '">Chờ duyệt</a> và bấm <strong>Đăng</strong>.</p>';
	echo '<table class="widefat striped"><thead><tr><th>Nguồn</th><th>Bộ đọc</th><th>Trạng thái</th><th>Bài/ngày</th><th>Kho: mới · đã thành bài · bỏ qua</th><th>Lần gần nhất</th><th></th></tr></thead><tbody>';
	$bo = cadao_bo_doc();
	foreach ( cadao_nguon_tat_ca() as $id => $n ) {
		$n   = $n + cadao_nguon_mac_dinh();
		$dem = cadao_kho_dem( (string) $id );
		$gan = $n['nhat_ky'][0] ?? null;
		echo '<tr><td><strong>' . esc_html( $n['ten'] ) . '</strong><br><span class="description">' . esc_html( $n['url'] ) . '</span></td>';
		echo '<td>' . esc_html( $bo[ $n['kieu'] ]['ten'] ?? $n['kieu'] ) . '</td>';
		echo '<td>' . ( $n['bat'] ? '<span style="color:#0a7d2c">● Đang bật</span>' : '<span class="description">○ Tắt</span>' ) . '</td>';
		echo '<td>' . (int) $n['so_bai_moi_ngay'] . '</td>';
		printf( '<td>%d · %d · %d</td>', $dem['moi'], $dem['da_tao_bai'], $dem['bo_qua'] );
		echo '<td>' . ( $gan ? esc_html( $gan['luc'] . ' — ' . cadao_mo_ta_kq( $gan ) ) : '<span class="description">chưa chạy</span>' ) . '</td><td>';
		echo '<a class="button" href="' . esc_url( cadao_url_trang( CADAO_TRANG_NGUON, array( 'sua' => $id ) ) ) . '">Sửa</a> ';
		foreach ( array( 'cadao_lay_ngay' => 'Lấy ngay', 'cadao_tao_ngay' => 'Tạo bài chờ duyệt ngay' ) as $viec => $nhan ) {
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">';
			wp_nonce_field( $viec );
			echo '<input type="hidden" name="action" value="' . esc_attr( $viec ) . '"><input type="hidden" name="id" value="' . esc_attr( $id ) . '">';
			submit_button( $nhan, 'secondary', 'submit', false );
			echo '</form> ';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
	echo '<p class="description">"Lấy ngay" chạy một lượt khoảng 20 giây (lịch sự với máy chủ nguồn: 3 giây một yêu cầu). Lấy hết một nguồn lớn cần nhiều lượt — máy tự chạy tiếp mỗi 5 phút khi nguồn đang bật.</p></div>';
}

function cadao_form_nguon( ?array $n ): void {
	$moi = null === $n;
	$n   = ( $n ?? array() ) + cadao_nguon_mac_dinh();
	$bo  = cadao_bo_doc();
	$o   = static fn( $k ) => esc_attr( is_array( $n[ $k ] ) ? implode( ', ', $n[ $k ] ) : (string) $n[ $k ] );
	echo '<h2>' . ( $moi ? 'Thêm nguồn' : 'Sửa nguồn: ' . esc_html( $n['ten'] ) ) . '</h2>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'cadao_luu_nguon_tu_dong' );
	echo '<input type="hidden" name="action" value="cadao_luu_nguon_tu_dong"><input type="hidden" name="id_cu" value="' . $o( 'id' ) . '">';
	echo '<table class="form-table" role="presentation"><tbody>';
	$hang = static function ( $nhan, $o_html, $goi_y = '' ) {
		echo '<tr><th scope="row">' . esc_html( $nhan ) . '</th><td>' . $o_html . ( $goi_y ? '<p class="description">' . esc_html( $goi_y ) . '</p>' : '' ) . '</td></tr>';
	};
	$hang( 'Bật', '<label><input type="checkbox" name="bat" value="1" ' . checked( $n['bat'], true, false ) . '> Tự lấy bài theo lịch</label>' );
	$hang( 'Tên nguồn', '<input class="regular-text" name="ten" required value="' . $o( 'ten' ) . '">' );
	$chon = '';
	foreach ( $bo as $k => $b ) {
		$chon .= '<option value="' . esc_attr( $k ) . '" ' . selected( $n['kieu'], $k, false ) . '>' . esc_html( $b['ten'] ) . '</option>';
	}
	$hang( 'Bộ đọc', '<select name="kieu">' . $chon . '</select>', 'Mỗi trang web có cấu trúc riêng; site mới cần thêm bộ đọc (nhờ agent).' );
	$hang( 'Địa chỉ', '<input class="regular-text" type="url" name="url" value="' . $o( 'url' ) . '" placeholder="https://">' );
	$loai = '';
	foreach ( $bo[ $n['kieu'] ]['loai'] ?? array() as $k => $nhan ) {
		$loai .= '<label style="margin-right:1em"><input type="checkbox" name="loai_lay[]" value="' . esc_attr( $k ) . '" ' . checked( in_array( $k, $n['loai_lay'], true ), true, false ) . '> ' . esc_html( $nhan ) . '</label>';
	}
	$hang( 'Lấy loại', $loai );
	$hang( 'Số bài Chờ duyệt mỗi ngày', '<input class="small-text" type="number" min="0" max="500" name="so_bai_moi_ngay" value="' . (int) $n['so_bai_moi_ngay'] . '">', 'Mỗi ngày máy tạo chừng này bài Chờ duyệt từ kho. 0 = không tự tạo.' );
	$hang( 'Dòng ghi nguồn', '<input class="regular-text" name="dong_ghi_nguon" value="' . $o( 'dong_ghi_nguon' ) . '">', 'Tên hiện trong dòng "Nguồn: …" dưới chú thích đã chép. Để trống thì dùng tên nguồn.' );
	$hang( 'Chép chú thích', '<label><input type="checkbox" name="chep_chu_thich" value="1" ' . checked( $n['chep_chu_thich'], true, false ) . '> Chép chú thích và lời giải (đáp án câu đố) của nguồn</label>', 'Tắt nếu nguồn yêu cầu. Thư xin phép Ca dao Mẹ: T-034.' );
	$hang( 'Ghi nguồn cho chú thích', '<label><input type="checkbox" name="ghi_cong" value="1" ' . checked( $n['ghi_cong'] ?? true, true, false ) . '> Dưới chú thích, lời giải đã chép, hiện "Nguồn: … ↗" về trang gốc</label>', 'Lời ca dao, tục ngữ và dị bản không ghi nguồn.' );
	$co = '';
	foreach ( array( 'tho_tuc' => 'Thô tục', 'can_xem' => 'Trào phúng, đấu tranh, lịch sử (cần xem)', 'hien_dai' => 'Có năm 19xx/20xx (có thể hiện đại)' ) as $k => $nhan ) {
		$co .= '<label style="display:block"><input type="checkbox" name="bo_qua_co[]" value="' . esc_attr( $k ) . '" ' . checked( in_array( $k, $n['bo_qua_co'], true ), true, false ) . '> ' . esc_html( $nhan ) . '</label>';
	}
	$hang( 'Không tự tạo bài khi có cờ', $co, 'Mục có cờ vẫn nằm trong kho; bạn tự chọn tạo bài ở Kho thu thập nếu muốn.' );
	$hang( 'Từ thô tục', '<input class="large-text" name="tu_tho_tuc" value="' . $o( 'tu_tho_tuc' ) . '">', 'Cách nhau bằng dấu phẩy. Dùng để gắn cờ "Thô tục".' );
	echo '</tbody></table>';
	submit_button( $moi ? 'Thêm nguồn' : 'Lưu' );
	echo '</form>';
	if ( ! $moi ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'Xóa nguồn này? Kho và các bài đã tạo vẫn giữ.\')">';
		wp_nonce_field( 'cadao_xoa_nguon_tu_dong' );
		echo '<input type="hidden" name="action" value="cadao_xoa_nguon_tu_dong"><input type="hidden" name="id" value="' . $o( 'id' ) . '">';
		submit_button( 'Xóa nguồn', 'delete', 'submit', false );
		echo '</form>';
		if ( $n['nhat_ky'] ) {
			echo '<h2>Nhật ký</h2><ul>';
			foreach ( $n['nhat_ky'] as $d ) {
				echo '<li>' . esc_html( $d['luc'] . ' · ' . $d['viec'] . ' · ' . cadao_mo_ta_kq( $d ) ) . '</li>';
			}
			echo '</ul>';
		}
	}
}

add_action(
	'admin_post_cadao_luu_nguon_tu_dong',
	static function () {
		cadao_kiem_quyen( 'cadao_luu_nguon_tu_dong' );
		$p     = wp_unslash( $_POST );
		$id_cu = sanitize_key( $p['id_cu'] ?? '' );
		$ten   = sanitize_text_field( $p['ten'] ?? '' );
		$cu    = $id_cu ? cadao_nguon_lay( $id_cu ) : null;
		$id    = $id_cu ?: ( sanitize_title( $ten ) ?: 'nguon-' . wp_generate_password( 6, false ) );
		$tach  = static fn( $s ) => array_values( array_filter( array_map( 'trim', explode( ',', sanitize_text_field( (string) $s ) ) ), 'strlen' ) );
		$kieu  = sanitize_key( $p['kieu'] ?? 'cadao_me' );
		cadao_nguon_luu(
			array(
				'id'              => $id,
				'ten'             => $ten,
				'kieu'            => array_key_exists( $kieu, cadao_bo_doc() ) ? $kieu : 'cadao_me',
				'url'             => esc_url_raw( $p['url'] ?? '' ),
				'bat'             => ! empty( $p['bat'] ),
				'loai_lay'        => array_map( 'sanitize_key', (array) ( $p['loai_lay'] ?? array() ) ),
				'so_bai_moi_ngay' => max( 0, min( 500, (int) ( $p['so_bai_moi_ngay'] ?? 0 ) ) ),
				'dong_ghi_nguon'  => sanitize_text_field( $p['dong_ghi_nguon'] ?? '' ),
				'chep_chu_thich'  => ! empty( $p['chep_chu_thich'] ),
				'ghi_cong'        => ! empty( $p['ghi_cong'] ),
				'bo_qua_co'       => array_map( 'sanitize_key', (array) ( $p['bo_qua_co'] ?? array() ) ),
				'tu_tho_tuc'      => $tach( $p['tu_tho_tuc'] ?? '' ),
			) + ( $cu ?? cadao_nguon_mac_dinh() )
		);
		cadao_ve( CADAO_TRANG_NGUON, 'Đã lưu nguồn "' . $ten . '".' );
	}
);

add_action(
	'admin_post_cadao_xoa_nguon_tu_dong',
	static function () {
		cadao_kiem_quyen( 'cadao_xoa_nguon_tu_dong' );
		cadao_nguon_xoa( sanitize_key( wp_unslash( $_POST['id'] ?? '' ) ) );
		cadao_ve( CADAO_TRANG_NGUON, 'Đã xóa nguồn. Kho và các bài đã tạo vẫn giữ.' );
	}
);

add_action(
	'admin_post_cadao_lay_ngay',
	static function () {
		cadao_kiem_quyen( 'cadao_lay_ngay' );
		$kq = cadao_nguon_chay( sanitize_key( wp_unslash( $_POST['id'] ?? '' ) ) );
		cadao_ve( CADAO_TRANG_NGUON, 'Lấy ngay: ' . cadao_mo_ta_kq( $kq ) . '.' );
	}
);

add_action(
	'admin_post_cadao_tao_ngay',
	static function () {
		cadao_kiem_quyen( 'cadao_tao_ngay' );
		$n  = cadao_nguon_lay( sanitize_key( wp_unslash( $_POST['id'] ?? '' ) ) );
		$so = $n ? cadao_kho_tao_bai_cho_duyet( $n, max( 1, (int) $n['so_bai_moi_ngay'] ) ) : 0;
		if ( $n ) {
			cadao_nguon_ghi( $n, 'tạo bài chờ duyệt', array( 'bai' => $so ) );
			cadao_nguon_luu( $n );
		}
		cadao_ve( CADAO_TRANG_NGUON, sprintf( 'Đã tạo %d bài Chờ duyệt.', $so ) );
	}
);

// ---------- Trang "Kho thu thập" ----------
function cadao_trang_kho(): void {
	global $wpdb;
	$bang = cadao_kho_bang();
	$tt   = sanitize_key( wp_unslash( $_GET['tt'] ?? 'moi' ) );
	$tim  = sanitize_text_field( wp_unslash( $_GET['tim'] ?? '' ) );
	$dem  = cadao_kho_dem();
	$nhan = array( 'moi' => 'Mới', 'da_tao_bai' => 'Đã thành bài', 'bo_qua' => 'Bỏ qua' );
	echo '<div class="wrap"><h1>Kho thu thập</h1>';
	cadao_hien_bao();
	echo '<p>Mọi bài máy đã lấy về. Kho không hiện ra trang; chọn mục rồi bấm <strong>Tạo bài chờ duyệt</strong>, hoặc để máy tự tạo mỗi ngày.</p><ul class="subsubsub">';
	$i = 0;
	foreach ( $nhan as $k => $ten ) {
		echo ( $i++ ? ' | ' : '' ) . '<li><a ' . ( $tt === $k ? 'class="current" ' : '' ) . 'href="' . esc_url( cadao_url_trang( CADAO_TRANG_KHO, array( 'tt' => $k ) ) ) . '">' . esc_html( $ten ) . ' <span class="count">(' . (int) $dem[ $k ] . ')</span></a></li>';
	}
	echo '</ul><form method="get" style="clear:both;padding-top:8px"><input type="hidden" name="post_type" value="' . esc_attr( CADAO_LOAI_BAI ) . '"><input type="hidden" name="page" value="' . esc_attr( CADAO_TRANG_KHO ) . '"><input type="hidden" name="tt" value="' . esc_attr( $tt ) . '">';
	echo '<input type="search" name="tim" value="' . esc_attr( $tim ) . '" placeholder="Tìm trong lời, tiêu đề"> ';
	submit_button( 'Tìm', 'secondary', '', false );
	echo '</form>';

	$dk = $wpdb->prepare( 'trang_thai = %s', isset( $nhan[ $tt ] ) ? $tt : 'moi' );
	if ( '' !== $tim ) {
		$giong = '%' . $wpdb->esc_like( $tim ) . '%';
		$dk   .= $wpdb->prepare( ' AND (tieu_de LIKE %s OR loi LIKE %s)', $giong, $giong );
	}
	$ds = $wpdb->get_results( "SELECT id, nguon_id, url, loai, tieu_de, loi, du_lieu, post_id FROM $bang WHERE $dk ORDER BY id ASC LIMIT 100" );

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'cadao_kho_hang_loat' );
	echo '<input type="hidden" name="action" value="cadao_kho_hang_loat"><p>';
	submit_button( 'Tạo bài chờ duyệt từ mục đã chọn', 'primary', 'viec_tao', false );
	echo ' ';
	submit_button( 'Bỏ qua mục đã chọn', 'secondary', 'viec_bo', false );
	echo '</p><table class="widefat striped"><thead><tr><td class="check-column"><input type="checkbox" onclick="document.querySelectorAll(\'.cadao-chon\').forEach(c=>c.checked=this.checked)"></td><th>Lời</th><th>Loại</th><th>Cờ</th><th>Nguồn</th></tr></thead><tbody>';
	foreach ( $ds as $m ) {
		$d  = json_decode( $m->du_lieu, true ) ?: array();
		$co = implode( ', ', $d['co'] ?? array() );
		echo '<tr><th class="check-column"><input class="cadao-chon" type="checkbox" name="chon[]" value="' . (int) $m->id . '"></th>';
		echo '<td><strong>' . esc_html( $m->tieu_de ) . '</strong><br><span style="white-space:pre-line">' . esc_html( wp_trim_words( $m->loi, 40 ) ) . '</span>';
		if ( $m->post_id ) {
			echo '<br><a href="' . esc_url( get_edit_post_link( (int) $m->post_id ) ) . '">Mở bài</a>';
		}
		echo '</td><td>' . esc_html( $m->loai ) . '</td><td>' . esc_html( $co ?: '—' ) . '</td>';
		echo '<td><a href="' . esc_url( $m->url ) . '" target="_blank" rel="noopener">' . esc_html( $m->nguon_id ) . ' ↗</a></td></tr>';
	}
	if ( ! $ds ) {
		echo '<tr><td colspan="5">Không có mục nào.</td></tr>';
	}
	echo '</tbody></table></form></div>';
}

add_action(
	'admin_post_cadao_kho_hang_loat',
	static function () {
		global $wpdb;
		cadao_kiem_quyen( 'cadao_kho_hang_loat' );
		$chon = array_map( 'intval', (array) wp_unslash( $_POST['chon'] ?? array() ) );
		$so   = 0;
		foreach ( $chon as $id ) {
			if ( isset( $_POST['viec_bo'] ) ) {
				$so += (int) $wpdb->update( cadao_kho_bang(), array( 'trang_thai' => 'bo_qua' ), array( 'id' => $id ) );
			} elseif ( ! is_wp_error( cadao_kho_tao_bai( $id ) ) ) {
				++$so;
			}
		}
		cadao_ve( CADAO_TRANG_KHO, isset( $_POST['viec_bo'] ) ? "Đã bỏ qua $so mục." : "Đã tạo $so bài Chờ duyệt." );
	}
);

// ---------- Ô "Chờ duyệt" trên Bảng tin ----------
add_action(
	'wp_dashboard_setup',
	static function () {
		wp_add_dashboard_widget(
			'cadao_cho_duyet',
			'Ca dao – Tục ngữ: chờ duyệt',
			static function () {
				$so  = (int) ( wp_count_posts( CADAO_LOAI_BAI )->pending ?? 0 );
				$url = admin_url( 'edit.php?post_status=pending&post_type=' . CADAO_LOAI_BAI );
				echo '<p style="font-size:1.3em"><strong>' . $so . '</strong> bài đang chờ duyệt.</p>';
				echo '<p><a class="button button-primary" href="' . esc_url( $url ) . '">Duyệt ngay</a> <a class="button" href="' . esc_url( admin_url( 'post-new.php?post_type=' . CADAO_LOAI_BAI ) ) . '">Thêm bài</a></p>';
				foreach ( cadao_nguon_tat_ca() as $n ) {
					$gan = $n['nhat_ky'][0] ?? null;
					echo '<p class="description">' . esc_html( $n['ten'] ) . ( empty( $n['bat'] ) ? ' (tắt)' : '' ) . ': ' . esc_html( $gan ? $gan['luc'] . ' — ' . cadao_mo_ta_kq( $gan ) : 'chưa chạy' ) . '</p>';
				}
			}
		);
	}
);
