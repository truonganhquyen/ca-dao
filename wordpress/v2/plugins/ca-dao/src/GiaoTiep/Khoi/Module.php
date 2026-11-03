<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\HaTang\KhoiDong;

defined( 'ABSPATH' ) || exit;

/**
 * Đăng ký khối động cadao/* từ khoi/<tên>/block.json (render.php chạy phía máy chủ; docs/26 mục 6).
 */
final class Module implements \Phongdao\HaTang\Module {

	public function dang_ky(): void {
		add_action( 'init', array( $this, 'dang_ky_khoi' ) );
		add_action( 'init', array( Nguon::class, 'dang_ky' ) );
		// Tuyển tập chưa đủ điều kiện công khai (lời dẫn, số câu): khách nhận 404, người biên soạn vẫn xem được
		add_action(
			'template_redirect',
			static function (): void {
				$tt = get_queried_object();
				if ( is_singular( \Phongdao\HaTang\KhaiBao::TUYEN_TAP ) && $tt instanceof \WP_Post && ! TuyenTap::cong_khai( $tt ) && ! current_user_can( 'cadao_soan' ) ) {
					global $wp_query;
					$wp_query->set_404();
					status_header( 404 );
					nocache_headers();
				}
			}
		);
	}

	public function dang_ky_khoi(): void {
		foreach ( glob( KhoiDong::thu_muc() . '/khoi/*/block.json' ) ?: array() as $tep ) {
			register_block_type( dirname( $tep ) );
		}
	}
}
