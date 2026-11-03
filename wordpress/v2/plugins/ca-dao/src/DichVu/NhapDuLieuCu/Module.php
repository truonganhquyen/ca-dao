<?php

namespace Phongdao\DichVu\NhapDuLieuCu;

defined( 'ABSPATH' ) || exit;

/**
 * Trong lúc chuyển (docs/29 mục 5 bước 2–3): V2 đã thay chỗ nhưng dữ liệu MVP chưa nhập xong thì khách chưa đăng nhập
 * nhận trang "Thư viện đang được nâng cấp" (503, Retry-After) — không thấy trang thiếu bài. Người đăng nhập vẫn dùng
 * được để kiểm. Gỡ module này cùng công cụ nhập sau khi dọn dữ liệu cũ (T-164).
 */
final class Module implements \Phongdao\HaTang\Module {

	public function dang_ky(): void {
		add_action( 'template_redirect', array( self::class, 'bao_tri' ), 0 );
	}

	public static function bao_tri(): void {
		if ( ! NhapLam::dang_nang_cap() || is_user_logged_in() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}
		status_header( 503 );
		header( 'Retry-After: 1800' );
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
		echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">'
			. '<title>' . esc_html__( 'Thư viện đang được nâng cấp', 'ca-dao' ) . '</title>'
			. '<style>body{margin:0;min-height:100vh;display:grid;place-items:center;font:18px/1.6 system-ui,sans-serif;background:#faf8f3;color:#2a2238;padding:16px;box-sizing:border-box}main{max-width:32rem;text-align:center}h1{font-size:1.5rem;margin:0 0 .5rem}@media (prefers-color-scheme:dark){body{background:#17131f;color:#ece7f5}}</style>'
			. '</head><body><main><h1>' . esc_html__( 'Thư viện đang được nâng cấp', 'ca-dao' ) . '</h1><p>'
			. esc_html__( 'Mời bạn quay lại sau ít phút. Mọi bài và đường dẫn cũ vẫn giữ nguyên.', 'ca-dao' ) . '</p></main></body></html>';
		exit;
	}
}
