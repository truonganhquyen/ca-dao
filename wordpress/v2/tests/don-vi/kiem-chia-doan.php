<?php
/**
 * Chia lời thành đoạn (MienDuLieu\ChiaDoan, chuyển từ cadao_chia_doan của MVP) và đoạn, bối cảnh trên dữ liệu trang
 * tác phẩm. Dữ liệu tổng hợp.
 */

declare(strict_types=1);

use Phongdao\DichVu\DungTrang\TrangTacPham;
use Phongdao\MienDuLieu\ChiaDoan;
use Phongdao\MienDuLieu\NguoiLam;

$dong = static fn( int $tu, int $den ): array => array_map( static fn( int $i ): string => "Dòng mẫu số $i của bài", range( $tu, $den ) );
$dem  = static fn( array $cd ): int => array_sum( array_map( static fn( $d ) => count( array_merge( ...$d['kho'] ) ), $cd['doan'] ) );

kt( 'bài ngắn không đặt đoạn → hiện như thường', null === ChiaDoan::chia( $dong( 1, 40 ) ) && null === ChiaDoan::chia( array() ) );

$cd = ChiaDoan::chia( $dong( 1, 45 ) );
kt(
	'bài dài một khổ (như MVP): mỗi đoạn khoảng 20 dòng; đoạn bắt đầu sau dòng 30 thu gọn; còn lại 5 dòng',
	$cd && $cd['dai'] && array( 1, 21, 41 ) === array_column( $cd['doan'], 'tu' ) && array( 20, 40, 45 ) === array_column( $cd['doan'], 'den' ) && array( false, false, true ) === array_column( $cd['doan'], 'sau' ) && 5 === $cd['con_lai'] && 45 === $dem( $cd ),
	$cd
);
$kho = array();
foreach ( range( 0, 4 ) as $k ) {
	$kho = array_merge( $kho, $k ? array( '' ) : array(), $dong( $k * 10 + 1, $k * 10 + 10 ) );
}
$cd = ChiaDoan::chia( $kho );
kt( 'bài dài chia khổ: mỗi khổ một đoạn; đoạn từ dòng 31 trở đi thu gọn', $cd && array( 1, 11, 21, 31, 41 ) === array_column( $cd['doan'], 'tu' ) && array( false, false, false, true, true ) === array_column( $cd['doan'], 'sau' ) && 20 === $cd['con_lai'], $cd );

$cd = ChiaDoan::chia( array_merge( $dong( 1, 2 ), array( '' ), $dong( 3, 4 ) ), array( array( 'tieu_de' => 'Phần một', 'tu_dong' => 1, 'den_dong' => 4 ) ) );
kt( 'bài ngắn có phần "Đoạn": hiện đoạn có tiêu đề, giữ chỗ ngắt khổ, không thu gọn', $cd && ! $cd['dai'] && 'Phần một' === $cd['doan'][0]['tieu_de'] && 2 === count( $cd['doan'][0]['kho'] ) && ! $cd['doan'][0]['sau'], $cd );

$cd = ChiaDoan::chia(
	$dong( 1, 10 ),
	array(
		array( 'tieu_de' => 'B', 'tu_dong' => 4, 'den_dong' => 8 ),
		array( 'tieu_de' => 'A', 'tu_dong' => 3, 'den_dong' => 5 ),
		array( 'tieu_de' => 'C', 'tu_dong' => 9, 'den_dong' => 99 ),
		array( 'tieu_de' => 'Hỏng', 'tu_dong' => 0 ),
	)
);
kt(
	'phần "Đoạn" lệch: xếp theo dòng đầu, chồng nhau thì cắt, vượt lời thì cắt, dòng ngoài mọi đoạn thành đoạn không tiêu đề — không mất dòng',
	$cd && array( '', 'A', 'B', 'C' ) === array_column( $cd['doan'], 'tieu_de' ) && array( 1, 3, 6, 9 ) === array_column( $cd['doan'], 'tu' ) && array( 2, 5, 8, 10 ) === array_column( $cd['doan'], 'den' ) && 10 === $dem( $cd ),
	$cd
);
$cd = ChiaDoan::chia( $dong( 1, 48 ), array( array( 'tieu_de' => 'Một', 'tu_dong' => 1, 'den_dong' => 30 ), array( 'tieu_de' => 'Hai', 'tu_dong' => 31, 'den_dong' => 48 ) ) );
kt( 'bài dài có phần "Đoạn": dùng đúng các đoạn đó; thời gian đọc theo số chữ (khoảng 200 chữ một phút)', $cd && $cd['dai'] && array( 'Một', 'Hai' ) === array_column( $cd['doan'], 'tieu_de' ) && 18 === $cd['con_lai'] && 2 === $cd['phut'], $cd );

$tp = TrangTacPham::tu_phan(
	array(
		'loi'      => array( 'dong' => array_merge( $dong( 1, 3 ), array( '' ), $dong( 4, 5 ) ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
		'doan'     => array( 'muc' => array( array( 'tieu_de' => 'Phần mẫu', 'tu_dong' => 1, 'den_dong' => 5 ) ) ),
		'boi_canh' => array( 'chu' => 'Lời giới thiệu mẫu.', 'nam' => '1900', 'noi' => 'Nơi mẫu', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
	),
	array( 'id' => 9, 'tieu_de' => 'Vè mẫu', 'url' => 'https://phongdao.test/ve/ve-mau/', 'the_loai' => 've' )
);
kt( 'trang tác phẩm: lời vẫn đủ dòng có chữ; đoạn có tiêu đề; bối cảnh = lời giới thiệu + "năm · nơi"', $tp && 5 === count( $tp['loi'] ) && 'Phần mẫu' === ( $tp['doan']['doan'][0]['tieu_de'] ?? '' ) && array( 'chu' => 'Lời giới thiệu mẫu.', 'them' => '1900 · Nơi mẫu' ) === $tp['boi_canh'], $tp );
$tp = TrangTacPham::tu_phan( array( 'loi' => array( 'dong' => $dong( 1, 4 ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), array( 'id' => 9, 'tieu_de' => 'Bài mẫu', 'url' => 'https://phongdao.test/ca-dao/bai-mau/', 'the_loai' => 'ca-dao' ) );
kt( 'bài ngắn không có đoạn, bối cảnh: hai khoá trống', null === $tp['doan'] && null === $tp['boi_canh'] );
