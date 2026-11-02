<?php
/**
 * Dựng trang đọc từ các phần đã lưu (DichVu\DungTrang\TrangTacPham), thể thơ, chữ khác của dị bản.
 * Lằn ranh hiển thị: địa chỉ gốc máy đã lấy không ra trang; nguồn chú thích máy chép ẩn; "Đã đối chiếu" chỉ khi có sách.
 */

declare(strict_types=1);

use Phongdao\DichVu\DungTrang\TrangTacPham;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\TheTho;
use Phongdao\MienDuLieu\ToChoKhac;

// ---------- Thể thơ (ca kiểm chuyển từ MVP wordpress/tests/kiem-thu.php) ----------
$am = static fn( int $n ): string => implode( ' ', array_fill( 0, $n, 'a' ) );
kt( 'thể thơ: lục bát', 'luc-bat' === TheTho::nhan( array( $am( 6 ), $am( 8 ), $am( 6 ), $am( 8 ) ) ) );
kt( 'thể thơ: song thất lục bát (7 – 7 – 6 – 8)', 'song-that-luc-bat' === TheTho::nhan( array( $am( 7 ), $am( 7 ), $am( 6 ), $am( 8 ), $am( 7 ), $am( 7 ), $am( 6 ), $am( 8 ) ) ) );
kt( 'thể thơ: câu bốn chữ', 'bon-chu' === TheTho::nhan( array_fill( 0, 6, $am( 4 ) ) ) );
kt( 'thể thơ: câu ba chữ, năm chữ', 'ba-chu' === TheTho::nhan( array_fill( 0, 5, $am( 3 ) ) ) && 'nam-chu' === TheTho::nhan( array_fill( 0, 5, $am( 5 ) ) ) );
kt( 'thể thơ: câu một dòng; tự do', 'mot-dong' === TheTho::nhan( array( $am( 7 ) ) ) && 'tu-do' === TheTho::nhan( array( $am( 9 ), $am( 5 ), $am( 11 ) ) ) );
kt( 'thể thơ: đếm âm tiết không tính dấu câu đứng riêng; dòng trống bỏ qua', 'luc-bat' === TheTho::nhan( array( $am( 6 ) . ' –', '', $am( 8 ) . ' !' ) ) );
kt( 'đếm âm tiết chữ có dấu', 6 === TheTho::so_am_tiet( 'Dòng mẫu một của bài này' ) && 8 === TheTho::so_am_tiet( 'Dòng mẫu hai dài hơn chút cho vừa' ) );

// ---------- Chữ khác của dị bản ----------
kt( 'dị bản: chữ không có trong bản tham chiếu', array( 'khác' ) === ToChoKhac::tu_khac( array( 'Dòng mẫu một của bài này' ), array( 'Dòng mẫu khác của bài này' ) ) );
kt( 'dị bản: không phân biệt hoa thường, bỏ dấu câu, mỗi chữ một lần', array() === ToChoKhac::tu_khac( array( 'Mẫu, một.' ), array( 'mẫu một!', 'MẪU' ) ) );
kt( 'dị bản: bỏ dấu câu không làm hỏng chữ có dấu (“chờ”)', array( 'chờ' ) === ToChoKhac::tu_khac( array( 'đợi' ), array( '“chờ”' ) ) );

