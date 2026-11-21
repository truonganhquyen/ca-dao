<?php
/**
 * Hồi quy Từ điển nối bài (D-126): trang bài hiện nghĩa "Theo Từ điển" khi người đã nối mà bài chưa có từ khó riêng; Bổ
 * sung → Gợi ý Từ điển tìm câu có trọn chữ, người chọn nghĩa (máy không tự gắn), "Không nối" được nhớ; ô "Nối Từ điển" ở
 * từ khó đổi / bỏ nối và gắn / gỡ mục từ của bài; trang mục từ hiện dòng có chữ; bài viết tạm không lập chỉ mục. Chạy sau
 * kiem-tu-dien.php (dùng lại $muc_td, $bai_td, $sang, $hsang, $sang2, $mai). Chỉ dữ liệu kỹ thuật.
 */
use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\DichVu\TuDien\GoiYNoi;
use Phongdao\GiaoTiep\BienTap\TamSua;
use Phongdao\GiaoTiep\Khoi\TacPham;
use Phongdao\GiaoTiep\QuanTri\BoSung;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\NguoiLam;

wp_set_current_user( $ai_ql );

// 1. Tô chữ: trọn chữ, đúng dấu, không phân biệt hoa thường; cụm nhiều chữ
$to1 = GoiYNoi::to( 'Muốn chết thì lết vào Săng.', 'săng' );
$to2 = GoiYNoi::to( 'Cái sàng cái sảy', 'săng' );
$to3 = GoiYNoi::to( 'Làm hàng săng, chết bó chiếu', 'hàng săng' );
$to4 = GoiYNoi::to( 'Ăn săngsái <b>x</b> săng', 'săng' );
kt(
	'Gợi ý Từ điển: tô đúng chữ (khác hoa thường vẫn khớp, khác dấu không khớp, cụm nhiều chữ, không khớp nửa chữ), chữ quanh được thoát',
	'Muốn chết thì lết vào <mark>Săng</mark>.' === $to1 && null === $to2 && '<mark>Làm hàng săng</mark>, chết bó chiếu' !== $to3 && 'Làm <mark>hàng săng</mark>, chết bó chiếu' === $to3
		&& 'Ăn săngsái &lt;b&gt;x&lt;/b&gt; <mark>săng</mark>' === $to4,
	array( $to1, $to2, $to3, $to4 )
);

// 2. Câu chưa nối: bài có trọn chữ, chưa nối nghĩa nào của chữ
$g_a = $bai_td( '[UX-TEST] Gợi ý săng một', array( '[UX-TEST] Dòng đầu không có', '[UX-TEST] Muốn chết thì lết vào săng' ), array() );
$g_b = $bai_td( '[UX-TEST] Gợi ý săng hai', array( '[UX-TEST] Vô rú mà đốn săng' ), array() );
$g_c = $bai_td( '[UX-TEST] Gợi ý chỉ có sàng', array( '[UX-TEST] Cái sàng cái sảy' ), array() );
$g_d = $bai_td( '[UX-TEST] Gợi ý đã nối săng', array( '[UX-TEST] Lết vào săng' ), array( $sang ) );
delete_transient( GoiYNoi::DEM );
$cau = GoiYNoi::cau( 'săng' );
$ids = array_column( $cau['ds'], 'bai' );
$ca  = array_values( array_filter( $cau['ds'], static fn( array $c ): bool => $g_a === $c['bai'] ) )[0] ?? array();
$tq  = GoiYNoi::tong_quan();
kt(
	'Gợi ý Từ điển: liệt kê bài có trọn chữ chưa nối (bỏ bài chỉ có chữ khác dấu, bài đã nối nghĩa của chữ); câu hiện đúng dòng có chữ, chữ được tô; tổng quan đếm câu chưa nối',
	in_array( $g_a, $ids, true ) && in_array( $g_b, $ids, true ) && ! in_array( $g_c, $ids, true ) && ! in_array( $g_d, $ids, true )
		&& array( '[UX-TEST] Muốn chết thì lết vào <mark>săng</mark>' ) === ( $ca['dong'] ?? null )
		&& ( $tq['săng']['goi_y'] ?? 0 ) >= 2 && 1 === ( $tq['săng']['nghia'] ?? 0 ) && ( $tq['mai']['nghia'] ?? 0 ) >= 3,
	array( $cau, $tq['săng'] ?? null )
);

