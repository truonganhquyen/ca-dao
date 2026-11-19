<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\GiaoTiep\BienTap\CheDoSua;

defined( 'ABSPATH' ) || exit;

/**
 * Trang tác phẩm gọn (D-119, docs/41; docs/27 mục 6): khung lời (nhãn thể loại, tiêu đề — bài ngắn là chính lời —, lời,
 * chú thích kiểu sách, một dòng nguồn) → hiểu bài (Ý nghĩa / Nghĩa / Cách chơi) + công cụ đọc → hàng mở một dòng (Chuyện
 * quanh bài, Dị bản, Câu gần nghĩa · trái nghĩa, Hình ảnh trong bài) → Hiểu thêm khi có bài của bạn đọc → Đọc thêm → một
 * lời mời góp. Khối không có dữ liệu thì không hiện, kể cả tiêu đề (docs/27 mục 1). Một bố cục cho người đọc, chế độ sửa
 * và bài có từ ngữ thô tục (T-215). Đồng dao: nút Đọc cùng bé; Trình chiếu ở cột công cụ đọc (doc.js).
 */
final class TacPham {

	/**
	 * @param array<string,mixed>      $tp  xem DichVu\DungTrang\DuLieuMau
	 * @param array<string,mixed>|null $sua chế độ sửa (GiaoTiep\BienTap\CheDoSua::ngu_canh) — chỉ khi người xem có quyền
	 */
	public static function hien( array $tp, ?array $sua = null ): string {
		$tp['url'] = $tp['url'] ?? ( get_permalink() ?: home_url( '/' ) );
		$tuc_ngu   = 'proverb' === $tp['kieu'];
		$cau_do    = 'cau-do' === $tp['the_loai'];
		$bai_that  = ctype_digit( (string) $tp['ma'] );
		// Có từ ngữ thô tục (D-111): tên trên trang trung tính, cả bố cục nằm trong khối đóng "Hiển thị lời". Người biên soạn ở
		// chế độ sửa thấy đủ (khối mở sẵn, tên thật).
		$tho       = ! empty( $tp['tho_tuc'] );
		$che       = $tho && ! $sua;
		// Tục ngữ dài (từ 3 dòng, hoặc hơn khoảng 60 chữ cái): hiện như ca dao — tên rồi lời trong khung; câu ngắn giữ kiểu "câu
		// là tiêu đề" (D-105). Bài thô tục luôn hiện lời trong khung để che được.
		$cau_dai   = $tuc_ngu && ( $tho || count( $tp['loi'] ) >= 3 || mb_strlen( (string) preg_replace( '/[^\p{L}]/u', '', implode( '', $tp['loi'] ) ) ) > 60 );
		$cau_ten   = $tuc_ngu && ! $cau_dai;
		$anh       = (string) ( $tp['anh_minh_hoa_html'] ?? '' );
		$kieu      = $cau_dai ? '' : $tp['kieu'];
		$nut       = static fn( string $tam, string $nhan ): string => $sua ? CheDoSua::nut( $tam, $nhan ) : '';
		$goc       = static fn( string $nhan, string $phan ): string => $sua ? CheDoSua::goc( $nhan, $sua['phan'][ $phan ] ?? null, $sua ) : '';
		$nhap      = $sua && $sua['nhap'] ? ' · có ' . count( $sua['nhap'] ) . ' nháp chờ duyệt trong Hộp việc' : '';
		// Trang bài gọn (D-119, docs/41) cho người đọc, người biên soạn (chế độ sửa) và bài có từ ngữ thô tục: khung lời có nhãn
		// thể loại, bài ngắn lấy lời làm tiêu đề, chú thích kiểu sách ngay dưới lời, một dòng nguồn, Sao chép ở thanh công cụ,
		// hiểu bài ngay sau khung lời. Chế độ sửa thêm nút Sửa tại chỗ, dòng nguồn gốc của phần, phần trống để thêm, Lịch sử.
		$ngan      = ! $tho && empty( $tp['doan'] ) && ( $tuc_ngu ? ! $cau_dai : ( in_array( $tp['the_loai'], array( 'ca-dao', 'cau-do' ), true ) && count( $tp['loi'] ) <= 4 ) );
		$dong_hien = $cau_ten ? self::mot_dong( $tp['loi'] ) : $tp['loi'];
		$ca_cau    = Ve::khoa_cum( implode( ' ', $tp['loi'] ) );
		$ct_muc    = array();
		$ct_chung  = array();
		$ct_goc    = array();
		foreach ( $tp['chu_thich'] as $c ) {
			$t = trim( (string) ( $c['tu'] ?? '' ) );
			if ( '' === $t ) {
				$ct_chung[] = $c; // chú thích chung, không gắn từ: hàng "Chuyện quanh bài"
			} elseif ( $tuc_ngu && Ve::khoa_cum( $t ) === $ca_cau ) {
				$ct_goc[] = $c; // mục trùng cả câu tục ngữ (điển tích): hàng "Nguồn gốc", câu không bị tô
			} else {
				$ct_muc[] = $c;
			}
		}
		$tu    = array_values( array_filter( array_map( static fn( array $c ): string => trim( (string) $c['tu'] ), $ct_muc ) ) );
		$so_ct = Ve::danh_so_chu_thich( self::cac_dong( $tp, $dong_hien ), $tu );
		// Câu là tiêu đề: từ khó được tô cả trong câu tiêu đề; ca dao, câu đố từ 4 dòng trở xuống: lời là tiêu đề (D-119)
		$tieu_de = $cau_ten
			? str_replace( array( '<div', '</div>' ), array( '<span', '</span>' ), Ve::loi( $dong_hien, 'proverb', $tu, array( 'max' => '48', 'so_ct' => $so_ct ) ) )
			: ( $ngan
				? str_replace( array( '<div', '</div>' ), array( '<span', '</span>' ), Ve::loi( $dong_hien, $kieu, $tu, array( 'nhom' => (string) $tp['ma'], 'so_ct' => $so_ct ) ) )
				: esc_html( $cau_dai && ! $tho ? (string) ( $tp['loi'][0] ?? $tp['tieu_de'] ) : $tp['tieu_de'] ) );
		$y = empty( $tp['y_nghia']['chu'] )
			? ( $sua ? '<div class="meaning trong" data-chi-sua><div class="meaning-head"><h2>Ý nghĩa</h2>' . $nut( 'y_nghia', 'Thêm ý nghĩa' ) . '</div><p class="small muted">Chưa có ý nghĩa' . $nhap . '.</p></div>' : self::chua_nghia( $bai_that ) )
			: Ve::y_nghia( $tp['y_nghia']['chu'], ! empty( $tp['y_nghia']['ai'] ), 'Ý nghĩa', $nut( 'y_nghia', 'Sửa' ), $tp['dinh_dang']['y_nghia'] ?? '' ) . $goc( 'Ý nghĩa', 'y_nghia' );
		// Hiểu bài ngay sau khung lời (D-119): tục ngữ một khối Nghĩa; thể khác Ý nghĩa hoặc một dòng "Chưa có giải nghĩa"
		$hieu = $cau_do ? '' : ( $tuc_ngu ? ( self::nghia_tuc_ngu( $tp, $sua ? self::sua_nghia( $tp, $nut, $goc, $nhap ) : '' ) ?: self::chua_nghia( $bai_that ) ) : $y );
		if ( $cau_do ) {
			// Câu đố: đáp án thu gọn sau nút "Hiện đáp án", tắt JavaScript vẫn mở được (D-087). Giải nghĩa của câu đố là "Giải
			// thích đáp án" (vì sao đáp án là như vậy), nằm trong khối ẩn sau đáp án — không lộ đáp án trước khi đoán (D-105)
			$giai = empty( $tp['y_nghia']['chu'] ) ? '' : Ve::y_nghia( $tp['y_nghia']['chu'], ! empty( $tp['y_nghia']['ai'] ), 'Giải thích đáp án', $nut( 'y_nghia', 'Sửa' ), $tp['dinh_dang']['y_nghia'] ?? '' ) . $goc( 'Giải thích đáp án', 'y_nghia' );
			$y    = ( $sua ? '<p class="row no-print">' . $nut( 'cau_hoi', 'Sửa câu hỏi' ) . $nut( 'goi_y', 'Sửa gợi ý' ) . $nut( 'dap_an', 'Sửa đáp án' ) . ( '' === $giai ? $nut( 'y_nghia', 'Thêm giải thích đáp án' ) : '' ) . '</p>' : '' )
				. ( empty( $tp['goi_y'] ) ? '' : '<details class="disclosure"><summary>Gợi ý</summary><div class="detail-body"><p>' . \Phongdao\MienDuLieu\DinhDang::hien( $tp['goi_y'], $tp['dinh_dang']['goi_y'] ?? '' ) . '</p></div></details>' )
				. ( empty( $tp['dap_an'] ) ? $giai : '<details class="answer"><summary>Hiện đáp án</summary><div class="detail-body" data-nosnippet><h2>Đáp án</h2><p>' . \Phongdao\MienDuLieu\DinhDang::hien( $tp['dap_an'], $tp['dinh_dang']['dap_an'] ?? '' ) . '</p>' . ( $tp['dap_an_them'] ?? '' ) . $giai . $anh . '</div></details>' )
				. ( $sua && '' === $giai ? '<p class="small muted" data-chi-sua>Chưa có giải thích đáp án' . $nhap . '.</p>' : '' );
		}
		// Vè: bối cảnh trên lời; bài dài, bài nhiều phần chia đoạn (tiêu đề, mục lục, "Đọc toàn bài")
		$bc       = $tp['boi_canh'] ?? null;
		$boi_canh = empty( $bc ) ? '' : '<div class="boi-canh"><h2 class="boi-canh-dau">Bối cảnh</h2>' . ( '' === $bc['chu'] ? '' : '<p>' . \Phongdao\MienDuLieu\DinhDang::hien( $bc['chu'], $tp['dinh_dang']['boi_canh'] ?? '' ) . '</p>' ) . ( '' === $bc['them'] ? '' : '<p class="small muted">' . esc_html( $bc['them'] ) . '</p>' ) . '</div>';
		$sua_ve   = $sua && 've' === $tp['the_loai']
			? '<p class="row no-print">' . $nut( 'boi_canh', empty( $sua['phan']['boi_canh'] ) ? 'Thêm bối cảnh' : 'Sửa bối cảnh' ) . $nut( 'doan', empty( $sua['phan']['doan'] ) ? 'Chia đoạn' : 'Sửa đoạn' ) . '</p>'
			: '';
		// Đồng dao (D-119): khối Cách chơi sau khung lời khi trò chơi của bài đã có cách chơi; nút "Cách chơi" cạnh Đọc cùng bé
		$cach_choi = self::cach_choi( $tp );
		$be        = 'dong-dao' === $tp['the_loai'] && ! $tho ? '<p class="row no-print"><button type="button" class="btn" data-action="kids" data-chi-js hidden>' . Ve::icon( 'kids' ) . 'Đọc cùng bé</button>'
			. ( '' === $cach_choi ? '' : '<a class="btn" href="#cach-choi">Cách chơi</a>' ) . '</p>' : '';
		$co_nghi = empty( $tp['co_nghi'] ) ? '' : '<div class="co-nghi"><p class="flag">Có thể không phải dân gian</p><details class="disclosure vi-sao" id="vi-sao"><summary>Vì sao?</summary><div class="detail-body"><p>' . esc_html( $tp['co_nghi'] ) . '</p>'
			. ( empty( $tp['co_nghi_nguon'] ) ? '' : '<p class="small">Nguồn: ' . ( empty( $tp['co_nghi_nguon']['url'] ) ? esc_html( $tp['co_nghi_nguon']['chu'] ) : '<a href="' . esc_url( $tp['co_nghi_nguon']['url'] ) . '" rel="nofollow">' . esc_html( $tp['co_nghi_nguon']['chu'] ) . '</a>' ) . '</p>' ) . '</div></details></div>';
		// Khung lời gọn: nhãn thể loại → tiêu đề (bài ngắn: chính lời) → lời → chú thích kiểu sách → một dòng nguồn
		[ $ct_sau_loi, $sau_doan ] = self::chu_thich_gon( $tp, $ct_muc, $so_ct, self::cac_dong( $tp, $dong_hien ) );
		$sua_ct  = '';
		if ( $sua ) {
			// Người biên soạn: từ không có trong lời thì nhắc kiểm chính tả (vẫn lưu được — chú thích chung)
			$thieu  = \Phongdao\MienDuLieu\TuKho::thieu( $tp['loi'], array_values( array_filter( array_column( $tp['chu_thich'], 'tu' ) ) ) );
			$sua_ct = '<p class="row no-print">' . $nut( 'chu_thich', $tp['chu_thich'] ? 'Sửa từ khó' : 'Thêm từ khó' ) . '</p>'
				. ( $tp['chu_thich'] ? '' : '<p class="small muted" data-chi-sua>Chưa có từ khó.</p>' )
				. ( $thieu ? '<p class="notice no-print" data-chi-sua role="note">' . esc_html( 'Không thấy từ ' . implode( ', ', array_map( static fn( string $t ): string => '«' . $t . '»', $thieu ) ) . ' trong lời, kiểm lại chính tả.' ) . '</p>' : '' )
				. $goc( 'Từ khó', 'chu_thich' );
		}
		$the_tho = in_array( (string) $tp['hinh_thuc'], array( 'Lục bát', 'Song thất lục bát' ), true ) ? ' · ' . $tp['hinh_thuc'] : '';
		$so_lieu = Ve::so_lieu( $tp['so_lieu'] );
		$y_truoc = 've' === $tp['the_loai'] && ! empty( $tp['y_nghia']['chu'] ); // vè: Bối cảnh, Ý nghĩa trước lời dài
		$loi_gon = $ngan ? '' : ( empty( $tp['doan'] )
			? Ve::loi( $dong_hien, $kieu, $tu, array( 'so_dong' => ! $cau_do && count( $tp['loi'] ) >= 8, 'nhom' => $tp['ma'], 'so_ct' => $so_ct ) )
			: Ve::loi_doan( $tp['doan'], $kieu, $tu, array( 'so_dong' => ! $cau_do, 'nhom' => $tp['ma'], 'bai' => $tp['ma'], 'so_ct' => $so_ct, 'sau_doan' => $sau_doan ) ) );
		$the_gon = '<section class="verse-card the-gon g-' . esc_attr( (string) $tp['the_loai'] ) . '" id="chu-thich" aria-label="Lời và chú thích">'
			. '<p class="the-gon-dau"><span class="the-gon-loai">' . esc_html( $tp['ten_the_loai'] . $the_tho ) . '</span>'
			. '<span class="row">' . $nut( 'loi', 'Sửa lời' ) . ( $tp['di_ban'] ? '<a class="the-gon-di-ban" href="#di-ban">' . count( $tp['di_ban'] ) . ' dị bản</a>' : '' ) . Ve::nut_nghe() . '</span></p>'
			// Bài có từ ngữ thô tục: tiêu đề trung tính ở ngoài khối che (h1), trong khung không in tên
			. ( $che ? '' : '<h1 class="read-title' . ( $ngan ? ' loi-ten' : '' ) . '">' . $tieu_de . '</h1>' ) . $co_nghi
			. ( '' === $so_lieu ? '' : '<p class="read-sub">' . $so_lieu . '</p>' )
			. $sua_ve . ( $y_truoc ? $boi_canh . '<div class="hieu-gon">' . $hieu . '</div>' : $boi_canh ) . $be . $loi_gon . $goc( 'Lời', 'loi' ) . self::ghi_cong( $tp['ghi_cong'] ?? array() )
			. ( $cau_do && ! empty( $tp['cau_hoi'] ) ? '<p class="riddle-q">' . esc_html( $tp['cau_hoi'] ) . '</p>' : '' )
			. ( $cau_do ? $y . self::do_tiep( $tp ) : '' )
			. ( '' === $ct_sau_loi . $sua_ct ? '' : '<div class="ct-khung">' . $ct_sau_loi . $sua_ct . '</div>' )
			. self::dong_nguon( $tp ) . ( $sua ? '<p class="row no-print sua-doi-chieu">' . $nut( 'doi-chieu', 'Đối chiếu sách' ) . '</p>' : '' ) . '</section>'
			. ( $y_truoc || '' === $hieu ? '' : '<div class="hieu-gon">' . $hieu . '</div>' ) . $cach_choi;
		$duong_dan = '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span>' . Ve::lien_ket( $tp['the_loai'], esc_html( $tp['ten_the_loai'] ) ) . '<span aria-hidden="true">/</span><span>' . esc_html( $che ? 'Bài có từ ngữ thô tục' : $tp['tieu_de'] ) . '</span></nav>';
		$bo_cuc    = '<div class="work-layout trang-gon"><div class="work-main">' . $the_gon . '</div>' . Ve::cong_cu( $tp, true ) . '<div class="work-rest">' . self::sau_gon( $tp, $kieu, $ct_goc, $ct_chung, $anh, $sua, $nut, $goc ) . '</div></div>';
		return ( $sua ? CheDoSua::dai( $sua ) : '' ) . $duong_dan
			. ( $che ? '<h1 class="read-title tho-tuc-ten">' . esc_html( ( $tp['ten_the_loai'] ?: 'Bài' ) . ' có từ ngữ thô tục' ) . '</h1>' : '' )
			. ( $tho ? Ve::che_tho_tuc( $bo_cuc, ! $che, 'tho-tuc-bai' ) : $bo_cuc )
			. ( $sua ? CheDoSua::tam( $sua, (string) $tp['the_loai'] ) . ( ( $bai_sua = get_post( (int) $sua['id'] ) ) ? CheDoSua::tam_phan_loai( $bai_sua, (string) $tp['the_loai'] ) : '' ) . CheDoSua::tam_doi_chieu( $sua ) : '' );
	}

