<?php

namespace Phongdao\DichVu\TimMeili;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\MienDuLieu\SoKhopLoi;

defined( 'ABSPATH' ) || exit;

/** Hai chỉ mục riêng; hàng đợi bền trong options, xác nhận từng lô sau task thành công. Không thêm bảng. */
final class ChiMuc implements \Phongdao\HaTang\Module {
	public const TT = 'phongdao_meili_trang_thai';
	public const DOI = 'phongdao_meili_doi_';
	public const CRON = 'phongdao_meili_dong_bo';
	private const KHOA = 'phongdao_meili_khoa';

	public function dang_ky(): void {
		add_action( self::CRON, array( self::class, 'nen' ) );
		add_action( 'init', static function (): void {
			if ( MayTim::bat() && self::cho( 1 ) && ! wp_next_scheduled( self::CRON ) ) { wp_schedule_single_event( time() + 5, self::CRON ); }
		} );
		add_action( 'phongdao_da_luu_phan', static fn( int $id ) => self::doi( 'bai:' . $id ), 99 );
		add_action( 'phongdao_kho_da_luu', static fn( int $id ) => self::doi( 'kho:' . $id ), 99 );
		add_action( 'save_post', static function ( int $id, \WP_Post $p ): void {
			if ( in_array( $p->post_type, array( KhaiBao::TAC_PHAM, KhaiBao::GOP_Y ), true ) ) { self::doi( ( KhaiBao::TAC_PHAM === $p->post_type ? 'bai:' : 'gop:' ) . $id ); }
		}, 99, 2 );
		add_action( 'before_delete_post', static function ( int $id, \WP_Post $p ): void {
			if ( in_array( $p->post_type, array( KhaiBao::TAC_PHAM, KhaiBao::GOP_Y ), true ) ) { self::doi( ( KhaiBao::TAC_PHAM === $p->post_type ? 'bai:' : 'gop:' ) . $id ); }
		}, 99, 2 );
		add_action( 'set_object_terms', static function ( int $id, $terms, $ids, string $tax ): void {
			if ( KhaiBao::THE_LOAI === $tax ) { self::doi( 'bai:' . $id ); }
		}, 99, 4 );
	}

	public static function doi( string $muc ): void {
		if ( ! preg_match( '/^(bai|kho|gop):[1-9][0-9]*$/D', $muc ) || ( ! MayTim::bat() && ! get_option( self::TT ) ) ) { return; }
		update_option( self::DOI . str_replace( ':', '_', $muc ), bin2hex( random_bytes( 12 ) ), false );
		if ( MayTim::bat() && ! wp_next_scheduled( self::CRON ) ) { wp_schedule_single_event( time() + 5, self::CRON ); }
	}

	public static function cho( int $so = 100 ): array {
		global $wpdb;
		$r = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_id LIMIT %d", $wpdb->esc_like( self::DOI ) . '%', $so ), ARRAY_A );
		if ( $wpdb->last_error ) { throw new \RuntimeException( 'Chưa đọc được hàng đợi máy tìm.' ); }
		return $r ?: array();
	}

	public static function san_sang(): bool {
		return MayTim::bat() && ! empty( get_option( self::TT, array() )['san_sang'] ) && ! self::cho( 1 );
	}

