<?php

namespace Phongdao\DichVu\TuDien;

use Phongdao\DichVu\ChiMucTim\ChiMucTim;
use Phongdao\DichVu\LuuPhan\LuuPhanLoai;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\ChiMuc;

defined( 'ABSPATH' ) || exit;

/**
 * Gợi ý nối Từ điển (D-126). Mỗi mục từ là một nghĩa của một chữ; một chữ có thể nhiều nghĩa (ba mục "Mai"). Máy chỉ tìm
 * bài có **trọn chữ, đúng dấu** của một mục từ (không phân biệt hoa thường) mà chưa nối với nghĩa nào của chữ đó, rồi
 * hiện câu có chữ. Người chọn nghĩa cho từng câu, hoặc "Không nối" (máy nhớ, không gợi ý lại bài đó cho chữ đó). Máy không
 * tự gắn, không viết nghĩa. Lưu qua đường sửa phân loại (Lịch sử, khôi phục được).
 */
final class GoiYNoi {

	/** Chữ người đã tắt gợi ý (tuỳ chọn, không tự nạp). */
	public const TAT = 'phongdao_tu_dien_tat_goi_y';

	/** Post meta: chữ người đã chọn "Không nối" cho bài này. */
	public const KHONG = '_phongdao_khong_noi_td';

	/** Chữ có nhiều câu chưa nối hơn số này là chữ rất thông dụng: vẫn xem được, xếp riêng ở cuối. */
	public const THONG_DUNG = 300;

	/** Số câu hiện mỗi lần duyệt một chữ. */
	public const MOI_LAN = 100;

	/** Đệm tổng quan; xoá khi mục từ thêm, sửa, xoá, gắn bài (ChiMucTim) hay khi người lưu lựa chọn. */
	public const DEM = 'phongdao_goi_y_td_dem';

	/** Khoá so khớp của một chữ: thường, NFC, bỏ dấu câu, dấu thanh kiểu mới ("hoà" → "hòa"). */
	public static function khoa( string $ten ): string {
		return ChiMuc::chu( $ten );
	}

	/** @return array<string,list<\WP_Term>> mục từ gom theo chữ */
	public static function theo_chu(): array {
		$ds = get_terms( array( 'taxonomy' => 'muc_tu', 'hide_empty' => false, 'orderby' => 'name' ) );
		$ra = array();
		foreach ( is_array( $ds ) ? $ds : array() as $t ) {
			$k = self::khoa( (string) $t->name );
			if ( '' !== $k ) {
				$ra[ $k ][] = $t;
			}
		}
		return $ra;
	}

	/** @return string[] chữ đã tắt gợi ý */
	public static function da_tat(): array {
		return array_values( array_filter( array_map( 'strval', (array) get_option( self::TAT, array() ) ) ) );
	}

	public static function tat( string $khoa, bool $tat ): void {
		$ds = array_diff( self::da_tat(), array( $khoa ) );
		if ( $tat ) {
			$ds[] = $khoa;
		}
		update_option( self::TAT, array_values( $ds ), false );
		delete_transient( self::DEM );
	}

	/** @return string[] chữ người đã chọn "Không nối" cho một bài */
	public static function khong_noi( int $bai ): array {
		return array_values( array_filter( array_map( 'strval', (array) get_post_meta( $bai, self::KHONG, true ) ) ) );
	}