	/** Chế độ sửa, tục ngữ: nút sửa các lớp nghĩa (Nghĩa bóng, Dùng khi chỉ khi bài đã có — D-118, D-119) và nguồn gốc từng phần. */
	private static function sua_nghia( array $tp, callable $nut, callable $goc, string $nhap ): string {
		$co = static fn( string $p ): bool => '' !== (string) ( $tp[ $p ] ?? '' );
		return '<p class="row no-print">' . $nut( 'y_nghia', empty( $tp['y_nghia']['chu'] ) ? 'Thêm ý nghĩa' : 'Sửa ý nghĩa' ) . $nut( 'nghia_den', $co( 'nghia_den' ) ? 'Sửa nghĩa đen' : 'Thêm nghĩa đen' )
			. ( $co( 'nghia_bong' ) ? $nut( 'nghia_bong', 'Sửa nghĩa bóng' ) : '' ) . ( $tp['dung_khi'] ? $nut( 'dung_khi', 'Sửa "Dùng khi"' ) : '' ) . '</p>'
			. ( '' === $nhap ? '' : '<p class="small muted" data-chi-sua>' . esc_html( ltrim( $nhap, ' ·' ) ) . '.</p>' )
			. $goc( 'Ý nghĩa', 'y_nghia' ) . $goc( 'Nghĩa đen', 'nghia_den' ) . $goc( 'Nghĩa bóng', 'nghia_bong' ) . $goc( 'Dùng khi', 'dung_khi' );
	}

