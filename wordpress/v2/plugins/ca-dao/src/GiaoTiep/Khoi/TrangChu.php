<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\DungTrang\DuLieuTrangChu;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Trang chủ (docs/27 mục 4; làm lại D-126 đợt giao diện): đầu trang hai cột — tên thư viện, ô tìm, lối tìm nhanh | Câu
 * hôm nay (trọng tâm của trang, viền hoạ tiết sóng) → Đọc nhiều tuần này (bảng xếp hạng) cạnh thẻ Bản đồ theo vùng →
 * Bắt đầu từ đâu? (bốn lối vào) → Đọc theo dịp → Tuyển tập nổi bật → Đọc theo thể loại → Vừa bổ sung → dải Phương pháp.
 * Bỏ khối "Bạn là" (chỉ đổi thứ tự khối, người đọc không thấy tác dụng). Khối không có dữ liệu thì không hiện (docs/27
 * mục 1); mỗi lối vào chỉ hứa điều đã có (D-119).
 */
final class TrangChu {

	/** Số bài có nơi lưu truyền tối thiểu để mời "Theo vùng" và thẻ bản đồ (bản đồ ít bài thì lối vào hứa quá điều có). */
	public const NGUONG_VUNG = 100;

	/**
	 * Bắt đầu từ đâu? — bốn lối vào (D-126), mỗi lối chỉ hứa điều đã có (D-119): "Dạy và học" dẫn tới tục ngữ đã có nghĩa
	 * và ghi số câu (chưa có câu nào thì ẩn); "Đọc theo dịp" chỉ khi đã có tuyển tập theo dịp.
	 */
	private const BAT_DAU = array(
		array( 'tuc-ngu', 'book', 'Dạy và học', '%s câu tục ngữ đã có nghĩa ngắn, dễ giảng.', 'tuc-ngu' ),
		array( 'dong-dao', 'kids', 'Đọc cùng bé', 'Đồng dao đọc cùng bé, kèm cách chơi khi có.', 'dong-dao' ),
		array( 'theo-dip', 'calendar', 'Đọc theo dịp', 'Tết, Vu Lan, 20/11… dịp gần nhất xếp đầu.', 'dip' ),
		array( 'tu-dien', 'quote', 'Tra từ khó', 'Hiểu một chữ khó trong lời.', 've' ),
	);

	public const HINH_THE_LOAI = array(
		'ca-dao'   => 'book',
		'tuc-ngu'  => 'quote',
		'dong-dao' => 'kids',
		've'       => 'layers',
		'cau-do'   => 'spark',
	);

	public static function hien(): string {
		return self::dau() . GopMotCau::moi()
			. self::doc_nhieu() . self::bat_dau() . self::tuyen_tap_noi_bat() . self::the_loai() . self::vua_bo_sung()
			. '<section class="section band"><div><h2>Cẩn trọng với từng lời.</h2><p class="muted">Cùng tìm hiểu cách đọc mức kiểm và nguồn theo từng lớp.</p></div>' . Ve::lien_ket( 'phuong-phap', 'Cách chúng tôi biên soạn ' . Ve::icon( 'arrow' ), 'btn' ) . '</section>';
	}

	/** Đủ bài có nơi lưu truyền để mời tìm theo vùng. */
	private static function co_vung(): bool {
		return DuLieuTrangChu::so_bai_co_vung() >= self::NGUONG_VUNG;
	}

	private static function dau(): string {
		$loi = array( array( 'tu-dien', 'Tra từ khó' ) );
		if ( self::co_vung() ) {
			$loi[] = array( 'ban-do', 'Theo vùng' );
		}
		$loi[] = array( 'chu-de', 'Theo chủ đề' );
		if ( DuLieuTrangChu::tuyen_tap( 'dip', 1 ) ) {
			$loi[] = array( 'theo-dip', 'Theo dịp' );
		}
		$chip = implode( '', array_map( static fn( array $l ): string => Ve::lien_ket( $l[0], esc_html( $l[1] ), 'loi-tat-chip' ), $loi ) );
		return '<section class="home-hero"><div class="home-hero-chu"><p class="eyebrow">Thư viện mở · phi lợi nhuận</p><h1>Thư viện Văn học Dân gian Việt Nam</h1>'
			. '<p class="slogan">Đọc một câu. Hiểu một đời sống.</p><p class="promise">Lời giữ nguyên văn. Mỗi câu ghi rõ đã được kiểm đến mức nào.</p>'
			. '<form class="search-form big" role="search" action="' . esc_url( Ve::url( 'tim' ) ) . '"><label for="home-q" class="sr-only">Tìm câu bạn nhớ</label><input id="home-q" type="search" name="q" placeholder="Gõ câu bạn nhớ…" aria-describedby="search-hint"><button type="submit" class="btn primary">' . Ve::icon( 'search' ) . ' Tìm</button></form>'
			. '<p id="search-hint" class="small muted" style="margin-top:10px">Gõ câu bạn nhớ, gõ không dấu cũng được</p>'
			. '<nav class="loi-tat" aria-label="Lối tìm nhanh">' . $chip . '</nav></div>'
			. self::cau_hom_nay() . '</section>';
	}

