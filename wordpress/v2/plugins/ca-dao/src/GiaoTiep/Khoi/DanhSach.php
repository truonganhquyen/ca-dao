<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Danh sách tác phẩm theo truy vấn chính (bộ thiết kế R02 Tất cả, R03 Thể loại, R04 Chủ đề / vùng / mô-típ / hình thức):
 * thẻ bài, bộ lọc gửi bằng form (tắt JavaScript vẫn lọc được), phân trang 30 bài. Số bài, thứ tự do
 * HaTang\KhaiBao::truy_van_danh_sach đặt.
 */
final class DanhSach {

	/** Phân loại => dòng chữ nhỏ trên tiêu đề. */
	private const DAU = array(
		'the_loai'  => 'Đọc theo thể loại',
		'chu_de'    => 'Chủ đề',
		'vung'      => 'Nơi lưu truyền',
		'mo_tip'    => 'Mô-típ',
		'hinh_thuc' => 'Hình thức',
		'muc_tu'    => 'Bài có từ này',
		'tro_choi'  => 'Trò chơi',
	);

	/** Bộ lọc của trang Tất cả: tham số đường dẫn (= phân loại) => nhãn. */
	private const LOC = array(
		'the_loai'  => array( 'Thể loại', 'Tất cả' ),
		'chu_de'    => array( 'Chủ đề', 'Tất cả chủ đề' ),
		'hinh_thuc' => array( 'Hình thức', 'Tất cả hình thức' ),
		'vung'      => array( 'Nơi lưu truyền', 'Chưa giới hạn' ),
	);

	public static function hien( \WP_Query $q ): string {
		$tat_ca = $q->is_post_type_archive( KhaiBao::TAC_PHAM );
		$term   = $tat_ca ? null : $q->get_queried_object();
		if ( ! $tat_ca && ! ( $term instanceof \WP_Term && isset( self::DAU[ $term->taxonomy ] ) ) ) {
			return '';
		}
		$the = '';
		foreach ( $q->posts as $bai ) {
			$c    = $bai instanceof \WP_Post ? DocBai::the( $bai ) : null;
			$the .= $c ? self::the( $c ) : '';
		}
		$dang_loc = $tat_ca && array_filter( array_map( static fn( string $k ): string => (string) $q->get( $k ), array_keys( self::LOC ) ) );

		if ( $tat_ca ) {
			$dau = self::duong_dan( 'Tất cả tác phẩm' ) . '<p class="eyebrow">Kho tác phẩm</p><h1>Mỗi câu, một lối đọc.</h1><p class="muted">Tìm theo thể loại, hình thức, chủ đề hoặc nơi lưu truyền.</p>' . self::bo_loc( $q );
		} else {
			$mo_ta = trim( wp_strip_all_tags( (string) $term->description ) );
			$dau   = self::duong_dan( $term->name ) . '<p class="eyebrow">' . esc_html( self::DAU[ $term->taxonomy ] ) . '</p><h1>' . esc_html( $term->name ) . '</h1>'
				. ( '' === $mo_ta ? '' : '<p class="muted">' . esc_html( $mo_ta ) . '</p>' );
		}
		$dem = (int) $q->found_posts;
		$giua = '<p class="small muted">' . number_format( $dem, 0, ',', '.' ) . ' tác phẩm' . ( $q->max_num_pages > 1 ? ' · 30 bài mỗi trang' : '' ) . '</p>';
		if ( '' === $the ) {
			$giua = $dang_loc
				? self::trong( 'Chưa có bài khớp các bộ lọc', 'Thử bỏ một lựa chọn hoặc xoá toàn bộ bộ lọc.', '<a class="btn primary" href="' . esc_url( (string) get_post_type_archive_link( KhaiBao::TAC_PHAM ) ) . '">Xoá lọc</a>' )
				: self::trong( 'Chưa có bài nào ở đây', 'Thư viện đang bổ sung dần. Bạn có thể đọc các bài khác trong kho.', Ve::lien_ket( 'kho', 'Xem tất cả tác phẩm', 'btn primary' ) );
		} else {
			$giua .= '<div class="grid">' . $the . '</div>' . self::phan_trang( $q );
		}
		return $dau . '<section class="section" aria-labelledby="ds-tieu-de"><h2 class="sr-only" id="ds-tieu-de">Các tác phẩm</h2>' . $giua . '</section>';
	}

	/** Thẻ bài (bộ thiết kế: card trong common.mjs). */
	public static function the( array $c ): string {
		$dong = implode( '', array_map( static fn( string $d ): string => '<span class="dong">' . esc_html( $d ) . '</span>', $c['dong'] ) );
		return '<article class="card g-' . esc_attr( $c['the_loai'] ?: 'ca-dao' ) . '">'
			. ( $c['ten_the_loai'] ? '<span class="tag">' . esc_html( $c['ten_the_loai'] ) . '</span>' : '' )
			. ( $c['co_nghi'] ? '<p class="flag">Nghi không phải dân gian</p>' : '' )
			. '<h3><a href="' . esc_url( $c['url'] ) . '">' . esc_html( $c['tieu_de'] ) . '</a></h3>'
			. '<p class="serif dong-wrap">' . $dong . '</p>'
			. ( '' === $c['y_nghia'] ? '' : '<p class="muted">' . esc_html( $c['y_nghia'] ) . '</p>' )
			. '<div class="row between"><span></span><a class="btn compact ghost" href="' . esc_url( $c['url'] ) . '" aria-label="' . esc_attr( 'Đọc: ' . $c['tieu_de'] ) . '">Đọc ' . Ve::icon( 'arrow' ) . '</a></div></article>';
	}

