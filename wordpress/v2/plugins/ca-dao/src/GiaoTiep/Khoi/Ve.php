<?php

namespace Phongdao\GiaoTiep\Khoi;

defined( 'ABSPATH' ) || exit;

/**
 * Vẽ HTML các thành phần của trang người đọc, cùng cấu trúc và lớp CSS với bộ thiết kế (design/v2/source/common.mjs).
 * Mọi chữ lấy từ dữ liệu đều qua esc_html / esc_attr / esc_url trước khi in.
 */
final class Ve {

	/** Đường dẫn theo docs/27 mục 3 (trang chưa có thì tạm 404 tới khi làm). */
	public const DUONG = array(
		'trang-chu'   => '/',
		'kho'         => '/kho/',
		'ca-dao'      => '/the-loai/ca-dao/',
		'tuc-ngu'     => '/the-loai/tuc-ngu/',
		'dong-dao'    => '/the-loai/dong-dao/',
		've'          => '/the-loai/ve/',
		'cau-do'      => '/the-loai/cau-do/',
		'tuyen-tap'   => '/tuyen-tap/',
		'chu-de'      => '/chu-de/',
		'theo-dip'    => '/tuyen-tap/?loai=dip',
		'ban-do'      => '/ban-do/',
		'mo-tip'      => '/mo-tip/',
		'ngau-nhien'  => '/ngau-nhien/',
		'kham-pha'    => '/kham-pha/',
		'tim'         => '/tim/',
		'tu-dien'     => '/tu-dien/',
		'hay-bi-nham' => '/hay-bi-nham/',
		'phuong-phap' => '/phuong-phap/',
		'nguon'       => '/nguon/',
		've-du-an'    => '/ve-du-an/',
		'chinh-sach'  => '/chinh-sach-du-lieu/',
		'rieng-tu'    => '/quyen-rieng-tu/',
		'gop'         => '/gop/',
		'da-luu'      => '/da-luu/',
		'bien-tap'    => '/bien-tap/',
		'bt-tac-pham' => '/bien-tap/tac-pham/',
		'bt-them'     => '/bien-tap/them/',
		'bt-so-lieu'  => '/bien-tap/so-lieu/',
	);

	private const HINH = array(
		'search'   => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
		'arrow'    => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
		'book'     => '<path d="M12 5v15M3 4c4-1 6 0 9 2 3-2 5-3 9-2v15c-4-1-6 0-9 2-3-2-5-3-9-2z"/>',
		'heart'    => '<path d="M12 20s-7-4.4-9-9a5 5 0 0 1 9-3 5 5 0 0 1 9 3c-2 4.6-9 9-9 9z"/>',
		'pen'      => '<path d="M4 20h4L19 9l-4-4L4 16zM14 6l4 4"/>',
		'save'     => '<path d="M6 3h12v18l-6-4-6 4z"/>',
		'home'     => '<path d="m3 10 9-7 9 7M5 9v12h5v-7h4v7h5V9"/>',
		'compass'  => '<circle cx="12" cy="12" r="9"/><path d="m16 8-2.5 5.5L8 16l2.5-5.5z"/>',
		'quote'    => '<path d="M5 12H3V6h6v6c0 4-2 6-5 7m13-7h-2V6h6v6c0 4-2 6-5 7"/>',
		'share'    => '<path d="M14 3h7v7m0-7L10 14M10 5H4v16h16v-6"/>',
		'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1 1m12 12 1 1M5 19l1-1M18 6l1-1"/>',
		'check'    => '<path d="m4 12 5 5L20 6"/>',
		'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v1"/>',
		'eye'      => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
		'nghe'     => '<path d="M4 9v6h4l5 4V5L8 9z"/><path d="M16 9a4 4 0 0 1 0 6M18.5 6.5a8 8 0 0 1 0 11"/>',
		'copy'     => '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/>',
		'aa'       => '<path d="M3 19 8 5l5 14M5 14h6M15 19l3-8 3 8m-5-2.5h4"/>',
		'download' => '<path d="M12 3v12m-5-5 5 5 5-5M4 20h16"/>',
		'link'     => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3A4 4 0 0 0 11 18.7l1-1"/>',
		'edit'     => '<path d="m15 4 5 5M3 21l1-6L16 3l5 5L9 20z"/>',
		'type'     => '<path d="M4 7V5h16v2M12 5v14m-3 0h6"/>',
		'map'      => '<path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3z"/><path d="M9 3v15m6-12v15"/>',
		'tag'      => '<path d="M3 12V3h9l9 9-9 9z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
		'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4m8-4v4"/>',
		'kids'     => '<circle cx="9" cy="7" r="3"/><circle cx="17" cy="9" r="2"/><path d="M3 21v-2a6 6 0 0 1 12 0v2m0-6a4 4 0 0 1 6 3.5V21"/>',
		'layers'   => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
		'present'  => '<rect x="3" y="4" width="18" height="12" rx="1"/><path d="M8 20h8M12 16v4"/>',
		'spark'    => '<path d="M12 3v4m0 10v4M3 12h4m10 0h4M6 6l2.5 2.5m7 7L18 18M6 18l2.5-2.5m7-7L18 6"/>',
	);

