<?php
/**
 * Trích lời từ HTML — TẤT ĐỊNH, KHÔNG QUA AI. Cùng cách làm với bộ trích của kho thô
 * (ca-dao-kho-tho/scripts/lib/trich.mjs, đã kiểm 20/20 bài khớp từng ký tự):
 * <br> thành xuống dòng, bỏ dấu chú thích nhưng nhớ vị trí, bỏ ảnh, giải mã thực thể HTML, NFC,
 * gộp khoảng trắng của mã nguồn HTML như trình duyệt, cắt khoảng trắng đầu/cuối dòng.
 * Không sửa chính tả, không đổi kiểu bỏ dấu, không bỏ dấu câu.
 */

defined( 'ABSPATH' ) || exit;

/** Bỏ ảnh và chú thích ảnh: không lấy ảnh của nguồn (docs/24 mục 5). */
function cadao_bo_anh( string $html ): string {
	$html = preg_replace( '~<div[^>]*class="[^"]*wp-caption[^"]*"[^>]*>.*?</div>~is', '', $html );
	$html = preg_replace( '~<figure\b.*?</figure>~is', '', $html );
	return preg_replace( '~<img\b[^>]*>~i', '', $html );
}

function cadao_nfc( string $s ): string {
	if ( class_exists( 'Normalizer' ) ) {
		$n = Normalizer::normalize( $s, Normalizer::FORM_C );
		if ( is_string( $n ) ) {
			return $n;
		}
	}
	return $s;
}

/**
 * Cắt khoảng trắng đầu/cuối như String.prototype.trim() của JavaScript (bộ trích của kho thô):
 * gồm dấu cách không ngắt và mọi khoảng trắng Unicode. KHÔNG dùng trim() của PHP ở đây —
 * nó xóa cả ký tự \0 dùng để đánh dấu vị trí chú thích, và không xóa dấu cách không ngắt.
 */
// Đúng tập của JavaScript: TAB, LF, VT, FF, CR, khoảng trắng Unicode (Zs), U+FEFF, U+2028, U+2029.
// Không dùng \v của PCRE: nó khớp cả U+0085, ký tự JavaScript giữ nguyên (đã gặp ở một bài thật).
const CADAO_KHOANG_TRANG = '[\t\n\x0B\f\r\p{Zs}\x{FEFF}\x{2028}\x{2029}]';

function cadao_cat( string $s ): string {
	return preg_replace( '/^' . CADAO_KHOANG_TRANG . '+|' . CADAO_KHOANG_TRANG . '+$/u', '', $s );
}

function cadao_cat_dau( string $s ): string {
	return preg_replace( '/^' . CADAO_KHOANG_TRANG . '+/u', '', $s );
}

