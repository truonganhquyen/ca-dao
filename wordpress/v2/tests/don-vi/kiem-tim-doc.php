<?php

use Phongdao\MienDuLieu\ChiMuc;
use Phongdao\MienDuLieu\TrichTim;
use Phongdao\MienDuLieu\UuTienDoc;

// Chữ kỹ thuật để kiểm ranh giới âm tiết; không phải lời văn học dân gian.
$r203 = ChiMuc::dong_chi_muc( 's203 lồng mạng', '' );
kt( 'tìm đã gửi: âm tiết cuối phải trọn, không lấy lồng khi tìm lồn', array( ' lon ' ) === ChiMuc::mau( 'lon', true ) && ! ChiMuc::co( $r203, ChiMuc::phep_so( 'lồn', true ) )['lc'] && ChiMuc::co( $r203, ChiMuc::phep_so( 'lồn' ) )['lc'] );
kt( 'tìm: khoảng gần nhau của tìm đã gửi cũng không khớp nửa âm tiết', ! ChiMuc::gan_nhau( ' s203 mạng ', array( 's203', 'mã' ), true ) && ChiMuc::gan_nhau( ' s203 ma ', array( 's203', 'ma' ), true ) );
$h203 = TrichTim::danh_dau( 'lồn lồng <script>mã</script>', 'lồn' );
kt( 'trích tìm: đánh dấu trọn từ và thoát HTML', '<mark>lồn</mark> lồng &lt;script&gt;mã&lt;/script&gt;' === $h203 );
kt( 'trích tìm: có dấu, không dấu, NFC và gõ dở là các chế độ riêng', str_contains( TrichTim::danh_dau( "ma\u{0303} mạng", 'mã' ), '<mark>ma' ) && str_contains( TrichTim::danh_dau( 'mạng', 'ma', true, true ), '<mark>mạng</mark>' ) && ! str_contains( TrichTim::danh_dau( 'mạng', 'ma', true ), '<mark>' ) );
kt( 'thẻ nổi bật: một từ không thành khớp cả câu, trọn dòng hai chữ thì được', ! TrichTim::ca_cau( array( 'loi' => array( 'dong' => array( 's203 mã' ) ) ), 'fixture', 'mã' ) && TrichTim::ca_cau( array( 'loi' => array( 'dong' => array( 's203 mã' ) ) ), 'fixture', 's203 mã' ) );
$tr203 = TrichTim::trich( array( 'chu_thich' => array( 'muc' => array( array( 'tu' => 'fixture', 'loi_giai' => str_repeat( 'technical ', 40 ) . 's203 mã' ) ) ) ), 'fixture', 'mã' );
kt( 'trích tìm: chữ khớp cuối chú thích dài vẫn ở trong cửa sổ, có lý do khớp', 'Khớp trong chú thích' === $tr203['nhan'] && str_contains( $tr203['html'], '<mark>mã</mark>' ) );
kt( 'ưu tiên đọc: phần nghĩa/bối cảnh trước chú thích trước lời, không coi media là giải nghĩa', 2 === UuTienDoc::tinh( array( 'boi_canh' => array( 'chu' => 'fixture' ) ), 've' ) && 1 === UuTienDoc::tinh( array( 'chu_thich' => array( 'muc' => array( array( 'loi_giai' => 'fixture' ) ) ) ), 'ca-dao' ) && 0 === UuTienDoc::tinh( array( 'media' => array( 'muc' => array( 1 ) ) ), 'ca-dao' ) );
kt( 'ưu tiên câu đố: đáp án và giải thích, chỉ đáp án, chưa đáp án', 2 === UuTienDoc::tinh( array( 'dap_an' => array( 'chu' => 'fixture' ), 'y_nghia' => array( 'chu' => 'fixture' ) ), 'cau-do' ) && 1 === UuTienDoc::tinh( array( 'dap_an' => array( 'chu' => 'fixture' ) ), 'cau-do' ) && 0 === UuTienDoc::tinh( array( 'y_nghia' => array( 'chu' => 'fixture' ) ), 'cau-do' ) );
kt( 'ưu tiên bối cảnh: thông tin sự kiện công khai được tính, chỉ dấu người làm thì không', 2 === UuTienDoc::tinh( array( 'boi_canh' => array( 'su_kien' => '[technical fixture]', 'nguoi_lam' => 'nguoi_viet' ) ), 've' ) && 0 === UuTienDoc::tinh( array( 'boi_canh' => array( 'nguoi_lam' => 'nguoi_viet' ) ), 've' ) );
