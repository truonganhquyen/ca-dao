<?php
/**
 * Kiểm thử plugin ca-dao trong WordPress Playground (docs/24 mục 6): `npm run test:wordpress`.
 * Dữ liệu tổng hợp — không chứa nội dung thật của nguồn (repo này công khai).
 * Đặt hằng CADAO_KIEM_MANG để thử thêm bộ đọc trên cadao.me thật (vài yêu cầu, giữ nhịp 3 giây).
 */

require_once '/wordpress/wp-load.php';

// Playground không in đầu ra của bước runPHP: ghi thẳng từng dòng kết quả ra tệp (kể cả khi lỗi giữa chừng)
const CADAO_TEP_KQ = '/repo/tests/.ket-qua.txt';
file_put_contents( CADAO_TEP_KQ, '' );
function ra( string $dong ): void {
	file_put_contents( CADAO_TEP_KQ, $dong . "\n", FILE_APPEND );
	echo $dong, "\n";
}
register_shutdown_function(
	static function () {
		$loi = error_get_last();
		if ( $loi && in_array( $loi['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR ), true ) ) {
			ra( "LỖI PHP: {$loi['message']} ({$loi['file']}:{$loi['line']})" );
		}
	}
);

$GLOBALS['cadao_hong'] = 0;
$GLOBALS['cadao_dat']  = 0;
function kt( string $ten, bool $dk, $chi_tiet = null ): void {
	if ( $dk ) {
		++$GLOBALS['cadao_dat'];
		ra( "ĐẠT   $ten" );
	} else {
		++$GLOBALS['cadao_hong'];
		ra( "HỎNG  $ten" . ( null !== $chi_tiet ? ' — ' . var_export( $chi_tiet, true ) : '' ) );
	}
}

// ---------- Trích lời (tất định, như bộ trích của kho thô) ----------
$r = cadao_trich_loi( "<p>Dòng một,<a data-counter=\"1\" data-ref-id=\"42\"\n class=\"footnote \" href=\"#note-42\"></a><br />\nDòng  hai <em>nghiêng</em>&#8230;</p>" );
kt( 'br thành xuống dòng, gộp khoảng trắng, giải mã thực thể', "Dòng một,\nDòng hai nghiêng…" === $r['loi'], $r['loi'] );
kt( 'nhớ vị trí dấu chú thích ở cuối dòng', array( array( 'ma' => 42, 'dong' => 1, 'cot' => 9 ) ) === $r['chu_thich'], $r['chu_thich'] );
$r = cadao_trich_loi( "<p>Dòng có dấu cách cuối&nbsp;<a class=\"footnote\" data-ref-id=\"7\"></a></p>" );
kt( 'cắt dấu cách không ngắt ở cuối dòng (như JavaScript)', 'Dòng có dấu cách cuối' === $r['loi'], $r['loi'] );
kt( 'dấu chú thích sau dấu cách cuối dòng vẫn còn, cột không vượt độ dài', array( array( 'ma' => 7, 'dong' => 1, 'cot' => 21 ) ) === $r['chu_thich'], $r['chu_thich'] );
kt( 'giữ ký tự U+0085 như bộ trích JavaScript (không coi là khoảng trắng)', "đi\u{0085}" === cadao_trich_loi( "<p>đi\u{0085}</p>" )['loi'] );
kt( 'đoạn mới thành dòng trống', "Đoạn một\n\nĐoạn hai" === cadao_trich_loi( "<p>Đoạn một</p>\n<p>Đoạn hai</p>" )['loi'] );
kt( 'bỏ ảnh và chú thích ảnh', 'Có ảnh' === cadao_trich_loi( '<p>Có ảnh<div class="wp-caption alignnone"><img src="x.jpg"><p class="wp-caption-text">Chú thích ảnh</p></div></p>' )['loi'] );
kt( 'PHP có Normalizer (phần mở rộng intl) để chuẩn hóa NFC', class_exists( 'Normalizer' ) );
$tohop = "Hoa\u{0300}"; // "Hoà" viết bằng dấu huyền tổ hợp đặt sau chữ a
kt( 'chuẩn hóa NFC', "Ho\u{00E0}" === cadao_trich_loi( "<p>$tohop</p>" )['loi'], bin2hex( cadao_trich_loi( "<p>$tohop</p>" )['loi'] ) );
kt( 'văn bản thường: bỏ thẻ, giữ khối', "Một liên kết\nHai" === cadao_van_ban( '<p>Một <a href="/x">liên kết</a></p><div class="wp-caption"><img src="a.jpg"><p>ảnh</p></div><p>Hai</p>' ) );

// ---------- Cờ ----------
kt( 'cờ can_xem theo chủ đề', in_array( 'can_xem', cadao_gan_co( 'Dòng mẫu', array( 'Lịch sử' ), array() ), true ) );
kt( 'cờ tho_tuc theo trọn âm tiết', in_array( 'tho_tuc', cadao_gan_co( 'Dòng có từ cấmmẫu và cấm', array(), array( 'cấm' ) ), true ) );
kt( 'không gắn tho_tuc khi chỉ là một phần âm tiết', ! in_array( 'tho_tuc', cadao_gan_co( 'Dòng có cấmmẫu', array(), array( 'cấm' ) ), true ) );
kt( 'cờ hien_dai khi có năm 19xx', in_array( 'hien_dai', cadao_gan_co( 'Năm 1975 mẫu', array(), array() ), true ) );

// ---------- Kho → bài Chờ duyệt ----------
cadao_nguon_luu( array( 'id' => 'nguon-thu', 'ten' => 'Nguồn thử', 'url' => 'https://example.org', 'dong_ghi_nguon' => 'Nguồn thử', 'so_bai_moi_ngay' => 5, 'chep_chu_thich' => true, 'ghi_cong' => true, 'loai_lay' => array( 'ca_dao', 've' ) ) + cadao_nguon_mac_dinh() );
$muc = array(
	'ma_goc' => 101, 'doc_trang' => '2026-10-25', 'url' => 'https://example.org/bai-101/', 'loai' => 'ca_dao', 'tieu_de' => 'Dòng mẫu thứ nhất',
	'loi' => "Dòng mẫu thứ nhất\nDòng mẫu thứ hai <có dấu nhỏ hơn>", 'di_ban' => array( "Dị bản mẫu một\nDị bản mẫu hai" ),
	'chu_thich' => array( array( 'tu' => 'từ mẫu', 'noi_dung' => 'Chú thích mẫu.' ) ), 'co' => array(),
);
kt( 'lưu mục mới vào kho', 'moi' === cadao_kho_luu( 'nguon-thu', $muc ) );
kt( 'lưu lại y nguyên thì giữ nguyên', 'giu_nguyen' === cadao_kho_luu( 'nguon-thu', $muc ) );
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 102, 'doc_trang' => '2026-10-25', 'url' => 'https://example.org/bai-102/', 'loai' => 'ca_dao', 'tieu_de' => 'Bài có cờ', 'loi' => 'Dòng có cờ', 'co' => array( 'tho_tuc' ) ) );
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 103, 'doc_trang' => '2026-10-25', 'url' => 'https://example.org/ve-103/', 'loai' => 've', 'tieu_de' => 'Vè mẫu', 'loi' => "Vè mẫu dòng một\nVè mẫu dòng hai" ) );
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 104, 'doc_trang' => '2026-10-25', 'url' => 'https://example.org/dd-104/', 'loai' => 'dong_dao', 'tieu_de' => 'Đồng dao mẫu', 'loi' => 'Đồng dao mẫu' ) );

$so = cadao_kho_tao_bai_cho_duyet( cadao_nguon_lay( 'nguon-thu' ), 5 );
kt( 'tạo bài chờ duyệt: bỏ mục có cờ bỏ qua và loại không lấy', 2 === $so, $so );
$bai = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'pending', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
kt( 'bài ở trạng thái Chờ duyệt', 2 === count( $bai ), count( $bai ) );
$b = $bai[0];
kt( 'lời trong khối Thơ, đúng từng dòng, đã thoát ký tự HTML', str_contains( $b->post_content, "<pre class=\"wp-block-verse\">Dòng mẫu thứ nhất<br>Dòng mẫu thứ hai &lt;có dấu nhỏ hơn&gt;</pre>" ), $b->post_content );
kt( 'có phần Dị bản', str_contains( $b->post_content, 'Dị bản' ) && str_contains( $b->post_content, 'Dị bản mẫu một<br>Dị bản mẫu hai' ) );
kt( 'chú thích chép kèm "Nguồn: …" về trang gốc', str_contains( $b->post_content, 'cadao-chu-thich' ) && str_contains( $b->post_content, 'Nguồn: <a href="https://example.org/bai-101/"' ) );
kt( 'lời không ghi nguồn: bài tự động không có dòng Nguồn cuối bài (D-057)', '' === cadao_dong_nguon( $b->ID ) && '' === get_post_meta( $b->ID, 'nguon_ten', true ) );
kt( 'địa chỉ gốc giữ ở ô nội bộ', 'https://example.org/bai-101/' === get_post_meta( $b->ID, '_cadao_url_goc', true ) );
kt( 'bài không có chú thích thì không có chữ "Nguồn"', ! str_contains( $bai[1]->post_content, 'Nguồn:' ) );
kt( 'thể loại theo loại trên nguồn', has_term( 'ca-dao', 'the_loai', $b ) && has_term( 've', 'the_loai', $bai[1] ) );
kt( 'không tạo trùng bài cho cùng mục', 0 === cadao_kho_tao_bai_cho_duyet( cadao_nguon_lay( 'nguon-thu' ), 5 ) );
kt( 'trạng thái kho: 2 đã thành bài', 2 === cadao_kho_dem( 'nguon-thu' )['da_tao_bai'] );

// Công tắc chép chú thích tắt → không chép
$n                   = cadao_nguon_lay( 'nguon-thu' );
$n['chep_chu_thich'] = false;
cadao_nguon_luu( $n );
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 105, 'doc_trang' => '2026-10-25', 'url' => 'https://example.org/bai-105/', 'loai' => 'ca_dao', 'tieu_de' => 'Không chép', 'loi' => 'Dòng không chép', 'chu_thich' => array( array( 'tu' => 'x', 'noi_dung' => 'y' ) ) ) );
cadao_kho_tao_bai_cho_duyet( cadao_nguon_lay( 'nguon-thu' ), 1 );
$k = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'pending', 'title' => 'Không chép', 'numberposts' => 1 ) );
kt( 'tắt "Chép chú thích" thì không chép', $k && ! str_contains( $k[0]->post_content, 'cadao-chu-thich' ) );

// Chép chú thích nhưng tắt "Ghi nguồn cho chú thích"
$n['chep_chu_thich'] = true;
$n['ghi_cong']       = false;
cadao_nguon_luu( $n );
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 106, 'doc_trang' => '2026-10-25', 'url' => 'https://example.org/bai-106/', 'loai' => 'ca_dao', 'tieu_de' => 'Không ghi nguồn', 'loi' => 'Dòng không ghi nguồn', 'chu_thich' => array( array( 'tu' => 'x', 'noi_dung' => 'y' ) ) ) );
cadao_kho_tao_bai_cho_duyet( cadao_nguon_lay( 'nguon-thu' ), 1 );
$k = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'pending', 'title' => 'Không ghi nguồn', 'numberposts' => 1 ) );
kt( 'tắt "Ghi nguồn cho chú thích": vẫn chép chú thích, không có dòng Nguồn', $k && str_contains( $k[0]->post_content, 'cadao-chu-thich' ) && ! str_contains( $k[0]->post_content, 'Nguồn:' ) );

// Nâng cấp 0.2.0: bỏ nguồn máy tự điền ở bài cũ, giữ nguồn người duyệt đã sửa
$cu  = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Bài cũ tự động', 'meta_input' => array( '_cadao_nguon_id' => 'nguon-thu', 'nguon_ten' => 'Nguồn thử', 'nguon_url' => 'https://example.org/bai-cu/', 'ngay_truy_cap' => '2026-10-24' ) ) );
$sua = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Bài cũ đã sửa nguồn', 'meta_input' => array( '_cadao_nguon_id' => 'nguon-thu', 'nguon_ten' => 'Sách ABC', 'nguon_url' => 'https://example.org/sach/' ) ) );
cadao_bo_nguon_tu_dien();
kt( 'nâng cấp: bỏ dòng Nguồn máy tự điền, giữ địa chỉ gốc nội bộ', '' === cadao_dong_nguon( $cu ) && 'https://example.org/bai-cu/' === get_post_meta( $cu, '_cadao_url_goc', true ) );
kt( 'nâng cấp: giữ nguồn người duyệt đã sửa', 'Sách ABC' === get_post_meta( $sua, 'nguon_ten', true ) );

// ---------- Dòng "Nguồn: …" (bài nhập tay, tùy chọn) và noindex ----------
kt( 'dòng Nguồn có liên kết khi người viết điền', str_contains( cadao_dong_nguon( $sua ), 'Nguồn: <a href="https://example.org/sach/"' ), cadao_dong_nguon( $sua ) );
$tay = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Bài nhập tay', 'post_content' => cadao_khoi_tho( 'Dòng nhập tay' ) ) );
kt( 'bài nhập tay không nguồn: đăng được, không có dòng Nguồn', ! is_wp_error( $tay ) && 'publish' === get_post_status( $tay ) && '' === cadao_dong_nguon( $tay ) );
kt( 'chưa chọn thể loại: đường dẫn /ca-dao/…', str_contains( get_permalink( $tay ), '/ca-dao/bai-nhap-tay/' ), get_permalink( $tay ) );
wp_set_object_terms( $tay, 've', 'the_loai' );
clean_post_cache( $tay );
kt( 'đường dẫn theo thể loại: /ve/…', str_contains( get_permalink( $tay ), '/ve/bai-nhap-tay/' ), get_permalink( $tay ) );
kt( 'đường dẫn /ve/… dẫn tới đúng bài (quy tắc đứng trước quy tắc tệp đính kèm)', $tay === url_to_postid( get_permalink( $tay ) ), url_to_postid( get_permalink( $tay ) ) );
kt( 'đường dẫn gốc /bai/… vẫn dẫn tới bài', $tay === url_to_postid( home_url( '/bai/bai-nhap-tay/' ) ) );
kt( 'chưa có Hiểu nhanh thì noindex', ! cadao_co_gia_tri_rieng( get_post( $tay ) ) );
wp_update_post( array( 'ID' => $tay, 'post_excerpt' => 'Hiểu nhanh mẫu.' ) );
kt( 'có Hiểu nhanh thì được đánh chỉ mục', cadao_co_gia_tri_rieng( get_post( $tay ) ) );


// ---------- T-073: đọc trang HTML (dị bản, chú thích, câu đố, các loại khác) trên một nguồn giả ----------
$G = 'https://mau.example';
$tr_cd1 = '<article id="post-1" class="post"><h1 class="post-title entry-title"><a href="' . $G . '/cd-1/">Dòng một mẫu</a></h1>'
	. '<div class="entry-content"><p>Dòng một mẫu<a class="footnote" data-ref-id="71" href="#note-71">1</a><br>Dòng hai mẫu</p></div>'
	. '<section class="variants"><ul><li class="variant"><p>Dị một mẫu<br>Dị hai mẫu<a class="footnote" data-ref-id="72">2</a></p></li></ul></section>'
	. '<aside class="post-data"><ul class="post-categories"><li><a href="#">Chủ đề mẫu</a></li></ul></aside></article>'
	. '<article id="post-2"><h1 class="post-title"><a href="' . $G . '/cd-2/">Bài hai</a></h1><div class="entry-content"><p>Bài hai dòng một<br>Bài hai dòng hai</p></div>'
	. '<section class="variants"><ul><li class="variant"><p>Bài hai dị bản</p></li></ul></section></article>'
	. '<article id="post-3"><h1 class="post-title"><a href="' . $G . '/cd-3/">Bài ba</a></h1><div class="entry-content"><p>Bài ba dòng một…</p></div></article>'
	. '<section class="ref"><ol><li data-ref-id="71" id="note-71"><dl><dt>từ mẫu</dt><dd><p>Nghĩa &amp; mẫu.</p></dd></dl></li></ol></section>';
$tr_cd3 = '<article id="post-3"><h1 class="post-title"><a href="' . $G . '/cd-3/">Bài ba</a></h1><div class="entry-content"><p>Bài ba dòng một<br>Bài ba dòng hai</p></div></article>';
$tr_ve  = '<article id="post-50"><h1 class="post-title"><a href="' . $G . '/ve/ve-mau/">Vè mẫu</a></h1><div class="entry-content"><p>Vè mẫu câu một<br>Vè mẫu câu hai</p></div></article>';
$tr_do  = '<article id="post-60"><h1 class="post-title"><a href="' . $G . '/cau-do/do-mau/">Đố mẫu</a></h1><div class="entry-content"><p>Mình tròn mẫu<br>Đố là gì mẫu</p>'
	. '<div class="q">Là cái gì?</div><div class="a"><p>Cái mẫu</p></div></div></article>';
$api    = wp_json_encode(
	array(
		array( 'id' => 1, 'link' => "$G/cd-1/", 'title' => array( 'rendered' => 'Dòng một mẫu' ), 'content' => array( 'rendered' => '<p>Dòng một mẫu<a class="footnote" data-ref-id="71">1</a><br>Dòng hai mẫu</p>' ), 'categories' => array( 5 ), 'modified' => '2026-01-01T00:00:00' ),
		array( 'id' => 2, 'link' => "$G/cd-2/", 'title' => array( 'rendered' => 'Bài hai' ), 'content' => array( 'rendered' => '<p>Bài hai dòng một<br>Bài hai dòng hai</p>' ), 'categories' => array(), 'modified' => '2026-01-01T00:00:00' ),
		array( 'id' => 3, 'link' => "$G/cd-3/", 'title' => array( 'rendered' => 'Bài ba' ), 'content' => array( 'rendered' => '<p>Bài ba dòng một<br>Bài ba dòng hai</p>' ), 'categories' => array(), 'modified' => '2026-01-01T00:00:00' ),
	)
);
$trang_gia = array(
	"$G/wp-json/wp/v2/categories?per_page=100&_fields=id,name" => wp_json_encode( array( array( 'id' => 5, 'name' => 'Chủ đề mẫu' ) ) ),
	"$G/wp-json/wp/v2/posts?per_page=100&page=1&orderby=id&order=asc&_fields=id,link,title,content,categories,modified" => $api,
	"$G/ve-sitemap.xml"           => "<urlset><url><loc>$G/ve/ve-mau/</loc><lastmod>2026-01-02T00:00:00+00:00</lastmod></url><url><loc>$G/ve/</loc></url></urlset>",
	"$G/cau-do-sitemap.xml"       => "<urlset><url><loc>$G/cau-do/do-mau/</loc></url></urlset>",
	"$G/cd-1/"                    => $tr_cd1,
	"$G/cd-3/"                    => $tr_cd3,
	"$G/ve/ve-mau/"               => $tr_ve,
	"$G/cau-do/do-mau/"           => $tr_do,
	"$G/chu-thich/chi-tiet/72"    => '<li data-ref-id="72" id="note-72"><dl><dt></dt><dd>Chú thích chi tiết mẫu.</dd></dl></li>',
);
$GLOBALS['cadao_da_hoi'] = array();
add_filter( 'cadao_nhip_giay', '__return_zero' );
add_filter(
	'pre_http_request',
	static function ( $tra, $tham, $url ) use ( $trang_gia ) {
		$GLOBALS['cadao_da_hoi'][] = $url;
		$co = array_key_exists( $url, $trang_gia );
		return array( 'headers' => array( 'x-wp-totalpages' => '1' ), 'body' => $co ? $trang_gia[ $url ] : '', 'response' => array( 'code' => $co ? 200 : 404, 'message' => '' ), 'cookies' => array(), 'filename' => null );
	},
	10,
	3
);
cadao_nguon_luu( array( 'id' => 'me-thu', 'ten' => 'Mẹ thử', 'url' => $G, 'dong_ghi_nguon' => 'Mẹ thử', 'bat' => true, 'ghi_cong' => true ) + cadao_nguon_mac_dinh() );
$kq = cadao_nguon_chay( 'me-thu', 60 );
$k  = static function ( $url ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_kho_bang() . ' WHERE nguon_id = %s AND url = %s', 'me-thu', $url ) );
};
$d  = static fn( $h ) => $h ? ( json_decode( $h->du_lieu, true ) ?: array() ) : array();
$h1 = $k( "$G/cd-1/" );
kt( 'đọc trang: lời ca dao vẫn là lời API, dị bản lấy từ trang', $h1 && "Dòng một mẫu\nDòng hai mẫu" === $h1->loi && array( "Dị một mẫu\nDị hai mẫu" ) === $d( $h1 )['di_ban'], $h1 );
kt( 'đọc trang: chú thích theo thứ tự, giải mã HTML; chú thích thiếu lấy ở trang chi tiết', array( array( 'ma' => 71, 'tu' => 'từ mẫu', 'noi_dung' => 'Nghĩa & mẫu.' ), array( 'ma' => 72, 'tu' => '', 'noi_dung' => 'Chú thích chi tiết mẫu.' ) ) === $d( $h1 )['chu_thich'], $d( $h1 )['chu_thich'] ?? null );
kt( 'đọc trang: đánh dấu đã đọc', $h1 && 1 === preg_match( '/^\d{4}-\d{2}-\d{2}$/', $h1->doc_trang ) );
$h2 = $k( "$G/cd-2/" );
kt( 'bài liên quan đủ lời: nhận dị bản ngay, không tải trang riêng', $h2 && array( 'Bài hai dị bản' ) === $d( $h2 )['di_ban'] && ! in_array( "$G/cd-2/", $GLOBALS['cadao_da_hoi'], true ) );
$h3 = $k( "$G/cd-3/" );
kt( 'bài liên quan chỉ là đoạn trích: không nhận, tải trang riêng', $h3 && in_array( "$G/cd-3/", $GLOBALS['cadao_da_hoi'], true ) && "Bài ba dòng một\nBài ba dòng hai" === $h3->loi );
$hv = $k( "$G/ve/ve-mau/" );
kt( 'vè lấy qua sitemap: lời và tiêu đề từ trang, mã gốc là đường dẫn', $hv && 've' === $hv->loai && "Vè mẫu câu một\nVè mẫu câu hai" === $hv->loi && 'Vè mẫu' === $hv->tieu_de && '/ve/ve-mau' === $hv->ma_goc, $hv );
kt( 'sitemap: bỏ trang danh mục', null === $k( "$G/ve/" ) );
$hd = $k( "$G/cau-do/do-mau/" );
kt( 'câu đố: câu hỏi và đáp án tách khỏi lời', $hd && "Mình tròn mẫu\nĐố là gì mẫu" === $hd->loi && 'Là cái gì?' === $d( $hd )['cau_hoi'] && 'Cái mẫu' === $d( $hd )['dap_an'], $hd );
kt( 'chạy lại khi không có gì mới: không đọc lại trang', 0 === cadao_nguon_chay( 'me-thu', 60 )['trang'] );

$so = cadao_kho_tao_bai_cho_duyet( cadao_nguon_lay( 'me-thu' ), 10 );
kt( 'tạo bài chờ duyệt từ mọi loại đã đọc trang', 5 === $so, $so );
$bai_cd1 = get_post( (int) $k( "$G/cd-1/" )->post_id );
kt( 'bài ca dao có Dị bản, Chú thích và một dòng "Nguồn: …" dưới chú thích', str_contains( $bai_cd1->post_content, 'Dị một mẫu<br>Dị hai mẫu' ) && str_contains( $bai_cd1->post_content, 'Nghĩa &amp; mẫu.' ) && 1 === substr_count( $bai_cd1->post_content, 'Nguồn: <a href="' . $G . '/cd-1/"' ) );
$bai_cd2 = get_post( (int) $k( "$G/cd-2/" )->post_id );
kt( 'bài chỉ có dị bản (không chép gì của nguồn): không có "Nguồn"', str_contains( $bai_cd2->post_content, 'Bài hai dị bản' ) && ! str_contains( $bai_cd2->post_content, 'Nguồn:' ) );
$bai_do = get_post( (int) $k( "$G/cau-do/do-mau/" )->post_id );
kt( 'câu đố: đáp án (lời giải) hiện sẵn, kèm "Nguồn: …" (D-058)', str_contains( $bai_do->post_content, '<details class="wp-block-details" open><summary>Đáp án</summary>' ) && str_contains( $bai_do->post_content, 'Cái mẫu' ) && str_contains( $bai_do->post_content, 'Nguồn:' ) && has_term( 'cau-do', 'the_loai', $bai_do ) );
kt( 'vè vào thể loại Vè', has_term( 've', 'the_loai', (int) $k( "$G/ve/ve-mau/" )->post_id ) );

// Bài chưa ai sửa: kho đổi thì bài tự cập nhật; bài đã sửa tay giữ nguyên
cadao_kho_luu( 'me-thu', array( 'ma_goc' => 2, 'di_ban' => array( 'Bài hai dị bản', 'Bài hai dị bản mới' ) ) );
kt( 'bài chưa sửa tay: tự thêm dị bản mới từ kho', str_contains( get_post( $bai_cd2->ID )->post_content, 'Bài hai dị bản mới' ) );
wp_update_post( array( 'ID' => $bai_cd2->ID, 'post_content' => get_post( $bai_cd2->ID )->post_content . "\n<!-- wp:paragraph --><p>Người duyệt viết thêm.</p><!-- /wp:paragraph -->" ) );
cadao_kho_luu( 'me-thu', array( 'ma_goc' => 2, 'di_ban' => array( 'Bài hai dị bản' ) ) );
kt( 'bài đã sửa tay: không ghi đè', str_contains( get_post( $bai_cd2->ID )->post_content, 'Người duyệt viết thêm.' ) && str_contains( get_post( $bai_cd2->ID )->post_content, 'Bài hai dị bản mới' ) );
// Nguồn sửa bài (ngày sửa đổi): đánh dấu đọc lại trang
cadao_kho_luu( 'me-thu', array( 'ma_goc' => 3, 'ngay_sua_goc' => '2026-02-01' ) );
kt( 'nguồn sửa bài: đọc lại trang ở lượt sau', '' === $k( "$G/cd-3/" )->doc_trang );
remove_all_filters( 'pre_http_request' );
remove_all_filters( 'cadao_nhip_giay' );

// ---------- Trang Kho thu thập: cột "Đã có dị bản, chú thích", bộ lọc "chỉ hiện câu đã đọc xong" ----------
kt( 'mô tả cột: chưa đọc', 'Chưa — máy sẽ đọc trang gốc' === cadao_mo_ta_doc_trang( '', array() ) );
kt( 'mô tả cột: đã đọc, có dị bản và chú thích', 'Rồi · 1 dị bản, 2 chú thích' === cadao_mo_ta_doc_trang( '2026-10-25', array( 'di_ban' => array( 'a' ), 'chu_thich' => array( 1, 2 ) ) ) );
kt( 'mô tả cột: đã đọc, nguồn không có gì thêm', 'Rồi · nguồn không có dị bản, chú thích' === cadao_mo_ta_doc_trang( '2026-10-25', array() ) );
kt( 'mô tả cột: không đọc được', 'Không đọc được trang gốc' === cadao_mo_ta_doc_trang( 'loi', array() ) );
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 301, 'url' => 'https://example.org/chua-doc/', 'loai' => 'ca_dao', 'tieu_de' => 'Câu chưa đọc trang', 'loi' => 'Câu chưa đọc trang' ) );
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 302, 'doc_trang' => '2026-10-25', 'url' => 'https://example.org/da-doc/', 'loai' => 'ca_dao', 'tieu_de' => 'Câu đã đọc trang', 'loi' => 'Câu đã đọc trang', 'di_ban' => array( 'x' ) ) );
require_once ABSPATH . 'wp-admin/includes/template.php'; // submit_button() của trang quản trị
$trang_kho = static function ( array $get ) {
	$_GET = $get;
	ob_start();
	cadao_trang_kho();
	return ob_get_clean();
};
$tat_ca = $trang_kho( array() );
$loc    = $trang_kho( array( 'da_doc' => '1' ) );
kt( 'trang Kho có cột "Đã có dị bản, chú thích"', str_contains( $tat_ca, 'Đã có dị bản, chú thích' ) && str_contains( $tat_ca, 'Rồi · 1 dị bản' ) && str_contains( $tat_ca, 'Chưa — máy sẽ đọc trang gốc' ) );
kt( 'bộ lọc "chỉ hiện câu đã đọc xong": ẩn câu chưa đọc', str_contains( $loc, 'Câu đã đọc trang' ) && ! str_contains( $loc, 'Câu chưa đọc trang' ) );
$_GET = array();