	/**
	 * Phần sau khung lời (D-119): ảnh minh hoạ → hàng mở một dòng (Chuyện quanh bài / Nguồn gốc, Dị bản, Câu gần nghĩa · trái
	 * nghĩa, Hình ảnh trong bài) → Hiểu thêm (chỉ khi đã có bài của bạn đọc; câu đố không có phần Cách hiểu) → Đọc thêm (chủ
	 * đề, mô-típ, nơi lưu truyền khi có, tuyển tập, Đọc tiếp, hàng Ảnh để chia sẻ) → một lời mời góp theo thể loại. Chế độ sửa:
	 * hàng mở sẵn, phần trống vẫn hiện để thêm (chỉ người biên soạn thấy), cờ, phân loại, Lịch sử.
	 */
	private static function sau_gon( array $tp, string $kieu, array $ct_goc, array $ct_chung, string $anh, ?array $sua, callable $nut, callable $goc ): string {
		$cau_do  = 'cau-do' === $tp['the_loai'];
		$tuc_ngu = 'tuc-ngu' === $tp['the_loai'];
		$mo      = null !== $sua;
		$co_phan = static fn( string $p ): bool => ! empty( $sua['phan'][ $p ] );
		$hang_sua = static fn( string $tam, bool $co, string $ten = '', string $phan = '' ): string => $sua ? '<p class="row no-print">' . $nut( $tam, $co ? 'Sửa' : 'Thêm' ) . '</p>' . ( '' === $phan ? '' : $goc( $ten, $phan ) ) : '';
		// Chuyện quanh bài (tục ngữ: Nguồn gốc): phần riêng (D-119), rồi mục chú thích chưa chuyển sang (chung / trùng cả câu)
		$cq      = (array) ( $tp['chuyen_quanh'] ?? array() );
		$ten_cq  = $tuc_ngu ? 'Nguồn gốc' : 'Chuyện quanh bài';
		$cu_ct   = $tuc_ngu ? $ct_goc : $ct_chung;
		$sua_cq  = $hang_sua( 'chuyen_quanh', $co_phan( 'chuyen_quanh' ), $ten_cq, 'chuyen_quanh' )
			. ( $sua && $cu_ct ? '<p class="small muted" data-chi-sua>' . esc_html( count( $cu_ct ) . ' mục đang nằm ở Từ khó. Muốn chuyển sang ' . $ten_cq . ': mở "Sửa từ khó", đánh dấu "Chuyển sang ' . $ten_cq . '".' ) . '</p>' : '' );
		$hang    = self::hang_chu_thich( $ten_cq, $tuc_ngu ? 'nguon-goc' : 'chuyen-quanh-bai', array_merge( $cq, $cu_ct ), $sua_cq, $mo )
			. ( $tuc_ngu ? self::hang_chu_thich( 'Chuyện quanh bài', 'chuyen-quanh-bai', $ct_chung, '', $mo ) : '' );
		if ( $tp['di_ban'] || $sua ) {
			$hang .= self::hang( 'Dị bản', count( $tp['di_ban'] ), 'di-ban', $hang_sua( 'di_ban', $co_phan( 'di_ban' ), 'Dị bản', 'di_ban' ) . ( $tp['di_ban'] ? self::di_ban( $tp, $kieu ) : '<p class="small muted">Chưa có dị bản.</p>' ), $mo, ! $tp['di_ban'] );
		}
		// Tục ngữ: câu gần nghĩa · trái nghĩa người biên soạn đã chọn (D-119); máy không tự nối. Phần không ghi người làm nên
		// không có dòng nguồn gốc; chế độ sửa ghi số câu đã nối mà người đọc chưa thấy (chưa đăng, có từ ngữ thô tục)
		$gn    = (array) ( $tp['gan_nghia'] ?? array() );
		$gn_an = $sua ? count( (array) ( $sua['phan']['gan_nghia']['muc'] ?? array() ) ) - count( $gn ) : 0;
		if ( $tuc_ngu && ( $gn || $sua ) ) {
			$hang .= self::hang(
				self::ten_gan_nghia( $gn ),
				count( $gn ),
				'gan-nghia',
				$hang_sua( 'gan_nghia', $co_phan( 'gan_nghia' ) )
					. ( $gn_an > 0 ? '<p class="small muted" data-chi-sua>' . esc_html( $gn_an . ' câu đã nối chưa hiện cho người đọc (câu chưa đăng hoặc có từ ngữ thô tục).' ) . '</p>' : '' )
					. ( $gn ? self::gan_nghia( $gn ) : ( $gn_an > 0 ? '' : '<p class="small muted">Chưa nối câu nào. Người biên soạn chọn câu; máy không tự nối.</p>' ) ),
				$mo,
				! $gn
			);
		}
		$hinh = (array) ( $tp['hinh_anh'] ?? array() );
		if ( in_array( $tp['the_loai'], array( 'ca-dao', 'dong-dao' ), true ) && ( $hinh || $sua ) ) {
			$hang .= self::hang( 'Hình ảnh trong bài', count( $hinh ), 'hinh-anh', $hang_sua( 'hinh_anh', $co_phan( 'hinh_anh' ), 'Hình ảnh trong bài', 'hinh_anh' ) . ( $hinh ? self::hinh_anh( $tp ) : '<p class="small muted">Chưa có. Tuỳ chọn: mỗi dòng một cặp hình ảnh → điều được nói tới.</p>' ), $mo, ! $hinh );
		}
		$sua_anh = $sua ? '<p class="no-print" data-chi-sua><a class="btn" href="' . esc_url( \Phongdao\GiaoTiep\QuanTri\SuaTacPham::url( (int) $sua['id'] ) . '&phan=media#phan-media' ) . '">Chọn hoặc sửa ảnh minh hoạ trong quản trị</a></p>' : '';
		$sua_co  = $sua ? '<section class="block tint-dat no-print" data-chi-sua><div class="block-dau"><h2>Cờ “Có thể không phải dân gian”</h2>' . $nut( 'co_nghi', empty( $tp['co_nghi'] ) ? 'Gắn cờ' : 'Sửa' ) . '</div><p class="small muted">' . ( empty( $tp['co_nghi'] ) ? 'Chưa gắn cờ.' : esc_html( 'Đang gắn: ' . $tp['co_nghi'] ) ) . '</p></section>' : '';
		// Viên phân loại: chủ đề, mô-típ (D-119), nơi lưu truyền chỉ khi đã có (lời mời "Ở quê nói khác" nằm ở lời mời góp),
		// tuyển tập; chế độ sửa: Sửa phân loại, Đổi thể loại
		$nhan = '';
		foreach ( $tp['chu_de'] as $c ) {
			$nhan .= self::nhan_lien_ket( $c, 'chu-de', 'Chủ đề: ', 'tag-pill' );
		}
		foreach ( (array) ( $tp['mo_tip'] ?? array() ) as $c ) {
			$nhan .= self::nhan_lien_ket( $c, 'mo-tip', 'Mô-típ: ', 'tag-pill' );
		}
		$vung  = self::ten_vung( $tp );
		$nhan .= '' === $vung ? '' : '<span class="tag-pill plain">Nơi lưu truyền: ' . esc_html( $vung ) . '</span>';
		foreach ( $tp['tuyen_tap'] as $t ) {
			$nhan .= self::nhan_lien_ket( $t, 'tuyen-tap', 'Có trong tuyển tập: ', 'tag-pill plain' );
		}
		if ( $sua ) {
			$nhan .= '<span class="row no-print" data-chi-sua>' . $nut( 'phan-loai', 'Sửa phân loại' ) . ( current_user_can( 'cadao_duyet' ) ? $nut( 'the-loai', 'Đổi thể loại' ) : '' ) . '</span>';
		}
		[ $gui, $gui_ch, $bao_ch ] = self::bao_gui();
		$bai_that = ctype_digit( (string) $tp['ma'] );
		$ht       = $bai_that ? HieuThem::hien( (int) $tp['ma'], $tp, $bao_ch, array( 'gon' => true, 'cach_hieu' => ! $cau_do ) ) : '';
		$form_ch  = $bai_that && '' === $ht && ! $cau_do ? HieuThem::form_gon( (int) $tp['ma'], $bao_ch, HieuThem::danh( $tp ) ) : '';
		return ( $cau_do ? '' : $anh ) . $sua_anh
			. ( '' === $hang ? '' : '<div class="hang-nhom">' . $hang . '</div>' ) . $sua_co
			. $ht
			. '<section class="tang" id="doc-them" aria-labelledby="doc-them-tieu-de"><h2 class="tang-dau" id="doc-them-tieu-de">Đọc thêm</h2>'
			. ( '' === $nhan ? '' : '<div class="work-tags">' . $nhan . '</div>' )
			. ( $cau_do ? '' : self::doc_tiep_gon( $tp ) )
			. '<div class="hang-nhom">' . AnhChiaSe::hien( $tp, true ) . '</div></section>'
			. ( $bai_that ? GopTuLieu::moi_gon( (int) $tp['ma'], (string) $tp['the_loai'], HieuThem::danh( $tp ), 'da_gui' === $gui && ! $gui_ch, $form_ch, '' !== $ht ) : '' )
			. ( $sua ? CheDoSua::lich_su( $sua ) : '' );
	}

