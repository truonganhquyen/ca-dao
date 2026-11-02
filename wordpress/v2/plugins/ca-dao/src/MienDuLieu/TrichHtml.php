<?php

namespace Phongdao\MienDuLieu;

/**
 * Trích lời từ HTML của nguồn — TẤT ĐỊNH, KHÔNG QUA AI (chuyển nguyên MVP inc/trich.php; cùng cách với bộ trích của kho
 * thô, đã kiểm 20/20 bài khớp từng ký tự): <br> thành xuống dòng, bỏ dấu chú thích nhưng nhớ vị trí, bỏ ảnh, giải mã
 * thực thể HTML, NFC, gộp khoảng trắng của mã nguồn như trình duyệt, cắt khoảng trắng đầu / cuối dòng.
 * Không sửa chính tả, không đổi kiểu bỏ dấu, không bỏ dấu câu. Không tải ảnh của nguồn.
 */
final class TrichHtml {

	/**
	 * Khoảng trắng đúng tập của String.prototype.trim() trong JavaScript: TAB, LF, VT, FF, CR, Zs, U+FEFF, U+2028, U+2029.
	 * Không dùng \v của PCRE (khớp cả U+0085, ký tự JavaScript giữ nguyên — đã gặp ở một bài thật).
	 */
	private const KHOANG_TRANG = '[\t\n\x0B\f\r\p{Zs}\x{FEFF}\x{2028}\x{2029}]';

	public static function bo_anh( string $html ): string {
		$html = (string) preg_replace( '~<div[^>]*class="[^"]*wp-caption[^"]*"[^>]*>.*?</div>~is', '', $html );
		$html = (string) preg_replace( '~<figure\b.*?</figure>~is', '', $html );
		return (string) preg_replace( '~<img\b[^>]*>~i', '', $html );
	}

	/** Cắt khoảng trắng như JavaScript (không dùng trim() của PHP: nó xoá \0 đánh dấu chú thích, giữ dấu cách không ngắt). */
	public static function cat( string $s ): string {
		return (string) preg_replace( '/^' . self::KHOANG_TRANG . '+|' . self::KHOANG_TRANG . '+$/u', '', $s );
	}

	private static function cat_dau( string $s ): string {
		return (string) preg_replace( '/^' . self::KHOANG_TRANG . '+/u', '', $s );
	}

	public static function giai_ma( string $s ): string {
		return html_entity_decode( $s, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}

	/**
	 * Lời từ một khối HTML (nội dung bài hoặc một dị bản).
	 *
	 * @return array{loi:string,chu_thich:array<int,array{ma:int,dong:int,cot:int}>}
	 */
	public static function loi( string $html ): array {
		$mo   = "\x00";
		$br   = "\x01";
		$doan = "\x02";
		$h    = self::bo_anh( $html );
		$h    = (string) preg_replace( '~<script\b.*?</script>~is', '', $h );
		$h    = (string) preg_replace_callback(
			'~<a\b[^>]*class="footnote[^"]*"[^>]*>.*?</a>~is',
			static fn( array $m ): string => preg_match( '~data-ref-id="(\d+)"~', $m[0], $id ) ? $mo . $id[1] . $mo : '',
			$h
		);
		$h = (string) preg_replace( '~<br\s*/?>~i', $br, $h );
		$h = (string) preg_replace( '~</p>\s*<p\b[^>]*>~i', $doan, $h );
		$h = (string) preg_replace( '~<[^>]+>~', '', $h );
		$h = (string) preg_replace( '~[ \t\r\n\f]+~', ' ', $h );
		$h = BoDau::nfc( self::giai_ma( $h ) );
		$h = str_replace( $doan, $br . $br, $h );

		$dong = array_map( array( self::class, 'cat' ), explode( $br, $h ) );
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
			$dau = mb_strlen( $ra ) - mb_strlen( self::cat_dau( $ra ) );
			$ra  = self::cat( $ra );
			foreach ( $tren as $c ) {
				$c['cot']    = min( max( $c['cot'] - $dau, 0 ), mb_strlen( $ra ) );
				$chu_thich[] = $c;
			}
			$sach[] = $ra;
		}
		return array( 'loi' => implode( "\n", $sach ), 'chu_thich' => $chu_thich );
	}

	/** Văn bản thường từ HTML (tiêu đề, chú thích): bỏ thẻ, giữ xuống dòng giữa các khối. */
	public static function van_ban( string $html ): string {
		$br = "\x01";
		$h  = self::bo_anh( $html );
		$h  = (string) preg_replace( '~<br\s*/?>~i', $br, $h );
		$h  = (string) preg_replace( '~</(p|div|li|h\d)>~i', $br, $h );
		$h  = (string) preg_replace( '~<[^>]+>~', '', $h );
		$h  = (string) preg_replace( '~[ \t\r\n\f]+~', ' ', $h );
		$h  = BoDau::nfc( self::giai_ma( $h ) );
		return implode( "\n", array_filter( array_map( array( self::class, 'cat' ), explode( $br, $h ) ), 'strlen' ) );
	}

