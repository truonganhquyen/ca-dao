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
		$loc = TimDoc::loc( $vao );
		$an = '<input type="hidden" name="khop" value="' . esc_attr( $loc['khop'] ) . '"><input type="hidden" name="loai" value="' . esc_attr( $loc['loai'] ) . '">';
		return '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span><span>Tìm</span></nav>'
			. '<p class="eyebrow">Tra cứu</p><h1>Tìm câu bạn nhớ</h1>'
			. '<form class="search-form" role="search" method="get" action="' . esc_url( Ve::url( 'tim' ) ) . '" data-tim><label class="sr-only" for="query">Câu hoặc từ cần tìm</label>'
			. '<input id="query" name="q" type="search" autocomplete="off" value="' . esc_attr( $q ) . '" placeholder="Gõ vài chữ, có dấu hoặc không dấu" aria-describedby="search-help">'
			. $an . '<button type="submit" class="btn primary">' . Ve::icon( 'search' ) . 'Tìm</button></form>'
			. '<p class="small muted" id="search-help" style="margin-top:12px">Gõ câu bạn nhớ, gõ không dấu cũng được</p>'
			. self::ket_qua( $q, $vao );
	}

	/** Tìm đã gửi: trọn chữ; REST đang gõ: tiền tố. Mỗi phạm vi, thể loại đều có phân trang riêng. */
	public static function ket_qua( string $q, array $vao = array(), bool $dang_go = false ): string {
		$q = trim( mb_substr( $q, 0, 120 ) );
		if ( '' === ChiMuc::chu( $q ) ) {
			return '<p id="search-status" class="small muted" aria-live="polite"></p><div id="search-results"><section class="section"><h2>Bạn có thể bắt đầu từ đây</h2>' . Ve::lien_ket( 'kho', 'Tất cả tác phẩm', 'btn' ) . '</section></div>';
		}
		$loc = TimDoc::loc( $vao );
		if ( $dang_go ) { $loc['trang'] = 1; }
		$kq = TimDoc::tim( $q, $loc, $dang_go, self::SO );
		$loc['trang'] = $kq['trang'];
		$muc = self::muc_tu( $q, $loc['khop'], $dang_go );
		$tong = $kq['tong'];
		$nhom = array( 'dung' => 'Khớp trọn chữ', 'khong_dau' => 'Mở rộng không dấu', 'go_do' => 'Gợi ý theo đầu chữ' );
		$bo = $dang_go ? '<p class="notice">Gợi ý khi đang gõ. Bấm Tìm để tìm trọn chữ.</p>' : '<nav class="row" aria-label="Kiểu khớp">';
		if ( ! $dang_go ) {
			foreach ( $nhom as $k => $ten ) {
				if ( ! $kq['nhom'][ $k ] && $k !== $loc['khop'] ) { continue; }
				$bo .= '<a class="btn compact ' . ( $k === $loc['khop'] ? 'primary' : 'ghost' ) . '"' . ( $k === $loc['khop'] ? ' aria-current="true"' : '' ) . ' href="' . esc_url( self::url( $q, array_merge( $loc, array( 'khop' => $k, 'trang' => 1 ) ) ) ) . '">' . esc_html( $ten . ' (' . number_format_i18n( $kq['nhom'][ $k ] ) . ')' ) . '</a>';
			}
			$bo .= '</nav>';
		}
		if ( $kq['loai'] ) {
			$bo .= '<nav class="row" aria-label="Lọc thể loại kết quả">';
			foreach ( array( '' => 'Mọi thể loại' ) + TheLoai::DS as $k => $ten ) {
				$n = '' === $k ? array_sum( $kq['loai'] ) : ( $kq['loai'][ $k ] ?? 0 );
				if ( ! $n && $k !== $loc['loai'] ) { continue; }
				$bo .= '<a class="btn compact ' . ( $k === $loc['loai'] ? 'primary' : 'ghost' ) . '"' . ( $k === $loc['loai'] ? ' aria-current="true"' : '' ) . ' href="' . esc_url( self::url( $q, array_merge( $loc, array( 'loai' => $k, 'trang' => 1 ) ) ) ) . '">' . esc_html( $ten . ' (' . number_format_i18n( $n ) . ')' ) . '</a>';
			}
			$bo .= '</nav>';
		}
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
