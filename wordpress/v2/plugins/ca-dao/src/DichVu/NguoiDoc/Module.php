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
		add_action( 'admin_post_' . NhanGui::HANH_DONG, array( NhanGui::class, 'xu_ly' ) );
		add_action( 'admin_post_nopriv_' . NhanGui::HANH_DONG, array( NhanGui::class, 'xu_ly' ) );
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
				$site = NhanGui::turnstile()['site'];
				$co   = 'gop' === TrangCoDinh::dang_xem() || ( is_page() && has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'cadao_viet_phan_tich' ) );
				if ( '' !== $site && $co ) {
					wp_enqueue_script( 'phongdao-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, array( 'strategy' => 'defer', 'in_footer' => true ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- dịch vụ ngoài, người quản trị bật
				}
			}
		);
	}
}
