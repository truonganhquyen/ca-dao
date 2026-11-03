<?php

namespace Phongdao\DichVu\NhapDuLieuCu;

/**
 * Tách nội dung một bài MVP (các khối của trình soạn, kết quả parse_blocks) thành lời, dị bản, câu hỏi, đáp án,
 * chú thích — docs/29 mục 3 quy tắc 2. PHP thuần (bộ kiểm đơn vị).
 *
 * Đọc đúng như trang MVP đang hiện (theme MVP, cadao_chuan_bi_bai): khối Thơ đầu là lời, khối Thơ sau tiêu đề
 * "Dị bản" là dị bản, danh sách lớp cadao-chu-thich là chú thích (lớp cadao-chep = máy chép), khối Chi tiết "Đáp án"
 * là đáp án. Vè nhiều phần (tiêu đề phụ ngay trên mỗi khối Thơ): các khối Thơ nối thành lời, mỗi tiêu đề thành một
 * đoạn; dòng chú (in nghiêng, trong ngoặc) đứng trước lời là bối cảnh. Thứ gì không nhận ra được ghi vào can_xem kèm
 * lý do — không đoán, không bỏ im lặng.
 */
final class TachBai {

	/** Khoảng trắng MVP cắt ở hai đầu mỗi dòng (CADAO_KHOANG_TRANG). */
	private const KHOANG_TRANG = '[\t\n\x0B\f\r\p{Zs}\x{FEFF}\x{2028}\x{2029}]';