// ---------- Dữ liệu trang tác phẩm ----------
$bai  = array( 'id' => 7, 'tieu_de' => 'Bài mẫu', 'url' => 'https://phongdao.test/ca-dao/bai-mau/', 'the_loai' => 'ca-dao', 'the_tho' => 'luc-bat', 'hinh_thuc' => 'Lục bát', 'ho_so' => array( 5 => 'Sách mẫu' ) );
$phan = array(
	'loi'       => array( 'dong' => array( 'Dòng mẫu một của bài này', '', 'Dòng mẫu hai dài hơn chút cho vừa' ), 'nguoi_lam' => NguoiLam::MAY_CHEP, 'doi_chieu' => array( 'ho_so' => 5, 'trang' => '41' ) ),
	'chu_thich' => array(
		'muc' => array(
			array( 'tu' => 'chút', 'loi_giai' => 'Giải mẫu.', 'nguoi_lam' => NguoiLam::MAY_CHEP, 'nguon' => array( 'ho_so' => 5, 'trang' => '9' ) ),
			array( 'loi_giai' => 'Người viết mẫu.', 'nguoi_lam' => NguoiLam::NGUOI_VIET, 'nguon' => array( 'ho_so' => 5, 'trang' => '12', 'url' => 'https://example.org/sach' ) ),
			array( 'tu' => 'mẫu', 'loi_giai' => 'Không nguồn.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
		),
	),
	'di_ban'    => array( 'muc' => array( array( 'dong' => array( 'Dòng mẫu khác của bài này' ), 'nguoi_lam' => NguoiLam::MAY_CHEP, 'nam' => '1901' ) ) ),
	'y_nghia'   => array( 'chu' => 'Ý nghĩa mẫu.', 'ai' => true, 'nguoi_lam' => NguoiLam::AI_DA_DUYET ),
	'nguon_may' => array( 'nguon_id' => 'nguon-mau', 'url_goc' => 'https://nguon-an.example/bai-goc' ),
);
$tp = TrangTacPham::tu_phan( $phan, $bai );
kt( 'chưa có lời → không dựng trang', null === TrangTacPham::tu_phan( array( 'y_nghia' => $phan['y_nghia'] ), $bai ) );
kt( 'lời: bỏ dòng trống (chỗ ngắt khổ), giữ đúng chữ', array( 'Dòng mẫu một của bài này', 'Dòng mẫu hai dài hơn chút cho vừa' ) === $tp['loi'] );
kt( 'địa chỉ gốc máy đã lấy không có trong dữ liệu trang (dù có trong phần)', ! str_contains( (string) json_encode( $tp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), 'nguon-an.example' ) );
kt( 'chú thích máy chép: nguồn ẩn (chính sách mặc định), kể cả khi phần có ghi nguồn', true === $tp['chu_thich'][0]['may'] && null === $tp['chu_thich'][0]['nguon'] );
kt( 'chú thích người biên soạn điền nguồn: hiện tên sách, trang, đường dẫn', array( 'chu' => 'Sách mẫu, tr. 12', 'url' => 'https://example.org/sach' ) === $tp['chu_thich'][1]['nguon'], $tp['chu_thich'][1] );
kt( 'chú thích người viết không ghi nguồn: không tự thêm nguồn', null === $tp['chu_thich'][2]['nguon'] && false === $tp['chu_thich'][2]['may'] );
kt( '"Đã đối chiếu" có tên sách, trang khi người biên soạn điền và hồ sơ sách còn', array( 'sach' => 'Sách mẫu', 'trang' => '41' ) === $tp['doi_chieu'] );
kt( 'hồ sơ sách không còn → không hiện "Đã đối chiếu"', null === TrangTacPham::tu_phan( $phan, array( 'ho_so' => array() ) + $bai )['doi_chieu'] );
kt( 'ý nghĩa có hỗ trợ AI giữ dấu AI', array( 'chu' => 'Ý nghĩa mẫu.', 'ai' => true ) === $tp['y_nghia'] );
kt( 'dị bản: chữ khác, năm', array( 'khác' ) === $tp['di_ban'][0]['khac'] && '1901' === $tp['di_ban'][0]['nam'] );
kt( 'kiểu trình bày: lục bát → lucbat; tục ngữ → proverb; khác → thường', 'lucbat' === $tp['kieu'] && 'proverb' === TrangTacPham::tu_phan( $phan, array( 'the_loai' => 'tuc-ngu' ) + $bai )['kieu'] && '' === TrangTacPham::tu_phan( $phan, array( 'the_tho' => 'tu-do' ) + $bai )['kieu'] );
kt( 'tên thể loại theo danh sách cố định; thể loại lạ → trống', 'Ca dao' === $tp['ten_the_loai'] && '' === TrangTacPham::tu_phan( $phan, array( 'the_loai' => 'la' ) + $bai )['ten_the_loai'] );
$tn = TrangTacPham::tu_phan( array( 'loi' => array( 'dong' => array( 'Tục ngữ mẫu' ) ), 'dung_khi' => array( 'chu' => "Một\n\n Hai " ) ), array( 'the_loai' => 'tuc-ngu' ) + $bai );
kt( 'dùng khi: mỗi dòng một ví dụ', array( 'Một', 'Hai' ) === $tn['dung_khi'] );
$cd = TrangTacPham::tu_phan( array( 'loi' => array( 'dong' => array( 'Câu đố mẫu' ) ), 'dap_an' => array( 'chu' => 'Đáp án mẫu' ), 'goi_y' => array( 'chu' => 'Gợi ý mẫu' ) ), array( 'the_loai' => 'cau-do' ) + $bai );
kt( 'câu đố: có đáp án, gợi ý', 'Đáp án mẫu' === $cd['dap_an'] && 'Gợi ý mẫu' === $cd['goi_y'] );

$the = TrangTacPham::the( array( 'loi' => array( 'dong' => array( 'Một', '', 'Hai', 'Ba' ) ), 'y_nghia' => array( 'chu' => str_repeat( 'ý ', 100 ) ), 'co_nghi' => array( 'ly_do' => 'x' ) ), $bai );
kt( 'thẻ danh sách: hai dòng đầu, ý nghĩa cắt ở 160 ký tự, cờ nghi', array( 'Một', 'Hai' ) === $the['dong'] && 160 === mb_strlen( $the['y_nghia'] ) && str_ends_with( $the['y_nghia'], '…' ) && $the['co_nghi'] );
kt( 'thẻ danh sách: chưa có lời → không có thẻ', null === TrangTacPham::the( array(), $bai ) );

// ---------- Câu hôm nay (giữ cách MVP) ----------
use Phongdao\MienDuLieu\CauHomNay;

kt( 'Câu hôm nay: bài 1–4 câu mới hợp (dòng trống không tính)', CauHomNay::hop_le( array( 'a' ) ) && CauHomNay::hop_le( array( 'a', '', 'b', 'c', 'd' ) ) && ! CauHomNay::hop_le( array( 'a', 'b', 'c', 'd', 'e' ) ) && ! CauHomNay::hop_le( array( '', ' ' ) ) );
kt( 'Câu hôm nay: cùng ngày cùng câu (ai mở cũng thấy như nhau), danh sách trống → không có', CauHomNay::chon( array( 3, 5, 9 ), '2026-11-02' ) === CauHomNay::chon( array( 3, 5, 9 ), '2026-11-02' ) && in_array( CauHomNay::chon( array( 3, 5, 9 ), '2026-11-02' ), array( 3, 5, 9 ), true ) && null === CauHomNay::chon( array(), '2026-11-02' ) );
$ngay_khac = array_unique( array_map( static fn( int $d ): ?int => CauHomNay::chon( range( 1, 50 ), sprintf( '2026-11-%02d', $d ) ), range( 1, 30 ) ) );
kt( 'Câu hôm nay: đổi theo ngày (30 ngày cho nhiều câu khác nhau)', count( $ngay_khac ) >= 10, count( $ngay_khac ) );
