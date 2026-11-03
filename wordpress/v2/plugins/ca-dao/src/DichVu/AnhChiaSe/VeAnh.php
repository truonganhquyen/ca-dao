<?php

namespace Phongdao\DichVu\AnhChiaSe;

/**
 * Vẽ ảnh chia sẻ PNG bằng GD (docs/27 mục 10.1; chuyển từ MVP anh-chia-se.php sang bộ thiết kế V2): khổ ngang
 * 1200×630 (thẻ og:image cho Zalo, Facebook), vuông 1080×1080, dọc 1080×1350; nền theo thể loại, hoạ tiết vẽ bằng mã
 * như phần xem trước trên trang (GiaoTiep\Khoi\AnhChiaSe). Phông TTF tự lưu (Literata, Be Vietnam Pro — OFL,
 * assets/phong/). Không gọi WordPress (bộ kiểm thuần vẽ thử). Máy không vẽ được chữ (GD không có FreeType, như PHP
 * của Playground) thì không có ảnh.
 */
final class VeAnh {

	/** Đổi khi đổi cách vẽ: ảnh đã lưu sinh lại. */
	public const PHIEN_BAN = 1;

	/** Khổ => [rộng, cao]. */
	public const KHO = array(
		'ngang' => array( 1200, 630 ),
		'vuong' => array( 1080, 1080 ),
		'doc'   => array( 1080, 1350 ),
	);

	/** Nền => [màu đầu, màu cuối của dải màu, chữ, chữ phụ] — đúng giá trị CSS của bộ thiết kế. */
	public const NEN = array(
		'tim'  => array( '#35205F', '#6A42C2', '#FFFFFF', '#E8DDFF' ),
		'giay' => array( '#FBF4E4', '#EBDDBE', '#2A2118', '#5E3AAE' ),
		'dem'  => array( '#12102A', '#2E2560', '#FFFFFF', '#CFC6FF' ),
		'luc'  => array( '#0E3A28', '#236B4C', '#FFFFFF', '#CDEBDC' ),
		'nang' => array( '#FFC65C', '#FF8A4C', '#2A1708', '#5A2E00' ),
		'xanh' => array( '#132E4A', '#2F6690', '#FFFFFF', '#D2E6F7' ),
	);

	/** Các nền được chọn theo thể loại (nền đầu là nền mặc định, cũng là nền của thẻ og:image). */
	public const NEN_THEO_LOAI = array(
		'ca-dao'   => array( 'tim', 'giay', 'dem' ),
		'tuc-ngu'  => array( 'luc', 'giay', 'tim' ),
		'dong-dao' => array( 'nang', 'giay', 'luc' ),
		've'       => array( 'xanh', 'giay', 'tim' ),
		'cau-do'   => array( 'dem', 'nang', 'tim' ),
	);

	public static function nen_cua( string $the_loai ): array {
		return self::NEN_THEO_LOAI[ $the_loai ] ?? self::NEN_THEO_LOAI['ca-dao'];
	}

