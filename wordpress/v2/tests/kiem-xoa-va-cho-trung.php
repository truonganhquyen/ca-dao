<?php

// Chỉ dữ liệu kỹ thuật tổng hợp trong WordPress kiểm thử; không xoá dữ liệu site thật.
use Phongdao\DichVu\XoaTacPham\XoaTacPham;
use Phongdao\DichVu\XuLyLo\Lo as Lo207;
use Phongdao\DichVu\ChongTrung\ChongTrung as Trung207;
use Phongdao\KhoLuu\Kho as Kho207;
use Phongdao\KhoLuu\Viec as Viec207;
use Phongdao\GiaoTiep\QuanTri\DanhSachTacPham as Ds207;
use Phongdao\GiaoTiep\QuanTri\DanhSachKho as KhoUi207;
use Phongdao\GiaoTiep\QuanTri\TrangLo as Trang207;

wp_set_current_user( $ai_ql );
$p207 = array();
foreach ( range( 1, 3 ) as $n207 ) {
	$p207[] = wp_insert_post( array( 'post_type' => 'tac_pham', 'post_status' => 'draft', 'post_title' => '[UX-TEST] Delete 207 ' . $n207 ) );
}
$lo207 = Lo207::tao( 'thung_rac', $p207, 'chon', array(), $ai_ql );
kt( 'thùng rác: xem trước không đổi bài, áp dụng được nhiều bài', $lo207['ok'] && 'draft' === get_post_status( $p207[0] ) && 3 === Lo207::ap_dung( $lo207['lo'], $ai_ql, true )['dem']['thanh_cong'] );
$_GET = array( 'tt' => 'trash', 'q' => 'Delete 207' );
$h207 = Ds207::hien();
kt( 'thùng rác: hàng có Khôi phục, Xoá vĩnh viễn; thanh chọn nhiều bài có hai thao tác', str_contains( $h207, 'name="lam_mot" value="xoa_vinh_vien:' . $p207[0] ) && str_contains( $h207, 'name="thao_tac_nhanh" value="lay_ra"' ) && str_contains( $h207, 'name="thao_tac_nhanh" value="xoa_vinh_vien"' ) );
$lo207 = Lo207::tao( 'xoa_vinh_vien', $p207, 'chon', array(), $ai_ql );
$_GET = array( 'lo' => $lo207['lo'] );
$h207 = Trang207::hien();
kt( 'xoá: màn xem trước cảnh báo không lấy lại, xác nhận bắt buộc, không tự chạy', str_contains( $h207, 'name="xac_nhan_xoa" value="1" required' ) && str_contains( $h207, 'không thể lấy lại' ) && ! str_contains( $h207, 'name="tu_chay"' ) );
kt( 'xoá: thiếu xác nhận phía máy chủ bị chặn, lô vẫn ở xem trước', ! Lo207::ap_dung( $lo207['lo'], $ai_ql, true )['ok'] && get_post( $p207[0] ) && Viec207::LO_XEM === Viec207::lay( $lo207['lo'] )['trang_thai'] );
wp_untrash_post( $p207[0] );
update_post_meta( $p207[1], '_test207', 'changed after preview' );
$kq207 = Lo207::ap_dung( $lo207['lo'], $ai_ql, true, true );
kt( 'xoá: bài khôi phục hoặc đổi phần sau xem trước báo xung đột; chỉ xoá bài còn nguyên', 2 === $kq207['dem']['xung_dot'] && 1 === $kq207['dem']['thanh_cong'] && get_post( $p207[0] ) && get_post( $p207[1] ) && ! get_post( $p207[2] ), $kq207 );
kt( 'xoá: không đưa bài ngoài thùng rác vào lô', ! Lo207::tao( 'xoa_vinh_vien', array( $p207[0] ), 'chon', array(), $ai_ql )['ok'] );
kt( 'xoá: người chỉ soạn và người đọc không có quyền', ! Lo207::tao( 'xoa_vinh_vien', array( $p207[1] ), 'chon', array(), $chi_soan )['ok'] && 'loi' === XoaTacPham::xoa( $p207[1], XoaTacPham::phien( $p207[1] ), $doc_gia )[0] );
$chan207 = static fn( $pre, $p ) => (int) $p->ID === (int) $p207[1] ? false : $pre;
add_filter( 'pre_delete_post', $chan207, 10, 2 );
$nk207 = count( Viec207::lich_su( 'post', $p207[1], 100 ) );
$kq207 = XoaTacPham::xoa( $p207[1], XoaTacPham::phien( $p207[1] ), $ai_ql );
remove_filter( 'pre_delete_post', $chan207, 10 );
kt( 'xoá: WordPress từ chối thì hoàn nguyên nhật ký, bài và phần vẫn còn', 'loi' === $kq207[0] && get_post( $p207[1] ) && 'changed after preview' === get_post_meta( $p207[1], '_test207', true ) && $nk207 === count( Viec207::lich_su( 'post', $p207[1], 100 ) ) );
$chan207 = static fn( $sql ) => preg_match( '/^INSERT\s+INTO\s+`?' . preg_quote( Viec207::bang(), '/' ) . '`?/i', $sql ) ? 'TEST207_AUDIT_FAIL' : $sql;
add_filter( 'query', $chan207 );
$kq207 = XoaTacPham::xoa( $p207[1], XoaTacPham::phien( $p207[1] ), $ai_ql );
remove_filter( 'query', $chan207 );
kt( 'xoá: nhật ký lỗi thì không xoá bài', 'loi' === $kq207[0] && get_post( $p207[1] ) );

