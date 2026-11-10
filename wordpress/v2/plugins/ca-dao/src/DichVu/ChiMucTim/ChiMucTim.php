<?php

namespace Phongdao\DichVu\ChiMucTim;

use Phongdao\HaTang\KhaiBao;
use Phongdao\HaTang\Module;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\ChiMuc;

defined( 'ABSPATH' ) || exit;

/**
 * Chỉ mục tìm `wp_cadao_tim` (giữ cấu trúc bảng MVP; docs/26 mục 8) dựng từ các phần đã lưu của tác phẩm đã đăng.
 * Ghi lại dòng của bài khi lưu một phần, đổi tên hoặc trạng thái; dựng lại toàn bộ theo lô (WP-Cron, lệnh
 * `wp phongdao tim --dung-lai`). Từ vựng âm tiết cho gợi ý gõ sai giữ trong một tuỳ chọn không tự nạp.
 */
final class ChiMucTim implements Module {

	/** Phần của bài có trong chỉ mục: chính (tên + lời + dị bản), phụ (nghĩa, chú thích). */
	private const PHAN = array( 'loi', 'di_ban', 'y_nghia', 'nghia_den', 'nghia_bong', 'dung_khi', 'chu_thich' );

	public const TU_VUNG = 'phongdao_tim_am_tiet';

	/** Con trỏ dựng lại theo lô: ID bài cuối đã làm; -1 = xong. */
	public const SAU = 'phongdao_tim_sau';

	public const VIEC_NEN = 'phongdao_tim_dung_lai';

	public function dang_ky(): void {
		add_action(
			'phongdao_da_luu_phan',
			static function ( int $bai, string $phan ): void {
				if ( in_array( $phan, self::PHAN, true ) ) {
					self::ghi( $bai );
				}
			},
			20,
			2
		);
		add_action( 'save_post_' . KhaiBao::TAC_PHAM, static fn( int $id ) => self::ghi( $id ), 20 );
		add_action(
			'transition_post_status',
			static function ( string $moi, string $cu, \WP_Post $bai ): void {
				if ( KhaiBao::TAC_PHAM === $bai->post_type && $moi !== $cu ) {
					self::ghi( $bai->ID );
				}
			},
			10,
			3
		);
		add_action( 'deleted_post', static fn( int $id ) => self::xoa( $id ) );
		add_action( self::VIEC_NEN, array( self::class, 'dung_lai_lo' ) );
		foreach ( array( 'created_muc_tu', 'edited_muc_tu', 'delete_muc_tu', 'set_object_terms' ) as $viec ) {
			add_action( $viec, static fn() => delete_transient( 'phongdao_tim_muc_tu' ) );
		}
	}

	public static function bang(): string {
		global $wpdb;
		return $wpdb->prefix . 'cadao_tim';
	}

