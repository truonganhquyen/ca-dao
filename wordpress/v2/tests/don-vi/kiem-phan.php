<?php
/**
 * Phần của bài (D-094, docs/26 mục 4.3): danh mục phần, bộ kiểm lằn ranh khi lưu, cấp chất lượng, bản chữ.
 * Kiểm thử khẳng định các lằn ranh ở docs/28 mục 6. Chữ mẫu tổng hợp, không phải lời dân gian thật.
 */

declare(strict_types=1);

use Phongdao\MienDuLieu\BanChu;
use Phongdao\MienDuLieu\CapChatLuong;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\Phan;
use Phongdao\MienDuLieu\TheLoai;

$nguoi = array( 'loai_bai' => 'tac_pham', 'the_loai' => 'ca-dao', 'goi' => KiemPhan::GOI_NGUOI, 'duyet' => false );
$duyet = array( 'duyet' => true ) + $nguoi;
$may   = array( 'goi' => KiemPhan::GOI_MAY ) + $nguoi;
$cu    = array( 'goi' => KiemPhan::GOI_NHAP_CU ) + $nguoi;
$tn    = array( 'the_loai' => 'tuc-ngu' ) + $nguoi;
$k     = static fn( string $phan, mixed $v, array $nc ): array => KiemPhan::kiem( $phan, $v, $nc );
$dat   = static fn( array $kq ): bool => array() === $kq[1] && null !== $kq[0];
$hong  = static fn( array $kq ): bool => array() !== $kq[1] && null === $kq[0];
$loi   = array( 'dong' => array( 'Dòng mẫu một', 'Dòng mẫu hai' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET );
$yn    = static fn( bool $ai ): array => array( 'chu' => 'Một câu giải nghĩa mẫu.', 'ai' => $ai, 'nguoi_lam' => $ai ? NguoiLam::AI_DA_DUYET : NguoiLam::NGUOI_VIET );
$ct    = static fn( array $them = array() ): array => array( $them + array( 'tu' => 'mẫu', 'loi_giai' => 'Lời giải mẫu.', 'nguoi_lam' => NguoiLam::MAY_CHEP ) );

// ---------- Danh mục phần ----------
$kieu_hop_le = '/^(dong|chu|chu_ngan|chu_dai|bool|so|id|ds_id|ds_so|url|ngay|doi_chieu|chon:[a-z_|]+)!?$/';
$sai         = array();
foreach ( Phan::loai_bai() as $loai_bai ) {
	foreach ( Phan::ds( $loai_bai ) as $p ) {
		$dn = Phan::lay( $loai_bai, $p );
		if ( ! in_array( $dn['dang'], array( Phan::MOT, Phan::DS ), true ) || ( null !== $dn['nguoi_lam'] && array_diff( $dn['nguoi_lam'], NguoiLam::DS ) ) || ( null !== $dn['the_loai'] && array_diff( $dn['the_loai'], array_keys( TheLoai::DS ) ) ) ) {
			$sai[] = "$loai_bai.$p";
		}
		foreach ( $dn['truong'] as $kieu ) {
			if ( ! preg_match( $kieu_hop_le, $kieu ) ) {
				$sai[] = "$loai_bai.$p:$kieu";
			}
		}
		// có ô "có hỗ trợ của AI" ⇔ người làm được phép gồm ai_da_duyet
		if ( isset( $dn['truong']['ai'] ) !== in_array( NguoiLam::AI_DA_DUYET, $dn['nguoi_lam'] ?? array(), true ) ) {
			$sai[] = "$loai_bai.$p:ai";
		}
	}
}
kt( 'danh mục phần: dạng, người làm, thể loại, kiểu trường hợp lệ; ô AI khớp người làm', array() === $sai, $sai );
kt( 'tác phẩm có đủ phần ở docs/26 mục 4.3', array( 'loi', 'di_ban', 'y_nghia', 'nghia_den', 'nghia_bong', 'dung_khi', 'hinh_anh', 'gan_nghia', 'giai_sach', 'chu_thich', 'cau_hoi', 'dap_an', 'goi_y', 'doan', 'boi_canh', 'media', 'ban_co_biet', 'co_nghi', 'sgk', 'nguon_may' ) === Phan::ds( 'tac_pham' ) );
kt( 'khoá meta có tiền tố _phongdao_ (ẩn khỏi Trường tuỳ chỉnh)', '_phongdao_loi' === Phan::khoa( 'loi' ) );
$ai_trong_loi = array_filter( Phan::KHONG_AI, static fn( $p ) => array_filter( Phan::lay( 'tac_pham', $p )['nguoi_lam'], array( NguoiLam::class, 'la_ai' ) ) );
kt( 'lằn ranh 1: lời, dị bản không có người làm AI trong danh mục', array() === $ai_trong_loi );

// ---------- Chuẩn hoá ----------
$kq = $k( 'loi', array( 'dong' => array( '', '  Dòng   mẫu một ', '', '', "Dòng mẫu hai\t", "Ho\u{0061}\u{0300} mẫu", '' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nguoi );
kt( 'lời: cắt khoảng trắng, gộp dòng trống thành một chỗ ngắt khổ, bỏ dòng trống đầu / cuối, NFC', $dat( $kq ) && array( 'Dòng mẫu một', '', 'Dòng mẫu hai', "Ho\u{00E0} mẫu" ) === $kq[0]['dong'], $kq );
kt( 'lời trống → lỗi thiếu lời', $hong( $k( 'loi', array( 'dong' => array( ' ', '' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nguoi ) ) );
kt( 'một dòng chứa xuống dòng → lỗi', $hong( $k( 'loi', array( 'dong' => array( "a\nb" ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nguoi ) ) );
kt( 'trường lạ → lỗi', $hong( $k( 'loi', $loi + array( 'la' => 1 ), $nguoi ) ) );
$kq = $k( 'loi', $loi + array( 'sua_boi' => 9, 'sua_luc' => '2026-01-01 00:00:00' ), $nguoi );
kt( 'trường máy chủ đặt (sua_boi, sua_luc…) gửi lên thì bỏ qua', $dat( $kq ) && ! isset( $kq[0]['sua_boi'] ) && ! isset( $kq[0]['sua_luc'] ) );
kt( 'chữ ngắn quá 200 ký tự → lỗi', $hong( $k( 'chu_thich', array( array( 'tu' => str_repeat( 'a', 201 ), 'loi_giai' => 'x', 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nguoi ) ) );
kt( 'ký tự điều khiển, khoảng trắng không rộng bị bỏ', $dat( $kq = $k( 'co_nghi', array( 'ly_do' => "Lý\u{200B} do\x07 mẫu", 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nguoi ) ) && 'Lý do mẫu' === $kq[0]['ly_do'], $kq );
kt( 'phần không có → lỗi; ai đang lưu không rõ → lỗi', $hong( $k( 'khong_co', array(), $nguoi ) ) && $hong( $k( 'loi', $loi, array( 'goi' => 'la' ) + $nguoi ) ) );

// ---------- Danh sách ----------
$kq = $k( 'chu_thich', $ct(), $nguoi );
kt( 'danh sách gửi dạng mảng mục → lưu dạng ["muc" => [...]]', $dat( $kq ) && 1 === count( $kq[0]['muc'] ) );
kt( 'danh sách dạng ["muc" => …] cũng nhận', $dat( $k( 'chu_thich', array( 'muc' => $ct(), 'sua_luc' => 'x' ), $nguoi ) ) );
kt( 'danh sách rỗng = xoá phần, không lỗi', array( null, array() ) === $k( 'chu_thich', array(), $nguoi ) );
kt( 'null = xoá phần (người bấm)', array( null, array() ) === $k( 'y_nghia', null, $nguoi ) );
kt( 'mục thiếu lời giải → lỗi, ghi rõ mục thứ mấy', ( $kq = $k( 'chu_thich', array( array( 'tu' => 'x', 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ), $nguoi ) ) && $hong( $kq ) && str_contains( implode( ' ', $kq[1] ), 'chu_thich #1' ) );

// ---------- Lằn ranh 1: lời, dị bản không nhận người làm AI ----------
kt( 'lời với người làm ai_da_duyet → lỗi', $hong( $k( 'loi', array( 'nguoi_lam' => NguoiLam::AI_DA_DUYET ) + $loi, $duyet ) ) );
kt( 'dị bản với người làm ai_da_duyet → lỗi, kể cả bước nhập cũ', $hong( $k( 'di_ban', array( array( 'dong' => array( 'x' ), 'nguoi_lam' => NguoiLam::AI_DA_DUYET ) ), $cu ) ) );

// ---------- Lằn ranh 2: dấu AI / "đã duyệt" chỉ do người có quyền bấm ----------
kt( 'ý nghĩa có hỗ trợ AI, người không có quyền duyệt → lỗi (thành nháp ở bảng việc)', $hong( $k( 'y_nghia', $yn( true ), $nguoi ) ) );
kt( 'ý nghĩa có hỗ trợ AI, người có quyền duyệt bấm → nhận', $dat( $k( 'y_nghia', $yn( true ), $duyet ) ) );
kt( 'máy không bao giờ đưa phần có hỗ trợ AI vào bài', $hong( $k( 'y_nghia', $yn( true ), array( 'duyet' => true ) + $may ) ) && $hong( $k( 'nghia_bong', $yn( true ), array( 'duyet' => true, 'the_loai' => 'tuc-ngu' ) + $may ) ) );
kt( 'bước nhập cũ mang đúng dấu AI đã duyệt trên MVP', $dat( $k( 'y_nghia', $yn( true ), $cu ) ) );
kt( 'dấu AI phải khớp người làm (bật AI mà người làm là người viết → lỗi, và ngược lại)', $hong( $k( 'y_nghia', array( 'ai' => true ) + $yn( false ), $duyet ) ) && $hong( $k( 'y_nghia', array( 'ai' => false ) + $yn( true ), $duyet ) ) );
kt( 'ô AI không gửi thì là "không" (không tự đặt dấu)', false === $k( 'y_nghia', array( 'chu' => 'x', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nguoi )[0]['ai'] );

// ---------- Lằn ranh 3: máy chỉ chép, không điền nguồn ----------
kt( 'máy chép chú thích kèm hồ sơ nguồn → nhận', $dat( $k( 'chu_thich', $ct( array( 'nguon' => array( 'ho_so' => 3 ) ) ), $may ) ) );
kt( 'máy điền trang / đường dẫn / ngày của nguồn → lỗi', $hong( $k( 'chu_thich', $ct( array( 'nguon' => array( 'ho_so' => 3, 'trang' => '12' ) ) ), $may ) ) && $hong( $k( 'chu_thich', $ct( array( 'nguon' => array( 'url' => 'https://vi.wikipedia.org/' ) ) ), $may ) ) );
kt( 'máy ghi phần với người làm "người viết" → lỗi', $hong( $k( 'loi', $loi, $may ) ) );
kt( 'máy điền nơi, người kể, năm của dị bản → lỗi', $hong( $k( 'di_ban', array( array( 'dong' => array( 'x' ), 'noi' => 'Nơi mẫu', 'nguoi_lam' => NguoiLam::MAY_CHEP ) ), $may ) ) );
kt( 'máy điền "Lời đã đối chiếu với" → lỗi', $hong( $k( 'loi', array( 'nguoi_lam' => NguoiLam::MAY_CHEP, 'doi_chieu' => array( 'ho_so' => 1, 'trang' => '5' ) ) + $loi, $may ) ) );
kt( 'máy không ghi ý nghĩa, bối cảnh, cờ, SGK (chỉ người viết)', $hong( $k( 'y_nghia', $yn( false ), $may ) ) && $hong( $k( 'co_nghi', array( 'ly_do' => 'x', 'nguoi_lam' => NguoiLam::MAY_CHEP ), $may ) ) && $hong( $k( 'sgk', array( array( 'bo_sach' => 'x', 'lop' => 6, 'bai' => '1', 'nguoi_lam' => NguoiLam::MAY_CHEP ) ), $may ) ) );
kt( 'máy không xoá phần nào', $hong( array( null, $k( 'loi', null, $may )[1] ) ) );
kt( 'địa chỉ gốc máy đã lấy: máy ghi được, người không ghi tay', $dat( $k( 'nguon_may', array( 'nguon_id' => 'nguon-mau', 'url_goc' => 'https://example.org/bai' ), $may ) ) && $hong( $k( 'nguon_may', array( 'nguon_id' => 'x' ), $nguoi ) ) );

// Không trường nguồn nào có giá trị mặc định: thiếu thì đầu ra cũng không có
$ra_thieu = array();
foreach ( array( array( 'loi', $loi ), array( 'chu_thich', $ct() ), array( 'di_ban', array( array( 'dong' => array( 'x' ), 'nguoi_lam' => NguoiLam::NGUOI_VIET ) ) ) ) as [ $p, $v ] ) {
	$ra  = $k( $p, $v, $nguoi )[0];
	$muc = isset( $ra['muc'] ) ? $ra['muc'][0] : $ra;
	foreach ( array( 'nguon', 'doi_chieu', 'noi', 'nguoi_ke', 'nam', 'trang' ) as $truong ) {
		if ( array_key_exists( $truong, $muc ) ) {
			$ra_thieu[] = "$p.$truong";
		}
	}
}
kt( 'lằn ranh 3: không gửi nguồn / đối chiếu / nơi / người kể / năm thì không tự có', array() === $ra_thieu, $ra_thieu );
kt( 'nguồn gửi trống hết → không ghi nguồn', ! isset( $k( 'chu_thich', $ct( array( 'nguon' => array( 'trang' => ' ', 'url' => '' ) ) ), $nguoi )[0]['muc'][0]['nguon'] ) );

// ---------- Kiểu trường ----------
kt( 'đường dẫn javascript: → lỗi; https → nhận', $hong( $k( 'nguon_may', array( 'nguon_id' => 'x', 'url_goc' => 'javascript:alert(1)' ), $may ) ) && $dat( $k( 'nguon_may', array( 'nguon_id' => 'x', 'url_goc' => 'https://example.org/a?b=1' ), $may ) ) );
kt( 'ngày không có thật → lỗi', $hong( $k( 'chu_thich', $ct( array( 'nguoi_lam' => NguoiLam::NGUOI_VIET, 'nguon' => array( 'ngay_truy_cap' => '2026-02-30' ) ) ), $nguoi ) ) );
kt( 'đối chiếu sách: cần cả sách và trang', $hong( $k( 'loi', array( 'doi_chieu' => array( 'ho_so' => 2 ) ) + $loi, $nguoi ) ) && $dat( $k( 'loi', array( 'doi_chieu' => array( 'ho_so' => 2, 'trang' => '41' ) ) + $loi, $nguoi ) ) );
kt( 'lựa chọn ngoài danh sách → lỗi', $hong( $k( 'gan_nghia', array( array( 'tac_pham_id' => 3, 'quan_he' => 'khac' ) ), $tn ) ) );
kt( 'số: chuỗi chữ số nhận, số âm → lỗi', 7 === $k( 'gan_nghia', array( array( 'tac_pham_id' => '7', 'quan_he' => 'gan' ) ), $tn )[0]['muc'][0]['tac_pham_id'] && $hong( $k( 'gan_nghia', array( array( 'tac_pham_id' => -1, 'quan_he' => 'gan' ) ), $tn ) ) );

// ---------- Theo thể loại ----------
kt( 'nghĩa đen chỉ cho tục ngữ – thành ngữ', $hong( $k( 'nghia_den', $yn( false ), $nguoi ) ) && $dat( $k( 'nghia_den', $yn( false ), $tn ) ) );
kt( 'đáp án chỉ cho câu đố', $hong( $k( 'dap_an', array( 'chu' => 'x', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $nguoi ) ) && $dat( $k( 'dap_an', array( 'chu' => 'x', 'nguoi_lam' => NguoiLam::NGUOI_VIET ), array( 'the_loai' => 'cau-do' ) + $nguoi ) ) );
$ve = array( 'the_loai' => 've' ) + $nguoi;
kt( 'bối cảnh trống → lỗi; đoạn có dòng cuối trước dòng đầu → lỗi', $hong( $k( 'boi_canh', array( 'nguoi_lam' => NguoiLam::NGUOI_VIET ), $ve ) ) && $hong( $k( 'doan', array( array( 'tu_dong' => 5, 'den_dong' => 2 ) ), $ve ) ) );
kt( 'tuyển tập: lời dẫn, thông tin, mục', $dat( $k( 'muc', array( array( 'tac_pham_id' => 4, 'ghi_chu' => 'Hợp khi mẫu' ) ), array( 'loai_bai' => 'tuyen_tap', 'the_loai' => null ) + $nguoi ) ) && $hong( $k( 'thong_tin', array( 'loai' => 'tam_trang' ), array( 'loai_bai' => 'tuyen_tap' ) + $nguoi ) ) );

kt( 'noi_dung() bỏ trường máy chủ ở phần và ở từng mục', array( 'muc' => array( array( 'a' => 1 ) ) ) === KiemPhan::noi_dung( array( 'muc' => array( array( 'a' => 1, 'sua_boi' => 2 ) ), 'sua_luc' => 'x' ) ) && null === KiemPhan::noi_dung( null ) );

// ---------- Cấp chất lượng ----------
$p0 = array( 'loi' => array( 'dong' => array( 'x' ) ) );
kt( 'cấp: chưa có lời → null; lời mà thiếu thể loại → L0; có thể loại → L1', null === CapChatLuong::tinh( array(), 'ca-dao' ) && 0 === CapChatLuong::tinh( $p0, null ) && 1 === CapChatLuong::tinh( $p0, 'ca-dao' ) );
kt( 'cấp: có ý nghĩa → L2; chỉ nghĩa đen → vẫn L1; nghĩa đen + nghĩa bóng → L2', 2 === CapChatLuong::tinh( $p0 + array( 'y_nghia' => array( 'chu' => 'x' ) ), 'ca-dao' ) && 1 === CapChatLuong::tinh( $p0 + array( 'nghia_den' => array( 'chu' => 'x' ) ), 'tuc-ngu' ) && 2 === CapChatLuong::tinh( $p0 + array( 'nghia_den' => array( 'chu' => 'x' ), 'nghia_bong' => array( 'chu' => 'y' ) ), 'tuc-ngu' ) );
kt( 'cấp: L2 + lời đã đối chiếu sách hoặc bối cảnh → L3; đối chiếu mà chưa có nghĩa → L1', 3 === CapChatLuong::tinh( array( 'loi' => array( 'dong' => array( 'x' ), 'doi_chieu' => array( 'ho_so' => 1, 'trang' => '2' ) ), 'y_nghia' => array( 'chu' => 'x' ) ), 'ca-dao' ) && 1 === CapChatLuong::tinh( array( 'loi' => array( 'dong' => array( 'x' ), 'doi_chieu' => array( 'ho_so' => 1, 'trang' => '2' ) ) ), 'ca-dao' ) );

// ---------- Bản chữ (post_content) ----------
$bc = BanChu::tu_phan( array( 'loi' => array( 'dong' => array( 'Câu <b>một</b>', 'Câu hai', '', 'Khổ hai' ) ), 'y_nghia' => array( 'chu' => 'Ý & nghĩa' ), 'chu_thich' => array( 'muc' => array( array( 'loi_giai' => 'KHÔNG-IN' ) ) ), 'nguon_may' => array( 'url_goc' => 'https://example.org/an' ) ) );
kt( 'bản chữ: mỗi khổ một đoạn, mỗi câu một dòng, chữ được thoát', str_starts_with( $bc, "<p>Câu &lt;b&gt;một&lt;/b&gt;<br>Câu hai</p>\n<p>Khổ hai</p>" ) && str_contains( $bc, 'Ý &amp; nghĩa' ), $bc );
kt( 'bản chữ không có chú thích, địa chỉ gốc máy đã lấy', ! str_contains( $bc, 'KHÔNG-IN' ) && ! str_contains( $bc, 'example.org' ) );
