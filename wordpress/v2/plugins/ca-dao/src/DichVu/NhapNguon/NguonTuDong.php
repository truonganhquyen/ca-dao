<?php

namespace Phongdao\DichVu\NhapNguon;

use Phongdao\HaTang\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Nguồn tự động: cấu hình, lịch chạy (chuyển từ MVP inc/nguon-tu-dong.php). Giữ nguyên tên tuỳ chọn
 * `cadao_nguon_tu_dong` và tên lịch `cadao_thu_thap` / `cadao_tao_bai_hang_ngay` của MVP: sau ngày chuyển, cấu hình,
 * điểm dừng của bộ đọc và lịch đã hẹn chạy tiếp, không phải đọc lại nguồn từ đầu (docs/29).
 * Mỗi 5 phút: mỗi nguồn đang bật chạy bộ đọc một lượt ngắn. Hằng ngày: chọn câu mới vào Hộp việc (DichVu\UngVien).
 */
final class NguonTuDong implements Module {

	public const TUY_CHON = 'cadao_nguon_tu_dong';

	public const LICH_LAY  = 'cadao_thu_thap';
	public const LICH_NGAY = 'cadao_tao_bai_hang_ngay';

	/** Lượt chạy trong yêu cầu web (WP-Cron); cron thật của máy chủ qua WP-CLI được lâu hơn. */
	public const GIAY_WEB = 20;
	public const GIAY_CLI = 120;

	public const BO_DOC = array(
		'cadao_me' => array(
			'ten'  => 'Ca dao Mẹ (API + sitemap + trang)',
			'chay' => array( CadaoMe::class, 'chay' ),
		),
	);

	public function dang_ky(): void {
		add_filter(
			'cron_schedules',
			static function ( array $lich ): array {
				$lich['cadao_5_phut'] = array(
					'interval' => 5 * MINUTE_IN_SECONDS,
					'display'  => 'Mỗi 5 phút (Phongdao)',
				);
				return $lich;
			}
		);
		add_action(
			self::LICH_LAY,
			static function (): void {
				if ( \Phongdao\DichVu\May\DieuKhien::dang_dung( 'doc_nguon' ) ) {
					return; // người quản trị tạm dừng ở bảng điều khiển máy
				}
				foreach ( self::tat_ca() as $id => $n ) {
					if ( ! empty( $n['bat'] ) ) {
						self::chay( (string) $id );
					}
				}
			}
		);
		add_action( 'init', array( self::class, 'hen_lich' ), 20 );
	}

	public static function mac_dinh(): array {
		return array(
			'id'              => '',
			'ten'             => '',
			'kieu'            => 'cadao_me',
			'url'             => '',
			'bat'             => false,
			'loai_lay'        => array_keys( CadaoMe::LOAI ),
			'so_bai_moi_ngay' => 10,
			'dong_ghi_nguon'  => '',
			'chep_chu_thich'  => true,  // D-055: chép chú thích, lời giải được phép; tắt được bất cứ lúc nào
			'ghi_cong'        => false, // D-057, D-068: dòng "Nguồn: …" dưới chú thích chép — mặc định tắt
			'ho_so'           => 0,     // hồ sơ nguồn (loại bài ho_so_nguon) gắn với nguồn này
			'bo_qua_co'       => array( 'tho_tuc' ),
			'tu_tho_tuc'      => array( 'đụ', 'địt', 'lồn', 'buồi', 'cặc', 'đéo' ),
			'nguoi_duyet'     => 0,
			'trang_thai_doc'  => array(),
			'nhat_ky'         => array(),
		);
	}

	public static function tat_ca(): array {
		$ds = get_option( self::TUY_CHON, array() );
		return is_array( $ds ) ? $ds : array();
	}

	/** Tên hiển thị của một nguồn tự động; chưa cấu hình thì giữ mã. */
	/** Tên nguồn tự động để hiện cho người biên soạn; nguồn chưa có tên thì "nguồn chưa đặt tên", không in mã máy. */
	public static function ten( string $id ): string {
		$ten = (string) ( self::tat_ca()[ $id ]['ten'] ?? '' );
		return '' !== $ten ? $ten : 'nguồn chưa đặt tên';
	}

	public static function lay( string $id ): ?array {
		$ds = self::tat_ca();
		return isset( $ds[ $id ] ) ? $ds[ $id ] + self::mac_dinh() : null;
	}

	public static function luu( array $nguon ): void {
		$ds                 = self::tat_ca();
		$ds[ $nguon['id'] ] = $nguon;
		update_option( self::TUY_CHON, $ds, false );
	}

