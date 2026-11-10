<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\DichVu\ChongTrung\ChongTrung;
use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\GiaoTiep\Khoi\Ve;
use Phongdao\HaTang\KhaiBao;
use Phongdao\MienDuLieu\KiemPhan;
use Phongdao\MienDuLieu\NguoiLam;
use Phongdao\MienDuLieu\TheLoai;
use Phongdao\MienDuLieu\TheTho;
use Phongdao\MienDuLieu\VanTay;

defined( 'ABSPATH' ) || exit;

/**
 * Thêm bài `/bien-tap/them/` (docs/28 mục 4.4; màn mẫu B04): chọn thể loại, dán lời, máy nhận thể thơ và báo trùng
 * (chỉ báo, không chặn — D-054), Lưu nháp hoặc Đăng; xong mở trang bài để sửa tiếp. Form gửi `admin-post.php`.
 */
final class ThemBai {

	public const HANH_DONG = 'phongdao_them_bai';

	public static function dang_ky(): void {
		add_action( 'admin_post_' . self::HANH_DONG, array( self::class, 'nhan_form' ) );
	}

	public static function hien(): string {
		$da   = sanitize_text_field( wp_unslash( (string) ( $_GET['da'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- chỉ đọc
		$loai = '';
		foreach ( TheLoai::DS as $ma => $ten ) {
			$loai .= '<label class="chip-chon"><input type="radio" name="the_loai" value="' . esc_attr( $ma ) . '"' . checked( 'ca-dao', $ma, false ) . '><span>' . esc_html( $ten ) . '</span></label>';
		}
		return '<p class="eyebrow">Biên tập</p><h1>Thêm bài</h1>' . ( '' === $da ? '' : '<p class="notice" role="status">' . esc_html( $da ) . '</p>' )
			. '<form class="them-bai" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-them-bai><input type="hidden" name="action" value="' . self::HANH_DONG . '">' . wp_nonce_field( self::HANH_DONG, '_wpnonce', true, false )
			. '<fieldset class="nhom-o"><legend>Thể loại</legend><div class="row">' . $loai . '</div></fieldset>'
			. '<div class="o-nhap"><label for="them-loi">Lời · dán vào, mỗi dòng một câu <span class="small muted">không sửa theo trí nhớ</span></label><textarea id="them-loi" name="loi" rows="8" required></textarea></div>'
			. '<div id="them-phan-tich" class="phan-tich" aria-live="polite"><p class="small muted">Dán lời để máy nhận thể thơ và kiểm trùng.</p></div>'
			. '<div class="o-nhap"><label for="them-ten">Tên bài <span class="small muted">để trống = dòng đầu</span></label><input id="them-ten" name="ten" type="text"></div>'
			. '<div class="o-nhap"><label for="them-nguon">Nguồn <span class="small muted">sách, trang, đường dẫn, hoặc người kể (cần đồng ý) — không bắt buộc</span></label><input id="them-nguon" name="nguon" type="text"></div>'
			. '<div class="actions"><button type="submit" name="dang" value="0" class="btn primary">Lưu nháp</button>' . ( current_user_can( 'cadao_duyet' ) ? '<button type="submit" name="dang" value="1" class="btn">Đăng ngay</button>' : '' ) . '</div>'
			. '<p class="small muted">Lưu nháp rồi sửa tiếp ngay trên trang bài (ý nghĩa, từ khó, chủ đề). Không bắt buộc đủ phần mới được đăng.</p></form>';
	}

	public static function nhan_form(): void {
		if ( ! current_user_can( 'cadao_soan' ) || ! wp_verify_nonce( (string) ( $_POST['_wpnonce'] ?? '' ), self::HANH_DONG ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			wp_die( esc_html__( 'Phiên làm việc đã hết hoặc không có quyền. Tải lại trang rồi thử lại.', 'ca-dao' ), '', array( 'response' => 403 ) );
		}
		$kq = self::tao(
			array(
				'the_loai' => sanitize_key( (string) ( $_POST['the_loai'] ?? '' ) ),
				'loi'      => (string) wp_unslash( $_POST['loi'] ?? '' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- KiemPhan chuẩn hoá
				'ten'      => sanitize_text_field( wp_unslash( (string) ( $_POST['ten'] ?? '' ) ) ),
				'nguon'    => sanitize_text_field( wp_unslash( (string) ( $_POST['nguon'] ?? '' ) ) ),
				'dang'     => ! empty( $_POST['dang'] ) && current_user_can( 'cadao_duyet' ),
			),
			get_current_user_id()
		);
		if ( ! $kq['ok'] ) {
			wp_safe_redirect( add_query_arg( 'da', rawurlencode( 'Chưa lưu: ' . implode( ' ', $kq['loi'] ) ), home_url( '/bien-tap/them/' ) ) );
			exit;
		}
		$url = 'publish' === get_post_status( $kq['bai'] ) ? (string) get_permalink( $kq['bai'] ) : (string) get_preview_post_link( $kq['bai'] );
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Tạo tác phẩm nhập tay: lời người viết; nguồn người gõ (đường dẫn hoặc ghi chú), không tự điền gì.
	 *
	 * @param array{the_loai:string,loi:string,ten:string,nguon:string,dang:bool} $f
	 * @return array{ok:bool,bai:int,loi:string[]}
	 */
	public static function tao( array $f, int $nguoi ): array {
		$the_loai = TheLoai::co( $f['the_loai'] ) ? $f['the_loai'] : 'ca-dao';
		$dong     = explode( "\n", str_replace( "\r", '', trim( $f['loi'] ) ) );
		if ( '' === trim( implode( '', $dong ) ) ) {
			return array( 'ok' => false, 'bai' => 0, 'loi' => array( 'Chưa có lời.' ) );
		}
		$ten = '' !== trim( $f['ten'] ) ? trim( $f['ten'] ) : trim( $dong[0] );
		$id  = wp_insert_post( wp_slash( array( 'post_type' => KhaiBao::TAC_PHAM, 'post_status' => 'draft', 'post_title' => $ten, 'post_name' => \Phongdao\DichVu\LuuPhan\DuongDan::ten_moi( $ten ), 'post_author' => $nguoi ) ), true );
		if ( is_wp_error( $id ) || ! $id ) {
			return array( 'ok' => false, 'bai' => 0, 'loi' => array( 'Chưa tạo được bài.' ) );
		}
		wp_set_object_terms( $id, $the_loai, KhaiBao::THE_LOAI );
		$nguon = trim( $f['nguon'] );
		$loi   = array( 'dong' => $dong, 'nguoi_lam' => NguoiLam::NGUOI_VIET )
			+ ( '' === $nguon ? array() : array( 'nguon' => preg_match( '#^https?://#i', $nguon ) ? array( 'url' => $nguon ) : array( 'ghi_chu' => mb_substr( $nguon, 0, 200 ) ) ) );
		$kq    = LuuPhan::luu( $id, 'loi', $loi, array( 'nguoi' => $nguoi, 'goi' => KiemPhan::GOI_NGUOI ) );
		if ( ! $kq['ok'] ) {
			wp_delete_post( $id, true );
			return array( 'ok' => false, 'bai' => 0, 'loi' => $kq['loi'] );
		}
		// Đăng qua wp_update_post (kiểm trùng tên đường dẫn); wp_publish_post không kiểm, từng làm hai bài cùng tên chung một
		// địa chỉ và người đọc mất lối vào bài cũ (T-178)
		if ( $f['dang'] ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		}
		return array( 'ok' => true, 'bai' => $id, 'loi' => array() );
	}

	/** Máy nhận thể thơ và kiểm trùng cho lời đang dán (REST, chỉ người biên soạn). */
	public static function kiem( string $loi ): array {
		$dong = array_values( array_filter( array_map( 'trim', explode( "\n", str_replace( "\r", '', $loi ) ) ), 'strlen' ) );
		$ra   = array();
		foreach ( $dong ? ChongTrung::tim( implode( "\n", $dong ), array(), 5 ) : array() as $t ) {
			[ $loai, $id ] = ChongTrung::tach_muc( $t['muc'] );
			$bai           = 'bai' === $loai ? get_post( $id ) : null;
			$ra[]          = array(
				'muc'   => VanTay::MUC[ $t['cap_do'] ] ?? $t['cap_do'],
				'giong' => $t['giong'],
				'ten'   => $bai ? $bai->post_title : 'Một câu trong kho (chưa đăng)',
				'url'   => $bai ? (string) get_permalink( $bai ) : '',
			);
		}
		return array(
			'the_tho' => $dong ? TheTho::DS[ TheTho::nhan( $dong ) ] : '',
			'so_dong' => count( $dong ),
			'trung'   => $ra,
		);
	}
}