	public static function icon( string $ten ): string {
		return '<svg class="icon" aria-hidden="true" viewBox="0 0 24 24">' . ( self::HINH[ $ten ] ?? '<circle cx="12" cy="12" r="8"/>' ) . '</svg>';
	}

	/** Trang tĩnh người sáng lập viết (trang WordPress, mang từ MVP): mã => đường dẫn trang. */
	private const TRANG_TINH = array(
		'phuong-phap' => 'phuong-phap',
		'nguon'       => 'nguon',
		've-du-an'    => 've-du-an',
		'chinh-sach'  => 'chinh-sach-du-lieu',
		'rieng-tu'    => 'quyen-rieng-tu',
	);

	/** Đích của liên kết đã có chưa: trang tĩnh đã đăng; Hay bị nhầm đủ câu. Chưa có thì menu, chân trang không hiện liên kết. */
	public static function co( string $ma ): bool {
		if ( 'hay-bi-nham' === $ma ) {
			return KhamPha::hay_bi_nham_cong_khai();
		}
		if ( ! isset( self::TRANG_TINH[ $ma ] ) ) {
			return true;
		}
		$p = get_page_by_path( self::TRANG_TINH[ $ma ] );
		return $p instanceof \WP_Post && 'publish' === $p->post_status;
	}

	public static function url( string $ma ): string {
		return home_url( self::DUONG[ $ma ] ?? '/' );
	}

	/** Liên kết; $nhan đã là HTML an toàn (biểu tượng + chữ đã thoát). */
	public static function lien_ket( string $ma, string $nhan, string $lop = '', string $them = '' ): string {
		return '<a href="' . esc_url( self::url( $ma ) ) . '"' . ( $lop ? ' class="' . esc_attr( $lop ) . '"' : '' ) . $them . '>' . $nhan . '</a>';
	}

	public static function brand(): string {
		return self::lien_ket( 'trang-chu', '<span class="logo" aria-hidden="true">P</span><span>Phongdao</span>', 'brand' );
	}

	/**
	 * Lời: mỗi câu một dòng (data-fit cho fit.js, --dai cho CSS khi tắt JavaScript — D-093).
	 *
	 * @param string[] $dong   các câu của lời, giữ nguyên như nguồn
	 * @param string[] $tu     từ có chú thích: lần xuất hiện đầu trong mỗi dòng thành liên kết tới khối Từ khó
	 * @param array{so_dong?:bool,nhom?:string,max?:string,san?:string} $tuy
	 */
	public static function loi( array $dong, string $kieu = 'lucbat', array $tu = array(), array $tuy = array() ): string {
		$dai = 0;
		foreach ( array_values( $dong ) as $i => $d ) {
			$dai = max( $dai, mb_strlen( $d ) + ( 'lucbat' === $kieu && 0 === $i % 2 ? 3 : 0 ) );
		}
		$html = self::khung_loi( $kieu, $dai, $tuy );
		foreach ( array_values( $dong ) as $i => $d ) {
			$html .= self::dong( $d, $kieu, $tu, empty( $tuy['so_dong'] ) ? 0 : $i + 1 );
		}
		return $html . '</div></div>';
	}

