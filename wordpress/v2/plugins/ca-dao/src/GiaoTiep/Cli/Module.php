<?php

namespace Phongdao\GiaoTiep\Cli;

use Phongdao\HaTang\CaiDat;

defined( 'ABSPATH' ) || exit;

/**
 * Lệnh `wp phongdao …` (kiểm kê, nhập dữ liệu cũ, nạp nháp theo lô — docs/29 mục 4, docs/28 mục 3.5).
 */
final class Module implements \Phongdao\HaTang\Module {

	public function dang_ky(): void {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'phongdao phien-ban', array( $this, 'phien_ban' ) );
		}
	}

	/**
	 * In phiên bản plugin và lược đồ cơ sở dữ liệu.
	 */
	public function phien_ban(): void {
		\WP_CLI::line( 'Plugin ' . CADAO_PHIEN_BAN . ' · lược đồ ' . (int) get_option( CaiDat::TUY_CHON, 0 ) );
	}
}
