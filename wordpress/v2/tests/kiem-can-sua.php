<?php
/**
 * Hồi quy Bổ sung → "Cần sửa" (D-119, T-216): máy rà nội dung đã đăng theo quy tắc viết, xếp theo loại lỗi; chỉ đánh
 * dấu, không sửa chữ; "Bỏ qua" theo đúng bản chữ lúc bấm, chữ đổi thì hiện lại. Chỉ dữ liệu kỹ thuật [UX-TEST].
 */
use Phongdao\DichVu\CanSua\CanSua;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

wp_set_current_user( $ai_ql );
$nc_cs  = array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
$tao_cs = static function ( string $ten, string $the_loai, array $dong, string $tt = 'publish' ) use ( $nc_cs ): int {
	$id = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => $tt, 'post_title' => $ten, 'post_name' => sanitize_title( $ten ) ) );
	wp_set_object_terms( $id, $the_loai, KhaiBao::THE_LOAI );
	LuuPhan::luu( $id, 'loi', array( 'dong' => $dong, 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_cs );
	return $id;
};
$a_cs = $tao_cs( '[UX-TEST] Cần sửa A', 'ca-dao', array( '[UX-TEST] Dòng một , có dấu cách', 'Dòng hai bình thường' ) );
LuuPhan::luu( $a_cs, 'y_nghia', array( 'chu' => 'Câu ca dao nói về nỗi nhớ quê', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_cs );
LuuPhan::luu( $a_cs, 'chu_thich', array( array( 'tu' => 'nỗi', 'loi_giai' => trim( str_repeat( 'chữ ', 85 ) ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nc_cs );
$cd_cs = $tao_cs( '[UX-TEST] Câu đố cần sửa', 'cau-do', array( '[UX-TEST] Cái gì thử đố' ) );
LuuPhan::luu( $cd_cs, 'y_nghia', array( 'chu' => trim( str_repeat( 'chữ ', 70 ) ) . '.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_cs );
$nhap_cs = $tao_cs( '[UX-TEST] Bài nháp cần sửa', 'ca-dao', array( '[UX-TEST] Nháp , lỗi' ), 'draft' );
foreach ( array( 'toan-quoc' => 'Phổ biến toàn quốc', 'bac-bo' => 'Bắc Bộ' ) as $sl => $ten ) {
	if ( ! term_exists( $sl, 'vung' ) ) {
		wp_insert_term( $ten, 'vung', array( 'slug' => $sl ) );
	}
}
wp_set_object_terms( $a_cs, array( 'toan-quoc', 'bac-bo' ), 'vung' );
$ds_cs = CanSua::ds();
kt(
	'Cần sửa: bài đã đăng được xếp đúng loại (Ý nghĩa mở đầu nhắc lại câu, thiếu dấu câu cuối; từ khó quá 80 chữ; lời có dấu cách trước dấu câu; vùng vừa toàn quốc vừa cụ thể); câu đố không bị xếp "quá 60 chữ"; bài nháp không có',
	in_array( $a_cs, $ds_cs['nhac_cau'], true ) && in_array( $a_cs, $ds_cs['thieu_dau'], true ) && in_array( $a_cs, $ds_cs['ct_dai'], true ) && in_array( $a_cs, $ds_cs['cach_dau'], true ) && in_array( $a_cs, $ds_cs['vung'], true )
		&& ! in_array( $cd_cs, $ds_cs['qua_dai'], true ) && ! in_array( $nhap_cs, array_merge( ...array_values( $ds_cs ) ), true ),
	array_map( 'count', $ds_cs )
);
$yn_truoc_cs = PhanBai::lay( $a_cs, 'y_nghia' );
$bo_cs       = CanSua::bo_qua( $a_cs, 'thieu_dau' );
$ds_bo_cs    = CanSua::ds();
kt(
	'Cần sửa: "Bỏ qua" ẩn đúng một lỗi của bài (lỗi khác vẫn còn), không đổi nội dung bài',
	$bo_cs && ! in_array( $a_cs, $ds_bo_cs['thieu_dau'], true ) && in_array( $a_cs, $ds_bo_cs['nhac_cau'], true ) && $yn_truoc_cs === PhanBai::lay( $a_cs, 'y_nghia' ) && ! CanSua::bo_qua( $a_cs, 'ma_la' )
);
LuuPhan::luu( $a_cs, 'y_nghia', array( 'chu' => 'Câu ca dao nói về nỗi nhớ quê nhà', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_cs );
kt( 'Cần sửa: lưu phần làm mới danh sách; chữ đã đổi thì lỗi đã bỏ qua hiện lại', in_array( $a_cs, CanSua::ds()['thieu_dau'], true ) );
LuuPhan::luu( $a_cs, 'y_nghia', array( 'chu' => 'Nỗi nhớ quê của người đi xa.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc_cs );
$ds_sua_cs = CanSua::ds();
kt( 'Cần sửa: Ý nghĩa đã sửa đúng quy tắc thì rời các nhóm Ý nghĩa', ! in_array( $a_cs, $ds_sua_cs['thieu_dau'], true ) && ! in_array( $a_cs, $ds_sua_cs['nhac_cau'], true ) && in_array( $a_cs, $ds_sua_cs['cach_dau'], true ) );
$_GET  = array();
$h_cs  = \Phongdao\GiaoTiep\QuanTri\BoSung::hien();
kt(
	'Bổ sung: khối "Cần sửa" có nhóm theo loại kèm số bài, tên bài dẫn tới đúng phần ở màn sửa, đoạn trích, nút "Bỏ qua" có nonce; bảng ưu tiên có dòng Cần sửa',
	str_contains( $h_cs, '<h2 id="can-sua">Cần sửa</h2>' ) && str_contains( $h_cs, 'id="can-sua-cach_dau"' ) && str_contains( $h_cs, 'Lời có dấu cách trước dấu câu · ' )
		&& str_contains( $h_cs, '[UX-TEST] Dòng một , có dấu cách' ) && str_contains( $h_cs, 'value="phongdao_bo_sung_bo_qua"' ) && str_contains( $h_cs, 'name="_wpnonce"' ) && str_contains( $h_cs, 'Cần sửa trình bày (máy rà theo quy tắc viết)' )
		&& str_contains( $h_cs, 'phan_loai=vung' ),
	substr( (string) strstr( $h_cs, 'id="can-sua"' ), 0, 1500 )
);
foreach ( array( $a_cs, $cd_cs, $nhap_cs ) as $x_cs ) {
	wp_delete_post( $x_cs, true );
}
delete_transient( CanSua::TAM );
wp_set_current_user( 0 );
