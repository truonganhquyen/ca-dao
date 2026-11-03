<?php
/**
 * Khối cadao/tu-dien: trang Từ điển (/tu-dien/?q=, ?chu=).
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification -- tra cứu chỉ đọc
echo \Phongdao\GiaoTiep\Khoi\TuDien::hien( sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) ), sanitize_text_field( wp_unslash( $_GET['chu'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong TuDien.
