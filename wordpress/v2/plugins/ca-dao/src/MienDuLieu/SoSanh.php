<?php

namespace Phongdao\MienDuLieu;

/**
 * So trước/sau một lần sửa (docs/30 mục 4.4; T-170) — PHP thuần, không gọi WordPress. Một giá trị phần bài (hay tập phân
 * loại, trạng thái, hồ sơ nguồn) được trải thành các dòng chữ người đọc được, rồi so từng dòng (dãy con chung dài nhất).
 * Chỉ để xem: không đổi dữ liệu.
 */
final class SoSanh {

	/** Trường kỹ thuật không đưa vào phần so (ai sửa, lúc nào đã có ở dòng lịch sử). */
	private const BO_QUA = array( 'sua_boi', 'sua_luc', 'duyet_boi', 'duyet_luc', 'nguoi_lam', 'vi_tri', 'muc_tu_id', 'term_ids', 'nhan_vat', 'attachment_id' );

	/** Tên trường => nhãn tiếng Việt. */
	private const NHAN = array(
		'tu'             => 'Từ',
		'loi_giai'       => 'Lời giải',
		'noi'            => 'Nơi',
		'nguoi_ke'       => 'Người kể',
		'nam'            => 'Năm',
		'trang'          => 'Trang',
		'url'            => 'Đường dẫn',
		'ho_so'          => 'Hồ sơ nguồn',
		'ly_do'          => 'Lý do',
		'su_kien'        => 'Sự kiện',
		'tieu_de'        => 'Tiêu đề',
		'tu_dong'        => 'Từ dòng',
		'den_dong'       => 'Đến dòng',
		'trang_thai'     => 'Trạng thái',
		'doi_chieu'      => 'Đối chiếu',
		'nguon'          => 'Nguồn',
		'loai'           => 'Loại',
		'ten'            => 'Tên',
		'chu'            => 'Nội dung',
		'chep_chu_thich' => 'Chép chú thích',
		'bat'            => 'Bật',
		'so_bai_moi_ngay' => 'Số câu mới mỗi ngày',
		'loai_lay'       => 'Loại lấy',
		'bo_qua_co'      => 'Bỏ qua cờ',
		'giay_phep'      => 'Giấy phép',
		'tac_gia'        => 'Tác giả',
		'nha_xuat_ban'   => 'Nhà xuất bản',
		'ghi_cong'       => 'Cách ghi công',
		'xin_phep'       => 'Xin phép',
		'chinh_sach_hien' => 'Hiện với người đọc',
		'ghi_chu'        => 'Ghi chú',
	);

	/** Trạng thái bài => chữ. */
	private const TRANG_THAI = array(
		'publish' => 'Đã đăng',
		'draft'   => 'Bản nháp',
		'pending' => 'Chờ',
		'private' => 'Đã ẩn',
		'future'  => 'Đã hẹn',
		'trash'   => 'Thùng rác',
	);

	/**
	 * Giá trị đã lưu → các dòng chữ. Phần dạng danh sách ({muc: [...]}) đánh số từng mục.
	 *
	 * @return string[]
	 */
	public static function dong( mixed $gt ): array {
		if ( null === $gt || array() === $gt ) {
			return array();
		}
		if ( ! is_array( $gt ) ) {
			return array( self::chu( $gt ) );
		}
		if ( isset( $gt['muc'] ) && is_array( $gt['muc'] ) ) {
			$ra = array();
			foreach ( array_values( $gt['muc'] ) as $i => $m ) {
				foreach ( array_values( self::dong_mot( is_array( $m ) ? $m : array( 'chu' => $m ) ) ) as $j => $l ) {
					$ra[] = ( 0 === $j ? ( $i + 1 ) . '. ' : '    ' ) . $l;
				}
			}
			return $ra;
		}
		if ( array_is_list( $gt ) ) {
			return array_map( array( self::class, 'chu' ), $gt );
		}
		return self::dong_mot( $gt );
	}

	/** @return string[] */
	private static function dong_mot( array $m ): array {
		$ra = array();
		foreach ( $m as $k => $v ) {
			if ( in_array( $k, self::BO_QUA, true ) ) {
				continue;
			}
			if ( 'dong' === $k ) {
				foreach ( (array) $v as $d ) {
					$ra[] = self::chu( $d );
				}
				continue;
			}
			if ( 'chu' === $k && is_string( $v ) ) {
				foreach ( explode( "\n", $v ) as $d ) {
					$ra[] = $d;
				}
				continue;
			}
			if ( 'ten' === $k && is_array( $v ) ) {
				foreach ( $v as $t ) {
					$ra[] = self::chu( $t );
				}
				continue;
			}
			if ( 'ai' === $k ) {
				if ( $v ) {
					$ra[] = '(có hỗ trợ AI)';
				}
				continue;
			}
			$nhan = self::NHAN[ $k ] ?? (string) $k;
			if ( 'trang_thai' === $k && is_string( $v ) ) {
				$ra[] = $nhan . ': ' . ( self::TRANG_THAI[ $v ] ?? $v );
				continue;
			}
			if ( is_array( $v ) ) {
				$con = self::phang( $v );
				if ( '' !== $con ) {
					$ra[] = $nhan . ': ' . $con;
				}
				continue;
			}
			if ( is_bool( $v ) ) {
				$ra[] = $nhan . ': ' . ( $v ? 'Có' : 'Không' );
				continue;
			}
			if ( '' !== self::chu( $v ) ) {
				$ra[] = $nhan . ': ' . self::chu( $v );
			}
		}
		return $ra;
	}

