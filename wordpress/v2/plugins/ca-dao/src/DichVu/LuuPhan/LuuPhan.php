<?php

namespace Phongdao\DichVu\LuuPhan;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\BanChu;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\Phan;

defined( 'ABSPATH' ) || exit;

/**
 * Lưu một phần của bài — đường ghi duy nhất (D-094, docs/26 mục 4.3): kiểm lằn ranh (MienDuLieu\KiemPhan),
 * ghi một dòng nhật ký vào bảng việc, ghi post meta, cập nhật bản chữ. Hỏng giữa chừng thì trả lại như cũ.
 *
 * Ngữ cảnh $nc: nguoi (ID người dùng; 0 khi máy), goi (KiemPhan::GOI_*), duyet (người có quyền duyệt bấm),
 * luc (giờ UTC 'Y-m-d H:i:s', tuỳ chọn — bước nhập dữ liệu cũ giữ giờ của MVP).
 * Quyền đăng nhập, nonce kiểm ở lớp REST (docs/26 mục 5); lớp này chỉ nhận ngữ cảnh đã kiểm.
 */
final class LuuPhan {

	/**
	 * @param array{nguoi:int,goi:string,duyet?:bool,luc?:string} $nc
	 * @return array{ok:bool,loi:string[],doi:bool,viec:int}
	 */
	public static function luu( int $bai_id, string $phan, mixed $gia_tri, array $nc ): array {
		$nc  = self::nc( $nc );
		$bai = get_post( $bai_id );
		if ( ! $bai || ! Phan::co( $bai->post_type, $phan ) ) {
			return self::hong( "Không có phần “{$phan}” cho bài này." );
		}
		[ $sach, $loi ] = KiemPhan::kiem( $phan, $gia_tri, self::ngu_canh_kiem( $bai, $nc ) );
		if ( $loi ) {
			return self::hong( ...$loi );
		}
		$truoc = PhanBai::lay( $bai->ID, $phan );
		if ( KiemPhan::noi_dung( $truoc ) === $sach ) {
			return self::xong( false, 0 );
		}
		$luc = self::luc( $nc );
		if ( null !== $sach ) {
			$sach['sua_boi'] = $nc['nguoi'];
			$sach['sua_luc'] = $luc;
			if ( ! empty( $sach['ai'] ) ) {
				$sach['duyet_boi'] = $nc['nguoi'];
				$sach['duyet_luc'] = $luc;
			}
		}
		return self::ghi( $bai, $phan, $truoc, $sach, $nc, null === $sach ? 'xoa' : 'luu', $luc );
	}

	/**
	 * Đặt lại giá trị "trước" của một lần sửa (Hoàn tác 10 giây; khôi phục bản cũ từ Lịch sử).
	 * Giá trị cũ vẫn phải qua bộ kiểm với quyền của người đang bấm (vd. bản cũ có hỗ trợ AI cần quyền duyệt).
	 *
	 * @return array{ok:bool,loi:string[],doi:bool,viec:int}
	 */
	public static function khoi_phuc( int $viec_id, array $nc ): array {
		$nc   = self::nc( $nc );
		$viec = Viec::lay( $viec_id );
		if ( ! $viec || Viec::SUA !== $viec['loai'] || 'post' !== $viec['doi_tuong_loai'] ) {
			return self::hong( 'Không tìm thấy lần sửa này.' );
		}
		$bai  = get_post( $viec['doi_tuong_id'] );
		$phan = (string) $viec['phan'];
		if ( ! $bai || ! Phan::co( $bai->post_type, $phan ) ) {
			return self::hong( 'Bài của lần sửa này không còn.' );
		}
		$cu = is_array( $viec['truoc'] ) ? $viec['truoc'] : null;
		[ , $loi ] = KiemPhan::kiem( $phan, KiemPhan::noi_dung( $cu ), self::ngu_canh_kiem( $bai, $nc ) );
		if ( $loi ) {
			return self::hong( ...$loi );
		}
		$truoc = PhanBai::lay( $bai->ID, $phan );
		if ( $truoc === $cu ) {
			return self::xong( false, 0 );
		}
		return self::ghi( $bai, $phan, $truoc, $cu, $nc, 'khoi_phuc', self::luc( $nc ), $viec_id );
	}

