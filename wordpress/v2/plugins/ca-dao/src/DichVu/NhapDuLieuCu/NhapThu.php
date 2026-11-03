<?php

namespace Phongdao\DichVu\NhapDuLieuCu;

use Phongdao\KhoLuu\HoSo;
use Phongdao\MienDuLieu\BoDau;

defined( 'ABSPATH' ) || exit;

/**
 * Nhập thử (docs/29 mục 4, `wp phongdao nhap --thu`): chạy mọi quy tắc nhập trong bộ nhớ, không ghi gì vào cơ sở dữ
 * liệu. Báo cáo chỉ có mã bài, số lượng, lý do — không chép lời (repo công khai, báo cáo có thể được dán lại).
 */
final class NhapThu {

	/** ID tạm cho hồ sơ nguồn chưa có (chỉ trong bộ nhớ của lần chạy thử). */
	private const ID_TAM = 900000000;

	/**
	 * @return array{tong:int, trang_thai:array<string,int>, cach:array<string,int>, can_xem:array<string,int[]>, ghi_chu:array<string,int[]>, so_sanh:array<string,array<string,int>>, phan:array<string,int>, nhap:int, doi_ten:list<array{0:int,1:string,2:string}>, doi_the_loai:int[], ho_so_moi:list<array{0:string,1:string}>, bai:array<int,array>}
	 */
	public static function chay( bool $giu_bai = false ): array {
		$bc = array(
			'tong'         => 0,
			'trang_thai'   => array(),
			'cach'         => array(
				'kho_chua_sua' => 0,
				'sua_tay'      => 0,
				'khong_kho'    => 0,
				'khong_mang'   => 0,
			),
			'can_xem'      => array(),
			'ghi_chu'      => array(),
			'so_sanh'      => array(),
			'phan'         => array(),
			'nhap'         => 0,
			'doi_ten'      => array(),
			'doi_the_loai' => array(),
			'ho_so_moi'    => array(),
			'bai'          => array(),
		);
		$nc  = self::ngu_canh( $bc );
		$ids = DuLieuCu::bai_ids();
		$ten = self::ten_da_co();
		foreach ( $ids as $id ) {
			$bai = DuLieuCu::bai( $id );
			if ( ! $bai ) {
				continue;
			}
			++$bc['tong'];
			$bc['trang_thai'][ $bai['trang_thai'] ] = ( $bc['trang_thai'][ $bai['trang_thai'] ] ?? 0 ) + 1;
			$kh = self::ke_hoach( $bai, $nc, $ten );
			++$bc['cach'][ $kh['cach'] ];
			if ( 'khong_mang' === $kh['cach'] ) {
				continue;
			}
			foreach ( $kh['can_xem'] as $l ) {
				$bc['can_xem'][ self::nhom( $l ) ][] = $id;
			}
			foreach ( $kh['ghi_chu'] as $l ) {
				$bc['ghi_chu'][ self::nhom( $l ) ][] = $id;
			}
			foreach ( $kh['so_sanh'] as $p => $kq ) {
				$bc['so_sanh'][ $p ][ $kq ] = ( $bc['so_sanh'][ $p ][ $kq ] ?? 0 ) + 1;
			}
			foreach ( array_keys( $kh['phan'] ) as $p ) {
				$bc['phan'][ $p ] = ( $bc['phan'][ $p ] ?? 0 ) + 1;
			}
			$bc['nhap'] += $kh['nhap'] ? 1 : 0;
			if ( null !== $kh['ten_moi'] ) {
				$bc['doi_ten'][] = array( $id, $bai['ten'], $kh['ten_moi'] );
			}
			if ( $kh['doi_the_loai'] ) {
				$bc['doi_the_loai'][] = $id;
			}
			if ( $giu_bai ) {
				$bc['bai'][ $id ] = $kh;
			}
		}
		$pl              = NhapPhanLoai::ke_hoach();
		$bc['phan_loai'] = array(
			'muc_tu'      => count( $pl['muc_tu'] ),
			'tro_choi'    => count( $pl['tro_choi'] ),
			'mo_tip'      => count( $pl['mo_tip'] ),
			'tinh_huong'  => count( $pl['tinh_huong'] ),
			'muc'         => array_sum( array_map( static fn( $t ) => count( $t['bai'] ), $pl['tinh_huong'] ) ),
			'ban_co_biet' => count( $pl['ban_co_biet'] ),
			'wikidata'    => count( $pl['wikidata'] ),
		);
		foreach ( $pl['ghi_chu'] as $l ) {
			$bc['ghi_chu'][ self::nhom( $l ) ][] = 0;
		}
		return $bc;
	}

