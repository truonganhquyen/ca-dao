<?php
use Phongdao\MienDuLieu\GopNguoiDoc;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

$form123 = array( 'loai' => 'tac_pham', 'noi_dung' => "[UX-TEST] Text one\r\nText two", 'dong_y' => 1 );
[ $err123, $d123 ] = GopNguoiDoc::kiem( $form123 );
kt( 'Góp câu không cần chọn thể loại/nguồn/tên, giữ ngắt dòng', null === $err123 && "[UX-TEST] Text one\nText two" === $d123['noi_dung'] && ! $d123['hien_ten'] );
kt( 'Góp câu không cắt âm thầm lời hoặc nguồn quá dài', 'loi' === GopNguoiDoc::kiem( array( 'noi_dung' => str_repeat( 'x', 4001 ) ) + $form123 )[0] && 'loi' === GopNguoiDoc::kiem( array( 'xuat_xu' => str_repeat( 'x', 2001 ) ) + $form123 )[0] );
kt( 'Góp câu yêu cầu đồng ý và có chữ, từ chối mảng giả ô chữ', null !== GopNguoiDoc::kiem( array( 'dong_y' => 0 ) + $form123 )[0] && null !== GopNguoiDoc::kiem( array( 'noi_dung' => '!!!' ) + $form123 )[0] && 'loi' === GopNguoiDoc::kiem( array( 'noi_dung' => array() ) + $form123 )[0] );
$nc123 = array( 'loai_bai' => 'tac_pham', 'the_loai' => 'ca-dao', 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
$loi123 = array( 'dong' => array( '[UX-TEST] Reader words' ), 'nguoi_lam' => NguoiLam::DOC_GIA_GOP );
kt( 'Lời người đọc góp được người duyệt dùng, máy không được mạo nhận', ! KiemPhan::kiem( 'loi', $loi123, $nc123 )[1] && !! KiemPhan::kiem( 'loi', $loi123, array( 'goi' => KiemPhan::GOI_MAY ) + $nc123 )[1] );
kt( 'Ghi công là phần người quyết, máy không tự công khai nguồn/bút danh', !! KiemPhan::kiem( 'dong_gop', array( array( 'gop_id' => 1, 'phan' => 'loi', 'dau_loi' => 'test' ) ), array( 'goi' => KiemPhan::GOI_MAY ) + $nc123 )[1] );
