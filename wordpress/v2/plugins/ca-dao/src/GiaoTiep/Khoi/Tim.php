<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\ChiMucTim\ChiMucTim;
use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\ChiMuc;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Trang Tìm (docs/26 mục 8, bộ thiết kế R12): form gửi /tim/?q= (tắt JavaScript vẫn tìm được; có JavaScript thì
 * kết quả hiện khi gõ, cùng HTML này qua REST). Khớp cả cụm ở bài đầu → thẻ lớn; còn lại nhóm theo thể loại;
 * mục từ; bài có đủ chữ nhưng rời rạc ở nhóm riêng. Không ra kết quả → gợi ý sửa một chữ cái.
 */
final class Tim {

	public const SO = 20;

	public static function trang( string $q ): string {
		return '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span><span>Tìm</span></nav>'
			. '<p class="eyebrow">Tra cứu</p><h1>Tìm câu bạn nhớ</h1>'
			. '<form class="search-form" role="search" method="get" action="' . esc_url( Ve::url( 'tim' ) ) . '" data-tim><label class="sr-only" for="query">Câu hoặc từ cần tìm</label>'
			. '<input id="query" name="q" type="search" autocomplete="off" value="' . esc_attr( $q ) . '" placeholder="Gõ vài chữ, có dấu hoặc không dấu" aria-describedby="search-help">'
			. '<button type="submit" class="btn primary">' . Ve::icon( 'search' ) . 'Tìm</button></form>'
			. '<p class="small muted" id="search-help" style="margin-top:12px">Gõ câu bạn nhớ, gõ không dấu cũng được</p>'
			. self::ket_qua( $q );
	}

