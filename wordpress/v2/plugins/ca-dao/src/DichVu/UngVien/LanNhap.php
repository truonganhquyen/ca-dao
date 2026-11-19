<?php

namespace Phongdao\DichVu\UngVien;

use Phongdao\KhoLuu\Viec;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/** D-122: bản xem trước và biên nhận riêng tư trong bảng việc, không là thẻ Hộp việc hay bảng mới. */
final class LanNhap {

	public const LOAI = 'nhap_tep';
	public const XEM = 'nhap_xem';
	public const DANG = 'nhap_dang';
	public const XONG = 'nhap_xong';
	public const MOI_LUOT = 25;

	public static function lay( int $id ): ?array {
		$v = Viec::lay( $id );
		return $v && self::LOAI === $v['loai'] && in_array( $v['trang_thai'], array( self::XEM, self::DANG, self::XONG ), true ) ? $v : null;
	}

	/** Cùng dữ liệu và lựa chọn nhập mở lại biên nhận cũ, kể cả đổi tên tệp. */
	public static function tao( array $muc, int $ho_so, string $ten, string $bam_tep, int $nguoi, string $nguoi_lam = NguoiLam::NGUOI_VIET, int $cha = 0 ): array {
		global $wpdb;
		if ( ! user_can( $nguoi, 'cadao_soan' ) || ! $muc || count( $muc ) > NhapTep::TOI_DA || ( $ho_so && KhaiBao::HO_SO_NGUON !== get_post_type( $ho_so ) ) || ! in_array( $nguoi_lam, array( NguoiLam::NGUOI_VIET, NguoiLam::MAY_CHEP ), true ) ) {
			return self::sai( 'Không có quyền hoặc tập mục nhập chưa hợp lệ.' );
		}
		$bam = hash( 'sha256', wp_json_encode( array( 1, $ho_so, $nguoi_lam, $bam_tep, $muc, $cha ) ) );
		$ds = $wpdb->get_col( $wpdb->prepare( 'SELECT id FROM ' . Viec::bang() . ' WHERE loai = %s AND phan = %s ORDER BY id DESC LIMIT 10', self::LOAI, substr( $bam, 0, 32 ) ) );
		foreach ( $ds ?: array() as $id ) {
			$v = self::lay( (int) $id );
			if ( $v && $bam === ( $v['noi_dung']['bam'] ?? '' ) ) {
				return array( 'ok' => true, 'lan' => (int) $id, 'da_co' => true, 'loi' => '' );
			}
		}
		$id = Viec::tao( array( 'loai' => self::LOAI, 'trang_thai' => self::XEM, 'doi_tuong_loai' => self::LOAI, 'phan' => substr( $bam, 0, 32 ), 'nguoi_tao' => (string) $nguoi, 'noi_dung' => array( 'ban' => 1, 'bam' => $bam, 'bam_tep' => $bam_tep, 'ten' => mb_substr( sanitize_file_name( $ten ), 0, 190 ) ?: 'Danh sách đã dán', 'ho_so' => $ho_so, 'nguoi_lam' => $nguoi_lam, 'muc' => array_values( $muc ), 'bo' => array(), 'cha' => $cha ), 'sau' => array( 'ket_qua' => array() ) ) );
		return $id ? array( 'ok' => true, 'lan' => $id, 'da_co' => false, 'loi' => '' ) : self::sai( 'Chưa lưu được bản xem trước. Tệp chưa vào Kho.' );
	}

