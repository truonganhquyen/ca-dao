<?php

namespace Phongdao\KhoLuu;

use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\HoSoNguon;

defined( 'ABSPATH' ) || exit;

/**
 * Hồ sơ nguồn lưu ở loại bài ẩn `ho_so_nguon`: tên = tiêu đề, khoá = tên đường dẫn, các trường ở meta `_phongdao_ho_so`.
 */
final class HoSo {

	public const META = '_phongdao_ho_so';

	public static function theo_khoa( string $khoa ): ?\WP_Post {
		$bai = get_page_by_path( $khoa, OBJECT, KhaiBao::HO_SO_NGUON );
		return $bai instanceof \WP_Post ? $bai : null;
	}

	/** @return array<string,mixed> trường của hồ sơ (thiếu = chưa rõ) */
	public static function truong( int $id ): array {
		$v = get_post_meta( $id, self::META, true );
		return is_array( $v ) ? $v : array();
	}

	/** Tạo hồ sơ (đã có khoá này thì giữ nguyên, trả ID cũ). */
	public static function tao( string $khoa, string $ten, array $truong ): int {
		$co = self::theo_khoa( $khoa );
		if ( $co ) {
			return $co->ID;
		}
		$id = wp_insert_post(
			wp_slash(
				array(
					'post_type'   => KhaiBao::HO_SO_NGUON,
					'post_status' => 'publish',
					'post_name'   => $khoa,
					'post_title'  => $ten,
				)
			)
		);
		if ( $id ) {
			update_post_meta( $id, self::META, wp_slash( HoSoNguon::chuan( $truong ) ) );
		}
		return (int) $id;
	}
}
