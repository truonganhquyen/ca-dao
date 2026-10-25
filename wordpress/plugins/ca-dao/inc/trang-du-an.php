<?php
/**
 * Trang Phương pháp, Nguồn, Về dự án (T-046). Máy tạo MỘT lần ở dạng **Bản nháp** với câu chữ soạn theo đúng cách site
 * đang làm (docs/24, docs/25, D-054..D-067); người sáng lập đọc, sửa rồi bấm Đăng. Trang Nguồn có danh sách tự sinh
 * (mã ngắn [cadao_nguon]) chỉ gồm nguồn đang được dùng. Chân trang chỉ hiện liên kết tới trang đã đăng.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_TRANG_DU_AN = array(
	'phuong-phap' => 'Phương pháp',
	'nguon'       => 'Nguồn',
	've-du-an'    => 'Về dự án',
);

/** Câu chữ ban đầu của ba trang (khối WordPress). Người sáng lập sửa trong trình soạn. */
function cadao_noi_dung_trang_du_an( string $slug ): string {
	$p  = static fn( $s ) => "<!-- wp:paragraph -->\n<p>$s</p>\n<!-- /wp:paragraph -->";
	$h  = static fn( $s ) => "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">$s</h2>\n<!-- /wp:heading -->";
	$ul = static fn( array $ds ) => "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . implode( '', array_map( static fn( $x ) => "<!-- wp:list-item -->\n<li>$x</li>\n<!-- /wp:list-item -->", $ds ) ) . "</ul>\n<!-- /wp:list -->";
	$khoi = array(
		'phuong-phap' => array(
			$p( '<strong>Lời giữ nguyên văn. Chú thích ghi nguồn. Máy chỉ gợi ý, người biên soạn quyết.</strong>' ),
			$h( 'Lời ca dao, tục ngữ lấy từ đâu' ),
			$p( 'Phần lớn lời được máy chép nguyên văn từ trang <a href="https://cadao.me">Ca dao Mẹ</a> — không qua AI, không sửa chữ, không đổi cách bỏ dấu. Người biên soạn cũng nhập tay những câu mình biết. Lời ca dao, tục ngữ là của cộng đồng nên không ghi nguồn dưới lời.' ),
			$p( 'Ca dao, tục ngữ sống bằng truyền miệng, một câu thường có nhiều cách nói. Chúng tôi không chọn "bản đúng duy nhất": các cách nói khác được ghi ở mục <strong>Dị bản</strong>, chữ khác lời chính được tô để dễ so.' ),
			$h( 'Chú thích, đáp án' ),
			$p( 'Chú thích từ khó và đáp án câu đố chép từ nguồn luôn có dòng <strong>"Nguồn: …"</strong> dẫn về đúng trang đã lấy. Chú thích do dự án viết thì không có dòng đó. Ảnh minh hoạ chỉ lấy từ Wikimedia Commons với giấy phép mở, ghi tác giả và giấy phép ngay dưới ảnh.' ),
			$h( 'Máy làm gì, người làm gì' ),
			$ul(
				array(
					'Máy đọc nguồn tự động (chậm rãi, vài giây một trang), đưa bài vào hàng <strong>Chờ duyệt</strong>; người biên soạn đọc rồi mới đăng.',
					'Máy so cả lời để nhận ra bài trùng: lời giống hệt thì gắn vào bài có sẵn, nghi là dị bản thì chờ người biên soạn quyết. Không bài nào bị xoá.',
					'Máy gợi ý thể loại (thành ngữ hay tục ngữ), vùng, chủ đề, địa danh trên Wikidata; chỉ những gì người biên soạn đồng ý mới hiện ra trang.',
					'AI không viết lời, dị bản, nguồn. Phần giải nghĩa nào có hỗ trợ của AI thì chỉ hiện sau khi người biên soạn duyệt, và bài ghi rõ: "Phần giải nghĩa có hỗ trợ của AI, đã được người biên soạn duyệt".',
				)
			),
			$h( 'Đóng góp của bạn đi đâu' ),
			$p( 'Góp ý, dị bản ở quê bạn, báo lỗi, cảm nghĩ và bài phân tích bạn gửi đều vào hàng Chờ duyệt; người biên soạn đọc rồi mới hiện. Không cần tên thật, email hay số điện thoại.' ),
			$h( 'Khi chúng tôi sai' ),
			$p( 'Mỗi bài có nút <strong>Báo lỗi</strong>. Báo lỗi được đọc trước mọi việc khác; khi phát hiện sai, chúng tôi sửa ngay trên bài.' ),
		),
		'nguon'       => array(
			$p( 'Danh sách dưới đây do máy lập từ những nguồn đang thực sự được dùng trên site, kèm số bài.' ),
			"<!-- wp:shortcode -->\n[cadao_nguon]\n<!-- /wp:shortcode -->",
		),
		've-du-an'    => array(
			$p( '<strong>Kho tàng Ca dao Tục ngữ Việt Nam</strong> là dự án phi lợi nhuận, không quảng cáo: một nơi để đọc, hiểu, trích dẫn và chia sẻ ca dao, tục ngữ, thành ngữ, đồng dao, vè, câu đố — đúng lời, có nghĩa từ khó, có dị bản, có nguồn cho phần chú thích.' ),
			$p( 'Mã nguồn của site là mã nguồn mở: <a href="https://github.com/truonganhquyen/ca-dao">github.com/truonganhquyen/ca-dao</a>. Phần dự án tự viết dùng giấy phép CC BY 4.0; phần chép từ Wiktionary, Wikipedia giữ CC BY-SA và được ghi ngay tại chỗ.' ),
			$p( 'Site không dùng cookie theo dõi, không lưu địa chỉ IP khi đếm lượt đọc.' ),
			$p( '<strong>Liên hệ, báo lỗi, xin gỡ:</strong> dùng mục "Góp ý cho bài này" ở cuối mỗi bài.' ),
		),
	);
	return implode( "\n\n", $khoi[ $slug ] ?? array() );
}