	/** Tên hàng câu gần nghĩa · trái nghĩa theo các câu đã nối. */
	private static function ten_gan_nghia( array $gn ): string {
		$loai = array_unique( array_column( $gn, 'quan_he' ) );
		return array( 'gan' ) === $loai ? 'Câu gần nghĩa' : ( array( 'trai' ) === $loai ? 'Câu trái nghĩa' : 'Câu gần nghĩa · trái nghĩa' );
	}

	/** Phần thân câu gần nghĩa · trái nghĩa: gần nghĩa trước, mỗi câu dẫn tới trang của câu đó. */
	private static function gan_nghia( array $gn ): string {
		usort( $gn, static fn( array $a, array $b ): int => (int) ( 'trai' === $a['quan_he'] ) <=> (int) ( 'trai' === $b['quan_he'] ) );
		$o = '';
		foreach ( $gn as $c ) {
			$o .= '<li><span class="gn-loai">' . ( 'trai' === $c['quan_he'] ? 'Trái nghĩa' : 'Gần nghĩa' ) . '</span><a href="' . esc_url( (string) $c['url'] ) . '">' . esc_html( (string) $c['cau'] ) . '</a></li>';
		}
		return '<ul class="gn-ds">' . $o . '</ul>';
	}

	/**
	 * Đọc tiếp gọn (D-119): tối đa 3 dòng — hai dòng đầu của bài, rồi thể loại · lý do. Không có bài gần thì một dòng mời
	 * xem các bài cùng thể loại, không lấp bằng thẻ "cùng thể loại".
	 */
	private static function doc_tiep_gon( array $tp ): string {
		$o = '';
		foreach ( array_slice( (array) ( $tp['doc_tiep'] ?? array() ), 0, \Phongdao\DichVu\DungTrang\DocTiep::TOI_DA ) as $c ) {
			$o .= '<li><a href="' . esc_url( (string) $c['url'] ) . '">' . esc_html( implode( ' / ', array_slice( (array) $c['dong'], 0, 2 ) ) ) . '</a>'
				. '<span class="dt-ly-do">' . esc_html( implode( ' · ', array_filter( array( (string) ( $c['ten_the_loai'] ?? '' ), (string) ( $c['ly_do'] ?? '' ) ), 'strlen' ) ) ) . '</span></li>';
		}
		$than = '' !== $o ? '<ul class="dt-ds">' . $o . '</ul>'
			: '<p class="dt-trong">Chưa có bài gần với bài này. ' . Ve::lien_ket( (string) $tp['the_loai'], esc_html( 'Xem các bài ' . mb_strtolower( (string) $tp['ten_the_loai'] ) . ' khác' ) ) . '</p>';
		return '<section class="doc-tiep-gon" id="doc-tiep" aria-labelledby="doc-tiep-tieu-de"><h3 id="doc-tiep-tieu-de">Đọc tiếp</h3>' . $than . '</section>';
	}

