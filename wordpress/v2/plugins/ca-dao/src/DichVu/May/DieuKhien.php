<?php

namespace Phongdao\DichVu\May;

use Phongdao\DichVu\GoiY\GoiY;
use Phongdao\DichVu\NhapNguon\NguonTuDong;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Bảng điều khiển máy (docs/31 mục 1.3; T-179): người có quyền nguồn xem lịch, lần chạy, lỗi gần nhất, hạn mức của
 * từng việc nền và tạm dừng / chạy lại từng việc — mỗi lần bấm ghi nhật ký (ai, lúc nào, lý do). Tạm dừng chỉ làm việc
 * nền bỏ qua lượt chạy theo lịch: không gỡ bài, không xoá kho, không đổi cài đặt nguồn. Lệnh WP-CLI người vận hành gõ tay
 * vẫn chạy.
 */
final class DieuKhien {

	public const TUY_CHON = 'phongdao_may_tam_dung';

	/** Việc nền tạm dừng được => [tên, giải thích, móc lịch]. */
	public const VIEC = array(
		'doc_nguon' => array( 'Đọc nguồn tự động', 'Mỗi 5 phút: đọc các nguồn đang bật, lưu câu mới vào kho (không đăng gì).', NguonTuDong::LICH_LAY ),
		'chon_cau'  => array( 'Chọn câu mới hằng ngày', '06:00 mỗi ngày: chọn câu từ kho thành thẻ Câu mới trong Hộp việc, theo số câu mỗi ngày của từng nguồn.', NguonTuDong::LICH_NGAY ),
		'goi_y'     => array( 'Gợi ý chủ đề, vùng theo lô', 'Chạy theo lô khi cần tính lại: tạo thẻ gợi ý cho bài đã đăng.', GoiY::VIEC_NEN ),
	);

	public static function dang_dung( string $viec ): bool {
		return in_array( $viec, (array) get_option( self::TUY_CHON, array() ), true );
	}

	/**
	 * Tạm dừng hoặc chạy lại một việc nền; ghi nhật ký.
	 *
	 * @return array{ok:bool,chu:string}
	 */
	public static function dat( string $viec, bool $dung, int $nguoi, string $ly_do = '' ): array {
		if ( ! isset( self::VIEC[ $viec ] ) ) {
			return array( 'ok' => false, 'chu' => 'Không có việc nền này.' );
		}
		$cu = self::dang_dung( $viec );
		if ( $cu === $dung ) {
			return array( 'ok' => true, 'chu' => $dung ? 'Việc này đang tạm dừng sẵn.' : 'Việc này đang chạy theo lịch.' );
		}
		$ds = array_values( array_diff( (array) get_option( self::TUY_CHON, array() ), array( $viec ) ) );
		if ( $dung ) {
			$ds[] = $viec;
		}
		update_option( self::TUY_CHON, $ds, false );
		Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => 'may',
				'doi_tuong_id'   => 0,
				'phan'           => $viec,
				'noi_dung'       => '' === $ly_do ? null : array( 'ly_do' => mb_substr( $ly_do, 0, 300 ) ),
				'nguoi_tao'      => (string) $nguoi,
				'quyet'          => $dung ? 'tam_dung' : 'chay_lai',
				'truoc'          => array( 'tam_dung' => $cu ),
				'sau'            => array( 'tam_dung' => $dung ),
				'nguoi_quyet'    => $nguoi,
				'quyet_luc'      => gmdate( 'Y-m-d H:i:s' ),
			)
		);
		// Gợi ý theo lô đang dở: chạy lại thì hẹn lô kế tiếp (lúc tạm dừng lô không tự hẹn tiếp)
		if ( ! $dung && 'goi_y' === $viec && (int) get_option( GoiY::SAU, -1 ) >= 0 && ! wp_next_scheduled( GoiY::VIEC_NEN ) ) {
			wp_schedule_single_event( time() + 60, GoiY::VIEC_NEN );
		}
		return array( 'ok' => true, 'chu' => ( $dung ? 'Đã tạm dừng: ' : 'Đã cho chạy lại: ' ) . self::VIEC[ $viec ][0] . '.' );
	}

	/**
	 * Trạng thái từng việc nền cho bảng điều khiển: tạm dừng, lần chạy tới, lần chạy gần nhất, lỗi gần nhất, hạn mức.
	 *
	 * @return array<string,array{ten:string,giai:string,tam_dung:bool,lan_toi:int,lan_cuoi:string,loi:string,han_muc:string}>
	 */
	public static function trang_thai(): array {
		$nguon = array();
		foreach ( array_keys( NguonTuDong::tat_ca() ) as $id ) {
			$n = NguonTuDong::lay( (string) $id );
			if ( $n ) {
				$nguon[] = $n;
			}
		}
		$tim = static function ( string $tu ) use ( $nguon ): array {
			$cuoi = '';
			$loi  = '';
			foreach ( $nguon as $n ) {
				foreach ( (array) $n['nhat_ky'] as $d ) {
					if ( ( $d['viec'] ?? '' ) !== $tu ) {
						continue;
					}
					$luc = (string) ( $d['luc'] ?? '' );
					if ( $luc > $cuoi ) {
						$cuoi = $luc;
					}
					if ( '' !== (string) ( $d['loi'] ?? '' ) && '' === $loi ) {
						$loi = $luc . ' · ' . ( $n['ten'] ?: $n['id'] ) . ': ' . (string) $d['loi'];
					}
				}
			}
			return array( $cuoi, $loi );
		};
		$ra = array();
		foreach ( self::VIEC as $ma => [ $ten, $giai, $moc ] ) {
			[ $cuoi, $loi ] = 'doc_nguon' === $ma ? $tim( 'lấy bài' ) : ( 'chon_cau' === $ma ? $tim( 'chọn câu mới' ) : array( '', '' ) );
			$han = '';
			if ( 'chon_cau' === $ma ) {
				$han = implode( ' · ', array_map( static fn( array $n ): string => ( $n['ten'] ?: $n['id'] ) . ': ' . ( $n['bat'] ? (int) $n['so_bai_moi_ngay'] . ' câu/ngày' : 'nguồn tắt' ), $nguon ) );
			} elseif ( 'doc_nguon' === $ma ) {
				$han = implode( ' · ', array_map( static fn( array $n ): string => ( $n['ten'] ?: $n['id'] ) . ': ' . ( $n['bat'] ? 'bật' : 'tắt' ), $nguon ) );
			} else {
				$sau = (int) get_option( GoiY::SAU, -1 );
				$han = $sau < 0 ? 'Không có lô đang dở.' : 'Đang dở, sau bài #' . $sau . '.';
			}
			$ra[ $ma ] = array(
				'ten'      => $ten,
				'giai'     => $giai,
				'tam_dung' => self::dang_dung( $ma ),
				'lan_toi'  => (int) wp_next_scheduled( $moc ),
				'lan_cuoi' => $cuoi,
				'loi'      => $loi,
				'han_muc'  => $han,
			);
		}
		return $ra;
	}
}