	private static function cau_hom_nay(): string {
		$tp = DuLieuTrangChu::cau_hom_nay();
		if ( ! $tp ) {
			return '';
		}
		// Nhãn và cách hiện như trang bài gọn (D-119): thể thơ chỉ Lục bát, Song thất lục bát; tục ngữ câu ngắn hai vế một dòng
		$nhan = $tp['ten_the_loai'] . ( in_array( (string) $tp['hinh_thuc'], array( 'Lục bát', 'Song thất lục bát' ), true ) ? ' · ' . $tp['hinh_thuc'] : '' );
		$loi  = 'proverb' === $tp['kieu'] && mb_strlen( (string) preg_replace( '/[^\p{L}]/u', '', implode( '', $tp['loi'] ) ) ) <= 60 ? TacPham::mot_dong( $tp['loi'] ) : $tp['loi'];
		$du   = ' data-id="' . esc_attr( $tp['ma'] ) . '" data-tieu-de="' . esc_attr( $tp['tieu_de'] ) . '" data-the-loai="' . esc_attr( $tp['ten_the_loai'] ) . '" data-url="' . esc_url( $tp['url'] ) . '"';
		return '<section class="today-card cau-hn" aria-labelledby="today-h"><div class="today-top"><h2 class="eyebrow" id="today-h">Câu hôm nay</h2><span class="small">' . esc_html( $nhan ) . '</span>' . Ve::nut_chep( $tp['url'], $tp['ten_the_loai'] ) . '</div>'
			. Ve::loi( $loi, $tp['kieu'], array(), array( 'max' => '26', 'nhom' => 'hom-nay' ) )
			. Ve::muc_kiem( $tp['doi_chieu'], false, $tp['url'] . '#tu-lieu' )
			. ( empty( $tp['y_nghia']['chu'] ) || 'cau-do' === $tp['the_loai'] ? '' : Ve::y_nghia( $tp['y_nghia']['chu'], ! empty( $tp['y_nghia']['ai'] ) ) )
			. '<div class="actions"><a class="btn primary" href="' . esc_url( $tp['url'] ) . '">Đọc bài ' . Ve::icon( 'arrow' ) . '</a><button type="button" class="btn" data-action="save"' . $du . ' aria-pressed="false">' . Ve::icon( 'save' ) . '<span class="nhan">Lưu</span></button></div></section>';
	}

	/** Bắt đầu từ đâu? (bốn lối vào có màu) + Đọc theo dịp (khi có tuyển tập loại dịp). */
	private static function bat_dau(): string {
		$dip_ds = DuLieuTrangChu::tuyen_tap( 'dip', 6 );
		$the    = '';
		foreach ( self::BAT_DAU as [ $ma, $hinh, $ten, $mo_ta, $mau ] ) {
			$url = Ve::url( $ma );
			if ( 'tuc-ngu' === $ma ) {
				$n = DuLieuTrangChu::so_co_nghia( 'tuc-ngu' );
				if ( ! $n ) {
					continue;
				}
				$mo_ta = sprintf( $mo_ta, number_format( $n, 0, ',', '.' ) );
				$url   = add_query_arg( 'co', 'nghia', $url );
			} elseif ( 'theo-dip' === $ma && ! $dip_ds ) {
				continue;
			}
			$the .= '<a href="' . esc_url( $url ) . '" class="cua g-' . esc_attr( $mau ) . '"><span class="entry-icon">' . Ve::icon( $hinh ) . '</span><span class="cua-ten">' . esc_html( $ten ) . '</span><span class="cua-mo-ta">' . esc_html( $mo_ta ) . '</span></a>';
		}
		$dip = '';
		foreach ( $dip_ds as $t ) {
			$dip .= '<a class="tile g-dip" href="' . esc_url( $t['url'] ) . '"><span class="entry-icon">' . Ve::icon( 'calendar' ) . '</span><strong>' . esc_html( $t['ten'] ) . '</strong><span>' . (int) $t['so'] . ' câu</span></a>';
		}
		return '<section class="section"><h2>Bắt đầu từ đâu?</h2><div class="cua-luoi">' . $the . '</div></section>'
			. ( '' === $dip ? '' : '<section class="section"><div class="section-heading"><h2>Đọc theo dịp</h2>' . Ve::lien_ket( 'theo-dip', 'Xem hết ' . Ve::icon( 'arrow' ) ) . '</div><div class="tiles">' . $dip . '</div></section>' );
	}

