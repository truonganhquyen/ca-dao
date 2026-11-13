<?php

namespace Phongdao\MienDuLieu;

/**
 * Kiểm và chuẩn hoá một phần của bài trước khi lưu (D-094, docs/26 mục 4.3). Mọi lần ghi phần đi qua đây
 * (DichVu\LuuPhan), nên các lằn ranh nằm trong mã, không trong lời dặn (docs/28 mục 6):
 * - lời, dị bản không nhận người làm là AI;
 * - phần có hỗ trợ AI chỉ vào bài khi người có quyền duyệt bấm (máy không bao giờ đặt "đã duyệt");
 * - máy chỉ chép (người làm may_chep), không điền nguồn, trang, đường dẫn, nơi, người kể, năm, đối chiếu;
 * - không trường nguồn nào có giá trị mặc định: thiếu thì để trống ("chưa rõ").
 */
final class KiemPhan {

	/** Người bấm trên trang (REST có quyền + nonce). */
	public const GOI_NGUOI = 'nguoi';

	/** Nguồn tự động, lịch chạy, lệnh nạp. */
	public const GOI_MAY = 'may';

	/** Bước nhập dữ liệu cũ: mang đúng trạng thái người sáng lập đã đặt trên MVP (docs/29 mục 3). */
	public const GOI_NHAP_CU = 'nhap_cu';

	public const GOI = array( self::GOI_NGUOI, self::GOI_MAY, self::GOI_NHAP_CU );

	/** Trường do LuuPhan đặt; dữ liệu gửi lên có thì bỏ qua. */
	public const TRUONG_MAY_CHU = array( 'sua_boi', 'sua_luc', 'duyet_boi', 'duyet_luc' );

	/** Trường nguồn máy không bao giờ điền. */
	private const TRUONG_NGUON = array( 'doi_chieu', 'noi', 'nguoi_ke', 'nam' );

	private const DAI = array(
		'chu'      => 4000,
		'chu_ngan' => 200,
		'chu_dai'  => 20000,
	);

	private const SO_DONG  = 600;
	private const DAI_DONG = 400;

	/**
	 * @param array{loai_bai:string,the_loai:?string,goi:string,duyet?:bool} $nc
	 * @return array{0:?array,1:string[]} [giá trị đã chuẩn hoá (null = xoá phần), các lỗi bằng tiếng Việt]
	 */
	public static function kiem( string $phan, mixed $gia_tri, array $nc ): array {
		$dn = Phan::lay( (string) ( $nc['loai_bai'] ?? '' ), $phan );
		if ( null === $dn ) {
			return array( null, array( "Không có phần “{$phan}” cho loại bài này." ) );
		}
		$goi = (string) ( $nc['goi'] ?? '' );
		if ( ! in_array( $goi, self::GOI, true ) ) {
			return array( null, array( 'Không rõ ai đang lưu.' ) );
		}
		$loi = array();
		if ( self::GOI_MAY === $goi && ! $dn['may'] ) {
			$loi[] = "Máy không được ghi phần “{$phan}”.";
		}
		if ( 'nguon_may' === $phan && self::GOI_NGUOI === $goi ) {
			$loi[] = 'Địa chỉ gốc máy đã lấy chỉ do máy ghi.';
		}
		if ( null === $gia_tri ) {
			if ( self::GOI_MAY === $goi ) {
				$loi[] = "Máy không được xoá phần “{$phan}”.";
			}
			return array( null, $loi );
		}
		if ( null !== $dn['the_loai'] && ! in_array( $nc['the_loai'] ?? null, $dn['the_loai'], true ) ) {
			$loi[] = "Phần “{$phan}” không dùng cho thể loại này.";
		}
		if ( ! is_array( $gia_tri ) ) {
			$loi[] = "Phần “{$phan}”: dữ liệu không đúng dạng.";
			return array( null, $loi );
		}

		if ( Phan::DS === $dn['dang'] ) {
			$ds = $gia_tri;
			if ( array_key_exists( 'muc', $gia_tri ) ) {
				$ds = $gia_tri['muc'];
				foreach ( array_keys( $gia_tri ) as $k ) {
					if ( 'muc' !== $k && ! in_array( $k, self::TRUONG_MAY_CHU, true ) ) {
						$loi[] = "Phần “{$phan}”: trường lạ “{$k}”.";
					}
				}
			}
			if ( ! is_array( $ds ) || ! array_is_list( $ds ) ) {
				$loi[] = "Phần “{$phan}”: cần một danh sách.";
				return array( null, $loi );
			}
			$muc = array();
			foreach ( $ds as $i => $m ) {
				$muc[] = self::mot_muc( $phan, $dn, $m, $goi, $nc, $loi, $phan . ' #' . ( $i + 1 ) );
			}
			$sach = $muc ? array( 'muc' => $muc ) : null;
		} else {
			$sach = self::mot_muc( $phan, $dn, $gia_tri, $goi, $nc, $loi, $phan );
		}
		return $loi ? array( null, array_values( array_unique( $loi ) ) ) : array( $sach, array() );
	}