// ---------- Trích dẫn, chia sẻ (T-042) ----------
$td = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Dòng trích mẫu', 'post_content' => cadao_khoi_tho( "Dòng trích mẫu & <một>\nDòng trích mẫu hai" ) ) );
wp_set_object_terms( $td, 'tuc-ngu', 'the_loai' );
clean_post_cache( $td );
kt( 'lời của bài: khối Thơ đầu tiên, đúng từng dòng, giải mã HTML', "Dòng trích mẫu & <một>\nDòng trích mẫu hai" === cadao_loi_cua_bai( get_post( $td ) ), cadao_loi_cua_bai( get_post( $td ) ) );
$mau = cadao_mau_trich_dan( get_post( $td ) );
kt( 'trích dẫn thông dụng: tên bài, thể loại, tên dự án, địa chỉ trang, ngày truy cập', '"Dòng trích mẫu" (Tục ngữ – Thành ngữ). Kho tàng Ca dao Tục ngữ Việt Nam. Truy cập tại: ' . get_permalink( $td ) . ', ngày {ngay}.' === $mau['thong_dung'], $mau );
kt( 'trích dẫn APA 7', 'Kho tàng Ca dao Tục ngữ Việt Nam. (' . get_the_date( 'Y', $td ) . '). Dòng trích mẫu [Tục ngữ – Thành ngữ]. ' . get_permalink( $td ) === $mau['apa'], $mau );
$hd = cadao_khoi_hanh_dong( get_post( $td ) );
kt( 'khối Trích dẫn · Chia sẻ: hai kiểu, hai nút sao chép, nút Chia sẻ', str_contains( $hd, '<summary><span aria-hidden="true">❝</span> Trích dẫn</summary>' ) && str_contains( $hd, 'APA 7' ) && str_contains( $hd, 'Sao chép kèm nguồn' ) && str_contains( $hd, 'Chỉ sao chép lời' ) && str_contains( $hd, '>Chia sẻ</button>' ), $hd );
kt( 'lời mang sẵn trong khối (đã thoát HTML)', str_contains( $hd, 'data-loi="Dòng trích mẫu &amp; &lt;một&gt;' ) );
kt( 'tắt JavaScript: các nút ẩn, chữ trích dẫn vẫn hiện', str_contains( $hd, '<p class="cadao-nut cadao-can-js" hidden>' ) && str_contains( $hd, 'class="cadao-chia-se cadao-can-js" hidden' ) && str_contains( $hd, '<span class="cadao-mau-chu">&quot;Dòng trích mẫu&quot; (Tục ngữ)' ) );
kt( 'lời không ghi nguồn ngoài (D-057); bài không có phần chép thì không có ô "Kèm…"', ! str_contains( $hd, 'Nguồn:' ) && ! str_contains( $hd, 'cadao-kem' ) );
kt( 'bài có chú thích chép từ nguồn: có ô "Kèm chú thích"', str_contains( cadao_khoi_hanh_dong( get_post( $bai_cd1->ID ) ), 'Kèm chú thích' ) );
kt( 'câu đố có đáp án, không chú thích: ô "Kèm đáp án"', str_contains( cadao_khoi_hanh_dong( get_post( $bai_do->ID ) ), 'Kèm đáp án</label>' ) );
kt( 'bài không có khối Thơ: không có khối Trích dẫn', '' === cadao_khoi_hanh_dong( get_post( wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Không thơ', 'post_content' => '<p>Chỉ đoạn văn.</p>' ) ) ) ) );
kt( 'chèn khối trước Dị bản / Chú thích', '<pre>l</pre>[K]<h2 class="wp-block-heading">Dị bản</h2>' === cadao_chen_hanh_dong( '<pre>l</pre><h2 class="wp-block-heading">Dị bản</h2>', '[K]' ) );
kt( 'chèn khối trước dòng Nguồn cuối bài, hoặc ở cuối', '<pre>l</pre>[K]<p class="cadao-nguon">Nguồn</p>' === cadao_chen_hanh_dong( '<pre>l</pre><p class="cadao-nguon">Nguồn</p>', '[K]' ) && '<pre>l</pre>[K]' === cadao_chen_hanh_dong( '<pre>l</pre>', '[K]' ) );

// ---------- Lượt đọc (T-081) ----------
kt( 'bảng lượt đọc đã tạo', (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', cadao_luot_doc_bang() ) ) );
$gui = static function ( int $id, string $ua = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Mobile Safari/537.36', string $tu = '' ) {
	$yc = new WP_REST_Request( 'POST', '/cadao/v1/luot-doc/' . $id );
	$yc->set_header( 'user-agent', $ua );
	$yc->set_header( 'content-type', 'application/json' );
	$yc->set_body( wp_json_encode( array( 'tu' => $tu ) ) );
	return rest_do_request( $yc );
};
$_SERVER['REMOTE_ADDR'] = '198.51.100.1';
$r = $gui( $td, 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Mobile Safari/537.36', 'www.zalo.me' );
kt( 'gửi một lượt đọc: được tính', 200 === $r->get_status() && true === ( $r->get_data()['tinh'] ?? null ) && 1 === cadao_luot_doc_tong( $td ), array( $r->get_status(), $r->get_data() ) );
kt( 'trang giới thiệu: chỉ tên miền', array( 'zalo.me' => 1 ) === cadao_gioi_thieu_nhieu(), cadao_gioi_thieu_nhieu() );
$r = $gui( $td, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)' );
kt( 'máy dò tự khai tên (Googlebot) không được tính', true !== ( $r->get_data()['tinh'] ?? null ) && 1 === cadao_luot_doc_tong( $td ) );
kt( 'cùng máy gửi lại trong ngày: máy chủ không tính thêm (T-118)', true !== ( $gui( $td )->get_data()['tinh'] ?? null ) && 1 === cadao_luot_doc_tong( $td ) );
$_SERVER['HTTP_CF_CONNECTING_IP'] = '192.0.2.77';
kt( 'gửi thẳng tới máy chủ, tự đặt header Cloudflare: vẫn là cùng máy, không tính thêm', true !== ( $gui( $td )->get_data()['tinh'] ?? null ) && 1 === cadao_luot_doc_tong( $td ) );
$_SERVER['HTTP_CF_CONNECTING_IP'] = '192.0.2.1';
$_SERVER['REMOTE_ADDR']           = '203.0.113.5';
$ip_a                             = cadao_ip_nguoi_gui();
$_SERVER['REMOTE_ADDR']           = '173.245.48.10';
$ip_b                             = cadao_ip_nguoi_gui();
$_SERVER['REMOTE_ADDR']           = '2606:4700:10::1';
$ip_c                             = cadao_ip_nguoi_gui();
$_SERVER['HTTP_CF_CONNECTING_IP'] = 'không phải IP';
$ip_d                             = cadao_ip_nguoi_gui();
kt( 'IP người gửi: chỉ tin CF-Connecting-IP khi yêu cầu đến từ dải Cloudflare (IPv4, IPv6); header lạ thì bỏ', array( '203.0.113.5', '192.0.2.1', '192.0.2.1', '2606:4700:10::1' ) === array( $ip_a, $ip_b, $ip_c, $ip_d ), array( $ip_a, $ip_b, $ip_c, $ip_d ) );
kt( 'dải CIDR: đúng biên, không lẫn IPv4 với IPv6', cadao_ip_trong_dai( '104.23.255.255', '104.16.0.0/13' ) && ! cadao_ip_trong_dai( '104.24.0.0', '104.16.0.0/13' ) && cadao_ip_trong_dai( '2a06:98c7::1', '2a06:98c0::/29' ) && ! cadao_ip_trong_dai( '2a06:98c8::1', '2a06:98c0::/29' ) && ! cadao_ip_trong_dai( '1.2.3.4', '2400:cb00::/32' ) && ! cadao_ip_trong_dai( 'x', '173.245.48.0/20' ) );
unset( $_SERVER['HTTP_CF_CONNECTING_IP'] );
$_SERVER['REMOTE_ADDR'] = '198.51.100.1';
kt( 'không lộ phiên bản WordPress (thẻ generator trong <head> và nguồn tin)', false === has_action( 'wp_head', 'wp_generator' ) && '' === apply_filters( 'the_generator', '<meta name="generator" content="WordPress">', 'xhtml' ) );
kt( 'trình duyệt không giao diện không được tính', cadao_la_may_do( 'Mozilla/5.0 (X11; Linux x86_64) HeadlessChrome/120.0' ) && cadao_la_may_do( '' ) );
kt( 'bài chưa đăng không nhận lượt đọc', 404 === $gui( $bai_cd1->ID )->get_status() && 0 === cadao_luot_doc_tong( $bai_cd1->ID ) );
kt( 'bài không phải Ca dao – Tục ngữ không nhận lượt đọc', 404 === $gui( wp_insert_post( array( 'post_title' => 'Bài viết thường', 'post_status' => 'publish' ) ) )->get_status() );
kt( 'trang giới thiệu: bỏ chuỗi không phải tên miền và chính site', '' === cadao_ten_mien_gioi_thieu( 'https://x.y/z' ) && '' === cadao_ten_mien_gioi_thieu( wp_parse_url( home_url(), PHP_URL_HOST ) ) && 'google.com' === cadao_ten_mien_gioi_thieu( 'WWW.Google.com' ) );
kt( 'không đặt cookie (không có lệnh setcookie nào trong plugin)', ! preg_match( '/setcookie|setrawcookie|document\.cookie/', implode( '', array_map( 'file_get_contents', array_merge( glob( CADAO_THU_MUC . '/inc/*.php' ), glob( CADAO_THU_MUC . '/assets/*.js' ) ) ) ) ) );
kt( 'chưa tới 10 lượt: không hiện số lượt đọc', '' === cadao_html_so_lieu( $td ) && ! str_contains( cadao_khoi_hanh_dong( get_post( $td ) ), 'lượt đọc' ) );
for ( $i = 0; $i < 9; $i++ ) {
	cadao_luot_doc_cong( $td );
}
kt( 'từ 10 lượt: hiện 👁 10 dưới tiêu đề (đọc được: "10 lượt đọc")', str_contains( cadao_html_so_lieu( $td ), '<span aria-hidden="true">10</span>' ) && str_contains( cadao_html_so_lieu( $td ), '>10 lượt đọc<' ) );
kt( '"Đọc nhiều tuần này" ẩn khi chưa đủ 3 bài được đọc', '' === do_shortcode( '[cadao_doc_nhieu]' ) );
cadao_luot_doc_cong( $tay );
cadao_luot_doc_cong( $sua );
cadao_luot_doc_cong( $sua );
kt( 'bài đọc nhiều: xếp theo số lượt, chỉ bài đang đăng', array( $td => 10, $sua => 2, $tay => 1 ) === cadao_luot_doc_nhieu( 7, 5 ), cadao_luot_doc_nhieu( 7, 5 ) );
$dn = do_shortcode( '[cadao_doc_nhieu]' );
kt( '"Đọc nhiều tuần này" hiện khi có từ 3 bài', str_contains( $dn, 'Đọc nhiều tuần này' ) && strpos( $dn, 'Dòng trích mẫu' ) < strpos( $dn, 'Bài cũ đã sửa nguồn' ), $dn );
kt( 'lượt đọc theo ngày: đủ 7 ngày, hôm nay có 13 lượt', 7 === count( cadao_luot_doc_theo_ngay( 7 ) ) && 13 === cadao_luot_doc_theo_ngay( 7 )[ wp_date( 'Y-m-d' ) ] );

// ---------- Chống trùng (T-109, T-110, T-111) — lời tổng hợp "chữ<số>", không phải lời thật ----------
$dong = static fn( int $tu, int $so ) => implode( ' ', array_map( static fn( $i ) => "chữ$i", range( $tu, $tu + $so - 1 ) ) );
// T-109: mức giống
$L = $dong( 1000, 6 ) . "\n" . $dong( 1006, 8 );
kt( 'y hệt: chỉ khác dấu câu, chữ hoa, khoảng trắng', 'y_het' === cadao_do_giong( $L, mb_strtoupper( str_replace( ' chữ1003', ',  chữ1003', $L ) ) . '!' )[0] );
kt( 'gần như y hệt: chỉ khác cách bỏ dấu / gõ thiếu dấu (hoà / hòa)', array( 'gan_y_het', 100 ) === array_slice( cadao_do_giong( 'Hoà ' . $L, 'Hòa ' . str_replace( 'chữ', 'chư', $L ) ), 0, 2 ) );
$L40 = implode( "\n", array( $dong( 2000, 10 ), $dong( 2010, 10 ), $dong( 2020, 10 ), $dong( 2030, 10 ) ) );
$g   = cadao_do_giong( $L40, substr( $L40, 0, strrpos( $L40, ' ' ) ) . ' chữ9999' );
kt( 'gần như y hệt: khác một chữ trong bài dài (≥ 90%)', 'gan_y_het' === $g[0] && $g[1] >= 90 && $g[1] < 100, $g );
$g = cadao_do_giong( $L, $dong( 1000, 6 ) . "\n" . $dong( 1006, 4 ) . ' ' . $dong( 5000, 4 ) );
kt( 'có thể là dị bản: giống 50–90%', 'di_ban' === $g[0] && $g[1] >= 50 && $g[1] < 90, $g );
kt( 'câu ngắn nằm trong bài dài: chỉ là "liên quan"', 'lien_quan' === cadao_do_giong( $dong( 2000, 8 ), $L40 )[0] );
kt( 'hai bài khác nhau chung dòng đầu: khác', 'khac' === cadao_do_giong( $dong( 4000, 4 ) . "\n" . $dong( 4004, 8 ), $dong( 4000, 4 ) . "\n" . $dong( 4100, 8 ) )[0] );
kt( 'câu một dòng giống 64%: không tính là dị bản', 'khac' === cadao_do_giong( $dong( 3000, 10 ), $dong( 3000, 8 ) . ' ' . $dong( 3100, 2 ) )[0] );
kt( 'câu ngắn (dưới 6 âm tiết) khác một chữ: khác', 'khac' === cadao_do_giong( $dong( 6000, 4 ), $dong( 6000, 3 ) . ' chữ6999' )[0] );
kt( 'trùng một dị bản đã ghi: có thể là dị bản', 'di_ban' === cadao_do_giong( $L, $L, true )[0] );

// T-110: hai nguồn giả cùng một câu
wp_set_current_user( cadao_quan_tri_dau_tien() );
foreach ( array( 'nguon-a' => 'Nguồn A', 'nguon-b' => 'Nguồn B' ) as $id_n => $ten_n ) {
	cadao_nguon_luu( array( 'id' => $id_n, 'ten' => $ten_n, 'url' => "https://$id_n.example", 'dong_ghi_nguon' => $ten_n, 'loai_lay' => array( 'ca_dao', 'cau_do' ), 'bo_qua_co' => array(), 'ghi_cong' => true ) + cadao_nguon_mac_dinh() );
}
$kho = static function ( string $n, string $ma, string $loi, array $them = array() ): int {
	global $wpdb;
	cadao_kho_luu( $n, array_merge( array( 'ma_goc' => $ma, 'doc_trang' => '2026-10-25', 'url' => "https://$n.example/$ma/", 'loai' => 'ca_dao', 'tieu_de' => 'Câu thử ' . $ma, 'loi' => $loi ), $them ) );
	return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . cadao_kho_bang() . ' WHERE nguon_id = %s AND ma_goc = %s', $n, $ma ) );
};
$hang = static fn( int $id ) => $GLOBALS['wpdb']->get_row( $GLOBALS['wpdb']->prepare( 'SELECT * FROM ' . cadao_kho_bang() . ' WHERE id = %d', $id ) );
$V  = $dong( 1000, 6 ) . "\n" . $dong( 7000, 8 );
$a1 = $kho( 'nguon-a', 'a1', $L, array( 'di_ban' => array( $V ), 'chu_thich' => array( array( 'tu' => 'chữ1000', 'noi_dung' => 'Chú thích của nguồn A.' ) ) ) );
cadao_kho_tao_bai_cho_duyet( cadao_nguon_lay( 'nguon-a' ), 5 );
$P = (int) $hang( $a1 )->post_id;
kt( 'câu đầu tiên thành bài như thường', $P > 0 );
$b1 = $kho( 'nguon-b', 'b1', mb_strtoupper( $L ) . '.', array( 'di_ban' => array( $dong( 1000, 6 ) . "\n" . $dong( 7100, 8 ) ), 'chu_thich' => array( array( 'tu' => '', 'noi_dung' => 'Chú thích của nguồn B.' ) ) ) );
kt( 'nguồn khác, lời y hệt, cùng thể loại: gắn vào bài có sẵn ("Đã gộp")', 'da_gop' === $hang( $b1 )->trang_thai && "bai:$P" === $hang( $b1 )->gop_vao, $hang( $b1 ) );
$nd = get_post( $P )->post_content;
kt( 'bài (chưa sửa tay) nhận dị bản, chú thích của nguồn kia, mỗi nguồn một dòng "Nguồn"', str_contains( $nd, 'Chú thích của nguồn B.' ) && str_contains( $nd, 'chữ7100' ) && str_contains( $nd, 'Nguồn: <a href="https://nguon-a.example/a1/"' ) && str_contains( $nd, 'Nguồn: <a href="https://nguon-b.example/b1/"' ), $nd );
kt( 'lời chính của bài giữ nguyên (lời của nguồn đầu)', $L === cadao_loi_cac_khoi_tho( $nd )[0] );
$b2 = $kho( 'nguon-b', 'b2', str_replace( 'chữ', 'chư', $L ) );
kt( 'chỉ khác cách bỏ dấu: cũng gắn vào bài có sẵn', 'da_gop' === $hang( $b2 )->trang_thai );
$b3 = $kho( 'nguon-b', 'b3', $dong( 1000, 6 ) . "\n" . $dong( 1006, 4 ) . ' ' . $dong( 5000, 4 ) );
kt( 'có thể là dị bản: vào hàng "Có thể trùng", không tự gộp', 'moi' === $hang( $b3 )->trang_thai && cadao_trung_dang_cho( $b3 ) );
$b4 = $kho( 'nguon-b', 'b4', $L, array( 'loai' => 'cau_do' ) );
$r4 = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_trung_bang() . ' WHERE a = %s', "kho:$b4" ) );
kt( 'cùng lời, khác thể loại: vào hàng chờ để chọn', 'moi' === $hang( $b4 )->trang_thai && $r4 && 'y_het' === $r4->muc && str_contains( $r4->ly_do, 'khác thể loại' ), $r4 );
$b5 = $kho( 'nguon-b', 'b5', $V );
$r5 = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_trung_bang() . ' WHERE a = %s', "kho:$b5" ) );
kt( 'trùng 100% một dị bản đã ghi: tự gắn làm dị bản của bài, không vào hàng chờ (D-074)', 'da_gop' === $hang( $b5 )->trang_thai && "bai:$P" === $hang( $b5 )->gop_vao && 'di_ban' === $hang( $b5 )->gop_lam && ! cadao_trung_dang_cho( $b5 ) && ( ! $r5 || 'da_gop' === $r5->trang_thai ), $hang( $b5 ) );
kt( 'gắn làm dị bản: bài không có dị bản lặp lại', 1 === count( array_keys( array_map( 'cadao_chuan_loi', cadao_loi_cac_khoi_tho( get_post( $P )->post_content ) ), cadao_chuan_loi( $V ), true ) ) );
$b6 = $kho( 'nguon-b', 'b6', str_replace( 'chữ', 'chư', $V ), array( 'loai' => 'cau_do' ) );
kt( 'trùng một dị bản đã ghi, khác cách bỏ dấu, khác thể loại: cũng tự gắn (D-074)', 'da_gop' === $hang( $b6 )->trang_thai && "bai:$P" === $hang( $b6 )->gop_vao );
kt( 'máy tạo bài hằng ngày bỏ qua câu đang chờ quyết', 0 === cadao_kho_tao_bai_cho_duyet( cadao_nguon_lay( 'nguon-b' ), 10 ) );
$_GET = array( 'tab' => 'cho' );
ob_start();
cadao_trang_trung();
$tr = ob_get_clean();
kt( 'trang "Có thể trùng": cặp, mức giống, các nút quyết', str_contains( $tr, 'Có thể là dị bản' ) && str_contains( $tr, 'Gộp làm dị bản' ) && str_contains( $tr, 'Là bài khác' ) && str_contains( $tr, 'Để sau' ) && str_contains( $tr, 'khác thể loại' ), $tr );
$_GET = array();
kt( '"Gộp làm dị bản": lời câu mới thành dị bản của bài, đúng từng ký tự', cadao_trung_gop( "kho:$b3", "bai:$P", 'di_ban' ) && in_array( $hang( $b3 )->loi, cadao_loi_cac_khoi_tho( get_post( $P )->post_content ), true ) );
cadao_trung_dat( "kho:$b4", "bai:$P", 'khac' );
kt( '"Là bài khác": so lại không báo nữa', 'khong' === cadao_trung_kiem_kho( $b4 ) && ! cadao_trung_dang_cho( $b4 ) );
kt( '"Tách ra": câu đã gộp thành câu mới, bài bỏ phần của nguồn đó', cadao_trung_tach( $b1 ) && 'moi' === $hang( $b1 )->trang_thai && ! str_contains( get_post( $P )->post_content, 'Chú thích của nguồn B.' ) );
kt( 'tách ra rồi so lại không tự gộp nữa (đã ghi là bài khác)', 'da_gop' !== ( cadao_trung_kiem_kho( $b1 ) ) && 'moi' === $hang( $b1 )->trang_thai );
cadao_trung_kiem_kho( $b4 );
kt( 'một cặp chỉ ghi một lần (không ghi thêm chiều ngược lại); câu bên kia không bị giữ lại', ! $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . cadao_trung_bang() . ' WHERE a = %s AND b = %s', "kho:$b4", "kho:$b1" ) ) && ! cadao_trung_dang_cho( $b4 ) );
kt( '"Tách ra" câu tự gắn làm dị bản: thành câu mới, so lại không tự gắn nữa', cadao_trung_tach( $b6 ) && 'moi' === $hang( $b6 )->trang_thai && 'da_gop' !== cadao_trung_kiem_kho( $b6 ) );
// D-074 một lần sau nâng cấp: cặp cũ "trùng một dị bản đã ghi" đang chờ thì so lại để tự gắn; cặp khác của câu đó rời hàng chờ
$b7 = $kho( 'nguon-b', 'b7', $dong( 9900, 6 ) . "\n" . $dong( 9906, 8 ) );
$wpdb->update( cadao_kho_bang(), array( 'loi' => mb_strtoupper( $hang( $b3 )->loi ) ), array( 'id' => $b7 ) ); // lời = dị bản b3 đã gộp vào bài
cadao_trung_ghi_cho( "kho:$b7", "bai:$P", 'di_ban', 100, 'trùng một dị bản đã ghi' );
cadao_trung_ghi_cho( "kho:$b7", "kho:$b3", 'di_ban', 60, '' );
delete_option( 'cadao_trung_tu_gan' );
$tu_gan = cadao_trung_tu_gan_di_ban_cu();
$tt_b7  = $wpdb->get_col( $wpdb->prepare( 'SELECT trang_thai FROM ' . cadao_trung_bang() . ' WHERE a = %s ORDER BY id', "kho:$b7" ) );
kt( 'nâng cấp: cặp cũ trùng một dị bản đã ghi được tự gắn, cặp khác của câu rời hàng chờ, chỉ chạy một lần', 1 === $tu_gan && 'da_gop' === $hang( $b7 )->trang_thai && array( 'da_gop', 'bo' ) === $tt_b7 && 1 === (int) get_option( 'cadao_trung_tu_gan' ), array( $tu_gan, $tt_b7 ) );

// Bài viết tay: câu kho y hệt gắn vào, bài không bị sửa; "Gộp làm dị bản" chèn khối Thơ vào bài đã sửa tay
$Q = $dong( 8000, 6 ) . "\n" . $dong( 8006, 8 );
$M = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Bài viết tay thử', 'post_content' => cadao_khoi_tho( $Q ) . "\n\n" . cadao_khoi_doan( 'Người biên soạn viết.' ) ) );
$nd_m = get_post( $M )->post_content;
$c1   = $kho( 'nguon-b', 'c1', mb_strtoupper( $Q ) );
kt( 'câu kho y hệt bài viết tay: gắn vào, bài không đổi', 'da_gop' === $hang( $c1 )->trang_thai && $nd_m === get_post( $M )->post_content );
$c2 = $kho( 'nguon-b', 'c2', $dong( 8000, 6 ) . "\n" . $dong( 8006, 4 ) . ' ' . $dong( 8500, 4 ) );
cadao_trung_gop( "kho:$c2", "bai:$M", 'di_ban' );
$cac = cadao_loi_cac_khoi_tho( get_post( $M )->post_content );
kt( 'bài đã sửa tay: dị bản chèn thêm, đúng từng ký tự, phần người viết giữ nguyên', $Q === $cac[0] && $hang( $c2 )->loi === ( $cac[1] ?? null ) && str_contains( get_post( $M )->post_content, 'Người biên soạn viết.' ) && str_contains( get_post( $M )->post_content, '<h2 class="wp-block-heading">Dị bản</h2>' ) );

// T-111: trình soạn
$yc = static function ( string $duong, array $du_lieu ) {
	$r = new WP_REST_Request( 'POST', $duong );
	$r->set_header( 'content-type', 'application/json' );
	$r->set_body( wp_json_encode( $du_lieu ) );
	return rest_do_request( $r );
};
$r = $yc( '/cadao/v1/trung', array( 'loi_html' => esc_html( $dong( 8000, 6 ) ) . '<br>' . esc_html( $dong( 8006, 8 ) ), 'bai' => 0 ) );
kt( 'trình soạn: dán lời của bài đã có → báo "Trùng y hệt" bài đó', 200 === $r->get_status() && ( $r->get_data()['ket_qua'][0]['id'] ?? 0 ) === $M && 'Trùng y hệt' === ( $r->get_data()['ket_qua'][0]['nhan'] ?? '' ), $r->get_data() );
kt( 'trình soạn: lời trống thì không so', false === $yc( '/cadao/v1/trung', array( 'loi_html' => '<br>' ) )->get_data()['co_loi'] );
$W  = "Lời thêm “ngoặc kép” & <nhỏ hơn>\nDòng hai — gạch dài, chữ9";
$D  = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'draft', 'post_title' => 'Bản nháp thử', 'post_content' => cadao_khoi_tho( $W ) ) );
$r  = $yc( '/cadao/v1/them-di-ban', array( 'bai' => $D, 'dich' => $M, 'loi_html' => esc_html( "Lời thêm “ngoặc kép” & <nhỏ hơn>" ) . '<br>' . esc_html( 'Dòng hai — gạch dài, chữ9' ) ) );
kt( '"Thêm làm dị bản": lời vừa gõ vào bài cũ đúng từng ký tự, bản nháp vào thùng rác', 200 === $r->get_status() && in_array( $W, cadao_loi_cac_khoi_tho( get_post( $M )->post_content ), true ) && 'trash' === get_post_status( $D ), $r->get_data() );
$E = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'draft', 'post_title' => 'Bài trùng viết tay', 'post_content' => cadao_khoi_tho( $Q ) ) );
kt( 'bài trùng bài khác: đăng được (không chặn), danh sách có nhãn "Có thể trùng"', ! is_wp_error( $E ) && isset( apply_filters( 'display_post_states', array(), get_post( $E ) )['cadao_trung'] ) );
$yc( '/cadao/v1/khong-trung', array( 'bai' => $E, 'cac' => array( "bai:$M" ) ) );
kt( '"Vẫn đăng bài mới": máy không báo nữa', ! isset( apply_filters( 'display_post_states', array(), get_post( $E ) )['cadao_trung'] ) && array() === array_filter( cadao_trung_cho_soan( $Q, $E ), static fn( $x ) => $x['id'] === $M ) );
wp_trash_post( $E );
kt( 'bài vào thùng rác: ra khỏi chỉ mục', ! $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . cadao_van_tay_bang() . ' WHERE muc = %s', "bai:$E" ) ) );