	/**
	 * Tổng quan mọi chữ: số nghĩa, số bài đã nối, số câu chưa nối (gợi ý). Đọc chỉ mục tìm theo lô, so trọn chữ trong tên +
	 * lời; đệm đến khi mục từ đổi hay người lưu lựa chọn (bài mới đăng có thể chậm vài giờ, màn duyệt một chữ luôn tính lại).
	 *
	 * @return array<string,array{ten:string,nghia:int,noi:int,goi_y:int}>
	 */
	public static function tong_quan(): array {
		$dem = get_transient( self::DEM );
		if ( is_array( $dem ) ) {
			return $dem;
		}
		global $wpdb;
		$chu  = self::theo_chu();
		$dai  = 1;
		foreach ( array_keys( $chu ) as $k ) {
			$dai = max( $dai, substr_count( $k, ' ' ) + 1 );
		}
		$dai  = min( $dai, 8 );
		$thay = array();
		$sau  = 0;
		do {
			$hang = $wpdb->get_results( $wpdb->prepare( 'SELECT i.post_id, i.chinh FROM ' . ChiMucTim::bang() . " i JOIN {$wpdb->posts} p ON p.ID = i.post_id WHERE i.post_id > %d AND p.post_type = %s AND p.post_status = 'publish' ORDER BY i.post_id LIMIT 2000", $sau, KhaiBao::TAC_PHAM ) ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tên bảng của plugin
			foreach ( $hang as $h ) {
				$sau = (int) $h->post_id;
				$am  = explode( ' ', trim( (string) $h->chinh ) );
				$n   = count( $am );
				for ( $i = 0; $i < $n; $i++ ) {
					$k = '';
					for ( $j = 0; $j < $dai && $i + $j < $n; $j++ ) {
						$k .= ( $j ? ' ' : '' ) . $am[ $i + $j ];
						if ( isset( $chu[ $k ] ) ) {
							$thay[ $k ][ $sau ] = true;
						}
					}
				}
			}
		} while ( count( $hang ) === 2000 );
		// Bài đã nối với một nghĩa của chữ, bài người đã chọn "Không nối": không phải gợi ý
		$noi = array();
		foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT r.object_id, tt.term_id FROM {$wpdb->term_relationships} r JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = r.term_taxonomy_id WHERE tt.taxonomy = %s", 'muc_tu' ) ) ?: array() as $r ) {
			$noi[ (int) $r->term_id ][ (int) $r->object_id ] = true;
		}
		$khong = array();
		foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s", self::KHONG ) ) ?: array() as $m ) {
			foreach ( (array) maybe_unserialize( $m->meta_value ) as $k ) {
				$khong[ (string) $k ][ (int) $m->post_id ] = true;
			}
		}
		$ds = array();
		foreach ( $chu as $k => $cac ) {
			$da = array();
			foreach ( $cac as $t ) {
				$da += $noi[ (int) $t->term_id ] ?? array();
			}
			$goi_y   = array_diff_key( $thay[ $k ] ?? array(), $da, $khong[ $k ] ?? array() );
			$ds[ $k ] = array( 'ten' => (string) $cac[0]->name, 'nghia' => count( $cac ), 'noi' => count( $da ), 'goi_y' => count( $goi_y ) );
		}
		set_transient( self::DEM, $ds, 12 * HOUR_IN_SECONDS );
		return $ds;
	}

	/**
	 * Câu chưa nối của một chữ: bài đã đăng có trọn chữ trong lời, chưa nối nghĩa nào của chữ, người chưa chọn "Không nối".
	 * Bài đọc nhiều tuần này lên trước.
	 *
	 * @return array{tong:int,ds:list<array{bai:int,tieu_de:string,the_loai:string,dong:list<string>}>} dong: HTML đã thoát, chữ tô <mark>
	 */
	public static function cau( string $khoa, int $so = self::MOI_LAN ): array {
		global $wpdb;
		$cac = self::theo_chu()[ $khoa ] ?? array();
		if ( ! $cac ) {
			return array( 'tong' => 0, 'ds' => array() );
		}
		\Phongdao\DichVu\TomTat\TomTat::xong();
		$tt  = implode( ',', array_map( static fn( \WP_Term $t ): int => (int) $t->term_taxonomy_id, $cac ) );
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				'SELECT i.post_id FROM ' . ChiMucTim::bang() . " i JOIN {$wpdb->posts} p ON p.ID = i.post_id LEFT JOIN " . \Phongdao\DichVu\TomTat\TomTat::bang() . " s ON s.post_id = i.post_id
				WHERE p.post_type = %s AND p.post_status = 'publish' AND i.chinh LIKE BINARY %s
				AND NOT EXISTS ( SELECT 1 FROM {$wpdb->term_relationships} r WHERE r.object_id = i.post_id AND r.term_taxonomy_id IN ($tt) )
				ORDER BY COALESCE(s.doc_7, 0) DESC, i.post_id ASC",
				KhaiBao::TAC_PHAM,
				'% ' . $wpdb->esc_like( $khoa ) . ' %'
			)
		) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tên bảng của plugin, số đã ép kiểu
		$ra = array();
		foreach ( array_map( 'intval', $ids ) as $bai ) {
			if ( in_array( $khoa, self::khong_noi( $bai ), true ) ) {
				continue;
			}
			if ( count( $ra ) < $so ) {
				$dong = array();
				foreach ( (array) ( PhanBai::lay( $bai, 'loi' )['dong'] ?? array() ) as $d ) {
					$to = self::to( (string) $d, $khoa );
					if ( null !== $to ) {
						$dong[] = $to;
					}
				}
				$tl   = get_the_terms( $bai, KhaiBao::THE_LOAI );
				$ra[] = array( 'bai' => $bai, 'tieu_de' => (string) get_post_field( 'post_title', $bai ), 'the_loai' => is_array( $tl ) && $tl ? (string) $tl[0]->name : '', 'dong' => $dong ?: array( esc_html( (string) get_post_field( 'post_title', $bai ) ) ) );
			} else {
				$ra[] = null;
			}
		}
		return array( 'tong' => count( $ra ), 'ds' => array_values( array_filter( $ra ) ) );
	}

	/**
	 * Một dòng lời, chữ khớp (trọn chữ, đúng dấu, không phân biệt hoa thường) được tô <mark>; null khi dòng không có chữ.
	 * Trả HTML đã thoát.
	 */
	public static function to( string $dong, string $khoa ): ?string {
		$can = '' === $khoa ? array() : explode( ' ', $khoa );
		$n   = count( $can );
		if ( ! $n || ! preg_match_all( '/[\p{L}\p{M}\p{N}]+/u', $dong, $m, PREG_OFFSET_CAPTURE ) ) {
			return null;
		}
		$am   = $m[0];
		$chu  = array_map( static fn( array $a ): string => ChiMuc::chu( $a[0] ), $am );
		$ra   = '';
		$tu   = 0;
		$thay = false;
		for ( $i = 0; $i + $n <= count( $am ); $i++ ) {
			if ( array_slice( $chu, $i, $n ) !== $can ) {
				continue;
			}
			$dau  = (int) $am[ $i ][1];
			$cuoi = (int) $am[ $i + $n - 1 ][1] + strlen( $am[ $i + $n - 1 ][0] );
			$ra  .= esc_html( substr( $dong, $tu, $dau - $tu ) ) . '<mark>' . esc_html( substr( $dong, $dau, $cuoi - $dau ) ) . '</mark>';
			$tu   = $cuoi;
			$thay = true;
			$i   += $n - 1;
		}
		return $thay ? $ra . esc_html( substr( $dong, $tu ) ) : null;
	}

	/**
	 * Lưu lựa chọn của người: bài => số mục từ (nối nghĩa đó) hoặc 0 (Không nối). Mỗi lựa chọn được kiểm lại: mục từ thuộc
	 * đúng chữ, bài đã đăng có trọn chữ, chưa nối nghĩa nào của chữ.
	 *
	 * @param array<int,int> $chon
	 * @return array{noi:int,khong:int,loi:int}
	 */
	public static function luu( string $khoa, array $chon, int $nguoi ): array {
		global $wpdb;
		$kq  = array( 'noi' => 0, 'khong' => 0, 'loi' => 0 );
		$cac = array();
		foreach ( self::theo_chu()[ $khoa ] ?? array() as $t ) {
			$cac[ (int) $t->term_id ] = $t;
		}
		if ( ! $cac ) {
			return $kq;
		}
		foreach ( $chon as $bai => $muc ) {
			$bai = (int) $bai;
			$muc = (int) $muc;
			if ( $muc && ! isset( $cac[ $muc ] ) ) {
				++$kq['loi'];
				continue;
			}
			$co = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . ChiMucTim::bang() . ' WHERE post_id = %d AND chinh LIKE BINARY %s', $bai, '% ' . $wpdb->esc_like( $khoa ) . ' %' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tên bảng của plugin
			$dang = LuuPhanLoai::dang_gan( $bai, 'muc_tu' );
			if ( ! $co || null === $dang || array_intersect( $dang, array_keys( $cac ) ) || ! current_user_can( 'edit_post', $bai ) ) {
				++$kq['loi'];
				continue;
			}
			if ( ! $muc ) {
				update_post_meta( $bai, self::KHONG, array_values( array_unique( array_merge( self::khong_noi( $bai ), array( $khoa ) ) ) ) );
				++$kq['khong'];
				continue;
			}
			$r = LuuPhanLoai::luu_muc_tu( $bai, array_merge( $dang, array( $muc ) ), $dang, $nguoi );
			$r['ok'] ? ++$kq['noi'] : ++$kq['loi'];
		}
		delete_transient( self::DEM );
		return $kq;
	}
}
