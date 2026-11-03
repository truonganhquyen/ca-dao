<?php
/**
 * Đo lường không cookie: máy dò, chuỗi tìm, tên miền giới thiệu, khoá sự kiện. Dữ liệu tổng hợp.
 */

use Phongdao\MienDuLieu\DoLuong;

kt( 'máy dò, trình xem trước liên kết, yêu cầu không có trình duyệt → không tính', DoLuong::la_may_do( 'Googlebot/2.1' ) && DoLuong::la_may_do( 'facebookexternalhit/1.1' ) && DoLuong::la_may_do( 'Mozilla/5.0 HeadlessChrome' ) && DoLuong::la_may_do( '' ) && ! DoLuong::la_may_do( 'Mozilla/5.0 (iPhone)' ) );
kt( 'chuỗi tìm: chữ thường, gọn khoảng trắng, ≤ 80 ký tự; bỏ chuỗi quá ngắn, giống email hay số điện thoại', 'con cò bé bé' === DoLuong::chuoi_tim( '  Con  Cò <b>bé</b> bé ' ) && '' === DoLuong::chuoi_tim( 'a' ) && '' === DoLuong::chuoi_tim( 'ten@mien.vn' ) && '' === DoLuong::chuoi_tim( '0912 345 678' ) && 80 === mb_strlen( DoLuong::chuoi_tim( str_repeat( 'â', 100 ) ) ) );
kt( 'trang giới thiệu: chỉ tên miền hợp lệ, bỏ www. / m., không tính chính site', 'facebook.com' === DoLuong::ten_mien( 'www.facebook.com', 'phongdao.com' ) && 'zalo.me' === DoLuong::ten_mien( 'm.zalo.me', 'phongdao.com' ) && '' === DoLuong::ten_mien( 'phongdao.com', 'phongdao.com' ) && '' === DoLuong::ten_mien( 'không phải/tên miền', 'phongdao.com' ) );
kt( 'khoá sự kiện: bài + kiểu đã biết; kiểu lạ, bài 0, sự kiện lạ → không ghi', '12:apa' === DoLuong::khoa( 'trich_dan_sao_chep', array( 'bai' => '12', 'kieu' => 'apa' ) ) && '' === DoLuong::khoa( 'trich_dan_sao_chep', array( 'bai' => 12, 'kieu' => 'x' ) ) && '' === DoLuong::khoa( 'chia_se', array( 'bai' => 0, 'kieu' => 'web_share' ) ) && '' === DoLuong::khoa( 'theo_doi', array() ) );
kt( 'mở chú giải: ghi chữ được chạm (chữ thường, ≤ 60), bỏ thẻ', 'chút' === DoLuong::khoa( 'chu_giai_mo', array( 'muc' => '<i>Chút</i>' ) ) && '' === DoLuong::khoa( 'chu_giai_mo', array( 'muc' => '' ) ) );
kt( 'đúng 5 sự kiện (D-088); "Mong có câu này": khoá là câu trong kho, mã lạ → không ghi', array( 'tim_khong_ket_qua', 'trich_dan_sao_chep', 'chu_giai_mo', 'chia_se', 'mong_co_cau' ) === array_keys( DoLuong::SU_KIEN ) && 'kho:42' === DoLuong::khoa( 'mong_co_cau', array( 'kho' => '42' ) ) && '' === DoLuong::khoa( 'mong_co_cau', array( 'kho' => 'x' ) ) );
