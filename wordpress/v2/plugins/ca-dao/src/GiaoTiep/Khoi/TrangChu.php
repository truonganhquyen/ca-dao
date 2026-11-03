<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\DungTrang\DuLieuTrangChu;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Trang chủ (docs/27 mục 4, bộ thiết kế R01): đầu trang chủ (tìm, "Bạn là", ba lối tìm) → Câu hôm nay →
 * các khối đổi thứ tự theo vai (data-home-block, doc.js; nhớ trên máy người đọc) → dải Phương pháp.
 * Khối không có dữ liệu thì không hiện (docs/27 mục 1): Đọc theo dịp, Tuyển tập nổi bật cần tuyển tập;
 * Bạn có biết?, Đọc nhiều tuần này làm khi có dữ liệu (lượt đọc, Bạn có biết đã duyệt).
 */
final class TrangChu {

	private const LOI_TIM = array(
		array( 'tim', 'type', 'Tìm khi nhớ vài chữ', 'Gõ không dấu, gõ rời rạc cũng ra' ),
		array( 'ban-do', 'map', 'Tìm theo vùng', 'Nơi lưu truyền, địa danh trong lời' ),
		array( 'chu-de', 'tag', 'Tìm theo chủ đề', 'Mẹ, quê hương, lao động…' ),
	);

	private const BAT_DAU = array(
		array( 'tuc-ngu', 'book', 'Hiểu nghĩa tục ngữ', 'Nghĩa ngắn trong 10 giây, có nghĩa đen, nghĩa bóng.', 'tuc-ngu' ),
		array( 'theo-dip', 'calendar', 'Đọc theo dịp', 'Tết, Vu Lan, 20/11… dịp gần nhất xếp đầu.', 'ca-dao' ),
		array( 'dong-dao', 'kids', 'Cùng bé chơi', 'Đồng dao kèm trò chơi, lọc theo tuổi và số bé.', 'dong-dao' ),
	);

	private const HINH_THE_LOAI = array(
		'ca-dao'   => 'book',
		'tuc-ngu'  => 'quote',
		'dong-dao' => 'kids',
		've'       => 'layers',
		'cau-do'   => 'spark',
	);

	public static function hien(): string {
		return self::dau() . self::cau_hom_nay()
			. '<div id="role-sections">' . self::bat_dau() . self::tuyen_tap_noi_bat() . self::the_loai() . self::vua_bo_sung() . '</div>'
			. '<section class="section band"><div><h2>Cẩn trọng với từng lời.</h2><p class="muted">Cùng tìm hiểu cách đọc mức kiểm và nguồn theo từng lớp.</p></div>' . Ve::lien_ket( 'phuong-phap', 'Cách chúng tôi biên soạn ' . Ve::icon( 'arrow' ), 'btn' ) . '</section>';
	}

	private static function dau(): string {
		$vai = '';
		foreach ( array( 'Học sinh', 'Giáo viên', 'Phụ huynh', 'Yêu văn hoá' ) as $v ) {
			$vai .= '<button type="button" class="chip" data-action="role" data-value="' . esc_attr( $v ) . '" aria-pressed="false">' . esc_html( $v ) . '</button>';
		}
		$loi = '';
		foreach ( self::LOI_TIM as [ $ma, $hinh, $ten, $mo_ta ] ) {
			$loi .= Ve::lien_ket( $ma, '<span class="entry-icon">' . Ve::icon( $hinh ) . '</span><span><strong>' . esc_html( $ten ) . '</strong><span class="small">' . esc_html( $mo_ta ) . '</span></span>' . Ve::icon( 'arrow' ), 'entry-card' );
		}
		// "Bạn là" chỉ đổi thứ tự khối, cần JavaScript: ẩn sẵn, doc.js hiện ra (tắt JavaScript không có nút chết)
		return '<section class="home-hero"><div><p class="eyebrow">Thư viện mở · phi lợi nhuận</p><h1>Thư viện Văn học Dân gian Việt Nam</h1>'
			. '<p class="slogan">Đọc một câu. Hiểu một đời sống.</p><p class="promise">Mỗi câu ghi rõ lấy từ đâu, và đã được kiểm đến mức nào.</p>'
			. '<form class="search-form big" role="search" action="' . esc_url( Ve::url( 'tim' ) ) . '"><label for="home-q" class="sr-only">Tìm câu bạn nhớ</label><input id="home-q" type="search" name="q" placeholder="Gõ câu bạn nhớ…" aria-describedby="search-hint"><button type="submit" class="btn primary">' . Ve::icon( 'search' ) . ' Tìm</button></form>'
			. '<p id="search-hint" class="small muted" style="margin-top:10px">Gõ câu bạn nhớ, gõ không dấu cũng được</p>'
			. '<div class="role-bar" hidden><span>Bạn là</span>' . $vai . '<button type="button" class="btn compact ghost" data-action="role-reset">Bỏ qua</button></div></div>'
			. '<div class="entry-cards">' . $loi . '</div></section>';
	}