	/** Chỉ xuất lời và định danh, không xuất ghi công, email, nguồn hay hồ sơ cá nhân. */
	public static function doc( string $muc, bool $cong_khai ): array {
		[ $loai, $id ] = explode( ':', $muc ); $id = (int) $id;
		$lois = array(); $ten = ''; $url = ''; $the_loai = '';
		if ( 'bai' === $loai ) {
			$p = get_post( $id );
			if ( ! $p || KhaiBao::TAC_PHAM !== $p->post_type || in_array( $p->post_status, array( 'trash', 'auto-draft', 'inherit' ), true ) ) { return array(); }
			if ( $cong_khai && ( 'publish' !== $p->post_status || '' !== $p->post_password || PhanBai::lay( $id, 'tho_tuc' ) ) ) { return array(); }
			$lois[] = implode( "\n", PhanBai::lay( $id, 'loi' )['dong'] ?? array() );
			foreach ( PhanBai::lay( $id, 'di_ban' )['muc'] ?? array() as $v ) { $lois[] = implode( "\n", $v['dong'] ?? array() ); }
			$ten = $p->post_title; $url = 'publish' === $p->post_status ? get_permalink( $id ) : '';
			$t = get_the_terms( $id, KhaiBao::THE_LOAI ); $the_loai = is_array( $t ) && $t ? $t[0]->slug : '';
		} elseif ( ! $cong_khai && 'kho' === $loai ) {
			$m = Kho::lay( $id );
			if ( ! $m || 'moi' !== $m->trang_thai || $m->post_id ) { return array(); }
			$lois = array_merge( array( $m->loi ), (array) ( Kho::du_lieu( $m )['di_ban'] ?? array() ) ); $ten = 'Kho #' . $id;
		} elseif ( ! $cong_khai && 'gop' === $loai ) {
			$p = get_post( $id );
			if ( ! $p || KhaiBao::GOP_Y !== $p->post_type || ! in_array( $p->post_status, array( 'pending', 'draft' ), true ) ) { return array(); }
			$lois[] = \Phongdao\DichVu\NguoiDoc\DuyetTacPham::loi( $id ); $ten = 'Đóng góp đang chờ #' . $id;
		}
		$ra = array();
		foreach ( $lois as $phan => $loi ) {
			$chu = SoKhopLoi::chu( (string) $loi );
			if ( '' === $chu ) { continue; }
			$ra[] = array( 'id' => str_replace( ':', '_', $muc ) . '_' . $phan, 'muc' => $muc, 'phan' => $phan, 'loi' => $chu, 'loi_kd' => SoKhopLoi::chu( (string) $loi, true ), 'ban' => hash( 'sha256', (string) $loi ), 'ten' => $ten, 'url' => $url, 'the_loai' => $the_loai, 'dong' => explode( "\n", (string) $loi ) );
		}
		return $ra;
	}

	private static function gui( string $index, array $docs ): void {
		// Lời gốc chỉ đọc lại từ WordPress lúc hiển thị; không cần lưu vào engine lần hai.
		$docs = array_map( static function ( $d ) { unset( $d['dong'], $d['ten'], $d['url'] ); return $d; }, $docs );
		if ( $docs ) { MayTim::ghi( 'POST', '/indexes/' . $index . '/documents', $docs ); }
	}

	private static function khoa(): string {
		$token = bin2hex( random_bytes( 12 ) );
		// Không tự cướp khoá theo thời gian: hai worker không được ghi chồng. CLI có lệnh mở khoá sau khi kiểm tiến trình.
		if ( ! add_option( self::KHOA, $token, '', false ) ) { throw new \RuntimeException( 'Đang có lượt đồng bộ máy tìm.' ); }
		return $token;
	}

	private static function mo_khoa( string $token ): void {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name=%s AND option_value=%s", self::KHOA, $token ) );
		wp_cache_delete( self::KHOA, 'options' );
	}

	public static function dong_bo(): int {
		if ( ! MayTim::bat() ) { return 0; }
		$tt = get_option( self::TT, array() );
		if ( empty( $tt['san_sang'] ) ) { throw new \RuntimeException( 'Cần dựng chỉ mục lần đầu bằng WP-CLI.' ); }
		$token = self::khoa();
		try {
			$cho = self::cho();
			if ( ! $cho ) { return 0; }
			$mucs = array_map( static fn( $r ) => str_replace( '_', ':', substr( $r['option_name'], strlen( self::DOI ) ) ), $cho );
			foreach ( array( 'public', 'staff' ) as $scope ) {
				$index = $tt[ $scope ]; $docs = array();
				MayTim::ghi( 'POST', '/indexes/' . $index . '/documents/delete', array( 'filter' => 'muc IN [' . implode( ',', array_map( static fn( $m ) => '"' . $m . '"', $mucs ) ) . ']' ) );
				foreach ( $mucs as $muc ) { $docs = array_merge( $docs, self::doc( $muc, 'public' === $scope ) ); }
				self::gui( $index, $docs );
			}
			global $wpdb;
			foreach ( $cho as $r ) {
				// Lượt sửa mới trong lúc đang ghi vẫn giữ trong hàng đợi, không bị lượt cũ xoá.
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name=%s AND option_value=%s", $r['option_name'], $r['option_value'] ) );
				if ( $wpdb->last_error ) { throw new \RuntimeException( 'Chưa xác nhận được hàng đợi.' ); }
				wp_cache_delete( $r['option_name'], 'options' );
			}
			return count( $cho );
		} finally { self::mo_khoa( $token ); }
	}

