<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\LuuPhan\LuuPhanLoai;
use Phongdao\HaTang\KhaiBao;

defined( 'ABSPATH' ) || exit;

/** Các bộ thuật ngữ hiện có; người thêm mục, máy không tự tạo giá trị văn hoá (D-109). */
final class DanhMuc {

	public const TRANG = 'phongdao-danh-muc';

	public static function duoc( string $tax ): bool {
		$t = get_taxonomy( $tax );
		return array_key_exists( $tax, LuuPhanLoai::SUA_DUOC ) && $t && current_user_can( 'cadao_soan' ) && current_user_can( $t->cap->manage_terms );
	}

	public static function url( string $tax ): string {
		return add_query_arg( array( 'taxonomy' => $tax, 'post_type' => KhaiBao::TAC_PHAM ), admin_url( 'edit-tags.php' ) );
	}

	public static function hien(): string {
		$ra = '<div class="wrap phongdao-danh-muc"><h1>Quản lý danh mục Phongdao</h1><p>Thêm hoặc sửa tên các mục dùng chung. Thêm mục chưa gắn mục đó vào tác phẩm; trở lại trang sửa bài để chọn. Việc gắn/gỡ trong bài được ghi Lịch sử.</p><ul>';
		foreach ( LuuPhanLoai::SUA_DUOC as $tax => $_ ) {
			if ( self::duoc( $tax ) ) {
				$ra .= '<li><a class="button" style="min-height:44px;line-height:42px;margin:4px" href="' . esc_url( self::url( $tax ) ) . '">' . esc_html( KhaiBao::PHAN_LOAI[ $tax ][1] ) . '</a></li>';
			}
		}
		return $ra . '</ul><p>Thể loại tác phẩm giữ năm nhóm hiện có. Các mục đang dùng cần được người phụ trách cân nhắc trước khi đổi tên, đường dẫn hoặc xoá. Thể thơ riêng của bài được sửa tại trang Sửa tác phẩm.</p></div>';
	}

	/** Không gắn vào bài, không đổi phần văn bản hoặc quyền nội dung. */
	public static function tao( string $tax, string $ten ): array {
		if ( ! self::duoc( $tax ) ) {
			return array( 'ok' => false, 'loi' => 'Bạn không có quyền thêm mục vào bộ này.' );
		}
		$ten = trim( sanitize_text_field( $ten ) );
		if ( '' === $ten || mb_strlen( $ten ) > 100 ) {
			return array( 'ok' => false, 'loi' => 'Nhập tên mục từ 1 đến 100 ký tự.' );
		}
		$co = term_exists( $ten, $tax );
		$trung = (bool) $co;
		if ( ! $co ) {
			$co = wp_insert_term( $ten, $tax );
		}
		if ( is_wp_error( $co ) ) {
			if ( 'term_exists' !== $co->get_error_code() ) {
				return array( 'ok' => false, 'loi' => 'Chưa tạo được mục. Giữ tên vừa nhập và thử lại.' );
			}
			$co = array( 'term_id' => (int) $co->get_error_data() );
			$trung = true;
		}
		$id = is_array( $co ) ? (int) $co['term_id'] : (int) $co;
		$t = get_term( $id, $tax );
		return $t instanceof \WP_Term ? array( 'ok' => true, 'id' => $id, 'ten' => $t->name, 'trung' => $trung, 'the_tho' => LuuPhanLoai::may_giu( $tax, $t ) ) : array( 'ok' => false, 'loi' => 'Chưa đọc được mục. Thử lại sau.' );
	}

	public static function ajax(): void {
		$tax = sanitize_key( (string) ( $_POST['tax'] ?? '' ) );
		check_ajax_referer( 'phongdao_them_muc_' . $tax );
		$kq = self::tao( $tax, (string) wp_unslash( $_POST['ten'] ?? '' ) );
		if ( empty( $kq['ok'] ) ) {
			wp_send_json_error( array( 'thong_bao' => $kq['loi'] ), self::duoc( $tax ) ? 400 : 403 );
		}
		wp_send_json_success( $kq );
	}

	public static function cong_cu( string $tax ): string {
		if ( ! self::duoc( $tax ) ) {
			return '';
		}
		$ma = 'them-dm-' . $tax;
		return '<p><a href="' . esc_url( self::url( $tax ) ) . '" target="_blank" rel="noopener">Quản lý ' . esc_html( mb_strtolower( KhaiBao::PHAN_LOAI[ $tax ][1] ) ) . ' (mở tab mới)</a></p>'
			. '<details class="phongdao-them-muc" data-chi-js hidden><summary>+ Thêm mục</summary><div data-them-danh-muc data-tax="' . esc_attr( $tax ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'phongdao_them_muc_' . $tax ) ) . '"><label for="' . $ma . '">Tên mục mới</label><input type="text" id="' . $ma . '" maxlength="100" data-ten-muc><button type="button" class="button" data-tao-muc>Thêm vào danh sách</button><p data-tao-muc-bao role="status">Thêm xong, tự đánh dấu mục nếu muốn gắn vào bài rồi lưu phân loại.</p></div></details>';
	}
}
