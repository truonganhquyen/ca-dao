<?php
/**
 * Nhập dữ liệu cũ (docs/29): tách nội dung bài MVP thành các phần, người làm theo kho, "trước = sau", tên đường dẫn mới.
 * Dữ liệu tổng hợp.
 */

use Phongdao\DichVu\NhapDuLieuCu\DungPhan;
use Phongdao\DichVu\NhapDuLieuCu\TachBai;

/** Một khối như parse_blocks trả về. */
function nc_khoi( string $ten, string $html, array $attrs = array(), array $con = array() ): array {
	return array( 'blockName' => $ten, 'attrs' => $attrs, 'innerHTML' => $html, 'innerBlocks' => $con );
}
function nc_tho( string $html ): array {
	return nc_khoi( 'core/verse', '<pre class="wp-block-verse">' . $html . '</pre>' );
}
function nc_tieu_de( string $chu ): array {
	return nc_khoi( 'core/heading', '<h2 class="wp-block-heading">' . $chu . '</h2>' );
}
function nc_ds_ct( array $li, bool $chep ): array {
	$lop = 'cadao-chu-thich' . ( $chep ? ' cadao-chep' : '' );
	return nc_khoi( 'core/list', '<ul class="wp-block-list ' . $lop . '"></ul>', array( 'className' => $lop ), array_map( static fn( $x ) => nc_khoi( 'core/list-item', "<li>$x</li>" ), $li ) );
}
function nc_bai( array $khoi, array $them = array() ): array {
	return $them + array( 'id' => 1, 'the_loai' => array( 'ca-dao' ), 'khoi' => $khoi, 'tom_tat' => '', 'meta' => array(), 'kho' => array() );
}
$GLOBALS['nc_ho_so_goi'] = array();
$nc = array(
	'ho_so_nguon' => array( 'cadao-me' => 7 ),
	'ho_so'       => static function ( string $loai, string $ten, string $url ): int {
		$GLOBALS['nc_ho_so_goi'][] = array( $loai, $ten, $url );
		return 'sach' === $loai ? 41 : 40;
	},
);

kt( 'lời khối Thơ đọc như MVP: <br> thành dòng, giải mã, cắt khoảng trắng hai đầu mỗi dòng', "Dòng một & hai\nDòng hai <nhỏ>" === TachBai::loi_tho( "  Dòng một &amp; hai<br>Dòng hai &lt;nhỏ&gt;\u{00A0}" ) );

// Bài máy tạo từ kho, chưa ai sửa
$kho = array(
	array( 'loi' => "Dòng mẫu một\nDòng mẫu hai", 'nguon_id' => 'cadao-me', 'du_lieu' => array( 'di_ban' => array( "Dị bản một\nDị bản hai" ), 'chu_thich' => array( array( 'tu' => 'mẫu', 'noi_dung' => 'Lời giải mẫu.' ) ) ) ),
);
$khoi = array(
	nc_tho( 'Dòng mẫu một<br>Dòng mẫu hai' ),
	nc_tieu_de( 'Dị bản' ),
	nc_tho( 'Dị bản một<br>Dị bản hai' ),
	nc_tieu_de( 'Chú thích' ),
	nc_ds_ct( array( '<strong>mẫu</strong>: Lời giải mẫu.' ), true ),
	nc_khoi( 'core/paragraph', '<p class="cadao-chu-thich-nguon">Nguồn: <a href="https://example.org/1">Nguồn thử</a></p>', array( 'className' => 'cadao-chu-thich-nguon' ) ),
	array( 'blockName' => null, 'attrs' => array(), 'innerHTML' => "\n\n", 'innerBlocks' => array() ),
);
$t = TachBai::tach( $khoi );
kt( 'tách: lời, dị bản, chú thích máy chép, dòng Nguồn; không gì cần xem', "Dòng mẫu một\nDòng mẫu hai" === $t['loi'] && array( "Dị bản một\nDị bản hai" ) === $t['di_ban'] && array( array( 'tu' => 'mẫu', 'noi_dung' => 'Lời giải mẫu.', 'chep' => true ) ) === $t['chu_thich'] && $t['dong_nguon'] && array() === $t['can_xem'], $t );
$r = DungPhan::dung( nc_bai( $khoi, array( 'kho' => $kho ) ), $nc );
kt( 'bài từ kho: lời, dị bản, chú thích là máy chép, gắn hồ sơ nguồn của kho', 'may_chep' === $r['phan']['loi']['nguoi_lam'] && 7 === ( $r['phan']['loi']['nguon']['ho_so'] ?? 0 ) && 'may_chep' === $r['phan']['di_ban']['muc'][0]['nguoi_lam'] && 'may_chep' === $r['phan']['chu_thich']['muc'][0]['nguoi_lam'] && 7 === $r['phan']['chu_thich']['muc'][0]['nguon']['ho_so'], $r['phan'] );
kt( 'bài từ kho: trước = sau đúng từng ký tự, không cần xem', array( 'khop' ) === array_values( array_unique( $r['so_sanh'] ) ) && array() === $r['can_xem'], $r );
kt( 'máy chép không mang trang, đường dẫn, ngày vào nguồn', array( 'ho_so' ) === array_keys( $r['phan']['chu_thich']['muc'][0]['nguon'] ) );

