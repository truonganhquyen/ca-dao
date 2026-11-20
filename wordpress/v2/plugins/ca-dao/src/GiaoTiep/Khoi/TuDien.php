<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\MienDuLieu\BoDau;

defined( 'ABSPATH' ) || exit;

/**
 * Trang Từ điển /tu-dien/ (docs/27 mục 7; bộ thiết kế R13). Tìm mục từ (?q=), theo chữ cái (?chu=), mặc định các mục
 * từ hay gặp trong lời. Mọi thứ chạy bằng form GET — tắt JavaScript vẫn tra được. Trang mục từ là trang phân loại
 * muc_tu (/tu-dien/<tên>/, khối danh-sach).
 *
 * Kết quả tìm (D-125) xếp theo nhóm: đúng chữ → tên có chứa đúng chữ → cùng chữ khi bỏ dấu → tên gần giống → chỉ nhắc
 * trong lời giải. Mỗi mặt chữ một dòng; chữ có nhiều nghĩa (vd. ba mục "Mai") gom một dòng liệt kê từng nghĩa. Phân trang
 * 30 dòng (?trang=), có tổng số đúng.
 */
final class TuDien {

	public const CHU_CAI = array( 'A', 'Ă', 'Â', 'B', 'C', 'D', 'Đ', 'E', 'Ê', 'G', 'H', 'I', 'K', 'L', 'M', 'N', 'O', 'Ô', 'Ơ', 'P', 'Q', 'R', 'S', 'T', 'U', 'Ư', 'V', 'X', 'Y' );

	private const SO_HAY_GAP = 30;

	/** Số dòng (mặt chữ) mỗi trang kết quả tìm / theo chữ cái. */
	public const MOI_TRANG = 30;

	/** Nhóm kết quả tìm, theo thứ tự hiện. */
	private const NHOM = array(
		0 => 'Đúng chữ',
		1 => 'Có chứa chữ này',
		2 => 'Cùng chữ khi bỏ dấu',
		3 => 'Tên gần giống',
		4 => 'Nhắc tới trong lời giải',
	);

	/**
	 * Nhóm khớp của một mục từ với câu tìm; null = không khớp.
	 */
	public static function nhom( \WP_Term $t, string $q ): ?int {
		$q_dau = BoDau::chuan_hoa_co_dau( trim( $q ) );
		$q_tim = BoDau::cho_tim( $q );
		if ( '' === $q_tim ) {
			return null;
		}
		$ten_dau = BoDau::chuan_hoa_co_dau( trim( $t->name ) );
		$ten_tim = BoDau::cho_tim( $t->name );
		if ( $ten_dau === $q_dau ) {
			return 0;
		}
		// Trọn chữ, đúng dấu, nằm trong tên ("Hàng săng", "Đậu săng" khi tìm "săng")
		if ( preg_match( '/(?<![\p{L}\p{M}])' . preg_quote( $q_dau, '/' ) . '(?![\p{L}\p{M}])/u', $ten_dau ) ) {
			return 1;
		}
		if ( $ten_tim === $q_tim ) {
			return 2;
		}
		if ( str_contains( $ten_tim, $q_tim ) ) {
			return 3;
		}
		return str_contains( BoDau::cho_tim( wp_strip_all_tags( (string) $t->description ) ), $q_tim ) ? 4 : null;
	}

