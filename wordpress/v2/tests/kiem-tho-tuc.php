<?php
/**
 * Hồi quy công tắc "Có từ ngữ thô tục" (D-111): người bật tay, lưu qua đường lưu một phần có phiên và lịch sử; trang
 * đọc, thẻ bài che lời sau lời báo "Hiển thị lời" (tắt JavaScript vẫn mở được); bài bị che không vào trang chủ, Câu hôm
 * nay, Một câu ngẫu nhiên, Đọc tiếp; ảnh và mô tả khi chia sẻ link không in lời. Chỉ dữ liệu kỹ thuật [UX-TEST].
 */
use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\DichVu\DungTrang\DocTiep;
use Phongdao\DichVu\DungTrang\DuLieuTrangChu;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\GiaoTiep\BienTap\Module as BienTap202;
use Phongdao\GiaoTiep\Khoi\DanhSach;
use Phongdao\GiaoTiep\Khoi\TacPham;
use Phongdao\GiaoTiep\Khoi\Ve;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

wp_set_current_user( $ai_ql );
$nc202  = array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
$tao202 = static function ( string $ten, string $dong ) use ( $nc202 ): int {
	$id = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => $ten, 'post_name' => sanitize_title( $ten ) ) );
	wp_set_object_terms( $id, 'ca-dao', KhaiBao::THE_LOAI );
	LuuPhan::luu( $id, 'loi', array( 'dong' => array( $dong, $dong . ' hai' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc202 );
	return $id;
};
$an202 = $tao202( '[UX-TEST] Tên bài cần che', '[UX-TEST] Dòng cần che' );
$sau202 = $tao202( '[UX-TEST] Bài kế bên', '[UX-TEST] Dòng bài kế bên' );
$gui202 = static function ( array $than, ?string $phien = null ) use ( $an202 ): array {
	$r = new WP_REST_Request( 'POST' );
	$r->set_body_params( $than + array( 'id' => $an202, 'phan' => 'tho_tuc', 'phien_phan' => $phien ?? LuuPhan::phien( $an202, 'tho_tuc', PhanBai::lay( $an202, 'tho_tuc' ) ) ) );
	return BienTap202::luu_phan( $r );
};

// ---------- Quản trị: công tắc ở cột bên, lưu có phiên và lịch sử ----------
$_GET     = array( 'id' => (string) $an202 );
$h_sua202 = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
kt( 'thô tục: Sửa tác phẩm có công tắc "Từ ngữ thô tục" ở cột bên (Đang tắt, nút Bật), không nằm trong danh sách phần', str_contains( $h_sua202, '<section class="phongdao-sua-co" id="phan-tho_tuc">' ) && str_contains( $h_sua202, 'Đang tắt.' ) && str_contains( $h_sua202, 'value="tho_tuc"' ) && str_contains( $h_sua202, 'name="bat" value="1"' ) && ! str_contains( $h_sua202, '<details class="phongdao-sua-phan chua-co" id="phan-tho_tuc"' ) && ! str_contains( $h_sua202, 'data-mo-phan="tho_tuc"' ) );
$may202 = LuuPhan::luu( $an202, 'tho_tuc', array( 'nguoi_lam' => NguoiLam::NGUOI_VIET ), array( 'nguoi' => 0, 'goi' => KiemPhan::GOI_MAY ) );
kt( 'thô tục: máy không được bật công tắc', ! $may202['ok'] && null === PhanBai::lay( $an202, 'tho_tuc' ), $may202 );
$phien_cu202 = LuuPhan::phien( $an202, 'tho_tuc', null );
$bat202      = $gui202( array( 'bat' => '1' ) );
$ls202       = array_values( array_filter( Viec::lich_su( 'post', $an202, 20 ), static fn( array $r ): bool => 'tho_tuc' === $r['phan'] ) );
kt( 'thô tục: bấm Bật lưu phần (người viết), Lịch sử ghi một dòng', $bat202['ok'] && NguoiLam::NGUOI_VIET === ( PhanBai::lay( $an202, 'tho_tuc' )['nguoi_lam'] ?? '' ) && 1 === count( $ls202 ) && null === $ls202[0]['truoc'], array( $bat202, $ls202 ) );
$lai202 = $gui202( array( 'bat' => '1' ) );
kt( 'thô tục: bấm Bật lần nữa không ghi lịch sử giả', $lai202['ok'] && empty( $lai202['doi'] ) && 1 === count( array_filter( Viec::lich_su( 'post', $an202, 20 ), static fn( array $r ): bool => 'tho_tuc' === $r['phan'] ) ) );
$cu202 = $gui202( array(), $phien_cu202 );
kt( 'thô tục: bản mở cũ bấm Tắt bị chặn, công tắc vẫn bật', ! $cu202['ok'] && null !== PhanBai::lay( $an202, 'tho_tuc' ), $cu202 );
$h_bat202 = \Phongdao\GiaoTiep\QuanTri\SuaTacPham::hien();
kt( 'thô tục: đang bật thì cột bên ghi "Đang bật." và nút Tắt (không gửi ô bat)', str_contains( $h_bat202, '<strong>Đang bật.</strong>' ) && str_contains( $h_bat202, '>Tắt</button>' ) && ! str_contains( (string) strstr( (string) strstr( $h_bat202, 'id="phan-tho_tuc"' ), '</section>', true ), 'name="bat"' ) );
$_GET = array();

// ---------- Trang đọc, thẻ bài ----------
$tp202 = DocBai::tac_pham( get_post( $an202 ) );
$h202  = TacPham::hien( $tp202 );
$vi_che202 = strpos( $h202, '<details class="tho-tuc tho-tuc-bai">' );
$vi_loi202 = strpos( $h202, '[UX-TEST] Dòng cần che' );
kt( 'thô tục: trang đọc đóng sẵn khối che có đúng lời báo và nút "Hiển thị lời"; lời nằm trong khối, không trước khối', false !== $vi_che202 && false !== $vi_loi202 && $vi_loi202 > $vi_che202 && str_contains( $h202, '<span class="tho-tuc-bao">' . esc_html( Ve::BAO_THO_TUC ) . '</span>' ) && str_contains( $h202, 'Hiển thị lời' ) && 'Bài có từ ngữ thô tục - cân nhắc khi xem.' === Ve::BAO_THO_TUC );
kt( 'thô tục: tên bài trên trang (tiêu đề, đường dẫn vị trí) trung tính, không lộ tên', str_contains( $h202, '<h1 class="read-title">Ca dao có từ ngữ thô tục</h1>' ) && str_contains( $h202, '<span>Bài có từ ngữ thô tục</span></nav>' ) && false === strpos( substr( $h202, 0, (int) $vi_che202 ), '[UX-TEST] Tên bài cần che' ) );
$sua202 = \Phongdao\GiaoTiep\BienTap\CheDoSua::ngu_canh( get_post( $an202 ) );
$h_sua_doc202 = $sua202 ? TacPham::hien( $tp202, $sua202 ) : '';
kt( 'thô tục: người biên soạn ở chế độ sửa thấy khối mở sẵn và tên thật', str_contains( $h_sua_doc202, '<details class="tho-tuc tho-tuc-bai" open>' ) && str_contains( $h_sua_doc202, '[UX-TEST] Tên bài cần che' ) );
$the202 = DanhSach::the( DocBai::the( get_post( $an202 ) ) );
kt( 'thô tục: thẻ bài trong danh sách che tên, lời trong khối đóng; nút Đọc không đọc to tên bài', str_contains( $the202, '<details class="tho-tuc">' ) && strpos( $the202, '[UX-TEST] Dòng cần che' ) > strpos( $the202, '<details class="tho-tuc">' ) && strpos( $the202, '[UX-TEST] Tên bài cần che' ) > strpos( $the202, '<details class="tho-tuc">' ) && str_contains( $the202, 'aria-label="Đọc bài có từ ngữ thô tục"' ) );
$the_thuong202 = DanhSach::the( DocBai::the( get_post( $sau202 ) ) );
kt( 'thô tục: bài không bật vẫn hiện như cũ, không có khối che', ! str_contains( $the_thuong202, 'tho-tuc' ) && ! str_contains( TacPham::hien( DocBai::tac_pham( get_post( $sau202 ) ) ), 'class="tho-tuc' ) );

// ---------- Chỗ người đọc không chủ động tìm ----------
$moi202 = array_column( DuLieuTrangChu::vua_bo_sung( 20 ), 'id' );
kt( 'thô tục: Vừa bổ sung (trang chủ) không có bài bị che', ! in_array( $an202, $moi202, true ) && in_array( $sau202, $moi202, true ), $moi202 );
delete_transient( 'phongdao_cau_hom_nay_ds' );
DuLieuTrangChu::cau_hom_nay();
$hom202 = (array) get_transient( 'phongdao_cau_hom_nay_ds' );
kt( 'thô tục: danh sách chọn Câu hôm nay không có bài bị che', ! in_array( $an202, $hom202, true ) );
$doc_tiep202 = array_column( DocTiep::cho( get_post( $sau202 ) ), 'id' );
kt( 'thô tục: Đọc tiếp không gợi ý bài bị che (kể cả bài cùng thể loại ngay trước)', ! in_array( $an202, $doc_tiep202, true ), $doc_tiep202 );
$sql202 = array();
$bat_sql202 = static function ( string $sql ) use ( &$sql202 ): string {
	$sql202[] = $sql;
	return $sql;
};
// get_posts tắt bộ lọc truy vấn (suppress_filters): bắt câu SQL ở tầng $wpdb
add_filter( 'query', $bat_sql202 );
\Phongdao\GiaoTiep\Khoi\KhamPha::hien( 'ngau-nhien' );
remove_filter( 'query', $bat_sql202 );
kt( 'thô tục: Một câu ngẫu nhiên lọc bỏ bài bị che (và bài có cờ nghi)', (bool) array_filter( $sql202, static fn( string $s ): bool => str_contains( $s, '_phongdao_tho_tuc' ) && str_contains( $s, '_phongdao_co_nghi' ) ) );

// ---------- Chia sẻ link ----------
$mo_ta202 = \Phongdao\GiaoTiep\Seo\Module::mo_ta_tac_pham( $an202 );
kt( 'thô tục: mô tả gửi Google và ô xem trước khi chia sẻ trung tính, không trích lời', ! str_contains( $mo_ta202, '[UX-TEST]' ) && str_contains( $mo_ta202, 'có từ ngữ thô tục' ), $mo_ta202 );
kt( 'thô tục: ảnh xem trước khi chia sẻ link là ảnh chung của thư viện, không in lời', \Phongdao\DichVu\AnhChiaSe\Module::og( get_post( $an202 ) ) == \Phongdao\DichVu\AnhChiaSe\Module::og_chung() ); // phpcs:ignore Universal.Operators.StrictComparisons -- so hai mảng url/kích thước

// ---------- Tắt ----------
$tat202 = $gui202( array() );
kt( 'thô tục: bấm Tắt gỡ công tắc, trang đọc hiện lại như thường, Lịch sử thêm một dòng', $tat202['ok'] && null === PhanBai::lay( $an202, 'tho_tuc' ) && ! str_contains( TacPham::hien( DocBai::tac_pham( get_post( $an202 ) ) ), 'class="tho-tuc' ) && 2 === count( array_filter( Viec::lich_su( 'post', $an202, 20 ), static fn( array $r ): bool => 'tho_tuc' === $r['phan'] ) ), $tat202 );
wp_delete_post( $an202, true );
wp_delete_post( $sau202, true );
