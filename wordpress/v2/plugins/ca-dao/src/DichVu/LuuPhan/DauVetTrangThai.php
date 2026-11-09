<?php

namespace Phongdao\DichVu\LuuPhan;

use Phongdao\HaTang\KhaiBao;
use Phongdao\HaTang\Module;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Dấu vết đổi trạng thái tác phẩm (T-170): mọi lần trạng thái đổi — kể cả từ màn có sẵn của WordPress (Sửa nhanh, Thùng
 * rác, Khôi phục, Xoá vĩnh viễn) — ghi một dòng nhật ký "trạng thái" như nút Đăng / Ẩn của Phongdao: ai, lúc nào, trước,
 * sau. Nút Đăng / Ẩn tự ghi dòng của nó nên đặt cờ bỏ qua quanh lần ghi; bước nhập dữ liệu cũ không ghi (có nhật ký riêng).
 * Bài mới tạo (từ "new" / "auto-draft") không ghi: lần tạo đã có dòng nhật ký của từng phần.
 */
final class DauVetTrangThai implements Module {

	/** Đặt true quanh lần đổi trạng thái đã tự ghi nhật ký (BienTap\Module::trang_thai). */
	public static bool $bo_qua = false;

	private const QUYET = array(
		'publish' => 'dang',
		'private' => 'an',
		'draft'   => 'nhap',
		'pending' => 'cho',
		'future'  => 'hen',
		'trash'   => 'thung_rac',
	);

	public function dang_ky(): void {
		add_action( 'transition_post_status', array( self::class, 'doi' ), 5, 3 );
		add_action( 'before_delete_post', array( self::class, 'xoa' ), 10, 2 );
	}

	public static function doi( string $moi, string $cu, \WP_Post $bai ): void {
		if ( self::$bo_qua || KhaiBao::TAC_PHAM !== $bai->post_type || $moi === $cu || in_array( $cu, array( 'new', 'auto-draft' ), true ) || 'auto-draft' === $moi || self::dang_nhap() ) {
			return;
		}
		self::ghi( $bai->ID, 'trash' === $cu ? 'ra_thung_rac' : ( self::QUYET[ $moi ] ?? $moi ), array( 'trang_thai' => $cu ), array( 'trang_thai' => $moi ) );
	}

	/** Xoá vĩnh viễn: bài mất hẳn, dòng nhật ký giữ tên và trạng thái cuối. */
	public static function xoa( int $id, ?\WP_Post $bai = null ): void {
		$bai = $bai ?? get_post( $id );
		if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || self::dang_nhap() ) {
			return;
		}
		self::ghi( (int) $bai->ID, 'xoa_vinh_vien', array( 'trang_thai' => $bai->post_status, 'ten' => $bai->post_title ), null );
	}

	private static function dang_nhap(): bool {
		return 'dang' === get_option( \Phongdao\DichVu\NhapDuLieuCu\NhapLam::TRANG_THAI );
	}

	private static function ghi( int $id, string $quyet, array $truoc, ?array $sau ): void {
		Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => 'post',
				'doi_tuong_id'   => $id,
				'phan'           => 'trang_thai',
				'nguoi_tao'      => (string) get_current_user_id(),
				'quyet'          => $quyet,
				'truoc'          => $truoc,
				'sau'            => $sau,
				'nguoi_quyet'    => get_current_user_id(),
				'quyet_luc'      => gmdate( 'Y-m-d H:i:s' ),
			)
		);
	}
}
