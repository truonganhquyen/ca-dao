<?php

namespace Phongdao\DichVu\TomTat;

use Phongdao\DichVu\DungTrang\DocBai;
use Phongdao\HaTang\KhaiBao;
use Phongdao\HaTang\Module;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\CapChatLuong;

defined( 'ABSPATH' ) || exit;

/**
 * Bảng tóm tắt bài `wp_cadao_tom_tat` (docs/32 mục 3.9): mỗi tác phẩm một dòng tính sẵn — thể loại, trạng thái, cấp chất
 * lượng, còn thiếu gì, cờ, nguồn, lượt đọc 7 ngày. Danh sách Tác phẩm, bộ lọc, số đếm, Hôm nay, Bổ sung đọc từ đây thay
 * vì đọc post meta từng bài. Dòng được ghi lại trong cùng lượt lưu một phần, đổi trạng thái, đổi thể loại; dựng lại toàn
 * bộ theo lô (bước nâng cấp CSDL, lệnh `wp phongdao tom-tat --dung-lai`). Chỉ là bản tính sẵn để đọc nhanh: nguồn sự
 * thật vẫn là các phần trong bài; bảng hỏng thì dựng lại, không mất gì.
 */
final class TomTat implements Module {

	/** Mã "còn thiếu" => chữ hiện cho người biên soạn. Câu đố không nằm trong "Thiếu ý nghĩa" (D-105). */
	public const THIEU = array(
		'loi'        => 'Lời',
		'y_nghia'    => 'Ý nghĩa',
		'nghia_tn'   => 'Nghĩa đen / nghĩa bóng',
		'dap_an'     => 'Đáp án',
		'giai_thich' => 'Giải thích đáp án',
		'tu_kho'     => 'Từ khó',
		'doi_chieu'  => 'Đối chiếu sách',
	);

	/** Con trỏ dựng lại theo lô: ID bài cuối đã làm; -1 = xong. */
	public const SAU = 'phongdao_tom_tat_sau';

	public const VIEC_NEN = 'phongdao_tom_tat_dung_lai';

	/** Việc nền hằng ngày: tính lại lượt đọc 7 ngày của mọi dòng. */
	public const VIEC_DOC = 'phongdao_tom_tat_luot_doc';