	/** Chưa có nguồn nào: thêm sẵn Ca dao Mẹ ở trạng thái TẮT — người quản trị tự bật. */
	public static function tao_mac_dinh( int $ho_so ): void {
		if ( self::tat_ca() ) {
			return;
		}
		self::luu(
			array(
				'id'             => 'cadao-me',
				'ten'            => 'Ca dao Mẹ',
				'url'            => 'https://cadao.me',
				'dong_ghi_nguon' => 'Ca dao Mẹ',
				'ho_so'          => $ho_so,
			) + self::mac_dinh()
		);
	}

	/** Ghi một dòng nhật ký cho nguồn (giữ 30 dòng gần nhất). */
	public static function ghi( array &$nguon, string $viec, array $kq ): void {
		array_unshift( $nguon['nhat_ky'], array( 'luc' => current_time( 'mysql' ), 'viec' => $viec ) + $kq );
		$nguon['nhat_ky'] = array_slice( $nguon['nhat_ky'], 0, 30 );
	}

	/** Chạy bộ đọc của một nguồn một lượt; hai lượt không chạy chồng. */
	public static function chay( string $id, ?int $giay = null ): array {
		$giay ??= defined( 'WP_CLI' ) && WP_CLI ? self::GIAY_CLI : self::GIAY_WEB;
		$nguon  = self::lay( $id );
		$bo     = $nguon ? ( self::BO_DOC[ $nguon['kieu'] ] ?? null ) : null;
		if ( ! $nguon || ! $bo ) {
			return array( 'yeu_cau' => 0, 'moi' => 0, 'cap_nhat' => 0, 'ly_do' => 'không có nguồn hoặc bộ đọc' );
		}
		if ( get_transient( 'cadao_dang_chay_' . $id ) ) {
			return array( 'yeu_cau' => 0, 'moi' => 0, 'cap_nhat' => 0, 'ly_do' => 'lượt trước đang chạy' );
		}
		set_transient( 'cadao_dang_chay_' . $id, 1, $giay + 60 );
		try {
			$kq = call_user_func_array( $bo['chay'], array( &$nguon, microtime( true ) + $giay ) );
		} finally {
			delete_transient( 'cadao_dang_chay_' . $id );
		}
		// Chỉ ghi điểm dừng và nhật ký lên cấu hình MỚI NHẤT: người quản trị có thể đã sửa nguồn trong lúc lượt chạy
		wp_cache_delete( self::TUY_CHON, 'options' );
		wp_cache_delete( 'alloptions', 'options' );
		$moi = self::lay( $id );
		if ( $moi ) {
			$moi['trang_thai_doc'] = $nguon['trang_thai_doc'];
			self::ghi( $moi, 'lấy bài', $kq );
			self::luu( $moi );
		}
		return $kq;
	}

	/** Hẹn lịch nếu chưa có (lịch của MVP còn thì dùng tiếp). */
	public static function hen_lich(): void {
		if ( ! wp_next_scheduled( self::LICH_LAY ) ) {
			wp_schedule_event( time() + MINUTE_IN_SECONDS, 'cadao_5_phut', self::LICH_LAY );
		}
		if ( ! wp_next_scheduled( self::LICH_NGAY ) ) {
			wp_schedule_event( self::sau_6_gio(), 'daily', self::LICH_NGAY );
		}
	}

	/** 06:00 sắp tới theo giờ của site (giờ Việt Nam), không theo giờ máy chủ (UTC). */
	public static function sau_6_gio(): int {
		$bay_gio = new \DateTimeImmutable( 'now', wp_timezone() );
		$moc     = $bay_gio->setTime( 6, 0 );
		return ( $moc > $bay_gio ? $moc : $moc->modify( '+1 day' ) )->getTimestamp();
	}

	/**
	 * Lịch chọn câu hằng ngày từng hẹn 06:00 giờ máy chủ (UTC, tức 13:00 giờ Việt Nam): hẹn lại đúng 06:00 giờ site.
	 * Lịch đã đúng giờ thì để nguyên.
	 */
	public static function hen_lai_lich_ngay(): void {
		$t = wp_next_scheduled( self::LICH_NGAY );
		if ( $t && '06:00' !== wp_date( 'H:i', $t ) ) {
			wp_clear_scheduled_hook( self::LICH_NGAY );
			wp_schedule_event( self::sau_6_gio(), 'daily', self::LICH_NGAY );
		}
	}
}
