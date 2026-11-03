<?php
/**
 * Phép thử chuyển đổi, bước 1 (docs/29 mục 4): plugin MVP đang bật. Tạo dữ liệu mẫu bằng chính mã MVP (mọi cách một
 * bài có thể nằm trên site thật), rồi chạy tệp nhập thử cong-cu/thu-nhap.php như trên site thật và ghi số dòng của
 * cơ sở dữ liệu trước / sau (chạy thử không được ghi gì). Kết quả để bước 2 (kiem.php, V2 đã thay chỗ) kiểm.
 * Dữ liệu tổng hợp.
 */

require_once '/wordpress/wp-load.php';

const CD_TEP = '/repo/tests/.chuyen-doi-mvp.json';
$GLOBALS['cd'] = array( 'loi' => array() );
register_shutdown_function(
	static function (): void {
		$loi = error_get_last();
		if ( $loi && in_array( $loi['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR ), true ) ) {
			$GLOBALS['cd']['loi'][] = "LỖI PHP: {$loi['message']} ({$loi['file']}:{$loi['line']})";
		}
		file_put_contents( CD_TEP, wp_json_encode( $GLOBALS['cd'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
	}
);

global $wpdb;
wp_set_current_user( 1 );
$ma_kho = static fn( int $ma ): int => (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . cadao_kho_bang() . " WHERE nguon_id = 'cadao-me' AND ma_goc = %s", (string) $ma ) );
$luu    = static fn( int $ma, array $muc ): string => cadao_kho_luu( 'cadao-me', $muc + array( 'ma_goc' => $ma, 'doc_trang' => '2026-10-25', 'url' => "https://example.org/bai-$ma/", 'co' => array() ) );
$tao    = static function ( int $ma, string $trang_thai = 'publish' ) use ( $ma_kho ): int {
	$id = cadao_kho_tao_bai( $ma_kho( $ma ) );
	if ( is_wp_error( $id ) ) {
		$GLOBALS['cd']['loi'][] = "kho $ma: " . $id->get_error_message();
		return 0;
	}
	if ( 'pending' !== $trang_thai ) {
		wp_update_post( array( 'ID' => $id, 'post_status' => $trang_thai ) );
	}
	return (int) $id;
};
$tay = static function ( string $ten, string $noi_dung, string $the_loai, array $meta = array() ): int {
	$id = wp_insert_post( wp_slash( array( 'post_type' => 'cdtn', 'post_status' => 'publish', 'post_title' => $ten, 'post_content' => $noi_dung, 'meta_input' => $meta ) ) );
	wp_set_object_terms( $id, $the_loai, 'the_loai' );
	return (int) $id;
};

// Bài từ kho, chưa sửa tay; một câu khác gộp vào làm dị bản; một cảm nghĩ
$luu( 1, array( 'loai' => 'ca_dao', 'tieu_de' => 'Dòng mẫu một của bài', 'loi' => "Dòng mẫu một của bài\nDòng mẫu hai dài hơn chút", 'di_ban' => array( "Dòng mẫu một khác chữ\nDòng mẫu hai dài hơn chút" ), 'chu_thich' => array( array( 'tu' => 'mẫu', 'noi_dung' => 'Lời giải mẫu.' ) ) ) );
$a = $tao( 1 );
$luu( 6, array( 'loai' => 'ca_dao', 'tieu_de' => 'Dòng gộp vào bài một', 'loi' => "Dòng gộp vào bài một\nDòng gộp hai của câu" ) );
cadao_trung_gop( 'kho:' . $ma_kho( 6 ), 'bai:' . $a, 'di_ban' );
wp_insert_comment( array( 'comment_post_ID' => $a, 'comment_content' => 'Cảm nghĩ mẫu.', 'comment_approved' => 1, 'comment_author' => 'Bạn đọc' ) );

// Bài từ kho đã sửa tay lời
$luu( 2, array( 'loai' => 'ca_dao', 'tieu_de' => 'Dòng sửa tay gốc', 'loi' => "Dòng sửa tay gốc\nDòng thứ hai giữ nguyên" ) );
$b = $tao( 2 );
wp_update_post( wp_slash( array( 'ID' => $b, 'post_content' => str_replace( 'Dòng sửa tay gốc<br>', 'Dòng đã sửa tay<br>', get_post_field( 'post_content', $b ) ) ) ) );

// Câu đố có câu hỏi, đáp án chép
$luu( 3, array( 'loai' => 'cau_do', 'tieu_de' => 'Cái gì mẫu tròn tròn', 'loi' => "Cái gì mẫu tròn tròn\nCó hai dòng để đố", 'cau_hoi' => 'Là cái gì?', 'dap_an' => 'Cái mẫu' ) );
$c = $tao( 3 );

// Tục ngữ có Hiểu nhanh người sáng lập đã duyệt từ nháp AI
$luu( 4, array( 'loai' => 'thanh_ngu_tuc_ngu', 'tieu_de' => 'Tục ngữ mẫu ngắn gọn', 'loi' => 'Tục ngữ mẫu ngắn gọn' ) );
$d = $tao( 4 );
wp_update_post( wp_slash( array( 'ID' => $d, 'post_excerpt' => 'Ý nghĩa mẫu có hỗ trợ.' ) ) );
update_post_meta( $d, '_cadao_ho_tro_ai', 1 );

// Bài đã đăng có nháp Hiểu nhanh của máy (chưa duyệt)
$luu( 8, array( 'loai' => 'ca_dao', 'tieu_de' => 'Dòng có nháp ý nghĩa', 'loi' => "Dòng có nháp ý nghĩa\nDòng hai của bài nháp" ) );
$n = $tao( 8 );
update_post_meta( $n, '_cadao_nhap_hieu_nhanh', 'Nháp ý nghĩa mẫu.' );

// Chờ duyệt do máy tạo, chưa ai đụng: không mang
$luu( 5, array( 'loai' => 'ca_dao', 'tieu_de' => 'Dòng chờ duyệt mẫu', 'loi' => "Dòng chờ duyệt mẫu\nDòng hai chờ duyệt" ) );
$e = $tao( 5, 'pending' );

// Nhập tay: vè còn trong khối Đoạn văn; bài có chú thích người viết, nguồn người điền, đối chiếu; hai bài trùng tên
$g = $tay( 'Vè mẫu trong đoạn văn', "<!-- wp:paragraph -->\n<p>Vè mẫu dòng một<br>Vè mẫu dòng hai</p>\n<!-- /wp:paragraph -->", 've' );
$h = $tay(
	'Bài có chú thích người viết',
	"<!-- wp:verse -->\n<pre class=\"wp-block-verse\">Bài có chú thích người viết<br>Dòng hai của bài tay</pre>\n<!-- /wp:verse -->\n\n<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Chú thích</h2>\n<!-- /wp:heading -->\n\n<!-- wp:list {\"className\":\"cadao-chu-thich\"} -->\n<ul class=\"wp-block-list cadao-chu-thich\"><!-- wp:list-item -->\n<li><strong>bài tay</strong>: Lời giải người viết.</li>\n<!-- /wp:list-item --></ul>\n<!-- /wp:list -->",
	'ca-dao',
	array( 'nguon_ten' => 'Sách mẫu người điền', 'nguon_url' => 'https://example.org/sach-mau', 'ngay_truy_cap' => '2026-10-20', 'doi_chieu_ten' => 'Tuyển tập mẫu', 'doi_chieu_trang' => '12' )
);
$i1 = $tay( 'Câu trùng tên mẫu', "<!-- wp:verse -->\n<pre class=\"wp-block-verse\">Câu trùng tên mẫu<br>Dòng sau thứ nhất</pre>\n<!-- /wp:verse -->", 'ca-dao' );
$i2 = $tay( 'Câu trùng tên mẫu', "<!-- wp:verse -->\n<pre class=\"wp-block-verse\">Câu trùng tên mẫu<br>Dòng sau phân biệt</pre>\n<!-- /wp:verse -->", 'ca-dao' );

$GLOBALS['cd']['id'] = compact( 'a', 'b', 'c', 'd', 'n', 'e', 'g', 'h', 'i1', 'i2' );
$GLOBALS['cd']['ten_i2'] = get_post_field( 'post_name', $i2 );

// Chạy thử như trên site thật: đếm dòng mọi bảng trước / sau
$dem = static function () use ( $wpdb ): array {
	$ra = array();
	foreach ( preg_grep( '/^wp_/', $wpdb->get_col( 'SHOW TABLES' ) ) as $bang ) {
		$ra[ $bang ] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `$bang`" );
	}
	$ra['tuy_chon'] = md5( wp_json_encode( $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name NOT LIKE '%transient%' ORDER BY option_name" ) ) );
	return $ra;
};
$GLOBALS['cd']['truoc'] = $dem();
ob_start();
require '/repo/cong-cu/thu-nhap.php';
$GLOBALS['cd']['thu_nhap'] = (string) ob_get_clean();
$GLOBALS['cd']['sau']      = $dem();
