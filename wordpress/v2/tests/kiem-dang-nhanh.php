<?php
/**
 * Hồi quy Bài mới → Đăng nhanh (D-119, T-216): mỗi câu kho một thẻ; Đăng đi đúng đường "Xem trước và đăng" của Kho; Ý nghĩa
 * (người viết) và "Có từ ngữ thô tục" chỉ khi người điền / đánh dấu; Để sau là dấu Để dành; chỉ người có quyền duyệt
 * đăng. Chỉ dữ liệu kỹ thuật [UX-TEST].
 */
use Phongdao\DichVu\UngVien\DanhDau;
use Phongdao\GiaoTiep\QuanTri\DanhSachKho as Kho216;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\NguoiLam;

global $wpdb;
wp_set_current_user( $ai_ql );
$them_dn = static function ( string $ma, string $loi ) use ( $wpdb ): int {
	$wpdb->insert( Kho::bang(), array( 'nguon_id' => 'dn-thu', 'ma_goc' => $ma, 'url' => 'https://example.org/dn-thu/' . $ma . '/', 'loai' => 'ca_dao', 'tieu_de' => '[UX-TEST] Đăng nhanh ' . $ma, 'loi' => $loi, 'du_lieu' => '{}', 'ngay_lay' => current_time( 'mysql' ), 'trang_thai' => 'moi', 'doc_trang' => '2026-11-17' ) );
	return (int) $wpdb->insert_id;
};
$k1 = $them_dn( 'mot', "[UX-TEST] Đăng nhanh qwz dòng một\n[UX-TEST] Đăng nhanh qwz dòng hai" );
$k2 = $them_dn( 'hai', "[UX-TEST] Chín tầng mây trắng bay xa vbx\nRuộng đồng lúa chín vàng ươm vbx" );
$k3 = $them_dn( 'ba', "[UX-TEST] Đèn khuya leo lét canh thâu jkm\nGió bấc lùa qua khe cửa jkm" );
$k4 = $them_dn( 'bon', "[UX-TEST] Đăng nhanh qwz dòng một\n[UX-TEST] Đăng nhanh qwz dòng hai nữa" );

// ---------- Màn ----------
$_GET   = array( 'page' => Kho216::TRANG, 'nguon' => 'dn-thu' );
$h_ds   = Kho216::hien();
$_GET   = array( 'page' => Kho216::TRANG, 'nguon' => 'dn-thu', 'xem' => 'nhanh', 'so' => '2' );
$h_nh   = Kho216::hien();
$_GET   = array();
kt(
	'Đăng nhanh: danh sách Bài mới có lối "Đăng nhanh"; thẻ theo đúng bộ lọc và thứ tự của danh sách (mới trước — thẻ 2 / 4), có lời, nguồn, ô Ý nghĩa tuỳ chọn, công tắc thô tục chưa đánh dấu, nút Đăng, Để sau, Sang thẻ sau, nonce',
	str_contains( $h_ds, 'xem=nhanh' ) && str_contains( $h_nh, 'Thẻ 2 / 4' ) && str_contains( $h_nh, '[UX-TEST] Đèn khuya leo lét canh thâu jkm' ) && ! str_contains( $h_nh, 'Chín tầng mây' )
		&& str_contains( $h_nh, 'name="y_nghia"' ) && (bool) preg_match( '/<input type="checkbox" name="tho_tuc" value="1">/', $h_nh ) && str_contains( $h_nh, 'value="dang"' ) && str_contains( $h_nh, 'value="de_sau"' )
		&& str_contains( $h_nh, 'Sang thẻ sau' ) && str_contains( $h_nh, 'name="_wpnonce"' ),
	substr( (string) strstr( $h_nh, 'phongdao-dn-the' ), 0, 1500 )
);
$soan_dn = (int) wp_insert_user( array( 'user_login' => 'ux_test_soan_dn', 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ) );
( new WP_User( $soan_dn ) )->add_cap( 'cadao_soan' );
wp_set_current_user( $soan_dn );
$_GET    = array( 'page' => Kho216::TRANG, 'nguon' => 'dn-thu', 'xem' => 'nhanh' );
$h_soan  = Kho216::hien();
$_GET    = array();
$kq_soan = Kho216::dang_nhanh_mot( $k1, array( 'lam' => 'dang', 'ban' => 'moi' ), $soan_dn );
wp_set_current_user( $ai_ql );
kt( 'Đăng nhanh: người chỉ soạn không có lối Đăng nhanh, không đăng được', ! str_contains( $h_soan, 'phongdao-dn-the' ) && ! str_contains( $h_soan, 'xem=nhanh' ) && ! $kq_soan['ok'] && 0 === (int) Kho::lay( $k1 )->post_id, $kq_soan );

