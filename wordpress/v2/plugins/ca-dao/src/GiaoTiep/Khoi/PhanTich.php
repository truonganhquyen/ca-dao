<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\GopNguoiDoc;

defined( 'ABSPATH' ) || exit;

/**
 * Bài cảm nhận – phân tích (bộ thiết kế R23; giữ loại bài và khoá meta MVP: _cadao_bai, _cadao_but_danh, _cadao_vai_tro,
 * _cadao_du_an). Chỉ bài đã duyệt mới có trang; phần người viết bàn luận tách khỏi lời tác phẩm, không thành Ý nghĩa.
 */
final class PhanTich {

	/** Dòng người viết: "Bút danh · vai", hoặc "Bài phân tích của thư viện". */
	public static function nguoi_viet( \WP_Post $p ): string {
		if ( get_post_meta( $p->ID, '_cadao_du_an', true ) ) {
			return 'Bài phân tích của thư viện';
		}
		$ten = trim( (string) get_post_meta( $p->ID, '_cadao_but_danh', true ) ) ?: 'Một bạn đọc';
		$vai = GopNguoiDoc::VAI[ (string) get_post_meta( $p->ID, '_cadao_vai_tro', true ) ] ?? '';
		return $ten . ( '' !== $vai ? ' · ' . $vai : '' );
	}

	/** Bài cảm nhận đã duyệt của một tác phẩm (cũ trước). @return list<\WP_Post> */
	public static function cua_bai( int $bai ): array {
		return get_posts( array( 'post_type' => KhaiBao::PHAN_TICH, 'post_status' => 'publish', 'numberposts' => 30, 'meta_key' => '_cadao_bai', 'meta_value' => $bai, 'orderby' => 'date', 'order' => 'ASC' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery -- vài chục bài
	}

	public static function hien( \WP_Post $p ): string {
		$bai = get_post( (int) get_post_meta( $p->ID, '_cadao_bai', true ) );
		$bai = $bai && KhaiBao::TAC_PHAM === $bai->post_type && 'publish' === $bai->post_status ? $bai : null;
		$the = $bai ? DocBai::the( $bai ) : null;
		return '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span>'
			. ( $bai ? '<a href="' . esc_url( (string) get_permalink( $bai ) ) . '">' . esc_html( $bai->post_title ) . '</a><span aria-hidden="true">/</span>' : '' )
			. '<span>' . esc_html( $p->post_title ) . '</span></nav>'
			. '<p class="eyebrow">Cảm nhận – phân tích</p><h1>' . esc_html( $p->post_title ) . '</h1>'
			. '<p class="small muted">' . esc_html( self::nguoi_viet( $p ) . ' · ' . mysql2date( 'd/m/Y', $p->post_date ) ) . '</p>'
			// Nội dung đã thoát khi nhận (GopNguoiDoc::van_ban) hoặc do người biên soạn viết; vẫn lọc thẻ khi in
			. '<article class="bai-cam-nhan">' . wp_kses_post( do_blocks( $p->post_content ) ) . '</article>'
			. ( $the ? '<section class="section"><h2>Tác phẩm đang bàn</h2><div class="grid">' . DanhSach::the( $the ) . '</div></section>' : '' )
			. '<p class="small muted">Bài viết của người đọc, người biên soạn đã duyệt để hiện. Không thay thế Ý nghĩa của tác phẩm · CC BY 4.0 · '
			. ( $bai ? '<a href="' . esc_url( add_query_arg( array( 'loai' => 'vi_pham', 'bai' => $bai->ID, 'vi_pham' => $p->ID ), Ve::url( 'gop' ) ) . '#gop-form' ) . '">Báo vi phạm</a>' : '' ) . '</p>';
	}
}
