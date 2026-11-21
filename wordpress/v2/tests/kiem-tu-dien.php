<?php
/**
 * Hồi quy Từ điển và từ khó (D-125): sửa lời giải giữ nối mục từ, đổi chữ thì bỏ nối và báo; danh sách từ khó mất nối
 * và "Nối lại" do người bấm; tìm Từ điển xếp theo nhóm, gom chữ nhiều nghĩa, phân trang; trang mục từ dẫn sang nghĩa
 * khác và đường dẫn "Từ điển"; đường thể loại kiểu cũ về trang thể loại; khuôn trang bài viết. Chỉ dữ liệu kỹ thuật.
 */
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\DichVu\TuDien\NoiLai;
use Phongdao\GiaoTiep\BienTap\TamSua;
use Phongdao\GiaoTiep\Khoi\TuDien;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

wp_set_current_user( $ai_ql );
$nc_td  = array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
$muc_td = static function ( string $ten, string $slug, string $giai ): int {
	$t = term_exists( $slug, 'muc_tu' ) ?: wp_insert_term( $ten, 'muc_tu', array( 'slug' => $slug, 'description' => $giai ) );
	return (int) $t['term_id'];
};
$bai_td = static function ( string $ten, array $dong, array $muc ) use ( $nc_td ): int {
	$id = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => $ten, 'post_name' => sanitize_title( $ten ) ) );
	wp_set_object_terms( $id, 'tuc-ngu', KhaiBao::THE_LOAI );
	LuuPhan::luu( $id, 'loi', array( 'dong' => $dong, 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_td );
	if ( $muc ) {
		wp_set_object_terms( $id, $muc, 'muc_tu' );
	}
	return $id;
};
$sang   = $muc_td( 'Săng', 'sang-ux-test', '[UX-TEST] Quan tài.' );
$hsang  = $muc_td( 'Hàng săng', 'hang-sang-ux-test', '[UX-TEST] Hàng bán quan tài.' );
$sang2  = $muc_td( 'Sàng', 'sang-sang-ux-test', '[UX-TEST] Đồ đan bằng tre.' );
$ngua   = $muc_td( 'Ngựa thử Từ điển', 'ngua-thu-ux-test', '[UX-TEST] Con ngựa đi sang sông.' );
$mai    = array( $muc_td( 'Mai', 'mai-ux-test', '[UX-TEST] Cây hoa mai.' ), $muc_td( 'Mai', 'mai-ux-test-2', '[UX-TEST] Buổi sáng.' ), $muc_td( 'Mai', 'mai-ux-test-3', '[UX-TEST] Cây cùng loại với tre.' ) );
$b_td   = $bai_td( '[UX-TEST] Bài có từ săng', array( '[UX-TEST] Muốn chết thì lết vào săng' ), array( $sang, $hsang, $sang2, $ngua ) );
$b_mai  = $bai_td( '[UX-TEST] Bài có hai chữ mai', array( '[UX-TEST] Mai này mai nọ' ), $mai );