// ---------- Đăng ----------
$kq1  = Kho216::dang_nhanh_mot( $k1, array( 'lam' => 'dang', 'ban' => 'moi', 'y_nghia' => 'Câu ca dao nói về lời hẹn', 'tho_tuc' => '1' ), $ai_ql );
$bai1 = (int) Kho::lay( $k1 )->post_id;
$yn1  = PhanBai::lay( $bai1, 'y_nghia' );
kt(
	'Đăng nhanh: Đăng tạo bài đã đăng qua đường của Kho; Ý nghĩa người điền lưu là người viết (không AI); "Có từ ngữ thô tục" bật vì người đánh dấu; lời báo có tên bài và lời nhắc trình bày (không chặn)',
	$kq1['ok'] && $bai1 > 0 && $bai1 === $kq1['bai'] && 'publish' === get_post_status( $bai1 ) && 'Câu ca dao nói về lời hẹn' === ( $yn1['chu'] ?? '' ) && empty( $yn1['ai'] ) && NguoiLam::NGUOI_VIET === ( $yn1['nguoi_lam'] ?? '' )
		&& null !== PhanBai::lay( $bai1, 'tho_tuc' ) && str_contains( $kq1['chu'], 'Đã đăng «' ) && str_contains( $kq1['chu'], 'Nhắc (không chặn lưu)' ),
	$kq1
);
$kq2  = Kho216::dang_nhanh_mot( $k2, array( 'lam' => 'dang', 'ban' => 'moi', 'y_nghia' => '   ' ), $ai_ql );
$bai2 = (int) Kho::lay( $k2 )->post_id;
kt( 'Đăng nhanh: để trống Ý nghĩa thì đăng chưa có nghĩa; không đánh dấu thì không bật thô tục (máy không tự bật)', $kq2['ok'] && $bai2 > 0 && null === PhanBai::lay( $bai2, 'y_nghia' ) && null === PhanBai::lay( $bai2, 'tho_tuc' ), $kq2 );
$kq_lai = Kho216::dang_nhanh_mot( $k2, array( 'lam' => 'dang', 'ban' => 'moi' ), $ai_ql );
kt( 'Đăng nhanh: câu đã đăng (đổi trạng thái sau lúc mở thẻ) không đăng lần nữa', ! $kq_lai['ok'] && $bai2 === (int) Kho::lay( $k2 )->post_id, $kq_lai );

$kq4 = Kho216::dang_nhanh_mot( $k4, array( 'lam' => 'dang', 'ban' => 'moi' ), $ai_ql );
kt( 'Đăng nhanh: câu gần giống bài đã đăng thì máy dừng ở thẻ "Có thể trùng" trong Hộp việc, không đăng', ! $kq4['ok'] && str_contains( $kq4['chu'], 'Có thể trùng' ) && 0 === (int) Kho::lay( $k4 )->post_id, $kq4 );
$_GET = array( 'page' => Kho216::TRANG, 'nguon' => 'dn-thu', 'xem' => 'nhanh', 'q' => 'qwz' );
$h_tr = Kho216::hien();
$_GET = array();
kt( 'Đăng nhanh: thẻ của câu chờ quyết trùng không có nút Đăng, có lời báo quyết ở Hộp việc', str_contains( $h_tr, 'Câu đang chờ quyết trùng' ) && ! str_contains( $h_tr, 'value="dang"' ), substr( (string) strstr( $h_tr, 'phongdao-dn-the' ), 0, 800 ) );