	private static function cau_hom_nay(): string {
		$tp = DuLieuTrangChu::cau_hom_nay();
		if ( ! $tp ) {
			return '';
		}
		$nhan = trim( $tp['ten_the_loai'] . ( $tp['hinh_thuc'] ? ' · ' . $tp['hinh_thuc'] : '' ) );
		$du   = ' data-id="' . esc_attr( $tp['ma'] ) . '" data-tieu-de="' . esc_attr( $tp['tieu_de'] ) . '" data-the-loai="' . esc_attr( $tp['ten_the_loai'] ) . '" data-url="' . esc_url( $tp['url'] ) . '"';
		return '<section class="today-card" aria-labelledby="today-h"><div class="today-top"><h2 class="eyebrow" id="today-h">Câu hôm nay</h2><span class="small">' . esc_html( $nhan ) . '</span>' . Ve::nut_chep( $tp['url'], $tp['ten_the_loai'] ) . '</div>'
			. Ve::loi( $tp['loi'], $tp['kieu'], array(), array( 'max' => '26', 'nhom' => 'hom-nay' ) )
			. Ve::muc_kiem( $tp['doi_chieu'], false, $tp['url'] . '#tu-lieu' )
			. ( empty( $tp['y_nghia']['chu'] ) ? '' : Ve::y_nghia( $tp['y_nghia']['chu'], ! empty( $tp['y_nghia']['ai'] ) ) )
			. '<div class="actions"><a class="btn primary" href="' . esc_url( $tp['url'] ) . '">Đọc bài ' . Ve::icon( 'arrow' ) . '</a><button type="button" class="btn" data-action="save"' . $du . ' aria-pressed="false">' . Ve::icon( 'save' ) . '<span class="nhan">Lưu</span></button></div></section>';
	}

	/** Khối 4: Bắt đầu từ đâu? + Đọc theo dịp (khi có tuyển tập loại dịp). */
	private static function bat_dau(): string {
		$the = '';
		foreach ( self::BAT_DAU as [ $ma, $hinh, $ten, $mo_ta, $mau ] ) {
			$the .= Ve::lien_ket( $ma, '<span class="entry-icon">' . Ve::icon( $hinh ) . '</span><h3>' . esc_html( $ten ) . '</h3><p>' . esc_html( $mo_ta ) . '</p><span class="more">Mở ' . Ve::icon( 'arrow' ) . '</span>', 'card start-card g-' . $mau );
		}
		$dip = '';
		foreach ( DuLieuTrangChu::tuyen_tap( 'dip', 6 ) as $t ) {
			$dip .= '<a class="tile g-dip" href="' . esc_url( $t['url'] ) . '"><span class="entry-icon">' . Ve::icon( 'calendar' ) . '</span><strong>' . esc_html( $t['ten'] ) . '</strong><span>' . (int) $t['so'] . ' câu</span></a>';
		}
		return '<div data-home-block="4"><section class="section"><h2>Bắt đầu từ đâu?</h2><div class="start-grid">' . $the . '</div></section>'
			. ( '' === $dip ? '' : '<section class="section"><div class="section-heading"><h2>Đọc theo dịp</h2>' . Ve::lien_ket( 'theo-dip', 'Xem hết ' . Ve::icon( 'arrow' ) ) . '</div><div class="tiles">' . $dip . '</div></section>' )
			. '</div>';
	}

	/** Khối 5: Tuyển tập nổi bật (khi có tuyển tập). */
	private static function tuyen_tap_noi_bat(): string {
		$o = '';
		foreach ( DuLieuTrangChu::tuyen_tap( '', 3 ) as $t ) {
			$o .= '<a class="card feat-card" href="' . esc_url( $t['url'] ) . '"><span class="feat-num">' . (int) $t['so'] . '</span><span><h3>' . esc_html( $t['ten'] ) . '</h3><p class="small">' . (int) $t['so'] . ' câu</p></span></a>';
		}
		return '' === $o ? '' : '<section class="section" data-home-block="5"><div class="section-heading"><h2>Tuyển tập nổi bật</h2>' . Ve::lien_ket( 'tuyen-tap', 'Xem tất cả ' . Ve::icon( 'arrow' ) ) . '</div><div class="featured">' . $o . '</div></section>';
	}

	/** Khối 7: Đọc theo thể loại — số bài thật; thể loại chưa có bài thì ẩn. */
	private static function the_loai(): string {
		$so = DuLieuTrangChu::so_bai_the_loai();
		if ( ! $so ) {
			return '';
		}
		$o = '';
		foreach ( $so as $ma => $n ) {
			$o .= Ve::lien_ket( $ma, '<span class="entry-icon">' . Ve::icon( self::HINH_THE_LOAI[ $ma ] ?? 'book' ) . '</span><strong>' . esc_html( TheLoai::DS[ $ma ] ) . '</strong><span>' . number_format( $n, 0, ',', '.' ) . ' bài</span>', 'tile g-' . $ma );
		}
		return '<section class="section" data-home-block="7"><h2>Đọc theo thể loại</h2><div class="tiles five">' . $o . '</div></section>';
	}

	/** Khối 8: Vừa bổ sung; Đọc nhiều tuần này khi đã có từ 3 bài được đọc trong 7 ngày (như MVP). */
	private static function vua_bo_sung(): string {
		$the = array_map( array( DanhSach::class, 'the' ), DuLieuTrangChu::vua_bo_sung( 2 ) );
		$h   = $the ? '<section class="section" data-home-block="8"><h2>Vừa bổ sung</h2><div class="grid two">' . implode( '', $the ) . '</div></section>' : '';
		$doc = \Phongdao\DichVu\DoLuong\DoLuong::doc_nhieu( 7, 5 );
		if ( count( $doc ) >= 3 ) {
			$li = '';
			foreach ( array_keys( $doc ) as $id ) {
				$li .= '<li><a href="' . esc_url( (string) get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></li>';
			}
			$h .= '<section class="section" aria-labelledby="doc-nhieu"><h2 id="doc-nhieu">Đọc nhiều tuần này</h2><ol class="uses">' . $li . '</ol></section>';
		}
		return $h;
	}
}