	/**
	 * Lời chia đoạn (MienDuLieu\ChiaDoan) trong một khối lời: tiêu đề đoạn, khổ, số dòng nối tiếp. Bài dài thêm dòng
	 * tóm tắt, mục lục, số dòng mỗi 5 dòng; đoạn sau dòng 30 thu gọn sau nút "Đọc toàn bài" (doc.js — tắt JavaScript
	 * thì hiện đủ). Mốc #cadao-doan-N như MVP.
	 *
	 * @param array{dai:bool,so_dong:int,phut:int,con_lai:int,doan:list<array>} $cd
	 * @param array{so_dong?:bool,nhom?:string,bai?:string} $tuy
	 */
	public static function loi_doan( array $cd, string $kieu, array $tu = array(), array $tuy = array() ): string {
		$dai = 0;
		foreach ( $cd['doan'] as $d ) {
			foreach ( $d['kho'] as $kho ) {
				foreach ( $kho as $i => $l ) {
					$dai = max( $dai, mb_strlen( $l ) + ( 'lucbat' === $kieu && 0 === $i % 2 ? 3 : 0 ) );
				}
			}
		}
		$so   = ! empty( $tuy['so_dong'] );
		$moi  = $cd['dai'] ? 5 : 1;
		$dem  = 0;
		$than = '';
		$muc  = '';
		foreach ( $cd['doan'] as $d ) {
			$id = 'cadao-doan-' . (int) $d['so'];
			$o  = '' === $d['tieu_de'] ? '' : '<h2 class="doan-ten">' . esc_html( $d['tieu_de'] ) . '</h2>';
			foreach ( $d['kho'] as $kho ) {
				$o .= '<div class="kho">';
				foreach ( $kho as $l ) {
					++$dem;
					$o .= self::dong( $l, $kieu, $tu, $so && 0 === $dem % $moi ? $dem : 0 );
				}
				$o .= '</div>';
			}
			$than .= '<div class="doan" id="' . $id . '" data-so-doan="' . (int) $d['so'] . '"' . ( $d['sau'] ? ' data-sau' : '' ) . '>' . $o . '</div>';
			$muc  .= '<li><a href="#' . $id . '">' . esc_html( '' === $d['tieu_de'] ? 'Đoạn ' . (int) $d['so'] : $d['tieu_de'] ) . '</a> <span class="small muted">· dòng ' . (int) $d['tu'] . '–' . (int) $d['den'] . '</span></li>';
		}
		$loi = self::khung_loi( $kieu, $dai, $tuy, 'loi-doan' ) . $than . '</div></div>';
		if ( ! $cd['dai'] ) {
			return $loi;
		}
		return '<div class="bai-dai" data-bai="' . esc_attr( (string) ( $tuy['bai'] ?? '' ) ) . '" data-con-lai="' . (int) $cd['con_lai'] . '">'
			. '<p class="bai-dai-tom small muted">' . number_format( (int) $cd['so_dong'], 0, ',', '.' ) . ' dòng · ' . count( $cd['doan'] ) . ' đoạn · khoảng ' . (int) $cd['phut'] . ' phút đọc</p>'
			. '<details class="disclosure muc-luc no-print"><summary>Mục lục</summary><div class="detail-body"><ol>' . $muc . '</ol></div></details>'
			. $loi . '</div>';
	}