// ---------- Chủ đề máy gợi ý (D-121): chip nét đứt chưa đánh dấu; người bấm thì gắn khi Đăng ----------
$k5     = $them_dn( 'nam', "[UX-TEST] Hò ơi thuyền ai xuôi bến zqp\nNước trong veo bóng trăng chao zqp" );
$m5     = Kho::lay( $k5 );
$gy5    = \Phongdao\DichVu\GoiY\GoiY::cho_kho( $m5 );
$_GET   = array( 'page' => Kho216::TRANG, 'nguon' => 'dn-thu', 'xem' => 'nhanh', 'q' => 'zqp' );
$h_gy   = Kho216::hien();
$_GET   = array();
$cd_dn  = wp_insert_term( '[UX-TEST] Chủ đề Đăng nhanh', 'chu_de', array( 'slug' => 'ux-test-chu-de-dang-nhanh' ) );
$kq5    = Kho216::dang_nhanh_mot( $k5, array( 'lam' => 'dang', 'ban' => 'moi', 'pl' => array( 'chu_de:' . (int) $cd_dn['term_id'], 'tac_gia:1', 'chu_de:abc' ) ), $ai_ql );
$bai5   = (int) Kho::lay( $k5 )->post_id;
kt(
	'Đăng nhanh: chủ đề, vùng máy gợi ý (nếu có) là chip nét đứt chưa đánh dấu; Đăng kèm mục người bấm thì gắn mục đó (bỏ giá trị lạ), có Lịch sử; có thanh tiến độ và gợi ý phím tắt',
	( $gy5 ? str_contains( $h_gy, 'class="pq-chip goi-y"' ) && str_contains( $h_gy, 'name="pl[]" value="' . $gy5[0]['loai'] . ':' . (int) $gy5[0]['term']->term_id . '">' ) : ! str_contains( $h_gy, 'name="pl[]"' ) )
		&& str_contains( $h_gy, '<progress class="pq-tien-do"' ) && str_contains( $h_gy, '<kbd>Ctrl</kbd>+<kbd>Enter</kbd> Đăng' )
		&& $kq5['ok'] && $bai5 > 0 && has_term( (int) $cd_dn['term_id'], 'chu_de', $bai5 ) && str_contains( $kq5['chu'], 'Đã gắn chủ đề.' )
		&& (bool) array_filter( \Phongdao\KhoLuu\Viec::lich_su( 'post', $bai5, 20 ), static fn( array $r ): bool => 'chu_de' === $r['phan'] ),
	array( $gy5 ? $gy5[0]['ma'] : 'không có gợi ý', $kq5 )
);
if ( $bai5 ) {
	wp_delete_post( $bai5, true );
}
wp_delete_term( (int) $cd_dn['term_id'], 'chu_de' );

// ---------- Để sau ----------
$kq3 = Kho216::dang_nhanh_mot( $k3, array( 'lam' => 'de_sau', 'ban' => 'moi' ), $ai_ql );
kt( 'Đăng nhanh: Để sau đánh dấu "Để dành", câu vẫn ở Bài mới (chưa đăng)', $kq3['ok'] && 'de_danh' === ( DanhDau::cua( $k3 )['loai'] ?? '' ) && 0 === (int) Kho::lay( $k3 )->post_id, $kq3 );

foreach ( array( $bai1, $bai2 ) as $x_dn ) {
	if ( $x_dn ) {
		wp_delete_post( $x_dn, true );
	}
}
DanhDau::bo( $k3, $ai_ql );
foreach ( \Phongdao\KhoLuu\Viec::mo( 'kho', $k4 ) as $v_dn ) {
	\Phongdao\KhoLuu\Viec::xoa( (int) $v_dn['id'] );
}
$wpdb->delete( \Phongdao\DichVu\ChongTrung\ChongTrung::bang( 'trung' ), array( 'a' => 'kho:' . $k4 ) );
$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Kho::bang() . ' WHERE nguon_id = %s', 'dn-thu' ) );
if ( $soan_dn ) {
	require_once ABSPATH . 'wp-admin/includes/user.php';
	wp_delete_user( $soan_dn );
}
wp_set_current_user( 0 );
