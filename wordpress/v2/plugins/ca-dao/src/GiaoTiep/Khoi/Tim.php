<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\ChiMucTim\ChiMucTim;
use Phongdao\DichVu\ChiMucTim\TimDoc;
use Phongdao\MienDuLieu\TrichTim;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\ChiMuc;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Trang Tìm (docs/26 mục 8, bộ thiết kế R12): form gửi /tim/?q= (tắt JavaScript vẫn tìm được; có JavaScript thì
	 * kết quả hiện khi gõ, cùng HTML này qua REST). Trọn chữ/khác dấu/gõ dở tách phạm vi, giữ thứ tự liên quan và
	 * lọc thể loại trên toàn tập; chỉ nổi bật khi trọn tên/dòng lời duy nhất. Không ra kết quả → gợi ý sửa một chữ cái; kho có câu chứa cụm
 * này thì báo "đang chờ biên soạn" + nút Mong có câu này (sự kiện mong_co_cau).
 */
final class Tim {

	public const SO = 20;

	public static function trang( string $q, array $vao = array() ): string {
		return '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span><span>Tìm</span></nav>'
			. '<p class="eyebrow">Tra cứu</p><h1>Tìm câu bạn nhớ</h1>'
			. '<form id="tim-form" class="search-form" role="search" method="get" action="' . esc_url( Ve::url( 'tim' ) ) . '" data-tim><label class="sr-only" for="query">Câu hoặc từ cần tìm</label>'
			. '<input id="query" name="q" type="search" autocomplete="off" value="' . esc_attr( $q ) . '" placeholder="Gõ vài chữ, có dấu hoặc không dấu" aria-describedby="search-help">'
			. '<button type="submit" class="btn primary">' . Ve::icon( 'search' ) . 'Tìm</button></form>'
			. '<p class="small muted" id="search-help" style="margin-top:12px">Gõ câu bạn nhớ, gõ không dấu cũng được</p>'
			. '<div class="tim-phan-ket-qua">' . self::ket_qua( $q, $vao ) . '</div>';
	}