// Tạo kho không chạy bộ dò để chủ động mô phỏng cặp mất thẻ, nhiều cặp và cờ chờ sót.
$k207 = array();
foreach ( range( 1, 5 ) as $n207 ) {
	$wpdb->insert( Kho207::bang(), array( 'nguon_id' => 'test207', 'ma_goc' => (string) $n207, 'tieu_de' => '[UX-TEST] Repair 207 ' . $n207, 'loi' => 'technical fixture 207 ' . $n207, 'du_lieu' => '{}', 'ngay_lay' => gmdate( 'Y-m-d H:i:s' ), 'trang_thai' => 'moi' ) );
	$k207[] = (int) $wpdb->insert_id;
}
$target207 = $p207[0];
Trung207::ghi_cho( 'kho:' . $k207[0], 'bai:' . $target207, 'gan_giong', 80, 'technical fixture' );
foreach ( Viec207::mo( 'kho', $k207[0], 'co_the_trung' ) as $v207 ) { Viec207::xoa( $v207['id'] ); }
$_GET = array( 'nguon' => 'test207' );
$h207 = KhoUi207::hien();
kt( 'chờ trùng: GET không tạo thẻ; hàng mất thẻ vẫn có nút kiểm tra POST và nonce riêng', array() === Viec207::mo( 'kho', $k207[0], 'co_the_trung' ) && str_contains( $h207, 'value="' . KhoUi207::DOI_SOAT . '"' ) && str_contains( $h207, 'Kiểm tra chờ trùng' ) && str_contains( $h207, '_wpnonce' ) );
$r207 = Trung207::doi_soat_cho( $k207[0], $ai_ql );
$again207 = Trung207::doi_soat_cho( $k207[0], $ai_ql );
kt( 'chờ trùng: nối lại đúng cặp, bấm lại không tạo thêm thẻ, chưa tự quyết', $r207['ok'] && $r207['viec'] === $again207['viec'] && 1 === count( Viec207::mo( 'kho', $k207[0], 'co_the_trung' ) ) && Trung207::dang_cho( $k207[0] ) && 'bai:' . $target207 === Viec207::lay( $r207['viec'] )['noi_dung']['b'] );
Trung207::ghi_cho( 'kho:' . $k207[1], 'bai:' . $target207, 'gan_giong', 80, 'technical fixture' );
$v207 = Viec207::mo( 'kho', $k207[1], 'co_the_trung' )[0];
Viec207::dong( $v207['id'], Viec207::DE_SAU, 'de_sau', $ai_ql );
Trung207::dat( 'kho:' . $k207[1], 'bai:' . $target207, 'de_sau' );
$r207 = Trung207::doi_soat_cho( $k207[1], $ai_ql );
kt( 'chờ trùng: thẻ Để sau mở bằng trạng thái Để sau, không đưa lại Chờ', $v207['id'] === $r207['viec'] && Viec207::DE_SAU === $r207['tt'] );
Trung207::ghi_cho( 'kho:' . $k207[2], 'bai:' . $p207[1], 'gan_giong', 80, 'technical fixture' );
Trung207::ghi_cho( 'kho:' . $k207[2], 'bai:' . $target207, 'gan_giong', 80, 'technical fixture' );
$r207 = Trung207::doi_soat_cho( $k207[2], $ai_ql );
kt( 'chờ trùng: cặp với bài trong rác hết hiệu lực; còn cặp khác thì vẫn chờ đúng cặp', $r207['ok'] && Trung207::dang_cho( $k207[2] ) && 'bai:' . $target207 === Viec207::lay( $r207['viec'] )['noi_dung']['b'] && 1 === count( Viec207::mo( 'kho', $k207[2], 'co_the_trung' ) ) );
Trung207::ghi_cho( 'kho:' . $k207[3], 'bai:' . $target207, 'gan_giong', 80, 'technical fixture' );
$wpdb->update( Kho207::bang(), array( 'post_id' => $target207, 'trang_thai' => 'da_tao_bai' ), array( 'id' => $k207[3] ) );
$r207 = Trung207::doi_soat_cho( $k207[3], $ai_ql );
kt( 'chờ trùng: câu đã thành bài gỡ cờ chờ sót, không gộp hoặc xoá bài', $r207['ok'] && ! Trung207::dang_cho( $k207[3] ) && get_post( $target207 ) && (int) Kho207::lay( $k207[3] )->post_id === $target207 );
Trung207::ghi_cho( 'kho:' . $k207[4], 'bai:' . $target207, 'gan_giong', 80, 'technical fixture' );
$v207 = Viec207::mo( 'kho', $k207[4], 'co_the_trung' )[0];
Viec207::dong( $v207['id'], Viec207::XONG, 'khac', $ai_ql );
$r207 = Trung207::doi_soat_cho( $k207[4], $ai_ql );
kt( 'chờ trùng: không tạo lại thẻ đã có quyết định của người, giữ lịch sử', $r207['ok'] && ! $r207['viec'] && ! Trung207::dang_cho( $k207[4] ) && 'khac' === Viec207::lay( $v207['id'] )['quyet'] );
kt( 'chờ trùng: người đọc không được đối soát', ! Trung207::doi_soat_cho( $k207[0], $doc_gia )['ok'] );