	/** Máy này vẽ được chữ không: thử đo chữ thật (có bản GD báo có FreeType mà không vẽ được). */
	public static function duoc(): bool {
		static $duoc = null;
		return $duoc ??= function_exists( 'imagecreatetruecolor' ) && function_exists( 'imagettfbbox' ) && function_exists( 'imagepng' )
			&& false !== @imagettfbbox( 12, 0, self::phong( 'bvp-400' ), 'Ngưỡng' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors -- không vẽ được thì báo false
	}

	/**
	 * Nội dung ảnh của một tác phẩm (dữ liệu trang của GiaoTiep\Khoi\TacPham): lời (bài trên 8 dòng: 4 dòng đầu + "…"),
	 * ý nghĩa (câu đố thì không — không lộ đáp án), đường dẫn ngắn; bài có cờ ghi thêm "Nghi không phải dân gian".
	 *
	 * @return array{the_loai:string,dong:string[],luc_bat:bool,cau:bool,y_nghia:string,cau_do:bool,ten_mien:string,duong:string}
	 */
	public static function noi_dung( array $tp ): array {
		$cau_do = 'cau-do' === ( $tp['the_loai'] ?? '' );
		$dong   = array_values( array_filter( array_map( 'strval', $tp['loi'] ?? array() ), static fn( string $d ): bool => '' !== trim( $d ) ) );
		$url    = parse_url( (string) ( $tp['url'] ?? '' ) ) ?: array();
		$host   = (string) ( $url['host'] ?? '' );
		return array(
			'the_loai' => (string) ( $tp['ten_the_loai'] ?? '' ) . ( empty( $tp['co_nghi'] ) ? '' : ' · Nghi không phải dân gian' ),
			'dong'     => count( $dong ) > 8 ? array_merge( array_slice( $dong, 0, 4 ), array( '…' ) ) : $dong,
			'luc_bat'  => 'lucbat' === ( $tp['kieu'] ?? '' ),
			'cau'      => 'proverb' === ( $tp['kieu'] ?? '' ),
			'y_nghia'  => $cau_do ? '' : trim( (string) ( $tp['y_nghia']['chu'] ?? '' ) ),
			'cau_do'   => $cau_do,
			'ten_mien' => $host,
			'duong'    => $host . rtrim( (string) ( $url['path'] ?? '' ), '/' ),
		);
	}

	/**
	 * Ảnh PNG (chuỗi byte); null khi máy không vẽ được hoặc khổ, nền lạ.
	 *
	 * @param array{the_loai:string,dong:string[],luc_bat:bool,cau:bool,y_nghia:string,cau_do:bool,ten_mien:string,duong:string} $nd
	 */
	public static function ve( array $nd, string $kho = 'ngang', string $nen = 'tim', bool $kem_y = false ): ?string {
		if ( ! self::duoc() || ! isset( self::KHO[ $kho ], self::NEN[ $nen ] ) || ! $nd['dong'] ) {
			return null;
		}
		[ $w, $h ]               = self::KHO[ $kho ];
		[ $a1, $a2, $chu, $phu ] = self::NEN[ $nen ];
		$anh                     = imagecreatetruecolor( $w, $h );
		imagealphablending( $anh, true );
		self::dai_mau( $anh, $w, $h, $a1, $a2 );
		self::hoa_tiet( $anh, $w, $h, $nen, $chu );

		$q    = $w / 100; // 1 đơn vị cqi của phần xem trước
		$le   = 6 * $q;
		$tren = 5 * $q;
		$rong = $w - 2 * $le;
		$c    = self::mau( $anh, $chu );
		$p    = self::mau( $anh, $phu );
		$dam  = self::phong( 'bvp-600' );
		$tho  = self::phong( 'literata-400' );

		// Thể loại (trên cùng): chữ hoa, giãn chữ .12em
		$co_tl = 2.4 * $q;
		self::chu_gian( $anh, mb_strtoupper( $nd['the_loai'] ), $dam, $co_tl, $le, $tren + $co_tl, $p, 0.12 * $co_tl );

		// Chân: ô chữ P, tên thư viện, đường dẫn bài
		$o    = 6.4 * $q;
		$y_o  = $h - $tren - $o;
		self::o_bo_goc( $anh, $le, $y_o, $o, 1.6 * $q, $c );
		$co_p = 3.8 * $q;
		self::chu( $anh, 'P', $dam, $co_p, $le + ( $o - self::rong( 'P', $dam, $co_p ) ) / 2, $y_o + $o / 2 + 0.36 * $co_p, self::mau( $anh, $a1 ) );
		$x_ten = $le + $o + 2.4 * $q;
		$co1   = 2.5 * $q;
		$co2   = 0.85 * $co1;
		$giua  = $y_o + $o / 2;
		self::chu( $anh, self::cat( 'Thư viện Văn học Dân gian Việt Nam', $dam, $co1, $w - $le - $x_ten ), $dam, $co1, $x_ten, $giua - 0.15 * $co1, $c );
		self::chu( $anh, self::cat( $nd['duong'], self::phong( 'bvp-400' ), $co2, $w - $le - $x_ten ), self::phong( 'bvp-400' ), $co2, $x_ten, $giua + 1.05 * $co2, $p );

		// Giữa: lời (mỗi câu một dòng, cỡ lớn nhất vừa khung), ý nghĩa hoặc lời nhắc của câu đố
		$dau    = $tren + 1.4 * $co_tl + 2 * $q;
		$cuoi   = $y_o - 2 * $q;
		$y_nghia = $kem_y && '' !== $nd['y_nghia'];
		$phu_y  = $y_nghia || $nd['cau_do'];
		$dong   = $nd['dong'];
		$thut   = $nd['luc_bat'] && count( $dong ) > 1 ? 1.6 : 0.0; // câu lục thụt 1,6 chữ như trang đọc
		$co     = self::co_loi( $dong, $tho, $w, $h, $rong, $thut, $nd['cau'], $phu_y );
		if ( $nd['cau'] && $co < 0.045 * $w ) { // tục ngữ dài: ngắt theo vế
			$dong = array_values( array_filter( array_map( 'trim', preg_split( '/(?<=,)\s+|\s+(?=—)/u', implode( ' ', $dong ) ) ?: $dong ) ) );
			$co   = self::co_loi( $dong, $tho, $w, $h, $rong, 0.0, true, $phu_y );
		}
		$cao_dong = 1.5 * $co;
		$co_y     = 2.6 * $q;
		$vach     = max( 3, (int) round( 0.0055 * $w ) );
		$dong_y   = $y_nghia ? self::xuong_dong( $nd['y_nghia'], $tho, $co_y, $rong - $vach - 2 * $q, 4 ) : ( $nd['cau_do'] ? array( 'Lời giải ở ' . $nd['ten_mien'] ) : array() );
		$cao      = count( $dong ) * $cao_dong + ( $dong_y ? 2 * $q + count( $dong_y ) * 1.5 * $co_y : 0 );
		$y        = $dau + max( 0, ( $cuoi - $dau - $cao ) / 2 );
		foreach ( $dong as $i => $d ) {
			$x = $le + ( $thut && 0 === $i % 2 ? $thut * $co : 0 );
			self::chu( $anh, self::cat( $d, $tho, $co, $w - $le - $x ), $tho, $co, $x, $y + $i * $cao_dong + 0.5 * $cao_dong + 0.36 * $co, $c );
		}
		if ( $dong_y ) {
			$y += count( $dong ) * $cao_dong + 2 * $q;
			$x  = $le;
			if ( $y_nghia ) {
				imagefilledrectangle( $anh, (int) $le, (int) $y, (int) $le + $vach - 1, (int) ( $y + count( $dong_y ) * 1.5 * $co_y ), $p );
				$x += $vach + 2 * $q;
			}
			foreach ( $dong_y as $i => $d ) {
				self::chu( $anh, $d, $y_nghia ? $tho : $dam, $co_y, $x, $y + $i * 1.5 * $co_y + 0.75 * $co_y + 0.36 * $co_y, $p );
			}
		}

		ob_start();
		imagepng( $anh, null, 6 );
		$png = (string) ob_get_clean();
		imagedestroy( $anh );
		return $png;
	}

	/**
	 * Cỡ chữ lời (px): trần theo khổ (như fit.js: tỷ lệ bề rộng, chiều cao khung), vừa bề rộng — đo lại ở đúng cỡ vì
	 * bề rộng chữ không tăng đúng tỷ lệ; sàn 2,2 % bề rộng (dưới sàn thì dòng dài bị cắt "…").
	 */
	private static function co_loi( array $dong, string $phong, int $w, int $h, float $rong, float $thut, bool $cau, bool $phu_y ): float {
		$tran = min( ( $cau ? 0.085 : 0.06 ) * $w, $h * ( $phu_y ? 0.32 : 0.44 ) / ( count( $dong ) * 1.5 ) );
		$dai  = static function ( float $co ) use ( $dong, $phong, $thut ): float {
			$ra = 0.0;
			foreach ( $dong as $i => $d ) {
				$ra = max( $ra, self::rong( $d, $phong, $co ) + ( $thut && 0 === $i % 2 ? $thut * $co : 0 ) );
			}
			return $ra;
		};
		$san = 0.022 * $w;
		$em  = $dai( 100 ) / 100;
		$co  = max( $san, min( $tran, $em > 0 ? $rong / $em : $tran ) );
		while ( $co > $san && $dai( $co ) > $rong ) {
			$co = max( $san, $co * 0.98 );
		}
		return $co;
	}

	/** Dải màu chéo (140°) từ $a1 sang $a2: mỗi đường chéo x + y = t một màu. */
	private static function dai_mau( \GdImage $anh, int $w, int $h, string $a1, string $a2 ): void {
		[ $r1, $g1, $b1 ] = self::rgb( $a1 );
		[ $r2, $g2, $b2 ] = self::rgb( $a2 );
		$n                = $w + $h - 2;
		for ( $t = 0; $t <= $n; $t++ ) {
			$k = $t / $n;
			imageline( $anh, max( 0, $t - $h + 1 ), min( $t, $h - 1 ), min( $t, $w - 1 ), max( 0, $t - $w + 1 ), imagecolorallocate( $anh, (int) round( $r1 + ( $r2 - $r1 ) * $k ), (int) round( $g1 + ( $g2 - $g1 ) * $k ), (int) round( $b1 + ( $b2 - $b1 ) * $k ) ) );
		}
	}

	/** Hoạ tiết của nền, đặt như CSS .hoa-tiet (khung SVG 400 × 400 của GiaoTiep\Khoi\AnhChiaSe::hoa_tiet). */
	private static function hoa_tiet( \GdImage $anh, int $w, int $h, string $nen, string $chu ): void {
		switch ( $nen ) {
			case 'giay':
				[ $bw, $x0, $y0, $mau, $do ] = array( 0.9 * $w, 0.2 * $w, 1.1 * $h - 0.9 * $w, '#8C6A2E', 0.3 );
				break;
			case 'dem':
			case 'nang':
				$bw                   = min( $w, $h );
				[ $x0, $y0, $mau, $do ] = array( ( $w - $bw ) / 2, ( $h - $bw ) / 2, $chu, 0.22 );
				break;
			case 'luc':
			case 'xanh':
				[ $bw, $x0, $y0, $mau, $do ] = array( $w, 0, 1.4 * $h - $w, $chu, 0.18 );
				break;
			default:
				[ $bw, $x0, $y0, $mau, $do ] = array( 0.72 * $w, 0.44 * $w, 1.28 * $h - 0.72 * $w, $chu, 0.18 );
		}
		// Vẽ đặc trên một lớp riêng rồi trộn cả lớp theo độ mờ: nét chồng nhau không đậm lên thành chấm
		$lop   = imagecreatetruecolor( $w, $h );
		$trong = imagecolorallocate( $lop, 255, 0, 255 );
		imagefill( $lop, 0, 0, $trong );
		imagecolortransparent( $lop, $trong );
		$k = $bw / 400;
		$c = self::mau( $lop, $mau );
		imagesetthickness( $lop, max( 1, (int) round( 2 * $k ) ) );
		$p     = static fn( float $x, float $y ): array => array( (int) round( $x0 + $x * $k ), (int) round( $y0 + $y * $k ) );
		$duong = static function ( array $diem ) use ( $lop, $c, $p ): void {
			for ( $i = 1, $n = count( $diem ); $i < $n; $i++ ) {
				[ $xa, $ya ] = $p( ...$diem[ $i - 1 ] );
				[ $xb, $yb ] = $p( ...$diem[ $i ] );
				imageline( $lop, $xa, $ya, $xb, $yb, $c );
			}
		};
		$cung  = static function ( float $cx, float $cy, float $r, float $tu = 0, float $den = 2 * M_PI ) use ( $duong ): void {
			$diem = array();
			for ( $i = 0; $i <= 48; $i++ ) {
				$a      = $tu + ( $den - $tu ) * $i / 48;
				$diem[] = array( $cx + $r * cos( $a ), $cy + $r * sin( $a ) );
			}
			$duong( $diem );
		};
		switch ( $nen ) {
			case 'tim': // trống đồng: sao 14 cánh, vòng đồng tâm, vạch quanh vành
				$sao = array();
				for ( $i = 0; $i <= 28; $i++ ) {
					$r     = $i % 2 ? 20 : 58;
					$a     = M_PI * $i / 14 - M_PI / 2;
					$sao[] = array( 200 + $r * cos( $a ), 200 + $r * sin( $a ) );
				}
				$duong( $sao );
				foreach ( array( 80, 106, 132, 158, 184 ) as $r ) {
					$cung( 200, 200, $r );
				}
				for ( $i = 0; $i < 36; $i++ ) {
					$a = $i * M_PI / 18;
					$duong( array( array( 200 + 140 * cos( $a ), 200 + 140 * sin( $a ) ), array( 200 + 150 * cos( $a ), 200 + 150 * sin( $a ) ) ) );
				}
				break;
			case 'giay': // mây
				foreach ( array( array( 30, 90 ), array( 190, 60 ), array( 110, 200 ), array( 260, 190 ), array( 40, 300 ), array( 220, 320 ) ) as [ $x, $y ] ) {
					$cung( $x + 40, $y, 40, M_PI, 2 * M_PI );
					$cung( $x + 105, $y, 25, M_PI, 2 * M_PI );
					$cung( $x + 40, $y, 20, M_PI, 2 * M_PI );
				}
				break;
			case 'dem': // sao, trăng khuyết
				for ( $i = 0; $i < 40; $i++ ) {
					[ $x, $y ] = $p( ( $i * 97 ) % 400, ( $i * 53 ) % 400 );
					$r         = max( 1, (int) round( 2 * ( 1 + $i % 3 ) * $k ) );
					imagefilledellipse( $lop, $x, $y, $r, $r, $c );
				}
				// Trăng: trong vòng (300, 130) bán kính 50, ngoài vòng (320, 120) bán kính 44,7
				[ $xa, $ya ] = $p( 250, 80 );
				[ $xb, $yb ] = $p( 350, 180 );
				for ( $y = $ya; $y <= $yb; $y++ ) {
					for ( $x = $xa; $x <= $xb; $x++ ) {
						$u = ( $x - $x0 ) / $k;
						$v = ( $y - $y0 ) / $k;
						if ( ( $u - 300 ) ** 2 + ( $v - 130 ) ** 2 <= 2500 && ( $u - 320 ) ** 2 + ( $v - 120 ) ** 2 > 2000 ) {
							imagesetpixel( $lop, $x, $y, $c );
						}
					}
				}
				break;
			case 'nang': // vòng nắng
				for ( $i = 0; $i < 30; $i++ ) {
					$cung( ( $i * 71 ) % 400, ( $i * 131 ) % 400, 6 + ( $i % 5 ) * 5 );
				}
				break;
			case 'luc': // sóng lúa: đường cong bậc hai, mỗi nhịp 50
				for ( $i = 0; $i < 9; $i++ ) {
					$diem = array();
					for ( $x = 0; $x <= 400; $x += 5 ) {
						$t      = fmod( $x, 50 ) / 50;
						$diem[] = array( $x, 60 + $i * 40 + ( 0 === intdiv( $x, 50 ) % 2 ? -1 : 1 ) * 36 * $t * ( 1 - $t ) );
					}
					$duong( $diem );
				}
				break;
			default: // sông nước: đường cong bậc ba (40, −30), (60, 30), (100, 0) lặp lại
				for ( $i = 0; $i < 9; $i++ ) {
					$diem = array();
					for ( $j = 0; $j < 4; $j++ ) {
						for ( $s = 0; $s <= 16; $s++ ) {
							$t      = $s / 16;
							$diem[] = array( 100 * $j + 3 * ( 1 - $t ) ** 2 * $t * 40 + 3 * ( 1 - $t ) * $t ** 2 * 60 + $t ** 3 * 100, 50 + $i * 42 + 3 * ( 1 - $t ) ** 2 * $t * -30 + 3 * ( 1 - $t ) * $t ** 2 * 30 );
						}
					}
					$duong( $diem );
				}
		}
		imagecopymerge( $anh, $lop, 0, 0, 0, 0, $w, $h, (int) round( 100 * $do ) );
		imagedestroy( $lop );
	}

	/** Ô vuông bo góc (ô chữ P ở chân ảnh). */
	private static function o_bo_goc( \GdImage $anh, float $x, float $y, float $canh, float $bo, int $c ): void {
		$x  = (int) round( $x );
		$y  = (int) round( $y );
		$n  = (int) round( $canh );
		$b  = (int) round( $bo );
		imagefilledrectangle( $anh, $x + $b, $y, $x + $n - $b, $y + $n, $c );
		imagefilledrectangle( $anh, $x, $y + $b, $x + $n, $y + $n - $b, $c );
		foreach ( array( array( $x + $b, $y + $b ), array( $x + $n - $b, $y + $b ), array( $x + $b, $y + $n - $b ), array( $x + $n - $b, $y + $n - $b ) ) as [ $cx, $cy ] ) {
			imagefilledellipse( $anh, $cx, $cy, 2 * $b, 2 * $b, $c );
		}
	}

	/** Viết chữ: $co là cỡ px, $y là đường chân chữ. GD đo cỡ chữ bằng point (96 dpi). */
	private static function chu( \GdImage $anh, string $chu, string $phong, float $co, float $x, float $y, int $c ): void {
		imagettftext( $anh, $co * 0.75, 0, (int) round( $x ), (int) round( $y ), $c, $phong, $chu );
	}

	/** Chữ giãn cách (nhãn thể loại). */
	private static function chu_gian( \GdImage $anh, string $chu, string $phong, float $co, float $x, float $y, int $c, float $gian ): void {
		foreach ( mb_str_split( $chu ) as $kt ) {
			self::chu( $anh, $kt, $phong, $co, $x, $y, $c );
			$x += self::rong( $kt, $phong, $co ) + $gian;
		}
	}

	/** Bề rộng (px) của chữ ở cỡ $co px. */
	public static function rong( string $chu, string $phong, float $co ): float {
		$h = imagettfbbox( $co * 0.75, 0, $phong, $chu );
		return $h ? (float) ( $h[2] - $h[0] ) : 0.0;
	}

	/** Cắt chữ cho vừa bề rộng, thêm "…". */
	public static function cat( string $chu, string $phong, float $co, float $toi_da ): string {
		if ( self::rong( $chu, $phong, $co ) <= $toi_da ) {
			return $chu;
		}
		while ( mb_strlen( $chu ) > 1 && self::rong( $chu . '…', $phong, $co ) > $toi_da ) {
			$chu = mb_substr( $chu, 0, -1 );
		}
		return rtrim( $chu ) . '…';
	}

	/** Ngắt đoạn chữ thành dòng vừa bề rộng; quá $toi_da dòng thì dòng cuối thêm "…". @return string[] */
	private static function xuong_dong( string $chu, string $phong, float $co, float $rong, int $toi_da ): array {
		$ra   = array();
		$dong = '';
		foreach ( preg_split( '/\s+/u', trim( $chu ) ) ?: array() as $tu ) {
			$thu = '' === $dong ? $tu : $dong . ' ' . $tu;
			if ( '' !== $dong && self::rong( $thu, $phong, $co ) > $rong ) {
				$ra[] = $dong;
				$dong = $tu;
			} else {
				$dong = $thu;
			}
		}
		if ( '' !== $dong ) {
			$ra[] = $dong;
		}
		if ( count( $ra ) > $toi_da ) {
			$ra                = array_slice( $ra, 0, $toi_da );
			$ra[ $toi_da - 1 ] = self::cat( $ra[ $toi_da - 1 ] . '…', $phong, $co, $rong );
		}
		return array_map( static fn( string $d ): string => self::cat( $d, $phong, $co, $rong ), $ra );
	}

	private static function mau( \GdImage $anh, string $hex, int $alpha = 0 ): int {
		[ $r, $g, $b ] = self::rgb( $hex );
		return imagecolorallocatealpha( $anh, $r, $g, $b, $alpha );
	}

	/** @return int[] */
	private static function rgb( string $hex ): array {
		return array( hexdec( substr( $hex, 1, 2 ) ), hexdec( substr( $hex, 3, 2 ) ), hexdec( substr( $hex, 5, 2 ) ) );
	}

	public static function phong( string $ten ): string {
		return dirname( __DIR__, 3 ) . '/assets/phong/' . $ten . '.ttf';
	}
}
