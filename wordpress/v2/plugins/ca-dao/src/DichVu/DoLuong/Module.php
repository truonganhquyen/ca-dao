<?php

namespace Phongdao\DichVu\DoLuong;

use Phongdao\HaTang\KhaiBao;
use Phongdao\HaTang\KhoiDong;
use Phongdao\MienDuLieu\DoLuong as Kiem;

defined( 'ABSPATH' ) || exit;

/**
 * Nhận lượt đọc và sự kiện (REST phongdao/v1, không cần đăng nhập) và nạp assets/do-luong.js cho khách — người biên
 * soạn đang đăng nhập không tính. Mã đọc trang (doc.js) không gọi máy chủ: nó chỉ phát sự kiện trong trang
 * ("phongdao:su-kien"), do-luong.js nhận và gửi.
 */
final class Module implements \Phongdao\HaTang\Module {

	public function dang_ky(): void {
		add_action( 'rest_api_init', array( self::class, 'duong' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'nap' ), 30 );
	}

	public static function duong(): void {
		register_rest_route(
			'phongdao/v1',
			'/luot-doc/(?P<id>\d+)',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true', // người đọc không đăng nhập
				'callback'            => array( self::class, 'luot_doc' ),
			)
		);
		register_rest_route(
			'phongdao/v1',
			'/su-kien',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( self::class, 'su_kien' ),
			)
		);
	}

	public static function luot_doc( \WP_REST_Request $yc ): \WP_REST_Response {
		$bai = get_post( (int) $yc['id'] );
		if ( ! $bai || KhaiBao::TAC_PHAM !== $bai->post_type || 'publish' !== $bai->post_status ) {
			return new \WP_REST_Response( array( 'tinh' => false ), 404 );
		}
		if ( Kiem::la_may_do( (string) $yc->get_header( 'user_agent' ) ) || ! DoLuong::lan_dau_trong_ngay( 'doc:' . $bai->ID ) ) {
			return new \WP_REST_Response( array( 'tinh' => false ), 200 );
		}
		DoLuong::cong_luot_doc( $bai->ID, Kiem::ten_mien( (string) $yc->get_param( 'tu' ), (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) );
		return new \WP_REST_Response( array( 'tinh' => true ), 200 );
	}

	public static function su_kien( \WP_REST_Request $yc ): \WP_REST_Response {
		$loai = (string) $yc->get_param( 'loai' );
		$khoa = Kiem::khoa( $loai, (array) $yc->get_params() );
		if ( '' === $khoa || Kiem::la_may_do( (string) $yc->get_header( 'user_agent' ) ) ) {
			return new \WP_REST_Response( array( 'ghi' => false ), 200 );
		}
		if ( in_array( $loai, array( 'trich_dan_sao_chep', 'chia_se' ), true ) && KhaiBao::TAC_PHAM !== get_post_type( (int) $yc->get_param( 'bai' ) ) ) {
			return new \WP_REST_Response( array( 'ghi' => false ), 200 );
		}
		if ( 'mong_co_cau' === $loai && ! \Phongdao\KhoLuu\Kho::lay( (int) $yc->get_param( 'kho' ) ) ) {
			return new \WP_REST_Response( array( 'ghi' => false ), 200 );
		}
		if ( ! DoLuong::lan_dau_trong_ngay( $loai . ':' . $khoa ) ) {
			return new \WP_REST_Response( array( 'ghi' => false ), 200 );
		}
		DoLuong::ghi_su_kien( $loai, $khoa );
		return new \WP_REST_Response( array( 'ghi' => true ), 200 );
	}

	/** Ghi "tìm không ra" khi trang Tìm dựng xong không có kết quả (khách, không phải máy dò). */
	public static function tim_khong_ra( string $q ): void {
		$khoa = Kiem::chuoi_tim( $q );
		if ( '' === $khoa || is_user_logged_in() || Kiem::la_may_do( (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ) || ! DoLuong::lan_dau_trong_ngay( 'tim:' . $khoa ) ) {
			return;
		}
		DoLuong::ghi_su_kien( 'tim_khong_ket_qua', $khoa );
	}

	public static function nap(): void {
		if ( is_user_logged_in() ) {
			return; // người biên soạn xem bài không tính
		}
		$bai = is_singular( KhaiBao::TAC_PHAM ) ? get_queried_object_id() : 0;
		wp_enqueue_script( 'phongdao-do-luong', plugins_url( 'assets/do-luong.js', KhoiDong::thu_muc() . '/ca-dao.php' ), array(), CADAO_PHIEN_BAN, array( 'strategy' => 'defer', 'in_footer' => true ) );
		// Cấu hình là khối dữ liệu JSON (trình duyệt không chạy), không phải mã nội tuyến: CSP không cần cho phép
		$du = array(
			'luotDoc' => $bai ? rest_url( 'phongdao/v1/luot-doc/' . $bai ) : '',
			'bai'     => (string) $bai,
			'suKien'  => rest_url( 'phongdao/v1/su-kien' ),
		);
		add_action(
			'wp_footer',
			static function () use ( $du ): void {
				echo '<script type="application/json" id="phongdao-do-luong">' . wp_json_encode( $du, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- JSON, JSON_HEX_TAG chặn </script>
			},
			5
		);
	}
}
