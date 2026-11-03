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
			\WP_CLI::add_command( 'phongdao tim', array( $this, 'tim' ) );
			\WP_CLI::add_command( 'phongdao nguon', array( $this, 'nguon' ) );
			\WP_CLI::add_command( 'phongdao cau-moi', array( $this, 'cau_moi' ) );
			\WP_CLI::add_command( 'phongdao kiem-ke', array( $this, 'kiem_ke' ) );
			\WP_CLI::add_command( 'phongdao nhap', array( $this, 'nhap' ) );
		}
	}

	/**
	 * Kiểm kê dữ liệu người làm trên MVP (docs/29 mục 2): đếm từng dòng, liệt kê khoá lạ. Không ghi gì.
	 */
	public function kiem_ke(): void {
		foreach ( \Phongdao\DichVu\NhapDuLieuCu\KiemKe::in() as $dong ) {
			\WP_CLI::line( $dong );
		}
	}

	/**
	 * Nhập dữ liệu MVP sang V2 (docs/29 mục 3, 4).
	 *
	 * ## OPTIONS
	 *
	 * [--thu]
	 * : Chạy mọi quy tắc trong bộ nhớ, in báo cáo; không ghi gì.
	 */
	public function nhap( array $doi, array $tuy ): void {
		if ( ! isset( $tuy['thu'] ) ) {
			\WP_CLI::error( 'Bước nhập thật chưa có ở bản này; dùng --thu.' );
		}
		foreach ( \Phongdao\DichVu\NhapDuLieuCu\NhapThu::in( \Phongdao\DichVu\NhapDuLieuCu\NhapThu::chay() ) as $dong ) {
			\WP_CLI::line( $dong );
		}
	}

	/**
	 * Chọn câu mới từ kho vào Hộp việc ngay (không đợi lịch hằng ngày).
	 *
	 * ## OPTIONS
	 *
	 * [--nguon=<id>]
	 * : Mã nguồn. Mặc định: cadao-me.
	 *
	 * [--so=<so>]
	 * : Số câu. Mặc định: số câu mỗi ngày của nguồn.
	 */
	public function cau_moi( array $doi, array $tuy ): void {
		$n = \Phongdao\DichVu\NhapNguon\NguonTuDong::lay( (string) ( $tuy['nguon'] ?? 'cadao-me' ) );
		if ( ! $n ) {
			\WP_CLI::error( 'Không có nguồn này.' );
		}
		$so = \Phongdao\DichVu\UngVien\UngVien::chon( $n, (int) ( $tuy['so'] ?? $n['so_bai_moi_ngay'] ) );
		\WP_CLI::success( "Đã thêm $so câu mới vào Hộp việc." );
	}

	/**
	 * Liệt kê nguồn tự động, hoặc chạy một lượt đọc của một nguồn (cron thật của máy chủ gọi được).
	 *
	 * ## OPTIONS
	 *
	 * [<id>]
	 * : Mã nguồn cần chạy một lượt (ví dụ cadao-me).
	 */
	public function nguon( array $doi ): void {
		$id = (string) ( $doi[0] ?? '' );
		if ( '' === $id ) {
			foreach ( \Phongdao\DichVu\NhapNguon\NguonTuDong::tat_ca() as $ma => $n ) {
				\WP_CLI::line( $ma . "\t" . ( empty( $n['bat'] ) ? 'tắt' : 'bật' ) . "\t" . ( $n['ten'] ?? '' ) );
			}
			$dem = \Phongdao\KhoLuu\Kho::dem();
			\WP_CLI::line( 'Kho: ' . $dem['moi'] . ' mới · ' . $dem['da_tao_bai'] . ' đã thành tác phẩm · ' . $dem['da_gop'] . ' đã gộp · ' . $dem['bo_qua'] . ' bỏ qua' );
			return;
		}
		$kq = \Phongdao\DichVu\NhapNguon\NguonTuDong::chay( $id );
		\WP_CLI::line( wp_json_encode( $kq, JSON_UNESCAPED_UNICODE ) );
	}

	/**
	 * Tìm thử, hoặc dựng lại toàn bộ chỉ mục tìm.
	 *
	 * ## OPTIONS
	 *
	 * [<cau>]
	 * : Câu cần tìm.
	 *
	 * [--dung-lai]
	 * : Dựng lại chỉ mục từ các phần đã lưu của mọi tác phẩm đã đăng.
	 */
	public function tim( array $doi, array $tuy ): void {
		if ( isset( $tuy['dung-lai'] ) ) {
			\Phongdao\DichVu\ChiMucTim\ChiMucTim::bat_dau_dung_lai();
			$tong = 0;
			do {
				$so    = \Phongdao\DichVu\ChiMucTim\ChiMucTim::dung_lai_lo();
				$tong += $so;
			} while ( $so > 0 && (int) get_option( \Phongdao\DichVu\ChiMucTim\ChiMucTim::SAU, -1 ) >= 0 );
			wp_clear_scheduled_hook( \Phongdao\DichVu\ChiMucTim\ChiMucTim::VIEC_NEN );
			\WP_CLI::success( "Đã dựng lại chỉ mục cho $tong bài." );
			return;
		}
		foreach ( \Phongdao\DichVu\ChiMucTim\ChiMucTim::tim( (string) ( $doi[0] ?? '' ) )['ds'] as $x ) {
			\WP_CLI::line( $x['id'] . "\t" . $x['diem'] . ( $x['roi'] ? "\trời" : '' ) . "\t" . get_the_title( $x['id'] ) );
		}
	}

	/**
	 * In phiên bản plugin và lược đồ cơ sở dữ liệu.
	 */
	public function phien_ban(): void {
		\WP_CLI::line( 'Plugin ' . CADAO_PHIEN_BAN . ' · lược đồ ' . (int) get_option( CaiDat::TUY_CHON, 0 ) );
	}
}
