<?php
/**
 * Hồi quy T-215 (D-119): phần Chuyện quanh bài (tục ngữ: Nguồn gốc) — người đánh dấu chuyển mục từ khó sang, giữ người làm
 * và nguồn, cùng một lượt lưu, máy chỉ gợi ý; Câu gần nghĩa · trái nghĩa — dán đường dẫn / số bài, "Ghi cả ở câu kia",
 * trang đọc chỉ hiện câu đã đăng; chế độ sửa và bài có từ ngữ thô tục dùng chung bố cục gọn. Chỉ dữ liệu kỹ thuật [UX-TEST].
 */
use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\GiaoTiep\BienTap\CheDoSua;
use Phongdao\GiaoTiep\BienTap\Module as BienTap215;
use Phongdao\GiaoTiep\BienTap\TamSua;
use Phongdao\GiaoTiep\Khoi\TacPham;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\PhanRieng;

wp_set_current_user( $ai_ql );
$nc215  = array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
$tao215 = static function ( string $ten, string $the_loai, array $dong, string $trang_thai = 'publish' ) use ( $nc215 ): int {
	$id = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => $trang_thai, 'post_title' => $ten, 'post_name' => sanitize_title( $ten ) ) );
	wp_set_object_terms( $id, $the_loai, KhaiBao::THE_LOAI );
	LuuPhan::luu( $id, 'loi', array( 'dong' => $dong, 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc215 );
	return $id;
};
$gui215 = static function ( int $id, string $phan, array $than ): array {
	$r = new WP_REST_Request( 'POST' );
	$r->set_body_params( $than + array( 'phien_phan' => LuuPhan::phien( $id, $phan, PhanBai::lay( $id, $phan ) ) ) );
	$r->set_param( 'id', $id );
	$r->set_param( 'phan', $phan );
	return BienTap215::luu_phan( $r );
};

// ---------- Phần mới: kiểm lằn ranh ----------
$cq_bai = $tao215( '[UX-TEST] Ca dao có chuyện quanh bài', 'ca-dao', array( '[UX-TEST] Dòng một có chữ gạo trắng', '[UX-TEST] Dòng hai nước trong' ) );
$may215 = LuuPhan::luu( $cq_bai, 'chuyen_quanh', array( array( 'chu' => 'Chuyện máy tự ghi.', 'nguoi_lam' => NguoiLam::MAY_CHEP ) ), array( 'nguoi' => 0, 'goi' => KiemPhan::GOI_MAY ) );
$ai215  = LuuPhan::luu( $cq_bai, 'chuyen_quanh', array( array( 'chu' => 'Chuyện AI.', 'nguoi_lam' => NguoiLam::AI_DA_DUYET ) ), $nc215 );
kt( 'Chuyện quanh bài: máy không được ghi; không nhận người làm là AI', ! $may215['ok'] && ! $ai215['ok'] && null === PhanBai::lay( $cq_bai, 'chuyen_quanh' ), array( $may215, $ai215 ) );

