<?php

use Phongdao\MienDuLieu\SoKhopLoi;

$a = "Mẫu ĐẬP dữ liệu kiểm thử\nDòng hai giữ nguyên ký tự";
kt( 'chỉ mục: NFC, hoa thường, Ð/Đ, ð/đ, khoảng trắng Unicode', SoKhopLoi::chu( $a ) === SoKhopLoi::chu( Normalizer::normalize( str_replace( 'ĐẬP', 'ÐẬP', mb_strtolower( $a ) ), Normalizer::FORM_D ) ) );
kt( 'bỏ dấu: Ð/ð và Đ/đ tương đương', 'dap da' === SoKhopLoi::chu( 'Ðập ðá', true ) );
kt( 'ký tự ẩn không chẻ một từ', 'dữ liệu' === SoKhopLoi::chu( "dữ li\u{200B}ệ\u{00AD}u\u{FEFF}" ) );
kt( 'khoảng trắng đặc biệt gộp, dấu câu ngăn từ', 'a b c' === SoKhopLoi::chu( "A\u{202F}B—C" ) );
kt( 'khớp toàn văn: đổi dòng, dấu câu không đổi kết luận', SoKhopLoi::so( $a, str_replace( "\n", '; ', $a ) )['khop'] );
kt( 'mất dấu không được gọi là trùng chắc chắn', ! SoKhopLoi::so( $a, SoKhopLoi::chu( $a, true ) )['khop'] );
kt( 'một dòng thuộc bài dài là đoạn trích, không chặn toàn văn', 'lien_quan' === SoKhopLoi::so( 'Mẫu đập dữ liệu kiểm thử', $a )['cap_do'] );
kt( 'sai một tiếng vẫn tìm được, không gọi là khớp hệt', ! SoKhopLoi::so( 'Mẫu đập dữ liệu chạy thử', $a )['khop'] );
kt( 'không tiêu thụ một mẩu ngắn rồi coi là khớp cả truy vấn', null === SoKhopLoi::so( 'không liên quan dữ liệu thêm mới hoàn toàn khác xa', $a ) );
kt( 'một từ ngắn không nới sai chữ', null === SoKhopLoi::so( 'xữ', $a ) );
kt( 'truy vấn ngắn chỉ hai nhánh, nhánh rỗng không được gửi', 2 === count( SoKhopLoi::truy_van( 'dữ' ) ) && array() === SoKhopLoi::truy_van( '?!' ) );
kt( 'truy vấn dài không nhân nhánh vô hạn', 2 === count( SoKhopLoi::truy_van( str_repeat( 'fixture ', 100 ) ) ) );
kt( 'câu 6 tiếng tối đa 8 nhánh trong một HTTP request', 8 === count( SoKhopLoi::truy_van( 'mẫu đập dữ liệu kiểm thử' ) ) );
$dai = implode( ' ', array_map( static fn( $i ) => 'fixture' . $i, range( 1, 500 ) ) );
kt( 'bài dài không bị cắt rồi báo trùng hệt', SoKhopLoi::so( $dai, $dai )['khop'] && ! SoKhopLoi::so( $dai, $dai . ' phần thêm' )['khop'] );