// Xoá thật chỉ fixture: dọn cặp chờ trong cùng giao dịch, giữ hồ sơ nguồn, ảnh, lịch sử và bài khác.
$src207 = wp_insert_post( array( 'post_type' => 'ho_so_nguon', 'post_status' => 'private', 'post_title' => '[UX-TEST] Source 207' ) );
$img207 = wp_insert_post( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_parent' => $p207[1], 'post_title' => '[UX-TEST] Attachment 207' ) );
$c207 = wp_insert_comment( array( 'comment_post_ID' => $p207[1], 'comment_content' => '[UX-TEST] Comment 207', 'comment_approved' => 1 ) );
Trung207::ghi_cho( 'kho:' . $k207[4], 'bai:' . $p207[1], 'gan_giong', 80, 'technical fixture' );
$chan207 = static fn( $sql ) => preg_match( '/^UPDATE\s+`?' . preg_quote( Trung207::bang( 'trung' ), '/' ) . '`?/i', $sql ) ? 'TEST207_PAIR_FAIL' : $sql;
$nk207 = count( Viec207::lich_su( 'post', $p207[1], 100 ) );
add_filter( 'query', $chan207 );
$kq207 = XoaTacPham::xoa( $p207[1], XoaTacPham::phien( $p207[1] ), $ai_ql );
remove_filter( 'query', $chan207 );
kt( 'xoá: lỗi sau khi WordPress đã xoá thì trả lại bài, phần, bình luận, ảnh gắn bài, thẻ và nhật ký', 'loi' === $kq207[0] && get_post( $p207[1] ) && 'changed after preview' === get_post_meta( $p207[1], '_test207', true ) && get_comment( $c207 ) && (int) get_post( $img207 )->post_parent === $p207[1] && Trung207::dang_cho( $k207[4] ) && $nk207 === count( Viec207::lich_su( 'post', $p207[1], 100 ) ), $kq207 );
$kq207 = XoaTacPham::xoa( $p207[1], XoaTacPham::phien( $p207[1] ), $ai_ql );
kt( 'xoá thành công: bài, phần, bình luận mất; ảnh, nguồn, bài khác và nhật ký được giữ; cờ chờ được dọn', 'thanh_cong' === $kq207[0] && ! get_post( $p207[1] ) && array() === get_post_meta( $p207[1] ) && ! get_comment( $c207 ) && get_post( $img207 ) && get_post( $src207 ) && get_post( $target207 ) && ! Trung207::dang_cho( $k207[4] ) && 'xoa_vinh_vien' === Viec207::lich_su( 'post', $p207[1], 1 )[0]['quyet'], $kq207 );
$wpdb->update( Kho207::bang(), array( 'post_id' => 0, 'trang_thai' => 'moi' ), array( 'id' => $k207[3] ) );
Trung207::ghi_cho( 'kho:' . $k207[3], 'bai:' . $target207, 'gan_giong', 80, 'technical fixture after restore' );
$r207 = Trung207::doi_soat_cho( $k207[3], $ai_ql );
kt( 'chờ trùng: tái phát hiện sau hết hiệu lực kỹ thuật mở thẻ mới, không mở lại quyết định bài khác', $r207['ok'] && $r207['viec'] && Trung207::dang_cho( $k207[3] ) && in_array( 'bai:' . $target207, Trung207::da_khac( 'kho:' . $k207[4] ), true ) );

// Gọi đúng controller với redirect/wp_die bị chặn trong môi trường kiểm thử.
add_filter( 'wp_die_handler', $chan_die_lo );
add_filter( 'wp_redirect', $chan_di_lo );
$gui207 = static function ( string $lop, string $ham, int $nguoi, array $than, string $nonce = '' ): string {
	wp_set_current_user( $nguoi );
	$_POST = $_REQUEST = $than + ( $nonce ? array( '_wpnonce' => wp_create_nonce( $nonce ) ) : array() );
	try { $lop::$ham(); return 'không dừng'; } catch ( \RuntimeException $e ) { return $e->getMessage(); }
};
$a207 = array( 'id' => $k207[3] );
$bad207 = $gui207( KhoUi207::class, 'kiem_trung', $ai_ql, $a207 );
$badRole207 = $gui207( KhoUi207::class, 'kiem_trung', $doc_gia, $a207, KhoUi207::DOI_SOAT . '_' . $k207[3] );
$ok207 = $gui207( KhoUi207::class, 'kiem_trung', $ai_ql, $a207, KhoUi207::DOI_SOAT . '_' . $k207[3] );
kt( 'kiểm tra chờ trùng qua POST: chặn thiếu nonce/người đọc, mở đúng thẻ khi hợp lệ', ! str_starts_with( $bad207, 'redirect:' ) && ! str_starts_with( $badRole207, 'redirect:' ) && str_contains( $ok207, 'viec=' . $r207['viec'] ), array( $bad207, $badRole207, $ok207 ) );
$single207 = $gui207( Trang207::class, 'tao_bai', $ai_ql, array( 'lam_mot' => 'thung_rac:' . $target207, 'ids' => array( $src207 ), 'pham_vi' => 'loc' ), 'phongdao_lo_tao_bai' );
parse_str( (string) parse_url( substr( $single207, strlen( 'redirect: ' ) ), PHP_URL_QUERY ), $params207 );
$saved207 = Viec207::lay( (int) ( $params207['lo'] ?? 0 ) );
kt( 'nút Thùng rác từng hàng chỉ xem trước đúng một bài, bỏ qua chọn/lọc khác', str_starts_with( $single207, 'redirect:' ) && array( $target207 ) === array_column( $saved207['noi_dung']['muc'] ?? array(), 0 ) && get_post( $target207 ) );
remove_filter( 'wp_die_handler', $chan_die_lo );
remove_filter( 'wp_redirect', $chan_di_lo );
wp_set_current_user( $ai_ql );
$_GET = array();
$_POST = $_REQUEST = array();
