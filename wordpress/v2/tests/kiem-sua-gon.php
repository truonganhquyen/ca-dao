<?php
/**
 * Hồi quy T-216 (D-119, docs/41 mục 2 điều 9): màn Sửa tác phẩm gọn (Lời → Nghĩa → Phân loại, phần ít dùng gom vào "Phần
 * khác"), tục ngữ nhập Ý nghĩa và Nghĩa đen (Nghĩa bóng, Dùng khi chỉ ở bài cũ đã có), gộp Nghĩa bóng cũ vào Ý nghĩa do
 * người có quyền duyệt bấm, nhắc lỗi trình bày khi lưu (không chặn). Chỉ dữ liệu kỹ thuật [UX-TEST].
 */
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\GiaoTiep\BienTap\Module as BienTap216;
use Phongdao\GiaoTiep\QuanTri\SuaTacPham as Sua216;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

wp_set_current_user( $ai_ql );
$nc216  = array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
$tao216 = static function ( string $ten, string $the_loai, array $dong ) use ( $nc216 ): int {
	$id = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => $ten, 'post_name' => sanitize_title( $ten ) ) );
	wp_set_object_terms( $id, $the_loai, KhaiBao::THE_LOAI );
	LuuPhan::luu( $id, 'loi', array( 'dong' => $dong, 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc216 );
	return $id;
};
$man216 = static function ( int $id ): string {
	$_GET = array( 'id' => (string) $id );
	$h    = Sua216::hien();
	$_GET = array();
	return $h;
};

// ---------- Bố cục gọn ----------
$tn216 = $tao216( '[UX-TEST] Tục ngữ sửa gọn', 'tuc-ngu', array( '[UX-TEST] Ăn cây nào rào cây ấy thử' ) );
$h216  = $man216( $tn216 );
$vt216 = static fn( string $kim ): int => (int) strpos( $h216, $kim );
kt(
	'Sửa tác phẩm gọn: cột chính theo thứ tự Lời → Nghĩa (Ý nghĩa mở sẵn) → Phân loại → "Phần khác" (mỗi phần một hàng); cột bên khung Đăng, còn thiếu, công tắc, đường dẫn, lịch sử',
	$vt216( 'id="loi-doi-chieu"' ) < $vt216( '<h2 class="phongdao-sua-nhom" id="pq-nghia">Nghĩa</h2>' ) && $vt216( '<h2 class="phongdao-sua-nhom" id="pq-nghia">Nghĩa</h2>' ) < $vt216( 'id="phan-y_nghia"' )
		&& $vt216( 'id="phan-y_nghia"' ) < $vt216( 'id="phan-loai"' ) && $vt216( 'id="phan-loai"' ) < $vt216( 'id="phan-khac"' ) && $vt216( 'id="phan-khac"' ) < $vt216( '<aside class="pq-cot-phai phongdao-sua-ben"' )
		&& (bool) preg_match( '/<details class="phongdao-sua-phan chua-co" id="phan-y_nghia"[^>]* open>/', $h216 ) && (bool) preg_match( '/<section class="pq-khoi phongdao-phan-khac" id="phan-khac"[^>]*><h2 id="pq-phan-khac">Phần khác <span class="pq-dem">\d+<\/span><\/h2>/', $h216 )
		&& $vt216( 'id="phan-khac"' ) < $vt216( 'id="phan-chu_thich"' ) && $vt216( 'id="phan-khac"' ) < $vt216( 'id="phan-gan_nghia"' ) && $vt216( '<aside class="pq-cot-phai phongdao-sua-ben"' ) < $vt216( 'id="lich-su"' ),
	substr( (string) strstr( $h216, 'phongdao-sua-bo' ), 0, 1500 )
);
kt(
	'tục ngữ: nhập Ý nghĩa và Nghĩa đen (ô "Thêm nghĩa đen" nằm trong nhóm Nghĩa, luôn thấy); không có ô Nghĩa bóng, Dùng khi khi bài chưa có (D-119)',
	str_contains( (string) strstr( $h216, '<div class="phongdao-nghia">' ), 'id="phan-nghia_den"' ) && ! str_contains( $h216, 'id="phan-nghia_bong"' ) && ! str_contains( $h216, 'id="phan-dung_khi"' ) && $vt216( 'id="phan-nghia_den"' ) < $vt216( 'id="phan-loai"' )
);

// ---------- Nhắc lỗi trình bày khi lưu: không chặn ----------
$gui216 = static function ( int $id, string $phan, array $than ): array {
	$r = new WP_REST_Request( 'POST' );
	$r->set_body_params( $than + array( 'phien_phan' => LuuPhan::phien( $id, $phan, PhanBai::lay( $id, $phan ) ) ) );
	$r->set_param( 'id', $id );
	$r->set_param( 'phan', $phan );
	return BienTap216::luu_phan( $r );
};
$kq_nhac216 = $gui216( $tn216, 'y_nghia', array( 'chu' => 'Câu tục ngữ nói về lòng biết ơn', 'duyet' => '1' ) );
kt(
	'nhắc lỗi trình bày: Ý nghĩa mở đầu nhắc lại câu, thiếu dấu chấm vẫn lưu được; lời báo ghi "Nhắc (không chặn lưu)"; màn sửa hiện lời nhắc dưới phần Ý nghĩa',
	$kq_nhac216['ok'] && 'Câu tục ngữ nói về lòng biết ơn' === ( PhanBai::lay( $tn216, 'y_nghia' )['chu'] ?? '' )
		&& str_contains( (string) $kq_nhac216['thong_bao'], 'Nhắc (không chặn lưu): Mở đầu nhắc lại câu: nên bắt đầu bằng nghĩa. Thiếu dấu chấm cuối.' )
		&& str_contains( (string) strstr( $man216( $tn216 ), 'id="phan-y_nghia"' ), '<div class="notice notice-warning inline phongdao-nhac"><p><strong>Nhắc (không chặn lưu):</strong> Mở đầu nhắc lại câu' ),
	$kq_nhac216
);
$kq_dung216 = $gui216( $tn216, 'y_nghia', array( 'chu' => 'Nhận lợi ích từ đâu thì giữ gìn, bảo vệ nơi đó.', 'duyet' => '1' ) );
kt( 'nhắc lỗi trình bày: Ý nghĩa đúng quy tắc thì lời báo không có phần nhắc', $kq_dung216['ok'] && ! str_contains( (string) $kq_dung216['thong_bao'], 'Nhắc' ), $kq_dung216 );
$cd216 = $tao216( '[UX-TEST] Ca dao lời viết hoa', 'ca-dao', array( '[UX-TEST] Ai Làm Anh Phải Xa Em', 'Dòng hai , có dấu cách' ) );
kt( 'nhắc lỗi trình bày: lời viết hoa giữa câu, dấu cách trước dấu câu được nhắc dưới khối Lời (máy không sửa lời)', str_contains( (string) strstr( $man216( $cd216 ), 'id="loi-doi-chieu"' ), 'Dòng 1 viết hoa giữa câu.' ) && 'Dòng hai , có dấu cách' === ( PhanBai::lay( $cd216, 'loi' )['dong'][1] ?? '' ) );

