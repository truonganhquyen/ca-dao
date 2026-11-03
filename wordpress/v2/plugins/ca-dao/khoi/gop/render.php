<?php
/**
 * Khối cadao/gop: trang Góp tư liệu (/gop/?loai=&bai=&vi_pham=).
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification -- chỉ đọc để điền sẵn form; form gửi có nonce
echo \Phongdao\GiaoTiep\Khoi\GopTuLieu::hien( // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát trong GopTuLieu.
	sanitize_key( wp_unslash( $_GET['loai'] ?? '' ) ),
	(int) ( $_GET['bai'] ?? 0 ),
	sanitize_key( wp_unslash( $_GET['gui'] ?? '' ) ),
	sanitize_key( wp_unslash( $_GET['ma'] ?? '' ) ),
	(int) ( $_GET['vi_pham'] ?? 0 )
);
