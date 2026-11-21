<?php
/**
 * Hồi quy đợt giao diện (D-126): thanh lọc dùng chung (chip có ×, Xoá lọc, nút Bộ lọc cho điện thoại, vẫn là form GET),
 * thẻ bài lấy lời làm trọng tâm (dải màu, số từ khó), ô "Từ khó hôm nay", đầu trang thể loại có màu và hoạ tiết. Chỉ dữ
 * liệu kỹ thuật.
 */
use Phongdao\GiaoTiep\Khoi\DanhSach;
use Phongdao\HaTang\KhaiBao;

$gd_ds = static function ( array $tham, bool $tat_ca ): string {
	$q = new WP_Query( $tham + ( $tat_ca ? array( 'post_type' => KhaiBao::TAC_PHAM ) : array() ) + array( 'posts_per_page' => 30, 'orderby' => 'title', 'order' => 'ASC' ) );
	if ( $tat_ca ) {
		$q->is_post_type_archive = true;
		$q->is_archive           = true;
	}
	return DanhSach::hien( $q );
};
$kho   = (string) get_post_type_archive_link( KhaiBao::TAC_PHAM );
$h_loc = $gd_ds( array( KhaiBao::THE_LOAI => 'ca-dao', 'sap' => 'az' ), true );
$h_ko  = $gd_ds( array(), true );
kt(
	'Thanh lọc (D-126): mục đang chọn thành chip có × (bỏ đúng mục đó, giữ sắp xếp), có Xoá lọc; ô đang chọn được tô; nút "Bộ lọc (1)" cho điện thoại; chưa lọc thì không có dòng "Đang lọc"',
	str_contains( $h_loc, 'aria-label="Bỏ lọc Thể loại: Ca dao"' ) && str_contains( $h_loc, 'class="loc-x" href="' . esc_url( add_query_arg( array( 'sap' => 'az' ), $kho ) ) . '"' )
		&& str_contains( $h_loc, '<a class="loc-xoa" href="' . esc_url( $kho ) . '">Xoá lọc</a>' ) && str_contains( $h_loc, 'name="the_loai" class="co"' ) && str_contains( $h_loc, '<summary>Bộ lọc (1)</summary>' )
		&& str_contains( $h_loc, '<button type="submit" class="btn primary loc-ap">Áp dụng</button>' ) && ! str_contains( $h_ko, 'Đang lọc:' ) && str_contains( $h_ko, '<summary>Bộ lọc</summary>' ),
	substr( (string) strstr( $h_loc, '<form class="thanh-loc"' ), 0, 2500 )
);
$h_tl = $gd_ds( array( KhaiBao::THE_LOAI => 'ca-dao' ), false );
kt(
	'Trang thể loại (D-126): đầu trang màu thể loại (số bài), thanh lọc chung chỉ có "Có giải nghĩa" và Sắp xếp',
	str_contains( $h_tl, '<header class="dau-tl g-ca-dao"><p class="eyebrow">Thể loại</p><h1>Ca dao</h1>' ) && str_contains( $h_tl, '</b> bài</span>' ) && str_contains( $h_tl, '<form class="thanh-loc"' ) && ! str_contains( $h_tl, 'name="chu_de"' ) && str_contains( $h_tl, 'name="sap"' ),
	substr( (string) strstr( $h_tl, '<header class="dau-tl' ), 0, 1200 )
);
$h_the = DanhSach::the( array( 'id' => 1, 'tieu_de' => 'Tên riêng thử', 'url' => home_url( '/' ), 'the_loai' => 'ca-dao', 'ten_the_loai' => 'Ca dao', 'dong' => array( 'Dòng một', 'Dòng hai', 'Dòng ba', 'Dòng bốn' ), 'y_nghia' => 'Nghĩa thử.', 'co_nghi' => false, 'so_tu_kho' => 2 ) );
kt(
	'Thẻ bài (D-126): lời làm trọng tâm (tới bốn dòng), dải màu thể loại, Ý nghĩa ngắn, số từ khó',
	str_contains( $h_the, '<article class="card the-loi g-ca-dao">' ) && 4 === substr_count( $h_the, '<span class="dong">' ) && str_contains( $h_the, '<p class="muted y-mot">Nghĩa thử.</p>' ) && str_contains( $h_the, '2 từ khó' ),
	$h_the
);
// Ô "Từ khó hôm nay": mục từ có giải thích và từ hai bài
$gd_t   = wp_insert_term( 'Từ khó giao diện thử', 'muc_tu', array( 'slug' => 'tu-kho-giao-dien-ux-test', 'description' => '[UX-TEST] Giải thích thử.' ) );
$gd_tid = (int) $gd_t['term_id'];
$gd_bai = array();
foreach ( array( 1, 2 ) as $i ) {
	$gd_bai[] = (int) wp_insert_post( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'post_title' => '[UX-TEST] Giao diện ' . $i ) );
	wp_set_object_terms( end( $gd_bai ), $gd_tid, 'muc_tu' );
}
delete_transient( 'phongdao_tu_kho_hom_nay_' . wp_date( 'Ymd' ) );
$gd_m = new ReflectionMethod( DanhSach::class, 'tu_kho_hom_nay' );
$gd_m->setAccessible( true );
$h_o  = (string) $gd_m->invoke( null );
kt(
	'Ô "Từ khó hôm nay" (D-126): một mục từ có giải thích, từ hai bài; tên dẫn tới mục từ, có số bài và lối Từ điển; cùng ngày thì cùng mục',
	str_contains( $h_o, '<aside class="card o-noi" aria-labelledby="o-noi-h">' ) && str_contains( $h_o, 'bài có từ này' ) && str_contains( $h_o, 'Mở Từ điển' ) && $h_o === (string) $gd_m->invoke( null ),
	$h_o
);
foreach ( $gd_bai as $x ) {
	wp_delete_post( $x, true );
}
wp_delete_term( $gd_tid, 'muc_tu' );
delete_transient( 'phongdao_tu_kho_hom_nay_' . wp_date( 'Ymd' ) );