	public function dang_ky(): void {
		// Mọi đường ghi một phần (tấm sửa, trang sửa, lô, nhập, đăng từ Kho) đi qua post meta `_phongdao_…`
		foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $moc ) {
			add_action(
				$moc,
				static function ( $meta_id, $id, $khoa ): void {
					if ( is_string( $khoa ) && str_starts_with( $khoa, \Phongdao\MienDuLieu\Phan::TIEN_TO ) && KhaiBao::TAC_PHAM === get_post_type( (int) $id ) ) {
						self::ghi_sau( (int) $id );
					}
				},
				20,
				3
			);
		}
		add_action(
			'transition_post_status',
			static function ( string $moi, string $cu, \WP_Post $bai ): void {
				if ( KhaiBao::TAC_PHAM === $bai->post_type && $moi !== $cu ) {
					self::ghi_sau( $bai->ID );
				}
			},
			20,
			3
		);
		add_action(
			'set_object_terms',
			static function ( $id, $terms, $tt_ids, $tax ): void {
				if ( KhaiBao::THE_LOAI === $tax && KhaiBao::TAC_PHAM === get_post_type( (int) $id ) ) {
					self::ghi_sau( (int) $id );
				}
			},
			20,
			4
		);
		add_action(
			'deleted_post',
			static function ( int $id ): void {
				unset( self::$cho[ $id ] );
				self::xoa( $id );
			}
		);
		add_action( 'shutdown', array( self::class, 'xong' ), 1 );
		add_action( self::VIEC_NEN, array( self::class, 'dung_lai_lo' ) );
		add_action( self::VIEC_DOC, array( self::class, 'cap_nhat_luot_doc' ) );
	}

	public static function bang(): string {
		global $wpdb;
		return $wpdb->prefix . 'cadao_tom_tat';
	}

	public static function sql_tao( string $bang, string $bo_ma ): string {
		return "CREATE TABLE $bang (
		post_id bigint(20) unsigned NOT NULL,
		the_loai varchar(40) NOT NULL DEFAULT '',
		trang_thai varchar(20) NOT NULL DEFAULT '',
		cap tinyint(4) NOT NULL DEFAULT -1,
		thieu varchar(191) NOT NULL DEFAULT '',
		co tinyint(1) NOT NULL DEFAULT 0,
		nguon_may varchar(64) NOT NULL DEFAULT '',
		nguon_nguoi tinyint(1) NOT NULL DEFAULT 0,
		doc_7 int(11) NOT NULL DEFAULT 0,
		sua_luc datetime NOT NULL,
		PRIMARY KEY  (post_id),
		KEY trang_thai (trang_thai),
		KEY the_loai (the_loai),
		KEY doc_7 (doc_7)
		) $bo_ma;";
	}

	/**
	 * Dòng tóm tắt của một tác phẩm, tính từ các phần đã lưu (không ghi). null khi không phải tác phẩm.
	 *
	 * @return array{post_id:int,the_loai:string,trang_thai:string,cap:int,thieu:string,co:int,nguon_may:string,nguon_nguoi:int,doc_7:int,sua_luc:string}|null
	 */
	public static function tinh( int $id ): ?array {
		$bai = get_post( $id );
		if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'auto-draft' === $bai->post_status ) {
			return null;
		}
		$phan  = PhanBai::tat_ca( $id, KhaiBao::TAC_PHAM );
		$loai  = (string) DocBai::the_loai( $bai );
		$co    = static fn( string $p ): bool => ! empty( $phan[ $p ] );
		$thieu = array();
		if ( empty( $phan['loi']['dong'] ) ) {
			$thieu[] = 'loi';
		}
		if ( 'cau-do' === $loai ) {
			if ( ! $co( 'dap_an' ) ) {
				$thieu[] = 'dap_an';
			}
			if ( ! $co( 'y_nghia' ) ) {
				$thieu[] = 'giai_thich';
			}
		} elseif ( 'tuc-ngu' === $loai ) {
			if ( ! $co( 'nghia_den' ) || ! $co( 'nghia_bong' ) ) {
				$thieu[] = 'nghia_tn';
			}
			if ( ! $co( 'y_nghia' ) && ! $co( 'nghia_bong' ) ) {
				$thieu[] = 'y_nghia';
			}
		} elseif ( ! $co( 'y_nghia' ) ) {
			$thieu[] = 'y_nghia';
		}
		if ( ! $co( 'chu_thich' ) ) {
			$thieu[] = 'tu_kho';
		}
		if ( ! empty( $phan['loi']['dong'] ) && empty( $phan['loi']['doi_chieu'] ) ) {
			$thieu[] = 'doi_chieu';
		}
		return array(
			'post_id'     => $id,
			'the_loai'    => $loai,
			'trang_thai'  => (string) $bai->post_status,
			'cap'         => CapChatLuong::tinh( $phan, '' === $loai ? null : $loai ) ?? -1,
			'thieu'       => $thieu ? ',' . implode( ',', $thieu ) . ',' : '',
			'co'          => $co( 'co_nghi' ) ? 1 : 0,
			'nguon_may'   => substr( (string) ( $phan['nguon_may']['nguon_id'] ?? '' ), 0, 64 ),
			'nguon_nguoi' => empty( $phan['loi']['nguon'] ) ? 0 : 1,
			'doc_7'       => self::doc_7( $id ),
			'sua_luc'     => (string) $bai->post_modified_gmt ?: gmdate( 'Y-m-d H:i:s' ),
		);
	}

	/** Lượt đọc 7 ngày của một bài (bảng lượt đọc của đo lường). */
	private static function doc_7( int $id ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT SUM(so) FROM ' . \Phongdao\DichVu\DoLuong\DoLuong::bang( 'luot_doc' ) . ' WHERE post_id = %d AND ngay >= %s', $id, wp_date( 'Y-m-d', time() - 6 * DAY_IN_SECONDS ) ) );
	}

	/** Bài đã đổi trong lượt này, chờ tính lại dòng (mỗi bài một lần dù nhiều phần đổi). @var array<int,true> */
	private static array $cho = array();

	/** Đánh dấu bài cần tính lại; dòng được ghi một lần ở cuối lượt hoặc ngay khi có ai đọc bảng (xong()). */
	public static function ghi_sau( int $id ): void {
		self::$cho[ $id ] = true;
	}

	/** Tính lại các dòng đang chờ (cuối lượt, hoặc trước khi đọc bảng để danh sách luôn đúng trong cùng lượt). */
	public static function xong(): void {
		while ( self::$cho ) {
			$id = (int) array_key_first( self::$cho );
			unset( self::$cho[ $id ] );
			self::ghi( $id );
		}
	}

	/** Ghi lại dòng của một bài (bài không còn là tác phẩm thì xoá dòng). */
	public static function ghi( int $id ): void {
		global $wpdb;
		$dong = self::tinh( $id );
		if ( null === $dong ) {
			self::xoa( $id );
			return;
		}
		$wpdb->replace( self::bang(), $dong, array( '%d', '%s', '%s', '%d', '%s', '%d', '%s', '%d', '%d', '%s' ) );
	}

	public static function xoa( int $id ): void {
		global $wpdb;
		$wpdb->delete( self::bang(), array( 'post_id' => $id ), array( '%d' ) );
	}

	/** @return array<string,mixed>|null dòng đã ghi của một bài */
	public static function lay( int $id ): ?array {
		global $wpdb;
		self::xong();
		$d = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::bang() . ' WHERE post_id = %d', $id ), ARRAY_A );
		return is_array( $d ) ? $d : null;
	}

	/** @return string[] mã "còn thiếu" của một dòng */
	public static function thieu( array $dong ): array {
		return array_values( array_filter( explode( ',', (string) ( $dong['thieu'] ?? '' ) ), 'strlen' ) );
	}

	/** Bắt đầu dựng lại toàn bộ theo lô (nâng cấp CSDL, lệnh). */
	public static function bat_dau_dung_lai(): void {
		update_option( self::SAU, 0, false );
		if ( ! wp_next_scheduled( self::VIEC_NEN ) ) {
			wp_schedule_single_event( time() + 30, self::VIEC_NEN );
		}
	}

	/** Một lô dựng lại (500 bài, mọi trạng thái); còn bài thì hẹn lô sau. @return int số bài đã làm */
	public static function dung_lai_lo( int $so = 500 ): int {
		global $wpdb;
		$sau = (int) get_option( self::SAU, -1 );
		if ( $sau < 0 ) {
			return 0;
		}
		$ds = array_map( 'intval', $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status <> 'auto-draft' AND ID > %d ORDER BY ID LIMIT %d", KhaiBao::TAC_PHAM, $sau, $so ) ) ?: array() );
		if ( $ds ) {
			_prime_post_caches( $ds, true, true );
		}
		foreach ( $ds as $id ) {
			self::ghi( $id );
		}
		update_option( self::SAU, count( $ds ) === $so ? (int) end( $ds ) : -1, false );
		if ( count( $ds ) === $so ) {
			wp_schedule_single_event( time() + 30, self::VIEC_NEN );
		}
		return count( $ds );
	}

	/** Dựng lại toàn bộ ngay (lệnh, kiểm thử): mọi lô liền nhau. @return int số bài */
	public static function dung_lai_ngay(): int {
		update_option( self::SAU, 0, false );
		$tong = 0;
		do {
			$n     = self::dung_lai_lo();
			$tong += $n;
		} while ( $n > 0 && (int) get_option( self::SAU, -1 ) >= 0 );
		wp_clear_scheduled_hook( self::VIEC_NEN );
		return $tong;
	}

	/** Tính lại lượt đọc 7 ngày cho mọi dòng (việc nền hằng ngày): đặt 0, rồi ghi số của bài có lượt đọc. */
	public static function cap_nhat_luot_doc(): void {
		global $wpdb;
		$wpdb->query( 'UPDATE ' . self::bang() . ' SET doc_7 = 0 WHERE doc_7 <> 0' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- tên bảng của plugin
		$ds = $wpdb->get_results( $wpdb->prepare( 'SELECT post_id, SUM(so) AS so FROM ' . \Phongdao\DichVu\DoLuong\DoLuong::bang( 'luot_doc' ) . ' WHERE ngay >= %s GROUP BY post_id', wp_date( 'Y-m-d', time() - 6 * DAY_IN_SECONDS ) ) ) ?: array();
		foreach ( $ds as $r ) {
			$wpdb->update( self::bang(), array( 'doc_7' => (int) $r->so ), array( 'post_id' => (int) $r->post_id ), array( '%d' ), array( '%d' ) );
		}
	}

	/** Hẹn việc nền lượt đọc hằng ngày (bước nâng cấp CSDL). */
	public static function hen_lich(): void {
		if ( ! wp_next_scheduled( self::VIEC_DOC ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::VIEC_DOC );
		}
	}
}
