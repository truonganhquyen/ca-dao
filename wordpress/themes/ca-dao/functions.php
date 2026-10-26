<?php
/**
 * Theme con "Kho tàng Ca dao" (T-072, docs/20): màu, phông, trang bài. Dữ liệu và chức năng nằm ở plugin ca-dao;
 * theme chỉ lo cách hiển thị.
 */

defined( 'ABSPATH' ) || exit;

/** Lục bát: thụt lề câu lục so với câu bát (docs/20 mục 1.2). Đổi sau khi hỏi người thử (Q-006, Mốc B). */
const CADAO_LUC_BAT_THUT_LE = true;

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'ca-dao', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
	}
);
add_action(
	'after_setup_theme',
	static function () {
		add_editor_style( 'style.css' );
	}
);

/**
 * Bài lục bát? Đa số cặp dòng (lẻ, chẵn) có 6 rồi 8 âm tiết. Âm tiết đếm theo khoảng trắng, như site cũ.
 * (Plugin có cadao_the_tho() nhận đủ các thể; hàm này giữ cho theme chạy được khi chưa có plugin.)
 *
 * @param string[] $dong các dòng (chữ thường, không thẻ)
 */
function cadao_la_luc_bat( array $dong ): bool {
	$dong = array_values( array_filter( $dong, static fn( $d ) => '' !== trim( $d ) ) );
	if ( count( $dong ) < 2 ) {
		return false;
	}
	$dem   = static fn( $d ) => count( preg_split( '/\s+/u', trim( $d ), -1, PREG_SPLIT_NO_EMPTY ) );
	$cap   = intdiv( count( $dong ), 2 );
	$trung = 0;
	for ( $i = 0; $i < $cap; $i++ ) {
		$trung += ( 6 === $dem( $dong[ 2 * $i ] ) && 8 === $dem( $dong[ 2 * $i + 1 ] ) ) ? 1 : 0;
	}
	return $trung * 4 >= $cap * 3; // ≥ 3/4 số cặp
}

/**
 * Trước khi dựng nội dung bài: biết khối Thơ nào là dị bản (đứng sau tiêu đề "Dị bản"), lời chính, và các chú thích
 * (từ → số thứ tự) để từ có chú thích trong lời thành nút chạm (T-082, T-083).
 */
