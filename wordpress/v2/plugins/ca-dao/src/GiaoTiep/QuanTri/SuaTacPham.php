<?php

namespace Phongdao\GiaoTiep\QuanTri;

use Phongdao\DichVu\XepViec\QuyetViec;
use Phongdao\GiaoTiep\BienTap\CheDoSua;
use Phongdao\GiaoTiep\BienTap\Module as BienTap;
use Phongdao\GiaoTiep\BienTap\TamSua;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\Viec;

defined( 'ABSPATH' ) || exit;

/** Sửa từng phần trong wp-admin bằng đúng dịch vụ của tấm sửa trên trang đọc. */
final class SuaTacPham {
	public const TRANG = 'phongdao-sua-tac-pham';

	public static function dang_ky(): void {
		add_action( 'admin_post_phongdao_sua_phan', array( self::class, 'luu' ) );
	}

	public static function url( int $id ): string {
		return add_query_arg( array( 'page' => self::TRANG, 'id' => $id ), admin_url( 'admin.php' ) );
	}

	/** @return string[] */
	private static function phan_theo_loai( string $loai ): array {
		$ra = array();
		$rieng = array( 'nghia_den' => 'tuc-ngu', 'nghia_bong' => 'tuc-ngu', 'dung_khi' => 'tuc-ngu', 'cau_hoi' => 'cau-do', 'dap_an' => 'cau-do', 'goi_y' => 'cau-do', 'boi_canh' => 've', 'doan' => 've' );
		foreach ( array_keys( TamSua::TAM ) as $phan ) {
			if ( ! isset( $rieng[ $phan ] ) || $loai === $rieng[ $phan ] ) {
				$ra[] = $phan;
			}
		}
		return $ra;
	}

