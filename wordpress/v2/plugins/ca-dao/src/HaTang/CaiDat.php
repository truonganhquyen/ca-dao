<?php

namespace Phongdao\HaTang;

use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Cài đặt và nâng cấp cơ sở dữ liệu theo số phiên bản lược đồ (docs/26 mục 4.4). Mỗi bước đánh số, chạy lại được.
 * Máy chủ cập nhật plugin bằng cách chép tệp (không bấm Kích hoạt), nên nâng cấp chạy ở init khi số lược đồ cũ hơn.
 */
final class CaiDat implements Module {

	public const PHIEN_BAN_CSDL = 2;

	/** Tuỳ chọn lưu phiên bản lược đồ đã cài. */
	public const TUY_CHON = 'phongdao_csdl';

	public function dang_ky(): void {
		add_action( 'init', array( self::class, 'nang_cap' ), 1 );
	}

	public static function kich_hoat(): void {
		self::nang_cap();
	}

	public static function nang_cap(): void {
		$da_cai = (int) get_option( self::TUY_CHON, 0 );
		if ( $da_cai >= self::PHIEN_BAN_CSDL ) {
			return;
		}
		if ( $da_cai < 2 ) {
			self::buoc_2();
		}
		update_option( self::TUY_CHON, self::PHIEN_BAN_CSDL, false );
	}

	/** Bước 2 (D-094): bảng việc, các thể loại cố định, đường dẫn /<thể loại>/<tên>/. */
	private static function buoc_2(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( Viec::sql_tao( Viec::bang(), $wpdb->get_charset_collate() ) );
		KhaiBao::khai_bao();
		foreach ( TheLoai::DS as $ma => $ten ) {
			if ( ! term_exists( $ma, KhaiBao::THE_LOAI ) ) {
				wp_insert_term( $ten, KhaiBao::THE_LOAI, array( 'slug' => $ma ) );
			}
		}
		flush_rewrite_rules( false );
	}
}