// Lời đã sửa tay, dị bản thêm tay
$khoi2    = $khoi;
$khoi2[0] = nc_tho( 'Dòng mẫu một<br>Dòng mẫu hai đã sửa' );
array_splice( $khoi2, 3, 0, array( nc_tho( 'Dị bản người thêm' ) ) );
$r = DungPhan::dung( nc_bai( $khoi2, array( 'kho' => $kho ) ), $nc );
kt( 'lời sửa tay → người viết; dị bản thêm tay → người viết, dị bản của kho vẫn máy chép', 'nguoi_viet' === $r['phan']['loi']['nguoi_lam'] && ! isset( $r['phan']['loi']['nguon'] ) && 'may_chep' === $r['phan']['di_ban']['muc'][0]['nguoi_lam'] && 'nguoi_viet' === $r['phan']['di_ban']['muc'][1]['nguoi_lam'], $r['phan'] );

// Câu đố
$do = array(
	nc_tho( 'Cái gì mẫu<br>Có hai dòng' ),
	nc_khoi( 'core/paragraph', '<p><strong>Là cái gì?</strong></p>' ),
	nc_khoi( 'core/details', '<details class="wp-block-details" open><summary>Đáp án</summary></details>', array( 'showContent' => true ), array( nc_khoi( 'core/paragraph', '<p>Cái mẫu</p>' ) ) ),
);
$kho_do = array( array( 'loi' => "Cái gì mẫu\nCó hai dòng", 'nguon_id' => 'cadao-me', 'du_lieu' => array( 'cau_hoi' => 'Là cái gì?', 'dap_an' => 'Cái mẫu' ) ) );
$r = DungPhan::dung( nc_bai( $do, array( 'the_loai' => array( 'cau-do' ), 'kho' => $kho_do ) ), $nc );
kt( 'câu đố: câu hỏi, đáp án tách riêng, máy chép, trước = sau', 'Là cái gì?' === ( $r['phan']['cau_hoi']['chu'] ?? '' ) && 'Cái mẫu' === ( $r['phan']['dap_an']['chu'] ?? '' ) && 'may_chep' === $r['phan']['dap_an']['nguoi_lam'] && 'khop' === $r['so_sanh']['dap_an'] && array() === $r['can_xem'], $r );
$r = DungPhan::dung( nc_bai( $do, array( 'the_loai' => array( 'ca-dao' ) ) ), $nc );
kt( 'đáp án, dòng in đậm ở bài không phải câu đố → cần xem, không nhập vào phần câu đố', ! isset( $r['phan']['dap_an'] ) && ! isset( $r['phan']['cau_hoi'] ) && 2 === count( $r['can_xem'] ), $r['can_xem'] );