	public static function hien( string $q, string $chu, int $trang = 1 ): string {
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
			$nhom = array();
			foreach ( self::tat_ca() as $t ) {
				$n = self::nhom( $t, $q );
				if ( null !== $n ) {
					$nhom[ $n ][] = $t;
				}
			}
			ksort( $nhom );
			$dong = array();
			foreach ( $nhom as $n => $ds ) {
				foreach ( self::gom( $ds ) as $g ) {
					$dong[] = array( $n, $g );
				}
			}
			$tieu = 'Kết quả cho “' . $q . '”';
		} elseif ( '' !== $chu ) {
			$dong = array_map( static fn( array $g ): array => array( null, $g ), self::gom( array_values( array_filter( self::tat_ca(), static fn( \WP_Term $t ): bool => self::chu_dau( $t->name ) === $chu ) ) ) );
			$tieu = 'Bắt đầu bằng ' . $chu;
		} else {
			$ds = self::tat_ca();
			usort( $ds, static fn( $a, $b ) => $b->count <=> $a->count ?: strcmp( $a->name, $b->name ) );
			$dong = array_map( static fn( \WP_Term $t ): array => array( null, array( $t ) ), array_slice( $ds, 0, self::SO_HAY_GAP ) );
			$tieu = 'Hay gặp trong lời';
		}