// Tạo ba trang một lần, ở dạng Bản nháp
add_action(
	'init',
	static function () {
		if ( get_option( 'cadao_trang_du_an' ) || wp_installing() ) {
			return;
		}
		foreach ( CADAO_TRANG_DU_AN as $slug => $ten ) {
			if ( ! get_page_by_path( $slug, OBJECT, 'page' ) && ! get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1 ) ) ) {
				wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => $ten, 'post_name' => $slug, 'post_content' => cadao_noi_dung_trang_du_an( $slug ) ) );
			}
		}
		update_option( 'cadao_trang_du_an', 1, false );
	},
	31
);

/** [cadao_nguon]: nguồn đang được dùng — nguồn tự động, nguồn người biên soạn ghi trong bài, dữ liệu mở. */
add_shortcode(
	'cadao_nguon',
	static function () {
		global $wpdb;
		$ra = '';
		// Nguồn tự động: số bài đang đăng có chú thích / đáp án chép từ nguồn đó
		$tu_dong = '';
		foreach ( cadao_nguon_tat_ca() as $id => $n ) {
			$so = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_cadao_nguon_id' AND m.meta_value = %s WHERE p.post_status = 'publish' AND p.post_type = %s AND p.post_content LIKE %s", (string) $id, CADAO_LOAI_BAI, '%cadao-chu-thich-nguon%' ) );
			if ( $so ) {
				$tu_dong .= '<li><a href="' . esc_url( $n['url'] ) . '" rel="noopener">' . esc_html( $n['dong_ghi_nguon'] ?: $n['ten'] ) . '</a> — chú thích, đáp án chép kèm dòng "Nguồn" ở ' . esc_html( number_format_i18n( $so ) ) . ' bài; lời ca dao lấy từ đây không ghi nguồn.</li>';
			}
		}
		if ( $tu_dong ) {
			$ra .= '<h2>Nguồn của chú thích, đáp án</h2><ul>' . $tu_dong . '</ul>';
		}
		// Nguồn người biên soạn tự ghi trong bài
		$tay = $wpdb->get_results( $wpdb->prepare( "SELECT m.meta_value AS ten, COUNT(*) AS so FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id WHERE m.meta_key = 'nguon_ten' AND m.meta_value <> '' AND p.post_status = 'publish' AND p.post_type = %s GROUP BY m.meta_value ORDER BY so DESC", CADAO_LOAI_BAI ) );
		if ( $tay ) {
			$ra .= '<h2>Nguồn người biên soạn ghi trong bài</h2><ul>';
			foreach ( $tay as $r ) {
				$ra .= '<li>' . esc_html( $r->ten ) . ' — ' . esc_html( number_format_i18n( (int) $r->so ) ) . ' bài</li>';
			}
			$ra .= '</ul>';
		}
		// Dữ liệu mở (chỉ khi đã dùng)
		$mo = array();
		$wd = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . cadao_wd_bang() . " WHERE trang_thai = 'dung'" );
		if ( $wd ) {
			$mo[] = '<a href="https://www.wikidata.org">Wikidata</a> (CC0) — ' . esc_html( number_format_i18n( $wd ) ) . ' địa danh, sự vật đã được người biên soạn xác nhận: tọa độ, mô tả, liên kết Wikipedia.';
			$mo[] = '<a href="https://commons.wikimedia.org">Wikimedia Commons</a> — ảnh minh hoạ với giấy phép mở; tác giả và giấy phép ghi dưới từng ảnh.';
			$mo[] = '<a href="https://www.naturalearthdata.com">Natural Earth</a> (phạm vi công cộng) — đường biên của bản đồ nhỏ.';
		}
		if ( $mo ) {
			$ra .= '<h2>Dữ liệu mở</h2><ul><li>' . implode( '</li><li>', $mo ) . '</li></ul>';
		}
		return $ra ?: '<p>Chưa có bài nào dùng nguồn.</p>';
	}
);

/** [cadao_chan_trang]: liên kết tới các trang của dự án đã đăng (chân trang). */
add_shortcode(
	'cadao_chan_trang',
	static function () {
		$ra = array();
		foreach ( CADAO_TRANG_DU_AN as $slug => $ten ) {
			$p = get_page_by_path( $slug );
			if ( $p && 'publish' === $p->post_status ) {
				$ra[] = '<a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a>';
			}
		}
		if ( function_exists( 'cadao_url_da_luu' ) && cadao_url_da_luu() ) {
			$ra[] = '<a href="' . esc_url( cadao_url_da_luu() ) . '">Bài đã lưu</a>';
		}
		return implode( ' · ', $ra );
	}
);
