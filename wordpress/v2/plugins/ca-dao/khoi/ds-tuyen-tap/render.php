<?php
/**
 * Khối cadao/ds-tuyen-tap: danh mục tuyển tập (mẫu archive-tuyen_tap), lọc ?loai=.
 */

defined( 'ABSPATH' ) || exit;

echo \Phongdao\GiaoTiep\Khoi\TuyenTap::danh_muc( sanitize_key( wp_unslash( $_GET['loai'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.EscapeOutput -- lọc đọc, đã thoát trong TuyenTap.
