<?php

namespace Phongdao\DichVu\XuLyLo;

use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Xử lý lô (docs/30 mục 3; T-171): một thao tác cho nhiều mục, theo hợp đồng chốt phạm vi → xem trước → áp dụng.
 *
 * - Tạo lô chốt tập mục và phiên của từng mục lúc xem trước; người bỏ chọn ngoại lệ trước khi áp dụng.
 * - Áp dụng chạy từng lượt có giới hạn, mỗi mục qua đúng dịch vụ của thao tác đơn (QuyetViec…), kiểm quyền và phiên;
 *   mục đã đổi sau lúc xem trước thì bỏ qua, báo xung đột — không ghi đè. Kết quả từng mục: thành công / bỏ qua / xung
 *   đột / lỗi. Mục đã có kết quả không làm lại, nên chạy lại hay tiếp tục sau gián đoạn không áp dụng trùng.
 * - Một lô là một dòng `wp_cadao_viec` loại "lo" (trạng thái riêng, không vào Hộp việc): mã, người, phạm vi/bộ lọc, tập
 *   mục, thời điểm, kết quả.
 *
 * Máy không tự quyết nội dung: lô chỉ chạy khi người có quyền bấm; không đăng bài, không xoá vĩnh viễn.
 */
final class Lo {

	/** Số mục mỗi lượt áp dụng. */
	public const MOI_LUOT = 50;

	/** Một lô tối đa (thu hẹp bộ lọc nếu nhiều hơn). */
	public const TOI_DA = 2000;

	/** Thao tác => [nhãn, đối tượng, quyền cần, lựa chọn của thẻ]. */
	public const THAO_TAC = array(
		'viec_de_sau'  => array( 'Để sau', 'viec', 'cadao_soan', 'de_sau' ),
		'viec_bo'      => array( 'Bỏ đề xuất', 'viec', 'cadao_soan', 'bo' ),
		'goi_y_dong_y' => array( 'Đồng ý gợi ý', 'viec', 'cadao_duyet', 'dong_y' ),
	);

	public const KET_QUA = array(
		'thanh_cong' => 'Thành công',
		'bo_qua'     => 'Bỏ qua',
		'xung_dot'   => 'Xung đột',
		'loi'        => 'Lỗi',
	);

	/** Thẻ người đọc gửi không bỏ theo lô: "Bỏ" của chúng đưa nội dung người đọc vào thùng rác — quyết từng thẻ. */
	private const KHONG_BO_THEO_LO = array( 'nguoi_doc' );

	/**
	 * Tạo lô ở bước xem trước.
	 *
	 * @param int[]                $ids     mục đã chọn (hoặc mọi mục khớp bộ lọc, đã tính sẵn)
	 * @param array<string,string> $loc     bộ lọc lúc chọn (để ghi lại phạm vi)
	 * @return array{ok:bool,lo:int,loi:string}
	 */
	public static function tao( string $thao_tac, array $ids, string $pham_vi, array $loc, int $nguoi ): array {
		if ( ! isset( self::THAO_TAC[ $thao_tac ] ) ) {
			return self::sai( 'Không có thao tác này.' );
		}
		[ $ten, , $quyen ] = self::THAO_TAC[ $thao_tac ];
		if ( ! user_can( $nguoi, $quyen ) ) {
			return self::sai( 'cadao_duyet' === $quyen ? "Cần quyền duyệt để {$ten} theo lô." : 'Bạn không có quyền làm việc này.' );
		}
		$ids = array_values( array_unique( array_filter( array_map( 'intval', $ids ), static fn( int $i ): bool => $i > 0 ) ) );
		if ( ! $ids ) {
			return self::sai( 'Chưa chọn mục nào.' );
		}
		if ( count( $ids ) > self::TOI_DA ) {
			return self::sai( 'Một lô tối đa ' . number_format( self::TOI_DA, 0, ',', '.' ) . ' mục. Thu hẹp bộ lọc rồi thử lại.' );
		}
		$muc = self::chot_viec( $ids );
		if ( ! $muc ) {
			return self::sai( 'Các thẻ đã chọn không còn ở Chờ hay Để sau.' );
		}
		$loai = array_values( array_unique( array_column( $muc, 2 ) ) );
		if ( count( $loai ) > 1 ) {
			return self::sai( 'Chọn các thẻ cùng một loại (lọc theo Loại việc trước).' );
		}
		if ( 'goi_y_dong_y' === $thao_tac && 'goi_y' !== $loai[0] ) {
			return self::sai( 'Đồng ý theo lô chỉ dùng cho thẻ "Gợi ý chủ đề, vùng".' );
		}
		$id = Viec::tao(
			array(
				'loai'           => Viec::LO,
				'trang_thai'     => Viec::LO_XEM,
				'doi_tuong_loai' => Viec::LO,
				'doi_tuong_id'   => 0,
				'phan'           => $thao_tac,
				'noi_dung'       => array(
					'thao_tac'  => $thao_tac,
					'loai_viec' => $loai[0],
					'pham_vi'   => 'loc' === $pham_vi ? 'loc' : 'chon',
					'loc'       => array_map( 'strval', $loc ),
					'muc'       => array_map( static fn( array $m ): array => array( $m[0], $m[1] ), $muc ),
					'bo'        => array(),
				),
				'sau'            => array( 'ket_qua' => array() ),
				'nguoi_tao'      => (string) $nguoi,
			)
		);
		return $id ? array( 'ok' => true, 'lo' => $id, 'loi' => '' ) : self::sai( 'Chưa tạo được lô. Thử lại sau.' );
	}

