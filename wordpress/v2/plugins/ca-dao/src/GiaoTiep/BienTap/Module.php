<?php

namespace Phongdao\GiaoTiep\BienTap;

use Phongdao\DichVu\LuuPhan\LuuPhan;
use Phongdao\GiaoTiep\Rest\Module as Rest;
use Phongdao\HaTang\KhaiBao;
use Phongdao\KhoLuu\PhanBai;
use Phongdao\KhoLuu\Viec;
use Phongdao\MienDuLieu\KiemPhan;

defined( 'ABSPATH' ) || exit;

/**
 * Biên tập trên trang đọc (D-094, docs/26 mục 7): REST lưu một phần / đổi trạng thái / hoàn tác (quyền riêng + nonce REST
 * của WordPress), mã sửa `bien-tap.js` chỉ nạp cho người có quyền, trang của người đăng nhập không vào bộ nhớ đệm.
 */
final class Module implements \Phongdao\HaTang\Module {

	public function dang_ky(): void {
		add_action( 'rest_api_init', array( self::class, 'route' ) );
		add_action(
			'send_headers',
			static function (): void {
				if ( is_user_logged_in() ) {
					header( 'Cache-Control: private, no-store, max-age=0' );
				}
			}
		);
		add_action( 'wp_enqueue_scripts', array( self::class, 'nap_ma' ) );
	}

	/** Mã sửa (JavaScript thuần, không bước dựng) chỉ cho người biên soạn, chỉ ở trang bài. */
	public static function nap_ma(): void {
		if ( ! CheDoSua::co_quyen() || ! is_singular( KhaiBao::TAC_PHAM ) ) {
			return;
		}
		$tep = dirname( __DIR__, 3 ) . '/ca-dao.php';
		wp_enqueue_script( 'phongdao-bien-tap', plugins_url( 'assets/bien-tap.js', $tep ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_add_inline_script(
			'phongdao-bien-tap',
			'window.phongdaoBienTap=' . wp_json_encode(
				array(
					'api'   => esc_url_raw( rest_url( Rest::KHONG_GIAN ) ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
				)
			) . ';',
			'before'
		);
	}

	public static function route(): void {
		$sua_bai = static fn( \WP_REST_Request $yc ): bool => current_user_can( 'cadao_soan' ) && current_user_can( 'edit_post', (int) $yc['id'] ) && KhaiBao::TAC_PHAM === get_post_type( (int) $yc['id'] );
		register_rest_route(
			Rest::KHONG_GIAN,
			'/tac-pham/(?P<id>\d+)/phan/(?P<phan>[a-z_]+)',
			array(
				'methods'             => 'POST',
				'permission_callback' => $sua_bai,
				'callback'            => array( self::class, 'luu_phan' ),
			)
		);
		register_rest_route(
			Rest::KHONG_GIAN,
			'/tac-pham/(?P<id>\d+)/trang-thai',
			array(
				'methods'             => 'POST',
				'permission_callback' => $sua_bai,
				'callback'            => array( self::class, 'trang_thai' ),
			)
		);
		register_rest_route(
			Rest::KHONG_GIAN,
			'/viec/(?P<id>\d+)/hoan-tac',
			array(
				'methods'             => 'POST',
				'permission_callback' => static function ( \WP_REST_Request $yc ): bool {
					$v = Viec::lay( (int) $yc['id'] );
					return $v && 'post' === $v['doi_tuong_loai'] && current_user_can( 'cadao_soan' ) && current_user_can( 'edit_post', (int) $v['doi_tuong_id'] );
				},
				'callback'            => static function ( \WP_REST_Request $yc ): array {
					$kq = LuuPhan::khoi_phuc( (int) $yc['id'], self::nc() );
					return $kq + array( 'thong_bao' => $kq['ok'] ? 'Đã hoàn tác.' : '' );
				},
			)
		);
	}

	/** Người đang bấm: ngữ cảnh lưu (quyền duyệt theo vai). */
	private static function nc(): array {
		return array(
			'nguoi' => get_current_user_id(),
			'goi'   => KiemPhan::GOI_NGUOI,
			'duyet' => current_user_can( 'cadao_duyet' ),
		);
	}

	/** Lưu một phần từ tấm sửa; ý nghĩa chưa duyệt thành nháp trong Hộp việc. */
	public static function luu_phan( \WP_REST_Request $yc ): array {
		$id   = (int) $yc['id'];
		$phan = (string) $yc['phan'];
		if ( ! isset( TamSua::TAM[ $phan ] ) ) {
			return array( 'ok' => false, 'loi' => array( 'Phần này chưa sửa được ở đây.' ) );
		}
		$doc = TamSua::doc( $phan, (array) $yc->get_body_params(), PhanBai::lay( $id, $phan ) );
		if ( null !== $doc['nhap'] ) {
			$viec = Viec::tao(
				array(
					'loai'           => 'nhap_y_nghia',
					'doi_tuong_loai' => 'post',
					'doi_tuong_id'   => $id,
					'phan'           => $phan,
					'noi_dung'       => $doc['nhap'],
					'nguoi_tao'      => (string) get_current_user_id(),
					'uu_tien'        => 35,
				)
			);
			return array( 'ok' => (bool) $viec, 'loi' => array(), 'viec' => 0, 'thong_bao' => 'Đã lưu nháp vào Hộp việc; người đọc chưa thấy.' );
		}
		$kq = LuuPhan::luu( $id, $phan, $doc['gia_tri'], self::nc() );
		return $kq + array( 'thong_bao' => $kq['ok'] ? ( $kq['doi'] ? 'Đã lưu.' : 'Không có gì thay đổi.' ) : '' );
	}

	/** Đăng / Ẩn / về nháp. Đăng cần quyền duyệt. Ghi một dòng nhật ký. */
	public static function trang_thai( \WP_REST_Request $yc ): array {
		$id  = (int) $yc['id'];
		$moi = (string) $yc['trang_thai'];
		$map = array( 'publish' => 'dang', 'private' => 'an', 'draft' => 'nhap' );
		if ( ! isset( $map[ $moi ] ) || ( 'publish' === $moi && ! current_user_can( 'cadao_duyet' ) ) ) {
			return array( 'ok' => false, 'loi' => array( 'publish' === $moi ? 'Cần quyền duyệt để đăng.' : 'Trạng thái không hợp lệ.' ) );
		}
		$cu = (string) get_post_status( $id );
		if ( $cu === $moi ) {
			return array( 'ok' => true, 'loi' => array(), 'thong_bao' => 'Không có gì thay đổi.' );
		}
		wp_update_post( array( 'ID' => $id, 'post_status' => $moi ) );
		$viec = Viec::tao(
			array(
				'loai'           => Viec::SUA,
				'trang_thai'     => Viec::XONG,
				'doi_tuong_loai' => 'post',
				'doi_tuong_id'   => $id,
				'phan'           => 'trang_thai',
				'nguoi_tao'      => (string) get_current_user_id(),
				'quyet'          => $map[ $moi ],
				'truoc'          => array( 'trang_thai' => $cu ),
				'sau'            => array( 'trang_thai' => $moi ),
				'nguoi_quyet'    => get_current_user_id(),
				'quyet_luc'      => gmdate( 'Y-m-d H:i:s' ),
			)
		);
		return array( 'ok' => true, 'loi' => array(), 'viec' => 0, 'thong_bao' => array( 'dang' => 'Đã đăng.', 'an' => 'Đã ẩn bài khỏi người đọc.', 'nhap' => 'Đã chuyển về bản nháp.' )[ $map[ $moi ] ], 'nhat_ky' => $viec );
	}
}
