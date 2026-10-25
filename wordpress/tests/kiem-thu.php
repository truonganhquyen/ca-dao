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
	'ma_goc' => 101, 'url' => 'https://example.org/bai-101/', 'loai' => 'ca_dao', 'tieu_de' => 'Dòng mẫu thứ nhất',
	'loi' => "Dòng mẫu thứ nhất\nDòng mẫu thứ hai <có dấu nhỏ hơn>", 'di_ban' => array( "Dị bản mẫu một\nDị bản mẫu hai" ),
	'chu_thich' => array( array( 'tu' => 'từ mẫu', 'noi_dung' => 'Chú thích mẫu.' ) ), 'co' => array(),
);
kt( 'lưu mục mới vào kho', 'moi' === cadao_kho_luu( 'nguon-thu', $muc ) );
kt( 'lưu lại y nguyên thì giữ nguyên', 'giu_nguyen' === cadao_kho_luu( 'nguon-thu', $muc ) );
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 102, 'url' => 'https://example.org/bai-102/', 'loai' => 'ca_dao', 'tieu_de' => 'Bài có cờ', 'loi' => 'Dòng có cờ', 'co' => array( 'tho_tuc' ) ) );
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 103, 'url' => 'https://example.org/ve-103/', 'loai' => 've', 'tieu_de' => 'Vè mẫu', 'loi' => "Vè mẫu dòng một\nVè mẫu dòng hai" ) );
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 104, 'url' => 'https://example.org/dd-104/', 'loai' => 'dong_dao', 'tieu_de' => 'Đồng dao mẫu', 'loi' => 'Đồng dao mẫu' ) );

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
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 105, 'url' => 'https://example.org/bai-105/', 'loai' => 'ca_dao', 'tieu_de' => 'Không chép', 'loi' => 'Dòng không chép', 'chu_thich' => array( array( 'tu' => 'x', 'noi_dung' => 'y' ) ) ) );
cadao_kho_tao_bai_cho_duyet( cadao_nguon_lay( 'nguon-thu' ), 1 );
$k = get_posts( array( 'post_type' => CADAO_LOAI_BAI, 'post_status' => 'pending', 'title' => 'Không chép', 'numberposts' => 1 ) );
kt( 'tắt "Chép chú thích" thì không chép', $k && ! str_contains( $k[0]->post_content, 'cadao-chu-thich' ) );

// Chép chú thích nhưng tắt "Ghi nguồn cho chú thích"
$n['chep_chu_thich'] = true;
$n['ghi_cong']       = false;
cadao_nguon_luu( $n );
cadao_kho_luu( 'nguon-thu', array( 'ma_goc' => 106, 'url' => 'https://example.org/bai-106/', 'loai' => 'ca_dao', 'tieu_de' => 'Không ghi nguồn', 'loi' => 'Dòng không ghi nguồn', 'chu_thich' => array( array( 'tu' => 'x', 'noi_dung' => 'y' ) ) ) );
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
}

ra( "\nKiểm thử WordPress: {$GLOBALS['cadao_dat']} đạt, {$GLOBALS['cadao_hong']} hỏng." );
ra( $GLOBALS['cadao_hong'] ? 'KẾT QUẢ: HỎNG' : 'KẾT QUẢ: ĐẠT' );