// 3. Màn Bổ sung: tab tổng quan, màn duyệt một chữ (mặc định Chưa chọn, có Không nối, Chọn cả nhóm cần JS)
$_GET   = array( 'page' => BoSung::TRANG, 'xem' => 'tu_dien' );
$h_tab  = BoSung::hien();
$_GET   = array( 'page' => BoSung::TRANG, 'xem' => 'tu_dien', 'chu' => 'Săng' );
$h_chu  = BoSung::hien();
$_GET   = array( 'page' => BoSung::TRANG, 'xem' => 'tu_dien', 'chu' => 'mai' );
$h_mai  = BoSung::hien();
$_GET   = array();
kt(
	'Bổ sung → Gợi ý Từ điển: tab có bảng chữ và lối duyệt; màn một chữ hiện nghĩa, mỗi câu một nhóm chọn (Chưa chọn đánh dấu sẵn · nghĩa · Không nối), nút Chọn cả nhóm ẩn khi chưa có JS; chữ nhiều nghĩa có đủ nghĩa',
	str_contains( $h_tab, 'Gợi ý Từ điển' ) && str_contains( $h_tab, '<h2 id="tu-dien">Gợi ý nối Từ điển</h2>' ) && str_contains( $h_tab, esc_url( BoSung::url_tu_dien( 'săng' ) ) )
		&& str_contains( $h_chu, '<h1>Chữ “Săng”</h1>' ) && str_contains( $h_chu, 'name="chon[' . $g_a . ']" value="" checked' ) && str_contains( $h_chu, 'name="chon[' . $g_a . ']" value="' . $sang . '"' ) && str_contains( $h_chu, 'name="chon[' . $g_a . ']" value="0"' )
		&& str_contains( $h_chu, 'data-ca-nhom="' . $sang . '" data-chi-js hidden' ) && str_contains( $h_chu, 'value="phongdao_bo_sung_tu_dien"' ) && str_contains( $h_chu, '<mark>săng</mark>' ) && ! str_contains( $h_chu, 'name="chon[' . $g_d . ']"' )
		&& 3 === substr_count( $h_mai, '<li><strong>Mai</strong>' ),
	array( substr( (string) strstr( $h_chu, '<h1>' ), 0, 1500 ) )
);

// 4. Lưu lựa chọn: nối qua Lịch sử; Không nối được nhớ; nghĩa của chữ khác bị từ chối; máy không tự gắn bài chưa chọn
$truoc_viec = (int) $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( 'SELECT COUNT(*) FROM ' . Viec::bang() . " WHERE doi_tuong_id = %d AND phan = 'muc_tu'", $g_a ) );
$kq_luu = GoiYNoi::luu( 'săng', array( $g_a => $sang, $g_b => 0, $g_c => $sang, $g_d => $sang2 ), $ai_ql );
$sau_viec = (int) $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( 'SELECT COUNT(*) FROM ' . Viec::bang() . " WHERE doi_tuong_id = %d AND phan = 'muc_tu'", $g_a ) );
$cau2 = array_column( GoiYNoi::cau( 'săng' )['ds'], 'bai' );
kt(
	'Gợi ý Từ điển: lưu nối đúng nghĩa (ghi Lịch sử), "Không nối" nhớ cho bài đó, bài không có chữ hay nghĩa của chữ khác bị từ chối; bài đã chọn không còn trong danh sách',
	1 === $kq_luu['noi'] && 1 === $kq_luu['khong'] && 2 === $kq_luu['loi']
		&& has_term( $sang, 'muc_tu', $g_a ) && ! has_term( $sang, 'muc_tu', $g_b ) && ! has_term( $sang, 'muc_tu', $g_c ) && ! has_term( $sang2, 'muc_tu', $g_d )
		&& $sau_viec === $truoc_viec + 1 && array( 'săng' ) === GoiYNoi::khong_noi( $g_b ) && ! in_array( $g_a, $cau2, true ) && ! in_array( $g_b, $cau2, true ),
	array( $kq_luu, $truoc_viec, $sau_viec, $cau2 )
);