	/** Câu đố (D-119): "Đố tiếp" tới câu đố kế tiếp và "Cả bộ" (chủ đề đầu của câu, chưa có thì mọi câu đố) thay thẻ Đọc tiếp. */
	private static function do_tiep( array $tp ): string {
		$url = (string) ( $tp['do_tiep'] ?? '' );
		$cd  = $tp['chu_de'][0] ?? null;
		return '<p class="do-tiep no-print">' . ( '' === $url ? '' : '<a class="btn primary" href="' . esc_url( $url ) . '">Đố tiếp ' . Ve::icon( 'arrow' ) . '</a>' )
			. ( null === $cd ? Ve::lien_ket( 'cau-do', 'Cả bộ câu đố', 'btn' ) : self::nhan_lien_ket( $cd, 'chu-de', 'Cả bộ: ', 'btn' ) ) . '</p>';
	}

	/**
	 * Đồng dao: khối Cách chơi (D-119, docs/27 mục 6.4) từ trò chơi gắn với bài — tên trò chơi dẫn tới trang trò chơi,
	 * thông số (số người, độ tuổi, đồ dùng; thiếu ô nào bỏ ô đó), các bước đánh số, nguồn và giấy phép ngay dưới (cách chơi
	 * chép từ Wikipedia mang CC BY-SA nên luôn ghi tại chỗ). Trò chơi chưa có cách chơi thì không hiện. Máy không viết
	 * cách chơi; chữ hiện nguyên như người biên soạn đã lưu ở trò chơi.
	 */
	private static function cach_choi( array $tp ): string {
		$o = '';
		foreach ( (array) ( $tp['tro_choi'] ?? array() ) as $tc ) {
			$cc   = (array) ( $tc['cach_choi'] ?? array() );
			$buoc = array_values( array_filter( array_map( static fn( $b ): string => trim( (string) $b ), (array) ( $cc['buoc'] ?? array() ) ), 'strlen' ) );
			$so   = '';
			foreach ( array( 'so_nguoi' => 'Số người', 'do_tuoi' => 'Độ tuổi', 'do_dung' => 'Đồ dùng' ) as $k => $nhan ) {
				$v   = trim( (string) ( $cc[ $k ] ?? '' ) );
				$so .= '' === $v ? '' : '<div><dt>' . $nhan . '</dt><dd>' . esc_html( $v ) . '</dd></div>';
			}
			if ( ! $buoc && '' === $so ) {
				continue;
			}
			$ng    = (array) ( $cc['nguon'] ?? array() );
			$url   = (string) ( $ng['url'] ?? '' );
			$ten   = implode( ' — ', array_filter( array( trim( (string) ( $ng['ten_ho_so'] ?? '' ) ), trim( (string) ( $ng['ghi_chu'] ?? '' ) ) ), 'strlen' ) );
			$ten   = '' === $ten && '' !== $url ? (string) wp_parse_url( $url, PHP_URL_HOST ) : $ten;
			$gp    = trim( (string) ( $cc['giay_phep'] ?? '' ) );
			$nguon = '' === $ten ? '' : '<p class="small muted cc-nguon">Nguồn cách chơi: ' . ( '' === $url ? esc_html( $ten ) : '<a href="' . esc_url( $url ) . '" rel="nofollow">' . esc_html( $ten ) . '</a>' ) . ( '' === $gp ? '' : ' · ' . esc_html( $gp ) ) . '</p>';
			$o    .= '<p class="cc-ten">Trò chơi ' . ( '' === (string) $tc['url'] ? '<b>' . esc_html( (string) $tc['ten'] ) . '</b>' : '<a href="' . esc_url( (string) $tc['url'] ) . '">' . esc_html( (string) $tc['ten'] ) . '</a>' ) . '</p>'
				. ( '' === $so ? '' : '<dl class="cc-so">' . $so . '</dl>' )
				. ( $buoc ? '<ol class="steps cc-buoc">' . implode( '', array_map( static fn( string $b ): string => '<li>' . esc_html( $b ) . '</li>', $buoc ) ) . '</ol>' : '' )
				. $nguon;
		}
		return '' === $o ? '' : '<section class="cach-choi" id="cach-choi" aria-labelledby="cach-choi-tieu-de"><h2 id="cach-choi-tieu-de">Cách chơi</h2>' . $o . '</section>';
	}

	/** Hàng mở một dòng (D-119): tên và số mục bên trái, mũi tên bên phải; $mo: mở sẵn (chế độ sửa); $chi_sua: hàng trống. */
	private static function hang( string $ten, int $so, string $id, string $than, bool $mo = false, bool $chi_sua = false ): string {
		return '<details class="hang-mo" id="' . esc_attr( $id ) . '"' . ( $mo ? ' open' : '' ) . ( $chi_sua ? ' data-chi-sua' : '' ) . '><summary><span>' . esc_html( $ten ) . ( $so > 1 ? '<span class="hang-so"> · ' . $so . '</span>' : '' ) . '</span></summary><div class="detail-body">' . $than . '</div></details>';
	}