	/**
	 * @param array<int,array> $khoi các khối cấp ngoài cùng
	 * @return array{loi:?string, di_ban:string[], cau_hoi:?string, dap_an:?string, chu_thich:list<array{tu:string,noi_dung:string,chep:bool}>, doan:list<array{tieu_de?:string,tu_dong:int,den_dong:int}>, boi_canh:string[], dong_nguon:bool, doan_van:bool, can_xem:string[]}
	 *         doan: chỉ khi lời có tiêu đề phụ (dòng đếm theo dòng có chữ, từ 1)
	 */
	public static function tach( array $khoi ): array {
		$ra   = array(
			'loi'        => null,
			'di_ban'     => array(),
			'cau_hoi'    => null,
			'dap_an'     => null,
			'chu_thich'  => array(),
			'doan'       => array(),
			'boi_canh'   => array(),
			'dong_nguon' => false,
			'doan_van'   => false,
			'can_xem'    => array(),
		);
		// Bài chưa có khối Thơ (vè dán từ trang khác): lời lấy từ khối Đoạn văn như nút "Đưa lời vào khối Thơ" của MVP
		$tho = self::doan_thanh_tho( $khoi );
		if ( null !== $tho ) {
			$khoi           = $tho;
			$ra['doan_van'] = true;
		}
		$khoi    = array_values( array_filter( $khoi, static fn( $k ) => null !== ( $k['blockName'] ?? null ) || '' !== trim( (string) ( $k['innerHTML'] ?? '' ) ) ) );
		$vung    = 'loi'; // loi | di_ban | chu_thich | khac
		$xen     = false; // có khối khác xen sau khối Thơ của lời
		$vua_loi = false; // khối ngay trước là khối Thơ của lời
		$phan    = array(); // các phần của lời: [tiêu đề, dòng đầu, dòng cuối]
		$cho     = null;    // tiêu đề phần đang chờ khối Thơ của nó
		$db_dau  = false;   // có dị bản đứng trước lời ("Dị bản" ở đầu bài)
		foreach ( $khoi as $i => $k ) {
			$ten  = $k['blockName'] ?? null;
			$html = (string) ( $k['innerHTML'] ?? '' );
			if ( null === $ten ) {
				$ra['can_xem'][] = 'có nội dung không ở dạng khối';
				continue;
			}
			$truoc   = $vua_loi;
			$vua_loi = false;
			$la_phan = false;
			switch ( $ten ) {
				case 'core/heading':
					$tieu_de = self::chu( $html );
					if ( 'Dị bản' === $tieu_de ) {
						$vung = 'di_ban';
					} elseif ( 'Chú thích' === $tieu_de ) {
						$vung = 'chu_thich';
					} elseif ( 'core/verse' === ( $khoi[ $i + 1 ]['blockName'] ?? null ) && 'chu_thich' !== $vung && ( null === $ra['loi'] || 'loi' === $vung ) ) {
						// Tiêu đề phụ ngay trên khối Thơ (vè nhiều phần): khối Thơ đó là một phần của lời
						$cho     = $tieu_de;
						$vung    = 'loi';
						$la_phan = true;
					} else {
						$vung            = 'khac';
						$ra['can_xem'][] = "có tiêu đề «{$tieu_de}»";
					}
					break;
				case 'core/verse':
					$loi = self::loi_tho( $html );
					if ( '' === $loi ) {
						break;
					}
					if ( 'loi' === $vung ) {
						if ( null !== $ra['loi'] && $xen ) {
							$ra['can_xem'][] = 'có khối khác xen giữa các khổ của lời';
						}
						$dau       = self::so_dong( $ra['loi'] );
						$ra['loi'] = null === $ra['loi'] ? $loi : $ra['loi'] . "\n\n" . $loi;
						if ( null !== $cho || ! $phan ) {
							$phan[] = array( (string) $cho, $dau + 1, self::so_dong( $ra['loi'] ) );
						} else { // khối Thơ không tiêu đề: khổ tiếp của phần trước
							$phan[ count( $phan ) - 1 ][2] = self::so_dong( $ra['loi'] );
						}
						$cho     = null;
						$vua_loi = true;
					} elseif ( 'di_ban' === $vung ) {
						$ra['di_ban'][] = $loi;
						$db_dau         = $db_dau || null === $ra['loi'];
					} else {
						$ra['can_xem'][] = 'có khối Thơ ngoài lời và dị bản';
					}
					break;
				case 'core/paragraph':
					$lop = (string) ( $k['attrs']['className'] ?? '' );
					if ( str_contains( $lop, 'cadao-chu-thich-nguon' ) ) {
						$ra['dong_nguon'] = true;
						break;
					}
					$chu = self::chu( $html );
					if ( '' === $chu ) {
						break;
					}
					if ( $truoc && null === $ra['cau_hoi'] && preg_match( '~^\s*<p\b[^>]*>\s*<strong>(.*?)</strong>\s*</p>\s*$~is', $html ) ) {
						$ra['cau_hoi'] = $chu;
						break;
					}
					if ( preg_match( '/^\(?[\s\x{00A0}]*Nguồn[\s\x{00A0}]*:/u', $chu ) ) {
						$ra['can_xem'][] = 'dòng «' . mb_substr( (string) preg_replace( '/[\s\x{00A0}]+/u', ' ', $chu ), 0, 160 ) . '» không mang sang: nếu là sách đã đối chiếu, ghi ở Sửa lời';
						break;
					}
					if ( null === $ra['loi'] && 'loi' === $vung && null === $cho && 'chu' === self::loai_dong( (string) preg_replace( '~^\s*<p\b[^>]*>|</p>\s*$~i', '', trim( $html ) ) ) ) {
						$ra['boi_canh'][] = $chu; // dòng chú đứng trước lời: bối cảnh (vè)
						break;
					}
					$ra['can_xem'][] = 'có đoạn văn «' . self::ngan( $chu ) . '»';
					break;
				case 'core/details':
					$tom = preg_match( '~<summary\b[^>]*>(.*?)</summary>~is', $html, $m ) ? self::chu( $m[1] ) : '';
					if ( 'Đáp án' !== $tom || null !== $ra['dap_an'] ) {
						$ra['can_xem'][] = '' === $tom ? 'có khối Chi tiết' : "có khối Chi tiết «{$tom}»";
						break;
					}
					$doan = array();
					foreach ( (array) ( $k['innerBlocks'] ?? array() ) as $con ) {
						$c = self::chu( (string) ( $con['innerHTML'] ?? '' ) );
						if ( '' !== $c ) {
							$doan[] = $c;
						}
					}
					if ( ! $doan ) { // Đáp án viết thẳng trong khối, không qua khối Đoạn văn
						$doan[] = self::chu( (string) preg_replace( '~<summary\b[^>]*>.*?</summary>~is', '', $html ) );
					}
					$dap = trim( implode( "\n", $doan ) );
					if ( '' !== $dap ) {
						$ra['dap_an'] = $dap;
					}
					break;
				case 'core/list':
					$lop = (string) ( $k['attrs']['className'] ?? '' );
					if ( ! str_contains( $lop, 'cadao-chu-thich' ) ) {
						$ra['can_xem'][] = 'có danh sách không phải chú thích';
						break;
					}
					$chep = str_contains( $lop, 'cadao-chep' );
					$li   = $html . implode( '', array_map( static fn( $c ) => (string) ( $c['innerHTML'] ?? '' ), (array) ( $k['innerBlocks'] ?? array() ) ) );
					preg_match_all( '~<li\b[^>]*>(.*?)</li>~is', $li, $m );
					foreach ( $m[1] as $o ) {
						$tu = '';
						if ( preg_match( '~^\s*<strong>(.*?)</strong>\s*:?\s*~is', $o, $t ) ) {
							$tu = self::chu( $t[1] );
							$o  = substr( $o, strlen( $t[0] ) );
						}
						$noi = self::chu( $o );
						if ( '' === $noi ) {
							if ( '' !== $tu ) {
								$ra['can_xem'][] = "chú thích «{$tu}» không có lời giải";
							}
							continue;
						}
						$ra['chu_thich'][] = array( 'tu' => $tu, 'noi_dung' => $noi, 'chep' => $chep );
					}
					break;
				default:
					$ra['can_xem'][] = "có khối «{$ten}»";
			}
			if ( null !== $ra['loi'] && ! $vua_loi && ! $la_phan && 'core/verse' !== $ten ) {
				$xen = true;
			}
		}
		if ( null === $ra['loi'] ) {
			$ra['can_xem'][] = 'không thấy lời (khối Thơ)';
		} elseif ( $db_dau ) {
			$ra['can_xem'][] = 'tiêu đề «Dị bản» đứng trước lời: khối Thơ ngay dưới nhập làm dị bản';
		}
		if ( array_filter( array_column( $phan, 0 ), 'strlen' ) ) {
			foreach ( $phan as [ $tieu_de, $tu, $den ] ) {
				$ra['doan'][] = ( '' === $tieu_de ? array() : array( 'tieu_de' => $tieu_de ) ) + array( 'tu_dong' => $tu, 'den_dong' => $den );
			}
		}
		$ra['can_xem'] = array_values( array_unique( $ra['can_xem'] ) );
		return $ra;
	}