	/** Bỏ các trường do máy chủ đặt — để so hai giá trị theo nội dung. */
	public static function noi_dung( ?array $gia_tri ): ?array {
		if ( null === $gia_tri ) {
			return null;
		}
		$bo = array_flip( self::TRUONG_MAY_CHU );
		$gia_tri = array_diff_key( $gia_tri, $bo );
		if ( isset( $gia_tri['muc'] ) && is_array( $gia_tri['muc'] ) ) {
			$gia_tri['muc'] = array_map( static fn( $m ) => is_array( $m ) ? array_diff_key( $m, $bo ) : $m, $gia_tri['muc'] );
		}
		return $gia_tri;
	}

	private static function mot_muc( string $phan, array $dn, mixed $m, string $goi, array $nc, array &$loi, string $nhan ): ?array {
		if ( ! is_array( $m ) || ( $m && array_is_list( $m ) ) ) {
			$loi[] = "{$nhan}: dữ liệu không đúng dạng.";
			return null;
		}
		$truong = $dn['truong'];
		$co_goc = null !== $dn['nguoi_lam'];
		foreach ( array_keys( $m ) as $k ) {
			$hop_le = isset( $truong[ $k ] ) || in_array( $k, self::TRUONG_MAY_CHU, true ) || ( $co_goc && ( 'nguoi_lam' === $k || 'nguon' === $k ) );
			if ( ! $hop_le ) {
				$loi[] = "{$nhan}: trường lạ “{$k}”.";
			}
		}

		$ra = array();
		foreach ( $truong as $k => $kieu ) {
			$bat_buoc = str_ends_with( $kieu, '!' );
			$kieu     = rtrim( $kieu, '!' );
			$v        = array_key_exists( $k, $m ) ? self::truong( $kieu, $m[ $k ], "{$nhan}.{$k}", $loi ) : null;
			if ( 'bool' === $kieu ) {
				$ra[ $k ] = (bool) $v;
				continue;
			}
			if ( null === $v ) {
				if ( $bat_buoc ) {
					$loi[] = "{$nhan}: thiếu “{$k}”.";
				}
				continue;
			}
			$ra[ $k ] = $v;
		}

		if ( isset( $ra['dinh_dang'] ) ) {
			$ra['dinh_dang'] = DinhDang::sach( $ra['dinh_dang'] );
			$chu = $ra[ DinhDang::truong( $phan ) ?? '' ] ?? '';
			if ( DinhDang::chu( $ra['dinh_dang'] ) !== DinhDang::chu( DinhDang::tu_chu( $chu ) ) ) {
				$loi[] = "{$nhan}: định dạng không khớp chữ đang lưu.";
			}
			if ( '' === $ra['dinh_dang'] ) { unset( $ra['dinh_dang'] ); }
		}

		if ( $co_goc ) {
			$nl = $m['nguoi_lam'] ?? null;
			if ( ! is_string( $nl ) || ! in_array( $nl, $dn['nguoi_lam'], true ) ) {
				$loi[] = "{$nhan}: người làm không hợp lệ cho phần này.";
			} else {
				$ra['nguoi_lam'] = $nl;
			}
			if ( in_array( $phan, Phan::KHONG_AI, true ) && is_string( $nl ) && NguoiLam::la_ai( $nl ) ) {
				$loi[] = "{$nhan}: lời và dị bản không nhận người làm là AI.";
			}
			$nguon = array_key_exists( 'nguon', $m ) ? self::nguon( $m['nguon'], "{$nhan}.nguon", $loi ) : null;
			if ( null !== $nguon ) {
				$ra['nguon'] = $nguon;
			}
		}

		if ( isset( $truong['ai'] ) ) {
			$ai = $ra['ai'];
			if ( $ai !== ( NguoiLam::AI_DA_DUYET === ( $ra['nguoi_lam'] ?? '' ) ) ) {
				$loi[] = "{$nhan}: dấu “có hỗ trợ của AI” phải khớp người làm.";
			}
			if ( $ai && self::GOI_MAY === $goi ) {
				$loi[] = "{$nhan}: máy không được đưa phần có hỗ trợ AI vào bài.";
			}
			if ( $ai && self::GOI_NGUOI === $goi && empty( $nc['duyet'] ) ) {
				$loi[] = "{$nhan}: cần quyền duyệt để đưa phần có hỗ trợ AI lên trang.";
			}
		}

		if ( self::GOI_MAY === $goi ) {
			if ( $co_goc && NguoiLam::MAY_CHEP !== ( $ra['nguoi_lam'] ?? '' ) ) {
				$loi[] = "{$nhan}: máy chỉ ghi phần máy chép.";
			}
			foreach ( self::TRUONG_NGUON as $k ) {
				if ( isset( $ra[ $k ] ) ) {
					$loi[] = "{$nhan}: máy không điền “{$k}”.";
				}
			}
			if ( isset( $ra['nguon'] ) && array_diff( array_keys( $ra['nguon'] ), array( 'ho_so' ) ) ) {
				$loi[] = "{$nhan}: máy chỉ ghi hồ sơ nguồn, không điền trang, đường dẫn, ngày.";
			}
		}

		if ( 'boi_canh' === $phan && ! array_diff_key( $ra, array( 'nguoi_lam' => 1, 'nguon' => 1 ) ) ) {
			$loi[] = "{$nhan}: bối cảnh cần ít nhất một thông tin.";
		}
		if ( 'doan' === $phan && isset( $ra['tu_dong'], $ra['den_dong'] ) && $ra['den_dong'] < $ra['tu_dong'] ) {
			$loi[] = "{$nhan}: dòng cuối đứng trước dòng đầu.";
		}
		return $ra;
	}