	/** Tìm đã gửi: trọn chữ; REST đang gõ: tiền tố. Mỗi phạm vi, thể loại đều có phân trang riêng. */
	public static function ket_qua( string $q, array $vao = array(), bool $dang_go = false ): string {
		$q = trim( mb_substr( $q, 0, 120 ) );
		$loc = TimDoc::loc( $vao );
		if ( '' === ChiMuc::chu( $q ) ) {
			return '<p id="search-status" class="small muted" aria-live="polite"></p><div id="search-results"><input type="hidden" form="tim-form" name="khop" value="' . esc_attr( $loc['khop'] ) . '"><input type="hidden" form="tim-form" name="loai" value="' . esc_attr( $loc['loai'] ) . '"><section class="section"><h2>Bạn có thể bắt đầu từ đây</h2>' . Ve::lien_ket( 'kho', 'Tất cả tác phẩm', 'btn' ) . '</section></div>';
		}
		if ( $dang_go ) { $loc['trang'] = 1; }
		$kq = TimDoc::tim( $q, $loc, $dang_go, self::SO );
		$loc['trang'] = $kq['trang'];
		$muc = self::muc_tu( $q, $loc['khop'], $dang_go );
		$tong = $kq['tong'];
		$bo = self::bo_loc( $q, $loc, $kq, $dang_go );
		if ( ! $kq['ds'] && ! $muc && ! array_sum( $kq['nhom'] ) ) {
			$sua = ChiMucTim::goi_y( $q );
			$kho = ChongTrung::kho_co( $q );
			return '<p id="search-status" class="small muted" aria-live="polite">0 kết quả</p><div id="search-results">'
				. $bo
				. ( $sua ? '<div class="notice">Có phải bạn tìm <a class="btn ghost compact" href="' . esc_url( add_query_arg( 'q', $sua, Ve::url( 'tim' ) ) ) . '">' . esc_html( $sua ) . '</a>?</div>' : '' )
				// Kho có câu chứa cụm này: báo đang chờ biên soạn (không hiện lời kho); nút chỉ hiện khi có JavaScript (doc.js)
				. ( $kho ? '<div class="notice cho-bien-soan"><p><strong>Câu này đang chờ biên soạn.</strong> Thư viện đang kiểm lời, chú thích trước khi đăng.</p><button type="button" class="btn compact" data-action="mong-co-cau" data-kho="' . $kho . '" hidden>Mong có câu này</button></div>' : '' )
				. '<div class="empty">' . Ve::icon( 'book' ) . '<h2>Chưa tìm thấy câu phù hợp</h2><p class="muted">Thử gõ ít chữ hơn — vài chữ liền nhau trong câu là đủ, có dấu hay không dấu đều được. Kho đang được bổ sung dần.</p>'
				. Ve::lien_ket( 'kho', 'Xem tất cả tác phẩm', 'btn' ) . ' ' . Ve::lien_ket( 'gop', 'Góp câu đang tìm', 'btn ghost' ) . '</div></div>';
		}
		$ids = array_column( $kq['ds'], 'id' );
		if ( $ids ) { _prime_post_caches( $ids, true, true ); }
		$phan = array(); $ca_cau = array();
		foreach ( $ids as $id ) {
			$phan[ $id ] = PhanBai::tat_ca( $id, KhaiBao::TAC_PHAM );
			if ( TrichTim::ca_cau( $phan[ $id ], get_the_title( $id ), $q ) ) { $ca_cau[] = $id; }
		}
		// Chỉ nổi bật khi xét hết tập khớp và có đúng một bài khớp tên/dòng lời; một từ luôn là danh sách thường.
		$noi = ! $dang_go && 'dung' === $loc['khop'] && 1 === $loc['trang'] && $tong <= self::SO && 1 === count( $ca_cau ) && $ca_cau[0] === ( $ids[0] ?? 0 ) ? $ca_cau[0] : 0;
		$dau = ''; $dong = '';
		$rong = $dang_go || 'dung' !== $loc['khop'] || ChiMuc::chu( $q ) === ChiMuc::khong_dau( ChiMuc::chu( $q ) );
		foreach ( $kq['ds'] as $x ) {
			$bai = get_post( $x['id'] );
			if ( ! $bai || ! ( $the = DocBai::the( $bai ) ) ) { continue; }
			$trich = TrichTim::trich( $phan[ $bai->ID ], $bai->post_title, $q, $rong, $dang_go || 'go_do' === $loc['khop'] );
			$the['tim_ten_html'] = TrichTim::danh_dau( $bai->post_title, $q, $rong, $dang_go || 'go_do' === $loc['khop'] );
			$the['tim_chu_html'] = $trich['html'];
			$the['ly_do'] = $x['roi'] ? $trich['nhan'] . ' · Các chữ không liền nhau' : $trich['nhan'];
			$html = DanhSach::the( $the );
			if ( $noi === $bai->ID ) {
				$html = str_replace( array( '<h3>', '</h3>' ), array( '<h2>', '</h2>' ), $html );
				$dau = '<section class="section result-primary"><p class="eyebrow">Khớp cả câu · ' . esc_html( $the['ten_the_loai'] ) . '</p>' . $html . '<a class="btn primary" href="' . esc_url( $the['url'] ) . '">Đọc bài</a></section>';
			} else { $dong .= $html; }
		}
		$o = $bo . $dau . ( $dong ? '<section class="section"><h2>Các bài phù hợp</h2><div class="grid two">' . $dong . '</div></section>' : '' );
		if ( ! $ids ) { $o .= '<p class="notice">Chưa có bài trong phạm vi này. Bạn có thể chọn kiểu khớp hoặc thể loại khác ở trên.</p>'; }
		if ( $muc ) { $o .= '<section class="section"><h2>Từ điển</h2><ul class="uses">' . implode( '', $muc ) . '</ul></section>'; }
		$so_trang = max( 1, (int) ceil( $tong / self::SO ) );
		if ( $so_trang > 1 ) {
			$o .= '<nav class="pagination" aria-label="Phân trang kết quả tìm">';
			$trang = array_unique( array_merge( array( 1, $so_trang ), range( max( 1, $loc['trang'] - 2 ), min( $so_trang, $loc['trang'] + 2 ) ) ) );
			sort( $trang ); $truoc = 0;
			foreach ( $trang as $n ) {
				if ( $n - $truoc > 1 ) { $o .= '<span aria-hidden="true">…</span>'; }
				$o .= $n === $loc['trang'] ? '<span class="btn primary" aria-current="page">' . $n . '</span>' : '<a class="btn" aria-label="Trang ' . $n . '" href="' . esc_url( self::url( $q, array_merge( $loc, array( 'trang' => $n ) ) ) ) . '">' . $n . '</a>';
				$truoc = $n;
			}
			$o .= '</nav>';
		}
		$hien = count( $ids );
		$dem = $tong > $hien ? $hien . ' trên ' . number_format_i18n( $tong ) . ' bài · Trang ' . $loc['trang'] . '/' . $so_trang : $hien . ' bài';
		return '<p id="search-status" class="small muted" aria-live="polite">' . esc_html( $dem ) . '</p><div id="search-results">' . $o . '</div>';
	}

	private static function url( string $q, array $loc ): string {
		return add_query_arg( array_merge( array( 'q' => $q ), $loc ), Ve::url( 'tim' ) );
	}