	public static function hien(): string {
		$id = absint( $_GET['id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$bai = $id ? get_post( $id ) : null;
		if ( ! current_user_can( 'cadao_soan' ) || ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || ! current_user_can( 'edit_post', $id ) || 'trash' === $bai->post_status ) {
			return '<div class="wrap"><h1>Sửa tác phẩm</h1><p>Chọn tác phẩm có quyền sửa từ <a href="' . esc_url( admin_url( 'admin.php?page=' . DanhSachTacPham::TRANG ) ) . '">danh sách Tác phẩm</a>.</p></div>';
		}
		$ctx = CheDoSua::ngu_canh( $bai );
		if ( null === $ctx ) {
			return '<div class="wrap"><p>Bạn không có quyền sửa bài này.</p></div>';
		}
		$terms = get_the_terms( $id, KhaiBao::THE_LOAI );
		$loai = is_array( $terms ) && $terms ? $terms[0]->slug : '';
		$mo = Viec::mo( 'post', $id );
		$ket_qua = sanitize_key( (string) ( $_GET['ket_qua'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc thông báo
		$bao = array( 'da_luu' => 'Đã lưu phần bài.', 'nhap' => 'Đã lưu nháp vào Hộp việc; người đọc chưa thấy.', 'khong_doi' => 'Không có gì thay đổi.', 'loi' => 'Chưa lưu được. Kiểm lại dữ liệu và thử tiếp.' )[ $ket_qua ] ?? '';
		if ( 'loi' === $ket_qua ) {
			$khoa_loi = 'phongdao_sua_loi_' . $id . '_' . get_current_user_id();
			$bao = (string) get_transient( $khoa_loi ) ?: $bao;
			delete_transient( $khoa_loi );
		}
		$ra = '<div class="wrap phongdao-admin-sua"><h1>Sửa tác phẩm #' . $id . ': ' . esc_html( $bai->post_title ) . '</h1>';
		$ra .= '<p><a href="' . esc_url( admin_url( 'admin.php?page=' . DanhSachTacPham::TRANG ) ) . '">← Danh sách Tác phẩm</a> · <a href="' . esc_url( get_preview_post_link( $bai ) ) . '">Xem trên trang đọc</a></p>';
		$ten_trang_thai = array( 'publish' => 'Đã đăng', 'draft' => 'Bản nháp', 'pending' => 'Chờ', 'private' => 'Đã ẩn', 'future' => 'Đã hẹn' )[ $bai->post_status ] ?? $bai->post_status;
		$ra .= '<p>Trạng thái: <strong>' . esc_html( $ten_trang_thai ) . '</strong>. Mỗi phần lưu riêng, qua cùng quy tắc với nút Sửa trên trang đọc.</p>';
		if ( $bao ) {
			$ra .= '<div class="notice ' . ( 'loi' === $ket_qua ? 'notice-error' : 'notice-success' ) . '"><p role="status">' . esc_html( $bao ) . '</p></div>';
		}
		$ra .= '<section class="phongdao-sua-viec"><h2>Việc đang chờ (' . count( $mo ) . ')</h2>';
		if ( ! $mo ) {
			$ra .= '<p>Không có việc đang chờ cho bài này.</p>';
		} else {
			$ra .= '<ul>';
			foreach ( $mo as $viec ) {
				$ten_viec = QuyetViec::LOAI[ $viec['loai'] ][0] ?? $viec['loai'];
				$ten_viec_tt = array( Viec::CHO => 'Chờ', Viec::DE_SAU => 'Để sau' )[ $viec['trang_thai'] ] ?? $viec['trang_thai'];
				$ra .= '<li><a href="' . esc_url( add_query_arg( array( 'page' => DanhSachViec::TRANG, 'viec' => $viec['id'] ), admin_url( 'admin.php' ) ) ) . '">' . esc_html( $ten_viec . ' #' . $viec['id'] ) . '</a> · ' . esc_html( $ten_viec_tt ) . '</li>';
			}
			$ra .= '</ul>';
		}
		$ra .= '</section><h2>Sửa theo phần</h2>';
		foreach ( self::phan_theo_loai( $loai ) as $phan ) {
			$ten = TamSua::TAM[ $phan ][0];
			$cu = $ctx['phan'][ $phan ] ?? null;
			$ra .= '<details class="phongdao-sua-phan" id="phan-' . esc_attr( $phan ) . '"' . ( $phan === ( $_GET['phan'] ?? '' ) ? ' open' : '' ) . '><summary>' . esc_html( $ten ) . ( null === $cu ? ' · Chưa có' : '' ) . '</summary>';
			$ten_goc = str_starts_with( $ten, 'Sửa ' ) ? mb_substr( $ten, 4 ) : $ten;
			$ten_goc = mb_strtoupper( mb_substr( $ten_goc, 0, 1 ) ) . mb_substr( $ten_goc, 1 );
			$ra .= CheDoSua::goc( $ten_goc, $cu, $ctx );
			$ra .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="phongdao_sua_phan"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="phan" value="' . esc_attr( $phan ) . '">';
			$ra .= wp_nonce_field( 'phongdao_sua_phan_' . $id . '_' . $phan, '_wpnonce', true, false );
			$ra .= TamSua::than( $phan, $cu, $ctx ) . '<p><button type="submit" class="button button-primary">Lưu phần này</button></p></form></details>';
		}
		return $ra . '</div>';
	}

	public static function luu(): void {
		$id = absint( $_POST['id'] ?? 0 );
		$phan = sanitize_key( (string) ( $_POST['phan'] ?? '' ) );
		$bai = $id ? get_post( $id ) : null;
		if ( ! current_user_can( 'cadao_soan' ) || ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'trash' === $bai->post_status || ! current_user_can( 'edit_post', $id ) || ! isset( TamSua::TAM[ $phan ] ) ) {
			wp_die( 'Bạn không có quyền sửa phần này.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'phongdao_sua_phan_' . $id . '_' . $phan );
		$yc = new \WP_REST_Request( 'POST', '/phongdao/v1/tac-pham/' . $id . '/phan/' . $phan );
		$yc->set_param( 'id', $id );
		$yc->set_param( 'phan', $phan );
		$yc->set_body_params( wp_unslash( $_POST ) );
		$kq = BienTap::luu_phan( $yc );
		if ( ! $kq['ok'] ) {
			set_transient( 'phongdao_sua_loi_' . $id . '_' . get_current_user_id(), implode( ' ', $kq['loi'] ?? array() ), MINUTE_IN_SECONDS );
		}
		$ket_qua = ! $kq['ok'] ? 'loi' : ( ! empty( $kq['thong_bao'] ) && str_contains( $kq['thong_bao'], 'nháp' ) ? 'nhap' : ( ! empty( $kq['doi'] ) ? 'da_luu' : 'khong_doi' ) );
		wp_safe_redirect( add_query_arg( array( 'ket_qua' => $ket_qua, 'phan' => $phan ), self::url( $id ) ) . '#phan-' . $phan );
		exit;
	}
}