// Vè dán vào khối Đoạn văn; lời chia khổ xen dòng chú
$r = DungPhan::dung( nc_bai( array( nc_khoi( 'core/paragraph', '<p>Vè mẫu dòng một<br>Vè mẫu dòng hai</p>' ) ), array( 'the_loai' => array( 've' ) ) ), $nc );
kt( 'lời còn trong khối Đoạn văn → lấy theo dòng như nút "Đưa lời vào khối Thơ", ghi chú, không cần xem', array( 'Vè mẫu dòng một', 'Vè mẫu dòng hai' ) === ( $r['phan']['loi']['dong'] ?? null ) && array() === $r['can_xem'] && 1 === count( $r['ghi_chu'] ), $r );
$doan = array(
	nc_khoi( 'core/paragraph', '<p>Khổ một dòng một<br>Khổ một dòng hai</p>' ),
	nc_khoi( 'core/paragraph', '<p>Khổ hai dòng một<br>Khổ hai dòng hai<br><em>Lời người ghi chép</em></p>' ),
	nc_khoi( 'core/paragraph', '<p>(Hát ở quê nhà)</p>' ),
	nc_khoi( 'core/paragraph', '<p>Dòng lẻ cuối bài</p>' ),
);
$t = TachBai::tach( $doan );
kt( 'đoạn văn nhiều dòng → khổ cách một dòng trống; dòng chú in nghiêng, trong ngoặc (kể cả kết thúc bằng "à") giữ riêng để cần xem', "Khổ một dòng một\nKhổ một dòng hai\n\nKhổ hai dòng một\nKhổ hai dòng hai\n\nDòng lẻ cuối bài" === $t['loi'] && in_array( 'có đoạn văn «Lời người ghi chép»', $t['can_xem'], true ) && in_array( 'có đoạn văn «(Hát ở quê nhà)»', $t['can_xem'], true ), $t );
kt( 'đoạn văn không có dòng lời nào → không chuyển, không bịa lời', null === TachBai::doan_thanh_tho( array( nc_khoi( 'core/paragraph', '<p><em>Chỉ có ghi chú</em></p>' ) ) ) && null === TachBai::tach( array( nc_khoi( 'core/paragraph', '<p><em>Chỉ có ghi chú</em></p>' ) ) )['loi'] );
$r = DungPhan::dung( nc_bai( array( nc_tho( 'Khổ một' ), nc_khoi( 'core/paragraph', '<p><em>(Dòng chú)</em></p>' ), nc_tho( 'Khổ hai' ) ), array( 'the_loai' => array( 've' ) ) ), $nc );
kt( 'nhiều khối Thơ: lời nối thành khổ; dòng chú xen giữa → cần xem kèm chữ của đoạn', "Khổ một\n\nKhổ hai" === implode( "\n", $r['phan']['loi']['dong'] ) && in_array( 'có đoạn văn «(Dòng chú)»', $r['can_xem'], true ) && in_array( 'có khối khác xen giữa các khổ của lời', $r['can_xem'], true ), $r['can_xem'] );
$r = DungPhan::dung( nc_bai( array( nc_tho( 'Lời' ), nc_khoi( 'core/image', '<figure><img src="a.jpg"></figure>' ) ) ), $nc );
kt( 'khối lạ (ảnh) → cần xem, phần chắc chắn vẫn nhập', isset( $r['phan']['loi'] ) && in_array( 'có khối «core/image»', $r['can_xem'], true ) );