// Làm dần (dọn trùng sẵn có khi nâng cấp) và thống kê
update_option( 'cadao_trung_lam_dan', array( 'buoc' => 'bai', 'sau' => 0 ), false );
kt( 'làm dần: chỉ mục cho bài, kho rồi so từng câu — xong', 'xong' === cadao_trung_lam_dan( 120 )['buoc'] );
$tk = cadao_trung_thong_ke();
kt( 'thống kê (chỉ đọc): đếm được câu y hệt, chỉ khác dấu, là dị bản đã ghi', $tk['y_het'] >= 2 && $tk['chi_khac_dau'] >= 1 && $tk['la_di_ban'] >= 1, $tk );
// ---------- Một mục "Tục ngữ – Thành ngữ" (D-078) ----------
kt( 'thể loại: một mục "Tục ngữ – Thành ngữ" ở /tuc-ngu/, không còn thể loại "Thành ngữ"', 'Tục ngữ – Thành ngữ' === get_term_by( 'slug', 'tuc-ngu', 'the_loai' )->name && ! isset( CADAO_THE_LOAI['thanh-ngu'] ) && ! term_exists( 'thanh-ngu', 'the_loai' ) );
$tg = $kho( 'nguon-a', 'tg1', $dong( 9400, 4 ), array( 'loai' => 'thanh_ngu_tuc_ngu' ) );
$p_tg = (int) cadao_kho_tao_bai( $tg );
kt( 'câu thành ngữ – tục ngữ của nguồn thành bài trong mục gộp, đường dẫn /tuc-ngu/…', has_term( 'tuc-ngu', 'the_loai', $p_tg ) && str_contains( get_permalink( $p_tg ), '/tuc-ngu/' ) );
// Nâng cấp từ bản có hai thể loại: bài "Thành ngữ" (đã đăng, có thể loại khác) chuyển sang mục gộp; mô tả nối vào
wp_update_term( (int) get_term_by( 'slug', 'tuc-ngu', 'the_loai' )->term_id, 'the_loai', array( 'name' => 'Tục ngữ', 'description' => 'Giới thiệu tục ngữ thử.' ) );
$tn_cu = wp_insert_term( 'Thành ngữ', 'the_loai', array( 'slug' => 'thanh-ngu', 'description' => 'Giới thiệu thành ngữ thử.' ) );
$tn_bai = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Thành ngữ cũ thử', 'post_name' => 'thanh-ngu-cu-thu', 'post_content' => cadao_khoi_tho( $dong( 9450, 4 ) ) ) );
wp_set_object_terms( $tn_bai, array( 'thanh-ngu', 'cau-do' ), 'the_loai' );
delete_option( 'cadao_gop_tn' );
cadao_gop_tuc_ngu_thanh_ngu();
clean_post_cache( $tn_bai );
$tn_moi = get_term_by( 'slug', 'tuc-ngu', 'the_loai' );
kt( 'nâng cấp: bài "Thành ngữ" vào mục gộp, giữ thể loại khác; thể loại cũ bỏ đi; tên đổi, mô tả hai bên đều giữ', has_term( 'tuc-ngu', 'the_loai', $tn_bai ) && has_term( 'cau-do', 'the_loai', $tn_bai ) && ! term_exists( 'thanh-ngu', 'the_loai' ) && 'Tục ngữ – Thành ngữ' === $tn_moi->name && str_contains( $tn_moi->description, 'Giới thiệu tục ngữ thử.' ) && str_contains( $tn_moi->description, 'Giới thiệu thành ngữ thử.' ) && 1 === (int) get_option( 'cadao_gop_tn' ), $tn_moi );
wp_update_term( (int) $tn_moi->term_id, 'the_loai', array( 'description' => '' ) );
$tn_url = get_permalink( $tn_bai );
kt( 'đường dẫn cũ /thanh-ngu/<tên>/ vẫn mở được bài và chuyển hướng 301 sang /tuc-ngu/<tên>/', str_contains( $tn_url, '/tuc-ngu/thanh-ngu-cu-thu/' ) && $tn_bai === url_to_postid( home_url( '/thanh-ngu/thanh-ngu-cu-thu/' ) ) && $tn_url === cadao_duong_dan_dung( '/thanh-ngu/thanh-ngu-cu-thu/', get_post( $tn_bai ) ) && null === cadao_duong_dan_dung( '/tuc-ngu/thanh-ngu-cu-thu/', get_post( $tn_bai ) ), $tn_url );
kt( 'chống trùng: thể loại "Thành ngữ" cũ tính là cùng mục gộp', cadao_nhom_the_loai( "bai:$tn_bai" ) === cadao_nhom_the_loai( "bai:$p_tg" ) );
kt( 'không còn trang "Tách thành ngữ – tục ngữ", không còn hàng chờ đó trên Bàn biên tập', ! function_exists( 'cadao_trang_tach_loai' ) && ! in_array( 'tach_loai', array_column( cadao_hang_cho(), 'khoa' ), true ) && ! wp_next_scheduled( 'cadao_wiktionary_hang_tuan' ) );
wp_delete_post( $tn_bai, true );

// ---------- Chuyển bài site cũ (T-076) ----------
$cu_ds = array(
	array( 'ma' => 'cd-thu-1', 'slug' => 'bai-cu-thu-mot', 'tieu_de' => 'Bài cũ thử một', 'the_loai' => 'ca-dao', 'loi' => $dong( 9600, 6 ) . "\n" . $dong( 9606, 8 ), 'di_ban' => array(), 'nguon' => array( 'ten' => 'Sách thử', 'url' => '', 'ngay_truy_cap' => '' ), 'hieu_nhanh' => 'Nghĩa thử đã duyệt.', 'chu_thich' => array( array( 'tu' => 'chữ9600', 'noi_dung' => 'Chú giải thử.' ) ), 'ho_tro_ai' => true ),
	array( 'ma' => 'cd-thu-2', 'slug' => 'bai-cu-thu-hai', 'tieu_de' => 'Bài cũ thử hai', 'the_loai' => 'ca-dao', 'loi' => $Q, 'di_ban' => array(), 'nguon' => null, 'hieu_nhanh' => '', 'chu_thich' => array(), 'ho_tro_ai' => false ),
);
cadao_chuyen_site_cu( $cu_ds );
$p1 = cadao_site_cu_bai( 'cd-thu-1' );
$p2 = cadao_site_cu_bai( 'cd-thu-2' );
$n1 = get_post( $p1 );
kt( 'bài cũ không trùng: đăng như site cũ — lời nguyên văn, Hiểu nhanh, chú giải, nguồn đã nhập, đường dẫn cũ', 'publish' === $n1->post_status && $cu_ds[0]['loi'] === cadao_loi_cac_khoi_tho( $n1->post_content )[0] && 'Nghĩa thử đã duyệt.' === $n1->post_excerpt && str_contains( $n1->post_content, 'Chú giải thử.' ) && 'Sách thử' === get_post_meta( $p1, 'nguon_ten', true ) && str_contains( get_permalink( $p1 ), '/ca-dao/bai-cu-thu-mot/' ), $n1 );
kt( 'chú giải có hỗ trợ AI (đã duyệt): bài được đánh dấu để ghi rõ', '1' === (string) get_post_meta( $p1, '_cadao_ho_tro_ai', true ) && ! get_post_meta( $p2, '_cadao_ho_tro_ai', true ) );
$r2 = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_trung_bang() . ' WHERE a = %s AND b = %s', "bai:$p2", "bai:$M" ) );
kt( 'bài cũ trùng bài đã có: vào ở dạng Bản nháp, hiện trong "Có thể trùng"', 'draft' === get_post_status( $p2 ) && $r2 && 'cho' === $r2->trang_thai && str_contains( $r2->ly_do, 'site cũ' ), $r2 );
kt( 'chạy lại không tạo bài trùng', 'đã có' === cadao_chuyen_site_cu( $cu_ds )['cd-thu-1'] && 1 === count( get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'any', 'meta_key' => '_cadao_ma_site_cu', 'meta_value' => 'cd-thu-1', 'fields' => 'ids' ) ) ) );
kt( 'tệp site cũ đi kèm plugin đọc được', is_array( json_decode( (string) file_get_contents( CADAO_THU_MUC . '/du-lieu/site-cu.json' ), true )['bai'] ?? null ) );
wp_set_current_user( 0 );

// ---------- Theme: nhận lục bát (T-072) ----------
require_once '/repo/themes/ca-dao/functions.php';
kt( 'lục bát: cặp 6–8 âm tiết', cadao_la_luc_bat( array( 'một hai ba bốn năm sáu', 'một hai ba bốn năm sáu bảy tám', 'một hai ba bốn năm sáu', 'một hai ba bốn năm sáu bảy tám' ) ) );
kt( 'không phải lục bát: dòng 7 âm tiết', ! cadao_la_luc_bat( array( 'một hai ba bốn năm sáu bảy', 'một hai ba bốn năm sáu bảy' ) ) );
kt( 'không phải lục bát: một dòng', ! cadao_la_luc_bat( array( 'một hai ba bốn năm sáu' ) ) );
$tho = do_blocks( cadao_khoi_tho( "một hai ba bốn năm sáu\nmột hai ba bốn năm sáu bảy tám" ) );
kt( 'khối Thơ ngoài trang: mỗi dòng một span, câu lục thụt lề', str_contains( $tho, '<span class="cadao-dong cadao-luc">một hai ba bốn năm sáu</span><span class="cadao-dong">' ), $tho );

// ---------- Thể thơ (T-084) — dòng tổng hợp theo số âm tiết ----------
$am = static fn( int $n, int $tu = 0 ) => implode( ' ', array_map( static fn( $i ) => "chữ$i", range( $tu, $tu + $n - 1 ) ) );
kt( 'thể thơ: lục bát', 'luc-bat' === cadao_the_tho( implode( "\n", array( $am( 6 ), $am( 8 ), $am( 6 ), $am( 8 ) ) ) ) );
kt( 'thể thơ: song thất lục bát (7 – 7 – 6 – 8)', 'song-that-luc-bat' === cadao_the_tho( implode( "\n", array( $am( 7 ), $am( 7 ), $am( 6 ), $am( 8 ), $am( 7 ), $am( 7 ), $am( 6 ), $am( 8 ) ) ) ) );
kt( 'thể thơ: câu bốn chữ', 'bon-chu' === cadao_the_tho( implode( "\n", array_fill( 0, 6, $am( 4 ) ) ) ) );
kt( 'thể thơ: câu ba chữ, năm chữ', 'ba-chu' === cadao_the_tho( implode( "\n", array_fill( 0, 5, $am( 3 ) ) ) ) && 'nam-chu' === cadao_the_tho( implode( "\n", array_fill( 0, 5, $am( 5 ) ) ) ) );
kt( 'thể thơ: câu một dòng; tự do', 'mot-dong' === cadao_the_tho( $am( 7 ) ) && 'tu-do' === cadao_the_tho( implode( "\n", array( $am( 9 ), $am( 5 ), $am( 11 ) ) ) ) );
kt( 'thể thơ: đếm âm tiết không tính dấu câu đứng riêng', 'luc-bat' === cadao_the_tho( $am( 6 ) . " –\n" . $am( 8 ) . ' !' ) );
$tt_id = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Thể thơ thử', 'post_content' => cadao_khoi_tho( $am( 6, 9700 ) . "\n" . $am( 8, 9710 ) ) ) );
kt( 'lưu bài: máy gắn thể thơ để lọc danh sách (/the-tho/luc-bat/)', has_term( 'luc-bat', 'the_tho', $tt_id ) && str_contains( (string) get_term_link( 'luc-bat', 'the_tho' ), '/the-tho/luc-bat/' ) );
$st = do_blocks( cadao_khoi_tho( implode( "\n", array( $am( 7 ), $am( 7 ), $am( 6 ), $am( 8 ) ) ) ) );
kt( 'song thất lục bát: câu lục thụt lề, hai câu thất không thụt', 1 === substr_count( $st, 'cadao-dong cadao-luc' ) && str_contains( $st, 'cadao-the-song-that-luc-bat' ), $st );

// ---------- Dị bản tô chỗ khác (T-083), chạm vào từ khó (T-082) ----------
$to = cadao_to_cho_khac( "Một hai ba bốn năm sáu\nBảy tám chín mười mười một mười hai", array( 'Một hai, ba BỐN năm bảy', 'Bảy tám chín &amp; mười' ) );
kt( 'dị bản: chỉ chữ khác lời chính được tô (bỏ qua chữ hoa, dấu câu)', 'Một hai, ba BỐN năm <mark class="cadao-khac">bảy</mark>' === $to[0], $to );
kt( 'dị bản: ký tự đặc biệt giữ nguyên, chữ thêm được tô', str_contains( $to[1], '<mark class="cadao-khac">&amp;</mark>' ) || str_contains( $to[1], 'chín &amp; mười' ), $to );
$gt = cadao_gan_tu_chu_thich( array( 'Lên núi Thái Sơn cao', 'Thái Sơn lần nữa, mô tê' ), array( 'Thái Sơn' => 1, 'mô' => 2 ) );
kt( 'từ có chú thích: thành liên kết tới chú thích, chỉ lần đầu', 1 === substr_count( implode( '', $gt ), 'href="#cadao-ct-1"' ) && str_contains( $gt[0], '<a class="cadao-tu" href="#cadao-ct-1" data-ct="1">Thái Sơn<span class="screen-reader-text"> (xem chú thích)</span></a>' ), $gt );
kt( 'từ có chú thích: chỉ khớp trọn từ (không khớp "mô" trong chữ khác)', str_contains( $gt[1], '>mô<span' ) && 1 === substr_count( implode( '', cadao_gan_tu_chu_thich( array( 'Một mômen' ), array( 'mô' => 2 ) ) ), 'mômen' ) && ! str_contains( implode( '', cadao_gan_tu_chu_thich( array( 'Một mômen' ), array( 'mô' => 2 ) ) ), 'cadao-tu' ) );
$nd_thu = cadao_khoi_tho( "Lời chính Thái Sơn đây\nDòng hai lời chính" ) . "\n\n" . cadao_khoi_tieu_de( 'Dị bản' ) . "\n\n" . cadao_khoi_tho( "Lời khác Thái Sơn đây\nDòng hai lời chính" ) . "\n\n" . cadao_khoi_tieu_de( 'Chú thích' )
	. "\n\n<!-- wp:list {\"className\":\"cadao-chu-thich\"} -->\n<ul class=\"wp-block-list cadao-chu-thich\">\n<!-- wp:list-item -->\n<li><strong>Thái Sơn</strong>: Núi mẫu.</li>\n<!-- /wp:list-item -->\n</ul>\n<!-- /wp:list -->";
$GLOBALS['cadao_bai_dung'] = cadao_chuan_bi_bai( $nd_thu ) + array( 'tho' => -1, 'ct' => 0 );
$ra = do_blocks( $nd_thu );
$GLOBALS['cadao_bai_dung'] = null;
kt( 'trang bài: dị bản có nhãn "Dị bản 1", chữ khác được tô', str_contains( $ra, '<p class="cadao-nhan-di-ban">Dị bản 1</p>' ) && str_contains( $ra, '<mark class="cadao-khac">khác</mark>' ), $ra );
kt( 'trang bài: từ chú thích trong lời chính trỏ tới đúng mục chú thích có id', str_contains( $ra, 'href="#cadao-ct-1"' ) && str_contains( $ra, '<li id="cadao-ct-1">' ) && 1 === substr_count( $ra, 'class="cadao-tu"' ), $ra );
kt( 'trang bài: tắt JavaScript vẫn đọc được danh sách chú thích', str_contains( $ra, 'Núi mẫu.' ) );

// ---------- Công cụ đọc (T-085), bài dài (T-090) ----------
$hd2 = cadao_khoi_hanh_dong( get_post( $tt_id ) );
kt( 'công cụ đọc: cỡ chữ, sáng / tối, trình chiếu; nút Nghe ẩn cho tới khi máy có giọng tiếng Việt, có dòng "Giọng đọc máy"', str_contains( $hd2, 'aria-label="Công cụ đọc"' ) && str_contains( $hd2, '>Aa</button>' ) && str_contains( $hd2, 'Trình chiếu' ) && str_contains( $hd2, '<button type="button" class="cadao-nghe" hidden>' ) && str_contains( $hd2, 'Giọng đọc máy' ) );
ob_start();
do_action( 'wp_head' );
kt( 'cỡ chữ, sáng / tối áp ngay trong <head> (không nháy), không cookie', str_contains( (string) ob_get_clean(), "localStorage.getItem('cadao-co-chu')" ) );
$js = (string) file_get_contents( CADAO_THU_MUC . '/assets/ca-dao.js' );
kt( 'JavaScript trang bài ≤ 15 KB cho phần thêm (cả tệp ≤ 21 KB), không tự phát giọng', strlen( $js ) <= 21 * 1024 && ! preg_match( '/speak\([^)]*\)\s*;\s*}\s*\(\)/', $js ), strlen( $js ) );
$khong_kho = array_map( static fn( $i ) => "dòng $i", range( 1, 50 ) );
$cd        = cadao_chia_doan( $khong_kho );
kt( 'bài dài không chia khổ: mỗi đoạn ~20 dòng', array( 1 => 1, 2 => 21, 3 => 41 ) === $cd['dong_dau'] && 50 === $cd['dong_cuoi'][3], $cd );
$co_kho = array_merge( array_fill( 0, 12, 'a b' ), array( '' ), array_fill( 0, 30, 'c d' ), array( '' ), array_fill( 0, 5, 'e f' ) );
$kho_dai = array_merge( array_fill( 0, 5, 'a b' ), array( '' ), array_fill( 0, 60, 'c d' ) );
kt( 'khổ dài quá 40 dòng: chia tiếp ~20 dòng một đoạn', array( 1 => 1, 2 => 6, 3 => 26, 4 => 46 ) === cadao_chia_doan( $kho_dai )['dong_dau'], cadao_chia_doan( $kho_dai ) );
kt( 'bài dài có khổ: chia theo khổ', array( 1 => 1, 2 => 13, 3 => 43 ) === cadao_chia_doan( $co_kho )['dong_dau'], cadao_chia_doan( $co_kho ) );
$dai = array();
for ( $k = 1; $k <= 3; $k++ ) {
	$dai = array_merge( $dai, array_map( static fn( $d ) => "Khổ $k dòng thử $d", range( 1, 20 ) ), array( '' ) );
}
$nd_dai = cadao_khoi_tho( rtrim( implode( "\n", $dai ) ) );
$GLOBALS['cadao_bai_dung'] = cadao_chuan_bi_bai( $nd_dai ) + array( 'tho' => -1, 'ct' => 0 );
$ra_dai = do_blocks( $nd_dai );
$GLOBALS['cadao_bai_dung'] = null;
kt( 'bài dài: số dòng, thời gian đọc, mục lục 3 đoạn, số dòng bên lề mỗi 5 dòng', str_contains( $ra_dai, '60 dòng · 3 đoạn · khoảng' ) && 3 === substr_count( $ra_dai, '<li><a href="#cadao-doan-' ) && str_contains( $ra_dai, 'data-so="5"' ) && str_contains( $ra_dai, 'data-so="60"' ), $ra_dai );
kt( 'bài dài: đoạn sau dòng 30 được đánh dấu để thu gọn (tắt JavaScript vẫn hiện đủ)', str_contains( $ra_dai, 'id="cadao-doan-3" data-so-doan="3" data-sau' ) && ! str_contains( $ra_dai, 'id="cadao-doan-2" data-so-doan="2" data-sau' ) && str_contains( $ra_dai, 'Khổ 3 dòng thử 20' ) );
// ---------- Từ điển (T-044) ----------
$ds_ct = static fn( string $tu, string $nd, string $nguon = '' ) => "<!-- wp:list {\"className\":\"cadao-chu-thich\"} -->\n<ul class=\"wp-block-list cadao-chu-thich\">\n<!-- wp:list-item -->\n<li><strong>" . esc_html( $tu ) . '</strong>: ' . esc_html( $nd ) . "</li>\n<!-- /wp:list-item -->\n</ul>\n<!-- /wp:list -->"
	. ( $nguon ? "\n\n" . cadao_khoi_doan( 'Nguồn: <a href="' . $nguon . '" rel="noopener">Nguồn từ điển thử <span aria-hidden="true">↗</span></a>', 'cadao-chu-thich-nguon' ) : '' );
$td1 = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Từ điển thử một', 'post_content' => cadao_khoi_tho( 'Đi qua bến Đò Thử' ) . "\n\n" . $ds_ct( 'Đò Thử', 'Bến đò mẫu ở Nam Bộ.', 'https://example.org/td1/' ) ) );
$td2 = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Từ điển thử hai', 'post_content' => cadao_khoi_tho( 'Lại qua Đò Thử' ) . "\n\n" . $ds_ct( 'Đò Thử', 'Bến đò mẫu ở Nam Bộ.', 'https://example.org/td2/' ) ) );
$td3 = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Từ điển thử ba', 'post_content' => cadao_khoi_tho( 'Chữ cổ thử' ) . "\n\n" . $ds_ct( 'Đò Thử', 'Một nghĩa khác, từ cổ.' ) ) );
$m_do = cadao_muc_tu( array( 'tu' => 'Đò Thử', 'noi_dung' => 'Bến đò mẫu ở Nam Bộ.' ), false );
kt( 'từ điển: cùng từ, cùng lời giải ở hai bài → một mục từ, có cả hai bài', $m_do && 2 === (int) get_term( $m_do->term_id, 'tu_kho' )->count && has_term( $m_do->term_id, 'tu_kho', $td1 ) && has_term( $m_do->term_id, 'tu_kho', $td2 ) );
$m_co = cadao_muc_tu( array( 'tu' => 'Đò Thử', 'noi_dung' => 'Một nghĩa khác, từ cổ.' ), false );
kt( 'từ điển: cùng từ, lời giải khác → mục từ khác (đường dẫn riêng)', $m_co && $m_co->term_id !== $m_do->term_id && str_contains( (string) get_term_link( $m_co ), '/tu-dien/do-thu-2/' ), get_term_link( $m_co ) );
kt( 'từ điển: nguồn là dòng "Nguồn" của danh sách chú thích; tự viết thì không có nguồn', 'https://example.org/td1/' === get_term_meta( $m_do->term_id, '_cadao_nguon_url', true ) && '' === get_term_meta( $m_co->term_id, '_cadao_nguon_ten', true ) );
$mo_ta = apply_filters( 'term_description', 'Bến đò mẫu ở Nam Bộ.', $m_do->term_id, 'tu_kho', 'tu_kho' );
kt( 'trang mục từ: lời giải, "Nguồn: …", số bài có từ này', str_contains( $mo_ta, 'Nguồn: <a href="https://example.org/td1/"' ) && str_contains( $mo_ta, '2 bài có từ này' ), $mo_ta );
$_GET = array( 'loc' => 'nam-bo' );
$tdien = do_shortcode( '[cadao_tu_dien]' );
$_GET = array( 'loc' => 'tu-co' );
$tdien_co = do_shortcode( '[cadao_tu_dien]' );
$_GET = array();
kt( 'trang /tu-dien/: lọc Nam Bộ, từ cổ theo lời giải; nhóm theo chữ cái (Đ riêng)', str_contains( $tdien, 'Bến đò mẫu' ) && ! str_contains( $tdien, 'Một nghĩa khác' ) && str_contains( $tdien_co, 'Một nghĩa khác' ) && str_contains( $tdien, '<h2>Đ</h2>' ), $tdien );
kt( 'trang "Từ điển" có sẵn ở /tu-dien/', ( $p_td = get_page_by_path( 'tu-dien' ) ) && str_contains( $p_td->post_content, '[cadao_tu_dien]' ) );
$tdu = do_blocks( get_post( $td1 )->post_content );
$GLOBALS['cadao_bai_dung'] = cadao_chuan_bi_bai( get_post( $td1 )->post_content ) + array( 'tho' => -1, 'ct' => 0 );
$tdu = do_blocks( get_post( $td1 )->post_content );
$GLOBALS['cadao_bai_dung'] = null;
kt( 'chú thích trong bài trỏ tới mục từ (ô chạm có "Xem trong từ điển")', str_contains( $tdu, 'data-tu-dien="' . get_term_link( $m_do ) . '"' ) && str_contains( $tdu, 'data-so-bai="2"' ), $tdu );
kt( 'chú thích mang mã mục từ (đo "mở chú giải", T-052)', str_contains( $tdu, 'data-muc="' . $m_do->term_id . '"' ) );