	private static function truong( string $kieu, mixed $v, string $nhan, array &$loi ): mixed {
		if ( str_starts_with( $kieu, 'chon:' ) ) {
			if ( null === $v || '' === $v ) {
				return null;
			}
			if ( ! is_string( $v ) || ! in_array( $v, explode( '|', substr( $kieu, 5 ) ), true ) ) {
				$loi[] = "{$nhan}: giá trị không hợp lệ.";
				return null;
			}
			return $v;
		}
		switch ( $kieu ) {
			case 'dong':
				return self::dong( $v, $nhan, $loi );
			case 'chu':
			case 'chu_ngan':
			case 'chu_dai':
				return self::chu( $v, $kieu, $nhan, $loi );
			case 'bool':
				if ( null === $v || is_bool( $v ) ) {
					return $v;
				}
				$loi[] = "{$nhan}: cần đúng / sai.";
				return null;
			case 'so':
			case 'id':
				return self::so( $v, $nhan, $loi );
			case 'ds_id':
			case 'ds_so':
				if ( null === $v || array() === $v ) {
					return null;
				}
				if ( ! is_array( $v ) || ! array_is_list( $v ) ) {
					$loi[] = "{$nhan}: cần một danh sách số.";
					return null;
				}
				$so = array();
				foreach ( $v as $x ) {
					$n = self::so( $x, $nhan, $loi );
					if ( null !== $n ) {
						$so[] = $n;
					}
				}
				$so = array_values( array_unique( $so ) );
				return $so ?: null;
			case 'url':
				return self::url( $v, $nhan, $loi );
			case 'ngay':
				return self::ngay( $v, $nhan, $loi );
			case 'doi_chieu':
				return self::doi_chieu( $v, $nhan, $loi );
		}
		$loi[] = "{$nhan}: kiểu trường chưa khai báo “{$kieu}”.";
		return null;
	}