// 1. Sửa lời giải giữ nối; đổi chữ thì bỏ nối và báo
$dau_td  = LuuPhan::luu( $b_td, 'chu_thich', array( array( 'tu' => 'săng', 'loi_giai' => '[UX-TEST] Quan tài.', 'muc_tu_id' => $sang, 'vi_tri' => array( 1 ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nc_td );
$cu_td   = PhanBai::lay( $b_td, 'chu_thich' );
$sua_td  = TamSua::doc( 'chu_thich', array( 'muc' => array( array( 'goc' => '0', 'tu' => 'Săng', 'loi_giai' => '[UX-TEST] Quan tài, hòm đựng người chết.' ) ) ), $cu_td );
$muc_sua = $sua_td['gia_tri'][0] ?? array();
$doi_td  = TamSua::doc( 'chu_thich', array( 'muc' => array( array( 'goc' => '0', 'tu' => 'săng gỗ', 'loi_giai' => '[UX-TEST] Quan tài.' ) ) ), $cu_td );
kt(
	'Từ khó: sửa lời giải (chữ giữ nguyên, khác hoa thường) giữ nối mục từ và vị trí; đổi chữ thì bỏ nối và ghi lại để báo',
	$dau_td['ok'] && $sang === (int) ( $muc_sua['muc_tu_id'] ?? 0 ) && array( 1 ) === ( $muc_sua['vi_tri'] ?? null ) && '[UX-TEST] Quan tài, hòm đựng người chết.' === ( $muc_sua['loi_giai'] ?? '' ) && NguoiLam::NGUOI_VIET === ( $muc_sua['nguoi_lam'] ?? '' )
		&& empty( $doi_td['gia_tri'][0]['muc_tu_id'] ) && array( 'săng' ) === TamSua::$bo_noi,
	array( $dau_td, $muc_sua, $doi_td['gia_tri'] ?? null, TamSua::$bo_noi )
);
$luu_td = LuuPhan::luu( $b_td, 'chu_thich', $sua_td['gia_tri'], $nc_td );
kt( 'Từ khó: lưu bản sửa lời giải vẫn còn nối mục từ trong dữ liệu', $luu_td['ok'] && $sang === (int) ( ( PhanBai::lay( $b_td, 'chu_thich' )['muc'] ?? PhanBai::lay( $b_td, 'chu_thich' ) )[0]['muc_tu_id'] ?? 0 ), array( $luu_td, PhanBai::lay( $b_td, 'chu_thich' ) ) );

// 2. Từ khó mất nối (dữ liệu như lỗi cũ để lại): máy chỉ liệt kê; người bấm Nối lại mới ghi
LuuPhan::luu( $b_td, 'chu_thich', array( array( 'tu' => 'săng', 'loi_giai' => '[UX-TEST] Quan tài (mất nối).', 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nc_td );
LuuPhan::luu( $b_mai, 'chu_thich', array( array( 'tu' => 'mai', 'loi_giai' => '[UX-TEST] Không biết nghĩa nào.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nc_td );
$ds_td  = NoiLai::ds();
$co_td  = array_values( array_filter( $ds_td, static fn( array $d ): bool => $b_td === $d['bai'] ) );
$_GET   = array( 'xem' => 'can_sua' );
$h_nl   = \Phongdao\GiaoTiep\QuanTri\BoSung::hien();
$_GET   = array();
kt(
	'Từ khó mất nối: liệt kê đúng từ có một mục từ cùng chữ bài đang gắn (chữ có nhiều nghĩa thì không đoán); khối ở tab Cần sửa, đánh dấu sẵn, có nonce; máy chưa ghi gì',
	1 === count( $co_td ) && $sang === $co_td[0]['muc'] && 0 === $co_td[0]['i'] && ! array_filter( $ds_td, static fn( array $d ): bool => $b_mai === $d['bai'] )
		&& str_contains( $h_nl, '<h2 id="noi-lai">Từ khó mất nối Từ điển</h2>' ) && str_contains( $h_nl, 'value="' . $b_td . ':0" id="noi-lai-' . $b_td . '-0" checked' ) && str_contains( $h_nl, 'value="phongdao_bo_sung_noi_lai"' )
		&& empty( ( PhanBai::lay( $b_td, 'chu_thich' )['muc'] ?? PhanBai::lay( $b_td, 'chu_thich' ) )[0]['muc_tu_id'] ),
	array( $co_td, substr( (string) strstr( $h_nl, 'id="noi-lai"' ), 0, 900 ) )
);
$kq_td  = NoiLai::noi( array( $b_td . ':0', $b_mai . ':0', 'rac' ), $ai_ql );
$sau_td = PhanBai::lay( $b_td, 'chu_thich' );
$sau_td = $sau_td['muc'] ?? $sau_td;
kt(
	'Nối lại: chỉ ghi số mục từ cho đúng dòng hợp lệ (bỏ qua chữ nhiều nghĩa, dòng rác); lời giải giữ nguyên; danh sách hết dòng đó',
	1 === $kq_td['muc'] && 1 === $kq_td['bai'] && 0 === $kq_td['loi'] && $sang === (int) ( $sau_td[0]['muc_tu_id'] ?? 0 ) && '[UX-TEST] Quan tài (mất nối).' === ( $sau_td[0]['loi_giai'] ?? '' )
		&& ! array_filter( NoiLai::ds(), static fn( array $d ): bool => $b_td === $d['bai'] ),
	array( $kq_td, $sau_td )
);

// 3. Tìm Từ điển: nhóm, gom nghĩa, phân trang
$h_tim = TuDien::hien( 'săng', '' );
$vt    = static fn( string $ten ): int => (int) strpos( $h_tim, '>' . $ten . '</a>' );
kt(
	'Tìm Từ điển "săng": đúng chữ trước, rồi tên có chứa đúng chữ, rồi cùng chữ khi bỏ dấu, cuối cùng mục chỉ nhắc trong lời giải; có tên từng nhóm',
	$vt( 'Săng' ) > 0 && $vt( 'Săng' ) < $vt( 'Hàng săng' ) && $vt( 'Hàng săng' ) < $vt( 'Sàng' ) && $vt( 'Sàng' ) < $vt( 'Ngựa thử Từ điển' )
		&& str_contains( $h_tim, '>Đúng chữ</h3>' ) && str_contains( $h_tim, '>Có chứa chữ này</h3>' ) && str_contains( $h_tim, '>Cùng chữ khi bỏ dấu</h3>' ) && str_contains( $h_tim, '>Nhắc tới trong lời giải</h3>' ) && str_contains( $h_tim, 'list-row tu-dien-dung' ),
	substr( (string) strstr( $h_tim, 'id="tu-dien-kq"' ), 0, 2500 )
);
$h_mai = TuDien::hien( 'mai', '' );
kt(
	'Tìm Từ điển "mai": ba mục cùng chữ gom một dòng "3 nghĩa", mỗi nghĩa một đường dẫn; trang mục từ có lối sang hai nghĩa kia',
	str_contains( $h_mai, '3 nghĩa</span>' ) && 1 === substr_count( $h_mai, '>Mai</a> <span class="small muted">3 nghĩa' ) && str_contains( $h_mai, '[UX-TEST] Buổi sáng.' ) && str_contains( $h_mai, (string) get_term_link( $mai[2], 'muc_tu' ) )
		&& 2 === count( TuDien::cung_mat_chu( get_term( $mai[0], 'muc_tu' ) ) ) && array() === TuDien::cung_mat_chu( get_term( $sang, 'muc_tu' ) ),
	substr( (string) strstr( $h_mai, 'id="tu-dien-kq"' ), 0, 1500 )
);
$nhieu_td = array();
for ( $i = 1; $i <= 32; $i++ ) {
	$nhieu_td[] = $muc_td( 'Phân trang thử ' . $i, 'phan-trang-thu-ux-' . $i, '[UX-TEST] Mục phân trang.' );
}
wp_set_object_terms( $b_td, $nhieu_td, 'muc_tu', true );
$h_t1 = TuDien::hien( 'phân trang thử', '' );
$h_t2 = TuDien::hien( 'phân trang thử', '', 2 );
kt(
	'Tìm Từ điển: 30 dòng mỗi trang, tổng số đúng (không cắt ở 60), trang 2 có phần còn lại, đường dẫn trang giữ câu tìm',
	str_contains( $h_t1, '32 mục từ · trang 1/2' ) && 30 === substr_count( $h_t1, '<div class="list-row' ) && 2 === substr_count( $h_t2, '<div class="list-row' ) && str_contains( $h_t1, 'trang=2' ) && str_contains( $h_t1, 'q=ph' ),
	array( substr( (string) strstr( $h_t1, 'id="tu-dien-kq"' ), 0, 400 ), substr_count( $h_t2, '<div class="list-row' ) )
);

// 4. Trang mục từ: đường dẫn Từ điển, nghĩa khác
$q_mt = new WP_Query( array( 'muc_tu' => 'mai-ux-test' ) );
$h_mt = \Phongdao\GiaoTiep\Khoi\DanhSach::hien( $q_mt );
kt(
	'Trang mục từ: đường dẫn "Trang chủ / Từ điển / Mai", nhãn Từ điển, lối sang hai nghĩa khác của chữ "Mai"',
	str_contains( $h_mt, '>Từ điển</a><span aria-hidden="true">/</span><span>Mai</span>' ) && ! str_contains( $h_mt, 'Kho tác phẩm' ) && str_contains( $h_mt, '<p class="eyebrow">Từ điển</p>' ) && str_contains( $h_mt, 'còn 2 nghĩa khác trong Từ điển' ) && str_contains( $h_mt, '[UX-TEST] Cây cùng loại với tre.' ),
	substr( $h_mt, 0, 1500 )
);

// 5. Đường thể loại kiểu cũ; khuôn trang bài viết
kt(
	'Đường cũ /ca-dao/, /ve/, /cau-do/, /thanh-ngu/ về trang thể loại (không để WordPress đoán sang bài khác); đường lạ không đụng',
	(string) get_term_link( 'ca-dao', KhaiBao::THE_LOAI ) === \Phongdao\HaTang\ChuyenHuong::dich( '/ca-dao/' ) && (string) get_term_link( 've', KhaiBao::THE_LOAI ) === \Phongdao\HaTang\ChuyenHuong::dich( '/ve' )
		&& (string) get_term_link( 'cau-do', KhaiBao::THE_LOAI ) === \Phongdao\HaTang\ChuyenHuong::dich( '/cau-do/' ) && (string) get_term_link( 'tuc-ngu', KhaiBao::THE_LOAI ) === \Phongdao\HaTang\ChuyenHuong::dich( '/thanh-ngu/' ) && null === \Phongdao\HaTang\ChuyenHuong::dich( '/khong-co-the-loai-nay/' )
);
$khuon_td = (string) file_get_contents( get_stylesheet_directory() . '/templates/single.html' );
kt( 'Bài viết có khuôn trang riêng: đầu trang, chân trang của site, khung đọc', str_contains( $khuon_td, '"slug":"dau-trang"' ) && str_contains( $khuon_td, '"slug":"chan-trang"' ) && str_contains( $khuon_td, 'page reading bai-viet' ) && str_contains( $khuon_td, 'wp:post-content' ) );

// Từ điển nối bài (D-126): dùng lại các mục từ ở trên trước khi dọn
require '/repo/tests/kiem-tu-dien-noi.php';

foreach ( array( $b_td, $b_mai ) as $x_td ) {
	wp_delete_post( $x_td, true );
}
foreach ( array_merge( array( $sang, $hsang, $sang2, $ngua ), $mai, $nhieu_td ) as $x_td ) {
	wp_delete_term( $x_td, 'muc_tu' );
}
wp_set_current_user( 0 );