// Vè nhiều phần (tiêu đề phụ ngay trên mỗi khối Thơ), bối cảnh, dòng nguồn trong bài, "Dị bản" đứng đầu bài
$h3     = static fn( string $chu ): array => nc_khoi( 'core/heading', '<h3 class="wp-block-heading">' . $chu . '</h3>', array( 'level' => 3 ) );
$nhieu  = array(
	nc_khoi( 'core/paragraph', '<p><em>(Bối cảnh mẫu của bài vè)</em></p>' ),
	$h3( 'Phần mẫu (I)' ),
	nc_tho( 'Phần một dòng một<br>Phần một dòng hai' ),
	$h3( 'Phần mẫu (II)' ),
	nc_tho( 'Phần hai dòng một<br><br>Phần hai dòng hai<br>Phần hai dòng ba' ),
	nc_khoi( 'core/paragraph', '<p><em>(Nguồn:&nbsp;Sách mẫu cuối bài, Nhà xuất bản Mẫu)</em></p>' ),
);
$t = TachBai::tach( $nhieu );
kt(
	'vè nhiều phần: các khối Thơ nối thành lời, mỗi tiêu đề phụ thành một đoạn (đếm dòng có chữ); không báo "xen giữa"',
	"Phần một dòng một\nPhần một dòng hai\n\nPhần hai dòng một\n\nPhần hai dòng hai\nPhần hai dòng ba" === $t['loi'] && array( array( 'tieu_de' => 'Phần mẫu (I)', 'tu_dong' => 1, 'den_dong' => 2 ), array( 'tieu_de' => 'Phần mẫu (II)', 'tu_dong' => 3, 'den_dong' => 5 ) ) === $t['doan'] && ! in_array( 'có khối khác xen giữa các khổ của lời', $t['can_xem'], true ),
	$t
);
kt( 'dòng chú in nghiêng đứng trước lời → bối cảnh, đúng từng chữ', array( '(Bối cảnh mẫu của bài vè)' ) === $t['boi_canh'] );
kt( 'dòng "(Nguồn: …)" trong bài → cần xem, kèm đủ chữ; không tự gắn vào lời', array( 'dòng «(Nguồn: Sách mẫu cuối bài, Nhà xuất bản Mẫu)» không mang sang: nếu là sách đã đối chiếu, ghi ở Sửa lời' ) === $t['can_xem'], $t['can_xem'] );
$r = DungPhan::dung( nc_bai( $nhieu, array( 'the_loai' => array( 've' ) ) ), $nc );
kt(
	'vè: phần Đoạn và Bối cảnh (người viết, không điền năm, nơi); trước = sau',
	2 === count( $r['phan']['doan']['muc'] ?? array() ) && array( 'chu' => '(Bối cảnh mẫu của bài vè)', 'nguoi_lam' => 'nguoi_viet' ) === ( $r['phan']['boi_canh'] ?? null ) && 'khop' === $r['so_sanh']['doan'] && 'khop' === $r['so_sanh']['boi_canh'] && 'khop' === $r['so_sanh']['loi'],
	$r
);
$r = DungPhan::dung( nc_bai( $nhieu, array( 'the_loai' => array( 'ca-dao' ) ) ), $nc );
kt( 'bài không phải vè: không có Đoạn, Bối cảnh; tiêu đề phụ và dòng chú → cần xem', ! isset( $r['phan']['doan'] ) && ! isset( $r['phan']['boi_canh'] ) && in_array( 'có tiêu đề phụ trong lời nhưng bài không phải vè: tiêu đề không mang sang', $r['can_xem'], true ) && in_array( 'có đoạn văn «(Bối cảnh mẫu của bài vè)»', $r['can_xem'], true ), $r['can_xem'] );
$t = TachBai::tach( array( nc_tho( 'Khổ đầu không tiêu đề' ), $h3( 'Phần sau' ), nc_tho( 'Dòng của phần sau' ), nc_tho( 'Khổ tiếp của phần sau' ) ) );
kt( 'khối Thơ không tiêu đề: đầu bài thành đoạn không tiêu đề, sau một phần thì là khổ tiếp của phần đó', array( array( 'tu_dong' => 1, 'den_dong' => 1 ), array( 'tieu_de' => 'Phần sau', 'tu_dong' => 2, 'den_dong' => 3 ) ) === $t['doan'], $t['doan'] );
$t = TachBai::tach( array( nc_tieu_de( 'Dị bản' ), nc_tho( 'Dị bản đứng đầu' ), $h3( 'Phần một' ), nc_tho( 'Lời phần một' ), $h3( 'Phần hai' ), nc_tho( 'Lời phần hai' ) ) );
kt( '"Dị bản" đứng đầu bài, sau là các phần có tiêu đề: khối Thơ dưới "Dị bản" là dị bản, các phần là lời, cần xem', array( 'Dị bản đứng đầu' ) === $t['di_ban'] && "Lời phần một\n\nLời phần hai" === $t['loi'] && 2 === count( $t['doan'] ) && in_array( 'tiêu đề «Dị bản» đứng trước lời: khối Thơ ngay dưới nhập làm dị bản', $t['can_xem'], true ), $t );
$t = TachBai::tach( array( nc_tho( 'Lời' ), $h3( 'Tiêu đề lạ' ), nc_khoi( 'core/paragraph', '<p>Đoạn văn</p>' ) ) );
kt( 'tiêu đề không đứng trên khối Thơ: vẫn cần xem như cũ, lời không có đoạn', in_array( 'có tiêu đề «Tiêu đề lạ»', $t['can_xem'], true ) && array() === $t['doan'] );
kt( 'bài một khối Thơ: không có đoạn, không có bối cảnh', array() === TachBai::tach( array( nc_tho( 'Lời' ) ) )['doan'] && array() === TachBai::tach( array( nc_tho( 'Lời' ) ) )['boi_canh'] );

