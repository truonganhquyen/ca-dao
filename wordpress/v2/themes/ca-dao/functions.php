<?php
/**
 * Theme Phongdao (V2): chỉ hiển thị. Nghiệp vụ và khối cadao/* nằm ở plugin (docs/26 mục 6); tệp này giữ dưới 50 dòng.
 * CSS và fit.js sinh từ bộ thiết kế design/v2 (`npm run dong-bo:giao-dien-v2`); doc.js là tương tác của người đọc.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$ban = wp_get_theme()->get( 'Version' );
		$goc = get_stylesheet_directory_uri() . '/assets/';
		wp_enqueue_style( 'phongdao-giao-dien', $goc . 'giao-dien.css', array(), $ban );
		wp_enqueue_script( 'phongdao-fit', $goc . 'fit.js', array(), $ban, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_enqueue_script( 'phongdao-doc', $goc . 'doc.js', array( 'phongdao-fit' ), $ban, array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
);

// Sáng / tối người đọc đã chọn: đặt trước khi vẽ trang để không nháy màu. Không chọn thì theo cài đặt máy (CSS).
// CSP của plugin không cho mã nội tuyến: đoạn mã cố định này được phép bằng dấu băm (bộ lọc phongdao_csp_script).
const PHONGDAO_MA_GIAO_DIEN = 'try{var t=localStorage.getItem("phongdao-giao-dien");if(t==="light"||t==="dark")document.documentElement.dataset.theme=t}catch(e){}';
add_action( 'wp_head', static fn() => print( '<script>' . PHONGDAO_MA_GIAO_DIEN . "</script>\n" ), 1 ); // phpcs:ignore WordPress.Security.EscapeOutput -- hằng cố định
add_filter( 'phongdao_csp_script', static fn( array $ds ): array => array_merge( $ds, array( "'sha256-" . base64_encode( hash( 'sha256', PHONGDAO_MA_GIAO_DIEN, true ) ) . "'" ) ) );
// Theme đã có liên kết "Bỏ qua điều hướng"; liên kết của WordPress là mã nội tuyến (trùng, CSP chặn)
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' );

// Không tải emoji từ máy chủ ngoài (không dịch vụ bên thứ ba khi người đọc chưa mở — AGENTS.md).
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