	public static function nen(): void {
		if ( ! MayTim::bat() ) { return; }
		try { self::dong_bo(); delete_option( 'phongdao_meili_loi' ); }
		catch ( \Throwable $e ) { update_option( 'phongdao_meili_loi', 'Chưa đồng bộ được máy tìm.', false ); }
		if ( self::cho( 1 ) && ! wp_next_scheduled( self::CRON ) ) { wp_schedule_single_event( time() + 60, self::CRON ); }
	}

	/** Dựng chỉ mục mới; chỉ chuyển tên hoạt động sau khi mọi task đã thành công. */
	public static function dung_lai(): int {
		if ( ! MayTim::bat() ) { throw new \RuntimeException( 'Chưa bật cấu hình máy tìm.' ); }
		$token = self::khoa(); $cu = get_option( self::TT, array() );
		try {
			$prefix = 'pd_' . substr( hash( 'sha256', home_url() ), 0, 12 ) . '_' . bin2hex( random_bytes( 5 ) );
			$tt = array( 'san_sang' => true, 'public' => $prefix . '_public', 'staff' => $prefix . '_staff' );
			foreach ( array( 'public', 'staff' ) as $scope ) {
				MayTim::ghi( 'POST', '/indexes', array( 'uid' => $tt[ $scope ], 'primaryKey' => 'id' ) );
				MayTim::ghi( 'PATCH', '/indexes/' . $tt[ $scope ] . '/settings', array( 'searchableAttributes' => array( 'loi', 'loi_kd' ), 'filterableAttributes' => array( 'muc', 'the_loai' ) ) );
			}
			global $wpdb; $tong = 0;
			foreach ( array( 'post', 'kho' ) as $nhom ) {
				$sau = 0;
				do {
					$ds = 'post' === $nhom
						? $wpdb->get_results( $wpdb->prepare( "SELECT ID AS id, post_type FROM {$wpdb->posts} WHERE post_type IN (%s,%s) AND ID>%d ORDER BY ID LIMIT 100", KhaiBao::TAC_PHAM, KhaiBao::GOP_Y, $sau ), ARRAY_A )
						: $wpdb->get_results( $wpdb->prepare( 'SELECT id FROM ' . Kho::bang() . ' WHERE id>%d ORDER BY id LIMIT 100', $sau ), ARRAY_A );
					if ( $wpdb->last_error ) { throw new \RuntimeException( 'Chưa đọc được dữ liệu để dựng chỉ mục.' ); }
					$docs = array( 'public' => array(), 'staff' => array() );
					foreach ( $ds ?: array() as $r ) {
						$sau = (int) $r['id']; $muc = ( 'kho' === $nhom ? 'kho' : ( KhaiBao::TAC_PHAM === $r['post_type'] ? 'bai' : 'gop' ) ) . ':' . $sau;
						foreach ( array_keys( $docs ) as $scope ) { $docs[ $scope ] = array_merge( $docs[ $scope ], self::doc( $muc, 'public' === $scope ) ); }
						++$tong;
					}
					foreach ( $docs as $scope => $lo ) { self::gui( $tt[ $scope ], $lo ); }
				} while ( count( $ds ?: array() ) === 100 );
			}
			update_option( self::TT, $tt, false );
			delete_option( 'phongdao_meili_loi' );
			// Giữ hàng đợi các lượt sửa trong lúc dựng; truy vấn chỉ dùng sau khi chúng đã được đồng bộ.
			foreach ( array( 'public', 'staff' ) as $scope ) {
				if ( ! empty( $cu[ $scope ] ) && preg_match( '/^pd_[a-f0-9_]+_(public|staff)$/D', $cu[ $scope ] ) ) {
					try { MayTim::ghi( 'DELETE', '/indexes/' . $cu[ $scope ] ); } catch ( \Throwable $e ) { /* Chỉ mục cũ không còn được đọc; vận hành có thể dọn sau. */ }
				}
			}
			return $tong;
		} finally { self::mo_khoa( $token ); }
	}
}
