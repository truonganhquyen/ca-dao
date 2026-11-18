<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\GiaoTiep\BienTap\ThemBai;
use Phongdao\MienDuLieu\TheLoai;

defined( 'ABSPATH' ) || exit;

/**
 * Thêm tác phẩm ngay trong wp-admin (D-104, docs/32 mục 3.3; báo cáo trinh sát lần 2 mục 3.4): cùng biểu mẫu, cùng đường
 * lưu (`ThemBai::tao`: lời người viết, nguồn người gõ, không tự điền) và cùng kiểm trùng khi dán lời (`bien-tap.js` gọi
 * REST `trung/kiem` — chỉ báo, không chặn) như `/bien-tap/them/`, nhưng không văng ra giao diện trang đọc. Lưu xong mở
 * trang Sửa tác phẩm để viết tiếp.
 */
final class ThemTacPham {

	public const TRANG = 'phongdao-them';

	public static function dang_ky(): void {
		// Màn ẩn khỏi menu, mở từ Bài mới, Tác phẩm, "Thêm mới → Tác phẩm" của thanh trên cùng
	}

	public static function url(): string {
		return admin_url( 'admin.php?page=' . self::TRANG );
	}

	/** Mã kiểm trùng khi dán lời: dùng lại `bien-tap.js` (chỉ phần Thêm bài chạy ở màn này) với nonce REST. */
	public static function nap_ma(): void {
		$tep = dirname( __DIR__, 3 ) . '/ca-dao.php';
		wp_enqueue_script( 'phongdao-bien-tap', plugins_url( 'assets/bien-tap.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_add_inline_script( 'phongdao-bien-tap', 'window.phongdaoBienTap=' . wp_json_encode( array( 'api' => esc_url_raw( rest_url( \Phongdao\GiaoTiep\Rest\Module::KHONG_GIAN ) ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) ) . ';', 'before' );
		wp_enqueue_style( 'phongdao-admin-tac-pham', plugins_url( 'assets/admin-tac-pham.css', $tep ), array(), CADAO_PHIEN_BAN );
	}

	public static function hien(): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '<div class="wrap"><p>Bạn không có quyền thêm tác phẩm.</p></div>';
		}
		$da    = sanitize_text_field( wp_unslash( (string) ( $_GET['da'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc lời báo
		$duyet = current_user_can( 'cadao_duyet' );
		$loai  = '';
		foreach ( TheLoai::DS as $ma => $ten ) {
			$loai .= '<label class="phongdao-them-loai"><input type="radio" name="the_loai" value="' . esc_attr( $ma ) . '"' . checked( 'ca-dao', $ma, false ) . '> ' . esc_html( $ten ) . '</label>';
		}
		// Người có quyền duyệt: "Đăng ngay" là nút chính, "Lưu nháp" phụ; người chỉ soạn: chỉ "Lưu nháp"
		$nut = $duyet
			? '<button type="submit" name="dang" value="1" class="button button-primary">Đăng ngay</button> <button type="submit" name="dang" value="0" class="button">Lưu nháp</button>'
			: '<button type="submit" name="dang" value="0" class="button button-primary">Lưu nháp</button>';
		return '<div class="wrap pq-man phongdao-admin-tp phongdao-them">'
			. '<p class="pq-duong"><a href="' . esc_url( admin_url( 'admin.php?page=' . DanhSachTacPham::TRANG ) ) . '">Tác phẩm</a> › Thêm</p>'
			. '<div class="pq-dau"><div class="pq-dau-chu"><h1>Thêm tác phẩm</h1><p>Dán lời đúng như nguồn; máy nhận thể thơ và báo nếu có thể trùng. Lưu xong mở trang sửa để viết tiếp.</p></div><div class="pq-dau-nut"><a class="button" href="' . esc_url( DanhSachKho::url() ) . '">← Bài mới</a></div></div><hr class="wp-header-end">'
			. ( '' === $da ? '' : '<div class="notice notice-error"><p role="status">' . esc_html( $da ) . '</p></div>' )
			. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="pq-khoi phongdao-them-the" data-them-bai><input type="hidden" name="action" value="' . ThemBai::HANH_DONG . '"><input type="hidden" name="tu" value="quan_tri">' . wp_nonce_field( ThemBai::HANH_DONG, '_wpnonce', true, false )
			. '<div class="phongdao-them-o"><label for="them-loi">Lời <span class="description">dán vào, mỗi dòng một câu — không sửa theo trí nhớ</span></label><textarea id="them-loi" name="loi" rows="8" class="large-text phongdao-them-loi-o" required></textarea></div>'
			. '<div id="them-phan-tich" class="phongdao-them-kiem" aria-live="polite"><p class="description">Dán lời để máy nhận thể thơ và kiểm trùng (chỉ báo, không chặn).</p></div>'
			. '<div class="phongdao-them-hai"><fieldset class="phongdao-them-nhom"><legend>Thể loại</legend>' . $loai . '</fieldset>'
			. '<div class="phongdao-them-o"><label for="them-ten">Tên bài <span class="description">để trống = dòng đầu</span></label><input id="them-ten" name="ten" type="text" class="regular-text"></div></div>'
			. '<div class="phongdao-them-o"><label for="them-nguon">Nguồn <span class="description">sách, trang, đường dẫn, hoặc người kể (cần đồng ý) — không bắt buộc, không đoán</span></label><input id="them-nguon" name="nguon" type="text" class="large-text"></div>'
			. '<p class="phongdao-them-nut">' . $nut . '</p>'
			. '<p class="description">Không bắt buộc đủ phần mới được đăng. Ý nghĩa, từ khó, chủ đề viết tiếp ở trang sửa.</p></form></div>';
	}
}
