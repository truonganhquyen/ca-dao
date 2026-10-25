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