	/** D-113: một form GET chung; disclosure điều khiển nhóm bên cạnh, dùng được không JavaScript. */
	private static function bo_loc( string $q, array $loc, array $kq, bool $dang_go ): string {
		$nhom = array( 'dung' => 'Khớp trọn chữ', 'khong_dau' => 'Khác dấu', 'go_do' => 'Khớp đầu chữ' );
		$so_loc = (int) ( 'dung' !== $loc['khop'] ) + (int) ( '' !== $loc['loai'] );
		$tom = $nhom[ $loc['khop'] ] . ' · ' . ( TheLoai::DS[ $loc['loai'] ] ?? 'Mọi thể loại' );
		$o = '<div class="tim-loc" data-tim-loc><details class="tim-loc-mo"' . ( ! $kq['tong'] && ! $dang_go ? ' open' : '' ) . '><summary aria-controls="tim-loc-chon">Bộ lọc' . ( $so_loc ? ' (' . $so_loc . ')' : '' ) . '</summary></details>'
			. '<p class="tim-loc-tom-tat small muted">' . esc_html( $tom ) . '</p><div id="tim-loc-chon" class="tim-loc-chon" role="group" aria-label="Bộ lọc kết quả">'
			. '<div class="tim-loc-truong"><label for="tim-khop">Cách khớp</label><select id="tim-khop" form="tim-form" name="khop">';
		foreach ( $nhom as $k => $ten ) {
			$n = $kq['nhom'][ $k ];
			$o .= '<option value="' . $k . '"' . selected( $loc['khop'], $k, false ) . '>' . esc_html( $ten . ' (' . number_format_i18n( $n ) . ')' ) . '</option>';
		}
		$o .= '</select></div><div class="tim-loc-truong"><label for="tim-loai">Thể loại</label><select id="tim-loai" form="tim-form" name="loai">';
		foreach ( array( '' => 'Mọi thể loại' ) + TheLoai::DS as $k => $ten ) {
			$n = $kq['loai'][ $k ] ?? 0;
			$o .= '<option value="' . esc_attr( $k ) . '"' . selected( $loc['loai'], $k, false ) . '>' . esc_html( $ten . ( '' === $k ? '' : ' (' . number_format_i18n( $n ) . ')' ) ) . '</option>';
		}
		$o .= '</select></div><button type="submit" form="tim-form" class="btn primary">Áp dụng</button>'
			. ( $so_loc ? '<a class="tim-loc-dat-lai" href="' . esc_url( self::url( $q, array( 'khop' => 'dung', 'loai' => '', 'trang' => 1 ) ) ) . '">Đặt lại</a>' : '' );
		if ( ! $kq['tong'] && ! $dang_go ) {
			$goi = '';
			foreach ( $nhom as $k => $ten ) {
				if ( $kq['nhom'][ $k ] ) {
					$goi = '<a href="' . esc_url( self::url( $q, array_merge( $loc, array( 'khop' => $k, 'trang' => 1 ) ) ) ) . '">' . esc_html( $ten . ' (' . number_format_i18n( $kq['nhom'][ $k ] ) . ')' ) . '</a>';
					break;
				}
			}
			if ( ! $goi && array_sum( $kq['loai'] ) ) {
				$goi = '<a href="' . esc_url( self::url( $q, array_merge( $loc, array( 'loai' => '', 'trang' => 1 ) ) ) ) . '">Mọi thể loại (' . number_format_i18n( array_sum( $kq['loai'] ) ) . ')</a>';
			}
			if ( $goi ) { $o .= '<p class="tim-loc-goi-y small">Thử phạm vi còn bài: ' . $goi . '.</p>'; }
		}
		return $o . '</div></div>' . ( $dang_go ? '<p class="notice">Gợi ý khi đang gõ. Bấm Tìm để tìm trọn chữ.</p>' : '' );
	}

	/** Mục từ có tên khớp truy vấn (đệm một giờ). @return string[] HTML từng mục */
	private static function muc_tu( string $q, string $khop = 'dung', bool $dang_go = false ): array {
		$chu = ChiMuc::chu( $q );
		$kd = ChiMuc::khong_dau( ChiMuc::chu( $q ) );
		if ( mb_strlen( $kd ) < 2 ) {
			return array();
		}
		$ten = get_transient( 'phongdao_tim_muc_tu' );
		if ( ! is_array( $ten ) ) {
			$ten = array();
			$ds  = get_terms( array( 'taxonomy' => 'muc_tu', 'hide_empty' => true, 'number' => 0 ) );
			foreach ( is_array( $ds ) ? $ds : array() as $t ) {
				$ten[ ChiMuc::khong_dau( ChiMuc::chu( $t->name ) ) ][] = (int) $t->term_id;
			}
			set_transient( 'phongdao_tim_muc_tu', $ten, HOUR_IN_SECONDS );
		}
		$ra = array();
		foreach ( array_slice( $ten[ $kd ] ?? array(), 0, 5 ) as $id ) {
			$t = get_term( $id, 'muc_tu' );
			if ( $t instanceof \WP_Term ) {
				$khac_dau = $chu !== $kd && ChiMuc::chu( $t->name ) !== $chu;
				if ( ! $dang_go && ( 'go_do' === $khop || ( 'khong_dau' === $khop ) !== $khac_dau ) ) { continue; }
				$giai = wp_trim_words( wp_strip_all_tags( $t->description ), 20 );
				$ra[] = '<li><a href="' . esc_url( (string) get_term_link( $t ) ) . '"><strong>' . esc_html( $t->name ) . '</strong></a>' . ( '' === $giai ? '' : ' — ' . esc_html( $giai ) ) . ' <span class="small muted">(' . (int) $t->count . ' bài)</span></li>';
			}
		}
		return $ra;
	}
}
