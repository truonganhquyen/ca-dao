<?php
/**
 * Mức giống của hai lời (MienDuLieu\VanTay): ca kiểm chuyển nguyên từ MVP (wordpress/tests/kiem-thu.php, T-109).
 * Lời tổng hợp "chữ<số>", không phải lời thật.
 */

declare(strict_types=1);

use Phongdao\MienDuLieu\VanTay;

$dong = static fn( int $tu, int $so ): string => implode( ' ', array_map( static fn( $i ) => "chữ$i", range( $tu, $tu + $so - 1 ) ) );
$L    = $dong( 1000, 6 ) . "\n" . $dong( 1006, 8 );
kt( 'y hệt: chỉ khác dấu câu, chữ hoa, khoảng trắng', 'y_het' === VanTay::do_giong( $L, mb_strtoupper( str_replace( ' chữ1003', ',  chữ1003', $L ) ) . '!' )[0] );
kt( 'gần như y hệt: chỉ khác cách bỏ dấu / gõ thiếu dấu (hoà / hòa)', array( 'gan_y_het', 100 ) === array_slice( VanTay::do_giong( 'Hoà ' . $L, 'Hòa ' . str_replace( 'chữ', 'chư', $L ) ), 0, 2 ) );
$L40 = implode( "\n", array( $dong( 2000, 10 ), $dong( 2010, 10 ), $dong( 2020, 10 ), $dong( 2030, 10 ) ) );
$g   = VanTay::do_giong( $L40, substr( $L40, 0, strrpos( $L40, ' ' ) ) . ' chữ9999' );
kt( 'gần như y hệt: khác một chữ trong bài dài (≥ 90%)', 'gan_y_het' === $g[0] && $g[1] >= 90 && $g[1] < 100, $g );
$g = VanTay::do_giong( $L, $dong( 1000, 6 ) . "\n" . $dong( 1006, 4 ) . ' ' . $dong( 5000, 4 ) );
kt( 'có thể là dị bản: giống 50–90%', 'di_ban' === $g[0] && $g[1] >= 50 && $g[1] < 90, $g );
kt( 'câu ngắn nằm trong bài dài: chỉ là "liên quan"', 'lien_quan' === VanTay::do_giong( $dong( 2000, 8 ), $L40 )[0] );
kt( 'hai bài khác nhau chung dòng đầu: khác', 'khac' === VanTay::do_giong( $dong( 4000, 4 ) . "\n" . $dong( 4004, 8 ), $dong( 4000, 4 ) . "\n" . $dong( 4100, 8 ) )[0] );
kt( 'câu một dòng giống 64%: không tính là dị bản', 'khac' === VanTay::do_giong( $dong( 3000, 10 ), $dong( 3000, 8 ) . ' ' . $dong( 3100, 2 ) )[0] );
kt( 'câu ngắn (dưới 6 âm tiết) khác một chữ: khác', 'khac' === VanTay::do_giong( $dong( 6000, 4 ), $dong( 6000, 3 ) . ' chữ6999' )[0] );
kt( 'trùng một dị bản đã ghi: có thể là dị bản', 'di_ban' === VanTay::do_giong( $L, $L, true )[0] );
kt( 'vân tay: bản (1) giữ dấu, bản (2) bỏ dấu; đếm dòng không tính dòng trống', VanTay::cua( 'Hoà' )['vt1'] !== VanTay::cua( 'Hòa' )['vt1'] && VanTay::cua( 'Hoà' )['vt2'] === VanTay::cua( 'hoa' )['vt2'] && 2 === VanTay::cua( "a\n\nb" )['so_dong'] );