// Hiểu nhanh, dấu AI, nháp
$r = DungPhan::dung( nc_bai( array( nc_tho( 'Lời' ) ), array( 'tom_tat' => 'Ý nghĩa mẫu.', 'meta' => array( '_cadao_ho_tro_ai' => '1', '_cadao_nhap_hieu_nhanh' => 'Nháp khác.' ) ) ), $nc );
kt( 'Hiểu nhanh có dấu AI → ý nghĩa ai_da_duyet (đúng trạng thái MVP)', array( 'chu' => 'Ý nghĩa mẫu.', 'ai' => true, 'nguoi_lam' => 'ai_da_duyet' ) === $r['phan']['y_nghia'], $r['phan']['y_nghia'] );
kt( 'nháp Hiểu nhanh của máy → việc chờ, không vào bài', array( 'chu' => 'Nháp khác.', 'ai' => true ) === $r['nhap'] );
$r = DungPhan::dung( nc_bai( array( nc_tho( 'Lời' ) ), array( 'tom_tat' => 'Người viết.', 'meta' => array( '_cadao_nhap_hieu_nhanh' => 'Người viết.' ) ) ), $nc );
kt( 'Hiểu nhanh người viết → nguoi_viet; nháp trùng ý nghĩa đã lên trang thì không thành việc', 'nguoi_viet' === $r['phan']['y_nghia']['nguoi_lam'] && false === $r['phan']['y_nghia']['ai'] && null === $r['nhap'] );
$r = DungPhan::dung( nc_bai( array( nc_tho( 'Lời' ) ), array( 'meta' => array( '_cadao_ho_tro_ai' => '1' ) ) ), $nc );
kt( 'dấu AI mà không có Hiểu nhanh → không tạo ý nghĩa', ! isset( $r['phan']['y_nghia'] ) );