// ---------- Wikidata (T-086), địa danh (T-087), ảnh đáp án (T-088) — API giả ----------
$GLOBALS['wd_hoi'] = array();
add_filter( 'cadao_nhip_giay', '__return_zero' );
add_filter(
	'pre_http_request',
	static function ( $tra, $tham, $url ) {
		$GLOBALS['wd_hoi'][] = $url;
		$q = array();
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $q );
		$tl = static fn( $body ) => array( 'headers' => array(), 'body' => wp_json_encode( $body ), 'response' => array( 'code' => 200, 'message' => '' ), 'cookies' => array(), 'filename' => null );
		if ( 'wbsearchentities' === ( $q['action'] ?? '' ) ) {
			$s = $q['search'];
			return $tl( array( 'search' => 'Đò Thử' === $s ? array(
				array( 'id' => 'Q901', 'label' => 'Đò Thử', 'description' => 'trang định hướng Wikimedia', 'match' => array( 'type' => 'label', 'language' => 'vi', 'text' => 'Đò Thử' ) ),
				array( 'id' => 'Q902', 'label' => 'Đò Thử', 'description' => 'bến đò mẫu', 'match' => array( 'type' => 'label', 'language' => 'vi', 'text' => 'Đò Thử' ) ),
			) : ( 'Cái mẫu' === $s ? array( array( 'id' => 'Q903', 'label' => 'cái mẫu', 'description' => 'đồ vật mẫu', 'match' => array( 'type' => 'alias', 'language' => 'vi', 'text' => 'cái mẫu' ) ) ) : array() ) ) );
		}
		if ( 'wbgetentities' === ( $q['action'] ?? '' ) ) {
			$e = array(
				'Q902' => array( 'labels' => array( 'vi' => array( 'value' => 'Đò Thử' ) ), 'descriptions' => array( 'vi' => array( 'value' => 'bến đò mẫu' ) ), 'claims' => array( 'P625' => array( array( 'mainsnak' => array( 'datavalue' => array( 'value' => array( 'latitude' => 10.03, 'longitude' => 105.78 ) ) ) ) ), 'P18' => array( array( 'mainsnak' => array( 'datavalue' => array( 'value' => 'Anh do thu.jpg' ) ) ) ) ), 'sitelinks' => array( 'viwiki' => array( 'title' => 'Đò Thử' ) ) ),
				'Q903' => array( 'labels' => array( 'vi' => array( 'value' => 'cái mẫu' ) ), 'claims' => array( 'P18' => array( array( 'mainsnak' => array( 'datavalue' => array( 'value' => 'Anh khong mo.jpg' ) ) ) ) ) ),
			);
			return $tl( array( 'entities' => array( $q['ids'] => $e[ $q['ids'] ] ?? array() ) ) );
		}
		if ( 'query' === ( $q['action'] ?? '' ) ) { // Commons: giấy phép của ảnh
			$mo = str_contains( $q['titles'], 'do thu' );
			return $tl( array( 'query' => array( 'pages' => array( '1' => array( 'imageinfo' => array( array( 'thumburl' => 'https://upload.wikimedia.org/thu/480px-anh.png', 'descriptionurl' => 'https://commons.wikimedia.org/wiki/File:Anh', 'extmetadata' => array( 'LicenseShortName' => array( 'value' => $mo ? 'CC BY-SA 4.0' : 'All rights reserved' ), 'Artist' => array( 'value' => '<a href="#">Người Chụp Thử</a>' ), 'LicenseUrl' => array( 'value' => 'https://creativecommons.org/licenses/by-sa/4.0' ) ) ) ) ) ) ) ) );
		}
		if ( str_starts_with( $url, 'https://upload.wikimedia.org/' ) ) { // tải ảnh: ghi một PNG 1×1
			file_put_contents( $tham['filename'], base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==' ) );
			return array( 'headers' => array( 'content-type' => 'image/png' ), 'body' => '', 'response' => array( 'code' => 200, 'message' => '' ), 'cookies' => array(), 'filename' => $tham['filename'] );
		}
		return $tra;
	},
	10,
	3
);
cadao_wd_lam_dan( 120 );
$hang_wd = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_wd_bang() . ' WHERE khoa = %s', 'tu:' . $m_do->term_id ) );
kt( 'Wikidata: bỏ trang định hướng, lấy thực thể trùng nhãn; vào hàng chờ "Xác nhận"', $hang_wd && 'Q902' === $hang_wd->wd && 'cho' === $hang_wd->trang_thai, $hang_wd );
kt( 'Wikidata: chưa xác nhận thì chưa dùng (không ảnh, không bản đồ)', null === cadao_wd_cua_muc_tu( $m_do->term_id ) && '' === cadao_them_cho_chu_thich( 'Đò Thử', 'Bến đò mẫu ở Nam Bộ.' )['them'] );
kt( 'Wikidata: đáp án câu đố cũng vào hàng chờ', (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . cadao_wd_bang() . " WHERE khoa = %s AND trang_thai = 'cho'", cadao_khoa_dap_an( 'Cái mẫu' ) ) ) );
cadao_wd_xac_nhan( (int) $hang_wd->id, 'dung' );
$wd = cadao_wd_cua_muc_tu( $m_do->term_id );
kt( 'xác nhận "Đúng": ảnh giấy phép mở tải về thư viện, ghi tác giả và giấy phép', $wd && ! empty( $wd['anh_da_tai']['id'] ) && 'Người Chụp Thử' === $wd['anh_da_tai']['tac_gia'] && 'CC BY-SA 4.0' === $wd['anh_da_tai']['giay_phep'] && array( 10.03, 105.78 ) === $wd['toa_do'], $wd );
$dd = cadao_html_dia_danh( $wd );
kt( 'địa danh: ảnh có dòng ghi công, bản đồ nhỏ có chấm đúng chỗ, mở bản đồ lớn, Wikipedia', str_contains( $dd, 'Ảnh: Người Chụp Thử · <a href="https://creativecommons.org/licenses/by-sa/4.0"' ) && str_contains( $dd, 'class="cadao-cham" style="left:24.23%;top:86.99%"' ) && str_contains( $dd, 'openstreetmap.org/?mlat=10.03&#038;mlon=105.78' ) && str_contains( $dd, 'vi.wikipedia.org/wiki/' ), $dd );
kt( 'trang mục từ có ảnh và bản đồ sau khi xác nhận', str_contains( apply_filters( 'term_description', '', $m_do->term_id, 'tu_kho', 'tu_kho' ), 'cadao-ban-do-nho' ) );
$hang_dap = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_wd_bang() . ' WHERE khoa = %s', cadao_khoa_dap_an( 'Cái mẫu' ) ) );
cadao_wd_xac_nhan( (int) $hang_dap->id, 'dung' );
kt( 'ảnh giấy phép không mở: không dùng; đáp án không có ảnh thì không để chỗ trống', '' === cadao_them_cho_dap_an( 'Cái mẫu' ) && false === ( json_decode( $wpdb->get_var( $wpdb->prepare( 'SELECT du_lieu FROM ' . cadao_wd_bang() . ' WHERE id = %d', $hang_dap->id ) ), true )['anh_da_tai'] ?? null ) );
cadao_wd_xac_nhan( (int) $hang_wd->id, 'sai' );
kt( 'đổi thành "Sai": thôi dùng', null === cadao_wd_cua_muc_tu( $m_do->term_id ) );
cadao_wd_xac_nhan( (int) $hang_wd->id, 'dung' );
$so_hoi = count( $GLOBALS['wd_hoi'] );
cadao_wd_lam_dan( 60 );
kt( 'chạy lại: không hỏi lại mục đã xét (lịch sự với API)', count( $GLOBALS['wd_hoi'] ) === $so_hoi );
remove_all_filters( 'pre_http_request' );
remove_all_filters( 'cadao_nhip_giay' );
$_GET = array( 'tab' => 'dung' );
ob_start();
cadao_trang_wikidata();
$twd = ob_get_clean();
$_GET = array();
kt( 'trang "Xác nhận Wikidata": thực thể, mô tả, nút quyết', str_contains( $twd, 'Q902' ) && str_contains( $twd, 'bến đò mẫu' ) && str_contains( $twd, 'Sai' ) );

// ---------- Gợi ý vùng (T-089), chủ đề (T-091) ----------
kt( 'bộ chủ đề, vùng của dự án có sẵn (nhóm kinh nghiệm, lối hát, loại câu đố)', term_exists( 'tinh-yeu-hon-nhan', 'chu_de' ) && term_exists( 'kn-thoi-tiet', 'chu_de' ) && (int) get_term_by( 'slug', 'kn-thoi-tiet', 'chu_de' )->parent === (int) get_term_by( 'slug', 'kinh-nghiem', 'chu_de' )->term_id && term_exists( 'hat-ru', 'chu_de' ) && term_exists( 'nam-bo', 'vung' ) );
kt( 'vùng theo tọa độ (gần đúng, chỉ để gợi ý)', array( 'bac-bo', 'bac-trung-bo', 'trung-trung-bo', 'nam-trung-bo', 'tay-nguyen', 'nam-bo', 'nam-trung-bo' ) === array_map( static fn( $p ) => cadao_vung_theo_toa_do( $p[0], $p[1] ), array( array( 21.03, 105.85 ), array( 18.67, 105.68 ), array( 16.46, 107.6 ), array( 13.78, 109.22 ), array( 12.67, 108.04 ), array( 10.03, 105.78 ), array( 10.93, 108.1 ) ) ) );
$g1 = cadao_goi_y_bai( $td1 );
kt( 'gợi ý vùng từ chú thích nhắc miền và từ địa danh đã xác nhận', isset( $g1['vung']['nam-bo'] ), $g1 );
$gv = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Gợi ý thử', 'post_content' => cadao_khoi_tho( "À ơi chữ8800 chữ8801\nchữ8802 chữ8803" ) . "\n\n" . $ds_ct( 'Mô', 'Đâu (tiếng Nghệ, Huế).' ) ) );
$g2 = get_post_meta( $gv, '_cadao_goi_y', true );
kt( 'gợi ý khi lưu bài: lời ru → Hát ru; chú thích nhắc Nghệ, Huế → hai vùng', isset( $g2['chu_de']['hat-ru'], $g2['vung']['bac-trung-bo'], $g2['vung']['trung-trung-bo'] ), $g2 );
kt( 'chưa duyệt: không gắn vùng, chủ đề nào', ! has_term( '', 'vung', $gv ) && ! has_term( '', 'chu_de', $gv ) );
cadao_duyet_goi_y( $gv, array( 'trung-trung-bo' ), array( 'hat-ru' ) );
kt( 'duyệt: chỉ gắn mục đã chọn; bài rời hàng chờ', has_term( 'trung-trung-bo', 'vung', $gv ) && ! has_term( 'bac-trung-bo', 'vung', $gv ) && has_term( 'hat-ru', 'chu_de', $gv ) && '' === get_post_meta( $gv, '_cadao_goi_y', true ) );
wp_update_post( array( 'ID' => $gv, 'post_title' => 'Gợi ý thử sửa' ) );
kt( 'bài đã duyệt: sửa bài không hiện lại gợi ý', '' === get_post_meta( $gv, '_cadao_goi_y', true ) );
$kng = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Kinh nghiệm thử', 'post_content' => cadao_khoi_tho( 'chữ1 gà chữ2 mưa' ) ) );
wp_set_object_terms( $kng, 'tuc-ngu', 'the_loai' );
kt( 'tục ngữ: gợi ý nhóm kinh nghiệm theo từ (chữ có dấu nhận đúng)', ( $gk = cadao_goi_y_bai( $kng ) ) && isset( $gk['chu_de']['kn-chan-nuoi'], $gk['chu_de']['kn-thoi-tiet'] ), $gk ?? null );
$kcd = $kho( 'nguon-a', 'cd-goi-y', $dong( 9800, 6 ) . "\n" . $dong( 9806, 8 ), array( 'chu_de_goc' => array( 'Tình yêu đôi lứa', 'Khác' ) ) );
$pcd = (int) cadao_kho_tao_bai( $kcd );
$gc  = cadao_goi_y_bai( $pcd );
kt( 'chủ đề của nguồn → chủ đề của dự án qua bảng ánh xạ ("Khác" không dùng); tên chủ đề của nguồn không ra trang', isset( $gc['chu_de']['tinh-yeu-hon-nhan'] ) && 1 === count( $gc['chu_de'] ) && ! term_exists( 'Tình yêu đôi lứa', 'chu_de' ), $gc );
update_option( 'cadao_anh_xa_chu_de', array( 'Tình yêu đôi lứa' => 'gia-dinh' ) + CADAO_ANH_XA_DE_XUAT, false );
kt( 'bảng ánh xạ người sáng lập sửa thì gợi ý theo bảng mới', isset( cadao_goi_y_bai( $pcd )['chu_de']['gia-dinh'] ) );
delete_option( 'cadao_anh_xa_chu_de' );
ob_start();
cadao_trang_goi_y();
$tgy = ob_get_clean();
kt( 'trang "Gợi ý vùng, chủ đề": bảng ánh xạ, gợi ý có lý do, nút đồng ý', str_contains( $tgy, 'Bảng ánh xạ chủ đề của nguồn' ) && str_contains( $tgy, 'Tình yêu đôi lứa' ) && str_contains( $tgy, 'Đồng ý các gợi ý' ) && str_contains( $tgy, 'nguồn xếp vào' ), $tgy );

// ---------- Tìm kiếm (T-041) — các ca của docs/21 mục 5 ----------
kt( 'boDau: "Công cha" → "cong cha", "Đình" → "dinh"', 'cong cha' === cadao_bo_dau_tim( 'Công cha' ) && 'dinh' === cadao_bo_dau_tim( 'Đình' ) );
kt( 'chuanHoaCoDau: hoà→hòa, thuỷ→thủy, khoẻ→khỏe, huỷ→hủy', 'hòa thủy khỏe hủy' === cadao_chuan_hoa_co_dau( 'hoà thuỷ khoẻ huỷ' ) );
kt( 'chuanHoaCoDau giữ nguyên: toàn, hoàng, quý, quả, loẹt, thuyền', 'toàn hoàng quý quả loẹt thuyền' === cadao_chuan_hoa_co_dau( 'toàn hoàng quý quả loẹt thuyền' ) );
$tim_bai = static fn( string $tieu, string $loi, string $tt = 'publish', string $ct = '' ) => wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => $tt, 'post_title' => $tieu, 'post_content' => cadao_khoi_tho( $loi ) . $ct ) );
$t1 = $tim_bai( 'Tìm thử hoà thuận', "Tìm thử hoà thuận chữ9901\nchữ9902 cao chữ9903" );
$t2 = $tim_bai( 'Tìm thử thủy chung', "Tìm thử thủy chung chữ9904\nchữ9905 chữ9906" );
$t3 = $tim_bai( 'Tìm thử chú thích', "chữ9907 chữ9908\nchữ9909", 'publish', "\n\n" . $ds_ct( 'chữ9907', 'Từ khóa riêng zênhthử trong chú thích.' ) );
$t4 = $tim_bai( 'Tìm thử nháp', 'Tìm thử hoà thuận bản nháp', 'draft' );
$ids = static fn( string $q ) => array_column( cadao_tim( $q ), 'id' );
kt( 'tìm: kiểu bỏ dấu cũ khớp kiểu mới (q04 "hoà thuận"), có dấu mới, không dấu', in_array( $t1, $ids( 'hoà thuận' ), true ) && in_array( $t1, $ids( 'hòa thuận' ), true ) && in_array( $t1, $ids( 'hoa thuan' ), true ) );
kt( 'tìm: gõ không dấu (q03 "thuy chung")', in_array( $t2, $ids( 'thuy chung' ), true ) );
kt( 'tìm: đang gõ dở chữ cuối vẫn ra', in_array( $t2, $ids( 'tim thu thuy ch' ), true ) );
kt( 'tìm: so trọn âm tiết ("ca" không khớp "cao")', ! in_array( $t1, $ids( 'ca chữ9903' ), true ) && in_array( $t1, $ids( 'cao chữ9903' ), true ) );
kt( 'tìm: tìm cả trong chú thích', in_array( $t3, $ids( 'zênhthử' ), true ) && in_array( $t3, $ids( 'zenhthu' ), true ) );
kt( 'tìm: bài chưa đăng không có trong kết quả; đăng rồi mới có', ! in_array( $t4, $ids( 'ban nhap' ), true ) && ( wp_publish_post( $t4 ) || true ) && in_array( $t4, $ids( 'ban nhap' ), true ) );
wp_update_post( array( 'ID' => $t4, 'post_status' => 'draft' ) );
kt( 'tìm: bài thôi đăng ra khỏi kết quả', ! in_array( $t4, $ids( 'ban nhap' ), true ) );
$a_dau = $tim_bai( 'Tìm dấu một', 'chữ9911 mà chữ9912' );
$a_ko  = $tim_bai( 'Tìm dấu hai', 'chữ9911 ma chữ9912' );
$thu   = $ids( 'chữ9911 mà' );
kt( 'tìm: gõ có dấu thì bài đúng dấu đứng trước', array_search( $a_dau, $thu, true ) < array_search( $a_ko, $thu, true ), $thu );
$r = rest_do_request( new WP_REST_Request( 'GET', '/cadao/v1/tim' ) );
$yc_tim = new WP_REST_Request( 'GET', '/cadao/v1/tim' );
$yc_tim->set_param( 'q', 'thuy chung' );
$kt_tim = rest_do_request( $yc_tim )->get_data();
kt( 'REST tìm: dòng đầu, thể loại, đường dẫn', 'Tìm thử thủy chung chữ9904' === ( $kt_tim['bai'][0]['dong_dau'] ?? '' ) && ! empty( $kt_tim['bai'][0]['url'] ), $kt_tim );
$_GET = array( 'q' => 'do thu' );
$trang_tim = do_shortcode( '[cadao_tim]' );
$_GET = array( 'q' => 'khongcocaunaydau' );
$trang_khong = do_shortcode( '[cadao_tim]' );
$_GET = array();
kt( 'trang /tim/: ô tìm có nhãn, nhóm "Từ điển" khi khớp tên mục từ', str_contains( $trang_tim, '<label for="cadao-tim-o"' ) && str_contains( $trang_tim, 'Từ điển' ) && str_contains( $trang_tim, 'Đò Thử' ), $trang_tim );
kt( 'trang /tim/: không có kết quả thì báo trung thực, gợi ý cách tìm', str_contains( $trang_khong, 'Chưa tìm thấy câu này trong kho.' ) );
kt( 'trang "Tìm" có sẵn ở /tim/', ( $p_tim = get_page_by_path( 'tim' ) ) && str_contains( $p_tim->post_content, '[cadao_tim]' ) );

// ---------- Trang chủ (T-043), Bạn có biết? (T-093), trang thể loại (T-094), tình huống ----------
kt( 'Bạn có biết?: mệnh đề đầu của lời giải (tới dấu chấm phẩy, bỏ hoa đầu câu)', 'bến đò mẫu ở Nam Bộ' === cadao_menh_de_dau( 'Bến đò mẫu ở Nam Bộ. Còn nữa.' ) && '' === cadao_menh_de_dau( 'Ngắn' ) );
delete_option( 'cadao_ban_co_biet' );
$so_bcb = cadao_bcb_ghep( 8 );
$bcb    = cadao_bcb_ds();
$bcb_do = array_values( array_filter( $bcb, static fn( $x ) => $x['tu_kho'] === (int) $m_do->term_id ) )[0] ?? null;
kt( 'Bạn có biết?: máy ghép theo khuôn từ chú thích có sẵn, kèm nguồn, chờ duyệt', $so_bcb > 0 && $bcb_do && str_starts_with( $bcb_do['chu'], '… "Đò Thử" là bến đò mẫu ở Nam Bộ, và có trong câu "' ) && 'cho' === $bcb_do['trang_thai'] && 'https://example.org/td1/' === $bcb_do['nguon_url'], $bcb_do );
kt( 'Bạn có biết?: chưa duyệt thì chưa lên trang chủ', ! str_contains( do_shortcode( '[cadao_trang_chu]' ), 'Bạn có biết?' ) );
foreach ( $bcb as &$x ) {
	$x['trang_thai'] = $x === $bcb_do ? 'duyet' : $x['trang_thai'];
	if ( $x['tu_kho'] === (int) $m_do->term_id ) {
		$x['trang_thai'] = 'duyet';
		$x['chu']        = '… "Đò Thử" là một bến đò mẫu, có trong một câu ca dao?';
	}
}
unset( $x );
cadao_bcb_luu( $bcb );
$tc = do_shortcode( '[cadao_trang_chu]' );
kt( 'trang chủ: câu định vị, ô tìm lớn có nhãn, Câu hôm nay, Bạn có biết? (câu đã duyệt, kèm nguồn), Vừa bổ sung, lối vào thể loại', str_contains( $tc, 'class="cadao-dinh-vi"' ) && str_contains( $tc, '<label for="cadao-tim-chu"' ) && str_contains( $tc, 'Câu hôm nay' ) && str_contains( $tc, 'là một bến đò mẫu' ) && str_contains( $tc, '(Nguồn: <a href="https://example.org/td1/"' ) && str_contains( $tc, 'Vừa bổ sung' ) && str_contains( $tc, 'Theo thể loại' ), $tc );
$chn = cadao_cau_hom_nay();
kt( 'Câu hôm nay: bài ngắn (≤ 4 dòng) đang đăng, cả ngày một câu', $chn && 'publish' === $chn->post_status && count( array_filter( explode( "\n", cadao_loi_cac_khoi_tho( $chn->post_content )[0] ) ) ) <= 4 && cadao_cau_hom_nay()->ID === $chn->ID );
kt( 'tình huống của site cũ có sẵn (chưa đủ bài thì chưa có thẻ)', term_exists( 'biet-on-cha-me', 'tinh_huong' ) && ! in_array( 'biet-on-cha-me', array_column( cadao_tinh_huong_hom_nay(), 'slug' ), true ) );
wp_set_object_terms( $td1, 'biet-on-cha-me', 'tinh_huong' );
wp_set_object_terms( $td2, 'biet-on-cha-me', 'tinh_huong' );
update_post_meta( $td1, 'hop_khi', 'bạn muốn nói lời biết ơn' );
kt( 'tình huống đủ 2 bài: có thẻ "Hôm nay bạn đang…" trên trang chủ', in_array( 'biet-on-cha-me', array_column( cadao_tinh_huong_hom_nay(), 'slug' ), true ) && str_contains( do_shortcode( '[cadao_trang_chu]' ), 'Hôm nay bạn đang…' ) );
$th_mo = apply_filters( 'term_description', '', get_term_by( 'slug', 'biet-on-cha-me', 'tinh_huong' )->term_id, 'tinh_huong', 'tinh_huong' );
kt( 'trang tình huống: lời rút gọn ≤ 4 dòng và "Hợp khi…"', str_contains( $th_mo, 'Đi qua bến Đò Thử' ) && str_contains( $th_mo, 'Hợp khi bạn muốn nói lời biết ơn' ), $th_mo );
wp_set_object_terms( $p1, 'nam-bo', 'vung', true ); // bài ca dao đang đăng
$tl_mo = apply_filters( 'term_description', '<p>Giới thiệu người sáng lập viết.</p>', get_term_by( 'slug', 'ca-dao', 'the_loai' )->term_id, 'the_loai', 'the_loai' );
kt( 'trang thể loại: giới thiệu (người sáng lập viết), bài tiêu biểu, lối vào theo chủ đề / vùng, tổng số bài', str_contains( $tl_mo, 'Giới thiệu người sáng lập viết.' ) && str_contains( $tl_mo, 'Bài tiêu biểu' ) && str_contains( $tl_mo, 'Theo vùng' ) && preg_match( '/Tất cả ca dao \(\d+ bài\)/u', $tl_mo ), $tl_mo );