	/**
	 * Đọc nhiều tuần này (khi đã có từ 3 bài được đọc trong 7 ngày): bảng xếp hạng hai cột, số thứ tự lớn. Bên cạnh là thẻ
	 * Bản đồ ca dao theo vùng (khi đủ bài có nơi lưu truyền). Không có cả hai thì không hiện.
	 */
	private static function doc_nhieu(): string {
		$doc = \Phongdao\DichVu\DoLuong\DoLuong::doc_nhieu( 7, 6 );
		$xh  = '';
		if ( count( $doc ) >= 3 ) {
			$li = '';
			$i  = 0;
			foreach ( array_keys( $doc ) as $id ) {
				$tl  = \Phongdao\DichVu\DungTrang\DocBai::the_loai( get_post( $id ) );
				$li .= '<li><span class="xh-so" aria-hidden="true">' . ( ++$i ) . '</span><span class="xh-chu"><a href="' . esc_url( (string) get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a>'
					. ( $tl && isset( TheLoai::DS[ $tl ] ) ? '<span class="small muted">' . esc_html( TheLoai::DS[ $tl ] ) . '</span>' : '' ) . '</span></li>';
			}
			$xh = '<div class="xh"><h2 id="doc-nhieu">Đọc nhiều tuần này</h2><ol class="xh-ds">' . $li . '</ol></div>';
		}
		$bd = self::co_vung()
			? '<a class="the-ban-do" href="' . esc_url( Ve::url( 'ban-do' ) ) . '"><img src="' . esc_url( plugins_url( 'assets/ban-do-vn.svg', dirname( __DIR__, 3 ) . '/ca-dao.php' ) ) . '" alt="" width="120" height="120" loading="lazy" decoding="async"><span><strong>Ca dao theo vùng</strong><span class="small">Chạm một vùng trên bản đồ để đọc câu lưu truyền ở đó.</span></span></a>'
			: '';
		return '' === $xh . $bd ? '' : '<section class="section tc-hai' . ( '' === $xh || '' === $bd ? ' mot' : '' ) . '"' . ( '' === $xh ? ' aria-label="Bản đồ"' : ' aria-labelledby="doc-nhieu"' ) . '>' . $xh . $bd . '</section>';
	}

	/** Khối 5: Tuyển tập nổi bật (khi có tuyển tập). */
	private static function tuyen_tap_noi_bat(): string {
		$o = '';
		foreach ( DuLieuTrangChu::tuyen_tap( '', 3 ) as $t ) {
			$o .= '<a class="card feat-card" href="' . esc_url( $t['url'] ) . '"><span class="feat-num">' . (int) $t['so'] . '</span><span><h3>' . esc_html( $t['ten'] ) . '</h3><p class="small">' . (int) $t['so'] . ' câu</p></span></a>';
		}
		return '' === $o ? '' : '<section class="section"><div class="section-heading"><h2>Tuyển tập nổi bật</h2>' . Ve::lien_ket( 'tuyen-tap', 'Xem tất cả ' . Ve::icon( 'arrow' ) ) . '</div><div class="featured">' . $o . '</div></section>';
	}

	/** Khối 7: Đọc theo thể loại — số bài thật; thể loại chưa có bài thì ẩn. */
	private static function the_loai(): string {
		$so = DuLieuTrangChu::so_bai_the_loai();
		if ( ! $so ) {
			return '';
		}
		$o = '';
		foreach ( $so as $ma => $n ) {
			$o .= Ve::lien_ket( $ma, '<span class="tl-chu" aria-hidden="true">' . esc_html( mb_substr( TheLoai::DS[ $ma ], 0, 1 ) ) . '</span><strong>' . esc_html( TheLoai::DS[ $ma ] ) . '</strong><span>' . number_format( $n, 0, ',', '.' ) . ' bài</span>', 'tile tl g-' . $ma );
		}
		return '<section class="section"><h2>Đọc theo thể loại</h2><div class="tiles five">' . $o . '</div></section>';
	}

	/** Vừa bổ sung: hai bài mới nhất. */
	private static function vua_bo_sung(): string {
		$the = array_map( array( DanhSach::class, 'the' ), DuLieuTrangChu::vua_bo_sung( 2 ) );
		return $the ? '<section class="section vua-bo-sung"><h2>Vừa bổ sung</h2><div class="grid two">' . implode( '', $the ) . '</div></section>' : '';
	}
}