	/** Mở khung lời: verse-box + verse (data-fit). $dai: độ dài dòng dài nhất, chưa tính chỗ số dòng. */
	private static function khung_loi( string $kieu, int $dai, array $tuy, string $lop = '' ): string {
		$so       = ! empty( $tuy['so_dong'] );
		$dai     += $so ? 5 : 0;
		$max      = (string) ( $tuy['max'] ?? '' );
		$kieu_css = '--dai:' . $dai . ( ctype_digit( $max ) ? ';--max:' . $max . 'px' : '' );
		return '<div class="verse-box"><div class="verse ' . esc_attr( trim( $kieu . ' ' . $lop ) ) . ( $so ? ' numbered' : '' ) . '" data-fit'
			. ( empty( $tuy['nhom'] ) ? '' : ' data-nhom="' . esc_attr( $tuy['nhom'] ) . '"' )
			. ( '' === $max ? '' : ' data-max="' . esc_attr( $max ) . '"' )
			. ( empty( $tuy['san'] ) ? '' : ' data-san="' . esc_attr( $tuy['san'] ) . '"' )
			. ' style="' . esc_attr( $kieu_css ) . '">';
	}

	/** Một dòng lời; $so > 0 thì ghi số dòng bên lề. Từ có chú thích (lần đầu trong dòng) thành liên kết. */
	private static function dong( string $d, string $kieu, array $tu, int $so ): string {
		$chu = 'proverb' === $kieu
			? implode( ' ', array_map( static fn( string $v ): string => '<span class="ve">' . esc_html( $v ) . '</span>', preg_split( '/(?<=,)\s+|\s+(?=—)/u', $d ) ?: array( $d ) ) )
			: esc_html( $d );
		foreach ( array_filter( $tu ) as $t ) {
			$t   = esc_html( $t );
			$chu = (string) preg_replace_callback(
				'/(?<![\p{L}])' . preg_quote( $t, '/' ) . '(?![\p{L}])/u',
				static fn( array $m ): string => '<a class="word" href="#chu-thich" aria-label="Xem chú thích từ ' . $m[0] . '">' . $m[0] . '</a>',
				$chu,
				1
			);
		}
		return '<span class="line">' . ( $so ? '<span class="ln" aria-hidden="true">' . sprintf( '%02d', $so ) . '</span>' : '' ) . $chu . '</span>';
	}

	/**
	 * Nhãn mức kiểm (D-093): nói điều có, chi tiết trong ⓘ và khối "Lời này từ đâu?". Máy không tự đặt "đã đối chiếu".
	 *
	 * @param array{sach:string,trang?:string}|null $doi_chieu người biên soạn điền
	 * @param string $tu_lieu đường dẫn khối "Lời này từ đâu?" (ngoài trang bài, ví dụ Câu hôm nay: đường dẫn bài + #tu-lieu)
	 */
	public static function muc_kiem( ?array $doi_chieu, bool $gon = false, string $tu_lieu = '#tu-lieu' ): string {
		$co   = null !== $doi_chieu && '' !== ( $doi_chieu['sach'] ?? '' );
		$nhan = $co
			? '<span class="pill checked">' . self::icon( 'check' ) . 'Đã đối chiếu: ' . esc_html( $doi_chieu['sach'] ) . ( empty( $doi_chieu['trang'] ) ? '' : ', tr. ' . esc_html( $doi_chieu['trang'] ) ) . '</span>'
			: '<span class="pill">' . self::icon( 'book' ) . 'Bản lưu truyền phổ biến</span>';
		if ( $gon ) {
			return '<div class="level">' . $nhan . '</div>';
		}
		$giai = $co
			? 'Lời khớp với bản in trong sách, đúng trang đã ghi. Người biên soạn đối chiếu; máy không tự gắn nhãn này.'
			: 'Lời ghi theo cách người Việt thường đọc, hát hiện nay. Thư viện đang đối chiếu dần với sách in; khi xong, nhãn này ghi tên sách và số trang.';
		$lien = ( $co ? '' : self::lien_ket( 'gop', 'Có sách? Gửi số trang' ) . ' · ' ) . '<a href="' . esc_url( $tu_lieu ) . '">Lời này từ đâu?</a> · ' . self::lien_ket( 'phuong-phap', 'Cách biên soạn' );
		return '<div class="level">' . $nhan . '<details class="vi-sao"><summary aria-label="' . ( $co ? 'Đã đối chiếu nghĩa là gì?' : 'Bản lưu truyền phổ biến là gì?' ) . '">' . self::icon( 'info' ) . '</summary><div class="vi-sao-pop"><p>' . $giai . '</p><p>' . $lien . '</p></div></details></div>';
	}