// ---------- Chuyển mục từ khó sang Chuyện quanh bài: người đánh dấu, giữ người làm và nguồn, một lượt lưu ----------
$hs215 = (int) wp_insert_post( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'post_title' => '[UX-TEST] Hồ sơ nguồn chuyện' ) );
LuuPhan::luu(
	$cq_bai,
	'chu_thich',
	array(
		array( 'tu' => 'gạo trắng', 'loi_giai' => 'Từ khó ở lại.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ),
		array( 'loi_giai' => 'Giai thoại máy chép về bài.', 'nguoi_lam' => NguoiLam::MAY_CHEP, 'nguon' => array( 'ho_so' => $hs215 ) ),
		array( 'tu' => 'nước trong', 'loi_giai' => 'Điển tích người viết.', 'nguoi_lam' => NguoiLam::NGUOI_VIET, 'nguon' => array( 'ho_so' => $hs215, 'trang' => '12' ) ),
	),
	$nc215
);
$ctx215 = CheDoSua::ngu_canh( get_post( $cq_bai ) );
$form215 = TamSua::than( 'chu_thich', PhanBai::lay( $cq_bai, 'chu_thich' ), $ctx215 );
kt(
	'Sửa từ khó: mỗi mục đã lưu có ô "Chuyển sang Chuyện quanh bài khi lưu"; mục chung có dòng "Máy gợi ý", mục gắn từ không có; mục mới (mẫu) không có ô chuyển',
	3 === substr_count( $form215, 'name="muc[0][chuyen]"' ) + substr_count( $form215, 'name="muc[1][chuyen]"' ) + substr_count( $form215, 'name="muc[2][chuyen]"' )
		&& 1 === substr_count( $form215, 'Máy gợi ý: mục chung, không gắn từ nào trong lời. Chuyển sang Chuyện quanh bài nếu đúng.' )
		&& ! str_contains( $form215, 'name="muc[__i__][chuyen]"' ),
	substr( $form215, 0, 1500 )
);
$ls_truoc215 = count( Viec::lich_su( 'post', $cq_bai, 50 ) );
$kq_chuyen215 = $gui215(
	$cq_bai,
	'chu_thich',
	array(
		'muc' => array(
			array( 'goc' => '0', 'tu' => 'gạo trắng', 'loi_giai' => 'Từ khó ở lại.' ),
			array( 'goc' => '1', 'tu' => '', 'loi_giai' => 'Giai thoại máy chép về bài.', 'nguon' => array( 'ho_so' => (string) $hs215 ), 'chuyen' => '1' ),
			array( 'goc' => '2', 'tu' => 'nước trong', 'loi_giai' => 'Điển tích người viết.', 'nguon' => array( 'ho_so' => (string) $hs215, 'trang' => '12' ), 'chuyen' => '1' ),
		),
	)
);
$ct_sau215 = PhanBai::lay( $cq_bai, 'chu_thich' )['muc'] ?? array();
$cq_sau215 = PhanBai::lay( $cq_bai, 'chuyen_quanh' )['muc'] ?? array();
kt(
	'Chuyển sang Chuyện quanh bài: hai mục đánh dấu rời Từ khó, nối vào Chuyện quanh bài cùng lượt lưu; mục máy chép giữ người làm "máy chép" và hồ sơ nguồn, mục người viết giữ nguồn và trang; từ thành tên chuyện; Lịch sử ghi cả hai phần; lời báo nói đã chuyển',
	$kq_chuyen215['ok'] && 1 === count( $ct_sau215 ) && 'gạo trắng' === $ct_sau215[0]['tu'] && 2 === count( $cq_sau215 )
		&& NguoiLam::MAY_CHEP === $cq_sau215[0]['nguoi_lam'] && $hs215 === (int) ( $cq_sau215[0]['nguon']['ho_so'] ?? 0 ) && 'Giai thoại máy chép về bài.' === $cq_sau215[0]['chu'] && ! isset( $cq_sau215[0]['tieu_de'] )
		&& NguoiLam::NGUOI_VIET === $cq_sau215[1]['nguoi_lam'] && 'nước trong' === $cq_sau215[1]['tieu_de'] && '12' === (string) ( $cq_sau215[1]['nguon']['trang'] ?? '' )
		&& $ls_truoc215 + 2 === count( Viec::lich_su( 'post', $cq_bai, 50 ) ) && str_contains( (string) $kq_chuyen215['thong_bao'], 'Đã chuyển 2 mục sang Chuyện quanh bài.' ),
	array( $kq_chuyen215, $ct_sau215, $cq_sau215 )
);
// Phần Chuyện quanh bài hỏng giữa chừng: cả từ khó không đổi
LuuPhan::luu( $cq_bai, 'chu_thich', array( array( 'tu' => 'gạo trắng', 'loi_giai' => 'Từ khó ở lại.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), array( 'loi_giai' => 'Mục sẽ hỏng khi chuyển.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nc215 );
$cq_dai215 = array_fill( 0, 1, array( 'chu' => str_repeat( 'x', 4001 ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ) );
update_post_meta( $cq_bai, '_phongdao_chuyen_quanh', array( 'muc' => array_merge( $cq_sau215, $cq_dai215 ) ) );
clean_post_cache( $cq_bai );
$ct_truoc_hong215 = PhanBai::lay( $cq_bai, 'chu_thich' );
$kq_hong215 = $gui215( $cq_bai, 'chu_thich', array( 'muc' => array( array( 'goc' => '0', 'tu' => 'gạo trắng', 'loi_giai' => 'Từ khó ở lại.' ), array( 'goc' => '1', 'tu' => '', 'loi_giai' => 'Mục sẽ hỏng khi chuyển.', 'chuyen' => '1' ) ) ) );
kt(
	'Chuyển sang Chuyện quanh bài: phần Chuyện quanh bài không lưu được thì từ khó cũng không đổi (một lượt lưu), có lời báo',
	! $kq_hong215['ok'] && $ct_truoc_hong215 === PhanBai::lay( $cq_bai, 'chu_thich' ) && str_contains( implode( ' ', $kq_hong215['loi'] ), 'Chưa chuyển được mục sang Chuyện quanh bài' ),
	$kq_hong215
);
update_post_meta( $cq_bai, '_phongdao_chuyen_quanh', array( 'muc' => $cq_sau215 ) );
LuuPhan::luu( $cq_bai, 'chu_thich', array( array( 'tu' => 'gạo trắng', 'loi_giai' => 'Từ khó ở lại.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nc215 );
clean_post_cache( $cq_bai );

// ---------- Trang đọc: hàng Chuyện quanh bài, bảng nguồn, phần riêng, Tìm ----------
wp_set_current_user( 0 );
$h_cq215 = TacPham::hien( DocBai::tac_pham( get_post( $cq_bai ) ) );
$hang_cq215 = (string) strstr( (string) strstr( $h_cq215, '<details class="hang-mo" id="chuyen-quanh-bai"' ), '</details>', true );
kt(
	'trang đọc: hàng "Chuyện quanh bài · 2" có mục máy chép (không in nguồn — D-068) và mục người viết có tên chuyện in đậm, dòng Nguồn của người viết; bảng nguồn có dòng Chuyện quanh bài',
	'' !== $hang_cq215 && str_contains( $hang_cq215, 'Chuyện quanh bài<span class="hang-so"> · 2</span>' ) && str_contains( $hang_cq215, 'Giai thoại máy chép về bài.' )
		&& str_contains( $hang_cq215, '<strong>nước trong.</strong> Điển tích người viết.' ) && 1 === substr_count( $hang_cq215, 'Nguồn: ' ) && str_contains( $hang_cq215, '[UX-TEST] Hồ sơ nguồn chuyện, tr. 12' )
		&& str_contains( $h_cq215, '<dt>Chuyện quanh bài</dt><dd>1 mục chép từ nguồn tham khảo' ) && ! str_contains( $h_cq215, 'data-chi-sua' ),
	$hang_cq215
);
kt( 'Chuyện quanh bài người viết là phần riêng (D-117), như chú thích người viết', in_array( 'chuyen_quanh', PhanRieng::cac( PhanBai::tat_ca( $cq_bai, KhaiBao::TAC_PHAM ) ), true ) );
$trich215 = \Phongdao\MienDuLieu\TrichTim::trich( PhanBai::tat_ca( $cq_bai, KhaiBao::TAC_PHAM ), '[UX-TEST] Ca dao có chuyện quanh bài', 'Điển tích người viết' );
kt( 'Tìm: đoạn trích lấy được chữ trong Chuyện quanh bài', 'Khớp trong chuyện quanh bài' === ( $trich215['nhan'] ?? '' ), $trich215 );

// ---------- Câu gần nghĩa · trái nghĩa ----------
wp_set_current_user( $ai_ql );
$tn_a = $tao215( '[UX-TEST] Tục ngữ gần nghĩa A', 'tuc-ngu', array( '[UX-TEST] Câu A một vế', '[UX-TEST] Câu A vế hai' ) );
$tn_b = $tao215( '[UX-TEST] Tục ngữ gần nghĩa B', 'tuc-ngu', array( '[UX-TEST] Câu B đủ một dòng' ) );
$tn_c = $tao215( '[UX-TEST] Tục ngữ trái nghĩa C', 'tuc-ngu', array( '[UX-TEST] Câu C đủ một dòng' ) );
$tn_nhap = $tao215( '[UX-TEST] Tục ngữ chưa đăng D', 'tuc-ngu', array( '[UX-TEST] Câu D chưa đăng' ), 'draft' );
$tn_tho  = $tao215( '[UX-TEST] Tục ngữ thô tục E', 'tuc-ngu', array( '[UX-TEST] Câu E cần che' ) );
LuuPhan::luu( $tn_tho, 'tho_tuc', array( 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc215 );
$cd215 = $tao215( '[UX-TEST] Ca dao không có câu gần nghĩa', 'ca-dao', array( '[UX-TEST] Dòng ca dao một', '[UX-TEST] Dòng ca dao hai' ) );
$ca_dao_gn215 = LuuPhan::luu( $cd215, 'gan_nghia', array( array( 'tac_pham_id' => $tn_a, 'quan_he' => 'gan' ) ), $nc215 );
kt( 'Câu gần nghĩa chỉ dùng cho tục ngữ', ! $ca_dao_gn215['ok'], $ca_dao_gn215 );
$khong215 = $gui215( $tn_a, 'gan_nghia', array( 'muc' => array( array( 'cau' => 'https://phongdao.com/tuc-ngu/khong-co-bai-nay/', 'quan_he' => 'gan' ) ), 'hai_chieu' => '1' ) );
$chinh215 = $gui215( $tn_a, 'gan_nghia', array( 'muc' => array( array( 'cau' => (string) $tn_a, 'quan_he' => 'gan' ) ) ) );
kt(
	'Câu gần nghĩa: dòng dán không ra bài thì báo, không đoán; không nối với chính nó; chưa lưu gì',
	! $khong215['ok'] && str_contains( implode( ' ', $khong215['loi'] ), 'Không tìm thấy bài cho “https://phongdao.com/tuc-ngu/khong-co-bai-nay/”' ) && ! $chinh215['ok'] && str_contains( implode( ' ', $chinh215['loi'] ), 'Không nối một câu với chính nó.' ) && null === PhanBai::lay( $tn_a, 'gan_nghia' ),
	array( $khong215, $chinh215 )
);
$kq_gn215 = $gui215(
	$tn_a,
	'gan_nghia',
	array(
		'muc'       => array(
			array( 'cau' => (string) get_permalink( $tn_b ), 'quan_he' => 'gan' ),
			array( 'cau' => (string) $tn_c, 'quan_he' => 'trai' ),
			array( 'cau' => '[UX-TEST] Tục ngữ chưa đăng D', 'quan_he' => 'gan' ),
			array( 'cau' => (string) $tn_tho, 'quan_he' => 'gan' ),
			array( 'cau' => (string) $tn_b, 'quan_he' => 'trai' ),
		),
		'hai_chieu' => '1',
	)
);
$gn_a215 = PhanBai::lay( $tn_a, 'gan_nghia' )['muc'] ?? array();
$gn_b215 = PhanBai::lay( $tn_b, 'gan_nghia' )['muc'] ?? array();
$gn_c215 = PhanBai::lay( $tn_c, 'gan_nghia' )['muc'] ?? array();
kt(
	'Câu gần nghĩa: đường dẫn, số bài, tên bài đúng từng chữ đều ra bài; câu trùng chỉ giữ một; "Ghi cả ở câu kia" thêm chiều ngược lại cùng quan hệ ở câu tục ngữ kia, có Lịch sử ở câu kia',
	$kq_gn215['ok'] && 4 === count( $gn_a215 ) && array( $tn_b, $tn_c, $tn_nhap, $tn_tho ) === array_map( 'intval', array_column( $gn_a215, 'tac_pham_id' ) ) && 'trai' === $gn_a215[1]['quan_he']
		&& array( array( 'tac_pham_id' => $tn_a, 'quan_he' => 'gan' ) ) === $gn_b215 && array( array( 'tac_pham_id' => $tn_a, 'quan_he' => 'trai' ) ) === $gn_c215
		&& 1 === count( array_filter( Viec::lich_su( 'post', $tn_b, 20 ), static fn( array $r ): bool => 'gan_nghia' === $r['phan'] ) ) && str_contains( (string) $kq_gn215['thong_bao'], 'Đã ghi cả ở' ),
	array( $kq_gn215, $gn_a215, $gn_b215, $gn_c215 )
);
$bo215 = $gui215( $tn_a, 'gan_nghia', array( 'muc' => array( array( 'tac_pham_id' => (string) $tn_b, 'quan_he' => 'gan', 'xoa' => '1' ), array( 'tac_pham_id' => (string) $tn_c, 'quan_he' => 'trai' ), array( 'tac_pham_id' => (string) $tn_nhap, 'quan_he' => 'gan' ), array( 'tac_pham_id' => (string) $tn_tho, 'quan_he' => 'gan' ) ), 'hai_chieu' => '1' ) );
kt( 'Câu gần nghĩa: "Bỏ nối câu này" chỉ bỏ ở câu này, không lan sang câu kia', $bo215['ok'] && 3 === count( PhanBai::lay( $tn_a, 'gan_nghia' )['muc'] ?? array() ) && 1 === count( PhanBai::lay( $tn_b, 'gan_nghia' )['muc'] ?? array() ), $bo215 );
$form_gn215 = TamSua::than( 'gan_nghia', PhanBai::lay( $tn_a, 'gan_nghia' ), CheDoSua::ngu_canh( get_post( $tn_a ) ) );
kt(
	'Sửa câu gần nghĩa: câu đã nối hiện tên, số bài, "chưa đăng" khi chưa đăng, chọn quan hệ, "Bỏ nối câu này"; câu mới là ô dán; "Ghi cả ở câu kia" đánh dấu sẵn',
	str_contains( $form_gn215, '[UX-TEST] Tục ngữ chưa đăng D <span class="small muted">· #' . $tn_nhap . ' · chưa đăng</span>' ) && str_contains( $form_gn215, '<option value="trai" selected' ) && str_contains( $form_gn215, 'Bỏ nối câu này' )
		&& str_contains( $form_gn215, 'name="muc[__i__][cau]"' ) && (bool) preg_match( '/name="hai_chieu" value="1" checked/', $form_gn215 ),
	substr( $form_gn215, 0, 1500 )
);
wp_set_current_user( 0 );
$h_gn215 = TacPham::hien( DocBai::tac_pham( get_post( $tn_a ) ) );
$hang_gn215 = (string) strstr( (string) strstr( $h_gn215, '<details class="hang-mo" id="gan-nghia"' ), '</details>', true );
$h_gn_b215 = TacPham::hien( DocBai::tac_pham( get_post( $tn_b ) ) );
kt(
	'trang đọc: hàng "Câu trái nghĩa" chỉ có câu đã đăng, không có từ ngữ thô tục (câu nháp, câu thô tục bị bỏ); câu ngắn hiện chính lời, dẫn tới trang câu kia; câu kia có hàng "Câu gần nghĩa" ngược lại; hàng nằm sau Dị bản',
	str_contains( $hang_gn215, '<summary><span>Câu trái nghĩa</span></summary>' ) && str_contains( $hang_gn215, '<span class="gn-loai">Trái nghĩa</span><a href="' . esc_url( (string) get_permalink( $tn_c ) ) . '">[UX-TEST] Câu C đủ một dòng</a>' )
		&& ! str_contains( $hang_gn215, 'Câu D' ) && ! str_contains( $hang_gn215, 'Câu E' )
		&& str_contains( $h_gn_b215, '<span class="gn-loai">Gần nghĩa</span><a href="' . esc_url( (string) get_permalink( $tn_a ) ) . '">[UX-TEST] Câu A một vế, [UX-TEST] Câu A vế hai</a>' ),
	array( $hang_gn215, (string) strstr( $h_gn_b215, 'gan-nghia' ) )
);
$h_khong_gn215 = TacPham::hien( DocBai::tac_pham( get_post( $tn_c ) ) );
wp_update_post( array( 'ID' => $tn_c, 'post_status' => 'draft' ) );
$h_het_gn215 = TacPham::hien( DocBai::tac_pham( get_post( $tn_a ) ) );
kt( 'trang đọc: không còn câu đã đăng nào thì không có hàng câu gần nghĩa (khách không thấy hàng trống)', ! str_contains( $h_het_gn215, 'id="gan-nghia"' ) && str_contains( $h_khong_gn215, 'id="gan-nghia"' ), (string) strstr( $h_het_gn215, 'hang-nhom' ) );
wp_set_current_user( $ai_ql );
$h_het_sua215 = TacPham::hien( DocBai::tac_pham( get_post( $tn_a ) ), CheDoSua::ngu_canh( get_post( $tn_a ) ) );
kt( 'chế độ sửa: câu đã nối mà người đọc chưa thấy thì ghi số câu, không ghi "Chưa nối câu nào"; không có dòng nguồn gốc (phần không ghi người làm)', str_contains( $h_het_sua215, '3 câu đã nối chưa hiện cho người đọc (câu chưa đăng hoặc có từ ngữ thô tục).' ) && ! str_contains( $h_het_sua215, 'Chưa nối câu nào' ) && ! str_contains( $h_het_sua215, 'Câu gần nghĩa: ' ), (string) strstr( $h_het_sua215, 'id="gan-nghia"' ) );
wp_update_post( array( 'ID' => $tn_c, 'post_status' => 'publish' ) );

// ---------- Chế độ sửa trên bố cục gọn ----------
wp_set_current_user( $ai_ql );
$sua215 = CheDoSua::ngu_canh( get_post( $tn_a ) );
$h_sua215 = TacPham::hien( DocBai::tac_pham( get_post( $tn_a ) ), $sua215 );
kt(
	'chế độ sửa dùng bố cục gọn: dải "Đang sửa", khung lời gọn có nút Sửa lời, Từ khó trong khung, Đối chiếu sách dưới dòng nguồn; tục ngữ có nút Thêm ý nghĩa / Thêm nghĩa đen, không có nút Nghĩa bóng, Dùng khi khi bài chưa có; hàng mở sẵn có nút Thêm (Nguồn gốc, Dị bản, Câu gần nghĩa); Lịch sử; không còn bố cục cũ',
	str_contains( $h_sua215, '<div class="dai-bien-tap' ) && str_contains( $h_sua215, 'class="verse-card the-gon g-tuc-ngu"' ) && str_contains( $h_sua215, 'data-tam="loi"' ) && str_contains( $h_sua215, 'data-tam="chu_thich"' )
		&& str_contains( $h_sua215, '<p class="row no-print sua-doi-chieu">' ) && str_contains( $h_sua215, 'data-tam="nghia_den"' ) && ! str_contains( $h_sua215, 'data-tam="nghia_bong"' ) && ! str_contains( $h_sua215, 'data-tam="dung_khi"' )
		&& (bool) preg_match( '/<details class="hang-mo" id="nguon-goc" open data-chi-sua>/', $h_sua215 ) && (bool) preg_match( '/<details class="hang-mo" id="di-ban" open data-chi-sua>/', $h_sua215 ) && (bool) preg_match( '/<details class="hang-mo" id="gan-nghia" open>/', $h_sua215 )
		&& str_contains( $h_sua215, 'data-tam="gan_nghia"' ) && str_contains( $h_sua215, 'id="lich-su"' ) && str_contains( $h_sua215, 'id="tam-gan_nghia"' ) && str_contains( $h_sua215, 'id="tam-chuyen_quanh"' )
		&& ! str_contains( $h_sua215, 'class="work-head"' ) && ! str_contains( $h_sua215, 'Lời này từ đâu?' ) && ! str_contains( $h_sua215, 'class="verse-card dang-sua"' ),
	substr( (string) strstr( $h_sua215, 'work-rest' ), 0, 3000 )
);
kt( 'tấm sửa Chuyện quanh bài của tục ngữ tên là "Sửa nguồn gốc"', str_contains( $h_sua215, '<h2 id="tam-chuyen_quanh-tieu-de">Sửa nguồn gốc</h2>' ) );
$sua_cd215 = CheDoSua::ngu_canh( get_post( $cq_bai ) );
$h_sua_cd215 = TacPham::hien( DocBai::tac_pham( get_post( $cq_bai ) ), $sua_cd215 );
kt(
	'chế độ sửa ca dao: Ý nghĩa trống hiện "Thêm ý nghĩa" (chỉ người biên soạn thấy); Hình ảnh trong bài trống hiện để thêm; hàng Chuyện quanh bài mở sẵn có nút Sửa và dòng nguồn gốc của phần; cờ, phân loại ở chế độ sửa; không có hàng Câu gần nghĩa',
	str_contains( $h_sua_cd215, '<div class="meaning trong" data-chi-sua>' ) && (bool) preg_match( '/<details class="hang-mo" id="hinh-anh" open data-chi-sua>/', $h_sua_cd215 )
		&& (bool) preg_match( '/<details class="hang-mo" id="chuyen-quanh-bai" open>/', $h_sua_cd215 ) && str_contains( $h_sua_cd215, 'Chuyện quanh bài: 1 mục máy chép' )
		&& str_contains( $h_sua_cd215, 'Cờ “Có thể không phải dân gian”' ) && str_contains( $h_sua_cd215, 'data-tam="phan-loai"' ) && ! str_contains( $h_sua_cd215, 'id="gan-nghia"' ),
	substr( (string) strstr( $h_sua_cd215, 'work-rest' ), 0, 3000 )
);

// ---------- Bài có từ ngữ thô tục trên bố cục gọn ----------
wp_set_current_user( 0 );
$h_tho215 = TacPham::hien( DocBai::tac_pham( get_post( $tn_tho ) ) );
$vi_h1215 = strpos( $h_tho215, '<h1 class="read-title tho-tuc-ten">Tục ngữ – Thành ngữ có từ ngữ thô tục</h1>' );
$vi_che215 = strpos( $h_tho215, '<details class="tho-tuc tho-tuc-bai">' );
kt(
	'bài thô tục: tên trung tính là h1 ngoài khối che; cả bố cục gọn (khung lời) trong khối "Hiển thị lời" đóng sẵn; một h1; lời không làm tiêu đề, không có trước khối che',
	false !== $vi_h1215 && false !== $vi_che215 && $vi_h1215 < $vi_che215 && 1 === substr_count( $h_tho215, '<h1' ) && strpos( $h_tho215, 'class="verse-card the-gon' ) > $vi_che215
		&& strpos( $h_tho215, '[UX-TEST] Câu E cần che' ) > $vi_che215 && ! str_contains( $h_tho215, 'loi-ten' ),
	substr( $h_tho215, 0, 1500 )
);
wp_set_current_user( $ai_ql );
$h_tho_sua215 = TacPham::hien( DocBai::tac_pham( get_post( $tn_tho ) ), CheDoSua::ngu_canh( get_post( $tn_tho ) ) );
kt( 'bài thô tục, người biên soạn: khối che mở sẵn, tên thật là h1 trong khung lời', str_contains( $h_tho_sua215, '<details class="tho-tuc tho-tuc-bai" open>' ) && str_contains( $h_tho_sua215, '<h1 class="read-title">[UX-TEST] Tục ngữ thô tục E</h1>' ) && ! str_contains( $h_tho_sua215, 'tho-tuc-ten' ) );

foreach ( array( $cq_bai, $tn_a, $tn_b, $tn_c, $tn_nhap, $tn_tho, $cd215, $hs215 ) as $x215 ) {
	wp_delete_post( $x215, true );
}
wp_set_current_user( 0 );
