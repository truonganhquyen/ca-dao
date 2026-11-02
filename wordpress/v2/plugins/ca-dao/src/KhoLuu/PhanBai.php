<?php

namespace Phongdao\KhoLuu;

use Phongdao\MienDuLieu\Phan;

defined( 'ABSPATH' ) || exit;

/**
 * Đọc / ghi các phần của bài ở post meta `_phongdao_<phần>` (D-094). Chỉ DichVu\LuuPhan gọi ghi():
 * mọi lần ghi phải qua bộ kiểm lằn ranh và nhật ký.
 */
final class PhanBai {

	public static function lay( int $bai, string $phan ): ?array {
		$v = get_post_meta( $bai, Phan::khoa( $phan ), true );
		return is_array( $v ) && array() !== $v ? $v : null;
	}

	/** @return array<string,?array> mọi phần của bài (phần chưa có = null) */
	public static function tat_ca( int $bai, string $loai_bai ): array {
		$ra = array();
		foreach ( Phan::ds( $loai_bai ) as $phan ) {
			$ra[ $phan ] = self::lay( $bai, $phan );
		}
		return $ra;
	}

	/** Ghi (null = xoá) rồi đọc lại để chắc đã ghi đúng. */
	public static function ghi( int $bai, string $phan, ?array $gia_tri ): bool {
		$khoa = Phan::khoa( $phan );
		if ( null === $gia_tri ) {
			delete_post_meta( $bai, $khoa );
			return ! metadata_exists( 'post', $bai, $khoa );
		}
		// update_metadata() bỏ dấu \ một lần: thêm trước để lời có dấu \ hay " giữ nguyên
		update_post_meta( $bai, $khoa, wp_slash( $gia_tri ) );
		return self::lay( $bai, $phan ) === $gia_tri;
	}
}