// ---------- Người đọc góp phần (T-045), bài phân tích (T-097) ----------
wp_set_current_user( 0 );
$fgy = cadao_form_gop_y( get_post( $td1 ) );
preg_match_all( '/name="([a-z_]+)"[^>]*required/', $fgy, $bat_buoc );
kt( 'form góp ý: tối đa 5 câu bắt buộc, không hỏi email / số điện thoại / tên thật', count( array_unique( $bat_buoc[1] ) ) <= 5 && ! preg_match( '/type="(email|tel)"/', $fgy ) && str_contains( $fgy, 'Tên hiển thị' ), array_unique( $bat_buoc[1] ) );
kt( 'form góp ý: thông báo quyền riêng tư ngay trong form, ô bẫy ẩn, nút mở có loại điền sẵn', str_contains( $fgy, '91/2025/QH15' ) && str_contains( $fgy, 'name="cadao_web"' ) && str_contains( $fgy, 'Ở quê bạn câu này nói khác?' ) && str_contains( $fgy, 'Bạn có sách? Gửi số trang' ) && str_contains( $fgy, 'name="bai" value="' . $td1 . '"' ) );
$_SERVER['REMOTE_ADDR'] = '203.0.113.7';
$gy = cadao_luu_gop_y( array( 'bai' => $td1, 'loai' => 'di_ban', 'noi_dung' => "Đi qua bến Đò Thử khác\nDòng hai", 'noi_nghe' => 'Làng mẫu', 'nguoi_ke' => 'bà tôi', 'dong_y' => '1' ) );
kt( 'gửi góp ý: vào Chờ duyệt (không tự hiện), giữ loại, bài, nơi nghe', is_int( $gy ) && 'pending' === get_post_status( $gy ) && 'di_ban' === get_post_meta( $gy, '_cadao_loai', true ) && $td1 === (int) get_post_meta( $gy, '_cadao_bai', true ) && 'Làng mẫu' === get_post_meta( $gy, '_cadao_noi_nghe', true ), $gy );
kt( 'gửi góp ý: ô bẫy có chữ thì không nhận; thiếu đồng ý thì báo', is_string( cadao_luu_gop_y( array( 'bai' => $td1, 'loai' => 'bao_loi', 'noi_dung' => 'x', 'dong_y' => '1', 'cadao_web' => 'spam' ) ) ) && is_string( cadao_luu_gop_y( array( 'bai' => $td1, 'loai' => 'bao_loi', 'noi_dung' => 'x' ) ) ) );
for ( $i = 0; $i < 4; $i++ ) {
	cadao_luu_gop_y( array( 'bai' => $td1, 'loai' => 'bao_loi', 'noi_dung' => "lần $i", 'dong_y' => '1' ) );
}
kt( 'giới hạn 5 lần gửi mỗi giờ (không lưu địa chỉ IP, chỉ dấu băm tạm)', is_string( cadao_luu_gop_y( array( 'bai' => $td1, 'loai' => 'bao_loi', 'noi_dung' => 'lần 6', 'dong_y' => '1' ) ) ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_value LIKE %s", '%203.0.113.7%' ) ) );
$_SERVER['REMOTE_ADDR'] = '203.0.113.8';
update_option( 'cadao_turnstile_site', 'khoa-site-thu' );
update_option( 'cadao_turnstile_bi_mat', 'khoa-bi-mat-thu' );
add_filter( 'pre_http_request', static fn( $tra, $tham, $url ) => str_contains( $url, 'turnstile' ) ? array( 'headers' => array(), 'body' => wp_json_encode( array( 'success' => 'ok' === ( $tham['body']['response'] ?? '' ) ) ), 'response' => array( 'code' => 200, 'message' => '' ), 'cookies' => array(), 'filename' => null ) : $tra, 10, 3 );
kt( 'Turnstile (khi có khóa): sai thì không nhận, đúng thì nhận; form có ô Turnstile', is_string( cadao_luu_gop_y( array( 'bai' => $td1, 'loai' => 'bao_loi', 'noi_dung' => 'x', 'dong_y' => '1', 'cf-turnstile-response' => 'sai' ) ) ) && is_int( cadao_luu_gop_y( array( 'bai' => $td1, 'loai' => 'bao_loi', 'noi_dung' => 'x', 'dong_y' => '1', 'cf-turnstile-response' => 'ok' ) ) ) && str_contains( cadao_form_gop_y( get_post( $td1 ) ), 'data-sitekey="khoa-site-thu"' ) );
remove_all_filters( 'pre_http_request' );
delete_option( 'cadao_turnstile_site' );
delete_option( 'cadao_turnstile_bi_mat' );
$_SERVER['REMOTE_ADDR'] = '203.0.113.9';
kt( 'cảm nghĩ: bài đang đăng mở bình luận; không bắt tên, email; không cookie bình luận', comments_open( $td1 ) && 0 === (int) get_option( 'require_name_email' ) && false === has_action( 'set_comment_cookies', 'wp_set_comment_cookies' ) );
$bl = wp_new_comment( array( 'comment_post_ID' => $td1, 'comment_content' => 'Cảm nghĩ thử của bạn đọc.', 'comment_author' => '', 'comment_author_email' => '', 'comment_type' => 'comment' ), true );
kt( 'cảm nghĩ: luôn vào Chờ duyệt, không tên thì ghi "Một bạn đọc"', ! is_wp_error( $bl ) && '0' === get_comment( $bl )->comment_approved && 'Một bạn đọc' === get_comment( $bl )->comment_author, $bl );
wp_set_comment_status( $bl, 'approve' );
kt( 'cảm nghĩ đã duyệt: có "Báo vi phạm"', str_contains( apply_filters( 'comment_text', 'x', get_comment( $bl ) ), 'bao_vi_pham=' . $bl ) );
$ob = '';
$GLOBALS['post'] = get_post( $td1 );
ob_start();
comment_form( array(), $td1 );
$ob = ob_get_clean();
kt( 'form cảm nghĩ: không ô email / trang web, có thông báo quyền riêng tư', ! str_contains( $ob, 'name="email"' ) && ! str_contains( $ob, 'name="url"' ) && str_contains( $ob, '91/2025/QH15' ) && str_contains( $ob, 'Cảm nghĩ của bạn' ), $ob );
$vb = cadao_van_ban_phan_tich( "Đoạn một có **chữ đậm** và <script>x</script>.\n\n> Dòng trích một\n> Dòng trích hai\n\nĐoạn ba." );
kt( 'bài phân tích: đoạn, chữ đậm, trích lời; chữ HTML được thoát', str_contains( $vb, '<strong>chữ đậm</strong>' ) && str_contains( $vb, '&lt;script&gt;' ) && str_contains( $vb, '<blockquote class="wp-block-quote"><!-- wp:paragraph -->' . "\n" . '<p>Dòng trích một<br>Dòng trích hai</p>' ) && 3 === substr_count( $vb, '<!-- wp:paragraph -->' ), $vb );
$dai_pt = str_repeat( 'Câu này hay vì hình ảnh gần gũi. ', 8 );
kt( 'bài phân tích: quá ngắn, thiếu cam kết thì báo', is_string( cadao_luu_phan_tich( array( 'bai' => $td1, 'tieu_de' => 'T', 'noi_dung' => 'ngắn', 'tu_viet' => '1', 'dong_y' => '1' ) ) ) && is_string( cadao_luu_phan_tich( array( 'bai' => $td1, 'tieu_de' => 'T', 'noi_dung' => $dai_pt, 'dong_y' => '1' ) ) ) );
$pt = cadao_luu_phan_tich( array( 'bai' => $td2, 'tieu_de' => 'Cảm nhận thử', 'noi_dung' => $dai_pt, 'but_danh' => 'Bút danh thử', 'vai_tro' => 'hoc_sinh', 'tu_viet' => '1', 'dong_y' => '1' ) );
kt( 'bài phân tích: vào Chờ duyệt; chưa duyệt thì chưa hiện, bài ca dao vẫn noindex', is_int( $pt ) && 'pending' === get_post_status( $pt ) && ! cadao_phan_tich_cua_bai( $td2 ) && ! cadao_co_gia_tri_rieng( get_post( $td2 ) ), $pt );
wp_publish_post( $pt );
kt( 'bài phân tích đã duyệt: hiện dưới bài ca dao (bút danh · vai trò); bài ca dao có giá trị riêng (bỏ noindex)', 1 === count( cadao_phan_tich_cua_bai( $td2 ) ) && 'Bút danh thử · Học sinh' === cadao_dong_tac_gia( get_post( $pt ) ) && cadao_co_gia_tri_rieng( get_post( $td2 ) ) && str_contains( (string) get_permalink( $pt ), '/phan-tich/' ) );
update_post_meta( $pt, '_cadao_du_an', true );
kt( '"Bài phân tích của dự án" có nhãn riêng', str_contains( cadao_dong_tac_gia( get_post( $pt ) ), 'Bài phân tích của dự án' ) );
kt( 'trang "Viết cảm nhận" có sẵn; form có cam kết tự viết, CC BY 4.0, quyền riêng tư', ( $p_vt = get_page_by_path( 'viet-cam-nhan' ) ) && ( $_GET = array( 'bai' => $td2 ) ) && str_contains( $f_pt = do_shortcode( '[cadao_viet_phan_tich]' ), 'Bài do tôi tự viết' ) && str_contains( $f_pt, 'CC BY 4.0' ) && str_contains( $f_pt, '91/2025/QH15' ) );
$_GET = array();

// ---------- Trang Phương pháp, Nguồn, Về dự án (T-046) ----------
$pp = get_page_by_path( 'phuong-phap', OBJECT, 'page' );
kt( 'ba trang của dự án tạo sẵn ở dạng Bản nháp (người sáng lập sửa rồi đăng)', $pp && 'draft' === $pp->post_status && str_contains( $pp->post_content, 'AI không viết lời' ) && 'draft' === get_post_status( get_page_by_path( 've-du-an', OBJECT, 'page' ) ) && 'draft' === get_post_status( get_page_by_path( 'nguon', OBJECT, 'page' ) ) );
kt( 'chân trang: trang dự án chưa đăng thì không có liên kết tới nó', ! str_contains( do_shortcode( '[cadao_chan_trang]' ), 'Phương pháp' ) && ! str_contains( do_shortcode( '[cadao_chan_trang]' ), 'Về dự án' ) );
wp_publish_post( $pp->ID );
kt( 'chân trang: trang đã đăng thì có liên kết', str_contains( do_shortcode( '[cadao_chan_trang]' ), 'Phương pháp' ) );
wp_publish_post( $P );
$ng = do_shortcode( '[cadao_nguon]' );
kt( 'trang Nguồn tự sinh: nguồn tự động có chú thích chép, nguồn ghi trong bài, dữ liệu mở đang dùng', str_contains( $ng, 'Nguồn A' ) && str_contains( $ng, 'Sách thử' ) && str_contains( $ng, 'Wikidata' ) && str_contains( $ng, 'Natural Earth' ), $ng );

// ---------- Gộp hai bài đã có (T-101) ----------
$LX = $dong( 9950, 6 ) . "\n" . $dong( 9956, 8 );
$LY = $dong( 9950, 6 ) . "\n" . $dong( 9956, 4 ) . ' ' . $dong( 9990, 4 );
$gx = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Gộp bài chính', 'post_name' => 'gop-bai-chinh', 'post_content' => cadao_khoi_tho( $LX ) ) );
$gyy = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Gộp bài phụ', 'post_name' => 'gop-bai-phu', 'post_excerpt' => 'Hiểu nhanh của bài phụ.', 'post_content' => cadao_khoi_tho( $LY ) . "\n\n" . $ds_ct( 'chữ9990', 'Chú giải tự viết của bài phụ.' ), 'meta_input' => array( '_cadao_ho_tro_ai' => 1, 'nguon_ten' => 'Sách bài phụ' ) ) );
wp_set_object_terms( $gyy, 'nam-bo', 'vung' );
$r_gop = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . cadao_trung_bang() . ' WHERE (a = %s AND b = %s)', "bai:$gyy", "bai:$gx" ) );
kt( 'hai bài đã đăng nghi trùng: vào hàng "Có thể trùng" (bài–bài)', $r_gop && 'cho' === $r_gop->trang_thai, $r_gop );
cadao_luot_doc_cong( $gx );
cadao_luot_doc_cong( $gyy );
cadao_luot_doc_cong( $gyy );
$bl_phu = wp_insert_comment( array( 'comment_post_ID' => $gyy, 'comment_content' => 'Cảm nghĩ ở bài phụ.', 'comment_approved' => 1 ) );
$pt_phu = wp_insert_post( array( 'post_type' => CADAO_LOAI_PHAN_TICH, 'post_status' => 'publish', 'post_title' => 'Phân tích bài phụ', 'post_content' => 'x', 'meta_input' => array( '_cadao_bai' => $gyy ) ) );
$_GET = array( 'tab' => 'cho' );
ob_start();
cadao_trang_trung();
$tr_gop = ob_get_clean();
$_GET = array();
kt( 'trang "Có thể trùng": cặp bài–bài có nút chọn bài giữ lại', str_contains( $tr_gop, 'Gộp: giữ &quot;Gộp bài chính&quot;' ) || str_contains( $tr_gop, 'Gộp: giữ "Gộp bài chính"' ), substr( $tr_gop, 0, 300 ) );
kt( 'gộp: thực hiện được', cadao_gop_hai_bai( $gx, $gyy ) );
$nd_gx = get_post( $gx );
kt( 'gộp: lời bài phụ thành dị bản của bài chính (đúng từng ký tự), lời chính giữ nguyên', $LX === cadao_loi_cac_khoi_tho( $nd_gx->post_content )[0] && in_array( $LY, cadao_loi_cac_khoi_tho( $nd_gx->post_content ), true ) );
kt( 'gộp: giữ Hiểu nhanh, dấu "có hỗ trợ AI", nguồn đã ghi, chú giải tự viết của bài phụ', 'Hiểu nhanh của bài phụ.' === $nd_gx->post_excerpt && get_post_meta( $gx, '_cadao_ho_tro_ai', true ) && 'Sách bài phụ' === get_post_meta( $gx, 'nguon_ten', true ) && str_contains( $nd_gx->post_content, 'Chú giải tự viết của bài phụ.' ) );
kt( 'gộp: lượt đọc cộng dồn, cảm nghĩ và bài phân tích chuyển sang bài chính', 3 === cadao_luot_doc_tong( $gx ) && 0 === cadao_luot_doc_tong( $gyy ) && $gx === (int) get_comment( $bl_phu )->comment_post_ID && $gx === (int) get_post_meta( $pt_phu, '_cadao_bai', true ) && 1 === (int) get_comments_number( $gx ) );
kt( 'gộp: vùng gộp lại; bài phụ vào thùng rác (không xóa hẳn); cặp đã gộp', has_term( 'nam-bo', 'vung', $gx ) && 'trash' === get_post_status( $gyy ) && 'da_gop' === $wpdb->get_var( $wpdb->prepare( 'SELECT trang_thai FROM ' . cadao_trung_bang() . ' WHERE id = %d', $r_gop->id ) ) );
kt( 'gộp: đường dẫn cũ của bài phụ chuyển về bài chính (tên cũ ghi vào _wp_old_slug)', in_array( 'gop-bai-phu', get_post_meta( $gx, '_wp_old_slug' ), true ) );

// ---------- Đã lưu (T-051), đo lường (T-052) ----------
$p_luu = get_page_by_path( 'da-luu', OBJECT, 'page' );
kt( 'trang "Đã lưu" có sẵn ở /da-luu/, danh sách dựng trên máy người đọc, có lời giải thích quyền riêng tư', $p_luu && 'publish' === $p_luu->post_status && str_contains( do_shortcode( $p_luu->post_content ), 'cadao-da-luu-ds' ) && str_contains( do_shortcode( $p_luu->post_content ), 'site không biết bạn lưu gì' ) );
$hd_luu = cadao_khoi_hanh_dong( get_post( $td ) );
kt( 'trang bài: nút ☆ Lưu (ẩn tới khi JavaScript thấy bộ nhớ dùng được), liên kết "Bài đã lưu", mã bài', str_contains( $hd_luu, 'class="cadao-luu" aria-pressed="false" hidden' ) && str_contains( $hd_luu, 'cadao-xem-da-luu' ) && str_contains( $hd_luu, 'data-id="' . $td . '"' ) );
kt( 'chân trang có liên kết "Bài đã lưu"', str_contains( do_shortcode( '[cadao_chan_trang]' ), 'Bài đã lưu' ) );
kt( 'bảng sự kiện đã tạo', (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', cadao_su_kien_bang() ) ) );
kt( 'chuỗi tìm: chữ thường, gọn khoảng trắng; bỏ chuỗi trông như email, số điện thoại', 'con cò bay lả' === cadao_su_kien_chuoi_tim( '  Con Cò   bay LẢ ' ) && '' === cadao_su_kien_chuoi_tim( 'ten@mail.vn' ) && '' === cadao_su_kien_chuoi_tim( '0912 345 678' ) && 'năm 1945' === cadao_su_kien_chuoi_tim( 'năm 1945' ) && '' === cadao_su_kien_chuoi_tim( 'a' ) );
$may_sk = 0; // mỗi lần gửi là một người đọc khác (máy chủ chặn cùng máy gửi lặp — T-118), trừ khi chỉ định IP
$sk     = static function ( array $du, string $ua = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Mobile Safari/537.36', ?string $ip = null ) use ( &$may_sk ) {
	$_SERVER['REMOTE_ADDR'] = $ip ?? '198.51.100.' . ( ++$may_sk % 200 + 10 );
	$yc                     = new WP_REST_Request( 'POST', '/cadao/v1/su-kien' );
	$yc->set_header( 'user-agent', $ua );
	$yc->set_header( 'content-type', 'application/json' );
	$yc->set_body( wp_json_encode( $du ) );
	return rest_do_request( $yc );
};
$tinh = static fn( $r ) => 200 === $r->get_status() && true === ( $r->get_data()['tinh'] ?? null );
kt( 'sự kiện lạ: bị từ chối (chỉ đúng 3 sự kiện)', 400 === $sk( array( 'loai' => 'nhap_chuot' ) )->get_status() );
kt( 'tìm không ra: được ghi, hai lần thì cộng 2', $tinh( $sk( array( 'loai' => 'tim_khong_ket_qua', 'q' => 'Zzqx Khongcocau' ) ) ) && $tinh( $sk( array( 'loai' => 'tim_khong_ket_qua', 'q' => 'zzqx khongcocau' ) ) ) && array( 'zzqx khongcocau' => 2 ) === cadao_su_kien_nhieu( 'tim_khong_ket_qua' ), cadao_su_kien_nhieu( 'tim_khong_ket_qua' ) );
kt( 'tìm có kết quả: không ghi là "không ra"', ! $tinh( $sk( array( 'loai' => 'tim_khong_ket_qua', 'q' => 'thuy chung' ) ) ) );
kt( 'máy dò: không ghi', ! $tinh( $sk( array( 'loai' => 'tim_khong_ket_qua', 'q' => 'zzqx khac' ), 'Mozilla/5.0 (compatible; Googlebot/2.1)' ) ) );
kt( 'sao chép trích dẫn: ghi theo bài và kiểu; kiểu lạ, bài chưa đăng thì không', $tinh( $sk( array( 'loai' => 'trich_dan_sao_chep', 'bai' => $td, 'kieu' => 'apa' ) ) ) && $tinh( $sk( array( 'loai' => 'trich_dan_sao_chep', 'bai' => $td, 'kieu' => 'chi_loi' ) ) ) && ! $tinh( $sk( array( 'loai' => 'trich_dan_sao_chep', 'bai' => $td, 'kieu' => 'gi_do' ) ) ) && ! $tinh( $sk( array( 'loai' => 'trich_dan_sao_chep', 'bai' => $bai_cd1->ID, 'kieu' => 'apa' ) ) ) && array( $td . ':apa' => 1, $td . ':chi_loi' => 1 ) === cadao_su_kien_nhieu( 'trich_dan_sao_chep' ), cadao_su_kien_nhieu( 'trich_dan_sao_chep' ) );
kt( 'mở chú giải: ghi theo mục từ; mục từ không có thì không', $tinh( $sk( array( 'loai' => 'chu_giai_mo', 'muc' => $m_do->term_id ) ) ) && ! $tinh( $sk( array( 'loai' => 'chu_giai_mo', 'muc' => 999999 ) ) ) && array( (string) $m_do->term_id => 1 ) === cadao_su_kien_nhieu( 'chu_giai_mo' ) );
kt( 'bảng sự kiện chỉ có ngày, loại, khóa, số (không IP, không định danh)', array( 'ngay', 'loai', 'khoa', 'so' ) === $wpdb->get_col( 'SHOW COLUMNS FROM ' . cadao_su_kien_bang() ) );
ob_start();
cadao_su_kien_o_bang_tin();
$o_sk = ob_get_clean();
kt( 'Bảng tin "người đọc cần gì": câu tìm không ra, số lần sao chép theo kiểu, từ được chạm, cách đọc mỗi tuần', str_contains( $o_sk, 'zzqx khongcocau' ) && str_contains( $o_sk, '<strong>2</strong> lần (kèm nguồn, APA 7: 1, chỉ lời: 1)' ) && str_contains( $o_sk, esc_html( $m_do->name ) ) && str_contains( $o_sk, 'Đọc mỗi tuần' ), $o_sk );
$ua_dt = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Mobile Safari/537.36';
kt( 'cùng máy gửi lặp một sự kiện trong ngày: chỉ tính một lần; không lưu IP (T-118)', $tinh( $sk( array( 'loai' => 'tim_khong_ket_qua', 'q' => 'zzqx lap lai' ), $ua_dt, '192.0.2.50' ) ) && ! $tinh( $sk( array( 'loai' => 'tim_khong_ket_qua', 'q' => 'zzqx lap lai' ), $ua_dt, '192.0.2.50' ) ) && $tinh( $sk( array( 'loai' => 'tim_khong_ket_qua', 'q' => 'zzqx lap lai' ), $ua_dt, '192.0.2.51' ) ) && 2 === ( cadao_su_kien_nhieu( 'tim_khong_ket_qua', 30, 50 )['zzqx lap lai'] ?? 0 ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_value LIKE %s OR option_name LIKE %s", '%192.0.2.5%', '%192.0.2.5%' ) ) );
$js_bai = file_get_contents( CADAO_THU_MUC . '/assets/ca-dao.js' );
kt( 'trang bài gửi sự kiện sao chép, mở chú giải; trang tìm gửi "không ra" sau 2 giây đứng yên', str_contains( $js_bai, "suKien('trich_dan_sao_chep'" ) && str_contains( $js_bai, "suKien('chu_giai_mo'" ) && str_contains( file_get_contents( CADAO_THU_MUC . '/assets/tim.js' ), "loai: 'tim_khong_ket_qua'" ) );

// ---------- Đồng dao: trò chơi, Cách chơi, Đọc cùng bé (T-103) ----------
kt( 'phân loại "Trò chơi" có đường dẫn /tro-choi/', taxonomy_exists( 'tro_choi' ) && 'tro-choi' === get_taxonomy( 'tro_choi' )->rewrite['slug'] );
$dd1 = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Đồng dao thử', 'post_content' => cadao_khoi_tho( "Đồng dao thử một\nĐồng dao thử hai" ) ) );
wp_set_object_terms( $dd1, 'dong-dao', 'the_loai' );
$tc  = wp_insert_term( 'Trò chơi thử', 'tro_choi' );
$tc1 = get_term( $tc['term_id'], 'tro_choi' );
kt( 'trò chơi chưa có cách chơi: trên bài chỉ hiện liên kết tới trang trò chơi, không có khối trống', str_contains( cadao_khoi_cach_choi( $tc1 ), 'Trò chơi: <a' ) && ! str_contains( cadao_khoi_cach_choi( $tc1 ), '<ol' ) && '' === cadao_khoi_cach_choi( $tc1, false ) );
wp_set_current_user( cadao_quan_tri_dau_tien() );
$_POST = array( 'cadao_cc' => array( 'so_nguoi' => '6–10 bạn', 'do_tuoi' => '5–10 tuổi', 'do_dung' => 'Không cần', 'buoc' => "Bước một <b>thử</b>\n\nBước hai thử", 'nguon_ten' => '', 'nguon_url' => '', 'giay_phep' => '' ), 'cadao_cc_wiki' => '' );
wp_update_term( $tc1->term_id, 'tro_choi', array( 'name' => 'Trò chơi thử' ) );
wp_set_object_terms( $dd1, $tc1->term_id, 'tro_choi' );
$tc1 = get_term( $tc1->term_id, 'tro_choi' );
$cc1 = cadao_khoi_cach_choi( $tc1 );
kt( 'Cách chơi do dự án viết: số người, độ tuổi, đồ dùng, các bước (mỗi dòng một bước, chữ được thoát); không có dòng Nguồn', str_contains( $cc1, '<dt>Số người</dt><dd>6–10 bạn</dd>' ) && str_contains( $cc1, '<li>Bước một thử</li><li>Bước hai thử</li>' ) && ! str_contains( $cc1, 'Nguồn:' ) && str_contains( $cc1, 'Cách chơi: Trò chơi thử' ), $cc1 );
kt( 'trang trò chơi: Cách chơi rồi danh sách bài', str_contains( apply_filters( 'term_description', '', $tc1->term_id, 'tro_choi', 'tro_choi' ), 'Bài đồng dao của trò chơi này (1)' ) );
kt( 'Đọc cùng bé: nút chỉ có ở bài đồng dao (ẩn tới khi JavaScript sẵn sàng)', str_contains( cadao_khoi_hanh_dong( get_post( $dd1 ) ), '<button type="button" class="cadao-doc-cung-be" hidden>Đọc cùng bé</button>' ) && ! str_contains( cadao_khoi_hanh_dong( get_post( $td ) ), 'cadao-doc-cung-be' ) );
$js_be = file_get_contents( CADAO_THU_MUC . '/assets/dong-dao.js' );
kt( 'Đọc cùng bé: không phát âm thanh', ! preg_match( '/speechSynthesis|new Audio|<audio|\.play\(/', $js_be ) );
$tl_dd = apply_filters( 'term_description', '', get_term_by( 'slug', 'dong-dao', 'the_loai' )->term_id, 'the_loai', 'the_loai' );
kt( 'trang thể loại Đồng dao: lối vào "Theo trò chơi"', str_contains( $tl_dd, 'Theo trò chơi' ) && str_contains( $tl_dd, 'Trò chơi thử' ), $tl_dd );
// Lấy từ Wikipedia: giả lập API (không cần mạng)
kt( 'Wikipedia: nhận tên bài hoặc đường dẫn vi.wikipedia.org; đường dẫn nơi khác thì không', 'Rồng rắn lên mây' === cadao_wiki_ten_bai( 'https://vi.wikipedia.org/wiki/R%E1%BB%93ng_r%E1%BA%AFn_l%C3%AAn_m%C3%A2y' ) && 'Ô ăn quan' === cadao_wiki_ten_bai( ' Ô ăn quan ' ) && '' === cadao_wiki_ten_bai( 'https://example.org/wiki/X' ) );
kt( 'Wikipedia: tìm đúng mục "Cách chơi" / "Luật chơi"', '3' === cadao_wiki_tim_muc( array( array( 'index' => '1', 'line' => 'Lịch sử' ), array( 'index' => '3', 'line' => 'Cách chơi' ) ) ) && '2' === cadao_wiki_tim_muc( array( array( 'index' => '2', 'line' => 'Luật chơi' ) ) ) && null === cadao_wiki_tim_muc( array( array( 'index' => '1', 'line' => 'Chơi chữ' ) ) ) );
$html_wiki = '<div class="mw-parser-output"><div class="mw-heading mw-heading2"><h2 id="C">Cách chơi</h2></div><p>Đoạn một thử<sup class="reference"><a href="#c1">[1]</a></sup>.</p><ul><li>Ý một</li><li>Ý hai&nbsp;thử</li></ul><table><tr><td>Bảng</td></tr></table></div>';
kt( 'Wikipedia: chép chữ của mục — bỏ tiêu đề, chỉ số chú thích, bảng; mỗi đoạn, mỗi ý một dòng', array( 'Đoạn một thử.', 'Ý một', 'Ý hai thử' ) === cadao_wiki_van_ban( $html_wiki ), cadao_wiki_van_ban( $html_wiki ) );
add_filter( 'cadao_nhip_giay', '__return_zero' );
$gia_wiki = static function ( $pre, $tham, $url ) use ( $html_wiki ) {
	if ( ! str_starts_with( $url, 'https://vi.wikipedia.org/w/api.php' ) ) {
		return $pre;
	}
	$than = str_contains( $url, 'prop=sections' )
		? array( 'parse' => array( 'title' => 'Trò chơi thử Wiki', 'sections' => array( array( 'index' => '2', 'line' => 'Cách chơi' ) ) ) )
		: array( 'parse' => array( 'title' => 'Trò chơi thử Wiki', 'text' => array( '*' => $html_wiki ) ) );
	return array( 'headers' => array(), 'body' => wp_json_encode( $than ), 'response' => array( 'code' => 200, 'message' => 'OK' ), 'cookies' => array(), 'filename' => null );
};
add_filter( 'pre_http_request', $gia_wiki, 10, 3 );
$_POST = array( 'cadao_cc' => array( 'so_nguoi' => '6–10 bạn', 'buoc' => 'sẽ bị thay' ), 'cadao_cc_wiki' => 'Trò chơi thử Wiki', 'cadao_cc_lay_wiki' => '1' );
wp_update_term( $tc1->term_id, 'tro_choi', array( 'name' => 'Trò chơi thử' ) );
remove_filter( 'pre_http_request', $gia_wiki, 10 );
$cc2 = cadao_khoi_cach_choi( get_term( $tc1->term_id, 'tro_choi' ) );
kt( 'Lấy từ Wikipedia: chép nguyên văn mục Cách chơi; dòng "Nguồn: Wikipedia — <bài> ↗ · CC BY-SA 4.0" trỏ đúng bài đã lấy', str_contains( $cc2, '<li>Đoạn một thử.</li><li>Ý một</li><li>Ý hai thử</li>' ) && str_contains( $cc2, 'Nguồn: <a href="https://vi.wikipedia.org/wiki/Tr%C3%B2_ch%C6%A1i_th%E1%BB%AD_Wiki" rel="noopener">Wikipedia — Trò chơi thử Wiki' ) && str_contains( $cc2, 'CC BY-SA 4.0</a>' ), $cc2 );
kt( 'Lấy từ Wikipedia: báo cho người sáng lập số dòng đã chép', str_contains( (string) ( get_transient( 'cadao_cc_bao_' . get_current_user_id() )[1] ?? '' ), 'Đã chép 3 dòng' ) );
kt( 'trang Nguồn: có nguồn của cách chơi', str_contains( do_shortcode( '[cadao_nguon]' ), 'Wikipedia — Trò chơi thử Wiki' ) );
$_POST = array( 'cadao_cc' => array( 'buoc' => 'giữ nguyên' ), 'cadao_cc_wiki' => 'https://example.org/x', 'cadao_cc_lay_wiki' => '1' );
wp_update_term( $tc1->term_id, 'tro_choi', array( 'name' => 'Trò chơi thử' ) );
kt( 'Lấy từ Wikipedia: đường dẫn không phải Wikipedia tiếng Việt thì báo lỗi, không ghi nguồn', str_contains( (string) ( get_transient( 'cadao_cc_bao_' . get_current_user_id() )[1] ?? '' ), 'Wikipedia tiếng Việt' ) && 'giữ nguyên' === cadao_tro_choi_o( $tc1->term_id, 'buoc' ) );
$_POST = array();
remove_filter( 'cadao_nhip_giay', '__return_zero' );
wp_set_current_user( 0 );

// ---------- Bản đồ ca dao (T-104) ----------
$bd = static function ( string $tieu, string $tu, string $nd, ?array $toa ) {
	wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => $tieu, 'post_content' => cadao_khoi_tho( $tieu . ' dòng một' ) . "\n\n<!-- wp:list {\"className\":\"cadao-chu-thich\"} -->\n<ul class=\"wp-block-list cadao-chu-thich\">\n<!-- wp:list-item -->\n<li><strong>" . $tu . '</strong>: ' . $nd . "</li>\n<!-- /wp:list-item -->\n</ul>\n<!-- /wp:list -->" ) );
	$muc = cadao_muc_tu( array( 'tu' => $tu, 'noi_dung' => $nd ), false );
	update_term_meta( $muc->term_id, '_cadao_wd', array( 'nhan_wd' => $tu, 'mo_ta' => 'mô tả thử', 'toa_do' => $toa, 'wiki' => '' ) );
	return $muc;
};
$m_bac   = $bd( 'Bản đồ thử bắc', 'Làng Bắc Thử', 'Làng thử phía bắc.', array( 21.0, 105.8 ) );
$m_gan   = $bd( 'Bản đồ thử gần', 'Làng Gần Thử', 'Làng thử gần làng bắc.', array( 21.2, 105.9 ) );
$m_nam   = $bd( 'Bản đồ thử nam', 'Làng Nam Thử', 'Làng thử phía nam.', array( 8.6, 104.8 ) ); // xa Đò Thử (10.03, 105.78) ở phần Wikidata
$m_xa    = $bd( 'Bản đồ thử ngoài khung', 'Nơi Xa Thử', 'Nơi thử ngoài khung bản đồ.', array( 48.8, 2.3 ) );
$m_khong = $bd( 'Bản đồ thử không tọa độ', 'Nơi Không Thử', 'Nơi thử không có tọa độ.', null );
$diem    = cadao_ban_do_diem();
$ten_d   = array_map( static fn( $d ) => $d['muc']->name, $diem );
kt( 'bản đồ: chỉ địa danh có tọa độ trong khung và có bài đang đăng, xếp từ Bắc vào Nam', array_search( 'Làng Gần Thử', $ten_d, true ) < array_search( 'Làng Nam Thử', $ten_d, true ) && in_array( 'Làng Bắc Thử', $ten_d, true ) && ! in_array( 'Nơi Xa Thử', $ten_d, true ) && ! in_array( 'Nơi Không Thử', $ten_d, true ), $ten_d );
$nhom = cadao_ban_do_gom( array_values( array_filter( $diem, static fn( $d ) => str_starts_with( $d['muc']->name, 'Làng' ) ) ) );
kt( 'bản đồ: điểm gần nhau gộp thành một (vùng chạm đủ lớn), điểm xa đứng riêng', 2 === count( $nhom ) && 2 === count( $nhom[0]['ds'] ), array_map( static fn( $n ) => count( $n['ds'] ), $nhom ) );
$tr_bd = do_shortcode( '[cadao_ban_do]' );
kt( 'trang bản đồ: điểm là liên kết có tên đọc được, tới danh sách bài dưới bản đồ; không gọi dịch vụ bản đồ ngoài', str_contains( $tr_bd, 'href="#dd-' . $m_nam->term_id . '"' ) && str_contains( $tr_bd, 'Làng Nam Thử — 1 bài' ) && str_contains( $tr_bd, 'id="dd-' . $m_nam->term_id . '"' ) && str_contains( $tr_bd, 'Bản đồ thử nam' ) && ! preg_match( '~openstreetmap|googleapis|tile|leaflet~i', $tr_bd ) );
update_term_meta( $m_bac->term_id, '_cadao_wd', array( 'nhan_wd' => 'Làng Bắc Thử', 'mo_ta' => 'ward in Vietnam', 'toa_do' => array( 21.0, 105.8 ), 'wiki' => '' ) );
kt( 'trang bản đồ: mô tả Wikidata chỉ hiện khi là tiếng Việt', ! str_contains( do_shortcode( '[cadao_ban_do]' ), 'ward in Vietnam' ) && str_contains( do_shortcode( '[cadao_ban_do]' ), 'mô tả thử' ) );
kt( 'trang "Bản đồ ca dao" có sẵn ở /ban-do/', ( $p_bd = get_page_by_path( 'ban-do', OBJECT, 'page' ) ) && 'publish' === $p_bd->post_status );
kt( 'trang mục từ có tọa độ: "Xem trên bản đồ ca dao"', str_contains( apply_filters( 'term_description', '', $m_nam->term_id, 'tu_kho', 'tu_kho' ), '#dd-' . $m_nam->term_id . '">Xem trên bản đồ ca dao' ) );
kt( 'trang chủ, chân trang: liên kết tới bản đồ', str_contains( do_shortcode( '[cadao_trang_chu]' ), 'Xem ca dao trên bản đồ' ) && str_contains( do_shortcode( '[cadao_chan_trang]' ), 'Bản đồ ca dao' ) );

// ---------- Lời vào khối Thơ (T-112): bài dán vào khối Đoạn văn ----------
$p_ = static fn( string $h ) => "<!-- wp:paragraph -->\n<p>$h</p>\n<!-- /wp:paragraph -->";
$loi_kt = static fn( string $nd ) => cadao_loi_cac_khoi_tho( cadao_de_xuat_khoi_tho( $nd )['noi_dung'] ?? '' );
// Dạng 1: đoạn trống, rồi một đoạn có dòng chú in nghiêng và lời chung một đoạn
$nd1 = $p_( '' ) . "\n\n" . $p_( '<em>(Chú thích thử về bài vè, năm 1886.)</em><br>Dòng kt một,<br>Dòng kt hai &amp; ba,<br>Dòng kt bốn.' );
$dx1 = cadao_de_xuat_khoi_tho( $nd1 );
kt( 'khối Thơ: dòng chú in nghiêng giữ là đoạn văn, lời vào khối Thơ đúng từng ký tự, bỏ đoạn trống', $dx1 && array( "Dòng kt một,\nDòng kt hai & ba,\nDòng kt bốn." ) === $loi_kt( $nd1 ) && array( '(Chú thích thử về bài vè, năm 1886.)' ) === $dx1['ghi_chu'] && str_starts_with( $dx1['noi_dung'], "<!-- wp:paragraph -->\n<p><em>(Chú thích" ), $dx1 );
// Dạng 2: mỗi dòng một đoạn văn → một khối Thơ, không chèn dòng trống
$nd2 = implode( "\n\n", array_map( $p_, array( '', 'Dòng lẻ một,', 'Dòng lẻ hai.', 'Dòng lẻ ba,', 'Dòng lẻ bốn.' ) ) );
kt( 'khối Thơ: mỗi dòng một đoạn văn → các dòng liền nhau trong một khối Thơ', array( "Dòng lẻ một,\nDòng lẻ hai.\nDòng lẻ ba,\nDòng lẻ bốn." ) === $loi_kt( $nd2 ), $loi_kt( $nd2 ) );
// Dạng 3: đoạn chú trong ngoặc riêng; hai đoạn nhiều dòng → hai khổ
$nd3 = $p_( '<em>(Bài thử tả một trận đánh)</em>' ) . "\n\n" . $p_( 'Khổ một dòng một<br>Khổ một dòng hai' ) . "\n\n" . $p_( '<br>Khổ hai dòng một<br>Khổ hai dòng hai' );
kt( 'khối Thơ: hai đoạn nhiều dòng → hai khổ cách một dòng trống; dòng trống đầu đoạn bỏ qua', array( "Khổ một dòng một\nKhổ một dòng hai\n\nKhổ hai dòng một\nKhổ hai dòng hai" ) === $loi_kt( $nd3 ) && 1 === count( cadao_de_xuat_khoi_tho( $nd3 )['ghi_chu'] ), $loi_kt( $nd3 ) );
// Dạng 4: nhiều phần dưới tiêu đề h3 → tiêu đề giữ, mỗi phần một khối Thơ
$nd4 = "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">Phần thử một</h3>\n<!-- /wp:heading -->\n\n" . $p_( 'Phần một dòng a,<br>Phần một dòng b.' ) . "\n\n<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">Phần thử hai</h3>\n<!-- /wp:heading -->\n\n" . $p_( 'Phần hai dòng a,<br>Phần hai dòng b.' );
$dx4 = cadao_de_xuat_khoi_tho( $nd4 );
kt( 'khối Thơ: phần dưới tiêu đề → tiêu đề giữ nguyên, mỗi phần một khối Thơ', $dx4 && 2 === $dx4['so_tho'] && 4 === $dx4['so_dong'] && 2 === substr_count( $dx4['noi_dung'], '<h3 class="wp-block-heading">' ) && array( "Phần một dòng a,\nPhần một dòng b.", "Phần hai dòng a,\nPhần hai dòng b." ) === $loi_kt( $nd4 ) );
kt( 'khối Thơ: chữ trước và sau khi chuyển giống hệt (kể cả &nbsp;, chữ in nghiêng trong lời)', cadao_chu_de_so( $nd1 ) === cadao_chu_de_so( $dx1['noi_dung'] ) && null !== cadao_de_xuat_khoi_tho( $p_( 'Lời có&nbsp;cách cứng<br>Lời có <strong>chữ đậm</strong> thử' ) ) );
kt( 'khối Thơ: bài đã có khối Thơ, bài chỉ có lời chú: không đề xuất', null === cadao_de_xuat_khoi_tho( cadao_khoi_tho( "Đã có\nkhối Thơ" ) . "\n\n" . $p_( 'Đoạn thêm' ) ) && null === cadao_de_xuat_khoi_tho( $p_( '<em>(Chỉ có lời chú)</em>' ) ) );
$kt_bai = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Vè thử dán đoạn văn', 'post_content' => wp_slash( $nd1 ) ) );
kt( 'trang "Lời chưa vào khối Thơ" có bài này; trang bài chưa có hàng nút', isset( cadao_bai_chua_khoi_tho()[ $kt_bai ] ) && '' === cadao_khoi_hanh_dong( get_post( $kt_bai ) ) && ! in_array( $kt_bai, array_column( cadao_tim( 'dong kt hai' ), 'id' ), true ) );
wp_set_current_user( cadao_quan_tri_dau_tien() );
ob_start();
cadao_trang_khoi_tho();
$tr_kt = ob_get_clean();
kt( 'trang "Lời chưa vào khối Thơ": xem trước lời, dòng chú giữ lại, nút Chuyển', str_contains( $tr_kt, 'Vè thử dán đoạn văn' ) && str_contains( $tr_kt, 'Lời: 3 dòng trong 1 khối Thơ.' ) && str_contains( $tr_kt, 'Giữ là đoạn văn: (Chú thích thử' ) && str_contains( $tr_kt, 'value="' . $kt_bai . '">Chuyển bài này' ), substr( $tr_kt, 0, 600 ) );
$so_sua = count( wp_get_post_revisions( $kt_bai ) );
kt( 'Chuyển: bài có khối Thơ, lời đúng từng ký tự; có hàng nút; tìm theo lời ra bài; có bản sửa đổi để hoàn tác', cadao_dua_vao_khoi_tho( $kt_bai ) && array( "Dòng kt một,\nDòng kt hai & ba,\nDòng kt bốn." ) === cadao_loi_cac_khoi_tho( get_post( $kt_bai )->post_content ) && '' !== cadao_khoi_hanh_dong( get_post( $kt_bai ) ) && in_array( $kt_bai, array_column( cadao_tim( 'dong kt hai' ), 'id' ), true ) && count( wp_get_post_revisions( $kt_bai ) ) > $so_sua && ! isset( cadao_bai_chua_khoi_tho()[ $kt_bai ] ) );
$yc_kt = new WP_REST_Request( 'POST', '/cadao/v1/khoi-tho' );
$yc_kt->set_param( 'noi_dung', $nd2 );
$r_kt = rest_do_request( $yc_kt )->get_data();
kt( 'REST cho trình soạn: trả nội dung đã chuyển (người biên soạn)', str_contains( (string) ( $r_kt['noi_dung'] ?? '' ), '<!-- wp:verse -->' ) && 4 === ( $r_kt['so_dong'] ?? 0 ), $r_kt );
wp_set_current_user( 0 );
kt( 'REST cho trình soạn: người lạ không dùng được', 401 === rest_do_request( $yc_kt )->get_status() );
kt( 'trình soạn: ô báo "Lời đang nằm trong khối Đoạn văn" và nút "Đưa lời vào khối Thơ"', str_contains( file_get_contents( CADAO_THU_MUC . '/assets/soan-trung.js' ), "'Đưa lời vào khối Thơ'" ) );

// ---------- Bàn biên tập (T-047) ----------
$hc_truoc = array_column( cadao_hang_cho(), 'so', 'khoa' );
$gy_loi   = wp_insert_post( array( 'post_type' => CADAO_LOAI_GOP_Y, 'post_status' => 'pending', 'post_title' => 'Báo lỗi thử', 'post_content' => 'Sai chữ', 'meta_input' => array( '_cadao_loai' => 'bao_loi' ) ) );
$gy_khac  = wp_insert_post( array( 'post_type' => CADAO_LOAI_GOP_Y, 'post_status' => 'pending', 'post_title' => 'Dị bản thử', 'post_content' => 'Ở quê nói khác', 'meta_input' => array( '_cadao_loai' => 'di_ban' ) ) );
$cd_bbt   = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'pending', 'post_title' => 'Chờ duyệt thử bàn', 'post_content' => cadao_khoi_tho( 'Chờ duyệt thử bàn biên tập' ) ) );
$hc       = cadao_hang_cho();
$hc_so    = array_column( $hc, 'so', 'khoa' );
kt( 'bàn biên tập: báo lỗi của người đọc đứng đầu', 'bao_loi' === $hc[0]['khoa'] );
kt( 'bàn biên tập: đếm đúng báo lỗi, góp ý khác, bài Chờ duyệt', $hc_so['bao_loi'] === $hc_truoc['bao_loi'] + 1 && $hc_so['gop_y'] === $hc_truoc['gop_y'] + 1 && $hc_so['cho_duyet'] === $hc_truoc['cho_duyet'] + 1, array( $hc_truoc, $hc_so ) );
kt( 'bàn biên tập: gom đủ các hàng chờ (đóng góp người đọc, Chờ duyệt, khối Thơ, trùng, Wikidata, gợi ý, Bạn có biết?, trang dự án, thể loại)', array() === array_diff( array( 'bao_loi', 'gop_y', 'binh_luan', 'phan_tich', 'cho_duyet', 'khoi_tho', 'trung', 'wikidata', 'goi_y', 'bcb', 'du_an', 'the_loai' ), array_keys( $hc_so ) ), array_keys( $hc_so ) );
kt( 'bàn biên tập: mỗi hàng có đường dẫn trong trang quản trị và một dòng "làm gì"', array() === array_filter( $hc, static fn( $h ) => ! str_starts_with( $h['url'], admin_url() ) || '' === $h['viec'] ) );
wp_set_current_user( cadao_quan_tri_dau_tien() );
ob_start();
cadao_trang_ban_bien_tap();
$tr_bbt = ob_get_clean();
wp_set_current_user( 0 );
kt( 'trang Bàn biên tập: hàng còn việc có số và nút Mở; hàng đã xong gom ở cuối', str_contains( $tr_bbt, 'Báo lỗi, báo vi phạm của người đọc' ) && str_contains( $tr_bbt, '>Mở</a>' ) && ( ! array_filter( $hc, static fn( $h ) => $h['so'] <= 0 ) || str_contains( $tr_bbt, '<h2>Đã xong</h2>' ) ), substr( $tr_bbt, 0, 400 ) );
kt( 'bàn biên tập: "Làm trước" có báo lỗi, góp ý; bài Chờ duyệt thu vào "Khi rảnh" (T-121)', $hc[0]['truoc'] && str_contains( $tr_bbt, '<h2>Làm trước</h2>' ) && strpos( $tr_bbt, 'Góp ý khác của người đọc' ) < strpos( $tr_bbt, 'Khi rảnh (' ) && strpos( $tr_bbt, 'Khi rảnh (' ) < strpos( $tr_bbt, 'Bài Chờ duyệt' ), substr( $tr_bbt, 0, 600 ) );
wp_delete_post( $gy_loi, true );
wp_delete_post( $gy_khac, true );
wp_delete_post( $cd_bbt, true );

