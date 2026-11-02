<?php

namespace Phongdao\GiaoTiep\TrangCoDinh;

defined( 'ABSPATH' ) || exit;

/**
 * Trang cố định của thư viện (docs/27 mục 3): /tim/, /da-luu/, /kham-pha/… — đường dẫn do plugin khai báo, không tạo
 * trang trong cơ sở dữ liệu (không ai xoá nhầm được). Theme có mẫu `trang-<mã>.html` chứa khối `cadao/<mã>`.
 * Trang cũ cùng tên của MVP (nếu có) bị đường dẫn này che.
 */
final class Module implements \Phongdao\HaTang\Module {

	/** Mã (= đường dẫn) => tên trang. Thêm trang: thêm vào đây, thêm mẫu theme, tăng KhaiBao::PHIEN_BAN_DUONG_DAN. */
	public const TRANG = array(
		'tim' => 'Tìm',
	);

	public const BIEN = 'phongdao_trang';

	public function dang_ky(): void {
		add_action( 'init', array( self::class, 'duong_dan' ), 1 );
		add_filter( 'query_vars', static fn( array $v ): array => array_merge( $v, array( self::BIEN ) ) );
		add_action( 'parse_query', array( self::class, 'danh_dau' ) );
		add_filter( 'pre_handle_404', static fn( bool $co ): bool => $co || '' !== self::dang_xem() );
		add_filter( 'index_template_hierarchy', static fn( array $mau ): array => '' === self::dang_xem() ? $mau : array_merge( array( 'trang-' . self::dang_xem() ), $mau ) );
		add_filter(
			'document_title_parts',
			static function ( array $phan ): array {
				if ( '' !== self::dang_xem() ) {
					$phan['title'] = self::TRANG[ self::dang_xem() ];
				}
				return $phan;
			}
		);
		// Trang kết quả tìm không cần lập chỉ mục
		add_filter(
			'wp_robots',
			static function ( array $r ): array {
				if ( 'tim' === self::dang_xem() && '' !== trim( (string) ( $_GET['q'] ?? '' ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$r['noindex'] = true;
				}
				return $r;
			}
		);
		// Ô tìm mặc định của WordPress (?s=) chuyển về /tim/
		add_action(
			'template_redirect',
			static function (): void {
				if ( is_search() ) {
					wp_safe_redirect( add_query_arg( 'q', rawurlencode( get_search_query( false ) ), home_url( '/tim/' ) ), 302 );
					exit;
				}
			}
		);
	}

	public static function duong_dan(): void {
		add_rewrite_rule( '^(' . implode( '|', array_map( 'preg_quote', array_keys( self::TRANG ) ) ) . ')/?$', 'index.php?' . self::BIEN . '=$matches[1]', 'top' );
	}

	/** Trang cố định không phải trang chủ, không phải 404; không cần truy vấn bài. */
	public static function danh_dau( \WP_Query $q ): void {
		if ( ! $q->is_main_query() || ! isset( self::TRANG[ (string) $q->get( self::BIEN ) ] ) ) {
			return;
		}
		$q->is_home       = false;
		$q->is_404        = false;
		$q->is_archive    = false;
		$q->set( 'posts_per_page', 1 );
		$q->set( 'no_found_rows', true );
		$q->set( 'post_type', 'page' );
		$q->set( 'post__in', array( 0 ) );
	}

	/** Mã trang cố định đang xem, hoặc rỗng. */
	public static function dang_xem(): string {
		$ma = (string) get_query_var( self::BIEN );
		return isset( self::TRANG[ $ma ] ) ? $ma : '';
	}
}