function cadao_chuan_bi_bai( string $noi_dung ): array {
	$ra    = array( 'loi' => '', 'di_ban' => array(), 'chu_thich' => array() );
	$sau_di_ban = false;
	$so_tho     = 0;
	$so_ct      = 0;
	foreach ( parse_blocks( $noi_dung ) as $khoi ) {
		if ( 'core/heading' === $khoi['blockName'] ) {
			$sau_di_ban = 'Dị bản' === trim( wp_strip_all_tags( $khoi['innerHTML'] ) );
		} elseif ( 'core/verse' === $khoi['blockName'] ) {
			if ( 0 === $so_tho && function_exists( 'cadao_loi_tu_html_tho' ) ) {
				$ra['loi'] = cadao_loi_tu_html_tho( $khoi['innerHTML'] );
			}
			if ( $sau_di_ban && $so_tho > 0 ) {
				$ra['di_ban'][ $so_tho ] = count( $ra['di_ban'] ) + 1;
			}
			++$so_tho;
		} elseif ( 'core/list' === $khoi['blockName'] && str_contains( (string) ( $khoi['attrs']['className'] ?? '' ), 'cadao-chu-thich' ) ) {
			preg_match_all( '~<li\b[^>]*>(.*?)</li>~s', $khoi['innerHTML'] . implode( '', array_column( $khoi['innerBlocks'], 'innerHTML' ) ), $li );
			foreach ( $li[1] as $o ) {
				++$so_ct;
				if ( preg_match( '~^\s*<strong>(.*?)</strong>~s', $o, $tu ) ) {
					$chu = trim( html_entity_decode( wp_strip_all_tags( $tu[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
					if ( '' !== $chu && ! isset( $ra['chu_thich'][ $chu ] ) ) {
						$ra['chu_thich'][ $chu ] = $so_ct;
					}
				}
			}
		}
	}
	return $ra;
}

/** Trạng thái của bài đang dựng (một bài mỗi lần the_content): đếm khối Thơ, mục chú thích lại từ đầu mỗi lần. */
function cadao_bai_dang_dung(): ?array {
	return $GLOBALS['cadao_bai_dung'] ?? null;
}

add_filter(
	'the_content',
	static function ( $noi_dung ) {
		$dung = is_singular( 'cdtn' ) && get_the_ID() === get_queried_object_id() && ! doing_filter( 'get_the_excerpt' );
		$GLOBALS['cadao_bai_dung'] = $dung ? cadao_chuan_bi_bai( $noi_dung ) + array( 'tho' => -1, 'ct' => 0 ) : null;
		return $noi_dung;
	},
	8 // trước do_blocks (9)
);

/**
 * Dị bản: chữ khác lời chính được tô (so theo âm tiết; bỏ qua chữ hoa, dấu câu). Một dòng HTML đã thoát → dòng có
 * <mark>. Dòng có thẻ HTML giữ nguyên. Bài quá dài (so quá tốn) thì không tô.
 *
 * @param string[] $dong_html các dòng của dị bản (đã thoát HTML)
 * @return string[]
 */
function cadao_to_cho_khac( string $loi_chinh, array $dong_html ): array {
	$khoa = static fn( string $t ) => function_exists( 'cadao_chuan_loi' ) ? cadao_chuan_loi( $t ) : mb_strtolower( preg_replace( '/[^\p{L}\p{N}]/u', '', $t ) );
	$am   = static fn( string $d ) => array_values( array_filter( array_map( $khoa, preg_split( '/\s+/u', $d, -1, PREG_SPLIT_NO_EMPTY ) ), 'strlen' ) );
	$chinh = array_values( array_filter( array_map( $am, explode( "\n", $loi_chinh ) ) ) ); // các dòng có chữ
	$tu    = array(); // theo dòng có chữ của dị bản: [[dòng, vị trí trong dòng, khóa], …]
	$phan  = array();
	foreach ( $dong_html as $i => $d ) {
		$phan[ $i ] = str_contains( $d, '<' ) ? null : preg_split( '/(\s+)/u', html_entity_decode( $d, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), -1, PREG_SPLIT_DELIM_CAPTURE );
		$dong       = array();
		foreach ( (array) $phan[ $i ] as $j => $t ) {
			if ( 0 === $j % 2 && '' !== $khoa( $t ) ) {
				$dong[] = array( $i, $j, $khoa( $t ) );
			}
		}
		if ( $dong ) {
			$tu[] = $dong;
		}
	}
	// Cùng số dòng: so từng dòng với dòng tương ứng (dễ hiểu hơn); khác số dòng: so cả bài
	$cap = count( $tu ) === count( $chinh ) ? array_map( null, $tu, $chinh ) : array( array( array_merge( ...( $tu ?: array( array() ) ) ), array_merge( ...( $chinh ?: array( array() ) ) ) ) );
	$khac = array();
	foreach ( $cap as [ $x, $y ] ) {
		$n = count( $x );
		$m = count( $y );
		if ( ! $n || $n * $m > 400000 ) {
			continue;
		}
		// Dãy con chung dài nhất (LCS) theo âm tiết; chữ của dị bản không nằm trong dãy chung là chữ khác
		$L = array_fill( 0, $n + 1, array_fill( 0, $m + 1, 0 ) );
		for ( $a = $n - 1; $a >= 0; $a-- ) {
			for ( $b = $m - 1; $b >= 0; $b-- ) {
				$L[ $a ][ $b ] = $x[ $a ][2] === $y[ $b ] ? $L[ $a + 1 ][ $b + 1 ] + 1 : max( $L[ $a + 1 ][ $b ], $L[ $a ][ $b + 1 ] );
			}
		}
		for ( $a = 0, $b = 0; $a < $n; ) {
			if ( $b < $m && $x[ $a ][2] === $y[ $b ] ) {
				++$a;
				++$b;
			} elseif ( $b < $m && $L[ $a ][ $b + 1 ] >= $L[ $a + 1 ][ $b ] ) {
				++$b;
			} else {
				$khac[ $x[ $a ][0] ][ $x[ $a ][1] ] = true;
				++$a;
			}
		}
	}
	$ra = array();
	foreach ( $dong_html as $i => $d ) {
		if ( null === $phan[ $i ] || empty( $khac[ $i ] ) ) {
			$ra[ $i ] = $d;
			continue;
		}
		$s = '';
		foreach ( $phan[ $i ] as $j => $t ) {
			$s .= isset( $khac[ $i ][ $j ] ) ? '<mark class="cadao-khac">' . esc_html( $t ) . '</mark>' : esc_html( $t );
		}
		$ra[ $i ] = $s;
	}
	return $ra;
}

/**
 * Lời chính: từ có chú thích thành liên kết tới chú thích dưới lời (tắt JavaScript vẫn dùng được); JavaScript mở ô
 * chú thích khi chạm (T-082). Mỗi từ chỉ gắn ở lần xuất hiện đầu tiên, từ dài trước.
 *
 * @param string[]           $dong_html các dòng (đã thoát HTML)
 * @param array<string, int> $chu_thich từ → số thứ tự chú thích
 * @return string[]
 */
function cadao_gan_tu_chu_thich( array $dong_html, array $chu_thich ): array {
	uksort( $chu_thich, static fn( $a, $b ) => mb_strlen( $b ) <=> mb_strlen( $a ) );
	foreach ( $chu_thich as $tu => $so ) {
		$mau = '/(?<![\p{L}\p{M}\p{N}])(' . preg_quote( esc_html( $tu ), '/' ) . ')(?![\p{L}\p{M}\p{N}])/iu';
		foreach ( $dong_html as $i => $d ) {
			// Không gắn vào chỗ đã nằm trong một liên kết khác
			$phan = preg_split( '~(<a\b.*?</a>)~s', $d, -1, PREG_SPLIT_DELIM_CAPTURE );
			$xong = false;
			foreach ( $phan as $k => $p ) {
				if ( 0 === $k % 2 && ! $xong ) {
					$moi = preg_replace( $mau, '<a class="cadao-tu" href="#cadao-ct-' . (int) $so . '" data-ct="' . (int) $so . '">$1<span class="screen-reader-text"> (xem chú thích)</span></a>', $p, 1, $dem );
					if ( $dem ) {
						$phan[ $k ] = $moi;
						$xong       = true;
					}
				}
			}
			if ( $xong ) {
				$dong_html[ $i ] = implode( '', $phan );
				continue 2;
			}
		}
	}
	return $dong_html;
}

/** Bài dài (T-090): từ 41 dòng; hiện sẵn các đoạn bắt đầu trong 30 dòng đầu, còn lại sau nút "Đọc toàn bài". */
const CADAO_BAI_DAI      = 40;
const CADAO_BAI_DAI_HIEN = 30;
const CADAO_BAI_DAI_DOAN = 20; // bài không chia khổ: mỗi đoạn khoảng 20 dòng

/**
 * Chia bài dài thành đoạn: theo khổ (dòng trống); không có khổ thì mỗi đoạn ~20 dòng.
 *
 * @param string[] $chu các dòng (chữ thường, không thẻ)
 * @return array{bat_dau: array<int, int>, dong_dau: array<int, int>, dong_cuoi: array<int, int>} chỉ số dòng → số đoạn; số đoạn → dòng có chữ đầu / cuối (đếm từ 1)
 */
function cadao_chia_doan( array $chu ): array {
	$ra   = array( 'bat_dau' => array(), 'dong_dau' => array(), 'dong_cuoi' => array() );
	// Độ dài từng khổ (khổ cách nhau dòng trống); khổ dài quá 40 dòng thì chia tiếp ~20 dòng một đoạn
	$dai_kho = array();
	$kho     = 0;
	foreach ( $chu as $k => $d ) {
		if ( '' === trim( $d ) ) {
			$kho = $k + 1;
			continue;
		}
		$dai_kho[ $kho ] = ( $dai_kho[ $kho ] ?? 0 ) + 1;
	}
	$so    = 0;
	$thu   = 0; // dòng có chữ thứ mấy
	$moi   = true;
	$trong = 0;
	$kho   = 0;
	foreach ( $chu as $k => $d ) {
		if ( '' === trim( $d ) ) {
			$moi = true;
			$kho = $k + 1;
			continue;
		}
		++$thu;
		if ( $moi || ( $dai_kho[ $kho ] > CADAO_BAI_DAI && $trong >= CADAO_BAI_DAI_DOAN ) ) {
			++$so;
			$ra['bat_dau'][ $k ]   = $so;
			$ra['dong_dau'][ $so ] = $thu;
			$trong                 = 0;
			$moi                   = false;
		}
		++$trong;
		$ra['dong_cuoi'][ $so ] = $thu;
	}
	return $ra;
}

/** Khung bài dài: số dòng, thời gian đọc, mục lục đoạn; JavaScript thu gọn phần sau và nhớ chỗ đang đọc. */
function cadao_khung_bai_dai( string $tho, array $doan, int $so_dong, int $so_am ): string {
	$hien = 0;
	foreach ( $doan['dong_dau'] as $so => $dau ) {
		if ( $dau <= CADAO_BAI_DAI_HIEN ) {
			$hien = $doan['dong_cuoi'][ $so ];
		}
	}
	$muc = '';
	foreach ( $doan['dong_dau'] as $so => $dau ) {
		$muc .= '<li><a href="#cadao-doan-' . $so . '">Đoạn ' . $so . '</a> <span class="cadao-muc-luc-dong">· dòng ' . $dau . '–' . $doan['dong_cuoi'][ $so ] . '</span></li>';
	}
	$phut = max( 1, (int) ceil( $so_am / 200 ) ); // ~200 âm tiết mỗi phút
	return '<div class="cadao-bai-dai" data-bai="' . (int) get_the_ID() . '" data-con-lai="' . max( 0, $so_dong - $hien ) . '">'
		. '<p class="cadao-bai-dai-tom">' . number_format_i18n( $so_dong ) . ' dòng · ' . count( $doan['dong_dau'] ) . ' đoạn · khoảng ' . $phut . ' phút đọc</p>'
		. '<details class="cadao-muc-luc"><summary>Mục lục</summary><ol>' . $muc . '</ol></details>'
		. $tho . '</div>';
}

/**
 * Khối Thơ ngoài trang: mỗi dòng một <span class="cadao-dong">; trình bày theo thể (lục bát, song thất lục bát: câu lục
 * thụt lề); dị bản có nhãn "Dị bản N" và chữ khác lời chính được tô; lời chính có từ chú thích bấm được.
 * Chỉ đổi cách trình bày; chữ giữ nguyên (đã thoát HTML khi lưu).
 */
add_filter(
	'render_block_core/verse',
	static function ( $html ) {
		if ( ! preg_match( '~^(\s*<pre\b)([^>]*>)(.*)(</pre>\s*)$~s', $html, $m ) ) {
			return $html;
		}
		$bai = cadao_bai_dang_dung();
		$i   = $bai ? ++$GLOBALS['cadao_bai_dung']['tho'] : -1;
		$dong = preg_split( '~<br\s*/?>|\n~i', $m[3] );
		$chu  = array_map( static fn( $d ) => wp_strip_all_tags( html_entity_decode( $d ) ), $dong );
		$the  = function_exists( 'cadao_the_tho' ) ? cadao_the_tho( implode( "\n", $chu ) ) : ( cadao_la_luc_bat( $chu ) ? 'luc-bat' : 'tu-do' );
		$so_dong = array_values( array_filter( array_keys( $chu ), static fn( $k ) => '' !== trim( $chu[ $k ] ) ) );
		$la_di_ban = $bai && isset( $bai['di_ban'][ $i ] );
		if ( $la_di_ban && '' !== $bai['loi'] ) {
			$dong = cadao_to_cho_khac( $bai['loi'], $dong );
		} elseif ( $bai && 0 === $i && $bai['chu_thich'] ) {
			$dong = cadao_gan_tu_chu_thich( $dong, $bai['chu_thich'] );
		}
		$dai = $bai && 0 === $i && count( $so_dong ) > CADAO_BAI_DAI; // bài dài (T-090): chia đoạn
		$doan = $dai ? cadao_chia_doan( $chu ) : array();
		$ra   = '';
		foreach ( $dong as $k => $d ) {
			$thu = array_search( $k, $so_dong, true ); // thứ tự trong các dòng có chữ
			$luc = CADAO_LUC_BAT_THUT_LE && false !== $thu && ( ( 'luc-bat' === $the && 0 === $thu % 2 ) || ( 'song-that-luc-bat' === $the && 2 === $thu % 4 ) );
			if ( $dai && isset( $doan['bat_dau'][ $k ] ) ) {
				$so  = $doan['bat_dau'][ $k ];
				$ra .= ( $so > 1 ? '</span>' : '' ) . '<span class="cadao-doan" id="cadao-doan-' . $so . '" data-so-doan="' . $so . '"' . ( $doan['dong_dau'][ $so ] > CADAO_BAI_DAI_HIEN ? ' data-sau' : '' ) . '>';
			}
			$so_le = $dai && false !== $thu && 0 === ( $thu + 1 ) % 5 ? ' data-so="' . ( $thu + 1 ) . '"' : ''; // số dòng bên lề, mỗi 5 dòng
			$ra   .= '<span class="cadao-dong' . ( $luc ? ' cadao-luc' : '' ) . '"' . $so_le . '>' . ( '' === trim( $d ) ? '&nbsp;' : $d ) . '</span>';
		}
		$ra .= $dai && $doan['bat_dau'] ? '</span>' : '';
		$lop = preg_replace( '~class="([^"]*)"~', 'class="$1 cadao-the-' . esc_attr( $the ) . '"', $m[2], 1, $co );
		$tho = $m[1] . ( $co ? $lop : ' class="cadao-the-' . esc_attr( $the ) . '"' . $m[2] ) . $ra . $m[4];
		if ( $dai ) {
			return cadao_khung_bai_dai( $tho, $doan, count( $so_dong ), array_sum( array_map( static fn( $d ) => count( preg_split( '/\s+/u', trim( $d ), -1, PREG_SPLIT_NO_EMPTY ) ), $chu ) ) );
		}
		return $la_di_ban ? '<div class="cadao-di-ban"><p class="cadao-nhan-di-ban">Dị bản ' . (int) $bai['di_ban'][ $i ] . '</p>' . $tho . '</div>' : $tho;
	}
);

/** Danh sách chú thích: mỗi mục có id để từ trong lời trỏ tới (T-082). */
add_filter(
	'render_block_core/list',
	static function ( $html, $khoi ) {
		if ( ! cadao_bai_dang_dung() || ! str_contains( (string) ( $khoi['attrs']['className'] ?? '' ), 'cadao-chu-thich' ) ) {
			return $html;
		}
		return preg_replace_callback(
			'~<li\b([^>]*)>(.*?)</li>~s',
			static function ( $m ) {
				$id  = ++$GLOBALS['cadao_bai_dung']['ct'];
				$dl  = '';
				$tem = '';
				// Mục từ trong từ điển (T-044), ảnh / bản đồ của địa danh đã xác nhận (T-087): ô chú thích dùng khi chạm từ
				if ( function_exists( 'cadao_them_cho_chu_thich' ) && preg_match( '~^\s*<strong>(.*?)</strong>\s*:\s*(.*)$~s', $m[2], $p ) ) {
					$giai  = static fn( $s ) => trim( html_entity_decode( wp_strip_all_tags( $s ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
					$them  = cadao_them_cho_chu_thich( $giai( $p[1] ), $giai( $p[2] ) );
					if ( $them ) {
						$dl  = ' data-tu-dien="' . esc_url( $them['url'] ) . '" data-so-bai="' . (int) $them['so_bai'] . '"' . ( isset( $them['id'] ) ? ' data-muc="' . (int) $them['id'] . '"' : '' );
						$tem = $them['them'] ? '<template class="cadao-ct-them">' . $them['them'] . '</template>' : '';
					}
				}
				return '<li id="cadao-ct-' . $id . '"' . $m[1] . $dl . '>' . $m[2] . $tem . '</li>';
			},
			$html
		);
	},
	10,
	2
);

/** Câu đố: đáp án đã xác nhận trên Wikidata có ảnh nhỏ kèm ghi công (T-088); không có ảnh thì không để chỗ trống. */
add_filter(
	'render_block_core/details',
	static function ( $html ) {
		if ( ! cadao_bai_dang_dung() || ! function_exists( 'cadao_them_cho_dap_an' ) || ! preg_match( '~<summary>Đáp án</summary>(.*)</details>~s', $html, $m ) ) {
			return $html;
		}
		$them = cadao_them_cho_dap_an( trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
		return '' === $them ? $html : str_replace( '</details>', '<div class="cadao-dap-an-them">' . $them . '</div></details>', $html );
	}
);

/** "Hiểu nhanh" (đoạn trích do dự án viết) ngay dưới lời, trước dị bản và chú thích (docs/20 mục 4.1, lớp 1). */
add_filter(
	'the_content',
	static function ( $noi_dung ) {
		if ( ! is_singular( 'cdtn' ) || get_the_ID() !== get_queried_object_id() || doing_filter( 'get_the_excerpt' ) || ! has_excerpt() ) {
			return $noi_dung;
		}
		$khoi = '<section class="cadao-hieu-nhanh" aria-labelledby="cadao-hieu-nhanh"><h2 id="cadao-hieu-nhanh">Hiểu nhanh</h2>'
			. wpautop( wp_kses_post( get_post()->post_excerpt ) ) . '</section>';
		$vt   = strpos( $noi_dung, '</pre>' );
		return false === $vt ? $khoi . $noi_dung : substr_replace( $noi_dung, '</pre>' . $khoi, $vt, strlen( '</pre>' ) );
	},
	20
);

/**
 * Thanh điều hướng dưới cho điện thoại (T-120, docs/20): Trang chủ · Tìm · Tất cả · Đã lưu. Chỉ hiện ở màn hình hẹp
 * (CSS); máy tính dùng menu trên. Trang đang xem có aria-current và vạch trên (không chỉ dựa vào màu).
 */
function cadao_thanh_duoi(): string {
	$icon = array(
		'nha' => '<path d="M3 11l9-7 9 7"/><path d="M5 10v10h14V10"/>',
		'tim' => '<circle cx="11" cy="11" r="6"/><path d="M20 20l-4.5-4.5"/>',
		'ds'  => '<path d="M8 6h12M8 12h12M8 18h12"/><path d="M4 6h.01M4 12h.01M4 18h.01"/>',
		'luu' => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
	);
	$muc  = array(
		array( 'Trang chủ', home_url( '/' ), 'nha' ),
		array( 'Tìm', home_url( '/tim/' ), 'tim' ),
		array( 'Tất cả', (string) get_post_type_archive_link( 'cdtn' ), 'ds' ),
	);
	if ( function_exists( 'cadao_url_da_luu' ) && cadao_url_da_luu() ) {
		$muc[] = array( 'Đã lưu', cadao_url_da_luu(), 'luu' );
	}
	$dang = untrailingslashit( (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ), PHP_URL_PATH ) );
	$li   = '';
	foreach ( $muc as list( $ten, $url, $i ) ) {
		if ( '' === $url ) {
			continue;
		}
		$day  = untrailingslashit( (string) wp_parse_url( $url, PHP_URL_PATH ) ) === $dang;
		$li  .= '<li><a href="' . esc_url( $url ) . '"' . ( $day ? ' aria-current="page"' : '' ) . '><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icon[ $i ] . '</svg><span>' . esc_html( $ten ) . '</span></a></li>';
	}
	return '<nav class="cadao-thanh-duoi" aria-label="Điều hướng nhanh"><ul>' . $li . '</ul></nav>';
}
add_action( 'wp_footer', static fn() => print( cadao_thanh_duoi() ), 5 ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát từng phần
