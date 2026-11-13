<?php
/** Hồi quy D-109, nạp bởi kiem-thu.php. Chỉ dùng dữ liệu kỹ thuật. */
use Phongdao\DichVu\LuuPhan\LuuTheTho;
use Phongdao\DichVu\LuuPhan\GanHinhThuc;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\DichVu\LuuPhan\LuuPhanLoai;
use Phongdao\GiaoTiep\QuanTri\DanhMuc;
use Phongdao\GiaoTiep\QuanTri\SuaTacPham;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\TheTho;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\KiemPhan;

wp_set_current_user( $ai_ql );
$p200 = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'draft', 'post_title' => '[UX-TEST] Trang sửa đầy đủ' ) );
wp_set_object_terms( $p200, 'ca-dao', KhaiBao::THE_LOAI );
$nc200 = array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
LuuPhan::luu( $p200, 'loi', array( 'dong' => array( '[UX-TEST] technical line 01' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc200 );
$extra200 = wp_insert_term( '[UX-TEST] Hình thức bổ sung ' . $p200, 'hinh_thuc' );
wp_set_object_terms( $p200, array( (int) $extra200['term_id'] ), 'hinh_thuc', true );
$state200 = LuuTheTho::phien( $p200 );
$ma200 = array_key_first( TheTho::DS );
kt( 'Thể thơ: không có quyền hoặc không có phiên thì không ghi lựa chọn', ! LuuTheTho::luu( $p200, 'nguoi', $ma200, $state200, 0 )['ok'] && ! LuuTheTho::luu( $p200, 'nguoi', $ma200, '', $ai_ql )['ok'] && 'may' === LuuTheTho::lay( $p200 )['che_do'] );
kt( 'Thể thơ: không nhận mã hoặc chế độ ngoài danh sách', ! LuuTheTho::luu( $p200, 'nguoi', 'khong_co', $state200, $ai_ql )['ok'] && ! LuuTheTho::luu( $p200, 'khong_co', '', $state200, $ai_ql )['ok'] );
$manual200 = LuuTheTho::luu( $p200, 'nguoi', $ma200, $state200, $ai_ql );
$log200 = Viec::lay( (int) $manual200['viec'] );
kt( 'Thể thơ: người chọn, gắn nhãn, giữ hình thức bổ sung và ghi người quyết trong lịch sử', $manual200['ok'] && $manual200['doi'] && 'nguoi' === LuuTheTho::lay( $p200 )['che_do'] && has_term( $ma200, 'hinh_thuc', $p200 ) && has_term( (int) $extra200['term_id'], 'hinh_thuc', $p200 ) && 'the_tho' === $log200['phan'] && $ai_ql === $log200['nguoi_quyet'] );
kt( 'Thể thơ: phiên cũ không ghi đè lựa chọn vừa lưu', ! LuuTheTho::luu( $p200, 'may', '', $state200, $ai_ql )['ok'] && 'nguoi' === LuuTheTho::lay( $p200 )['che_do'] );
LuuPhan::luu( $p200, 'loi', array( 'dong' => array( '[UX-TEST] changed technical line 02', '[UX-TEST] technical line 03' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc200 );
kt( 'Thể thơ: sửa lời về sau giữ lựa chọn người và hình thức bổ sung', $ma200 === LuuTheTho::lay( $p200 )['ma'] && has_term( $ma200, 'hinh_thuc', $p200 ) && has_term( (int) $extra200['term_id'], 'hinh_thuc', $p200 ) );
$none200 = LuuTheTho::luu( $p200, 'nguoi', '', LuuTheTho::phien( $p200 ), $ai_ql );
GanHinhThuc::gan( $p200, 'loi' );
$tags200 = wp_get_object_terms( $p200, 'hinh_thuc' );
kt( 'Thể thơ: người chọn không gắn nhãn vẫn được giữ khi máy tính lại', $none200['ok'] && 0 === count( array_filter( $tags200, static fn( $t ) => isset( TheTho::DS[ $t->slug ] ) ) ) && has_term( (int) $extra200['term_id'], 'hinh_thuc', $p200 ) );
$auto200 = LuuTheTho::luu( $p200, 'may', '', LuuTheTho::phien( $p200 ), $ai_ql );
$predicted200 = TheTho::nhan( PhanBai::lay( $p200, 'loi' )['dong'] );
kt( 'Thể thơ: quay lại dùng nhận diện máy gắn đúng kết quả, giữ phần bổ sung', $auto200['ok'] && 'may' === LuuTheTho::lay( $p200 )['che_do'] && has_term( $predicted200, 'hinh_thuc', $p200 ) && has_term( (int) $extra200['term_id'], 'hinh_thuc', $p200 ) );
$before200 = LuuTheTho::lay( $p200 ); $terms200 = wp_get_object_terms( $p200, 'hinh_thuc', array( 'fields' => 'ids' ) ); $n200 = count( Viec::lich_su( 'post', $p200, 50 ) );
$fail200 = static function ( int $id ): void { global $p200; if ( $id === $p200 ) { throw new RuntimeException( '[UX-TEST] lỗi sau ghi quan hệ' ); } };
add_action( 'set_object_terms', $fail200 );
$bad200 = LuuTheTho::luu( $p200, 'nguoi', '', LuuTheTho::phien( $p200 ), $ai_ql );
remove_action( 'set_object_terms', $fail200 );
kt( 'Thể thơ: lỗi gắn nhãn rollback cả lựa chọn, quan hệ và lịch sử', ! $bad200['ok'] && $before200 === LuuTheTho::lay( $p200 ) && $terms200 === wp_get_object_terms( $p200, 'hinh_thuc', array( 'fields' => 'ids' ) ) && $n200 === count( Viec::lich_su( 'post', $p200, 50 ) ) );

$name200 = '[UX-TEST] Danh mục ' . $p200;
$term200 = DanhMuc::tao( 'chu_de', $name200 );
$duplicate200 = DanhMuc::tao( 'chu_de', $name200 );
kt( 'Danh mục: thêm/trùng tên trả một ID, không tự gắn vào tác phẩm', $term200['ok'] && $duplicate200['ok'] && $duplicate200['trung'] && $term200['id'] === $duplicate200['id'] && ! has_term( $term200['id'], 'chu_de', $p200 ) );
kt( 'Danh mục: từ chối thể loại, bộ không có, tên trống hoặc quá dài', ! DanhMuc::tao( 'the_loai', $name200 )['ok'] && ! DanhMuc::tao( 'khong_co', $name200 )['ok'] && ! DanhMuc::tao( 'chu_de', '' )['ok'] && ! DanhMuc::tao( 'chu_de', str_repeat( 'a', 101 ) )['ok'] );
wp_set_current_user( 0 );
kt( 'Danh mục: khách không có quyền tạo mục', ! DanhMuc::tao( 'chu_de', '[UX-TEST] Không được tạo' )['ok'] );
wp_set_current_user( $ai_ql );
$assigned200 = LuuPhanLoai::luu( $p200, 'chu_de', array( $term200['id'] ), array(), $ai_ql );
kt( 'Danh mục: mục mới có thể chọn và lưu qua đường phân loại hiện có', $assigned200['ok'] && has_term( $term200['id'], 'chu_de', $p200 ) && in_array( $term200['id'], array_map( static fn( $t ) => $t->term_id, LuuPhanLoai::lua_chon( 'chu_de' ) ), true ) );
$count200 = count( Viec::lich_su( 'post', $p200, 50 ) );
$blockLock200 = static function ( string $sql ) use ( $p200 ): string {
	global $wpdb;
	return str_contains( $sql, "SELECT ID FROM {$wpdb->posts} WHERE ID = {$p200} FOR UPDATE" ) ? "SELECT ID FROM {$wpdb->posts} WHERE ID = -1 FOR UPDATE" : $sql;
};
add_filter( 'query', $blockLock200 );
$noLock200 = LuuPhanLoai::luu( $p200, 'chu_de', array(), array( $term200['id'] ), $ai_ql );
remove_filter( 'query', $blockLock200 );
kt( 'Phân loại: không lấy được khoá bài thì không ghi quan hệ hoặc lịch sử', ! $noLock200['ok'] && has_term( $term200['id'], 'chu_de', $p200 ) && $count200 === count( Viec::lich_su( 'post', $p200, 50 ) ) );
add_action( 'set_object_terms', $fail200 );
$badTax200 = LuuPhanLoai::luu( $p200, 'chu_de', array(), array( $term200['id'] ), $ai_ql );
remove_action( 'set_object_terms', $fail200 );
kt( 'Phân loại: lỗi sau ghi quan hệ hoàn tác cả quan hệ và lịch sử', ! $badTax200['ok'] && has_term( $term200['id'], 'chu_de', $p200 ) && $count200 === count( Viec::lich_su( 'post', $p200, 50 ) ) );
$rows200 = array();for ( $i200 = 1; $i200 <= 36; $i200++ ) { $rows200[] = array( 'tu' => '[UX-TEST] item ' . $i200, 'loi_giai' => '[UX-TEST] Explanation ' . $i200, 'nguoi_lam' => NguoiLam::NGUOI_VIET ); }
LuuPhan::luu( $p200, 'chu_thich', $rows200, $nc200 );
$_GET = array( 'id' => $p200 );
$html200 = SuaTacPham::hien();
kt( 'Trang sửa: bản nháp có nhãn lưu chính xác, cảnh báo nằm trong phần Lời', str_contains( $html200, 'Lưu phần đã duyệt vào bài nháp' ) && ! str_contains( $html200, 'Lời đang đăng' ) && str_contains( $html200, 'Lưu nháp vào Hộp việc' ) && str_contains( $html200, 'Lời đang lưu trong bài chưa công khai' ) );
kt( 'Trang sửa: mục lục có Phân loại, Ảnh, Căn cứ, Lịch sử và bản đã lưu', str_contains( $html200, 'data-toi-phan="phan-loai"' ) && str_contains( $html200, 'data-toi-phan="phan-media"' ) && str_contains( $html200, 'data-toi-phan="can-cu-noi-bo"' ) && str_contains( $html200, 'data-toi-phan="lich-su"' ) && str_contains( $html200, 'Xem bản đã lưu trên trang đọc' ) );
kt( 'Trang sửa: 36 mục được dựng gọn, thêm/lọc ở đầu, có lối nhảy mục cần xem', 36 === count( PhanBai::lay( $p200, 'chu_thich' )['muc'] ) && substr_count( $html200, 'class="phongdao-muc-gon"' ) >= 36 && substr_count( $html200, 'class="phongdao-muc-gon" open' ) <= 2 && str_contains( $html200, 'data-loc-hang' ) && str_contains( $html200, 'data-mo-hang-muc' ) );
foreach ( array( 've' => array( 'boi_canh', 'chu_thich' ), 'cau-do' => array( 'cau_hoi', 'y_nghia' ), 'tuc-ngu' => array( 'nghia_den', 'chu_thich' ) ) as $genre200 => [ $first200, $second200 ] ) {
	wp_set_object_terms( $p200, $genre200, KhaiBao::THE_LOAI );$_GET = array( 'id' => $p200 );$h200 = SuaTacPham::hien();
	kt( 'Trang sửa: ưu tiên phần theo thể loại ' . $genre200, strpos( $h200, 'id="phan-' . $first200 . '"' ) < strpos( $h200, 'id="phan-' . $second200 . '"' ) );
}
wp_delete_post( $p200, true );wp_delete_term( $term200['id'], 'chu_de' );wp_delete_term( $extra200['term_id'], 'hinh_thuc' );$_GET = array();
