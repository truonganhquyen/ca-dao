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
			\WP_CLI::add_command( 'phongdao tom-tat', array( $this, 'tom_tat' ) );
			\WP_CLI::add_command( 'phongdao nguon', array( $this, 'nguon' ) );
			\WP_CLI::add_command( 'phongdao cau-moi', array( $this, 'cau_moi' ) );
			\WP_CLI::add_command( 'phongdao kiem-ke', array( $this, 'kiem_ke' ) );
			\WP_CLI::add_command( 'phongdao nhap', array( $this, 'nhap' ) );
			\WP_CLI::add_command( 'phongdao doi-chieu', array( $this, 'doi_chieu' ) );
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
	 *
	 * [--lam]
	 * : Nhập thật theo lô (giữ ID bài, ghi giá trị cũ để hoàn tác). Dừng giữa chừng chạy lại được.
	 *
	 * [--hoan-tac]
	 * : Đặt lại mọi thứ bước nhập đã đổi; dữ liệu MVP trở về như trước khi nhập.
	 *
	 * [--lo=<so>]
	 * : Số bài mỗi lô khi --lam. Mặc định: 50.
	 */
	public function nhap( array $doi, array $tuy ): void {
		if ( isset( $tuy['hoan-tac'] ) ) {
			$kq = \Phongdao\DichVu\NhapDuLieuCu\NhapLam::hoan_tac();
			\WP_CLI::success( "Đã hoàn tác: {$kq['bai']} bài, {$kq['kho']} câu kho, {$kq['ho_so']} hồ sơ nguồn." );
			return;
		}
		if ( isset( $tuy['lam'] ) ) {
			$lo   = max( 1, (int) ( $tuy['lo'] ?? 50 ) );
			$tong = array( 'nhap' => 0, 'khong_mang' => 0, 'can_xem' => 0 );
			do {
				$kq = \Phongdao\DichVu\NhapDuLieuCu\NhapLam::chay( $lo );
				foreach ( $tong as $k => $v ) {
					$tong[ $k ] = $v + $kq[ $k ];
				}
				foreach ( $kq['loi'] as $loi ) {
					\WP_CLI::warning( $loi );
				}
				\WP_CLI::line( "… đã nhập {$tong['nhap']} bài, còn {$kq['con']}" );
			} while ( $kq['con'] && ! $kq['loi'] && ( $kq['nhap'] + $kq['khong_mang'] ) > 0 );
			$kq['loi'] ? \WP_CLI::error( 'Dừng vì có lỗi; sửa rồi chạy lại (bài đã nhập giữ nguyên, bài hỏng chưa đổi gì).' ) : \WP_CLI::success( "Xong: {$tong['nhap']} bài nhập ({$tong['can_xem']} bài có thẻ Cần xem), {$tong['khong_mang']} bài chờ duyệt máy tạo không mang." );
			return;
		}
		if ( ! isset( $tuy['thu'] ) ) {
			\WP_CLI::error( 'Chọn --thu, --lam hoặc --hoan-tac.' );
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
	/**
	 * Bảng tóm tắt bài (docs/32 mục 3.9): `--dung-lai` dựng lại mọi dòng ngay; không kèm gì thì in số dòng.
	 *
	 * ## OPTIONS
	 *
	 * [--dung-lai]
	 * : Dựng lại toàn bộ bảng từ các phần đã lưu trong bài.
	 */
	public function tom_tat( array $doi, array $tuy ): void {
		global $wpdb;
		if ( isset( $tuy['dung-lai'] ) ) {
			$n = \Phongdao\DichVu\TomTat\TomTat::dung_lai_ngay();
			\Phongdao\DichVu\TomTat\TomTat::cap_nhat_luot_doc();
			\WP_CLI::success( "Đã dựng lại bảng tóm tắt cho $n bài." );
			return;
		}
		\WP_CLI::line( (string) (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . \Phongdao\DichVu\TomTat\TomTat::bang() ) . ' dòng tóm tắt.' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- tên bảng của plugin
	}

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
	 * Đối chiếu trước / sau ngày chuyển (docs/29 mục 5, bước 4): số liệu MVP lưu lúc bắt đầu nhập cạnh số liệu sau khi
	 * nhập, cùng định nghĩa. Chỉ đọc.
	 *
	 * ## OPTIONS
	 *
	 * [--duong-dan]
	 * : Thử thêm tối đa 20 đường dẫn cũ (bài đổi tên, đổi thể loại; tình huống cũ) có ra 301 về đúng chỗ mới.
	 */
	public function doi_chieu( array $doi, array $tuy ): void {
		$khac = 0;
		foreach ( \Phongdao\DichVu\NhapDuLieuCu\DoiChieu::so() as [ $viec, $truoc, $sau ] ) {
			$kq    = null === $truoc ? 'chưa có số trước' : ( $truoc === $sau ? 'khớp' : 'KHÁC' );
			$khac += 'KHÁC' === $kq ? 1 : 0;
			\WP_CLI::line( '  ' . $viec . str_repeat( ' ', max( 1, 32 - mb_strlen( $viec ) ) ) . ( null === $truoc ? '?' : $truoc ) . ' → ' . $sau . '  ' . $kq );
		}
		if ( isset( $tuy['duong-dan'] ) ) {
			foreach ( \Phongdao\DichVu\NhapDuLieuCu\DoiChieu::duong_dan() as [ $cu, $moi ] ) {
				$r     = wp_remote_head( $cu, array( 'redirection' => 0, 'timeout' => 15 ) );
				$ma    = (int) wp_remote_retrieve_response_code( $r );
				$toi   = (string) wp_remote_retrieve_header( $r, 'location' );
				$dung  = 301 === $ma && untrailingslashit( $toi ) === untrailingslashit( $moi );
				$khac += $dung ? 0 : 1;
				\WP_CLI::line( ( $dung ? '  ĐÚNG ' : '  SAI  ' ) . "$cu → $ma $toi" );
			}
		}
		if ( $khac ) {
			\WP_CLI::warning( "$khac dòng khác — xem lại trước khi mở cho người đọc." );
		} else {
			\WP_CLI::success( 'Trước = sau.' );
		}
	}

	/**
	 * In phiên bản plugin và lược đồ cơ sở dữ liệu.
	 */
	public function phien_ban(): void {
		\WP_CLI::line( 'Plugin ' . CADAO_PHIEN_BAN . ' · lược đồ ' . (int) get_option( CaiDat::TUY_CHON, 0 ) );
	}
}
