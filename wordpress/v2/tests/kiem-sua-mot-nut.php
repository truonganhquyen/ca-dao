<?php
/**
 * Hồi quy T-218 (D-121, docs/42 mục 4): màn Sửa tác phẩm hai cột — khung Đăng với một nút "Lưu thay đổi" (mỗi form phần
 * gửi kèm tra_json=1 nhận JSON, Lịch sử ghi từng phần), lời báo sau khi lưu chung, phân loại thành chip (chủ đề máy gợi ý
 * là chip nét đứt, chỉ gắn khi người bấm và lưu; gắn xong thì thẻ gợi ý đóng với quyết "Đồng ý"), "Bài này còn thiếu".
 * Chỉ dữ liệu kỹ thuật [UX-TEST].
 */
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\GiaoTiep\QuanTri\SuaTacPham as Sua218;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;

wp_set_current_user( $ai_ql );
$nc218 = array( 'nguoi' => $ai_ql, 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => true );
$p218  = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => '[UX-TEST] Một nút lưu', 'post_name' => 'ux-test-mot-nut-luu' ) );
wp_set_object_terms( $p218, 'ca-dao', KhaiBao::THE_LOAI );
LuuPhan::luu( $p218, 'loi', array( 'dong' => array( '[UX-TEST] Dòng một nút lưu một', '[UX-TEST] Dòng một nút lưu hai' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nc218 );
$cd218a = wp_insert_term( '[UX-TEST] Chủ đề gắn sẵn', 'chu_de', array( 'slug' => 'ux-test-chu-de-gan-san' ) );
$cd218b = wp_insert_term( '[UX-TEST] Chủ đề máy gợi ý', 'chu_de', array( 'slug' => 'ux-test-chu-de-may-goi-y' ) );
wp_set_object_terms( $p218, array( (int) $cd218a['term_id'] ), 'chu_de' );
$gy218 = Viec::tao( array( 'loai' => 'goi_y', 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $p218, 'noi_dung' => array( 'loai' => 'chu_de', 'ma' => 'ux-test-chu-de-may-goi-y', 'ly_do' => '[UX-TEST] gợi ý' ), 'nguoi_tao' => 'may' ) );
$man218 = static function ( array $them = array() ) use ( $p218 ): string {
	$_GET = array( 'id' => (string) $p218 ) + $them;
	$h    = Sua218::hien();
	$_GET = array();
	return $h;
};

// ---------- Bố cục ----------
$h218 = $man218();
kt(
	'Sửa tác phẩm (D-121): khung Đăng có trạng thái, nút "Lưu thay đổi" (JavaScript, tắt khi chưa sửa), Đăng / Ẩn / Về nháp, Xem bài; tiêu đề màn là tên bài kèm thẻ trạng thái màu',
	str_contains( $h218, 'data-hop-dang data-bai="' . $p218 . '"' ) && str_contains( $h218, 'data-luu-tat-ca disabled>Lưu thay đổi</button>' ) && str_contains( $h218, '<span class="pq-the luc">Đã đăng</span>' )
		&& str_contains( $h218, '<h1><span class="screen-reader-text">Sửa tác phẩm: </span>[UX-TEST] Một nút lưu' ) && str_contains( (string) strstr( $h218, 'id="dang"' ), 'Ẩn khỏi người đọc' ) && str_contains( $h218, '<div class="pq-thanh-luu" data-thanh-luu hidden>' )
);
kt(
	'Phân loại (D-121): mục đang gắn là chip đặc (bấm để bỏ), chủ đề máy gợi ý là chip nét đứt chưa đánh dấu (máy không tự gắn), mục khác trong "＋ Thêm"; vẫn là form riêng của bộ',
	(bool) preg_match( '/<label class="pq-chip" for="pl-chu_de-' . (int) $cd218a['term_id'] . '"><input type="checkbox"[^>]*value="' . (int) $cd218a['term_id'] . '" checked/', $h218 )
		&& (bool) preg_match( '/<label class="pq-chip goi-y" for="pl-chu_de-' . (int) $cd218b['term_id'] . '"><input type="checkbox"[^>]*value="' . (int) $cd218b['term_id'] . '">/', $h218 )
		&& str_contains( $h218, 'máy gợi ý' ) && str_contains( $h218, '<details class="pq-pl-them"><summary class="pq-chip them">＋ Thêm' ) && str_contains( $h218, 'value="phongdao_sua_phan_loai"' ) && str_contains( $h218, 'id="phan-loai-chu_de"' )
);
kt(
	'"Bài này còn thiếu": Lời, Chủ đề đã có (✓); Ý nghĩa, Đối chiếu sách thiếu là lối mở đúng phần; Từ khó ghi tuỳ chọn',
	(bool) preg_match( '/<li class="co"><span aria-hidden="true">✓<\/span> Lời/', $h218 ) && (bool) preg_match( '/<li class="co"><span aria-hidden="true">✓<\/span> Chủ đề/', $h218 )
		&& str_contains( $h218, '<a href="#phan-y_nghia" data-toi-phan="phan-y_nghia">' ) && str_contains( $h218, '<a href="#doi-chieu" data-toi-phan="doi-chieu">Đối chiếu sách</a>' ) && str_contains( $h218, '(tuỳ chọn)' )
);

// ---------- Lưu chung: mỗi form phần gửi kèm tra_json=1, nhận JSON ----------
$json218 = static function ( callable $f ): ?array {
	$chan = static fn() => static function (): void {
		throw new \RuntimeException( 'json' );
	};
	add_filter( 'wp_doing_ajax', '__return_true' );
	add_filter( 'wp_die_ajax_handler', $chan );
	ob_start();
	try {
		$f();
	} catch ( \RuntimeException $e ) {
		unset( $e );
	}
	$ra = json_decode( (string) ob_get_clean(), true );
	remove_filter( 'wp_doing_ajax', '__return_true' );
	remove_filter( 'wp_die_ajax_handler', $chan );
	return is_array( $ra ) ? $ra : null;
};
$_POST = $_REQUEST = array( 'tra_json' => '1', 'id' => (string) $p218, 'phan' => 'y_nghia', 'duyet' => '1', 'phien_phan' => LuuPhan::phien( $p218, 'y_nghia', null ), 'chu' => '[UX-TEST] Ý nghĩa lưu bằng nút chung.', '_wpnonce' => wp_create_nonce( 'phongdao_sua_phan_' . $p218 . '_y_nghia' ) );
$j_yn = $json218( array( Sua218::class, 'luu' ) );
$_POST = $_REQUEST = array( 'tra_json' => '1', 'id' => (string) $p218, 'phan' => 'y_nghia', 'duyet' => '1', 'phien_phan' => 'phien-cu', 'chu' => '[UX-TEST] Bản mở cũ.', '_wpnonce' => wp_create_nonce( 'phongdao_sua_phan_' . $p218 . '_y_nghia' ) );
$j_cu = $json218( array( Sua218::class, 'luu' ) );
$ls_yn = array_values( array_filter( Viec::lich_su( 'post', $p218, 20 ), static fn( array $r ): bool => 'y_nghia' === $r['phan'] ) );
kt(
	'Lưu chung: một phần gửi kèm tra_json nhận JSON {ok, phan, ket_qua} thay vì chuyển trang; Lịch sử ghi đúng phần đó; bản mở cũ bị chặn, trả lời báo',
	true === ( $j_yn['ok'] ?? null ) && 'y_nghia' === ( $j_yn['phan'] ?? '' ) && 'da_luu' === ( $j_yn['ket_qua'] ?? '' ) && '[UX-TEST] Ý nghĩa lưu bằng nút chung.' === ( PhanBai::lay( $p218, 'y_nghia' )['chu'] ?? '' ) && 1 === count( $ls_yn )
		&& false === ( $j_cu['ok'] ?? null ) && 'loi' === ( $j_cu['ket_qua'] ?? '' ) && '' !== ( $j_cu['loi'] ?? '' ) && '[UX-TEST] Ý nghĩa lưu bằng nút chung.' === ( PhanBai::lay( $p218, 'y_nghia' )['chu'] ?? '' ),
	array( $j_yn, $j_cu )
);
// Chip nét đứt: người đánh dấu rồi lưu thì mục được gắn và thẻ gợi ý đóng "Đồng ý" (người quyết)
$_POST = $_REQUEST = array( 'tra_json' => '1', 'id' => (string) $p218, 'tax' => 'chu_de', 'chon' => array( (string) $cd218a['term_id'], (string) $cd218b['term_id'] ), 'da_thay' => array( (string) $cd218a['term_id'] ), '_wpnonce' => wp_create_nonce( 'phongdao_sua_phan_loai_' . $p218 . '_chu_de' ) );
$j_pl = $json218( array( Sua218::class, 'luu_phan_loai' ) );
$_POST = $_REQUEST = array();
$v_gy = Viec::lay( (int) $gy218 );
kt(
	'Lưu chung phân loại: JSON {ok, phan: chu_de}; chủ đề máy gợi ý được gắn vì người đánh dấu; thẻ gợi ý xong, quyết "Đồng ý", ghi người quyết',
	true === ( $j_pl['ok'] ?? null ) && 'chu_de' === ( $j_pl['phan'] ?? '' ) && has_term( (int) $cd218b['term_id'], 'chu_de', $p218 ) && Viec::XONG === ( $v_gy['trang_thai'] ?? '' ) && 'dong_y' === ( $v_gy['quyet'] ?? '' ) && $ai_ql === (int) ( $v_gy['nguoi_quyet'] ?? 0 ),
	array( $j_pl, $v_gy )
);
// Lời báo sau "Lưu thay đổi": tên phần đã lưu, gửi nháp, chưa lưu được (chỉ nhận tên phần có thật)
$h_bao = $man218( array( 'ket_qua' => 'da_luu_nhieu', 'cac_phan' => 'y_nghia,chu_de,the_tho,khong-co-phan' ) );
$h_loi = $man218( array( 'ket_qua' => 'luu_chua_het', 'cac_phan' => 'y_nghia', 'chua' => 'chu_thich' ) );
kt(
	'Sau "Lưu thay đổi": báo "Đã lưu 3 phần: Ý nghĩa, Chủ đề, Thể thơ. Lịch sử ghi từng phần." (bỏ tên lạ); còn phần chưa lưu được thì báo lỗi kèm tên phần, chữ vẫn giữ trên tab',
	str_contains( $h_bao, 'Đã lưu 3 phần: Ý nghĩa, Chủ đề, Thể thơ. Lịch sử ghi từng phần.' ) && ! str_contains( $h_bao, 'khong-co-phan' ) && str_contains( $h_bao, 'notice-success' )
		&& str_contains( $h_loi, 'notice-error' ) && str_contains( $h_loi, 'Chưa lưu được: Từ khó' ) && str_contains( $h_loi, 'chữ đang nhập vẫn giữ trên tab này' )
);

wp_delete_post( $p218, true );
wp_delete_term( (int) $cd218a['term_id'], 'chu_de' );
wp_delete_term( (int) $cd218b['term_id'], 'chu_de' );
Viec::xoa( (int) $gy218 );
$_GET = $_POST = $_REQUEST = array();