	public static function sql_tao( string $bang, string $bo_ma ): string {
		return "CREATE TABLE $bang (
		post_id bigint(20) unsigned NOT NULL,
		chinh longtext NOT NULL,
		chinh_kd longtext NOT NULL,
		phu longtext NOT NULL,
		phu_kd longtext NOT NULL,
		PRIMARY KEY  (post_id)
		) $bo_ma;";
	}

	/** Đánh chỉ mục một bài; bài không phải tác phẩm đã đăng thì bỏ khỏi chỉ mục. */
	public static function ghi( int $id ): void {
		global $wpdb;
		$bai = get_post( $id );
		if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'publish' !== $bai->post_status ) {
			self::xoa( $id );
			return;
		}
		$phan = PhanBai::tat_ca( $bai->ID, $bai->post_type );
		$dong = array_merge( $phan['loi']['dong'] ?? array(), ...array_map( static fn( $d ) => $d['dong'] ?? array(), $phan['di_ban']['muc'] ?? array() ) );
		$hang = ChiMuc::dong_chi_muc(
			$bai->post_title . "\n" . implode( "\n", $dong ),
			implode(
				"\n",
				array_merge(
					array_map( static fn( string $p ): string => (string) ( $phan[ $p ]['chu'] ?? '' ), array( 'y_nghia', 'nghia_den', 'nghia_bong', 'dung_khi' ) ),
					array_map( static fn( $c ): string => ( $c['tu'] ?? '' ) . ' ' . ( $c['loi_giai'] ?? '' ), $phan['chu_thich']['muc'] ?? array() )
				)
			)
		);
		if ( ! $hang ) {
			self::xoa( $id );
			return;
		}
		$wpdb->replace( self::bang(), array( 'post_id' => $id ) + $hang );
		self::them_tu_vung( trim( $hang['chinh_kd'] ) );
	}

	public static function xoa( int $id ): void {
		global $wpdb;
		$wpdb->delete( self::bang(), array( 'post_id' => $id ), array( '%d' ) );
	}

	/** Bắt đầu dựng lại toàn bộ (nâng cấp CSDL, lệnh): xoá từ vựng, đặt con trỏ, hẹn việc nền. */
	public static function bat_dau_dung_lai(): void {
		update_option( self::TU_VUNG, array(), false );
		update_option( self::SAU, 0, false );
		if ( ! wp_next_scheduled( self::VIEC_NEN ) ) {
			wp_schedule_single_event( time() + 60, self::VIEC_NEN );
		}
	}

	/** Một lô dựng lại (500 bài); còn bài thì hẹn lô sau. @return int số bài đã làm */
	public static function dung_lai_lo( int $so = 500 ): int {
		global $wpdb;
		$sau = (int) get_option( self::SAU, -1 );
		if ( $sau < 0 ) {
			return 0;
		}
		$ds = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND ID > %d ORDER BY ID LIMIT %d", KhaiBao::TAC_PHAM, $sau, $so ) );
		update_meta_cache( 'post', array_map( 'intval', $ds ) );
		foreach ( $ds as $id ) {
			self::ghi( (int) $id );
		}
		update_option( self::SAU, count( $ds ) === $so ? (int) end( $ds ) : -1, false );
		if ( count( $ds ) === $so ) {
			wp_schedule_single_event( time() + 60, self::VIEC_NEN );
		}
		return count( $ds );
	}

	/**
	 * Tìm bài đã đăng, xếp trên MỌI bài có đủ các chữ (T-181; trước đây chỉ 500 bài đầu bảng chưa sắp nên bỏ sót bài
	 * mới và đếm tối đa 500). Câu 1 lấy cờ khớp do CSDL tính (không lấy chữ); chỉ bài còn phải xét chữ gần nhau mà còn
	 * có thể lọt vào $so bài đầu mới được đọc chữ, từng lô (ChiMuc::xep_co).
	 *
	 * @return array{ds:array<int,array{id:int,diem:int,roi:bool,lien:bool}>,tong:int,doc:int} doc = số bài đã đọc chữ
	 */
	public static function tim( string $q, int $so = 20 ): array {
		global $wpdb;
		$phep = ChiMuc::phep_so( $q );
		if ( ! $phep ) {
			return array( 'ds' => array(), 'tong' => 0, 'doc' => 0 );
		}
		$cot  = implode( ', ', array_map( static fn( string $ten, array $p ): string => self::sql_co( $p ) . " AS $ten", array_keys( $phep ), $phep ) );
		$hang = $wpdb->get_results( "SELECT post_id, LENGTH(chinh_kd) AS dai, $cot FROM " . self::bang() . ' WHERE ' . self::sql_dk( $phep ), ARRAY_A ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- từng mẫu đã qua prepare()
		return ChiMuc::xep_co(
			$hang,
			$q,
			$so,
			static function ( array $ids ) use ( $wpdb ): array {
				$ra = array();
				foreach ( $wpdb->get_results( 'SELECT post_id, chinh_kd, phu_kd FROM ' . self::bang() . ' WHERE post_id IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')', ARRAY_A ) ?: array() as $r ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- chỉ số nguyên
					$ra[ (int) $r['post_id'] ] = $r;
				}
				return $ra;
			}
		);
	}

	/** Số bài đã đăng có đủ các chữ (gợi ý gõ sai chỉ cần số này, không cần xếp). */
	public static function dem( string $q ): int {
		global $wpdb;
		$phep = ChiMuc::phep_so( $q );
		return $phep ? (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::bang() . ' WHERE ' . self::sql_dk( $phep ) ) : 0; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- từng mẫu đã qua prepare()
	}

	/** Điều kiện ứng viên: mọi chữ tìm (không dấu, trọn âm tiết, chữ cuối gõ dở) có ở phần chính hoặc phụ. */
	private static function sql_dk( array $phep ): string {
		global $wpdb;
		return implode( ' AND ', array_map( static fn( string $t ): string => $wpdb->prepare( '(chinh_kd LIKE BINARY %s OR phu_kd LIKE BINARY %s)', '%' . $wpdb->esc_like( $t ) . '%', '%' . $wpdb->esc_like( $t ) . '%' ), $phep['hc'][1] ) );
	}

	/** Một cờ của ChiMuc::phep_so() thành biểu thức SQL 0/1 (chạy trên MySQL/MariaDB và SQLite của Playground). */
	private static function sql_co( array $p ): string {
		global $wpdb;
		[ $cot, $ds, $dau ] = $p;
		return '(' . implode( ' AND ', array_map( static fn( string $s ): string => $wpdb->prepare( "$cot LIKE BINARY %s", ( $dau ? '' : '%' ) . $wpdb->esc_like( $s ) . '%' ), $ds ) ) . ')';
	}

	/**
	 * Không có kết quả: thử thay một âm tiết bằng âm tiết gần nó một chữ cái (trong từ vựng của chỉ mục).
	 *
	 * @return string|null truy vấn gợi ý (không dấu) cho nhiều kết quả nhất
	 */
	public static function goi_y( string $q ): ?string {
		$tu = explode( ' ', ChiMuc::khong_dau( ChiMuc::chu( $q ) ) );
		if ( array( '' ) === $tu || count( $tu ) > 8 ) {
			return null;
		}
		$tu_vung = get_option( self::TU_VUNG, array() );
		$tu_vung = is_array( $tu_vung ) ? $tu_vung : array();
		$tot     = null;
		$nhieu   = 0;
		foreach ( $tu as $i => $t ) {
			foreach ( ChiMuc::tu_gan( $t, $tu_vung ) as $thay ) {
				$thu      = $tu;
				$thu[ $i ] = $thay;
				$moi       = implode( ' ', $thu );
				$dem       = self::dem( $moi );
				if ( $dem > $nhieu ) {
					$tot   = $moi;
					$nhieu = $dem;
				}
			}
		}
		return $tot;
	}

	private static function them_tu_vung( string $chinh_kd ): void {
		$tu_vung = get_option( self::TU_VUNG, array() );
		$tu_vung = is_array( $tu_vung ) ? $tu_vung : array();
		$doi     = false;
		foreach ( array_unique( explode( ' ', $chinh_kd ) ) as $am ) {
			if ( '' !== $am && ! is_numeric( $am ) && strlen( $am ) <= 12 ) {
				$tu_vung[ $am ] = ( $tu_vung[ $am ] ?? 0 ) + 1;
				$doi            = true;
			}
		}
		if ( $doi ) {
			update_option( self::TU_VUNG, $tu_vung, false );
		}
	}
}
