<?php

namespace Phongdao\DichVu\NhapDuLieuCu;

use Phongdao\MienDuLieu\BoDau;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\TheLoai;

/**
 * Dựng các phần V2 cho một bài MVP (docs/29 mục 2, 3) — PHP thuần, không ghi gì. Bước nhập thử in kết quả này,
 * bước nhập thật ghi đúng kết quả này qua LuuPhan (gọi nhap_cu).
 *
 * - Lời, dị bản, câu hỏi, đáp án, chú thích tách từ nội dung bài (TachBai) — đúng thứ trang MVP đang hiện.
 * - Người làm không đoán: lời / dị bản / câu hỏi / đáp án trùng từng chữ với câu trong kho (câu chính hoặc câu đã gộp)
 *   là máy chép, còn lại người viết; chú thích giữ đúng cách MVP đã đánh dấu (danh sách cadao-chep = máy chép).
 * - Nguồn chỉ lấy từ chỗ người đã điền (ô Nguồn của bài, ô "Lời đã đối chiếu với"); không ô nào được tự điền.
 * - Ý nghĩa = Hiểu nhanh đã lên trang, giữ dấu "có hỗ trợ của AI"; nháp máy thành việc chờ, không vào bài.
 * - So "trước = sau" từng ký tự; khác chữ → "Cần xem", khác khoảng trắng / dạng Unicode → ghi chú.
 */
final class DungPhan {

	/** Thể loại cũ → mới (CADAO_THE_LOAI_CU của MVP). */
	public const THE_LOAI_CU = array( 'thanh-ngu' => 'tuc-ngu' );