	/** Mảng con (vd. đối chiếu, nguồn) → một dòng "Trang: 12 · Đường dẫn: …". */
	private static function phang( array $v ): string {
		$o = array();
		foreach ( $v as $k => $x ) {
			if ( in_array( $k, self::BO_QUA, true ) || null === $x || '' === $x || array() === $x ) {
				continue;
			}
			$chu = is_array( $x ) ? self::phang( $x ) : ( is_bool( $x ) ? ( $x ? 'Có' : 'Không' ) : self::chu( $x ) );
			if ( '' !== $chu ) {
				$o[] = is_int( $k ) ? $chu : ( self::NHAN[ $k ] ?? (string) $k ) . ': ' . $chu;
			}
		}
		return implode( ' · ', $o );
	}

	private static function chu( mixed $v ): string {
		return is_scalar( $v ) ? (string) $v : '';
	}

	/**
	 * So hai dãy dòng: '=' giữ, '-' chỉ có ở trước, '+' chỉ có ở sau (theo thứ tự đọc).
	 *
	 * @param string[] $a
	 * @param string[] $b
	 * @return list<array{0:string,1:string}>
	 */
	public static function khac( array $a, array $b ): array {
		$a = array_values( $a );
		$b = array_values( $b );
		$n = count( $a );
		$m = count( $b );
		// Bảng độ dài dãy con chung dài nhất, tính từ cuối
		$l = array_fill( 0, $n + 1, array_fill( 0, $m + 1, 0 ) );
		for ( $i = $n - 1; $i >= 0; $i-- ) {
			for ( $j = $m - 1; $j >= 0; $j-- ) {
				$l[ $i ][ $j ] = $a[ $i ] === $b[ $j ] ? $l[ $i + 1 ][ $j + 1 ] + 1 : max( $l[ $i + 1 ][ $j ], $l[ $i ][ $j + 1 ] );
			}
		}
		$ra = array();
		$i  = 0;
		$j  = 0;
		while ( $i < $n && $j < $m ) {
			if ( $a[ $i ] === $b[ $j ] ) {
				$ra[] = array( '=', $a[ $i ] );
				++$i;
				++$j;
			} elseif ( $l[ $i + 1 ][ $j ] >= $l[ $i ][ $j + 1 ] ) {
				$ra[] = array( '-', $a[ $i ] );
				++$i;
			} else {
				$ra[] = array( '+', $b[ $j ] );
				++$j;
			}
		}
		for ( ; $i < $n; $i++ ) {
			$ra[] = array( '-', $a[ $i ] );
		}
		for ( ; $j < $m; $j++ ) {
			$ra[] = array( '+', $b[ $j ] );
		}
		return $ra;
	}

	/**
	 * Hai bản hồ sơ / cài đặt (mảng phẳng) → các dòng "Nhãn: cũ → mới" cho trường đã đổi.
	 *
	 * @return string[]
	 */
	public static function truong_doi( array $truoc, array $sau ): array {
		$ra = array();
		$rong = static fn( mixed $x ): bool => null === $x || '' === $x || array() === $x || false === $x;
		foreach ( array_unique( array_merge( array_keys( $truoc ), array_keys( $sau ) ) ) as $k ) {
			$cu  = $truoc[ $k ] ?? null;
			$moi = $sau[ $k ] ?? null;
			if ( $cu === $moi || ( $rong( $cu ) && $rong( $moi ) ) || in_array( $k, self::BO_QUA, true ) ) {
				continue;
			}
			$hien = static fn( mixed $x ): string => null === $x || '' === $x || array() === $x ? '(trống)' : ( is_bool( $x ) ? ( $x ? 'Có' : 'Không' ) : ( is_array( $x ) ? self::phang( $x ) : self::chu( $x ) ) );
			$ra[] = ( self::NHAN[ $k ] ?? (string) $k ) . ': ' . $hien( $cu ) . ' → ' . $hien( $moi );
		}
		return $ra;
	}
}
