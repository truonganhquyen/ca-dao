<?php
/**
 * Khối cadao/tim (mẫu trang-tim): ô tìm và kết quả theo ?q=.
 */

defined( 'ABSPATH' ) || exit;

$q    = sanitize_text_field( wp_unslash( (string) ( $_GET['q'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc.
$html = \Phongdao\GiaoTiep\Khoi\Tim::trang( $q, \Phongdao\DichVu\ChiMucTim\TimDoc::loc( $_GET ) );
// Tìm bằng cả trang (gửi form, đường dẫn chia sẻ) mà không ra: ghi để biết nên biên soạn câu nào (không ghi khi đang gõ)
if ( '' !== trim( $q ) && str_contains( $html, 'id="search-status" class="small muted" aria-live="polite">0 kết quả' ) ) {
	\Phongdao\DichVu\DoLuong\Module::tim_khong_ra( $q );
}
echo str_replace( '<form ', '<form data-api="' . esc_url( rest_url( 'phongdao/v1/tim' ) ) . '" ', $html ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong Tim.
