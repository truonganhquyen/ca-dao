<?php

use Phongdao\MienDuLieu\DinhDang;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

$html = '<strong>Thử chữ</strong> <a href="/ca-dao/mau/">liên kết</a><br><span class="pd-chu-do">màu</span>';
$sach = DinhDang::sach( $html );
kt( 'định dạng: giữ đậm/link/màu được phép và chữ xuống dòng', "Thử chữ liên kết\nmàu" === DinhDang::chu( $html ) && str_contains( $sach, '<a href="/ca-dao/mau/">' ) && str_contains( $sach, 'pd-chu-do' ) );
kt( 'định dạng: chuẩn hoá lặp không đổi kết quả', $sach === DinhDang::sach( $sach ) );
kt( 'định dạng: dán đoạn văn giữ chỗ ngắt đoạn', "Đoạn một\n\nĐoạn hai" === DinhDang::chu( '<p>Đoạn một</p><p>Đoạn hai</p>' ) );
$xss = DinhDang::sach( '<script>alert(1)</script><svg onload="bad()"></svg><img src=x onerror=bad()><a href="javascript:alert(1)" onclick="bad()">chữ</a><span class="pd-chu-do" style="position:fixed" onmouseover="bad()">màu</span>' );
kt( 'định dạng: bỏ mã chạy/URL lạ/thuộc tính và CSS tuỳ ý', ! str_contains( $xss, 'script' ) && ! str_contains( $xss, 'alert' ) && ! str_contains( $xss, 'href' ) && ! str_contains( $xss, 'style' ) && ! str_contains( $xss, 'onmouse' ) && 'chữmàu' === DinhDang::chu( $xss ) );
kt( 'định dạng: HTML trong chữ cũ vẫn hiện nguyên chữ, không chạy', '&lt;strong&gt;chữ cũ&lt;/strong&gt;' === DinhDang::hien( '<strong>chữ cũ</strong>' ) );
kt( 'định dạng: không dùng bản HTML khác chữ thuần', 'chữ đang lưu' === DinhDang::hien( 'chữ đang lưu', '<strong>chữ khác</strong>' ) );
$nc = array( 'loai_bai' => 'tac_pham', 'the_loai' => 'ca-dao', 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
[$v,$loi] = KiemPhan::kiem( 'y_nghia', array( 'chu' => "Thử chữ liên kết\nmàu", 'dinh_dang' => $html, 'ai' => false, 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc );
kt( 'định dạng: đi cùng chữ qua bộ kiểm phần', ! $loi && $sach === $v['dinh_dang'] );
[, $loi] = KiemPhan::kiem( 'y_nghia', array( 'chu' => 'chữ khác', 'dinh_dang' => $html, 'ai' => false, 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc );
kt( 'định dạng: chặn sửa HTML lệch chữ qua đường ghi trực tiếp', (bool) $loi );
[, $loi] = KiemPhan::kiem( 'loi', array( 'dong' => array( '[TEST] technical line' ), 'dinh_dang' => '<b>khác</b>', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc );
kt( 'định dạng: lời theo dòng không nhận trường HTML', (bool) $loi );
