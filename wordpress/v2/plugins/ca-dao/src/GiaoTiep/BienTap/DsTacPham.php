<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\GiaoTiep\Khoi\Ve;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\Phan;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Tác phẩm `/bien-tap/tac-pham/` (docs/28 mục 4.3; màn mẫu B03): mọi tác phẩm (đã đăng, nháp, đã ẩn), tìm theo tên / lời,
 * lọc theo thiếu gì; bấm một dòng mở trang bài ở chế độ sửa. Form GET, tắt JavaScript vẫn dùng được.
 */
final class DsTacPham {

	/** Bộ lọc => [nhãn, tham số truy vấn]. */
	private static function loc(): array {
		return array(
			'tat-ca'         => array( 'Tất cả', array() ),
			'thieu-y-nghia'  => array( 'Thiếu ý nghĩa', array( 'meta_query' => array( array( 'key' => Phan::khoa( 'y_nghia' ), 'compare' => 'NOT EXISTS' ) ) ) ),
			'chua-chu-de'    => array( 'Chưa chủ đề', array( 'tax_query' => array( array( 'taxonomy' => 'chu_de', 'operator' => 'NOT EXISTS' ) ) ) ),
			'co-co'          => array( 'Có cờ', array( 'meta_query' => array( array( 'key' => Phan::khoa( 'co_nghi' ), 'compare' => 'EXISTS' ) ) ) ),
			'ban-nhap'       => array( 'Bản nháp', array( 'post_status' => array( 'draft' ) ) ),
			'da-an'          => array( 'Đã ẩn', array( 'post_status' => array( 'private' ) ) ),
		);
	}

	public static function hien(): string {
		$q    = sanitize_text_field( wp_unslash( (string) ( $_GET['q'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$chon = sanitize_key( (string) ( $_GET['loc'] ?? 'tat-ca' ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$loc  = self::loc();
		$chon = isset( $loc[ $chon ] ) ? $chon : 'tat-ca';
		$co_ban = array(
			'post_type'      => KhaiBao::TAC_PHAM,
			'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
			'posts_per_page' => 30,
			'paged'          => max( 1, (int) ( $_GET['trang'] ?? 1 ) ), // phpcs:ignore WordPress.Security.NonceVerification
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) + ( '' === $q ? array() : array( 's' => $q ) );
		$truy = new \WP_Query( $loc[ $chon ][1] + $co_ban );
		$chip = '';
		foreach ( $loc as $ma => [ $nhan, $tham ] ) {
			$dem   = ( new \WP_Query( $tham + array( 'fields' => 'ids', 'posts_per_page' => 1 ) + $co_ban ) )->found_posts;
			$chip .= '<a class="chip" href="' . esc_url( add_query_arg( array( 'loc' => $ma, 'trang' => false ) ) ) . '" aria-current="' . ( $ma === $chon ? 'true' : 'false' ) . '">' . esc_html( $nhan ) . ' <span class="dem">' . (int) $dem . '</span></a>';
		}
		$dong = '';
		foreach ( $truy->posts as $bai ) {
			$dong .= self::dong( $bai );
		}
		$trang = '';
		if ( $truy->max_num_pages > 1 ) {
			$hien  = (int) $co_ban['paged'];
			$trang = '<nav class="pagination" aria-label="Phân trang">' . ( $hien > 1 ? '<a class="btn" href="' . esc_url( add_query_arg( 'trang', $hien - 1 ) ) . '">Trang trước</a>' : '' )
				. '<span class="btn primary" aria-current="page">' . $hien . ' / ' . (int) $truy->max_num_pages . '</span>'
				. ( $hien < $truy->max_num_pages ? '<a class="btn" href="' . esc_url( add_query_arg( 'trang', $hien + 1 ) ) . '">Trang sau</a>' : '' ) . '</nav>';
		}
		return '<p class="eyebrow">Biên tập</p><h1>Tác phẩm <span class="dem-lon">' . (int) $truy->found_posts . '</span></h1>'
			. '<form class="search-form big" method="get" action="' . esc_url( home_url( '/bien-tap/tac-pham/' ) ) . '"><input type="hidden" name="loc" value="' . esc_attr( $chon ) . '"><label for="tp-q" class="sr-only">Tìm tác phẩm</label><input id="tp-q" name="q" type="search" value="' . esc_attr( $q ) . '" placeholder="Tìm theo tên hoặc lời"><button type="submit" class="btn primary">' . Ve::icon( 'search' ) . ' Tìm</button></form>'
			. '<nav class="loc-viec" aria-label="Lọc tác phẩm">' . $chip . '</nav><p class="small muted">Sửa gần nhất trước. Bấm một bài để mở trang bài ở chế độ sửa.</p>'
			. ( '' === $dong ? '<div class="empty">' . Ve::icon( 'book' ) . '<h2>Không có tác phẩm khớp</h2><p class="muted">Thử bỏ bớt bộ lọc hoặc gõ ít chữ hơn.</p><a class="btn primary" href="' . esc_url( home_url( '/bien-tap/them/' ) ) . '">Thêm bài</a></div>' : '<ul class="ds-tp">' . $dong . '</ul>' . $trang );
	}

	private static function dong( \WP_Post $bai ): string {
		$g     = (string) DocBai::the_loai( $bai );
		$loi   = array_slice( array_values( array_filter( PhanBai::lay( $bai->ID, 'loi' )['dong'] ?? array(), 'strlen' ) ), 0, 2 );
		$thieu = null === PhanBai::lay( $bai->ID, 'y_nghia' );
		$tt    = array( 'publish' => array( 'ok', 'Đã đăng' ), 'draft' => array( 'nhap', 'Bản nháp' ), 'private' => array( 'an', 'Đã ẩn' ), 'pending' => array( 'nhap', 'Chờ' ) )[ $bai->post_status ] ?? array( 'nhap', $bai->post_status );
		$url   = 'publish' === $bai->post_status || 'private' === $bai->post_status ? (string) get_permalink( $bai ) : (string) get_preview_post_link( $bai );
		return '<li class="dong-tp g-' . esc_attr( $g ?: 'ca-dao' ) . '"><a class="dtp-lien-ket" href="' . esc_url( $url ) . '"><span class="dtp-than"><span class="tag">' . esc_html( TheLoai::DS[ $g ] ?? 'Chưa có thể loại' ) . '</span><strong>' . esc_html( $bai->post_title ) . '</strong>'
			. '<span class="serif dong-wrap">' . implode( '', array_map( static fn( $d ) => '<span class="dong">' . esc_html( $d ) . '</span>', $loi ) ) . '</span></span>'
			. '<span class="dtp-phai"><span class="trang-thai tt-' . esc_attr( $tt[0] ) . '">' . esc_html( $tt[1] ) . '</span>' . ( $thieu ? '<span class="small muted">thiếu ý nghĩa</span>' : '' ) . '</span></a></li>';
	}
}
