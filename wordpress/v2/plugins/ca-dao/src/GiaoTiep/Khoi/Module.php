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
	}

	public function dang_ky_khoi(): void {
		foreach ( glob( KhoiDong::thu_muc() . '/khoi/*/block.json' ) ?: array() as $tep ) {
			register_block_type( dirname( $tep ) );
		}
	}
}
