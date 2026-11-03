<?php

namespace Phongdao\HaTang;

use Phongdao\DichVu;
use Phongdao\GiaoTiep;

defined( 'ABSPATH' ) || exit;

/**
 * Khởi động plugin: gắn hook kích hoạt, đăng ký các module khi WordPress đã nạp plugin.
 */
final class KhoiDong {

	/** Thứ tự đăng ký: hạ tầng → dịch vụ → giao tiếp. Module mới thêm vào đây (docs/26 mục 3). */
	public const MODULE = array(
		KhaiBao::class,
		CaiDat::class,
		AnToan::class,
		ChuyenHuong::class,
		DichVu\LuuPhan\GanHinhThuc::class,
		DichVu\DungTrang\DuLieuTrangChu::class,
		DichVu\ChiMucTim\ChiMucTim::class,
		DichVu\NhapNguon\NguonTuDong::class,
		DichVu\ChongTrung\ChongTrung::class,
		DichVu\UngVien\UngVien::class,
		DichVu\GoiY\GoiY::class,
		DichVu\NhapDuLieuCu\Module::class,
		DichVu\NguoiDoc\Module::class,
		DichVu\DoLuong\Module::class,
		DichVu\AnhChiaSe\Module::class,
		GiaoTiep\Rest\Module::class,
		GiaoTiep\Seo\Module::class,
		GiaoTiep\Khoi\Module::class,
		GiaoTiep\TrangCoDinh\Module::class,
		GiaoTiep\BienTap\Module::class,
		GiaoTiep\Cli\Module::class,
		GiaoTiep\QuanTri\Module::class,
	);

	private static string $tep = '';

	public static function chay( string $tep ): void {
		self::$tep = $tep;
		register_activation_hook( $tep, array( CaiDat::class, 'kich_hoat' ) );
		add_action( 'plugins_loaded', array( self::class, 'dang_ky_tat_ca' ) );
	}

	public static function dang_ky_tat_ca(): void {
		foreach ( self::MODULE as $lop ) {
			( new $lop() )->dang_ky();
		}
	}

	/** Thư mục gốc của plugin (không có dấu / ở cuối). */
	public static function thu_muc(): string {
		return dirname( self::$tep );
	}
}
