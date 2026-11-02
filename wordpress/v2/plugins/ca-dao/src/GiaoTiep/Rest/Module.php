<?php

namespace Phongdao\GiaoTiep\Rest;

use Phongdao\HaTang\CaiDat;
use Phongdao\HaTang\KhoiDong;

defined( 'ABSPATH' ) || exit;

/**
 * REST phongdao/v1 cho Bàn biên tập (docs/26 mục 5). Mọi route kiểm quyền; route ghi kiểm thêm nonce.
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
	}
}