	/**
	 * @param array{id:int, the_loai:string[], khoi:array, tom_tat:string, meta:array<string,mixed>, kho:list<array{loi:string,du_lieu:array,nguon_id:string}>} $bai
	 * @param array{ho_so_nguon:array<string,int>, ho_so:callable(string,string,string):int} $nc
	 *        ho_so_nguon: mã nguồn tự động => ID hồ sơ; ho_so: tìm / tạo hồ sơ theo (loại, tên, đường dẫn)
	 * @return array{the_loai:string[], phan:array<string,array>, nhap:?array, can_xem:string[], ghi_chu:string[], so_sanh:array<string,string>}
	 */
	public static function dung( array $bai, array $nc ): array {
		$can_xem = array();
		$ghi_chu = array();

		$the_loai = array();
		foreach ( $bai['the_loai'] as $tl ) {
			$tl = self::THE_LOAI_CU[ $tl ] ?? $tl;
			if ( TheLoai::co( $tl ) ) {
				$the_loai[] = $tl;
			} else {
				$can_xem[] = "thể loại «{$tl}» không còn ở V2";
			}
		}
		$the_loai = array_values( array_unique( $the_loai ) );
		if ( ! $the_loai ) {
			$the_loai  = array( 'ca-dao' ); // như MVP: bài chưa chọn thể loại hiện ở /ca-dao/…
			$ghi_chu[] = 'chưa chọn thể loại: để Ca dao như đường dẫn MVP';
		}
		$chinh = $the_loai[0];

		$t       = TachBai::tach( $bai['khoi'] );
		$can_xem = array_merge( $can_xem, $t['can_xem'] );
		if ( $t['doan_van'] ) {
			$ghi_chu[] = 'lời lấy từ khối Đoạn văn (bài chưa có khối Thơ), không đổi chữ nào';
		}
		$meta    = $bai['meta'];
		$ho_so   = static fn( string $nguon_id ): array => empty( $nc['ho_so_nguon'][ $nguon_id ] ) ? array() : array( 'nguon' => array( 'ho_so' => (int) $nc['ho_so_nguon'][ $nguon_id ] ) );

		// Chữ máy đã lấy (câu trong kho của bài và các câu đã gộp vào bài) → mã nguồn
		$kho = array(
			'loi'     => array(),
			'cau_hoi' => array(),
			'dap_an'  => array(),
		);
		$kho_ct = array();
		foreach ( $bai['kho'] as $h ) {
			$d = $h['du_lieu'];
			foreach ( array_merge( array( (string) $h['loi'] ), array_map( 'strval', (array) ( $d['di_ban'] ?? array() ) ) ) as $l ) {
				$kho['loi'][ self::so_chu( $l ) ] ??= $h['nguon_id'];
			}
			foreach ( array( 'cau_hoi', 'dap_an' ) as $k ) {
				if ( ! empty( $d[ $k ] ) ) {
					$kho[ $k ][ self::so_chu( (string) $d[ $k ] ) ] ??= $h['nguon_id'];
				}
			}
			foreach ( (array) ( $d['chu_thich'] ?? array() ) as $c ) {
				$kho_ct[ self::khoa_ct( (string) ( $c['tu'] ?? '' ), (string) ( $c['noi_dung'] ?? '' ) ) ] ??= $h['nguon_id'];
			}
		}
		$nguon_chinh = (string) ( $bai['kho'][0]['nguon_id'] ?? '' );
		$may_hay_nguoi = static function ( string $loai, string $chu ) use ( $kho, $ho_so ): array {
			$nguon = $kho[ $loai ][ self::so_chu( $chu ) ] ?? null;
			return null === $nguon ? array( 'nguoi_lam' => NguoiLam::NGUOI_VIET ) : array( 'nguoi_lam' => NguoiLam::MAY_CHEP ) + $ho_so( $nguon );
		};

		// Nguồn người biên soạn điền cho bài (hộp "Nguồn của chú thích") → hồ sơ + nguồn của chú thích người viết
		$nguon_bai = null;
		$ten_nguon = trim( (string) ( $meta['nguon_ten'] ?? '' ) );
		if ( '' !== $ten_nguon ) {
			$nguon_bai = array_filter(
				array(
					'ho_so'         => ( $nc['ho_so'] )( 'web', $ten_nguon, (string) ( $meta['nguon_url'] ?? '' ) ),
					'url'           => trim( (string) ( $meta['nguon_url'] ?? '' ) ),
					'ngay_truy_cap' => self::ngay( (string) ( $meta['ngay_truy_cap'] ?? '' ) ),
				)
			);
			$ngay = trim( (string) ( $meta['ngay_truy_cap'] ?? '' ) );
			if ( '' !== $ngay && '' === self::ngay( $ngay ) ) {
				$can_xem[] = "ngày truy cập «{$ngay}» của nguồn bài không đọc được";
			}
		}

		$phan = array();
		if ( null !== $t['loi'] ) {
			$phan['loi'] = array( 'dong' => explode( "\n", $t['loi'] ) ) + $may_hay_nguoi( 'loi', $t['loi'] );
			$dc_ten      = trim( (string) ( $meta['doi_chieu_ten'] ?? '' ) );
			if ( '' !== $dc_ten ) {
				$dc_trang = trim( (string) ( $meta['doi_chieu_trang'] ?? '' ) );
				if ( '' === $dc_trang ) {
					$can_xem[] = "đối chiếu với «{$dc_ten}» chưa ghi trang";
				} else {
					$phan['loi']['doi_chieu'] = array_filter(
						array(
							'ho_so' => ( $nc['ho_so'] )( 'sach', $dc_ten, '' ),
							'trang' => $dc_trang,
							'url'   => trim( (string) ( $meta['doi_chieu_url'] ?? '' ) ),
						)
					);
				}
			}
		}
		if ( $t['di_ban'] ) {
			$phan['di_ban'] = array_map( static fn( string $l ): array => array( 'dong' => explode( "\n", $l ) ) + $may_hay_nguoi( 'loi', $l ), $t['di_ban'] );
		}
		foreach ( array( 'cau_hoi', 'dap_an' ) as $k ) {
			if ( null === $t[ $k ] ) {
				continue;
			}
			if ( 'cau-do' !== $chinh ) {
				$can_xem[] = 'cau_hoi' === $k ? 'có dòng in đậm ngay sau lời (bài không phải câu đố)' : 'có đáp án (bài không phải câu đố)';
				continue;
			}
			$phan[ $k ] = array( 'chu' => $t[ $k ] ) + $may_hay_nguoi( $k, $t[ $k ] );
		}
		if ( $t['chu_thich'] ) {
			$co_nguoi_viet = false;
			$phan['chu_thich'] = array();
			foreach ( $t['chu_thich'] as $c ) {
				$muc = array_filter(
					array(
						'tu'        => $c['tu'],
						'loi_giai'  => $c['noi_dung'],
						// Mục từ MVP tạo từ chú thích này (cùng từ + lời giải): chạm từ trên trang mở đúng mục từ
						'muc_tu_id' => '' !== $c['tu'] && isset( $nc['muc_tu'] ) ? ( $nc['muc_tu'] )( $c['tu'], $c['noi_dung'] ) : 0,
					)
				);
				if ( $c['chep'] ) {
					$muc += array( 'nguoi_lam' => NguoiLam::MAY_CHEP ) + $ho_so( $kho_ct[ self::khoa_ct( $c['tu'], $c['noi_dung'] ) ] ?? $nguon_chinh );
				} else {
					$co_nguoi_viet = true;
					$muc['nguoi_lam'] = NguoiLam::NGUOI_VIET;
					if ( $nguon_bai ) {
						$muc['nguon'] = $nguon_bai;
					}
				}
				$phan['chu_thich'][] = $muc;
			}
			if ( $nguon_bai && ! $co_nguoi_viet ) {
				$can_xem[] = "nguồn «{$ten_nguon}» của bài chưa gắn vào chú thích nào";
			}
		} elseif ( $nguon_bai ) {
			$can_xem[] = "nguồn «{$ten_nguon}» của bài chưa gắn vào chú thích nào";
		}

		$tom_tat = trim( (string) $bai['tom_tat'] );
		$ai      = ! empty( $meta['_cadao_ho_tro_ai'] );
		if ( '' !== $tom_tat ) {
			$phan['y_nghia'] = array(
				'chu'       => $tom_tat,
				'ai'        => $ai,
				'nguoi_lam' => $ai ? NguoiLam::AI_DA_DUYET : NguoiLam::NGUOI_VIET,
			);
		} elseif ( $ai ) {
			$ghi_chu[] = 'có dấu "hỗ trợ AI" nhưng chưa có Hiểu nhanh: bỏ dấu';
		}
		$nhap = trim( (string) ( $meta['_cadao_nhap_hieu_nhanh'] ?? '' ) );
		$nhap = '' !== $nhap && self::so_chu( $nhap ) !== self::so_chu( $tom_tat ) ? array( 'chu' => $nhap, 'ai' => true ) : null;

		if ( '' !== (string) ( $meta['_cadao_nguon_id'] ?? '' ) ) {
			$phan['nguon_may'] = array_filter(
				array(
					'nguon_id' => (string) $meta['_cadao_nguon_id'],
					'url_goc'  => (string) ( $meta['_cadao_url_goc'] ?? '' ),
					'ma_goc'   => (string) ( $meta['_cadao_ma_goc'] ?? '' ),
					'ngay_lay' => self::ngay( (string) ( $meta['_cadao_ngay_lay'] ?? '' ) ),
				)
			);
		}

		// Kiểm từng phần như mọi lần lưu (lằn ranh, độ dài…); phần hỏng không nhập, vào "Cần xem"
		foreach ( $phan as $ten => $gia_tri ) {
			[ $sach, $loi ] = KiemPhan::kiem(
				$ten,
				$gia_tri,
				array(
					'loai_bai' => 'tac_pham',
					'the_loai' => $chinh,
					'goi'      => KiemPhan::GOI_NHAP_CU,
				)
			);
			if ( $loi || null === $sach ) {
				unset( $phan[ $ten ] );
				$can_xem[] = "phần «{$ten}» không nhập được: " . implode( ' ', $loi );
				continue;
			}
			$phan[ $ten ] = $sach;
		}

		// Trước = sau
		$sau = static fn( ?array $p ): ?string => null === $p ? null : implode( "\n", $p['dong'] );
		$so_sanh = array(
			'loi'     => self::so( $t['loi'], $sau( $phan['loi'] ?? null ) ),
			'di_ban'  => self::so_ds( $t['di_ban'], array_map( $sau, $phan['di_ban']['muc'] ?? array() ) ),
			'chu_thich' => self::so_ds(
				array_map( static fn( $c ) => $c['tu'] . '|' . $c['noi_dung'], $t['chu_thich'] ),
				array_map( static fn( $c ) => ( $c['tu'] ?? '' ) . '|' . $c['loi_giai'], $phan['chu_thich']['muc'] ?? array() )
			),
			'y_nghia' => self::so( '' === $tom_tat ? null : $tom_tat, $phan['y_nghia']['chu'] ?? null ),
		);
		if ( 'cau-do' === $chinh ) {
			$so_sanh['cau_hoi'] = self::so( $t['cau_hoi'], $phan['cau_hoi']['chu'] ?? null );
			$so_sanh['dap_an']  = self::so( $t['dap_an'], $phan['dap_an']['chu'] ?? null );
		}
		$ten_phan = array(
			'loi'       => 'lời',
			'di_ban'    => 'dị bản',
			'chu_thich' => 'chú thích',
			'y_nghia'   => 'ý nghĩa',
			'cau_hoi'   => 'câu hỏi',
			'dap_an'    => 'đáp án',
		);
		foreach ( $so_sanh as $k => $kq ) {
			if ( 'lech' === $kq ) {
				$can_xem[] = "{$ten_phan[$k]} sau khi nhập khác bản đang hiện";
			} elseif ( 'chuan_hoa' === $kq ) {
				$ghi_chu[] = "{$ten_phan[$k]}: chỉ khác khoảng trắng / dạng chữ Unicode";
			}
		}

		return array(
			'the_loai' => $the_loai,
			'phan'     => $phan,
			'nhap'     => $nhap,
			'can_xem'  => array_values( array_unique( $can_xem ) ),
			'ghi_chu'  => array_values( array_unique( $ghi_chu ) ),
			'so_sanh'  => $so_sanh,
		);
	}

