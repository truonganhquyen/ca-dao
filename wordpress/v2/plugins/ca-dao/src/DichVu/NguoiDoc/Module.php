<?php

namespace Phongdao\DichVu\NguoiDoc;

use Phongdao\GiaoTiep\Khoi\GopTuLieu;
use Phongdao\GiaoTiep\TrangCoDinh\Module as TrangCoDinh;
use Phongdao\HaTang\KhaiBao;

defined( 'ABSPATH' ) || exit;

/**
 * Người đọc góp: nhận form (admin-post, có hoặc không đăng nhập), đóng đường bình luận mặc định của WordPress cho tác
 * phẩm (cảm nghĩ chỉ qua form của thư viện — không ghi địa chỉ mạng), mã ngắn [cadao_viet_phan_tich] của trang
 * "Viết cảm nhận" mang từ MVP, nạp Turnstile chỉ ở trang có form và chỉ khi đã có khoá.
 */
final class Module implements \Phongdao\HaTang\Module {

	public function dang_ky(): void {
		add_action( 'template_redirect', static function (): void { if ( 'gop' === TrangCoDinh::dang_xem() ) { nocache_headers(); } }, 0 );
		add_action( 'rest_api_init', static function (): void {
			register_rest_route( \Phongdao\GiaoTiep\Rest\Module::KHONG_GIAN, '/gop/gan-giong', array( 'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => static function ( \WP_REST_Request $r ) {
				if ( strlen( $r->get_body() ) > 24000 || ! is_string( $r['loi'] ) || mb_strlen( $r['loi'] ) > 4000 ) { return new \WP_Error( 'du_lieu', 'Lời quá dài hoặc chưa hợp lệ.', array( 'status' => 400 ) ); }
				$ip = \Phongdao\MienDuLieu\DiaChiMang::cua( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ), (string) ( $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '' ) );
				$key = 'phongdao_tra_gop_' . substr( wp_hash( $ip . gmdate( 'YmdHi' ) ), 0, 24 ); $n = (int) get_transient( $key );
				if ( $n >= 30 ) { return new \WP_Error( 'nhieu', 'Xin thử tra lại sau một phút; bạn vẫn có thể gửi.', array( 'status' => 429 ) ); }
				set_transient( $key, $n + 1, MINUTE_IN_SECONDS );
				try { $data = SoTrung::cong_khai( (string) $r['loi'] ); } catch ( \Throwable $e ) { return new \WP_Error( 'loi', 'Chưa tra được, bạn vẫn có thể gửi.', array( 'status' => 503 ) ); }
				$resp = new \WP_REST_Response( array( 'bai' => $data ) ); $resp->header( 'Cache-Control', 'private, no-store' ); return $resp;
			} ) );
		} );
		add_action( 'admin_post_' . NhanGui::HANH_DONG, array( NhanGui::class, 'xu_ly' ) );
		add_action( 'admin_post_nopriv_' . NhanGui::HANH_DONG, array( NhanGui::class, 'xu_ly' ) );
		add_action( 'template_redirect', array( NhanGui::class, 'nhan_trang' ), 1 );
		add_filter( 'comments_open', static fn( bool $mo, $id ): bool => KhaiBao::TAC_PHAM === get_post_type( (int) $id ) ? false : $mo, 10, 2 );
		add_filter( 'pings_open', static fn( bool $mo, $id ): bool => KhaiBao::TAC_PHAM === get_post_type( (int) $id ) ? false : $mo, 10, 2 );
		add_action(
			'init',
			static function (): void {
				add_shortcode(
					'cadao_viet_phan_tich',
					// phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc để điền sẵn form
					static fn(): string => GopTuLieu::hien( 'phan_tich', (int) ( $_GET['bai'] ?? 0 ), sanitize_key( wp_unslash( $_GET['gui'] ?? '' ) ), sanitize_key( wp_unslash( $_GET['ma'] ?? '' ) ), 0, false )
				);
			}
		);
		add_action(
			'wp_enqueue_scripts',
			static function (): void {
				if ( 'gop' === TrangCoDinh::dang_xem() ) {
					wp_enqueue_script( 'phongdao-gop', plugins_url( 'assets/gop.js', dirname( __DIR__, 3 ) . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
				}
				$site = NhanGui::turnstile()['site'];
				// Trang bài cũng có form (Cách hiểu của bạn đọc, D-105)
				$co   = 'gop' === TrangCoDinh::dang_xem() || is_singular( \Phongdao\HaTang\KhaiBao::TAC_PHAM ) || ( is_page() && has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'cadao_viet_phan_tich' ) );
				if ( '' !== $site && $co ) {
					wp_enqueue_script( 'phongdao-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, array( 'strategy' => 'defer', 'in_footer' => true ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- dịch vụ ngoài, người quản trị bật
				}
			}
		);
	}
}
