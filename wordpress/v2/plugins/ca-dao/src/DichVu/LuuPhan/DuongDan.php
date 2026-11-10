<?php

namespace Phongdao\DichVu\LuuPhan;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/**
 * Tên đường dẫn của tác phẩm (T-178). Địa chỉ /<thể loại>/<tên>/ tra bài theo tên, nên hai tác phẩm đã đăng cùng tên thì
 * người đọc chỉ mở được một bài; bài kia không còn lối vào (thẻ của nó mở ra bài khác). Lớp này cho bài mới một tên chưa
 * ai dùng, tìm các nhóm đang trùng, và — chỉ khi người quản trị bấm (H6: đổi đường dẫn bài đã đăng) — cho một bài tên
 * riêng có đuôi số.
 */
final class DuongDan {

	/** Tên đường dẫn chưa tác phẩm nào dùng (xét mọi trạng thái), cho bài mới tạo ở trạng thái nháp. */
	public static function ten_moi( string $tieu_de ): string {
		return wp_unique_post_slug( sanitize_title( $tieu_de ), 0, 'publish', KhaiBao::TAC_PHAM, 0 );
	}

	/**
	 * Nhóm tác phẩm đã đăng cùng tên đường dẫn; mo_duoc = bài người đọc mở được khi vào địa chỉ đó (đúng truy vấn
	 * WordPress dùng cho trang bài).
	 *
	 * @return array<string,array<int,array{id:int,ten:string,ngay:string,mo_duoc:bool}>> tên => các bài (bài mở được trước)
	 */
	public static function trung(): array {
		global $wpdb;
		$p    = $wpdb->posts;
		$hang = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_name, post_title, post_date FROM $p WHERE post_type = %s AND post_status = 'publish' AND post_name IN ( SELECT post_name FROM $p WHERE post_type = %s AND post_status = 'publish' AND post_name <> '' GROUP BY post_name HAVING COUNT(*) > 1 ) ORDER BY post_name, post_date, ID", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tên bảng
				KhaiBao::TAC_PHAM,
				KhaiBao::TAC_PHAM
			),
			ARRAY_A
		) ?: array();
		$ra = array();
		foreach ( $hang as $r ) {
			$ra[ $r['post_name'] ][] = array( 'id' => (int) $r['ID'], 'ten' => (string) $r['post_title'], 'ngay' => (string) $r['post_date'], 'mo_duoc' => false );
		}
		foreach ( $ra as $ten => $ds ) {
			$q   = new \WP_Query( array( 'name' => $ten, 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'posts_per_page' => 1, 'no_found_rows' => true, 'fields' => 'ids' ) );
			$mo  = (int) ( $q->posts[0] ?? 0 );
			$ds  = array_map( static fn( array $b ): array => array( 'mo_duoc' => $b['id'] === $mo ) + $b, $ds );
			usort( $ds, static fn( array $a, array $b ): int => (int) $b['mo_duoc'] <=> (int) $a['mo_duoc'] );
			$ra[ $ten ] = $ds;
		}
		return $ra;
	}

	/**
	 * Cho tác phẩm $id tên đường dẫn riêng (tên hiện tại + đuôi số chưa ai dùng) — chỉ khi bài đang trùng tên với một tác
	 * phẩm đã đăng khác. WordPress giữ tên cũ trong _wp_old_slug; ghi một dòng nhật ký trước/sau cùng giao dịch.
	 *
	 * @return array{ok:bool,thong_bao:string}
	 */
	public static function tach( int $id, int $nguoi ): array {
		global $wpdb;
		$bai  = get_post( $id );
		$nhom = $bai ? ( self::trung()[ $bai->post_name ] ?? array() ) : array();
		if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'publish' !== $bai->post_status || count( $nhom ) < 2 ) {
			return array( 'ok' => false, 'thong_bao' => 'Bài này không còn trùng đường dẫn với bài nào; chưa đổi gì.' );
		}
		$cu  = $bai->post_name;
		$moi = wp_unique_post_slug( $cu, $id, 'publish', KhaiBao::TAC_PHAM, 0 );
		if ( $moi === $cu || false === $wpdb->query( 'START TRANSACTION' ) ) {
			return array( 'ok' => false, 'thong_bao' => 'Chưa tạo được đường dẫn riêng. Thử lại sau.' );
		}
		$doi  = wp_update_post( array( 'ID' => $id, 'post_name' => $moi ), true );
		$luc  = gmdate( 'Y-m-d H:i:s' );
		$viec = is_wp_error( $doi ) || get_post_field( 'post_name', $id, 'raw' ) !== $moi ? 0 : Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => 'post',
				'doi_tuong_id'   => $id,
				'phan'           => 'duong_dan',
				'nguoi_tao'      => (string) $nguoi,
				'tao_luc'        => $luc,
				'quyet'          => 'tach_duong_dan',
				'truoc'          => array( 'post_name' => $cu, 'cung_ten' => array_values( array_diff( array_column( $nhom, 'id' ), array( $id ) ) ) ),
				'sau'            => array( 'post_name' => $moi ),
				'nguoi_quyet'    => $nguoi,
				'quyet_luc'      => $luc,
			)
		);
		if ( ! $viec || false === $wpdb->query( 'COMMIT' ) ) {
			$wpdb->query( 'ROLLBACK' );
			clean_post_cache( $id );
			return array( 'ok' => false, 'thong_bao' => 'Chưa đổi được đường dẫn; mọi thay đổi đã được huỷ.' );
		}
		return array( 'ok' => true, 'thong_bao' => 'Bài #' . $id . ' nay có đường dẫn riêng: ' . get_permalink( $id ) );
	}
}
