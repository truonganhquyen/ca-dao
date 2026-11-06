<?php

namespace Phongdao\GiaoTiep\Khoi;

use Phongdao\HaTang\KhoiDong;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\VanTay;

defined( 'ABSPATH' ) || exit;

/**
 * Phần thêm của mục từ và đáp án câu đố đã xác nhận trên Wikidata (docs/27 mục 7.4; chuyển từ MVP wikidata.php,
 * sua-muc-tu.php): ảnh Commons đã tải về site (giấy phép mở, ghi công tác giả · giấy phép · Wikimedia Commons) hoặc ảnh
 * riêng người biên soạn chọn, ẩn ảnh được; bản đồ nhỏ có chấm (bản đồ tự lưu, có Hoàng Sa, Trường Sa); liên kết bản
 * đồ lớn, Wikipedia. Không ảnh thì không chừa chỗ. Ảnh nằm trên site, không tải từ máy chủ ngoài.
 */
final class DiaDanh {

	/** Dữ liệu Wikidata đã xác nhận của mục từ, cộng lựa chọn ảnh của người biên soạn. */
	public static function cua_muc_tu( int $term_id ): ?array {
		$d   = get_term_meta( $term_id, '_phongdao_wikidata', true );
		$d   = is_array( $d ) ? $d : array();
		$anh = get_term_meta( $term_id, '_phongdao_anh', true );
		$anh = is_array( $anh ) ? $anh : array();
		$url = empty( $anh['rieng'] ) ? false : wp_get_attachment_image_url( (int) $anh['rieng'], 'medium_large' );
		if ( $url ) {
			$t               = get_term( $term_id );
			$d['anh_da_tai'] = array( 'rieng' => true, 'id' => (int) $anh['rieng'], 'url' => $url, 'alt' => $t instanceof \WP_Term ? $t->name : '', 'ghi_cong' => (string) ( $anh['ghi_cong'] ?? '' ) );
		} elseif ( ! empty( $anh['an'] ) ) {
			unset( $d['anh_da_tai'] );
		}
		return $d ?: null;
	}

	/** Ảnh, Wikipedia của một đáp án câu đố người sáng lập đã xác nhận Đúng (việc wikidata, khoá như MVP). */
	public static function cua_dap_an( string $dap_an ): ?array {
		global $wpdb;
		$khoa = 'dap:' . md5( VanTay::chuan_loi( $dap_an ) );
		$nd   = $wpdb->get_var( $wpdb->prepare( 'SELECT noi_dung FROM ' . Viec::bang() . " WHERE loai = 'wikidata' AND quyet = 'dung' AND noi_dung LIKE %s ORDER BY id DESC LIMIT 1", '%' . $wpdb->esc_like( '"khoa":"' . $khoa . '"' ) . '%' ) );
		$d    = (array) ( json_decode( (string) $nd, true )['du_lieu'] ?? array() );
		$d    = array_intersect_key( $d, array( 'anh_da_tai' => 1, 'nhan_wd' => 1, 'wiki' => 1 ) );
		return $d ?: null;
	}

	/** HTML: ảnh (kèm ghi công), bản đồ nhỏ, liên kết bản đồ lớn, Wikipedia. Rỗng khi không có gì. */
	public static function html( array $wd, string $kich_thuoc = 'medium' ): string {
		$ra  = '';
		$anh = $wd['anh_da_tai'] ?? null;
		if ( is_array( $anh ) && ! empty( $anh['url'] ) ) {
			$id_anh = (int) ( $anh['id'] ?? 0 );
			if ( ! $id_anh ) {
				$id_anh = attachment_url_to_postid( (string) $anh['url'] );
			}
			$ban_nho = $id_anh ? wp_get_attachment_image_src( $id_anh, $kich_thuoc ) : false;
			$url_anh = $ban_nho ? (string) $ban_nho[0] : (string) $anh['url'];
			$ghi = ! empty( $anh['rieng'] )
				? esc_html( (string) ( $anh['ghi_cong'] ?? '' ) )
				: 'Ảnh: ' . esc_html( (string) ( $anh['tac_gia'] ?? '' ) ) . ' · ' . ( empty( $anh['giay_phep_url'] ) ? esc_html( (string) ( $anh['giay_phep'] ?? '' ) ) : '<a href="' . esc_url( $anh['giay_phep_url'] ) . '" rel="noopener">' . esc_html( (string) ( $anh['giay_phep'] ?? '' ) ) . '</a>' )
					. ' · <a href="' . esc_url( (string) ( $anh['trang'] ?? 'https://commons.wikimedia.org' ) ) . '" rel="noopener">Wikimedia Commons</a>';
			$ra .= '<figure class="anh-wd" style="margin:12px 0"><img src="' . esc_url( $url_anh ) . '" alt="' . esc_attr( (string) ( $anh['alt'] ?? $wd['nhan_wd'] ?? '' ) ) . '" loading="lazy" decoding="async" style="display:block;max-width:100%;max-height:14rem;width:auto;height:auto;border-radius:6px">'
				. ( '' === trim( wp_strip_all_tags( $ghi ) ) ? '' : '<figcaption class="small muted">' . $ghi . '</figcaption>' ) . '</figure>';
		}
		if ( ! empty( $wd['toa_do'] ) && is_array( $wd['toa_do'] ) ) {
			[ $lat, $lon ]              = array_map( 'floatval', array_values( $wd['toa_do'] ) + array( 0, 0 ) );
			[ $tay, $dong, $bac, $nam ] = BanDo::KHUNG;
			if ( $lon >= $tay && $lon <= $dong && $lat <= $bac && $lat >= $nam ) {
				$ra .= '<div class="ban-do-nho" role="img" aria-label="' . esc_attr( 'Vị trí của ' . ( $wd['nhan_wd'] ?? $wd['nhan'] ?? '' ) . ' trên bản đồ Việt Nam' ) . '" style="position:relative;max-width:160px;margin:12px 0"><img src="' . esc_url( plugins_url( 'assets/ban-do-vn.svg', KhoiDong::thu_muc() . '/ca-dao.php' ) ) . '" alt="" width="160" height="160" loading="lazy" style="display:block;width:100%;height:auto">'
					. '<span aria-hidden="true" style="position:absolute;left:' . round( ( $lon - $tay ) / ( $dong - $tay ) * 100, 2 ) . '%;top:' . round( ( $bac - $lat ) / ( $bac - $nam ) * 100, 2 ) . '%;width:12px;height:12px;margin:-6px 0 0 -6px;border-radius:50%;background:var(--tim);border:2px solid var(--white)"></span></div>';
			}
			$ra .= '<p class="small"><a href="' . esc_url( sprintf( 'https://www.openstreetmap.org/?mlat=%1$s&mlon=%2$s#map=11/%1$s/%2$s', $lat, $lon ) ) . '" rel="noopener">Mở bản đồ lớn <span aria-hidden="true">↗</span></a></p>';
		}
		if ( ! empty( $wd['wiki'] ) ) {
			$ra .= '<p class="small"><a href="' . esc_url( (string) $wd['wiki'] ) . '" rel="noopener">Đọc thêm trên Wikipedia <span aria-hidden="true">↗</span></a></p>';
		}
		return $ra;
	}
}