	/**
	 * Tên đường dẫn mới cho bài có đuôi số (-2, -3…; D-089): thêm chữ phân biệt lấy từ dòng sau của lời.
	 *
	 * @param callable(string):bool $da_co tên đã có người dùng chưa
	 * @return ?string null = giữ tên cũ (không có đuôi số) hoặc không tìm được tên mới
	 */
	public static function ten_moi( string $ten, ?string $loi, callable $da_co ): ?string {
		if ( ! preg_match( '/^(.+)-\d+$/', $ten, $m ) ) {
			return null;
		}
		$goc  = $m[1];
		$dong = array_values( array_filter( array_map( 'trim', explode( "\n", (string) $loi ) ) ) );
		$co   = array_flip( explode( '-', $goc ) );
		foreach ( array_slice( $dong, 1 ) ?: $dong as $d ) {
			$tu = array_values( array_filter( preg_split( '/[^a-z0-9]+/', strtolower( BoDau::bo_dau( BoDau::nfc( $d ) ) ) ) ?: array(), static fn( $x ) => '' !== $x && ! isset( $co[ $x ] ) ) );
			$ra = $goc;
			foreach ( array_slice( $tu, 0, 6 ) as $x ) {
				$ra .= '-' . $x;
				if ( strlen( $ra ) <= 190 && ! $da_co( $ra ) ) {
					return $ra;
				}
			}
		}
		return null;
	}

