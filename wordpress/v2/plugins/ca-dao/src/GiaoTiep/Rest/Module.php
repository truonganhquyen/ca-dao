<?php

namespace Phongdao\GiaoTiep\Rest;

use Phongdao\HaTang\CaiDat;
use Phongdao\HaTang\KhoiDong;

defined( 'ABSPATH' ) || exit;

/**
 * REST phongdao/v1 (docs/26 mục 5). Route biên tập kiểm quyền và nonce; route người đọc công khai, chỉ đọc hoặc chống rác.
 */
final class Module implements \Phongdao\HaTang\Module {

	public const KHONG_GIAN = 'phongdao/v1';

	public function dang_ky(): void {
		add_action( 'rest_api_init', array( $this, 'dang_ky_route' ) );
	}

	public function dang_ky_route(): void {
		register_rest_route(
			self::KHONG_GIAN,
			'/suc-khoe',
			array(
				'methods'             => 'GET',
				'permission_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
				'callback'            => static fn(): array => array(
					'phien_ban' => CADAO_PHIEN_BAN,
					'csdl'      => (int) get_option( CaiDat::TUY_CHON, 0 ),
					'module'    => count( KhoiDong::MODULE ),
				),
			)
		);
		// Người đọc: kết quả tìm khi gõ (cùng HTML trang /tim/ in ra; công khai, chỉ đọc, không ghi gì)
		register_rest_route(
			self::KHONG_GIAN,
			'/tim',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'args'                => array(
					'q' => array(
						'type'     => 'string',
						'required' => true,
					),
				),
				'callback'            => static fn( \WP_REST_Request $yc ): array => array( 'html' => \Phongdao\GiaoTiep\Khoi\Tim::ket_qua( (string) $yc['q'], \Phongdao\DichVu\ChiMucTim\TimDoc::loc( $yc->get_params() ), '1' === (string) $yc['dang_go'] ) ),
			)
		);
	}
}
