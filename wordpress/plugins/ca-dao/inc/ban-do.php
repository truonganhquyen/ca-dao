<?php
/**
 * Bản đồ ca dao (T-104, docs/25 mục 4.2, ý 6): trang /ban-do/ — bản đồ Việt Nam (ảnh SVG tự lưu, có Hoàng Sa,
 * Trường Sa) với điểm các địa danh người sáng lập đã xác nhận trên Wikidata; chạm điểm tới các bài nhắc nơi đó.
 * Không gọi dịch vụ bản đồ nào, không cần JavaScript (điểm là liên kết tới danh sách dưới bản đồ). Điểm gần nhau gộp
 * thành một để vùng chạm đủ lớn trên điện thoại.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Địa danh đã xác nhận, có tọa độ trong khung bản đồ và có bài đang đăng; từ Bắc vào Nam.
 *
 * @return array<int, array{muc: WP_Term, wd: array, lat: float, x: float, y: float, bai: WP_Post[]}>
 */
function cadao_ban_do_diem(): array {
	global $wpdb;
	[ $tay, $dong, $bac, $nam ] = CADAO_BAN_DO_KHUNG;
	$ds = array();
	foreach ( $wpdb->get_results( "SELECT term_id, meta_value FROM {$wpdb->termmeta} WHERE meta_key = '_cadao_wd'" ) as $r ) {
		$wd = maybe_unserialize( $r->meta_value );
		if ( ! is_array( $wd ) || empty( $wd['toa_do'] ) ) {
			continue;
		}
		[ $lat, $lon ] = array_map( 'floatval', $wd['toa_do'] );
		$muc           = get_term( (int) $r->term_id, 'tu_kho' );
		if ( $lon < $tay || $lon > $dong || $lat > $bac || $lat < $nam || ! $muc || is_wp_error( $muc ) ) {
			continue;
		}
		$bai = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'numberposts' => 30, 'tax_query' => array( array( 'taxonomy' => 'tu_kho', 'terms' => $muc->term_id ) ) ) );
		if ( $bai ) {
			$ds[] = array( 'muc' => $muc, 'wd' => $wd, 'lat' => $lat, 'x' => round( ( $lon - $tay ) / ( $dong - $tay ) * 100, 2 ), 'y' => round( ( $bac - $lat ) / ( $bac - $nam ) * 100, 2 ), 'bai' => $bai );
		}
	}
	usort( $ds, static fn( $a, $b ) => $b['lat'] <=> $a['lat'] );
	return $ds;
}

/** Gộp các điểm cách nhau dưới $gan phần trăm chiều rộng bản đồ (24 px trên màn hình 360 px ≈ 7%). */
function cadao_ban_do_gom( array $diem, float $gan = 7.0 ): array {
	$nhom = array();
	foreach ( $diem as $d ) {
		foreach ( array_keys( $nhom ) as $i ) {
			if ( abs( $nhom[ $i ]['x'] - $d['x'] ) < $gan && abs( $nhom[ $i ]['y'] - $d['y'] ) < $gan ) {
				$nhom[ $i ]['ds'][] = $d;
				continue 2;
			}
		}
		$nhom[] = array( 'x' => $d['x'], 'y' => $d['y'], 'ds' => array( $d ) );
	}
	return $nhom;
}

/** Đường dẫn trang bản đồ ('' nếu chưa có). */
function cadao_url_ban_do(): string {
	$id = (int) get_option( 'cadao_trang_ban_do' );
	return $id && 'publish' === get_post_status( $id ) ? (string) get_permalink( $id ) : '';
}

