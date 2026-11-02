<?php
/**
 * Theme Phongdao (V2): chỉ hiển thị. Nghiệp vụ nằm ở plugin (docs/26 mục 6); tệp này giữ dưới 50 dòng.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		wp_enqueue_style( 'ca-dao', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
	}
);

// Không tải emoji từ máy chủ ngoài (không dịch vụ bên thứ ba khi người đọc chưa mở — AGENTS.md).
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
