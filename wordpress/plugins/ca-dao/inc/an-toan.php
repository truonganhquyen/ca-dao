<?php
/**
 * Khoá cửa quản trị (T-124, D-076; báo cáo E2E 26/10/2026, L-16, L-03): tắt XML-RPC, không lộ tên đăng nhập, một câu
 * báo chung khi đăng nhập sai, tạm khoá sau nhiều lần sai. Đếm lần sai theo tài khoản (hoặc theo chữ đã gõ khi không có
 * tài khoản đó), không theo địa chỉ IP — không thu IP (AGENTS.md). Site không dùng XML-RPC: người sáng lập soạn bằng
 * trình soạn khối, máy chủ cập nhật bằng WP-CLI.
 */

defined( 'ABSPATH' ) || exit;

const CADAO_DANG_NHAP_SAI_TOI_DA = 5;
const CADAO_DANG_NHAP_KHOA_GIAY  = 15 * MINUTE_IN_SECONDS;

// ---------- XML-RPC: tắt hẳn (system.multicall cho thử hàng trăm mật khẩu trong một yêu cầu; pingback dội tải) ----------

add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );
add_filter( 'pings_open', '__return_false' );
remove_action( 'wp_head', 'rsd_link' );
add_filter(
	'wp_headers',
	static function ( $dau ) {
		unset( $dau['X-Pingback'] );
		return $dau;
	}
);
add_action(
	'plugins_loaded',
	static function () {
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			status_header( 403 );
			header( 'Content-Type: text/plain; charset=utf-8' );
			echo 'XML-RPC đã tắt trên site này.';
			exit;
		}
	},
	0
);

// ---------- Không lộ tên đăng nhập ----------

// REST: danh sách người dùng chỉ cho người soạn bài (trình soạn khối cần)
add_filter(
	'rest_endpoints',
	static function ( $duong ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			foreach ( array_keys( $duong ) as $k ) {
				if ( str_starts_with( $k, '/wp/v2/users' ) ) {
					unset( $duong[ $k ] );
				}
			}
		}
		return $duong;
	}
);

// Trang tác giả và ?author=N (chuyển tới /author/<tên đăng nhập>/) → trang chủ. Site không có trang tác giả.
add_action(
	'template_redirect',
	static function () {
		if ( is_author() || isset( $_GET['author'] ) ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	0
);
add_filter( 'wp_sitemaps_add_provider', static fn( $nguon, $ten ) => 'users' === $ten ? false : $nguon, 10, 2 );
add_filter(
	'oembed_response_data',
	static function ( $du_lieu ) {
		unset( $du_lieu['author_name'], $du_lieu['author_url'] );
		return $du_lieu;
	}
);
add_filter( 'comment_class', static fn( $lop ) => array_values( array_filter( $lop, static fn( $c ) => ! str_starts_with( $c, 'comment-author-' ) ) ) );

// ---------- Đăng nhập: một câu báo chung, tạm khoá sau nhiều lần sai ----------

/** Khoá đếm lần sai: theo tài khoản nếu chữ gõ vào là tên hoặc email của một tài khoản, không thì theo chính chữ đó. */
function cadao_khoa_dang_nhap( string $ten ): string {
	$ten = strtolower( trim( $ten ) );
	$tk  = get_user_by( 'login', $ten ) ?: get_user_by( 'email', $ten );
	return 'cadao_dn_' . ( $tk ? 'u' . $tk->ID : md5( $ten ) );
}

/** Đang tạm khoá không (đã sai đủ số lần trong 15 phút). */
function cadao_dang_nhap_bi_khoa( string $ten ): bool {
	$dem = get_transient( cadao_khoa_dang_nhap( $ten ) );
	return is_array( $dem ) && $dem[0] >= CADAO_DANG_NHAP_SAI_TOI_DA;
}

add_action(
	'wp_login_failed',
	static function ( $ten ) {
		$k   = cadao_khoa_dang_nhap( (string) $ten );
		$dem = get_transient( $k );
		$dem = is_array( $dem ) ? array( $dem[0] + 1, $dem[1] ) : array( 1, time() );
		set_transient( $k, $dem, max( 1, $dem[1] + CADAO_DANG_NHAP_KHOA_GIAY - time() ) ); // hết hạn tính từ lần sai đầu
	}
);
add_action( 'wp_login', static fn( $ten ) => delete_transient( cadao_khoa_dang_nhap( (string) $ten ) ) );

add_filter(
	'authenticate',
	static function ( $kq, $ten ) {
		if ( '' !== trim( (string) $ten ) && cadao_dang_nhap_bi_khoa( (string) $ten ) ) {
			return new WP_Error( 'cadao_tam_khoa', '<strong>Lỗi:</strong> Đăng nhập sai nhiều lần. Xin đợi 15 phút rồi thử lại, hoặc bấm "Quên mật khẩu?" để đặt mật khẩu mới.' );
		}
		if ( is_wp_error( $kq ) && array_intersect( $kq->get_error_codes(), array( 'invalid_username', 'invalid_email', 'incorrect_password' ) ) ) {
			return new WP_Error( 'cadao_dang_nhap_sai', '<strong>Lỗi:</strong> Tên đăng nhập hoặc mật khẩu không đúng.' );
		}
		return $kq;
	},
	99,
	2
);
