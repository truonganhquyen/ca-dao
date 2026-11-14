<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;

defined( 'ABSPATH' ) || exit;

/**
 * Tuyển tập (docs/27 mục 5.4; bộ thiết kế R10 danh mục, R11 đọc liền). Một tuyển tập công khai khi đã đăng, có lời
 * dẫn và đủ số câu đã đăng: 8 câu (docs/27); tuyển tập "Tình huống" chuyển từ MVP giữ ngưỡng 2 của MVP để các trang
 * tình huống đang hiện không biến mất khi chuyển (đường dẫn cũ /tinh-huong/… chuyển hướng tới đây).
 */
final class TuyenTap {

	public const LOAI = array(
		'chu_de'     => 'Chủ đề',
		'dip'        => 'Theo dịp',
		'tinh_huong' => 'Tình huống',
		'bai_hoc'    => 'Bài học',
	);

	private const NHOM = array(
		'chu_de'     => 'Chủ đề để bắt đầu',
		'dip'        => 'Theo dịp',
		'tinh_huong' => 'Cho một tình huống',
		'bai_hoc'    => 'Đọc theo bài học',
	);

	private const NGUONG = array( 'tinh_huong' => 2 );

	private const NGUONG_CHUNG = 8;

	/** Các tác phẩm đã đăng của tuyển tập, đúng thứ tự, kèm nhóm và ghi chú. @return list<array{bai:\WP_Post,nhom:string,ghi_chu:string}> */
	public static function muc( \WP_Post $tt ): array {
		$ra = array();
		foreach ( PhanBai::lay( $tt->ID, 'muc' )['muc'] ?? array() as $m ) {
			$bai = get_post( (int) ( $m['tac_pham_id'] ?? 0 ) );
			if ( $bai && KhaiBao::TAC_PHAM === $bai->post_type && 'publish' === $bai->post_status ) {
				$ra[] = array(
					'bai'     => $bai,
					'nhom'    => (string) ( $m['nhom'] ?? '' ),
					'ghi_chu' => (string) ( $m['ghi_chu'] ?? '' ),
				);
			}
		}
		return $ra;
	}

	public static function loai( \WP_Post $tt ): string {
		$l = (string) ( PhanBai::lay( $tt->ID, 'thong_tin' )['loai'] ?? '' );
		return isset( self::LOAI[ $l ] ) ? $l : 'chu_de';
	}

	public static function cong_khai( \WP_Post $tt ): bool {
		return KhaiBao::TUYEN_TAP === $tt->post_type && 'publish' === $tt->post_status
			&& '' !== trim( (string) ( PhanBai::lay( $tt->ID, 'loi_dan' )['chu'] ?? '' ) )
			&& count( self::muc( $tt ) ) >= ( self::NGUONG[ self::loai( $tt ) ] ?? self::NGUONG_CHUNG );
	}

	/** Tuyển tập công khai (mới nhất trước trong mỗi loại). @return list<\WP_Post> */
	public static function cong_khai_ds( string $loai = '' ): array {
		$ds = get_posts( array( 'post_type' => KhaiBao::TUYEN_TAP, 'post_status' => 'publish', 'numberposts' => 200, 'orderby' => 'menu_order title', 'order' => 'ASC' ) );
		return array_values( array_filter( $ds, static fn( \WP_Post $t ): bool => ( '' === $loai || self::loai( $t ) === $loai ) && self::cong_khai( $t ) ) );
	}

