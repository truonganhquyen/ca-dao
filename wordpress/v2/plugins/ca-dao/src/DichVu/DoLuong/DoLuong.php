<?php

namespace Phongdao\DichVu\DoLuong;

use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\DiaChiMang;

defined( 'ABSPATH' ) || exit;

/**
 * Lượt đọc, trang giới thiệu, sự kiện — ba bảng giữ nguyên cấu trúc MVP (wp_cadao_luot_doc, wp_cadao_gioi_thieu,
 * wp_cadao_su_kien) nên số liệu trước ngày chuyển nối liền. Chỉ cộng số theo ngày; chặn một máy gửi lặp bằng dấu băm
 * tạm (địa chỉ mạng + khoá + ngày, hết hạn sau một ngày) — không lưu địa chỉ.
 */
final class DoLuong {

	public static function bang( string $ten ): string {
		global $wpdb;
		return $wpdb->prefix . 'cadao_' . $ten;
	}

	/** @return string[] câu lệnh dbDelta (HaTang\CaiDat bước 8) */
	public static function sql_tao( string $bo_ma ): array {
		return array(
			'CREATE TABLE ' . self::bang( 'luot_doc' ) . " (
			post_id bigint(20) unsigned NOT NULL,
			ngay date NOT NULL,
			so int(10) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (post_id,ngay),
			KEY ngay (ngay)
			) $bo_ma;",
			'CREATE TABLE ' . self::bang( 'gioi_thieu' ) . " (
			ngay date NOT NULL,
			ten_mien varchar(190) NOT NULL,
			so int(10) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (ngay,ten_mien)
			) $bo_ma;",
			'CREATE TABLE ' . self::bang( 'su_kien' ) . " (
			ngay date NOT NULL,
			loai varchar(30) NOT NULL,
			khoa varchar(190) NOT NULL,
			so int(10) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (ngay,loai,khoa),
			KEY loai (loai,ngay)
			) $bo_ma;",
		);
	}

	/** Lần đầu trong ngày của máy này với khoá này? */
	public static function lan_dau_trong_ngay( string $khoa ): bool {
		$ip = DiaChiMang::cua( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ), (string) ( $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '' ) );
		$t  = 'phongdao_1n_' . substr( wp_hash( $ip . '|' . $khoa . '|' . wp_date( 'Y-m-d' ) ), 0, 24 );
		if ( get_transient( $t ) ) {
			return false;
		}
		set_transient( $t, 1, DAY_IN_SECONDS );
		return true;
	}

	/** Cộng một (bản ghi theo ngày; câu lệnh chạy được cả MySQL và SQLite). */
	private static function cong( string $bang, array $khoa ): void {
		global $wpdb;
		$dk = implode( ' AND ', array_map( static fn( $k ) => "$k = %s", array_keys( $khoa ) ) );
		$co = $wpdb->query( $wpdb->prepare( "UPDATE $bang SET so = so + 1 WHERE $dk", ...array_values( $khoa ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL -- tên cột trong mã
		if ( ! $co ) {
			$wpdb->insert( $bang, $khoa + array( 'so' => 1 ) );
		}
	}

	public static function cong_luot_doc( int $id, string $tu ): void {
		$ngay = wp_date( 'Y-m-d' );
		self::cong( self::bang( 'luot_doc' ), array( 'post_id' => (string) $id, 'ngay' => $ngay ) );
		self::cong( self::bang( 'gioi_thieu' ), array( 'ngay' => $ngay, 'ten_mien' => '' !== $tu ? $tu : '(vào thẳng)' ) );
	}

	public static function ghi_su_kien( string $loai, string $khoa ): void {
		self::cong( self::bang( 'su_kien' ), array( 'ngay' => wp_date( 'Y-m-d' ), 'loai' => $loai, 'khoa' => $khoa ) );
	}

	private static function tu_ngay( int $so_ngay ): string {
		return wp_date( 'Y-m-d', time() - ( $so_ngay - 1 ) * DAY_IN_SECONDS );
	}

	/** Số dưới tiêu đề bài: tổng lượt đọc, chia sẻ, trích dẫn (Ve::so_lieu chỉ hiện khi đủ ngưỡng). */
	public static function so_lieu( int $id ): array {
		global $wpdb;
		$sk = static fn( string $loai ): int => (int) $wpdb->get_var( $wpdb->prepare( 'SELECT SUM(so) FROM ' . self::bang( 'su_kien' ) . ' WHERE loai = %s AND khoa LIKE %s', $loai, $id . ':%' ) );
		return array(
			'doc'   => (int) $wpdb->get_var( $wpdb->prepare( 'SELECT SUM(so) FROM ' . self::bang( 'luot_doc' ) . ' WHERE post_id = %d', $id ) ),
			'chia'  => $sk( 'chia_se' ),
			'trich' => $sk( 'trich_dan_sao_chep' ),
		);
	}

	/** Tác phẩm đọc nhiều trong N ngày (đang đăng). @return array<int,int> id => lượt */
	public static function doc_nhieu( int $so_ngay = 7, int $so = 5 ): array {
		global $wpdb;
		$ds = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT l.post_id, SUM(l.so) AS so FROM ' . self::bang( 'luot_doc' ) . " l JOIN {$wpdb->posts} p ON p.ID = l.post_id WHERE l.ngay >= %s AND p.post_status = 'publish' AND p.post_type = %s GROUP BY l.post_id ORDER BY so DESC, l.post_id ASC LIMIT %d",
				self::tu_ngay( $so_ngay ),
				KhaiBao::TAC_PHAM,
				$so
			)
		) ?: array();
		$ra = array();
		foreach ( $ds as $r ) {
			$ra[ (int) $r->post_id ] = (int) $r->so;
		}
		return $ra;
	}

	/** @return array<string,int> ngày => tổng lượt, đủ N ngày */
	public static function theo_ngay( int $so_ngay = 7 ): array {
		global $wpdb;
		$ra = array();
		for ( $i = $so_ngay - 1; $i >= 0; $i-- ) {
			$ra[ wp_date( 'Y-m-d', time() - $i * DAY_IN_SECONDS ) ] = 0;
		}
		foreach ( $wpdb->get_results( $wpdb->prepare( 'SELECT ngay, SUM(so) AS so FROM ' . self::bang( 'luot_doc' ) . ' WHERE ngay >= %s GROUP BY ngay', self::tu_ngay( $so_ngay ) ) ) ?: array() as $r ) {
			$ra[ (string) $r->ngay ] = (int) $r->so;
		}
		return $ra;
	}

	/** Trang giới thiệu, hoặc chuỗi tìm không ra, nhiều nhất trong N ngày. @return array<string,int> */
	public static function nhieu_nhat( string $loai, int $so_ngay = 7, int $so = 5 ): array {
		global $wpdb;
		$sql = 'gioi_thieu' === $loai
			? $wpdb->prepare( 'SELECT ten_mien AS k, SUM(so) AS so FROM ' . self::bang( 'gioi_thieu' ) . ' WHERE ngay >= %s GROUP BY ten_mien ORDER BY so DESC LIMIT %d', self::tu_ngay( $so_ngay ), $so )
			: $wpdb->prepare( 'SELECT khoa AS k, SUM(so) AS so FROM ' . self::bang( 'su_kien' ) . ' WHERE loai = %s AND ngay >= %s GROUP BY khoa ORDER BY so DESC LIMIT %d', $loai, self::tu_ngay( $so_ngay ), $so );
		$ra = array();
		foreach ( $wpdb->get_results( $sql ) ?: array() as $r ) { // phpcs:ignore WordPress.DB.PreparedSQL -- đã prepare ở trên
			$ra[ (string) $r->k ] = (int) $r->so;
		}
		return $ra;
	}
}
