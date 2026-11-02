<?php

namespace Phongdao\HaTang;

defined( 'ABSPATH' ) || exit;

/**
 * Cài đặt và nâng cấp cơ sở dữ liệu theo số phiên bản lược đồ.
 * T-142 khai báo các bảng ở docs/26 mục 4.4 (SQL chạy được trên SQLite và MySQL) rồi tăng PHIEN_BAN_CSDL.
 */
final class CaiDat implements Module {

	public const PHIEN_BAN_CSDL = 1;

	/** Tuỳ chọn lưu phiên bản lược đồ đã cài. */
	public const TUY_CHON = 'phongdao_csdl';

	public function dang_ky(): void {
		add_action( 'admin_init', array( self::class, 'nang_cap' ) );
	}

	public static function kich_hoat(): void {
		self::nang_cap();
	}

	public static function nang_cap(): void {
		if ( (int) get_option( self::TUY_CHON, 0 ) >= self::PHIEN_BAN_CSDL ) {
			return;
		}
		update_option( self::TUY_CHON, self::PHIEN_BAN_CSDL, false );
	}
}