	/**
	 * Kế hoạch nhập một bài: cách nhập, các phần, việc kèm theo, tên đường dẫn mới.
	 *
	 * @param array<string,bool> $ten tên đường dẫn đã có (thêm tên mới vào khi cấp)
	 */
	public static function ke_hoach( array $bai, array $nc, array &$ten ): array {
		$chua_sua = $bai['bam_khop'] && '' === trim( $bai['tom_tat'] );
		if ( 'pending' === $bai['trang_thai'] && $chua_sua ) {
			// Chờ duyệt do máy tạo, chưa ai đụng: câu trong kho thành câu mới trong Hộp việc (docs/29 mục 2, "Không mang")
			return array( 'cach' => 'khong_mang' );
		}
		$kh = DungPhan::dung( $bai, $nc );
		if ( 'pending' === $bai['trang_thai'] ) {
			$kh['can_xem'][] = 'bài chờ duyệt đã có người sửa: nhập thành bản nháp';
		}
		$kh['cach']         = $bai['bam_khop'] ? 'kho_chua_sua' : ( $bai['kho'] ? 'sua_tay' : 'khong_kho' );
		$kh['trang_thai']   = 'pending' === $bai['trang_thai'] ? 'draft' : $bai['trang_thai'];
		$kh['doi_the_loai'] = array_values( array_diff( $bai['the_loai'], $kh['the_loai'] ) ) !== array();
		$kh['ten_moi']      = DungPhan::ten_moi( $bai['ten'], isset( $kh['phan']['loi'] ) ? implode( "\n", $kh['phan']['loi']['dong'] ) : null, static fn( string $t ): bool => isset( $ten[ $t ] ) );
		if ( null !== $kh['ten_moi'] ) {
			$ten[ $kh['ten_moi'] ] = true;
		} elseif ( preg_match( '/-\d+$/', $bai['ten'] ) ) {
			$kh['can_xem'][] = 'tên đường dẫn có đuôi số, chưa tìm được tên mới';
		}
		return $kh;
	}

	/** Hồ sơ nguồn theo mã nguồn tự động, và bộ tìm / cấp hồ sơ cho nguồn người điền (chạy thử: không tạo). */
	public static function ngu_canh( array &$bc ): array {
		global $wpdb;
		$nguon = array();
		$ten   = array();
		foreach ( (array) get_option( 'cadao_nguon_tu_dong', array() ) as $ma => $n ) {
			$ma           = (string) ( $n['id'] ?? $ma );
			$hs           = (int) ( $n['ho_so'] ?? 0 ) ?: (int) ( HoSo::theo_khoa( $ma )?->ID ?? 0 );
			$nguon[ $ma ] = $hs ?: self::ID_TAM + count( $bc['ho_so_moi'] );
			if ( ! $hs ) {
				$bc['ho_so_moi'][] = array( 'nguồn tự động', $ma );
			}
			// Tên nguồn người điền trùng tên nguồn tự động (vd. "Ca dao Mẹ") → dùng hồ sơ có sẵn, không tạo hồ sơ thứ hai
			foreach ( array( $n['ten'] ?? '', $n['dong_ghi_nguon'] ?? '' ) as $t ) {
				if ( '' !== trim( (string) $t ) ) {
					$ten[ mb_strtolower( trim( (string) $t ) ) ] = $ma;
				}
			}
		}
		// Mục từ theo khoá MVP (từ + lời giải): nối chú thích với mục từ của nó
		$muc_tu = array();
		foreach ( $wpdb->get_results( "SELECT m.term_id, m.meta_value FROM {$wpdb->termmeta} m JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = m.term_id WHERE m.meta_key = '_cadao_khoa' AND tt.taxonomy IN ('tu_kho', 'muc_tu')" ) as $r ) {
			$muc_tu[ (string) $r->meta_value ] = (int) $r->term_id;
		}
		$nc = array(
			'ho_so_nguon' => $nguon,
			'ho_so_ten'   => $ten,
			'muc_tu'      => static fn( string $tu, string $loi_giai ): int => $muc_tu[ md5( mb_strtolower( BoDau::nfc( trim( $tu ) ) ) . '|' . BoDau::nfc( trim( $loi_giai ) ) ) ] ?? 0,
		);
		$da          = array();
		$nguon_nc    = $nc;
		$nc['ho_so'] = static function ( string $loai, string $ten, string $url ) use ( &$bc, &$da, $nguon_nc ): int {
			$khoa = self::khoa_ho_so( $loai, $ten );
			if ( ! isset( $da[ $khoa ] ) ) {
				$co = self::hs_theo_ten( $nguon_nc, $ten ) ?: (int) ( HoSo::theo_khoa( $khoa )?->ID ?? 0 );
				if ( $co ) {
					$da[ $khoa ] = $co;
				} else {
					$da[ $khoa ]       = self::ID_TAM + count( $bc['ho_so_moi'] );
					$bc['ho_so_moi'][] = array( $loai, $ten );
				}
			}
			return $da[ $khoa ];
		};
		return $nc;
	}