	private static function ghi( \WP_Post $bai, string $phan, ?array $truoc, ?array $sau, array $nc, string $quyet, string $luc, int $tu_viec = 0 ): array {
		$viec = Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => 'post',
				'doi_tuong_id'   => $bai->ID,
				'phan'           => $phan,
				'noi_dung'       => $tu_viec ? array( 'tu_viec' => $tu_viec ) : null,
				'nguoi_tao'      => KiemPhan::GOI_NGUOI === $nc['goi'] ? (string) $nc['nguoi'] : $nc['goi'],
				'tao_luc'        => $luc,
				'quyet'          => $quyet,
				'truoc'          => $truoc,
				'sau'            => $sau,
				'nguoi_quyet'    => $nc['nguoi'],
				'quyet_luc'      => $luc,
			)
		);
		if ( ! $viec ) {
			return self::hong( 'Không ghi được nhật ký nên chưa lưu. Thử lại sau.' );
		}
		if ( ! PhanBai::ghi( $bai->ID, $phan, $sau ) ) {
			PhanBai::ghi( $bai->ID, $phan, $truoc );
			Viec::xoa( $viec );
			return self::hong( 'Chưa lưu được phần này. Thử lại sau.' );
		}
		if ( KhaiBao::TAC_PHAM === $bai->post_type && in_array( $phan, BanChu::PHAN, true ) ) {
			$cap_nhat = wp_update_post(
				wp_slash(
					array(
						'ID'           => $bai->ID,
						'post_content' => BanChu::tu_phan( PhanBai::tat_ca( $bai->ID, $bai->post_type ) ),
					)
				),
				true
			);
			if ( is_wp_error( $cap_nhat ) || ! $cap_nhat ) {
				PhanBai::ghi( $bai->ID, $phan, $truoc );
				Viec::xoa( $viec );
				return self::hong( 'Chưa cập nhật được bản chữ; phần bài và nhật ký chưa được xác nhận.' );
			}
		}
		/** Sau khi lưu một phần: dựng lại chỉ mục tìm, xoá đệm của bài (T-144, T-152). */
		do_action( 'phongdao_da_luu_phan', $bai->ID, $phan, $viec );
		return self::xong( true, $viec );
	}

	private static function nc( array $nc ): array {
		return array(
			'nguoi' => (int) ( $nc['nguoi'] ?? 0 ),
			'goi'   => (string) ( $nc['goi'] ?? '' ),
			'duyet' => ! empty( $nc['duyet'] ),
			'luc'   => (string) ( $nc['luc'] ?? '' ),
		);
	}

	/** Thể loại của bài (tác phẩm) để kiểm phần riêng của thể loại. */
	private static function ngu_canh_kiem( \WP_Post $bai, array $nc ): array {
		$the_loai = null;
		if ( KhaiBao::TAC_PHAM === $bai->post_type ) {
			$terms    = get_the_terms( $bai, KhaiBao::THE_LOAI );
			$the_loai = is_array( $terms ) && $terms ? $terms[0]->slug : null;
		}
		return array(
			'loai_bai' => $bai->post_type,
			'the_loai' => $the_loai,
			'goi'      => $nc['goi'],
			'duyet'    => $nc['duyet'],
		);
	}

	private static function luc( array $nc ): string {
		$luc = $nc['luc'];
		return preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $luc ) ? $luc : gmdate( 'Y-m-d H:i:s' );
	}

	private static function xong( bool $doi, int $viec ): array {
		return array(
			'ok'   => true,
			'loi'  => array(),
			'doi'  => $doi,
			'viec' => $viec,
		);
	}

	private static function hong( string ...$loi ): array {
		return array(
			'ok'   => false,
			'loi'  => $loi,
			'doi'  => false,
			'viec' => 0,
		);
	}
}