// ---------- Sửa mục từ: nguồn của lời giải, ảnh (T-113) ----------
$mt     = get_term( $m_do->term_id, 'tu_kho' );
$mt_mo  = static fn() => apply_filters( 'term_description', '', $m_do->term_id, 'tu_kho', 'tu_kho' );
$mt_wd  = cadao_wd_cua_muc_tu( $m_do->term_id );
$mt_anh = (int) ( $mt_wd['anh_da_tai']['id'] ?? 0 );
$mt_cu  = array( (string) get_term_meta( $mt->term_id, '_cadao_nguon_ten', true ), (string) get_term_meta( $mt->term_id, '_cadao_nguon_url', true ) );
ob_start();
do_action( 'tu_kho_edit_form_fields', $mt, 'tu_kho' );
$mt_form = ob_get_clean();
kt( 'Sửa mục từ: ô nguồn điền sẵn giá trị đang có, ô ảnh, liên kết sửa Wikidata khớp nhầm', str_contains( $mt_form, 'name="cadao_mt[nguon_ten]" value="' . esc_attr( $mt_cu[0] ) . '"' ) && str_contains( $mt_form, 'Chọn ảnh khác…' ) && str_contains( $mt_form, 'tab=dung' ) && str_contains( $mt_form, 'Không hiện ảnh lấy từ Wikidata' ) );
wp_set_current_user( cadao_quan_tri_dau_tien() );
$_POST = array( 'cadao_mt' => array( 'nguon_ten' => '', 'nguon_url' => '', 'anh_rieng' => '', 'ghi_cong' => '' ) );
wp_update_term( $mt->term_id, 'tu_kho', array( 'description' => $mt->description ) );
kt( 'Sửa mục từ: xóa nguồn (tự viết lại lời giải) → trang mục từ không còn dòng Nguồn', ! str_contains( $mt_mo(), 'cadao-chu-thich-nguon' ) && str_contains( $mt_mo(), 'cadao-anh-wd' ) );
$_POST = array( 'cadao_mt' => array( 'nguon_ten' => 'Sách mục từ thử', 'nguon_url' => 'https://example.org/sach', 'anh_rieng' => '', 'ghi_cong' => '', 'an_anh' => '1' ) );
wp_update_term( $mt->term_id, 'tu_kho', array( 'description' => $mt->description ) );
kt( 'Sửa mục từ: ghi nguồn khác; ẩn ảnh Wikidata (bản đồ, Wikipedia vẫn còn)', str_contains( $mt_mo(), 'Nguồn: <a href="https://example.org/sach" rel="noopener">Sách mục từ thử' ) && ! str_contains( $mt_mo(), 'cadao-anh-wd' ) && str_contains( $mt_mo(), 'cadao-ban-do-nho' ) );
if ( $mt_anh ) {
	$_POST = array( 'cadao_mt' => array( 'nguon_ten' => 'Sách mục từ thử', 'nguon_url' => '', 'anh_rieng' => (string) $mt_anh, 'ghi_cong' => 'Ảnh: Người Chụp Riêng · CC BY 4.0' ) );
	wp_update_term( $mt->term_id, 'tu_kho', array( 'description' => $mt->description ) );
	$mo_rieng = $mt_mo();
	kt( 'Sửa mục từ: ảnh chọn từ Thư viện thay ảnh Wikidata, ghi công do người biên soạn viết, chữ thay thế là tên mục từ; ô chạm từ trong bài cũng dùng ảnh đó', str_contains( $mo_rieng, '<figcaption>Ảnh: Người Chụp Riêng · CC BY 4.0</figcaption>' ) && str_contains( $mo_rieng, 'alt="' . esc_attr( $mt->name ) . '"' ) && ! str_contains( $mo_rieng, 'Wikimedia Commons</a></figcaption>' ) && str_contains( cadao_them_cho_chu_thich( 'Đò Thử', 'Bến đò mẫu ở Nam Bộ.' )['them'], 'Người Chụp Riêng' ), $mo_rieng );
}
$_POST = array( 'cadao_mt' => array( 'nguon_ten' => 'Bịa', 'anh_rieng' => '99999999' ) );
wp_set_current_user( 0 );
wp_update_term( $mt->term_id, 'tu_kho', array( 'description' => $mt->description ) );
kt( 'Sửa mục từ: người không có quyền không đổi được', 'Bịa' !== get_term_meta( $mt->term_id, '_cadao_nguon_ten', true ) );
// Trả lại như cũ cho các phần sau
update_term_meta( $mt->term_id, '_cadao_nguon_ten', $mt_cu[0] );
update_term_meta( $mt->term_id, '_cadao_nguon_url', $mt_cu[1] );
delete_term_meta( $mt->term_id, '_cadao_anh_rieng' );
delete_term_meta( $mt->term_id, '_cadao_an_anh' );
$_POST = array();

// ---------- D-068: bỏ dòng "Nguồn" dưới chú thích chép; trang Chính sách thu thập dữ liệu (T-114) ----------
kt( 'nguồn tự động mới: mặc định không ghi dòng Nguồn dưới chú thích (bật lại được)', false === cadao_nguon_mac_dinh()['ghi_cong'] );
cadao_nguon_luu( array( 'id' => 'cadao-me-thu', 'ten' => 'Ca dao Mẹ thử', 'url' => 'https://cadao.me', 'ghi_cong' => false ) + cadao_nguon_mac_dinh() );
cadao_nguon_luu( array( 'id' => 'bat-thu', 'ten' => 'Bật thử', 'url' => 'https://bat-thu.example', 'ghi_cong' => true ) + cadao_nguon_mac_dinh() );
kt( 'tên miền không ghi nguồn: lấy từ nguồn đã tắt ghi nguồn', in_array( 'cadao.me', cadao_mien_khong_ghi_nguon(), true ) && ! in_array( 'bat-thu.example', cadao_mien_khong_ghi_nguon(), true ) );
$ds_068 = "<!-- wp:list {\"className\":\"cadao-chu-thich\"} -->\n<ul class=\"wp-block-list cadao-chu-thich\">\n<!-- wp:list-item -->\n<li><strong>chữ068</strong>: Lời giải chép thử &amp; nguyên văn.</li>\n<!-- /wp:list-item -->\n</ul>\n<!-- /wp:list -->";
$ng_068 = static fn( string $url, string $ten ) => cadao_khoi_doan( 'Nguồn: <a href="' . $url . '" rel="noopener">' . $ten . ' <span aria-hidden="true">↗</span></a>', 'cadao-chu-thich-nguon' );
$nd_068 = cadao_khoi_tho( "Lời thử 068 một\nLời thử 068 hai" ) . "\n\n" . cadao_khoi_tieu_de( 'Chú thích' ) . "\n\n" . $ds_068 . "\n\n" . $ng_068( 'https://cadao.me/loi-thu-068/', 'Ca dao Mẹ' ) . "\n\n" . cadao_khoi_doan( 'Đoạn người biên soạn viết thêm.' ) . "\n\n" . $ng_068( 'https://sach.example/trang-12', 'Sách khác' );
$b_068  = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Bài thử bỏ nguồn', 'post_content' => wp_slash( $nd_068 ) ) );
$m_068  = cadao_muc_tu( array( 'tu' => 'chữ068', 'noi_dung' => 'Lời giải chép thử & nguyên văn.' ), false );
kt( 'trước khi dọn: mục từ mang nguồn Ca dao Mẹ (như trên site hiện nay)', $m_068 && 'Ca dao Mẹ' === get_term_meta( $m_068->term_id, '_cadao_nguon_ten', true ) );
$sua_068 = count( wp_get_post_revisions( $b_068 ) );
cadao_don_dong_nguon( microtime( true ) + 20 );
$sau_068 = get_post( $b_068 )->post_content;
kt( 'dọn: bỏ đúng dòng "Nguồn: Ca dao Mẹ"; nguồn khác người biên soạn ghi vẫn giữ; lời, chú thích, đoạn viết thêm không đổi chữ', ! str_contains( $sau_068, 'cadao.me' ) && str_contains( $sau_068, 'https://sach.example/trang-12' ) && str_contains( $sau_068, 'Lời giải chép thử &amp; nguyên văn.' ) && str_contains( $sau_068, 'Đoạn người biên soạn viết thêm.' ) && array( "Lời thử 068 một\nLời thử 068 hai" ) === cadao_loi_cac_khoi_tho( $sau_068 ), $sau_068 );
kt( 'dọn: danh sách chú thích chép được đánh dấu (không hiện ra trang), khối hợp lệ; có bản sửa đổi để hoàn tác', str_contains( $sau_068, '{"className":"cadao-chu-thich cadao-chep"}' ) && str_contains( $sau_068, 'class="wp-block-list cadao-chu-thich cadao-chep"' ) && true === cadao_chu_thich_cua_bai( $sau_068 )[0]['chep'] && count( wp_get_post_revisions( $b_068 ) ) > $sua_068 );
kt( 'dọn: mục từ hết dòng Nguồn nhưng vẫn mang dấu "chép" (giữ noindex)', '' === (string) get_term_meta( $m_068->term_id, '_cadao_nguon_ten', true ) && get_term_meta( $m_068->term_id, '_cadao_chep', true ) && ! str_contains( apply_filters( 'term_description', '', $m_068->term_id, 'tu_kho', 'tu_kho' ), 'cadao-chu-thich-nguon' ) );
kt( 'dọn: chạy lại không đổi gì thêm', 0 === cadao_don_dong_nguon( microtime( true ) + 20 ) && $sau_068 === get_post( $b_068 )->post_content );
$m_068b = cadao_muc_tu( array( 'tu' => 'chữ068b', 'noi_dung' => 'Mục từ mới từ danh sách chép.', 'nguon_ten' => '', 'nguon_url' => '', 'chep' => true ) );
kt( 'mục từ mới tạo từ chú thích chép (không còn dòng Nguồn) vẫn mang dấu "chép"', (bool) get_term_meta( $m_068b->term_id, '_cadao_chep', true ) );
$p_cs = get_page_by_path( 'chinh-sach-du-lieu', OBJECT, 'page' );
kt( 'trang "Chính sách thu thập dữ liệu" đã đăng ở /chinh-sach-du-lieu/, có danh sách nguồn tự sinh', $p_cs && 'publish' === $p_cs->post_status && str_contains( $p_cs->post_content, '[cadao_nguon]' ) && str_contains( $p_cs->post_content, 'Xin gỡ, xin sửa' ) );
kt( 'chân trang có "Chính sách dữ liệu"; trang Phương pháp (nháp) trỏ tới chính sách, không còn hứa dòng Nguồn dưới chú thích', str_contains( do_shortcode( '[cadao_chan_trang]' ), 'Chính sách dữ liệu' ) && ( $pp_068 = get_page_by_path( 'phuong-phap', OBJECT, 'page' ) ) && str_contains( $pp_068->post_content, '/chinh-sach-du-lieu/' ) && ! str_contains( $pp_068->post_content, 'luôn có dòng' ) );
kt( 'danh sách nguồn: nêu trang đã thu thập và số bài đang đăng lấy từ đó', str_contains( do_shortcode( '[cadao_nguon]' ), 'bài đang đăng được thu thập từ trang này' ) );