	/** Lời: mỗi phần tử một câu; dòng trống ở giữa là chỗ ngắt khổ, dòng trống đầu / cuối bỏ. */
	private static function dong( mixed $v, string $nhan, array &$loi ): ?array {
		if ( null === $v || array() === $v ) {
			return null;
		}
		if ( ! is_array( $v ) || ! array_is_list( $v ) ) {
			$loi[] = "{$nhan}: cần danh sách dòng.";
			return null;
		}
		$dong = array();
		foreach ( $v as $d ) {
			if ( ! is_string( $d ) ) {
				$loi[] = "{$nhan}: mỗi dòng phải là chữ.";
				return null;
			}
			$d = trim( (string) preg_replace( '/[ \t\x{00A0}]+/u', ' ', self::sach( $d ) ) );
			if ( str_contains( $d, "\n" ) ) {
				$loi[] = "{$nhan}: một dòng không được chứa xuống dòng.";
				return null;
			}
			if ( mb_strlen( $d ) > self::DAI_DONG ) {
				$loi[] = "{$nhan}: có dòng dài quá " . self::DAI_DONG . ' ký tự.';
				return null;
			}
			if ( '' === $d && ( ! $dong || '' === end( $dong ) ) ) {
				continue;
			}
			$dong[] = $d;
		}
		while ( $dong && '' === end( $dong ) ) {
			array_pop( $dong );
		}
		if ( count( $dong ) > self::SO_DONG ) {
			$loi[] = "{$nhan}: quá " . self::SO_DONG . ' dòng.';
			return null;
		}
		return $dong ?: null;
	}

	private static function chu( mixed $v, string $kieu, string $nhan, array &$loi ): ?string {
		if ( null === $v ) {
			return null;
		}
		if ( ! is_string( $v ) ) {
			$loi[] = "{$nhan}: cần chữ.";
			return null;
		}
		$v = self::sach( $v );
		if ( 'chu_ngan' === $kieu ) {
			$v = (string) preg_replace( '/\s+/u', ' ', $v );
		} else {
			$v = (string) preg_replace( array( '/[ \t\x{00A0}]+/u', '/ ?\n ?/u', '/\n{3,}/u' ), array( ' ', "\n", "\n\n" ), $v );
		}
		$v = trim( $v );
		if ( mb_strlen( $v ) > self::DAI[ $kieu ] ) {
			$loi[] = "{$nhan}: dài quá " . self::DAI[ $kieu ] . ' ký tự.';
			return null;
		}
		return '' === $v ? null : $v;
	}

	/** NFC, xuống dòng kiểu \n, bỏ ký tự điều khiển. */
	private static function sach( string $v ): string {
		$v = BoDau::nfc( str_replace( array( "\r\n", "\r" ), "\n", $v ) );
		return (string) preg_replace( '/[\x{0000}-\x{0008}\x{000B}-\x{001F}\x{007F}\x{200B}\x{FEFF}]/u', '', $v );
	}

