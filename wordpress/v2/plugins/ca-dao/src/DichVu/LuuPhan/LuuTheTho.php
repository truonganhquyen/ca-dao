<?php

namespace Phongdao\DichVu\LuuPhan;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\TheTho;

defined( 'ABSPATH' ) || exit;

/** Lựa chọn thể thơ theo bài (D-109). Không sửa lời, không tự duyệt nội dung. */
final class LuuTheTho {

	public const META = '_phongdao_the_tho_nguoi';

	public static function lay( int $id ): array {
		$cu = get_post_meta( $id, self::META, true );
		return is_array( $cu ) && 'nguoi' === ( $cu['che_do'] ?? '' ) ? array( 'che_do' => 'nguoi', 'ma' => (string) ( $cu['ma'] ?? '' ) ) : array( 'che_do' => 'may', 'ma' => '' );
	}

	public static function phien( int $id ): string {
		$ids = wp_get_object_terms( $id, 'hinh_thuc', array( 'fields' => 'ids' ) );
		if ( is_wp_error( $ids ) ) {
			return '';
		}
		sort( $ids, SORT_NUMERIC );
		return hash( 'sha256', wp_json_encode( array( self::lay( $id ), $ids, Viec::moi_nhat_sua( $id, 'the_tho' ) ) ) );
	}

	public static function luu( int $id, string $che_do, string $ma, string $phien, int $nguoi ): array {
		global $wpdb;
		$hong = static fn( string $loi ): array => array( 'ok' => false, 'doi' => false, 'loi' => array( $loi ), 'viec' => 0 );
		if ( ! user_can( $nguoi, 'cadao_duyet' ) || ! user_can( $nguoi, 'edit_post', $id ) || KhaiBao::TAC_PHAM !== get_post_type( $id ) || 'trash' === get_post_status( $id ) ) {
			return $hong( 'Bạn không có quyền sửa thể thơ của bài này.' );
		}
		if ( ! in_array( $che_do, array( 'may', 'nguoi' ), true ) || ( 'nguoi' === $che_do && '' !== $ma && ! isset( TheTho::DS[ $ma ] ) ) ) {
			return $hong( 'Chọn cách dùng và thể thơ có trong danh sách.' );
		}
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
			return $hong( 'Chưa mở được lượt lưu thể thơ. Thử lại sau.' );
		}
		try {
			if ( ! LuuPhan::khoa_bai( $id ) || '' === $phien || ! hash_equals( self::phien( $id ), $phien ) ) {
				throw new \RuntimeException( 'Lựa chọn hoặc phân loại vừa thay đổi. Tải lại bài để đối chiếu trước khi lưu tiếp.' );
			}
			$cu = self::lay( $id );
			$moi = array( 'che_do' => $che_do, 'ma' => 'nguoi' === $che_do ? $ma : '' );
			if ( $cu === $moi ) {
				$wpdb->query( 'ROLLBACK' );
				return array( 'ok' => true, 'doi' => false, 'loi' => array(), 'viec' => 0 );
			}
			if ( false === update_post_meta( $id, self::META, $moi ) ) {
				throw new \RuntimeException( 'Chưa ghi được lựa chọn thể thơ.' );
			}
			GanHinhThuc::gan( $id, 'loi' );
			$luc = gmdate( 'Y-m-d H:i:s' );
			$viec = Viec::tao( array( 'loai' => Viec::SUA, 'trang_thai' => Viec::XONG, 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $id, 'phan' => 'the_tho', 'nguoi_tao' => (string) $nguoi, 'tao_luc' => $luc, 'quyet' => 'luu', 'truoc' => $cu, 'sau' => $moi, 'nguoi_quyet' => $nguoi, 'quyet_luc' => $luc ) );
			if ( ! $viec || false === $wpdb->query( 'COMMIT' ) ) {
				throw new \RuntimeException( 'Chưa ghi được lựa chọn cùng lịch sử.' );
			}
			do_action( 'phongdao_da_luu_phan_loai', $id, 'hinh_thuc', $viec );
			return array( 'ok' => true, 'doi' => true, 'loi' => array(), 'viec' => $viec );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			clean_post_cache( $id );
			clean_object_term_cache( $id, KhaiBao::TAC_PHAM );
			return $hong( $e instanceof \RuntimeException ? $e->getMessage() : 'Chưa lưu được thể thơ; thay đổi đã được huỷ.' );
		}
	}
}