// ---------- Số liệu của bài: 👁 đọc · ↗ chia sẻ · ❝ trích dẫn (T-115, D-069) ----------
kt( 'số rút gọn: 999 · 1,2K · 15K · 1,2M', '999' === cadao_so_gon( 999 ) && '1K' === cadao_so_gon( 1000 ) && '1,2K' === cadao_so_gon( 1234 ) && '15K' === cadao_so_gon( 15200 ) && '1,2M' === cadao_so_gon( 1250000 ) );
$sl_bai = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Bài thử số liệu', 'post_content' => cadao_khoi_tho( "Số liệu thử một\nSố liệu thử hai" ) ) );
kt( 'chưa đủ ngưỡng: không có dòng số liệu', '' === cadao_html_so_lieu( $sl_bai ) );
for ( $i = 0; $i < 12; $i++ ) {
	cadao_luot_doc_cong( $sl_bai );
}
$may_sl = 0;
$sk_sl  = static function ( array $du ) use ( &$may_sl ) {
	$_SERVER['REMOTE_ADDR'] = '198.51.101.' . ( ++$may_sl % 200 + 10 ); // mỗi lần một người đọc khác
	$yc                     = new WP_REST_Request( 'POST', '/cadao/v1/su-kien' );
	$yc->set_header( 'user-agent', 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Mobile Safari/537.36' );
	$yc->set_header( 'content-type', 'application/json' );
	$yc->set_body( wp_json_encode( $du ) );
	return rest_do_request( $yc )->get_data()['tinh'] ?? null;
};
kt( 'chia sẻ: nhận đúng hai kiểu (hộp chia sẻ, sao chép liên kết); kiểu lạ, bài chưa đăng thì không', true === $sk_sl( array( 'loai' => 'chia_se', 'bai' => $sl_bai, 'kieu' => 'web_share' ) ) && true === $sk_sl( array( 'loai' => 'chia_se', 'bai' => $sl_bai, 'kieu' => 'sao_chep_lien_ket' ) ) && false === $sk_sl( array( 'loai' => 'chia_se', 'bai' => $sl_bai, 'kieu' => 'facebook' ) ) && false === $sk_sl( array( 'loai' => 'chia_se', 'bai' => $bai_cd1->ID, 'kieu' => 'web_share' ) ) );
$sk_sl( array( 'loai' => 'trich_dan_sao_chep', 'bai' => $sl_bai, 'kieu' => 'chi_loi' ) );
$sk_sl( array( 'loai' => 'trich_dan_sao_chep', 'bai' => $sl_bai, 'kieu' => 'apa' ) );
kt( 'số liệu của bài: đọc, chia sẻ, trích dẫn (chỉ tính sao chép kèm nguồn)', array( 'doc' => 12, 'chia_se' => 2, 'trich_dan' => 1 ) === cadao_so_lieu_bai( $sl_bai ), cadao_so_lieu_bai( $sl_bai ) );
$h_sl = cadao_html_so_lieu( $sl_bai );
kt( 'đọc ≥ 10 thì hiện; chia sẻ, trích dẫn dưới 3 thì ẩn; icon ẩn với trình đọc màn hình, có câu đầy đủ', str_contains( $h_sl, '<span class="screen-reader-text">12 lượt đọc</span>' ) && ! str_contains( $h_sl, 'lượt chia sẻ' ) && ! str_contains( $h_sl, 'lượt trích dẫn' ) && str_contains( $h_sl, 'aria-hidden="true" focusable="false"' ), $h_sl );
$sk_sl( array( 'loai' => 'chia_se', 'bai' => $sl_bai, 'kieu' => 'web_share' ) );
$sk_sl( array( 'loai' => 'trich_dan_sao_chep', 'bai' => $sl_bai, 'kieu' => 'thong_dung' ) );
$sk_sl( array( 'loai' => 'trich_dan_sao_chep', 'bai' => $sl_bai, 'kieu' => 'apa' ) );
$h_sl = cadao_html_so_lieu( $sl_bai );
kt( 'đủ 3 lượt: hiện cả chia sẻ và trích dẫn', str_contains( $h_sl, '3 lượt chia sẻ' ) && str_contains( $h_sl, '3 lượt trích dẫn' ), $h_sl );
$tieu_ds = ( new WP_Block( array( 'blockName' => 'core/post-title', 'attrs' => array( 'isLink' => true ), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ), array( 'postId' => $sl_bai, 'postType' => CADAO_LOAI_BAI, 'queryId' => 1 ) ) )->render();
kt( 'danh sách bài (vòng lặp): dưới tiêu đề chỉ có 👁', str_contains( $tieu_ds, 'cadao-so-lieu-ds' ) && str_contains( $tieu_ds, '12 lượt đọc' ) && ! str_contains( $tieu_ds, 'lượt chia sẻ' ), $tieu_ds );
kt( 'nút Chia sẻ: chỉ tính khi gửi xong (hủy thì không), sao chép liên kết cũng tính; mỗi máy một lần mỗi ngày', (bool) preg_match( "/\\.then\\(function \\(\\) \\{ daChiaSe\\('web_share'\\); \\}/", file_get_contents( CADAO_THU_MUC . '/assets/ca-dao.js' ) ) && str_contains( file_get_contents( CADAO_THU_MUC . '/assets/ca-dao.js' ), "daChiaSe('sao_chep_lien_ket')" ) && str_contains( file_get_contents( CADAO_THU_MUC . '/assets/ca-dao.js' ), "'cadao-da-chia-se'" ) );

$ngan = do_blocks( cadao_khoi_tho( implode( "\n", array_slice( $khong_kho, 0, 40 ) ) ) );
kt( 'bài 40 dòng trở xuống: không chia đoạn', ! str_contains( $ngan, 'cadao-bai-dai' ) );

// ---------- Dòng "lời và mức kiểm" (T-119, D-072) ----------
$mk_bai = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Bài thử mức kiểm', 'post_content' => cadao_khoi_tho( "Mức kiểm thử một\nMức kiểm thử hai" ) ) );
$pp_mk  = get_page_by_path( 'phuong-phap', OBJECT, 'page' );
wp_update_post( array( 'ID' => $pp_mk->ID, 'post_status' => 'draft' ) );
$h_mk = cadao_html_muc_kiem( $mk_bai );
kt( 'mặc định: "chưa đối chiếu sách"; trang Phương pháp chưa đăng thì không có "Vì sao?"', str_contains( $h_mk, 'Lời theo bản lưu truyền phổ biến · chưa đối chiếu sách' ) && ! str_contains( $h_mk, 'Vì sao?' ) && ! str_contains( $h_mk, 'cadao-muc-kiem-da' ), $h_mk );
update_post_meta( $mk_bai, 'nguon_ten', 'Nguồn chú thích thử' );
kt( 'nguồn của chú thích (hộp Nguồn cũ) không biến thành "đã đối chiếu"', ! cadao_muc_kiem( $mk_bai )['da_doi_chieu'] && str_contains( cadao_html_muc_kiem( $mk_bai ), 'chưa đối chiếu sách' ) );
wp_publish_post( $pp_mk->ID );
$h_mk = cadao_html_muc_kiem( $mk_bai );
kt( 'trang Phương pháp đã đăng: "Vì sao?" dẫn tới mục #muc-kiem, có chữ cho trình đọc màn hình', str_contains( $h_mk, '#muc-kiem">Vì sao?<span class="screen-reader-text">' ), $h_mk );
update_post_meta( $mk_bai, 'doi_chieu_ten', 'Sách <b>Thử</b> & Bàn' ); // thẻ bị lọc khi lưu, & được thoát khi hiện
update_post_meta( $mk_bai, 'doi_chieu_trang', '45' );
$h_mk = cadao_html_muc_kiem( $mk_bai );
kt( 'người biên soạn điền đối chiếu: "Lời đã đối chiếu với <sách>, tr. N" (chữ được thoát)', str_contains( $h_mk, 'Lời đã đối chiếu với Sách Thử &amp; Bàn, tr. 45' ) && str_contains( $h_mk, 'cadao-muc-kiem-da' ) && ! str_contains( $h_mk, 'chưa đối chiếu' ), $h_mk );
update_post_meta( $mk_bai, 'doi_chieu_ten', '' );
update_post_meta( $mk_bai, 'doi_chieu_trang', '' );
update_post_meta( $mk_bai, 'doi_chieu_url', 'https://sach.example.org/ban-quet' );
$h_mk = cadao_html_muc_kiem( $mk_bai );
kt( 'chỉ có địa chỉ: tên là tên miền, có liên kết ↗', str_contains( $h_mk, 'Lời đã đối chiếu với <a href="https://sach.example.org/ban-quet" rel="noopener">sach.example.org <span aria-hidden="true">↗</span></a>' ), $h_mk );
update_post_meta( $mk_bai, 'doi_chieu_ten', 'Sách thử đối chiếu' );
update_post_meta( $mk_bai, 'doi_chieu_trang', '45' );
$tr_mk = cadao_mau_trich_dan( get_post( $mk_bai ) );
kt( 'trích dẫn nêu nơi đối chiếu khi có; bài chưa đối chiếu giữ nguyên', str_contains( $tr_mk['thong_dung'], '(Ca dao, lời đối chiếu với Sách thử đối chiếu, tr. 45)' ) && str_contains( $tr_mk['apa'], '[Ca dao; lời đối chiếu với Sách thử đối chiếu, tr. 45]' ) && ! str_contains( implode( '', cadao_mau_trich_dan( get_post( $td ) ) ), 'đối chiếu' ), $tr_mk );
$kq_mk = cadao_tim_ket_qua( 'muc kiem thu' );
kt( 'kết quả Tìm có mức kiểm ngắn', 'đã đối chiếu' === ( $kq_mk['bai'][0]['muc_kiem'] ?? '' ) && str_contains( cadao_tim_html( $kq_mk ), '· Ca dao · đã đối chiếu' ), $kq_mk['bai'][0] ?? null );
kt( 'dòng chèn ngay sau lời, trước Hiểu nhanh (theme chèn Hiểu nhanh sát </pre> ở ưu tiên 20, dòng này ở 25)', '<pre>l</pre>[M]<section class="cadao-hieu-nhanh">h</section>' === cadao_chen_muc_kiem( '<pre>l</pre><section class="cadao-hieu-nhanh">h</section>', '[M]' ) && 'không thơ' === cadao_chen_muc_kiem( 'không thơ', '[M]' ) );
kt( 'không dùng từ cấm trong dòng mức kiểm', ! preg_match( '/bản gốc|bản đúng|bản chuẩn|bản sai/u', CADAO_MUC_KIEM_CHUA . cadao_html_muc_kiem( $mk_bai ) . cadao_pp_muc_kiem() ) );
// Bản nháp Phương pháp cũ (chưa có mục) được thêm mục; trang đã đăng thì không đụng
wp_update_post( array( 'ID' => $pp_mk->ID, 'post_status' => 'draft', 'post_content' => wp_slash( "<!-- wp:paragraph -->\n<p>Đoạn đầu.</p>\n<!-- /wp:paragraph -->\n\n" . CADAO_PP_LOI_CU ) ) );
cadao_pp_them_muc_kiem();
cadao_pp_them_muc_kiem();
$pp_mk = get_post( $pp_mk->ID );
kt( 'bản nháp Phương pháp: thêm mục "Dòng nhỏ dưới lời nói gì" (#muc-kiem) một lần, giữ đoạn khác', 1 === substr_count( $pp_mk->post_content, 'id="muc-kiem"' ) && str_contains( $pp_mk->post_content, 'Đoạn đầu.' ) && str_contains( $pp_mk->post_content, 'dưới lời không ghi trang web đã chép' ) && ! str_contains( $pp_mk->post_content, CADAO_PP_LOI_CU ), $pp_mk->post_content );
wp_update_post( array( 'ID' => $pp_mk->ID, 'post_status' => 'publish', 'post_content' => wp_slash( CADAO_PP_LOI_CU ) ) );
cadao_pp_them_muc_kiem();
kt( 'trang Phương pháp đã đăng: máy không sửa', CADAO_PP_LOI_CU === get_post( $pp_mk->ID )->post_content );
kt( 'trang Phương pháp mới tạo đã có mục #muc-kiem', str_contains( cadao_noi_dung_trang_du_an( 'phuong-phap' ), 'id="muc-kiem"' ) );
ob_start();
cadao_hop_doi_chieu( get_post( $mk_bai ) );
$hop_dc = ob_get_clean();
kt( 'hộp "Lời đã đối chiếu với" có ba ô và lời nhắc chỉ điền khi tự so', str_contains( $hop_dc, 'name="doi_chieu_ten"' ) && str_contains( $hop_dc, 'name="doi_chieu_trang"' ) && str_contains( $hop_dc, 'name="doi_chieu_url"' ) && str_contains( $hop_dc, 'chính bạn' ) );

// ---------- Điều hướng, danh sách bài (T-120) ----------
kt( 'đường dẫn lọc: tham số riêng (không trùng tên phân loại), giữ bộ lọc khác, bỏ ô trống, thêm cách xếp', 'https://x.example/kho/?loai=ve&chude=gia-dinh&xep=moi' === cadao_url_ds( 'https://x.example/kho/', array( 'the_loai' => 've', 'vung' => '', 'chu_de' => 'gia-dinh' ), 'moi' ) && 'https://x.example/kho/' === cadao_url_ds( 'https://x.example/kho/', array(), 'az' ) );
kt( 'khóa xếp A–Z: bỏ dấu, chữ thường; "đ" sau mọi "d"', 'cau d{o' === cadao_khoa_xep( 'Câu Đố' ) && strcmp( cadao_khoa_xep( 'Câu' ), cadao_khoa_xep( 'Con' ) ) < 0 && strcmp( cadao_khoa_xep( 'Dừa' ), cadao_khoa_xep( 'Đá' ) ) < 0, cadao_khoa_xep( 'Câu Đố' ) );
$_GET = array( 'loai' => 'Ve', 'the' => '', 'mien' => 'nam-bo' );
kt( 'bộ lọc đọc từ đường dẫn; trang thể loại bỏ qua tham số thể loại', array( 'the_loai' => 've', 'vung' => 'nam-bo' ) === cadao_loc_dang() && array( 'vung' => 'nam-bo' ) === cadao_loc_dang( 'the_loai' ) );
$_GET = array();
$ds_goc = (string) get_post_type_archive_link( CADAO_LOAI_BAI );
$ds_loc = cadao_html_loc_ds( $ds_goc, array( 'the_loai' => 'ca-dao' ), 'az', 12 );
kt( 'khối lọc: chip đang chọn có ✓ và aria-current, bấm lại thì bỏ; "N bài" là h2; có "Bỏ lọc", A–Z đang chọn, "Mới thêm" là liên kết', str_contains( $ds_loc, 'aria-label="Lọc danh sách bài"' ) && (bool) preg_match( '~<a class="cadao-chip" href="' . preg_quote( esc_url( $ds_goc ), '~' ) . '" aria-current="true"><span aria-hidden="true">✓ </span>Ca dao~u', $ds_loc ) && str_contains( $ds_loc, '<h2 class="cadao-ds-tieu">12 bài' ) && str_contains( $ds_loc, 'Bỏ lọc' ) && str_contains( $ds_loc, '<strong aria-current="true">A–Z</strong>' ) && str_contains( $ds_loc, 'xep=moi' ), $ds_loc );
kt( 'trang thể loại: không lọc lại theo thể loại', ! str_contains( cadao_html_loc_ds( $ds_goc, array(), 'az', 3, 'the_loai' ), 'aria-label="Thể loại"' ) );
kt( 'trang Tất cả có tên "Tất cả bài"', 'Tất cả bài' === apply_filters( 'post_type_archive_title', 'Ca dao – Tục ngữ', CADAO_LOAI_BAI ) );
$ds_bai = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Danh sách thử một', 'post_content' => cadao_khoi_tho( "Danh sách thử một\nDanh sách thử hai\nDanh sách thử ba\nDanh sách thử bốn" ) ) );
wp_set_object_terms( $ds_bai, 've', 'the_loai' );
$ds_muc = cadao_html_muc_ds( get_post( $ds_bai ) );
kt( 'bài mới có khóa xếp', 'danh sach thu mot' === get_post_meta( $ds_bai, '_cadao_ten_xep', true ) );
kt( 'mục trong danh sách: 2 dòng đầu (bỏ dòng trùng tiêu đề), dấu … khi còn, thể loại', str_contains( $ds_muc, 'Danh sách thử hai<br>Danh sách thử ba…' ) && ! str_contains( $ds_muc, 'thử một' ) && str_contains( $ds_muc, '<p class="cadao-ds-phu">Vè' ), $ds_muc );
$ds_html = do_blocks( '<!-- wp:query {"queryId":7,"query":{"perPage":3,"postType":"cdtn","order":"desc","orderBy":"date","inherit":false}} --><div class="wp-block-query"><!-- wp:post-template {"className":"cadao-ds-bai"} --><!-- wp:post-title {"level":3,"isLink":true} /--><!-- /wp:post-template --></div><!-- /wp:query -->' );
$ds_khac = do_blocks( '<!-- wp:query {"queryId":8,"query":{"perPage":3,"postType":"cdtn","order":"desc","orderBy":"date","inherit":false}} --><div class="wp-block-query"><!-- wp:post-template --><!-- wp:post-title {"level":3,"isLink":true} /--><!-- /wp:post-template --></div><!-- /wp:query -->' );
kt( 'danh sách bài (khối có lớp cadao-ds-bai): dưới tiêu đề có 2 dòng đầu; danh sách khác (trang chủ…) không thêm', str_contains( $ds_html, 'Danh sách thử hai<br>Danh sách thử ba…' ) && ! str_contains( $ds_khac, 'cadao-ds-loi' ), $ds_html );
wp_insert_term( 'Thể loại trống thử', 'the_loai', array( 'slug' => 'trong-thu' ) );
kt( 'menu: thể loại chưa có bài thì ẩn; thể loại có bài, liên kết khác giữ nguyên', '' === cadao_an_the_loai_trong( '<li>x</li>', array( 'attrs' => array( 'url' => '/the-loai/trong-thu/' ) ) ) && '<li>v</li>' === cadao_an_the_loai_trong( '<li>v</li>', array( 'attrs' => array( 'url' => '/the-loai/ve/' ) ) ) && '<li>t</li>' === cadao_an_the_loai_trong( '<li>t</li>', array( 'attrs' => array( 'url' => '/tu-dien/' ) ) ) );
$ds_trong = do_shortcode( '[cadao_ds_trong]' );
kt( 'trang trống: lối sang Tìm và các thể loại có bài (không có thể loại trống)', str_contains( $ds_trong, '/tim/' ) && str_contains( $ds_trong, '>Vè</a>' ) && ! str_contains( $ds_trong, 'Thể loại trống thử' ), $ds_trong );
kt( 'chân trang: danh sách liên kết (điện thoại chia hai cột)', str_starts_with( do_shortcode( '[cadao_chan_trang]' ), '<ul class="cadao-chan-trang"><li>' ) );

// ---------- Cuối bài, Tìm (T-121) ----------
$gop = cadao_khoi_gop( get_post( $td1 ) );
kt( 'cuối bài: một khối "Bạn muốn góp gì?" với Góp ý, Cảm nghĩ ngắn (form thu gọn), Bài cảm nhận', str_contains( $gop, '<h2 id="cadao-gop-tieu">Bạn muốn góp gì?</h2>' ) && str_contains( $gop, '<h3 id="cadao-gop-y-tieu">Góp ý cho bài này</h3>' ) && str_contains( $gop, '<summary>Viết cảm nghĩ</summary>' ) && str_contains( $gop, 'name="comment"' ) && str_contains( $gop, 'Viết cảm nhận của bạn về câu này' ) && strpos( $gop, 'Góp ý cho bài này' ) < strpos( $gop, 'Cảm nghĩ ngắn' ), $gop );
kt( 'cuối bài: form vẫn đủ chống rác, quyền riêng tư (mỗi form một lần, nằm trong ô thu gọn)', 2 === substr_count( $gop, 'name="cadao_web"' ) && 2 === substr_count( $gop, '91/2025/QH15' ) && ! str_contains( $gop, 'id="reply-title"' ) );
kt( 'Tìm: "N bài", hoặc "20 trên N bài" khi chỉ hiện một phần', '3 bài' === cadao_tim_dem( 3, 3 ) && '20 trên 37 bài — gõ thêm chữ để thu hẹp' === cadao_tim_dem( 20, 37 ) && isset( cadao_tim_ket_qua( 'thu' )['tong'] ) );
kt( 'Tìm không ra: gợi ý gõ ít chữ, có dấu hay không, tất cả bài, từ điển; nói rõ câu tìm không ra được ghi lại', str_contains( $kr_tim = cadao_tim_html( array( 'q' => 'zzqx', 'bai' => array(), 'muc_tu' => array() ) ), 'không dấu' ) && str_contains( $kr_tim, 'tất cả bài' ) && str_contains( $kr_tim, 'được ghi lại' ) && str_contains( file_get_contents( CADAO_THU_MUC . '/assets/tim.js' ), 'được ghi lại' ) );

// ---------- Đọc tiếp cuối bài (D-075) ----------
$dt_bai = static function ( string $ten, string $the, array $chu_de = array(), array $vung = array(), string $tt = 'publish' ) use ( $dong ): int {
	static $so = 0;
	$id = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => $tt, 'post_title' => $ten, 'post_content' => cadao_khoi_tho( $dong( 9700 + 20 * ++$so, 6 ) . "\n" . $dong( 9710 + 20 * $so, 8 ) . "\nDòng ba đọc tiếp" ) ) );
	wp_set_object_terms( $id, $the, 'the_loai' );
	wp_set_object_terms( $id, $chu_de, 'chu_de' );
	wp_set_object_terms( $id, $vung, 'vung' );
	return $id;
};
$dt_x  = $dt_bai( 'Đọc tiếp đang xem', 'ca-dao', array( 'gia-dinh' ), array( 'nam-bo' ) );
$dt_a  = $dt_bai( 'Đọc tiếp cùng chủ đề và vùng', 'ca-dao', array( 'gia-dinh' ), array( 'nam-bo' ) );
$dt_b  = $dt_bai( 'Đọc tiếp cùng chủ đề', 've', array( 'gia-dinh' ) );
$dt_c  = $dt_bai( 'Đọc tiếp cùng vùng', 'ca-dao', array(), array( 'nam-bo' ) );
$dt_d  = $dt_bai( 'Đọc tiếp bản nháp', 'ca-dao', array( 'gia-dinh' ), array( 'nam-bo' ), 'draft' );
$dt    = cadao_doc_tiep( get_post( $dt_x ) );
$dt_id = array_map( static fn( $x ) => $x['bai']->ID, $dt );
$dt_vt = static fn( int $id ) => array_search( $id, $dt_id, true );
kt( 'đọc tiếp: cùng chủ đề và vùng trước, rồi cùng chủ đề, rồi cùng vùng; không có bài đang xem, bản nháp; tối đa 5', false !== $dt_vt( $dt_a ) && false !== $dt_vt( $dt_b ) && false !== $dt_vt( $dt_c ) && $dt_vt( $dt_a ) < $dt_vt( $dt_b ) && $dt_vt( $dt_b ) < $dt_vt( $dt_c ) && ! in_array( $dt_x, $dt_id, true ) && ! in_array( $dt_d, $dt_id, true ) && count( $dt ) <= 5, $dt_id );
$dt_ten = get_term_by( 'slug', 'gia-dinh', 'chu_de' )->name;
kt( 'đọc tiếp: nhãn nói vì sao (chủ đề trước vùng)', 'Cùng chủ đề ' . $dt_ten === $dt[ $dt_vt( $dt_a ) ]['nhan'] && str_starts_with( $dt[ $dt_vt( $dt_c ) ]['nhan'], 'Cùng vùng ' ), wp_list_pluck( $dt, 'nhan' ) );
$dt_bai( 'Đọc tiếp vè thêm', 've' );
$dt_y  = $dt_bai( 'Đọc tiếp vè không chủ đề', 've' );
$dt_y2 = cadao_doc_tiep( get_post( $dt_y ) );
kt( 'đọc tiếp: bài chưa có chủ đề, vùng thì lấy 3 bài cùng thể loại đăng gần đó', 3 === count( $dt_y2 ) && array() === array_filter( $dt_y2, static fn( $x ) => $x['bai']->ID === $dt_y || ! has_term( 've', 'the_loai', $x['bai'] ) || 'Cùng thể loại vè' !== $x['nhan'] ), wp_list_pluck( $dt_y2, 'nhan' ) );
wp_insert_term( 'Thể loại một bài thử', 'the_loai', array( 'slug' => 'mot-bai-thu' ) );
kt( 'đọc tiếp: không có bài nào để gợi ý thì không có khối', '' === cadao_khoi_doc_tiep( get_post( $dt_bai( 'Đọc tiếp một mình', 'mot-bai-thu' ) ) ) );
$dt_html = cadao_khoi_doc_tiep( get_post( $dt_x ) );
kt( 'khối "Đọc tiếp": tiêu đề h2, mỗi bài một liên kết có tên, 2 dòng đầu, nhãn', str_contains( $dt_html, '<h2 id="cadao-doc-tiep-tieu">Đọc tiếp</h2>' ) && str_contains( $dt_html, 'href="' . esc_url( get_permalink( $dt_a ) ) . '"><strong>Đọc tiếp cùng chủ đề và vùng</strong>' ) && str_contains( $dt_html, ' / ' ) && str_contains( $dt_html, '…' ) && str_contains( $dt_html, 'Cùng chủ đề ' . esc_html( $dt_ten ) ), $dt_html );

