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
cadao_nguon_luu( array( 'id' => 'nguon-thu', 'ten' => 'Nguồn thử', 'url' => 'https://example.org', 'dong_ghi_nguon' => 'Nguồn thử', 'so_bai_moi_ngay' => 5, 'chep_chu_thich' => true, 'loai_lay' => array( 'ca_dao', 've' ) ) + cadao_nguon_mac_dinh() );
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
cadao_nguon_luu( array( 'id' => 'me-thu', 'ten' => 'Mẹ thử', 'url' => $G, 'dong_ghi_nguon' => 'Mẹ thử', 'bat' => true ) + cadao_nguon_mac_dinh() );
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
kt( 'câu đố: đáp án (lời giải) trong khối mở ra, kèm "Nguồn: …"', str_contains( $bai_do->post_content, '<summary>Đáp án</summary>' ) && str_contains( $bai_do->post_content, 'Cái mẫu' ) && str_contains( $bai_do->post_content, 'Nguồn:' ) && has_term( 'cau-do', 'the_loai', $bai_do ) );
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
