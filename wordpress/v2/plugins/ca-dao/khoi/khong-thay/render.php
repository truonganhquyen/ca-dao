<?php
/**
 * Khối cadao/khong-thay (mẫu 404): đường dẫn gõ sai hoặc bài đã gỡ — đưa người đọc tới Tìm với chữ lấy từ đường dẫn.
 */

defined( 'ABSPATH' ) || exit;

$ve    = \Phongdao\GiaoTiep\Khoi\Ve::class;
$duong = (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
$cuoi  = basename( untrailingslashit( rawurldecode( $duong ) ) );
$chu   = trim( (string) preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $cuoi ) );
echo '<p class="eyebrow">Không tìm thấy trang</p><h1>Trang này không có ở đây.</h1>'
	. '<p class="muted">Có thể đường dẫn gõ nhầm, hoặc bài đã đổi địa chỉ. Bạn thử tìm câu mình nhớ:</p>'
	. '<form class="search-form" role="search" method="get" action="' . esc_url( $ve::url( 'tim' ) ) . '"><label class="sr-only" for="q-404">Câu hoặc từ cần tìm</label>'
	. '<input id="q-404" name="q" type="search" value="' . esc_attr( mb_substr( $chu, 0, 80 ) ) . '" placeholder="Gõ vài chữ, có dấu hoặc không dấu">'
	. '<button type="submit" class="btn primary">' . $ve::icon( 'search' ) . 'Tìm</button></form>'
	. '<div class="actions" style="margin-top:24px">' . $ve::lien_ket( 'kho', 'Xem tất cả tác phẩm', 'btn' ) . $ve::lien_ket( 'trang-chu', 'Về trang chủ', 'btn ghost' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- đã thoát.
