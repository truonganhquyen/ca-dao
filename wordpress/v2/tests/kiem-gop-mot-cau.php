<?php
/** D-123: dữ liệu tổng hợp kỹ thuật; không phải tác phẩm dân gian. Chạy trong cổng test:v2. */

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\DichVu\NguoiDoc\DuyetTacPham as DG;
use Phongdao\DichVu\NguoiDoc\NhanGui as NG123;
use Phongdao\DichVu\NguoiDoc\SoTrung as ST123;
use Phongdao\DichVu\XepViec\QuyetViec as Q123;

wp_set_current_user( $ai_ql );
$gui123 = static function ( string $loi, array $them = array() ): array {
	static $lan = 120;
	$_SERVER['REMOTE_ADDR'] = '192.0.2.' . ++$lan; $_SERVER['HTTP_CF_CONNECTING_IP'] = '';
	$u = wp_generate_uuid4();
	return NG123::luu( $them + array( 'loai' => 'tac_pham', 'noi_dung' => $loi, 'dong_y' => 1, 'lan_gui' => $u . '.' . wp_hash( $u . '|gop' ) ) );
};
$the123 = static fn( int $id ): array => Viec::mo( 'post', $id, 'nguoi_doc' )[0] ?? array();
$quyet123 = static function ( int $gop, string $lam, int $bai = 0, array $them = array() ) use ( $ai_ql, $the123 ): array {
	$v = $the123( $gop );
	return Q123::quyet( $v['id'], $lam, $ai_ql, true, $v['phien'], '', null, $them + array( 'ban_gop' => DG::ban( $gop, $bai ), 'bai_dich' => $bai, 'gop_the_loai' => 'ca-dao' ) );
};
$loi123 = '[UX-TEST] amber birch cedar drift ember fern glade hill';
$u123 = wp_generate_uuid4(); $token123 = $u123 . '.' . wp_hash( $u123 . '|gop' );
$g123 = $gui123( $loi123, array( 'lan_gui' => $token123, 'ten' => 'Reader <test>', 'hien_ten' => 1, 'xuat_xu' => 'https://example.org/reader-test' ) );
$re123 = $gui123( $loi123, array( 'lan_gui' => $token123, 'ten' => 'Reader <test>', 'hien_ten' => 1, 'xuat_xu' => 'https://example.org/reader-test' ) );
kt( 'Góp câu: một bản chờ + một thẻ, gửi lại cùng lần không nhân đôi', $g123['ok'] && $re123['id'] === $g123['id'] && 'pending' === get_post_status( $g123['id'] ) && !! $the123( $g123['id'] ) );
kt( 'Góp câu: đang chờ không ra tra công khai, vẫn ra đối chiếu nội bộ', ! ST123::cong_khai( $loi123 ) && count( ST123::tim( $loi123, false ) ) > 0 );
$other123 = $gui123( $loi123 );
kt( 'Góp câu: người gửi khác cùng lời giữ riêng chứng cứ', $other123['ok'] && $other123['id'] !== $g123['id'] );
$v123 = $the123( $g123['id'] );
$r123 = $quyet123( $g123['id'], 'gop_dang', 0, array( 'gop_hien_ten' => 1, 'gop_nguon' => 'https://example.org/reader-test' ) );
$done123 = Viec::lay( $v123['id'] ); $b123 = (int) ( $done123['sau']['bai'] ?? 0 );
kt( 'Góp câu: người duyệt đăng thật, giữ nguyên lời/nguồn, đóng thẻ đúng bài', $r123['ok'] && $b123 > 0 && 'publish' === get_post_status( $b123 ) && 'xong' === $done123['trang_thai'] && array( $loi123 ) === ( PhanBai::lay( $b123, 'loi' )['dong'] ?? null ) && 'doc_gia_gop' === ( PhanBai::lay( $b123, 'loi' )['nguoi_lam'] ?? '' ), $r123 );
if ( $b123 ) {
	$public123 = ST123::cong_khai( $loi123 );
	kt( 'Góp câu: tra khách chỉ trả tên/link/khớp của bài đã đăng', 1 === count( $public123 ) && array( 'ten', 'url', 'khop' ) === array_keys( $public123[0] ) && $public123[0]['khop'] );
	$r_dup123 = $quyet123( $other123['id'], 'gop_dang' );
	kt( 'Góp câu: chặn tạo tác phẩm y hệt, giữ thẻ để bổ sung nguồn', ! $r_dup123['ok'] && !! $the123( $other123['id'] ) );
	$r_src123 = $quyet123( $other123['id'], 'gop_nguon', $b123, array( 'gop_nguon' => 'https://example.org/second-test' ) );
	kt( 'Góp câu: nguồn thứ hai không bị mất vì trùng lời', $r_src123['ok'] && 2 === count( PhanBai::lay( $b123, 'dong_gop' )['muc'] ?? array() ) );
	$var123 = str_replace( 'hill', 'island', $loi123 );
	$gvar123 = $gui123( $var123, array( 'ten' => 'Private test name', 'hien_ten' => 0 ) );
	$near123 = ST123::tim( $var123 );
	kt( 'Góp câu: trên 70% vẫn cho gửi để cân nhắc dị bản, không coi là xác suất trùng', $gvar123['ok'] && ( $near123[0]['diem'] ?? 0 ) > 70 && ! $near123[0]['khop'] );
	$rvar123 = $quyet123( $gvar123['id'], 'gop_di_ban', $b123, array( 'gop_hien_ten' => 1 ) );
	$credits123 = PhanBai::lay( $b123, 'dong_gop' )['muc'] ?? array();
	kt( 'Góp câu: thêm dị bản nguyên lời, không công khai tên thiếu đồng ý dù người duyệt chọn', $rvar123['ok'] && $var123 === implode( "\n", PhanBai::lay( $b123, 'di_ban' )['muc'][0]['dong'] ?? array() ) && '' === ( $credits123[2]['ten'] ?? null ), $rvar123 );
	$twice123 = $gui123( $var123 );
	kt( 'Góp câu: không thêm dị bản y hệt, vẫn nhận bổ sung nguồn', ! $quyet123( $twice123['id'], 'gop_di_ban', $b123 )['ok'] && $quyet123( $twice123['id'], 'gop_nguon', $b123 )['ok'] );
	$stale123 = $gui123( $var123 ); $vstale123 = $the123( $stale123['id'] ); $stamp123 = DG::ban( $stale123['id'], $b123 );
	LuuPhan::luu( $b123, 'y_nghia', array( 'chu' => '[UX-TEST] Updated note.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true ) );
	$stale_r123 = Q123::quyet( $vstale123['id'], 'gop_nguon', $ai_ql, true, $vstale123['phien'], '', null, array( 'ban_gop' => $stamp123, 'bai_dich' => $b123 ) );
	kt( 'Góp câu: bài đích đổi sau xem trước thì không ghi', ! $stale_r123['ok'] && !! $the123( $stale123['id'] ) );
	$truoc123 = PhanBai::lay( $b123, 'dong_gop' );
	$fail123 = static function ( int $id, string $phan ) use ( $b123 ): void { if ( $b123 === $id && 'dong_gop' === $phan ) { throw new RuntimeException( 'technical rollback fixture' ); } };
	add_action( 'phongdao_da_luu_phan', $fail123, 99, 2 );
	$rr123 = $quyet123( $stale123['id'], 'gop_nguon', $b123 );
	remove_action( 'phongdao_da_luu_phan', $fail123, 99 );
	kt( 'Góp câu: lỗi giữa lúc duyệt hoàn tác nguồn, trạng thái và thẻ; thử lại được', ! $rr123['ok'] && $truoc123 === PhanBai::lay( $b123, 'dong_gop' ) && 'pending' === get_post_status( $stale123['id'] ) && !! $the123( $stale123['id'] ) && $quyet123( $stale123['id'], 'gop_nguon', $b123 )['ok'], $rr123 );
	// Kiểm ở bộ chuyển dữ liệu thuần để không phụ thuộc hình dạng DocBai.
	$view123 = \Phongdao\DichVu\DungTrang\TrangTacPham::tu_phan( PhanBai::tat_ca( $b123 ), array( 'id' => $b123, 'tieu_de' => '[UX-TEST]', 'the_loai' => 'ca-dao', 'url' => get_permalink( $b123 ) ) );
	$html123 = \Phongdao\GiaoTiep\Khoi\TacPham::hien( $view123 );
	kt( 'Góp câu: ghi công đúng lời/dị bản, escape tên, không lộ tên riêng tư', str_contains( $html123, 'Reader &lt;test&gt;' ) && ! str_contains( $html123, 'Private test name' ) && str_contains( $html123, 'https://example.org/second-test' ) );
	wp_update_post( array( 'ID' => $b123, 'post_status' => 'draft' ) );
	kt( 'Góp câu: bài chuyển nháp biến mất khỏi tra khách', ! ST123::cong_khai( $loi123 ) );
	wp_update_post( array( 'ID' => $b123, 'post_status' => 'publish', 'post_password' => 'technical' ) );
	kt( 'Góp câu: bài có mật khẩu không rò tên/link qua tra khách', ! ST123::cong_khai( $loi123 ) );
	wp_update_post( array( 'ID' => $b123, 'post_password' => '' ) );
	update_post_meta( $b123, '_phongdao_tho_tuc', array( 'co' => true ) );
	kt( 'Góp câu: bài nhạy cảm không ra tra khách', ! ST123::cong_khai( $loi123 ) );
	wp_delete_post( $b123, true );
}
// Dọn toàn bộ fixture riêng của nhóm này; không đụng dữ liệu thực hay fixture trước.
foreach ( get_posts( array( 'post_type' => KhaiBao::GOP_Y, 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => '_cadao_loai', 'meta_value' => 'tac_pham' ) ) as $p123 ) {
	\Phongdao\DichVu\ChongTrung\ChongTrung::xoa( 'gop:' . $p123->ID ); wp_delete_post( $p123->ID, true );
}