add_shortcode(
	'cadao_ban_do',
	static function () {
		$diem = cadao_ban_do_diem();
		if ( ! $diem ) {
			return '<p>Chưa có địa danh nào được xác nhận. Bản đồ sẽ có điểm khi người biên soạn xác nhận các địa danh được chú thích trong bài.</p>';
		}
		$so_bai = count( array_unique( array_merge( ...array_map( static fn( $d ) => wp_list_pluck( $d['bai'], 'ID' ), $diem ) ) ) );
		$ra     = '<p>' . esc_html( sprintf( '%d địa danh trong %d bài. Chạm vào một điểm để xem các bài nhắc tới nơi đó; danh sách đầy đủ ở dưới bản đồ.', count( $diem ), $so_bai ) ) . '</p>'
			. '<div class="cadao-ban-do-lon"><img src="' . esc_url( plugins_url( 'assets/ban-do-vn.svg', CADAO_THU_MUC . '/ca-dao.php' ) ) . '" alt="" width="300" height="300" decoding="async">';
		foreach ( cadao_ban_do_gom( $diem ) as $n ) {
			$ten = implode( ', ', array_map( static fn( $d ) => $d['muc']->name, $n['ds'] ) );
			$so  = array_sum( array_map( static fn( $d ) => count( $d['bai'] ), $n['ds'] ) );
			$ra .= '<a class="cadao-ban-do-diem' . ( count( $n['ds'] ) > 1 ? ' cadao-ban-do-nhom' : '' ) . '" href="#dd-' . (int) $n['ds'][0]['muc']->term_id . '" style="left:' . $n['x'] . '%;top:' . $n['y'] . '%">'
				. ( count( $n['ds'] ) > 1 ? '<span aria-hidden="true">' . count( $n['ds'] ) . '</span>' : '' )
				. '<span class="screen-reader-text">' . esc_html( sprintf( '%s — %d bài', $ten, $so ) ) . '</span></a>';
		}
		$ra .= '</div><h2>Các địa danh, từ Bắc vào Nam</h2><ol class="cadao-ban-do-ds">';
		foreach ( $diem as $d ) {
			$ra .= '<li id="dd-' . (int) $d['muc']->term_id . '" tabindex="-1"><h3>' . esc_html( $d['muc']->name ) . '</h3>'
				. ( ! empty( $d['wd']['mo_ta'] ) ? '<p class="cadao-tim-phu">' . esc_html( $d['wd']['mo_ta'] ) . '</p>' : '' ) . '<ul class="cadao-tim-ds">';
			foreach ( $d['bai'] as $p ) {
				$ra .= '<li><a href="' . esc_url( get_permalink( $p ) ) . '"><strong>' . esc_html( get_the_title( $p ) ) . '</strong></a> <span class="cadao-tim-phu">· ' . esc_html( cadao_ten_the_loai( $p ) ) . '</span></li>';
			}
			$ra .= '</ul><p><a href="' . esc_url( (string) get_term_link( $d['muc'] ) ) . '">Xem "' . esc_html( $d['muc']->name ) . '" trong từ điển</a></p></li>';
		}
		return $ra . '</ol>';
	}
);

/** Trang "Bản đồ ca dao" (/ban-do/) tạo một lần. */
add_action(
	'init',
	static function () {
		if ( get_option( 'cadao_trang_ban_do' ) || wp_installing() ) {
			return;
		}
		$co = get_page_by_path( 'ban-do', OBJECT, 'page' );
		$id = $co ? $co->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Bản đồ ca dao', 'post_name' => 'ban-do', 'post_content' => "<!-- wp:shortcode -->\n[cadao_ban_do]\n<!-- /wp:shortcode -->" ) );
		update_option( 'cadao_trang_ban_do', (int) $id, false );
	},
	30
);

// Trang mục từ có tọa độ: "Xem trên bản đồ ca dao"
add_filter(
	'cadao_muc_tu_them',
	static function ( $html, $term_id ) {
		$wd  = cadao_wd_cua_muc_tu( (int) $term_id );
		$muc = get_term( (int) $term_id, 'tu_kho' );
		return $html . ( $wd && ! empty( $wd['toa_do'] ) && $muc && ! is_wp_error( $muc ) && $muc->count > 0 && cadao_url_ban_do() ?'<p class="cadao-lien-ket-wd"><a href="' . esc_url( cadao_url_ban_do() . '#dd-' . (int) $term_id ) . '">Xem trên bản đồ ca dao</a></p>' : '' );
	},
	20,
	2
);
