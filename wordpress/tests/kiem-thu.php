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
kt( 'trích dẫn thông dụng: tên bài, thể loại, tên dự án, địa chỉ trang, ngày truy cập', '"Dòng trích mẫu" (Tục ngữ). Kho tàng Ca dao Tục ngữ Việt Nam. Truy cập tại: ' . get_permalink( $td ) . ', ngày {ngay}.' === $mau['thong_dung'], $mau );
kt( 'trích dẫn APA 7', 'Kho tàng Ca dao Tục ngữ Việt Nam. (' . get_the_date( 'Y', $td ) . '). Dòng trích mẫu [Tục ngữ]. ' . get_permalink( $td ) === $mau['apa'], $mau );
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
$r = $gui( $td, 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Mobile Safari/537.36', 'www.zalo.me' );
kt( 'gửi một lượt đọc: được tính', 200 === $r->get_status() && true === ( $r->get_data()['tinh'] ?? null ) && 1 === cadao_luot_doc_tong( $td ), array( $r->get_status(), $r->get_data() ) );
kt( 'trang giới thiệu: chỉ tên miền', array( 'zalo.me' => 1 ) === cadao_gioi_thieu_nhieu(), cadao_gioi_thieu_nhieu() );
$r = $gui( $td, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)' );
kt( 'máy dò tự khai tên (Googlebot) không được tính', true !== ( $r->get_data()['tinh'] ?? null ) && 1 === cadao_luot_doc_tong( $td ) );
kt( 'trình duyệt không giao diện không được tính', cadao_la_may_do( 'Mozilla/5.0 (X11; Linux x86_64) HeadlessChrome/120.0' ) && cadao_la_may_do( '' ) );
kt( 'bài chưa đăng không nhận lượt đọc', 404 === $gui( $bai_cd1->ID )->get_status() && 0 === cadao_luot_doc_tong( $bai_cd1->ID ) );
kt( 'bài không phải Ca dao – Tục ngữ không nhận lượt đọc', 404 === $gui( wp_insert_post( array( 'post_title' => 'Bài viết thường', 'post_status' => 'publish' ) ) )->get_status() );
kt( 'trang giới thiệu: bỏ chuỗi không phải tên miền và chính site', '' === cadao_ten_mien_gioi_thieu( 'https://x.y/z' ) && '' === cadao_ten_mien_gioi_thieu( wp_parse_url( home_url(), PHP_URL_HOST ) ) && 'google.com' === cadao_ten_mien_gioi_thieu( 'WWW.Google.com' ) );
kt( 'không đặt cookie (không có lệnh setcookie nào trong plugin)', ! preg_match( '/setcookie|setrawcookie|document\.cookie/', implode( '', array_map( 'file_get_contents', array_merge( glob( CADAO_THU_MUC . '/inc/*.php' ), glob( CADAO_THU_MUC . '/assets/*.js' ) ) ) ) ) );
kt( 'chưa tới 10 lượt: không hiện số lượt đọc', ! str_contains( cadao_khoi_hanh_dong( get_post( $td ) ), 'lượt đọc' ) );
for ( $i = 0; $i < 9; $i++ ) {
	cadao_luot_doc_cong( $td );
}
kt( 'từ 10 lượt: hiện "10 lượt đọc"', str_contains( cadao_khoi_hanh_dong( get_post( $td ) ), '>10 lượt đọc<' ) );
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
	cadao_nguon_luu( array( 'id' => $id_n, 'ten' => $ten_n, 'url' => "https://$id_n.example", 'dong_ghi_nguon' => $ten_n, 'loai_lay' => array( 'ca_dao', 'cau_do' ), 'bo_qua_co' => array() ) + cadao_nguon_mac_dinh() );
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
kt( 'trùng một dị bản đã ghi: vào hàng chờ', $r5 && 'di_ban' === $r5->muc && "bai:$P" === $r5->b, $r5 );
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
// ---------- Tách thành ngữ – tục ngữ (T-080) — danh mục Wiktionary giả, lời tổng hợp ----------
add_filter( 'cadao_nhip_giay', '__return_zero' );
add_filter(
	'pre_http_request',
	static function ( $tra, $tham, $url ) {
		if ( ! str_contains( $url, 'vi.wiktionary.org' ) ) {
			return $tra;
		}
		$ds = str_contains( urldecode( $url ), 'Thành ngữ' ) ? array( 'chữ9100 chữ9101 chữ9102 chữ9103', 'từ đơn mẫu' ) : array( 'chữ9200 chữ9201 chữ9202 chữ9203 chữ9204 chữ9205', 'tiếng mẫu' );
		return array( 'headers' => array(), 'body' => wp_json_encode( array( 'query' => array( 'categorymembers' => array_map( static fn( $t ) => array( 'title' => $t ), $ds ) ) ) ), 'response' => array( 'code' => 200, 'message' => '' ), 'cookies' => array(), 'filename' => null );
	},
	10,
	3
);
kt( 'tải danh mục Wiktionary (chỉ lưu dấu vân tay, không lưu chữ)', cadao_wiktionary_cap_nhat() && 2 === count( get_option( 'cadao_wiktionary' )['thanh_ngu'] ) && ! str_contains( wp_json_encode( get_option( 'cadao_wiktionary' ) ), 'chữ9100' ) );
remove_all_filters( 'pre_http_request' );
remove_all_filters( 'cadao_nhip_giay' );
$gy = static fn( $l ) => array( cadao_goi_y_loai( $l )['loai'], cadao_goi_y_loai( $l )['muc'] );
kt( 'có trong danh mục thành ngữ, câu ngắn → thành ngữ (chắc)', array( 'thanh_ngu', 'chac' ) === $gy( 'Chữ9100 chữ9101, chữ9102 chữ9103' ) );
kt( 'có trong danh mục tục ngữ, câu đủ dài → tục ngữ (chắc)', array( 'tuc_ngu', 'chac' ) === $gy( $dong( 9200, 6 ) ) );
kt( 'danh mục Wiktionary ngược với độ dài câu → không theo danh mục', array( 'thanh_ngu', 'nghieng' ) === $gy( 'Tiếng mẫu' ) );
kt( 'câu nhiều vế → tục ngữ (chắc)', array( 'tuc_ngu', 'chac' ) === $gy( $dong( 1, 3 ) . "\n" . $dong( 4, 3 ) ) );
kt( 'câu dài từ 8 âm tiết → tục ngữ (chắc)', array( 'tuc_ngu', 'chac' ) === $gy( $dong( 9300, 8 ) ) );
kt( 'hai vế cách dấu phẩy → tục ngữ (chắc)', array( 'tuc_ngu', 'chac' ) === $gy( $dong( 1, 3 ) . ', ' . $dong( 4, 3 ) ) );
kt( 'có từ nối "thì" → nghiêng về tục ngữ', array( 'tuc_ngu', 'nghieng' ) === $gy( 'chữ1 chữ2 thì chữ3' ) );
kt( 'cụm ngắn → nghiêng về thành ngữ', array( 'thanh_ngu', 'nghieng' ) === $gy( $dong( 1, 4 ) ) );
kt( 'câu 5–7 âm tiết không dấu hiệu → chưa rõ', array( '', 'chua' ) === $gy( $dong( 1, 5 ) ) );
$tg = $kho( 'nguon-a', 'tg1', $dong( 9400, 4 ), array( 'loai' => 'thanh_ngu_tuc_ngu' ) );
$p_tg = (int) cadao_kho_tao_bai( $tg );
wp_update_post( array( 'ID' => $p_tg, 'post_status' => 'publish' ) );
clean_post_cache( $p_tg );
$duong_cu = get_permalink( $p_tg );
kt( 'chưa duyệt: không tự đổi — bài vẫn vào Tục ngữ như trước', has_term( 'tuc-ngu', 'the_loai', $p_tg ) && str_contains( $duong_cu, '/tuc-ngu/' ), $duong_cu );
cadao_tach_loai_quyet( $tg, 'thanh_ngu' );
kt( 'duyệt "Là thành ngữ": bài đổi thể loại, đường dẫn /thanh-ngu/…', has_term( 'thanh-ngu', 'the_loai', $p_tg ) && ! has_term( 'tuc-ngu', 'the_loai', $p_tg ) && str_contains( get_permalink( $p_tg ), '/thanh-ngu/' ) );
kt( 'đường dẫn cũ vẫn dẫn tới bài và được chuyển hướng 301 sang đường dẫn mới', $p_tg === url_to_postid( $duong_cu ) && get_permalink( $p_tg ) === cadao_duong_dan_dung( (string) wp_parse_url( $duong_cu, PHP_URL_PATH ), get_post( $p_tg ) ) && null === cadao_duong_dan_dung( (string) wp_parse_url( get_permalink( $p_tg ), PHP_URL_PATH ), get_post( $p_tg ) ) );
$tg2 = $kho( 'nguon-a', 'tg2', $dong( 9500, 4 ), array( 'loai' => 'thanh_ngu_tuc_ngu' ) );
cadao_tach_loai_quyet( $tg2, 'thanh_ngu' );
kt( 'câu đã duyệt tách rồi mới thành bài: vào đúng Thành ngữ', has_term( 'thanh-ngu', 'the_loai', (int) cadao_kho_tao_bai( $tg2 ) ) );
$kho( 'nguon-a', 'tg3', $dong( 9550, 3 ), array( 'loai' => 'thanh_ngu_tuc_ngu' ) );
$_GET = array( 'tab' => 'nghieng' );
ob_start();
cadao_trang_tach_loai();
$tt = ob_get_clean();
$_GET = array();
kt( 'trang "Tách thành ngữ – tục ngữ": gợi ý, lý do, nút duyệt hàng loạt', str_contains( $tt, 'Đồng ý gợi ý' ) && str_contains( $tt, 'Là thành ngữ' ) && str_contains( $tt, 'cụm ngắn' ) && str_contains( $tt, 'chữ9550' ), $tt );

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