	/** R11: một tuyển tập đọc liền. */
	public static function hien( \WP_Post $tt ): string {
		$loai    = self::loai( $tt );
		$muc     = self::muc( $tt );
		$loi_dan = trim( (string) ( PhanBai::lay( $tt->ID, 'loi_dan' )['chu'] ?? '' ) );
		$so      = count( $muc );
		$h       = '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span>' . Ve::lien_ket( 'tuyen-tap', 'Tuyển tập' ) . '<span aria-hidden="true">/</span><span>' . esc_html( $tt->post_title ) . '</span></nav>'
			. '<p class="eyebrow">Tuyển tập · ' . esc_html( self::LOAI[ $loai ] ) . '</p><h1>' . esc_html( $tt->post_title ) . '</h1>'
			. '<p class="muted">' . $so . ' câu · ' . max( 1, (int) ceil( $so / 2 ) ) . ' phút đọc</p>'
			// Trình chiếu cả bộ (hành động chính của tuyển tập, docs/27 mục 7.1): doc.js lấy các câu ngay trên trang
			. ( $so ? '<div class="actions no-print"><button type="button" class="btn primary" data-action="present-set" data-chi-js hidden>' . Ve::icon( 'present' ) . 'Trình chiếu cả bộ</button></div>' : '' );
		foreach ( '' === $loi_dan ? array() : preg_split( '/\n\s*\n/u', $loi_dan ) as $doan ) {
			$h .= '<p>' . nl2br( esc_html( trim( $doan ) ) ) . '</p>';
		}
		// Mục lục khi tuyển tập chia nhóm
		$nhom = array();
		foreach ( $muc as $i => $m ) {
			if ( '' !== $m['nhom'] && ! isset( $nhom[ $m['nhom'] ] ) ) {
				$nhom[ $m['nhom'] ] = $i + 1;
			}
		}
		if ( count( $nhom ) > 1 ) {
			$h .= '<details class="details"><summary>Mục lục · ' . count( $nhom ) . ' nhóm</summary>' . implode( '', array_map( static fn( $ten, $n ) => '<p><a href="#cau-' . (int) $n . '">' . sprintf( '%02d', $n ) . ' · ' . esc_html( $ten ) . '</a></p>', array_keys( $nhom ), $nhom ) ) . '</details>';
		}
		$h .= '<div data-collection>';
		foreach ( $muc as $i => $m ) {
			$h .= self::muc_html( $m, $i + 1, $loai );
		}
		$h .= '</div>';
		$khac = array_slice( array_values( array_filter( self::cong_khai_ds(), static fn( \WP_Post $t ): bool => $t->ID !== $tt->ID ) ), 0, 2 );
		if ( $khac ) {
			$h .= '<section class="section"><div class="section-heading"><h2>Tuyển tập khác</h2>' . Ve::lien_ket( 'tuyen-tap', 'Xem tất cả ' . Ve::icon( 'arrow' ) ) . '</div><div class="grid two">'
				. implode( '', array_map( static fn( \WP_Post $t ): string => '<div class="card"><span class="tag">' . count( self::muc( $t ) ) . ' câu</span><h3><a href="' . esc_url( (string) get_permalink( $t ) ) . '">' . esc_html( $t->post_title ) . '</a></h3></div>', $khac ) )
				. '</div></section>';
		}
		return $h;
	}

	/** Một câu trong tuyển tập (bộ thiết kế: collectionItem). */
	private static function muc_html( array $m, int $n, string $loai ): string {
		$tp = DocBai::tac_pham( $m['bai'] );
		if ( ! $tp || ! $tp['loi'] ) {
			return '';
		}
		$so  = sprintf( '%02d', $n );
		$g   = $tp['the_loai'] ?: 'ca-dao';
		$ghi = '' === $m['ghi_chu'] ? '' : '<p class="small"><strong>' . ( 'tinh_huong' === $loai ? 'Hợp khi:' : 'Ghi chú:' ) . '</strong> ' . esc_html( $m['ghi_chu'] ) . '</p>';
		$than = Ve::loi( $tp['loi'], $tp['kieu'], array(), array( 'nhom' => 'tuyen-tap' ) )
			. $ghi
			. ( empty( $tp['y_nghia']['chu'] )
				? '<p class="small muted">Bài đang được bổ sung ý nghĩa.</p>'
				: '<p><strong>Ý nghĩa:</strong> ' . esc_html( $tp['y_nghia']['chu'] ) . '</p>' . ( empty( $tp['y_nghia']['ai'] ) ? '' : '<p class="ai-note">Có hỗ trợ của AI, đã được người biên soạn duyệt.</p>' ) );
		return '<article class="collection-item g-' . esc_attr( $g ) . '" id="cau-' . $n . '"><span class="num">' . $so . '</span><div class="ci-than">'
			. '<div class="the-dau"><span class="tag"><span class="ci-so">' . $so . ' · </span>' . esc_html( $tp['ten_the_loai'] ?: 'Tác phẩm' ) . '</span>' . Ve::nut_chep( $tp['url'], $tp['the_loai'] ) . '</div>'
			. ( '' === $tp['co_nghi'] ? '' : '<p class="flag">Có thể không phải dân gian</p>' )
			// Có từ ngữ thô tục (D-111): lời, ghi chú, ý nghĩa nằm trong khối đóng
			. ( empty( $tp['tho_tuc'] ) ? $than : Ve::che_tho_tuc( $than ) )
			. Ve::muc_kiem( $tp['doi_chieu'], true, $tp['url'] . '#tu-lieu' )
			. '<div class="actions"><a class="btn compact ghost" href="' . esc_url( $tp['url'] ) . '" aria-label="' . esc_attr( empty( $tp['tho_tuc'] ) ? 'Xem bài: ' . $tp['tieu_de'] : 'Xem bài có từ ngữ thô tục' ) . '">Xem bài ' . Ve::icon( 'arrow' ) . '</a></div>'
			. '</div></article>';
	}

