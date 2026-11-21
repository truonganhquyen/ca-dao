<?php

// Cuối bộ WordPress: trước đó toàn bộ hồi quy chạy với Meili tắt mặc định.
use Phongdao\DichVu\TimMeili\ChiMuc as MeiliChiMuc;
use Phongdao\DichVu\TimMeili\MayTim as MeiliMay;
use Phongdao\DichVu\TimMeili\Tim as MeiliTim;

kt( 'Meili mặc định tắt, không đòi dịch vụ ngoài ở mọi luồng cũ', ! MeiliMay::bat() );
define( 'PHONGDAO_MEILI_BAT', true );
define( 'PHONGDAO_MEILI_URL', 'http://127.0.0.1:17770' );
define( 'PHONGDAO_MEILI_KEY', 'technical-test-key-not-a-real-secret' );
wp_set_current_user( $ai_ql );
$meili_p = \Phongdao\GiaoTiep\BienTap\ThemBai::tao( array( 'the_loai' => 'ca-dao', 'loi' => "Fixture đập dữ liệu kiểm thử\nDòng kỹ thuật thêm riêng biệt", 'ten' => 'Fixture Meili', 'nguon' => '', 'dang' => true ), $ai_ql )['bai'];
$meili_d = MeiliChiMuc::doc( 'bai:' . $meili_p, true )[0];
kt( 'chỉ xuất bản tìm; không đưa hồ sơ nguồn và người góp vào engine', ! isset( $meili_d['nguon'], $meili_d['ten_nguoi_gop'] ) && str_contains( $meili_d['loi_kd'], 'dap' ) );
wp_update_post( array( 'ID' => $meili_p, 'post_status' => 'draft' ) );
kt( 'bài ẩn không được xuất công khai nhưng có trong chỉ mục biên tập', ! MeiliChiMuc::doc( 'bai:' . $meili_p, true ) && MeiliChiMuc::doc( 'bai:' . $meili_p, false ) );
wp_update_post( array( 'ID' => $meili_p, 'post_status' => 'publish', 'post_password' => 'fixture' ) );
kt( 'bài có mật khẩu không xuất công khai', ! MeiliChiMuc::doc( 'bai:' . $meili_p, true ) );
wp_update_post( array( 'ID' => $meili_p, 'post_password' => '' ) );
$meili_n = 0; $meili_bad = false; $meili_sua_giua = false; $meili_hits = array( array_intersect_key( $meili_d, array_flip( array( 'muc', 'phan', 'ban' ) ) ) );
$meili_http = static function ( $pre, $args, $url ) use ( &$meili_n, &$meili_bad, &$meili_hits, &$meili_sua_giua, $meili_p ) {
	if ( ! str_starts_with( $url, PHONGDAO_MEILI_URL ) ) { return $pre; }
	++$meili_n;
	$data = json_decode( $args['body'] ?? '{}', true );
	if ( $meili_sua_giua && str_ends_with( $url, '/documents/delete' ) ) { $meili_sua_giua = false; MeiliChiMuc::doi( 'bai:' . $meili_p ); }
	if ( str_ends_with( $url, '/multi-search' ) ) {
		kt( 'multi-search chỉ dùng tên public từ cấu hình server, không lấy khoá từ khách', str_ends_with( $data['queries'][0]['indexUid'], '_public' ) && ! str_contains( $args['body'], PHONGDAO_MEILI_KEY ) );
		$r = array( 'results' => array_map( static fn( $q ) => array( 'hits' => $meili_hits ), $data['queries'] ) );
	} elseif ( str_contains( $url, '/tasks/' ) ) { $r = array( 'status' => $meili_bad ? 'failed' : 'succeeded' ); }
	else { $r = array( 'taskUid' => 123 ); }
	return array( 'response' => array( 'code' => 200 ), 'body' => wp_json_encode( $r ) );
};
add_filter( 'pre_http_request', $meili_http, 99, 3 );
update_option( MeiliChiMuc::TT, array( 'san_sang' => true, 'public' => 'pd_fixture_public', 'staff' => 'pd_fixture_staff' ), false );
$meili_bad = true;
try { MeiliChiMuc::dong_bo(); $meili_loi = false; } catch ( Throwable $e ) { $meili_loi = true; }
kt( 'task failed: không báo đồng bộ xong, giữ hàng đợi để thử lại', $meili_loi && MeiliChiMuc::cho( 1 ) && ! MeiliChiMuc::san_sang() );
$meili_admin = \Phongdao\GiaoTiep\BienTap\ThemBai::kiem( 'Fixture đập dữ liệu kiểm thử' );
kt( 'admin: chỉ mục đang cập nhật báo chưa kiểm trùng, không báo sạch', false === $meili_admin['kiem_trung_ok'] && str_contains( $meili_admin['thong_bao'], 'Chưa kiểm trùng' ) );
$meili_bad = false;
$meili_sua_giua = true;
MeiliChiMuc::dong_bo();
kt( 'sửa trong lúc đồng bộ giữ token mới, không xác nhận sạch theo bản cũ', MeiliChiMuc::cho( 1 ) && ! MeiliChiMuc::san_sang() );
while ( MeiliChiMuc::dong_bo() ) { /* xác nhận sau khi cả hai chỉ mục đã ghi thành công */ }
kt( 'thử lại thành công: hết hàng đợi, máy tìm sẵn sàng', MeiliChiMuc::san_sang() );
$meili_n = 0;
$meili_kq = MeiliTim::tim( 'Fixture đập dữ liệu chạy thử' );
kt( 'một HTTP request gom các nhánh và đối chiếu lại lời trong WordPress', 1 === $meili_n && count( $meili_kq ) === 1 && ! $meili_kq[0]['khop'] );
$meili_hits[0]['ban'] = str_repeat( '0', 64 );
try { MeiliTim::tim( 'Fixture đập dữ liệu kiểm thử' ); $meili_cu = false; } catch ( Throwable $e ) { $meili_cu = true; }
kt( 'index sai bản chữ báo chưa kiểm được và hẹn sửa, không báo sạch', $meili_cu && ! MeiliChiMuc::san_sang() );
while ( MeiliChiMuc::dong_bo() ) { }
$meili_hits[0]['ban'] = $meili_d['ban'];
wp_update_post( array( 'ID' => $meili_p, 'post_status' => 'draft' ) );
while ( MeiliChiMuc::dong_bo() ) { }
kt( 'engine trả nhầm bài vừa ẩn cũng không lọt ra khách', ! MeiliTim::tim( 'Fixture đập dữ liệu kiểm thử' ) );
$meili_truoc = get_option( MeiliChiMuc::TT ); $meili_bad = true;
try { MeiliChiMuc::dung_lai(); $meili_xd_loi = false; } catch ( Throwable $e ) { $meili_xd_loi = true; }
kt( 'dựng lại lỗi giữ nguyên tên chỉ mục hoạt động', $meili_xd_loi && $meili_truoc === get_option( MeiliChiMuc::TT ) );
$meili_bad = false;
kt( 'dựng lại chỉ đổi index sau khi mọi lô thành công', MeiliChiMuc::dung_lai() > 0 && $meili_truoc !== get_option( MeiliChiMuc::TT ) );
wp_set_current_user( 0 );
try { MeiliTim::tim( 'fixture', false ); $meili_chan = false; } catch ( Throwable $e ) { $meili_chan = true; }
kt( 'khách không gọi được tìm trong chỉ mục staff', $meili_chan );
remove_filter( 'pre_http_request', $meili_http, 99 );
wp_set_current_user( $ai_ql );
wp_delete_post( $meili_p, true );
