<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\DichVu\NhapNguon\NguonTuDong;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\HoSo;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\HoSoNguon;

defined( 'ABSPATH' ) || exit;

/**
 * Mã ngắn [cadao_nguon] — danh sách nguồn đang được dùng, nằm trong nội dung trang Nguồn và trang Chính sách thu thập
 * dữ liệu (trang tĩnh do người sáng lập sửa, mang nguyên từ MVP). Cùng tên mã ngắn MVP để các trang đó vẫn hiện đúng
 * sau khi chuyển (bộ thiết kế R25). Đọc từ hồ sơ nguồn và nguồn tự động của V2.
 */
final class Nguon {

	public static function dang_ky(): void {
		add_shortcode( 'cadao_nguon', array( self::class, 'hien' ) );
	}

	public static function hien(): string {
		global $wpdb;
		$ra     = '';
		$da_lay = array(); // hồ sơ của nguồn tự động: đã nói ở phần "Trang web đã thu thập"
		// Nguồn tự động: số tác phẩm đang đăng lấy từ nguồn đó (D-068: không ghi dưới từng chú thích, ghi ở đây)
		$tu_dong = '';
		foreach ( NguonTuDong::tat_ca() as $ma => $n ) {
			$so = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_phongdao_nguon_may' WHERE p.post_type = %s AND p.post_status = 'publish' AND m.meta_value LIKE %s", KhaiBao::TAC_PHAM, '%"' . $ma . '"%' ) );
			if ( ! empty( $n['ho_so'] ) ) {
				$da_lay[ (int) $n['ho_so'] ] = true;
			}
			if ( $so ) {
				$ten      = (string) ( ( $n['dong_ghi_nguon'] ?? '' ) ?: ( $n['ten'] ?? $ma ) );
				$tu_dong .= '<li>' . ( empty( $n['url'] ) ? esc_html( $ten ) : '<a href="' . esc_url( $n['url'] ) . '" rel="noopener">' . esc_html( $ten ) . '</a>' )
					. ' — lời, dị bản, chú thích, đáp án của ' . esc_html( number_format_i18n( $so ) ) . ' tác phẩm đang đăng được thu thập từ trang này.</li>';
			}
		}
		if ( $tu_dong ) {
			$ra .= '<h2>Trang web đã thu thập</h2><ul>' . $tu_dong . '</ul>';
		}
		// Hồ sơ nguồn người biên soạn ghi (sách, trang web, người kể…) và wiki giấy phép mở
		$nhom = array();
		foreach ( get_posts( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'numberposts' => 500, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
			if ( isset( $da_lay[ $p->ID ] ) ) {
				continue;
			}
			$hs     = HoSo::truong( $p->ID );
			$loai   = (string) ( $hs['loai'] ?? 'web' );
			$chi    = array_filter( array( $hs['tac_gia'] ?? '', $hs['nam'] ?? '', $hs['giay_phep'] ?? '' ) );
			$ten    = empty( $hs['url'] ) ? esc_html( $p->post_title ) : '<a href="' . esc_url( (string) $hs['url'] ) . '" rel="noopener">' . esc_html( $p->post_title ) . '</a>';
			$nhom[ 'wiki' === $loai ? 'wiki' : 'khac' ][] = '<li>' . $ten . ( $chi ? ' — ' . esc_html( implode( ', ', $chi ) ) : '' ) . ( isset( HoSoNguon::LOAI[ $loai ] ) && 'wiki' !== $loai ? ' <span class="small muted">(' . esc_html( HoSoNguon::LOAI[ $loai ] ) . ')</span>' : '' ) . '</li>';
		}
		if ( ! empty( $nhom['khac'] ) ) {
			$ra .= '<h2>Nguồn người biên soạn ghi</h2><ul>' . implode( '', $nhom['khac'] ) . '</ul>';
		}
		// Dữ liệu mở (chỉ khi đã dùng)
		$mo = $nhom['wiki'] ?? array();
		$wd = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . Viec::bang() . " WHERE loai = 'wikidata' AND trang_thai = %s AND quyet = 'dung'", Viec::XONG ) );
		if ( $wd ) {
			$mo[] = '<li><a href="https://www.wikidata.org" rel="noopener">Wikidata</a> (CC0) — ' . esc_html( number_format_i18n( $wd ) ) . ' địa danh, sự vật đã được người biên soạn xác nhận.</li>';
			$mo[] = '<li><a href="https://commons.wikimedia.org" rel="noopener">Wikimedia Commons</a> — ảnh minh hoạ với giấy phép mở; tác giả và giấy phép ghi dưới từng ảnh.</li>';
		}
		if ( $mo ) {
			$ra .= '<h2>Dữ liệu mở</h2><ul>' . implode( '', $mo ) . '</ul>';
		}
		return $ra ?: '<p>Chưa có tác phẩm nào dùng nguồn.</p>';
	}
}
