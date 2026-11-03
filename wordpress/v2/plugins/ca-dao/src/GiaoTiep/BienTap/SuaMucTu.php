<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\NguoiLam;

defined( 'ABSPATH' ) || exit;

/**
 * Sửa mục từ ngay trên trang mục từ (docs/28 mục 4.3; thay trang "Sửa mục từ" của MVP sua-muc-tu.php): lời giải,
 * nguồn lời giải (chỉ người điền, máy không điền), ảnh riêng từ Thư viện ảnh kèm ghi công, ẩn ảnh Wikidata. Ảnh sai
 * vì máy khớp nhầm thực thể thì sửa ở việc Wikidata. Form gửi thẳng (không cần JavaScript), chỉ người có quyền biên
 * tập thấy; mỗi lần lưu ghi một dòng nhật ký (bảng việc).
 */
final class SuaMucTu {

	public const HANH_DONG = 'phongdao_sua_muc_tu';

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::HANH_DONG, array( self::class, 'nhan' ) );
	}

	/** Khối sửa trên trang mục từ (rỗng với người không có quyền). */
	public static function form( \WP_Term $t ): string {
		if ( ! current_user_can( 'cadao_soan' ) ) {
			return '';
		}
		$lg    = get_term_meta( $t->term_id, '_phongdao_loi_giai', true );
		$lg    = is_array( $lg ) ? $lg : array();
		$anh   = get_term_meta( $t->term_id, '_phongdao_anh', true );
		$anh   = is_array( $anh ) ? $anh : array();
		$ho_so = array( 0 => '— Chưa rõ —' );
		foreach ( get_posts( array( 'post_type' => KhaiBao::HO_SO_NGUON, 'post_status' => 'publish', 'numberposts' => 500, 'orderby' => 'title', 'order' => 'ASC' ) ) as $hs ) {
			$ho_so[ $hs->ID ] = $hs->post_title;
		}
		$chon = '';
		foreach ( $ho_so as $id => $ten ) {
			$chon .= '<option value="' . (int) $id . '"' . selected( (int) $id, (int) ( $lg['nguon']['ho_so'] ?? 0 ), false ) . '>' . esc_html( $ten ) . '</option>';
		}
		$may  = NguoiLam::MAY_CHEP === ( $lg['nguoi_lam'] ?? '' );
		$o    = static fn( string $id, string $nhan, string $html ): string => '<div class="o-nhap"><label for="' . $id . '">' . $nhan . '</label>' . $html . '</div>';
		$url  = empty( $anh['rieng'] ) ? '' : (string) wp_get_attachment_url( (int) $anh['rieng'] );
		$bao  = sanitize_text_field( wp_unslash( (string) ( $_GET['da'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc lời báo
		return ( '' === $bao ? '' : '<div class="notice" role="status">' . esc_html( $bao ) . '</div>' )
			. '<details class="disclosure no-print" id="sua-muc-tu"' . ( '' === $bao ? '' : ' open' ) . '><summary>Sửa mục từ</summary><div class="detail-body">'
			. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . wp_nonce_field( self::HANH_DONG . $t->term_id, '_wpnonce', true, false )
			. '<input type="hidden" name="action" value="' . self::HANH_DONG . '"><input type="hidden" name="muc" value="' . (int) $t->term_id . '">'
			. $o( 'smt-giai', 'Lời giải' . ( $may ? ' <span class="small muted">hiện: máy chép từ nguồn; sửa chữ thì thành "người viết"</span>' : '' ), '<textarea id="smt-giai" name="giai" rows="4">' . esc_textarea( $t->description ) . '</textarea>' )
			. '<fieldset class="nhom-o"><legend>Nguồn lời giải <span class="small muted">chỉ người điền, máy không tự điền</span></legend>'
			. $o( 'smt-hs', 'Hồ sơ nguồn', '<select id="smt-hs" name="ho_so">' . $chon . '</select>' )
			. $o( 'smt-url', 'Đường dẫn', '<input id="smt-url" name="url" type="url" value="' . esc_attr( (string) ( $lg['nguon']['url'] ?? '' ) ) . '">' ) . '</fieldset>'
			. '<fieldset class="nhom-o"><legend>Ảnh</legend>'
			. $o( 'smt-anh', 'Ảnh riêng <span class="small muted">dán đường dẫn một ảnh trong Thư viện ảnh; để trống = dùng ảnh Wikidata</span>', '<input id="smt-anh" name="anh" type="url" value="' . esc_attr( $url ) . '">' )
			. $o( 'smt-ghi', 'Ghi công ảnh riêng', '<input id="smt-ghi" name="ghi_cong" value="' . esc_attr( (string) ( $anh['ghi_cong'] ?? '' ) ) . '">' )
			. '<label class="choice" for="smt-an"><input type="checkbox" id="smt-an" name="an" value="1"' . checked( ! empty( $anh['an'] ), true, false ) . '>Ẩn ảnh lấy từ Wikidata</label>'
			. '<p class="small muted">Ảnh sai vì máy khớp nhầm thực thể thì sửa ở việc Wikidata (bấm "Sai").</p></fieldset>'
			. '<div class="actions"><button type="submit" class="btn primary">Lưu mục từ</button></div></form></div></details>';
	}

	public static function nhan(): void {
		$id = (int) ( $_POST['muc'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- kiểm ngay dưới
		if ( ! current_user_can( 'cadao_soan' ) || ! wp_verify_nonce( (string) ( $_POST['_wpnonce'] ?? '' ), self::HANH_DONG . $id ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			wp_die( esc_html__( 'Phiên làm việc đã hết hoặc không có quyền. Tải lại trang rồi thử lại.', 'ca-dao' ), '', array( 'response' => 403 ) );
		}
		$kq = self::luu( $id, wp_unslash( $_POST ), get_current_user_id() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- luu() làm sạch từng ô
		$t  = get_term( $id, 'muc_tu' );
		wp_safe_redirect( add_query_arg( 'da', rawurlencode( $kq ), $t instanceof \WP_Term ? (string) get_term_link( $t ) : home_url( '/tu-dien/' ) ) . '#sua-muc-tu' );
		exit;
	}

	/** Lưu một lần sửa; trả lời báo. Không có gì đổi thì không ghi. */
	public static function luu( int $id, array $f, int $nguoi ): string {
		$t = get_term( $id, 'muc_tu' );
		if ( ! $t instanceof \WP_Term ) {
			return 'Không tìm thấy mục từ.';
		}
		$lg_cu  = get_term_meta( $id, '_phongdao_loi_giai', true );
		$lg_cu  = is_array( $lg_cu ) ? $lg_cu : array();
		$anh_cu = get_term_meta( $id, '_phongdao_anh', true );
		$anh_cu = is_array( $anh_cu ) ? $anh_cu : array();
		$giai   = trim( sanitize_textarea_field( (string) ( $f['giai'] ?? '' ) ) );
		$doi    = $giai !== trim( (string) $t->description );
		$nguon  = array_filter(
			array(
				'ho_so' => (int) ( $f['ho_so'] ?? 0 ),
				'url'   => esc_url_raw( trim( (string) ( $f['url'] ?? '' ) ) ),
			)
		);
		// Sửa chữ lời giải → người viết; nguồn chỉ là thứ người vừa điền (máy không giữ nguồn của bản đã bị viết lại)
		$lg = array_filter(
			array(
				'nguoi_lam' => $doi || empty( $lg_cu['nguoi_lam'] ) ? NguoiLam::NGUOI_VIET : $lg_cu['nguoi_lam'],
				'nguon'     => $nguon ?: null,
			)
		);
		$url_anh = esc_url_raw( trim( (string) ( $f['anh'] ?? '' ) ) );
		$rieng   = '' === $url_anh ? 0 : (int) attachment_url_to_postid( $url_anh );
		if ( '' !== $url_anh && ! $rieng ) {
			return 'Chưa lưu: đường dẫn ảnh không phải ảnh trong Thư viện ảnh của site.';
		}
		$anh = array_filter(
			array(
				'rieng'    => $rieng,
				'ghi_cong' => trim( sanitize_text_field( (string) ( $f['ghi_cong'] ?? '' ) ) ),
				'an'       => ! empty( $f['an'] ),
			)
		);
		if ( ! $doi && $lg == $lg_cu && $anh == $anh_cu ) { // phpcs:ignore Universal.Operators.StrictComparisons -- thứ tự khoá không quan trọng
			return 'Không có gì thay đổi.';
		}
		if ( $doi ) {
			wp_update_term( $id, 'muc_tu', array( 'description' => $giai ) );
		}
		update_term_meta( $id, '_phongdao_loi_giai', $lg );
		$anh ? update_term_meta( $id, '_phongdao_anh', $anh ) : delete_term_meta( $id, '_phongdao_anh' );
		Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => 'term',
				'doi_tuong_id'   => $id,
				'phan'           => 'muc_tu',
				'nguoi_tao'      => (string) $nguoi,
				'quyet'          => 'luu',
				'truoc'          => array( 'loi_giai' => (string) $t->description, 'nguon' => $lg_cu, 'anh' => $anh_cu ),
				'sau'            => array( 'loi_giai' => $giai, 'nguon' => $lg, 'anh' => $anh ),
				'nguoi_quyet'    => $nguoi,
				'quyet_luc'      => gmdate( 'Y-m-d H:i:s' ),
			)
		);
		return 'Đã lưu mục từ.';
	}
}
