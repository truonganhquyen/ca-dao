<?php
/**
 * Từ khó trong lời (MienDuLieu\TuKho, D-105, docs/32 mục 4.9): không phân biệt hoa thường, kiểu bỏ dấu, bộ mã chữ;
 * lần đầu trong cả bài; báo từ không có trong lời. Dữ liệu tổng hợp.
 */

declare(strict_types=1);

use Phongdao\MienDuLieu\TuKho;

kt( 'khoá tiếng: hoa thường, kiểu bỏ dấu cũ / mới ("hoà" / "hòa", "THUỶ" / "thủy"), chữ tổ hợp như nhau; khác dấu thanh thì khác', TuKho::khoa( 'Hoà' ) === TuKho::khoa( 'hòa' ) && TuKho::khoa( 'THUỶ' ) === TuKho::khoa( 'thủy' ) && TuKho::khoa( "ho\u{0300}a" ) === TuKho::khoa( 'hoà' ) && TuKho::khoa( 'hoa' ) !== TuKho::khoa( 'hóa' ) && TuKho::khoa( 'hoà' ) !== TuKho::khoa( 'hoá' ) );

$dong = array( 'Trèo lên cây Bưởi hái hoa', 'Bước xuống vườn cà hái nụ tầm xuân', 'Hái hoa rồi lại bưởi xanh' );
$tim  = TuKho::tim( $dong, array( 'hái hoa', 'Tầm Xuân', 'bưởi', 'không có trong lời', '' ) );
$chu  = static fn( int $i, array $d ): string => mb_substr( $dong[ $i ], $d[0], $d[1] - $d[0] );
kt(
	'tìm từ khó: mỗi từ chỉ lần đầu trong cả bài (dòng 3 không đánh dấu lại), giữ đúng chữ trong lời, cụm nhiều tiếng, không phân biệt hoa thường',
	array( 0, 1 ) === array_keys( $tim ) && array( 'Bưởi', 'hái hoa' ) === array_map( static fn( $d ) => $chu( 0, $d ), $tim[0] ) && array( 'tầm xuân' ) === array_map( static fn( $d ) => $chu( 1, $d ), $tim[1] ),
	$tim
);
kt( 'tìm từ khó: không khớp nửa tiếng ("hoa" không khớp trong "hoan"), từ dài ưu tiên, không đánh dấu lồng', array() === TuKho::tim( array( 'Hân hoan' ), array( 'hoa' ) ) && array( array( array( 0, 8 ) ) ) === array_values( TuKho::tim( array( 'tầm xuân nở' ), array( 'xuân', 'tầm xuân' ) ) ) );
kt( 'tìm từ khó: chữ tổ hợp trong lời vẫn khớp từ viết dựng sẵn; vị trí tính theo ký tự', array( 0 ) === array_keys( TuKho::tim( array( "Cây ho\u{0300}e đầu ngõ" ), array( 'hoè' ) ) ) );
kt( 'từ không có trong lời → báo để nhắc kiểm chính tả; từ có (khác hoa thường, kiểu dấu) thì không báo', array( 'trèo cao', 'hoà' ) === TuKho::thieu( array( 'Trèo lên cây bưởi', 'Thuỷ chung' ), array( 'BƯỞI', 'trèo cao', 'thủy chung', 'hoà' ) ) );