// 5. Trang bài: đã nối, chưa có từ khó riêng → nghĩa "Theo Từ điển"; có từ khó riêng cùng chữ → chỉ từ khó riêng
$tp_a  = DocBai::tac_pham( get_post( $g_a ) );
$td_a  = array_values( array_filter( $tp_a['chu_thich'], static fn( array $c ): bool => ! empty( $c['theo_td'] ) ) );
$h_a   = TacPham::hien( $tp_a );
LuuPhan::luu( $g_d, 'chu_thich', array( array( 'tu' => 'săng', 'loi_giai' => '[UX-TEST] Lời giải riêng của bài.', 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nc_td );
$tp_d  = DocBai::tac_pham( get_post( $g_d ) );
$h_d   = TacPham::hien( $tp_d );
kt(
	'Trang bài: người đã nối mà bài chưa có từ khó riêng thì hiện nghĩa trong Từ điển, nhãn "Theo Từ điển", lối sang mục từ, chữ được tô trong lời; bài có từ khó riêng cùng chữ chỉ hiện lời giải riêng',
	1 === count( $td_a ) && '[UX-TEST] Quan tài.' === $td_a[0]['giai'] && str_contains( $h_a, '<span class="nhan-td">Theo Từ điển</span>' ) && str_contains( $h_a, '[UX-TEST] Quan tài.' ) && str_contains( $h_a, 'Xem trong Từ điển' )
		&& str_contains( $h_a, 'mục theo' ) && ! str_contains( $h_a, 'mục người biên soạn viết' )
		&& ! str_contains( $h_d, 'nhan-td' ) && str_contains( $h_d, '[UX-TEST] Lời giải riêng của bài.' ) && ! str_contains( $h_d, '[UX-TEST] Quan tài.' ),
	array( $td_a, substr( (string) strstr( $h_a, 'id="chu-thich"' ), 0, 1500 ) )
);

// 6. Ô "Nối Từ điển" ở từ khó: đủ nghĩa cùng chữ + Không nối; đổi nghĩa thì gắn mục từ mới, gỡ mục từ cũ (Lịch sử)
$g_m = $bai_td( '[UX-TEST] Bài mai nối thử', array( '[UX-TEST] Mai về quê' ), array( $mai[0] ) );
LuuPhan::luu( $g_m, 'chu_thich', array( array( 'tu' => 'mai', 'loi_giai' => '[UX-TEST] Ngày mai.', 'muc_tu_id' => $mai[0], 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nc_td );
$h_o  = TamSua::than( 'chu_thich', PhanBai::lay( $g_m, 'chu_thich' ), array( 'ho_so' => array() ) );
$yc_m = new WP_REST_Request( 'POST', '/phongdao/v1/tac-pham/' . $g_m . '/phan/chu_thich' );
$yc_m->set_body_params( array( 'muc' => array( array( 'goc' => '0', 'tu' => 'mai', 'loi_giai' => '[UX-TEST] Ngày mai.', 'muc_tu' => (string) $mai[1] ) ) ) );
$yc_m->set_param( 'id', $g_m );
$yc_m->set_param( 'phan', 'chu_thich' );
$yc_m->set_param( 'phien_phan', LuuPhan::phien( $g_m, 'chu_thich', PhanBai::lay( $g_m, 'chu_thich' ) ) );
$kq_m  = \Phongdao\GiaoTiep\BienTap\Module::luu_phan( $yc_m );
$sau_m = PhanBai::lay( $g_m, 'chu_thich' )['muc'][0] ?? array();
$gan_m = wp_get_object_terms( $g_m, 'muc_tu', array( 'fields' => 'ids' ) );
$yc_b  = new WP_REST_Request( 'POST', '/phongdao/v1/tac-pham/' . $g_m . '/phan/chu_thich' );
$yc_b->set_body_params( array( 'muc' => array( array( 'goc' => '0', 'tu' => 'mai', 'loi_giai' => '[UX-TEST] Ngày mai.', 'muc_tu' => '0' ) ) ) );
$yc_b->set_param( 'id', $g_m );
$yc_b->set_param( 'phan', 'chu_thich' );
$yc_b->set_param( 'phien_phan', LuuPhan::phien( $g_m, 'chu_thich', PhanBai::lay( $g_m, 'chu_thich' ) ) );
$kq_b  = \Phongdao\GiaoTiep\BienTap\Module::luu_phan( $yc_b );
$sau_b = PhanBai::lay( $g_m, 'chu_thich' )['muc'][0] ?? array();
kt(
	'Từ khó: ô "Nối Từ điển" có đủ ba nghĩa của "mai" và Không nối; chọn nghĩa khác thì từ khó nối nghĩa mới, bài gắn mục từ mới, gỡ mục từ cũ; chọn Không nối thì bỏ nối và gỡ mục từ (không bị coi là mất nối)',
	str_contains( $h_o, 'name="muc[0][muc_tu]"' ) && 4 === substr_count( (string) strstr( (string) strstr( $h_o, 'name="muc[0][muc_tu]"' ), '</select>', true ), '<option' ) && str_contains( $h_o, 'value="' . $mai[0] . '" selected' )
		&& $kq_m['ok'] && $mai[1] === (int) ( $sau_m['muc_tu_id'] ?? 0 ) && array( $mai[1] ) === array_map( 'intval', $gan_m ) && '[UX-TEST] Ngày mai.' === ( $sau_m['loi_giai'] ?? '' )
		&& $kq_b['ok'] && empty( $sau_b['muc_tu_id'] ) && array() === wp_get_object_terms( $g_m, 'muc_tu', array( 'fields' => 'ids' ) )
		&& ! array_filter( \Phongdao\DichVu\TuDien\NoiLai::ds(), static fn( array $d ): bool => $g_m === $d['bai'] ),
	array( $kq_m, $sau_m, $gan_m, $kq_b, $sau_b )
);

// 7. Trang mục từ: tiêu đề "Câu có chữ …", thẻ bài hiện dòng có chữ, nói rõ chú giải riêng hay theo Từ điển
$q_mt = new WP_Query( array( 'muc_tu' => 'sang-ux-test' ) );
$h_mt = \Phongdao\GiaoTiep\Khoi\DanhSach::hien( $q_mt );
kt(
	'Trang mục từ: khối "Câu có chữ “Săng”", thẻ hiện đúng dòng có chữ đã tô, dòng dưới ghi "Hiện nghĩa theo Từ điển" hay "Bài có chú giải riêng"',
	str_contains( $h_mt, '<h2 id="ds-tieu-de">Câu có chữ “Săng”</h2>' ) && str_contains( $h_mt, '[UX-TEST] Muốn chết thì lết vào <mark>săng</mark>' ) && str_contains( $h_mt, 'Hiện nghĩa theo Từ điển' ) && str_contains( $h_mt, 'Bài có chú giải riêng' ),
	substr( (string) strstr( $h_mt, 'ds-tieu-de' ), 0, 1500 )
);

// 8. Bài viết (loại post): tạm không lập chỉ mục cho tới khi người biên soạn đánh dấu đã có quyền
$bv = (int) wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => '[UX-TEST] Bài viết của tác giả khác', 'post_content' => '<p>[UX-TEST]</p>' ) );
$robots_bv = static function () use ( $bv ): array {
	$GLOBALS['wp_query'] = new WP_Query( array( 'p' => $bv, 'post_type' => 'post' ) );
	$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
	return \Phongdao\GiaoTiep\Seo\Module::robots( array() );
};
$r1 = $robots_bv();
update_post_meta( $bv, \Phongdao\GiaoTiep\Seo\Module::BAI_VIET_GOOGLE, '1' );
$r2 = $robots_bv();
$GLOBALS['wp_query'] = new WP_Query();
$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
kt(
	'Bài viết: mặc định noindex (vẫn follow); đánh dấu "Đã có quyền đăng" thì Google được lập chỉ mục',
	! empty( $r1['noindex'] ) && ! empty( $r1['follow'] ) && empty( $r2['noindex'] ),
	array( $r1, $r2 )
);

foreach ( array( $g_a, $g_b, $g_c, $g_d, $g_m, $bv ) as $x ) {
	wp_delete_post( $x, true );
}
delete_option( GoiYNoi::TAT );
delete_transient( GoiYNoi::DEM );