	/**
	 * Một trang HTML của Ca dao Mẹ: mọi bài (<article>) — bài chính và bài liên quan — kèm dị bản, chủ đề; câu đố tách câu
	 * hỏi, đáp án khỏi lời; và mọi chú thích của trang.
	 *
	 * @return array{bai:array<int,array>,chu_thich:array<int,array{ma:int,tu:string,noi_dung:string}>}
	 */
	public static function trang( string $html ): array {
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
			$noi_dung = (string) preg_replace( '~</div>\s*$~', '', $noi_dung );
			// Câu đố: sau lời có khối câu hỏi (<div class="q">) và đáp án (<div class="a">) — tách riêng
			$cau_hoi = null;
			$dap_an  = null;
			$vt      = strpos( $noi_dung, '<div class="q">' );
			if ( false !== $vt ) {
				$duoi     = substr( $noi_dung, $vt );
				$cau_hoi  = preg_match( '~<div class="q">(.*?)</div>~s', $duoi, $x ) ? ( self::van_ban( $x[1] ) ?: null ) : null;
				$dap_an   = preg_match( '~<div class="a">(.*?)</div>~s', $duoi, $x ) ? ( self::van_ban( $x[1] ) ?: null ) : null;
				$noi_dung = substr( $noi_dung, 0, $vt );
			}
			$di_ban = array();
			if ( preg_match( '~<section class="variants">(.*?)</section>~s', $a, $kdb ) ) {
				preg_match_all( '~<li class="variant"[^>]*>(.*?)</li>~s', $kdb[1], $dsdb );
				$di_ban = array_map( array( self::class, 'loi' ), $dsdb[1] );
			}
			$chu_de = array();
			if ( preg_match( '~<ul class="post-categories">(.*?)</ul>~s', $a, $kcd ) ) {
				preg_match_all( '~<a [^>]*>(.*?)</a>~s', $kcd[1], $dscd );
				$chu_de = array_values( array_filter( array_map( array( self::class, 'van_ban' ), $dscd[1] ), 'strlen' ) );
			}
			$chinh = self::loi( $noi_dung );
			$bai[] = array(
				'ma_goc'                  => (int) $m[1],
				'url'                     => $td[1],
				'tieu_de'                 => self::van_ban( $td[2] ),
				'loi'                     => $chinh['loi'],
				'vi_tri_chu_thich'        => $chinh['chu_thich'],
				'di_ban'                  => array_column( $di_ban, 'loi' ),
				'vi_tri_chu_thich_di_ban' => array_column( $di_ban, 'chu_thich' ),
				'chu_de_goc'              => $chu_de,
				'cau_hoi'                 => $cau_hoi,
				'dap_an'                  => $dap_an,
			);
		}
		return array( 'bai' => $bai, 'chu_thich' => self::chu_thich( $html ) );
	}

	/** Chú thích trong <section class="ref"> (hoặc trang /chu-thich/chi-tiet/<mã>): mã → {ma, tu (có thể rỗng), noi_dung}. */
	public static function chu_thich( string $html ): array {
		$ra = array();
		preg_match_all( '~<li data-ref-id="(\d+)" id="note-\d+">(.*?)</dl>~s', $html, $ds, PREG_SET_ORDER );
		foreach ( $ds as $m ) {
			if ( ! preg_match( '~<dd>(.*)</dd>~s', $m[2], $dd ) ) {
				continue;
			}
			$dt                = preg_match( '~<dt>(.*?)</dt>~s', $m[2], $x ) ? $x[1] : '';
			$ra[ (int) $m[1] ] = array( 'ma' => (int) $m[1], 'tu' => self::van_ban( $dt ), 'noi_dung' => self::van_ban( $dd[1] ) );
		}
		return $ra;
	}

	/** Sitemap: [{url, lastmod}]. */
	public static function sitemap( string $xml ): array {
		preg_match_all( '~<url>\s*<loc>([^<]+)</loc>(?:\s*<lastmod>([^<]+)</lastmod>)?~', $xml, $ds, PREG_SET_ORDER );
		return array_map( static fn( array $m ): array => array( 'url' => self::giai_ma( self::cat( $m[1] ) ), 'lastmod' => isset( $m[2] ) ? self::cat( $m[2] ) : null ), $ds );
	}

	/** Hai địa chỉ cùng một trang (bỏ khác biệt mã hoá và dấu / cuối). */
	public static function cung_trang( string $a, string $b ): bool {
		$p = static fn( string $u ): string => rtrim( rawurldecode( (string) parse_url( $u, PHP_URL_PATH ) ), '/' );
		return $p( $a ) === $p( $b );
	}
}
