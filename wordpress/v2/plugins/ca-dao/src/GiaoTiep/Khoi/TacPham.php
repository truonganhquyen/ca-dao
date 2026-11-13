<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\GiaoTiep\BienTap\CheDoSua;

defined( 'ABSPATH' ) || exit;

/**
 * Trang tác phẩm (docs/27 mục 6, bộ thiết kế R05–R09): đầu trang căn giữa → thẻ lời (lời, nhãn mức kiểm, ý nghĩa)
 * + công cụ đọc → từ khó, dị bản, (tục ngữ: giải nghĩa chi tiết, dùng khi, câu gần nghĩa) → chủ đề / vùng / tuyển tập
 * → Lời này từ đâu? → Ảnh để chia sẻ. Khối không có dữ liệu thì không hiện, kể cả tiêu đề (docs/27 mục 1).
 * → Đọc tiếp (3–5 bài gần, kèm lý do) → Ảnh để chia sẻ → Bạn muốn góp gì?, Cảm nghĩ. Đồng dao: nút Đọc cùng bé trên lời;
 * Trình chiếu ở cột công cụ đọc (doc.js — nút chỉ hiện khi có JavaScript).
 */
final class TacPham {

	/**
	 * @param array<string,mixed>      $tp  xem DichVu\DungTrang\DuLieuMau
	 * @param array<string,mixed>|null $sua chế độ sửa (GiaoTiep\BienTap\CheDoSua::ngu_canh) — chỉ khi người xem có quyền
	 */
	public static function hien( array $tp, ?array $sua = null ): string {
		$tp['url'] = $tp['url'] ?? ( get_permalink() ?: home_url( '/' ) );
		$tu        = array_values( array_filter( array_column( $tp['chu_thich'], 'tu' ) ) );
		$tuc_ngu   = 'proverb' === $tp['kieu'];
		$cau_do    = 'cau-do' === $tp['the_loai'];
		// Tục ngữ dài (từ 3 dòng, hoặc hơn khoảng 60 chữ cái): hiện như ca dao — tên là dòng đầu, lời đủ trong khung lời;
		// câu ngắn giữ kiểu "câu là tiêu đề" (D-105)
		$cau_dai   = $tuc_ngu && ( count( $tp['loi'] ) >= 3 || mb_strlen( (string) preg_replace( '/[^\p{L}]/u', '', implode( '', $tp['loi'] ) ) ) > 60 );
		$cau_ten   = $tuc_ngu && ! $cau_dai;
		$anh = (string) ( $tp['anh_minh_hoa_html'] ?? '' );
		$kieu      = $cau_dai ? '' : $tp['kieu'];
		$nut       = static fn( string $tam, string $nhan ): string => $sua ? CheDoSua::nut( $tam, $nhan ) : '';
		$goc       = static fn( string $nhan, string $phan ): string => $sua ? CheDoSua::goc( $nhan, $sua['phan'][ $phan ] ?? null, $sua ) : '';
		$nhan      = array( array( trim( $tp['ten_the_loai'] . ( $tp['hinh_thuc'] ? ' · ' . $tp['hinh_thuc'] : '' ) ) ) );
		if ( $tp['di_ban'] ) {
			$nhan[] = array( 'Bản tham chiếu · ' . count( $tp['di_ban'] ) . ' dị bản', true, '#di-ban' );
		}
		// Câu là tiêu đề: từ khó được tô cả trong câu tiêu đề
		$tieu_de = $cau_ten
			? str_replace( array( '<div', '</div>' ), array( '<span', '</span>' ), Ve::loi( $tp['loi'], 'proverb', $tu, array( 'max' => '48' ) ) )
			: esc_html( $cau_dai ? (string) ( $tp['loi'][0] ?? $tp['tieu_de'] ) : $tp['tieu_de'] );
		$y = empty( $tp['y_nghia']['chu'] )
			// Tục ngữ: chuẩn là khối Nghĩa (đen, bóng, dùng khi) — bài mới không có ô Ý nghĩa riêng; bài cũ có Ý nghĩa vẫn hiện
			? ( $sua && $tuc_ngu ? '' : ( $sua ? '<div class="meaning trong" data-chi-sua><div class="meaning-head"><h2>Ý nghĩa</h2>' . $nut( 'y_nghia', 'Thêm ý nghĩa' ) . '</div><p class="small muted">Chưa có ý nghĩa' . ( $sua['nhap'] ? ' · có ' . count( $sua['nhap'] ) . ' nháp chờ duyệt trong Hộp việc' : '' ) . '.</p></div>'
				: ( $tuc_ngu && ! empty( $tp['nghia_bong'] ) ? '' : self::moi_cach_hieu( ctype_digit( (string) $tp['ma'] ) ) ) ) )
			: Ve::y_nghia( $tp['y_nghia']['chu'], ! empty( $tp['y_nghia']['ai'] ), 'Ý nghĩa', $nut( 'y_nghia', 'Sửa' ), $tp['dinh_dang']['y_nghia'] ?? '' ) . $goc( 'Ý nghĩa', 'y_nghia' );
		if ( $cau_do ) {
			// Câu đố: đáp án thu gọn sau nút "Hiện đáp án", tắt JavaScript vẫn mở được (D-087). Giải nghĩa của câu đố là "Giải
			// thích đáp án" (vì sao đáp án là như vậy), nằm trong khối ẩn sau đáp án — không lộ đáp án trước khi đoán (D-105)
			$giai = empty( $tp['y_nghia']['chu'] ) ? '' : Ve::y_nghia( $tp['y_nghia']['chu'], ! empty( $tp['y_nghia']['ai'] ), 'Giải thích đáp án', $nut( 'y_nghia', 'Sửa' ), $tp['dinh_dang']['y_nghia'] ?? '' ) . $goc( 'Giải thích đáp án', 'y_nghia' );
			$y    = ( $sua ? '<p class="row no-print">' . $nut( 'cau_hoi', 'Sửa câu hỏi' ) . $nut( 'goi_y', 'Sửa gợi ý' ) . $nut( 'dap_an', 'Sửa đáp án' ) . ( '' === $giai ? $nut( 'y_nghia', 'Thêm giải thích đáp án' ) : '' ) . '</p>' : '' )
				. ( empty( $tp['goi_y'] ) ? '' : '<details class="disclosure"><summary>Gợi ý</summary><div class="detail-body"><p>' . \Phongdao\MienDuLieu\DinhDang::hien( $tp['goi_y'], $tp['dinh_dang']['goi_y'] ?? '' ) . '</p></div></details>' )
				. ( empty( $tp['dap_an'] ) ? $giai : '<details class="answer"><summary>Hiện đáp án</summary><div class="detail-body"><h2>Đáp án</h2><p>' . \Phongdao\MienDuLieu\DinhDang::hien( $tp['dap_an'], $tp['dinh_dang']['dap_an'] ?? '' ) . '</p>' . ( $tp['dap_an_them'] ?? '' ) . $giai . $anh . '</div></details>' )
				. ( $sua && '' === $giai ? '<p class="small muted" data-chi-sua>Chưa có giải thích đáp án' . ( $sua['nhap'] ? ' · có ' . count( $sua['nhap'] ) . ' nháp chờ duyệt trong Hộp việc' : '' ) . '.</p>' : '' );
		}
		$muc_kiem = $sua ? '<div class="level-sua">' . Ve::muc_kiem( $tp['doi_chieu'] ) . $nut( 'doi-chieu', 'Đối chiếu sách' ) . '</div>' : Ve::muc_kiem( $tp['doi_chieu'] );
		// Vè: bối cảnh trên lời; bài dài, bài nhiều phần chia đoạn (tiêu đề, mục lục, "Đọc toàn bài")
		$bc       = $tp['boi_canh'] ?? null;
		$boi_canh = empty( $bc ) ? '' : '<div class="boi-canh">' . ( '' === $bc['chu'] ? '' : '<p>' . \Phongdao\MienDuLieu\DinhDang::hien( $bc['chu'], $tp['dinh_dang']['boi_canh'] ?? '' ) . '</p>' ) . ( '' === $bc['them'] ? '' : '<p class="small muted">' . esc_html( $bc['them'] ) . '</p>' ) . '</div>';
		$sua_ve   = $sua && 've' === $tp['the_loai']
			? '<p class="row no-print">' . $nut( 'boi_canh', empty( $sua['phan']['boi_canh'] ) ? 'Thêm bối cảnh' : 'Sửa bối cảnh' ) . $nut( 'doan', empty( $sua['phan']['doan'] ) ? 'Chia đoạn' : 'Sửa đoạn' ) . '</p>'
			: '';
		$be       = 'dong-dao' === $tp['the_loai'] ? '<p class="row no-print"><button type="button" class="btn" data-action="kids" data-chi-js hidden>' . Ve::icon( 'kids' ) . 'Đọc cùng bé</button></p>' : '';
		$than_loi = $cau_ten
			? $goc( 'Lời', 'loi' ) . $muc_kiem . $y
			: $sua_ve . $boi_canh . $be
				. ( empty( $tp['doan'] )
					? Ve::loi( $tp['loi'], $kieu, $tu, array( 'so_dong' => ! $cau_do, 'nhom' => $tp['ma'] ) )
					: Ve::loi_doan( $tp['doan'], $kieu, $tu, array( 'so_dong' => ! $cau_do, 'nhom' => $tp['ma'], 'bai' => $tp['ma'] ) ) )
				. ( $cau_do && ! empty( $tp['cau_hoi'] ) ? '<p class="riddle-q">' . esc_html( $tp['cau_hoi'] ) . '</p>' : '' )
				. $goc( 'Lời', 'loi' ) . $muc_kiem . $y;

		$sau = '';
		// Tầng Đọc (D-105): lời → Ý nghĩa (tục ngữ: khối Nghĩa — Nghĩa đen · Nghĩa bóng, hiện to nhất · Dùng khi) → Từ khó → Dị bản
		if ( $tuc_ngu && ( '' !== $tp['nghia_den'] || '' !== (string) ( $tp['nghia_bong'] ?? '' ) || $tp['dung_khi'] || $sua ) ) {
			$o = '';
			foreach ( array( 'nghia_den' => 'Nghĩa đen', 'nghia_bong' => 'Nghĩa bóng' ) as $ma => $ten ) {
				$chu = (string) ( $tp[ $ma ] ?? '' );
				if ( '' !== $chu || $sua ) {
					$o .= '<div class="' . ( 'nghia_bong' === $ma ? 'nghia-bong' : 'nghia-den' ) . '"><h3>' . $ten . '</h3>' . ( '' === $chu ? '<p class="small muted">Chưa có.</p>' : '<p>' . \Phongdao\MienDuLieu\DinhDang::hien( $chu, $tp['dinh_dang'][ $ma ] ?? '' ) . '</p>' ) . $nut( $ma, ( '' === $chu ? 'Thêm ' : 'Sửa ' ) . mb_strtolower( $ten ) ) . '</div>';
				}
			}
			if ( $tp['dung_khi'] || $sua ) {
				$o .= '<div class="dung-khi"><h3>Dùng khi</h3>' . ( $tp['dung_khi'] ? '<ul class="uses">' . implode( '', array_map( static fn( string $x ): string => '<li>' . esc_html( $x ) . '</li>', $tp['dung_khi'] ) ) . '</ul>' : '<p class="small muted">Chưa có.</p>' ) . $nut( 'dung_khi', $tp['dung_khi'] ? 'Sửa "Dùng khi"' : 'Thêm "Dùng khi"' ) . '</div>';
			}
			$sau .= Ve::khoi( 'Nghĩa', '<div class="nghia-tn">' . $o . '</div>', 'tint-luc', 'nghia' );
		}
		$sau .= $cau_do ? '' : $anh;
		if ( $sua ) {
			$sau .= '<p class="no-print" data-chi-sua><a class="btn" href="' . esc_url( \Phongdao\GiaoTiep\QuanTri\SuaTacPham::url( (int) $sua['id'] ) . '&phan=media#phan-media' ) . '">Chọn hoặc sửa ảnh minh hoạ trong quản trị</a></p>';
		}
		// Ca dao, đồng dao: khối tuỳ chọn "Hình ảnh trong bài" (hình ảnh → điều được nói tới), ngay sau Ý nghĩa
		$hinh = (array) ( $tp['hinh_anh'] ?? array() );
		if ( in_array( $tp['the_loai'], array( 'ca-dao', 'dong-dao' ), true ) && ( $hinh || $sua ) ) {
			$o = '';
			foreach ( $hinh as [ $anh, $noi ] ) {
				$o .= '<div class="hinh-anh-muc"><dt>' . esc_html( $anh ) . '</dt>' . ( '' === $noi ? '' : '<dd><span aria-hidden="true">→ </span>' . esc_html( $noi ) . '</dd>' ) . '</div>';
			}
			$sau .= $hinh
				? Ve::khoi( 'Hình ảnh trong bài', '<dl class="hinh-anh">' . $o . '</dl>' . ( empty( $tp['hinh_anh_ai'] ) ? '' : '<p class="ai-note">Có hỗ trợ của AI, đã được người biên soạn duyệt.</p>' ) . $goc( 'Hình ảnh trong bài', 'hinh_anh' ), 'tint-luc', 'hinh-anh', $nut( 'hinh_anh', 'Sửa' ) )
				: '<section class="block tint-luc" id="hinh-anh" data-chi-sua><div class="block-dau"><h2>Hình ảnh trong bài</h2>' . $nut( 'hinh_anh', 'Thêm' ) . '</div><p class="small muted">Chưa có. Tuỳ chọn: mỗi dòng một cặp hình ảnh → điều được nói tới.</p></section>';
		}
		if ( $sua ) {
			$sau .= '<section class="block tint-dat no-print" data-chi-sua><div class="block-dau"><h2>Cờ “Có thể không phải dân gian”</h2>' . $nut( 'co_nghi', empty( $tp['co_nghi'] ) ? 'Gắn cờ' : 'Sửa' ) . '</div><p class="small muted">' . ( empty( $tp['co_nghi'] ) ? 'Chưa gắn cờ.' : esc_html( 'Đang gắn: ' . $tp['co_nghi'] ) ) . '</p></section>';
			if ( ! $tp['chu_thich'] ) {
				$sau .= '<section class="block tint-vang" id="chu-thich" data-chi-sua><div class="block-dau"><h2>Từ khó</h2>' . $nut( 'chu_thich', 'Thêm' ) . '</div><p class="small muted">Chưa có từ khó.</p></section>';
			}
		}
		if ( $tp['chu_thich'] ) {
			$o = '';
			foreach ( $tp['chu_thich'] as $c ) {
				// Nguồn người biên soạn điền thì hiện (D-081); nguồn của chú thích máy chép ẩn theo chính sách (D-068)
				$nguon = empty( $c['nguon'] ) ? '' : '<br><span class="small">Nguồn: ' . ( empty( $c['nguon']['url'] ) ? esc_html( $c['nguon']['chu'] ) : '<a href="' . esc_url( $c['nguon']['url'] ) . '" rel="nofollow">' . esc_html( $c['nguon']['chu'] ) . '</a>' ) . '</span>';
				$muc   = is_array( $c['muc'] ?? null ) ? '<br><a class="small" href="' . esc_url( $c['muc']['url'] ) . '">Xem trong Từ điển · ' . (int) $c['muc']['so_bai'] . ' bài</a>' . $c['muc']['them'] : '';
				$o    .= '<div class="term"><dt>' . esc_html( '' !== $c['tu'] ? $c['tu'] : 'Chú thích chung' ) . '</dt><dd>' . \Phongdao\MienDuLieu\DinhDang::hien( $c['giai'], $c['dinh_dang'] ?? '' ) . $nguon . $muc . '</dd></div>';
			}
			// Người biên soạn: từ không có trong lời thì nhắc kiểm chính tả (vẫn lưu được — chú thích chung)
			$thieu_tu = $sua ? \Phongdao\MienDuLieu\TuKho::thieu( $tp['loi'], $tu ) : array();
			$nhac_tu  = $thieu_tu ? '<p class="notice no-print" data-chi-sua role="note">' . esc_html( 'Không thấy từ ' . implode( ', ', array_map( static fn( string $t ): string => '«' . $t . '»', $thieu_tu ) ) . ' trong lời, kiểm lại chính tả.' ) . '</p>' : '';
			$sau .= Ve::khoi( 'Từ khó', $nhac_tu . '<dl class="terms">' . $o . '</dl>' . $goc( 'Từ khó', 'chu_thich' ), 'tint-vang', 'chu-thich', $nut( 'chu_thich', 'Sửa' ) );
		}
		if ( $sua && ! $tp['di_ban'] ) {
			$sau .= '<section class="block tint-tim" id="di-ban" data-chi-sua><div class="block-dau"><h2>Dị bản</h2>' . $nut( 'di_ban', 'Thêm' ) . '</div><p class="small muted">Chưa có dị bản.</p></section>';
		}
		if ( $tp['di_ban'] ) {
			$o = '<p class="small muted">Các cách lưu truyền đặt cạnh Bản tham chiếu. Chữ gạch dưới là phần khác.</p>';
			foreach ( $tp['di_ban'] as $i => $d ) {
				$v = Ve::loi( $d['loi'], $kieu, array(), array( 'nhom' => 'di-ban' ) );
				foreach ( $d['khac'] as $k ) {
					$v = str_replace( '>' . esc_html( $k ), '><ins>' . esc_html( $k ) . '</ins>', str_replace( ' ' . esc_html( $k ), ' <ins>' . esc_html( $k ) . '</ins>', $v ) );
				}
				$noi = array_filter( array( $d['noi'], $d['nguoi_ke'], $d['nam'] ?? '' ) );
				$o  .= '<div class="variant"><span class="tag">' . sprintf( '%02d', $i + 1 ) . ' · Dị bản</span>' . $v . '<p class="small muted">' . ( $noi ? esc_html( implode( ' · ', $noi ) ) : 'Chưa ghi nơi, người kể hoặc năm.' ) . '</p></div>';
			}
			$sau .= Ve::khoi_gon( 'Dị bản · ' . count( $tp['di_ban'] ), ( $sua ? '<p class="row no-print">' . $nut( 'di_ban', 'Sửa dị bản' ) . '</p>' . $goc( 'Dị bản', 'di_ban' ) : '' ) . $o, 'di-ban', 'tim' );
		}
		$nhan_cuoi = '';
		foreach ( $tp['chu_de'] as $c ) {
			$nhan_cuoi .= self::nhan_lien_ket( $c, 'chu-de', 'Chủ đề: ', 'tag-pill' );
		}
		$vung       = implode( ', ', array_map( static fn( $v ): string => is_array( $v ) ? (string) $v[0] : (string) $v, $tp['vung'] ) );
		// Chưa có nơi lưu truyền: mời người đọc góp "Ở quê nói khác" thay chữ "chưa ghi nhận" (D-105)
		$nhan_cuoi .= '' !== $vung
			? '<span class="tag-pill plain">Nơi lưu truyền: ' . esc_html( $vung ) . '</span>'
			: ( ctype_digit( (string) $tp['ma'] )
				? '<span class="tag-pill plain">Nơi lưu truyền: <a href="' . esc_url( add_query_arg( array( 'loai' => 'di_ban', 'bai' => (int) $tp['ma'] ), Ve::url( 'gop' ) ) . '#gop-form' ) . '">Ở quê bạn có câu này không? Góp</a></span>'
				: '<span class="tag-pill plain">Nơi lưu truyền: chưa ghi nhận</span>' );
		if ( $sua ) {
			$nhan_cuoi .= '<span class="row no-print" data-chi-sua>' . $nut( 'phan-loai', 'Sửa phân loại' ) . ( current_user_can( 'cadao_duyet' ) ? $nut( 'the-loai', 'Đổi thể loại' ) : '' ) . '</span>';
		}
		foreach ( $tp['tuyen_tap'] as $t ) {
			$nhan_cuoi .= self::nhan_lien_ket( $t, 'tuyen-tap', 'Có trong tuyển tập: ', 'tag-pill plain' );
		}
		// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc lời báo sau khi gửi
		$gui       = sanitize_key( wp_unslash( (string) ( $_GET['gop'] ?? '' ) ) );
		$gui_ch    = 'cach_hieu' === ( $_GET['gop_loai'] ?? '' );
		$bao_ch    = $gui_ch ? array( 'gui' => $gui, 'ma' => sanitize_key( wp_unslash( (string) ( $_GET['ma'] ?? '' ) ) ) ) : array();
		// phpcs:enable
		$bai_that  = ctype_digit( (string) $tp['ma'] );
		// Tầng Hiểu thêm: Cách hiểu của bạn đọc · Bài cảm nhận · Cảm nghĩ (bài thật; dữ liệu mẫu không có form gửi)
		$sau .= $bai_that ? HieuThem::hien( (int) $tp['ma'], $tp, $bao_ch ) : '';
		// Tầng Đọc thêm: phân loại, có trong tuyển tập, Đọc tiếp (cùng mô-típ, chủ đề…), Lời này từ đâu?, ảnh để chia sẻ
		$sau .= '<section class="tang" id="doc-them" aria-labelledby="doc-them-tieu-de"><h2 class="tang-dau" id="doc-them-tieu-de">Đọc thêm</h2>'
			. '<div class="work-tags">' . $nhan_cuoi . '</div>'
			. ( empty( $tp['doc_tiep'] ) ? '' : Ve::khoi( 'Đọc tiếp', '<div class="grid two doc-tiep">' . implode( '', array_map( array( DanhSach::class, 'the' ), $tp['doc_tiep'] ) ) . '</div>', '', 'doc-tiep' ) )
			. self::tu_lieu( $tp ) . AnhChiaSe::hien( $tp ) . '</section>'
			. ( $bai_that ? GopTuLieu::cuoi_bai( (int) $tp['ma'], 'da_gui' === $gui && ! $gui_ch ) : '' )
			. ( $sua ? CheDoSua::lich_su( $sua ) : '' );

		return ( $sua ? CheDoSua::dai( $sua ) : '' )
			. '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span>' . Ve::lien_ket( $tp['the_loai'], esc_html( $tp['ten_the_loai'] ) ) . '<span aria-hidden="true">/</span><span>' . esc_html( $tp['tieu_de'] ) . '</span></nav>'
			. '<header class="work-head">' . Ve::nhan_dau( $nhan ) . '<h1 class="read-title">' . $tieu_de . '</h1>'
			// Cờ (D-105): "Có thể không phải dân gian", kèm "Vì sao?" mở lý do và nguồn người viết đã ghi
			. ( empty( $tp['co_nghi'] ) ? '' : '<div class="co-nghi"><p class="flag">Có thể không phải dân gian</p><details class="disclosure vi-sao" id="vi-sao"><summary>Vì sao?</summary><div class="detail-body"><p>' . esc_html( $tp['co_nghi'] ) . '</p>'
				. ( empty( $tp['co_nghi_nguon'] ) ? '' : '<p class="small">Nguồn: ' . ( empty( $tp['co_nghi_nguon']['url'] ) ? esc_html( $tp['co_nghi_nguon']['chu'] ) : '<a href="' . esc_url( $tp['co_nghi_nguon']['url'] ) . '" rel="nofollow">' . esc_html( $tp['co_nghi_nguon']['chu'] ) . '</a>' ) . '</p>' ) . '</div></details></div>' )
			. '<p class="read-sub">' . Ve::so_lieu( $tp['so_lieu'] ) . '</p></header>'
			. '<div class="work-layout"><div class="work-main">' . Ve::the_loi( $than_loi, $tp['url'], $cau_ten ? 'Ý nghĩa' : 'Lời', 'Lời và ý nghĩa', $nut( 'loi', 'Sửa lời' ) ) . '</div>' . Ve::cong_cu( $tp ) . '<div class="work-rest">' . $sau . '</div></div>'
			. ( $sua ? CheDoSua::tam( $sua, (string) $tp['the_loai'] ) . ( ( $bai_sua = get_post( (int) $sua['id'] ) ) ? CheDoSua::tam_phan_loai( $bai_sua, (string) $tp['the_loai'] ) : '' ) . CheDoSua::tam_doi_chieu( $sua ) : '' );
	}

