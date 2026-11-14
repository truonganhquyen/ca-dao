<?php

// Chỉ chạy trong WordPress kiểm thử; các chuỗi s203 là dữ liệu kỹ thuật tổng hợp.
$s203_ids = array();
foreach ( range( 1, 38 ) as $s203_n ) {
	$s203_tu = $s203_n <= 36 ? 'mã' : ( 37 === $s203_n ? 'ma' : 'mạng' );
	$s203_id = wp_insert_post( array( 'post_type' => 'tac_pham', 'post_status' => 'publish', 'post_title' => 's203 ' . $s203_tu . ' ' . $s203_n, 'post_date' => '2026-01-01 00:00:00' ) );
	wp_set_object_terms( $s203_id, $s203_n % 2 ? 'ca-dao' : 'tuc-ngu', 'the_loai' );
	update_post_meta( $s203_id, '_phongdao_loi', array( 'dong' => array( 's203 ' . $s203_tu . ' ' . $s203_n ), 'nguoi_lam' => 'nguoi_viet' ) );
	\Phongdao\DichVu\ChiMucTim\ChiMucTim::ghi( $s203_id );
	$s203_ids[] = $s203_id;
}
update_post_meta( $s203_ids[35], '_phongdao_y_nghia', array( 'chu' => 'Technical fixture explanation', 'nguoi_lam' => 'nguoi_viet' ) );
\Phongdao\DichVu\TomTat\TomTat::xong();
$s203_tim = \Phongdao\DichVu\ChiMucTim\TimDoc::class;
$s203_a = $s203_tim::tim( 's203 mã' );
$s203_b = $s203_tim::tim( 's203 mã', array( 'trang' => 2 ) );
kt( 'tìm đã gửi: 36 bài đúng dấu, một bài khác dấu, một bài chỉ khớp đầu chữ tách riêng', 36 === $s203_a['tong'] && array( 'dung' => 36, 'khong_dau' => 1, 'go_do' => 1 ) === $s203_a['nhom'], $s203_a['nhom'] );
kt( 'tìm: trang 1 và 2 đủ 36 bài, không trùng, trang vượt giới hạn quay về trang cuối', 20 === count( $s203_a['ds'] ) && 16 === count( $s203_b['ds'] ) && ! array_intersect( array_column( $s203_a['ds'], 'id' ), array_column( $s203_b['ds'], 'id' ) ) && 2 === $s203_tim::tim( 's203 mã', array( 'trang' => 999999 ) )['trang'] );
kt( 'tìm: giải nghĩa chỉ phân hạng bài khớp tương đương', $s203_ids[35] === $s203_a['ds'][0]['id'] && 1 === $s203_tim::tim( 's203 mã', array( 'khop' => 'khong_dau' ) )['tong'] && 1 === $s203_tim::tim( 's203 mã', array( 'khop' => 'go_do' ) )['tong'] );
$s203_c = $s203_tim::tim( 's203 mã', array( 'loai' => 'ca-dao' ) );
kt( 'tìm: bộ lọc thể loại đếm trên toàn tập, không phải chỉ trang đầu', 18 === $s203_c['tong'] && 18 === $s203_c['loai']['ca-dao'] && 18 === $s203_c['loai']['tuc-ngu'] );
kt( 'tìm: khi đang gõ có tiền tố, khi gửi thì không', $s203_tim::tim( 's203 m', array(), true )['tong'] >= 38 && 0 === $s203_tim::tim( 's203 m' )['tong'] );
$s203_html = \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 's203 mã' );
kt( 'trang tìm: một cụm có nhiều bài tương đương không có thẻ nổi bật; có mark, lý do và phân trang', ! str_contains( $s203_html, 'result-primary' ) && str_contains( $s203_html, '<mark>mã</mark>' ) && str_contains( $s203_html, 'Khớp trong lời' ) && str_contains( $s203_html, 'Phân trang kết quả tìm' ) && str_contains( $s203_html, 'trang=2' ) );
$s203_html = \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 's203 mã 1' );
kt( 'trang tìm: khớp duy nhất trọn tiêu đề thì nổi bật, không phải chỉ có một từ', str_contains( $s203_html, 'result-primary' ) && ! str_contains( \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 's203' ), 'result-primary' ) );
$s203_html = \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 's203 mã', array( 'loai' => 'ca-dao', 'khop' => 'dung' ) );
kt( 'tìm: bộ lọc nằm trên liên kết GET, không nhóm lại kết quả theo thể loại', str_contains( $s203_html, 'loai=ca-dao' ) && ! str_contains( $s203_html, '<h2>Ca dao <span' ) && str_contains( $s203_html, 'aria-current="true"' ) );
kt( 'tìm: truy vấn có ngoặc kép chỉ giữ cụm liền và vẫn so trọn từ cuối', 1 === $s203_tim::tim( '"s203 mã 1"' )['tong'] && $s203_ids[0] === $s203_tim::tim( '"s203 mã 1"' )['ds'][0]['id'] );
update_post_meta( $s203_ids[0], '_phongdao_tho_tuc', array( 'nguoi_lam' => 'nguoi_viet' ) );
$s203_html = \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 's203 mã 1' );
kt( 'tìm: phần trích và mark của bài bật che vẫn nằm trong details đóng', str_contains( $s203_html, '<details class="tho-tuc">' ) && strpos( $s203_html, '<mark>mã</mark>' ) > strpos( $s203_html, '<details class="tho-tuc">' ) && ! str_contains( $s203_html, '<details class="tho-tuc" open' ) );
wp_update_post( array( 'ID' => $s203_ids[37], 'post_status' => 'draft' ) );
// Cố ý để dòng chỉ mục cũ để kiểm đường tìm không tin trạng thái chỉ mục.
$wpdb->replace( \Phongdao\DichVu\ChiMucTim\ChiMucTim::bang(), array( 'post_id' => $s203_ids[37] ) + \Phongdao\MienDuLieu\ChiMuc::dong_chi_muc( 's203 mạng', '' ) );
kt( 'tìm: chỉ mục còn dòng cũ cũng không làm lộ bài vừa chuyển nháp', 0 === $s203_tim::tim( 's203 mã', array( 'khop' => 'go_do' ) )['tong'] );
wp_update_post( array( 'ID' => $s203_ids[37], 'post_status' => 'publish' ) );
\Phongdao\DichVu\TomTat\TomTat::xong();
$s203_q = new WP_Query( array( 'post_type' => 'tac_pham', 'post__in' => $s203_ids, 'posts_per_page' => 20, 'phongdao_danh_sach' => true ) );
kt( 'danh sách: có giải nghĩa đứng trước cả khi ID mới hơn, ngày đăng bằng nhau', $s203_ids[35] === (int) $s203_q->posts[0]->ID );
$s203_q = new WP_Query( array( 'post_type' => 'tac_pham', 'post__in' => $s203_ids, 'posts_per_page' => 20, 'phongdao_danh_sach' => true, 'sap' => 'moi' ) );
kt( 'danh sách Mới thêm: ngày bằng nhau dùng ID giảm dần ổn định', $s203_ids[37] === (int) $s203_q->posts[0]->ID );
global $wpdb;
$wpdb->update( \Phongdao\DichVu\TomTat\TomTat::bang(), array( 'doc_7' => 50 ), array( 'post_id' => $s203_ids[0] ) );
$s203_q = new WP_Query( array( 'post_type' => 'tac_pham', 'post__in' => $s203_ids, 'posts_per_page' => 20, 'phongdao_danh_sach' => true, 'sap' => 'doc' ) );
kt( 'danh sách Đọc nhiều tuần này: dùng số tổng hợp 7 ngày có sẵn', $s203_ids[0] === (int) $s203_q->posts[0]->ID );
$s203_before = get_post_meta( $s203_ids[35] );
$s203_status = get_post_status( $s203_ids[35] );
update_option( \Phongdao\HaTang\CaiDat::TUY_CHON, 13 );
\Phongdao\HaTang\CaiDat::nang_cap();
$s203_sum = \Phongdao\DichVu\TomTat\TomTat::lay( $s203_ids[35] );
kt( 'nâng cấp 13 lên 14 chạy lại được: tính ưu tiên, giữ nguyên phần bài và trạng thái đăng', 14 === (int) get_option( \Phongdao\HaTang\CaiDat::TUY_CHON ) && 2 === (int) $s203_sum['uu_tien_doc'] && $s203_before === get_post_meta( $s203_ids[35] ) && $s203_status === get_post_status( $s203_ids[35] ) );
// Giữ kiểm hồi quy tổng > 500 và cách ghi hàng nghìn ở trang Tìm: dùng tác phẩm thật trong CSDL thử,
// không dùng dòng chỉ mục mồ côi (đường người đọc mới bắt buộc đối chiếu trạng thái bài).
$s203_base = 100 + (int) $wpdb->get_var( "SELECT MAX(ID) FROM {$wpdb->posts}" );
foreach ( array_chunk( range( 1, 1001 ), 100 ) as $s203_lo ) {
	// Dùng bảng chỉ mục để lưu ID cho SELECT lô; chữ hoàn toàn là fixture kỹ thuật.
	$wpdb->query( 'INSERT INTO ' . \Phongdao\DichVu\ChiMucTim\ChiMucTim::bang() . ' (post_id,chinh,chinh_kd,phu,phu_kd) VALUES ' . implode( ',', array_map( static fn( int $i ): string => $wpdb->prepare( '(%d,%s,%s,%s,%s)', $s203_base + $i, ' s203count ', ' s203count ', '  ', '  ' ), $s203_lo ) ) );
}
$s203_post_cols = 'post_author,post_date,post_date_gmt,post_content,post_excerpt,post_status,comment_status,ping_status,post_password,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count';
$s203_from_cols = implode( ',', array_map( static fn( string $c ): string => 'p.' . $c, explode( ',', $s203_post_cols ) ) );
$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->posts} (ID,post_title,post_name,$s203_post_cols) SELECT i.post_id,'s203count',CONCAT('s203count-',i.post_id),$s203_from_cols FROM " . \Phongdao\DichVu\ChiMucTim\ChiMucTim::bang() . " i CROSS JOIN {$wpdb->posts} p WHERE p.ID = %d AND i.post_id > %d AND i.post_id <= %d", $s203_ids[0], $s203_base, $s203_base + 1001 ) );
$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->postmeta} (post_id,meta_key,meta_value) SELECT ID,'_phongdao_loi',%s FROM {$wpdb->posts} WHERE ID > %d AND ID <= %d", maybe_serialize( array( 'dong' => array( 's203count' ), 'nguoi_lam' => 'nguoi_viet' ) ), $s203_base, $s203_base + 1001 ) );
$s203_count_html = \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( 's203count' );
kt( 'tìm kho lớn: trang Tìm đếm đủ 1.001 tác phẩm, hiện 20 thẻ và phân trang tới trang 51', 1001 === $s203_tim::tim( 's203count' )['tong'] && str_contains( $s203_count_html, '20 trên 1.001 bài' ) && 20 === substr_count( $s203_count_html, '<article class="card ' ) && str_contains( $s203_count_html, 'trang=51' ), substr( $s203_count_html, 0, 160 ) );
$s203_last = $s203_tim::tim( 's203count', array( 'trang' => 51 ) );
kt( 'tìm kho lớn: trang cuối không bị chặn ở 20/500, đủ bài cuối và không lặp trang đầu', 1 === count( $s203_last['ds'] ) && ! in_array( $s203_last['ds'][0]['id'], array_column( $s203_tim::tim( 's203count' )['ds'], 'id' ), true ) );
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE post_id > %d AND post_id <= %d", $s203_base, $s203_base + 1001 ) );
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE ID > %d AND ID <= %d", $s203_base, $s203_base + 1001 ) );
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . \Phongdao\DichVu\ChiMucTim\ChiMucTim::bang() . ' WHERE post_id > %d AND post_id <= %d', $s203_base, $s203_base + 1001 ) );
// Dọn đúng các fixture vừa tạo, không đụng dữ liệu mẫu khác.
foreach ( $s203_ids as $s203_id ) { wp_delete_post( $s203_id, true ); }
wp_reset_query();
