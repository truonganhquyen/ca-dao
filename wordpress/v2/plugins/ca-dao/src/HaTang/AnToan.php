<?php

namespace Phongdao\HaTang;

defined( 'ABSPATH' ) || exit;

/**
 * Khoá cửa quản trị (chuyển nguyên từ MVP an-toan.php; T-124, D-076): tắt XML-RPC và pingback, không lộ tên đăng nhập
 * (REST danh sách người dùng, trang tác giả, oEmbed, lớp CSS bình luận, sơ đồ trang), một câu báo chung khi đăng nhập
 * sai, tạm khoá 15 phút sau 5 lần sai. Đếm lần sai theo tài khoản (hoặc theo chữ đã gõ khi không có tài khoản đó), không
 * theo địa chỉ mạng — không thu địa chỉ mạng.
 */
final class AnToan implements Module {

	public const SAI_TOI_DA = 5;

	public const KHOA_GIAY = 15 * MINUTE_IN_SECONDS;

	public function dang_ky(): void {
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'xmlrpc_methods', '__return_empty_array' );
		add_filter( 'pings_open', '__return_false' );
		remove_action( 'wp_head', 'rsd_link' );
		add_filter(
			'wp_headers',
			static function ( array $dau ): array {
				unset( $dau['X-Pingback'] );
				return $dau;
			}
		);
		add_action(
			'plugins_loaded',
			static function (): void {
				if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
					status_header( 403 );
					header( 'Content-Type: text/plain; charset=utf-8' );
					echo 'XML-RPC đã tắt trên site này.';
					exit;
				}
			},
			0
		);
		// Danh sách người dùng qua REST chỉ cho người soạn bài
		add_filter(
			'rest_endpoints',
			static function ( array $duong ): array {
				if ( ! current_user_can( 'edit_posts' ) ) {
					foreach ( array_keys( $duong ) as $k ) {
						if ( str_starts_with( (string) $k, '/wp/v2/users' ) ) {
							unset( $duong[ $k ] );
						}
					}
				}
				return $duong;
			}
		);
		// Trang tác giả và ?author=N (lộ tên đăng nhập) → trang chủ
		add_action(
			'template_redirect',
			static function (): void {
				if ( is_author() || isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					wp_safe_redirect( home_url( '/' ), 301 );
					exit;
				}
			},
			0
		);
		add_filter( 'wp_sitemaps_add_provider', static fn( $nguon, $ten ) => 'users' === $ten ? false : $nguon, 10, 2 );
		add_filter(
			'oembed_response_data',
			static function ( array $du ): array {
				unset( $du['author_name'], $du['author_url'] );
				return $du;
			}
		);
		add_filter( 'comment_class', static fn( array $lop ): array => array_values( array_filter( $lop, static fn( $c ) => ! str_starts_with( (string) $c, 'comment-author-' ) ) ) );

		add_action( 'wp_login_failed', array( self::class, 'ghi_sai' ) );
		add_action( 'wp_login', static fn( $ten ) => delete_transient( self::khoa( (string) $ten ) ) );
		add_filter( 'authenticate', array( self::class, 'xac_thuc' ), 99, 2 );
	}

	/** Khoá đếm lần sai: theo tài khoản nếu chữ gõ là tên hoặc email của một tài khoản, không thì theo chính chữ đó. */
	public static function khoa( string $ten ): string {
		$ten = strtolower( trim( $ten ) );
		$tk  = get_user_by( 'login', $ten ) ?: get_user_by( 'email', $ten );
		return 'cadao_dn_' . ( $tk ? 'u' . $tk->ID : md5( $ten ) ); // cùng khoá MVP: lần sai đang đếm không mất khi chuyển
	}

	public static function bi_khoa( string $ten ): bool {
		$dem = get_transient( self::khoa( $ten ) );
		return is_array( $dem ) && $dem[0] >= self::SAI_TOI_DA;
	}

	public static function ghi_sai( $ten ): void {
		$k   = self::khoa( (string) $ten );
		$dem = get_transient( $k );
		$dem = is_array( $dem ) ? array( $dem[0] + 1, $dem[1] ) : array( 1, time() );
		set_transient( $k, $dem, max( 1, $dem[1] + self::KHOA_GIAY - time() ) ); // hết hạn tính từ lần sai đầu
	}

	/** @param \WP_User|\WP_Error|null $kq */
	public static function xac_thuc( $kq, $ten ) {
		if ( '' !== trim( (string) $ten ) && self::bi_khoa( (string) $ten ) ) {
			return new \WP_Error( 'cadao_tam_khoa', '<strong>Lỗi:</strong> Đăng nhập sai nhiều lần. Xin đợi 15 phút rồi thử lại, hoặc bấm "Quên mật khẩu?" để đặt mật khẩu mới.' );
		}
		if ( $kq instanceof \WP_Error && array_intersect( $kq->get_error_codes(), array( 'invalid_username', 'invalid_email', 'incorrect_password' ) ) ) {
			return new \WP_Error( 'cadao_dang_nhap_sai', '<strong>Lỗi:</strong> Tên đăng nhập hoặc mật khẩu không đúng.' );
		}
		return $kq;
	}
}
