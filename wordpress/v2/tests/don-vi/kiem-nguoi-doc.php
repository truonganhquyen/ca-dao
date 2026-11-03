<?php
/**
 * Người đọc góp: kiểm dữ liệu một lần gửi, định dạng bài cảm nhận, địa chỉ mạng sau Cloudflare. Dữ liệu tổng hợp.
 */

use Phongdao\MienDuLieu\DiaChiMang;
use Phongdao\MienDuLieu\GopNguoiDoc;

[ $ma, $d ] = GopNguoiDoc::kiem( array( 'loai' => 'di_ban', 'noi_dung' => "  Dòng một\r\nDòng hai \x07 ", 'dong_y' => '1', 'noi_nghe' => "Làng   mẫu\n tỉnh mẫu", 'bai' => '12' ) );
kt( 'góp dị bản: đạt, giữ xuống dòng, bỏ ký tự điều khiển, gộp khoảng trắng ô ngắn', null === $ma && "Dòng một\nDòng hai" === $d['noi_dung'] && 'Làng mẫu tỉnh mẫu' === $d['noi_nghe'] && 12 === $d['bai'], $d );
kt( 'thiếu đồng ý giấy phép → chưa gửi', 'thieu' === GopNguoiDoc::kiem( array( 'loai' => 'bao_loi', 'noi_dung' => 'Lỗi mẫu' ) )[0] );
kt( 'thiếu nội dung → chưa gửi; gửi số trang chỉ cần số trang', 'thieu' === GopNguoiDoc::kiem( array( 'loai' => 'bao_loi', 'dong_y' => 1 ) )[0] && null === GopNguoiDoc::kiem( array( 'loai' => 'so_trang', 'trang' => '12', 'dong_y' => 1 ) )[0] );
kt( 'loại lạ → chưa gửi; cảm nghĩ cần bài', 'thieu' === GopNguoiDoc::kiem( array( 'loai' => 'khac', 'noi_dung' => 'x', 'dong_y' => 1 ) )[0] && 'bai' === GopNguoiDoc::kiem( array( 'loai' => 'cam_nghi', 'noi_dung' => 'x', 'dong_y' => 1 ) )[0] );
kt( 'cảm nghĩ cắt ở 2.000 ký tự', 2000 === mb_strlen( GopNguoiDoc::kiem( array( 'loai' => 'cam_nghi', 'bai' => 3, 'noi_dung' => str_repeat( 'â', 2500 ), 'dong_y' => 1 ) )[1]['noi_dung'] ) );
$dai = str_repeat( 'Chữ cái mẫu ', 30 );
kt( 'bài cảm nhận cần tiêu đề, ≥ 200 chữ cái, cam kết tự viết và đồng ý', 'thieu_pt' === GopNguoiDoc::kiem( array( 'loai' => 'phan_tich', 'tieu_de' => 'T', 'noi_dung' => 'ngắn', 'tu_viet' => 1, 'dong_y' => 1 ) )[0] && 'thieu_pt' === GopNguoiDoc::kiem( array( 'loai' => 'phan_tich', 'tieu_de' => 'T', 'noi_dung' => $dai, 'dong_y' => 1 ) )[0] && null === GopNguoiDoc::kiem( array( 'loai' => 'phan_tich', 'tieu_de' => 'T', 'noi_dung' => $dai, 'tu_viet' => 1, 'dong_y' => 1, 'vai' => 'giao_vien' ) )[0] );
kt( 'vai lạ của người viết cảm nhận → bỏ trống', '' === GopNguoiDoc::kiem( array( 'loai' => 'phan_tich', 'tieu_de' => 'T', 'noi_dung' => $dai, 'tu_viet' => 1, 'dong_y' => 1, 'vai' => 'quan_tri' ) )[1]['vai'] );
$vb = GopNguoiDoc::van_ban( "Đoạn **đậm** <script>x</script>\ndòng hai\n\n> Trích một\n> Trích hai" );
kt( 'bài cảm nhận: đoạn, chữ đậm, trích lời; mọi thẻ HTML người gõ bị thoát', str_contains( $vb, '<strong>đậm</strong>' ) && str_contains( $vb, '&lt;script&gt;' ) && ! str_contains( $vb, '<script>' ) && str_contains( $vb, '<blockquote class="wp-block-quote">' ) && str_contains( $vb, 'Trích một<br>Trích hai' ), $vb );

kt( 'dải CIDR IPv4, IPv6', DiaChiMang::trong_dai( '104.16.1.2', '104.16.0.0/13' ) && ! DiaChiMang::trong_dai( '104.24.0.1', '104.16.0.0/13' ) && DiaChiMang::trong_dai( '2606:4700::1', '2606:4700::/32' ) && ! DiaChiMang::trong_dai( 'x', '104.16.0.0/13' ) );
kt( 'chỉ tin CF-Connecting-IP khi yêu cầu đến từ Cloudflare (gửi thẳng kèm header giả không né được giới hạn)', '198.51.100.7' === DiaChiMang::cua( '104.16.1.2', '198.51.100.7' ) && '203.0.113.9' === DiaChiMang::cua( '203.0.113.9', '198.51.100.7' ) && '203.0.113.9' === DiaChiMang::cua( '203.0.113.9', 'không phải ip' ) );
