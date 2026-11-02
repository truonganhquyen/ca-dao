<?php

namespace Phongdao\DichVu\LuuPhan;

use Phongdao\HaTang\KhaiBao;
use Phongdao\HaTang\Module;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\TheTho;

defined( 'ABSPATH' ) || exit;

/**
 * Sau khi lưu lời của tác phẩm: máy gắn thể thơ (đếm âm tiết, MienDuLieu\TheTho) vào phân loại hinh_thuc để lọc
 * danh sách và trình bày (docs/26 mục 4.2). Chỉ thay mục thể thơ; lối hát người đã gán giữ nguyên.
 */
final class GanHinhThuc implements Module {

	public function dang_ky(): void {
		add_action( 'phongdao_da_luu_phan', array( self::class, 'gan' ), 10, 2 );
	}

	public static function gan( int $bai_id, string $phan ): void {
		if ( 'loi' !== $phan || KhaiBao::TAC_PHAM !== get_post_type( $bai_id ) ) {
			return;
		}
		$giu = array();
		foreach ( wp_get_object_terms( $bai_id, 'hinh_thuc' ) ?: array() as $t ) {
			if ( $t instanceof \WP_Term && ! isset( TheTho::DS[ $t->slug ] ) ) {
				$giu[] = $t->term_id;
			}
		}
		$dong = PhanBai::lay( $bai_id, 'loi' )['dong'] ?? array();
		if ( $dong ) {
			$the = TheTho::nhan( $dong );
			$co  = term_exists( $the, 'hinh_thuc' );
			$co  = $co ? $co : wp_insert_term( TheTho::DS[ $the ], 'hinh_thuc', array( 'slug' => $the ) );
			if ( is_array( $co ) ) {
				$giu[] = (int) $co['term_id'];
			}
		}
		wp_set_object_terms( $bai_id, $giu, 'hinh_thuc' );
	}
}
