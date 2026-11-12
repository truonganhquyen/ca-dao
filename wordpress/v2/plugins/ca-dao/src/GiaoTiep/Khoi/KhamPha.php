<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\DichVu\DungTrang\DuLieuTrangChu;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Các trang lối đọc của menu Khám phá (docs/27 mục 3, 7.2, 7.8; bộ thiết kế R31, R04, R20, R19): /kham-pha/,
 * /chu-de/, /mo-tip/, /ngau-nhien/, /hay-bi-nham/. Chỉ điều hướng và dữ liệu đã có — không viết nội dung mới.
 * Liên kết tới trang chưa có (trang tĩnh chưa tạo, Hay bị nhầm dưới 5 câu) không hiện (Ve::co).
 */
final class KhamPha {

	/** Hay bị nhầm công khai khi có ít nhất chừng này câu có cờ (docs/27 mục 7.8). */
	public const HAY_BI_NHAM_TOI_THIEU = 5;

	public static function hien( string $trang ): string {
		return match ( $trang ) {
			'chu-de'      => self::phan_loai( 'chu_de', 'Chủ đề', 'Mẹ, quê hương, lao động… Mỗi chủ đề mở các bài người biên soạn đã xếp vào.' ),
			'mo-tip'      => self::phan_loai( 'mo_tip', 'Mô-típ', 'Những lối mở đầu, hình ảnh lặp lại qua nhiều bài: "Thân em như…", "Chiều chiều…".' ),
			'ngau-nhien'  => self::ngau_nhien(),
			'hay-bi-nham' => self::hay_bi_nham(),
			default       => self::kham_pha(),
		};
	}

	private static function dau( string $ten, string $eyebrow, string $h1, string $mo_ta = '' ): string {
		return '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span>'
			. ( 'Khám phá' === $ten ? '' : Ve::lien_ket( 'kham-pha', 'Khám phá' ) . '<span aria-hidden="true">/</span>' ) . '<span>' . esc_html( $ten ) . '</span></nav>'
			. '<p class="eyebrow">' . esc_html( $eyebrow ) . '</p><h1>' . esc_html( $h1 ) . '</h1>' . ( '' === $mo_ta ? '' : '<p class="muted">' . esc_html( $mo_ta ) . '</p>' );
	}

	/** Hàng liên kết "tên →"; bỏ trang chưa có. @param array<string,string> $ds mã => chữ */
	private static function hang( array $ds ): string {
		$o = '';
		foreach ( $ds as $ma => $chu ) {
			$o .= Ve::co( $ma ) ? Ve::lien_ket( $ma, '<span>' . esc_html( $chu ) . '</span>' . Ve::icon( 'arrow' ), 'genre-row' ) : '';
		}
		return $o;
	}

	/** /kham-pha/ (R31): tìm, thể loại, lối đọc, tra cứu, về thư viện — chạy cả khi tắt JavaScript. */
	private static function kham_pha(): string {
		$the_loai = '';
		foreach ( DuLieuTrangChu::so_bai_the_loai() as $ma => $n ) {
			$the_loai .= Ve::lien_ket( $ma, '<span class="entry-icon">' . Ve::icon( TrangChu::HINH_THE_LOAI[ $ma ] ?? 'book' ) . '</span><strong>' . esc_html( TheLoai::DS[ $ma ] ) . '</strong><span>' . number_format( $n, 0, ',', '.' ) . ' bài</span>', 'tile g-' . $ma );
		}
		$loi = '';
		foreach ( array(
			'tuyen-tap'  => array( 'Tuyển tập', 'layers', 'Đọc liền một chủ đề, trình chiếu cả bộ' ),
			'theo-dip'   => array( 'Theo dịp', 'calendar', 'Tết, Vu Lan, 20/11…' ),
			'chu-de'     => array( 'Chủ đề', 'tag', 'Mẹ, quê hương, lao động…' ),
			'ban-do'     => array( 'Vùng & Bản đồ', 'map', 'Nơi lưu truyền, địa danh trong lời' ),
			'mo-tip'     => array( 'Mô-típ', 'spark', '"Thân em như…", "Chiều chiều…"' ),
			'ngau-nhien' => array( 'Một câu ngẫu nhiên', 'compass', 'Mở một trang bất chợt' ),
		) as $ma => [ $ten, $hinh, $mo_ta ] ) {
			$loi .= Ve::lien_ket( $ma, '<span class="entry-icon">' . Ve::icon( $hinh ) . '</span><h3>' . esc_html( $ten ) . '</h3><p>' . esc_html( $mo_ta ) . '</p>', 'card start-card g-dip' );
		}
		return self::dau( 'Khám phá', 'Khám phá', 'Bạn muốn đọc gì?' )
			. '<form class="search-form big" role="search" method="get" action="' . esc_url( Ve::url( 'tim' ) ) . '"><label for="kp-q" class="sr-only">Tìm câu bạn nhớ</label>'
			. '<input id="kp-q" type="search" name="q" placeholder="Gõ câu bạn nhớ…"><button type="submit" class="btn primary">' . Ve::icon( 'search' ) . ' Tìm</button></form>'
			. ( '' === $the_loai ? '' : '<section class="section"><h2>Theo thể loại</h2><div class="tiles">' . Ve::lien_ket( 'kho', '<span class="entry-icon">' . Ve::icon( 'layers' ) . '</span><strong>Tất cả</strong>', 'tile g-tat-ca' ) . $the_loai . '</div></section>' )
			. '<section class="section"><h2>Lối đọc</h2><div class="start-grid">' . $loi . '</div></section>'
			. '<section class="section"><h2>Tra cứu</h2>' . self::hang( array( 'tim' => 'Tìm', 'tu-dien' => 'Từ điển', 'hay-bi-nham' => 'Hay bị nhầm là ca dao', 'phuong-phap' => 'Phương pháp & nguồn' ) ) . '</section>'
			. '<section class="section"><h2>Về thư viện</h2>' . self::hang( array( 've-du-an' => 'Về dự án', 'chinh-sach' => 'Chính sách thu thập dữ liệu', 'rieng-tu' => 'Quyền riêng tư', 'gop' => 'Góp tư liệu' ) ) . '</section>';
	}