// ---------- Gộp Nghĩa bóng cũ vào Ý nghĩa ----------
$nb216 = $tao216( '[UX-TEST] Tục ngữ có nghĩa bóng cũ', 'tuc-ngu', array( '[UX-TEST] Uống nước nhớ nguồn thử' ) );
LuuPhan::luu( $nb216, 'nghia_bong', array( 'chu' => 'Nhận ơn thì nhớ người làm ra.', 'ai' => true, 'nguoi_lam' => NguoiLam::AI_DA_DUYET ), $nc216 );
$may216 = $tao216( '[UX-TEST] Tục ngữ nghĩa bóng máy chép', 'tuc-ngu', array( '[UX-TEST] Câu máy chép thử' ) );
LuuPhan::luu( $may216, 'nghia_bong', array( 'chu' => 'Nghĩa chép từ nguồn.', 'nguoi_lam' => NguoiLam::MAY_CHEP ), array( 'nguoi' => 0, 'goi' => KiemPhan::GOI_MAY ) );
$h_nb216  = $man216( $nb216 );
$h_may216 = $man216( $may216 );
kt(
	'tục ngữ có Nghĩa bóng cũ (người viết / AI đã duyệt), chưa có Ý nghĩa: ô Nghĩa bóng vẫn hiện, có lời mời "Gộp vào Ý nghĩa"; Nghĩa bóng máy chép không có nút gộp',
	str_contains( $h_nb216, 'id="phan-nghia_bong"' ) && str_contains( $h_nb216, 'Bài này còn "Nghĩa bóng" cũ: «Nhận ơn thì nhớ người làm ra.»' ) && str_contains( $h_nb216, 'value="phongdao_gop_nghia_bong"' )
		&& str_contains( $h_may216, 'id="phan-nghia_bong"' ) && ! str_contains( $h_may216, 'phongdao_gop_nghia_bong' ),
	substr( (string) strstr( $h_nb216, 'id="phan-y_nghia"' ), 0, 1200 )
);
$soan216 = (int) wp_insert_user( array( 'user_login' => 'ux_test_soan_216', 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ) );
( new WP_User( $soan216 ) )->add_cap( 'cadao_soan' );
$tu_choi216 = Sua216::gop_nghia_bong_bai( $nb216, $soan216 );
$may_gop216 = Sua216::gop_nghia_bong_bai( $may216, $ai_ql );
$ls_truoc216 = count( Viec::lich_su( 'post', $nb216, 30 ) );
$gop216 = Sua216::gop_nghia_bong_bai( $nb216, $ai_ql );
$yn216  = PhanBai::lay( $nb216, 'y_nghia' );
kt(
	'Gộp vào Ý nghĩa: chỉ người có quyền duyệt; Nghĩa bóng máy chép không gộp; gộp thì Ý nghĩa nhận đúng chữ, người làm, dấu AI; Nghĩa bóng gỡ; Lịch sử ghi hai phần',
	! $tu_choi216['ok'] && ! $may_gop216['ok'] && null === PhanBai::lay( $may216, 'y_nghia' ) && $gop216['ok']
		&& 'Nhận ơn thì nhớ người làm ra.' === ( $yn216['chu'] ?? '' ) && ! empty( $yn216['ai'] ) && NguoiLam::AI_DA_DUYET === ( $yn216['nguoi_lam'] ?? '' ) && null === PhanBai::lay( $nb216, 'nghia_bong' )
		&& $ls_truoc216 + 2 === count( Viec::lich_su( 'post', $nb216, 30 ) ),
	array( $tu_choi216, $may_gop216, $gop216, $yn216 )
);
$lai216 = Sua216::gop_nghia_bong_bai( $nb216, $ai_ql );
kt( 'Gộp vào Ý nghĩa: bài đã có Ý nghĩa thì không gộp lần nữa, báo tải lại', ! $lai216['ok'] && str_contains( $lai216['loi'], 'đã có Ý nghĩa' ) && ! str_contains( $man216( $nb216 ), 'id="phan-nghia_bong"' ), $lai216 );

foreach ( array( $tn216, $cd216, $nb216, $may216 ) as $x216 ) {
	wp_delete_post( $x216, true );
}
if ( $soan216 ) {
	require_once ABSPATH . 'wp-admin/includes/user.php';
	wp_delete_user( $soan216 );
}
wp_set_current_user( 0 );