	/** Ý nghĩa (tên hiển thị của lớp hieu_nhanh — D-093). */
	/** $nut: nút Sửa (HTML an toàn, chỉ ở chế độ sửa). */
	public static function y_nghia( string $chu, bool $ai = false, string $tieu_de = 'Ý nghĩa', string $nut = '' ): string {
		return '<div class="meaning"><div class="meaning-head"><h2>' . esc_html( $tieu_de ) . '</h2>' . $nut . '</div><p class="y-nghia-chu">' . esc_html( $chu ) . '</p>'
			. ( $ai ? '<p class="ai-note">Có hỗ trợ của AI, đã được người biên soạn duyệt.</p>' : '' ) . '</div>';
	}

	/** Nút Sao chép lời; $the_loai ghi kèm khi nút nằm ngoài trang bài (Câu hôm nay). */
	public static function nut_chep( string $url, string $the_loai = '' ): string {
		return '<button type="button" class="nut-chep no-print" data-action="copy-loi" data-url="' . esc_url( $url ) . '"' . ( '' === $the_loai ? '' : ' data-the-loai="' . esc_attr( $the_loai ) . '"' ) . ' aria-label="Sao chép lời kèm đường dẫn bài" title="Sao chép lời kèm đường dẫn bài">' . self::icon( 'copy' ) . '<span class="nhan">Sao chép</span></button>';
	}

	public static function the_loi( string $than, string $url, string $nhan = 'Lời', string $aria = 'Lời và ý nghĩa', string $nut = '' ): string {
		return '<section class="verse-card' . ( '' === $nut ? '' : ' dang-sua' ) . '" aria-label="' . esc_attr( $aria ) . '"><div class="the-dau"><span class="eyebrow">' . esc_html( $nhan ) . '</span><span class="row">' . $nut . self::nut_nghe() . self::nut_chep( $url ) . '</span></div>' . $than . '</section>';
	}

	/** Nút Nghe giọng máy (D-095): doc.js chỉ cho hiện khi máy người đọc có giọng tiếng Việt; không tự phát. */
	public static function nut_nghe(): string {
		return '<button type="button" class="nut-chep no-print" data-action="nghe" aria-pressed="false" hidden aria-label="Nghe lời bằng giọng đọc có sẵn trên máy" title="Nghe lời bằng giọng đọc có sẵn trên máy">' . self::icon( 'nghe' ) . '<span class="nhan">Nghe</span></button>';
	}

	private static function nut( string $viec, string $hinh, string $nhan, string $lop = '', string $them = '' ): string {
		return '<button type="button" class="btn ' . esc_attr( $lop ) . '" data-action="' . esc_attr( $viec ) . '"' . $them . '>' . self::icon( $hinh ) . '<span class="nhan">' . esc_html( $nhan ) . '</span></button>';
	}