		$h .= '<section class="section" aria-labelledby="tu-dien-kq"><h2 id="tu-dien-kq">' . esc_html( $tieu ) . '</h2>';
		if ( ! $dong ) {
			return $h . '<div class="empty"><h3>Chưa có mục từ phù hợp</h3><p>Thử một chữ cái khác, hoặc tìm theo lời ở trang Tìm.</p>' . Ve::lien_ket( 'tim', 'Sang trang Tìm', 'btn primary' ) . '</div></section>';
		}
		$tong_dong = count( $dong );
		$so_trang  = '' === $q && '' === $chu ? 1 : (int) ceil( $tong_dong / self::MOI_TRANG );
		$trang     = min( max( 1, $trang ), max( 1, $so_trang ) );
		$so_muc    = array_sum( array_map( static fn( array $d ): int => count( $d[1] ), $dong ) );
		$h        .= '<p class="small muted">' . esc_html( number_format_i18n( $so_muc ) ) . ' mục từ' . ( $so_muc !== $tong_dong ? ' · ' . esc_html( number_format_i18n( $tong_dong ) ) . ' chữ' : '' ) . ( $so_trang > 1 ? ' · trang ' . $trang . '/' . $so_trang : '' ) . '</p>';
		$nhom_dang = -1;
		foreach ( 1 === $so_trang ? $dong : array_slice( $dong, ( $trang - 1 ) * self::MOI_TRANG, self::MOI_TRANG ) as [ $n, $g ] ) {
			if ( null !== $n && $n !== $nhom_dang ) {
				$h        .= '<h3 class="eyebrow tu-dien-nhom">' . esc_html( self::NHOM[ $n ] ) . '</h3>';
				$nhom_dang = $n;
			}
			$h .= self::dong( $g, 0 === $n );
		}
		return $h . self::phan_trang( $q, $chu, $trang, $so_trang ) . '</section>';
	}

	/**
	 * Gom các mục từ cùng mặt chữ (cùng tên khi chuẩn hoá dấu) thành một nhóm, giữ thứ tự xuất hiện; mỗi nhóm xếp theo tên.
	 *
	 * @param list<\WP_Term> $ds
	 * @return list<list<\WP_Term>>
	 */
	private static function gom( array $ds ): array {
		usort( $ds, static fn( \WP_Term $a, \WP_Term $b ): int => strcmp( BoDau::cho_tim( $a->name ), BoDau::cho_tim( $b->name ) ) ?: strcmp( $a->name, $b->name ) ?: $a->term_id <=> $b->term_id );
		$ra = array();
		foreach ( $ds as $t ) {
			$ra[ BoDau::chuan_hoa_co_dau( trim( $t->name ) ) ][] = $t;
		}
		return array_values( $ra );
	}

	/** Một dòng kết quả: một mục từ, hoặc một chữ nhiều nghĩa (liệt kê từng nghĩa, mỗi nghĩa một đường dẫn). */
	private static function dong( array $g, bool $dung ): string {
		$tom = static function ( \WP_Term $t ): string {
			$giai = trim( wp_strip_all_tags( (string) $t->description ) );
			return mb_strlen( $giai ) > 140 ? rtrim( mb_substr( $giai, 0, 139 ) ) . '…' : $giai;
		};
		$url = static function ( \WP_Term $t ): string {
			$u = get_term_link( $t );
			return is_wp_error( $u ) ? '' : $u;
		};
		$lop = 'list-row' . ( $dung ? ' tu-dien-dung' : '' );
		if ( 1 === count( $g ) ) {
			$t    = $g[0];
			$giai = $tom( $t );
			return '<div class="' . $lop . '"><div><h3><a href="' . esc_url( $url( $t ) ) . '">' . esc_html( $t->name ) . '</a></h3>'
				. ( '' === $giai ? '' : '<p class="muted">' . esc_html( $giai ) . '</p>' ) . '<p class="small muted">' . (int) $t->count . ' bài có từ này</p></div>'
				. '<a class="btn ghost compact" href="' . esc_url( $url( $t ) ) . '" aria-label="' . esc_attr( 'Xem mục từ ' . $t->name ) . '">Xem ' . Ve::icon( 'arrow' ) . '</a></div>';
		}
		$li = '';
		foreach ( $g as $i => $t ) {
			$giai = $tom( $t );
			$li  .= '<li><a href="' . esc_url( $url( $t ) ) . '" aria-label="' . esc_attr( $t->name . ', nghĩa ' . ( $i + 1 ) ) . '">' . esc_html( '' === $giai ? 'Nghĩa ' . ( $i + 1 ) : $giai ) . '</a> <span class="small muted">· ' . (int) $t->count . ' bài</span></li>';
		}
		return '<div class="' . $lop . '"><div><h3><a href="' . esc_url( $url( $g[0] ) ) . '">' . esc_html( $g[0]->name ) . '</a> <span class="small muted">' . count( $g ) . ' nghĩa</span></h3><ol class="tu-dien-nghia">' . $li . '</ol></div></div>';
	}

	/** Phân trang kết quả bằng đường dẫn GET (giữ q / chữ cái). */
	private static function phan_trang( string $q, string $chu, int $dang, int $tong ): string {
		if ( $tong < 2 ) {
			return '';
		}
		$o     = '';
		$truoc = 0;
		for ( $i = 1; $i <= $tong; $i++ ) {
			if ( $tong > 7 && 1 !== $i && $tong !== $i && abs( $i - $dang ) > 2 ) {
				continue;
			}
			if ( $truoc && $i - $truoc > 1 ) {
				$o .= '<span class="small muted" aria-hidden="true">…</span>';
			}
			$u     = add_query_arg( array_filter( array( 'q' => $q, 'chu' => $chu, 'trang' => $i > 1 ? $i : null ) ), Ve::url( 'tu-dien' ) );
			$o    .= $i === $dang ? '<span class="btn primary" aria-current="page">' . $i . '</span>' : '<a class="btn" href="' . esc_url( $u ) . '" aria-label="Trang ' . $i . '">' . $i . '</a>';
			$truoc = $i;
		}
		return '<nav class="pagination" aria-label="Phân trang Từ điển">' . $o . '</nav>';
	}

	/**
	 * Các mục từ khác cùng mặt chữ với một mục (chữ nhiều nghĩa, vd. ba mục "Mai") — để trang mục từ dẫn sang nghĩa khác.
	 *
	 * @return list<\WP_Term>
	 */
	public static function cung_mat_chu( \WP_Term $t ): array {
		$khoa = BoDau::chuan_hoa_co_dau( trim( $t->name ) );
		return array_values( array_filter( self::tat_ca(), static fn( \WP_Term $x ): bool => $x->term_id !== $t->term_id && BoDau::chuan_hoa_co_dau( trim( $x->name ) ) === $khoa ) );
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
		static $ds = null, $moc = null;
		$moi = wp_cache_get_last_changed( 'terms' ); // mục từ thêm, sửa, gắn bài → dựng lại danh sách
		if ( null === $ds || $moc !== $moi ) {
			$moc = $moi;
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
