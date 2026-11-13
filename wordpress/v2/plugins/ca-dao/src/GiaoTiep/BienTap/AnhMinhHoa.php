<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\KhoLuu\AnhMinhHoa as Anh;

defined( 'ABSPATH' ) || exit;

/** Dùng trình chọn/tải của WordPress, chỉ nạp trên màn Sửa tác phẩm. */
final class AnhMinhHoa {

	public static function dang_ky(): void {
		add_filter( 'attachment_fields_to_edit', static function ( array $fields, \WP_Post $post ): array {
			if ( wp_attachment_is_image( $post->ID ) && current_user_can( 'edit_post', $post->ID ) ) {
				$fields['phongdao_ghi_cong'] = array( 'label' => 'Ghi công ảnh', 'input' => 'textarea', 'value' => Anh::ghi_cong( $post->ID ), 'helps' => 'Điền đúng tác giả, giấy phép và địa chỉ theo quyền áp dụng. Hiện cùng ảnh; không tự xác nhận quyền sử dụng.' );
			}
			return $fields;
		} , 10, 2 );
		add_filter( 'attachment_fields_to_save', static function ( array $post, array $attachment ): array {
			$id = (int) ( $post['ID'] ?? 0 );
			if ( current_user_can( 'edit_post', $id ) && wp_attachment_is_image( $id ) && isset( $attachment['phongdao_ghi_cong'] ) ) {
				update_post_meta( $id, Anh::GHI_CONG, sanitize_textarea_field( $attachment['phongdao_ghi_cong'] ) );
			}
			return $post;
		}, 10, 2 );
		add_filter( 'wp_prepare_attachment_for_js', static function ( array $response, \WP_Post $post ): array {
			$response['phongdaoGhiCong'] = Anh::ghi_cong( $post->ID );
			return $response;
		}, 10, 2 );
		add_action( 'wp_enqueue_scripts', static function (): void {
			if ( is_singular( \Phongdao\HaTang\KhaiBao::TAC_PHAM ) ) {
				self::css();
			}
		} );
	}

	public static function css(): void {
		wp_enqueue_style( 'phongdao-anh-minh-hoa', plugins_url( 'assets/anh-minh-hoa.css', dirname( __DIR__, 3 ) . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN );
	}

	public static function nap_ma(): void {
		self::css();
		if ( current_user_can( 'upload_files' ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'phongdao-anh-minh-hoa', plugins_url( 'assets/anh-minh-hoa.js', dirname( __DIR__, 3 ) . '/ca-dao.php' ), array( 'media-views' ), CADAO_PHIEN_BAN, true );
		}
	}

	public static function form( ?array $cu ): string {
		$id = isset( $cu['attachment_id'] ) ? (int) $cu['attachment_id'] : Anh::id( $cu );
		$quyen = current_user_can( 'upload_files' );
		$edit = $id && current_user_can( 'edit_post', $id ) ? get_edit_post_link( $id ) : '';
		return '<div data-anh-minh-hoa><input type="hidden" name="attachment_id" value="' . $id . '">'
			. '<div data-anh-xem>' . ( $id ? \Phongdao\GiaoTiep\Khoi\AnhMinhHoa::html( $id ) : '<p>Chưa gắn ảnh.</p>' ) . '</div>'
			. '<p class="description">1. Chọn hoặc tải ảnh → 2. Kiểm mô tả và ghi công → 3. Lưu ảnh vào bài. Tệp tối đa: <strong>' . esc_html( size_format( wp_max_upload_size() ) ) . '</strong>.</p>'
			. '<p><button type="button" class="button" data-anh-chon' . ( $quyen ? '' : ' disabled' ) . '>Chọn hoặc tải ảnh</button> <button type="button" class="button" data-anh-bo' . ( $id ? '' : ' disabled' ) . '>Bỏ ảnh khỏi bài</button></p>'
			. '<p data-anh-bao role="status">' . ( $quyen ? 'Tải vào Thư viện chưa gắn ảnh vào bài. Chọn xong, bấm Lưu ảnh minh hoạ.' : 'Tài khoản cần quyền tải tệp để chọn hoặc thay ảnh.' ) . '</p>'
			. '<p class="description">Một ảnh tuỳ chọn, khác phần chữ “Hình ảnh trong bài”. Alt, chú thích và ghi công sửa trong Thư viện, dùng chung cho mọi bài gắn tệp đó. Bỏ khỏi bài không xoá tệp. Với câu đố, ảnh chỉ hiện trong đáp án.</p>'
			. '<p data-anh-sua>' . ( $edit ? '<a href="' . esc_url( $edit ) . '" target="_blank" rel="noopener">Sửa mô tả, chú thích và ghi công ảnh trong Thư viện</a>' : '' ) . '</p>'
			. '<noscript><p>Để chọn hoặc tải ảnh bằng Thư viện, bật JavaScript ở màn quản trị. Bạn vẫn có thể bỏ ảnh bằng nút Bỏ ảnh khi bật lại JavaScript.</p></noscript></div>';
	}
}
