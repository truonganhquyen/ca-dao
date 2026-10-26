<?php
/**
 * Bài mẫu để xem và quét giao diện (T-072): dữ liệu TỔNG HỢP, không phải lời thật (repo công khai, AGENTS.md).
 * Dùng trong blueprint-giao-dien.json (quét trợ năng) và link xem trước WordPress.
 */

require_once '/wordpress/wp-load.php';

cadao_nguon_luu( array( 'id' => 'mau', 'ten' => 'Nguồn mẫu', 'url' => 'https://example.org', 'dong_ghi_nguon' => 'Nguồn mẫu' ) + cadao_nguon_mac_dinh() );
$ngay = gmdate( 'Y-m-d' );
// Lục bát mẫu (6 rồi 8 âm tiết), có dị bản và chú thích
cadao_kho_luu(
	'mau',
	array(
		'ma_goc' => 1, 'doc_trang' => $ngay, 'url' => 'https://example.org/luc-bat-mau/', 'loai' => 'ca_dao', 'tieu_de' => 'Dòng mẫu một của bài',
		'loi' => "Dòng mẫu một của bài này\nDòng mẫu hai dài hơn chút cho vừa\nDòng mẫu ba của bài này\nDòng mẫu bốn dài hơn chút cho xong",
		'di_ban' => array( "Dòng mẫu khác của bài này\nDòng mẫu hai khác dài hơn cho vừa" ),
		'chu_thich' => array( array( 'ma' => 1, 'tu' => 'chút', 'noi_dung' => 'Chú thích tổng hợp để xem giao diện (từ có trong lời: chạm để xem).' ), array( 'ma' => 2, 'tu' => '', 'noi_dung' => 'Chú thích không ghi từ.' ) ),
	)
);
// Câu đố mẫu
cadao_kho_luu( 'mau', array( 'ma_goc' => '/cau-do/mau', 'doc_trang' => $ngay, 'url' => 'https://example.org/cau-do/mau/', 'loai' => 'cau_do', 'tieu_de' => 'Câu đố mẫu', 'loi' => "Câu đố mẫu dòng một\nCâu đố mẫu dòng hai", 'cau_hoi' => 'Là cái gì?', 'dap_an' => 'Đáp án mẫu' ) );
// Tục ngữ mẫu, không chú thích
cadao_kho_luu( 'mau', array( 'ma_goc' => '/tuc-ngu/mau', 'doc_trang' => $ngay, 'url' => 'https://example.org/tuc-ngu/mau/', 'loai' => 'thanh_ngu_tuc_ngu', 'tieu_de' => 'Tục ngữ mẫu ngắn', 'loi' => 'Tục ngữ mẫu ngắn một dòng' ) );
cadao_kho_tao_bai_cho_duyet( cadao_nguon_lay( 'mau' ), 10 );
foreach ( get_posts( array( 'post_type' => 'cdtn', 'post_status' => 'pending', 'numberposts' => -1 ) ) as $p ) {
	wp_update_post( array( 'ID' => $p->ID, 'post_status' => 'publish' ) );
}
// Bài nhập tay có "Hiểu nhanh"
$tay = wp_insert_post(
	array(
		'post_type' => 'cdtn', 'post_status' => 'publish', 'post_title' => 'Bài nhập tay mẫu',
		'post_content' => cadao_khoi_tho( "Lời nhập tay mẫu dòng một\nLời nhập tay mẫu dòng hai" ),
		'post_excerpt' => 'Một câu giải nghĩa tổng hợp để xem khối Hiểu nhanh.',
	)
);
wp_set_object_terms( $tay, 'ca-dao', 'the_loai' );
// Lời đã đối chiếu (D-072) — tên sách tổng hợp, để xem dạng thứ hai của dòng mức kiểm
update_post_meta( $tay, 'doi_chieu_ten', 'Sách mẫu tổng hợp' );
update_post_meta( $tay, 'doi_chieu_trang', '12' );
// Bài dài mẫu (vè, 60 dòng, 3 khổ) để xem chia đoạn, mục lục, "Đọc toàn bài" (T-090) — lời tổng hợp
$dai = array();
for ( $k = 1; $k <= 3; $k++ ) {
	for ( $d = 1; $d <= 20; $d++ ) {
		$dai[] = sprintf( 'Khổ %d dòng mẫu số %d', $k, $d );
	}
	$dai[] = '';
}
$ve = wp_insert_post( array( 'post_type' => 'cdtn', 'post_status' => 'publish', 'post_title' => 'Vè mẫu dài', 'post_content' => cadao_khoi_tho( rtrim( implode( "\n", $dai ) ) ) ) );
wp_set_object_terms( $ve, 've', 'the_loai' );
// Đồng dao mẫu (T-103): trò chơi mẫu có Cách chơi do dự án viết; hai địa danh mẫu đã "xác nhận" để có điểm trên
// bản đồ ca dao (T-104). Lời, cách chơi, địa danh đều tổng hợp.
$ct_dd = static fn( array $ds ) => "<!-- wp:list {\"className\":\"cadao-chu-thich\"} -->\n<ul class=\"wp-block-list cadao-chu-thich\">\n"
	. implode( '', array_map( static fn( $tu, $nd ) => "<!-- wp:list-item -->\n<li><strong>" . esc_html( $tu ) . '</strong>: ' . esc_html( $nd ) . "</li>\n<!-- /wp:list-item -->\n", array_keys( $ds ), $ds ) )
	. "</ul>\n<!-- /wp:list -->";