	/**
	 * Công cụ đọc: cột dính bên phải (máy tính), hàng 4 nút dưới lời (điện thoại).
	 *
	 * @param array{ma:string,tieu_de:string,ten_the_loai:string,url:string} $tp
	 */
	public static function cong_cu( array $tp ): string {
		$du = ' data-id="' . esc_attr( $tp['ma'] ) . '" data-tieu-de="' . esc_attr( $tp['tieu_de'] ) . '" data-the-loai="' . esc_attr( $tp['ten_the_loai'] ) . '" data-url="' . esc_url( $tp['url'] ) . '"';
		$co = '';
		foreach ( array( '22' => 'Vừa', '28' => 'Lớn', '34' => 'Rất lớn' ) as $n => $t ) {
			$co .= '<button type="button" data-action="size" data-size="' . $n . '" aria-pressed="' . ( '22' === (string) $n ? 'true' : 'false' ) . '">' . $t . '</button>';
		}
		return '<aside class="work-rail no-print" aria-label="Công cụ đọc"><div class="rail-card">'
			. self::nut( 'cite', 'quote', 'Trích dẫn', 'primary', $du )
			. self::nut( 'save', 'save', 'Lưu', '', $du . ' aria-pressed="false"' )
			. self::nut( 'share', 'share', 'Chia sẻ', '', $du )
			. self::nut( 'present', 'present', 'Trình chiếu', 'chi-rong', $du )
			. self::nut( 'aa', 'aa', 'Cỡ chữ', 'chi-hep', ' aria-label="Cỡ chữ và sáng tối"' )
			. '<p class="rail-label chi-rong" id="co-chu-' . esc_attr( $tp['ma'] ) . '">Cỡ chữ lời</p><div class="size-group chi-rong" role="group" aria-labelledby="co-chu-' . esc_attr( $tp['ma'] ) . '">' . $co . '</div>'
			. '<p class="rail-note chi-rong">Lời luôn giữ mỗi câu một dòng; cỡ lớn tự co cho vừa màn hình.</p></div></aside>';
	}

	/** @param array<int,array{0:string,1?:bool,2?:string}> $nhan [chữ, nhạt?, đường dẫn trong trang?] */
	public static function nhan_dau( array $nhan ): string {
		$html = '<div class="pills">';
		foreach ( $nhan as $n ) {
			$lop   = 'tag-pill' . ( empty( $n[1] ) ? '' : ' plain' );
			$html .= empty( $n[2] ) ? '<span class="' . $lop . '">' . esc_html( $n[0] ) . '</span>' : '<a class="' . $lop . '" href="' . esc_attr( $n[2] ) . '">' . esc_html( $n[0] ) . '</a>';
		}
		return $html . '</div>';
	}

	/** Số liệu dưới tiêu đề, chỉ khi đủ ngưỡng (D-069): đọc ≥ 10, chia sẻ và trích dẫn ≥ 3. */
	public static function so_lieu( array $s ): string {
		$o = array();
		if ( ( $s['doc'] ?? 0 ) >= 10 ) {
			$o[] = self::icon( 'eye' ) . number_format( (int) $s['doc'], 0, ',', '.' ) . ' lượt đọc';
		}
		if ( ( $s['chia'] ?? 0 ) >= 3 ) {
			$o[] = self::icon( 'share' ) . (int) $s['chia'] . ' chia sẻ';
		}
		if ( ( $s['trich'] ?? 0 ) >= 3 ) {
			$o[] = self::icon( 'quote' ) . (int) $s['trich'] . ' trích dẫn';
		}
		return implode( '', array_map( static fn( string $x ): string => '<span>' . $x . '</span>', $o ) );
	}

	public static function khoi( string $tieu_de, string $than, string $lop = '', string $id = '', string $nut = '' ): string {
		$dau = '<h2>' . esc_html( $tieu_de ) . '</h2>';
		return '<section class="block ' . esc_attr( $lop ) . '"' . ( $id ? ' id="' . esc_attr( $id ) . '"' : '' ) . '>' . ( '' === $nut ? $dau : '<div class="block-dau">' . $dau . $nut . '</div>' ) . $than . '</section>';
	}

	public static function khoi_gon( string $tieu_de, string $than, string $id, string $mau ): string {
		return '<details class="block tint-' . esc_attr( $mau ) . '" id="' . esc_attr( $id ) . '"><summary>' . $tieu_de . '</summary><div class="detail-body">' . $than . '</div></details>';
	}
}
