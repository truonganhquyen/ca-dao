<?php
/**
 * Tự nạp lớp, miền dữ liệu thuần (không gọi WordPress), BoDau (ca kiểm chuyển từ MVP wordpress/tests/kiem-thu.php).
 */

declare(strict_types=1);

use Phongdao\MienDuLieu\BoDau;

kt( 'tự nạp Phongdao\MienDuLieu\BoDau', class_exists( BoDau::class ) );
kt( 'lớp không có thì báo không có, không lỗi', ! class_exists( 'Phongdao\\KhongCo\\LopNay' ) );
kt( 'bỏ qua lớp ngoài không gian tên Phongdao', ! class_exists( 'PhongdaoKhac\\BoDau' ) );

// Miền dữ liệu không gọi hàm WordPress và không dùng $wpdb (docs/26 mục 3)
$goi_wp = array();
$mau    = '/^(add_|get_|update_|delete_|wp_|esc_|apply_filters|do_action|current_user_can|register_|__|_e|_x|_n)/';
foreach ( tep_php( V2_GOC . '/plugins/ca-dao/src/MienDuLieu' ) as $tep ) {
	$the = token_get_all( (string) file_get_contents( $tep ) );
	foreach ( $the as $i => $t ) {
		if ( is_array( $t ) && T_STRING === $t[0] && preg_match( $mau, $t[1] ) ) {
			$sau = $the[ $i + 1 ] ?? null;
			$truoc = $the[ $i - 1 ] ?? null;
			$la_phuong_thuc = is_array( $truoc ) && in_array( $truoc[0], array( T_FUNCTION, T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR ), true );
			if ( '(' === $sau && ! $la_phuong_thuc ) {
				$goi_wp[] = basename( $tep ) . ':' . $t[2] . ' ' . $t[1];
			}
		}
		if ( is_array( $t ) && T_VARIABLE === $t[0] && '$wpdb' === $t[1] ) {
			$goi_wp[] = basename( $tep ) . ':' . $t[2] . ' $wpdb';
		}
	}
}
kt( 'src/MienDuLieu không gọi hàm WordPress, không dùng $wpdb', array() === $goi_wp, $goi_wp );

kt( 'PHP có Normalizer (phần mở rộng intl) để chuẩn hoá NFC', class_exists( 'Normalizer' ) );
kt( 'nfc: "Hoa" + dấu huyền tổ hợp → "Hoà" dựng sẵn', "Ho\u{00E0}" === BoDau::nfc( "Hoa\u{0300}" ) );
kt( 'bo_dau: đ → d, bỏ dấu thanh và dấu phụ', 'Dong dao duoc' === BoDau::bo_dau( 'Đồng dao được' ) );
kt( 'cho_tim: "Công cha" → "cong cha", "Đình" → "dinh"', 'cong cha' === BoDau::cho_tim( 'Công cha' ) && 'dinh' === BoDau::cho_tim( 'Đình' ) );
kt( 'cho_tim cắt khoảng trắng hai đầu, chữ thường', 'ba co doi gao' === BoDau::cho_tim( '  Ba Cô Đội Gạo ' ) );
kt( 'chuan_hoa_co_dau: hoà→hòa, thuỷ→thủy, khoẻ→khỏe, huỷ→hủy', 'hòa thủy khỏe hủy' === BoDau::chuan_hoa_co_dau( 'hoà thuỷ khoẻ huỷ' ) );
kt( 'chuan_hoa_co_dau giữ nguyên: toàn, hoàng, quý, quả, loẹt, thuyền', 'toàn hoàng quý quả loẹt thuyền' === BoDau::chuan_hoa_co_dau( 'toàn hoàng quý quả loẹt thuyền' ) );
kt( 'chuan_hoa_co_dau trước dấu câu: "hoà," → "hòa,"', 'hòa, thủy.' === BoDau::chuan_hoa_co_dau( 'Hoà, thuỷ.' ) );