function cadao_giai_ma( string $s ): string {
	return html_entity_decode( $s, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

/**
 * Lời từ một khối HTML (nội dung bài hoặc một dị bản).
 *
 * @return array{loi: string, chu_thich: array<int, array{ma: int, dong: int, cot: int}>}
 */
function cadao_trich_loi( string $html ): array {
	$mo   = "\x00";
	$br   = "\x01";
	$doan = "\x02";
	$h    = cadao_bo_anh( $html );
	$h    = preg_replace( '~<script\b.*?</script>~is', '', $h );
	$h    = preg_replace_callback(
		'~<a\b[^>]*class="footnote[^"]*"[^>]*>.*?</a>~is',
		static function ( $m ) use ( $mo ) {
			return preg_match( '~data-ref-id="(\d+)"~', $m[0], $id ) ? $mo . $id[1] . $mo : '';
		},
		$h
	);
	$h = preg_replace( '~<br\s*/?>~i', $br, $h );
	$h = preg_replace( '~</p>\s*<p\b[^>]*>~i', $doan, $h );
	$h = preg_replace( '~<[^>]+>~', '', $h );
	$h = preg_replace( '~[ \t\r\n\f]+~', ' ', $h );
	$h = cadao_nfc( cadao_giai_ma( $h ) );
	$h = str_replace( $doan, $br . $br, $h );

	$dong = array_map( 'cadao_cat', explode( $br, $h ) );
	while ( $dong && '' === $dong[0] ) {
		array_shift( $dong );
	}
	while ( $dong && '' === end( $dong ) ) {
		array_pop( $dong );
	}

	$chu_thich = array();
	$sach      = array();
	foreach ( array_values( $dong ) as $i => $d ) {
		$ra   = '';
		$tren = array();
		foreach ( explode( $mo, $d ) as $j => $phan ) {
			if ( 1 === $j % 2 ) {
				$tren[] = array( 'ma' => (int) $phan, 'dong' => $i + 1, 'cot' => mb_strlen( $ra ) );
			} else {
				$ra .= $phan;
			}
		}
		$dau = mb_strlen( $ra ) - mb_strlen( cadao_cat_dau( $ra ) );
		$ra  = cadao_cat( $ra );
		foreach ( $tren as $c ) {
			$c['cot']    = min( max( $c['cot'] - $dau, 0 ), mb_strlen( $ra ) );
			$chu_thich[] = $c;
		}
		$sach[] = $ra;
	}
	return array( 'loi' => implode( "\n", $sach ), 'chu_thich' => $chu_thich );
}

/** Văn bản thường từ HTML (tiêu đề, chú thích): bỏ thẻ, giữ xuống dòng giữa các khối. */
function cadao_van_ban( string $html ): string {
	$br = "\x01";
	$h  = cadao_bo_anh( $html );
	$h  = preg_replace( '~<br\s*/?>~i', $br, $h );
	$h  = preg_replace( '~</(p|div|li|h\d)>~i', $br, $h );
	$h  = preg_replace( '~<[^>]+>~', '', $h );
	$h  = preg_replace( '~[ \t\r\n\f]+~', ' ', $h );
	$h  = cadao_nfc( cadao_giai_ma( $h ) );
	return implode( "\n", array_filter( array_map( 'cadao_cat', explode( $br, $h ) ), 'strlen' ) );
}

/**
 * Đọc một trang HTML của Ca dao Mẹ (T-073; như docTrang của kho thô): mọi bài (<article>) trên trang — bài chính
 * và các bài liên quan — kèm dị bản, chủ đề; câu đố tách câu hỏi, đáp án khỏi lời; và mọi chú thích của trang.
 *
 * @return array{bai: array<int, array>, chu_thich: array<int, array{ma: int, tu: string, noi_dung: string}>}
 */
function cadao_doc_trang( string $html ): array {
	$bai = array();
	preg_match_all( '~<article id="post-(\d+)"[^>]*>(.*?)</article>~s', $html, $ds, PREG_SET_ORDER );
	foreach ( $ds as $m ) {
		$a = $m[2];
		if ( ! preg_match( '~<h1 class="post-title[^"]*">\s*<a href="([^"]+)"[^>]*>(.*?)</a>~s', $a, $td ) ) {
			continue;
		}
		$mo = '<div class="entry-content">';
		$bd = strpos( $a, $mo );
		if ( false === $bd ) {
			continue;
		}
		$sau      = substr( $a, $bd + strlen( $mo ) );
		$noi_dung = preg_match( '~<section class="variants">|<aside class="post-data~', $sau, $kt, PREG_OFFSET_CAPTURE ) ? substr( $sau, 0, $kt[0][1] ) : $sau;
		$noi_dung = preg_replace( '~</div>\s*$~', '', $noi_dung );
		// Câu đố: sau lời có khối câu hỏi (<div class="q">) và đáp án (<div class="a">) — tách riêng, không gộp vào lời
		$cau_hoi = null;
		$dap_an  = null;
		$vt      = strpos( $noi_dung, '<div class="q">' );
		if ( false !== $vt ) {
			$duoi     = substr( $noi_dung, $vt );
			$cau_hoi  = preg_match( '~<div class="q">(.*?)</div>~s', $duoi, $x ) ? ( cadao_van_ban( $x[1] ) ?: null ) : null;
			$dap_an   = preg_match( '~<div class="a">(.*?)</div>~s', $duoi, $x ) ? ( cadao_van_ban( $x[1] ) ?: null ) : null;
			$noi_dung = substr( $noi_dung, 0, $vt );
		}
		$di_ban = array();
		if ( preg_match( '~<section class="variants">(.*?)</section>~s', $a, $kdb ) ) {
			preg_match_all( '~<li class="variant"[^>]*>(.*?)</li>~s', $kdb[1], $dsdb );
			$di_ban = array_map( 'cadao_trich_loi', $dsdb[1] );
		}
		$chu_de = array();
		if ( preg_match( '~<ul class="post-categories">(.*?)</ul>~s', $a, $kcd ) ) {
			preg_match_all( '~<a [^>]*>(.*?)</a>~s', $kcd[1], $dscd );
			$chu_de = array_values( array_filter( array_map( 'cadao_van_ban', $dscd[1] ), 'strlen' ) );
		}
		$chinh = cadao_trich_loi( $noi_dung );
		$bai[] = array(
			'ma_goc'                  => (int) $m[1],
			'url'                     => $td[1],
			'tieu_de'                 => cadao_van_ban( $td[2] ),
			'loi'                     => $chinh['loi'],
			'vi_tri_chu_thich'        => $chinh['chu_thich'],
			'di_ban'                  => array_column( $di_ban, 'loi' ),
			'vi_tri_chu_thich_di_ban' => array_column( $di_ban, 'chu_thich' ),
			'chu_de_goc'              => $chu_de,
			'cau_hoi'                 => $cau_hoi,
			'dap_an'                  => $dap_an,
		);
	}
	return array( 'bai' => $bai, 'chu_thich' => cadao_doc_chu_thich( $html ) );
}

/** Chú thích trong <section class="ref"> (hoặc trang /chu-thich/chi-tiet/<mã>): mã → { ma, tu (có thể rỗng), noi_dung }. */
function cadao_doc_chu_thich( string $html ): array {
	$ra = array();
	preg_match_all( '~<li data-ref-id="(\d+)" id="note-\d+">(.*?)</dl>~s', $html, $ds, PREG_SET_ORDER );
	foreach ( $ds as $m ) {
		if ( ! preg_match( '~<dd>(.*)</dd>~s', $m[2], $dd ) ) {
			continue;
		}
		$dt                  = preg_match( '~<dt>(.*?)</dt>~s', $m[2], $x ) ? $x[1] : '';
		$ra[ (int) $m[1] ] = array( 'ma' => (int) $m[1], 'tu' => cadao_van_ban( $dt ), 'noi_dung' => cadao_van_ban( $dd[1] ) );
	}
	return $ra;
}

/** Sitemap: [{ url, lastmod }]. */
function cadao_doc_sitemap( string $xml ): array {
	preg_match_all( '~<url>\s*<loc>([^<]+)</loc>(?:\s*<lastmod>([^<]+)</lastmod>)?~', $xml, $ds, PREG_SET_ORDER );
	return array_map( static fn( $m ) => array( 'url' => cadao_giai_ma( cadao_cat( $m[1] ) ), 'lastmod' => isset( $m[2] ) ? cadao_cat( $m[2] ) : null ), $ds );
}