	/** /chu-de/, /mo-tip/ (R04): các mục đã có bài, nhiều bài trước. */
	private static function phan_loai( string $tax, string $ten, string $mo_ta ): string {
		$ds = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
		$o  = '';
		foreach ( is_array( $ds ) ? $ds : array() as $t ) {
			$url = get_term_link( $t );
			if ( is_string( $url ) ) {
				$o .= '<a class="tile" href="' . esc_url( $url ) . '"><strong>' . esc_html( $t->name ) . '</strong><span>' . number_format( (int) $t->count, 0, ',', '.' ) . ' bài</span></a>';
			}
		}
		return self::dau( $ten, 'Khám phá', $ten, $mo_ta )
			. ( '' === $o
				? '<div class="empty"><h2>Chưa có mục nào</h2><p>Người biên soạn đang xếp dần các bài.</p>' . Ve::lien_ket( 'kho', 'Xem tất cả tác phẩm', 'btn primary' ) . '</div>'
				: '<section class="section"><h2 class="sr-only">Các mục</h2><div class="tiles">' . $o . '</div></section>' );
	}

	/** /ngau-nhien/ (R20): một bài đã đăng, không có cờ nghi; "Câu khác" là liên kết (chạy cả khi tắt JavaScript). */
	private static function ngau_nhien(): string {
		$ds  = get_posts(
			array(
				'post_type'   => KhaiBao::TAC_PHAM,
				'post_status' => 'publish',
				'numberposts' => 1,
				'orderby'     => 'rand',
				'meta_query'  => array( array( 'key' => '_phongdao_co_nghi', 'compare' => 'NOT EXISTS' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery -- một bài, ít bài có cờ
			)
		);
		$c   = $ds ? DocBai::the( $ds[0] ) : null;
		$dau = self::dau( 'Một câu ngẫu nhiên', 'Mở một trang bất chợt', 'Một câu ngẫu nhiên' );
		if ( ! $c ) {
			return $dau . '<div class="empty"><h2>Chưa có bài đủ điều kiện</h2><p>Trở về kho để đọc các tác phẩm đã có.</p>' . Ve::lien_ket( 'kho', 'Mở kho tác phẩm', 'btn primary' ) . '</div>';
		}
		return $dau . '<div class="grid">' . DanhSach::the( $c ) . '</div>'
			. '<div class="actions"><a class="btn primary" href="' . esc_url( add_query_arg( 'lan', wp_rand( 1, 99999 ), Ve::url( 'ngau-nhien' ) ) ) . '">Câu khác ' . Ve::icon( 'arrow' ) . '</a>'
			. '<a class="btn ghost" href="' . esc_url( $c['url'] ) . '">Xem đầy đủ</a></div><p class="small muted">Không chọn bài có cờ "Có thể không phải dân gian".</p>';
	}

	/** @return int[] ID các bài đã đăng có cờ "Có thể không phải dân gian" */
	public static function bai_co_nghi(): array {
		return array_map(
			'intval',
			get_posts(
				array(
					'post_type'   => KhaiBao::TAC_PHAM,
					'post_status' => 'publish',
					'numberposts' => 200,
					'fields'      => 'ids',
					'meta_query'  => array( array( 'key' => '_phongdao_co_nghi', 'compare' => 'EXISTS' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery -- ít bài
				)
			)
		);
	}

	public static function hay_bi_nham_cong_khai(): bool {
		return count( self::bai_co_nghi() ) >= self::HAY_BI_NHAM_TOI_THIEU;
	}

	/** /hay-bi-nham/ (R19): câu có cờ nghi, kèm lý do người biên soạn viết. Dưới 5 câu: khách nhận 404 (TrangCoDinh). */
	private static function hay_bi_nham(): string {
		$o = '';
		foreach ( self::bai_co_nghi() as $id ) {
			$bai = get_post( $id );
			$c   = $bai ? DocBai::the( $bai ) : null;
			$ly  = PhanBai::lay( $id, 'co_nghi' );
			if ( $c ) {
				$o .= '<div>' . DanhSach::the( $c ) . ( empty( $ly['ly_do'] ) ? '' : '<p class="small"><strong>Vì sao nghi:</strong> ' . esc_html( (string) $ly['ly_do'] ) . '</p>' ) . '</div>';
			}
		}
		return self::dau( 'Hay bị nhầm là ca dao', 'Tra cứu', 'Hay bị nhầm là ca dao', 'Những câu hay được gọi là ca dao, tục ngữ nhưng người biên soạn có lý do để nghi; mỗi câu kèm lý do.' )
			. '<div class="grid">' . $o . '</div>';
	}
}