	/** Dạng so: chuẩn hoá Unicode, khoảng trắng, dòng trống liền nhau — chỉ để biết hai bản có cùng chữ không. */
	public static function so_chu( ?string $s ): string {
		$s = BoDau::nfc( str_replace( array( "\r\n", "\r" ), "\n", (string) $s ) );
		$s = (string) preg_replace( array( '/[\t\x{00A0}\p{Zs}]+/u', '/ ?\n ?/u', '/\n{3,}/u' ), array( ' ', "\n", "\n\n" ), $s );
		return trim( $s );
	}

	/** khop: đúng từng ký tự; chuan_hoa: chỉ khác khoảng trắng / Unicode; lech: khác chữ (kể cả mất hẳn). */
	private static function so( ?string $truoc, ?string $sau ): string {
		if ( $truoc === $sau ) {
			return 'khop';
		}
		return null !== $truoc && null !== $sau && self::so_chu( $truoc ) === self::so_chu( $sau ) ? 'chuan_hoa' : 'lech';
	}

	private static function so_ds( array $truoc, array $sau ): string {
		if ( count( $truoc ) !== count( $sau ) ) {
			return 'lech';
		}
		$kq = 'khop';
		foreach ( array_values( $truoc ) as $i => $x ) {
			$m = self::so( $x, array_values( $sau )[ $i ] );
			if ( 'lech' === $m ) {
				return 'lech';
			}
			if ( 'chuan_hoa' === $m ) {
				$kq = 'chuan_hoa';
			}
		}
		return $kq;
	}

	private static function khoa_ct( string $tu, string $noi_dung ): string {
		return mb_strtolower( trim( $tu ) ) . '|' . self::so_chu( $noi_dung );
	}

	/** Ngày MVP ('2026-10-25', '2026-10-25 08:00:00', '25/10/2026') → NNNN-TT-NN; không đọc được thì bỏ. */
	private static function ngay( string $s ): string {
		$s = trim( $s );
		if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})/', $s, $m ) && checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) ) {
			return "{$m[1]}-{$m[2]}-{$m[3]}";
		}
		if ( preg_match( '#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $s, $m ) && checkdate( (int) $m[2], (int) $m[1], (int) $m[3] ) ) {
			return sprintf( '%04d-%02d-%02d', $m[3], $m[2], $m[1] );
		}
		return '';
	}
}