	/** Ghi lựa chọn hoặc nhập tiếp một lượt: khoá biên nhận và chốt cả dữ liệu/kết quả trong một giao dịch. */
	public static function ap_dung( int $id, int $phien, int $nguoi, ?array $giu = null, array $loai = array(), string $loai_chung = '', bool $chi_luu = false ): array {
		global $wpdb;
		if ( ! user_can( $nguoi, 'cadao_soan' ) || false === $wpdb->query( 'START TRANSACTION' ) ) {
			return self::sai( 'Không có quyền hoặc chưa mở được lượt nhập.' );
		}
		try {
			$v = self::lay( $id );
			if ( ! $v || $phien !== (int) $v['phien'] || self::XONG === $v['trang_thai'] ) {
				throw new \RuntimeException( 'Lần nhập đã đổi hoặc đã xong. Tải lại để xem kết quả; không nhập thêm.' );
			}
			if ( 1 !== $wpdb->query( $wpdb->prepare( 'UPDATE ' . Viec::bang() . ' SET phien = phien + 1 WHERE id = %d AND loai = %s AND phien = %d', $id, self::LOAI, $phien ) ) ) {
				throw new \RuntimeException( 'Lần nhập đang được xử lý ở tab khác. Tải lại để xem tiến độ.' );
			}
			$nd = $v['noi_dung'];
			$kq = (array) ( $v['sau']['ket_qua'] ?? array() );
			if ( self::XEM === $v['trang_thai'] && null !== $giu ) {
				$nd['bo'] = array_values( array_diff( array_keys( $nd['muc'] ), array_map( 'intval', $giu ) ) );
				foreach ( $nd['muc'] as $i => &$m ) {
					$tl = '' !== $loai_chung ? $loai_chung : (string) ( $loai[ $i ] ?? $m['loai'] );
					$m['loai'] = isset( TheLoai::TU_LOAI_GOC[ $tl ] ) ? $tl : '';
				}
				unset( $m );
			}
			$nhac = '';
			if ( ! $chi_luu && self::XEM === $v['trang_thai'] ) {
				foreach ( $nd['muc'] as $i => $m ) {
					if ( in_array( $i, $nd['bo'], true ) ) { continue; }
					if ( '' === trim( (string) $m['loi'] ) || ! isset( TheLoai::TU_LOAI_GOC[ $m['loai'] ] ) ) {
						$nhac = 'Mục ' . ( $i + 1 ) . ': cần có lời và chọn thể loại. Lựa chọn được giữ lại; chưa nhập mục nào.';
						$chi_luu = true;
						break;
					}
				}
			}
			$n = 0;
			if ( ! $chi_luu ) {
				foreach ( $nd['muc'] as $i => $m ) {
					if ( isset( $kq[ $i ] ) ) { continue; }
					if ( $n >= self::MOI_LUOT ) { break; }
					$kq[ $i ] = in_array( $i, $nd['bo'], true ) ? array( 'tt' => 'bo_qua', 'id' => 0, 'chu' => 'Người bỏ chọn trước khi nhập.' ) : NhapTep::nhap_muc( $m, (int) $nd['ho_so'], $nguoi, (string) $nd['bam_tep'], (string) $nd['nguoi_lam'], true );
					++$n;
				}
			}
			$con = count( $nd['muc'] ) - count( $kq );
			$tt = $chi_luu ? self::XEM : ( $con ? self::DANG : self::XONG );
			$so = $wpdb->update( Viec::bang(), array( 'phien' => $phien + 2, 'trang_thai' => $tt, 'noi_dung' => wp_json_encode( $nd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), 'sau' => wp_json_encode( array( 'ket_qua' => $kq ), JSON_UNESCAPED_UNICODE ), 'nguoi_quyet' => $nguoi, 'quyet_luc' => gmdate( 'Y-m-d H:i:s' ) ), array( 'id' => $id, 'loai' => self::LOAI, 'phien' => $phien + 1 ) );
			if ( 1 !== $so || false === $wpdb->query( 'COMMIT' ) ) {
				throw new \RuntimeException( 'Chưa chốt được kết quả lượt nhập; lượt này được huỷ. Tải lại rồi tiếp tục.' );
			}
			return array( 'ok' => true, 'lan' => $id, 'con' => $con, 'loi' => '', 'nhac' => $nhac );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			return self::sai( $e instanceof \RuntimeException ? $e->getMessage() : 'Chưa nhập được; tải lại để xem rồi tiếp tục.' );
		}
	}

	/** Tạo bản xem trước con chỉ gồm mục lỗi; giữ biên nhận gốc và đối soát lại khi áp dụng. */
	public static function thu_lai( int $id, int $phien, int $nguoi ): array {
		$v = self::lay( $id );
		if ( ! $v || (int) $v['phien'] !== $phien || self::XONG !== $v['trang_thai'] || ! user_can( $nguoi, 'cadao_soan' ) ) {
			return self::sai( 'Lần nhập đã đổi hoặc chưa xử lý xong. Tải lại để xem.' );
		}
		$nd = $v['noi_dung'];
		$muc = array();
		foreach ( (array) ( $v['sau']['ket_qua'] ?? array() ) as $i => $k ) {
			if ( 'loi' === $k['tt'] ) { $muc[] = $nd['muc'][ $i ]; }
		}
		return self::tao( $muc, (int) $nd['ho_so'], (string) $nd['ten'], (string) $nd['bam_tep'], $nguoi, (string) $nd['nguoi_lam'], $id );
	}

	public static function ids( int $id ): array {
		$v = self::lay( $id );
		return $v ? array_values( array_unique( array_filter( array_map( 'intval', array_column( (array) ( $v['sau']['ket_qua'] ?? array() ), 'id' ) ) ) ) ) : array();
	}

	public static function gan_day(): array {
		global $wpdb;
		$ids = $wpdb->get_col( $wpdb->prepare( 'SELECT id FROM ' . Viec::bang() . ' WHERE loai = %s ORDER BY id DESC LIMIT 5', self::LOAI ) );
		return array_values( array_filter( array_map( static fn( $id ) => self::lay( (int) $id ), $ids ?: array() ) ) );
	}

	public static function dem( array $v ): array {
		$dem = array_fill_keys( array( 'moi', 'da_co', 'da_gop', 'nghi_trung', 'loi', 'xung_dot', 'bo_qua' ), 0 );
		foreach ( (array) ( $v['sau']['ket_qua'] ?? array() ) as $k ) {
			if ( isset( $dem[ $k['tt'] ] ) ) { ++$dem[ $k['tt'] ]; }
		}
		return $dem;
	}

	private static function sai( string $loi ): array {
		return array( 'ok' => false, 'lan' => 0, 'loi' => $loi );
	}
}
