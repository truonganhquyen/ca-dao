<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\MienDuLieu\DinhDang;

defined( 'ABSPATH' ) || exit;

/** Editor WordPress theo ô, khởi tạo khi người biên soạn mở phần. */
final class EditorDonGian {

	public static function nap(): void {
		wp_enqueue_editor();
		$tep = dirname( __DIR__, 3 ) . '/ca-dao.php';
		wp_enqueue_script( 'phongdao-editor', plugins_url( 'assets/editor-don-gian.js', $tep ), array( 'editor', 'phongdao-giu-nhap' ), CADAO_PHIEN_BAN, true );
		wp_enqueue_style( 'phongdao-editor', plugins_url( 'assets/editor-don-gian.css', $tep ), array(), CADAO_PHIEN_BAN );
	}

	public static function o( string $id, string $ten, string $nhan, array $cu, string $truong = 'chu', string $tien_to = '' ): string {
		return '<div class="pd-editor" data-editor' . ( $tien_to ? ' data-editor-hang' : '' ) . '><label for="' . esc_attr( $id ) . '">' . esc_html( $nhan ) . '</label><textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $ten ) . '" rows="5" data-editor-chu>' . esc_textarea( (string) ( $cu[ $truong ] ?? '' ) ) . '</textarea>'
			. '<input type="hidden" data-editor-html name="' . esc_attr( $tien_to ? $tien_to . '[dinh_dang]' : 'dinh_dang' ) . '" value="' . esc_attr( DinhDang::sach( (string) ( $cu['dinh_dang'] ?? '' ) ) ) . '"><input type="hidden" data-editor-bat name="' . esc_attr( $tien_to ? $tien_to . '[editor_truc_quan]' : 'editor_truc_quan' ) . '" value="0">'
			. '<p class="description">Bôi chọn chữ để định dạng hoặc chèn liên kết. Bấm Lưu riêng phần này khi xong.</p></div>';
	}

	/** Nhập không JavaScript/sửa nhanh vẫn dùng chữ thuần; định dạng cũ chỉ giữ khi chữ không đổi. */
	public static function doc( array $vao, ?array $cu, string $truong = 'chu' ): ?string {
		if ( '1' === (string) ( $vao['editor_truc_quan'] ?? '' ) ) {
			$html = DinhDang::sach( (string) ( $vao['dinh_dang'] ?? '' ) );
			return '' === $html ? null : $html;
		}
		return (string) ( $vao[ $truong ] ?? '' ) === (string) ( $cu[ $truong ] ?? '' ) ? ( $cu['dinh_dang'] ?? null ) : null;
	}
}