	/** Trạng thái + kết quả (REST trả đúng phần này). */
	public static function ket_qua( string $q ): string {
		$q = trim( mb_substr( $q, 0, 120 ) );
		if ( '' === ChiMuc::khong_dau( ChiMuc::chu( $q ) ) ) {
			return '<p id="search-status" class="small muted" aria-live="polite"></p><div id="search-results">'
				. '<section class="section"><div class="section-heading"><h2>Bạn có thể bắt đầu từ đây</h2></div><div class="row">' . Ve::lien_ket( 'kho', 'Tất cả tác phẩm', 'chip' ) . Ve::lien_ket( 'tuc-ngu', 'Tục ngữ – Thành ngữ', 'chip' ) . Ve::lien_ket( 'ca-dao', 'Ca dao', 'chip' ) . '</div></section></div>';
		}
		$kq   = ChiMucTim::tim( $q, self::SO );
		$muc  = self::muc_tu( $q );
		$tong = $kq['tong'];
		if ( ! $kq['ds'] && ! $muc ) {
			$sua = ChiMucTim::goi_y( $q );
			return '<p id="search-status" class="small muted" aria-live="polite">0 kết quả</p><div id="search-results">'
				. ( $sua ? '<div class="notice">Có phải bạn tìm <a class="btn ghost compact" href="' . esc_url( add_query_arg( 'q', rawurlencode( $sua ), Ve::url( 'tim' ) ) ) . '">' . esc_html( $sua ) . '</a>?</div>' : '' )
				. '<div class="empty">' . Ve::icon( 'book' ) . '<h2>Chưa tìm thấy câu phù hợp</h2><p class="muted">Thử gõ ít chữ hơn — vài chữ liền nhau trong câu là đủ, có dấu hay không dấu đều được. Kho đang được bổ sung dần.</p>'
				. Ve::lien_ket( 'kho', 'Xem tất cả tác phẩm', 'btn' ) . ' ' . Ve::lien_ket( 'gop', 'Góp câu đang tìm', 'btn ghost' ) . '</div></div>';
		}

		$dau    = '';
		$nhom   = array();
		$roi    = '';
		foreach ( $kq['ds'] as $i => $x ) {
			$bai = get_post( $x['id'] );
			if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type ) {
				continue;
			}
			if ( 0 === $i && $x['lien'] && ( $tp = DocBai::tac_pham( $bai ) ) ) {
				$dau = '<section class="section result-primary"><p class="eyebrow">Khớp cả câu · ' . esc_html( $tp['ten_the_loai'] ) . '</p><h2><a href="' . esc_url( $tp['url'] ) . '">' . esc_html( $tp['tieu_de'] ) . '</a></h2>'
					. Ve::loi( array_slice( $tp['loi'], 0, 4 ), $tp['kieu'], array(), array( 'nhom' => 'tim-dau' ) )
					. ( empty( $tp['y_nghia']['chu'] ) ? '' : '<p>' . esc_html( $tp['y_nghia']['chu'] ) . '</p>' )
					. '<a class="btn primary" href="' . esc_url( $tp['url'] ) . '">Đọc bài ' . Ve::icon( 'arrow' ) . '</a></section>';
				continue;
			}
			$the = DocBai::the( $bai );
			if ( ! $the ) {
				continue;
			}
			$the['dong'] = self::dong_khop( $bai, $q, $the['dong'] );
			if ( $x['roi'] ) {
				$roi .= DanhSach::the( $the );
			} else {
				$nhom[ $the['the_loai'] ][] = DanhSach::the( $the );
			}
		}
		$o = $dau;
		foreach ( array_keys( TheLoai::DS ) as $ma ) {
			if ( ! empty( $nhom[ $ma ] ) ) {
				$o .= '<section class="section"><h2>' . esc_html( TheLoai::DS[ $ma ] ) . ' <span class="count">' . count( $nhom[ $ma ] ) . ' kết quả</span></h2><div class="grid two">' . implode( '', $nhom[ $ma ] ) . '</div></section>';
			}
		}
		if ( $muc ) {
			$o .= '<section class="section"><h2>Từ điển <span class="count">' . count( $muc ) . ' kết quả</span></h2><ul class="uses">' . implode( '', $muc ) . '</ul></section>';
		}
		if ( '' !== $roi ) {
			$o .= '<section class="section"><h2>Có các chữ này nhưng không liền nhau</h2><div class="grid two">' . $roi . '</div></section>';
		}
		$hien = count( $kq['ds'] );
		$dem  = $tong > $hien ? $hien . ' trên ' . $tong . ' bài — gõ thêm chữ để thu hẹp' : $hien . ' bài';
		return '<p id="search-status" class="small muted" aria-live="polite">' . esc_html( $dem ) . '</p><div id="search-results">' . $o . '</div>';
	}

	/** Hai dòng cho thẻ: dòng đầu, và dòng có chữ khớp nếu nằm giữa bài. */
	private static function dong_khop( \WP_Post $bai, string $q, array $hai_dong ): array {
		$kd = ChiMuc::khong_dau( ChiMuc::chu( $q ) );
		foreach ( \Phongdao\KhoLuu\PhanBai::lay( $bai->ID, 'loi' )['dong'] ?? array() as $i => $d ) {
			if ( $i >= 2 && '' !== $d && str_contains( ' ' . ChiMuc::khong_dau( ChiMuc::chu( $d ) ) . ' ', ' ' . $kd ) ) {
				return array( $hai_dong[0] ?? '', '… ' . $d );
			}
		}
		return $hai_dong;
	}

	/** Mục từ có tên khớp truy vấn (đệm một giờ). @return string[] HTML từng mục */
	private static function muc_tu( string $q ): array {
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
				$giai = wp_trim_words( wp_strip_all_tags( $t->description ), 20 );
				$ra[] = '<li><a href="' . esc_url( (string) get_term_link( $t ) ) . '"><strong>' . esc_html( $t->name ) . '</strong></a>' . ( '' === $giai ? '' : ' — ' . esc_html( $giai ) ) . ' <span class="small muted">(' . (int) $t->count . ' bài)</span></li>';
			}
		}
		return $ra;
	}
}
