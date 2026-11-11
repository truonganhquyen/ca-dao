<?php

namespace Phongdao\HaTang;

use Phongdao\DichVu\ChiMucTim\ChiMucTim;
use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\NhapNguon\NguonTuDong;
use Phongdao\KhoLuu\HoSo;
use Phongdao\KhoLuu\Kho;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Cài đặt và nâng cấp cơ sở dữ liệu theo số phiên bản lược đồ (docs/26 mục 4.4). Mỗi bước đánh số, chạy lại được.
 * Máy chủ cập nhật plugin bằng cách chép tệp (không bấm Kích hoạt), nên nâng cấp chạy ở init khi số lược đồ cũ hơn.
 */
final class CaiDat implements Module {

	public const PHIEN_BAN_CSDL = 12;

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
		if ( $da_cai < 3 ) {
			self::buoc_3();
		}
		if ( $da_cai < 4 ) {
			self::buoc_4();
		}
		if ( $da_cai < 5 ) {
			self::buoc_5();
		}
		if ( $da_cai < 6 ) {
			self::buoc_6();
		}
		if ( $da_cai < 7 ) {
			self::buoc_7();
		}
		if ( $da_cai < 8 ) {
			// Bước 8: ba bảng đo lường, cùng cấu trúc MVP (site thật đã có: dbDelta giữ nguyên dữ liệu)
			global $wpdb;
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			foreach ( \Phongdao\DichVu\DoLuong\DoLuong::sql_tao( $wpdb->get_charset_collate() ) as $sql ) {
				dbDelta( $sql );
			}
		}
		if ( $da_cai < 9 ) {
			self::buoc_9();
		}
		if ( $da_cai < 10 ) {
			global $wpdb;
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( Viec::sql_tao( Viec::bang(), $wpdb->get_charset_collate() ) );
			if ( false === $wpdb->query( 'SELECT phien FROM ' . Viec::bang() . ' LIMIT 0' ) ) {
				return; // không ghi phiên bản lược đồ mới để lượt sau thử lại
			}
		}
		if ( $da_cai < 11 ) {
			// Bước 11 (T-187): lịch chọn câu mới hằng ngày đúng 06:00 giờ Việt Nam (trước chạy 13:00 vì hẹn theo UTC)
			\Phongdao\DichVu\NhapNguon\NguonTuDong::hen_lai_lich_ngay();
		}
		if ( $da_cai < 12 && ! self::buoc_12() ) {
			return; // không ghi phiên bản lược đồ mới để lượt sau thử lại
		}
		update_option( self::TUY_CHON, self::PHIEN_BAN_CSDL, false );
	}

	/**
	 * Bước 12 (docs/32 mục 3.9): bảng tóm tắt bài. Chạy lại được (dbDelta giữ dữ liệu). Kho vừa (≤ 3.000 tác phẩm) dựng ngay
	 * để danh sách đúng từ lần mở đầu; kho lớn dựng theo lô bằng việc nền. Hẹn việc nền tính lượt đọc 7 ngày hằng ngày.
	 */
	private static function buoc_12(): bool {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$bang = \Phongdao\DichVu\TomTat\TomTat::bang();
		dbDelta( \Phongdao\DichVu\TomTat\TomTat::sql_tao( $bang, $wpdb->get_charset_collate() ) );
		if ( false === $wpdb->query( 'SELECT doc_7 FROM ' . $bang . ' LIMIT 0' ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- tên bảng của plugin
			return false;
		}
		$so = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status <> 'auto-draft'", KhaiBao::TAC_PHAM ) );
		if ( $so <= 3000 ) {
			\Phongdao\DichVu\TomTat\TomTat::dung_lai_ngay();
		} else {
			\Phongdao\DichVu\TomTat\TomTat::bat_dau_dung_lai();
		}
		\Phongdao\DichVu\TomTat\TomTat::hen_lich();
		return true;
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

	/** Bước 3: bảng chỉ mục tìm (cùng cấu trúc bảng MVP), dựng lại chỉ mục từ các phần đã lưu. */
	private static function buoc_3(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( ChiMucTim::sql_tao( ChiMucTim::bang(), $wpdb->get_charset_collate() ) );
		ChiMucTim::bat_dau_dung_lai();
	}

	/**
	 * Bước 4: kho thu thập (cùng cấu trúc bảng MVP), hồ sơ nguồn Ca dao Mẹ, nguồn tự động Ca dao Mẹ (TẮT — người quản trị
	 * tự bật; cấu hình MVP còn thì giữ). Chính sách hiện mặc định như hiện nay: nguồn chú thích chép ẩn (D-068, D-085);
	 * xin phép: chưa (thư chưa gửi).
	 */
	private static function buoc_4(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( Kho::sql_tao( Kho::bang(), $wpdb->get_charset_collate() ) );
		KhaiBao::khai_bao();
		$ho_so = HoSo::tao(
			'cadao-me',
			'Ca dao Mẹ',
			array(
				'loai'            => 'web',
				'url'             => 'https://cadao.me',
				'ghi_cong'        => 'Ca dao Mẹ (cadao.me)',
				'xin_phep'        => 'chua',
				'chinh_sach_hien' => 'an',
				'chep_chu_thich'  => true,
			)
		);
		NguonTuDong::tao_mac_dinh( $ho_so );
		$nguon = NguonTuDong::lay( 'cadao-me' );
		if ( $nguon && empty( $nguon['ho_so'] ) ) {
			$nguon['ho_so'] = $ho_so;
			NguonTuDong::luu( $nguon );
		}
	}

	/**
	 * Bước 6: quyền riêng (docs/26 mục 5.1). Quản trị: mọi quyền; Biên tập: soạn, duyệt; vai mới Cộng tác: gửi đối chiếu,
	 * soát lời, không đăng. Quyền nguồn (hồ sơ nguồn, nguồn tự động, chính sách hiện) chỉ Quản trị.
	 */
	private static function buoc_6(): void {
		$quyen = array(
			'administrator' => array( 'cadao_soan', 'cadao_duyet', 'cadao_nguon', 'cadao_cong_tac' ),
			'editor'        => array( 'cadao_soan', 'cadao_duyet', 'cadao_cong_tac' ),
		);
		foreach ( $quyen as $vai => $ds ) {
			$r = get_role( $vai );
			foreach ( $r ? $ds : array() as $q ) {
				$r->add_cap( $q );
			}
		}
		if ( ! get_role( 'cadao_cong_tac' ) ) {
			add_role( 'cadao_cong_tac', 'Cộng tác', array( 'read' => true, 'cadao_cong_tac' => true ) );
		}
	}

	/**
	 * Bước 7 (docs/29 mục 5): V2 vừa thay chỗ MVP trên site có bài MVP đã đăng → đánh dấu "chờ nhập" để khách thấy trang
	 * nâng cấp tới khi `wp phongdao nhap --lam` xong. Site mới (bản xem trước, kiểm thử) không có bài MVP: không đánh dấu.
	 */
	private static function buoc_7(): void {
		global $wpdb;
		$co = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'", \Phongdao\DichVu\NhapDuLieuCu\DuLieuCu::LOAI_BAI ) );
		if ( $co && ! get_option( \Phongdao\DichVu\NhapDuLieuCu\NhapLam::TRANG_THAI ) ) {
			update_option( \Phongdao\DichVu\NhapDuLieuCu\NhapLam::TRANG_THAI, 'cho' );
		}
	}

	/**
	 * Bước 9: bộ vùng, chủ đề của dự án (cùng tên đường dẫn MVP — site thật đã có, chỉ tạo mục thiếu) và một lượt gợi ý
	 * chủ đề, vùng cho mọi bài đã đăng (thẻ trong Hộp việc).
	 */
	private static function buoc_9(): void {
		KhaiBao::khai_bao();
		foreach ( \Phongdao\MienDuLieu\GoiY::VUNG as $ma => $ten ) {
			if ( ! term_exists( $ma, 'vung' ) ) {
				wp_insert_term( $ten, 'vung', array( 'slug' => $ma ) );
			}
		}
		foreach ( \Phongdao\MienDuLieu\GoiY::CHU_DE as $nhom => $ds ) {
			$cha = 0;
			if ( '' !== $nhom ) {
				$co  = term_exists( $nhom, 'chu_de' );
				$cha = (int) ( $co ? $co['term_id'] : ( wp_insert_term( \Phongdao\MienDuLieu\GoiY::NHOM_CHU_DE[ $nhom ], 'chu_de', array( 'slug' => $nhom ) )['term_id'] ?? 0 ) );
			}
			foreach ( $ds as $ma => $ten ) {
				if ( ! term_exists( $ma, 'chu_de' ) ) {
					wp_insert_term( $ten, 'chu_de', array( 'slug' => $ma, 'parent' => $cha ) );
				}
			}
		}
		\Phongdao\DichVu\GoiY\GoiY::bat_dau();
	}

	/** Bước 5: ba bảng chống trùng (cùng cấu trúc MVP: vân tay, cặp âm tiết, các cặp và quyết định). */
	private static function buoc_5(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( ChongTrung::sql_tao( $wpdb->get_charset_collate() ) as $sql ) {
			dbDelta( $sql );
		}
	}
}
