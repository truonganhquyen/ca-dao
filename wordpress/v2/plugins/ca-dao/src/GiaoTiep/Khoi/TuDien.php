<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\MienDuLieu\BoDau;

defined( 'ABSPATH' ) || exit;

/**
 * Trang Từ điển /tu-dien/ (docs/27 mục 7; bộ thiết kế R13). Tìm mục từ (?q=), theo chữ cái (?chu=), mặc định các mục
 * từ hay gặp trong lời. Mọi thứ chạy bằng form GET — tắt JavaScript vẫn tra được. Trang mục từ là trang phân loại
 * muc_tu (/tu-dien/<tên>/, khối danh-sach).
 */
final class TuDien {

	public const CHU_CAI = array( 'A', 'Ă', 'Â', 'B', 'C', 'D', 'Đ', 'E', 'Ê', 'G', 'H', 'I', 'K', 'L', 'M', 'N', 'O', 'Ô', 'Ơ', 'P', 'Q', 'R', 'S', 'T', 'U', 'Ư', 'V', 'X', 'Y' );

	private const SO_HAY_GAP = 30;

	private const SO_TIM = 60;

	public static function hien( string $q, string $chu ): string {
		$q   = trim( $q );
		$chu = in_array( $chu, self::CHU_CAI, true ) ? $chu : '';
		$h   = '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span><span>Từ điển</span></nav>'
			. '<p class="eyebrow">Tra cứu</p><h1>Hiểu thêm một chữ.</h1>'
			. '<form class="search-form" role="search" method="get" action="' . esc_url( Ve::url( 'tu-dien' ) ) . '"><label class="sr-only" for="tu-dien-q">Tìm mục từ</label>'
			. '<input id="tu-dien-q" type="search" name="q" value="' . esc_attr( $q ) . '" placeholder="Tìm một từ hoặc địa danh"><button type="submit" class="btn primary">' . Ve::icon( 'search' ) . 'Tìm</button></form>'
			. '<section class="section" aria-labelledby="tu-dien-chu"><h2 id="tu-dien-chu">Theo chữ cái</h2><form class="alpha" method="get" action="' . esc_url( Ve::url( 'tu-dien' ) ) . '" aria-label="Chữ cái tiếng Việt">';
		foreach ( self::CHU_CAI as $c ) {
			$h .= '<button type="submit" name="chu" value="' . esc_attr( $c ) . '" aria-pressed="' . ( $c === $chu ? 'true' : 'false' ) . '">' . esc_html( $c ) . '</button>';
		}
		$h .= '</form></section>';

		if ( '' !== $q ) {
			$tim  = BoDau::cho_tim( $q );
			$ds   = array_values( array_filter( self::tat_ca(), static fn( \WP_Term $t ): bool => str_contains( BoDau::cho_tim( $t->name ), $tim ) || str_contains( BoDau::cho_tim( $t->description ), $tim ) ) );
			usort( $ds, static fn( $a, $b ) => ( (int) ! str_starts_with( BoDau::cho_tim( $a->name ), $tim ) ) <=> ( (int) ! str_starts_with( BoDau::cho_tim( $b->name ), $tim ) ) ?: strcmp( $a->name, $b->name ) );
			$tieu = 'Kết quả cho “' . $q . '”';
			$ds   = array_slice( $ds, 0, self::SO_TIM );
		} elseif ( '' !== $chu ) {
			$ds   = array_values( array_filter( self::tat_ca(), static fn( \WP_Term $t ): bool => self::chu_dau( $t->name ) === $chu ) );
			$tieu = 'Bắt đầu bằng ' . $chu;
		} else {
			$ds = self::tat_ca();
			usort( $ds, static fn( $a, $b ) => $b->count <=> $a->count ?: strcmp( $a->name, $b->name ) );
			$ds   = array_slice( $ds, 0, self::SO_HAY_GAP );
			$tieu = 'Hay gặp trong lời';
		}

		$h .= '<section class="section" aria-labelledby="tu-dien-kq"><h2 id="tu-dien-kq">' . esc_html( $tieu ) . '</h2>';
		if ( ! $ds ) {
			return $h . '<div class="empty"><h3>Chưa có mục từ phù hợp</h3><p>Thử một chữ cái khác, hoặc tìm theo lời ở trang Tìm.</p>' . Ve::lien_ket( 'tim', 'Sang trang Tìm', 'btn primary' ) . '</div></section>';
		}
		$h .= '<p class="small muted">' . count( $ds ) . ' mục từ</p>';
		foreach ( $ds as $t ) {
			$giai = trim( wp_strip_all_tags( (string) $t->description ) );
			$giai = mb_strlen( $giai ) > 140 ? rtrim( mb_substr( $giai, 0, 139 ) ) . '…' : $giai;
			$url  = get_term_link( $t );
			$url  = is_wp_error( $url ) ? '' : $url;
			$h   .= '<div class="list-row"><div><h3><a href="' . esc_url( $url ) . '">' . esc_html( $t->name ) . '</a></h3>'
				. ( '' === $giai ? '' : '<p class="muted">' . esc_html( $giai ) . '</p>' ) . '<p class="small muted">' . (int) $t->count . ' bài có từ này</p></div>'
				. '<a class="btn ghost compact" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( 'Xem mục từ ' . $t->name ) . '">Xem ' . Ve::icon( 'arrow' ) . '</a></div>';
		}
		return $h . '</section>';
	}

	/** Chữ cái đầu theo bảng chữ tiếng Việt: bỏ dấu thanh, giữ ă â ê ô ơ ư đ. */
	public static function chu_dau( string $ten ): string {
		$c = mb_substr( trim( $ten ), 0, 1 );
		if ( class_exists( 'Normalizer' ) ) {
			$d = \Normalizer::normalize( $c, \Normalizer::FORM_D );
			$c = is_string( $d ) ? $d : $c;
		}
		$c = (string) preg_replace( '/[\x{0300}\x{0301}\x{0303}\x{0309}\x{0323}]/u', '', $c ); // huyền, sắc, ngã, hỏi, nặng
		return mb_strtoupper( BoDau::nfc( $c ) );
	}

	/** Mục từ có ít nhất một bài (đệm trong một lần dựng trang). @return list<\WP_Term> */
	private static function tat_ca(): array {
		static $ds = null;
		if ( null === $ds ) {
			$t  = get_terms(
				array(
					'taxonomy'   => 'muc_tu',
					'hide_empty' => true,
					'orderby'    => 'name',
				)
			);
			$ds = is_array( $t ) ? array_values( $t ) : array();
		}
		return $ds;
	}
}