	/** Thẻ còn mở trong tập đã chọn, kèm phiên và loại lúc này. @return list<array{0:int,1:int,2:string}> */
	private static function chot_viec( array $ids ): array {
		global $wpdb;
		$ra = array();
		foreach ( array_chunk( $ids, 500 ) as $phan ) {
			$dau = implode( ',', array_fill( 0, count( $phan ), '%d' ) );
			foreach ( $wpdb->get_results( $wpdb->prepare( 'SELECT id, phien, loai FROM ' . Viec::bang() . " WHERE id IN ($dau) AND trang_thai IN ('cho','de_sau') AND loai NOT IN ('sua','lo')", $phan ), ARRAY_A ) ?: array() as $h ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$ra[ (int) $h['id'] ] = array( (int) $h['id'], (int) $h['phien'], (string) $h['loai'] );
			}
		}
		// Giữ thứ tự người đã thấy
		return array_values( array_filter( array_map( static fn( int $i ): ?array => $ra[ $i ] ?? null, $ids ) ) );
	}

	/** Lô của người này, còn ở bước xem trước / đang chạy; null kèm lý do khi không dùng được. */
	private static function lay_cua( int $lo_id, int $nguoi, array $trang_thai ): array {
		$lo = Viec::lay( $lo_id );
		if ( ! $lo || Viec::LO !== $lo['loai'] ) {
			return array( null, 'Không tìm thấy lô này.' );
		}
		if ( (string) $nguoi !== $lo['nguoi_tao'] ) {
			return array( null, 'Lô này do người khác tạo.' );
		}
		if ( ! in_array( $lo['trang_thai'], $trang_thai, true ) ) {
			return array( null, Viec::LO_XONG === $lo['trang_thai'] ? 'Lô đã chạy xong.' : ( Viec::LO_HUY === $lo['trang_thai'] ? 'Lô đã huỷ.' : 'Lô không còn ở bước này.' ) );
		}
		return array( $lo, '' );
	}

	/**
	 * Bỏ chọn ngoại lệ trên một trang xem trước: mục trên trang không đánh dấu thì bỏ khỏi lô, đánh dấu lại thì giữ.
	 *
	 * @param int[] $tren_trang mục hiện trên trang
	 * @param int[] $giu        mục còn đánh dấu
	 * @return array{ok:bool,loi:string,bo:int}
	 */
	public static function bo_chon( int $lo_id, array $tren_trang, array $giu, int $nguoi ): array {
		[ $lo, $ly_do ] = self::lay_cua( $lo_id, $nguoi, array( Viec::LO_XEM ) );
		if ( ! $lo ) {
			return array( 'ok' => false, 'loi' => $ly_do, 'bo' => 0 );
		}
		$co   = array_flip( array_map( static fn( array $m ): int => (int) $m[0], $lo['noi_dung']['muc'] ) );
		$tren = array_filter( array_map( 'intval', $tren_trang ), static fn( int $i ): bool => isset( $co[ $i ] ) );
		$giu  = array_map( 'intval', $giu );
		$bo   = array_values( array_unique( array_merge( array_diff( array_map( 'intval', (array) $lo['noi_dung']['bo'] ), $tren ), array_diff( $tren, $giu ) ) ) );
		sort( $bo, SORT_NUMERIC );
		$nd       = $lo['noi_dung'];
		$nd['bo'] = $bo;
		if ( ! Viec::ghi_lo( $lo_id, (int) $lo['phien'], Viec::LO_XEM, (array) $lo['sau'], $nd ) ) {
			return array( 'ok' => false, 'loi' => 'Lô vừa đổi ở phiên khác. Tải lại trang.', 'bo' => 0 );
		}
		return array( 'ok' => true, 'loi' => '', 'bo' => count( $bo ) );
	}