	/** Phần thân Dị bản: mỗi dị bản đặt cạnh bản tham chiếu, chữ khác gạch dưới, nơi · người kể · năm. */
	private static function di_ban( array $tp, string $kieu ): string {
		$o = '<p class="small muted">Các cách lưu truyền đặt cạnh Bản tham chiếu. Chữ gạch dưới là phần khác.</p>';
		foreach ( $tp['di_ban'] as $i => $d ) {
			$v = Ve::loi( $d['loi'], $kieu, array(), array( 'nhom' => 'di-ban' ) );
			foreach ( $d['khac'] as $k ) {
				$v = str_replace( '>' . esc_html( $k ), '><ins>' . esc_html( $k ) . '</ins>', str_replace( ' ' . esc_html( $k ), ' <ins>' . esc_html( $k ) . '</ins>', $v ) );
			}
			$noi = array_filter( array( $d['noi'], $d['nguoi_ke'], $d['nam'] ?? '' ) );
			$o  .= '<div class="variant"><span class="tag">' . sprintf( '%02d', $i + 1 ) . ' · Dị bản</span>' . $v . '<p class="small muted">' . ( $noi ? esc_html( implode( ' · ', $noi ) ) : 'Chưa ghi nơi, người kể hoặc năm.' ) . '</p>' . self::ghi_cong( $d['ghi_cong'] ?? array() ) . '</div>';
		}
		return $o;
	}

	private static function ghi_cong( array $ds ): string {
		$o = '';
		foreach ( $ds as $m ) {
			$s = '' !== $m['ten'] ? 'Bạn đọc ' . $m['ten'] . ' đóng góp.' : 'Bạn đọc đóng góp.';
			if ( '' !== $m['xuat_xu'] ) { $s .= ' Nguồn người góp cung cấp — chưa đối chiếu: ' . $m['xuat_xu']; }
			$o .= '<p class="small muted gop-ghi-cong">' . esc_html( $s ) . '</p>';
		}
		return $o;
	}

	/** Phần thân "Hình ảnh trong bài" (ca dao, đồng dao): hình ảnh → điều được nói tới. */
	private static function hinh_anh( array $tp ): string {
		$o = '';
		foreach ( (array) ( $tp['hinh_anh'] ?? array() ) as [ $anh, $noi ] ) {
			$o .= '<div class="hinh-anh-muc"><dt>' . esc_html( $anh ) . '</dt>' . ( '' === $noi ? '' : '<dd><span aria-hidden="true">→ </span>' . esc_html( $noi ) . '</dd>' ) . '</div>';
		}
		return '<dl class="hinh-anh">' . $o . '</dl>' . ( empty( $tp['hinh_anh_ai'] ) ? '' : '<p class="ai-note">Có hỗ trợ của AI, đã được người biên soạn duyệt.</p>' );
	}

	/** Tên các vùng lưu truyền, cách nhau bởi dấu phẩy ('' = chưa có). */
	private static function ten_vung( array $tp ): string {
		return implode( ', ', array_map( static fn( $v ): string => is_array( $v ) ? (string) $v[0] : (string) $v, $tp['vung'] ) );
	}

	/** @return array{0:string,1:bool,2:array} lời báo sau khi gửi: [gop, có phải cách hiểu, lời báo cho form cách hiểu] */
	private static function bao_gui(): array {
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc lời báo sau khi gửi
		$gui    = sanitize_key( wp_unslash( (string) ( $_GET['gop'] ?? '' ) ) );
		$gui_ch = 'cach_hieu' === ( $_GET['gop_loai'] ?? '' );
		$bao_ch = $gui_ch ? array( 'gui' => $gui, 'ma' => sanitize_key( wp_unslash( (string) ( $_GET['ma'] ?? '' ) ) ) ) : array();
		// phpcs:enable
		return array( $gui, $gui_ch, $bao_ch );
	}

	/**
	 * Tục ngữ câu ngắn hai dòng hiện một dòng (D-119): "Một chữ nên thầy, / Một ngày nên nghĩa" → "Một chữ nên thầy, một
	 * ngày nên nghĩa". Chỉ là cách hiện; lời đã lưu giữ nguyên. Vế sau viết thường chữ đầu khi tiếng thứ hai của vế viết
	 * thường (tránh hạ chữ của tên riêng như "Hà Nội"); vế trước chưa có dấu ngắt thì nối bằng dấu phẩy.
	 *
	 * @param string[] $loi
	 * @return string[]
	 */
	public static function mot_dong( array $loi ): array {
		$loi = array_values( array_filter( array_map( 'trim', $loi ), 'strlen' ) );
		if ( 2 !== count( $loi ) ) {
			return $loi;
		}
		[ $a, $b ] = $loi;
		$tieng     = preg_split( '/\s+/u', $b ) ?: array();
		$thu_hai   = (string) ( $tieng[1] ?? '' );
		if ( '' === $thu_hai || mb_strtolower( mb_substr( $thu_hai, 0, 1 ) ) === mb_substr( $thu_hai, 0, 1 ) ) {
			$b = mb_strtolower( mb_substr( $b, 0, 1 ) ) . mb_substr( $b, 1 );
		}
		return array( ( preg_match( '/[,;:–—-]$/u', $a ) ? $a : $a . ',' ) . ' ' . $b );
	}

	/**
	 * Mọi dòng đúng như sẽ hiện (bài chia đoạn: nối các khổ theo thứ tự) — để đánh số chú thích khớp với lời.
	 *
	 * @param string[] $dong_hien
	 * @return string[]
	 */
	private static function cac_dong( array $tp, array $dong_hien ): array {
		if ( empty( $tp['doan']['doan'] ) ) {
			return array_values( $dong_hien );
		}
		$ra = array();
		foreach ( $tp['doan']['doan'] as $d ) {
			foreach ( $d['kho'] as $kho ) {
				foreach ( $kho as $l ) {
					$ra[] = (string) $l;
				}
			}
		}
		return $ra;
	}

	/** Bài chưa có nghĩa (D-119): một dòng thay hộp mời lớn; lời mời góp chính ở cuối trang. */
	private static function chua_nghia( bool $co_form ): string {
		return '<p class="chua-nghia">Chưa có giải nghĩa' . ( $co_form ? ' · <a href="#cach-hieu-form" data-mo-cach-hieu>Góp cách hiểu</a>' : '.' ) . '</p>';
	}

