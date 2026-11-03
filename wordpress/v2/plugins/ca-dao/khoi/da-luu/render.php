<?php
/**
 * Khối cadao/da-luu: khung trang Đã lưu; view.js điền danh sách từ bộ nhớ trình duyệt (khoá cadao-da-luu, như MVP).
 */

defined( 'ABSPATH' ) || exit;

use Phongdao\GiaoTiep\Khoi\Ve;
use Phongdao\MienDuLieu\TheLoai;

$chon = '<option value="">Tất cả</option>';
foreach ( TheLoai::DS as $ten ) {
	$chon .= '<option>' . esc_html( $ten ) . '</option>';
}
// phpcs:disable WordPress.Security.EscapeOutput -- các phần động đã thoát ở trên / trong Ve
echo '<nav aria-label="Đường dẫn" class="breadcrumbs">' . Ve::lien_ket( 'trang-chu', 'Trang chủ' ) . '<span aria-hidden="true">/</span><span>Đã lưu</span></nav>'
	. '<p class="eyebrow">Giữ để đọc lại</p><h1>Những câu đã lưu.</h1>'
	. '<p class="muted">Danh sách nằm trong trình duyệt trên máy này — thư viện không biết bạn lưu gì. Đổi máy, dùng chế độ ẩn danh hay xoá dữ liệu trình duyệt thì danh sách không còn.</p>'
	. '<div data-da-luu>'
	. '<noscript><div class="notice">Trang này cần JavaScript: danh sách bài đã lưu nằm trong trình duyệt của bạn, không nằm trên máy chủ.</div></noscript>'
	. '<div class="notice" data-chan hidden>Bộ nhớ trình duyệt đang bị chặn (thường do chế độ ẩn danh hoặc cài đặt riêng tư) nên không đọc, không lưu được danh sách.</div>'
	. '<div class="row" data-loc hidden><label for="da-luu-loc">Lọc thể loại</label><select id="da-luu-loc">' . $chon . '</select></div>'
	. '<p class="small muted" role="status" aria-live="polite" data-so></p>'
	. '<div class="grid" data-ds></div>'
	. '<div class="empty" data-trong hidden><h2>Giữ lại một câu để đọc sau.</h2><p>Bấm Lưu ở tác phẩm bạn muốn giữ.</p>' . Ve::lien_ket( 'kho', 'Mở kho tác phẩm', 'btn primary' ) . '</div>'
	. '</div>';
