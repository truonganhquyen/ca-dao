<?php
/**
 * Bộ tách lời của nguồn (MienDuLieu\TrichHtml): ca kiểm chuyển từ MVP (wordpress/tests/kiem-thu.php); hồ sơ nguồn.
 * HTML mẫu tự đặt, không phải trang thật của nguồn.
 */

declare(strict_types=1);

use Phongdao\MienDuLieu\HoSoNguon;
use Phongdao\MienDuLieu\TrichHtml;

$r = TrichHtml::loi( "<p>Dòng một,<a data-counter=\"1\" data-ref-id=\"42\"\n class=\"footnote \" href=\"#note-42\"></a><br />\nDòng  hai <em>nghiêng</em>&#8230;</p>" );
kt( 'trích lời: br thành xuống dòng, gộp khoảng trắng, giải mã thực thể', "Dòng một,\nDòng hai nghiêng…" === $r['loi'], $r['loi'] );
kt( 'trích lời: nhớ vị trí dấu chú thích ở cuối dòng', array( array( 'ma' => 42, 'dong' => 1, 'cot' => 9 ) ) === $r['chu_thich'], $r['chu_thich'] );
$r = TrichHtml::loi( '<p>Dòng có dấu cách cuối&nbsp;<a class="footnote" data-ref-id="7"></a></p>' );
kt( 'trích lời: cắt dấu cách không ngắt ở cuối dòng (như JavaScript)', 'Dòng có dấu cách cuối' === $r['loi'], $r['loi'] );
kt( 'trích lời: dấu chú thích sau dấu cách cuối dòng vẫn còn, cột không vượt độ dài', array( array( 'ma' => 7, 'dong' => 1, 'cot' => 21 ) ) === $r['chu_thich'], $r['chu_thich'] );
kt( 'trích lời: giữ ký tự U+0085 như bộ trích JavaScript', "đi\u{0085}" === TrichHtml::loi( "<p>đi\u{0085}</p>" )['loi'] );
kt( 'trích lời: đoạn mới thành dòng trống', "Đoạn một\n\nĐoạn hai" === TrichHtml::loi( "<p>Đoạn một</p>\n<p>Đoạn hai</p>" )['loi'] );
kt( 'trích lời: bỏ ảnh và chú thích ảnh (không lấy ảnh của nguồn)', 'Có ảnh' === TrichHtml::loi( '<p>Có ảnh<div class="wp-caption alignnone"><img src="x.jpg"><p class="wp-caption-text">Chú thích ảnh</p></div></p>' )['loi'] );
kt( 'trích lời: chuẩn hoá NFC', "Ho\u{00E0}" === TrichHtml::loi( "<p>Hoa\u{0300}</p>" )['loi'] );
kt( 'văn bản thường: bỏ thẻ, giữ khối', "Một liên kết\nHai" === TrichHtml::van_ban( '<p>Một <a href="/x">liên kết</a></p><div class="wp-caption"><img src="a.jpg"><p>ảnh</p></div><p>Hai</p>' ) );

$G     = 'https://mau.example';
$trang = TrichHtml::trang(
	'<article id="post-1" class="post"><h1 class="post-title entry-title"><a href="' . $G . '/cd-1/">Dòng một mẫu</a></h1>'
	. '<div class="entry-content"><p>Dòng một mẫu<a class="footnote" data-ref-id="71" href="#note-71">1</a><br>Dòng hai mẫu</p></div>'
	. '<section class="variants"><ul><li class="variant"><p>Dị một mẫu<br>Dị hai mẫu<a class="footnote" data-ref-id="72">2</a></p></li></ul></section>'
	. '<aside class="post-data"><ul class="post-categories"><li><a href="#">Chủ đề mẫu</a></li></ul></aside></article>'
	. '<article id="post-60"><h1 class="post-title"><a href="' . $G . '/cau-do/do-mau/">Đố mẫu</a></h1><div class="entry-content"><p>Mình tròn mẫu<br>Đố là gì mẫu</p>'
	. '<div class="q">Là cái gì?</div><div class="a"><p>Cái mẫu</p></div></div></article>'
	. '<section class="ref"><ol><li data-ref-id="71" id="note-71"><dl><dt>từ mẫu</dt><dd><p>Nghĩa &amp; mẫu.</p></dd></dl></li></ol></section>'
);
$b1 = $trang['bai'][0] ?? array();
kt( 'đọc trang: lời, dị bản, chủ đề, vị trí chú thích của lời và dị bản', "Dòng một mẫu\nDòng hai mẫu" === ( $b1['loi'] ?? '' ) && array( "Dị một mẫu\nDị hai mẫu" ) === ( $b1['di_ban'] ?? null ) && array( 'Chủ đề mẫu' ) === ( $b1['chu_de_goc'] ?? null ) && 71 === ( $b1['vi_tri_chu_thich'][0]['ma'] ?? 0 ) && 72 === ( $b1['vi_tri_chu_thich_di_ban'][0][0]['ma'] ?? 0 ), $b1 );
$b2 = $trang['bai'][1] ?? array();
kt( 'đọc trang: câu đố tách câu hỏi, đáp án khỏi lời', "Mình tròn mẫu\nĐố là gì mẫu" === ( $b2['loi'] ?? '' ) && 'Là cái gì?' === ( $b2['cau_hoi'] ?? '' ) && 'Cái mẫu' === ( $b2['dap_an'] ?? '' ), $b2 );
kt( 'đọc trang: chú thích của trang, giải mã HTML', array( 71 => array( 'ma' => 71, 'tu' => 'từ mẫu', 'noi_dung' => 'Nghĩa & mẫu.' ) ) === $trang['chu_thich'] );
kt( 'sitemap: địa chỉ và ngày sửa', array( array( 'url' => "$G/ve/a/", 'lastmod' => '2026-01-02' ), array( 'url' => "$G/ve/", 'lastmod' => null ) ) === TrichHtml::sitemap( "<urlset><url><loc>$G/ve/a/</loc><lastmod>2026-01-02</lastmod></url><url><loc> $G/ve/ </loc></url></urlset>" ) );
kt( 'cùng một trang: bỏ khác biệt mã hoá và dấu / cuối', TrichHtml::cung_trang( "$G/c%E1%BA%A7u/", "$G/cầu" ) && ! TrichHtml::cung_trang( "$G/a/", "$G/b/" ) );

$hs = HoSoNguon::chuan( array( 'loai' => 'web', 'xin_phep' => 'da_ky', 'chinh_sach_hien' => 'an', 'url' => 'javascript:x', 'chep_chu_thich' => 1, 'la' => 'x' ) );
kt( 'hồ sơ nguồn: lựa chọn ngoài danh sách thành trống, đường dẫn lạ bỏ, trường lạ bỏ', array( 'loai' => 'web', 'url' => '', 'xin_phep' => '', 'chinh_sach_hien' => 'an', 'chep_chu_thich' => true ) === $hs, $hs );
kt( 'hồ sơ nguồn: không thêm giá trị mặc định cho trường không gửi', array() === HoSoNguon::chuan( array() ) );