	/** Bài chưa có Ý nghĩa: mời bạn đọc chia sẻ cách hiểu (form nhanh ở tầng Hiểu thêm; D-105). */
	private static function moi_cach_hieu( bool $co_form ): string {
		return '<div class="notice moi-cach-hieu"><p>Câu này chưa có lời giải nghĩa. Bạn hiểu câu này thế nào? Viết vài dòng để người đọc sau hiểu rõ hơn.</p>'
			. ( $co_form ? '<p><a class="btn" href="#cach-hieu-form" data-mo-cach-hieu>' . Ve::icon( 'pen' ) . 'Chia sẻ cách hiểu</a></p>' : '' ) . '</div>';
	}

	/** Nhãn dẫn tới trang phân loại: $muc là tên (dữ liệu mẫu) hoặc [tên, đường dẫn]. */
	private static function nhan_lien_ket( string|array $muc, string $ma, string $dau, string $lop ): string {
		[ $ten, $url ] = is_array( $muc ) ? array( (string) $muc[0], (string) ( $muc[1] ?? '' ) ) : array( $muc, '' );
		return '' === $url
			? Ve::lien_ket( $ma, esc_html( $dau . $ten ), $lop )
			: '<a class="' . esc_attr( $lop ) . '" href="' . esc_url( $url ) . '">' . esc_html( $dau . $ten ) . '</a>';
	}

	/** "Lời này từ đâu?" (D-085, D-093): nguồn đã ẩn không có trong HTML gửi người đọc. */
	private static function tu_lieu( array $tp ): string {
		$loi = null === $tp['doi_chieu']
			? 'Bản lưu truyền phổ biến, chưa đối chiếu với sách in. ' . Ve::lien_ket( 'gop', 'Có sách? Gửi số trang' )
			: 'Đã đối chiếu với ' . esc_html( $tp['doi_chieu']['sach'] ) . '.';
		$dong = '<dt>Lời</dt><dd>' . $loi . '</dd>';
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
		$dong .= '<dt>Cách làm</dt><dd>' . Ve::lien_ket( 'phuong-phap', 'Cách chúng tôi biên soạn' ) . '</dd>';
		return Ve::khoi_gon( Ve::icon( 'info' ) . 'Lời này từ đâu?', '<dl class="metadata">' . $dong . '</dl>', 'tu-lieu', 'dat' );
	}
}