// ---------- SEO cơ bản, ảnh chia sẻ (T-050) ----------
$seo_bai = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Ngưỡng thuở quyện thử', 'post_content' => cadao_khoi_tho( "Ngưỡng thuở quyện thử dòng một sáu chữ\nNgưỡng thuở quyện thử dòng hai tám chữ đây\nDòng ba thử\nDòng bốn thử\nDòng năm thử" ) . cadao_khoi_tho( "Dị bản thử một\nDị bản thử hai" ) ) );
wp_set_object_terms( $seo_bai, 'ca-dao', 'the_loai' );
$mt_seo = cadao_mo_ta_bai( get_post( $seo_bai ) );
kt( 'mô tả bài chưa có Hiểu nhanh: 2 dòng đầu, thể loại, số dị bản; ≤ 160 ký tự', str_starts_with( $mt_seo, 'Ngưỡng thuở quyện thử dòng một sáu chữ / Ngưỡng thuở quyện thử dòng hai tám chữ đây… — Ca dao.' ) && str_contains( $mt_seo, 'Có 1 dị bản.' ) && mb_strlen( $mt_seo ) <= 160, $mt_seo );
wp_update_post( array( 'ID' => $seo_bai, 'post_excerpt' => str_repeat( 'Hiểu nhanh thử rất dài. ', 12 ) ) );
kt( 'mô tả bài có Hiểu nhanh: lấy Hiểu nhanh, cắt ở ranh giới từ', str_starts_with( cadao_mo_ta_bai( get_post( $seo_bai ) ), 'Hiểu nhanh thử rất dài.' ) && str_ends_with( cadao_mo_ta_bai( get_post( $seo_bai ) ), '…' ) && mb_strlen( cadao_mo_ta_bai( get_post( $seo_bai ) ) ) <= 160 );
$anh_nd = cadao_anh_noi_dung( get_post( $seo_bai ) );
kt( 'nội dung ảnh: tối đa 4 dòng, còn thì đánh dấu; thể loại; dòng mức kiểm; tên miền', 4 === count( $anh_nd['dong'] ) && $anh_nd['con'] && 'Ca dao' === $anh_nd['loai'] && CADAO_MUC_KIEM_CHUA === $anh_nd['muc_kiem'] && '' !== $anh_nd['ten_mien'], $anh_nd );
update_post_meta( $seo_bai, 'doi_chieu_ten', 'Sách thử ảnh' );
kt( 'nội dung ảnh: mức kiểm theo phần người biên soạn đã đối chiếu', 'Lời đã đối chiếu với Sách thử ảnh' === cadao_anh_noi_dung( get_post( $seo_bai ) )['muc_kiem'] );
// Phần vẽ (GD + FreeType) kiểm bằng PHP thật: npm run test:anh. Playground không vẽ được chữ: kiểm đường bỏ qua êm.
if ( cadao_anh_ve_duoc() ) {
	delete_post_meta( $seo_bai, 'doi_chieu_ten' );
	$anh     = cadao_anh_chia_se( get_post( $seo_bai ) );
	$anh_tep = $anh ? cadao_anh_thu_muc()[0] . '/' . basename( $anh['url'] ) : '';
	$anh_co  = $anh_tep && file_exists( $anh_tep ) ? getimagesize( $anh_tep ) : false;
	kt( 'ảnh chia sẻ: PNG 1200×630 trong uploads/cadao-anh/; lần sau dùng lại', $anh_co && 1200 === $anh_co[0] && 630 === $anh_co[1] && $anh['url'] === cadao_anh_chia_se( get_post( $seo_bai ) )['url'], $anh );
	update_post_meta( $seo_bai, 'doi_chieu_ten', 'Sách thử ảnh' );
	$anh2 = cadao_anh_chia_se( get_post( $seo_bai ) );
	kt( 'đổi mức kiểm: ảnh sinh lại, ảnh cũ bị xoá', $anh2 && $anh2['url'] !== $anh['url'] && ! file_exists( $anh_tep ) && 1 === count( glob( cadao_anh_thu_muc()[0] . '/' . $seo_bai . '-*.png' ) ) );
} else {
	$anh2 = null;
	kt( 'máy không vẽ được chữ: không có ảnh riêng, không lỗi, không ghi tệp', null === cadao_anh_chia_se( get_post( $seo_bai ) ) && null === cadao_anh_chung() && ! glob( cadao_anh_thu_muc()[0] . '/*.png' ) );
}
// Trang bài thật: thẻ meta, Open Graph, JSON-LD
$GLOBALS['wp_the_query'] = new WP_Query( array( 'p' => $seo_bai, 'post_type' => CADAO_LOAI_BAI ) );
$GLOBALS['wp_query']     = $GLOBALS['wp_the_query'];
$GLOBALS['wp_query']->the_post();
$the_seo = cadao_the_chia_se();
$ld_seo  = cadao_json_ld();
kt( 'trang bài: meta description, og:title, og:url, og:locale vi_VN; og:image 1200×630 + twitter:card khi có ảnh, không có thẻ ảnh rỗng khi không', str_contains( $the_seo, '<meta name="description" content="Hiểu nhanh thử' ) && str_contains( $the_seo, '<meta property="og:title" content="Ngưỡng thuở quyện thử">' ) && str_contains( $the_seo, 'og:url" content="' . esc_attr( get_permalink( $seo_bai ) ) ) && str_contains( $the_seo, 'og:locale" content="vi_VN"' ) && ( $anh2 ? str_contains( $the_seo, 'og:image" content="' . esc_attr( $anh2['url'] ) ) && str_contains( $the_seo, 'og:image:width" content="1200"' ) && str_contains( $the_seo, 'summary_large_image' ) : ! str_contains( $the_seo, 'og:image' ) ), $the_seo );
$ld_tp = $ld_seo['@graph'][0] ?? array();
kt( 'JSON-LD bài: CreativeWork (thể loại, tiếng Việt, lời, isBasedOn chỉ khi đã đối chiếu), BreadcrumbList qua thể loại', 'CreativeWork' === ( $ld_tp['@type'] ?? '' ) && 'Ca dao' === $ld_tp['genre'] && 'vi' === $ld_tp['inLanguage'] && str_contains( $ld_tp['text'], 'Ngưỡng thuở' ) && 'Sách thử ảnh' === ( $ld_tp['isBasedOn']['name'] ?? '' ) && 'BreadcrumbList' === $ld_seo['@graph'][1]['@type'] && 'Ca dao' === $ld_seo['@graph'][1]['itemListElement'][1]['name'], $ld_seo );
delete_post_meta( $seo_bai, 'doi_chieu_ten' );
kt( 'JSON-LD bài chưa đối chiếu: không có isBasedOn (không gán nguồn tự động)', ! isset( cadao_json_ld()['@graph'][0]['isBasedOn'] ) );
wp_reset_postdata();
$GLOBALS['wp_the_query'] = new WP_Query();
$GLOBALS['wp_query']     = $GLOBALS['wp_the_query'];
// Sitemap: bài có giá trị riêng; trang Đã lưu, Viết cảm nhận không vào
$gt = cadao_bai_co_gia_tri_rieng();
kt( 'sitemap bài: có bài có Hiểu nhanh, không có bài chưa có', in_array( $seo_bai, $gt, true ) && ! in_array( $ds_bai, $gt, true ) );
$sm_bai  = apply_filters( 'wp_sitemaps_posts_query_args', array(), CADAO_LOAI_BAI );
$sm_tr   = apply_filters( 'wp_sitemaps_posts_query_args', array(), 'page' );
$sm_loai = apply_filters( 'wp_sitemaps_taxonomies_query_args', array(), 'the_loai' );
kt( 'sitemap: bài chỉ gồm bài có giá trị riêng; bỏ trang Đã lưu, Viết cảm nhận; phân loại dưới 3 bài không vào; không có người dùng', $gt === $sm_bai['post__in'] && in_array( (int) get_option( 'cadao_trang_da_luu' ), $sm_tr['post__not_in'], true ) && ! in_array( (int) get_term_by( 'slug', 'trong-thu', 'the_loai' )->term_id, $sm_loai['include'], true ) && false === apply_filters( 'wp_sitemaps_add_provider', new stdClass(), 'users' ) );
kt( 'sitemap mục từ: bỏ mục có lời giải chép', '_cadao_chep' === ( apply_filters( 'wp_sitemaps_taxonomies_query_args', array(), 'tu_kho' )['meta_query'][0]['key'] ?? '' ) );
kt( 'không đổi blog_public (mở cho Google là quyết định của người sáng lập)', ! str_contains( file_get_contents( CADAO_THU_MUC . '/inc/seo.php' ), "update_option( 'blog_public'" ) );

// ---------- Trợ giúp viết Hiểu nhanh (T-100) ----------
$hn_co  = cadao_hn_da_co();
$hn_a   = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Hiểu nhanh thử A', 'post_content' => cadao_khoi_tho( "Hiểu nhanh thử A một\nHiểu nhanh thử A hai" ) . $ds_ct( 'Thử A', 'Lời giải tổng hợp của từ thử.' ) ) );
$hn_b   = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'publish', 'post_title' => 'Hiểu nhanh thử B', 'post_content' => cadao_khoi_tho( "Hiểu nhanh thử B một\nHiểu nhanh thử B hai" ) ) );
$hn_nhp = wp_insert_post( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'draft', 'post_title' => 'Hiểu nhanh thử nháp', 'post_content' => cadao_khoi_tho( 'Chưa đăng' ) ) );
for ( $i = 0; $i < 5; $i++ ) {
	cadao_luot_doc_cong( $hn_b );
}
$hn_ds = array_column( cadao_hn_can_viet( 500 ), 'id' );
kt( 'cần viết: bài đang đăng chưa có Hiểu nhanh, đọc nhiều trước; không có bài đã có, bài chưa đăng', array_search( $hn_b, $hn_ds, true ) < array_search( $hn_a, $hn_ds, true ) && ! in_array( $seo_bai, $hn_ds, true ) && ! in_array( $hn_nhp, $hn_ds, true ) );
kt( 'chú thích của bài dạng chữ (để tham khảo khi viết)', array( 'Thử A: Lời giải tổng hợp của từ thử.' ) === cadao_hn_chu_thich( get_post( $hn_a ) ), cadao_hn_chu_thich( get_post( $hn_a ) ) );
$hn_kq = cadao_hn_nap_lo( array( array( 'id' => $hn_a, 'nhap' => "Nháp máy thử cho A.\n<b>đậm</b>" ), array( 'id' => $seo_bai, 'nhap' => 'Bài đã có Hiểu nhanh' ), array( 'id' => $hn_nhp, 'nhap' => 'Bài chưa đăng' ), array( 'id' => 999999, 'nhap' => 'x' ), array( 'id' => $hn_b, 'nhap' => '' ) ) );
kt( 'nạp lô nháp: chỉ bài đang đăng chưa có Hiểu nhanh; bỏ thẻ HTML', array( 'da_nap' => 1, 'bo_qua' => 4 ) === $hn_kq && "Nháp máy thử cho A.\nđậm" === get_post_meta( $hn_a, CADAO_HN_NHAP, true ), $hn_kq );
kt( 'nạp nháp không đụng ô Tóm tắt, trạng thái, dấu "có hỗ trợ của AI"; bài vẫn noindex', '' === get_post( $hn_a )->post_excerpt && 'publish' === get_post_status( $hn_a ) && ! get_post_meta( $hn_a, '_cadao_ho_tro_ai', true ) && ! cadao_co_gia_tri_rieng( get_post( $hn_a ) ) );
kt( 'nháp không ra trang, mô tả, REST công khai', ! str_contains( cadao_mo_ta_bai( get_post( $hn_a ) ), 'Nháp máy' ) && ! str_contains( wp_json_encode( rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/cdtn/' . $hn_a ) )->get_data() ), 'Nháp máy' ) );
wp_set_current_user( cadao_quan_tri_dau_tien() );
ob_start();
cadao_hop_hieu_nhanh( get_post( $hn_a ) );
$hn_hop = ob_get_clean();
kt( 'hộp Viết Hiểu nhanh: chú thích, nháp ghi rõ "chưa duyệt" (chỉ đọc), nút Dùng nháp, ô "có hỗ trợ của AI" chưa đánh dấu', str_contains( $hn_hop, 'Thử A: Lời giải tổng hợp' ) && str_contains( $hn_hop, 'Nháp máy — chưa duyệt' ) && str_contains( $hn_hop, 'readonly>Nháp máy thử cho A.' ) && str_contains( $hn_hop, 'Dùng nháp này' ) && (bool) preg_match( '/id="cadao-ho-tro-ai" name="cadao_ho_tro_ai" value="1">/', $hn_hop ), $hn_hop );
ob_start();
cadao_hop_hieu_nhanh( get_post( $hn_b ) );
kt( 'bài không có nháp: không có ô nháp, nút dùng nháp', ! str_contains( ob_get_clean(), 'Nháp máy' ) );
$_POST = array( 'cadao_hn_nonce' => wp_create_nonce( 'cadao_luu_hn' ), 'cadao_ho_tro_ai' => '1' );
cadao_luu_hop_hieu_nhanh( $hn_a );
kt( 'lưu: người biên soạn đánh dấu "có hỗ trợ của AI" thì ghi; nháp giữ khi chưa bảo xoá', '1' === (string) get_post_meta( $hn_a, '_cadao_ho_tro_ai', true ) && '' !== get_post_meta( $hn_a, CADAO_HN_NHAP, true ) );
$_POST = array( 'cadao_hn_nonce' => wp_create_nonce( 'cadao_luu_hn' ), 'cadao_hn_xoa_nhap' => '1' );
cadao_luu_hop_hieu_nhanh( $hn_a );
kt( 'lưu: bỏ đánh dấu thì xoá dấu; "Xoá nháp máy" thì xoá nháp', ! get_post_meta( $hn_a, '_cadao_ho_tro_ai', true ) && '' === get_post_meta( $hn_a, CADAO_HN_NHAP, true ) );
$_POST = array( 'cadao_ho_tro_ai' => '1' );
cadao_luu_hop_hieu_nhanh( $hn_a );
kt( 'lưu không có mã xác nhận (lưu nhanh, REST, máy): không đổi gì', ! get_post_meta( $hn_a, '_cadao_ho_tro_ai', true ) );
$_POST = array();
$hn_hang = array_values( array_filter( cadao_hang_cho(), static fn( $h ) => 'hieu_nhanh' === $h['khoa'] ) )[0] ?? array();
kt( 'Bàn biên tập: hàng "Viết Hiểu nhanh (đã có X/50)" ở Làm trước, số còn thiếu tới 50', $hn_hang && $hn_hang['truoc'] && max( 0, 50 - cadao_hn_da_co() ) === $hn_hang['so'] && str_contains( $hn_hang['ten'], '/50' ), $hn_hang );
ob_start();
cadao_trang_hieu_nhanh();
$hn_tr = ob_get_clean();
wp_set_current_user( 0 );
kt( 'trang Viết Hiểu nhanh: đếm X/50, bài đọc nhiều trước, cột nháp máy', str_contains( $hn_tr, '/ 50 bài' ) && strpos( $hn_tr, 'Hiểu nhanh thử B' ) < strpos( $hn_tr, 'Hiểu nhanh thử A' ) && str_contains( $hn_tr, 'Nháp máy' ), substr( $hn_tr, 0, 300 ) );

// ---------- Khoá cửa quản trị (T-124, D-076) ----------
kt( 'XML-RPC tắt: không còn phương thức, không bật', array() === apply_filters( 'xmlrpc_methods', array( 'wp.getUsersBlogs' => 'x' ) ) && false === apply_filters( 'xmlrpc_enabled', true ) );
wp_set_current_user( 0 );
kt( 'REST: người ngoài không xem được danh sách người dùng', 404 === rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/users' ) )->get_status() );
wp_set_current_user( cadao_quan_tri_dau_tien() );
kt( 'REST: người soạn bài vẫn xem được (trình soạn khối cần)', 200 === rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/users' ) )->get_status() );
wp_set_current_user( 0 );
kt( 'sitemap không có danh sách người dùng', false === apply_filters( 'wp_sitemaps_add_provider', 'x', 'users' ) && 'x' === apply_filters( 'wp_sitemaps_add_provider', 'x', 'posts' ) );
$dn_id = wp_insert_user( array( 'user_login' => 'kt-dang-nhap', 'user_pass' => 'Mat-khau-thu-9x', 'user_email' => 'kt-dang-nhap@example.org', 'role' => 'subscriber' ) );
$dn_a  = wp_authenticate( 'kt-khong-co-ai', 'sai' );
$dn_b  = wp_authenticate( 'kt-dang-nhap', 'sai' );
kt( 'đăng nhập sai tên hay sai mật khẩu: cùng một câu báo (không lộ tên nào có thật)', is_wp_error( $dn_a ) && is_wp_error( $dn_b ) && $dn_a->get_error_message() === $dn_b->get_error_message() && 'cadao_dang_nhap_sai' === $dn_b->get_error_code() && ! str_contains( $dn_a->get_error_message(), 'kt-khong-co-ai' ), array( $dn_a, $dn_b ) );
kt( 'đếm lần sai theo tài khoản: gõ tên hay email cùng một khoá', cadao_khoa_dang_nhap( 'kt-dang-nhap' ) === cadao_khoa_dang_nhap( 'KT-Dang-Nhap@example.org' ) );
for ( $i = 0; $i < CADAO_DANG_NHAP_SAI_TOI_DA; $i++ ) {
	wp_authenticate( 'kt-dang-nhap@example.org', 'sai' );
}
$dn_c = wp_authenticate( 'kt-dang-nhap', 'Mat-khau-thu-9x' );
kt( 'sai 5 lần trong 15 phút: tạm khoá, kể cả khi lần sau đúng mật khẩu', is_wp_error( $dn_c ) && 'cadao_tam_khoa' === $dn_c->get_error_code(), $dn_c );
delete_transient( cadao_khoa_dang_nhap( 'kt-dang-nhap' ) );
kt( 'hết khoá: đăng nhập đúng được', wp_authenticate( 'kt-dang-nhap', 'Mat-khau-thu-9x' ) instanceof WP_User );
kt( 'tên không có thật cũng bị đếm (khoá không cho biết tên nào có thật)', str_starts_with( cadao_khoa_dang_nhap( 'kt-khong-co-ai' ), 'cadao_dn_' ) && ! str_contains( cadao_khoa_dang_nhap( 'kt-khong-co-ai' ), 'kt-khong' ) );
require_once ABSPATH . 'wp-admin/includes/user.php';
wp_delete_user( $dn_id );
kt( 'lời báo gửi chưa được: địa chỉ chỉ mang mã, câu báo tra trong bảng', 'nhieu' === cadao_ma_bao_gui( CADAO_BAO_GUI['nhieu'] ) && 'loi' === cadao_ma_bao_gui( 'Câu lạ bất kỳ' ) );
$_GET = array( 'gop_y' => 'loi', 'ly_do' => 'Tài khoản của bạn bị khóa, gọi số lạ', 'ma' => 'nhieu' );
$bao_gy = cadao_form_gop_y( get_post( $td1 ) );
$_GET = array( 'gop_y' => 'loi', 'ma' => '<b>lạ</b>' );
$bao_la = cadao_form_gop_y( get_post( $td1 ) );
$_GET = array();
kt( 'trang bài không hiện chữ người ngoài gắn vào địa chỉ (?ly_do=), chỉ hiện câu của site', ! str_contains( $bao_gy, 'số lạ' ) && str_contains( $bao_gy, CADAO_BAO_GUI['nhieu'] ) && str_contains( $bao_la, CADAO_BAO_GUI['loi'] ) && ! str_contains( $bao_la, 'lạ</b>' ) );

// ---------- Tục ngữ, thành ngữ vào hàng Chờ duyệt: xoay vòng theo loại (T-125) ----------
cadao_nguon_luu( array( 'id' => 'nguon-xv', 'ten' => 'Nguồn xoay vòng', 'url' => 'https://nguon-xv.example', 'dong_ghi_nguon' => 'Nguồn xoay vòng', 'loai_lay' => array( 'ca_dao', 'thanh_ngu_tuc_ngu' ), 'bo_qua_co' => array() ) + cadao_nguon_mac_dinh() );
$xv_ca = array();
for ( $i = 0; $i < 4; $i++ ) {
	$xv_ca[] = $kho( 'nguon-xv', "ca$i", $dong( 12000 + 20 * $i, 6 ) . "\n" . $dong( 12010 + 20 * $i, 8 ) );
}
$xv_tn_k = $kho( 'nguon-xv', 'tn-k', $dong( 12200, 7 ), array( 'loai' => 'thanh_ngu_tuc_ngu' ) );
$xv_tn_q = $kho( 'nguon-xv', 'tn-q', $dong( 12300, 7 ), array( 'loai' => 'thanh_ngu_tuc_ngu' ) );
$xv_so = cadao_kho_tao_bai_cho_duyet( cadao_nguon_lay( 'nguon-xv' ), 3 );
$xv_co = static fn( int $id ) => (int) $hang( $id )->post_id > 0;
kt( 'tạo bài hằng ngày xoay vòng theo loại: tục ngữ không phải chờ hết ca dao', 3 === $xv_so && $xv_co( $xv_ca[0] ) && $xv_co( $xv_ca[1] ) && ! $xv_co( $xv_ca[2] ) && $xv_co( $xv_tn_k ) && ! $xv_co( $xv_tn_q ), array( $xv_so, array_map( $xv_co, array_merge( $xv_ca, array( $xv_tn_k, $xv_tn_q ) ) ) ) );
kt( 'bài tục ngữ – thành ngữ: đúng mục gộp, ở Chờ duyệt', has_term( 'tuc-ngu', 'the_loai', (int) $hang( $xv_tn_k )->post_id ) && 'pending' === get_post_status( (int) $hang( $xv_tn_k )->post_id ) );
cadao_kho_tao_bai_cho_duyet( cadao_nguon_lay( 'nguon-xv' ), 10 );
kt( 'lượt sau lấy nốt các câu còn lại của mọi loại', $xv_co( $xv_ca[2] ) && $xv_co( $xv_ca[3] ) && $xv_co( $xv_tn_q ) );
$kdc = cadao_kho_dang_co();
kt( 'Bàn biên tập: dòng "Kho đang có" đủ các thể loại, kể cả thể loại 0 bài', str_starts_with( $kdc, 'Kho đang có (bài đã đăng): ' ) && str_contains( $kdc, ' tục ngữ – thành ngữ' ) && str_contains( $kdc, ' câu đố' ), $kdc );

// ---------- Người đọc bớt tắc (T-126) ----------
kt( 'danh sách: 30 bài mỗi trang (truy vấn và mẫu trang)', 30 === CADAO_DS_MOI_TRANG && str_contains( file_get_contents( '/repo/themes/ca-dao/templates/archive-cdtn.html' ), '"perPage":30' ) && str_contains( file_get_contents( '/repo/themes/ca-dao/templates/taxonomy.html' ), '"perPage":30' ) );
$css = file_get_contents( '/repo/themes/ca-dao/style.css' );
kt( 'nhóm lọc thu gọn có mũi tên; số trang cao ≥ 44 px', str_contains( $css, '.cadao-loc-nhom summary::after' ) && (bool) preg_match( '~\.wp-block-query-pagination \.page-numbers \{[^}]*min-height: 44px~', $css ) );
kt( 'mẫu trang 404 của theme dùng [cadao_404]', str_contains( (string) @file_get_contents( '/repo/themes/ca-dao/templates/404.html' ), '[cadao_404]' ) );
$_SERVER['REQUEST_URI'] = '/ca-dao/cong-thu-nhu-nui-2/?x=1';
$h404 = do_shortcode( '[cadao_404]' );
kt( 'trang 404: ô tìm (gửi tới /tim/) điền sẵn chữ từ đường dẫn, lối sang Tất cả bài, Từ điển', str_contains( $h404, 'action="' . esc_url( home_url( '/tim/' ) ) . '"' ) && str_contains( $h404, 'value="cong thu nhu nui"' ) && str_contains( $h404, esc_url( home_url( '/kho/' ) ) ) && str_contains( $h404, esc_url( home_url( '/tu-dien/' ) ) ), $h404 );
kt( 'tìm: chữ gần nhau (≤ 3 âm tiết chen giữa) / rời rạc', cadao_tim_gan_nhau( ' zqa x y zqb ', array( 'zqa', 'zqb' ) ) && ! cadao_tim_gan_nhau( ' zqa a b c d e zqb ', array( 'zqa', 'zqb' ) ) && cadao_tim_gan_nhau( ' zqb a zqa ', array( 'zqa', 'zq' ) ) );
$tg_ngan = $tim_bai( 'Tìm gần thử một', "zqa zqb chữ12500\nchữ12501 chữ12502" );
$tg_gan  = $tim_bai( 'Tìm gần thử hai', "zqa chữ12510 zqb\nchữ12511 chữ12512" );
$tg_dai  = $tim_bai( 'Tìm gần thử ba', "zqa " . $dong( 12520, 30 ) . "\n" . $dong( 12550, 10 ) . ' zqb' );
$tg      = cadao_tim( 'zqa zqb' );
$tg_vt   = static fn( int $id ) => array_search( $id, array_column( $tg, 'id' ), true );
kt( 'tìm: cụm liền nhau trước, chữ gần nhau sau, bài dài có chữ rời rạc xếp cuối và mang cờ "rời"', false !== $tg_vt( $tg_dai ) && $tg_vt( $tg_ngan ) < $tg_vt( $tg_gan ) && $tg_vt( $tg_gan ) < $tg_vt( $tg_dai ) && $tg[ $tg_vt( $tg_dai ) ]['roi'] && ! $tg[ $tg_vt( $tg_gan ) ]['roi'], $tg );
$tg_kq   = cadao_tim_ket_qua( 'zqa zqb' );
$tg_html = cadao_tim_html( $tg_kq );
kt( 'trang Tìm: bài chữ rời rạc ở nhóm riêng "Có các chữ này nhưng không liền nhau"', strpos( $tg_html, 'Tìm gần thử hai' ) < strpos( $tg_html, 'Có các chữ này nhưng không liền nhau' ) && strpos( $tg_html, 'Có các chữ này nhưng không liền nhau' ) < strpos( $tg_html, 'Tìm gần thử ba' ), $tg_html );
$tg_ten = $tim_bai( 'Tìm tên zqd - gạch nối', "chữ12600 chữ12601\nchữ12602" );
$tg_k2  = cadao_tim_ket_qua( 'zqd' );
kt( 'tìm: kết quả hiện tên bài (chữ người đọc gõ nằm ở tên), tên không còn mã HTML', 'Tìm tên zqd – gạch nối' === ( $tg_k2['bai'][0]['tieu_de'] ?? '' ) && str_contains( cadao_tim_html( $tg_k2 ), '<strong>Tìm tên zqd – gạch nối</strong>' ) && str_contains( cadao_tim_html( $tg_k2 ), 'chữ12600 chữ12601' ), $tg_k2['bai'][0] ?? null );
kt( 'tìm.js: hiện tên bài và nhóm chữ rời rạc như trang', str_contains( $tj = file_get_contents( CADAO_THU_MUC . '/assets/tim.js' ), 'to(b.tieu_de, d.q)' ) && str_contains( $tj, 'Có các chữ này nhưng không liền nhau' ) );

// ---------- Canonical (T-127) ----------
$cn_dat = static function ( array $hoi ) {
	$GLOBALS['wp_the_query'] = new WP_Query( $hoi );
	$GLOBALS['wp_query']     = $GLOBALS['wp_the_query'];
};
$cn_dat( array( 'post_type' => CADAO_LOAI_BAI ) );
$cn_kho = cadao_canonical();
$cn_dat( array( 'post_type' => CADAO_LOAI_BAI, 'paged' => 2 ) );
$cn_kho2 = cadao_canonical();
$_GET = array( 'loai' => 've', 'xep' => 'moi' );
$cn_dat( array( 'post_type' => CADAO_LOAI_BAI, 'paged' => 2 ) );
$cn_loc = cadao_canonical();
$_GET = array();
$cn_dat( array( 'tax_query' => array( array( 'taxonomy' => 'the_loai', 'field' => 'slug', 'terms' => 've' ) ) ) );
$cn_tl = cadao_canonical();
$cn_dat( array( 'p' => $td1, 'post_type' => CADAO_LOAI_BAI ) );
$cn_bai = cadao_canonical();
$cn_dat( array() );
$goc_kho = get_post_type_archive_link( CADAO_LOAI_BAI );
kt( 'canonical: Tất cả bài, trang 2 trỏ về chính nó, trang lọc trỏ về danh sách không lọc, trang thể loại; bài để WordPress tự in', $goc_kho === $cn_kho && trailingslashit( $goc_kho ) . 'page/2/' === $cn_kho2 && $goc_kho === $cn_loc && get_term_link( 've', 'the_loai' ) === $cn_tl && '' === $cn_bai, array( $cn_kho, $cn_kho2, $cn_loc, $cn_tl, $cn_bai ) );

// ---------- Lịch ----------
kt( 'lịch lấy bài mỗi 5 phút đã đặt', (bool) wp_next_scheduled( 'cadao_thu_thap' ) );
kt( 'lịch tạo bài hằng ngày đã đặt', (bool) wp_next_scheduled( 'cadao_tao_bai_hang_ngay' ) );
kt( 'nguồn Ca dao Mẹ có sẵn, đang tắt', ( $cdm = cadao_nguon_lay( 'cadao-me' ) ) && ! $cdm['bat'] );

// ---------- Bộ đọc trên cadao.me thật (tùy chọn) ----------
if ( defined( 'CADAO_KIEM_MANG' ) ) {
	$kq = cadao_nguon_chay( 'cadao-me', 12 ); // ~3 yêu cầu
	kt( 'bộ đọc cadao.me lấy được bài vào kho', $kq['moi'] > 0, $kq );
	global $wpdb;
	$mau = $wpdb->get_row( $wpdb->prepare( 'SELECT url, loi FROM ' . cadao_kho_bang() . ' WHERE nguon_id = %s ORDER BY id ASC LIMIT 1', 'cadao-me' ) );
	kt( 'mục kho có địa chỉ đúng trang và lời không rỗng', $mau && str_starts_with( $mau->url, 'https://cadao.me/' ) && '' !== $mau->loi );
	ra( '      mẫu: ' . ( $mau ? $mau->url . ' | ' . str_replace( "\n", ' / ', mb_substr( $mau->loi, 0, 80 ) ) : '—' ) );
	// Trang HTML thật: sitemap vè rồi vài trang vè (nhịp 5 giây)
	cadao_nguon_luu( array( 'id' => 've-that', 'ten' => 'Ca dao Mẹ (vè)', 'url' => 'https://cadao.me', 'loai_lay' => array( 've' ) ) + cadao_nguon_mac_dinh() );
	$kq = cadao_nguon_chay( 've-that', 30 );
	$ve = $wpdb->get_row( $wpdb->prepare( 'SELECT url, tieu_de, loi, du_lieu FROM ' . cadao_kho_bang() . " WHERE nguon_id = %s AND doc_trang LIKE '2%%' AND loi <> '' ORDER BY id LIMIT 1", 've-that' ) );
	kt( 'bộ đọc trang thật: đọc được vè (lời, tiêu đề) qua sitemap và trang', $ve && '' !== $ve->tieu_de, $kq );
	ra( '      vè: ' . ( $ve ? $ve->url . ' | ' . str_replace( "\n", ' / ', mb_substr( $ve->loi, 0, 80 ) ) . ' | ' . mb_substr( $ve->du_lieu, 0, 160 ) : '—' ) );
}

ra( "\nKiểm thử WordPress: {$GLOBALS['cadao_dat']} đạt, {$GLOBALS['cadao_hong']} hỏng." );
ra( $GLOBALS['cadao_hong'] ? 'KẾT QUẢ: HỎNG' : 'KẾT QUẢ: ĐẠT' );