	/** Hồ sơ của nguồn tự động có tên này (0 = không có). */
	public static function hs_theo_ten( array $nc, string $ten ): int {
		$ma = $nc['ho_so_ten'][ mb_strtolower( trim( $ten ) ) ] ?? null;
		return null === $ma ? 0 : (int) ( $nc['ho_so_nguon'][ $ma ] ?? 0 );
	}

	/** Khoá (tên đường dẫn) của hồ sơ nguồn người điền: loại + tên. */
	public static function khoa_ho_so( string $loai, string $ten ): string {
		return substr( $loai . '-' . sanitize_title( $ten ), 0, 190 );
	}

	/** Tên đường dẫn đang dùng của bài MVP và tác phẩm V2 (tránh trùng khi đặt tên mới). */
	public static function ten_da_co(): array {
		global $wpdb;
		return array_fill_keys( $wpdb->get_col( $wpdb->prepare( "SELECT post_name FROM {$wpdb->posts} WHERE post_type IN (%s, 'tac_pham') AND post_name <> ''", DuLieuCu::LOAI_BAI ) ), true );
	}

	/** Lý do để gộp nhóm: bỏ phần chữ trích trong «…». */
	public static function nhom( string $ly_do ): string {
		return (string) preg_replace( '/«[^»]*»/u', '«…»', $ly_do );
	}

	/** Báo cáo thành các dòng chữ (lệnh WP-CLI và tệp chạy thử). */
	public static function in( array $bc ): array {
		$ds   = static fn( array $ids ): string => implode( ', ', array_slice( $ids, 0, 15 ) ) . ( count( $ids ) > 15 ? ' … (+' . ( count( $ids ) - 15 ) . ')' : '' );
		$ra   = array( '== Nhập thử (không ghi gì) ==' );
		$ra[] = "Bài MVP: {$bc['tong']} (" . implode( ', ', array_map( static fn( $k, $v ) => "$k $v", array_keys( $bc['trang_thai'] ), $bc['trang_thai'] ) ) . ')';
		$ra[] = "Cách nhập: {$bc['cach']['kho_chua_sua']} từ kho, chưa sửa tay · {$bc['cach']['sua_tay']} đã sửa tay · {$bc['cach']['khong_kho']} không có câu trong kho · {$bc['cach']['khong_mang']} chờ duyệt do máy tạo (không mang; câu trong kho thành câu mới)";
		$ra[] = 'Phần sẽ nhập: ' . implode( ', ', array_map( static fn( $k, $v ) => "$k $v", array_keys( $bc['phan'] ), $bc['phan'] ) );
		$ra[] = "Nháp ý nghĩa thành việc chờ: {$bc['nhap']}";
		$ra[] = '-- Trước = sau --';
		foreach ( $bc['so_sanh'] as $p => $kq ) {
			$ra[] = sprintf( '  %-10s khớp %d · chỉ khác khoảng trắng %d · lệch %d', $p, $kq['khop'] ?? 0, $kq['chuan_hoa'] ?? 0, $kq['lech'] ?? 0 );
		}
		$tong_cx = count( array_unique( array_merge( array(), ...array_values( $bc['can_xem'] ) ) ) );
		$ra[]    = "-- Cần xem: {$tong_cx} bài --";
		uasort( $bc['can_xem'], static fn( $a, $b ) => count( $b ) <=> count( $a ) );
		foreach ( $bc['can_xem'] as $l => $ids ) {
			$ra[] = '  ' . count( $ids ) . " × $l: " . $ds( $ids );
		}
		if ( $bc['ghi_chu'] ) {
			$ra[] = '-- Ghi chú (không cần xem) --';
			foreach ( $bc['ghi_chu'] as $l => $ids ) {
				$ra[] = '  ' . count( $ids ) . " × $l: " . $ds( $ids );
			}
		}
		$ra[] = '-- Đường dẫn sẽ đổi: ' . count( $bc['doi_ten'] ) . ' tên (đuôi số), ' . count( $bc['doi_the_loai'] ) . ' bài đổi thể loại cũ --';
		foreach ( array_slice( $bc['doi_ten'], 0, 30 ) as [ $id, $cu, $moi ] ) {
			$ra[] = "  $id: $cu → $moi";
		}
		$ra[] = '-- Hồ sơ nguồn sẽ tạo: ' . count( $bc['ho_so_moi'] ) . ' --';
		foreach ( $bc['ho_so_moi'] as [ $loai, $ten ] ) {
			$ra[] = "  $loai: $ten";
		}
		$pl   = $bc['phan_loai'];
		$ra[] = "-- Sau các bài: {$pl['muc_tu']} mục từ (giữ ID), {$pl['tro_choi']} trò chơi có cách chơi, {$pl['mo_tip']} mô-típ, {$pl['tinh_huong']} tình huống → tuyển tập ({$pl['muc']} mục), {$pl['ban_co_biet']} câu \"Bạn có biết\" đã duyệt, {$pl['wikidata']} quyết định Wikidata --";
		return $ra;
	}
}
