<?php
/**
 * Kiểm lỗi trình bày (MienDuLieu\KiemTrinhBay, D-119): máy chỉ nhắc theo quy tắc viết, không chặn, không sửa chữ.
 * Dữ liệu tổng hợp.
 */

declare(strict_types=1);

use Phongdao\MienDuLieu\KiemTrinhBay;

$ma = static fn( array $ds ): array => array_column( $ds, 'ma' );
$loi_mau = array( 'Dòng mẫu một của bài này', 'Dòng mẫu hai của bài này' );

kt( 'trình bày: Ý nghĩa đúng quy tắc (bắt đầu bằng nghĩa, có dấu chấm, ngắn) thì không nhắc gì', array() === KiemTrinhBay::y_nghia( 'Lời một người con nhớ mẹ khi đi xa. Nỗi nhớ gắn với bữa cơm quê.', $loi_mau ) );
kt( 'trình bày: Ý nghĩa trống không nhắc', array() === KiemTrinhBay::y_nghia( '   ' ) && array() === KiemTrinhBay::nhac( 'y_nghia', null ) );
kt(
	'trình bày: Ý nghĩa mở đầu "Câu ca dao…", "Thành ngữ…", hay nhắc lại ba tiếng đầu của lời thì nhắc "Mở đầu nhắc lại câu"',
	array( 'nhac_cau' ) === $ma( KiemTrinhBay::y_nghia( 'Câu ca dao nói về nỗi nhớ.', $loi_mau ) )
	&& array( 'nhac_cau' ) === $ma( KiemTrinhBay::y_nghia( 'Thành ngữ này có nghĩa là chịu khổ để nuôi chí.' ) )
	&& array( 'nhac_cau' ) === $ma( KiemTrinhBay::y_nghia( 'Ý của "dòng mẫu một" là nỗi nhớ.', $loi_mau ) )
	&& array() === $ma( KiemTrinhBay::y_nghia( 'Câu chuyện của người đi xa, nhớ quê.', $loi_mau ) )
);
kt(
	'trình bày: Ý nghĩa thiếu dấu câu cuối thì nhắc; kết thúc bằng ?, !, …, hoặc dấu chấm trong ngoặc kép thì không',
	array( 'thieu_dau' ) === $ma( KiemTrinhBay::y_nghia( 'Nỗi nhớ của người đi xa' ) )
	&& array() === KiemTrinhBay::y_nghia( 'Ai mà không nhớ quê?' ) && array() === KiemTrinhBay::y_nghia( 'Nhớ quê lắm thay…' ) && array() === KiemTrinhBay::y_nghia( 'Người xưa gọi là "nhớ nhà."' )
);
$dai = trim( str_repeat( 'chữ ', 61 ) ) . '.';
kt( 'trình bày: Ý nghĩa quá 60 chữ thì nhắc kèm số chữ; đúng 60 chữ thì không', array( 'qua_dai' ) === $ma( KiemTrinhBay::y_nghia( $dai ) ) && str_contains( KiemTrinhBay::y_nghia( $dai )[0]['chu'], 'Dài 61 chữ' ) && array() === KiemTrinhBay::y_nghia( trim( str_repeat( 'chữ ', 60 ) ) . '.' ) );
kt( 'trình bày: Ý nghĩa nhắc tác giả, bài thơ thì nhắc', array( 'tac_gia' ) === $ma( KiemTrinhBay::y_nghia( 'Lời này giống ý của một nhà thơ thời trước.' ) ) );
kt( 'trình bày: số chữ đếm theo khoảng trắng, bỏ thẻ định dạng', 4 === KiemTrinhBay::so_chu( ' Một  <b>hai</b> ba bốn ' ) && 0 === KiemTrinhBay::so_chu( '' ) );
kt(
	'trình bày: lời có dấu cách trước dấu câu, chữ "Ð" (Eth), viết hoa giữa câu thì nhắc (ghi số dòng); tên riêng một hai chữ hay chữ hoa sau dấu chấm không bị nhắc',
	array( 'cach_dau', 'd_sai', 'viet_hoa' ) === $ma( KiemTrinhBay::loi( array( 'Sợi thẳng , sợi dùn', 'Ðêm với ngày', 'Ai Làm Anh Phải Xa Em' ) ) )
	&& str_contains( KiemTrinhBay::loi( array( 'Dòng đúng', 'Ðêm' ) )[0]['chu'], 'Dòng 2' )
	&& array() === KiemTrinhBay::loi( array( 'Chơi cho lịch mới là chơi', 'Hà Nội băm sáu phố phường', 'Anh đi. Em ở. Ai về' ) )
);
kt(
	'trình bày: nhắc theo phần — Ý nghĩa đủ quy tắc, Nghĩa đen chỉ dấu câu cuối, lời theo quy tắc lời, phần khác không nhắc',
	array( 'thieu_dau' ) === $ma( KiemTrinhBay::nhac( 'nghia_den', array( 'chu' => 'Câu ca dao nói theo chữ' ) ) )
	&& array( 'cach_dau' ) === $ma( KiemTrinhBay::nhac( 'loi', array( 'dong' => array( 'Một dòng , hai' ) ) ) )
	&& array() === KiemTrinhBay::nhac( 'chu_thich', array( 'muc' => array() ) )
);