	private static function so( mixed $v, string $nhan, array &$loi ): ?int {
		if ( null === $v || '' === $v ) {
			return null;
		}
		if ( is_string( $v ) && ctype_digit( $v ) ) {
			$v = (int) $v;
		}
		if ( ! is_int( $v ) || $v < 1 || $v > 1000000000 ) {
			$loi[] = "{$nhan}: cần số nguyên dương.";
			return null;
		}
		return $v;
	}

	private static function url( mixed $v, string $nhan, array &$loi ): ?string {
		if ( null === $v || '' === $v ) {
			return null;
		}
		$v = is_string( $v ) ? trim( $v ) : $v;
		if ( ! is_string( $v ) || strlen( $v ) > 500 || ! preg_match( '#^https?://[^\s/$.?\#][^\s]*$#i', $v ) ) {
			$loi[] = "{$nhan}: đường dẫn phải bắt đầu bằng http:// hoặc https://.";
			return null;
		}
		return $v;
	}

	private static function ngay( mixed $v, string $nhan, array &$loi ): ?string {
		if ( null === $v || '' === $v ) {
			return null;
		}
		if ( ! is_string( $v ) || ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $v, $p ) || ! checkdate( (int) $p[2], (int) $p[3], (int) $p[1] ) ) {
			$loi[] = "{$nhan}: ngày cần dạng NNNN-TT-NN.";
			return null;
		}
		return $v;
	}

	/** Nguồn của một phần: mọi trường không bắt buộc, không mặc định; trống hết = không ghi nguồn. */
	private static function nguon( mixed $v, string $nhan, array &$loi ): ?array {
		if ( null === $v || array() === $v ) {
			return null;
		}
		if ( ! is_array( $v ) || array_is_list( $v ) ) {
			$loi[] = "{$nhan}: dữ liệu không đúng dạng.";
			return null;
		}
		$kieu = array(
			'ho_so'         => 'id',
			'trang'         => 'chu_ngan',
			'url'           => 'url',
			'ngay_truy_cap' => 'ngay',
			'ghi_chu'       => 'chu_ngan',
		);
		$ra = array();
		foreach ( $v as $k => $x ) {
			if ( ! isset( $kieu[ $k ] ) ) {
				$loi[] = "{$nhan}: trường lạ “{$k}”.";
				continue;
			}
			$x = self::truong( $kieu[ $k ], $x, "{$nhan}.{$k}", $loi );
			if ( null !== $x ) {
				$ra[ $k ] = $x;
			}
		}
		return $ra ?: null;
	}

	/** "Lời đã đối chiếu với": sách (hồ sơ nguồn) và trang bắt buộc; chỉ người điền (máy bị chặn ở mot_muc). */
	private static function doi_chieu( mixed $v, string $nhan, array &$loi ): ?array {
		if ( null === $v || array() === $v ) {
			return null;
		}
		if ( ! is_array( $v ) || array_is_list( $v ) || array_diff( array_keys( $v ), array( 'ho_so', 'trang', 'url' ) ) ) {
			$loi[] = "{$nhan}: dữ liệu không đúng dạng.";
			return null;
		}
		$ra = array(
			'ho_so' => self::so( $v['ho_so'] ?? null, "{$nhan}.ho_so", $loi ),
			'trang' => self::chu( $v['trang'] ?? null, 'chu_ngan', "{$nhan}.trang", $loi ),
		);
		if ( null === $ra['ho_so'] || null === $ra['trang'] ) {
			$loi[] = "{$nhan}: cần cả sách và số trang.";
			return null;
		}
		$url = self::url( $v['url'] ?? null, "{$nhan}.url", $loi );
		if ( null !== $url ) {
			$ra['url'] = $url;
		}
		return $ra;
	}
}
