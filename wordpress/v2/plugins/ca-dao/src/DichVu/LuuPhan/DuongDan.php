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
	public const META_CU = '_phongdao_duong_dan_cu';

	public static function phien( int $id ): string {
		return hash( 'sha256', wp_json_encode( array( get_post_field( 'post_name', $id ), get_post_status( $id ), get_permalink( $id ), Viec::moi_nhat_sua( $id, 'duong_dan' ) ) ) );
	}

	/** Sửa tên cuối đường dẫn do quản trị chủ động gửi; không đổi lời, ID hoặc trạng thái bài. */
	public static function luu( int $id, string $ten, string $phien, int $nguoi ): array {
		global $wpdb;
		$hong = static fn( string $loi ): array => array( 'ok' => false, 'doi' => false, 'loi' => array( $loi ) );
		if ( ! user_can( $nguoi, 'manage_options' ) || ! user_can( $nguoi, 'cadao_soan' ) || ! user_can( $nguoi, 'edit_post', $id ) || KhaiBao::TAC_PHAM !== get_post_type( $id ) || in_array( get_post_status( $id ), array( 'trash', 'auto-draft' ), true ) ) {
			return $hong( 'Chỉ quản trị có quyền sửa bài mới được đổi đường dẫn.' );
		}
		if ( preg_match( '~[/\\\\?#]~', $ten ) || '' === ( $moi = sanitize_title( trim( $ten ) ) ) ) {
			return $hong( 'Nhập tên cuối đường dẫn, ví dụ ten-tac-pham; không nhập địa chỉ đầy đủ.' );
		}
		if ( preg_match( '/%[0-7][a-f0-9]/i', $moi ) || ! mb_check_encoding( rawurldecode( $moi ), 'UTF-8' ) ) {
			return $hong( 'Tên đường dẫn chứa ký tự mã hoá không dùng được. Dùng chữ, số và dấu gạch nối.' );
		}
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) { return $hong( 'Chưa mở được lượt lưu đường dẫn.' ); }
		try {
			// Cùng một khoá cho mọi lần đổi URL: hai bài khác nhau không cùng nhận một tên mới.
			if ( null === $wpdb->get_var( "SELECT option_id FROM {$wpdb->options} WHERE option_name = 'siteurl' FOR UPDATE" ) || ! LuuPhan::khoa_bai( $id ) ) {
				throw new \RuntimeException( 'Chưa khoá được bài để đổi đường dẫn.' );
			}
			if ( '' === $phien || ! hash_equals( self::phien( $id ), $phien ) ) {
				throw new \RuntimeException( 'Đường dẫn hoặc trạng thái bài đã đổi. Tải lại để đối chiếu trước khi lưu tiếp.' );
			}
			$bai = get_post( $id ); $cu = $bai->post_name;
			if ( $moi === $cu ) { $wpdb->query( 'ROLLBACK' ); return array( 'ok' => true, 'doi' => false, 'loi' => array() ); }
			if ( wp_unique_post_slug( $moi, $id, 'publish', KhaiBao::TAC_PHAM, 0 ) !== $moi ) {
				throw new \RuntimeException( 'Tên đường dẫn này đã có bài dùng hoặc được WordPress dành riêng. Chọn tên khác.' );
			}
			$giu_cu = $wpdb->get_var( $wpdb->prepare( "SELECT p.ID FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID WHERE p.post_type = %s AND p.ID <> %d AND m.meta_key = '_wp_old_slug' AND m.meta_value = %s LIMIT 1", KhaiBao::TAC_PHAM, $id, $moi ) );
			if ( $giu_cu ) { throw new \RuntimeException( 'Tên này là đường dẫn cũ của tác phẩm khác. Chọn tên khác để giữ liên kết cũ.' ); }
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s AND ID <> %d LIMIT 1", KhaiBao::TAC_PHAM, $cu, $id ) ) ) {
				throw new \RuntimeException( 'Đường dẫn hiện tại đang dùng chung với bài khác. Dùng thao tác tách đường dẫn trong danh sách Tác phẩm trước.' );
			}
			$url_cu = (string) get_permalink( $id );
			$path = (string) wp_parse_url( $url_cu, PHP_URL_PATH );
			$doi = wp_update_post( array( 'ID' => $id, 'post_name' => $moi ), true );
			if ( is_wp_error( $doi ) || get_post_field( 'post_name', $id, 'raw' ) !== $moi ) { throw new \RuntimeException( 'Chưa ghi được tên đường dẫn mới.' ); }
			if ( '' !== $path && ! in_array( $path, get_post_meta( $id, self::META_CU ), true ) && ! add_post_meta( $id, self::META_CU, $path ) ) { throw new \RuntimeException( 'Chưa giữ được chuyển hướng đường dẫn cũ.' ); }
			// Giữ tên cũ cả cho bản nháp; không để bài khác lấy mất sau khi bài được đăng.
			if ( '' !== $cu && ! in_array( $cu, get_post_meta( $id, '_wp_old_slug' ), true ) && ! add_post_meta( $id, '_wp_old_slug', $cu ) ) { throw new \RuntimeException( 'Chưa giữ được tên đường dẫn cũ.' ); }
			$luc = gmdate( 'Y-m-d H:i:s' );
			$viec = Viec::tao( array( 'loai' => Viec::SUA, 'trang_thai' => Viec::XONG, 'doi_tuong_loai' => 'post', 'doi_tuong_id' => $id, 'phan' => 'duong_dan', 'nguoi_tao' => (string) $nguoi, 'tao_luc' => $luc, 'quyet' => 'luu', 'truoc' => array( 'post_name' => $cu, 'url' => $url_cu ), 'sau' => array( 'post_name' => $moi, 'url' => get_permalink( $id ) ), 'nguoi_quyet' => $nguoi, 'quyet_luc' => $luc ) );
			if ( ! $viec || false === $wpdb->query( 'COMMIT' ) ) { throw new \RuntimeException( 'Chưa lưu được đường dẫn cùng lịch sử và chuyển hướng.' ); }
			return array( 'ok' => true, 'doi' => true, 'loi' => array() );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' ); clean_post_cache( $id );
			return $hong( $e instanceof \RuntimeException ? $e->getMessage() : 'Chưa đổi được đường dẫn; thay đổi đã được huỷ.' );
		}
	}

	/** Chỉ chuyển URL cũ đã ghi nhận tới một bài công khai; trang còn tồn tại không bị đè. */
	public static function chuyen_cu( string $path ): ?string {
		$path = '/' . trim( $path, '/' ) . '/';
		$ids = get_posts( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => 2, 'meta_key' => self::META_CU, 'meta_value' => $path ) );
		return 1 === count( $ids ) ? (string) get_permalink( $ids[0] ) : null;
	}

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