$dd_mau = wp_insert_post(
	array(
		'post_type' => 'cdtn', 'post_status' => 'publish', 'post_title' => 'Đồng dao mẫu',
		'post_content' => cadao_khoi_tho( "Mẫu đồng dao một\nBé qua bến Mẫu Bắc\nBé về chợ Mẫu Nam\nMẫu đồng dao bốn" ) . "\n\n" . cadao_khoi_tieu_de( 'Chú thích' ) . "\n\n"
			. $ct_dd( array( 'bến Mẫu Bắc' => 'Địa danh tổng hợp ở phía bắc để xem bản đồ.', 'chợ Mẫu Nam' => 'Địa danh tổng hợp ở phía nam để xem bản đồ.' ) ),
	)
);
wp_set_object_terms( $dd_mau, 'dong-dao', 'the_loai' );
$tc_mau = wp_insert_term( 'Trò chơi mẫu', 'tro_choi' );
if ( ! is_wp_error( $tc_mau ) ) {
	foreach ( array( 'so_nguoi' => '4–8 bạn', 'do_tuoi' => '4–10 tuổi', 'do_dung' => 'Không cần', 'buoc' => "Bước mẫu thứ nhất của trò chơi.\nBước mẫu thứ hai: vừa đọc đồng dao vừa chơi." ) as $k => $v ) {
		update_term_meta( $tc_mau['term_id'], '_cadao_cc_' . $k, $v );
	}
	wp_set_object_terms( $dd_mau, (int) $tc_mau['term_id'], 'tro_choi' );
}
foreach ( array( 'bến Mẫu Bắc' => array( 'Địa danh tổng hợp ở phía bắc để xem bản đồ.', 21.03, 105.85 ), 'chợ Mẫu Nam' => array( 'Địa danh tổng hợp ở phía nam để xem bản đồ.', 10.03, 105.78 ) ) as $tu => [ $nd, $lat, $lon ] ) {
	$muc = cadao_muc_tu( array( 'tu' => $tu, 'noi_dung' => $nd ), false );
	if ( $muc ) {
		update_term_meta( $muc->term_id, '_cadao_wd', array( 'nhan_wd' => $tu, 'mo_ta' => 'địa danh tổng hợp', 'toa_do' => array( $lat, $lon ), 'wiki' => '' ) );
	}
}
// Số liệu mẫu (T-115): bài lục bát mẫu có đủ lượt đọc, chia sẻ, trích dẫn để thấy dòng 👁 ↗ ❝ — số tổng hợp
global $wpdb;
$lb_mau = get_posts( array( 'post_type' => 'cdtn', 'post_status' => 'publish', 'title' => 'Dòng mẫu một của bài', 'numberposts' => 1, 'fields' => 'ids' ) );
if ( $lb_mau ) {
	$wpdb->replace( cadao_luot_doc_bang(), array( 'post_id' => $lb_mau[0], 'ngay' => gmdate( 'Y-m-d' ), 'so' => 1234 ) );
	$wpdb->replace( cadao_su_kien_bang(), array( 'ngay' => gmdate( 'Y-m-d' ), 'loai' => 'chia_se', 'khoa' => $lb_mau[0] . ':web_share', 'so' => 38 ) );
	$wpdb->replace( cadao_su_kien_bang(), array( 'ngay' => gmdate( 'Y-m-d' ), 'loai' => 'trich_dan_sao_chep', 'khoa' => $lb_mau[0] . ':apa', 'so' => 12 ) );
}