	/**
	 * Tục ngữ: một khối Nghĩa ngay sau câu (D-119) — Nghĩa đen khi có, Ý nghĩa (gọi là Nghĩa bóng khi có Nghĩa đen mà bài
	 * chưa có Nghĩa bóng riêng), Nghĩa bóng và Dùng khi của bài cũ vẫn hiện. Không tự chuyển chữ giữa các phần.
	 */
	private static function nghia_tuc_ngu( array $tp, string $sua = '' ): string {
		$y  = (string) ( $tp['y_nghia']['chu'] ?? '' );
		$nd = (string) ( $tp['nghia_den'] ?? '' );
		$nb = (string) ( $tp['nghia_bong'] ?? '' );
		$dk = (array) ( $tp['dung_khi'] ?? array() );
		if ( '' === $y && '' === $nd && '' === $nb && ! $dk ) {
			return '' === $sua ? '' : '<section class="meaning nghia-gon trong" id="nghia" data-chi-sua aria-labelledby="nghia-tieu-de"><div class="meaning-head"><h2 id="nghia-tieu-de">Nghĩa</h2></div><p class="small muted">Chưa có nghĩa.</p>' . $sua . '</section>';
		}
		$dd   = (array) ( $tp['dinh_dang'] ?? array() );
		$dong = static fn( string $nhan, string $chu, string $ma ): string => '<p class="nghia-dong"><span class="nghia-nhan">' . $nhan . '</span> ' . \Phongdao\MienDuLieu\DinhDang::hien( $chu, $dd[ $ma ] ?? '' ) . '</p>';
		// Ý nghĩa chung đứng đầu (D-118); có Nghĩa đen mà chưa có Nghĩa bóng riêng thì Ý nghĩa là dòng Nghĩa bóng ngay sau
		$y_la_bong = '' !== $y && '' !== $nd && '' === $nb;
		$o         = '' === $y || $y_la_bong ? '' : '<p class="y-nghia-chu">' . \Phongdao\MienDuLieu\DinhDang::hien( $y, $dd['y_nghia'] ?? '' ) . '</p>';
		$o        .= '' === $nd ? '' : $dong( 'Nghĩa đen', $nd, 'nghia_den' );
		$o        .= $y_la_bong ? $dong( 'Nghĩa bóng', $y, 'y_nghia' ) : '';
		$o        .= '' === $nb ? '' : $dong( 'Nghĩa bóng', $nb, 'nghia_bong' );
		if ( $dk ) {
			$o .= '<div class="nghia-dong"><span class="nghia-nhan">Dùng khi</span><ul class="uses">' . implode( '', array_map( static fn( string $x ): string => '<li>' . esc_html( $x ) . '</li>', $dk ) ) . '</ul></div>';
		}
		return '<section class="meaning nghia-gon" id="nghia" aria-labelledby="nghia-tieu-de"><div class="meaning-head"><h2 id="nghia-tieu-de">Nghĩa</h2></div>' . $o
			. ( empty( $tp['y_nghia']['ai'] ) ? '' : '<p class="ai-note">Có hỗ trợ của AI, đã được người biên soạn duyệt.</p>' ) . $sua . '</section>';
	}

	/** Lời giải một mục chú thích: chữ, nguồn người viết điền (D-081; nguồn máy chép ẩn — D-068), liên kết Từ điển. */
	private static function giai_chu_thich( array $c ): string {
		$nguon = empty( $c['nguon'] ) ? '' : '<br><span class="small">Nguồn: ' . ( empty( $c['nguon']['url'] ) ? esc_html( $c['nguon']['chu'] ) : '<a href="' . esc_url( $c['nguon']['url'] ) . '" rel="nofollow">' . esc_html( $c['nguon']['chu'] ) . '</a>' ) . '</span>';
		$muc   = is_array( $c['muc'] ?? null ) ? '<br><a class="small" href="' . esc_url( $c['muc']['url'] ) . '">Xem trong Từ điển · ' . (int) $c['muc']['so_bai'] . ' bài</a>' . $c['muc']['them'] : '';
		return \Phongdao\MienDuLieu\DinhDang::hien( $c['giai'], $c['dinh_dang'] ?? '' ) . $nguon . $muc;
	}

	/** Một mục chú thích kiểu sách; $n > 0: mốc #ct-N và số (CSS vẽ từ data-so, chữ trong dt giữ nguyên cho ô chạm từ). */
	private static function mot_chu_thich( int $n, array $c ): string {
		return '<div class="term"' . ( $n ? ' id="ct-' . $n . '" data-so="' . $n . '"' : '' ) . '><dt>' . esc_html( '' !== $c['tu'] ? $c['tu'] : 'Chú thích chung' ) . '</dt><dd>' . self::giai_chu_thich( $c ) . '</dd></div>';
	}

	/** @param list<array{0:int,1:array}> $ds [số, mục]; $toi_da > 0: phần sau mục thứ $toi_da thu vào "Xem thêm N chú thích". */
	private static function ds_chu_thich( array $ds, int $toi_da ): string {
		$muc = array_map( static fn( array $x ): string => self::mot_chu_thich( (int) $x[0], $x[1] ), $ds );
		if ( $toi_da <= 0 || count( $muc ) <= $toi_da ) {
			return '<dl class="terms ct-ds">' . implode( '', $muc ) . '</dl>';
		}
		return '<dl class="terms ct-ds">' . implode( '', array_slice( $muc, 0, $toi_da ) ) . '</dl>'
			. '<details class="ct-con"><summary>' . esc_html( 'Xem thêm ' . ( count( $muc ) - $toi_da ) . ' chú thích' ) . '</summary><dl class="terms ct-ds">' . implode( '', array_slice( $muc, $toi_da ) ) . '</dl></details>';
	}

	/**
	 * Chú thích kiểu sách (D-119): xếp theo số (lần xuất hiện đầu trong lời), mục không thấy trong lời xếp cuối. Bài chia
	 * đoạn có từ TU_KHO_NHOM mục: chú thích nằm sau từng đoạn, thu gọn; mục không thấy trong lời thành "Chú thích chung".
	 *
	 * @param array<string,int> $so
	 * @param string[]          $cac_dong
	 * @return array{0:string,1:array<int,string>} [HTML sau lời, HTML sau từng đoạn theo số đoạn]
	 */
	private static function chu_thich_gon( array $tp, array $muc, array $so, array $cac_dong ): array {
		if ( ! $muc ) {
			return array( '', array() );
		}
		$ds = array();
		foreach ( $muc as $c ) {
			$ds[] = array( $so[ Ve::khoa_cum( trim( (string) $c['tu'] ) ) ] ?? 0, $c );
		}
		usort( $ds, static fn( array $a, array $b ): int => array( 0 === $a[0], $a[0] ) <=> array( 0 === $b[0], $b[0] ) );
		$doan = (array) ( $tp['doan']['doan'] ?? array() );
		if ( count( $doan ) < 2 || count( $ds ) < self::TU_KHO_NHOM ) {
			return array( self::ds_chu_thich( $ds, 4 ), array() );
		}
		$nhom = array();
		foreach ( $ds as $x ) {
			$dong = array_key_first( \Phongdao\MienDuLieu\TuKho::tim( $cac_dong, array( trim( (string) $x[1]['tu'] ) ) ) );
			$k    = 0;
			if ( null !== $dong ) {
				foreach ( $doan as $d ) {
					if ( $dong + 1 >= (int) $d['tu'] && $dong + 1 <= (int) $d['den'] ) {
						$k = (int) $d['so'];
						break;
					}
				}
			}
			$nhom[ $k ][] = $x;
		}
		$sau_doan = array();
		foreach ( $doan as $d ) {
			$k = (int) $d['so'];
			if ( ! empty( $nhom[ $k ] ) ) {
				$ten            = '' !== trim( (string) $d['tieu_de'] ) ? (string) $d['tieu_de'] : 'đoạn ' . $k;
				$sau_doan[ $k ] = '<details class="ct-doan"><summary>' . esc_html( 'Chú thích ' . $ten . ' · ' . count( $nhom[ $k ] ) ) . '</summary>' . self::ds_chu_thich( $nhom[ $k ], 0 ) . '</details>';
			}
		}
		$chung = empty( $nhom[0] ) ? '' : '<details class="ct-doan"><summary>' . esc_html( 'Chú thích chung · ' . count( $nhom[0] ) ) . '</summary>' . self::ds_chu_thich( $nhom[0], 0 ) . '</details>';
		return array( $chung, $sau_doan );
	}

