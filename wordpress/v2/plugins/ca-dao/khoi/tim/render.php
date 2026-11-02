<?php
/**
 * Khối cadao/tim (mẫu trang-tim): ô tìm và kết quả theo ?q=.
 */

defined( 'ABSPATH' ) || exit;

$q = sanitize_text_field( wp_unslash( (string) ( $_GET['q'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc.
echo str_replace( '<form ', '<form data-api="' . esc_url( rest_url( 'phongdao/v1/tim' ) ) . '" ', \Phongdao\GiaoTiep\Khoi\Tim::trang( $q ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong Tim.