	/**
	 * Khối Đoạn văn chứa lời → khối Thơ, đúng quy tắc cadao_de_xuat_khoi_tho của MVP: mỗi dòng một dòng; hai đoạn
	 * nhiều dòng cách nhau một dòng trống (khổ mới); dòng chú (in nghiêng trọn dòng, hoặc nằm trong ngoặc) giữ là đoạn
	 * văn; khối khác giữ nguyên. Chữ sau khi chuyển khác chữ trước thì không chuyển.
	 *
	 * @return ?array các khối mới; null khi bài đã có khối Thơ hoặc không có gì để chuyển
	 */
	public static function doan_thanh_tho( array $khoi ): ?array {
		if ( in_array( 'core/verse', array_column( $khoi, 'blockName' ), true ) ) {
			return null;
		}
		$ra    = array();
		$cho   = array(); // các dòng lời đang gom thành một khối Thơ
		$nhieu = false;   // đoạn vừa gom có nhiều dòng
		$xa    = static function () use ( &$cho, &$ra, &$nhieu ): void {
			if ( $cho ) {
				$ra[]  = array(
					'blockName'   => 'core/verse',
					'attrs'       => array(),
					'innerHTML'   => '<pre class="wp-block-verse">' . implode( '<br>', $cho ) . '</pre>',
					'innerBlocks' => array(),
				);
				$cho   = array();
				$nhieu = false;
			}
		};
		$co_tho = false;
		foreach ( $khoi as $k ) {
			$ten = $k['blockName'] ?? null;
			if ( null === $ten && '' === trim( (string) ( $k['innerHTML'] ?? '' ) ) ) {
				continue;
			}
			if ( 'core/paragraph' !== $ten ) {
				$xa();
				$ra[] = $k;
				continue;
			}
			$trong = (string) preg_replace( '~^\s*<p\b[^>]*>|</p>\s*$~i', '', trim( (string) $k['innerHTML'] ) );
			$nhom  = array();
			foreach ( preg_split( '~<br\s*/?>~i', $trong ) as $d ) {
				$loai = self::loai_dong( $d );
				if ( 'rong' === $loai ) {
					continue;
				}
				$d = trim( $d );
				if ( $nhom && end( $nhom )[0] === $loai ) {
					$nhom[ count( $nhom ) - 1 ][1][] = $d;
				} else {
					$nhom[] = array( $loai, array( $d ) );
				}
			}
			foreach ( $nhom as [ $loai, $dong ] ) {
				if ( 'chu' === $loai ) {
					$xa();
					$ra[] = array(
						'blockName'   => 'core/paragraph',
						'attrs'       => array(),
						'innerHTML'   => '<p>' . implode( '<br>', $dong ) . '</p>',
						'innerBlocks' => array(),
					);
					continue;
				}
				if ( $cho && ( $nhieu || count( $dong ) > 1 ) ) {
					$cho[] = '';
				}
				array_push( $cho, ...$dong );
				$nhieu  = count( $dong ) > 1;
				$co_tho = true;
			}
		}
		$xa();
		if ( ! $co_tho ) {
			return null;
		}
		$chu = static fn( array $ds ): string => (string) preg_replace( '/[\s\x{00A0}]+/u', '', html_entity_decode( self::bo_the( implode( '', array_map( static fn( $k ) => (string) ( $k['innerHTML'] ?? '' ), $ds ) ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		return $chu( $ra ) === $chu( $khoi ) ? $ra : null; // không bao giờ đổi chữ
	}

	/** Một dòng trong đoạn văn: 'rong' | 'chu' (in nghiêng trọn dòng, hoặc nằm trong ngoặc) | 'loi' — như cadao_loai_dong. */
	private static function loai_dong( string $html ): string {
		// Cắt khoảng trắng (cả dấu cách không ngắt) theo ký tự, không theo byte: trim() với "\u{00A0}" cắt nhầm byte cuối của "à"
		$chu = (string) preg_replace( '/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', html_entity_decode( self::bo_the( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		if ( '' === $chu ) {
			return 'rong';
		}
		if ( preg_match( '~^\s*<(em|i)\b[^>]*>.*</\1>\s*$~is', $html ) || preg_match( '/^\(.*\)[.,;:]?$/us', $chu ) ) {
			return 'chu';
		}
		return 'loi';
	}

	/** Lời của một khối Thơ, đúng như MVP đọc (cadao_loi_tu_html_tho): mỗi dòng cắt khoảng trắng hai đầu. */
	public static function loi_tho( string $html ): string {
		$h = (string) preg_replace( '~<br\s*/?>~i', "\n", $html );
		$h = html_entity_decode( self::bo_the( $h ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$dong = array_map( array( self::class, 'cat' ), explode( "\n", str_replace( "\r", '', $h ) ) );
		return trim( implode( "\n", $dong ), "\n" );
	}

	/** Chữ của một đoạn HTML: xuống dòng ở <br>, bỏ thẻ, giải mã, cắt khoảng trắng hai đầu mỗi dòng và cả đoạn. */
	public static function chu( string $html ): string {
		return trim( self::loi_tho( $html ) );
	}

	public static function cat( string $s ): string {
		return (string) preg_replace( '/^' . self::KHOANG_TRANG . '+|' . self::KHOANG_TRANG . '+$/u', '', $s );
	}

	/** Như wp_strip_all_tags: bỏ cả nội dung script / style rồi bỏ thẻ. */
	private static function bo_the( string $h ): string {
		return strip_tags( (string) preg_replace( '@<(script|style)[^>]*?>.*?</\1>@si', '', $h ) );
	}

	/** Số dòng có chữ của lời. */
	private static function so_dong( ?string $loi ): int {
		return null === $loi ? 0 : count( array_filter( explode( "\n", $loi ), static fn( string $d ): bool => '' !== trim( $d ) ) );
	}

	public static function ngan( string $s ): string {
		$s = (string) preg_replace( '/\s+/u', ' ', $s );
		return mb_strlen( $s ) > 40 ? mb_substr( $s, 0, 40 ) . '…' : $s;
	}
}
