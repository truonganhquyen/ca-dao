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
	/**
	 * Hồ sơ nguồn người thêm bằng tay (Nguồn & cài đặt, tấm Đối chiếu sách — D-104 ý 5, 6): tên và các trường người gõ, không
	 * thêm giá trị mặc định nào. Người chỉ có quyền Biên tập chỉ tạo hồ sơ **sách** với tên, người biên soạn, nhà xuất bản,
	 * năm; giấy phép, xin phép, chính sách hiện để trống ("chưa rõ") cho Quản trị điền. Ghi nhật ký người tạo.
	 *
	 * @param bool $quan_tri người tạo có quyền nguồn (được điền mọi trường)
	 * @return array{ok:bool,id:int,loi:string}
	 */
	public static function them( string $ten, array $truong, bool $quan_tri, int $nguoi ): array {
		$ten = trim( sanitize_text_field( $ten ) );
		if ( '' === $ten ) {
			return array( 'ok' => false, 'id' => 0, 'loi' => 'Cần tên hồ sơ (tên sách, trang web, người kể…).' );
		}
		$truong = $quan_tri
			? array_intersect_key( $truong, array_flip( HoSoNguon::TRUONG ) )
			: array( 'loai' => 'sach' ) + array_intersect_key( $truong, array_flip( HoSoNguon::TRUONG_SACH ) );
		$truong = array_map( static fn( $v ) => is_bool( $v ) ? $v : sanitize_text_field( (string) $v ), $truong );
		$id     = wp_insert_post( wp_slash( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'post_title' => $ten, 'post_author' => $nguoi ) ), true );
		if ( is_wp_error( $id ) || ! $id ) {
			return array( 'ok' => false, 'id' => 0, 'loi' => 'Chưa tạo được hồ sơ.' );
		}
		update_post_meta( (int) $id, self::META, wp_slash( HoSoNguon::chuan( $truong ) ) );
		Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => 'ho_so',
				'doi_tuong_id'   => (int) $id,
				'phan'           => 'ho_so',
				'nguoi_tao'      => (string) $nguoi,
				'quyet'          => 'them',
				'truoc'          => array(),
				'sau'            => array( 'ten' => $ten ) + HoSoNguon::chuan( $truong ),
				'nguoi_quyet'    => $nguoi,
				'quyet_luc'      => gmdate( 'Y-m-d H:i:s' ),
			)
		);
		return array( 'ok' => true, 'id' => (int) $id, 'loi' => '' );
	}

	/** @return array<int,string> hồ sơ loại sách (chọn khi đối chiếu) */
	public static function sach(): array {
		$ra = array();
		foreach ( get_posts( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'numberposts' => 500, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
			if ( 'sach' === ( self::truong( $p->ID )['loai'] ?? '' ) ) {
				$ra[ (int) $p->ID ] = $p->post_title;
			}
		}
		return $ra;
	}

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