	/** R10: danh mục tuyển tập, lọc theo loại (?loai=). */
	public static function danh_muc( string $loai ): string {
		$loai = isset( self::LOAI[ $loai ] ) ? $loai : '';
		$h    = '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span><span>Tuyển tập</span></nav>'
			. '<p class="eyebrow">Đọc liền</p><h1>Một chủ đề, nhiều lời.</h1><p class="muted">Tuyển tập ghép từ những trang tác phẩm, để bạn đọc theo mạch và trích dẫn từng câu.</p>';
		$goc  = (string) get_post_type_archive_link( KhaiBao::TUYEN_TAP );
		$h   .= '<nav class="row" aria-label="Loại tuyển tập"><a class="chip" href="' . esc_url( $goc ) . '" aria-current="' . ( '' === $loai ? 'true' : 'false' ) . '">Tất cả</a>';
		foreach ( self::LOAI as $ma => $ten ) {
			$h .= '<a class="chip" href="' . esc_url( add_query_arg( 'loai', $ma, $goc ) ) . '" aria-current="' . ( $ma === $loai ? 'true' : 'false' ) . '">' . esc_html( $ten ) . '</a>';
		}
		$h  .= '</nav>';
		$co  = false;
		foreach ( '' === $loai ? array_keys( self::LOAI ) : array( $loai ) as $l ) {
			$ds = self::cong_khai_ds( $l );
			if ( ! $ds ) {
				continue;
			}
			$co = true;
			$h .= '<section class="section"><h2>' . esc_html( self::NHOM[ $l ] ) . '</h2><div class="grid">';
			foreach ( $ds as $t ) {
				$dan = trim( (string) ( PhanBai::lay( $t->ID, 'loi_dan' )['chu'] ?? '' ) );
				$dan = mb_strlen( $dan ) > 120 ? rtrim( mb_substr( $dan, 0, 119 ) ) . '…' : $dan;
				$url = (string) get_permalink( $t );
				$h  .= '<article class="card collection-card"><span class="tag">' . esc_html( self::LOAI[ $l ] ) . '</span><h3><a href="' . esc_url( $url ) . '">' . esc_html( $t->post_title ) . '</a></h3>'
					. '<p>' . count( self::muc( $t ) ) . ' câu' . ( '' === $dan ? '' : ' · ' . esc_html( $dan ) ) . '</p>'
					. '<a class="btn ghost" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( 'Đọc liền: ' . $t->post_title ) . '">Đọc liền ' . Ve::icon( 'arrow' ) . '</a></article>';
			}
			$h .= '</div></section>';
		}
		if ( ! $co ) {
			$h .= '<div class="empty"><h2>Chưa có tuyển tập ở đây</h2><p>Tuyển tập hiện khi có lời dẫn và đủ câu đã đăng. Trong lúc chờ, bạn có thể đọc từ kho tác phẩm.</p>' . Ve::lien_ket( 'kho', 'Đọc từ kho tác phẩm', 'btn primary' ) . '</div>';
		}
		return $h;
	}
}