// Nguồn người điền, đối chiếu, nguồn máy
$GLOBALS['nc_ho_so_goi'] = array();
$meta = array(
	'nguon_ten'       => 'Sách mẫu', 'nguon_url' => 'https://example.org/sach', 'ngay_truy_cap' => '2026-10-20',
	'doi_chieu_ten'   => 'Tuyển tập mẫu', 'doi_chieu_trang' => '12',
	'_cadao_nguon_id' => 'cadao-me', '_cadao_url_goc' => 'https://example.org/bai-1/', '_cadao_ma_goc' => '1', '_cadao_ngay_lay' => '2026-10-01',
);
$r = DungPhan::dung( nc_bai( array( nc_tho( 'Lời' ), nc_tieu_de( 'Chú thích' ), nc_ds_ct( array( '<strong>từ</strong>: Người viết giải.' ), false ) ), array( 'meta' => $meta ) ), $nc );
kt( 'nguồn người điền → hồ sơ + nguồn của chú thích người viết (trang, đường dẫn, ngày giữ đúng)', array( 'ho_so' => 40, 'url' => 'https://example.org/sach', 'ngay_truy_cap' => '2026-10-20' ) === ( $r['phan']['chu_thich']['muc'][0]['nguon'] ?? null ) && 'nguoi_viet' === $r['phan']['chu_thich']['muc'][0]['nguoi_lam'], $r['phan']['chu_thich'] );
kt( 'đối chiếu → hồ sơ loại sách + trang', array( 'ho_so' => 41, 'trang' => '12' ) === ( $r['phan']['loi']['doi_chieu'] ?? null ) && in_array( array( 'sach', 'Tuyển tập mẫu', '' ), $GLOBALS['nc_ho_so_goi'], true ), $r['phan']['loi'] );
kt( 'địa chỉ gốc máy đã lấy → phần nội bộ nguon_may', array( 'nguon_id' => 'cadao-me', 'url_goc' => 'https://example.org/bai-1/', 'ma_goc' => '1', 'ngay_lay' => '2026-10-01' ) === ( $r['phan']['nguon_may'] ?? null ) );
$nc_mt           = $nc;
$nc_mt['muc_tu'] = static fn( string $tu, string $giai ): int => 'từ' === $tu && 'Người viết giải.' === $giai ? 77 : 0;
$r               = DungPhan::dung( nc_bai( array( nc_tho( 'Lời' ), nc_tieu_de( 'Chú thích' ), nc_ds_ct( array( '<strong>từ</strong>: Người viết giải.', '<strong>khác</strong>: Không có mục từ.' ), false ) ) ), $nc_mt );
kt( 'chú thích nối với mục từ MVP tạo từ nó (cùng từ + lời giải); không có mục từ thì không gắn', 77 === ( $r['phan']['chu_thich']['muc'][0]['muc_tu_id'] ?? 0 ) && ! isset( $r['phan']['chu_thich']['muc'][1]['muc_tu_id'] ), $r['phan']['chu_thich'] );
$r = DungPhan::dung( nc_bai( array( nc_tho( 'Lời' ) ) ), $nc );
kt( 'bài không có ô nguồn → không phần nào có nguồn (không tự điền)', ! isset( $r['phan']['loi']['nguon'] ) && ! isset( $r['phan']['nguon_may'] ) );
$GLOBALS['nc_ho_so_goi'] = array();
$r = DungPhan::dung( nc_bai( array( nc_tho( 'Lời' ) ), array( 'meta' => array( 'nguon_ten' => 'Nguồn lẻ', 'doi_chieu_ten' => 'Sách', 'ngay_truy_cap' => 'hôm qua' ) ) ), $nc );
kt( 'nguồn bài không có chú thích người viết, đối chiếu thiếu trang, ngày lạ → cần xem, không gắn bừa', 3 === count( $r['can_xem'] ) && ! isset( $r['phan']['loi']['doi_chieu'] ), $r['can_xem'] );
kt( 'ô Nguồn không có đường dẫn → hồ sơ loại "chưa rõ" (không đoán là trang web)', in_array( array( '', 'Nguồn lẻ', '' ), $GLOBALS['nc_ho_so_goi'], true ), $GLOBALS['nc_ho_so_goi'] );

// Thể loại, chuẩn hoá, độ dài
$r = DungPhan::dung( nc_bai( array( nc_tho( 'Lời' ) ), array( 'the_loai' => array( 'thanh-ngu' ) ) ), $nc );
kt( 'thể loại cũ "Thành ngữ" → Tục ngữ – Thành ngữ', array( 'tuc-ngu' ) === $r['the_loai'] );
$r = DungPhan::dung( nc_bai( array( nc_tho( 'Lời  có hai dấu cách' ) ) ), $nc );
kt( 'chỉ khác khoảng trắng → ghi chú, không cần xem', 'chuan_hoa' === $r['so_sanh']['loi'] && array() === $r['can_xem'] && 1 === count( $r['ghi_chu'] ), $r );
$r = DungPhan::dung( nc_bai( array( nc_tho( str_repeat( 'dài ', 120 ) ) ) ), $nc );
kt( 'lời không qua được bộ kiểm (dòng quá dài) → không nhập phần đó, cần xem, trước ≠ sau', ! isset( $r['phan']['loi'] ) && 'lech' === $r['so_sanh']['loi'] && count( $r['can_xem'] ) >= 2, $r['can_xem'] );

// Tên đường dẫn có đuôi số
$da = array( 'con-co-no' => true );
kt( 'đuôi -2 → thêm chữ phân biệt từ dòng sau, tránh tên đã có', 'con-co-no-dau' === DungPhan::ten_moi( 'con-co-2', "Con cò bé bé\nNó đậu cành tre", static fn( $x ) => isset( $da[ $x ] ) ) );
kt( 'không có đuôi số → giữ tên', null === DungPhan::ten_moi( 'con-co-be-be', "Con cò\nNó đậu", static fn() => false ) );
kt( 'câu một dòng: lấy chữ chưa có trong tên từ chính dòng đó', 'trang-tron' === DungPhan::ten_moi( 'trang-3', 'Trăng tròn tròn', static fn() => false ) );
