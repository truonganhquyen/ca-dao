<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\LuuPhan\DuongDan;
use Phongdao\GiaoTiep\BienTap\NhapDo;

defined( 'ABSPATH' ) || exit;

final class SuaDuongDan {
	public static function dang_ky(): void {
		add_action( 'admin_post_phongdao_sua_duong_dan', array( self::class, 'luu' ) );
	}

	public static function hien( \WP_Post $bai, string $ve ): string {
		$url = (string) get_permalink( $bai );
		$ra = '<details class="phongdao-sua-phan" id="duong-dan"' . ( 'duong_dan' === ( $_GET['phan'] ?? '' ) ? ' open' : '' ) . '><summary>Đường dẫn tác phẩm</summary><p><a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a></p>';
		if ( ! current_user_can( 'manage_options' ) ) { return $ra . '<p>Quản trị có thể sửa tên cuối đường dẫn tại đây.</p></details>'; }
		$loi = 'loi' === ( $_GET['ket_qua'] ?? '' ) ? NhapDo::lay( (string) ( $_GET['nhap_do'] ?? '' ), $bai->ID, 'duong_dan' ) : null;
		$phien = DuongDan::phien( $bai->ID );
		return $ra . '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-giu-nhap="' . $bai->ID . ':duong_dan" data-ten-phan="Đường dẫn" data-phien-soan="' . esc_attr( NhapDo::phien() ) . '" data-url-hien-tai="' . esc_url( $url ) . '" data-phien-url="' . esc_attr( $phien ) . '"' . ( $loi ? ' data-giu-loi="1"' : '' ) . '>'
			. '<input type="hidden" name="action" value="phongdao_sua_duong_dan"><input type="hidden" name="id" value="' . $bai->ID . '"><input type="hidden" name="ve" value="' . esc_attr( $ve ) . '"><input type="hidden" name="phien_duong_dan" value="' . esc_attr( (string) ( $loi['phien_duong_dan'] ?? $phien ) ) . '">' . wp_nonce_field( 'phongdao_sua_duong_dan_' . $bai->ID, '_wpnonce', true, false )
			. '<label for="pd-url-slug">Tên cuối đường dẫn</label><input class="large-text" id="pd-url-slug" name="slug" value="' . esc_attr( (string) ( $loi['slug'] ?? $bai->post_name ) ) . '" maxlength="200" required autocomplete="off">'
			. '<p class="description">Ví dụ: ten-tac-pham. Phần thể loại được giữ; đổi thể loại dùng nút riêng. Đường cũ chuyển hướng 301 sau khi đổi, khi bài công khai.</p><p data-url-xem aria-live="polite"></p>'
			. '<label class="choice"><input type="checkbox" name="xac_nhan_url" value="1" required>Tôi muốn đổi đường dẫn của tác phẩm này.</label><p><button type="submit" class="button button-primary">Lưu đường dẫn</button></p></form></details>';
	}

	public static function luu(): void {
		$id = absint( $_POST['id'] ?? 0 );
		check_admin_referer( 'phongdao_sua_duong_dan_' . $id );
		$vao = wp_unslash( $_POST );
		$kq = '1' === ( $vao['xac_nhan_url'] ?? '' )
			? DuongDan::luu( $id, (string) ( $vao['slug'] ?? '' ), (string) ( $vao['phien_duong_dan'] ?? '' ), get_current_user_id() )
			: array( 'ok' => false, 'loi' => array( 'Đánh dấu xác nhận trước khi đổi đường dẫn.' ) );
		$args = array( 'phan' => 'duong_dan', 'ket_qua' => $kq['ok'] ? ( $kq['doi'] ? 'da_luu' : 'khong_doi' ) : 'loi' );
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), implode( ' ', $kq['loi'] ), MINUTE_IN_SECONDS );
			$args['nhap_do'] = NhapDo::giu( $id, 'duong_dan', $vao );
		}
		$ve = SuaTacPham::ve_hop_le( (string) ( $vao['ve'] ?? '' ) );
		wp_safe_redirect( add_query_arg( $args, SuaTacPham::url( $id, $ve ) ) . '#duong-dan' );
		exit;
	}
}
