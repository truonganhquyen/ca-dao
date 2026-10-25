<?php
/**
 * Bàn biên tập (T-047): một trang gom mọi hàng chờ của người biên soạn, mỗi hàng có số đếm, một dòng "làm gì" và nút
 * mở đúng trang (nơi đó có duyệt hàng loạt nếu làm được). Báo lỗi của người đọc đứng đầu (trang Phương pháp hứa đọc
 * báo lỗi trước mọi việc khác). Ô "Việc chờ bạn" ở đầu Bảng tin tóm tắt các hàng còn việc.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRANG_BAN = 'cadao-ban-bien-tap';

/** Số bài (loại bài, trạng thái), có thể lọc theo một khóa meta. */
function cadao_dem_bai( string $loai, string $trang_thai, string $khoa = '', string $gia_tri = '' ): int {
	global $wpdb;
	if ( '' === $khoa ) {
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s", $loai, $trang_thai ) );
	}
	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = %s AND m.meta_value = %s WHERE p.post_type = %s AND p.post_status = %s", $khoa, $gia_tri, $loai, $trang_thai ) );
}

/**
 * Các hàng chờ, theo thứ tự nên làm.
 *
 * @return array<int, array{khoa: string, ten: string, so: int, viec: string, url: string}>
 */
function cadao_hang_cho(): array {
	global $wpdb;
	$trang = static fn( string $p ) => admin_url( 'edit.php?post_type=' . CADAO_LOAI_BAI . '&page=' . $p );
	$ds    = array();
	if ( defined( 'CADAO_LOAI_GOP_Y' ) ) {
		$bao_loi = cadao_dem_bai( CADAO_LOAI_GOP_Y, 'pending', '_cadao_loai', 'bao_loi' ) + cadao_dem_bai( CADAO_LOAI_GOP_Y, 'pending', '_cadao_loai', 'vi_pham' );
		$ds[]    = array( 'khoa' => 'bao_loi', 'ten' => 'Báo lỗi, báo vi phạm của người đọc', 'so' => $bao_loi, 'viec' => 'Đọc trước mọi việc khác; sai thì sửa ngay trên bài.', 'url' => admin_url( 'edit.php?post_type=' . CADAO_LOAI_GOP_Y . '&post_status=pending' ) );
		$ds[]    = array( 'khoa' => 'gop_y', 'ten' => 'Góp ý khác của người đọc', 'so' => cadao_dem_bai( CADAO_LOAI_GOP_Y, 'pending' ) - $bao_loi, 'viec' => 'Dị bản ở quê, góp ý chú thích, số trang sách: dùng được thì chép vào bài, rồi bấm Đăng (đánh dấu đã xem) hoặc Thùng rác.', 'url' => admin_url( 'edit.php?post_type=' . CADAO_LOAI_GOP_Y . '&post_status=pending' ) );
	}
	$ds[] = array( 'khoa' => 'binh_luan', 'ten' => 'Cảm nghĩ chờ duyệt', 'so' => (int) wp_count_comments()->moderated, 'viec' => 'Chấp thuận thì cảm nghĩ hiện dưới bài.', 'url' => admin_url( 'edit-comments.php?comment_status=moderated' ) );
	if ( defined( 'CADAO_LOAI_PHAN_TICH' ) ) {
		$ds[] = array( 'khoa' => 'phan_tich', 'ten' => 'Bài cảm nhận, phân tích chờ duyệt', 'so' => cadao_dem_bai( CADAO_LOAI_PHAN_TICH, 'pending' ), 'viec' => 'Đọc rồi bấm Đăng; bài ca dao có bài phân tích được duyệt sẽ lên Google.', 'url' => admin_url( 'edit.php?post_type=' . CADAO_LOAI_PHAN_TICH . '&post_status=pending' ) );
	}
	$ds[] = array( 'khoa' => 'cho_duyet', 'ten' => 'Bài Chờ duyệt', 'so' => cadao_dem_bai( CADAO_LOAI_BAI, 'pending' ), 'viec' => 'Đọc lời, chú thích rồi Đăng. Muốn đăng nhiều bài một lần: chọn bài → Hành động: Chỉnh sửa → Trạng thái: Đã xuất bản.', 'url' => admin_url( 'edit.php?post_type=' . CADAO_LOAI_BAI . '&post_status=pending' ) );
	if ( function_exists( 'cadao_bai_chua_khoi_tho' ) ) {
		$ds[] = array( 'khoa' => 'khoi_tho', 'ten' => 'Lời chưa vào khối Thơ', 'so' => count( cadao_bai_chua_khoi_tho() ), 'viec' => 'Xem đề xuất rồi bấm "Chuyển các bài đã chọn" (không đổi chữ nào).', 'url' => $trang( CADAO_TRANG_KHOI_THO ) );
	}
	if ( function_exists( 'cadao_trung_dem_cho' ) ) {
		$ds[] = array( 'khoa' => 'trung', 'ten' => 'Có thể trùng', 'so' => cadao_trung_dem_cho(), 'viec' => 'Mỗi cặp: cùng một bài, dị bản, hay bài khác. Câu đang chờ thì máy chưa tạo thành bài.', 'url' => $trang( CADAO_TRANG_TRUNG ) );
	}
	if ( function_exists( 'cadao_kho_bang' ) && defined( 'CADAO_TRANG_TACH_LOAI' ) ) {
		$ds[] = array( 'khoa' => 'tach_loai', 'ten' => 'Tách thành ngữ – tục ngữ', 'so' => (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . cadao_kho_bang() . " WHERE loai = 'thanh_ngu_tuc_ngu' AND loi <> '' AND trang_thai <> 'bo_qua' AND du_lieu NOT LIKE '%\"loai_tach\"%'" ), 'viec' => 'Nhóm "Máy đã chắc": kiểm vài câu rồi chọn hàng loạt, bấm "Đồng ý gợi ý".', 'url' => $trang( CADAO_TRANG_TACH_LOAI ) );
	}
	if ( function_exists( 'cadao_wd_bang' ) ) {
		$ds[] = array( 'khoa' => 'wikidata', 'ten' => 'Xác nhận Wikidata', 'so' => (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . cadao_wd_bang() . " WHERE trang_thai = 'cho'" ), 'viec' => 'Mỗi mục: Đúng / Sai / Bỏ qua. Mục "Đúng" có ảnh, bản đồ nhỏ và chấm trên Bản đồ ca dao.', 'url' => $trang( CADAO_TRANG_WIKIDATA ) );
	}
	if ( defined( 'CADAO_TRANG_GOI_Y' ) ) {
		$ds[] = array( 'khoa' => 'goi_y', 'ten' => 'Gợi ý vùng, chủ đề', 'so' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s", '_cadao_goi_y' ) ), 'viec' => 'Xem bảng ánh xạ chủ đề ở đầu trang rồi bấm "Lưu"; sau đó bỏ dấu cái sai, bấm "Đồng ý".', 'url' => $trang( CADAO_TRANG_GOI_Y ) );
	}
	if ( function_exists( 'cadao_bcb_ds' ) ) {
		$ds[] = array( 'khoa' => 'bcb', 'ten' => 'Bạn có biết?', 'so' => count( array_filter( cadao_bcb_ds(), static fn( $x ) => 'cho' === $x['trang_thai'] ) ), 'viec' => 'Sửa câu máy ghép cho tự nhiên rồi chọn "Duyệt"; trang chủ hiện 4 câu đã duyệt mỗi tuần.', 'url' => $trang( CADAO_TRANG_BCB ) );
	}
	if ( defined( 'CADAO_TRANG_DU_AN' ) ) {
		$nhap = 0;
		foreach ( array_keys( CADAO_TRANG_DU_AN ) as $slug ) {
			$p     = get_page_by_path( $slug, OBJECT, 'page' );
			$nhap += $p && 'publish' !== $p->post_status ? 1 : 0;
		}
		$ds[] = array( 'khoa' => 'du_an', 'ten' => 'Trang Phương pháp, Nguồn, Về dự án', 'so' => $nhap, 'viec' => 'Máy đã soạn bản nháp; đọc, sửa câu chữ rồi bấm Đăng. Chân trang tự có liên kết.', 'url' => admin_url( 'edit.php?post_type=page&post_status=draft' ) );
	}
	$trong = get_terms( array( 'taxonomy' => 'the_loai', 'hide_empty' => true, 'fields' => 'all' ) );
	$ds[]  = array( 'khoa' => 'the_loai', 'ten' => 'Giới thiệu thể loại', 'so' => is_array( $trong ) ? count( array_filter( $trong, static fn( $t ) => '' === trim( $t->description ) ) ) : 0, 'viec' => 'Vài dòng giới thiệu, cách đọc cho mỗi thể loại (ô "Mô tả"); hiện ở đầu trang thể loại.', 'url' => admin_url( 'edit-tags.php?taxonomy=the_loai&post_type=' . CADAO_LOAI_BAI ) );
	return $ds;
}

function cadao_trang_ban_bien_tap(): void {
	$ds   = cadao_hang_cho();
	$con  = array_filter( $ds, static fn( $h ) => $h['so'] > 0 );
	$xong = array_filter( $ds, static fn( $h ) => $h['so'] <= 0 );
	echo '<div class="wrap"><h1>Bàn biên tập</h1><p>Mọi việc đang chờ bạn, xếp theo thứ tự nên làm. Mỗi hàng mở đúng trang để làm; trang nào làm hàng loạt được thì có nút ở đó.</p>';
	if ( ! $con ) {
		echo '<p><strong>Không còn việc nào đang chờ.</strong></p>';
	}
	foreach ( $con as $h ) {
		echo '<div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px 16px;background:#fff;border:1px solid #dcdcde;border-left:4px solid ' . ( 'bao_loi' === $h['khoa'] ? '#d63638' : '#2271b1' ) . ';padding:12px 16px;margin:10px 0;max-width:52rem">'
			. '<span style="font-size:1.6em;font-weight:600;min-width:3ch;text-align:right">' . esc_html( number_format_i18n( $h['so'] ) ) . '</span>'
			. '<span style="flex:1 1 13rem"><strong>' . esc_html( $h['ten'] ) . '</strong><br><span class="description">' . esc_html( $h['viec'] ) . '</span></span>'
			. '<a class="button button-primary" href="' . esc_url( $h['url'] ) . '">Mở</a></div>';
	}
	if ( $xong ) {
		echo '<h2>Đã xong</h2><p class="description">' . esc_html( implode( ' · ', array_column( $xong, 'ten' ) ) ) . '</p>';
	}
	echo '<p class="description">Việc khác khi rảnh: đọc số liệu mỗi tuần (ô "người đọc cần gì" trên Bảng tin), tạo trò chơi cho đồng dao (Ca dao – Tục ngữ → Trò chơi), gắn bài vào tình huống.</p></div>';
}

add_action(
	'admin_menu',
	static function () {
		$so = array_sum( array_column( array_filter( cadao_hang_cho(), static fn( $h ) => in_array( $h['khoa'], array( 'bao_loi', 'gop_y', 'binh_luan', 'phan_tich', 'cho_duyet' ), true ) ), 'so' ) );
		add_submenu_page( 'edit.php?post_type=' . CADAO_LOAI_BAI, 'Bàn biên tập', 'Bàn biên tập' . ( $so > 0 ? ' <span class="awaiting-mod">' . (int) $so . '</span>' : '' ), 'edit_others_posts', CADAO_TRANG_BAN, 'cadao_trang_ban_bien_tap', 0 );
	},
	20
);

// Ô "Việc chờ bạn" ở đầu Bảng tin
add_action(
	'wp_dashboard_setup',
	static function () {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			return;
		}
		wp_add_dashboard_widget(
			'cadao_viec_cho',
			'Ca dao – Tục ngữ: việc chờ bạn',
			static function () {
				$con = array_filter( cadao_hang_cho(), static fn( $h ) => $h['so'] > 0 );
				if ( ! $con ) {
					echo '<p>Không còn việc nào đang chờ.</p>';
					return;
				}
				echo '<ul>';
				foreach ( $con as $h ) {
					echo '<li><a href="' . esc_url( $h['url'] ) . '"><strong>' . esc_html( number_format_i18n( $h['so'] ) ) . '</strong> · ' . esc_html( $h['ten'] ) . '</a></li>';
				}
				echo '</ul><p><a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=' . CADAO_LOAI_BAI . '&page=' . CADAO_TRANG_BAN ) ) . '">Mở Bàn biên tập</a></p>';
			},
			null,
			null,
			'normal',
			'high'
		);
	}
);