	private static function duong_dan( string $ten ): string {
		$giua = 'Tất cả tác phẩm' === $ten ? '' : Ve::lien_ket( 'kho', 'Kho tác phẩm' ) . '<span aria-hidden="true">/</span>';
		return '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span>' . $giua . '<span>' . esc_html( $ten ) . '</span></nav>';
	}

	/** Bộ lọc của trang Tất cả: form GET, mỗi ô là một phân loại (WordPress tự lọc theo tham số cùng tên). */
	private static function bo_loc( \WP_Query $q ): string {
		$o   = '';
		$dem = 0;
		foreach ( self::LOC as $pl => [ $nhan, $tat_ca ] ) {
			$chon = (string) $q->get( $pl );
			$dem += '' === $chon ? 0 : 1;
			$opt  = '<option value="">' . esc_html( $tat_ca ) . '</option>';
			$ds   = 'the_loai' === $pl
				? array_map( static fn( string $ma, string $ten ): array => array( $ma, $ten ), array_keys( TheLoai::DS ), TheLoai::DS )
				: array_map( static fn( \WP_Term $t ): array => array( $t->slug, $t->name ), self::muc( $pl ) );
			foreach ( $ds as [ $ma, $ten ] ) {
				$opt .= '<option value="' . esc_attr( $ma ) . '"' . selected( $chon, $ma, false ) . '>' . esc_html( $ten ) . '</option>';
			}
			$o .= '<div><label for="loc-' . esc_attr( $pl ) . '">' . esc_html( $nhan ) . '</label><select id="loc-' . esc_attr( $pl ) . '" name="' . esc_attr( $pl ) . '">' . $opt . '</select></div>';
		}
		$sap = 'moi' === $q->get( 'sap' ) ? 'moi' : 'az';
		$o  .= '<div><label for="loc-sap">Sắp xếp</label><select id="loc-sap" name="sap"><option value="az"' . selected( $sap, 'az', false ) . '>A–Z</option><option value="moi"' . selected( $sap, 'moi', false ) . '>Mới thêm</option></select></div>';
		$xoa = $dem ? '<a class="btn ghost" href="' . esc_url( (string) get_post_type_archive_link( KhaiBao::TAC_PHAM ) ) . '">Xoá lọc</a>' : '';
		return '<details class="filters"' . ( $dem ? ' open' : '' ) . '><summary>Bộ lọc <span class="count">· ' . ( $dem ? $dem . ' đang chọn' : 'chưa chọn' ) . '</span></summary>'
			. '<form method="get" action="' . esc_url( (string) get_post_type_archive_link( KhaiBao::TAC_PHAM ) ) . '"><div class="field-grid">' . $o . '</div>'
			. '<div class="actions"><button type="submit" class="btn primary">Áp dụng</button>' . $xoa . '</div></form></details>';
	}

	/** Phân trang: mọi trang khi ≤ 7 trang; nhiều hơn thì trang đầu, cuối và hai trang quanh trang đang xem. */
	private static function phan_trang( \WP_Query $q ): string {
		$tong = (int) $q->max_num_pages;
		if ( $tong < 2 ) {
			return '';
		}
		$dang = max( 1, (int) $q->get( 'paged' ) );
		$o    = '';
		$truoc = 0;
		for ( $i = 1; $i <= $tong; $i++ ) {
			if ( $tong > 7 && 1 !== $i && $tong !== $i && abs( $i - $dang ) > 2 ) {
				continue;
			}
			if ( $truoc && $i - $truoc > 1 ) {
				$o .= '<span class="small muted" aria-hidden="true">…</span>';
			}
			$o    .= $i === $dang
				? '<span class="btn primary" aria-current="page">' . $i . '</span>'
				: '<a class="btn" href="' . esc_url( get_pagenum_link( $i ) ) . '" aria-label="Trang ' . $i . '">' . $i . '</a>';
			$truoc = $i;
		}
		return '<nav class="pagination" aria-label="Phân trang">' . $o . '</nav>';
	}

	/** @return \WP_Term[] các mục đang có bài của một phân loại */
	private static function muc( string $phan_loai ): array {
		$ds = get_terms( array( 'taxonomy' => $phan_loai, 'hide_empty' => true ) );
		return is_array( $ds ) ? $ds : array();
	}

	private static function trong( string $tieu_de, string $than, string $nut ): string {
		return '<div class="empty">' . Ve::icon( 'book' ) . '<h2>' . esc_html( $tieu_de ) . '</h2><p class="muted">' . esc_html( $than ) . '</p>' . $nut . '</div>';
	}
}