	/**
	 * Hàng mở một dòng (D-119) "Chuyện quanh bài" (tục ngữ: "Nguồn gốc"): mục của phần Chuyện quanh bài (tên chuyện in đậm
	 * khi có), rồi chú thích không gắn từ hay điển tích của cả câu chưa được chuyển sang. Chế độ sửa: $sua là nút và nguồn gốc
	 * của phần; chưa có mục nào thì hàng chỉ người biên soạn thấy.
	 */
	private static function hang_chu_thich( string $ten, string $id, array $ds, string $sua = '', bool $mo = false ): string {
		if ( ! $ds && '' === $sua ) {
			return '';
		}
		$than = implode(
			'',
			array_map(
				static fn( array $c ): string => '<p>' . ( '' === trim( (string) ( $c['tieu_de'] ?? '' ) ) ? '' : '<strong>' . esc_html( trim( (string) $c['tieu_de'] ) ) . '.</strong> ' ) . self::giai_chu_thich( $c ) . '</p>',
				$ds
			)
		);
		return '<details class="hang-mo" id="' . esc_attr( $id ) . '"' . ( $mo ? ' open' : '' ) . ( $ds ? '' : ' data-chi-sua' ) . '><summary>' . esc_html( $ten ) . ( count( $ds ) > 1 ? '<span class="hang-so"> · ' . count( $ds ) . '</span>' : '' ) . '</summary><div class="detail-body">' . $sua . ( $ds ? $than : '<p class="small muted">Chưa có.</p>' ) . '</div></details>';
	}

	/**
	 * Một dòng nguồn ở chân khung lời (D-119) thay nhãn mức kiểm + ⓘ + khối "Lời này từ đâu?": trạng thái bên trái,
	 * "Nguồn" bên phải; mở ra là giải thích, bảng nguồn và lối gửi số trang. Giữ mốc #tu-lieu cho liên kết cũ (Câu hôm nay).
	 */
	private static function dong_nguon( array $tp ): string {
		$dc   = $tp['doi_chieu'];
		$co   = null !== $dc && '' !== ( $dc['sach'] ?? '' );
		$chu  = $co
			? '<b>Đã đối chiếu</b> · ' . esc_html( $dc['sach'] . ( empty( $dc['trang'] ) ? '' : ', tr. ' . $dc['trang'] ) )
			: '<b>Bản lưu truyền phổ biến</b> · chưa đối chiếu sách in';
		$giai = $co
			? 'Lời khớp với bản in trong sách, đúng trang đã ghi. Người biên soạn đối chiếu; máy không tự gắn nhãn này.'
			: 'Lời ghi theo cách người Việt thường đọc, hát hiện nay. Thư viện đang đối chiếu dần với sách in; khi xong, dòng này ghi tên sách và số trang.';
		$nut  = ( $co ? '' : Ve::lien_ket( 'gop', 'Có sách? Gửi số trang', 'btn compact' ) ) . Ve::lien_ket( 'phuong-phap', 'Cách biên soạn' );
		$bang = self::tu_lieu_dong( $tp, false, false );
		return '<details class="dong-nguon" id="tu-lieu"><summary>' . Ve::icon( $co ? 'check' : 'book' ) . '<span class="dong-nguon-chu">' . $chu . '</span><span class="dong-nguon-mo">Nguồn</span></summary>'
			. '<div class="dong-nguon-than"><p>' . $giai . '</p>' . ( '' === $bang ? '' : '<dl class="metadata">' . $bang . '</dl>' ) . '<p class="dong-nguon-nut">' . $nut . '</p></div></details>';
	}

	/** Chú thích từ số này trở lên ở bài chia đoạn thì nằm sau từng đoạn (D-114, D-119). */
	public const TU_KHO_NHOM = 8;

	/** Nhãn dẫn tới trang phân loại: $muc là tên (dữ liệu mẫu) hoặc [tên, đường dẫn]. */
	private static function nhan_lien_ket( string|array $muc, string $ma, string $dau, string $lop ): string {
		[ $ten, $url ] = is_array( $muc ) ? array( (string) $muc[0], (string) ( $muc[1] ?? '' ) ) : array( $muc, '' );
		return '' === $url
			? Ve::lien_ket( $ma, esc_html( $dau . $ten ), $lop )
			: '<a class="' . esc_attr( $lop ) . '" href="' . esc_url( $url ) . '">' . esc_html( $dau . $ten ) . '</a>';
	}

	/** Các dòng của bảng nguồn: Lời ($loi), Ý nghĩa, Chú thích, Chuyện quanh bài, Cách làm ($cach_lam). */
	private static function tu_lieu_dong( array $tp, bool $loi_dong = true, bool $cach_lam = true ): string {
		$loi  = null === $tp['doi_chieu']
			? 'Bản lưu truyền phổ biến, chưa đối chiếu với sách in. ' . Ve::lien_ket( 'gop', 'Có sách? Gửi số trang' )
			: 'Đã đối chiếu với ' . esc_html( $tp['doi_chieu']['sach'] ) . '.';
		$dong = $loi_dong ? '<dt>Lời</dt><dd>' . $loi . '</dd>' : '';
		if ( ! empty( $tp['y_nghia']['chu'] ) ) {
			$dong .= '<dt>' . ( 'cau-do' === $tp['the_loai'] ? 'Giải thích đáp án' : 'Ý nghĩa' ) . '</dt><dd>Người biên soạn của thư viện viết' . ( empty( $tp['y_nghia']['ai'] ) ? '' : '; có hỗ trợ của AI, đã duyệt' ) . '.</dd>';
		}
		if ( $tp['chu_thich'] ) {
			// Dữ liệu mẫu không ghi người làm: coi như máy chép (nguồn ẩn)
			$may  = count( array_filter( $tp['chu_thich'], static fn( array $c ): bool => $c['may'] ?? true ) );
			$viet = count( $tp['chu_thich'] ) - $may;
			$o    = array();
			if ( $may ) {
				$o[] = $may . ' mục chép từ nguồn tham khảo · nguồn đã ẩn theo ' . Ve::lien_ket( 'chinh-sach', 'Chính sách thu thập dữ liệu' );
			}
			if ( $viet ) {
				$o[] = $viet . ' mục người biên soạn viết' . ( array_filter( array_column( $tp['chu_thich'], 'nguon' ) ) ? ', nguồn ghi tại chỗ' : '' );
			}
			$dong .= '<dt>Chú thích</dt><dd>' . implode( '<br>', $o ) . '</dd>';
		}
		$cq = (array) ( $tp['chuyen_quanh'] ?? array() );
		if ( $cq ) {
			$may  = count( array_filter( $cq, static fn( array $c ): bool => ! empty( $c['may'] ) ) );
			$viet = count( $cq ) - $may;
			$o    = array();
			if ( $may ) {
				$o[] = $may . ' mục chép từ nguồn tham khảo · nguồn đã ẩn theo ' . Ve::lien_ket( 'chinh-sach', 'Chính sách thu thập dữ liệu' );
			}
			if ( $viet ) {
				$o[] = $viet . ' mục người biên soạn viết' . ( array_filter( array_column( $cq, 'nguon' ) ) ? ', nguồn ghi tại chỗ' : '' );
			}
			$dong .= '<dt>' . ( 'tuc-ngu' === $tp['the_loai'] ? 'Nguồn gốc' : 'Chuyện quanh bài' ) . '</dt><dd>' . implode( '<br>', $o ) . '</dd>';
		}
		return $dong . ( $cach_lam ? '<dt>Cách làm</dt><dd>' . Ve::lien_ket( 'phuong-phap', 'Cách chúng tôi biên soạn' ) . '</dd>' : '' );
	}
}