	/**
	 * Chạy tiếp một lượt (tối đa MOI_LUOT mục chưa có kết quả).
	 *
	 * @return array{ok:bool,loi:string,con:int,dem:array<string,int>}
	 */
	public static function ap_dung( int $lo_id, int $nguoi, bool $duyet ): array {
		[ $lo, $ly_do ] = self::lay_cua( $lo_id, $nguoi, array( Viec::LO_XEM, Viec::LO_DANG ) );
		if ( ! $lo ) {
			return array( 'ok' => false, 'loi' => $ly_do, 'con' => 0, 'dem' => array() );
		}
		$thao_tac = (string) $lo['noi_dung']['thao_tac'];
		if ( ! isset( self::THAO_TAC[ $thao_tac ] ) || ! user_can( $nguoi, self::THAO_TAC[ $thao_tac ][2] ) ) {
			return array( 'ok' => false, 'loi' => 'Bạn không có quyền chạy lô này.', 'con' => 0, 'dem' => array() );
		}
		if ( ! Viec::nhan_lo( $lo_id, (int) $lo['phien'] ) ) {
			return array( 'ok' => false, 'loi' => 'Lô đang chạy ở phiên khác. Tải lại trang để xem tiến độ.', 'con' => 0, 'dem' => array() );
		}
		$phien = (int) $lo['phien'] + 1;
		$kq    = (array) ( $lo['sau']['ket_qua'] ?? array() );
		$bo    = array_flip( array_map( 'intval', (array) $lo['noi_dung']['bo'] ) );
		$lam   = 0;
		foreach ( $lo['noi_dung']['muc'] as [ $id, $p ] ) {
			$id = (int) $id;
			if ( isset( $kq[ (string) $id ] ) ) {
				continue;
			}
			if ( $lam >= self::MOI_LUOT ) {
				break;
			}
			$kq[ (string) $id ] = isset( $bo[ $id ] ) ? array( 'bo_qua', 'Người bỏ khỏi lô lúc xem trước.' ) : self::mot( $thao_tac, $id, (int) $p, $nguoi, $duyet );
			++$lam;
		}
		$con = count( $lo['noi_dung']['muc'] ) - count( $kq );
		if ( ! Viec::ghi_lo( $lo_id, $phien, $con > 0 ? Viec::LO_DANG : Viec::LO_XONG, array( 'ket_qua' => $kq ) ) ) {
			return array( 'ok' => false, 'loi' => 'Chưa ghi được kết quả lượt này; các mục đã làm vẫn có nhật ký riêng. Bấm Tiếp tục để chạy lại an toàn.', 'con' => $con, 'dem' => self::dem( $kq ) );
		}
		return array( 'ok' => true, 'loi' => '', 'con' => $con, 'dem' => self::dem( $kq ) );
	}

	/** Một mục: kiểm lại phiên lúc xem trước, rồi quyết bằng đúng nghiệp vụ của thẻ. @return array{0:string,1:string} */
	private static function mot( string $thao_tac, int $id, int $phien, int $nguoi, bool $duyet ): array {
		$quyet = self::THAO_TAC[ $thao_tac ][3];
		$v     = Viec::lay( $id );
		if ( ! $v ) {
			return array( 'loi', 'Không còn thẻ này.' );
		}
		if ( ! in_array( $v['trang_thai'], array( Viec::CHO, Viec::DE_SAU ), true ) ) {
			return array( 'bo_qua', 'Thẻ đã được quyết.' );
		}
		if ( $v['phien'] !== $phien ) {
			return array( 'xung_dot', 'Thẻ đã đổi sau lúc xem trước.' );
		}
		if ( 'bo' === $quyet && in_array( $v['loai'], self::KHONG_BO_THEO_LO, true ) ) {
			return array( 'bo_qua', 'Nội dung người đọc gửi: quyết từng thẻ.' );
		}
		if ( ! isset( QuyetViec::LOAI[ $v['loai'] ][1][ $quyet ] ) ) {
			return array( 'bo_qua', 'Loại thẻ này không có lựa chọn "' . self::THAO_TAC[ $thao_tac ][0] . '".' );
		}
		if ( 'de_sau' === $quyet && Viec::DE_SAU === $v['trang_thai'] ) {
			return array( 'bo_qua', 'Thẻ đã ở Để sau.' );
		}
		$kq = QuyetViec::quyet( $id, $quyet, $nguoi, $duyet, $phien );
		if ( $kq['ok'] ) {
			return array( 'thanh_cong', (string) $kq['thong_bao'] );
		}
		$loi = implode( ' ', $kq['loi'] );
		return array( str_contains( $loi, 'phiên khác' ) ? 'xung_dot' : ( str_contains( $loi, 'đã được quyết' ) ? 'bo_qua' : 'loi' ), $loi );
	}

	/** Dừng lô: mục chưa làm giữ nguyên; mục đã làm vẫn có kết quả và nhật ký. */
	public static function huy( int $lo_id, int $nguoi ): array {
		[ $lo, $ly_do ] = self::lay_cua( $lo_id, $nguoi, array( Viec::LO_XEM, Viec::LO_DANG ) );
		if ( ! $lo ) {
			return array( 'ok' => false, 'loi' => $ly_do );
		}
		return Viec::ghi_lo( $lo_id, (int) $lo['phien'], Viec::LO_HUY, (array) $lo['sau'] )
			? array( 'ok' => true, 'loi' => '' )
			: array( 'ok' => false, 'loi' => 'Lô vừa đổi ở phiên khác. Tải lại trang.' );
	}

	/** @return array<string,int> số mục theo kết quả */
	public static function dem( array $kq ): array {
		$dem = array_fill_keys( array_keys( self::KET_QUA ), 0 );
		foreach ( $kq as $k ) {
			$ma = (string) ( $k[0] ?? '' );
			if ( isset( $dem[ $ma ] ) ) {
				++$dem[ $ma ];
			}
		}
		return $dem;
	}

	private static function sai( string $loi ): array {
		return array( 'ok' => false, 'lo' => 0, 'loi' => $loi );
	}
}
